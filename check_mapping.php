<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");
$res = $mysqli->query("SELECT * FROM alert_user_client_mapping WHERE user_id = 1");
while ($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
