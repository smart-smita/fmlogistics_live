<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = \Config\Database::connect();
$fields = $db->getFieldData('alert_users');
foreach ($fields as $f) {
    echo $f->name . " (PRI: " . $f->primary_key . ")\n";
}
?>
