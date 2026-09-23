<?php

namespace App\Controllers\Customer;

use App\Controllers\BaseController;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class Gemba_Dashboard extends BaseController
    {
        public function __construct()
        {
            helper(['gemba_acl', 'designation_acl', 'url', 'form']);
        }

        private function getDynamicAgeingSql()
        {
            return "CASE
                WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) BETWEEN 0 AND 30 THEN '0-30 Days'
                WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) BETWEEN 31 AND 60 THEN '31-60 Days'
                WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) BETWEEN 61 AND 90 THEN '61-90 Days'
                WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) BETWEEN 91 AND 120 THEN '91-120 Days'
                WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) BETWEEN 121 AND 180 THEN '121-180 Days'
                WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) BETWEEN 181 AND 365 THEN '181-365 Days'
                WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) > 365 THEN 'Above 365 Days'
                ELSE 'Not Available'
            END";
        }



        private function applyFilters($builder, $request)
        {
            $builder->where('status !=', 2);

            // Apply Role-Based Visibility
            apply_gemba_role_filters($builder, 'alert_gemba_audits');

            // Only include the latest HSE Audits (exclude historical re-audit records from Gemba)
            $builder->groupStart()
                    ->where("source_module !=", "hse_audit")
                    ->orGroupStart()
                        ->where("source_module", "hse_audit")
                        ->where("hse_audit_id IN (SELECT MAX(hse_audit_id) FROM alert_hse_audit_master GROUP BY audit_no)", null, false)
                    ->groupEnd()
                    ->groupEnd();

            $fields = ['region', 'audit_category', 'site_category', 'site_name', 'point_status', 'auditor_name', 'nc_recommendation']; // Removed age_bracket
            foreach ($fields as $field) {
                $val = $request->getVar($field);
                if (!empty($val)) {
                    if (is_array($val)) {
                        $val = array_filter($val, function($v) { return $v !== 'selectAll'; });
                        if (!empty($val)) {
                            $builder->whereIn($field, $val);
                        }
                    } else if (trim((string) $val) !== '' && $val !== 'selectAll') {
                        $builder->where($field, trim((string) $val));
                    }
                }
            }

            $ageBrackets = $request->getVar('age_bracket');
            if (!empty($ageBrackets) && is_array($ageBrackets)) {
                $ageSql = "DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date)";
                $builder->groupStart();
                foreach ($ageBrackets as $bracket) {
                    switch ($bracket) {
                        case '≤ 30 Days':
                            $builder->orWhere("$ageSql <= 30", null, false);
                            break;
                        case '> 30 Days':
                            $builder->orWhere("$ageSql > 30", null, false);
                            break;
                        case '≤ 60 Days':
                            $builder->orWhere("$ageSql <= 60", null, false);
                            break;
                        case '> 60 Days':
                            $builder->orWhere("$ageSql > 60", null, false);
                            break;
                        case '≤ 90 Days':
                            $builder->orWhere("$ageSql <= 90", null, false);
                            break;
                        case '> 90 Days':
                            $builder->orWhere("$ageSql > 90", null, false);
                            break;
                        case '≤ 120 Days':
                            $builder->orWhere("$ageSql <= 120", null, false);
                            break;
                        case '> 120 Days':
                            $builder->orWhere("$ageSql > 120", null, false);
                            break;
                        case '> 180 Days':
                            $builder->orWhere("$ageSql > 180", null, false);
                            break;
                        case '> 365 Days':
                            $builder->orWhere("$ageSql > 365", null, false);
                            break;
                    }
                }
                $builder->groupEnd();
                // Exclude invalid dates
                $builder->where("audit_report_date IS NOT NULL", null, false);
                $builder->where("audit_report_date != '0000-00-00'", null, false);
            }

            return $builder;
        }

        private function getSummaryData($request, $db)
    {
        $builder = $db->table('alert_gemba_audits');
        $this->applyFilters($builder, $request);

        $summaryData = $builder->select("
            COUNT(*) as total,
            SUM(CASE WHEN LOWER(point_status) IN ('open', 'wip') THEN 1 ELSE 0 END) as open,
            SUM(CASE WHEN LOWER(point_status) = 'closed' THEN 1 ELSE 0 END) as closed,
            SUM(CASE WHEN LOWER(point_status) = 'excluded' THEN 1 ELSE 0 END) as excluded_nc,
            SUM(CASE WHEN LOWER(point_status) IN ('hold', 'hold-review with client') THEN 1 ELSE 0 END) as hold_nc,
            SUM(CASE WHEN LOWER(point_status) IN ('open', 'wip') AND target_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN LOWER(color_code) = 'red' THEN 1 ELSE 0 END) as red,
            SUM(CASE WHEN LOWER(color_code) = 'yellow' THEN 1 ELSE 0 END) as yellow,
            SUM(CASE WHEN LOWER(color_code) = 'black' THEN 1 ELSE 0 END) as black,
            SUM(CASE WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) <= 30 THEN 1 ELSE 0 END) as age_lte_30,
            SUM(CASE WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) > 30 THEN 1 ELSE 0 END) as age_gt_30,
            SUM(CASE WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) <= 60 THEN 1 ELSE 0 END) as age_lte_60,
            SUM(CASE WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) > 60 THEN 1 ELSE 0 END) as age_gt_60,
            SUM(CASE WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) <= 90 THEN 1 ELSE 0 END) as age_lte_90,
            SUM(CASE WHEN DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) > 90 THEN 1 ELSE 0 END) as age_gt_90,
            AVG(
                DATEDIFF(
                    CASE
                        WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client')
                        THEN closed_date
                        ELSE CURDATE()
                    END,
                    audit_report_date
                )
            ) as avg_aging
        ")->get()->getRowArray();

        return [
            'total'      => $summaryData['total'] ?? 0,
            'open'       => $summaryData['open'] ?? 0,
            'closed'     => $summaryData['closed'] ?? 0,
            'excluded'   => $summaryData['excluded_nc'] ?? 0,
            'hold'       => $summaryData['hold_nc'] ?? 0,
            'overdue'    => $summaryData['overdue'] ?? 0,
            'red'        => $summaryData['red'] ?? 0,
            'yellow'     => $summaryData['yellow'] ?? 0,
            'black'      => $summaryData['black'] ?? 0,
            'age_lte_30' => $summaryData['age_lte_30'] ?? 0,
            'age_gt_30'  => $summaryData['age_gt_30'] ?? 0,
            'age_lte_60' => $summaryData['age_lte_60'] ?? 0,
            'age_gt_60'  => $summaryData['age_gt_60'] ?? 0,
            'age_lte_90' => $summaryData['age_lte_90'] ?? 0,
            'age_gt_90'  => $summaryData['age_gt_90'] ?? 0,
            'avg_aging'  => round($summaryData['avg_aging'] ?? 0, 1)
        ];
    }

        private function getRegionWiseData($request, $db)
        {
            $builder = $db->table('alert_gemba_audits');
            $this->applyFilters($builder, $request);
            return $builder->select("
                region, 
                SUM(CASE WHEN LOWER(color_code) = 'red' THEN 1 ELSE 0 END) as red_count,
                SUM(CASE WHEN LOWER(color_code) = 'yellow' THEN 1 ELSE 0 END) as yellow_count,
                SUM(CASE WHEN LOWER(color_code) = 'black' THEN 1 ELSE 0 END) as black_count
            ")->groupBy('region')->get()->getResultArray();
        }

        private function getColorData($request, $db)
        {
            $builder = $db->table('alert_gemba_audits');
            $this->applyFilters($builder, $request);
            $colorData = $builder->select("color_code, COUNT(*) as count")
                ->whereIn('LOWER(color_code)', ['red', 'yellow', 'black'])
                ->whereIn('LOWER(point_status)', ['open', 'wip'])
                ->groupBy('color_code')
                ->get()->getResultArray();
            $ret = ['Red' => 0, 'Yellow' => 0, 'Black' => 0];
            foreach ($colorData as $c) {
                $key = ucfirst(strtolower($c['color_code']));
                $ret[$key] = (int) $c['count'];
            }
            return $ret;
        }

        private function getNcRecData($request, $db)
        {
            $builder = $db->table('alert_gemba_audits');
            $this->applyFilters($builder, $request);
            $ncRecData = $builder->select("nc_recommendation, COUNT(*) as count")
                ->whereIn('nc_recommendation', ['NC', 'RECOMMENDATION'])
                ->whereIn('LOWER(point_status)', ['open', 'wip'])
                ->groupBy('nc_recommendation')
                ->get()->getResultArray();
            $ret = ['NC' => 0, 'RECOMMENDATION' => 0];
            foreach ($ncRecData as $n)
                $ret[$n['nc_recommendation']] = (int) $n['count'];
            return $ret;
        }

        private function getMonthlyTrendData($request, $db)
        {
            $builder = $db->table('alert_gemba_audits');
            $this->applyFilters($builder, $request);
            return $builder->select("
                DATE_FORMAT(audit_report_date, '%b %Y') as month_year,
                YEAR(audit_report_date) as yr,
                MONTH(audit_report_date) as mnth,
                COUNT(*) as total,
                SUM(CASE WHEN LOWER(point_status) = 'open' THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN 1 ELSE 0 END) as closed,
                SUM(CASE WHEN LOWER(point_status) = 'open' AND target_date < CURDATE() THEN 1 ELSE 0 END) as overdue
            ")
                ->where("audit_report_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)")
                ->groupBy('yr, mnth, month_year')
                ->orderBy('yr', 'ASC')
                ->orderBy('mnth', 'ASC')
                ->get()->getResultArray();
        }

        public function index()
        {
            $db = \Config\Database::connect();
            $request = \Config\Services::request();
            
            $data = [];

            // Fetch latest unread notification for the logged-in user
            $userId = session()->get('user_id') ?? 0;
            $gembaNotification = $db->table('alert_gemba_notifications')
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->orderBy('id', 'DESC')
                ->get()
                ->getRowArray();
            $data['gemba_notification'] = $gembaNotification;

            // Calculate dynamic real-time pending counts for the current week widget (due this week based on target_date)
            $userDesignation = getUserDesignation();
            $userName = getUserName();
            
            $widgetBuilder = $db->table('alert_gemba_audits')
                ->where('status !=', 2)
                ->where('nc_status !=', 3);
                
            $now = time();
            $dayOfWeek = date('N', $now);
            $monday = strtotime('-' . ($dayOfWeek - 1) . ' days', strtotime(date('Y-m-d 00:00:00', $now)));
            $sunday = strtotime('+' . (7 - $dayOfWeek) . ' days', strtotime(date('Y-m-d 23:59:59', $now)));
            
            $startDate = date('Y-m-d', $monday);
            $endDate = date('Y-m-d', $sunday);
            
            $widgetBuilder->where('target_date >=', $startDate)
                        ->where('target_date <=', $endDate);

            
            if ($userDesignation === 'account manager' || $userDesignation === 'account_manager') {
                $widgetBuilder->where('account_manager', $userName);
            } else if ($userDesignation === 'cluster manager' || $userDesignation === 'cluster_manager') {
                $widgetBuilder->where('cluster_manager_spoc', $userName);
            } else if ($userDesignation === 'auditor') {
                $widgetBuilder->where('auditor_name', $userName);
            }
            
            $widgetRecords = $widgetBuilder->get()->getResultArray();
            
            $widgetCounts = [
                'black' => 0,
                'red' => 0,
                'yellow' => 0,
                'total' => 0
            ];
            
            foreach ($widgetRecords as $wr) {
                $color = strtoupper(trim($wr['color_code'] ?? ''));
                if ($color === 'BLACK') $widgetCounts['black']++;
                elseif ($color === 'RED') $widgetCounts['red']++;
                elseif ($color === 'YELLOW') $widgetCounts['yellow']++;
            }
            $widgetCounts['total'] = count($widgetRecords);
            $data['widget_counts'] = $widgetCounts;

            // Build Filters
            $filters = [
                'region' => (array) ($request->getVar('region') ?? []),
                'audit_category' => (array) ($request->getVar('audit_category') ?? []),
                'site_category' => (array) ($request->getVar('site_category') ?? []),
                'site_name' => (array) ($request->getVar('site_name') ?? []),
                'point_status' => (array) ($request->getVar('point_status') ?? []),
                'auditor_name' => (array) ($request->getVar('auditor_name') ?? []),
                'nc_recommendation' => (array) ($request->getVar('nc_recommendation') ?? []),
                'age_bracket' => (array) ($request->getVar('age_bracket') ?? []),
            ];

            // Fetch independent dropdowns
            $db_table = $db->table('alert_gemba_audits');
            $db_site_table = $db->table('alert_gemba_sites');

            $builderRegion = clone $db_table;
            $data['regions'] = apply_gemba_role_filters($builderRegion, 'alert_gemba_audits')->select('DISTINCT(region)')->where('status !=', 2)->where('region !=', '')->orderBy('region', 'ASC')->get()->getResultArray();

            $builderCategory = clone $db_table;
            $data['audit_categories'] = apply_gemba_role_filters($builderCategory, 'alert_gemba_audits')->select('DISTINCT(audit_category)')->where('status !=', 2)->where('audit_category !=', '')->orderBy('audit_category', 'ASC')->get()->getResultArray();

            $builderAuditor = clone $db_table;
            $data['auditors'] = apply_gemba_role_filters($builderAuditor, 'alert_gemba_audits')->select('DISTINCT(auditor_name)')->where('status !=', 2)->where('auditor_name !=', '')->orderBy('auditor_name', 'ASC')->get()->getResultArray();

            $builderSiteCat = clone $db_table;
            $builderSiteCat->select('DISTINCT(site_category)')->where('status !=', 2)->where('site_category !=', '')->orderBy('site_category', 'ASC');
            if (!empty($filters['region']) && !in_array('selectAll', $filters['region'])) {
                $builderSiteCat->whereIn('region', $filters['region']);
            }
            $data['site_categories'] = apply_gemba_role_filters($builderSiteCat, 'alert_gemba_audits')->get()->getResultArray();

            $builderSiteName = clone $db_table;
            $builderSiteName->select('DISTINCT(site_name)')->where('status !=', 2)->where('site_name !=', '')->orderBy('site_name', 'ASC');
            if (!empty($filters['region']) && !in_array('selectAll', $filters['region'])) {
                $builderSiteName->whereIn('region', $filters['region']);
            }
            if (!empty($filters['site_category']) && !in_array('selectAll', $filters['site_category'])) {
                $builderSiteName->whereIn('site_category', $filters['site_category']);
            }
            $data['site_names'] = apply_gemba_role_filters($builderSiteName, 'alert_gemba_audits')->get()->getResultArray();

            $builderNcType = clone $db_table;
            $data['nc_types'] = apply_gemba_role_filters($builderNcType, 'alert_gemba_audits')->select('DISTINCT(nc_recommendation)')->where('status !=', 2)->where('nc_recommendation !=', '')->orderBy('nc_recommendation', 'ASC')->get()->getResultArray();

            $data['age_brackets'] = [
                ['age_bracket' => '≤ 30 Days'],
                ['age_bracket' => '> 30 Days'],
                ['age_bracket' => '≤ 60 Days'],
                ['age_bracket' => '> 60 Days'],
                ['age_bracket' => '≤ 90 Days'],
                ['age_bracket' => '> 90 Days'],
                ['age_bracket' => '≤ 120 Days'],
                ['age_bracket' => '> 120 Days'],
                ['age_bracket' => '> 180 Days'],
                ['age_bracket' => '> 365 Days'],
            ];

            $data['filters'] = $filters;

            // --- SUMMARY CARDS ---
            $data['summary'] = $this->getSummaryData($request, $db);

            // --- CHARTS DATA ---
            // Chart 1: Region-wise
            $data['chart_region'] = $this->getRegionWiseData($request, $db);

            // Chart 2: Color Code (Open Only)
            $data['chart_color'] = $this->getColorData($request, $db);

            // Chart 3: NC vs Recommendation (Only NC and RECOMMENDATION)
            $data['chart_nc_rec'] = $this->getNcRecData($request, $db);

            // Chart 4: Monthly Audit Trend
            $data['chart_monthly_trend'] = $this->getMonthlyTrendData($request, $db);

            $data['aging_summary'] = $data['summary'];
            $data['aging_summary_total'] = $data['summary']['total'];

            $hiddenInputs = '';
            foreach ($filters as $key => $valArray) {
                foreach ($valArray as $val) {
                    if ($val !== '') {
                        $hiddenInputs .= '<input type="hidden" name="' . htmlspecialchars($key) . '[]" value="' . htmlspecialchars($val) . '">';
                    }
                }
            }
            $data['hiddenInputs'] = $hiddenInputs;

            // Gemba Table Config
            $tdataGemba = [
                'example2' => 'gemba_reports_table',
                'is_server_side' => 'true',
                'ajax_type' => 'POST',
                'ajax_url_for_data' => base_url('Customer/Gemba_Dashboard/get_data'),
                'ajax_data' => $filters,
                'enable_export' => false,
                'export_button' => '<form action="' . base_url('Customer/Gemba_Dashboard/export_gemba') . '" method="POST" style="display:inline-block;">' . $hiddenInputs . '<button type="submit" class="btn btn-info btn-sm"><i class="fas fa-file-csv"></i> Export CSV</button></form>',
                'title' => 'Gemba & Aging Reports',
                'display_contents' => [
                    'gemba_sr_no' => 'Sr. No.',
                    'unique_no' => 'Unique No',
                    'region' => 'Region',
                    'auditor_name' => 'Auditor',
                    'audit_category' => 'Audit Category',
                    'audit_type' => 'Audit Type',
                    'cluster_manager_spoc' => 'Cluster Manager',
                    'account_manager' => 'Account Manager',
                    'site_name' => 'Site Name',
                    'site_category' => 'Category',
                    'audit_report_date' => 'Audit Report Date',
                    'target_date' => 'Target Date',
                    'closed_date' => 'Close Date',
                    'ageing_days' => 'Aging (Days)',
                    'age_bracket' => 'Age Bracket',
                    'nc_recommendation' => 'NC/Rec',
                    'observation_point' => 'Observation',
                    'action_recommendation' => 'Recommendation Action',
                    'color_code' => 'Color',
                    'point_status' => 'Status'
                ]
            ];

            $data['gemba_table'] = view('Layout/table-view', $tdataGemba);

            return view('Customer/gemba_dashboard', $data);
        }

        public function get_data()
        {
            $db = \Config\Database::connect();
            $request = \Config\Services::request();

            $start = $request->getVar('start') ?? 0;
            $length = $request->getVar('length') ?? 15;

            $searchPost = $request->getVar('search');
            $search = is_array($searchPost) && isset($searchPost['value']) ? trim((string) $searchPost['value']) : '';

            $builder = $db->table('alert_gemba_audits');
            $builder = $this->applyFilters($builder, $request);

            if (!empty($search)) {
                $builder->groupStart()
                    ->like('unique_no', $search)
                    ->orLike('observation_point', $search)
                    ->orLike('site_name', $search)
                    ->groupEnd();
            }

            $totalRecords = $builder->countAllResults(false);

            $builder->select("*, DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) AS dynamic_ageing_days, " . $this->getDynamicAgeingSql() . " AS dynamic_age_bracket");

            $orderPost = $request->getVar('order');
            $columnsPost = $request->getVar('columns');

            if (!empty($orderPost) && !empty($columnsPost)) {
                $columnIndex = $orderPost[0]['column'];
                $columnName = $columnsPost[$columnIndex]['data'];
                $columnSortOrder = $orderPost[0]['dir'];
                
                // basic sanitization
                if (in_array($columnSortOrder, ['asc', 'desc']) && !empty($columnName)) {
                    $builder->orderBy($columnName, $columnSortOrder);
                } else {
                    $builder->orderBy('gemba_sr_no', 'DESC');
                }
            } else {
                $builder->orderBy('gemba_sr_no', 'DESC');
            }

            if ($length != -1) {
                $builder->limit($length, $start);
            }

            $data = $builder->get()->getResultArray();

            // format colors
            foreach ($data as &$row) {
                $color = strtolower(trim($row['color_code']));
                if ($color == 'red') {
                    $row['color_code'] = '<span class="badge bg-danger">RED</span>';
                } elseif ($color == 'yellow') {
                    $row['color_code'] = '<span class="badge bg-warning text-dark">YELLOW</span>';
                } elseif ($color == 'black') {
                    $row['color_code'] = '<span class="badge bg-dark">BLACK</span>';
                } else {
                    $row['color_code'] = '';
                }

                $status = strtolower(trim($row['point_status'] ?? ''));

                if ($status == 'open') {
                    $row['point_status'] = '<span class="badge bg-danger">Open</span>';
                } elseif ($status == 'closed') {
                    $row['point_status'] = '<span class="badge bg-success">Closed</span>';
                } elseif ($status == 'excluded') {
                    $row['point_status'] = '<span class="badge bg-dark">Excluded</span>';
                } elseif ($status == 'hold-review with client') {
                    $row['point_status'] = '<span class="badge bg-warning text-dark">Hold-review with Client</span>';
                } else {
                    $row['point_status'] = '<span class="badge bg-secondary">N/A</span>';
                }

                $ageing_val = $row['dynamic_ageing_days'] ?? 'NA';
                $ageing_bracket = $row['dynamic_age_bracket'] ?? 'NA';
                
                if ($ageing_val !== 'NA') {
                    $agCls = (int) $ageing_val > 30 ? 'badge-aging-bad' : 'badge-aging-ok';
                    $row['ageing_days'] = '<span class="' . $agCls . '">' . htmlspecialchars((string)$ageing_val) . '</span>';
                    $row['age_bracket'] = '<span class="' . $agCls . '">' . htmlspecialchars((string)$ageing_bracket) . '</span>';
                } else {
                    $row['ageing_days'] = '<span class="badge bg-secondary">NA</span>';
                    $row['age_bracket'] = '<span class="badge bg-secondary">NA</span>';
                }

                // Add safe escaping for text
                $row['observation_point'] = htmlspecialchars($row['observation_point'] ?? '');
                $row['action_recommendation'] = htmlspecialchars($row['action_recommendation'] ?? '');
                $row['risks_details'] = htmlspecialchars($row['risks_details'] ?? '');
            }

            return $this->response->setJSON([
                "draw" => intval($request->getVar('draw')),
                "recordsTotal" => $totalRecords,
                "recordsFiltered" => $totalRecords,
                "data" => $data
            ]);
        }

        private function exportDetailedExcel($builder, $filenamePrefix)
        {
            $audits = $builder->select("*, DATEDIFF(CASE WHEN LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN closed_date ELSE CURDATE() END, audit_report_date) AS dynamic_ageing_days, " . $this->getDynamicAgeingSql() . " AS dynamic_age_bracket")
                            ->orderBy('gemba_sr_no', 'DESC')
                            ->get()
                            ->getResultArray();

            while (ob_get_level()) {
                ob_end_clean();
            }

            $filename = $filenamePrefix . "_" . date('Ymd_His') . ".csv";

            header("Content-Type: text/csv; charset=utf-8");
            header("Content-Disposition: attachment; filename=\"$filename\"");
            header("Pragma: no-cache");
            header("Expires: 0");

            $fp = fopen('php://output', 'w');
            if (!$fp) {
                exit('Unable to open output stream');
            }

            // UTF-8 BOM
            fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

            $headers = ['Gemba Sr. No.', 'Unique No', 'Region', 'Auditor Name', 'Audit Category', 'Audit Type', 'Audit Document Type', 'Site Name', 'Site Type-1', 'Site Type-2', 'Site Category', 'Audit mail received date', 'Gemba Round Done on/Audit report date', 'Weeknum for Point Raised', 'Month for Point raised', 'Year-Month for Point raised', 'Year', 'Checklist Category', 'NC/RECOMMENDATION', 'QHSE REMARKS', 'Gemba round observations/Point', 'Risks & Details', 'Action / Recommendation', 'Specifications/Other details/Tips for Capex-Opex Infra, if any', 'UA/UC, Quality or others', 'Risk Severity', 'Risk Probability', 'COLOR CODE', 'Cost Type', 'Remarks (By WMs) / Action Plan', 'Whose Scope?', 'Remarks-2 (By Corporate HO)', 'Ageing in days', 'Age Bracket', 'Target Date-1', 'Closed Date', 'Point Open/Closed', 'Point Category', 'Point Closure status', 'Weeknum for Point Closed', 'Month for Point Closed', 'Year-Month for Point Closed', 'Risk Severity Rating', 'Risk Probability Rating', 'Combined Risk Rating', 'No. of times point repeated', 'Combined Risk Rating wrt Frequency', 'Final Rating', 'Follow-up by', 'Weeknum & Year-Month (Pts Raised)', 'Weeknum & Year-Month (Pts Closed)', 'Cluster Manager/SPOC'];

            fputcsv($fp, $headers);

            foreach ($audits as $a) {
                $color = strtolower(trim((string) ($a['color_code'] ?? '')));

                $wk_raised = (!empty($a['weeknum_yearmonth_raised'])) ? $a['weeknum_yearmonth_raised'] : 'NA';
                $wk_closed = (!empty($a['weeknum_yearmonth_closed'])) ? $a['weeknum_yearmonth_closed'] : 'NA';

                $row_data = [
                    $a['gemba_sr_no'] ?? 'NA',
                    $a['unique_no'] ?? 'NA',
                    $a['region'] ?? 'NA',
                    $a['auditor_name'] ?? 'NA',
                    $a['audit_category'] ?? 'NA',
                    $a['audit_type'] ?? 'NA',
                    $a['audit_doc_type'] ?? 'NA',
                    $a['site_name'] ?? 'NA',
                    $a['site_type_1'] ?? 'NA',
                    $a['site_type_2'] ?? 'NA',
                    $a['site_category'] ?? 'NA',
                    $a['audit_mail_received_date'] ?? 'NA',
                    $a['audit_report_date'] ?? 'NA',
                    $a['weeknum_raised'] ?? 'NA',
                    $a['month_raised'] ?? 'NA',
                    $a['year_month_raised'] ?? 'NA',
                    $a['year_raised'] ?? 'NA',
                    $a['checklist_category'] ?? 'NA',
                    $a['nc_recommendation'] ?? 'NA',
                    $a['qhse_remarks'] ?? 'NA',
                    $a['observation_point'] ?? 'NA',
                    $a['risks_details'] ?? 'NA',
                    $a['action_recommendation'] ?? 'NA',
                    $a['specifications'] ?? 'NA',
                    $a['ua_uc_type'] ?? 'NA',
                    $a['risk_severity'] ?? 'NA',
                    $a['risk_probability'] ?? 'NA',
                    ucfirst($color) ?: 'NA',
                    $a['cost_type'] ?? 'NA',
                    $a['remarks_wm'] ?? 'NA',
                    $a['whose_scope'] ?? 'NA',
                    $a['remarks_corporate'] ?? 'NA',
                    $a['dynamic_ageing_days'] ?? 'NA',
                    $a['dynamic_age_bracket'] ?? 'NA',
                    $a['target_date'] ?? 'NA',
                    $a['closed_date'] ?? 'NA',
                    $a['point_status'] ?? 'NA',
                    $a['point_category'] ?? 'NA',
                    $a['closure_status'] ?? 'NA',
                    $a['weeknum_closed'] ?? 'NA',
                    $a['month_closed'] ?? 'NA',
                    $a['year_month_closed'] ?? 'NA',
                    $a['risk_severity_rating'] ?? 'NA',
                    $a['risk_probability_rating'] ?? 'NA',
                    $a['combined_risk_rating'] ?? 'NA',
                    $a['times_repeated'] ?? 'NA',
                    $a['combined_risk_freq'] ?? 'NA',
                    $a['final_rating'] ?? 'NA',
                    (!empty($a['followup_by']) ? $a['followup_by'] : 'NA'),
                    $wk_raised,
                    $wk_closed,
                    $a['cluster_manager_spoc'] ?? 'NA'
                ];

                // Normalize empty values to 'NA' and clean line breaks to prevent text wrapping in Excel
                foreach ($row_data as $index => $val) {
                    if (!isset($val) || trim((string) $val) === '') {
                        $row_data[$index] = 'NA';
                    } else {
                        $cleaned = str_replace(["\r\n", "\r", "\n"], " ", (string) $val);
                        $row_data[$index] = preg_replace('/\s+/', ' ', trim($cleaned));
                    }
                }

                fputcsv($fp, $row_data);
            }

            fclose($fp);
            exit();
        }

        public function export_gemba()
        {
            $db = \Config\Database::connect();
            $request = \Config\Services::request();

            $builder = $db->table('alert_gemba_audits');
            $builder = $this->applyFilters($builder, $request);
            $this->exportDetailedExcel($builder, "Gemba_Reports_Export");
        }

        public function exportChartExcel()
        {
            $chartType = $this->request->getVar('chart_type');

            switch ($chartType) {

                case 'open_closed':
                    return $this->exportOpenClosedChart();

                case 'color_code':
                    return $this->exportColorCodeChart();

                case 'nc_recommendation':
                    return $this->exportNcRecommendationChart();

                case 'region_wise':
                    return $this->exportRegionWiseChart();

                case 'monthly_trend':
                    return $this->exportMonthlyTrendChart();
            }
        }

        private function exportColorCodeChart()
        {
            $db = \Config\Database::connect();
            $builder = $db->table('alert_gemba_audits');
            $builder = $this->applyFilters($builder, $this->request);
            $builder->whereIn('LOWER(color_code)', ['red', 'yellow', 'black']);
            $builder->whereIn('LOWER(point_status)', ['open', 'wip']);
            $this->exportDetailedExcel($builder, 'Open_Points_by_Color_Code_Data');
        }

        private function exportOpenClosedChart()
        {
            $db = \Config\Database::connect();
            $builder = $db->table('alert_gemba_audits');
            $builder = $this->applyFilters($builder, $this->request);
            $builder->whereIn('LOWER(point_status)', ['open', 'closed', 'excluded', 'hold-review with client']);
            $this->exportDetailedExcel($builder, 'Open_vs_Closed_Data');
        }

        private function exportNcRecommendationChart()
        {
            $db = \Config\Database::connect();
            $builder = $db->table('alert_gemba_audits');
            $builder = $this->applyFilters($builder, $this->request);
            $builder->whereIn('nc_recommendation', ['NC', 'RECOMMENDATION']);
            $builder->whereIn('LOWER(point_status)', ['open', 'wip']);
            $this->exportDetailedExcel($builder, 'NC_vs_Recommendation_Data');
        }

        private function exportRegionWiseChart()
        {
            $db = \Config\Database::connect();
            $builder = $db->table('alert_gemba_audits');
            $builder = $this->applyFilters($builder, $this->request);
            $builder->whereIn('LOWER(color_code)', ['red', 'yellow', 'black']);
            $this->exportDetailedExcel($builder, 'Region_Wise_Data');
        }

        private function exportMonthlyTrendChart()
        {
            $db = \Config\Database::connect();
            $builder = $db->table('alert_gemba_audits');
            $builder = $this->applyFilters($builder, $this->request);
            $builder->where("audit_report_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)");
            $this->exportDetailedExcel($builder, 'Monthly_Trend_Data');
        }
        public function get_dependent_dropdowns()
        {
            $db = \Config\Database::connect();
            $req = service('request');
            $type = $req->getPost('type');
            $regions = $req->getPost('regions') ?? [];
            $site_categories = $req->getPost('site_categories') ?? [];

            if ($type === 'site_category') {
                $builder = $db->table('alert_gemba_audits')->select('DISTINCT(site_category)')->where('status !=', 2)->where('site_category !=', '')->orderBy('site_category', 'ASC');
                if (!empty($regions)) {
                    $builder->whereIn('region', $regions);
                }
                apply_gemba_role_filters($builder, 'alert_gemba_audits');
                $data = $builder->get()->getResultArray();
                return $this->response->setJSON($data);
            }

            if ($type === 'site_name') {
                $builder = $db->table('alert_gemba_audits')->select('DISTINCT(site_name)')->where('status !=', 2)->where('site_name !=', '')->orderBy('site_name', 'ASC');
                if (!empty($regions)) {
                    $builder->whereIn('region', $regions);
                }
                if (!empty($site_categories)) {
                    $builder->whereIn('site_category', $site_categories);
                }
                apply_gemba_role_filters($builder, 'alert_gemba_audits');
                $data = $builder->get()->getResultArray();
                return $this->response->setJSON($data);
            }

            return $this->response->setJSON([]);
        }
    }
