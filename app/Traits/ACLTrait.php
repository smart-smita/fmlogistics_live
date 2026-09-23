<?php

namespace App\Traits;

/**
 * ACL Trait for Controllers
 * Created: 12/11/25
 * Purpose: Provides ACL methods to controllers for designation-based access control
 */
trait ACLTrait
{
    /**
     * Check if user has permission and return JSON error if not
     * @param string $permission - create, edit, delete, audit, masters
     * @param bool $returnJson - return JSON response or redirect
     * @return mixed
     */
    protected function checkPermission($permission, $returnJson = true)
    {
        $hasPermission = false;
        $message = 'You do not have permission to perform this action.';
        
        switch ($permission) {
            case 'create':
                $hasPermission = canCreateRecord();
                $message = 'You do not have permission to create records.';
                break;
            case 'edit':
                $hasPermission = canEditRecord();
                $message = 'You do not have permission to edit records.';
                break;
            case 'delete':
                $hasPermission = canDeleteRecord();
                $message = 'You do not have permission to delete records.';
                break;
            case 'audit':
                $hasPermission = canPerformAudit();
                $message = 'You do not have permission to perform audits.';
                break;
            case 'masters':
                $hasPermission = canAccessMasters();
                $message = 'You do not have permission to access master data.';
                break;
        }
        
        if (!$hasPermission) {
            if ($returnJson) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message,
                    'permission_denied' => true
                ])->setStatusCode(403);
            } else {
                session()->setFlashdata('error', $message);
                return redirect()->back();
            }
        }
        
        return null; // Permission granted
    }
    
    /**
     * Apply cluster filtering to query builder
     * @param object $builder - Query builder instance
     * @param string $clusterColumn - cluster column name
     * @param string $locationColumn - location column name
     * @param string $regionColumn - region column name
     * @return object
     */
    protected function applyACLFilter($builder, $clusterColumn = 'user_cluster', $locationColumn = 'user_location', $regionColumn = 'user_region')
    {
        return applyClusterFilter($builder, $clusterColumn, $locationColumn, $regionColumn);
    }
    
    /**
     * Get WHERE clause for raw SQL queries
     * @param string $clusterColumn
     * @param string $locationColumn
     * @param string $regionColumn
     * @return string
     */
    protected function getACLWhereClause($clusterColumn = 'user_cluster', $locationColumn = 'user_location', $regionColumn = 'user_region')
    {
        return getClusterWhereClause($clusterColumn, $locationColumn, $regionColumn);
    }
    
    /**
     * Check if current user needs data filtering
     * @return bool
     */
    protected function needsDataFiltering()
    {
        return needsClusterFiltering();
    }
    
    /**
     * Get user's filter conditions
     * @return array
     */
    protected function getFilterConditions()
    {
        return getClusterFilterConditions();
    }
    
    /**
     * Block action if user is Higher Authority (read-only)
     * @param bool $returnJson
     * @return mixed
     */
    protected function blockIfReadOnly($returnJson = true)
    {
        if (isHigherAuthority()) {
            $message = 'Higher Authority users have read-only access. This action is not permitted.';
            
            if ($returnJson) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => $message,
                    'read_only' => true
                ])->setStatusCode(403);
            } else {
                session()->setFlashdata('error', $message);
                return redirect()->back();
            }
        }
        
        return null;
    }
    
    /**
     * Get user permissions array for passing to views
     * @return array
     */
    protected function getUserACLData()
    {
        return getUserPermissions();
    }
    
    /**
     * Add ACL data to view data array
     * @param array $data - existing view data
     * @return array - data with ACL info added
     */
    protected function addACLToViewData($data = [])
    {
        $data['acl'] = getUserPermissions();
        $data['acl_message'] = showACLMessage('info');
        return $data;
    }
}
