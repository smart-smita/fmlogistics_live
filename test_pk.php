<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Models\CRUDBaseModel;
$dbParams = [
    'table' => 'alert_users',
    'allowedFields' => ['user_name'],
    'primaryKey' => 'user_id'
];
$model = new CRUDBaseModel($dbParams);
echo "Primary Key is: " . $model->primaryKey . "\n";
?>
