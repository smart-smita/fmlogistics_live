<?php

namespace App\Controllers\Customer;

use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;
use App\Traits\ACLTrait;

class Audit_Dashboard_HSE extends BaseController
{
    use ACLTrait;

    public $BaseModel = null;

    public function __construct()
    {
        helper(['designation_acl', 'hse_acl_helper']);
    }

    private function formatKey($name)
    {
        $key = strtolower(trim($name));
        $key = str_replace(['&'], ['and'], $key);
        $key = preg_replace('/[^a-z0-9]+/', '_', $key);
        $key = trim($key, '_');
        return $key;
    }

    /**
     * Build a safe SQL WHERE condition for both single values and arrays.
     * Returns a string like " AND column = 'value'" or " AND column IN ('val1', 'val2')".
     */
    private function buildSqlCondition($column, $value)
    {
        $db = db_connect();
        if (empty($value)) {
            return "";
        }

        if (is_array($value)) {
            $escapedValues = array_map([$db, 'escape'], $value);
            return " AND {$column} IN (" . implode(',', $escapedValues) . ")";
        } else {
            return " AND {$column} = " . $db->escape($value);
        }
    }

    private function getCommonData($db)
    {
        $data = [];
        $data['siteCategories'] = $db->table('alert_hse_site_category')
            ->where('status', 1)
            ->get()->getResultArray();

        // Fetch Audit Categories dynamically
        $data['auditCategories'] = $db->table('alert_audit_questions')
            ->select('DISTINCT(audit_category) as audit_category')
            ->where('audit_category IS NOT NULL')
            ->where('audit_category !=', '')
            ->get()->getResultArray();

        $data['months'] = [
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'August',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
        ];

        $data['dynamic_regions'] = $this->getDynamicRegions($db);
        $data['region_color_map'] = $this->getRegionColorMap($data['dynamic_regions']);

        // ACL logic for initial view
        $data['is_higher_authority'] = isHigherAuthority();

        if (isClusterManager()) {
            $data['is_cluster_manager'] = true;
            $data['is_account_manager'] = false;

            $data['locked_region'] = getClusterManagerAssignedRegionHSE();
            $data['locked_cluster'] = getClusterManagerAssignedClusterHSE();

            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r];
            }, (array) $data['locked_region']);

            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c];
            }, (array) $data['locked_cluster']);

            $locationQuery = $db->table('alert_hse_client_master')
                ->select('DISTINCT(client_name) as location_name')
                ->whereIn('cluster', (array) $data['locked_cluster'])
                ->where('status', 1);

            $data['location'] = $locationQuery->get()->getResultArray();

        } elseif (isAccountManager()) {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = true;

            $assignedDetails = getAccountManagerAssignedDetailsHSE();
            $data['locked_region'] = array_unique(array_filter(array_column($assignedDetails, 'region')));
            $data['locked_cluster'] = array_unique(array_filter(array_column($assignedDetails, 'cluster')));
            $assignedClients = array_unique(array_filter(array_column($assignedDetails, 'client_name')));

            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r];
            }, (array) $data['locked_region']);

            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c];
            }, (array) $data['locked_cluster']);

            $data['location'] = array_map(function ($c) {
                return ['location_name' => $c];
            }, $assignedClients);

        } else {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = false;

            $data['region'] = $db->table('alert_hse_client_master')
                ->select('DISTINCT(region) as region_name')
                ->where('status', 1)
                ->orderBy('region', 'ASC')
                ->get()->getResultArray();

            $data['cluster'] = [];
            $data['location'] = [];
        }

        return $data;
    }

    public function HSE_Audit()
    {
        ini_set('memory_limit', '512M');
        set_time_limit(300);
        $db = db_connect();
        $post = $this->request->getVar();

        $data = $this->getCommonData($db);

        $region = array_filter((array)($post['region'] ?? []));
        $cluster = array_filter((array)($post['cluster_name'] ?? []));
        $client = array_filter((array)($post['location_name'] ?? []));
        $month = array_filter((array)($post['month'] ?? []));
        $year = array_filter((array)($post['filter_year'] ?? []));

        // Normalize month values (convert 'April' to 4, etc.)
        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3,
            'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9,
            'october' => 10, 'november' => 11, 'december' => 12
        ];
        $selectedMonthNums = [];
        foreach ($month as $m) {
            $m = (string)$m;
            if (ctype_digit($m)) {
                $selectedMonthNums[] = (int)$m;
            } else {
                $key = strtolower(trim($m));
                if (isset($monthMap[$key])) {
                    $selectedMonthNums[] = $monthMap[$key];
                }
            }
        }
        $selectedMonthNums = array_unique($selectedMonthNums);

        $data['selected_region'] = $region;
        $data['selected_cluster'] = $cluster;
        $data['selected_location'] = $client;
        $data['selected_month'] = $month;
        $data['selected_year'] = $year;
        $data['selectedMonthNums'] = $selectedMonthNums;

        if (!$data['is_cluster_manager'] && !$data['is_account_manager']) {
            if (!empty($region)) {
                $clusterQuery = $db->table('alert_hse_client_master')
                    ->select('DISTINCT(cluster) as cluster_name')
                    ->whereIn('region', $region)
                    ->where('status', 1)
                    ->orderBy('cluster', 'ASC');
                $data['cluster'] = $clusterQuery->get()->getResultArray();
            }
            if (!empty($cluster)) {
                $locationQuery = $db->table('alert_hse_client_master')
                    ->select('DISTINCT(client_name) as location_name');
                if (!empty($region)) {
                    $locationQuery->whereIn('region', $region);
                }
                $locationQuery->whereIn('cluster', $cluster)
                    ->where('status', 1)
                    ->orderBy('client_name', 'ASC');
                $data['location'] = $locationQuery->get()->getResultArray();
            }
        }

        $aclFilter = getClusterFilterByClientForHSE('m.client_name');
        $where = "WHERE m.status != 2 {$aclFilter}";

        $where .= $this->buildSqlCondition('m.region', $region);
        $where .= $this->buildSqlCondition('m.cluster_name', $cluster);
        $where .= $this->buildSqlCondition('m.client_name', $client);
        $where .= $this->buildSqlCondition('MONTH(m.audit_date)', $selectedMonthNums);
        $where .= $this->buildSqlCondition('YEAR(m.audit_date)', $year);

        $this->getDashboardData($db, $data, $where);

        return view("Customer/hse_dashboard", $data);
    }

    private function getDashboardData($db, &$data, $where)
    {
        $siteCategories = $data['siteCategories'];
        $auditCategories = $data['auditCategories'];

        // 1. Pie Chart - Region wise Score (Full Circle Design handled in View)
        $data['pie_chart'] = $db->query("
            SELECT m.region, ROUND(AVG(m.score), 2) AS score, COUNT(*) AS total_audits
            FROM alert_hse_audit_master m
            INNER JOIN (
                SELECT client_name, MAX(hse_audit_id) as latest_id
                FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name
            ) latest ON m.hse_audit_id = latest.latest_id
            {$where}
            AND m.region IS NOT NULL AND m.region != ''
            GROUP BY m.region
        ")->getResultArray();

        // 2. Open Points Summary (Location-wise) - Used for Bar Chart too
        $colOpen = [];
        foreach ($siteCategories as $cat) {
            $key = $this->formatKey($cat['site_category_name']);
            $colOpen[] = "SUM(CASE WHEN TRIM(LOWER(d.site_category)) = TRIM(LOWER(" . $db->escape($cat['site_category_name']) . ")) AND d.nc_status != 3 THEN 1 ELSE 0 END) AS {$key}_open";
        }
        $colOpenSQL = implode(", ", $colOpen);

        $data['total_HSE_score_openpoints'] = $db->query("
            SELECT m.client_name as location, m.region, m.cluster_name,
                   SUM(CASE WHEN d.finding = 'NO' AND d.nc_status = 0 THEN 1 ELSE 0 END) as open_pts,
                    SUM(CASE WHEN d.finding = 'NO' AND d.nc_status = 1 THEN 1 ELSE 0 END) as working_pts,
                    SUM(CASE WHEN d.finding = 'NO' AND d.nc_status = 5 THEN 1 ELSE 0 END) as review_cm,
                    SUM(CASE WHEN d.finding = 'NO' AND d.nc_status = 2 THEN 1 ELSE 0 END) as review_auditor,
                    SUM(CASE WHEN d.finding = 'NO' AND d.nc_status = 3 THEN 1 ELSE 0 END) as closed_pts,
                   IFNULL(SUM(CASE WHEN d.finding = 'NO' AND d.nc_status IN (0, 1, 5, 2) THEN 1 ELSE 0 END), 0) as total_active_open,
                   {$colOpenSQL}
            FROM alert_hse_audit_master m
            INNER JOIN (
                SELECT client_name, MAX(hse_audit_id) as latest_id
                FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name
            ) latest ON m.hse_audit_id = latest.latest_id
            LEFT JOIN alert_hse_audit_details d ON m.hse_audit_id = d.hse_audit_id AND d.finding = 'NO'
            {$where}
            GROUP BY m.client_name, m.region, m.cluster_name
            ORDER BY total_active_open DESC
        ")->getResultArray();

        // 3. Month Summary (Financial Year: April - March, Latest Entry Only)
        $data['month_summary'] = $db->query("
            SELECT m.client_name as location, m.region, m.cluster_name,
                   DATE_FORMAT(m.audit_date, '%m') as month_num,
                   DATE_FORMAT(m.audit_date, '%M') as month_name,
                   m.score
            FROM alert_hse_audit_master m
            INNER JOIN (
                SELECT client_name, DATE_FORMAT(audit_date, '%Y-%m') as ym, MAX(hse_audit_id) as latest_id
                FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name, ym
            ) latest ON m.hse_audit_id = latest.latest_id
            {$where}
            ORDER BY m.client_name, m.audit_date ASC
        ")->getResultArray();

        // 4. Open Points Report (All Locations, Latest Entry Only)
        // $data['open_point_report'] = $db->query("
        //     SELECT m.client_name as location, m.region, m.cluster_name, m.audit_date,
        //            d.audit_category, d.audit_question, d.nc_remark, m.perform_audit_by,
        //            DATE_FORMAT(m.audit_date, '%M') as audit_month
        //     FROM alert_hse_audit_master m
        //     INNER JOIN (
        //         SELECT client_name, MAX(hse_audit_id) as latest_id
        //         FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name
        //     ) latest ON m.hse_audit_id = latest.latest_id
        //     LEFT JOIN alert_hse_audit_details d ON m.hse_audit_id = d.hse_audit_id
        //     {$where}
        //     AND d.finding = 'NO' AND d.nc_status != 3
        //     ORDER BY m.audit_date DESC
        // ")->getResultArray();


        // Open Points Report - Latest Entry Audit No Wise (Only Open NO Findings)
        $data['open_point_report'] = $db->query("
                SELECT 
                    m.hse_audit_id,
                    m.audit_no,
                    m.client_name AS location,
                    m.region,
                    m.cluster_name,
                    m.audit_date,
                    m.perform_audit_by,
                    DATE_FORMAT(m.audit_date, '%M') AS audit_month,
                    d.id AS detail_id,
                    d.site_category,
                    d.sub_category,
                    d.audit_category,
                    d.audit_question,
                    d.remark,
                    d.finding,
                    d.nc_status,
                    d.nc_remark,
                    d.capa_json,
                    d.default_date,
                    d.update_date
                FROM alert_hse_audit_master m

                INNER JOIN (
                    SELECT audit_no, MAX(hse_audit_id) AS latest_id
                    FROM alert_hse_audit_master
                    WHERE status != 2
                    GROUP BY audit_no
                ) latest 
                    ON m.hse_audit_id = latest.latest_id

                INNER JOIN alert_hse_audit_details d 
                    ON m.hse_audit_id = d.hse_audit_id

                {$where}

                AND d.finding = 'NO'
                AND d.nc_status != 3
                AND d.status != 2

                ORDER BY m.audit_date DESC, m.audit_no ASC, d.id DESC
            ")->getResultArray();

        // Decode CAPA JSON into separate report columns
        foreach ($data['open_point_report'] as &$row) {
            $row['findings_text'] = '';
            $row['risk_text'] = '';
            $row['actions_text'] = '';
            $row['action_category_text'] = '';
            $row['ua_uc_text'] = '';
            $row['risk_severity_text'] = '';
            $row['risk_probability_text'] = '';
            $row['color_code_text'] = '';
            $row['cost_type_text'] = '';
            $row['combined_risk_rating_text'] = '';

            if (!empty($row['capa_json'])) {
                $decoded = json_decode($row['capa_json'], true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $findingKey = strtolower(trim($row['finding'] ?? ''));
                    if (!in_array($findingKey, ['yes', 'no', 'na'])) {
                        $findingKey = 'no';
                    }

                    $row['findings_text'] = $decoded['findings'][$findingKey] ?? '';
                    $row['risk_text'] = $decoded['risk'][$findingKey] ?? '';
                    $row['actions_text'] = $decoded['actions'][$findingKey] ?? '';
                    $row['action_category_text'] = $decoded['action_category'][$findingKey] ?? '';
                    $row['ua_uc_text'] = $decoded['ua_uc'][$findingKey] ?? '';
                    $row['risk_severity_text'] = $decoded['risk_severity'][$findingKey] ?? '';
                    $row['risk_probability_text'] = $decoded['risk_probability'][$findingKey] ?? '';
                    $row['color_code_text'] = $decoded['color_code'][$findingKey] ?? '';
                    $row['cost_type_text'] = $decoded['cost_type'][$findingKey] ?? '';
                    $row['combined_risk_rating_text'] = $decoded['combined_risk_rating'][$findingKey] ?? '';
                }
            }
        }
        unset($row);


        // =====================================
        // Color Code Wise Gemba Points (Filtered)
        // =====================================
        $colorCodeCounts = [];

        foreach ($data['open_point_report'] as $row) {
            // Only NO findings
            if (strtoupper(trim($row['finding'] ?? '')) !== 'NO') {
                continue;
            }

            if (!empty($row['capa_json'])) {
                $decoded = json_decode($row['capa_json'], true);

                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $findingKey = strtolower(trim($row['finding'] ?? ''));

                    if (!in_array($findingKey, ['yes', 'no', 'na'])) {
                        $findingKey = 'no';
                    }

                    $rawColorCode = strtoupper(trim($decoded['color_code'][$findingKey] ?? ''));

                    // Map variations to standardized colors
                    $colorMap = [
                        'RED' => 'Red',
                        'YELLOW' => 'Yellow',
                        'BLACK' => 'Black'
                    ];

                    // Only process matched, valid colors. Ignore Unknown/Empty or other colors.
                    if (isset($colorMap[$rawColorCode])) {
                        $stdColor = $colorMap[$rawColorCode];

                        if (!isset($colorCodeCounts[$stdColor])) {
                            $colorCodeCounts[$stdColor] = 0;
                        }

                        $colorCodeCounts[$stdColor]++;
                    }
                }
            }
        }

        // Convert to dashboard array
        $data['color_code_open_points'] = [];
        foreach ($colorCodeCounts as $code => $count) {
            $data['color_code_open_points'][] = [
                'color_code' => $code,
                'total_count' => $count
            ];
        }
        // 5. Aging Report (Audit Category based, Original Audit Date logic)
        $colAuditOpen = [];
        foreach ($auditCategories as $cat) {
            $key = $this->formatKey($cat['audit_category']);
            $colAuditOpen[] = "SUM(CASE WHEN TRIM(LOWER(d.audit_category)) = TRIM(LOWER(" . $db->escape($cat['audit_category']) . ")) AND d.nc_status != 3 THEN 1 ELSE 0 END) AS {$key}_audit_open";
        }
        $colAuditOpenSQL = implode(", ", $colAuditOpen);

        // Fetching aging based on the EARLIEST audit date where the NC was found but still not closed
        $data['aging_report'] = $db->query("
            SELECT 
                m.client_name as location, m.audit_no, m.audit_name, m.region, m.cluster_name, 
                MAX(m.audit_date) as latest_audit_date,
                MIN(m.audit_date) as original_audit_date,
                DATEDIFF(CURDATE(), MIN(m.audit_date)) as aging_days,
                {$colAuditOpenSQL}
            FROM alert_hse_audit_master m
            INNER JOIN alert_hse_audit_details d ON m.hse_audit_id = d.hse_audit_id
            {$where}
            AND d.finding = 'NO' AND d.nc_status != 3
            AND d.hse_audit_id IN (
                SELECT MAX(hse_audit_id) FROM alert_hse_audit_master WHERE status != 2 GROUP BY audit_no
            )
            GROUP BY m.client_name
            ORDER BY aging_days DESC
        ")->getResultArray();

        // 6. Upcoming Audit (Latest Entry Only per location)
        $data['upcoming_audits'] = $db->query("
            SELECT m.client_name as location, m.audit_no, m.audit_date as last_audit_date, 
                   m.report_date as next_audit_date, m.region, m.cluster_name, m.auditor_name, 
                   m.account_manager, m.status
            FROM alert_hse_audit_master m
            INNER JOIN (
                SELECT client_name, MAX(hse_audit_id) as latest_id
                FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name
            ) latest ON m.hse_audit_id = latest.latest_id
            {$where}
            AND m.report_date >= CURDATE()
            ORDER BY m.report_date ASC
        ")->getResultArray();

        // 7. NC vs Recommendation
        $data['nc_rec_chart'] = $db->query("
            SELECT 
                SUM(CASE WHEN LOWER(d.nc_type) = 'nc' THEN 1 ELSE 0 END) AS nc_count,
                SUM(CASE WHEN LOWER(d.nc_type) IN ('rd','recommendation') THEN 1 ELSE 0 END) AS rd_count
            FROM alert_hse_audit_details d
            INNER JOIN alert_hse_audit_master m ON m.hse_audit_id = d.hse_audit_id
            {$where}
            AND d.finding = 'NO'
            AND d.hse_audit_id IN (
                SELECT MAX(hse_audit_id) FROM alert_hse_audit_master WHERE status != 2 GROUP BY audit_no
            )
        ")->getRowArray();

        // Overall Score (using latest entries)
        $data['overall_hse_score'] = 0;
        if (!empty($data['pie_chart'])) {
            $data['overall_hse_score'] = round(array_sum(array_column($data['pie_chart'], 'score')) / count($data['pie_chart']), 2);
        }

        // Add overall NC status totals for summary cards
        $data['overall_open_nc'] = array_sum(array_column($data['total_HSE_score_openpoints'], 'open_pts'));
        $data['overall_working_nc'] = array_sum(array_column($data['total_HSE_score_openpoints'], 'working_pts'));
        $data['overall_review_nc'] = array_sum(array_column($data['total_HSE_score_openpoints'], 'review_cm')) + array_sum(array_column($data['total_HSE_score_openpoints'], 'review_auditor'));
        $data['overall_closed_nc'] = array_sum(array_column($data['total_HSE_score_openpoints'], 'closed_pts'));
        $data['total_audits_count'] = array_sum(array_column($data['pie_chart'], 'total_audits'));

        // Total open points for summary card (open + working + review)
        $data['total_open_points'] = $data['overall_open_nc'] + $data['overall_working_nc'] + $data['overall_review_nc'];

        // Critical findings = Red color-coded open points
        $data['critical_findings_count'] = 0;
        foreach ($data['color_code_open_points'] as $cp) {
            if (strtolower($cp['color_code']) === 'red') {
                $data['critical_findings_count'] = (int) $cp['total_count'];
                break;
            }
        }

        // Aging > 30 days open points count
        $data['aging_over_30_count'] = 0;
        foreach ($data['aging_report'] as $ar) {
            if ((int) $ar['aging_days'] > 30) {
                $data['aging_over_30_count']++;
            }
        }

        // Top 10 locations for Score chart (sorted by score desc)
        $scoreByLocation = $db->query("
            SELECT m.client_name as location, m.region, ROUND(AVG(m.score), 2) AS score
            FROM alert_hse_audit_master m
            INNER JOIN (
                SELECT client_name, MAX(hse_audit_id) as latest_id
                FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name
            ) latest ON m.hse_audit_id = latest.latest_id
            {$where}
            AND m.client_name IS NOT NULL AND m.client_name != ''
            GROUP BY m.client_name, m.region
            ORDER BY score DESC
            LIMIT 10
        ")->getResultArray();
        $data['score_by_location'] = $scoreByLocation;
    }

    public function get_clusters_by_region()
    {
        $db = db_connect();
        $region = array_filter((array)$this->request->getVar('region_name'));
        $builder = $db->table('alert_hse_client_master')->select('DISTINCT(cluster) as cluster_name')->where('status', 1);
        if (!empty($region))
            $builder->whereIn('region', $region);

        helper('designation_acl');
        if (isClusterManager()) {
            $clusters = getClusterManagerAssignedClusterHSE();
            if (!empty($clusters))
                $builder->whereIn('cluster', $clusters);
        }

        return $this->response->setJSON(['clusters' => $builder->orderBy('cluster', 'ASC')->get()->getResultArray()]);
    }

    public function get_locations_by_cluster()
    {
        $db = db_connect();
        $cluster = array_filter((array)$this->request->getVar('cluster_name'));
        $region = array_filter((array)$this->request->getVar('region_name'));
        $builder = $db->table('alert_hse_client_master')->select('DISTINCT(client_name) as location_name')->where('status', 1);
        if (!empty($cluster))
            $builder->whereIn('cluster', $cluster);
        if (!empty($region))
            $builder->whereIn('region', $region);

        helper('designation_acl');
        if (isAccountManager()) {
            $clients = getAccountManagerAssignedClientsHSE();
            if (!empty($clients))
                $builder->whereIn('client_name', $clients);
        }

        return $this->response->setJSON(['locations' => $builder->orderBy('client_name', 'ASC')->get()->getResultArray()]);
    }

    private function getRegionColorMap(array $regions)
    {
        $palette = ['#D2691E', '#808080', '#FFCE56', '#FF6384', '#36A2EB', '#4BC0C0', '#9966FF', '#FF9F40', '#C9CBCF', '#7BC225', '#E7544B', '#8B4513'];
        $colorMap = [];
        foreach ($regions as $i => $region) {
            $color = $palette[$i % count($palette)];
            $colorMap[$region] = $color;
            $colorMap[strtoupper($region)] = $color;
            $colorMap[ucfirst(strtolower($region))] = $color;
        }
        return $colorMap;
    }

    private function getDynamicRegions($db)
    {
        helper('designation_acl');
        $builder = $db->table('alert_hse_client_master')->select('DISTINCT(region) as region')->where('status', 1)->where('region IS NOT NULL')->where('region !=', '');
        if (isClusterManager()) {
            $clusters = getClusterManagerAssignedClusterHSE();
            if (!empty($clusters))
                $builder->whereIn('cluster', $clusters);
        }
        return array_column($builder->orderBy('region', 'ASC')->get()->getResultArray(), 'region');
    }

    // ========================================================================
    // EXPORT HANDLERS (EXCEL DATA EXPORT)
    // ========================================================================

    private function applyFiltersExport($builder, $request)
    {
        $region = array_filter((array)$request->getVar('region'));
        $cluster = array_filter((array)$request->getVar('cluster')); // sometimes passed as array from some frontend, but HSE uses 'cluster_name'
        if (empty($cluster))
            $cluster = array_filter((array)$request->getVar('cluster_name'));
        $location = array_filter((array)$request->getVar('location'));
        if (empty($location))
            $location = array_filter((array)$request->getVar('location_name'));
        $month = array_filter((array)$request->getVar('month'));

        // Normalize month values
        $monthMap = [
            'january' => 1, 'february' => 2, 'march' => 3,
            'april' => 4, 'may' => 5, 'june' => 6,
            'july' => 7, 'august' => 8, 'september' => 9,
            'october' => 10, 'november' => 11, 'december' => 12
        ];
        $selectedMonthNums = [];
        foreach ($month as $m) {
            $m = (string)$m;
            if (ctype_digit($m)) {
                $selectedMonthNums[] = (int)$m;
            } else {
                $key = strtolower(trim($m));
                if (isset($monthMap[$key])) {
                    $selectedMonthNums[] = $monthMap[$key];
                }
            }
        }
        $selectedMonthNums = array_unique($selectedMonthNums);

        $aclFilter = getClusterFilterByClientForHSE('m.client_name');
        $builder->where('m.status !=', 2);
        if ($aclFilter) {
            $builder->where("1=1 {$aclFilter}", null, false);
        }

        if (!empty($region)) {
            $builder->whereIn('m.region', $region);
        }
        if (!empty($cluster)) {
            $builder->whereIn('m.cluster_name', $cluster);
        }
        if (!empty($location)) {
            $builder->whereIn('m.client_name', $location);
        }
        if (!empty($selectedMonthNums)) {
            $builder->whereIn('MONTH(m.audit_date)', $selectedMonthNums);
        }
    }

    private function getDetailedExportQueryBuilder()
    {
        $db = db_connect();
        $builder = $db->table('alert_hse_audit_master m');
        // We only want the latest audit per client
        $builder->join('(SELECT client_name, MAX(hse_audit_id) as latest_id FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name) latest', 'm.hse_audit_id = latest.latest_id', 'inner');
        $builder->join('alert_hse_audit_details d', 'm.hse_audit_id = d.hse_audit_id', 'left');

        $builder->select('
            m.audit_no, m.region, m.cluster_name, m.client_name, m.perform_audit_by, m.audit_date,
            d.site_category, d.audit_category, d.audit_question, d.finding, d.nc_status,
            d.nc_remark, d.nc_type
        ');
        return $builder;
    }

    private function exportDetailedExcel($builder, $filename)
    {
        $records = $builder->get()->getResultArray();

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
        header('Cache-Control: max-age=0');

        $fp = fopen('php://output', 'w');
        if (!$fp) {
            exit('Unable to open output stream');
        }

        // UTF-8 BOM
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = [
            'Audit No', 'Region', 'Cluster', 'Location', 'Auditor', 'Audit Date',
            'Site Category', 'Audit Category', 'Question', 'Finding', 'NC Type', 'Status', 'Remarks'
        ];

        fputcsv($fp, $headers);

        $statusMap = [
            0 => 'Open',
            1 => 'Working',
            2 => 'Review (Auditor)',
            3 => 'Closed',
            5 => 'Review (CM)'
        ];

        foreach ($records as $row) {
            $statusText = isset($statusMap[$row['nc_status']]) ? $statusMap[$row['nc_status']] : 'N/A';
            if ($row['finding'] === 'YES' || $row['finding'] === 'NA') {
                $statusText = '-';
            }

            $row_data = [
                $row['audit_no'] ?? '',
                $row['region'] ?? '',
                $row['cluster_name'] ?? '',
                $row['client_name'] ?? '',
                $row['perform_audit_by'] ?? '',
                $row['audit_date'] ?? '',
                $row['site_category'] ?? '',
                $row['audit_category'] ?? '',
                $row['audit_question'] ?? '',
                $row['finding'] ?? '',
                $row['nc_type'] ?? '',
                $statusText,
                $row['nc_remark'] ?? ''
            ];

            foreach ($row_data as $idx => $val) {
                if (!isset($val) || trim((string) $val) === '') {
                    $row_data[$idx] = 'NA';
                } else {
                    $cleaned = str_replace(["\r\n", "\r", "\n"], " ", (string) $val);
                    $row_data[$idx] = preg_replace('/\s+/', ' ', trim($cleaned));
                }
            }

            fputcsv($fp, $row_data);
        }

        fclose($fp);
        exit;
    }

    public function exportRegionWiseHseChart()
    {
        $builder = $this->getDetailedExportQueryBuilder();
        $this->applyFiltersExport($builder, $this->request);
        $this->exportDetailedExcel($builder, 'Region_Wise_HSE_Data');
    }

    public function exportOpenPointsHseChart()
    {
        $builder = $this->getDetailedExportQueryBuilder();
        $this->applyFiltersExport($builder, $this->request);
        $builder->where('d.finding', 'NO');
        $builder->where('d.nc_status !=', 3);
        $this->exportDetailedExcel($builder, 'Open_Points_HSE_Data');
    }

    public function exportNcRecommendationHseChart()
    {
        $builder = $this->getDetailedExportQueryBuilder();
        $this->applyFiltersExport($builder, $this->request);
        $builder->where('d.finding', 'NO');
        $builder->groupStart()
            ->where('LOWER(d.nc_type)', 'nc')
            ->orWhere('LOWER(d.nc_type)', 'rd')
            ->orWhere('LOWER(d.nc_type)', 'recommendation')
            ->groupEnd();
        $this->exportDetailedExcel($builder, 'NC_Recommendation_HSE_Data');
    }

    public function exportTrendHseChart()
    {
        $builder = $this->getDetailedExportQueryBuilder();
        $this->applyFiltersExport($builder, $this->request);
        $builder->where('m.audit_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)');
        $this->exportDetailedExcel($builder, 'Trend_HSE_Data');
    }

    public function exportUpcomingAuditsHseChart()
    {
        $db = db_connect();
        $builder = $db->table('alert_hse_audit_master m');
        
        $this->applyFiltersExport($builder, $this->request);
        
        // We only want the latest audit per client
        $builder->join('(SELECT client_name, MAX(hse_audit_id) as latest_id FROM alert_hse_audit_master WHERE status != 2 GROUP BY client_name) latest', 'm.hse_audit_id = latest.latest_id', 'inner');
        
        $builder->select('
            m.client_name as location, m.audit_no, m.audit_date as last_audit_date, 
            m.report_date as next_audit_date, m.region, m.cluster_name, m.auditor_name, 
            m.account_manager, m.status
        ');
        
        $builder->where('m.report_date >=', date('Y-m-d'));
        $builder->orderBy('m.report_date', 'ASC');
        
        $results = $builder->get()->getResultArray();

        $filename = 'Upcoming_Audits_HSE_Data';

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="' . $filename . '.csv"');
        header('Cache-Control: max-age=0');

        $fp = fopen('php://output', 'w');
        if (!$fp) {
            exit('Unable to open output stream');
        }

        // UTF-8 BOM
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $headers = ['Region', 'Cluster', 'Location', 'Audit No', 'Auditor', 'Account Manager', 'Last Audit Date', 'Next Audit Date'];
        fputcsv($fp, $headers);

        foreach ($results as $row) {
            fputcsv($fp, [
                $row['region'] ?? '',
                $row['cluster_name'] ?? '',
                $row['location'] ?? '',
                $row['audit_no'] ?? '',
                $row['auditor_name'] ?? '',
                $row['account_manager'] ?? '',
                $row['last_audit_date'] ?? '',
                $row['next_audit_date'] ?? ''
            ]);
        }

        fclose($fp);
        exit;
    }
}
