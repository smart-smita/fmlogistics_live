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
        $tdata['button_name']="Add Lmra_controle";
        $tdata['button_id']="Lmra_controle_modal";
        
        $tdata['display_contents'] = [
                'lmra_id' => 'ID',
                'lmra_category_text' => 'Category Text',
                'lmra_text' => 'LMRA',
                'lmra_options' => 'Option',
                'action' => 'Actions'
            ];
         $data['lmra_options'] = ["Low","Medium","High"];
        $data['ajax_url']=base_url("Masters/Lmra_controle/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Lmra_controle/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        // $db=db_connect();
        // $alert_lmra_controles=$db->table("alert_lmra_controles");

        // $alfa_laval_country=$db->table("alfa_laval_country");
        // $data['country_list'] =$alfa_laval_country->where('status',1)->get()->getResultArray(); 
        // $data['zone_list'] =["East","West","North","South"];
        
        return view("Master/add_lmra_controle",$data);
    }
    public function table_ajax(){
        // if($_SESSION['role']!="super_admin")
        //     $tdata['table_data'] = $this->BaseModel->where(["employee_reporting_to"=>$_SESSION['lmra_id']])->findAll();
        // else
            $tdata['table_data'] = $this->BaseModel->findAll();
            $statusMessages = [
                0 => '<span class="badge badge-warning">Pending</span>',
                1 => '<span class="badge badge-info">Verified</span>',
                2 => '<span class="badge badge-success">Active</span>',
                3 => '<span class="badge badge-secondary">Deactivated</span>'
            ];
            
            foreach($tdata['table_data'] as $key=>$row){
                $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Lmra_controle/save_details/".$row['lmra_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Lmra_controle/save_details/".$row['lmra_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Lmra_controle/save_details/".$row['lmra_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Lmra_controle/get_form_data/".$row['lmra_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['lmra_id'].');">
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
			$tdata['table_data'][$key]['action']="<center>".$statusMessages[$row['status']]."<br><br>".$edit.$active.$deactive.$delete."</center>";
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
                if(isset($postData['honeypot'])){
                     unset($postData['honeypot']);   
                    }
                    if(isset($postData['options'])){
                           $postData['lmra_options'] = implode(",",$postData['options']); 
                           unset($postData['options']);
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
                echo json_encode($responce);
    }
}
