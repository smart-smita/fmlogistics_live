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

        // Filters: Year, Date Range, Module ('all', 'OE', 'Gemba_HSE'), Status
        $requestedYear = trim($this->request->getVar('year') ?? date('Y'));
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

        // 1. Two-Category KPI Metrics (OE and Gemba/HSE)
        $kpis = $this->calculateAuditorKpis($db, $auditorId, $auditorName, $yearInt, $fromDate, $toDate, $selectedModule);

        // 2. Graph 1: Performed vs Pending Audits (OE, Gemba/HSE, or Both based on scope)
        $performedVsPendingChart = $this->getPerformedVsPendingChart($kpis, $selectedModule);

        // 3. Graph 2: Audit Due and Overdue Status (OE, Gemba/HSE, or Both based on scope)
        $dueOverdueStatusChart = $this->getDueOverdueStatusChart($kpis, $selectedModule);

        // 4. Graph 3: Monthly Audit Trend (Per Yearly Scope - 12 Months: OE vs Gemba/HSE)
        $monthlyTrendChart = $this->getMonthlyTrendChart($db, $auditorId, $auditorName, $yearInt, $selectedModule);

        // 5. Graph 4: Re-Audit Analysis (Performed vs Re-Audits)
        $reauditChart = $this->getReauditPerformanceChart($kpis, $selectedModule);

        // 6. Dedicated NC Analytics (Status Distribution & Action Progress)
        $ncAnalytics = $this->getNcAnalytics($kpis, $selectedModule);

        // 7. Audit Schedule and Report Table (Site-level schedules with next audit dates)
        $scheduleTable = $this->getAuditScheduleTable($db, $auditorId, $auditorName, $selectedModule, $selectedStatus, $yearInt, $fromDate, $toDate);

        // 8. Latest NCs Report Table (Combined Gemba/HSE NCs and OE NCs with full details)
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
                'nc_analytics' => $ncAnalytics
            ],
            'schedule_table' => $scheduleTable,
            'latest_ncs' => $latestNcsTable,
            'filters_applied' => [
                'year' => $requestedYear,
                'date_range' => $dateRange,
                'from_date' => $fromDate,
                'to_date' => $toDate,
                'module' => $selectedModule,
                'status' => $selectedStatus
            ]
        ]);
    }

    /**
     * Calculates complete KPIs for the 2 audit categories:
     * 1. OE Audit
     * 2. Gemba/HSE Audit (HSE combined into Gemba/HSE without duplicate counting)
     */
    private function calculateAuditorKpis($db, int $auditorId, string $auditorName, $year = null, $fromDate = null, $toDate = null, $selectedModule = 'all'): array
    {
        $today = date('Y-m-d');
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        $yearFilter = is_numeric($year) ? (int)$year : null;

        // ════════════════════════════════════════════════════════════
        // CATEGORY 1: OE AUDIT DASHBOARD (Monthly Frequency)
        // ════════════════════════════════════════════════════════════
        $oeTotal = 0;
        $oePerformed = 0;
        $oeReaudits = 0;
        $oePending = 0;
        $oeDue = 0;
        $oeOverdue = 0;
        $oeLastDate = '-';
        $oeNextDate = '-';
        $oeTotalNc = 0;
        $oeOpenNc = 0;
        $oeWorkingNc = 0;
        $oeReviewNc = 0;
        $oeClosedNc = 0;

        $checkOe = ($selectedModule === 'all' || strcasecmp($selectedModule, 'oe') === 0);

        if ($checkOe) {
            $oeWhere = "(audit_by_user_id = {$auditorId} OR LOWER(TRIM(auditor_name)) IN ({$inAliases}))";
            if ($fromDate && $toDate) {
                $oeWhere .= " AND audit_date >= '{$fromDate}' AND audit_date <= '{$toDate}'";
            } elseif ($yearFilter) {
                $oeWhere .= " AND YEAR(audit_date) = {$yearFilter}";
            }

            // OE Master query: reaudit = 0 is original performed audit; reaudit > 0 is re-audit
            $oeAuditSql = "SELECT 
                COUNT(*) as total_oe,
                SUM(IF(reaudit = 0, 1, 0)) as performed_oe,
                SUM(IF(reaudit > 0, 1, 0)) as reaudit_oe,
                MAX(audit_date) as last_oe_date,
                SUM(IF(next_date IS NOT NULL AND next_date != '0000-00-00' AND next_date >= '{$today}', 1, 0)) as pending_oe,
                SUM(IF(next_date IS NOT NULL AND next_date != '0000-00-00' AND next_date > DATE_ADD('{$today}', INTERVAL 15 DAY), 1, 0)) as upcoming_oe,
                SUM(IF(next_date IS NOT NULL AND next_date != '0000-00-00' AND next_date BETWEEN '{$today}' AND DATE_ADD('{$today}', INTERVAL 15 DAY), 1, 0)) as due_oe,
                SUM(IF(next_date IS NOT NULL AND next_date != '0000-00-00' AND next_date < '{$today}', 1, 0)) as overdue_oe
            FROM alert_final_structured_audit
            WHERE {$oeWhere}";
            $oeRes = $db->query($oeAuditSql)->getRowArray();

            if ($oeRes) {
                $oeTotal = (int)($oeRes['total_oe'] ?? 0);
                $oePerformed = (int)($oeRes['performed_oe'] ?? 0);
                $oeReaudits = (int)($oeRes['reaudit_oe'] ?? 0);
                $oePending = (int)($oeRes['pending_oe'] ?? 0);
                $oeUpcoming = (int)($oeRes['upcoming_oe'] ?? 0);
                $oeDue = (int)($oeRes['due_oe'] ?? 0);
                $oeOverdue = (int)($oeRes['overdue_oe'] ?? 0);
                if (!empty($oeRes['last_oe_date'])) {
                    $oeLastDate = $oeRes['last_oe_date'];
                    $oeNextDate = date('Y-m-d', strtotime('+1 month', strtotime($oeLastDate)));
                }
            }

            // OE NC query: Matches Oe_nc_tracker by taking only latest pending NCs
            // (Excludes historical re-audited records unless status is Closed(3) or Forced Closed(5))
            // Statuses: 0 = Open, 1 & 4 = Working/Draft, 6 & 2 = Pending Review, 3 & 5 = Closed/Forced Closed
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
        }

        // ════════════════════════════════════════════════════════════
        // CATEGORY 2: GEMBA/HSE AUDIT DASHBOARD (Combined)
        // HSE is part of Gemba/HSE. Zero duplicate counting.
        // ════════════════════════════════════════════════════════════
        $hseTotal = 0;
        $hsePerformed = 0;
        $hseReaudits = 0;
        $hsePending = 0;
        $hseUpcoming = 0;
        $hseDue = 0;
        $hseOverdue = 0;
        $hseLastDate = '-';

        $gembaWalks = 0;
        $gembaPending = 0;
        $gembaUpcoming = 0;
        $gembaDue = 0;
        $gembaOverdue = 0;
        $gembaLastDate = '-';

        $gembaHseTotalNc = 0;
        $gembaHseOpenNc = 0;
        $gembaHseWorkingNc = 0;
        $gembaHseReviewNc = 0;
        $gembaHseClosedNc = 0;

        $hseTotalNc = 0;
        $hseOpenNc = 0;
        $hseWorkingNc = 0;
        $hseReviewNc = 0;
        $hseClosedNc = 0;

        $gembaTotalNc = 0;
        $gembaOpenNc = 0;
        $gembaWorkingNc = 0;
        $gembaReviewNc = 0;
        $gembaClosedNc = 0;

        $checkGembaHse = ($selectedModule === 'all' || in_array(strtolower($selectedModule), ['gemba_hse', 'gemba/hse', 'gemba', 'hse']));

        if ($checkGembaHse) {
            // Part 2.1: HSE Audits from alert_hse_audit_master
            // HSE schedule is evaluated using a 3-month cycle (quarterly) from audit_date
            $hseWhere = "(LOWER(TRIM(auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(perform_audit_by)) IN ({$inAliases}))";
            if ($fromDate && $toDate) {
                $hseWhere .= " AND audit_date >= '{$fromDate}' AND audit_date <= '{$toDate}'";
            } elseif ($yearFilter) {
                $hseWhere .= " AND SUBSTRING(audit_date, 1, 4) = '{$yearFilter}'";
            }

            // HSE Performed audits = distinct audit_no; Re-audits = total - distinct audit_no
            $hseAuditSql = "SELECT 
                COUNT(*) as total_hse,
                COUNT(DISTINCT audit_no) as performed_hse,
                (COUNT(*) - COUNT(DISTINCT audit_no)) as reaudit_hse,
                MAX(audit_date) as last_hse_date,
                SUM(IF(COALESCE(NULLIF(next_date, '0000-00-00'), NULLIF(report_date, '0000-00-00'), DATE_ADD(audit_date, INTERVAL 3 MONTH)) >= '{$today}', 1, 0)) as pending_hse,
                SUM(IF(COALESCE(NULLIF(next_date, '0000-00-00'), NULLIF(report_date, '0000-00-00'), DATE_ADD(audit_date, INTERVAL 3 MONTH)) > DATE_ADD('{$today}', INTERVAL 15 DAY), 1, 0)) as upcoming_hse,
                SUM(IF(COALESCE(NULLIF(next_date, '0000-00-00'), NULLIF(report_date, '0000-00-00'), DATE_ADD(audit_date, INTERVAL 3 MONTH)) BETWEEN '{$today}' AND DATE_ADD('{$today}', INTERVAL 15 DAY), 1, 0)) as due_hse,
                SUM(IF(COALESCE(NULLIF(next_date, '0000-00-00'), NULLIF(report_date, '0000-00-00'), DATE_ADD(audit_date, INTERVAL 3 MONTH)) < '{$today}', 1, 0)) as overdue_hse
            FROM alert_hse_audit_master
            WHERE {$hseWhere}";
            $hseRes = $db->query($hseAuditSql)->getRowArray();

            if ($hseRes) {
                $hseTotal = (int)($hseRes['total_hse'] ?? 0);
                $hsePerformed = (int)($hseRes['performed_hse'] ?? 0);
                $hseReaudits = max(0, (int)($hseRes['reaudit_hse'] ?? 0));
                $hsePending = (int)($hseRes['pending_hse'] ?? 0);
                $hseUpcoming = (int)($hseRes['upcoming_hse'] ?? 0);
                $hseDue = (int)($hseRes['due_hse'] ?? 0);
                $hseOverdue = (int)($hseRes['overdue_hse'] ?? 0);
                $hseLastDate = $hseRes['last_hse_date'] ?: '-';
            }

            // Part 2.2: Gemba/HSE NCs from alert_gemba_audits (Directly matching Masters/GembaNcTracker)
            // alert_gemba_audits is the authoritative store for all Gemba walk observation points
            // and synced HSE audit NC points. Applies exact deduplication for latest HSE re-audits.
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

            // Part 2.3: Pure Gemba Walk Sessions and Schedule from alert_gemba_audits
            $gembaWhere = "status = 1 AND LOWER(TRIM(auditor_name)) IN ({$inAliases}) AND (source_module != 'hse_audit' OR source_module IS NULL)";
            if ($fromDate && $toDate) {
                $gembaWhere .= " AND audit_report_date >= '{$fromDate}' AND audit_report_date <= '{$toDate}'";
            } elseif ($yearFilter) {
                $gembaWhere .= " AND YEAR(audit_report_date) = {$yearFilter}";
            }

            $gembaAuditSql = "SELECT 
                COUNT(DISTINCT site_name, audit_report_date) as total_walks,
                MAX(audit_report_date) as last_gemba_date,
                SUM(IF(target_date IS NOT NULL AND target_date != '0000-00-00' AND target_date >= '{$today}' AND nc_status NOT IN (3, 4), 1, 0)) as pending_gemba,
                SUM(IF(target_date IS NOT NULL AND target_date != '0000-00-00' AND target_date > DATE_ADD('{$today}', INTERVAL 15 DAY) AND nc_status NOT IN (3, 4), 1, 0)) as upcoming_gemba,
                SUM(IF(target_date IS NOT NULL AND target_date != '0000-00-00' AND target_date BETWEEN '{$today}' AND DATE_ADD('{$today}', INTERVAL 15 DAY) AND nc_status NOT IN (3, 4), 1, 0)) as due_gemba,
                SUM(IF(target_date IS NOT NULL AND target_date != '0000-00-00' AND target_date < '{$today}' AND nc_status NOT IN (3, 4), 1, 0)) as overdue_gemba
            FROM alert_gemba_audits
            WHERE {$gembaWhere}";
            $gembaRes = $db->query($gembaAuditSql)->getRowArray();

            if ($gembaRes) {
                $gembaWalks = (int)($gembaRes['total_walks'] ?? 0);
                $gembaPending = (int)($gembaRes['pending_gemba'] ?? 0);
                $gembaUpcoming = (int)($gembaRes['upcoming_gemba'] ?? 0);
                $gembaDue = (int)($gembaRes['due_gemba'] ?? 0);
                $gembaOverdue = (int)($gembaRes['overdue_gemba'] ?? 0);
                $gembaLastDate = $gembaRes['last_gemba_date'] ?: '-';
            }
        }

        // Combined Gemba/HSE Metrics (Zero double-counting)
        $gembaHseTotalAudits = $hseTotal + $gembaWalks;
        $gembaHsePerformedAudits = $hsePerformed + $gembaWalks;
        $gembaHseReaudits = $hseReaudits;
        $gembaHsePendingAudits = $hsePending + $gembaPending;
        $gembaHseUpcomingAudits = $hseUpcoming + $gembaUpcoming;
        $gembaHseDueAudits = $hseDue + $gembaDue;
        $gembaHseOverdueAudits = $hseOverdue + $gembaOverdue;

        $gembaHseLastDate = '-';
        if ($hseLastDate !== '-' && $gembaLastDate !== '-') {
            $gembaHseLastDate = (strtotime($hseLastDate) > strtotime($gembaLastDate)) ? $hseLastDate : $gembaLastDate;
        } elseif ($hseLastDate !== '-') {
            $gembaHseLastDate = $hseLastDate;
        } elseif ($gembaLastDate !== '-') {
            $gembaHseLastDate = $gembaLastDate;
        }

        // Summary Aggregates across the 2 categories
        $totalAudits = $oeTotal + $gembaHseTotalAudits;
        $totalPerformed = $oePerformed + $gembaHsePerformedAudits;
        $totalReaudits = $oeReaudits + $gembaHseReaudits;
        $totalPending = $oePending + $gembaHsePendingAudits;
        $totalUpcoming = ($oeUpcoming ?? 0) + $gembaHseUpcomingAudits;
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
                'upcoming_audits' => $oeUpcoming ?? 0,
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
                    'total_audits' => $hseTotal,
                    'performed_audits' => $hsePerformed,
                    'reaudits' => $hseReaudits,
                    'pending_audits' => $hsePending,
                    'total_ncs' => $hseTotalNc,
                    'open_ncs' => $hseOpenNc,
                    'working_ncs' => $hseWorkingNc,
                    'review_ncs' => $hseReviewNc,
                    'closed_ncs' => $hseClosedNc
                ],
                'gemba_portion' => [
                    'walk_sessions' => $gembaWalks,
                    'pending_audits' => $gembaPending,
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
                    'action_url' => base_url('Masters/Audit_final_structure/auditViewDetailsPdf/' . $r['audit_id']),
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
