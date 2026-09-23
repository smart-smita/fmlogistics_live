<?php
$db = mysqli_connect('localhost', 'root', '', 'fmlogistic');
if (!$db) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "=== Created Users ===\n";
$res = mysqli_query($db, "SELECT user_id, user_name, user_designation, user_cluster FROM alert_users WHERE user_name LIKE 'Test%'");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
echo "=== Added Clusters ===\n";
$res = mysqli_query($db, "SELECT cluster_id, cluster_name, status FROM alert_cluster_master WHERE cluster_name LIKE 'Test%'");
while($row = mysqli_fetch_assoc($res)) {
    print_r($row);
}
mysqli_close($db);
