<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Inplant_nc_tracker extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;
 
 
  public function __construct(){
        // changes on 16/10/25 by darsh: Using simple ACL helper without database changes
        // ACL: Load designation ACL helper - 12/11/25
        helper(["form", "simple_acl", "designation_acl"]);
     $db = null;
     $db['table']         = 'alert_hse_audit_details';
     // changes on 13/11/25 by darsh: Add nc_worked_by and nc_closed_by_user to allowedFields
     $db['allowedFields'] = ['hse_audit_id','question_audit_id','question_name','audit_question','client_leased','inplant','fm_leased','remark','attachment','nc_closed_date','nc_reviewed_date','nc_close_by','nc_reviewed_by','nc_status','nc_remark','nc_after_photo','client_leased_json','inplant_json','fm_leased_json','status','nc_worked_by','nc_closed_by_user'];
     $db['primaryKey'] = "id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];
        $tdata['title']="Inplant Nc Tracker";
        // $tdata['button_name']="Add Inplant_nc_tracker";
        $tdata['button_id']="user_modal";
        // changes on 10/11/12 by darsh: pass multi-filter qs to ajax url
        $req = service('request');
        $qs = http_build_query([
            'region'   => $req->getGet('region'),
            'cluster'  => $req->getGet('cluster'),
            'location' => $req->getGet('location'),
            'month'    => $req->getGet('month'),
        ]);
        
        // changes on 13/11/25 by darsh: Add NC Worked By and NC Closed By User columns
        $tdata['display_contents'] = [
            'id'               => 'Id',
            'action'           => 'Action',
            'audit_no'         => 'Audit no.',
            'audit_name'       => 'Audit Name',
            'auditor_name'     => 'Auditor Name',
            'auditee_name'     => 'Auditee Name',
            'client_name'      => 'Client Name',
            'audit_date'       => 'Audit Date',
            'region'           => 'Region',
            'score'            => 'Score',
            'perform_audit_by' => 'Site Category',
            'question_name'    => 'Question Name',
            'audit_question'   => 'Audit Question',
            'inplant'          => 'Inplant',
            'remark'           => 'Remark',
            'attachment'       => 'Attachment',
            'nc_closed_date'   => 'Nc Closed Date',
            'nc_reviewed_date' => 'Nc Reviewed Date',
            'nc_closed_by'     => 'Nc Closed By',
            'nc_reviewed_by'   => 'Nc Reviewed By',
            'nc_worked_by'     => 'NC Worked By',
            'nc_closed_by_user' => 'NC Closed By User',
            'nc_remark'        => 'Nc Remark',
            'nc_after_photo'   => 'Nc After Photo',
            // 'nc_status'        => 'Nc Status',
            ];
        $data['ajax_url']=base_url("Masters/Inplant_nc_tracker/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Inplant_nc_tracker/table_ajax").($qs ? ('?'.$qs) : '');
        // $data['user_designation'] = ["Engineer","Reporting manager",""];
        
        
        // $data['table'] ="";
        // NEW CHANGES: Top counters matching current filter (inplant = 'NO')
        $db2 = db_connect();
        $countQuery = $db2->query("SELECT COUNT(*) AS found_count, nc_status FROM alert_hse_audit_details WHERE inplant = 'NO' GROUP BY nc_status");
        $counts = array_column($countQuery->getResultArray(), 'found_count', 'nc_status');
        $openCount = $counts[0] ?? 0;
        $closedCount = $counts[2] ?? 0;
        $workingCount = $counts[1] ?? 0;
        $datatop='<div class="row g-5 g-xl-8">
                    <div class="col-md-4">
                        <a href="'.base_url('Masters/Inplant_nc_tracker/template_type_filter/0').'" class="card bg-primary hoverable mb-xl-8">
                            <div class="card-body" style="padding: 1rem 2.25rem;">
                                <div class="fw-semibold text-gray-100">Open('.$openCount.')</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="'.base_url('Masters/Inplant_nc_tracker/template_type_filter/2').'" class="card bg-dark hoverable mb-xl-8">
                            <div class="card-body" style="padding: 1rem 2.25rem;">
                                <div class="fw-semibold text-gray-100">Close('.$closedCount.')</div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="'.base_url('Masters/Inplant_nc_tracker/template_type_filter/1').'" class="card bg-warning hoverable mb-xl-8">
                            <div class="card-body" style="padding: 1rem 2.25rem;">
                                <div class="fw-semibold text-white">Working('.$workingCount.')</div>
                            </div>
                        </a>
                    </div>
                </div>';
        // changes on 10/11/12 by darsh: Select2 filter bar fed from DB
        $db2 = db_connect();
        $regions  = $db2->table('alert_region')->select('region_name')->where('status', 1)->get()->getResultArray();
                $clusterBuilder = $db2->table('alert_location_master')->select('DISTINCT(cluster_name) cluster_name', false)->where('status', 1);
        // ACL: Retrieve user cluster FIRST
        $userClusterACL = null;
        if (isClusterManager() || isWHManager()) {
            $userClusterACL = getClusterManagerAssignedCluster();
        }

        if (!empty($userClusterACL)) {
            $clusterBuilder->whereIn('cluster_name', $userClusterACL);
        }
        $clusters = $clusterBuilder->get()->getResultArray();

        $locBuilder = $db2->table('alert_location_master')->select('location_name')->where('status', 1);
        if (!empty($userClusterACL)) {
            $locBuilder->whereIn('cluster_name', $userClusterACL);
        }
        $locations = $locBuilder->get()->getResultArray();
        $selRegion   = (string)($req->getGet('region') ?? '');
        $selCluster  = (string)($req->getGet('cluster') ?? '');
        $selLocation = (string)($req->getGet('location') ?? '');
        $selMonth    = (string)($req->getGet('month') ?? '');
        $opt = function(array $rows, string $col, string $selected){ $h = '<option value="">Select '.$col.'</option>'; foreach($rows as $r){ $v=trim($r[$col]??''); if(!$v) continue; $sel = ($v===$selected)?' selected':''; $h.='<option value="'.htmlspecialchars($v,ENT_QUOTES).'"'.$sel.'>'.$v.'</option>'; } return $h; };
        $filterBar = '
            <form method="get" class="card mb-5 p-3" style="overflow:visible">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Region</label>
                        <select id="flt_region" name="region" class="form-select form-select-sm select2">'. $opt($regions,'region_name',$selRegion) .'</select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cluster</label>
                        <select id="flt_cluster" name="cluster" class="form-select form-select-sm select2" data-selected="'.htmlspecialchars($selCluster,ENT_QUOTES).'">'. $opt($clusters,'cluster_name',$selCluster) .'</select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Location</label>
                        <select id="flt_location" name="location" class="form-select form-select-sm select2" data-selected="'.htmlspecialchars($selLocation,ENT_QUOTES).'">'. $opt($locations,'location_name',$selLocation) .'</select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Month (YYYY-MM)</label>
                        <input type="month" name="month" value="'.htmlspecialchars($selMonth, ENT_QUOTES).'" class="form-control form-control-sm"/>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-sm btn-primary w-100" type="submit">Show</button>
                    </div>
                </div>
            </form>
            <script>
            $(function(){
                $(".select2").select2();
                $("#flt_region").on("change", function(){
                    var region = $(this).val();
                    $("#flt_cluster").empty().append("<option value=\"\">Loading...</option>").trigger("change");
                    $("#flt_location").empty().append("<option value=\"\">Select Location</option>").trigger("change");
                    $.post("'.base_url('Customer/Audit_dashboard/get_clusters_by_region').'", { region_name: region }, function(rows){
                        var opts = "<option value=\\\"\\\">Select cluster_name</option>";
                        if(Array.isArray(rows)){ rows.forEach(function(r){ var v = (r.cluster_name||\" \").trim(); if(v){ opts += \"<option>\"+v+\"</option>\"; } });}
                        $("#flt_cluster").html(opts).val("'.htmlspecialchars($selCluster,ENT_QUOTES).'").trigger("change");
                    });
                });
                $("#flt_cluster").on("change", function(){
                    var cluster = $(this).val();
                    var region = $("#flt_region").val();
                    $("#flt_location").empty().append("<option value=\"\">Loading...</option>").trigger("change");
                    $.post("'.base_url('Customer/Audit_dashboard/get_locations_by_cluster').'", { region_name: region, cluster_name: cluster }, function(rows){
                        var opts = "<option value=\\\"\\\">Select location_name</option>";
                        if(Array.isArray(rows)){ rows.forEach(function(r){ var v = (r.location_name||\" \").trim(); if(v){ opts += \"<option>\"+v+\"</option>\"; } });}
                        $("#flt_location").html(opts).val("'.htmlspecialchars($selLocation,ENT_QUOTES).'").trigger("change");
                    });
                });
                if("'.($selRegion !== '' ? '1':'').'" !== ""){ $("#flt_region").trigger("change"); }
            });
            </script>';
        $data['table'] =$filterBar.$datatop;
      
        
        $data['table'] .= view("Layout/table-view",$tdata);
        return view("Master/add_inplant_nc_tracker",$data);
    }
    public function template_type_filter($nc_status)
    {
        $data = [];
        $tdata['title']="Inplant Nc Tracker";
        // $tdata['button_name']="Add Inplant_nc_tracker";
        $tdata['button_id']="user_modal";
        // changes on 10/11/12 by darsh: pass multi-filter qs to ajax url
        $req = service('request');
        $qs = http_build_query([
            'region'   => $req->getGet('region'),
            'cluster'  => $req->getGet('cluster'),
            'location' => $req->getGet('location'),
            'month'    => $req->getGet('month'),
        ]);
        
        $tdata['display_contents'] = [
            'id'=>'Id',
        'audit_no'       => 'Audit no.',
        'audit_name' => 'Audit Name',
        'auditor_name' => 'Auditor Name',
        'auditee_name' => 'Auditee Name',
        'client_name' => 'Client Name',
        'audit_date'=>'Audit Date',
        'template_date'=>'Completion  Date',
        'region'=>'Region',
        'location'=>'Site Location',
        'score'=>'Score',
        'perform_audit_by'=>'Site Category',
        'auditor_name' => 'Auditor Name',
        'question_name'  => 'Question Name',
        'audit_question' => 'Audit Question',
        // 'client_leased'  => 'Client Leased',
         'inplant'        => 'Inplant',
        // 'fm_leased'      => 'FM Leased',
        'remark'         => 'Remark',
        'attachment'     => 'Attachment',
        'nc_closed_date' => 'nc_closed_date',
        'nc_reviewed_date' => 'nc_reviewed_date',
        'nc_closed_by' => 'nc_closed_by',
        'nc_reviewed_by'=>'Nc Reviewed By',
        'nc_remark'=>'Nc Remark',
        'nc_after_photo'=>'Nc After Photo',
        'nc_status'  => "Nc Status",
        'action' =>'Action'
        
            ];
        $data['ajax_url']=base_url("Masters/Inplant_nc_tracker/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Inplant_nc_tracker/table_ajax/".$nc_status).($qs ? ('?'.$qs) : '');
        // $data['user_designation'] = ["Engineer","Reporting manager",""];
        
        
        // $data['table'] ="";
        $datatop='<div class="row g-5 g-xl-8">
                    <div class="col-md-4">
                        <a href="'.base_url('Masters/Inplant_nc_tracker/template_type_filter/0').'" class="card bg-primary hoverable mb-xl-8">
                            <!--begin::Body-->
                            <div class="card-body" style="padding: 1rem 2.25rem;">
                               
                                <div class="fw-semibold text-gray-100">Open('.($counts[0] ?? 0).')</div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <a href="'.base_url('Masters/Inplant_nc_tracker/template_type_filter/2').'" class="card bg-dark hoverable mb-xl-8">
                            <div class="card-body" style="padding: 1rem 2.25rem;">
                                <div class="fw-semibold text-gray-100">Close('.($counts[2] ?? 0).')</div>
                            </div>
                        </a>
                    </div>
                    
                    <div class="col-md-4">
                        <a href="'.base_url('Masters/Inplant_nc_tracker/template_type_filter/1').'" class="card bg-warning hoverable mb-xl-8">
                            <div class="card-body" style="padding: 1rem 2.25rem;">
                                <div class="fw-semibold text-white">Working('.($counts[1] ?? 0).')</div>
                            </div>
                        </a>
                    </div>
                </div>
                ';
        // changes on 10/11/12 by darsh: Select2 filter bar fed from DB
        $db2 = db_connect();
        $regions  = $db2->table('alert_region')->select('region_name')->where('status', 1)->get()->getResultArray();
        
        // ACL: Retrieve user cluster FIRST
        $userClusterACL = null;
        if (isClusterManager() || isWHManager()) {
            $userClusterACL = getClusterManagerAssignedCluster();
        }

        $clusterBuilder = $db2->table('alert_location_master')->select('DISTINCT(cluster_name) cluster_name', false)->where('status', 1);
        if (!empty($userClusterACL)) {
            $clusterBuilder->whereIn('cluster_name', $userClusterACL);
        }
        $clusters = $clusterBuilder->get()->getResultArray();

        $locBuilder = $db2->table('alert_location_master')->select('location_name')->where('status', 1);
        if (!empty($userClusterACL)) {
            $locBuilder->whereIn('cluster_name', $userClusterACL);
        }
        $locations = $locBuilder->get()->getResultArray();
        $selRegion   = (string)($req->getGet('region') ?? '');
        $selCluster  = (string)($req->getGet('cluster') ?? '');
        $selLocation = (string)($req->getGet('location') ?? '');
        $selMonth    = (string)($req->getGet('month') ?? '');
        $opt = function(array $rows, string $col, string $selected){ $h = '<option value="">Select '.$col.'</option>'; foreach($rows as $r){ $v=trim($r[$col]??''); if(!$v) continue; $sel = ($v===$selected)?' selected':''; $h.='<option value="'.htmlspecialchars($v,ENT_QUOTES).'"'.$sel.'>'.$v.'</option>'; } return $h; };
        $filterBar = '
            <form method="get" class="card mb-5 p-3" style="overflow:visible">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Region</label>
                        <select id="flt_region" name="region" class="form-select form-select-sm select2">'. $opt($regions,'region_name',$selRegion) .'</select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Cluster</label>
                        <select id="flt_cluster" name="cluster" class="form-select form-select-sm select2" data-selected="'.htmlspecialchars($selCluster,ENT_QUOTES).'">'. $opt($clusters,'cluster_name',$selCluster) .'</select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Location</label>
                        <select id="flt_location" name="location" class="form-select form-select-sm select2" data-selected="'.htmlspecialchars($selLocation,ENT_QUOTES).'">'. $opt($locations,'location_name',$selLocation) .'</select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Month (YYYY-MM)</label>
                        <input type="month" name="month" value="'.htmlspecialchars($selMonth, ENT_QUOTES).'" class="form-control form-control-sm"/>
                    </div>
                    <div class="col-md-1">
                        <button class="btn btn-sm btn-primary w-100" type="submit">Show</button>
                    </div>
                </div>
            </form>
            <script>
            $(function(){
                $(".select2").select2();
                $("#flt_region").on("change", function(){
                    var region = $(this).val();
                    $("#flt_cluster").empty().append("<option value=\"\">Loading...</option>").trigger("change");
                    $("#flt_location").empty().append("<option value=\"\">Select Location</option>").trigger("change");
                    $.post("'.base_url('Customer/Audit_dashboard/get_clusters_by_region').'", { region_name: region }, function(rows){
                        var opts = "<option value=\\\"\\\">Select cluster_name</option>";
                        if(Array.isArray(rows)){ rows.forEach(function(r){ var v = (r.cluster_name||\" \").trim(); if(v){ opts += \"<option>\"+v+\"</option>\"; } });}
                        $("#flt_cluster").html(opts).val("'.htmlspecialchars($selCluster,ENT_QUOTES).'").trigger("change");
                    });
                });
                $("#flt_cluster").on("change", function(){
                    var cluster = $(this).val();
                    var region = $("#flt_region").val();
                    $("#flt_location").empty().append("<option value=\"\">Loading...</option>").trigger("change");
                    $.post("'.base_url('Customer/Audit_dashboard/get_locations_by_cluster').'", { region_name: region, cluster_name: cluster }, function(rows){
                        var opts = "<option value=\\\"\\\">Select location_name</option>";
                        if(Array.isArray(rows)){ rows.forEach(function(r){ var v = (r.location_name||\" \").trim(); if(v){ opts += \"<option>\"+v+\"</option>\"; } });}
                        $("#flt_location").html(opts).val("'.htmlspecialchars($selLocation,ENT_QUOTES).'").trigger("change");
                    });
                });
                if("'.($selRegion !== '' ? '1':'').'" !== ""){ $("#flt_region").trigger("change"); }
            });
            </script>';
        $data['table'] =$filterBar.$datatop;
      
        
        $data['table'] .= view("Layout/table-view",$tdata);
        return view("Master/add_inplant_nc_tracker",$data);
    }
        
    
  public function table_ajax($nc_status=null)
{
    // changes on 14/10/25 by darsh: Implemented ACL filtering for regional data access
    $db = db_connect();
    
    // Build base query with ACL filtering
    // changes on 13/11/25 by darsh: Add nc_worked_by and nc_closed_by_user columns to SELECT and join with alert_users to get usernames
    $sql = "SELECT 
                alert_hse_audit_details.id,
                alert_hse_audit_details.question_name, 
                alert_hse_audit_details.audit_question,
                alert_hse_audit_details.inplant, 
                alert_hse_audit_details.remark, 
                alert_hse_audit_details.attachment,
                alert_hse_audit_details.nc_closed_date,
                alert_hse_audit_details.nc_reviewed_date,
                alert_hse_audit_details.nc_closed_by,
                alert_hse_audit_details.nc_reviewed_by,
                alert_hse_audit_details.nc_worked_by,
                alert_hse_audit_details.nc_closed_by_user,
                worked_user.user_name AS nc_worked_by_name,
                closed_user.user_name AS nc_closed_by_user_name,
                alert_hse_audit_details.nc_status,
                alert_hse_audit_details.nc_remark,
                alert_hse_audit_details.nc_after_photo, 
                alert_hse_audit_details.status,
                alert_hse_audit_master.audit_no, 
                alert_hse_audit_master.audit_name, 
                alert_hse_audit_master.auditor_name, 
                alert_hse_audit_master.auditee_name, 
                alert_hse_audit_master.client_name, 
                alert_hse_audit_master.audit_date, 
                alert_hse_audit_master.template_date, 
                alert_hse_audit_master.region, 
                alert_hse_audit_master.location, 
                alert_hse_audit_master.score,
                alert_hse_audit_master.perform_audit_by
            FROM alert_hse_audit_details
            LEFT JOIN alert_hse_audit_master ON alert_hse_audit_master.hse_audit_id = alert_hse_audit_details.hse_audit_id
            LEFT JOIN alert_location_master L ON L.location_name = alert_hse_audit_master.location
            LEFT JOIN alert_users worked_user ON worked_user.user_id = alert_hse_audit_details.nc_worked_by
            LEFT JOIN alert_users closed_user ON closed_user.user_id = alert_hse_audit_details.nc_closed_by_user
            WHERE alert_hse_audit_details.inplant = 'NO'";
    
    $params = [];
    
    // Add nc_status filter if specified
    if(isset($nc_status)) {
        $sql .= " AND alert_hse_audit_details.nc_status = ?";
        $params[] = $nc_status;
    }
    // changes on 10/11/12 by darsh: apply multi-filters for region/cluster/location/month
    $req = service('request');
    $region   = trim((string)$req->getGet('region'));
    $cluster  = trim((string)$req->getGet('cluster'));
    $location = trim((string)$req->getGet('location'));
    $month    = trim((string)$req->getGet('month'));
    if ($region !== '')   { $sql .= " AND alert_hse_audit_master.region = ?"; $params[] = $region; }
    if ($cluster !== '')  { $sql .= " AND L.cluster_name = ?";                 $params[] = $cluster; }
    if ($location !== '') { $sql .= " AND alert_hse_audit_master.location = ?";$params[] = $location; }
    if ($month !== '' && preg_match('/^\d{4}\-\d{2}$/', $month)) {
        $sql .= " AND DATE_FORMAT(alert_hse_audit_master.audit_date, '%Y-%m') = ?";
        $params[] = $month;
    }
    
    // ACL: Apply cluster-based filtering for Cluster Managers - 13/11/25
    if (isClusterManager() || isWHManager()) {
        $userClusters = getClusterManagerAssignedCluster();
        if (!empty($userClusters)) {
            $escapedClusters = array_map([$db, 'escape'], $userClusters);
            $sql .= " AND EXISTS (
                SELECT 1 FROM alert_client 
                WHERE alert_client.client_name = alert_hse_audit_master.client_name
                AND LOWER(TRIM(alert_client.cluster)) IN (" . implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters)) . ")
                AND alert_client.status = 1
            )";
        }
    }
    
    $sql .= " ORDER BY alert_hse_audit_details.id DESC";
    
    $query = $db->query($sql, $params)->getResultArray();
 
     // changes on 10/11/12 by darsh: status labels aligned with OE mapping
     $statusMessages = [                
             0 => '<span class="badge badge-danger">Open</span>',
             1 => '<span class="badge badge-warning">Working</span>',
             2 => '<span class="badge badge-info">Under Review</span>',
            3 => '<span class="badge badge-success">Closed</span>',
            4 => '<span class="badge badge-secondary">Draft</span>',
             ];
                
    $tdata['table_data'] = []; 

    foreach ($query as $row) {
        // NEW CHANGES: Status-driven lock/check buttons using AJAX
        $active = '<button class="btn btn-icon btn-success" title="Move to Working" onclick="updateInplantStatus(' . $row['id'] . ', \'working\');">
                <span class="indicator-label svg-icon svg-icon-2">
                    <i class="fa fa-unlock"></i>
                </span>
            </button>';
        
        $edit = '<button data-ajax-url="'.base_url("Masters/Inplant_nc_tracker/get_form_data/".$row['id']).'" class="btn btn-icon btn-primary" title="Edit / Submit" onclick="edit_id(this,'.$row['id'].');">
                 <span class="indicator-label svg-icon svg-icon-3">
                     <i class="fa fa-edit"></i>
                 </span>
                 <span class="indicator-progress">
                     <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                 </span>
                 </button>';
        // NEW CHANGES: Correct status-based icon visibility logic
        // ACL: Cluster Managers cannot approve/reject - 12/11/25
        // Open (0): lock only; Working (1): edit only; Under Review (2): approve/reject; Closed (3): none
        if($row['nc_status']=="0"){
            $edit = "";
        } elseif($row['nc_status']=="1"){
            $active = "";
        } elseif($row['nc_status']=="2"){
            // Status 2 (Under Review): Show approve (✓) and reject (✗) icons
            // ACL: Cluster Managers cannot approve/reject - only Auditors can - 12/11/25
            if (isClusterManager() || isWHManager()) {
                // Cluster Managers see "Pending Approval" status
                $active = "";
                $edit = '<span class="badge badge-light-info" title="Waiting for approval from Auditor/Higher Authority">Pending Approval</span>';
            } else {
                // Auditors and Higher Authority can approve/reject
                $active = '<button class="btn btn-icon btn-success me-2" title="Approve & Close" onclick="updateInplantStatus(' . $row['id'] . ', \'closed\');"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-check"></i></span></button>';
                $edit = '<button class="btn btn-icon btn-danger" title="Send Back to Working" onclick="updateInplantStatus(' . $row['id'] . ', \'working\');"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-times"></i></span></button>';
            }
        } else if($row['nc_status']=="4"){
            // Draft: only edit icon
            $active = "";
        } else if($row['nc_status']=="3"){
            // Status 3 (Closed): No icons shown
            // ACL: For Cluster Managers, show disabled message - 12/11/25
            if (isClusterManager() || isWHManager()) {
                $edit = '<span class="badge badge-light-secondary" title="Cluster Managers cannot modify closed NCs"></span>';
            }
            $active = "";
        } else {
            $active = ""; $edit = "";
        }

        //$action = $active.' '. $edit;
               $statuss=$row['nc_status'];
        		$action	="<center>".$statusMessages[$row['nc_status']]."<br><br>".$active.$edit."</center>";

    // changes on 7/10/25 by darsh: Robust Before NC photo from uploads/audit_files/{audit_no}
    // Display before photo (always image)
    $attachmentPath = $row['attachment'] ?? '';
    // Normalize stored paths coming from different environments (strip absolute prefixes)
    if (!empty($attachmentPath)) {
        if (preg_match('/(uploads\/.+)$/', $attachmentPath, $m)) { $attachmentPath = $m[1]; }
        elseif (preg_match('/(writable\/.+)$/', $attachmentPath, $m)) { $attachmentPath = $m[1]; }
    }
    // Try constructing from uploads/audit_files/{audit_no} if needed
    $auditNo = $row['audit_no'] ?? '';
    if ((empty($attachmentPath) || !is_file(FCPATH.$attachmentPath)) && !empty($auditNo)) {
        $folder = 'uploads/audit_files/'.$auditNo.'/';
        $base = basename((string)$row['attachment']);
        if (!empty($base) && $base !== '.' && $base !== '..') {
            $candidate = $folder.$base;
            if (is_file(FCPATH.$candidate)) {
                $attachmentPath = $candidate;
            }
        }
        if (empty($attachmentPath) || !is_file(FCPATH.$attachmentPath)) {
            $matches = glob(FCPATH.$folder.'*');
            if ($matches) {
                usort($matches, function($a,$b){ return filemtime($b) <=> filemtime($a); });
                $picked = null;
                foreach ($matches as $mfile) {
                    $ext = strtolower(pathinfo($mfile, PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg','jpeg','png','gif'])) { $picked = $mfile; break; }
                }
                if (!$picked) { $picked = $matches[0]; }
                $rel = str_replace(FCPATH, '', $picked);
                $rel = str_replace('\\', '/', $rel);
                $attachmentPath = $rel;
            }
        }
    }
    $attachment_html = "<img src='".base_url($attachmentPath)."' onerror=\"this.src='".env("defaultLogo")."'\" height='50' width='50' />";
    
    // Display after photo/file with proper handling for different file types
    $nc_after_photos = "-";
    if (!empty($row['nc_after_photo'])) {
        $file_path = $row['nc_after_photo'];
        if (preg_match('/(uploads\/.+)$/', $file_path, $m)) { $file_path = $m[1]; }
        elseif (preg_match('/(writable\/.+)$/', $file_path, $m)) { $file_path = $m[1]; }
        $file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
        
        if (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            // Display as image
            $nc_after_photos = "<img src='".base_url($file_path)."' onerror=\"this.src='".env("defaultLogo")."'\" height='50' width='50' />";
        } elseif (in_array($file_extension, ['pdf'])) {
            // Display as PDF icon with click to open
            $nc_after_photos = "<a href='".base_url($file_path)."' target='_blank' class='btn btn-sm btn-danger'><i class='fa fa-file-pdf'></i> PDF</a>";
        } elseif (in_array($file_extension, ['xls', 'xlsx'])) {
            // Display as Excel icon with click to open
            $nc_after_photos = "<a href='".base_url($file_path)."' target='_blank' class='btn btn-sm btn-success'><i class='fa fa-file-excel'></i> Excel</a>";
        } elseif (in_array($file_extension, ['csv'])) {
            // Display as CSV icon with click to open
            $nc_after_photos = "<a href='".base_url($file_path)."' target='_blank' class='btn btn-sm btn-info'><i class='fa fa-file-csv'></i> CSV</a>";
        }
    }

        $tdata['table_data'][] = [
            'id'               => $row['id'],
            'audit_no'         => $row['audit_no'],
            'audit_name'       => $row['audit_name'],
            'auditor_name'     => $row['auditor_name'],
            'auditee_name'     => $row['auditee_name'],
            'client_name'      => $row['client_name'],
            'audit_date'       => $row['audit_date'],
            'template_date'    => $row['template_date'],
            'region'           => $row['region'],
            'location'         => $row['location'],
            'score'            => $row['score'],
            'perform_audit_by' => $row['perform_audit_by'],
            'question_name'    => $row['question_name'],
            'audit_question'   => $row['audit_question'],
            'inplant'    => $row['inplant'],
            'remark'           => $row['remark'],
            'attachment'       => $attachment_html,
            'nc_closed_date'        => $row['nc_closed_date'],
            'nc_reviewed_date'        => $row['nc_reviewed_date'],
            'nc_closed_by'        => $row['nc_closed_by'],
            'nc_reviewed_by'        => $row['nc_reviewed_by'],
            'nc_worked_by'      => $row['nc_worked_by_name'] ?? '-',
            'nc_closed_by_user' => $row['nc_closed_by_user_name'] ?? '-',
            'nc_remark'        => nl2br(htmlspecialchars($row['nc_remark'])),
            'nc_after_photo'        => $nc_after_photos,
            'nc_status'        => $statuss,
            'action'           => $action
        ];
    }

    $tdata['data'] = $tdata['table_data'];
    unset($tdata['table_data']);

    return $this->response->setJSON($tdata);
}


     public function get_form_data($id) {
    $response = [
        'status' => "0",
        'message' => "Details not found"
    ];

    if (isset($id)) {
        $db = db_connect();

        // changes on 7/10/25 by darsh: include audit_no to resolve before photo from uploads/audit_files/{audit_no}
        $query = $db->table('alert_hse_audit_details')
            ->select('alert_hse_audit_details.*, alert_hse_audit_master.audit_no, alert_hse_audit_master.audit_name, 
                      alert_hse_audit_master.auditor_name, alert_hse_audit_master.auditee_name, 
                      alert_hse_audit_master.client_name, alert_hse_audit_master.audit_date, 
                      alert_hse_audit_master.template_date, alert_hse_audit_master.region, 
                      alert_hse_audit_master.location, alert_hse_audit_master.score, 
                      alert_hse_audit_master.perform_audit_by')
            ->join('alert_hse_audit_master', 'alert_hse_audit_master.hse_audit_id = alert_hse_audit_details.hse_audit_id', 'left')
            ->where('alert_hse_audit_details.id', $id)
            ->get();
        if ($query->getNumRows() > 0) {
            $response['data'] = $query->getRowArray();
            // changes on 7/10/25 by darsh: compute before_photo_url for edit form
            $beforePath = $response['data']['attachment'] ?? '';
            if (!empty($beforePath)) {
                if (preg_match('/(uploads\/.+)$/', $beforePath, $m)) { $beforePath = $m[1]; }
                elseif (preg_match('/(writable\/.+)$/', $beforePath, $m)) { $beforePath = $m[1]; }
            }
            if (empty($beforePath) || strpos($beforePath, 'uploads/') !== 0 || !is_file(FCPATH.$beforePath)) {
                $auditNo = $response['data']['audit_no'] ?? '';
                if (!empty($auditNo)) {
                    $folder = 'uploads/audit_files/'.$auditNo.'/';
                    $base = basename((string)($response['data']['attachment'] ?? ''));
                    if (!empty($base) && $base !== '.' && $base !== '..') {
                        $candidate = $folder.$base;
                        if (is_file(FCPATH.$candidate)) { $beforePath = $candidate; }
                    }
                    if (empty($beforePath) || !is_file(FCPATH.$beforePath)) {
                        $matches = glob(FCPATH.$folder.'*');
                        if ($matches) {
                            usort($matches, function($a,$b){ return filemtime($b) <=> filemtime($a); });
                            $picked = null;
                            foreach ($matches as $mfile) {
                                $ext = strtolower(pathinfo($mfile, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) { $picked = $mfile; break; }
                            }
                            if (!$picked) { $picked = $matches[0]; }
                            $rel = str_replace(FCPATH, '', $picked);
                            $rel = str_replace('\\', '/', $rel);
                            $beforePath = $rel;
                        }
                    }
                }
            }
            $response['data']['before_photo_url'] = !empty($beforePath) ? base_url($beforePath) : '';
            $response['status'] = "1";
            $response['message'] = "Details found";
        } else {
            log_message('error', 'No results found for ID: ' . $id);
        }
    }

    echo json_encode($response);
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
                    // NEW CHANGES: Keep records visible in this tracker; default inplant to 'NO'
                    if (!isset($postData['inplant']) || $postData['inplant'] === '') {
                        $postData['inplant'] = "NO";
                    }
               
                    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'webp');

                
                // NEW CHANGES: Before photo always comes from audit - no file upload allowed
                if(isset($postData['before_photo_url']))
                    $postData['attachment'] = $postData['before_photo_url'];
                if(isset($postData['before_photo_url']))
                    unset($postData['before_photo_url']);
                
                // NEW CHANGES: Handle NC After Photo upload
                    if(isset($_FILES['nc_after_photo']) &&  isset( $_FILES['nc_after_photo']['error'])){
                        if( $_FILES['nc_after_photo']['error'] == UPLOAD_ERR_OK){
                            $ext = pathinfo($_FILES["nc_after_photo"]["name"], PATHINFO_EXTENSION);
                            if (in_array($ext, $allowed)) {
                              $folder="uploads/nc_after_photo/";
                                $url = "";
                                if(isset($postData['after_photo_url']))
                                $url = $this->uploadImage($folder,"nc_after_photo",$postData['after_photo_url']);
                                else
                                $url = $this->uploadImage($folder,"nc_after_photo");
                                
                                $postData['nc_after_photo'] = $url;
                                // NEW CHANGES: Set status to 2 (Closed) when after photo is uploaded
                                $postData['nc_status'] = '2';
                                $postData['nc_closed_date'] = date('Y-m-d H:i:s');
                            }
                        }
                    }else{
                        if(isset($postData['after_photo_url']))
                                $postData['nc_after_photo'] = $postData['after_photo_url'];
                        
                    }
                    if(isset($postData['after_photo_url']))
                            unset($postData['after_photo_url']);
               
                if(isset($id)){
                        $responce['message'] = "Data updation faild";
                if(isset($action)){
                           switch($action){
                                case "open":
                                    $postData['nc_status']= "0";
                                    break;
                                case "working":
                                    $postData['nc_status']= "1";
                                    break;
                                case "closed":
                                    $postData['nc_status']= "2";
                                    break;
                                case "reviewed":
                                    $postData['nc_status']= "3";
                                    break;
                                case "draft":
                                    $postData['nc_status']= "4";
                                    break;
                               
                            }
                        }
                        // Save as Draft support
                        if(isset($postData['action']) && $postData['action']==='save_as_draft'){
                            $postData['nc_status'] = '4';
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

    // NEW CHANGES: Add status update method for AJAX calls
    public function update_nc_status() {
        helper('designation_acl'); // ACL: Load helper - 12/11/25
        $request = service('request');
        $id = $request->getVar('id');
        $status = $request->getVar('status');
        
        $response = [
            'status' => "0",
            'message' => "Update failed"
        ];
        
        if ($id && $status) {
            $db = db_connect();
            
            // ACL: Cluster Managers cannot approve/reject or modify closed - 12/11/25
            if (isClusterManager() || isWHManager()) {
                $currentRecord = $db->table('alert_hse_audit_details')
                    ->where('id', $id)
                    ->get()
                    ->getRowArray();
                
                if ($currentRecord && isset($currentRecord['nc_status'])) {
                    $currentStatus = $currentRecord['nc_status'];
                    
                    // Block if trying to modify closed NC
                    if ($currentStatus == 3) {
                        return $this->response->setJSON([
                            'status' => 0,
                            'message' => 'Cluster Managers cannot modify closed NCs'
                        ]);
                    }
                    
                    // Block if trying to approve (2 → 3) or reject (2 → 1)
                    if ($currentStatus == 2 && ($status === 'closed' || $status === 'working')) {
                        return $this->response->setJSON([
                            'status' => 0,
                            'message' => 'Cluster Managers cannot approve or reject NCs. Only Auditors can approve/reject.'
                        ]);
                    }
                }
            }
            
            $updateData = [];
            
            if ($status === 'working') {
                $updateData['nc_status'] = '1';
                $updateData['nc_reviewed_date'] = date('Y-m-d H:i:s');
            } elseif ($status === 'closed' || $status === 'reviewed') {
                // changes on 10/11/12 by darsh: reviewed => closed
                $updateData['nc_status'] = '3';
                $updateData['nc_closed_date'] = date('Y-m-d H:i:s');
            }
            
            // changes on 13/11/25 by darsh: Track who worked on and closed NC using session (store user_id)
            $loggedInUserId = $_SESSION['user_id'] ?? null;
            if ($status === 'working' && $loggedInUserId) {
                $updateData['nc_worked_by'] = $loggedInUserId;
            } elseif (($status === 'closed' || $status === 'reviewed') && $loggedInUserId) {
                $updateData['nc_closed_by_user'] = $loggedInUserId;
            }
            
            if (!empty($updateData)) {
                $result = $db->table('alert_hse_audit_details')
                    ->where('id', $id)
                    ->update($updateData);
                
                if ($result) {
                    $response['status'] = "1";
                    $response['message'] = "Status updated successfully";
                }
            }
        }
        
        return $this->response->setJSON($response);
    }
}
