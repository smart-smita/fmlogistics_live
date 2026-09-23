<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Models\CRUDBaseModel;
$dbParams = [
    'table' => 'alert_users',
    'allowedFields' => ['user_email'],
    'primaryKey' => 'user_id'
];
$model = new CRUDBaseModel($dbParams);

$model->update(1, ['user_email' => 'smita1@unitglo.com']);

$db = \Config\Database::connect();
$users = $db->table('alert_users')->get()->getResultArray();
foreach ($users as $u) {
    echo $u['user_id'] . " - " . $u['user_email'] . "\n";
}
?>
