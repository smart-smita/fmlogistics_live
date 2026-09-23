<?php

namespace App\Controllers;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public $BaseModel=null;
    public function __construct(){
        // changes on 16/10/25 by darsh: Using simple ACL helper for regional filtering
        helper(['simple_acl', 'dashboard']);
    }
    
    /**
     * Get simple ACL filter condition for dashboard queries
     * changes on 16/10/25 by darsh: Simplified ACL using session-based filtering
     */
    private function getACLCondition($regionColumn = 'region') {
        // Multi-Cluster ACL: Support for multiple regions and clusters - 14/11/25
        helper(['designation_acl']);
        $filter = '';
        if (isClusterManager() || isWHManager()) {
            $assignedRegions = getClusterManagerAssignedRegion();
            if (!empty($assignedRegions)) {
                $escapedRegions = array_map([\Config\Database::connect(), 'escape'], $assignedRegions);
                $filter .= " AND {$regionColumn} IN (" . implode(',', $escapedRegions) . ")";
            }
            
            // Also apply cluster filter based on client_name if appropriate
            // Note: In Dashboard.php many queries join alert_users, but cluster filtering is more reliable
            $clusterFilter = getClusterFilterByClientName(); 
            if (!empty($clusterFilter)) {
                $filter .= $clusterFilter;
            }
        }
        return $filter;
    }
    public function index(){
        $db=db_connect();
        
        // DEBUG: Check session and ACL for Balamurugan
        // Remove this debug code after testing
        if (isset($_SESSION['user_name']) && $_SESSION['user_name'] === 'Balamurugan') {
            error_log("DEBUG Balamurugan - user_designation: " . ($_SESSION['user_designation'] ?? 'NOT SET'));
            error_log("DEBUG Balamurugan - user_region: " . ($_SESSION['user_region'] ?? 'NOT SET'));
            error_log("DEBUG Balamurugan - isClusterManager: " . (isClusterManager() ? 'YES' : 'NO'));
            error_log("DEBUG Balamurugan - getUserRegionFromSession: " . getUserRegionFromSession());
            $aclTest = addRegionWhereClause('region');
            error_log("DEBUG Balamurugan - ACL condition: '" . $aclTest . "'");
        }
        
        //Country DDL
        $data['country']=$db->table("alert_country")->get()->getResultArray();
         // Region DDL
        $data['region']=$db->table("alert_region")->get()->getResultArray();
         // Location DDL
        $data['location']=$db->table("alert_location_master")->get()->getResultArray();
         // Cluster DDL
        $data['cluster']=$db->table("alert_cluster_master")->get()->getResultArray();
       
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
    
            // Year DDL
            $currentYear = date('Y');
            $financialYears = [];
            // Generate last 5 financial years dynamically
            for ($i = 0; $i < 5; $i++) {
                $startYear = ($currentYear - $i) - 1;
                $endYear = $currentYear - $i;
                $financialYears[] = "$startYear-$endYear";
            }
            $data['financialYears'] = $financialYears;
        
        // Apply simple ACL filtering to all dashboard queries
        $aclCondition = $this->getACLCondition('region');
        
        // Normal Audit Query with ACL
        $aging_normal_sql = "SELECT audit.`normal_audit_id`,`audit_no`, `audit_name`, `region`, `category`, COUNT(audit_finding) openpoints 
                            FROM `alert_normal_audit` audit 
                            LEFT JOIN `alert_normal_audit_details` audit_details ON audit.`normal_audit_id` = audit_details.`normal_audit_id` AND audit_finding = 'NO' 
                            LEFT JOIN alert_users u ON audit.user_id = u.user_id
                            WHERE 1=1 {$aclCondition}
                            GROUP BY audit.`normal_audit_id`, category";
        $aging_normal_query = $db->query($aging_normal_sql)->getResultArray();
        $data['aging_normal_score'] = $aging_normal_query;
        
        // OE Audit Query with ACL
        $aging_OE_sql = "SELECT audit.`structured_audit_id`,`audit_no`, `audit_name`, `region`, `category`, COUNT(audit_finding) openpoints 
                        FROM `alert_final_structured_audit` audit 
                        LEFT JOIN `alert_final_structured_audit_details` audit_details ON audit.`structured_audit_id` = audit_details.`structured_audit_id` AND audit_finding = 'NO' 
                        LEFT JOIN alert_users u ON audit.user_id = u.user_id
                        WHERE 1=1 {$aclCondition}
                        GROUP BY audit.`structured_audit_id`, category";
        $aging_OE_query = $db->query($aging_OE_sql)->getResultArray();
        $data['aging_OE_score'] = $aging_OE_query;
        
        // HSE Audit Query with ACL
        $aging_HSE_sql = "SELECT b.hse_audit_id, audit_no, audit_name, c.question_name, 
                         SUM(CASE WHEN c.client_leased = 'NO' THEN 1 ELSE 0 END) AS total_client_leased_no, 
                         SUM(CASE WHEN c.inplant = 'NO' THEN 1 ELSE 0 END) AS total_inplant_no, 
                         SUM(CASE WHEN c.fm_leased = 'NO' THEN 1 ELSE 0 END) AS total_fm_leased_no 
                         FROM alert_hse_audit_master b 
                         LEFT JOIN alert_hse_audit_details c ON c.hse_audit_id = b.hse_audit_id 
                         LEFT JOIN alert_users u ON b.user_id = u.user_id
                         WHERE 1=1 {$aclCondition}
                         GROUP BY c.question_name";
        $aging_HSE_query = $db->query($aging_HSE_sql)->getResultArray();
        $data['aging_HSE_score'] = $aging_HSE_query;    
    
        
        $total_query =$db->query("SELECT COUNT(DISTINCT alert_final_structured_audit.audit_template_id) AS audit_count, alert_final_structured_audit.client_name, alert_final_structured_audit.zone, SUM(weightage) AS total, SUM(CASE WHEN audit_finding = 'yes' THEN weightage ELSE 0 END) AS yes, SUM(CASE WHEN audit_finding = 'no' THEN weightage ELSE 0 END) AS no FROM alert_final_structured_audit_details LEFT JOIN alert_final_structured_audit ON alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id GROUP BY client_name, zone;");
        $total_result = $total_query->getResultArray();
        $data['total_score'] = $total_result;    
        
        $total_query =$db->query("SELECT * FROM `alert_final_structured_audit`");
        $total_result = $total_query->getResultArray();
        $data['upcoming_audit'] = $total_result;    
        
        // Finantial Years Wise Month Score
        $normal_year_score_query =$db->query("SELECT 
                audit.location, 
                loc.cluster_name, 
                audit.region,
            
                -- Financial Year Score Calculation
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT audit.normal_audit_id), 0)), 0), '%') AS avg_score_percentage,
            
                -- Monthly Score Calculation for each Financial Month
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 4 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 4 THEN audit.normal_audit_id END), 0)), 0), '%') AS Apr_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 5 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 5 THEN audit.normal_audit_id END), 0)), 0), '%') AS May_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 6 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 6 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jun_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 7 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 7 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jul_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 8 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 8 THEN audit.normal_audit_id END), 0)), 0), '%') AS Aug_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 9 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 9 THEN audit.normal_audit_id END), 0)), 0), '%') AS Sep_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 10 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 10 THEN audit.normal_audit_id END), 0)), 0), '%') AS Oct_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 11 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 11 THEN audit.normal_audit_id END), 0)), 0), '%') AS Nov_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 12 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 12 THEN audit.normal_audit_id END), 0)), 0), '%') AS Dec_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 1 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 1 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jan_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 2 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 2 THEN audit.normal_audit_id END), 0)), 0), '%') AS Feb_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 3 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 3 THEN audit.normal_audit_id END), 0)), 0), '%') AS Mar_Score
            
            FROM alert_normal_audit AS audit
            LEFT JOIN alert_normal_audit_details AS details 
                ON audit.normal_audit_id = details.normal_audit_id
            LEFT JOIN alert_location_master AS loc 
                ON audit.location = loc.location_name 
            
            GROUP BY audit.location;")->getResultArray();
        $data['normal_year_score_query'] = $normal_year_score_query;   
         $OE_year_score_query =$db->query("SELECT 
    audit.location, 
    loc.cluster_name, 
    audit.region,

    -- Financial Year Score Calculation
    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT audit.structured_audit_id), 0)), 0), '%') AS avg_score_percentage,

    -- Monthly Score Calculation for each Financial Month
    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 4 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 4 THEN audit.structured_audit_id END), 0)), 0), '%') AS Apr_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 5 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 5 THEN audit.structured_audit_id END), 0)), 0), '%') AS May_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 6 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 6 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jun_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 7 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 7 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jul_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 8 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 8 THEN audit.structured_audit_id END), 0)), 0), '%') AS Aug_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 9 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 9 THEN audit.structured_audit_id END), 0)), 0), '%') AS Sep_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 10 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 10 THEN audit.structured_audit_id END), 0)), 0), '%') AS Oct_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 11 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 11 THEN audit.structured_audit_id END), 0)), 0), '%') AS Nov_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 12 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 12 THEN audit.structured_audit_id END), 0)), 0), '%') AS Dec_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 1 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 1 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jan_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 2 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 2 THEN audit.structured_audit_id END), 0)), 0), '%') AS Feb_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 3 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 3 THEN audit.structured_audit_id END), 0)), 0), '%') AS Mar_Score

