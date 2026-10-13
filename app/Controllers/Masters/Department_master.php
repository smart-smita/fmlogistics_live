<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Department_master extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_department_master';
     $db['allowedFields'] = ['department_name','location_name','location_id','status'];
     $db['primaryKey'] = "department_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="Department";
        $tdata['button_name']="Add department";
        $tdata['button_id']="user_modal";
        
        $tdata['display_contents'] = [
            "department_id"=>"ID",
            "department_name"=>"Department Name",
            "location_name"=>"Location Name",
            "status_text"=>"Status",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Masters/Department_master/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Department_master/table_ajax");
        $data['user_designation'] = ["Engineer","Reporting manager",""];
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
        $db = db_connect();
        $data['location_list']=$db->table("alert_location_master")->where("status","1")->get()->getResultArray();
        return view("Master/add_department_master",$data);
    }
    public function table_ajax(){
        // if($_SESSION['role']!="super_admin")
        //     $tdata['table_data'] = $this->BaseModel->where(["employee_reporting_to"=>$_SESSION['department_id']])->findAll();
        // else
            $tdata['table_data'] = $this->BaseModel->where('status !=', 3)->findAll();
             $statusMessages = [
                0 => '<span class="badge badge-warning">Pending</span>',
                1 => '<span class="badge badge-success">Active</span>',
                2 => '<span class="badge badge-danger">Deactivated</span>',
                3 => '<span class="badge badge-secondary">Deleted</span>'
           ];
        foreach($tdata['table_data'] as $key=>$row){
            $active = '<button class="btn btn-icon btn-sm btn-success" onclick="url_call_ajax(\''.base_url("Masters/Department_master/save_details/".$row['department_id']).'/active\',$(this));" title="Activate" data-bs-toggle="tooltip">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-sm btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Department_master/save_details/".$row['department_id']).'/deactive\',$(this));" title="Deactivate" data-bs-toggle="tooltip">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
			$delete = '<button data-ajax-url="'.base_url("Masters/Department_master/save_details/".$row['department_id']).'/delete" class="btn btn-icon btn-sm btn-danger" onclick="delete_row(this);" title="Delete" data-bs-toggle="tooltip">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Department_master/get_form_data/".$row['department_id']).'" class="btn btn-icon btn-sm btn-primary" onclick="edit_id(this,'.$row['department_id'].');" title="Edit" data-bs-toggle="tooltip">
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
    // print_r($postData);
    // exit();
                if(isset($postData['honeypot']))
                    {
                        // if($postData['honeypot'] != ""){
                        //     $responce['status'] = "0";
                        //     $responce['message'] = "Data insertion faild";
                        //     die;
                        // }
                     unset($postData['honeypot']);   
                    }
                    
                if(isset($id)){
                        $responce['message'] = "Data updation faild";
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
                        $responce['message'] = "Data saved successfully";
                    }
                }else{
                    $postData['status']="1";
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                    if($this->BaseModel->insert($postData)){
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }
                echo json_encode($responce);
    }
}
