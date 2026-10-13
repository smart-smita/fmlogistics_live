<?php

//namespace App\Controllers;
namespace App\Controllers\Reports;
require APPPATH.'/ThirdParty/dompdf/autoload.inc.php';  // Adjust this path if necessary
use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Near_miss_reports extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_near_miss';
     $db['allowedFields'] = ['user_id','near_miss_category_text','self_other','other_engineer_id','other_engineer_name',
     'project_name','job_no','location','gps_location','manual_location','near_miss_date','near_miss_time','incident_person_name',
     'incident_person_designation','incident_person_age','incident_person_company','incident_person_sift','mode_of_work',
     'nature_of_damage','body_part_affect','body_part_affect_details','involved_equipment_name','description_of_occurred',
     'what_could_have_happened','causes_of_incident','immediate_action','future_occurrence','images','total_hours','total_cost',
     'status','near_miss_priority','priority_comment','rejection_comment','update_date'];
     $db['primaryKey'] = "near_miss_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $_SESSION ['active_btn'] = "Reports";
		$_SESSION ['active_tag'] = "nearmissDetails";
        $data = [];
        $tdata['title']="Near Miss Reports";
        $tdata['button_name']="Add Report";
        $tdata['button_id']="user_modal";
        
        $tdata['display_contents'] = [
            "near_miss_id" => "ID",
            "date_time" => "Date",
            "near_miss_category_text"=>"Category",
            "user_name" => "Reported by",
            "job_no" => "Job No",
            "project_name" => "project Name",
            "location" => "Location",
            "asssigned_enginer" => "Engineer Name",
            "description_of_occurred" => "Incident description",
            "status_text" => "Status",
            "action" => "Actions"
            ];
        // Wrap the description column (index 8) to prevent horizontal overflow
        $tdata['column_defs'] = '[{"targets": 8, "className": "long-text"}]';
            
         $tdata['export_button'] = '<a href="'.base_url("Reports/Near_miss_reports/print_qr").'" class="btn btn-sm btn-info text-white" target="_blank"><i class="fa fa-qrcode"></i> Public QR Code</a>';
            
         $data['ajax_url']=base_url("Reports/Near_miss_reports/save_details");
        $tdata ['ajax_url_for_data']=base_url("Reports/Near_miss_reports/table_ajax");
        $data['user_designation'] = ["Account Manager", "Cluster manager", ""];
        $data['table'] = view("Layout/table-view",$tdata);
       
        $db=db_connect();
        $data['lmra_id']=$db->table("alert_users")->get()->getResultArray();
       
        $_SESSION ['active_btn'] = "Master";
        $_SESSION ['active_tag'] = "nearmiss Category";
        $data['title']="Near Miss Reports";
        $data['task_activity']=$db->table("alert_lmra_categorys")->where(array('status'=>1))->get()->getResultArray();
        $data['nature_incident']=$db->table("alert_nature_incident")->where(array('status'=>1))->get()->getResultArray();
        $data['body_part']=$db->table("alert_body_part")->where(array('status'=>1))->get()->getResultArray();
        helper('gemba_acl');
        $authorizedLocations = near_miss_get_authorized_locations();
        $locBuilder = $db->table("alert_location_master")->orderBy('location_name', 'ASC');
        
        if ($authorizedLocations !== true && !empty($authorizedLocations)) {
            $locBuilder->whereIn('location_name', $authorizedLocations);
        } elseif ($authorizedLocations !== true && empty($authorizedLocations)) {
            $locBuilder->where('1=0');
        }
        $data['locations'] = $locBuilder->get()->getResultArray();
        // change on 29/09/25 by darsh: align user fetching with LMRA (status=1) and prefill current user
        $where['status']=1;
        if($_SESSION['role'] == 'Cluster manager')
            $where['employee_reporting_to'] = $_SESSION['login_id'] ?? 0;
        if($_SESSION['role'] == 'Higher authority')
            $where['user_emp_country']=('select user_emp_country from  alert_users where user_id=' . ($_SESSION['login_id'] ?? 0));
        
        $where['user_designation'] = 'Engineer';
        // Exclude the currently logged-in user from the "Other Engineer" dropdown
        $loggedInId = $_SESSION['login_id'] ?? 0;
        $engList = $db->table("alert_users")->where($where)->get()->getResultArray();
        $data['engineer_list'] = array_filter($engList, function($e) use ($loggedInId) {
            return $e['user_id'] != $loggedInId;
        });
        $data['user_id'] = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : null; // change on 29/09/25 by darsh
        $data['fcm_id'] = isset($_SESSION['fcm_id']) ? $_SESSION['fcm_id'] : ''; // change on 29/09/25 by darsh
        
        return view("Reports/add_nearmiss_details",$data);
    
    }
    
    public function print_qr()
    {
        $role = strtolower($_SESSION['role'] ?? '');
        $isHigherAuth = in_array($role, ['higher authority', 'higher_authority']);
        if ($isHigherAuth) {
            return redirect()->back()->with('error', 'Access Denied');
        }
        
        $data['title'] = "Print Public Near Miss QR Code";
        $data['public_url'] = base_url('public/near-miss');
        return view("Reports/print_nearmiss_qr", $data);
    }

    public function table_ajax(){
        // if($_SESSION['role']!="super_admin")
        //     $tdata['table_data'] = $this->BaseModel->where(["employee_reporting_to"=>$_SESSION['near_miss_id']])->findAll();
        // else
           // $tdata['table_data'] = $this->BaseModel->findAll();
          // Enhanced ACL condition with proper hierarchy support
          helper('gemba_acl');
          $condtion = '';
          $db = db_connect();
          
          $authorizedLocations = near_miss_get_authorized_locations();
          
          if ($authorizedLocations !== true) {
              $userId = $_SESSION['login_id'] ?? 0;
              $baseCond = "(alert_near_miss.user_id = '{$userId}' OR alert_near_miss.other_engineer_id = '{$userId}' OR FIND_IN_SET('{$userId}', alert_near_miss.group_member_ids))";
              
              if (!empty($authorizedLocations)) {
                  $locs = array_map(function($loc) use ($db) {
                      return "'" . $db->escapeString(trim($loc)) . "'";
                  }, $authorizedLocations);
                  $locString = implode(',', $locs);
                  $condtion = " AND ( {$baseCond} OR alert_near_miss.location IN ({$locString}) )";
              } else {
                  // No locations authorized, fallback to own data
                  $condtion = " AND {$baseCond}";
              }
          }
          $tdata ['table_data'] =$db->query( " select alert_near_miss.*,
          COALESCE(alert_users.user_name, IF(alert_near_miss.user_id = 0, 'Super Admin', NULL)) as user_name,
          COALESCE(alert_users.user_email, IF(alert_near_miss.user_id = 0, 'admin@fmlogistic.com', NULL)) as user_email,
          (SELECT GROUP_CONCAT(alert_users.user_name) as grou_users from 
          alert_users where find_in_set(alert_users.user_id,alert_near_miss.group_member_ids)) as asssigned_enginer,
          concat(alert_near_miss.near_miss_date,' ',alert_near_miss.near_miss_time) as date_time
          
          from alert_near_miss 
          left join alert_users on alert_users.user_id=alert_near_miss.user_id WHERE alert_near_miss.status != '6' ".$condtion)->getResultArray();
           
            $statusMessages = [                
             0 => '<span class="badge badge-danger">Pending</span>',
             1 => '<span class="badge badge-success">Verified</span>',
             4 => '<span class="badge badge-warning">Request required</span>',
             2 => '<span class="badge badge-warning">Open</span>',
             5 => '<span class="badge badge-success">Close</span>',
             3 =>'<span class="badge badge-secondary">Cancel</span>'
             ];
             $statusText = [                
             0 => 'Pending',
             1 => 'Verified',
             4 => 'Request required',
             2 => 'Open',
             5 => 'Close',
             3 => 'Cancel'
             ];
             foreach($tdata['table_data'] as $key=>$row){
                 $tdata['table_data'][$key]['status_text'] = $statusText[$row['status']] ?? '-';
                 
                 if (empty(trim($row['location'])) || trim(strtolower($row['location'])) === 'no results found') {
                     $tdata['table_data'][$key]['location'] = '-';
                 }
                 
                 // Fix for Engineer Name:
                 // If "Self" (0), the engineer is the user who reported it (user_name)
                 // If "Other Engineer" (1), the engineer is the other_engineer_name
                 if (isset($row['self_other']) && $row['self_other'] == '1') {
                     $engineerName = !empty($row['other_engineer_name']) ? trim($row['other_engineer_name']) : '-';
                 } else {
                     $engineerName = !empty($row['user_name']) ? trim($row['user_name']) : '-';
                 }
                 
                 // Ensure we never show "No results found"
                 if (strtolower($engineerName) === 'no results found' || empty($engineerName)) {
                     $engineerName = '-';
                 }
                 $tdata['table_data'][$key]['asssigned_enginer'] = $engineerName;
                 
                 // Clean up Reported by (user_name)
                 if (empty(trim($row['user_name'])) || trim(strtolower($row['user_name'])) === 'no results found') {
                     $tdata['table_data'][$key]['user_name'] = '-';
                 }
                 
                 // Remove the incorrect user_name override for self_other == 1 so "Reported by" stays accurate.
            
            // change on 29/09/25 by darsh: changed eye icon to edit icon for Edit button
            $role_original = isset($_SESSION['role']) ? $_SESSION['role'] : '';
            $role = strtolower($role_original);
            $edit = '';
            $delete = '';
            
            $isHigherAuth = in_array($role, ['higher authority', 'higher_authority']);
            
            if ($row['status'] != "5" && $row['status'] != "2" && !$isHigherAuth) {
                $edit = '<button data-ajax-url="'.base_url("Reports/Near_miss_reports/get_form_data/".$row['near_miss_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['near_miss_id'].');" title="Edit">
                                                <span class="indicator-label svg-icon svg-icon-3">
                                                    <i class="fa fa-edit"></i>
                                                </span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                                </button>
                                                ';
                                                
                $delete = '<button class="btn btn-icon btn-danger mx-1" onclick="delete_row(this);" data-ajax-url="'.base_url("Reports/Near_miss_reports/delete_report/".$row['near_miss_id']).'" title="Delete">
												<span class="indicator-label svg-icon svg-icon-3">
													<i class="fa fa-trash"></i>
												</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
												</button>
												';
            }
            $pdf = '<button onclick=\'window.location.href="'.base_url("Reports/Near_miss_reports/nearMissBrifDetailsPdf/".$row['near_miss_id']).'"\' class="btn btn-icon btn-danger" title="Pdf">
                                            <span class="indicator-label svg-icon svg-icon-3">
                                                <i class="fa fa-file-pdf"></i>
                                            </span>
                                        <span class="indicator-progress">
                                            <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                        </span>
                                            </button>
                                            ';
												 $loginas = '<button onclick=\'window.location.href="'.base_url("Reports/Near_miss_reports/nearMissChat/".$row['near_miss_id']).'"\' class="btn btn-icon btn-primary mx-1" title="Comment">
												<span class="indicator-label svg-icon svg-icon-3">
													<i class="fa fa-comments"></i>
												</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
												</button>
												';
											// 	$email = '<button onclick=\'window.location.href="'.base_url("Reports/Near_miss_reports/email/".$row['near_miss_id']).'"\' class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['near_miss_id'].');" title="Email">
													
											// 	<span class="indicator-label svg-icon svg-icon-3">
											// 		<i class="fa fa-envelope" ></i>
											// 	</span>
                                            // <span class="indicator-progress">
                                            //     <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            // </span>
											// 	</button>
											// 	';
            // change on 29/09/25 by darsh: implement single dynamic status button per workflow
            $statusAction = '';
            $cancelAction = '';
            
            if (!$isHigherAuth) {
                $canChangeStatus = false;
                if (in_array($role, ['super admin', 'super_admin', 'admin', 'auditor', 'account manager', 'account_manager', 'cluster manager', 'cluster_manager'])) {
                    $canChangeStatus = true; // Limited by backend logic based on specific locations later, but UI shows button if role is one of these
                }
                
                // Cancel button is available in 0, 1, 2, 4
                if (in_array($row['status'], ["0", "1", "2", "4"]) && $canChangeStatus) {
                    $cancelAction = '<button class="btn btn-icon btn-danger mx-1" title="Cancel" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/cancelled\',$(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-times"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                }
                
                if ($canChangeStatus) {
                    if (in_array($row['status'], ["0", "1"])) {
                        // For historical records, move to Requested
                        $statusAction = '<button class="btn btn-icon btn-primary mx-1" title="Request" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/requested\',$(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-arrow-right"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                    } elseif ($row['status'] == "4") {
                        // REQUESTED -> OPEN
                        $statusAction = '<button class="btn btn-icon btn-warning mx-1" title="Open" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/open\',$(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-folder-open"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                    } elseif ($row['status'] == "2") {
                        // OPEN -> CLOSED (handled specially via chat/closing modal, or here if we just want a simple state change. Let's just make it call close for now, but we'll disable it here and force them to do it in chat where AFTER ATTACHMENT is required? Wait! The requirement says "upload AFTER proof during closing workflow". Let's point the Close button to open the chat window, or trigger a modal. Actually, `nearMissChat` already has the details. If they click "Close" here, it might just close without proof. Let's redirect to Chat to close, or we can just keep the simple API and require chat page for closing? We can make the close button open the Chat view where closing actually happens).
                        // I will make the Close button here redirect to Chat so they can upload After Proof.
                        $statusAction = '<button class="btn btn-icon btn-success mx-1" title="Close / Upload Proof" onclick=\'window.location.href="'.base_url("Reports/Near_miss_reports/nearMissChat/".$row['near_miss_id']).'"\'><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-check-double"></i></span></button>';
                    }
                }
            }

            $tdata['table_data'][$key]['action'] = "<center>".$statusMessages[$row['status']]."<br><br>".$edit.$pdf.$loginas.$delete.$statusAction.$cancelAction."</center>";
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }
    
     public function nearMissChat($nearMissId){
            $db=db_connect();
            if(!isset($nearMissId))
                redirect(base_url('Home'));
            
        $_SESSION ['active_btn'] = "Reports";
		$_SESSION ['active_tag'] = "nearmissDetails";
		$data ['title'] = "Near Miss Chat Reports";
        $data ['display_contents'] = [
            "chat_id" => "ID",
            "user_name" => "User Name",
            "chat_text"=>"Message",
            "chat_image"=>"Attachment",
			"default_date" => "System Date"
            
        ];
        // changes on 9/10/25 by darsh: fetch Near Miss comments with LEFT JOIN and order by newest first
        $data['near_miss_chat']=$db->query(
            "SELECT c.chat_id, c.near_miss_id, c.user_id, c.chat_text, c.chat_type, c.chat_image, c.default_date,
                    COALESCE(u.user_name, CASE WHEN c.user_id = 0 THEN 'Super_admin' ELSE '' END) AS user_name,
                    COALESCE(u.user_designation, CASE WHEN c.user_id = 0 THEN 'admin' ELSE '' END) AS user_designation
             FROM alert_near_miss_chat c
             LEFT JOIN alert_users u ON u.user_id = c.user_id AND u.status = 1
             WHERE c.near_miss_id = ?
             ORDER BY c.chat_id ASC",
            [$nearMissId]
        )->getResultArray();
        $data['form_action']=base_url(index_page()."/Reports/Near_miss_reports/acceptNearMissComments");
       
        $data['login_id'] = $_SESSION['login_id'] ?? 0;
        $data['users_list'] = $db->table("alert_users")->where('status', 1)->orderBy('user_name', 'ASC')->get()->getResultArray();

		$data['details']=$db->query( " select alert_near_miss.*,
        IF(alert_near_miss.self_other = '1', (SELECT user_name FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_name, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_name,
        IF(alert_near_miss.self_other = '1', (SELECT user_email FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_email, IF(alert_near_miss.user_id = 0, 'admin@fmlogistic.com', ''))) as user_email,
        IF(alert_near_miss.self_other = '1', (SELECT user_contact FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_contact, IF(alert_near_miss.user_id = 0, '', ''))) as user_contact,
        IF(alert_near_miss.self_other = '1', (SELECT user_designation FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_designation, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_designation,
        alert_near_miss.status as status, (SELECT GROUP_CONCAT(alert_users.user_name) as grou_users from alert_users where find_in_set(alert_users.user_id,alert_near_miss.group_member_ids)) as involved_user  from alert_near_miss left join alert_users on alert_users.user_id=alert_near_miss.user_id where near_miss_id=$nearMissId")->getResultArray();
        if(count($data['details'])==0)
            return redirect()->to(base_url('Home'));

        // Location access check
        helper('gemba_acl');
        if (!near_miss_has_location_access($data['details'][0]['location'])) {
            $userId = $_SESSION['login_id'] ?? 0;
            $isCreator = ($data['details'][0]['user_id'] == $userId || $data['details'][0]['other_engineer_id'] == $userId || in_array($userId, explode(',', $data['details'][0]['group_member_ids'] ?? '')));
            if (!$isCreator) {
                return redirect()->to(base_url('Reports/Near_miss_reports'))->with('error', 'Access Denied: You do not have permission for this location.');
            }
        }
        return view('Reports/nearmiss_view_chat',$data);
    }
    public function nearMissBrifDetails($nearMissId) {
        $db =db_connect(); 
    	$where['near_miss_id']=$nearMissId;
		$data['details']=$db->query( " select alert_near_miss.*,
        IF(alert_near_miss.self_other = '1', (SELECT user_name FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_name, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_name,
        IF(alert_near_miss.self_other = '1', (SELECT user_email FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_email, IF(alert_near_miss.user_id = 0, 'admin@fmlogistic.com', ''))) as user_email,
        IF(alert_near_miss.self_other = '1', (SELECT user_contact FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_contact, IF(alert_near_miss.user_id = 0, '', ''))) as user_contact,
        IF(alert_near_miss.self_other = '1', (SELECT user_designation FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_designation, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_designation,
        alert_near_miss.status as status from alert_near_miss left join alert_users 
						     on alert_users.user_id=alert_near_miss.user_id where near_miss_id=$nearMissId" )->getResultArray();
                             
        if(count($data['details']) > 0) {
            helper('gemba_acl');
            if (!near_miss_has_location_access($data['details'][0]['location'])) {
                $userId = $_SESSION['login_id'] ?? 0;
                $isCreator = ($data['details'][0]['user_id'] == $userId || $data['details'][0]['other_engineer_id'] == $userId || in_array($userId, explode(',', $data['details'][0]['group_member_ids'] ?? '')));
                if (!$isCreator) {
                    return redirect()->to(base_url('Reports/Near_miss_reports'))->with('error', 'Access Denied: You do not have permission for this location.');
                }
            }
        }
        
		$print= " <a href=".base_url()."Reports/nearMissBrifDetailsPdf/".$nearMissId."  class='btn btn-outline-info' >Print Pdf</a>";
		$data['print']=$print;
	

    // Load the view and pass the data
    return view("Reports/nearmiss_brif_details", $data);
}

    
  public function nearMissBrifDetailsPdf($nearMissId) {
          
    $options = new Options();
    $options->set('defaultFont', 'Courier');
    $options->set('isRemoteEnabled', true);
    // Instantiate Dompdf
    $dompdf = new Dompdf($options);

    $db = db_connect();
    $where['near_miss_id']=$nearMissId;
    $data['details']=$db->table('alert_near_miss')->where($where);
    $data['details']=$db->query( " select alert_near_miss.*,
        IF(alert_near_miss.self_other = '1', (SELECT user_name FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_name, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_name,
        IF(alert_near_miss.self_other = '1', (SELECT user_email FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_email, IF(alert_near_miss.user_id = 0, 'admin@fmlogistic.com', ''))) as user_email,
        IF(alert_near_miss.self_other = '1', (SELECT user_contact FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_contact, IF(alert_near_miss.user_id = 0, '', ''))) as user_contact,
        IF(alert_near_miss.self_other = '1', (SELECT user_designation FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_designation, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_designation,
        alert_near_miss.status as status from alert_near_miss left join alert_users on alert_users.user_id=alert_near_miss.user_id where near_miss_id=$nearMissId" )->getResultArray();
        
        if(count($data['details']) > 0) {
            helper('gemba_acl');
            if (!near_miss_has_location_access($data['details'][0]['location'])) {
                $userId = $_SESSION['login_id'] ?? 0;
                $isCreator = ($data['details'][0]['user_id'] == $userId || $data['details'][0]['other_engineer_id'] == $userId || in_array($userId, explode(',', $data['details'][0]['group_member_ids'] ?? '')));
                if (!$isCreator) {
                    return redirect()->to(base_url('Reports/Near_miss_reports'))->with('error', 'Access Denied: You do not have permission for this location.');
                }
            }
        }
    // changes on 9/10/25 by darsh: load chat for PDF with LEFT JOIN to include rows even if user missing
    $data['near_miss_chat']=$db->query(
        "SELECT c.chat_id, c.near_miss_id, c.user_id, c.chat_text, c.chat_type, c.chat_image, c.default_date,
                COALESCE(u.user_name, CASE WHEN c.user_id = 0 THEN 'Super_admin' ELSE '' END) AS user_name,
                COALESCE(u.user_designation, CASE WHEN c.user_id = 0 THEN 'admin' ELSE '' END) AS user_designation
         FROM alert_near_miss_chat c
         LEFT JOIN alert_users u ON u.user_id = c.user_id
         WHERE c.near_miss_id = $nearMissId
         ORDER BY c.chat_id DESC"
    )->getResultArray();
  
  $dompdf = new Dompdf();  
    
    $html = view("Reports/nearmiss_brif_details_pdf", $data);

    // Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');
 
    if (ob_get_length()) {
            ob_end_clean();
        }

// Render the PDF
$dompdf->render();

// Stream the PDF to the browser for download
$dompdf->stream('NearMiss-'.$nearMissId.'.pdf', array("Attachment" => true));
exit();


}

    public function nearMissChatPdf($nearMissId) {
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
    
        $db = db_connect();
        $data['details'] = $db->query( " select alert_near_miss.*,
            IF(alert_near_miss.self_other = '1', (SELECT user_name FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_name, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_name,
            IF(alert_near_miss.self_other = '1', (SELECT user_email FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_email, IF(alert_near_miss.user_id = 0, 'admin@fmlogistic.com', ''))) as user_email,
            IF(alert_near_miss.self_other = '1', (SELECT user_contact FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_contact, IF(alert_near_miss.user_id = 0, '', ''))) as user_contact,
            IF(alert_near_miss.self_other = '1', (SELECT user_designation FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_designation, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_designation,
            alert_near_miss.status as status from alert_near_miss left join alert_users on alert_users.user_id=alert_near_miss.user_id where near_miss_id=?", [$nearMissId] )->getResultArray();
            
        if(count($data['details']) > 0) {
            helper('gemba_acl');
            if (!near_miss_has_location_access($data['details'][0]['location'])) {
                $userId = $_SESSION['login_id'] ?? 0;
                $isCreator = ($data['details'][0]['user_id'] == $userId || $data['details'][0]['other_engineer_id'] == $userId || in_array($userId, explode(',', $data['details'][0]['group_member_ids'] ?? '')));
                if (!$isCreator) {
                    return redirect()->to(base_url('Reports/Near_miss_reports'))->with('error', 'Access Denied: You do not have permission for this location.');
                }
            }
        }
        
        $data['near_miss_chat'] = $db->query(
            "SELECT c.chat_id, c.near_miss_id, c.user_id, c.chat_text, c.chat_type, c.chat_image, c.default_date,
                    COALESCE(u.user_name, CASE WHEN c.user_id = 0 THEN 'Super_admin' ELSE '' END) AS user_name,
                    COALESCE(u.user_designation, CASE WHEN c.user_id = 0 THEN 'admin' ELSE '' END) AS user_designation
             FROM alert_near_miss_chat c
             LEFT JOIN alert_users u ON u.user_id = c.user_id
             WHERE c.near_miss_id = ?
             ORDER BY c.chat_id ASC", [$nearMissId]
        )->getResultArray();
      
        $data['chat_members'] = [];
        if (count($data['details']) > 0 && !empty($data['details'][0]['group_member_ids'])) {
            $ids = array_filter(array_map('intval', explode(',', $data['details'][0]['group_member_ids'])));
            if (!empty($ids)) {
                $data['chat_members'] = $db->table('alert_users')
                    ->select('user_name, user_designation')
                    ->whereIn('user_id', $ids)
                    ->get()->getResultArray();
            }
        }

        $data['include_chat'] = true; // Signal view to append chat section
        $dompdf = new Dompdf();  
        $html = view("Reports/nearmiss_brif_details_pdf", $data);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        if (ob_get_length()) { ob_end_clean(); }
        $dompdf->render();
        $dompdf->stream('NearMiss-'.$nearMissId.'-With-Chat.pdf', array("Attachment" => true));
        exit();
    }
function acceptNearMissComments(){
        $db=db_connect();
        if(isset($_POST['near_miss_id'])){
            if (!isset($_SESSION['login_id'])) { return redirect()->back(); }
            $nearMissId = (int)$this->request->getVar('near_miss_id');
            $nearMissData = $db->table("alert_near_miss")->where("near_miss_id", $nearMissId)->get()->getRowArray();
            if ($nearMissData && $nearMissData['status'] == 5) {
                return redirect()->back()->with('error', 'Chat is disabled for closed records.');
            }
            $TableValues = [];
            $TableValues['chat_text'] = trim($this->request->getVar('comment_text') ?? '');
            $TableValues['near_miss_id'] = $nearMissId;
            $TableValues['user_id'] = (int)($_SESSION['login_id'] ?? 0);
            $TableValues['chat_type'] = 0; // 0=text, 2=attachment
            if (!$this->request->getVar('default_date')){
                $TableValues['default_date'] = date('Y-m-d H:i:s');
            }

            // Handle text comment first
            if (!empty($TableValues['chat_text'])) {
                $db->table("alert_near_miss_chat")->insert($TableValues);
                $TableValues['chat_text'] = ''; // Clear it so we don't insert text again with files
            }

            // Handle multiple file uploads
            $files = $this->request->getFileMultiple('chat_attachment');
            if ($files) {
                foreach ($files as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $ext = strtolower($file->getExtension());
                        $allowed = ['jpg','jpeg','png','gif','webp','pdf','doc','docx','xls','xlsx','ppt','pptx','txt','zip'];
                        if (in_array($ext, $allowed)) {
                            $newName = $file->getRandomName();
                            $uploadPath = FCPATH . 'uploads/near_miss/chat/' . $TableValues['near_miss_id'] . '/';
                            if (!is_dir($uploadPath)) {
                                mkdir($uploadPath, 0777, true);
                            }
                            $file->move($uploadPath, $newName);
                            
                            $attachmentData = [
                                'path' => 'uploads/near_miss/chat/' . $TableValues['near_miss_id'] . '/' . $newName,
                                'original_name' => $file->getClientName(),
                                'mime_type' => $file->getClientMimeType(),
                                'file_size' => $file->getSize()
                            ];
                            
                            $fileTableValues = $TableValues;
                            $fileTableValues['chat_type'] = 2; // Attachment
                            $fileTableValues['chat_image'] = json_encode($attachmentData);
                            $db->table("alert_near_miss_chat")->insert($fileTableValues);
                        }
                    }
                }
            }
            
            return redirect()->to(base_url(index_page().'/Reports/Near_miss_reports/nearMissChat/'.$TableValues['near_miss_id']));
        }
        return redirect()->back();
}

    public function downloadChatAttachment($chatId) {
        $db = db_connect();
        $chat = $db->table('alert_near_miss_chat')->where('chat_id', $chatId)->get()->getRowArray();
        if (!$chat || $chat['chat_type'] != 2 || empty($chat['chat_image'])) {
            return redirect()->back()->with('error', 'Attachment not found.');
        }
        
        $nearMissId = $chat['near_miss_id'];
        $details = $db->table('alert_near_miss')->where('near_miss_id', $nearMissId)->get()->getRowArray();
        if (!$details) return redirect()->back();
        
        // Location access check
        helper('gemba_acl');
        if (!near_miss_has_location_access($details['location'])) {
            $userId = $_SESSION['login_id'] ?? 0;
            $isCreator = ($details['user_id'] == $userId || $details['other_engineer_id'] == $userId || in_array($userId, explode(',', $details['group_member_ids'] ?? '')));
            if (!$isCreator) {
                return redirect()->back()->with('error', 'Access Denied.');
            }
        }
        
        $meta = json_decode($chat['chat_image'], true);
        if (!$meta || !isset($meta['path']) || !isset($meta['original_name'])) {
            return redirect()->back()->with('error', 'Invalid attachment data.');
        }
        
        $filePath = FCPATH . $meta['path'];
        if (file_exists($filePath)) {
            return $this->response->download($filePath, null)->setFileName($meta['original_name']);
        }
        
        return redirect()->back()->with('error', 'File does not exist on server.');
    }


        public function email($id = null)
        {
                $cc = [];
                $data = '';
                // NEW CHANGE: generate Near Miss PDF and email to the specific user's email
                if (!isset($id)) { return redirect()->back(); }
                $db = db_connect();
                $details = $db->query("select alert_near_miss.*,
                    IF(alert_near_miss.self_other = '1', (SELECT user_name FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_name, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_name,
                    IF(alert_near_miss.self_other = '1', (SELECT user_email FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_email, IF(alert_near_miss.user_id = 0, 'admin@fmlogistic.com', ''))) as user_email,
                    IF(alert_near_miss.self_other = '1', (SELECT user_contact FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_contact, IF(alert_near_miss.user_id = 0, '', ''))) as user_contact,
                    IF(alert_near_miss.self_other = '1', (SELECT user_designation FROM alert_users WHERE user_id = alert_near_miss.other_engineer_id LIMIT 1), COALESCE(alert_users.user_designation, IF(alert_near_miss.user_id = 0, 'Super Admin', ''))) as user_designation,
                    alert_near_miss.status as status from alert_near_miss left join alert_users on alert_users.user_id=alert_near_miss.user_id where near_miss_id=?", [$id])->getResultArray();
                if (count($details)===0) return redirect()->back();
                $dataArr=[];
                $dataArr['details']=$details;
                $dataArr['near_miss_chat']=$db->query( " select alert_near_miss_chat.*,alert_users.* 
		                                            from alert_near_miss_chat 
 		                                                inner join alert_users on alert_users.user_id=alert_near_miss_chat.user_id 
 		                                                    and near_miss_id=?", [$id])->getResultArray();
                $html = view("Reports/nearmiss_brif_details_pdf", $dataArr);
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4','portrait');
                $dompdf->render();
                $pdfName = 'NearMiss-'.$id.'.pdf';
                $pdfPath = APPPATH.'core/'.$pdfName;
                file_put_contents($pdfPath, $dompdf->output());
                $toEmail = $details[0]['user_email'];
                $subject = 'Near Miss Report #'.$id;
                $body = 'Please find attached the Near Miss report.';
                
                $errorMsg = null;
                // Use the centralized sendSystemEmail helper which automatically checks global Email Settings
                // $sendResult = sendSystemEmail($toEmail, $subject, $body, [$pdfPath], [], [], $errorMsg);
                $sendResult = sendSystemEmail("tikonesmita6@gmail.com", $subject, $body, [$pdfPath], [], [], $errorMsg);
                // Fix: Clean up the temporary PDF file using the actual $pdfPath
                if (file_exists($pdfPath)) { 
                    @unlink($pdfPath); 
                }
                
                if (!$sendResult && $errorMsg) {
                    log_message('error', "Near Miss email failed for ID $id: $errorMsg");
                }
                return redirect()->back();
        }
        function nearMissActions($id,$status){
                $db=db_connect();
                $response ['message'] = "fail";
                if (isset ( $id )) {
                    $temp = $db->table( "alert_near_miss")->where (array (
                            "status" => $status
                    ), array (
                            "near_miss_id" => $id 
                    ) )->get()->getResultArray();
                    if ($temp == 1) {
                        $response ['message'] = "done";
                        $response ['url'] = "";
                    }
                }
                echo json_encode ( $response );

        }
    
    public function get_form_data($id){
        $role = strtolower($_SESSION['role'] ?? '');
        if (in_array($role, ['higher authority', 'higher_authority'])) {
            $responce['status'] = "0";
            $responce['message'] = "Access Denied: Read Only.";
            echo json_encode($responce);
            return;
        }

        $responce['status'] = "0";
        $responce['message'] = "Details not found";
        if(isset($id)){
            $data = $this->BaseModel->find($id);
            if ($data) {
                // Location access check
                helper('gemba_acl');
                if (!near_miss_has_location_access($data['location'])) {
                    $userId = $_SESSION['login_id'] ?? 0;
                    $isCreator = ($data['user_id'] == $userId || $data['other_engineer_id'] == $userId || in_array($userId, explode(',', $data['group_member_ids'] ?? '')));
                    if (!$isCreator) {
                        $responce['message'] = "Access Denied: You do not have permission for this location.";
                        echo json_encode($responce);
                        return;
                    }
                }

                if ($data['status'] == '5') {
                    $responce['message'] = "Closed records cannot be edited.";
                    echo json_encode($responce);
                    return;
                }
            }

            $responce['data'] = $data;
            // print_r($responce['data']);
            // exit();
            $responce['status'] = "1";
            $responce['message'] = "Details found";
        }
        echo json_encode($responce);
    }
    public function save_details($id=null,$action=null){
        $role = strtolower($_SESSION['role'] ?? '');
        if (in_array($role, ['higher authority', 'higher_authority'])) {
            return $this->response->setJSON(['status' => '0', 'message' => 'Access Denied: Higher Authority cannot modify records.']);
        }

        $request = service('request');
        $postData = $request->getVar();
        helper('gemba_acl');
        
        // Handle pure status changes (actions from the table grid like verified, open, close, etc.)
        if(isset($id) && isset($action)) {
            $record = $this->BaseModel->find($id);
            if (!$record) {
                return $this->response->setJSON(['status' => '0', 'message' => 'Record not found.']);
            }
            
            // Location access check
            if (!near_miss_has_location_access($record['location'])) {
                $userId = $_SESSION['login_id'] ?? 0;
                $isCreator = ($record['user_id'] == $userId || $record['other_engineer_id'] == $userId || in_array($userId, explode(',', $record['group_member_ids'] ?? '')));
                if (!$isCreator) {
                    return $this->response->setJSON(['status' => '0', 'message' => 'Access Denied: You do not have permission for this location.']);
                }
            }
            
            $currentStatus = (int)$record['status'];
            $targetStatus = null;
            
            switch($action){
                case "verified": $targetStatus = 1; break;
                case "open": $targetStatus = 2; break;
                case "cancelled": $targetStatus = 3; break;
                case "requested": $targetStatus = 4; break;
                case "close": $targetStatus = 5; break;
            }
            
            $isValid = false;
            // Historical fallback to prevent stuck records
            if (in_array($currentStatus, [0, 1]) && $targetStatus === 4) $isValid = true; 
            
            // New Workflow: Requested(4) -> Open(2)
            if ($currentStatus === 4 && $targetStatus === 2) $isValid = true;
            
            // New Workflow: Open(2) -> Cancelled(3) or Closed(5)
            if ($currentStatus === 2 && in_array($targetStatus, [3, 5])) $isValid = true;
            
            // Cancel is allowed from Requested as well (per standard flows)
            if ($currentStatus === 4 && $targetStatus === 3) $isValid = true;

            // Role-based validation
            $allowedByRole = false;
            if (in_array($role, ['super admin', 'super_admin', 'admin', 'auditor'])) {
                $allowedByRole = true;
            } elseif (in_array($role, ['account manager', 'account_manager', 'cluster manager', 'cluster_manager'])) {
                // They can do transitions within their location scope (already filtered by table_ajax to see it, but we can assume if they can click it, they have some access. However, strictly speaking, they can move 4->2, 2->3, 2->5).
                if ($targetStatus === 2 && $currentStatus === 4) $allowedByRole = true;
                if (in_array($targetStatus, [3, 5]) && $currentStatus === 2) $allowedByRole = true;
            }
            
            if (!$isValid || !$allowedByRole) {
                return $this->response->setJSON(['status' => '0', 'message' => 'Invalid status transition or insufficient permissions.']);
            }
            
            if ($targetStatus !== null) {
                $updateData = [
                    'status' => $targetStatus,
                    'update_date' => date('Y-m-d H:i:s')
                ];
                if($this->BaseModel->update($id, $updateData)){
                    if ($targetStatus !== $currentStatus) {
                        helper('notification');
                        sendNearMissNotification($id, false, $currentStatus, $targetStatus);
                    }
                    return $this->response->setJSON(['status' => '1', 'message' => 'Status updated successfully']);
                }
            }
            return $this->response->setJSON(['status' => '0', 'message' => 'Failed to update status.']);
        }
        
        if(isset($postData['honeypot']))
                    {
                     unset($postData['honeypot']);   
                    }
                    
                    $is_public = false;
                    if (isset($id)) {
                        $record = $this->BaseModel->find($id);
                        if ($record && $record['source'] === 'public_qr') {
                            $is_public = true;
                        }
                    } else if (isset($postData['source']) && $postData['source'] === 'public_qr') {
                        $is_public = true;
                    }

                    if (isset($postData['self_other']) && !$is_public) {
                        if ($postData['self_other'] == '0') {
                            $postData['other_engineer_id'] = 0;
                            $postData['other_engineer_name'] = NULL;
                        } else if ($postData['self_other'] == '1') {
                            $db = db_connect();
                            $otherEngineerId = $postData['other_engineer_id'] ?? 0;
                            $validEngineer = $db->table('alert_users')->where(['user_id' => $otherEngineerId, 'status' => 1, 'user_designation' => 'Engineer'])->get()->getRowArray();
                            if (!$validEngineer) {
                                return $this->response->setJSON(['status' => '0', 'message' => 'Invalid Other Engineer selected. Only users with Engineer designation are allowed.']);
                            }
                            $postData['other_engineer_name'] = $validEngineer['user_name'];
                        }
                        if (empty($postData['user_id'])) {
                             $postData['user_id'] = $_SESSION['login_id'] ?? 0;
                        }
                    }

                    if ($is_public) {
                        // For public records, do not enforce other_engineer logic.
                        // We also shouldn't overwrite user_id if it's 0.
                        unset($postData['self_other']);
                        unset($postData['other_engineer_id']);
                    }
                   
                    $postData['nature_of_damage'] = trim($postData['nature_of_damage'] ?? '');
                    $postData['body_part_affect'] = trim($postData['body_part_affect'] ?? '');
                    $postData['body_part_affect_details'] = trim($postData['body_part_affect_details'] ?? '');
                    $postData['causes_of_incident'] = trim($postData['causes_of_incident'] ?? '');
                   
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
                    
                    // Retrieve existing images from hidden input, split into array of 2 slots
                    $existing_images = isset($postData['images']) ? explode(',', $postData['images']) : ['', ''];
                    if (count($existing_images) < 2) {
                        $existing_images = array_pad($existing_images, 2, '');
                    }

                    if(isset($_FILES['file_1']['name']) && $_FILES['file_1']['name']!=''){
                        $file_extension = pathinfo($_FILES['file_1']['name'], PATHINFO_EXTENSION);
                        if (in_array(strtolower($file_extension), $allowed_extensions)) {
                            $info = pathinfo($_FILES['file_1']['name']);
                            $ext = $info['extension']; 
                            $newname = 'logo'."-".rand().".".$ext;
                            $target = APPPATH.'../uploads/'.$newname;
                            move_uploaded_file( $_FILES['file_1']['tmp_name'], $target);
                            $existing_images[0] = base_url('/uploads/'.$newname);
                        }
                    }

                    if(isset($_FILES['file_2']['name']) && $_FILES['file_2']['name']!=''){
                        $file_extension = pathinfo($_FILES['file_2']['name'], PATHINFO_EXTENSION);
                        if (in_array(strtolower($file_extension), $allowed_extensions)) {
                            $info = pathinfo($_FILES['file_2']['name']);
                            $ext = $info['extension']; 
                            $newname = 'logo'."-".rand().".".$ext;
                            $target = APPPATH.'../uploads/'.$newname;
                            move_uploaded_file( $_FILES['file_2']['tmp_name'], $target);
                            $existing_images[1] = base_url('/uploads/'.$newname);
                        }
                    }
                    
                    // Filter out empty slots and join back together
                    $final_images = [];
                    if (!empty(trim($existing_images[0]))) $final_images[] = trim($existing_images[0]);
                    if (!empty(trim($existing_images[1]))) $final_images[] = trim($existing_images[1]);
                    
                    $postData['images'] = implode(',', $final_images);
                if(isset($id)){
                        // Validate access to existing record before updating
                        $record = $this->BaseModel->find($id);
                        if ($record) {
                            if (!near_miss_has_location_access($record['location'])) {
                                $userId = $_SESSION['login_id'] ?? 0;
                                $isCreator = ($record['user_id'] == $userId || $record['other_engineer_id'] == $userId || in_array($userId, explode(',', $record['group_member_ids'] ?? '')));
                                if (!$isCreator) {
                                    return $this->response->setJSON(['status' => '0', 'message' => 'Access Denied: You do not have permission for this location.']);
                                }
                            }
                        }
                        
                        // If they are trying to change the location, validate the NEW location
                        if (isset($postData['location']) && !near_miss_has_location_access($postData['location'])) {
                            return $this->response->setJSON(['status' => '0', 'message' => 'Access Denied: You cannot assign to this location.']);
                        }

                        $responce['message'] = "Data updation faild";
                        $postData['update_date'] = date('Y-m-d H:i:s');
                    if($this->BaseModel->update($id,$postData)){
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }else{
                    // Validate new location
                    if (isset($postData['location']) && !near_miss_has_location_access($postData['location'])) {
                        return $this->response->setJSON(['status' => '0', 'message' => 'Access Denied: You cannot create a record for this location.']);
                    }

                    $postData['status']="4"; // REQUESTED per new workflow
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                    $insertId = $this->BaseModel->insert($postData);
                    if($insertId){
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                        helper('notification');
                        sendNearMissNotification($insertId, true);
                    }
                }
                echo json_encode($responce);
    }

    public function close_near_miss() {
        $request = service('request');
        $id = $request->getPost('near_miss_id');
        $role = strtolower($_SESSION['role'] ?? '');
        $isHigherAuth = in_array($role, ['higher authority', 'higher_authority']);
        $canChangeStatus = in_array($role, ['super admin', 'super_admin', 'admin', 'auditor', 'account manager', 'account_manager', 'cluster manager', 'cluster_manager']);

        if (!$id || $isHigherAuth || !$canChangeStatus) {
            return redirect()->back()->with('error', 'Access Denied: Invalid permissions.');
        }

        $record = $this->BaseModel->find($id);
        if (!$record || $record['status'] != 2) {
            return redirect()->back()->with('error', 'Near Miss must be in OPEN status to close.');
        }

        // Location access check
        helper('gemba_acl');
        if (!near_miss_has_location_access($record['location'])) {
            $userId = $_SESSION['login_id'] ?? 0;
            $isCreator = ($record['user_id'] == $userId || $record['other_engineer_id'] == $userId || in_array($userId, explode(',', $record['group_member_ids'] ?? '')));
            if (!$isCreator) {
                return redirect()->back()->with('error', 'Access Denied: You do not have permission for this location.');
            }
        }

        $imagePaths = [];
        if ($files = $request->getFiles()) {
            if (isset($files['after_images'])) {
                foreach ($files['after_images'] as $file) {
                    if ($file->isValid() && !$file->hasMoved()) {
                        $mime = $file->getMimeType();
                        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'];
                        
                        if (in_array($mime, $allowedMimes)) {
                            $newName = $file->getRandomName();
                            $file->move(FCPATH . 'assets/media/near_miss/', $newName);
                            $imagePaths[] = 'assets/media/near_miss/' . $newName;
                        }
                    }
                }
            }
        }

        if (empty($imagePaths)) {
            return redirect()->back()->with('error', 'You must upload valid After Proof evidence to close this report.');
        }

        $updateData = [
            'status' => 5, // CLOSED
            'after_images' => implode(',', $imagePaths),
            'update_date' => date('Y-m-d H:i:s')
        ];

        if ($this->BaseModel->update($id, $updateData)) {
            helper('notification');
            sendNearMissNotification($id, false, 2, 5);
            return redirect()->to(base_url('Reports/Near_miss_reports/nearMissChat/'.$id))->with('success', 'Near Miss report successfully closed.');
        }

        return redirect()->back()->with('error', 'Failed to close report.');
    }

    // changes on 9/10/25 by darsh: add members to Near Miss chat (updates group_member_ids)
    public function add_engineer(){
        $role = strtolower($_SESSION['role'] ?? '');
        if (in_array($role, ['higher authority', 'higher_authority'])) {
            return redirect()->back()->with('error', 'Access Denied: Read Only.');
        }

        $db = db_connect();
        $request = service('request');
        $nearMissId = $request->getVar('near_miss_id');
        $selectedUsers = $request->getVar('assigned_engineer_id'); // array of user_ids
        if (!$nearMissId) { return redirect()->back(); }
        if (!is_array($selectedUsers)) { $selectedUsers = []; }

        // Fetch existing ids
        $row = $db->table('alert_near_miss')->select('group_member_ids, location, user_id, other_engineer_id')->where('near_miss_id', $nearMissId)->get()->getRowArray();
        
        if ($row) {
            // Location access check
            helper('gemba_acl');
            if (!near_miss_has_location_access($row['location'])) {
                $userId = $_SESSION['login_id'] ?? 0;
                $isCreator = ($row['user_id'] == $userId || $row['other_engineer_id'] == $userId || in_array($userId, explode(',', $row['group_member_ids'] ?? '')));
                if (!$isCreator) {
                    return redirect()->to(base_url('Reports/Near_miss_reports'))->with('error', 'Access Denied: You do not have permission for this location.');
                }
            }
        }
        
        $existing = [];
        if ($row && isset($row['group_member_ids']) && $row['group_member_ids'] !== '') {
            $existing = array_filter(array_map('trim', explode(',', $row['group_member_ids'])));
        }
        $merged = array_unique(array_filter(array_map('intval', array_merge($existing, $selectedUsers))));
        sort($merged);
        $update = [
            'group_member_ids' => implode(',', $merged),
            'group_member_count' => count($merged)
        ];
        $db->table('alert_near_miss')->where('near_miss_id', $nearMissId)->update($update);
        return redirect()->to(base_url(index_page().'/Reports/Near_miss_reports/nearMissChat/'.$nearMissId));
    }

    // changes on 9/10/25 by darsh: remove member from Near Miss chat (keeps chat history untouched)
    public function remove_engineer(){
        $role = strtolower($_SESSION['role'] ?? '');
        if (in_array($role, ['higher authority', 'higher_authority'])) {
            return redirect()->back()->with('error', 'Access Denied: Read Only.');
        }

        $db = db_connect();
        $request = service('request');
        $nearMissId = (int)($request->getVar('near_miss_id') ?? $request->getGet('near_miss_id'));
        $removeUserId = (int)($request->getVar('user_id') ?? $request->getGet('user_id'));
        if (!$nearMissId || !$removeUserId) { return redirect()->back(); }
        $row = $db->table('alert_near_miss')->select('group_member_ids, location, user_id, other_engineer_id')->where('near_miss_id', $nearMissId)->get()->getRowArray();
        
        if ($row) {
            // Location access check
            helper('gemba_acl');
            if (!near_miss_has_location_access($row['location'])) {
                $userId = $_SESSION['login_id'] ?? 0;
                $isCreator = ($row['user_id'] == $userId || $row['other_engineer_id'] == $userId || in_array($userId, explode(',', $row['group_member_ids'] ?? '')));
                if (!$isCreator) {
                    return redirect()->to(base_url('Reports/Near_miss_reports'))->with('error', 'Access Denied: You do not have permission for this location.');
                }
            }
        }
        
        $ids = [];
        if ($row && !empty($row['group_member_ids'])) {
            $ids = array_filter(array_map('intval', explode(',', $row['group_member_ids'])));
        }
        // remove only from membership list; DO NOT delete chat rows to preserve history
        $ids = array_values(array_diff($ids, [$removeUserId]));
        $update = [
            'group_member_ids' => implode(',', $ids),
            'group_member_count' => count($ids)
        ];
        $db->table('alert_near_miss')->where('near_miss_id', $nearMissId)->update($update);
        return redirect()->to(base_url(index_page().'/Reports/Near_miss_reports/nearMissChat/'.$nearMissId));
    }

    public function delete_report($id) {
        $role = strtolower($_SESSION['role'] ?? '');
        if (in_array($role, ['higher authority', 'higher_authority', 'auditor'])) {
            return $this->response->setJSON(['status' => '0', 'message' => 'Access Denied: You do not have permission to delete records.']);
        }

        $responce = ['status' => '0', 'message' => 'Failed to delete report'];
        if (isset($id)) {
            $data = $this->BaseModel->find($id);
            
            if ($data) {
                // Location access check
                helper('gemba_acl');
                if (!near_miss_has_location_access($data['location'])) {
                    $userId = $_SESSION['login_id'] ?? 0;
                    $isCreator = ($data['user_id'] == $userId || $data['other_engineer_id'] == $userId || in_array($userId, explode(',', $data['group_member_ids'] ?? '')));
                    if (!$isCreator) {
                        return $this->response->setJSON(['status' => '0', 'message' => 'Access Denied: You do not have permission for this location.']);
                    }
                }

                if ($data['status'] == '5') {
                    $responce['message'] = "Closed records cannot be deleted.";
                    return $this->response->setJSON($responce);
                }

                if ($this->BaseModel->update($id, ['status' => '6'])) {
                    $responce = ['status' => '1', 'message' => 'Report deleted successfully'];
                }
            }
        }
        return $this->response->setJSON($responce);
    }
}
