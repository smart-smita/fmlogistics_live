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
        helper(['form', 'url', 'designation_acl', 'gemba_acl', 'hse_acl']);
        $this->gembaModel = new GembaAuditModel();
        $this->db = Database::connect();
        $this->ensure_stage_columns();
    }

    private function ensure_stage_columns()
    {
        try {
            $db = db_connect();
            $tables = ['alert_gemba_audits', 'alert_hse_audit_details'];
            foreach ($tables as $tbl) {
                if ($db->tableExists($tbl)) {
                    $fields = $db->getFieldNames($tbl);
                    $newCols = [];
                    if (!in_array('working_remarks', $fields)) {
                        $newCols[] = "ADD COLUMN working_remarks TEXT NULL";
                    }
                    if (!in_array('working_uploaded_file', $fields)) {
                        $newCols[] = "ADD COLUMN working_uploaded_file VARCHAR(500) NULL";
                    }
                    if (!in_array('working_date', $fields)) {
                        $newCols[] = "ADD COLUMN working_date DATETIME NULL";
                    }
                    if (!in_array('closed_remarks', $fields)) {
                        $newCols[] = "ADD COLUMN closed_remarks TEXT NULL";
                    }
                    if (!in_array('closed_uploaded_file', $fields)) {
                        $newCols[] = "ADD COLUMN closed_uploaded_file VARCHAR(500) NULL";
                    }
                    if (!empty($newCols)) {
                        $sql = "ALTER TABLE " . $tbl . " " . implode(", ", $newCols);
                        $db->query($sql);
                    }
                }
            }
        } catch (\Exception $e) {
            // log or ignore
        }
    }

    public function index()
    {
        return $this->template_type_filter();
    }

    public function read_and_redirect($id)
    {
        $this->db->table('alert_gemba_notifications')
            ->where('id', $id)
            ->update(['is_read' => 1]);

        return redirect()->to(base_url('Masters/GembaNcTracker/template_type_filter/pending'));
    }

    public function template_type_filter($nc_status = null)
    {
        $req = service('request');
        $data = [];

        // Add nc_status for filtering
        $data['nc_status'] = $nc_status;

        // Fetch Regions from alert_gemba_sites
        $builder = $this->db->table('alert_gemba_sites')->select('DISTINCT(region)')->where('status', 1)->where('region !=', '')->where('region IS NOT NULL')->orderBy('region', 'ASC');
        apply_gemba_role_filters($builder, 'alert_gemba_sites');
        $data['regions'] = $builder->get()->getResultArray();

        // Fetch Allowed Sites to determine Clusters
        $siteBuilder = $this->db->table('alert_gemba_sites')->select('DISTINCT(site_name)')->where('status', 1)->where('site_name !=', '');
        apply_gemba_role_filters($siteBuilder, 'alert_gemba_sites');
        $allowedSitesArray = $siteBuilder->get()->getResultArray();
        $allowedSites = array_values(array_column($allowedSitesArray, 'site_name'));

        $data['clusters'] = [];
        if (!empty($allowedSites)) {
             $c1 = $this->db->table("alert_location_master")->select("cluster_name AS cluster")->whereIn("location_name", $allowedSites)->whereIn("status", [1, '1'])->where("cluster_name IS NOT NULL")->where("TRIM(cluster_name) != ''")->get()->getResultArray();
             $c2 = $this->db->table("alert_hse_client_master")->select("cluster")->whereIn("client_name", $allowedSites)->where("cluster IS NOT NULL")->where("TRIM(cluster) != ''")->get()->getResultArray();
             $mergedC = array_filter(array_unique(array_merge(
                 array_column($c1, 'cluster'),
                 array_column($c2, 'cluster')
             )), function($val) { return trim((string)$val) !== ''; });
             natcasesort($mergedC);
             $data['clusters'] = array_map(function($c) { return ['cluster' => $c]; }, array_values($mergedC));
        }

        $builder = $this->db->table('alert_gemba_audits')->select('DISTINCT(audit_category)')->where('status', 1)->where('audit_category !=', 'NA')->where('audit_category IS NOT NULL')->orderBy('audit_category', 'ASC');
        $this->applyRoleBasedACL($builder);
        $data['audit_categories'] = $builder->get()->getResultArray();

        $builder = $this->db->table('alert_gemba_audits')->select('DISTINCT(nc_recommendation)')->where('status', 1)->where('nc_recommendation !=', 'NA')->where('nc_recommendation IS NOT NULL')->orderBy('nc_recommendation', 'ASC');
        $this->applyRoleBasedACL($builder);
        $data['nc_types'] = $builder->get()->getResultArray();

        $regionFilters = $req->getGet('region') ?? [];
        $clusterFilters = $req->getGet('cluster') ?? [];
        $siteCategoryFilters = $req->getGet('site_category') ?? [];
        
        // Remove 'selectAll' from filters
        if (!empty($regionFilters) && is_array($regionFilters)) {
            $regionFilters = array_values(array_filter($regionFilters, function($v) { return $v !== 'selectAll' && trim((string)$v) !== ''; }));
        }
        if (!empty($clusterFilters) && is_array($clusterFilters)) {
            $clusterFilters = array_values(array_filter($clusterFilters, function($v) { return $v !== 'selectAll' && trim((string)$v) !== ''; }));
        }
        if (!empty($siteCategoryFilters) && is_array($siteCategoryFilters)) {
            $siteCategoryFilters = array_values(array_filter($siteCategoryFilters, function($v) { return $v !== 'selectAll' && trim((string)$v) !== ''; }));
        }

        // Get sites matching cluster filter
        $clusterSiteFilters = [];
        if (!empty($clusterFilters)) {
            $locSites = $this->db->table('alert_location_master')->select('location_name')->whereIn('cluster_name', $clusterFilters)->get()->getResultArray();
            $hseSites = $this->db->table('alert_hse_client_master')->select('client_name')->whereIn('cluster', $clusterFilters)->get()->getResultArray();
            $clusterSiteFilters = array_values(array_unique(array_merge(array_column($locSites, 'location_name'), array_column($hseSites, 'client_name'))));
        }

        // Fetch Site Categories from alert_gemba_sites
        $builder = $this->db->table('alert_gemba_sites')->select('DISTINCT(site_category)')->where('status', 1)->where('site_category !=', '')->orderBy('site_category', 'ASC');
        apply_gemba_role_filters($builder, 'alert_gemba_sites');
        if (!empty($regionFilters)) {
            $builder->whereIn('region', $regionFilters);
        }
        if (!empty($clusterFilters)) {
            if (!empty($clusterSiteFilters)) {
                $builder->whereIn('site_name', $clusterSiteFilters);
            } else {
                $builder->where('1=0');
            }
        }
        $data['site_categories'] = $builder->get()->getResultArray();

        // Fetch Site Names from alert_gemba_sites
        $builder = $this->db->table('alert_gemba_sites')->select('DISTINCT(site_name)')->where('status', 1)->where('site_name !=', '')->orderBy('site_name', 'ASC');
        apply_gemba_role_filters($builder, 'alert_gemba_sites');
        if (!empty($regionFilters)) {
            $builder->whereIn('region', $regionFilters);
        }
        if (!empty($clusterFilters)) {
            if (!empty($clusterSiteFilters)) {
                $builder->whereIn('site_name', $clusterSiteFilters);
            } else {
                $builder->where('1=0');
            }
        }
        if (!empty($siteCategoryFilters)) {
            $builder->whereIn('site_category', $siteCategoryFilters);
        }
        $data['site_names'] = $builder->get()->getResultArray();

        // Summary Statistics
        $data['stats'] = $this->getSummaryStats($req, $nc_status);

        // Filters from Request
        $data['filters'] = [
            'region' => $req->getGet('region'),
            'cluster' => $req->getGet('cluster'),
            'audit_category' => $req->getGet('audit_category'),
            'site_category' => $req->getGet('site_category'),
            'site_name' => $req->getGet('site_name'),
            'point_status' => $req->getGet('point_status'),
            'nc_recommendation' => $req->getGet('nc_recommendation'),
        ];

        $ajaxUrl = base_url('Masters/GembaNcTracker/table_ajax');
        $queryParams = $req->getGet();
        if ($nc_status !== null) {
            $queryParams['nc_status'] = $nc_status;
        }
        if (!empty($queryParams)) {
            $ajaxUrl .= '?' . http_build_query($queryParams);
        }

        $isSuperAdmin = isSuperAdmin();
        $isAdminFlag = (session()->get('admin_flag') ?? 0) == 1;
        $isAuditorRole = isAuditor();
        $canBulkAction = ($isSuperAdmin || $isAdminFlag || $isAuditorRole);

        $display_contents = [];
        if ($canBulkAction) {
            $display_contents['bulk_select'] = '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input selectAllCheckbox" type="checkbox" /></div>';
        }
        $display_contents = array_merge($display_contents, [
            'action' => 'Action',
            'unique_no' => 'Unique No',
            'region' => 'Region',
            'auditor_name' => 'Auditor',
            'audit_category' => 'Audit Category',
            'audit_type' => 'Audit Type',
            'cluster_manager_spoc' => 'Cluster Manager',
            'account_manager' => 'Account Manager',
            'site_name' => 'Site Name',
            'site_category' => 'Category',
            'nc_recommendation' => 'NC/Rec',
            'observation_point' => 'Observation',
            'action_recommendation' => 'Recommendation Action',
            'color_code' => 'Color',
            'point_status' => 'Status',
            'target_date' => 'Target',
            'closed_date' => 'Closed',
            'ageing_days' => 'Ageing',
            'nc_status' => 'NC Status',
            'working_remarks' => 'Working Remarks',
            'working_uploaded_file' => 'Working File',
            'nc_worked_by' => 'Worked By',
            'working_date' => 'Worked Date',
            'closed_remarks' => 'Closing Remarks',
            'closed_uploaded_file' => 'Closing File',
            'nc_closed_by' => 'Closed By',
            'nc_closed_date' => 'NC Closed Date',
            'nc_cluster_reviewed_by' => 'Cluster Reviewed By',
            'nc_cluster_reviewed_date' => 'Cluster Reviewed Date',
            'nc_auditor_reviewed_by' => 'Auditor Reviewed By',
            'nc_auditor_reviewed_date' => 'Auditor Reviewed Date',
            'nc_rejected_by' => 'Rejected By',
            'nc_rejected_date' => 'Rejected Date',
            'rejection_reason' => 'Reject Reason'
        ]);

        $columnDefsArr = [];
        if ($canBulkAction) {
            $columnDefsArr[] = ['orderable' => false, 'targets' => [0]];
        }
        $columnDefsArr[] = [
            'targets' => 'long-text-column',
            'className' => 'long-text-column'
        ];
        $columnDefs = json_encode($columnDefsArr);

        $tdata = [
            'title' => 'Gemba HSE NC Tracker',
            'display_contents' => $display_contents,
            'example2' => 'gemba_nc_tracker_table',
            'ajax_url_for_data' => $ajaxUrl,
            'export_button' => '<button type="button" class="btn btn-sm btn-primary ms-2 d-none" id="bulkCloseBtn" disabled onclick="openBulkCloseModal()">Close Selected NCs</button>
                                <button type="button" class="btn btn-sm btn-success ms-2 d-none" id="bulkWorkingBtn" disabled onclick="submitBulkWorking()">Convert to Working</button>
                                <button type="button" class="btn btn-sm btn-danger ms-2 d-none" id="bulkRejectBtn" disabled onclick="submitBulkReject()">Reject Selected</button>',
            'column_defs' => $columnDefs
        ];
        $tdata['is_server_side'] = true;
        $tdata['enable_export'] = false;

        $data['table'] = view('Layout/table-view', $tdata);

        return view('Master/gemba_nc_tracker', $data);
    }

    private function applyBaseFilters($builder, $req = null)
    {
        $this->applyRoleBasedACL($builder);
        
        if ($req !== null) {
            $fields = ['region', 'audit_category', 'site_category', 'site_name', 'point_status', 'nc_recommendation', 'color_code'];
            foreach ($fields as $field) {
                $val = $req->getVar($field);
                if (!empty($val)) {
                    if (is_array($val)) {
                        $val = array_values(array_filter($val, function($v) { return $v !== 'selectAll' && trim((string)$v) !== ''; }));
                        if (!empty($val)) {
                            $builder->whereIn($field, $val);
                        }
                    } else if ($val !== 'selectAll') {
                        $builder->where($field, $val);
                    }
                }
            }
            
            // Handle cluster mapping since alert_gemba_audits doesn't have cluster
            $clusterFilters = $req->getVar('cluster');
            if (!empty($clusterFilters)) {
                if (is_array($clusterFilters)) {
                    $clusterFilters = array_values(array_filter($clusterFilters, function($v) { return $v !== 'selectAll' && trim((string)$v) !== ''; }));
                }
                if (!empty($clusterFilters)) {
                    $locSites = $this->db->table('alert_location_master')->select('location_name')->whereIn('cluster_name', $clusterFilters)->get()->getResultArray();
                    $hseSites = $this->db->table('alert_hse_client_master')->select('client_name')->whereIn('cluster', $clusterFilters)->get()->getResultArray();
                    
                    $clusterSites = array_values(array_unique(array_merge(array_column($locSites, 'location_name'), array_column($hseSites, 'client_name'))));
                    if (!empty($clusterSites)) {
                        $builder->whereIn('site_name', $clusterSites);
                    } else {
                        $builder->where('1=0');
                    }
                }
            }
        }

        $builder->where('status', 1);
        
        $builder->groupStart()
                ->where("source_module !=", "hse_audit")
                ->orGroupStart()
                    ->where("source_module", "hse_audit")
                    ->where("hse_detail_id IN (
                        SELECT latest_detail_id FROM (
                            SELECT 
                                MAX(d2.id) AS latest_detail_id
                            FROM alert_hse_audit_details d2
                            INNER JOIN alert_hse_audit_master latest_master
                                ON latest_master.hse_audit_id = d2.hse_audit_id
                            INNER JOIN (
                                SELECT audit_no, MAX(hse_audit_id) AS latest_hse_audit_id
                                FROM alert_hse_audit_master
                                GROUP BY audit_no
                            ) latest_audit
                                ON latest_audit.latest_hse_audit_id = latest_master.hse_audit_id
                            GROUP BY latest_master.audit_no, d2.question_id
                        ) as temp_latest_nc
                    )", null, false)
                ->groupEnd()
                ->groupEnd();
    }

    private function getSummaryStats($req, $nc_status = null)
    {
        $builder = $this->db->table('alert_gemba_audits');
        $this->applyBaseFilters($builder, $req);

        if ($nc_status !== null && $nc_status !== '') {
            if ($nc_status === 'Excluded') {
                $builder->where('LOWER(point_status)', 'excluded');
            } else if ($nc_status === 'Hold') {
                $builder->whereIn('LOWER(point_status)', ['hold', 'hold-review with client']);
            } else if ($nc_status === 'pending') {
                $builder->where('nc_status !=', 3);
            } else if ($nc_status === 'pending_due') {
                $builder->where('nc_status !=', 3);
                $now = time();
                $dayOfWeek = date('N', $now);
                $monday = strtotime('-' . ($dayOfWeek - 1) . ' days', strtotime(date('Y-m-d 00:00:00', $now)));
                $sunday = strtotime('+' . (7 - $dayOfWeek) . ' days', strtotime(date('Y-m-d 23:59:59', $now)));
                $builder->where('target_date >=', date('Y-m-d', $monday));
                $builder->where('target_date <=', date('Y-m-d', $sunday));
            } else {
                $builder->where('nc_status', $nc_status);
            }
        }

        $results = $builder->select("
            COUNT(*) as total,
            SUM(CASE WHEN nc_recommendation = 'NC' THEN 1 ELSE 0 END) as total_nc,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' THEN 1 ELSE 0 END) as total_rd,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) IN ('open', 'wip') THEN 1 ELSE 0 END) as open_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) = 'closed' THEN 1 ELSE 0 END) as closed_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) = 'excluded' THEN 1 ELSE 0 END) as excluded_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) IN ('hold', 'hold-review with client') THEN 1 ELSE 0 END) as hold_nc,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' AND LOWER(point_status) IN ('open', 'wip') THEN 1 ELSE 0 END) as open_rd,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' AND LOWER(point_status) = 'closed' THEN 1 ELSE 0 END) as closed_rd,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' AND LOWER(point_status) = 'excluded' THEN 1 ELSE 0 END) as excluded_rd,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' AND LOWER(point_status) IN ('hold', 'hold-review with client') THEN 1 ELSE 0 END) as hold_rd,
            SUM(CASE WHEN LOWER(point_status) IN ('open', 'wip') AND target_date < CURDATE() THEN 1 ELSE 0 END) as overdue,
            SUM(CASE WHEN color_code = 'Red' THEN 1 ELSE 0 END) as red_count,
            SUM(CASE WHEN color_code = 'Yellow' THEN 1 ELSE 0 END) as yellow_count,
            SUM(CASE WHEN color_code = 'Black' THEN 1 ELSE 0 END) as black_count
        ")->get()->getRowArray();

        // Additional counts for workflow status
        $builderWF = $this->db->table('alert_gemba_audits');
        $this->applyBaseFilters($builderWF, $req); // Get overall workflow counts without status filter

        $workflowStats = $builderWF->select("
            COUNT(*) as total_overall,
            SUM(CASE WHEN nc_status = 0 THEN 1 ELSE 0 END) as workflow_open,
            SUM(CASE WHEN nc_status = 1 THEN 1 ELSE 0 END) as workflow_working,
            SUM(CASE WHEN nc_status = 5 THEN 1 ELSE 0 END) as workflow_cluster_review,
            SUM(CASE WHEN nc_status = 2 THEN 1 ELSE 0 END) as workflow_auditor_review,
            SUM(CASE WHEN nc_status = 3 AND LOWER(point_status) = 'closed' THEN 1 ELSE 0 END) as workflow_closed,
            SUM(CASE WHEN LOWER(point_status) = 'excluded' THEN 1 ELSE 0 END) as workflow_excluded,
            SUM(CASE WHEN LOWER(point_status) IN ('hold', 'hold-review with client') THEN 1 ELSE 0 END) as workflow_hold,
            SUM(CASE WHEN nc_status = 4 THEN 1 ELSE 0 END) as workflow_draft,
            SUM(CASE WHEN nc_status = 6 THEN 1 ELSE 0 END) as workflow_rejected
        ")->get()->getRowArray();

        // Use total_overall as total so the dashboard total card doesn't decrease when clicking other status cards
        $results['total'] = $workflowStats['total_overall'];
        $results = array_merge($results, $workflowStats);

        // Calculate trends (This Month vs Last Month)
        $builderTM = $this->db->table('alert_gemba_audits');
        $this->applyBaseFilters($builderTM, $req);
        
        if ($nc_status !== null && $nc_status !== '') {
            if ($nc_status === 'pending') {
                $builderTM->where('nc_status !=', 3);
            } else if ($nc_status === 'pending_due') {
                $builderTM->where('nc_status !=', 3);
            } else {
                $builderTM->where('nc_status', $nc_status);
            }
        }
        
        $builderTM->where('MONTH(audit_report_date)', date('m'))->where('YEAR(audit_report_date)', date('Y'));
        $tm = $builderTM->select("
            COUNT(*) as total,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Open' THEN 1 ELSE 0 END) as open_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) = 'closed' THEN 1 ELSE 0 END) as closed_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) = 'excluded' THEN 1 ELSE 0 END) as excluded_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) IN ('hold', 'hold-review with client') THEN 1 ELSE 0 END) as hold_nc,
            SUM(CASE WHEN nc_recommendation = 'RECOMMENDATION' THEN 1 ELSE 0 END) as total_rd,
            SUM(CASE WHEN point_status = 'Open' AND target_date < CURDATE() THEN 1 ELSE 0 END) as overdue
        ")->get()->getRowArray();

        $builderLM = $this->db->table('alert_gemba_audits');
        $this->applyBaseFilters($builderLM, $req);
        
        if ($nc_status !== null && $nc_status !== '') {
            if ($nc_status === 'Excluded') {
                $builderLM->where('LOWER(point_status)', 'excluded');
            } else if ($nc_status === 'Hold') {
                $builderLM->whereIn('LOWER(point_status)', ['hold', 'hold-review with client']);
            } else if ($nc_status === 'pending') {
                $builderLM->where('nc_status !=', 3);
            } else if ($nc_status === 'pending_due') {
                $builderLM->where('nc_status !=', 3);
            } else {
                $builderLM->where('nc_status', $nc_status);
            }
        }
        
        $lastMonth = date('m', strtotime('first day of last month'));
        $lastYear = date('Y', strtotime('first day of last month'));
        $builderLM->where('MONTH(audit_report_date)', $lastMonth)->where('YEAR(audit_report_date)', $lastYear);
        $lm = $builderLM->select("
            COUNT(*) as total,
            SUM(CASE WHEN nc_recommendation = 'NC' AND point_status = 'Open' THEN 1 ELSE 0 END) as open_nc,
            SUM(CASE WHEN nc_recommendation = 'NC' AND LOWER(point_status) IN ('closed', 'excluded', 'hold-review with client') THEN 1 ELSE 0 END) as closed_nc,
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

    private function applyRoleBasedACL($builder)
    {
        apply_gemba_role_filters($builder, 'alert_gemba_audits');
    }

    private function applyFilters($builder, $req, $nc_status = null)
    {
        $this->applyBaseFilters($builder, $req);

        $statusParam = $req->getGet('nc_status');
        if ($statusParam !== null && $statusParam !== '') {
            $nc_status = $statusParam;
        } elseif ($statusParam === '') {
            $nc_status = null;
        }

        if ($nc_status !== null && $nc_status !== '') {
            if ($nc_status === 'Excluded') {
                $builder->where('LOWER(point_status)', 'excluded');
            } else if ($nc_status === 'Hold') {
                $builder->whereIn('LOWER(point_status)', ['hold', 'hold-review with client']);
            } else if ($nc_status === 'pending') {
                $builder->where('nc_status !=', 3);
            } else if ($nc_status === 'pending_due') {
                $builder->where('nc_status !=', 3);
                $now = time();
                $dayOfWeek = date('N', $now);
                $monday = strtotime('-' . ($dayOfWeek - 1) . ' days', strtotime(date('Y-m-d 00:00:00', $now)));
                $sunday = strtotime('+' . (7 - $dayOfWeek) . ' days', strtotime(date('Y-m-d 23:59:59', $now)));
                $builder->where('target_date >=', date('Y-m-d', $monday));
                $builder->where('target_date <=', date('Y-m-d', $sunday));
            } else if ($nc_status == 3 || $nc_status === '3') {
                $builder->where('nc_status', 3);
                $builder->where('LOWER(point_status)', 'closed');
            } else {
                $builder->where('nc_status', $nc_status);
            }
        }
    }

    public function stats_ajax()
    {
        $req = service('request');
        $nc_status = $req->getGet('nc_status');
        $stats = $this->getSummaryStats($req, $nc_status);
        return $this->response->setJSON($stats);
    }

    public function dependent_filters_ajax()
    {
        $req = service('request');
        $baseBuilder = $this->db->table('alert_gemba_audits');
        
        // Apply Base ACL and Deduplication
        $baseBuilder->where('status !=', 2);
        $this->applyRoleBasedACL($baseBuilder);
        $baseBuilder->groupStart()
                ->where("source_module !=", "hse_audit")
                ->orGroupStart()
                    ->where("source_module", "hse_audit")
                    ->where("hse_audit_id IN (SELECT MAX(hse_audit_id) FROM alert_hse_audit_master GROUP BY audit_no)", null, false)
                ->groupEnd()
                ->groupEnd();

        $region = $req->getVar('region');
        $cluster = $req->getVar('cluster');
        $auditCat = $req->getVar('audit_category');
        $siteCat = $req->getVar('site_category');
        $siteName = $req->getVar('site_name');

        $results = [];

        // Determine matching sites based on region or cluster if provided to get corresponding clusters
        $siteBuilderForCluster = $this->db->table('alert_gemba_sites')->select('DISTINCT(site_name)')->where('status', 1)->where('site_name !=', '');
        apply_gemba_role_filters($siteBuilderForCluster, 'alert_gemba_sites');
        if (!empty($region)) {
            if (is_array($region)) {
                $region = array_values(array_filter($region, function($v) { return $v !== 'selectAll' && trim((string)$v) !== ''; }));
            }
            if (!empty($region)) {
                $siteBuilderForCluster->whereIn('region', $region);
            }
        }
        $allowedSitesArray = $siteBuilderForCluster->get()->getResultArray();
        $allowedSites = array_values(array_column($allowedSitesArray, 'site_name'));

        $results['cluster'] = [];
        if (!empty($allowedSites)) {
            $c1 = $this->db->table("alert_location_master")->select("cluster_name AS cluster")->whereIn("location_name", $allowedSites)->whereIn("status", [1, '1'])->where("cluster_name IS NOT NULL")->where("TRIM(cluster_name) != ''")->get()->getResultArray();
            $c2 = $this->db->table("alert_hse_client_master")->select("cluster")->whereIn("client_name", $allowedSites)->where("cluster IS NOT NULL")->where("TRIM(cluster) != ''")->get()->getResultArray();
            $mergedC = array_filter(array_unique(array_merge(
                array_column($c1, 'cluster'),
                array_column($c2, 'cluster')
            )), function($val) { return trim((string)$val) !== ''; });
            natcasesort($mergedC);
            $results['cluster'] = array_values($mergedC);
        }

        // Apply cluster site filter to the base builder
        if (!empty($cluster)) {
            if (is_array($cluster)) {
                $cluster = array_values(array_filter($cluster, function($v) { return $v !== 'selectAll' && trim((string)$v) !== ''; }));
            }
            if (!empty($cluster)) {
                $locSites = $this->db->table('alert_location_master')->select('location_name')->whereIn('cluster_name', $cluster)->get()->getResultArray();
                $hseSites = $this->db->table('alert_hse_client_master')->select('client_name')->whereIn('cluster', $cluster)->get()->getResultArray();
                $clusterSites = array_values(array_unique(array_merge(array_column($locSites, 'location_name'), array_column($hseSites, 'client_name'))));
                
                if (!empty($clusterSites)) {
                    $baseBuilder->whereIn('site_name', $clusterSites);
                } else {
                    $baseBuilder->where('1=0');
                }
            }
        }

        // 1. Audit Categories (Filter by Region and Cluster)
        $b0 = clone $baseBuilder;
        if (!empty($region)) {
            $regArr = is_array($region) ? array_values($region) : [$region];
            $b0->whereIn('region', $regArr);
        }
        $results['audit_category'] = array_column($b0->select('audit_category')->distinct()->where('audit_category !=', '')->where('audit_category IS NOT NULL')->get()->getResultArray(), 'audit_category');

        // 2. Site Categories (Filter by Region, Cluster, and Audit Cat)
        $b1 = clone $b0;
        if (!empty($auditCat)) {
            $catArr = is_array($auditCat) ? array_values($auditCat) : [$auditCat];
            $b1->whereIn('audit_category', $catArr);
        }
        $results['site_category'] = array_column($b1->select('site_category')->distinct()->where('site_category !=', '')->where('site_category IS NOT NULL')->get()->getResultArray(), 'site_category');

        // 3. Site Names (Filter by Region, Audit Cat, Site Cat)
        $b2 = clone $b1;
        if (!empty($siteCat)) {
            $siteCatArr = is_array($siteCat) ? array_values($siteCat) : [$siteCat];
            $b2->whereIn('site_category', $siteCatArr);
        }
        $results['site_name'] = array_column($b2->select('site_name')->distinct()->where('site_name !=', '')->where('site_name IS NOT NULL')->get()->getResultArray(), 'site_name');

        // 4. Personnel (Filter by Region, Audit Cat, Site Cat, Site Name)
        $b3 = clone $b2;
        if (!empty($siteName)) {
            $siteNameArr = is_array($siteName) ? array_values($siteName) : [$siteName];
            $b3->whereIn('site_name', $siteNameArr);
        }
        $results['auditor_name'] = array_column((clone $b3)->select('auditor_name')->distinct()->where('auditor_name !=', '')->where('auditor_name IS NOT NULL')->get()->getResultArray(), 'auditor_name');
        $results['account_manager'] = array_column((clone $b3)->select('account_manager')->distinct()->where('account_manager !=', '')->where('account_manager IS NOT NULL')->get()->getResultArray(), 'account_manager');
        $results['cluster_manager_spoc'] = array_column((clone $b3)->select('cluster_manager_spoc')->distinct()->where('cluster_manager_spoc !=', '')->where('cluster_manager_spoc IS NOT NULL')->get()->getResultArray(), 'cluster_manager_spoc');

        // NC Type and Point Status (No dependent filters, just based on ACL)
        $results['nc_recommendation'] = array_column((clone $baseBuilder)->select('nc_recommendation')->distinct()->where('nc_recommendation !=', '')->where('nc_recommendation IS NOT NULL')->get()->getResultArray(), 'nc_recommendation');
        $results['point_status'] = array_column((clone $baseBuilder)->select('point_status')->distinct()->where('point_status !=', '')->where('point_status IS NOT NULL')->get()->getResultArray(), 'point_status');

        return $this->response->setJSON($results);
    }

    public function table_ajax()
    {
        $req = service('request');
        $builder = $this->db->table('alert_gemba_audits');

        $nc_status = $req->getGet('nc_status');
        if ($nc_status === '') $nc_status = null;
        
        $this->applyFilters($builder, $req, $nc_status);

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
                ->orLike('nc_worked_by', $searchValue)
                ->orLike('nc_remark', $searchValue)
                ->orLike('working_remarks', $searchValue)
                ->orLike('closed_remarks', $searchValue)
                ->groupEnd();
        }

        $recordsFiltered = $builder->countAllResults(false);

        $totalRecordsBuilder = $this->db->table('alert_gemba_audits')->where('status', 1);
        $this->applyRoleBasedACL($totalRecordsBuilder);
        // Apply the base HSE deduplication filter so recordsTotal matches the available pool
        $totalRecordsBuilder->groupStart()
                ->where("source_module !=", "hse_audit")
                ->orGroupStart()
                    ->where("source_module", "hse_audit")
                    ->where("hse_audit_id IN (SELECT MAX(hse_audit_id) FROM alert_hse_audit_master GROUP BY audit_no)", null, false)
                ->groupEnd()
                ->groupEnd();
        
        $totalRecords = $totalRecordsBuilder->countAllResults();

        $start = (int) ($req->getGet('start') ?? 0);
        $length = (int) ($req->getGet('length') ?? 10);
        $order = $req->getGet('order');

        if ($order && isset($order[0])) {
            $colIdx = $order[0]['column'];
            $dir = $order[0]['dir'];
            $columns = $req->getGet('columns');
            $colName = $columns[$colIdx]['data'] ?? 'gemba_sr_no';
            if ($colName == 'action' || $colName == 'bulk_select') {
                $colName = 'gemba_sr_no';
            }
            $builder->orderBy($colName, $dir);
        } else {
            if ($nc_status === 'pending' || $nc_status === 'pending_due') {
                $builder->orderBy("CASE 
                    WHEN UPPER(color_code)='BLACK' THEN 1
                    WHEN UPPER(color_code)='RED' THEN 2
                    WHEN UPPER(color_code)='YELLOW' THEN 3
                    ELSE 4
                END", 'ASC', false);
                $builder->orderBy('target_date', 'ASC');
            } else {
                $builder->orderBy('gemba_sr_no', 'DESC');
            }
        }

        if ($length != -1) {
            $builder->limit($length, $start);
        }

        $data = $builder->get()->getResultArray();

        $canClose = gemba_can_write();



        foreach ($data as &$row) {
            $row['point_status_raw'] = trim($row['point_status'] ?? '');
            $row['point_status'] = $this->getStatusBadge(!empty($row['point_status_raw']) ? $row['point_status_raw'] : 'N/A');
            $row['color_code'] = $this->getColorBadge($row['color_code']);

            // Formatting Working Stage
            $workingRem = !empty($row['working_remarks']) ? $row['working_remarks'] : '';
            $row['working_remarks'] = !empty($workingRem) ? nl2br(htmlspecialchars($workingRem)) : '-';

            $workingFile = trim((string)($row['working_uploaded_file'] ?? ''));
            if (!empty($workingFile)) {
                $filePath = base_url($workingFile);
                $ext = strtolower(pathinfo($workingFile, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $row['working_uploaded_file'] = '<a href="' . $filePath . '" target="_blank" title="View Working Image"><img src="' . $filePath . '" style="max-height: 45px; max-width: 65px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-block\';" /><span class="btn btn-sm btn-light-primary py-1 px-2 fs-8 text-nowrap" style="display:none;"><i class="fa fa-file-alt me-1 text-primary"></i> View</span></a>';
                } else if ($ext === 'pdf') {
                    $row['working_uploaded_file'] = '<a href="' . $filePath . '" target="_blank" class="btn btn-sm btn-light-danger py-1 px-2 fs-8 text-nowrap"><i class="fa fa-file-pdf me-1 text-danger"></i> PDF</a>';
                } else {
                    $row['working_uploaded_file'] = '<a href="' . $filePath . '" target="_blank" class="btn btn-sm btn-light-primary py-1 px-2 fs-8 text-nowrap"><i class="fa fa-file-alt me-1 text-primary"></i> View File</a>';
                }
            } else {
                $row['working_uploaded_file'] = '-';
            }

            $row['working_date'] = !empty($row['working_date']) ? date('d-M-Y H:i', strtotime($row['working_date'])) : '-';

            // Formatting Closing Stage
            $closingRem = !empty($row['closed_remarks']) ? $row['closed_remarks'] : '';
            $row['closed_remarks'] = !empty($closingRem) ? nl2br(htmlspecialchars($closingRem)) : '-';

            $closingFile = trim((string)($row['closed_uploaded_file'] ?? ''));
            if (!empty($closingFile)) {
                $filePath = base_url($closingFile);
                $ext = strtolower(pathinfo($closingFile, PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $row['closed_uploaded_file'] = '<a href="' . $filePath . '" target="_blank" title="View Closing Image"><img src="' . $filePath . '" style="max-height: 45px; max-width: 65px; border-radius: 6px; object-fit: cover; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'inline-block\';" /><span class="btn btn-sm btn-light-success py-1 px-2 fs-8 text-nowrap" style="display:none;"><i class="fa fa-file-alt me-1 text-success"></i> View</span></a>';
                } else if ($ext === 'pdf') {
                    $row['closed_uploaded_file'] = '<a href="' . $filePath . '" target="_blank" class="btn btn-sm btn-light-danger py-1 px-2 fs-8 text-nowrap"><i class="fa fa-file-pdf me-1 text-danger"></i> PDF</a>';
                } else {
                    $row['closed_uploaded_file'] = '<a href="' . $filePath . '" target="_blank" class="btn btn-sm btn-light-success py-1 px-2 fs-8 text-nowrap"><i class="fa fa-file-alt me-1 text-success"></i> View File</a>';
                }
            } else {
                $row['closed_uploaded_file'] = '-';
            }

            // Formatting Workflow Status
            $wfStatusMap = [
                0 => '<span class="badge bg-danger">Open</span>',
                1 => '<span class="badge bg-info">Working</span>',
                2 => '<span class="badge bg-warning text-dark">Auditor Review</span>',
                3 => '<span class="badge bg-success">Closed</span>',
                4 => '<span class="badge bg-secondary text-dark">Draft</span>',
                5 => '<span class="badge bg-primary">Cluster Review</span>',
                6 => '<span class="badge bg-dark">Rejected</span>',
            ];
            $row['nc_status_val'] = $row['nc_status'];
            $row['nc_status'] = $wfStatusMap[(int) $row['nc_status']] ?? '<span class="badge bg-secondary text-dark">Unknown</span>';

            $action = '';

            // Workflow buttons
            $wfStatus = (int) $row['nc_status_val'];

            $isSuperAdmin = isSuperAdmin();
            $isAdminFlag = (session()->get('admin_flag') ?? 0) == 1;
            $isAuditorRole = isAuditor();
            $canBulkAction = ($isSuperAdmin || $isAdminFlag || $isAuditorRole);

            $userName = trim((string) getUserName());
            $isAssignedAM = isAccountManager();
            $isAssignedCM = isClusterManager();
            $isAssignedAuditor = isAuditor();
            
            $canRejectWorking = ($isSuperAdmin || $isAssignedAM || $isAssignedCM || $isAssignedAuditor);

            if (($canBulkAction && ($wfStatus === 2 || $wfStatus === 0)) || ($canRejectWorking && $wfStatus === 1)) {
                $row['bulk_select'] = '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="' . $row['gemba_sr_no'] . '" data-status="' . $wfStatus . '" /></div>';
            } else {
                $row['bulk_select'] = '';
            }

                // Status Badge
                $statusColors = [
                    0 => ['class' => 'bg-danger', 'text' => 'Open'],
                    1 => ['class' => 'bg-warning text-dark', 'text' => 'Working'],
                    5 => ['class' => 'bg-dark', 'text' => 'Cluster Review'],
                    2 => ['class' => 'bg-primary', 'text' => 'Auditor Review'],
                    3 => ['class' => 'bg-success', 'text' => 'Closed'],
                    4 => ['class' => 'bg-secondary', 'text' => 'Draft'],
                    6 => ['class' => 'bg-danger', 'text' => 'Rejected'],
                ];
                $badgeInfo = $statusColors[$wfStatus] ?? ['class' => 'bg-secondary', 'text' => 'Unknown'];
                $action .= '<div class="mb-2"><span class="badge ' . $badgeInfo['class'] . ' fs-7 px-3 py-2">' . $badgeInfo['text'] . '</span></div>';

                // Action buttons based on workflow
                $action .= '<div class="btn-group">';

                if ($wfStatus === 0 && ($isSuperAdmin || $isAssignedAM || $isAssignedCM || $isAssignedAuditor)) {
                    $action .= '<button class="btn btn-success btn-sm mt-2 me-1" title="Start Working" onclick="updateNcStatus(' . $row['gemba_sr_no'] . ',\'working\')"><i class="fa fa-play"></i></button>';
                }

                if ($wfStatus === 1 && ($isSuperAdmin || $isAssignedAM || $isAssignedCM || $isAssignedAuditor)) {
                    $action .= '<button class="btn btn-info btn-sm mt-2 me-1" title="Working - Update Details" onclick="edit_id(this,' . $row['gemba_sr_no'] . ')" data-ajax-url="' . base_url("Masters/GembaNcTracker/get_form_data/" . $row['gemba_sr_no']) . '"><i class="fa fa-pencil-alt"></i></button>';
                    $action .= '<button class="btn btn-danger btn-sm mt-2 me-1" title="Reject to Open" onclick="updateNcStatus(' . $row['gemba_sr_no'] . ',\'0\')"><i class="fa fa-times"></i></button>';
                }

                if ($wfStatus === 5 && ($isSuperAdmin || $isAssignedCM || $isAssignedAuditor)) {
                    $action .= '<button class="btn btn-success btn-sm mt-2 me-1" title="Approve to Auditor Review" onclick="updateNcStatus(' . $row['gemba_sr_no'] . ',\'auditor\')"><i class="fa fa-check"></i></button>';
                }

                if ($wfStatus === 5 && ($isSuperAdmin || $isAssignedCM || $isAssignedAuditor)) {
                    $action .= '<button class="btn btn-danger btn-sm mt-2 me-1" title="Reject to Open" onclick="updateNcStatus(' . $row['gemba_sr_no'] . ',\'0\')"><i class="fa fa-times"></i></button>';
                }

                if ($wfStatus === 2 && ($isSuperAdmin || $isAssignedAuditor)) {
                    $action .= '<button class="btn btn-success btn-sm mt-2 me-1" title="Close NC" onclick="openCloseModal(' . $row['gemba_sr_no'] . ', \'' . htmlspecialchars($row['audit_report_date'] ?? '', ENT_QUOTES) . '\', \'closed\')"><i class="fa fa-check-double"></i></button>';
                    $action .= '<button class="btn btn-danger btn-sm mt-2 me-1" title="Reject to Open" onclick="updateNcStatus(' . $row['gemba_sr_no'] . ',\'0\')"><i class="fa fa-times"></i></button>';
                }

                $action .= '</div>';
                $action .= '<button class="btn btn-sm btn-light-info mt-2 ms-1" title="View Action History" onclick="showNcActionHistory(' . $row['gemba_sr_no'] . ', \'GEMBA\', \'' . htmlspecialchars($row['site_name'] ?? '', ENT_QUOTES) . '\')"><i class="fas fa-history"></i></button>';
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
        helper(['gemba_hse_sync']);
        $req = service('request');
        $id     = $req->getVar('id');
        $action = $req->getVar('status');

        $postData = [
            'close_date'       => $req->getVar('close_date'),
            'rejection_reason' => $req->getVar('rejection_reason'),
            'closure_status'   => $req->getVar('closure_status') ?? 'Closed',
            'closed_remarks'   => $req->getVar('closed_remarks') ?? null,
        ];

        // Handle closing proof file upload when closing
        if (
            in_array($action, ['closed', 'auditor_direct_close'], true) &&
            isset($_FILES['closed_uploaded_file']) &&
            !empty($_FILES['closed_uploaded_file']['name']) &&
            $_FILES['closed_uploaded_file']['error'] === UPLOAD_ERR_OK
        ) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'doc', 'docx'];
            $ext     = strtolower(pathinfo($_FILES['closed_uploaded_file']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, $allowed)) {
                $newName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['closed_uploaded_file']['name']);
                $dest    = FCPATH . 'uploads/nc_closed_files/';
                if (!is_dir($dest)) mkdir($dest, 0755, true);
                if (move_uploaded_file($_FILES['closed_uploaded_file']['tmp_name'], $dest . $newName)) {
                    $postData['closed_uploaded_file'] = 'uploads/nc_closed_files/' . $newName;
                }
            }
        }

        $db = db_connect();
        $gembaRow = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();

        if (!$gembaRow) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Record not found']);
        }

        $userId   = $_SESSION['user_id'] ?? null;
        if (!empty($userId) && is_numeric($userId) && (int) $userId > 0) {
            $userId = (int) $userId;
        } elseif (isSuperAdmin()) {
            $userId = 0;
        } else {
            $userId = null;
        }
        $userName = trim((string) getUserName());

        if ($gembaRow['source_module'] !== 'hse_audit') {
             $result = $this->execute_gemba_only_nc_action($id, $action, $postData, $userId, $userName);
        } else {
             $hseDetailId = $gembaRow['hse_detail_id'];
             $result = execute_hse_gemba_nc_action($hseDetailId, $action, $postData, $userId, $userName);
        }

        if ($result['status'] == 1 && isset($result['new_status']) && $result['old_status'] != $result['new_status']) {
            $this->send_nc_status_email($result['gemba_sr_no'], $result['old_status'], $result['new_status']);
        }

        return $this->response->setJSON([
            'status'  => $result['status'],
            'message' => $result['message']
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
                $response['data']['before_photo_url'] = '';
                $workingFile = !empty($data['working_uploaded_file']) ? $data['working_uploaded_file'] : '';
                if (!empty($workingFile)) {
                    $response['data']['after_photo_url'] = base_url($workingFile);
                }
                if (isset($data['working_remarks'])) {
                    $response['data']['nc_remark'] = $data['working_remarks'];
                }
                $response['status'] = "1";
                $response['message'] = "Details found";
            }
        }
        return $this->response->setJSON($response);
    }

    public function save_details($id = null)
    {
        helper(['gemba_hse_sync']);
        $request = service('request');
        $id = $id ?? $request->getVar('gemba_sr_no');
        $ncRemark = $request->getVar('nc_remark');
        $action = $request->getVar('action');

        if (!$id) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid ID']);
        }

        $db = db_connect();
        $gembaRow = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();
        
        if (!$gembaRow) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Record not found']);
        }
        $userId = $_SESSION['user_id'] ?? null;
        $userName = trim((string) getUserName());

        $postData = [
            'nc_remark' => $ncRemark
        ];

        $file = $request->getFile('nc_after_photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if ($file->move(FCPATH . 'uploads/gemba_nc_proofs/', $newName)) {
                $postData['nc_after_photo'] = 'uploads/gemba_nc_proofs/' . $newName;
            }
        }

        if ($gembaRow['source_module'] !== 'hse_audit') {
             $result = $this->execute_gemba_only_nc_action($id, $action, $postData, $userId, $userName);
        } else {
             $hseDetailId = $gembaRow['hse_detail_id'];
             $result = execute_hse_gemba_nc_action($hseDetailId, $action, $postData, $userId, $userName);
        }

        if ($result['status'] == 1 && $action === 'submit_for_review') {
            $this->send_nc_status_email($id, 1, $result['new_status']);
        }

        return $this->response->setJSON(['status' => $result['status'] == 1 ? 'success' : 'error', 'message' => $result['message']]);
    }

    public function send_nc_status_email($ncId, $oldStatus, $newStatus)
    {
        $detail = $this->db->table('alert_gemba_audits')->where('gemba_sr_no', $ncId)->get()->getRowArray();
        if (!$detail)
            return false;

        $db = \Config\Database::connect();
        
        $clusterManagerEmail = '';
        if (!empty($detail['cluster_manager_spoc']) && $detail['cluster_manager_spoc'] !== 'NA') {
            $cm = $db->table('alert_users')->where('user_name', $detail['cluster_manager_spoc'])->where('status', 1)->get()->getRowArray();
            if ($cm) {
                $clusterManagerEmail = $cm['user_email'];
            }
        }
        
        $auditorEmail = '';
        if (!empty($detail['auditor_name']) && $detail['auditor_name'] !== 'NA') {
            $auditor = $db->table('alert_users')->where('user_name', $detail['auditor_name'])->where('status', 1)->get()->getRowArray();
            if ($auditor) {
                $auditorEmail = $auditor['user_email'];
            }
        }

        $toEmail = '';
        $ccEmail = '';
        $subject = 'Gemba NC Tracker Update: ' . $detail['unique_no'];
        $message = "<h3>Gemba NC Tracker Update</h3>";
        $message .= "<p>The status of Gemba Audit (<strong>{$detail['unique_no']}</strong> - {$detail['site_name']}) has been updated.</p>";
        $message .= "<ul>";
        $message .= "<li><strong>Observation Point:</strong> {$detail['observation_point']}</li>";
        $message .= "<li><strong>Target Date:</strong> {$detail['target_date']}</li>";
        
        if ($newStatus == 5) {
            $toEmail = $clusterManagerEmail;
            $ccEmail = $auditorEmail;
            $message .= "<li><strong>New Status:</strong> Pending Cluster Review</li>";
            $message .= "<li><strong>Action Required:</strong> Please review the NC details.</li>";
        } elseif ($newStatus == 2) {
            $toEmail = $auditorEmail;
            $message .= "<li><strong>New Status:</strong> Pending Auditor Review</li>";
            $message .= "<li><strong>Action Required:</strong> Please review the NC details for closure.</li>";
        } elseif ($newStatus == 3) {
            $toEmail = $auditorEmail;
            $message .= "<li><strong>New Status:</strong> Closed</li>";
            $message .= "<li><strong>Note:</strong> This NC has been successfully closed.</li>";
        } elseif ($newStatus == 0) {
            $toEmail = $auditorEmail;
            $message .= "<li><strong>New Status:</strong> Rejected / Reopened</li>";
            $message .= "<li><strong>Reason:</strong> " . ($detail['rejection_reason'] ?? 'Not specified') . "</li>";
            $message .= "<li><strong>Action Required:</strong> Please review the rejection reason and update the NC.</li>";
        } else {
            return false;
        }
        $message .= "</ul>";

        if (empty($toEmail)) {
            return false;
        }

        helper('email_service');
        $ccArray = !empty($ccEmail) ? explode(',', $ccEmail) : [];
        return sendSystemEmail($toEmail, $subject, $message, [], $ccArray);
    }

    public function close_nc()
    {
        helper(['gemba_hse_sync']);
        gemba_require_write_access(true);

        $req = service('request');
        $id = $req->getVar('gemba_sr_no') ?: $req->getVar('id');
        $closeDate = $req->getVar('closed_date') ?: ($req->getVar('close_date') ?: date('Y-m-d'));
        $remarks = $req->getVar('closure_remarks');
        $status = $req->getVar('closure_status') ?: 'Closed';

        $postData = [
            'close_date' => $closeDate,
            'closed_remarks' => $remarks,
            'closure_status' => $status
        ];

        $file = $req->getFile('closed_nc_photo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $newName = $file->getRandomName();
            if ($file->move(FCPATH . 'uploads/gemba_nc_closure_proofs/', $newName)) {
                $postData['closed_uploaded_file'] = 'uploads/gemba_nc_closure_proofs/' . $newName;
            }
        }

        if (!$id)
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid ID']);

        $db = db_connect();
        $gembaRow = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();
        if (!$gembaRow) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Record not found']);
        }
        $userId = $_SESSION['user_id'] ?? null;
        $userName = trim((string) getUserName());

        if ($gembaRow['source_module'] !== 'hse_audit') {
             $result = $this->execute_gemba_only_nc_action($id, 'closed', $postData, $userId, $userName);
        } else {
             $hseDetailId = $gembaRow['hse_detail_id'];
             $result = execute_hse_gemba_nc_action($hseDetailId, 'closed', $postData, $userId, $userName);
        }

        if ($result['status'] == 1 && isset($result['new_status'])) {
            $this->send_nc_status_email($id, $result['old_status'], $result['new_status']);
        }

        return $this->response->setJSON(['status' => $result['status'] == 1 ? 'success' : 'error', 'message' => $result['message']]);
    }

    private function getStatusBadge($status)
    {
        $badges = [
            'open' => '<span class="badge bg-danger">Open</span>',
            'closed' => '<span class="badge bg-success">Closed</span>',
            'excluded' => '<span class="badge bg-dark">Excluded</span>',
            'hold-review with client' => '<span class="badge bg-warning text-dark">Hold-review with Client</span>'
        ];
        return $badges[strtolower(trim($status))] ?? '<span class="badge bg-secondary text-dark">' . $status . '</span>';
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

    public function bulk_close_nc()
    {
        helper(['gemba_hse_sync']);
        gemba_require_write_access(true);

        $req = service('request');
        $ids = $req->getVar('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No records selected']);
        }

        $closeDate = $req->getVar('closed_date') ?: date('Y-m-d');
        $remarks = $req->getVar('closure_remarks') ?: 'Closed via Bulk Tracker';
        $status = $req->getVar('closure_status') ?: 'Closed';
        
        $postData = [
            'close_date' => $closeDate,
            'nc_remark' => $remarks,
            'closure_status' => $status
        ];

        $userId = $_SESSION['user_id'] ?? null;
        $userName = trim((string) getUserName());

        $db = db_connect();
        $db->transBegin();

        $successCount = 0;
        $emailsToSend = [];

        foreach ($ids as $id) {
            $gembaRow = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();
            if (!$gembaRow) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Record not found for ID ' . $id]);
            }
            if ((int)$gembaRow['nc_status'] !== 2) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Only NCs in Auditor Review can be closed. Record ' . $gembaRow['unique_no'] . ' is not in Auditor Review.']);
            }

            if ($gembaRow['source_module'] !== 'hse_audit') {
                $result = $this->execute_gemba_only_nc_action($id, 'closed', $postData, $userId, $userName);
            } else {
                $hseDetailId = $gembaRow['hse_detail_id'];
                $result = execute_hse_gemba_nc_action($hseDetailId, 'closed', $postData, $userId, $userName);
            }

            if ($result['status'] == 0) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to close record ' . $gembaRow['unique_no'] . ': ' . $result['message']]);
            }
            $successCount++;
            if (isset($result['new_status'])) {
                $emailsToSend[] = ['id' => $id, 'old' => $result['old_status'], 'new' => $result['new_status']];
            }
        }

        $db->transCommit();

        foreach ($emailsToSend as $email) {
            $this->send_nc_status_email($email['id'], $email['old'], $email['new']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $successCount . ' NCs closed successfully']);
    }

    public function bulk_working_nc()
    {
        helper(['gemba_hse_sync']);
        gemba_require_write_access(true);

        $req = service('request');
        $ids = $req->getVar('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No records selected']);
        }

        $userId = $_SESSION['user_id'] ?? null;
        $userName = trim((string) getUserName());

        $db = db_connect();
        $db->transBegin();

        $successCount = 0;
        $emailsToSend = [];

        foreach ($ids as $id) {
            $gembaRow = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();
            if (!$gembaRow) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Record not found for ID ' . $id]);
            }
            if ((int)$gembaRow['nc_status'] !== 0) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Only Open NCs can be converted to Working. Record ' . $gembaRow['unique_no'] . ' is not Open.']);
            }

            if ($gembaRow['source_module'] !== 'hse_audit') {
                $result = $this->execute_gemba_only_nc_action($id, 'working', [], $userId, $userName);
            } else {
                $hseDetailId = $gembaRow['hse_detail_id'];
                $result = execute_hse_gemba_nc_action($hseDetailId, 'working', [], $userId, $userName);
            }

            if ($result['status'] == 0) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => 'Failed to update record ' . $gembaRow['unique_no'] . ': ' . $result['message']]);
            }
            $successCount++;
            if (isset($result['new_status']) && $result['old_status'] != $result['new_status']) {
                $emailsToSend[] = ['id' => $id, 'old' => $result['old_status'], 'new' => $result['new_status']];
            }
        }

        $db->transCommit();

        foreach ($emailsToSend as $email) {
            $this->send_nc_status_email($email['id'], $email['old'], $email['new']);
        }

        return $this->response->setJSON(['status' => 'success', 'message' => $successCount . ' NCs converted to Working successfully']);
    }

    public function bulk_reject_nc()
    {
        helper(['gemba_hse_sync']);
        gemba_require_write_access(true);

        $req = service('request');
        $ids = $req->getVar('ids');
        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No records selected']);
        }

        $userId = $_SESSION['user_id'] ?? null;
        $userName = trim((string) getUserName());

        $db = db_connect();
        $db->transBegin();

        $successCount = 0;
        $skippedCount = 0;
        $emailsToSend = [];

        foreach ($ids as $id) {
            $gembaRow = $db->table('alert_gemba_audits')->where('gemba_sr_no', $id)->get()->getRowArray();
            
            if (!$gembaRow) {
                $skippedCount++;
                continue;
            }
            
            if ((int)$gembaRow['nc_status'] !== 1) { // 1 is Working
                $skippedCount++;
                continue;
            }

            $postData = [
                'rejection_reason' => 'Bulk Rejected'
            ];

            if ($gembaRow['source_module'] !== 'hse_audit') {
                $result = $this->execute_gemba_only_nc_action($id, '0', $postData, $userId, $userName);
            } else {
                $hseDetailId = $gembaRow['hse_detail_id'];
                $result = execute_hse_gemba_nc_action($hseDetailId, '0', $postData, $userId, $userName);
            }

            if ($result['status'] == 0) {
                $skippedCount++;
            } else {
                $successCount++;
                if (isset($result['new_status']) && $result['old_status'] != $result['new_status']) {
                    $emailsToSend[] = ['id' => $id, 'old' => $result['old_status'], 'new' => $result['new_status']];
                }
            }
        }

        $db->transCommit();

        foreach ($emailsToSend as $email) {
            $this->send_nc_status_email($email['id'], $email['old'], $email['new']);
        }
        
        if ($successCount > 0) {
            if ($skippedCount > 0) {
                $msg = "{$successCount} NC records converted from Working to Open. {$skippedCount} record(s) was skipped because its status was no longer Working.";
            } else {
                $msg = "{$successCount} NC records rejected successfully and converted to Open.";
            }
            return $this->response->setJSON(['status' => 'success', 'message' => $msg]);
        } else {
            return $this->response->setJSON(['status' => 'error', 'message' => "0 records were rejected. {$skippedCount} record(s) skipped (status may have changed or unauthorized)."]);
        }
    }

    public function export_excel()
    {
        return $this->export_csv();
    }
    public function export_csv()
    {
        $req = service('request');
        $builder = $this->db->table('alert_gemba_audits');
        $nc_status = $req->getVar('nc_status');
        $this->applyFilters($builder, $req, $nc_status);

        $searchPost = $req->getVar('search');
        $searchValue = is_array($searchPost) && isset($searchPost['value']) ? trim((string) $searchPost['value']) : '';
        
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

        $builder->orderBy('gemba_sr_no', 'DESC');
        $data = $builder->get()->getResultArray();

        while (ob_get_level()) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment;filename="Gemba_NC_Tracker_' . date('YmdHis') . '.csv"');
        header('Cache-Control: max-age=0');
        header("Pragma: no-cache");
        header("Expires: 0");

        $output = fopen('php://output', 'w');
        // Add BOM for Excel compatibility
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $columns = [
            'gemba_sr_no' => 'Gemba Sr No', 'unique_no' => 'Unique No', 'region' => 'Region', 'auditor_name' => 'Auditor Name', 'audit_category' => 'Audit Category', 'audit_type' => 'Audit Type', 'audit_doc_type' => 'Audit Doc Type', 'site_name' => 'Site Name', 'site_type_1' => 'Site Type 1', 'site_type_2' => 'Site Type 2', 'site_category' => 'Site Category', 'audit_mail_received_date' => 'Audit Mail Received Date', 'audit_report_date' => 'Audit Report Date', 'weeknum_raised' => 'Weeknum Raised', 'month_raised' => 'Month Raised', 'year_month_raised' => 'Year Month Raised', 'year_raised' => 'Year Raised', 'checklist_category' => 'Checklist Category', 'nc_recommendation' => 'NC Recommendation', 'qhse_remarks' => 'QHSE Remarks', 'observation_point' => 'Observation Point', 'risks_details' => 'Risks Details', 'action_recommendation' => 'Action Recommendation', 'specifications' => 'Specifications', 'ua_uc_type' => 'UA/UC Type', 'risk_severity' => 'Risk Severity', 'risk_probability' => 'Risk Probability', 'color_code' => 'Color Code', 'cost_type' => 'Cost Type', 'remarks_wm' => 'Remarks WM', 'whose_scope' => 'Whose Scope', 'remarks_corporate' => 'Remarks Corporate', 'ageing_days' => 'Ageing Days', 'age_bracket' => 'Age Bracket', 'target_date' => 'Target Date', 'closed_date' => 'Closed Date', 'point_status' => 'Point Status', 'point_category' => 'Point Category', 'closure_status' => 'Closure Status', 'weeknum_closed' => 'Weeknum Closed', 'month_closed' => 'Month Closed', 'year_month_closed' => 'Year Month Closed', 'risk_severity_rating' => 'Risk Severity Rating', 'risk_probability_rating' => 'Risk Probability Rating', 'combined_risk_rating' => 'Combined Risk Rating', 'times_repeated' => 'Times Repeated', 'combined_risk_freq' => 'Combined Risk Freq', 'final_rating' => 'Final Rating', 'followup_by' => 'Followup By', 'weeknum_yearmonth_raised' => 'Weeknum Yearmonth Raised', 'weeknum_yearmonth_closed' => 'Weeknum Yearmonth Closed', 'cluster_manager_spoc' => 'Cluster Manager SPOC', 'account_manager' => 'Account Manager', 'created_by' => 'Created By', 'updated_by' => 'Updated By', 'status' => 'Status', 'default_date' => 'Default Date', 'update_date' => 'Update Date', 'hse_audit_id' => 'HSE Audit ID', 'hse_detail_id' => 'HSE Detail ID', 'source_module' => 'Source Module', 'nc_status' => 'NC Status', 'working_remarks' => 'Working Remarks', 'working_uploaded_file' => 'Working Uploaded File', 'nc_worked_by' => 'NC Worked By', 'working_date' => 'Working Date', 'closed_remarks' => 'Closing Remarks', 'closed_uploaded_file' => 'Closing Uploaded File', 'nc_closed_by' => 'NC Closed By', 'nc_closed_date' => 'NC Closed Date', 'nc_cluster_reviewed_by' => 'NC Cluster Reviewed By', 'nc_cluster_reviewed_date' => 'NC Cluster Reviewed Date', 'nc_auditor_reviewed_by' => 'NC Auditor Reviewed By', 'nc_auditor_reviewed_date' => 'NC Auditor Reviewed Date', 'nc_rejected_by' => 'NC Rejected By', 'nc_rejected_date' => 'NC Rejected Date', 'rejection_reason' => 'Rejection Reason', 'action_category' => 'Action Category'
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
                if (!isset($val) || trim((string) $val) === '') {
                    $valStr = 'NA';
                } else {
                    $str = html_entity_decode(strip_tags((string)$val), ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $cleaned = str_replace(["\r\n", "\r", "\n", "\t"], " ", $str);
                    $valStr = preg_replace('/\s+/', ' ', trim($cleaned));
                }
                $csvRow[] = $valStr;
            }
            fputcsv($output, $csvRow);
        }
        fclose($output);
        exit;
    }

    public function get_dependent_dropdowns()
    {
        $req = service('request');
        $type = $req->getPost('type');
        $regions = $req->getPost('regions') ?? [];
        $site_categories = $req->getPost('site_categories') ?? [];

        if ($type === 'site_category') {
            $builder = $this->db->table('alert_gemba_sites')->select('DISTINCT(site_category)')->where('status', 1)->where('site_category !=', '')->orderBy('site_category', 'ASC');
            if (!empty($regions)) {
                $builder->whereIn('region', $regions);
            }
            apply_gemba_role_filters($builder, 'alert_gemba_sites');
            $data = $builder->get()->getResultArray();
            return $this->response->setJSON($data);
        }

        if ($type === 'site_name') {
            $builder = $this->db->table('alert_gemba_sites')->select('DISTINCT(site_name)')->where('status', 1)->where('site_name !=', '')->orderBy('site_name', 'ASC');
            if (!empty($regions)) {
                $builder->whereIn('region', $regions);
            }
            if (!empty($site_categories)) {
                $builder->whereIn('site_category', $site_categories);
            }
            apply_gemba_role_filters($builder, 'alert_gemba_sites');
            $data = $builder->get()->getResultArray();
            return $this->response->setJSON($data);
        }

        return $this->response->setJSON([]);
    }

    private function execute_gemba_only_nc_action($gembaSrNo, $action, $postData = [], $userId = null, $userName = null)
    {
        $db = db_connect();
        $now = date('Y-m-d H:i:s');
        $closeDate = $postData['close_date'] ?? null;
        $actionBy = !empty($userName) ? $userName : (isSuperAdmin() ? '0' : null);

        $current = $db->table('alert_gemba_audits')->where('gemba_sr_no', $gembaSrNo)->get()->getRowArray();
        if (!$current) return ['status' => 0, 'message' => 'Record not found'];

        if (isHigherAuthority()) return ['status' => 0, 'message' => 'Read only access'];

        $currentStatus = (int) $current['nc_status'];
        
        $isAssignedAM = false;
        if (isAccountManager() && !empty($current['account_manager']) && strtolower(trim($current['account_manager'])) === strtolower(trim($userName))) {
            $isAssignedAM = true;
        }

        $isAssignedCM = false;
        if (isClusterManager() && !empty($current['cluster_manager_spoc']) && strtolower(trim($current['cluster_manager_spoc'])) === strtolower(trim($userName))) {
            $isAssignedCM = true;
        }

        $isSuperAdmin = isSuperAdmin();
        $isAuditor = isAuditor();

        $gembaUpdate = [];

        if ($action === '0') {
            if ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM) {
                $gembaUpdate['nc_status'] = 0;
                $gembaUpdate['nc_rejected_by'] = $actionBy;
                $gembaUpdate['nc_rejected_date'] = $now;
                if (!empty($postData['rejection_reason'])) {
                    $gembaUpdate['rejection_reason'] = $postData['rejection_reason'];
                    $gembaUpdate['nc_remark'] = "Rejected Reason: " . $postData['rejection_reason'] . "\n" . ($current['nc_remark'] ?? '');
                    $gembaUpdate['qhse_remarks'] = 'Rejected Reason: ' . $postData['rejection_reason'];
                }
                $gembaUpdate['point_status'] = 'Open';
                $gembaUpdate['closure_status'] = 'Open';
                $gembaUpdate['closed_date'] = null;
            }
        } else if ($action === 'submit_for_review' || $action === 'save_wip') {
            if (isset($postData['nc_remark'])) {
                $gembaUpdate['nc_remark'] = $postData['nc_remark'];
                $gembaUpdate['working_remarks'] = $postData['nc_remark'];
            }
            if (isset($postData['nc_after_photo'])) {
                $gembaUpdate['nc_after_photo'] = $postData['nc_after_photo'];
                $gembaUpdate['working_uploaded_file'] = $postData['nc_after_photo'];
            }
            $gembaUpdate['working_date'] = $now;
            
            $gembaUpdate['point_status'] = 'WIP';
            
            if ($action === 'submit_for_review') {
                if ($isAuditor || $isSuperAdmin) {
                    $gembaUpdate['nc_status'] = 2;
                    $gembaUpdate['nc_auditor_reviewed_by'] = $actionBy;
                    $gembaUpdate['nc_auditor_reviewed_date'] = $now;
                } else {
                    $gembaUpdate['nc_status'] = 5;
                    $gembaUpdate['nc_cluster_reviewed_by'] = $actionBy;
                    $gembaUpdate['nc_cluster_reviewed_date'] = $now;
                }
            } else {
                // save_wip
                $gembaUpdate['nc_status'] = 1;
                $gembaUpdate['nc_worked_by'] = $actionBy;
            }
        } else {
            switch ($currentStatus) {
                case 0:
                case 6:
                    if ($action === 'working' && ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM)) {
                        $gembaUpdate['nc_status'] = 1;
                        $gembaUpdate['nc_worked_by'] = $actionBy;
                    }
                    break;
                case 1:
                    if ($action === 'review') {
                        if ($isSuperAdmin || $isAssignedCM || $isAssignedAM) {
                            $gembaUpdate['nc_status'] = 5;
                            $gembaUpdate['nc_cluster_reviewed_by'] = $actionBy;
                            $gembaUpdate['nc_cluster_reviewed_date'] = $now;
                        } elseif ($isAuditor) {
                            $gembaUpdate['nc_status'] = 2;
                            $gembaUpdate['nc_auditor_reviewed_by'] = $actionBy;
                            $gembaUpdate['nc_auditor_reviewed_date'] = $now;
                        }
                    }
                    break;
                case 5:
                    if ($action === 'auditor' && ($isSuperAdmin || $isAssignedCM || $isAssignedAM)) {
                        $gembaUpdate['nc_status'] = 2;
                        $gembaUpdate['nc_auditor_reviewed_by'] = $actionBy;
                        $gembaUpdate['nc_auditor_reviewed_date'] = $now;
                    }
                    if ($action === 'auditor_direct_close' && $isAuditor) {
                        $gembaUpdate['nc_status'] = 3;
                        $gembaUpdate['nc_cluster_reviewed_by'] = $actionBy;
                        $gembaUpdate['nc_cluster_reviewed_date'] = $now;
                        $gembaUpdate['nc_auditor_reviewed_by'] = $actionBy;
                        $gembaUpdate['nc_auditor_reviewed_date'] = $now;
                        $gembaUpdate['nc_closed_by'] = $actionBy;
                        $gembaUpdate['nc_closed_date'] = !empty($closeDate) ? $closeDate : $now;
                        // Wire closing remarks and proof through from postData
                        if (isset($postData['closed_remarks'])) {
                            $gembaUpdate['closed_remarks'] = $postData['closed_remarks'];
                        }
                        if (isset($postData['closed_uploaded_file'])) {
                            $gembaUpdate['closed_uploaded_file'] = $postData['closed_uploaded_file'];
                        }
                    }
                    break;
                case 2:
                    if ($action === 'closed' && ($isSuperAdmin || $isAuditor || $isAssignedCM || $isAssignedAM)) {
                        $gembaUpdate['nc_status'] = 3;
                        $gembaUpdate['nc_closed_by'] = $actionBy;
                        $gembaUpdate['nc_closed_date'] = !empty($closeDate) ? $closeDate : $now;
                        if (isset($postData['closed_remarks'])) {
                            $gembaUpdate['closed_remarks'] = $postData['closed_remarks'];
                        }
                        if (isset($postData['closed_uploaded_file'])) {
                            $gembaUpdate['closed_uploaded_file'] = $postData['closed_uploaded_file'];
                        }
                    }
                    break;
                case 3:
                    return ['status' => 0, 'message' => 'Already Closed'];
                case 4:
                    if ($action === 'working') {
                        $gembaUpdate['nc_status'] = 1;
                    }
                    break;
            }
        }

        if (empty($gembaUpdate) && ($action === 'save' || empty($action))) {
            if (isset($postData['nc_remark'])) $gembaUpdate['nc_remark'] = $postData['nc_remark'];
            if (isset($postData['nc_after_photo'])) $gembaUpdate['nc_after_photo'] = $postData['nc_after_photo'];
        }

        if (empty($gembaUpdate)) return ['status' => 0, 'message' => 'Unauthorized action or no changes'];

        if (isset($gembaUpdate['nc_status']) && (int)$gembaUpdate['nc_status'] === 3) {
            $closedTs = isset($gembaUpdate['nc_closed_date']) ? strtotime($gembaUpdate['nc_closed_date']) : strtotime($now);

            // closure_status carries the selected value (Closed / Hold-review with Client / Excluded)
            // point_status must ALWAYS be 'Closed' when nc_status = 3
            // point_category must ALWAYS be 'Closed Category' when nc_status = 3
            $closureStatus = $postData['closure_status'] ?? 'Closed';
            $gembaUpdate['point_status']   = 'Closed';           // always 'Closed'
            $gembaUpdate['point_category'] = 'Closed Category';  // always 'Closed Category'
            $gembaUpdate['closure_status'] = $closureStatus;     // selected value

            $gembaUpdate['closed_date']              = date('Y-m-d', $closedTs);
            $gembaUpdate['qhse_remarks']             = $gembaUpdate['nc_remark'] ?? ($current['nc_remark'] ?? 'Closed via Gemba NC Tracker');
            $gembaUpdate['updated_by']               = $actionBy;
            $gembaUpdate['update_date']              = $now;
            $gembaUpdate['weeknum_closed']           = date('W', $closedTs);
            $gembaUpdate['month_closed']             = date('Y-m-01', $closedTs);
            $gembaUpdate['year_month_closed']        = date('Y-m', $closedTs);
            $gembaUpdate['weeknum_yearmonth_closed'] = date('Y-m', $closedTs) . '-W' . date('W', $closedTs);

            if (!empty($current['audit_report_date'])) {
                $reportTs = strtotime($current['audit_report_date']);
                $days = floor(($closedTs - $reportTs) / (60 * 60 * 24));
                $gembaUpdate['ageing_days'] = $days >= 0 ? $days : 0;
                if ($gembaUpdate['ageing_days'] <= 30) $gembaUpdate['age_bracket'] = '<=30';
                elseif ($gembaUpdate['ageing_days'] <= 60) $gembaUpdate['age_bracket'] = '31-60';
                elseif ($gembaUpdate['ageing_days'] <= 90) $gembaUpdate['age_bracket'] = '61-90';
                else $gembaUpdate['age_bracket'] = '>90';
            }
        }

        $gembaUpdate['update_date'] = $now;

        $db->table('alert_gemba_audits')->where('gemba_sr_no', $gembaSrNo)->update($gembaUpdate);

        return [
            'status' => 1, 
            'message' => 'NC updated successfully', 
            'new_status' => $gembaUpdate['nc_status'] ?? $currentStatus, 
            'old_status' => $currentStatus, 
            'gemba_sr_no' => $gembaSrNo
        ];
    }
}
