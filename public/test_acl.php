<?php
// Simple ACL test page - Access this directly at /test_acl.php
session_start();

// Include CodeIgniter database connection
require_once '../app/Config/Database.php';

function db_connect() {
    $config = new \Config\Database();
    $db = \Config\Database::connect();
    return $db;
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>ACL Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { background: #f8f9fa; padding: 20px; margin: 20px 0; border-radius: 5px; border-left: 5px solid #007bff; }
        .error { border-left-color: #dc3545; background: #f8d7da; }
        .success { border-left-color: #28a745; background: #d4edda; }
        .warning { border-left-color: #ffc107; background: #fff3cd; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>🔍 ACL Test Page</h1>
    
    <div class="section">
        <h2>📋 Session Data</h2>
        <table>
            <tr><th>Variable</th><th>Value</th></tr>
            <tr><td>user_name</td><td><?= $_SESSION['user_name'] ?? '<span style="color:red;">NOT SET</span>' ?></td></tr>
            <tr><td>user_designation</td><td><?= $_SESSION['user_designation'] ?? '<span style="color:red;">NOT SET</span>' ?></td></tr>
            <tr><td>user_region</td><td><?= $_SESSION['user_region'] ?? '<span style="color:red;">NOT SET</span>' ?></td></tr>
            <tr><td>user_cluster</td><td><?= $_SESSION['user_cluster'] ?? '<span style="color:red;">NOT SET</span>' ?></td></tr>
            <tr><td>role</td><td><?= $_SESSION['role'] ?? '<span style="color:red;">NOT SET</span>' ?></td></tr>
        </table>
    </div>

    <?php
    try {
        $db = db_connect();
        
        // Test 1: Check OE Audit data
        echo '<div class="section">';
        echo '<h2>📊 OE Audit Log Data</h2>';
        
        $totalQuery = "SELECT COUNT(*) as total FROM alert_final_structured_audit";
        $totalResult = $db->query($totalQuery)->getRowArray();
        echo "<p><strong>Total OE audit records:</strong> " . ($totalResult['total'] ?? 0) . "</p>";
        
        if ($totalResult['total'] > 0) {
            // Show by region
            $regionQuery = "SELECT region, COUNT(*) as count FROM alert_final_structured_audit GROUP BY region ORDER BY region";
            $regionResults = $db->query($regionQuery)->getResultArray();
            
            echo "<h3>By Region:</h3>";
            echo "<table>";
            echo "<tr><th>Region</th><th>Count</th></tr>";
            foreach ($regionResults as $region) {
                $highlight = ($region['region'] === ($_SESSION['user_region'] ?? '')) ? 'style="background:yellow;"' : '';
                echo "<tr {$highlight}><td>" . htmlspecialchars($region['region']) . "</td><td>" . $region['count'] . "</td></tr>";
            }
            echo "</table>";
            
            // Test ACL filtering
            $userRegion = $_SESSION['user_region'] ?? '';
            $isClusterManager = (isset($_SESSION['user_designation']) && $_SESSION['user_designation'] === 'Cluster manager');
            
            if ($isClusterManager && !empty($userRegion)) {
                $aclQuery = "SELECT COUNT(*) as filtered_total FROM alert_final_structured_audit WHERE region = '" . $db->escapeString($userRegion) . "'";
                $aclResult = $db->query($aclQuery)->getRowArray();
                
                echo "<h3>🔒 ACL Filtered (for {$userRegion}):</h3>";
                echo "<p><strong>Records visible to cluster manager:</strong> " . ($aclResult['filtered_total'] ?? 0) . "</p>";
                
                if ($aclResult['filtered_total'] == 0) {
                    echo '<p style="color:red;"><strong>❌ This is why "Showing no records" appears!</strong></p>';
                }
            }
        } else {
            echo '<p style="color:red;"><strong>❌ No OE audit data in database</strong></p>';
        }
        echo '</div>';
        
        // Test 2: Check User data
        echo '<div class="section">';
        echo '<h2>👥 User Data</h2>';
        
        $userTotalQuery = "SELECT COUNT(*) as total FROM alert_users";
        $userTotalResult = $db->query($userTotalQuery)->getRowArray();
        echo "<p><strong>Total users:</strong> " . ($userTotalResult['total'] ?? 0) . "</p>";
        
        if ($userTotalResult['total'] > 0) {
            // Show by region
            $userRegionQuery = "SELECT user_region, COUNT(*) as count FROM alert_users WHERE user_region IS NOT NULL AND user_region != '' GROUP BY user_region ORDER BY user_region";
            $userRegionResults = $db->query($userRegionQuery)->getResultArray();
            
            echo "<h3>By Region:</h3>";
            echo "<table>";
            echo "<tr><th>Region</th><th>Count</th></tr>";
            foreach ($userRegionResults as $region) {
                $highlight = ($region['user_region'] === ($_SESSION['user_region'] ?? '')) ? 'style="background:yellow;"' : '';
                echo "<tr {$highlight}><td>" . htmlspecialchars($region['user_region']) . "</td><td>" . $region['count'] . "</td></tr>";
            }
            echo "</table>";
            
            // Test user ACL filtering
            $userRegion = $_SESSION['user_region'] ?? '';
            $isClusterManager = (isset($_SESSION['user_designation']) && $_SESSION['user_designation'] === 'Cluster manager');
            
            if ($isClusterManager && !empty($userRegion)) {
                $userAclQuery = "SELECT COUNT(*) as filtered_total FROM alert_users WHERE user_region = '" . $db->escapeString($userRegion) . "'";
                $userAclResult = $db->query($userAclQuery)->getRowArray();
                
                echo "<h3>🔒 User ACL Filtered (for {$userRegion}):</h3>";
                echo "<p><strong>Users visible to cluster manager:</strong> " . ($userAclResult['filtered_total'] ?? 0) . "</p>";
            }
        }
        echo '</div>';
        
        // Test 3: ACL Status
        echo '<div class="section">';
        echo '<h2>🔒 ACL Status</h2>';
        
        $isClusterManager = (isset($_SESSION['user_designation']) && $_SESSION['user_designation'] === 'Cluster manager');
        $userRegion = $_SESSION['user_region'] ?? '';
        
        if ($isClusterManager) {
            if (!empty($userRegion)) {
                echo '<p style="color:green;"><strong>✅ ACL Active:</strong> Filtering by region "' . htmlspecialchars($userRegion) . '"</p>';
                echo '<p><strong>Expected SQL condition:</strong> AND region = \'' . htmlspecialchars($userRegion) . '\'</p>';
            } else {
                echo '<p style="color:red;"><strong>❌ ACL Problem:</strong> Cluster manager but no region set</p>';
            }
        } else {
            echo '<p style="color:blue;"><strong>ℹ️ No ACL:</strong> Not a cluster manager - should see all data</p>';
        }
        echo '</div>';
        
    } catch (Exception $e) {
        echo '<div class="section error">';
        echo '<h2>❌ Database Error</h2>';
        echo '<p>' . htmlspecialchars($e->getMessage()) . '</p>';
        echo '</div>';
    }
    ?>
    
    <div class="section warning">
        <h2>📝 Next Steps</h2>
        <ol>
            <li>If session data is missing, check login process</li>
            <li>If no OE audit data exists, that explains "Showing no records"</li>
            <li>If data exists but ACL shows 0 records, check region names match exactly</li>
            <li>Remove this test file after debugging</li>
        </ol>
    </div>
</body>
</html>