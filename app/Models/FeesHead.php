<?php 
namespace App\Models;

use CodeIgniter\Model;

class FeesHead extends Model
{
    protected $DBGroup = 'default'; 
    protected $table         = 'asti_fees_head_master';
    protected $allowedFields = [
        'fees_head_name','status'
    ];
    protected $primaryKey = "fees_head_id";
    protected $returnType    = "array";
    protected $useTimestamps = true;
   // Dates 
   protected $createdField = 'default_date'; 
   protected $updatedField = 'update_date'; 
   //protected $deletedField = 'deleted_at'; 


// protected $DBGroup = 'default'; 
//   protected $table = 'subjects'; 
//   protected $primaryKey = 'id'; 
//   protected $useAutoIncrement = true; 
//   protected $insertID = 0; 
//   protected $returnType = 'array'; 
//   protected $useSoftDeletes = false; 
//   protected $protectFields = true; 
//   protected $allowedFields = ['name','description']; 

//   // Dates 
//   protected $useTimestamps = false; 
//   protected $dateFormat = 'datetime'; 
//   protected $createdField = 'created_at'; 
//   protected $updatedField = 'updated_at'; 
//   protected $deletedField = 'deleted_at'; 

//   // Validation 
//   protected $validationRules = []; 
//   protected $validationMessages = []; 
//   protected $skipValidation = false; 
//   protected $cleanValidationRules = true; 

//   // Callbacks 
//   protected $allowCallbacks = true; 
//   protected $beforeInsert = []; 
//   protected $afterInsert = []; 
//   protected $beforeUpdate = []; 
//   protected $afterUpdate = []; 
//   protected $beforeFind = []; 
//   protected $afterFind = []; 
//   protected $beforeDelete = []; 
//   protected $afterDelete = [];     
}
