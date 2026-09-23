<?php

namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;

class Hse_client extends BaseController
{
    protected $BaseModel;

    public function __construct(){
        helper("form");
        $db['table'] = 'alert_hse_client_master';
        $db['primaryKey'] = "client_id";
        $db['allowedFields'] = [
            'category',
            'sub_category',
            'client_name',
            'email',
            'location',
            'region',
            'cluster',
            'account_manager',
            'status'
        ];
        $this->BaseModel = new CRUDBaseModel($db);
    }

    public function index()
    {
        $tdata['title'] = "HSE Client";
        $tdata['button_name'] = "Add HSE Client";
        $tdata['button_id'] = "HseClient";

        $tdata['display_contents'] = [
            "client_id"       => "ID",
            "category"        => "Category",
            "sub_category"    => "Sub Category",
            "client_name"     => "Client Name",
            "email"           => "E-mail",
            "location_name"   => "Location",
            "region"          => "Region",
            "cluster"         => "Cluster",
            "account_manager" => "Account Manager",
            "action"        =>  "Action"
        ];

        $data['ajax_url'] = base_url("Masters/Hse_client/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Hse_client/table_ajax");
        $data['table'] = view("Layout/table-view", $tdata);

        $db = db_connect();
        $data['location_list'] = $db->table("alert_location_master")->where("status","1")->get()->getResultArray();
        
        // Fetch from new masters
        $data['category_list'] = $db->table("alert_hse_site_category")->where("status","1")->get()->getResultArray();
        $data['region_list']   = $db->table("alert_hse_region_master")->where("status","1")->get()->getResultArray();
        
        // Fetch active clusters from cluster master, location master and existing client master
        $clusters1 = $db->table("alert_cluster_master")->select("cluster_name AS cluster")->whereIn("status", [1, '1'])->where("cluster_name IS NOT NULL")->where("TRIM(cluster_name) != ''")->get()->getResultArray();
        $clusters2 = $db->table("alert_location_master")->select("cluster_name AS cluster")->whereIn("status", [1, '1'])->where("cluster_name IS NOT NULL")->where("TRIM(cluster_name) != ''")->get()->getResultArray();
        $clusters3 = $db->table("alert_hse_client_master")->select("cluster")->where("cluster IS NOT NULL")->where("TRIM(cluster) != ''")->get()->getResultArray();
        
        $mergedClusters = array_filter(array_unique(array_merge(
            array_column($clusters1, 'cluster'),
            array_column($clusters2, 'cluster'),
            array_column($clusters3, 'cluster')
        )), function($val) { return trim((string)$val) !== ''; });
        natcasesort($mergedClusters);
        $data['cluster_list'] = array_map(function($c) { return ['cluster' => $c]; }, array_values($mergedClusters));

        // Fetch active account managers from alert_users, alert_client and existing client master
        $am1 = $db->table("alert_users")
            ->select("user_name AS account_manager")
            ->groupStart()
                ->like("LOWER(user_designation)", "account")
                ->orLike("LOWER(user_designation)", "manager")
            ->groupEnd()
            ->whereIn("status", [1, '1'])
            ->where("user_name IS NOT NULL")
            ->where("TRIM(user_name) != ''")
            ->get()->getResultArray();
        $am2 = $db->table("alert_hse_client_master")->select("account_manager")->where("account_manager IS NOT NULL")->where("TRIM(account_manager) != ''")->get()->getResultArray();
        $am3 = $db->table("alert_client")->select("account_manager")->where("account_manager IS NOT NULL")->where("TRIM(account_manager) != ''")->get()->getResultArray();

        $mergedAMs = array_filter(array_unique(array_merge(
            array_column($am1, 'account_manager'),
            array_column($am2, 'account_manager'),
            array_column($am3, 'account_manager')
        )), function($val) { return trim((string)$val) !== ''; });
        natcasesort($mergedAMs);
        $data['am_list'] = array_map(function($am) { return ['account_manager' => $am]; }, array_values($mergedAMs));

        return view("Master/add_hse_client", $data);
    }

