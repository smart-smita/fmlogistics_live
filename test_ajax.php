<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$session = \Config\Services::session();
$session->set('user_id', 1);
$session->set('user_name', 'Balamurugan');
$session->set('role', 'Admin');

$_SERVER['REQUEST_METHOD'] = 'POST';

$controller = new \App\Controllers\Masters\User();
$response = $controller->table_ajax();

echo json_encode(json_decode($response->getBody()), JSON_PRETTY_PRINT);
?>
