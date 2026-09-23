<?php

namespace App\Models;

use CodeIgniter\Model;

class SoftwareDefinitionModel extends Model
{
    protected $table            = 'software_definitions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'question',
        'answer',
        'category',
        'sub_category',
        'keywords',
        'status',
        'display_order',
        'created_by',
        'updated_by',
        'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';
}
