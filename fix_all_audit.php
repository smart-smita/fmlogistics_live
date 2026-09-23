<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

$res = $mysqli->query("
    UPDATE alert_final_structured_audit a
    JOIN alert_client c ON a.client_name = c.client_name
    SET a.cluster_name = c.cluster, 
        a.snapshot_cluster_manager_name = c.cluster
    WHERE (a.cluster_name = '' OR a.cluster_name IS NULL)
");

echo "Updated " . $mysqli->affected_rows . " rows in alert_final_structured_audit.\n";

$res2 = $mysqli->query("SELECT * FROM alert_final_structured_audit ORDER BY structured_audit_id DESC LIMIT 5");
while ($row = $res2->fetch_assoc()) {
    print_r($row);
}
?>
