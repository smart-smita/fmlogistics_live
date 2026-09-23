<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailSettingsModel extends Model
{
    protected $table            = 'email_settings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'email_enabled',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'from_email',
        'from_name',
        'reply_to_email',
        'status',
        'created_by',
        'updated_by',
        'created_at',
        'updated_at',
        'smtp_status',
        'last_test_email_date'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSettings()
    {
        return $this->first();
    }
}
