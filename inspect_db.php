<?php
require 'vendor/autoload.php';

// Bootstrap CodeIgniter framework environment
define('FCPATH', __DIR__ . '/public/');
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();
$tables = $db->listTables();

echo "=== ALL TABLES AND CLIENT/SITE COLUMNS ===\n";
$matching_tables = [];

foreach ($tables as $table) {
    $fields = $db->getFieldData($table);
    $found = [];
    foreach ($fields as $field) {
        $name = strtolower($field->name);
        if (strpos($name, 'client') !== false || strpos($name, 'site') !== false || strpos($name, 'location') !== false) {
            $found[] = "{$field->name} ({$field->type})";
        }
    }
    if (!empty($found)) {
        $matching_tables[$table] = $found;
        echo "Table: {$table}\n";
        foreach ($found as $col) {
            echo "  - {$col}\n";
        }
    }
}

echo "\nTotal tables checked: " . count($tables) . "\n";
echo "Total matching tables: " . count($matching_tables) . "\n";
