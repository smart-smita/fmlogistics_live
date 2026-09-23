<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$session = \Config\Services::session();
$session->set('user_id', 1); // Smita
$session->set('user_designation', 'Cluster manager');

helper('designation_acl');
$db = \Config\Database::connect();

$selected_region = ['North'];
$cluster_name = [];
$location_name = [];
$selected_category = [];

$oeAclFilter = getOEAuditACLWhere('audit', 'OE');

$sql = "
    SELECT 
        sub_a.audit_score AS score
    FROM (
        SELECT 
            audit.audit_score
        FROM alert_final_structured_audit audit
        WHERE audit.region IS NOT NULL AND audit.region <> '' {$oeAclFilter}
        AND audit.region IN ('North')
    ) sub_a
";

echo "SQL:\n" . $sql . "\n\n";
$res = $db->query($sql)->getResultArray();
echo "Results count: " . count($res) . "\n";
print_r($res);
?>
