<?php

/**
 * Gemba Module Access Control Helper
 * 
 * Provides centralized Role-Based Access Control logic for the Gemba Module.
 * Defines which roles have read-only access vs full write access.
 */

if (!function_exists('gemba_can_write')) {
    /**
     * Checks if the currently logged-in user has write privileges for the Gemba module.
     * Write roles: Super Admin, Admin, Auditor, Higher Authority.
     * Read-only roles: Cluster Manager, Account Manager.
     *
     * @return bool True if user can create/edit/delete/approve, False if read-only.
     */
    function gemba_can_write()
    {
        if ((session()->get('admin_flag') ?? 0) == 1) {
            return true;
        }

        if (!session()->get('role')) {
            return false;
        }

        $role = strtolower(trim(session()->get('role')));

        // Define roles that are allowed to perform write/modification actions
        $write_roles = [
            'super_admin',
            'super admin',
            'admin',
            'auditor',
            'engineer'
        ];

        return in_array($role, $write_roles, true);
    }
}

if (!function_exists('gemba_require_write_access')) {
    /**
     * Checks write access and automatically aborts or redirects if unauthorized.
     * Useful for protecting backend controller methods.
     *
     * @param bool $is_ajax Set to true if the request is AJAX to return a JSON response instead of redirecting.
     * @return \CodeIgniter\HTTP\Response|void
     */
    function gemba_require_write_access($is_ajax = false)
    {
        if (!gemba_can_write()) {
            if ($is_ajax) {
                // If it's an AJAX request, send a JSON error
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => false,
                    'message' => 'You have read-only access. This action is not permitted.'
                ]);
                exit;
            } else {
                // For normal HTTP requests, set flashdata and redirect
                $session = \Config\Services::session();
                $session->setFlashdata('error', 'You have read-only access. This action is not permitted.');

                // Redirect back to dashboard or previous page
                header('Location: ' . base_url('Customer/Gemba_Dashboard'));
                exit;
            }
        }
    }
}

if (!function_exists('apply_gemba_role_filters')) {
    /**
     * Applies role-based filtering to a Query Builder instance for Gemba module tables.
     * 
     * @param \CodeIgniter\Database\BaseBuilder $builder The Query Builder instance
     * @param string $tableName The table being queried ('alert_gemba_audits' or 'alert_gemba_sites')
     * @return \CodeIgniter\Database\BaseBuilder
     */
    function apply_gemba_role_filters($builder, $tableName = 'alert_gemba_audits')
    {
        if (!session()->get('role')) {
            return $builder;
        }

        $role = strtolower(trim(session()->get('role')));
        $userName = strtolower(trim(session()->get('user_name') ?? ''));
        
        $db = \Config\Database::connect();
        $escapedUserName = $db->escape($userName);

        if ($role === 'cluster manager' || $role === 'cluster_manager') {
            $siteSubQuery = "(
                SELECT DISTINCT client_name FROM alert_client
                    WHERE LOWER(TRIM(cluster)) = {$escapedUserName} AND status != 2
                UNION
                SELECT DISTINCT client_name FROM alert_hse_client_master
                    WHERE LOWER(TRIM(cluster)) = {$escapedUserName} AND status != 2
                UNION
                SELECT DISTINCT location_name FROM alert_location_master
                    WHERE LOWER(TRIM(cluster_name)) = {$escapedUserName} AND status != 2
            )";

            if ($tableName === 'alert_gemba_audits') {
                $builder->groupStart()
                    ->where("{$tableName}.site_name IN {$siteSubQuery}", null, false)
                    ->orWhere("LOWER(TRIM({$tableName}.cluster_manager_spoc))", $userName)
                ->groupEnd();
            } else if ($tableName === 'alert_gemba_sites') {
                $builder->where("{$tableName}.site_name IN {$siteSubQuery}", null, false);
            }
        } elseif ($role === 'account manager' || $role === 'account_manager') {
            $siteSubQuery = "(
                SELECT DISTINCT client_name FROM alert_client
                    WHERE LOWER(TRIM(account_manager)) = {$escapedUserName} AND status != 2
                UNION
                SELECT DISTINCT client_name FROM alert_hse_client_master
                    WHERE LOWER(TRIM(account_manager)) = {$escapedUserName} AND status != 2
                UNION
                SELECT DISTINCT location_name FROM alert_location_master
                    WHERE LOWER(TRIM(account_manager)) = {$escapedUserName} AND status != 2
            )";

            if ($tableName === 'alert_gemba_audits') {
                $builder->groupStart()
                    ->where("{$tableName}.site_name IN {$siteSubQuery}", null, false)
                    ->orWhere("LOWER(TRIM({$tableName}.account_manager))", $userName)
                ->groupEnd();
            } else if ($tableName === 'alert_gemba_sites') {
                $builder->where("{$tableName}.site_name IN {$siteSubQuery}", null, false);
            }
        }

        return $builder;
    }
}

