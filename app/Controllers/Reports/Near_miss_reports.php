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
     'status','near_miss_priority','priority_comment','priority_comment','rejection_comment'];
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
            "action" => "Actions"
            ];
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
        $data['nature_incident']=$db->table("alert_nature_incident")->where(array('status'=>2))->get()->getResultArray();
        $data['body_part']=$db->table("alert_body_part")->where(array('status'=>2))->get()->getResultArray();
        // change on 29/09/25 by darsh: align user fetching with LMRA (status=1) and prefill current user
        $where['status']=1;
        if($_SESSION['role'] == 'Cluster manager')
            $where['employee_reporting_to']=$_SESSION['login_id'];
        if($_SESSION['role'] == 'Higher authority')
            $where['user_emp_country']=('select user_emp_country from  alert_users where user_id='.$_SESSION['login_id']);
        
        $data['engineer_list']=$db->table("alert_users")->where($where)->get()->getResultArray();
        $data['user_id'] = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : null; // change on 29/09/25 by darsh
        $data['fcm_id'] = isset($_SESSION['fcm_id']) ? $_SESSION['fcm_id'] : ''; // change on 29/09/25 by darsh
        
        return view("Reports/add_nearmiss_details",$data);
    
    }
    public function table_ajax(){
        // if($_SESSION['role']!="super_admin")
        //     $tdata['table_data'] = $this->BaseModel->where(["employee_reporting_to"=>$_SESSION['near_miss_id']])->findAll();
        // else
           // $tdata['table_data'] = $this->BaseModel->findAll();
          // Enhanced ACL condition with proper hierarchy support
          $condtion = '';
          
          switch($_SESSION['role'] ?? '') {
              case 'Cluster manager':
                  // Cluster managers can only see reports from their region and cluster
                  $regionId = $_SESSION['region_id'] ?? '';
                  $clusterId = $_SESSION['cluster_id'] ?? '';
                  
                  if($regionId && $clusterId) {
                      $condtion = " AND alert_users.region_id = '{$regionId}' AND alert_users.cluster_id = '{$clusterId}'";
                  } else {
                      $condtion = ' AND 1=0';
                  }
                  break;
                  
              case 'Higher authority':
                  $country = $_SESSION['country'] ?? '';
                  if($country) {
                      $condtion = " AND alert_users.user_emp_country = '{$country}'";
                  } else {
                      $condtion = ' AND 1=0';
                  }
                  break;
                  
              case 'Account Manager':
              case 'Engineer': // Backward compatibility
              case 'Auditor':
                  $userId = $_SESSION['login_id'] ?? '';
                  if($userId) {
                      $condtion = " AND alert_near_miss.user_id = '{$userId}'";
                  } else {
                      $condtion = ' AND 1=0';
                  }
                  break;
                  
              case 'admin':
              case 'super_admin':
                  // Admins can see all data
                  break;
                  
              default:
                  $condtion = ' AND 1=0';
          }
          $db=db_connect();
          $tdata ['table_data'] =$db->query( " select alert_near_miss.*,
          alert_users.user_name,
          alert_users.user_email,
          (SELECT GROUP_CONCAT(alert_users.user_name) as grou_users from 
          alert_users where find_in_set(alert_users.user_id,alert_near_miss.group_member_ids)) as asssigned_enginer,
          concat(alert_near_miss.near_miss_date,' ',alert_near_miss.near_miss_time) as date_time
          
          from alert_near_miss 
          inner join alert_users on alert_users.user_id=alert_near_miss.user_id".$condtion)->getResultArray();
           
            $statusMessages = [                
             0 => '<span class="badge badge-danger">Pending</span>',
             1 => '<span class="badge badge-success">Verified</span>',
             4 => '<span class="badge badge-warning">Request required</span>',
             2 => '<span class="badge badge-warning">Open</span>',
             5 => '<span class="badge badge-success">Close</span>',
             3 =>'<span class="badge badge-secondary">Cancel</span>'
             ];
             foreach($tdata['table_data'] as $key=>$row){
            
            // change on 29/09/25 by darsh: changed eye icon to edit icon for Edit button
            $edit = '<button data-ajax-url="'.base_url("Reports/Near_miss_reports/get_form_data/".$row['near_miss_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['near_miss_id'].');" title="Edit">
                                                <span class="indicator-label svg-icon svg-icon-3">
                                                    <i class="fa fa-edit"></i>
                                                </span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                                </button>
                                                ';
												$pdf = '<button onclick=\'window.location.href="'.base_url("Reports/Near_miss_reports/nearMissBrifDetailsPdf/".$row['near_miss_id']).'"\' class="btn btn-icon btn-primary" title="Pdf">
												<span class="indicator-label svg-icon svg-icon-3">
													<i class="fa fa-file-pdf"></i>
												</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
												</button>
												';
												 $loginas = '<button onclick=\'window.location.href="'.base_url("Reports/Near_miss_reports/nearMissChat/".$row['near_miss_id']).'"\' class="btn btn-icon btn-primary" title="Comment">
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
            if ($row['status'] == "0") {
                // show green verify -> sets to verified (1)
                $statusAction = '<button class="btn btn-icon btn-success" title="Verify" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/verified\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-check"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-warning";
            } elseif ($row['status'] == "1") {
                // show yellow open -> sets to open (2)
                $statusAction = '<button class="btn btn-icon btn-warning" title="Open" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/open\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-check"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            } elseif ($row['status'] == "2") {
                // show red cancel (lock) -> sets to cancelled (3)
                $statusAction = '<button class="btn btn-icon btn-danger" title="Cancel" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/cancelled\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-lock"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            } elseif ($row['status'] == "3") {
                // show yellow requested (lock) -> sets to requested (4)
                $statusAction = '<button class="btn btn-icon btn-warning" title="Requested" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/requested\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-lock"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            } elseif ($row['status'] == "4") {
                // show close (double check) -> sets to close (5)
                $statusAction = '<button class="btn btn-icon btn-success" title="Close" onclick="url_call_ajax(\''.base_url("Reports/Near_miss_reports/save_details/".$row['near_miss_id']).'/close\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-check-double"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            } else {
                // status 5: close -> no action button
                $statusAction = '';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            }

            $tdata['table_data'][$key]['action'] = $row['status']."<center>".$statusMessages[$row['status']]."<br><br>".$edit.$pdf.$loginas.$statusAction."</center>";
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
             LEFT JOIN alert_users u ON u.user_id = c.user_id
             WHERE c.near_miss_id = ?
             ORDER BY c.chat_id DESC",
            [$nearMissId]
        )->getResultArray();
        $data['form_action']=base_url(index_page()."/Reports/Near_miss_reports/acceptNearMissComments");
       
        $data['login_id']=$_SESSION['login_id'];
        $data ['users_list'] = $db->table( "alert_users" )->get()->getResultArray();

		$data['details']=$db->query( " select alert_near_miss.*,alert_users.*, (SELECT GROUP_CONCAT(alert_users.user_name) as grou_users from alert_users where find_in_set(alert_users.user_id,alert_near_miss.group_member_ids)) as involved_user  from alert_near_miss inner join alert_users on alert_users.user_id=alert_near_miss.user_id and near_miss_id=$nearMissId")->getResultArray();
        if(count($data['details'])==0)
            redirect(base_url('Home'));
        return view('Reports/nearmiss_view_chat',$data);
    }
    public function nearMissBrifDetails($nearMissId) {
        $db =db_connect(); 
    	$where['near_miss_id']=$nearMissId;
		$data['details']=$db->query( " select alert_near_miss.*,alert_users.* from alert_near_miss inner join alert_users 
						     on alert_users.user_id=alert_near_miss.user_id and near_miss_id=$nearMissId" )->getResultArray();
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
    $data['details']=$db->query( " select alert_near_miss.*,alert_users.* from alert_near_miss inner join alert_users on alert_users.user_id=alert_near_miss.user_id and near_miss_id=$nearMissId" )->getResultArray();
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

// Output the generated PDF (force download)
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="NearMiss-'.$nearMissId.'.pdf"');

// Stream the PDF to the browser
echo $dompdf->output();


}
function acceptNearMissComments(){
    $db=db_connect();
        // changes on 9/10/25 by darsh: take user from session, set timestamp, and save text comment
        if(isset($_POST['comment_text']) && isset($_POST['near_miss_id'])){
            $TableValues = [];
            $TableValues['chat_text'] = trim($this->request->getVar('comment_text'));
            $TableValues['near_miss_id'] = (int)$this->request->getVar('near_miss_id');
            $TableValues['user_id'] = isset($_SESSION['login_id']) ? (int)$_SESSION['login_id'] : (int)$this->request->getVar('user_id');
            $TableValues['chat_type'] = 0; // 0=text, 1=image
            if (!$this->request->getVar('default_date')){
                $TableValues['default_date'] = date('Y-m-d H:i:s');
            }
            $db->table("alert_near_miss_chat")->insert($TableValues);
            return redirect()->to(base_url(index_page().'/Reports/Near_miss_reports/nearMissChat/'.$TableValues['near_miss_id']));
        }
        return redirect()->back();
}

public function email($id = null)
      {
                $cc = [];
                $data = '';
                // NEW CHANGE: generate Near Miss PDF and email to the specific user's email
                if (!isset($id)) { return redirect()->back(); }
                $db = db_connect();
                $details = $db->query(" select alert_near_miss.*,alert_users.* from alert_near_miss inner join alert_users on alert_users.user_id=alert_near_miss.user_id and near_miss_id=?", [$id])->getResultArray();
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
                $sendResult = $this->send($toEmail, $details[0]['user_name'], null, $subject, $body, 'ALert', true, $pdfName);
                if ($sendResult === 1 && file_exists(__DIR__.'/'.$pdfName)) { @unlink(__DIR__.'/'.$pdfName); }
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
        $responce['status'] = "0";
        $responce['message'] = "Details not found";
        if(isset($id)){

            $responce['data'] = $this->BaseModel->find($id);
            // print_r($responce['data']);
            // exit();
            $responce['status'] = "1";
            $responce['message'] = "Details found";
        }
        echo json_encode($responce);
    }
    public function save_details($id=null,$action=null){
                $request = service('request');
                $postData = $request->getVar();
                if(isset($postData['honeypot']))
                    {
                     unset($postData['honeypot']);   
                    }
                   
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                 if(isset($_FILES['file_1']['name']) && $_FILES['file_1']['name']!=''){
                    
                    $file_extension = pathinfo($_FILES['file_1']['name'], PATHINFO_EXTENSION);
                    
                    if (in_array(strtolower($file_extension), $allowed_extensions)) {
                        $info = pathinfo($_FILES['file_1']['name']);
                        $ext = $info['extension']; 
                        $newname = 'logo'."-".rand().".".$ext;
                    
                        $target = APPPATH.'../uploads/'.$newname;
                        move_uploaded_file( $_FILES['file_1']['tmp_name'], $target);
                        $postData['images']=base_url('/uploads/'.$newname);

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
                        $postData['images'].=','.base_url('/uploads/'.$newname);
                     }
                    }
                if(isset($id)){
                        $responce['message'] = "Data updation faild";
                        if(isset($action)){
                            switch($action){
                                case "open":
                                    $postData['status']= "2";
                                    break;
                                case "verified":
                                    $postData['status']= "1";
                                    break;
                                case "cancelled":
                                    $postData['status']= "3";
                                    break;
                                case "pending":
                                    $postData['status']= "0";
                                    break;
                                case "close":
                                    $postData['status']= "5";
                                    break;
                                case "requested":
                                    $postData['status']= "4";
                                    break;
                            }
                        }
                    if($this->BaseModel->update($id,$postData)){
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }else{
                    $postData['status']="1";
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                    if($this->BaseModel->insert($postData)){
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }
                echo json_encode($responce);
    }

    // changes on 9/10/25 by darsh: add members to Near Miss chat (updates group_member_ids)
    public function add_engineer(){
        $db = db_connect();
        $request = service('request');
        $nearMissId = $request->getVar('near_miss_id');
        $selectedUsers = $request->getVar('assigned_engineer_id'); // array of user_ids
        if (!$nearMissId) { return redirect()->back(); }
        if (!is_array($selectedUsers)) { $selectedUsers = []; }

        // Fetch existing ids
        $row = $db->table('alert_near_miss')->select('group_member_ids')->where('near_miss_id', $nearMissId)->get()->getRowArray();
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
        $db = db_connect();
        $request = service('request');
        $nearMissId = (int)($request->getVar('near_miss_id') ?? $request->getGet('near_miss_id'));
        $removeUserId = (int)($request->getVar('user_id') ?? $request->getGet('user_id'));
        if (!$nearMissId || !$removeUserId) { return redirect()->back(); }
        $row = $db->table('alert_near_miss')->select('group_member_ids')->where('near_miss_id', $nearMissId)->get()->getRowArray();
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
}
