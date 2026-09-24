<?php
// app/Helpers/hse_acl_helper.php

/**
 * HSE Module Specific ACL Helper
 * Created: 20/03/26
 * Purpose: Isolate Access Control Logic for the HSE Dashboard to avoid conflicts with the OE module.
 * Data Source: alert_hse_client_master
 */

if (!function_exists('getAccountManagerAssignedClientsHSE')) {
    /**
     * Get allocated site names for current user for the HSE module.
     * @return array
     */
    function getAccountManagerAssignedClientsHSE()
    {
        if (!isClusterManager() && !isWHManager() && !isAccountManager()) {
            return [];
        }

        $allocated = getUserAllocatedSiteNames('HSE');
        if (!empty($allocated)) {
            return $allocated;
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $db = \Config\Database::connect();
        $results = $db->table('alert_hse_client_master')
            ->select('DISTINCT(client_name) as client_name')
            ->groupStart()
                ->where('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
                ->orWhere('LOWER(TRIM(cluster))', strtolower(trim($userName)))
            ->groupEnd()
            ->where('status !=', 2)
            ->get()
            ->getResultArray();

        return array_column($results, 'client_name');
    }
}

if (!function_exists('getClusterManagerAssignedClusterHSE')) {
    /**
     * Get clusters assigned to current user for the HSE module.
     * @return array
     */
    function getClusterManagerAssignedClusterHSE()
    {
        if (!isClusterManager() && !isWHManager() && !isAccountManager()) {
            return [];
        }

        $allocated = getUserAllocatedSiteNames('HSE');
        $db = \Config\Database::connect();
        if (!empty($allocated)) {
            $results = $db->table('alert_hse_client_master')
                ->select('DISTINCT(cluster) as cluster')
                ->whereIn('client_name', $allocated)
                ->where('status !=', 2)
                ->get()
                ->getResultArray();
            $clusters = array_filter(array_map('trim', array_column($results, 'cluster')));
            return array_values(array_unique($clusters));
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $results = $db->table('alert_hse_client_master')
            ->select('DISTINCT(cluster) as cluster')
            ->groupStart()
                ->where('LOWER(TRIM(cluster))', strtolower(trim($userName)))
                ->orWhere('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
            ->groupEnd()
            ->where('status !=', 2)
            ->get()
            ->getResultArray();

        $clusters = array_filter(array_map('trim', array_column($results, 'cluster')));
        return array_values(array_unique($clusters));
    }
}

if (!function_exists('getClusterManagerAssignedRegionHSE')) {
    /**
     * Get regions assigned to current user for the HSE module.
     * @return array
     */
    function getClusterManagerAssignedRegionHSE()
    {
        if (!isClusterManager() && !isWHManager() && !isAccountManager()) {
            return [];
        }

        $allocated = getUserAllocatedSiteNames('HSE');
        $db = \Config\Database::connect();
        if (!empty($allocated)) {
            $results = $db->table('alert_hse_client_master')
                ->select('DISTINCT(region) as region')
                ->whereIn('client_name', $allocated)
                ->where('status !=', 2)
                ->get()
                ->getResultArray();
            $regions = array_filter(array_map('trim', array_column($results, 'region')));
            return array_values(array_unique($regions));
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $results = $db->table('alert_hse_client_master')
            ->select('DISTINCT(region) as region')
            ->groupStart()
                ->where('LOWER(TRIM(cluster))', strtolower(trim($userName)))
                ->orWhere('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
            ->groupEnd()
            ->where('status !=', 2)
            ->get()
            ->getResultArray();

        $regions = array_filter(array_map('trim', array_column($results, 'region')));
        return array_values(array_unique($regions));
    }
}

if (!function_exists('getAccountManagerAssignedDetailsHSE')) {
    /**
     * Get full details (region, cluster, client_name) assigned to current user for the HSE module.
     * @return array
     */
    function getAccountManagerAssignedDetailsHSE()
    {
        if (!isWHManager() && !isAccountManager() && !isClusterManager()) {
            return [];
        }

        $allocated = getUserAllocatedSiteNames('HSE');
        $db = \Config\Database::connect();

        if (!empty($allocated)) {
            return $db->table('alert_hse_client_master')
                ->select('region, cluster, client_name')
                ->whereIn('client_name', $allocated)
                ->where('status !=', 2)
                ->get()
                ->getResultArray();
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        return $db->table('alert_hse_client_master')
            ->select('region, cluster, client_name')
            ->groupStart()
                ->where('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
                ->orWhere('LOWER(TRIM(cluster))', strtolower(trim($userName)))
            ->groupEnd()
            ->where('status !=', 2)
            ->get()
            ->getResultArray();
    }
}



if (!function_exists('getClusterFilterByClientForHSE')) {
    /**
     * Builds the primary ACL WHERE clause for all HSE dashboard queries.
     * This function is the single point of entry for HSE ACL.
     *
     * @param string $clientNameColumn The client name column in the master audit table (e.g., 'master.client_name').
     * @return string The SQL WHERE clause segment to be appended.
     */
    function getClusterFilterByClientForHSE($clientNameColumn = 'master.client_name')
    {
        $parts = explode('.', $clientNameColumn);
        $col = count($parts) > 1 ? $parts[1] : $parts[0];
        $alias = count($parts) > 1 ? $parts[0] : '';
        return getSiteACLWhereClause('HSE', $col, $alias);
    }
}

if (!function_exists('getHSEAuditACLWhere')) {
    /**
     * Get SQL WHERE clause for HSE Audit Master table based on user role.
     * @param string $alias The alias of the alert_hse_audit_master table (e.g., 'a').
     * @return string
     */
    function getHSEAuditACLWhere($alias = 'a')
    {
        return getSiteACLWhereClause('HSE', 'client_name', $alias);
    }
}
