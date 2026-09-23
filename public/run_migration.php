<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

if ($mysqli->connect_errno) {
    echo "Failed to connect to MySQL: " . $mysqli->connect_error;
    exit();
}

$query = "CREATE TABLE IF NOT EXISTS `software_definitions` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` VARCHAR(255) NOT NULL,
    `answer` LONGTEXT NOT NULL,
    `category` VARCHAR(100) NOT NULL,
    `sub_category` VARCHAR(100) NULL,
    `keywords` VARCHAR(255) NULL,
    `status` ENUM('Active', 'Inactive', 'Archived') NOT NULL DEFAULT 'Active',
    `display_order` INT(11) NOT NULL DEFAULT 0,
    `created_by` INT(11) NULL,
    `updated_by` INT(11) NULL,
    `is_deleted` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NULL,
    `updated_at` DATETIME NULL,
    `deleted_at` DATETIME NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

if (!$mysqli->query($query)) {
    echo "Table creation failed: (" . $mysqli->errno . ") " . $mysqli->error . "\n";
} else {
    echo "Table software_definitions created successfully.\n";
}
