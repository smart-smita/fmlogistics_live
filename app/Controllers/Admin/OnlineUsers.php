<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LoginHistoryModel;

class OnlineUsers extends BaseController
{
    public function __construct()
    {
        helper(['form', 'url', 'designation_acl']);
    }

    public function index()
    {
        helper('designation_acl');
        
        if (!isSuperAdmin() && !isHigherAuthority() && !isClusterManager()) {
            return redirect()->to(base_url())->with('error', 'Unauthorized access.');
        }

        $db = \Config\Database::connect();
        $builder = $db->table('alert_users')
                    ->select('user_designation')
                    ->distinct()
                    ->where('user_designation !=', '')
                    ->where('user_designation IS NOT NULL')
                    ->where('status !=', 2);
                    
        if (isClusterManager()) {
            $allowedUsers = $this->getClusterManagerAllowedUserList();
            if (!empty($allowedUsers)) {
                $builder->whereIn('user_name', $allowedUsers);
            } else {
                $builder->where('1=0', null, false);
            }
        }

        $roles = $builder->get()->getResultArray();
        $data['roles'] = array_column($roles, 'user_designation');
        
        return view('Admin/online_users', $data);
    }
    
    public function get_users_by_role()
    {
        helper('designation_acl');
        if (!$this->request->isAJAX() || (!isSuperAdmin() && !isHigherAuthority() && !isClusterManager())) {
            return $this->response->setStatusCode(403, 'Invalid request');
        }

        $role = $this->request->getGet('role');
        $db = \Config\Database::connect();
        
        $builder = $db->table('alert_users')
                      ->select('user_name')
                      ->distinct()
                      ->where('user_name !=', '')
                      ->where('user_name IS NOT NULL')
                      ->where('status !=', 2);
                      
        if (!empty($role)) {
            $builder->where('user_designation', $role);
        }

        if (isClusterManager()) {
            $allowedUsers = $this->getClusterManagerAllowedUserList();
            if (!empty($allowedUsers)) {
                $builder->whereIn('user_name', $allowedUsers);
            } else {
                $builder->where('1=0', null, false);
            }
        }
        
        $users = $builder->orderBy('user_name', 'ASC')->get()->getResultArray();

        return $this->response->setJSON([
            'users' => $users
        ]);
    }

    public function get_online_users()
    {
        helper('designation_acl');
        if (!$this->request->isAJAX() || (!isSuperAdmin() && !isHigherAuthority() && !isClusterManager())) {
            return $this->response->setStatusCode(403, 'Invalid request');
        }

        $filters = [
            'role' => $this->request->getGet('role'),
            'user_name' => $this->request->getGet('user_name'),
            'login_date' => $this->request->getGet('login_date'),
            'last_activity_date' => $this->request->getGet('last_activity_date'),
        ];

        if (isClusterManager()) {
            $filters['allowed_users'] = $this->getClusterManagerAllowedUserList();
        }

        $model = new LoginHistoryModel();
        // 15 minutes timeout
        $onlineUsers = $model->getOnlineUsers(15, $filters);
        
        $totalRegistered = $this->getTotalRegisteredUsers($filters);
        $todaysLogins = $this->getTodaysLogins($filters);
        $totalActiveUsers = $this->getTotalActiveUsers($filters);

        return $this->response->setJSON([
            'data' => $onlineUsers,
            'summary' => [
                'online' => count($onlineUsers),
                'total_registered' => $totalRegistered,
                'todays_logins' => $todaysLogins,
                'total_active_users' => $totalActiveUsers
            ]
        ]);
    }

    public function get_login_history()
    {
        helper('designation_acl');
        if (!$this->request->isAJAX() || (!isSuperAdmin() && !isHigherAuthority() && !isClusterManager())) {
            return $this->response->setStatusCode(403, 'Invalid request');
        }
        
        $filters = [
            'role' => $this->request->getGet('role'),
            'user_name' => $this->request->getGet('user_name'),
            'login_date' => $this->request->getGet('login_date'),
            'last_activity_date' => $this->request->getGet('last_activity_date'),
        ];

        if (isClusterManager()) {
            $filters['allowed_users'] = $this->getClusterManagerAllowedUserList();
        }

        $model = new LoginHistoryModel();
        $history = $model->getLoginHistory($filters);

        return $this->response->setJSON([
            'data' => $history
        ]);
    }

    private function getTotalRegisteredUsers($filters = [])
    {
        $db = \Config\Database::connect();
        $builder = $db->table('alert_users');
        $builder = $this->applyFilters($builder, $filters, 'alert_users', true);
        $builder->where('alert_users.status', 1);
        return $builder->countAllResults();
    }

    private function getTodaysLogins($filters = [])
    {
        $db = \Config\Database::connect();
        $today = date('Y-m-d');
        $builder = $db->table('alert_login_history')
                      ->join('alert_users', 'alert_users.user_id = alert_login_history.user_id', 'left')
                      ->like('alert_login_history.login_time', $today);
        $builder = $this->applyFilters($builder, $filters, 'alert_login_history');
        return $builder->countAllResults();
    }

    private function getTotalActiveUsers($filters = [])
    {
        $db = \Config\Database::connect();
        $builder = $db->table('alert_login_history')
                      ->join('alert_users', 'alert_users.user_id = alert_login_history.user_id', 'left')
                      ->select('alert_login_history.user_id')
                      ->distinct();
        $builder = $this->applyFilters($builder, $filters, 'alert_login_history');
        return $builder->countAllResults();
    }
    
