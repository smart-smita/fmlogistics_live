<?php

//namespace App\Controllers;
namespace App\Controllers\Reports;
require APPPATH.'/ThirdParty/dompdf/autoload.inc.php';  // Adjust this path if necessary
use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Lmra_reports extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;
 
 
   public function __construct(){
       
     helper("form");
     $db = null;
     $db['table']         = 'alert_lmra_details';
     $db['allowedFields'] = ["user_id","self_other","other_engineer_id","other_engineer_name","lmra_project_name","lmra_job_no",
                            "lmra_location","lmra_visit_date","lmra_visit_time","lmra_json_data","task_to_be_perform","sta_data","rejection_comment","status"];
     $db['primaryKey'] = "lmra_details_id";
     $this->BaseModel = new CRUDBaseModel($db);
    //   $_SESSION['role']='admin';
    }
   
    public function index()
    {
        // echo"<pre>";
        // print_r($_SESSION);
        // exit();
        $_SESSION ['active_btn'] = "Reports";
		$_SESSION ['active_tag'] = "lmraView";
        
        $data = [];
        $tdata['title']="Lmra Reports";
        $tdata['button_name']="Add Reports";
        $tdata['button_id']="user_modal";
        
        $tdata['display_contents'] = [
            "lmra_details_id" => "ID",
            "date_time" => "Date",
            "user_name" => "Reported by",
            "assessment_for" => "Assessment For",
            "engineer_name" => "Engineer Name",
            "lmra_job_no" => "Job No",
            "lmra_project_name" => "Customer Name",
            "lmra_location" => "Location",
            "action" => "Actions"  
            ];
        // Wrap Customer Name column (index 6) and Location (index 7) to prevent overflow
        $tdata['column_defs'] = '[{"targets": [6,7], "className": "long-text"}]';
        $data['ajax_url']=base_url("Reports/Lmra_reports/save_details");
        $tdata ['ajax_url_for_data']=base_url("Reports/Lmra_reports/table_ajax");
        $data['user_designation'] = ["Account Manager","Reporting manager",""];
        $data['table'] = view("Layout/table-view",$tdata);
        
        $db= db_connect();
                
        // $data['lmra_categorys']=$this->Base_Models->GetAllValues('alert_lmra_categorys', array('status'=>2));
        $data['lmra_id']=$db->table("alert_lmra")->get()->getResultArray();
        
        $_SESSION ['active_btn'] = "Master";
        $_SESSION ['active_tag'] = "lmra Category";
        $data['title']="Lmra Reports";
        // $data ['done_message'] = "lmras" . ($id == null ? "Added..." : "Updated...");
        $data['lmra_categorys'] = $db->table("alert_lmra_categorys")->where(array('status'=>2))->get()->getResultArray();
        $data['lmra_list']=$db->table("alert_lmra")->where(array('status'=>2))->get()->getResultArray();
        $active_questions = $db->query("SELECT * FROM `alert_lmra` WHERE `status` = '1'")->getResultArray();
        
        $grouped_lmra = [];
        foreach ($active_questions as $q) {
            $cat_id = $q['lmra_category_id'];
            if (!isset($grouped_lmra[$cat_id])) {
                $grouped_lmra[$cat_id] = [
                    'lmra_category_id' => $cat_id,
                    'lmra_category_text' => $q['lmra_category_text'],
                    'title1' => 'Yes',
                    'title2' => 'No',
                    'title3' => 'NA',
                    'title4' => 'Severity',
                    'questions' => []
                ];
            }
            $grouped_lmra[$cat_id]['questions'][] = [
                'lmra_id' => $q['lmra_id'],
                'title1' => 'Yes',
                'titlew' => 'No',
                'title3' => 'NA',
                'title4' => 'Severity',
                'lmra_text' => $q['lmra_text'],
                'lmra_options' => $q['lmra_options']
            ];
        }
        
        $data['lmra_data'] = [];
        foreach ($grouped_lmra as $group) {
            $group['lmra_data'] = json_encode($group['questions']);
            $data['lmra_data'][] = $group;
        }
        $where['status']=1;
                if($_SESSION['role'] == 'Cluster manager')
                    $where['employee_reporting_to'] = $_SESSION['login_id'] ?? 0;
                if($_SESSION['role'] == 'Higher authority')
                    $where['user_emp_country']=('select user_emp_country from  alert_users where user_id=' . ($_SESSION['login_id'] ?? 0));       

        // change on 29/09/25 by darsh: prefill current user in LMRA form
        // Exclude the currently logged-in user from the "Other Engineer" dropdown
        $loggedInId = $_SESSION['login_id'] ?? 0;
        $engList = $db->table("alert_users")->where($where)->get()->getResultArray();
        $data['engineer_list'] = array_filter($engList, function($e) use ($loggedInId) {
            return $e['user_id'] != $loggedInId;
        });
        $data['all_engineers'] = $db->table("alert_users")->where(['status' => 1, 'user_designation' => 'Engineer'])->where('user_id !=', $loggedInId)->get()->getResultArray();
        $data['user_id'] = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : null;
        $data['fcm_id'] = isset($_SESSION['fcm_id']) ? $_SESSION['fcm_id'] : '';

        // $data['table'] ="";
        return view("Reports/add_lmra_reports",$data);
    }
    public function table_ajax(){
        $db = db_connect();
        $condtion = "";
        if(isset($_SESSION['role'])) {
            if ($_SESSION['role'] == 'Cluster manager') {
                $condtion = " AND alert_users.employee_reporting_to = " . $db->escape($_SESSION['login_id']);
            } elseif ($_SESSION['role'] == 'Higher authority') {
                $condtion = " AND alert_users.user_emp_country IN ('" . $_SESSION['country'] . "')";
            } elseif ($_SESSION['role'] == 'Account Manager') {
                $condtion = " AND alert_lmra_details.user_id = " . $db->escape($_SESSION['login_id']);
            }
        }
        
        $tdata['table_data'] = $db->query("select alert_lmra_details.*, alert_users.user_name, alert_users.user_email,
        concat(alert_lmra_details.lmra_visit_date,' ',alert_lmra_details.lmra_visit_time) as date_time from
        alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id WHERE alert_lmra_details.status != '6' " . $condtion . " ORDER BY alert_lmra_details.lmra_details_id DESC")->getResultArray();
        
        $statusMessages = [                
             0 => '<span class="badge badge-warning">Pending</span>',
             1 => '<span class="badge badge-info">Verified</span>',
             2 => '<span class="badge badge-primary">Open</span>',
             3 => '<span class="badge badge-danger">Cancelled</span>',
             4 => '<span class="badge badge-dark">Request Required</span>',
             5 => '<span class="badge badge-success">Closed</span>'
        ];
        
        foreach($tdata['table_data'] as $key=>$row){
            
            $tdata['table_data'][$key]['assessment_for'] = isset($row['self_other']) && $row['self_other'] == '1' ? 'Other Engineer' : 'Self';
            $tdata['table_data'][$key]['engineer_name'] = isset($row['self_other']) && $row['self_other'] == '1' ? ($row['other_engineer_name'] ?? '-') : $row['user_name'];

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
            // change on 29/09/25 by darsh: changed eye icon to edit icon for Edit button
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
            $edit = '';
            $delete = '';
            
            if ($row['status'] != "5" || $role == 'super_admin' || $role == 'super admin') {
                $edit = '<button type="button" data-ajax-url="'.base_url("Reports/Lmra_reports/get_form_data/".$row['lmra_details_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['lmra_details_id'].');" title="Edit">
                                                <span class="indicator-label svg-icon svg-icon-3">
                                                    <i class="fa fa-edit"></i>
                                                </span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                                </button>
                                                ';
                                                
                                        $delete = '<button type="button" class="btn btn-icon btn-danger mx-1" onclick="delete_row(this);" data-ajax-url="'.base_url("Reports/Lmra_reports/delete_report/".$row['lmra_details_id']).'" title="Delete">
												<span class="indicator-label svg-icon svg-icon-3">
													<i class="fa fa-trash"></i>
												</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
												</button>
												';
                        }
            
            $pdf = '<button type="button" onclick=\'window.location.href="'.base_url("Reports/Lmra_reports/lmraViewDetailsPdf/".$row['lmra_details_id']).'"\' class="btn btn-icon btn-danger" title="Pdf">
                                    <span class="indicator-label svg-icon svg-icon-3">
                                        <i class="fa fa-file-pdf"></i>
                                    </span>
                                <span class="indicator-progress">
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
                                    </button>
                                    ';
												 $loginas = '<button type="button" onclick=\'window.location.href="'.base_url("Reports/Lmra_reports/lmraComment/".$row['lmra_details_id']).'"\' class="btn btn-icon btn-primary mx-1" title="Comment">
												<span class="indicator-label svg-icon svg-icon-3">
													<i class="fa fa-comments"></i>
												</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
												</button>
												';
											// 	$email = '<button onclick=\'window.location.href="'.base_url("Reports/Lmra_reports/email/".$row['lmra_details_id']).'"\' class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['lmra_details_id'].');" title="Email">
													
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
                $tdata['table_data'][$key]['tr_class'] = "bg-light-warning";
                if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Account Manager', 'Cluster manager', 'Engineer'])) {
                    // Verify
                    $statusAction .= '<button class="btn btn-icon btn-success mx-1" title="Verify" onclick="change_lmra_status(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/verified\', \'Verify\', $(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-check"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                }
                if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Engineer'])) {
                    // Cancel
                    $statusAction .= '<button class="btn btn-icon btn-danger mx-1" title="Cancel" onclick="change_lmra_status_with_comment(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/cancelled\', \'Cancel\', \'Cancellation Reason *\', $(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-ban"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                }
            } elseif ($row['status'] == "1") {
                $tdata['table_data'][$key]['tr_class'] = "bg-light-info";
                if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Account Manager', 'Cluster manager', 'Engineer'])) {
                    // Open
                    $statusAction .= '<button class="btn btn-icon btn-primary mx-1" title="Open" onclick="change_lmra_status(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/open\', \'Open\', $(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-folder-open"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                    // Reject (reverts to status 0)
                    $statusAction .= '<button class="btn btn-icon btn-warning mx-1" title="Reject" onclick="change_lmra_status_with_comment(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/reject\', \'Reject\', \'Rejection Reason *\', $(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-times"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                }
            } elseif ($row['status'] == "2") {
                $tdata['table_data'][$key]['tr_class'] = "bg-light-primary";
               
                if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Engineer'])) {
                    // Close
                    $statusAction .= '<button type="button" class="btn btn-icon btn-success mx-1" title="Close" onclick="change_lmra_status_with_comment(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/close\', \'Close\', \'Closure Comment / Details *\', $(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-check-double"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                }
                 if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Account Manager', 'Cluster manager', 'Engineer'])) {
                    // Reject (reverts to status 0)
                    $statusAction .= '<button type="button" class="btn btn-icon btn-warning mx-1" title="Reject" onclick="change_lmra_status_with_comment(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/reject\', \'Reject\', \'Rejection Reason *\', $(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-times"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                }
            } elseif ($row['status'] == "3") {
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
                if (in_array($role, ['super_admin', 'Engineer'])) {
                    // Set Pending
                    $statusAction .= '<button type="button" class="btn btn-icon btn-warning mx-1" title="Set Pending" onclick="change_lmra_status(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/pending\', \'Set Pending\', $(this));"><span class="indicator-label svg-icon svg-icon-2"><i class="fa fa-undo"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span></button>';
                }
            } elseif ($row['status'] == "4") {
                $tdata['table_data'][$key]['tr_class'] = "bg-light-dark";
            } elseif ($row['status'] == "5") {
                $tdata['table_data'][$key]['tr_class'] = "bg-light-success";
            }

            $tdata['table_data'][$key]['action'] = "<center>".$statusMessages[$row['status']]."<br><br>".$edit.$pdf.$loginas.$delete.$statusAction."</center>";

        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }
    
    public function search()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('alert_location_master')->where('status',2);

        $keyword = $this->request->getGet('query'); // Get the search query

        if (!empty($keyword)) {
            $builder->like('location_name', $keyword);
        }

        $query = $builder->get();
        $results = $query->getResultArray();

        return $this->response->setJSON($results);
    }
     public function searchAuditor()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('alert_users');

        $keyword = $this->request->getGet('user_name'); // Get the search query

        if (!empty($keyword)) {
            $builder->like('user_name', $keyword);
        }

        $query = $builder->get();
        $results = $query->getResultArray();

        return $this->response->setJSON($results);
    }
    
     function lmraView($id=null){
         $db=db_connect();
        if(isset($id)){
            $where['lmra_details_id']=$id;
            $data ['details'] = $db->query( " select alert_lmra_details.*,alert_users.*, alert_lmra_details.status as status from alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id and lmra_details_id=$id" )->getResultArray();
			$print= " <a href=".base_url()."Reports/lmraViewDetailsPdf/".$id." class='btn btn-outline-info' >Print Pdf</a>";
		    $data['print']=$print;
            return view("Reports/lmra_view_details_pdf",$data);
        }
    }
    	
    public function lmraViewDetailsPdf($lmraId){ 
        if (empty($lmraId) || !is_numeric($lmraId)) {
            return redirect()->back();
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans'); // Set to DejaVu Sans for Unicode checkboxes
        $dompdf = new Dompdf($options);

        $db = db_connect();
        
        $data['details'] = $db->query(
            "SELECT alert_lmra_details.*, alert_users.*, alert_lmra_details.status as status FROM alert_lmra_details INNER JOIN alert_users ON alert_users.user_id = alert_lmra_details.user_id WHERE alert_lmra_details.lmra_details_id = ?", 
            [$lmraId]
        )->getResultArray();

        if (empty($data['details'])) {
            return redirect()->back();
        }
               
                // changes on 9/10/25 by darsh: PDF comments via explicit SQL to match DB columns
                // changes on 9/10/25 by darsh: use LEFT JOIN + parameter binding for PDF data
                $data['lmra_comments'] = $db->query(
                    "SELECT c.comment_id, c.lmra_id, c.user_id, c.comment_text, c.comment_type, c.default_date, 
                    COALESCE(u.user_name, CASE WHEN c.user_id = 0 THEN 'Super_admin' ELSE '' END) AS user_name,
                    COALESCE(u.user_designation, CASE WHEN c.user_id = 0 THEN 'admin' ELSE '' END) AS user_designation,
                    u.user_email 
                    FROM alert_lmra_comments c LEFT JOIN alert_users u ON u.user_id = c.user_id WHERE c.lmra_id = ? ORDER BY c.comment_id DESC",
                    [$lmraId]
                )->getResultArray();
                // changes on 9/10/25 by darsh: removed fallback to unknown column c.lmra_details_id (PDF path)
                $html = view("Reports/lmra_view_details_pdf", $data);

                // Load HTML into Dompdf
                $dompdf->loadHtml($html);

                // Set paper size and orientation
                $dompdf->setPaper('A4', 'portrait');

                // Render the PDF
                $dompdf->render();

                    if (ob_get_length()) {
                        ob_end_clean();
                    }

                // Stream the PDF to the browser for download
                $dompdf->stream('LMRA-'.$lmraId.'.pdf', array("Attachment" => true));
                exit();

        }
        
    public function email($id = null){
                $cc = [];
                $data = '';
                // NEW CHANGE: generate LMRA PDF and email to the specific user's email
                if (!isset($id)) {
                    return redirect()->back();
                }
                $db = db_connect();
                $details = $db->query(" select alert_lmra_details.*,alert_users.*, alert_lmra_details.status as status from alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id and lmra_details_id=?", [$id])->getResultArray();
                if (count($details) === 0) return redirect()->back();
                $dataArr = [];
                $dataArr['details'] = $details;
                // changes on 9/10/25 by darsh: email/PDF comments via explicit SQL to match DB columns
                // changes on 9/10/25 by darsh: use LEFT JOIN + parameter binding for email/PDF
                $dataArr['lmra_comments'] = $db->query(
                    "SELECT c.comment_id, c.lmra_id, c.user_id, c.comment_text, c.comment_type, c.default_date, 
                    COALESCE(u.user_name, CASE WHEN c.user_id = 0 THEN 'Super_admin' ELSE '' END) AS user_name,
                    COALESCE(u.user_designation, CASE WHEN c.user_id = 0 THEN 'admin' ELSE '' END) AS user_designation,
                    u.user_email 
                    FROM alert_lmra_comments c LEFT JOIN alert_users u ON u.user_id = c.user_id WHERE c.lmra_id = ? ORDER BY c.comment_id DESC",
                    [$id]
                )->getResultArray();
                // changes on 9/10/25 by darsh: removed fallback to unknown column c.lmra_details_id (email path)

                $html = view("Reports/lmra_view_details_pdf", $dataArr); // NEW CHANGE
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4','portrait');
                $dompdf->render();
                $pdfName = 'LMRA-'.$id.'.pdf';
                $pdfPath = APPPATH.'core/'.$pdfName;
                file_put_contents($pdfPath, $dompdf->output());

                $toEmail = $details[0]['user_email'];
                $subject = 'LMRA Report #'.$id;
                $body = 'Please find attached the LMRA report.';
                
                // Fetch CC users (Auditor, Higher authority, Account Manager) matching the user's country
                $userCountry = $details[0]['user_emp_country'];
                if (!empty($userCountry)) {
                    $ccUsers = $db->table('alert_users')
                                  ->where('user_emp_country', $userCountry)
                                  ->whereIn('user_designation', ['Higher authority', 'Higher Authority', 'Auditor', 'Account Manager', 'Account manager'])
                                  ->get()->getResultArray();
                    foreach ($ccUsers as $ccUser) {
                        if (!empty($ccUser['user_email'])) {
                            $cc[] = $ccUser['user_email'];
                        }
                    }
                }
                
                helper('email_service');
                // $sendResult = sendSystemEmail($toEmail, $subject, $body, [$pdfPath], $cc);
                $sendResult = sendSystemEmail("tikonesmita6@gmail.com", $subject, $body, [$pdfPath]);
                
                if ($sendResult && file_exists($pdfPath)) { @unlink($pdfPath); }
                //return redirect()->back();
    }
 public function lmraComment($lmraId=null){
        $db=db_connect();
        if(isset($lmraId)){
            $where['lmra_details_id']=$lmraId;
            $data['login_id'] = $_SESSION['login_id'] ?? 0;
            $data ['details'] = $db->query( " select alert_lmra_details.*,alert_users.user_id,alert_users.user_contact,alert_users.user_email,alert_users.user_name,alert_users.user_designation, alert_lmra_details.status as status,
            (select count(*) from alert_lmra_comments where alert_lmra_comments.lmra_id=alert_lmra_details.lmra_details_id) as comment_count,

            (select count(counts) from 
             ( select alert_lmra_comments.user_id as counts ,alert_lmra_comments.lmra_id as lmra_id from alert_lmra_comments where alert_lmra_comments.lmra_id=".$lmraId." group by alert_lmra_comments.user_id
             ) tt where tt.lmra_id=alert_lmra_details.lmra_details_id 
            ) as user_count
                        from alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id and lmra_details_id=$lmraId" )->getResultArray();
                        // changes on 9/10/25 by darsh: fetch LMRA comments with explicit SQL (ensures correct binding)
                        // changes on 9/10/25 by darsh: fetch comments with LEFT JOIN and parameter binding
                        $data['lmra_comments'] = $db->query(
                            "SELECT c.comment_id, c.lmra_id, c.user_id, c.comment_text, c.comment_type, c.default_date, 
                            COALESCE(u.user_name, CASE WHEN c.user_id = 0 THEN 'Super_admin' ELSE '' END) AS user_name,
                            COALESCE(u.user_designation, CASE WHEN c.user_id = 0 THEN 'admin' ELSE '' END) AS user_designation,
                            u.user_email 
                            FROM alert_lmra_comments c LEFT JOIN alert_users u ON u.user_id = c.user_id WHERE c.lmra_id = ? ORDER BY c.comment_id DESC",
                            [$lmraId]
                        )->getResultArray();
                        // changes on 9/10/25 by darsh: removed fallback to unknown column c.lmra_details_id
                        $data ['form_action'] =base_url(index_page()."/Reports/Lmra_reports/acceptLMRAComments");
                        // if($_SESSION['role']=='Engineer'){
                        //     $data ['form_action'] =base_url(index_page()."/Lmra_form/acceptLMRAComments");
                        // }
                        $data['button_id']="test";
                        $data['table']="";
                        return view("Reports/lmra_view_comments",$data);
                      
                    }


    }

function acceptLMRAComments(){
    $db=db_connect();
        // changes on 9/10/25 by darsh: sanitize inputs, set defaults, and redirect to the same comments page
        if(isset($_POST['comment_text']) && isset($_POST['lmra_id'])){
            $lmraId = (int)$this->request->getVar('lmra_id');
            $lmraData = $db->table("alert_lmra_details")->where("lmra_details_id", $lmraId)->get()->getRowArray();
            if ($lmraData && $lmraData['status'] == 5) {
                return redirect()->back()->with('error', 'Chat is disabled for closed records.');
            }
            $TableValues = [];
            $TableValues['comment_text'] = trim($this->request->getVar('comment_text'));
            $TableValues['lmra_id'] = (int)$this->request->getVar('lmra_id');
            // changes on 9/10/25 by darsh: take user_id from current session (alert_users), ignore client-provided value
            $TableValues['user_id'] = isset($_SESSION['login_id']) ? (int)$_SESSION['login_id'] : (int)$this->request->getVar('user_id');
            $TableValues['comment_type'] = 0; // 0=text, 1=image
            if (!$this->request->getVar('default_date')){
                $TableValues['default_date'] = date('Y-m-d H:i:s');
            }
            $db->table("alert_lmra_comments")->insert($TableValues);
            return redirect()->to(base_url('Reports/Lmra_reports/lmraComment/'.$TableValues['lmra_id']));
        }
        return redirect()->back();
    }

function lmraAction($id,$status){
    $db=db_connect();
        $response ['message'] = "fail";
		if (isset ( $id )) {
			$temp = $db->table("alert_lmra_details")->where(array (
					"status" => $status
			), array (
					"lmra_details_id" => $id 
			) );
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
            $data = $this->BaseModel->find($id);
            if ($data) {
                // If closed, only super_admin can get form data for editing
                $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
                if ($data['status'] == '5' && $role != 'super_admin' && $role != 'super admin') {
                    $responce['message'] = "Closed records can only be edited by Super Admin.";
                    return $this->response->setJSON($responce);
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
        $request = service('request');
        $postData = $request->getVar();

        if(isset($postData['honeypot'])) {
            unset($postData['honeypot']);   
        }
        
        $db = db_connect();
        
        if(isset($id)){
            $responce['message'] = "Data updation failed";
            if(isset($action)){
                $currentData = $this->BaseModel->find($id);
                if (!$currentData) {
                    return $this->response->setJSON(['status' => '0', 'message' => 'LMRA record not found']);
                }
                $currentStatus = $currentData['status'];
                $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
                $isValid = false;
                $message = "Invalid status transition or insufficient permissions.";

                // Pending -> Verified -> Open -> Closed
                // Status IDs: 0 = Pending, 1 = Verified, 2 = Open, 3 = Cancelled, 4 = Request Required, 5 = Closed

                switch($action){
                    case "verified":
                        if ($currentStatus == "0") {
                            if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Account Manager', 'Cluster manager', 'Engineer'])) {
                                $postData['status'] = "1";
                                $isValid = true;
                            } else {
                                $message = "You do not have permission to Verify.";
                            }
                        }
                        break;
                    case "reject": // Reject keeps status 0, requires reason
                        if (in_array($currentStatus, ["0", "1", "2"])) {
                            if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Account Manager', 'Cluster manager', 'Engineer'])) {
                                if (empty($postData['rejection_comment'])) {
                                    $message = "Rejection Reason is mandatory.";
                                } else {
                                    $postData['status'] = "0"; // keep/revert to pending
                                    $isValid = true;
                                }
                            } else {
                                $message = "You do not have permission to Reject.";
                            }
                        }
                        break;
                    case "cancelled":
                        if ($currentStatus == "0") {
                            if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Engineer'])) {
                                if (empty($postData['rejection_comment'])) {
                                    $message = "Cancellation Reason is mandatory.";
                                } else {
                                    $postData['status'] = "3";
                                    $isValid = true;
                                }
                            } else {
                                $message = "You do not have permission to Cancel.";
                            }
                        }
                        break;
                    case "open":
                        if ($currentStatus == "1") {
                            if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Account Manager', 'Cluster manager', 'Engineer'])) {
                                $postData['status'] = "2";
                                $isValid = true;
                            } else {
                                $message = "You do not have permission to Open.";
                            }
                        }
                        break;
                    case "close":
                        if ($currentStatus == "2") {
                            if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Engineer'])) {
                                if (empty($postData['rejection_comment'])) {
                                    $message = "Closure Comment / Details is mandatory.";
                                } else {
                                    $postData['status'] = "5";
                                    $isValid = true;
                                }
                            } else {
                                $message = "You do not have permission to Close.";
                            }
                        }
                        break;
                    case "pending":
                        if ($currentStatus == "3") {
                            if (in_array($role, ['super_admin', 'Engineer'])) {
                                $postData['status'] = "0"; // back to pending
                                $isValid = true;
                            } else {
                                $message = "Only Super Admin can set a Cancelled LMRA to Pending.";
                            }
                        }
                        break;
                }

                if (!$isValid) {
                    return $this->response->setJSON(['status' => '0', 'message' => $message]);
                }
            } else {
                // Updating the LMRA record itself (no action)
                if (isset($postData['self_other'])) {
                    if ($postData['self_other'] == '0') {
                        $postData['other_engineer_id'] = 0;
                        $postData['other_engineer_name'] = NULL;
                    } else if ($postData['self_other'] == '1') {
                        $db = db_connect();
                        $otherEngineerId = $postData['other_engineer_id'] ?? 0;
                        $validEngineer = $db->table('alert_users')->where(['user_id' => $otherEngineerId, 'user_designation' => 'Engineer', 'status' => 1])->get()->getRowArray();
                        if (!$validEngineer) {
                            return $this->response->setJSON(['status' => '0', 'message' => 'Invalid Other Engineer selected.']);
                        }
                        $postData['other_engineer_name'] = $validEngineer['user_name'];
                    }
                }
            }
            
            $db->transStart();
            $updateSuccess = $this->BaseModel->update($id,$postData);
            if($updateSuccess){
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                $responce['status'] = "0";
                $responce['message'] = "Transaction failed during update.";
            } else if ($updateSuccess && isset($action) && isset($currentStatus) && isset($postData['status']) && $postData['status'] != $currentStatus) {
                helper('notification');
                sendLmraNotification($id, false, $currentStatus, $postData['status']);
            }
        }else{
            // NEW LMRA Submission
            // Validate required fields
            $validation = \Config\Services::validation();
            $validation->setRules([
                'lmra_project_name' => 'required',
                'lmra_job_no' => 'required',
                'lmra_location' => 'required',
                'lmra_visit_date' => 'required',
                'lmra_visit_time' => 'required',
                'task_to_be_perform' => 'required',
                'sta_data' => 'required',
                'lmra_json_data' => 'required'
            ]);

            if (!$validation->withRequest($request)->run()) {
                return $this->response->setJSON(['status' => '0', 'message' => 'Please fill all mandatory fields.']);
            }
            
            // Validate that STA basic questions are fully answered
            $sta_data = json_decode($postData['sta_data'], true);
            if (!$sta_data || count($sta_data) < 5 || in_array(null, $sta_data, true)) {
                return $this->response->setJSON(['status' => '0', 'message' => 'Please answer all basic safety questions.']);
            }

            // Enforce user_id logic: if Engineer, force their own ID.
            if(isset($_SESSION['role']) && $_SESSION['role'] == 'Engineer'){
                $postData['user_id'] = $_SESSION['login_id'] ?? 0;
            }

            // Initial status should be 0 = Pending
            $postData['status']= "0";
            $responce['message'] = "Data insertion failed";
            
            // Check self/other engineer logic on insert
            if (isset($postData['self_other'])) {
                if ($postData['self_other'] == '0') {
                    $postData['other_engineer_id'] = 0;
                    $postData['other_engineer_name'] = NULL;
                    $postData['user_id'] = $_SESSION['login_id'] ?? 0;
                } else if ($postData['self_other'] == '1') {
                    $db = db_connect();
                    $otherEngineerId = $postData['other_engineer_id'] ?? 0;
                    $validEngineer = $db->table('alert_users')->where(['user_id' => $otherEngineerId, 'user_designation' => 'Engineer', 'status' => 1])->get()->getRowArray();
                    if (!$validEngineer) {
                        return $this->response->setJSON(['status' => '0', 'message' => 'Invalid Other Engineer selected.']);
                    }
                    $postData['other_engineer_name'] = $validEngineer['user_name'];
                    $postData['user_id'] = $_SESSION['login_id'] ?? 0;
                }
            } else {
                $postData['user_id'] = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : (isset($postData['user_id']) ? $postData['user_id'] : 0);
            }

            $db->transStart();
            $insertId = $this->BaseModel->insert($postData);
            if($insertId){
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }
            $db->transComplete();
            
            if ($db->transStatus() === false) {
                $responce['status'] = "0";
                $responce['message'] = "Transaction failed during insertion.";
            } else if ($insertId) {
                helper('notification');
                sendLmraNotification($insertId, true);
            }
        }
        return $this->response->setJSON($responce);
    }

    public function delete_report($id) {
        $responce = ['status' => '0', 'message' => 'Failed to delete report'];
        if (isset($id)) {
            $data = $this->BaseModel->find($id);
            $role = isset($_SESSION['role']) ? $_SESSION['role'] : '';
            
            if ($data && $data['status'] == '5' && $role != 'super_admin' && $role != 'super admin') {
                $responce['message'] = "Closed records can only be deleted by Super Admin.";
                return $this->response->setJSON($responce);
            }

            if (in_array($role, ['Auditor', 'admin', 'super_admin', 'Engineer', 'Account Manager', 'Cluster manager'])) {
                if ($this->BaseModel->update($id, ['status' => '6'])) {
                    $responce = ['status' => '1', 'message' => 'Report deleted successfully'];
                }
            } else {
                $responce['message'] = "You do not have permission to delete.";
            }
        }
        return $this->response->setJSON($responce);
    }
}
