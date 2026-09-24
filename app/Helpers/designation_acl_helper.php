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

    if (!function_exists('getUserACL')) {
        /**
         * Get current user's Multi-Site ACL configuration from session or database
         * @return array
         */
        function getUserACL() {
            static $aclCache = null;
            if ($aclCache !== null) {
                return $aclCache;
            }

            $designation = getUserDesignation();
            $isAdminFlag = (session()->get('admin_flag') ?? 0) == 1 || (isset($_SESSION['admin_flag']) && $_SESSION['admin_flag'] == 1);
            $isRestrictedRole = in_array($designation, ['cluster manager', 'account manager']) && !$isAdminFlag;

            if (!$isRestrictedRole) {
                $acl = [
                    'is_restricted' => false,
                    'can_view_all' => true,
                    'oe_client_ids' => [],
                    'oe_site_names' => [],
                    'hse_client_ids' => [],
                    'hse_site_names' => [],
                    'allocated_clusters' => []
                ];
                $_SESSION['user_acl'] = $acl;
                return $acl;
            }

            $userId = getUserId();
            $userName = getUserName();
            $db = \Config\Database::connect();
            $mappings = $db->table('alert_user_client_mapping')
                           ->where('user_id', $userId)
                           ->where('status', 1)
                           ->get()->getResultArray();

            $oeIds = [];
            $oeSites = [];
            $hseIds = [];
            $hseSites = [];
            $clusters = [];

            foreach ($mappings as $m) {
                if ($m['client_type'] === 'OE') {
                    $oeIds[] = (int)$m['client_id'];
                    if (!empty($m['site_name'])) $oeSites[] = trim($m['site_name']);
                } elseif ($m['client_type'] === 'HSE') {
                    $hseIds[] = (int)$m['client_id'];
                    if (!empty($m['site_name'])) $hseSites[] = trim($m['site_name']);
                }
                if (!empty($m['cluster_name'])) $clusters[] = trim($m['cluster_name']);
            }

            // Fallback & expansion for legacy setup if user has no entries in alert_user_client_mapping
            if (empty($mappings)) {
                if (!empty($userName)) {
                    // Fetch OE clients for Manager from alert_client
                    $oeRows = $db->table('alert_client')
                        ->select('client_id, client_name, cluster')
                        ->groupStart()
                            ->where('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
                            ->orWhere('LOWER(TRIM(cluster))', strtolower(trim($userName)))
                        ->groupEnd()
                        ->where('status !=', 2)
                        ->get()->getResultArray();
                    foreach ($oeRows as $r) {
                        $oeIds[] = (int)$r['client_id'];
                        if (!empty($r['client_name'])) $oeSites[] = trim($r['client_name']);
                        if (!empty($r['cluster'])) $clusters[] = trim($r['cluster']);
                    }

                    // Fetch HSE clients for Manager from alert_hse_client_master
                    $hseRows = $db->table('alert_hse_client_master')
                        ->select('client_id, client_name, cluster')
                        ->groupStart()
                            ->where('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
                            ->orWhere('LOWER(TRIM(cluster))', strtolower(trim($userName)))
                        ->groupEnd()
                        ->where('status !=', 2)
                        ->get()->getResultArray();
                    foreach ($hseRows as $r) {
                        $hseIds[] = (int)$r['client_id'];
                        if (!empty($r['client_name'])) $hseSites[] = trim($r['client_name']);
                        if (!empty($r['cluster'])) $clusters[] = trim($r['cluster']);
                    }
                }

                $userLoc = getUserLocation();
                if (!empty($userLoc)) {
                    $oeLocRows = $db->table('alert_client')
                        ->select('client_id, client_name, cluster')
                        ->where('LOWER(TRIM(client_name))', strtolower(trim($userLoc)))
                        ->where('status !=', 2)
                        ->get()->getResultArray();
                    foreach ($oeLocRows as $r) {
                        $oeIds[] = (int)$r['client_id'];
                        if (!empty($r['client_name'])) $oeSites[] = trim($r['client_name']);
                        if (!empty($r['cluster'])) $clusters[] = trim($r['cluster']);
                    }
                    $hseLocRows = $db->table('alert_hse_client_master')
                        ->select('client_id, client_name, cluster')
                        ->where('LOWER(TRIM(client_name))', strtolower(trim($userLoc)))
                        ->where('status !=', 2)
                        ->get()->getResultArray();
                    foreach ($hseLocRows as $r) {
                        $hseIds[] = (int)$r['client_id'];
                        if (!empty($r['client_name'])) $hseSites[] = trim($r['client_name']);
                        if (!empty($r['cluster'])) $clusters[] = trim($r['cluster']);
                    }
                }
            }

            // Resolve site names & location names by IDs so both client_name and location variations are matched
            if (!empty($oeIds)) {
                $resolvedOE = $db->table('alert_client')->select('client_name, location')->whereIn('client_id', array_unique($oeIds))->get()->getResultArray();
                foreach ($resolvedOE as $r) {
                    if (!empty($r['client_name'])) $oeSites[] = trim($r['client_name']);
                    if (!empty($r['location'])) $oeSites[] = trim($r['location']);
                }
            }

            if (!empty($hseIds)) {
                $resolvedHSE = $db->table('alert_hse_client_master')->select('client_name, location')->whereIn('client_id', array_unique($hseIds))->get()->getResultArray();
                foreach ($resolvedHSE as $r) {
                    if (!empty($r['client_name'])) $hseSites[] = trim($r['client_name']);
                    if (!empty($r['location'])) $hseSites[] = trim($r['location']);
                }
            }

            $acl = [
                'is_restricted' => true,
                'can_view_all' => false,
                'oe_client_ids' => array_values(array_unique($oeIds)),
                'oe_site_names' => array_values(array_unique($oeSites)),
                'hse_client_ids' => array_values(array_unique($hseIds)),
                'hse_site_names' => array_values(array_unique($hseSites)),
                'allocated_clusters' => array_values(array_unique($clusters))
            ];

            $aclCache = $acl;
            $_SESSION['user_acl'] = $acl;
            return $acl;
        }
    }

    if (!function_exists('getUserAllocatedSiteNames')) {
        /**
         * Get array of allocated site names for current user based on module
         * @param string $moduleType 'OE', 'HSE', 'GEMBA', or 'ALL'
         * @return array
         */
        function getUserAllocatedSiteNames($moduleType = 'OE') {
            $acl = getUserACL();
            if ($acl['can_view_all']) {
                return [];
            }
            $type = strtoupper($moduleType);
            if ($type === 'HSE') {
                $sites = $acl['hse_site_names'] ?? [];
            } elseif ($type === 'OE') {
                $sites = $acl['oe_site_names'] ?? [];
            } else {
                $sites = array_values(array_unique(array_merge($acl['oe_site_names'] ?? [], $acl['hse_site_names'] ?? [])));
            }

            $activeClient = $_SESSION['selected_active_client'] ?? 'ALL';
            if ($activeClient !== 'ALL' && in_array($activeClient, $sites)) {
                return [$activeClient];
            }

            return $sites;
        }
    }

    if (!function_exists('getUserAllocatedClientIds')) {
        /**
         * Get array of allocated client_ids for master tables
         * @param string $moduleType 'OE' or 'HSE'
         * @return array
         */
        function getUserAllocatedClientIds($moduleType = 'OE') {
            $acl = getUserACL();
            if ($acl['can_view_all']) {
                return [];
            }
            $type = strtoupper($moduleType);
            if ($type === 'HSE') {
                $ids = $acl['hse_client_ids'] ?? [];
            } else {
                $ids = $acl['oe_client_ids'] ?? [];
            }

            $activeClient = $_SESSION['selected_active_client'] ?? 'ALL';
            if ($activeClient !== 'ALL') {
                $db = \Config\Database::connect();
                $matchedIds = [];
                if ($type === 'HSE') {
                    $row = $db->table('alert_hse_client_master')->select('client_id')->where('client_name', $activeClient)->get()->getRowArray();
                    if ($row) $matchedIds[] = (int)$row['client_id'];
                } else {
                    $row = $db->table('alert_client')->select('client_id')->where('client_name', $activeClient)->get()->getRowArray();
                    if ($row) $matchedIds[] = (int)$row['client_id'];
                }
                
                $intersect = array_values(array_intersect($ids, $matchedIds));
                if (!empty($intersect)) {
                    return $intersect;
                }
            }

            return $ids;
        }
    }

    if (!function_exists('verifySiteAccess')) {
        /**
         * Security guard to prevent parameter tampering / IDOR.
         * Verifies if the current logged-in user has access to the specified site.
         * @param string|int $siteIdentifier Site Name or numeric Client ID
         * @param string $moduleType 'OE', 'HSE', or 'GEMBA'
         * @return bool True if authorized, False otherwise.
         */
        function verifySiteAccess($siteIdentifier, $moduleType = 'OE') {
            $acl = getUserACL();
            if ($acl['can_view_all']) {
                return true;
            }

            if (!$acl['is_restricted']) {
                return true;
            }

            if (empty($siteIdentifier)) {
                return false;
            }

            if (is_numeric($siteIdentifier)) {
                $allowedIds = getUserAllocatedClientIds($moduleType);
                return in_array((int)$siteIdentifier, $allowedIds);
            }

            $allowedNames = array_map('strtolower', array_map('trim', getUserAllocatedSiteNames($moduleType)));
            return in_array(strtolower(trim($siteIdentifier)), $allowedNames);
        }
    }

    if (!function_exists('applySiteACLFilter')) {
        /**
         * Apply Multi-Site ACL filter to Query Builder
         * @param object $query CodeIgniter Query Builder
         * @param string $moduleType 'OE', 'HSE', or 'GEMBA'
         * @param string $siteColumn Column name for site string matching
         * @param string|null $idColumn Optional column name for numeric client_id matching
         * @return object Modified Query Builder
         */
        function applySiteACLFilter($query, $moduleType = 'OE', $siteColumn = 'client_name', $idColumn = null) {
            $acl = getUserACL();
            if ($acl['can_view_all']) {
                return $query;
            }
            if ($acl['is_restricted']) {
                if (!empty($idColumn)) {
                    $ids = getUserAllocatedClientIds($moduleType);
                    if (!empty($ids)) {
                        $query->whereIn($idColumn, $ids);
                    } else {
                        $query->where('1=0');
                    }
                } else {
                    $siteNames = getUserAllocatedSiteNames($moduleType);
                    if (!empty($siteNames)) {
                        $query->whereIn($siteColumn, $siteNames);
                    } else {
                        $query->where('1=0');
                    }
                }
            }
            return $query;
        }
    }

    if (!function_exists('getSiteACLWhereClause')) {
        /**
         * Get raw SQL WHERE clause for multi-site ACL
         * @param string $moduleType 'OE', 'HSE', or 'GEMBA'
         * @param string $siteColumn Column name in SQL query
         * @param string $alias Table alias if any
         * @return string
         */
        function getSiteACLWhereClause($moduleType = 'OE', $siteColumn = 'client_name', $alias = '') {
            $acl = getUserACL();
            if ($acl['can_view_all']) {
                return '';
            }
            if ($acl['is_restricted']) {
                $siteNames = getUserAllocatedSiteNames($moduleType);
                if (empty($siteNames)) {
                    return ' AND 1=0';
                }
                $db = \Config\Database::connect();
                $escaped = array_map([$db, 'escape'], $siteNames);
                $fullCol = (!empty($alias) ? "{$alias}." : "") . $siteColumn;
                return " AND LOWER(TRIM({$fullCol})) IN (" . implode(',', array_map(function($s) { return "LOWER(TRIM($s))"; }, $escaped)) . ")";
            }
            return '';
        }
    }

    if (!function_exists('getClusterManagerAssignedCluster')) {
        /**
         * Get clusters assigned to Cluster Manager
         * @return array
         */
        function getClusterManagerAssignedCluster() {
            $acl = getUserACL();
            if ($acl['can_view_all']) {
                return [];
            }
            return $acl['allocated_clusters'] ?? [];
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
            
            // Fetch regions from alert_client where cluster matches user_name
            $db = \Config\Database::connect();
            $results = $db->table('alert_client')
                ->select('DISTINCT(region) as region')
                ->where('LOWER(TRIM(cluster))', strtolower(trim($userName)))
                ->where('status !=', 2)
                ->get()
                ->getResultArray();
            
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
        function getAccountManagerAssignedClient() {
            if (!isAccountManager()) return [];
            return getUserAllocatedSiteNames('OE');
        }
    }

    if (!function_exists('getAccountManagerAssignedRegion')) {
        function getAccountManagerAssignedRegion() {
            if (!isAccountManager()) return [];
            $clients = getUserAllocatedSiteNames('OE');
            if(empty($clients)) return [];
            
            $db = \Config\Database::connect();
            $results = $db->table('alert_client')
                ->select('DISTINCT(region) as region')
                ->whereIn('client_name', $clients)
                ->where('status !=', 2)
                ->get()
                ->getResultArray();
            
            $regions = [];
            foreach ($results as $row) {
                $regions[] = trim($row['region']);
            }
            return array_unique(array_filter($regions));
        }
    }

    if (!function_exists('getAccountManagerAssignedCluster')) {
        function getAccountManagerAssignedCluster() {
            if (!isAccountManager()) return [];
            $clients = getUserAllocatedSiteNames('OE');
            if(empty($clients)) return [];
            
            $db = \Config\Database::connect();
            $results = $db->table('alert_client')
                ->select('DISTINCT(cluster) as cluster')
                ->whereIn('client_name', $clients)
                ->where('status !=', 2)
                ->get()
                ->getResultArray();
            
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
             $assignedSites = getUserAllocatedSiteNames('OE');
             if (empty($assignedSites)) {
                 return " AND 1=0";
             }
             $escapedSites = array_map([$db, 'escape'], (array)$assignedSites);
             $siteList = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedSites));

             return " AND (
                 LOWER(TRIM({$alias}.location)) IN ({$siteList})
                 OR LOWER(TRIM({$alias}.client_name)) IN ({$siteList})
                 OR EXISTS (
                     SELECT 1 FROM alert_client c
                     WHERE (LOWER(TRIM(c.location)) = LOWER(TRIM({$alias}.location)) OR LOWER(TRIM(c.client_name)) = LOWER(TRIM({$alias}.client_name)) OR LOWER(TRIM(c.location)) = LOWER(TRIM({$alias}.client_name)) OR LOWER(TRIM(c.client_name)) = LOWER(TRIM({$alias}.location)))
                     AND LOWER(TRIM(c.client_name)) IN ({$siteList})
                     AND c.status != 2
                 )
             )";
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
            $results = $db->table('alert_client')
                ->select('client_name, region, cluster, location')
                ->where('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
                ->where('status !=', 2)
                ->get()
                ->getResultArray();
            
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
         * Get SQL WHERE clause for site filtering via client_name
         * @param string $clientNameColumn - column name for client_name in main table
         * @return string - WHERE clause
         */
        function getClusterFilterByClientName($clientNameColumn = 'client_name') {
            if (!needsClusterFiltering()) {
                return '';
            }
            
            $db = \Config\Database::connect();
            $assignedClients = getUserAllocatedSiteNames('OE');
            if (!empty($assignedClients)) {
                $escapedClients = array_map([$db, 'escape'], $assignedClients);
                return " AND LOWER(TRIM({$clientNameColumn})) IN (" . implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClients)) . ")";
            }
            return ' AND 1=0';
        }
    }

    if (!function_exists('getClusterFilterByLocation')) {
        /**
         * Get SQL WHERE clause for site filtering via location column
         * @param string $locationColumn - column name for location in main table
         * @return string - WHERE clause
         */
        function getClusterFilterByLocation($locationColumn = 'client_name')
        {
            if (!needsClusterFiltering()) {
                return '';
            }

            $db = \Config\Database::connect();
            $assignedClients = getUserAllocatedSiteNames('OE');
            if (!empty($assignedClients)) {
                $escapedClients = array_map([$db, 'escape'], $assignedClients);
                $siteList = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClients));

                return " AND (
                    LOWER(TRIM({$locationColumn})) IN ({$siteList})
                    OR EXISTS (
                        SELECT 1 FROM alert_client ac
                        WHERE (LOWER(TRIM(ac.client_name)) = LOWER(TRIM({$locationColumn})) OR LOWER(TRIM(ac.location)) = LOWER(TRIM({$locationColumn})))
                        AND LOWER(TRIM(ac.client_name)) IN ({$siteList})
                        AND (ac.status IS NULL OR ac.status != 2)
                    )
                )";
            }
            return ' AND 1=0';
        }
    }

    if (!function_exists('getClusterFilterByLocationForHSE')) {
        /**
         * Get SQL WHERE clause for HSE site filtering
         * @param string $column - column name for client_name/location in HSE table
         * @return string - WHERE clause
         */
        function getClusterFilterByLocationForHSE($column = 'master.client_name')
        {
            if (!needsClusterFiltering()) {
                return '';
            }

            $db = \Config\Database::connect();
            $assignedClients = getUserAllocatedSiteNames('HSE');
            if (!empty($assignedClients)) {
                $escapedClients = array_map([$db, 'escape'], $assignedClients);
                $siteList = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClients));

                return " AND (
                    LOWER(TRIM({$column})) IN ({$siteList})
                    OR EXISTS (
                        SELECT 1 FROM alert_hse_client_master cm
                        WHERE (LOWER(TRIM(cm.client_name)) = LOWER(TRIM({$column})) OR LOWER(TRIM(cm.location)) = LOWER(TRIM({$column})))
                        AND LOWER(TRIM(cm.client_name)) IN ({$siteList})
                        AND (cm.status IS NULL OR cm.status != 2)
                    )
                )";
            }
            return ' AND 1=0';
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
                        return " AND LOWER(TRIM({$clusterColumn})) IN (" . implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters)) . ")";
                    }
                }
                return ' AND 1=0';
            }
            
            // Cluster Manager / WH Manager logic
            $assignedClusters = getClusterManagerAssignedCluster();
            
            if (!empty($assignedClusters)) {
                $escapedClusters = array_map([$db, 'escape'], $assignedClusters);
                return " AND LOWER(TRIM({$clusterColumn})) IN (" . implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters)) . ")";
            }
            
            return '';
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
    }

    if (!function_exists('logNcActionHistory')) {
        /**
         * Log an entry into alert_nc_action_history
         *
         * @param string $moduleType 'OE', 'HSE', 'GEMBA', 'NORMAL'
         * @param int $ncDetailId ID of the NC detail record
         * @param string $actionType e.g., 'CREATED', 'WORK_STARTED', 'EVIDENCE_UPLOADED', 'SUBMITTED_TO_CM', 'FORWARDED_TO_AUDITOR', 'REJECTED_BY_CM', 'REJECTED_BY_AUDITOR', 'CLOSED', 'FORCED_CLOSED', 'RE_AUDITED'
         * @param string|null $previousStatus
         * @param string|null $newStatus
         * @param string|null $actionRemarks
         * @param string|null $evidenceAttachment
         * @param int|null $structuredAuditId
         * @param string|null $siteName
         * @return bool
         */
        function logNcActionHistory(
            string $moduleType,
            int $ncDetailId,
            string $actionType,
            ?string $previousStatus = null,
            ?string $newStatus = null,
            ?string $actionRemarks = null,
            ?string $evidenceAttachment = null,
            ?int $structuredAuditId = null,
            ?string $siteName = null
        ): bool {
            try {
                $db = \Config\Database::connect();
                if (!$db->tableExists('alert_nc_action_history')) {
                    return false;
                }

                $session = session();
                $userId = $session->get('user_id') ?? $session->get('login_id') ?? 0;
                $userName = $session->get('user_name') ?? $session->get('name') ?? 'System User';
                $userDesignation = getUserDesignation() ?: ($session->get('role') ?? 'User');

                $data = [
                    'module_type'              => strtoupper(trim($moduleType)),
                    'nc_detail_id'             => $ncDetailId,
                    'structured_audit_id'      => $structuredAuditId,
                    'site_name'                => $siteName,
                    'action_type'              => strtoupper(trim($actionType)),
                    'previous_status'          => $previousStatus,
                    'new_status'               => $newStatus,
                    'performed_by_user_id'     => $userId,
                    'performed_by_user_name'   => $userName,
                    'performed_by_designation' => $userDesignation,
                    'action_remarks'           => $actionRemarks,
                    'evidence_attachment'      => $evidenceAttachment,
                    'created_at'               => date('Y-m-d H:i:s')
                ];

                return (bool) $db->table('alert_nc_action_history')->insert($data);
            } catch (\Throwable $e) {
                log_message('error', 'logNcActionHistory failed: ' . $e->getMessage());
                return false;
            }
        }
    }

