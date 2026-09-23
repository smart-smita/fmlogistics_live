<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}

$columns = [
    'region_id' => 'INT(11) NULL DEFAULT 0',
    'cluster_id' => 'INT(11) NULL DEFAULT 0',
    'location_id' => 'INT(11) NULL DEFAULT 0'
];

foreach ($columns as $col => $def) {
    $sql = "ALTER TABLE alert_users ADD COLUMN $col $def";
    if ($mysqli->query($sql) === TRUE) {
        echo "Added $col to alert_users<br/>";
    } else {
        echo "Error adding $col: " . $mysqli->error . "<br/>";
    }
}

$mysqli->close();
?>
