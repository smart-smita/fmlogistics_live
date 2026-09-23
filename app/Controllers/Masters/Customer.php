<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Customer extends BaseController
{
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'ads_customer';
     $db['allowedFields'] = [
       'customer_name','user_id','customer_email','customer_expiry_date','customer_password','status'];
     $db['primaryKey'] = "customer_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="User";
        $tdata['button_name']="Add Users";
        $tdata['button_id']="course_modal";
        
        $tdata['display_contents'] = [
            "sr_no"=>"sr. no",
            // "customer_id"=>"ID",
            "customer_name"=>"User Name",
            "client_name"=>"Client Name",
            "customer_email"=>"Email",
            "customer_password"=>"Password",
            "customer_expiry_date"=>"Expiry Date",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Masters/Customer/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Customer/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
        
        // start: to get select option values 
    //  Dashboard
    //  ads_customer
             $db = db_connect();
             $ads_user = $db->table('ads_user');
             if(isset($_SESSION['role']) && $_SESSION['role']=="super_admin"){
                 
             }else{
             $ads_user->where("user_id",$_SESSION['user_id']);    
             }
             $data['user_list']=$ads_user->get()->getResultArray();
        return view("Master/add_customer",$data);
    }
    public function table_ajax(){
        
        if($_SESSION['role']=="super_admin" && (isset($_SESSION['user_id']) && $_SESSION['user_id']==""))
        $tdata['table_data'] = $this->BaseModel->select("*,(select company_name from ads_user where ads_user.user_id=ads_customer.user_id) as client_name")->findAll();
        else
        $tdata['table_data'] = $this->BaseModel->select("*,(select company_name from ads_user where ads_user.user_id=ads_customer.user_id) as client_name")->where("user_id",$_SESSION['user_id'])->findAll();
        foreach($tdata['table_data'] as $key=>$row){
            $tdata['table_data'][$key]['sr_no'] = $key+1;
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Customer/save_details/".$row['customer_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Customer/save_details/".$row['customer_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Customer/save_details/".$row['customer_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Customer/get_form_data/".$row['customer_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['customer_id'].');">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>
											';
																						 $loginas = '<button onclick=\'window.location.href="'.base_url("Login/direct_login/customer/".$row['customer_id']).'"\' class="btn btn-icon btn-primary">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-key"></i>
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
			$tdata['table_data'][$key]['action']=$edit.$active.$deactive.$delete.$loginas;
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
                                    $postData['status']= "0";
                                    break;
                                case "delete":
                                    $postData['status']= "2";
                                    break;
                            }
                        }
                        $user_id="";
                        if(isset($postData['user_id'])){
                        $user_id = $postData['user_id'];
                        //unset($postData['user_id']);
                        }
                    if($this->BaseModel->update($id,$postData)){
                         $db = db_connect();
                         $ads_user = $db->table('ads_customer_user');
                        if($user_id!="")
                            $ads_user->replace(["user_id"=>$user_id,"customer_id"=>$id]);
                        else
                            $ads_user->replace(["status"=>$postData['status'],"customer_id"=>$id]);
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }else{
                    $postData['status']="1";
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                        $user_id = $postData['user_id'];
                        //unset($postData['user_id']);
                    if($this->BaseModel->insert($postData)){
                        $id  = $this->BaseModel->insertID();
                         $db = db_connect();
                         $ads_user = $db->table('ads_customer_user');
                            $ads_user->replace(["user_id"=>$user_id,"customer_id"=>$id]);
                            $responce['status'] = "1";
                            $responce['message'] = "Data saved successfully";
                    }
                }
                echo json_encode($responce);
    }
}
