<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;
use APP\Helpers\designation_acl_helper;


class Normal_nc_tracker extends BaseController
{
    public function __construct()
    {
        // changes on 16/10/25 by darsh: Using simple ACL helper without database changes
        helper(["form", "simple_acl"]);
    }

    public function index($status = null)
    {
        $data = [];

        $tdata['title'] = "Normal NC Tracker";
        $tdata['button_id'] = "user_modal";
        $tdata['hide_add_button'] = true; // explicitly hide the add button

        // carry multi-filter values in ajax URLs
        $req = service('request');

        // Capture inputs (arrays or strings)
        $inAuditType = $req->getGet('audit_type');
        $inRegion = $req->getGet('region');
        $inCluster = $req->getGet('cluster');
        $inLocation = $req->getGet('location');
        $month = trim((string) $req->getGet('month') ?? ''); // format YYYY-MM

        $qsArr = [
            'audit_type' => $inAuditType,
            'region' => $inRegion,
            'cluster' => $inCluster,
            'location' => $inLocation,
            'month' => $month,
        ];
        $qs = http_build_query($qsArr);

        // Normalize selections to arrays for logic
        $selAuditType = !empty($inAuditType) ? (is_array($inAuditType) ? $inAuditType : [$inAuditType]) : [];
        $selRegion = !empty($inRegion) ? (is_array($inRegion) ? $inRegion : [$inRegion]) : [];
        $selCluster = !empty($inCluster) ? (is_array($inCluster) ? $inCluster : [$inCluster]) : [];
        $selLocation = !empty($inLocation) ? (is_array($inLocation) ? $inLocation : [$inLocation]) : [];

        // if(!isset($status) && (!empty($selRegion) || !empty($selCluster) || !empty($selLocation) || $month!='' ))
        //     $status ="open";

        // table header config
        $tdata['display_contents'] = [
            'bulk_select' => '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" id="selectAllNc" /></div>',
            'audit_details_id' => 'Audit Details Id',
            'normal_audit_id' => 'Audit Details Id',
            'action' => 'Action',
            'audit_no' => 'Audit no.',
            'audit_name' => 'Audit Type Name',
            'auditor_name' => 'Auditor Name',
            'auditee_name' => 'Auditee Name',
            'region' => 'Region',
            'cluster_name' => 'Cluster',
            'client_manager_name' => 'Account Manager',
            'audit_date' => 'Audit Date',
            'client_name' => 'Client Name',
            'audit_score' => 'Audit Score',
            'category' => 'Category',
            'risk_priority' => 'Target',
            'weightage' => 'Weightage',
            'audit_finding' => 'Audit Finding',
            'audit_remark' => 'Audit Remark',
            'before_photo' => 'Before NC Photo',
            'audit_attachment' => 'After NC Photo',
            'nc_worked_by' => 'NC Worked By',
            'nc_closed_by' => 'NC Closed By',
            'nc_rejected_by' => 'NC Rejected By',
            'approved_by' => 'Approved By',
            'update_date' => 'Update Date',
        ];

        $data['ajax_url'] = base_url("Masters/Normal_nc_tracker/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Normal_nc_tracker/table_ajax/" . $status) . ($qs ? ('?' . $qs) : '');

        // ----- Dependent Dropdown (Region → Cluster → Location) -----
        $db2 = db_connect();

        // ACL: Retrieve user cluster / client FIRST
        helper('designation_acl');
        $userClusterACL = null;
        $userClientACL = null;

        if (isClusterManager() || isWHManager()) {
            $userClusterACLs = getClusterManagerAssignedCluster();
            $cmRegions = getClusterManagerAssignedRegion();

            if (!empty($userClusterACLs)) {
                $selCluster = is_array($userClusterACLs) ? $userClusterACLs : [$userClusterACLs];
            }
            if (!empty($cmRegions)) {
                $selRegion = is_array($cmRegions) ? $cmRegions : [$cmRegions];
            } else if (!empty($userClusterACLs)) {

                $cmRegionsData = $db2->table('alert_client')
                    ->select('DISTINCT(region) AS region_name', false)
                    ->where('status', 1)
                    ->whereIn('cluster', $userClusterACLs)
                    ->orderBy('region', 'ASC')
                    ->get()
                    ->getResultArray();

                $selRegion = array_values(
                    array_filter(
                        array_map(function ($r) {
                            return $r['region_name'] ?? '';
                        }, $cmRegionsData)
                    )
                );
            }
        } elseif (isAccountManager()) {
            // Get full client details for Account Manager
            $amClientDetailsList = getAccountManagerClientDetails();

            if ($amClientDetailsList) {
                $amClients = [];
                $amRegions = [];
                $amClusters = [];

                foreach ($amClientDetailsList as $details) {
                    if (!empty($details['client_name'])) {
                        $amClients[] = $details['client_name'];
                    }
                    if (!empty($details['region'])) {
                        $amRegions[] = $details['region'];
                    }
                    if (!empty($details['cluster'])) {
                        $amClusters[] = $details['cluster'];
                    }
                }

                $selRegion = array_unique($amRegions);
                $selCluster = array_unique($amClusters);
                if (empty($selLocation)) {
                    $selLocation = array_unique($amClients);
                }
                $userClientACL = array_unique($amClients); // For AM, client_name is used for filtering
            } else {
                // IMPORTANT: If AM has no assigned clients, show empty dropdowns and data
                $data['regions'] = [];
                $data['clusters'] = [];
                $data['locations'] = [];
                $data['months'] = [];
                $data['selRegion'] = [];
                $data['selCluster'] = [];
                $data['selLocation'] = [];
                $data['selMonth'] = '';

                // Set top counters to 0
                $openCount = $workingCount = $submittedToCMCount = $underReviewAuditorCount = $closedCount = $forcedClosedCount = 0;

                $qsString = $qs ? ('?' . $qs) : '';
                $datatop = '<div class="row justify-content-center text-center mb-5" style="gap:30px;">'
                    . '<div class="col-md-2 p-0"><a href="' . base_url('Masters/Normal_nc_tracker/index' . $qsString) . '" class="card bg-dark hoverable shadow-sm"><div class="card-body py-4"><div class="fw-semibold text-white fs-6">All</div></div></a></div>'
                    . '<div class="col-md-2 p-0"><a href="' . base_url('Masters/Normal_nc_tracker/index/open' . $qsString) . '" class="card bg-danger hoverable shadow-sm"><div class="card-body py-4"><div class="fw-semibold text-white fs-6">Open (0)</div></div></a></div>'
                    . '<div class="col-md-2 p-0"><a href="' . base_url('Masters/Normal_nc_tracker/index/working' . $qsString) . '" class="card bg-warning hoverable shadow-sm"><div class="card-body py-4"><div class="fw-semibold text-white fs-6">Working (0)</div></div></a></div>'
                    . '<div class="col-md-4 p-0"><a href="' . base_url('Masters/Normal_nc_tracker/index/submitted_to_cm' . $qsString) . '" class="card bg-primary hoverable shadow-sm"><div class="card-body py-4"><div class="fw-semibold text-white fs-6">Under Review Cluster Manager (0)</div></div></a></div>'
                    . '<div class="col-md-3 p-0"><a href="' . base_url('Masters/Normal_nc_tracker/index/under_review_auditor' . $qsString) . '" class="card bg-info hoverable shadow-sm"><div class="card-body py-4"><div class="fw-semibold text-white fs-6">Under Review Auditor (0)</div></div></a></div>'
                    . '<div class="col-md-2 p-0"><a href="' . base_url('Masters/Normal_nc_tracker/index/close' . $qsString) . '" class="card bg-success hoverable shadow-sm"><div class="card-body py-4"><div class="fw-semibold text-white fs-6">Closed (0)</div></div></a></div>'
                    . '<div class="col-md-3 p-0"><a href="' . base_url('Masters/Normal_nc_tracker/index/forced' . $qsString) . '" class="card bg-dark hoverable shadow-sm"><div class="card-body py-4"><div class="fw-semibold text-white fs-6">Forced Close (0)</div></div></a></div>'
                    . '</div>';

                $data['table'] = $datatop;
                $data['table'] .= view("Layout/table-view", $tdata);
                return view("Master/add_normal_nc_tracker", $data);
            }
        }

        $regionQuery = $db2->table('alert_region')
            ->select('region_name')
            ->where('status', 1);

        if (isAccountManager() && !empty($selRegion)) {
            $regionQuery->whereIn('region_name', $selRegion);
        }

        $regions = $regionQuery->orderBy('region_name', 'ASC')
            ->get()->getResultArray();


        // Cluster list (dependent on region)
        $clusterQuery = $db2->table('alert_client')
            ->select('DISTINCT(cluster) AS cluster_name', false)
            ->where('status', 1);

        if (!empty($selRegion)) {
            $clusterQuery->whereIn('region', $selRegion);
        }

        // ACL Filter for Cluster Manager
        if (!empty($userClusterACLs)) {
            $clusterQuery->whereIn('cluster', $userClusterACLs);
        }

        $clusters = $clusterQuery
            ->orderBy('cluster', 'ASC')
            ->get()
            ->getResultArray();


        // Location list (dependent on region + cluster)
        $locationQuery = $db2->table('alert_client')
            ->select('DISTINCT(client_name) AS location_name', false)
            ->where('status', 1);

        if (!empty($selRegion)) {
            $locationQuery->whereIn('region', $selRegion);
        }

        if (!empty($selCluster)) {
            $locationQuery->whereIn('cluster', $selCluster);
        }

        // ACL Filter for Cluster Manager
        if (!empty($userClusterACLs)) {
            $locationQuery->whereIn('cluster', $userClusterACLs);
        }

        $locations = $locationQuery
            ->orderBy('client_name', 'ASC')
            ->get()
            ->getResultArray();

        // ---------- Month dropdown (distinct months from audit_date, respecting filters) ----------
        $monthQuery = $db2->table('alert_normal_audit')
            ->select("DISTINCT DATE_FORMAT(audit_date, '%Y-%m') AS ym", false)
            ->where('audit_date IS NOT NULL', null, false);

        // ACL Filter for Month Query (using EXISTS logic from lower down)
        if (!empty($userClusterACLs)) {
            $monthQuery->where(
                "LOWER(TRIM(alert_normal_audit.cluster_name)) IN (" . implode(',', array_map([$db2, 'escape'], $userClusterACLs)) . ")",
                null,
                false
            );
        }
        if (!empty($userClientACL)) {
            $monthQuery->whereIn('alert_normal_audit.client_name', $userClientACL);
        }

        if (!empty($selRegion)) {
            $monthQuery->whereIn('region', $selRegion);
        }

        if (!empty($selCluster)) {

            $monthQuery->where(
                "EXISTS (
                    SELECT 1 
                    FROM alert_client ac
                    WHERE ac.client_name = alert_normal_audit.client_name
                    AND ac.cluster IN (" . implode(',', array_map([$db2, 'escape'], $selCluster)) . ")
                )",
                null,
                false
            );
        }