    public function table_ajax()
    {
        helper('designation_acl');
        $builder = $this->BaseModel;
        applySiteACLFilter($builder, 'HSE', 'client_name', 'client_id');
        $tdata['table_data'] = $builder->findAll();

        $statusMessages = [
            0 => '<span class="badge badge-warning">Pending</span>',
            1 => '<span class="badge badge-info">Verified</span>',
            2 => '<span class="badge badge-success">Active</span>',
            3 => '<span class="badge badge-secondary">Deactivated</span>'
        ];

        $db = db_connect();
        
        // Let's get category mapping
        $catList = $db->table("alert_hse_site_category")->get()->getResultArray();
        $categories = array_column($catList, 'site_category_name', 'site_category_id');
        
        $subCatList = $db->table("alert_hse_sub_category")->get()->getResultArray();
        $subCategories = array_column($subCatList, 'sub_category_name', 'sub_category_id');

        $regList = $db->table("alert_hse_region_master")->get()->getResultArray();
        $regions = array_column($regList, 'region_name', 'region_id');

        foreach($tdata['table_data'] as $key => $row){

            // If you store IDs, you display names. If you store names, display names. 
            // The task implies dropdown lists. The options will likely store IDs for Category/SubCategory/Region or Names. Let's assume Names for Region/Cluster to align with previous code, but IDs for Sub/Cat since it's dependent. Let's resolve safely.
            // If numeric ID, map to name. Else use as is.
            $c_name = $row['category'];
            if(is_numeric($c_name) && isset($categories[$c_name])) $c_name = $categories[$c_name];
            else if(empty($c_name)) $c_name = '-';
            
            $sc_name = $row['sub_category'];
            if(is_numeric($sc_name) && isset($subCategories[$sc_name])) $sc_name = $subCategories[$sc_name];
            else if(empty($sc_name)) $sc_name = '-';

            $r_name = $row['region'];
            if(is_numeric($r_name) && isset($regions[$r_name])) $r_name = $regions[$r_name];
            else if(empty($r_name)) $r_name = '-';

            $tdata['table_data'][$key]['category']        = $c_name;
            $tdata['table_data'][$key]['sub_category']    = $sc_name;
            $tdata['table_data'][$key]['region']          = $r_name;

            $tdata['table_data'][$key]['location_name']   = $row['location'] ?: '-';
            $tdata['table_data'][$key]['sr_no']           = $key+1;

            $active = '';
            $deactive = '';
            $delete = '';
            $edit = '';

            if (session()->get('admin_flag') == 1 || session()->get('role') == "admin") {
                $active = '<button class="btn btn-icon btn-success"
                    onclick="url_call_ajax(\''.base_url("Masters/Hse_client/save_details/".$row['client_id']).'/active\',$(this));">
                    <i class="fa fa-unlock"></i></button>';

                $deactive = ''; // Removed deactivate button per user request

                $delete = '<button class="btn btn-icon btn-danger"
                    data-ajax-url="'.base_url("Masters/Hse_client/save_details/".$row['client_id']).'/delete"
                    onclick="delete_row(this);">
                    <i class="fa fa-trash"></i></button>';

                $edit = '<button class="btn btn-icon btn-primary"
                    data-ajax-url="'.base_url("Masters/Hse_client/get_form_data/".$row['client_id']).'"
                    onclick="edit_id(this,'.$row['client_id'].');">
                    <i class="fa fa-edit"></i></button>';

                if($row['status']=="0"){ }
                else if($row['status']=="1"){ $active=""; }
                else if($row['status']=="2"){ $delete=""; }
                else if($row['status']=="3"){ }
            }

            $tdata['table_data'][$key]['action'] =
                "<center>".$statusMessages[$row['status']]."<br><br>".$active.$delete.$edit."</center>";
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);
    }

