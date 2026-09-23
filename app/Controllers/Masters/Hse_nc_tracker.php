<?php
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class Hse_nc_tracker extends BaseController
{
    use \App\Traits\ACLTrait; // Use the fully qualified name

    // Methods that are publicly accessible and don't require ACL checks
    protected $publicMethods = ['get_cluster', 'get_location', 'get_sub_category', 'update_nc_status', 'get_form_data', 'save_details'];

    /**
     * @var CRUDBaseModel
     */
    protected $BaseModel;

    public function __construct()
    {
        // changes on 16/10/25 by darsh: Using simple ACL helper without database changes
        // ACL: Load designation ACL helper - 12/11/25
        helper(["designation_acl", "hse_acl"]);
        $db = null;
        $db['table'] = 'alert_hse_audit_details';
        // changes on 13/11/25 by darsh: Add nc_worked_by and nc_closed_by_user to allowedFields
        $db['allowedFields'] = ['hse_audit_id', 'question_id', 'audit_template_id', 'site_category', 'sub_category', 'audit_category_id', 'audit_category', 'audit_question', 'finding', 'capa_json', 'remark', 'attachment', 'nc_status', 'nc_remark', 'nc_worked_by', 'nc_after_photo', 'nc_cluster_reviewed_by', 'nc_cluster_reviewed_date', 'nc_auditor_reviewed_by', 'nc_auditor_reviewed_date', 'nc_closed_by', 'nc_closed_date', 'nc_rejected_by', 'nc_closed_by_user'];
        $db['primaryKey'] = "id";
        $this->BaseModel = new CRUDBaseModel($db);
    }


    public function index()
    {
        ini_set('memory_limit', '2G');

        $db = db_connect();
        $req = service('request');

        $data = [];
        $tdata = [];

        $tdata['title'] = "";
        $tdata['button_id'] = "user_modal";

        helper('designation_acl');

        $selRegions = $this->cleanArrayValues($req->getGet('region'));
        $selClusters = $this->cleanArrayValues($req->getGet('cluster'));
        $selLocations = $this->cleanArrayValues($req->getGet('location'));
        $selSiteCategory = $this->cleanArrayValues($req->getGet('site_category'));
        $selSubCategory = $this->cleanArrayValues($req->getGet('sub_category'));

        // Cluster Manager restriction
        if (isClusterManager()) {
            $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());
            if (!empty($assignedClusters) && empty($selClusters)) {
                $selClusters = $assignedClusters;
            }
        }

        // Account Manager restriction
        if (isAccountManager()) {
            $assignedDetails = getAccountManagerAssignedDetailsHSE();

            if (!empty($assignedDetails)) {
                $amClients = [];
                $amRegions = [];
                $amClusters = [];

                foreach ($assignedDetails as $details) {
                    if (!empty($details['client_name'])) {
                        $amClients[] = $details['client_name'];
                    }
                    if (!empty($details['region'])) {
                        $amRegions[] = $details['region'];
                    }
                    if (!empty($details['cluster'])) {
                        $amClusters[] = $details['cluster'];
                    }
                }

                if (empty($selRegions))
                    $selRegions = array_unique($amRegions);
                if (empty($selClusters))
                    $selClusters = array_unique($amClusters);
                if (empty($selLocations))
                    $selLocations = array_unique($amClients);
            }
        }
        if (isAccountManager() || isWHManager()) {
            $assignedDetails = getAccountManagerAssignedDetailsHSE();

            $data['assigned_regions'] = array_unique(array_filter(array_column($assignedDetails, 'region')));
            $data['assigned_clusters'] = array_unique(array_filter(array_column($assignedDetails, 'cluster')));
            $data['assigned_locations'] = array_unique(array_filter(array_column($assignedDetails, 'client_name')));
        }
        $selNcType = $req->getGet('nc_type') ?? '';

        $qs = http_build_query([
            'region' => $selRegions,
            'cluster' => $selClusters,
            'location' => $selLocations,
            'site_category' => $selSiteCategory,
            'sub_category' => $selSubCategory,
            'nc_type' => $selNcType,
            'detail_id' => $req->getGet('detail_id')
        ]);

        $tdata['display_contents'] = [
            'bulk_select' => '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" id="selectAllCheckbox" /></div>',
            'id' => 'Id',
            'action' => 'Action',
            'audit_no' => 'Audit No',
            'audit_name' => 'Audit Name',
            'auditor_name' => 'Auditor',
            'auditee_name' => 'Auditee',
            'client_name' => 'Client',
            'hse_type' => 'Account Type',
            'audit_date' => 'Audit Date',
            'region' => 'Region',
            'location' => 'Location',
            'score' => 'Score',
            'main_category' => 'Site Category',
            'sub_category' => 'Sub Category',
            'cluster_name' => 'Cluster',
            'account_manager' => 'Account Manager',
            'audit_category' => 'Question Name',
            'audit_question' => 'Audit Question',
            'finding' => 'Finding',
            'remark' => 'Remark',
            'attachment' => 'Attachment',
            'nc_worked_by' => 'Worked By',
            'nc_worked_date' => 'Worked Date',
            'nc_cluster_reviewed_by' => 'Cluster Reviewed By',
            'nc_cluster_reviewed_date' => 'Cluster Reviewed Date',
            'nc_auditor_reviewed_by' => 'Auditor Reviewed By',
            'nc_auditor_reviewed_date' => 'Auditor Reviewed Date',
            'nc_closed_by' => 'Closed By',
            'nc_closed_date' => 'Closed Date',
            'nc_rejected_by' => 'Rejected By',
            'nc_type' => 'NC Type',
            'nc_remark' => 'NC Remark',
            'nc_after_photo' => 'After Photo'
        ];

        $data['ajax_url'] = base_url("Masters/Hse_nc_tracker/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Hse_nc_tracker/table_ajax") . ($qs ? ('?' . $qs) : '');
        $tdata['export_button'] = '<button type="button" class="btn btn-sm btn-primary ms-2" id="bulkCloseBtn" disabled onclick="openBulkCloseModal()">Close Selected NCs</button>';

        // Status Count Query
        $params = [];
        $whereSql = $this->buildWhere($params, $req);

        $sql = "
        SELECT d.nc_status, COUNT(*) AS total
        FROM alert_hse_audit_details d

        INNER JOIN alert_hse_audit_master m 
            ON m.hse_audit_id = d.hse_audit_id

        INNER JOIN (
        SELECT 
            latest_master.audit_no,
            d2.question_id,
            MAX(d2.id) AS latest_detail_id
        FROM alert_hse_audit_details d2
        INNER JOIN alert_hse_audit_master latest_master
            ON latest_master.hse_audit_id = d2.hse_audit_id
        INNER JOIN (
            SELECT 
                audit_no,
                MAX(hse_audit_id) AS latest_hse_audit_id
            FROM alert_hse_audit_master
            GROUP BY audit_no
        ) latest_audit
            ON latest_audit.latest_hse_audit_id = latest_master.hse_audit_id
        GROUP BY latest_master.audit_no, d2.question_id
    ) latest_nc 
        ON latest_nc.latest_detail_id = d.id

        WHERE $whereSql
        GROUP BY d.nc_status
    ";

        $result = $db->query($sql, $params)->getResultArray();

        $statusCounts = [];
        foreach ($result as $r) {
            $statusCounts[$r['nc_status']] = $r['total'];
        }

        $open = $statusCounts[0] ?? 0;
        $working = $statusCounts[1] ?? 0;
        $reviewAuditor = $statusCounts[2] ?? 0;
        $reviewCluster = $statusCounts[5] ?? 0;
        $closed = $statusCounts[3] ?? 0;

        $datatop = $this->getStatusCardsHtml($open, $working, $reviewCluster, $reviewAuditor, $closed, $qs, null);

        // Regions
        $regionBuilder = $db->table('alert_hse_client_master')
            ->select('DISTINCT(region) region_name')
            ->where('status', 1);

        if (isClusterManager()) {
            $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());
            if (!empty($assignedClusters))
                $regionBuilder->whereIn('cluster', $assignedClusters);
        }

        if (isAccountManager()) {
            $assignedDetails = getAccountManagerAssignedDetailsHSE();
            $assignedRegions = array_unique(array_filter(array_column($assignedDetails, 'region')));
            if (!empty($assignedRegions))
                $regionBuilder->whereIn('region', $assignedRegions);
        }

        $regions = $regionBuilder->get()->getResultArray();

        // Clusters
        $clusterBuilder = $db->table('alert_hse_client_master')
            ->select('DISTINCT(cluster) cluster_name')
            ->where('status', 1);

        if (isClusterManager()) {
            $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());
            if (!empty($assignedClusters))
                $clusterBuilder->whereIn('cluster', $assignedClusters);
        }

        if (isAccountManager()) {
            $assignedDetails = getAccountManagerAssignedDetailsHSE();
            $assignedClusters = array_unique(array_filter(array_column($assignedDetails, 'cluster')));
            if (!empty($assignedClusters))
                $clusterBuilder->whereIn('cluster', $assignedClusters);
        }

        $clusters = $clusterBuilder->get()->getResultArray();

        // Locations
        $locationBuilder = $db->table('alert_hse_client_master')
            ->select('DISTINCT(client_name) location_name')
            ->where('status', 1);

        if (isClusterManager()) {
            $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());
            if (!empty($assignedClusters))
                $locationBuilder->whereIn('cluster', $assignedClusters);
        }

        if (isAccountManager()) {
            $assignedDetails = getAccountManagerAssignedDetailsHSE();
            $assignedClients = array_unique(array_filter(array_column($assignedDetails, 'client_name')));
            if (!empty($assignedClients))
                $locationBuilder->whereIn('client_name', $assignedClients);
        }

        $locations = $locationBuilder->get()->getResultArray();

        $siteCategories = $db->query("SELECT DISTINCT site_category FROM alert_gemba_sites WHERE status = 1 AND site_category IS NOT NULL AND site_category <> '' ORDER BY site_category ASC")->getResultArray();
        $subCategories = $db->table('alert_hse_sub_category')->where('status', 1)->get()->getResultArray();

        $data['is_cluster_manager'] = isClusterManager();
        $data['is_account_manager'] = isAccountManager();

        $data['table'] = view('Master/hse_nc_filter', [
            'req' => $req,
            'regions' => $regions,
            'clusters' => $clusters,
            'locations' => $locations,
            'siteCategories' => $siteCategories,
            'subCategories' => $subCategories,
            'is_cluster_manager' => $data['is_cluster_manager'],
            'is_account_manager' => $data['is_account_manager']
        ]);

        $data['table'] .= $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);

        return view("Master/hse_nc_tracker", $data);
    }

    public function template_type_filter($nc_status)
    {
        ini_set('memory_limit', '2G');

        $db = db_connect();
        $req = service('request');

        $data = [];
        $tdata = [];

        $tdata['title'] = "";
        $tdata['button_id'] = "user_modal";

        helper('designation_acl');

        $selRegions = $this->cleanArrayValues($req->getGet('region'));
        $selClusters = $this->cleanArrayValues($req->getGet('cluster'));
        $selLocations = $this->cleanArrayValues($req->getGet('location'));
        $selSiteCategory = $this->cleanArrayValues($req->getGet('site_category'));
        $selSubCategory = $this->cleanArrayValues($req->getGet('sub_category'));
        $selNcType = $req->getGet('nc_type') ?? '';
        // Cluster Manager restriction
        if (isClusterManager()) {
            $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());
            if (!empty($assignedClusters) && empty($selClusters)) {
                $selClusters = $assignedClusters;
            }
        }

        // Account Manager restriction
        if (isAccountManager()) {
            $assignedDetails = getAccountManagerAssignedDetailsHSE();

            if (!empty($assignedDetails)) {
                $amClients = [];
                $amRegions = [];
                $amClusters = [];

                foreach ($assignedDetails as $details) {
                    if (!empty($details['client_name'])) {
                        $amClients[] = $details['client_name'];
                    }
                    if (!empty($details['region'])) {
                        $amRegions[] = $details['region'];
                    }
                    if (!empty($details['cluster'])) {
                        $amClusters[] = $details['cluster'];
                    }
                }

                if (empty($selRegions))
                    $selRegions = array_unique($amRegions);
                if (empty($selClusters))
                    $selClusters = array_unique($amClusters);
                if (empty($selLocations))
                    $selLocations = array_unique($amClients);
            }
        }

        $qs = http_build_query([
            'region' => $selRegions,
            'cluster' => $selClusters,
            'location' => $selLocations,
            'site_category' => $selSiteCategory,
            'sub_category' => $selSubCategory,
            'nc_type' => $selNcType,
            'detail_id' => $req->getGet('detail_id')
        ]);

        $tdata['display_contents'] = [
            'id' => 'Id',
            'action' => 'Action',
            'audit_no' => 'Audit No',
            'audit_name' => 'Audit Name',
            'auditor_name' => 'Auditor',
            'auditee_name' => 'Auditee',
            'client_name' => 'Client',
            'hse_type' => 'Account Type',
            'audit_date' => 'Audit Date',
            'region' => 'Region',
            'location' => 'Location',
            'score' => 'Score',
            'main_category' => 'Site Category',
            'sub_category' => 'Sub Category',
            'cluster_name' => 'Cluster',
            'account_manager' => 'Account Manager',
            'audit_category' => 'Question',
            'audit_question' => 'Audit Question',
            'finding' => 'Finding',
            'remark' => 'Remark',
            'attachment' => 'Attachment',
            'nc_worked_by' => 'Worked By',
            'nc_worked_date' => 'Worked Date',
            'nc_cluster_reviewed_by' => 'Cluster Reviewed By',
            'nc_cluster_reviewed_date' => 'Cluster Reviewed Date',
            'nc_auditor_reviewed_by' => 'Auditor Reviewed By',
            'nc_auditor_reviewed_date' => 'Auditor Reviewed Date',
            'nc_closed_by' => 'Closed By',
            'nc_closed_date' => 'Closed Date',
            'nc_rejected_by' => 'Rejected By',
            'nc_remark' => 'NC Remark',
            'nc_after_photo' => 'After Proof'
        ];

        $data['ajax_url'] = base_url("Masters/Hse_nc_tracker/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/Hse_nc_tracker/table_ajax/" . $nc_status) . ($qs ? "?" . $qs : '');

        $params = [];
        $whereSql = $this->buildWhere($params, $req);

        $sql = "
            SELECT d.nc_status, COUNT(*) AS total
            FROM alert_hse_audit_details d
        
            INNER JOIN alert_hse_audit_master m 
                ON m.hse_audit_id = d.hse_audit_id
        
            WHERE $whereSql
            GROUP BY d.nc_status
        ";

        $result = $db->query($sql, $params)->getResultArray();

        $statusCounts = [];
        foreach ($result as $r) {
            $statusCounts[$r['nc_status']] = $r['total'];
        }

        $open = $statusCounts[0] ?? 0;
        $working = $statusCounts[1] ?? 0;
        $reviewAuditor = $statusCounts[2] ?? 0;
        $reviewCluster = $statusCounts[5] ?? 0;
        $closed = $statusCounts[3] ?? 0;

        $datatop = $this->getStatusCardsHtml($open, $working, $reviewCluster, $reviewAuditor, $closed, $qs, $nc_status);

        $regions = $db->table('alert_hse_client_master')->select('DISTINCT(region) region_name')->where('status', 1)->get()->getResultArray();
        $clusters = $db->table('alert_hse_client_master')->select('DISTINCT(cluster) cluster_name')->where('status', 1)->get()->getResultArray();
        $locations = $db->table('alert_hse_client_master')->select('DISTINCT(client_name) location_name')->where('status', 1)->get()->getResultArray();
        $siteCategories = $db->query("SELECT DISTINCT site_category FROM alert_gemba_sites WHERE status = 1 AND site_category IS NOT NULL AND site_category <> '' ORDER BY site_category ASC")->getResultArray();
        $subCategories = $db->table('alert_hse_sub_category')->where('status', 1)->get()->getResultArray();

        $data['is_cluster_manager'] = isClusterManager();
        $data['is_account_manager'] = isAccountManager();

        $data['table'] = view('Master/hse_nc_filter', [
            'req' => $req,
            'regions' => $regions,
            'clusters' => $clusters,
            'locations' => $locations,
            'siteCategories' => $siteCategories,
            'subCategories' => $subCategories,
            'is_cluster_manager' => $data['is_cluster_manager'],
            'is_account_manager' => $data['is_account_manager']
        ]);

        $data['table'] .= $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);

        return view("Master/hse_nc_tracker", $data);
    }


    public function get_filter_data()
    {
        $db = db_connect();
        $req = service('request');

        $selectedRegions = $this->cleanArrayValues($req->getGet('region'));
        $selectedClusters = $this->cleanArrayValues($req->getGet('cluster'));
        $selectedLocations = $this->cleanArrayValues($req->getGet('location'));

        $regionBuilder = $db->table('alert_hse_client_master')
            ->select('DISTINCT(region) region_name')
            ->where('status', 1);

        $clusterBuilder = $db->table('alert_hse_client_master')
            ->select('DISTINCT(cluster) cluster_name')
            ->where('status', 1);

        $locationBuilder = $db->table('alert_hse_client_master')
            ->select('DISTINCT(client_name) location_name')
            ->where('status', 1);

        if (!empty($selectedRegions)) {
            $clusterBuilder->whereIn('region', $selectedRegions);
            $locationBuilder->whereIn('region', $selectedRegions);
        }

        if (!empty($selectedClusters)) {
            $regionBuilder->whereIn('cluster', $selectedClusters);
            $locationBuilder->whereIn('cluster', $selectedClusters);
        }

        if (!empty($selectedLocations)) {
            $regionBuilder->whereIn('client_name', $selectedLocations);
            $clusterBuilder->whereIn('client_name', $selectedLocations);
        }

        if (isClusterManager()) {
            $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());
            if (!empty($assignedClusters)) {
                $regionBuilder->whereIn('cluster', $assignedClusters);
                $clusterBuilder->whereIn('cluster', $assignedClusters);
                $locationBuilder->whereIn('cluster', $assignedClusters);
            } else {
                $regionBuilder->where('1=0');
                $clusterBuilder->where('1=0');
                $locationBuilder->where('1=0');
            }
        }

        if (isAccountManager()) {
            $assignedClients = $this->cleanArrayValues(getAccountManagerAssignedClientsHSE());
            if (!empty($assignedClients)) {
                $regionBuilder->whereIn('client_name', $assignedClients);
                $clusterBuilder->whereIn('client_name', $assignedClients);
                $locationBuilder->whereIn('client_name', $assignedClients);
            } else {
                $regionBuilder->where('1=0');
                $clusterBuilder->where('1=0');
                $locationBuilder->where('1=0');
            }
        }

        return $this->response->setJSON([
            'regions' => $regionBuilder->orderBy('region', 'ASC')->get()->getResultArray(),
            'clusters' => $clusterBuilder->orderBy('cluster', 'ASC')->get()->getResultArray(),
            'locations' => $locationBuilder->orderBy('client_name', 'ASC')->get()->getResultArray()
        ]);
    }

    private function cleanArrayValues($arr)
    {
        if (!is_array($arr)) {
            $arr = [$arr];
        }

        $arr = array_map(function ($v) {
            return trim((string) $v);
        }, $arr);

        $arr = array_filter($arr, function ($v) {
            return $v !== '';
        });

        return array_values(array_unique($arr));
    }
    public function table_ajax($nc_status = null)
    {
        $db = db_connect();
        $req = service('request');

        $params = [];

        $sql = "
                SELECT 
                    d.id,
                    d.hse_audit_id,
                    d.question_id,
                    d.audit_template_id,
                    d.site_category,
                    d.sub_category,
                    d.audit_category_id,
                    d.audit_category,
                    d.audit_question,
                    d.finding,
                    d.nc_type,
                    d.capa_json,
                    d.remark,
                    d.attachment,
                    d.nc_status,
                    d.nc_remark,
                    d.nc_worked_by,
                    d.nc_after_photo,
                    d.nc_cluster_reviewed_by,
                    d.nc_cluster_reviewed_date,
                    d.nc_auditor_reviewed_by,
                    d.nc_auditor_reviewed_date,
                    d.nc_closed_by,
                    d.nc_closed_date,
                    d.nc_rejected_by,
                    d.nc_closed_by_user,
                    d.status,
                    d.default_date,
                    d.update_date,

                    m.audit_no,
                    m.audit_name,
                    m.auditor_name,
                    m.auditee_name,
                    m.client_name,
                    m.audit_date,
                    m.template_date,
                    cm.region,
                    m.location,
                    m.score,
                    m.perform_audit_by,
                    m.main_category,
                    m.sub_category AS master_sub_category,
                    cm.cluster AS cluster_name,
                    cm.account_manager,
                    worked_user.user_name AS nc_worked_by_name,
                    closed_user.user_name AS nc_closed_by_user_name,
                    auditor_user.user_name AS nc_auditor_reviewed_by_name,
                    cluster_user.user_name AS nc_cluster_reviewed_by_name,
                    rejected_user.user_name AS nc_rejected_by_name

                FROM alert_hse_audit_details d

                INNER JOIN alert_hse_audit_master m 
                    ON m.hse_audit_id = d.hse_audit_id

                /* ✅ Removed complex duplicate latest_nc join in favor of buildWhere subquery */

                /* ✅ FIX CLIENT DUPLICATE */
                LEFT JOIN (
                    SELECT 
                        client_name,
                        MAX(region) AS region,
                        MAX(cluster) AS cluster,
                        MAX(account_manager) AS account_manager
                    FROM alert_hse_client_master
                    WHERE status = 1
                    GROUP BY client_name
                ) cm ON cm.client_name = m.client_name

                LEFT JOIN alert_users worked_user 
                    ON worked_user.user_id = d.nc_worked_by

                LEFT JOIN alert_users closed_user 
                    ON closed_user.user_id = d.nc_closed_by_user

                LEFT JOIN alert_users auditor_user 
                    ON auditor_user.user_id = d.nc_auditor_reviewed_by

                LEFT JOIN alert_users cluster_user 
                    ON cluster_user.user_id = d.nc_cluster_reviewed_by
                LEFT JOIN alert_users rejected_user 
                    ON rejected_user.user_id = d.nc_rejected_by
                WHERE 1=1
            ";

        $whereSql = $this->buildWhere($params, $req);
        $sql .= " AND $whereSql";

        if ($nc_status !== null && $nc_status !== '') {
            $sql .= " AND d.nc_status = ?";
            $params[] = $nc_status;
        }

        $sql .= " ORDER BY d.id DESC";

        $query = $db->query($sql, $params)->getResultArray();

        $statusLabels = [
            0 => '<span class="badge badge-danger">Open</span>',
            1 => '<span class="badge badge-warning">Working</span>',
            2 => '<span class="badge badge-info">Auditor Review</span>',
            3 => '<span class="badge badge-success">Closed</span>',
            4 => '<span class="badge badge-secondary">Draft</span>',
            5 => '<span class="badge badge-dark">Cluster Review</span>',
        ];

        $data = [];

        foreach ($query as $row) {
            $attachment_html = "-";
            if (!empty($row['attachment'])) {
                $attachmentUrl = base_url($row['attachment']);
                $attachmentExt = strtolower(pathinfo($row['attachment'], PATHINFO_EXTENSION));
                if (in_array($attachmentExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $attachment_html = "<a href='" . $attachmentUrl . "' target='_blank' class='btn btn-sm btn-success' title='View file: " . basename($row['attachment']) . "'><img src='" . $attachmentUrl . "' height='50' width='50' onerror=\"this.src='" . env('defaultLogo') . "'\" /></a>";
                } else {
                    $attachment_html = "<a href='" . $attachmentUrl . "' target='_blank' class='btn btn-sm btn-success' title='Download file: " . basename($row['attachment']) . "'>Download File</a>";
                }
            }

            $after_photo = "-";
            if (!empty($row['nc_after_photo'])) {
                $afterUrl = base_url($row['nc_after_photo']);
                $afterExt = strtolower(pathinfo($row['nc_after_photo'], PATHINFO_EXTENSION));
                if (in_array($afterExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $after_photo = "<a href='" . $afterUrl . "' target='_blank' class='btn btn-sm btn-success' title='View file: " . basename($row['nc_after_photo']) . "'><img src='" . $afterUrl . "' height='50' width='50' onerror=\"this.src='" . env('defaultLogo') . "'\" /></a>";
                } else {
                    $after_photo = "<a href='" . $afterUrl . "' target='_blank' class='btn btn-sm btn-success' title='Download file: " . basename($row['nc_after_photo']) . "'>Download File</a>";
                }
            }

            $hseTypeLabel = !empty($row['site_category'])
                ? '<span class="badge badge-primary">' . htmlspecialchars($row['site_category']) . '</span>'
                : "-";

            $status = (int) $row['nc_status'];
            $active = "";
            $edit = "";

            $isSuperAdmin = isSuperAdmin();
            $isAuditor = isAuditor();
            $isCluster = isClusterManager();
            $isAccount = isAccountManager();
            $isHigher = isHigherAuthority();

            $userName = getUserName();

            /* 🔒 Check if current user is the assigned Account Manager for this record */
            $isAssignedAM = false;
            if ($isAccount && !empty($row['account_manager']) && strtolower(trim($row['account_manager'])) === strtolower(trim($userName))) {
                $isAssignedAM = true;
            }

            /* 🔒 Check if current user is the assigned Cluster Manager for this record */
            $isAssignedCM = false;
            if ($isCluster) {
                $assignedClusters = getClusterManagerAssignedClusterHSE();
                if (in_array($row['cluster_name'], $assignedClusters)) {
                    $isAssignedCM = true;
                }
            }

            if ($isHigher) {
                $action = "<center>" . ($statusLabels[$status] ?? '-') . "</center>";
            } else {
                switch ($status) {
                    case 0:
                        if ($isSuperAdmin || $isAuditor || $isAssignedAM || $isAssignedCM) {
                            $siteCategory = addslashes($row['site_category'] ?? '');
                            $active .= '<button class="btn btn-success btn-sm mt-2" title="Start Working"
                                        onclick="updateNcStatus(' . $row['id'] . ',\'working\')"><i class="fa fa-play"></i></button>';
                        }
                        break;

                    case 1:
                        if ($isSuperAdmin || $isAuditor || $isAssignedAM || $isAssignedCM) {
                            $active .= '<button class="btn btn-info btn-sm mt-2" title="Working - Update Details" onclick="edit_id(this,' . $row['id'] . ')" data-ajax-url="' . base_url("Masters/Hse_nc_tracker/get_form_data/" . $row['id']) . '"><i class="fa fa-pencil-alt"></i></button>';

                            $active .= '<button class="btn btn-danger btn-sm mt-2 ms-1" title="Reject to Open"
                                        onclick="updateNcStatus(' . $row['id'] . ',\'0\')"><i class="fa fa-times"></i></button>';
                        }
                        break;

                    case 4: // Draft status - allow editing
                        if ($isSuperAdmin || $isAuditor || $isAssignedAM || $isAssignedCM) {
                            $edit .= '<button class="btn btn-primary btn-sm mt-2" onclick="edit_id(this,' . $row['id'] . ')" data-ajax-url="' . base_url("Masters/Hse_nc_tracker/get_form_data/" . $row['id']) . '"><i class="fa fa-edit"></i></button>';
                        }
                        break;

                    case 5:
                        if ($isSuperAdmin || $isAssignedCM) {
                            // Cluster / AM / Admin → Send to Auditor Review
                            $active .= '<button class="btn btn-success btn-sm me-1 mt-2" title="Approve to Auditor Review" onclick="updateNcStatus(' . $row['id'] . ',\'auditor\')"><i class="fa fa-check"></i></button>';
                            $active .= '<button class="btn btn-warning btn-sm me-1 mt-2" title="Send Back to Open" onclick="updateNcStatus(' . $row['id'] . ',\'0\')"><i class="fa fa-undo"></i></button>';
                        } elseif ($isAuditor) {
                            // Auditor → Direct Close
                            $active .= '<button class="btn btn-success btn-sm me-1 mt-2" title="Accept & Close" onclick="openCloseModal(' . $row['id'] . ', \'' . htmlspecialchars($row['audit_date'] ?? '', ENT_QUOTES) . '\', \'auditor_direct_close\')"><i class="fa fa-check-double"></i></button>';
                            $active .= '<button class="btn btn-danger btn-sm me-1 mt-2" title="Reject to Open" onclick="updateNcStatus(' . $row['id'] . ',\'0\')"><i class="fa fa-times"></i></button>';
                        }
                        break;

                    case 2:
                        if ($isSuperAdmin || $isAuditor) {
                            $active .= '<button class="btn btn-success btn-sm me-1 mt-2" title="Close NC" onclick="openCloseModal(' . $row['id'] . ', \'' . htmlspecialchars($row['audit_date'] ?? '', ENT_QUOTES) . '\', \'closed\')"><i class="fa fa-check-double"></i></button>';
                            $active .= '<button class="btn btn-danger btn-sm mt-2" title="Reject to Open" onclick="updateNcStatus(' . $row['id'] . ',\'0\')"><i class="fa fa-times"></i></button>';
                        }
                        break;
                }

                $historyBtn = '<button class="btn btn-info btn-sm mt-2 ms-1" title="View Action History" onclick="showNcActionHistory(' . $row['id'] . ', \'HSE\', \'' . htmlspecialchars($row['client_name'] ?? '', ENT_QUOTES) . '\')"><i class="fa fa-history"></i></button>';
                $action = "<center>" . ($statusLabels[$status] ?? '-') . "<br>" . $active . $edit . $historyBtn . "</center>";
            }

            $disabled = ($status === 2) ? '' : 'disabled';
            $bulk_select = '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input row-checkbox" type="checkbox" value="' . $row['id'] . '" data-status="' . $status . '" ' . $disabled . ' /></div>';

            $data[] = [
                'bulk_select' => $bulk_select,
                'id' => $row['id'],
                'audit_no' => $row['audit_no'],
                'audit_name' => $row['audit_name'],
                'auditor_name' => $row['auditor_name'],
                'auditee_name' => $row['auditee_name'],
                'client_name' => $row['client_name'],
                'hse_type' => $hseTypeLabel,
                'audit_date' => $row['audit_date'],
                'region' => $row['region'],
                'location' => $row['location'],
                'score' => $row['score'],
                'main_category' => $row['main_category'],
                'sub_category' => $row['master_sub_category'],
                'cluster_name' => $row['cluster_name'],
                'account_manager' => $row['account_manager'],
                'audit_category' => $row['audit_category'],
                'audit_question' => $row['audit_question'],
                'finding' => $row['finding'],
                'remark' => $row['remark'],
                'attachment' => $attachment_html,
                'nc_type' => $row['nc_type'],
                'nc_worked_by' => $this->formatUserDisplay($row['nc_worked_by'], $row['nc_worked_by_name']),
                'nc_worked_date' => !is_null($row['nc_worked_by']) && $row['nc_worked_by'] !== ''
                    ? date('d-m-Y H:i', strtotime($row['update_date'] ?? $row['default_date'] ?? ''))
                    : '-',

                'nc_cluster_reviewed_by' => $this->formatUserDisplay($row['nc_cluster_reviewed_by'], $row['nc_cluster_reviewed_by_name']),
                'nc_cluster_reviewed_date' => !empty($row['nc_cluster_reviewed_date'])
                    ? date('d-m-Y H:i', strtotime($row['nc_cluster_reviewed_date']))
                    : '-',

                'nc_auditor_reviewed_by' => $this->formatUserDisplay($row['nc_auditor_reviewed_by'], $row['nc_auditor_reviewed_by_name']),
                'nc_auditor_reviewed_date' => !empty($row['nc_auditor_reviewed_date'])
                    ? date('d-m-Y H:i', strtotime($row['nc_auditor_reviewed_date']))
                    : '-',

                'nc_closed_by' => $this->formatUserDisplay($row['nc_closed_by_user'], $row['nc_closed_by_user_name']),
                'nc_closed_date' => !empty($row['nc_closed_date'])
                    ? date('d-m-Y H:i', strtotime($row['nc_closed_date']))
                    : '-',

                'nc_rejected_by' => $this->formatUserDisplay($row['nc_rejected_by'], $row['nc_rejected_by_name']),
                'nc_remark' => $row['nc_remark'],
                'nc_after_photo' => $after_photo,
                'action' => $action
            ];
        }

        return $this->response->setJSON(["data" => $data]);
    }

    private function formatUserDisplay($id, $name = null)
    {
        if ((string) $id === '0') {
            return 'Super Admin';
        }

        if (!empty($name)) {
            return $name;
        }

        if (!empty($id)) {
            return 'User ID: ' . $id;
        }

        return '-';
    }
    private function applyFilter(&$sql, &$params, $column, $value)
    {
        if (!empty($value)) {
            if (!is_array($value))
                $value = [$value];

            $placeholders = implode(',', array_fill(0, count($value), '?'));

            $sql .= " AND $column IN ($placeholders)";

            $params = array_merge($params, $value);
        }
    }
    private function buildWhere(&$params, $req)
    {
        $where = "d.finding = 'NO'";
        $where .= " AND d.hse_audit_id IN (SELECT MAX(hse_audit_id) FROM alert_hse_audit_master GROUP BY audit_no)";

        $filters = [
            'm.sub_category' => $this->cleanArrayValues($req->getGet('sub_category')),
        ];

        foreach ($filters as $column => $value) {
            if (!empty($value)) {
                $placeholders = implode(',', array_fill(0, count($value), '?'));
                $where .= " AND $column IN ($placeholders)";
                $params = array_merge($params, $value);
            }
        }

        $selRegion = $this->cleanArrayValues($req->getGet('region'));
        $selCluster = $this->cleanArrayValues($req->getGet('cluster'));
        $selLocation = $this->cleanArrayValues($req->getGet('location'));

        $db = db_connect();

        if (!empty($selRegion)) {
            $placeholders = implode(',', array_fill(0, count($selRegion), '?'));
            $where .= " AND EXISTS (
                SELECT 1 FROM alert_hse_client_master cm
                WHERE cm.client_name = m.client_name
                AND cm.region IN ($placeholders)
                AND cm.status = 1
            )";
            $params = array_merge($params, $selRegion);
        }

        if (!empty($selCluster)) {
            $placeholders = implode(',', array_fill(0, count($selCluster), '?'));
            $where .= " AND EXISTS (
                SELECT 1 FROM alert_hse_client_master cm
                WHERE cm.client_name = m.client_name
                AND cm.cluster IN ($placeholders)
                AND cm.status = 1
            )";
            $params = array_merge($params, $selCluster);
        }

        if (!empty($selLocation)) {
            $placeholders = implode(',', array_fill(0, count($selLocation), '?'));
            $where .= " AND m.client_name IN ($placeholders)";
            $params = array_merge($params, $selLocation);
        }

        $selSiteCategory = $this->cleanArrayValues($req->getGet('site_category'));
        if (!empty($selSiteCategory)) {
            $placeholders = implode(',', array_fill(0, count($selSiteCategory), '?'));
            $where .= " AND EXISTS (
                SELECT 1 FROM alert_gemba_sites gs
                WHERE gs.site_name = m.client_name
                AND gs.site_category IN ($placeholders)
                AND gs.status = 1
            )";
            $params = array_merge($params, $selSiteCategory);
        }

        $ncType = trim((string) $req->getGet('nc_type'));
        if (!empty($ncType) && in_array($ncType, ['NC', 'RD'], true)) {
            $where .= " AND d.nc_type = ?";
            $params[] = $ncType;
        }

        $detailId = $req->getGet('detail_id');
        if (!empty($detailId)) {
            $where .= " AND d.id = ?";
            $params[] = $detailId;
        }

        // ACL logic
        helper('hse_acl');
        $where .= getClusterFilterByClientForHSE('m.client_name');

        return $where;
    }

    public function get_form_data($id)
    {
        $response = [
            'status' => "0",
            'message' => "Details not found"
        ];

        if (isset($id)) {
            $db = db_connect();

            $query = $db->table('alert_hse_audit_details')
                ->select('alert_hse_audit_details.*, alert_hse_audit_master.audit_no, alert_hse_audit_master.audit_name, 
                            alert_hse_audit_master.auditor_name, alert_hse_audit_master.auditee_name, 
                            alert_hse_audit_master.client_name, alert_hse_audit_master.audit_date, 
                            alert_hse_audit_master.template_date, alert_hse_audit_master.region, 
                            alert_hse_audit_master.location, alert_hse_audit_master.score, 
                            alert_hse_audit_master.perform_audit_by,
                            alert_hse_audit_master.main_category,
                            alert_hse_audit_master.sub_category,
                            alert_hse_audit_master.auditor_name')
                ->join('alert_hse_audit_master', 'alert_hse_audit_master.hse_audit_id = alert_hse_audit_details.hse_audit_id', 'left')
                ->where('alert_hse_audit_details.id', $id)
                ->get();
            if ($query->getNumRows() > 0) {
                $response['data'] = $query->getRowArray();
                $beforePath = $response['data']['attachment'] ?? '';
                if (!empty($beforePath)) {
                    if (preg_match('/(uploads\/.+)$/', $beforePath, $m)) {
                        $beforePath = $m[1];
                    } elseif (preg_match('/(writable\/.+)$/', $beforePath, $m)) {
                        $beforePath = $m[1];
                    }
                }
                if (empty($beforePath) || strpos($beforePath, 'uploads/') !== 0 || !is_file(FCPATH . $beforePath)) {
                    $auditNo = $response['data']['audit_no'] ?? '';
                    if (!empty($auditNo)) {
                        $folder = 'uploads/audit_files/' . $auditNo . '/';
                        $base = basename((string) ($response['data']['attachment'] ?? ''));
                        if (!empty($base) && $base !== '.' && $base !== '..') {
                            $candidate = $folder . $base;
                            if (is_file(FCPATH . $candidate)) {
                                $beforePath = $candidate;
                            }
                        }
                        if (empty($beforePath) || !is_file(FCPATH . $beforePath)) {
                            $matches = glob(FCPATH . $folder . '*');
                            if ($matches) {
                                usort($matches, function ($a, $b) {
                                    return filemtime($b) <=> filemtime($a);
                                });
                                $picked = null;
                                foreach ($matches as $mfile) {
                                    $ext = strtolower(pathinfo($mfile, PATHINFO_EXTENSION));
                                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                                        $picked = $mfile;
                                        break;
                                    }
                                }
                                if (!$picked) {
                                    $picked = $matches[0];
                                }
                                $rel = str_replace(FCPATH, '', $picked);
                                $rel = str_replace('\\', '/', $rel);
                                $beforePath = $rel;
                            }
                        }
                    }
                }
                if (!empty($beforePath)) {
                    $beforePath = str_replace('\\', '/', $beforePath);
                    $beforePath = trim($beforePath, '/');
                    $response['data']['before_photo_url'] = base_url($beforePath);
                } else {
                    $response['data']['before_photo_url'] = '';
                }

                $response['status'] = "1";
                $response['message'] = "Details found";
            } else {
                log_message('error', 'No results found for ID: ' . $id);
            }
        }

        echo json_encode($response);
    }

    public function save_details($id = null, $action = null)
    {
        helper('designation_acl');

        $request = service('request');
        $postData = $request->getVar();
        $db = db_connect();

        $formAction = $postData['action'] ?? 'submit_for_review';
        unset($postData['action']);

        if (isset($postData['honeypot'])) {
            unset($postData['honeypot']);
        }

        if (!isset($postData['finding']) || $postData['finding'] === '') {
            $postData['finding'] = "NO";
        }

        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'doc', 'docx'];

        /* =========================
        BEFORE Proof SAVE
        ========================= */
        if (isset($postData['before_photo_url'])) {
            $postData['attachment'] = $postData['before_photo_url'];
            unset($postData['before_photo_url']);
        }

        /* =========================
        AFTER Proof SAVE
        ========================= */
        if (
            isset($_FILES['nc_after_photo']) &&
            !empty($_FILES['nc_after_photo']['name'])
        ) {
            if ($_FILES['nc_after_photo']['error'] == UPLOAD_ERR_OK) {

                $ext = strtolower(pathinfo($_FILES["nc_after_photo"]["name"], PATHINFO_EXTENSION));

                if (in_array($ext, $allowed)) {

                    $folder = "uploads/nc_after_photo/";
                    $oldFile = $postData['after_photo_url'] ?? '';

                    $url = $this->uploadImage($folder, "nc_after_photo", $oldFile);

                    if (!empty($url)) {
                        $postData['nc_after_photo'] = $url;
                    }
                }
            }
        } else {
            // 🔥 IMPORTANT: Only keep old image in EDIT case
            if (!empty($postData['after_photo_url'])) {
                $postData['nc_after_photo'] = $postData['after_photo_url'];
            }
        }

        // cleanup
        unset($postData['after_photo_url']);

        /* =========================
        REQUIRED VALIDATION
        ========================= */
        if ($formAction === 'submit_for_review') {
            if (empty(trim($postData['nc_remark'] ?? ''))) {
                return $this->response->setJSON([
                    'status' => 0,
                    'message' => 'NC Remark is required'
                ]);
            }

            // if (empty(trim($postData['nc_after_photo'] ?? ''))) {
            //     return $this->response->setJSON([
            //         'status' => 0,
            //         'message' => 'NC After Proof is required'
            //     ]);
            // }
        }

        /* =========================
        COMMON FIELD MAP
        ========================= */
        if (!empty($postData['main_category'])) {
            $postData['site_category'] = $postData['main_category'];
        }

        if (!empty($postData['sub_category'])) {
            $postData['sub_category'] = $postData['sub_category'];
        }

        $now = date('Y-m-d H:i:s');
        $userId = $_SESSION['user_id'] ?? null;

        if (!empty($userId) && is_numeric($userId) && (int) $userId > 0) {
            $userId = (int) $userId;
        } elseif (isSuperAdmin()) {
            $userId = 0; // Super Admin save as 0
        } else {
            $userId = null; // other empty save null
        }

        /* =========================
        UPDATE EXISTING RECORD
        ========================= */
        if (!empty($id)) {

            if (isHigherAuthority()) {
                return $this->response->setJSON([
                    'status' => 0,
                    'message' => 'Higher Authority has read-only access to NC Tracker'
                ]);
            }

            $currentRow = $db->table('alert_hse_audit_details')
                ->where('id', $id)
                ->get()
                ->getRowArray();

            if (!$currentRow) {
                return $this->response->setJSON([
                    'status' => 0,
                    'message' => 'Record not found'
                ]);
            }

            $currentStatus = (int) ($currentRow['nc_status'] ?? 0);

            if ($currentStatus == 3) {
                return $this->response->setJSON([
                    'status' => 0,
                    'message' => 'Closed NC cannot be modified'
                ]);
            }

            /* =========================
            STATUS FLOW
            ========================= */
            if ($formAction === 'save_as_draft') {
                $postData['nc_status'] = 4;
            }

            if ($formAction === 'submit_for_review') {

                // Always mark worked by on submit from modal
                $postData['nc_worked_by'] = $userId;
                $postData['update_date'] = $now;

                if (isAuditor()) {
                    // Auditor direct shift to Auditor Review
                    $postData['nc_status'] = 2;
                    $postData['nc_auditor_reviewed_by'] = $userId;
                    $postData['nc_auditor_reviewed_date'] = $now;
                } elseif (isClusterManager() || isAccountManager() || isSuperAdmin()) {
                    // Cluster / AM direct shift to Cluster Review
                    $postData['nc_status'] = 5;
                    $postData['nc_cluster_reviewed_by'] = $userId;
                    $postData['nc_cluster_reviewed_date'] = $now;
                } else {
                    // fallback
                    $postData['nc_status'] = 1;
                }
            }

            $postData['update_date'] = $now;

            if ($this->BaseModel->update($id, $postData)) {
                $userName = trim((string) getUserName());
                $this->sync_gemba_nc($id, $postData, $userName);
                
                return $this->response->setJSON([
                    'status' => 1,
                    'message' => 'Data saved successfully'
                ]);
            }

            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Data update failed'
            ]);
        }

        /* =========================
        INSERT NEW RECORD
        ========================= */
        $postData['nc_status'] = 1;
        $postData['nc_worked_by'] = $userId;
        $postData['update_date'] = $now;

        if ($this->BaseModel->insert($postData)) {
            return $this->response->setJSON([
                'status' => 1,
                'message' => 'Data saved successfully'
            ]);
        }

        return $this->response->setJSON([
            'status' => 0,
            'message' => 'Data insert failed'
        ]);
    }
    public function update_nc_status()
    {
        helper(['gemba_hse_sync']);
        $req = service('request');

        $id     = $req->getVar('id');
        $action = $req->getVar('status');

        $userId   = $_SESSION['user_id'] ?? null;
        if (!empty($userId) && is_numeric($userId) && (int) $userId > 0) {
            $userId = (int) $userId;
        } elseif (isSuperAdmin()) {
            $userId = 0;
        } else {
            $userId = null;
        }
        $userName = trim((string) getUserName());
        $now      = date('Y-m-d H:i:s');

        // Build postData for the sync helper
        $postData = [
            'close_date'     => $req->getVar('close_date'),
            'closure_status' => $req->getVar('closure_status') ?? 'Closed',
            'closed_remarks' => $req->getVar('closed_remarks') ?? null,
        ];

        // Handle closing proof file upload (only relevant for 'closed' / 'auditor_direct_close')
        if (
            in_array($action, ['closed', 'auditor_direct_close'], true) &&
            isset($_FILES['closed_uploaded_file']) &&
            !empty($_FILES['closed_uploaded_file']['name']) &&
            $_FILES['closed_uploaded_file']['error'] === UPLOAD_ERR_OK
        ) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'xls', 'xlsx', 'doc', 'docx'];
            $ext     = strtolower(pathinfo($_FILES['closed_uploaded_file']['name'], PATHINFO_EXTENSION));

            if (in_array($ext, $allowed)) {
                $folder  = 'uploads/nc_closed_files/';
                $oldFile = ''; // no old file to delete on individual close
                $url     = $this->uploadImage($folder, 'closed_uploaded_file', $oldFile);
                if (!empty($url)) {
                    $postData['closed_uploaded_file'] = $url;
                }
            }
        }

        $result = execute_hse_gemba_nc_action($id, $action, $postData, $userId, $userName);

        if ($result['status'] == 1 && isset($result['new_status']) && $result['old_status'] != $result['new_status']) {
            $this->send_nc_status_email($id, $result['old_status'], $result['new_status']);
        }

        return $this->response->setJSON([
            'status'  => $result['status'],
            'message' => $result['message']
        ]);
    }


    /* 🔥 COMMON DENY FUNCTION */
    private function deny()
    {
        return $this->response->setJSON([
            'status' => 0,
            'message' => 'Unauthorized action'
        ]);
    }
    public function get_regions_by_account_type()
    {
        ini_set('memory_limit', '2G');
        helper('designation_acl');
        $req = service('request');
        $accountType = $req->getVar('hse_type');

        $db = db_connect();
        $where = ["status = 1", "region IS NOT NULL", "region != ''"];
        $params = [];

        // Map filter values to alert_hse_client_master.category values
        $categoryMap = [
            'client' => 'Client Leased',
            'fm' => 'FM Leased',
            'inplant' => 'Inplant',
        ];

        if ($accountType && isset($categoryMap[$accountType])) {
            $where[] = "category = ?";
            $params[] = $categoryMap[$accountType];
        }

        // ACL: cluster manager restriction
        if (isClusterManager() || isAccountManager()) {
            $userCluster = getClusterManagerAssignedClusterHSE();
            if (!empty($userCluster)) {
                if (is_array($userCluster)) {
                    $ph = implode(',', array_fill(0, count($userCluster), '?'));
                    $where[] = "cluster IN ($ph)";
                    $params = array_merge($params, $userCluster);
                } else {
                    $where[] = "LOWER(TRIM(cluster)) = LOWER(TRIM(?))";
                    $params[] = $userCluster;
                }
            }
        }

        $whereSql = implode(' AND ', $where);
        $query = $db->query("SELECT DISTINCT region AS region_name FROM alert_hse_client_master WHERE $whereSql ORDER BY region", $params);

        return $this->response->setJSON($query->getResultArray());
    }

    /**
     * AJAX: Get clusters filtered by account type(s) and region(s)
     * POST params: account_types[], regions[]
     */
    public function get_clusters_by_regions()
    {
        ini_set('memory_limit', '2G');
        helper('designation_acl');
        $req = service('request');
        $region = $req->getVar('region');
        $accountType = $req->getVar('hse_type');

        $db = db_connect();
        $where = ["status = 1", "cluster IS NOT NULL", "cluster != ''"];
        $params = [];

        // Map filter values to alert_hse_client_master.category values
        $categoryMap = [
            'client' => 'Client Leased',
            'fm' => 'FM Leased',
            'inplant' => 'Inplant',
        ];

        // Filter by account type (category)
        if ($accountType && isset($categoryMap[$accountType])) {
            $where[] = "category = ?";
            $params[] = $categoryMap[$accountType];
        }

        // Filter by regions
        if ($region) {
            $where[] = "region = ?";
            $params[] = $region;
        }

        // ACL Logic
        if (isClusterManager() || isAccountManager()) {
            $userCluster = getClusterManagerAssignedClusterHSE();
            if (!empty($userCluster)) {
                if (is_array($userCluster)) {
                    $ph = implode(',', array_fill(0, count($userCluster), '?'));
                    $where[] = "cluster IN ($ph)";
                    $params = array_merge($params, $userCluster);
                } else {
                    $where[] = "LOWER(TRIM(cluster)) = LOWER(TRIM(?))";
                    $params[] = $userCluster;
                }
            }
        }

        $whereSql = implode(' AND ', $where);
        $query = $db->query("SELECT DISTINCT cluster AS cluster_name FROM alert_hse_client_master WHERE $whereSql ORDER BY cluster", $params);

        return $this->response->setJSON($query->getResultArray());
    }

    /**
     * AJAX: Get locations filtered by account type(s), region(s), and cluster(s)
     * POST params: account_types[], regions[], clusters[]
     */
    public function get_locations_by_clusters()
    {
        ini_set('memory_limit', '2G');
        helper('designation_acl');
        $req = service('request');
        $cluster = $req->getVar('cluster');
        $region = $req->getVar('region');
        $accountType = $req->getVar('hse_type');

        $db = db_connect();
        $where = ["status = 1", "client_name IS NOT NULL", "client_name != ''"];
        $params = [];

        // Map filter values to alert_hse_client_master.category values
        $categoryMap = [
            'client' => 'Client Leased',
            'fm' => 'FM Leased',
            'inplant' => 'Inplant',
        ];

        // Filter by account type (category)
        if ($accountType && isset($categoryMap[$accountType])) {
            $where[] = "category = ?";
            $params[] = $categoryMap[$accountType];
        }

        // ACL Logic: Force cluster selection if CM/WH
        if (isClusterManager() || isAccountManager()) {
            $userCluster = getClusterManagerAssignedClusterHSE();
            if (!empty($userCluster)) {
                $cluster = $userCluster; // Override user input
            }
        }

        if ($cluster) {
            $where[] = "cluster = ?";
            $params[] = $cluster;
        }

        if ($region) {
            $where[] = "region = ?";
            $params[] = $region;
        }

        $whereSql = implode(' AND ', $where);
        $query = $db->query("
                SELECT DISTINCT client_name AS location_name 
                FROM alert_hse_client_master 
                WHERE $whereSql
                ORDER BY client_name
            ", $params);

        return $this->response->setJSON($query->getResultArray());
    }

    /**
     * Export HSE NC Report as PDF
     */
    public function export_report_pdf()
    {
        require_once APPPATH . '/ThirdParty/dompdf/autoload.inc.php';

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);

        $dompdf = new \Dompdf\Dompdf($options);

        $db = db_connect();
        $req = service('request');

        /* -------------------------------
        FILTER (USE SAME LOGIC)
        --------------------------------*/

        $params = [];
        $whereSql = $this->buildWhere($params, $req);

        // Add nc_status filter if provided
        $nc_status = $req->getGet('nc_status');
        if ($nc_status !== null && $nc_status !== '') {
            $whereSql .= " AND d.nc_status = ?";
            $params[] = $nc_status;
        }

        /* -------------------------------
        FETCH DATA (latest NC per audit_no/question_id)
        --------------------------------*/

        $sql = "
            SELECT 
                d.nc_status,
                m.client_name,
                m.region
            FROM alert_hse_audit_details d
            INNER JOIN (
        SELECT 
            latest_master.audit_no,
            d2.question_id,
            MAX(d2.id) AS latest_detail_id
        FROM alert_hse_audit_details d2
        INNER JOIN alert_hse_audit_master latest_master
            ON latest_master.hse_audit_id = d2.hse_audit_id
        INNER JOIN (
            SELECT 
                audit_no,
                MAX(hse_audit_id) AS latest_hse_audit_id
            FROM alert_hse_audit_master
            GROUP BY audit_no
        ) latest_audit
            ON latest_audit.latest_hse_audit_id = latest_master.hse_audit_id
        GROUP BY latest_master.audit_no, d2.question_id
    ) latest_nc ON latest_nc.latest_detail_id = d.id
            LEFT JOIN alert_hse_audit_master m 
                ON m.hse_audit_id = d.hse_audit_id
            WHERE $whereSql
        ";

        $rows = $db->query($sql, $params)->getResultArray();

        if (empty($rows)) {
            // For PDF, we can't easily exit with a partial file, so we'll show an error view or simple HTML
            echo "<h3>No data found for the selected filters.</h3><p>Please adjust your filters and try again.</p>";
            exit;
        }

        /* -------------------------------
        CLIENT WISE GROUP
        --------------------------------*/

        $clientStats = [];

        foreach ($rows as $r) {
            $client = $r['client_name'] ?? 'Unknown';
            $region = $r['region'] ?? 'N/A';

            if (!isset($clientStats[$client])) {
                $clientStats[$client] = [
                    'client_name' => $client,
                    'region' => $region,
                    'total_ncs' => 0,
                    'open' => 0,
                    'working' => 0,
                    'cluster_review' => 0,
                    'auditor_review' => 0,
                    'closed' => 0,
                    'draft' => 0
                ];
            }

            $clientStats[$client]['total_ncs']++;

            switch ((int) $r['nc_status']) {
                case 0:
                    $clientStats[$client]['open']++;
                    break;
                case 1:
                    $clientStats[$client]['working']++;
                    break;
                case 5:
                    $clientStats[$client]['cluster_review']++;
                    break;
                case 2:
                    $clientStats[$client]['auditor_review']++;
                    break;
                case 3:
                    $clientStats[$client]['closed']++;
                    break;
                case 4:
                    $clientStats[$client]['draft']++;
                    break;
            }
        }

        /* -------------------------------
        OVERALL COUNT
        --------------------------------*/

        $total_ncs = count($rows);

        $open = 0;
        $working = 0;
        $cluster_review = 0;
        $auditor_review = 0;
        $closed = 0;
        $draft = 0;

        foreach ($clientStats as $cs) {
            $open += $cs['open'];
            $working += $cs['working'];
            $cluster_review += $cs['cluster_review'];
            $auditor_review += $cs['auditor_review'];
            $closed += $cs['closed'];
            $draft += $cs['draft'];
        }

        /* -------------------------------
        FINAL DATA
        --------------------------------*/

        $data = [
            'month' => $req->getGet('month'),
            'regions' => $req->getGet('region'),
            'clusters' => $req->getGet('cluster'),
            'locations' => $req->getGet('location'),
            'hse_types' => $req->getGet('hse_type'),

            'total_clients' => count($clientStats),
            'total_ncs' => $total_ncs,
            'open_count' => $open,
            'working_count' => $working,
            'cluster_review_count' => $cluster_review,
            'auditor_review_count' => $auditor_review,
            'closed_count' => $closed,
            'draft_count' => $draft,

            'client_data' => array_values($clientStats)
        ];

        /* -------------------------------
        GENERATE PDF
        --------------------------------*/

        $html = view("Master/hse_nc_tracker_report_pdf", $data);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        if (ob_get_length())
            ob_end_clean();

        header("Content-Type: application/pdf");
        header("Content-Disposition: attachment; filename=HSE_NC_Report.pdf");

        echo $dompdf->output();
    }

    public function export_report_excel()
    {
        $db = db_connect();
        $req = service('request');

        $params = [];

        // Same filter logic as table
        $whereSql = $this->buildWhere($params, $req);

        // Add nc_status filter if provided
        $nc_status = $req->getGet('nc_status');
        if ($nc_status !== null && $nc_status !== '') {
            $whereSql .= " AND d.nc_status = ?";
            $params[] = $nc_status;
        }

        $sql = "
            SELECT
                d.id,
                m.audit_no,
                m.audit_name,
                m.auditor_name,
                m.auditee_name,
                m.client_name,
                cm.region AS region,
                cm.cluster AS cluster_name,
                m.location,
                m.audit_date,
                m.main_category,
                m.sub_category,
                d.nc_type,
                d.audit_category,
                d.audit_question,
                d.remark,
                d.nc_status,
                d.nc_remark,
                d.nc_closed_date,
                d.nc_auditor_reviewed_date,
                d.nc_cluster_reviewed_date,
                d.nc_after_photo,
                worked_user.user_name AS nc_worked_by_name,
                closed_user.user_name AS nc_closed_by_user_name,
                auditor_user.user_name AS nc_auditor_reviewed_by_name,
                cluster_user.user_name AS nc_cluster_reviewed_by_name
            FROM alert_hse_audit_details d
            INNER JOIN (
                SELECT 
                    latest_master.audit_no,
                    d2.question_id,
                    MAX(d2.id) AS latest_detail_id
                FROM alert_hse_audit_details d2
                INNER JOIN alert_hse_audit_master latest_master
                    ON latest_master.hse_audit_id = d2.hse_audit_id
                INNER JOIN (
                    SELECT 
                        audit_no,
                        MAX(hse_audit_id) AS latest_hse_audit_id
                    FROM alert_hse_audit_master
                    GROUP BY audit_no
                ) latest_audit
                    ON latest_audit.latest_hse_audit_id = latest_master.hse_audit_id
                GROUP BY latest_master.audit_no, d2.question_id
            ) latest_nc ON latest_nc.latest_detail_id = d.id
    
            LEFT JOIN alert_hse_audit_master m
                ON m.hse_audit_id = d.hse_audit_id
            LEFT JOIN (
                SELECT 
                    client_name,
                    MAX(region) AS region,
                    MAX(cluster) AS cluster,
                    MAX(account_manager) AS account_manager
                FROM alert_hse_client_master
                WHERE status = 1
                GROUP BY client_name
            ) cm ON cm.client_name = m.client_name
            
            LEFT JOIN alert_users worked_user ON worked_user.user_id = d.nc_worked_by
            LEFT JOIN alert_users closed_user ON closed_user.user_id = d.nc_closed_by_user
            LEFT JOIN alert_users auditor_user ON auditor_user.user_id = d.nc_auditor_reviewed_by
            LEFT JOIN alert_users cluster_user ON cluster_user.user_id = d.nc_cluster_reviewed_by

            WHERE $whereSql
            ORDER BY d.id DESC
        ";

        $rows = $db->query($sql, $params)->getResultArray();

        $filename = "HSE_NC_Report_" . date('Ymd_His') . ".csv";

        $header = [
            'ID',
            'Audit No',
            'Audit Name',
            'Auditor',
            'Auditee',
            'Client',
            'Region',
            'Cluster',
            'Audit Date',
            'Site Category',
            'Sub Category',
            'NC Type',
            'Question Category',
            'Audit Question',
            'Remark',
            'NC Status',
            'NC Remark',
            'NC Worked By',
            'NC Closed By',
            'NC Auditor Reviewed By',
            'NC Cluster Reviewed By',
            'NC Worked Date',
            'NC Closed Date',
            'NC Auditor Reviewed Date'
        ];

        $statusMap = [
            0 => 'Open',
            1 => 'Working',
            2 => 'Auditor Review',
            3 => 'Closed',
            4 => 'Draft',
            5 => 'Cluster Review',
        ];

        $fp = fopen('php://temp', 'w+');
        if (!$fp) {
            return $this->response->setStatusCode(500)->setBody('Unable to open temporary stream for CSV output');
        }

        // UTF-8 BOM for Excel
        fprintf($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));
        fputcsv($fp, $header, ',', '"', '\\');

        foreach ($rows as $r) {
            $statusLabel = $statusMap[$r['nc_status']] ?? 'Unknown';

            $row_data = [
                $r['id'],
                $r['audit_no'],
                $r['audit_name'],
                $r['auditor_name'],
                $r['auditee_name'],
                $r['client_name'],
                $r['region'],
                $r['cluster_name'],
                $r['audit_date'],
                $r['main_category'],
                $r['sub_category'],
                $r['nc_type'],
                $r['audit_category'],
                $r['audit_question'],
                $r['remark'],
                $statusLabel,
                $r['nc_remark'],
                $r['nc_worked_by_name'] ?? $r['nc_worked_by'] ?? '-',
                $r['nc_closed_by_user_name'] ?? $r['nc_closed_by_user'] ?? '-',
                $r['nc_auditor_reviewed_by_name'] ?? '-',
                $r['nc_cluster_reviewed_by_name'] ?? '-',
                $r['nc_closed_date'],
                $r['nc_auditor_reviewed_date']
            ];

            foreach ($row_data as $idx => $val) {
                if (!isset($val) || trim((string) $val) === '') {
                    $row_data[$idx] = 'NA';
                } else {
                    $cleaned = str_replace(["\r\n", "\r", "\n"], " ", (string) $val);
                    $row_data[$idx] = preg_replace('/\s+/', ' ', trim($cleaned));
                }
            }

            fputcsv($fp, $row_data, ',', '"', '\\');
        }

        rewind($fp);
        $csvData = stream_get_contents($fp);
        fclose($fp);

        return $this->response
            ->setStatusCode(200)
            ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvData);
    }
    // public function export_report_excel()
    // {
    //     $db  = db_connect();
    //     $req = service('request');

    //     $params = [];

    //     // ✅ SAME FILTER
    //     $whereSql = $this->buildWhere($params, $req);

    //     // Add nc_status filter if provided
    //     $nc_status = $req->getGet('nc_status');
    //     if ($nc_status !== null && $nc_status !== '') {
    //         $whereSql .= " AND d.nc_status = ?";
    //         $params[] = $nc_status;
    //     }

    //     $sql = "
    //         SELECT
    //             d.id,
    //             m.audit_no,
    //             m.audit_name,
    //             m.auditor_name,
    //             m.client_name,
    //             m.region,
    //             m.cluster_name,
    //             m.location,
    //             m.audit_date,
    //             m.main_category,
    //             m.sub_category,
    //             d.audit_question,
    //             d.remark,
    //             d.nc_status,
    //             d.nc_remark,
    //             d.nc_closed_date,
    //             d.nc_auditor_reviewed_date,
    //             d.nc_after_photo
    //         FROM alert_hse_audit_details d
    //         LEFT JOIN alert_hse_audit_master m
    //             ON m.hse_audit_id = d.hse_audit_id
    //         WHERE $whereSql
    //         ORDER BY d.id DESC
    //     ";

    //     $rows = $db->query($sql, $params)->getResultArray();

    //     $filename = "HSE_NC_Report_" . date('Ymd_His') . ".csv";

    //     $header = [
    //         'ID',
    //         'Audit No',
    //         'Audit Name',
    //         'Auditor',
    //         'Client',
    //         'Region',
    //         'Cluster',
    //         'Location',
    //         'Date',
    //         'Category',
    //         'Sub Category',
    //         'Question',
    //         'Remark',
    //         'Status',
    //         'NC Remark',
    //         'Closed Date',
    //         'Reviewed Date'
    //     ];

    //     $statusMap = [
    //         0 => 'Open',
    //         1 => 'Working',
    //         2 => 'Auditor Review',
    //         3 => 'Closed',
    //         4 => 'Draft',
    //         5 => 'Cluster Review',
    //     ];

    //     $fp = fopen('php://temp', 'w+');
    //     if (!$fp) {
    //         return $this->response->setStatusCode(500)->setBody('Unable to open temporary stream for CSV output');
    //     }

    //     // BOM for Excel UTF-8
    //     fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
    //     fputcsv($fp, $header, ',', '"', '\\');

    //     foreach ($rows as $r) {
    //         $statusLabel = $statusMap[$r['nc_status']] ?? 'Unknown';
    //         fputcsv($fp, [
    //             $r['id'],
    //             $r['audit_no'],
    //             $r['audit_name'],
    //             $r['auditor_name'],
    //             $r['client_name'],
    //             $r['region'],
    //             $r['cluster_name'],
    //             $r['location'],
    //             $r['audit_date'],
    //             $r['main_category'],
    //             $r['sub_category'],
    //             $r['audit_question'],
    //             $r['remark'],
    //             $statusLabel,
    //             $r['nc_remark'],
    //             $r['nc_closed_date'],
    //             $r['nc_auditor_reviewed_date']
    //         ], ',', '"', '\\');
    //     }

    //     rewind($fp);
    //     $csvData = stream_get_contents($fp);
    //     fclose($fp);

    //     return $this->response
    //         ->setStatusCode(200)
    //         ->setHeader('Content-Type', 'text/csv; charset=UTF-8')
    //         ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
    //         ->setBody($csvData);
    // }


    public function get_sub_category()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => false,
                'message' => 'Direct access not allowed'
            ]);
        }

        $siteCategory = $this->request->getVar('site_category_name');
        $db = db_connect();

        $builder = $db->table('alert_hse_sub_category sc')
            ->select('sc.sub_category_name')
            ->join('alert_hse_site_category s', 's.site_category_id = sc.site_category_id')
            ->where('sc.status !=', 2);

        if (!empty($siteCategory)) {
            if (!is_array($siteCategory))
                $siteCategory = [$siteCategory];
            $siteCategory = array_filter(array_map('trim', $siteCategory));
            if (!empty($siteCategory) && !in_array('ALL', $siteCategory)) {
                $builder->whereIn('s.site_category_name', $siteCategory);
            }
        }

        $result = $builder->orderBy('sc.sub_category_name', 'ASC')->get()->getResultArray();

        return $this->response->setJSON($result);
    }

    public function get_cluster()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Not AJAX']);
            }

            $region = $this->request->getVar('region');
            log_message('debug', 'get_cluster called with region: ' . json_encode($region));

            $db = db_connect();

            $builder = $db->table('alert_hse_client_master');
            $builder->distinct();
            $builder->select('cluster as cluster_name');
            $builder->where('cluster IS NOT NULL');
            $builder->where('cluster !=', '');
            $builder->where('status', 1);

            // Region filter
            if (!empty($region)) {
                if (!is_array($region))
                    $region = [$region];
                $region = array_filter(array_map('trim', $region));
                if (!empty($region) && !in_array('ALL', $region)) {
                    $builder->whereIn('region', $region);
                }
            }

            // Cluster Manager restriction
            if (isClusterManager()) {
                $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());

                if (!empty($assignedClusters)) {
                    $builder->whereIn('cluster', $assignedClusters);
                } else {
                    $builder->where('1=0');
                }
            }

            // Account Manager / WH Manager restriction
            if (isAccountManager() || isWHManager()) {
                $assignedDetails = getAccountManagerAssignedDetailsHSE();

                $assignedClusters = array_unique(array_filter(array_column($assignedDetails, 'cluster')));
                $assignedClients = array_unique(array_filter(array_column($assignedDetails, 'client_name')));

                if (!empty($assignedClusters)) {
                    $builder->whereIn('cluster', $assignedClusters);
                }

                if (!empty($assignedClients)) {
                    $builder->whereIn('client_name', $assignedClients);
                }
                // ✅ DO NOT block if empty
            }

            $result = $builder->orderBy('cluster', 'ASC')->get()->getResultArray();

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'get_cluster error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal Server Error']);
        }
    }

    public function get_location()
    {
        try {
            if (!$this->request->isAJAX()) {
                return $this->response->setStatusCode(403)->setJSON(['error' => 'Not AJAX']);
            }

            $cluster = $this->request->getVar('cluster');
            log_message('debug', 'get_location called with cluster: ' . json_encode($cluster));

            $db = db_connect();

            $builder = $db->table('alert_hse_client_master');
            $builder->distinct();
            $builder->select('client_name as location_name');
            $builder->where('client_name IS NOT NULL');
            $builder->where('client_name !=', '');
            $builder->where('status', 1);

            // Cluster filter
            if (!empty($cluster)) {
                if (!is_array($cluster))
                    $cluster = [$cluster];
                $cluster = array_filter(array_map('trim', $cluster));
                if (!empty($cluster) && !in_array('ALL', $cluster)) {
                    $builder->whereIn('cluster', $cluster);
                }
            }

            // Cluster Manager restriction
            if (isClusterManager()) {
                $assignedClusters = $this->cleanArrayValues(getClusterManagerAssignedClusterHSE());

                if (!empty($assignedClusters)) {
                    $builder->whereIn('cluster', $assignedClusters);
                } else {
                    $builder->where('1=0');
                }
            }

            // Account Manager / WH Manager restriction
            if (isAccountManager() || isWHManager()) {
                $assignedDetails = getAccountManagerAssignedDetailsHSE();

                $assignedClients = array_unique(array_filter(array_column($assignedDetails, 'client_name')));

                if (!empty($assignedClients)) {
                    $builder->whereIn('client_name', $assignedClients);
                }
                // ✅ DO NOT block if empty
            }

            $result = $builder->orderBy('client_name', 'ASC')->get()->getResultArray();

            return $this->response->setJSON($result);
        } catch (\Exception $e) {
            log_message('error', 'get_location error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['error' => 'Internal Server Error']);
        }
    }

    public function send_nc_status_email($ncId, $oldStatus, $newStatus)
    {
        $db = db_connect();

        $detail = $db->table('alert_hse_audit_details d')
            ->select('d.*, m.audit_no, m.audit_name, m.client_name, m.region, m.auditee_name, m.auditor_name, cm.cluster, cm.account_manager')
            ->join('alert_hse_audit_master m', 'm.hse_audit_id = d.hse_audit_id', 'left')
            ->join('alert_hse_client_master cm', 'cm.client_name = m.client_name AND cm.status = 1', 'left')
            ->where('d.id', $ncId)
            ->get()
            ->getRowArray();

        if (!$detail)
            return false;

        // If both Account Manager and Cluster are empty, it means the entire process 
        // is being handled solely by the Auditor. We should not send emails in this case.
        if (empty($detail['account_manager']) && empty($detail['cluster'])) {
            return false;
        }

        $toEmail = '';
        $ccEmail = '';
        $subject = '';
        $template = '';

        $data = [
            'audit_no' => $detail['audit_no'] ?? '',
            'audit_name' => $detail['audit_name'] ?? '',
            'client' => $detail['client_name'] ?? '',
            'region' => $detail['region'] ?? '',
            'cluster' => $detail['cluster'] ?? '',
            'auditor' => $detail['auditor_name'] ?? '',
            'auditee' => $detail['auditee_name'] ?? '',
            'audit_date' => date('d-M-Y', strtotime($detail['update_date'] ?? date('Y-m-d'))),
            'nc_type' => $detail['nc_type'] ?? 'NC',
            'question' => $detail['audit_question'] ?? '',
            'nc_remark' => $detail['nc_remark'] ?? '',
            'action_link' => base_url('/Masters/Hse_nc_tracker'),
        ];

        // Auditor email
        $auditorEmail = '';
        if (!empty($detail['auditor_name'])) {
            $auditorRow = $db->table('alert_users')->where('user_name', $detail['auditor_name'])->get()->getRowArray();
            if ($auditorRow)
                $auditorEmail = $auditorRow['user_email'];
        }

        // log_message('info', 'NC Mail To: ' . $auditorEmail);
        // Cluster Manager email
        $clusterEmail = '';
        if (!empty($detail['cluster'])) {
            $cmUser = $db->table('alert_users')->where('user_name', $detail['cluster'])->where('user_designation', 'Cluster Manager')->get()->getRowArray();
            if ($cmUser)
                $clusterEmail = $cmUser['user_email'];
        }

        // Account Manager email
        $amEmail = '';
        if (!empty($detail['account_manager'])) {
            $amRow = $db->table('alert_users')->where('user_name', $detail['account_manager'])->get()->getRowArray();
            if ($amRow) {
                $amEmail = $amRow['user_email'];
            }
        }

        if ($oldStatus == 1 && $newStatus == 5) {
            $toEmail = $clusterEmail;
            $ccEmail = $auditorEmail;
            $subject = "NC Submitted for Cluster Review - " . $data['audit_no'];
            $template = 'Emails/nc_cluster_review';
        } elseif ($oldStatus == 5 && $newStatus == 2) {
            $toEmail = $auditorEmail;
            $subject = "NC Submitted for Auditor Review - " . $data['audit_no'];
            $template = 'Emails/nc_auditor_review';
        } elseif ($oldStatus == 2 && $newStatus == 3) {
            $toEmail = $auditorEmail;
            $subject = "NC Closed Successfully - " . $data['audit_no'];
            $template = 'Emails/nc_closed';
            $data['closed_date'] = date('d-M-Y', strtotime($detail['nc_closed_date'] ?? date('Y-m-d')));
            $data['closure_remark'] = $detail['remark'] ?? 'Closed';
        } elseif ($newStatus == 0) {
            // Rejected
            $allEmails = array_filter([$amEmail, $clusterEmail, $auditorEmail]);
            if (!empty($allEmails)) {
                $toEmail = array_shift($allEmails); // Main recipient
                if (!empty($allEmails)) {
                    $ccEmail = implode(',', $allEmails); // Remaining to CC
                }
            }
            $subject = "NC Status Rejected - " . $data['audit_no'];
            $template = 'Emails/nc_rejected';
        }

        if (empty($template) || empty($toEmail))
            return false;

        $message = view($template, $data);
        helper('email_service');
        return sendSystemEmail($toEmail, $subject, $message, [], $ccEmail);
    }

    private function getStatusCardsHtml($open, $working, $reviewCluster, $reviewAuditor, $closed, $qsStr, $activeStatus = null)
    {
        $totalPoints = $open + $working + $reviewCluster + $reviewAuditor + $closed;

        $indexUrl = base_url('Masters/Hse_nc_tracker') . ($qsStr ? '?' . $qsStr : '');
        $openUrl = base_url('Masters/Hse_nc_tracker/template_type_filter/0') . ($qsStr ? '?' . $qsStr : '');
        $workingUrl = base_url('Masters/Hse_nc_tracker/template_type_filter/1') . ($qsStr ? '?' . $qsStr : '');
        $clusterUrl = base_url('Masters/Hse_nc_tracker/template_type_filter/5') . ($qsStr ? '?' . $qsStr : '');
        $auditorUrl = base_url('Masters/Hse_nc_tracker/template_type_filter/2') . ($qsStr ? '?' . $qsStr : '');
        $closedUrl = base_url('Masters/Hse_nc_tracker/template_type_filter/3') . ($qsStr ? '?' . $qsStr : '');

        $totalActive = ($activeStatus === null || $activeStatus === '') ? 'active-filter' : '';
        $openActive = ($activeStatus === '0' || $activeStatus === 0) ? 'active-filter' : '';
        $workingActive = ($activeStatus === '1' || $activeStatus === 1) ? 'active-filter' : '';
        $clusterActive = ($activeStatus === '5' || $activeStatus === 5) ? 'active-filter' : '';
        $auditorActive = ($activeStatus === '2' || $activeStatus === 2) ? 'active-filter' : '';
        $closedActive = ($activeStatus === '3' || $activeStatus === 3) ? 'active-filter' : '';

        return '
        <style>
        .tracker-cards-container {
            margin-bottom: 24px;
        }
        .tracker-card {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 20px 16px;
            height: 100%;
            min-height: 145px;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            text-decoration: none !important;
            position: relative;
            overflow: hidden;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025);
            cursor: pointer;
        }
        .tracker-card::before {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background-color: var(--status-color);
            transition: height 0.25s ease;
        }
        .tracker-card:hover::before {
            height: 8px;
        }
        .tracker-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px var(--shadow-color), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }
        .tracker-card.active-filter {
            background: var(--light-bg);
            border: 2px solid var(--status-color) !important;
            box-shadow: 0 12px 25px -8px var(--shadow-color) !important;
        }
        .tracker-card .card-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            margin-bottom: 12px;
        }
        .tracker-card .icon-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 12px;
            background-color: var(--light-bg);
            color: var(--status-color);
            transition: all 0.25s ease;
        }
        .tracker-card:hover .icon-wrapper {
            transform: scale(1.1);
        }
        .tracker-card .card-value {
            font-size: 34px;
            font-weight: 800;
            color: var(--status-color);
            line-height: 1;
            margin: 0;
        }
        .tracker-card .card-body-content {
            display: flex;
            flex-direction: column;
            width: 100%;
            margin-bottom: 4px;
        }
        .tracker-card .card-title-text {
            font-size: 14px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 3px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .tracker-card .card-subtitle-text {
            font-size: 12px;
            font-weight: 500;
            color: #64748b;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        </style>
        <div class="tracker-cards-container">
            <div class="row g-4">
                <!-- Total Points -->
                <div class="col-xl-2 col-md-4 col-sm-6 col-6">
                    <a href="' . $indexUrl . '" class="tracker-card ' . $totalActive . '" style="--status-color: #4f46e5; --light-bg: #f5f3ff; --shadow-color: rgba(79, 70, 229, 0.25);">
                        <div class="card-top">
                            <div class="icon-wrapper">
                                <i class="fas fa-clipboard-list" style="font-size: 24px;"></i>
                            </div>
                            <h3 class="card-value">' . number_format($totalPoints) . '</h3>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-text" title="Total Observation Points">Total Observation Points</div>
                            <div class="card-subtitle-text" title="Overall NC observations">Overall NC observations</div>
                        </div>
                    </a>
                </div>
                <!-- Closed -->
                <div class="col-xl-2 col-md-4 col-sm-6 col-6">
                    <a href="' . $closedUrl . '" class="tracker-card ' . $closedActive . '" style="--status-color: #22c55e; --light-bg: #f0fdf4; --shadow-color: rgba(34, 197, 94, 0.25);">
                        <div class="card-top">
                            <div class="icon-wrapper">
                                <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                            </div>
                            <h3 class="card-value">' . number_format($closed) . '</h3>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-text" title="Closed">Closed</div>
                            <div class="card-subtitle-text" title="Successfully Closed">Successfully Closed</div>
                        </div>
                    </a>
                </div>
                <!-- Open -->
                <div class="col-xl-2 col-md-4 col-sm-6 col-6">
                    <a href="' . $openUrl . '" class="tracker-card ' . $openActive . '" style="--status-color: #ef4444; --light-bg: #fef2f2; --shadow-color: rgba(239, 68, 68, 0.25);">
                        <div class="card-top">
                            <div class="icon-wrapper">
                                <i class="fas fa-exclamation-circle" style="font-size: 24px;"></i>
                            </div>
                            <h3 class="card-value">' . number_format($open) . '</h3>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-text" title="Open">Open</div>
                            <div class="card-subtitle-text" title="Pending action">Pending action</div>
                        </div>
                    </a>
                </div>
                <!-- Working -->
                <div class="col-xl-2 col-md-4 col-sm-6 col-6">
                    <a href="' . $workingUrl . '" class="tracker-card ' . $workingActive . '" style="--status-color: #f97316; --light-bg: #fff7ed; --shadow-color: rgba(249, 115, 22, 0.25);">
                        <div class="card-top">
                            <div class="icon-wrapper">
                                <i class="fas fa-circle-notch fa-spin" style="font-size: 24px;"></i>
                            </div>
                            <h3 class="card-value">' . number_format($working) . '</h3>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-text" title="Working">Working</div>
                            <div class="card-subtitle-text" title="Under progress">Under progress</div>
                        </div>
                    </a>
                </div>
                <!-- Cluster Review -->
                <div class="col-xl-2 col-md-4 col-sm-6 col-6">
                    <a href="' . $clusterUrl . '" class="tracker-card ' . $clusterActive . '" style="--status-color: #3b82f6; --light-bg: #eff6ff; --shadow-color: rgba(59, 130, 246, 0.25);">
                        <div class="card-top">
                            <div class="icon-wrapper">
                                <i class="fas fa-users" style="font-size: 24px;"></i>
                            </div>
                            <h3 class="card-value">' . number_format($reviewCluster) . '</h3>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-text" title="Cluster Review">Cluster Review</div>
                            <div class="card-subtitle-text" title="Awaiting Cluster Review">Awaiting Cluster Review</div>
                        </div>
                    </a>
                </div>
                <!-- Auditor Review -->
                <div class="col-xl-2 col-md-4 col-sm-6 col-6">
                    <a href="' . $auditorUrl . '" class="tracker-card ' . $auditorActive . '" style="--status-color: #06b6d4; --light-bg: #ecfeff; --shadow-color: rgba(6, 182, 212, 0.25);">
                        <div class="card-top">
                            <div class="icon-wrapper">
                                <i class="fas fa-shield-alt" style="font-size: 24px;"></i>
                            </div>
                            <h3 class="card-value">' . number_format($reviewAuditor) . '</h3>
                        </div>
                        <div class="card-body-content">
                            <div class="card-title-text" title="Auditor Review">Auditor Review</div>
                            <div class="card-subtitle-text" title="Awaiting Auditor Approval">Awaiting Auditor Approval</div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
        ';
    }

    private function sync_gemba_nc($hseDetailId, $updateData, $userName = null)
    {
        $db = db_connect();

        $hseDetail = $db->table('alert_hse_audit_details')
            ->where('id', $hseDetailId)
            ->get()
            ->getRowArray();

        if (!$hseDetail) return false;

        $gembaRow = $db->table('alert_gemba_audits')
            ->where('hse_audit_id', $hseDetail['hse_audit_id'])
            ->where('hse_detail_id', $hseDetail['id'])
            ->where('source_module', 'hse_audit')
            ->get()
            ->getRowArray();

        if (!$gembaRow) return false;

        if (empty($userName)) {
            $userName = session()->get('user_name') ?? 'System';
        }

        $gembaUpdate = [];

        // Direct mappings
        if (array_key_exists('nc_status', $updateData)) {
            $gembaUpdate['nc_status'] = $updateData['nc_status'];
        }
        if (array_key_exists('nc_remark', $updateData)) {
            $gembaUpdate['nc_remark'] = $updateData['nc_remark'];
        }
        if (array_key_exists('nc_after_photo', $updateData)) {
            $gembaUpdate['nc_after_photo'] = $updateData['nc_after_photo'];
        }
        if (array_key_exists('rejection_reason', $updateData)) {
            $gembaUpdate['rejection_reason'] = $updateData['rejection_reason'];
        }

        // Action By (User Name instead of ID)
        if (array_key_exists('nc_worked_by', $updateData)) {
            $gembaUpdate['nc_worked_by'] = $userName;
        }
        if (array_key_exists('nc_cluster_reviewed_by', $updateData)) {
            $gembaUpdate['nc_cluster_reviewed_by'] = $userName;
            $gembaUpdate['nc_cluster_reviewed_date'] = $updateData['nc_cluster_reviewed_date'] ?? date('Y-m-d H:i:s');
        }
        if (array_key_exists('nc_auditor_reviewed_by', $updateData)) {
            $gembaUpdate['nc_auditor_reviewed_by'] = $userName;
            $gembaUpdate['nc_auditor_reviewed_date'] = $updateData['nc_auditor_reviewed_date'] ?? date('Y-m-d H:i:s');
        }
        if (array_key_exists('nc_rejected_by', $updateData)) {
            $gembaUpdate['nc_rejected_by'] = $userName;
            $gembaUpdate['nc_rejected_date'] = $updateData['nc_rejected_date'] ?? date('Y-m-d H:i:s');
        }
        if (array_key_exists('nc_closed_by', $updateData)) {
            $gembaUpdate['nc_closed_by'] = $userName;
            $gembaUpdate['nc_closed_date'] = $updateData['nc_closed_date'] ?? date('Y-m-d H:i:s');
        }

        // Special handling for Closed Status (3)
        if (isset($updateData['nc_status']) && (int)$updateData['nc_status'] === 3) {
            $now = date('Y-m-d H:i:s');
            $closedTs = strtotime($now);
            if (isset($updateData['nc_closed_date']) && !empty($updateData['nc_closed_date'])) {
                $closedTs = strtotime($updateData['nc_closed_date']);
            }

            $gembaUpdate['point_status'] = 'Closed';
            $gembaUpdate['closure_status'] = 'Closed';
            $gembaUpdate['closed_date'] = date('Y-m-d', $closedTs);
            $gembaUpdate['qhse_remarks'] = $updateData['nc_remark'] ?? ($hseDetail['nc_remark'] ?? 'Closed via HSE NC Tracker');
            $gembaUpdate['updated_by'] = $userName;
            $gembaUpdate['weeknum_closed'] = date('W', $closedTs);
            $gembaUpdate['month_closed'] = date('Y-m-01', $closedTs);
            $gembaUpdate['year_month_closed'] = date('Y-m', $closedTs);
            $gembaUpdate['weeknum_yearmonth_closed'] = date('Y-m', $closedTs) . '-W' . date('W', $closedTs);

            // Ageing Calculation
            if (!empty($gembaRow['audit_report_date'])) {
                $reportTs = strtotime($gembaRow['audit_report_date']);
                $days = floor(($closedTs - $reportTs) / (60 * 60 * 24));
                $gembaUpdate['ageing_days'] = $days >= 0 ? $days : 0;

                if ($gembaUpdate['ageing_days'] <= 30) {
                    $gembaUpdate['age_bracket'] = '<=30';
                } elseif ($gembaUpdate['ageing_days'] <= 60) {
                    $gembaUpdate['age_bracket'] = '31-60';
                } elseif ($gembaUpdate['ageing_days'] <= 90) {
                    $gembaUpdate['age_bracket'] = '61-90';
                } else {
                    $gembaUpdate['age_bracket'] = '>90';
                }
            }
        }

        if (!empty($gembaUpdate)) {
            return $db->table('alert_gemba_audits')
                ->where('gemba_sr_no', $gembaRow['gemba_sr_no'])
                ->update($gembaUpdate);
        }

        return false;
    }

    public function bulk_close_nc()
    {
        $req = $this->request;
        if (!$req->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        helper('gemba_acl');
        if (!gemba_can_write()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Write access denied.']);
        }

        $ids             = $req->getPost('ids');
        $closed_date     = $req->getPost('closed_date');
        $closure_remarks = $req->getPost('closure_remarks');
        $closure_status  = $req->getPost('closure_status') ?? 'Closed';

        if (empty($ids) || !is_array($ids)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'No NCs selected.']);
        }

        $db = \Config\Database::connect();
        $db->transBegin();

        $processedCount = 0;
        $userId         = $_SESSION['user_id'] ?? null;
        if (!empty($userId) && is_numeric($userId) && (int) $userId > 0) {
            $userId = (int) $userId;
        } elseif (isSuperAdmin()) {
            $userId = 0;
        } else {
            $userId = null;
        }
        $userName = getUserName();

        helper('gemba_hse_sync');

        foreach ($ids as $id) {
            $id = (int) $id;

            // Fetch the details record
            $detail = $db->table('alert_hse_audit_details')->where('id', $id)->get()->getRowArray();
            if (!$detail) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => "Detail record ID {$id} not found."]);
            }

            // Only allow closing if it is in Auditor Review (status = 2)
            if ((int) $detail['nc_status'] !== 2) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => "Detail record ID {$id} is not in Auditor Review status."]);
            }

            // Build postData for sync helper – correct positional signature: ($id, $action, $postData, $userId, $userName)
            $postData = [
                'close_date'      => $closed_date,
                'closed_remarks'  => $closure_remarks,
                'closure_status'  => $closure_status,
            ];

            $actionResult = execute_hse_gemba_nc_action($id, 'closed', $postData, $userId, $userName);

            if ($actionResult['status'] !== 1) {
                $db->transRollback();
                return $this->response->setJSON(['status' => 'error', 'message' => "Error on ID {$id}: " . $actionResult['message']]);
            }

            $processedCount++;
        }

        if ($db->transStatus() === false) {
            $db->transRollback();
            return $this->response->setJSON(['status' => 'error', 'message' => 'Database transaction failed.']);
        } else {
            $db->transCommit();
            return $this->response->setJSON(['status' => 'success', 'message' => "Successfully closed {$processedCount} NC(s)."]);
        }
    }
}