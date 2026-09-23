<?php

namespace App\Controllers\Customer;
use App\Models\CRUDBaseModel;
use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public $BaseModel=null;
    public function __construct(){

    }
    public function index(){
        $db=db_connect();
        
       
        $data['register_user']=0;
        $data['t_near_miss']=0;
        $data['pending_near_miss']=0;
        $data['close_near_miss']=0;
        $data['low_risk']=0;
        $data['medium_risk']=0;
        $data['high_risk']=0;
        // Enhanced ACL condition using proper IDs instead of names
        $condtion = '';
        if(isset($_SESSION['role']) && $_SESSION['role']=='Cluster manager'){
            // Use proper region_id and cluster_id instead of names
            $regionId = $_SESSION['region_id'] ?? '';
            $clusterId = $_SESSION['cluster_id'] ?? '';
            
            if($regionId && $clusterId) {
                $condtion = " AND alert_users.region_id = '{$regionId}' AND alert_users.cluster_id = '{$clusterId}'";
            } else {
                $condtion = ' AND 1=0'; // No access if region/cluster not set
            }
        }
        elseif(isset($_SESSION['role']) && $_SESSION['role']=='Higher authority'){
            $country = $_SESSION['country'] ?? '';
            if($country) {
                $condtion = " AND alert_users.user_emp_country = '{$country}'";
            } else {
                $condtion = ' AND 1=0';
            }
        }
        elseif(isset($_SESSION['role']) && ($_SESSION['role']=='Engineer' || $_SESSION['role']=='Auditor')){
            $userId = $_SESSION['login_id'] ?? '';
            if($userId) {
                $condtion = " AND alert_users.user_id = '{$userId}'";
            } else {
                $condtion = ' AND 1=0';
            }
        }
     
     
       $tdata['title']="User Management";
        // $tdata['button_name']="Add"; // NEW CHANGE: removed add button from Customer Dashboard
        // $tdata['button_id']="Lmra_controle_modal"; // NEW CHANGE: removed add button from Customer Dashboard
        
        $tdata['display_contents'] = [
                "user_id" => "ID",
                "user_emp_country"=>"Country",
                "user_emp_zone"=>"Region",
                "user_name"=>"Name",
                "user_email"=>"Email",
                "user_contact"=>"Contact",
                "user_designation"=>"Designation",
            ];
     
        $tdata ['ajax_url_for_data']=base_url("Customer/Dashboard/table_ajax");
        $data['user_table'] = view("Layout/table-view",$tdata);  
        $data['userDetails']=[1];
        $data['near_miss']=$db->query("SELECT count(alert_near_miss.status) as counts ,
                                                        alert_near_miss.status, 
                                                        alert_near_miss.near_miss_category_text 
                                                        FROM alert_near_miss
                                    inner join alert_users on alert_users.user_id=alert_near_miss.user_id ". $condtion ." 
                                    GROUP by alert_near_miss.near_miss_category_text ,alert_near_miss.status")->getResultArray();
                                    
            $temp=$db->table( "alert_lmra_details")->select("count(*) as counts" )->get()->getResultArray();
            $data['lmra']=$temp[0]['counts'];
            
            $hse=$db->table( "alert_hse_audit_master")->select("count(*) as counts" )->get()->getResultArray();
            $data['hse_audit']=$hse[0]['counts'];
            
            $st_audit=$db->table( "alert_final_structured_audit")->select("count(*) as counts" )->get()->getResultArray();
            $data['st_audit']=$st_audit[0]['counts'];
            
            $sixes_audit =$db->table( "alert_sixs_audit")->select("count(*) as counts" )->get()->getResultArray();
            $data['sixes_audit']=$hse[0]['counts'];
            
            $categories = $db->table('alert_hse_site_category')
            ->where('status', 1)
            ->get()
            ->getResultArray();
            
            $data['category_counts'] = [];
            $total_open = 0;

            foreach ($categories as $cat) {

                $catId = $cat['site_category_id'];
                $catName = $cat['site_category_name'];

                $result = $db->table('alert_hse_audit_details')
                    ->select('COUNT(*) as total_count')
                    ->join('alert_hse_audit_master', 'alert_hse_audit_master.hse_audit_id = alert_hse_audit_details.hse_audit_id', 'left')
                    ->where('alert_hse_audit_details.site_category', $catName)
                    ->where('alert_hse_audit_details.nc_status', '0') // optional if exists
                    ->where('alert_hse_audit_details.finding', 'NO') // optional if exists
                    ->get()
                    ->getRowArray();

                $count = $result['total_count'] ?? 0;

                $data['category_counts'][$catName] = $count;

                $total_open += $count;
            }

            $data['open_count'] = $total_open;
            
            $oe_query = $db->table('alert_final_structured_audit')
                           ->select('COUNT(*) as total_count')->get()->getResultArray();
            
            $data['oe_total_count'] = $oe_query[0]['total_count'];
            
            // where condtion is tobe set properly 
            $data['graph_count']=$db->query("SELECT COALESCE(nearmiss, 0 ) as nearmiss, COALESCE(minor, 0 ) as minor,month
                     FROM ( SELECT '1' AS MONTH UNION SELECT '2' AS MONTH UNION SELECT 'March' AS MONTH UNION SELECT 'April'
                     AS MONTH UNION SELECT 'May' AS MONTH UNION SELECT 'June' AS MONTH UNION SELECT 'July' AS MONTH UNION SELECT 'August' 
                     AS MONTH UNION SELECT 'September' AS MONTH UNION SELECT 'October' AS MONTH UNION SELECT 'November' AS MONTH UNION SELECT '12'
                     AS MONTH ) AS m LEFT join 
                     (SELECT Month(alert_near_miss.default_date)as t ,count(case WHEN near_miss_category_text='Near Miss' 
                     THEN Month(alert_near_miss.default_date) END )as nearmiss,count(case WHEN near_miss_category_text='Minor Injury' 
                     THEN Month(alert_near_miss.default_date) END )as minor FROM `alert_near_miss` inner join alert_users 
                     on alert_users.user_id=alert_near_miss.user_id ". $condtion ."  
                     where alert_near_miss.status = 0 group by Month(alert_near_miss.default_date))as b on t=MONTH")->getResultArray();

	
		$data['safe_man_days']=$db->query("SELECT DATEDIFF(CURDATE(),STR_TO_DATE(`default_date`, '%Y-%m-%d')) AS days FROM `alert_near_miss` WHERE near_miss_category_text = 'Near Miss' LIMIT 1;")->getResultArray();
            

		return view ( "Customer/dashboard" ,$data);
	}    
	
function table_ajax(){
        $db=db_connect();
        
     
// $data ['userDetails'] = $this->Base_Models->GetAllValues ( "alert_users" );
if(isset($_SESSION['role']) && $_SESSION['role']=='Cluster manager'){
        // changes on 8/10/25 by Darsh - replaced Reporting manager with Cluster manager and enforced cluster/region scope
        $data ['userDetails'] = $db->table( "alert_users")
            ->where('user_region', isset($_SESSION['region_id'])?$_SESSION['region_id']:null)
            ->where('user_cluster', isset($_SESSION['cluster_id'])?$_SESSION['cluster_id']:null)
            ->get()->getResultArray();
     }
     elseif(isset($_SESSION['role']) && $_SESSION['role']=='Higher authority'){
         $data ['userDetails'] = $db->query( "select * from alert_users where user_emp_country in ('".$_SESSION['country']."')")->getResultArray();
     }
     elseif(isset($_SESSION['role']) && $_SESSION['role']=='admin'){

        $countries = explode(',', $_SESSION['country']);  // convert string to array

$data['userDetails'] = $db->table('alert_users')
    ->whereIn('user_emp_country', $countries)
    ->get()
    ->getResultArray();

     }elseif(isset($_SESSION['role']) && $_SESSION['role']=='Engineer'){
         $data ['userDetails']=array();
     }
foreach (isset( $data ['userDetails']) ? $data ['userDetails'] : [] as $key => $val ) {
	$pending_message = '<span class="badge badge-warning">Pending</span>';
	$verified_message = '<span class="badge badge-info">Verified</span>';
	$active_message = '<span class="badge badge-success">Active</span>';
	$deactivated_message='<span class="badge badge-success">Deactive</span>';
	$data ['userDetails'] [$key] ['action'] = ($val ['status'] == 0) ? $pending_message : ($val ['status'] == 1 ? $verified_message: ($val ['status'] == 2 ? $active_message : ($val ['status'] == 3 ? $deactivated_message : "")));
	
}

        $data['data'] = isset($data['userDetails']) ? $data['userDetails'] : [];
        if(isset($data['userDetails'])){
        unset($data['userDetails']);
        }
        return $this->response->setJSON($data);
    }
}