    public function get_form_data($id){
        $db = db_connect();
        $data = $this->BaseModel->find($id);

        if ($data) {
            if (is_numeric($data['category'])) {
                $cat = $db->table("alert_hse_site_category")->where("site_category_id", $data['category'])->get()->getRowArray();
                if ($cat) $data['category'] = $cat['site_category_name'];
            }
            if (is_numeric($data['sub_category'])) {
                $subCat = $db->table("alert_hse_sub_category")->where("sub_category_id", $data['sub_category'])->get()->getRowArray();
                if ($subCat) $data['sub_category'] = $subCat['sub_category_name'];
            }
            if (is_numeric($data['region'])) {
                $reg = $db->table("alert_hse_region_master")->where("region_id", $data['region'])->get()->getRowArray();
                if ($reg) $data['region'] = $reg['region_name'];
            }
        }

        echo json_encode([
            'status'=>1,
            'data'=>$data
        ]);
    }

    public function save_details($id=null,$action=null)
    {
        try {
            $request  = service('request');
            $postData = $request->getVar();

            if (isset($postData['honeypot'])) {
                unset($postData['honeypot']);
            }

            // -------------------------------
            // SERVER SIDE VALIDATION RULES
            // -------------------------------
            // Skip validation if we are just changing status (active/deactive/delete)
            if (!$action) {
                $rules = [
                    'category'    => 'required',
                    'client_name' => 'required|min_length[3]',
                    'location'    => 'required|min_length[2]',
                    'email'       => 'permit_empty|valid_email'
                ];

                if (!$this->validate($rules)) {
                    return $this->response->setJSON([
                        'status'  => 0,
                        'message' => 'Validation failed',
                        'errors'  => $this->validator->getErrors()
                    ]);
                }
            }

            // -------------------------------
            // STATUS / ACTION HANDLING
            // -------------------------------
            if ($id) {
                if ($action) {
                    if (!in_array($action, ['active','deactive','delete'])) {
                        throw new \Exception('Invalid action');
                    }

                    if ($action === 'active')   $postData['status'] = 1;
                    if ($action === 'deactive') $postData['status'] = 0;
                    if ($action === 'delete')   $postData['status'] = 2;
                }
                
                $oldData = $this->BaseModel->find($id); // Get old data for sync

                // Synchronize HSE Client changes (client_name, location, cluster, account_manager, region, status) across all HSE & Gemba dependent tables
                $syncService = new \App\Services\ClientSiteDependencySyncService();
                $syncRes = $syncService->syncHseClientChanges(
                    $oldData,
                    $postData,
                    session()->get('user_id') ?? 0,
                    session()->get('user_name') ?? 'System'
                );

                if ($syncRes['status'] === 0) {
                    return $this->response->setJSON([
                        'status'  => 0,
                        'message' => $syncRes['message']
                    ]);
                }

                if (!$this->BaseModel->update($id, $postData)) {
                    throw new \Exception('Failed to update record');
                }

            } else {
                $postData['status'] = 1;

                if (!$this->BaseModel->insert($postData)) {
                    throw new \Exception('Failed to insert record');
                }
                
                // Insert into Location Master if it doesn't exist
                if(isset($postData['client_name'])) {
                    $db = db_connect();
                    $existingLoc = $db->table('alert_location_master')->where('location_name', trim($postData['client_name']))->get()->getRowArray();
                    if(!$existingLoc) {
                        $locInsert = [
                            'location_name' => trim($postData['client_name']),
                            'region_name' => $postData['region'] ?? '',
                            'cluster_name' => $postData['cluster'] ?? '',
                            'account_manager' => $postData['account_manager'] ?? '',
                            'audit_type' => 'HSE',
                            'status' => 1
                        ];
                        $db->table('alert_location_master')->insert($locInsert);
                    }
                }
            }

            return $this->response->setJSON([
                'status'  => 1,
                'message' => 'Data saved successfully'
            ]);

        } catch (\Throwable $e) {

            log_message('error', 'HSE Client Error: '.$e->getMessage());

            return $this->response->setJSON([
                'status'  => 0,
                'message' => 'Something went wrong. Please try again.'
            ]);
        }
    }

