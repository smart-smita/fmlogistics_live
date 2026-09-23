<?php
namespace App\Controllers\Masters;

use App\Controllers\BaseController;
use App\Models\GembaAuditModel;
use Config\Database;

class GembaNcTracker extends BaseController
{
    protected $gembaModel;
    protected $db;
    // protected $api_emailId = 'donotreply@fmlogistic.com'; // Adding for email

    public function __construct()
    {
        helper(['form', 'url', 'designation_acl', 'gemba_acl']);
        $this->gembaModel = new GembaAuditModel();
        $this->db = Database::connect();
    }

    public function index()
    {
        return $this->template_type_filter();
    }

    public function template_type_filter($nc_status = null)
    {
        $req = service('request');
        $data = [];

        // Add nc_status for filtering
        $data['nc_status'] = $nc_status;

        // Fetch initial regions (others via AJAX)
        $db_table = $this->db->table('alert_gemba_audits');
        
        $builderRegion = clone $db_table;
        $data['regions'] = apply_gemba_role_filters($builderRegion, 'alert_gemba_audits')->select('DISTINCT(region)')->where('status !=', 2)->where('region !=', 'NA')->orderBy('region', 'ASC')->get()->getResultArray();
        
        $builderCategory = clone $db_table;
        $data['audit_categories'] = apply_gemba_role_filters($builderCategory, 'alert_gemba_audits')->select('DISTINCT(audit_category)')->where('status !=', 2)->where('audit_category !=', 'NA')->where('audit_category IS NOT NULL')->orderBy('audit_category', 'ASC')->get()->getResultArray();
        
        $builderNcType = clone $db_table;
        $data['nc_types'] = apply_gemba_role_filters($builderNcType, 'alert_gemba_audits')->select('DISTINCT(nc_recommendation)')->where('status !=', 2)->where('nc_recommendation !=', 'NA')->where('nc_recommendation IS NOT NULL')->orderBy('nc_recommendation', 'ASC')->get()->getResultArray();
        
        $builderSiteName = clone $db_table;
        $data['site_names'] = apply_gemba_role_filters($builderSiteName, 'alert_gemba_audits')->select('DISTINCT(site_name)')->where('status !=', 2)->where('site_name !=', '')->orderBy('site_name', 'ASC')->get()->getResultArray();
        
        $builderSiteCat = clone $db_table;
        $data['site_categories'] = apply_gemba_role_filters($builderSiteCat, 'alert_gemba_audits')->select('DISTINCT(site_category)')->where('status !=', 2)->where('site_category !=', '')->orderBy('site_category', 'ASC')->get()->getResultArray();

        // Summary Statistics
        $data['stats'] = $this->getSummaryStats($req, $nc_status);

        // Filters from Request
        $data['filters'] = [
            'region' => $req->getGet('region'),
            'audit_category' => $req->getGet('audit_category'),
            'site_category' => $req->getGet('site_category'),
            'site_name' => $req->getGet('site_name'),
            'point_status' => $req->getGet('point_status'),
            'nc_recommendation' => $req->getGet('nc_recommendation'),
        ];

        $ajaxUrl = base_url('Masters/GembaNcTracker/table_ajax');
        if ($nc_status !== null) {
            $ajaxUrl .= '?nc_status=' . $nc_status;
        }

        $tdata = [
            'title' => 'Gemba NC Tracker',
            'display_contents' => [
                'action' => 'Action',
                'unique_no' => 'Audit No',
                'region' => 'Region',
                'auditor_name' => 'Auditor',
                'audit_category' => 'Audit Category',
                'site_name' => 'Site Name',
                'site_category' => 'Category',
                'nc_recommendation' => 'NC/Rec',
                'observation_point' => 'Observation',
                'color_code' => 'Color',
                'point_status' => 'Status',
                'target_date' => 'Target',
                'closed_date' => 'Closed',
                'ageing_days' => 'Ageing',
                'nc_status' => 'NC Status'
            ],
            'example2' => 'gemba_nc_tracker_table',
            'ajax_url_for_data' => $ajaxUrl
        ];
        $tdata['is_server_side'] = true;

        $data['table'] = view('Layout/table-view', $tdata);

        return view('Master/gemba_nc_tracker', $data);
    }

