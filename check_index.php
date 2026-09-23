<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}

$res = $mysqli->query("SHOW INDEX FROM alert_user_client_mapping");
while ($row = $res->fetch_assoc()) {
    echo $row['Key_name'] . " -> " . $row['Column_name'] . "<br/>";
}

$mysqli->close();
?>
