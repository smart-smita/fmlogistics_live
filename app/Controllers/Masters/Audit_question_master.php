<?php

namespace App\Controllers\Masters;

use Dompdf\Options;
use Dompdf\Dompdf;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;

class Audit_question_master extends BaseController
{
    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct(){
        helper("form");
        $db = null;
        $db['table']         = 'alert_audit_questions';
        $db['allowedFields'] = ['audit_template_id', 'audit_site_category_name', 'audit_sub_category_name', 'audit_category_id', 'audit_category', 'audit_question', 'capa_json', 'audit_question_default_value', 'status'];
        $db['primaryKey']    = "question_id";
        $this->BaseModel     = new CRUDBaseModel($db);
    }

    public function index()
    {
        $data = [];
        $tdata['title']       = "HSE Audit Questions";
        $tdata['button_name'] = "Add HSE Question";
        $tdata['button_id']   = "user_modal";
        
        $tdata['display_contents'] = [
            "question_id"               => "ID",
            "audit_site_category_name"  => "Site Category",
            "audit_sub_category_name"   => "Sub Category",
            "audit_category_id"         => "Cat ID",
            "audit_category"            => "Category",
            "audit_question"            => "Question",
            "audit_question_default_value" => "Default",
            "action"                    => "Action"
        ];
        $tdata['is_server_side']    = "true";
        $data['ajax_url']           = base_url("Masters/Audit_question_master/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Audit_question_master/table_ajax");
        $tdata['column_defs']       = '[{"targets": [7], "orderable": false}]';

        $data['table']              = view("Layout/table-view", $tdata);
        
        $db = db_connect();
        //$data['name_list'] = $db->table("alert_audit_category_master")->where("status", "1")->get()->getResultArray();
        $data['site_categories'] = $db->table("alert_hse_site_category")->where("status", 1)->get()->getResultArray();
        $data['sub_categories'] = $db->table("alert_hse_sub_category")->where("status", 1)->get()->getResultArray();
        
        return view("Master/audit_question_master", $data);
    }

