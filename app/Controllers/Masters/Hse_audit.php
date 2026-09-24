<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
require APPPATH . '/ThirdParty/dompdf/autoload.inc.php';
use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;
use App\Services\HseGembaSyncService;

class Hse_audit extends BaseController
{

    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct()
    {
        // changes on 16/10/25 by darsh: Using simple ACL helper without database changes
        helper(["form", "simple_acl", "designation_acl", "hse_acl_helper"]);
        $db = null;
        $db['table'] = 'alert_hse_audit_master';
        $db['allowedFields'] = ['audit_no', 'audit_name', 'auditor_name', 'auditee_name', 'client_name', 'audit_date', 'template_date', 'region', 'location', 'score', 'perform_audit_by', 'main_category', 'sub_category', 'cluster_name', 'account_manager', 'status'];
        $db['primaryKey'] = "hse_audit_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];
        $db = db_connect();

        $tdata['title'] = "Perform HSE Audit";
        $tdata['form_id'] = "user_modal";

        $tdata['display_contents'] = [
            "hse_audit_id" => "ID",
            "audit_no" => "Audit No",
            "audit_name" => "Audit Name",
            "auditor_name" => "Auditor Name",
            "auditee_name" => "Auditee Name",
            "client_name" => "Client Name",
            "audit_date" => "Audit Date",
            "region" => "Region",
            "cluster_name" => "Cluster Name",
            "account_manager" => "Account Manager",
            "score" => "Score",
            // "perform_audit_by" => "Perform Audit By",
            "action" => "Action"
        ];

        if (isClusterManager()) {
            unset($tdata['display_contents']['action']);
        }

        $data['ajax_url'] = base_url("Masters/Hse_audit/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Hse_audit/table_ajax/all");

        // =========================
        // ACL FILTER FOR COUNTS
        // =========================
        $aclWhere = getHSEAuditACLWhere('a');

        // =========================
        // COUNT QUERY (ACL + Latest Audit Only)
        // =========================
        $countSql = "
                SELECT 
                    COUNT(*) AS total_count,
                    SUM(
                        CASE 
                            WHEN CAST(a.score AS DECIMAL(10,2)) = 100
                                AND NOT EXISTS (
                                    SELECT 1
                                    FROM alert_hse_audit_details d
                                    WHERE d.hse_audit_id = a.hse_audit_id
                                    AND UPPER(TRIM(d.finding)) = 'NO'
                                )
                            THEN 1 ELSE 0
                        END
                    ) AS no_nc_count,
                    SUM(
                        CASE 
                            WHEN CAST(a.score AS DECIMAL(10,2)) < 100
                                OR EXISTS (
                                    SELECT 1
                                    FROM alert_hse_audit_details d
                                    WHERE d.hse_audit_id = a.hse_audit_id
                                    AND UPPER(TRIM(d.finding)) = 'NO'
                                )
                            THEN 1 ELSE 0
                        END
                    ) AS found_nc_count
                FROM alert_hse_audit_master a
                INNER JOIN (
                    SELECT audit_no, MAX(hse_audit_id) AS latest_id
                    FROM alert_hse_audit_master
                    GROUP BY audit_no
                ) latest ON a.audit_no = latest.audit_no 
                        AND a.hse_audit_id = latest.latest_id
                WHERE a.status = 1
                $aclWhere
            ";

        $countRow = $db->query($countSql)->getRowArray();

        $noNcCount = (int) ($countRow['no_nc_count'] ?? 0);
        $foundNcCount = (int) ($countRow['found_nc_count'] ?? 0);
        $totalCount = (int) ($countRow['total_count'] ?? 0);

        //     $datatop = '
        // <div class="row g-5 g-xl-8 mb-5">
        //     <div class="col-md-4">
        //         <a href="' . base_url('Masters/Hse_audit/template_type_filter/no_nc') . '" class="card bg-primary hoverable mb-xl-8">
        //             <div class="card-body" style="padding: 1rem 2.25rem;">
        //                 <div class="fw-semibold text-gray-100">No NC (' . $noNcCount . ')</div>
        //             </div>
        //         </a>
        //     </div>
        //     <div class="col-md-4">
        //         <a href="' . base_url('Masters/Hse_audit/template_type_filter/found_nc') . '" class="card bg-dark hoverable mb-xl-8">
        //             <div class="card-body" style="padding: 1rem 2.25rem;">
        //                 <div class="fw-semibold text-gray-100">Found NC (' . $foundNcCount . ')</div>
        //             </div>
        //         </a>
        //     </div>
        //     <div class="col-md-4">
        //         <a href="' . base_url('Masters/Hse_audit') . '" class="card bg-warning hoverable mb-xl-8">
        //             <div class="card-body" style="padding: 1rem 2.25rem;">
        //                 <div class="fw-semibold text-white">All (' . $totalCount . ')</div>
        //             </div>
        //         </a>
        //     </div>
        // </div>';

        //$data['table'] = $datatop;
        $data['table'] = view("Layout/table-view", $tdata);

        return view("Layout/table-view-2", $data);
    }
    public function template_type_filter($nc_status)
    {
        $data = [];
        $db = db_connect();

        $tdata['title'] = "HSE audit";
        $tdata['form_id'] = "user_modal";

        $tdata['display_contents'] = [
            "hse_audit_id" => "ID",
            "audit_no" => "Audit No",
            "audit_name" => "Audit Name",
            "auditor_name" => "Auditor Name",
            "auditee_name" => "Auditee Name",
            "client_name" => "Client Name",
            "audit_date" => "Audit Date",
            "region" => "Region",
            "cluster_name" => "Cluster Name",
            "account_manager" => "Account Manager",
            "score" => "Score",
            // "perform_audit_by" => "Perform Audit By",
            "action" => "Action"
        ];

        $data['ajax_url'] = base_url("Masters/Hse_audit/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Hse_audit/table_ajax/" . $nc_status);

        // =========================
        // ACL FILTER FOR COUNTS
        // =========================
        $aclWhere = getHSEAuditACLWhere('a');

        $countSql = "
        SELECT 
            COUNT(*) AS total_count,
            SUM(
                CASE 
                    WHEN CAST(a.score AS DECIMAL(10,2)) = 100
                         AND NOT EXISTS (
                             SELECT 1
                             FROM alert_hse_audit_details d
                             WHERE d.hse_audit_id = a.hse_audit_id
                               AND UPPER(TRIM(d.finding)) = 'NO'
                         )
                    THEN 1 ELSE 0
                END
            ) AS no_nc_count,
            SUM(
                CASE 
                    WHEN CAST(a.score AS DECIMAL(10,2)) < 100
                         OR EXISTS (
                             SELECT 1
                             FROM alert_hse_audit_details d
                             WHERE d.hse_audit_id = a.hse_audit_id
                               AND UPPER(TRIM(d.finding)) = 'NO'
                         )
                    THEN 1 ELSE 0
                END
            ) AS found_nc_count
        FROM alert_hse_audit_master a
        INNER JOIN (
            SELECT audit_no, MAX(hse_audit_id) AS latest_id
            FROM alert_hse_audit_master
            GROUP BY audit_no
        ) latest ON a.audit_no = latest.audit_no 
                AND a.hse_audit_id = latest.latest_id
        WHERE a.status = 1
        $aclWhere
        ";

        $countRow = $db->query($countSql)->getRowArray();

        $noNcCount = (int) ($countRow['no_nc_count'] ?? 0);
        $foundNcCount = (int) ($countRow['found_nc_count'] ?? 0);
        $totalCount = (int) ($countRow['total_count'] ?? 0);

        $datatop = '
        <div class="row g-5 g-xl-8 mb-5">
            <div class="col-md-4">
                <a href="' . base_url('Masters/Hse_audit/template_type_filter/no_nc') . '" class="card bg-primary hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">No NC (' . $noNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Hse_audit/template_type_filter/found_nc') . '" class="card bg-dark hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-gray-100">Found NC (' . $foundNcCount . ')</div>
                    </div>
                </a>
            </div>
            <div class="col-md-4">
                <a href="' . base_url('Masters/Hse_audit') . '" class="card bg-warning hoverable mb-xl-8">
                    <div class="card-body" style="padding: 1rem 2.25rem;">
                        <div class="fw-semibold text-white">All (' . $totalCount . ')</div>
                    </div>
                </a>
            </div>
        </div>';

        $data['table'] = $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);

