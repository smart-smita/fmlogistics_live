<?php

namespace App\Controllers;
use App\Controllers\BaseController;

class Structure_audit_dashboard extends BaseController
{
    public function __construct(){
        // changes on 14/11/25 by darsh: Using designation ACL helper for cluster-based filtering
        helper(['designation_acl']);
    }
    
    public function index(){
        $db = db_connect();

        $tdata['title'] = "Upcoming Audits";
        // $tdata['button_id'] = "Lmra_controle_modal"; // NEW CHANGE: removed add button for Upcoming Audits
        $tdata['display_contents'] = [
            "audit_template_id" => "Id",
            "audit_name" => "Audit Name",
            "next_date" => "Next Date",
        ];

        // changes on 8/10/25 by Darsh - wire ajax for upcoming audits table
        $tdata ['ajax_url_for_data']=base_url("Structure_audit_dashboard/table_ajax");
        $data['user_table'] = view("Layout/table-view", $tdata);
        
        // changes on 14/11/25 by darsh: For cluster managers, lock region and cluster with their assigned values
        if (isClusterManager()) {
            $assignedClusters = getClusterManagerAssignedCluster();
            $assignedRegions = getClusterManagerAssignedRegion();
            
            // Set locked values for cluster manager
            $data['locked_clusters'] = $assignedClusters;
            $data['locked_regions'] = $assignedRegions;
            $data['is_cluster_manager'] = true;
            
            // changes on 14/11/25 by darsh: Fetch locations from alert_location_master filtered by region
            // Show all locations in their regions (data will be filtered by cluster in queries)
            $locBuilder = $db->table("alert_location_master");
            if (!empty($assignedRegions)) {
                $locBuilder->whereIn('region_name', $assignedRegions);
            }
            $data['location'] = $locBuilder->groupBy("location_name")
                ->get()->getResultArray();

            $data['cluster'] = array_map(function($c) { return ['cluster_name' => $c]; }, $assignedClusters);
            $data['region'] = array_map(function($r) { return ['region_name' => $r]; }, $assignedRegions);
        } else {
            // For non-cluster managers, show all options
            $data['is_cluster_manager'] = false;
            $data['location'] = $db->table("alert_location_master")->groupBy("location_name")->get()->getResultArray();
            $data['cluster'] = $db->table("alert_location_master")->groupBy("cluster_name")->get()->getResultArray();
            $data['region'] = $db->table("alert_location_master")->groupBy("region_name")->get()->getResultArray();
        }
        
        $data['audit_template'] = $db->table("alert_audit_template")->like('audit_name', 'OE')->get()->getResultArray();
        // changes on 6/10/25 by darsh: expose audit template list for filtering by specific audit on the dashboard

        // Financial Year
        $currentMonth = date('m');
        $currentYear = date('Y');
        if ($currentMonth >= 4) {
            $data['audit_year'] = $currentYear . '-' . ($currentYear + 1);
        } else {
            $data['audit_year'] = ($currentYear - 1) . '-' . $currentYear;
        }

        // changes on 14/11/25 by darsh: Cluster-based ACL filtering via client_name
        $aclCondition = getClusterFilterByClientName('f.client_name');
        $results = $db->query("
            SELECT a.audit_name, COUNT(f.audit_template_id) AS audit_count
            FROM alert_audit_template a
            LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
            WHERE a.audit_name LIKE '%OE%' {$aclCondition}
            GROUP BY a.audit_template_id
        ")->getResultArray();

        $auditNames = $auditCounts = [];
        foreach ($results as $row) {
            $auditNames[] = $row['audit_name'];
            $auditCounts[] = $row['audit_count'];
        }
        $data['auditNames'] = json_encode($auditNames);
        $data['auditCounts'] = json_encode($auditCounts);

        // Audit Findings - Filter for OE audits only with ACL
        // changes on 14/11/25 by darsh: Join with final audit to apply cluster filter
        $audit_finding = $db->query("
            SELECT a.audit_name, 
                   SUM(fd.audit_finding = 'Yes') AS yes_count,
                   SUM(fd.audit_finding = 'No') AS no_count
            FROM alert_audit_template a
            LEFT JOIN alert_final_structured_audit_details fd ON a.audit_template_id = fd.audit_template_id
            LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
            WHERE a.audit_name LIKE '%OE%' {$aclCondition}
            GROUP BY a.audit_name
        ")->getResultArray();

        $auditFindingNames = $countsYes = $countsNo = [];
        foreach ($audit_finding as $row) {
            $auditFindingNames[] = $row['audit_name'];
            $countsYes[] = $row['yes_count'];
            $countsNo[] = $row['no_count'];
        }
        $data['auditFindingNames'] = json_encode($auditFindingNames);
        $data['countsYes'] = json_encode($countsYes);
        $data['countsNo'] = json_encode($countsNo);

        // Risk Priority counts - Filter for OE audits only
        // changes on 14/11/25 by darsh: Apply cluster filter via client_name
        $audit_risk = $db->query("
            SELECT 
                SUM(CASE WHEN fd.risk_priority='Medium' THEN 1 ELSE 0 END) AS medium_count,
                SUM(CASE WHEN fd.risk_priority='High' THEN 1 ELSE 0 END) AS high_count,
                SUM(CASE WHEN fd.risk_priority='Low' THEN 1 ELSE 0 END) AS low_count
            FROM alert_audit_template a
            LEFT JOIN alert_final_structured_audit_details fd ON a.audit_template_id = fd.audit_template_id
            LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
            WHERE a.audit_name LIKE '%OE%' {$aclCondition}
        ")->getRowArray();

        // changes on 8/10/25 by Darsh - pass scalar counts for chart
        $data['mediumCount'] = (int)($audit_risk['medium_count'] ?? 0);
        $data['highCount'] = (int)($audit_risk['high_count'] ?? 0);
        $data['lowCount'] = (int)($audit_risk['low_count'] ?? 0);

        // Location risk - Filter for OE audits only
        // changes on 14/11/25 by darsh: Apply cluster filter via client_name
        $audit_location = $db->query("
            SELECT a.location, COUNT(fd.risk_priority) AS risk_priority_count
            FROM alert_audit_template a
            LEFT JOIN alert_final_structured_audit_details fd ON a.audit_template_id = fd.audit_template_id
            LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
            WHERE a.audit_name LIKE '%OE%' {$aclCondition}
            GROUP BY a.location
        ")->getResultArray();

        $locationPriority = $riskPriorityCount = [];
        foreach ($audit_location as $row) {
            $locationPriority[] = $row['location'];
            $riskPriorityCount[] = $row['risk_priority_count'];
        }
        $data['location_risk'] = json_encode($locationPriority);
        $data['risk_priority'] = json_encode($riskPriorityCount);

        // Audit frequency by location (from structure audits) - Filter for OE audits only
        // changes on 14/11/25 by darsh: Apply cluster filter via client_name
        $audit_frequency = $db->query("
            SELECT a.location, a.audit_name, COUNT(f.audit_template_id) AS frequency_count
            FROM alert_final_structured_audit f
            JOIN alert_audit_template a ON a.audit_template_id = f.audit_template_id
            WHERE a.audit_name LIKE '%OE%' " . getClusterFilterByClientName('f.client_name') . "
            GROUP BY a.location, a.audit_name
        ")->getResultArray();

        $locationAudit = $auditName = $frequencyCount = [];
        foreach ($audit_frequency as $row) {
            $locationAudit[] = $row['location'];
            $auditName[] = $row['audit_name'];
            $frequencyCount[] = $row['frequency_count'];
        }
        $data['location_audit'] = json_encode($locationAudit);
        $data['audit_name'] = json_encode($auditName);
        $data['frequency_count'] = json_encode($frequencyCount);

        // Audit frequency by audit (counts of final audits per audit_type) - Filter for OE audits only
        // changes on 14/11/25 by darsh: Apply cluster filter via client_name
        $freq_by_audit = $db->query("
            SELECT a.audit_name, COUNT(f.audit_template_id) AS audit_count
            FROM alert_final_structured_audit f
            JOIN alert_audit_template a ON a.audit_template_id = f.audit_template_id
            WHERE a.audit_name LIKE '%OE%' " . getClusterFilterByClientName('f.client_name') . "
            GROUP BY a.audit_name
        ")->getResultArray();

        $frequencyScore = $auditName = [];
        foreach ($freq_by_audit as $row) {
            $frequencyScore[] = (int)$row['audit_count'];
            $auditName[] = $row['audit_name'];
        }
        $data['frequency_score'] = json_encode($frequencyScore); // reused key for chart
        $data['audit_score_name'] = json_encode($auditName);

        // Audit timeline - Filter for OE audits only
        // changes on 14/11/25 by darsh: Apply cluster filter via client_name
        $audit_time = $db->query("
            SELECT f.audit_date, f.audit_score 
            FROM alert_final_structured_audit f
            JOIN alert_audit_template a ON a.audit_template_id = f.audit_template_id
            WHERE a.audit_name LIKE '%OE%' " . getClusterFilterByClientName('f.client_name') . "
        ")->getResultArray();
        $auditDate = $auditScores = [];
        foreach ($audit_time as $row) {
            $auditDate[] = $row['audit_date'];
            $auditScores[] = $row['audit_score'];
        }
        $data['audit_date'] = json_encode($auditDate);
        $data['audit_scores'] = json_encode($auditScores);

        return view("structure_audit_dashboard", $data);
    }

    // AJAX source for upcoming audits table - Filter for OE audits only
    function table_ajax(){
        // changes on 14/11/25 by darsh: Cluster-based ACL filtering (no region filtering)
        $db = db_connect();
        
        // Build query - no ACL filtering for audit templates (they don't have client_name)
        // Cluster managers see all templates but filtered data in charts
        $sql = "SELECT audit_template_id, audit_name, next_date 
                FROM alert_audit_template 
                WHERE next_date >= ? AND audit_name LIKE '%OE%'
                ORDER BY next_date ASC";
        $params = [date('Y-m-d')];
        
        $rows = $db->query($sql, $params)->getResultArray();
        $data['data'] = $rows;
        return $this->response->setJSON($data);
    }

    // Get locations by region
    function GetLocation()
    {
        $db = db_connect();
        $region = $this->request->getVar('region_id');
        $cluster = $this->request->getVar('cluster_name');

        $builder = $db->table("alert_location_master")
                      ->select('location_name')
                      ->groupBy('location_name');

        if (!empty($region)) {
            $builder->where('region_name', $region);
        }
        if (!empty($cluster)) {
            $builder->where('cluster_name', $cluster);
        }

        $location = $builder->get()->getResultArray();
        echo json_encode($location);
    }

    // Get clusters by region
    function GetCluster()
    {
        $db = db_connect();
        $region = $this->request->getVar('region_id');

        $builder = $db->table('alert_location_master')
                      ->select('cluster_name')
                      ->groupBy('cluster_name');

        if (!empty($region)) {
            $builder->where('region_name', $region);
        }

        $clusters = $builder->get()->getResultArray();
        echo json_encode($clusters);
    }

    // Get audit names independent of other fields (OE audits only)
    function GetAuditName()
    {
        $db = db_connect();
        // Filter for OE audits only
        $audit_name = $db->table("alert_audit_template")
                        ->select('audit_name')
                        ->like('audit_name', 'OE')
                        ->groupBy('audit_name')
                        ->get()->getResultArray();
        echo json_encode($audit_name);
    }

    // Filter dashboard AJAX
    public function filter()
{
    try {
        $db = db_connect();

        $cluster    = $this->request->getVar('cluster_name');
        $location   = $this->request->getVar('location_name');
        $year       = $this->request->getVar('year');
        // changes on 8/10/25 by Darsh - use audit_name param (replaces audit type)
        $auditNameF = $this->request->getVar('audit_name');
        // Common WHERE conditions
        $where = [];
        // changes on 8/10/25 by Darsh - remove dependency of charts on cluster/location fields; templates are independent
        // (Keep Cluster Manager scope below for authorization only)
        
        // Always filter for OE audits
        $where = [];
        
        if (!empty($auditNameF)) { // changes on 6/10/25 by darsh: filter by specific audit name when provided
            $where['a.audit_name'] = $auditNameF;
        }
        // Year condition (financial year range) on final audit date
        $dateStart = null; $dateEnd = null; $hasYear = false;
        if (!empty($year)) {
            // Expect formats like "2025-2026" or just "2025"
            if (strpos($year, '-') !== false) {
                [$startY, $endY] = explode('-', $year);
                $startY = (int)trim($startY); $endY = (int)trim($endY);
                if ($startY > 0 && $endY > 0) {
                    $dateStart = sprintf('%04d-04-01', $startY);
                    $dateEnd   = sprintf('%04d-03-31', $endY);
                    $hasYear = true;
                }
            } else {
                $y = (int)trim($year);
                if ($y > 0) {
                    $dateStart = sprintf('%04d-01-01', $y);
                    $dateEnd   = sprintf('%04d-12-31', $y);
                    $hasYear = true;
                }
            }
        }

        // changes on 14/11/25 by darsh: Apply cluster-based ACL filtering via client_name
        $aclFilter = getClusterFilterByClientName('f.client_name');

        // -------------------
        // 1. Audit Counts
        // -------------------
        $sql1 = "SELECT a.audit_name, COUNT(f.audit_template_id) AS audit_count
                 FROM alert_audit_template a
                 LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
                 WHERE a.audit_name LIKE '%OE%' {$aclFilter}";
        if (!empty($auditNameF)) $sql1 .= " AND a.audit_name = " . $db->escape($auditNameF);
        if ($hasYear) $sql1 .= " AND f.audit_date >= " . $db->escape($dateStart) . " AND f.audit_date <= " . $db->escape($dateEnd);
        $sql1 .= " GROUP BY a.audit_name";
        $results1 = $db->query($sql1)->getResultArray();

        $auditNames = [];
        $auditCounts = [];
        foreach ($results1 as $row) {
            $auditNames[]  = $row['audit_name'];
            $auditCounts[] = (int) $row['audit_count'];
        }

        // -------------------
        // 2. Audit Findings
        // -------------------
        $sql2 = "SELECT a.audit_name, 
                      COALESCE(SUM(CASE WHEN fd.audit_finding = 'Yes' THEN 1 ELSE 0 END),0) AS yes_count,
                      COALESCE(SUM(CASE WHEN fd.audit_finding = 'No' THEN 1 ELSE 0 END),0) AS no_count
                 FROM alert_audit_template a
                 LEFT JOIN alert_final_structured_audit_details fd ON a.audit_template_id = fd.audit_template_id
                 LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
                 WHERE a.audit_name LIKE '%OE%' {$aclFilter}";
        if (!empty($auditNameF)) $sql2 .= " AND a.audit_name = " . $db->escape($auditNameF);
        if ($hasYear) $sql2 .= " AND f.audit_date >= " . $db->escape($dateStart) . " AND f.audit_date <= " . $db->escape($dateEnd);
        $sql2 .= " GROUP BY a.audit_name";
        $results2 = $db->query($sql2)->getResultArray();

        $auditFindingNames = [];
        $countsYes = [];
        $countsNo  = [];
        foreach ($results2 as $row) {
            $auditFindingNames[] = $row['audit_name'];
            $countsYes[] = (int) $row['yes_count'];
            $countsNo[]  = (int) $row['no_count'];
        }

        // -------------------
        // 3. Risk Priority
        // -------------------
        $sql3 = "SELECT COALESCE(SUM(CASE WHEN fd.risk_priority='Medium' THEN 1 ELSE 0 END),0) AS medium_count,
                      COALESCE(SUM(CASE WHEN fd.risk_priority='High' THEN 1 ELSE 0 END),0) AS high_count,
                      COALESCE(SUM(CASE WHEN fd.risk_priority='Low' THEN 1 ELSE 0 END),0) AS low_count
                 FROM alert_audit_template a
                 LEFT JOIN alert_final_structured_audit_details fd ON a.audit_template_id = fd.audit_template_id
                 LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
                 WHERE a.audit_name LIKE '%OE%' {$aclFilter}";
        if (!empty($auditNameF)) $sql3 .= " AND a.audit_name = " . $db->escape($auditNameF);
        if ($hasYear) $sql3 .= " AND f.audit_date >= " . $db->escape($dateStart) . " AND f.audit_date <= " . $db->escape($dateEnd);
        $row3 = $db->query($sql3)->getRowArray();
        $mediumCount = (int)($row3['medium_count'] ?? 0);
        $highCount   = (int)($row3['high_count'] ?? 0);
        $lowCount    = (int)($row3['low_count'] ?? 0);

        // -------------------
        // 4. Location Risk
        // -------------------
        $sql4 = "SELECT a.location, COALESCE(COUNT(fd.risk_priority),0) AS risk_priority_count
                 FROM alert_audit_template a
                 LEFT JOIN alert_final_structured_audit_details fd ON a.audit_template_id = fd.audit_template_id
                 LEFT JOIN alert_final_structured_audit f ON a.audit_template_id = f.audit_template_id
                 WHERE a.audit_name LIKE '%OE%' {$aclFilter}";
        if (!empty($auditNameF)) $sql4 .= " AND a.audit_name = " . $db->escape($auditNameF);
        if ($hasYear) $sql4 .= " AND f.audit_date >= " . $db->escape($dateStart) . " AND f.audit_date <= " . $db->escape($dateEnd);
        $sql4 .= " GROUP BY a.location";
        $results4 = $db->query($sql4)->getResultArray();

        $locationPriority = [];
        $riskPriorityCount = [];
        foreach ($results4 as $row) {
            $locationPriority[] = $row['location'];
            $riskPriorityCount[] = (int) $row['risk_priority_count'];
        }

        // -------------------
        // 5. Audit Frequency
        // -------------------
        $sql5 = "SELECT a.location, a.audit_name, COALESCE(COUNT(f.audit_template_id),0) AS frequency_count
                 FROM alert_final_structured_audit f
                 INNER JOIN alert_audit_template a ON a.audit_template_id = f.audit_template_id
                 WHERE a.audit_name LIKE '%OE%' {$aclFilter}";
        if (!empty($auditNameF)) $sql5 .= " AND a.audit_name = " . $db->escape($auditNameF);
        if ($hasYear) $sql5 .= " AND f.audit_date >= " . $db->escape($dateStart) . " AND f.audit_date <= " . $db->escape($dateEnd);
        $sql5 .= " GROUP BY a.location, a.audit_name";
        $results5 = $db->query($sql5)->getResultArray();

        $locationAudit = [];
        $auditNameArr  = [];
        $frequencyCount = [];
        foreach ($results5 as $row) {
            $locationAudit[]   = $row['location'];
            $auditNameArr[]    = $row['audit_name'];
            $frequencyCount[]  = (int) $row['frequency_count'];
        }

        // -------------------
        // 6. Audit Scores
        // -------------------
        $sql6 = "SELECT a.audit_name, COALESCE(COUNT(f.audit_template_id),0) AS frequency_score
                 FROM alert_final_structured_audit f
                 INNER JOIN alert_audit_template a ON a.audit_template_id = f.audit_template_id
                 WHERE a.audit_name LIKE '%OE%' {$aclFilter}";
        if (!empty($auditNameF)) $sql6 .= " AND a.audit_name = " . $db->escape($auditNameF);
        if ($hasYear) $sql6 .= " AND f.audit_date >= " . $db->escape($dateStart) . " AND f.audit_date <= " . $db->escape($dateEnd);
        $sql6 .= " GROUP BY a.audit_name";
        $results6 = $db->query($sql6)->getResultArray();

        $frequencyScore = [];
        $auditScoreName = [];
        foreach ($results6 as $row) {
            $frequencyScore[] = (int) $row['frequency_score'];
            $auditScoreName[] = $row['audit_name'];
        }

        // -------------------
        // 7. Audit Timeline
        // -------------------
        $sql7 = "SELECT f.audit_date, f.audit_score
                 FROM alert_final_structured_audit f
                 LEFT JOIN alert_audit_template a ON a.audit_template_id = f.audit_template_id
                 WHERE a.audit_name LIKE '%OE%' {$aclFilter}";
        if (!empty($auditNameF)) $sql7 .= " AND a.audit_name = " . $db->escape($auditNameF);
        if ($hasYear) $sql7 .= " AND f.audit_date >= " . $db->escape($dateStart) . " AND f.audit_date <= " . $db->escape($dateEnd);
        $results7 = $db->query($sql7)->getResultArray();

        $auditDate   = [];
        $auditScores = [];
        foreach ($results7 as $row) {
            $auditDate[]   = $row['audit_date'];
            $auditScores[] = (float) $row['audit_score'];
        }

        return $this->response->setJSON([
            'success' => true,
            'filters' => [
                'cluster'    => $cluster,
                'location'   => $location,
                'audit_name' => $auditNameF,
                'year'       => $year
            ],
            'auditNames'        => $auditNames,
            'auditCounts'       => $auditCounts,
            'auditFindingNames' => $auditFindingNames,
            'countsYes'         => $countsYes,
            'countsNo'          => $countsNo,
            'mediumCount'       => $mediumCount,
            'highCount'         => $highCount,
            'lowCount'          => $lowCount,
            'location_risk'     => $locationPriority,
            'risk_priority'     => $riskPriorityCount,
            'location_audit'    => $locationAudit,
            'audit_name'        => $auditNameArr,
            'frequency_count'   => $frequencyCount,
            'frequency_score'   => $frequencyScore,
            'audit_score_name'  => $auditScoreName,
            'audit_date'        => $auditDate,
            'audit_scores'      => $auditScores,
        ]);
    } catch (\Exception $e) {
        return $this->response->setJSON([
            'success' => false,
            'error'   => $e->getMessage(),
        ])->setStatusCode(500);
    }
}

}
