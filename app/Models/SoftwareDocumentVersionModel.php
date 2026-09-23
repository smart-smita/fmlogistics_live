<?php

namespace App\Models;

use CodeIgniter\Model;

class SoftwareDocumentVersionModel extends Model
{
    protected $table            = 'software_document_versions';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'version_number',
        'revision_number',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'uploaded_by',
        'upload_date'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';
    protected $deletedField  = '';
}
