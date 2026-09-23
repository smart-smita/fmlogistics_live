<?php

namespace App\Models;

use CodeIgniter\Model;

class GembaSitesModel extends Model
{
    protected $table = 'alert_gemba_sites';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'site_name',
        'site_type_1',
        'site_type_2',
        'site_category',
        'region',
        'status'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'default_date';
    protected $updatedField = 'update_date';
}
