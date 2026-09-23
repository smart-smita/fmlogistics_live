<?php
$db = new mysqli('localhost', 'root', '', 'fmlogistics');
$res = $db->query("SHOW COLUMNS FROM alert_hse_audit_master");
while ($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
