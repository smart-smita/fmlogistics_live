<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Device_details extends BaseController
{
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'ads_device_details';
     $db['allowedFields'] = ['user_id','screen_id',
       'device_id','device_name','location_type','screen_location','latitude','longitude','screen_size','orientation','resolution','aspect_ratio','monthly_footfall','daily_impression','cost_for_impression',
       'languages','state','city','device_image','monday_start_time','monday_end_time','tuesday_start_time','tuesday_end_time','wednesday_start_time','wednesday_end_time',
       'wednesday_end_time','thursday_end_time','friday_start_time','friday_end_time','saturday_start_time','saturday_end_time','sunday_start_time','sunday_end_time','status'];
     $db['primaryKey'] = "device_details_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];
        $tdata['title']="Device Details";
        $tdata['button_name']="Add Device Details";
        $tdata['button_id']="course_modal";
        
        $tdata['display_contents'] = [
            "device_details_id"=>"ID",
            // "customer_id"=>"ID",
            "device_image" => "Image",
            "device_name"=>"Device Name",
            "location_type"=>"Location Type",
            "screen_location"=>"Screen Location",
            "latitude"=>"Latitude",
            "longitude"=>"Longitude",
            "screen_size"=>"Screen Size",
            "orientation"=>"Orientation",
            "resolution"=>"Resolution",
            "aspect_ratio"=>"Aspect Ratio",
            "monthly_footfall"=>"Monthly Footfall",
            "daily_impression"=>"Impression",
            "cost_for_impression"=>"CPI",
            "languages"=>"Languages",
            "state" => "State",
            "city" => "City",
            "action"=>"Action"
            ];
            $db = db_connect();
                $ads_device_details = $db->table('ads_user');
                $ads_device_details->where(["status"=>"1","admin_flag"=>"2"]);
                $data['user_list']= $ads_device_details->get()->getResultArray();
            
        $data['ajax_url']=base_url("Masters/Device_details/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Device_details/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";   
                 $ads_screen_master = $db->table('ads_screen_master');
                 $ads_screen_master->where("user_id",$this->user_id);
                 $data['screen_list'] = $ads_screen_master->get()->getResultArray();
                
        return view("Master/add_device_details",$data);
    }
    public function table_ajax(){
        $db = db_connect();
        $ads_device_details = $db->table('ads_device_details');
        // echo $this->user_id;
        if(isset($this->user_id) && $this->user_id!=""){
            if($this->user_id!=1)
             $ads_device_details->where("user_id",$this->user_id);
        }
        $tdata['table_data']=$ads_device_details->get()->getResultArray();
        foreach($tdata['table_data'] as $key=>$row){
            //$tdata['table_data'][$key]['sr_no'] = $key+1;
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Device_details/save_details/".$row['device_details_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Device_details/save_details/".$row['device_details_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Device_details/save_details/".$row['device_details_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Device_details/get_form_data/".$row['device_details_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['device_details_id'].');">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>
											';
										//	$loginas = '<button onclick=\'window.location.href="'.base_url("Login/direct_login/customer/".$row['customer_id']).'"\' class="btn btn-icon btn-primary">
										//	<span class="indicator-label svg-icon svg-icon-3">
										//		<i class="fa fa-key"></i>
										//	</span>
                                          //  <span class="indicator-progress">
                                            //    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        //    </span>
										//	</button>
										//	';

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
                			$tdata['table_data'][$key]["device_image"] = "<img src='".base_url($tdata['table_data'][$key]["device_image"])."' onerror=\"this.src='".env("defaultLogo")."'\" height='100' width='100' />";

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
                    
                    
                     $allowed = array('mp4', 'jpg', 'png', 'jpeg', 'gif');
                    if(isset($_FILES['device_image']) &&  isset( $_FILES['device_image']['error'])){
                        if( $_FILES['device_image']['error'] == UPLOAD_ERR_OK){
                            $ext = pathinfo($_FILES["device_image"]["name"], PATHINFO_EXTENSION);
                            if (in_array($ext, $allowed)) {
                               $folder="uploads/Device_image/";
                                $url = "";
                                if(isset($postData['device_image_url']))
                                $url = $this->uploadImage($folder,"device_image",$postData['device_image_url']);
                                else
                                $url = $this->uploadImage($folder,"device_image");
                                
                                $postData['device_image'] = $url;
                            }
                        }
                    }else{
                        if(isset($postData['device_image_url']))
                                $postData['device_image'] = $postData['device_image_url'];
                        
                    }
                    if(isset($postData['device_image_url']))
                            unset($postData['device_image_url']);
                            // echo $postData['device_image'];
                            // exit();
                    
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
                       // $user_id="";
                        //if(isset($postData['user_id'])){
                        //$user_id = $postData['user_id'];
                        //unset($postData['user_id']);
                        //}
                    if($this->BaseModel->update($id,$postData)){
                         $db = db_connect();
                         $ads_user = $db->table('ads_device_details');
                      //  if($user_id!="")
                        //    $ads_user->replace(["user_id"=>$user_id,"customer_id"=>$id]);
                        //else
                          //  $ads_user->replace(["status"=>$postData['status'],"customer_id"=>$id]);
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }else{
                    $responce['status'] = "0";
                    $responce['message'] = "Data insertion faild";
                    
                    $postData = $_POST;
                    $postData['status']="1";
                    $folder = "";
                    // $url = $this->uploadImage($folder,"device_image","");
                    // $postData['device_image'] = $url;
                                    // unset($postData['audio_file_url']);
                        //$user_id = $postData['user_id'];
                        //unset($postData['user_id']);
                    if($this->BaseModel->insert($postData)){
                        $id  = $this->BaseModel->insertID();
                        //  $db = db_connect();
                        //  $ads_user = $db->table('ads_device_details');
                        //     $ads_user->replace(["user_id"=>$user_id,"customer_id"=>$id]);
                            $responce['status'] = "1";
                            $responce['message'] = "Data saved successfully";
                    }
                }
                echo json_encode($responce);
    }
}
