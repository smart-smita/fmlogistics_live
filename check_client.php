<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");
$res = $mysqli->query("SELECT * FROM alert_client WHERE client_name = 'constructor'");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
