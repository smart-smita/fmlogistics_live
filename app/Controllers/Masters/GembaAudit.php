<?php

namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\GembaAuditModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class GembaAudit extends BaseController
{
    protected $model;

    public function __construct()
    {
        helper(['gemba_acl']);
        $this->model = new GembaAuditModel();
    }

    public function index()
    {
        $this->model->refreshAgeing();

        $request = \Config\Services::request();
        $db = \Config\Database::connect();
        $builderRegion = clone $db->table('alert_gemba_audits');
        $data['regions'] = apply_gemba_role_filters($builderRegion, 'alert_gemba_audits')->select('DISTINCT(region)')->where('status !=', 2)->where('region !=', 'NA')->orderBy('region', 'ASC')->get()->getResultArray();

        $tdata = [
            'title' => 'Gemba Audits',
            'display_contents' => [
                'gemba_sr_no' => 'Gemba Sr No',
                'unique_no' => 'Unique No',
                'region' => 'Region',
                'audit_report_date' => 'Audit Date',
                'site_name' => 'Site Name',
                'audit_category' => 'Audit Category',
                'observation_point' => 'Observation',
                'risk_rating' => 'Risk (Sev/Prob)',
                'color_code' => 'Color',
                'point_status' => 'Status',
                'ageing_days' => 'Ageing',
                'action' => 'Actions'
            ],
            'example2' => 'gemba_audit_table',
            'ajax_url_for_data' => base_url('gemba-audit/table_ajax'),
            'is_server_side' => true,
            'export_csv' => false,
            'enable_export' => false
        ];

        $data['table'] = view('Layout/table-view', $tdata);

        return view('Master/gemba_audit/list', $data);
    }

    public function table_ajax()
    {
        $req = service('request');
        $db = \Config\Database::connect();
        $builder = $db->table('alert_gemba_audits');
        $builder->where('status !=', 2);

        apply_gemba_role_filters($builder, 'alert_gemba_audits');
        apply_gemba_latest_filter($builder, 'alert_gemba_audits');

        // Apply Filters
        if ($val = $req->getGet('region')) {
            if (is_array($val))
                $builder->whereIn('region', $val);
            else
                $builder->where('region', $val);
        }
        if ($val = $req->getGet('audit_category')) {
            if (is_array($val))
                $builder->whereIn('audit_category', $val);
            else
                $builder->where('audit_category', $val);
        }
        if ($val = $req->getGet('point_status')) {
            if (is_array($val))
                $builder->whereIn('point_status', $val);
            else
                $builder->where('point_status', $val);
        }
        if ($val = $req->getGet('color_code')) {
            if (is_array($val))
                $builder->whereIn('color_code', $val);
            else
                $builder->where('color_code', $val);
        }

        // Search
        $searchValue = $req->getGet('search')['value'] ?? '';
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('unique_no', $searchValue)
                ->orLike('observation_point', $searchValue)
                ->orLike('risks_details', $searchValue)
                ->orLike('auditor_name', $searchValue)
                ->orLike('site_name', $searchValue)
                ->groupEnd();
        }

        // Total Records
        $totalBuilder = clone $builder;
        $recordsFiltered = $totalBuilder->countAllResults(false);

        $totalBuilderAll = $db->table('alert_gemba_audits')->where('status !=', 2);
        apply_gemba_role_filters($totalBuilderAll, 'alert_gemba_audits');
        apply_gemba_latest_filter($totalBuilderAll, 'alert_gemba_audits');
        $totalRecords = $totalBuilderAll->countAllResults();

        // Order
        $order = $req->getGet('order');
        if ($order && isset($order[0])) {
            $columns = $req->getGet('columns');
            $colName = $columns[$order[0]['column']]['data'];
            if ($colName != 'action' && $colName != 'risk_rating') {
                $builder->orderBy($colName, $order[0]['dir']);
            } else {
                $builder->orderBy('gemba_sr_no', 'DESC');
            }
        } else {
            $builder->orderBy('gemba_sr_no', 'DESC');
        }

        // Pagination
        $start = (int) ($req->getGet('start') ?? 0);
        $length = (int) ($req->getGet('length') ?? 15);
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        $results = $builder->get()->getResultArray();

        foreach ($results as &$row) {
            $row['risk_rating'] = ($row['risk_severity'] ?? '') . ' / ' . ($row['risk_probability'] ?? '');
            $row['color_code'] = $this->getColorBadge($row['color_code']);
            $row['point_status'] = $this->getStatusBadge($row['point_status']);
            $row['ageing_days'] = ($row['ageing_days'] ?? 0) . ' days';

            // Check if managed in HSE
            $isHseMapped = (!empty($row['hse_audit_id']) && strtolower(trim($row['audit_category'] ?? '')) == 'hse audit');

            $action = '<div class="d-flex justify-content-center gap-2">';

            if ($isHseMapped) {
                $action = '<span class="badge badge-light-info">Managed in HSE</span>';
            } else {
                // View Button (Blue/Solid)
                $action .= '<a href="' . base_url('gemba-audit/view/' . $row['gemba_sr_no']) . '" class="btn btn-icon btn-primary btn-sm" title="View"><i class="fas fa-eye"></i></a>';

                if (gemba_can_write()) {
                    // Edit Button (Blue/Solid)
                    $action .= '<a href="' . base_url('gemba-audit/edit/' . $row['gemba_sr_no']) . '" class="btn btn-icon btn-info btn-sm" title="Edit"><i class="fas fa-edit"></i></a>';

                    // Delete Button (Red/Solid)
                    $action .= '<a href="' . base_url('gemba-audit/delete/' . $row['gemba_sr_no']) . '" class="btn btn-icon btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')" title="Delete"><i class="fas fa-trash"></i></a>';
                }
            }

            $action .= '</div>';
            $row['action'] = $action;
        }

        return $this->response->setJSON([
            'draw' => intval($req->getGet('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $results
        ]);
    }

    private function getStatusBadge($status)
    {
        $status = trim($status);
        $badges = [
            'Open' => '<span class="badge badge-danger fw-bold px-4 py-2">Open</span>',
            'Closed' => '<span class="badge badge-success fw-bold px-4 py-2">Closed</span>',
            'Excluded' => '<span class="badge badge-dark fw-bold px-4 py-2">Excluded</span>',
            'Hold-review with Client' => '<span class="badge badge-warning text-dark fw-bold px-4 py-2">Hold-review</span>'
        ];
        return $badges[$status] ?? '<span class="badge badge-secondary fw-bold px-4 py-2">' . ($status ?: 'N/A') . '</span>';
    }

    private function getColorBadge($color)
    {
        $color = strtolower(trim($color));
        $colors = [
            'red' => '<span class="badge badge-danger">Red</span>',
            'yellow' => '<span class="badge badge-warning text-dark">Yellow</span>',
            'black' => '<span class="badge badge-dark">Black</span>',
        ];
        return $colors[$color] ?? '<span class="badge badge-secondary">' . ucfirst($color) . '</span>';
    }


    private function getFormDropdownData()
    {
        $db = \Config\Database::connect();

        $data['db_regions'] = $db->query("SELECT region_name FROM alert_hse_region_master WHERE status = 1")->getResultArray();
        $data['db_auditors'] = $db->query("
    SELECT 
        user_id,
        user_name,
        user_email,
        user_designation,
        user_region
    FROM alert_users
    WHERE user_designation IN ('Auditor', 'Higher authority')
    AND status = 1
    ORDER BY user_designation, user_name
")->getResultArray();
        $data['db_followups'] = $db->query("SELECT DISTINCT user_designation as designation FROM alert_users WHERE status = 1 AND user_designation IS NOT NULL")->getResultArray();

        // Fetch distinct categories from database and merge with hardcoded defaults
        $dbCats = $db->query("
            SELECT DISTINCT audit_category 
            FROM alert_gemba_audits 
            WHERE status != 2 
              AND audit_category != '' 
              AND audit_category IS NOT NULL 
            ORDER BY audit_category ASC
        ")->getResultArray();
        
        $catsList = ['ISO Audit', 'HSE Audit', 'GIA Audit', 'Electric Audit', 'Fire Audit', 'Leadership Visit'];
        foreach ($dbCats as $row) {
            $catsList[] = $row['audit_category'];
        }
        $catsList = array_unique(array_map('trim', $catsList));
        sort($catsList);
        $data['db_categories'] = $catsList;

        // Site details from alert_gemba_sites
        $data['db_sites'] = [];
        $data['db_site_type_1'] = [];
        $data['db_site_type_2'] = [];
        $data['db_site_category'] = [];
        if ($db->tableExists('alert_gemba_sites')) {
            $data['db_sites'] = $db->query("SELECT * FROM alert_gemba_sites WHERE status = 1")->getResultArray();
            $data['db_site_type_1'] = $db->query("SELECT DISTINCT site_type_1 FROM alert_gemba_sites WHERE status = 1 AND site_type_1 IS NOT NULL AND site_type_1 != ''")->getResultArray();
            $data['db_site_type_2'] = $db->query("SELECT DISTINCT site_type_2 FROM alert_gemba_sites WHERE status = 1 AND site_type_2 IS NOT NULL AND site_type_2 != ''")->getResultArray();
            $data['db_site_category'] = $db->query("SELECT DISTINCT site_category FROM alert_gemba_sites WHERE status = 1 AND site_category IS NOT NULL AND site_category != ''")->getResultArray();
        }

        $data['db_cluster_managers'] = $db->query("SELECT user_id, user_name, user_cluster, user_region FROM alert_users WHERE (user_designation = 'Cluster Manager' OR user_designation = 'Cluster manager' OR user_designation LIKE '%\"Cluster Manager\"%' OR user_designation LIKE '%\"Cluster manager\"%') AND status = 1 ORDER BY user_name ASC")->getResultArray();
        $data['db_account_managers'] = $db->query("SELECT user_id, user_name FROM alert_users WHERE (user_designation = 'Account Manager' OR user_designation LIKE '%\"Account Manager\"%') AND status = 1 ORDER BY user_name ASC")->getResultArray();

        return $data;
    }

    public function create()
    {
        gemba_require_write_access();
        if ($this->request->getMethod() === 'post') {
            $postData = $this->request->getVar();
            $postData['status'] = 1; // 1: active

            if (empty($postData['unique_no'])) {
                $postData['unique_no'] = 'S' . rand(1000, 9999);
            }

            $rules = [
                'region' => 'required',
                'auditor_name' => 'required',
                'audit_category' => 'required',
                'audit_type' => 'required',
                'audit_doc_type' => 'required',
                'site_name' => 'required',
                'audit_report_date' => 'required|valid_date',
                'checklist_category' => 'required',
                'nc_recommendation' => 'required',
                'observation_point' => 'required',
                'risk_severity' => 'required',
                'risk_probability' => 'required',
                'color_code' => 'required',
                'cost_type' => 'required',
                'point_status' => 'required'
            ];

            if ($postData['point_status'] === 'Closed' || !empty($postData['closed_date'])) {
                $rules['closed_date'] = 'required|valid_date';

                if (!empty($postData['closed_date'])) {
                    $postData['point_status'] = 'Closed';
                    $postData['point_category'] = 'Closed Category';
                    $postData['closure_status'] = 'Closed';
                }
            }

            if ($this->validate($rules)) {
                $postData['account_manager'] = $postData['account_manager'] ?? null;
                $postData['source_module'] = 'manual';
                $postData['created_by'] = session()->get('user_name');
                $postData['action_category'] = (!empty($postData['action_category'])) ? $postData['action_category'] : 'Not Categorized';
                $postData['default_date'] = date('Y-m-d H:i:s');

                if ($postData['point_status'] === 'Closed' || !empty($postData['closed_date'])) {
                    $postData['nc_status'] = 3;
                    $postData['nc_closed_by'] = session()->get('user_name');
                    $postData['nc_closed_date'] = date('Y-m-d H:i:s');
                } else {
                    $postData['nc_status'] = 0;
                }

                $postData['snapshot_account_manager_name'] = $postData['account_manager'] ?? '';
                $postData['snapshot_cluster_manager_name'] = $postData['cluster_manager_spoc'] ?? '';
                $postData['snapshot_created_by_user_id'] = session()->get('user_id');
                $postData['snapshot_created_by_user_name'] = session()->get('user_name');
                $postData['snapshot_created_at'] = date('Y-m-d H:i:s');

                $this->model->insert($postData);
                $gembaInsertId = $this->model->getInsertID();

                helper(['designation_acl']);
                logNcActionHistory(
                    'GEMBA',
                    (int)$gembaInsertId,
                    'CREATED',
                    null,
                    (string)($postData['nc_status'] ?? '0'),
                    $postData['observation_point'] ?? 'Gemba audit point created',
                    $postData['working_uploaded_file'] ?? null,
                    (int)($postData['hse_audit_id'] ?? 0),
                    $postData['site_name'] ?? null
                );

                return redirect()->to('/gemba-audit')->with('success', 'Gemba Audit created successfully.');
            } else {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $dropdownData = $this->getFormDropdownData();
        $data = array_merge(['audit' => ['unique_no' => 'S' . rand(1000, 9999)]], $dropdownData);
        return view('Master/gemba_audit/form', $data);
    }

    public function edit($id = null)
    {
        gemba_require_write_access();
        $data['audit'] = $this->model->find($id);
        if (!$data['audit']) {
            return redirect()->to('/gemba-audit')->with('error', 'Record not found.');
        }

        if ($this->request->getMethod() === 'post') {
            $postData = $this->request->getVar();
            $rules = [
                'region' => 'required',
                'auditor_name' => 'required',
                'audit_category' => 'required',
                'audit_type' => 'required',
                'audit_doc_type' => 'required',
                'site_name' => 'required',
                'audit_report_date' => 'required|valid_date',
                'checklist_category' => 'required',
                'nc_recommendation' => 'required',
                'observation_point' => 'required',
                'risk_severity' => 'required',
                'risk_probability' => 'required',
                'color_code' => 'required',
                'cost_type' => 'required',
                'point_status' => 'required'
            ];

            if ($postData['point_status'] === 'Closed' || !empty($postData['closed_date'])) {
                $rules['closed_date'] = 'required|valid_date';

                if (!empty($postData['closed_date'])) {
                    $postData['point_status'] = 'Closed';
                    $postData['point_category'] = 'Closed Category';
                    $postData['closure_status'] = 'Closed';
                }
            }

            if ($this->validate($rules)) {
                $postData['updated_by'] = session()->get('user_name');
                $postData['update_date'] = date('Y-m-d H:i:s');

                if ($postData['point_status'] === 'Closed' || !empty($postData['closed_date'])) {
                    $postData['nc_status'] = 3;
                    if (empty($data['audit']['nc_closed_by'])) {
                        $postData['nc_closed_by'] = session()->get('user_name');
                        $postData['nc_closed_date'] = date('Y-m-d H:i:s');
                    }
                }

                $this->model->update($id, $postData);
                return redirect()->to('/gemba-audit')->with('success', 'Gemba Audit updated successfully.');
            } else {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }
        }

        $dropdownData = $this->getFormDropdownData();
        $data = array_merge($data, $dropdownData);

        return view('Master/gemba_audit/form', $data);
    }

    public function view($id = null)
    {
        $data['audit'] = $this->model->find($id);
        if (!$data['audit']) {
            return redirect()->to('/gemba-audit')->with('error', 'Record not found.');
        }
        return view('Master/gemba_audit/view', $data);
    }

    public function delete($id = null)
    {
        gemba_require_write_access();
        $audit = $this->model->find($id);
        if ($audit) {
            $this->model->update($id, ['status' => 2, 'updated_by' => session()->get('user_name'), 'update_date' => date('Y-m-d H:i:s')]);
            return redirect()->to('/gemba-audit')->with('success', 'Gemba Audit deleted successfully.');
        }
        return redirect()->to('/gemba-audit')->with('error', 'Record not found.');
    }

    public function cascadingDropdown()
    {
        $category = $this->request->getGet('audit_category');
        $db = \Config\Database::connect();

        $auditTypes = [];
        $docTypes = [];

        if (!empty($category)) {
            // Fetch distinct audit types for this category
            $typesResult = $db->table('alert_gemba_audits')
                ->select('DISTINCT(audit_type) as type')
                ->where('audit_category', $category)
                ->where('audit_type !=', '')
                ->where('audit_type IS NOT NULL')
                ->where('status !=', 2)
                ->orderBy('audit_type', 'ASC')
                ->get()
                ->getResultArray();
            $auditTypes = array_column($typesResult, 'type');

            // Fetch distinct doc types for this category
            $docsResult = $db->table('alert_gemba_audits')
                ->select('DISTINCT(audit_doc_type) as doc')
                ->where('audit_category', $category)
                ->where('audit_doc_type !=', '')
                ->where('audit_doc_type IS NOT NULL')
                ->where('status !=', 2)
                ->orderBy('audit_doc_type', 'ASC')
                ->get()
                ->getResultArray();
            $docTypes = array_column($docsResult, 'doc');
        } else {
            // Default
            $typesResult = $db->table('alert_gemba_audits')
                ->select('DISTINCT(audit_type) as type')
                ->where('audit_type !=', '')
                ->where('audit_type IS NOT NULL')
                ->where('status !=', 2)
                ->orderBy('audit_type', 'ASC')
                ->get()
                ->getResultArray();
            $auditTypes = array_column($typesResult, 'type');

            $docsResult = $db->table('alert_gemba_audits')
                ->select('DISTINCT(audit_doc_type) as doc')
                ->where('audit_doc_type !=', '')
                ->where('audit_doc_type IS NOT NULL')
                ->where('status !=', 2)
                ->orderBy('audit_doc_type', 'ASC')
                ->get()
                ->getResultArray();
            $docTypes = array_column($docsResult, 'doc');
        }

        return $this->response->setJSON([
            'audit_types' => $auditTypes,
            'doc_types' => $docTypes
        ]);
    }

    public function getSiteDetails()
    {
        $siteName = $this->request->getGet('site_name');
        $db = \Config\Database::connect();
        $site = [];
        if ($db->tableExists('alert_gemba_sites')) {
            $site = $db->table('alert_gemba_sites')->where('site_name', $siteName)->where('status', 1)->get()->getRowArray();
        }
        return $this->response->setJSON($site ?: []);
    }

    public function gemba_export()
    {
        $request = \Config\Services::request();
        $db = \Config\Database::connect();

        while (ob_get_level()) {
            ob_end_clean();
        }

        $builder = $db->table('alert_gemba_audits');
        $builder->where('status !=', 2);

        apply_gemba_role_filters($builder, 'alert_gemba_audits');
        apply_gemba_latest_filter($builder, 'alert_gemba_audits');

        // ---------------- FILTERS ----------------
        if ($val = $request->getGet('region')) {
            if (is_array($val))
                $builder->whereIn('region', $val);
            else
                $builder->where('region', $val);
        }

        if ($val = $request->getGet('audit_category')) {
            if (is_array($val))
                $builder->whereIn('audit_category', $val);
            else
                $builder->where('audit_category', $val);
        }

        if ($val = $request->getGet('site_name')) {
            if (is_array($val))
                $builder->whereIn('site_name', $val);
            else
                $builder->where('site_name', $val);
        }

        if ($val = $request->getGet('point_status')) {
            if (is_array($val))
                $builder->whereIn('point_status', $val);
            else
                $builder->where('point_status', $val);
        }

        if ($val = $request->getGet('color_code')) {
            if (is_array($val)) {
                $val = array_map('strtolower', $val);
                $builder->whereIn('LOWER(color_code)', $val);
            } else {
                $builder->where('LOWER(color_code)', strtolower($val));
            }
        }

        if ($request->getGet('keyword')) {
            $keyword = trim($request->getGet('keyword'));
            $builder->groupStart()
                ->like('unique_no', $keyword)
                ->orLike('observation_point', $keyword)
                ->orLike('risks_details', $keyword)
                ->orLike('auditor_name', $keyword)
                ->groupEnd();
        }

        $audits = $builder
            ->orderBy('gemba_sr_no', 'DESC')
            ->get()
            ->getResultArray();

        // ---------------- FILE NAME ----------------
        $filename = "Gemba_Audit_Report_" . date('Ymd_His') . ".csv";

        // ---------------- HEADERS ----------------
        header("Content-Type: text/csv; charset=UTF-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header("Pragma: no-cache");
        header("Expires: 0");

        $fp = fopen('php://output', 'w');
        if (!$fp) {
            exit('Unable to open output stream');
        }

        // UTF-8 BOM
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = [
            'Gemba Sr. No.', 'Unique No', 'Region', 'Auditor Name', 'Audit Category', 'Audit Type', 'Audit Document Type',
            'Site Name', 'Site Type-1', 'Site Type-2', 'Site Category', 'Audit mail received date', 'Gemba Round Done on/Audit report date',
            'Weeknum for Point Raised', 'Month for Point raised', 'Year-Month for Point raised', 'Year', 'Checklist Category',
            'NC/RECOMMENDATION', 'QHSE REMARKS', 'Gemba round observations/Point', 'Risks & Details', 'Action / Recommendation',
            'Specifications/Other details/Tips for Capex-Opex Infra, if any', 'UA/UC, Quality or others', 'Risk Severity', 'Risk Probability',
            'COLOR CODE', 'Cost Type', 'Remarks (By WMs) / Action Plan', 'Whose Scope?', 'Remarks-2 (By Corporate HO)',
            'Ageing in days', 'Age Bracket', 'Target Date-1', 'Closed Date', 'Point Open/Closed', 'Point Category', 'Point Closure status',
            'Weeknum for Point Closed', 'Month for Point Closed', 'Year-Month for Point closed', 'Risk Severity Rating', 'Risk Probability Rating',
            'Combined Risk Rating', 'No. of times point repeated', 'Combined Risk Rating wrt Frequency', 'Final Rating', 'Follow-up by',
            'Weeknum & Year-Month (Pts Raised)', 'Weeknum & Year-Month (Pts Closed)', 'Cluster Manager/SPOC'
        ];

        fputcsv($fp, $headers);

        $safeVal = function ($val) {
            return (!isset($val) || trim((string)$val) === '') ? 'NA' : trim((string)$val);
        };

        foreach ($audits as $audit) {
            $color = strtolower(trim($audit['color_code'] ?? ''));

            $row = [
                $safeVal($audit['gemba_sr_no']),
                $safeVal($audit['unique_no']),
                $safeVal($audit['region']),
                $safeVal($audit['auditor_name']),
                $safeVal($audit['audit_category']),
                $safeVal($audit['audit_type']),
                $safeVal($audit['audit_doc_type']),
                $safeVal($audit['site_name']),
                $safeVal($audit['site_type_1']),
                $safeVal($audit['site_type_2']),
                $safeVal($audit['site_category']),
                $safeVal($audit['audit_mail_received_date']),
                $safeVal($audit['audit_report_date']),
                $safeVal($audit['weeknum_raised']),
                $safeVal($audit['month_raised']),
                $safeVal($audit['year_month_raised']),
                $safeVal($audit['year_raised']),
                $safeVal($audit['checklist_category']),
                $safeVal($audit['nc_recommendation']),
                $safeVal($audit['qhse_remarks']),
                $safeVal($audit['observation_point']),
                $safeVal($audit['risks_details']),
                $safeVal($audit['action_recommendation']),
                $safeVal($audit['specifications']),
                $safeVal($audit['ua_uc_type']),
                $safeVal($audit['risk_severity']),
                $safeVal($audit['risk_probability']),
                $safeVal(strtoupper($color)),
                $safeVal($audit['cost_type']),
                $safeVal($audit['remarks_wm']),
                $safeVal($audit['whose_scope']),
                $safeVal($audit['remarks_corporate']),
                $safeVal($audit['ageing_days']),
                $safeVal($audit['age_bracket']),
                $safeVal($audit['target_date']),
                $safeVal($audit['closed_date']),
                $safeVal($audit['point_status']),
                $safeVal($audit['point_category']),
                $safeVal($audit['closure_status']),
                $safeVal($audit['weeknum_closed']),
                $safeVal($audit['month_closed']),
                $safeVal($audit['year_month_closed']),
                $safeVal($audit['risk_severity_rating']),
                $safeVal($audit['risk_probability_rating']),
                $safeVal($audit['combined_risk_rating']),
                $safeVal($audit['times_repeated']),
                $safeVal($audit['combined_risk_freq']),
                $safeVal($audit['final_rating']),
                $safeVal(!empty($audit['followup_by']) ? $audit['followup_by'] : 'NA'),
                $safeVal($audit['weeknum_raised'] . ' - ' . $audit['year_month_raised']),
                $safeVal($audit['weeknum_closed'] . ' - ' . $audit['year_month_closed']),
                $safeVal($audit['cluster_manager_spoc'] ?? '')
            ];

            fputcsv($fp, $row);
        }

        fclose($fp);
        exit;
    }

    public function import_hse_to_gemba()
    {
        gemba_require_write_access();
        // Use the newly created HseGembaSyncService
        $syncService = new \App\Services\HseGembaSyncService();
        $stats = $syncService->syncHseToGemba();

        return $this->response->setJSON([
            'status' => true,
            'message' => "HSE to Gemba sync completed.",
            'stats' => $stats
        ]);
    }

    public function import()
    {
        gemba_require_write_access();
        $db = \Config\Database::connect();

        if ($this->request->getMethod() === 'post') {
            $file = $this->request->getFile('import_file');

            if (!$file || !$file->isValid()) {
                return redirect()->back()->with('error', 'Please upload a valid file.');
            }

            $extension = strtolower($file->getClientExtension());
            if (!in_array($extension, ['xlsx', 'xls', 'csv'])) {
                return redirect()->back()->with('error', 'Only .xlsx, .xls, and .csv files are supported.');
            }

            $fileName = $file->getName();
            $tempPath = $file->getTempName();

            try {
                // Load spreadsheet using PhpSpreadsheet
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($tempPath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray(null, true, true, true);
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Error reading file: ' . $e->getMessage());
            }

            if (empty($rows)) {
                return redirect()->back()->with('error', 'The uploaded file is empty.');
            }

            // Headers check
            $headerRow = array_shift($rows);
            $headerMap = [];
            foreach ($headerRow as $columnLetter => $columnName) {
                if ($columnName !== null) {
                    $headerMap[strtolower(trim((string)$columnName))] = $columnLetter;
                }
            }

            // Required header columns list (must be present in the uploaded sheet headers)
            $requiredHeaders = [
                'auditor_name', 'audit_category', 'audit_type', 'site_name',
                'audit_report_date', 'observation_point', 'risks_details',
                'action_recommendation', 'point_status', 'point_category'
            ];

            // Verify headers contain all required columns
            $missingHeaders = [];
            foreach ($requiredHeaders as $col) {
                if (!isset($headerMap[$col])) {
                    $missingHeaders[] = $col;
                }
            }

            if (!empty($missingHeaders)) {
                return redirect()->back()->with('error', 'Missing required header columns: ' . implode(', ', $missingHeaders));
            }

            // Mandatory columns to check at row-level (including region which is resolved dynamically)
            $mandatoryColumns = [
                'auditor_name', 'audit_category', 'audit_type', 'site_name',
                'audit_report_date', 'observation_point', 'risks_details',
                'action_recommendation', 'point_status', 'point_category'
            ];

            // Statistics counters
            $totalCount = 0;
            $insertedCount = 0;
            $updatedCount = 0;
            $skippedCount = 0;
            $failedCount = 0;
            $duplicateCount = 0;

            $errors = [];

            $updates = [];
            $batchSize = 500;

            $currentUser = session()->get('user_name') ?? 'System';
            $now = date('Y-m-d H:i:s');

            $db->transStart();

            $rowNumber = 1; // header was row 1
            foreach ($rows as $rowData) {
                $rowNumber++;

                // Skip fully empty row
                $nonEmptyValues = array_filter($rowData, function($val) {
                    return $val !== null && trim((string)$val) !== '';
                });
                if (empty($nonEmptyValues)) {
                    $skippedCount++;
                    continue;
                }

                $totalCount++;

                // Helper to safely get value from row
                $getVal = function($colName) use ($rowData, $headerMap) {
                    if (isset($headerMap[$colName])) {
                        $colLetter = $headerMap[$colName];
                        $val = $rowData[$colLetter] ?? null;
                        return $val !== null ? trim((string)$val) : null;
                    }
                    return null;
                };

                $siteName = $getVal('site_name');

                // Master Site details mapping
                $region = $getVal('region');
                $siteType1 = $getVal('site_type_1');
                $siteType2 = $getVal('site_type_2');
                $siteCategory = $getVal('site_category');

                if (!empty($siteName)) {
                    $cleanSiteName = strtolower(trim($siteName));
                    $siteMasterRow = $db->query("
                        SELECT site_type_1, site_type_2, site_category, region
                        FROM alert_gemba_sites
                        WHERE LOWER(TRIM(site_name)) = ?
                          AND status = 1
                        LIMIT 1
                    ", [$cleanSiteName])->getRowArray();
                    
                    if ($siteMasterRow) {
                        if (empty($region) && !empty($siteMasterRow['region'])) {
                            $region = $siteMasterRow['region'];
                        }
                        if (empty($siteType1) && !empty($siteMasterRow['site_type_1'])) {
                            $siteType1 = $siteMasterRow['site_type_1'];
                        }
                        if (empty($siteType2) && !empty($siteMasterRow['site_type_2'])) {
                            $siteType2 = $siteMasterRow['site_type_2'];
                        }
                        if (empty($siteCategory) && !empty($siteMasterRow['site_category'])) {
                            $siteCategory = $siteMasterRow['site_category'];
                        }
                    }
                }

                // Client Master mapping
                $clusterManagerSpoc = $getVal('cluster_manager_spoc');
                $accountManager = $getVal('account_manager');

                if (!empty($siteName)) {
                    $cleanSiteName = strtolower(trim($siteName));
                    $clientMasterRow = $db->query("
                        SELECT cluster, account_manager, region
                        FROM alert_hse_client_master
                        WHERE (
                            LOWER(TRIM(client_name)) = ?
                            OR LOWER(TRIM(location)) = ?
                        )
                        AND status = 1
                        LIMIT 1
                    ", [$cleanSiteName, $cleanSiteName])->getRowArray();
                    
                    if ($clientMasterRow) {
                        if (!empty($clientMasterRow['cluster'])) {
                            $clusterManagerSpoc = $clientMasterRow['cluster'];
                        }
                        if (!empty($clientMasterRow['account_manager'])) {
                            $accountManager = $clientMasterRow['account_manager'];
                        }
                        if (empty($region) && !empty($clientMasterRow['region'])) {
                            $region = $clientMasterRow['region'];
                        }
                    }
                }

                // Validate mandatory fields (using resolved master values for region and site_name)
                $rowErrors = [];
                foreach ($mandatoryColumns as $col) {
                    if ($col === 'region') {
                        $val = $region;
                    } else {
                        $val = $getVal($col);
                    }
                    if ($val === null || $val === '') {
                        $rowErrors[] = "Mandatory column '{$col}' cannot be empty.";
                    }
                }



                // Date Fields validation & parsing
                $dateFields = [
                    'audit_mail_received_date',
                    'audit_report_date',
                    'target_date',
                    'closed_date',
                    'month_raised',
                    'month_closed'
                ];
                
                $parsedDates = [];
                foreach ($dateFields as $df) {
                    $val = $getVal($df);
                    if ($val !== null && $val !== '') {
                        $parsedDate = $this->parseExcelDate($val);
                        if ($parsedDate === null) {
                            $rowErrors[] = "Invalid date format for field '{$df}': '{$val}'.";
                        } else {
                            $parsedDates[$df] = $parsedDate;
                        }
                    } else {
                        $parsedDates[$df] = null;
                    }
                }

                // Numeric Fields validation
                $numericFields = [
                    'weeknum_raised',
                    'year_raised',
                    'ageing_days',
                    'weeknum_closed',
                    'risk_severity_rating',
                    'risk_probability_rating',
                    'combined_risk_rating',
                    'times_repeated',
                    'combined_risk_freq',
                    'final_rating'
                ];
                $parsedNumerics = [];
                foreach ($numericFields as $nf) {
                    $val = $getVal($nf);
                    if ($val !== null && $val !== '') {
                        if (!is_numeric($val)) {
                            $rowErrors[] = "Field '{$nf}' must be numeric: '{$val}'.";
                        } else {
                            $parsedNumerics[$nf] = (int)$val;
                        }
                    } else {
                        $parsedNumerics[$nf] = null;
                    }
                }

                // If row has validation errors, count as failed and skip processing
                if (!empty($rowErrors)) {
                    $failedCount++;
                    foreach ($rowErrors as $err) {
                        $errors[] = [
                            'row' => $rowNumber,
                            'sr_no' => '',
                            'message' => $err
                        ];
                    }
                    continue;
                }



                // Construct baseline array of excel fields
                $mappedRow = [
                    'region' => $region,
                    'auditor_name' => $getVal('auditor_name'),
                    'audit_category' => $getVal('audit_category'),
                    'audit_type' => $getVal('audit_type'),
                    'audit_doc_type' => $getVal('audit_doc_type'),
                    'site_name' => $siteName,
                    'site_type_1' => $siteType1,
                    'site_type_2' => $siteType2,
                    'site_category' => $siteCategory,
                    'audit_mail_received_date' => $parsedDates['audit_mail_received_date'],
                    'audit_report_date' => $parsedDates['audit_report_date'],
                    'weeknum_raised' => $parsedNumerics['weeknum_raised'],
                    'month_raised' => $parsedDates['month_raised'],
                    'year_month_raised' => $getVal('year_month_raised'),
                    'year_raised' => $parsedNumerics['year_raised'],
                    'checklist_category' => $getVal('checklist_category'),
                    'nc_recommendation' => $getVal('nc_recommendation'),
                    'qhse_remarks' => $getVal('qhse_remarks'),
                    'observation_point' => $getVal('observation_point'),
                    'risks_details' => $getVal('risks_details'),
                    'action_recommendation' => $getVal('action_recommendation'),
                    'specifications' => $getVal('specifications'),
                    'ua_uc_type' => $getVal('ua_uc_type'),
                    'risk_severity' => $getVal('risk_severity'),
                    'risk_probability' => $getVal('risk_probability'),
                    'color_code' => $getVal('color_code'),
                    'cost_type' => $getVal('cost_type'),
                    'remarks_wm' => $getVal('remarks_wm'),
                    'whose_scope' => $getVal('whose_scope'),
                    'remarks_corporate' => $getVal('remarks_corporate'),
                    'ageing_days' => $parsedNumerics['ageing_days'],
                    'age_bracket' => $getVal('age_bracket'),
                    'target_date' => $parsedDates['target_date'],
                    'closed_date' => $parsedDates['closed_date'],
                    'point_status' => $getVal('point_status'),
                    'point_category' => $getVal('point_category'),
                    'closure_status' => $getVal('closure_status'),
                    'weeknum_closed' => $parsedNumerics['weeknum_closed'],
                    'month_closed' => $parsedDates['month_closed'],
                    'year_month_closed' => $getVal('year_month_closed'),
                    'risk_severity_rating' => $parsedNumerics['risk_severity_rating'],
                    'risk_probability_rating' => $parsedNumerics['risk_probability_rating'],
                    'combined_risk_rating' => $parsedNumerics['combined_risk_rating'],
                    'times_repeated' => $parsedNumerics['times_repeated'] ?? 1,
                    'combined_risk_freq' => $parsedNumerics['combined_risk_freq'],
                    'final_rating' => $parsedNumerics['final_rating'],
                    'followup_by' => $getVal('followup_by'),
                    'weeknum_yearmonth_raised' => $getVal('weeknum_yearmonth_raised'),
                    'weeknum_yearmonth_closed' => $getVal('weeknum_yearmonth_closed'),
                    'cluster_manager_spoc' => $clusterManagerSpoc,
                    'account_manager' => $accountManager,
                    'nc_closed_by' => null,
                    'nc_closed_date' => null
                ];

                // Run calculations to ensure computed ratings and dates are populated if they were empty
                $mappedRow = $this->calculateComputedFields($mappedRow);

                // Check string fallback to NA
                $stringCols = [
                    'region', 'auditor_name', 'audit_category', 'audit_type', 'audit_doc_type',
                    'site_name', 'site_type_1', 'site_type_2', 'site_category',
                    'checklist_category', 'nc_recommendation', 'qhse_remarks', 'observation_point',
                    'risks_details', 'action_recommendation', 'specifications', 'ua_uc_type',
                    'risk_severity', 'risk_probability', 'color_code', 'cost_type',
                    'remarks_wm', 'whose_scope', 'remarks_corporate', 'point_status',
                    'point_category', 'closure_status', 'followup_by', 'cluster_manager_spoc',
                    'account_manager'
                ];
                foreach ($stringCols as $col) {
                    if (array_key_exists($col, $mappedRow) && trim((string)$mappedRow[$col]) === '') {
                        $mappedRow[$col] = 'NA';
                    }
                }

                // Check Insert vs Update
                $pointStatusCheck = strtolower(trim((string)($mappedRow['point_status'] ?? '')));
                $autoNcStatus = null;
                if ($pointStatusCheck === 'closed') {
                    $autoNcStatus = 3; // Closed
                    $mappedRow['nc_closed_by'] = $currentUser;
                    $mappedRow['nc_closed_date'] = !empty($mappedRow['closed_date']) ? $mappedRow['closed_date'] : date('Y-m-d');
                } elseif ($pointStatusCheck === 'wip' || $pointStatusCheck === 'work in progress') {
                    $autoNcStatus = 1; // Working
                }

                    $mappedRow['unique_no'] = 'GMB-' . date('Ymd') . '-' . sprintf('%04d', rand(0, 9999));
                    $mappedRow['created_by'] = $currentUser;
                    $mappedRow['updated_by'] = $currentUser;
                    $mappedRow['status'] = 1;
                    $mappedRow['default_date'] = $now;
                    $mappedRow['update_date'] = $now;
                    $mappedRow['source_module'] = 'GEMBA';

                    if ($autoNcStatus !== null) {
                        $mappedRow['nc_status'] = $autoNcStatus;
                    }

                    $inserts[] = $mappedRow;
                    $insertedCount++;

                // Process batch inserts in chunks of 500
                if (count($inserts) >= $batchSize) {
                    $db->table('alert_gemba_audits')->insertBatch($inserts);
                    $inserts = [];
                }

            }

            // Insert remainder
            if (!empty($inserts)) {
                $db->table('alert_gemba_audits')->insertBatch($inserts);
            }


            // Save Import Log
            $logData = [
                'file_name' => $fileName,
                'imported_by' => $currentUser,
                'import_date' => $now,
                'total_rows' => $totalCount,
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'failed' => $failedCount
            ];
            $db->table('alert_gemba_import_logs')->insert($logData);

            $db->transComplete();

            if ($db->transStatus() === false) {
                return redirect()->back()->with('error', 'Import failed due to database transaction error.');
            }

            // Session summaries and errors
            $summaryData = [
                'file_name' => $fileName,
                'total_rows' => $totalCount,
                'inserted' => $insertedCount,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
                'duplicates' => $duplicateCount
            ];

            session()->setFlashdata('import_summary', $summaryData);

            if (!empty($errors)) {
                session()->set('gemba_import_errors', $errors);
                session()->setFlashdata('import_errors_exist', true);
            } else {
                session()->remove('gemba_import_errors');
            }

            if ($failedCount > 0) {
                return redirect()->to(base_url('gemba-audit/import'))->with('success', "Import completed with validation errors. {$insertedCount} records inserted, {$updatedCount} updated, {$failedCount} failed.");
            }

            return redirect()->to(base_url('gemba-audit/import'))->with('success', "All records imported successfully! {$insertedCount} records inserted, {$updatedCount} updated.");
        }

        // GET request - render UI
        $history = $db->table('alert_gemba_import_logs')
            ->orderBy('import_date', 'DESC')
            ->get()
            ->getResultArray();

        return view('Master/gemba_audit/import', [
            'history' => $history
        ]);
    }

    public function downloadErrorReport()
    {
        $errors = session()->get('gemba_import_errors');
        if (empty($errors)) {
            return redirect()->to(base_url('gemba-audit/import'))->with('error', 'No error report available.');
        }

        $filename = "Gemba_Import_Error_Report_" . date('Ymd_His') . ".csv";

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        
        // CSV Header
        fputcsv($output, ['Row Number', 'Gemba SR No', 'Error Message']);

        // Data
        foreach ($errors as $err) {
            fputcsv($output, [
                $err['row'],
                $err['sr_no'] ?: 'N/A',
                $err['message']
            ]);
        }

        fclose($output);
        exit;
    }

    public function downloadSample()
    {
        gemba_require_write_access();
        $db = \Config\Database::connect();

        // 1. Query active client names from alert_hse_client_master
        $clients = $db->query("
            SELECT DISTINCT client_name 
            FROM alert_hse_client_master 
            WHERE status = 1 AND client_name IS NOT NULL AND client_name != ''
            ORDER BY client_name
        ")->getResultArray();

        $clientNames = array_column($clients, 'client_name');
        $clientCount = count($clientNames);

        // 2. Columns list
        $headers = [
            'auditor_name',
            'audit_category',
            'audit_type',
            'audit_doc_type',
            'site_name',
            'audit_mail_received_date',
            'audit_report_date',
            'weeknum_raised',
            'month_raised',
            'year_month_raised',
            'year_raised',
            'checklist_category',
            'nc_recommendation',
            'qhse_remarks',
            'observation_point',
            'risks_details',
            'action_recommendation',
            'specifications',
            'ua_uc_type',
            'risk_severity',
            'risk_probability',
            'color_code',
            'cost_type',
            'remarks_wm',
            'whose_scope',
            'remarks_corporate',
            'ageing_days',
            'age_bracket',
            'target_date',
            'closed_date',
            'point_status',
            'point_category',
            'closure_status',
            'weeknum_closed',
            'month_closed',
            'year_month_closed',
            'risk_severity_rating',
            'risk_probability_rating',
            'combined_risk_rating',
            'times_repeated',
            'combined_risk_freq',
            'final_rating',
            'followup_by'
        ];

        // 3. Create PhpSpreadsheet Spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        
        // Sheet 0 is the main upload sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Gemba Audit Upload');

        // Header Style
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1F4E78'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ];

        // Fill headers
        $colIdx = 1;
        foreach ($headers as $header) {
            $sheet->setCellValueByColumnAndRow($colIdx, 1, $header);
            $colIdx++;
        }
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->applyFromArray($headerStyle);

        // If clients exist, write them to a hidden sheet and link validation
        if ($clientCount > 0) {
            // Create hidden sheet
            $clientsSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadsheet, 'Client List');
            $spreadsheet->addSheet($clientsSheet);
            
            // Populate clients
            for ($r = 0; $r < $clientCount; $r++) {
                $clientsSheet->setCellValueByColumnAndRow(1, $r + 1, $clientNames[$r]);
            }
            
            // Hide the sheet
            $clientsSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

            // Add dropdown validation to site_name column (E) for rows 2 to 100
            for ($row = 2; $row <= 100; $row++) {
                $validation = $sheet->getCell('E' . $row)->getDataValidation();
                $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
                $validation->setAllowBlank(true);
                $validation->setShowInputMessage(true);
                $validation->setShowErrorMessage(true);
                $validation->setShowDropDown(true);
                $validation->setErrorTitle('Input Error');
                $validation->setError('Value is not in the allowed client list.');
                $validation->setPromptTitle('Select Site Name');
                $validation->setPrompt('Please choose a client from the dropdown list.');
                // Formula 1 refers to the range of client names in the hidden sheet
                $validation->setFormula1('\'Client List\'!$A$1:$A$' . $clientCount);
            }
        }

        // Auto-fit columns
        foreach ($sheet->getColumnIterator() as $column) {
            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
        }

        // Clean buffer
        while (ob_get_level()) {
            ob_end_clean();
        }

        // Force download header
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Gemba_Audit_Sample.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function parseExcelDate($value)
    {
        if ($value === null || trim((string)$value) === '') {
            return null;
        }

        // Check if numeric (Excel serial date)
        if (is_numeric($value) && (float)$value > 20000) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
            } catch (\Exception $e) {
                // fallback
            }
        }

        $value = trim((string)$value);

        // Try standard conversion
        $timestamp = strtotime($value);
        if ($timestamp !== false) {
            return date('Y-m-d', $timestamp);
        }

        // Try common formats
        $formats = ['d-m-Y', 'd/m/Y', 'm/d/Y', 'Y-m-d', 'Y/m/d', 'Y.m.d', 'd.m.Y'];
        foreach ($formats as $format) {
            $dateObj = \DateTime::createFromFormat($format, $value);
            if ($dateObj) {
                return $dateObj->format('Y-m-d');
            }
        }

        return null;
    }

    private function calculateComputedFields(array $row)
    {
        // Severity rating
        $sev = 1;
        if (!empty($row['risk_severity'])) {
            $sevStr = strtolower($row['risk_severity']);
            if ($sevStr === 'low') $sev = 1;
            elseif ($sevStr === 'medium') $sev = 3;
            elseif ($sevStr === 'high') $sev = 4;
            $row['risk_severity_rating'] = $sev;
        } else {
            $sev = isset($row['risk_severity_rating']) ? (int)$row['risk_severity_rating'] : 1;
        }

        // Probability rating
        $prob = 1;
        if (!empty($row['risk_probability'])) {
            $probStr = strtolower($row['risk_probability']);
            if ($probStr === 'low') $prob = 1;
            elseif ($probStr === 'medium') $prob = 3;
            elseif ($probStr === 'high') $prob = 4;
            $row['risk_probability_rating'] = $prob;
        } else {
            $prob = isset($row['risk_probability_rating']) ? (int)$row['risk_probability_rating'] : 1;
        }

        // Combined ratings
        $row['combined_risk_rating'] = $sev * $prob;
        $timesRepeated = isset($row['times_repeated']) ? (int)$row['times_repeated'] : 1;
        $row['combined_risk_freq'] = $sev * $prob * $timesRepeated;

        // Color multiplier
        $colorMult = 1;
        if (!empty($row['color_code'])) {
            $row['color_code'] = ucfirst(strtolower(trim($row['color_code'])));
            $colorStr = strtolower($row['color_code']);
            if ($colorStr === 'red') $colorMult = 4;
            elseif ($colorStr === 'black') $colorMult = 6;
            elseif ($colorStr === 'yellow') $colorMult = 2;
            elseif ($colorStr === 'green') $colorMult = 1;
        }

        // Cost multiplier
        $costMult = 1;
        if (!empty($row['cost_type'])) {
            $costStr = strtolower($row['cost_type']);
            if ($costStr === 'capex') $costMult = 3;
            elseif ($costStr === 'opex') $costMult = 1;
        }

        // Final Rating
        $row['final_rating'] = $sev * $prob * $timesRepeated * $colorMult * $costMult;

        // Dates & Computed Week/Month/Year logic
        if (!empty($row['audit_report_date'])) {
            $reportDate = strtotime($row['audit_report_date']);
            $row['weeknum_raised'] = date('W', $reportDate);
            $row['month_raised'] = date('Y-m-01', $reportDate);
            $row['year_month_raised'] = date('Y-m', $reportDate);
            $row['year_raised'] = date('Y', $reportDate);
            $row['weeknum_yearmonth_raised'] = date('Y-m', $reportDate) . '-W' . date('W', $reportDate);
        }

        // Point Closure logic
        if (isset($row['point_status']) && $row['point_status'] === 'Closed' && !empty($row['closed_date'])) {
            $closedDate = strtotime($row['closed_date']);
            $row['weeknum_closed'] = date('W', $closedDate);
            $row['month_closed'] = date('Y-m-01', $closedDate);
            $row['year_month_closed'] = date('Y-m', $closedDate);
            $row['weeknum_yearmonth_closed'] = date('Y-m', $closedDate) . '-W' . date('W', $closedDate);
            
            // Ageing days for closed points
            if (!empty($row['audit_report_date'])) {
                $reportDate = strtotime($row['audit_report_date']);
                $diff = $closedDate - $reportDate;
                $days = floor($diff / (60 * 60 * 24));
                $row['ageing_days'] = $days >= 0 ? $days : 0;
                
                if ($row['ageing_days'] <= 30) {
                    $row['age_bracket'] = '<=30';
                } elseif ($row['ageing_days'] <= 60) {
                    $row['age_bracket'] = '.31-60';
                } elseif ($row['ageing_days'] <= 90) {
                    $row['age_bracket'] = '.61-90';
                } else {
                    $row['age_bracket'] = '.>90';
                }
            }
        } else {
            $row['weeknum_closed'] = null;
            $row['month_closed'] = null;
            $row['year_month_closed'] = null;
            $row['weeknum_yearmonth_closed'] = null;
            
            if (isset($row['point_status']) && $row['point_status'] === 'Open') {
                $row['closed_date'] = null; // Clear closed date if open
                
                // Ageing days for open points
                if (!empty($row['audit_report_date'])) {
                    $reportDate = strtotime($row['audit_report_date']);
                    $diff = time() - $reportDate;
                    $days = floor($diff / (60 * 60 * 24));
                    $row['ageing_days'] = $days >= 0 ? $days : 0;
                    
                    if ($row['ageing_days'] <= 30) {
                        $row['age_bracket'] = '<=30';
                    } elseif ($row['ageing_days'] <= 60) {
                        $row['age_bracket'] = '.31-60';
                    } elseif ($row['ageing_days'] <= 90) {
                        $row['age_bracket'] = '.61-90';
                    } else {
                        $row['age_bracket'] = '.>90';
                    }
                }
            }
        }

        return $row;
    }
}

