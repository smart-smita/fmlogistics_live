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
     // reverted location field back to dropdown from manual text input
     // changes on 27/01/26: Added account_manager field
     $db['allowedFields'] = ['client_name','email', 'location','region','cluster','status', 'account_manager'];
     $db['primaryKey'] = "client_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        if (function_exists('isHseAuditType') && isHseAuditType()) {
            return redirect()->to(base_url('Masters/Hse_client'));
        }
        $data = [];

        $tdata['title']="";
        $tdata['button_name']="Add Client";
        $tdata['button_id']="Client";
        $tdata['example2'] = "client_master_table";
        
        $tdata['display_contents'] = [
            "client_id"=>"ID",
            "client_name"=>"Client Name",
            "email"=>"E-mail",
            "region"=>"Region",
            "cluster"=>"cluster",
            "account_manager"=>"Account Manager",
            "action"=>"Action"
            ];
        $tdata['export_csv'] = true;
        $tdata['top_dynamic_content'] = '
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
            <div class="btn-group client-status-filter-group shadow-sm" role="group" aria-label="Status Filter">
                <button type="button" class="btn btn-sm btn-primary active text-white" id="filter_active_btn" onclick="setStatusFilter(\'active\')">
                    <i class="fa fa-check-circle me-1"></i> Active (<span id="active_count_badge">0</span>)
                </button>
                <button type="button" class="btn btn-sm" id="filter_all_btn" onclick="setStatusFilter(\'all\')">
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
        // Fetch users with designation 'Cluster Manager' for cluster dropdown
        $data['cluster_list'] = $db->table("alert_users")
            ->select("user_name AS cluster_name, user_id AS cluster_id")
            ->where("status", 1)
            ->groupStart()
                ->like("LOWER(user_designation)", "cluster")
            ->groupEnd()
            ->orderBy("user_name", "ASC")
            ->get()->getResultArray();

        $db = db_connect();
        // Fetch users with designation 'Account Manager'
        $data['account_managers'] = $db->table("alert_users")
            ->select("user_name, user_email")
            ->where("status", 1)
            ->groupStart()
                ->like("LOWER(user_designation)", "account")
            ->groupEnd()
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
        if (isClusterManager() || isWHManager()) {
            $userClusters = getClusterManagerAssignedCluster();
            if (!empty($userClusters)) {
                $countBuilder->whereIn('cluster', $userClusters);
            }
        }
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
        if (isClusterManager() || isWHManager()) {
            $userClusters = getClusterManagerAssignedCluster();
            if (!empty($userClusters)) {
                $builder->whereIn('cluster', $userClusters);
            }
        }

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
            $tdata['table_data'][$key]['sr_no'] = $key+1;
            $active = '<button class="btn btn-icon btn-sm btn-success me-1" title="Activate" onclick="url_call_ajax(\''.base_url("Masters/Client/save_details/".$row['client_id']).'/active\',$(this));">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
			$deactive = '<button type="button" class="btn btn-icon btn-sm btn-danger me-1" title="Deactivate" onclick="openDeactivateConfirmModal('.$row['client_id'].');">
                			<span class="indicator-label svg-icon svg-icon-3">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
				$delete = '<button data-ajax-url="'.base_url("Masters/Client/save_details/".$row['client_id']).'/delete" class="btn btn-icon btn-sm btn-danger me-1" title="Delete" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-3">
								<i class="fa fa-trash"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

             $edit = '<button data-ajax-url="'.base_url("Masters/Client/get_form_data/".$row['client_id']).'" class="btn btn-icon btn-sm btn-primary me-1" title="Edit" onclick="edit_id(this,'.$row['client_id'].');">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
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
				$tdata['table_data'][$key]['action']='<div class="d-flex flex-column align-items-center justify-content-center py-1">'
                    . '<div class="mb-2">' . $msg . '</div>'
                    . '<div class="d-inline-flex align-items-center justify-content-center">'
                    . $edit . $active . $deactive . $delete . $loginas
                    . '</div>'
                    . '</div>';
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
        helper('designation_acl');
        if (!isSuperAdmin() && !isAdmin() && !isAuditor() && !isClusterManager() && !isHigherAuthority()) {
            $responce['status'] = "0";
            $responce['message'] = 'Unauthorized access. You do not have permission to modify site allocations.';
            return $this->response->setJSON($responce);
        }

        $request = service('request');
        $postData = $request->getVar();
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

                    // Preserve existing location on update if not provided
                    if (!isset($postData['location']) || trim((string)$postData['location']) === '') {
                        if (!empty($oldData['location'])) {
                            $postData['location'] = $oldData['location'];
                        } else {
                            $postData['location'] = trim($postData['client_name'] ?? '');
                        }
                    }

                    $db = \Config\Database::connect();
                    $db->transStart();

                    // Synchronize OE Client changes (client_name, location, cluster, account_manager, region, status) across all OE dependent tables
                    $postData['client_id'] = $id;
                    $syncService = new \App\Services\ClientSiteDependencySyncService();
                    $syncRes = $syncService->syncOeClientChanges(
                        $oldData,
                        $postData,
                        session()->get('user_id') ?? 0,
                        session()->get('user_name') ?? 'System'
                    );

                    if ($syncRes['status'] === 0) {
                        $db->transRollback();
                        $responce['status'] = "0";
                        $responce['message'] = $syncRes['message'];
                        echo json_encode($responce);
                        return;
                    }

                    if($this->BaseModel->update($id,$postData)){
                        $db->transComplete();
                        if ($db->transStatus() === FALSE) {
                            $responce['status'] = "0";
                            $responce['message'] = "Transaction failed. Changes were rolled back.";
                        } else {
                            $responce['status'] = "1";
                            $responce['message'] = "Data saved successfully";
                        }
                    } else {
                        $db->transRollback();
                    }
                }else{
                    $postData['status']="1";
                    if (!isset($postData['location']) || trim((string)$postData['location']) === '') {
                        $postData['location'] = trim($postData['client_name'] ?? '');
                    }
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

    public function get_nc_action_history()
    {
        $req = service('request');
        $ncDetailId = (int) ($req->getGet('nc_detail_id') ?: $req->getPost('nc_detail_id'));
        $moduleType = strtoupper(trim((string) ($req->getGet('module_type') ?: $req->getPost('module_type') ?: 'GEMBA')));
        $siteName = trim((string) ($req->getGet('site_name') ?: $req->getPost('site_name') ?: ''));

        if (!$ncDetailId) {
            return $this->response->setJSON(['status' => 0, 'message' => 'NC ID is required', 'history' => []]);
        }

        $db = \Config\Database::connect();
        $history = [];

        $statusLabels = [
            0 => 'Open',
            1 => 'Working',
            2 => 'Auditor Review',
            3 => 'Closed',
            4 => 'Hold',
            5 => 'Cluster Review',
            6 => 'Rejected'
        ];

        // Helper to resolve user name and designation
        $resolveUser = function($actionBy) use ($db) {
            if ($actionBy === '0' || $actionBy === 0 || empty($actionBy)) {
                return ['name' => 'Super Admin', 'designation' => 'Super Admin'];
            }
            if (is_numeric($actionBy)) {
                $user = $db->table('alert_users')->where('user_id', (int)$actionBy)->get()->getRowArray();
                if ($user) {
                    return [
                        'name' => $user['user_name'] ?? 'User #' . $actionBy,
                        'designation' => $user['user_designation'] ?? 'Staff'
                    ];
                }
            }
            // If actionBy is already a string name
            $user = $db->table('alert_users')->where('LOWER(TRIM(user_name))', strtolower(trim((string)$actionBy)))->get()->getRowArray();
            return [
                'name' => !empty($user['user_name']) ? $user['user_name'] : $actionBy,
                'designation' => !empty($user['user_designation']) ? $user['user_designation'] : 'Staff'
            ];
        };

        // 1. Fetch from alert_gemba_nc_audit_trail
        $auditTrailRows = $db->table('alert_gemba_nc_audit_trail')
            ->where('gemba_audit_id', $ncDetailId)
            ->orderBy('id', 'ASC')
            ->get()
            ->getResultArray();

        // 2. Fetch the NC row itself for metadata and milestone fallbacks
        $gembaRow = null;
        if ($moduleType === 'GEMBA' || $moduleType === 'HSE') {
            $gembaRow = $db->table('alert_gemba_audits')
                ->where('gemba_sr_no', $ncDetailId)
                ->get()
                ->getRowArray();

            if (!$gembaRow && $moduleType === 'HSE') {
                $gembaRow = $db->table('alert_gemba_audits')
                    ->where('hse_detail_id', $ncDetailId)
                    ->get()
                    ->getRowArray();
            }
            if ($gembaRow && empty($siteName)) {
                $siteName = $gembaRow['site_name'] ?? ($gembaRow['client_name'] ?? ($gembaRow['location'] ?? ''));
            }
        }

        // Add CREATED milestone if gembaRow exists
        if ($gembaRow) {
            $createdDate = !empty($gembaRow['audit_report_date']) ? $gembaRow['audit_report_date'] . ' 09:00:00' : (!empty($gembaRow['created_at']) ? $gembaRow['created_at'] : null);
            $auditorUser = !empty($gembaRow['lead_auditor']) ? $gembaRow['lead_auditor'] : (!empty($gembaRow['auditor_name']) ? $gembaRow['auditor_name'] : 'Lead Auditor');
            $uInfo = $resolveUser($auditorUser);
            
            $history[] = [
                'action_type' => 'CREATED',
                'performed_by_user_name' => $uInfo['name'],
                'performed_by_designation' => !empty($uInfo['designation']) && $uInfo['designation'] !== 'Staff' ? $uInfo['designation'] : 'Auditor',
                'created_at' => $createdDate ?: 'Initial Audit',
                'previous_status' => 'New Audit',
                'new_status' => 'Open',
                'action_remarks' => !empty($gembaRow['point_description']) ? substr(strip_tags($gembaRow['point_description']), 0, 150) . '...' : 'NC Logged during Audit',
                'evidence_attachment' => $gembaRow['before_photo'] ?? null
            ];
        }

        if (!empty($auditTrailRows)) {
            foreach ($auditTrailRows as $trail) {
                $statusFrom = (int) $trail['status_from'];
                $statusTo   = (int) $trail['status_to'];
                $uInfo = $resolveUser($trail['action_by']);

                $actionType = 'ACTION';
                if ($statusTo === 1) {
                    $actionType = 'WORKING';
                } elseif ($statusTo === 5) {
                    $actionType = 'SUBMITTED TO CLUSTER';
                } elseif ($statusTo === 2) {
                    $actionType = ($statusFrom === 5) ? 'FORWARDED TO AUDITOR' : 'SUBMITTED TO AUDITOR';
                } elseif ($statusTo === 3) {
                    $actionType = 'CLOSED';
                } elseif ($statusTo === 0) {
                    $actionType = 'REJECTED';
                }

                $history[] = [
                    'action_type' => $actionType,
                    'performed_by_user_name' => $uInfo['name'],
                    'performed_by_designation' => $uInfo['designation'],
                    'created_at' => $trail['action_date'],
                    'previous_status' => $statusLabels[$statusFrom] ?? ('Status ' . $statusFrom),
                    'new_status' => $statusLabels[$statusTo] ?? ('Status ' . $statusTo),
                    'action_remarks' => $trail['remarks'] ?? '',
                    'evidence_attachment' => $trail['evidence'] ?? null
                ];
            }
        } elseif ($gembaRow) {
            // Fallback: build milestones from alert_gemba_audits columns if trail is empty
            if (!empty($gembaRow['working_date']) || !empty($gembaRow['nc_worked_by'])) {
                $uInfo = $resolveUser($gembaRow['nc_worked_by'] ?? '');
                $history[] = [
                    'action_type' => 'WORKING',
                    'performed_by_user_name' => $uInfo['name'],
                    'performed_by_designation' => $uInfo['designation'],
                    'created_at' => $gembaRow['working_date'] ?? 'N/A',
                    'previous_status' => 'Open',
                    'new_status' => 'Working',
                    'action_remarks' => $gembaRow['working_remarks'] ?? ($gembaRow['nc_remark'] ?? 'NC marked as Working'),
                    'evidence_attachment' => $gembaRow['working_uploaded_file'] ?? ($gembaRow['nc_after_photo'] ?? null)
                ];
            }

            if (!empty($gembaRow['nc_cluster_reviewed_date']) || !empty($gembaRow['nc_cluster_reviewed_by'])) {
                $uInfo = $resolveUser($gembaRow['nc_cluster_reviewed_by'] ?? '');
                $history[] = [
                    'action_type' => 'SUBMITTED TO CLUSTER',
                    'performed_by_user_name' => $uInfo['name'],
                    'performed_by_designation' => $uInfo['designation'],
                    'created_at' => $gembaRow['nc_cluster_reviewed_date'] ?? 'N/A',
                    'previous_status' => 'Working',
                    'new_status' => 'Cluster Review',
                    'action_remarks' => 'Submitted for Cluster Review',
                    'evidence_attachment' => null
                ];
            }

            if (!empty($gembaRow['nc_auditor_reviewed_date']) || !empty($gembaRow['nc_auditor_reviewed_by'])) {
                $uInfo = $resolveUser($gembaRow['nc_auditor_reviewed_by'] ?? '');
                $history[] = [
                    'action_type' => 'FORWARDED TO AUDITOR',
                    'performed_by_user_name' => $uInfo['name'],
                    'performed_by_designation' => $uInfo['designation'],
                    'created_at' => $gembaRow['nc_auditor_reviewed_date'] ?? 'N/A',
                    'previous_status' => 'Cluster Review',
                    'new_status' => 'Auditor Review',
                    'action_remarks' => 'Forwarded to Auditor for Final Review',
                    'evidence_attachment' => null
                ];
            }

            if (!empty($gembaRow['nc_rejected_date']) || !empty($gembaRow['nc_rejected_by'])) {
                $uInfo = $resolveUser($gembaRow['nc_rejected_by'] ?? '');
                $history[] = [
                    'action_type' => 'REJECTED',
                    'performed_by_user_name' => $uInfo['name'],
                    'performed_by_designation' => $uInfo['designation'],
                    'created_at' => $gembaRow['nc_rejected_date'] ?? 'N/A',
                    'previous_status' => 'In Review',
                    'new_status' => 'Open',
                    'action_remarks' => $gembaRow['rejection_reason'] ?? 'Rejected',
                    'evidence_attachment' => null
                ];
            }

            if ((int)$gembaRow['nc_status'] === 3 || !empty($gembaRow['nc_closed_date']) || !empty($gembaRow['closed_date'])) {
                $uInfo = $resolveUser($gembaRow['nc_closed_by'] ?? '');
                $history[] = [
                    'action_type' => 'CLOSED',
                    'performed_by_user_name' => $uInfo['name'],
                    'performed_by_designation' => $uInfo['designation'],
                    'created_at' => $gembaRow['nc_closed_date'] ?: ($gembaRow['closed_date'] ?? 'N/A'),
                    'previous_status' => 'Auditor Review',
                    'new_status' => 'Closed (' . ($gembaRow['closure_status'] ?? 'Closed') . ')',
                    'action_remarks' => $gembaRow['closed_remarks'] ?? ($gembaRow['qhse_remarks'] ?? 'NC Closed'),
                    'evidence_attachment' => $gembaRow['closed_uploaded_file'] ?? null
                ];
            }
        }

        return $this->response->setJSON([
            'status' => 1,
            'site_name' => $siteName,
            'history' => $history
        ]);
    }
}
