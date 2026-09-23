<?php

namespace App\Controllers\Admin;
use App\Controllers\BaseController;

class DataMigration extends BaseController
{
    /**
     * Run the complete data migration for hierarchical relationships
     */
    public function runMigration()
    {
        // Only allow super admin to run migration
        if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return redirect()->to(base_url('Login'))->with('error', 'Access denied');
        }
        
        $db = db_connect();
        $results = [];
        
        try {
            // Step 1: Add new columns if they don't exist
            $results[] = $this->addHierarchyColumns($db);
            
            // Step 2: Populate region-cluster relationships
            $results[] = $this->populateClusterRegions($db);
            
            // Step 3: Populate cluster-location relationships  
            $results[] = $this->populateLocationClusters($db);
            
            // Step 4: Update user table with proper IDs
            $results[] = $this->updateUserHierarchy($db);
            
            // Step 5: Add foreign key constraints
            $results[] = $this->addForeignKeys($db);
            
            // Step 6: Validate data integrity
            $results[] = $this->validateDataIntegrity($db);
            
            // Step 7: Multi-Site ACL schema and seed migration
            $results[] = $this->runMultiSiteAclMigration($db);
            
            $data['results'] = $results;
            $data['success'] = true;
            
        } catch (Exception $e) {
            $data['results'] = $results;
            $data['error'] = $e->getMessage();
            $data['success'] = false;
        }
        
