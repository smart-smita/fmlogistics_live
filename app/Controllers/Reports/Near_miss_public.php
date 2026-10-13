<?php

namespace App\Controllers\Reports;

use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;

class Near_miss_public extends BaseController
{
    protected $BaseModel;

    public function __construct()
    {
        helper("form");
        $db = null;
        $db['table']         = 'alert_near_miss';
        $db['allowedFields'] = ['user_id', 'near_miss_category_text', 'self_other', 'other_engineer_id', 'other_engineer_name',
            'project_name', 'job_no', 'location', 'gps_location', 'manual_location', 'near_miss_date', 'near_miss_time', 'incident_person_name',
            'incident_person_designation', 'incident_person_age', 'incident_person_company', 'incident_person_sift', 'mode_of_work',
            'nature_of_damage', 'body_part_affect', 'body_part_affect_details', 'involved_equipment_name', 'description_of_occurred',
            'what_could_have_happened', 'causes_of_incident', 'immediate_action', 'future_occurrence', 'images', 'after_images', 'total_hours', 'total_cost',
            'status', 'near_miss_priority', 'priority_comment', 'rejection_comment', 'update_date', 'source',
            'public_reporter_name', 'public_reporter_email', 'public_reporter_mobile', 'public_reporter_emp_code'];
        $db['primaryKey'] = "near_miss_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }

    public function index()
    {
        echo "<div style='display: flex; justify-content: center; align-items: center; height: 100vh; font-family: sans-serif; background-color: #f8f9fa;'>
                <div style='text-align: center;'>
                    <h1 style='color: #343a40; font-size: 3rem; margin-bottom: 10px;'>Coming Soon</h1>
                    <p style='color: #6c757d; font-size: 1.2rem;'>We are working hard to bring this feature to you.</p>
                </div>
              </div>";
        return;
        
        $request = service('request');
        $db = db_connect();

        if ($request->getMethod() === 'post') {
            $postData = $request->getPost();

            // Prevent unauthorized injection of fields
            unset($postData['status'], $postData['user_id'], $postData['source'], $postData['near_miss_id'], $postData['after_images']);

            $validation = \Config\Services::validation();
            $validation->setRules([
                'public_reporter_name' => 'required',
                'near_miss_category_text' => 'required',
                'project_name' => 'required',
                'job_no' => 'required',
                'location' => 'required',
                'near_miss_date' => 'required',
                'near_miss_time' => 'required',
                'mode_of_work' => 'required',
                'description_of_occurred' => 'required'
            ]);

            if (!$validation->withRequest($request)->run()) {
                return $this->response->setJSON(['status' => '0', 'message' => 'Please fill all mandatory fields.']);
            }

            // Handle Attachments (Before images)
            $imagePaths = [];
            if ($files = $request->getFiles()) {
                if (isset($files['before_images'])) {
                    foreach ($files['before_images'] as $file) {
                        if ($file->isValid() && !$file->hasMoved()) {
                            // MIME validation
                            $mime = $file->getMimeType();
                            $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation'];
                            
                            if (in_array($mime, $allowedMimes)) {
                                $newName = $file->getRandomName();
                                $file->move(FCPATH . 'assets/media/near_miss/', $newName);
                                $imagePaths[] = 'assets/media/near_miss/' . $newName;
                            }
                        }
                    }
                }
            }

            $postData['images'] = !empty($imagePaths) ? implode(',', $imagePaths) : '';
            $postData['status'] = '4'; // REQUESTED
            $postData['user_id'] = '0'; // Public User
            $postData['source'] = 'public_qr';

            $insertId = $this->BaseModel->insert($postData);
            if ($insertId) {
                helper('notification');
                sendNearMissNotification($insertId, true);
                return $this->response->setJSON(['status' => '1', 'message' => 'Near Miss report submitted successfully. Reference No: NM-' . $insertId]);
            } else {
                return $this->response->setJSON(['status' => '0', 'message' => 'Failed to submit the report.']);
            }
        }

        // GET request - render the public form
        $data['title'] = "Submit Near Miss (Public)";
        $data['nature_incident'] = $db->table("alert_nature_incident")->where(['status' => 1])->get()->getResultArray();
        $data['body_part'] = $db->table("alert_body_part")->where(['status' => 1])->get()->getResultArray();
        $data['locations'] = $db->table("alert_location_master")->orderBy('location_name', 'ASC')->get()->getResultArray();
        $data['task_activity'] = $db->table("alert_lmra_categorys")->where(['status' => 1])->get()->getResultArray();

        return view("Reports/public_nearmiss_form", $data);
    }
}
