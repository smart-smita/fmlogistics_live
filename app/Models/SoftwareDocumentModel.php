<?php

namespace App\Models;

use CodeIgniter\Model;

class SoftwareDocumentModel extends Model
{
    protected $table            = 'software_documents';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true; // We use soft deletes for documents
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'category',
        'sub_category',
        'doc_name',
        'doc_number',
        'version_number',
        'revision_number',
        'department',
        'description',
        'keywords',
        'effective_date',
        'review_date',
        'expiry_date',
        'file_name',
        'file_path',
        'file_size',
        'file_type',
        'status',
        'uploaded_by',
        'upload_date',
        'last_updated_by',
        'last_updated_date',
        'is_deleted'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];
}
