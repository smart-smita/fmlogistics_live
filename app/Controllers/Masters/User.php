<?php

//namespace App\Controllers;
namespace App\Controllers\Masters;
use App\Controllers\BaseController;
use App\Models\CRUDBaseModel;


class User extends BaseController
{
    protected $BaseModel;   //  Declare property here

    public function __construct()
    {
        // changes on 16/10/25 by darsh: Using simple ACL helper for regional filtering
        helper(["form", "simple_acl"]);
        $db = null;
        $db['table'] = 'alert_users';
        $db['allowedFields'] = ['user_emp_country', 'user_emp_zone', 'user_emp_code', 'user_region', 'user_name', 'user_cluster', 'user_location', 'user_email', 'user_contact', 'user_password', 'user_designation', 'employee_reporting_to', 'fcm_id', 'status', 'admin_flag'];
        $db['primaryKey'] = "user_id";
        $this->BaseModel = new CRUDBaseModel($db);
    }
    public function index()
    {
        $data = [];

        $tdata['title'] = "";
        $tdata['button_name'] = "Add User";
        $tdata['button_id'] = "user_modal";
        // $tdata['hide_add_button'] = true;

        $tdata['display_contents'] = [
            "user_id" => "ID",
            "user_emp_country" => "Country",
            //"user_emp_zone"=>"Region",
            "user_region" => "Region",
            "user_location" => "Client Name",
            "user_cluster" => "Cluster",
            "user_emp_code" => "Code",
            "user_name" => "Name",
            "user_email" => "E-mail",
            "user_contact" => "Contact",
            "user_password" => "Password",
            "user_designation" => "Designation",
            //"employee_reporting_to"=>"Reporting to",
            // "fcm_id"=>"ID",
            "action" => "Action"
        ];

        // $data['ajax_url']=base_url("Masters/User/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/User/table_ajax");

        $db = db_connect();

        $datatop = $this->getUserSummaryCards($db);
        $data['table'] = $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);


        $db = db_connect();
        $alert_users = $db->table("alert_users");

        $alert_country = $db->table("alert_country");

        $data['ajax_url'] = base_url("Masters/User/save_details");
        $data['country_list'] = $alert_country->where('status', 1)->get()->getResultArray();

        $data['zone_list'] = ["WEST", "NORTH", "SOUTH"];

        $data['designation'] = array();
        $designation['id'] = "1";
        $designation['name'] = "Account Manager";
        array_push($data['designation'], $designation);
        $designation['id'] = "2";
        $designation['name'] = "Cluster manager"; // Changed on 29/09/25 By Darsh
        array_push($data['designation'], $designation);
        $designation['id'] = "3";
        $designation['name'] = "Higher authority";
        array_push($data['designation'], $designation);
        $designation['id'] = "4";
        $designation['name'] = "Auditor";
        array_push($data['designation'], $designation);
        $data['title'] = $tdata['title'];
        $data['button_id'] = $tdata['button_id'];
        // $users_list
        // changes on 8/10/25 by Darsh - replaced Reporting manager with Cluster manager
        $data['users_list'] = $db->table("alert_users")->where(array("user_designation" => "Cluster manager"))->select('user_id,user_emp_country,user_name')->get()->getResultArray();

        $db = db_connect();

