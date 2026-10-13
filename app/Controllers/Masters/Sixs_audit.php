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
        helper(["form", "designation_acl"]);
        $db = null;
        $db['table']         = 'alert_sixs_audit';
        $db['allowedFields'] = [
            'user_id', 'location','nc_description','nc_photo','action_required_dep','devision_dept','responsibilty','target_date','sixs_category','details_of_ca_pa','after_photo','department_status','final_status_soi','status','default_date','update_date'];
        $db['primaryKey'] = "audit_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title']="6s Audit Report";
        if (canCreateSixsAudit()) {
            $tdata['button_name']="Add Audit";
            $tdata['button_id']="course_modal";
        } else {
            $tdata['button_name']="";
            $tdata['button_id']="";
        }
        
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
            "status"=>"Status",
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
        if (!canViewData()) {
            return $this->response->setJSON(['data' => []]);
        }
        
        $db = db_connect();
        $builder = $this->BaseModel->builder();
        $builder->select('alert_sixs_audit.*');
        
        if (!isAdmin() && !isHigherAuthority()) {
            if (isAuditor()) {
                $builder->where('alert_sixs_audit.user_id', getUserId());
            } else if (isEngineer()) {
                $builder->where('LOWER(TRIM(alert_sixs_audit.responsibilty))', strtolower(trim(getUserName())));
            } else if (isClusterManager() || isAccountManager() || isWHManager()) {
                $builder->join('alert_location_master m', 'LOWER(TRIM(m.location_name)) = LOWER(TRIM(alert_sixs_audit.location))', 'left');
                
                if (isClusterManager() || isWHManager()) {
                    $assignedClusters = getClusterManagerAssignedCluster();
                    if (!empty($assignedClusters)) {
                        $builder->whereIn('m.cluster_name', $assignedClusters);
                    } else {
                        $builder->where('1=0');
                    }
                } else if (isAccountManager()) {
                    $clientDetails = getAccountManagerClientDetails();
                    $clients = [];
                    if ($clientDetails) {
                        foreach ($clientDetails as $details) {
                            if (!empty($details['client_name'])) $clients[] = $details['client_name'];
                        }
                    }
                    if (!empty($clients)) {
                        $builder->whereIn('m.location_name', array_unique($clients)); // alert_location_master location_name maps to client_name logically here
                    } else {
                        $builder->where('1=0');
                    }
                }
            } else {
                $builder->where('1=0');
            }
        }
        $builder->where('alert_sixs_audit.status !=', 5);
        
        $tdata['table_data'] = $builder->get()->getResultArray();
             $statusMessages = [
                0 => '<span class="badge badge-warning">Pending</span>',
                1 => '<span class="badge badge-info">Verify</span>',
                2 => '<span class="badge badge-primary">Inprogress</span>',
                3 => '<span class="badge badge-success">Close</span>',
                4 => '<span class="badge badge-danger">Reject</span>',
                5 => '<span class="badge badge-secondary">Deleted</span>'
            ];
        
          foreach($tdata['table_data'] as $key=>$row){
            $tdata['table_data'][$key]['sr_no'] = $key+1;
            
            $delete = '';
            if (canDeleteSixsAudit($row)) {
			    $delete = '<button data-ajax-url="'.base_url("Masters/Sixs_audit/save_details/".$row['audit_id']).'/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);">
                			<span class="indicator-label svg-icon svg-icon-2">
								<i class="fa fa-trash"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
            }
            
            $edit = '';
            if (canEditSixsAudit($row)) {
                $edit = '<button data-ajax-url="'.base_url("Masters/Sixs_audit/get_form_data/".$row['audit_id']).'" class="btn btn-icon btn-primary" onclick="edit_id(this,'.$row['audit_id'].');" title="edit">
        						<span class="indicator-label svg-icon svg-icon-3">
        							<i class="fa fa-edit"></i>
        						</span>
                                <span class="indicator-progress">
                                    <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                </span>
        						</button>';
            }
		
    		$pdf = '<button onclick=\'window.location.href="'.base_url("Masters/Sixs_audit/sixsAuditPdf/".$row['audit_id']).'"\' class="btn btn-icon btn-danger" title="pdf">
    						<span class="indicator-label svg-icon svg-icon-3">
    							<i class="fa fa-file-pdf"></i>
    						</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
    						</button>';
			$loginas = '<button onclick=\'window.location.href="'.base_url("Masters/Sixs_audit/auditComment/".$row['audit_id']).'"\' class="btn btn-icon btn-primary" title="comment">
    						<span class="indicator-label svg-icon svg-icon-3">
    							<i class="fa fa-comments"></i>
    						</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
    						</button>';

											if($row['status']=="0"){
											    $tdata['table_data'][$key]['tr_class']="bg-light-warning";
											}else if($row['status']=="4"){
											    $tdata['table_data'][$key]['tr_class']="bg-light-danger";
											}
			$tdata['table_data'][$key]['status']="<center>".$statusMessages[$row['status'] ?? 0]."</center>";
			$tdata['table_data'][$key]['action']="<center>".$delete.$edit.$pdf.$loginas."</center>";
        	$tdata['table_data'][$key]["nc_photo"] = "<img src='".base_url($tdata['table_data'][$key]["nc_photo"])."' onerror=\"this.src='".env("defaultLogo")."'\" height='50' width='50' />";
         	$tdata['table_data'][$key]["after_photo"] = "<img src='".base_url($tdata['table_data'][$key]["after_photo"])."' onerror=\"this.src='".env("defaultLogo")."'\" height='50' width='50' />";

        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        echo json_encode($tdata);
    }
    public function get_form_data($id){
        $responce['status'] = "0";
        $responce['message'] = "Details not found";
        if(isset($id)){

            $responce['data'] = $this->BaseModel->find($id);
            $responce['status'] = "1";
            $responce['message'] = "Details found";
        }
        echo json_encode($responce);
    }
    public function save_details($id=null,$action=null){
        $request = service('request');
        $postData = $request->getPost();

        if(isset($postData['honeypot'])) {
             unset($postData['honeypot']);   
        }
        
        // Ensure Engineer cannot change core fields
        if (isset($id) && isEngineer() && !isAdmin()) {
            unset($postData['location']);
            unset($postData['devision_dept']);
            unset($postData['sixs_category']);
            unset($postData['nc_description']);
            unset($postData['nc_photo']);
            unset($postData['nc_photo_url']);
            unset($postData['action_required_dep']);
            unset($postData['responsibilty']);
            unset($postData['target_date']);
        }
        
        // Location-Department dependency validation
        if (empty($action) && isset($postData['location']) && isset($postData['devision_dept'])) {
            $db = db_connect();
            $loc = $db->table('alert_location_master')->where('location_name', $postData['location'])->get()->getRowArray();
            if ($loc) {
                $depExists = $db->table('alert_department_master')
                                ->where('location_id', $loc['location_id'])
                                ->where('department_name', $postData['devision_dept'])
                                ->get()->getRowArray();
                if (!$depExists) {
                    echo json_encode(['status' => '0', 'message' => 'Invalid Department for the selected Location.']);
                    return;
                }
            }
        }
        
        // Handle NC Photo (skip if engineer trying to modify)
        if (!isset($id) || !isEngineer() || isAdmin()) {
            $ncFile = $request->getFile('nc_photo');
            if ($ncFile && $ncFile->isValid() && !$ncFile->hasMoved()) {
                $newName = $ncFile->getRandomName();
                $ncFile->move(FCPATH . 'uploads/nc_photo', $newName);
                $postData['nc_photo'] = 'uploads/nc_photo/' . $newName;
            } else if(isset($postData['nc_photo_url']) && !empty($postData['nc_photo_url'])) {
                $postData['nc_photo'] = $postData['nc_photo_url'];
            }
        }
        unset($postData['nc_photo_url']);

        // Handle After Photo
        $afterFile = $request->getFile('after_photo');
        if ($afterFile && $afterFile->isValid() && !$afterFile->hasMoved()) {
            $newName = $afterFile->getRandomName();
            $afterFile->move(FCPATH . 'uploads/after_photo', $newName);
            $postData['after_photo'] = 'uploads/after_photo/' . $newName;
        } else if(isset($postData['after_photo_url']) && !empty($postData['after_photo_url'])) {
            $postData['after_photo'] = $postData['after_photo_url'];
        }
        unset($postData['after_photo_url']);
            
        if(isset($id)){
            $responce['message'] = "Data updation failed";
            if(isset($action)){
                switch($action){
                    case "delete":
                        $existing = $this->BaseModel->find($id);
                        if (!canDeleteSixsAudit($existing)) {
                            echo json_encode(['status' => '0', 'message' => 'You do not have permission to delete this record.']);
                            return;
                        }
                        $postData['status']= "5";
                        break;
                }
            }
            
            // ACL verification before update
            $existing = $this->BaseModel->find($id);
            if (!canEditSixsAudit($existing)) {
                echo json_encode(['status' => '0', 'message' => 'You do not have permission to modify this record.']);
                return;
            }
            
            $postData['update_date'] = date('Y-m-d H:i:s');
            if($this->BaseModel->update($id,$postData)){
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }
        }else{
            if (!canCreateSixsAudit()) {
                echo json_encode(['status' => '0', 'message' => 'Unauthorized access to create audit.']);
                return;
            }
            $postData['status']="0";
            $postData['user_id'] = getUserId();
            $postData['default_date'] = date('Y-m-d H:i:s');
            $postData['department_status'] = 'Pending';
            $postData['final_status_soi'] = 'Pending';
            
            $responce['status'] = "0";
            $responce['message'] = "Data insertion failed";
            
            if($this->BaseModel->insert($postData)){
                $id  = $this->BaseModel->insertID();
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";
            }
        }
        echo json_encode($responce);
    }
    
    private function checkAuditAccess($audit_id) {
        $db = db_connect();
        $audit = $db->table('alert_sixs_audit')->where('audit_id', $audit_id)->get()->getRowArray();
        return canViewSixsAudit($audit);
    }

    function auditView($id=null){
        if (!canViewData() || !$this->checkAuditAccess($id)) {
            return redirect()->to(base_url('Masters/Sixs_audit'))->with('error', 'Unauthorized access.');
        }
        
        $db = db_connect();
        if(isset($id)){
            $data['details'] = $db->table('alert_sixs_audit')
                ->select('alert_sixs_audit.*, alert_users.*')
                ->join('alert_users', 'alert_users.user_id = alert_sixs_audit.user_id', 'left')
                ->where('audit_id', $id)
                ->get()->getResultArray();
                
            $print= " <a href=".base_url()."Masters/sixsAuditPdf/".$id." class='btn btn-outline-info' >Print Pdf</a>";
            $data['print']=$print;
            return view("Master/sixs_audit_details_pdf",$data);
        }
    }
    
    public function auditComment($audit_id=null){
        if (!canViewData() || !$this->checkAuditAccess($audit_id)) {
            return redirect()->to(base_url('Masters/Sixs_audit'))->with('error', 'Unauthorized access.');
        }
        
        $db = db_connect();
        if(isset($audit_id)){
            $data['login_id'] = getUserId();
            $data['details'] = $db->table('alert_sixs_audit')
                ->select('alert_sixs_audit.*, alert_users.user_id, alert_users.user_email, alert_users.user_contact, alert_users.status, alert_users.user_name, alert_users.user_designation')
                ->select('(SELECT count(*) FROM alert_sixs_audit_comment WHERE alert_sixs_audit_comment.audit_id = alert_sixs_audit.audit_id) as comment_count')
                ->select('(SELECT count(DISTINCT user_id) FROM alert_sixs_audit_comment WHERE alert_sixs_audit_comment.audit_id = alert_sixs_audit.audit_id) as user_count')
                ->join('alert_users', 'alert_users.user_id = alert_sixs_audit.user_id', 'left')
                ->where('alert_sixs_audit.audit_id', $audit_id)
                ->get()->getResultArray();

            $data['sixs_comments'] = $db->table('alert_sixs_audit_comment')
                ->select('alert_sixs_audit_comment.*, alert_users.user_name, alert_users.user_designation')
                ->join('alert_users', 'alert_users.user_id = alert_sixs_audit_comment.user_id', 'left')
                ->where('alert_sixs_audit_comment.audit_id', $audit_id)
                ->orderBy('alert_sixs_audit_comment.comment_id', 'DESC')
                ->get()->getResultArray();
                
            $data['form_action'] = base_url("Masters/Sixs_audit/acceptSixsComments");
            $data['button_id']="test";
            $data['table']="";
            return view("Master/audit_view_comment",$data);
        }
    }

    function acceptSixsComments(){
        $db=db_connect();
        $request = service('request');
        $comment_text = $request->getPost('comment_text');
        $audit_id = $request->getPost('audit_id');
        
        $audit = $db->table('alert_sixs_audit')->where('audit_id', $audit_id)->get()->getRowArray();
        
        if (!canCommentSixsAudit($audit)) {
            return redirect()->back()->with('error', 'Unauthorized to add comments.');
        }

        if ($audit && ($audit['status'] == 3 || $audit['status'] == 5)) {
            return redirect()->back()->with('error', 'Chat is disabled for closed records.');
        }
        
        if(!empty($comment_text) && !empty($audit_id)){
            $TableValues = [
                'comment_text' => $comment_text,
                'audit_id' => $audit_id,
                'user_id' => getUserId(),
                'default_date' => date('Y-m-d H:i:s'),
                'status' => 1
            ];
            $db->table("alert_sixs_audit_comment")->insert($TableValues);
            return redirect()->back()->with('success', 'Comment added successfully.');
        }
        return redirect()->back()->with('error', 'Invalid comment data.');
    }
    
    public function sixsAuditPdf($audit_id) {
        $db = db_connect();
        $audit = $db->table('alert_sixs_audit')->where('audit_id', $audit_id)->get()->getRowArray();
        
        if (!canDownloadSixsPdf($audit)) {
            return redirect()->to(base_url('Masters/Sixs_audit'))->with('error', 'Unauthorized access.');
        }
        
        $options = new Options();
        $options->set('defaultFont', 'Courier');
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
    
        $data['details'] = $db->table('alert_sixs_audit')
            ->select('alert_sixs_audit.*, alert_users.*')
            ->join('alert_users', 'alert_users.user_id = alert_sixs_audit.user_id', 'left')
            ->where('audit_id', $audit_id)
            ->get()->getResultArray();
           
        $data['sixs_comments'] = $db->table('alert_sixs_audit_comment')
            ->select('alert_users.*, alert_sixs_audit_comment.*')
            ->join('alert_users', 'alert_users.user_id = alert_sixs_audit_comment.user_id', 'left')
            ->where('audit_id', $audit_id)
            ->get()->getResultArray();
   
        $html = view("Master/sixs_audit_details_pdf", $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        
        if (ob_get_length()) {
            ob_end_clean();
        }

        $dompdf->render();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="6sAudit-'.$audit_id.'.pdf"');
        echo $dompdf->output();
    }
}
