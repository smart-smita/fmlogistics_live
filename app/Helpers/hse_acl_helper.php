<?php
// app/Helpers/hse_acl_helper.php

/**
 * HSE Module Specific ACL Helper
 * Created: 20/03/26
 * Purpose: Isolate Access Control Logic for the HSE Dashboard to avoid conflicts with the OE module.
 * Data Source: alert_hse_client_master
 */

if (!function_exists('getClusterManagerAssignedClusterHSE')) {
    /**
     * Get clusters assigned to a Cluster Manager for the HSE module.
     * Fetches from alert_hse_client_master where 'cluster' matches the user's name.
     * @return array
     */
    function getClusterManagerAssignedClusterHSE()
    {
        if (!isClusterManager()) {
            return [];
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $db = \Config\Database::connect();
        $results = $db->table('alert_hse_client_master')
            ->select('DISTINCT(cluster) as cluster')
            ->where('LOWER(TRIM(cluster))', strtolower(trim($userName)))
            ->where('status !=', 2)
            ->get()
            ->getResultArray();

        $clusters = array_column($results, 'cluster');
        $clusters = array_filter(array_map('trim', $clusters));

        return !empty($clusters) ? $clusters : [];
    }
}

if (!function_exists('getClusterManagerAssignedRegionHSE')) {
    /**
     * Get regions assigned to a Cluster Manager for the HSE module based on their assigned cluster.
     * @return array
     */
    function getClusterManagerAssignedRegionHSE()
    {
        if (!isClusterManager()) {
            return [];
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $db = \Config\Database::connect();
        $results = $db->table('alert_hse_client_master')
            ->select('DISTINCT(region) as region')
            ->where('LOWER(TRIM(cluster))', strtolower(trim($userName)))
            ->where('status !=', 2)
            ->get()
            ->getResultArray();

        return array_column($results, 'region');
    }
}

if (!function_exists('getAccountManagerAssignedClientsHSE')) {
    /**
     * Get clients assigned to an Account Manager (or WH Manager) for the HSE module.
     * Fetches from alert_hse_client_master where 'account_manager' matches the user's name.
     * @return array
     */
    function getAccountManagerAssignedClientsHSE()
    {
        if (!isWHManager() && !isAccountManager()) {
            return [];
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $db = \Config\Database::connect();
        $results = $db->table('alert_hse_client_master')
            ->select('DISTINCT(client_name) as client_name')
            ->where('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
            ->where('status !=', 2)
            ->get()
            ->getResultArray();

        return array_column($results, 'client_name');
    }
}

if (!function_exists('getAccountManagerAssignedDetailsHSE')) {
    /**
     * Get full details (region, cluster, client_name) assigned to an Account Manager for the HSE module.
     * @return array
     */
    function getAccountManagerAssignedDetailsHSE()
    {
        if (!isWHManager() && !isAccountManager()) {
            return [];
        }

        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $db = \Config\Database::connect();
        return $db->table('alert_hse_client_master')
            ->select('region, cluster, client_name')
            ->where('LOWER(TRIM(account_manager))', strtolower(trim($userName)))
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
