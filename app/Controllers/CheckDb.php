<?php
namespace App\Controllers;
use CodeIgniter\Controller;
class CheckDb extends Controller {
    public function index() {
        $db = \Config\Database::connect();
        
        $sql = "SELECT a.structured_audit_id, a.audit_no, a.audit_name, a.client_name, a.region, a.cluster_name, a.location, a.auditor_name, a.audit_date, a.next_date, a.status,
                (SELECT COUNT(*) FROM alert_final_structured_audit_details d WHERE d.structured_audit_id = a.structured_audit_id AND d.audit_finding = 'NO' AND d.status NOT IN (3, 5)) as pending_nc_count
                FROM alert_final_structured_audit a
                JOIN alert_client c ON c.client_name = a.client_name
                WHERE c.status = 1
                HAVING pending_nc_count > 0
                LIMIT 5";
                
        $res = $db->query($sql)->getResultArray();
        print_r($res);
    }
}