    private function getSummaryStats($req, $nc_status = null)
    {
        $builder = $this->db->table('alert_gemba_audits');
        $this->applyFilters($builder, $req, $nc_status);

        $results = $builder->select("
            COUNT(*) as total,
            SUM(CASE WHEN nc_recommendation = 'NC' THEN 1 ELSE 0 END) as total_nc,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' THEN 1 ELSE 0 END) as total_rd,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Open' THEN 1 ELSE 0 END) as open_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Closed' THEN 1 ELSE 0 END) as closed_nc,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' AND point_status = 'Open' THEN 1 ELSE 0 END) as open_rd,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' AND point_status = 'Closed' THEN 1 ELSE 0 END) as closed_rd,
            SUM(CASE WHEN point_status = 'Open' AND target_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN color_code = 'Red' THEN 1 ELSE 0 END) as red_count,
            SUM(CASE WHEN color_code = 'Yellow' THEN 1 ELSE 0 END) as yellow_count,
            SUM(CASE WHEN color_code = 'Black' THEN 1 ELSE 0 END) as black_count
        ")->get()->getRowArray();

        // Additional counts for workflow status
        $builderWF = $this->db->table('alert_gemba_audits');
        $this->applyFilters($builderWF, $req, null); // Get overall workflow counts without status filter

        $workflowStats = $builderWF->select("
            SUM(CASE WHEN nc_status = 0 THEN 1 ELSE 0 END) as workflow_open,
            SUM(CASE WHEN nc_status = 1 THEN 1 ELSE 0 END) as workflow_working,
            SUM(CASE WHEN nc_status = 5 THEN 1 ELSE 0 END) as workflow_cluster_review,
            SUM(CASE WHEN nc_status = 2 THEN 1 ELSE 0 END) as workflow_auditor_review,
            SUM(CASE WHEN nc_status = 3 THEN 1 ELSE 0 END) as workflow_closed,
            SUM(CASE WHEN nc_status = 4 THEN 1 ELSE 0 END) as workflow_draft,
            SUM(CASE WHEN nc_status = 6 THEN 1 ELSE 0 END) as workflow_rejected
        ")->get()->getRowArray();

        $results = array_merge($results, $workflowStats);

        // Calculate trends (This Month vs Last Month)
        $builderTM = $this->db->table('alert_gemba_audits');
        $this->applyFilters($builderTM, $req, $nc_status);
        $builderTM->where('MONTH(audit_report_date)', date('m'))->where('YEAR(audit_report_date)', date('Y'));
        $tm = $builderTM->select("
            COUNT(*) as total,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Open' THEN 1 ELSE 0 END) as open_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Closed' THEN 1 ELSE 0 END) as closed_nc,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' THEN 1 ELSE 0 END) as total_rd,
            SUM(CASE WHEN point_status = 'Open' AND target_date < CURDATE() THEN 1 ELSE 0 END) as overdue
        ")->get()->getRowArray();

        $builderLM = $this->db->table('alert_gemba_audits');
        $this->applyFilters($builderLM, $req, $nc_status);
        $lastMonth = date('m', strtotime('first day of last month'));
        $lastYear = date('Y', strtotime('first day of last month'));
        $builderLM->where('MONTH(audit_report_date)', $lastMonth)->where('YEAR(audit_report_date)', $lastYear);
        $lm = $builderLM->select("
            COUNT(*) as total,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Open' THEN 1 ELSE 0 END) as open_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Closed' THEN 1 ELSE 0 END) as closed_nc,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' THEN 1 ELSE 0 END) as total_rd,
            SUM(CASE WHEN point_status = 'Open' AND target_date < CURDATE() THEN 1 ELSE 0 END) as overdue
        ")->get()->getRowArray();

        $results['trends'] = [
            'total' => $this->calcTrend($tm['total'] ?? 0, $lm['total'] ?? 0),
            'open_nc' => $this->calcTrend($tm['open_nc'] ?? 0, $lm['open_nc'] ?? 0),
            'closed_nc' => $this->calcTrend($tm['closed_nc'] ?? 0, $lm['closed_nc'] ?? 0),
            'total_rd' => $this->calcTrend($tm['total_rd'] ?? 0, $lm['total_rd'] ?? 0),
            'overdue' => $this->calcTrend($tm['overdue'] ?? 0, $lm['overdue'] ?? 0)
        ];

        return $results;
    }

