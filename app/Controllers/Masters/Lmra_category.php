<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Lmra_category extends BaseController
{
    /**
 * @var CRUDBaseModel
 */
protected $BaseModel;
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_lmra_categorys';
     $db['allowedFields'] = ['category_name','status'];
     $db['primaryKey'] = "category_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="";
        $tdata['button_name']="Add Category";
        $tdata['button_id']="user_modal";
        
        $tdata['display_contents'] = [
            "category_id"=>"ID",
            "category_name"=>"category Name",
            "status_text"=>"Status",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Masters/Lmra_category/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Lmra_category/table_ajax");
        $data['user_designation'] = ["Account Manager","Reporting manager",""];
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
        return view("Master/add_lmra_category",$data);
    }
    public function table_ajax(){
        // Only fetch categories that are not soft-deleted (status != 3)
        $tdata['table_data'] = $this->BaseModel->where('status !=', 3)->findAll();
        
        $statusMessages = [
            0 => '<span class="badge badge-warning">Pending</span>',
            1 => '<span class="badge badge-success">Active</span>',
            2 => '<span class="badge badge-danger">Deactivated</span>',
            3 => '<span class="badge badge-secondary">Deleted</span>'
        ];
        
        foreach($tdata['table_data'] as $key=>$row){
            $active = '<button class="btn btn-icon btn-sm btn-success" onclick="url_call_ajax(\''.base_url("Masters/Lmra_category/save_details/".$row['category_id']).'/active\',$(this));" title="Activate" data-bs-toggle="tooltip">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button class="btn btn-icon btn-sm btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Lmra_category/save_details/".$row['category_id']).'/deactive\',$(this));" title="Deactivate" data-bs-toggle="tooltip">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Lmra_category/save_details/".$row['category_id']).'/delete" class="btn btn-icon btn-sm btn-danger" onclick="delete_row(this);" title="Delete" data-bs-toggle="tooltip">
                			<span class="indicator-label svg-icon svg-icon-2">
								<i class="fa fa-trash"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

             $edit = '<button data-ajax-url="'.base_url("Masters/Lmra_category/get_form_data/".$row['category_id']).'" class="btn btn-icon btn-sm btn-primary" onclick="edit_id(this,'.$row['category_id'].');" title="Edit" data-bs-toggle="tooltip">
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
            $responce['status'] = "1";
            $responce['message'] = "Details found";
        }
        return $this->response->setJSON($responce);
    }
    public function save_details($id=null,$action=null){
        $request = service('request');
        $postData = $request->getVar();

        if(isset($postData['honeypot'])) {
            unset($postData['honeypot']);   
        }

        // Validate category_name if saving/updating from form (not just a status action)
        if (empty($action)) {
            if (!isset($postData['category_name']) || trim($postData['category_name']) === '') {
                return $this->response->setJSON(['status' => '0', 'message' => 'Category name is required.']);
            }
            $postData['category_name'] = trim($postData['category_name']);
            
            // Prevent duplicate active category names
            $existing = $this->BaseModel->where('category_name', $postData['category_name'])
                                        ->where('status !=', 3);
            if (isset($id)) {
                $existing = $existing->where('category_id !=', $id);
            }
            $existing = $existing->first();
            
            if ($existing) {
                return $this->response->setJSON(['status' => '0', 'message' => 'An active category with this name already exists.']);
            }
        }
            
        if(isset($id)){
            $responce['message'] = "Data updation failed";
            if(isset($action)){
                switch($action){
                    case "active":
                        $postData['status'] = "1";
                        break;
                    case "deactive":
                        $postData['status'] = "2";
                        break;
                    case "delete":
                        $postData['status'] = "3";
                        break;
                }
            }
            if($this->BaseModel->update($id, $postData)){
                $responce['status'] = "1";
                $responce['message'] = "Data updated successfully";
            } else {
                $responce['status'] = "0";
            }
        }else{
            $postData['status'] = "1";
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
