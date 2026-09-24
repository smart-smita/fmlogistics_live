<?php

namespace App\Controllers\Customer;

use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;
use App\Traits\ACLTrait;

class Audit_dashboard extends BaseController
{
    use ACLTrait;

    public $BaseModel = null;

    public function __construct()
    {
        helper('designation_acl');
    }

    /* ========================= NORMAL AUDIT DASHBOARD ========================= */

    public function Normal_Audit()
    {
        $db = db_connect();

        $data = $this->addACLToViewData([]);

        // Dynamic regions from alert_client
        $data['dynamic_regions'] = $this->getDynamicRegions($db);
        $data['region_color_map'] = $this->getRegionColorMap($data['dynamic_regions']);

        // Country DDL
        $data['country'] = $db->table("alert_country")->get()->getResultArray();

        // Role-based Dropdown Locking & Filtering
        if (isClusterManager()) {
            $assignedClusters = getClusterManagerAssignedCluster();
            $assignedRegions = getClusterManagerAssignedRegion();

            $data['locked_cluster'] = $assignedClusters;
            $data['locked_region'] = $assignedRegions;
            $data['is_cluster_manager'] = true;
            $data['is_account_manager'] = false;

            $locationQuery = $db->table("alert_location_master");
            if (!empty($assignedClusters)) {
                $locationQuery->whereIn('cluster_name', $assignedClusters);
            }
            if (!empty($assignedRegions)) {
                $locationQuery->whereIn('region_name', $assignedRegions);
            }

            $data['location'] = $locationQuery->groupBy("location_name")
                ->get()->getResultArray();

            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c]; }, $assignedClusters);
            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r]; }, $assignedRegions);

            // For CM, cluster/location are not locked
            $data['lock_cluster'] = false;
            $data['lock_location'] = false;
        } elseif (isAccountManager() || isWHManager()) {
            $assignedRegions = getAccountManagerAssignedRegion();
            $assignedClusters = getAccountManagerAssignedCluster();
            $assignedClients = getAccountManagerAssignedClient();

            $data['locked_region'] = $assignedRegions;
            $data['locked_cluster'] = $assignedClusters;
            $data['locked_location'] = $assignedClients;
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = true;

            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r]; }, (array) $assignedRegions);
            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c]; }, (array) $assignedClusters);
            $data['location'] = array_map(function ($l) {
                return ['location_name' => $l]; }, (array) $assignedClients);

            $data['lock_cluster'] = false;
            $data['lock_location'] = false;
        } else {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = false;

            // Region list from alert_client (dynamic)
            $data['region'] = $db->table('alert_client')
                ->select('DISTINCT(region) AS region_name', false)
                ->where('region IS NOT NULL')->where('status', 1)
                ->where('region !=', '')
                ->where('status', 1)
                ->orderBy('region', 'ASC')
                ->get()
                ->getResultArray();

            // For initial load, cluster & location are locked (empty)
            $data['cluster'] = [];
            $data['location'] = [];

            $data['lock_cluster'] = true;   // no region selected yet
            $data['lock_location'] = true;   // no cluster selected yet
        }

        // Audit Types
        $data['audit_types'] = $db->table("alert_audit_template")
            ->select("audit_name")
            ->where("audit_template_type", "Normal")
            ->where("status", 1)
            ->get()->getResultArray();

        // Month DDL
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

        // FY DDL
        $currentYear = date('Y');
        $financialYears = [];
        for ($i = 0; $i < 5; $i++) {
            $startYear = ($currentYear - $i) - 1;
            $endYear = $currentYear - $i;
            $financialYears[] = "$startYear-$endYear";
        }
        $data['financialYears'] = $financialYears;

        /* ---------- Normal Pie Chart (ACL-based) ---------- */
        $oeAclFilterNormal = getOEAuditACLWhere('audit', 'Normal');
        $pieChartSQL = "SELECT 
            audit.region, 
            ROUND(IFNULL((SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) 
                  / NULLIF(SUM(weightage), 0)) * 100, 0), 2) AS score,
            COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS openpoints 
        FROM alert_normal_audit audit 
        LEFT JOIN alert_normal_audit_details audit_details 
            ON audit.normal_audit_id = audit_details.normal_audit_id 
        WHERE audit.region IS NOT NULL 
          AND audit.region <> '' {$oeAclFilterNormal}";
        $pieChartSQL .= " GROUP BY audit.region";
        $data['pie_chart'] = $db->query($pieChartSQL)->getResultArray();

        /* ---------- Normal Aging ---------- */
        $agingSQL = "SELECT audit.normal_audit_id, audit_no, audit_name, region, category, 
                            COUNT(audit_finding) AS openpoints 
                     FROM alert_normal_audit audit 
                     LEFT JOIN alert_normal_audit_details audit_details 
                        ON audit.normal_audit_id = audit_details.normal_audit_id 
                        AND audit_finding = 'NO' 
                     WHERE 1=1 {$oeAclFilterNormal}";
        $agingSQL .= " GROUP BY audit.normal_audit_id, category";
        $data['aging_normal_score'] = $db->query($agingSQL)->getResultArray();

        /* ---------- Normal Open Point Report (ACL) open_point_report // change at 26-02-2026
        $openPointSQL = "SELECT audit.*, audit.normal_audit_id, audit.location, audit.region,
                                loc.cluster_name, category, audit_parameter, audit_remark
        FROM alert_normal_audit audit
        LEFT JOIN alert_normal_audit_details audit_details
            ON audit.normal_audit_id = audit_details.normal_audit_id
        LEFT JOIN alert_location_master AS loc 
            ON audit.location = loc.location_name
        WHERE 1=1";
        $openPointSQL .= getClusterFilterByLocation('audit.location');
        $data['open_point_report'] = $db->query($openPointSQL)->getResultArray();
       ---------- */
        /* ---------- Normal Month Summary (LATEST audit per location+month+year) ---------- */
        $aclFilter = getOEAuditACLWhere('audit', 'Normal');

        $normal_year_score_query = $db->query("
            SELECT 
                b.location,
                b.region,
                b.cluster_name,

                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(d.weightage), 0)) * 100,
                    0), 2
                ) AS avg_score_percentage,

                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 4 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 4 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Apr_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 5 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 5 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS May_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 6 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 6 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Jun_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 7 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 7 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Jul_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 8 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 8 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Aug_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 9 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 9 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Sep_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 10 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 10 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Oct_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 11 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 11 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Nov_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 12 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 12 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Dec_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 1 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 1 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Jan_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 2 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 2 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Feb_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 3 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 3 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Mar_Score

            FROM (
                SELECT 
                    audit.normal_audit_id,
                    audit.location,
                    audit.region,
                    loc.cluster_name,
                    MONTH(audit.audit_date) AS audit_month,
                    YEAR(audit.audit_date)  AS audit_year,
                    audit.audit_date
                FROM alert_normal_audit AS audit
                LEFT JOIN alert_location_master AS loc 
                    ON audit.location = loc.location_name
                WHERE 1=1 {$aclFilter}
            ) b
            JOIN (
                SELECT 
                    audit.location,
                    audit.region,
                    loc.cluster_name,
                    MONTH(audit.audit_date) AS audit_month,
                    YEAR(audit.audit_date)  AS audit_year,
                    MAX(audit.audit_date)   AS max_audit_date
                FROM alert_normal_audit AS audit
                LEFT JOIN alert_location_master AS loc 
                    ON audit.location = loc.location_name
                WHERE 1=1 {$aclFilter}
                GROUP BY audit.location, audit.region, loc.cluster_name,
                         YEAR(audit.audit_date), MONTH(audit.audit_date)
            ) latest
                ON b.location      = latest.location
               AND b.region        = latest.region
               AND b.cluster_name  = latest.cluster_name
               AND b.audit_year    = latest.audit_year
               AND b.audit_month   = latest.audit_month
               AND b.audit_date    = latest.max_audit_date
            LEFT JOIN alert_normal_audit_details d
                ON b.normal_audit_id = d.normal_audit_id
            GROUP BY b.location, b.region, b.cluster_name
        ")->getResultArray();

        $data['normal_year_score_query'] = $normal_year_score_query;

        /* ---------- Normal Openpoint Summary (location-wise) ---------- */
        $oeAclFilterSummary = getOEAuditACLWhere('audit', 'Normal');
        $total_normal_score_openpoints = $db->query("
            SELECT 
                MAX(audit.audit_no) AS audit_no,
                MAX(audit.audit_date) AS audit_date,
                audit.location, 
                audit.region,
                loc.cluster_name,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) /
                         NULLIF(SUM(weightage), 0)) * 100,
                    0), 2) AS avg_score_percentage,
                COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
            FROM alert_normal_audit audit
            LEFT JOIN alert_normal_audit_details audit_details 
                ON audit.normal_audit_id = audit_details.normal_audit_id
            LEFT JOIN alert_location_master AS loc 
                ON audit.location = loc.location_name 
            WHERE 1=1 {$oeAclFilterSummary}
            GROUP BY audit.location
        ")->getResultArray();

        $data['total_Normal_score_openpoints'] = $total_normal_score_openpoints;

        // Upcoming Normal audits (ACL via location)
        $aclFilterUpcoming = getOEAuditACLWhere('alert_normal_audit', 'Normal');
        $data['Normal_audit'] = $db->query("
            SELECT * FROM alert_normal_audit 
            WHERE next_date >= CURDATE() {$aclFilterUpcoming}
        ")->getResultArray();

        // Category list
        $data['category_normal'] = $db->table("alert_normal_audit_excel_import")
            ->select("category")
            ->groupby("category")
            ->get()->getResultArray();

        // Selected defaults
        if (isClusterManager()) {
            $data['selected_region'] = getClusterManagerAssignedRegion();
            $data['selected_cluster'] = getClusterManagerAssignedCluster();
            $data['selected_location'] = '';
        } else {
            $data['selected_region'] = '';
            $data['selected_cluster'] = '';
            $data['selected_location'] = '';
        }

        return view("Customer/normal_dashboard", $data);
    }



    /* ========================= OE AUDIT DASHBOARD ========================= */

    public function OE_Audit()
    {
        $db = db_connect();
        $postData = $this->request->getVar();

        // Basic filters from POST — all fields are now multi-select arrays (name="field[]")
        $selected_region = array_filter((array) ($postData['region'] ?? []));
        $cluster_name    = array_filter((array) ($postData['cluster_name'] ?? []));
        $location_name   = array_filter((array) ($postData['location_name'] ?? []));
        $selected_month  = array_filter((array) ($postData['month'] ?? []));
        $selected_year   = array_filter((array) ($postData['filter_year'] ?? []));
        $selected_category = array_filter((array) ($postData['category'] ?? []));

        // Handle comma-separated strings from ACL-locked hidden inputs
        foreach (['selected_region', 'cluster_name', 'location_name'] as $var) {
            $flat = [];
            array_walk_recursive($$var, function($a) use (&$flat) { $flat[] = $a; });
            $expanded = [];
            foreach ($flat as $item) {
                if (is_string($item) && strpos($item, ',') !== false) {
                    $expanded = array_merge($expanded, explode(',', $item));
                } else {
                    $expanded[] = $item;
                }
            }
            $$var = array_unique(array_filter($expanded));
        }

        // Parse year filter — support multi-select
        $selectedYearNums = [];
        foreach ($selected_year as $year) {
            $year = (string)$year;
            if (ctype_digit($year)) {
                $selectedYearNums[] = (int)$year;
            }
        }
        $selectedYearNums = array_unique($selectedYearNums);
        // Keep backward-compat: use first year num if needed
        $selectedYearNum = !empty($selectedYearNums) ? $selectedYearNums[0] : null;

        // Normalize month (support '04' or 'April') — collect all selected months
        $selectedMonthNums = [];
        foreach ($selected_month as $sm) {
            $sm = (string)$sm;
            if (ctype_digit($sm)) {
                $selectedMonthNums[] = (int)$sm;
            } else {
                $monthMap = [
                    'january' => 1, 'february' => 2, 'march' => 3,
                    'april' => 4, 'may' => 5, 'june' => 6,
                    'july' => 7, 'august' => 8, 'september' => 9,
                    'october' => 10, 'november' => 11, 'december' => 12
                ];
                $key = strtolower(trim($sm));
                if (isset($monthMap[$key])) {
                    $selectedMonthNums[] = $monthMap[$key];
                }
            }
        }
        $selectedMonthNums = array_unique($selectedMonthNums);
        // Keep backward-compat: use first month num for single-month queries
        $selectedMonthNum = !empty($selectedMonthNums) ? $selectedMonthNums[0] : null;

        // Prepare data with ACL
        $data = $this->addACLToViewData([]);
        $data['selected_month'] = $selected_month;
        $data['selectedMonthNums'] = $selectedMonthNums;
        $data['selected_year']  = $selected_year;
        $data['selected_category'] = $selected_category;

        // Fetch Audit Categories (dynamically from details)
        $data['audit_categories'] = $db->table('alert_final_structured_audit_details')
            ->select('DISTINCT(category) AS category', false)
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->orderBy('category', 'ASC')
            ->get()->getResultArray();

        // Dynamic regions from alert_client
        $data['dynamic_regions'] = $this->getDynamicRegions($db);
        $data['region_color_map'] = $this->getRegionColorMap($data['dynamic_regions']);

        $data['country'] = $db->table("alert_country")->get()->getResultArray();

        /* -------- Role-based Dropdown Locking & Filtering -------- */
        if (isClusterManager() || isAccountManager() || isWHManager()) {
            $assignedRegions = getAccountManagerAssignedRegion();
            $assignedClusters = getAccountManagerAssignedCluster();
            $assignedClients = getUserAllocatedSiteNames('OE');

            if (empty($location_name))
                $location_name = $assignedClients;


            $data['locked_region'] = $assignedRegions;
            $data['locked_cluster'] = $assignedClusters;
            $data['locked_location'] = $assignedClients;
            $data['is_cluster_manager'] = isClusterManager();
            $data['is_account_manager'] = isAccountManager() || isWHManager();

            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r]; }, (array) $assignedRegions);
            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c]; }, (array) $assignedClusters);
            $data['location'] = array_map(function ($l) {
                return ['location_name' => $l]; }, (array) $assignedClients);

            $data['lock_cluster'] = false;
            $data['lock_location'] = false;
        } else {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = false;

            // Region list from alert_client (dynamic)
            $data['region'] = $db->table('alert_client')
                ->select('DISTINCT(region) AS region_name', false)
                ->where('region IS NOT NULL')->where('status', 1)
                ->where('region !=', '')
                ->where('status', 1)
                ->orderBy('region', 'ASC')
                ->get()
                ->getResultArray();

            // Default locks
            $data['cluster']  = [];
            $data['location'] = [];

            // If region(s) selected, load clusters and locations
            if (!empty($selected_region)) {
                $clusterQ = $db->table('alert_client')
                    ->select('DISTINCT(cluster) AS cluster_name', false)
                    ->whereIn('region', (array) $selected_region)
                    ->where('status', 1)
                    ->orderBy('cluster', 'ASC')
                    ->get()->getResultArray();
                $data['cluster'] = $clusterQ;
                $data['lock_cluster'] = false;

                // If cluster(s) selected, load locations (depends on region and cluster)
                if (!empty($cluster_name)) {
                    $locationQ = $db->table('alert_client')
                        ->select('DISTINCT(client_name) AS location_name', false)
                        ->whereIn('region', (array) $selected_region)
                        ->whereIn('cluster', (array) $cluster_name)
                        ->where('status', 1)
                        ->orderBy('client_name', 'ASC')
                        ->get()->getResultArray();
                    $data['location'] = $locationQ;
                    $data['lock_location'] = false;
                } else {
                    $data['lock_location'] = true;
                }
            } else if (!empty($selected_year)) {
                // If year selected and region not, load all relevant locations (based on year's ACL)
                $locationQ = $db->table('alert_client')
                    ->select('DISTINCT(client_name) AS location_name', false)
                    ->where('status', 1)
                    ->orderBy('client_name', 'ASC')
                    ->get()->getResultArray();
                $data['location'] = $locationQ;
                $data['lock_cluster'] = true; // hide cluster
                $data['lock_location'] = false;
            } else {
                $data['lock_cluster']  = true;
                $data['lock_location'] = true;
            }
        }

        $data['selected_region'] = $selected_region;
        $data['selected_cluster'] = $cluster_name;
        $data['selected_location'] = $location_name;

        $data['audit_template'] = $db->table("alert_audit_template")->get()->getResultArray();

        /* -------- Month & FY Lists -------- */
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

        $currentYear = date('Y');
        $financialYears = [];
        for ($i = 0; $i < 5; $i++) {
            $startYear = ($currentYear - $i) - 1;
            $endYear = $currentYear - $i;
            $financialYears[] = "$startYear-$endYear";
        }
        $data['financialYears'] = $financialYears;

        $currentMonth = date('m');
        if ($currentMonth >= 4) {
            $data['audit_year'] = $currentYear . '-' . ($currentYear + 1);
        } else {
            $data['audit_year'] = ($currentYear - 1) . '-' . $currentYear;
        }

        /* ==========================================
         * 1) OE PIE / DONUT CHART  (Dynamic grouping by deepest filter)
         *    - No filter          → Group by Region   (Top 10)
         *    - Region selected    → Group by Region (for selected regions)
         *    - Cluster selected   → Group by Cluster (for selected clusters)
         *    - Account Name selected → Group by Account Name (Location/Client)
         * ========================================== */
        $oeAclFilter = getOEAuditACLWhere('audit', 'OE');
        $oeAclFilterSub_a = getOEAuditACLWhere('sub_a', 'OE');

        $latestAuditSubquery_audit = "";
        $latestAuditSubquery_a = "";
        
        $reauditCondition_audit = "";
        $reauditCondition_a     = "";

        $subConditionCore = "
            SELECT MAX(sub_a.structured_audit_id)
            FROM alert_final_structured_audit sub_a
            WHERE sub_a.region IS NOT NULL AND sub_a.region <> '' {$oeAclFilterSub_a}
        ";
        $subConditionCore .= $this->buildSqlCondition("sub_a.region", $selected_region);
        $subConditionCore .= $this->buildSqlCondition("sub_a.cluster_name", $cluster_name);
        $subConditionCore .= $this->buildSqlCondition("sub_a.client_name", $location_name);
        $subConditionCore .= $this->buildSqlCondition("YEAR(sub_a.audit_date)", $selectedYearNums);
        $subConditionCore .= $this->buildSqlCondition("MONTH(sub_a.audit_date)", $selectedMonthNums);
        $subConditionCore .= " GROUP BY sub_a.client_name";

        $latestAuditSubquery_audit = " AND audit.structured_audit_id IN ({$subConditionCore}) ";
        $latestAuditSubquery_a     = " AND a.structured_audit_id IN ({$subConditionCore}) ";

        // Determine group-by column and chart title based on user's request
        if (!empty($location_name)) {
            $pieGroupCol   = 'audit.client_name';
            $pieGroupAlias = 'audit.client_name AS label';
            $pieChartTitle = 'OE Score by Account Name';
        } elseif (!empty($cluster_name)) {
            $pieGroupCol   = 'audit.client_name';
            $pieGroupAlias = 'audit.client_name AS label';
            $pieChartTitle = 'OE Score by Account Name (Cluster Filtered)';
        } elseif (!empty($selected_region)) {
            $pieGroupCol   = 'audit.cluster_name';
            $pieGroupAlias = 'audit.cluster_name AS label';
            $pieChartTitle = 'OE Score by Cluster (Region Filtered)';
        } else {
            $pieGroupCol   = 'audit.region';
            $pieGroupAlias = 'audit.region AS label';
            $pieChartTitle = 'OE Score by Region (Top 10)';
        }
        $data['pie_chart_title'] = $pieChartTitle;

        $pieChartSQL = "
            SELECT 
                label,
                ROUND(AVG(final_scores.score), 2) AS score,
                ROUND(AVG(final_scores.openpoints), 2) AS openpoints
            FROM (
                SELECT 
                    {$pieGroupAlias},
                    t.audit_score AS score,
                    t.openpoints
                FROM (
                    SELECT 
                        a.structured_audit_id,
                        a.client_name,
                        ROUND((SUM(CASE WHEN d.audit_finding IN ('YES','NA') THEN d.weightage ELSE 0 END) / NULLIF(SUM(d.weightage), 0)) * 100, 2) AS audit_score,
                        ROUND((COUNT(CASE WHEN d.audit_finding = 'NO' THEN 1 ELSE NULL END) * 100) / NULLIF(COUNT(d.audit_finding), 0), 2) AS openpoints
                    FROM alert_final_structured_audit a
                    INNER JOIN alert_final_structured_audit_details d ON d.structured_audit_id = a.structured_audit_id
                    WHERE 1=1 " . $this->buildSqlCondition('d.category', $selected_category) . "
                    GROUP BY a.structured_audit_id
                ) t
                INNER JOIN alert_final_structured_audit audit ON audit.structured_audit_id = t.structured_audit_id
                WHERE audit.region IS NOT NULL AND audit.region <> '' {$oeAclFilter}
        ";

        $pieChartSQL .= $this->buildSqlCondition("audit.region", $selected_region);
        $pieChartSQL .= $this->buildSqlCondition("audit.cluster_name", $cluster_name);
        $pieChartSQL .= $this->buildSqlCondition("audit.client_name", $location_name);
        $pieChartSQL .= $this->buildSqlCondition("YEAR(audit.audit_date)", $selectedYearNums);
        $pieChartSQL .= $this->buildSqlCondition("MONTH(audit.audit_date)", $selectedMonthNums);
        $pieChartSQL .= $latestAuditSubquery_audit;

        $pieChartSQL .= " GROUP BY t.structured_audit_id, {$pieGroupCol} ) final_scores GROUP BY label ORDER BY score DESC LIMIT 10";
        $data['pie_chart'] = $db->query($pieChartSQL)->getResultArray();

        // Overall Score (exact same logic, no GROUP BY slice)
        $overallScoreSQL = "
            SELECT ROUND(AVG(final_scores.audit_score), 2) AS overall_score
            FROM (
                SELECT t.audit_score
                FROM (
                    SELECT 
                        a.structured_audit_id,
                        ROUND((SUM(CASE WHEN d.audit_finding IN ('YES','NA') THEN d.weightage ELSE 0 END) / NULLIF(SUM(d.weightage), 0)) * 100, 2) AS audit_score
                    FROM alert_final_structured_audit a
                    INNER JOIN alert_final_structured_audit_details d ON d.structured_audit_id = a.structured_audit_id
                    WHERE 1=1 " . $this->buildSqlCondition('d.category', $selected_category) . "
                    GROUP BY a.structured_audit_id
                ) t
                INNER JOIN alert_final_structured_audit audit ON audit.structured_audit_id = t.structured_audit_id
                WHERE audit.region IS NOT NULL AND audit.region <> '' {$oeAclFilter}
        ";
        $overallScoreSQL .= $this->buildSqlCondition("audit.region", $selected_region);
        $overallScoreSQL .= $this->buildSqlCondition("audit.cluster_name", $cluster_name);
        $overallScoreSQL .= $this->buildSqlCondition("audit.client_name", $location_name);
        $overallScoreSQL .= $this->buildSqlCondition("YEAR(audit.audit_date)", $selectedYearNums);
        $overallScoreSQL .= $this->buildSqlCondition("MONTH(audit.audit_date)", $selectedMonthNums);
        $overallScoreSQL .= $latestAuditSubquery_audit;
        
        $overallScoreSQL .= " GROUP BY t.structured_audit_id ) final_scores";

        $overallScoreResult = $db->query($overallScoreSQL)->getRowArray();
        $data['overall_oe_score'] = isset($overallScoreResult['overall_score']) ? (float)$overallScoreResult['overall_score'] : null;

        /* ==========================================
         * Calculate Total OE Audits Count
         * ========================================== */
        $totalAuditsSQL = "SELECT COUNT(DISTINCT audit.structured_audit_id) AS total_audits
            FROM alert_final_structured_audit audit
            " . (!empty($selected_category) ? "INNER JOIN alert_final_structured_audit_details d ON audit.structured_audit_id = d.structured_audit_id" : "") . "
            WHERE audit.region IS NOT NULL
            " . (!empty($selected_category) ? $this->buildSqlCondition('d.category', $selected_category) : "") . "
            {$reauditCondition_audit}
              AND audit.region <> '' {$oeAclFilter}";

        $totalAuditsSQL .= $this->buildSqlCondition("audit.region", $selected_region);
        $totalAuditsSQL .= $this->buildSqlCondition("audit.cluster_name", $cluster_name);
        $totalAuditsSQL .= $this->buildSqlCondition("audit.client_name", $location_name);
        $totalAuditsSQL .= $this->buildSqlCondition("YEAR(audit.audit_date)", $selectedYearNums);
        $totalAuditsSQL .= $this->buildSqlCondition("MONTH(audit.audit_date)", $selectedMonthNums);
        $totalAuditsSQL .= $latestAuditSubquery_audit;

        $totalAuditsResult = $db->query($totalAuditsSQL)->getRowArray();
        $data['total_oe_audits_count'] = isset($totalAuditsResult['total_audits']) ? (int)$totalAuditsResult['total_audits'] : 0;

        /* ==========================================
         * 2) OE AGING (category-wise, by audit_date)
         * ========================================== */
        $oeAgingFilter = getOEAuditACLWhere('audit', 'OE');
        $agingSQL = "SELECT 
            audit.client_name as location, 
            DATEDIFF(CURDATE(), audit.audit_date) as aging_days,
            audit.audit_date,
            audit.structured_audit_id,
            audit.audit_no,
            COALESCE(NULLIF(audit.snapshot_cluster_manager_name, ''), NULLIF(audit.cluster_name, ''), ac.cluster, '-') as cluster_name,
            COALESCE(NULLIF(audit.snapshot_account_manager_name, ''), NULLIF(audit.client_manager_name, ''), ac.account_manager, '-') as account_manager,
            audit.region,
            audit.client_name,
            audit_details.audit_template_id,
            audit_details.category,
            COUNT(DISTINCT audit_details.audit_details_id) as openpoints
        FROM `alert_final_structured_audit` audit
        LEFT JOIN alert_client ac ON (ac.client_name = audit.client_name OR ac.client_name = audit.location)
        LEFT JOIN alert_final_structured_audit_details audit_details ON audit.structured_audit_id = audit_details.structured_audit_id
        WHERE audit_details.audit_finding = 'NO' AND audit_details.status IN (0, 1, 2, 3, 6) {$oeAgingFilter}";

        $agingSQL .= $this->buildSqlCondition("audit_details.category", $selected_category);
        $agingSQL .= $this->buildSqlCondition("audit.region", $selected_region);
        $agingSQL .= $this->buildSqlCondition("audit.cluster_name", $cluster_name);
        $agingSQL .= $this->buildSqlCondition("audit.client_name", $location_name);
        $agingSQL .= $this->buildSqlCondition("YEAR(audit.audit_date)", $selectedYearNums);
        $agingSQL .= $this->buildSqlCondition("MONTH(audit.audit_date)", $selectedMonthNums);
        $agingSQL .= $latestAuditSubquery_audit;

        $agingSQL .= " GROUP BY audit.structured_audit_id, category";
        $data['aging_OE_score'] = $db->query($agingSQL)->getResultArray();

        /* ==========================================
         * 3) Template-wise Total (unchanged)
         * ========================================== */
        $total_query = $db->query("
            SELECT COUNT(DISTINCT a.audit_template_id) AS audit_count, 
                   a.client_name, a.region, 
                   SUM(d.weightage) AS total, 
                   SUM(CASE WHEN d.audit_finding IN ('YES','NA') THEN d.weightage ELSE 0 END) AS yes, 
                   SUM(CASE WHEN d.audit_finding = 'no' THEN d.weightage ELSE 0 END) AS no 
            FROM alert_final_structured_audit_details d
            LEFT JOIN alert_final_structured_audit a 
                ON d.structured_audit_id = a.structured_audit_id 
            WHERE 1=1 " . $this->buildSqlCondition('d.category', $selected_category) . "
            GROUP BY client_name, zone
        ");
        $data['total_score'] = $total_query->getResultArray();

        // Raw upcoming (not filtered)
        $data['upcoming_audit'] = $db->query("SELECT * FROM alert_final_structured_audit")->getResultArray();

        /* ==========================================
         * 4) Upcoming OE Audits (still based on next_date)
         * ========================================== */
        $aclFilter = getOEAuditACLWhere('audit', 'OE');
        $data['OE_audit'] = $db->query("
            SELECT * 
            FROM alert_final_structured_audit audit 
            LEFT JOIN alert_final_structured_audit_details audit_details
                ON audit.structured_audit_id = audit_details.structured_audit_id
            WHERE next_date >= CURDATE() AND reaudit = 0 {$aclFilter}
            " . $this->buildSqlCondition("audit_details.category", $selected_category) . "
            " . $this->buildSqlCondition("audit.region", $selected_region) .
            $this->buildSqlCondition("audit.cluster_name", $cluster_name) .
            $this->buildSqlCondition("audit.client_name", $location_name) .
            " GROUP BY audit.structured_audit_id
        ")->getResultArray();

        /* ==========================================
         * 5) OE Openpoint Report (by audit_date month)
         * ========================================== */
        $oeAclFilterReport = getOEAuditACLWhere('audit', 'OE');
        $yearWhereSql = $this->buildSqlCondition("YEAR(audit.audit_date)", $selectedYearNums);
        $monthWhereSql = $this->buildSqlCondition("MONTH(audit.audit_date)", $selectedMonthNums);

        $data['open_point_report'] = $db->query("
                SELECT 
                    audit.*, 
                    audit.structured_audit_id, 
                    audit.client_name,
                    audit.region,
                    DATE_FORMAT(audit.audit_date, '%M') AS audit_month,

                    COALESCE(NULLIF(audit.snapshot_cluster_manager_name, ''), NULLIF(audit.cluster_name, ''), ac.cluster, '-') AS cluster_name,
                    COALESCE(NULLIF(audit.snapshot_account_manager_name, ''), NULLIF(audit.client_manager_name, ''), ac.account_manager, '-') AS account_manager,

                    audit_details.category, 
                    audit_details.audit_parameter, 
                    audit_details.audit_remark

                FROM alert_final_structured_audit audit

                LEFT JOIN alert_client ac ON (ac.client_name = audit.client_name OR ac.client_name = audit.location)

                LEFT JOIN alert_final_structured_audit_details audit_details
                    ON audit.structured_audit_id = audit_details.structured_audit_id

                WHERE audit_details.audit_finding = 'NO' AND audit_details.status = 0
                {$reauditCondition_audit}
                {$oeAclFilterReport}
                " . $this->buildSqlCondition("audit_details.category", $selected_category) . "

                " . $this->buildSqlCondition(
                    "audit.region",
                    $selected_region
                ) .

            $this->buildSqlCondition(
                "audit.cluster_name",
                $cluster_name
            ) .

            $this->buildSqlCondition(
                "audit.client_name",
                $location_name
            ) .

            $yearWhereSql .
            $monthWhereSql . $latestAuditSubquery_audit . "

        GROUP BY audit_details.audit_details_id

        ")->getResultArray();

        $oeAclWhereA = getOEAuditACLWhere('a', 'OE');
        $whereClause = " WHERE 1=1 {$reauditCondition_a} {$oeAclWhereA} ";

        $whereClause .= $this->buildSqlCondition("a.region", $selected_region);
        $whereClause .= $this->buildSqlCondition("a.cluster_name", $cluster_name);
        if (!empty($location_name)) {
            $locArray = (array)$location_name;
            $escapedLocs = array_map([$db, 'escape'], $locArray);
            $whereClause .= " AND (a.location IN (" . implode(',', $escapedLocs) . ") OR a.client_name IN (" . implode(',', $escapedLocs) . "))";
        }

        $whereClause .= $this->buildSqlCondition("YEAR(a.audit_date)", $selectedYearNums);
        $whereClause .= $this->buildSqlCondition("MONTH(a.audit_date)", $selectedMonthNums);
        $whereClause .= $latestAuditSubquery_a;

        /* ---------------- MAIN QUERY ---------------- */

        $data['total_OE_score_openpoints'] = $db->query("
                SELECT 
                    MAX(a.audit_no) AS audit_no,
                    MAX(a.audit_date) AS audit_date,
                    a.location,
                    a.region,
                    COALESCE(NULLIF(a.snapshot_cluster_manager_name, ''), NULLIF(a.cluster_name, ''), ac.cluster, '-') AS cluster_name,
                    COALESCE(NULLIF(a.snapshot_account_manager_name, ''), NULLIF(a.client_manager_name, ''), ac.account_manager, '-') AS account_manager,
                    a.client_name,
    
                    /* TOTAL OPEN POINTS */
                    COUNT(d.structured_audit_id) AS total_openpoints,
    
                    /* STATUS WISE COUNTS */
                    SUM(CASE WHEN d.status = 0 THEN 1 ELSE 0 END) AS count_open_points,
                    SUM(CASE WHEN d.status = 1 THEN 1 ELSE 0 END) AS count_working_points,
                    SUM(CASE WHEN d.status = 6 THEN 1 ELSE 0 END) AS count_under_review_cm,
                    SUM(CASE WHEN d.status = 2 THEN 1 ELSE 0 END) AS count_under_review_auditor,
                    SUM(CASE WHEN d.status = 3 THEN 1 ELSE 0 END) AS count_closed_points,
                    SUM(CASE WHEN d.status = 4 THEN 1 ELSE 0 END) AS count_draft_points,
                    SUM(CASE WHEN d.status = 5 THEN 1 ELSE 0 END) AS count_force_closed,
    
                    GROUP_CONCAT(
                        DISTINCT d.audit_remark
                        ORDER BY d.audit_remark
                        SEPARATOR ', '
                    ) AS remarks_combined
    
                FROM alert_final_structured_audit a

                LEFT JOIN alert_client ac ON (ac.client_name = a.client_name OR ac.client_name = a.location)
    
                LEFT JOIN alert_final_structured_audit_details d
                    ON a.structured_audit_id = d.structured_audit_id
                    AND d.audit_finding = 'NO'
    
                {$whereClause}
                " . $this->buildSqlCondition('d.category', $selected_category) . "
    
                GROUP BY a.location
    
                ")->getResultArray();




        /* ==========================================
         * 8) OE Month Summary (FY + month, by audit_date)
         * ========================================== */
        $oeFilterSql = $this->buildSqlCondition("audit.region", $selected_region);
        $oeFilterSql .= $this->buildSqlCondition("audit.cluster_name", $cluster_name);
        $oeFilterSql .= $this->buildSqlCondition("audit.location", $location_name);

        $fyFilterSql = '';
        if (!empty($postData['financial_year_months'])) {
            $fy = $postData['financial_year_months'];
            $dateCol = "audit.audit_date";
            $fyFilterSql = " AND (
                    CASE 
                        WHEN MONTH($dateCol) >= 4 
                            THEN CONCAT(YEAR($dateCol), '-', YEAR($dateCol) + 1) 
                        ELSE CONCAT(YEAR($dateCol) - 1, '-', YEAR($dateCol)) 
                    END
                ) = '{$fy}'";
        }

        $yearFilterSql = $this->buildSqlCondition("YEAR(audit.audit_date)", $selectedYearNums);
        $monthFilterSql = $this->buildSqlCondition("MONTH(audit.audit_date)", $selectedMonthNums);

        $oeAclFilterBase = getOEAuditACLWhere('audit', 'OE');

        $data['OE_year_score_query'] = $db->query("
    SELECT 
        audit.location,
        audit.region,

        CASE 
            WHEN YEAR(audit.audit_date) = 2025 
            THEN loc_first.cluster
            ELSE loc_last.cluster
        END AS cluster_name,

        ROUND(
            IFNULL(
                (SUM(CASE WHEN d.audit_finding IN ('YES','NA') THEN d.weightage ELSE 0 END) /
                 NULLIF(SUM(d.weightage), 0)) * 100,
            0), 2
        ) AS avg_score_percentage,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 4 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 4 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Apr_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 5 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 5 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS May_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 6 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 6 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Jun_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 7 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 7 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Jul_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 8 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 8 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Aug_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 9 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 9 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Sep_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 10 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 10 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Oct_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 11 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 11 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Nov_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 12 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 12 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Dec_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 1 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 1 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Jan_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 2 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 2 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Feb_Score,

        ROUND(IFNULL((SUM(CASE WHEN d.audit_finding IN ('YES','NA') AND MONTH(audit.audit_date) = 3 THEN d.weightage ELSE 0 END) /
            NULLIF(SUM(CASE WHEN MONTH(audit.audit_date) = 3 THEN d.weightage ELSE 0 END), 0)) * 100,0),2) AS Mar_Score

    FROM alert_final_structured_audit AS audit

    LEFT JOIN alert_final_structured_audit_details d
        ON audit.structured_audit_id = d.structured_audit_id

    /* FIRST cluster per location */
    LEFT JOIN (
        SELECT *
        FROM alert_client l1
        WHERE l1.client_id = (
            SELECT MIN(l2.client_id)
            FROM alert_client l2
            WHERE l2.client_name = l1.client_name
        )
    ) AS loc_first
        ON audit.location = loc_first.client_name

    /* LAST cluster per location */
    LEFT JOIN (
        SELECT *
        FROM alert_client l1
        WHERE l1.client_id = (
            SELECT MAX(l2.client_id)
            FROM alert_client l2
            WHERE l2.client_name = l1.client_name
        )
    ) AS loc_last
        ON audit.location = loc_last.client_name

    WHERE 1=1 
        " . $this->buildSqlCondition('d.category', $selected_category) . "
        {$oeAclFilterBase} 
        {$oeFilterSql} 
        {$fyFilterSql} 
        {$yearFilterSql} 
        {$monthFilterSql}

        AND audit.structured_audit_id IN (
            SELECT MAX(audit.structured_audit_id)
            FROM alert_final_structured_audit AS audit
            WHERE 1=1 
                {$oeAclFilterBase} 
                {$oeFilterSql} 
                {$fyFilterSql} 
                {$yearFilterSql} 
                {$monthFilterSql}
            GROUP BY audit.location, YEAR(audit.audit_date), MONTH(audit.audit_date)
        )

    GROUP BY 
        audit.location, 
        audit.region,
        CASE 
            WHEN YEAR(audit.audit_date) = 2025 
            THEN loc_first.cluster
            ELSE loc_last.cluster
        END
")->getResultArray();
        /* -------- Selected defaults for dropdowns -------- */
        if (isClusterManager()) {
            $data['is_cluster_manager'] = true;
            $data['is_account_manager'] = false;
            $data['selected_region'] = getClusterManagerAssignedRegion();
            $data['selected_cluster'] = getClusterManagerAssignedCluster();
            $data['selected_location'] = $this->request->getVar('location_name') ?? '';

            $data['locked_region'] = $data['selected_region'];
            $data['locked_cluster'] = $data['selected_cluster'];
        } elseif (isAccountManager()) {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = true;
            $data['selected_region'] = $this->request->getVar('region') ?? '';
            $data['selected_cluster'] = $this->request->getVar('cluster_name') ?? '';
            $data['selected_location'] = $this->request->getVar('location_name') ?? '';

            $amDetails = getAccountManagerAssignedClient();
            $data['locked_region'] = array_unique(array_filter(array_column(getAccountManagerClientDetails() ?: [], 'region')));
            $data['locked_cluster'] = array_unique(array_filter(array_column(getAccountManagerClientDetails() ?: [], 'cluster')));
        } else {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = false;
            $data['selected_region'] = $this->request->getVar('region') ?? '';
            $data['selected_cluster'] = $this->request->getVar('cluster_name') ?? '';
            $data['selected_location'] = $this->request->getVar('location_name') ?? '';
        }

        // Add year and month to data
        $data['selected_year'] = $this->request->getVar('filter_year') ?? '';
        $data['selected_month'] = $this->request->getVar('month') ?? '';

        // Ensure locked_region and locked_cluster are always set for Account Manager
        if (isAccountManager()) {
            if (!isset($data['locked_region'])) {
                $data['locked_region'] = getAccountManagerAssignedRegion();
            }
            if (!isset($data['locked_cluster'])) {
                $data['locked_cluster'] = getAccountManagerAssignedCluster();
            }
            if (!isset($data['locked_location'])) {
                $data['locked_location'] = getAccountManagerAssignedClient();
            }
        }

        /* ==========================================
         * TOTAL UNIQUE OE AUDITS KPI
         * COUNT(DISTINCT) excluding re-audits, with same filters + ACL
         * ========================================== */
        $oeAclTotalKpi = getOEAuditACLWhere('a', 'OE');
        $totalKpiWhere = " WHERE 1=1 {$reauditCondition_a} {$oeAclTotalKpi}";
        $totalKpiWhere .= $this->buildSqlCondition("a.region", $selected_region);
        $totalKpiWhere .= $this->buildSqlCondition("a.cluster_name", $cluster_name);
        if (!empty($location_name)) {
            $locArray = (array)$location_name;
            $escapedLocs = array_map([$db, 'escape'], $locArray);
            $totalKpiWhere .= " AND (a.location IN (" . implode(',', $escapedLocs) . ") OR a.client_name IN (" . implode(',', $escapedLocs) . "))";
        }

        if ($selectedYearNum !== null)
            $totalKpiWhere .= " AND YEAR(a.audit_date) = {$selectedYearNum}";
        if ($selectedMonthNum !== null)
            $totalKpiWhere .= " AND MONTH(a.audit_date) = {$selectedMonthNum}";
            
        $totalKpiWhere .= $latestAuditSubquery_a;

        $totalAuditsRow = $db->query(
            "SELECT COUNT(DISTINCT a.structured_audit_id) AS cnt
             FROM alert_final_structured_audit a
             {$totalKpiWhere}"
        )->getRow();
        $data['total_oe_audits_count'] = $totalAuditsRow ? (int) $totalAuditsRow->cnt : 0;

        $selectedSiteManagers = [];
        if (!empty($location_name)) {
            $siteRows = $db->table('alert_client')
                ->select('client_id, client_name, region, cluster AS cluster_manager, account_manager')
                ->whereIn('client_name', (array)$location_name)
                ->get()->getResultArray();

            foreach ($siteRows as $ssm) {
                $siteName = $ssm['client_name'];
                $mappedUsers = $db->table('alert_user_client_mapping m')
                    ->select('u.user_name, u.user_designation AS designation')
                    ->join('alert_users u', 'u.user_id = m.user_id')
                    ->groupStart()
                        ->where('LOWER(TRIM(m.site_name))', strtolower(trim($siteName)))
                        ->orWhere('m.client_id', (int)($ssm['client_id'] ?? 0))
                    ->groupEnd()
                    ->where('u.status', 1)
                    ->get()->getResultArray();

                $mappedAMs = [];
                $mappedCMs = [];
                foreach ($mappedUsers as $mu) {
                    $des = strtolower(trim($mu['designation'] ?? ''));
                    if ($des === 'account manager') {
                        $mappedAMs[] = trim($mu['user_name']);
                    } elseif ($des === 'cluster manager') {
                        $mappedCMs[] = trim($mu['user_name']);
                    }
                }

                if (!empty($mappedAMs)) {
                    $ssm['account_manager'] = implode(', ', array_unique($mappedAMs));
                }
                if (!empty($mappedCMs)) {
                    $ssm['cluster_manager'] = implode(', ', array_unique($mappedCMs));
                }

                $selectedSiteManagers[] = $ssm;
            }
        }
        $data['selected_site_managers'] = $selectedSiteManagers;


        return view("Customer/oe_dashboard", $data);

    }


    /* ========================= AJAX CASCADE HELPERS (for Select2) ========================= */

    /* ========================= HSE FILTER + DASHBOARD ========================= */
    // Updated: Implementing OE dashboard features - ACL, month filter, improved logic

    public function HSE_filter()
    {
        $db = db_connect();
        $postData = $this->request->getVar();

        // Basic filters from POST
        $selected_region = $postData['region'] ?? '';
        $cluster_name = $postData['cluster_name'] ?? '';
        $location_name = $postData['location_name'] ?? '';
        $selected_month = $postData['month'] ?? '';

        // NEW CHANGE: Handle comma-separated strings from locked multi-filters
        if (is_string($selected_region) && strpos($selected_region, ',') !== false) {
            $selected_region = explode(',', $selected_region);
        }
        if (is_string($cluster_name) && strpos($cluster_name, ',') !== false) {
            $cluster_name = explode(',', $cluster_name);
        }
        if (is_string($location_name) && strpos($location_name, ',') !== false) {
            $location_name = explode(',', $location_name);
        }

        // Prepare data with ACL (like OE dashboard)
        $data = $this->addACLToViewData([]);
        $data['selected_month'] = $selected_month;

        // Dynamic regions from alert_client
        $data['dynamic_regions'] = $this->getDynamicRegions($db);
        $data['region_color_map'] = $this->getRegionColorMap($data['dynamic_regions']);

        // Normalize month (support '04' or 'April')
        $selectedMonthNum = null;
        if ($selected_month !== '') {
            if (ctype_digit($selected_month)) {
                $selectedMonthNum = (int) $selected_month;
            } else {
                $monthMap = [
                    'january' => 1,
                    'february' => 2,
                    'march' => 3,
                    'april' => 4,
                    'may' => 5,
                    'june' => 6,
                    'july' => 7,
                    'august' => 8,
                    'september' => 9,
                    'october' => 10,
                    'november' => 11,
                    'december' => 12
                ];
                $key = strtolower(trim($selected_month));
                if (isset($monthMap[$key])) {
                    $selectedMonthNum = $monthMap[$key];
                }
            }
        }

        $data['country'] = $db->table("alert_country")->get()->getResultArray();

        /* -------- Role-based Dropdown Locking & Filtering -------- */
        if (isClusterManager() || isAccountManager() || isWHManager()) {
            $assignedClients = getUserAllocatedSiteNames('HSE');
            $assignedRegions = getClusterManagerAssignedRegionHSE();
            $assignedClusters = getClusterManagerAssignedClusterHSE();

            if (empty($selected_region))
                $selected_region = $assignedRegions;
            if (empty($cluster_name))
                $cluster_name = $assignedClusters;
            if (empty($location_name))
                $location_name = $assignedClients;

            $data['locked_cluster'] = $assignedClusters;
            $data['locked_region'] = $assignedRegions;
            $data['locked_location'] = $assignedClients;
            $data['is_cluster_manager'] = isClusterManager();
            $data['is_account_manager'] = isAccountManager() || isWHManager();

            $data['location'] = array_map(function ($c) {
                return ['location_name' => $c]; }, (array) $assignedClients);
            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c]; }, (array) $assignedClusters);
            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r]; }, (array) $assignedRegions);

            $data['lock_cluster'] = false;
            $data['lock_location'] = false;
        } else {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = false;

            // Region list from alert_client (dynamic)
            $data['region'] = $db->table('alert_client')
                ->select('DISTINCT(region) AS region_name', false)
                ->where('region IS NOT NULL')->where('status', 1)
                ->where('region !=', '')
                ->where('status', 1)
                ->orderBy('region', 'ASC')
                ->get()
                ->getResultArray();

            // Default locks
            $data['cluster'] = [];
            $data['location'] = [];

            // If region selected, load clusters for that region
            if ($selected_region != '') {
                $data['cluster'] = $db->table('alert_location_master')
                    ->select('DISTINCT(cluster_name) AS cluster_name', false)
                    ->where('region_name', $selected_region)
                    ->where('status', 1)
                    ->get()
                    ->getResultArray();

                $data['lock_cluster'] = false;

                // If cluster selected, load locations for region+cluster
                if ($cluster_name != '') {
                    $data['location'] = $db->table('alert_location_master')
                        ->select('DISTINCT(location_name) AS location_name', false)
                        ->where('region_name', $selected_region)
                        ->where('cluster_name', $cluster_name)
                        ->where('status', 1)
                        ->get()
                        ->getResultArray();

                    $data['lock_location'] = false;
                } else {
                    $data['lock_location'] = true;
                }
            } else {
                $data['lock_cluster'] = true;
                $data['lock_location'] = true;
            }
        }

        $data['selected_region'] = $selected_region;
        $data['selected_cluster'] = $cluster_name;
        $data['selected_location'] = $location_name;

        /* -------- Month & FY Lists (like OE dashboard) -------- */
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

        $currentYear = date('Y');
        $financialYears = [];
        for ($i = 0; $i < 5; $i++) {
            $startYear = ($currentYear - $i) - 1;
            $endYear = $currentYear - $i;
            $financialYears[] = "$startYear-$endYear";
        }
        $data['financialYears'] = $financialYears;

        $currentMonth = date('m');
        if ($currentMonth >= 4) {
            $data['audit_year'] = $currentYear . '-' . ($currentYear + 1);
        } else {
            $data['audit_year'] = ($currentYear - 1) . '-' . $currentYear;
        }

        $data['category_HSE'] = $db->table("alert_question_audit_master")
            ->select("question_name")
            ->groupby("TRIM(question_name)")
            ->get()->getResultArray();

        /* ==========================================
         * 1) HSE PIE CHART (with ACL filtering and month filter)
         * ========================================== */
        $aclFilter = getClusterFilterByLocationForHSE('master.location');

        $pieChartSQL = "SELECT 
            master.region,
            ROUND((
                IFNULL((COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) / 
                        NULLIF(COUNT(CASE WHEN details.client_leased IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
              + IFNULL((COUNT(CASE WHEN details.inplant = 'YES' THEN 1 ELSE NULL END) / 
                        NULLIF(COUNT(CASE WHEN details.inplant IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
              + IFNULL((COUNT(CASE WHEN details.fm_leased = 'YES' THEN 1 ELSE NULL END) / 
                        NULLIF(COUNT(CASE WHEN details.fm_leased IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
            ) * 100 / 3, 2) AS score,
            (
                COUNT(CASE WHEN details.client_leased = 'NO' THEN 1 ELSE NULL END) +
                COUNT(CASE WHEN details.inplant = 'NO' THEN 1 ELSE NULL END) +
                COUNT(CASE WHEN details.fm_leased = 'NO' THEN 1 ELSE NULL END)
            ) AS openpoints
        FROM alert_hse_audit_master AS master
        LEFT JOIN alert_hse_audit_details AS details 
            ON master.hse_audit_id = details.hse_audit_id
        LEFT JOIN alert_location_master AS loc 
            ON master.location = loc.location_name
        WHERE master.region IS NOT NULL 
          AND master.region <> '' {$aclFilter}";

        $pieChartSQL .= $this->buildSqlCondition("master.region", $selected_region);
        $pieChartSQL .= $this->buildSqlCondition("loc.cluster_name", $cluster_name);
        $pieChartSQL .= $this->buildSqlCondition("master.location", $location_name);
        if ($selectedMonthNum !== null)
            $pieChartSQL .= " AND MONTH(master.audit_date) = {$selectedMonthNum}";

        $pieChartSQL .= " GROUP BY master.region";
        $data['pie_chart'] = $db->query($pieChartSQL)->getResultArray();

        /* ==========================================
         * 2) HSE AGING (with ACL and month filter)
         * ========================================== */
        $monthWhere = ($selectedMonthNum !== null) ? " AND MONTH(master.audit_date) = {$selectedMonthNum}" : "";

        $data['aging_HSE_score'] = $db->query("
            SELECT b.hse_audit_id, audit_no, audit_name, c.question_name, 
                   SUM(CASE WHEN c.client_leased = 'NO' THEN 1 ELSE 0 END) AS total_client_leased_no, 
                   SUM(CASE WHEN c.inplant = 'NO' THEN 1 ELSE 0 END) AS total_inplant_no, 
                   SUM(CASE WHEN c.fm_leased = 'NO' THEN 1 ELSE 0 END) AS total_fm_leased_no 
            FROM alert_hse_audit_master b 
            LEFT JOIN alert_hse_audit_details c 
                ON c.hse_audit_id = b.hse_audit_id 
            LEFT JOIN alert_location_master loc 
                ON b.location = loc.location_name
            WHERE 1=1 {$aclFilter}
            " . $this->buildSqlCondition("b.region", $selected_region) .
            $this->buildSqlCondition("loc.cluster_name", $cluster_name) .
            $this->buildSqlCondition("b.location", $location_name) .
            $monthWhere . "
            GROUP BY c.question_name
        ")->getResultArray();

        /* ==========================================
         * 3) HSE UPCOMING AUDITS (with ACL)
         * ========================================== */
        $aclFilterNormal = getClusterFilterByLocation('location');
        $data['HSE_audit'] = $db->query("
            SELECT * 
            FROM alert_normal_audit 
            WHERE next_date >= CURDATE() {$aclFilterNormal}
            " . $this->buildSqlCondition("region", $selected_region) .
            $this->buildSqlCondition("location", $location_name) . "
        ")->getResultArray();

        /* ==========================================
         * 4) HSE OPEN POINTS REPORT (with ACL and month filter)
         * ========================================== */
        $monthWhere2 = ($selectedMonthNum !== null) ? " AND MONTH(master.audit_date) = {$selectedMonthNum}" : "";

        $data['open_point_report'] = $db->query("
            SELECT details.*, master.location, master.region, loc.cluster_name, 
                   details.question_name, details.audit_question, details.nc_remark, master.perform_audit_by
            FROM alert_hse_audit_master AS master
            LEFT JOIN alert_hse_audit_details details 
                ON details.hse_audit_id = master.hse_audit_id 
            LEFT JOIN alert_location_master loc 
                ON master.location = loc.location_name
            WHERE 1=1 {$aclFilter}
            " . $this->buildSqlCondition("master.region", $selected_region) .
            $this->buildSqlCondition("loc.cluster_name", $cluster_name) .
            $this->buildSqlCondition("master.location", $location_name) .
            $monthWhere2 . "
        ")->getResultArray();

        /* ==========================================
         * 5) HSE OPEN POINTS SUMMARY (with ACL and month filter - latest audit per location)
         * ========================================== */
        $whereClause = " WHERE 1=1 {$aclFilter}";
        $whereClause .= $this->buildSqlCondition("master.region", $selected_region);
        $whereClause .= $this->buildSqlCondition("loc.cluster_name", $cluster_name);
        $whereClause .= $this->buildSqlCondition("master.location", $location_name);
        if ($selectedMonthNum !== null)
            $whereClause .= " AND MONTH(master.audit_date) = {$selectedMonthNum}";

        $latestAuditPerLocationSQL = "
            SELECT 
                master.location,
                MAX(master.audit_date) AS latest_audit_date
            FROM alert_hse_audit_master master
            LEFT JOIN alert_location_master AS loc 
                ON master.location = loc.location_name
            {$whereClause}
            GROUP BY master.location
        ";

        $data['total_HSE_score_openpoints'] = $db->query("
            SELECT 
                a.location, 
                a.region,
                loc.cluster_name, 
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.client_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100),
                    0), 2
                ) AS client_leased_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.inplant = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100),
                    0), 2
                ) AS inplant_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.fm_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100),
                    0), 2
                ) AS fm_leased_avg_score,
                COUNT(CASE WHEN d.client_leased = 'NO' THEN 1 ELSE NULL END) AS client_leased_openpoints,
                COUNT(CASE WHEN d.inplant = 'NO' THEN 1 ELSE NULL END) AS inplant_openpoints,
                COUNT(CASE WHEN d.fm_leased = 'NO' THEN 1 ELSE NULL END) AS fm_leased_openpoints,
                GROUP_CONCAT(DISTINCT d.remark SEPARATOR ', ') AS remarks
            FROM alert_hse_audit_master a
            LEFT JOIN alert_hse_audit_details d 
                ON a.hse_audit_id = d.hse_audit_id
            LEFT JOIN alert_location_master AS loc 
                ON a.location = loc.location_name
            INNER JOIN (
                {$latestAuditPerLocationSQL}
            ) latest
                ON latest.location = a.location
               AND latest.latest_audit_date = a.audit_date
            GROUP BY a.location
        ")->getResultArray();

        /* ==========================================
         * 6) HSE MONTH SUMMARY (with ACL, month filter, latest audit per location/month)
         * ========================================== */
        $hseFilterSql = $this->buildSqlCondition("master.region", $selected_region);
        $hseFilterSql .= $this->buildSqlCondition("loc.cluster_name", $cluster_name);
        $hseFilterSql .= $this->buildSqlCondition("master.location", $location_name);

        $fyFilterSql = '';
        if (!empty($postData['financial_year_months'])) {
            $fy = $postData['financial_year_months'];
            $dateCol = "master.audit_date";
            $fyFilterSql = " AND (
                CASE 
                    WHEN MONTH($dateCol) >= 4 
                        THEN CONCAT(YEAR($dateCol), '-', YEAR($dateCol) + 1) 
                    ELSE CONCAT(YEAR($dateCol) - 1, '-', YEAR($dateCol)) 
                END
            ) = '{$fy}'";
        }

        $monthFilterSql = '';
        if ($selectedMonthNum !== null) {
            $monthFilterSql = " AND MONTH(master.audit_date) = {$selectedMonthNum}";
        }

        $data['HSE_year_score_query'] = $db->query("
            SELECT 
                b.location, 
                b.region,
                b.cluster_name, 
                b.financial_year, 
                b.audit_month, 

                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.client_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS client_leased_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.inplant = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS inplant_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.fm_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS fm_leased_avg_score

            FROM (
                SELECT 
                    master.hse_audit_id,
                    master.location, 
                    master.region,
                    loc.cluster_name, 
                    CASE 
                        WHEN MONTH(master.audit_date) >= 4 THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1)
                        ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date))
                    END AS financial_year, 
                    DATE_FORMAT(master.audit_date, '%M') AS audit_month, 
                    master.audit_date
                FROM alert_hse_audit_master AS master
                LEFT JOIN alert_location_master loc 
                    ON master.location = loc.location_name
                WHERE 1=1 {$aclFilter} {$hseFilterSql} {$fyFilterSql} {$monthFilterSql}
            ) b
            JOIN (
                SELECT 
                    master.location,
                    loc.cluster_name,
                    CASE 
                        WHEN MONTH(master.audit_date) >= 4 THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1)
                        ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date))
                    END AS financial_year, 
                    DATE_FORMAT(master.audit_date, '%M') AS audit_month, 
                    MAX(master.audit_date) AS max_audit_date
                FROM alert_hse_audit_master AS master
                LEFT JOIN alert_location_master loc 
                    ON master.location = loc.location_name
                WHERE 1=1 {$aclFilter} {$hseFilterSql} {$fyFilterSql} {$monthFilterSql}
                GROUP BY master.location, loc.cluster_name, financial_year, audit_month
            ) latest
                ON b.location       = latest.location
               AND b.cluster_name   = latest.cluster_name
               AND b.financial_year = latest.financial_year
               AND b.audit_month    = latest.audit_month
               AND b.audit_date     = latest.max_audit_date
            LEFT JOIN alert_hse_audit_details d 
                ON b.hse_audit_id = d.hse_audit_id
            GROUP BY b.location, b.region, b.cluster_name, b.financial_year, b.audit_month
            ORDER BY b.financial_year DESC, 
                     FIELD(b.audit_month, 'April','May','June','July','August','September','October','November','December','January','February','March')
        ")->getResultArray();

        /* -------- Calculate overall HSE score (like OE dashboard) -------- */
        $overall_hse_score = null;
        if (!empty($data['pie_chart']) && is_array($data['pie_chart'])) {
            $sum = 0;
            $count = 0;

            foreach ($data['pie_chart'] as $row) {
                if (isset($row['score']) && is_numeric($row['score'])) {
                    $sum += (float) $row['score'];
                    $count++;
                }
            }

            if ($count > 0) {
                $overall_hse_score = round($sum / $count, 2);
            }
        }
        $data['overall_hse_score'] = $overall_hse_score;

        /* -------- Bar chart data (sorted and filtered like OE dashboard) -------- */
        $data['bar_chart_data'] = $data['total_HSE_score_openpoints'];

        /* -------- Selected defaults for dropdowns -------- */
        if (isClusterManager()) {
            $data['is_cluster_manager'] = true;
            $data['is_account_manager'] = false;
            $data['selected_region'] = getClusterManagerAssignedRegion();
            $data['selected_cluster'] = getClusterManagerAssignedCluster();
            $data['selected_location'] = $this->request->getVar('location_name') ?? '';

            $data['locked_region'] = $data['selected_region'];
            $data['locked_cluster'] = $data['selected_cluster'];
        } elseif (isAccountManager()) {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = true;
            $data['selected_region'] = $this->request->getVar('region') ?? '';
            $data['selected_cluster'] = $this->request->getVar('cluster_name') ?? '';
            $data['selected_location'] = $this->request->getVar('location_name') ?? '';

            $amDetails = getAccountManagerClientDetails();
            $data['locked_region'] = array_unique(array_filter(array_column($amDetails ?: [], 'region')));
            $data['locked_cluster'] = array_unique(array_filter(array_column($amDetails ?: [], 'cluster')));
        } else {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = false;
            $data['selected_region'] = $this->request->getVar('region') ?? '';
            $data['selected_cluster'] = $this->request->getVar('cluster_name') ?? '';
            $data['selected_location'] = $this->request->getVar('location_name') ?? '';
        }

        return view("Customer/hse_dashboard", $data);
    }


    public function HSE_Audit()
    {
        $db = db_connect();

        $data['country'] = $db->table("alert_country")->get()->getResultArray();

        // Dynamic regions from alert_client
        $data['dynamic_regions'] = $this->getDynamicRegions($db);
        $data['region_color_map'] = $this->getRegionColorMap($data['dynamic_regions']);

        if (isClusterManager()) {
            $assignedCluster = getClusterManagerAssignedCluster();
            $assignedRegion = getClusterManagerAssignedRegion();

            $data['locked_cluster'] = $assignedCluster;
            $data['locked_region'] = $assignedRegion;
            $data['is_cluster_manager'] = true;
            $data['is_account_manager'] = false;

            $locationQueryForCM = $db->table("alert_location_master");
            if (!empty($assignedCluster)) {
                $locationQueryForCM->whereIn('cluster_name', $assignedCluster);
            }
            if (!empty($assignedRegion)) {
                $locationQueryForCM->whereIn('region_name', $assignedRegion);
            }
            $data['location'] = $locationQueryForCM->groupBy("location_name")
                ->get()->getResultArray();

            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c]; }, $assignedCluster);
            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r]; }, $assignedRegion);

            $data['lock_cluster'] = false;
            $data['lock_location'] = false;
        } elseif (isAccountManager()) {
            $assignedDetails = getAccountManagerClientDetails();
            $assignedClients = [];
            $assignedRegions = [];
            $assignedClusters = [];

            if ($assignedDetails) {
                foreach ($assignedDetails as $row) {
                    if (!empty($row['client_name']))
                        $assignedClients[] = $row['client_name'];
                    if (!empty($row['region']))
                        $assignedRegions[] = $row['region'];
                    if (!empty($row['cluster']))
                        $assignedClusters[] = $row['cluster'];
                }
            }

            $assignedClients = array_unique(array_filter($assignedClients));
            $assignedRegions = array_unique(array_filter($assignedRegions));
            $assignedClusters = array_unique(array_filter($assignedClusters));

            $data['locked_cluster'] = $assignedClusters;
            $data['locked_region'] = $assignedRegions;
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = true;

            $data['location'] = array_map(function ($c) {
                return ['location_name' => $c]; }, $assignedClients);
            $data['cluster'] = array_map(function ($c) {
                return ['cluster_name' => $c]; }, $assignedClusters);
            $data['region'] = array_map(function ($r) {
                return ['region_name' => $r]; }, $assignedRegions);

            $data['lock_cluster'] = false;
            $data['lock_location'] = false;
        } else {
            $data['is_cluster_manager'] = false;
            $data['is_account_manager'] = false;

            // Region list from alert_client (dynamic)
            $data['region'] = $db->table('alert_client')
                ->select('DISTINCT(region) AS region_name', false)
                ->where('region IS NOT NULL')->where('status', 1)
                ->where('region !=', '')
                ->where('status', 1)
                ->orderBy('region', 'ASC')
                ->get()
                ->getResultArray();

            // Cluster & location locked initially
            $data['cluster'] = [];
            $data['location'] = [];

            $data['lock_cluster'] = true;
            $data['lock_location'] = true;
        }

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
        $currentYear = date('Y');
        $financialYears = [];
        for ($i = 0; $i < 5; $i++) {
            $startYear = ($currentYear - $i) - 1;
            $endYear = $currentYear - $i;
            $financialYears[] = "$startYear-$endYear";
        }
        $data['financialYears'] = $financialYears;

        $aclFilter = getClusterFilterByLocationForHSE('master.location');
        $data['pie_chart'] = $db->query("
            SELECT 
                master.region,
                ROUND((
                    IFNULL((COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) / NULLIF(COUNT(CASE WHEN details.client_leased IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
                  + IFNULL((COUNT(CASE WHEN details.inplant      = 'YES' THEN 1 ELSE NULL END) / NULLIF(COUNT(CASE WHEN details.inplant      IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
                  + IFNULL((COUNT(CASE WHEN details.fm_leased    = 'YES' THEN 1 ELSE NULL END) / NULLIF(COUNT(CASE WHEN details.fm_leased    IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
                ) * 100 / 3, 2) AS score,
                (COUNT(CASE WHEN details.client_leased = 'NO' THEN 1 ELSE NULL END)  +
                 COUNT(CASE WHEN details.inplant      = 'NO' THEN 1 ELSE NULL END) +
                 COUNT(CASE WHEN details.fm_leased    = 'NO' THEN 1 ELSE NULL END)) AS openpoints
            FROM alert_hse_audit_master AS master
            LEFT JOIN alert_hse_audit_details details 
                ON master.hse_audit_id = details.hse_audit_id
            WHERE master.region IS NOT NULL AND master.region <> '' {$aclFilter}
            GROUP BY master.region
        ")->getResultArray();

        // Calculate overall HSE score across all regions
        $overallScoreResult = $db->query("
            SELECT 
                ROUND((
                    IFNULL((COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) / NULLIF(COUNT(CASE WHEN details.client_leased IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
                  + IFNULL((COUNT(CASE WHEN details.inplant      = 'YES' THEN 1 ELSE NULL END) / NULLIF(COUNT(CASE WHEN details.inplant      IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
                  + IFNULL((COUNT(CASE WHEN details.fm_leased    = 'YES' THEN 1 ELSE NULL END) / NULLIF(COUNT(CASE WHEN details.fm_leased    IN ('YES','NO') THEN 1 ELSE NULL END),0)),0)
                ) * 100 / 3, 2) AS overall_score
            FROM alert_hse_audit_master AS master
            LEFT JOIN alert_hse_audit_details details 
                ON master.hse_audit_id = details.hse_audit_id
            WHERE master.region IS NOT NULL AND master.region <> '' {$aclFilter}
        ")->getRowArray();

        $data['overall_hse_score'] = $overallScoreResult['overall_score'] ?? null;

        $data['aging_HSE_score'] = $db->query("
            SELECT b.hse_audit_id, audit_no, audit_name, c.question_name, 
                   SUM(CASE WHEN c.client_leased = 'NO' THEN 1 ELSE 0 END) AS total_client_leased_no, 
                   SUM(CASE WHEN c.inplant      = 'NO' THEN 1 ELSE 0 END) AS total_inplant_no, 
                   SUM(CASE WHEN c.fm_leased    = 'NO' THEN 1 ELSE 0 END) AS total_fm_leased_no 
            FROM alert_hse_audit_master b 
            LEFT JOIN alert_hse_audit_details c 
                ON c.hse_audit_id= b.hse_audit_id 
            WHERE 1=1 " . getClusterFilterByLocationForHSE('b.location') . " 
            GROUP BY c.question_name
        ")->getResultArray();

        $data['open_point_report'] = $db->query("
            SELECT details.*, master.location, master.region, loc.cluster_name, 
                   details.question_name, details.audit_question, details.nc_remark, master.perform_audit_by
            FROM alert_hse_audit_master AS master
            LEFT JOIN alert_hse_audit_details details 
                ON details.hse_audit_id = master.hse_audit_id 
            LEFT JOIN alert_location_master loc 
                ON master.location = loc.location_name
            WHERE 1=1 {$aclFilter}
        ")->getResultArray();

        /* ---------- HSE Month Summary (LATEST hse_audit per location+month+year, ACL) ---------- */
        $data['HSE_year_score_query'] = $db->query("
            SELECT 
                b.location, 
                b.region, 
                b.cluster_name, 
                b.financial_year, 
                b.audit_month, 

                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.client_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS client_leased_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.inplant = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS inplant_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.fm_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS fm_leased_avg_score

            FROM (
                SELECT 
                    master.hse_audit_id,
                    master.location, 
                    master.region, 
                    loc.cluster_name, 
                    CASE 
                        WHEN MONTH(master.audit_date) >= 4 THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1)
                        ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date))
                    END AS financial_year, 
                    DATE_FORMAT(master.audit_date, '%M') AS audit_month, 
                    master.audit_date
                FROM alert_hse_audit_master AS master
                LEFT JOIN alert_location_master loc 
                    ON master.location = loc.location_name
                WHERE 1=1 {$aclFilter}
            ) b
            JOIN (
                SELECT 
                    master.location,
                    loc.cluster_name, 
                    CASE 
                        WHEN MONTH(master.audit_date) >= 4 THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1)
                        ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date))
                    END AS financial_year, 
                    DATE_FORMAT(master.audit_date, '%M') AS audit_month, 
                    MAX(master.audit_date) AS max_audit_date
                FROM alert_hse_audit_master AS master
                LEFT JOIN alert_location_master loc 
                    ON master.location = loc.location_name
                WHERE 1=1 {$aclFilter}
                GROUP BY master.location, loc.cluster_name, financial_year, audit_month
            ) latest
                ON b.location       = latest.location
               AND b.cluster_name   = latest.cluster_name
               AND b.financial_year = latest.financial_year
               AND b.audit_month    = latest.audit_month
               AND b.audit_date     = latest.max_audit_date
            LEFT JOIN alert_hse_audit_details d 
                ON b.hse_audit_id = d.hse_audit_id
            GROUP BY b.location, b.region, b.cluster_name, b.financial_year, b.audit_month
            ORDER BY b.financial_year DESC,
                     FIELD(b.audit_month, 'April','May','June','July','August','September','October','November','December','January','February','March')
        ")->getResultArray();

        $hseUpcomingFilter = getClusterFilterByLocationForHSE('master.location');
        $data['HSE_audit'] = $db->query("
            SELECT master.*, loc.location_name, master.region
            FROM alert_hse_audit_master master
            LEFT JOIN alert_location_master AS loc 
                ON master.location = loc.location_name
            WHERE next_date >= CURDATE() {$hseUpcomingFilter}
            GROUP BY master.hse_audit_id
        ")->getResultArray();

        $data['category_HSE'] = $db->table("alert_question_audit_master")
            ->select("question_name")
            ->groupBy("TRIM(question_name)")
            ->get()->getResultArray();

        /* ---------- HSE Open Points Summary (location-wise, latest audit) ---------- */
        $latestAuditPerLocationSQL = "
            SELECT 
                master.location,
                MAX(master.audit_date) AS latest_audit_date
            FROM alert_hse_audit_master master
            LEFT JOIN alert_location_master AS loc 
                ON master.location = loc.location_name
            WHERE 1=1 {$aclFilter}
            GROUP BY master.location
        ";

        $data['total_HSE_score_openpoints'] = $db->query("
            SELECT 
                a.location, 
                a.region,
                loc.cluster_name, 
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.client_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100),
                    0), 2
                ) AS client_leased_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.inplant = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100),
                    0), 2
                ) AS inplant_avg_score,
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN d.fm_leased = 'YES' THEN 1 ELSE NULL END) /
                         NULLIF(COUNT(CASE WHEN d.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100),
                    0), 2
                ) AS fm_leased_avg_score,
                COUNT(CASE WHEN d.client_leased = 'NO' THEN 1 ELSE NULL END) AS client_leased_openpoints,
                COUNT(CASE WHEN d.inplant = 'NO' THEN 1 ELSE NULL END) AS inplant_openpoints,
                COUNT(CASE WHEN d.fm_leased = 'NO' THEN 1 ELSE NULL END) AS fm_leased_openpoints,
                GROUP_CONCAT(DISTINCT d.remark SEPARATOR ', ') AS remarks
            FROM alert_hse_audit_master a
            LEFT JOIN alert_hse_audit_details d 
                ON a.hse_audit_id = d.hse_audit_id
            LEFT JOIN alert_location_master AS loc 
                ON a.location = loc.location_name
            INNER JOIN (
                {$latestAuditPerLocationSQL}
            ) latest
                ON latest.location = a.location
               AND latest.latest_audit_date = a.audit_date
            GROUP BY a.location
        ")->getResultArray();

        if (isClusterManager()) {
            $data['selected_region'] = getClusterManagerAssignedRegion();
            $data['selected_cluster'] = getClusterManagerAssignedCluster();
            $data['selected_location'] = '';
        } else {
            $data['selected_region'] = '';
            $data['selected_cluster'] = '';
            $data['selected_location'] = '';
        }

        return view("Customer/hse_dashboard", $data);
    }


    /* ========================= AJAX ENDPOINTS + NORMAL_FILTER ========================= */

    /**
     * AJAX: Return distinct regions linked to a specific year.
     * Logic: Fetch from dynamic table "alert_users_{year}" as requested.
     */
    public function get_regions_by_year()
    {
        $yearInput = $this->request->getVar('year');
        $db = db_connect();
        helper('designation_acl');

        $assignedRegions = null;
        if (isClusterManager() || isWHManager()) {
            $assignedRegions = getClusterManagerAssignedRegion();
        } elseif (isAccountManager()) {
            $assignedRegions = getAccountManagerAssignedRegion();
        }

        // Extract numeric year (e.g., '2024-2025' -> 2025, '2025' -> 2025)
        $yearNum = null;
        if (!empty($yearInput)) {
            if (is_numeric($yearInput)) {
                $yearNum = (int) $yearInput;
            } else if (strpos($yearInput, '-') !== false) {
                $yearNum = (int) substr($yearInput, -4);
            }
        }

        // ALWAYS use alert_client as primary source for Account Manager
        // This ensures assigned regions are always available
        if (isAccountManager()) {

            if (!empty($assignedRegions)) {

                $builder = $db->table('alert_client')
                    ->select('DISTINCT(region) AS region_name', false)
                    ->where('region IS NOT NULL')->where('status', 1)
                    ->where('region !=', '')
                    ->where('status', 1)
                    ->whereIn('region', (array) $assignedRegions);

                $rows = $builder
                    ->orderBy('region', 'ASC')
                    ->get()
                    ->getResultArray();

            } else {

                // Empty dropdown if no assigned region
                $rows = [];
            }

            return $this->response->setJSON($rows);
        }

        // For other roles: check year-specific tables
        if ($yearNum) {
            $tableName = "alert_users_" . $yearNum;

            // Check if user-specified year table exists
            if ($db->tableExists($tableName)) {
                $builder = $db->table($tableName)
                    ->select('DISTINCT(user_region) AS region_name', false)
                    ->where('user_region IS NOT NULL')
                    ->where('user_region !=', '');

                if (!empty($assignedRegions)) {
                    $builder->whereIn('user_region', (array) $assignedRegions);
                }

                $rows = $builder->orderBy('user_region', 'ASC')
                    ->get()
                    ->getResultArray();
            } else {
                // FALLBACK 1: Fetch from audit table if specific user year table doesn't exist
                $builder = $db->table('alert_final_structured_audit')
                    ->select('DISTINCT(region) AS region_name', false)
                    ->where('region IS NOT NULL')->where('status', 1)
                    ->where('region !=', '')
                    ->where('YEAR(audit_date)', $yearNum);

                if (!empty($assignedRegions)) {
                    $builder->whereIn('region', (array) $assignedRegions);
                }

                $rows = $builder->orderBy('region', 'ASC')
                    ->get()
                    ->getResultArray();
            }
        } else {
            // FALLBACK 2: No year provided (or 'All') — fetch from alert_client
            $builder = $db->table('alert_client')
                ->select('DISTINCT(region) AS region_name', false)
                ->where('region IS NOT NULL')->where('status', 1)
                ->where('region !=', '')
                ->where('status', 1);

            if (!empty($assignedRegions)) {
                $builder->whereIn('region', (array) $assignedRegions);
            }

            $rows = $builder->orderBy('region', 'ASC')
                ->get()
                ->getResultArray();
        }

        return $this->response->setJSON($rows);
    }

    public function get_clusters_by_region()
    {
        $region_name = $this->request->getVar('region_name');
        $yearInput = $this->request->getVar('year');

        // Handle JSON stringified input
        if (is_string($region_name) && strpos($region_name, '[') === 0) {
            $decoded = json_decode($region_name, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $region_name = $decoded;
            }
        }

        // Extract year number (use first selected year if multiple)
        $yearNum = null;
        if (!empty($yearInput)) {
            $yearVal = is_array($yearInput) ? reset($yearInput) : $yearInput;
            $yearNum = is_numeric($yearVal)
                ? (int) $yearVal
                : (int) substr($yearVal, -4);
        }

        helper('designation_acl');

        $userCluster = null;

        if (isClusterManager() || isWHManager()) {
            $userCluster = getClusterManagerAssignedCluster();
        } elseif (isAccountManager()) {
            $assignedDetails = getAccountManagerClientDetails();
            $userCluster = array_unique(
                array_filter(
                    array_column($assignedDetails ?? [], 'cluster')
                )
            );
        }

        $db = db_connect();

        if (empty($region_name)) {
            return $this->response->setJSON([]);
        }

        // ALWAYS use alert_client for Account Manager to ensure data availability
        if (isAccountManager()) {
            $builder = $db->table('alert_client')
                ->select('DISTINCT(cluster) AS cluster_name', false)
                ->where('status', 1);

            if (!empty($userCluster)) {
                $builder->whereIn('cluster', (array) $userCluster);
            }

            if (is_array($region_name)) {
                $rList = array_values(
                    array_filter($region_name, function ($v) {
                        return $v !== '';
                    })
                );
                if (!empty($rList)) {
                    $builder->whereIn('region', $rList);
                }
            } else {
                $builder->where('region', $region_name);
            }

            $builder->orderBy('cluster', 'ASC');
            $clusters = $builder->get()->getResultArray();
            return $this->response->setJSON($clusters);
        }

        // For other roles: check year-specific tables
        $tableName = ($yearNum) ? "alert_users_" . $yearNum : null;

        if ($tableName && $db->tableExists($tableName)) {
            $builder = $db->table($tableName)
                ->select('DISTINCT(user_cluster) AS cluster_name', false);

            if (is_array($region_name)) {
                $rList = array_values(
                    array_filter($region_name, function ($v) {
                        return $v !== '';
                    })
                );
                if (!empty($rList)) {
                    $builder->whereIn('user_region', $rList);
                }
            } else {
                $builder->where('user_region', $region_name);
            }

            if (!empty($userCluster)) {
                $builder->whereIn('user_cluster', (array) $userCluster);
            }

            $builder->orderBy('user_cluster', 'ASC');
        } else {
            // FALLBACK: Use alert_client
            $builder = $db->table('alert_client')
                ->select('DISTINCT(cluster) AS cluster_name', false)
                ->where('status', 1);

            if (!empty($userCluster)) {
                $builder->whereIn('cluster', (array) $userCluster);
            }

            if (is_array($region_name)) {
                $rList = array_values(
                    array_filter($region_name, function ($v) {
                        return $v !== '';
                    })
                );
                if (!empty($rList)) {
                    $builder->whereIn('region', $rList);
                }
            } else {
                $builder->where('region', $region_name);
            }

            $builder->orderBy('cluster', 'ASC');
        }

        $clusters = $builder->get()->getResultArray();
        return $this->response->setJSON($clusters);
    }



    public function get_locations_by_cluster()
    {
        $cluster_name = $this->request->getVar('cluster_name');
        $region_name = $this->request->getVar('region_name');
        $yearInput = $this->request->getVar('year');

        // Handle JSON strings
        if (is_string($cluster_name) && strpos($cluster_name, '[') === 0) {
            $decoded = json_decode($cluster_name, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $cluster_name = $decoded;
            }
        }

        if (is_string($region_name) && strpos($region_name, '[') === 0) {
            $decoded = json_decode($region_name, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $region_name = $decoded;
            }
        }

        // Extract year number (use first selected year if multiple)
        $yearNum = null;
        if (!empty($yearInput)) {
            $yearVal = is_array($yearInput) ? reset($yearInput) : $yearInput;
            $yearNum = is_numeric($yearVal)
                ? (int) $yearVal
                : (int) substr($yearVal, -4);
        }

        helper('designation_acl');

        $userCluster = null;
        $userLocations = null;

        if (isClusterManager() || isWHManager()) {
            $userCluster = getClusterManagerAssignedCluster();
        } elseif (isAccountManager()) {
            $assignedDetails = getAccountManagerClientDetails();
            $userCluster = array_unique(
                array_filter(
                    array_column($assignedDetails ?? [], 'cluster')
                )
            );
            $userLocations = array_unique(
                array_filter(
                    array_column($assignedDetails ?? [], 'client_name')
                )
            );
        }

        $db = db_connect();

        if (empty($cluster_name) && empty($region_name)) {
            return $this->response->setJSON([]);
        }

        // ALWAYS use alert_client for Account Manager to ensure data availability
        if (isAccountManager()) {
            $builder = $db->table('alert_client')
                ->select('DISTINCT(client_name) AS location_name', false)
                ->where('status', 1);

            if (!empty($userCluster)) {
                $builder->whereIn('cluster', $userCluster);
            }

            if (!empty($userLocations)) {
                $builder->whereIn('client_name', $userLocations);
            }

            if (is_array($cluster_name)) {
                $cList = array_values(
                    array_filter($cluster_name, function ($v) {
                        return $v !== '';
                    })
                );
                if (!empty($cList)) {
                    $builder->whereIn('cluster', $cList);
                }
            } else {
                $builder->where('cluster', $cluster_name);
            }

            if (!empty($region_name)) {
                if (is_array($region_name)) {
                    $rList = array_values(
                        array_filter($region_name, function ($v) {
                            return $v !== '';
                        })
                    );
                    if (!empty($rList)) {
                        $builder->whereIn('region', $rList);
                    }
                } else {
                    $builder->where('region', $region_name);
                }
            }

            $builder->orderBy('client_name', 'ASC');
            $locations = $builder->get()->getResultArray();
            return $this->response->setJSON($locations);
        }

        // For other roles: check year-specific tables
        $tableName = ($yearNum) ? "alert_users_" . $yearNum : null;

        if ($tableName && $db->tableExists($tableName)) {
            $builder = $db->table($tableName)
                ->select('DISTINCT(user_location) AS location_name', false);

            if (!empty($userCluster)) {
                $builder->whereIn('user_cluster', $userCluster);
            }

            if (!empty($userLocations)) {
                $builder->whereIn('user_location', $userLocations);
            }

            if (is_array($cluster_name)) {
                $cList = array_values(
                    array_filter($cluster_name, function ($v) {
                        return $v !== '';
                    })
                );
                if (!empty($cList)) {
                    $builder->whereIn('user_cluster', $cList);
                }
            } else {
                $builder->where('user_cluster', $cluster_name);
            }

            if (!empty($region_name)) {
                if (is_array($region_name)) {
                    $rList = array_values(
                        array_filter($region_name, function ($v) {
                            return $v !== '';
                        })
                    );

                    if (!empty($rList)) {
                        $builder->whereIn('user_region', $rList);
                    }

                } else {

                    $builder->where('user_region', $region_name);
                }
            }

            $builder->orderBy('user_location', 'ASC');

        } else {

            /*
            |--------------------------------------------------------------------------
            | FALLBACK TABLE
            |--------------------------------------------------------------------------
            */
            $builder = $db->table('alert_client')
                ->select('DISTINCT(client_name) AS location_name', false)
                ->where('status', 1);

            if (!empty($userCluster)) {
                $builder->whereIn('cluster', $userCluster);
            }

            if (!empty($userLocations)) {
                $builder->whereIn('client_name', $userLocations);
            }

            if (is_array($cluster_name)) {

                $cList = array_values(
                    array_filter($cluster_name, function ($v) {
                        return $v !== '';
                    })
                );

                if (!empty($cList)) {
                    $builder->whereIn('cluster', $cList);
                }

            } else {

                $builder->where('cluster', $cluster_name);
            }

            if (!empty($region_name)) {

                if (is_array($region_name)) {

                    $rList = array_values(
                        array_filter($region_name, function ($v) {
                            return $v !== '';
                        })
                    );

                    if (!empty($rList)) {
                        $builder->whereIn('region', $rList);
                    }

                } else {

                    $builder->where('region', $region_name);
                }
            }

            $builder->orderBy('client_name', 'ASC');
        }

        $locations = $builder->get()->getResultArray();

        return $this->response->setJSON($locations);
    }

    public function Normal_filter()
    {
        $db = db_connect();
        $postData = $this->request->getVar();

        $data['country'] = $db->table("alert_country")->get()->getResultArray();

        // Dynamic regions from alert_client
        $data['dynamic_regions'] = $this->getDynamicRegions($db);
        $data['region_color_map'] = $this->getRegionColorMap($data['dynamic_regions']);

        // Region list from alert_client (dynamic)
        $data['region'] = $db->table('alert_client')
            ->select('DISTINCT(region) AS region_name', false)
            ->where('region IS NOT NULL')->where('status', 1)
            ->where('region !=', '')
            ->where('status', 1)
            ->orderBy('region', 'ASC')
            ->get()
            ->getResultArray();

        // Cluster & location lock logic for Normal filter
        $data['cluster'] = [];
        $data['location'] = [];

        if (!empty($postData['region'])) {
            $data['cluster'] = $db->table('alert_location_master')
                ->select('DISTINCT(cluster_name) AS cluster_name', false)
                ->where('region_name', $postData['region'])
                ->where('status', 1)
                ->get()
                ->getResultArray();

            $data['lock_cluster'] = false;

            if (!empty($postData['cluster_name'])) {
                $data['location'] = $db->table('alert_location_master')
                    ->select('DISTINCT(location_name) AS location_name', false)
                    ->where('region_name', $postData['region'])
                    ->where('cluster_name', $postData['cluster_name'])
                    ->where('status', 1)
                    ->get()
                    ->getResultArray();

                $data['lock_location'] = false;
            } else {
                $data['lock_location'] = true;  // cluster not chosen
            }
        } else {
            $data['lock_cluster'] = true;
            $data['lock_location'] = true;
        }

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
            '03' => 'March'
        ];
        $currentYear = date('Y');
        $financialYears = [];
        for ($i = 0; $i < 5; $i++) {
            $startYear = ($currentYear - $i) - 1;
            $endYear = $currentYear - $i;
            $financialYears[] = "$startYear-$endYear";
        }
        $data['financialYears'] = $financialYears;

        /* ---------- Normal Pie Chart (filter) ---------- */
        $nfWhere = [];
        $nfParams = [];
        $nfWhereSql = " WHERE audit.region IS NOT NULL AND audit.region <> ''";

        $regFilter = $postData['region'] ?? '';
        $clsFilter = $postData['cluster_name'] ?? '';

        if (!empty($regFilter)) {
            if (is_string($regFilter) && strpos($regFilter, ',') !== false) {
                $regFilter = explode(',', $regFilter);
            }
            if (is_array($regFilter)) {
                $nfWhere[] = "audit.region IN (" . implode(',', array_map([$db, 'escape'], $regFilter)) . ")";
            } else {
                $nfWhere[] = 'audit.region = ?';
                $nfParams[] = $regFilter;
            }
        }

        if (!empty($clsFilter)) {
            if (is_string($clsFilter) && strpos($clsFilter, ',') !== false) {
                $clsFilter = explode(',', $clsFilter);
            }
            if (is_array($clsFilter)) {
                $nfWhere[] = "loc.cluster_name IN (" . implode(',', array_map([$db, 'escape'], $clsFilter)) . ")";
            } else {
                $nfWhere[] = 'loc.cluster_name = ?';
                $nfParams[] = $clsFilter;
            }
        }

        if (!empty($postData['location_name'])) {
            $nfWhere[] = 'audit.location = ?';
            $nfParams[] = $postData['location_name'];
        }
        helper('designation_acl');
        $aclFilterDashboard = getOEAuditACLWhere('audit', 'Normal');
        if (!empty($aclFilterDashboard)) {
            $nfWhere[] = preg_replace('/^\s*AND\s*/i', '', $aclFilterDashboard);
        }
        if ($nfWhere) {
            $nfWhereSql .= ' AND ' . implode(' AND ', $nfWhere);
        }

        $nfPieSql = "SELECT 
                        audit.region, 
                        ROUND(IFNULL((SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) 
                              / NULLIF(SUM(weightage), 0)) * 100, 0), 2) AS score,
                        COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS openpoints 
                    FROM alert_normal_audit audit 
                    LEFT JOIN alert_normal_audit_details audit_details 
                        ON audit.normal_audit_id = audit_details.normal_audit_id 
                    LEFT JOIN alert_location_master AS loc 
                        ON audit.location = loc.location_name" . $nfWhereSql . "
                    GROUP BY audit.region";
        $pie_chart = $db->query($nfPieSql, $nfParams)->getResultArray();
        if (empty($pie_chart)) {
            $pie_chart = $db->query("
                SELECT 
                    audit.region, 
                    ROUND(IFNULL((SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) 
                          / NULLIF(SUM(weightage), 0)) * 100, 0), 2) AS score,
                    COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS openpoints 
                FROM alert_normal_audit audit 
                LEFT JOIN alert_normal_audit_details audit_details 
                    ON audit.normal_audit_id = audit_details.normal_audit_id 
                WHERE audit.region IS NOT NULL AND audit.region <> ''
                GROUP BY audit.region
            ")->getResultArray();
        }
        $data['pie_chart'] = $pie_chart;

        $data['category_normal'] = $db->table("alert_normal_audit_excel_import")
            ->select("category")
            ->groupby("category")
            ->get()->getResultArray();

        /* ---------- Normal Open point report (filter) ---------- */
        $oprWhere = [];
        $oprParams = [];
        if (!empty($regFilter)) {
            if (is_array($regFilter)) {
                $oprWhere[] = "audit.region IN (" . implode(',', array_map([$db, 'escape'], $regFilter)) . ")";
            } else {
                $oprWhere[] = 'audit.region = ?';
                $oprParams[] = $regFilter;
            }
        }
        if (!empty($clsFilter)) {
            if (is_array($clsFilter)) {
                $oprWhere[] = "loc.cluster_name IN (" . implode(',', array_map([$db, 'escape'], $clsFilter)) . ")";
            } else {
                $oprWhere[] = 'loc.cluster_name = ?';
                $oprParams[] = $clsFilter;
            }
        }
        if (!empty($postData['location_name'])) {
            $oprWhere[] = 'audit.location = ?';
            $oprParams[] = $postData['location_name'];
        }
        if (!empty($aclFilterDashboard)) {
            $oprWhere[] = preg_replace('/^\s*AND\s*/i', '', $aclFilterDashboard);
        }

        $oprSql = "SELECT audit.*, audit.normal_audit_id, audit.location, audit.region,
                          loc.cluster_name, category, audit_parameter, audit_remark
                    FROM alert_normal_audit audit
                    LEFT JOIN alert_normal_audit_details audit_details 
                        ON audit.normal_audit_id = audit_details.normal_audit_id
                    LEFT JOIN alert_location_master AS loc 
                        ON audit.location = loc.location_name";
        if ($oprWhere)
            $oprSql .= ' WHERE ' . implode(' AND ', $oprWhere);
        $data['open_point_report'] = $db->query($oprSql, $oprParams)->getResultArray();

        /* ---------- Normal Aging (filter) ---------- */
        $anWhere = [];
        $anParams = [];
        if (!empty($regFilter)) {
            if (is_array($regFilter)) {
                $anWhere[] = "audit.region IN (" . implode(',', array_map([$db, 'escape'], $regFilter)) . ")";
            } else {
                $anWhere[] = 'audit.region = ?';
                $anParams[] = $regFilter;
            }
        }
        if (!empty($clsFilter)) {
            if (is_array($clsFilter)) {
                $anWhere[] = "loc.cluster_name IN (" . implode(',', array_map([$db, 'escape'], $clsFilter)) . ")";
            } else {
                $anWhere[] = 'loc.cluster_name = ?';
                $anParams[] = $clsFilter;
            }
        }
        if (!empty($postData['location_name'])) {
            $anWhere[] = 'audit.location = ?';
            $anParams[] = $postData['location_name'];
        }
        if (!empty($aclFilterDashboard)) {
            $anWhere[] = preg_replace('/^\s*AND\s*/i', '', $aclFilterDashboard);
        }

        $agingSql = "SELECT audit.normal_audit_id, audit_no, audit_name, audit.region, category, 
                            COUNT(audit_finding) AS openpoints
                     FROM alert_normal_audit audit
                     LEFT JOIN alert_normal_audit_details audit_details 
                        ON audit.normal_audit_id = audit_details.normal_audit_id AND audit_finding = 'NO'
                     LEFT JOIN alert_location_master AS loc 
                        ON audit.location = loc.location_name";
        if ($anWhere)
            $agingSql .= ' WHERE ' . implode(' AND ', $anWhere);
        $agingSql .= " GROUP BY audit.normal_audit_id, category";

        $data['aging_normal_score'] = $db->query($agingSql, $anParams)->getResultArray();

        /* ---------- Normal Month Summary (LATEST normal audit per location+month+year, filter) ---------- */
        $nyWhere = [];
        if (!empty($regFilter)) {
            if (is_array($regFilter)) {
                $nyWhere[] = "audit.region IN (" . implode(',', array_map([$db, 'escape'], $regFilter)) . ")";
            } else {
                $nyWhere[] = "audit.region = " . $db->escape($regFilter);
            }
        }
        if (!empty($clsFilter)) {
            if (is_array($clsFilter)) {
                $nyWhere[] = "loc.cluster_name IN (" . implode(',', array_map([$db, 'escape'], $clsFilter)) . ")";
            } else {
                $nyWhere[] = "loc.cluster_name = " . $db->escape($clsFilter);
            }
        }
        if (!empty($postData['location_name']))
            $nyWhere[] = "audit.location = " . $db->escape($postData['location_name']);
        if (!empty($aclFilterDashboard)) {
            $nyWhere[] = preg_replace('/^\s*AND\s*/i', '', $aclFilterDashboard);
        }
        $nyWhereSql = '';
        if ($nyWhere)
            $nyWhereSql = ' AND ' . implode(' AND ', $nyWhere);

        $data['normal_year_score_query'] = $db->query("
            SELECT 
                b.location,
                b.region,
                b.cluster_name,

                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(d.weightage), 0)) * 100,
                    0), 2
                ) AS avg_score_percentage,

                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 4 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 4 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Apr_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 5 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 5 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS May_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 6 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 6 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Jun_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 7 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 7 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Jul_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 8 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 8 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Aug_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 9 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 9 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Sep_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 10 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 10 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Oct_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 11 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 11 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Nov_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 12 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 12 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Dec_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 1 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 1 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Jan_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 2 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 2 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Feb_Score,
                ROUND(
                    IFNULL(
                        (SUM(CASE WHEN d.audit_finding = 'YES' AND b.audit_month = 3 THEN d.weightage ELSE 0 END) /
                         NULLIF(SUM(CASE WHEN b.audit_month = 3 THEN d.weightage ELSE 0 END), 0)) * 100,
                    0), 2
                ) AS Mar_Score

            FROM (
                SELECT 
                    audit.normal_audit_id,
                    audit.location,
                    audit.region,
                    loc.cluster_name,
                    MONTH(audit.audit_date) AS audit_month,
                    YEAR(audit.audit_date)  AS audit_year,
                    audit.audit_date
                FROM alert_normal_audit AS audit
                LEFT JOIN alert_location_master AS loc 
                    ON audit.location = loc.location_name
                WHERE 1=1 {$nyWhereSql}
            ) b
            JOIN (
                SELECT 
                    audit.location,
                    audit.region,
                    loc.cluster_name,
                    MONTH(audit.audit_date) AS audit_month,
                    YEAR(audit.audit_date)  AS audit_year,
                    MAX(audit.audit_date)   AS max_audit_date
                FROM alert_normal_audit AS audit
                LEFT JOIN alert_location_master AS loc 
                    ON audit.location = loc.location_name
                WHERE 1=1 {$nyWhereSql}
                GROUP BY audit.location, audit.region, loc.cluster_name,
                         YEAR(audit.audit_date), MONTH(audit.audit_date)
            ) latest
                ON b.location      = latest.location
               AND b.region        = latest.region
               AND b.cluster_name  = latest.cluster_name
               AND b.audit_year    = latest.audit_year
               AND b.audit_month   = latest.audit_month
               AND b.audit_date    = latest.max_audit_date
            LEFT JOIN alert_normal_audit_details d 
                ON b.normal_audit_id = d.normal_audit_id
            GROUP BY b.location, b.region, b.cluster_name
        ")->getResultArray();

        /* ---------- Upcoming Normal (filter) ---------- */
        $uaWhere = [];
        $uaParams = [];
        if (!empty($postData['region'])) {
            $uaWhere[] = 'region = ?';
            $uaParams[] = $postData['region'];
        }
        if (!empty($postData['location_name'])) {
            $uaWhere[] = 'location = ?';
            $uaParams[] = $postData['location_name'];
        }

        $uaSql = "SELECT * FROM alert_normal_audit WHERE next_date >= CURDATE()";
        if ($uaWhere)
            $uaSql .= ' AND ' . implode(' AND ', $uaWhere);
            
        helper('designation_acl');
        $aclFilterUpcoming = getOEAuditACLWhere('alert_normal_audit', 'Normal');
        $uaSql .= $aclFilterUpcoming;
        
        $data['Normal_audit'] = $db->query($uaSql, $uaParams)->getResultArray();

        /* ---------- Openpoint Summary (filter) ---------- */
        $opsWhere = [];
        $opsParams = [];
        if (!empty($postData['region'])) {
            $opsWhere[] = 'audit.region = ?';
            $opsParams[] = $postData['region'];
        }
        if (!empty($postData['cluster_name'])) {
            $opsWhere[] = 'loc.cluster_name = ?';
            $opsParams[] = $postData['cluster_name'];
        }
        if (!empty($postData['location_name'])) {
            $opsWhere[] = 'audit.location = ?';
            $opsParams[] = $postData['location_name'];
        }

        helper('designation_acl');
        $aclFilterOpenpoint = getOEAuditACLWhere('audit', 'Normal');
        if (!empty($aclFilterOpenpoint)) {
            $opsWhere[] = preg_replace('/^\s*AND\s*/i', '', $aclFilterOpenpoint);
        }

        $opsSql = "SELECT 
                        audit.location, 
                        audit.region,
                        loc.cluster_name,
                        ROUND(
                            IFNULL(
                                (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) /
                                NULLIF(SUM(weightage), 0)) * 100,
                            0), 2) AS avg_score_percentage,
                        COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                        GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
                    FROM alert_normal_audit audit
                    LEFT JOIN alert_normal_audit_details audit_details 
                        ON audit.normal_audit_id = audit_details.normal_audit_id
                    LEFT JOIN alert_location_master AS loc 
                        ON audit.location = loc.location_name";
        if ($opsWhere)
            $opsSql .= ' WHERE ' . implode(' AND ', $opsWhere);
        $opsSql .= " GROUP BY audit.location";

        $data['total_Normal_score_openpoints'] = $db->query($opsSql, $opsParams)->getResultArray();

        $data['selected_region'] = $postData['region'] ?? '';
        $data['selected_cluster'] = $postData['cluster_name'] ?? '';
        $data['selected_location'] = $postData['location_name'] ?? '';

        return view("Customer/normal_dashboard", $data);
    }

    /* ========================= HELPER: DYNAMIC REGIONS ========================= */

    /**
     * Fetch distinct region names from alert_client table.
     * Returns a simple indexed array of unique region strings.
     */
    private function getDynamicRegions($db)
    {
        $rows = $db->table('alert_client')
            ->select('DISTINCT(region) AS region', false)
            ->where('region IS NOT NULL')->where('status', 1)
            ->where('region !=', '')
            ->where('status', 1)
            ->orderBy('region', 'ASC')
            ->get()
            ->getResultArray();

        return array_column($rows, 'region');
    }

    /**
     * Generate a color map keyed by region name (case-insensitive).
     * Uses a curated color palette so each region gets a distinct color.
     * Returns an associative array: e.g. ['North' => '#808080', 'north' => '#808080', ...]
     */
    private function getRegionColorMap(array $regions)
    {
        // Curated palette of distinct, chart-friendly colors
        $palette = [
            '#D2691E', // brown/chocolate
            '#808080', // grey
            '#FFCE56', // gold/yellow
            '#FF6384', // pink/red
            '#36A2EB', // blue
            '#4BC0C0', // teal
            '#9966FF', // purple
            '#FF9F40', // orange
            '#C9CBCF', // silver
            '#7BC225', // green
            '#E7544B', // coral
            '#8B4513', // saddle brown
        ];

        $colorMap = [];
        $index = 0;
        foreach ($regions as $region) {
            $color = $palette[$index % count($palette)];
            // Map both original case and uppercase for flexible JS matching
            $colorMap[$region] = $color;
            $colorMap[strtoupper($region)] = $color;
            $colorMap[ucfirst(strtolower($region))] = $color;
            $index++;
        }

        return $colorMap;
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

    public function Normal_filter_ajax()
    {
        $db = db_connect();
        $request = service('request');
        $postData = $request->getPost();

        // 1. Base Where Clause
        $whereSql = " WHERE audit.region IS NOT NULL AND audit.region <> '' ";

        // ACL
        helper('designation_acl');
        $aclFilter = getOEAuditACLWhere('audit', 'Normal');
        if (!empty($aclFilter)) {
            $whereSql .= " AND " . preg_replace('/^\s*AND\s*/i', '', $aclFilter);
        }

        // Region
        if (!empty($postData['region']) && $postData['region'] !== 'All') {
            $regions = is_array($postData['region']) ? $postData['region'] : explode(',', $postData['region']);
            $whereSql .= " AND audit.region IN (" . implode(',', array_map([$db, 'escape'], $regions)) . ") ";
        }

        // Cluster
        if (!empty($postData['cluster']) && $postData['cluster'] !== 'All') {
            $clusters = is_array($postData['cluster']) ? $postData['cluster'] : explode(',', $postData['cluster']);
            $whereSql .= " AND audit.cluster_name IN (" . implode(',', array_map([$db, 'escape'], $clusters)) . ") ";
        }

        // Location
        if (!empty($postData['location']) && $postData['location'] !== 'All') {
            $locations = is_array($postData['location']) ? $postData['location'] : explode(',', $postData['location']);
            $whereSql .= " AND audit.location IN (" . implode(',', array_map([$db, 'escape'], $locations)) . ") ";
        }

        // Year
        if (!empty($postData['year']) && $postData['year'] !== 'All') {
            $years = is_array($postData['year']) ? $postData['year'] : explode(',', $postData['year']);
            $whereSql .= " AND YEAR(audit.audit_date) IN (" . implode(',', array_map([$db, 'escape'], $years)) . ") ";
        }

        // Month
        if (!empty($postData['month']) && $postData['month'] !== 'All') {
            $months = is_array($postData['month']) ? $postData['month'] : explode(',', $postData['month']);
            $whereSql .= " AND MONTH(audit.audit_date) IN (" . implode(',', array_map([$db, 'escape'], $months)) . ") ";
        }

        // Audit Type
        if (!empty($postData['audit_type']) && $postData['audit_type'] !== 'All') {
            $types = is_array($postData['audit_type']) ? $postData['audit_type'] : explode(',', $postData['audit_type']);
            $typeConditions = [];
            foreach ($types as $type) {
                $escapedType = $db->escape($type);
                $escapedReAudit = $db->escape($type . " Re-Audit");
                $typeConditions[] = "audit.audit_name = $escapedType OR audit.audit_name = $escapedReAudit";
            }
            $whereSql .= " AND (" . implode(" OR ", $typeConditions) . ") ";
        }

        // ==========================================
        // KPIs
        // ==========================================
        
        $kpiQuery = "SELECT 
                        COUNT(DISTINCT audit.normal_audit_id) as total_audits,
                        COUNT(DISTINCT CASE WHEN audit.audit_name NOT LIKE '%Re-Audit%' THEN audit.normal_audit_id END) as perform_audits,
                        COUNT(DISTINCT CASE WHEN audit.audit_name LIKE '%Re-Audit%' THEN audit.normal_audit_id END) as re_audits,
                        COUNT(DISTINCT audit.location) as total_locations
                     FROM alert_normal_audit audit
                     " . $whereSql . " AND audit.reaudit = '0'";
        $kpis = $db->query($kpiQuery)->getRowArray();
        if (!$kpis) $kpis = [];

        $pointsQuery = "SELECT 
                            SUM(CASE WHEN det.audit_finding = 'NO' AND det.status = '0' AND audit.reaudit = '0' THEN 1 ELSE 0 END) as open_points,
                            SUM(CASE WHEN det.audit_finding = 'NO' AND det.status IN ('1','4') AND audit.reaudit = '0' THEN 1 ELSE 0 END) as working_points,
                            SUM(CASE WHEN det.audit_finding = 'NO' AND det.status = '6' AND audit.reaudit = '0' THEN 1 ELSE 0 END) as review_cm_points,
                            SUM(CASE WHEN det.audit_finding = 'NO' AND det.status = '2' AND audit.reaudit = '0' THEN 1 ELSE 0 END) as review_auditor_points,
                            SUM(CASE WHEN det.audit_finding = 'NO' AND det.status IN ('3','5') AND audit.reaudit = '0' THEN 1 ELSE 0 END) as closed_points,
                            SUM(CASE WHEN det.audit_finding = 'NO' AND audit.reaudit = '0' THEN 1 ELSE 0 END) as total_nc_points
                        FROM alert_normal_audit audit
                        JOIN alert_normal_audit_details det ON audit.normal_audit_id = det.normal_audit_id
                        " . $whereSql;
        $points = $db->query($pointsQuery)->getRowArray();
        if (!$points) $points = [];
        
        $kpiScoreQuery = "SELECT ROUND(IFNULL((SUM(CASE WHEN det.audit_finding IN ('YES','NA') THEN det.weightage ELSE 0 END) / NULLIF(SUM(det.weightage), 0)) * 100, 0), 2) as avg_score
                        FROM alert_normal_audit a
                        INNER JOIN (
                            SELECT location, MAX(normal_audit_id) AS latest_id
                            FROM alert_normal_audit
                            WHERE reaudit = '0'
                            GROUP BY location
                        ) latest ON a.normal_audit_id = latest.latest_id
                        LEFT JOIN alert_normal_audit_details det ON a.normal_audit_id = det.normal_audit_id
                        " . str_replace('audit.', 'a.', $whereSql);
        $points['avg_score'] = $db->query($kpiScoreQuery)->getRowArray()['avg_score'] ?? 0;

        // ==========================================
        // Charts
        // ==========================================

        // 1. Dynamic Score Overview based on Region/Cluster/Account filter
        $isLocationFiltered = !empty($postData['location']) && $postData['location'] !== 'All';
        $isClusterFiltered  = !empty($postData['cluster']) && $postData['cluster'] !== 'All';
        $isRegionFiltered   = !empty($postData['region']) && $postData['region'] !== 'All';

        if ($isLocationFiltered) {
            $pieGroupCol   = 'a.location';
            $pieGroupAlias = 'a.location AS label';
            $pieChartTitle = 'Normal Score by Account Name';
        } elseif ($isClusterFiltered) {
            $pieGroupCol   = 'a.location';
            $pieGroupAlias = 'a.location AS label';
            $pieChartTitle = 'Normal Score by Account Name';
        } elseif ($isRegionFiltered) {
            $pieGroupCol   = 'a.cluster_name';
            $pieGroupAlias = 'a.cluster_name AS label';
            $pieChartTitle = 'Normal Score by Cluster';
        } else {
            $pieGroupCol   = 'a.region';
            $pieGroupAlias = 'a.region AS label';
            $pieChartTitle = 'Normal Score by Region';
        }

        $scoreQuery = "
            SELECT 
                label,
                ROUND(AVG(score), 2) AS score
            FROM (
                SELECT 
                    {$pieGroupAlias},
                    ROUND(IFNULL((SUM(CASE WHEN det.audit_finding IN ('YES','NA') THEN det.weightage ELSE 0 END) / NULLIF(SUM(det.weightage), 0)) * 100, 0), 2) as score
                FROM alert_normal_audit a
                INNER JOIN (
                    SELECT location, MAX(normal_audit_id) AS latest_id
                    FROM alert_normal_audit
                    WHERE reaudit = '0'
                    GROUP BY location
                ) latest ON a.normal_audit_id = latest.latest_id
                LEFT JOIN alert_normal_audit_details det ON a.normal_audit_id = det.normal_audit_id
                " . str_replace('audit.', 'a.', $whereSql) . "
                GROUP BY a.normal_audit_id
            ) AS final_scores
            GROUP BY label
            ORDER BY score DESC
        ";
        
        $score_region = $db->query($scoreQuery)->getResultArray();

        // 2. Open Points by Region
        $regionPointsQuery = "SELECT 
                                audit.region,
                                SUM(CASE WHEN det.audit_finding = 'NO' THEN 1 ELSE 0 END) as open_count
                              FROM alert_normal_audit audit
                              JOIN alert_normal_audit_details det ON audit.normal_audit_id = det.normal_audit_id
                              " . $whereSql . "
                              GROUP BY audit.region
                              ORDER BY open_count DESC";
        $region_points = $db->query($regionPointsQuery)->getResultArray();

        // 3. Trend
        $trendQuery = "SELECT 
                            MONTH(audit.audit_date) as mth,
                            COUNT(DISTINCT CASE WHEN audit.audit_name NOT LIKE '%Re-Audit%' THEN audit.normal_audit_id END) as perform_count,
                            COUNT(DISTINCT CASE WHEN audit.audit_name LIKE '%Re-Audit%' THEN audit.normal_audit_id END) as re_count
                       FROM alert_normal_audit audit
                       " . $whereSql . "
                       GROUP BY MONTH(audit.audit_date)
                       ORDER BY MONTH(audit.audit_date)";
        $trend = $db->query($trendQuery)->getResultArray();

        // 4. Risk Priority Distribution (Open Points)
        $riskQuery = "SELECT 
                        det.risk_priority,
                        COUNT(det.audit_details_id) as count
                      FROM alert_normal_audit audit
                      JOIN alert_normal_audit_details det ON audit.normal_audit_id = det.normal_audit_id
                      " . $whereSql . " AND det.audit_finding = 'NO'
                      GROUP BY det.risk_priority";
        $risk_dist = $db->query($riskQuery)->getResultArray();

        // 5. Audit Type Distribution
        $typeQuery = "SELECT 
                        audit.audit_name,
                        COUNT(DISTINCT audit.normal_audit_id) as count
                      FROM alert_normal_audit audit
                      " . $whereSql . "
                      GROUP BY audit.audit_name";
        $type_dist = $db->query($typeQuery)->getResultArray();

        // ==========================================
        // Tables
        // ==========================================

        // Recent Audits
        $recentQuery = "SELECT 
                            audit.audit_no,
                            audit.audit_name as audit_type,
                            audit.audit_date,
                            audit.location as account_name,
                            audit.region,
                            audit.cluster_name as cluster,
                            audit.auditor_name as auditor,
                            ROUND(IFNULL((SUM(CASE WHEN det.audit_finding = 'YES' THEN det.weightage ELSE 0 END) / NULLIF(SUM(det.weightage), 0)) * 100, 0), 2) as score,
                            SUM(CASE WHEN det.audit_finding = 'NO' THEN 1 ELSE 0 END) as open_points,
                            SUM(CASE WHEN det.audit_finding = 'YES' THEN 1 ELSE 0 END) as closed_points,
                            (SELECT MAX(status) FROM alert_normal_audit_details ad WHERE ad.normal_audit_id = audit.normal_audit_id) as status
                        FROM alert_normal_audit audit
                        LEFT JOIN alert_normal_audit_details det ON audit.normal_audit_id = det.normal_audit_id
                        " . $whereSql . "
                        GROUP BY audit.normal_audit_id
                        ORDER BY audit.audit_date DESC LIMIT 500";
        $recent_audits = $db->query($recentQuery)->getResultArray();

        // Open Points
        $openPointsTableQuery = "SELECT 
                                    audit.audit_no,
                                    audit.location as account_name,
                                    audit.region,
                                    audit.cluster_name as cluster,
                                    audit.audit_date,
                                    det.category,
                                    det.audit_question as sub_category,
                                    det.risk_priority,
                                    det.audit_remark as description,
                                    det.default_date as target_date,
                                    det.nc_worked_by,
                                    det.status
                                 FROM alert_normal_audit audit
                                 JOIN alert_normal_audit_details det ON audit.normal_audit_id = det.normal_audit_id
                                 " . $whereSql . " AND det.audit_finding = 'NO'
                                 AND audit.reaudit = '0'
                                 ORDER BY audit.audit_date DESC LIMIT 1000";
        $open_points_table = $db->query($openPointsTableQuery)->getResultArray();

        // Aging
        $agingQuery = "SELECT 
                            audit.audit_no,
                            audit.location as account_name,
                            audit.region,
                            audit.cluster_name as cluster,
                            audit.audit_date,
                            DATEDIFF(CURDATE(), audit.audit_date) as days_open
                       FROM alert_normal_audit audit
                       JOIN alert_normal_audit_details det ON audit.normal_audit_id = det.normal_audit_id
                       " . $whereSql . " AND det.audit_finding = 'NO' 
                       AND det.status = '0' AND audit.reaudit = '0'";
        $agingData = $db->query($agingQuery)->getResultArray();
        
        $agingTable = [];
        foreach ($agingData as $r) {
            $days = (int)$r['days_open'];
            $bucket = '';
            if ($days <= 30) $bucket = '0-30';
            else if ($days <= 60) $bucket = '31-60';
            else if ($days <= 90) $bucket = '61-90';
            else if ($days <= 120) $bucket = '91-120';
            else if ($days <= 150) $bucket = '121-150';
            else if ($days <= 180) $bucket = '151-180';
            else $bucket = 'Above 180';
            
            $key = $r['audit_no'];
            if (!isset($agingTable[$key])) {
                $agingTable[$key] = [
                    'audit_no' => $r['audit_no'],
                    'region' => $r['region'],
                    'cluster' => $r['cluster'],
                    'account_name' => $r['account_name'],
                    'audit_date' => $r['audit_date'],
                    'aging_days' => $days,
                    '0-30' => 0, '31-60' => 0, '61-90' => 0, '91-120' => 0, '121-150' => 0, '151-180' => 0, 'Above 180' => 0,
                    'total' => 0
                ];
            }
            $agingTable[$key][$bucket]++;
            $agingTable[$key]['total']++;
        }

        // Monthly Summary
        $monthlyQuery = "SELECT 
                            a.location as account_name,
                            a.region,
                            a.cluster_name as cluster,
                            MONTH(a.audit_date) as month_val,
                            ROUND(IFNULL((SUM(CASE WHEN det.audit_finding IN ('YES','NA') THEN det.weightage ELSE 0 END) / NULLIF(SUM(det.weightage), 0)) * 100, 0), 2) as score
                         FROM alert_normal_audit a
                         INNER JOIN (
                             SELECT location, MONTH(audit_date) as m_val, MAX(normal_audit_id) AS latest_id
                             FROM alert_normal_audit
                             WHERE reaudit = '0'
                             GROUP BY location, MONTH(audit_date)
                         ) latest ON a.normal_audit_id = latest.latest_id
                         LEFT JOIN alert_normal_audit_details det ON a.normal_audit_id = det.normal_audit_id
                         " . str_replace('audit.', 'a.', $whereSql) . "
                         GROUP BY a.location, a.region, a.cluster_name, MONTH(a.audit_date)";
        $monthlyData = $db->query($monthlyQuery)->getResultArray();
        
        $monthlyTable = [];
        foreach ($monthlyData as $r) {
            $key = $r['account_name'] . '|' . $r['region'] . '|' . $r['cluster'];
            if (!isset($monthlyTable[$key])) {
                $monthlyTable[$key] = [
                    'account_name' => $r['account_name'],
                    'region' => $r['region'],
                    'cluster' => $r['cluster']
                ];
                for ($m=1; $m<=12; $m++) $monthlyTable[$key]['m'.$m] = 0;
            }
            $monthlyTable[$key]['m'.$r['month_val']] = $r['score'] . '%';
        }

        // Upcoming Audits
        // If there's an explicit "upcoming" table or we just look for audits without details yet or future dates
        // Looking at the legacy code, maybe from alert_normal_audit_details next_date? Or alert_normal_audit next_date?
        $upcomingQuery = "SELECT 
                            audit.audit_no,
                            audit.audit_name,
                            audit.location as account_name,
                            audit.next_date as audit_date,
                            audit.auditor_name as auditor
                          FROM alert_normal_audit audit
                          " . $whereSql . " AND audit.next_date >= CURDATE() AND audit.reaudit = '0'
                          ORDER BY audit.next_date ASC LIMIT 100";
        $upcoming_audits = $db->query($upcomingQuery)->getResultArray();

        // Audit Overdue
        $overdueQuery = "SELECT 
                            audit.audit_no,
                            audit.audit_name,
                            audit.location as account_name,
                            audit.next_date as audit_date,
                            audit.auditor_name as auditor,
                            DATEDIFF(CURDATE(), audit.next_date) as days_overdue
                          FROM alert_normal_audit audit
                          " . $whereSql . " AND audit.next_date < CURDATE()
                          ORDER BY audit.next_date DESC LIMIT 100";
        $overdue_audits = $db->query($overdueQuery)->getResultArray();

        return $this->response->setJSON([
            'status' => 1,
            'kpis' => array_merge($kpis ?? [], $points ?? []),
            'charts' => [
                'score_region' => $score_region ?? [],
                'pie_chart_title' => $pieChartTitle ?? 'Audit Score Overview',
                'region_points' => $region_points,
                'trend' => $trend,
                'risk_dist' => $risk_dist,
                'type_dist' => $type_dist
            ],
            'tables' => [
                'recent_audits' => $recent_audits,
                'open_points' => $open_points_table,
                'aging' => array_values($agingTable),
                'monthly' => array_values($monthlyTable),
                'upcoming' => $upcoming_audits,
                'overdue' => $overdue_audits
            ]
        ]);
    }
}