FROM alert_final_structured_audit AS audit
LEFT JOIN alert_final_structured_audit_details AS details 
    ON audit.structured_audit_id = details.structured_audit_id
LEFT JOIN alert_location_master AS loc 
    ON audit.location = loc.location_name 
 
GROUP BY audit.location, loc.cluster_name, audit.region;")->getResultArray();
         $data['OE_year_score_query'] = $OE_year_score_query;
         $HSE_year_score_query =$db->query("SELECT 
                master.location, 
                master.region, 
                loc.cluster_name, 
            
                -- Financial Year Calculation
                CASE 
                    WHEN MONTH(master.audit_date) >= 4 THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1)
                    ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date))
                END AS financial_year, 
            
                DATE_FORMAT(master.audit_date, '%M') AS audit_month, 
            
                -- Client Leased Avg Score
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) / 
                         NULLIF(COUNT(CASE WHEN details.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS client_leased_avg_score,
            
                -- Inplant Avg Score
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN details.inplant = 'YES' THEN 1 ELSE NULL END) / 
                         NULLIF(COUNT(CASE WHEN details.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS inplant_avg_score,
            
                -- FM Leased Avg Score
                ROUND(
                    IFNULL(
                        (COUNT(CASE WHEN details.fm_leased = 'YES' THEN 1 ELSE NULL END) / 
                         NULLIF(COUNT(CASE WHEN details.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                    ), 2
                ) AS fm_leased_avg_score
            
            FROM alert_hse_audit_master AS master
            LEFT JOIN alert_hse_audit_details details 
                ON master.hse_audit_id = details.hse_audit_id
            LEFT JOIN alert_location_master loc 
                ON master.location = loc.location_name
            
            -- Grouping by Financial Year, Location, and Month
            GROUP BY 
                master.location
            
            -- Order by Financial Year Descending
            ORDER BY financial_year DESC, FIELD(audit_month, 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March');")->getResultArray();
         $data['HSE_year_score_query'] = $HSE_year_score_query;
            
         //Upcomming Audit
        $total_normal_up_audit_result = $db->query("SELECT * FROM `alert_hse_audit_master` WHERE next_date >= CURDATE()")->getResultArray();
        $data['Normal_audit'] = $total_normal_up_audit_result;   
        $total_OE_up_audit_result = $db->query("SELECT * FROM `alert_final_structured_audit` WHERE next_date >= CURDATE()")->getResultArray();
        $data['OE_audit'] = $total_OE_up_audit_result;    
        $total_HSE_up_audit_result = $db->query("SELECT * FROM `alert_normal_audit` WHERE next_date >= CURDATE()")->getResultArray();
        $data['HSE_audit'] = $total_HSE_up_audit_result;   
            
        // Aging Category
        $data['category_normal']=$db->table("alert_normal_audit_excel_import")->select("category")->groupby("category")->get()->getResultArray();
        $data['category_OE']=$db->table("alert_audit_excel_import")->select("category")->groupby("category")->get()->getResultArray();
        $data['category_HSE']=$db->table("alert_question_audit_master")->select("question_name")->groupby("TRIM(question_name)")->get()->getResultArray();
      
             //OE Aging
            //$total_OE_score_openpoints = $db->query("SELECT audit.location, CASE WHEN MONTH(audit.audit_date) >= 4 THEN CONCAT(YEAR(audit.audit_date), '-', YEAR(audit.audit_date) + 1) ELSE CONCAT(YEAR(audit.audit_date) - 1, '-', YEAR(audit.audit_date)) END AS financial_year, DATE_FORMAT(audit.audit_date, '%M') AS month, MONTH(audit.audit_date) AS month_number, audit.region, SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) AS total_score, COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints, audit_details.audit_remark FROM alert_final_structured_audit audit LEFT JOIN alert_final_structured_audit_details audit_details ON audit.structured_audit_id = audit_details.structured_audit_id GROUP BY audit.location, financial_year, month_number, DATE_FORMAT(audit.audit_date, '%M'), audit.region, audit_details.audit_remark ORDER BY financial_year DESC, month_number;")->getResultArray();
            $total_OE_score_openpoints = $db->query("SELECT 
                audit.location, 
                CONCAT(
                        IFNULL(
                            (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) / 
                            NULLIF(COUNT(DISTINCT audit.structured_audit_id), 0)),
                        0), '%') AS avg_score_percentage,
                COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
            FROM alert_final_structured_audit audit
            LEFT JOIN alert_final_structured_audit_details audit_details 
                ON audit.structured_audit_id = audit_details.structured_audit_id
            
            GROUP BY audit.location")->getResultArray();
        $data['total_OE_score_openpoints'] = $total_OE_score_openpoints;   
              //Normal Aging
             $total_normal_score_openpoints = $db->query("SELECT 
                            audit.location, 
                            CONCAT(
                                    IFNULL(
                                        (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) / 
                                        NULLIF(COUNT(DISTINCT audit.normal_audit_id), 0)),
                                    0), '%') AS avg_score_percentage,
                        
                            -- Count Total Open Points
                            COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                        
                            -- Concatenated Remarks
                            GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
                        
                        FROM alert_normal_audit audit
                        LEFT JOIN alert_normal_audit_details audit_details 
                            ON audit.normal_audit_id = audit_details.normal_audit_id
                        
                        GROUP BY audit.location;")->getResultArray();
                        $data['total_Normal_score_openpoints'] = $total_normal_score_openpoints;
                     //HSE Aging    
                $total_HSE_score_openpoints = $db->query("SELECT 
    master.location,  
    ROUND(
    (
        (
            COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) 
        ) /
        NULLIF(
            (
                COUNT(CASE WHEN details.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END) 
            ), 0
        ) * 100
    ), 2
) AS client_leased_avg_score,

ROUND(
    (
        (
            COUNT(CASE WHEN details.inplant = 'YES' THEN 1 ELSE NULL END) 
        ) /
        NULLIF(
            (
                COUNT(CASE WHEN details.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END)
            ), 0
        ) * 100
    ), 2
) AS inplant_avg_score,

ROUND(
    (
        (
            COUNT(CASE WHEN details.fm_leased = 'YES' THEN 1 ELSE NULL END)
        ) /
        NULLIF(
            (
                COUNT(CASE WHEN details.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END)
            ), 0
        ) * 100
    ), 2
) AS fm_leased_avg_score,
    COUNT(CASE WHEN details.client_leased = 'NO' THEN 1 ELSE NULL END) AS client_leased_openpoints,
    COUNT(CASE WHEN details.inplant = 'NO' THEN 1 ELSE NULL END) AS inplant_openpoints,
    COUNT(CASE WHEN details.fm_leased = 'NO' THEN 1 ELSE NULL END) AS fm_leased_openpoints,
GROUP_CONCAT(DISTINCT details.remark SEPARATOR ', ') AS remarks
FROM alert_hse_audit_master AS master
LEFT JOIN alert_hse_audit_details details 
    ON master.hse_audit_id = details.hse_audit_id
GROUP BY master.location;")->getResultArray();        
            $data['total_HSE_score_openpoints'] = $total_HSE_score_openpoints; 
		return view ( "new_dashboard", $data );
	}    


   
    function filter(){
        $request = service('request');
                $postData = $request->getVar();
        
        // Helper to handle multiple select for IN queries
        $buildInClause = function($fieldArray) {
            if (empty($fieldArray)) return "''";
            $fieldArray = is_array($fieldArray) ? $fieldArray : [$fieldArray];
            // remove 'selectAll' if present
            $fieldArray = array_filter($fieldArray, function($v) { return $v !== 'selectAll'; });
            if (empty($fieldArray)) return "''";
            $fieldArray = array_map(function($val) {
                return "'" . addslashes(trim($val)) . "'";
            }, $fieldArray);
            return implode(',', $fieldArray);
        };
        
        $regionInSql = isset($postData['region']) ? $buildInClause($postData['region']) : "''";
        $finMonthInSql = isset($postData['financial_month']) ? $buildInClause($postData['financial_month']) : "''";
        $finYearInSql = isset($postData['financial_year']) ? $buildInClause($postData['financial_year']) : "''";
        $finYearMonthsInSql = isset($postData['financial_year_months']) ? $buildInClause($postData['financial_year_months']) : "''";
                // print_r($postData);
        $db=db_connect();
        
        // $tdata['button_id']="Lmra_controle_modal"; // NEW CHANGE: removed add button from Dashboard
        
        $tdata['display_contents'] = [
              "audit_template_id"=>"Id",
                "audit_name" => "Audit Name",
                "next_date"=>"Next Date",
          ];
           
        
       $data['user_table'] = view("Layout/table-view",$tdata); 
       $data['oe_score']=$db->table("alert_final_structured_audit_details")->get()->getResultArray();
    
    
        $data['country']=$db->table("alert_country")->get()->getResultArray();
        $data['region']=$db->table("alert_region")->get()->getResultArray();
        $data['location']=$db->table("alert_location_master")->get()->getResultArray();
       $data['cluster']=$db->table("alert_cluster_master")->get()->getResultArray();
       
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
            // Generate last 5 financial years dynamically
            for ($i = 0; $i < 5; $i++) {
                $startYear = ($currentYear - $i) - 1;
                $endYear = $currentYear - $i;
                $financialYears[] = "$startYear-$endYear";
            }
            $data['financialYears'] = $financialYears;
            $data['category_normal']=$db->table("alert_normal_audit_excel_import")->select("category")->groupby("category")->get()->getResultArray();
            $data['category_OE']=$db->table("alert_audit_excel_import")->select("category")->groupby("category")->get()->getResultArray();
            $data['category_HSE']=$db->table("alert_question_audit_master")->select("question_name")->groupby("TRIM(question_name)")->get()->getResultArray();
      
         if(!empty($postData) && !empty($postData['aging_reason'])) { 
          
              //$aging_normal_query =$db->query("SELECT *, COUNT(alert_normal_audit_details.audit_details_id) AS openpoints FROM `alert_normal_audit` LEFT JOIN alert_normal_audit_details ON alert_normal_audit_details.normal_audit_id = alert_normal_audit.`normal_audit_id` AND audit_finding = 'NO' GROUP BY alert_normal_audit.normal_audit_id;")->getResultArray();
                $aging_normal_query =$db->query("SELECT audit.`normal_audit_id`,`audit_no`, `audit_name`, `region`, `category`, COUNT(audit_finding) openpoints FROM `alert_normal_audit` audit LEFT JOIN `alert_normal_audit_details` audit_details ON audit.`normal_audit_id` = audit_details.`normal_audit_id` AND audit_finding = 'NO' WHERE `region` = '".$postData['aging_reason']."' GROUP BY audit.`normal_audit_id`, category;")->getResultArray();
                $data['aging_normal_score'] = $aging_normal_query;  
                //$aging_OE_query =$db->query("SELECT *, COUNT(alert_final_structured_audit_details.audit_details_id) AS openpoints FROM `alert_final_structured_audit` LEFT JOIN alert_final_structured_audit_details ON alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id AND audit_finding = 'NO' GROUP BY alert_final_structured_audit.structured_audit_id;")->getResultArray();
                $aging_OE_query =$db->query("SELECT audit.`structured_audit_id`,`audit_no`, `audit_name`, `region`, `category`, COUNT(audit_finding) openpoints FROM `alert_final_structured_audit` audit LEFT JOIN `alert_final_structured_audit_details` audit_details ON audit.`structured_audit_id` = audit_details.`structured_audit_id` AND audit_finding = 'NO' WHERE `region` = '".$postData['aging_reason']."' GROUP BY audit.`structured_audit_id`, category;")->getResultArray();
                $data['aging_OE_score'] = $aging_OE_query;    
                $aging_HSE_query =$db->query("SELECT b.hse_audit_id, audit_no, audit_name, c.question_name, SUM(CASE WHEN c.client_leased = 'NO' THEN 1 ELSE 0 END) AS total_client_leased_no, SUM(CASE WHEN c.inplant = 'NO' THEN 1 ELSE 0 END) AS total_inplant_no, SUM(CASE WHEN c.fm_leased = 'NO' THEN 1 ELSE 0 END) AS total_fm_leased_no FROM alert_hse_audit_master b LEFT JOIN alert_hse_audit_details c ON c.hse_audit_id= b.hse_audit_id WHERE region= '".$postData['aging_reason']."' GROUP BY c.question_name;")->getResultArray();
                $data['aging_HSE_score'] = $aging_HSE_query; 
          }else{
            //$aging_normal_query =$db->query("SELECT *, COUNT(alert_normal_audit_details.audit_details_id) AS openpoints FROM `alert_normal_audit` LEFT JOIN alert_normal_audit_details ON alert_normal_audit_details.normal_audit_id = alert_normal_audit.`normal_audit_id` AND audit_finding = 'NO' GROUP BY alert_normal_audit.normal_audit_id;")->getResultArray();
            $aging_normal_query =$db->query("SELECT audit.`normal_audit_id`,`audit_no`, `audit_name`, `region`, `category`, COUNT(audit_finding) openpoints FROM `alert_normal_audit` audit LEFT JOIN `alert_normal_audit_details` audit_details ON audit.`normal_audit_id` = audit_details.`normal_audit_id` AND audit_finding = 'NO' GROUP BY audit.`normal_audit_id`, category;")->getResultArray();
            $data['aging_normal_score'] = $aging_normal_query;  
            //$aging_OE_query =$db->query("SELECT *, COUNT(alert_final_structured_audit_details.audit_details_id) AS openpoints FROM `alert_final_structured_audit` LEFT JOIN alert_final_structured_audit_details ON alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id AND audit_finding = 'NO' GROUP BY alert_final_structured_audit.structured_audit_id;")->getResultArray();
            $aging_OE_query =$db->query("SELECT audit.`structured_audit_id`,`audit_no`, `audit_name`, `region`, `category`, COUNT(audit_finding) openpoints FROM `alert_final_structured_audit` audit LEFT JOIN `alert_final_structured_audit_details` audit_details ON audit.`structured_audit_id` = audit_details.`structured_audit_id` AND audit_finding = 'NO' GROUP BY audit.`structured_audit_id`, category;")->getResultArray();
            $data['aging_OE_score'] = $aging_OE_query;    
            $aging_HSE_query =$db->query("SELECT b.hse_audit_id, audit_no, audit_name, c.question_name, SUM(CASE WHEN c.client_leased = 'NO' THEN 1 ELSE 0 END) AS total_client_leased_no, SUM(CASE WHEN c.inplant = 'NO' THEN 1 ELSE 0 END) AS total_inplant_no, SUM(CASE WHEN c.fm_leased = 'NO' THEN 1 ELSE 0 END) AS total_fm_leased_no FROM alert_hse_audit_master b LEFT JOIN alert_hse_audit_details c ON c.hse_audit_id= b.hse_audit_id GROUP BY c.question_name;")->getResultArray();
            $data['aging_HSE_score'] = $aging_HSE_query;    
         }
    
        
        $total_query =$db->query("SELECT COUNT(DISTINCT alert_final_structured_audit.audit_template_id) AS audit_count, alert_final_structured_audit.client_name, alert_final_structured_audit.zone, SUM(weightage) AS total, SUM(CASE WHEN audit_finding = 'yes' THEN weightage ELSE 0 END) AS yes, SUM(CASE WHEN audit_finding = 'no' THEN weightage ELSE 0 END) AS no FROM alert_final_structured_audit_details LEFT JOIN alert_final_structured_audit ON alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id GROUP BY client_name, zone;");
        $total_result = $total_query->getResultArray();
        $data['total_score'] = $total_result;    
        
        $total_query =$db->query("SELECT * FROM `alert_final_structured_audit`");
        $total_result = $total_query->getResultArray();
        $data['upcoming_audit'] = $total_result;    
            
         
            $total_normal_up_audit_result = $db->query("SELECT * FROM `alert_hse_audit_master` WHERE next_date >= CURDATE()")->getResultArray();
            $data['Normal_audit'] = $total_normal_up_audit_result;   
            $total_OE_up_audit_result = $db->query("SELECT * FROM `alert_final_structured_audit` WHERE next_date >= CURDATE()")->getResultArray();
            $data['OE_audit'] = $total_OE_up_audit_result;    
            $total_HSE_up_audit_result = $db->query("SELECT * FROM `alert_normal_audit` WHERE next_date >= CURDATE()")->getResultArray();
            $data['HSE_audit'] = $total_HSE_up_audit_result;   
            
            //$total_OE_score_openpoints = $db->query("SELECT audit.location, CASE WHEN MONTH(audit.audit_date) >= 4 THEN CONCAT(YEAR(audit.audit_date), '-', YEAR(audit.audit_date) + 1) ELSE CONCAT(YEAR(audit.audit_date) - 1, '-', YEAR(audit.audit_date)) END AS financial_year, DATE_FORMAT(audit.audit_date, '%M') AS month, MONTH(audit.audit_date) AS month_number, audit.region, SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) AS total_score, COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints, audit_details.audit_remark FROM alert_final_structured_audit audit LEFT JOIN alert_final_structured_audit_details audit_details ON audit.structured_audit_id = audit_details.structured_audit_id GROUP BY audit.location, financial_year, month_number, DATE_FORMAT(audit.audit_date, '%M'), audit.region, audit_details.audit_remark ORDER BY financial_year DESC, month_number;")->getResultArray();
            if(!empty($postData) && !empty($postData['region']) && !empty($postData['financial_month']) && !empty($postData['financial_year'])) { 
             //Normal Aging
                $total_normal_score_openpoints = $db->query("SELECT 
                            audit.location, 
                        
                            -- Calculate Average Score in Percentage
                            CONCAT(
                                    IFNULL(
                                        (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) / 
                                        NULLIF(COUNT(DISTINCT audit.normal_audit_id), 0)),
                                    0), '%') AS avg_score_percentage,
                        
                            -- Count Total Open Points
                            COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                        
                            -- Concatenated Remarks
                            GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
                        
                        FROM alert_normal_audit audit
                        LEFT JOIN alert_normal_audit_details audit_details 
                            ON audit.normal_audit_id = audit_details.normal_audit_id
                        
                        -- Apply Filters: Month, Financial Year, and Region
                        WHERE 
                            (CASE 
                                WHEN MONTH(audit.audit_date) >= 4 
                            THEN CONCAT(YEAR(audit.audit_date), '-', YEAR(audit.audit_date) + 1) 
                            ELSE CONCAT(YEAR(audit.audit_date) - 1, '-', YEAR(audit.audit_date)) 
                        END) IN (".$finYearInSql.")
                        AND DATE_FORMAT(audit.audit_date, '%M') IN (".$finMonthInSql.")
                        AND audit.region IN (".$regionInSql.")
                        
                        GROUP BY audit.location;")->getResultArray();
                         //OE Aging
                $total_OE_score_openpoints = $db->query("SELECT 
                        audit.location, 
                        CONCAT(
                                IFNULL(
                                    (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) / 
                                    NULLIF(COUNT(DISTINCT audit.structured_audit_id), 0)),
                                0), '%') AS avg_score_percentage,
                        COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                        GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
                    FROM alert_final_structured_audit audit
                    LEFT JOIN alert_final_structured_audit_details audit_details 
                        ON audit.structured_audit_id = audit_details.structured_audit_id
                    WHERE 
                        (CASE 
                            WHEN MONTH(audit.audit_date) >= 4 
                            THEN CONCAT(YEAR(audit.audit_date), '-', YEAR(audit.audit_date) + 1) 
                            ELSE CONCAT(YEAR(audit.audit_date) - 1, '-', YEAR(audit.audit_date)) 
                        END) IN (".$finYearInSql.")
                        AND DATE_FORMAT(audit.audit_date, '%M') IN (".$finMonthInSql.")
                        AND audit.region IN (".$regionInSql.")
                    GROUP BY audit.location")->getResultArray();
                     //HSE Aging
                     $total_HSE_score_openpoints = $db->query("SELECT 
                            master.location, 
                            
                            ROUND(
                            (
                                (
                                    COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) 
                                ) /
                                NULLIF(
                                    (
                                        COUNT(CASE WHEN details.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END) 
                                    ), 0
                                ) * 100
                            ), 2
                        ) AS client_leased_avg_score,
                        
                        ROUND(
                            (
                                (
                                    COUNT(CASE WHEN details.inplant = 'YES' THEN 1 ELSE NULL END) 
                                ) /
                                NULLIF(
                                    (
                                        COUNT(CASE WHEN details.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END)
                                    ), 0
                                ) * 100
                            ), 2
                        ) AS inplant_avg_score,
                        
                        ROUND(
                            (
                                (
                                    COUNT(CASE WHEN details.fm_leased = 'YES' THEN 1 ELSE NULL END)
                                ) /
                                NULLIF(
                                    (
                                        COUNT(CASE WHEN details.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END)
                                    ), 0
                                ) * 100
                            ), 2
                        ) AS fm_leased_avg_score,
                        
                            COUNT(CASE WHEN details.client_leased = 'NO' THEN 1 ELSE NULL END) AS client_leased_openpoints,
                            COUNT(CASE WHEN details.inplant = 'NO' THEN 1 ELSE NULL END) AS inplant_openpoints,
                            COUNT(CASE WHEN details.fm_leased = 'NO' THEN 1 ELSE NULL END) AS fm_leased_openpoints,
                        GROUP_CONCAT(DISTINCT details.remark SEPARATOR ', ') AS remarks
                        FROM alert_hse_audit_master AS master
                        LEFT JOIN alert_hse_audit_details details 
                            ON master.hse_audit_id = details.hse_audit_id
                         WHERE 
                                                    (CASE 
                                                        WHEN MONTH(master.audit_date) >= 4 
                                                    THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1) 
                                                    ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date)) 
                                                END) IN (".$finYearInSql.")
                                                AND DATE_FORMAT(master.audit_date, '%M') IN (".$finMonthInSql.")
                                                AND master.region IN (".$regionInSql.")
                        GROUP BY master.location;")->getResultArray();  
            } else {
                 //Normal Aging
                 $total_normal_score_openpoints = $db->query("SELECT 
                            audit.location, 
                            CONCAT(
                                    IFNULL(
                                        (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) / 
                                        NULLIF(COUNT(DISTINCT audit.normal_audit_id), 0)),
                                    0), '%') AS avg_score_percentage,
                        
                            -- Count Total Open Points
                            COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                        
                            -- Concatenated Remarks
                            GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
                        
                        FROM alert_normal_audit audit
                        LEFT JOIN alert_normal_audit_details audit_details 
                            ON audit.normal_audit_id = audit_details.normal_audit_id
                        
                        GROUP BY audit.location;")->getResultArray();
                         //OE Aging
                $total_OE_score_openpoints = $db->query("SELECT 
                        audit.location, 
                        CONCAT(
                                IFNULL(
                                    (SUM(CASE WHEN audit_finding = 'YES' THEN weightage ELSE 0 END) / 
                                    NULLIF(COUNT(DISTINCT audit.structured_audit_id), 0)),
                                0), '%') AS avg_score_percentage,
                        COUNT(CASE WHEN audit_finding = 'NO' THEN 1 ELSE NULL END) AS total_openpoints,
                        GROUP_CONCAT(DISTINCT audit_details.audit_remark ORDER BY audit_details.audit_remark SEPARATOR ', ') AS remarks_combined
                    FROM alert_final_structured_audit audit
                    LEFT JOIN alert_final_structured_audit_details audit_details 
                        ON audit.structured_audit_id = audit_details.structured_audit_id
                    
                    GROUP BY audit.location")->getResultArray();
                //HSE Aging    
                    $total_HSE_score_openpoints = $db->query("SELECT 
                        master.location,  
                        ROUND(
                        (
                            (
                                COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) 
                            ) /
                            NULLIF(
                                (
                                    COUNT(CASE WHEN details.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END) 
                                ), 0
                            ) * 100
                        ), 2
                    ) AS client_leased_avg_score,
                    
                    ROUND(
                        (
                            (
                                COUNT(CASE WHEN details.inplant = 'YES' THEN 1 ELSE NULL END) 
                            ) /
                            NULLIF(
                                (
                                    COUNT(CASE WHEN details.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END)
                                ), 0
                            ) * 100
                        ), 2
                    ) AS inplant_avg_score,
                    
                    ROUND(
                        (
                            (
                                COUNT(CASE WHEN details.fm_leased = 'YES' THEN 1 ELSE NULL END)
                            ) /
                            NULLIF(
                                (
                                    COUNT(CASE WHEN details.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END)
                                ), 0
                            ) * 100
                        ), 2
                    ) AS fm_leased_avg_score,
                        COUNT(CASE WHEN details.client_leased = 'NO' THEN 1 ELSE NULL END) AS client_leased_openpoints,
                        COUNT(CASE WHEN details.inplant = 'NO' THEN 1 ELSE NULL END) AS inplant_openpoints,
                        COUNT(CASE WHEN details.fm_leased = 'NO' THEN 1 ELSE NULL END) AS fm_leased_openpoints,
                    GROUP_CONCAT(DISTINCT details.remark SEPARATOR ', ') AS remarks
                    FROM alert_hse_audit_master AS master
                    LEFT JOIN alert_hse_audit_details details 
                        ON master.hse_audit_id = details.hse_audit_id
                    GROUP BY master.location;")->getResultArray();       
            }
            $data['total_Normal_score_openpoints'] = $total_normal_score_openpoints;
            $data['total_OE_score_openpoints'] = $total_OE_score_openpoints;   
            $data['total_HSE_score_openpoints'] = $total_HSE_score_openpoints; 
            
            // Finantial Years Wise Month Score
            if(!empty($postData) && !empty($postData['financial_year_months'])) {      
                $normal_year_score_query =$db->query("SELECT 
                audit.location, 
                loc.cluster_name, 
                audit.region,
            
                -- Financial Year Score Calculation
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT audit.normal_audit_id), 0)), 0), '%') AS avg_score_percentage,
            
                -- Monthly Score Calculation for each Financial Month
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 4 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 4 THEN audit.normal_audit_id END), 0)), 0), '%') AS Apr_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 5 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 5 THEN audit.normal_audit_id END), 0)), 0), '%') AS May_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 6 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 6 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jun_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 7 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 7 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jul_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 8 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 8 THEN audit.normal_audit_id END), 0)), 0), '%') AS Aug_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 9 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 9 THEN audit.normal_audit_id END), 0)), 0), '%') AS Sep_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 10 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 10 THEN audit.normal_audit_id END), 0)), 0), '%') AS Oct_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 11 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 11 THEN audit.normal_audit_id END), 0)), 0), '%') AS Nov_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 12 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 12 THEN audit.normal_audit_id END), 0)), 0), '%') AS Dec_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 1 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 1 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jan_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 2 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 2 THEN audit.normal_audit_id END), 0)), 0), '%') AS Feb_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 3 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 3 THEN audit.normal_audit_id END), 0)), 0), '%') AS Mar_Score
            
            FROM alert_normal_audit AS audit
            LEFT JOIN alert_normal_audit_details AS details 
                ON audit.normal_audit_id = details.normal_audit_id
            LEFT JOIN alert_location_master AS loc 
                ON audit.location = loc.location_name 
            
            WHERE (CASE 
                                                 WHEN MONTH(audit.audit_date) >= 4 
                                                 THEN CONCAT(YEAR(audit.audit_date), '-', YEAR(audit.audit_date) + 1) 
                                                 ELSE CONCAT(YEAR(audit.audit_date) - 1, '-', YEAR(audit.audit_date)) 
                                            END) IN (".$finYearMonthsInSql.")
            
            GROUP BY audit.location;")->getResultArray();
                $OE_year_score_query =$db->query("SELECT 
    audit.location, 
    loc.cluster_name, 
    audit.region,

    -- Financial Year Score Calculation
    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT audit.structured_audit_id), 0)), 0), '%') AS avg_score_percentage,

    -- Monthly Score Calculation for each Financial Month
    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 4 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 4 THEN audit.structured_audit_id END), 0)), 0), '%') AS Apr_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 5 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 5 THEN audit.structured_audit_id END), 0)), 0), '%') AS May_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 6 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 6 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jun_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 7 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 7 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jul_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 8 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 8 THEN audit.structured_audit_id END), 0)), 0), '%') AS Aug_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 9 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 9 THEN audit.structured_audit_id END), 0)), 0), '%') AS Sep_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 10 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 10 THEN audit.structured_audit_id END), 0)), 0), '%') AS Oct_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 11 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 11 THEN audit.structured_audit_id END), 0)), 0), '%') AS Nov_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 12 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 12 THEN audit.structured_audit_id END), 0)), 0), '%') AS Dec_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 1 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 1 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jan_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 2 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 2 THEN audit.structured_audit_id END), 0)), 0), '%') AS Feb_Score,

    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 3 THEN details.weightage ELSE 0 END) / 
          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 3 THEN audit.structured_audit_id END), 0)), 0), '%') AS Mar_Score