        return view('Admin/migration_results', $data);
    }
    
    public function runMultiSiteAclMigration($db = null)
    {
        if (!$db) {
            $db = db_connect();
        }
        $result = ['step' => 'Multi-Site ACL Migration', 'status' => 'success', 'details' => []];

        try {
            // 1. Create mapping table
            $db->query("CREATE TABLE IF NOT EXISTS `alert_user_client_mapping` (
              `mapping_id` INT AUTO_INCREMENT PRIMARY KEY,
              `user_id` INT NOT NULL,
              `client_id` INT NOT NULL,
              `client_type` ENUM('OE', 'HSE') NOT NULL DEFAULT 'OE',
              `site_name` VARCHAR(255) NOT NULL,
              `cluster_name` VARCHAR(255) NULL,
              `region_name` VARCHAR(255) NULL,
              `created_by` INT NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `status` TINYINT(1) NOT NULL DEFAULT 1,
              INDEX `idx_user_type` (`user_id`, `client_type`),
              INDEX `idx_client_id` (`client_id`, `client_type`),
              INDEX `idx_site_name` (`site_name`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $result['details'][] = "Ensured alert_user_client_mapping table exists";

            $mappingCols = $db->getFieldNames('alert_user_client_mapping');
            if (!in_array('status', $mappingCols)) {
                $db->query("ALTER TABLE `alert_user_client_mapping` ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 1");
                $result['details'][] = "Added status column to alert_user_client_mapping";
            }

            // 1b. Create allocation history table
            $db->query("CREATE TABLE IF NOT EXISTS `alert_user_client_allocation_history` (
              `history_id` INT AUTO_INCREMENT PRIMARY KEY,
              `user_id` INT NOT NULL,
              `client_id` INT NOT NULL,
              `client_type` ENUM('OE', 'HSE') NOT NULL DEFAULT 'OE',
              `site_name` VARCHAR(255) NOT NULL,
              `cluster_name` VARCHAR(255) NULL,
              `region_name` VARCHAR(255) NULL,
              `action` ENUM('ASSIGNED', 'REMOVED') NOT NULL,
              `effective_start_date` DATETIME NOT NULL,
              `effective_end_date` DATETIME NULL,
              `changed_by_user_id` INT NULL,
              `changed_by_user_name` VARCHAR(255) NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              INDEX `idx_hist_user` (`user_id`),
              INDEX `idx_hist_client` (`client_id`, `client_type`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $result['details'][] = "Ensured alert_user_client_allocation_history table exists";

            // 1c. Create NC action history table
            $db->query("CREATE TABLE IF NOT EXISTS `alert_nc_action_history` (
              `history_id` INT AUTO_INCREMENT PRIMARY KEY,
              `module_type` ENUM('OE', 'HSE', 'GEMBA', 'NORMAL') NOT NULL,
              `nc_detail_id` INT NOT NULL,
              `structured_audit_id` INT NULL,
              `site_name` VARCHAR(255) NULL,
              `action_type` VARCHAR(100) NOT NULL,
              `previous_status` VARCHAR(50) NULL,
              `new_status` VARCHAR(50) NULL,
              `performed_by_user_id` INT NOT NULL,
              `performed_by_user_name` VARCHAR(255) NOT NULL,
              `performed_by_designation` VARCHAR(255) NOT NULL,
              `action_remarks` TEXT NULL,
              `evidence_attachment` VARCHAR(500) NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              INDEX `idx_nc_module` (`nc_detail_id`, `module_type`),
              INDEX `idx_parent_audit` (`structured_audit_id`),
              INDEX `idx_user_id` (`performed_by_user_id`),
              INDEX `idx_action_type` (`action_type`),
              INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
            $result['details'][] = "Ensured alert_nc_action_history table exists";

            // 2. Add snapshot columns to audit master tables
            $tables = [
                'alert_final_structured_audit',
                'alert_normal_audit',
                'alert_hse_audit_master',
                'alert_gemba_audits'
            ];

            foreach ($tables as $table) {
                if (!$db->tableExists($table)) continue;
                $cols = $db->getFieldNames($table);

                if (!in_array('snapshot_site_id', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_site_id` INT NULL");
                }
                if (!in_array('snapshot_account_manager_id', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_account_manager_id` INT NULL");
                }
                if (!in_array('snapshot_account_manager_name', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_account_manager_name` VARCHAR(255) NULL");
                }
                if (!in_array('snapshot_cluster_manager_id', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_cluster_manager_id` INT NULL");
                }
                if (!in_array('snapshot_cluster_manager_name', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_cluster_manager_name` VARCHAR(255) NULL");
                }
                if (!in_array('snapshot_created_by_user_id', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_created_by_user_id` INT NULL");
                }
                if (!in_array('snapshot_created_by_user_name', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_created_by_user_name` VARCHAR(255) NULL");
                }
                if (!in_array('snapshot_created_at', $cols)) {
                    $db->query("ALTER TABLE `{$table}` ADD COLUMN `snapshot_created_at` DATETIME NULL");
                }
                $result['details'][] = "Ensured snapshot columns in {$table}";
            }

            // 3. Seed user client mapping from existing client masters if table is empty
            $mappingCount = $db->table('alert_user_client_mapping')->countAllResults();
            if ($mappingCount === 0) {
                // OE Clients seeding
                $oeClients = $db->table('alert_client')->where('status !=', 2)->get()->getResultArray();
                foreach ($oeClients as $c) {
                    // Account manager seeding
                    if (!empty($c['account_manager'])) {
                        $user = $db->table('alert_users')->where('LOWER(TRIM(user_name))', strtolower(trim($c['account_manager'])))->where('status !=', 2)->get()->getRowArray();
                        if ($user) {
                            $db->table('alert_user_client_mapping')->insert([
                                'user_id' => $user['user_id'],
                                'client_id' => $c['client_id'],
                                'client_type' => 'OE',
                                'site_name' => $c['client_name'],
                                'cluster_name' => $c['cluster'] ?? '',
                                'region_name' => $c['region'] ?? '',
                                'status' => 1
                            ]);
                        }
                    }
                    // Cluster manager seeding
                    if (!empty($c['cluster'])) {
                        $user = $db->table('alert_users')->where('LOWER(TRIM(user_name))', strtolower(trim($c['cluster'])))->where('status !=', 2)->get()->getRowArray();
                        if ($user) {
                            $db->table('alert_user_client_mapping')->insert([
                                'user_id' => $user['user_id'],
                                'client_id' => $c['client_id'],
                                'client_type' => 'OE',
                                'site_name' => $c['client_name'],
                                'cluster_name' => $c['cluster'] ?? '',
                                'region_name' => $c['region'] ?? '',
                                'status' => 1
                            ]);
                        }
                    }
                }

                // HSE Clients seeding
                $hseClients = $db->table('alert_hse_client_master')->where('status !=', 2)->get()->getResultArray();
                foreach ($hseClients as $c) {
                    if (!empty($c['account_manager'])) {
                        $user = $db->table('alert_users')->where('LOWER(TRIM(user_name))', strtolower(trim($c['account_manager'])))->where('status !=', 2)->get()->getRowArray();
                        if ($user) {
                            $db->table('alert_user_client_mapping')->insert([
                                'user_id' => $user['user_id'],
                                'client_id' => $c['client_id'],
                                'client_type' => 'HSE',
                                'site_name' => $c['client_name'],
                                'cluster_name' => $c['cluster'] ?? '',
                                'region_name' => $c['region'] ?? '',
                                'status' => 1
                            ]);
                        }
                    }
                    if (!empty($c['cluster'])) {
                        $user = $db->table('alert_users')->where('LOWER(TRIM(user_name))', strtolower(trim($c['cluster'])))->where('status !=', 2)->get()->getRowArray();
                        if ($user) {
                            $db->table('alert_user_client_mapping')->insert([
                                'user_id' => $user['user_id'],
                                'client_id' => $c['client_id'],
                                'client_type' => 'HSE',
                                'site_name' => $c['client_name'],
                                'cluster_name' => $c['cluster'] ?? '',
                                'region_name' => $c['region'] ?? '',
                                'status' => 1
                            ]);
                        }
                    }
                }
                $result['details'][] = "Seeded initial user client mappings";
            }

            // 4. Backfill historical audit snapshots if missing
            if ($db->tableExists('alert_final_structured_audit')) {
                $db->query("UPDATE alert_final_structured_audit SET snapshot_account_manager_name = client_manager_name, snapshot_cluster_manager_name = cluster_name WHERE snapshot_account_manager_name IS NULL OR snapshot_account_manager_name = ''");
            }
            if ($db->tableExists('alert_normal_audit')) {
                $db->query("UPDATE alert_normal_audit SET snapshot_account_manager_name = client_manager_name, snapshot_cluster_manager_name = cluster_name WHERE snapshot_account_manager_name IS NULL OR snapshot_account_manager_name = ''");
            }
            if ($db->tableExists('alert_hse_audit_master')) {
                $db->query("UPDATE alert_hse_audit_master SET snapshot_account_manager_name = account_manager, snapshot_cluster_manager_name = cluster_name WHERE snapshot_account_manager_name IS NULL OR snapshot_account_manager_name = ''");
            }
            if ($db->tableExists('alert_gemba_audits')) {
                $db->query("UPDATE alert_gemba_audits SET snapshot_account_manager_name = account_manager, snapshot_cluster_manager_name = cluster_manager_spoc WHERE snapshot_account_manager_name IS NULL OR snapshot_account_manager_name = ''");
            }
            $result['details'][] = "Backfilled audit historical snapshots";

        } catch (\Throwable $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }

        return $result;
    }
    
    private function addHierarchyColumns($db)
    {
        $result = ['step' => 'Add Hierarchy Columns', 'status' => 'success', 'details' => []];
        
        try {
            // Check if columns already exist
            $clusterColumns = $db->query("SHOW COLUMNS FROM alert_cluster_master LIKE 'region_id'")->getResultArray();
            if(empty($clusterColumns)) {
                $db->query("ALTER TABLE alert_cluster_master ADD COLUMN region_id INT AFTER cluster_name, ADD INDEX idx_region_id (region_id)");
                $result['details'][] = "Added region_id to alert_cluster_master";
            }
            
            $locationColumns = $db->query("SHOW COLUMNS FROM alert_location_master LIKE 'cluster_id'")->getResultArray();
            if(empty($locationColumns)) {
                $db->query("ALTER TABLE alert_location_master ADD COLUMN cluster_id INT AFTER location_name, ADD INDEX idx_cluster_id (cluster_id)");
                $result['details'][] = "Added cluster_id to alert_location_master";
            }
            
            $userColumns = $db->query("SHOW COLUMNS FROM alert_users LIKE 'region_id'")->getResultArray();
            if(empty($userColumns)) {
                $db->query("ALTER TABLE alert_users ADD COLUMN region_id INT AFTER user_cluster, ADD COLUMN cluster_id INT AFTER region_id, ADD COLUMN location_id INT AFTER cluster_id");
                $db->query("ALTER TABLE alert_users ADD INDEX idx_user_region (region_id), ADD INDEX idx_user_cluster (cluster_id), ADD INDEX idx_user_location (location_id)");
                $result['details'][] = "Added hierarchy columns to alert_users";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        return $result;
    }
    
    private function populateClusterRegions($db)
    {
        $result = ['step' => 'Populate Cluster-Region Relationships', 'status' => 'success', 'details' => []];
        
        // Define business logic mapping - adjust based on your actual data
        $clusterRegionMapping = [
            'NORTH' => ['Delhi NCR', 'Punjab', 'Haryana', 'Chandigarh', 'Himachal', 'Uttarakhand'],
            'SOUTH' => ['Chennai', 'Bangalore', 'Hyderabad', 'Kerala', 'Karnataka', 'Tamil Nadu'],
            'EAST' => ['Kolkata', 'Bhubaneswar', 'Guwahati', 'Patna', 'Ranchi', 'West Bengal'],
            'WEST' => ['Mumbai', 'Pune', 'Ahmedabad', 'Surat', 'Rajasthan', 'Gujarat']
        ];
        
        try {
            foreach($clusterRegionMapping as $regionName => $clusters) {
                // Get region ID
                $region = $db->query("SELECT region_id FROM alert_region WHERE region_name = ? LIMIT 1", [$regionName])->getRowArray();
                
                if($region) {
                    $regionId = $region['region_id'];
                    
                    foreach($clusters as $clusterName) {
                        // Update cluster with region_id
                        $updated = $db->query("UPDATE alert_cluster_master SET region_id = ? WHERE cluster_name LIKE ? AND region_id IS NULL", 
                            [$regionId, "%{$clusterName}%"]);
                        
                        if($db->affectedRows() > 0) {
                            $result['details'][] = "Assigned cluster '{$clusterName}' to region '{$regionName}'";
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        return $result;
    }
    
    private function populateLocationClusters($db)
    {
        $result = ['step' => 'Populate Location-Cluster Relationships', 'status' => 'success', 'details' => []];
        
        // Define business logic mapping - adjust based on your actual data
        $locationClusterMapping = [
            'Mumbai' => ['Mumbai Central', 'Mumbai West', 'Navi Mumbai', 'Thane', 'Kalyan'],
            'Delhi NCR' => ['Delhi Office', 'Gurgaon Office', 'Noida Office', 'Faridabad', 'Ghaziabad'],
            'Chennai' => ['Chennai North', 'Chennai South', 'Chennai Central', 'Tambaram'],
            'Bangalore' => ['Bangalore North', 'Bangalore South', 'Electronic City', 'Whitefield'],
            'Pune' => ['Pune Central', 'Pune West', 'Hinjewadi', 'Wakad'],
            'Kolkata' => ['Kolkata North', 'Kolkata South', 'Salt Lake', 'New Town']
        ];
        
        try {
            foreach($locationClusterMapping as $clusterName => $locations) {
                // Get cluster ID
                $cluster = $db->query("SELECT cluster_id FROM alert_cluster_master WHERE cluster_name LIKE ? LIMIT 1", ["%{$clusterName}%"])->getRowArray();
                
                if($cluster) {
                    $clusterId = $cluster['cluster_id'];
                    
                    foreach($locations as $locationName) {
                        // Update location with cluster_id
                        $updated = $db->query("UPDATE alert_location_master SET cluster_id = ? WHERE location_name LIKE ? AND cluster_id IS NULL", 
                            [$clusterId, "%{$locationName}%"]);
                        
                        if($db->affectedRows() > 0) {
                            $result['details'][] = "Assigned location '{$locationName}' to cluster '{$clusterName}'";
                        }
                    }
                }
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        return $result;
    }
    
    private function updateUserHierarchy($db)
    {
        $result = ['step' => 'Update User Hierarchy', 'status' => 'success', 'details' => []];
        
        try {
            // Update region_id based on user_region name
            $updated = $db->query("UPDATE alert_users au 
                                  JOIN alert_region ar ON ar.region_name = au.user_region 
                                  SET au.region_id = ar.region_id 
                                  WHERE au.user_region IS NOT NULL AND au.user_region != '' AND au.region_id IS NULL");
            
            if($db->affectedRows() > 0) {
                $result['details'][] = "Updated {$db->affectedRows()} users with region_id";
            }
            
            // Update cluster_id based on user_cluster name
            $updated = $db->query("UPDATE alert_users au 
                                  JOIN alert_cluster_master acm ON acm.cluster_name = au.user_cluster 
                                  SET au.cluster_id = acm.cluster_id 
                                  WHERE au.user_cluster IS NOT NULL AND au.user_cluster != '' AND au.cluster_id IS NULL");
            
            if($db->affectedRows() > 0) {
                $result['details'][] = "Updated {$db->affectedRows()} users with cluster_id";
            }
            
            // Update location_id based on user_location name
            $updated = $db->query("UPDATE alert_users au 
                                  JOIN alert_location_master alm ON alm.location_name = au.user_location 
                                  SET au.location_id = alm.location_id 
                                  WHERE au.user_location IS NOT NULL AND au.user_location != '' AND au.location_id IS NULL");
            
            if($db->affectedRows() > 0) {
                $result['details'][] = "Updated {$db->affectedRows()} users with location_id";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        return $result;
    }
    
    private function addForeignKeys($db)
    {
        $result = ['step' => 'Add Foreign Key Constraints', 'status' => 'success', 'details' => []];
        
        try {
            // Add foreign key constraints (with error handling for existing constraints)
            $constraints = [
                "ALTER TABLE alert_cluster_master ADD CONSTRAINT fk_cluster_region FOREIGN KEY (region_id) REFERENCES alert_region(region_id) ON DELETE SET NULL",
                "ALTER TABLE alert_location_master ADD CONSTRAINT fk_location_cluster FOREIGN KEY (cluster_id) REFERENCES alert_cluster_master(cluster_id) ON DELETE SET NULL",
                "ALTER TABLE alert_users ADD CONSTRAINT fk_user_region FOREIGN KEY (region_id) REFERENCES alert_region(region_id) ON DELETE SET NULL",
                "ALTER TABLE alert_users ADD CONSTRAINT fk_user_cluster FOREIGN KEY (cluster_id) REFERENCES alert_cluster_master(cluster_id) ON DELETE SET NULL",
                "ALTER TABLE alert_users ADD CONSTRAINT fk_user_location FOREIGN KEY (location_id) REFERENCES alert_location_master(location_id) ON DELETE SET NULL"
            ];
            
            foreach($constraints as $constraint) {
                try {
                    $db->query($constraint);
                    $result['details'][] = "Added foreign key constraint";
                } catch (Exception $e) {
                    if(strpos($e->getMessage(), 'Duplicate key name') !== false) {
                        $result['details'][] = "Foreign key constraint already exists";
                    } else {
                        throw $e;
                    }
                }
            }
            
        } catch (Exception $e) {
            $result['status'] = 'warning';
            $result['details'][] = "Warning: " . $e->getMessage();
        }
        
        return $result;
    }
    
    private function validateDataIntegrity($db)
    {
        $result = ['step' => 'Validate Data Integrity', 'status' => 'success', 'details' => []];
        
        try {
            // Check for orphaned clusters
            $orphanedClusters = $db->query("SELECT cluster_name FROM alert_cluster_master WHERE region_id IS NULL")->getResultArray();
            if(!empty($orphanedClusters)) {
                $result['details'][] = "Warning: " . count($orphanedClusters) . " clusters without regions";
                $result['status'] = 'warning';
            }
            
            // Check for orphaned locations
            $orphanedLocations = $db->query("SELECT location_name FROM alert_location_master WHERE cluster_id IS NULL")->getResultArray();
            if(!empty($orphanedLocations)) {
                $result['details'][] = "Warning: " . count($orphanedLocations) . " locations without clusters";
                $result['status'] = 'warning';
            }
            
            // Check for users with inconsistent hierarchy
            $inconsistentUsers = $db->query("SELECT user_name, user_region, user_cluster, region_id, cluster_id 
                                           FROM alert_users 
                                           WHERE (user_region IS NOT NULL AND region_id IS NULL) 
                                              OR (user_cluster IS NOT NULL AND cluster_id IS NULL)")->getResultArray();
            
            if(!empty($inconsistentUsers)) {
                $result['details'][] = "Warning: " . count($inconsistentUsers) . " users with inconsistent hierarchy";
                $result['status'] = 'warning';
            }
            
            if(empty($orphanedClusters) && empty($orphanedLocations) && empty($inconsistentUsers)) {
                $result['details'][] = "All data integrity checks passed";
            }
            
        } catch (Exception $e) {
            $result['status'] = 'error';
            $result['details'][] = "Error: " . $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Show migration status page
     */
    public function index()
    {
        if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            return redirect()->to(base_url('Login'))->with('error', 'Access denied');
        }
        
        $db = db_connect();
        
        // Check current migration status
        $data['migration_status'] = $this->checkMigrationStatus($db);
        
        return view('Admin/migration_dashboard', $data);
    }
    
    private function checkMigrationStatus($db)
    {
        $status = [];
        
        // Check if hierarchy columns exist
        $clusterColumns = $db->query("SHOW COLUMNS FROM alert_cluster_master LIKE 'region_id'")->getResultArray();
        $status['hierarchy_columns'] = !empty($clusterColumns);
        
        // Check if data is populated
        $populatedClusters = $db->query("SELECT COUNT(*) as count FROM alert_cluster_master WHERE region_id IS NOT NULL")->getRowArray();
        $status['data_populated'] = $populatedClusters['count'] > 0;
        
        // Check foreign keys
        $foreignKeys = $db->query("SELECT COUNT(*) as count FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND CONSTRAINT_NAME LIKE 'fk_%'")->getRowArray();
        $status['foreign_keys'] = $foreignKeys['count'] > 0;
        
        return $status;
    }
}