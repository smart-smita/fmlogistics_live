<?php 
namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
    protected $table         = '';
    protected $allowedFields = [];
    protected $primaryKey = "";

       public function __construct($db){
        $this->table         = $db['table'];
        $this->allowedFields = $db['allowedFields'];
        $this->primaryKey = $db['primaryKey'];
                   parent::__construct();

    }
    

    protected $DBGroup = 'default'; 
    protected $useTimestamps = true;
      protected $returnType    = "array";
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