        return view("Layout/table-view-2", $data);
    }
    public function table_ajax($nc_status = 'all')
    {
        $db = db_connect();

        $sql = "
        SELECT 
            a.*,
            CAST(a.score AS DECIMAL(10,2)) as score_decimal,
            CASE 
                WHEN CAST(a.score AS DECIMAL(10,2)) = 100
                     AND NOT EXISTS (
                         SELECT 1
                         FROM alert_hse_audit_details d
                         WHERE d.hse_audit_id = a.hse_audit_id
                           AND UPPER(TRIM(d.finding)) = 'NO'
                     )
                THEN 'No NC'
                ELSE 'Found NC'
            END AS nc_status_label
        FROM alert_hse_audit_master a
        INNER JOIN (
            SELECT audit_no, MAX(hse_audit_id) AS latest_id
            FROM alert_hse_audit_master
            GROUP BY audit_no
        ) AS latest
        ON a.audit_no = latest.audit_no AND a.hse_audit_id = latest.latest_id
        WHERE a.status = 1
    ";

        $params = [];

        // =========================
        // NC FILTER
        // =========================
        if ($nc_status === 'no_nc') {
            $sql .= "
            AND CAST(a.score AS DECIMAL(10,2)) = 100
            AND NOT EXISTS (
                SELECT 1
                FROM alert_hse_audit_details d
                WHERE d.hse_audit_id = a.hse_audit_id
                  AND UPPER(TRIM(d.finding)) = 'NO'
            )
        ";
        } elseif ($nc_status === 'found_nc') {
            $sql .= "
            AND (
                CAST(a.score AS DECIMAL(10,2)) < 100
                OR EXISTS (
                    SELECT 1
                    FROM alert_hse_audit_details d
                    WHERE d.hse_audit_id = a.hse_audit_id
                      AND UPPER(TRIM(d.finding)) = 'NO'
                )
            )
        ";
        }

        // =========================
        // ACL FILTER
        // =========================
        $sql .= getHSEAuditACLWhere('a');

        $sql .= " ORDER BY a.hse_audit_id DESC";

        $tdata['table_data'] = $db->query($sql, $params)->getResultArray();

        foreach ($tdata['table_data'] as $key => $row) {

            $pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Hse_audit/auditNormalDetailsPdf/" . $row['hse_audit_id']) . '"\' class="btn btn-sm btn-primary text-nowrap" title="Normal & Autogrid Pdf">
            <i class="fa fa-file-pdf me-1"></i> Autogrid PDF
        </button>';

            $capa_pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Hse_audit/capa_report/" . $row['hse_audit_id']) . '"\' class="btn btn-sm btn-primary text-nowrap" title="Capa Pdf">
            <i class="fa fa-file-pdf me-1"></i> Audit CAPA PDF
        </button>';

            $excel = '<button onclick=\'window.location.href="' . base_url("Masters/Hse_audit/exportExcel/" . $row['hse_audit_id']) . '"\' class="btn btn-sm btn-success text-nowrap" title="Normal & AutoGrid Excel">
            <i class="fa fa-file-excel me-1"></i> Autogrid Excel
        </button>';

            $capa_excel = '<button onclick=\'window.location.href="' . base_url("Masters/Hse_audit/capa_report_excel/" . $row['hse_audit_id']) . '"\' class="btn btn-sm btn-success text-nowrap" title="CAPA Excel">
            <i class="fa fa-file-excel me-1"></i> Audit CAPA Excel
        </button>';

            $reaudit = '';
            if (canPerformAudit('HSE')) {
                $reaudit = '<button title="Reaudit" onclick=\'window.location.href="' . base_url("Masters/Hse_audit/hse_reaudit/" . $row['hse_audit_id']) . '"\' class="btn btn-sm btn-warning text-nowrap">
                <i class="fas fa-sync-alt me-1"></i> Reaudit
            </button>';
            }

            $view_attendance = '<button onclick=\'viewAttendanceModal(' . $row['hse_audit_id'] . ')\' class="btn btn-sm btn-info text-nowrap" title="View Attendance">
                <i class="fa fa-eye me-1"></i> Attendance
            </button>';

            $row1 = '<div class="d-flex align-items-center gap-1 text-nowrap">' . $capa_pdf . $capa_excel . $pdf . '</div>';
            $row2 = '<div class="d-flex align-items-center gap-1 text-nowrap">' . $excel . $view_attendance . $reaudit . '</div>';

            $tdata['table_data'][$key]['action'] = '<div class="d-flex flex-column gap-1 text-nowrap" style="white-space: nowrap; min-width: max-content;">' . $row1 . $row2 . '</div>';

            // score format
            $scoreToFormat = isset($row['score_decimal']) ? $row['score_decimal'] : $row['score'];

            if ($scoreToFormat !== null && is_numeric($scoreToFormat)) {
                $scoreValue = (float) $scoreToFormat;
                if ($scoreValue == (int) $scoreValue) {
                    $tdata['table_data'][$key]['score'] = (string) (int) $scoreValue;
                } else {
                    $formatted = number_format($scoreValue, 2, '.', '');
                    $formatted = preg_replace('/\.?0+$/', '', $formatted);
                    $tdata['table_data'][$key]['score'] = $formatted;
                }
            }

            unset($tdata['table_data'][$key]['score_decimal']);
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);

        return $this->response->setJSON($tdata);
    }
    // public function table_ajax($nc_status = null)
    // {
    //             $db = db_connect();

    //             // ðŸ”¹ Subquery ensures only the latest record per audit_no is fetched
    //             // changes on 8/11/25 by darsh: Cast score to DECIMAL to preserve decimal values if column is INT
    //             $sql = "
    //                 SELECT a.*, CAST(a.score AS DECIMAL(10,2)) as score_decimal
    //                 FROM alert_hse_audit_master a
    //                 INNER JOIN (
    //                     SELECT audit_no, MAX(hse_audit_id) AS latest_id
    //                     FROM alert_hse_audit_master
    //                     GROUP BY audit_no
    //                 ) AS latest
    //                 ON a.audit_no = latest.audit_no AND a.hse_audit_id = latest.latest_id
    //                 WHERE 1=1
    //             ";

    //             $params = [];

    //             // ðŸ”¹ Optional status filter
    //             if (isset($nc_status)) {
    //                 $sql .= " AND a.status = ?";
    //                 $params[] = $nc_status;
    //             }

    //             // ACL: Apply cluster-based filtering for Cluster Managers - 13/11/25

    //             // ACL FILTER
    //             if (isClusterManager()) {
    //                 // Cluster Manager -> only assigned cluster data
    //                 $userClusters = getClusterManagerAssignedClusterHSE();

    //                 if (!empty($userClusters)) {
    //                     $escapedClusters = array_map([$db, 'escape'], (array)$userClusters);

    //                     $sql .= " AND EXISTS (
    //                         SELECT 1 
    //                         FROM alert_hse_client_master c
    //                         WHERE (
    //                             LOWER(TRIM(c.location)) = LOWER(TRIM(a.client_name))
    //                             OR LOWER(TRIM(c.client_name)) = LOWER(TRIM(a.client_name))
    //                         )
    //                         AND LOWER(TRIM(c.cluster)) IN (" . implode(',', array_map(function($c) {
    //                             return "LOWER(TRIM($c))";
    //                         }, $escapedClusters)) . ")
    //                         AND c.status = 1
    //                     )";
    //                 } else {
    //                     // no assigned cluster -> show nothing
    //                     $sql .= " AND 1=0";
    //                 }

    //             } elseif (isAccountManager()) {
    //                 // Account Manager -> only own account manager data
    //                 $loginUser = getUserName(); // or session()->get('user_name')

    //                 $sql .= " AND EXISTS (
    //                     SELECT 1 
    //                     FROM alert_hse_client_master c
    //                     WHERE (
    //                         LOWER(TRIM(c.client_name)) = LOWER(TRIM(a.client_name))
    //                         OR LOWER(TRIM(c.client_name)) = LOWER(TRIM(a.client_name))
    //                     )
    //                     AND LOWER(TRIM(c.account_manager)) = " . $db->escape($loginUser) . "
    //                     AND c.status = 1
    //                 )";
    //             }

    //             $sql .= " ORDER BY a.hse_audit_id DESC";

    //             $tdata['table_data'] = $db->query($sql, $params)->getResultArray();

    //             foreach ($tdata['table_data'] as $key => $row) {
    //                 $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\'' . base_url("Masters/Hse_audit/save_details/" . $row['hse_audit_id']) . '/active\',$(this));">
    //                     <span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-unlock"></i></span>
    //                     <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                 </button>';

    //                 $deactive = '<button class="btn btn-icon btn-danger" onclick="url_call_ajax(\'' . base_url("Masters/Hse_audit/save_details/" . $row['hse_audit_id']) . '/deactive\',$(this));">
    //                     <span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-lock"></i></span>
    //                     <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                 </button>';

    //                 $delete = '<button data-ajax-url="' . base_url("Masters/Hse_audit/save_details/" . $row['hse_audit_id']) . '/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
    //                     <span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-trash"></i></span>
    //                     <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                 </button>';

    //                 $edit = '<button data-ajax-url="' . base_url("Masters/Hse_audit/get_form_data/" . $row['hse_audit_id']) . '" class="btn btn-icon btn-primary" onclick="edit_id(this,' . $row['hse_audit_id'] . ');">
    //                     <span class="indicator-label svg-icon svg-icon-3"><i class="fa fa-edit"></i></span>
    //                     <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                 </button>';

    //                 $pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Hse_audit/auditNormalDetailsPdf/" . $row['hse_audit_id']) . '"\' class="btn btn-icon btn-primary" title="Normal & Autogrid Pdf">
    //                     <span class="indicator-label svg-icon svg-icon-3"><i class="fa fa-file-pdf"></i></span>
    //                     <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                 </button>';

    //                 $capa_pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Hse_audit/capa_report/" . $row['hse_audit_id']) . '"\' class="btn btn-icon btn-primary" title="Capa Pdf">
    //                     <span class="indicator-label svg-icon svg-icon-3"><i class="fa fa-file-pdf"></i></span>
    //                     <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                 </button>';

    //                 $excel = '<button onclick=\'window.location.href="'
    //                 . base_url("Masters/Hse_audit/exportExcel/" . $row['hse_audit_id']) .
    //                 '"\' class="btn btn-icon btn-success" title="Normal & AutoGrid Excel">
    //                     <i class="fa fa-file-excel"></i>
    //                 </button>';


    //                 // ACL: Cluster Managers and Account Managers have read-only access - no reaudit button
    //                 $reaudit = '';
    //                 if (!isClusterManager() && !isAccountManager() && !isAccountManager()) {
    //                     $reaudit = '<button title="Reaudit" onclick=\'window.location.href="' . base_url("Masters/Hse_audit/hse_reaudit/" . $row['hse_audit_id']) . '"\' class="btn btn-icon btn-warning">
    //                         <span class="indicator-label svg-icon svg-icon-3"><i class="fas fa-sync-alt"></i></span>
    //                         <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                     </button>';
    //                 }

    //                 $autogrid = '<button title="AutoGrid" onclick=\'window.location.href="' . base_url("Masters/Hse_audit/hse_autogrid_pdf/" . $row['hse_audit_id']) . '"\' class="btn btn-icon btn-primary">
    //                     <span class="indicator-label svg-icon svg-icon-3"><i class="fas fa-chart-bar"></i></span>
    //                     <span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
    //                 </button>';
    //                 $autogrid_excel = '<button title="AutoGrid Excel" 
    //                     onclick=\'window.location.href="' . base_url("Masters/Hse_audit/hse_autogrid_excel/" . $row['hse_audit_id']) . '"\'
    //                     class="btn btn-icon btn-success">
    //                     <i class="fas fa-file-excel"></i>
    //                 </button>';
    //                 $capa_excel = '<button onclick=\'window.location.href="' 
    //                 . base_url("Masters/Hse_audit/capa_report_excel/" . $row['hse_audit_id']) .
    //                 '"\' class="btn btn-icon btn-success" title="CAPA Excel">
    //                     <i class="fa fa-file-excel"></i>
    //                 </button>';



    //                 if ($row['status'] == "0") {
    //                     $deactive = "";
    //                     $tdata['table_data'][$key]['tr_class'] = "bg-light-warning";
    //                 } elseif ($row['status'] == "1") {
    //                     $active = "";
    //                 } elseif ($row['status'] == "2") {
    //                     $delete = "";
    //                     $deactive = "";
    //                     $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
    //                 }

    //                 //$tdata['table_data'][$key]['action'] = $pdf . $capa_pdf . $reaudit . $autogrid;
    //                 //$tdata['table_data'][$key]['action'] = $pdf . $capa_pdf . $reaudit . $autogrid;
    //                 // changes on 20/12/25: Combined Normal & AutoGrid PDF/Excel. Removed separate AutoGrid buttons.
    //                 $tdata['table_data'][$key]['action'] = $pdf . $excel . $capa_pdf . $capa_excel . $reaudit;

    //                 // changes on 8/11/25 by darsh: Format score to show decimal values (e.g., 62.5 instead of 63)
    //                 // Use score_decimal if available (from CAST), otherwise use score
    //                 $scoreToFormat = isset($tdata['table_data'][$key]['score_decimal']) 
    //                     ? $tdata['table_data'][$key]['score_decimal'] 
    //                     : (isset($tdata['table_data'][$key]['score']) ? $tdata['table_data'][$key]['score'] : null);

    //                 if ($scoreToFormat !== null && is_numeric($scoreToFormat)) {
    //                     $scoreValue = (float)$scoreToFormat;
    //                     // Always show decimal values properly - preserve actual decimal precision
    //                     if ($scoreValue == (int)$scoreValue) {
    //                         // Whole number - show as integer
    //                         $tdata['table_data'][$key]['score'] = (string)(int)$scoreValue;
    //                     } else {
    //                         // Decimal number - show with up to 2 decimal places, preserving significant decimals
    //                         // Use number_format to ensure proper decimal display, then remove only trailing zeros after decimal point
    //                         $formatted = number_format($scoreValue, 2, '.', '');
    //                         // Remove trailing zeros but keep at least one decimal place if original had decimals
    //                         $formatted = preg_replace('/\.?0+$/', '', $formatted);
    //                         // Ensure we don't remove the decimal point if there's a significant decimal (e.g., 62.5)
    //                         if (strpos($formatted, '.') === false && $scoreValue != (int)$scoreValue) {
    //                             // If we accidentally removed the decimal point, add it back with one decimal place
    //                             $formatted = number_format($scoreValue, 1, '.', '');
    //                             $formatted = rtrim($formatted, '0');
    //                             $formatted = rtrim($formatted, '.');
    //                         }
    //                         $tdata['table_data'][$key]['score'] = $formatted;
    //                     }
    //                 } elseif (isset($tdata['table_data'][$key]['score'])) {
    //                     // Fallback: try to format the original score value
    //                     $scoreValue = (float)$tdata['table_data'][$key]['score'];
    //                     if ($scoreValue == (int)$scoreValue) {
    //                         $tdata['table_data'][$key]['score'] = (string)(int)$scoreValue;
    //                     } else {
    //                         $formatted = number_format($scoreValue, 2, '.', '');
    //                         $formatted = preg_replace('/\.?0+$/', '', $formatted);
    //                         if (strpos($formatted, '.') === false && $scoreValue != (int)$scoreValue) {
    //                             $formatted = number_format($scoreValue, 1, '.', '');
    //                             $formatted = rtrim($formatted, '0');
    //                             $formatted = rtrim($formatted, '.');
    //                         }
    //                         $tdata['table_data'][$key]['score'] = $formatted;
    //                     }
    //                 }

    //                 // Remove the temporary score_decimal field from output
    //                 unset($tdata['table_data'][$key]['score_decimal']);
    //             }

    //             $tdata['data'] = $tdata['table_data'];
    //             unset($tdata['table_data']);

    //             return $this->response->setJSON($tdata);
    // }


    public function audit_view($id)
    {
        if (!canPerformAudit('HSE')) {
            return redirect()->back()->with('error', 'Access Denied: You do not have permission to perform audits. Only Auditors can perform audits.');
        }

        $data = [];
        //pending code 
        $db = db_connect();
        // changes on 1/10/25 by darsh: some databases miss `audit_template_id` column in `alert_question_audit_master`.
        // Try filtering by audit_template_id; if column doesn't exist, fallback to full list.
        try {
            // $data['audit_data'] = $db->table("alert_audit_questions")->where("audit_template_id",$id)->where("audit_site_category_name","External Warehouse")->get()->getResultArray();
        } catch (\Throwable $e) {
            // Fallback without where to avoid breaking the page
            //$data['audit_data'] = $db->table("alert_audit_questions")->get()->getResultArray();
        }


        // changes on 14/10/25 by darsh: Load previous audit details for prefilling if reaudit
        if (isset($data['details']['hse_audit_id'])) {
            $data['audit_details'] = $db->table("alert_hse_audit_details")
                ->where("hse_audit_id", $data['details']['hse_audit_id'])
                ->get()
                ->getResultArray();
        }

        // changes on 8/11/25 by darsh: Get audit template details to pre-fill audit type name
        try {
            $template = $db->table("alert_audit_template")->where("audit_template_id", $id)->get()->getRowArray();
            if ($template && !isset($data['details']['audit_name'])) {
                $data['details'] = $data['details'] ?? [];
                $data['details']['audit_name'] = $template['audit_name'] ?? '';
            }
        } catch (\Throwable $e) {
            // Silently ignore if template not found
        }

        // changes on 21/02/26: Use location as client_id for dropdown compatibility (no id column in table)
        $data['client'] = $db->table("alert_hse_client_master")
            ->select("location as client_id, client_name, location, region, cluster, status")
            ->where("status != ", 2)
            ->get()
            ->getResultArray();
        $data['region'] = $db->table("alert_hse_region_master")
            ->select("region_id, region_name")
            ->where("status", 1)
            ->orderBy("region_name", "ASC")
            ->get()
            ->getResultArray();
        // changes on 14/10/25 by darsh: Pass location and auditors list for Select2 dropdown
        $data['location'] = $db->table("alert_hse_client_master")->where("status != ", 2)->get()->getResultArray();
        // changes on 8/11/25 by darsh: Filter auditors list to show only users with Auditor designation (same as OE audit)
        if (isset($_SESSION['role']) && $_SESSION['role'] == "Auditor") {

            $auditor = $db->table("alert_users")
                ->where("user_id", $_SESSION['login_id'])
                ->where("status", 1)
                ->get()
                ->getRowArray();

            if ($auditor) {
                $data['auditors'] = [$auditor];

                // Set default auditor name
                $data['details'] = $data['details'] ?? [];
                $data['details']['auditor_name'] = $auditor['user_name'];
            }

        } else {

            $data['auditors'] = $db->table("alert_users")
                ->where("user_designation", "Auditor")
                ->where("status", 1)
                ->get()
                ->getResultArray();
        }
        // changes on 20/02/26: New flow with Main Category and Sub Category

        /* SITE CATEGORY */
        $site_categories = $db->table("alert_hse_site_category")
            ->select("site_category_id, site_category_name")
            ->where("status", 1)
            ->orderBy("site_category_name", "ASC")
            ->get()
            ->getResultArray();

        $name_list = [];

        foreach ($site_categories as $row) {

            $displayName = trim($row['site_category_name']);

            // safe slug for html class / js
            $hseType = strtolower($displayName);
            $hseType = preg_replace('/[^a-z0-9]+/i', '_', $hseType);
            $hseType = trim($hseType, '_');

            $name_list[] = [
                'hse_type' => $hseType,
                'display' => $displayName
            ];
        }

        $data['name_list'] = $name_list;
        //   print_r($name_list); // Debugging line to check the output of name_list  
        $subCategories = $db->table("alert_hse_sub_category sc")
            ->select("sc.sub_category_name, c.site_category_name")
            ->join("alert_hse_site_category c", "c.site_category_id = sc.site_category_id", "left")
            ->where("sc.status", 1)
            ->where("c.status", 1)
            ->orderBy("c.site_category_name", "ASC")
            ->get()
            ->getResultArray();

        $category_map = [];

        foreach ($subCategories as $row) {

            $category_map[$row['site_category_name']][] = $row['sub_category_name'];

        }

        $data['category_map'] = $category_map;


        $data['action'] = base_url("Masters/Hse_audit/save_audit_view/" . $id);
        $data['audit_template_id'] = $id;

        return view("Audit/audit_question_form", $data);
    }

    public function save_audit_view($id)
    {
        $request = service('request');
        $postData = $request->getVar();
        $files = $request->getFiles();

        $db = db_connect();

        $masterTable = $db->table("alert_hse_audit_master");
        $detailsTable = $db->table("alert_hse_audit_details");

        $scoreValue = isset($postData['score']) ? (float) $postData['score'] : 0;

        /* =========================================================
           PREPARE AUDIT NUMBER
        ========================================================= */
        $auditNo = $postData['audit_no'] ?? '';
        if (empty($auditNo) || $auditNo === '[ Auto Generated on Final Save ]') {
            // Will be generated after insert to use AUTO_ID
            $postData['audit_no'] = '';
            $auditNo = '';
        }

        /* =========================================================
           BASIC VALIDATION
        ========================================================= */
        if (empty($postData['audit_name'])) {
            return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with('error', 'Audit details missing.');
        }

        if (empty($postData['auditor_name'])) {
            return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with('error', 'Auditor Name is required.');
        }

        if (empty($postData['client_name'])) {
            return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with('error', 'Client Name is required.');
        }

        if (empty($postData['main_category']) || empty($postData['sub_category'])) {
            return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with('error', 'Site Category / Sub Category is required.');
        }

        // Auto-set next audit date to +3 months if not explicitly provided
        if (empty($postData['report_date']) && !empty($postData['audit_date'])) {
            $postData['report_date'] = date('Y-m-d', strtotime('+3 months', strtotime($postData['audit_date'])));
        }

        // Prevent duplicate perform audit for same client/location
        $clientName = trim($postData['client_name'] ?? '');
        $location = trim($postData['location'] ?? $clientName);

        if (!empty($clientName) || !empty($location)) {
            $existingQuery = $db->table('alert_hse_audit_master')
                ->where('status', 1)
                ->groupStart()
                ->groupStart()
                ->where('LOWER(TRIM(client_name))', strtolower(trim($clientName)))
                ->orWhere('LOWER(TRIM(location))', strtolower(trim($location)))
                ->groupEnd();

            if (!empty($clientName)) {
                $existingQuery->orWhere('LOWER(TRIM(location))', strtolower(trim($clientName)));
            }

            $existingQuery->groupEnd();

            $exists = $existingQuery->countAllResults(false);

            if ($exists > 0) {
                return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with('error', 'This Client/Location has already been audited once. Duplicate perform audit is not allowed.');
            }
        }

        /* =========================================================
           START TRANSACTION
        ========================================================= */
        $db->transStart();

        /* =========================================================
           INSERT MASTER RECORD
        ========================================================= */
        $clientRow = $db->table('alert_hse_client_master')
            ->where('client_name', $postData['client_name'] ?? '')
            ->get()->getRowArray();

        $masterData = [
            'audit_no' => $postData['audit_no'] ?? '',
            'audit_name' => $postData['audit_name'] ?? '',
            'auditor_name' => $postData['auditor_name'] ?? '',
            'auditee_name' => $postData['auditee_name'] ?? '',
            'client_name' => $postData['client_name'] ?? '',
            'location' => $postData['location'] ?? ($postData['client_name'] ?? ''),
            'cluster_name' => $postData['cluster_name'] ?? '',
            'account_manager' => $postData['account_manager'] ?? '',
            'audit_date' => $postData['audit_date'] ?? '',
            'report_date' => $postData['report_date'] ?? '',
            'region' => $postData['region'] ?? '',
            'perform_audit_by' => $postData['perform_audit_by'] ?? '',
            'main_category' => $postData['main_category'] ?? '',
            'sub_category' => $postData['sub_category'] ?? '',
            'audit_template_id' => $id,
            'status' => 1,
            'snapshot_site_id' => $clientRow['client_id'] ?? null,
            'snapshot_account_manager_name' => ($postData['account_manager'] ?? '') ?: ($clientRow['account_manager'] ?? ''),
            'snapshot_cluster_manager_name' => ($postData['cluster_name'] ?? '') ?: ($clientRow['cluster'] ?? ''),
            'snapshot_created_by_user_id' => $_SESSION['user_id'] ?? null,
            'snapshot_created_by_user_name' => $_SESSION['user_name'] ?? null,
            'snapshot_created_at' => date('Y-m-d H:i:s')
        ];

        $masterTable->set($masterData);
        $masterTable->set('score', 'CAST(' . $db->escape($scoreValue) . ' AS DECIMAL(10,2))', false);
        $masterTable->insert();

        $hse_id = $db->insertID();

        if (!$hse_id) {
            $db->transRollback();
            return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with('error', 'Master audit save failed.');
        }

        /* =========================================================
           GENERATE AUDIT NUMBER IF EMPTY
        ========================================================= */
        if (empty($auditNo)) {
            $auditNamePrefix = $postData['audit_name'] ?? 'HSE';
            $auditDatePart = date("Y-m-d", strtotime($postData['audit_date'] ?? date('Y-m-d')));
            $newAuditNo = $auditNamePrefix . "-" . $auditDatePart . "-" . $hse_id;
            
            $masterTable->where('hse_audit_id', $hse_id)->update(['audit_no' => $newAuditNo]);
            $postData['audit_no'] = $newAuditNo;
        }


        /* =========================================================
           PRELOAD QUESTION MASTER DATA
        ========================================================= */
        $questionIds = [];

        if (isset($postData['question_audit_id']) && is_array($postData['question_audit_id'])) {
            foreach ($postData['question_audit_id'] as $qid) {
                if ((int) $qid > 0) {
                    $questionIds[] = (int) $qid;
                }
            }
        }

        $questionById = [];

        if (!empty($questionIds)) {
            $qRows = $db->table("alert_audit_questions")
                ->whereIn("question_id", array_unique($questionIds))
                ->get()
                ->getResultArray();

            foreach ($qRows as $q) {
                $questionById[$q['question_id']] = $q;
            }
        }

        /* =========================================================
           ACTIVE FINDING COLUMN
        ========================================================= */
        $siteCategorySlug = strtolower($postData['perform_audit_by'] ?? '');
        $siteCategorySlug = preg_replace('/[^a-z0-9]+/i', '_', $siteCategorySlug);
        $siteCategorySlug = trim($siteCategorySlug, '_');

        /* =========================================================
           DETAILS SAVE LOOP
        ========================================================= */
        $allIndexes = [];

        if (isset($postData['audit_category_id']) && is_array($postData['audit_category_id'])) {
            $allIndexes = array_keys($postData['audit_category_id']);
        }

        if (!empty($allIndexes)) {

            foreach ($allIndexes as $key) {

                $question_audit_id = isset($postData['question_audit_id'][$key])
                    ? (int) $postData['question_audit_id'][$key]
                    : 0;

                $audit_category_id_val = strtoupper(trim($postData['audit_category_id'][$key] ?? ''));
                $audit_category_id_val = rtrim($audit_category_id_val, '.');

                $question_name = trim($postData['question_name'][$key] ?? '');
                $audit_question = trim($postData['audit_question'][$key] ?? '');
                $remark = trim($postData['remark'][$key] ?? '');
                $note = trim($postData['note'][$key] ?? '');

                $is_header = preg_match('/^[A-Z]$/', $audit_category_id_val);

                /* =====================================================
                   IMPORTANT: DO NOT SKIP MISC
                ===================================================== */
                if ($audit_category_id_val === '' && $question_name === '' && $audit_question === '') {
                    continue;
                }

                $qMeta = $questionById[$question_audit_id] ?? null;

                /* =====================================================
                   FORCE VALUES FROM MASTER IF BLANK / NULL
                ===================================================== */

                // audit_question blank/null -> take from master
                if ($audit_question === '' || strtolower($audit_question) === 'null') {
                    $audit_question = trim($qMeta['audit_question'] ?? '');
                }

                // audit_category blank/null -> take from master
                $audit_category_val = $question_name;
                if ($audit_category_val === '' || strtolower($audit_category_val) === 'null') {
                    $audit_category_val = trim($qMeta['audit_category'] ?? '');
                }

                // If header and audit_question still blank, use category name
                if ($is_header && $audit_question === '') {
                    $audit_question = $audit_category_val;
                }

                /* =====================================================
                   FINDING VALUE
                ===================================================== */
                $finding = '';

                if (!$is_header && isset($postData['finding'][$key])) {
                    $finding = strtoupper(trim($postData['finding'][$key]));
                }

                if (!in_array($finding, ['YES', 'NO', 'NA'])) {
                    $finding = '';
                }

                /* =====================================================
                   CAPA LOGIC
                ===================================================== */
                $isMisc = (isset($postData['is_misc'][$key]) && $postData['is_misc'][$key] == 1);

                $rawCapaJson = $postData['capa_json'][$key] ?? '';
                $validCapaJson = $this->normalizeCapaJson($rawCapaJson);



                // =====================================================
                // ✅ FINAL CAPA LOGIC
                // =====================================================
                if ($finding === 'NO') {

                    // If frontend didn't pass valid JSON but we have it in question master as default
                    if (empty($validCapaJson) && !empty($qMeta['capa_json'])) {
                        $validCapaJson = $this->normalizeCapaJson($qMeta['capa_json']);
                    }

                    if (empty($validCapaJson)) {
                        $db->transRollback();
                        return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with(
                            'error',
                            'CAPA details are mandatory for all NO responses.'
                        );
                    }

                    $capa_json = $validCapaJson;

                } else {
                    // YES / NA → CLEAR CAPA
                    $capa_json = '';
                }

                /* =====================================================
                   SITE / SUB CATEGORY
                ===================================================== */
                $site_category_val = $postData['main_category'] ?? '';
                $sub_category_val = $postData['sub_category'] ?? '';

                /* =====================================================
                   NOTE
                ===================================================== */
                $note_val = $note;

                /* =====================================================
                   NOTE
                ===================================================== */
                $note_val = $note;

                /* =====================================================
                   EXISTING ATTACHMENT
                ===================================================== */
                $attachmentPath = (!$is_header) ? ($postData['existing_attachment'][$key] ?? '') : '';

                /* =====================================================
                   FILE UPLOAD
                ===================================================== */
                if (
                    !$is_header &&
                    isset($files['attachment'][$key]) &&
                    $files['attachment'][$key] instanceof \CodeIgniter\HTTP\Files\UploadedFile &&
                    $files['attachment'][$key]->isValid() &&
                    !$files['attachment'][$key]->hasMoved()
                ) {
                    $file = $files['attachment'][$key];

                    $folderPath = FCPATH . 'uploads/audit_files/' . $postData['audit_no'] . '/';

                    if (!is_dir($folderPath)) {
                        mkdir($folderPath, 0777, true);
                    }

                    $newFileName = $postData['audit_no'] . '-' . $file->getRandomName();
                    $file->move($folderPath, $newFileName);

                    $attachmentPath = 'uploads/audit_files/' . $postData['audit_no'] . '/' . $newFileName;
                }

                /* =====================================================
                   INSERT DETAIL
                ===================================================== */
                $insertData = [
                    'hse_audit_id' => $hse_id,
                    'question_id' => $question_audit_id,
                    'audit_template_id' => $id,

                    'site_category' => $site_category_val,
                    'sub_category' => $sub_category_val,

                    'audit_category_id' => $audit_category_id_val,
                    'audit_category' => $audit_category_val,
                    'audit_question' => $audit_question,

                    'finding' => $is_header ? '' : $finding,
                    'note' => $note_val,
                    'capa_json' => $capa_json,
                    'remark' => $is_header ? '' : $remark,
                    'attachment' => $attachmentPath,
                    'nc_type' => ($postData['nc_type'][$key] ?? 'NC'),

                    'nc_status' => 0,
                    'status' => $is_header ? 4 : 1
                ];

                $detailsTable->insert($insertData);
            }
        }

        $this->processAttendanceSave($hse_id);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(base_url("Masters/Hse_audit/audit_view/" . $id))->with('error', 'Audit save failed. Please try again.');
        }

        /* =========================================================
           SEND EMAIL + PDF
        ========================================================= */
        // if ($hse_id) {

        //     $auditor_email = $db->table("alert_users")
        //         ->select('user_email')
        //         ->where('user_name', $postData['auditor_name'])
        //         ->get()
        //         ->getResultArray();

        //     if (!empty($auditor_email) && !empty($auditor_email[0]['user_email'])) {

        //         $html = $this->capa_report($hse_id, 1);

        //         $fileName = 'Audit-HSE-' . $hse_id . '.pdf';

        //         $options = new \Dompdf\Options();
        //         $options->set('defaultFont', 'Courier');
        //         $options->set('isRemoteEnabled', true);

        //         $dompdf = new \Dompdf\Dompdf($options);

        //         $dompdf->loadHtml($html);
        //         $dompdf->setPaper('A3', 'landscape');
        //         $dompdf->render();

        //         $output = $dompdf->output();

        //         $filePath = WRITEPATH . 'uploads/' . $fileName;
        //         file_put_contents($filePath, $output);

        //         $email = \Config\Services::email();

        //         $email->setFrom('api-email@unitglo.com', 'Alert');
        //         $email->setTo($auditor_email[0]['user_email']);
        //         $email->setSubject('Perform Audit No. ' . $postData['audit_no']);

        //         $email->setMessage(
        //             'Auditor Name : ' . ($postData['auditor_name'] ?? '') .
        //             '<br>Client Name : ' . ($postData['client_name'] ?? '') .
        //             '<br>Region : ' . ($postData['region'] ?? '')
        //         );

        //         $email->attach($filePath);
        //         $email->send();
        //     }
        // }

        // Requirement 3: Call sync service
        $syncService = new HseGembaSyncService();
        $syncService->syncAudit($hse_id);

        $this->sendHseAuditEmail($hse_id, 'perform');

        $this->delete_draft([
            'draft_type' => 'perform',
            'client_name' => $postData['client_name'] ?? '',
            'location' => $postData['location'] ?? '',
            'main_category' => $postData['main_category'] ?? '',
            'sub_category' => $postData['sub_category'] ?? ''
        ]);

        return redirect()->to(base_url("/Masters/Hse_audit"))
            ->with('success', 'HSE Audit saved successfully.');
    }

    public function view_attendance_modal($hseAuditId)
    {
        $db = \Config\Database::connect();
        
        $master = $db->table('alert_hse_audit_master')
                     ->where('hse_audit_id', $hseAuditId)
                     ->get()
                     ->getRowArray();
                     
        if (!$master) {
            return "<div class='alert alert-danger'>Audit not found.</div>";
        }
        
        $attendance = $db->table('alert_hse_audit_attendance')
                         ->where('hse_audit_id', $hseAuditId)
                         ->orderBy('row_no', 'ASC')
                         ->get()
                         ->getResultArray();
                         
        $data = [
            'master' => $master,
            'attendance' => $attendance
        ];
        
        return view('Audit/view_attendance_modal', $data);
    }

    public function upload_signature_base64()
    {
        $request = \Config\Services::request();
        $base64 = $request->getVar('image');
        $auditNo = $request->getVar('audit_no') ?? 'DRAFT';
        
        if (!$base64) {
            return $this->response->setJSON(['status' => 0, 'message' => 'No image data provided.']);
        }
        
        list($type, $base64) = explode(';', $base64);
        list(, $base64)      = explode(',', $base64);
        
        $data = base64_decode($base64);
        
        $uploadPath = 'uploads/audit_signatures/';
        if (!is_dir(FCPATH . $uploadPath)) {
            mkdir(FCPATH . $uploadPath, 0777, true);
        }
        
        $fileName = 'sign_' . $auditNo . '_' . uniqid() . '.png';
        $fullPath = FCPATH . $uploadPath . $fileName;
        
        if (file_put_contents($fullPath, $data)) {
            return $this->response->setJSON([
                'status' => 1,
                'path' => $uploadPath . $fileName,
                'message' => 'Signature saved successfully.'
            ]);
        }
        
        return $this->response->setJSON(['status' => 0, 'message' => 'Failed to save signature file.']);
    }

    private function processAttendanceSave($hseAuditId)
    {
        $db = \Config\Database::connect();
        $request = \Config\Services::request();
        
        $names = $request->getVar('att_auditee_name');
        $openDates = $request->getVar('att_opening_date');
        $closeDates = $request->getVar('att_closing_date');
        $existingOpen = $request->getVar('existing_att_opening_sign');
        $existingClose = $request->getVar('existing_att_closing_sign');

        if (!is_array($names)) $names = [];
        if (!is_array($openDates)) $openDates = [];
        if (!is_array($closeDates)) $closeDates = [];
        if (!is_array($existingOpen)) $existingOpen = [];
        if (!is_array($existingClose)) $existingClose = [];

        // Delete existing rows to replace with new valid ones
        $db->table('alert_hse_audit_attendance')->where('hse_audit_id', $hseAuditId)->delete();
        
        $userName = session()->get('user_name') ?? '';
        $now = date('Y-m-d H:i:s');
        $uploadPath = 'uploads/hse_audit_attendance/';
        if (!is_dir(FCPATH . $uploadPath)) {
            mkdir(FCPATH . $uploadPath, 0777, true);
        }

        $rowIndices = array_unique(array_merge(array_keys($names), array_keys($existingOpen), array_keys($existingClose)));
        
        $newRowNo = 1;
        $savedPaths = [];

        foreach ($rowIndices as $i) {
            $name = trim($names[$i] ?? '');
            $openDate = trim($openDates[$i] ?? '');
            $closeDate = trim($closeDates[$i] ?? '');
            $openPath = trim($existingOpen[$i] ?? '');
            $closePath = trim($existingClose[$i] ?? '');
            
            $openFile = $request->getFile("att_opening_sign.{$i}");
            if ($openFile && $openFile->isValid() && !$openFile->hasMoved()) {
                $newName = $openFile->getRandomName();
                if ($openFile->move(FCPATH . $uploadPath, $newName)) {
                    $openPath = $uploadPath . $newName;
                }
            }

            $closeFile = $request->getFile("att_closing_sign.{$i}");
            if ($closeFile && $closeFile->isValid() && !$closeFile->hasMoved()) {
                $newName = $closeFile->getRandomName();
                if ($closeFile->move(FCPATH . $uploadPath, $newName)) {
                    $closePath = $uploadPath . $newName;
                }
            }

            if (empty($name) || empty($openDate) || empty($openPath)) {
                continue;
            }

            $db->table('alert_hse_audit_attendance')->insert([
                'hse_audit_id' => $hseAuditId,
                'row_no' => $newRowNo,
                'auditee_attendance' => $name,
                'opening_date' => $openDate ?: null,
                'closing_date' => $closeDate ?: null,
                'opening_sign' => $openPath,
                'closing_sign' => $closePath,
                'created_by' => $userName,
                'updated_by' => $userName,
                'created_at' => $now,
                'updated_at' => $now,
                'status' => 1
            ]);
            
            $savedPaths[$i] = [
                'opening_sign' => base_url($openPath),
                'opening_sign_rel' => $openPath,
                'closing_sign' => $closePath ? base_url($closePath) : '',
                'closing_sign_rel' => $closePath
            ];
            
            $newRowNo++;
        }
        
        return $savedPaths;
    }

    public function save_attendance_ajax()
    {
        $request = \Config\Services::request();
        $hseAuditId = $request->getVar('hse_audit_id');
        
        if (!$hseAuditId) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid Audit ID']);
        }

        $savedPaths = $this->processAttendanceSave($hseAuditId);

        return $this->response->setJSON([
            'status' => 1, 
            'message' => 'Attendance saved successfully',
            'new_paths' => $savedPaths
        ]);
    }

    public function capa_report($id, $flag = null)
    {

        ini_set('memory_limit', '512M');
        set_time_limit(300);
        $db = db_connect();
        $data = [];

        // ---------------- MASTER ----------------
        $master = $db->table('alert_hse_audit_master')
            ->where("hse_audit_id", $id)
            ->get()
            ->getRowArray();

        $data['details'] = $master;

        // ---------------- GET CATEGORY FROM DB ----------------
        $siteCategories = $db->table('alert_hse_site_category')
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $categorySlugs = [];

        foreach ($siteCategories as $cat) {

            $slug = strtolower(trim($cat['site_category_name']));
            $slug = preg_replace('/[^a-z0-9]+/i', '_', $slug);
            $slug = trim($slug, '_');

            $categorySlugs[] = $slug;
        }

        // ---------------- FETCH DETAILS ----------------
        $rows = $db->table('alert_hse_audit_details d')
            ->select("d.*, q.audit_category_id, q.note")
            ->join("alert_audit_questions q", "q.question_id = d.question_id", "left")
            ->where("d.hse_audit_id", $id)
            ->orderBy("d.id", "ASC")
            ->get()
            ->getResultArray();

        $results = [];
        // print_r($rows);
        // exit;
        foreach ($rows as $row) {
            $capa_json = json_decode($row['capa_json'] ?? '[]', true) ?: [];

            $selectedFinding = strtoupper(trim($row['finding'] ?? ''));
            $selectedFinding = in_array($selectedFinding, ['YES', 'NO', 'NA']) ? $selectedFinding : '';

            // ✅ ONLY FETCH CAPA FOR NO
            if ($selectedFinding == 'NO') {
                $values = $this->extractJsonValues($capa_json, $selectedFinding);
            } else {
                $values = [];
            }

            // normalize DB site_category
            $siteCategory = strtolower(trim($row['site_category'] ?? ''));
            $siteCategory = preg_replace('/[^a-z0-9]+/i', '_', $siteCategory);
            $siteCategory = trim($siteCategory, '_');

            $resultRow = [

                "id" => $row['id'],

                "site_category" => $row['site_category'] ?? '',
                "sub_category" => $row['sub_category'] ?? '',

                "audit_category_id" => $row['audit_category_id'] ?? '',
                "audit_category" => $row['audit_category'] ?? '',
                "audit_question" => $row['audit_question'] ?? '',

                "finding" => $selectedFinding,

                "remark" => $row['remark'] ?? '',
                "attachment" => $row['attachment'] ?? '',

                "capa_values" => $values,
                "full_data" => $row
            ];

            // ---------------- DYNAMIC CATEGORY ----------------
            foreach ($categorySlugs as $cat) {

                // Always assign values
                $resultRow[$cat] = $values;

                // Only category match → show YES/NO/NA
                if ($siteCategory === $cat) {
                    $resultRow[$cat . '_value'] = $selectedFinding;
                } else {
                    $resultRow[$cat . '_value'] = '';
                }
            }

            $results[] = $resultRow;
        }

        $data['results'] = $results;

        // pass category to view
        $data['categorySlugs'] = $categorySlugs;
        $data['activeCategory'] = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $master['perform_audit_by'] ?? ''));
        // print_r($data);
