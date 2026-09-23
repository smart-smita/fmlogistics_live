<?php
// load CI
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require FCPATH . 'app/Config/Paths.php';
$paths = new Config\Paths();
require rtrim($paths->systemDirectory, '\\/ ') . DIRECTORY_SEPARATOR . 'bootstrap.php';

$session = \Config\Services::session();
$session->set('user_id', 1);
$session->set('user_name', 'Balamurugan');
$session->set('role', 'Admin');

$db = db_connect();
$builder = $db->table('alert_users');
$builder->where('status !=', 2);
$builder->orderBy('user_id', 'DESC');
$table_data = $builder->get()->getResultArray();

$userIds = array_column($table_data, 'user_id');
if (!empty($userIds)) {
    $mappings = $db->table('alert_user_client_mapping')
                   ->whereIn('user_id', $userIds)
                   ->where('status', 1)
                   ->get()->getResultArray();
    $userMappingData = [];
    foreach ($mappings as $m) {
        $userMappingData[$m['user_id']]['sites'][] = $m['site_name'];
        $userMappingData[$m['user_id']]['clusters'][] = $m['cluster_name'];
    }
    
    foreach ($table_data as &$row) {
        $uid = $row['user_id'];
        if (isset($userMappingData[$uid])) {
            $sites = array_unique(array_filter($userMappingData[$uid]['sites']));
            $clusters = array_unique(array_filter($userMappingData[$uid]['clusters']));
            $row['user_location'] = implode(', ', $sites);
            $row['user_cluster'] = implode(', ', $clusters);
        } else if (in_array($row['user_designation'], ['Cluster manager', 'Account Manager'])) {
            $row['user_location'] = '';
            $row['user_cluster'] = '';
        }
    }
}

foreach ($table_data as $row) {
    echo $row['user_id'] . " - " . $row['user_name'] . "\n";
}
echo "\nTotal rows: " . count($table_data) . "\n";
?>
