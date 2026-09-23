<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

$res = $mysqli->query("UPDATE alert_final_structured_audit SET cluster_name = 'Smita', snapshot_cluster_manager_name = 'Smita' WHERE client_name = 'Alfa Laval'");

echo "Updated " . $mysqli->affected_rows . " rows in alert_final_structured_audit.\n";
?>
