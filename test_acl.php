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
echo "ACL Query:\n";
echo getOEAuditACLWhere('audit', 'OE');
?>
