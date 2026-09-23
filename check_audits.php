<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");
$res = $mysqli->query("SELECT * FROM alert_final_structured_audit ORDER BY structured_audit_id DESC LIMIT 5");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