if (!function_exists('apply_gemba_latest_filter')) {
    /**
     * Excludes historical re-audit records from HSE audits so that only the latest NCs are displayed.
     * Ensures consistency between Gemba Dashboard, Gemba Audit lists, and Gemba NC Tracker.
     * 
     * @param \CodeIgniter\Database\BaseBuilder $builder
     * @param string $tableName
     * @return \CodeIgniter\Database\BaseBuilder
     */
    function apply_gemba_latest_filter($builder, $tableName = 'alert_gemba_audits')
    {
        if ($tableName === 'alert_gemba_audits') {
            $builder->groupStart()
                ->where("{$tableName}.source_module !=", "hse_audit")
                ->orGroupStart()
                    ->where("{$tableName}.source_module", "hse_audit")
                    ->where("{$tableName}.hse_audit_id IN (SELECT MAX(hse_audit_id) FROM alert_hse_audit_master GROUP BY audit_no)", null, false)
                ->groupEnd()
            ->groupEnd();
        }
        return $builder;
    }
}

if (!function_exists('near_miss_get_authorized_locations')) {
    /**
     * Gets the list of authorized locations for the current user in Near Miss module.
     * Returns TRUE if the user has access to all locations.
     * Returns an array of location names if restricted.
     * Returns an empty array if no locations are authorized.
     */
    function near_miss_get_authorized_locations() {
        $role = strtolower(trim(session()->get('role') ?? ''));
        $userName = strtolower(trim(session()->get('user_name') ?? ''));
        $adminFlag = session()->get('admin_flag') ?? 0;
        $loginId = session()->get('login_id');
        
        // Roles with full access
        $full_access_roles = ['super admin', 'super_admin', 'admin', 'auditor', 'higher authority', 'higher_authority'];
        
        if ($adminFlag == 1 || in_array($role, $full_access_roles, true) || ($loginId == 0 && $loginId !== null)) {
            return true;
        }
        
        if (!$loginId) return [];
        
        $db = \Config\Database::connect();
        $escapedUserName = $db->escape($userName);
        $locations = [];
        
        // Fetch fresh user record for user_location
        $userRecord = $db->table('alert_users')->select('user_location')->where('user_id', $loginId)->get()->getRowArray();
        $userLoc = $userRecord ? trim($userRecord['user_location']) : '';
        
        if (in_array($role, ['cluster manager', 'cluster_manager'])) {
            $results = $db->query("
                SELECT DISTINCT client_name AS location_name FROM alert_client
                    WHERE LOWER(TRIM(cluster)) = {$escapedUserName} AND status != 2
                UNION
                SELECT DISTINCT client_name AS location_name FROM alert_hse_client_master
                    WHERE LOWER(TRIM(cluster)) = {$escapedUserName} AND status != 2
            ")->getResultArray();
            foreach ($results as $row) {
                if (!empty($row['location_name'])) {
                    $locations[] = trim($row['location_name']);
                }
            }
        } elseif (in_array($role, ['account manager', 'account_manager'])) {
            $results = $db->query("
                SELECT DISTINCT client_name AS location_name FROM alert_client
                    WHERE LOWER(TRIM(account_manager)) = {$escapedUserName} AND status != 2
                UNION
                SELECT DISTINCT client_name AS location_name FROM alert_hse_client_master
                    WHERE LOWER(TRIM(account_manager)) = {$escapedUserName} AND status != 2
            ")->getResultArray();
            foreach ($results as $row) {
                if (!empty($row['location_name'])) {
                    $locations[] = trim($row['location_name']);
                }
            }
            if (!empty($userLoc)) {
                $locs = array_map('trim', explode(',', $userLoc));
                foreach ($locs as $l) {
                    if (!empty($l)) {
                        $locations[] = $l;
                    }
                }
            }
        } elseif ($role === 'engineer') {
            if (!empty($userLoc)) {
                // Handle multiple locations if comma-separated
                $locs = array_map('trim', explode(',', $userLoc));
                foreach ($locs as $l) {
                    if (!empty($l)) {
                        $locations[] = $l;
                    }
                }
            }
        }
        
        return array_values(array_unique($locations));
    }
}

if (!function_exists('near_miss_has_location_access')) {
    function near_miss_has_location_access($locationName) {
        $authorized = near_miss_get_authorized_locations();
        if ($authorized === true) return true;
        if (empty($locationName)) return false;
        
        $locationName = trim($locationName);
        foreach ($authorized as $loc) {
            if (strtolower(trim($loc)) === strtolower($locationName)) {
                return true;
            }
        }
        return false;
    }
}
