<?php

//namespace App\Controllers;
//namespace App\Libraries\Pdf;
namespace App\Controllers\Reports;
require APPPATH.'/ThirdParty/dompdf/autoload.inc.php';  // Adjust this path if necessary
use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Mailing_report extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_mail';
     $db['allowedFields'] = ['user_id','receiver_email','from_email','cc_emails','subject','body','doc_category','doc_id','resend_counter','email_reminder'];
     $db['primaryKey'] = "mail_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="Mail Reports";
        // $tdata['button_name']="Add Mail Details";
        // $tdata['button_id']="user_modal";
        
        $tdata['display_contents'] = [
            "mail_id" => "ID",
            "receiver_email" => "To Whome",
            "subject" => "Subject",
            "body" => "Content",
            "resend_counter" => "Attempts",
			"update_date" => "latest",
// 			"which" => "Type",
	        "action" => "Actions" 
            ];
        $data['ajax_url']=base_url("Reports/Mailing_report/save_details");
        $tdata ['ajax_url_for_data']=base_url("Reports/Mailing_report/table_ajax");
        $data['user_designation'] = ["Account Manager","Reporting manager",""];
        $data['table'] = view("Layout/table-view",$tdata);
        // $data['table'] ="";
        $db=db_connect();
        
        $_SESSION ['active_btn'] = "Reports";
		$_SESSION ['active_tag'] = "mailDetails";

         if(isset($_SESSION['role']) && $_SESSION['role']=='admin'){
            $table_data = $db->table('alert_mail')->get()->getResultArray();
         }else{
             $table_data = $db->table('alert_mail')->where(array('user_id'=>$_SESSION['login_id']))->get()->getResultArray();
         }
         
         // NEW CHANGE: Add action buttons to initial table data with popup functionality
         foreach($table_data as $key => $row) {
             $refresh = '<button onclick=\'openEmailModal("'.$row['mail_id'].'", "'.$row['receiver_email'].'", "'.$row['subject'].'", "'.$row['body'].'", "'.$row['doc_id'].'", "'.$row['doc_category'].'")\' 
class="btn btn-icon btn-primary" title="Send Mail">     
    <span class="indicator-label svg-icon svg-icon-3">
        <i class="fas fa-envelope"></i>  
    </span>     
    <span class="indicator-progress">
        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
    </span> 
</button>';
             $table_data[$key]['action'] = $refresh;
         }
         
         $data['table_data'] = $table_data;
        
        // NEW CHANGE: Add table_data to tdata and generate the table view
        $tdata['table_data'] = $table_data;
        $data['table'] = view("Layout/table-view", $tdata);
       
        // NEW CHANGE: Return the proper view with layout and styling
        return view("Reports/add_mailing_reports",$data);
    }
    public function table_ajax(){
        // if($_SESSION['role']!="super_admin")
        //     $tdata['table_data'] = $this->BaseModel->where(["employee_reporting_to"=>$_SESSION['mail_id']])->findAll();
        // else
            $tdata['table_data'] = $this->BaseModel->findAll();
        foreach($tdata['table_data'] as $key=>$row){
           
            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
             
								$refresh = '<button onclick=\'openEmailModal("'.$row['mail_id'].'", "'.$row['receiver_email'].'", "'.$row['subject'].'", "'.$row['body'].'", "'.$row['doc_id'].'", "'.$row['doc_category'].'")\' 
class="btn btn-icon btn-primary" title="Send Mail">     
    <span class="indicator-label svg-icon svg-icon-3">
        <i class="fas fa-envelope"></i>  
    </span>     
    <span class="indicator-progress">
        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
    </span> 
</button>';


											
										
								// 			if($row['status']=="0"){
								// 			    $deactive = "";
								// 			    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											    
								// 			}else if($row['status']=="1"){
								// 			    $active = "";
											    
								// 			}else if($row['status']=="2"){
								// 			    $delete = "";
								// 			    $deactive = "";
								// 			    $tdata['table_data'][$key]['tr_class']="bg-light-danger";
											    
								// 			}
			$tdata['table_data'][$key]['action']=$refresh;
			
			
        }
       
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }
    
    public function email($Id=null){
         $db=db_connect();
		   $cc=array();
		   $data='';
		   
		   // NEW CHANGE: Handle both GET and POST requests
		   if($this->request->getMethod() == 'POST') {
		       // Handle AJAX POST request from popup
		       $postData = $this->request->getVar();
		       
		       try {
		           if(isset($postData['doc_id']) && !empty($postData['doc_id'])){
		               $cc = array();
		               if($postData['doc_category'] == 1){
		                   $nearmiss = $db->query(" select * ,GROUP_CONCAT(user_email) as emails from alert_users where FIND_IN_SET (user_id,(select group_member_ids from alert_near_miss where near_miss_id =".$postData['doc_id']." )) ")->getResultArray();
		                   if(!empty($nearmiss)) {
		                       $cc = explode(",",$nearmiss[0]['emails']);
		                   }
		               }
		               
		               // Add CC emails from form if provided
		               if(!empty($postData['cc_emails'])) {
		                   $formCC = array_map('trim', explode(',', $postData['cc_emails']));
		                   $cc = array_merge($cc, $formCC);
		               }
		               
		               $data = $this->send_email('', $cc);
		           } else {
		               $data = $this->send_email($postData['mail_id'] ?? null);
		           }
		           
		           if($data == 1){
		               return $this->response->setJSON(['status' => 1, 'message' => 'Email sent successfully!']);
		           } else {
		               return $this->response->setJSON(['status' => 0, 'message' => 'Failed to send email. Please try again.']);
		           }
		           
		       } catch(Exception $e) {
		           return $this->response->setJSON(['status' => 0, 'message' => 'Error: ' . $e->getMessage()]);
		       }
		   } else {
		       // Handle GET request (legacy support)
		       if(isset($_POST['doc_id'])){
		           $cc='';
		           if($_POST['doc_category']==1){
		               $nearmiss= $db->query(" select * ,GROUP_CONCAT(user_email) as emails from alert_users where FIND_IN_SET (user_id,(select group_member_ids from alert_near_miss where near_miss_id =".$_POST['doc_id']." )) ")->getResultArray();
		               $cc = explode(",",$nearmiss[0]['emails']);
		           }

		           $data=$this->send_email('', $cc );
		       }else{
		           $data=$this->send_email($Id);
		       }
		   
		       // NEW CHANGE: Fixed comparison operator and added proper error handling
		       if($data == 1){
		           return redirect()->back();
		       } else {
		           // NEW CHANGE: Added error handling for failed email sending
		           session()->setFlashdata('error', 'Failed to send email. Please try again.');
		           return redirect()->back();
		       }
		   }
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

    // NEW CHANGE: Added missing save_details method
    public function save_details(){
        $response = ['status' => '0', 'message' => 'Failed to save details'];
        
        try {
            $data = $this->request->getVar();
            
            if(empty($data)) {
                $response['message'] = 'No data received';
                return $this->response->setJSON($response);
            }
            
            // Validate required fields
            $required_fields = ['receiver_email', 'subject', 'body'];
            foreach($required_fields as $field) {
                if(empty($data[$field])) {
                    $response['message'] = ucfirst($field) . ' is required';
                    return $this->response->setJSON($response);
                }
            }
            
            // Add user_id if not provided
            if(!isset($data['user_id'])) {
                $data['user_id'] = $_SESSION['login_id'] ?? '';
            }
            
            // Add timestamp
            $data['update_date'] = date('Y-m-d H:i:s');
            $data['resend_counter'] = 0;
            
            // Save to database
            $result = $this->BaseModel->insert($data);
            
            if($result) {
                $response['status'] = '1';
                $response['message'] = 'Mail details saved successfully';
            } else {
                $response['message'] = 'Database error occurred';
            }
            
        } catch(Exception $e) {
            $response['message'] = 'Error: ' . $e->getMessage();
        }
        
        return $this->response->setJSON($response);
    }
   
}
