<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class UserFeedback extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'designation_acl']);
        $this->db = Database::connect();
    }

    /**
     * Display Super Admin feedback management dashboard
     */
    public function index()
    {
        if (!isSuperAdmin()) {
            return redirect()->to(base_url())->with('error', 'Unauthorized access.');
        }

        // Summary statistics
        $totalSubmissions = $this->db->table('alert_user_feedback')->countAllResults();
        
        $avgRow = $this->db->table('alert_user_feedback')
            ->selectAvg('rating', 'avg_rating')
            ->get()
            ->getRowArray();
        $avgRating = !empty($avgRow['avg_rating']) ? round((float)$avgRow['avg_rating'], 2) : 0;

        // Breakdown by rating
        $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $ratingRows = $this->db->table('alert_user_feedback')
            ->select('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->get()
            ->getResultArray();
        foreach ($ratingRows as $r) {
            $ratingCounts[(int)$r['rating']] = (int)$r['cnt'];
        }

        $data = [
            'total_submissions' => $totalSubmissions,
            'avg_rating' => $avgRating,
            'rating_counts' => $ratingCounts,
            'categories' => ['UI/Design', 'Functionality', 'Performance', 'Reports', 'Login/Access', 'Other']
        ];

        return view('Admin/user_feedback_view', $data);
    }

    /**
     * DataTables AJAX endpoint with server-side pagination, searching & filtering
     */
    public function datatable_ajax()
    {
        if (!isSuperAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $request = service('request');

        $ratingFilter = $request->getVar('rating');
        $categoryFilter = $request->getVar('category');
        $fromDate = $request->getVar('from_date');
        $toDate = $request->getVar('to_date');
        $searchValue = trim((string)($request->getVar('search')['value'] ?? ''));

        $builder = $this->db->table('alert_user_feedback f')
            ->select('f.feedback_id, f.user_id, f.rating, f.category, f.comments, f.suggestion, f.submitted_at, u.user_name, u.user_emp_code, u.user_designation, u.user_email')
            ->join('alert_users u', 'u.user_id = f.user_id', 'left');

        // Total count before filters
        $totalRecords = $this->db->table('alert_user_feedback')->countAllResults();

        // Apply filters
        if (!empty($ratingFilter)) {
            $builder->where('f.rating', (int)$ratingFilter);
        }
        if (!empty($categoryFilter)) {
            $builder->where('f.category', $categoryFilter);
        }
        if (!empty($fromDate)) {
            $builder->where('DATE(f.submitted_at) >=', $fromDate);
        }
        if (!empty($toDate)) {
            $builder->where('DATE(f.submitted_at) <=', $toDate);
        }

        // Global search
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('u.user_name', $searchValue)
                ->orLike('u.user_emp_code', $searchValue)
                ->orLike('u.user_designation', $searchValue)
                ->orLike('f.category', $searchValue)
                ->orLike('f.comments', $searchValue)
                ->orLike('f.suggestion', $searchValue)
                ->groupEnd();
        }

        $filteredRecords = $builder->countAllResults(false);

        // Sorting
        $order = $request->getVar('order');
        $columns = [
            0 => 'f.feedback_id',
            1 => 'u.user_name',
            2 => 'u.user_designation',
            3 => 'f.rating',
            4 => 'f.category',
            5 => 'f.comments',
            6 => 'f.suggestion',
            7 => 'f.submitted_at'
        ];

        if (!empty($order) && isset($order[0]['column'])) {
            $colIdx = (int)$order[0]['column'];
            $colDir = strtolower($order[0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $sortCol = $columns[$colIdx] ?? 'f.feedback_id';
            $builder->orderBy($sortCol, $colDir);
        } else {
            $builder->orderBy('f.feedback_id', 'DESC');
        }

        // Pagination
        $start = (int)($request->getVar('start') ?? 0);
        $length = (int)($request->getVar('length') ?? 10);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $dataRows = $builder->get()->getResultArray();

        $rows = [];
        $srNo = $start + 1;
        foreach ($dataRows as $item) {
            // Star rating display
            $stars = '<div class="d-inline-flex gap-1 text-warning">';
            for ($s = 1; $s <= 5; $s++) {
                if ($s <= (int)$item['rating']) {
                    $stars .= '<i class="fas fa-star text-warning fs-7"></i>';
                } else {
                    $stars .= '<i class="far fa-star text-muted fs-7"></i>';
                }
            }
            $stars .= ' <span class="ms-1 fw-bold text-dark fs-8">(' . $item['rating'] . '/5)</span></div>';

            // User Info
            $displayName = !empty($item['user_name']) ? htmlspecialchars($item['user_name']) : 'User #' . $item['user_id'];
            $empCode = !empty($item['user_emp_code']) ? '<span class="badge badge-light-secondary fs-8 ms-1">' . htmlspecialchars($item['user_emp_code']) . '</span>' : '';
            $userInfo = '<div class="d-flex flex-column">
                <span class="fw-bold text-dark fs-7">' . $displayName . ' ' . $empCode . '</span>
                <span class="text-muted fs-8">' . htmlspecialchars($item['user_email'] ?? '') . '</span>
            </div>';

            // Category badge
            $badgeColor = match($item['category']) {
                'UI/Design' => 'badge-light-primary',
                'Functionality' => 'badge-light-success',
                'Performance' => 'badge-light-danger',
                'Reports' => 'badge-light-info',
                'Login/Access' => 'badge-light-warning',
                default => 'badge-light-secondary'
            };
            $categoryBadge = '<span class="badge ' . $badgeColor . ' fw-semibold fs-7 px-3 py-1">' . htmlspecialchars($item['category']) . '</span>';

            $rows[] = [
                'sr_no' => $srNo++,
                'user_info' => $userInfo,
                'role' => htmlspecialchars(!empty($item['user_designation']) ? $item['user_designation'] : '-'),
                'rating' => $stars,
                'category' => $categoryBadge,
                'comments' => '<div style="max-width: 320px; white-space: normal; word-break: break-word;">' . nl2br(htmlspecialchars($item['comments'])) . '</div>',
                'suggestion' => !empty($item['suggestion']) ? '<div style="max-width: 260px; white-space: normal; word-break: break-word;">' . nl2br(htmlspecialchars($item['suggestion'])) . '</div>' : '<span class="text-muted">-</span>',
                'submitted_at' => '<span class="fs-7 text-nowrap">' . date('d-M-Y H:i', strtotime($item['submitted_at'])) . '</span>'
            ];
        }

        return $this->response->setJSON([
            'draw' => (int)($request->getVar('draw') ?? 1),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $rows
        ]);
    }

    /**
     * Export all filtered feedback to CSV
     */
    public function export_csv()
    {
        if (!isSuperAdmin()) {
            return redirect()->to(base_url())->with('error', 'Unauthorized access.');
        }

        $request = service('request');

        $ratingFilter = $request->getGet('rating');
        $categoryFilter = $request->getGet('category');
        $fromDate = $request->getGet('from_date');
        $toDate = $request->getGet('to_date');

        $builder = $this->db->table('alert_user_feedback f')
            ->select('f.feedback_id, f.user_id, f.rating, f.category, f.comments, f.suggestion, f.submitted_at, u.user_name, u.user_emp_code, u.user_designation, u.user_email')
            ->join('alert_users u', 'u.user_id = f.user_id', 'left');

        if (!empty($ratingFilter)) {
            $builder->where('f.rating', (int)$ratingFilter);
        }
        if (!empty($categoryFilter)) {
            $builder->where('f.category', $categoryFilter);
        }
        if (!empty($fromDate)) {
            $builder->where('DATE(f.submitted_at) >=', $fromDate);
        }
        if (!empty($toDate)) {
            $builder->where('DATE(f.submitted_at) <=', $toDate);
        }

        $builder->orderBy('f.feedback_id', 'DESC');
        $records = $builder->get()->getResultArray();

        $filename = 'User_Feedback_Export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        fputcsv($output, [
            'Sr No',
            'User Name',
            'Emp Code',
            'Email',
            'Role / Designation',
            'Rating (1-5)',
            'Category',
            'Comments',
            'Improvement Suggestions',
            'Submitted At'
        ]);

        $srNo = 1;
        foreach ($records as $row) {
            fputcsv($output, [
                $srNo++,
                $row['user_name'] ?? 'User #' . $row['user_id'],
                $row['user_emp_code'] ?? '',
                $row['user_email'] ?? '',
                $row['user_designation'] ?? '',
                $row['rating'],
                $row['category'],
                $row['comments'],
                $row['suggestion'] ?? '',
                date('d-M-Y H:i:s', strtotime($row['submitted_at']))
            ]);
        }

        fclose($output);
        exit;
    }
}
