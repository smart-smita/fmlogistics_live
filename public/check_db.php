<?php
$db = new mysqli('localhost', 'root', '', 'fmlogistic'); // Adjust credentials if needed
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

echo "Tables:\n";
$result = $db->query("SHOW TABLES");
if ($result) {
    while ($row = $result->fetch_array()) {
        echo " - " . $row[0] . "\n";
    }
} else {
    echo "Error: " . $db->error . "\n";
}