        // Load regions based on user's access level
        if ($_SESSION['role'] == 'Cluster manager') {
            // Cluster managers can only see their own region
            $userRegionId = $_SESSION['region_id'] ?? '';
            if ($userRegionId) {
                $data['region_list'] = $db->query("SELECT * FROM alert_region WHERE region_id = ? AND status = 1", [$userRegionId])->getResultArray();
                $data['cluster_list'] = $db->query("
                    SELECT DISTINCT c.cluster_id, c.cluster_name, c.status
                    FROM alert_cluster_master c
                    INNER JOIN alert_location_master l ON c.cluster_name = l.cluster_name
                    INNER JOIN alert_region r ON l.region_name = r.region_name
                    WHERE r.region_id = ? AND c.status = 1
                    ORDER BY c.cluster_name
                ", [$userRegionId])->getResultArray();
                $data['location_list'] = $db->query("
                    SELECT MIN(l.location_id) as location_id, l.location_name, l.status
                    FROM alert_location_master l
                    INNER JOIN alert_region r ON l.region_name = r.region_name
                    WHERE r.region_id = ? AND l.status = 1
                    GROUP BY l.location_name
                    ORDER BY l.location_name
                ", [$userRegionId])->getResultArray();
            } else {
                $data['region_list'] = [];
                $data['cluster_list'] = [];
                $data['location_list'] = [];
            }
        } else {
            // Other roles can see all regions/clusters/locations
            $data['region_list'] = $db->table("alert_region")->where("status", "1")->orderBy("region_name")->get()->getResultArray();
            $data['cluster_list'] = $db->table("alert_cluster_master")->where("status", "1")->orderBy("cluster_name")->get()->getResultArray();
            $data['location_list'] = $db->table("alert_location_master")
                ->select("MIN(location_id) as location_id, location_name, status")
                ->where("status", "1")
                ->groupBy("location_name")
                ->orderBy("location_name")
                ->get()
                ->getResultArray();
        }

        $data['oe_clients'] = $db->table("alert_client")->where("status !=", 2)->orderBy("client_name")->get()->getResultArray();
        $data['hse_clients'] = $db->table("alert_hse_client_master")->where("status !=", 2)->orderBy("client_name")->get()->getResultArray();

        return view("Master/add_user", $data);
    }
    public function template_type_filter($category)
    {
        $data = [];

        $tdata['title'] = "";
        $tdata['button_name'] = "Add User";
        $tdata['button_id'] = "user_modal";
        $tdata['hide_add_button'] = true;

        $tdata['display_contents'] = [
            "user_id" => "ID",
            "user_emp_country" => "Country",
            "user_region" => "Region",
            "user_location" => "Client Name",
            "user_cluster" => "Cluster",
            "user_emp_code" => "Code",
            "user_name" => "Name",
            "user_email" => "E-mail",
            "user_contact" => "Contact",
            "user_password" => "Password",
            "user_designation" => "Designation",
            "action" => "Action"
        ];

        // $data['ajax_url']=base_url("Masters/User/save_details");
        $tdata['ajax_url_for_data'] = base_url("Masters/User/table_ajax/" . $category);

        $db = db_connect();

        $datatop = $this->getUserSummaryCards($db);
        $data['table'] = $datatop;
        $data['table'] .= view("Layout/table-view", $tdata);


        $db = db_connect();
        $alert_users = $db->table("alert_users");

        $alert_country = $db->table("alert_country");

        $data['ajax_url'] = base_url("Masters/User/save_details");
        $data['country_list'] = $alert_country->where('status', 1)->get()->getResultArray();
        $data['zone_list'] = ["WEST", "NORTH", "SOUTH"];

        $data['designation'] = array();
        $designation['id'] = "1";
        $designation['name'] = "Account Manager";
        array_push($data['designation'], $designation);
        $designation['id'] = "2";
        $designation['name'] = "Cluster manager"; // changed on 01/10/25 By Darsh - replaced reporting manager to cluster manager
        array_push($data['designation'], $designation);
        $designation['id'] = "3";
        $designation['name'] = "Higher authority";
        array_push($data['designation'], $designation);
        $designation['id'] = "4";
        $designation['name'] = "Auditor";
        array_push($data['designation'], $designation);
        $data['title'] = $tdata['title'];
        $data['button_id'] = $tdata['button_id'];
        // $users_list
        // changes on 8/10/25 by Darsh - replaced Reporting manager with Cluster manager
        $data['users_list'] = $db->table("alert_users")->where(array("user_designation" => "Cluster manager"))->select('user_id,user_emp_country,user_name')->get()->getResultArray();

        $db = db_connect();
        $data['location_list'] = $db->table("alert_location_master")
            ->select("MIN(location_id) as location_id, location_name, status")
            ->where("status", "1")
            ->groupBy("location_name")
            ->orderBy("location_name")
            ->get()
            ->getResultArray();

        $db = db_connect();
        $data['region_list'] = $db->table("alert_region")->where("status", "1")->orderBy("region_name")->get()->getResultArray();

        $db = db_connect();
        $data['cluster_list'] = $db->table("alert_cluster_master")->where("status", "1")->orderBy("cluster_name")->get()->getResultArray();

        $data['oe_clients'] = $db->table("alert_client")->where("status !=", 2)->orderBy("client_name")->get()->getResultArray();
        $data['hse_clients'] = $db->table("alert_hse_client_master")->where("status !=", 2)->orderBy("client_name")->get()->getResultArray();

        return view("Master/add_user", $data);
    }
    public function table_ajax($category = null)
    {
        $db = db_connect();

        // Simple query without complex joins (using existing columns)
        $builder = $db->table('alert_users');

        // Always hide soft-deleted users (status = 2)
        $builder->where('status !=', 2);

        // Apply category filter if specified
        if ($category) {
            $category = urldecode($category);
            if ($category === 'Active') {
                $builder->where('status', 1);
            } else {
                $builder->where('user_designation', $category);
                // Removed status = 1 to show pending (0) and deactivated (3) as requested
            }
        }

        // ACL: Apply cluster-based filtering for Cluster Managers - 13/11/25
        helper('designation_acl');
        if (isClusterManager() || isWHManager()) {
            $userClusters = getClusterManagerAssignedCluster();
            if (!empty($userClusters)) {
                $builder->whereIn('user_cluster', $userClusters);
            }
        }

        $builder->orderBy('user_id', 'DESC');
        $tdata['table_data'] = $builder->get()->getResultArray();

        // Fetch client mappings to show in Client and Cluster columns
        $userIds = array_column($tdata['table_data'], 'user_id');
        if (!empty($userIds)) {
            $mappings = $db->table('alert_user_client_mapping')
                           ->whereIn('user_id', $userIds)
                           ->where('status', 1)
                           ->get()->getResultArray();
            $userMappingData = [];
            foreach ($mappings as $m) {
                $userMappingData[$m['user_id']]['sites'][] = $m['site_name'];
                $userMappingData[$m['user_id']]['clusters'][] = $m['cluster_name'];
            }
            
            foreach ($tdata['table_data'] as &$row) {
                $uid = $row['user_id'];
                if (isset($userMappingData[$uid])) {
                    $sites = array_unique(array_filter($userMappingData[$uid]['sites']));
                    $clusters = array_unique(array_filter($userMappingData[$uid]['clusters']));
                    $row['user_location'] = implode(', ', $sites);
                    $row['user_cluster'] = implode(', ', $clusters);
                } else if (in_array($row['user_designation'], ['Cluster manager', 'Account Manager'])) {
                    $row['user_location'] = '';
                    $row['user_cluster'] = '';
                }
            }
            unset($row); // Important: destroy reference to avoid bug in the next loop
        }

        // Debug logging for results
        if (isset($_SESSION['user_name']) && $_SESSION['user_name'] === 'Balamurugan') {
            error_log("DEBUG User List - Users found: " . count($tdata['table_data']));
        }
        $statusMessages = [
            0 => '<span class="badge badge-warning">Pending</span>',
            1 => '<span class="badge badge-success">Active</span>',
            3 => '<span class="badge badge-danger">Deactivated</span>'
        ];
        foreach ($tdata['table_data'] as $key => $row) {
            // changes on 1/11/25 by darsh: Added tooltips to action buttons
            $active_btn = '<button class="btn btn-icon btn-success" onclick="url_call_ajax(\'' . base_url("Masters/User/save_details/" . $row['user_id']) . '/active\',$(this));" title="Activate User" data-bs-toggle="tooltip" data-bs-placement="top">
											<span class="indicator-label svg-icon svg-icon-2">
												<i class="fa fa-unlock"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button> ';
            $deactive_btn = '<button  class="btn btn-icon btn-danger" onclick="url_call_ajax(\'' . base_url("Masters/User/save_details/" . $row['user_id']) . '/deactive\',$(this));" title="Deactivate User" data-bs-toggle="tooltip" data-bs-placement="top">
                			<span class="indicator-label svg-icon svg-icon-2">
                			<i class="fa fa-lock"></i>
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';
            $delete = '<button data-ajax-url="' . base_url("Masters/User/save_details/" . $row['user_id']) . '/delete" class="btn btn-icon btn-danger" onclick="delete_row(this);" title="Delete User" data-bs-toggle="tooltip" data-bs-placement="top">
                			<span class="indicator-label svg-icon svg-icon-2">
                				
								<i class="fa fa-trash"></i>
                				
                			</span>
                            <span class="indicator-progress">
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                			</button> ';

            //$(this).attr('data-kt-indicator', 'on');$(this).attr('disabled', true);setTimeout(function (obj) {obj.attr('data-kt-indicator', 'off');obj.attr('disabled', false);},500,$(this));
            // changes on 1/11/25 by darsh: Added tooltips to action buttons and removed userDevoiceDetails button
            $edit = '<button data-ajax-url="' . base_url("Masters/User/get_form_data/" . $row['user_id']) . '" class="btn btn-icon btn-primary" onclick="edit_id(this,' . $row['user_id'] . ');" title="Edit User" data-bs-toggle="tooltip" data-bs-placement="top">
											<span class="indicator-label svg-icon svg-icon-3">
												<i class="fa fa-edit"></i>
											</span>
                                            <span class="indicator-progress">
                                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                            </span>
											</button>
											';
                                            
            $toggle_status_btn = $active_btn; // Default to Unlock button (Activate)
            
            if ($row['status'] == "0") {
                $tdata['table_data'][$key]['tr_class'] = "bg-light-warning";
            } else if ($row['status'] == "1") {
                $toggle_status_btn = $deactive_btn; // If active, show Lock button (Deactivate)
            } else if ($row['status'] == "3") {
                $tdata['table_data'][$key]['tr_class'] = "bg-light-danger";
            }
            
            // changes on 1/11/25 by darsh: Removed userDevoiceDetails button from action column
            $tdata['table_data'][$key]['action'] = "<center>" . ($statusMessages[$row['status']] ?? '') . "<br><br>" . $edit . $toggle_status_btn . $delete . "</center>";
        }
        $tdata['data'] = $tdata['table_data'];
        unset($tdata['table_data']);
        return $this->response->setJSON($tdata);

    }
    function userDevoiceDetails($id = null)
    {
        $db = db_connect();
        $_SESSION['active_btn'] = "Master";
        $_SESSION['active_tag'] = "User";
        //= ;
        $data['title'] = "<a href='" . base_url(index_page() . 'User/userDetails') . "'>Users</a> / User Device Details";
        $data['display_contents'] = array(
            "u_id" => "ID",
            "user_name" => "User Name",
            "user_emp_code" => "User Code",
            "product" => "Product Name",
            "manufacture" => "Device",
            "imei_no" => "IMEI",
            "action" => "Actions"
        );
        $where = "";

        if (isset($id)) {
            $where = "where alert_users.user_id=$id";
        }

        $data['table_data'] = $db->query("SELECT `alert_users_devices`.*, `alert_users`.`user_name`, `alert_users`.`user_emp_code` FROM `alert_users_devices` inner join alert_users on `alert_users_devices`.`user_id`=`alert_users`.`user_id` $where")->getResultArray();
        //    print_r($data ['table_data'] );
        $i = 1;
        foreach ($data['table_data'] as $key => $val) {
            $pending_message = '<span class="badge badge-warning">Pending</span>';
            $active_message = '<span class="badge badge-success">Active</span>';
            $deactivated_message = '<span class="badge badge-danger">Deactivated</span>';
            $data['table_data'][$key]['action'] = "";
            $data['table_data'][$key]['action'] = ($val['status'] == 0) ? $pending_message : ($val['status'] == 1 ? $active_message : ($val['status'] == 3 ? $deactivated_message : ""));
            $data['table_data'][$key]['action'] .= "<br>";
            $data['table_data'][$key]['id'] = $key + 1;
            $activate = " <button  onclick='$(\"#myModalActionBtn\").attr(\"value\",$(this).attr(\"data-url\")); $(\"#myModalBody\").html(\"Are you sure to activate this record?\"); $(\"#myModalLabel\").html(\"Activate Record\"); $(\"#myModalLabel\").parent().parent().css(\"background-color\",\"#fff5f5\");' class='btn btn-outline-success'  data-url='" . base_url() . "/User/userDevoiceAction/" . $data['table_data'][$key]['user_device_id'] . "/2' data-target='#myModal' data-toggle='modal'><i class='fa fa-unlock-alt' data-toggle='tooltip' data-placement='bottom' title='User activate'></i></button>";
            $deactivate = " <button  onclick='$(\"#myModalActionBtn\").attr(\"value\",$(this).attr(\"data-url\")); $(\"#myModalBody\").html(\"Are you sure to deactivate this record?\"); $(\"#myModalLabel\").html(\"Deactivate Record\"); $(\"#myModalLabel\").parent().parent().css(\"background-color\",\"#fff5f5\");' class='btn btn-outline-danger'  data-url='" . base_url() . "/User/userDevoiceAction/" . $data['table_data'][$key]['user_device_id'] . "/3' data-target='#myModal' data-toggle='modal'><i class='fa fa-lock' data-toggle='tooltip' data-placement='bottom' title='Deactivate User'></i></button>";

            if ($data['table_data'][$key]['status'] == 2) {
                $data['table_data'][$key]['action'] .= $deactivate;
            } elseif ($data['table_data'][$key]['status'] == 1 || $data['table_data'][$key]['status'] == 0 || $data['table_data'][$key]['status'] == 3) {
                $data['table_data'][$key]['action'] .= $activate;
            }

            $data['table_data'][$key]['u_id'] = $i++;
        }
        return view('Layout/table-view', $data);
    }
    public function get_form_data($id)
    {
        $responce['status'] = "0";
        $responce['message'] = "Details not found";
        if (isset($id)) {
            $userData = $this->BaseModel->find($id);
            if ($userData) {
                $db = db_connect();
                $mappings = $db->table('alert_user_client_mapping')
                               ->where('user_id', $id)
                               ->where('status', 1)
                               ->get()->getResultArray();
                $oe_ids = [];
                $hse_ids = [];
                foreach ($mappings as $m) {
                    if ($m['client_type'] === 'OE') {
                        $oe_ids[] = (string)$m['client_id'];
                    } else if ($m['client_type'] === 'HSE') {
                        $hse_ids[] = (string)$m['client_id'];
                    }
                }
                $totalOeCount = $db->table('alert_client')->where('status !=', 2)->countAllResults();
                if ($totalOeCount > 0 && count(array_unique($oe_ids)) >= $totalOeCount) {
                    $oe_ids[] = 'ALL';
                }

                $totalHseCount = $db->table('alert_hse_client_master')->where('status !=', 2)->countAllResults();
                if ($totalHseCount > 0 && count(array_unique($hse_ids)) >= $totalHseCount) {
                    $hse_ids[] = 'ALL';
                }

                $userData['oe_site_ids'] = $oe_ids;
                $userData['hse_site_ids'] = $hse_ids;
                $responce['data'] = $userData;
                $responce['status'] = "1";
                $responce['message'] = "Details found";
            }
        }
        echo json_encode($responce);
    }
    public function save_details($id = null, $action = null)
    {
        helper('designation_acl');
        $request = service('request');
        $postData = $request->getVar();
        $responce = ['status' => '0', 'message' => 'Operation failed'];

        // Clean up form data
        if (isset($postData['honeypot'])) {
            unset($postData['honeypot']);
        }
        if (isset($postData['options'])) {
            $postData['lmra_options'] = implode(",", $postData['options']);
            unset($postData['options']);
        }

        // Set default values for optional form inputs if this is a form submission
        if ($action === null) {
            if (!isset($postData['user_location'])) {
                $postData['user_location'] = '';
            }
            if (!isset($postData['user_cluster'])) {
                $postData['user_cluster'] = '';
            }
            if (!isset($postData['user_region'])) {
                $postData['user_region'] = '';
            }
            if (!isset($postData['user_emp_code'])) {
                $postData['user_emp_code'] = '';
            }
            if (!isset($postData['user_designation'])) {
                $postData['user_designation'] = '';
            }
            if (!isset($postData['user_name'])) {
                $postData['user_name'] = '';
            }
            if (!isset($postData['user_contact'])) {
                $postData['user_contact'] = '';
            }
            if (!isset($postData['user_email'])) {
                $postData['user_email'] = '';
            }
            if (!isset($postData['user_password'])) {
                $postData['user_password'] = '';
            }
        }

        // Convert region/cluster/location names to IDs for new structure
        if (isset($postData['user_region']) && !empty($postData['user_region'])) {
            $db = db_connect();
            $region = $db->table('alert_region')->where('region_name', $postData['user_region'])->get()->getRowArray();
            if ($region) {
                $postData['region_id'] = $region['region_id'];
            }
        }

        if (isset($postData['user_cluster']) && !empty($postData['user_cluster'])) {
            $db = db_connect();
            $cluster = $db->table('alert_cluster_master')->where('cluster_name', $postData['user_cluster'])->get()->getRowArray();
            if ($cluster) {
                $postData['cluster_id'] = $cluster['cluster_id'];
            }
        }

        if (isset($postData['user_location']) && !empty($postData['user_location'])) {
            $db = db_connect();
            $location = $db->table('alert_location_master')->where('location_name', $postData['user_location'])->get()->getRowArray();
            if ($location) {
                $postData['location_id'] = $location['location_id'];
            }
        }

        // Validate hierarchy based on designation
        if (isset($postData['user_designation'])) {
            $designation = $postData['user_designation'];

            if ($designation == 'Cluster manager') {
                if (empty($postData['user_region'])) {
                    $responce['message'] = "Region is required for Cluster Managers";
                    echo json_encode($responce);
                    return;
                }

                // Validate that cluster belongs to region ONLY if both are provided
                if (!empty($postData['user_cluster']) && !empty($postData['user_region'])) {
                    $db = db_connect();
                    $validCluster = $db->query(
                        "SELECT COUNT(*) as count FROM alert_location_master WHERE cluster_name = ? AND region_name = ?",
                        [$postData['user_cluster'], $postData['user_region']]
                    )->getRowArray();

                    if ($validCluster['count'] == 0) {
                        $responce['message'] = "Selected cluster does not belong to the selected region";
                        echo json_encode($responce);
                        return;
                    }
                }
            } elseif ($designation == 'Account Manager') {
                if (empty($postData['user_cluster'])) {
                    $responce['message'] = "Cluster is required for Account Managers";
                    echo json_encode($responce);
                    return;
                }
            }
        }

        // Check ACL permissions for current user
        if ($_SESSION['role'] == 'Cluster manager') {
            $assignedClusters = getClusterManagerAssignedCluster();
            if (isset($postData['user_cluster']) && !in_array($postData['user_cluster'], $assignedClusters)) {
                $responce['message'] = "You can only manage users in your assigned cluster";
                echo json_encode($responce);
                return;
            }
        }

        // Validate admin_flag limit (max 100 users)
        if (isset($postData['admin_flag']) && $postData['admin_flag'] == 1) {
            $db = db_connect();
            $builder = $db->table('alert_users')->where('admin_flag', 1);
            if (isset($id)) {
                $builder->where('user_id !=', $id);
            }
            $adminCount = $builder->countAllResults();
            if ($adminCount >= 100) {
                $responce['message'] = "Admin access limit of 100 users has been reached.";
                echo json_encode($responce);
                return;
            }
        }

        $mappingPostData = $postData;
        unset(
            $postData['oe_site_ids'],
            $postData['hse_site_ids'],
            $postData['oe_site_ids[]'],
            $postData['hse_site_ids[]']
        );

        if (isset($id)) {
            $responce['message'] = "Data update failed";
            if (isset($action)) {
                switch ($action) {
                    case "active":
                        $postData['status'] = "1";
                        break;
                    case "deactive":
                        $postData['status'] = "3";
                        break;
                    case "delete":
                        $postData['status'] = "2";
                        break;
                }
            }

            // Additional ACL check for updates
            if ($_SESSION['role'] == 'Cluster manager') {
                // Check if the user being updated belongs to cluster manager's assigned cluster
                $existingUser = $this->BaseModel->find($id);
                $assignedClusters = getClusterManagerAssignedCluster();
                if ($existingUser && !in_array($existingUser['user_cluster'], $assignedClusters)) {
                    $responce['message'] = "You can only update users in your assigned cluster";
                    echo json_encode($responce);
                    return;
                }
            }

            if ($this->BaseModel->update($id, $postData)) {
                $responce['status'] = "1";
                $responce['message'] = "Data updated successfully";

                if ($action === null) {
                    $this->saveUserClientMappings($id, $mappingPostData);
                }

                // Add entry to cluster master if designation is Cluster manager
                if ($action === null && isset($postData['user_designation']) && $postData['user_designation'] == 'Cluster manager') {
                    $clusterName = trim($postData['user_name'] ?? '');
                    if (!empty($clusterName)) {
                        $db = db_connect();
                        // Check if it already exists
                        $existing = $db->table('alert_cluster_master')
                            ->where('cluster_name', $clusterName)
                            ->get()
                            ->getRowArray();
                        if (!$existing) {
                            $db->table('alert_cluster_master')->insert([
                                'cluster_name' => $clusterName,
                                'status' => 1,
                                'default_date' => date('Y-m-d H:i:s'),
                                'update_date' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }


            }
        } else {
            $postData['status'] = "1";
            $responce['message'] = "Data insertion failed";

            if ($this->BaseModel->insert($postData)) {
                $responce['status'] = "1";
                $responce['message'] = "Data saved successfully";

                $newUserId = $this->BaseModel->insertID() ?: db_connect()->insertID();
                $this->saveUserClientMappings($newUserId, $mappingPostData);

                // Add entry to cluster master if designation is Cluster manager
                if (isset($postData['user_designation']) && $postData['user_designation'] == 'Cluster manager') {
                    $clusterName = trim($postData['user_name'] ?? '');
                    if (!empty($clusterName)) {
                        $db = db_connect();
                        // Check if it already exists
                        $existing = $db->table('alert_cluster_master')
                            ->where('cluster_name', $clusterName)
                            ->get()
                            ->getRowArray();
                        if (!$existing) {
                            $db->table('alert_cluster_master')->insert([
                                'cluster_name' => $clusterName,
                                'status' => 1,
                                'default_date' => date('Y-m-d H:i:s'),
                                'update_date' => date('Y-m-d H:i:s')
                            ]);
                        }
                    }
                }
                


                // Send Email Notification to Higher Authority
                $db = db_connect();
                helper('email_service');
                $higherAuthorities = $db->table('alert_users')
                    ->where('user_designation', 'Higher authority')
                    ->where('status', 1)
                    ->get()->getResultArray();
                    
                if (!empty($higherAuthorities)) {
                    $toEmails = array_column($higherAuthorities, 'user_email');
                    // filter out empty emails
                    $toEmails = array_filter($toEmails);
                    if (!empty($toEmails)) {
                        $subject = "New User Created - ALERT Audit Management Tool";
                        $adminStatus = (isset($postData['admin_flag']) && $postData['admin_flag'] == 1) ? "Yes" : "No";
                        $createdBy = $_SESSION['user_name'] ?? 'System';
                        $createdDate = date('Y-m-d H:i:s');
                        
                        $contact = $postData['user_contact'] ?? 'N/A';
                        $reportingManager = $postData['employee_reporting_to'] ?? 'N/A';
                        
                        // Fetch active user summary for the email
                        $countQuery = $db->query("SELECT user_designation, COUNT(user_id) as found_count FROM alert_users WHERE status = 1 GROUP BY user_designation");
                        $countResults = $countQuery->getResultArray();
                        $designationCounts = [];
                        foreach ($countResults as $row) {
                            $designationCounts[$row['user_designation']] = $row['found_count'];
                        }

                        $totalQuery = $db->query("SELECT COUNT(user_id) as total_count FROM alert_users WHERE status = 1");
                        $totalActiveUsers = $totalQuery->getRow()->total_count;

                        $accountManagerCount = ($designationCounts['Account Manager'] ?? 0) + ($designationCounts['Engineer'] ?? 0);
                        $reportingManagerCount = $designationCounts['Cluster manager'] ?? 0;
                        $higherAuthorityCount = $designationCounts['Higher authority'] ?? 0;
                        $auditorCount = $designationCounts['Auditor'] ?? 0;
                        
                        $body = "
                        <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                            <p>A new user has been created in the ALERT Audit Management Tool.</p>
                            <table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; max-width: 600px;'>
                                <tr><th style='background-color: #f8f9fa; text-align: left; width: 40%;'>New User Name</th><td>{$postData['user_name']}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Employee Code</th><td>{$postData['user_emp_code']}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Email ID</th><td>{$postData['user_email']}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Contact Number</th><td>{$contact}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Designation</th><td>{$postData['user_designation']}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Admin Role</th><td>{$adminStatus}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Region</th><td>{$postData['user_region']}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Cluster</th><td>{$postData['user_cluster']}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Location</th><td>{$postData['user_location']}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Reporting Manager</th><td>{$reportingManager}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Created By</th><td>{$createdBy}</td></tr>
                                <tr><th style='background-color: #f8f9fa; text-align: left;'>Created Date & Time</th><td>{$createdDate}</td></tr>
                            </table>
                            
                            <br>
                            <h3>Active User Summary</h3>
                            <table border='1' cellpadding='8' cellspacing='0' style='border-collapse: collapse; width: 100%; max-width: 400px;'>
                                <thead>
                                    <tr><th style='background-color: #f8f9fa; text-align: left;'>Role</th><th style='background-color: #f8f9fa; text-align: right;'>Active Users</th></tr>
                                </thead>
                                <tbody>
                                    <tr><td>Higher Authority</td><td style='text-align: right;'>{$higherAuthorityCount}</td></tr>
                                    <tr><td>Cluster Manager</td><td style='text-align: right;'>{$reportingManagerCount}</td></tr>
                                    <tr><td>Account Manager</td><td style='text-align: right;'>{$accountManagerCount}</td></tr>
                                    <tr><td>Auditor</td><td style='text-align: right;'>{$auditorCount}</td></tr>
                                    <tr><td><strong>Total Active Users</strong></td><td style='text-align: right;'><strong>{$totalActiveUsers}</strong></td></tr>
                                </tbody>
                            </table>";

                        if ($totalActiveUsers > 100) {
                            $body .= "
                            <br>
                            <div style='background-color: #fff3cd; color: #856404; padding: 15px; border-left: 5px solid #ffeeba; margin-top: 20px; max-width: 600px;'>
                                <h3 style='margin-top: 0; margin-bottom: 10px;'>&#9888; User Threshold Alert</h3>
                                <p style='margin-bottom: 10px;'>The total number of active users has exceeded the configured limit.</p>
                                <p style='margin-bottom: 10px;'>Current Active Users: <strong>{$totalActiveUsers}</strong></p>
                                <p style='margin-bottom: 0;'>Please review inactive, duplicate, or unnecessary user accounts and take appropriate action if required.</p>
                            </div>";
                        }
                        
                        $body .= "<br><p>Thank you.</p></div>";
                        
                        //sendSystemEmail($toEmails, $subject, $body);
                        sendSystemEmail("tikonesmita6@gmail.com", $subject, $body);
                    }
                }
            }
        }

        echo json_encode($responce);
    }
    private function getUserSummaryCards($db) {
        $countQuery = $db->query("SELECT user_designation, COUNT(user_id) as found_count FROM alert_users WHERE status != 2 GROUP BY user_designation");
        $countResults = $countQuery->getResultArray();
        $designationCounts = [];
        foreach ($countResults as $row) {
            $key = strtolower(trim($row['user_designation']));
            $designationCounts[$key] = ($designationCounts[$key] ?? 0) + $row['found_count'];
        }

        $totalQuery = $db->query("SELECT COUNT(user_id) as total_count FROM alert_users WHERE status = 1");
        $totalActiveUsers = $totalQuery->getRow()->total_count;

        $accountManagerCount = $designationCounts['account manager'] ?? 0;
        $engineerCount = $designationCounts['engineer'] ?? 0;
        $clusterManagerCount = $designationCounts['cluster manager'] ?? 0;
        $higherAuthorityCount = $designationCounts['higher authority'] ?? 0;
        $auditorCount = $designationCounts['auditor'] ?? 0;

        return '
        <style>
            .user-filter-pill {
                background: #ffffff;
                border: 1px solid #eef2f6;
                box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
                border-radius: 50rem !important;
                padding: 6px 18px 6px 8px !important;
                transition: all 0.25s ease;
                display: inline-flex;
                align-items: center;
                text-decoration: none !important;
                color: #2d3748 !important;
                font-weight: 600;
                font-size: 14px;
            }
            .user-filter-pill:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 22px rgba(0, 0, 0, 0.08);
                border-color: #cbd5e1;
                color: #1e293b !important;
            }
            .pill-icon-circle {
                width: 38px;
                height: 38px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                margin-right: 12px;
            }
        </style>
        <div class="d-flex flex-wrap gap-3 mb-6 align-items-center">
            <a href="' . base_url('Masters/User/template_type_filter/Active') . '" class="user-filter-pill">
                <div class="pill-icon-circle" style="background-color: #2ecc71;">
                    <i class="fas fa-users text-white fs-6"></i>
                </div>
                <span>Total Active Users (' . $totalActiveUsers . ')</span>
                <i class="fas fa-chevron-right text-muted fs-8 ms-2"></i>
            </a>

            <a href="' . base_url('Masters/User/template_type_filter/Higher authority') . '" class="user-filter-pill">
                <div class="pill-icon-circle" style="background-color: #ff9f43;">
                    <i class="fas fa-crown text-white fs-6"></i>
                </div>
                <span>Higher Authority (' . $higherAuthorityCount . ')</span>
                <i class="fas fa-chevron-right text-muted fs-8 ms-2"></i>
            </a>

            <a href="' . base_url('Masters/User/template_type_filter/Auditor') . '" class="user-filter-pill">
                <div class="pill-icon-circle" style="background-color: #a55eea;">
                    <i class="fas fa-user-tie text-white fs-6"></i>
                </div>
                <span>Auditor (' . $auditorCount . ')</span>
                <i class="fas fa-chevron-right text-muted fs-8 ms-2"></i>
            </a>

            ' . ($engineerCount > 0 ? '
            <a href="' . base_url('Masters/User/template_type_filter/Engineer') . '" class="user-filter-pill">
                <div class="pill-icon-circle" style="background-color: #20c997;">
                    <i class="fas fa-hard-hat text-white fs-6"></i>
                </div>
                <span>Engineer (' . $engineerCount . ')</span>
                <i class="fas fa-chevron-right text-muted fs-8 ms-2"></i>
            </a>
            ' : '') . '

            <a href="' . base_url('Masters/User/template_type_filter/Account Manager') . '" class="user-filter-pill">
                <div class="pill-icon-circle" style="background-color: #4b6cb7;">
                    <i class="fas fa-user text-white fs-6"></i>
                </div>
                <span>Account Manager (' . $accountManagerCount . ')</span>
                <i class="fas fa-chevron-right text-muted fs-8 ms-2"></i>
            </a>

            <a href="' . base_url('Masters/User/template_type_filter/Cluster manager') . '" class="user-filter-pill">
                <div class="pill-icon-circle" style="background-color: #1e1b4b;">
                    <i class="fas fa-briefcase text-white fs-6"></i>
                </div>
                <span>Cluster Manager (' . $clusterManagerCount . ')</span>
                <i class="fas fa-chevron-right text-muted fs-8 ms-2"></i>
            </a>
        </div>';
    }

    public function get_dependency_data()
    {
        $request = service('request');
        $action = $request->getPost('action');
        $db = db_connect();
        $response = ['status' => 0, 'data' => []];

        if ($action == 'get_customers') {
            $location = $request->getPost('location');
            if (!empty($location)) {
                $builder = $db->table('alert_client')->where('status', 1);
                $builder->where('location', $location);
                $response['data'] = $builder->select('client_id, client_name')->get()->getResultArray();
                $response['status'] = 1;
            }
        } elseif ($action == 'get_reporting_managers') {
            $customers = $request->getPost('customers');
            $builder = $db->table('alert_users')->where('status', 1);
            $builder->whereIn('user_designation', ['Higher authority', 'Cluster manager', 'Account Manager']);
            // If specific customers are selected, we could filter Reporting Managers by those customers.
            // But since Reporting Managers might just be any active users in these roles, we load all.
            $response['data'] = $builder->select('user_id, user_name, user_designation')->get()->getResultArray();
            $response['status'] = 1;
        } elseif ($action == 'get_user_customers') {
            $user_id = $request->getPost('user_id');
            if ($user_id) {
                $mappings = $db->table('alert_user_client_mapping')
                               ->where('user_id', $user_id)
                               ->get()->getResultArray();
                $client_ids = array_column($mappings, 'client_id');
                $response['data'] = $client_ids;
                $response['status'] = 1;
            }
        }

        return $this->response->setJSON($response);
    }

    private function saveUserClientMappings($userId, $postData)
    {
        if (!$userId) return;
        $db = db_connect();

        if (!isset($postData['oe_site_ids']) && !isset($postData['hse_site_ids']) &&
            !isset($postData['oe_site_ids[]']) && !isset($postData['hse_site_ids[]'])) {
            return;
        }

        $existingMappings = $db->table('alert_user_client_mapping')
                               ->where('user_id', $userId)
                               ->get()->getResultArray();
        
        $oldKeys = [];
        foreach ($existingMappings as $em) {
            $oldKeys[$em['client_type'] . '_' . $em['client_id']] = $em;
        }

        $db->table('alert_user_client_mapping')->where('user_id', $userId)->delete();

        $newKeys = [];

        $oeSiteIds = $postData['oe_site_ids'] ?? ($postData['oe_site_ids[]'] ?? []);
        if (is_string($oeSiteIds)) {
            $oeSiteIds = array_filter(explode(',', $oeSiteIds));
        }
        if (is_array($oeSiteIds)) {
            $hasAllOe = false;
            foreach ($oeSiteIds as $val) {
                $valStr = strtolower(trim((string)$val));
                if (in_array($valStr, ['all', 'all_selected', 'select_all', 'all selected', '0'], true)) {
                    $hasAllOe = true;
                    break;
                }
            }
            if ($hasAllOe) {
                $allOeClients = $db->table('alert_client')->where('status !=', 2)->select('client_id')->get()->getResultArray();
                $oeSiteIds = array_column($allOeClients, 'client_id');
            }

            foreach ($oeSiteIds as $oeId) {
                $oeId = trim($oeId);
                if (empty($oeId) || strtolower($oeId) === 'all') continue;
                $client = $db->table('alert_client')->where('client_id', $oeId)->get()->getRowArray();
                if ($client) {
                    $key = 'OE_' . $client['client_id'];
                    $newKeys[$key] = [
                        'user_id' => $userId,
                        'client_id' => $client['client_id'],
                        'client_type' => 'OE',
                        'site_name' => $client['client_name'],
                        'cluster_name' => $client['cluster'] ?? '',
                        'region_name' => $client['region'] ?? ''
                    ];
                    $db->table('alert_user_client_mapping')->insert([
                        'user_id' => $userId,
                        'client_id' => $client['client_id'],
                        'client_type' => 'OE',
                        'site_name' => $client['client_name'],
                        'cluster_name' => $client['cluster'] ?? '',
                        'region_name' => $client['region'] ?? '',
                        'created_by' => $_SESSION['user_id'] ?? null,
                        'status' => 1
                    ]);
                }
            }
        }

        $hseSiteIds = $postData['hse_site_ids'] ?? ($postData['hse_site_ids[]'] ?? []);
        if (is_string($hseSiteIds)) {
            $hseSiteIds = array_filter(explode(',', $hseSiteIds));
        }
        if (is_array($hseSiteIds)) {
            $hasAllHse = false;
            foreach ($hseSiteIds as $val) {
                $valStr = strtolower(trim((string)$val));
                if (in_array($valStr, ['all', 'all_selected', 'select_all', 'all selected', '0'], true)) {
                    $hasAllHse = true;
                    break;
                }
            }
            if ($hasAllHse) {
                $allHseClients = $db->table('alert_hse_client_master')->where('status !=', 2)->select('client_id')->get()->getResultArray();
                $hseSiteIds = array_column($allHseClients, 'client_id');
            }

            foreach ($hseSiteIds as $hseId) {
                $hseId = trim($hseId);
                if (empty($hseId) || strtolower($hseId) === 'all') continue;
                $hseClient = $db->table('alert_hse_client_master')->where('client_id', $hseId)->get()->getRowArray();
                if ($hseClient) {
                    $key = 'HSE_' . $hseClient['client_id'];
                    $newKeys[$key] = [
                        'user_id' => $userId,
                        'client_id' => $hseClient['client_id'],
                        'client_type' => 'HSE',
                        'site_name' => $hseClient['client_name'],
                        'cluster_name' => $hseClient['cluster'] ?? '',
                        'region_name' => $hseClient['region'] ?? ''
                    ];
                    $db->table('alert_user_client_mapping')->insert([
                        'user_id' => $userId,
                        'client_id' => $hseClient['client_id'],
                        'client_type' => 'HSE',
                        'site_name' => $hseClient['client_name'],
                        'cluster_name' => $hseClient['cluster'] ?? '',
                        'region_name' => $hseClient['region'] ?? '',
                        'created_by' => $_SESSION['user_id'] ?? null,
                        'status' => 1
                    ]);
                }
            }
        }

        if ($db->tableExists('alert_user_client_allocation_history')) {
            $changedByUserId = $_SESSION['user_id'] ?? null;
            $changedByUserName = $_SESSION['user_name'] ?? 'System';

            foreach ($oldKeys as $k => $oldItem) {
                if (!isset($newKeys[$k])) {
                    $db->table('alert_user_client_allocation_history')->insert([
                        'user_id' => $userId,
                        'client_id' => $oldItem['client_id'],
                        'client_type' => $oldItem['client_type'],
                        'site_name' => $oldItem['site_name'],
                        'cluster_name' => $oldItem['cluster_name'] ?? '',
                        'region_name' => $oldItem['region_name'] ?? '',
                        'action' => 'REMOVED',
                        'effective_start_date' => $oldItem['created_at'] ?? date('Y-m-d H:i:s'),
                        'effective_end_date' => date('Y-m-d H:i:s'),
                        'changed_by_user_id' => $changedByUserId,
                        'changed_by_user_name' => $changedByUserName,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }

            foreach ($newKeys as $k => $newItem) {
                if (!isset($oldKeys[$k])) {
                    $db->table('alert_user_client_allocation_history')->insert([
                        'user_id' => $userId,
                        'client_id' => $newItem['client_id'],
                        'client_type' => $newItem['client_type'],
                        'site_name' => $newItem['site_name'],
                        'cluster_name' => $newItem['cluster_name'] ?? '',
                        'region_name' => $newItem['region_name'] ?? '',
                        'action' => 'ASSIGNED',
                        'effective_start_date' => date('Y-m-d H:i:s'),
                        'effective_end_date' => null,
                        'changed_by_user_id' => $changedByUserId,
                        'changed_by_user_name' => $changedByUserName,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
        }
    }
}
