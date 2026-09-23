<?php
$mysqli = new mysqli("localhost", "root", "", "fmlogistic");

if ($mysqli->connect_error) {
  die("Connection failed: " . $mysqli->connect_error);
}

$sql1 = "ALTER TABLE alert_user_client_mapping DROP INDEX user_client_unique";
if ($mysqli->query($sql1) === TRUE) {
    echo "Dropped old unique index.<br/>";
} else {
    echo "Error dropping index: " . $mysqli->error . "<br/>";
}

$sql2 = "ALTER TABLE alert_user_client_mapping ADD UNIQUE INDEX user_client_unique (user_id, client_id, client_type)";
if ($mysqli->query($sql2) === TRUE) {
    echo "Added new unique index.<br/>";
} else {
    echo "Error adding new index: " . $mysqli->error . "<br/>";
}

$mysqli->close();
?>
