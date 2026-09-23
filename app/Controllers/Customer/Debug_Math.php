<?php
namespace App\Controllers\Customer;
use App\Controllers\BaseController;

class Debug_Math extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        
        $sql = "SELECT a.structured_audit_id, a.client_name, loc.cluster,
                    ROUND((SUM(CASE WHEN d.audit_finding IN ('YES','NA') THEN d.weightage ELSE 0 END) / NULLIF(SUM(d.weightage), 0)) * 100, 2) AS audit_score
                FROM alert_final_structured_audit a
                INNER JOIN alert_final_structured_audit_details d ON d.structured_audit_id = a.structured_audit_id
                LEFT JOIN alert_client loc ON a.client_name = loc.client_name
                WHERE a.reaudit = 0
                  AND a.region = 'North' 
                  AND loc.cluster = 'Amit Sharma' 
                  AND YEAR(a.audit_date) = 2026 
                  AND MONTH(a.audit_date) = 6
                GROUP BY a.structured_audit_id";

        $results = $db->query($sql)->getResultArray();
        echo "<pre>";
        print_r($results);
        
        $avgSql = "SELECT ROUND(AVG(t.audit_score), 2) AS overall_score FROM ($sql) t";
        $avgResult = $db->query($avgSql)->getRowArray();
        print_r($avgResult);
    }
}
