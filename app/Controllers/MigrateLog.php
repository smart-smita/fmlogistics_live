<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class MigrateLog extends BaseController
{
    public function index()
    {
        $db = Database::connect();
        $sql = "CREATE TABLE IF NOT EXISTS `cron_logs` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `cron_name` varchar(100) NOT NULL,
          `run_date` date NOT NULL,
          `start_time` datetime NOT NULL,
          `end_time` datetime NOT NULL,
          `cluster_manager_id` varchar(100) DEFAULT NULL,
          `records_sent_count` int(11) NOT NULL DEFAULT 0,
          `status` varchar(20) NOT NULL,
          `error_message` text DEFAULT NULL,
          `created_at` datetime DEFAULT NULL,
          `updated_at` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $db->query($sql);
        echo "Table cron_logs created successfully.\n";
    }
}