    private function applyFilters($builder, $filters, $mainTable, $usersOnly = false)
    {
        if (isset($filters['allowed_users']) && is_array($filters['allowed_users'])) {
            if (!empty($filters['allowed_users'])) {
                $builder->whereIn('alert_users.user_name', $filters['allowed_users']);
            } else {
                $builder->where('1=0', null, false);
            }
        }

        if (!empty($filters['role'])) {
            $builder->like('alert_users.user_designation', $filters['role']);
        }
        
        if (!empty($filters['user_name'])) {
            $builder->like('alert_users.user_name', $filters['user_name']);
        }
        
        if (!$usersOnly) {
            if (!empty($filters['login_date'])) {
                $builder->like('alert_login_history.login_time', $filters['login_date']);
            }
            
            if (!empty($filters['last_activity_date'])) {
                $builder->like('alert_login_history.last_activity', $filters['last_activity_date']);
            }
        }
        return $builder;
    }

    private function getClusterManagerAllowedUserList()
    {
        $db = \Config\Database::connect();
        $userName = getUserName();
        if (empty($userName)) {
            return [];
        }

        $cleanUser = strtolower(trim($userName));
        
        $assignedClusters = [];
        if (function_exists('getClusterManagerAssignedCluster')) {
            $assignedClusters = getClusterManagerAssignedCluster();
        }
        
        // 1. Account managers from alert_client
        $amFromClient = [];
        if ($db->fieldExists('account_manager', 'alert_client')) {
            $bClient = $db->table('alert_client')
                ->select('DISTINCT(account_manager) as am')
                ->where('status !=', 2)
                ->where('account_manager !=', '')
                ->where('account_manager IS NOT NULL');
            if (!empty($assignedClusters)) {
                $bClient->groupStart()
                    ->where('LOWER(TRIM(cluster))', $cleanUser)
                    ->orWhereIn('cluster', $assignedClusters)
                ->groupEnd();
            } else {
                $bClient->where('LOWER(TRIM(cluster))', $cleanUser);
            }
            $resClient = $bClient->get()->getResultArray();
            foreach ($resClient as $r) {
                if (!empty($r['am'])) $amFromClient[] = trim($r['am']);
            }
        }

        // 2. Account managers from alert_location_master
        $amFromLocation = [];
        if ($db->fieldExists('account_manager', 'alert_location_master')) {
            $bLoc = $db->table('alert_location_master')
                ->select('DISTINCT(account_manager) as am')
                ->where('status', 1)
                ->where('account_manager !=', '')
                ->where('account_manager IS NOT NULL');
            if (!empty($assignedClusters)) {
                $bLoc->groupStart()
                    ->where('LOWER(TRIM(cluster_name))', $cleanUser)
                    ->orWhereIn('cluster_name', $assignedClusters)
                ->groupEnd();
            } else {
                $bLoc->where('LOWER(TRIM(cluster_name))', $cleanUser);
            }
            $resLoc = $bLoc->get()->getResultArray();
            foreach ($resLoc as $r) {
                if (!empty($r['am'])) $amFromLocation[] = trim($r['am']);
            }
        }

        // 3. Account managers from alert_gemba_audits
        $amFromGembaAudits = [];
        if ($db->fieldExists('account_manager', 'alert_gemba_audits')) {
            $resGemba = $db->table('alert_gemba_audits')
                ->select('DISTINCT(account_manager) as am')
                ->where('status !=', 2)
                ->where('LOWER(TRIM(cluster_manager_spoc))', $cleanUser)
                ->where('account_manager !=', '')
                ->where('account_manager IS NOT NULL')
                ->get()->getResultArray();
            foreach ($resGemba as $r) {
                if (!empty($r['am'])) $amFromGembaAudits[] = trim($r['am']);
            }
        }

        // 4. Users in alert_users reporting to this Cluster Manager or in assigned clusters
        $bUsers = $db->table('alert_users')
            ->select('user_name')
            ->where('status !=', 2);
        
        $bUsers->groupStart();
            // Self
            $bUsers->where('LOWER(TRIM(user_name))', $cleanUser);
            
            // Direct reporting
            $bUsers->orWhere('LOWER(TRIM(employee_reporting_to))', $cleanUser);
            
            // User cluster match for Account Managers
            if (!empty($assignedClusters)) {
                $bUsers->orGroupStart()
                    ->whereIn('user_cluster', $assignedClusters)
                    ->whereIn('LOWER(TRIM(user_designation))', ['account manager', 'account_manager', 'engineer'])
                ->groupEnd();
            }
            
            // Account managers found from tables
            $allAmNames = array_unique(array_filter(array_merge($amFromClient, $amFromLocation, $amFromGembaAudits)));
            if (!empty($allAmNames)) {
                $bUsers->orWhereIn('user_name', $allAmNames);
            }
        $bUsers->groupEnd();

        $userRows = $bUsers->get()->getResultArray();
        $allowedUserNames = [];
        foreach ($userRows as $u) {
            if (!empty($u['user_name'])) {
                $allowedUserNames[] = trim($u['user_name']);
            }
        }
        
        $allowedUserNames[] = $userName;

        return array_values(array_unique($allowedUserNames));
    }
}
