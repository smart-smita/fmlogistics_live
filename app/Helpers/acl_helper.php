<?php

/**
 * ACL Helper for Regional/Cluster Access Control
 * Provides centralized access control logic for different user roles
 * changes on 14/10/25 by darsh: Created comprehensive ACL system for Cluster Manager regional restrictions
 */

if (!function_exists('getRegionalClusterCondition')) {
    /**
     * Get SQL WHERE condition based on user's role and regional/cluster access
     * 
     * @param string $tableAlias Optional table alias for the query
     * @param string $type Type of data being filtered (users, audits, etc.)
     * @return string SQL WHERE condition
     */
    function getRegionalClusterCondition($tableAlias = '', $type = 'users') {
        $condition = '';
        $prefix = $tableAlias ? $tableAlias . '.' : '';
        
        if (($_SESSION['admin_flag'] ?? 0) == 1) {
            return ''; // Admins can see all data
        }
        
        if (!isset($_SESSION['role'])) {
            return ' AND 1=0'; // No access if no role
        }
        
        switch ($_SESSION['role']) {
            case 'Cluster manager':
                // Get user's region name from session (backward compatibility)
                $userRegion = $_SESSION['user_region'] ?? '';
                $regionId = $_SESSION['region_id'] ?? '';
                
                if ($type === 'audits') {
                    // For audit tables, filter by region name
                    if ($userRegion) {
                        $condition = " AND {$prefix}region = '{$userRegion}'";
                    } else {
                        $condition = ' AND 1=0';
                    }
                } else {
                    // For user tables, use region_id and cluster_id if available
                    $clusterId = $_SESSION['cluster_id'] ?? '';
                    
                    if ($regionId && $clusterId) {
                        $condition = " AND {$prefix}region_id = '{$regionId}' AND {$prefix}cluster_id = '{$clusterId}'";
                    } elseif ($userRegion) {
                        // Fallback to region name matching
                        $condition = " AND {$prefix}user_region = '{$userRegion}'";
                    } else {
                        $condition = ' AND 1=0';
                    }
                }
                break;
                
            case 'Higher authority':
                // Higher authority can see data from their country
                $country = isset($_SESSION['country']) ? $_SESSION['country'] : '';
                if ($country) {
                    if ($type === 'audits') {
                        // For audits, we might need to join with users table
                        $condition = " AND {$prefix}country = '{$country}'";
                    } else {
                        $condition = " AND {$prefix}user_emp_country = '{$country}'";
                    }
                } else {
                    $condition = ' AND 1=0';
                }
                break;
                
            case 'admin':
            case 'super_admin':
                // Admins can see all data
                $condition = '';
                break;
                
            case 'Engineer':
            case 'Auditor':
                // Engineers and Auditors can only see their own data
                $userId = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : '';
                if ($userId) {
                    $condition = " AND {$prefix}user_id = '{$userId}'";
                } else {
                    $condition = ' AND 1=0';
                }
                break;
                
            default:
                $condition = ' AND 1=0'; // No access for unknown roles
        }
        
        return $condition;
    }
}

if (!function_exists('canAccessRegion')) {
    /**
     * Check if current user can access data from a specific region
     * 
     * @param string $region Region to check access for
     * @return bool
     */
    function canAccessRegion($region) {
        if (($_SESSION['admin_flag'] ?? 0) == 1) {
            return true;
        }
        
        if (!isset($_SESSION['role'])) {
            return false;
        }
        
        switch ($_SESSION['role']) {
            case 'Cluster manager':
                $userRegion = $_SESSION['user_region'] ?? '';
                return $userRegion === $region;
                
            case 'Higher authority':
            case 'admin':
            case 'super_admin':
                return true;
                
            default:
                return false;
        }
    }
}

if (!function_exists('canPerformAudit')) {
    /**
     * Check if current user can perform (create/edit) audits
     * 
     * @param string $auditType Type of audit (structure, normal, hse)
     * @return bool
     */
    function canPerformAudit($auditType = 'structure') {
        if (($_SESSION['admin_flag'] ?? 0) == 1) {
            return true;
        }
        
        if (!isset($_SESSION['role'])) {
            return false;
        }
        
        switch ($_SESSION['role']) {
            case 'Cluster manager':
                // Cluster managers have read-only access to structure audits
                if ($auditType === 'structure') {
                    return false;
                }
                // Can perform other types of audits in their region
                return true;
                
            case 'Engineer':
            case 'Auditor':
                // Engineers and Auditors can perform all types of audits
                return true;
                
            case 'Higher authority':
            case 'admin':
            case 'super_admin':
                return true;
                
            default:
                return false;
        }
    }
}

if (!function_exists('getAccessibleRegions')) {
    /**
     * Get list of regions accessible to current user
     * 
     * @return array Array of region names/IDs
     */
    function getAccessibleRegions() {
        if (($_SESSION['admin_flag'] ?? 0) == 1) {
            $db = db_connect();
            $regions = $db->table('alert_region')
                ->select('region_name')
                ->where('status', 1)
                ->get()
                ->getResultArray();
            return array_column($regions, 'region_name');
        }

        if (!isset($_SESSION['role'])) {
            return [];
        }
        
        $db = db_connect();
        
        switch ($_SESSION['role']) {
            case 'Cluster manager':
                $userRegion = $_SESSION['user_region'] ?? '';
                if ($userRegion) {
                    return [$userRegion];
                }
                return [];
                
            case 'Higher authority':
            case 'admin':
            case 'super_admin':
                $regions = $db->table('alert_region')
                    ->select('region_name')
                    ->where('status', 1)
                    ->get()
                    ->getResultArray();
                return array_column($regions, 'region_name');
                
            default:
                return [];
        }
    }
}

if (!function_exists('getUserRegionInfo')) {
    /**
     * Get current user's region information
     * 
     * @return array User's region details
     */
    function getUserRegionInfo() {
        return [
            'region' => $_SESSION['user_region'] ?? '',
            'region_id' => $_SESSION['region_id'] ?? '',
            'cluster' => $_SESSION['user_cluster'] ?? '',
            'cluster_id' => $_SESSION['cluster_id'] ?? '',
            'location' => $_SESSION['user_location'] ?? '',
            'location_id' => $_SESSION['location_id'] ?? '',
            'country' => $_SESSION['country'] ?? '',
            'role' => $_SESSION['role'] ?? ''
        ];
    }
}