<?php 
namespace App\Models;

use CodeIgniter\Model;

class CRUDBaseModel extends Model
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

   public function insert($data = null, bool $returnID = true)
   {
       if ($this->table === 'alert_users' && isset($data['admin_flag']) && $data['admin_flag'] == 1) {
           $db = \Config\Database::connect();
           $count = $db->table('alert_users')->where('admin_flag', 1)->countAllResults();
           if ($count >= 100) {
               return false;
           }
       }
       return parent::insert($data, $returnID);
   }

   public function update($id = null, $data = null): bool
   {
       if ($this->table === 'alert_users' && isset($data['admin_flag']) && $data['admin_flag'] == 1) {
           $db = \Config\Database::connect();
           $builder = $db->table('alert_users')->where('admin_flag', 1);
           if ($id !== null) {
               $builder->whereNotIn($this->primaryKey, (array) $id);
           }
           $count = $builder->countAllResults();
           if ($count >= 100) {
               return false;
           }
       }
       return parent::update($id, $data);
   }

   public function insertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 100, bool $testing = false)
   {
       if ($this->table === 'alert_users' && is_array($set)) {
           // Count how many new admins are in this batch
           $newAdmins = 0;
           foreach ($set as $row) {
               if (isset($row['admin_flag']) && $row['admin_flag'] == 1) {
                   $newAdmins++;
               }
           }
           
           if ($newAdmins > 0) {
               $db = \Config\Database::connect();
               $currentAdmins = $db->table('alert_users')->where('admin_flag', 1)->countAllResults();
               if (($currentAdmins + $newAdmins) > 100) {
                   return false; // abort batch
               }
           }
       }
       return parent::insertBatch($set, $escape, $batchSize, $testing);
   }

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