// exit;
        // ---------------- VIEW ----------------
        $html = view('Audit/capa_report_pdf', $data);

        if ($flag == 1) {
            return $html;
        }

        // ---------------- PDF ----------------
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('chroot', FCPATH); // ✅ IMPORTANT

        $dompdf = new Dompdf($options);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A3', 'landscape');
        $dompdf->render();

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="AuditWithCAPA-' . $id . '.pdf"');

        echo $dompdf->output();
    }

    // changes on 4/10/25 by darsh: Add HSE reaudit functionality similar to OE/Normal audit logic
    public function hse_reaudit($hseAuditId)
    {
        $db = db_connect();

        // Get current HSE audit details
        $currentHSEAudit = $db->table("alert_hse_audit_master")
            ->where("hse_audit_id", $hseAuditId)
            ->get()
            ->getRowArray();

        if (!$currentHSEAudit) {
            return redirect()->back()->with('error', 'HSE Audit record not found.');
        }

        // changes on 4/10/25 by darsh: Since reaudit column doesn't exist in HSE table, 
        // we'll reuse original audit number
        $currentAuditNo = $currentHSEAudit['audit_no'];


        // Prepare data for reaudit view
        $data['details'] = $currentHSEAudit;
        // changes on 15/10/25 by darsh: keep audit no as normal number without R suffix
        $data['details']['audit_no'] = $currentHSEAudit['audit_no'];
        // changes on 15/10/25 by darsh: ensure Next Date and Client/Location names are prefilled like perform audit
        if (!isset($data['details']['template_date']) || $data['details']['template_date'] === '') {
            $data['details']['template_date'] = $currentHSEAudit['template_date'] ?? ($currentHSEAudit['next_date'] ?? '');
        }
        // Normalize client and location text fields used by the view for preselect
        $data['details']['client_name'] = $currentHSEAudit['client_name'] ?? ($data['details']['client_name'] ?? '');
        $data['details']['location'] = $currentHSEAudit['location'] ?? ($data['details']['location'] ?? '');
        
        // Fetch Attendance for Reaudit
        $data['attendance'] = $db->table('alert_hse_audit_attendance')
            ->where('hse_audit_id', $hseAuditId)
            ->orderBy('row_no', 'ASC')
            ->get()
            ->getResultArray();

        // Get HSE audit details for prefilling with audit_category_id (site + sub-category wise)
        // Fetch old answers (details) WITH group_id from master questions
        $oldDetails = $db->table("alert_hse_audit_details as d")
            ->select("d.*, q.audit_category_id, q.group_id") // Fetch category info
            ->join(
                "alert_audit_questions as q",
                "q.question_id = d.question_id",
                "left"
            )
            ->where("d.hse_audit_id", $hseAuditId)
            ->where("d.site_category", $currentHSEAudit['main_category'] ?? '')
            ->where("d.sub_category", $currentHSEAudit['sub_category'] ?? '')
            ->get()
            ->getResultArray();

        // Create a lookup for old answers by group_id
        $oldAnswersByGroup = [];
        foreach($oldDetails as $d) {
            $grp = !empty($d['group_id']) ? $d['group_id'] : $d['question_id'];
            $oldAnswersByGroup[$grp] = $d;
        }

        // Get LATEST HSE Template
        $latestTemplate = $db->table('alert_audit_template')
            ->where('audit_template_type', 'HSE')
            ->where('status', 1)
            ->orderBy('audit_template_id', 'DESC')
            ->get()
            ->getRowArray();

        $originalTemplateId = $currentHSEAudit['audit_template_id'] ?? 0;
        $latestTemplateId = $latestTemplate ? $latestTemplate['audit_template_id'] : $originalTemplateId;
        $data['template'] = $latestTemplate;

        if ($latestTemplate && !isset($data['details']['audit_name'])) {
            $data['details']['audit_name'] = $latestTemplate['audit_name'] ?? '';
        }
        
        $data['audit_template_id'] = $latestTemplateId;

        // Pass a flag to view if template version changed
        if ($originalTemplateId != $latestTemplateId) {
            $data['template_version_changed'] = true;
        }

        // Reaudit must load the LATEST questions for this category
        $latestQuestions = $db->table('alert_audit_questions')
            ->where('audit_template_id', $latestTemplateId)
            ->where('status !=', 2)
            ->where('audit_site_category_name', $currentHSEAudit['main_category'] ?? '')
            ->where('audit_sub_category_name', $currentHSEAudit['sub_category'] ?? '')
            ->get()
            ->getResultArray();

        usort($latestQuestions, function($a, $b) {
            return strnatcmp($a['audit_category_id'] ?? '', $b['audit_category_id'] ?? '');
        });

        $data['audit_details'] = [];
        $data['audit_data'] = [];

        foreach ($latestQuestions as $newQ) {
            $grp = !empty($newQ['group_id']) ? $newQ['group_id'] : $newQ['question_id'];
            $oldD = $oldAnswersByGroup[$grp] ?? null;

            // Map old findings over the new question
            $mappedDetail = $oldD ? $oldD : [];
            // Override with NEW question's text and IDs
            $mappedDetail['question_id'] = $newQ['question_id'];
            $mappedDetail['question_name'] = $newQ['audit_category'] ?? '';
            $mappedDetail['audit_question'] = $newQ['audit_question'] ?? '';
            $mappedDetail['audit_category_id'] = $newQ['audit_category_id'] ?? '';
            $mappedDetail['note'] = $newQ['note'] ?? '';
            $mappedDetail['capa_json'] = $oldD ? ($oldD['capa_json'] ?? $newQ['capa_json'] ?? '') : ($newQ['capa_json'] ?? '');
            
            $data['audit_details'][] = $mappedDetail;

            $data['audit_data'][] = [
                'question_id' => $newQ['question_id'],
                'question_name' => $newQ['audit_category'] ?? '',
                'audit_question' => $newQ['audit_question'] ?? '',
                'audit_category_id' => $newQ['audit_category_id'] ?? '',
                'capa_json' => $mappedDetail['capa_json'],
                'nc_type' => $oldD ? ($oldD['nc_type'] ?? 'NC') : 'NC',
                'note' => $newQ['note'] ?? ''
            ];
        }

        // changes on 15/10/25 by darsh: Add missing client, region and location data for dropdowns
        // changes on 21/02/26: Use location as client_id for dropdown compatibility (no id column in table)
        $data['client'] = $db->table("alert_hse_client_master")
            ->select("location as client_id, client_name, location, region, cluster, status")
            ->where("status != ", 2)
            ->get()
            ->getResultArray();
        $data['region'] = $db->table("alert_hse_client_master")
            ->select("DISTINCT(region) as region_name")
            ->where("status !=", 2)
            ->orderBy("region", "ASC")
            ->get()
            ->getResultArray();
        // changes on 15/10/25 by darsh: also pass locations so Site Location works same as perform audit
        $data['location'] = $db->table("alert_hse_client_master")->where("status != ", 2)->get()->getResultArray();

        // changes on 4/10/25 by darsh: Add audit category master data for questions display
        // changes on 20/02/26: New flow with Main Category and Sub Category
        /* -------------------------------------------------------
        SITE CATEGORY LIST (Dynamic)
        -------------------------------------------------------*/

        $site_categories = $db->table("alert_hse_site_category")
            ->select("site_category_id, site_category_name")
            ->where("status", 1)
            ->orderBy("site_category_name", "ASC")
            ->get()
            ->getResultArray();

        $name_list = [];

        foreach ($site_categories as $row) {

            $displayName = trim($row['site_category_name']);

            // Create slug for JS / HTML column names
            $hseType = strtolower($displayName);
            $hseType = preg_replace('/[^a-z0-9]+/i', '_', $hseType);
            $hseType = trim($hseType, '_');

            $name_list[] = [
                'hse_type' => $hseType,
                'display' => $displayName
            ];
        }

        $data['name_list'] = $name_list;
        /* -------------------------------------------------------
        ACTIVE SITE CATEGORY (for Reaudit column display)
        -------------------------------------------------------*/
        
        $activeSiteCategoryRaw = $currentHSEAudit['main_category'] ?? $currentHSEAudit['perform_audit_by'] ?? '';
        $activeSiteCategory = strtolower($activeSiteCategoryRaw);
        $activeSiteCategory = preg_replace('/[^a-z0-9]+/i', '_', $activeSiteCategory);
        $activeSiteCategory = trim($activeSiteCategory, '_');

        $data['active_site_category'] = $activeSiteCategory;

        /* -------------------------------------------------------
        SUB CATEGORY MAP (Dynamic)
        -------------------------------------------------------*/

        $subCategories = $db->table("alert_hse_sub_category sc")
            ->select("sc.sub_category_name, c.site_category_name")
            ->join("alert_hse_site_category c", "c.site_category_id = sc.site_category_id", "left")
            ->where("sc.status", 1)
            ->where("c.status", 1)
            ->orderBy("c.site_category_name", "ASC")
            ->get()
            ->getResultArray();

        $category_map = [];

        foreach ($subCategories as $row) {

            $category_map[$row['site_category_name']][] = $row['sub_category_name'];

        }

        $data['category_map'] = $category_map;
        // changes on 8/11/25 by darsh: Filter auditors list to show only users with Auditor designation (same as OE audit)
        if (isset($_SESSION['role']) && $_SESSION['role'] == "Auditor") {
            $data['auditors'] = $db->table("alert_users")->where("user_id", $_SESSION['login_id'])->where("status", 1)->get()->getResultArray();
        } else {
            $data['auditors'] = $db->table("alert_users")->where("user_designation", "Auditor")->where("status", 1)->get()->getResultArray();
        }

        $data['title'] = "HSE Reaudit";
        $data['action'] = base_url("Masters/Hse_audit/save_hse_reaudit/" . $hseAuditId);

        return view("Audit/audit_question_form", $data);
    }

    public function save_hse_reaudit($originalHseId)
    {
        $request = service('request');
        $postData = $request->getVar();
        $files = $request->getFiles();
        $db = db_connect();

        $masterTbl = $db->table("alert_hse_audit_master");
        $detailsTbl = $db->table("alert_hse_audit_details");

        /* -----------------------------
           GET ORIGINAL AUDIT
        ----------------------------- */
        $orig = $masterTbl->where("hse_audit_id", $originalHseId)->get()->getRowArray();

        if (!$orig) {
            return redirect()->back()->with('error', 'Audit not found');
        }

        $db->transStart();

        // Auto-set next audit date to +3 months if not explicitly provided
        if (empty($postData['report_date']) && !empty($postData['audit_date'])) {
            $postData['report_date'] = date('Y-m-d', strtotime('+3 months', strtotime($postData['audit_date'])));
        }

        /* =========================================================
           ATTENDANCE VALIDATION (REMOVED - NOW OPTIONAL)
        ========================================================= */

        /* -----------------------------
           INSERT MASTER
        ----------------------------- */
        // Reuse Original Audit Number (No Suffix)
        $auditNo = $orig['audit_no'];
        
        $scoreValue = isset($postData['score']) ? (float) $postData['score'] : 0;

        $masterData = [
            'audit_no' => $auditNo,
            'audit_name' => $postData['audit_name'] ?? '',
            'audit_template_id' => !empty($postData['audit_template_id']) ? $postData['audit_template_id'] : $orig['audit_template_id'],
            'auditor_name' => $postData['auditor_name'] ?? '',
            'auditee_name' => $postData['auditee_name'] ?? '',
            'client_name' => $postData['client_name'] ?? '',
            'audit_date' => $postData['audit_date'] ?? '',
            'report_date' => $postData['report_date'] ?? '',
            'region' => $postData['region'] ?? '',
            'location' => !empty($postData['location']) ? $postData['location'] : ($postData['client_name'] ?? ''),
            'cluster_name' => $postData['cluster_name'] ?? '',
            'account_manager' => $postData['account_manager'] ?? '',
            'perform_audit_by' => $postData['perform_audit_by'] ?? '',
            'main_category' => $postData['main_category'] ?? '',
            'sub_category' => $postData['sub_category'] ?? '',
            'status' => 1,
            'default_date' => date("Y-m-d H:i:s")
        ];

        $masterTbl->set($masterData);
        $masterTbl->set('score', 'CAST(' . $db->escape($scoreValue) . ' AS DECIMAL(10,2))', false);
        $masterTbl->insert();

        $newAuditId = $db->insertID();

        if (!$newAuditId) {
            $dbError = $db->error();
            $lastQuery = (string) $db->getLastQuery();
            return redirect()->to(base_url("Masters/Hse_audit/hse_reaudit/" . $originalHseId))->with('error', 'Reaudit master save failed: ' . ($dbError['message'] ?? 'Unknown DB Error') . ' | SQL: ' . $lastQuery);
        }

        /* -----------------------------
           PRELOAD QUESTION MASTER
        ----------------------------- */
        $questionIds = [];

        if (!empty($postData['question_audit_id']) && is_array($postData['question_audit_id'])) {
            foreach ($postData['question_audit_id'] as $qid) {
                if ((int) $qid > 0) {
                    $questionIds[] = (int) $qid;
                }
            }
        }

        $questionById = [];

        if (!empty($questionIds)) {
            $qRows = $db->table('alert_audit_questions')
                ->whereIn('question_id', array_unique($questionIds))
                ->get()
                ->getResultArray();

            foreach ($qRows as $q) {
                $questionById[$q['question_id']] = $q;
            }
        }

        /* -----------------------------
           ACTIVE FINDING COLUMN
        ----------------------------- */
        $siteType = strtolower($postData['perform_audit_by'] ?? '');
        $siteType = preg_replace('/[^a-z0-9]+/i', '_', $siteType);
        $siteType = trim($siteType, '_');

        /* -----------------------------
           INSERT DETAILS
        ----------------------------- */
        if (isset($postData['audit_category_id']) && is_array($postData['audit_category_id'])) {

            foreach ($postData['audit_category_id'] as $key => $catIdRaw) {

                $question_id = isset($postData['question_audit_id'][$key]) ? (int) $postData['question_audit_id'][$key] : 0;

                $audit_category_id = strtoupper(trim($catIdRaw ?? ''));
                $audit_category_id = rtrim($audit_category_id, '.');

                $audit_category = trim($postData['question_name'][$key] ?? '');
                $audit_question = trim($postData['audit_question'][$key] ?? '');
                $remark = trim($postData['remark'][$key] ?? '');
                $note = trim($postData['note'][$key] ?? '');

                $is_header = preg_match('/^[A-Z]$/', $audit_category_id);
                $is_misc = isset($postData['is_misc'][$key]) && $postData['is_misc'][$key] == '1';

                $qMeta = $questionById[$question_id] ?? null;

                /* -----------------------------
                   FORCE VALUES FROM MASTER IF NEEDED
                ----------------------------- */
                if (($audit_category === '' || strtolower($audit_category) === 'null') && $qMeta) {
                    $audit_category = trim($qMeta['audit_category'] ?? '');
                }

                if (($audit_question === '' || strtolower($audit_question) === 'null') && $qMeta) {
                    $audit_question = trim($qMeta['audit_question'] ?? '');
                }

                if ($is_header && $audit_question === '') {
                    $audit_question = $audit_category;
                }

                /* -----------------------------
                   IMPORTANT: DO NOT SKIP MISC
                ----------------------------- */
                if ($audit_category_id === '' && $audit_category === '' && $audit_question === '') {
                    continue;
                }

                /* -----------------------------
                   FINDING
                ----------------------------- */
                $finding = '';

                if (!$is_header && isset($postData['finding'][$key])) {
                    $finding = strtoupper(trim($postData['finding'][$key]));
                }

                $finding = strtoupper(trim($finding));

                if (!in_array($finding, ['YES', 'NO', 'NA'])) {
                    $finding = '';
                }

                /* -----------------------------
                   NOTE
                ----------------------------- */
                $note_val = $note;

                /* -----------------------------
                   ATTACHMENT
                ----------------------------- */
                $attachment = (!$is_header) ? ($postData['existing_attachment'][$key] ?? '') : '';

                if (
                    !$is_header &&
                    isset($files['attachment'][$key]) &&
                    $files['attachment'][$key] instanceof \CodeIgniter\HTTP\Files\UploadedFile &&
                    $files['attachment'][$key]->isValid() &&
                    !$files['attachment'][$key]->hasMoved()
                ) {
                    $file = $files['attachment'][$key];

                    $folder = FCPATH . "uploads/audit_files/" . $auditNo . "/";

                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }

                    $name = $auditNo . "-" . $file->getRandomName();
                    $file->move($folder, $name);

                    $attachment = "uploads/audit_files/" . $auditNo . "/" . $name;
                }

                /* =====================================================
                CAPA VALIDATION FOR MISC + CATEGORY V
                ===================================================== */

                $rawCapaJson = $postData['capa_json'][$key] ?? '';
                $validCapaJson = $this->normalizeCapaJson($rawCapaJson);

                $isMisc = isset($postData['is_misc'][$key]) && $postData['is_misc'][$key] == 1;



                /* =====================================================
                FINAL CAPA LOGIC
                ===================================================== */

                if ($finding === 'NO') {

                    // If frontend didn't pass valid JSON but we have it in question master as default
                    if (empty($validCapaJson) && !empty($qMeta['capa_json'])) {
                        $validCapaJson = $this->normalizeCapaJson($qMeta['capa_json']);
                    }

                    if (empty($validCapaJson)) {
                        $db->transRollback();
                        return redirect()->to(base_url("Masters/Hse_audit/hse_reaudit/" . $originalHseId))->with(
                            'error',
                            'CAPA details are mandatory for all NO responses.'
                        );
                    }

                    $capa_json = $validCapaJson;

                } else {
                    // ✅ YES / NA → ALWAYS CLEAR
                    $capa_json = '';
                }
                $ncType = $postData['nc_type'][$key] ?? 'NC';

                /* -----------------------------
                   INSERT DETAIL
                ----------------------------- */
                $detail = [
                    'hse_audit_id' => $newAuditId,
                    'question_id' => $question_id ?: null,
                    'audit_template_id' => $orig['audit_template_id'],
                    'site_category' => $postData['main_category'] ?? '',
                    'sub_category' => $postData['sub_category'] ?? '',
                    'audit_category_id' => $audit_category_id,
                    'audit_category' => $audit_category,
                    'audit_question' => $audit_question,
                    'finding' => $is_header ? '' : $finding,
                    'note' => $note_val,
                    'nc_type' => $is_header ? '' : $ncType,
                    'capa_json' => $capa_json,
                    'remark' => $is_header ? '' : $remark,
                    'attachment' => $attachment,
                    'nc_status' => 0,
                    'status' => $is_header ? 4 : 1,
                    'default_date' => date("Y-m-d H:i:s")
                ];

                $detailsTbl->insert($detail);
            }
        }

        $this->processAttendanceSave($newAuditId);

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(base_url("Masters/Hse_audit/hse_reaudit/" . $originalHseId))->with('error', 'HSE Reaudit save failed. Please try again.');
        }

        // Requirement 3: Call sync service
        $syncService = new HseGembaSyncService();
        $syncService->syncAudit($newAuditId);

        // Update all open Gemba records for this audit_no with the new audit/target dates
        $masterIds = $db->table('alert_hse_audit_master')->select('hse_audit_id')->where('audit_no', $auditNo)->get()->getResultArray();
        $masterIdList = array_column($masterIds, 'hse_audit_id');
        
        if (!empty($masterIdList) && (!empty($postData['audit_date']) || !empty($postData['report_date']))) {
            $gembaUpdateData = [];
            if (!empty($postData['audit_date'])) {
                $gembaUpdateData['audit_mail_received_date'] = date('Y-m-d', strtotime($postData['audit_date']));
                $gembaUpdateData['audit_report_date'] = date('Y-m-d', strtotime($postData['audit_date']));
            }
            if (!empty($postData['report_date'])) {
                $gembaUpdateData['target_date'] = date('Y-m-d', strtotime($postData['report_date']));
            }
            if (!empty($gembaUpdateData)) {
                $db->table('alert_gemba_audits')
                   ->whereIn('hse_audit_id', $masterIdList)
                   ->where('status !=', 2)
                   ->where('nc_status !=', 3)
                   ->update($gembaUpdateData);
            }
        }

        $this->sendHseAuditEmail($newAuditId, 'reaudit', $originalHseId);

        $this->delete_draft([
            'draft_type' => 'reaudit',
            'original_hse_audit_id' => $originalHseId
        ]);

        return redirect()->to(base_url("/Masters/Hse_audit"))
            ->with('success', 'HSE Reaudit saved successfully.');
    }

    // public function exportExcel($auditId)
    // {
    //     while (ob_get_level()) ob_end_clean();

    //     $db = db_connect();

    //     // ---------------- MASTER ----------------
    //     $details = $db->table("alert_hse_audit_master")
    //         ->where("hse_audit_id", $auditId)
    //         ->get()
    //         ->getRowArray();

    //     if (!$details) {
    //         echo "Audit not found!";
    //         return;
    //     }

    //     // ---------------- GET CATEGORIES ----------------
    //     $siteCategories = $db->table('alert_hse_site_category')
    //         ->where('status', 1)
    //         ->get()
    //         ->getResultArray();

    //     $categoryKeys = [];
    //     foreach ($siteCategories as $cat) {
    //         $key = strtolower(str_replace(' ', '_', trim($cat['site_category_name'])));
    //         $categoryKeys[$key] = $cat['site_category_name'];
    //     }

    //     // ---------------- FETCH DETAILS ----------------
    //     $rows = $db->table("alert_hse_audit_details d")
    //         ->select("d.*, q.audit_category_id")
    //         ->join("alert_audit_questions q", "q.question_id = d.question_id", "left")
    //         ->where("d.hse_audit_id", $auditId)
    //         ->orderBy("d.id", "ASC")
    //         ->get()
    //         ->getResultArray();

    //     // ---------------- PROCESS ----------------
    //     $audit_details = [];

    //     foreach ($rows as $row) {

    //         $rowCategories = array_fill_keys(array_keys($categoryKeys), '');

    //         $siteCategory = strtolower(str_replace(' ', '_', trim($row['site_category'] ?? '')));
    //         $finding = strtoupper(trim($row['finding'] ?? ''));

    //         if (isset($rowCategories[$siteCategory])) {
    //             $rowCategories[$siteCategory] = $finding;
    //         }

    //         $audit_details[] = $row + ['categories'=>$rowCategories];
    //     }

    //     // ---------------- HEADERS ----------------
    //     header("Content-Type: application/vnd.ms-excel");
    //     header("Content-Disposition: attachment; filename=HSE-Audit-$auditId.xls");

    //     // ---------------- SUMMARY ----------------
    //     echo "<table border='1' style='border-collapse:collapse;'>";

    //     echo "<tr>
    //             <th colspan='2' style='font-size:18px;background:#d9d9d9;text-align:center;'>
    //                 HSE Audit Summary
    //             </th>
    //         </tr>";

    //     echo "<tr><th>Audit No</th><td>".htmlspecialchars($details['audit_no'] ?? '')."</td></tr>";
    //     echo "<tr><th>Audit Name</th><td>".htmlspecialchars($details['audit_name'] ?? '')."</td></tr>";
    //     echo "<tr><th>Auditor Name</th><td>".htmlspecialchars($details['auditor_name'] ?? '')."</td></tr>";
    //     echo "<tr><th>Auditee Name</th><td>".htmlspecialchars($details['auditee_name'] ?? '')."</td></tr>";
    //     echo "<tr><th>Client Name</th><td>".htmlspecialchars($details['client_name'] ?? '')."</td></tr>";
    //     echo "<tr><th>Region</th><td>".htmlspecialchars($details['region'] ?? '')."</td></tr>";
    //     echo "<tr><th>Audit Date</th><td>".htmlspecialchars($details['audit_date'] ?? '')."</td></tr>";
    //     echo "<tr><th>Score</th><td>".htmlspecialchars($details['score'] ?? '')."</td></tr>";
    //     echo "<tr><th>Site Category</th><td>".htmlspecialchars($details['main_category'] ?? '')."</td></tr>";
    //     echo "<tr><th>Sub Category</th><td>".htmlspecialchars($details['sub_category'] ?? '')."</td></tr>";
    //     echo "</table><br>";

    //     // ---------------- QUESTIONS ----------------
    //     echo "<table border='1'>";

    //     echo "<tr style='background:#1e1c77;color:#fff'>
    //         <th>Sr No</th>
    //         <th>Category</th>
    //         <th>Question</th>";

    //     foreach ($categoryKeys as $label) {
    //         echo "<th>$label</th>";
    //     }

    //     echo "<th>Remark</th></tr>";

    //     foreach ($audit_details as $i => $d) {

    //         echo "<tr>
    //             <td>".($d['audit_category_id'] ?? ($i+1))."</td>
    //             <td>".htmlspecialchars($d['audit_category'] ?? '')."</td>
    //             <td>".htmlspecialchars($d['audit_question'] ?? '')."</td>";

    //         foreach ($categoryKeys as $key => $label) {

    //             $val = strtoupper($d['categories'][$key] ?? '');

    //             $color = $val == 'YES' ? 'green' : ($val == 'NO' ? 'red' : 'gray');

    //             echo "<td style='color:$color;font-weight:bold;'>$val</td>";
    //         }

    //         echo "<td>".htmlspecialchars($d['remark'] ?? '')."</td></tr>";
    //     }

    //     echo "</table>";

    //     exit;
    // }

    public function capa_report_excel($auditId)
    {
        while (ob_get_level() > 0)
            ob_end_clean();

        $db = db_connect();

        // ---------------- MASTER ----------------
        $master = $db->table('alert_hse_audit_master')
            ->where('hse_audit_id', $auditId)
            ->get()
            ->getRowArray();

        if (!$master) {
            return service('response')->setStatusCode(404)->setBody('Invalid Audit ID');
        }

        // ---------------- DETAILS ----------------
        $rows = $db->table('alert_hse_audit_details d')
            ->select("d.*, q.audit_category_id")
            ->join("alert_audit_questions q", "q.question_id = d.question_id", "left")
            ->where("d.hse_audit_id", $auditId)
            ->orderBy("CAST(SUBSTRING_INDEX(d.audit_category_id, '.', 1) AS CHAR)", "ASC", false)
            ->orderBy("LENGTH(d.audit_category_id)", "ASC", false)
            ->orderBy("d.audit_category_id", "ASC", false)
            ->get()
            ->getResultArray();

        $results = [];

        foreach ($rows as $row) {
            $capa_json = json_decode($row['capa_json'] ?? '[]', true) ?: [];

            $finding = strtoupper(trim($row['finding'] ?? ''));
            $finding = in_array($finding, ['YES', 'NO', 'NA']) ? $finding : '';

            // ✅ ONLY FETCH CAPA FOR NO
            $capaValues = ($finding === 'NO')
                ? $this->extractJsonValues($capa_json, $finding)
                : [];

            // ✅ MAP KEYS (IMPORTANT)
            $mapped = [
                'Findings' => $capaValues['Findings'] ?? '',
                'Risk' => $capaValues['Risk'] ?? '',
                'Actions' => $capaValues['Actions'] ?? '',
                'Action Category' => $capaValues['Action Category'] ?? '',
                'UA-UC' => $capaValues['UA-UC'] ?? '',
                'Risk Severity' => $capaValues['Risk Severity'] ?? '',
                'Risk Probability' => $capaValues['Risk Probability'] ?? '',
                'Color Code' => $capaValues['Color Code'] ?? '',
                'Cost Type' => $capaValues['Cost Type'] ?? '',
                'Combined Risk Rating' => $capaValues['Combined Risk Rating'] ?? ''
            ];

            $results[] = [
                "sr_no" => $row['audit_category_id'] ?? '',
                "category" => $row['audit_category'] ?? '',
                "question" => $row['audit_question'] ?? '',
                "remark" => $row['remark'] ?? '',
                "attachment" => $row['attachment'] ?? '',
                "value" => $finding,
                "capa" => $mapped
            ];
        }

        // ---------------- SAFE FUNCTION ----------------
        $safe = fn($v) => htmlspecialchars((string) $v, ENT_QUOTES);

        // ---------------- HTML ----------------
        $html = '<html><head>
    <meta charset="UTF-8">
    <style>
        table { border-collapse: collapse; width:100%; }
        th { background:#1e1c77;color:#fff;border:1px solid #000;padding:6px; }
        td { border:1px solid #000;padding:5px; }
        .title { font-size:18px;font-weight:bold;text-align:center;margin-bottom:10px; }
        .section { background:#333;color:#fff;font-weight:bold;padding:6px; }
    </style>
    </head><body>';

        // ---------------- HEADER ----------------
        $html .= '<div class="title">HSE CAPA REPORT</div>';

        $html .= '<table>
        <tr><td><b>Audit No</b></td><td>' . $safe($master['audit_no']) . '</td></tr>
        <tr><td><b>Audit Name</b></td><td>' . $safe($master['audit_name']) . '</td></tr>
        <tr><td><b>Auditor</b></td><td>' . $safe($master['auditor_name']) . '</td></tr>
        <tr><td><b>Auditee</b></td><td>' . $safe($master['auditee_name']) . '</td></tr>
        <tr><td><b>Client</b></td><td>' . $safe($master['client_name']) . '</td></tr>
        <tr><td><b>Region</b></td><td>' . $safe($master['region']) . '</td></tr>
        <tr><td><b>Audit Date</b></td><td>' . $safe($master['audit_date']) . '</td></tr>
        <tr><td><b>Score</b></td><td>' . $safe($master['score']) . '</td></tr>
    </table><br>';

        // ---------------- TABLE ----------------
        $html .= '<table>
        <tr><td colspan="16" class="section">Audit Questions & CAPA</td></tr>
        <tr>
            <th>Sr No</th>
            <th>Category</th>
            <th>Audit Question</th>
            <th>Remark</th>
            <th>Attachment</th>
            <th>Value</th>
            <th>Findings</th>
            <th>Risk</th>
            <th>Actions</th>
            <th>Action Category</th>
            <th>UA/UC</th>
            <th>Severity</th>
            <th>Probability</th>
            <th>Color Code</th>
            <th>Cost Type</th>
            <th>Combined Rating</th>
        </tr>';

        // ---------------- ROWS ----------------
        foreach ($results as $r) {
            $attachment = !empty($r['attachment']) ? base_url($r['attachment']) : '';

            $html .= '<tr>
            <td>' . $safe($r['sr_no']) . '</td>
            <td>' . $safe($r['category']) . '</td>
            <td>' . $safe($r['question']) . '</td>
            <td>' . $safe($r['remark']) . '</td>
            <td>' . $safe($attachment) . '</td>
            <td>' . $safe($r['value']) . '</td>

            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Findings'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Risk'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Actions'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Action Category'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['UA-UC'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Risk Severity'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Risk Probability'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Color Code'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Cost Type'] : '') . '</td>
            <td>' . $safe($r['value'] == 'NO' ? $r['capa']['Combined Risk Rating'] : '') . '</td>
        </tr>';
        }

        $html .= '</table></body></html>';

        // ---------------- DOWNLOAD ----------------
        return service('response')
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="AuditWithCAPA-' . $auditId . '.xls"')
            ->setBody($html);
    }

    public function hse_autogrid_excel($hseAuditId)
    {
        $db = db_connect();

        /* ---------- CLEAN ANY PREVIOUS OUTPUT ---------- */
        while (ob_get_level()) {
            ob_end_clean();
        }
        ob_start();

        /* ---------- HEADERS ---------- */
        header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
        header("Content-Disposition: attachment; filename=HSE-AutoGrid-$hseAuditId.xls");
        header("Cache-Control: no-cache, no-store, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        /* ---------- FETCH AUDIT MASTER ---------- */
        $details = $db->table("alert_hse_audit_master")
            ->where("hse_audit_id", $hseAuditId)
            ->get()->getRowArray();

        if (!$details) {
            echo "Invalid Audit ID";
            exit;
        }

        /* ---------- FETCH AUDIT GRID DETAILS ---------- */
        $auditDetails = $db->table("alert_hse_audit_details")
            ->where("hse_audit_id", $hseAuditId)
            ->orderBy("id", "ASC")
            ->get()->getResultArray();

        /* ---------- CATEGORY & OVERALL COUNTS ---------- */
        $categoryStats = [];
        $overallStats = [
            "client_leased" => ["yes" => 0, "no" => 0, "na" => 0],
            "fm_leased" => ["yes" => 0, "no" => 0, "na" => 0],
            "office" => ["yes" => 0, "no" => 0, "na" => 0]
        ];

        foreach ($auditDetails as $row) {

            $cat = trim($row["question_name"]);

            if (!isset($categoryStats[$cat])) {
                $categoryStats[$cat] = [
                    "client_leased" => ["yes" => 0, "no" => 0, "na" => 0],
                    "fm_leased" => ["yes" => 0, "no" => 0, "na" => 0],
                    "office" => ["yes" => 0, "no" => 0, "na" => 0]
                ];
            }

            $answers = [
                "client_leased" => strtolower($row["client_leased"]),
                "fm_leased" => strtolower($row["fm_leased"]),
                "office" => strtolower($row["inplant"] ?? '')
            ];

            foreach ($answers as $k => $v) {
                if (!in_array($v, ['yes', 'no', 'na']))
                    $v = 'na';
                $categoryStats[$cat][$k][$v]++;
                $overallStats[$k][$v]++;
            }
        }

        /* ---------- PERCENTAGE CALCULATION ---------- */
        foreach ($categoryStats as $cat => &$ct) {
            foreach ($ct as $type => &$cnt) {
                $total = $cnt["yes"] + $cnt["no"] + $cnt["na"];
                $app = $total - $cnt["na"];
                $cnt["percentage"] = ($app > 0) ? round(($cnt["yes"] / $app) * 100, 2) : 0;
            }
        }

        foreach ($overallStats as $type => &$cnt) {
            $total = $cnt["yes"] + $cnt["no"] + $cnt["na"];
            $app = $total - $cnt["na"];
            $cnt["percentage"] = ($app > 0) ? round(($cnt["yes"] / $app) * 100, 2) : 0;
        }

        /* ===========================================================
           🟩 1. HEADER SECTION (MATCHES PDF)
        ============================================================ */
        echo "<table border='1' style='border-collapse:collapse; font-family:Arial;'>";

        echo "<tr>
                <td colspan='6' style='background:#1e1c77;color:white;font-size:20px;text-align:center;font-weight:bold;height:45px;'>
                    FM India Supply Chain Pvt Ltd – HSE Audit AutoGrid Report
                </td>
              </tr>";

        echo "<tr><th>Audit No</th><td>{$details['audit_no']}</td></tr>";
        echo "<tr><th>Audit Name</th><td>{$details['audit_name']}</td></tr>";
        echo "<tr><th>Auditor</th><td>{$details['auditor_name']}</td></tr>";
        echo "<tr><th>Auditee</th><td>{$details['auditee_name']}</td></tr>";
        echo "<tr><th>Client</th><td>{$details['client_name']}</td></tr>";
        echo "<tr><th>Region</th><td>{$details['region']}</td></tr>";
        echo "<tr><th>Audit Date</th><td>{$details['audit_date']}</td></tr>";
        echo "<tr><th>Score</th><td>{$details['score']}</td></tr>";

        echo "</table><br><br>";

        /* ===========================================================
           🟩 2. OVERALL SUMMARY (MATCHES PDF)
        ============================================================ */
        echo "<table border='1' style='border-collapse:collapse; font-family:Arial;'>";

        echo "<tr>
                <td colspan='6' style='background:#244062; color:white; font-size:18px; text-align:center; font-weight:bold;'>
                    Overall Summary of Compliance
                </td>
              </tr>";

        echo "<tr style='background:#d9e1f2;font-weight:bold;text-align:center;'>
                <th>Category</th>
                <th>Total Selected</th>
                <th>YES</th><th>NO</th><th>NA</th>
                <th>Score %</th>
              </tr>";

        foreach ($overallStats as $type => $st) {

            $score = $st["percentage"];
            $totalSelected = $st['yes'] + $st['no'] + $st['na'];

            echo "<tr style='text-align:center;'>
                    <td style='font-weight:bold;'>" . strtoupper($type) . "</td>
                    <td>{$totalSelected}</td>
                    <td>{$st['yes']}</td>
                    <td>{$st['no']}</td>
                    <td>{$st['na']}</td>
                    <td>{$score}%</td>
                  </tr>";
        }

        echo "</table><br><br>";

        /* ===========================================================
           🟩 3. CATEGORY-WISE SUMMARY (MATCHES PDF)
        ============================================================ */
        echo "<table border='1' style='border-collapse:collapse; font-family:Arial;'>";

        echo "<tr>
                <td colspan='6' style='background:#244062; color:white; font-size:18px;text-align:center;font-weight:bold;'>
                    Category-wise Header Score Summary
                </td>
              </tr>";

        echo "<tr style='background:#d9e1f2;font-weight:bold;text-align:center;'>
                <th>Sr No</th>
                <th>Category</th>
                <th>Client %</th>
                <th>FM %</th>
                <th>Office %</th>
              </tr>";

        $sr = 1;
        foreach ($categoryStats as $cat => $st) {
            echo "<tr>
                    <td>$sr</td>
                    <td style='text-align:left;padding-left:8px;'>$cat</td>
                    <td>{$st['client_leased']['percentage']}%</td>
                    <td>{$st['fm_leased']['percentage']}%</td>
                    <td>{$st['office']['percentage']}%</td>
                  </tr>";
            $sr++;
        }

        echo "</table>";

        ob_end_flush();
        exit;
    }

    //     public function auditNormalDetailsPdf($auditId)
// {
//    ini_set('memory_limit', '512M');
// set_time_limit(300);

    // $options = new \Dompdf\Options();
// $options->set('isRemoteEnabled', false);
// $options->set('isHtml5ParserEnabled', true);

    // $dompdf = new \Dompdf\Dompdf($options);
//     $db = db_connect();

    //     try {

    //         // ================= MASTER =================
//         $master = $db->table("alert_hse_audit_master")
//             ->where("hse_audit_id", $auditId)
//             ->get()
//             ->getRowArray();

    //         if (!$master) throw new \Exception("Audit not found");

    //         // ================= SITE CATEGORY =================
//         $siteCategories = $db->table('alert_hse_site_category')
//             ->where('status', 1)
//             ->get()
//             ->getResultArray();

    //         $categoryKeys = [];
//         foreach ($siteCategories as $cat) {
//             $key = strtolower(str_replace(' ', '_', trim($cat['site_category_name'])));
//             $categoryKeys[$key] = $cat['site_category_name'];
//         }

    //         // ================= FETCH DATA =================
//         $rows = $db->table("alert_hse_audit_details d")
//         ->select("d.*, d.audit_category_id, d.audit_category as question_name")
//         ->where("d.hse_audit_id", $auditId)
//         ->orderBy("CAST(SUBSTRING_INDEX(d.audit_category_id, '.', 1) AS CHAR)", "ASC")
//         ->orderBy("LENGTH(d.audit_category_id)", "ASC")
//         ->orderBy("d.audit_category_id", "ASC")
//         ->get()
//         ->getResultArray();

    //         // ================= GROUP DATA (PIVOT) =================
//         // ================= GROUP DATA (PIVOT) =================
// $grouped = [];

    // foreach ($rows as $row) {

    //     $rowCategoryId = trim($row['audit_category_id'] ?? '');
//     $siteKey = strtolower(preg_replace('/[^a-z0-9]+/i','_', trim($row['site_category'] ?? '')));
//     $siteKey = trim($siteKey, '_');

    //     $finding = strtoupper(trim($row['finding'] ?? ''));
//     if (!in_array($finding, ['YES','NO','NA'])) {
//         $finding = '';
//     }

    //     // ✅ Use audit_category_id as unique row key
//     if (!isset($grouped[$rowCategoryId])) {
//         $grouped[$rowCategoryId] = [
//             'audit_category_id' => $rowCategoryId,
//             'question_name'     => $row['question_name'] ?? '',
//             'audit_question'    => $row['audit_question'] ?? '',
//             'remark'            => $row['remark'] ?? '',
//             'attachment'        => $row['attachment'] ?? '',
//             'nc_after_photo'    => $row['nc_after_photo'] ?? '',
//             'nc_remark'         => $row['nc_remark'] ?? '',
//             'categories'        => array_fill_keys(array_keys($categoryKeys), '')
//         ];
//     }

    //     // map finding to correct category column
//     if (isset($grouped[$rowCategoryId]['categories'][$siteKey])) {
//         $grouped[$rowCategoryId]['categories'][$siteKey] = $finding;
//     }

    //     // keep latest non-empty remark / attachment
//     if (!empty($row['remark'])) {
//         $grouped[$rowCategoryId]['remark'] = $row['remark'];
//     }
//     if (!empty($row['nc_remark'])) {
//         $grouped[$rowCategoryId]['nc_remark'] = $row['nc_remark'];
//     }
//     if (!empty($row['attachment'])) {
//         $grouped[$rowCategoryId]['attachment'] = $row['attachment'];
//     }
//     if (!empty($row['nc_after_photo'])) {
//         $grouped[$rowCategoryId]['nc_after_photo'] = $row['nc_after_photo'];
//     }
// }

    // $audit_details = array_values($grouped);

    //         // ================= STATS =================
//         $categoryStats = [];
//         $overallStats = [];

    //         foreach ($categoryKeys as $key => $label) {
//             $overallStats[$key] = ['yes'=>0,'no'=>0,'na'=>0];
//         }

    //         foreach ($audit_details as $row) {

    //             $cat = $row['question_name'];

    //             if (!isset($categoryStats[$cat])) {
//                 foreach ($categoryKeys as $key => $label) {
//                     $categoryStats[$cat][$key] = ['yes'=>0,'no'=>0,'na'=>0];
//                 }
//             }

    //             foreach ($row['categories'] as $key => $val) {

    //                 if ($val == 'YES') {
//                     $categoryStats[$cat][$key]['yes']++;
//                     $overallStats[$key]['yes']++;
//                 } elseif ($val == 'NO') {
//                     $categoryStats[$cat][$key]['no']++;
//                     $overallStats[$key]['no']++;
//                 } else {
//                     $categoryStats[$cat][$key]['na']++;
//                     $overallStats[$key]['na']++;
//                 }
//             }
//         }

    //         // ================= CALCULATE % =================
//         $calc = function(&$stats){
//             foreach ($stats as &$s) {
//                 $total = $s['yes'] + $s['no'] + $s['na'];
//                 $applicable = $total - $s['na'];
//                 $s['percentage'] = $applicable ? round(($s['yes']/$applicable)*100,2) : 0;
//             }
//         };

    //         foreach ($categoryStats as &$cs) $calc($cs);
//         $calc($overallStats);

    //         $data = [
//             'details'        => $master,
//             'audit_details'  => $audit_details,
//             'categoryStats'  => $categoryStats,
//             'overallStats'   => $overallStats,
//             'categoryKeys'   => $categoryKeys
//         ];

    //     } catch (\Exception $e) {
//         echo $e->getMessage();
//         return;
//     }

    //     // ================= VIEW =================
//     $html = view("Audit/audit_details_pdf", $data);

    //     $dompdf->loadHtml($html);
//     $dompdf->setPaper('A4', 'landscape');
//     $dompdf->render();

    //     if (ob_get_length()) ob_end_clean();

    //     header('Content-Type: application/pdf');
//     header('Content-Disposition: attachment; filename="Audit-'.$auditId.'.pdf"');

    //     echo $dompdf->output();
// }

    public function auditNormalDetailsPdf($auditId)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('chroot', FCPATH);

        $dompdf = new \Dompdf\Dompdf($options);
        $db = db_connect();

        try {

            // ================= MASTER =================
            $master = $db->table("alert_hse_audit_master")
                ->where("hse_audit_id", $auditId)
                ->get()
                ->getRowArray();

            if (!$master) {
                throw new \Exception("Audit not found");
            }

            // ================= SITE CATEGORY =================
            $siteCategories = $db->table('alert_hse_site_category')
                ->where('status', 1)
                ->get()
                ->getResultArray();

            $categoryKeys = [];
            foreach ($siteCategories as $cat) {
                $key = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($cat['site_category_name'])));
                $key = trim($key, '_');
                $categoryKeys[$key] = $cat['site_category_name'];
            }

            // ================= FETCH DATA =================
            $rows = $db->table("alert_hse_audit_details d")
                ->select("d.*, q.audit_category_id, q.audit_category as question_name")
                ->join("alert_audit_questions q", "q.question_id = d.question_id", "left")
                ->where("d.hse_audit_id", $auditId)
                ->orderBy("CAST(SUBSTRING_INDEX(d.audit_category_id, '.', 1) AS CHAR)", "ASC", false)
                ->orderBy("LENGTH(d.audit_category_id)", "ASC", false)
                ->orderBy("d.audit_category_id", "ASC", false)
                ->get()
                ->getResultArray();

            // ================= GROUP DATA =================
            $grouped = [];

            foreach ($rows as $row) {

                $categoryId = trim($row['audit_category_id'] ?? '');
                $siteKey = strtolower(preg_replace('/[^a-z0-9]+/i', '_', trim($row['site_category'] ?? '')));
                $siteKey = trim($siteKey, '_');

                $finding = strtoupper(trim($row['finding'] ?? ''));
                if (!in_array($finding, ['YES', 'NO', 'NA'])) {
                    $finding = '';
                }

                // ✅ use audit_category_id as row key
                if (!isset($grouped[$categoryId])) {
                    $grouped[$categoryId] = [
                        'audit_category_id' => $categoryId,
                        'question_name' => $row['audit_category'] ?? '',
                        'audit_question' => $row['audit_question'] ?? '',
                        'remark' => $row['remark'] ?? '',
                        'attachment' => $row['attachment'] ?? '',
                        'nc_after_photo' => $row['nc_after_photo'] ?? '',
                        'nc_remark' => $row['nc_remark'] ?? '',
                        'categories' => array_fill_keys(array_keys($categoryKeys), '')
                    ];
                }

                if (isset($grouped[$categoryId]['categories'][$siteKey])) {
                    $grouped[$categoryId]['categories'][$siteKey] = $finding;
                }

                // keep latest non-empty values
                if (!empty($row['remark'])) {
                    $grouped[$categoryId]['remark'] = $row['remark'];
                }
                if (!empty($row['nc_remark'])) {
                    $grouped[$categoryId]['nc_remark'] = $row['nc_remark'];
                }
                if (!empty($row['attachment'])) {
                    $grouped[$categoryId]['attachment'] = $row['attachment'];
                }
                if (!empty($row['nc_after_photo'])) {
                    $grouped[$categoryId]['nc_after_photo'] = $row['nc_after_photo'];
                }
            }

            // ================= SORT CATEGORY IDS =================
            uksort($grouped, function ($a, $b) {
                $parse = function ($id) {
                    if (preg_match('/^([A-Z])(?:\.(\d+))?$/', $id, $m)) {
                        return [
                            'letter' => ord($m[1]),
                            'number' => isset($m[2]) ? (int) $m[2] : 0
                        ];
                    }
                    return ['letter' => 999, 'number' => 999];
                };

                $pa = $parse($a);
                $pb = $parse($b);

                if ($pa['letter'] === $pb['letter']) {
                    return $pa['number'] <=> $pb['number'];
                }
                return $pa['letter'] <=> $pb['letter'];
            });

            $audit_details = array_values($grouped);

            // ================= STATS =================
            $categoryStats = [];
            $overallStats = [];

            foreach ($categoryKeys as $key => $label) {
                $overallStats[$key] = ['yes' => 0, 'no' => 0, 'na' => 0];
            }

            foreach ($audit_details as $row) {

                $cat = $row['question_name'];

                if (!isset($categoryStats[$cat])) {
                    foreach ($categoryKeys as $key => $label) {
                        $categoryStats[$cat][$key] = ['yes' => 0, 'no' => 0, 'na' => 0];
                    }
                }

                foreach ($row['categories'] as $key => $val) {
                    if ($val == 'YES') {
                        $categoryStats[$cat][$key]['yes']++;
                        $overallStats[$key]['yes']++;
                    } elseif ($val == 'NO') {
                        $categoryStats[$cat][$key]['no']++;
                        $overallStats[$key]['no']++;
                    } else {
                        $categoryStats[$cat][$key]['na']++;
                        $overallStats[$key]['na']++;
                    }
                }
            }

            // ================= CALCULATE % =================
            $calc = function (&$stats) {
                foreach ($stats as &$s) {
                    $total = $s['yes'] + $s['no'] + $s['na'];
                    $applicable = $total - $s['na'];
                    $s['percentage'] = $applicable ? round(($s['yes'] / $applicable) * 100, 2) : 0;
                }
            };

            foreach ($categoryStats as &$cs)
                $calc($cs);
            $calc($overallStats);

            $data = [
                'details' => $master,
                'audit_details' => $audit_details,
                'categoryStats' => $categoryStats,
                'overallStats' => $overallStats,
                'categoryKeys' => $categoryKeys,
                'attendance' => $db->table("alert_hse_audit_attendance")
                                  ->where("hse_audit_id", $auditId)
                                  ->orderBy("row_no", "ASC")
                                  ->get()->getResultArray()
            ];

        } catch (\Exception $e) {
            echo $e->getMessage();
            return;
        }

        // ================= VIEW =================
        $html = view("Audit/audit_details_pdf", $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        if (ob_get_length())
            ob_end_clean();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Audit-' . $auditId . '.pdf"');

        echo $dompdf->output();
    }

    public function capaReportsPdf($auditId)
    {
        ini_set('memory_limit', '1G');
        set_time_limit(300);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $db = db_connect();

        try {
            // ================= MASTER =================
            $master = $db->table("alert_hse_audit_master")
                ->where("hse_audit_id", $auditId)
                ->get()
                ->getRowArray();

            if (!$master)
                throw new \Exception("Audit not found");

            // ================= ACTIVE CATEGORY =================
            // Detect active account type for CAPA
            $activeCategory = 'client_leased'; // Default
            if (!empty($master['account_type'])) {
                $activeCategory = strtolower(str_replace(' ', '_', $master['account_type']));
                if ($activeCategory == 'inplant')
                    $activeCategory = 'office';
            }

            // ================= FETCH DATA =================
            $rows = $db->table("alert_hse_audit_details d")
                ->select("d.*, q.audit_category_id, q.audit_category as question_name")
                ->join("alert_audit_questions q", "q.question_id = d.question_id", "left")
                ->where("d.hse_audit_id", $auditId)
                ->orderBy("CAST(SUBSTRING_INDEX(d.audit_category_id, '.', 1) AS CHAR)", "ASC", false)
                ->orderBy("LENGTH(d.audit_category_id)", "ASC", false)
                ->orderBy("d.audit_category_id", "ASC", false)
                ->get()
                ->getResultArray();

            $results = [];

            foreach ($rows as $row) {
                // Determine which JSON to use based on the row's own category
                $jsonCol = 'client_leased_json';
                $statusCol = 'client_leased';

                $rowSite = strtolower(str_replace(' ', '_', trim($row['site_category'] ?? '')));

                if ($rowSite === 'fm_leased') {
                    $jsonCol = 'fm_leased_json';
                    $statusCol = 'fm_leased';
                } elseif ($rowSite === 'office' || $rowSite === 'inplant') {
                    $jsonCol = 'inplant_json';
                    $statusCol = 'inplant';
                }

                $jsonData = json_decode($row[$jsonCol] ?? '[]', true) ?: [];
                $statusValue = strtoupper(trim($row[$statusCol] ?? ''));

                $results[] = [
                    'audit_category_id' => $row['audit_category_id'],
                    'client_leased_value' => $row['client_leased'] ?? '',
                    'fm_leased_value' => $row['fm_leased'] ?? '',
                    'office_value' => $row['inplant'] ?? '',
                    'capa_values' => $this->extractJsonValues($jsonData, $statusValue),
                    'full_data' => $row
                ];
            }

            $data = [
                'details' => $master,
                'results' => $results,
                'activeCategory' => $activeCategory
            ];

            $html = view("Audit/capa_report_pdf", $data);

            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            if (ob_get_length())
                ob_end_clean();

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="CapaReport-' . $auditId . '.pdf"');
            echo $dompdf->output();

        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
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

    public function save_details($id = null, $action = null)
    {
        $request = service('request');
        $postData = $request->getVar();

        if (isset($postData['honeypot'])) {
            // if($postData['honeypot'] != ""){
            //     $responce['status'] = "0";
            //     $responce['message'] = "Data insertion faild";
            //     die;
            // }
            unset($postData['honeypot']);
        }

        if (isset($id)) {
            $responce['message'] = "Data updation faild";
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
            $postData['status'] = "1";
            $responce['status'] = "0";
            $responce['message'] = "Data insertion faild";
            if ($this->BaseModel->insert($postData)) {
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }
        }
        $this->importExcel($id);
        echo json_encode($responce);
    }






    public function exportExcel($auditId)
    {
        while (ob_get_level() > 0)
            ob_end_clean();

        $db = db_connect();

        // ================= MASTER =================
        $master = $db->table("alert_hse_audit_master")
            ->where("hse_audit_id", $auditId)
            ->get()
            ->getRowArray();

        if (!$master) {
            return service('response')->setStatusCode(404)->setBody('Audit not found');
        }

        // ================= SITE CATEGORY =================
        $siteCategories = $db->table('alert_hse_site_category')
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $categoryKeys = [];
        foreach ($siteCategories as $cat) {
            $key = strtolower(str_replace(' ', '_', trim($cat['site_category_name'])));
            $categoryKeys[$key] = $cat['site_category_name'];
        }

        // ================= FETCH DATA =================
        $rows = $db->table("alert_hse_audit_details d")
            ->select("d.*")
            ->where("d.hse_audit_id", $auditId)
            ->orderBy("CAST(SUBSTRING_INDEX(d.audit_category_id, '.', 1) AS CHAR)", "ASC", false)
            ->orderBy("LENGTH(d.audit_category_id)", "ASC", false)
            ->orderBy("d.audit_category_id", "ASC", false)
            ->get()
            ->getResultArray();

        // ================= GROUP DATA (PIVOT) =================
        $grouped = [];
        foreach ($rows as $row) {
            $questionId = $row['question_id'];
            $siteKey = strtolower(str_replace(' ', '_', trim($row['site_category'] ?? '')));
            $finding = strtoupper(trim($row['finding'] ?? ''));

            if (!in_array($finding, ['YES', 'NO', 'NA']))
                $finding = '';

            if (!isset($grouped[$questionId])) {
                $grouped[$questionId] = [
                    'audit_category_id' => $row['audit_category_id'],
                    'question_name' => $row['audit_category'],
                    'audit_question' => $row['audit_question'],
                    'remark' => $row['remark'] ?? '',
                    'nc_remark' => $row['nc_remark'] ?? '',
                    'attachment' => $row['attachment'] ?? '',
                    'nc_after_photo' => $row['nc_after_photo'] ?? '',
                    'categories' => array_fill_keys(array_keys($categoryKeys), '')
                ];
            }

            if (isset($grouped[$questionId]['categories'][$siteKey])) {
                $grouped[$questionId]['categories'][$siteKey] = $finding;
            }
        }

        $audit_details = array_values($grouped);

        // ================= EXCEL GENERATION =================
        $filename = "HSE_Audit_" . $auditId . "_" . date('Ymd_His') . ".xls";

        $safe = function ($val) {
            return htmlspecialchars(trim((string) $val));
        };

        $html = '
        <html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
        <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
        <body>
        <style>
            .header { background-color: #1e1c77; color: white; font-weight: bold; text-align: center; border: 1px solid #000; }
            .section { background-color: #f0f0f0; font-weight: bold; border: 1px solid #000; }
            td { border: 1px solid #000; vertical-align: top; }
        </style>
        <table>
            <tr><td colspan="' . (5 + count($categoryKeys)) . '" style="font-size:18px; font-weight:bold; text-align:center;">HSE Audit Report</td></tr>
            <tr><td colspan="' . (5 + count($categoryKeys)) . '">&nbsp;</td></tr>
            <tr><td class="section">Audit No</td><td colspan="' . (4 + count($categoryKeys)) . '">' . $safe($master['audit_no']) . '</td></tr>
            <tr><td class="section">Audit Name</td><td colspan="' . (4 + count($categoryKeys)) . '">' . $safe($master['audit_name']) . '</td></tr>
            <tr><td class="section">Auditor</td><td colspan="' . (4 + count($categoryKeys)) . '">' . $safe($master['auditor_name']) . '</td></tr>
            <tr><td class="section">Client</td><td colspan="' . (4 + count($categoryKeys)) . '">' . $safe($master['client_name']) . '</td></tr>
            <tr><td class="section">Region</td><td colspan="' . (4 + count($categoryKeys)) . '">' . $safe($master['region']) . '</td></tr>
            <tr><td class="section">Date</td><td colspan="' . (4 + count($categoryKeys)) . '">' . $safe($master['audit_date']) . '</td></tr>
            <tr><td class="section">Score</td><td colspan="' . (4 + count($categoryKeys)) . '">' . $safe($master['score']) . '</td></tr>
            <tr><td colspan="' . (5 + count($categoryKeys)) . '">&nbsp;</td></tr>

        <tr><td colspan="' . (5 + count($categoryKeys)) . '" class="section" style="font-size:16px; font-weight:bold; text-align:left; background-color: #f2f2f2;">Auditee Attendance</td></tr>';
        
        $attendance = $db->table("alert_hse_audit_attendance")->where("hse_audit_id", $auditId)->orderBy("row_no", "ASC")->get()->getResultArray();
        
        if (!empty($attendance)) {
            $html .= '<tr>
                <td class="header" colspan="1">Sr No</td>
                <td class="header" colspan="' . (count($categoryKeys) > 0 ? 2 : 1) . '">Auditee Name</td>
                <td class="header" colspan="1">Opening Date</td>
                <td class="header" colspan="1">Opening Sign</td>
                <td class="header" colspan="1">Closing Date</td>
                <td class="header" colspan="' . max(1, count($categoryKeys)) . '">Closing Sign</td>
            </tr>';
            $idx = 1;
            foreach ($attendance as $att) {
                $openSign = !empty($att['opening_sign']) ? base_url($att['opening_sign']) : 'N/A';
                $closeSign = !empty($att['closing_sign']) ? base_url($att['closing_sign']) : 'N/A';
                $html .= '<tr>
                    <td colspan="1">' . $idx++ . '</td>
                    <td colspan="' . (count($categoryKeys) > 0 ? 2 : 1) . '">' . $safe($att['auditee_attendance']) . '</td>
                    <td colspan="1">' . (!empty($att['opening_date']) ? $safe(date('d-m-Y', strtotime($att['opening_date']))) : 'N/A') . '</td>
                    <td colspan="1">' . $safe($openSign) . '</td>
                    <td colspan="1">' . (!empty($att['closing_date']) ? $safe(date('d-m-Y', strtotime($att['closing_date']))) : 'N/A') . '</td>
                    <td colspan="' . max(1, count($categoryKeys)) . '">' . $safe($closeSign) . '</td>
                </tr>';
            }
        } else {
            $html .= '<tr><td colspan="' . (5 + count($categoryKeys)) . '">No Auditee Attendance Records Available</td></tr>';
        }

            $html .= '<tr><td colspan="' . (5 + count($categoryKeys)) . '">&nbsp;</td></tr>
            
            <tr>
                <td class="header">Sr No</td>
                <td class="header">Category</td>
                <td class="header">Audit Question</td>';

        foreach ($categoryKeys as $label) {
            $html .= '<td class="header">' . $safe($label) . '</td>';
        }

        $html .= '
                <td class="header">Remark</td>
                <td class="header">Attachment</td>
            </tr>';

        $currentLetter = '';
        foreach ($audit_details as $detail) {
            $catId = trim($detail['audit_category_id'] ?? '');
            $isHeader = (strlen($catId) === 1 && ctype_alpha($catId));

            if ($isHeader) {
                $currentLetter = $catId;
                $html .= '<tr class="section"><td colspan="' . (5 + count($categoryKeys)) . '"><b>' . $currentLetter . '. ' . $safe($detail['question_name']) . '</b></td></tr>';
            } else {
                $attachments = [];
                if (!empty($detail['attachment']))
                    $attachments[] = base_url($detail['attachment']);
                if (!empty($detail['nc_after_photo']))
                    $attachments[] = base_url($detail['nc_after_photo']);

                $remarks = [];
                if (!empty($detail['remark']))
                    $remarks[] = "Remark: " . $detail['remark'];
                if (!empty($detail['nc_remark']))
                    $remarks[] = "NC Remark: " . $detail['nc_remark'];

                $html .= '<tr>
                    <td>' . $safe($catId) . '</td>
                    <td>' . $safe($detail['question_name']) . '</td>
                    <td>' . $safe($detail['audit_question']) . '</td>';

                foreach ($categoryKeys as $key => $label) {
                    $val = $detail['categories'][$key] ?? '';
                    $color = ($val == 'YES') ? 'green' : ($val == 'NO' ? 'red' : 'gray');
                    $html .= '<td style="text-align:center; color:' . $color . '; font-weight:bold;">' . $safe($val) . '</td>';
                }

                $html .= '
                    <td>' . $safe(implode("\n", $remarks)) . '</td>
                    <td>' . implode("\n", $attachments) . '</td>
                </tr>';
            }
        }

        $html .= '</table></body></html>';

        return service('response')
            ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($html);
    }

    public function dummy_values_for_capa_report()
    {
        $db = db_connect();
        $query = $db->table('alert_hse_audit_details')->get();
        $rows = $query->getResultArray();
        $results = [];

        foreach ($rows as $row) {
            // Decode JSON data with error handling
            $client_leased_json = json_decode($row['client_leased_json'], true) ?: [];
            $inplant_json = json_decode($row['inplant_json'], true) ?: [];
            $fm_leased_json = json_decode($row['fm_leased_json'], true) ?: [];

            // Process client_leased values
            $client_leased_values = $this->extractJsonValues($client_leased_json, $row['client_leased']);

            // Process fm_leased values with error handling
            $fm_leased_values = $this->extractJsonValues($fm_leased_json, $row['fm_leased']);

            // Process office values
            $office_values = $this->extractJsonValues($inplant_json, $row['inplant'] ?? '');

            // Store results for this row in an array
            $results[] = [
                'id' => $row['id'],
                'client_leased' => $client_leased_values,
                'fm_leased' => $fm_leased_values,
                'office' => $office_values,
                'client_leased_value' => $row['client_leased'],
                'fm_leased_value' => $row['fm_leased'],
                'office_value' => $row['inplant'] ?? '',
            ];
        }

        // Display results in a table
        echo "<table border='1'>";
        echo "<tr>
            <th>ID</th>
            <th>Client Leased</th>
            <th>Findings</th>
            <th>Risk</th>
            <th>Actions</th>
            <th>Action Category</th>
            <th>UA-UC</th>
            <th>Risk Severity</th>
            <th>Risk Probability</th>
            <th>Color Code</th>
            <th>Cost Type</th>
            <th>Combined Risk Rating</th>
            <th>&nbsp;</th>
            <th>FM Leased</th>
            <th>Findings</th>
            <th>Risk</th>
            <th>Actions</th>
            <th>Action Category</th>
            <th>UA-UC</th>
            <th>Risk Severity</th>
            <th>Risk Probability</th>
            <th>Color Code</th>
            <th>Cost Type</th>
            <th>Combined Risk Rating</th>
            <th>&nbsp;</th>
            <th>Office</th>
            <th>Findings</th>
            <th>Risk</th>
            <th>Actions</th>
            <th>Action Category</th>
            <th>UA-UC</th>
            <th>Risk Severity</th>
            <th>Risk Probability</th>
            <th>Color Code</th>
            <th>Cost Type</th>
            <th>Combined Risk Rating</th>
            </tr>";

        foreach ($results as $result) {
            echo "<tr>";
            echo "<td>" . $result['id'] . "</td>";
            echo "<td>" . $result['client_leased_value'] . "</td>";

            // Display client_leased values
            foreach ($result['client_leased'] as $key => $value) {
                echo "<td>$value</td>";
            }
            echo "<td></td>";

            // Display fm_leased values
            echo "<td>" . $result['fm_leased_value'] . "</td>";
            foreach ($result['fm_leased'] as $key => $value) {
                echo "<td>$value</td>";
            }
            echo "<td></td>";

            // Display office values
            echo "<td>" . $result['office_value'] . "</td>";
            foreach ($result['office'] as $key => $value) {
                echo "<td>$value</td>";
            }
            echo "<td></td>";

            echo "</tr>";
        }

        echo "</table>";
    }

    // public function capa_report_excel($auditId)
    // {
    //     while (ob_get_level() > 0) ob_end_clean();

    //     $db = db_connect();

    //     // ================= MASTER =================
    //     $master = $db->table("alert_hse_audit_master")
    //         ->where("hse_audit_id", $auditId)
    //         ->get()
    //         ->getRowArray();

    //     if (!$master) {
    //         return service('response')->setStatusCode(404)->setBody('Audit not found');
    //     }

    //     // ================= FETCH DATA =================
    //     $rows = $db->table("alert_hse_audit_details d")
    //         ->where("d.hse_audit_id", $auditId)
    //         ->get()
    //         ->getResultArray();

    //     // ================= ACTIVE CATEGORY =================
    //     $activeCategory = 'client_leased';
    //     if (!empty($master['account_type'])) {
    //         $activeCategory = strtolower(str_replace(' ', '_', $master['account_type']));
    //         if ($activeCategory == 'inplant') $activeCategory = 'office';
    //     }

    //     $results = [];
    //     foreach ($rows as $row) {
    //         $jsonCol = 'client_leased_json';
    //         $statusCol = 'client_leased';

    //         $rowSite = strtolower(str_replace(' ', '_', trim($row['site_category'] ?? '')));
    //         if ($rowSite === 'fm_leased') {
    //             $jsonCol = 'fm_leased_json';
    //             $statusCol = 'fm_leased';
    //         } elseif ($rowSite === 'office' || $rowSite === 'inplant') {
    //             $jsonCol = 'inplant_json';
    //             $statusCol = 'inplant';
    //         }

    //         $jsonData = json_decode($row[$jsonCol] ?? '[]', true) ?: [];
    //         $statusValue = strtoupper(trim($row[$statusCol] ?? ''));

    //         $results[] = [
    //             'full_data' => $row,
    //             'status_value' => $statusValue,
    //             'capa_values' => $this->extractJsonValues($jsonData, $statusValue)
    //         ];
    //     }

    //     // ================= EXCEL GENERATION =================
    //     $filename = "HSE_CAPA_" . $auditId . "_" . date('Ymd_His') . ".xls";

    //     $safe = function($val) {
    //         return htmlspecialchars(trim((string)$val));
    //     };

    //     $html = '
    //     <html>
    //     <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"></head>
    //     <body>
    //     <style>
    //         .header { background-color: #1e1c77; color: white; font-weight: bold; text-align: center; border: 1px solid #000; }
    //         .section { background-color: #f0f0f0; font-weight: bold; border: 1px solid #000; }
    //         td { border: 1px solid #000; vertical-align: top; }
    //     </style>
    //     <table>
    //         <tr><td colspan="15" style="font-size:18px; font-weight:bold; text-align:center;">HSE CAPA Report</td></tr>
    //         <tr><td colspan="15">&nbsp;</td></tr>
    //         <tr><td class="section">Audit No</td><td colspan="14">'.$safe($master['audit_no']).'</td></tr>
    //         <tr><td class="section">Audit Name</td><td colspan="14">'.$safe($master['audit_name']).'</td></tr>
    //         <tr><td class="section">Auditor</td><td colspan="14">'.$safe($master['auditor_name']).'</td></tr>
    //         <tr><td class="section">Client</td><td colspan="14">'.$safe($master['client_name']).'</td></tr>
    //         <tr><td colspan="15">&nbsp;</td></tr>

    //         <tr>
    //             <td class="header">Sr No</td>
    //             <td class="header">Category</td>
    //             <td class="header">Audit Question</td>
    //             <td class="header">Remark</td>
    //             <td class="header">Attachment</td>
    //             <td class="header">Finding (YES/NO)</td>
    //             <td class="header">Findings</td>
    //             <td class="header">Risk</td>
    //             <td class="header">Actions</td>
    //             <td class="header">Action Category</td>
    //             <td class="header">UA-UC</td>
    //             <td class="header">Risk Severity</td>
    //             <td class="header">Risk Probability</td>
    //             <td class="header">Color Code</td>
    //             <td class="header">Cost Type</td>
    //             <td class="header">Combined Risk Rating</td>
    //         </tr>';

    //     foreach ($results as $res) {
    //         $row = $res['full_data'];
    //         $capa = $res['capa_values'];
    //         $status = $res['status_value'];

    //         $attachments = [];
    //         if (!empty($row['attachment'])) $attachments[] = base_url($row['attachment']);
    //         if (!empty($row['nc_after_photo'])) $attachments[] = base_url($row['nc_after_photo']);

    //         $remarks = [];
    //         if (!empty($row['remark'])) $remarks[] = "Remark: " . $row['remark'];
    //         if (!empty($row['nc_remark'])) $remarks[] = "NC Remark: " . $row['nc_remark'];

    //         $html .= '<tr>
    //             <td>'.$safe($row['audit_category_id']).'</td>
    //             <td>'.$safe($row['audit_category']).'</td>
    //             <td>'.$safe($row['audit_question']).'</td>
    //             <td>'.$safe(implode("\n", $remarks)).'</td>
    //             <td>'.implode("\n", $attachments).'</td>
    //             <td style="text-align:center; color:'.($status=='NO'?'red':'green').'; font-weight:bold;">'.$status.'</td>
    //             <td>'.$safe($capa['Findings'] ?? '').'</td>
    //             <td>'.$safe($capa['Risk'] ?? '').'</td>
    //             <td>'.$safe($capa['Actions'] ?? '').'</td>
    //             <td>'.$safe($capa['Action Category'] ?? '').'</td>
    //             <td>'.$safe($capa['UA-UC'] ?? '').'</td>
    //             <td>'.$safe($capa['Risk Severity'] ?? '').'</td>
    //             <td>'.$safe($capa['Risk Probability'] ?? '').'</td>
    //             <td>'.$safe($capa['Color Code'] ?? '').'</td>
    //             <td>'.$safe($capa['Cost Type'] ?? '').'</td>
    //             <td>'.$safe($capa['Combined Risk Rating'] ?? '').'</td>
    //         </tr>';
    //     }

    //     $html .= '</table></body></html>';

    //     return service('response')
    //         ->setHeader('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
    //         ->setHeader('Content-Disposition', 'attachment; filename="'.$filename.'"')
    //         ->setBody($html);
    // }

    private function extractJsonValues(array $json, string $finding): array
    {
        $map = [
            'findings' => 'Findings',
            'risk' => 'Risk',
            'actions' => 'Actions',
            'action_category' => 'Action Category',
            'ua_uc' => 'UA-UC',
            'risk_severity' => 'Risk Severity',
            'risk_probability' => 'Risk Probability',
            'color_code' => 'Color Code',
            'cost_type' => 'Cost Type',
            'combined_risk_rating' => 'Combined Risk Rating',
            'nc_type' => 'NC Type'
        ];

        $result = [];

        foreach ($map as $jsonKey => $label) {
            if (isset($json[$jsonKey])) {
                $result[$label] = $json[$jsonKey][strtolower($finding)] ?? '';
            } else {
                $result[$label] = '';
            }
        }

        return $result;
    }

    private function normalizeCapaJson($json)
    {
        $json = trim((string) $json);
        if ($json === '') {
            return '';
        }

        $decoded = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return '';
        }

        return json_encode($decoded);
    }

    private function isCategoryVAudit($mainCategory, $auditCategory, $questionText)
    {
        $mainCategory = strtolower(trim($mainCategory));
        $auditCategory = strtoupper(trim($auditCategory));

        // ✅ STRICT CHECK
        return (
            $mainCategory === 'ims documents review' &&
            preg_match('/^V\.\d+$/', $auditCategory)
        );
    }

    // changes on 8/11/25 by darsh: Get region by client name (AJAX endpoint) - same as OE perform audit
    public function get_region_by_client()
    {
        $request = service('request');
        $clientName = $request->getVar('client_name');

        if (empty($clientName)) {
            return $this->response->setJSON(['status' => 0, 'region' => '']);
        }

        $db = db_connect();
        // Use case-insensitive comparison with raw SQL for better performance
        $query = $db->query(
            "SELECT region FROM alert_hse_client_master WHERE LOWER(TRIM(client_name)) = ?) AND status = 1 LIMIT 1",
            [strtolower(trim($clientName)), strtolower(trim($clientName))]
        );

        $debug = [];
        $client = $query->getRowArray();
        $debug['query_result'] = $client;

        if ($client && !empty($client['region'])) {
            return $this->response->setJSON(['status' => 1, 'region' => trim($client['region']), 'debug' => $debug]);
        }

        return $this->response->setJSON(['status' => 0, 'region' => '', 'debug' => $debug]);
    }

    // changes on 20/02/26: Get locations by region and sub-category from alert_hse_client_master
    public function get_clients_by_region_and_category()
    {
        $request = service('request');
        $region = $request->getVar('region');
        $mainCategory = $request->getVar('main_category');
        $subCategory = $request->getVar('sub_category');

        $logFile = WRITEPATH . 'debug_log.txt';
        $logData = date('Y-m-d H:i:s') . " - Request: Region: $region, Main: $mainCategory, Sub: $subCategory" . PHP_EOL;

        $db = db_connect();

        // 1. Check if table exists and get columns
        try {
            $cols = $db->getFieldNames('alert_hse_client_master');
            $logData .= "Columns: " . implode(', ', $cols) . PHP_EOL;
        } catch (\Exception $e) {
            $logData .= "Error getting columns: " . $e->getMessage() . PHP_EOL;
        }

        // 2. Try a very simple query first to see if region matches ANYTHING
        $simpleQuery = $db->query("SELECT COUNT(*) as count FROM alert_hse_client_master WHERE LOWER(TRIM(region)) = ?", [strtolower(trim($region))]);
        $simpleCount = $simpleQuery->getRowArray()['count'] ?? 0;
        $logData .= "Count for region '$region': $simpleCount" . PHP_EOL;

        // 3. Final Query - changes on 21/02/26: Use location as client_id (no id column in table)
        $query = $db->query(
            "SELECT DISTINCT 
                hc.location as client_id,
                hc.client_name,
                hc.location as location_name,
                hc.region
             FROM alert_hse_client_master hc
             WHERE hc.status = 1
             AND LOWER(TRIM(hc.region)) = ?
             AND NOT EXISTS (
                 SELECT 1 FROM alert_hse_audit_master ham
                 WHERE (LOWER(TRIM(ham.location)) = LOWER(TRIM(hc.location)) OR LOWER(TRIM(ham.client_name)) = LOWER(TRIM(hc.location)))
                 AND LOWER(TRIM(ham.main_category)) = ?
                 AND LOWER(TRIM(ham.sub_category)) = ?
                 AND ham.status = 1
             )
             ORDER BY hc.location ASC",
            [
                strtolower(trim($region)),
                strtolower(trim($mainCategory)),
                strtolower(trim($subCategory))
            ]
        );

        $logData .= "SQL: " . $db->getLastQuery()->getQuery() . PHP_EOL;
        $clients = $query->getResultArray();
        $logData .= "Final Result Count: " . count($clients) . PHP_EOL;

        @file_put_contents($logFile, $logData, FILE_APPEND);

        if (!empty($clients)) {
            return $this->response->setJSON([
                'status' => 1,
                'clients' => $clients
            ]);
        }

        return $this->response->setJSON([
            'status' => 0,
            'clients' => [],
            'message' => 'No clients found matching the criteria'
        ]);
    }

    // Get site categories (status=1)
    public function get_categories_by_region()
    {
        $db = db_connect();

        $region = $this->request->getVar('region');

        if (!$region) {
            return $this->response->setJSON([
                'status' => 0,
                'categories' => []
            ]);
        }

        $categories = $db->table('alert_hse_client_master')
            ->select('DISTINCT(category) as category')
            ->where('region', $region)
            ->where('status', 1)
            ->orderBy('category', 'ASC')
            ->get()
            ->getResultArray();

        return $this->response->setJSON([
            'status' => 1,
            'categories' => $categories
        ]);
    }

    // Get sub categories from master table based on site category
    public function get_subcategories_by_region_and_category()
    {
        $request = service('request');
        $categoryName = $request->getVar('category');

        if (empty($categoryName)) {
            return $this->response->setJSON(['status' => 0, 'subcategories' => []]);
        }

        $db = db_connect();

        $cat = $db->table('alert_hse_site_category')
            ->where('site_category_name', $categoryName)
            ->get()->getRowArray();

        if ($cat) {
            $subs = $db->table('alert_hse_sub_category')
                ->select('sub_category_name as sub_category')
                ->where('site_category_id', $cat['site_category_id'])
                ->where('status', 1)
                ->orderBy('sub_category_name', 'ASC')
                ->get()->getResultArray();

            return $this->response->setJSON(['status' => 1, 'subcategories' => $subs]);
        }

        return $this->response->setJSON(['status' => 0, 'subcategories' => []]);
    }

    // changes on 21/02/26: Get clients by region, category and subcategory
    public function get_clients_by_region_category_subcategory()
    {
        $request = service('request');
        $region = $request->getVar('region');
        $category = $request->getVar('category');
        $subCategory = $request->getVar('sub_category');

        // Debug logging
        $logFile = WRITEPATH . 'hse_client_debug.log';
        $logData = date('Y-m-d H:i:s') . " - get_clients_by_region_category_subcategory called\n";
        $logData .= "  Region: " . ($region ?? 'NULL') . "\n";
        $logData .= "  Category: " . ($category ?? 'NULL') . "\n";
        $logData .= "  Sub Category: " . ($subCategory ?? 'NULL') . "\n";

        if (empty($region) || empty($category) || empty($subCategory)) {
            $logData .= "  ERROR: Missing required parameters\n";
            @file_put_contents($logFile, $logData, FILE_APPEND);
            return $this->response->setJSON([
                'status' => 0,
                'clients' => [],
                'message' => 'Missing required parameters: region, category, or sub_category',
                'debug' => [
                    'region' => $region,
                    'category' => $category,
                    'sub_category' => $subCategory
                ]
            ]);
        }

        $db = db_connect();

        // Normalize values for comparison
        $regionNormalized = strtolower(trim($region));
        $categoryNormalized = strtolower(trim($category));
        $subCategoryNormalized = strtolower(trim($subCategory));

        $logData .= "  Normalized values - Region: '$regionNormalized', Category: '$categoryNormalized', Sub Category: '$subCategoryNormalized'\n";

        try {
            // Return client_id, client_name, location with status=1
            $query = $db->query(
                "SELECT DISTINCT 
                    client_id,
                    client_name,
                    location,
                    cluster, 
                    account_manager
                 FROM alert_hse_client_master hc
                 WHERE LOWER(TRIM(region)) = ?
                 AND LOWER(TRIM(category)) = ?
                 AND LOWER(TRIM(sub_category)) = ?
                 AND status = 1
                 AND NOT EXISTS (
                    SELECT 1 FROM alert_hse_audit_master ham
                    WHERE ham.status = 1
                      AND LOWER(TRIM(ham.main_category)) = ?
                      AND LOWER(TRIM(ham.sub_category)) = ?
                      AND (
                          LOWER(TRIM(ham.location)) = LOWER(TRIM(hc.location))
                          OR LOWER(TRIM(ham.client_name)) = LOWER(TRIM(hc.location))
                          OR LOWER(TRIM(ham.client_name)) = LOWER(TRIM(hc.client_name))
                          OR LOWER(TRIM(ham.location)) = LOWER(TRIM(hc.client_name))
                      )
                 )
                 ORDER BY COALESCE(location, client_name) ASC",
                [
                    $regionNormalized,
                    $categoryNormalized,
                    $subCategoryNormalized,
                    $categoryNormalized,
                    $subCategoryNormalized
                ]
            );

            $clients = $query->getResultArray();
            $logData .= "  Query executed successfully. Found " . count($clients) . " clients\n";

            if (!empty($clients)) {
                $logData .= "  Returning clients list\n";
                @file_put_contents($logFile, $logData, FILE_APPEND);
                return $this->response->setJSON([
                    'status' => 1,
                    'clients' => $clients,
                    'count' => count($clients)
                ]);
            } else {
                // Check if there are any clients with the region and category (for debugging)
                $checkQuery = $db->query(
                    "SELECT COUNT(*) as total FROM alert_hse_client_master 
                     WHERE status = 1 AND LOWER(TRIM(region)) = ? AND LOWER(TRIM(category)) = ?",
                    [$regionNormalized, $categoryNormalized]
                );
                $checkResult = $checkQuery->getRowArray();
                $logData .= "  No clients found. Total clients with region and category: " . ($checkResult['total'] ?? 0) . "\n";
                @file_put_contents($logFile, $logData, FILE_APPEND);

                return $this->response->setJSON([
                    'status' => 0,
                    'clients' => [],
                    'message' => 'No clients found matching the criteria',
                    'debug' => [
                        'region' => $region,
                        'category' => $category,
                        'sub_category' => $subCategory,
                        'total_with_region_category' => $checkResult['total'] ?? 0
                    ]
                ]);
            }
        } catch (\Exception $e) {
            $logData .= "  ERROR: " . $e->getMessage() . "\n";
            @file_put_contents($logFile, $logData, FILE_APPEND);

            return $this->response->setJSON([
                'status' => 0,
                'clients' => [],
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }

    // Load audit questions dynamically from alert_audit_questions
    public function get_audit_questions_ajax()
    {
        if (!$this->request->isAJAX() && strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'json') === false) {
            return redirect()->to(base_url('Masters/Hse_audit'));
        }

        $site_category = $this->request->getVar('site_category');
        $sub_category = $this->request->getVar('sub_category');
        $audit_template_id = $this->request->getVar('audit_template_id');
        $hse_audit_id = $this->request->getVar('hse_audit_id');

        $db = \Config\Database::connect();

        $builder = $db->table('alert_audit_questions');
        $builder->where('status', 1);

        if (!empty($audit_template_id)) {
            $builder->where('audit_template_id', $audit_template_id);
        }

        if (!empty($site_category)) {
            $builder->where('audit_site_category_name', $site_category);
        }

        if (!empty($sub_category)) {
            $builder->where('audit_sub_category_name', $sub_category);
        }

        //$builder->orderBy('audit_category_id','ASC');

        $questions = $builder->get()->getResultArray();

        usort($questions, function($a, $b) {
            return strnatcmp($a['audit_category_id'] ?? '', $b['audit_category_id'] ?? '');
        });

        $prefill = [];

        if (!empty($hse_audit_id)) {

            $builder = $db->table('alert_hse_audit_details');
            $builder->where('hse_audit_id', $hse_audit_id);

            $details = $builder->get()->getResultArray();

            foreach ($details as $row) {
                // Use question_id as key if available, otherwise fallback to plain text key
                $qId = (isset($row['question_id']) && !empty($row['question_id'])) ? (int) $row['question_id'] : null;
                $textKey = ($row['audit_category'] ?? '') . '|' . ($row['audit_question'] ?? '');
                $key = $qId ?? $textKey;

                $siteCategories = $db->table("alert_hse_site_category")
                    ->where("status", 1)
                    ->get()
                    ->getResultArray();

                $rowPrefill = [];

                foreach ($siteCategories as $site) {

                    $slug = strtolower(
                        preg_replace('/[^a-z0-9]+/', '_', $site['site_category_name'])
                    );

                    $value = '';

                    if ($slug === 'client_leased') {
                        $value = $row['client_leased'] ?? '';
                    } elseif ($slug === 'fm_leased') {
                        $value = $row['fm_leased'] ?? '';
                    } elseif ($slug === 'office' || $slug === 'inplant') {
                        $value = $row['inplant'] ?? '';
                    }

                    $rowPrefill[$slug] = strtoupper($value);
                }

                $rowPrefill['remark'] = $row['remark'] ?? '';
                $rowPrefill['attachment'] = $row['attachment'] ?? '';
                $rowPrefill['finding'] = $row['finding'] ?? '';
                $rowPrefill['note'] = $row['note'] ?? '';
                $rowPrefill['nc_status'] = isset($row['nc_status']) ? (int) $row['nc_status'] : 0;
                $rowPrefill['audit_question'] = $row['audit_question'] ?? '';
                $rowPrefill['capa_json'] = $row['capa_json'] ?? '';

                $prefill[$key] = $rowPrefill;
            }
        }

        return $this->response->setJSON([
            'status' => 1,
            'questions' => $questions,
            'prefill' => $prefill
        ]);
    }


    public function get_capa_by_question()
    {
        $db = db_connect();
        $request = service('request');

        $audit_id = $request->getVar('audit_id');
        $category_id = $request->getVar('category_id');
        $question_id = $request->getVar('question_id');

        $capa_json = '';

        // First check in alert_hse_audit_details if it's an existing audit/reaudit
        if (!empty($audit_id)) {
            $builder = $db->table('alert_hse_audit_details')->where('hse_audit_id', $audit_id);
            if (!empty($question_id)) {
                $builder->where('question_id', $question_id);
            } elseif (!empty($category_id)) {
                $builder->where('audit_category_id', $category_id);
            }
            $row = $builder->get()->getRowArray();
            if ($row && !empty($row['capa_json'])) {
                $capa_json = $row['capa_json'];
            }
        }

        // If not found in details, check HSE Question Master (alert_audit_questions)
        if (empty($capa_json) && !empty($question_id)) {
            $qRow = $db->table('alert_audit_questions')
                ->where('question_id', $question_id)
                ->get()
                ->getRowArray();

            if ($qRow && !empty($qRow['capa_json'])) {
                $capa_json = $qRow['capa_json'];
            }
        }

        if (!empty($capa_json)) {
            return $this->response->setJSON([
                'status' => true,
                'data' => json_decode($capa_json, true)
            ]);
        } else {
            return $this->response->setJSON([
                'status' => false,
                'data' => []
            ]);
        }
    }

    public function get_misc_capa()
    {
        $db = db_connect();
        $request = service('request');

        $hse_id = $request->getVar('hse_id');
        $question_id = $request->getVar('question_id');
        $category_id = $request->getVar('category_id');

        $row = $db->table('alert_hse_audit_details')
            ->where('hse_audit_id', $hse_id)
            ->where('question_id', $question_id)
            ->where('audit_category_id', $category_id)
            ->get()
            ->getRowArray();

        if ($row && !empty($row['capa_json'])) {

            return $this->response->setJSON([
                'status' => true,
                'data' => json_decode($row['capa_json'], true)
            ]);

        } else {

            return $this->response->setJSON([
                'status' => false,
                'data' => []
            ]);
        }
    }
    public function sendHseAuditEmail($auditId, $type, $originalAuditId = null)
    {
        $db = db_connect();

        $master = $db->table('alert_hse_audit_master')
            ->where('hse_audit_id', $auditId)
            ->get()->getRowArray();

        if (!$master)
            return;

        $auditNo = $master['audit_no'] ?? '';
        $siteName = $master['client_name'] ?? '';
        $location = $master['location'] ?? '';
        $region = $master['region'] ?? '';
        $auditDate = isset($master['audit_date']) && !empty($master['audit_date']) ? date('d-M-Y', strtotime($master['audit_date'])) : '';
        $reportDate = isset($master['report_date']) && !empty($master['report_date']) ? date('d-M-Y', strtotime($master['report_date'])) : '';
        $auditorName = $master['auditor_name'] ?? '';

        $submittedBy = $auditorName;
        if (isset($_SESSION['user_name'])) {
            $submittedBy = $_SESSION['user_name'];
        }

        $toEmails = [];

        $clientDetails = $db->table('alert_hse_client_master')
            ->where('status', 1)
            ->groupStart()
            ->where('location', $location)
            ->orWhere('client_name', $siteName)
            ->groupEnd()
            ->get()->getRowArray();

        $accountManagerName = $master['account_manager'] ?? ($clientDetails['account_manager'] ?? '');
        $clusterManagerName = $master['cluster_name'] ?? ($clientDetails['cluster'] ?? '');

        if (!empty($accountManagerName)) {
            $amUser = $db->table('alert_users')
                ->where('user_name', $accountManagerName)
                ->where('user_designation', 'Account Manager')
                ->get()->getRowArray();
            if ($amUser && !empty($amUser['user_email'])) {
                $toEmails[] = $amUser['user_email'];
            }
        }

        if (!empty($clusterManagerName)) {
            $cmUser = $db->table('alert_users')
                ->where('user_name', $clusterManagerName)
                ->groupStart()
                ->where('user_designation', 'Cluster manager')
                ->orWhere('user_designation', 'Cluster Manager')
                ->groupEnd()
                ->get()->getRowArray();
            if ($cmUser && !empty($cmUser['user_email'])) {
                $toEmails[] = $cmUser['user_email'];
            }
        }

        $ccEmails = [];
        if (!empty($auditorName)) {
            $auditorUser = $db->table('alert_users')
                ->where('user_name', $auditorName)
                ->get()->getRowArray();
            if ($auditorUser && !empty($auditorUser['user_email'])) {
                $ccEmails[] = $auditorUser['user_email'];
            }
        }

        $toEmails = array_unique(array_filter($toEmails));
        $ccEmails = array_unique(array_filter($ccEmails));

        if (empty($toEmails))
            return;

        $data = [
            'audit_no' => $auditNo,
            'site_name' => $siteName,
            'region' => $region,
            'auditor_name' => $auditorName,
            'submitted_by' => $submittedBy,
            'next_audit_date' => $reportDate,
        ];

        $subject = "";
        $message = "";

        if ($type === 'perform') {
            $data['audit_date'] = $auditDate;

            $details = $db->table('alert_hse_audit_details')
                ->where('hse_audit_id', $auditId)
                ->where('audit_category_id !=', '')
                ->get()->getResultArray();

            $totalQuestions = 0;
            $yesCount = 0;
            $noCount = 0;
            $naCount = 0;
            $ncCount = 0;
            $rdCount = 0;

            $colorBlack = 0;
            $colorRed = 0;
            $colorYellow = 0;

            foreach ($details as $row) {
                if (preg_match('/^[A-Z]$/', $row['audit_category_id']))
                    continue;

                $finding = strtoupper(trim($row['finding'] ?? ''));
                if (in_array($finding, ['YES', 'NO', 'NA'])) {
                    $totalQuestions++;
                    if ($finding === 'YES')
                        $yesCount++;
                    if ($finding === 'NO') {
                        $noCount++;
                        $ncType = strtoupper(trim($row['nc_type'] ?? 'NC'));
                        if ($ncType === 'NC')
                            $ncCount++;
                        if ($ncType === 'RD')
                            $rdCount++;

                        $capaJsonStr = $row['capa_json'] ?? '';
                        $capaValues = $this->extractJsonValues(json_decode($capaJsonStr, true) ?: [], 'NO');

                        $colorCode = strtoupper(trim($capaValues['Color Code'] ?? ($capaValues['Color code'] ?? ($capaValues['Color'] ?? ''))));
                        if (empty($colorCode)) {
                            $colorCode = strtoupper(trim($capaValues['Risk'] ?? ($capaValues['Risk Severity'] ?? '')));
                        }

                        if ($colorCode === 'BLACK')
                            $colorBlack++;
                        elseif ($colorCode === 'RED')
                            $colorRed++;
                        elseif ($colorCode === 'YELLOW')
                            $colorYellow++;
                    }
                    if ($finding === 'NA')
                        $naCount++;
                }
            }

            $score = 0;
            $applicableQuestions = $totalQuestions - $naCount;
            if ($applicableQuestions > 0) {
                $score = round(($yesCount / $applicableQuestions) * 100, 2);
            }

            $data['score'] = $score;
            $data['total_questions'] = $totalQuestions;
            $data['yes_count'] = $yesCount;
            $data['no_count'] = $noCount;
            $data['na_count'] = $naCount;
            $data['nc_count'] = $ncCount;
            $data['rd_count'] = $rdCount;

            $data['color_black'] = $colorBlack;
            $data['color_red'] = $colorRed;
            $data['color_yellow'] = $colorYellow;

            $subject = "Perform Audit Successfully Submitted - " . $auditNo;
            $message = view('Emails/hse_perform_audit', $data);

        } elseif ($type === 'reaudit') {
            $data['reaudit_date'] = $auditDate;

            $prevNcCount = 0;
            $prevRdCount = 0;

            if ($originalAuditId) {
                $prevDetails = $db->table('alert_hse_audit_details')
                    ->where('hse_audit_id', $originalAuditId)
                    ->get()->getResultArray();
                foreach ($prevDetails as $row) {
                    if (preg_match('/^[A-Z]$/', $row['audit_category_id']))
                        continue;
                    if (strtoupper(trim($row['finding'])) === 'NO') {
                        $ncType = strtoupper(trim($row['nc_type'] ?? 'NC'));
                        if ($ncType === 'NC')
                            $prevNcCount++;
                        if ($ncType === 'RD')
                            $prevRdCount++;
                    }
                }
            }

            $openNc = 0;
            $closedNc = 0;
            $openRd = 0;
            $closedRd = 0;

            $currDetails = $db->table('alert_hse_audit_details')
                ->where('hse_audit_id', $auditId)
                ->get()->getResultArray();

            foreach ($currDetails as $row) {
                if (preg_match('/^[A-Z]$/', $row['audit_category_id']))
                    continue;
                if (strtoupper(trim($row['finding'])) === 'NO') {
                    $ncType = strtoupper(trim($row['nc_type'] ?? 'NC'));
                    $status = (int) ($row['nc_status'] ?? 0);
                    $isClosed = ($status === 3);

                    if ($ncType === 'NC') {
                        if ($isClosed)
                            $closedNc++;
                        else
                            $openNc++;
                    }
                    if ($ncType === 'RD') {
                        if ($isClosed)
                            $closedRd++;
                        else
                            $openRd++;
                    }
                }
            }

            $totalPrev = $prevNcCount + $prevRdCount;
            $totalClosed = $closedNc + $closedRd;
            $closurePercentage = $totalPrev > 0 ? round(($totalClosed / $totalPrev) * 100, 2) : 100;

            $data['prev_nc_count'] = $prevNcCount;
            $data['prev_rd_count'] = $prevRdCount;
            $data['open_nc'] = $openNc;
            $data['closed_nc'] = $closedNc;
            $data['open_rd'] = $openRd;
            $data['closed_rd'] = $closedRd;
            $data['closure_percentage'] = $closurePercentage;

            $subject = "HSE Reaudit Successfully Submitted - " . $auditNo;
            $message = view('Emails/hse_reaudit', $data);
        }

        if (!empty($message)) {
            helper('email_service');
            //$to = 'smita.tikone@unitglo.com'; // Keeping hardcoded to from original
            sendSystemEmail($toEmails, $subject, $message, [], $ccEmails);
        }

    }
    
    // ==========================================
    // AUTO SAVE DRAFT APIs
    // ==========================================
    public function upload_draft_attachment()
    {
        $file = $this->request->getFile('file');
        $auditNo = $this->request->getVar('audit_no') ?: 'DRAFT_' . time();
        
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $folderPath = FCPATH . 'uploads/audit_files/' . $auditNo . '/drafts/';
            if (!is_dir($folderPath)) {
                mkdir($folderPath, 0777, true);
            }
            $newFileName = $auditNo . '-draft-' . $file->getRandomName();
            $file->move($folderPath, $newFileName);
            
            $path = 'uploads/audit_files/' . $auditNo . '/drafts/' . $newFileName;
            return $this->response->setJSON(['status' => 1, 'path' => $path]);
        }
        return $this->response->setJSON(['status' => 0, 'message' => 'Invalid file']);
    }

    public function save_draft()
    {
        $request = \Config\Services::request();
        $userId = session()->get('login_id');
        
        $draftType = $request->getVar('draft_type');
        $auditTemplateId = $request->getVar('audit_template_id') ?: null;
        $originalHseAuditId = $request->getVar('original_hse_audit_id') ?: null;
        $clientName = $request->getVar('client_name');
        $location = $request->getVar('location');
        $mainCategory = $request->getVar('main_category');
        $subCategory = $request->getVar('sub_category');
        
        $draftData = $request->getVar('draft_data');
        
        $userName = session()->get('user_name') ?: (session()->get('name') ?: '');
        if ($userName === '') {
            $userName = 'Super Admin'; // Fallback for super admin testing
        }

        if (empty($draftData)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Missing data']);
        }
        
        // Basic requirement for 'perform' draft type
        // Removed context block so we just save a single draft per user per perform audit

        $db = \Config\Database::connect();
        $builder = $db->table('alert_hse_audit_drafts');

        $builder->where('user_name', $userName)
                ->where('draft_type', $draftType);
                
        if ($draftType !== 'perform') {
            $builder->where('original_hse_audit_id', $originalHseAuditId);
        }

        $existing = $builder->get()->getRowArray();

        $currentTime = date('Y-m-d H:i:s');
        
        if ($existing) {
            // Merge existing data with new partial data
            $existingData = json_decode($existing['draft_data'], true) ?: [];
            $newData = json_decode($draftData, true) ?: [];
            $mergedData = array_merge($existingData, $newData);
            
            $db->table('alert_hse_audit_drafts')
               ->where('id', $existing['id'])
               ->update([
                   'audit_template_id' => $auditTemplateId,
                   'client_name' => $clientName,
                   'location' => $location,
                   'main_category' => $mainCategory,
                   'sub_category' => $subCategory,
                   'draft_data' => json_encode($mergedData),
                   'updated_at' => $currentTime
               ]);
        } else {
            $db->table('alert_hse_audit_drafts')->insert([
                'user_id' => $userId ?: 0,
                'user_name' => $userName,
                'draft_type' => $draftType,
                'audit_template_id' => $auditTemplateId,
                'original_hse_audit_id' => $originalHseAuditId,
                'client_name' => $clientName,
                'location' => $location,
                'main_category' => $mainCategory,
                'sub_category' => $subCategory,
                'draft_data' => $draftData,
                'updated_at' => $currentTime
            ]);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => 'Draft saved', 'timestamp' => $currentTime]);
    }

    public function get_draft()
    {
        $request = \Config\Services::request();
        $userName = session()->get('user_name') ?: (session()->get('name') ?: 'Super Admin');
        
        $draftType = $request->getVar('draft_type');
        $originalHseAuditId = $request->getVar('original_hse_audit_id') ?: null;
        $clientName = $request->getVar('client_name');
        $location = $request->getVar('location');
        $mainCategory = $request->getVar('main_category');
        $subCategory = $request->getVar('sub_category');

        $db = \Config\Database::connect();
        $builder = $db->table('alert_hse_audit_drafts')
                    ->where('user_name', $userName)
                    ->where('draft_type', $draftType);
                    
        if ($draftType !== 'perform') {
            $builder->where('original_hse_audit_id', $originalHseAuditId);
        }

        $draft = $builder->get()->getRowArray();

        if ($draft) {
            return $this->response->setJSON(['status' => 'success', 'data' => json_decode($draft['draft_data'], true), 'updated_at' => $draft['updated_at']]);
        }

        return $this->response->setJSON(['status' => 'empty']);
    }

    private function delete_draft($context = null)
    {
        $userName = session()->get('user_name') ?: (session()->get('name') ?: 'Super Admin');
        
        $db = \Config\Database::connect();
        $builder = $db->table('alert_hse_audit_drafts')
               ->where('user_name', $userName)
               ->where('draft_type', $context['draft_type'] ?? 'perform');
               
        if (($context['draft_type'] ?? 'perform') !== 'perform') {
            $builder->where('original_hse_audit_id', $context['original_hse_audit_id'] ?? 0);
        }
        $builder->delete();
    }
}
