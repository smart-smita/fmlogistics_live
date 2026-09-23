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
            "lmra_job_no" => "Job No",
            "lmra_project_name" => "Customer Name",
            "lmra_location" => "Location",
            "action" => "Actions"  
            ];
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
        $data ['lmra_data'] = $db->query("SELECT *, 'Yes' as title1, 'No' as title2, 'NA' as title3, 'Sevirity' as title4, 
                                CONCAT('[',GROUP_CONCAT(CONCAT('{\"lmra_id\":\"', lmra_id ,'\",\"title1\":\"Yes\",\"titlew\":\"No\",\"title3\":\"NA\",\"title4\":\"Sevirity\",\"lmra_text\":\"', lmra_text, '\", \"lmra_options\":\"',lmra_options,'\"}')),']') as lmra_data 
                                FROM `alert_lmra` 
                                WHERE `status` = '1' group by lmra_category_id")->getResultArray();
        $where['status']=1;
                if($_SESSION['role'] == 'Cluster manager')
                    $where['employee_reporting_to']=$_SESSION['login_id'];
                if($_SESSION['role'] == 'Higher authority')
                    $where['user_emp_country']=('select user_emp_country from  alert_users where user_id='.$_SESSION['login_id']);       

        // change on 29/09/25 by darsh: prefill current user in LMRA form
        $data['engineer_list']=$db->table("alert_users")->where($where)->get()->getResultArray();
        $data['user_id'] = isset($_SESSION['login_id']) ? $_SESSION['login_id'] : null;
        $data['fcm_id'] = isset($_SESSION['fcm_id']) ? $_SESSION['fcm_id'] : '';

        // $data['table'] ="";
        return view("Reports/add_lmra_reports",$data);
    }
    public function table_ajax(){
        // if($_SESSION['role']!="super_admin")
        //     $tdata['table_data'] = $this->BaseModel->where(["employee_reporting_to"=>$_SESSION['lmra_details_id']])->findAll();
        // else
            //$tdata['table_data'] = $this->BaseModel->findAll();
            $condtion='';
                    	if(isset($_SESSION['role']) && $_SESSION['role']=='Cluster manager'){
                            $condtion=' and alert_users.employee_reporting_to='.$_SESSION['login_id'];
                         }
                         elseif(isset($_SESSION['role']) && $_SESSION['role']=='Higher authority'){
                            
                            $condtion=' and alert_users.user_emp_country in ( "'.$_SESSION['country'].'")';     
                             
                         }
                         elseif(isset($_SESSION['role']) && ($_SESSION['role']=='Account Manager' || $_SESSION['role']=='Engineer')){
                            
                            $condtion=' and alert_lmra_details.user_id ='.$_SESSION['login_id'];     
                             
                         }
        $db=db_connect();
        $tdata['table_data'] = $db->query("select  alert_lmra_details.*,alert_users.user_name,alert_users.user_email,
        concat(alert_lmra_details.lmra_visit_date,' ',alert_lmra_details.lmra_visit_time) as date_time from
        alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id".$condtion)->getResultArray();
    
            $statusMessages = [                
             0 => '<span class="badge badge-danger">Pending</span>',
             1 => '<span class="badge badge-success">Verified</span>',
             4 => '<span class="badge badge-warning">Request required</span>',
             2 => '<span class="badge badge-warning">Open</span>',
             5 => '<span class="badge badge-success">Close</span>',
             3 =>'<span class="badge badge-secondary">Cancel</span>'
             ];
        foreach($tdata['table_data'] as $key=>$row){
            
            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
            // change on 29/09/25 by darsh: changed eye icon to edit icon for Edit button
            $edit = '<button data-ajax-url="'.base_url("Reports/Lmra_reports/get_form_data/".$row['lmra_details_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['lmra_details_id'].');" title="Edit">
                                                <span class="indicator-label svg-icon svg-icon-3">
                                                    <i class="fa fa-edit"></i>
                                                </span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
                                                </button>
                                                ';
									    	$pdf = '<button onclick=\'window.location.href="'.base_url("Reports/Lmra_reports/lmraViewDetailsPdf/".$row['lmra_details_id']).'"\' class="btn btn-icon btn-primary" title="Pdf">
                        							<span class="indicator-label svg-icon svg-icon-3">
                        								<i class="fa fa-file-pdf"></i>
                        							</span>
                                                <span class="indicator-progress">
                                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                                </span>
                        							</button>
                        							';
												 $loginas = '<button onclick=\'window.location.href="'.base_url("Reports/Lmra_reports/lmraComment/".$row['lmra_details_id']).'"\' class="btn btn-icon btn-primary" title="Comment">
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
                // show green verify -> sets to verified (1)
                $statusAction = '<button class="btn btn-icon btn-success" title="Verify" onclick="url_call_ajax(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/verified\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-check"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-warning";
            } elseif ($row['status'] == "1") {
                // show yellow open -> sets to open (2)
                $statusAction = '<button class="btn btn-icon btn-warning" title="Open" onclick="url_call_ajax(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/open\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-check"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            } elseif ($row['status'] == "2") {
                // show red cancel (lock) -> sets to cancelled (3)
                $statusAction = '<button class="btn btn-icon btn-danger" title="Cancel" onclick="url_call_ajax(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/cancelled\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-lock"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            } elseif ($row['status'] == "3") {
                // show yellow requested (lock) -> sets to requested (4)
                $statusAction = '<button class="btn btn-icon btn-warning" title="Requested" onclick="url_call_ajax(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/requested\',$(this));">'
                    .'<span class="indicator-label svg-icon svg-icon-2">'
                    .'<i class="fa fa-lock"></i>'
                    .'</span>'
                    .'<span class="indicator-progress"><span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>'
                    .'</button>';
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            } elseif ($row['status'] == "4") {
                // show close (double check) -> sets to close (5)
                $statusAction = '<button class="btn btn-icon btn-success" title="Close" onclick="url_call_ajax(\''.base_url("Reports/Lmra_reports/save_details/".$row['lmra_details_id']).'/close\',$(this));">'
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
    
    public function search()
    {
        $db = \Config\Database::connect();
        $builder = $db->table('alert_location_master');

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
            $data ['details'] = $db->query( " select alert_lmra_details.*,alert_users.* from alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id and lmra_details_id=$id" )->getResultArray();
			$print= " <a href=".base_url()."Reports/lmraViewDetailsPdf/".$id." class='btn btn-outline-info' >Print Pdf</a>";
		    $data['print']=$print;
            return view("Reports/lmra_view_details_pdf",$data);
        }
    }
    	
    public function lmraViewDetailsPdf($lmraId){ 
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        // $options->set('isRemoteEnabled', true);
        
        // Instantiate Dompdf
        $dompdf = new Dompdf($options);

        $db = db_connect();
        
            // Fetch LMRA details
            	$where['lmra_details_id']=$lmraId;
                
                $data ['details'] = $db->query( " select alert_lmra_details.*,alert_users.* from alert_lmra_details inner join alert_users
                                  on alert_users.user_id=alert_lmra_details.user_id and lmra_details_id=$lmraId" )->getResultArray();
               
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
            	
           
        $dompdf = new Dompdf();  
    
    // return view("Master/audit_view_comment",$data);

    $html = view("Reports/lmra_view_details_pdf", $data);

    // Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the PDF
$dompdf->render();

    //  if (ob_get_length()) {
    //         ob_end_clean();
    //     }

// Output the generated PDF (force download)
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="LMRA-'.$lmraId.'.pdf"');

// Stream the PDF to the browser
echo $dompdf->output();

}
   
    public function email($id = null){
                $cc = [];
                $data = '';
                // NEW CHANGE: generate LMRA PDF and email to the specific user's email
                if (!isset($id)) {
                    return redirect()->back();
                }
                $db = db_connect();
                $details = $db->query(" select alert_lmra_details.*,alert_users.* from alert_lmra_details inner join alert_users on alert_users.user_id=alert_lmra_details.user_id and lmra_details_id=?", [$id])->getResultArray();
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
                $sendResult = $this->send($toEmail, $details[0]['user_name'], null, $subject, $body, 'ALert', true, $pdfName);
                if ($sendResult === 1 && file_exists(__DIR__.'/'.$pdfName)) { @unlink(__DIR__.'/'.$pdfName); }
                return redirect()->back();
}
 public function lmraComment($lmraId=null){
        $db=db_connect();
        if(isset($lmraId)){
            $where['lmra_details_id']=$lmraId;
            $data['login_id']=$_SESSION['login_id'];
            $data ['details'] = $db->query( " select alert_lmra_details.*,alert_users.user_id,alert_users.user_contact,alert_users.status,alert_users.user_name,alert_users.user_designation,
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
    // print_r($postData);
    // exit();
                if(isset($postData['honeypot']))
                    {
                        // if($postData['honeypot'] != ""){
                        //     $responce['status'] = "0";
                        //     $responce['message'] = "Data insertion faild";
                        //     die;
                        // }
                     unset($postData['honeypot']);   
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
}
