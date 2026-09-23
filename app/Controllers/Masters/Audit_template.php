<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;
use App\Controllers\Masters\Audit_final_structure;
require APPPATH . '/ThirdParty/dompdf/autoload.inc.php';
use Dompdf\Options;
use Dompdf\Dompdf;
use CodeIgniter\Email\Email;
use PhpOffice\PhpSpreadsheet\IOFactory;
class Audit_template extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;


    public function __construct()
    {
        // changes on 16/10/25 by darsh: Using simple ACL helper without database changes
        helper(["form", "simple_acl_helper"]);
        $db = null;
        $db['table'] = 'alert_audit_template';
        $db['allowedFields'] = ['audit_name', 'audit_template_type', 'auditor_name', 'auditee_name', 'date', 'client_name', 'region', 'frequency', 'next_date', 'location', 'import_excel', 'score', 'status'];
        $db['primaryKey'] = "audit_template_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }

    /**
     * Helper: format weightage values for display by appending '%'.
     * Returns a new array (does NOT modify database).
     */
    private function formatWeightageDisplay(array $rows): array
    {
        foreach ($rows as $k => $r) {
            if (isset($r['weightage'])) {
                // If numeric, keep as is but add % for display.
                // If already contains %, avoid double-appending.
                $w = $r['weightage'];
                if ($w === null || $w === '') {
                    $rows[$k]['weightage'] = '';
                } else {
                    // Ensure numeric string preserved; if already contains % skip
                    if (is_string($w) && strpos($w, '%') !== false) {
                        $rows[$k]['weightage'] = $w;
                    } else {
                        $rows[$k]['weightage'] = (string) $w . '%';
                    }
                }
            }
        }
        return $rows;
    }

    public function index($view = null)
    {
        $data = [];
        $db = db_connect();
        $tdata['title'] = "Audit Templates";

        if (!isset($view)) {
            // changes on 14/10/25 by darsh: Hide audit creation buttons for Cluster Managers (read-only access)
            if ($_SESSION['role'] !== 'Cluster manager') {
                $tdata['button_name'] = "Create Audit";
                $tdata['button_id'] = "user_modal";
                $tdata['download_excel'] = '<div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" 
                            title="Click to Download Normal Sample Excel">
                            <a class="btn btn-sm btn-light btn-active-primary" 
                               href="' . base_url("assets/file/Normal_audit.csv") . '" 
                               download="normal_audit_sample_excel.csv" 
                               onclick="$(this).attr(\'data-kt-indicator\', \'on\'); 
                                        $(this).attr(\'disabled\', true); 
                                        setTimeout(function (obj) { 
                                            obj.attr(\'data-kt-indicator\', \'off\'); 
                                            obj.attr(\'disabled\', false); 
                                        }, 500, $(this));">
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                                <span class="indicator-label svg-icon svg-icon-3">
                                   Normal
                                </span>
                                <span class="indicator-progress">
                                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                <!--end::Svg Icon-->
                            </a> 
                            </div>
                            <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" 
                            title="Click to Download OE Sample Excel">
                            <a class="btn btn-sm btn-light btn-active-primary" 
                               href="' . base_url("assets/file/OE_audit.csv") . '" 
                               download="OE_audit_sample_excel.csv" 
                               onclick="$(this).attr(\'data-kt-indicator\', \'on\'); 
                                        $(this).attr(\'disabled\', true); 
                                        setTimeout(function (obj) { 
                                            obj.attr(\'data-kt-indicator\', \'off\'); 
                                            obj.attr(\'disabled\', false); 
                                        }, 500, $(this));">
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                                <span class="indicator-label svg-icon svg-icon-3">
                                   OE
                                </span>
                                <span class="indicator-progress">
                                    <!-- changes on 15/10/25 by darsh: fixed malformed span causing button misalignment -->
                                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                <!--end::Svg Icon-->
                            </a> 
                            </div> 
                            <div class="card-toolbar" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-trigger="hover" 
                            title="Click to Download HSE Sample Excel">
                            <a class="btn btn-sm btn-light btn-active-primary" 
                               href="' . base_url("assets/file/HSE_Audit_Question_Strict.csv") . '" 
                               download="HSE_Audit_question_sample_excel.csv" 
                               onclick="$(this).attr(\'data-kt-indicator\', \'on\'); 
                                        $(this).attr(\'disabled\', true); 
                                        setTimeout(function (obj) { 
                                            obj.attr(\'data-kt-indicator\', \'off\'); 
                                            obj.attr(\'disabled\', false); 
                                        }, 500, $(this));">
                                <!--begin::Svg Icon | path: icons/duotune/arrows/arr075.svg-->
                                <span class="indicator-label svg-icon svg-icon-3">
                                   HSE
                                </span>
                                <span class="indicator-progress">
                                    Please wait... <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                <!--end::Svg Icon-->
                            </a>
                        </div>';
            } else {
                // changes on 14/10/25 by darsh: Show read-only message for Cluster Managers
                $tdata['read_only_message'] = '<div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You have read-only access to audit templates.
            </div>';
            }
        } else {
            $data['button_id'] = "user_modal";

        }

        // changes on 29/10/25 by darsh: hide Location column from Audit Template list (UI only)
        $tdata['display_contents'] = [
            "audit_template_id" => "ID",
            "audit_name" => "Audit",
            "audit_template_type" => "Template Type",
            "date" => "Date",
            "frequency" => "Frequency",
            "score" => "Score",
            "action" => "Action"
        ];
        $data['ajax_url'] = base_url("Masters/Audit_template/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Audit_template/table_ajax");
        // changes on 1/11/25 by darsh: Replaced Engineer with Account Manager
        $data['user_designation'] = ["Account Manager", "Reporting manager", ""];
        $data['region_list'] = $db->table("alert_hse_client_master")->select("DISTINCT(region) as region_name")->where("status !=", 2)->orderBy("region", "ASC")->get()->getResultArray();

        $data['auditor_list'] = $db->table("alert_users")->select('*')->where("user_designation", "Auditor")->where("status", 1)->get()->getResultArray();
        $data['table'] = view("Layout/table-view", $tdata);
        // $data['table'] ="";
        return view("Master/audit_template", $data);
    }
    public function template_type_filter($category)
    {

        $data = [];
        $db = db_connect();
        $tdata['title'] = "Audit Template";
        // changes on 14/10/25 by darsh: Hide audit creation buttons for Cluster Managers (read-only access)
        if ($_SESSION['role'] !== 'Cluster manager') {
            $tdata['button_name'] = "Create Audit";
            $tdata['button_id'] = "user_modal";
            $tdata['button_sample'] = "Download Sample Excel";
        } else {
            // changes on 14/10/25 by darsh: Show read-only message for Cluster Managers
            $tdata['read_only_message'] = '<div class="alert alert-info">
                <i class="fas fa-info-circle"></i> You have read-only access to audit templates.
            </div>';
        }

        // changes on 29/10/25 by darsh: hide Location column from filtered view as well
        $tdata['display_contents'] = [
            "audit_template_id" => "ID",
            "audit_name" => "Audit",
            "audit_template_type" => "Template Type",
            "date" => "Date",
            "frequency" => "Frequency",
            "score" => "Score",
            "action" => "Action"
        ];
        $data['ajax_url'] = base_url("Masters/Audit_template/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Audit_template/table_ajax/" . $category);
        // changes on 1/11/25 by darsh: Replaced Engineer with Account Manager
        $data['user_designation'] = ["Account Manager", "Reporting manager", ""];
        $data['auditor_list'] = $db->table("alert_users")->select('*')->where("user_designation", "Auditor")->where("status", 1)->get()->getResultArray();
        $data['table'] = view("Layout/table-view", $tdata);
        // $data['table'] ="";
        return view("Master/audit_template", $data);
    }

    public function table_ajax($category = null)
    {
        // changes on 13/11/25 by darsh: Cluster-based ACL filtering
        helper('designation_acl');
        $db = db_connect();

        // Build base query
        $builder = $db->table('alert_audit_template');

        // Apply category filter if specified
        if ($category) {
            $builder->where('audit_template_type', $category);
        }

        // Exclude deactivated records (status = 3) from the table
        $builder->where('status !=', 3);

        // ACL: Apply cluster-based filtering for Cluster Managers - 13/11/25
        if (isClusterManager() || isWHManager()) {
            $userClusters = getClusterManagerAssignedCluster();
            if (!empty($userClusters)) {
                $escapedClusters = array_map([$db, 'escape'], $userClusters);
                $builder->where("EXISTS (
                    SELECT 1 FROM alert_client 
                    WHERE alert_client.client_name = alert_audit_template.client_name
                    AND LOWER(TRIM(alert_client.cluster)) IN (" . implode(',', array_map(function ($c) {
                    return "LOWER(TRIM($c))";
                }, $escapedClusters)) . ")
                    AND alert_client.status = 1
                )", null, false);
            }
        }

        $builder->orderBy('audit_template_id', 'DESC');
        $tdata['table_data'] = $builder->get()->getResultArray();

        $statusMessages = [
            0 => '<span class="badge badge-warning">New Version</span>',
            1 => '<span class="badge badge-info">Current Version</span>',
            2 => '<span class="badge badge-success">Next Version</span>',
            3 => '<span class="badge badge-secondary">Old Version</span>'
        ];
        foreach ($tdata['table_data'] as $key => $row) {
            $urrl = "";
            // ACL: Cluster Managers, WH Managers, and Account Managers have read-only access depending on audit type
            $perform = '';
            $showPerform = canPerformAudit($row['audit_template_type']);
            if ($showPerform) {
                if ($row['audit_template_type'] == "OE") {
                    $urrl = base_url("Masters/Audit_template/perform_audit/" . $row['audit_template_id']);
                } else if ($row['audit_template_type'] == "HSE") {
                    $urrl = base_url("Masters/Hse_audit/audit_view/" . $row['audit_template_id']);
                } else if ($row['audit_template_type'] == "Normal") {
                    $urrl = base_url("Masters/Audit_template/normal_perform_audit/" . $row['audit_template_id']);
                }
                if ($row['status'] != "3") {
                    $perform = '<button title="Perform Audit" onclick=\'window.location.href="' . $urrl . '"\' class="btn btn-sm btn-primary">
                            <span class="indicator-label">
                                <i class="fas fa-clipboard-check me-1"></i> Perform Audit
                            </span>
                                    <span class="indicator-progress">
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                            </button>';
                }
            }

            if ($row['status'] == "0") {
                $deactive = "";
                $tdata['table_data'][$key]['tr_class'] = "bg-light-warning";

            } else if ($row['status'] == "1") {
                $active = "";

            } else if ($row['status'] == "2") {
                $delete = "";
                $deactive = "";
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";

            }
            $tdata['table_data'][$key]['action'] = "<center>" . $statusMessages[$row['status']] . "<br><br>" . $perform . "</center>";
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }

    public function save_details($id = null, $action = null)
    {
        // JSON response clean
        if (ob_get_length()) {
            ob_end_clean();
        }
        $this->response->setContentType('application/json');

        // changes on 16/10/25 by darsh: Simple ACL restrictions for Cluster Managers
        if (!canCreateAudit() && !isset($id)) {
            return $this->response->setJSON([
                'status' => "0",
                'message' => "Access denied. Cluster managers have read-only access to audit templates."
            ]);
        }

        if (!canEditAudit() && isset($id)) {
            return $this->response->setJSON([
                'status' => "0",
                'message' => "Access denied. Cluster managers have read-only access to audit templates."
            ]);
        }

        $request = service('request');
        $postData = $request->getVar();

        if (isset($postData['honeypot'])) {
            unset($postData['honeypot']);
        }

        $responce = [
            'status' => "0",
            'message' => "Something went wrong"
        ];

        // ----------------------------
        // INSERT / UPDATE TEMPLATE
        // ----------------------------
        if (isset($id)) {
            $responce['message'] = "Data updation failed";

            if (isset($action)) {
                switch ($action) {
                    case "active":
                        $postData['status'] = "1";
                        break;
                    case "deactive":
                        $postData['status'] = "0";
                        break;
                    case "delete":
                        $postData['status'] = "2";
                        break;
                }
            }

            if ($this->BaseModel->update($id, $postData)) {
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }

        } else {
            // Require an Excel/CSV file when creating a new template
            $validation = $this->validateFileType('emport_excel');
            if (!$validation['valid']) {
                return $this->response->setJSON([
                    'status' => "0",
                    'message' => "An Excel/CSV file is required to create a new template."
                ]);
            }

            $postData['status'] = "1";
            $responce['message'] = "Data insertion failed";

            if ($this->BaseModel->insert($postData)) {
                $id = $this->BaseModel->getInsertID();
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }
        }

        // ----------------------------
        // IMPORT BASED ON TEMPLATE TYPE
        // ----------------------------
        if (isset($postData['audit_template_type']) && $postData['audit_template_type'] == "OE") {
            $importResult = $this->importExcel($id);

            if ($importResult === false) {
                $responce['status'] = "0";
                $responce['message'] = "File upload failed. Please check file type and size.";
            } else if (is_string($importResult)) {
                $responce['message'] = $importResult;
            }

        } else if (isset($postData['audit_template_type']) && $postData['audit_template_type'] == "HSE") {
            $importResult = $this->importHSE_Excel($id);

            if (is_array($importResult)) {
                $responce['status'] = $importResult['status'] ? "1" : "0";
                $responce['message'] = $importResult['message'];
                $responce['audit_type'] = "HSE";
            } else {
                $responce['status'] = "0";
                $responce['message'] = "HSE import failed.";
            }

        } else if (isset($postData['audit_template_type']) && $postData['audit_template_type'] == "Normal") {
            $importResult = $this->importNormal_Excel($id);

            if ($importResult === false) {
                $responce['status'] = "0";
                $responce['message'] = "File upload failed. Please check file type and size.";
            } else if ($importResult !== "ok") {
                $responce['status'] = "0";
                $responce['message'] = $importResult; // Show the actual validation error
            } else {
                $responce['message'] = "Normal Audit Template imported successfully.";
            }
        }

        return $this->response->setJSON($responce);
    }

   
    public function dashboard_view($audit_template_id = null)
    {
        $db = db_connect();

        // changes on 1/10/25 by darsh: region-wise monthly percentages, remove zone usage
        // Audit metadata for titles and fallback filtering
        // changes on 7/10/25 by darsh: also use audit_name as fallback filter where template_id column is missing (e.g., HSE)
        $templateRow = $db->table('alert_audit_template')
            ->select('audit_name, audit_template_type')
            ->where('audit_template_id', $audit_template_id)
            ->get()
            ->getRowArray();
        $audit_name = $templateRow['audit_name'] ?? 'Unknown Audit';
        $audit_type = $templateRow['audit_template_type'] ?? '';
        $data['audit_name'] = $audit_name;

        // Month summary by client (percentage yes of total for that month)
        // changes on 7/10/25 by darsh: fallback to Normal/HSE sources when OE data is absent
        $audit_rows = [];
        $q1 = $db->query(
            "SELECT a.client_name,
                    MONTHNAME(a.audit_date) AS audit_month,
                    SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END) AS yes_weight,
                    SUM(d.weightage) AS total_weight
             FROM alert_final_structured_audit_details d
             JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id
             WHERE a.audit_date IS NOT NULL AND a.audit_template_id = ?
             GROUP BY a.client_name, audit_month",
            [$audit_template_id]
        );
        $audit_rows = $q1->getResultArray();
        if (empty($audit_rows)) {
            // Normal fallback
            $q1n = $db->query(
                "SELECT a.client_name,
                        MONTHNAME(a.audit_date) AS audit_month,
                        SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END) AS yes_weight,
                        SUM(d.weightage) AS total_weight
                 FROM alert_normal_audit_details d
                 JOIN alert_normal_audit a ON a.normal_audit_id = d.normal_audit_id
                 WHERE a.audit_date IS NOT NULL AND (a.audit_template_id = ? OR a.audit_name = ?)
                 GROUP BY a.client_name, audit_month",
                [$audit_template_id, $audit_name]
            );
            $audit_rows = $q1n->getResultArray();
        }
        if (empty($audit_rows)) {
            // HSE fallback with accurate YES% across three statuses excluding NA
            $q1h = $db->query(
                "SELECT a.client_name, a.region, a.audit_date, d.client_leased, d.inplant, d.fm_leased
                 FROM alert_hse_audit_details d
                 JOIN alert_hse_audit_master a ON a.hse_audit_id = d.hse_audit_id
                 WHERE a.audit_date IS NOT NULL AND (a.audit_template_id = ? OR a.audit_name = ?)",
                [$audit_template_id, $audit_name]
            );
            $rows = $q1h->getResultArray();
            $agg = [];
            foreach ($rows as $r) {
                $month = date('F', strtotime($r['audit_date']));
                $key = $r['client_name'] . '|' . $month;
                if (!isset($agg[$key])) {
                    $agg[$key] = ['yes' => 0, 'total' => 0, 'client_name' => $r['client_name'], 'audit_month' => $month];
                }
                foreach (['client_leased', 'inplant', 'fm_leased'] as $fld) {
                    $val = strtoupper(trim($r[$fld] ?? ''));
                    if ($val === 'YES' || $val === 'NO') {
                        $agg[$key]['total']++;
                    }
                    if ($val === 'YES') {
                        $agg[$key]['yes']++;
                    }
                }
            }
            $audit_rows = [];
            foreach ($agg as $a) {
                $audit_rows[] = [
                    'client_name' => $a['client_name'],
                    'audit_month' => $a['audit_month'],
                    'yes_weight' => $a['yes'],
                    'total_weight' => max(1, $a['total'])
                ];
            }
        }
        $audit = [];
        foreach ($audit_rows as $r) {
            $percent = 0;
            if ((float) $r['total_weight'] > 0) {
                $percent = round(max(0, min(100, ($r['yes_weight'] / $r['total_weight']) * 100)));
            }
            $audit[] = [
                'client_name' => $r['client_name'],
                'audit_month' => $r['audit_month'],
                'yes' => $percent,
            ];
        }
        $data['audit'] = $audit;

        // Region by month table and region totals for chart (replacing zone)
        $zone_rows = [];
        $q2 = $db->query(
            "SELECT a.region,
                    MONTHNAME(a.audit_date) AS audit_month,
                    SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END) AS yes_weight,
                    SUM(d.weightage) AS total_weight
             FROM alert_final_structured_audit_details d
             JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id
             WHERE a.audit_date IS NOT NULL AND a.audit_template_id = ?
             GROUP BY a.region, audit_month",
            [$audit_template_id]
        );
        $zone_rows = $q2->getResultArray();
        if (empty($zone_rows)) {
            // Normal fallback
            $q2n = $db->query(
                "SELECT a.region,
                        MONTHNAME(a.audit_date) AS audit_month,
                        SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END) AS yes_weight,
                        SUM(d.weightage) AS total_weight
                 FROM alert_normal_audit_details d
                 JOIN alert_normal_audit a ON a.normal_audit_id = d.normal_audit_id
                 WHERE a.audit_date IS NOT NULL AND (a.audit_template_id = ? OR a.audit_name = ?)
                 GROUP BY a.region, audit_month",
                [$audit_template_id, $audit_name]
            );
            $zone_rows = $q2n->getResultArray();
        }
        if (empty($zone_rows)) {
            // HSE fallback with accurate YES% across three statuses excluding NA
            $q2h = $db->query(
                "SELECT a.region, a.audit_date, d.client_leased, d.inplant, d.fm_leased
                 FROM alert_hse_audit_details d
                 JOIN alert_hse_audit_master a ON a.hse_audit_id = d.hse_audit_id
                 WHERE a.audit_date IS NOT NULL AND (a.audit_template_id = ? OR a.audit_name = ?)",
                [$audit_template_id, $audit_name]
            );
            $rows = $q2h->getResultArray();
            $agg = [];
            foreach ($rows as $r) {
                $month = date('F', strtotime($r['audit_date']));
                $region = $r['region'] ?: 'Unknown';
                $key = $region . '|' . $month;
                if (!isset($agg[$key])) {
                    $agg[$key] = ['yes' => 0, 'total' => 0, 'region' => $region, 'audit_month' => $month];
                }
                foreach (['client_leased', 'inplant', 'fm_leased'] as $fld) {
                    $val = strtoupper(trim($r[$fld] ?? ''));
                    if ($val === 'YES' || $val === 'NO') {
                        $agg[$key]['total']++;
                    }
                    if ($val === 'YES') {
                        $agg[$key]['yes']++;
                    }
                }
            }
            $zone_rows = [];
            foreach ($agg as $a) {
                $zone_rows[] = [
                    'region' => $a['region'],
                    'audit_month' => $a['audit_month'],
                    'yes_weight' => $a['yes'],
                    'total_weight' => max(1, $a['total'])
                ];
            }
        }
        $zone = [];
        // changes on 1/10/25 by darsh: compute totals dynamically for any region label
        $zone_totals = [];
        $zone_denoms = [];
        foreach ($zone_rows as $r) {
            $percent = 0;
            if ((float) $r['total_weight'] > 0) {
                $percent = round(max(0, min(100, ($r['yes_weight'] / $r['total_weight']) * 100)));
            }
            $zone[] = [
                'zone' => $r['region'],
                'audit_month' => $r['audit_month'],
                'yes' => $percent,
            ];
            // Aggregate for pie chart
            $regionKey = $r['region'] ?? 'Unknown';
            if (!isset($zone_totals[$regionKey])) {
                $zone_totals[$regionKey] = 0;
                $zone_denoms[$regionKey] = 0;
            }
            $zone_totals[$regionKey] += (float) $r['yes_weight'];
            $zone_denoms[$regionKey] += (float) $r['total_weight'];
        }
        // Convert aggregated weights to percentages per zone
        foreach ($zone_totals as $z => $num) {
            $den = $zone_denoms[$z] ?: 0;
            $zone_totals[$z] = $den > 0 ? round(max(0, min(100, ($num / $den) * 100))) : 0;
        }
        $data['zone'] = $zone;
        $data['zone_totals'] = $zone_totals;

        // remove performed count from view; keep for internal use only
        $data['performed_count'] = null;

        // Helper to fetch region summaries parametrically
        $fetchRegion = function (string $region) use ($db, $audit_template_id) {
            $q = $db->query(
                "SELECT d.location,
                        a.client_name,
                        a.region,
                        SUM(d.weightage) AS total,
                        SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END) AS yes
                 FROM alert_final_structured_audit_details d
                 JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id
                 WHERE (d.audit_template_id = ? OR a.audit_template_id = ?) AND UPPER(a.region) = UPPER(?)
                 GROUP BY d.location, a.client_name, a.region",
                [$audit_template_id, $audit_template_id, $region]
            );
            $rows = $q->getResultArray();
            // Compute row percentage and location aggregates as weighted averages
            $locYes = [];
            $locTot = [];
            foreach ($rows as &$r) {
                $r['yes'] = (float) $r['total'] > 0 ? round(max(0, min(100, ($r['yes'] / $r['total']) * 100))) : 0;
                $loc = $r['location'];
                if (!isset($locYes[$loc])) {
                    $locYes[$loc] = 0;
                    $locTot[$loc] = 0;
                }
                $locYes[$loc] += (float) $r['yes'] * (float) $r['total'];
                $locTot[$loc] += (float) $r['total'];
            }
            $locationAgg = [];
            foreach ($locTot as $loc => $tot) {
                $locationAgg[$loc] = $tot > 0 ? round(max(0, min(100, ($locYes[$loc] / $tot)))) : 0;
            }
            return [$rows, $locationAgg];
        };

        // changes on 1/10/25 by darsh: helper reused for all regions
        // West
        list($west_rows, $west_locAgg) = $fetchRegion('West');
        $data['west_score'] = $west_rows;
        // North
        list($north_rows, $north_locAgg) = $fetchRegion('North');
        $data['north_score'] = $north_rows;
        // South
        list($south_rows, $south_locAgg) = $fetchRegion('South');
        $data['scores'] = $south_rows;
        // East
        list($east_rows, $east_locAgg) = $fetchRegion('East');
        $data['east_score'] = $east_rows;

        // Unique locations from all regions (for table headers)
        $allLocations = array_unique(array_merge(
            array_column($west_rows, 'location'),
            array_column($north_rows, 'location'),
            array_column($south_rows, 'location'),
            array_column($east_rows, 'location')
        ));
        sort($allLocations);
        $data['unique_locations'] = $allLocations;

        // Location aggregates per region
        $data['location_west'] = $west_locAgg;
        $data['location_north'] = $north_locAgg;
        $data['location_score'] = $south_locAgg; // south
        $data['location_totals'] = $east_locAgg; // east

        return view("audit_template_dashboard", $data);
    }

    public function importExcel($audit_template_id)
    {
        $db = db_connect();

        // Validate file type before processing
        $validation = $this->validateFileType('emport_excel');
        if (!$validation['valid']) {
            log_message('error', 'Excel import validation failed: ' . $validation['message']);
            return false;
        }

        if (isset($_FILES['emport_excel']) && $_FILES['emport_excel']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['emport_excel']['tmp_name'];

            if (($handle = fopen($fileTmpPath, 'r')) !== FALSE) {
                $header = fgetcsv($handle); // read header row
                $rowCount = 0;

                $currentCategory = "";

                while (($data = fgetcsv($handle)) !== FALSE) {
                    // changes on 1/10/25 by darsh: skip blank/header rows and trim values
                    // Normalize and skip empty rows
                    $data = array_map(function ($v) {
                        return is_string($v) ? trim($v) : $v;
                    }, $data);
                    if (
                        count(array_filter($data, function ($v) {
                            return $v !== null && $v !== '';
                        })) === 0
                    ) {
                        continue;
                    }
                    // Skip if this is another header row accidentally included
                    if (isset($data[0]) && stripos($data[0], 'parameter') !== false) {
                        continue;
                    }
                    $rowCount++;

                    if (!empty($data[0])) {
                        $currentCategory = $data[0]; // use Parameter as category grouping if needed
                    }

                    $insertData = [
                        "audit_template_id" => $audit_template_id,
                        "category" => $currentCategory,   // category from Parameter or group
                        "audit_question" => $data[0],           // Parameter
                        "audit_parameter" => $data[1],           // Particular
                        "risk_priority" => $data[2],           // Instruction
                        "calculated" => $data[3],           // Rating
                        "weightage" => $data[4],           // Weight
                        "audit_finding" => $data[5],           // Audit %
                        "status" => "1",                // pending
                        "default_date" => date("Y-m-d H:i:s"),
                        "update_date" => date("Y-m-d H:i:s"),
                    ];

                    $db->table("alert_audit_excel_import")->insert($insertData);
                }

                fclose($handle);

                return "Imported $rowCount rows successfully.";
            } else {
                return "Error opening the file.";
            }
        } else {
            return "No file uploaded or upload error.";
        }
    }

    public function importNormal_Excel($audit_template_id)
    {
        $db = db_connect();

        // Validate file type before processing
        // changes on 1/10/25 by darsh: allow csv for normal as well
        $validation = $this->validateFileType('emport_excel');
        if (!$validation['valid']) {
            log_message('error', 'Normal Excel import validation failed: ' . $validation['message']);
            return "Validation failed: " . $validation['message'];
        }

        if (isset($_FILES['emport_excel']) && $_FILES['emport_excel']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['emport_excel']['tmp_name'];

            try {
                $spreadsheet = IOFactory::load($fileTmpPath);
                $sheet = $spreadsheet->getActiveSheet();
                $rows = $sheet->toArray();

                $rowCount = 1;
                $errors = [];
                $batchData = [];

                foreach ($rows as $index => $data) {
                    if ($index === 0) continue; // Skip header row
                    
                    $rowCount++;
                    
                    // Normalize and skip empty rows
                    $data = array_map(function ($v) {
                        return is_string($v) ? trim($v) : $v;
                    }, $data);

                    if (count(array_filter($data, function ($v) { return $v !== null && $v !== ''; })) === 0) {
                        continue;
                    }

                    // Skip if this is another header row accidentally included
                    if (isset($data[0]) && stripos($data[0], 'Category') !== false) {
                        continue;
                    }

                    // Extract fields based on CSV structure:
                    // Category, Audit Question, Risk Priority (Target), Weightage, Audit Finding, Calculated
                    $category = $data[0] ?? '';
                    $auditQuestion = $data[1] ?? '';
                    $riskPriority = $data[2] ?? '';
                    $weightage = $data[3] ?? '';
                    $auditFinding = $data[4] ?? '';
                    $calculated = $data[5] ?? '';

                    // Validation
                    $missing = [];
                    if (empty($category)) $missing[] = "Category";
                    if (empty($auditQuestion)) $missing[] = "Audit Question";
                    if ($weightage === '') $missing[] = "Weightage";

                    if (!empty($missing)) {
                        $errors[] = "Row $rowCount is missing mandatory fields: " . implode(', ', $missing);
                        continue;
                    }

                    if (!is_numeric($weightage)) {
                        $errors[] = "Row $rowCount has invalid Weightage (must be numeric)";
                        continue;
                    }

                    $batchData[] = [
                        'audit_template_id' => $audit_template_id,
                        'location'          => '',
                        'category'          => $category,
                        'audit_question'    => $auditQuestion,
                        'risk_priority'     => $riskPriority,
                        'weightage'         => $weightage,
                        'audit_finding'     => $auditFinding,
                        'calculated'        => is_numeric($calculated) ? (int)$calculated : 0,
                        'status'            => 1, // Pending
                        'default_date'      => date('Y-m-d H:i:s'),
                        'update_date'       => date('Y-m-d H:i:s')
                    ];
                }

                if (!empty($errors)) {
                    return implode("<br>", $errors);
                }

                if (!empty($batchData)) {
                    $db->table("alert_normal_audit_excel_import")->insertBatch($batchData);
                }
                
                return "ok";
            } catch (\Exception $e) {
                return "Error parsing Excel file: " . $e->getMessage();
            }
        } else {
            return "No file uploaded or there was an upload error.";
        }
    }



    public function importHSE_Excel($audit_template_id)
    {
        try {

            $db = db_connect();

            if (!isset($_FILES['emport_excel']) || $_FILES['emport_excel']['error'] != UPLOAD_ERR_OK) {
                return ['status' => false, 'message' => 'File upload error'];
            }

            $file = $_FILES['emport_excel']['tmp_name'];

            if (($handle = fopen($file, 'r')) === false) {
                return ['status' => false, 'message' => 'Unable to open file'];
            }

            set_time_limit(0);
            ini_set('memory_limit', '1024M');

            fgetcsv($handle, 0, ",", '"', "\\");

            $batch = [];
            $batchSize = 500;

            $insert = 0;
            $skip = 0;

            $now = date('Y-m-d H:i:s');

            $db->transStart();

            while (($row = fgetcsv($handle, 0, ",", '"', "\\")) !== false) {

                $row = array_map(fn($v) => trim((string) $v), $row);

                // Skip full empty row
                if (count(array_filter($row)) == 0) {
                    $skip++;
                    continue;
                }

                for ($i = 0; $i <= 16; $i++) {
                    $row[$i] = $row[$i] ?? "";
                }

                $site_category = $row[0];
                $sub_category = $row[1];
                $category = $row[3];
                $question = $row[4];

                // 🔥 Handle merged cells
                static $last_site = null;
                static $last_sub = null;
                static $last_cat = null;

                if (!empty($site_category)) {
                    $last_site = $site_category;
                } else {
                    $site_category = $last_site;
                }

                if (!empty($sub_category)) {
                    $last_sub = $sub_category;
                } else {
                    $sub_category = $last_sub;
                }

                if (!empty($category)) {
                    $last_cat = $category;
                } else {
                    $category = $last_cat;
                }

                // ✅ NEW VALIDATION (IMPORTANT)
                if (empty($site_category) || empty($sub_category) || empty($category)) {
                    $skip++;
                    continue;
                }

                $default_val = $row[5] ?: '';

                // helper to handle null properly
                $val = function ($v) {
                    return ($v === "" || $v === null) ? null : $v;
                };

                $capa = [
                    "findings" => [
                        "text" => "Findings ($site_category - $default_val)",
                        "yes" => "",
                        "no" => $val($row[6]),
                        "na" => ""
                    ],
                    "risk" => [
                        "text" => "Risk",
                        "yes" => "",
                        "no" => $val($row[7]),
                        "na" => ""
                    ],
                    "actions" => [
                        "text" => "Actions/Recommendation",
                        "yes" => "",
                        "no" => $val($row[8]),
                        "na" => ""
                    ],
                    "action_category" => [
                        "text" => "Action Category",
                        "yes" => "",
                        "no" => $val($row[9]),
                        "na" => ""
                    ],
                    "ua_uc" => [
                        "text" => "Action Category",
                        "yes" => "",
                        "no" => $val($row[10]),
                        "na" => ""
                    ],
                    "risk_severity" => [
                        "text" => "UA-UC",
                        "yes" => "",
                        "no" => $val($row[11]),
                        "na" => ""
                    ],
                    "risk_probability" => [
                        "text" => "Risk Severity",
                        "yes" => "",
                        "no" => $val($row[12]),
                        "na" => ""
                    ],
                    "color_code" => [
                        "text" => "Risk Probability",
                        "yes" => "",
                        "no" => $val($row[13]),
                        "na" => ""
                    ],
                    "cost_type" => [
                        "text" => "Color Code",
                        "yes" => "",
                        "no" => $val($row[14]),
                        "na" => ""
                    ],
                    "combined_risk_rating" => [
                        "text" => "Cost Type",
                        "yes" => "",
                        "no" => $val($row[15]),
                        "na" => ""
                    ]
                ];

                $batch[] = [
                    'audit_template_id' => $audit_template_id,
                    'audit_site_category_name' => $row[0],
                    'audit_sub_category_name' => $row[1],
                    'audit_category_id' => $row[2],
                    'audit_category' => $row[3],
                    'audit_question' => $row[4],
                    'audit_question_default_value' => $default_val,
                    'note' => $row[16],
                    'capa_json' => json_encode($capa),
                    'status' => 1,
                    'default_date' => $now,
                    'update_date' => $now,
                ];

                $insert++;

                if (count($batch) >= $batchSize) {
                    $db->table('alert_audit_questions')->insertBatch($batch);
                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $db->table('alert_audit_questions')->insertBatch($batch);
            }

            fclose($handle);
            $db->transComplete();

            return [
                'status' => true,
                'message' => "Imported: $insert | Skipped: $skip"
            ];

        } catch (\Throwable $e) {

            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
    public function get_form_data($id)
    {
        $responce['status'] = "0";
        $responce['message'] = "Details not found";
        if (isset($id)) {

            $responce['data'] = $this->BaseModel->find($id);
            // print_r($responce['data']);
            // exit();
            $responce['status'] = "1";
            $responce['message'] = "Details found";
        }
        echo json_encode($responce);
    }

    public function perform_audit($id, $audit_template_id = null)
    {
        helper('designation_acl');
        if (!canPerformAudit('OE')) {
            return redirect()->back()->with('error', 'Access Denied: You do not have permission to perform audits. Only Auditors can perform audits.');
        }

        $data['details'] = $this->BaseModel->find($id);
        $db = db_connect();
        $data['title'] = "";

        $data['client'] = $db->table("alert_client")->where("status ", 1)->get()->getResultArray();
        $data['region'] = $db->table("alert_hse_client_master")->select("DISTINCT(region) as region_name")->where("status !=", 2)->orderBy("region", "ASC")->get()->getResultArray();

        // Filter out locations where audit is already performed
        $allLocations = $db->table("alert_client")->where("status", 1)->get()->getResultArray();
        $performedLocations = $db->table("alert_final_structured_audit")
            ->select('location')
            ->where('audit_template_id', $id)
            ->get()->getResultArray();

        $performedSet = [];
        foreach ($performedLocations as $pl) {
            $performedSet[strtolower(trim($pl['location']))] = true;
        }

        $data['location'] = [];
        foreach ($allLocations as $loc) {
            if (!isset($performedSet[strtolower(trim($loc['client_name']))])) {
                $data['location'][] = $loc;
            }
        }
        // changes on 14/10/25 by darsh: Pass auditors list for Select2 dropdown
        if ($_SESSION['role'] == "Auditor") {
            $data['auditors'] = $db->table("alert_users")->where("user_id", $_SESSION['login_id'])->where("status", 1)->get()->getResultArray();
        } else {
            $data['auditors'] = $db->table("alert_users")->where("user_designation", "Auditor")->where("status", 1)->get()->getResultArray();
        }

        $excel_details = $db->table("alert_audit_excel_import")->where("audit_template_id", $id)->get()->getResultArray();
        // Format weightage for display (append %)
        $data['excel_details'] = $this->formatWeightageDisplay($excel_details);

        // Prefill support: fetch the most recent completed audit for this template
        if (isset($audit_template_id)) {
            $lastAudit = $db->table('alert_final_structured_audit')
                ->where('audit_template_id', $audit_template_id)
                ->orderBy('structured_audit_id', 'DESC')
                ->limit(1)
                ->get()
                ->getRowArray();

            if ($lastAudit) {
                // Overlay header defaults from last audit onto template details
                $overlay = [
                    'auditor_name' => $lastAudit['auditor_name'] ?? ($data['details']['auditor_name'] ?? ''),
                    'auditee_name' => $lastAudit['auditee_name'] ?? ($data['details']['auditee_name'] ?? ''),
                    'region' => $lastAudit['region'] ?? ($data['details']['region'] ?? ''),
                    // changes on 30/09/25 by darsh: prefill Client/Site Location field
                    'location' => $lastAudit['location'] ?? ($data['details']['location'] ?? ''),
                ];
                $data['details'] = array_merge($data['details'] ?? [], $overlay);

                // Load last audit's detail rows and index by audit_question for easy lookup in the view
                $auditDetailsRaw = $db->table('alert_final_structured_audit_details')
                    ->where('structured_audit_id', $lastAudit['structured_audit_id'])
                    ->get()
                    ->getResultArray();
                if (!empty($auditDetailsRaw)) {
                    // changes on 8/10/25 by darsh: index by normalized audit_question for robust prefill
                    $data['audit_details'] = [];
                    foreach ($auditDetailsRaw as $detailRow) {
                        $key = strtolower(trim($detailRow['audit_question']));
                        $data['audit_details'][$key] = $detailRow;
                    }
                }
            } else {
                // changes on 30/09/25 by darsh: fallback by audit_name if template id not provided
                $fallbackAudit = $db->table('alert_final_structured_audit')
                    ->where('audit_name', $data['details']['audit_name'] ?? '')
                    ->orderBy('audit_date', 'DESC')
                    ->limit(1)
                    ->get()
                    ->getRowArray();
                if ($fallbackAudit) {
                    $overlay = [
                        'auditor_name' => $fallbackAudit['auditor_name'] ?? ($data['details']['auditor_name'] ?? ''),
                        'auditee_name' => $fallbackAudit['auditee_name'] ?? ($data['details']['auditee_name'] ?? ''),
                        'region' => $fallbackAudit['region'] ?? ($data['details']['region'] ?? ''),
                        'location' => $fallbackAudit['location'] ?? ($data['details']['location'] ?? ''),
                    ];
                    $data['details'] = array_merge($data['details'] ?? [], $overlay);
                }
            }
            // changes on 30/09/25 by darsh: ensure view gets clean defaults for site details when no last audit
            if (!isset($data['details']['location'])) {
                $data['details']['location'] = $data['details']['location'] ?? '';
            }
            if (!isset($data['details']['auditee_name'])) {
                $data['details']['auditee_name'] = $data['details']['auditee_name'] ?? '';
            }
            if (!isset($data['details']['region'])) {
                $data['details']['region'] = $data['details']['region'] ?? '';
            }
        }
        // changes on 30/09/25 by darsh: ensure region list is available to prefill dropdown
        if (!isset($data['region']) || empty($data['region'])) {
            $data['region'] = $db->table("alert_region")->get()->getResultArray();
        }

        $data['action'] = base_url("Masters/Audit_template/save_perform_audit/" . $id);
        return view("Audit/perform_audit_view", $data);
    }

    function save_perform_audit($audit_template_id)
    {
        helper('designation_acl');
        $request = service('request');
        $postData = $request->getVar();
        $db = db_connect();

        // Require next_date before saving (structured/OE)
        if (!isset($postData['next_date']) || trim($postData['next_date']) === '') {
            return redirect()->back()->with('error', 'Please select Next Audit Date.');
        }

        // NEW: Require location (extra safety)
        $location = isset($postData['location']) ? trim($postData['location']) : '';
        if ($location === '') {
            return redirect()->back()->with('error', 'Please select Client / Site Location.');
        }

        // 🔴 NEW: Prevent duplicate audit for same template + location
        $exists = $db->table('alert_final_structured_audit')
            ->where('audit_template_id', $audit_template_id)
            ->where('LOWER(TRIM(location)) =', strtolower($location))
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->back()->with(
                'error',
                'Audit for this location has already been performed for this template. You cannot perform it again.'
            );
        }
        // ✅ If no duplicate, continue normal save

        $alert_final_structured_audit = $db->table("alert_final_structured_audit");

        $alert_final_structured_audit_data['audit_no'] = $postData['audit_no'];
        $alert_final_structured_audit_data['audit_name'] = $postData['audit_name'];
        $alert_final_structured_audit_data['auditor_name'] = $postData['auditor_name'];
        $alert_final_structured_audit_data['auditee_name'] = $postData['auditee_name'];
        $alert_final_structured_audit_data['audit_date'] = $postData['audit_date'];
        $alert_final_structured_audit_data['next_date'] = $postData['next_date'];
        // persist client_name if provided (fallback to location)
        $alert_final_structured_audit_data['client_name'] = $postData['client_name'] ?? ($postData['location'] ?? '');
        $normalizedRegion = isset($postData['region']) ? ucwords(strtolower($postData['region'])) : '';
        // $alert_final_structured_audit_data['region']            = $normalizedRegion;
        $alert_final_structured_audit_data['location'] = $location;
        // region already normalized above; but keep original line if needed
        // $alert_final_structured_audit_data['region']            = $postData['region'];
        $alert_final_structured_audit_data['audit_score'] = $postData['score'];
        $alert_final_structured_audit_data['audit_template_id'] = $audit_template_id;
        $alert_final_structured_audit_data['audit_by_user_id'] = $_SESSION['login_id'];

        $clientRow = $db->table('alert_client')
            ->where('client_name', $location)
            ->where('status', 1)
            ->get()
            ->getRowArray();

        $alert_final_structured_audit_data['cluster_name'] =
            $clientRow['cluster'] ?? '';

        $alert_final_structured_audit_data['client_manager_name'] =
            $clientRow['account_manager'] ?? '';

        $alert_final_structured_audit_data['region'] =
            $clientRow['region'] ?? '';
        $alert_final_structured_audit_data['audit_note'] = $postData['audit_note'] ?? '';

        // Snapshot fields for historical audit preservation
        $alert_final_structured_audit_data['snapshot_site_id'] = $clientRow['client_id'] ?? null;
        $alert_final_structured_audit_data['snapshot_account_manager_name'] = $clientRow['account_manager'] ?? ($alert_final_structured_audit_data['client_manager_name'] ?? '');
        $alert_final_structured_audit_data['snapshot_cluster_manager_name'] = $clientRow['cluster'] ?? ($alert_final_structured_audit_data['cluster_name'] ?? '');
        $alert_final_structured_audit_data['snapshot_created_by_user_id'] = $_SESSION['user_id'] ?? null;
        $alert_final_structured_audit_data['snapshot_created_by_user_name'] = $_SESSION['user_name'] ?? null;
        $alert_final_structured_audit_data['snapshot_created_at'] = date('Y-m-d H:i:s');

        $alert_final_structured_audit->insert($alert_final_structured_audit_data);

        $inId = $db->insertID();
        // SEND MAIL
        //uncommetn for mail
        //$this->sendAuditEmail($inId);

        $alert_final_structured_audit_details_table = $db->table("alert_final_structured_audit_details");

        // File upload logic start
        $allowed = array('mp4', 'jpg', 'png', 'jpeg', 'gif', 'pdf', 'xls', 'xlsx');

        // Loop through each file in the array
        for ($i = 0; $i < count($postData['audit_question']); $i++) {
            // Determine selection first; if nothing selected, skip saving this detail row entirely
            $audit_finding = isset($postData['check_' . $i]) ? $postData['check_' . $i] : "NA";
            if ($audit_finding === null || $audit_finding === '') {
                continue; // do not insert a row when no Yes/No selected
            }

            $alert_final_structured_audit_details['structured_audit_id'] = $inId;
            $alert_final_structured_audit_details['audit_template_id'] = $audit_template_id;
            $alert_final_structured_audit_details['location'] = $postData['location_list'][$i];
            $alert_final_structured_audit_details['category'] = $postData['category'][$i];
            $alert_final_structured_audit_details['audit_question'] = $postData['audit_question'][$i];
            $alert_final_structured_audit_details['audit_parameter'] = $postData['audit_parameter'][$i];
            $alert_final_structured_audit_details['risk_priority'] = $postData['risk_priority'][$i];
            // Save raw numeric weightage to DB (strip % if accidentally present in UI)
            $rawWeight = $postData['weightage'][$i] ?? '';
            $rawWeight = is_string($rawWeight) ? rtrim($rawWeight, '%') : $rawWeight;
            $alert_final_structured_audit_details['weightage'] = $rawWeight;
            $alert_final_structured_audit_details['audit_remark'] = $postData['remark'][$i];
            // persist Sr No for stable re-audit mapping
            $alert_final_structured_audit_details['calculated'] = isset($postData['sr_no'][$i]) ? (int) $postData['sr_no'][$i] : ($i + 1);
            if ($audit_finding == 'NA')
                $alert_final_structured_audit_details['audit_finding'] = "NA";
            else
                $alert_final_structured_audit_details['audit_finding'] = ($audit_finding == '1') ? 'YES' : 'NO';

            $alert_final_structured_audit_details['audit_attachment'] = $postData['existing_attachment'][$i] ?? "";

            // Check if there's an error with the current file
            if (isset($_FILES['audit_attachment']['name']))
                if ($_FILES['audit_attachment']['error'][$i] == UPLOAD_ERR_OK) {
                    // Get the file extension and size
                    $ext = pathinfo($_FILES['audit_attachment']['name'][$i], PATHINFO_EXTENSION);
                    $fileSize = $_FILES['audit_attachment']['size'][$i];

                    // Check if the file type is allowed and the size is within the limit
                    if (in_array($ext, $allowed)) {
                        $folder = "uploads/Audit/" . $inId . "/";
                        $url = "";
                        $_FILES['final']['name'] = $_FILES['audit_attachment']['name'][$i];
                        $_FILES['final']['type'] = $_FILES['audit_attachment']['type'][$i];
                        $_FILES['final']['tmp_name'] = $_FILES['audit_attachment']['tmp_name'][$i];
                        $_FILES['final']['error'] = $_FILES['audit_attachment']['error'][$i];
                        $_FILES['final']['size'] = $_FILES['audit_attachment']['size'][$i];
                        // Assuming you have a method for uploading the file, handle the upload
                        $url = $this->uploadImage($folder, "final"); // Pass the index to handle multiple files
                        $alert_final_structured_audit_details['audit_attachment'] = $url; // override only when new upload
                    }
                }
            $alert_final_structured_audit_details_table->insert($alert_final_structured_audit_details);
            $detailInsertId = $db->insertID();

            // Log NC action history if finding is NO
            if ($alert_final_structured_audit_details['audit_finding'] === 'NO') {
                logNcActionHistory(
                    'OE',
                    (int)$detailInsertId,
                    'CREATED',
                    null,
                    '0',
                    $alert_final_structured_audit_details['audit_remark'] ?? 'NC created during audit performance',
                    $alert_final_structured_audit_details['audit_attachment'] ?? null,
                    (int)$inId,
                    $location
                );
            }

        } //for loop[]

        return redirect()->to(base_url("/Masters/Audit_final_structure"));
    }

    public function sendAuditEmail($structured_audit_id)
    {
        $db = \Config\Database::connect();

        /* ---------------------------------------------------------
           1. FETCH STRUCTURED AUDIT MASTER
        --------------------------------------------------------- */
        $audit = $db->table('alert_final_structured_audit')
            ->where('structured_audit_id', $structured_audit_id)
            ->get()->getRowArray();

        if (!$audit) {
            return false;
        }

        $audit_template_id = $audit['audit_template_id'];
        $client_name = $audit['client_name'];


        /* ---------------------------------------------------------
           2. FETCH NC COUNT (NO + NA)
        --------------------------------------------------------- */
        $details = $db->table('alert_final_structured_audit_details')
            ->where('structured_audit_id', $structured_audit_id)
            ->get()->getResultArray();

        $nc_count = 0;
        foreach ($details as $d) {
            if ($d['audit_finding'] === 'NO' || $d['audit_finding'] === 'NA') {
                $nc_count++;
            }
        }


        /* ---------------------------------------------------------
           3. GET CLUSTER MANAGER EMAIL
              SELECT email from alert_client
              JOIN alert_users to get manager's email
        --------------------------------------------------------- */
        $client = $db->table("alert_client")
            ->where("client_name", $client_name)
            ->get()->getRowArray();

        if (!$client) {
            log_message("error", "Client not found: " . $client_name);
            return false;
        }

        $cluster_name = $client['cluster'];

        // join alert_users for cluster manager email
        $clusterManager = $db->table("alert_users")
            ->where("user_cluster", $cluster_name)
            ->where("user_designation", "Cluster Manager")
            ->where("status", 1)
            ->get()->getRowArray();

        if (!$clusterManager) {
            log_message("error", "Cluster manager not found for cluster: " . $cluster_name);
            return false;
        }

        $cluster_manager_email = $clusterManager['user_email'];
        $cluster_manager_name = $clusterManager['user_name'];


        /* ---------------------------------------------------------
           4. AUDITOR (CC)
        --------------------------------------------------------- */
        $auditor_email = $_SESSION['user_email'] ?? null;
        $auditor_name = $_SESSION['user_name'] ?? "Auditor";


        /* ---------------------------------------------------------
           5. PREPARE TEMPLATE DATA
        --------------------------------------------------------- */
        $emailData = [
            "manager_name" => $cluster_manager_name,
            "audit_type" => $audit["audit_name"] ?? "",
            "cluster_name" => $cluster_name,
            "audit_id" => $structured_audit_id,
            "client_name" => $client_name,
            "region_name" => $audit["region"],
            "audit_details" => [
                "auditor_name" => $auditor_name,
                "audit_date" => $audit["audit_date"],
                "location" => $audit["location"],
                "nc_count" => $nc_count
            ]
        ];


        /* ---------------------------------------------------------
           6. LOAD HTML TEMPLATE
        --------------------------------------------------------- */
        $htmlMessage = view("emails/new_audit_created", $emailData);


        /* ---------------------------------------------------------
           7. SEND EMAIL
        --------------------------------------------------------- */
        try {
            helper('email_service');
            $subject = "New Audit Performed - Audit #" . $structured_audit_id;
            $ccArray = !empty($auditor_email) ? [$auditor_email] : [];
            
            if (!sendSystemEmail($cluster_manager_email, $subject, $htmlMessage, [], $ccArray)) {
                return false;
            }

            return true;

        } catch (\Exception $e) {
            log_message("error", "EMAIL EXCEPTION: " . $e->getMessage());
            return false;
        }
    }


    function import_perform_audit()
    {

        $db = db_connect();

        if (isset($_FILES['file1']) && $_FILES['file1']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['file1']['tmp_name'];

            if (($handle = fopen($fileTmpPath, 'r')) !== FALSE) {
                $temp = fgetcsv($handle);

                $pose = 0;
                if (!isset($temp['structured_audit_id']))
                    $pose = -1;

                $tempLocation = "";
                while (($data = fgetcsv($handle)) !== FALSE) {

                    if ($data[1 + $pose] != "")
                        $tempLocation = $data[1 + $pose];
                    else
                        $data[1 + $pose] = $tempLocation;


                    $studentData = array(
                        'audit_template_id' => $data[0],
                        'audit_no' => $data[1],
                        'audit_name' => $data[2],
                        'reaudit' => $data[3],
                        'auditor_name' => $data[4],
                        'auditee_name' => $data[5],
                        'region' => $data[6],
                        'audit_date' => $data[7],
                        'completion_date' => $data[8],
                        'next_date' => $data[9],
                        'report_date' => $data[10],
                        'client_name' => $data[11],
                        'location' => $data[12],
                        'audit_score' => $data[13],
                        'audit_revision_no' => $data[14],
                        'final_remark' => $data[15],
                        'audit_by_user_id' => $_SESSION['login_id'],

                    );

                    if ($pose == -1)
                        $db->table("alert_final_structured_audit")->insert($studentData);
                    else
                        $db->table("alert_final_structured_audit")->update($studentData, ["structured_audit_id " => $data[0]]);

                }
                fclose($handle);
            } else {
                return "Error opening the file.";
            }
        } else {
            return "No file uploaded or there was an upload error.";
        }

        if (isset($_FILES['file2']) && $_FILES['file2']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['file2']['tmp_name'];

            if (($handle = fopen($fileTmpPath, 'r')) !== FALSE) {
                $temp = fgetcsv($handle);

                $pose = 0;
                if (!isset($temp['structured_audit_id']))
                    $pose = -1;

                $tempLocation = "";
                while (($data = fgetcsv($handle)) !== FALSE) {

                    if ($data[1 + $pose] != "")
                        $tempLocation = $data[1 + $pose];
                    else
                        $data[1 + $pose] = $tempLocation;


                    $studentData = array(
                        'audit_template_id' => $data[0],
                        'structured_audit_id' => $data[1],
                        'location' => $data[2],
                        'category' => $data[3],
                        'audit_question' => $data[4],
                        'audit_parameter' => $data[5],
                        'risk_priority' => $data[6],
                        'weightage' => $data[7],
                        'audit_finding' => $data[8],
                        'calculated' => $data[9],
                        'audit_remark' => $data[10]
                    );

                    if ($pose == -1)
                        $db->table("alert_final_structured_audit_details")->insert($studentData);
                    else
                        $db->table("alert_final_structured_audit_details")->update($studentData, ["audit_details_id " => $data[0]]);

                }
                fclose($handle);
            } else {
                return "Error opening the Second file.";
            }
        } else {
            return "No Second file uploaded or there was an upload error.";
        }
        return redirect()->to(base_url("/Masters/Audit_final_structure"));
    }

    public function normal_perform_audit($id)
    {
        helper('designation_acl');
        if (!canPerformAudit('Normal')) {
            return redirect()->back()->with('error', 'Access Denied: You do not have permission to perform audits. Only Auditors can perform audits.');
        }

        $data['details'] = $this->BaseModel->find($id);
        $db = db_connect();
        $data['title'] = "New Audit";
        // changes on 14/10/25 by darsh: Pass auditors list for Select2 dropdown
        $data['auditors'] = $db->table("alert_users")->where("status", 1)->get()->getResultArray();
        $data['client'] = $db->table("alert_client")->where("status ", 1)->get()->getResultArray();
        $data['region'] = $db->table("alert_hse_client_master")->select("DISTINCT(region) as region_name")->where("status !=", 2)->orderBy("region", "ASC")->get()->getResultArray();

        // Get max ID for Audit No formatting
        $maxQuery = $db->query('SELECT MAX(normal_audit_id) as max_id FROM alert_normal_audit')->getRowArray();
        $data['next_audit_id'] = (int)($maxQuery['max_id'] ?? 0) + 1;

        // Filter out locations where audit is already performed
        $allLocations = $db->table("alert_location_master")->where("status != ", 2)->get()->getResultArray();
        $performedLocations = $db->table("alert_normal_audit")
            ->select('location')
            ->where('audit_template_id', $id)
            ->get()->getResultArray();

        $performedSet = [];
        foreach ($performedLocations as $pl) {
            $performedSet[strtolower(trim($pl['location']))] = true;
        }

        $data['location'] = [];
        foreach ($allLocations as $loc) {
            if (!isset($performedSet[strtolower(trim($loc['location_name']))])) {
                $clientName = $loc['client_name'] ?? ($loc['location_name'] ?? '');
                
                // alert_location_master uses 'cluster_name'
                $locCluster = $loc['cluster_name'] ?? ($loc['cluster'] ?? '');
                
                if (empty($locCluster) || empty($loc['account_manager'])) {
                    $clientRow = $db->table('alert_client')
                        ->select('cluster, account_manager')
                        ->where('client_name', $clientName)
                        ->get()
                        ->getRowArray();
                        
                    if (!$clientRow) {
                        $clientRow = $db->table('alert_hse_client_master')
                            ->select('cluster, account_manager')
                            ->where('client_name', $clientName)
                            ->get()
                            ->getRowArray();
                    }
                    
                    if ($clientRow) {
                        $loc['cluster'] = !empty($clientRow['cluster']) ? $clientRow['cluster'] : $locCluster;
                        $loc['account_manager'] = !empty($clientRow['account_manager']) ? $clientRow['account_manager'] : ($loc['account_manager'] ?? '');
                    } else {
                        $loc['cluster'] = $locCluster;
                    }
                } else {
                    $loc['cluster'] = $locCluster;
                }
                
                $data['location'][] = $loc;
            }
        }

        $excel_details = $db->table("alert_normal_audit_excel_import")->where("audit_template_id", $id)->get()->getResultArray();
        // Format weightage for display (append %)
        $data['excel_details'] = $this->formatWeightageDisplay($excel_details);

        // changes on 5/11/25 by darsh: Perform (Normal) is a FRESH audit - do NOT prefill from any previous audit
        $data['details']['auditor_name'] = $data['details']['auditor_name'] ?? '';
        $data['details']['auditee_name'] = $data['details']['auditee_name'] ?? '';
        $data['details']['region'] = $data['details']['region'] ?? '';
        $data['details']['location'] = $data['details']['location'] ?? '';
        $data['details']['client_name'] = $data['details']['client_name'] ?? '';

        $data['action'] = base_url("Masters/Audit_template/normal_save_perform_audit/" . $id);
        return view("Audit/perform_audit_view", $data);
    }
    function normal_save_perform_audit($audit_template_id = null)
    {
        $request = service('request');
        $postData = $request->getVar();
        $db = db_connect();

        // Require report_date before saving (Normal)
        if (!isset($postData['report_date']) || trim($postData['report_date']) === '') {
            //return redirect()->back()->with('error', 'Please select Report Date.');
        }
        // Require next_date before saving (Normal)
        if (!isset($postData['next_date']) || trim($postData['next_date']) === '') {
            //return redirect()->back()->with('error', 'Please select Next Audit Date.');
        }

        // Require location
        $location = isset($postData['location']) ? trim($postData['location']) : '';
        if ($location === '') {
            //return redirect()->back()->with('error', 'Please select Client / Site Location.');
        }

        $resolvedTemplateId = $audit_template_id ?? ($postData['audit_template_id'] ?? null);

        // Prevent duplicate audit for same template + location
        $exists = $db->table('alert_normal_audit')
            ->where('audit_template_id', $resolvedTemplateId)
            ->where('LOWER(TRIM(location)) =', strtolower($location))
            ->countAllResults();

        if ($exists > 0) {
            // return redirect()->back()->with(
            //     'error',
            //     'Audit for this location has already been performed for this template. You cannot perform it again.'
            // );
        }

        $alert_normal_audit = $db->table("alert_normal_audit");

        $alert_normal_audit_data['audit_no'] = $postData['audit_no'];
        $alert_normal_audit_data['audit_name'] = $postData['audit_name'];
        $alert_normal_audit_data['auditor_name'] = $postData['auditor_name'];
        $alert_normal_audit_data['auditee_name'] = $postData['auditee_name'];
        $alert_normal_audit_data['audit_date'] = $postData['audit_date'];
        $alert_normal_audit_data['next_date'] = $postData['next_date'];
        $alert_normal_audit_data['report_date'] = $postData['report_date'] ?? '';
        try {
            $db->query('SELECT client_id FROM alert_normal_audit LIMIT 1');
            $alert_normal_audit_data['client_id'] = $postData['client_id'] ?? '';
        } catch (\Throwable $e) {
            // skip setting client_id to avoid SQL error when column is absent
        }
        $alert_normal_audit_data['client_name'] = $postData['client_name'] ?? ($postData['location'] ?? '');
        $normalizedRegionN = isset($postData['region']) ? ucwords(strtolower($postData['region'])) : '';
        $alert_normal_audit_data['region'] = $normalizedRegionN;
        $alert_normal_audit_data['location'] = $location;
        $alert_normal_audit_data['zone'] = $normalizedRegionN;
        $alert_normal_audit_data['audit_score'] = $postData['score'] ?? null;
        
        // Retrieve cluster and account manager to save with audit (for NC Tracker routing)
        $clientRow = $db->table('alert_location_master')
            ->select('cluster_name as cluster, account_manager')
            ->where('location_name', $alert_normal_audit_data['client_name'])
            ->where('status !=', 2)
            ->get()
            ->getRowArray();

        // Fallback to alert_client if not found in alert_location_master
        if (!$clientRow) {
            $clientRow = $db->table('alert_client')
                ->select('cluster, account_manager')
                ->where('client_name', $alert_normal_audit_data['client_name'])
                ->get()
                ->getRowArray();
        }

        // Fallback to alert_hse_client_master if not found in either
        if (!$clientRow) {
            $clientRow = $db->table('alert_hse_client_master')
                ->select('cluster, account_manager')
                ->where('client_name', $alert_normal_audit_data['client_name'])
                ->get()
                ->getRowArray();
        }

        $alert_normal_audit_data['cluster_name'] = $clientRow['cluster'] ?? '';
        $alert_normal_audit_data['client_manager_name'] = $clientRow['account_manager'] ?? '';
        
        $alert_normal_audit_data['audit_template_id'] = $resolvedTemplateId;
        $alert_normal_audit_data['audit_by_user_id'] = $_SESSION['login_id'];

        // Snapshot fields for historical audit preservation
        $alert_normal_audit_data['snapshot_site_id'] = $clientRow['client_id'] ?? null;
        $alert_normal_audit_data['snapshot_account_manager_name'] = $clientRow['account_manager'] ?? ($alert_normal_audit_data['client_manager_name'] ?? '');
        $alert_normal_audit_data['snapshot_cluster_manager_name'] = $clientRow['cluster'] ?? ($alert_normal_audit_data['cluster_name'] ?? '');
        $alert_normal_audit_data['snapshot_created_by_user_id'] = $_SESSION['user_id'] ?? null;
        $alert_normal_audit_data['snapshot_created_by_user_name'] = $_SESSION['user_name'] ?? null;
        $alert_normal_audit_data['snapshot_created_at'] = date('Y-m-d H:i:s');

        $alert_normal_audit->insert($alert_normal_audit_data);

        $inId = $db->insertID();
        
        // Update audit_no to use the actual auto-increment ID
        $newAuditNo = ($postData['audit_name'] ?? 'Normal') . "-" . date("Y-m-d") . "-" . $inId;
        $db->table("alert_normal_audit")->where('normal_audit_id', $inId)->update(['audit_no' => $newAuditNo]);

        $alert_normal_audit_details_table = $db->table("alert_normal_audit_details");

        // File upload logic start
        $allowed = array('mp4', 'jpg', 'png', 'jpeg', 'gif', 'pdf', 'xls', 'xlsx');
        for ($i = 0; $i < count($postData['audit_question']); $i++) {
            // Determine selection first; if nothing selected, skip saving this detail row entirely
            $audit_finding = isset($postData['check_' . $i]) ? $postData['check_' . $i] : "NA";
            if ($audit_finding === null || $audit_finding === '') {
                continue; // do not insert a row when no Yes/No selected
            }

            $alert_normal_audit_details['normal_audit_id'] = $inId;
            $alert_normal_audit_details['audit_template_id'] = $resolvedTemplateId;
            $alert_normal_audit_details['location'] = $postData['location_list'][$i];
            $alert_normal_audit_details['category'] = $postData['category'][$i];
            $alert_normal_audit_details['audit_question'] = $postData['audit_question'][$i];
            $alert_normal_audit_details['risk_priority'] = $postData['risk_priority'][$i];
            // Save raw numeric weightage to DB (strip % if present)
            $rawWeight = $postData['weightage'][$i] ?? '';
            $rawWeight = is_string($rawWeight) ? rtrim($rawWeight, '%') : $rawWeight;
            $alert_normal_audit_details['weightage'] = $rawWeight;
            $alert_normal_audit_details['audit_remark'] = $postData['remark'][$i];
            $alert_normal_audit_details['calculated'] = isset($postData['sr_no'][$i]) ? (int) $postData['sr_no'][$i] : ($i + 1);
            
            if ($audit_finding === 'NA' || $audit_finding === 'na') {
                $alert_normal_audit_details['audit_finding'] = 'NA';
            } else {
                $alert_normal_audit_details['audit_finding'] = ($audit_finding == '1') ? 'YES' : 'NO';
            }
            
            $alert_normal_audit_details['audit_attachment'] = $postData['existing_attachment'][$i] ?? "";

            // Check if there's an error with the current file
            if (
                isset($_FILES['audit_attachment']['name']) &&
                isset($_FILES['audit_attachment']['error'][$i]) &&
                $_FILES['audit_attachment']['error'][$i] == UPLOAD_ERR_OK
            ) {
                // Get the file extension and size
                $ext = pathinfo($_FILES['audit_attachment']['name'][$i], PATHINFO_EXTENSION);
                
                // Check if the file type is allowed
                if (in_array(strtolower($ext), $allowed)) {
                    $folder = "uploads/Audit/" . $inId . "/";
                    $url = "";
                    $_FILES['final']['name'] = $_FILES['audit_attachment']['name'][$i];
                    $_FILES['final']['type'] = $_FILES['audit_attachment']['type'][$i];
                    $_FILES['final']['tmp_name'] = $_FILES['audit_attachment']['tmp_name'][$i];
                    $_FILES['final']['error'] = $_FILES['audit_attachment']['error'][$i];
                    $_FILES['final']['size'] = $_FILES['audit_attachment']['size'][$i];
                    $url = $this->uploadImage($folder, "final"); // Pass the index to handle multiple files
                    $alert_normal_audit_details['audit_attachment'] = $url; // override only if new upload
                }
            }
            $alert_normal_audit_details_table->insert($alert_normal_audit_details);
        }//for loop[]
        
        if (isset($inId) && isset($postData['auditor_name'])) {
            $auditor_email = $db->table("alert_users")
                ->select('user_email')
                ->where('user_name', $postData['auditor_name'])
                ->get()
                ->getResultArray();
            $afs = new Audit_final_structure();
            $Normaldf = $afs->auditViewDetailsPdf($inId, 1, 'normal');

            $fileName = 'Audit-Normal-' . $inId . '.pdf';

            $options = new Options();
            $options->set('defaultFont', 'Courier');
            $options->set('isRemoteEnabled', true);
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($Normaldf);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $output = $dompdf->output();
            $filePath = WRITEPATH . 'uploads/' . $fileName;  // Save PDF in writable/uploads/

            file_put_contents($filePath, $output);

            helper('email_service');
            $to = $auditor_email[0]['user_email'] ?? '';
            $subject = 'Perform Audit No.' . ($postData['audit_no'] ?? '');
            
            $auditorName = $postData['auditor_name'] ?? '';
            $clientName = $postData['client_name'] ?? ($postData['location'] ?? '');
            $location = $postData['location'] ?? '';
            $region = $postData['region'] ?? '';
            $message = 'Auditor Name : ' . $auditorName . ' <br> Client Name : ' . $clientName . ' <br> Site Location :  ' . $location . ' <br> Region : ' . $region;

            if (sendSystemEmail($to, $subject, $message, [$filePath])) {
                session()->setFlashdata('success', 'Audit saved and email sent successfully.');
            } else {
                session()->setFlashdata('error', 'Audit saved but failed to send email.');
            }
        }
        
        return redirect()->to(base_url("/Masters/Audit_final_structure/normal_audit_structure"));

    }

    public function checkPerformedAudit()
    {
        $db = db_connect();
        $location = $this->request->getPost('location');
        $auditName = $this->request->getPost('audit_name');

        if (!$location || !$auditName) {
            echo "NOT_EXISTS";
            return;
        }

        $isOE = $db->table('alert_final_structured_audit')
                   ->where('LOWER(TRIM(location))', strtolower(trim($location)))
                   ->where('audit_name', $auditName)
                   ->countAllResults();
        
        $isNormal = $db->table('alert_normal_audit')
                   ->where('LOWER(TRIM(location))', strtolower(trim($location)))
                   ->where('audit_name', $auditName)
                   ->countAllResults();

        if ($isOE > 0 || $isNormal > 0) {
            echo "EXISTS";
        } else {
            echo "NOT_EXISTS";
        }
    }

    function import_normal_audit()
    {

        //$data['details'] = $this->BaseModel->find();
        $db = db_connect();
        $data['title'] = "upload migration data for OE";
        //$data['client'] = $db->table("alert_client")->where("status != ", 2)->get()->getResultArray();
        // $data['region'] = $db->table("alert_region")->where("status != ", 2)->get()->getResultArray();
        //$data['excel_details'] = $db->table("alert_normal_audit_excel_import")->where("audit_template_id)->get()->getResultArray();
        $data['action'] = base_url("Masters/Audit_template/import_normal_save_perform_audit");
        return view("Audit/import_normal_audit", $data);
    }

    public function import_normal_save_perform_audit()
    {
        helper(['form']);
        $db = db_connect();

        $session = session();
        $userId = $_SESSION['login_id'];

        if (!$userId) {
            return redirect()->back()->with('error', 'Session expired. Please log in again.');
        }

        // === Validate File 1 ===
        if (!isset($_FILES['file1']) || $_FILES['file1']['error'] !== UPLOAD_ERR_OK) {
            return redirect()->back()->with('error', 'Please upload the first audit file.');
        }

        // === Validate File 2 ===
        if (!isset($_FILES['file2']) || $_FILES['file2']['error'] !== UPLOAD_ERR_OK) {
            return redirect()->back()->with('error', 'Please upload the second audit details file.');
        }

        // === Handle File 1: alert_normal_audit ===
        $file1Path = $_FILES['file1']['tmp_name'];
        if (($handle1 = fopen($file1Path, 'r')) !== false) {
            $header1 = fgetcsv($handle1);
            $pose = array_search('normal_audit_id', $header1) === false ? -1 : 0;

            while (($data = fgetcsv($handle1)) !== false) {
                //if (count($data) < 16) continue; // Basic validation

                $auditData = [
                    'audit_template_id' => $data[0],
                    'audit_no' => $data[1],
                    'audit_name' => $data[2],
                    'auditor_name' => $data[3],
                    'auditee_name' => $data[4],
                    'region' => $data[5],
                    'audit_date' => $data[6],
                    'next_date' => $data[7],
                    'report_date' => $data[8],
                    'client_name' => $data[9],
                    'location' => $data[10],
                    'zone' => $data[5],
                    'audit_score' => $data[11],
                    'audit_revision_no' => $data[12],
                    'final_remark' => $data[13],
                    'audit_by_user_id' => $userId
                ];

                // Retrieve cluster and account manager to save with audit
                $clientRow = $db->table('alert_location_master')
                    ->select('cluster_name as cluster, account_manager')
                    ->where('location_name', $auditData['client_name'])
                    ->where('status !=', 2)
                    ->get()
                    ->getRowArray();

                if (!$clientRow) {
                    $clientRow = $db->table('alert_client')
                        ->select('cluster, account_manager')
                        ->where('client_name', $auditData['client_name'])
                        ->get()
                        ->getRowArray();
                }

                if (!$clientRow) {
                    $clientRow = $db->table('alert_hse_client_master')
                        ->select('cluster, account_manager')
                        ->where('client_name', $auditData['client_name'])
                        ->get()
                        ->getRowArray();
                }

                $auditData['cluster_name'] = $clientRow['cluster'] ?? '';
                $auditData['client_manager_name'] = $clientRow['account_manager'] ?? '';

                if ($pose === -1) {
                    $db->table("alert_normal_audit")->insert($auditData);
                } else {
                    $db->table("alert_normal_audit")->update($auditData, ['normal_audit_id' => $data[0]]);
                }
            }
            fclose($handle1);
        } else {
            return redirect()->back()->with('error', 'Failed to open first file.');
        }

        // === Handle File 2: alert_normal_audit_details ===
        $file2Path = $_FILES['file2']['tmp_name'];
        if (($handle2 = fopen($file2Path, 'r')) !== false) {
            $header2 = fgetcsv($handle2);
            $pose = array_search('audit_details_id', $header2) === false ? -1 : 0;

            while (($data = fgetcsv($handle2)) !== false) {
                if (count($data) < 11)
                    continue;

                $detailData = [
                    'normal_audit_id' => $data[0],
                    'audit_template_id' => $data[1],
                    'location' => $data[2],
                    'category' => $data[3],
                    'audit_question' => $data[4],
                    'audit_parameter' => $data[5],
                    'risk_priority' => $data[6],
                    'weightage' => $data[7],
                    'audit_finding' => $data[8],
                    'calculated' => $data[9],
                    'audit_remark' => $data[10]
                ];

                if ($pose === -1) {
                    $db->table("alert_normal_audit_details")->insert($detailData);
                } else {
                    $db->table("alert_normal_audit_details")->update($detailData, ['audit_details_id' => $data[0]]);
                }
            }
            fclose($handle2);
        } else {
            return redirect()->back()->with('error', 'Failed to open second file.');
        }

        return redirect()->to(base_url('/Masters/Audit_final_structure/normal_audit_structure'))->with('success', 'Audit data imported successfully.');
    }

    public function import_HSE_save_perform_audit()
    {
        $db = db_connect();

        helper(['form']);

        // --- First CSV Import: HSE Audit Master ---
        if (isset($_FILES['file1']) && $_FILES['file1']['error'] == UPLOAD_ERR_OK) {
            $file1Path = $_FILES['file1']['tmp_name'];
            if (($handle1 = fopen($file1Path, 'r')) !== FALSE) {
                $headers1 = fgetcsv($handle1);

                while (($data = fgetcsv($handle1)) !== FALSE) {
                    // Validate required fields (e.g., audit_no)
                    if (empty($data[1]))
                        continue;

                    $auditData = [
                        'audit_template_id' => $data[0],
                        'audit_no' => $data[1],
                        'audit_name' => $data[2],
                        'auditor_name' => $data[3],
                        'auditee_name' => $data[4],
                        'client_name' => $data[5],
                        'audit_date' => $data[6],
                        'next_date' => $data[7],
                        'report_date' => $data[8],
                        'region' => $data[9],
                        'location' => $data[10],
                        'score' => $data[11],
                        'perform_audit_by' => $data[12]
                    ];

                    $db->table('alert_hse_audit_master')->insert($auditData);
                }
                fclose($handle1);
            } else {
                return redirect()->back()->with('error', 'Error opening HSE Audit Master file.');
            }
        } else {
            return redirect()->back()->with('error', 'HSE Audit Master file missing.');
        }

        // --- Second CSV Import: HSE Audit Details ---
        if (isset($_FILES['file2']) && $_FILES['file2']['error'] == UPLOAD_ERR_OK) {
            $file2Path = $_FILES['file2']['tmp_name'];
            if (($handle2 = fopen($file2Path, 'r')) !== FALSE) {
                $headers2 = fgetcsv($handle2);

                while (($data = fgetcsv($handle2)) !== FALSE) {
                    // Validate required fields
                    if (empty($data[1]))
                        continue;
                    // Get JSON field data from alert_question_audit_master
                    $question = $data[1]; // assuming audit_question is column 4
                    $qData = $db->table('alert_question_audit_master')
                        ->select('client_leased, inplant, fm_leased')
                        ->where('question_audit_id', $question)
                        ->get()
                        ->getRowArray();

                    $clientLeasedJson = isset($qData['client_leased']) ? $qData['client_leased'] : null;
                    $inplantJson = isset($qData['inplant']) ? $qData['inplant'] : null;
                    $fmLeasedJson = isset($qData['fm_leased']) ? $qData['fm_leased'] : null;

                    $detailData = [
                        'hse_audit_id' => $data[0],
                        'question_audit_id' => $data[1],
                        'question_name' => $data[2],
                        'audit_question' => $data[3],
                        'client_leased' => $data[4],
                        'inplant' => $data[5],
                        'fm_leased' => $data[6],
                        'remark' => $data[7],
                        'nc_reviewed_date' => $data[8],
                        'nc_remark' => $data[9],
                        'client_leased_json' => $clientLeasedJson,
                        'inplant_json' => $inplantJson,
                        'fm_leased_json' => $fmLeasedJson,
                    ];

                    $db->table('alert_hse_audit_details')->insert($detailData);
                }
                fclose($handle2);
            } else {
                return redirect()->back()->with('error', 'Error opening HSE Audit Details file.');
            }
        } else {
            return redirect()->back()->with('error', 'HSE Audit Details file missing.');
        }

        return redirect()->to(base_url('/Masters/Hse_audit'), 'HSE Audit imported successfully.');
    }

    public function extractJsonValues($jsonData, $status)
    {
        $result = [];
        $status = strtolower($status);

        // Extract values only if the JSON contains necessary keys
        $result['Findings'] = isset($jsonData['findings'][$status]) ? $jsonData['findings'][$status] : '';
        $result['Risk'] = isset($jsonData['risk'][$status]) ? $jsonData['risk'][$status] : '';
        $result['Actions'] = isset($jsonData['actions'][$status]) ? $jsonData['actions'][$status] : '';
        $result['Action Category'] = isset($jsonData['action_category'][$status]) ? $jsonData['action_category'][$status] : '';
        $result['UA-UC'] = isset($jsonData['ua_ca'][$status]) ? $jsonData['ua_ca'][$status] : '';
        $result['Risk Severity'] = isset($jsonData['risk_severity'][$status]) ? $jsonData['risk_severity'][$status] : '';
        $result['Risk Probability'] = isset($jsonData['risk_probability'][$status]) ? $jsonData['risk_probability'][$status] : '';
        $result['Color Code'] = isset($jsonData['color_code'][$status]) ? $jsonData['color_code'][$status] : '';
        $result['Cost Type'] = isset($jsonData['cost_type'][$status]) ? $jsonData['cost_type'][$status] : '';
        $result['Combined Risk Rating'] = isset($jsonData['combined_risk_rating'][$status]) ? $jsonData['combined_risk_rating'][$status] : '';

        return $result;
    }
    public function getJSONArray($index, $temp, $data)
    {
        return [
            "findings" => [
                "text" => $temp[2],
                "yes" => $data[2 + $index + 10],
                "no" => $data[2 + $index],
                "na" => $data[2 + $index + 20],
            ],
            "risk" => [
                "text" => $temp[3],
                "yes" => $data[3 + $index + 10],
                "no" => $data[3 + $index],
                "na" => $data[3 + $index + 20],
            ],
            "actions" => [
                "text" => $temp[4],
                "yes" => $data[4 + $index + 10],
                "no" => $data[4 + $index],
                "na" => $data[4 + $index + 20],
            ],
            "action_category" => [
                "text" => "Action Category",
                "yes" => $data[5 + $index + 10],
                "no" => $data[5 + $index],
                "na" => $data[5 + $index + 20],
            ],
            "ua_uc" => [
                "text" => $temp[5],
                "yes" => $data[6 + $index + 10],
                "no" => $data[6 + $index],
                "na" => $data[6 + $index + 20],
            ],
            "risk_severity" => [
                "text" => $temp[6],
                "yes" => $data[7 + $index + 10],
                "no" => $data[7 + $index],
                "na" => $data[7 + $index + 20],
            ],
            "risk_probability" => [
                "text" => $temp[7],
                "yes" => $data[8 + $index + 10],
                "no" => $data[8 + $index],
                "na" => $data[8 + $index + 20],
            ],
            "color_code" => [
                "text" => $temp[8],
                "yes" => $data[9 + $index + 10],
                "no" => $data[9 + $index],
                "na" => $data[9 + $index + 20],
            ],
            "cost_type" => [
                "text" => $temp[9],
                "yes" => $data[10 + $index + 10],
                "no" => $data[10 + $index],
                "na" => $data[10 + $index + 20],
            ],
            "combined_risk_rating" => [
                "text" => $temp[10],
                "yes" => $data[11 + $index + 10],
                "no" => $data[11 + $index],
                "na" => $data[11 + $index + 20],
            ],
        ];

    }
}
