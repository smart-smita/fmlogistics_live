<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

use App\Models\CRUDBaseModel;
$dbParams = [
    'table' => 'alert_users',
    'allowedFields' => ['user_name', 'user_email', 'user_password'],
    'primaryKey' => 'user_id'
];
$model = new CRUDBaseModel($dbParams);

$model->update(1, [
    'user_name' => 'Smita',
    'user_email' => 'smita.tikone@unitglo.com',
    'user_password' => 'Smita@123'
]);

$model->update(2, [
    'user_name' => 'Shivani',
    'user_email' => 'shivanishinde0024@gmail.com',
    'user_password' => 'Shivani@123'
]);

echo "Restored original data for Smita and Shivani.\n";
?>
