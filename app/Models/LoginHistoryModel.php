<?php

namespace App\Models;

use CodeIgniter\Model;

class LoginHistoryModel extends Model
{
    protected $table            = 'alert_login_history';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = [
        'user_id',
        'session_id',
        'login_time',
        'last_activity',
        'logout_time',
        'ip_address',
        'browser',
        'device_type',
        'status',
        'logout_reason'
    ];

    public function cleanupExpiredSessions()
    {
        // 1 hour timeout for inactive sessions
        $timeout = date('Y-m-d H:i:s', strtotime("-60 minutes"));
        
        $db = \Config\Database::connect();
        $db->table($this->table)
           ->set('logout_time', 'last_activity', false)
           ->set('logout_reason', 'Session Expired')
           ->where('logout_time IS NULL')
           ->where('last_activity <', $timeout)
           ->update();
    }

    public function getOnlineUsers($timeoutMinutes = 15, $filters = [])
    {
        $this->cleanupExpiredSessions();
        
        // No longer relying strictly on $timeoutMinutes because cleanup handles it (1 hour),
        // but we can still enforce a display timeout if desired, or just rely on logout_time IS NULL
        $builder = $this->select('alert_login_history.*, alert_users.user_name, alert_users.user_emp_code, alert_users.user_email, alert_users.user_designation, alert_users.user_location, alert_users.user_region, alert_users.user_cluster, alert_users.employee_reporting_to')
            ->join('alert_users', 'alert_users.user_id = alert_login_history.user_id', 'left')
            ->where('alert_login_history.logout_time IS NULL')
            ->where('alert_login_history.status', 'Success');
            
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
        
        if (!empty($filters['login_date'])) {
            $builder->like('alert_login_history.login_time', $filters['login_date']);
        }
        
        if (!empty($filters['last_activity_date'])) {
            $builder->like('alert_login_history.last_activity', $filters['last_activity_date']);
        }

        return $builder->orderBy('alert_login_history.last_activity', 'DESC')
            ->findAll();
    }

    // Get login history with filters
    public function getLoginHistory($filters = [])
    {
        $this->cleanupExpiredSessions();
        
        $builder = $this->select('alert_login_history.*, 
            alert_users.user_name, 
            alert_users.user_emp_code, 
            alert_users.user_email, 
            alert_users.user_designation, 
            alert_users.user_region,
            TIMESTAMPDIFF(MINUTE, alert_login_history.login_time, IFNULL(alert_login_history.logout_time, NOW())) as session_duration_minutes')
            ->join('alert_users', 'alert_users.user_id = alert_login_history.user_id', 'left');

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
        
        if (!empty($filters['login_date'])) {
            $builder->like('alert_login_history.login_time', $filters['login_date']);
        }
        
        if (!empty($filters['last_activity_date'])) {
            $builder->like('alert_login_history.last_activity', $filters['last_activity_date']);
        }

        $builder->orderBy('alert_login_history.login_time', 'DESC');
        
        return $builder->findAll();
    }
}