    public function get_pending_nc_counts($id)
    {
        $db = db_connect();
        $client = $this->BaseModel->find($id);
        if (!$client) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Client not found']);
        }

        $clientName = trim((string)$client['client_name']);
        $locationName = trim((string)$client['location']);

        $siteName = !empty($locationName) ? $locationName : $clientName;

        // ===============================
        // Get latest HSE Audit
        // ===============================
        $latestAuditBuilder = $db->table('alert_hse_audit_master')
            ->select('hse_audit_id')
            ->groupStart()
                ->where('client_name', $clientName)
                ->orWhere('client_name', $siteName)
                //->orWhere('location', $siteName)
            ->groupEnd();

        // Prefer report_date if available
        $latestAudit = $latestAuditBuilder
            ->orderBy('report_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        // ===============================
        // Count Gemba NCs
        // ===============================
        $gembaSql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN nc_status = 0 THEN 1 ELSE 0 END) AS open_cnt,
                SUM(CASE WHEN nc_status = 1 THEN 1 ELSE 0 END) AS working_cnt,
                SUM(CASE WHEN nc_status = 5 THEN 1 ELSE 0 END) AS cr_cnt,
                SUM(CASE WHEN nc_status = 2 THEN 1 ELSE 0 END) AS ar_cnt
            FROM alert_gemba_audits g
            WHERE g.site_name = ?
            AND g.status != 2
            AND g.nc_status IN (0,1,2,5)
            AND (
                    g.source_module <> 'hse_audit'
                 OR
                    (
                        g.source_module = 'hse_audit'
                        AND g.hse_audit_id = (
                            SELECT m.hse_audit_id
                            FROM alert_hse_audit_master m
                            WHERE m.client_name = g.site_name
                               OR m.location = g.site_name
                            ORDER BY m.report_date DESC, m.hse_audit_id DESC
                            LIMIT 1
                        )
                    )
            )
        ";

        $gembaResult = $db->query($gembaSql, [$siteName])->getRowArray();

        $g_open = (int)($gembaResult['open_cnt'] ?? 0);
        $g_work = (int)($gembaResult['working_cnt'] ?? 0);
        $g_cr = (int)($gembaResult['cr_cnt'] ?? 0);
        $g_ar = (int)($gembaResult['ar_cnt'] ?? 0);

        // ===============================
        // Count HSE NCs (Latest Audit Only & Finding = NO)
        // ===============================
        $h_open = 0; $h_work = 0; $h_cr = 0; $h_ar = 0;

        if ($latestAudit) {
            $hseRows = $db->table('alert_hse_audit_details')
                ->select('nc_status')
                ->where('hse_audit_id', $latestAudit->hse_audit_id)
                ->where('finding', 'NO')
                ->where('status !=', 2)
                ->whereIn('nc_status', [0, 1, 2, 5])
                ->get()
                ->getResultArray();

            foreach ($hseRows as $r) {
                switch ((int)$r['nc_status']) {
                    case 0: $h_open++; break;
                    case 1: $h_work++; break;
                    case 5: $h_cr++; break;
                    case 2: $h_ar++; break;
                }
            }
        }

