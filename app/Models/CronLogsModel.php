<?php

namespace App\Models;

use CodeIgniter\Model;

class CronLogsModel extends Model
{
    protected $table            = 'alert_cron_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'cron_name',
        'action',
        'previous_status',
        'new_status',
        'changed_by',
        'created_at'
    ];
}
