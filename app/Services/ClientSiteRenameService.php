<?php

namespace App\Services;

use Config\Database;

class ClientSiteRenameService
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->ensureSchema();
    }

    /**
     * Ensure only the 3 centralized history tables exist: OE_history, HSE_history, Gemba_history.
     */
    private function ensureSchema()
    {
        if (!$this->db->tableExists('OE_history')) {
            $sql = "CREATE TABLE IF NOT EXISTS `OE_history` (
                `history_id` INT AUTO_INCREMENT PRIMARY KEY,
                `client_id` INT NOT NULL,
                `site_id` INT NULL,
                `change_type` VARCHAR(100) NOT NULL COMMENT 'RENAME | ALLOCATION | DETAILS_UPDATE',
                `remark` VARCHAR(255) NOT NULL COMMENT 'Site Name Changed | Cluster Manager Changed | Account Manager Changed | etc.',
                `reason` TEXT NULL COMMENT 'User-provided justification/remarks',
                `prev_client_name` VARCHAR(255) NOT NULL,
                `prev_location` VARCHAR(255) NULL,
                `prev_region` VARCHAR(100) NULL,
                `prev_cluster` VARCHAR(100) NULL,
                `prev_account_manager` VARCHAR(100) NULL,
                `prev_email` VARCHAR(255) NULL,
                `prev_status` INT NULL,
                `new_client_name` VARCHAR(255) NOT NULL,
                `new_location` VARCHAR(255) NULL,
                `new_region` VARCHAR(100) NULL,
                `new_cluster` VARCHAR(100) NULL,
                `new_account_manager` VARCHAR(100) NULL,
                `new_email` VARCHAR(255) NULL,
                `new_status` INT NULL,
                `affected_fields` VARCHAR(255) NOT NULL COMMENT 'Comma-separated list of fields changed',
                `affected_audit_count` INT DEFAULT 0,
                `affected_nc_count` INT DEFAULT 0,
                `affected_audit_ids` TEXT NULL,
                `changed_by_user_id` INT NULL,
                `changed_by_user_name` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_oe_client_id` (`client_id`),
                INDEX `idx_oe_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->query($sql);
        }

        if (!$this->db->tableExists('HSE_history')) {
            $sql = "CREATE TABLE IF NOT EXISTS `HSE_history` (
                `history_id` INT AUTO_INCREMENT PRIMARY KEY,
                `client_id` INT NOT NULL,
                `site_id` INT NULL,
                `change_type` VARCHAR(100) NOT NULL COMMENT 'RENAME | CATEGORY_CHANGE | ALLOCATION | DETAILS_UPDATE',
                `remark` VARCHAR(255) NOT NULL COMMENT 'Site Name Changed | Site Category Changed | Cluster Manager Changed | Account Manager Changed | etc.',
                `reason` TEXT NULL COMMENT 'User-provided justification/remarks',
                `prev_client_name` VARCHAR(255) NOT NULL,
                `prev_category` VARCHAR(255) NULL,
                `prev_sub_category` VARCHAR(255) NULL,
                `prev_location` VARCHAR(255) NULL,
                `prev_region` VARCHAR(100) NULL,
                `prev_cluster` VARCHAR(100) NULL,
                `prev_account_manager` VARCHAR(100) NULL,
                `prev_email` VARCHAR(255) NULL,
                `prev_status` INT NULL,
                `new_client_name` VARCHAR(255) NOT NULL,
                `new_category` VARCHAR(255) NULL,
                `new_sub_category` VARCHAR(255) NULL,
                `new_location` VARCHAR(255) NULL,
                `new_region` VARCHAR(100) NULL,
                `new_cluster` VARCHAR(100) NULL,
                `new_account_manager` VARCHAR(100) NULL,
                `new_email` VARCHAR(255) NULL,
                `new_status` INT NULL,
                `affected_fields` VARCHAR(255) NOT NULL,
                `affected_audit_count` INT DEFAULT 0,
                `affected_nc_count` INT DEFAULT 0,
                `affected_audit_ids` TEXT NULL,
                `changed_by_user_id` INT NULL,
                `changed_by_user_name` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_hse_client_id` (`client_id`),
                INDEX `idx_hse_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->query($sql);
        }

        if (!$this->db->tableExists('Gemba_history')) {
            $sql = "CREATE TABLE IF NOT EXISTS `Gemba_history` (
                `history_id` INT AUTO_INCREMENT PRIMARY KEY,
                `client_id` INT NULL COMMENT 'HSE Client ID if linked',
                `site_id` INT NOT NULL COMMENT 'alert_gemba_sites.id',
                `change_type` VARCHAR(100) NOT NULL COMMENT 'RENAME | CATEGORY_CHANGE | ALLOCATION | DETAILS_UPDATE',
                `remark` VARCHAR(255) NOT NULL COMMENT 'Site Name Changed | Site Category Changed | Cluster Manager Changed | Account Manager Changed | etc.',
                `reason` TEXT NULL COMMENT 'User-provided justification/remarks',
                `prev_site_name` VARCHAR(255) NOT NULL,
                `prev_site_category` VARCHAR(100) NULL,
                `prev_site_type_1` VARCHAR(100) NULL,
                `prev_site_type_2` VARCHAR(100) NULL,
                `prev_region` VARCHAR(100) NULL,
                `prev_cluster` VARCHAR(100) NULL,
                `prev_account_manager` VARCHAR(100) NULL,
                `prev_status` INT NULL,
                `new_site_name` VARCHAR(255) NOT NULL,
                `new_site_category` VARCHAR(100) NULL,
                `new_site_type_1` VARCHAR(100) NULL,
                `new_site_type_2` VARCHAR(100) NULL,
                `new_region` VARCHAR(100) NULL,
                `new_cluster` VARCHAR(100) NULL,
                `new_account_manager` VARCHAR(100) NULL,
                `new_status` INT NULL,
                `affected_fields` VARCHAR(255) NOT NULL,
                `affected_gemba_ids` TEXT NULL,
                `changed_by_user_id` INT NULL,
                `changed_by_user_name` VARCHAR(255) NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                INDEX `idx_gemba_site_id` (`site_id`),
                INDEX `idx_gemba_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->query($sql);
        }

        if (!$this->db->tableExists('OE_audit_backup')) {
            $sql = "CREATE TABLE IF NOT EXISTS `OE_audit_backup` (
                `backup_id` INT AUTO_INCREMENT PRIMARY KEY,
                `history_id` INT NULL,
                `backed_up_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `structured_audit_id` int(11) NOT NULL,
                `audit_no` text NOT NULL,
                `audit_name` text NOT NULL,
                `reaudit` int(11) NOT NULL,
                `auditor_name` text NOT NULL,
                `auditee_name` text NOT NULL,
                `region` text NOT NULL,
                `cluster_name` varchar(100) NOT NULL,
                `client_manager_name` varchar(100) NOT NULL,
                `audit_date` date NOT NULL,
                `completion_date` date NOT NULL,
                `next_date` date NOT NULL,
                `report_date` date NOT NULL,
                `client_name` text NOT NULL,
                `audit_note` text NOT NULL,
                `location` varchar(100) NOT NULL,
                `zone` text NOT NULL,
                `audit_score` float NOT NULL,
                `audit_revision_no` text NOT NULL,
                `final_remark` text NOT NULL,
                `audit_template_id` int(11) NOT NULL,
                `audit_by_user_id` int(11) NOT NULL,
                `action_by_user_id` int(11) NOT NULL,
                `status` int(1) NOT NULL,
                `default_date` timestamp NOT NULL DEFAULT current_timestamp(),
                `update_date` datetime NOT NULL,
                INDEX `idx_oe_backup_hist` (`history_id`),
                INDEX `idx_oe_backup_audit` (`structured_audit_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $this->db->query($sql);
        }

        if (!$this->db->tableExists('HSE_audit_backup')) {
            $sql = "CREATE TABLE IF NOT EXISTS `HSE_audit_backup` (
                `backup_id` INT AUTO_INCREMENT PRIMARY KEY,
                `history_id` INT NULL,
                `backed_up_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `hse_audit_id` int(11) NOT NULL,
                `audit_no` text NOT NULL,
                `audit_name` text NOT NULL,
                `audit_template_id` int(11) NOT NULL,
                `auditor_name` text NOT NULL,
                `auditee_name` text NOT NULL,
                `client_name` text NOT NULL,
                `audit_date` text NOT NULL,
                `template_date` text NOT NULL,
                `next_date` date NOT NULL,
                `report_date` date NOT NULL,
                `region` text NOT NULL,
                `location` text NOT NULL,
                `score` decimal(10,2) DEFAULT NULL,
                `perform_audit_by` text NOT NULL,
                `main_category` varchar(225) DEFAULT NULL,
                `sub_category` varchar(225) DEFAULT NULL,
                `cluster_name` text NOT NULL,
                `account_manager` text NOT NULL,
                `status` int(11) NOT NULL,
                `default_date` timestamp NOT NULL DEFAULT current_timestamp(),
                `update_date` datetime NOT NULL,
                INDEX `idx_hse_backup_hist` (`history_id`),
                INDEX `idx_hse_backup_audit` (`hse_audit_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $this->db->query($sql);
        }

        if (!$this->db->tableExists('Gemba_audit_backup')) {
            $sql = "CREATE TABLE IF NOT EXISTS `Gemba_audit_backup` (
                `backup_id` INT AUTO_INCREMENT PRIMARY KEY,
                `history_id` INT NULL,
                `backed_up_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `gemba_sr_no` int(10) unsigned NOT NULL,
                `unique_no` varchar(20) DEFAULT NULL,
                `region` varchar(50) NOT NULL DEFAULT 'South',
                `auditor_name` varchar(100) NOT NULL,
                `audit_category` varchar(100) NOT NULL,
                `audit_type` varchar(100) NOT NULL,
                `audit_doc_type` varchar(100) NOT NULL,
                `site_name` varchar(200) NOT NULL,
                `site_type_1` varchar(50) NOT NULL,
                `site_type_2` varchar(50) NOT NULL,
                `site_category` varchar(50) NOT NULL DEFAULT 'Category-1',
                `audit_mail_received_date` date DEFAULT NULL,
                `audit_report_date` date NOT NULL,
                `weeknum_raised` tinyint(3) unsigned DEFAULT NULL,
                `month_raised` date DEFAULT NULL,
                `year_month_raised` varchar(10) DEFAULT NULL,
                `year_raised` smallint(5) unsigned DEFAULT NULL,
                `checklist_category` varchar(100) NOT NULL,
                `nc_recommendation` varchar(20) NOT NULL,
                `qhse_remarks` text DEFAULT NULL,
                `observation_point` text NOT NULL,
                `risks_details` text DEFAULT NULL,
                `action_recommendation` text DEFAULT NULL,
                `specifications` text DEFAULT NULL,
                `ua_uc_type` varchar(50) NOT NULL DEFAULT 'UA-UC',
                `risk_severity` varchar(20) NOT NULL,
                `risk_probability` varchar(20) NOT NULL,
                `color_code` varchar(20) NOT NULL,
                `cost_type` varchar(20) NOT NULL,
                `remarks_wm` text DEFAULT NULL,
                `whose_scope` varchar(100) DEFAULT NULL,
                `remarks_corporate` text DEFAULT NULL,
                `ageing_days` int(10) unsigned DEFAULT NULL,
                `age_bracket` varchar(20) DEFAULT NULL,
                `target_date` date DEFAULT NULL,
                `closed_date` date DEFAULT NULL,
                `point_status` varchar(50) NOT NULL DEFAULT 'Open',
                `point_category` varchar(50) NOT NULL DEFAULT 'Open Category',
                `closure_status` varchar(50) NOT NULL DEFAULT 'Delayed',
                `weeknum_closed` tinyint(3) unsigned DEFAULT NULL,
                `month_closed` date DEFAULT NULL,
                `year_month_closed` varchar(10) DEFAULT NULL,
                `risk_severity_rating` tinyint(3) unsigned NOT NULL DEFAULT 1,
                `risk_probability_rating` tinyint(3) unsigned NOT NULL DEFAULT 1,
                `combined_risk_rating` smallint(5) unsigned NOT NULL DEFAULT 1,
                `times_repeated` tinyint(3) unsigned NOT NULL DEFAULT 1,
                `combined_risk_freq` smallint(5) unsigned NOT NULL DEFAULT 1,
                `final_rating` int(10) unsigned NOT NULL DEFAULT 1,
                `followup_by` varchar(100) DEFAULT 'NA',
                `weeknum_yearmonth_raised` varchar(20) DEFAULT NULL,
                `weeknum_yearmonth_closed` varchar(100) DEFAULT NULL,
                `cluster_manager_spoc` varchar(255) DEFAULT NULL,
                `account_manager` varchar(255) DEFAULT NULL,
                `created_by` varchar(255) DEFAULT NULL,
                `updated_by` varchar(255) DEFAULT NULL,
                `status` int(11) NOT NULL DEFAULT 0,
                `default_date` timestamp NULL DEFAULT current_timestamp(),
                `update_date` datetime DEFAULT current_timestamp(),
                `hse_audit_id` int(11) DEFAULT NULL,
                `hse_detail_id` int(11) DEFAULT NULL,
                `source_module` varchar(50) DEFAULT 'manual',
                `nc_status` int(11) NOT NULL DEFAULT 0,
                `nc_remark` text DEFAULT NULL,
                `working_remarks` text DEFAULT NULL,
                `nc_worked_by` text DEFAULT NULL,
                `working_date` datetime DEFAULT NULL,
                `nc_after_photo` text DEFAULT NULL,
                `working_uploaded_file` varchar(500) DEFAULT NULL,
                `nc_cluster_reviewed_by` text DEFAULT NULL,
                `nc_cluster_reviewed_date` datetime DEFAULT NULL,
                `nc_auditor_reviewed_by` text DEFAULT NULL,
                `nc_auditor_reviewed_date` datetime DEFAULT NULL,
                `nc_closed_by` text DEFAULT NULL,
                `nc_closed_date` date DEFAULT NULL,
                `closed_remarks` text DEFAULT NULL,
                `closed_uploaded_file` varchar(500) DEFAULT NULL,
                `nc_rejected_by` text DEFAULT NULL,
                `nc_rejected_date` datetime DEFAULT NULL,
                `rejection_reason` text DEFAULT NULL,
                `action_category` varchar(255) DEFAULT NULL,
                INDEX `idx_gemba_backup_hist` (`history_id`),
                INDEX `idx_gemba_backup_audit` (`gemba_sr_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
            $this->db->query($sql);
        }
    }

    /**
     * 1. Rename an OE Client (alert_client) and update ONLY OE-dependent tables.
     * Records full pre-change state and affected audit IDs into OE_history.
     *
     * Affected tables:
     * - alert_client
     * - alert_final_structured_audit (OE Audit Master)
     * - alert_final_structured_audit_details (OE NC details)
     * - alert_normal_audit (Normal Audit Master)
     * - alert_normal_audit_details (Normal NC details)
     * - alert_location_master (where audit_type = 'OE' or matching location_name)
     *
     * EXCLUDED: HSE Client, HSE Audits, Gemba Sites, Gemba Audits.
     */
    public function renameOeClient(string $oldName, string $newName, int $userId = null, string $userName = null, int $clientId = null, string $remarks = null): array
    {
        $oldName = trim($oldName);
        $newName = trim($newName);

        if (empty($oldName) || empty($newName)) {
            return ['status' => 0, 'message' => 'Old OE client name and new name cannot be empty.'];
        }

        if ($oldName === $newName) {
            return ['status' => 1, 'message' => 'No name change requested.', 'affected_records' => 0];
        }

        if (strlen($newName) < 3) {
            return ['status' => 0, 'message' => 'New OE client name must be at least 3 characters long.'];
        }

        // Resolve client_id if not supplied
        if (empty($clientId) && $this->db->tableExists('alert_client')) {
            $clientRec = $this->db->table('alert_client')
                ->where('TRIM(client_name)', $oldName)
                ->where('status !=', 2)
                ->get()
                ->getRowArray();
            if ($clientRec && !empty($clientRec['client_id'])) {
                $clientId = (int)$clientRec['client_id'];
            }
        }

        // Check if new name already exists in active OE Client Master under a different client_id
        $dupQuery = $this->db->table('alert_client')
            ->where('TRIM(client_name)', $newName)
            ->where('status !=', 2);
        if (!empty($clientId)) {
            $dupQuery->where('client_id !=', $clientId);
        } else {
            $dupQuery->where('TRIM(client_name) !=', $oldName);
        }
        $existing = $dupQuery->countAllResults();

        if ($existing > 0) {
            return ['status' => 0, 'message' => "An active OE Client with the name '{$newName}' already exists."];
        }

        // Resolve site_id from alert_location_master if exists
        $siteId = null;
        if ($this->db->tableExists('alert_location_master')) {
            $locRow = $this->db->table('alert_location_master')
                ->where('TRIM(location_name)', $oldName)
                ->get()
                ->getRowArray();
            if ($locRow && !empty($locRow['location_id'])) {
                $siteId = (int)$locRow['location_id'];
            }
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Fetch OE audits before modification for history logging
        $oeAudits = [];
        $oeAuditIds = [];
        if ($this->db->tableExists('alert_final_structured_audit')) {
            $oldNameEscaped = $this->db->escape($oldName);
            $oeAudits = $this->db->query("
                SELECT * FROM alert_final_structured_audit
                WHERE TRIM(location) = TRIM({$oldNameEscaped}) OR TRIM(client_name) = TRIM({$oldNameEscaped})
            ")->getResultArray();
            $oeAuditIds = array_column($oeAudits, 'structured_audit_id');
        }

        // Fetch normal audits before modification
        $normalAudits = [];
        if ($this->db->tableExists('alert_normal_audit')) {
            $oldNameEscaped = $this->db->escape($oldName);
            $normalAudits = $this->db->query("
                SELECT * FROM alert_normal_audit
                WHERE TRIM(location) = TRIM({$oldNameEscaped}) OR TRIM(client_name) = TRIM({$oldNameEscaped})
            ")->getResultArray();
        }

        // Count NCs
        $ncCount = 0;
        if (!empty($oeAuditIds) && $this->db->tableExists('alert_final_structured_audit_details')) {
            $ncCount = $this->db->table('alert_final_structured_audit_details')
                ->whereIn('structured_audit_id', $oeAuditIds)
                ->where('audit_finding', 'NO')
                ->countAllResults();
        }

        // 2. Fetch full current OE client record for history snapshot
        $currentOeRec = null;
        if (!empty($clientId) && $this->db->tableExists('alert_client')) {
            $currentOeRec = $this->db->table('alert_client')->where('client_id', $clientId)->get()->getRowArray();
        }
        if (!$currentOeRec && $this->db->tableExists('alert_client')) {
            $currentOeRec = $this->db->table('alert_client')->where('TRIM(client_name)', $oldName)->get()->getRowArray();
        }

        // 3. Insert master change entry into OE_history
        $historyData = [
            'client_id'            => $clientId ?? ($currentOeRec['client_id'] ?? 0),
            'site_id'              => $siteId,
            'change_type'          => 'RENAME',
            'remark'               => 'Site Name Changed',
            'reason'               => $remarks,
            'prev_client_name'     => $oldName,
            'prev_location'        => $currentOeRec['location'] ?? null,
            'prev_region'          => $currentOeRec['region'] ?? null,
            'prev_cluster'         => $currentOeRec['cluster'] ?? null,
            'prev_account_manager' => $currentOeRec['account_manager'] ?? null,
            'prev_email'           => $currentOeRec['email'] ?? null,
            'prev_status'          => isset($currentOeRec['status']) ? (int)$currentOeRec['status'] : null,
            'new_client_name'      => $newName,
            'new_location'         => $currentOeRec['location'] ?? null,
            'new_region'           => $currentOeRec['region'] ?? null,
            'new_cluster'          => $currentOeRec['cluster'] ?? null,
            'new_account_manager'  => $currentOeRec['account_manager'] ?? null,
            'new_email'            => $currentOeRec['email'] ?? null,
            'new_status'           => isset($currentOeRec['status']) ? (int)$currentOeRec['status'] : null,
            'affected_fields'      => 'client_name',
            'affected_audit_count' => count($oeAudits) + count($normalAudits),
            'affected_nc_count'    => $ncCount,
            'affected_audit_ids'   => !empty($oeAuditIds) ? json_encode($oeAuditIds) : null,
            'changed_by_user_id'   => $userId,
            'changed_by_user_name' => $userName,
            'created_at'           => date('Y-m-d H:i:s')
        ];
        $this->db->table('OE_history')->insert($historyData);
        $renameHistoryId = $this->db->insertID();

        // 3b. Dump full OE audit records as-is into OE_audit_backup for direct export/restore
        if (!empty($oeAudits) && $this->db->tableExists('OE_audit_backup')) {
            $now = date('Y-m-d H:i:s');
            foreach ($oeAudits as $oeRow) {
                $backupRow = $oeRow;
                $backupRow['history_id'] = $renameHistoryId;
                $backupRow['backed_up_at'] = $now;
                unset($backupRow['backup_id']);
                $this->db->table('OE_audit_backup')->insert($backupRow);
            }
        }

        // 4. Update OE Client Master (alert_client) by stable client_id (or oldName fallback)
        if ($this->db->tableExists('alert_client')) {
            $clBuilder = $this->db->table('alert_client');
            if (!empty($clientId)) {
                $clBuilder->where('client_id', $clientId);
            } else {
                $clBuilder->where('TRIM(client_name)', $oldName);
            }
            $clBuilder->update(['client_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_client'] = $cnt;
            $totalAffected += $cnt;
        }

        // 5. Update OE Audit Master (alert_final_structured_audit)
        if ($this->db->tableExists('alert_final_structured_audit')) {
            $cntAud = 0;
            $this->db->table('alert_final_structured_audit')
                ->where('TRIM(client_name)', $oldName)
                ->update(['client_name' => $newName]);
            $cntAud += $this->db->affectedRows();

            $this->db->table('alert_final_structured_audit')
                ->where('TRIM(location)', $oldName)
                ->update(['location' => $newName]);
            $cntAud += $this->db->affectedRows();

            $affectedCounts['alert_final_structured_audit'] = $cntAud;
            $totalAffected += $cntAud;
        }

        // 6. Update OE Audit Details / NCs (alert_final_structured_audit_details)
        if ($this->db->tableExists('alert_final_structured_audit_details')) {
            $this->db->table('alert_final_structured_audit_details')
                ->where('TRIM(location)', $oldName)
                ->update(['location' => $newName]);
            $cntDtl = $this->db->affectedRows();
            $affectedCounts['alert_final_structured_audit_details'] = $cntDtl;
            $totalAffected += $cntDtl;
        }

        // 7. Update Normal Audit Master (alert_normal_audit)
        if ($this->db->tableExists('alert_normal_audit')) {
            $cntNorm = 0;
            $this->db->table('alert_normal_audit')
                ->where('TRIM(client_name)', $oldName)
                ->update(['client_name' => $newName]);
            $cntNorm += $this->db->affectedRows();

            $this->db->table('alert_normal_audit')
                ->where('TRIM(location)', $oldName)
                ->update(['location' => $newName]);
            $cntNorm += $this->db->affectedRows();

            $affectedCounts['alert_normal_audit'] = $cntNorm;
            $totalAffected += $cntNorm;
        }

        // 8. Update Normal Audit Details (alert_normal_audit_details)
        if ($this->db->tableExists('alert_normal_audit_details')) {
            $this->db->table('alert_normal_audit_details')
                ->where('TRIM(location)', $oldName)
                ->update(['location' => $newName]);
            $cntNormDtl = $this->db->affectedRows();
            $affectedCounts['alert_normal_audit_details'] = $cntNormDtl;
            $totalAffected += $cntNormDtl;
        }

        // 9. Update Location Master (alert_location_master - OE scope)
        if ($this->db->tableExists('alert_location_master')) {
            $this->db->table('alert_location_master')
                ->where('TRIM(location_name)', $oldName)
                ->groupStart()
                    ->where('audit_type', 'OE')
                    ->orWhere('audit_type', 'OE and HSE')
                    ->orWhere('audit_type IS NULL')
                    ->orWhere('audit_type', '')
                ->groupEnd()
                ->update(['location_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_location_master'] = $cnt;
            $totalAffected += $cnt;
        }

        // Audit trail is safely and centrally recorded in OE_history above

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteRenameService: Rename OE Client from '{$oldName}' to '{$newName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed while renaming OE client. Changes rolled back.'];
        }

        log_message('info', "ClientSiteRenameService: Renamed OE Client '{$oldName}' to '{$newName}'. Affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "OE Client renamed successfully from '{$oldName}' to '{$newName}'. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts,
            'history_id' => $renameHistoryId
        ];
    }

    /**
     * 2. Rename an HSE Client (alert_hse_client_master) and update ONLY HSE-dependent tables.
     * Records full pre-change state and affected audit IDs into HSE_history and Gemba_history.
     *
     * Affected tables:
     * - alert_hse_client_master
     * - alert_hse_audit_master (HSE Audit Master)
     * - alert_gemba_audits (synced HSE audit NCs and gemba observations)
     * - alert_gemba_sites
     * - alert_client_deactivation_logs
     * - alert_location_master (where audit_type = 'HSE' or matching location_name)
     *
     * EXCLUDED: OE Client (alert_client), OE Audits (alert_final_structured_audit, alert_normal_audit).
     */
    public function renameHseClient(string $oldName, string $newName, int $userId = null, string $userName = null, int $clientId = null, string $remarks = null): array
    {
        $oldName = trim($oldName);
        $newName = trim($newName);

        if (empty($oldName) || empty($newName)) {
            return ['status' => 0, 'message' => 'Old HSE client name and new name cannot be empty.'];
        }

        if ($oldName === $newName) {
            return ['status' => 1, 'message' => 'No name change requested.', 'affected_records' => 0];
        }

        if (strlen($newName) < 3) {
            return ['status' => 0, 'message' => 'New HSE client name must be at least 3 characters long.'];
        }

        // Resolve client_id if not supplied
        if (empty($clientId) && $this->db->tableExists('alert_hse_client_master')) {
            $clientRec = $this->db->table('alert_hse_client_master')
                ->where('TRIM(client_name)', $oldName)
                ->where('status !=', 2)
                ->get()
                ->getRowArray();
            if ($clientRec && !empty($clientRec['client_id'])) {
                $clientId = (int)$clientRec['client_id'];
            }
        }

        // Check if new name already exists in active HSE Client Master under a different client_id
        $dupQuery = $this->db->table('alert_hse_client_master')
            ->where('TRIM(client_name)', $newName)
            ->where('status !=', 2);
        if (!empty($clientId)) {
            $dupQuery->where('client_id !=', $clientId);
        } else {
            $dupQuery->where('TRIM(client_name) !=', $oldName);
        }
        $existing = $dupQuery->countAllResults();

        if ($existing > 0) {
            return ['status' => 0, 'message' => "An active HSE Client with the name '{$newName}' already exists."];
        }

        // Resolve site_id from alert_location_master or alert_gemba_sites
        $siteId = null;
        if ($this->db->tableExists('alert_location_master')) {
            $locRow = $this->db->table('alert_location_master')
                ->where('TRIM(location_name)', $oldName)
                ->get()
                ->getRowArray();
            if ($locRow && !empty($locRow['location_id'])) {
                $siteId = (int)$locRow['location_id'];
            }
        }
        if (empty($siteId) && $this->db->tableExists('alert_gemba_sites')) {
            $siteRow = $this->db->table('alert_gemba_sites')
                ->where('TRIM(site_name)', $oldName)
                ->get()
                ->getRowArray();
            if ($siteRow && !empty($siteRow['id'])) {
                $siteId = (int)$siteRow['id'];
            }
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Fetch HSE audits before modification for history logging
        $hseAudits = [];
        $hseAuditIds = [];
        if ($this->db->tableExists('alert_hse_audit_master')) {
            $oldNameEscaped = $this->db->escape($oldName);
            $hseAudits = $this->db->query("
                SELECT * FROM alert_hse_audit_master
                WHERE TRIM(client_name) = TRIM({$oldNameEscaped}) OR TRIM(location) = TRIM({$oldNameEscaped})
            ")->getResultArray();
            $hseAuditIds = array_column($hseAudits, 'hse_audit_id');
        }

        // Count NCs
        $ncCount = 0;
        if (!empty($hseAuditIds) && $this->db->tableExists('alert_hse_audit_details')) {
            $ncCount += $this->db->table('alert_hse_audit_details')
                ->whereIn('hse_audit_id', $hseAuditIds)
                ->where('finding', 'NO')
                ->countAllResults();
        }
        if ($this->db->tableExists('alert_gemba_audits')) {
            $ncCount += $this->db->table('alert_gemba_audits')
                ->where('TRIM(site_name)', $oldName)
                ->countAllResults();
        }

        // 2. Fetch full current HSE client record for history snapshot
        $currentHseRec = null;
        if (!empty($clientId) && $this->db->tableExists('alert_hse_client_master')) {
            $currentHseRec = $this->db->table('alert_hse_client_master')->where('client_id', $clientId)->get()->getRowArray();
        }
        if (!$currentHseRec && $this->db->tableExists('alert_hse_client_master')) {
            $currentHseRec = $this->db->table('alert_hse_client_master')->where('TRIM(client_name)', $oldName)->get()->getRowArray();
        }

        // 3. Insert master change entry into HSE_history
        $historyData = [
            'client_id'            => $clientId ?? ($currentHseRec['client_id'] ?? 0),
            'site_id'              => $siteId,
            'change_type'          => 'RENAME',
            'remark'               => 'Site Name Changed',
            'reason'               => $remarks,
            'prev_client_name'     => $oldName,
            'prev_category'        => $currentHseRec['category'] ?? null,
            'prev_sub_category'    => $currentHseRec['sub_category'] ?? null,
            'prev_location'        => $currentHseRec['location'] ?? null,
            'prev_region'          => $currentHseRec['region'] ?? null,
            'prev_cluster'         => $currentHseRec['cluster'] ?? null,
            'prev_account_manager' => $currentHseRec['account_manager'] ?? null,
            'prev_email'           => $currentHseRec['email'] ?? null,
            'prev_status'          => isset($currentHseRec['status']) ? (int)$currentHseRec['status'] : null,
            'new_client_name'      => $newName,
            'new_category'         => $currentHseRec['category'] ?? null,
            'new_sub_category'     => $currentHseRec['sub_category'] ?? null,
            'new_location'         => $currentHseRec['location'] ?? null,
            'new_region'           => $currentHseRec['region'] ?? null,
            'new_cluster'          => $currentHseRec['cluster'] ?? null,
            'new_account_manager'  => $currentHseRec['account_manager'] ?? null,
            'new_email'            => $currentHseRec['email'] ?? null,
            'new_status'           => isset($currentHseRec['status']) ? (int)$currentHseRec['status'] : null,
            'affected_fields'      => 'client_name',
            'affected_audit_count' => count($hseAudits),
            'affected_nc_count'    => $ncCount,
            'affected_audit_ids'   => !empty($hseAuditIds) ? json_encode($hseAuditIds) : null,
            'changed_by_user_id'   => $userId,
            'changed_by_user_name' => $userName,
            'created_at'           => date('Y-m-d H:i:s')
        ];
        $this->db->table('HSE_history')->insert($historyData);
        $renameHistoryId = $this->db->insertID();

        // 3b. Dump full HSE audit records as-is into HSE_audit_backup for direct export/restore
        if (!empty($hseAudits) && $this->db->tableExists('HSE_audit_backup')) {
            $now = date('Y-m-d H:i:s');
            foreach ($hseAudits as $hseRow) {
                $backupRow = $hseRow;
                $backupRow['history_id'] = $renameHistoryId;
                $backupRow['backed_up_at'] = $now;
                unset($backupRow['backup_id']);
                $this->db->table('HSE_audit_backup')->insert($backupRow);
            }
        }

        // 4. Update HSE Client Master (alert_hse_client_master) by stable client_id (or oldName fallback)
        $clBuilder = $this->db->table('alert_hse_client_master');
        if (!empty($clientId)) {
            $clBuilder->where('client_id', $clientId);
        } else {
            $clBuilder->where('TRIM(client_name)', $oldName);
        }
        $clBuilder->update(['client_name' => $newName]);
        $cnt = $this->db->affectedRows();
        $affectedCounts['alert_hse_client_master'] = $cnt;
        $totalAffected += $cnt;

        // 5. Update HSE Audit Master (alert_hse_audit_master)
        if ($this->db->tableExists('alert_hse_audit_master')) {
            $cntHse = 0;
            $this->db->table('alert_hse_audit_master')
                ->where('TRIM(client_name)', $oldName)
                ->update(['client_name' => $newName]);
            $cntHse += $this->db->affectedRows();

            $this->db->table('alert_hse_audit_master')
                ->where('TRIM(location)', $oldName)
                ->update(['location' => $newName]);
            $cntHse += $this->db->affectedRows();

            $affectedCounts['alert_hse_audit_master'] = $cntHse;
            $totalAffected += $cntHse;
        }

        // 6. Update Gemba Sites Master (alert_gemba_sites) & record in Gemba_history
        if ($this->db->tableExists('alert_gemba_sites')) {
            $gembaSiteRec = $this->db->table('alert_gemba_sites')
                ->where('TRIM(site_name)', $oldName)
                ->get()
                ->getRowArray();

            $this->db->table('alert_gemba_sites')
                ->where('TRIM(site_name)', $oldName)
                ->update(['site_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_gemba_sites'] = $cnt;
            $totalAffected += $cnt;

            if ($gembaSiteRec) {
                $this->db->table('Gemba_history')->insert([
                    'client_id'            => $clientId ?? 0,
                    'site_id'              => (int)$gembaSiteRec['id'],
                    'change_type'          => 'RENAME',
                    'remark'               => 'Site Name Changed',
                    'reason'               => $remarks,
                    'prev_site_name'       => $oldName,
                    'prev_site_category'   => $gembaSiteRec['site_category'] ?? null,
                    'prev_site_type_1'     => $gembaSiteRec['site_type_1'] ?? null,
                    'prev_site_type_2'     => $gembaSiteRec['site_type_2'] ?? null,
                    'prev_region'          => $gembaSiteRec['region'] ?? null,
                    'prev_cluster'         => $currentHseRec['cluster'] ?? null,
                    'prev_account_manager' => $currentHseRec['account_manager'] ?? null,
                    'prev_status'          => isset($gembaSiteRec['status']) ? (int)$gembaSiteRec['status'] : null,
                    'new_site_name'        => $newName,
                    'new_site_category'    => $gembaSiteRec['site_category'] ?? null,
                    'new_site_type_1'      => $gembaSiteRec['site_type_1'] ?? null,
                    'new_site_type_2'      => $gembaSiteRec['site_type_2'] ?? null,
                    'new_region'           => $gembaSiteRec['region'] ?? null,
                    'new_cluster'          => $currentHseRec['cluster'] ?? null,
                    'new_account_manager'  => $currentHseRec['account_manager'] ?? null,
                    'new_status'           => isset($gembaSiteRec['status']) ? (int)$gembaSiteRec['status'] : null,
                    'affected_fields'      => 'site_name',
                    'affected_gemba_ids'   => null,
                    'changed_by_user_id'   => $userId,
                    'changed_by_user_name' => $userName,
                    'created_at'           => date('Y-m-d H:i:s')
                ]);
            }
        }

        // 7. Update Gemba Audits (synced HSE audit NCs and gemba observations)
        if ($this->db->tableExists('alert_gemba_audits')) {
            $gembaAuditRows = $this->db->table('alert_gemba_audits')
                ->where('TRIM(site_name)', $oldName)
                ->get()
                ->getResultArray();

            if (!empty($gembaAuditRows) && $this->db->tableExists('Gemba_audit_backup')) {
                $now = date('Y-m-d H:i:s');
                foreach ($gembaAuditRows as $gRow) {
                    $backupRow = $gRow;
                    $backupRow['history_id'] = $renameHistoryId;
                    $backupRow['backed_up_at'] = $now;
                    unset($backupRow['backup_id']);
                    $this->db->table('Gemba_audit_backup')->insert($backupRow);
                }
            }

            $this->db->table('alert_gemba_audits')
                ->where('TRIM(site_name)', $oldName)
                ->update(['site_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_gemba_audits'] = $cnt;
            $totalAffected += $cnt;
        }

        // 8. Update Client Deactivation Logs (alert_client_deactivation_logs)
        if ($this->db->tableExists('alert_client_deactivation_logs')) {
            $this->db->table('alert_client_deactivation_logs')
                ->where('TRIM(client_name)', $oldName)
                ->update(['client_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_client_deactivation_logs'] = $cnt;
            $totalAffected += $cnt;
        }

        // 9. Update Location Master (alert_location_master - HSE scope)
        if ($this->db->tableExists('alert_location_master')) {
            $this->db->table('alert_location_master')
                ->where('TRIM(location_name)', $oldName)
                ->groupStart()
                    ->where('audit_type', 'HSE')
                    ->orWhere('audit_type', 'OE and HSE')
                    ->orWhere('audit_type IS NULL')
                    ->orWhere('audit_type', '')
                ->groupEnd()
                ->update(['location_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_location_master'] = $cnt;
            $totalAffected += $cnt;
        }

        // Audit trail is safely and centrally recorded in HSE_history and Gemba_history above

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteRenameService: Rename HSE Client from '{$oldName}' to '{$newName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed while renaming HSE client. Changes rolled back.'];
        }

        log_message('info', "ClientSiteRenameService: Renamed HSE Client '{$oldName}' to '{$newName}'. Affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "HSE Client renamed successfully from '{$oldName}' to '{$newName}'. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts,
            'history_id' => $renameHistoryId
        ];
    }

    /**
     * 3. Rename a Gemba Site (alert_gemba_sites) and update ONLY Gemba-dependent tables.
     *
     * Affected tables:
     * - alert_gemba_sites
     * - alert_gemba_audits
     *
     * EXCLUDED: OE Client (alert_client), OE Audits (alert_final_structured_audit, alert_normal_audit).
     *
     * @param string $oldName
     * @param string $newName
     * @param int|null $userId
     * @param string|null $userName
     * @param int|null $clientId
     * @param string|null $remarks
     * @return array ['status' => int, 'message' => string, 'affected_records' => int]
     */
    public function renameGembaSite(string $oldName, string $newName, int $userId = null, string $userName = null, int $clientId = null, string $remarks = null): array
    {
        $oldName = trim($oldName);
        $newName = trim($newName);

        if (empty($oldName) || empty($newName)) {
            return ['status' => 0, 'message' => 'Old site name and new site name cannot be empty.'];
        }

        if ($oldName === $newName) {
            return ['status' => 1, 'message' => 'No name change requested.', 'affected_records' => 0];
        }

        $this->db->transStart();

        $affectedCounts = [];
        $totalAffected = 0;

        // 1. Fetch current Gemba site record before rename
        $gembaSiteRec = null;
        if ($this->db->tableExists('alert_gemba_sites')) {
            $gembaSiteRec = $this->db->table('alert_gemba_sites')
                ->where('site_name', $oldName)
                ->get()
                ->getRowArray();
        }

        // 2. Update Gemba Sites Master
        if ($this->db->tableExists('alert_gemba_sites')) {
            $this->db->table('alert_gemba_sites')
                ->where('site_name', $oldName)
                ->update(['site_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_gemba_sites'] = $cnt;
            $totalAffected += $cnt;
        }

        // 3. Fetch Gemba Audits for backup
        $gembaAudits = [];
        $affectedGembaAuditIds = [];
        if ($this->db->tableExists('alert_gemba_audits')) {
            $oldNameEscaped = $this->db->escape($oldName);
            $gembaAudits = $this->db->query("
                SELECT * FROM alert_gemba_audits
                WHERE TRIM(site_name) = TRIM({$oldNameEscaped})
            ")->getResultArray();
            $affectedGembaAuditIds = array_column($gembaAudits, 'gemba_sr_no');
        }

        // 4. Record into centralized Gemba_history
        $gembaHistoryId = null;
        if ($this->db->tableExists('Gemba_history') && $gembaSiteRec) {
            $this->db->table('Gemba_history')->insert([
                'client_id'            => $clientId ?: ($gembaSiteRec['client_id'] ?? null),
                'site_id'              => $gembaSiteRec['id'] ?? 0,
                'change_type'          => 'RENAME',
                'remark'               => 'Site Name Changed',
                'reason'               => $remarks ?: 'Direct Gemba site rename',
                'prev_site_name'       => $oldName,
                'prev_site_category'   => $gembaSiteRec['site_category'] ?? null,
                'prev_site_type_1'     => $gembaSiteRec['site_type_1'] ?? null,
                'prev_site_type_2'     => $gembaSiteRec['site_type_2'] ?? null,
                'prev_region'          => $gembaSiteRec['region'] ?? null,
                'prev_cluster'         => null,
                'prev_account_manager' => null,
                'prev_status'          => isset($gembaSiteRec['status']) ? (int)$gembaSiteRec['status'] : null,
                'new_site_name'        => $newName,
                'new_site_category'    => $gembaSiteRec['site_category'] ?? null,
                'new_site_type_1'      => $gembaSiteRec['site_type_1'] ?? null,
                'new_site_type_2'      => $gembaSiteRec['site_type_2'] ?? null,
                'new_region'           => $gembaSiteRec['region'] ?? null,
                'new_cluster'          => null,
                'new_account_manager'  => null,
                'new_status'           => isset($gembaSiteRec['status']) ? (int)$gembaSiteRec['status'] : null,
                'affected_fields'      => 'site_name',
                'affected_gemba_ids'   => !empty($affectedGembaAuditIds) ? json_encode($affectedGembaAuditIds) : null,
                'changed_by_user_id'   => $userId,
                'changed_by_user_name' => $userName,
                'created_at'           => date('Y-m-d H:i:s')
            ]);
            $gembaHistoryId = $this->db->insertID();
        }

        // 5. Dump full Gemba audit records as-is into Gemba_audit_backup for direct export/restore
        if (!empty($gembaAudits) && $this->db->tableExists('Gemba_audit_backup')) {
            $now = date('Y-m-d H:i:s');
            foreach ($gembaAudits as $gRow) {
                $backupRow = $gRow;
                $backupRow['history_id'] = $gembaHistoryId;
                $backupRow['backed_up_at'] = $now;
                unset($backupRow['backup_id']);
                $this->db->table('Gemba_audit_backup')->insert($backupRow);
            }
        }

        // 6. Update Gemba Audits
        if ($this->db->tableExists('alert_gemba_audits')) {
            $this->db->table('alert_gemba_audits')
                ->where('site_name', $oldName)
                ->update(['site_name' => $newName]);
            $cnt = $this->db->affectedRows();
            $affectedCounts['alert_gemba_audits'] = $cnt;
            $totalAffected += $cnt;
        }

        $this->db->transComplete();

        if ($this->db->transStatus() === FALSE) {
            log_message('error', "ClientSiteRenameService: Rename Gemba Site from '{$oldName}' to '{$newName}' failed.");
            return ['status' => 0, 'message' => 'Database transaction failed while renaming Gemba site. Changes rolled back.'];
        }

        log_message('info', "ClientSiteRenameService: Renamed Gemba Site '{$oldName}' to '{$newName}'. Affected records: {$totalAffected}");

        return [
            'status' => 1,
            'message' => "Gemba site renamed successfully from '{$oldName}' to '{$newName}'. Updated {$totalAffected} dependent record(s).",
            'affected_records' => $totalAffected,
            'details' => $affectedCounts
        ];
    }
}
