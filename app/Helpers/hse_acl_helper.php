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
        $results = $db->query("
            SELECT DISTINCT cluster FROM alert_client 
            WHERE LOWER(TRIM(cluster)) = ? AND status != 2
            UNION
            SELECT DISTINCT cluster FROM alert_hse_client_master 
            WHERE LOWER(TRIM(cluster)) = ? AND status != 2
        ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();

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
        $results = $db->query("
            SELECT DISTINCT region FROM alert_client 
            WHERE LOWER(TRIM(cluster)) = ? AND status != 2
            UNION
            SELECT DISTINCT region FROM alert_hse_client_master 
            WHERE LOWER(TRIM(cluster)) = ? AND status != 2
        ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();

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
        $results = $db->query("
            SELECT DISTINCT client_name FROM alert_client 
            WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
            UNION
            SELECT DISTINCT client_name FROM alert_hse_client_master 
            WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
        ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();

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
        return $db->query("
            SELECT region, cluster, client_name FROM alert_client 
            WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
            UNION
            SELECT region, cluster, client_name FROM alert_hse_client_master 
            WHERE LOWER(TRIM(account_manager)) = ? AND status != 2
        ", [strtolower(trim($userName)), strtolower(trim($userName))])->getResultArray();
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
        if (canViewAllData()) {
            return '';
        }

        $db = \Config\Database::connect();

        if (isClusterManager()) {

            $assignedClusters = getClusterManagerAssignedClusterHSE();

            if (empty($assignedClusters)) {
                return ' AND 1=0';
            }

            $escapedClusters = array_map([$db, 'escape'], (array)$assignedClusters);
            $clusterList = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters));

            return " AND (EXISTS (
                SELECT 1 FROM alert_hse_client_master hc
                WHERE (LOWER(TRIM(hc.location)) = LOWER(TRIM({$clientNameColumn})) OR LOWER(TRIM(hc.client_name)) = LOWER(TRIM({$clientNameColumn})))
                AND LOWER(TRIM(hc.cluster)) IN ({$clusterList})
                AND hc.status != 2
            ) OR EXISTS (
                SELECT 1 FROM alert_client c
                WHERE (LOWER(TRIM(c.location)) = LOWER(TRIM({$clientNameColumn})) OR LOWER(TRIM(c.client_name)) = LOWER(TRIM({$clientNameColumn})))
                AND LOWER(TRIM(c.cluster)) IN ({$clusterList})
                AND c.status != 2
            ) OR EXISTS (
                SELECT 1 FROM alert_location_master lm
                WHERE LOWER(TRIM(lm.location_name)) = LOWER(TRIM({$clientNameColumn}))
                AND LOWER(TRIM(lm.cluster_name)) IN ({$clusterList})
                AND lm.status != 2
            ))";
        }

        elseif (isWHManager() || isAccountManager()) {

            $userName = getUserName();
            $loginUser = $db->escape(strtolower(trim($userName)));

            return " AND (EXISTS (
                SELECT 1 
                FROM alert_hse_client_master c
                WHERE (
                    LOWER(TRIM(c.location)) = LOWER(TRIM({$clientNameColumn}))
                    OR LOWER(TRIM(c.client_name)) = LOWER(TRIM({$clientNameColumn}))
                )
                AND LOWER(TRIM(c.account_manager)) = {$loginUser}
                AND c.status != 2
            ) OR EXISTS (
                SELECT 1 
                FROM alert_client ac
                WHERE (
                    LOWER(TRIM(ac.location)) = LOWER(TRIM({$clientNameColumn}))
                    OR LOWER(TRIM(ac.client_name)) = LOWER(TRIM({$clientNameColumn}))
                )
                AND LOWER(TRIM(ac.account_manager)) = {$loginUser}
                AND ac.status != 2
            ) OR EXISTS (
                SELECT 1 
                FROM alert_location_master lm
                WHERE LOWER(TRIM(lm.location_name)) = LOWER(TRIM({$clientNameColumn}))
                AND LOWER(TRIM(lm.account_manager)) = {$loginUser}
                AND lm.status != 2
            ))";
        }

        return ' AND 1=0';
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
        return getClusterFilterByClientForHSE("{$alias}.client_name");
    }
}
