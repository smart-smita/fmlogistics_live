<?php

//namespace App\Controllers;
namespace App\Controllers\Customer;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Media extends BaseController
{
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'ads_media_master';
     $db['allowedFields'] = [
       'media_type','media_name','media_url','customer_id','user_id','status'];
     $db['primaryKey'] = "media_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    
     public function index()
    {
        
        $data['customer_id'] = $this->customer_id;
        $data['user_id'] = $this->user_id;
        $data['action_url'] = base_url("/Customer/Media/save_details");
        $data['media_list_url'] = base_url("/Customer/Media/table_ajax");
      
        return view("Customer/media",$data);
    }
    
    public function list()
    {
        $data = [];

        $tdata['title']="Media";
        $tdata['button_name']="Add Media";
        $tdata['button_id']="media";
        
        $tdata['display_contents'] = [
            "media_id"=>"ID",
            "media_name"=>"Media Name",
            "media_url"=>"URL",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Customer/Media/save_details");
        $tdata ['ajax_url_for_data']=base_url("Customer/Media/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
        
        // start: to get select option values 
      //  return view("Layout/table-view",$data);
        return view("Customer/media_list",$data);
    }
    public function table_ajax(){
            // $request = service('request');
            // $postData = $request->getVar();
            // print_r($postData);
                $postData['user_id'] =$this->user_id;
            if($this->customer_id!=0)
                $postData['customer_id'] = $this->customer_id;
                $postData['status!=']="2";
                $this->BaseModel->select("*,concat(\"".base_url("/")."/\",media_url) as media_url ")->where($postData);
                $this->BaseModel->orderBy("media_id", "desc");
        $tdata['data'] = $this->BaseModel->get()->getResultArray();
       
        
        //.select("*,media_url as concat('".base_url()."',media_url)")
        $tdata['status']="1";
        
//         foreach($tdata['table_data'] as $key=>$row){
//             $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Customer/Media/save_details/".$row['media_id']).'/active\',$(this));">
// 											<span class="indicator-label svg-icon svg-icon-2">
// 												<i class="fa fa-unlock"></i>
// 											</span>
//                                             <span class="indicator-progress">
//                                                 <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
//                                             </span>
// 											</button> ';
// 			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Customer/Media/save_details/".$row['media_id']).'/deactive\',$(this));">
//                 			<span class="indicator-label svg-icon svg-icon-2">
//                 			<i class="fa fa-lock"></i>
//                 			</span>
//                             <span class="indicator-progress">
//                                 <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
//                             </span>
//                 			</button> ';
// 				$delete = '<button data-ajax-url="'.base_url("Customer/Media/save_details/".$row['media_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
//                 			<span class="indicator-label svg-icon svg-icon-2">
                				
// 								<i class="fa fa-trash"></i>
                				
//                 			</span>
//                             <span class="indicator-progress">
//                                 <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
//                             </span>
//                 			</button> ';

//             //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
//              $edit = '<button data-ajax-url="'.base_url("Customer/Media/get_form_data/".$row['media_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['media_id'].');">
// 											<span class="indicator-label svg-icon svg-icon-3">
// 												<i class="fa fa-edit"></i>
// 											</span>
//                                             <span class="indicator-progress">
//                                                 <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
//                                             </span>
// 											</button>
// 											';
// 											if($row['status']=="0"){
// 											    $deactive = "";
// 											    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											    
// 											}else if($row['status']=="1"){
// 											    $active = "";
											    
// 											}else if($row['status']=="2"){
// 											    $delete = "";
// 											    $deactive = "";
// 											    $tdata['table_data'][$key]['tr_class']="bg-light-danger";
											    
// 											}
// 			$tdata['table_data'][$key]['action']=$edit.$active.$deactive.$delete;
//         }
//         $tdata['data'] = $tdata['table_data'];
//         unset($tdata['table_data']);
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
                    if($this->BaseModel->update($id,$postData)){
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }else{
                    $fileData['user_id'] = $postData['user_id'];
                    $fileData['customer_id'] = $postData['customer_id'];
                    //File upload logic start
                    $allowed = array('mp4', 'jpg', 'png', 'jpeg', 'gif');
                    if(isset($_FILES['media_file']) &&  isset( $_FILES['media_file']['error'])){
                        if( $_FILES['media_file']['error'] == UPLOAD_ERR_OK){
                            $ext = pathinfo($_FILES["media_file"]["name"], PATHINFO_EXTENSION);
                            if (in_array($ext, $allowed)) {
                               $folder="uploads/Media/".$postData['user_id']."/".$postData['customer_id']."/";
                                $url = "";
                                $url = $this->uploadImage($folder,"media_file");
                                $fileData['media_url'] = $url;
                                $fileData['media_type'] = $ext;
                                $fileData['media_name'] = $_FILES["media_file"]["name"];
                                
                                
                                // else 
                                //     $url = $this->uploadSound($folder,"audio_file",$postData['audio_file_url']);
                                //     $postData['audio_file'] = $url;
                                //     unset($postData['audio_file_url']);
                            }
                        }
                    }
                  //File upload logic end 
                  
                    
                    
                    
                    $postData['status']="1";
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                    if($this->BaseModel->insert($fileData)){
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }
                echo json_encode($responce);
    }
    function Report(){
        
           $data= [];
        $tdata['title']="Media Wise report";
        $tdata['export_button']='<button class="btn btn-icon btn-success" onclick="ExportToExcel()">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-file-csv"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>';
        
        $tdata['display_contents'] = [
            // "ago"=>"Ago",
            "screen_name"=>"Screen name",
            // "media_count"=>"Media Count",
            "media_url"=>"Media",
            "device_name"=>"Device",
            "report_date"=>"Date",
            "company_name"=>"Company name",
            "count"=>"Run Count",
            "duration"=>"Run Duration",
            "start_date"=>"Start date",
            "end_date"=>"End date"
            ];
        $tdata ['ajax_url_for_data']=base_url("Customer/Media/report_ajax/");
        $data['table'] = view("Layout/table-view",$tdata);
        return view("Layout/table-view-2",$data);
    
    }
        public function report_ajax($device_id = null){
//            ads_media_master.media_type,
//            ads_media_master.media_url,

           $db =db_connect();
            $device_reports = $db->table('ads_device_reports');
            $device_reports->select("ads_device_master.device_details,
            ads_device_master.device_name,
            ads_device_reports.report_date,
            ads_media_master.media_url,
            ads_media_master.media_type,
            count(DISTINCT ads_media_master.media_id) as media_count,
            ads_user.company_name,
            ads_customer.customer_name,
            ads_device_reports.count,
            ads_device_reports.default_date as start_date,
            ads_device_reports.update_date as end_date,
            ads_screen_master.screen_name,
            TIMEDIFF(NOW(), ads_device_reports.`update_date`) as ago ,
            if(TIMEDIFF(max(ads_device_reports.update_date),min(ads_device_reports.default_date))>0,TIMEDIFF(max(ads_device_reports.update_date),min(ads_device_reports.default_date)),0) as duration 
            ");
            $device_reports->join("ads_device_master","ads_device_master.device_uuid=ads_device_reports.device_id ","left");
            $device_reports->join("ads_media_master","ads_media_master.media_id=ads_device_reports.media_id ","left");
            
            $device_reports->join("ads_user","ads_user.user_id=ads_device_reports.user_id ","left");
            $device_reports->join("ads_customer","ads_customer.customer_id=ads_device_reports.customer_id ","left");
            $device_reports->join("ads_screen_master","ads_screen_master.screen_id=ads_device_reports.screen_id ","left");
            $device_reports->where("ads_user.user_id",$this->user_id);
            
            
            if(isset($device_id))
            $device_reports->where("ads_device_reports.device_id",$device_id);
            
            $device_reports->groupBy(["ads_device_reports.report_date","ads_media_master.media_id"]);
            $device_reports = $device_reports->get()->getResultArray();
            foreach($device_reports as $key=>$row){
            if($this->isUrlExists(base_url($device_reports[$key]["media_url"]))){

                if($row['media_type']=="mp4")
                $device_reports[$key]["media_url"] = "<iframe src='".base_url($device_reports[$key]["media_url"])."' height='100' width='100' />";
                else if($row['media_type']=="custome"){
                    
                }else
                $device_reports[$key]["media_url"] = "<img src='".base_url($device_reports[$key]["media_url"])."' height='100' width='100' />";
            }else
                $device_reports[$key]["media_url"] = "<img src='".env("defaultLogo")."' height='100' width='100' />";
            }
                    $tdata['data'] = $device_reports;
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }

}
