<?php

namespace App\Controllers\Debug;
use App\Controllers\BaseController;

class ACLDebug extends BaseController
{
    public function index()
    {
        helper(['acl']);
        
        echo "<h2>ACL Debug Information</h2>";
        echo "<h3>Current Session:</h3>";
        echo "<pre>";
        print_r($_SESSION);
        echo "</pre>";
        
        echo "<h3>ACL Helper Tests:</h3>";
        
        // Test ACL condition for audits
        $aclCondition = getRegionalClusterCondition('', 'audits');
        echo "<p><strong>ACL Condition for audits:</strong> <code>{$aclCondition}</code></p>";
        
        // Test ACL condition for users
        $aclConditionUsers = getRegionalClusterCondition('', 'users');
        echo "<p><strong>ACL Condition for users:</strong> <code>{$aclConditionUsers}</code></p>";
        
        // Test region access
        $regions = ['NORTH', 'SOUTH', 'EAST', 'WEST'];
        echo "<h3>Region Access Tests:</h3>";
        foreach($regions as $region) {
            $canAccess = canAccessRegion($region);
            $status = $canAccess ? '✅ CAN ACCESS' : '❌ NO ACCESS';
            echo "<p><strong>{$region}:</strong> {$status}</p>";
        }
        
        // Test audit permissions
        echo "<h3>Audit Permission Tests:</h3>";
        $auditTypes = ['structure', 'normal', 'hse'];
        foreach($auditTypes as $type) {
            $canPerform = canPerformAudit($type);
            $status = $canPerform ? '✅ CAN PERFORM' : '❌ READ ONLY';
            echo "<p><strong>{$type} audit:</strong> {$status}</p>";
        }
        
        // Test database queries
        echo "<h3>Database Query Tests:</h3>";
        $db = db_connect();
        
        // Test 1: All audit templates
        $allAudits = $db->query("SELECT COUNT(*) as count FROM alert_audit_template")->getRowArray();
        echo "<p><strong>Total audit templates:</strong> {$allAudits['count']}</p>";
        
        // Test 2: SOUTH region audit templates
        $southAudits = $db->query("SELECT COUNT(*) as count FROM alert_audit_template WHERE region = 'SOUTH'")->getRowArray();
        echo "<p><strong>SOUTH region audit templates:</strong> {$southAudits['count']}</p>";
        
        // Test 3: With ACL filtering
        $sql = "SELECT COUNT(*) as count FROM alert_audit_template WHERE 1=1 {$aclCondition}";
        $filteredAudits = $db->query($sql)->getRowArray();
        echo "<p><strong>ACL filtered audit templates:</strong> {$filteredAudits['count']}</p>";
        
        // Test 4: HSE audit master
        $allHSE = $db->query("SELECT COUNT(*) as count FROM alert_hse_audit_master")->getRowArray();
        echo "<p><strong>Total HSE audits:</strong> {$allHSE['count']}</p>";
        
        $southHSE = $db->query("SELECT COUNT(*) as count FROM alert_hse_audit_master WHERE region = 'SOUTH'")->getRowArray();
        echo "<p><strong>SOUTH region HSE audits:</strong> {$southHSE['count']}</p>";
        
        $sql2 = "SELECT COUNT(*) as count FROM alert_hse_audit_master WHERE 1=1 {$aclCondition}";
        $filteredHSE = $db->query($sql2)->getRowArray();
        echo "<p><strong>ACL filtered HSE audits:</strong> {$filteredHSE['count']}</p>";
        
        // Test 5: Structure audits
        $allStructure = $db->query("SELECT COUNT(*) as count FROM alert_final_structured_audit")->getRowArray();
        echo "<p><strong>Total structure audits:</strong> {$allStructure['count']}</p>";
        
        $southStructure = $db->query("SELECT COUNT(*) as count FROM alert_final_structured_audit WHERE region = 'SOUTH'")->getRowArray();
        echo "<p><strong>SOUTH region structure audits:</strong> {$southStructure['count']}</p>";
        
        $sql3 = "SELECT COUNT(*) as count FROM alert_final_structured_audit WHERE 1=1 {$aclCondition}";
        $filteredStructure = $db->query($sql3)->getRowArray();
        echo "<p><strong>ACL filtered structure audits:</strong> {$filteredStructure['count']}</p>";
        
        echo "<h3>Sample Data:</h3>";
        echo "<h4>Sample Audit Templates (first 5):</h4>";
        $sampleAudits = $db->query("SELECT audit_template_id, audit_name, region FROM alert_audit_template LIMIT 5")->getResultArray();
        echo "<pre>";
        print_r($sampleAudits);
        echo "</pre>";
        
        echo "<h4>Sample HSE Audits (first 5):</h4>";
        $sampleHSE = $db->query("SELECT hse_audit_id, audit_name, region FROM alert_hse_audit_master LIMIT 5")->getResultArray();
        echo "<pre>";
        print_r($sampleHSE);
        echo "</pre>";
        
        echo "<h4>Sample Structure Audits (first 5):</h4>";
        $sampleStructure = $db->query("SELECT structured_audit_id, audit_name, region FROM alert_final_structured_audit LIMIT 5")->getResultArray();
        echo "<pre>";
        print_r($sampleStructure);
        echo "</pre>";
    }
    
    public function testClientNCTracker()
    {
        helper(['acl']);
        $db = db_connect();
        
        echo "<h2>Client NC Tracker Debug</h2>";
        
        // Test the exact query used in Client NC Tracker
        $aclCondition = getRegionalClusterCondition('alert_hse_audit_master', 'audits');
        echo "<p><strong>ACL Condition:</strong> <code>{$aclCondition}</code></p>";
        
        $sql = "SELECT 
                    alert_hse_audit_details.id,
                    alert_hse_audit_master.audit_name, 
                    alert_hse_audit_master.region,
                    alert_hse_audit_master.location
                FROM alert_hse_audit_details
                LEFT JOIN alert_hse_audit_master ON alert_hse_audit_master.hse_audit_id = alert_hse_audit_details.hse_audit_id
                WHERE alert_hse_audit_details.client_leased = 'NO' {$aclCondition}
                LIMIT 10";
        
        echo "<h3>Query:</h3>";
        echo "<pre>{$sql}</pre>";
        
        $results = $db->query($sql)->getResultArray();
        echo "<h3>Results:</h3>";
        echo "<pre>";
        print_r($results);
        echo "</pre>";
        
        echo "<p><strong>Total results:</strong> " . count($results) . "</p>";
    }
}