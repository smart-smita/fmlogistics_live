<?php
// =====================================================
// Simple ACL Helper - No Database Changes Required
// changes on 16/10/25 by darsh: Alternative ACL without schema changes
// =====================================================

if (!function_exists('getUserRegionFromSession')) {
    /**
     * Get user's region from session
     */
    function getUserRegionFromSession() {
        $session = session();
        return $session->get('user_region') ?? '';
    }
}

if (!function_exists('getUserClusterFromSession')) {
    /**
     * Get user's cluster from session
     */
    function getUserClusterFromSession() {
        $session = session();
        return $session->get('user_cluster') ?? '';
    }
}

if (!function_exists('isClusterManager')) {
    /**
     * Check if current user is a cluster manager
     */
    function isClusterManager() {
        if ((session()->get('admin_flag') ?? 0) == 1) return false;
        $session = session();
        $designation = $session->get('user_designation') ?? '';
        return strtolower(trim($designation)) === 'cluster manager';
    }
}

if (!function_exists('isAccountManager')) {
    /**
     * Check if current user is an account manager
     */
    function isAccountManager() {
        if ((session()->get('admin_flag') ?? 0) == 1) return false;
        $session = session();
        $designation = $session->get('user_designation') ?? '';
        return strtolower(trim($designation)) === 'account manager';
    }
}

if (!function_exists('filterQueryByUserRegion')) {
    /**
     * Add regional filtering to database queries for cluster managers
     * @param object $query - CodeIgniter query builder
     * @param string $regionColumn - column name for region filtering
     * @return object - modified query
     */
    function filterQueryByUserRegion($query, $regionColumn = 'region') {
        if (isClusterManager()) {
            $userRegion = getUserRegionFromSession();
            if (!empty($userRegion)) {
                $query->where($regionColumn, $userRegion);
            }
        }
        return $query;
    }
}

if (!function_exists('addRegionWhereClause')) {
    /**
     * Add region WHERE clause to SQL queries for cluster managers
     * @param string $regionColumn - column name for region filtering
     * @return string - WHERE clause to append
     */
    function addRegionWhereClause($regionColumn = 'region') {
        if (isClusterManager()) {
            $userRegion = getUserRegionFromSession();
            if (!empty($userRegion)) {
                return " AND {$regionColumn} = '" . esc($userRegion) . "'";
            }
        }
        return '';
    }
}

if (!function_exists('canCreateAudit')) {
    /**
     * Check if user can create audits (cluster managers have read-only access)
     */
    function canCreateAudit() {
        return !isClusterManager() && !isAccountManager(); // Cluster managers and Account Managers can't create
    }
}

if (!function_exists('canEditAudit')) {
    /**
     * Check if user can edit audits (cluster managers have read-only access)
     */
    function canEditAudit() {
        return !isClusterManager() && !isAccountManager(); // Cluster managers and Account Managers can't edit
    }
}

if (!function_exists('canDeleteAudit')) {
    /**
     * Check if user can delete audits (cluster managers have read-only access)
     */
    function canDeleteAudit() {
        return !isClusterManager() && !isAccountManager(); // Cluster managers and Account Managers can't delete
    }
}

if (!function_exists('canPerformAudit')) {
    /**
     * Determine if the current user can perform an audit action (create/reaudit)
     * Cluster managers are view-only and therefore cannot perform audits.
     * @param string|null $auditType Optional context like 'structure' or 'normal'
     * @return bool
     */
    function canPerformAudit($auditType = null) {
        return !isClusterManager() && !isAccountManager();
    }
}

if (!function_exists('getRegionFilterForClusterManager')) {
    /**
     * Get region filter array for cluster managers
     * @return array - filter conditions
     */
    function getRegionFilterForClusterManager() {
        $filter = [];
        if (isClusterManager()) {
            $userRegion = getUserRegionFromSession();
            if (!empty($userRegion)) {
                $filter['region'] = $userRegion;
            }
        }
        return $filter;
    }
}

if (!function_exists('showACLMessage')) {
    /**
     * Show ACL message for cluster managers
     * @param string $type - 'info', 'warning', 'success'
     * @return string - HTML message
     */
    function showACLMessage($type = 'info') {
        if (isClusterManager()) {
            $userRegion = getUserRegionFromSession();
            return '<div class="alert alert-' . $type . '">
                <i class="fas fa-info-circle"></i> 
                <strong>Regional Access:</strong> You can only view data from your region (' . $userRegion . '). 
                Create/Edit functions are restricted.
            </div>';
        }
        return '';
    }
}