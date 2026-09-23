<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

$res = $mysqli->query("UPDATE alert_final_structured_audit SET cluster_name = 'Smita', snapshot_cluster_manager_name = 'Smita' WHERE audit_no = 'OE3.O-2026-09-23-297'");

echo "Updated " . $mysqli->affected_rows . " rows in alert_final_structured_audit.\n";
?>
