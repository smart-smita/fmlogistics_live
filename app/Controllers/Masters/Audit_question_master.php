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

    public function __construct()
    {
        helper("form");
        $this->response = service('response');
        $this->request  = service('request');
        $db = null;
        $db['table']         = 'alert_hse_template_questions';
        $db['allowedFields'] = [
            'audit_template_id', 'question_order', 'category_id', 'category_name', 
            'question_text', 'default_value', 'findings', 'risk', 'actions_recommendation', 
            'action_category', 'ua_uc', 'risk_severity', 'risk_probability', 'color_code', 
            'cost_type', 'combined_risk_rating', 'note', 'capa_json', 'status'
        ];
        $db['primaryKey']    = "template_question_id";
        $this->BaseModel     = new CRUDBaseModel($db);
    }

    public function index()
    {
        $db = db_connect();
        $data = [];

        // Templates list (HSE)
        $data['template_list'] = $db->table("alert_audit_template")
            ->where("audit_template_type", "HSE")
            ->where("status !=", 2)
            ->orderBy("audit_template_id", "DESC")
            ->get()->getResultArray();

        // Selected template ID (from URL query param or first active HSE template)
        $selectedTemplateId = intval($this->request->getVar('audit_template_id') ?? 0);
        if ($selectedTemplateId <= 0) {
            $activeTemplate = $db->table("alert_audit_template")
                ->where("audit_template_type", "HSE")
                ->where("status", 1)
                ->orderBy("audit_template_id", "DESC")
                ->get()
                ->getRowArray();
            $selectedTemplateId = $activeTemplate ? intval($activeTemplate['audit_template_id']) : 3;
        }
        $data['selected_template_id'] = $selectedTemplateId;

        $tdata['title']       = "HSE Audit Questions";
        $tdata['button_name'] = "Add HSE Question";
        $tdata['button_id']   = "user_modal";
        
        $tdata['display_contents'] = [
            "template_question_id"      => "ID",
            "category_id"               => "Cat ID",
            "category_name"             => "Category",
            "question_text"             => "Question",
            "default_value"             => "Default",
            "config_status"             => "Type",
            "action"                    => "Action"
        ];
        $tdata['example2']          = "hse_questions_table";
        $tdata['is_server_side']    = "true";
        $tdata['ajax_type']         = "POST";
        $data['ajax_url']           = base_url("Masters/Audit_question_master/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Audit_question_master/table_ajax");
        $tdata['column_defs']       = '[{"targets": [6], "orderable": false}]';
        $tdata['script']            = '
        $(document).on("preXhr.dt", function(e, settings, data) {
            data.audit_template_id = $("#filter_template_id").val() || ' . $selectedTemplateId . ';
            data.site_name = $("#filter_site_name").val() || "";
            data.site_category = $("#filter_site_category").val() || "";
            data.sub_category = $("#filter_sub_category").val() || "";
        });
        ';

        $data['table']              = view("Layout/table-view", $tdata);

        // Sites list
        $data['site_list'] = $db->table("alert_hse_client_master")
            ->select("DISTINCT(client_name) as client_name, location, region")
            ->where("status !=", 2)
            ->orderBy("client_name", "ASC")
            ->get()->getResultArray();

        $data['site_categories'] = $db->table("alert_hse_site_category")->where("status", 1)->get()->getResultArray();
        $data['sub_categories'] = $db->table("alert_hse_sub_category")->where("status", 1)->get()->getResultArray();
        
        return view("Master/audit_question_master", $data);
    }

    public function table_ajax()
    {
        $request = service('request');
        $postData = $request->getVar();

        $draw = isset($postData['draw']) ? intval($postData['draw']) : 0;
        $start = isset($postData['start']) ? intval($postData['start']) : 0;
        $length = isset($postData['length']) ? intval($postData['length']) : 15;
        $searchValue = trim($postData['search']['value'] ?? '');
        
        $templateId = !empty($postData['audit_template_id']) ? intval($postData['audit_template_id']) : intval($request->getGet('audit_template_id') ?? 0);
        $siteName = trim($postData['site_name'] ?? '');
        $siteCategory = trim($postData['site_category'] ?? '');
        $subCategory = trim($postData['sub_category'] ?? '');

        $db = db_connect();

        // If no template selected, default to active HSE template
        if ($templateId <= 0) {
            $activeTemplate = $db->table('alert_audit_template')
                ->where('audit_template_type', 'HSE')
                ->where('status', 1)
                ->orderBy('audit_template_id', 'DESC')
                ->get()
                ->getRowArray();
            $templateId = $activeTemplate ? intval($activeTemplate['audit_template_id']) : 3;
        }

        // Check if template has audits performed (LOCK RULE)
        $hasAudits = $db->table('alert_hse_audit_master')
            ->where('audit_template_id', $templateId)
            ->where('status !=', 2)
            ->countAllResults();
        $isTemplateLocked = ($hasAudits > 0);

        // Columns for ordering
        $columns = [
            0 => "template_question_id",
            1 => "category_id",
            2 => "category_name",
            3 => "question_text",
            4 => "default_value",
            5 => "template_question_id",
            6 => "template_question_id"
        ];

        $orderColumnIndex = isset($postData['order'][0]['column']) ? intval($postData['order'][0]['column']) : 0;
        $orderDir = isset($postData['order'][0]['dir']) && strtoupper($postData['order'][0]['dir']) === 'DESC' ? 'DESC' : 'ASC';
        $orderColumn = isset($columns[$orderColumnIndex]) ? $columns[$orderColumnIndex] : 'question_order';

        $totalBuilder = $db->table('alert_hse_template_questions')
            ->where('audit_template_id', $templateId)
            ->where('status !=', 2);
        $totalRecords = $totalBuilder->countAllResults();

        $filteredBuilder = $db->table('alert_hse_template_questions')
            ->where('audit_template_id', $templateId)
            ->where('status !=', 2);

        if ($searchValue !== '') {
            $filteredBuilder->groupStart()
                ->like('category_id', $searchValue)
                ->orLike('category_name', $searchValue)
                ->orLike('question_text', $searchValue)
                ->orLike('default_value', $searchValue)
                ->orLike('findings', $searchValue)
                ->groupEnd();
        }
        $recordsFiltered = $filteredBuilder->countAllResults();

        $dataBuilder = $db->table('alert_hse_template_questions')
            ->where('audit_template_id', $templateId)
            ->where('status !=', 2);

        if ($searchValue !== '') {
            $dataBuilder->groupStart()
                ->like('category_id', $searchValue)
                ->orLike('category_name', $searchValue)
                ->orLike('question_text', $searchValue)
                ->orLike('default_value', $searchValue)
                ->orLike('findings', $searchValue)
                ->groupEnd();
        }

        $dataBuilder->orderBy($orderColumn, $orderDir);
        if ($length != -1) {
            $dataBuilder->limit($length, $start);
        }

        $baseRows = $dataBuilder->get()->getResultArray();

        // Check if overrides exist in alert_audit_questions for these rows if site/category is selected
        $overrides = [];
        if (!empty($baseRows) && (!empty($siteName) || !empty($siteCategory) || !empty($subCategory))) {
            $tqIds = array_column($baseRows, 'template_question_id');
            $ovBuilder = $db->table('alert_audit_questions')
                ->where('audit_template_id', $templateId)
                ->where('status !=', 2)
                ->whereIn('template_question_id', $tqIds);

            if (!empty($siteName)) {
                $ovBuilder->where('site_name', $siteName);
            }
            if (!empty($siteCategory)) {
                $ovBuilder->where('audit_site_category_name', $siteCategory);
            }
            if (!empty($subCategory)) {
                $ovBuilder->where('audit_sub_category_name', $subCategory);
            }

            $ovRows = $ovBuilder->get()->getResultArray();
            foreach ($ovRows as $ov) {
                $overrides[$ov['template_question_id']] = $ov;
            }
        }

        $data = [];
        foreach ($baseRows as $row) {
            $tqId = $row['template_question_id'];
            $ov = $overrides[$tqId] ?? null;

            $hasOverride = !empty($ov);
            $displayQuestion = $hasOverride ? $ov['audit_question'] : $row['question_text'];
            $displayDefault = $hasOverride ? ($ov['audit_question_default_value'] ?? $row['default_value']) : $row['default_value'];

            $configStatus = $hasOverride 
                ? '<span class="badge badge-light-success"><i class="fas fa-check-circle text-success me-1"></i>Configured</span>'
                : '<span class="badge badge-light-primary">Template Default</span>';

            $actions = '';
            
            if ($isTemplateLocked && empty($siteName)) {
                // Template has audits performed & no site override is selected:
                // Canonical question is completely locked - NO EDIT and NO DELETE.
                $viewBtn = '<button type="button" class="btn btn-icon btn-light-info btn-sm me-1" onclick="open_config_modal(' . $tqId . ', true);" title="View Question Details (Locked - Audits Performed)"><i class="fa fa-eye"></i></button>';
                $lockBadge = '<span class="badge badge-light-danger" title="Locked: ' . $hasAudits . ' audit(s) performed. Cannot edit or delete."><i class="fa fa-lock text-danger me-1"></i>Locked</span>';
                $actions = '<div class="d-flex align-items-center flex-nowrap">' . $viewBtn . $lockBadge . '</div>';
            } else if (!empty($siteName)) {
                // Site-specific configuration mode
                if ($hasOverride) {
                    $editBtn = '<button type="button" class="btn btn-icon btn-success btn-sm me-1" onclick="open_config_modal(' . $tqId . ', false);" title="Edit Site Override"><i class="fa fa-edit"></i></button>';
                    $resetBtn = '<button type="button" class="btn btn-icon btn-warning btn-sm" onclick="reset_override(' . $ov['question_id'] . ');" title="Reset to Template Default"><i class="fa fa-undo"></i></button>';
                    $actions = '<div class="d-flex align-items-center flex-nowrap">' . $editBtn . $resetBtn . '</div>';
                } else {
                    $configBtn = '<button type="button" class="btn btn-sm btn-light-primary text-nowrap" onclick="open_config_modal(' . $tqId . ', false);" title="Configure Override for Site"><i class="fa fa-sliders-h me-1"></i>Customize</button>';
                    $actions = $configBtn;
                }
            } else {
                // Template has NO audits performed (open for editing base questions)
                $editBtn = '<button type="button" class="btn btn-icon btn-primary btn-sm me-1" onclick="open_config_modal(' . $tqId . ', false);" title="Edit Base Template Question"><i class="fa fa-edit"></i></button>';
                $deleteBtn = '<button type="button" class="btn btn-icon btn-danger btn-sm" onclick="delete_row(this, ' . $tqId . ');" title="Delete Template Question"><i class="fa fa-trash"></i></button>';
                $actions = '<div class="d-flex align-items-center flex-nowrap">' . $editBtn . $deleteBtn . '</div>';
            }

            $data[] = [
                'template_question_id' => $tqId,
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'question_text' => htmlspecialchars($displayQuestion, ENT_QUOTES, 'UTF-8'),
                'default_value' => htmlspecialchars($displayDefault, ENT_QUOTES, 'UTF-8'),
                'config_status' => $configStatus,
                'action' => $actions
            ];
        }

        return $this->response->setJSON([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
            'is_locked' => $isTemplateLocked,
            'audit_count' => $hasAudits
        ]);
    }

    public function get_form_data($id)
    {
        $response = ['status' => "0", 'message' => "Details not found"];
        $request = service('request');
        $siteName = trim($request->getVar('site_name') ?? '');
        $siteCategory = trim($request->getVar('site_category') ?? '');
        $subCategory = trim($request->getVar('sub_category') ?? '');
        $templateId = intval($request->getVar('audit_template_id') ?? 0);

        $db = db_connect();

        // 1. Fetch base question from alert_hse_template_questions
        $baseQuestion = $db->table('alert_hse_template_questions')
            ->where('template_question_id', $id)
            ->get()
            ->getRowArray();

        if (!$baseQuestion) {
            return $this->response->setJSON($response);
        }

        $tId = $baseQuestion['audit_template_id'];

        // Check lock
        $hasAudits = $db->table('alert_hse_audit_master')
            ->where('audit_template_id', $tId)
            ->where('status !=', 2)
            ->countAllResults();
        $isLocked = ($hasAudits > 0);

        // 2. Check if override exists in alert_audit_questions
        $override = null;
        if (!empty($siteName) || !empty($siteCategory) || !empty($subCategory)) {
            $ovBuilder = $db->table('alert_audit_questions')
                ->where('audit_template_id', $tId)
                ->where('template_question_id', $id)
                ->where('status !=', 2);

            if (!empty($siteName)) {
                $ovBuilder->where('site_name', $siteName);
            }
            if (!empty($siteCategory)) {
                $ovBuilder->where('audit_site_category_name', $siteCategory);
            }
            if (!empty($subCategory)) {
                $ovBuilder->where('audit_sub_category_name', $subCategory);
            }

            $override = $ovBuilder->get()->getRowArray();
        }

        $isOverride = !empty($override);

        $data = [
            'template_question_id' => $baseQuestion['template_question_id'],
            'audit_template_id' => $tId,
            'site_name' => $isOverride ? ($override['site_name'] ?? $siteName) : $siteName,
            'audit_site_category_name' => $isOverride ? ($override['audit_site_category_name'] ?? $siteCategory) : $siteCategory,
            'audit_sub_category_name' => $isOverride ? ($override['audit_sub_category_name'] ?? $subCategory) : $subCategory,
            'category_id' => $baseQuestion['category_id'],
            'category_name' => $isOverride ? ($override['audit_category'] ?? $baseQuestion['category_name']) : $baseQuestion['category_name'],
            'question_text' => $isOverride ? ($override['audit_question'] ?? $baseQuestion['question_text']) : $baseQuestion['question_text'],
            'default_value' => $isOverride ? ($override['audit_question_default_value'] ?? $baseQuestion['default_value']) : $baseQuestion['default_value'],
            'findings' => $isOverride ? ($override['findings'] ?? $baseQuestion['findings']) : $baseQuestion['findings'],
            'risk' => $isOverride ? ($override['risk'] ?? $baseQuestion['risk']) : $baseQuestion['risk'],
            'actions_recommendation' => $isOverride ? ($override['actions_recommendation'] ?? $baseQuestion['actions_recommendation']) : $baseQuestion['actions_recommendation'],
            'action_category' => $isOverride ? ($override['action_category'] ?? $baseQuestion['action_category']) : $baseQuestion['action_category'],
            'ua_uc' => $isOverride ? ($override['ua_uc'] ?? $baseQuestion['ua_uc']) : $baseQuestion['ua_uc'],
            'risk_severity' => $isOverride ? ($override['risk_severity'] ?? $baseQuestion['risk_severity']) : $baseQuestion['risk_severity'],
            'risk_probability' => $isOverride ? ($override['risk_probability'] ?? $baseQuestion['risk_probability']) : $baseQuestion['risk_probability'],
            'color_code' => $isOverride ? ($override['color_code'] ?? $baseQuestion['color_code']) : $baseQuestion['color_code'],
            'cost_type' => $isOverride ? ($override['cost_type'] ?? $baseQuestion['cost_type']) : $baseQuestion['cost_type'],
            'combined_risk_rating' => $isOverride ? ($override['combined_risk_rating'] ?? $baseQuestion['combined_risk_rating']) : $baseQuestion['combined_risk_rating'],
            'note' => $isOverride ? ($override['note'] ?? $baseQuestion['note']) : $baseQuestion['note'],
            'is_override' => $isOverride,
            'is_locked' => $isLocked,
            'question_id' => $isOverride ? $override['question_id'] : null
        ];

        return $this->response->setJSON([
            'status' => "1",
            'message' => "Details found",
            'data' => $data
        ]);
    }

    public function save_details($id = null, $action = null)
    {
        $request = service('request');
        $rawVar = $request->getVar();
        $postData = (is_array($rawVar) && !empty($rawVar)) ? $rawVar : (is_array($_POST) ? $_POST : []);
        
        if (isset($postData['honeypot'])) unset($postData['honeypot']);

        $db = db_connect();

        $templateQuestionId = intval($postData['template_question_id'] ?? ($id ?: 0));
        $templateId = intval($postData['audit_template_id'] ?? 0);
        $siteName = trim($postData['site_name'] ?? '');
        $siteCategory = trim($postData['audit_site_category_name'] ?? ($postData['site_category'] ?? ''));
        $subCategory = trim($postData['audit_sub_category_name'] ?? ($postData['sub_category'] ?? ''));

        if ($templateQuestionId <= 0 && isset($id)) {
            $templateQuestionId = intval($id);
        }

        // Fetch base question
        $baseQuestion = $db->table('alert_hse_template_questions')
            ->where('template_question_id', $templateQuestionId)
            ->get()
            ->getRowArray();

        if (!$baseQuestion) {
            return $this->response->setJSON([
                'status' => "0",
                'message' => "Base template question not found."
            ]);
        }

        if ($templateId <= 0) {
            $templateId = intval($baseQuestion['audit_template_id']);
        }

        // Check if template is locked by performed audits
        $hasAudits = $db->table('alert_hse_audit_master')
            ->where('audit_template_id', $templateId)
            ->where('status !=', 2)
            ->countAllResults();
        $isLocked = ($hasAudits > 0);

        // Delete action handling
        if ($action === 'delete') {
            if ($isLocked) {
                return $this->response->setJSON([
                    'status' => "0",
                    'message' => "Cannot delete: Template is locked because $hasAudits audit(s) have already been performed."
                ]);
            }
            $db->table('alert_hse_template_questions')->where('template_question_id', $templateQuestionId)->update(['status' => 2]);
            return $this->response->setJSON(['status' => "1", 'message' => "Template question deleted successfully."]);
        }

        // Reset override action
        if ($action === 'reset_override' || (!empty($postData['action']) && $postData['action'] === 'reset_override')) {
            $overrideId = intval($postData['question_id'] ?? $id);
            if ($overrideId > 0) {
                $db->table('alert_audit_questions')->where('question_id', $overrideId)->delete();
                return $this->response->setJSON(['status' => "1", 'message' => "Configuration reset to template default."]);
            }
        }

        // Lock / Unlock action on template question
        if ($action === 'active' || $action === 'deactive') {
            if ($isLocked) {
                return $this->response->setJSON([
                    'status' => "0",
                    'message' => "Cannot change status: Template is locked because audits have already been conducted."
                ]);
            }
            $newStatus = ($action === 'active') ? 1 : 0;
            $db->table('alert_hse_template_questions')->where('template_question_id', $templateQuestionId)->update(['status' => $newStatus]);
            return $this->response->setJSON(['status' => "1", 'message' => "Status updated successfully."]);
        }

        // 15 fields
        $categoryId = trim($postData['category_id'] ?? $baseQuestion['category_id']);
        $categoryName = trim($postData['category_name'] ?? ($postData['audit_category'] ?? $baseQuestion['category_name']));
        $questionText = trim($postData['question_text'] ?? ($postData['audit_question'] ?? $baseQuestion['question_text']));
        $defaultValue = isset($postData['default_value']) 
            ? trim($postData['default_value']) 
            : (isset($postData['audit_question_default_value']) ? trim($postData['audit_question_default_value']) : ($baseQuestion['default_value'] ?? ''));
        $findings = trim($postData['findings'] ?? $baseQuestion['findings']);
        $risk = trim($postData['risk'] ?? $baseQuestion['risk']);
        $actions = trim($postData['actions_recommendation'] ?? $baseQuestion['actions_recommendation']);
        $actionCat = trim($postData['action_category'] ?? $baseQuestion['action_category']);
        $uaUc = trim($postData['ua_uc'] ?? $baseQuestion['ua_uc']);
        $severity = trim($postData['risk_severity'] ?? $baseQuestion['risk_severity']);
        $probability = trim($postData['risk_probability'] ?? $baseQuestion['risk_probability']);
        $colorCode = trim($postData['color_code'] ?? $baseQuestion['color_code']);
        $costType = trim($postData['cost_type'] ?? $baseQuestion['cost_type']);
        $combinedRisk = trim($postData['combined_risk_rating'] ?? $baseQuestion['combined_risk_rating']);
        $note = trim($postData['note'] ?? $baseQuestion['note']);

        // Build CAPA structure
        $capa = [
            "findings" => ["text" => "Findings", "yes" => "", "no" => $findings, "na" => ""],
            "risk" => ["text" => "Risk", "yes" => "", "no" => $risk, "na" => ""],
            "actions" => ["text" => "Actions/Recommendation", "yes" => "", "no" => $actions, "na" => ""],
            "action_category" => ["text" => "Action Category", "yes" => "", "no" => $actionCat, "na" => ""],
            "ua_uc" => ["text" => "UA-UC", "yes" => "", "no" => $uaUc, "na" => ""],
            "risk_severity" => ["text" => "Risk Severity", "yes" => "", "no" => $severity, "na" => ""],
            "risk_probability" => ["text" => "Risk Probability", "yes" => "", "no" => $probability, "na" => ""],
            "color_code" => ["text" => "Color Code", "yes" => "", "no" => $colorCode, "na" => ""],
            "cost_type" => ["text" => "Cost Type", "yes" => "", "no" => $costType, "na" => ""],
            "combined_risk_rating" => ["text" => "Combined Risk Rating", "yes" => "", "no" => $combinedRisk, "na" => ""]
        ];

        // -------------------------------------------------------------------
        // CASE 1: Site / Category Specific Configuration -> Save in alert_audit_questions
        // -------------------------------------------------------------------
        if (!empty($siteName) || !empty($siteCategory) || !empty($subCategory)) {
            $ovBuilder = $db->table('alert_audit_questions')
                ->where('audit_template_id', $templateId)
                ->where('template_question_id', $templateQuestionId)
                ->where('site_name', $siteName)
                ->where('audit_site_category_name', $siteCategory)
                ->where('audit_sub_category_name', $subCategory)
                ->where('status !=', 2);

            $existing = $ovBuilder->get()->getRowArray();

            $saveData = [
                'template_question_id' => $templateQuestionId,
                'audit_template_id' => $templateId,
                'site_name' => $siteName,
                'audit_site_category_name' => $siteCategory,
                'audit_sub_category_name' => $subCategory,
                'audit_category_id' => $categoryId,
                'audit_category' => $categoryName,
                'audit_question' => $questionText,
                'audit_question_default_value' => $defaultValue,
                'findings' => $findings,
                'risk' => $risk,
                'actions_recommendation' => $actions,
                'action_category' => $actionCat,
                'ua_uc' => $uaUc,
                'risk_severity' => $severity,
                'risk_probability' => $probability,
                'color_code' => $colorCode,
                'cost_type' => $costType,
                'combined_risk_rating' => $combinedRisk,
                'note' => $note,
                'capa_json' => json_encode($capa),
                'status' => 1,
                'update_date' => date('Y-m-d H:i:s')
            ];

            if ($existing) {
                // UPDATE existing configuration
                $db->table('alert_audit_questions')
                    ->where('question_id', $existing['question_id'])
                    ->update($saveData);
                $msg = "Question configuration updated successfully.";
                $targetId = $existing['question_id'];
                $actionType = "Edit Override";
            } else {
                // INSERT new configuration
                $saveData['default_date'] = date('Y-m-d H:i:s');
                $saveData['group_id'] = $templateQuestionId;
                $db->table('alert_audit_questions')->insert($saveData);
                $targetId = $db->insertID();
                $msg = "Question configuration saved successfully.";
                $actionType = "Add Override";
            }

            // Log audit trail
            $db->table('alert_hse_question_audit_log')->insert([
                'user_name' => $this->getUserName(),
                'action_performed' => $actionType,
                'question_id' => $targetId,
                'old_value' => $existing ? json_encode($existing) : null,
                'new_value' => json_encode($saveData),
                'template_version_created' => "Site Config: $siteName | $siteCategory",
                'action_date' => date('Y-m-d H:i:s')
            ]);

            return $this->response->setJSON([
                'status' => "1",
                'message' => $msg
            ]);
        }

        // -------------------------------------------------------------------
        // CASE 2: Base Template Question Edit -> Save in alert_hse_template_questions
        // -------------------------------------------------------------------
        if ($isLocked) {
            return $this->response->setJSON([
                'status' => "0",
                'message' => "Base template question is locked because audits have already been performed using this template. Select a site/category to create a site-specific configuration."
            ]);
        }

        $baseUpdate = [
            'category_id' => $categoryId,
            'category_name' => $categoryName,
            'question_text' => $questionText,
            'default_value' => $defaultValue,
            'findings' => $findings,
            'risk' => $risk,
            'actions_recommendation' => $actions,
            'action_category' => $actionCat,
            'ua_uc' => $uaUc,
            'risk_severity' => $severity,
            'risk_probability' => $probability,
            'color_code' => $colorCode,
            'cost_type' => $costType,
            'combined_risk_rating' => $combinedRisk,
            'note' => $note,
            'capa_json' => json_encode($capa),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $db->table('alert_hse_template_questions')
            ->where('template_question_id', $templateQuestionId)
            ->update($baseUpdate);

        // Log audit trail
        $db->table('alert_hse_question_audit_log')->insert([
            'user_name' => $this->getUserName(),
            'action_performed' => "Edit Base Template Question",
            'question_id' => $templateQuestionId,
            'old_value' => json_encode($baseQuestion),
            'new_value' => json_encode($baseUpdate),
            'template_version_created' => "Base Template #$templateId",
            'action_date' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON([
            'status' => "1",
            'message' => "Template question updated successfully."
        ]);
    }

    public function download_pdf()
    {
        require_once APPPATH . '/ThirdParty/dompdf/autoload.inc.php';
        $db = db_connect();

        $templateId = intval($this->request->getVar('audit_template_id') ?? 0);
        if ($templateId <= 0) {
            $activeTemplate = $db->table('alert_audit_template')
                ->where('audit_template_type', 'HSE')
                ->where('status', 1)
                ->orderBy('audit_template_id', 'DESC')
                ->get()
                ->getRowArray();
            $templateId = $activeTemplate ? intval($activeTemplate['audit_template_id']) : 3;
        }

        $template = $db->table('alert_audit_template')->where('audit_template_id', $templateId)->get()->getRowArray();
        $templateName = $template ? $template['audit_name'] : "HSE Audit Template #$templateId";

        $questions = $db->table('alert_hse_template_questions')
            ->where('audit_template_id', $templateId)
            ->where('status !=', 2)
            ->orderBy('question_order', 'ASC')
            ->get()->getResultArray();

        $grouped = [];
        foreach ($questions as $q) {
            $cat = $q['category_name'] ?: 'Uncategorized';
            $grouped[$templateName][$cat][] = [
                'category_id' => $q['category_id'],
                'question_text' => $q['question_text'],
                'default_value' => $q['default_value'] ?? ''
            ];
        }

        $data['template_name'] = $templateName;
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

    public function export_csv()
    {
        $db = db_connect();

        $templateId = intval($this->request->getVar('audit_template_id') ?? 0);
        $siteName = trim($this->request->getVar('site_name') ?? '');
        $siteCategory = trim($this->request->getVar('site_category') ?? '');
        $subCategory = trim($this->request->getVar('sub_category') ?? '');
        $searchValue = trim($this->request->getVar('search') ?? '');

        if ($templateId <= 0) {
            $activeTemplate = $db->table('alert_audit_template')
                ->where('audit_template_type', 'HSE')
                ->where('status', 1)
                ->orderBy('audit_template_id', 'DESC')
                ->get()
                ->getRowArray();
            $templateId = $activeTemplate ? intval($activeTemplate['audit_template_id']) : 3;
        }

        $template = $db->table('alert_audit_template')->where('audit_template_id', $templateId)->get()->getRowArray();
        $templateName = $template ? preg_replace('/[^a-zA-Z0-9_\-]/', '_', $template['audit_name']) : "Template_{$templateId}";

        // Fetch questions from alert_hse_template_questions
        $builder = $db->table('alert_hse_template_questions')
            ->where('audit_template_id', $templateId)
            ->where('status !=', 2);

        if ($searchValue !== '') {
            $builder->groupStart()
                ->like('category_id', $searchValue)
                ->orLike('category_name', $searchValue)
                ->orLike('question_text', $searchValue)
                ->orLike('default_value', $searchValue)
                ->orLike('findings', $searchValue)
                ->groupEnd();
        }

        $questions = $builder->orderBy('question_order', 'ASC')->get()->getResultArray();

        // Fallback to alert_audit_questions if template questions table has no records for this template
        if (empty($questions)) {
            $fbBuilder = $db->table('alert_audit_questions')
                ->where('audit_template_id', $templateId)
                ->where('status !=', 2);

            if (!empty($siteName)) {
                $fbBuilder->where('site_name', $siteName);
            }
            if (!empty($siteCategory)) {
                $fbBuilder->where('audit_site_category_name', $siteCategory);
            }
            if (!empty($subCategory)) {
                $fbBuilder->where('audit_sub_category_name', $subCategory);
            }
            if ($searchValue !== '') {
                $fbBuilder->groupStart()
                    ->like('audit_category_id', $searchValue)
                    ->orLike('audit_category', $searchValue)
                    ->orLike('audit_question', $searchValue)
                    ->orLike('audit_question_default_value', $searchValue)
                    ->orLike('findings', $searchValue)
                    ->groupEnd();
            }

            $fbRows = $fbBuilder->get()->getResultArray();
            foreach ($fbRows as $fr) {
                $questions[] = [
                    'template_question_id' => $fr['template_question_id'] ?? $fr['question_id'],
                    'category_id' => $fr['audit_category_id'] ?? '',
                    'category_name' => $fr['audit_category'] ?? '',
                    'question_text' => $fr['audit_question'] ?? '',
                    'default_value' => $fr['audit_question_default_value'] ?? '',
                    'findings' => $fr['findings'] ?? '',
                    'risk' => $fr['risk'] ?? '',
                    'actions_recommendation' => $fr['actions_recommendation'] ?? '',
                    'action_category' => $fr['action_category'] ?? '',
                    'ua_uc' => $fr['ua_uc'] ?? '',
                    'risk_severity' => $fr['risk_severity'] ?? '',
                    'risk_probability' => $fr['risk_probability'] ?? '',
                    'color_code' => $fr['color_code'] ?? '',
                    'cost_type' => $fr['cost_type'] ?? '',
                    'combined_risk_rating' => $fr['combined_risk_rating'] ?? '',
                    'note' => $fr['note'] ?? '',
                    'capa_json' => $fr['capa_json'] ?? ''
                ];
            }
        }

        // Fetch overrides if site/category filters applied
        $overrides = [];
        if (!empty($questions) && (!empty($siteName) || !empty($siteCategory) || !empty($subCategory))) {
            $tqIds = array_filter(array_column($questions, 'template_question_id'));
            if (!empty($tqIds)) {
                $ovBuilder = $db->table('alert_audit_questions')
                    ->where('audit_template_id', $templateId)
                    ->where('status !=', 2)
                    ->whereIn('template_question_id', $tqIds);
                if (!empty($siteName)) {
                    $ovBuilder->where('site_name', $siteName);
                }
                if (!empty($siteCategory)) {
                    $ovBuilder->where('audit_site_category_name', $siteCategory);
                }
                if (!empty($subCategory)) {
                    $ovBuilder->where('audit_sub_category_name', $subCategory);
                }
                $ovRows = $ovBuilder->get()->getResultArray();
                foreach ($ovRows as $ov) {
                    $overrides[$ov['template_question_id']] = $ov;
                }
            }
        }

        $headers = [
            'Category ID',
            'Category Name',
            'Questions',
            'Audit Question Default value',
            'Findings (Assertive Statement)',
            'Risk',
            'Actions/Recommendation',
            'Action Category',
            'UA-UC',
            'Risk Severity',
            'Risk Probability',
            'Color Code',
            'Cost Type',
            'Combined Risk Rating',
            'Note'
        ];

        $siteSuffix = !empty($siteName) ? '_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $siteName) : '';
        $filename = 'HSE_Audit_Questions_' . $templateName . $siteSuffix . '_' . date('Ymd_His') . '.csv';

        if (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $out = fopen('php://output', 'w');
        // UTF-8 BOM for Microsoft Excel compatibility
        fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($out, $headers);

        foreach ($questions as $q) {
            $tqId = $q['template_question_id'] ?? null;
            $ov = ($tqId && isset($overrides[$tqId])) ? $overrides[$tqId] : null;

            $catId = $q['category_id'] ?? ($ov['audit_category_id'] ?? '');
            $catName = !empty($ov['audit_category']) ? $ov['audit_category'] : ($q['category_name'] ?? '');
            $questionText = !empty($ov['audit_question']) ? $ov['audit_question'] : ($q['question_text'] ?? '');
            $defaultVal = !empty($ov['audit_question_default_value']) ? $ov['audit_question_default_value'] : ($q['default_value'] ?? '');

            $source = !empty($ov) ? $ov : $q;
            $capa = [];
            if (!empty($source['capa_json'])) {
                $capa = is_array($source['capa_json']) ? $source['capa_json'] : json_decode($source['capa_json'], true);
            }
            if (empty($capa) && !empty($q['capa_json'])) {
                $capa = is_array($q['capa_json']) ? $q['capa_json'] : json_decode($q['capa_json'], true);
            }

            $findings    = !empty($source['findings']) ? $source['findings'] : ($capa['findings']['no'] ?? ($capa['findings']['text'] ?? ''));
            $risk        = !empty($source['risk']) ? $source['risk'] : ($capa['risk']['no'] ?? ($capa['risk']['text'] ?? ''));
            $actions     = !empty($source['actions_recommendation']) ? $source['actions_recommendation'] : ($capa['actions']['no'] ?? ($capa['actions']['text'] ?? ''));
            $actionCat   = !empty($source['action_category']) ? $source['action_category'] : ($capa['action_category']['no'] ?? ($capa['action_category']['text'] ?? ''));
            $uaUc        = !empty($source['ua_uc']) ? $source['ua_uc'] : ($capa['ua_uc']['no'] ?? ($capa['ua_uc']['text'] ?? ''));
            $severity    = !empty($source['risk_severity']) ? $source['risk_severity'] : ($capa['risk_severity']['no'] ?? ($capa['risk_severity']['text'] ?? ''));
            $probability = !empty($source['risk_probability']) ? $source['risk_probability'] : ($capa['risk_probability']['no'] ?? ($capa['risk_probability']['text'] ?? ''));
            $colorCode   = !empty($source['color_code']) ? $source['color_code'] : ($capa['color_code']['no'] ?? ($capa['color_code']['text'] ?? ''));
            $costType    = !empty($source['cost_type']) ? $source['cost_type'] : ($capa['cost_type']['no'] ?? ($capa['cost_type']['text'] ?? ''));
            $combRisk    = !empty($source['combined_risk_rating']) ? $source['combined_risk_rating'] : ($capa['combined_risk_rating']['no'] ?? ($capa['combined_risk_rating']['text'] ?? ''));
            $note        = !empty($source['note']) ? $source['note'] : ($q['note'] ?? '');

            fputcsv($out, [
                $catId,
                $catName,
                $questionText,
                $defaultVal,
                $findings,
                $risk,
                $actions,
                $actionCat,
                $uaUc,
                $severity,
                $probability,
                $colorCode,
                $costType,
                $combRisk,
                $note
            ]);
        }

        fclose($out);
        exit();
    }

    private function getUserName(): string
    {
        if (PHP_SAPI !== 'cli' && function_exists('session')) {
            try {
                $session = session();
                if ($session && $session->has('login_name')) {
                    return (string)$session->get('login_name');
                }
            } catch (\Throwable $e) {
                // Ignore session initialization errors
            }
        }
        return 'System';
    }
}

