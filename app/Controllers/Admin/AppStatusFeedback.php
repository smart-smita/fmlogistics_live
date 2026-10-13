<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class AppStatusFeedback extends BaseController
{
    protected $db;

    public function __construct()
    {
        helper(['form', 'url', 'designation_acl']);
        $this->db = Database::connect();
    }

    /**
     * Questions definition
     */
    public static function getQuestions(): array
    {
        return [
            1 => 'Is the OE Audit module working properly?',
            2 => 'Is the OE Re-Audit functionality working properly?',
            3 => 'Is the HSE Audit module working properly?',
            4 => 'Is the HSE Re-Audit functionality working properly?',
            5 => 'Is the Gemba Audit module working properly?',
            6 => 'Are the dashboards and summary counts working properly?',
            7 => 'Are the dashboard graphs and charts displaying correctly?',
            8 => 'Are the NC trackers and NC status updates working properly?',
            9 => 'Are reports, filters, and data exports working properly?',
            10 => 'Are login, role permissions, and site-based data access working properly?',
            11 => 'Are all assigned client/site details displaying correctly?',
            12 => 'Is the overall application working properly?'
        ];
    }

    /**
     * Display Super Admin Application Status Feedback dashboard
     */
    public function index()
    {
        if (!isSuperAdmin()) {
            return redirect()->to(base_url())->with('error', 'Unauthorized access.');
        }

        // Summary statistics
        $totalSubmissions = $this->db->table('alert_app_status_feedback')->countAllResults();

        // Calculate counts of Yes, No, and NA for each of the 12 questions
        $questionStats = [];
        $questions = self::getQuestions();

        foreach ($questions as $qNum => $qTitle) {
            $col = 'q' . $qNum;
            $yesCount = $this->db->table('alert_app_status_feedback')->where($col, 'Yes')->countAllResults();
            $noCount = $this->db->table('alert_app_status_feedback')->where($col, 'No')->countAllResults();
            $naCount = $this->db->table('alert_app_status_feedback')->where($col, 'NA')->countAllResults();

            $questionStats[$qNum] = [
                'title' => $qTitle,
                'yes' => $yesCount,
                'no' => $noCount,
                'na' => $naCount
            ];
        }

        // Distinct roles for filter dropdown
        $roles = $this->db->table('alert_users')
            ->select('DISTINCT(user_designation) as role')
            ->where('user_designation !=', '')
            ->where('user_designation IS NOT NULL')
            ->orderBy('user_designation', 'ASC')
            ->get()
            ->getResultArray();
        $rolesList = array_filter(array_column($roles, 'role'));

        $data = [
            'total_submissions' => $totalSubmissions,
            'question_stats'    => $questionStats,
            'questions'         => $questions,
            'roles_list'        => $rolesList
        ];

        return view('Admin/app_status_feedback_view', $data);
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

        $roleFilter = $request->getVar('role');
        $fromDate = $request->getVar('from_date');
        $toDate = $request->getVar('to_date');
        $filterQNum = (int)($request->getVar('question_num') ?? 0);
        $filterQAns = $request->getVar('question_ans');
        $searchValue = trim((string)($request->getVar('search')['value'] ?? ''));

        $builder = $this->db->table('alert_app_status_feedback f')
            ->select('f.*, u.user_name, u.user_emp_code, u.user_designation, u.user_email')
            ->join('alert_users u', 'u.user_id = f.user_id', 'left');

        // Total count before filters
        $totalRecords = $this->db->table('alert_app_status_feedback')->countAllResults();

        // Apply filters
        if (!empty($roleFilter)) {
            $builder->where('u.user_designation', $roleFilter);
        }
        if (!empty($fromDate)) {
            $builder->where('DATE(f.submitted_at) >=', $fromDate);
        }
        if (!empty($toDate)) {
            $builder->where('DATE(f.submitted_at) <=', $toDate);
        }
        if ($filterQNum >= 1 && $filterQNum <= 12 && !empty($filterQAns)) {
            $builder->where('f.q' . $filterQNum, $filterQAns);
        }

        // Global search
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('u.user_name', $searchValue)
                ->orLike('u.user_emp_code', $searchValue)
                ->orLike('u.user_designation', $searchValue)
                ->orLike('f.comments', $searchValue)
                ->groupEnd();
        }

        $filteredRecords = $builder->countAllResults(false);

        // Sorting
        $order = $request->getVar('order');
        $columns = [
            0 => 'f.id',
            1 => 'u.user_name',
            2 => 'u.user_designation',
            3 => 'f.submitted_at'
        ];

        if (!empty($order) && isset($order[0]['column'])) {
            $colIdx = (int)$order[0]['column'];
            $colDir = strtolower($order[0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
            $sortCol = $columns[$colIdx] ?? 'f.id';
            $builder->orderBy($sortCol, $colDir);
        } else {
            $builder->orderBy('f.id', 'DESC');
        }

        // Pagination
        $start = (int)($request->getVar('start') ?? 0);
        $length = (int)($request->getVar('length') ?? 10);
        if ($length > 0) {
            $builder->limit($length, $start);
        }

        $dataRows = $builder->get()->getResultArray();
        $questions = self::getQuestions();

        $rows = [];
        $srNo = $start + 1;
        foreach ($dataRows as $item) {
            // User Info
            $displayName = !empty($item['user_name']) ? htmlspecialchars($item['user_name']) : 'User #' . $item['user_id'];
            $empCode = !empty($item['user_emp_code']) ? '<span class="badge badge-light-secondary fs-8 ms-1">' . htmlspecialchars($item['user_emp_code']) . '</span>' : '';
            $userInfo = '<div class="d-flex flex-column">
                <span class="fw-bold text-dark fs-7">' . $displayName . ' ' . $empCode . '</span>
                <span class="text-muted fs-8">' . htmlspecialchars($item['user_email'] ?? '') . '</span>
            </div>';

            // Role
            $roleBadge = '<span class="badge badge-light-primary fw-semibold fs-8">' . htmlspecialchars($item['user_designation'] ?? 'User') . '</span>';

            // Answers summary pills
            $answersHtml = '<div class="d-flex flex-wrap gap-1" style="max-width: 480px;">';
            for ($q = 1; $q <= 12; $q++) {
                $ans = $item['q' . $q] ?? 'NA';
                $badgeClass = 'badge-light-secondary text-secondary';
                if ($ans === 'Yes') {
                    $badgeClass = 'badge-light-success text-success';
                } elseif ($ans === 'No') {
                    $badgeClass = 'badge-light-danger text-danger';
                }
                $qShort = htmlspecialchars($questions[$q] ?? "Q{$q}");
                $answersHtml .= '<span class="badge ' . $badgeClass . ' fs-8 px-2 py-1" title="Q' . $q . ': ' . $qShort . ' - ' . $ans . '" data-bs-toggle="tooltip">' .
                    'Q' . $q . ': <strong>' . $ans . '</strong>' .
                    '</span>';
            }
            $answersHtml .= '</div>';

            // Comments
            $commentsHtml = !empty($item['comments'])
                ? '<span class="text-gray-800 fs-8">' . nl2br(htmlspecialchars($item['comments'])) . '</span>'
                : '<span class="text-muted fs-8 fst-italic">None</span>';

            // Date
            $submittedAt = '<span class="text-muted fs-8">' . date('d-M-Y H:i', strtotime($item['submitted_at'])) . '</span>';

            // View modal trigger
            $actionBtn = '<button type="button" class="btn btn-sm btn-icon btn-light-primary" onclick="viewAppStatusDetails(' . $item['id'] . ')" title="View All Answers">
                <i class="fas fa-eye fs-7"></i>
            </button>';

            $rows[] = [
                'sr_no'        => $srNo++,
                'id'           => $item['id'],
                'user_info'    => $userInfo,
                'role'         => $roleBadge,
                'answers'      => $answersHtml,
                'comments'     => $commentsHtml,
                'submitted_at' => $submittedAt,
                'actions'      => $actionBtn,
                'raw'          => $item
            ];
        }

        return $this->response->setJSON([
            'draw'            => (int)($request->getVar('draw') ?? 1),
            'recordsTotal'    => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data'            => $rows
        ]);
    }

    /**
     * Get single record details for detail modal view
     */
    public function get_details($id)
    {
        if (!isSuperAdmin()) {
            return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
        }

        $id = (int)$id;
        $row = $this->db->table('alert_app_status_feedback f')
            ->select('f.*, u.user_name, u.user_emp_code, u.user_designation, u.user_email')
            ->join('alert_users u', 'u.user_id = f.user_id', 'left')
            ->where('f.id', $id)
            ->get()
            ->getRowArray();

        if (!$row) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Record not found']);
        }

        return $this->response->setJSON([
            'status' => 1,
            'data'   => $row,
            'questions' => self::getQuestions()
        ]);
    }

    /**
     * Export all filtered responses to CSV
     */
    public function export_csv()
    {
        if (!isSuperAdmin()) {
            return redirect()->to(base_url())->with('error', 'Unauthorized access.');
        }

        $request = service('request');

        $roleFilter = $request->getGet('role');
        $fromDate = $request->getGet('from_date');
        $toDate = $request->getGet('to_date');
        $filterQNum = (int)($request->getGet('question_num') ?? 0);
        $filterQAns = $request->getGet('question_ans');

        $builder = $this->db->table('alert_app_status_feedback f')
            ->select('f.*, u.user_name, u.user_emp_code, u.user_designation, u.user_email')
            ->join('alert_users u', 'u.user_id = f.user_id', 'left');

        if (!empty($roleFilter)) {
            $builder->where('u.user_designation', $roleFilter);
        }
        if (!empty($fromDate)) {
            $builder->where('DATE(f.submitted_at) >=', $fromDate);
        }
        if (!empty($toDate)) {
            $builder->where('DATE(f.submitted_at) <=', $toDate);
        }
        if ($filterQNum >= 1 && $filterQNum <= 12 && !empty($filterQAns)) {
            $builder->where('f.q' . $filterQNum, $filterQAns);
        }

        $builder->orderBy('f.id', 'DESC');
        $records = $builder->get()->getResultArray();
        $questions = self::getQuestions();

        $filename = 'Application_Status_Feedback_Export_' . date('Ymd_His') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);

        $output = fopen('php://output', 'w');
        // UTF-8 BOM for Excel compatibility
        fputs($output, "\xEF\xBB\xBF");

        $headers = [
            'Sr No',
            'User Name',
            'Emp Code',
            'Email',
            'Role / Designation',
        ];

        for ($i = 1; $i <= 12; $i++) {
            $headers[] = "Q{$i}: " . $questions[$i];
        }

        $headers[] = 'Additional Comments';
        $headers[] = 'Submitted At';

        fputcsv($output, $headers);

        $srNo = 1;
        foreach ($records as $row) {
            $line = [
                $srNo++,
                $row['user_name'] ?? 'User #' . $row['user_id'],
                $row['user_emp_code'] ?? '',
                $row['user_email'] ?? '',
                $row['user_designation'] ?? '',
            ];

            for ($i = 1; $i <= 12; $i++) {
                $line[] = $row['q' . $i] ?? '';
            }

            $line[] = $row['comments'] ?? '';
            $line[] = date('d-M-Y H:i:s', strtotime($row['submitted_at']));

            fputcsv($output, $line);
        }

        fclose($output);
        exit;
    }
}