FROM alert_final_structured_audit AS audit
LEFT JOIN alert_final_structured_audit_details AS details 
    ON audit.structured_audit_id = details.structured_audit_id
LEFT JOIN alert_location_master AS loc 
    ON audit.location = loc.location_name 
 WHERE (CASE 
                                     WHEN MONTH(audit.audit_date) >= 4 
                                     THEN CONCAT(YEAR(audit.audit_date), '-', YEAR(audit.audit_date) + 1) 
                                     ELSE CONCAT(YEAR(audit.audit_date) - 1, '-', YEAR(audit.audit_date)) 
                                END) IN (".$finYearMonthsInSql.")
GROUP BY audit.location, loc.cluster_name, audit.region;")->getResultArray();
                $HSE_year_score_query =$db->query("SELECT 
    master.location, 
    master.region, 
    loc.cluster_name, 

    -- Financial Year Calculation
    CASE 
        WHEN MONTH(master.audit_date) >= 4 THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1)
        ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date))
    END AS financial_year, 

    DATE_FORMAT(master.audit_date, '%M') AS audit_month, 

    -- Client Leased Avg Score
    ROUND(
        IFNULL(
            (COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) / 
             NULLIF(COUNT(CASE WHEN details.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
        ), 2
    ) AS client_leased_avg_score,

    -- Inplant Avg Score
    ROUND(
        IFNULL(
            (COUNT(CASE WHEN details.inplant = 'YES' THEN 1 ELSE NULL END) / 
             NULLIF(COUNT(CASE WHEN details.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
        ), 2
    ) AS inplant_avg_score,

    -- FM Leased Avg Score
    ROUND(
        IFNULL(
            (COUNT(CASE WHEN details.fm_leased = 'YES' THEN 1 ELSE NULL END) / 
             NULLIF(COUNT(CASE WHEN details.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
        ), 2
    ) AS fm_leased_avg_score

FROM alert_hse_audit_master AS master
LEFT JOIN alert_hse_audit_details details 
    ON master.hse_audit_id = details.hse_audit_id
LEFT JOIN alert_location_master loc 
    ON master.location = loc.location_name
WHERE (CASE 
                                     WHEN MONTH(master.audit_date) >= 4 
                                     THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1) 
                                     ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date)) 
                                END) IN (".$finYearMonthsInSql.")
-- Grouping by Financial Year, Location, and Month
GROUP BY 
    master.location

-- Order by Financial Year Descending
ORDER BY financial_year DESC, FIELD(audit_month, 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March');")->getResultArray();
            } else {
                $normal_year_score_query =$db->query("SELECT 
                audit.location, 
                loc.cluster_name, 
                audit.region,
            
                -- Financial Year Score Calculation
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT audit.normal_audit_id), 0)), 0), '%') AS avg_score_percentage,
            
                -- Monthly Score Calculation for each Financial Month
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 4 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 4 THEN audit.normal_audit_id END), 0)), 0), '%') AS Apr_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 5 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 5 THEN audit.normal_audit_id END), 0)), 0), '%') AS May_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 6 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 6 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jun_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 7 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 7 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jul_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 8 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 8 THEN audit.normal_audit_id END), 0)), 0), '%') AS Aug_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 9 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 9 THEN audit.normal_audit_id END), 0)), 0), '%') AS Sep_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 10 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 10 THEN audit.normal_audit_id END), 0)), 0), '%') AS Oct_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 11 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 11 THEN audit.normal_audit_id END), 0)), 0), '%') AS Nov_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 12 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 12 THEN audit.normal_audit_id END), 0)), 0), '%') AS Dec_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 1 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 1 THEN audit.normal_audit_id END), 0)), 0), '%') AS Jan_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 2 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 2 THEN audit.normal_audit_id END), 0)), 0), '%') AS Feb_Score,
            
                CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 3 THEN details.weightage ELSE 0 END) / 
                      NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 3 THEN audit.normal_audit_id END), 0)), 0), '%') AS Mar_Score
            
            FROM alert_normal_audit AS audit
            LEFT JOIN alert_normal_audit_details AS details 
                ON audit.normal_audit_id = details.normal_audit_id
            LEFT JOIN alert_location_master AS loc 
                ON audit.location = loc.location_name 
            
            GROUP BY audit.location;")->getResultArray();
                $OE_year_score_query =$db->query("SELECT 
                    audit.location, 
                    loc.cluster_name, 
                    audit.region,
                
                    -- Financial Year Score Calculation
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT audit.structured_audit_id), 0)), 0), '%') AS avg_score_percentage,
                
                    -- Monthly Score Calculation for each Financial Month
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 4 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 4 THEN audit.structured_audit_id END), 0)), 0), '%') AS Apr_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 5 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 5 THEN audit.structured_audit_id END), 0)), 0), '%') AS May_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 6 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 6 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jun_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 7 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 7 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jul_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 8 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 8 THEN audit.structured_audit_id END), 0)), 0), '%') AS Aug_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 9 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 9 THEN audit.structured_audit_id END), 0)), 0), '%') AS Sep_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 10 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 10 THEN audit.structured_audit_id END), 0)), 0), '%') AS Oct_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 11 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 11 THEN audit.structured_audit_id END), 0)), 0), '%') AS Nov_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 12 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 12 THEN audit.structured_audit_id END), 0)), 0), '%') AS Dec_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 1 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 1 THEN audit.structured_audit_id END), 0)), 0), '%') AS Jan_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 2 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 2 THEN audit.structured_audit_id END), 0)), 0), '%') AS Feb_Score,
                
                    CONCAT(IFNULL((SUM(CASE WHEN details.audit_finding = 'YES' AND MONTH(audit.audit_date) = 3 THEN details.weightage ELSE 0 END) / 
                          NULLIF(COUNT(DISTINCT CASE WHEN MONTH(audit.audit_date) = 3 THEN audit.structured_audit_id END), 0)), 0), '%') AS Mar_Score
                
                FROM alert_final_structured_audit AS audit
                LEFT JOIN alert_final_structured_audit_details AS details 
                    ON audit.structured_audit_id = details.structured_audit_id
                LEFT JOIN alert_location_master AS loc 
                    ON audit.location = loc.location_name 
                 
                GROUP BY audit.location, loc.cluster_name, audit.region;")->getResultArray();
                $HSE_year_score_query =$db->query("SELECT 
                        master.location, 
                        master.region, 
                        loc.cluster_name, 
                    
                        -- Financial Year Calculation
                        CASE 
                            WHEN MONTH(master.audit_date) >= 4 THEN CONCAT(YEAR(master.audit_date), '-', YEAR(master.audit_date) + 1)
                            ELSE CONCAT(YEAR(master.audit_date) - 1, '-', YEAR(master.audit_date))
                        END AS financial_year, 
                    
                        DATE_FORMAT(master.audit_date, '%M') AS audit_month, 
                    
                        -- Client Leased Avg Score
                        ROUND(
                            IFNULL(
                                (COUNT(CASE WHEN details.client_leased = 'YES' THEN 1 ELSE NULL END) / 
                                 NULLIF(COUNT(CASE WHEN details.client_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                            ), 2
                        ) AS client_leased_avg_score,
                    
                        -- Inplant Avg Score
                        ROUND(
                            IFNULL(
                                (COUNT(CASE WHEN details.inplant = 'YES' THEN 1 ELSE NULL END) / 
                                 NULLIF(COUNT(CASE WHEN details.inplant IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                            ), 2
                        ) AS inplant_avg_score,
                    
                        -- FM Leased Avg Score
                        ROUND(
                            IFNULL(
                                (COUNT(CASE WHEN details.fm_leased = 'YES' THEN 1 ELSE NULL END) / 
                                 NULLIF(COUNT(CASE WHEN details.fm_leased IN ('YES', 'NO') THEN 1 ELSE NULL END), 0) * 100), 0
                            ), 2
                        ) AS fm_leased_avg_score
                    
                    FROM alert_hse_audit_master AS master
                    LEFT JOIN alert_hse_audit_details details 
                        ON master.hse_audit_id = details.hse_audit_id
                    LEFT JOIN alert_location_master loc 
                        ON master.location = loc.location_name
                    
                    -- Grouping by Financial Year, Location, and Month
                    GROUP BY 
                        master.location
                    
                    -- Order by Financial Year Descending
                    ORDER BY financial_year DESC, FIELD(audit_month, 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December', 'January', 'February', 'March');")->getResultArray();
            }
            
        $data['normal_year_score_query'] = $normal_year_score_query;   
        
        $data['OE_year_score_query'] = $OE_year_score_query;
        
        $data['HSE_year_score_query'] = $HSE_year_score_query;
            
		return view ( "new_dashboard", $data );
	} 
	
	// SELECT 
//     master.location, 
    
//     -- Calculate average score (%) for Client Leased, Inplant, FM Leased
//     ROUND(AVG(CASE WHEN master.perform_audit_by = 'client_leased'
//                   THEN master.score ELSE 0 END), 2) AS avg_score_client_leased,

//     ROUND(AVG(CASE WHEN master.perform_audit_by = 'inplant' THEN master.score ELSE 0 END), 2) AS avg_score_inplant,

//     ROUND(AVG(CASE WHEN master.perform_audit_by = 'fm_leased'
//                   THEN master.score ELSE 0 END), 2) AS avg_score_fm_leased,

//     -- Count open points (NO findings) for Client Leased, Inplant, FM Leased
//     COUNT(CASE WHEN master.perform_audit_by = 'client_leased'
//               THEN 1 ELSE NULL END) AS client_leased_open_points,

//     COUNT(CASE WHEN master.perform_audit_by = 'inplant' 
//               THEN 1 ELSE NULL END) AS inplant_open_points,

//     COUNT(CASE WHEN master.perform_audit_by = 'fm_leased' 
//               THEN 1 ELSE NULL END) AS fm_leased_open_points,

//     -- Concatenate distinct remarks
//     GROUP_CONCAT(DISTINCT details.remark SEPARATOR ', ') AS remarks

// FROM alert_hse_audit_master AS master
// LEFT JOIN alert_hse_audit_details details 
//     ON master.hse_audit_id = details.hse_audit_id

// -- Apply filters for Month, Financial Year, and Region

// -- Group by location
// GROUP BY master.location;
}
