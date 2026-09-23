<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Client extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_client';
     // changes on 1/11/25 by darsh: location field changed from dropdown (location_id) to manual text input
     // changes on 27/01/26: Added account_manager field
     $db['allowedFields'] = ['client_name','email', 'location','region','cluster','status', 'account_manager'];
     $db['primaryKey'] = "client_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="";
        $tdata['button_name']="Add Client";
        $tdata['button_id']="Client";
        
        $tdata['display_contents'] = [
            "client_id"=>"ID",
            "client_name"=>"Client Name",
            "email"=>"E-mail",
            "location_name"=>"Location",
            "region"=>"Region",
            "cluster"=>"cluster",
            "account_manager"=>"Account Manager",
            "action"=>"Action"
            ];
        $tdata['export_csv'] = true;
        $tdata['top_dynamic_content'] = '
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div class="btn-group" role="group" aria-label="Status Filter">
                <button type="button" class="btn btn-sm btn-primary active text-white" id="filter_active_btn" onclick="setStatusFilter(\'active\')">
                    <i class="fa fa-check-circle me-1"></i> Active (<span id="active_count_badge">0</span>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="filter_all_btn" onclick="setStatusFilter(\'all\')">
                    <i class="fa fa-list me-1"></i> All (<span id="all_count_badge">0</span>)
                </button>
            </div>
        </div>
        ';
        $data['ajax_url']=base_url("Masters/Client/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Client/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        $db = db_connect();
        $data['location_list']=$db->table("alert_location_master")->where("status","1")->get()->getResultArray();
        
        $db = db_connect();
        $data['region_list']=$db->table("alert_region")->where("status","1")->get()->getResultArray();
        
        $db = db_connect();
        $data['cluster_list']=$db->table("alert_cluster_master")->where("status","1")->get()->getResultArray();

        $db = db_connect();
        // Fetch users with designation 'Account Manager'
        $data['account_managers'] = $db->table("alert_users")
            ->where("user_designation", "Account Manager")
            ->where("status", 1)
            ->orderBy("user_name", "ASC")
            ->get()->getResultArray();
        
        return view("Master/add_client",$data);
    }
    public function table_ajax(){
        helper('designation_acl');
        $request = service('request');
        $statusFilter = $request->getVar('status_filter') ?? 'active';

        // 1. Calculate record counts for Active (status = 1) and All (status != 2) matching ACL permissions
        $countBuilder = $this->BaseModel->select("status")->where('alert_client.status !=', 2);
        applySiteACLFilter($countBuilder, 'OE', 'client_name', 'client_id');
        $allRecords = $countBuilder->findAll();
        $allCount = count($allRecords);
        $activeCount = 0;
        foreach ($allRecords as $rec) {
            if ((string)($rec['status'] ?? '') === '1' || (int)($rec['status'] ?? 0) === 1) {
                $activeCount++;
            }
        }

        // 2. Build filtered records for DataTables (always exclude soft deleted status = 2)
        $builder = $this->BaseModel->select("alert_client.*")->where('alert_client.status !=', 2);
        applySiteACLFilter($builder, 'OE', 'client_name', 'client_id');

        if ($statusFilter === 'active') {
            $builder->where('alert_client.status', 1);
        }

        $tdata['table_data'] = $builder->findAll();

        $statusMessages = [
            0 => '<span class="badge badge-warning">Pending</span>',
            1 => '<span class="badge badge-success">Active</span>',
            2 => '<span class="badge badge-danger">Deleted</span>',
            3 => '<span class="badge badge-secondary">Deactivated</span>'
        ];
       
        foreach($tdata['table_data'] as $key=>$row){
            if (!empty($row['location'])) {
                $tdata['table_data'][$key]['location_name'] = $row['location'];
            } else {
                $tdata['table_data'][$key]['location_name'] = "-";
            }
            $tdata['table_data'][$key]['sr_no'] = $key+1;
            $active = '<button class="btn btn-icon btn-success" title="Activate" onclick="url_call_ajax(\''.base_url("Masters/Client/save_details/".$row['client_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button type="button" class="btn btn-icon btn-danger" title="Deactivate" onclick="openDeactivateConfirmModal('.$row['client_id'].');">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Client/save_details/".$row['client_id']).'/delete" class="btn btn-icon btn-danger" title="Delete" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

             $edit = '<button data-ajax-url="'.base_url("Masters/Client/get_form_data/".$row['client_id']).'" class="btn btn-icon btn-primary" title="Edit" onclick="edit_id(this,'.$row['client_id'].');">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>
											';
										$loginas = '';

											if($row['status']=="0"){
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											}else if($row['status']=="1"){
											    $active = "";
											}else if($row['status']=="3"){
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-secondary";
											}
				$stVal = $row['status'] ?? 0;
				$msg = $statusMessages[$stVal] ?? '<span class="badge badge-secondary">'.$stVal.'</span>';
				$tdata['table_data'][$key]['action']="<center>".$msg."<br><br>".$active.$deactive.$delete.$edit.$loginas."</center>";
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        $tdata['counts'] = [
            'active' => $activeCount,
            'all'    => $allCount
        ];
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
                     unset($postData['honeypot']);   
                    }
                    
                // Backend validation if not an action (status change)
                if(!isset($action)) {
                    $rules = [
                        'client_name' => [
                            'rules' => 'required',
                            'errors' => ['required' => 'Please enter Client Name.']
                        ],
                        'region' => [
                            'rules' => 'required',
                            'errors' => ['required' => 'Please select Region.']
                        ],
                        'cluster' => [
                            'rules' => 'required',
                            'errors' => ['required' => 'Please select Cluster.']
                        ]
                    ];
                    
                    if (! $this->validate($rules)) {
                        $responce['status'] = "0";
                        $errors = $this->validator->getErrors();
                        $responce['message'] = reset($errors);
                        echo json_encode($responce);
                        return;
                    }
                }
                    
                if(isset($id)){
                        $responce['message'] = "Data updation failed";
                        $oldData = $this->BaseModel->find($id); // Get old data for sync
                        if(isset($action)){
                            switch($action){
                                case "active":
                                    $postData['status']= "1";
                                    break;
                                case "deactive":
                                    $postData['status']= "3";
                                    break;
                                case "delete":
                                    $postData['status']= "2";
                                    break;
                            }
                        }

                    // Synchronize OE Client changes (client_name, location, cluster, account_manager, region, status) across all OE dependent tables
                    $syncService = new \App\Services\ClientSiteDependencySyncService();
                    $syncRes = $syncService->syncOeClientChanges(
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
                        
                        // When inserting a new client, insert into Location Master if it doesn't exist
                        $db = db_connect();
                        if(isset($postData['client_name'])) {
                            $existingLoc = $db->table('alert_location_master')->where('location_name', trim($postData['client_name']))->get()->getRowArray();
                            if(!$existingLoc) {
                                $locInsert = [
                                    'location_name' => trim($postData['client_name']),
                                    'region_name' => $postData['region'] ?? '',
                                    'cluster_name' => $postData['cluster'] ?? '',
                                    'account_manager' => $postData['account_manager'] ?? '',
                                    'audit_type' => 'OE',
                                    'status' => 1
                                ];
                                $db->table('alert_location_master')->insert($locInsert);
                            }
                        }
                    }
                }
                echo json_encode($responce);
    }

    public function get_manager_email() {
        $request = service('request');
        $userName = $request->getVar('user_name');
        
        if (empty($userName)) {
            return $this->response->setJSON(['status' => 0, 'email' => '']);
        }
        
        $db = db_connect();
        $user = $db->table('alert_users')
            ->select('user_email')
            ->where('user_name', trim($userName))
            ->where('status', 1)
            ->get()
            ->getRowArray();
            
        if ($user && !empty($user['user_email'])) {
            return $this->response->setJSON(['status' => 1, 'email' => $user['user_email']]);
        }
        
        return $this->response->setJSON(['status' => 0, 'email' => '']);
    }

    // changes on 1/11/25 by darsh: Add new location to database
    public function add_location(){
        $request = service('request');
        $locationName = $request->getVar('location_name');
        
        $responce = [
            'status' => 0,
            'message' => 'Location name is required'
        ];
        
        if (empty($locationName) || trim($locationName) === '') {
            echo json_encode($responce);
            return;
        }
        
        $db = db_connect();
        
        // Check if location already exists
        $existing = $db->table('alert_location_master')
            ->where('location_name', trim($locationName))
            ->get()
            ->getRowArray();
        
        if ($existing) {
            $responce['status'] = 1;
            $responce['message'] = 'Location already exists';
            $responce['location_id'] = $existing['location_id'];
            echo json_encode($responce);
            return;
        }
        
        // Insert new location
        $locationData = [
            'location_name' => trim($locationName),
            'status' => 1,
            // Set default values for other required fields if needed
            'cluster_name' => '',
            'region_name' => '',
            'country_name' => ''
        ];
        
        if ($db->table('alert_location_master')->insert($locationData)) {
            $locationId = $db->insertID();
            $responce['status'] = 1;
            $responce['message'] = 'Location added successfully';
            $responce['location_id'] = $locationId;
            $responce['location_name'] = trim($locationName);
        } else {
            $responce['message'] = 'Failed to add location';
        }
        
        echo json_encode($responce);
    }
    
    public function get_pending_nc_counts($client_id) {
        $db = db_connect();
        
        $client = $this->BaseModel->find($client_id);
        if (!$client) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Client not found']);
        }
        $client_name = $client['client_name'];
        $location = $client['location'];
        $region = $client['region'];
        $client_status = ($client['status'] == 1) ? 'Active' : (($client['status'] == 0) ? 'Pending' : 'Deactivated');
        $client_code = isset($client['client_code']) ? $client['client_code'] : 'N/A';
        
        // Count OE NCs from alert_final_structured_audit_details
        $oeQuery = $db->query("
            SELECT d.status, COUNT(*) as count 
            FROM alert_final_structured_audit_details d 
            JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id 
                WHERE a.client_name = ? AND a.region = ? AND d.status != 3 AND a.reaudit = 0 AND d.audit_finding = 'NO'
                GROUP BY d.status
        ", [$client_name, $region]);
        
        $oeCounts = ['open' => 0, 'working' => 0, 'cluster_review' => 0, 'auditor_review' => 0, 'total' => 0];
        foreach ($oeQuery->getResultArray() as $row) {
            $status = $row['status'];
            $count = (int)$row['count'];
            
            if ($status == 0) $oeCounts['open'] += $count;
            elseif ($status == 1) $oeCounts['working'] += $count;
            elseif ($status == 6) $oeCounts['cluster_review'] += $count; // Status mappings might vary based on your system
            elseif ($status == 2) $oeCounts['auditor_review'] += $count;
            
            $oeCounts['total'] += $count;
        }

        return $this->response->setJSON([
            'status' => 1,
            'client_name' => $client_name,
            'client_code' => $client_code,
            'client_status' => $client_status,
            'location' => $location,
            'region' => $region,
            'oe' => $oeCounts
        ]);
    }

    public function deactivate_and_close_ncs() {
        $client_id = $this->request->getPost('client_id');
        if (empty($client_id)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid client ID']);
        }
        
        $db = db_connect();
        $client = $this->BaseModel->find($client_id);
        
        // In case find() returns an array of rows
        if ($client && isset($client[0])) {
            $client = $client[0];
        }

        if(!$client || !isset($client['client_name'])) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Client not found or invalid data']);
        }
        
        $client_name = $client['client_name'];
        $region = $client['region'];
        $db->transStart();
        
        // Close OE NCs
        $db->query("
            UPDATE alert_final_structured_audit_details d
            JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id
            SET d.status = 3,
                d.nc_closed_by = ?,
                d.audit_remark = ?
            WHERE a.client_name = ? AND a.region = ? AND d.status != 3
        ", [session()->get('user_id'), 'Automatically closed due to client deactivation.', $client_name, $region]);
        
        // Deactivate Client
        $this->BaseModel->update($client_id, ['status' => 3]);
        
        $db->transComplete();
        
        if ($db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Failed to deactivate client and close NCs.']);
        }
        
        return $this->response->setJSON(['status' => 1, 'message' => 'Client deactivated and NCs closed successfully.']);
    }

    /**
     * Get site manager details (Cluster Manager, Account Manager, Region, Cluster)
     * based on selected Site/Client across OE, HSE, and Gemba modules.
     */
    public function get_site_manager_info()
    {
        $request = service('request');
        $site = trim((string)($request->getVar('site_identifier') ?? $request->getVar('site_name') ?? $request->getVar('client_name') ?? ''));
        $module = strtoupper(trim((string)($request->getVar('module_type') ?? 'OE')));

        if (empty($site)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Site identifier required']);
        }

        $db = db_connect();
        $info = [
            'status' => 1,
            'client_name' => $site,
            'region_name' => '',
            'cluster_name' => '',
            'cluster_manager_name' => '',
            'account_manager_name' => ''
        ];

        if ($module === 'HSE') {
            $row = $db->table('alert_hse_client_master')
                ->where('LOWER(TRIM(client_name))', strtolower($site))
                ->orWhere('LOWER(TRIM(location))', strtolower($site))
                ->where('status !=', 2)
                ->get()
                ->getRowArray();

            if ($row) {
                $info['client_name'] = $row['client_name'] ?? $site;
                $info['region_name'] = $row['region'] ?? '';
                $info['cluster_name'] = $row['cluster'] ?? '';
                $info['cluster_manager_name'] = $row['cluster'] ?? '';
                $info['account_manager_name'] = $row['account_manager'] ?? '';
            }
        } else if ($module === 'GEMBA') {
            $row = $db->table('alert_gemba_sites')
                ->where('LOWER(TRIM(site_name))', strtolower($site))
                ->where('status', 1)
                ->get()
                ->getRowArray();

            if ($row) {
                $info['client_name'] = $row['site_name'] ?? $site;
                $info['region_name'] = $row['region'] ?? '';
                $info['cluster_name'] = $row['cluster_name'] ?? '';
                $info['cluster_manager_name'] = $row['cluster_manager_spoc'] ?? ($row['cluster_name'] ?? '');
                $info['account_manager_name'] = $row['account_manager'] ?? '';
            }
        } else {
            // OE / Normal Audit
            $row = $db->table('alert_client')
                ->where('LOWER(TRIM(client_name))', strtolower($site))
                ->orWhere('LOWER(TRIM(location))', strtolower($site))
                ->where('status !=', 2)
                ->get()
                ->getRowArray();

            if ($row) {
                $info['client_name'] = $row['client_name'] ?? $site;
                $info['region_name'] = $row['region'] ?? '';
                $info['cluster_name'] = $row['cluster'] ?? '';
                $info['cluster_manager_name'] = $row['cluster'] ?? '';
                $info['account_manager_name'] = $row['account_manager'] ?? '';
            }
        }

        // Additional lookup in alert_user_client_mapping if manager names are empty
        if (empty($info['cluster_manager_name']) || empty($info['account_manager_name'])) {
            $mappings = $db->table('alert_user_client_mapping m')
                ->select('m.user_id, u.user_name, u.user_designation')
                ->join('alert_users u', 'u.user_id = m.user_id', 'inner')
                ->where('LOWER(TRIM(m.site_name))', strtolower($info['client_name']))
                ->where('u.status', 1)
                ->get()
                ->getResultArray();

            foreach ($mappings as $m) {
                $desig = strtolower(trim($m['user_designation'] ?? ''));
                if (empty($info['cluster_manager_name']) && strpos($desig, 'cluster manager') !== false) {
                    $info['cluster_manager_name'] = $m['user_name'];
                }
                if (empty($info['account_manager_name']) && strpos($desig, 'account manager') !== false) {
                    $info['account_manager_name'] = $m['user_name'];
                }
            }
        }

        return $this->response->setJSON($info);
    }

    /**
     * Get NC Action History timeline for a specific NC detail ID and module.
     * Includes IDOR / ACL site security verification.
     */
    public function get_nc_action_history()
    {
        $request = service('request');
        $ncDetailId = (int)$request->getVar('nc_detail_id');
        $moduleType = strtoupper(trim((string)($request->getVar('module_type') ?? 'OE')));
        $siteName = trim((string)$request->getVar('site_name'));

        if (empty($ncDetailId)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'NC Detail ID required', 'history' => []]);
        }

        helper(['designation_acl']);
        if (!empty($siteName) && !verifySiteAccess($siteName, $moduleType)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Access denied to this site history', 'history' => []]);
        }

        $db = db_connect();
        if (!$db->tableExists('alert_nc_action_history')) {
            return $this->response->setJSON(['status' => 0, 'message' => 'History table not found', 'history' => []]);
        }

        $history = $db->table('alert_nc_action_history')
            ->where('nc_detail_id', $ncDetailId)
            ->where('module_type', $moduleType)
            ->orderBy('history_id', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 1,
            'nc_detail_id' => $ncDetailId,
            'module_type' => $moduleType,
            'site_name' => $siteName,
            'history' => $history
        ]);
    }
}


