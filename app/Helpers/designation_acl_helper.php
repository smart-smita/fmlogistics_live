    <?php
    // =====================================================
    // Designation-Based ACL Helper
    // Created: 12/11/25
    // Purpose: Access control based on user designations without database changes
    // =====================================================

    if (!function_exists('getUserDesignation')) {
        /**
         * Get current user's designation from session
         * Checks both CodeIgniter session and native $_SESSION for compatibility
         * @return string
         */
        function getUserDesignation() {
            $session = session();
            $designation = $session->get('user_designation') ?? '';
            
            // Fallback to $_SESSION['role'] if user_designation is empty (compatibility)
            if (empty($designation) && isset($_SESSION['role'])) {
                $designation = $_SESSION['role'];
            }
            
            return strtolower(trim($designation)); 
        }
    }


    if (!function_exists('getUserId')) {
        /**
         * Get current user's ID from session
         * @return int
         */
        function getUserId() {
            $session = session();
            return (int)($session->get('user_id') ?? 0);
        }
    }

    if (!function_exists('getUserLocation')) {
        /**
         * Get current user's location from session
         * @return string
         */
        function getUserLocation() {
            $session = session();
            return trim($session->get('user_location') ?? '');
        }
    }

    if (!function_exists('getUserCluster')) {
        /**
         * Get current user's cluster from session
         * @return string
         */
        function getUserCluster() {
            $session = session();
            return trim($session->get('user_cluster') ?? '');
        }
    }

    if (!function_exists('getUserName')) {
        /**
         * Get current user's name from session
         * @return string
         */
        function getUserName() {
            $session = session();
            return trim($session->get('user_name') ?? '');
        }
    }

    if (!function_exists('getClusterManagerAssignedCluster')) {
        /**
         * Get cluster assigned to Cluster Manager
         * For Cluster Managers: Fetch cluster from alert_client where cluster matches user_name
         * Updated: 14/11/25 - Fetch from alert_client table based on user_name
         * @return string
         */
        function getClusterManagerAssignedCluster() {
            if (!isClusterManager() && !isWHManager()) {
                return [];
            }
            
            // Get user name from session
            $userName = getUserName();
            if (empty($userName)) {
                return [];
            }
            
            // Fetch clusters from both alert_client and alert_hse_client_master where cluster matches user_name
            $db = \Config\Database::connect();
            $results = $db->query("
                SELECT DISTINCT cluster FROM alert_client 
                WHERE LOWER(TRIM(cluster)) = ? AND status != 2
                UNION
                SELECT DISTINCT cluster FROM alert_hse_client_master 
                WHERE LOWER(TRIM(cluster)) = ? AND status != 2
            ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();
            
            $clusters = [];
            foreach ($results as $row) {
                $clusters[] = trim($row['cluster']);
            }
            
            return array_unique(array_filter($clusters));
        }
    }

    if (!function_exists('getClusterManagerAssignedRegion')) {
        /**
         * Get region for the cluster assigned to Cluster Manager
         * Updated: 14/11/25 - Fetch region from alert_client based on cluster
         * @return string
         */
        function getClusterManagerAssignedRegion() {
            if (!isClusterManager() && !isWHManager()) {
                return [];
            }
            
            // Get user name from session
            $userName = getUserName();
            if (empty($userName)) {
                return [];
            }
            
            // Fetch regions from both alert_client and alert_hse_client_master where cluster matches user_name
            $db = \Config\Database::connect();
            $results = $db->query("
                SELECT DISTINCT region FROM alert_client 
                WHERE LOWER(TRIM(cluster)) = ? AND status != 2
                UNION
                SELECT DISTINCT region FROM alert_hse_client_master 
                WHERE LOWER(TRIM(cluster)) = ? AND status != 2
            ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();
            
            $regions = [];
            foreach ($results as $row) {
                $regions[] = trim($row['region']);
            }
            
            return array_unique(array_filter($regions));
        }
    }

    if (!function_exists('getUserRegion')) {
        /**
         * Get current user's region
         * For Cluster Managers: Returns empty (not used for filtering)
         * For others: Returns session value
         * Updated: 13/11/25 - Cluster Managers don't use region for filtering
         * @return string
         */
        function getUserRegion() {
            // For Cluster Managers, region is NOT used for filtering
            // They filter by cluster (username) only
            if (isClusterManager() || isWHManager()) {
                return '';
            }
            
            // For other roles, use session value
            $session = session();
            return trim($session->get('user_region') ?? '');
        }
    }

    // =====================================================
    // DESIGNATION CHECKS
    // =====================================================
    if (!function_exists('isSuperAdmin')) {
        function isSuperAdmin()
        {
            if ((session()->get('admin_flag') ?? 0) == 1 || (isset($_SESSION['admin_flag']) && $_SESSION['admin_flag'] == 1)) {
                return true;
            }

            $designation = getUserDesignation();

            // Check all possible super admin values
            if (in_array($designation, ['super admin', 'super_admin', 'admin'])) {
                return true;
            }

            // Fallback check from session role (safety)
            if (isset($_SESSION['role'])) {
                $role = strtolower(trim($_SESSION['role']));
                return in_array($role, ['super admin', 'super_admin', 'admin']);
            }

            return false;
        }
    }

    if (!function_exists('isHigherAuthority')) {
        /**
         * Check if user is Higher Authority
         * @return bool
         */
        function isHigherAuthority() {
            return getUserDesignation() === 'higher authority';
        }
    }

    if (!function_exists('isAuditor')) {
        /**
         * Check if user is Auditor
         * @return bool
         */
        function isAuditor() {
            return getUserDesignation() === 'auditor';
        }
    }

    if (!function_exists('isClusterManager')) {
        /**
         * Check if user is Cluster Manager
         * @return bool
         */
        function isClusterManager() {
            return getUserDesignation() === 'cluster manager';
        }
    }

    if (!function_exists('isAccountManager')) {
        /**
         * Check if user is Account Manager
         * @return bool
         */
        function isAccountManager() {
            return getUserDesignation() === 'account manager';
        }
    }

    if (!function_exists('isWHManager')) {
        /**
         * Check if user is WH Manager (formerly combined with Account Manager)
         * @return bool
         */
        function isWHManager() {
            $designation = getUserDesignation();
            // Removed 'account manager' from here as it is now a distinct role with its own logic
            return $designation === 'wh manager';
        }
    }

    if (!function_exists('getAccountManagerAssignedClient')) {
        /**
         * Get client assigned to Account Manager
         * Fetches from alert_client where account_manager matches user_name
         * @return array
         */
        function getAccountManagerAssignedClient() {
            if (!isAccountManager()) {
                return [];
            }
            
            $userName = getUserName();
            if (empty($userName)) {
                return [];
            }
            
            $db = \Config\Database::connect();
            $results = $db->query("
                SELECT DISTINCT client_name FROM alert_client 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
                UNION
                SELECT DISTINCT client_name FROM alert_hse_client_master 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
            ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();
            
            $clients = [];
            foreach ($results as $row) {
                $clients[] = trim($row['client_name']);
            }
            
            return array_unique(array_filter($clients));
        }
    }

    if (!function_exists('getAccountManagerAssignedRegion')) {
        /**
         * Get regions assigned to Account Manager
         * @return array
         */
        function getAccountManagerAssignedRegion() {
            if (!isAccountManager()) {
                return [];
            }
            
            $userName = getUserName();
            if (empty($userName)) {
                return [];
            }
            
            $db = \Config\Database::connect();
            $results = $db->query("
                SELECT DISTINCT region FROM alert_client 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
                UNION
                SELECT DISTINCT region FROM alert_hse_client_master 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
            ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();
            
            $regions = [];
            foreach ($results as $row) {
                $regions[] = trim($row['region']);
            }
            
            return array_unique(array_filter($regions));
        }
    }

    if (!function_exists('getAccountManagerAssignedCluster')) {
        /**
         * Get clusters assigned to Account Manager
         * @return array
         */
        function getAccountManagerAssignedCluster() {
            if (!isAccountManager()) {
                return [];
            }
            
            $userName = getUserName();
            if (empty($userName)) {
                return [];
            }
            
            $db = \Config\Database::connect();
            $results = $db->query("
                SELECT DISTINCT cluster FROM alert_client 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
                UNION
                SELECT DISTINCT cluster FROM alert_hse_client_master 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
            ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();
            
            $clusters = [];
            foreach ($results as $row) {
                $clusters[] = trim($row['cluster']);
            }
            
            return array_unique(array_filter($clusters));
        }
    }

    if (!function_exists('getOEAuditACLWhere')) {
        /**
         * Get SQL WHERE clause for OE Audit table (alert_final_structured_audit) based on user role.
         * @param string $alias The alias of the alert_final_structured_audit table (e.g., 'audit').
         * @return string
         */
        function getOEAuditACLWhere($alias = 'audit', $audit_type = 'OE')
         {
             if (isAdmin() || isAuditor() || isHigherAuthority()) {
                 return '';
             }
 
             $db = \Config\Database::connect();
             $userName = getUserName();
             $loginUser = $db->escape(strtolower(trim($userName)));
 
             // Both alert_normal_audit and alert_final_structured_audit have 'location' column
             // which stores the site/client identifier.
             $auditClientColumn = 'location';
 
             if (isClusterManager()) {
                 $assignedClusters = getClusterManagerAssignedCluster();
                 if (empty($assignedClusters)) {
                     return " AND 1=0";
                 }
                 $escapedClusters = array_map([$db, 'escape'], (array)$assignedClusters);
                 $clusterList = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters));
 
                 return " AND (EXISTS (
                     SELECT 1 FROM alert_client c
                     WHERE (LOWER(TRIM(c.location)) = LOWER(TRIM({$alias}.{$auditClientColumn})) OR LOWER(TRIM(c.client_name)) = LOWER(TRIM({$alias}.{$auditClientColumn})))
                     AND LOWER(TRIM(c.cluster)) IN ({$clusterList})
                     AND c.status != 2
                 ) OR EXISTS (
                     SELECT 1 FROM alert_hse_client_master hc
                     WHERE (LOWER(TRIM(hc.location)) = LOWER(TRIM({$alias}.{$auditClientColumn})) OR LOWER(TRIM(hc.client_name)) = LOWER(TRIM({$alias}.{$auditClientColumn})))
                     AND LOWER(TRIM(hc.cluster)) IN ({$clusterList})
                     AND hc.status != 2
                 ) OR LOWER(TRIM({$alias}.cluster_name)) IN ({$clusterList}))";
             }
 
             if (isAccountManager() || isWHManager()) {
                 return " AND (EXISTS (
                     SELECT 1 FROM alert_client c
                     WHERE (LOWER(TRIM(c.location)) = LOWER(TRIM({$alias}.{$auditClientColumn})) OR LOWER(TRIM(c.client_name)) = LOWER(TRIM({$alias}.{$auditClientColumn})))
                     AND LOWER(TRIM(c.account_manager)) = {$loginUser}
                     AND c.status != 2
                 ) OR EXISTS (
                     SELECT 1 FROM alert_hse_client_master hc
                     WHERE (LOWER(TRIM(hc.location)) = LOWER(TRIM({$alias}.{$auditClientColumn})) OR LOWER(TRIM(hc.client_name)) = LOWER(TRIM({$alias}.{$auditClientColumn})))
                     AND LOWER(TRIM(hc.account_manager)) = {$loginUser}
                     AND hc.status != 2
                 ) OR LOWER(TRIM({$alias}.client_manager_name)) = {$loginUser})";
             }
 
             return " AND 1=0";
         }
     }

    if (!function_exists('getAccountManagerClientDetails')) {
        /**
         * Get full client details assigned to Account Manager
         * Returns: client_name, region, cluster, location
         * @return array|null
         */
        function getAccountManagerClientDetails() {
            if (!isAccountManager()) {
                return null;
            }
            
            $userName = getUserName();
            if (empty($userName)) {
                return null;
            }
            
            $db = \Config\Database::connect();
            $results = $db->query("
                SELECT client_name, region, cluster, location FROM alert_client 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
                UNION
                SELECT client_name, region, cluster, location FROM alert_hse_client_master 
                WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
            ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();
            
            return !empty($results) ? $results : null;
        }
    }

    if (!function_exists('isAdmin')) {
        /**
         * Check if user is Admin or Super Admin
         * Admins have full access to everything
         * @return bool
         */
        function isAdmin() {
            if ((session()->get('admin_flag') ?? 0) == 1 || (isset($_SESSION['admin_flag']) && $_SESSION['admin_flag'] == 1)) {
                return true;
            }

            $designation = getUserDesignation();
            $session = session();
            $role = strtolower(trim($session->get('role') ?? ''));
            
            return $designation === 'admin' 
                || $designation === 'super admin' 
                || $role === 'admin' 
                || $role === 'super_admin'
                || $role === 'superadmin';
        }
    }

    // =====================================================
    // PERMISSION CHECKS
    // =====================================================

    if (!function_exists('canViewData')) {
        /**
         * Check if user can view data (all can view)
         * @return bool
         */
        function canViewData() {
            return true; // All designations can view
        }
    }

    if (!function_exists('canCreateAudit')) {
        /**
         * Check if user can create audits
         * Admin/Super Admin: YES (full access)
         * Higher Authority: NO (read-only)
         * Auditor: YES
         * Cluster Manager: NO (view only their cluster)
         * Account Manager: NO (view only OE NC Tracker)
         * @return bool
         */
        function canCreateAudit() {
            return isAdmin() || isAuditor();
        }
    }

    if (!function_exists('canEditAudit')) {
        /**
         * Check if user can edit audits
         * Admin/Super Admin: YES (full access)
         * Higher Authority: NO (read-only)
         * Auditor: YES
         * Cluster Manager: NO
         * Account Manager: NO (except OE NC Tracker status updates)
         * @return bool
         */
        function canEditAudit() {
            return isAdmin() || isAuditor();
        }
    }

    if (!function_exists('canDeleteAudit')) {
        /**
         * Check if user can delete audits
         * Admin/Super Admin: YES (full access)
         * Higher Authority: NO (read-only)
         * Auditor: YES
         * Cluster Manager: NO
         * Account Manager: NO
         * @return bool
         */
        function canDeleteAudit() {
            return isAdmin() || isAuditor();
        }
    }

    if (!function_exists('canPerformAudit')) {
        /**
         * Check if user can perform audit actions
         * Admin/Super Admin: YES (full access)
         * Auditor: YES
         * Account Manager: YES for Normal audits, NO for OE and HSE
         * Others: NO
         * @param string|null $auditType
         * @return bool
         */
        function canPerformAudit($auditType = null) {
            if (isAdmin() || isAuditor()) {
                return true;
            }
            if (isAccountManager()) {
                if ($auditType === 'OE' || $auditType === 'HSE') {
                    return false;
                }
                return true; // Can perform other audits like Normal
            }
            return false;
        }
    }

    if (!function_exists('canAccessMasters')) {
        /**
         * Check if user can access master data management
         * Admin/Super Admin: YES (full access)
         * Higher Authority: NO
         * Auditor: YES
         * Cluster Manager: YES (limited to their data)
         * Account Manager: YES (limited to OE NC Tracker only)
         * @return bool
         */
        function canAccessMasters() {
            return isAdmin() || isAuditor() || isClusterManager() || isWHManager() || isAccountManager() || isHigherAuthority();
        }
    }

    if (!function_exists('canDownloadReports')) {
        /**
         * Check if user can download reports (all can download)
         * @return bool
         */
        function canDownloadReports() {
            return true; // All designations can download
        }
    }

    if (!function_exists('canCreateRecord')) {
        /**
         * Generic permission check for creating records
         * Admin/Super Admin: YES (full access)
         * Higher Authority: NO
         * Auditor: YES
         * Cluster Manager: YES (for their cluster)
         * Account Manager: YES (for their cluster)
         * @return bool
         */
        function canCreateRecord() {
            return isAdmin() || !isHigherAuthority();
        }
    }

    if (!function_exists('canEditRecord')) {
        /**
         * Generic permission check for editing records
         * Admin/Super Admin: YES (full access)
         * Higher Authority: NO
         * Auditor: YES
         * Cluster Manager: YES (for their cluster)
         * Account Manager: YES (for their cluster)
         * @return bool
         */
        function canEditRecord() {
            return isAdmin() || !isHigherAuthority();
        }
    }

    if (!function_exists('canDeleteRecord')) {
        /**
         * Generic permission check for deleting records
         * Admin/Super Admin: YES (full access)
         * Higher Authority: NO
         * Auditor: YES
         * Cluster Manager: YES (for their cluster)
         * Account Manager: YES (for their cluster)
         * @return bool
         */
        function canDeleteRecord() {
            return isAdmin() || !isHigherAuthority();
        }
    }

    // =====================================================
    // DATA FILTERING FOR CLUSTER/ACCOUNT MANAGERS
    // =====================================================

    if (!function_exists('needsClusterFiltering')) {
        /**
         * Check if user needs cluster-based data filtering
         * Admin/Super Admin: NO (see all data)
         * Higher Authority and Auditors: NO (see all data)
         * Cluster/Account Managers: YES (see only their cluster data)
         * @return bool
         */
        function needsClusterFiltering() {
            // Admins see all data - no filtering
            if (isAdmin()) {
                return false;
            }
            return isClusterManager() || isWHManager() || isAccountManager();
        }
    }

    if (!function_exists('canViewAllData')) {
        /**
         * Check if user can view all data without filtering
         * Admin/Super Admin: YES (full access)
         * Higher Authority and Auditors: YES
         * Others: NO
         * @return bool
         */
        function canViewAllData() {
            return isAdmin() || isHigherAuthority() || isAuditor();
        }
    }

    if (!function_exists('getClusterFilterConditions')) {
        /**
         * Get filter conditions for cluster/account managers
         * Returns array of conditions to filter data by user's assigned cluster
         * Updated: 13/11/25 - Use cluster from alert_client lookup
         * Updated: 28/01/26 - Added Account Manager support
         * @return array
         */
        function getClusterFilterConditions() {
            if (!needsClusterFiltering()) {
                return [];
            }
            
            $conditions = [];
            
            if (isAccountManager()) {
                // Account Manager: Filter by assigned clients
                $clientDetailsList = getAccountManagerClientDetails();
                if ($clientDetailsList) {
                    $clients = [];
                    $clusters = [];
                    foreach ($clientDetailsList as $details) {
                        $clients[] = $details['client_name'];
                        if (!empty($details['cluster'])) {
                            $clusters[] = $details['cluster'];
                        }
                    }
                    $conditions['client_name'] = array_unique($clients);
                    if (!empty($clusters)) {
                        $conditions['cluster'] = array_unique($clusters);
                    }
                }
            } else {
                // Cluster Manager / WH Manager: Get clusters from alert_client table based on user name
                $assignedClusters = getClusterManagerAssignedCluster();
                
                if (!empty($assignedClusters)) {
                    $conditions['cluster'] = $assignedClusters;
                }
            }
            
            return $conditions;
        }
    }

    if (!function_exists('applyClusterFilter')) {
        /**
         * Apply cluster filtering to query builder
         * Updated: 13/11/25 - Filter by cluster from alert_client lookup
         * Updated: 28/01/26 - Added Account Manager support
         * @param object $query - CodeIgniter query builder instance
         * @param string $clusterColumn - column name for cluster (default: 'cluster')
         * @return object - modified query
         */
        function applyClusterFilter($query, $clusterColumn = 'cluster') {
            if (!needsClusterFiltering()) {
                return $query;
            }
            
            if (isAccountManager()) {
                // Account Manager: Filter by assigned clients
                $clientDetailsList = getAccountManagerClientDetails();
                if ($clientDetailsList) {
                    $clients = [];
                    foreach ($clientDetailsList as $details) {
                        if (!empty($details['client_name'])) {
                            $clients[] = $details['client_name'];
                        }
                    }
                    if (!empty($clients)) {
                        $query->whereIn('client_name', array_unique($clients));
                    }
                }
            } else {
                // Cluster Manager / WH Manager: Get clusters from alert_client table based on user name
                $assignedClusters = getClusterManagerAssignedCluster();
                
                if (!empty($assignedClusters)) {
                    $query->whereIn($clusterColumn, $assignedClusters);
                }
            }
            
            return $query;
        }
    }

    if (!function_exists('getClusterWhereClause')) {
        /**
         * Get SQL WHERE clause for cluster filtering (for raw queries)
         * Updated: 13/11/25 - Filter by cluster from alert_client lookup
         * Updated: 28/01/26 - Added Account Manager support
         * @param string $clusterColumn - column name for cluster (default: 'cluster')
         * @return string - WHERE clause to append
         */
        function getClusterWhereClause($clusterColumn = 'cluster') {
            if (!needsClusterFiltering()) {
                return '';
            }
            
            $db = \Config\Database::connect();
            
            if (isAccountManager()) {
                // Account Manager: Filter by assigned clients
                $clientDetailsList = getAccountManagerClientDetails();
                if ($clientDetailsList) {
                    $clients = [];
                    foreach ($clientDetailsList as $details) {
                        if (!empty($details['client_name'])) {
                            $clients[] = $details['client_name'];
                        }
                    }
                    if (!empty($clients)) {
                        $escapedClients = array_map([$db, 'escape'], array_unique($clients));
                        return " AND client_name IN (" . implode(',', $escapedClients) . ")";
                    }
                }
            } else {
                // Cluster Manager / WH Manager: Get clusters from alert_client table based on user name
                $assignedClusters = getClusterManagerAssignedCluster();
                
                if (!empty($assignedClusters)) {
                    $escapedClusters = array_map([$db, 'escape'], $assignedClusters);
                    return " AND {$clusterColumn} IN (" . implode(',', $escapedClusters) . ")";
                }
            }
            
            return '';
        }
    }

    if (!function_exists('getClusterFilterByClientName')) {
        /**
         * Get SQL WHERE clause for cluster filtering via client_name join
         * This is used when the main table has client_name but not cluster
         * Updated: 14/11/25 - Enhanced cluster-based filtering using username as cluster
         * Updated: 28/01/26 - Added Account Manager support
         * @param string $clientNameColumn - column name for client_name in main table
         * @return string - WHERE clause with EXISTS subquery
         */
        function getClusterFilterByClientName($clientNameColumn = 'client_name') {
            if (!needsClusterFiltering()) {
                return '';
            }
            
            $db = \Config\Database::connect();
            
            if (isAccountManager()) {
                $assignedClients = getAccountManagerAssignedClient();
                if (!empty($assignedClients)) {
                    $escapedClients = array_map([$db, 'escape'], $assignedClients);
                    return " AND {$clientNameColumn} IN (" . implode(',', $escapedClients) . ")";
                }
                return ' AND 1=0';
            }

            // Cluster Manager / WH Manager logic
            $assignedClusters = getClusterManagerAssignedCluster();
            
            if (!empty($assignedClusters)) {
                $escapedClusters = array_map([$db, 'escape'], $assignedClusters);
                return " AND EXISTS (
                    SELECT 1 FROM alert_client 
                    WHERE alert_client.client_name = {$clientNameColumn}
                    AND LOWER(TRIM(alert_client.cluster)) IN (" . implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters)) . ")
                    AND (alert_client.status IS NULL OR alert_client.status != 2)
                )";
            }
            
            return '';
        }
    }

    if (!function_exists('getClusterFilterByLocation')) {
        /**
         * Get SQL WHERE clause for cluster filtering via location join with alert_location_master
         * This is used when the main table has location but not client_name or cluster
         * For tables like alert_normal_audit that only have location column
         * Updated: 14/11/25 - Location-based filtering for normal audit tables
         * Updated: 28/01/26 - Added Account Manager support
         * @param string $locationColumn - column name for location in main table
         * @return string - WHERE clause with EXISTS subquery
         */
        function getClusterFilterByLocation($locationColumn = 'client_name')
        {
            if (!needsClusterFiltering()) {
                return '';
            }

            $db = \Config\Database::connect();
            $loginUser = $db->escape(strtolower(trim(getUserName())));

            // Account Manager
            if (isAccountManager()) {
                $assignedClients = getAccountManagerAssignedClient();
                if (!empty($assignedClients)) {
                    $escapedClients = array_map([$db, 'escape'], $assignedClients);
                    return " AND ({$locationColumn} IN (" . implode(',', $escapedClients) . ") OR LOWER(TRIM(client_manager_name)) = {$loginUser})";
                }
                return " AND LOWER(TRIM(client_manager_name)) = {$loginUser}";
            }

            // Cluster Manager / WH Manager
            $assignedClusters = getClusterManagerAssignedCluster();
            if (!empty($assignedClusters)) {
                $escapedClusters = array_map([$db, 'escape'], $assignedClusters);
                $clusterList = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters));

                return " AND (EXISTS (
                    SELECT 1
                    FROM alert_client ac
                    WHERE ac.client_name = {$locationColumn}
                    AND LOWER(TRIM(ac.cluster)) IN ({$clusterList})
                    AND (ac.status IS NULL OR ac.status != 2)
                ) OR LOWER(TRIM(cluster_name)) IN ({$clusterList}) OR LOWER(TRIM(cluster_name)) = {$loginUser})";
            }

            return " AND LOWER(TRIM(cluster_name)) = {$loginUser}";
        }
    }

    if (!function_exists('getClusterFilterByLocationForHSE')) {
        /**
         * Get SQL WHERE clause for cluster filtering via location join for HSE audits
         * HSE audit tables use different structure, so we need a specific function
         * Updated: 14/11/25 - HSE-specific location-based filtering
         * Updated: 28/01/26 - Added Account Manager support
         * @param string $locationColumn - column name for location in HSE table
         * @return string - WHERE clause with EXISTS subquery
         */
        function getClusterFilterByLocationForHSE($column = 'master.client_name')
        {
            if (!needsClusterFiltering()) {
                return '';
            }

            $db = \Config\Database::connect();
            $loginUser = $db->escape(strtolower(trim(getUserName())));

            if (isAccountManager()) {
                $assignedClients = getAccountManagerAssignedClient();
                if (!empty($assignedClients)) {
                    $escapedClients = array_map([$db, 'escape'], $assignedClients);
                    return " AND ({$column} IN (" . implode(',', $escapedClients) . ") OR LOWER(TRIM(account_manager)) = {$loginUser})";
                }
                return " AND LOWER(TRIM(account_manager)) = {$loginUser}";
            }

            $assignedClusters = getClusterManagerAssignedCluster();

            if (!empty($assignedClusters)) {
                $escapedClusters = array_map(function ($c) use ($db) {
                    return "LOWER(TRIM(" . $db->escape($c) . "))";
                }, $assignedClusters);
                $clusterList = implode(',', $escapedClusters);

                return " AND (EXISTS (
                    SELECT 1 
                    FROM alert_hse_client_master cm
                    WHERE 
                        TRIM(LOWER(cm.client_name)) = TRIM(LOWER({$column}))
                        AND LOWER(TRIM(cm.cluster)) IN (" . $clusterList . ")
                        AND (cm.status IS NULL OR cm.status != 2)
                ) OR LOWER(TRIM(cluster_name)) IN (" . $clusterList . ") OR LOWER(TRIM(cluster_name)) = {$loginUser})";
            }

            return " AND LOWER(TRIM(cluster_name)) = {$loginUser}";
        }
    }

    if (!function_exists('getDirectClusterFilter')) {
        /**
         * Get SQL WHERE clause for direct cluster filtering
         * This bypasses the alert_client table and filters directly by cluster_name
         * Updated: 14/11/25 - Direct cluster filtering for better reliability
         * Updated: 28/01/26 - Added Account Manager support
         * @param string $clusterColumn - column name for cluster in the query
         * @return string - WHERE clause
         */
        function getDirectClusterFilter($clusterColumn = 'cluster_name') {
            if (!needsClusterFiltering()) {
                return '';
            }
            
            $db = \Config\Database::connect();
            $loginUser = $db->escape(strtolower(trim(getUserName())));

            if (isAccountManager()) {
                $clientDetailsList = getAccountManagerClientDetails();
                if ($clientDetailsList) {
                    $clusters = [];
                    foreach ($clientDetailsList as $details) {
                        if (!empty($details['cluster'])) {
                            $clusters[] = $details['cluster'];
                        }
                    }
                    if (!empty($clusters)) {
                        $escapedClusters = array_map([$db, 'escape'], array_unique($clusters));
                        return " AND (LOWER(TRIM({$clusterColumn})) IN (" . implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters)) . ") OR LOWER(TRIM(account_manager)) = {$loginUser} OR LOWER(TRIM(client_manager_name)) = {$loginUser})";
                    }
                }
                return " AND (LOWER(TRIM(account_manager)) = {$loginUser} OR LOWER(TRIM(client_manager_name)) = {$loginUser})";
            }
            
            // Cluster Manager / WH Manager logic
            $assignedClusters = getClusterManagerAssignedCluster();
            
            if (!empty($assignedClusters)) {
                $escapedClusters = array_map([$db, 'escape'], $assignedClusters);
                return " AND (LOWER(TRIM({$clusterColumn})) IN (" . implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters)) . ") OR LOWER(TRIM({$clusterColumn})) = {$loginUser})";
            }
            
            return " AND LOWER(TRIM({$clusterColumn})) = {$loginUser}";
        }
    }

    // =====================================================
    // UI HELPERS
    // =====================================================

    if (!function_exists('showACLMessage')) {
        /**
         * Display ACL message based on user designation
         * @param string $type - 'info', 'warning', 'success', 'danger'
         * @return string - HTML message
         */
        function showACLMessage($type = 'info') {
            $designation = getUserDesignation();
            $message = '';
            
            if (isHigherAuthority()) {
                $message = '<div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-eye"></i> 
                    <strong>Read-Only Access:</strong> You can view all data and download reports, but all action buttons (Create, Edit, Delete, Perform Audit) are hidden.
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>';
            } elseif (isClusterManager() || isWHManager()) {
                // Cluster-Based Filtering message removed per user request
                $message = '';
            } elseif (isAccountManager()) {
                // Limited Access message removed per user request
                $message = '';
            }
            
            return $message;
        }
    }

    if (!function_exists('hideIfNoPermission')) {
        /**
         * Return CSS class to hide element if user has no permission
         * @param string $permission - permission to check (create, edit, delete, masters)
         * @return string - CSS class
         */
        function hideIfNoPermission($permission) {
            $hasPermission = false;
            
            switch ($permission) {
                case 'create':
                    $hasPermission = canCreateRecord();
                    break;
                case 'edit':
                    $hasPermission = canEditRecord();
                    break;
                case 'delete':
                    $hasPermission = canDeleteRecord();
                    break;
                case 'masters':
                    $hasPermission = canAccessMasters();
                    break;
                case 'audit':
                    $hasPermission = canPerformAudit();
                    break;
            }
            
            return $hasPermission ? '' : 'd-none';
        }
    }

    if (!function_exists('disableIfNoPermission')) {
        /**
         * Return disabled attribute if user has no permission
         * @param string $permission - permission to check
         * @return string - 'disabled' or ''
         */
        function disableIfNoPermission($permission) {
            $hasPermission = false;
            
            switch ($permission) {
                case 'create':
                    $hasPermission = canCreateRecord();
                    break;
                case 'edit':
                    $hasPermission = canEditRecord();
                    break;
                case 'delete':
                    $hasPermission = canDeleteRecord();
                    break;
                case 'masters':
                    $hasPermission = canAccessMasters();
                    break;
                case 'audit':
                    $hasPermission = canPerformAudit();
                    break;
            }
            
            return $hasPermission ? '' : 'disabled';
        }
    }

    // =====================================================
    // PERMISSION SUMMARY
    // =====================================================

    if (!function_exists('getUserPermissions')) {
        /**
         * Get array of all permissions for current user
         * @return array
         */
        function getUserPermissions() {
            return [
                'designation' => getUserDesignation(),
                'can_view' => canViewData(),
                'can_create' => canCreateRecord(),
                'can_edit' => canEditRecord(),
                'can_delete' => canDeleteRecord(),
                'can_create_audit' => canCreateAudit(),
                'can_edit_audit' => canEditAudit(),
                'can_delete_audit' => canDeleteAudit(),
                'can_perform_audit' => canPerformAudit(),
                'can_access_masters' => canAccessMasters(),
                'can_download_reports' => canDownloadReports(),
                'needs_filtering' => needsClusterFiltering(),
                'filter_conditions' => getClusterFilterConditions(),
            ];
        }
        
        if (!function_exists('applySiteACLFilter')) {
        /**
         * Applies Designation-based ACL filtering to Master Tables (OE and HSE Clients)
         */
        function applySiteACLFilter($builder, $auditType = 'OE', $clientColumn = 'client_name', $idColumn = 'client_id') {
            if (canViewAllData()) {
                return $builder;
            }

            $loginUser = getUserName();

            if (isAccountManager() || isWHManager()) {
                if (!empty($loginUser)) {
                    $builder->where('LOWER(TRIM(account_manager))', strtolower(trim($loginUser)));
                } else {
                    $builder->where($idColumn, -1);
                }
            } elseif (isClusterManager()) {
                $clusters = getClusterManagerAssignedCluster();
                if (!empty($clusters)) {
                    $builder->whereIn('cluster', $clusters);
                } else {
                    $builder->where($idColumn, -1);
                }
            } elseif (isEngineer()) {
                $location = getUserLocation();
                if (!empty($location)) {
                    // Match the location to the client_name/location column
                    $builder->groupStart()
                            ->where('LOWER(TRIM('.$clientColumn.'))', strtolower(trim($location)))
                            ->orWhere('LOWER(TRIM(location))', strtolower(trim($location)))
                            ->groupEnd();
                } else {
                    $builder->where($idColumn, -1);
                }
            } else {
                // Non-admin, non-manager, non-engineer (e.g. general staff)
                if (!isAuditor()) {
                    $builder->where($idColumn, -1);
                }
            }

            return $builder;
        }
    }
}