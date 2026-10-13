<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class AppStatusFeedback extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Check if the authenticated user has already submitted the application status feedback
     * Returns: ['status' => 1, 'has_submitted' => bool, 'skipped_this_session' => bool]
     */
    public function check_status()
    {
        $session = session();
        $userId = (int)($session->get('user_id') ?? 0);

        // Don't show to unauthenticated guests (if user_id is empty or not in session)
        // Note: For super_admin user_id is 0 or '0' but role is 'admin', so we check role/login_id
        $role = $session->get('role') ?? '';
        $isLoggedIn = !empty($session->get('user_name')) || !empty($role);

        if (!$isLoggedIn) {
            return $this->response->setJSON([
                'status' => 0,
                'has_submitted' => true,
                'message' => 'Unauthenticated'
            ]);
        }

        // Check if skipped in current session
        $skippedThisSession = (bool)($session->get('app_status_skipped_session') ?? false);

        // Check database if user already submitted application status feedback
        $existing = $this->db->table('alert_app_status_feedback')
            ->where('user_id', $userId)
            ->countAllResults();

        $hasSubmitted = ($existing > 0);

        return $this->response->setJSON([
            'status' => 1,
            'has_submitted' => $hasSubmitted,
            'skipped_this_session' => $skippedThisSession
        ]);
    }

    /**
     * Mark application status feedback as skipped for current session only
     */
    public function skip()
    {
        $session = session();
        $session->set('app_status_skipped_session', true);

        return $this->response->setJSON([
            'status' => 1,
            'message' => 'Application status feedback dismissed for current session'
        ]);
    }

    /**
     * Submit application status feedback (one-time only per user)
     */
    public function submit()
    {
        $session = session();
        $userId = (int)($session->get('user_id') ?? 0);
        $role = $session->get('role') ?? '';
        $isLoggedIn = !empty($session->get('user_name')) || !empty($role);

        if (!$isLoggedIn) {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Please log in to submit application status feedback.'
            ]);
        }

        // Check if already submitted
        $alreadySubmitted = $this->db->table('alert_app_status_feedback')
            ->where('user_id', $userId)
            ->countAllResults();

        if ($alreadySubmitted > 0) {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'You have already submitted your application status feedback. Thank you!'
            ]);
        }

        $validAnswers = ['Yes', 'No', 'NA'];
        $insertData = [
            'user_id' => $userId,
            'submitted_at' => date('Y-m-d H:i:s')
        ];

        // Validate all 12 questions
        for ($i = 1; $i <= 12; $i++) {
            $key = 'q' . $i;
            $val = trim((string)($this->request->getPost($key) ?? ''));
            if (!in_array($val, $validAnswers, true)) {
                return $this->response->setJSON([
                    'status' => 0,
                    'message' => "Please answer question {$i} before submitting."
                ]);
            }
            $insertData[$key] = $val;
        }

        $comments = trim((string)($this->request->getPost('comments') ?? ''));
        $insertData['comments'] = !empty($comments) ? $comments : null;

        try {
            $this->db->table('alert_app_status_feedback')->insert($insertData);

            // Mark session as completed so modal never appears again
            $session->set('app_status_submitted', true);
            $session->remove('app_status_skipped_session');

            return $this->response->setJSON([
                'status' => 1,
                'message' => 'Thank you! Your Application Status Feedback has been submitted successfully.'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'AppStatusFeedback submit error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Failed to save application status feedback. Please try again.'
            ]);
        }
    }
}
