<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
require APPPATH . '/ThirdParty/dompdf/autoload.inc.php';
use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Reaudit extends BaseController
{

    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct()
    {
        helper("form");
        helper('hse_acl_helper');
        helper('designation_acl_helper');
        $db = null;
        $db['table'] = 'alert_final_structured_audit';
        $db['allowedFields'] = ['audit_no', 'audit_name', 'reaudit', 'auditor_name', 'auditee_name', 'region', 'audit_date', 'completion_date', 'client_name', 'location', 'zone', 'audit_score', 'audit_revision_no', 'final_remark', 'audit_template_id', 'audit_by_user_id', 'action_by_user_id', 'status'];
        $db['primaryKey'] = "structured_audit_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }
    // changes on 5/11/25 by darsh: Add type filters (OE, Normal, HSE) for old entries
    public function index($type = 'OE')
    {
        $data = [];

        $type = strtoupper(trim($type));
        if (!in_array($type, ['OE', 'NORMAL', 'HSE'])) {
            $type = 'OE';
        }

        $tdata['title'] = "";
        // $tdata['button_name']="Create Audit";
        // $tdata['button_id']="user_modal";

        // Dynamic columns by type
        if ($type === 'NORMAL') {
            $tdata['display_contents'] = [
                "normal_audit_id" => "ID",
                "audit_no" => "Audit No",
                "audit_name" => "Audit Name",
                "audit_template_id" => "Template Id",
                "auditor_name" => "Auditor Name",
                "auditee_name" => "Auditee Name",
                "cluster_name" => "Cluster Name",
                "client_manager_name" => "Client Manager Name",
                "client_name" => "Account Name",
                "region" => "Region",
                "audit_score" => "Score",
                "final_remark" => "Final Remark",
                "audit_by_user_id" => "Audit User Id",
                "action" => "Action"
            ];
        } else if ($type === 'HSE') {
            $tdata['display_contents'] = [
                "hse_audit_id" => "ID",
                "audit_no" => "Audit No",
                "audit_name" => "Audit Name",
                "audit_template_id" => "Template Id",
                "auditor_name" => "Auditor Name",
                "auditee_name" => "Auditee Name",
                "cluster_name" => "Cluster Name",
                "account_manager" => "Account Manager",
                "client_name" => "Account Name",
                "audit_date" => "Audit Date",
                "report_date" => "Next Date",
                "region" => "Region",
                "score" => "Score",
                // "perform_audit_by" => "Perform Audit By",
                "action" => "Action"
            ];
        } else {
            $tdata['display_contents'] = [
                "structured_audit_id" => "ID",
                "audit_no" => "Audit No",
                "audit_name" => "Audit Name",
                "audit_template_id" => "Template Id",
                "auditor_name" => "Auditor Name",
                "auditee_name" => "Auditee Name",
                "cluster_name" => "Cluster Name",
                "client_manager_name" => "Client Manager Name",
                "client_name" => "Account Name",
                "audit_date" => "Audit Date",
                "next_date" => "Next Audit Date",
                "region" => "Region",
                "audit_score" => "Score",
                "final_remark" => "Final Remark",
                // "audit_by_user_id" => "Audit User Id",
                // "action_by_user_id" => "Action User Id",
                "action" => "Action"
            ];
        }

        if (isClusterManager()) {
            unset($tdata['display_contents']['action']);
        }

        $data['ajax_url'] = base_url("Masters/Reaudit/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Reaudit/table_ajax/" . $type);

        // Simple filter buttons like Audit Template (UI-level)
        $data['table_filter_buttons'] = (
            '<div class="d-flex" style="gap:8px;">'
            . '<a class="btn btn-sm ' . ($type === 'OE' ? 'btn-primary' : 'btn-light-primary') . '" href="' . base_url('Masters/Reaudit/index/OE') . '">OE</a>'
            //. '<a class="btn btn-sm ' . ($type === 'NORMAL' ? 'btn-primary' : 'btn-light-primary') . '" href="' . base_url('Masters/Reaudit/index/NORMAL') . '">Normal</a>'
            . '<a class="btn btn-sm ' . ($type === 'HSE' ? 'btn-primary' : 'btn-light-primary') . '" href="' . base_url('Masters/Reaudit/index/HSE') . '">HSE</a>'
            . '</div>'
        );
        // Render top filter buttons row
        $buttons = '<div class="row g-5 g-xl-8" style="margin-bottom:12px;">'
            . '<div class="col-md-4"><a href="' . base_url('Masters/Reaudit/index/OE') . '" class="card ' . ($type === 'OE' ? 'bg-primary' : 'bg-info') . ' hoverable mb-xl-8"><div class="card-body" style="padding: 1rem 2.25rem;"><div class="fw-semibold text-white">OE</div></div></a></div>'
            . '<div class="col-md-4"><a href="' . base_url('Masters/Reaudit/index/HSE') . '" class="card ' . ($type === 'HSE' ? 'bg-dark' : 'bg-dark') . ' hoverable mb-xl-8"><div class="card-body" style="padding: 1rem 2.25rem;"><div class="fw-semibold text-gray-100">HSE</div></div></a></div>'
            //. '<div class="col-md-4"><a href="' . base_url('Masters/Reaudit/index/NORMAL') . '" class="card ' . ($type === 'NORMAL' ? 'bg-warning' : 'bg-warning') . ' hoverable mb-xl-8"><div class="card-body" style="padding: 1rem 2.25rem;"><div class="fw-semibold text-white">Normal</div></div></a></div>'
            . '</div>';

        $data['table'] = $buttons . view("Layout/table-view", $tdata);
        // $data['table'] ="";
        return view("Layout/table-view-2", $data);

    }

    public function table_ajax($type = 'OE')
    {
        $type = strtoupper(trim($type));
        $db = db_connect();

        if ($type === 'NORMAL') {
            // ðŸ”¹ Normal Audit old entries (reaudit > 0)
            try {
                $builder = $db->table('alert_normal_audit');
                $builder->where('reaudit >', 0);
                
                $aclWhere = getOEAuditACLWhere('alert_normal_audit', 'Normal');
                if (!empty($aclWhere)) {
                    $builder->where("1=1 " . $aclWhere, null, false);
                }

                $builder->orderBy('normal_audit_id', 'DESC');
                $tdata['table_data'] = $builder->get()->getResultArray();
            } catch (\Throwable $e) {
                $tdata['table_data'] = [];
            }

            foreach ($tdata['table_data'] as $key => $row) {
                $pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Audit_final_structure/normalAuditViewDetailsPdf/" . $row['normal_audit_id']) . '"\' 
                        class="btn btn-icon btn-primary" title="PDF">
                            <i class="fa fa-file-pdf"></i>
                        </button>';
                $tdata['table_data'][$key]['action'] = $pdf;
            }
        } elseif ($type === 'HSE') {

            try {
                $params = [];

                $sql = "
                            SELECT 
                                a.hse_audit_id,
                                a.audit_no,
                                a.audit_name,
                                a.audit_template_id,
                                a.auditor_name,
                                a.auditee_name,
                                a.client_name,
                                a.audit_date,
                                a.template_date,
                                a.next_date,
                                a.report_date,
                                a.region,
                                a.location,
                                a.score,
                                a.perform_audit_by,
                                a.status,
                                a.default_date,
                                a.update_date,
                                a.cluster_name,
                                a.account_manager
                            FROM alert_hse_audit_master a
                            INNER JOIN (
                                SELECT audit_no, MAX(hse_audit_id) AS latest_id
                                FROM alert_hse_audit_master
                                GROUP BY audit_no
                            ) m ON a.audit_no = m.audit_no
                            WHERE a.hse_audit_id <> m.latest_id
                        ";

                // Apply HSE Audit ACL
                $sql .= getHSEAuditACLWhere('a');

                $sql .= " ORDER BY a.hse_audit_id DESC";

                $rows = $db->query($sql, $params)->getResultArray();

            } catch (\Throwable $e) {
                log_message('error', 'HSE old entries query failed: ' . $e->getMessage());
                $rows = [];
            }

            $tdata['table_data'] = $rows;

            foreach ($tdata['table_data'] as $key => $row) {

                $pdf = '<button onclick=\'window.location.href="'
                    . base_url("Masters/Hse_audit/auditNormalDetailsPdf/" . $row['hse_audit_id'])
                    . '"\' class="btn btn-icon btn-primary" title="HSE Audit PDF">
                                <i class="fa fa-file-pdf"></i>
                            </button>';

                $combinedExcel = '<a class="btn btn-warning btn-sm mt-1" 
                                href="' . base_url("Masters/Reaudit/exportCombinedExcelSheets/" . $row['hse_audit_id']) . '">
                                Combined Excel
                                </a>';

                $view_attendance = '<button onclick=\'viewAttendanceModal(' . $row['hse_audit_id'] . ')\' class="btn btn-icon btn-info mt-1" title="View Attendance">
                                <i class="fa fa-eye"></i>
                            </button>';

                $tdata['table_data'][$key]['action'] = $pdf . " " . $combinedExcel . " " . $view_attendance;
            }
        } else {
            // ðŸ”¹ OE old entries
            try {
                $builder = $db->table('alert_final_structured_audit');
                $builder->where('reaudit >', 0);
                
                $aclWhere = getOEAuditACLWhere('alert_final_structured_audit', 'OE');
                if (!empty($aclWhere)) {
                    $builder->where("1=1 " . $aclWhere, null, false);
                }
                
                $builder->orderBy('structured_audit_id', 'DESC');
                $tdata['table_data'] = $builder->get()->getResultArray();
            } catch (\Throwable $e) {
                $tdata['table_data'] = [];
            }

            foreach ($tdata['table_data'] as $key => $row) {
                $pdf = '<button onclick=\'window.location.href="' . base_url("Masters/Reaudit/auditViewDetailsPdf/" . $row['structured_audit_id']) . '"\' 
                        class="btn btn-icon btn-primary" title="PDF">
                            <i class="fa fa-file-pdf"></i>
                        </button>
                        ';


                $tdata['table_data'][$key]['action'] = $pdf;
            }
        }

        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);
    }

    public function auditViewDetailsPdf($auditId)
    {
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', true);

        // Instantiate Dompdf
        $dompdf = new Dompdf($options);

        $db = db_connect();

        // Fetch LMRA details
        // 	$where['audit_details_id']=$auditId;

        $data['details'] = $db->table("alert_final_structured_audit")->join("alert_audit_template", "alert_final_structured_audit.audit_template_id=alert_audit_template.audit_template_id", "left")->where("alert_final_structured_audit.structured_audit_id", $auditId)->select("alert_final_structured_audit.*,alert_audit_template.audit_name,alert_audit_template.auditor_name,alert_audit_template.date,alert_audit_template.auditee_name,alert_audit_template.region,alert_audit_template.score")->get()->getResultArray();
        $data['audit_details'] = $db->table("alert_final_structured_audit_details")->where("structured_audit_id", $auditId)->get()->getResultArray();
        $data['attendance'] = $db->table("alert_hse_audit_attendance")->where("hse_audit_id", $auditId)->orderBy("row_no", "ASC")->get()->getResultArray();

        $dompdf = new Dompdf();

        // return view("Audit/perform_audit_view_pdf", $data);
        $html = view("Audit/perform_audit_view_pdf", $data);

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
        // Output the generated PDF (force download)
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="Audit-' . $auditId . '.pdf"');

        // Stream the PDF to the browser

        echo $dompdf->output();

        // Set headers for the PDF
        // header("Content-Type: application/pdf");
        // header('Content-Disposition: inline; filename="AuditReport.pdf"');

        // Output the generated PDF to the browser
        //  $dompdf->output();

    }

    public function export_excel_hse($auditId)
    {
        // REMOVE ALL previous output (fix ERR_INVALID_RESPONSE)
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $db = db_connect();

        // Fetch master
        $details = $db->table("alert_hse_audit_master")
            ->where("hse_audit_id", $auditId)
            ->get()
            ->getRowArray();

        if (!$details) {
            echo "Invalid Audit ID";
            exit;
        }

        // Fetch details
        $rows = $db->table("alert_hse_audit_details")
            ->where("hse_audit_id", $auditId)
            ->get()
            ->getResultArray();

        // File name
        $filename = "HSE_Audit_Report_" . $auditId . ".xls";

        // SEND CLEAN HEADERS
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Cache-Control: no-store, no-cache, must-revalidate");
        header("Pragma: no-cache");
        header("Expires: 0");

        echo '<html><head>';
        echo '
        <style>
            body { font-family: Arial; }

            table { border-collapse: collapse; width: 100%; }

            th {
                background: #1e1c77; 
                color: #fff;
                border: 1px solid #000;
                padding: 6px;
                font-weight: bold;
                text-align: center;
            }

            td {
                border: 1px solid #000;
                padding: 5px;
                vertical-align: top;
            }

            .title {
                font-size: 20px;
                font-weight: bold;
                padding: 10px;
                background: #e8e8e8;
                border: 2px solid #000;
                text-align: center;
            }

            .header-row td {
                background: #d9edf7;
                font-weight: bold;
            }

            .section-header {
                background: #444;
                color: #fff;
                font-weight: bold;
                padding: 6px;
            }
        </style>';

        echo '</head><body>';

        // TITLE
        echo '<div class="title">HSE AUDIT REPORT</div><br>';

        // MASTER TABLE
        echo '<table>';
        echo '<tr class="header-row"><td>Audit No</td><td>' . $details['audit_no'] . '</td></tr>';
        echo '<tr class="header-row"><td>Audit Name</td><td>' . $details['audit_name'] . '</td></tr>';
        echo '<tr class="header-row"><td>Auditor</td><td>' . $details['auditor_name'] . '</td></tr>';
        echo '<tr class="header-row"><td>Auditee</td><td>' . $details['auditee_name'] . '</td></tr>';
        echo '<tr class="header-row"><td>Client</td><td>' . $details['client_name'] . '</td></tr>';
        echo '<tr class="header-row"><td>Region</td><td>' . $details['region'] . '</td></tr>';
        echo '<tr class="header-row"><td>Audit Date</td><td>' . $details['audit_date'] . '</td></tr>';
        echo '<tr class="header-row"><td>Score</td><td>' . $details['score'] . '</td></tr>';
        echo '<tr class="header-row"><td>Performed By</td><td>' . $details['perform_audit_by'] . '</td></tr>';
        echo '</table><br>';

        // DETAILS TABLE
        echo '<table>';
        echo '<tr class="section-header"><td colspan="16">Audit Checklist</td></tr>';

        echo '<tr>
                <th>Sr No</th>
                <th>Category</th>
                <th>Audit Question</th>
                <th>Remark</th>
                <th>Attachment</th>
                <th>Value</th>
                <th>Findings</th>
                <th>Risk</th>
                <th>Action</th>
                <th>Action Category</th>
                <th>UA/UC</th>
                <th>Severity</th>
                <th>Probability</th>
                <th>Color Code</th>
                <th>Cost Type</th>
                <th>Combined Rating</th>
            </tr>';

        $sr = 1;

        foreach ($rows as $r) {

            $value = "";
            if ($details['perform_audit_by'] == "client_leased")
                $value = $r['client_leased'];
            if ($details['perform_audit_by'] == "inplant")
                $value = $r['inplant'];
            if ($details['perform_audit_by'] == "fm_leased")
                $value = $r['fm_leased'];

            $attachment = !empty($r['attachment']) ? base_url($r['attachment']) : "";

            echo "<tr>
                <td>{$sr}</td>
                <td>{$r['question_name']}</td>
                <td>{$r['audit_question']}</td>
                <td>{$r['remark']}</td>
                <td>{$attachment}</td>
                <td>{$value}</td>
                <td>{$r['finding']}</td>
                <td>{$r['risk']}</td>
                <td>{$r['action']}</td>
                <td>{$r['action_category']}</td>
                <td>{$r['ua_uc']}</td>
                <td>{$r['severity']}</td>
                <td>{$r['probability']}</td>
                <td>{$r['color_code']}</td>
                <td>{$r['cost_type']}</td>
                <td>{$r['combined_rating']}</td>
            </tr>";

            $sr++;
        }

        echo '</table><br>';

        // AUDITEE ATTENDANCE
        echo '<table>';
        echo '<tr class="section-header"><td colspan="16" style="background-color:#f2f2f2; font-weight:bold; font-size:16px;">Auditee Attendance</td></tr>';
        
        $attendance = $db->table("alert_hse_audit_attendance")->where("hse_audit_id", $auditId)->orderBy("row_no", "ASC")->get()->getResultArray();
        
        if (!empty($attendance)) {
            echo '<tr>
                <th colspan="1">Sr No</th>
                <th colspan="3">Auditee Name</th>
                <th colspan="3">Opening Date</th>
                <th colspan="3">Opening Sign</th>
                <th colspan="3">Closing Date</th>
                <th colspan="3">Closing Sign</th>
            </tr>';
            $idx = 1;
            foreach ($attendance as $att) {
                $openSign = !empty($att['opening_sign']) ? base_url($att['opening_sign']) : 'N/A';
                $closeSign = !empty($att['closing_sign']) ? base_url($att['closing_sign']) : 'N/A';
                echo '<tr>
                    <td colspan="1">' . $idx++ . '</td>
                    <td colspan="3">' . htmlspecialchars($att['auditee_attendance']) . '</td>
                    <td colspan="3">' . (!empty($att['opening_date']) ? date('d-m-Y', strtotime($att['opening_date'])) : 'N/A') . '</td>
                    <td colspan="3">' . $openSign . '</td>
                    <td colspan="3">' . (!empty($att['closing_date']) ? date('d-m-Y', strtotime($att['closing_date'])) : 'N/A') . '</td>
                    <td colspan="3">' . $closeSign . '</td>
                </tr>';
            }
        } else {
            echo '<tr><td colspan="16" style="text-align:center; font-weight:bold;">No Auditee Attendance Records Available</td></tr>';
        }

        echo '</table></body></html>';
        exit; // VERY IMPORTANT
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

    // Added on 20/12/25: Generate Combined Excel with Separate Sheets (Normal, AutoGrid, CAPA)
    // Uses XML Spreadsheet 2003 format
    public function exportCombinedExcelSheets($auditId)
    {
        while (ob_get_level())
            ob_end_clean();
        error_reporting(0);
        ini_set('display_errors', 0);

        $db = db_connect();

        // ================= MASTER =================
        $master = $db->table("alert_hse_audit_master")
            ->where("hse_audit_id", $auditId)
            ->get()
            ->getRowArray();

        if (!$master) {
            exit("Invalid Audit ID");
        }

        // ================= DYNAMIC SITE CATEGORY =================
        $siteCategories = $db->table('alert_hse_site_category')
            ->where('status', 1)
            ->get()
            ->getResultArray();

        $categoryKeys = [];
        foreach ($siteCategories as $cat) {
            $key = strtolower(str_replace(' ', '_', trim($cat['site_category_name'])));
            $categoryKeys[$key] = $cat['site_category_name'];
        }

        // ================= DETAILS (MOVE UP) =================
        $details = $db->table("alert_hse_audit_details d")
            ->select("d.*")
            ->where("d.hse_audit_id", $auditId)

            ->get()
            ->getResultArray();

        // ================= PROCESS CATEGORY MAPPING =================
        $processedDetails = [];

        foreach ($details as $d) {

            $siteKey = strtolower(str_replace(' ', '_', trim($d['site_category'] ?? '')));

            // initialize all categories empty
            $rowCats = array_fill_keys(array_keys($categoryKeys), '');

            if (isset($rowCats[$siteKey])) {
                $rowCats[$siteKey] = $d['finding'];
            }

            $processedDetails[] = $d + ['categories' => $rowCats];
        }

        // ================= HEADER =================
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=HSE_Report_$auditId.xls");

        echo '<html><head><meta charset="UTF-8"></head><body>';

        // ================= STYLE =================
        function colorVal($val)
        {
            $v = strtoupper(trim($val));

            if ($v == 'YES') {
                return "<span style='color:green;font-weight:bold;'>YES</span>";
            } elseif ($v == 'NO') {
                return "<span style='color:red;font-weight:bold;'>NO</span>";
            } elseif ($v == 'NA') {
                return "<span style='color:gray;font-weight:bold;'>NA</span>";
            }

            return "";
        }

        echo '<table border="1" style="border-collapse:collapse;font-family:Arial;width:100%;">';

        // ================= TITLE =================
        echo '<tr style="background:#f4b400;">
            <th colspan="' . (4 + count($categoryKeys)) . '" style="font-size:18px;text-align:center;">
                HSE AUDIT REPORT - NORMAL
            </th>
          </tr>';

        // ================= MASTER =================
        $l = "background:#1e1c77;color:#fff;font-weight:bold;width:150px;";
        $r = "background:#f2f2f2;";

        echo "<tr><td style='$l'>Audit No</td><td colspan='" . (3 + count($categoryKeys)) . "' style='$r'>{$master['audit_no']}</td></tr>";
        echo "<tr><td style='$l'>Audit Name</td><td colspan='" . (3 + count($categoryKeys)) . "' style='$r'>{$master['audit_name']}</td></tr>";
        echo "<tr><td style='$l'>Auditor</td><td colspan='" . (3 + count($categoryKeys)) . "' style='$r'>{$master['auditor_name']}</td></tr>";
        echo "<tr><td style='$l'>Client</td><td colspan='" . (3 + count($categoryKeys)) . "' style='$r'>{$master['client_name']}</td></tr>";
        echo "<tr><td style='$l'>Region</td><td colspan='" . (3 + count($categoryKeys)) . "' style='$r'>{$master['region']}</td></tr>";
        echo "<tr><td style='$l'>Audit Date</td><td colspan='" . (3 + count($categoryKeys)) . "' style='$r'>{$master['audit_date']}</td></tr>";
        echo "<tr><td style='$l'>Score</td><td colspan='" . (3 + count($categoryKeys)) . "' style='$r'>{$master['score']}</td></tr>";

        echo "<tr><td colspan='" . (4 + count($categoryKeys)) . "'></td></tr>";

        // ================= HEADER =================
        echo '<tr style="background:#1e1c77;color:white;font-weight:bold;text-align:center;">
        <th>Sr No</th>
        <th>Category</th>
        <th style="width:400px;">Question</th>';

        foreach ($categoryKeys as $label) {
            echo "<th>$label</th>";
        }

        echo '<th>Remark</th></tr>';

        // ================= DATA =================
        foreach ($processedDetails as $i => $d) {

            $sr = $d['audit_category_id'] ?? ($i + 1);

            if (preg_match("/^[A-Z]$/", $sr)) {
                echo "<tr style='background:#d9e1f2;font-weight:bold;'>
                    <td>$sr</td>
                    <td colspan='" . (3 + count($categoryKeys)) . "'>" . $d['audit_question'] . "</td>
                  </tr>";
            } else {

                echo "<tr>
                <td>$sr</td>
                <td>" . htmlspecialchars($d['audit_category']) . "</td>
                <td style='white-space:normal;'>" . htmlspecialchars($d['audit_question']) . "</td>";

                foreach ($categoryKeys as $key => $label) {
                    $val = $d['categories'][$key] ?? '';
                    echo "<td align='center'>" . colorVal($val) . "</td>";
                }

                echo "<td>" . htmlspecialchars($d['remark'] ?? '') . "</td></tr>";
            }
        }

        echo "</table>";
        echo "</body></html>";
        exit;
    }

}
