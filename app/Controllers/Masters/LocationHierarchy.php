<?php

namespace App\Controllers\Masters;
use App\Controllers\BaseController;

class LocationHierarchy extends BaseController
{
    /**
     * Get clusters by region (AJAX endpoint)
     */
    public function getClustersByRegion()
    {
        $request = service('request');
        $regionName = $request->getVar('region');
        
        if (empty($regionName)) {
            return $this->response->setJSON([]);
        }
        
        $db = db_connect();
        
        // Query alert_cluster_master by joining on alert_location_master since region_id doesn't exist in alert_cluster_master
        $sql = "SELECT DISTINCT c.cluster_id, c.cluster_name, c.status 
                FROM alert_cluster_master c
                INNER JOIN alert_location_master l ON c.cluster_name = l.cluster_name
                WHERE l.region_name = ? AND c.status = 1
                ORDER BY c.cluster_name";
        
        $clusters = $db->query($sql, [$regionName])->getResultArray();
        
        return $this->response->setJSON($clusters);
    }
    
    /**
     * Get locations by cluster (AJAX endpoint)  
     */
    public function getLocationsByCluster()
    {
        $request = service('request');
        $clusterName = $request->getVar('cluster');
        
        if (empty($clusterName)) {
            return $this->response->setJSON([]);
        }
        
        $db = db_connect();
        
        // Select MIN(location_id) and GROUP BY location_name to avoid duplicates
        $sql = "SELECT MIN(l.location_id) as location_id, l.location_name, l.status
                FROM alert_location_master l
                WHERE l.cluster_name = ? AND l.status = 1
                GROUP BY l.location_name
                ORDER BY l.location_name";
        
        $locations = $db->query($sql, [$clusterName])->getResultArray();
        
        return $this->response->setJSON($locations);
    }
    
    /**
     * Get regions by country (AJAX endpoint)
     */
    public function getRegionsByCountry()
    {
        $request = service('request');
        $country = $request->getVar('country');
        
        if (empty($country)) {
            return $this->response->setJSON([]);
        }
        
        $db = db_connect();
        
        // For now, return all regions - later can add country-region relationship
        $sql = "SELECT region_id, region_name, status 
                FROM alert_region 
                WHERE status = 1
                ORDER BY region_name";
        
        $regions = $db->query($sql)->getResultArray();
        
        return $this->response->setJSON($regions);
    }
    
    /**
     * Validate hierarchy consistency
     */
    public function validateHierarchy()
    {
        $request = service('request');
        $regionName = $request->getVar('region');
        $clusterName = $request->getVar('cluster');
        $locationName = $request->getVar('location');
        
        $db = db_connect();
        $isValid = true;
        $errors = [];
        
        // Check if cluster belongs to the selected region
        if ($regionName && $clusterName) {
            $sql = "SELECT COUNT(*) as count 
                    FROM alert_location_master
                    WHERE region_name = ? AND cluster_name = ?";
            
            $result = $db->query($sql, [$regionName, $clusterName])->getRowArray();
            
            if ($result['count'] == 0) {
                $isValid = false;
                $errors[] = "Selected cluster does not belong to the selected region";
            }
        }
        
        // Check if location belongs to the selected cluster
        if ($clusterName && $locationName) {
            $sql = "SELECT COUNT(*) as count 
                    FROM alert_location_master
                    WHERE cluster_name = ? AND location_name = ?";
            
            $result = $db->query($sql, [$clusterName, $locationName])->getRowArray();
            
            if ($result['count'] == 0) {
                $isValid = false;
                $errors[] = "Selected location does not belong to the selected cluster";
            }
        }
        
        return $this->response->setJSON([
            'valid' => $isValid,
            'errors' => $errors
        ]);
    }
    
    /**
     * Get complete hierarchy for a user
     */
    public function getUserHierarchy($userId)
    {
        $db = db_connect();
        
        $user = $db->table('alert_users')->where('user_id', $userId)->get()->getRowArray();
        if (!$user) {
            return $this->response->setJSON([]);
        }
        
        $regionId = '';
        if (!empty($user['user_region'])) {
            $r = $db->table('alert_region')->where('region_name', $user['user_region'])->get()->getRowArray();
            $regionId = $r ? $r['region_id'] : '';
        }
        
        $clusterId = '';
        if (!empty($user['user_cluster'])) {
            $c = $db->table('alert_cluster_master')->where('cluster_name', $user['user_cluster'])->get()->getRowArray();
            $clusterId = $c ? $c['cluster_id'] : '';
        }
        
        $locationId = '';
        if (!empty($user['user_location'])) {
            $l = $db->table('alert_location_master')->where('location_name', $user['user_location'])->get()->getRowArray();
            $locationId = $l ? $l['location_id'] : '';
        }
        
        return $this->response->setJSON([
            'user_id' => $user['user_id'],
            'user_name' => $user['user_name'],
            'region_id' => $regionId,
            'region_name' => $user['user_region'],
            'cluster_id' => $clusterId,
            'cluster_name' => $user['user_cluster'],
            'location_id' => $locationId,
            'location_name' => $user['user_location']
        ]);
    }
}