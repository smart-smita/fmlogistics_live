<?php

namespace App\Controllers\Masters;

require APPPATH . '/ThirdParty/dompdf/autoload.inc.php';

use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;

class Audit_final_structure extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct()
    {
        // ACL + form helper
        helper(["form", "simple_acl"]);

        $db = null;
        $db['table']         = '';
        $db['allowedFields'] = [
            'audit_no',
            'audi_date',
            'completion_date',
            'audit_score',
            'audit_revision_no',
            'final_remark',
            'audit_template_id',
            'audit_by_user_id',
            'action_by_user_id',
            'status'
        ];
        $db['primaryKey'] = "structured_audit_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }

    public function index()
    {
        $data = [];

        $tdata['title'] = "Audit Final Structure";

        $tdata['display_contents'] = [
            "structured_audit_id" => "ID",
            "audit_no"            => "Audit No",
            "auditor_name"        => "Auditor Name",
            "auditee_name"        => "Auditee Name",
            "region"              => "Region",
            "cluster_name"        => "Cluster Name",
            "client_manager_name" => "Client Manager Name",
            "client_name"         => "Client Name",
            "audit_date"          => "Audit Date",
            "audit_score"         => "Audit Score",
            // "audit_revision_no"   => "Audit Revision No",
            // "final_remark"        => "Final Remark",
            // "audit_template_id"   => "Template Id",
            // "audit_by_user_id"    => "Audit User Id",
            // "action_by_user_id"   => "Action User Id",
            "action"              => "Action"
        ];

        $data['ajax_url']           = base_url("Masters/Audit_final_structure/save_details");
        
        // Handle filter query parameters
        $req = service('request');
        $inRegion = $req->getGet('region');
        $inCluster = $req->getGet('cluster');
        $inLocation = $req->getGet('location');
        $month = trim((string) $req->getGet('month') ?? '');

        $qsArr = [
            'region' => $inRegion,
            'cluster' => $inCluster,
            'location' => $inLocation,
            'month' => $month,
        ];
        $qs = http_build_query($qsArr);

        $selRegion = !empty($inRegion) ? (is_array($inRegion) ? $inRegion : [$inRegion]) : [];
        $selCluster = !empty($inCluster) ? (is_array($inCluster) ? $inCluster : [$inCluster]) : [];
        $selLocation = !empty($inLocation) ? (is_array($inLocation) ? $inLocation : [$inLocation]) : [];

        $tdata['ajax_url_for_data'] = base_url("Masters/Audit_final_structure/table_ajax") . ($qs ? ('?' . $qs) : '');

        $db = db_connect();

        // Fetch Regions
        $regionQuery = $db->table('alert_region')->select('region_name')->where('status', 1);
        helper('designation_acl');
        if (isAccountManager() && !empty($selRegion)) {
            $regionQuery->whereIn('region_name', $selRegion);
        }
        $data['regions'] = $regionQuery->orderBy('region_name', 'ASC')->get()->getResultArray();

        // Fetch Clusters
        $clusterQuery = $db->table('alert_client')->select('DISTINCT(cluster) AS cluster_name', false)->where('status', 1);
        if (!empty($selRegion)) {
            $clusterQuery->whereIn('region', $selRegion);
        }
        if (isClusterManager() || isWHManager()) {
            $userClusterACLs = getClusterManagerAssignedCluster();
            if (!empty($userClusterACLs)) {
                $clusterQuery->whereIn('cluster', $userClusterACLs);
            }
        }
        $data['clusters'] = $clusterQuery->orderBy('cluster', 'ASC')->get()->getResultArray();

        // Fetch Locations
        $locationQuery = $db->table('alert_client')->select('DISTINCT(client_name) AS location_name', false)->where('status', 1);
        if (!empty($selRegion)) {
            $locationQuery->whereIn('region', $selRegion);
        }
        if (!empty($selCluster)) {
            $locationQuery->whereIn('cluster', $selCluster);
        }
        if (isClusterManager() || isWHManager()) {
            if (!empty($userClusterACLs)) {
                $locationQuery->whereIn('cluster', $userClusterACLs);
            }
        }
        $data['locations'] = $locationQuery->orderBy('client_name', 'ASC')->get()->getResultArray();

        $data['selRegion'] = $selRegion;
        $data['selCluster'] = $selCluster;
        $data['selLocation'] = $selLocation;
        $data['selMonth'] = $month;

        // Top counters: No NC / Found NC / All
        $totalCountQuery = $db->query("SELECT count(structured_audit_id) as total FROM alert_final_structured_audit WHERE reaudit = 0");
        $totalCount = $totalCountQuery->getRow()->total ?? 0;

        $foundNcQuery = $db->query("SELECT count(DISTINCT audit.structured_audit_id) as found_count 
            FROM alert_final_structured_audit audit
            JOIN alert_final_structured_audit_details details ON audit.structured_audit_id = details.structured_audit_id
            WHERE details.audit_finding = 'NO' AND audit.reaudit = 0");
        $foundNcCount = $foundNcQuery->getRow()->found_count ?? 0;

        $noNcCount = $totalCount - $foundNcCount;
        if ($noNcCount < 0) $noNcCount = 0;

        $datatop = '
        <div class="row g-5 g-xl-8">
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure/template_type_filter/1') . '" class="card bg-primary hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">No NC(' . $noNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure/template_type_filter/0') . '" class="card bg-dark hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">Found NC(' . $foundNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure') . '" class="card bg-warning hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-white">All(' . $totalCount . ')</div>
                    </div>
                </a>
            </div>
        </div>';

        $data['table']  = view('Master/oe_audit_filter', $data);
        $data['table'] .= $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);

        return view("Layout/table-view-2", $data);
    }

    public function normal_audit_structure($category = null)
    {
        $data = [];

        $tdata['title'] = "Normal Audit Log";

        $tdata['display_contents'] = [
            "normal_audit_id"    => "ID",
            "audit_no"           => "Audit No",
            "auditor_name"       => "Auditor Name",
            "auditee_name"       => "Auditee Name",
            "region"             => "Region",
            "client_name"        => "Client Name",
            "cluster_name"       => "Cluster Name",
            "client_manager_name" => "Client Manager Name",
            "audit_date"         => "Audit Date",
            "audit_score"        => "Audit Score",
            "audit_revision_no"  => "Audit Revision No",
            "final_remark"       => "Final Remark",
            "audit_template_id"  => "Template Id",
            // "audit_by_user_id"   => "Audit User Id",
            // "action_by_user_id"  => "Action User Id",
            "action"             => "Action"
        ];

        $data['ajax_url']           = base_url("Masters/Audit_final_structure/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Audit_final_structure/table_ajax_normal/" . $category);

        $db = db_connect();
        helper('designation_acl');

        $aclWhere = getOEAuditACLWhere('audit', 'Normal'); 
        $aclWhereAuditOnly = getOEAuditACLWhere('alert_normal_audit', 'Normal'); 

        $totalCountQuery = $db->query("SELECT count(normal_audit_id) as total FROM alert_normal_audit WHERE reaudit = 0 {$aclWhereAuditOnly}");
        $totalCount = $totalCountQuery->getRow()->total ?? 0;

        $foundNcQuery = $db->query("SELECT count(DISTINCT audit.normal_audit_id) as found_count 
            FROM alert_normal_audit audit
            JOIN alert_normal_audit_details details ON audit.normal_audit_id = details.normal_audit_id
            WHERE details.audit_finding = 'NO' AND audit.reaudit = 0 {$aclWhere}");
        $foundNcCount = $foundNcQuery->getRow()->found_count ?? 0;

        $noNcCount = $totalCount - $foundNcCount;
        if ($noNcCount < 0) $noNcCount = 0;

        $ncOpenQuery = $db->query("SELECT count(details.audit_details_id) as total FROM alert_normal_audit_details details JOIN alert_normal_audit audit ON audit.normal_audit_id = details.normal_audit_id WHERE details.audit_finding = 'NO' AND details.status = 0 {$aclWhere}");
        $ncOpenCount = $ncOpenQuery->getRow()->total ?? 0;
        
        $ncWorkingQuery = $db->query("SELECT count(details.audit_details_id) as total FROM alert_normal_audit_details details JOIN alert_normal_audit audit ON audit.normal_audit_id = details.normal_audit_id WHERE details.audit_finding = 'NO' AND details.status IN (1, 4) {$aclWhere}");
        $ncWorkingCount = $ncWorkingQuery->getRow()->total ?? 0;
        
        $ncReviewQuery = $db->query("SELECT count(details.audit_details_id) as total FROM alert_normal_audit_details details JOIN alert_normal_audit audit ON audit.normal_audit_id = details.normal_audit_id WHERE details.audit_finding = 'NO' AND details.status IN (2, 6) {$aclWhere}");
        $ncReviewCount = $ncReviewQuery->getRow()->total ?? 0;

        $ncClosedQuery = $db->query("SELECT count(details.audit_details_id) as total FROM alert_normal_audit_details details JOIN alert_normal_audit audit ON audit.normal_audit_id = details.normal_audit_id WHERE details.audit_finding = 'NO' AND details.status IN (3, 5) {$aclWhere}");
        $ncClosedCount = $ncClosedQuery->getRow()->total ?? 0;

        $datatop = '
        <div class="row g-5 g-xl-8">
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure/normal_audit_structure/1') . '" class="card bg-primary hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">No NC(' . $noNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure/normal_audit_structure/0') . '" class="card bg-dark hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">Found NC(' . $foundNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure/normal_audit_structure') . '" class="card bg-warning hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-white">All(' . $totalCount . ')</div>
                    </div>
                </a>
            </div>
        </div>
        <!-- <div class="row g-5 g-xl-8 mb-xl-8 mt-0 pt-0">
            <div class="col-md-3">
                <a href="' . base_url('Masters/Normal_nc_tracker/index/open') . '" class="card bg-danger hoverable shadow-sm">
                    <div class="card-body py-4">
                        <div class="fw-semibold text-white fs-6">NC Open (' . $ncOpenCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="' . base_url('Masters/Normal_nc_tracker/index/working') . '" class="card bg-warning hoverable shadow-sm">
                    <div class="card-body py-4">
                        <div class="fw-semibold text-white fs-6">NC Working (' . $ncWorkingCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="' . base_url('Masters/Normal_nc_tracker/index/under_review_auditor') . '" class="card bg-info hoverable shadow-sm">
                    <div class="card-body py-4">
                        <div class="fw-semibold text-white fs-6">NC Review (' . $ncReviewCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-3">
                <a href="' . base_url('Masters/Normal_nc_tracker/index/close') . '" class="card bg-success hoverable shadow-sm">
                    <div class="card-body py-4">
                        <div class="fw-semibold text-white fs-6">NC Closed (' . $ncClosedCount . ')</div>
                    </div>
                </a>
            </div>
        </div> -->';

        $data['table']  = $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);

        return view("Layout/table-view-2", $data);
    }

    public function template_type_filter($category)
    {
        $data = [];

        $tdata['title'] = "Audit Final Structure";

        $tdata['display_contents'] = [
            "structured_audit_id" => "ID",
            "audit_no"            => "Audit No",
            "auditor_name"        => "Auditor Name",
            "auditee_name"        => "Auditee Name",
            "region"              => "Region",
            "cluster_name"        => "Cluster Name",
            "client_manager_name" => "Client Manager Name",
            // "zone"                => "Zone",
            "audit_date"          => "Audit Date",
            "audit_score"         => "Audit Score",
            "audit_revision_no"   => "Audit Revision No",
            "final_remark"        => "Final Remark",
            "audit_template_id"   => "Template Id",
            // "audit_by_user_id"    => "Audit User Id",
            // "action_by_user_id"   => "Action User Id",
            "action"              => "Action"
        ];

        $data['ajax_url']           = base_url("Masters/Audit_final_structure/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Audit_final_structure/table_ajax/" . $category);

        $db = db_connect();

        $totalCountQuery = $db->query("SELECT count(structured_audit_id) as total FROM alert_final_structured_audit WHERE reaudit = 0");
        $totalCount = $totalCountQuery->getRow()->total ?? 0;

        $foundNcQuery = $db->query("SELECT count(DISTINCT audit.structured_audit_id) as found_count 
            FROM alert_final_structured_audit audit
            JOIN alert_final_structured_audit_details details ON audit.structured_audit_id = details.structured_audit_id
            WHERE details.audit_finding = 'NO' AND audit.reaudit = 0");
        $foundNcCount = $foundNcQuery->getRow()->found_count ?? 0;

        $noNcCount = $totalCount - $foundNcCount;
        if ($noNcCount < 0) $noNcCount = 0;

        $datatop = '
        <div class="row g-5 g-xl-8">
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure/template_type_filter/1') . '" class="card bg-primary hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">No NC(' . $noNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure/template_type_filter/0') . '" class="card bg-dark hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">Found NC(' . $foundNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Audit_final_structure') . '" class="card bg-warning hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-white">All(' . $totalCount . ')</div>
                    </div>
                </a>
            </div>
        </div>';

        $data['table']  = $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);

        return view("Layout/table-view-2", $data);
    }

    public function table_ajax($category = null)
    {
        $db = db_connect();

        try {
            $builder = $db->table('alert_final_structured_audit');

            if (isset($category)) {
                if ($category == '0') {
                    $builder->where("structured_audit_id IN (SELECT structured_audit_id FROM alert_final_structured_audit_details WHERE audit_finding = 'NO')", null, false);
                } elseif ($category == '1') {
                    $builder->where("structured_audit_id NOT IN (SELECT structured_audit_id FROM alert_final_structured_audit_details WHERE audit_finding = 'NO')", null, false);
                }
            }

            $req = service('request');
            $inRegion = $req->getGet('region');
            $inCluster = $req->getGet('cluster');
            $inLocation = $req->getGet('location');
            $month = trim((string) $req->getGet('month') ?? '');

            $selRegion = !empty($inRegion) ? (is_array($inRegion) ? $inRegion : [$inRegion]) : [];
            $selCluster = !empty($inCluster) ? (is_array($inCluster) ? $inCluster : [$inCluster]) : [];
            $selLocation = !empty($inLocation) ? (is_array($inLocation) ? $inLocation : [$inLocation]) : [];

            if (!empty($selRegion)) {
                $builder->whereIn('region', $selRegion);
            }
            if (!empty($selCluster)) {
                $escapedSelClusters = array_map([$db, 'escape'], $selCluster);
                $selClusterInClause = implode(',', $escapedSelClusters);
                $builder->where("EXISTS (
                    SELECT 1 FROM alert_client 
                    WHERE alert_client.client_name = alert_final_structured_audit.client_name 
                    AND alert_client.cluster IN ($selClusterInClause)
                    AND alert_client.status = 1
                )", null, false);
            }
            if (!empty($selLocation)) {
                $builder->whereIn('client_name', $selLocation);
            }
            if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
                $builder->where("DATE_FORMAT(audit_date,'%Y-%m') = " . $db->escape($month), null, false);
            }

            // ACL for cluster managers
            helper('designation_acl');
            if (isClusterManager() || isWHManager()) {
                $userClusters = getClusterManagerAssignedCluster();
                if (!empty($userClusters)) {
                    $escapedClusters = array_map([$db, 'escape'], $userClusters);
                    $clusterInClause = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters));
                    $builder->where("EXISTS (
                        SELECT 1 FROM alert_client 
                        WHERE alert_client.client_name = alert_final_structured_audit.client_name 
                        AND LOWER(TRIM(alert_client.cluster)) IN ($clusterInClause)
                        AND alert_client.status = 1
                    )", null, false);
                }
            }

            // OE main list should show only original audits (reaudit = 0)
            $builder->where("reaudit", "0");
            $builder->orderBy('structured_audit_id', 'DESC');
            $tdata['table_data'] = $builder->get()->getResultArray();

            if (empty($tdata['table_data'])) {
                $totalCount = $db->table('alert_final_structured_audit')->countAllResults();
                if ($totalCount == 0) {
                    $sampleData = [
                        'audit_no'   => 'SAMPLE001',
                        'audit_name' => 'Sample OE Audit for Testing',
                        'region'     => '',
                        'audit_date' => date('Y-m-d'),
                        'status'     => 1
                    ];
                    try {
                        $db->table('alert_final_structured_audit')->insert($sampleData);
                        $builder = $db->table('alert_final_structured_audit');
                        if (isset($category)) {
                            $builder->where('status', $category);
                        }
                        if (isClusterManager() || isWHManager()) {
                            $userClusters = getClusterManagerAssignedCluster();
                            if (!empty($userClusters)) {
                                $escapedClusters = array_map([$db, 'escape'], $userClusters);
                                $clusterInClause = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters));
                                $builder->where("EXISTS (
                                    SELECT 1 FROM alert_client 
                                    WHERE alert_client.client_name = alert_final_structured_audit.client_name
                                    AND LOWER(TRIM(alert_client.cluster)) IN ($clusterInClause)
                                    AND alert_client.status = 1
                                )", null, false);
                            }
                        }
                        $builder->orderBy('structured_audit_id', 'DESC');
                        $tdata['table_data'] = $builder->get()->getResultArray();
                    } catch (\Exception $insertError) {
                        error_log("Could not insert sample data: " . $insertError->getMessage());
                    }
                }
            }
        } catch (\Exception $e) {
            error_log("OE Audit Log Error: " . $e->getMessage());
            $tdata['table_data'] = [];
        }

        foreach ($tdata['table_data'] as $key => $row) {

            $pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Audit_final_structure/auditViewDetailsPdf/" . $row['structured_audit_id']) . '"\' class="btn btn-icon btn-primary" title="View PDF">
                        <span class="indicator-label svg-icon svg-icon-3">
                            <i class="fa fa-file-pdf"></i>
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>';

            $reaudit = '';
            if (function_exists('canPerformAudit') && canPerformAudit('structure')) {
                $reaudit = '<button title="Reaudit" onclick=\'window.location.href="' . base_url("Masters/Audit_final_structure/reaudit/" . $row['audit_template_id'] . "/" . $row['structured_audit_id']) . '"\' class="btn btn-icon btn-success">
                                <span class="indicator-label svg-icon svg-icon-3">
                                    <i class="fas fa-sync-alt"></i>
                                </span>
                                <span class="indicator-progress">
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>';
            }

            $editBtn = '';
            if ((($_SESSION['admin_flag'] ?? 0) == 1 || strtolower($_SESSION['role'] ?? '') == 'super admin' || strtolower($_SESSION['role'] ?? '') == 'admin') && $row['reaudit'] == '0') {
                $editBtn = '<button title="Edit Audit" onclick=\'window.location.href="' . base_url("Masters/Audit_final_structure/edit_audit/" . $row['audit_template_id'] . "/" . $row['structured_audit_id']) . '"\' class="btn btn-icon btn-warning ms-1">
                                <span class="indicator-label svg-icon svg-icon-3">
                                    <i class="fas fa-edit"></i>
                                </span>
                            </button>';
            }

            $tdata['table_data'][$key]['action'] = $pdf . $reaudit . $editBtn;
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);

        return $this->response->setJSON($tdata);
    }

    public function table_ajax_normal($category = null)
    {
        helper('designation_acl');
        $db      = db_connect();
        $builder = $db->table("alert_normal_audit");

        if (isset($category)) {
            if ($category == '0') {
                $builder->where("normal_audit_id IN (SELECT normal_audit_id FROM alert_normal_audit_details WHERE audit_finding = 'NO')", null, false);
            } elseif ($category == '1') {
                $builder->where("normal_audit_id NOT IN (SELECT normal_audit_id FROM alert_normal_audit_details WHERE audit_finding = 'NO')", null, false);
            }
        }

        // Exclude older reaudit copies from normal list
        try {
            $builder->groupStart()->where('reaudit', 0)->orWhere('reaudit IS NULL', null, false)->groupEnd();
        } catch (\Throwable $e) {
            // ignore if column missing
        }

        if (isClusterManager() || isWHManager()) {
            $userClusters = getClusterManagerAssignedCluster();
            if (!empty($userClusters)) {
                $escapedClusters = array_map([$db, 'escape'], $userClusters);
                $clusterInClause = implode(',', array_map(function($c) { return "LOWER(TRIM($c))"; }, $escapedClusters));
                $builder->where("EXISTS (
                    SELECT 1 FROM alert_client 
                    WHERE alert_client.client_name = alert_normal_audit.location
                    AND LOWER(TRIM(alert_client.cluster)) IN ($clusterInClause)
                    AND alert_client.status = 1
                )", null, false);
            }
        } elseif (isAccountManager()) {
            $userClients = getAccountManagerAssignedClient();
            if (!empty($userClients)) {
                $builder->whereIn('location', $userClients);
            } else {
                $builder->where('1=0', null, false);
            }
        }

        $tdata['table_data'] = $builder->get()->getResultArray();

        foreach ($tdata['table_data'] as $key => $row) {

            $pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Audit_final_structure/normalAuditViewDetailsPdf/" . $row['normal_audit_id']) . '"\' class="btn btn-icon btn-primary" title="Pdf">
                        <span class="indicator-label svg-icon svg-icon-3">
                            <i class="fa fa-file-pdf"></i>
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                        </span>
                    </button>';

            $reaudit = '';
            // ACL: Check permission to perform normal audits
            if (canPerformAudit('Normal')) {
                $reaudit = '<button title="Reaudit" onclick=\'window.location.href="' . base_url("Masters/Audit_final_structure/normal_reaudit/" . $row['audit_template_id'] . "/" . $row['normal_audit_id']) . '"\' class="btn btn-icon btn-primary">
                                <span class="indicator-label svg-icon svg-icon-3">
                                    <i class="fas fa-sync-alt"></i>
                                </span>
                                <span class="indicator-progress">
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                            </button>';
            }

            $tdata['table_data'][$key]['action'] = $reaudit . $pdf;
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);

        return $this->response->setJSON($tdata);
    }

    // ------------------- NORMAL REAUDIT -------------------

    public function normal_reaudit($auditTemplateId, $normalAuditId)
    {
        $db = db_connect();

        $data['template'] = $db->table("alert_audit_template")
                               ->where("audit_template_id", $auditTemplateId)
                               ->get()->getRowArray();

        $current = $db->table("alert_normal_audit")
                      ->where("normal_audit_id", $normalAuditId)
                      ->get()->getRowArray();

        if (!$current) {
            return redirect()->back()->with('error', 'Normal audit not found.');
        }

        if (empty($current['region']) && !empty($current['location'])) {
            $locRow = $db->table('alert_location_master')
                         ->select('region_name')
                         ->where('location_name', $current['location'])
                         ->get()->getRowArray();
            if ($locRow && !empty($locRow['region_name'])) {
                $current['region'] = $locRow['region_name'];
            }
        }

        $data['details'] = $current;

        $audit_details_raw = $db->table("alert_normal_audit_details")
                                ->where("normal_audit_id", $normalAuditId)
                                ->get()->getResultArray();

        $data['audit_details'] = [];
        foreach ($audit_details_raw as $detail) {
            $key = strtolower(trim($detail['audit_question']));
            $data['audit_details'][$key] = $detail;
        }

        $bySrLoc = [];
        foreach ($audit_details_raw as $idx => $d) {
            $sr    = (string)($d['calculated'] ?? ($idx + 1));
            $srKey = strtolower(trim($sr . '|' . ($d['location'] ?? '')));
            $bySrLoc[$srKey] = $d;
        }
        $data['audit_details_by_sr_loc'] = $bySrLoc;

        $data['excel_details'] = $db->table("alert_normal_audit_excel_import")
                                    ->where("audit_template_id", $auditTemplateId)
                                    ->get()->getResultArray();

        $data['clients']   = $db->table('alert_client')->where('status', 1)->orderBy('client_name', 'ASC')->get()->getResultArray();
        $data['locations'] = $db->table('alert_location_master')->where('status', 1)->orderBy('location_name', 'ASC')->get()->getResultArray();
        $data['auditors']  = $db->table('alert_users')->select('user_name')->where('status', 1)->orderBy('user_name', 'ASC')->get()->getResultArray();
        $data['region']    = $db->table('alert_region')->orderBy('region_name', 'ASC')->get()->getResultArray();

        $data['action']             = base_url("Masters/Audit_final_structure/normal_save_reaudit/" . $auditTemplateId . "/" . $normalAuditId);
        $data['audit_template_type'] = 'Normal';

        return view("Audit/perform_reaudit_view", $data);
    }

    // ------------------- PDF GENERATION -------------------

    public function auditViewDetailsPdf($auditId, $flag = null, $type = null)
    {
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', true);

        $dompdf = new Dompdf($options);

        $db = db_connect();

        // $data['details'] = $db->table("alert_final_structured_audit")
        //                       ->join("alert_audit_template", "alert_final_structured_audit.audit_template_id=alert_audit_template.audit_template_id", "left")
        //                       ->where("alert_final_structured_audit.structured_audit_id", $auditId)
        //                       ->select("alert_final_structured_audit.*,alert_audit_template.audit_name,alert_audit_template.auditor_name,alert_audit_template.date,alert_audit_template.score,
        //                         (select cluster_name from alert_location_master where alert_location_master.location_name=alert_final_structured_audit.location limit 1) as cluster
        //                       ")
        //                       ->get()->getResultArray();
        $data['details'] = $db->table("alert_final_structured_audit")
    ->select("structured_audit_id,audit_no,audit_name,reaudit,auditor_name,auditee_name,region,cluster_name,client_manager_name,audit_date,completion_date,next_date,report_date,client_name,audit_note,location,zone,audit_score,audit_revision_no,final_remark,audit_template_id,audit_by_user_id,action_by_user_id,status,default_date,update_date")
    ->where("structured_audit_id", $auditId)
    ->get()
    ->getResultArray();

        $data['audit_details'] = $db->table("alert_final_structured_audit_details")
                                    ->where("structured_audit_id", $auditId)
                                    ->get()->getResultArray();

        $data['report_title'] = "OE Audit Report";

        if ($type == 'normal') {
            $data['details'] = $db->table("alert_normal_audit")
                                  ->join("alert_audit_template", "alert_normal_audit.audit_template_id=alert_audit_template.audit_template_id", "left")
                                  ->where("alert_normal_audit.normal_audit_id", $auditId)
                                  ->select("alert_normal_audit.*, alert_audit_template.audit_name AS template_audit_name, alert_audit_template.auditor_name AS template_auditor, alert_audit_template.date AS template_date, alert_audit_template.score AS template_score")
                                  ->get()->getResultArray();

            $data['audit_details'] = $db->table("alert_normal_audit_details")
                                        ->where("normal_audit_id", $auditId)
                                        ->orderBy('calculated', 'ASC')
                                        ->get()->getResultArray();
            $data['report_title'] = "Normal Audit Report";
        }

        $html = view("Audit/perform_audit_view_pdf", $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        if (ob_get_length()) {
            ob_end_clean();
        }

        if (isset($flag) == 1) {
            return $html;
        } else {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="Audit-' . $auditId . '.pdf"');
            echo $dompdf->output();
        }
    }

    public function normalAuditViewDetailsPdf($auditId)
    {
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('DOMPDF_ENABLE_CSS_FLOAT', true);

        $dompdf = new Dompdf($options);

        $db = db_connect();

        try {
            $details = $db->table("alert_normal_audit")
                          ->join("alert_audit_template", "alert_normal_audit.audit_template_id=alert_audit_template.audit_template_id", "left")
                          ->where("alert_normal_audit.normal_audit_id", $auditId)
                          ->select("alert_normal_audit.*,alert_audit_template.audit_name,alert_audit_template.auditor_name,alert_audit_template.date,alert_audit_template.auditee_name,alert_audit_template.region,alert_audit_template.score")
                          ->get()
                          ->getResultArray();

            if (empty($details)) {
                throw new \Exception("Normal audit record not found for ID: " . $auditId);
            }

            $data['details'] = $details;

            $audit_details = $db->table("alert_normal_audit_details")
                                ->where("normal_audit_id", $auditId)
                                ->orderBy('calculated', 'ASC')
                                ->get()
                                ->getResultArray();

            $data['audit_details'] = $audit_details;
            $data['report_title'] = "Normal Audit Report";
        } catch (\Exception $e) {
            log_message('error', 'Normal Audit PDF Error: ' . $e->getMessage());
            header('Content-Type: text/html');
            echo '<h1>Error generating PDF</h1><p>' . htmlspecialchars($e->getMessage()) . '</p>';
            return;
        }

        $html = view("Audit/perform_audit_view_pdf", $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="AuditNormal-' . $auditId . '.pdf"');
        echo $dompdf->output();
    }

    // ------------------- OE REAUDIT (WITH FORCE-CLOSE LOGIC) -------------------

    public function reaudit($auditTemplateId, $structuredAuditId)
    {
        $db = db_connect();

        $currentAudit = $db->table("alert_final_structured_audit")
                           ->select("*")
                           ->where("structured_audit_id", $structuredAuditId)
                           ->get()
                           ->getRowArray();

        if (!$currentAudit) {
            return redirect()->back()->with('error', 'Audit record not found.');
        }

        $data['template'] = $db->table("alert_audit_template")
                               ->where("audit_template_id", $auditTemplateId)
                               ->get()
                               ->getRowArray();

        $data['details'] = $currentAudit;
        $data['title']   = "";

        // Find the second last audit date (the audit that points to this one, or the last one where reaudit != 0)
        $previousAudit = $db->table("alert_final_structured_audit")
                            ->select("audit_date")
                            ->where("audit_no", $currentAudit['audit_no'])
                            ->where("reaudit !=", 0)
                            ->orderBy("structured_audit_id", "DESC")
                            ->get()
                            ->getRowArray();
                            
        $data['min_reaudit_date'] = $previousAudit ? $previousAudit['audit_date'] : $currentAudit['audit_date'];

        $data['excel_details'] = $db->table("alert_audit_excel_import")
                                    ->where("audit_template_id", $auditTemplateId)
                                    ->get()
                                    ->getResultArray();

        $audit_details_raw = $db->table("alert_final_structured_audit_details")
                                ->where("structured_audit_id", $structuredAuditId)
                                ->orderBy('audit_details_id', 'ASC')
                                ->get()
                                ->getResultArray();

        $bySrLoc = [];
        foreach ($audit_details_raw as $idx => $d) {
            $sr    = !empty($d['calculated']) ? (string)$d['calculated'] : (string)($idx + 1);
            $srKey = strtolower(trim($sr . '|' . ($d['location'] ?? '')));
            $bySrLoc[$srKey] = $d;
        }
        $data['audit_details_by_sr_loc'] = $bySrLoc;

        $byParamLoc = [];
        foreach ($audit_details_raw as $d) {
            $paramKey = strtolower(trim(($d['audit_parameter'] ?? '') . '|' . ($d['location'] ?? '')));
            if (!empty($d['audit_parameter'])) {
                $byParamLoc[$paramKey] = $d;
            }
        }
        $data['audit_details_by_param_loc'] = $byParamLoc;

        $data['audit_details'] = [];
        foreach ($audit_details_raw as $detail) {
            $key = strtolower(trim($detail['audit_question'] ?? ''));
            if (!empty($key)) {
                $data['audit_details'][$key] = $detail;
            }
        }

        $byTarget = [];
        foreach ($audit_details_raw as $d) {
            $tkey = strtolower(trim(($d['risk_priority'] ?? '') . '|' . ($d['category'] ?? '') . '|' . ($d['location'] ?? '')));
            $byTarget[$tkey] = $d;
        }
        $data['audit_details_by_target'] = $byTarget;

        $data['audit_details_by_index'] = array_values($audit_details_raw);

        log_message('debug', 'OE Reaudit - Audit ID: ' . $structuredAuditId);
        log_message('debug', 'OE Reaudit - Template Questions: ' . count($data['excel_details']));
        log_message('debug', 'OE Reaudit - Saved Details: ' . count($audit_details_raw));
        log_message('debug', 'OE Reaudit - Mapped by Sr+Loc: ' . count($bySrLoc));
        log_message('debug', 'OE Reaudit - Mapped by Param+Loc: ' . count($byParamLoc));

        $data['clients']   = $db->table('alert_client')->where('status', 1)->orderBy('client_name', 'ASC')->get()->getResultArray();
        $data['locations'] = $db->table('alert_location_master')->where('status', 1)->orderBy('location_name', 'ASC')->get()->getResultArray();

        if (isset($_SESSION['role']) && $_SESSION['role'] == "Auditor") {
            $data['auditors'] = $db->table("alert_users")->where("user_id", $_SESSION['login_id'])->where("status", 1)->get()->getResultArray();
        } else {
            $data['auditors'] = $db->table("alert_users")->where("user_designation", "Auditor")->where("status", 1)->get()->getResultArray();
        }

        $data['region'] = $db->table("alert_region")->get()->getResultArray();

        $data['action'] = base_url("Masters/Audit_final_structure/save_reaudit/" . $auditTemplateId . "/" . $structuredAuditId);
        $data['audit_template_type'] = 'OE'; // Set audit type for OE reaudit

        return view("Audit/perform_reaudit_view", $data);
    }

// changes on 28/10/25 by darsh: Reaudit updates the SAME entry and removes any previous duplicates
// changes on 21/11/25: FIXED - Now saves location field to prevent NULL values
public function save_reaudit($audit_template_id = null, $structuredAuditId = null)
{
    $request  = service('request');
    $postData = $request->getVar();
    $db       = db_connect();

    // DEBUG: log posted keys for troubleshooting (remove this in production)
    log_message('debug', 'save_reaudit POST keys: ' . implode(', ', array_keys($postData)));

    // ---------------- HEADER SAVE (NEW REAUDIT ENTRY) ----------------
    $alert_final_structured_audit = $db->table("alert_final_structured_audit");

    $alert_final_structured_audit_data                       = [];
    $alert_final_structured_audit_data['audit_no']           = $postData['audit_no']      ?? '';
    $alert_final_structured_audit_data['audit_name']         = $postData['audit_name']    ?? '';
    $alert_final_structured_audit_data['auditor_name']       = $postData['auditor_name']  ?? '';
    $alert_final_structured_audit_data['auditee_name']       = $postData['auditee_name']  ?? '';
    $alert_final_structured_audit_data['cluster_name']       = $postData['cluster_name']  ?? '';
    $alert_final_structured_audit_data['client_manager_name']       = $postData['client_manager_name']  ?? '';
    // changes on 03/12/25: Use reaudit_date from form so score appears in selected month
    $alert_final_structured_audit_data['audit_date']         = $postData['reaudit_date']  ?? date('Y-m-d');
    $alert_final_structured_audit_data['next_date']          = $postData['next_date']     ?? null;
    $alert_final_structured_audit_data['report_date']        = $postData['report_date']   ?? ($postData['report_date'] ?? '');

    // --------- CRITICAL: ensure header 'location' is saved ----------
    $postedLocation = trim($postData['location']    ?? '');
    $postedClient   = trim($postData['client_name'] ?? '');
    if ($postedLocation !== '') {
        $alert_final_structured_audit_data['location'] = $postedLocation;
    } elseif ($postedClient !== '') {
        $alert_final_structured_audit_data['location'] = $postedClient;
    } else {
        $alert_final_structured_audit_data['location'] = '';
        log_message('error', "save_reaudit: missing header location and client_name for template_id={$audit_template_id}, structuredAuditId={$structuredAuditId}");
    }

    // persist client_name if provided (fallback to location)
    $alert_final_structured_audit_data['client_name'] = $postData['client_name'] ?? ($postData['location'] ?? '');

    // normalize region
    $normalizedRegion = isset($postData['region']) ? ucwords(strtolower($postData['region'])) : '';
    $alert_final_structured_audit_data['region'] = $normalizedRegion;

    $alert_final_structured_audit_data['audit_score']      = $postData['score'] ?? 0;
    $alert_final_structured_audit_data['audit_template_id'] = $audit_template_id;
    $alert_final_structured_audit_data['audit_by_user_id']  = $_SESSION['login_id'] ?? null;
    // changes on 05/12/25: Set reaudit to 0 (integer) so this new entry is included in Month Summary
    // which filters by (reaudit = 0 OR reaudit IS NULL). Using "" caused it to be excluded.
    $alert_final_structured_audit_data['reaudit']           = 0;
    $alert_final_structured_audit_data['audit_note']           = $postData['audit_note']  ?? '';;

    // Insert new reaudit header
    $alert_final_structured_audit->insert($alert_final_structured_audit_data);
    $inId = $db->insertID();

    // Link previous header → this new reaudit id
    if (!empty($structuredAuditId)) {
        $alert_final_structured_audit
            ->where('structured_audit_id', $structuredAuditId)
            ->update(["reaudit" => $inId]);
    }

    // ---------------- DETAILS SAVE (NEW REAUDIT ENTRY) ----------------
    $alert_final_structured_audit_details_table = $db->table("alert_final_structured_audit_details");

    $allowed = array('mp4', 'jpg', 'png', 'jpeg', 'gif', 'pdf', 'xls', 'xlsx');

    if (isset($postData['audit_question']) && is_array($postData['audit_question'])) {
        for ($i = 0; $i < count($postData['audit_question']); $i++) {

            // Determine selection (YES/NO/NA). If nothing selected, SKIP row.
            $audit_finding = isset($postData['check_' . $i]) ? $postData['check_' . $i] : "NA";
            if ($audit_finding === null || $audit_finding === '') {
                continue; // do not insert if no selection
            }

            $detail = [];
            $detail['structured_audit_id'] = $inId;
            $detail['audit_template_id']   = $audit_template_id;
            $detail['location']            = $postData['location_list'][$i]   ?? '';
            $detail['category']            = $postData['category'][$i]        ?? '';
            $detail['audit_question']      = $postData['audit_question'][$i]  ?? '';
            $detail['audit_parameter']     = $postData['audit_parameter'][$i] ?? '';
            $detail['risk_priority']       = $postData['risk_priority'][$i]   ?? '';
            $detail['weightage']           = $postData['weightage'][$i]       ?? 0;
            $detail['audit_remark']        = $postData['remark'][$i]          ?? '';
            $detail['calculated']          = isset($postData['sr_no'][$i]) ? (int)$postData['sr_no'][$i] : ($i + 1);

            if ($audit_finding === 'NA') {
                $detail['audit_finding'] = "NA";
            } else {
                $detail['audit_finding'] = ($audit_finding == '1' || $audit_finding == '5' ) ? 'YES' : 'NO';
            }

            // carry forward existing attachment if present
            $detail['audit_attachment'] = $postData['existing_attachment'][$i] ?? "";

            // File upload
            if (
                isset($_FILES['audit_attachment']['name']) &&
                isset($_FILES['audit_attachment']['error'][$i]) &&
                $_FILES['audit_attachment']['error'][$i] == UPLOAD_ERR_OK
            ) {
                $ext = pathinfo($_FILES['audit_attachment']['name'][$i], PATHINFO_EXTENSION);
                if (in_array(strtolower($ext), $allowed)) {
                    $folder = "uploads/Audit/" . $inId . "/";
                    $_FILES['final']['name']     = $_FILES['audit_attachment']['name'][$i];
                    $_FILES['final']['type']     = $_FILES['audit_attachment']['type'][$i];
                    $_FILES['final']['tmp_name'] = $_FILES['audit_attachment']['tmp_name'][$i];
                    $_FILES['final']['error']    = $_FILES['audit_attachment']['error'][$i];
                    $_FILES['final']['size']     = $_FILES['audit_attachment']['size'][$i];

                    $url = $this->uploadImage($folder, 'final');
                    if ($url) {
                        $detail['audit_attachment'] = $url;
                    } else {
                        log_message('error', "save_reaudit: uploadImage failed for audit_attachment index={$i} on structured_audit_id={$inId}");
                    }
                } else {
                    log_message('warning', "save_reaudit: refused file extension {$ext} for index={$i}");
                }
            }

            $alert_final_structured_audit_details_table->insert($detail);
        }
    }

    // ---------------------------------------------------------
    // AUTO FORCE-CLOSE NCs OF PREVIOUS AUDIT WHEN REAUDIT SWITCHES TO YES
    // (Status = 5 → Forced Closed)
    // ---------------------------------------------------------
    if ($structuredAuditId) {

        // 1. Collect YES answers from current re-audit with precise matching keys
        $currentYesAnswers = [];
        if (isset($postData['audit_question']) && is_array($postData['audit_question'])) {
            for ($i = 0; $i < count($postData['audit_question']); $i++) {
                $finding = isset($postData['check_' . $i]) ? $postData['check_' . $i] : "NA";
                if ($finding == '1') { // YES
                    // Create a comprehensive unique key to match exact records
                    $loc      = strtolower(trim($postData['location_list'][$i]   ?? ''));
                    $cat      = strtolower(trim($postData['category'][$i]        ?? ''));
                    $param    = strtolower(trim($postData['audit_parameter'][$i] ?? ''));
                    $risk     = strtolower(trim($postData['risk_priority'][$i]   ?? ''));
                    $ques     = strtolower(trim($postData['audit_question'][$i]  ?? ''));
                    
                    // Use multiple fields to create a unique key
                    // Primary key: location + category + audit_parameter + risk_priority
                    $key = $loc . '|' . $cat . '|' . $param . '|' . $risk;
                    
                    // Store both the key and the question for fallback matching
                    $currentYesAnswers[$key] = [
                        'location' => $loc,
                        'category' => $cat,
                        'audit_parameter' => $param,
                        'risk_priority' => $risk,
                        'audit_question' => $ques
                    ];
                }
            }
        }

        if (!empty($currentYesAnswers)) {

            // 2. Fetch NCs (NO answers) from the previous audit that are not already closed/forced
            $oldNCs = $db->table('alert_final_structured_audit_details')
                ->where('structured_audit_id', $structuredAuditId)
                ->where('audit_finding', 'NO')
                ->where('status !=', 3) // not closed
                ->where('status !=', 5) // not force closed
                ->get()->getResultArray();

            // 3. Match using comprehensive key to ensure we only close the exact NC
            foreach ($oldNCs as $nc) {
                $ncLoc   = strtolower(trim($nc['location']          ?? ''));
                $ncCat   = strtolower(trim($nc['category']          ?? ''));
                $ncParam = strtolower(trim($nc['audit_parameter']   ?? ''));
                $ncRisk  = strtolower(trim($nc['risk_priority']     ?? ''));
                $ncQues  = strtolower(trim($nc['audit_question']    ?? ''));
                
                // Create the same comprehensive key
                $ncKey = $ncLoc . '|' . $ncCat . '|' . $ncParam . '|' . $ncRisk;

                // Check if this exact NC was switched to YES in the reaudit
                if (isset($currentYesAnswers[$ncKey])) {
                    $db->table('alert_final_structured_audit_details')
                        ->where('audit_details_id', $nc['audit_details_id'])
                        ->update([
                            'status'       => 5, // FORCE CLOSED
                            'nc_closed_by' => $_SESSION['login_id'] ?? 0
                        ]);
                    
                    log_message('info', "Force closed NC audit_details_id={$nc['audit_details_id']} - Location: '{$ncLoc}', Category: '{$ncCat}', Parameter: '{$ncParam}', Priority: '{$ncRisk}'");
                }
            }
        }
    }

    return redirect()->to(base_url("/Masters/Audit_final_structure"));
}



    // ------------------- NORMAL SAVE REAUDIT -------------------

    public function normal_save_reaudit($auditTemplateId = null, $normalAuditId = null)
    {
        $request  = service('request');
        $postData = $request->getVar();
        $db       = db_connect();

        if (!$normalAuditId) {
            return redirect()->back()->with('error', 'Invalid normal audit id for reaudit.');
        }

        $master = $db->table('alert_normal_audit');
        $orig   = $master->where('normal_audit_id', $normalAuditId)->get()->getRowArray();

        if (!$orig) {
            return redirect()->back()->with('error', 'Normal audit record not found.');
        }

        $new = [
            'audit_template_id' => $auditTemplateId ?? ($postData['audit_template_id'] ?? $orig['audit_template_id']),
            'audit_no'          => $postData['audit_no'] ?? $orig['audit_no'],
            'audit_name'        => $postData['audit_name'] ?? $orig['audit_name'],
            'auditor_name'      => $postData['auditor_name'] ?? $orig['auditor_name'],
            'auditee_name'      => $postData['auditee_name'] ?? $orig['auditee_name'],
            'audit_date'        => $postData['reaudit_date'] ?? date('Y-m-d'),
            'next_date'         => $postData['next_date'] ?? ($orig['next_date'] ?? ''),
            'report_date'       => $postData['report_date'] ?? ($orig['report_date'] ?? ''),
            'client_name'       => $postData['client_name'] ?? ($orig['client_name'] ?? ''),
            'region'            => $postData['region'] ?? ($orig['region'] ?? ''),
            'location'          => $postData['location'] ?? ($orig['location'] ?? ''),
            'zone'              => $postData['region'] ?? ($orig['zone'] ?? ''),
            'audit_score'       => $postData['score'] ?? ($orig['audit_score'] ?? 0),
            'audit_revision_no' => (int)($orig['audit_revision_no'] ?? 0) + 1,
            'audit_by_user_id'  => $_SESSION['login_id'] ?? ($orig['audit_by_user_id'] ?? null),
            'cluster_name'      => $postData['cluster_name'] ?? ($orig['cluster_name'] ?? ''),
            'client_manager_name' => $postData['client_manager_name'] ?? ($orig['client_manager_name'] ?? ''),
        ];

        $master->insert($new);
        $newId = $db->insertID();

        try {
            $db->query("UPDATE alert_normal_audit SET reaudit = ? WHERE normal_audit_id = ?", [$newId, (int)$normalAuditId]);
        } catch (\Throwable $e) {
            // ignore if column missing
        }

        // ---------------------------------------------------------
        // AUTO CLOSE NCs OF PREVIOUS AUDIT WHEN REAUDIT SWITCHES TO YES
        // (Status = 3 → Closed)
        // ---------------------------------------------------------
        if ($normalAuditId) {
            // 1. Collect YES answers from current re-audit with precise matching keys
            $currentYesAnswers = [];
            if (isset($postData['audit_question']) && is_array($postData['audit_question'])) {
                for ($i = 0; $i < count($postData['audit_question']); $i++) {
                    $finding = isset($postData['check_' . $i]) ? $postData['check_' . $i] : "NA";
                    if ($finding == '1') { // YES
                        $loc      = strtolower(trim($postData['location_list'][$i]   ?? ''));
                        $cat      = strtolower(trim($postData['category'][$i]        ?? ''));
                        $risk     = strtolower(trim($postData['risk_priority'][$i]   ?? ''));
                        $ques     = strtolower(trim($postData['audit_question'][$i]  ?? ''));
                        
                        $key = $loc . '|' . $cat . '|' . $risk . '|' . $ques;
                        
                        $currentYesAnswers[$key] = [
                            'location' => $loc,
                            'category' => $cat,
                            'risk_priority' => $risk,
                            'audit_question' => $ques
                        ];
                    }
                }
            }

            if (!empty($currentYesAnswers)) {
                // 2. Fetch NCs (NO answers) from the previous audit that are not already closed/forced
                $oldNCs = $db->table('alert_normal_audit_details')
                    ->where('normal_audit_id', $normalAuditId)
                    ->where('audit_finding', 'NO')
                    ->where('status !=', 3) // not closed
                    ->where('status !=', 5) // not force closed
                    ->get()->getResultArray();

                // 3. Match using comprehensive key to ensure we only close the exact NC
                foreach ($oldNCs as $nc) {
                    $ncLoc   = strtolower(trim($nc['location']          ?? ''));
                    $ncCat   = strtolower(trim($nc['category']          ?? ''));
                    $ncRisk  = strtolower(trim($nc['risk_priority']     ?? ''));
                    $ncQues  = strtolower(trim($nc['audit_question']    ?? ''));
                    
                    $ncKey = $ncLoc . '|' . $ncCat . '|' . $ncRisk . '|' . $ncQues;

                    // Check if this exact NC was switched to YES in the reaudit
                    if (isset($currentYesAnswers[$ncKey])) {
                        $db->table('alert_normal_audit_details')
                            ->where('audit_details_id', $nc['audit_details_id'])
                            ->update([
                                'status'       => 3, // CLOSED
                                'nc_closed_by' => $_SESSION['login_id'] ?? 0
                            ]);
                        
                        log_message('info', "Closed Normal NC audit_details_id={$nc['audit_details_id']} - Location: '{$ncLoc}', Category: '{$ncCat}', Priority: '{$ncRisk}', Question: '{$ncQues}'");
                    }
                }
            }
        }

        $details = $db->table('alert_normal_audit_details');
        $allowed = ['mp4', 'jpg', 'png', 'jpeg', 'gif', 'pdf', 'xls', 'xlsx'];

        for ($i = 0; $i < count($postData['audit_question']); $i++) {
            $row                        = [];
            $row['normal_audit_id']     = $newId;
            $row['audit_template_id']   = $auditTemplateId ?? ($postData['audit_template_id'] ?? $orig['audit_template_id']);
            $row['location']            = $postData['location_list'][$i];
            $row['category']            = $postData['category'][$i];
            $row['audit_question']      = $postData['audit_question'][$i];
            $row['risk_priority']       = $postData['risk_priority'][$i];
            $row['weightage']           = $postData['weightage'][$i];
            $row['audit_remark']        = $postData['remark'][$i];
            $row['calculated']          = isset($postData['sr_no'][$i]) ? (int)$postData['sr_no'][$i] : ($i + 1);
            $audit_finding              = isset($postData['check_' . $i]) ? $postData['check_' . $i] : null;

            if ($audit_finding === null || $audit_finding === '') {
                $row['audit_finding'] = 'NA';
            } elseif ($audit_finding === '0') {
                $row['audit_finding'] = 'NO';
            } elseif ($audit_finding === '1') {
                $row['audit_finding'] = 'YES';
            } else {
                $row['audit_finding'] = 'NA';
            }

            $row['audit_attachment'] = $postData['existing_attachment'][$i] ?? '';

            if (
                isset($_FILES['audit_attachment']['name']) &&
                isset($_FILES['audit_attachment']['error'][$i]) &&
                $_FILES['audit_attachment']['error'][$i] == UPLOAD_ERR_OK
            ) {
                $ext = pathinfo($_FILES['audit_attachment']['name'][$i], PATHINFO_EXTENSION);
                if (in_array($ext, $allowed)) {
                    $folder                     = 'uploads/Audit/' . $newId . '/';
                    $_FILES['final']['name']     = $_FILES['audit_attachment']['name'][$i];
                    $_FILES['final']['type']     = $_FILES['audit_attachment']['type'][$i];
                    $_FILES['final']['tmp_name'] = $_FILES['audit_attachment']['tmp_name'][$i];
                    $_FILES['final']['error']    = $_FILES['audit_attachment']['error'][$i];
                    $_FILES['final']['size']     = $_FILES['audit_attachment']['size'][$i];
                    if (method_exists($this, 'uploadImage')) {
                        $url = $this->uploadImage($folder, 'final');
                        $row['audit_attachment'] = $url;
                    }
                }
            }

            $details->insert($row);
        }

        return redirect()->to(base_url('/Masters/Audit_final_structure/normal_audit_structure'));
    }

    // ------------------- MISC -------------------

    public function get_form_data($id)
    {
        $responce['status']  = "0";
        $responce['message'] = "Details not found";

        if (isset($id)) {
            $responce['data']    = $this->BaseModel->find($id);
            $responce['status']  = "1";
            $responce['message'] = "Details found";
        }

        echo json_encode($responce);
    }

    // ------------------- EDIT NORMAL AUDIT -------------------

    public function edit_audit($auditTemplateId, $structuredAuditId)
    {
        if (($_SESSION['admin_flag'] ?? 0) != 1 && strtolower($_SESSION['role'] ?? '') != 'super admin' && strtolower($_SESSION['role'] ?? '') != 'admin') {
            return redirect()->back()->with('error', 'Unauthorized access.');
        }

        $db = db_connect();

        $data['template'] = $db->table("alert_audit_template")
                               ->where("audit_template_id", $auditTemplateId)
                               ->get()->getRowArray();

        $currentAudit = $db->table("alert_final_structured_audit")
                           ->where("structured_audit_id", $structuredAuditId)
                           ->get()->getRowArray();

        if (!$currentAudit || $currentAudit['reaudit'] != 0) {
            return redirect()->back()->with('error', 'Audit not found or cannot be edited.');
        }

        $data['details'] = $currentAudit;
        $data['min_reaudit_date'] = $currentAudit['audit_date'];

        $data['excel_details'] = $db->table("alert_audit_excel_import")
                                    ->where("audit_template_id", $auditTemplateId)
                                    ->get()
                                    ->getResultArray();

        $audit_details_raw = $db->table("alert_final_structured_audit_details")
                                ->where("structured_audit_id", $structuredAuditId)
                                ->orderBy('audit_details_id', 'ASC')
                                ->get()
                                ->getResultArray();

        $bySrLoc = [];
        foreach ($audit_details_raw as $idx => $d) {
            $sr    = !empty($d['calculated']) ? (string)$d['calculated'] : (string)($idx + 1);
            $srKey = strtolower(trim($sr . '|' . ($d['location'] ?? '')));
            $bySrLoc[$srKey] = $d;
        }
        $data['audit_details_by_sr_loc'] = $bySrLoc;

        $byParamLoc = [];
        foreach ($audit_details_raw as $d) {
            $paramKey = strtolower(trim(($d['audit_parameter'] ?? '') . '|' . ($d['location'] ?? '')));
            if (!empty($d['audit_parameter'])) {
                $byParamLoc[$paramKey] = $d;
            }
        }
        $data['audit_details_by_param_loc'] = $byParamLoc;

        $data['audit_details'] = [];
        foreach ($audit_details_raw as $detail) {
            $key = strtolower(trim($detail['audit_question'] ?? ''));
            if (!empty($key)) {
                $data['audit_details'][$key] = $detail;
            }
        }

        $byTarget = [];
        foreach ($audit_details_raw as $d) {
            $tkey = strtolower(trim(($d['risk_priority'] ?? '') . '|' . ($d['category'] ?? '') . '|' . ($d['location'] ?? '')));
            $byTarget[$tkey] = $d;
        }
        $data['audit_details_by_target'] = $byTarget;
        $data['audit_details_by_index'] = array_values($audit_details_raw);

        $data['clients']   = $db->table('alert_client')->where('status', 1)->orderBy('client_name', 'ASC')->get()->getResultArray();
        $data['locations'] = $db->table('alert_location_master')->where('status', 1)->orderBy('location_name', 'ASC')->get()->getResultArray();

        if (isset($_SESSION['role']) && $_SESSION['role'] == "Auditor") {
            $data['auditors'] = $db->table("alert_users")->where("user_id", $_SESSION['login_id'])->where("status", 1)->get()->getResultArray();
        } else {
            $data['auditors'] = $db->table("alert_users")->where("user_designation", "Auditor")->where("status", 1)->get()->getResultArray();
        }

        $data['region'] = $db->table("alert_region")->get()->getResultArray();

        $data['action'] = base_url("Masters/Audit_final_structure/save_edit_audit/" . $auditTemplateId . "/" . $structuredAuditId);
        $data['audit_template_type'] = 'OE';
        $data['is_edit_mode'] = true;

        return view("Audit/perform_reaudit_view", $data);
    }

    public function save_edit_audit($audit_template_id = null, $structuredAuditId = null)
    {
        if (($_SESSION['admin_flag'] ?? 0) != 1 && strtolower($_SESSION['role'] ?? '') != 'super admin' && strtolower($_SESSION['role'] ?? '') != 'admin') {
            return $this->response->setJSON(['status' => '0', 'message' => 'Permission denied.']);
        }

        $request  = service('request');
        $postData = $request->getVar();
        $db       = db_connect();

        $alert_final_structured_audit = $db->table("alert_final_structured_audit");
        $alert_final_structured_audit_data                       = [];
        $alert_final_structured_audit_data['audit_name']         = $postData['audit_name']    ?? '';
        $alert_final_structured_audit_data['auditor_name']       = $postData['auditor_name']  ?? '';
        $alert_final_structured_audit_data['auditee_name']       = $postData['auditee_name']  ?? '';
        $alert_final_structured_audit_data['cluster_name']       = $postData['cluster_name']  ?? '';
        $alert_final_structured_audit_data['client_manager_name']       = $postData['client_manager_name']  ?? '';
        $alert_final_structured_audit_data['audit_date']         = $postData['reaudit_date']  ?? date('Y-m-d');
        $alert_final_structured_audit_data['next_date']          = $postData['next_date']     ?? null;
        $alert_final_structured_audit_data['report_date']        = $postData['report_date']   ?? ($postData['report_date'] ?? '');
        
        $postedLocation = trim($postData['location']    ?? '');
        $postedClient   = trim($postData['client_name'] ?? '');
        if ($postedLocation !== '') {
            $alert_final_structured_audit_data['location'] = $postedLocation;
        } elseif ($postedClient !== '') {
            $alert_final_structured_audit_data['location'] = $postedClient;
        }
        
        $alert_final_structured_audit_data['client_name'] = $postData['client_name'] ?? ($postData['location'] ?? '');
        $normalizedRegion = isset($postData['region']) ? ucwords(strtolower($postData['region'])) : '';
        $alert_final_structured_audit_data['region'] = $normalizedRegion;
        $alert_final_structured_audit_data['audit_score']      = $postData['score'] ?? 0;
        $alert_final_structured_audit_data['audit_note']           = $postData['audit_note']  ?? '';

        $alert_final_structured_audit->where('structured_audit_id', $structuredAuditId)->update($alert_final_structured_audit_data);

        $alert_final_structured_audit_details_table = $db->table("alert_final_structured_audit_details");
        $allowed = array('mp4', 'jpg', 'png', 'jpeg', 'gif', 'pdf', 'xls', 'xlsx');

        if (isset($postData['audit_question']) && is_array($postData['audit_question'])) {
            for ($i = 0; $i < count($postData['audit_question']); $i++) {
                $audit_finding = isset($postData['check_' . $i]) ? $postData['check_' . $i] : "NA";
                if ($audit_finding === null || $audit_finding === '') {
                    continue; 
                }

                $detail = [];
                $detail['audit_remark'] = $postData['remark'][$i] ?? '';
                
                if ($audit_finding === 'NA') {
                    $detail['audit_finding'] = "NA";
                } else {
                    $detail['audit_finding'] = ($audit_finding == '1' || $audit_finding == '5' ) ? 'YES' : 'NO';
                }

                $detail['audit_attachment'] = $postData['existing_attachment'][$i] ?? "";

                if (
                    isset($_FILES['audit_attachment']['name']) &&
                    isset($_FILES['audit_attachment']['error'][$i]) &&
                    $_FILES['audit_attachment']['error'][$i] == UPLOAD_ERR_OK
                ) {
                    $ext = pathinfo($_FILES['audit_attachment']['name'][$i], PATHINFO_EXTENSION);
                    if (in_array(strtolower($ext), $allowed)) {
                        $folder = "uploads/Audit/" . $structuredAuditId . "/";
                        $_FILES['final']['name']     = $_FILES['audit_attachment']['name'][$i];
                        $_FILES['final']['type']     = $_FILES['audit_attachment']['type'][$i];
                        $_FILES['final']['tmp_name'] = $_FILES['audit_attachment']['tmp_name'][$i];
                        $_FILES['final']['error']    = $_FILES['audit_attachment']['error'][$i];
                        $_FILES['final']['size']     = $_FILES['audit_attachment']['size'][$i];

                        if (method_exists($this, 'uploadImage')) {
                            $url = $this->uploadImage($folder, 'final');
                            if ($url) {
                                $detail['audit_attachment'] = $url;
                            }
                        }
                    } 
                }

                $existingId = $postData['existing_detail_id'][$i] ?? null;
                
                if (!empty($existingId)) {
                    $alert_final_structured_audit_details_table
                        ->where('audit_details_id', $existingId)
                        ->update($detail);
                } else {
                    // Fallback just in case
                    $calculated = isset($postData['sr_no'][$i]) ? (int)$postData['sr_no'][$i] : ($i + 1);
                    $alert_final_structured_audit_details_table
                        ->where('structured_audit_id', $structuredAuditId)
                        ->where('calculated', $calculated)
                        ->update($detail);
                }
            }
        }

        return redirect()->to(base_url('/Masters/Audit_final_structure'));
    }
}
