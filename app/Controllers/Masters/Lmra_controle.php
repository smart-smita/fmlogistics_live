<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Lmra_controle extends BaseController
{
    /**
 * @var CRUDBaseModel
 */
protected $BaseModel;
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table'] = 'alert_lmra';
     $db['allowedFields'] = ['lmra_category_id','lmra_category_text','lmra_text','lmra_options','status'];
     $db['primaryKey'] = "lmra_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="";
        $tdata['button_name']="Add Lmra Control";
        $tdata['button_id']="Lmra_controle_modal";
        
        $tdata['display_contents'] = [
                'lmra_id' => 'ID',
                'lmra_category_text' => 'Category Text',
                'lmra_text' => 'LMRA',
                'lmra_options' => 'Options',
                'status_text' => 'Status',
                'action' => 'Actions'
            ];
            
        // Fetch active categories for the dropdown
        $db = db_connect();
        $data['active_categories'] = $db->table("alert_lmra_categorys")->where('status', 1)->get()->getResultArray();
        $data['lmra_options'] = ["Low","Medium","High"];
        
        $data['ajax_url']=base_url("Masters/Lmra_controle/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Lmra_controle/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        
        return view("Master/add_lmra_controle",$data);
    }
    public function table_ajax(){
        // Only fetch questions that are not soft-deleted (status != 3)
        $tdata['table_data'] = $this->BaseModel->where('status !=', 3)->findAll();
        
        $statusMessages = [
            0 => '<span class="badge badge-warning">Pending</span>',
            1 => '<span class="badge badge-success">Active</span>',
            2 => '<span class="badge badge-danger">Deactivated</span>',
            3 => '<span class="badge badge-secondary">Deleted</span>'
        ];
        
        foreach($tdata['table_data'] as $key=>$row){
            $active = '<button class="btn btn-icon btn-sm btn-success" onclick="url_call_ajax(\''.base_url("Masters/Lmra_controle/save_details/".$row['lmra_id']).'/active\',$(this));" title="Activate" data-bs-toggle="tooltip">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button class="btn btn-icon btn-sm btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Lmra_controle/save_details/".$row['lmra_id']).'/deactive\',$(this));" title="Deactivate" data-bs-toggle="tooltip">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Lmra_controle/save_details/".$row['lmra_id']).'/delete" class="btn btn-icon btn-sm btn-danger" onclick="delete_row(this);" title="Delete" data-bs-toggle="tooltip">
                			<span class="indicator-label svg-icon svg-icon-2">
								<i class="fa fa-trash"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

             $edit = '<button data-ajax-url="'.base_url("Masters/Lmra_controle/get_form_data/".$row['lmra_id']).'" class="btn btn-icon btn-sm btn-primary" onclick="edit_id(this,'.$row['lmra_id'].');" title="Edit" data-bs-toggle="tooltip">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>
											';
                                            
                                            $toggle_status_btn = $active;
                                            
										if($row['status']=="0"){
											    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											}else if($row['status']=="1"){
											    $toggle_status_btn = $deactive;
											}else if($row['status']=="2"){
											    $tdata['table_data'][$key]['tr_class']="bg-light-danger";
											}
			
            $tdata['table_data'][$key]['status_text'] = $statusMessages[$row['status']] ?? '';
            $tdata['table_data'][$key]['action']='<div class="d-flex justify-content-center gap-2">' . $edit . $toggle_status_btn . $delete . '</div>';
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);
    }
    public function get_form_data($id){
        $responce['status'] = "0";
        $responce['message'] = "Details not found";
        if(isset($id)){

            $responce['data'] = $this->BaseModel->find($id);
            // print_r($responce['data']);
            // exit();
            $responce['status'] = "1";
            $responce['message'] = "Details found";
        }
        echo json_encode($responce);
    }
    public function save_details($id=null,$action=null){
        $request = service('request');
        $postData = $request->getVar();

        if(isset($postData['honeypot'])){
            unset($postData['honeypot']);   
        }
        
        // Handle options array into comma-separated string, or trim input string
        if(isset($postData['options'])){
            $postData['lmra_options'] = implode(",", array_filter(array_map('trim', $postData['options']))); 
            unset($postData['options']);
        } elseif (isset($postData['lmra_options'])) {
            $postData['lmra_options'] = trim($postData['lmra_options']);
        }
        
        // Fetch category text based on selected category id
        if (isset($postData['lmra_category_id']) && empty($action)) {
            $db = db_connect();
            $category = $db->table("alert_lmra_categorys")->where('category_id', $postData['lmra_category_id'])->get()->getRowArray();
            if ($category) {
                $postData['lmra_category_text'] = $category['category_name'];
            }
        }
        
        // Validation for insert/update from form
        if (empty($action)) {
            if (empty($postData['lmra_category_id']) || empty($postData['lmra_text']) || empty($postData['lmra_options'])) {
                return $this->response->setJSON(['status' => '0', 'message' => 'Category, Question text, and Options are required.']);
            }
            $postData['lmra_text'] = trim($postData['lmra_text']);
            
            // Prevent duplicate questions in same category
            $existing = $this->BaseModel->where('lmra_category_id', $postData['lmra_category_id'])
                                        ->where('lmra_text', $postData['lmra_text'])
                                        ->where('status !=', 3);
            if (isset($id)) {
                $existing = $existing->where('lmra_id !=', $id);
            }
            $existing = $existing->first();
            
            if ($existing) {
                return $this->response->setJSON(['status' => '0', 'message' => 'This question already exists in this category.']);
            }
        }

        if(isset($id)){
            $responce['message'] = "Data updation failed";
            if(isset($action)){
                switch($action){
                    case "active":
                        $postData['status']= "1";
                        break;
                    case "deactive":
                        $postData['status']= "2";
                        break;
                    case "delete":
                        $postData['status']= "3";
                        break;
                }
            }
            if($this->BaseModel->update($id,$postData)){
                $responce['status'] = "1";
                $responce['message'] = "Data updated successfully";
            } else {
                $responce['status'] = "0";
            }
        }else{
            $postData['status']="1";
            $responce['status'] = "0";
            $responce['message'] = "Data insertion failed";
            if($this->BaseModel->insert($postData)){
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }
        }
        return $this->response->setJSON($responce);
    }
}
