<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Location_master extends BaseController
{
 /**
 * @var CRUDBaseModel
 */
protected $BaseModel;
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_location_master';
     $db['allowedFields'] = ['location_name', 'cluster_name', 'country_name', 'region_name', 'status', 'account_manager', 'audit_type'];
     $db['primaryKey'] = "location_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];
        $db = db_connect();
        $tdata['title']="Location";
        $tdata['button_name']="Add location";
        $tdata['button_id']="course_modal";
        
        $tdata['display_contents'] = [
            "location_id"     => "ID",
            "location_name"   => "Location",
            "cluster_name"    => "Cluster",
            "region_name"     => "Region",
            "country_name"    => "Country",
            "account_manager" => "Account Manager",
            "status_text"     => "Status",
            "action"          => "Action"
        ];
        $data['ajax_url']=base_url("Masters/Location_master/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Location_master/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
         $data['cluster_list']=$db->table("alert_cluster_master")->where("status","1")->get()->getResultArray();
         $data['country_list']=$db->table("alert_country")->where("status","1")->get()->getResultArray();
         $regions1 = $db->table("alert_region")->select("region_name")->where("status", 1)->get()->getResultArray();
         $regions2 = $db->table("alert_hse_region_master")->select("region_name")->where("status", 1)->get()->getResultArray();
         $mergedRegions = array_filter(array_unique(array_merge(
             array_column($regions1, 'region_name'),
             array_column($regions2, 'region_name')
         )), function($val) { return trim((string)$val) !== ''; });
         natcasesort($mergedRegions);
         $data['region_list'] = array_map(function($r) { return ['region_name' => $r]; }, array_values($mergedRegions));
        // Fetch active account managers
        $data['account_managers'] = $db->query("
            SELECT DISTINCT user_name FROM alert_users 
            WHERE (LOWER(user_designation) LIKE '%account%' OR LOWER(user_designation) LIKE '%manager%') 
            AND status IN (1, '1') AND user_name IS NOT NULL AND TRIM(user_name) != '' 
            ORDER BY user_name ASC
        ")->getResultArray();
            
        return view("Master/add_location_master",$data);
    }
    public function table_ajax(){
        
        // Multi-Cluster ACL: Filter locations by assigned clusters - 14/11/25
        helper('designation_acl');
        $builder = $this->BaseModel;
        if (isClusterManager() || isWHManager()) {
            $userClusters = getClusterManagerAssignedCluster();
            if (!empty($userClusters)) {
                $builder->whereIn('cluster_name', $userClusters);
            }
        }
        $tdata['table_data'] = $builder->findAll();
             $statusMessages = [
                 0 => '<span class="badge badge-warning">Pending</span>',
                1 => '<span class="badge badge-info">Verified</span>',
                2 => '<span class="badge badge-success">Active</span>',
                3 => '<span class="badge badge-secondary">Deactivated</span>'
                ];
       
        foreach($tdata['table_data'] as $key=>$row){
            $tdata['table_data'][$key]['sr_no'] = $key+1;
            $tdata['table_data'][$key]['status_text'] = $statusMessages[$row['status']] ?? '-';
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Location_master/save_details/".$row['location_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Location_master/save_details/".$row['location_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Location_master/save_details/".$row['location_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             $edit = '<button data-ajax-url="'.base_url("Masters/Location_master/get_form_data/".$row['location_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['location_id'].');">
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
// 			$tdata['table_data'][$key]['action']=$active.$deactive.$delete.$edit.$loginas;
                $tdata['table_data'][$key]['action']="<center>".$active.$deactive.$delete.$edit."</center>";
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
                        $oldData = $this->BaseModel->find($id); // Get old data for sync
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
                    // Synchronize Location Master changes (location_name, cluster_name, account_manager, region_name, audit_type, status) across all OE/HSE/Gemba tables
                    $syncService = new \App\Services\ClientSiteDependencySyncService();
                    $syncRes = $syncService->syncLocationMasterChanges(
                        $oldData,
                        $postData,
                        session()->get('user_id') ?? 0,
                        session()->get('user_name') ?? 'System'
                    );

                    if ($syncRes['status'] === 0) {
                        $responce['status'] = "0";
                        $responce['message'] = $syncRes['message'];
                        echo json_encode($responce);
                        return;
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
                        
                        // Insert into OE or HSE Master based on audit_type
                        $db = db_connect();
                        if(isset($postData['location_name']) && isset($postData['audit_type'])) {
                            if ($postData['audit_type'] === 'OE' || $postData['audit_type'] === 'OE and HSE') {
                                $existingOe = $db->table('alert_client')->where('client_name', trim($postData['location_name']))->get()->getRowArray();
                                if(!$existingOe) {
                                    $oeInsert = [
                                        'client_name' => trim($postData['location_name']),
                                        'region' => $postData['region_name'] ?? '',
                                        'cluster' => $postData['cluster_name'] ?? '',
                                        'account_manager' => $postData['account_manager'] ?? '',
                                        'status' => 1
                                    ];
                                    $db->table('alert_client')->insert($oeInsert);
                                }
                            }
                            
                            if ($postData['audit_type'] === 'HSE' || $postData['audit_type'] === 'OE and HSE') {
                                $existingHse = $db->table('alert_hse_client_master')->where('client_name', trim($postData['location_name']))->get()->getRowArray();
                                if(!$existingHse) {
                                    $hseInsert = [
                                        'client_name' => trim($postData['location_name']),
                                        'region' => $postData['region_name'] ?? '',
                                        'cluster' => $postData['cluster_name'] ?? '',
                                        'account_manager' => $postData['account_manager'] ?? '',
                                        'status' => 1
                                    ];
                                    $db->table('alert_hse_client_master')->insert($hseInsert);
                                }
                            }
                        }
                    }
                }
                echo json_encode($responce);
    }
}
