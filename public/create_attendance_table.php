<?php
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
chdir(__DIR__ . '/../');
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$db = \Config\Database::connect();

$sql = "CREATE TABLE IF NOT EXISTS `alert_hse_audit_attendance` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `hse_audit_id` int(11) NOT NULL,
    `row_no` int(11) NOT NULL,
    `auditee_attendance` varchar(255) DEFAULT NULL,
    `opening_sign` varchar(255) DEFAULT NULL,
    `closing_sign` varchar(255) DEFAULT NULL,
    `created_by` varchar(50) DEFAULT NULL,
    `updated_by` varchar(50) DEFAULT NULL,
    `created_at` datetime DEFAULT current_timestamp(),
    `updated_at` datetime DEFAULT NULL,
    `status` int(11) DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($db->query($sql)) {
    echo "Table alert_hse_audit_attendance created successfully.\n";
} else {
    print_r($db->error());
}
