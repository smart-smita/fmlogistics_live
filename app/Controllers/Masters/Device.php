<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Device extends BaseController
{
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'ads_device_master';
     $db['allowedFields'] = [
       'device_name','serial_no','device_uuid','status'];
     $db['primaryKey'] = "device_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
        public function report($device_id=""){
           $data= [];
        $tdata['title']="Device Wise report";
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
            "media_count"=>"Media Count",
            "media_url"=>"Media",
            "device_name"=>"Device",
            "report_date"=>"Date",
            "company_name"=>"Company name",
            "count"=>"Run Count",
            "duration"=>"Run Duration",
            "start_date"=>"Start date",
            "end_date"=>"End date"
            ];
        $tdata ['ajax_url_for_data']=base_url("Masters/Device/report_ajax/".$device_id);
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

    public function index()
    {
        $data = [];

        $tdata['title']="Device Details";
       // $tdata['button_name']="Add Users";
       // $tdata['button_id']="user_modal";
        
        $tdata['display_contents'] = [
            "ago"=>"Ago",
            "device_id"=>"ID",
            "device_name"=>"Device Name",
            "serial_no"=>"Serial Number",
            "device_uuid"=>"UUID",
            "company_name"=>"Assign To",
            "active_tv"=>"Status",
            "action"=>"Action"
            /*"action"=>"Action"*/
            ];
              $tdata['export_button']='<button class="btn btn-icon btn-success" onclick="ExportToExcel()">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-file-csv"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>';
        /*$data['ajax_url']=base_url("Masters/Device/save_details");*/
        $tdata ['ajax_url_for_data']=base_url("Masters/Device/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
            $db =db_connect();
            $ads_user = $db->table('ads_user');
            $client_details = $ads_user->get()->getResultArray();
            
            foreach($client_details as $row){
            $temp_client_details[$row['user_id']]=$row['first_name']." ".$row['last_name']." ( ".$row['company_name']." )";
            }
            $data['client_details'] = $temp_client_details;
        return view("Master/add_device_to_customer",$data);
        // start: to get select option values 
     
        //return view("Master/add_user",$data);
    }
    function time_elapsed_string($datetime,$nowdate, $full = false) {
    $now = new \DateTime($nowdate);
    $ago = new \DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
    public function demo(){
        echo "<pre>";
       print_r( $this->BaseModel->select("NOW(),TIMEDIFF(NOW(), max(ads_device_reports.update_date)),max(ads_device_reports.update_date)")->join("ads_device_reports","ads_device_reports.device_id=ads_device_master.device_uuid ","left")->groupBy("ads_device_reports.device_id")->findAll());
        echo $this->time_elapsed_string("2022-11-30 10:35:44","2022-11-30 10:25:44");
    }
    public function table_ajax(){
        //SELECT max(ads_device_reports.update_date) FROM ads_device_master join ads_device_reports on ads_device_reports.device_id=ads_device_master.device_uuid GROUP by ads_device_reports.device_id order by ads_device_reports.update_date DESC
        $tdata['table_data'] = $this->BaseModel->select("*,
        (SELECT count(*) FROM ads_device_reports WHERE ads_device_reports.update_date > now() - interval 20 minute and ads_device_reports.device_id=ads_device_master.device_uuid GROUP by ads_device_reports.device_id) as active_tv
        ,TIMEDIFF(NOW(), update_date) as ago ,(select concat(first_name,' ',last_name,' ',' ( ',company_name,' ) ') from ads_user where ads_user.user_id in ( select ads_customer_device.user_id from ads_customer_device where ads_customer_device.device_id=ads_device_master.device_id and status=1 ) limit 1) as company_name ")
        
        ->findAll();
        
        foreach($tdata['table_data'] as $key=>$row){
            if(isset($row['active_tv']) && $row['active_tv']>0){
            $tdata['table_data'] [$key]['active_tv'] = '<span class="badge badge-success fw-semibold me-1">ON</span>';
            }
            else{
            $tdata['table_data'] [$key]['active_tv'] = '<span class="badge badge-danger fw-semibold me-1">OFF</span>';
            }
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Device/save_details/".$row['device_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Device/save_details/".$row['device_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
		
                			
				$delete = '<button data-ajax-url="'.base_url("Masters/Device/save_details/".$row['device_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Device/get_form_data/".$row['device_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['device_id'].');">
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
			$assign =  '<button  class="btn btn-icon btn-danger" data-ajax-url=\''.base_url("Masters/Device/save_details/".$row['device_id']).'/assign/\' onclick="assign_to(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                		</button> ';
                		 $assign .= '<button class="btn btn-icon btn-success" onclick="window.location.href=\''.base_url("Masters/Device/report/".$row['device_uuid']).'\'">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-file"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
		
// 		$tdata['table_data'][$key]['ago'] = $this->get_time_ago(strtotime($tdata['table_data'][$key]['update_date']));
// 			$tdata['table_data'][$key]['action']=$edit.$active.$deactive.$delete;
			$tdata['table_data'][$key]['action']=$assign;
			
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
    public function save_details($id=null,$action=null,$client_id=null){
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
                                case "assign":
                                    // $postData['status']= "2";
                                    
                                    
                                    $db =db_connect();
                                    $ads_customer_device = $db->table('ads_customer_device');
                                    $ads_customer_device->where("device_id",$id);
                                    $ads_customer_device->set("status","0");
                                    $ads_customer_device->update();
                                    $ads_customer_device->replace(["user_id"=>$client_id,"device_id"=>$id,"status"=>"1"]);
                                    $postData=null;
                                        $responce['status'] = "1";
                                        $responce['message'] = "Data saved successfully";
                                    // $client_details = $ads_user->get()->getResultArray();

                                    break;
                                    
                            }
                        }
                        if(isset($postData))
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