    private function calcTrend($tm, $lm)
    {
        $tm = (int) $tm;
        $lm = (int) $lm;
        if ($lm == 0)
            return ['val' => $tm > 0 ? 100 : 0, 'dir' => $tm > 0 ? 'up' : 'flat'];
        $diff = (($tm - $lm) / $lm) * 100;
        return [
            'val' => round(abs($diff), 1),
            'dir' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'flat')
        ];
    }

    private function applyFilters($builder, $req, $nc_status = null)
    {
        apply_gemba_role_filters($builder, 'alert_gemba_audits');
        $fields = ['region', 'audit_category', 'site_category', 'site_name', 'point_status', 'nc_recommendation'];
        foreach ($fields as $field) {
            $val = $req->getVar($field);
            if (!empty($val)) {
                if (is_array($val)) {
                    $builder->whereIn($field, $val);
                } else {
                    $builder->where($field, $val);
                }
            }
        }

        $statusParam = $req->getGet('nc_status');
        if ($statusParam !== null && $statusParam !== '') {
            $nc_status = $statusParam;
        }

        if ($nc_status !== null) {
            $builder->where('nc_status', $nc_status);
        }

        $builder->where('status !=', 2);
    }

    public function stats_ajax()
    {
        $req = service('request');
        $nc_status = $req->getGet('nc_status');
        $stats = $this->getSummaryStats($req, $nc_status);
        return $this->response->setJSON($stats);
    }

    public function table_ajax()
    {
        $req = service('request');
        $builder = $this->db->table('alert_gemba_audits');

        $nc_status = $req->getGet('nc_status');
        $this->applyFilters($builder, $req, $nc_status);

        $totalBuilder = clone $builder;
        $recordsFiltered = $totalBuilder->countAllResults(false);

        $totalRecords = apply_gemba_role_filters($this->db->table('alert_gemba_audits')->where('status !=', 2), 'alert_gemba_audits')->countAllResults();

        $start = (int) ($req->getGet('start') ?? 0);
        $length = (int) ($req->getGet('length') ?? 10);
        $order = $req->getGet('order');

        if ($order && isset($order[0])) {
            $colIdx = $order[0]['column'];
            $dir = $order[0]['dir'];
            $columns = $req->getGet('columns');
            $colName = $columns[$colIdx]['data'] ?? 'gemba_sr_no';
            if ($colName == 'action') {
                $colName = 'gemba_sr_no';
            }
            $builder->orderBy($colName, $dir);
        } else {
            $builder->orderBy('gemba_sr_no', 'DESC');
        }

        if ($length != -1) {
            $builder->limit($length, $start);
        }

        $searchValue = $req->getGet('search')['value'] ?? '';
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('unique_no', $searchValue)
                ->orLike('region', $searchValue)
                ->orLike('auditor_name', $searchValue)
                ->orLike('audit_category', $searchValue)
                ->orLike('site_name', $searchValue)
                ->orLike('site_category', $searchValue)
                ->orLike('nc_recommendation', $searchValue)
                ->orLike('observation_point', $searchValue)
                ->orLike('color_code', $searchValue)
                ->orLike('point_status', $searchValue)
                ->orLike('target_date', $searchValue)
                ->orLike('closed_date', $searchValue)
                ->orLike('ageing_days', $searchValue)
                ->groupEnd();
        }

        $data = $builder->get()->getResultArray();

        $canClose = gemba_can_write();

        foreach ($data as &$row) {
            $row['point_status_raw'] = trim($row['point_status'] ?? '');
            $row['point_status'] = $this->getStatusBadge(!empty($row['point_status_raw']) ? $row['point_status_raw'] : 'N/A');
            $row['color_code'] = $this->getColorBadge($row['color_code']);

            // Formatting Workflow Status
            $wfStatusMap = [
                0 => '<span class="badge bg-danger">Open</span>',
                1 => '<span class="badge bg-info">Working</span>',
                2 => '<span class="badge bg-warning text-dark">Auditor Review</span>',
                3 => '<span class="badge bg-success">Closed</span>',
                4 => '<span class="badge bg-secondary">Draft</span>',
                5 => '<span class="badge bg-primary">Cluster Review</span>',
                6 => '<span class="badge bg-dark">Rejected</span>',
            ];
            $row['nc_status_val'] = $row['nc_status'];
            $row['nc_status'] = $wfStatusMap[(int) $row['nc_status']] ?? '<span class="badge bg-secondary">Unknown</span>';

            $action = '';
            $isHseMapped = false;

            if (strtolower(trim($row['audit_category'])) == 'hse audit' && !empty($row['hse_audit_id'])) {
                $hseAudit = $this->db->table('alert_hse_audit_master')
                    ->where('hse_audit_id', $row['hse_audit_id'])
                    ->where('status !=', 2)
                    ->get()
                    ->getRowArray();
                if ($hseAudit) {
                    $isHseMapped = true;
                }
            }

            if ($isHseMapped) {
                $action .= '<a href="' . base_url('Masters/Hse_nc_tracker') . '" target="_blank" class="badge bg-info text-decoration-none">Managed In HSE</a>';
            } else {
                // Workflow buttons
                $wfStatus = (int) $row['nc_status_val'];

                $userName = trim((string) getUserName());
                $isSuperAdmin = isSuperAdmin();
                $isAuditor = isAuditor();

                $isAssignedCM = false;
                if (isClusterManager()) {
                    $assignedClusters = getClusterManagerAssignedClusterHSE(); // Or a gemba specific one if available
                    // Assuming cluster_manager_spoc stores the cluster or user name
                    if (!empty($row['cluster_manager_spoc']) && strtolower(trim($row['cluster_manager_spoc'])) === strtolower(trim($userName))) {
                        $isAssignedCM = true;
                    } elseif (in_array($row['site_name'], $assignedClusters) || in_array($row['region'], $assignedClusters)) {
                        $isAssignedCM = true; // Simplified for now, adapt if needed
                    } else {
                        // Fallback check
                        $isAssignedCM = true; // In Gemba they might just view by region/site
                    }
                }

                $isAssignedAM = false;
                if (isAccountManager()) {
                    $isAssignedAM = true;
                }

                // Action buttons based on workflow
                $action .= '<div class="btn-group">';

                if ($wfStatus === 0 && ($isSuperAdmin || $isAssignedAM || $isAssignedCM || $isAuditor)) {
                    $action .= '<button class="btn btn-sm btn-info w-action-btn" data-id="' . $row['gemba_sr_no'] . '" data-action="working">Start Working</button>';
                }

                if ($wfStatus === 1 && ($isSuperAdmin || $isAssignedAM || $isAssignedCM || $isAuditor)) {
                    $action .= '<button class="btn btn-sm btn-primary w-action-btn" data-id="' . $row['gemba_sr_no'] . '" data-action="review">Submit Review</button>';
                    $action .= '<button class="btn btn-sm btn-secondary w-form-btn" data-id="' . $row['gemba_sr_no'] . '">Upload Details</button>';
                }

                if ($wfStatus === 5 && ($isSuperAdmin || $isAssignedAM || $isAssignedCM)) {
                    $action .= '<button class="btn btn-sm btn-warning w-action-btn" data-id="' . $row['gemba_sr_no'] . '" data-action="auditor">Send to Auditor</button>';
                }

                if ($wfStatus === 2 && ($isSuperAdmin || $isAuditor)) {
                    $action .= '<button class="btn btn-sm btn-success w-action-btn" data-id="' . $row['gemba_sr_no'] . '" data-action="closed">Close</button>';
                    $action .= '<button class="btn btn-sm btn-danger w-action-btn" data-id="' . $row['gemba_sr_no'] . '" data-action="0">Reject</button>';
                }

                if ($wfStatus === 6 && ($isSuperAdmin || $isAssignedAM || $isAssignedCM || $isAuditor)) {
                    $action .= '<button class="btn btn-sm btn-info w-action-btn" data-id="' . $row['gemba_sr_no'] . '" data-action="working">Rework</button>';
                }

                $action .= '</div>';
            }
            $row['action'] = $action;
        }

        return $this->response->setJSON([
            'draw' => intval($req->getGet('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function update_nc_status()
    {
        helper('designation_acl');

        $db = db_connect();
        $req = service('request');

        $id = $req->getVar('id');
        $action = $req->getVar('status');
        $closeDate = $req->getVar('close_date');
        $userId = $_SESSION['user_id'] ?? null;
        $userName = trim((string) getUserName());
        $now = date('Y-m-d H:i:s');

        if (!empty($userId) && is_numeric($userId) && (int) $userId > 0) {
            $actionBy = (int) $userId;
        } elseif (isSuperAdmin()) {
            $actionBy = 0;
        } else {
            $actionBy = null;
        }

        $current = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();

        if (!$current) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Record not found']);
        }

        $currentStatus = (int) $current['nc_status'];

        if (isHigherAuthority()) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Read only access']);
        }

        $isAssignedAM = isAccountManager();
        $isAssignedCM = isClusterManager();
        $isSuperAdmin = isSuperAdmin();
        $isAuditor = isAuditor();

        $update = [];

        if ($action === '0') {
            if ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM) {
                $update['nc_status'] = 0;
                $update['nc_rejected_by'] = $actionBy;
                $update['nc_rejected_date'] = $now;
            }
        } else {
            switch ($currentStatus) {
                case 0:
                case 6:
                    if ($action === 'working' && ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM)) {
                        $update['nc_status'] = 1;
                        $update['nc_worked_by'] = $actionBy;
                    }
                    break;
                case 1:
                    if ($action === 'review') {
                        if ($isSuperAdmin || $isAssignedCM || $isAssignedAM) {
                            $update['nc_status'] = 5;
                            $update['nc_cluster_reviewed_by'] = $actionBy;
                            $update['nc_cluster_reviewed_date'] = $now;
                        } elseif ($isAuditor) {
                            $update['nc_status'] = 2;
                            $update['nc_auditor_reviewed_by'] = $actionBy;
                            $update['nc_auditor_reviewed_date'] = $now;
                        }
                    }
                    break;
                case 5:
                    if ($action === 'auditor' && ($isSuperAdmin || $isAssignedCM || $isAssignedAM)) {
                        $update['nc_status'] = 2;
                        $update['nc_auditor_reviewed_by'] = $actionBy;
                        $update['nc_auditor_reviewed_date'] = $now;
                    }
                    break;
                case 2:
                    if ($action === 'closed' && ($isSuperAdmin || $isAuditor)) {
                        $update['nc_status'] = 3;
                        $update['nc_closed_by'] = $actionBy;
                        $update['nc_closed_date'] = !empty($closeDate) ? $closeDate : date('Y-m-d');
                        $update['point_status'] = 'Closed';
                        $update['closed_date'] = !empty($closeDate) ? $closeDate : date('Y-m-d');
                    }
                    break;
                case 3:
                    return $this->response->setJSON(['status' => 0, 'message' => 'Already Closed']);
            }
        }

        if (empty($update)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Unauthorized action']);
        }

        $result = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->update($update);

        if ($result) {
            $db->table('alert_gemba_nc_audit_trail')->insert([
                'gemba_audit_id' => $id,
                'action_by' => $actionBy,
                'action_date' => $now,
                'status_from' => $currentStatus,
                'status_to' => $update['nc_status'] ?? 0,
                'remarks' => 'Status changed to ' . ($update['nc_status'] ?? 0)
            ]);

            if (isset($update['nc_status']) && $currentStatus != $update['nc_status']) {
                $this->send_nc_status_email($id, $currentStatus, $update['nc_status']);
            }
        }

        return $this->response->setJSON([
            'status' => $result ? 1 : 0,
            'message' => $result ? 'Status Updated' : 'Update failed'
        ]);
    }

    public function get_form_data($id)
    {
        $response = ['status' => "0", 'message' => "Details not found"];
        if (isset($id)) {
            $query = $this->db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get();
            if ($query->getNumRows() > 0) {
                $data = $query->getRowArray();
                $response['data'] = $data;
                $response['data']['before_photo_url'] = ''; // Assuming no before photo handling here currently unless added
                if (!empty($data['nc_after_photo'])) {
                    $response['data']['after_photo_url'] = base_url($data['nc_after_photo']);
                }
                $response['status'] = "1";
                $response['message'] = "Details found";
            }
        }
        return $this->response->setJSON($response);
    }

    public function save_details($id = null)
    {
        $request = service('request');
        $id = $id ?? $request->getVar('gemba_sr_no');
        $ncRemark = $request->getVar('nc_remark');
        $actionBy = $_SESSION['user_id'] ?? 0;

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid ID']);
        }

        $update = ['nc_remark' => $ncRemark];

        $file = $request->getFile('nc_after_photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/gemba/', $newName);
            $update['nc_after_photo'] = 'uploads/gemba/' . $newName;
        }

        $result = $this->db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->update($update);

        if ($result) {
            $this->db->table('alert_gemba_nc_audit_trail')->insert([
                'gemba_audit_id' => $id,
                'action_by' => $actionBy,
                'action_date' => date('Y-m-d H:i:s'),
                'remarks' => 'Details updated'
            ]);
        }

        return $this->response->setJSON(['status' => $result ? 'success' : 'error', 'message' => $result ? 'Saved successfully' : 'Save failed']);
    }

    public function send_nc_status_email($ncId, $oldStatus, $newStatus)
    {
        // Simple mock for now based on HSE email
        $detail = $this->db->table('alert_gemba_audits')->where('gemba_sr_no', $ncId)->get()->getRowArray();
        if (!$detail)
            return false;

        $toEmail = '';
        $subject = '';
        $template = 'Emails/nc_cluster_review'; // placeholder

        // Implement actual logic similar to HSE
        return true;
    }

    public function close_nc()
    {
        gemba_require_write_access(true);

        $req = service('request');
        $id = $req->getVar('gemba_sr_no');
        $closeDate = $req->getVar('closed_date') ?: date('Y-m-d');
        $remarks = $req->getVar('closure_remarks') ?: 'Closed via Tracker';
        $status = $req->getVar('closure_status') ?: 'Closed';

        if (!$id)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid ID']);

        $record = $this->db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();
        if (!$record)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Record not found']);

        $closedTs = strtotime($closeDate);
        $updateData = [
            'point_status' => 'Closed',
            'point_category' => 'Closed Category',
            'closure_status' => $status,
            'closed_date' => $closeDate,
            'qhse_remarks' => $remarks,
            'nc_status' => 3,
            'nc_closed_date' => $closeDate,
            'weeknum_closed' => date('W', $closedTs),
            'month_closed' => date('Y-m-01', $closedTs),
            'year_month_closed' => date('Y-m', $closedTs),
            'weeknum_yearmonth_closed' => date('Y-m', $closedTs) . '-W' . date('W', $closedTs),
            'updated_by' => session()->get('user_name') ?? 'System'
        ];

        $this->db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->update($updateData);

        return $this->response->setJSON(['status' => 'success', 'message' => 'NC closed successfully']);
    }

    private function getStatusBadge($status)
    {
        $badges = [
            'Open' => '<span class="badge bg-danger">Open</span>',
            'Closed' => '<span class="badge bg-success">Closed</span>',
            'Excluded' => '<span class="badge bg-dark">Excluded</span>',
            'Hold-review with Client' => '<span class="badge bg-warning">Hold-review with Client</span>'
        ];
        return $badges[$status] ?? '<span class="badge bg-secondary">' . $status . '</span>';
    }

    private function getColorBadge($color)
    {
        $colors = [
            'Red' => '<span class="badge bg-danger">Red</span>',
            'Yellow' => '<span class="badge bg-warning text-dark">Yellow</span>',
            'Black' => '<span class="badge bg-dark">Black</span>',
            'Green' => '<span class="badge bg-success">Green</span>'
        ];
        return $colors[$color] ?? $color;
    }

    public function export_excel()
    {
        return $this->export_csv();
    }

    public function export_csv()
    {
        $req = service('request');
        $builder = $this->db->table('alert_gemba_audits');
        $nc_status = $req->getGet('nc_status');
        $this->applyFilters($builder, $req, $nc_status);
        $data = $builder->get()->getResultArray();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Gemba_NC_Tracker_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');

        $output = fopen('php://output', 'w');
        if (!$output) {
            exit('Unable to open output stream');
        }
        // Add BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $columns = [
            'gemba_sr_no' => 'Gemba Sr No', 'unique_no' => 'Unique No', 'region' => 'Region', 'auditor_name' => 'Auditor Name', 'audit_category' => 'Audit Category', 'audit_type' => 'Audit Type', 'audit_doc_type' => 'Audit Doc Type', 'site_name' => 'Site Name', 'site_type_1' => 'Site Type 1', 'site_type_2' => 'Site Type 2', 'site_category' => 'Site Category', 'audit_mail_received_date' => 'Audit Mail Received Date', 'audit_report_date' => 'Audit Report Date', 'weeknum_raised' => 'Weeknum Raised', 'month_raised' => 'Month Raised', 'year_month_raised' => 'Year Month Raised', 'year_raised' => 'Year Raised', 'checklist_category' => 'Checklist Category', 'nc_recommendation' => 'NC Recommendation', 'qhse_remarks' => 'QHSE Remarks', 'observation_point' => 'Observation Point', 'risks_details' => 'Risks Details', 'action_recommendation' => 'Action Recommendation', 'specifications' => 'Specifications', 'ua_uc_type' => 'UA/UC Type', 'risk_severity' => 'Risk Severity', 'risk_probability' => 'Risk Probability', 'color_code' => 'Color Code', 'cost_type' => 'Cost Type', 'remarks_wm' => 'Remarks WM', 'whose_scope' => 'Whose Scope', 'remarks_corporate' => 'Remarks Corporate', 'ageing_days' => 'Ageing Days', 'age_bracket' => 'Age Bracket', 'target_date' => 'Target Date', 'closed_date' => 'Closed Date', 'point_status' => 'Point Status', 'point_category' => 'Point Category', 'closure_status' => 'Closure Status', 'weeknum_closed' => 'Weeknum Closed', 'month_closed' => 'Month Closed', 'year_month_closed' => 'Year Month Closed', 'risk_severity_rating' => 'Risk Severity Rating', 'risk_probability_rating' => 'Risk Probability Rating', 'combined_risk_rating' => 'Combined Risk Rating', 'times_repeated' => 'Times Repeated', 'combined_risk_freq' => 'Combined Risk Freq', 'final_rating' => 'Final Rating', 'followup_by' => 'Followup By', 'weeknum_yearmonth_raised' => 'Weeknum Yearmonth Raised', 'weeknum_yearmonth_closed' => 'Weeknum Yearmonth Closed', 'cluster_manager_spoc' => 'Cluster Manager SPOC', 'account_manager' => 'Account Manager', 'created_by' => 'Created By', 'updated_by' => 'Updated By', 'status' => 'Status', 'default_date' => 'Default Date', 'update_date' => 'Update Date', 'hse_audit_id' => 'HSE Audit ID', 'hse_detail_id' => 'HSE Detail ID', 'source_module' => 'Source Module', 'nc_status' => 'NC Status', 'nc_remark' => 'NC Remark', 'nc_worked_by' => 'NC Worked By', 'nc_after_photo' => 'NC After Photo', 'nc_cluster_reviewed_by' => 'NC Cluster Reviewed By', 'nc_cluster_reviewed_date' => 'NC Cluster Reviewed Date', 'nc_auditor_reviewed_by' => 'NC Auditor Reviewed By', 'nc_auditor_reviewed_date' => 'NC Auditor Reviewed Date', 'nc_closed_by' => 'NC Closed By', 'nc_closed_date' => 'NC Closed Date', 'nc_rejected_by' => 'NC Rejected By', 'nc_rejected_date' => 'NC Rejected Date', 'rejection_reason' => 'Rejection Reason', 'action_category' => 'Action Category'
        ];

        fputcsv($output, array_values($columns));

        $statusMap = [0 => 'Open', 1 => 'Working', 2 => 'Auditor Review', 3 => 'Closed', 4 => 'Draft', 5 => 'Cluster Review', 6 => 'Rejected'];

        foreach ($data as $row) {
            $csvRow = [];
            foreach ($columns as $key => $label) {
                $val = $row[$key] ?? '';
                if ($key === 'nc_status') {
                    $val = $statusMap[$val] ?? $val;
                }
                $csvRow[] = (string)$val;
            }
            fputcsv($output, $csvRow);
        }
        fclose($output);
        exit;
    }
}
