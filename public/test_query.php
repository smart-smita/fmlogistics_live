<?php
$db = mysqli_connect('localhost','root','','smart-smita');

$master = $db->query('SELECT * FROM alert_hse_audit_master ORDER BY hse_audit_id DESC LIMIT 1')->fetch_assoc();
print_r($master);

$details = $db->query('SELECT * FROM alert_hse_audit_details WHERE hse_audit_id = ' . $master['hse_audit_id'])->fetch_all(MYSQLI_ASSOC);
print_r($details);

$gemba = $db->query('SELECT * FROM alert_gemba_audits WHERE source_module="hse_audit" AND hse_audit_id = ' . $master['hse_audit_id'])->fetch_all(MYSQLI_ASSOC);
print_r($gemba);
