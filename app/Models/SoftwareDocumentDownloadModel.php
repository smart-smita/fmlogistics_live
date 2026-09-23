<?php

namespace App\Models;

use CodeIgniter\Model;

class SoftwareDocumentDownloadModel extends Model
{
    protected $table            = 'software_document_downloads';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'document_id',
        'downloaded_by',
        'download_date'
    ];

    // Dates
    protected $useTimestamps = false;
}
