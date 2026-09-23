<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = \Config\Database::connect();
$users = $db->table('alert_users')->get()->getResultArray();
foreach ($users as $u) {
    echo $u['user_id'] . " - " . $u['user_name'] . " - " . $u['user_email'] . " - " . $u['default_date'] . "\n";
}
?>
