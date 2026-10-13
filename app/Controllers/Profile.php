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

        // If Auditor, prepare initial summary statistics for instant initial page render (Yearly Scope)
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

        // Save password as it is (plain text)
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

        // Filters: Year, Date Range, Module, Status
        $requestedYear = trim($this->request->getVar('year') ?? date('Y'));
        $dateRange = trim($this->request->getVar('date_range') ?? 'yearly');
        $customStart = trim($this->request->getVar('start_date') ?? '');
        $customEnd = trim($this->request->getVar('end_date') ?? '');
        $selectedModule = trim($this->request->getVar('module') ?? 'all'); // all, OE, HSE, Gemba
        $selectedStatus = trim($this->request->getVar('status') ?? 'all'); // all, completed, pending, overdue, due

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

        // 1. Module-Separated KPI Metrics (OE, HSE, Gemba)
        $kpis = $this->calculateAuditorKpis($db, $auditorId, $auditorName, $yearInt, $fromDate, $toDate, $selectedModule);

        // 2. Graph 1: Performed vs Pending Audits (Grouped by module)
        $performedVsPendingChart = $this->getPerformedVsPendingChart($kpis);

        // 3. Graph 2: Audit Due and Overdue Status (Upcoming, Due, Overdue, Completed)
        $dueOverdueStatusChart = $this->getDueOverdueStatusChart($kpis);

        // 4. Graph 3: Monthly Audit Trend (Per Yearly Scope - 12 Months)
        $monthlyTrendChart = $this->getMonthlyTrendChart($db, $auditorId, $auditorName, $yearInt, $selectedModule);

        // 5. Graph 4: Re-Audit Analysis (Latest Performed vs Previous Re-Audits)
        $reauditChart = $this->getReauditPerformanceChart($kpis);

        // 6. Dedicated NC Analytics (Status Distribution & Action Progress)
        $ncAnalytics = $this->getNcAnalytics($kpis);

        // 7. Audit Schedule and Report Table (Site-level schedules with next audit dates)
        $scheduleTable = $this->getAuditScheduleTable($db, $auditorId, $auditorName, $selectedModule, $selectedStatus, $yearInt);

        return $this->response->setJSON([
            'status' => 1,
            'auditor' => [
                'user_id' => $auditorId,
                'user_name' => $auditorName,
                'designation' => $targetUser['user_designation'] ?? 'Auditor',
                'email' => $targetUser['user_email'] ?? '',
                'emp_code' => $targetUser['user_emp_code'] ?? ''
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
     * Calculates complete module-separated KPIs for OE, HSE, and Gemba
     */
    private function calculateAuditorKpis($db, int $auditorId, string $auditorName, $year = null, $fromDate = null, $toDate = null, $selectedModule = 'all'): array
    {
        $today = date('Y-m-d');
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        $yearFilter = is_numeric($year) ? (int)$year : null;

        // ════════════════════════════════════════════════════════════
        // SECTION A: OE AUDIT DASHBOARD (Monthly Frequency - 1 Month)
        // ════════════════════════════════════════════════════════════
        $oeTotal = 0;
        $oeLatest = 0;
        $oeReaudits = 0;
        $oePending = 0;
        $oeDue = 0;
        $oeOverdue = 0;
        $oeCompleted = 0;
        $oeLastDate = '-';
        $oeNextDate = '-';
        $oeTotalNc = 0;
        $oeOpenNc = 0;
        $oeWorkingNc = 0;
        $oeClusterReviewNc = 0;
        $oeAuditorReviewNc = 0;
        $oeClosedNc = 0;
        $oePendingActions = 0;
        $oeCompletedActions = 0;
        $oeOverdueActions = 0;

        if ($selectedModule === 'all' || strtolower($selectedModule) === 'oe') {
            $oeWhere = "(audit_by_user_id = {$auditorId} OR LOWER(TRIM(auditor_name)) IN ({$inAliases}))";
            if ($fromDate && $toDate) {
                $oeWhere .= " AND audit_date >= '{$fromDate}' AND audit_date <= '{$toDate}'";
            } elseif ($yearFilter) {
                $oeWhere .= " AND YEAR(audit_date) = {$yearFilter}";
            }

            // Master query
            $oeAuditSql = "SELECT 
                COUNT(*) as total_oe,
                SUM(IF(reaudit = 0, 1, 0)) as latest_oe,
                SUM(IF(reaudit > 0, 1, 0)) as reaudit_oe,
                MAX(audit_date) as last_oe_date,
                SUM(IF(next_date IS NOT NULL AND next_date != '0000-00-00' AND next_date >= '{$today}', 1, 0)) as pending_oe,
                SUM(IF(next_date IS NOT NULL AND next_date != '0000-00-00' AND next_date BETWEEN '{$today}' AND DATE_ADD('{$today}', INTERVAL 15 DAY), 1, 0)) as due_oe,
                SUM(IF(next_date IS NOT NULL AND next_date != '0000-00-00' AND next_date < '{$today}', 1, 0)) as overdue_oe
            FROM alert_final_structured_audit
            WHERE {$oeWhere}";
            $oeRes = $db->query($oeAuditSql)->getRowArray();

            if ($oeRes) {
                $oeTotal = (int)($oeRes['total_oe'] ?? 0);
                $oeLatest = (int)($oeRes['latest_oe'] ?? 0);
                $oeReaudits = (int)($oeRes['reaudit_oe'] ?? 0);
                $oePending = (int)($oeRes['pending_oe'] ?? 0);
                $oeDue = (int)($oeRes['due_oe'] ?? 0);
                $oeOverdue = (int)($oeRes['overdue_oe'] ?? 0);
                $oeCompleted = $oeLatest;
                if (!empty($oeRes['last_oe_date'])) {
                    $oeLastDate = $oeRes['last_oe_date'];
                    // OE 1 Month Monthly Frequency
                    $oeNextDate = date('Y-m-d', strtotime('+1 month', strtotime($oeLastDate)));
                }
            }

            // OE NC query
            $oeNcSql = "SELECT 
                COUNT(*) as total_nc,
                SUM(IF(d.status = 0, 1, 0)) as open_nc,
                SUM(IF(d.status = 1, 1, 0)) as working_nc,
                SUM(IF(d.status = 6, 1, 0)) as cluster_review_nc,
                SUM(IF(d.status = 2, 1, 0)) as auditor_review_nc,
                SUM(IF(d.status = 3, 1, 0)) as closed_nc
            FROM alert_final_structured_audit_details d
            JOIN alert_final_structured_audit a ON a.structured_audit_id = d.structured_audit_id
            WHERE (a.audit_by_user_id = {$auditorId} OR LOWER(TRIM(a.auditor_name)) IN ({$inAliases}))
              AND d.audit_finding = 'NO'";
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
                $oeClusterReviewNc = (int)($oeNcRes['cluster_review_nc'] ?? 0);
                $oeAuditorReviewNc = (int)($oeNcRes['auditor_review_nc'] ?? 0);
                $oeClosedNc = (int)($oeNcRes['closed_nc'] ?? 0);
                $oePendingActions = $oeOpenNc + $oeWorkingNc + $oeClusterReviewNc + $oeAuditorReviewNc;
                $oeCompletedActions = $oeClosedNc;
            }
        }

        // ════════════════════════════════════════════════════════════
        // SECTION B: HSE AUDIT DASHBOARD (Quarterly - 3 Months)
        // ════════════════════════════════════════════════════════════
        $hseTotal = 0;
        $hseLatest = 0;
        $hseReaudits = 0;
        $hsePending = 0;
        $hseDue = 0;
        $hseOverdue = 0;
        $hseCompleted = 0;
        $hseLastDate = '-';
        $hseNextDate = '-';
        $hseTotalNc = 0;
        $hseOpenNc = 0;
        $hseWorkingNc = 0;
        $hseClusterReviewNc = 0;
        $hseAuditorReviewNc = 0;
        $hseClosedNc = 0;
        $hsePendingActions = 0;
        $hseCompletedActions = 0;
        $hseOverdueActions = 0;

        if ($selectedModule === 'all' || strtolower($selectedModule) === 'hse') {
            $hseWhere = "(LOWER(TRIM(auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(perform_audit_by)) IN ({$inAliases}))";
            if ($fromDate && $toDate) {
                $hseWhere .= " AND audit_date >= '{$fromDate}' AND audit_date <= '{$toDate}'";
            } elseif ($yearFilter) {
                $hseWhere .= " AND SUBSTRING(audit_date, 1, 4) = '{$yearFilter}'";
            }

            // HSE Master query
            $hseAuditSql = "SELECT 
                COUNT(*) as total_hse,
                COUNT(DISTINCT audit_no) as latest_hse,
                (COUNT(*) - COUNT(DISTINCT audit_no)) as reaudit_hse,
                MAX(audit_date) as last_hse_date,
                SUM(IF(report_date IS NOT NULL AND report_date != '0000-00-00' AND report_date >= '{$today}', 1, 0)) as pending_hse,
                SUM(IF(report_date IS NOT NULL AND report_date != '0000-00-00' AND report_date BETWEEN '{$today}' AND DATE_ADD('{$today}', INTERVAL 15 DAY), 1, 0)) as due_hse,
                SUM(IF(report_date IS NOT NULL AND report_date != '0000-00-00' AND report_date < '{$today}', 1, 0)) as overdue_hse
            FROM alert_hse_audit_master
            WHERE {$hseWhere}";
            $hseRes = $db->query($hseAuditSql)->getRowArray();

            if ($hseRes) {
                $hseTotal = (int)($hseRes['total_hse'] ?? 0);
                $hseLatest = (int)($hseRes['latest_hse'] ?? 0);
                $hseReaudits = max(0, (int)($hseRes['reaudit_hse'] ?? 0));
                $hsePending = (int)($hseRes['pending_hse'] ?? 0);
                $hseDue = (int)($hseRes['due_hse'] ?? 0);
                $hseOverdue = (int)($hseRes['overdue_hse'] ?? 0);
                $hseCompleted = $hseLatest;
                if (!empty($hseRes['last_hse_date'])) {
                    $hseLastDate = $hseRes['last_hse_date'];
                    // HSE 3 Months Quarterly Frequency
                    $hseNextDate = date('Y-m-d', strtotime('+3 months', strtotime($hseLastDate)));
                }
            }

            // HSE NC query
            $hseNcSql = "SELECT 
                COUNT(*) as total_nc,
                SUM(IF(d.nc_status = 0, 1, 0)) as open_nc,
                SUM(IF(d.nc_status = 1, 1, 0)) as working_nc,
                SUM(IF(d.nc_status = 5, 1, 0)) as cluster_review_nc,
                SUM(IF(d.nc_status = 2, 1, 0)) as auditor_review_nc,
                SUM(IF(d.nc_status = 3, 1, 0)) as closed_nc
            FROM alert_hse_audit_details d
            JOIN alert_hse_audit_master m ON m.hse_audit_id = d.hse_audit_id
            WHERE (LOWER(TRIM(m.auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(m.perform_audit_by)) IN ({$inAliases}))
              AND d.finding = 'NO'";
            if ($fromDate && $toDate) {
                $hseNcSql .= " AND m.audit_date >= '{$fromDate}' AND m.audit_date <= '{$toDate}'";
            } elseif ($yearFilter) {
                $hseNcSql .= " AND SUBSTRING(m.audit_date, 1, 4) = '{$yearFilter}'";
            }
            $hseNcRes = $db->query($hseNcSql)->getRowArray();
            if ($hseNcRes) {
                $hseTotalNc = (int)($hseNcRes['total_nc'] ?? 0);
                $hseOpenNc = (int)($hseNcRes['open_nc'] ?? 0);
                $hseWorkingNc = (int)($hseNcRes['working_nc'] ?? 0);
                $hseClusterReviewNc = (int)($hseNcRes['cluster_review_nc'] ?? 0);
                $hseAuditorReviewNc = (int)($hseNcRes['auditor_review_nc'] ?? 0);
                $hseClosedNc = (int)($hseNcRes['closed_nc'] ?? 0);
                $hsePendingActions = $hseOpenNc + $hseWorkingNc + $hseClusterReviewNc + $hseAuditorReviewNc;
                $hseCompletedActions = $hseClosedNc;
            }
        }

        // ════════════════════════════════════════════════════════════
        // SECTION C: GEMBA AUDIT DASHBOARD (Walk / Event Workflow)
        // ════════════════════════════════════════════════════════════
        $gembaTotal = 0;
        $gembaLatest = 0;
        $gembaReaudits = 0;
        $gembaPending = 0;
        $gembaDue = 0;
        $gembaOverdue = 0;
        $gembaCompleted = 0;
        $gembaLastDate = '-';
        $gembaNextDate = 'Workflow Driven';
        $gembaTotalNc = 0;
        $gembaOpenNc = 0;
        $gembaWorkingNc = 0;
        $gembaClusterReviewNc = 0;
        $gembaAuditorReviewNc = 0;
        $gembaClosedNc = 0;
        $gembaPendingActions = 0;
        $gembaCompletedActions = 0;
        $gembaOverdueActions = 0;

        if ($selectedModule === 'all' || strtolower($selectedModule) === 'gemba') {
            $gembaWhere = "LOWER(TRIM(auditor_name)) IN ({$inAliases})";
            if ($fromDate && $toDate) {
                $gembaWhere .= " AND audit_report_date >= '{$fromDate}' AND audit_report_date <= '{$toDate}'";
            } elseif ($yearFilter) {
                $gembaWhere .= " AND YEAR(audit_report_date) = {$yearFilter}";
            }

            // Gemba Walks and Observations
            $gembaAuditSql = "SELECT 
                COUNT(*) as total_points,
                COUNT(DISTINCT site_name, audit_report_date) as total_events,
                MAX(audit_report_date) as last_gemba_date,
                SUM(IF(nc_status = 2, 1, 0)) as auditor_review_gemba,
                SUM(IF(nc_status IN (0, 1, 5, 2), 1, 0)) as pending_gemba,
                SUM(IF(target_date < '{$today}' AND target_date != '0000-00-00' AND nc_status NOT IN (3, 4), 1, 0)) as overdue_gemba,
                SUM(IF(target_date BETWEEN '{$today}' AND DATE_ADD('{$today}', INTERVAL 15 DAY) AND nc_status NOT IN (3, 4), 1, 0)) as due_gemba,
                SUM(IF(nc_status = 0, 1, 0)) as open_nc,
                SUM(IF(nc_status = 1, 1, 0)) as working_nc,
                SUM(IF(nc_status = 5, 1, 0)) as cluster_review_nc,
                SUM(IF(nc_status = 3, 1, 0)) as closed_nc
            FROM alert_gemba_audits
            WHERE {$gembaWhere}";
            $gembaRes = $db->query($gembaAuditSql)->getRowArray();

            if ($gembaRes) {
                $gembaTotal = (int)($gembaRes['total_events'] ?? 0);
                $gembaLatest = $gembaTotal;
                $gembaReaudits = 0; // Gemba does not have formal re-audit structure
                $gembaPending = (int)($gembaRes['auditor_review_gemba'] ?? 0);
                $gembaDue = (int)($gembaRes['due_gemba'] ?? 0);
                $gembaOverdue = (int)($gembaRes['overdue_gemba'] ?? 0);
                $gembaCompleted = (int)($gembaRes['closed_nc'] ?? 0);
                $gembaLastDate = $gembaRes['last_gemba_date'] ?: '-';

                $gembaTotalNc = (int)($gembaRes['total_points'] ?? 0);
                $gembaOpenNc = (int)($gembaRes['open_nc'] ?? 0);
                $gembaWorkingNc = (int)($gembaRes['working_nc'] ?? 0);
                $gembaClusterReviewNc = (int)($gembaRes['cluster_review_nc'] ?? 0);
                $gembaAuditorReviewNc = (int)($gembaRes['auditor_review_gemba'] ?? 0);
                $gembaClosedNc = (int)($gembaRes['closed_nc'] ?? 0);
                $gembaPendingActions = (int)($gembaRes['pending_gemba'] ?? 0);
                $gembaCompletedActions = $gembaClosedNc;
                $gembaOverdueActions = $gembaOverdue;
            }
        }

        // Summary Aggregates
        $totalAuditsPerformed = $oeTotal + $hseTotal + $gembaTotal;
        $totalPendingAudits = $oePending + $hsePending + $gembaPending;
        $totalOverdueAudits = $oeOverdue + $hseOverdue + $gembaOverdue;
        $totalDueAudits = $oeDue + $hseDue + $gembaDue;
        $totalReaudits = $oeReaudits + $hseReaudits;
        $totalNcCompleted = $oeCompletedActions + $hseCompletedActions + $gembaCompletedActions;
        $totalNcPending = $oePendingActions + $hsePendingActions + $gembaPendingActions;
        $totalNcOverdue = $oeOverdueActions + $hseOverdueActions + $gembaOverdueActions;

        return [
            // Backward-compatible top-level keys
            'total_audits_performed' => $totalAuditsPerformed,
            'pending_audits' => $totalPendingAudits,
            'overdue_audits' => $totalOverdueAudits,
            'due_audits' => $totalDueAudits,
            'reaudits_performed' => $totalReaudits,
            'nc_actions_completed' => $totalNcCompleted,
            'nc_actions_pending' => $totalNcPending,
            'nc_actions_overdue' => $totalNcOverdue,
            'oe_audits' => $oeTotal,
            'hse_audits' => $hseTotal,
            'gemba_audits' => $gembaTotal,
            'oe_reaudits' => $oeReaudits,
            'hse_reaudits' => $hseReaudits,
            'oe_pending' => $oePending,
            'hse_pending' => $hsePending,
            'gemba_pending' => $gembaPending,

            // Section 3: Distinct Module-Specific Structures
            'summary' => [
                'total_audits_performed' => $totalAuditsPerformed,
                'pending_audits' => $totalPendingAudits,
                'overdue_audits' => $totalOverdueAudits,
                'due_audits' => $totalDueAudits,
                'reaudits_performed' => $totalReaudits,
                'nc_actions_completed' => $totalNcCompleted,
                'nc_actions_pending' => $totalNcPending,
                'nc_actions_overdue' => $totalNcOverdue
            ],
            'oe' => [
                'total_audits' => $oeTotal,
                'latest_performed' => $oeLatest,
                'reaudits_performed' => $oeReaudits,
                'pending_audits' => $oePending,
                'due_audits' => $oeDue,
                'overdue_audits' => $oeOverdue,
                'completed_audits' => $oeCompleted,
                'last_audit_date' => $oeLastDate,
                'next_audit_date' => $oeNextDate,
                'frequency' => '1 Month (Monthly)',
                'total_ncs' => $oeTotalNc,
                'open_ncs' => $oeOpenNc,
                'working_ncs' => $oeWorkingNc,
                'cluster_review_ncs' => $oeClusterReviewNc,
                'auditor_review_ncs' => $oeAuditorReviewNc,
                'closed_ncs' => $oeClosedNc,
                'pending_actions' => $oePendingActions,
                'completed_actions' => $oeCompletedActions,
                'overdue_actions' => $oeOverdueActions
            ],
            'hse' => [
                'total_audits' => $hseTotal,
                'latest_performed' => $hseLatest,
                'reaudits_performed' => $hseReaudits,
                'pending_audits' => $hsePending,
                'due_audits' => $hseDue,
                'overdue_audits' => $hseOverdue,
                'completed_audits' => $hseCompleted,
                'last_audit_date' => $hseLastDate,
                'next_audit_date' => $hseNextDate,
                'frequency' => '3 Months (Quarterly)',
                'total_ncs' => $hseTotalNc,
                'open_ncs' => $hseOpenNc,
                'working_ncs' => $hseWorkingNc,
                'cluster_review_ncs' => $hseClusterReviewNc,
                'auditor_review_ncs' => $hseAuditorReviewNc,
                'closed_ncs' => $hseClosedNc,
                'pending_actions' => $hsePendingActions,
                'completed_actions' => $hseCompletedActions,
                'overdue_actions' => $hseOverdueActions
            ],
            'gemba' => [
                'total_audits' => $gembaTotal,
                'latest_performed' => $gembaLatest,
                'previous_audits' => $gembaReaudits,
                'pending_audits' => $gembaPending,
                'due_audits' => $gembaDue,
                'overdue_audits' => $gembaOverdue,
                'completed_audits' => $gembaCompleted,
                'last_audit_date' => $gembaLastDate,
                'next_audit_date' => $gembaNextDate,
                'frequency' => 'Walk / Event-Based',
                'total_ncs' => $gembaTotalNc,
                'open_ncs' => $gembaOpenNc,
                'working_ncs' => $gembaWorkingNc,
                'cluster_review_ncs' => $gembaClusterReviewNc,
                'auditor_review_ncs' => $gembaAuditorReviewNc,
                'closed_ncs' => $gembaClosedNc,
                'pending_actions' => $gembaPendingActions,
                'completed_actions' => $gembaCompletedActions,
                'overdue_actions' => $gembaOverdueActions
            ]
        ];
    }

    /**
     * Graph 1: Performed vs Pending Audits
     */
    private function getPerformedVsPendingChart(array $kpis): array
    {
        return [
            'categories' => ['OE Audits', 'HSE Audits', 'Gemba Audits'],
            'series' => [
                [
                    'name' => 'Audits Performed',
                    'data' => [
                        (int)$kpis['oe']['latest_performed'],
                        (int)$kpis['hse']['latest_performed'],
                        (int)$kpis['gemba']['latest_performed']
                    ]
                ],
                [
                    'name' => 'Pending Audits',
                    'data' => [
                        (int)$kpis['oe']['pending_audits'],
                        (int)$kpis['hse']['pending_audits'],
                        (int)$kpis['gemba']['pending_audits']
                    ]
                ],
                [
                    'name' => 'Overdue Audits',
                    'data' => [
                        (int)$kpis['oe']['overdue_audits'],
                        (int)$kpis['hse']['overdue_audits'],
                        (int)$kpis['gemba']['overdue_audits']
                    ]
                ]
            ]
        ];
    }

    /**
     * Graph 2: Audit Due and Overdue Status
     */
    private function getDueOverdueStatusChart(array $kpis): array
    {
        $completed = (int)$kpis['summary']['total_audits_performed'];
        $pending = (int)$kpis['summary']['pending_audits'];
        $due = (int)$kpis['summary']['due_audits'];
        $overdue = (int)$kpis['summary']['overdue_audits'];

        $upcoming = max(0, $pending - $due);

        return [
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
     * Graph 3: Monthly Audit Trend (Per Yearly Scope - 12 Months)
     */
    private function getMonthlyTrendChart($db, int $auditorId, string $auditorName, int $year, string $selectedModule): array
    {
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        $monthKeys = ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
        $monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $oeSeries = array_fill_keys($monthKeys, 0);
        $hseSeries = array_fill_keys($monthKeys, 0);
        $gembaSeries = array_fill_keys($monthKeys, 0);

        // 1. OE Monthly
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'oe') {
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

        // 2. HSE Monthly
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'hse') {
            $hseSql = "SELECT SUBSTRING(audit_date, 6, 2) as m, COUNT(*) as c
                       FROM alert_hse_audit_master
                       WHERE (LOWER(TRIM(auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(perform_audit_by)) IN ({$inAliases}))
                         AND SUBSTRING(audit_date, 1, 4) = '{$year}'
                       GROUP BY m";
            $hseRows = $db->query($hseSql)->getResultArray();
            foreach ($hseRows as $r) {
                if (isset($hseSeries[$r['m']])) {
                    $hseSeries[$r['m']] = (int)$r['c'];
                }
            }
        }

        // 3. Gemba Monthly (Walks)
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'gemba') {
            $gembaSql = "SELECT DATE_FORMAT(audit_report_date, '%m') as m, COUNT(DISTINCT site_name, audit_report_date) as c
                         FROM alert_gemba_audits
                         WHERE LOWER(TRIM(auditor_name)) IN ({$inAliases})
                           AND YEAR(audit_report_date) = {$year}
                         GROUP BY m";
            $gembaRows = $db->query($gembaSql)->getResultArray();
            foreach ($gembaRows as $r) {
                if (isset($gembaSeries[$r['m']])) {
                    $gembaSeries[$r['m']] = (int)$r['c'];
                }
            }
        }

        $series = [];
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'oe') {
            $series[] = ['name' => 'OE Audits (Monthly)', 'data' => array_values($oeSeries)];
        }
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'hse') {
            $series[] = ['name' => 'HSE Audits (Quarterly)', 'data' => array_values($hseSeries)];
        }
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'gemba') {
            $series[] = ['name' => 'Gemba Audits (Walks)', 'data' => array_values($gembaSeries)];
        }

        return [
            'year' => $year,
            'categories' => $monthLabels,
            'series' => $series
        ];
    }

    /**
     * Graph 4: Re-Audit Analysis
     */
    private function getReauditPerformanceChart(array $kpis): array
    {
        return [
            'categories' => ['OE Audits', 'HSE Audits'],
            'series' => [
                [
                    'name' => 'Latest Performed Audits',
                    'data' => [
                        (int)$kpis['oe']['latest_performed'],
                        (int)$kpis['hse']['latest_performed']
                    ]
                ],
                [
                    'name' => 'Previous Audits / Re-Audits',
                    'data' => [
                        (int)$kpis['oe']['reaudits_performed'],
                        (int)$kpis['hse']['reaudits_performed']
                    ]
                ]
            ],
            'totals' => [
                'latest_total' => (int)$kpis['oe']['latest_performed'] + (int)$kpis['hse']['latest_performed'],
                'reaudit_total' => (int)$kpis['oe']['reaudits_performed'] + (int)$kpis['hse']['reaudits_performed']
            ]
        ];
    }

    /**
     * Dedicated NC Analytics (Status Distribution & Action Progress)
     */
    private function getNcAnalytics(array $kpis): array
    {
        return [
            'distribution' => [
                'categories' => ['OE Module', 'HSE Module', 'Gemba Module'],
                'series' => [
                    [
                        'name' => 'Open',
                        'data' => [
                            (int)$kpis['oe']['open_ncs'],
                            (int)$kpis['hse']['open_ncs'],
                            (int)$kpis['gemba']['open_ncs']
                        ]
                    ],
                    [
                        'name' => 'Working',
                        'data' => [
                            (int)$kpis['oe']['working_ncs'],
                            (int)$kpis['hse']['working_ncs'],
                            (int)$kpis['gemba']['working_ncs']
                        ]
                    ],
                    [
                        'name' => 'Cluster Review',
                        'data' => [
                            (int)$kpis['oe']['cluster_review_ncs'],
                            (int)$kpis['hse']['cluster_review_ncs'],
                            (int)$kpis['gemba']['cluster_review_ncs']
                        ]
                    ],
                    [
                        'name' => 'Auditor Review',
                        'data' => [
                            (int)$kpis['oe']['auditor_review_ncs'],
                            (int)$kpis['hse']['auditor_review_ncs'],
                            (int)$kpis['gemba']['auditor_review_ncs']
                        ]
                    ],
                    [
                        'name' => 'Closed',
                        'data' => [
                            (int)$kpis['oe']['closed_ncs'],
                            (int)$kpis['hse']['closed_ncs'],
                            (int)$kpis['gemba']['closed_ncs']
                        ]
                    ]
                ]
            ],
            'action_progress' => [
                'labels' => ['Completed Actions', 'Pending Review Actions', 'Overdue Actions'],
                'series' => [
                    (int)$kpis['summary']['nc_actions_completed'],
                    (int)$kpis['summary']['nc_actions_pending'],
                    (int)$kpis['summary']['nc_actions_overdue']
                ]
            ]
        ];
    }

    /**
     * Section 6: Audit Schedule and Report Table
     */
    private function getAuditScheduleTable($db, int $auditorId, string $auditorName, string $selectedModule, string $selectedStatus, int $year): array
    {
        $today = date('Y-m-d');
        $aliases = $this->getAuditorAliases($db, $auditorId, $auditorName);
        $escapedAliases = array_map(function($a) use ($db) { return $db->escape($a); }, $aliases);
        $inAliases = implode(', ', $escapedAliases);

        $table = [];

        // 1. OE Sites Schedule
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'oe') {
            $oeSql = "SELECT 
                'OE' as module,
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
            INNER JOIN (
                SELECT location, MAX(audit_date) as max_date
                FROM alert_final_structured_audit
                WHERE (audit_by_user_id = {$auditorId} OR LOWER(TRIM(auditor_name)) IN ({$inAliases}))
                GROUP BY location
            ) latest ON latest.location = a.location AND latest.max_date = a.audit_date
            WHERE (a.audit_by_user_id = {$auditorId} OR LOWER(TRIM(auditor_name)) IN ({$inAliases}))
            GROUP BY a.location
            ORDER BY next_audit_date ASC";

            $oeRows = $db->query($oeSql)->getResultArray();
            foreach ($oeRows as $r) {
                if ($selectedStatus !== 'all' && strtolower($selectedStatus) !== strtolower($r['audit_status'])) {
                    continue;
                }
                $table[] = [
                    'module' => 'OE',
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

        // 2. HSE Sites Schedule
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'hse') {
            $hseSql = "SELECT 
                'HSE' as module,
                m.client_name,
                m.location as site_name,
                m.hse_audit_id as audit_id,
                m.audit_no,
                m.audit_date as latest_audit_date,
                m.report_date,
                IF(m.report_date != '0000-00-00' AND m.report_date IS NOT NULL, m.report_date, DATE_ADD(m.audit_date, INTERVAL 3 MONTH)) as next_audit_date,
                '3 Months (Quarterly)' as audit_frequency,
                IF(m.hse_audit_id = (SELECT MAX(h2.hse_audit_id) FROM alert_hse_audit_master h2 WHERE h2.audit_no = m.audit_no), 'Performed Audit', 'Re-Audit') as audit_type,
                IF(m.report_date < '{$today}' AND m.report_date != '0000-00-00', 'Overdue', IF(m.report_date <= DATE_ADD('{$today}', INTERVAL 15 DAY), 'Due', 'Upcoming')) as audit_status,
                (SELECT COUNT(*) FROM alert_hse_audit_details d WHERE d.hse_audit_id = m.hse_audit_id AND d.finding = 'NO') as total_ncs,
                (SELECT COUNT(*) FROM alert_hse_audit_details d WHERE d.hse_audit_id = m.hse_audit_id AND d.finding = 'NO' AND d.nc_status IN (0, 1, 5, 2)) as pending_nc_actions
            FROM alert_hse_audit_master m
            INNER JOIN (
                SELECT location, MAX(hse_audit_id) as max_id
                FROM alert_hse_audit_master
                WHERE (LOWER(TRIM(auditor_name)) IN ({$inAliases}) OR LOWER(TRIM(perform_audit_by)) IN ({$inAliases}))
                GROUP BY location
            ) latest ON latest.max_id = m.hse_audit_id
            ORDER BY next_audit_date ASC";

            $hseRows = $db->query($hseSql)->getResultArray();
            foreach ($hseRows as $r) {
                if ($selectedStatus !== 'all' && strtolower($selectedStatus) !== strtolower($r['audit_status'])) {
                    continue;
                }
                $table[] = [
                    'module' => 'HSE',
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
                    'action_url' => base_url('Masters/Hse_nc_tracker'),
                    'action_text' => 'View Details'
                ];
            }
        }

        // 3. Gemba Sites Schedule
        if ($selectedModule === 'all' || strtolower($selectedModule) === 'gemba') {
            $gembaSql = "SELECT 
                'Gemba' as module,
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
            WHERE LOWER(TRIM(g.auditor_name)) IN ({$inAliases})
            GROUP BY g.site_name
            ORDER BY latest_audit_date DESC";

            $gembaRows = $db->query($gembaSql)->getResultArray();
            foreach ($gembaRows as $r) {
                if ($selectedStatus !== 'all' && strtolower($selectedStatus) !== strtolower($r['audit_status'])) {
                    continue;
                }
                $table[] = [
                    'module' => 'Gemba',
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
                    'action_text' => 'Open Points'
                ];
            }
        }

        return $table;
    }
}