        if (!empty($selLocation)) {
            $monthQuery->whereIn('location', $selLocation);
        }

        $monthRows = $monthQuery
            ->orderBy('ym', 'DESC')
            ->get()->getResultArray();

        // $months = ['2025-01' => 'Jan 2025', ...]
        $months = [];
        foreach ($monthRows as $row) {
            $ym = $row['ym'] ?? '';
            if (!$ym)
                continue;
            $label = date('M Y', strtotime($ym . '-01')); // Jan 2025
            $months[$ym] = $label;
        }

        $auditTypeQuery = $db2->table('alert_normal_audit')
            ->select('DISTINCT(audit_name) AS audit_name', false)
            ->where('audit_name IS NOT NULL', null, false)
            ->where("audit_name != ''", null, false);
            
        $auditTypes = $auditTypeQuery->orderBy('audit_name', 'ASC')->get()->getResultArray();

        $data['regions'] = $regions;
        $data['clusters'] = $clusters;
        $data['locations'] = $locations;
        $data['audit_types'] = $auditTypes;

        $data['selAuditType'] = $selAuditType;
        $data['selRegion'] = $selRegion;
        $data['selCluster'] = $selCluster;
        $data['selLocation'] = $selLocation;
        $data['months'] = $months;
        $data['selMonth'] = $month;

        // ---------- TOP COUNTERS: Open / Under Review / Working / Close / Forced Close ----------
        $db = db_connect();
        // helper('designation_acl'); // Already loaded

        $userClusterACLs = null;
        $userClient = null;
        if (isClusterManager() || isWHManager()) {
            $userClusterACLs = getClusterManagerAssignedCluster();
        } elseif (isAccountManager()) {
            $userClient = getAccountManagerAssignedClient();
        }

        $builder = $db->table('alert_normal_audit_details');
        $builder->join(
            'alert_normal_audit',
            'alert_normal_audit.normal_audit_id = alert_normal_audit_details.normal_audit_id',
            'left'
        );
        $builder->select('COUNT(*) as total, alert_normal_audit_details.audit_finding, alert_normal_audit_details.status');

        // Apply filters (audit_type / region / cluster / location / month)
        if (!empty($selAuditType)) {
            $builder->whereIn('alert_normal_audit.audit_name', $selAuditType);
        }

        if (!empty($selRegion)) {
            $builder->whereIn('alert_normal_audit.region', $selRegion);
        }