    public function table_ajax(){
        $request = service('request');
        $postData = $request->getVar();

        $draw = isset($postData['draw']) ? intval($postData['draw']) : 0;
        $start = isset($postData['start']) ? intval($postData['start']) : 0;
        $length = isset($postData['length']) ? intval($postData['length']) : 15;
        $searchValue = trim($postData['search']['value'] ?? '');
        
        $columns = [
            0 => "question_id",
            1 => "audit_site_category_name",
            2 => "audit_sub_category_name",
            3 => "audit_category_id",
            4 => "audit_category",
            5 => "audit_question",
            6 => "audit_question_default_value",
            7 => "question_id"
        ];

        $orderColumnIndex = isset($postData['order'][0]['column']) ? intval($postData['order'][0]['column']) : 0;
        $orderDir = isset($postData['order'][0]['dir']) && strtoupper($postData['order'][0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
        $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'question_id';

        $db = db_connect();

        $activeTemplate = $db->table('alert_audit_template')
            ->where('audit_template_type', 'HSE')
            ->where('status', 1)
            ->orderBy('audit_template_id', 'DESC')
            ->get()
            ->getRowArray();
        $activeTemplateId = $activeTemplate ? $activeTemplate['audit_template_id'] : 0;

        $totalBuilder = $db->table('alert_audit_questions')
            ->where('audit_template_id', $activeTemplateId)
            ->where('status !=', 2);
        $totalRecords = $totalBuilder->countAllResults();

        $filteredBuilder = $db->table('alert_audit_questions')
            ->where('audit_template_id', $activeTemplateId)
            ->where('status !=', 2);
        if ($searchValue !== '') {
            $filteredBuilder->groupStart()
                ->like('audit_site_category_name', $searchValue)
                ->orLike('audit_sub_category_name', $searchValue)
                ->orLike('audit_category_id', $searchValue)
                ->orLike('audit_category', $searchValue)
                ->orLike('audit_question', $searchValue)
                ->orLike('audit_question_default_value', $searchValue)
                ->groupEnd();
        }
        $recordsFiltered = $filteredBuilder->countAllResults();

        $dataBuilder = $db->table('alert_audit_questions')
            ->select('question_id,audit_site_category_name,audit_sub_category_name,audit_category_id,audit_category,audit_question,audit_question_default_value,status')
            ->where('audit_template_id', $activeTemplateId)
            ->where('status !=', 2);

        if ($searchValue !== '') {
            $dataBuilder->groupStart()
                ->like('audit_site_category_name', $searchValue)
                ->orLike('audit_sub_category_name', $searchValue)
                ->orLike('audit_category_id', $searchValue)
                ->orLike('audit_category', $searchValue)
                ->orLike('audit_question', $searchValue)
                ->orLike('audit_question_default_value', $searchValue)
                ->groupEnd();
        }

        $dataBuilder->orderBy($orderColumn, $orderDir);
        if ($length != -1) {
            $dataBuilder->limit($length, $start);
        }

        $table_data = $dataBuilder->get()->getResultArray();

        $data = [];
        foreach ($table_data as $row) {
            $id = $row['question_id'] ?? 0;
            $status = $row['status'] ?? 0;
            $lock_unlock = '';

            if ($status == 1) {
                $lock_unlock = '<button class="btn btn-icon btn-success" onclick="ajax_call(\''.base_url("Masters/Audit_question_master/save_details/" . $id) . '/deactive\',{},$(this),function(res){ try{ if(typeof res===\'string\') res=JSON.parse(res); if(res.status==1){ toastr.success(res.message); reload_data_table(); }else{ toastr.warning(res.message); } }catch(e){ console.error(e); toastr.error(\'Error processing lock request\'); } });" title="Lock"><span class="indicator-label"><i class="fa fa-unlock"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm"></span></span></button> ';
            } elseif ($status == 0) {
                $lock_unlock = '<button class="btn btn-icon btn-danger" onclick="ajax_call(\''.base_url("Masters/Audit_question_master/save_details/" . $id) . '/active\',{},$(this),function(res){ try{ if(typeof res===\'string\') res=JSON.parse(res); if(res.status==1){ toastr.success(res.message); reload_data_table(); }else{ toastr.warning(res.message); } }catch(e){ console.error(e); toastr.error(\'Error processing unlock request\'); } });" title="Unlock"><span class="indicator-label"><i class="fa fa-lock"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm"></span></span></button> ';
            }

            $edit = '';
            $delete = '';
            if ($status == 1) {
                $edit = '<button data-ajax-url="' . base_url("Masters/Audit_question_master/get_form_data/" . $id) . '" class="btn btn-icon btn-primary" onclick="edit_id(this,' . $id . ');" title="Edit"><span class="indicator-label"><i class="fa fa-edit"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm"></span></span></button> ';
                $delete = '<button data-ajax-url="' . base_url("Masters/Audit_question_master/save_details/" . $id) . '/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);" title="Delete"><span class="indicator-label"><i class="fa fa-trash"></i></span><span class="indicator-progress"><span class="spinner-border spinner-border-sm"></span></span></button> ';
            }

            $tr_class = '';
            if ($status == 2) {
                $tr_class = 'bg-light-danger';
                $lock_unlock = '<span class="badge badge-danger">Deleted</span>';
            } elseif ($status == 0) {
                $tr_class = 'bg-light-warning';
            }

            $row['tr_class'] = $tr_class;
            $row['action'] = $lock_unlock . $edit . $delete;
            $data[] = $row;
        }

        $response = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];

        return $this->response->setJSON($response);
    }

    public function get_form_data($id) {
        $response['status']  = "0";
        $response['message'] = "Details not found";
       
        if (isset($id)) {
            $data = $this->BaseModel->find($id);
            if ($data) {
                if (!empty($data['capa_json'])) {
                    $data['capa_json_decoded'] = json_decode($data['capa_json'], true);
                }
                $response['data']    = $data;
                $response['status']  = "1";
                $response['message'] = "Details found";
            }
        }
        return $this->response->setJSON($response);
    }

    public function save_details($id=null, $action=null){
        $request = service('request');
        $postData = $request->getVar();
        
        if(isset($postData['honeypot'])) unset($postData['honeypot']);

        $db = db_connect();
        $db->transStart();

        $activeTemplate = $db->table('alert_audit_template')
            ->where('audit_template_type', 'HSE')
            ->where('status', 1)
            ->orderBy('audit_template_id', 'DESC')
            ->get()
            ->getRowArray();

        $oldTemplateId = $activeTemplate ? $activeTemplate['audit_template_id'] : 0;
        $oldTemplateName = $activeTemplate ? $activeTemplate['audit_name'] : 'HSE Version 0';

        $versionNum = 1;
        if (preg_match('/Version\s+(\d+)/i', $oldTemplateName, $matches)) {
            $versionNum = intval($matches[1]) + 1;
        } else {
            $versionNum = 2;
        }
        $newTemplateName = "HSE Version " . $versionNum;

        if ($oldTemplateId > 0) {
            $db->table('alert_audit_template')
                ->where('audit_template_id', $oldTemplateId)
                ->update(['status' => 3]);
        }

        $newTemplateData = [
            'audit_name' => $newTemplateName,
            'audit_template_type' => 'HSE',
            'status' => 1,
            'update_date' => date('Y-m-d H:i:s')
        ];
        
        if ($activeTemplate) {
            $fieldsToCopy = ['auditor_name', 'auditee_name', 'client_name', 'region', 'frequency', 'frequency_type', 'location', 'score'];
            foreach ($fieldsToCopy as $f) {
                if(isset($activeTemplate[$f])) {
                    $newTemplateData[$f] = $activeTemplate[$f];
                }
            }
        }
        
        $db->table('alert_audit_template')->insert($newTemplateData);
        $newTemplateId = $db->insertID();

        if ($oldTemplateId > 0) {
            $oldQuestions = $db->table('alert_audit_questions')
                ->where('audit_template_id', $oldTemplateId)
                ->where('status !=', 2)
                ->get()
                ->getResultArray();

            foreach ($oldQuestions as $q) {
                $insertData = $q;
                unset($insertData['question_id']);
                $insertData['audit_template_id'] = $newTemplateId;
                
                if (empty($insertData['group_id'])) {
                    $insertData['group_id'] = $q['question_id']; 
                }
                
                $db->table('alert_audit_questions')->insert($insertData);
            }
        }

        $response['message'] = "Data operation failed";
        $response['status']  = "0";

        $oldValue = null;
        $targetQuestionId = null;
        $actionStr = "Add";

        $allowedFields = ['audit_template_id', 'audit_site_category_name', 'audit_sub_category_name', 'audit_category_id', 'audit_category', 'audit_question', 'capa_json', 'audit_question_default_value', 'status'];
        $filteredData = [];
        foreach ($allowedFields as $f) {
            if (isset($postData[$f])) {
                $filteredData[$f] = $postData[$f];
            }
        }

        if (isset($id)) {
            $oldQ = $db->table('alert_audit_questions')->where('question_id', $id)->get()->getRowArray();
            $targetGroup = $oldQ ? $oldQ['group_id'] : $id;
            
            $newQ = $db->table('alert_audit_questions')
                ->where('audit_template_id', $newTemplateId)
                ->where('group_id', $targetGroup)
                ->get()
                ->getRowArray();
                
            $oldValue = $oldQ ? json_encode($oldQ) : null;

            if ($newQ) {
                $targetQuestionId = $newQ['question_id'];
                if (isset($action)) {
                    switch ($action) {
                        case "active":   
                            $filteredData['status'] = "1"; 
                            $actionStr = "Activate";
                            break;
                        case "deactive": 
                            $filteredData['status'] = "0"; 
                            $actionStr = "Deactivate";
                            break;
                        case "delete":   
                            $filteredData['status'] = "2"; 
                            $actionStr = "Delete";
                            break;
                    }
                } else {
                    $actionStr = "Edit";
                }
                
                $filteredData['update_date'] = date('Y-m-d H:i:s');

                if ($db->table('alert_audit_questions')->where('question_id', $targetQuestionId)->update($filteredData)) {
                    $response['status']  = "1";
                    $response['message'] = "Data updated successfully";
                }
            } else {
                $response['message'] = "Target question not found in new template.";
            }
        } else {
            $filteredData['audit_template_id'] = $newTemplateId;
            $filteredData['status'] = "1";
            if ($db->table('alert_audit_questions')->insert($filteredData)) {
                $targetQuestionId = $db->insertID();
                $db->table('alert_audit_questions')->where('question_id', $targetQuestionId)->update(['group_id' => $targetQuestionId]);
                $response['status']  = "1";
                $response['message'] = "Data saved successfully";
                $actionStr = "Add";
            }
        }

        if ($response['status'] === "1") {
            $newValue = null;
            if ($targetQuestionId) {
                $newQRow = $db->table('alert_audit_questions')->where('question_id', $targetQuestionId)->get()->getRowArray();
                $newValue = $newQRow ? json_encode($newQRow) : null;
            }
            
            $db->table('alert_hse_question_audit_log')->insert([
                'user_name' => session()->get('login_name') ?? 'System',
                'action_performed' => $actionStr,
                'question_id' => $targetQuestionId,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'template_version_created' => $newTemplateName,
                'action_date' => date('Y-m-d H:i:s')
            ]);
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            $response['status']  = "0";
            $response['message'] = "Transaction failed.";
        } else {
            $db->transCommit();
        }

        return $this->response->setJSON($response);
    }

    public function download_pdf() {
        require_once APPPATH.'/ThirdParty/dompdf/autoload.inc.php';
        $db = db_connect();

        $activeTemplate = $db->table('alert_audit_template')
            ->where('audit_template_type', 'HSE')
            ->where('status', 1)
            ->orderBy('audit_template_id', 'DESC')
            ->get()
            ->getRowArray();
        $activeTemplateId = $activeTemplate ? $activeTemplate['audit_template_id'] : 0;

        $questions = $db->table('alert_audit_questions')
            ->where('audit_template_id', $activeTemplateId)
            ->where('status !=', 2)
            ->orderBy('audit_site_category_name', 'ASC')
            ->orderBy('audit_category_id', 'ASC')
            ->get()->getResultArray();

        $grouped = [];
        foreach($questions as $q) {
            $site = $q['audit_site_category_name'] ?: 'General';
            $cat = $q['audit_category'] ?: 'Uncategorized';
            $grouped[$site][$cat][] = $q;
        }

        $data['grouped_questions'] = $grouped;
        $html = view("Master/audit_questions_pdf", $data);

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="HSE_Audit_Questions.pdf"');
        echo $dompdf->output();
        exit();
    }
}
