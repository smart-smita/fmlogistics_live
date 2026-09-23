<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");
$res = $mysqli->query("SELECT * FROM alert_final_structured_audit WHERE client_name = 'constructor'");
echo "Constructor audits count: " . $res->num_rows . "\n";
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
