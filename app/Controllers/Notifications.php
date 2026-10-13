<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use Config\Database;

class Notifications extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
        $this->response = service('response');
        helper(['url', 'date']);
    }

    /**
     * AJAX endpoint to fetch unread notifications count and recent notification items
     */
    public function getNavbarNotifications()
    {
        $userId = session('user_id');
        if (empty($userId)) {
            return $this->response->setJSON([
                'status' => 'unauthorized',
                'unread_count' => 0,
                'notifications' => []
            ]);
        }

        try {
            $unreadCount = $this->db->table('alert_navbar_notifications')
                ->where('user_id', $userId)
                ->where('is_read', 0)
                ->countAllResults();

            $notifications = $this->db->table('alert_navbar_notifications')
                ->where('user_id', $userId)
                ->orderBy('id', 'DESC')
                ->limit(15)
                ->get()
                ->getResultArray();

            $formatted = [];
            foreach ($notifications as $n) {
                $createdTimestamp = strtotime($n['created_at']);
                $timeAgo = $this->formatTimeAgo($createdTimestamp);

                $reviewBadge = ($n['review_status'] === 'Cluster Review') ? 'badge-light-warning' : 'badge-light-danger';
                $moduleBadge = ($n['module'] === 'OE') ? 'badge-light-primary' : 'badge-light-success';

                $formatted[] = [
                    'id' => (int)$n['id'],
                    'module' => $n['module'],
                    'nc_reference' => $n['nc_reference'],
                    'client_site' => $n['client_site'],
                    'review_status' => $n['review_status'],
                    'title' => $n['title'],
                    'message' => $n['message'],
                    'target_url' => base_url('notifications/read_and_go/' . $n['id']),
                    'is_read' => (int)$n['is_read'],
                    'time_ago' => $timeAgo,
                    'created_at' => $n['created_at'],
                    'review_badge' => $reviewBadge,
                    'module_badge' => $moduleBadge
                ];
            }

            return $this->response->setJSON([
                'status' => 'success',
                'unread_count' => (int)$unreadCount,
                'notifications' => $formatted
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Error in getNavbarNotifications: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 'error',
                'unread_count' => 0,
                'notifications' => []
            ]);
        }
    }

    /**
     * Mark a single notification as read via AJAX
     */
    public function markAsRead($id = null)
    {
        $userId = session('user_id');
        if (empty($userId) || empty($id)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Invalid request']);
        }

        $this->db->table('alert_navbar_notifications')
            ->where('id', (int)$id)
            ->where('user_id', $userId)
            ->update([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * Mark notification as read and redirect user to target NC view/tracker
     */
    public function readAndGo($id = null)
    {
        $userId = session('user_id');
        if (empty($userId) || empty($id)) {
            return redirect()->to(base_url('/'));
        }

        $notif = $this->db->table('alert_navbar_notifications')
            ->where('id', (int)$id)
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if ($notif) {
            $this->db->table('alert_navbar_notifications')
                ->where('id', (int)$id)
                ->where('user_id', $userId)
                ->update([
                    'is_read' => 1,
                    'read_at' => date('Y-m-d H:i:s')
                ]);

            if (!empty($notif['target_url'])) {
                return redirect()->to($notif['target_url']);
            }
        }

        return redirect()->to(base_url('/'));
    }

    /**
     * Mark all notifications as read for current user
     */
    public function markAllAsRead()
    {
        $userId = session('user_id');
        if (empty($userId)) {
            return $this->response->setJSON(['status' => 'unauthorized']);
        }

        $this->db->table('alert_navbar_notifications')
            ->where('user_id', $userId)
            ->where('is_read', 0)
            ->update([
                'is_read' => 1,
                'read_at' => date('Y-m-d H:i:s')
            ]);

        return $this->response->setJSON(['status' => 'success']);
    }

    /**
     * Helper to render human-readable relative time
     */
    private function formatTimeAgo(int $timestamp): string
    {
        $diff = time() - $timestamp;
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return $mins . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return date('d M', $timestamp);
        }
    }
}
