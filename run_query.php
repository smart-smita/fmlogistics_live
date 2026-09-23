<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}

// Add client_type, site_name, cluster_name, region_name, created_by
$sql = "ALTER TABLE alert_user_client_mapping 
        ADD COLUMN client_type VARCHAR(50) NULL AFTER client_id, 
        ADD COLUMN site_name VARCHAR(255) NULL AFTER client_type, 
        ADD COLUMN cluster_name VARCHAR(255) NULL AFTER site_name, 
        ADD COLUMN region_name VARCHAR(255) NULL AFTER cluster_name, 
        ADD COLUMN created_by INT(11) NULL DEFAULT 0 AFTER region_name";

if ($mysqli->query($sql) === TRUE) {
  echo "Table altered successfully. ";
} else {
  echo "Error altering table: " . $mysqli->error . ". ";
}

// Let's also check if snapshot_site_id needs to be added to alert_users
$sql2 = "ALTER TABLE alert_users ADD COLUMN snapshot_site_id INT(11) NULL DEFAULT 0 AFTER location_id";
if ($mysqli->query($sql2) === TRUE) {
  echo "alert_users altered successfully.";
} else {
  echo "Error altering alert_users: " . $mysqli->error;
}

$mysqli->close();
?>
