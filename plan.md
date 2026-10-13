# 1. Existing Architecture and Relevant Files
- **Controllers**: `app/Controllers/Masters/Client.php`, `app/Controllers/Masters/Hse_client.php`, `app/Controllers/Masters/Oe_nc_tracker.php`, `app/Controllers/Masters/Reaudit.php`.
- **Services**: `app/Services/ClientSiteDependencySyncService.php` handles propagating changes across tables.
- **Helpers**: `app/Helpers/designation_acl_helper.php` handles ACL logic (like `getOEAuditACLWhere`, `getClusterManagerAssignedCluster`).
- **Database Tables**:
  - `alert_client` (OE master), `alert_hse_client_master` (HSE master)
  - `alert_location_master`
  - `alert_final_structured_audit`, `alert_hse_audit_master`, `alert_gemba_audits`

# 2 & 3. OE and HSE Client Master Dependency Analysis
- When a client is created/updated in **OE Client Master** (`Client.php`), `ClientSiteDependencySyncService->syncOeClientChanges` is called. This syncs `location` and `region` to dependent tables like `alert_final_structured_audit`, but it **does not** sync `cluster_name` or `client_manager_name`.
- When a client is updated in **HSE Client Master** (`Hse_client.php`), `syncHseClientChanges` is called. It syncs `location` and `region` to `alert_hse_audit_master` and `alert_gemba_audits`, but leaves old manager assignments as they were.

# 4. Current Database Tables and their Relationships
- `alert_client.cluster` and `alert_client.account_manager` store the current managers.
- `alert_final_structured_audit.cluster_name` and `alert_final_structured_audit.client_manager_name` store static snapshots from when the audit was created.

# 5. Root Cause of the Reassignment and Missing-Data Problem
The issue arises from a disconnect between where current assignments are stored versus how visibility (ACL) is enforced:
1. **Lost History**: Overwriting the `cluster` or `account_manager` in `alert_client` permanently erases who managed the site previously, as no dedicated assignment history table exists.
2. **Missing Data Visibility**: In files like `Oe_nc_tracker.php` (`table_ajax`), the ACL filter dynamically checks if the **current logged-in Cluster Manager's username** matches `alert_final_structured_audit.cluster_name`. Because `alert_final_structured_audit` retains the *old* manager's name (it is never updated by the sync service to preserve audit integrity), the new manager's name does not match the old records. Consequently, the new manager cannot see the site's existing NCs.

# 6. Proposed Assignment-History Design
Create two separate tables to maintain strict boundaries between OE and HSE workflows:
1. `alert_oe_site_assignment_history`
2. `alert_hse_site_assignment_history`

**Columns for both tables:**
- `id` (Primary Key, Auto Increment)
- `client_id` (Foreign Key to respective master table)
- `site_name` / `location`
- `old_cluster` & `new_cluster` (Storing Manager Names)
- `old_account_manager` & `new_account_manager` (Storing Manager Names)
- `changed_by` (User ID)
- `changed_by_name` (User Name)
- `changed_at` (Datetime)

When `Client.php` or `Hse_client.php` receives an update, it will compare the old record with the new submission. If `cluster` or `account_manager` differs, it will insert a snapshot into the respective history table before updating the master record.

# 7. Proposed Last-Performed Audit and Re-Audit Behavior
- **Re-Audit Creation**: Re-audits will continue to be user-initiated (e.g., from the Reaudit tracker). Manager reassignment **will not** automatically trigger or create new audits.
- **Data Integrity**: Previous audits (`alert_final_structured_audit`, `alert_hse_audit_master`) will keep their original `cluster_name` and `account_manager` snapshots intact. We will not overwrite history.
- **Visibility**: The new manager will gain access to these old audits and NCs by fixing the ACL queries (see step 8), allowing them to view history and initiate re-audits seamlessly. 

# 8. ACL and Data-Visibility Changes Required
Modify the queries in `Oe_nc_tracker.php`, `Hse_nc_tracker.php`, and `Reaudit.php` to rely on the current site assignment rather than the historical snapshot.
- For example, in `Oe_nc_tracker.php`'s `table_ajax`, replace `alert_final_structured_audit.cluster_name IN (...)` with a subquery or join against `alert_client.cluster`, similar to what is already done in `getOEAuditACLWhere()` in `designation_acl_helper.php`.
- Ensure AM ACL relies on `alert_client.account_manager`.

# 9. Exact SQL Queries for Proposed Schema Changes
```sql
CREATE TABLE IF NOT EXISTS alert_oe_site_assignment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    site_name VARCHAR(255) NULL,
    old_cluster VARCHAR(255) NULL,
    new_cluster VARCHAR(255) NULL,
    old_account_manager VARCHAR(255) NULL,
    new_account_manager VARCHAR(255) NULL,
    changed_by INT NULL,
    changed_by_name VARCHAR(255) NULL,
    changed_at DATETIME NOT NULL,
    INDEX (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS alert_hse_site_assignment_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT NOT NULL,
    site_name VARCHAR(255) NULL,
    old_cluster VARCHAR(255) NULL,
    new_cluster VARCHAR(255) NULL,
    old_account_manager VARCHAR(255) NULL,
    new_account_manager VARCHAR(255) NULL,
    changed_by INT NULL,
    changed_by_name VARCHAR(255) NULL,
    changed_at DATETIME NOT NULL,
    INDEX (client_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

# 10. Potential Risks and Safeguards for Unrelated Modules
- **Risk**: Changing ACL logic in `Oe_nc_tracker` might expose NCs to unintended managers if the join on `alert_client` is not strict.
- **Safeguard**: Ensure the join matches on `client_name` or `location` AND validates `status = 1` (Active). We will use existing helper functions where possible to avoid scattered raw SQL.
- **Risk**: Storing strings for managers instead of IDs.
- **Safeguard**: The current system architecture relies heavily on manager usernames as identifiers. We will persist this convention to ensure backward compatibility and prevent breaking existing dropdowns.

# 11. Step-by-Step Implementation Plan
1. **Database Migration**: Execute the `CREATE TABLE` queries to set up the two history tables.
2. **Update Controllers**: Modify `Client.php` and `Hse_client.php` `save_details` functions to check for assignment differences against `$oldData` and insert into the history tables before saving.
3. **Update OE NC Tracker ACL**: Modify `table_ajax` in `Oe_nc_tracker.php` to filter by current `alert_client.cluster` instead of `alert_final_structured_audit.cluster_name`.
4. **Update HSE NC Tracker ACL**: Modify `table_ajax` in `Hse_nc_tracker.php` / `GembaNcTracker.php` to similarly use `alert_hse_client_master` for current assignments.
5. **Update Reaudit ACL**: Ensure `Reaudit.php` `table_ajax` correctly shows historical audits to newly assigned managers.
6. **Verify Dashboards (Optional)**: If any dashboard counts are off, update their `WHERE EXISTS` clauses to match the new ACL logic.

# 12. Testing Plan
1. **Reassignment**: Change the Cluster Manager for an existing OE site. Verify that a record is correctly inserted into `alert_oe_site_assignment_history`.
2. **Data Preservation**: Verify that `alert_final_structured_audit` retains the old manager's name for past audits.
3. **NC Visibility (New Manager)**: Log in as the new Cluster Manager. Verify that the old NCs for the reassigned site appear in their NC Tracker.
4. **NC Visibility (Old Manager)**: Log in as the old Cluster Manager. Verify that the site's NCs are no longer visible in their tracker (unless they are assigned to it via another path).
5. **Re-Audits**: Log in as the new manager, navigate to Reaudit, and verify that the past audit is accessible and a new re-audit can be initiated.
