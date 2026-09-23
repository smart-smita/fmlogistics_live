<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

// Update Alfa Laval client cluster to 'Smita'
$mysqli->query("UPDATE alert_client SET cluster = 'Smita' WHERE client_name = 'Alfa Laval'");

// Update user mapping for Smita so she has the cluster
$mysqli->query("UPDATE alert_user_client_mapping SET cluster_name = 'Smita' WHERE user_id = 1 AND client_id = 1");

echo "Updated Alfa Laval cluster to Smita!";
?>
