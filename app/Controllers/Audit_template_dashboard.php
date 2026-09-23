<?php

namespace App\Controllers;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;

class Audit_template_dashboard extends BaseController
{
    public $BaseModel=null;
    public function __construct(){
        // changes on 16/10/25 by darsh: Using simple ACL helper for regional filtering
        helper(['simple_acl']);
    }
    public function index(){
        $db=db_connect();
        
        
    //   $tdata['title']="Upcoming Audits";
        // $tdata['button_name']="Add";
        $tdata['button_id']="Lmra_controle_modal";
        
        $tdata['display_contents'] = [
              "audit_template_id"=>"Id",
                "audit_name" => "Audit Name",
                "next_date"=>"Next Date",
          ];
           
        //   print_r($tdata['display_contents']);
        //   exit();
       $data['user_table'] = view("Layout/table-view",$tdata); 
       $data['oe_score']=$db->table("alert_final_structured_audit_details")->get()->getResultArray();
    //   $data['region']=$db->table("alert_audit_template")->get()->getResultArray();
    //   $data['audit_template']=$db->table("alert_audit_template")->get()->getResultArray();
    
   
      
        $query2 = $db->query("SELECT alert_final_structured_audit.client_name,
                      MONTHNAME(alert_final_structured_audit.audit_date) AS audit_month, 
                      SUM(CASE WHEN audit_finding = 'yes' THEN weightage ELSE 0 END) AS yes 
                      FROM alert_final_structured_audit_details 
                      LEFT JOIN alert_final_structured_audit
                      ON alert_final_structured_audit_details.audit_template_id = alert_final_structured_audit.audit_template_id 
                      WHERE alert_final_structured_audit.audit_date IS NOT NULL
                      GROUP BY client_name, audit_month;");

$audit_query = $query2->getResultArray();
$data['audit'] = $audit_query;
         
        // changes on 1/10/25 by darsh: replace zone with region and compute totals as well
        $query3 = $db->query("SELECT alert_final_structured_audit.region,
                      MONTHNAME(alert_final_structured_audit.audit_date) AS audit_month, 
                      SUM(CASE WHEN UPPER(audit_finding) = 'YES' THEN weightage ELSE 0 END) AS yes, 
                      SUM(weightage) AS total 
                      FROM alert_final_structured_audit_details 
                      LEFT JOIN alert_final_structured_audit
                      ON alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id 
                      WHERE alert_final_structured_audit.audit_date IS NOT NULL
                      GROUP BY region, audit_month");
$zone_query = $query3->getResultArray();
$data['zone'] = $zone_query;            
 
        // changes on 1/10/25 by darsh: use region filter instead of zone for the following summaries
$west_query = $db->query(" SELECT alert_final_structured_audit_details.location, alert_final_structured_audit.client_name,
                                       alert_final_structured_audit.region, SUM(weightage) AS total, SUM(CASE WHEN UPPER(audit_finding) = 'YES' THEN weightage ELSE 0 END) AS yes,
                                      SUM(CASE WHEN audit_finding = 'no' THEN weightage ELSE 0 END) AS no FROM  alert_final_structured_audit_details  
                                     LEFT JOIN alert_final_structured_audit ON  alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id
                                    WHERE alert_final_structured_audit_details.audit_template_id = 8 AND alert_final_structured_audit.region = 'West'  
                                  GROUP BY location, client_name, region");

$west_result = $west_query->getResultArray();
$data['west_score'] = $west_result;

$unique_locations = array_unique(array_column($west_result, 'location'));
$data['unique_locations'] = $unique_locations;

$location_west = [];
foreach ($west_result as $row) {
    if (!isset($location_west[$row['location']])) {
        $location_west[$row['location']] = 0;
    }
    $location_west[$row['location']] += $row['yes'];
}
$data['location_west'] = $location_west;
 
        $north_query = $db->query(" SELECT alert_final_structured_audit_details.location, alert_final_structured_audit.client_name,
                                       alert_final_structured_audit.region, SUM(weightage) AS total, SUM(CASE WHEN UPPER(audit_finding) = 'YES' THEN weightage ELSE 0 END) AS yes,
                                      SUM(CASE WHEN audit_finding = 'no' THEN weightage ELSE 0 END) AS no FROM  alert_final_structured_audit_details  
                                     LEFT JOIN alert_final_structured_audit ON  alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id
                                    WHERE alert_final_structured_audit_details.audit_template_id = 8 AND alert_final_structured_audit.region = 'North'  
                                  GROUP BY location, client_name, region");

$north_result = $north_query->getResultArray();
$data['north_score'] = $north_result;
$location_north = [];
foreach ($north_result as $row) {
    if (!isset($location_north[$row['location']])) {
        $location_north[$row['location']] = 0;
    }
    $location_north[$row['location']] += $row['yes'];
}
$data['location_north'] = $location_north;
   
        $South_query = $db->query(" SELECT alert_final_structured_audit_details.location, alert_final_structured_audit.client_name,
                                       alert_final_structured_audit.region, SUM(weightage) AS total, SUM(CASE WHEN UPPER(audit_finding) = 'YES' THEN weightage ELSE 0 END) AS yes,
                                      SUM(CASE WHEN audit_finding = 'no' THEN weightage ELSE 0 END) AS no FROM  alert_final_structured_audit_details  
                                     LEFT JOIN alert_final_structured_audit ON  alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id
                                    WHERE alert_final_structured_audit_details.audit_template_id = 8 AND alert_final_structured_audit.region = 'South'  
                                  GROUP BY location, client_name, region");

$scores_result = $South_query->getResultArray();
$data['scores'] = $scores_result;
$location_score = [];
foreach ($scores_result as $row) {
    if (!isset($location_score[$row['location']])) {
        $location_score[$row['location']] = 0;
    }
    $location_score[$row['location']] += $row['yes'];
}
$data['location_score'] = $location_score;

        $east_query = $db->query("SELECT alert_final_structured_audit_details.location, alert_final_structured_audit.client_name,
                alert_final_structured_audit.region, SUM(weightage) AS total, SUM(CASE WHEN UPPER(audit_finding) = 'YES' THEN weightage ELSE 0 END) AS yes,
                SUM(CASE WHEN audit_finding = 'no' THEN weightage ELSE 0 END) AS no FROM  alert_final_structured_audit_details  
                LEFT JOIN alert_final_structured_audit ON  alert_final_structured_audit_details.structured_audit_id = alert_final_structured_audit.structured_audit_id
                WHERE alert_final_structured_audit_details.audit_template_id = 8 AND alert_final_structured_audit.region = 'East'  
                GROUP BY location, client_name, region");

$east_result = $east_query->getResultArray();
$data['east_score'] = $east_result;

        // Calculate totals for each location
        $location_totals = [];
        foreach ($east_result as $row) {
            if (!isset($location_totals[$row['location']])) {
                $location_totals[$row['location']] = 0;
            }
            $location_totals[$row['location']] += $row['yes'];
        }
        $data['location_totals'] = $location_totals;

            
            
		return view ( "audit_template_dashboard", $data );
	}    


   
    function table_ajax(){
        $db=db_connect();
        
        //  $data['data'] = $data['userDetails'];
        // unset($data['userDetails']);
        // return $this->response->setJSON($data);
    }
}