        return $this->response->setJSON([
            'status' => 1,
            'client_name' => $clientName,
            'location' => $locationName,
            'gemba' => [
                'open' => $g_open,
                'working' => $g_work,
                'cluster_review' => $g_cr,
                'auditor_review' => $g_ar,
                'total' => $g_open + $g_work + $g_cr + $g_ar
            ],
            'hse' => [
                'open' => $h_open,
                'working' => $h_work,
                'cluster_review' => $h_cr,
                'auditor_review' => $h_ar,
                'total' => $h_open + $h_work + $h_cr + $h_ar
            ]
        ]);
    }

    public function deactivate_and_close_ncs()
    {
        $db = db_connect();
        $req = service('request');
        
        $clientId = $req->getVar('client_id');
        $closeDate = $req->getVar('closure_date');
        $closeReason = $req->getVar('closure_remarks');
        $closeStatusFront = $req->getVar('closure_status');
        $userId = session()->get('user_id') ?? 0;
        $userName = session()->get('user_name') ?? 'System';

        if (empty($clientId) || empty($closeDate) || empty($closeReason) || empty($closeStatusFront)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'All mandatory fields are required.']);
        }

        $client = $this->BaseModel->find($clientId);
        if (!$client) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Client not found.']);
        }

        $clientName = trim((string)$client['client_name']);
        $locationName = trim((string)$client['location']);
        $siteName = !empty($locationName) ? $locationName : $clientName;
        $now = date('Y-m-d H:i:s');

        // ===============================
        // Attachment Upload
        // ===============================
        $attachmentUrl = null;
        $file = $req->getFile('attachment');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $ext = strtolower($file->getExtension());
            if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'])) {
                $newName = $file->getRandomName();
                $file->move(FCPATH . 'uploads/nc_after_photo/', $newName);
                $attachmentUrl = base_url('uploads/nc_after_photo/' . $newName);
            }
        }

        $db->transStart();

        // ===============================
        // Get latest HSE Audit
        // ===============================
        $latestAuditBuilder = $db->table('alert_hse_audit_master')
            ->select('hse_audit_id')
            ->groupStart()
                ->where('client_name', $clientName)
                ->orWhere('client_name', $siteName)
                //->orWhere('location', $siteName)
            ->groupEnd();

        $latestAudit = $latestAuditBuilder
            ->orderBy('report_date', 'DESC')
            ->limit(1)
            ->get()
            ->getRow();

        // ===============================
        // 1. Update Gemba NCs
        // ===============================
        $gembaFetchSql = "
            SELECT gemba_sr_no, target_date, audit_report_date
            FROM alert_gemba_audits g
            WHERE g.site_name = ?
            AND g.status != 2
            AND g.nc_status IN (0,1,2,5)
            AND (
                    g.source_module <> 'hse_audit'
                 OR
                    (
                        g.source_module = 'hse_audit'
                        AND g.hse_audit_id = (
                            SELECT m.hse_audit_id
                            FROM alert_hse_audit_master m
                            WHERE m.client_name = g.site_name
                               OR m.location = g.site_name
                            ORDER BY m.report_date DESC, m.hse_audit_id DESC
                            LIMIT 1
                        )
                    )
            )
        ";

        $gembaRows = $db->query($gembaFetchSql, [$siteName])->getResultArray();

        $gembaClosed = count($gembaRows);
        foreach($gembaRows as $r) {
            
            $closedTs = strtotime($closeDate);
            $targetDate = $r['target_date'] ?? null;
            $closureStatus = $closeStatusFront;

            if ($closeStatusFront === 'Closed' && !empty($targetDate) && $targetDate != '0000-00-00' && $targetDate != '1970-01-01') {
                if ($closedTs <= strtotime($targetDate)) {
                    $closureStatus = 'On-Time';
                } else {
                    $closureStatus = 'Delayed';
                }
            }

            $gembaUpdate = [
                'point_status' => 'Closed',
                'closure_status' => $closureStatus,
                'point_category' => 'Closed Category',
                'closed_date' => date('Y-m-d', $closedTs),
                'nc_status' => 3,
                'nc_closed_by' => $userName,
                'nc_closed_date' => $closeDate,
                'nc_remark' => $closeReason,
                'updated_by' => $userName,
                'update_date' => $now,
                'weeknum_closed' => date('W', $closedTs),
                'month_closed' => date('Y-m-01', $closedTs),
                'year_month_closed' => date('Y-m', $closedTs),
                'weeknum_yearmonth_closed' => date('Y-m', $closedTs) . '-W' . date('W', $closedTs)
            ];

            if ($attachmentUrl) {
                $gembaUpdate['nc_after_photo'] = $attachmentUrl;
            }

            // Ageing Calculation
            if (!empty($r['audit_report_date'])) {
                $reportTs = strtotime($r['audit_report_date']);
                $days = floor(($closedTs - $reportTs) / (60 * 60 * 24));
                $gembaUpdate['ageing_days'] = $days >= 0 ? $days : 0;
                
                if ($gembaUpdate['ageing_days'] <= 30) {
                    $gembaUpdate['age_bracket'] = '<=30';
                } elseif ($gembaUpdate['ageing_days'] <= 60) {
                    $gembaUpdate['age_bracket'] = '31-60';
                } elseif ($gembaUpdate['ageing_days'] <= 90) {
                    $gembaUpdate['age_bracket'] = '61-90';
                } else {
                    $gembaUpdate['age_bracket'] = '>90';
                }
            }

            $db->table('alert_gemba_audits')->where('gemba_sr_no', $r['gemba_sr_no'])->update($gembaUpdate);
        }

        // ===============================
        // 2. Update HSE NCs (All Audits for Client & Finding = NO)
        // ===============================
        $allAudits = $db->table('alert_hse_audit_master')
            ->select('hse_audit_id')
            ->groupStart()
                ->where('client_name', $clientName)
                ->orWhere('location', $locationName)
            ->groupEnd()
            ->where('status !=', 2)
            ->get()->getResultArray();

        $auditIds = array_column($allAudits, 'hse_audit_id');
        $hseRows = [];
        
        if (!empty($auditIds)) {
            $hseRows = $db->table('alert_hse_audit_details')
                ->select('id')
                ->whereIn('hse_audit_id', $auditIds)
                ->where('finding', 'NO')
                ->where('status !=', 2)
                ->whereIn('nc_status', [0, 1, 2, 5])
                ->get()->getResultArray();
        }

        $hseClosed = count($hseRows);
        foreach($hseRows as $r) {
            $hseUpdate = [
                'nc_status' => 3,
                'nc_closed_by' => $userName,
                'nc_closed_by_user' => $userId,
                'nc_closed_date' => $closeDate,
                'remark' => $closeReason,
                'nc_remark' => $closeReason,
                'update_date' => $now
            ];

            if ($attachmentUrl) {
                $hseUpdate['nc_after_photo'] = $attachmentUrl;
            }

            $db->table('alert_hse_audit_details')->where('id', $r['id'])->update($hseUpdate);
        }

        // 3. Update Client Status
        $this->BaseModel->update($clientId, ['status' => 3]);
        
        // Update location status as well
        $db->table('alert_location_master')
            ->where('location_name', $clientName)
            ->update(['status' => 3]);

        // 4. Log Deactivation
        if ($db->tableExists('alert_client_deactivation_logs')) {
            $db->table('alert_client_deactivation_logs')->insert([
                'client_name' => $clientName,
                'location' => $locationName,
                'closed_by' => $userName,
                'closed_date' => $closeDate,
                'reason' => $closeReason,
                'closure_category' => $closeStatusFront,
                'total_gemba_closed' => $gembaClosed,
                'total_hse_closed' => $hseClosed,
                'action' => 'Deactivate',
                'is_system_generated' => 1,
                'created_at' => $now
            ]);
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Failed to close NCs. Please try again.']);
        }

        return $this->response->setJSON([
            'status' => 1,
            'message' => "Client Deactivated Successfully.\n{$gembaClosed} Gemba NCs Closed.\n{$hseClosed} HSE NCs Closed."
        ]);
    }
}
