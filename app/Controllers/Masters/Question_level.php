<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Question_level extends BaseController
{
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'question_level';
     $db['allowedFields'] = [
       'question_title','status'];
     $db['primaryKey'] = "question_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="Title";
        $tdata['button_name']="Add Title";
        $tdata['button_id']="course_modal";
        
        $tdata['display_contents'] = [
             "question_id"=>"ID",
            "question_title"=>"Title",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Masters/Question_level/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Question_level/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
       
        return view("Master/add_question_level",$data);
    }
    public function table_ajax(){
        
         foreach($tdata['table_data'] as $key=>$row){
            $tdata['table_data'][$key]['sr_no'] = $key+1;
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Question_level/save_details/".$row['question_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Question_level/save_details/".$row['question_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Question_level/save_details/".$row['question_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Question_level/get_form_data/".$row['question_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['question_id'].');">
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
                         $ads_user = $db->table('ads_Question_level_user');
                        if($user_id!="")
                            $ads_user->replace(["user_id"=>$user_id,"question_id"=>$id]);
                        else
                            $ads_user->replace(["status"=>$postData['status'],"question_id"=>$id]);
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
                         $ads_user = $db->table('ads_Question_level_user');
                            $ads_user->replace(["user_id"=>$user_id,"question_id"=>$id]);
                            $responce['status'] = "1";
                            $responce['message'] = "Data saved successfully";
                    }
                }
                echo json_encode($responce);
    }
}
