<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use Config\Database;

class Feedback extends BaseController
{
    protected $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    /**
     * Check if the authenticated user has already submitted feedback
     * Returns: ['status' => 1, 'has_submitted' => bool, 'skipped_this_session' => bool]
     */
    public function check_status()
    {
        $session = session();
        $userId = (int)($session->get('user_id') ?? 0);

        if ($userId <= 0) {
            return $this->response->setJSON([
                'status' => 0,
                'has_submitted' => true, // Don't show popup to unauthenticated guests
                'message' => 'Unauthenticated'
            ]);
        }

        // Check if skipped in current session
        $skippedThisSession = (bool)($session->get('feedback_skipped_session') ?? false);

        // Check database if user already submitted feedback
        $existing = $this->db->table('alert_user_feedback')
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
     * Mark feedback as skipped for the current session only
     */
    public function skip()
    {
        $session = session();
        $session->set('feedback_skipped_session', true);

        return $this->response->setJSON([
            'status' => 1,
            'message' => 'Feedback dismissed for current session'
        ]);
    }

    /**
     * Submit user feedback (multiple submissions allowed per user)
     */
    public function submit()
    {
        $session = session();
        $userId = (int)($session->get('user_id') ?? 0);

        if ($userId <= 0) {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Please log in to submit feedback.'
            ]);
        }

        $rating = (int)($this->request->getPost('rating') ?? 0);
        $category = trim((string)($this->request->getPost('category') ?? ''));
        $comments = trim((string)($this->request->getPost('comments') ?? ''));
        $suggestion = trim((string)($this->request->getPost('suggestion') ?? ''));

        // Validation
        if ($rating < 1 || $rating > 5) {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Please provide a valid rating between 1 and 5 stars.'
            ]);
        }

        $validCategories = ['UI/Design', 'Functionality', 'Performance', 'Reports', 'Login/Access', 'Other'];
        if (empty($category) || !in_array($category, $validCategories)) {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Please select a valid feedback category.'
            ]);
        }

        if (empty($comments)) {
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Please enter your comments or feedback.'
            ]);
        }

        try {
            $insertData = [
                'user_id' => $userId,
                'rating' => $rating,
                'category' => $category,
                'comments' => $comments,
                'suggestion' => !empty($suggestion) ? $suggestion : null,
                'submitted_at' => date('Y-m-d H:i:s')
            ];

            $this->db->table('alert_user_feedback')->insert($insertData);

            // Mark session as complete so popup never appears again
            $session->set('feedback_submitted', true);
            $session->remove('feedback_skipped_session');

            return $this->response->setJSON([
                'status' => 1,
                'message' => 'Thank you! Your feedback has been submitted successfully.'
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'Feedback submit error: ' . $e->getMessage());
            return $this->response->setJSON([
                'status' => 0,
                'message' => 'Failed to save feedback. Please try again.'
            ]);
        }
    }
}
