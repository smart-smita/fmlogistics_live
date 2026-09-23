<?php

namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class ACLTest extends BaseController
{
    /**
     * Test ACL functionality for different user roles
     */
    public function index()
    {
        // Only allow admin to access test page
        if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return redirect()->to(base_url('Login'))->with('error', 'Access denied');
        }
        
        $db = db_connect();
        
        // Get test data for different scenarios
        $data['current_user'] = [
            'role' => $_SESSION['role'] ?? 'Not set',
            'region_id' => $_SESSION['region_id'] ?? 'Not set',
            'cluster_id' => $_SESSION['cluster_id'] ?? 'Not set',
            'location_id' => $_SESSION['location_id'] ?? 'Not set',
            'country' => $_SESSION['country'] ?? 'Not set',
            'login_id' => $_SESSION['login_id'] ?? 'Not set'
        ];
        
        // Test user counts by role
        $data['user_counts'] = $this->getUserCountsByRole($db);
        
        // Test hierarchy relationships
        $data['hierarchy_test'] = $this->testHierarchyRelationships($db);
        
        // Test ACL filtering
        $data['acl_test'] = $this->testACLFiltering($db);
        
        return view('Admin/acl_test', $data);
    }
    
    private function getUserCountsByRole($db)
    {
        $sql = "SELECT 
                    user_designation,
                    COUNT(*) as total_count,
                    COUNT(CASE WHEN region_id IS NOT NULL THEN 1 END) as with_region,
                    COUNT(CASE WHEN cluster_id IS NOT NULL THEN 1 END) as with_cluster,
                    COUNT(CASE WHEN location_id IS NOT NULL THEN 1 END) as with_location
                FROM alert_users 
                GROUP BY user_designation";
        
        return $db->query($sql)->getResultArray();
    }
    
    private function testHierarchyRelationships($db)
    {
        $tests = [];
        
        // Test 1: Clusters with regions
        $sql = "SELECT 
                    COUNT(*) as total_clusters,
                    COUNT(CASE WHEN region_id IS NOT NULL THEN 1 END) as clusters_with_region
                FROM alert_cluster_master";
        $tests['clusters'] = $db->query($sql)->getRowArray();
        
        // Test 2: Locations with clusters
        $sql = "SELECT 
                    COUNT(*) as total_locations,
                    COUNT(CASE WHEN cluster_id IS NOT NULL THEN 1 END) as locations_with_cluster
                FROM alert_location_master";
        $tests['locations'] = $db->query($sql)->getRowArray();
        
        // Test 3: Users with complete hierarchy
        $sql = "SELECT 
                    COUNT(*) as total_users,
                    COUNT(CASE WHEN region_id IS NOT NULL AND cluster_id IS NOT NULL THEN 1 END) as users_with_hierarchy
                FROM alert_users";
        $tests['users'] = $db->query($sql)->getRowArray();
        
        // Test 4: Hierarchy consistency
        $sql = "SELECT 
                    u.user_name,
                    r.region_name,
                    c.cluster_name,
                    l.location_name,
                    CASE 
                        WHEN c.region_id = u.region_id THEN 'Valid'
                        ELSE 'Invalid'
                    END as hierarchy_status
                FROM alert_users u
                LEFT JOIN alert_region r ON u.region_id = r.region_id
                LEFT JOIN alert_cluster_master c ON u.cluster_id = c.cluster_id
                LEFT JOIN alert_location_master l ON u.location_id = l.location_id
                WHERE u.region_id IS NOT NULL AND u.cluster_id IS NOT NULL
                LIMIT 10";
        $tests['hierarchy_sample'] = $db->query($sql)->getResultArray();
        
        return $tests;
    }
    
    private function testACLFiltering($db)
    {
        $tests = [];
        
        // Simulate different user roles and test filtering
        $roles = ['Cluster manager', 'Higher authority', 'Engineer', 'admin'];
        
        foreach($roles as $role) {
            // Simulate session for each role
            $originalRole = $_SESSION['role'] ?? null;
            $_SESSION['role'] = $role;
            
            switch($role) {
                case 'Cluster manager':
                    $_SESSION['region_id'] = '1'; // Simulate region ID
                    $_SESSION['cluster_id'] = '1'; // Simulate cluster ID
                    
                    $sql = "SELECT COUNT(*) as count 
                            FROM alert_users 
                            WHERE region_id = '1' AND cluster_id = '1'";
                    break;
                    
                case 'Higher authority':
                    $_SESSION['country'] = 'IN';
                    
                    $sql = "SELECT COUNT(*) as count 
                            FROM alert_users 
                            WHERE user_emp_country = 'IN'";
                    break;
                    
                case 'Engineer':
                    $_SESSION['login_id'] = '1';
                    
                    $sql = "SELECT COUNT(*) as count 
                            FROM alert_users 
                            WHERE user_id = '1'";
                    break;
                    
                case 'admin':
                    $sql = "SELECT COUNT(*) as count FROM alert_users";
                    break;
            }
            
            $tests[$role] = $db->query($sql)->getRowArray();
            
            // Restore original session
            if($originalRole) {
                $_SESSION['role'] = $originalRole;
            }
        }
        
        return $tests;
    }
    
    /**
     * Test specific user's access
     */
    public function testUserAccess($userId)
    {
        $db = db_connect();
        
        // Get user details
        $sql = "SELECT u.*, r.region_name, c.cluster_name, l.location_name
                FROM alert_users u
                LEFT JOIN alert_region r ON u.region_id = r.region_id
                LEFT JOIN alert_cluster_master c ON u.cluster_id = c.cluster_id
                LEFT JOIN alert_location_master l ON u.location_id = l.location_id
                WHERE u.user_id = ?";
        
        $user = $db->query($sql, [$userId])->getRowArray();
        
        if(!$user) {
            return $this->response->setJSON(['error' => 'User not found']);
        }
        
        // Simulate login for this user
        $originalSession = $_SESSION;
        
        $_SESSION['role'] = $user['user_designation'];
        $_SESSION['region_id'] = $user['region_id'];
        $_SESSION['cluster_id'] = $user['cluster_id'];
        $_SESSION['location_id'] = $user['location_id'];
        $_SESSION['country'] = $user['user_emp_country'];
        $_SESSION['login_id'] = $user['user_id'];
        
        // Test what this user can access
        $accessTest = [];
        
        // Test user access
        if($user['user_designation'] == 'Cluster manager') {
            $sql = "SELECT COUNT(*) as accessible_users
                    FROM alert_users 
                    WHERE region_id = ? AND cluster_id = ?";
            $accessTest['users'] = $db->query($sql, [$user['region_id'], $user['cluster_id']])->getRowArray();
        }
        
        // Restore original session
        $_SESSION = $originalSession;
        
        return $this->response->setJSON([
            'user' => $user,
            'access_test' => $accessTest
        ]);
    }
}