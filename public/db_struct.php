<?php
// We have to bootstrap CodeIgniter to use db_connect()
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . '../app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = db_connect();
function get_cols($table, $db) {
    try {
        $fields = $db->getFieldNames($table);
        return implode(', ', $fields);
    } catch (Exception $e) { return 'Table not found'; }
}
echo 'alert_client: ' . get_cols('alert_client', $db) . "\n";
echo 'alert_hse_client_master: ' . get_cols('alert_hse_client_master', $db) . "\n";
echo 'alert_location_master: ' . get_cols('alert_location_master', $db) . "\n";
