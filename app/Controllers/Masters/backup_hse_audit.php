<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Hse_audit extends BaseController
{
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_hse_audit';
     $db['allowedFields'] = ['audit_date','auditor_name','auditee_name','customer_name','status'];
     $db['primaryKey'] = "hse_audit_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="HSE audit";
        $tdata['button_name']="Add Hse audit";
        $tdata['button_id']="user_modal";
        
        $tdata['display_contents'] = [
            "hse_audit_id"=>"ID",
            "audit_date"=>"Audit Date",
            "auditor_name"=>"Auditor Name",
            "auditee_name"=>"Auditee Name",
            "customer_name"=>"Customer Name",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Masters/Hse_audit/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Hse_audit/table_ajax");
        $data['user_designation'] = ["Engineer","Reporting manager",""];
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
        return view("Master/add_hse_audit",$data);
    }
    public function table_ajax(){
        // if($_SESSION['role']!="super_admin")
        //     $tdata['table_data'] = $this->BaseModel->where(["employee_reporting_to"=>$_SESSION['hse_audit_id']])->findAll();
        // else
            $tdata['table_data'] = $this->BaseModel->findAll();
            //  $statusMessages = [
            //     0 => '<span class="badge badge-warning">Pending</span>',
            //     1 => '<span class="badge badge-info">Verified</span>',
            //     2 => '<span class="badge badge-success">Active</span>',
            //     3 => '<span class="badge badge-secondary">Deactivated</span>'
            // ];
        foreach($tdata['table_data'] as $key=>$row){
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Hse_audit/save_details/".$row['hse_audit_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Hse_audit/save_details/".$row['hse_audit_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Hse_audit/save_details/".$row['hse_audit_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Hse_audit/get_form_data/".$row['hse_audit_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['hse_audit_id'].');">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>
											';
											
											if($row['status']=="0"){
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											    
											}else if($row['status']=="1"){
											    $active = "";
											    
											}else if($row['status']=="2"){
											    $delete = "";
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-danger";
											    
											}
			$tdata['table_data'][$key]['action']=$edit.$active.$deactive.$delete;
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }
    
    public function importExcel($hse_audit_id) {
    $db=db_connect();
    
    if (isset($_FILES['import_excel']) && $_FILES['import_excel']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['import_excel']['tmp_name'];

        if (($handle = fopen($fileTmpPath, 'r')) !== FALSE) {
            $temp = fgetcsv($handle);
        // echo "<pre>";
        //             while (($data = fgetcsv($handle)) !== FALSE) 
        //                 print_r($data);
        // exit();
            $pose = 0;
            if(!isset($temp['audit_grid_id']))
                $pose = -1;
                
            $tempLocation = "";
            while (($data = fgetcsv($handle)) !== FALSE) {
                
                if($data[1+$pose]!="")
                    $tempLocation = $data[1+$pose];
                else
                    $data[1+$pose] = $tempLocation;


                $studentData = array(
                    'hse_audit_id'=> $hse_audit_id ,
                    'category'=>$data[1+$pose],
                    'audit_parameter'=>$data[2+$pose],
                    'client_leased' => $data[3+$pose],
                    'inplant' => $data[4+$pose],
                    'fm_leased'=> $data[5+$pose],
                    'explanation'=> $data[6+$pose],
                    'status' => $data[7+$pose],
                    'default_date' => $data[8+$pose],
                    'update_date' => $data[9+$pose],
                );
//print_r($studentData);
                if($pose==-1)
                    $db->table("alert_new_audit_grid")->insert($studentData);
                else
                    $db->table("alert_new_audit_grid")->update($studentData,["audit_grid_id "=>$data[0]]);
                
            }
            fclose($handle);
        } else {
            return "Error opening the file.";
        }
    } else {
        return "No file uploaded or there was an upload error.";
    }
    return "ok"; 
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
                                    $postData['status']= "0";
                                    break;
                                case "delete":
                                    $postData['status']= "2";
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
                 $this->importExcel($id);
                echo json_encode($responce);
    }
}
