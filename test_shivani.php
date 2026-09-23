<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$session = \Config\Services::session();
$session->set('user_id', 2); // Shivani
$session->set('user_designation', 'Account Manager');
$session->set('role', 'Account Manager');
$session->set('user_name', 'Shivani');

helper('designation_acl');
$db = \Config\Database::connect();

$oeAclFilter = getOEAuditACLWhere('audit', 'OE');
echo "Shivani OE ACL Filter:\n" . $oeAclFilter . "\n\n";

$sql = "
    SELECT 
        sub_a.audit_score AS score
    FROM (
        SELECT 
            audit.audit_score
        FROM alert_final_structured_audit audit
        WHERE audit.region IS NOT NULL AND audit.region <> '' {$oeAclFilter}
    ) sub_a
";
echo "SQL:\n" . $sql . "\n\n";
$res = $db->query($sql)->getResultArray();
echo "Results count: " . count($res) . "\n";
print_r($res);
?>
