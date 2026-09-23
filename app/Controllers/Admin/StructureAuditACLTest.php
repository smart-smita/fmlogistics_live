<?php

namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class StructureAuditACLTest extends BaseController
{
    /**
     * Test Structure Audit ACL functionality
     */
    public function index()
    {
        // Only allow admin to access test page
        if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return redirect()->to(base_url('Login'))->with('error', 'Access denied');
        }
        
        helper(['acl']);
        $db = db_connect();
        
        $data['current_user'] = [
            'role' => $_SESSION['role'] ?? 'Not set',
            'region' => $_SESSION['user_region'] ?? 'Not set',
            'region_id' => $_SESSION['region_id'] ?? 'Not set',
            'cluster' => $_SESSION['user_cluster'] ?? 'Not set',
            'cluster_id' => $_SESSION['cluster_id'] ?? 'Not set',
            'country' => $_SESSION['country'] ?? 'Not set',
            'login_id' => $_SESSION['login_id'] ?? 'Not set'
        ];
        
        // Test ACL functions
        $data['acl_tests'] = $this->testACLFunctions();
        
        // Test audit template access by region
        $data['audit_access_test'] = $this->testAuditAccessByRegion($db);
        
        // Test structure audit permissions
        $data['permission_tests'] = $this->testStructureAuditPermissions();
        
        return view('Admin/structure_audit_acl_test', $data);
    }
    
    private function testACLFunctions()
    {
        $tests = [];
        
        // Test different regions
        $regions = ['NORTH', 'SOUTH', 'EAST', 'WEST'];
        foreach($regions as $region) {
            $tests['region_access'][$region] = canAccessRegion($region);
        }
        
        // Test audit permissions
        $auditTypes = ['structure', 'normal', 'hse'];
        foreach($auditTypes as $type) {
            $tests['audit_permissions'][$type] = canPerformAudit($type);
        }
        
        // Test accessible regions
        $tests['accessible_regions'] = getAccessibleRegions();
        
        // Test user region info
        $tests['user_region_info'] = getUserRegionInfo();
        
        return $tests;
    }
    
    private function testAuditAccessByRegion($db)
    {
        $tests = [];
        
        // Test audit template access for different regions
        $regions = ['NORTH', 'SOUTH', 'EAST', 'WEST'];
        
        foreach($regions as $region) {
            // Simulate different user regions
            $originalRegion = $_SESSION['user_region'] ?? null;
            $_SESSION['user_region'] = $region;
            
            // Test ACL condition
            $aclCondition = getRegionalClusterCondition('', 'audits');
            
            // Count audit templates for this region
            $sql = "SELECT COUNT(*) as count FROM alert_audit_template WHERE 1=1 {$aclCondition}";
            $result = $db->query($sql)->getRowArray();
            
            $tests[$region] = [
                'acl_condition' => $aclCondition,
                'audit_count' => $result['count']
            ];
            
            // Restore original region
            if($originalRegion) {
                $_SESSION['user_region'] = $originalRegion;
            }
        }
        
        return $tests;
    }
    
    private function testStructureAuditPermissions()
    {
        $tests = [];
        
        // Test permissions for different roles
        $roles = ['Cluster manager', 'Engineer', 'Higher authority', 'admin'];
        
        foreach($roles as $role) {
            $originalRole = $_SESSION['role'] ?? null;
            $_SESSION['role'] = $role;
            
            $tests[$role] = [
                'can_perform_structure_audit' => canPerformAudit('structure'),
                'can_perform_normal_audit' => canPerformAudit('normal'),
                'can_perform_hse_audit' => canPerformAudit('hse'),
                'accessible_regions' => getAccessibleRegions()
            ];
            
            // Restore original role
            if($originalRole) {
                $_SESSION['role'] = $originalRole;
            }
        }
        
        return $tests;
    }
    
    /**
     * Test specific user's structure audit access
     */
    public function testUserStructureAccess($userId)
    {
        $db = db_connect();
        
        // Get user details
        $user = $db->query("SELECT * FROM alert_users WHERE user_id = ?", [$userId])->getRowArray();
        
        if(!$user) {
            return $this->response->setJSON(['error' => 'User not found']);
        }
        
        // Simulate login for this user
        $originalSession = $_SESSION;
        
        $_SESSION['role'] = $user['user_designation'];
        $_SESSION['user_region'] = $user['user_region'];
        $_SESSION['region_id'] = $user['region_id'];
        $_SESSION['cluster_id'] = $user['cluster_id'];
        $_SESSION['country'] = $user['user_emp_country'];
        $_SESSION['login_id'] = $user['user_id'];
        
        // Test what this user can access
        $accessTest = [
            'user_info' => [
                'name' => $user['user_name'],
                'role' => $user['user_designation'],
                'region' => $user['user_region'],
                'cluster' => $user['user_cluster']
            ],
            'permissions' => [
                'can_perform_structure_audit' => canPerformAudit('structure'),
                'can_access_south_region' => canAccessRegion('SOUTH'),
                'can_access_north_region' => canAccessRegion('NORTH'),
                'accessible_regions' => getAccessibleRegions()
            ]
        ];
        
        // Test audit template access
        $aclCondition = getRegionalClusterCondition('', 'audits');
        $sql = "SELECT COUNT(*) as count FROM alert_audit_template WHERE audit_name LIKE '%OE%' {$aclCondition}";
        $result = $db->query($sql)->getRowArray();
        $accessTest['audit_access'] = [
            'acl_condition' => $aclCondition,
            'accessible_oe_audits' => $result['count']
        ];
        
        // Restore original session
        $_SESSION = $originalSession;
        
        return $this->response->setJSON($accessTest);
    }
    
    /**
     * Simulate Cluster Manager login and test access
     */
    public function simulateClusterManagerAccess()
    {
        $db = db_connect();
        
        // Find a cluster manager from SOUTH region
        $clusterManager = $db->query("SELECT * FROM alert_users WHERE user_designation = 'Cluster manager' AND user_region = 'SOUTH' LIMIT 1")->getRowArray();
        
        if(!$clusterManager) {
            return $this->response->setJSON(['error' => 'No SOUTH region cluster manager found']);
        }
        
        // Simulate login
        $originalSession = $_SESSION;
        
        $_SESSION['role'] = 'Cluster manager';
        $_SESSION['user_region'] = 'SOUTH';
        $_SESSION['region_id'] = $clusterManager['region_id'];
        $_SESSION['cluster_id'] = $clusterManager['cluster_id'];
        $_SESSION['login_id'] = $clusterManager['user_id'];
        $_SESSION['user_name'] = $clusterManager['user_name'];
        
        // Test access
        $testResults = [
            'simulated_user' => [
                'name' => $clusterManager['user_name'],
                'region' => 'SOUTH',
                'cluster' => $clusterManager['user_cluster']
            ],
            'permissions' => [
                'can_perform_structure_audit' => canPerformAudit('structure'),
                'can_access_south_audits' => canAccessRegion('SOUTH'),
                'can_access_north_audits' => canAccessRegion('NORTH'),
                'accessible_regions' => getAccessibleRegions()
            ]
        ];
        
        // Test audit queries
        $aclCondition = getRegionalClusterCondition('', 'audits');
        
        // Count accessible audit templates
        $auditTemplates = $db->query("SELECT COUNT(*) as count FROM alert_audit_template WHERE 1=1 {$aclCondition}")->getRowArray();
        $testResults['data_access']['audit_templates'] = $auditTemplates['count'];
        
        // Count accessible structure audits
        $structureAudits = $db->query("SELECT COUNT(*) as count FROM alert_final_structured_audit WHERE 1=1 {$aclCondition}")->getRowArray();
        $testResults['data_access']['structure_audits'] = $structureAudits['count'];
        
        // Test specific queries
        $testResults['query_tests'] = [
            'acl_condition' => $aclCondition,
            'south_only_templates' => $db->query("SELECT COUNT(*) as count FROM alert_audit_template WHERE region = 'SOUTH'")->getRowArray()['count'],
            'all_templates' => $db->query("SELECT COUNT(*) as count FROM alert_audit_template")->getRowArray()['count']
        ];
        
        // Restore original session
        $_SESSION = $originalSession;
        
        return $this->response->setJSON($testResults);
    }
}