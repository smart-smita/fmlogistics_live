<?php
require 'app/Config/Database.php';
$db = \Config\Database::connect();
$db->query('INSERT IGNORE INTO alert_hse_region_master (region_name, status, default_date, update_date) SELECT region_name, status, default_date, update_date FROM alert_region');
echo "Merged successfully\n";
