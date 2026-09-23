<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

$res = $mysqli->query("UPDATE alert_final_structured_audit SET cluster_name = 'Smita', snapshot_cluster_manager_name = 'Smita' WHERE structured_audit_id = 1");

echo "Updated " . $mysqli->affected_rows . " rows in alert_final_structured_audit.\n";
?>
