<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
require APPPATH.'/ThirdParty/dompdf/autoload.inc.php';  // Adjust this path if necessary

use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;



class Sixs_audit extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;
 
 
   public function __construct(){
        helper("form");
     $db = null;
     $db['table']         = 'alert_sixs_audit';
     $db['allowedFields'] = [
       'location','nc_description','nc_photo','action_required_dep','devision_dept','responsibilty','target_date','sixs_category','details_of_ca_pa','after_photo','department_status','final_status_soi','status'];
     $db['primaryKey'] = "audit_id";
     $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="6s Audit Report";
        $tdata['button_name']="Add Audit";
        $tdata['button_id']="course_modal";
        
        $tdata['display_contents'] = [
            "audit_id"=>"ID",
            "location"=>"Location",
            "nc_description"=>"Description",
            "nc_photo"=>"NC Photo",
            "action_required_dep"=>"Action Required",
            "devision_dept"=>"Div/Dep",
            "responsibilty"=>"Responsibilty",
            "target_date"=>"Target Date",
            "sixs_category"=>"Sixs Category",
            "details_of_ca_pa"=>"Details ca/pa",
            "after_photo"=>"After Photo",
            // "department_status"=>"Department Status",
            "final_status_soi"=>"Final Status SOI",
            "action"=>"Action"
            ];
        $data['ajax_url']=base_url("Masters/Sixs_audit/save_details");
        $tdata ['ajax_url_for_data']=base_url("Masters/Sixs_audit/table_ajax");
        $data['table'] = view("Layout/table-view",$tdata);
        $db = db_connect();
        $data['location_list']=$db->table("alert_location_master")->where("status","1")->get()->getResultArray();
        $data['location_list_dep']=$db->table("alert_department_master")->where("status","1")->get()->getResultArray();
        $data['location_list_cat']=$db->table("alert_sixs_category")->where("status","1")->get()->getResultArray();


       return view("Master/add_sixs_audit",$data);
    }
    public function table_ajax(){
       
        $tdata['table_data'] = $this->BaseModel->findAll();
             $statusMessages = [
                0 => '<span class="badge badge-warning">Pending</span>',
                1 => '<span class="badge badge-success">Active</span>',
                2 => '<span class="badge badge-secondary">Deactivated</span>'
            ];
        
          foreach($tdata['table_data'] as $key=>$row){
            $tdata['table_data'][$key]['sr_no'] = $key+1;
            $active = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\''.base_url("Masters/Sixs_audit/save_details/".$row['audit_id']).'/active\',$(this));">
							<span class="indicator-label svg-icon svg-icon-2">
								<i class="fa fa-unlock"></i>
							</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
							</button> ';
		    $deactive = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\''.base_url("Masters/Sixs_audit/save_details/".$row['audit_id']).'/deactive\',$(this));">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
			$delete = '<button data-ajax-url="'.base_url("Masters/Sixs_audit/save_details/".$row['audit_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

                                            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
            $edit = '<button data-ajax-url="'.base_url("Masters/Sixs_audit/get_form_data/".$row['audit_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['audit_id'].');" title="edit">
    						<span class="indicator-label svg-icon svg-icon-3">
    							<i class="fa fa-edit"></i>
    						</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
    						</button>
    						';
		
    		$pdf = '<button onclick=\'window.location.href="'.base_url("Masters/Sixs_audit/sixsAuditPdf/".$row['audit_id']).'"\' class="btn btn-icon btn-primary" title="pdf">
    						<span class="indicator-label svg-icon svg-icon-3">
    							<i class="fa fa-file-pdf"></i>
    						</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
    						</button>
    						';
			$loginas = '<button onclick=\'window.location.href="'.base_url("Masters/Sixs_audit/auditComment/".$row['audit_id']).'"\' class="btn btn-icon btn-primary" title="comment">
    						<span class="indicator-label svg-icon svg-icon-3">
    							<i class="fa fa-comments"></i>
    						</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
    						</button>
    						';

											if($row['status']=="0"){
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											    
											}else if($row['status']=="1"){
											    $active = "";
											    
											}else if($row['status']=="2"){
											    $delete = "";
											    $deactive = "";
											    $tdata['table_data'][$key]['tr_class']="bg-light-danger";
											    
											}
			$tdata['table_data'][$key]['action']="<center>".$statusMessages[$row['status']]."<br><br>".$active.$deactive.$delete.$edit.$pdf.$loginas."</center>";
        	$tdata['table_data'][$key]["nc_photo"] = "<img src='".base_url($tdata['table_data'][$key]["nc_photo"])."' onerror=\"this.src='".env("defaultLogo")."'\" height='50' width='50' />";
         	$tdata['table_data'][$key]["after_photo"] = "<img src='".base_url($tdata['table_data'][$key]["after_photo"])."' onerror=\"this.src='".env("defaultLogo")."'\" height='50' width='50' />";

// 			$tdata['table_data'][$key]['action']=$edit.$active.$deactive.$delete.$loginas;
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

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
                        // if($postData['honeypot'] != ""){
                        //     $responce['status'] = "0";
                        //     $responce['message'] = "Data insertion faild";
                        //     die;
                        // }
                     unset($postData['honeypot']);   
                    }
                     $allowed = array( 'jpg', 'png', 'jpeg', 'gif');
                    if(isset($_FILES['nc_photo']) &&  isset( $_FILES['nc_photo']['error'])){
                        if( $_FILES['nc_photo']['error'] == UPLOAD_ERR_OK){
                            $ext = pathinfo($_FILES["nc_photo"]["name"], PATHINFO_EXTENSION);
                            if (in_array($ext, $allowed)) {
                              $folder="uploads/nc_photo/";
                                $url = "";
                                if(isset($postData['nc_photo_url']))
                                $url = $this->uploadImage($folder,"nc_photo",$postData['nc_photo_url']);
                                else
                                $url = $this->uploadImage($folder,"nc_photo");
                                
                                $postData['nc_photo'] = $url;
                            }
                        }
                    }else{
                        if(isset($postData['nc_photo_url']))
                                $postData['nc_photo'] = $postData['nc_photo_url'];
                        
                    }
                    if(isset($postData['nc_photo_url']))
                            unset($postData['nc_photo_url']);
//print_r($_FILES);
  //                          echo $postData['nc_photo'];
   //                         exit();
                     $allowed = array( 'jpg', 'png', 'jpeg', 'gif');
                    if(isset($_FILES['after_photo']) &&  isset( $_FILES['after_photo']['error'])){
                        if( $_FILES['after_photo']['error'] == UPLOAD_ERR_OK){
                            $ext = pathinfo($_FILES["after_photo"]["name"], PATHINFO_EXTENSION);
                            if (in_array($ext, $allowed)) {
                              $folder="uploads/after_photo/";
                                $url = "";
                                if(isset($postData['after_photo_url']))
                                $url = $this->uploadImage($folder,"after_photo",$postData['after_photo_url']);
                                else
                                $url = $this->uploadImage($folder,"after_photo");
                                
                                $postData['after_photo'] = $url;
                            }
                        }
                    }else{
                        if(isset($postData['after_photo_url']))
                                $postData['after_photo'] = $postData['after_photo_url'];
                        
                    }
                    if(isset($postData['after_photo_url']))
                            unset($postData['after_photo_url']);
                    
                if(isset($id)){
                        $responce['message'] = "Data updation faild";
                        if(isset($action)){
                            switch($action){
                                case "active":
                                    $postData['status']= "1";
                                    break;
                                case "deactive":
                                    $postData['status']= "0";
                                    break;
                                case "delete":
                                    $postData['status']= "2";
                                    break;
                            }
                        }
                    if($this->BaseModel->update($id,$postData)){
                        // $db = db_connect();
                        //  $ads_user = $db->table('alert_sixs_audit');
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }else{
                    $postData['status']="1";
                        $responce['status'] = "0";
                        $responce['message'] = "Data insertion faild";
                         $postData = $_POST;
                          $folder = "";
                    
                    if($this->BaseModel->insert($postData)){
                     $id  = $this->BaseModel->insertID();
                        $responce['status'] = "1";
                        $responce['message'] = "Data saved successfully";
                    }
                }
                echo json_encode($responce);
    }
    
    function auditView($id=null){
         $db=db_connect();
        if(isset($id)){
            $where['audit_id']=$id;
            $data ['details'] = $db->query( " select alert_sixs_audit.*,alert_users.* from alert_sixs_audit inner join alert_users on alert_users.user_id=alert_sixs_audit.user_id and audit_id=$id" )->getResultArray();
			$print= " <a href=".base_url()."Masters/sixsAuditPdf/".$id." class='btn btn-outline-info' >Print Pdf</a>";
		    $data['print']=$print;
            return view("Master/sixs_audit_details_pdf",$data);
        }
    }
    public function auditComment($audit_id=null){
        $db=db_connect();
        if(isset($audit_id)){
            $where['audit_id']=$audit_id;
            $data['login_id']=$_SESSION['login_id'];
            // $data ['details'] = $db->query( " select alert_sixs_audit.*,alert_users.user_id,alert_users.user_contact,alert_users.user_status,alert_users.user_name,alert_users.user_designation,
            // (select count(*) from alert_sixs_audit_comment where alert_sixs_audit_comment.audit_id=alert_sixs_audit.audit_id) as comment_count,

            // (select count(counts) from 
            //  ( select alert_sixs_audit_comment.user_id as counts ,alert_sixs_audit_comment.audit_id as audit_id from alert_sixs_audit_comment where alert_sixs_audit_comment.audit_id=".$audit_id." group by alert_sixs_audit_comment.user_id
            //  ) tt where tt.audit_id=alert_sixs_audit.audit_id 
            // ) as user_count
            //             from alert_sixs_audit inner join alert_users on alert_users.user_id=alert_sixs_audit.user_id 
            //             where alert_sixs_audit.audit_id=$audit_id" )->getResultArray();


            $data ['details'] = $db->query( " select alert_sixs_audit.*,alert_users.user_id,alert_users.user_email ,alert_users.user_contact,alert_users.status,alert_users.user_name,alert_users.user_designation,
            (select count(*) from alert_sixs_audit_comment where alert_sixs_audit_comment.audit_id=alert_sixs_audit.audit_id) as comment_count,

            (select count(counts) from 
             ( select alert_sixs_audit_comment.user_id as counts ,alert_sixs_audit_comment.audit_id as audit_id from alert_sixs_audit_comment where alert_sixs_audit_comment.audit_id=".$audit_id." group by alert_sixs_audit_comment.user_id
             ) tt where tt.audit_id=alert_sixs_audit.audit_id 
            ) as user_count
                        from alert_sixs_audit inner join alert_users on alert_users.user_id=alert_sixs_audit.user_id 
                        where alert_sixs_audit.audit_id=$audit_id" )->getResultArray();

                        $data ['sixs_comments'] = $db->query( " SELECT alert_sixs_audit_comment.*,alert_users.user_name,alert_users.user_designation  FROM alert_sixs_audit_comment inner join alert_users on alert_users.user_id=alert_sixs_audit_comment.user_id where alert_sixs_audit_comment.audit_id=$audit_id order by alert_sixs_audit_comment.comment_id DESC" )->getResultArray();
                        $data ['form_action'] = base_url(index_page()."/Masters/Sixs_audit/acceptSixsComments");
                        if($_SESSION['role']=='Account Manager' || $_SESSION['role']=='Engineer'){
                        $data ['form_action'] = base_url(index_page()."/Masters/Sixs_audit/acceptSixsComments");
                        }
                        // echo $data ['form_action'];
                        // exit();
                        $data['button_id']="test";
                        $data['table']="";
                       
                        // echo"<pre>";
                        // print_r($data ['details']);
                        // exit();
                       
                        return view("Master/audit_view_comment",$data);
                      
                    }


    }
    function acceptSixsComments(){
        $db=db_connect();
        if(isset($_POST['comment_text']) && isset($_POST['audit_id']) && isset($_POST['user_id'])){
            $TableValues['comment_text']=$_POST['comment_text'];
            $TableValues['audit_id']=$_POST['audit_id'];
            $TableValues['user_id']=$_POST['user_id'];
               $db->table("alert_sixs_audit_comment")->insert($TableValues );
            // redirect(base_url(index_page()."/Masters/auditComment/".$_POST['audit_id']));
             return  redirect()->back();
        }
    }
    
  public function sixsAuditPdf($audit_id) {
      $options = new Options();
        $options->set('defaultFont', 'Courier');
         $options->set('isRemoteEnabled', true);
        // Instantiate Dompdf
        $dompdf = new Dompdf($options);
        $db = db_connect();
    
            	$where['audit_id']=$audit_id;
                
                $data ['details'] = $db->query( " select alert_sixs_audit.*,alert_users.* from alert_sixs_audit inner join alert_users
                                  on alert_users.user_id=alert_sixs_audit.user_id and audit_id=$audit_id" )->getResultArray();
		       
		       $data['sixs_comments']=$db->query( " select alert_users.* ,alert_sixs_audit_comment .* 
		                        from alert_sixs_audit_comment 
		                            inner join alert_users on alert_users.user_id=alert_sixs_audit_comment.user_id 
		                                and audit_id=$audit_id" )->getResultArray();
   
    $dompdf = new Dompdf();  
    
    // return view("Master/audit_view_comment",$data);

    $html = view("Master/sixs_audit_details_pdf", $data);

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
header('Content-Disposition: attachment; filename="6sAudit-'.$audit_id.'.pdf"');

// Stream the PDF to the browser
echo $dompdf->output();
}

}
