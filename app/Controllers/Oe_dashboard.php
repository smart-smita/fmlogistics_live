<?php

namespace App\Controllers;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;

class Oe_dashboard extends BaseController
{
    public $BaseModel=null;
    public function __construct(){
        // changes on 16/10/25 by darsh: Using simple ACL helper for regional filtering
        helper(['simple_acl']);
    }
    public function index(){
        $db=db_connect();
        
        // Multi-Cluster ACL: Support for multiple regions and clusters - 14/11/25
        helper(['designation_acl']);
        $regionFilter = '';
        if (isClusterManager() || isWHManager()) {
            $assignedRegions = getClusterManagerAssignedRegion();
            if (!empty($assignedRegions)) {
                $escapedRegions = array_map([$db, 'escape'], $assignedRegions);
                $regionFilter = " AND region IN (" . implode(',', $escapedRegions) . ")";
            }
            
            // Also apply cluster filter if data is grouped by client
            $clusterManagerFilter = getClusterFilterByClientName();
            if (!empty($clusterManagerFilter)) {
                $regionFilter .= $clusterManagerFilter;
            }
        }
        
        
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
    
   
      
        // 30/09/25 change by darsh: Client x Month now uses the latest audit per client per month
        // changes on 16/10/25 by darsh: Simplified ACL filtering for cluster managers
        $query2 = $db->query(" 
            SELECT a.client_name,
                   MONTHNAME(a.audit_date) AS audit_month,
                   ROUND((
                       SELECT SUM(CASE WHEN d.audit_finding = 'yes' THEN d.weightage ELSE 0 END)
                       FROM alert_final_structured_audit_details d
                       WHERE d.structured_audit_id = a.structured_audit_id
                   ) / NULLIF((
                       SELECT SUM(d2.weightage)
                       FROM alert_final_structured_audit_details d2
                       WHERE d2.structured_audit_id = a.structured_audit_id
                   ), 0) * 100, 0) AS yes
            FROM alert_final_structured_audit a
            JOIN (
                SELECT client_name, DATE_FORMAT(audit_date, '%Y-%m') AS ym, MAX(audit_date) AS max_date
                FROM alert_final_structured_audit
                WHERE audit_date IS NOT NULL {$regionFilter}
                GROUP BY client_name, ym
            ) m ON m.client_name = a.client_name
               AND DATE_FORMAT(a.audit_date, '%Y-%m') = m.ym
               AND a.audit_date = m.max_date
            WHERE 1=1 {$regionFilter}");
 
$audit_query = $query2->getResultArray();
$data['audit'] = $audit_query;
         
      // 30/09/25 Zone x Month now uses the latest audit per zone per month
      // changes on 16/10/25 by darsh: Simplified ACL filtering for cluster managers
      $query3 = $db->query(" 
            SELECT a.region AS zone,
                   MONTHNAME(a.audit_date) AS audit_month,
                   ROUND((
                       SELECT SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END)
                       FROM alert_final_structured_audit_details d
                       WHERE d.structured_audit_id = a.structured_audit_id
                   ) / NULLIF((
                       SELECT SUM(d2.weightage)
                       FROM alert_final_structured_audit_details d2
                       WHERE d2.structured_audit_id = a.structured_audit_id
                   ), 0) * 100, 0) AS yes
            FROM alert_final_structured_audit a
            JOIN (
                SELECT region, DATE_FORMAT(audit_date, '%Y-%m') AS ym, MAX(audit_date) AS max_date
                FROM alert_final_structured_audit
                WHERE audit_date IS NOT NULL {$regionFilter}
                GROUP BY region, ym
            ) m ON m.region = a.region
               AND DATE_FORMAT(a.audit_date, '%Y-%m') = m.ym
               AND a.audit_date = m.max_date
            WHERE 1=1 {$regionFilter}");
$zone_query = $query3->getResultArray();
$data['zone'] = $zone_query;            
      // changes on 30/09/25 by darsh: precompute zone_totals for chart as averages across months
      $zoneWeights = ['East'=>['sum'=>0,'cnt'=>0],'West'=>['sum'=>0,'cnt'=>0],'North'=>['sum'=>0,'cnt'=>0],'South'=>['sum'=>0,'cnt'=>0]];
      foreach ($zone_query as $zr) {
          $z = $zr['zone'];
          if (isset($zoneWeights[$z])) { $zoneWeights[$z]['sum'] += (float)$zr['yes']; $zoneWeights[$z]['cnt'] += 1; }
      }
      $zone_totals = [];
      foreach ($zoneWeights as $z => $agg) {
          $zone_totals[$z] = $agg['cnt'] > 0 ? round(max(0, min(100, $agg['sum'] / $agg['cnt']))) : 0;
      }
      $data['zone_totals'] = $zone_totals;
 
 // NEW CHANGE: West table now uses latest audit per client/location in West
 // changes on 16/10/25 by darsh: Simplified ACL filtering for cluster managers
 $west_query = $db->query(" 
    SELECT a.location, a.client_name, a.region AS zone,
           ROUND((
               SELECT SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END)
               FROM alert_final_structured_audit_details d
               WHERE d.structured_audit_id = a.structured_audit_id
           ) / NULLIF((
               SELECT SUM(d2.weightage)
               FROM alert_final_structured_audit_details d2
               WHERE d2.structured_audit_id = a.structured_audit_id
           ), 0) * 100, 2) AS yes
    FROM alert_final_structured_audit a
    JOIN (
        SELECT client_name, location, region, MAX(audit_date) AS max_date
        FROM alert_final_structured_audit
        WHERE 1=1 {$regionFilter}
        GROUP BY client_name, location, region
    ) m ON m.client_name = a.client_name AND m.location = a.location AND m.region = a.region AND a.audit_date = m.max_date
    WHERE 1=1 {$regionFilter}");

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
 
 
   
 // NEW CHANGE: North table now uses latest audit per client/location in North
 // changes: Show actual decimal scores instead of rounding to whole numbers
 $north_query = $db->query(" 
    SELECT a.location, a.client_name, a.region AS zone,
           ROUND((
               SELECT SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END)
               FROM alert_final_structured_audit_details d
               WHERE d.structured_audit_id = a.structured_audit_id
           ) / NULLIF((
               SELECT SUM(d2.weightage)
               FROM alert_final_structured_audit_details d2
               WHERE d2.structured_audit_id = a.structured_audit_id
           ), 0) * 100, 2) AS yes
    FROM alert_final_structured_audit a
    JOIN (
        SELECT client_name, location, region, MAX(audit_date) AS max_date
        FROM alert_final_structured_audit
        WHERE region = 'North'
        GROUP BY client_name, location, region
    ) m ON m.client_name = a.client_name AND m.location = a.location AND m.region = a.region AND a.audit_date = m.max_date
    WHERE a.region = 'North'");

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
   
            
 // NEW CHANGE: South table now uses latest audit per client/location in South
 // changes on 30/09/25 by darsh: remove hardcoded template filter, use case-insensitive YES, filter by region
 $South_query = $db->query(" 
    SELECT a.location, a.client_name, a.region AS zone, -- changes on 30/09/25 by darsh
           ROUND((
               SELECT SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END)
               FROM alert_final_structured_audit_details d
               WHERE d.structured_audit_id = a.structured_audit_id
           ) / NULLIF((
               SELECT SUM(d2.weightage)
               FROM alert_final_structured_audit_details d2
               WHERE d2.structured_audit_id = a.structured_audit_id
           ), 0) * 100, 2) AS yes
    FROM alert_final_structured_audit a
    JOIN (
        SELECT client_name, location, region, MAX(audit_date) AS max_date
        FROM alert_final_structured_audit
        WHERE region = 'South'
        GROUP BY client_name, location, region
    ) m ON m.client_name = a.client_name AND m.location = a.location AND m.region = a.region AND a.audit_date = m.max_date
    WHERE a.region = 'South'");

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



 // NEW CHANGE: East table now uses latest audit per client/location in East
 // changes: Show actual decimal scores instead of rounding to whole numbers
 $east_query = $db->query(" 
    SELECT a.location, a.client_name, a.region AS zone,
           ROUND((
               SELECT SUM(CASE WHEN UPPER(d.audit_finding) = 'YES' THEN d.weightage ELSE 0 END)
               FROM alert_final_structured_audit_details d
               WHERE d.structured_audit_id = a.structured_audit_id
           ) / NULLIF((
               SELECT SUM(d2.weightage)
               FROM alert_final_structured_audit_details d2
               WHERE d2.structured_audit_id = a.structured_audit_id
           ), 0) * 100, 2) AS yes
    FROM alert_final_structured_audit a
    JOIN (
        SELECT client_name, location, region, MAX(audit_date) AS max_date -- changes on 30/09/25 by darsh
        FROM alert_final_structured_audit
        WHERE region = 'East' -- changes on 30/09/25 by darsh
        GROUP BY client_name, location, region -- changes on 30/09/25 by darsh
    ) m ON m.client_name = a.client_name AND m.location = a.location AND m.region = a.region AND a.audit_date = m.max_date -- changes on 30/09/25 by darsh
    WHERE a.region = 'East'"); // changes on 30/09/25 by darsh

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

            
            
		return view ( "oe_dashboard", $data );
	}    


   
    function table_ajax(){
        $db=db_connect();
        
        //  $data['data'] = $data['userDetails'];
        // unset($data['userDetails']);
        // return $this->response->setJSON($data);
    }
}
