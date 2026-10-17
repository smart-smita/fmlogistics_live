<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Profile extends BaseController
{
    public function index()
    {
        helper(['designation_acl', 'form', 'url']);
        $db = db_connect();
        $user_id = session()->get('user_id');

        if (!$user_id) {
            return redirect()->to(base_url('Login'));
        }

        $query = "SELECT 
            user_id,
            user_emp_country,
            user_emp_zone,
            user_location,
            user_cluster,
            user_region,
            user_emp_code,
            user_name,
            user_email,
            user_contact,
            user_designation,
            employee_reporting_to,
            status,
            admin_flag,
            audit_type,
            last_date,
            default_date,
            update_date
        FROM alert_users 
        WHERE user_id = ?";

        $user = $db->query($query, [$user_id])->getRowArray();

        if (!$user) {
            return redirect()->to(base_url('Login'));
        }

        $data['user'] = $user;

        // Determine if current user is an Auditor or an Admin inspecting an Auditor
        $userDesignation = strtolower(trim($user['user_designation'] ?? (session()->get('role') ?? '')));
        $isAdmin = ((int)($user['admin_flag'] ?? 0) === 1) || in_array($userDesignation, ['super admin', 'super_admin', 'admin']);
        $isAuditor = ($userDesignation === 'auditor');

        $targetAuditorId = (int)$user_id;
        $auditorList = [];

        if ($isAdmin) {
            // Admins can see list of active auditors and preview their dashboard
            $auditorList = $db->table('alert_users')
                ->where('status', 1)
                ->where('LOWER(TRIM(user_designation))', 'auditor')
                ->orderBy('user_name', 'ASC')
                ->get()->getResultArray();

            $requestedId = (int)$this->request->getGet('auditor_id');
            if ($requestedId > 0) {
                $targetAuditorId = $requestedId;
                $isAuditor = true; // Admin inspecting an auditor
            }
        }

        $targetUser = $user;
        if ($targetAuditorId !== (int)$user_id) {
            $foundTarget = $db->table('alert_users')->where('user_id', $targetAuditorId)->get()->getRowArray();
            if ($foundTarget) {
                $targetUser = $foundTarget;
            }
        }

        $data['is_auditor'] = $isAuditor;
        $data['is_admin'] = $isAdmin;
        $data['target_user'] = $targetUser;
        $data['auditor_list'] = $auditorList;

        // Determine auditor scope (OE, HSE, or Both)
        $auditScope = trim($targetUser['audit_type'] ?? 'Both');
        if (empty($auditScope)) {
            $auditScope = 'Both';
        }
        $data['audit_scope'] = $auditScope;

        // Prepare initial summary statistics for instant initial page render (Yearly Scope)
        $currentYear = (int)date('Y');
        $data['current_year'] = $currentYear;
        $data['years_list'] = [2026, 2025, 2024, 2023, 2022];
        if ($isAuditor) {
            $data['initial_kpis'] = $this->calculateAuditorKpis(
                $db,
                (int)$targetUser['user_id'],
                trim($targetUser['user_name'] ?? ''),
                $currentYear
            );
        } else {
            $data['initial_kpis'] = [];
        }

        return view('Profile/profile_view', $data);
    }

    public function updatePassword()
    {
        $request = service('request');
        $db = db_connect();
        $user_id = session()->get('user_id');

        if (!$user_id) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Not logged in.']);
        }

        $current_password = $request->getVar('current_password');
        $new_password = $request->getVar('new_password');
        $confirm_password = $request->getVar('confirm_password');

        if (strlen($new_password) < 6) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Password must be at least 6 characters.']);
        }

        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&#])[A-Za-z\d@$!%*?&#]{6,}$/', $new_password)) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Password format should be e.g. Abc@123 (Min 6 chars, uppercase, lowercase, number, special char).']);
        }

        if ($new_password !== $confirm_password) {
            return $this->response->setJSON(['status' => 0, 'message' => 'New Password and Confirm Password do not match.']);
        }

        if ($new_password === $current_password) {
            return $this->response->setJSON(['status' => 0, 'message' => 'New Password cannot be the same as Current Password.']);
        }

        $user = $db->query("SELECT user_password FROM alert_users WHERE user_id = ?", [$user_id])->getRowArray();

        if (!$user) {
            return $this->response->setJSON(['status' => 0, 'message' => 'User not found.']);
        }

        $is_valid = false;
        if ($user['user_password'] === $current_password) {
            $is_valid = true;
        } else if (password_verify($current_password, $user['user_password'])) {
            $is_valid = true;
        }

        if (!$is_valid) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Current password is wrong.']);
        }

        // Save password
        $updateQuery = "UPDATE alert_users SET user_password = ?, update_date = NOW() WHERE user_id = ?";
        $db->query($updateQuery, [$new_password, $user_id]);

        return $this->response->setJSON([
            'status' => 1,
            'message' => 'Password updated successfully.',
            'redirect' => base_url('Login/log_out')
        ]);
    }

    /**
     * Helper to retrieve all recognized auditor name aliases (handles email prefix, spelling variations)
     */
    private function getAuditorAliases($db, int $auditorId, string $auditorName): array
    {
        $aliases = [];
        $rawName = strtolower(trim($auditorName));
        if (!empty($rawName)) {
            $aliases[] = $rawName;
        }

        $user = $db->table('alert_users')->select('user_name, user_email')->where('user_id', $auditorId)->get()->getRowArray();
        if ($user) {
            $dbName = strtolower(trim($user['user_name'] ?? ''));
            if (!empty($dbName)) {
                $aliases[] = $dbName;
            }
            if (!empty($user['user_email'])) {
                $parts = explode('@', $user['user_email']);
                $emailPrefix = strtolower(str_replace(['.', '_', '-'], ' ', trim($parts[0])));
                if (!empty($emailPrefix)) {
                    $aliases[] = $emailPrefix;
                }
            }
        }

        // Known historical variations (e.g. Noor Ahmed / Noor Ahamad)
        if (in_array('noor ahmed', $aliases) || in_array('noor ahamad', $aliases)) {
            $aliases[] = 'noor ahmed';
            $aliases[] = 'noor ahamad';
        }

        return array_values(array_unique(array_filter($aliases)));
    }

    /**
     * AJAX endpoint for Auditor Profile analytics graphs, metrics, and schedule
     */
    public function getAuditorAnalytics()
    {
        $db = db_connect();
        $loggedInUserId = (int)session()->get('user_id');

        if (!$loggedInUserId) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 0, 'message' => 'Session expired. Please log in again.']);
        }

        $userRecord = $db->table('alert_users')->where('user_id', $loggedInUserId)->get()->getRowArray();
        if (!$userRecord) {
            return $this->response->setStatusCode(401)->setJSON(['status' => 0, 'message' => 'User account not found.']);
        }

        $userDesignation = strtolower(trim($userRecord['user_designation'] ?? ''));
        $isAdmin = ((int)($userRecord['admin_flag'] ?? 0) === 1) || in_array($userDesignation, ['super admin', 'super_admin', 'admin']);
        $isAuditor = ($userDesignation === 'auditor');

        // Target Auditor resolution & ACL Enforcement
        $targetAuditorId = $loggedInUserId;
        if ($isAdmin) {
            $requestedId = (int)$this->request->getVar('auditor_id');
            if ($requestedId > 0) {
                $targetAuditorId = $requestedId;
            }
        } elseif (!$isAuditor) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 0, 'message' => 'Unauthorized access.']);
        }

        $targetUser = $db->table('alert_users')->where('user_id', $targetAuditorId)->get()->getRowArray();
        if (!$targetUser) {
            return $this->response->setJSON(['status' => 0, 'message' => 'Target auditor not found.']);
        }

        $auditorId = (int)$targetUser['user_id'];
        $auditorName = trim($targetUser['user_name'] ?? '');

        // Scope resolution (OE, HSE, or Both)
        $auditScope = trim($targetUser['audit_type'] ?? 'Both');
        if (empty($auditScope)) {
            $auditScope = 'Both';
        }

        // Filters: Year, Month, Date Range, Module ('all', 'OE', 'Gemba_HSE'), Status
        $requestedYear = trim($this->request->getVar('year') ?? date('Y'));
        $requestedMonth = (int)($this->request->getVar('month') ?? date('n'));
        if ($requestedMonth < 1 || $requestedMonth > 12) {
            $requestedMonth = (int)date('n');
        }
        $dateRange = trim($this->request->getVar('date_range') ?? 'yearly');
        $customStart = trim($this->request->getVar('start_date') ?? '');
        $customEnd = trim($this->request->getVar('end_date') ?? '');
        $selectedModule = trim($this->request->getVar('module') ?? 'all'); // 'all', 'OE', 'Gemba_HSE'
        $selectedStatus = trim($this->request->getVar('status') ?? 'all'); // 'all', 'completed', 'pending', 'overdue', 'due'

        // If auditor scope is restricted to OE or HSE, strictly enforce module filter accordingly
        if (strcasecmp($auditScope, 'OE') === 0) {
            $selectedModule = 'OE';
        } elseif (strcasecmp($auditScope, 'HSE') === 0) {
            $selectedModule = 'Gemba_HSE';
        }

        // Resolve Year and Date Boundaries
        $yearInt = is_numeric($requestedYear) ? (int)$requestedYear : (int)date('Y');
        $fromDate = null;
        $toDate = null;
        $today = date('Y-m-d');

        if ($dateRange === 'yearly' || (!empty($requestedYear) && $requestedYear !== 'all' && $dateRange === 'all')) {
            $fromDate = "{$yearInt}-01-01";
            $toDate = "{$yearInt}-12-31";
        } elseif ($dateRange === '30d') {
            $fromDate = date('Y-m-d', strtotime('-30 days'));
            $toDate = $today;
        } elseif ($dateRange === '90d') {
            $fromDate = date('Y-m-d', strtotime('-90 days'));
            $toDate = $today;
        } elseif ($dateRange === '180d') {
            $fromDate = date('Y-m-d', strtotime('-180 days'));
            $toDate = $today;
        } elseif ($dateRange === 'custom') {
            if (!empty($customStart) && !empty($customEnd)) {
                $fromDate = date('Y-m-d', strtotime($customStart));
                $toDate = date('Y-m-d', strtotime($customEnd));
            }
        }

        // 1. Unified Schedule & Activity Engine (Single Source of Truth)
        $unifiedData = $this->calculateUnifiedAuditorSchedules($db, $auditorId, $auditorName, $yearInt, $requestedMonth, $selectedModule);

        // 2. Two-Category KPI Metrics (OE and Gemba/HSE)
        $kpis = $this->calculateAuditorKpis($db, $auditorId, $auditorName, $yearInt, $fromDate, $toDate, $selectedModule, $requestedMonth, $unifiedData);

        // 3. Graph 1: Performed vs Pending Audits (Month-wise grouped bar chart)
        $performedVsPendingChart = $unifiedData['graph1'];

        // 4. Graph 2: Audit Due and Overdue Status (Donut Chart for selected month)
        $dueOverdueStatusChart = $unifiedData['graph2'];

        // 5. Graph 3: Monthly Audit Trend (Per Yearly Scope - 12 Months: OE vs Gemba/HSE)
        $monthlyTrendChart = $this->getMonthlyTrendChart($db, $auditorId, $auditorName, $yearInt, $selectedModule);

        // 6. Graph 4: Re-Audit Analysis (Performed vs Re-Audits)
        $reauditChart = $this->getReauditPerformanceChart($kpis, $selectedModule);

        // 7. Dedicated NC Analytics (Status Distribution & Action Progress)
        $ncAnalytics = $this->getNcAnalytics($kpis, $selectedModule);

        // 8. Audit Schedule and Report Table (Site-level schedules with next audit dates)
        $scheduleTable = $this->getAuditScheduleTable($db, $auditorId, $auditorName, $selectedModule, $selectedStatus, $yearInt, $fromDate, $toDate);

        // 9. Latest NCs Report Table (Combined Gemba/HSE NCs and OE NCs with full details)
        $latestNcsTable = $this->getLatestNcsTable($db, $auditorId, $auditorName, $selectedModule, $selectedStatus, $yearInt, $fromDate, $toDate);

        return $this->response->setJSON([
            'status' => 1,
            'auditor' => [
                'user_id' => $auditorId,
                'user_name' => $auditorName,
                'designation' => $targetUser['user_designation'] ?? 'Auditor',
                'email' => $targetUser['user_email'] ?? '',
                'emp_code' => $targetUser['user_emp_code'] ?? '',
                'audit_scope' => $auditScope
            ],
            'kpis' => $kpis,
            'charts' => [
                'performed_vs_pending' => $performedVsPendingChart,
                'due_overdue_status' => $dueOverdueStatusChart,
                'monthly_trend' => $monthlyTrendChart,
                'reaudit_analysis' => $reauditChart,
                'nc_analytics' => $ncAnalytics,
                'drilldown_records' => $unifiedData['records'],
                'months' => $unifiedData['months']
            ],
            'schedule_table' => $scheduleTable,
            'latest_ncs' => $latestNcsTable,
            'filters_applied' => [
                'year' => $requestedYear,
                'month' => $requestedMonth,
                'date_range' => $dateRange,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'module' => $selectedModule,
                'status' => $selectedStatus
            ]
        ]);
    }

    /**
     * Unified Schedule & Audit Activity Engine:
     * Single source of truth for OE (Monthly) and Gemba/HSE (Quarterly / 3-Month Cycle)
     * Reconciles Graph 1 (Month-wise activity), Graph 2 (Schedule health), and drilldown records.
     */
    private function calculateUnifiedAuditorSchedules($db, int $auditorId, string $auditorName, int $year, int $selectedMonth = null, string $selectedModule = 'all'): array
    {
        $today = date('Y-m-d');
        $dueSoonLimit = date('Y-m-d', strtotime('+15 days'));
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escaped = array_map(function($x) use ($db) { return $db->escape($x); }, $aliases);
        $in = implode(', ', $escaped);

        if (!$selectedMonth || $selectedMonth < 1 || $selectedMonth > 12) {
            $selectedMonth = (int)date('n');
        }

        $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = [
                'month_num' => $m,
                'month_name' => $monthNames[$m - 1],
                'oe' => [
                    'performed' => 0,
                    'reaudits' => 0,
                    'upcoming' => 0,
                    'due_soon' => 0,
                    'pending' => 0,
                    'overdue' => 0,
                    'records' => []
                ],
                'gemba_hse' => [
                    'performed' => 0,
                    'reaudits' => 0,
                    'upcoming' => 0,
                    'due_soon' => 0,
                    'pending' => 0,
                    'overdue' => 0,
                    'records' => []
                ]
            ];
        }

        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);
        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        // ─────────────────────────────────────────────────────────────
        // 1. OE AUDITS (Monthly Frequency: 1 audit due per calendar month)
        // ─────────────────────────────────────────────────────────────
        if ($checkOe) {
            $oeAudits = $db->query("SELECT 
                a.structured_audit_id,
                a.audit_no,
                a.audit_date,
                a.client_name,
                a.location as site_name,
                a.reaudit,
                IF(a.next_date != '0000-00-00' AND a.next_date IS NOT NULL, a.next_date, DATE_ADD(a.audit_date, INTERVAL 1 MONTH)) as next_due_date
            FROM alert_final_structured_audit a
            WHERE (a.audit_by_user_id = {$auditorId} OR LOWER(TRIM(a.auditor_name)) IN ($in))
              AND YEAR(a.audit_date) = {$year}
            ORDER BY a.audit_date ASC")->getResultArray();

            $oePerformedSitesPerMonth = [];

            foreach ($oeAudits as $a) {
                $m = (int)date('n', strtotime($a['audit_date']));
                $siteKey = strtolower(trim(($a['client_name'] ?? '') . '|' . ($a['site_name'] ?? '')));
                $oePerformedSitesPerMonth[$m][$siteKey] = true;

                $isReaudit = ((int)$a['reaudit'] > 0);
                if ($isReaudit) {
                    $months[$m]['oe']['reaudits']++;
                    $recStatus = 'Re-Audit';
                    $recType = 'OE Re-Audit';
                } else {
                    $months[$m]['oe']['performed']++;
                    $recStatus = 'Completed';
                    $recType = 'OE Audit (Monthly)';
                }

                $months[$m]['oe']['records'][] = [
                    'category' => 'OE',
                    'audit_id' => $a['structured_audit_id'],
                    'audit_no' => $a['audit_no'] ?: ('OE-' . $a['structured_audit_id']),
                    'site_name' => $a['site_name'] ?: 'N/A',
                    'client_name' => $a['client_name'] ?: 'FM Logistic',
                    'audit_date' => $a['audit_date'],
                    'due_date' => $a['next_due_date'],
                    'status' => $recStatus,
                    'type' => $recType,
                    'month_num' => $m,
                    'action_url' => base_url('Masters/Audit_final_structure')
                ];
            }

            // Scheduled OE due dates (where next due date falls in this year)
            $latestOePerSite = $db->query("SELECT 
                a.client_name,
                a.location as site_name,
                MAX(a.audit_date) as last_audit_date,
                IF(MAX(a.next_date) != '0000-00-00' AND MAX(a.next_date) IS NOT NULL, MAX(a.next_date), DATE_ADD(MAX(a.audit_date), INTERVAL 1 MONTH)) as next_due_date
            FROM alert_final_structured_audit a
            WHERE (a.audit_by_user_id = {$auditorId} OR LOWER(TRIM(a.auditor_name)) IN ($in))
            GROUP BY a.client_name, a.location")->getResultArray();

            foreach ($latestOePerSite as $s) {
                $nextDate = $s['next_due_date'];
                if (empty($nextDate) || $nextDate === '0000-00-00') continue;
                $nextYear = (int)date('Y', strtotime($nextDate));
                if ($nextYear !== $year) continue;
                $dueM = (int)date('n', strtotime($nextDate));
                $siteKey = strtolower(trim(($s['client_name'] ?? '') . '|' . ($s['site_name'] ?? '')));

                // If site was already audited in this month, skip to prevent double-counting
                if (!empty($oePerformedSitesPerMonth[$dueM][$siteKey])) {
                    continue;
                }

                if ($nextDate < $today) {
                    $status = 'Overdue';
                    $months[$dueM]['oe']['overdue']++;
                } elseif ($nextDate <= $dueSoonLimit) {
                    $status = 'Due Soon';
                    $months[$dueM]['oe']['due_soon']++;
                    $months[$dueM]['oe']['pending']++;
                } else {
                    $status = 'Upcoming';
                    $months[$dueM]['oe']['upcoming']++;
                    $months[$dueM]['oe']['pending']++;
                }

                $months[$dueM]['oe']['records'][] = [
                    'category' => 'OE',
                    'audit_id' => 0,
                    'audit_no' => 'SCHEDULED-OE',
                    'site_name' => $s['site_name'] ?: 'N/A',
                    'client_name' => $s['client_name'] ?: 'FM Logistic',
                    'audit_date' => $s['last_audit_date'],
                    'due_date' => $nextDate,
                    'status' => $status,
                    'type' => 'Scheduled OE Audit',
                    'month_num' => $dueM,
                    'action_url' => base_url('Masters/Audit_template/audits')
                ];
            }
        }

        // ─────────────────────────────────────────────────────────────
        // 2. GEMBA/HSE AUDITS (3-Month Cycle / Quarterly Frequency)
        // ─────────────────────────────────────────────────────────────
        if ($checkGembaHse) {
            // 2.1 HSE Audits
            $hseAudits = $db->query("SELECT 
                h.hse_audit_id,
                h.audit_no,
                h.audit_date,
                h.client_name,
                h.location as site_name,
                COALESCE(NULLIF(h.next_date, '0000-00-00'), NULLIF(h.report_date, '0000-00-00'), DATE_ADD(h.audit_date, INTERVAL 3 MONTH)) as next_due_date,
                (SELECT MIN(h2.hse_audit_id) FROM alert_hse_audit_master h2 WHERE h2.audit_no = h.audit_no) as first_id
            FROM alert_hse_audit_master h
            WHERE (LOWER(TRIM(h.auditor_name)) IN ($in) OR LOWER(TRIM(h.perform_audit_by)) IN ($in))
              AND SUBSTRING(h.audit_date, 1, 4) = '{$year}'
            ORDER BY h.audit_date ASC")->getResultArray();

            $hsePerformedSitesPerMonth = [];

            foreach ($hseAudits as $h) {
                $m = (int)substr($h['audit_date'], 5, 2);
                $siteKey = strtolower(trim(($h['client_name'] ?? '') . '|' . ($h['site_name'] ?? '')));
                $hsePerformedSitesPerMonth[$m][$siteKey] = true;

                $isReaudit = ((int)$h['hse_audit_id'] !== (int)$h['first_id']);
                if ($isReaudit) {
                    $months[$m]['gemba_hse']['reaudits']++;
                    $recStatus = 'Re-Audit';
                    $recType = 'HSE Re-Audit';
                } else {
                    $months[$m]['gemba_hse']['performed']++;
                    $recStatus = 'Completed';
                    $recType = 'HSE Audit (Quarterly)';
                }

                $months[$m]['gemba_hse']['records'][] = [
                    'category' => 'Gemba/HSE',
                    'audit_id' => $h['hse_audit_id'],
                    'audit_no' => $h['audit_no'] ?: ('HSE-' . $h['hse_audit_id']),
                    'site_name' => $h['site_name'] ?: 'N/A',
                    'client_name' => $h['client_name'] ?: 'FM Logistic',
                    'audit_date' => $h['audit_date'],
                    'due_date' => $h['next_due_date'],
                    'status' => $recStatus,
                    'type' => $recType,
                    'month_num' => $m,
                    'action_url' => base_url('Masters/Hse_nc_tracker')
                ];
            }

            // 2.2 Gemba Walks (Pure Gemba Walk sessions)
            $gembaWalks = $db->query("SELECT 
                g.gemba_sr_no,
                g.unique_no,
                g.audit_report_date,
                g.site_name,
                COUNT(*) as observation_count
            FROM alert_gemba_audits g
            WHERE LOWER(TRIM(g.auditor_name)) IN ($in)
              AND (g.source_module != 'hse_audit' OR g.source_module IS NULL)
              AND YEAR(g.audit_report_date) = {$year}
            GROUP BY g.site_name, g.audit_report_date
            ORDER BY g.audit_report_date ASC")->getResultArray();

            foreach ($gembaWalks as $gw) {
                $m = (int)date('n', strtotime($gw['audit_report_date']));
                $months[$m]['gemba_hse']['performed']++;
                $months[$m]['gemba_hse']['records'][] = [
                    'category' => 'Gemba/HSE',
                    'audit_id' => $gw['gemba_sr_no'],
                    'audit_no' => $gw['unique_no'] ?: 'GEMBA-WALK',
                    'site_name' => $gw['site_name'] ?: 'N/A',
                    'client_name' => $gw['site_name'] ?: 'FM Logistic',
                    'audit_date' => $gw['audit_report_date'],
                    'due_date' => date('Y-m-d', strtotime('+3 months', strtotime($gw['audit_report_date']))),
                    'status' => 'Completed',
                    'type' => 'Gemba Walk Session',
                    'month_num' => $m,
                    'action_url' => base_url('Masters/GembaNcTracker')
                ];
            }

            // 2.3 Scheduled HSE due dates (3-Month Cycle)
            $latestHsePerSite = $db->query("SELECT 
                h.client_name,
                h.location as site_name,
                MAX(h.audit_date) as last_audit_date,
                COALESCE(NULLIF(MAX(h.next_date), '0000-00-00'), NULLIF(MAX(h.report_date), '0000-00-00'), DATE_ADD(MAX(h.audit_date), INTERVAL 3 MONTH)) as next_due_date
            FROM alert_hse_audit_master h
            WHERE (LOWER(TRIM(h.auditor_name)) IN ($in) OR LOWER(TRIM(h.perform_audit_by)) IN ($in))
            GROUP BY h.client_name, h.location")->getResultArray();

            foreach ($latestHsePerSite as $s) {
                $nextDate = $s['next_due_date'];
                if (empty($nextDate) || $nextDate === '0000-00-00') continue;
                $nextYear = (int)date('Y', strtotime($nextDate));
                if ($nextYear !== $year) continue;
                $dueM = (int)date('n', strtotime($nextDate));
                $siteKey = strtolower(trim(($s['client_name'] ?? '') . '|' . ($s['site_name'] ?? '')));

                // If site was already audited in dueM, do not double-count
                if (!empty($hsePerformedSitesPerMonth[$dueM][$siteKey])) {
                    continue;
                }

                if ($nextDate < $today) {
                    $status = 'Overdue';
                    $months[$dueM]['gemba_hse']['overdue']++;
                } elseif ($nextDate <= $dueSoonLimit) {
                    $status = 'Due Soon';
                    $months[$dueM]['gemba_hse']['due_soon']++;
                    $months[$dueM]['gemba_hse']['pending']++;
                } else {
                    $status = 'Upcoming';
                    $months[$dueM]['gemba_hse']['upcoming']++;
                    $months[$dueM]['gemba_hse']['pending']++;
                }

                $months[$dueM]['gemba_hse']['records'][] = [
                    'category' => 'Gemba/HSE',
                    'audit_id' => 0,
                    'audit_no' => 'SCHEDULED-HSE',
                    'site_name' => $s['site_name'] ?: 'N/A',
                    'client_name' => $s['client_name'] ?: 'FM Logistic',
                    'audit_date' => $s['last_audit_date'],
                    'due_date' => $nextDate,
                    'status' => $status,
                    'type' => 'Scheduled HSE (3-Mo Cycle)',
                    'month_num' => $dueM,
                    'action_url' => base_url('Masters/Hse_nc_tracker')
                ];
            }
        }

        // ─────────────────────────────────────────────────────────────
        // 3. GRAPH 1: MONTH-WISE GROUPED BAR CHART
        // ─────────────────────────────────────────────────────────────
        $g1Categories = $monthNames;
        $g1Series = [];

        if ($checkOe && $checkGembaHse) {
            $g1Series[] = ['name' => 'OE Performed', 'data' => array_column(array_column($months, 'oe'), 'performed')];
            $g1Series[] = ['name' => 'OE Re-Audits', 'data' => array_column(array_column($months, 'oe'), 'reaudits')];
            $g1Series[] = ['name' => 'OE Pending', 'data' => array_column(array_column($months, 'oe'), 'pending')];
            $g1Series[] = ['name' => 'OE Overdue', 'data' => array_column(array_column($months, 'oe'), 'overdue')];
            $g1Series[] = ['name' => 'Gemba/HSE Completed', 'data' => array_column(array_column($months, 'gemba_hse'), 'performed')];
            $g1Series[] = ['name' => 'Gemba/HSE Re-Audits', 'data' => array_column(array_column($months, 'gemba_hse'), 'reaudits')];
            $g1Series[] = ['name' => 'Gemba/HSE Pending', 'data' => array_column(array_column($months, 'gemba_hse'), 'pending')];
            $g1Series[] = ['name' => 'Gemba/HSE Overdue', 'data' => array_column(array_column($months, 'gemba_hse'), 'overdue')];
        } elseif ($checkOe) {
            $g1Series[] = ['name' => 'Performed', 'data' => array_column(array_column($months, 'oe'), 'performed')];
            $g1Series[] = ['name' => 'Re-Audits', 'data' => array_column(array_column($months, 'oe'), 'reaudits')];
            $g1Series[] = ['name' => 'Pending', 'data' => array_column(array_column($months, 'oe'), 'pending')];
            $g1Series[] = ['name' => 'Overdue', 'data' => array_column(array_column($months, 'oe'), 'overdue')];
        } else {
            $g1Series[] = ['name' => 'Completed', 'data' => array_column(array_column($months, 'gemba_hse'), 'performed')];
            $g1Series[] = ['name' => 'Re-Audits', 'data' => array_column(array_column($months, 'gemba_hse'), 'reaudits')];
            $g1Series[] = ['name' => 'Pending', 'data' => array_column(array_column($months, 'gemba_hse'), 'pending')];
            $g1Series[] = ['name' => 'Overdue', 'data' => array_column(array_column($months, 'gemba_hse'), 'overdue')];
        }

        // ─────────────────────────────────────────────────────────────
        // 4. GRAPH 2: SCHEDULE HEALTH DONUT FOR SELECTED MONTH
        // ─────────────────────────────────────────────────────────────
        $selData = $months[$selectedMonth];
        $isOeScope = (strcasecmp($selectedModule, 'oe') === 0);
        $g2Source = $isOeScope ? $selData['oe'] : $selData['gemba_hse'];
        $g2ModuleLabel = $isOeScope ? 'OE' : 'Gemba/HSE';
        $g2Completed = (int)$g2Source['performed'];
        $g2Upcoming = (int)$g2Source['upcoming'];
        $g2DueSoon = (int)$g2Source['due_soon'];
        $g2Overdue = (int)$g2Source['overdue'];
        $g2Reaudits = (int)$g2Source['reaudits'];
        $g2Total = $g2Completed + $g2Upcoming + $g2DueSoon + $g2Overdue;

        $graph2 = [
            'title' => "Graph 2: {$g2ModuleLabel} Schedule Health ({$selData['month_name']} {$year})",
            'scope_badge' => "{$selData['month_name']} {$year}",
            'module' => $g2ModuleLabel,
            'labels' => ['Completed', 'Upcoming', 'Due Soon (15d)', 'Overdue'],
            'series' => [$g2Completed, $g2Upcoming, $g2DueSoon, $g2Overdue],
            'total' => $g2Total,
            'reaudits' => $g2Reaudits,
            'note' => "* Note: Follow-up Re-Audits ({$g2Reaudits} in {$selData['month_name']}) are tracked separately in Graph 1 and excluded from the schedule health donut so baseline schedule compliance is accurately reflected."
        ];

        // ─────────────────────────────────────────────────────────────
        // 5. ALL DRILLDOWN RECORDS
        // ─────────────────────────────────────────────────────────────
        $allRecords = [];
        foreach ($months as $m) {
            foreach ($m['oe']['records'] as $r) {
                $allRecords[] = $r;
            }
            foreach ($m['gemba_hse']['records'] as $r) {
                $allRecords[] = $r;
            }
        }

        return [
            'months' => $months,
            'selected_month' => $selectedMonth,
            'graph1' => [
                'categories' => $g1Categories,
                'series' => $g1Series
            ],
            'graph2' => $graph2,
            'records' => $allRecords
        ];
    }

    /**
     * Calculates complete KPIs for the 2 audit categories:
     * 1. OE Audit
     * 2. Gemba/HSE Audit
     * All audit totals reconcile directly with calculateUnifiedAuditorSchedules.
     */
    private function calculateAuditorKpis($db, int $auditorId, string $auditorName, $year = null, $fromDate = null, $toDate = null, $selectedModule = 'all', $selectedMonth = null, $precomputedUnified = null): array
    {
        $today = date('Y-m-d');
        $yearFilter = is_numeric($year) ? (int)$year : (int)date('Y');
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        // Fetch unified schedule metrics (Single Source of Truth)
        $unified = $precomputedUnified ?: $this->calculateUnifiedAuditorSchedules($db, $auditorId, $auditorName, $yearFilter, $selectedMonth, $selectedModule);

        // ════════════════════════════════════════════════════════════
        // CATEGORY 1: OE AUDIT DASHBOARD
        // ════════════════════════════════════════════════════════════
        $oePerformed = array_sum(array_column(array_column($unified['months'], 'oe'), 'performed'));
        $oeReaudits = array_sum(array_column(array_column($unified['months'], 'oe'), 'reaudits'));
        $oePending = array_sum(array_column(array_column($unified['months'], 'oe'), 'pending'));
        $oeUpcoming = array_sum(array_column(array_column($unified['months'], 'oe'), 'upcoming'));
        $oeDue = array_sum(array_column(array_column($unified['months'], 'oe'), 'due_soon'));
        $oeOverdue = array_sum(array_column(array_column($unified['months'], 'oe'), 'overdue'));
        $oeTotal = $oePerformed + $oeReaudits;

        // Last OE Date
        $lastOeRow = $db->query("SELECT MAX(audit_date) as last_date FROM alert_final_structured_audit WHERE (audit_by_user_id = {$auditorId} OR LOWER(TRIM(auditor_name)) IN ({$inAliases}))")->getRowArray();
        $oeLastDate = (!empty($lastOeRow['last_date'])) ? $lastOeRow['last_date'] : '-';
        $oeNextDate = ($oeLastDate !== '-') ? date('Y-m-d', strtotime('+1 month', strtotime($oeLastDate))) : '-';

        // OE NC query: Matches Oe_nc_tracker
        $oeTotalNc = 0; $oeOpenNc = 0; $oeWorkingNc = 0; $oeReviewNc = 0; $oeClosedNc = 0;
        $oeNcSql = "SELECT 
            COUNT(*) as total_nc,
            SUM(IF(d.audit_finding = 'NO' AND d.status = 0, 1, 0)) as open_nc,
            SUM(IF(d.audit_finding = 'NO' AND d.status IN (1, 4), 1, 0)) as working_nc,
            SUM(IF(d.audit_finding = 'NO' AND d.status IN (6, 2), 1, 0)) as review_nc,
            SUM(IF(d.status IN (3, 5), 1, 0)) as closed_nc
        FROM alert_final_structured_audit_details d
        JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id
        WHERE (a.audit_by_user_id = {$auditorId} OR LOWER(TRIM(a.auditor_name)) IN ({$inAliases}))
          AND (a.reaudit = 0 OR d.status IN (3, 5))
          AND (d.audit_finding = 'NO' OR d.status IN (3, 5))";
        if ($fromDate && $toDate) {
            $oeNcSql .= " AND a.audit_date >= '{$fromDate}' AND a.audit_date <= '{$toDate}'";
        } elseif ($yearFilter) {
            $oeNcSql .= " AND YEAR(a.audit_date) = {$yearFilter}";
        }
        $oeNcRes = $db->query($oeNcSql)->getRowArray();
        if ($oeNcRes) {
            $oeTotalNc = (int)($oeNcRes['total_nc'] ?? 0);
            $oeOpenNc = (int)($oeNcRes['open_nc'] ?? 0);
            $oeWorkingNc = (int)($oeNcRes['working_nc'] ?? 0);
            $oeReviewNc = (int)($oeNcRes['review_nc'] ?? 0);
            $oeClosedNc = (int)($oeNcRes['closed_nc'] ?? 0);
        }

        // ════════════════════════════════════════════════════════════
        // CATEGORY 2: GEMBA/HSE AUDIT DASHBOARD (Combined)
        // ════════════════════════════════════════════════════════════
        $gembaHsePerformedAudits = array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'performed'));
        $gembaHseReaudits = array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'reaudits'));
        $gembaHsePendingAudits = array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'pending'));
        $gembaHseUpcomingAudits = array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'upcoming'));
        $gembaHseDueAudits = array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'due_soon'));
        $gembaHseOverdueAudits = array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'overdue'));
        $gembaHseTotalAudits = $gembaHsePerformedAudits + $gembaHseReaudits;

        // Last Gemba/HSE Date
        $lastHseRow = $db->query("SELECT MAX(audit_date) as last_date FROM alert_hse_audit_master WHERE (LOWER(TRIM(auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(perform_audit_by)) IN ({$inAliases}))")->getRowArray();
        $lastGembaRow = $db->query("SELECT MAX(audit_report_date) as last_date FROM alert_gemba_audits WHERE LOWER(TRIM(auditor_name)) IN ({$inAliases}) AND (source_module != 'hse_audit' OR source_module IS NULL)")->getRowArray();
        $hseLast = $lastHseRow['last_date'] ?? null;
        $gembaLast = $lastGembaRow['last_date'] ?? null;
        $gembaHseLastDate = '-';
        if ($hseLast && $gembaLast) {
            $gembaHseLastDate = (strtotime($hseLast) > strtotime($gembaLast)) ? $hseLast : $gembaLast;
        } elseif ($hseLast) {
            $gembaHseLastDate = $hseLast;
        } elseif ($gembaLast) {
            $gembaHseLastDate = $gembaLast;
        }

        // Gemba/HSE NCs (Directly matching Masters/GembaNcTracker)
        $gembaHseTotalNc = 0; $gembaHseOpenNc = 0; $gembaHseWorkingNc = 0; $gembaHseReviewNc = 0; $gembaHseClosedNc = 0;
        $hseTotalNc = 0; $hseOpenNc = 0; $hseWorkingNc = 0; $hseReviewNc = 0; $hseClosedNc = 0;
        $gembaTotalNc = 0; $gembaOpenNc = 0; $gembaWorkingNc = 0; $gembaReviewNc = 0; $gembaClosedNc = 0;

        $gembaHseNcWhere = "status = 1 AND LOWER(TRIM(auditor_name)) IN ({$inAliases})";
        $gembaHseNcWhere .= " AND (
            source_module != 'hse_audit'
            OR (source_module = 'hse_audit' AND hse_detail_id IN (
                SELECT latest_detail_id FROM (
                    SELECT MAX(d2.id) AS latest_detail_id
                    FROM alert_hse_audit_details d2
                    INNER JOIN alert_hse_audit_master latest_master ON latest_master.hse_audit_id = d2.hse_audit_id
                    INNER JOIN (
                        SELECT audit_no, MAX(hse_audit_id) AS latest_hse_audit_id
                        FROM alert_hse_audit_master
                        GROUP BY audit_no
                    ) latest_audit ON latest_audit.latest_hse_audit_id = latest_master.hse_audit_id
                    GROUP BY latest_master.audit_no, d2.question_id
                ) as temp_latest_nc
            ))
        )";
        if ($fromDate && $toDate) {
            $gembaHseNcWhere .= " AND audit_report_date >= '{$fromDate}' AND audit_report_date <= '{$toDate}'";
        } elseif ($yearFilter) {
            $gembaHseNcWhere .= " AND YEAR(audit_report_date) = {$yearFilter}";
        }

        $gembaHseNcSql = "SELECT 
            COUNT(*) as total_nc,
            SUM(CASE WHEN nc_status = 0 THEN 1 ELSE 0 END) as open_nc,
            SUM(CASE WHEN nc_status = 1 THEN 1 ELSE 0 END) as working_nc,
            SUM(CASE WHEN nc_status IN (2, 5) THEN 1 ELSE 0 END) as review_nc,
            SUM(CASE WHEN nc_status = 3 THEN 1 ELSE 0 END) as closed_nc,
            SUM(CASE WHEN source_module = 'hse_audit' THEN 1 ELSE 0 END) as hse_total_nc,
            SUM(CASE WHEN source_module = 'hse_audit' AND nc_status = 0 THEN 1 ELSE 0 END) as hse_open_nc,
            SUM(CASE WHEN source_module = 'hse_audit' AND nc_status = 1 THEN 1 ELSE 0 END) as hse_working_nc,
            SUM(CASE WHEN source_module = 'hse_audit' AND nc_status IN (2, 5) THEN 1 ELSE 0 END) as hse_review_nc,
            SUM(CASE WHEN source_module = 'hse_audit' AND nc_status = 3 THEN 1 ELSE 0 END) as hse_closed_nc,
            SUM(CASE WHEN source_module != 'hse_audit' OR source_module IS NULL THEN 1 ELSE 0 END) as gemba_total_nc,
            SUM(CASE WHEN (source_module != 'hse_audit' OR source_module IS NULL) AND nc_status = 0 THEN 1 ELSE 0 END) as gemba_open_nc,
            SUM(CASE WHEN (source_module != 'hse_audit' OR source_module IS NULL) AND nc_status = 1 THEN 1 ELSE 0 END) as gemba_working_nc,
            SUM(CASE WHEN (source_module != 'hse_audit' OR source_module IS NULL) AND nc_status IN (2, 5) THEN 1 ELSE 0 END) as gemba_review_nc,
            SUM(CASE WHEN (source_module != 'hse_audit' OR source_module IS NULL) AND nc_status = 3 THEN 1 ELSE 0 END) as gemba_closed_nc
        FROM alert_gemba_audits
        WHERE {$gembaHseNcWhere}";
        $gembaHseNcRes = $db->query($gembaHseNcSql)->getRowArray();

        if ($gembaHseNcRes) {
            $gembaHseTotalNc = (int)($gembaHseNcRes['total_nc'] ?? 0);
            $gembaHseOpenNc = (int)($gembaHseNcRes['open_nc'] ?? 0);
            $gembaHseWorkingNc = (int)($gembaHseNcRes['working_nc'] ?? 0);
            $gembaHseReviewNc = (int)($gembaHseNcRes['review_nc'] ?? 0);
            $gembaHseClosedNc = (int)($gembaHseNcRes['closed_nc'] ?? 0);

            $hseTotalNc = (int)($gembaHseNcRes['hse_total_nc'] ?? 0);
            $hseOpenNc = (int)($gembaHseNcRes['hse_open_nc'] ?? 0);
            $hseWorkingNc = (int)($gembaHseNcRes['hse_working_nc'] ?? 0);
            $hseReviewNc = (int)($gembaHseNcRes['hse_review_nc'] ?? 0);
            $hseClosedNc = (int)($gembaHseNcRes['hse_closed_nc'] ?? 0);

            $gembaTotalNc = (int)($gembaHseNcRes['gemba_total_nc'] ?? 0);
            $gembaOpenNc = (int)($gembaHseNcRes['gemba_open_nc'] ?? 0);
            $gembaWorkingNc = (int)($gembaHseNcRes['gemba_working_nc'] ?? 0);
            $gembaReviewNc = (int)($gembaHseNcRes['gemba_review_nc'] ?? 0);
            $gembaClosedNc = (int)($gembaHseNcRes['gemba_closed_nc'] ?? 0);
        }

        // Summary Aggregates across the 2 categories
        $totalAudits = $oeTotal + $gembaHseTotalAudits;
        $totalPerformed = $oePerformed + $gembaHsePerformedAudits;
        $totalReaudits = $oeReaudits + $gembaHseReaudits;
        $totalPending = $oePending + $gembaHsePendingAudits;
        $totalUpcoming = $oeUpcoming + $gembaHseUpcomingAudits;
        $totalDue = $oeDue + $gembaHseDueAudits;
        $totalOverdue = $oeOverdue + $gembaHseOverdueAudits;

        $totalNc = $oeTotalNc + $gembaHseTotalNc;
        $totalOpenNc = $oeOpenNc + $gembaHseOpenNc;
        $totalWorkingNc = $oeWorkingNc + $gembaHseWorkingNc;
        $totalReviewNc = $oeReviewNc + $gembaHseReviewNc;
        $totalClosedNc = $oeClosedNc + $gembaHseClosedNc;

        return [
            // Overall summary
            'summary' => [
                'total_audits' => $totalAudits,
                'performed_audits' => $totalPerformed,
                'reaudits' => $totalReaudits,
                'pending_audits' => $totalPending,
                'upcoming_audits' => $totalUpcoming,
                'due_audits' => $totalDue,
                'overdue_audits' => $totalOverdue,
                'total_ncs' => $totalNc,
                'open_ncs' => $totalOpenNc,
                'working_ncs' => $totalWorkingNc,
                'review_ncs' => $totalReviewNc,
                'closed_ncs' => $totalClosedNc,
                'actions_completed' => $totalClosedNc,
                'actions_pending' => $totalOpenNc + $totalWorkingNc + $totalReviewNc
            ],

            // Category 1: OE Audits
            'oe' => [
                'total_audits' => $oeTotal,
                'performed_audits' => $oePerformed,
                'reaudits' => $oeReaudits,
                'pending_audits' => $oePending,
                'upcoming_audits' => $oeUpcoming,
                'due_audits' => $oeDue,
                'overdue_audits' => $oeOverdue,
                'last_audit_date' => $oeLastDate,
                'next_audit_date' => $oeNextDate,
                'frequency' => '1 Month (Monthly)',
                'total_ncs' => $oeTotalNc,
                'open_ncs' => $oeOpenNc,
                'working_ncs' => $oeWorkingNc,
                'review_ncs' => $oeReviewNc,
                'closed_ncs' => $oeClosedNc,
                'pending_actions' => $oeOpenNc + $oeWorkingNc + $oeReviewNc
            ],

            // Category 2: Gemba/HSE Audits (Combined)
            'gemba_hse' => [
                'total_audits' => $gembaHseTotalAudits,
                'performed_audits' => $gembaHsePerformedAudits,
                'reaudits' => $gembaHseReaudits,
                'pending_audits' => $gembaHsePendingAudits,
                'upcoming_audits' => $gembaHseUpcomingAudits,
                'due_audits' => $gembaHseDueAudits,
                'overdue_audits' => $gembaHseOverdueAudits,
                'last_audit_date' => $gembaHseLastDate,
                'next_audit_date' => '3 Months / Event Workflow',
                'frequency' => 'Quarterly & Walk Workflow',
                'total_ncs' => $gembaHseTotalNc,
                'open_ncs' => $gembaHseOpenNc,
                'working_ncs' => $gembaHseWorkingNc,
                'review_ncs' => $gembaHseReviewNc,
                'closed_ncs' => $gembaHseClosedNc,
                'pending_actions' => $gembaHseOpenNc + $gembaHseWorkingNc + $gembaHseReviewNc,

                // Component breakdown for transparency
                'hse_portion' => [
                    'total_audits' => array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'performed')) + array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'reaudits')),
                    'performed_audits' => array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'performed')),
                    'reaudits' => array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'reaudits')),
                    'pending_audits' => array_sum(array_column(array_column($unified['months'], 'gemba_hse'), 'pending')),
                    'total_ncs' => $hseTotalNc,
                    'open_ncs' => $hseOpenNc,
                    'working_ncs' => $hseWorkingNc,
                    'review_ncs' => $hseReviewNc,
                    'closed_ncs' => $hseClosedNc
                ],
                'gemba_portion' => [
                    'walk_sessions' => 0,
                    'pending_audits' => 0,
                    'total_ncs' => $gembaTotalNc,
                    'open_ncs' => $gembaOpenNc,
                    'working_ncs' => $gembaWorkingNc,
                    'review_ncs' => $gembaReviewNc,
                    'closed_ncs' => $gembaClosedNc
                ]
            ]
        ];
    }

    /**
     * Graph 1: Performed vs Pending Audits (OE, Gemba/HSE, or Both based on scope)
     */
    private function getPerformedVsPendingChart(array $kpis, string $selectedModule = 'all'): array
    {
        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);
        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        $categories = [];
        $performed = [];
        $reaudits = [];
        $pending = [];
        $overdue = [];

        if ($checkOe) {
            $categories[] = 'OE Audits';
            $performed[] = (int)($kpis['oe']['performed_audits'] ?? 0);
            $reaudits[] = (int)($kpis['oe']['reaudits'] ?? 0);
            $pending[] = (int)($kpis['oe']['pending_audits'] ?? 0);
            $overdue[] = (int)($kpis['oe']['overdue_audits'] ?? 0);
        }

        if ($checkGembaHse) {
            $categories[] = 'Gemba/HSE Audits';
            $performed[] = (int)($kpis['gemba_hse']['performed_audits'] ?? 0);
            $reaudits[] = (int)($kpis['gemba_hse']['reaudits'] ?? 0);
            $pending[] = (int)($kpis['gemba_hse']['pending_audits'] ?? 0);
            $overdue[] = (int)($kpis['gemba_hse']['overdue_audits'] ?? 0);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Performed Audits',
                    'data' => $performed
                ],
                [
                    'name' => 'Re-Audits',
                    'data' => $reaudits
                ],
                [
                    'name' => 'Pending Audits',
                    'data' => $pending
                ],
                [
                    'name' => 'Overdue Audits',
                    'data' => $overdue
                ]
            ]
        ];
    }

    /**
     * Graph 2: Audit Due and Overdue Status (Donut Chart) - Dynamically Scoped to OE or Gemba/HSE
     */
    private function getDueOverdueStatusChart(array $kpis, string $selectedModule = 'all'): array
    {
        $isOeOnly = (strcasecmp($selectedModule, 'oe') === 0);
        $source = $isOeOnly ? ($kpis['oe'] ?? []) : ($kpis['gemba_hse'] ?? []);
        $title = $isOeOnly ? 'Graph 2: OE Audit Schedule Health' : 'Graph 2: Gemba/HSE Schedule Health';
        $badge = $isOeOnly ? 'OE Only' : 'Gemba/HSE (3-Month Cycle)';
        $completed = (int)($source['performed_audits'] ?? 0);
        $upcoming = (int)($source['upcoming_audits'] ?? 0);
        $due = (int)($source['due_audits'] ?? 0);
        $overdue = (int)($source['overdue_audits'] ?? 0);

        return [
            'title' => $title,
            'scope_badge' => $badge,
            'module' => $isOeOnly ? 'OE' : 'Gemba_HSE',
            'labels' => ['Completed', 'Upcoming', 'Due Soon (15d)', 'Overdue'],
            'series' => [$completed, $upcoming, $due, $overdue],
            'totals' => [
                'completed' => $completed,
                'upcoming' => $upcoming,
                'due' => $due,
                'overdue' => $overdue
            ]
        ];
    }

    /**
     * Graph 3: Monthly Audit Trend (Per Yearly Scope - 12 Months: OE vs Gemba/HSE)
     */
    private function getMonthlyTrendChart($db, int $auditorId, string $auditorName, int $year, string $selectedModule): array
    {
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        $monthKeys = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $oeSeries = array_fill_keys($monthKeys, 0);
        $gembaHseSeries = array_fill_keys($monthKeys, 0);

        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);
        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        // 1. OE Monthly
        if ($checkOe) {
            $oeSql = "SELECT DATE_FORMAT(audit_date, '%m') as m, COUNT(*) as c
                      FROM alert_final_structured_audit
                      WHERE (audit_by_user_id = {$auditorId} OR LOWER(TRIM(auditor_name)) IN ({$inAliases}))
                        AND YEAR(audit_date) = {$year}
                      GROUP BY m";
            $oeRows = $db->query($oeSql)->getResultArray();
            foreach ($oeRows as $r) {
                if (isset($oeSeries[$r['m']])) {
                    $oeSeries[$r['m']] = (int)$r['c'];
                }
            }
        }

        // 2. Gemba/HSE Monthly (HSE Audits + Pure Gemba Walks)
        if ($checkGembaHse) {
            // HSE audits
            $hseSql = "SELECT SUBSTRING(audit_date, 6, 2) as m, COUNT(*) as c
                       FROM alert_hse_audit_master
                       WHERE (LOWER(TRIM(auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(perform_audit_by)) IN ({$inAliases}))
                         AND SUBSTRING(audit_date, 1, 4) = '{$year}'
                       GROUP BY m";
            $hseRows = $db->query($hseSql)->getResultArray();
            foreach ($hseRows as $r) {
                if (isset($gembaHseSeries[$r['m']])) {
                    $gembaHseSeries[$r['m']] += (int)$r['c'];
                }
            }

            // Pure Gemba walks
            $gembaSql = "SELECT DATE_FORMAT(audit_report_date, '%m') as m, COUNT(DISTINCT site_name, audit_report_date) as c
                         FROM alert_gemba_audits
                         WHERE LOWER(TRIM(auditor_name)) IN ({$inAliases})
                           AND (source_module != 'hse_audit' OR source_module IS NULL)
                           AND YEAR(audit_report_date) = {$year}
                         GROUP BY m";
            $gembaRows = $db->query($gembaSql)->getResultArray();
            foreach ($gembaRows as $r) {
                if (isset($gembaHseSeries[$r['m']])) {
                    $gembaHseSeries[$r['m']] += (int)$r['c'];
                }
            }
        }

        $series = [];
        if ($checkOe) {
            $series[] = ['name' => 'OE Audits (Monthly)', 'data' => array_values($oeSeries)];
        }
        if ($checkGembaHse) {
            $series[] = ['name' => 'Gemba/HSE Audits (Combined)', 'data' => array_values($gembaHseSeries)];
        }

        return [
            'year' => $year,
            'categories' => $monthLabels,
            'series' => $series
        ];
    }

    /**
     * Graph 4: Re-Audit Analysis (OE, Gemba/HSE, or Both based on scope)
     */
    private function getReauditPerformanceChart(array $kpis, string $selectedModule = 'all'): array
    {
        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);
        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        $categories = [];
        $performedData = [];
        $reauditData = [];

        if ($checkOe) {
            $categories[] = 'OE Audits';
            $performedData[] = (int)($kpis['oe']['performed_audits'] ?? 0);
            $reauditData[] = (int)($kpis['oe']['reaudits'] ?? 0);
        }
        if ($checkGembaHse) {
            $categories[] = 'Gemba/HSE Audits';
            $performedData[] = (int)($kpis['gemba_hse']['performed_audits'] ?? 0);
            $reauditData[] = (int)($kpis['gemba_hse']['reaudits'] ?? 0);
        }

        return [
            'categories' => $categories,
            'series' => [
                [
                    'name' => 'Performed Audits (Original)',
                    'data' => $performedData
                ],
                [
                    'name' => 'Follow-up Re-Audits',
                    'data' => $reauditData
                ]
            ],
            'totals' => [
                'performed_total' => array_sum($performedData),
                'reaudit_total' => array_sum($reauditData)
            ]
        ];
    }

    /**
     * Dedicated NC Analytics (Status Distribution & Action Progress for OE, Gemba/HSE, or Both)
     */
    private function getNcAnalytics(array $kpis, string $selectedModule = 'all'): array
    {
        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);
        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        $categories = [];
        $openData = [];
        $workingData = [];
        $reviewData = [];
        $closedData = [];

        if ($checkOe) {
            $categories[] = 'OE Audit NCs';
            $openData[] = (int)($kpis['oe']['open_ncs'] ?? 0);
            $workingData[] = (int)($kpis['oe']['working_ncs'] ?? 0);
            $reviewData[] = (int)($kpis['oe']['review_ncs'] ?? 0);
            $closedData[] = (int)($kpis['oe']['closed_ncs'] ?? 0);
        }
        if ($checkGembaHse) {
            $categories[] = 'Gemba/HSE Audit NCs';
            $openData[] = (int)($kpis['gemba_hse']['open_ncs'] ?? 0);
            $workingData[] = (int)($kpis['gemba_hse']['working_ncs'] ?? 0);
            $reviewData[] = (int)($kpis['gemba_hse']['review_ncs'] ?? 0);
            $closedData[] = (int)($kpis['gemba_hse']['closed_ncs'] ?? 0);
        }

        // Action progress totals
        $source = ($checkOe && !$checkGembaHse) ? ($kpis['oe'] ?? []) : ((!$checkOe && $checkGembaHse) ? ($kpis['gemba_hse'] ?? []) : ($kpis['summary'] ?? []));

        return [
            'distribution' => [
                'categories' => $categories,
                'series' => [
                    [
                        'name' => 'Open',
                        'data' => $openData
                    ],
                    [
                        'name' => 'Under Action / Working',
                        'data' => $workingData
                    ],
                    [
                        'name' => 'Pending Review',
                        'data' => $reviewData
                    ],
                    [
                        'name' => 'Closed / Resolved',
                        'data' => $closedData
                    ]
                ]
            ],
            'action_progress' => [
                'labels' => ['Closed / Resolved', 'Pending Review', 'Under Action', 'Open'],
                'series' => [
                    (int)($source['closed_ncs'] ?? 0),
                    (int)($source['review_ncs'] ?? 0),
                    (int)($source['working_ncs'] ?? 0),
                    (int)($source['open_ncs'] ?? 0)
                ]
            ]
        ];
    }

    /**
     * Audit Schedule and Status Report Table (Grouped by OE and Gemba/HSE)
     */
    private function getAuditScheduleTable($db, int $auditorId, string $auditorName, string $selectedModule, string $selectedStatus, int $year, $fromDate = null, $toDate = null): array
    {
        $today = date('Y-m-d');
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        $table = [];
        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);
        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        // 1. OE Sites Schedule
        if ($checkOe) {
            $oeWhere = "(a.audit_by_user_id = {$auditorId} OR LOWER(TRIM(a.auditor_name)) IN ({$inAliases}))";
            if ($fromDate && $toDate) {
                $oeWhere .= " AND a.audit_date >= '{$fromDate}' AND a.audit_date <= '{$toDate}'";
            } elseif ($year) {
                $oeWhere .= " AND YEAR(a.audit_date) = {$year}";
            }

            $oeSql = "SELECT 
                'OE' as category,
                a.client_name,
                a.location as site_name,
                a.structured_audit_id as audit_id,
                a.audit_no,
                a.audit_date as latest_audit_date,
                a.report_date,
                IF(a.next_date != '0000-00-00' AND a.next_date IS NOT NULL, a.next_date, DATE_ADD(a.audit_date, INTERVAL 1 MONTH)) as next_audit_date,
                '1 Month (Monthly)' as audit_frequency,
                IF(a.reaudit = 0, 'Performed Audit', 'Re-Audit') as audit_type,
                IF(a.next_date < '{$today}' AND a.next_date != '0000-00-00', 'Overdue', IF(a.next_date <= DATE_ADD('{$today}', INTERVAL 15 DAY), 'Due', 'Upcoming')) as audit_status,
                (SELECT COUNT(*) FROM alert_final_structured_audit_details d WHERE d.structured_audit_id = a.structured_audit_id AND d.audit_finding = 'NO') as total_ncs,
                (SELECT COUNT(*) FROM alert_final_structured_audit_details d WHERE d.structured_audit_id = a.structured_audit_id AND d.audit_finding = 'NO' AND d.status IN (0, 1, 6, 2)) as pending_nc_actions
            FROM alert_final_structured_audit a
            WHERE {$oeWhere}
            ORDER BY a.audit_date DESC";

            $oeRows = $db->query($oeSql)->getResultArray();
            foreach ($oeRows as $r) {
                if ($selectedStatus !== 'all' && strtolower($selectedStatus) !== strtolower($r['audit_status'])) {
                    continue;
                }
                $table[] = [
                    'category' => 'OE',
                    'client_name' => $r['client_name'] ?: 'FM Logistic',
                    'site_name' => $r['site_name'] ?: 'N/A',
                    'latest_audit_date' => !empty($r['latest_audit_date']) ? date('d-M-Y', strtotime($r['latest_audit_date'])) : '-',
                    'report_date' => (!empty($r['report_date']) && $r['report_date'] !== '0000-00-00') ? date('d-M-Y', strtotime($r['report_date'])) : '-',
                    'next_audit_date' => !empty($r['next_audit_date']) ? date('d-M-Y', strtotime($r['next_audit_date'])) : '-',
                    'audit_frequency' => $r['audit_frequency'],
                    'audit_type' => $r['audit_type'],
                    'audit_status' => $r['audit_status'],
                    'total_ncs' => (int)$r['total_ncs'],
                    'pending_nc_actions' => (int)$r['pending_nc_actions'],
                    'action_url' => base_url('Masters/Audit_final_structure'),
                    'action_text' => 'View Audit'
                ];
            }
        }

        // 2. Gemba/HSE Sites Schedule (HSE Audits + Pure Gemba Walks)
        if ($checkGembaHse) {
            // Part 2.1: HSE Audits
            $hseWhere = "(LOWER(TRIM(m.auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(m.perform_audit_by)) IN ({$inAliases}))";
            if ($fromDate && $toDate) {
                $hseWhere .= " AND m.audit_date >= '{$fromDate}' AND m.audit_date <= '{$toDate}'";
            } elseif ($year) {
                $hseWhere .= " AND SUBSTRING(m.audit_date, 1, 4) = '{$year}'";
            }

            $hseSql = "SELECT 
                'Gemba/HSE' as category,
                m.client_name,
                m.location as site_name,
                m.hse_audit_id as audit_id,
                m.audit_no,
                m.audit_date as latest_audit_date,
                m.report_date,
                COALESCE(NULLIF(m.next_date, '0000-00-00'), NULLIF(m.report_date, '0000-00-00'), DATE_ADD(m.audit_date, INTERVAL 3 MONTH)) as next_audit_date,
                '3 Months (Quarterly)' as audit_frequency,
                IF(m.hse_audit_id = (SELECT MAX(h2.hse_audit_id) FROM alert_hse_audit_master h2 WHERE h2.audit_no = m.audit_no), 'Performed Audit', 'Re-Audit') as audit_type,
                IF(COALESCE(NULLIF(m.next_date, '0000-00-00'), NULLIF(m.report_date, '0000-00-00'), DATE_ADD(m.audit_date, INTERVAL 3 MONTH)) < '{$today}', 'Overdue', IF(COALESCE(NULLIF(m.next_date, '0000-00-00'), NULLIF(m.report_date, '0000-00-00'), DATE_ADD(m.audit_date, INTERVAL 3 MONTH)) <= DATE_ADD('{$today}', INTERVAL 15 DAY), 'Due', 'Upcoming')) as audit_status,
                (SELECT COUNT(*) FROM alert_hse_audit_details d WHERE d.hse_audit_id = m.hse_audit_id AND d.finding = 'NO') as total_ncs,
                (SELECT COUNT(*) FROM alert_hse_audit_details d WHERE d.hse_audit_id = m.hse_audit_id AND d.finding = 'NO' AND d.nc_status IN (0, 1, 5, 2)) as pending_nc_actions
            FROM alert_hse_audit_master m
            WHERE {$hseWhere}
            ORDER BY m.audit_date DESC";

            $hseRows = $db->query($hseSql)->getResultArray();
            foreach ($hseRows as $r) {
                if ($selectedStatus !== 'all' && strtolower($selectedStatus) !== strtolower($r['audit_status'])) {
                    continue;
                }
                $table[] = [
                    'category' => 'Gemba/HSE',
                    'client_name' => $r['client_name'] ?: 'FM Logistic',
                    'site_name' => $r['site_name'] ?: 'N/A',
                    'latest_audit_date' => !empty($r['latest_audit_date']) ? date('d-M-Y', strtotime($r['latest_audit_date'])) : '-',
                    'report_date' => (!empty($r['report_date']) && $r['report_date'] !== '0000-00-00') ? date('d-M-Y', strtotime($r['report_date'])) : '-',
                    'next_audit_date' => !empty($r['next_audit_date']) ? date('d-M-Y', strtotime($r['next_audit_date'])) : '-',
                    'audit_frequency' => $r['audit_frequency'],
                    'audit_type' => $r['audit_type'],
                    'audit_status' => $r['audit_status'],
                    'total_ncs' => (int)$r['total_ncs'],
                    'pending_nc_actions' => (int)$r['pending_nc_actions'],
                    'action_url' => base_url('Masters/GembaNcTracker'),
                    'action_text' => 'View Gemba/HSE NCs'
                ];
            }

            // Part 2.2: Pure Gemba Walks
            $gembaWhere = "LOWER(TRIM(g.auditor_name)) IN ({$inAliases}) AND (g.source_module != 'hse_audit' OR g.source_module IS NULL)";
            if ($fromDate && $toDate) {
                $gembaWhere .= " AND g.audit_report_date >= '{$fromDate}' AND g.audit_report_date <= '{$toDate}'";
            } elseif ($year) {
                $gembaWhere .= " AND YEAR(g.audit_report_date) = {$year}";
            }

            $gembaSql = "SELECT 
                'Gemba/HSE' as category,
                g.site_name as client_name,
                g.site_name,
                g.gemba_sr_no as audit_id,
                g.unique_no as audit_no,
                MAX(g.audit_report_date) as latest_audit_date,
                MAX(g.audit_report_date) as report_date,
                'Workflow Driven' as next_audit_date,
                'Walk / Event-Based' as audit_frequency,
                'Gemba Walk' as audit_type,
                IF(SUM(IF(g.target_date < '{$today}' AND g.target_date != '0000-00-00' AND g.nc_status NOT IN (3, 4), 1, 0)) > 0, 'Overdue', IF(SUM(IF(g.nc_status NOT IN (3, 4), 1, 0)) > 0, 'In Progress', 'Completed')) as audit_status,
                COUNT(*) as total_ncs,
                SUM(IF(g.nc_status IN (0, 1, 5, 2), 1, 0)) as pending_nc_actions
            FROM alert_gemba_audits g
            WHERE {$gembaWhere}
            GROUP BY g.site_name, g.audit_report_date
            ORDER BY latest_audit_date DESC";

            $gembaRows = $db->query($gembaSql)->getResultArray();
            foreach ($gembaRows as $r) {
                if ($selectedStatus !== 'all' && strtolower($selectedStatus) !== strtolower($r['audit_status'])) {
                    continue;
                }
                $table[] = [
                    'category' => 'Gemba/HSE',
                    'client_name' => $r['client_name'] ?: 'FM Logistic',
                    'site_name' => $r['site_name'] ?: 'N/A',
                    'latest_audit_date' => !empty($r['latest_audit_date']) ? date('d-M-Y', strtotime($r['latest_audit_date'])) : '-',
                    'report_date' => !empty($r['report_date']) ? date('d-M-Y', strtotime($r['report_date'])) : '-',
                    'next_audit_date' => $r['next_audit_date'],
                    'audit_frequency' => $r['audit_frequency'],
                    'audit_type' => $r['audit_type'],
                    'audit_status' => $r['audit_status'],
                    'total_ncs' => (int)$r['total_ncs'],
                    'pending_nc_actions' => (int)$r['pending_nc_actions'],
                    'action_url' => base_url('Masters/GembaNcTracker/template_type_filter/pending'),
                    'action_text' => 'View Gemba Points'
                ];
            }
        }

        return $table;
    }

    /**
     * Latest NC Reports Table:
     * Returns the latest NCs across OE and Gemba/HSE (including latest HSE NCs)
     * with NC number, site, audit category, audit date, description, responsible person, current status, due date.
     */
    private function getLatestNcsTable($db, int $auditorId, string $auditorName, string $selectedModule, string $selectedStatus, int $year, $fromDate = null, $toDate = null): array
    {
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        $ncs = [];
        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);
        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        // 1. Latest OE NCs (Only latest pending NCs, matching Oe_nc_tracker)
        if ($checkOe) {
            $oeWhere = "(a.audit_by_user_id = {$auditorId} OR LOWER(TRIM(a.auditor_name)) IN ({$inAliases}))";
            $oeWhere .= " AND (a.reaudit = 0 OR d.status IN (3, 5))";
            $oeWhere .= " AND (d.audit_finding = 'NO' OR d.status IN (3, 5))";
            if ($fromDate && $toDate) {
                $oeWhere .= " AND a.audit_date >= '{$fromDate}' AND a.audit_date <= '{$toDate}'";
            } elseif ($year) {
                $oeWhere .= " AND YEAR(a.audit_date) = {$year}";
            }

            $oeNcSql = "SELECT 
                d.audit_details_id as nc_id,
                a.audit_no,
                a.location as site_name,
                'OE' as category,
                'OE Audit' as audit_type,
                a.audit_date,
                d.audit_question as description,
                a.auditee_name as responsible,
                d.status as nc_status,
                CASE 
                    WHEN d.status = 0 THEN 'Open'
                    WHEN d.status IN (1, 4) THEN 'Under Action'
                    WHEN d.status = 6 THEN 'Cluster Review'
                    WHEN d.status = 2 THEN 'Auditor Review'
                    WHEN d.status = 3 THEN 'Closed'
                    WHEN d.status = 5 THEN 'Forced Closed'
                    ELSE 'Pending'
                END as status_label,
                a.next_date as due_date
            FROM alert_final_structured_audit_details d
            JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id
            WHERE {$oeWhere}
            ORDER BY a.audit_date DESC LIMIT 100";

            $oeRows = $db->query($oeNcSql)->getResultArray();
            foreach ($oeRows as $r) {
                $ncs[] = [
                    'nc_id' => 'OE-' . $r['nc_id'],
                    'audit_no' => $r['audit_no'] ?: 'OE Audit',
                    'site_name' => $r['site_name'] ?: 'N/A',
                    'category' => 'OE',
                    'audit_type' => $r['audit_type'],
                    'audit_date' => !empty($r['audit_date']) ? date('d-M-Y', strtotime($r['audit_date'])) : '-',
                    'raw_date' => $r['audit_date'] ?: '1970-01-01',
                    'description' => $r['description'] ?: 'No observation point text',
                    'responsible' => $r['responsible'] ?: 'Auditee / CM',
                    'nc_status' => (int)$r['nc_status'],
                    'status_label' => $r['status_label'],
                    'due_date' => (!empty($r['due_date']) && $r['due_date'] !== '0000-00-00') ? date('d-M-Y', strtotime($r['due_date'])) : 'Monthly Cycle',
                    'action_url' => base_url('Masters/Oe_nc_tracker'),
                    'action_text' => 'Track OE NC'
                ];
            }
        }

        // 2. Latest Gemba/HSE NCs (Directly from alert_gemba_audits, strictly matching Masters/GembaNcTracker)
        if ($checkGembaHse) {
            $gembaWhere = "g.status = 1 AND LOWER(TRIM(g.auditor_name)) IN ({$inAliases})";
            $gembaWhere .= " AND (
                g.source_module != 'hse_audit'
                OR (g.source_module = 'hse_audit' AND g.hse_detail_id IN (
                    SELECT latest_detail_id FROM (
                        SELECT MAX(d2.id) AS latest_detail_id
                        FROM alert_hse_audit_details d2
                        INNER JOIN alert_hse_audit_master latest_master ON latest_master.hse_audit_id = d2.hse_audit_id
                        INNER JOIN (
                            SELECT audit_no, MAX(hse_audit_id) AS latest_hse_audit_id
                            FROM alert_hse_audit_master
                            GROUP BY audit_no
                        ) latest_audit ON latest_audit.latest_hse_audit_id = latest_master.hse_audit_id
                        GROUP BY latest_master.audit_no, d2.question_id
                    ) as temp_latest_nc
                ))
            )";
            if ($fromDate && $toDate) {
                $gembaWhere .= " AND g.audit_report_date >= '{$fromDate}' AND g.audit_report_date <= '{$toDate}'";
            } elseif ($year) {
                $gembaWhere .= " AND YEAR(g.audit_report_date) = {$year}";
            }

            $gembaNcSql = "SELECT 
                g.gemba_sr_no as nc_id,
                g.unique_no as audit_no,
                g.site_name,
                'Gemba/HSE' as category,
                IF(g.source_module = 'hse_audit', 'HSE Audit', 'Gemba Walk') as audit_type,
                g.audit_report_date as audit_date,
                g.observation_point as description,
                COALESCE(NULLIF(g.account_manager, ''), NULLIF(g.cluster_manager_spoc, ''), 'Site Spoc / AM') as responsible,
                g.nc_status,
                CASE 
                    WHEN g.nc_status = 0 THEN 'Open'
                    WHEN g.nc_status = 1 THEN 'Under Action'
                    WHEN g.nc_status = 5 THEN 'Cluster Review'
                    WHEN g.nc_status = 2 THEN 'Auditor Review'
                    WHEN g.nc_status = 3 THEN 'Closed'
                    ELSE 'Pending'
                END as status_label,
                g.target_date as due_date
            FROM alert_gemba_audits g
            WHERE {$gembaWhere}
            ORDER BY g.audit_report_date DESC LIMIT 100";

            $gembaRows = $db->query($gembaNcSql)->getResultArray();
            foreach ($gembaRows as $r) {
                $ncs[] = [
                    'nc_id' => ($r['audit_type'] === 'HSE Audit' ? 'HSE-' : 'GMB-') . $r['nc_id'],
                    'audit_no' => $r['audit_no'] ?: 'Gemba/HSE',
                    'site_name' => $r['site_name'] ?: 'N/A',
                    'category' => 'Gemba/HSE',
                    'audit_type' => $r['audit_type'],
                    'audit_date' => !empty($r['audit_date']) ? date('d-M-Y', strtotime($r['audit_date'])) : '-',
                    'raw_date' => $r['audit_date'] ?: '1970-01-01',
                    'description' => $r['description'] ?: 'Observation Point',
                    'responsible' => $r['responsible'] ?: 'Site Spoc / AM',
                    'nc_status' => (int)$r['nc_status'],
                    'status_label' => $r['status_label'],
                    'due_date' => (!empty($r['due_date']) && $r['due_date'] !== '0000-00-00') ? date('d-M-Y', strtotime($r['due_date'])) : 'Target Date',
                    'action_url' => base_url('Masters/GembaNcTracker'),
                    'action_text' => 'Track NC'
                ];
            }
        }

        // Sort combined NCs chronologically (latest first)
        usort($ncs, function($a, $b) {
            return strcmp($b['raw_date'], $a['raw_date']);
        });

        return $ncs;
    }
}
