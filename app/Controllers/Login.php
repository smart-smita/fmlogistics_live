<?php

namespace App\Controllers;

class Login extends BaseController
{
    public function __construct()
    {
    }
    public function demoLog($type = "")
    {
        $request = \Config\Services::request();
        $payload = @file_get_contents('php://input');

        log_message('error', " ===== $type ====");
        //  log_message('error', '{message}', ['message' => $request->getJSON()]);
        log_message('error', print_r($request, true));
        log_message('error', print_r($payload, true));

        log_message('error', print_r($_SERVER, true));
        log_message('error', print_r($_POST, true));

    }


    public function index()
    {
        $data['action'] = base_url("Login/accept_login");
        return view('Layout/login', $data);
    }
    public function test()
    {
        $ip = $this->request->getIPAddress();
        echo $ip;//apiip.net,https://www.whatismyip.com/
        $ip_address = $ip;

        $api_url = "https://freegeoip.app/json/$ip_address";

        $json_data = file_get_contents($api_url);

        $location_data = json_decode($json_data);

        $city_name = $location_data->city;

        echo "Your city is: " . $city_name;
    }
    public function update_details($user_id)
    {
        // ads_customer_device
        // user_id
        //ads_device_master

        $db = db_connect();
        // $ads_customer_device = $db->table('ads_device_master');
        // $ads_customer_device->where("user_id",$user_id);
        // $data = $ads_customer_device->select("serial_no")->get()->getResultArray();
        $sql = "select serial_no from ads_device_master where device_id in (select device_id from ads_customer_device where user_id = ?)";
        $query = $db->query($sql, [$user_id]);
        $resultdata = $query->getResultArray();
        $data['device_list'] = [];
        foreach ($resultdata as $row) {
            array_push($data['device_list'], $row['serial_no']);
        }
        return view('fcm', $data);
    }
    public function accept_login()
    {
        // Force POST only
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Invalid request method'
            ]);
        }

        $response = [
            'status' => 0,
            'message' => 'Login Failed',
            'url' => base_url('Login')
        ];

        $request = service('request');
        $db = db_connect();

        // SAFE INPUT
        $email = trim($request->getVar('email') ?? '');
        $password = trim($request->getVar('password') ?? '');

        // VALIDATION
        if ($email == '' || $password == '') {
            $response['message'] = 'Email and Password are required';
            return $this->response->setJSON($response);
        }

        // ---------------- SUPER ADMIN LOGIN ----------------
        $adminEmail = "admin@fmlogistic.com";
        $adminPassword = "Admin@123@";

        if ($email === $adminEmail && $password === $adminPassword) {
            $this->session->set("details", "admin");

            $_SESSION['role'] = "admin";
           // $_SESSION['admin_flag'] = 1;
            $_SESSION['login_id'] = "0";
            $_SESSION['user_id'] = "0";
            $_SESSION['fcm_id'] = "0";
            $_SESSION['country'] = "IN";
            $_SESSION['user_name'] = "Super_admin";
            $_SESSION['user_email'] = $adminEmail;
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            $_SESSION['user_acl'] = [
                'is_restricted' => false,
                'can_view_all' => true,
                'oe_client_ids' => [],
                'oe_site_names' => [],
                'hse_client_ids' => [],
                'hse_site_names' => [],
                'allocated_clusters' => []
            ];

            $agent = $this->request->getUserAgent();
            $browser = $agent->getBrowser() . ' ' . $agent->getVersion();
            $device = $agent->isMobile() ? $agent->getMobile() : 'Desktop (' . $agent->getPlatform() . ')';

            $db->table('alert_login_history')->insert([
                'user_id' => 0, // Super admin
                'session_id' => session_id(),
                'login_time' => date('Y-m-d H:i:s'),
                'last_activity' => date('Y-m-d H:i:s'),
                'ip_address' => $this->request->getIPAddress(),
                'browser' => $browser,
                'device_type' => $device,
                'status' => 'Success'
            ]);

            $response['status'] = 1;
            $response['message'] = "Login success";
            $response['url'] = base_url('Customer/Audit_dashboard/OE_Audit');

            return $this->response->setJSON($response);
        }

        // ---------------- NORMAL USER LOGIN ----------------
        // Step 1: Validate email and password first, ignoring soft deleted users
        $users = $db->table("alert_users")
            ->where([
                'user_email' => $email,
                'user_password' => $password
            ])
            ->where('status !=', 2)
            ->get()
            ->getResultArray();

        if (empty($users)) {
            $response['message'] = "Invalid email or password";
            return $this->response->setJSON($response);
        }

        // Step 2: Check for active roles
        $activeUsers = array_filter($users, function ($u) {
            return ($u['status'] == 1 || $u['status'] === '1');
        });

        if (empty($activeUsers)) {
            $response['message'] = "Your account is not active. Please contact the administrator.";
            return $this->response->setJSON($response);
        }

        // Step 3: Handle multiple roles
        $uniqueRoles = array_unique(array_map(function ($u) {
            return strtolower(trim($u['user_designation'] ?? ''));
        }, $activeUsers));

        if (count($uniqueRoles) > 1) {
            $roles = [];
            foreach ($activeUsers as $u) {
                $roles[] = [
                    'id' => $u['user_id'],
                    'designation' => $u['user_designation'] ?? 'No Role'
                ];
            }
            return $this->response->setJSON([
                'status' => 2, // Multiple roles found
                'message' => 'Multiple roles found. Please select one to continue.',
                'roles' => $roles
            ]);
        }

        // Step 4: Direct login for single role (or same roles across multiple entries)
        $userData = reset($activeUsers);
        return $this->create_user_session($userData);
    }

    public function process_role_login()
    {
        if ($this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid request']);
        }

        $userId = $this->request->getVar('user_id');
        $email = $this->request->getVar('email');

        if (!$userId) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Role selection required']);
        }

        $db = db_connect();
        $userData = $db->table("alert_users")
            ->where(['user_id' => $userId, 'user_email' => $email, 'status' => 1])
            ->get()
            ->getRowArray();

        if (!$userData) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Invalid user or role']);
        }

        return $this->create_user_session($userData);
    }

    private function create_user_session($userData)
    {
        $this->session->set("details", $userData['user_designation'] ?? '');

        $_SESSION['role'] = $userData['user_designation'] ?? '';
        $_SESSION['admin_flag'] = $userData['admin_flag'] ?? 0;
        $_SESSION['user_id'] = $userData['user_id'] ?? '';
        $_SESSION['login_id'] = $userData['user_id'] ?? '';
        $_SESSION['customer_id'] = "0"; // Default customer_id for BaseController
        $_SESSION['fcm_id'] = $userData['fcm_id'] ?? '';
        $_SESSION['country'] = $userData['user_emp_country'] ?? '';
        $_SESSION['user_name'] = $userData['user_name'] ?? '';
        $_SESSION['user_email'] = $userData['user_email'] ?? '';
        $_SESSION['user_designation'] = $userData['user_designation'] ?? '';
        $_SESSION['user_emp_code'] = $userData['user_emp_code'] ?? '';
        $_SESSION['user_contact'] = $userData['user_contact'] ?? '';
        $_SESSION['user_emp_zone'] = $userData['user_emp_zone'] ?? '';
        $_SESSION['employee_reporting_to'] = $userData['employee_reporting_to'] ?? 0;

        $_SESSION['region_id'] = $userData['region_id'] ?? null;
        $_SESSION['cluster_id'] = $userData['cluster_id'] ?? null;
        $_SESSION['location_id'] = $userData['location_id'] ?? null;

        $_SESSION['user_region'] = $userData['user_region'] ?? '';
        $_SESSION['user_cluster'] = $userData['user_cluster'] ?? '';
        $_SESSION['user_location'] = $userData['user_location'] ?? '';
        $_SESSION['user_emp_country'] = $userData['user_emp_country'] ?? '';
        $_SESSION['login_time'] = date('Y-m-d H:i:s');

        // Multi-Site Access Control (ACL) Population
        $userDesignation = strtolower(trim($userData['user_designation'] ?? ''));
        $isAdminFlag = !empty($userData['admin_flag']) && $userData['admin_flag'] == 1;
        $isRestrictedRole = in_array($userDesignation, ['cluster manager', 'account manager']) && !$isAdminFlag;

        $userAcl = [
            'is_restricted' => $isRestrictedRole,
            'can_view_all' => !$isRestrictedRole,
            'oe_client_ids' => [],
            'oe_site_names' => [],
            'hse_client_ids' => [],
            'hse_site_names' => [],
            'allocated_clusters' => []
        ];

        if ($isRestrictedRole) {
            $db = db_connect();
            $mappings = $db->table('alert_user_client_mapping')
                           ->where('user_id', $userData['user_id'])
                           ->where('status', 1)
                           ->get()->getResultArray();

            $oeIds = [];
            $oeSites = [];
            $hseIds = [];
            $hseSites = [];
            $clusters = [];

            foreach ($mappings as $m) {
                if ($m['client_type'] === 'OE') {
                    $oeIds[] = (int)$m['client_id'];
                    if (!empty($m['site_name'])) {
                        $oeSites[] = trim($m['site_name']);
                    }
                } elseif ($m['client_type'] === 'HSE') {
                    $hseIds[] = (int)$m['client_id'];
                    if (!empty($m['site_name'])) {
                        $hseSites[] = trim($m['site_name']);
                    }
                }
                if (!empty($m['cluster_name'])) {
                    $clusters[] = trim($m['cluster_name']);
                }
            }

            // Fallback for legacy setups if alert_user_client_mapping has no records for this user
            if (empty($mappings)) {
                if (!empty($userData['user_cluster'])) {
                    $clusters[] = trim($userData['user_cluster']);
                    $oeFallback = $db->table('alert_client')->where('cluster', $userData['user_cluster'])->where('status !=', 2)->get()->getResultArray();
                    foreach ($oeFallback as $c) {
                        $oeIds[] = (int)$c['client_id'];
                        $oeSites[] = trim($c['client_name']);
                    }
                    $hseFallback = $db->table('alert_hse_client_master')->where('cluster', $userData['user_cluster'])->where('status !=', 2)->get()->getResultArray();
                    foreach ($hseFallback as $c) {
                        $hseIds[] = (int)$c['client_id'];
                        $hseSites[] = trim($c['client_name']);
                    }
                }
                if (!empty($userData['user_location'])) {
                    $oeLoc = $db->table('alert_client')->where('client_name', $userData['user_location'])->where('status !=', 2)->get()->getResultArray();
                    foreach ($oeLoc as $c) {
                        $oeIds[] = (int)$c['client_id'];
                        $oeSites[] = trim($c['client_name']);
                    }
                    $hseLoc = $db->table('alert_hse_client_master')->where('client_name', $userData['user_location'])->where('status !=', 2)->get()->getResultArray();
                    foreach ($hseLoc as $c) {
                        $hseIds[] = (int)$c['client_id'];
                        $hseSites[] = trim($c['client_name']);
                    }
                }
            }

            $userAcl['oe_client_ids'] = array_values(array_unique($oeIds));
            $userAcl['oe_site_names'] = array_values(array_unique($oeSites));
            $userAcl['hse_client_ids'] = array_values(array_unique($hseIds));
            $userAcl['hse_site_names'] = array_values(array_unique($hseSites));
            $userAcl['allocated_clusters'] = array_values(array_unique($clusters));
        }

        $_SESSION['user_acl'] = $userAcl;

        $agent = $this->request->getUserAgent();
        $browser = $agent->getBrowser() . ' ' . $agent->getVersion();
        $device = $agent->isMobile() ? $agent->getMobile() : 'Desktop (' . $agent->getPlatform() . ')';

        $db = db_connect();
        $db->table('alert_login_history')->insert([
            'user_id' => $userData['user_id'],
            'session_id' => session_id(),
            'login_time' => date('Y-m-d H:i:s'),
            'last_activity' => date('Y-m-d H:i:s'),
            'ip_address' => $this->request->getIPAddress(),
            'browser' => $browser,
            'device_type' => $device,
            'status' => 'Success'
        ]);

        return $this->response->setJSON([
            'status' => 1,
            'message' => 'Login successful',
            'url' => base_url('Customer/Audit_dashboard/OE_Audit')
        ]);
    }

    public function reset_password()
    {
        return view('Layout/reset_password');
    }
    public function signup($admin_flag = "2")
    {
        $data['admin_flag'] = $admin_flag;
        $data['action'] = base_url("login/accept_signup");
        return view('Layout/signup', $data);
    }
    public function accept_signup($admin_flag = "2")
    {
        $responce = [];
        $request = service('request');
        $postData = $request->getVar();
        $db = db_connect();
        $ads_user = $db->table('ads_user');

        $ads_user->where("email_id", $postData['email']);
        $temp = $ads_user->get()->getResultArray();
        if (count($temp) > 0) {
            $responce['status'] = 0;
            $responce['message'] = "Opps this email is already registed with us contact to admin!";
        } else {
            if ($admin_flag == "2" || $admin_flag == "3") {
                $responce['status'] = 1;
                $responce['message'] = "You have successfully registered! Your profile under review.";
                $in['email_id'] = $postData['email'];
                $in['first_name'] = $postData['first_name'];
                $in['last_name'] = $postData['last_name'];
                $in['user_password'] = $postData['password'];
                $in['status'] = 0;
                $in['admin_flag'] = $admin_flag;

                $ads_user->insert($in);

            } else {
                $responce['status'] = 0;
                $responce['message'] = "You are unable register.";

            }
        }
        $responce['url'] = base_url("login/signup");
        return $this->response->setJSON($responce);

    }
    public function log_out()
    {
        $db = db_connect();
        
        // Close current session by session_id
        if (session_id()) {
            $db->table('alert_login_history')
               ->where('session_id', session_id())
               ->update([
                   'logout_time' => date('Y-m-d H:i:s'),
                   'logout_reason' => 'Manual Logout'
               ]);
        }
        
        // Close any other dangling sessions for this user
        $userId = session('user_id');
        if ($userId) {
            $db->table('alert_login_history')
               ->where('user_id', $userId)
               ->where('logout_time IS NULL')
               ->update([
                   'logout_time' => date('Y-m-d H:i:s'),
                   'logout_reason' => 'Manual Logout'
               ]);
        }
        
        $this->session->destroy();
        return redirect()->to(base_url('Login'));
    }
    
    public function heartbeat()
    {
        if ($this->request->isAJAX() && session_id()) {
            $db = db_connect();
            $db->table('alert_login_history')
               ->where('session_id', session_id())
               ->update(['last_activity' => date('Y-m-d H:i:s')]);
            return $this->response->setJSON(['status' => 'ok']);
        }
        return $this->response->setStatusCode(403, 'Invalid request');
    }
    public function direct_login($type = "", $id = "")
    {
        if (isset($id) && $id != "") {
            if (isset($_SESSION['role']) && ($_SESSION['role'] == "super_admin" || $_SESSION['role'] == "admin")) {
                $this->session->set("temp_role", $_SESSION['role']);
                if ($type == "admin") {
                    $db = db_connect();
                    $builder = $db->table('ads_user');
                    $builder->where("user_id", $id);
                    $datas = $builder->get()->getResultArray();
                    if (count($datas) > 0) {
                        $row = $datas[0];
                        $this->session->set("user_id", $id);
                        $this->session->set("customer_id", "0");
                        $this->session->set("details", $row);
                        $this->session->set("role", "admin");
                        return redirect()->to(base_url("Customer/Audit_dashboard/OE_Audit"));

                    }
                } else if ($type == "customer") {
                    $db = db_connect();
                    $builder = $db->table('ads_customer');
                    $builder->where("customer_id", $id);
                    $datas = $builder->get()->getResultArray();
                    if (count($datas) > 0) {
                        // if()

                        $ads_customer_user = $db->table('ads_customer_user');
                        $ads_customer_user->where("customer_id", $id);
                        if ($_SESSION['role'] == "admin") {
                            $ads_customer_user->where("user_id", $_SESSION['user_id']);
                        }
                        $ads_customer_user_row = $ads_customer_user->get()->getResultArray();

                        if (count($ads_customer_user_row) > 0) {
                            $row = $datas[0];
                            $this->session->set("user_id", $id);
                            $this->session->set("customer_id", $id);
                            $this->session->set("details", $row);
                            $this->session->set("role", "customer");
                            return redirect()->to(base_url("Customer/Audit_dashboard/OE_Audit"));
                        } else {
                            // return redirect()->to(base_url("Customer/Audit_dashboard/OE_Audit"));
                            echo "<script>alert('Sorry Unable to login by user');window.history.back()</script>";
                        }


                    }
                    // temp_role = "user";
                    // $this->session->set("user_id",$row1['user_id']);
                    // $this->session->set("customer_id",$row['customer_id']);
                    // $this->session->set("details",$row);
                    // $this->session->set("role","customer");
                }
            } else {

            }
        }
        //         echo "<pre>";
        // print_r($_SESSION);

    }
    public function back_to_role()
    {

        if (isset($_SESSION['role']) && ($_SESSION['role'] == "admin" || $_SESSION['role'] == "customer")) {
            $type = $_SESSION['temp_role'];
            if ($type != "customer") {
                switch ($type) {
                    case "super_admin":
                        $this->session->set("role", $type);
                        $this->session->set("user_id", "");
                        $this->session->set("customer_id", "");
                        $this->session->set("details", "");
                        $this->session->set("temp_role", "");
                        return redirect()->to(base_url("Customer/Audit_dashboard/OE_Audit"));

                        break;
                    case "admin":
                        $db = db_connect();
                        $builder = $db->table('ads_user');
                        $builder->where("user_id", $_SESSION['user_id']);
                        $datas = $builder->get()->getResultArray();
                        if (count($datas) > 0) {
                            $row = $datas[0];
                            // $this->session->set("role",$type);
                            // $this->session->set("user_id",$_SESSION['user_id']);
                            $this->session->set("customer_id", "0");
                            $this->session->set("details", $row);
                            $this->session->set("temp_role", "");
                            // return redirect()->to(base_url("Customer/Audit_dashboard/OE_Audit"));

                        }
                        return redirect()->to(base_url("Login"));
                        break;
                    default:
                        return redirect()->to(base_url("Login"));
                        break;
                }
                // $this->session->set("user_id",$row['user_id']);
                // $this->session->set("customer_id","");
                // $this->session->set("details",$row);
            } else if ($type == "customer") {
                // temp_role = "user";
                // $this->session->set("user_id",$row1['user_id']);
                // $this->session->set("customer_id",$row['customer_id']);
                // $this->session->set("details",$row);
                // $this->session->set("role","customer");
                echo "Restricted access";
            }
        } else {
            echo "Please Login";
        }
        echo "<pre>";
        print_r($_SESSION);
    }
    public function check()
    {
        echo "<pre>";
        print_r($_SESSION);
    }


    /*Email Test Start*/

    public function testemail()
    {
        helper('email_service');
        $setTo = "sruajk4437@gmail.com";
        $setSubject = "Test email";
        $thank['name'] = "Akash";

        $message = "this test email";
        sendSystemEmail($setTo, $setSubject, $message);
    }

    /*Email Test End*/

    public function viewmail()
    {
        return view('Emails/thank_you_email');
    }

}