        if (!empty($selCluster)) {

            $builder->where(
                "EXISTS (
                    SELECT 1 
                    FROM alert_client ac
                    WHERE ac.client_name = alert_normal_audit.client_name
                    AND ac.cluster IN (" . implode(',', array_map([$db, 'escape'], $selCluster)) . ")
                )",
                null,
                false
            );
        }

        if (!empty($selLocation)) {
            $builder->whereIn('alert_normal_audit.location', $selLocation);
        }

        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $builder->where(
                "DATE_FORMAT(alert_normal_audit.audit_date,'%Y-%m') = " . $db->escape($month),
                null,
                false
            );
        }

        // ACL: Cluster Manager / Account Manager
        helper('designation_acl');
        $aclWhere = getOEAuditACLWhere('alert_normal_audit', 'Normal');
        if (!empty($aclWhere)) {
            // getOEAuditACLWhere returns something like " AND EXISTS (...)"
            // We strip the leading " AND " to use it safely in query builder
            $aclCondition = preg_replace('/^\s*AND\s*/i', '', $aclWhere);
            $builder->where($aclCondition, null, false);
        }

        // Show only NCs from reaudit=0
        $builder->where("alert_normal_audit.reaudit = '0'", null, false);
        $builder->where("alert_normal_audit_details.audit_finding = 'NO'", null, false);

        $builder->groupBy('alert_normal_audit_details.status');
        $builder->groupBy('alert_normal_audit_details.audit_finding');

        $countRows = $builder->get()->getResultArray();

        $counts = [];
        $openCount = 0;
        $workingCount = 0;
        $submittedToCMCount = 0;  // Status 6
        $underReviewAuditorCount = 0; // Status 2
        $closedCount = 0;       // normal closed only
        $forcedClosedCount = 0;       // forced closed

        foreach ($countRows as $row) {

            if ($row['status'] == '5') {
                $forcedClosedCount += $row['total'];
            } else if ($row['status'] == '3') {
                $closedCount += $row['total'];
            } else if ($row['audit_finding'] == "NO" && $row['status'] == "0") {
                $openCount += $row['total'];
            } else if ($row['audit_finding'] == "NO" && ($row['status'] == "1" || $row['status'] == "4")) {
                $workingCount += $row['total'];
            } else if ($row['audit_finding'] == "NO" && $row['status'] == "6") {
                $submittedToCMCount += $row['total'];
            } else if ($row['audit_finding'] == "NO" && $row['status'] == "2") {
                $underReviewAuditorCount += $row['total'];
            }
        }

        // 0 => Open, 1 => Working, 2 => Under Review, 3 => Closed, 4 => Draft, 5 => Forced Closed
        $qsString = $qs ? ('?' . $qs) : '';

        $datatop =
            '<div class="row justify-content-center text-center mb-5" style="gap:30px;">'

            // ALL
            . '<div class="col-md-2 p-0">'
            . '<a href="' . base_url('Masters/Normal_nc_tracker/index' . $qsString) . '" 
            class="card bg-dark hoverable shadow-sm">'
            . '<div class="card-body py-4">'
            . '<div class="fw-semibold text-white fs-6">All</div>'
            . '</div>'
            . '</a>'
            . '</div>'

            // OPEN (RED)
            . '<div class="col-md-2 p-0">'
            . '<a href="' . base_url('Masters/Normal_nc_tracker/index/open' . $qsString) . '" 
            class="card bg-danger hoverable shadow-sm">'
            . '<div class="card-body py-4">'
            . '<div class="fw-semibold text-white fs-6">
                    Open (' . $openCount . ')
                </div>'
            . '</div>'
            . '</a>'
            . '</div>'

            // WORKING (YELLOW)
            . '<div class="col-md-2 p-0">'
            . '<a href="' . base_url('Masters/Normal_nc_tracker/index/working' . $qsString) . '" 
            class="card bg-warning hoverable shadow-sm">'
            . '<div class="card-body py-4">'
            . '<div class="fw-semibold text-white fs-6">
                    Working (' . $workingCount . ')
                </div>'
            . '</div>'
            . '</a>'
            . '</div>'

            // SUBMITTED TO CM (PRIMARY)
            . '<div class="col-md-4 p-0">'
            . '<a href="' . base_url('Masters/Normal_nc_tracker/index/submitted_to_cm' . $qsString) . '" 
            class="card bg-primary hoverable shadow-sm">'
            . '<div class="card-body py-4">'
            . '<div class="fw-semibold text-white fs-6">
                    Under Review Cluster Manager (' . $submittedToCMCount . ')
                </div>'
            . '</div>'
            . '</a>'
            . '</div>'

            // UNDER REVIEW AUDITOR (INFO)
            . '<div class="col-md-3 p-0">'
            . '<a href="' . base_url('Masters/Normal_nc_tracker/index/under_review_auditor' . $qsString) . '" 
            class="card bg-info hoverable shadow-sm">'
            . '<div class="card-body py-4">'
            . '<div class="fw-semibold text-white fs-6">
                    Under Review Auditor (' . $underReviewAuditorCount . ')
                </div>'
            . '</div>'
            . '</a>'
            . '</div>'

            // CLOSED (GREEN)
            . '<div class="col-md-2 p-0">'
            . '<a href="' . base_url('Masters/Normal_nc_tracker/index/close' . $qsString) . '" 
            class="card bg-success hoverable shadow-sm">'
            . '<div class="card-body py-4">'
            . '<div class="fw-semibold text-white fs-6">
                    Closed (' . $closedCount . ')
                </div>'
            . '</div>'
            . '</a>'
            . '</div>'

            // FORCED CLOSED (DARK)
            . '<div class="col-md-3 p-0">'
            . '<a href="' . base_url('Masters/Normal_nc_tracker/index/forced' . $qsString) . '" 
            class="card bg-dark hoverable shadow-sm">'
            . '<div class="card-body py-4">'
            . '<div class="fw-semibold text-white fs-6">
                    Forced Close (' . $forcedClosedCount . ')
                </div>'
            . '</div>'
            . '</a>'
            . '</div>'

            . '</div>';


        $data['table'] = $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);

        return view("Master/add_normal_nc_tracker", $data);
    }

    public function table_ajax($status = null)
    {
        $db = db_connect();
        helper('designation_acl');

        // base query
        $sql = "SELECT 
                    alert_normal_audit_details.audit_details_id,
                    alert_normal_audit_details.category,
                    alert_normal_audit_details.risk_priority,
                    alert_normal_audit_details.weightage,
                    alert_normal_audit_details.audit_finding,
                    alert_normal_audit_details.audit_remark,
                    alert_normal_audit_details.audit_attachment,
                    alert_normal_audit_details.audit_after_attachment,
                    alert_normal_audit_details.nc_worked_by,
                    alert_normal_audit_details.nc_closed_by,
                    alert_normal_audit_details.nc_rejected_by,
                    alert_normal_audit_details.approved_by,
                    alert_normal_audit_details.update_date,
                    worked_user.user_name AS nc_worked_by_name,
                    closed_user.user_name AS nc_closed_by_name,
                    reject_user.user_name AS nc_rejected_by_name,
                    approve_user.user_name AS approved_by_name,
                    alert_normal_audit_details.status,
                    alert_normal_audit_details.normal_audit_id,
                    alert_normal_audit.audit_no, 
                    alert_normal_audit.audit_name,
                    alert_normal_audit.auditor_name, 
                    alert_normal_audit.auditee_name,
                    alert_normal_audit.region, 
                    alert_normal_audit.cluster_name,
                    alert_normal_audit.client_manager_name,
                    alert_normal_audit.audit_date,
                    alert_normal_audit.completion_date, 
                    alert_normal_audit.client_name, 
                    alert_normal_audit.location, 
                    alert_normal_audit.zone,
                    alert_normal_audit.audit_score
                FROM alert_normal_audit_details
                LEFT JOIN alert_normal_audit ON alert_normal_audit.normal_audit_id = alert_normal_audit_details.normal_audit_id
                LEFT JOIN alert_users worked_user ON worked_user.user_id = alert_normal_audit_details.nc_worked_by
                LEFT JOIN alert_users closed_user ON closed_user.user_id = alert_normal_audit_details.nc_closed_by
                LEFT JOIN alert_users reject_user ON reject_user.user_id = alert_normal_audit_details.nc_rejected_by
                LEFT JOIN alert_users approve_user ON approve_user.user_id = alert_normal_audit_details.approved_by
                 ";

        $params = [];

        // status filter
        if (!empty($status)) {
            $sql .= " WHERE ";

            $status = strtolower(trim($status));

            switch ($status) {

                case 'open':
                    $sql .= "alert_normal_audit_details.audit_finding = 'NO'";
                    $sql .= " AND alert_normal_audit_details.status = ?";
                    $params[] = 0;
                    break;

                case 'working':
                    $sql .= "alert_normal_audit_details.audit_finding = 'NO'";
                    $sql .= " AND alert_normal_audit_details.status IN (?, ?)";
                    $params[] = 1;
                    $params[] = 4;
                    break;

                case 'submitted_to_cm':   // STATUS 6
                    $sql .= "alert_normal_audit_details.audit_finding = 'NO'";
                    $sql .= " AND alert_normal_audit_details.status = ?";
                    $params[] = 6;
                    break;

                case 'under_review_auditor':  // STATUS 2
                    $sql .= "alert_normal_audit_details.audit_finding = 'NO'";
                    $sql .= " AND alert_normal_audit_details.status = ?";
                    $params[] = 2;
                    break;

                case 'close':
                    $sql .= "alert_normal_audit_details.audit_finding = 'NO' AND alert_normal_audit_details.status = ?";
                    $params[] = 3;
                    break;

                case 'forced':
                case 'forced close':
                    $sql .= "alert_normal_audit_details.audit_finding = 'NO' AND alert_normal_audit_details.status = ?";
                    $params[] = 5;
                    break;

                default:
                    $sql .= " alert_normal_audit_details.audit_finding = 'NO'";
                    break;
            }
            // Always apply reaudit=0
            $sql .= " AND alert_normal_audit.reaudit = '0'";
        } else {
            // NO STATUS FILTER - DEFAULT view (Show Open/Working/Review/Closed/Forced)
            $sql .= " WHERE alert_normal_audit_details.audit_finding = 'NO' AND alert_normal_audit.reaudit = '0'";
        }

        // multi-filters
        $req = service('request');

        $inAuditType = $req->getGet('audit_type');
        $inRegion = $req->getGet('region');
        $inCluster = $req->getGet('cluster');
        $inLocation = $req->getGet('location');
        $month = trim((string) $req->getGet('month') ?? '');

        $selAuditType = !empty($inAuditType) ? (is_array($inAuditType) ? $inAuditType : [$inAuditType]) : [];
        $selRegion = !empty($inRegion) ? (is_array($inRegion) ? $inRegion : [$inRegion]) : [];
        $selCluster = !empty($inCluster) ? (is_array($inCluster) ? $inCluster : [$inCluster]) : [];
        $selLocation = !empty($inLocation) ? (is_array($inLocation) ? $inLocation : [$inLocation]) : [];

        // Small safety: if no WHERE yet, start it
        if (strpos($sql, 'WHERE') === false) {
            $sql .= " WHERE 1=1";
        }

        if (!empty($selAuditType)) {
            $placeholders = implode(',', array_fill(0, count($selAuditType), '?'));
            $sql .= " AND alert_normal_audit.audit_name IN ($placeholders)";
            foreach ($selAuditType as $a) {
                $params[] = $a;
            }
        }

        if (!empty($selRegion)) {
            $placeholders = implode(',', array_fill(0, count($selRegion), '?'));
            $sql .= " AND alert_normal_audit.region IN ($placeholders)";
            foreach ($selRegion as $r) {
                $params[] = $r;
            }
        }

        if (!empty($selCluster)) {
            $placeholders = implode(',', array_fill(0, count($selCluster), '?'));
            $sql .= " AND alert_normal_audit.cluster_name IN ($placeholders)";
            foreach ($selCluster as $c) {
                $params[] = $c;
            }
        }

        if (!empty($selLocation)) {
            $placeholders = implode(',', array_fill(0, count($selLocation), '?'));
            $sql .= " AND alert_normal_audit.location IN ($placeholders)";
            foreach ($selLocation as $l) {
                $params[] = $l;
            }
        }

        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $sql .= " AND DATE_FORMAT(alert_normal_audit.audit_date,'%Y-%m') = ?";
            $params[] = $month;
        }

        // ACL
        helper('designation_acl');
        $aclWhere = getOEAuditACLWhere('alert_normal_audit', 'Normal');
        if (!empty($aclWhere)) {
            $sql .= $aclWhere;
        }

        $sql .= " ORDER BY alert_normal_audit_details.audit_details_id DESC";

        $query = $db->query($sql, $params)->getResultArray();

        // status labels
        // $statusMessages = [
        //     0 => '<span class="badge badge-danger">Open</span>',
        //     1 => '<span class="badge badge-warning">Working</span>',
        //     2 => '<span class="badge badge-info">Under Review (Auditor)</span>',
        //     3 => '<span class="badge badge-success">Closed</span>',
        //     4 => '<span class="badge badge-secondary">Save as Draft</span>',
        //     5 => '<span class="badge badge-danger">Closed (Forced)</span>',
        //     6 => '<span class="badge badge-info">Under Review (Cluster Manager)</span>',
        // ];

        $statusMessages = [
            0 => '<span class="badge bg-danger">Open</span>',
            1 => '<span class="badge bg-warning">Working</span>',
            6 => '<span class="badge bg-primary">Under Review At Cluster Manager</span>',
            2 => '<span class="badge bg-info">Under Review At Auditor</span>',
            3 => '<span class="badge bg-success">Closed</span>',
            5 => '<span class="badge bg-dark">Forced Closed</span>',
            4 => '<span class="badge bg-secondary">Draft</span>',
        ];

        $tdata['table_data'] = [];
        foreach ($query as $row) {
            // before photo - with clickable preview
            $beforePath = $row['audit_attachment'] ?? '';
            if (!empty($beforePath)) {
                if (preg_match('/(uploads\/.+)$/', $beforePath, $m)) {
                    $beforePath = $m[1];
                } elseif (preg_match('/(writable\/.+)$/', $beforePath, $m)) {
                    $beforePath = $m[1];
                }
            }
            $beforeUrl = base_url($beforePath);
            $before_html = "<a href='" . $beforeUrl . "' target='_blank'><img src='" . $beforeUrl . "' onerror=\"this.src='" . env("defaultLogo") . "'\" height='50' width='50' /></a>";

            // after photo (trim to avoid trailing spaces breaking extension check)
            $after_html = "-";
            if (!empty($row['audit_after_attachment'])) {
                $ap = trim($row['audit_after_attachment']);

                if ($ap !== '') {
                    // Clean up the path - remove any absolute path prefixes
                    if (preg_match('/(uploads\/.+)$/', $ap, $m)) {
                        $ap = $m[1];
                    } elseif (preg_match('/(writable\/.+)$/', $ap, $m)) {
                        $ap = $m[1];
                    }
                    // If path already starts with uploads/, use it as is
                    elseif (strpos($ap, 'uploads/') === 0) {
                        // Path is already correct
                    }

                    $ap = trim($ap);
                    $ext = strtolower(pathinfo($ap, PATHINFO_EXTENSION));
                    $url = base_url($ap);

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $after_html = "<a href='" . $url . "' target='_blank'>
                            <img src='" . $url . "' onerror=\"this.src='" . env('defaultLogo') . "'\" height='50' width='50' />
                        </a>";
                    } elseif ($ext === 'pdf') {
                        $after_html = "<a href='" . $url . "' target='_blank' class='btn btn-sm btn-danger'>
                            <i class='fa fa-file-pdf'></i> PDF
                        </a>";
                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                        $after_html = "<a href='" . $url . "' target='_blank' class='btn btn-sm btn-success'>
                            <i class='fa fa-file-excel'></i> Excel
                        </a>";
                    }
                }
            }

            // action buttons
            helper('designation_acl');
            $currentStatus = isset($row['status']) ? $row['status'] : 0;
            $activeBtn = "";
            $editBtn = "";

            // Higher Authority can view data but cannot take actions on NC records
            $isHigherAuthorityUser = isHigherAuthority();

            // Skip action buttons for Higher Authority - they have read-only access
            if ($isHigherAuthorityUser) {
                // Higher Authority sees only status badge, no action buttons
                $activeBtn = "";
                $editBtn = "";
            } else if ($currentStatus == 0) {
                // Open: Account Manager and Cluster Manager can move to working
                if (isAccountManager() || isClusterManager() || isAdmin() || isAuditor()) {
                    $activeBtn = '<button class="btn btn-icon btn-success" title="Move to Working" onclick="updateOeStatus(this,' . $row['audit_details_id'] . ', \'working\');">
                        <span class="indicator-label svg-icon svg-icon-2">
                            <i class="fa fa-unlock"></i>
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                    </button>';
                } else if (isWHManager()) {
                    // WH Manager cannot unlock
                    $activeBtn = '';
                }
            } else if ($currentStatus == 1 || $currentStatus == 4) {
                // Working/Draft: Account Manager / Cluster Manager / Admin / Auditor can edit
                if (isAccountManager() || isClusterManager() || isAdmin() || isAuditor()) {
                    $editBtn = '<button data-ajax-url="' . base_url("Masters/Normal_nc_tracker/get_form_data/" . $row['audit_details_id']) . '" class="btn btn-icon btn-primary" title="Edit / Submit for Review" onclick="edit_id(this,' . $row['audit_details_id'] . ');">
                         <span class="indicator-label svg-icon svg-icon-3">
                             <i class="fa fa-edit"></i>
                         </span>
                         <span class="indicator-progress">
                             <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                         </span>
                         </button>';
                }
            } else if ($currentStatus == 6) {
                // Status 6: Under Review by AM --> Waiting for CM Approval
                if (isClusterManager() || isWHManager() || isAdmin()) {
                    // CM Can Approve or Reject
                    // Approve -> Status 2 (Under Review by CM)
                    $activeBtn = '<button class="btn btn-icon btn-success me-2" title="Accept & Forward to Auditor" onclick="updateOeStatus(this,' . $row['audit_details_id'] . ', \'Under Review\');">
                        <span class="indicator-label svg-icon svg-icon-2">
                            <i class="fa fa-check"></i>
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                    </button>';
                    $editBtn = '<button class="btn btn-icon btn-danger" title="Reject" onclick="updateOeStatus(this,' . $row['audit_details_id'] . ', \'open\');">
                        <span class="indicator-label svg-icon svg-icon-2">
                            <i class="fa fa-times"></i>
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                    </button>';
                } else {
                    // AM / Auditor sees pending
                    // $editBtn = '<span class="badge badge-light-info" title="Waiting for approval from Cluster Manager">Pending Approval at Cluster Manager</span>';
                }
            } else if ($currentStatus == 2) {
                // Status 2: Under Review by CM --> Waiting for Auditor Approval
                if (isAuditor() || isAdmin()) {
                    $activeBtn = '<button class="btn btn-icon btn-success me-2" title="Approve & Close" onclick="updateOeStatus(this,' . $row['audit_details_id'] . ', \'Closed\');">
                        <span class="indicator-label svg-icon svg-icon-2">
                            <i class="fa fa-check"></i>
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                    </button>';
                    $editBtn = '<button class="btn btn-icon btn-danger" title="Reject" onclick="updateOeStatus(this,' . $row['audit_details_id'] . ', \'open\');">
                        <span class="indicator-label svg-icon svg-icon-2">
                            <i class="fa fa-times"></i>
                        </span>
                        <span class="indicator-progress">
                            <span class="spinner-border spinner-border-sm align-middle"></span>
                        </span>
                    </button>';
                } else {
                    // CM / AM sees pending
                    //$editBtn = '<span class="badge badge-light-info" title="Waiting for approval from Auditor">Pending Approval at Auditor</span>';
                }
            } else if ($currentStatus == 3 || $currentStatus == 5) {
                // Closed
            }

            $riskPriorityFormatted =
                '<pre class="form-label" style="white-space: pre-wrap; margin: 0; width: 600px;">'
                . ($row['risk_priority'] ?? '')
                . '</pre>';

            $action = "<center>" . ($statusMessages[$currentStatus] ?? '') . "<br><br>" . $activeBtn . $editBtn . "</center>";

            $bulkSelectHtml = '';
            // Only show checkbox if status = 2 (Under Review Auditor) and user is Auditor/Admin
            if ($currentStatus == 2 && (isAuditor() || isAdmin())) {
                $bulkSelectHtml = '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input nc-checkbox" type="checkbox" value="' . $row['audit_details_id'] . '" /></div>';
            }

            $tdata['table_data'][] = [
                'bulk_select' => $bulkSelectHtml,
                'audit_details_id' => $row['audit_details_id'],
                'normal_audit_id' => $row['normal_audit_id'],
                'audit_no' => $row['audit_no'],
                'audit_name' => $row['audit_name'],
                'auditor_name' => $row['auditor_name'],
                'auditee_name' => $row['auditee_name'],
                'region' => $row['region'],
                'cluster_name' => $row['cluster_name'],
                'client_manager_name' => $row['client_manager_name'],
                'audit_date' => $row['audit_date'],
                'client_name' => $row['client_name'],
                'location' => $row['location'],
                'zone' => $row['zone'],
                'audit_score' => $row['audit_score'],
                'category' => $row['category'],
                'risk_priority' => $riskPriorityFormatted,
                'weightage' => $row['weightage'],
                'audit_finding' => $row['audit_finding'],
                'audit_remark' => nl2br(htmlspecialchars($row['audit_remark'] ?? '')),
                'before_photo' => $before_html,
                'audit_attachment' => $after_html,
                'nc_worked_by' => ($row['nc_worked_by'] == '0') ? 'Super Admin' : ($row['nc_worked_by_name'] ?? '-'),
                'nc_closed_by' => ($row['nc_closed_by'] == '0') ? 'Super Admin' : ($row['nc_closed_by_name'] ?? '-'),
                'nc_rejected_by' => ($row['nc_rejected_by'] == '0') ? 'Super Admin' : ($row['nc_rejected_by_name'] ?? '-'),
                'approved_by' => ($row['approved_by'] == '0') ? 'Super Admin' : ($row['approved_by_name'] ?? '-'),
                'update_date' => $row['update_date'] ?? '-',
                'status' => $currentStatus,
                'action' => $action
            ];
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);

        return $this->response->setJSON($tdata);
    }

    public function status_filter($status)
    {
        helper('designation_acl');
        $data = [];
        $tdata['title'] = ucfirst($status) . " Normal NC Tracker";
        $tdata['button_id'] = "user_modal";
        $tdata['hide_add_button'] = true; // explicitly hide the add button

        $req = service('request');
        $inAuditType = $req->getGet('audit_type');
        $inRegion = $req->getGet('region');
        $inCluster = $req->getGet('cluster');
        $inLocation = $req->getGet('location');
        $month = trim((string) $req->getGet('month'));

        $selAuditType = !empty($inAuditType) ? (is_array($inAuditType) ? $inAuditType : [$inAuditType]) : [];
        $selRegion = !empty($inRegion) ? (is_array($inRegion) ? $inRegion : [$inRegion]) : [];
        $selCluster = !empty($inCluster) ? (is_array($inCluster) ? $inCluster : [$inCluster]) : [];
        $selLocation = !empty($inLocation) ? (is_array($inLocation) ? $inLocation : [$inLocation]) : [];

        $db = db_connect();
        $builder = $db->table('alert_normal_audit_details')
            ->select('alert_normal_audit_details.audit_details_id,alert_normal_audit_details.category,
                      alert_normal_audit_details.audit_question,
                      alert_normal_audit_details.risk_priority,alert_normal_audit_details.weightage,
                      alert_normal_audit_details.audit_finding,alert_normal_audit_details.audit_remark,
                      alert_normal_audit_details.audit_attachment,alert_normal_audit_details.audit_after_attachment,alert_normal_audit_details.normal_audit_id,alert_normal_audit.audit_no, 
                      alert_normal_audit.audit_name,alert_normal_audit.auditor_name, 
                      alert_normal_audit.auditee_name,alert_normal_audit.region, 
                      alert_normal_audit.audit_date,alert_normal_audit.completion_date, 
                      alert_normal_audit.client_name, alert_normal_audit.location, 
                      alert_normal_audit.zone,alert_normal_audit.audit_score')
            ->join('alert_normal_audit', 'alert_normal_audit.normal_audit_id = alert_normal_audit_details.normal_audit_id', 'left');

        helper('designation_acl');
        $aclWhere = getOEAuditACLWhere('alert_normal_audit', 'Normal');
        if (!empty($aclWhere)) {
            $aclCondition = preg_replace('/^\s*AND\s*/i', '', $aclWhere);
            $builder->where($aclCondition, null, false);
        }

        switch (strtolower($status)) {
            case 'open':
                $builder->where('alert_normal_audit_details.status', 0);
                break;
            case 'working':
                // include working + draft
                $builder->whereIn('alert_normal_audit_details.status', [1, 4]);
                break;
            case 'under review':
                $builder->where('alert_normal_audit_details.status', 2);
                break;
            case 'close':
                $builder->where('alert_normal_audit_details.status', 3);
                break;
            case 'forced':
            case 'forced close':
                $builder->where('alert_normal_audit_details.status', 5);
                break;
            default:
                break;
        }

        // Apply reaudit filter: show closed/force-closed NCs even if parent audit was reaudited
        // For open/working/under review, only show if parent audit hasn't been reaudited
        $statusLower = strtolower($status);
        if ($statusLower !== 'close' && $statusLower !== 'forced' && $statusLower !== 'forced close') {
            // For non-closed statuses, exclude reaudited audits UNLESS the NC itself is closed/force-closed
            $builder->where("(alert_normal_audit.reaudit = '0' OR alert_normal_audit_details.status IN (3, 5))", null, false);
        }
        // For close/forced statuses, show all (no reaudit filter needed)

        if (!empty($selAuditType)) {
            $builder->whereIn('alert_normal_audit.audit_name', $selAuditType);
        }
        
        if (!empty($selRegion)) {
            $builder->whereIn('alert_normal_audit.region', $selRegion);
        }
        if (!empty($selCluster)) {
            $escapedClusters = implode(',', array_map([$db, 'escape'], $selCluster));
            $builder->where("alert_normal_audit.cluster_name IN ($escapedClusters)", null, false);
        }
        if (!empty($selLocation)) {
            $builder->whereIn('alert_normal_audit.location', $selLocation);
        }
        if ($month !== '' && preg_match('/^\d{4}\-\d{2}$/', $month)) {
            $builder->where(
                "DATE_FORMAT(alert_normal_audit.audit_date, '%Y-%m') = " . $db->escape($month),
                null,
                false
            );
        }

        $query = $builder->get()->getResultArray();

        $tdata['table_data'] = [];
        foreach ($query as $row) {
            $beforePath = $row['audit_attachment'] ?? '';
            if (!empty($beforePath)) {
                if (preg_match('/(uploads\/.+)$/', $beforePath, $m)) {
                    $beforePath = $m[1];
                } elseif (preg_match('/(writable\/.+)$/', $beforePath, $m)) {
                    $beforePath = $m[1];
                }
            }
            if (empty($beforePath) || strpos($beforePath, 'uploads/') !== 0 || !is_file(FCPATH . $beforePath)) {
                $sid = $row['normal_audit_id'] ?? null;
                if (!empty($sid)) {
                    $folder = 'uploads/Audit/' . $sid . '/';
                    $base = basename((string) ($row['audit_attachment'] ?? ''));
                    if (!empty($base) && $base !== '.' && $base !== '..') {
                        $candidate = $folder . $base;
                        if (is_file(FCPATH . $candidate)) {
                            $beforePath = $candidate;
                        }
                    }
                    if (empty($beforePath) || !is_file(FCPATH . $beforePath)) {
                        $matches = glob(FCPATH . $folder . '*');
                        if ($matches) {
                            usort($matches, function ($a, $b) {
                                return filemtime($b) <=> filemtime($a); });
                            $picked = null;
                            foreach ($matches as $mfile) {
                                $ext = strtolower(pathinfo($mfile, PATHINFO_EXTENSION));
                                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    $picked = $mfile;
                                    break;
                                }
                            }
                            if (!$picked) {
                                $picked = $matches[0];
                            }
                            $rel = str_replace(FCPATH, '', $picked);
                            $rel = str_replace('\\', '/', $rel);
                            $beforePath = $rel;
                        }
                    }
                }
            }
            // before photo - with clickable preview
            $beforeUrl = base_url($beforePath);
            $attachment_html = "<a href='" . $beforeUrl . "' target='_blank'><img src='" . $beforeUrl . "' onerror=\"this.src='" . env("defaultLogo") . "'\" height='50' width='50' /></a>";

            $edit = '<button data-ajax-url="' . base_url("Masters/Normal_nc_tracker/get_form_data/" . $row['audit_details_id']) . '" class="btn btn-icon btn-primary" onclick="edit_id(this,' . $row['audit_details_id'] . ');">
                     <i class="fa fa-edit"></i>
                     </button>';

            // after NC (trim path)
            $after_html = "-";
            if (!empty($row['audit_after_attachment'])) {
                $ap = trim($row['audit_after_attachment']);

                if ($ap !== '') {
                    // Clean up the path - remove any absolute path prefixes
                    if (preg_match('/(uploads\/.+)$/', $ap, $m)) {
                        $ap = $m[1];
                    } elseif (preg_match('/(writable\/.+)$/', $ap, $m)) {
                        $ap = $m[1];
                    }
                    // If path already starts with uploads/, use it as is
                    elseif (strpos($ap, 'uploads/') === 0) {
                        // Path is already correct
                    }

                    $ap = trim($ap);
                    $ext = strtolower(pathinfo($ap, PATHINFO_EXTENSION));
                    $url = base_url($ap);

                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $after_html = "<a href='" . $url . "' target='_blank'>
                            <img src='" . $url . "' onerror=\"this.src='" . env('defaultLogo') . "'\" height='50' width='50' />
                        </a>";
                    } elseif ($ext === 'pdf') {
                        $after_html = "<a href='" . $url . "' target='_blank' class='btn btn-sm btn-danger'>
                            <i class='fa fa-file-pdf'></i> PDF
                        </a>";
                    } elseif (in_array($ext, ['xls', 'xlsx'])) {
                        $after_html = "<a href='" . $url . "' target='_blank' class='btn btn-sm btn-success'>
                            <i class='fa fa-file-excel'></i> Excel
                        </a>";
                    }
                }
            }

            $tdata['table_data'][] = [
                'audit_details_id' => $row['audit_details_id'],
                'audit_no' => $row['audit_no'],
                'audit_name' => $row['audit_name'],
                'auditor_name' => $row['auditor_name'],
                'auditee_name' => $row['auditee_name'],
                'region' => $row['region'],
                'audit_date' => $row['audit_date'],
                'completion_date' => $row['completion_date'],
                'client_name' => $row['client_name'],
                'location' => $row['location'],
                'audit_score' => $row['audit_score'],
                'category' => $row['category'],
                'risk_priority' => $row['risk_priority'],
                'weightage' => $row['weightage'],
                'audit_finding' => $row['audit_finding'],
                'audit_remark' => $row['audit_remark'],
                'audit_attachment' => $after_html,
                'action' => $edit
            ];
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);

        $data['ajax_url'] = base_url("Masters/Normal_nc_tracker/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Normal_nc_tracker/table_ajax");
        $data['table'] = view("Layout/table-view", $tdata);

        return view("Master/add_normal_nc_tracker", $data);
    }

    public function get_form_data($id)
    {
        $responce['status'] = "0";
        $responce['message'] = "Details not found";

        if (isset($id)) {

            $db = db_connect();
            $responce['data'] = $db->table('alert_normal_audit_details')
                ->select('alert_normal_audit_details.*, 
                         alert_normal_audit.audit_no AS master_audit_no, 
                         alert_normal_audit.audit_name AS master_audit_name, 
                         alert_normal_audit.auditor_name AS master_auditor_name, 
                         alert_normal_audit.auditee_name AS master_auditee_name, 
                         alert_normal_audit.region AS master_region, 
                         alert_normal_audit.client_name AS master_client_name, 
                         alert_normal_audit.location AS master_location, 
                         alert_normal_audit.zone AS master_zone, 
                         alert_normal_audit.audit_score AS master_audit_score, 
                         alert_normal_audit.audit_date AS master_audit_date, 
                         alert_normal_audit.completion_date AS master_completion_date')
                ->join(
                    'alert_normal_audit',
                    'alert_normal_audit.normal_audit_id = alert_normal_audit_details.normal_audit_id',
                    'left'
                )
                ->where("audit_details_id", $id)
                ->get()
                ->getRowArray();

            $responce['status'] = "1";
            $responce['message'] = "Details found";

            if (!empty($responce['data'])) {

                $responce['data']['audit_remark'] = '';
                $responce['data']['nc_remark'] = '';

                $beforePath = $responce['data']['audit_attachment'] ?? '';
                if (!empty($beforePath)) {
                    if (preg_match('/(uploads\/.+)$/', $beforePath, $m)) {
                        $beforePath = $m[1];
                    } elseif (preg_match('/(writable\/.+)$/', $beforePath, $m)) {
                        $beforePath = $m[1];
                    }
                }
                if (empty($beforePath) || strpos($beforePath, 'uploads/') !== 0 || !is_file(FCPATH . $beforePath)) {
                    $sid = $responce['data']['normal_audit_id'] ?? null;
                    if (!empty($sid)) {
                        $folder = 'uploads/Audit/' . $sid . '/';
                        $base = basename((string) ($responce['data']['audit_attachment'] ?? ''));
                        if (!empty($base) && $base !== '.' && $base !== '..') {
                            $candidate = $folder . $base;
                            if (is_file(FCPATH . $candidate)) {
                                $beforePath = $candidate;
                            }
                        }
                        if (empty($beforePath) || !is_file(FCPATH . $beforePath)) {
                            $matches = glob(FCPATH . $folder . '*');
                            if ($matches) {
                                usort($matches, function ($a, $b) {
                                    return filemtime($b) <=> filemtime($a);
                                });
                                $picked = null;
                                foreach ($matches as $mfile) {
                                    $ext = strtolower(pathinfo($mfile, PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                        $picked = $mfile;
                                        break;
                                    }
                                }
                                if (!$picked) {
                                    $picked = $matches[0];
                                }
                                $rel = str_replace(FCPATH, '', $picked);
                                $rel = str_replace('\\', '/', $rel);
                                $beforePath = $rel;
                            }
                        }
                    }
                }
                $responce['data']['before_photo_url'] = !empty($beforePath) ? base_url($beforePath) : '';
                $responce['data']['after_photo_url'] = $responce['data']['audit_after_attachment'] ?? '';
            }
        }

        echo json_encode($responce);
    }


    public function save_details($id = null, $action = null)
    {
        $request = service('request');
        helper('designation_acl');
        $postData = $request->getVar();

        $formAction = $postData['action'] ?? 'submit_for_review';
        unset($postData['action']);

        $db = db_connect();
        $loggedInUser = $_SESSION['user_id'] ?? null;
        if (isset($postData['honeypot'])) {
            unset($postData['honeypot']);
        }

        if (!isset($id) || empty($id)) {
            $id = $request->getVar('audit_details_id');
        }

        if (isset($id) && $id !== '') {
            $currentRecord = $db->table('alert_normal_audit_details')
                ->where('audit_details_id', $id)
                ->get()
                ->getRowArray();
            if (!$currentRecord) {
                $responce['status'] = "0";
                $responce['message'] = "Audit details not found";
                echo json_encode($responce);
                return;
            }
            $previousStatus = (string) ($currentRecord['status'] ?? '');

            // Higher Authority has read-only access - cannot modify NC records
            if (isHigherAuthority()) {
                $responce['status'] = "0";
                $responce['message'] = "Higher Authority has read-only access to NC Tracker";
                echo json_encode($responce);
                return;
            }

            if (isClusterManager() || isWHManager()) {
                $currentStatus = $previousStatus;

                if ($currentStatus == 3 || $currentStatus == 5) {
                    $responce['status'] = "0";
                    $responce['message'] = "Cluster Managers cannot modify closed NCs";
                    echo json_encode($responce);
                    return;
                }

                if ($currentStatus == 2 && isset($postData['status']) && $postData['status'] == '3') {
                    $responce['status'] = "0";
                    $responce['message'] = "Cluster Managers cannot approve NCs. Only Auditors can approve.";
                    echo json_encode($responce);
                    return;
                }
            }
            $responce['message'] = "Data updation faild";
            if (isset($action)) {
                switch ($action) {
                    case "open":
                        $postData['status'] = "0";
                        break;
                    case "working":
                        $postData['status'] = "1";
                        break;
                    case "under review":
                    case "Under Review":
                        $postData['status'] = "2";
                        break;
                    case "Closed":
                        $postData['status'] = "3";
                        break;
                    case "Save as Draft":
                        $postData['status'] = "4";
                }
            }

            if ($formAction === 'save_as_draft') {
                $postData['status'] = '4';
            } else if ($formAction === 'submit_for_review') {
                if (isAuditor() || isAdmin()) {
                    $postData['status'] = "2";
                } else {
                    $postData['status'] = "6";
                }
            }

            $updatePayload = [];
            if (isset($postData['audit_remark'])) {
                $updatePayload['audit_remark'] = $postData['audit_remark'];
            }
            if (isset($postData['nc_remark']) && !isset($updatePayload['audit_remark'])) {
                $updatePayload['audit_remark'] = $postData['nc_remark'];
            }

            $ncAfterPhoto = $request->getFile('nc_after_photo');
            $hasAfter = false;
            if ($ncAfterPhoto && $ncAfterPhoto->isValid() && !$ncAfterPhoto->hasMoved()) {
                $randomName = $ncAfterPhoto->getRandomName();
                $uploadPath = 'uploads/nc_after_photos/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }
                $ext = pathinfo($randomName, PATHINFO_EXTENSION);
                $finalName = 'oe_after_' . $id . '_' . $randomName;
                $ncAfterPhoto->move($uploadPath, $finalName);
                $hasAfter = true;
                $updatePayload['audit_after_attachment'] = $uploadPath . $finalName;
            } else {
                $existingAfter = $request->getVar('nc_after_photo_url');
                if (!empty($existingAfter)) {
                    $hasAfter = true;
                    $updatePayload['audit_after_attachment'] = $existingAfter;
                }
            }

            if (!isset($postData['status'])) {
                // If not explicitly set, default to Working(1) or Review(2) depending on photos?
                // Standard logic: if submitting with photos, it goes Under Review?
                $updatePayload['status'] = $hasAfter ? ((isAuditor() || isAdmin()) ? '2' : '6') : '1';
            } else {
                $updatePayload['status'] = $postData['status'];
            }

            // Open (0) → Closed (3) directly => Forced Closed (5)
            if ($previousStatus === '0' && isset($updatePayload['status']) && $updatePayload['status'] === '3') {
                $updatePayload['status'] = '5';
            }

            if ($updatePayload['status'] == '1' && $loggedInUser) {
                $updatePayload['nc_worked_by'] = $loggedInUser;
            }

            if (in_array($updatePayload['status'], ['3', '5'], true) && $loggedInUser) {
                $updatePayload['nc_closed_by'] = $loggedInUser;
            }

            if ($updatePayload['status'] == '0' && ($previousStatus === '2' || $previousStatus === '6') && $loggedInUser) {
                $updatePayload['nc_rejected_by'] = $loggedInUser;
                $updatePayload['status'] = '0';
                $updatePayload['nc_worked_by'] = null;
            }

            if ($updatePayload['status'] == '2' && $previousStatus === '6' && $loggedInUser) {
                $updatePayload['approved_by'] = $loggedInUser;
            }

            $updatePayload['update_date'] = date('Y-m-d H:i:s');

            $update_id = $db
                ->table('alert_normal_audit_details')
                ->where(["audit_details_id" => $id])
                ->set($updatePayload)
                ->update();

            // Check if status changed, then send mail
            if (isset($updatePayload['status']) && $updatePayload['status'] != $previousStatus) {
                $ncDetails = $this->getNcMailDetails($id);
                if ($ncDetails) {
                    $this->sendNcStatusMail($updatePayload['status'], $ncDetails);
                }
            }

            $responce['status'] = "1";
            $responce['message'] = "Data Updated successfully";
        } else {
            $responce['status'] = "0";
            $responce['message'] = "Missing audit details id";
        }
        echo json_encode($responce);
    }

    // Lightweight status update endpoint for Normal NC Tracker
    // public function update_status(){
    //     helper('designation_acl');
    //     $id = $this->request->getVar('id');
    //     $status = $this->request->getVar('status');

    //     $map = [
    //         'open'          => '0',
    //         'reject'        => '0',
    //         'working'       => '1',
    //         'Under Review'  => '2',
    //         'under review'  => '2',
    //         'Closed'        => '3',
    //         'closed'        => '3',
    //         'draft'         => '4',
    //         'forced'        => '5',
    //         'force_closed'  => '5',
    //         'forced_closed' => '5'
    //     ];
    //     $numeric = isset($map[$status]) ? $map[$status] : $status;

    //     $db = db_connect();
    //     $currentRecord = $db->table('alert_normal_audit_details')
    //         ->where('audit_details_id', $id)
    //         ->get()
    //         ->getRowArray();
    //     if (!$currentRecord) {
    //         return $this->response->setJSON(['status'=>0,'message'=>'Audit details not found']);
    //     }
    //     $currentStatus = (string)($currentRecord['status'] ?? '');

    //     // Higher Authority has read-only access - cannot update NC status
    //     if (isHigherAuthority()) {
    //         return $this->response->setJSON([
    //             'status' => 0,
    //             'message' => 'Higher Authority has read-only access to NC Tracker'
    //         ]);
    //     }

    //     // Open (0) → Closed (3) directly => Forced Closed (5)
    //     if ($currentStatus === '0' && $numeric === '3') {
    //         $numeric = '5';
    //     }

    //     if (isClusterManager() || isWHManager()) {            
    //         if ($currentStatus == 3 || $currentStatus == 5) {
    //             return $this->response->setJSON([
    //                 'status' => 0,
    //                 'message' => 'Cluster Managers cannot modify closed NCs'
    //             ]);
    //         }

    //         // CM cannot approve Status 2 (waiting for Auditor).
    //         // Status 6 (waiting for CM) IS NOT CHECKED HERE, so CM *can* approve 6 -> 2.
    //         if ($currentStatus == 2 && in_array($numeric, ['3','5','1'], true)) {
    //             return $this->response->setJSON([
    //                 'status' => 0,
    //                 'message' => 'Cluster Managers cannot approve or reject NCs. Only Auditors can approve/reject.'
    //             ]);
    //         }
    //     }

    //     $loggedInUserId = $_SESSION['user_id'] ?? null;
    //     $updateData = ['status' => $numeric];

    //     if ($numeric == '1' && $loggedInUserId) {
    //         $updateData['nc_worked_by'] = $loggedInUserId;
    //     }

    //     if (in_array($numeric, ['3','5'], true) && $loggedInUserId) {
    //         $updateData['nc_closed_by'] = $loggedInUserId;
    //     }

    //     // Rejecting from 2->0 or 6->0
    //     if ($numeric == '0' && ($currentStatus === '2' || $currentStatus === '6') && $loggedInUserId) {
    //         $updateData['nc_rejected_by'] = $loggedInUserId;
    //     }

    //     $ok = $db->table('alert_normal_audit_details')
    //         ->where('audit_details_id',$id)
    //         ->set($updateData)
    //         ->update();

    //     if($ok){
    //          // Send Mail if status changed
    //          if ($numeric != $currentStatus) {
    //              $ncDetails = $this->getNcMailDetails($id);
    //              if ($ncDetails) {
    //                  $this->sendNcStatusMail($numeric, $ncDetails);
    //              }
    //          }
    //         return $this->response->setJSON(['status'=>1,'message'=>'Status updated']);
    //     }
    //     return $this->response->setJSON(['status'=>0,'message'=>'Update failed']);
    // }
    public function update_status()
    {
        helper('designation_acl');

        $id = $this->request->getVar('id');
        $action = strtolower(trim($this->request->getVar('status')));

        $db = db_connect();

        $record = $db->table('alert_normal_audit_details')
            ->where('audit_details_id', $id)
            ->get()
            ->getRowArray();

        if (!$record) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Record not found']);
        }

        $currentStatus = (string) $record['status'];
        $userId = session('user_id');

        /* ===============================
           STATUS MAP
        =============================== */
        $map = [
            'open' => '0',
            'working' => '1',
            'submit_to_cm' => '6',
            'under review' => '2',
            'closed' => '3',
            'forced' => '5',
            'draft' => '4',
            'reject' => '0'
        ];

        if (!isset($map[$action])) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid action']);
        }

        $newStatus = $map[$action];

        /* ===============================
           ROLE BASED VALIDATION
        =============================== */

        // Higher Authority → read only
        if (isHigherAuthority()) {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Higher Authority has read-only access'
            ]);
        }

        /* -------------------------------
           0 → 1 (AM / CM start working)
        -------------------------------- */
        if ($currentStatus === '0' && $newStatus === '1') {
            if (!isAccountManager() && !isClusterManager() && !isAuditor() && !isAdmin()) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Not allowed']);
            }
        }

        /* -------------------------------
           1 → 6 (AM / CM submit to CM)
        -------------------------------- */
        if ($currentStatus === '1' && $newStatus === '6') {
            if (!isAccountManager() && !isClusterManager()) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Only AM or CM can submit to CM']);
            }
        }

        /* -------------------------------
           6 → 2 (CM approve)
        -------------------------------- */
        if ($currentStatus === '6' && $newStatus === '2') {
            if (!isClusterManager() && !isWHManager() && !isAdmin()) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Only CM can approve']);
            }
        }

        /* -------------------------------
           2 → 3 (Auditor close)
        -------------------------------- */
        if ($currentStatus === '2' && $newStatus === '3') {
            if (!isAuditor() && !isAdmin()) {
                return $this->response->setJSON(['status' => 0, 'message' => 'Only Auditor can close']);
            }
        }

        /* -------------------------------
           0 → 3 (Forced Close)
        -------------------------------- */
        if ($currentStatus === '0' && $newStatus === '3') {
            $newStatus = '5'; // convert to forced close
        }

        /* ===============================
           PREPARE UPDATE
        =============================== */

        $updateData = [
            'status' => $newStatus,
            'update_date' => date('Y-m-d H:i:s')
        ];

        /* -------------------------------
           Reject (6 or 2 → 0)
        -------------------------------- */
        if ($newStatus === '0' && in_array($currentStatus, ['6', '2'])) {
            if ($userId) {
                $updateData['nc_rejected_by'] = $userId;
            }
        }

        if ($newStatus === '1' && $userId) {
            $updateData['nc_worked_by'] = $userId;
        }

        if (in_array($newStatus, ['3', '5']) && $userId) {
            $updateData['nc_closed_by'] = $userId;
        }

        if ($newStatus === '2' && $currentStatus === '6' && $userId) {
            $updateData['approved_by'] = $userId;
        }

        /* ===============================
           UPDATE DB
        =============================== */

        $db->table('alert_normal_audit_details')
            ->where('audit_details_id', $id)
            ->update($updateData);

        return $this->response->setJSON([
            'status' => 1,
            'message' => 'Status updated successfully'
        ]);
    }

    public function bulk_close_nc()
    {
        helper('designation_acl');
        
        $request = service('request');
        if (!$request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Invalid request']);
        }

        $nc_ids = $request->getPost('nc_ids');

        if (empty($nc_ids) || !is_array($nc_ids)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'No NC records selected']);
        }

        // Higher Authority has read-only access
        if (isHigherAuthority()) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Higher Authority has read-only access']);
        }

        // Only Auditor and Admin can close Auditor Review NCs
        if (!isAuditor() && !isAdmin()) {
            return $this->response->setJSON(['status' => 0, 'message' => 'You do not have permission to close Auditor Review NCs']);
        }

        $db = db_connect();
        $userId = session()->get('user_id');
        $closedCount = 0;
        $failedCount = 0;

        $db->transStart();

        foreach ($nc_ids as $id) {
            $record = $db->table('alert_normal_audit_details')
                         ->where('audit_details_id', $id)
                         ->get()->getRowArray();

            if (!$record) {
                $failedCount++;
                continue;
            }

            // Only close if it's currently Under Review Auditor (2)
            if ((string)$record['status'] !== '2') {
                $failedCount++;
                continue;
            }

            $updateData = [
                'status'       => '3', // Closed
                'nc_closed_by' => $userId,
                'update_date'  => date('Y-m-d H:i:s')
            ];

            $db->table('alert_normal_audit_details')
               ->where('audit_details_id', $id)
               ->update($updateData);

            $closedCount++;

            // Call mail function for each closed record
            $ncDetails = $this->getNcMailDetails($id);
            if ($ncDetails) {
                $this->sendNcStatusMail('3', $ncDetails);
            }
        }

        $db->transComplete();

        if ($db->transStatus() === FALSE) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Database transaction failed']);
        }

        return $this->response->setJSON([
            'status' => 1,
            'message' => "Successfully closed $closedCount NC(s)." . ($failedCount > 0 ? " Failed to close $failedCount NC(s)." : "")
        ]);
    }

    private function getNcMailDetails($audit_details_id)
    {
        return db_connect()
            ->table('alert_normal_audit_details d')
            ->select("
                d.audit_details_id,
                d.normal_audit_id,
                d.category AS category_name,
                d.risk_priority,
                d.nc_worked_by,
                u.user_name AS nc_worked_by_name,
    
                sa.audit_no,
                sa.audit_name,
                sa.audit_date,
                sa.client_name,
                sa.location,
                sa.region AS region_name,
                sa.zone,
                sa.audit_by_user_id,
    
                c.cluster AS cluster_name,
    
                auditor.user_email AS auditor_email,
                auditor.user_name AS auditor_name,
    
                cm.user_email AS cluster_manager_email,
                cm.user_name AS cluster_manager_name,
                
                c.email as client_email,
                
                am.user_email as account_manager_email,
                am.user_name as account_manager_name
            ")

            ->join(
                'alert_normal_audit sa',
                'sa.normal_audit_id = d.normal_audit_id',
                'left'
            )
            ->join(
                'alert_client c',
                'c.client_name = sa.client_name',
                'left'
            )
            ->join(
                'alert_users u',
                'u.user_id = d.nc_worked_by',
                'left'
            )
            ->join(
                'alert_users auditor',
                'auditor.user_id = sa.audit_by_user_id',
                'left'
            )
            ->join(
                'alert_users cm',
                "cm.user_name = c.cluster AND cm.user_designation = 'Cluster manager'",
                'left'
            )
            // Join Account Manager
            ->join(
                'alert_users am',
                "am.user_name = c.account_manager AND am.user_designation = 'Account Manager'",
                'left'
            )

            ->where('d.audit_details_id', $audit_details_id)
            ->get()->getRowArray();
    }

    private function sendNcStatusMail($status, $nc)
    {
        $auditNo = $nc['audit_no'] ?? '';
        $auditDate = isset($nc['audit_date']) ? date('d-M-Y', strtotime($nc['audit_date'])) : '';
        $clientName = $nc['client_name'] ?? '';

        $description = "";
        $to = "";
        $cc = "";
        $statusText = "";
        $receiverName = "";

        if ($status == '6') { // Under Review (AM -> CM)
            $to = $nc['cluster_manager_email'];
            $cc = $nc['account_manager_email']; // Copy AM
            $statusText = "NC Under Review (AM)";
            $receiverName = $nc['cluster_manager_name'] ?? 'Cluster Manager';

            $description = "
                NC has been submitted by Account Manager for Audit <b>{$auditNo}</b> 
                dated <b>{$auditDate}</b>. Please review for 
                <b>{$clientName}</b>.
            ";
        } else if ($status == '2') { // Under Review (CM -> Auditor)
            $to = $nc['auditor_email'];
            $cc = $nc['cluster_manager_email'];
            $statusText = "NC Under Review (CM)";
            $receiverName = $nc['auditor_name'] ?? 'Auditor';

            $description = "
                NC has been approved by Cluster Manager and is now Under Review for Audit <b>{$auditNo}</b> 
                dated <b>{$auditDate}</b>. Please review for 
                <b>{$clientName}</b>.
            ";
        } else if ($status == '3') { // Closed (Auditor -> Closed)
            $to = $nc['cluster_manager_email'];
            $cc = $nc['auditor_email'];
            if (!empty($nc['account_manager_email'])) {
                $cc .= "," . $nc['account_manager_email'];
            }
            $statusText = "NC Closed";
            $receiverName = $nc['cluster_manager_name'] ?? 'Team';

            $description = "
                NC has been <b>Closed</b> for Audit <b>{$auditNo}</b> 
                dated <b>{$auditDate}</b>. Please review for 
                <b>{$clientName}</b>.
            ";
        } else if ($status == '0') { // Rejected
            $to = $nc['cluster_manager_email'];
            $cc = $nc['auditor_email'];
            if (!empty($nc['account_manager_email'])) {
                $to .= "," . $nc['account_manager_email'];
            }

            $statusText = "NC Rejected";
            $receiverName = "Team";

            $description = "
                NC has been <b>Rejected</b> for Audit <b>{$auditNo}</b> 
                dated <b>{$auditDate}</b>. Please review for 
                <b>{$clientName}</b>.
            ";
        }

        // Only send email if we have a valid status that triggers notification
        if (!empty($to) && !empty($statusText)) {
            $message = view('Emails/nc_status_update', [
                'status_text' => $statusText,
                'receiver_name' => $receiverName,
                'description' => $description,
                'nc' => $nc
            ]);

            try {
                helper('email_service');
                $testTo = "smita.tikone@unitglo.com";
                $ccArray = !empty($cc) ? explode(',', $cc) : [];
                $subject = "NC Status Update - Audit ID " . $nc['audit_details_id'];

                sendSystemEmail($to, $subject, $message, [], $ccArray);
            } catch (\Exception $e) {
                log_message('error', "NC MAIL EXCEPTION → " . $e->getMessage());
            }
        }
    }

    /**
     * Generate Monthly NC Report PDF
     * Shows client-wise and month-wise NC statistics
     */
    public function monthly_report_pdf()
    {
        // Load Dompdf
        require_once APPPATH . '/ThirdParty/dompdf/autoload.inc.php';

        $options = new \Dompdf\Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $db = db_connect();
        helper('designation_acl');

        // Get filter parameters
        $req = service('request');
        // Capture inputs (arrays or strings)
        $inAuditType = $req->getGet('audit_type');
        $inRegion = $req->getGet('region');
        $inCluster = $req->getGet('cluster');
        $inLocation = $req->getGet('location');
        $month = trim((string) $req->getGet('month') ?? '');
        $statusFilter = trim((string) $req->getGet('status') ?? ''); // ⭐ NEW: Status filter

        // Normalize to arrays
        $selAuditType = !empty($inAuditType) ? (is_array($inAuditType) ? $inAuditType : [$inAuditType]) : [];
        $selRegion = !empty($inRegion) ? (is_array($inRegion) ? $inRegion : [$inRegion]) : [];
        $selCluster = !empty($inCluster) ? (is_array($inCluster) ? $inCluster : [$inCluster]) : [];
        $selLocation = !empty($inLocation) ? (is_array($inLocation) ? $inLocation : [$inLocation]) : [];

        $builder = $db->table('alert_normal_audit_details');
        $builder->join(
            'alert_normal_audit',
            'alert_normal_audit.normal_audit_id = alert_normal_audit_details.normal_audit_id',
            'left'
        );

        // Apply filters
        if (!empty($selAuditType)) {
            $builder->whereIn('alert_normal_audit.audit_name', $selAuditType);
        }

        if (!empty($selRegion)) {
            $builder->whereIn('alert_normal_audit.region', $selRegion);
        }

        if (!empty($selCluster)) {

            $builder->where(
                "EXISTS (
                    SELECT 1 
                    FROM alert_client ac
                    WHERE ac.client_name = alert_normal_audit.client_name
                    AND ac.cluster IN (" . implode(',', array_map([$db, 'escape'], $selCluster)) . ")
                )",
                null,
                false
            );
        }

        if (!empty($selLocation)) {
            $builder->whereIn('alert_normal_audit.location', $selLocation);
        }

        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $builder->where(
                "DATE_FORMAT(alert_normal_audit.audit_date,'%Y-%m') = " . $db->escape($month),
                null,
                false
            );
        }

        // ⭐ NEW: Apply status filter
        if (!empty($statusFilter)) {
            $statusLower = strtolower(trim($statusFilter));

            switch ($statusLower) {
                case 'open':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->where('alert_normal_audit_details.status', 0);
                    break;

                case 'working':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->whereIn('alert_normal_audit_details.status', [1, 4]);
                    break;

                case 'under review':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->whereIn('alert_normal_audit_details.status', [2, 6]);
                    break;

                case 'close':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->where('alert_normal_audit_details.status', 3);
                    break;

                case 'forced':
                case 'forced close':
                    $builder->where('alert_normal_audit_details.status', 5);
                    break;
            }
        }

        // ACL: Cluster Manager / Account Manager filtering
        helper('designation_acl');
        $aclWhere = getOEAuditACLWhere('alert_normal_audit', 'Normal');
        if (!empty($aclWhere)) {
            $aclCondition = preg_replace('/^\s*AND\s*/i', '', $aclWhere);
            $builder->where($aclCondition, null, false);
        }
        




        // Only latest audits (not reaudits), UNLESS status is Closed(3) or Force Closed(5)
        $builder->where("(alert_normal_audit.reaudit = '0' OR alert_normal_audit_details.status IN (3, 5))", null, false);

        // Only NCs (audit_finding = 'NO')
        // Note: Force Closed (5) usually has finding='NO'
        $builder->where('alert_normal_audit_details.audit_finding', 'NO');

        $builder->select('
            alert_normal_audit.client_name,
            alert_normal_audit.region,
            alert_normal_audit.audit_date,
            alert_normal_audit_details.status
        ');

        $ncData = $builder->get()->getResultArray();

        // Calculate overall statistics
        $total_ncs = count($ncData);
        $open_count = 0;
        $working_count = 0;
        $review_count = 0;
        $closed_count = 0;
        $forced_count = 0;

        foreach ($ncData as $nc) {
            $status = $nc['status'];
            if ($status == '0')
                $open_count++;
            elseif ($status == '1' || $status == '4')
                $working_count++;
            elseif ($status == '2')
                $review_count++;
            elseif ($status == '3')
                $closed_count++;
            elseif ($status == '5')
                $forced_count++;
        }

        // Group by client
        $clientStats = [];
        foreach ($ncData as $nc) {
            $client = $nc['client_name'] ?? 'Unknown';
            $region_name = $nc['region'] ?? 'N/A';
            $status = $nc['status'];

            if (!isset($clientStats[$client])) {
                $clientStats[$client] = [
                    'client_name' => $client,
                    'region' => $region_name,
                    'total_ncs' => 0,
                    'open' => 0,
                    'working' => 0,
                    'review' => 0,
                    'closed' => 0,
                    'forced' => 0
                ];
            }

            $clientStats[$client]['total_ncs']++;

            if ($status == '0')
                $clientStats[$client]['open']++;
            elseif ($status == '1' || $status == '4')
                $clientStats[$client]['working']++;
            elseif ($status == '2')
                $clientStats[$client]['review']++;
            elseif ($status == '3')
                $clientStats[$client]['closed']++;
            elseif ($status == '5')
                $clientStats[$client]['forced']++;
        }

        // Sort by client name
        usort($clientStats, function ($a, $b) {
            return strcmp($a['client_name'], $b['client_name']);
        });

        // Group by month (if no specific month filter)
        $monthlyStats = [];
        if ($month === '') {
            foreach ($ncData as $nc) {
                $audit_date = $nc['audit_date'] ?? '';
                if ($audit_date) {
                    $month_key = date('Y-m', strtotime($audit_date));
                    $status = $nc['status'];

                    if (!isset($monthlyStats[$month_key])) {
                        $monthlyStats[$month_key] = [
                            'total' => 0,
                            'open' => 0,
                            'working' => 0,
                            'review' => 0,
                            'closed' => 0,
                            'forced' => 0
                        ];
                    }

                    $monthlyStats[$month_key]['total']++;

                    if ($status == '0')
                        $monthlyStats[$month_key]['open']++;
                    elseif ($status == '1' || $status == '4')
                        $monthlyStats[$month_key]['working']++;
                    elseif ($status == '2')
                        $monthlyStats[$month_key]['review']++;
                    elseif ($status == '3')
                        $monthlyStats[$month_key]['closed']++;
                    elseif ($status == '5')
                        $monthlyStats[$month_key]['forced']++;
                }
            }

            // Sort by month descending
            krsort($monthlyStats);
        }

        // Prepare data for view
        $data = [
            'month' => $month,
            'status_filter' => $statusFilter, // ⭐ NEW: Pass status filter to template
            'region' => !empty($selRegion) ? implode(', ', $selRegion) : '',
            'cluster' => !empty($selCluster) ? implode(', ', $selCluster) : '',
            'location' => !empty($selLocation) ? implode(', ', $selLocation) : '',
            'total_clients' => count($clientStats),
            'total_ncs' => $total_ncs,
            'open_count' => $open_count,
            'working_count' => $working_count,
            'review_count' => $review_count,
            'closed_count' => $closed_count,
            'forced_count' => $forced_count,
            'client_data' => $clientStats,
            'monthly_data' => $monthlyStats
        ];

        // Generate HTML
        $html = view("Master/Normal_nc_tracker_monthly_report_pdf", $data);

        // Load HTML into Dompdf
        $dompdf->loadHtml($html);

        // Set paper size and orientation
        $dompdf->setPaper('A4', 'landscape');

        // Render the PDF
        $dompdf->render();

        // Clear any previous output
        if (ob_get_length()) {
            ob_end_clean();
        }

        // Generate filename
        $filename = 'OE_NC_Monthly_Report_' . date('Y-m-d_His');
        if ($month) {
            $filename = 'OE_NC_Report_' . $month . '_' . date('Y-m-d_His');
        }

        // Output the generated PDF (force download)
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '.pdf"');
        echo $dompdf->output();
    }
    /**
     * Export OE NC Report as Excel (CSV)
     */
    public function export_report_excel()
    {
        $db = db_connect();
        helper('designation_acl');

        $req = service('request');

        // Capture inputs (arrays or strings)
        $inAuditType = $req->getGet('audit_type');
        $inRegion = $req->getGet('region');
        $inCluster = $req->getGet('cluster');
        $inLocation = $req->getGet('location');
        $month = trim((string) $req->getGet('month') ?? '');
        $statusFilter = trim((string) $req->getGet('status') ?? ''); // ⭐ NEW: Status filter

        // Normalize to arrays
        $selAuditType = !empty($inAuditType) ? (is_array($inAuditType) ? $inAuditType : [$inAuditType]) : [];
        $selRegion = !empty($inRegion) ? (is_array($inRegion) ? $inRegion : [$inRegion]) : [];
        $selCluster = !empty($inCluster) ? (is_array($inCluster) ? $inCluster : [$inCluster]) : [];
        $selLocation = !empty($inLocation) ? (is_array($inLocation) ? $inLocation : [$inLocation]) : [];

        $builder = $db->table('alert_normal_audit_details');
        $builder->join(
            'alert_normal_audit',
            'alert_normal_audit.normal_audit_id = alert_normal_audit_details.normal_audit_id',
            'left'
        );

        // Apply filters
        if (!empty($selAuditType)) {
            $builder->whereIn('alert_normal_audit.audit_name', $selAuditType);
        }

        if (!empty($selRegion)) {
            $builder->whereIn('alert_normal_audit.region', $selRegion);
        }

        if (!empty($selCluster)) {

            $builder->where(
                "EXISTS (
                    SELECT 1 
                    FROM alert_client ac
                    WHERE ac.client_name = alert_normal_audit.client_name
                    AND ac.cluster IN (" . implode(',', array_map([$db, 'escape'], $selCluster)) . ")
                )",
                null,
                false
            );
        }

        if (!empty($selLocation)) {
            $builder->whereIn('alert_normal_audit.location', $selLocation);
        }

        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $builder->where(
                "DATE_FORMAT(alert_normal_audit.audit_date,'%Y-%m') = " . $db->escape($month),
                null,
                false
            );
        }

        // ⭐ NEW: Apply status filter
        if (!empty($statusFilter)) {
            $statusLower = strtolower(trim($statusFilter));

            switch ($statusLower) {
                case 'open':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->where('alert_normal_audit_details.status', 0);
                    break;

                case 'working':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->whereIn('alert_normal_audit_details.status', [1, 4]);
                    break;

                case 'under review':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->whereIn('alert_normal_audit_details.status', [2, 6]);
                    break;

                case 'close':
                    $builder->where('alert_normal_audit_details.audit_finding', 'NO');
                    $builder->where('alert_normal_audit_details.status', 3);
                    break;

                case 'forced':
                case 'forced close':
                    $builder->where('alert_normal_audit_details.status', 5);
                    break;
            }
        }

        // ACL: Cluster Manager / Account Manager filtering
        helper('designation_acl');
        $aclWhere = getOEAuditACLWhere('alert_normal_audit', 'Normal');
        if (!empty($aclWhere)) {
            $aclCondition = preg_replace('/^\s*AND\s*/i', '', $aclWhere);
            $builder->where($aclCondition, null, false);
        }


        // Only latest audits (not reaudits), UNLESS status is Closed(3) or Force Closed(5)
        $builder->where("(alert_normal_audit.reaudit = '0' OR alert_normal_audit_details.status IN (3, 5))", null, false);

        // Only NCs (audit_finding = 'NO')
        $builder->where('alert_normal_audit_details.audit_finding', 'NO');

        $builder->select('
            alert_normal_audit_details.audit_details_id,
            alert_normal_audit.client_name,
            alert_normal_audit.region,
            alert_normal_audit.location,
            alert_normal_audit.audit_date,
            alert_normal_audit_details.audit_question,
            alert_normal_audit_details.risk_priority,
            alert_normal_audit_details.audit_remark,
            "" AS auditor_remark,
            alert_normal_audit_details.status,
            "" AS nc_closed_date,
            "" AS nc_reviewed_date
        ');

        $builder->orderBy('alert_normal_audit.client_name', 'ASC');
        $builder->orderBy('alert_normal_audit_details.status', 'ASC');

        $ncData = $builder->get()->getResultArray();

        // Generate CSV content
        $filename = 'OE_NC_Report_' . date('Y-m-d_His') . '.csv';
        if ($month) {
            $filename = 'OE_NC_Report_' . $month . '_' . date('Y-m-d_His') . '.csv';
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // Add BOM for Excel UTF-8 support
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // CSV Headers
        fputcsv($output, [
            'ID',
            'Client Name',
            'Region',
            'Location',
            'Audit Date',
            'Observation / Question',
            'Risk / Priority',
            'Audit Remark',
            'NC Remark',
            'Status',
            'Closed Date',
            'Reviewed Date'
        ]);

        // Status mapping
        $statusMap = [
            '0' => 'Open',
            '1' => 'Working',
            '2' => 'Under Review',
            '3' => 'Closed',
            '4' => 'Draft',
            '5' => 'Closed (Forced)'
        ];

        // Add data rows
        foreach ($ncData as $row) {
            fputcsv($output, [
                $row['audit_details_id'],
                $row['client_name'],
                $row['region'],
                $row['location'],
                $row['audit_date'],
                $row['audit_question'],
                $row['risk_priority'],
                $row['audit_remark'],
                $row['auditor_remark'],
                $statusMap[$row['status']] ?? 'Unknown',
                $row['nc_closed_date'],
                $row['nc_reviewed_date']
            ]);
        }

        fclose($output);
        exit;
    }
}
