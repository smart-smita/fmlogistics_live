<?php
require 'system/bootstrap.php';
$db = \Config\Database::connect();
$tables = $db->listTables();
foreach(['hse_nc_tracker', 'oe_nc_tracker', 'client_nc_tracker', 'fm_nc_tracker'] as $t) {
    if(in_array($t, $tables)) {
        echo "$t:\n";
        print_r($db->getFieldNames($t));
        echo "\n";
    }
}
