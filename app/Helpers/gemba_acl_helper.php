<?php

/**
 * Gemba Module Access Control Helper
 * 
 * Provides centralized Role-Based Access Control logic for the Gemba Module.
 * Defines which roles have read-only access vs full write access.
 */

if (!function_exists('gemba_can_write')) {
    /**
     * Checks if the currently logged-in user has write privileges for the Gemba module.
     * Write roles: Super Admin, Admin, Auditor, Higher Authority.
     * Read-only roles: Cluster Manager, Account Manager.
     *
     * @return bool True if user can create/edit/delete/approve, False if read-only.
     */
    function gemba_can_write()
    {
        if ((session()->get('admin_flag') ?? 0) == 1) {
            return true;
        }

        helper('designation_acl');
        $role = strtolower(trim(session()->get('role') ?? ''));
        $designation = getUserDesignation();

        // Define roles that are allowed to perform write/modification actions
        $write_roles = [
            'super_admin',
            'super admin',
            'admin',
            'auditor',
            'cluster manager',
            'account manager',
            'wh manager'
        ];

        return in_array($role, $write_roles, true) || in_array($designation, $write_roles, true);
    }
}

if (!function_exists('gemba_require_write_access')) {
    /**
     * Checks write access and automatically aborts or redirects if unauthorized.
     * Useful for protecting backend controller methods.
     *
     * @param bool $is_ajax Set to true if the request is AJAX to return a JSON response instead of redirecting.
     * @return \CodeIgniter\HTTP\Response|void
     */
    function gemba_require_write_access($is_ajax = false)
    {
        if (!gemba_can_write()) {
            if ($is_ajax) {
                // If it's an AJAX request, send a JSON error
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => false,
                    'message' => 'You have read-only access. This action is not permitted.'
                ]);
                exit;
            } else {
                // For normal HTTP requests, set flashdata and redirect
                $session = \Config\Services::session();
                $session->setFlashdata('error', 'You have read-only access. This action is not permitted.');

                // Redirect back to dashboard or previous page
                header('Location: ' . base_url('Customer/Gemba_Dashboard'));
                exit;
            }
        }
    }
}

if (!function_exists('apply_gemba_role_filters')) {
    /**
     * Applies role-based filtering to a Query Builder instance for Gemba module tables.
     * 
     * @param \CodeIgniter\Database\BaseBuilder $builder The Query Builder instance
     * @param string $tableName The table being queried ('alert_gemba_audits' or 'alert_gemba_sites')
     * @return \CodeIgniter\Database\BaseBuilder
     */
    function apply_gemba_role_filters($builder, $tableName = 'alert_gemba_audits')
    {
        helper('designation_acl');
        $acl = getUserACL();
        if ($acl['can_view_all']) {
            return $builder;
        }
        if ($acl['is_restricted']) {
            $sites = getUserAllocatedSiteNames('GEMBA');
            if (!empty($sites)) {
                $col = ($tableName === 'alert_gemba_audits' || $tableName === 'alert_gemba_sites') ? "{$tableName}.site_name" : "{$tableName}.client_name";
                $builder->whereIn($col, $sites);
            } else {
                $builder->where('1=0');
            }
        }
        return $builder;
    }
}

if (!function_exists('apply_gemba_latest_filter')) {
    /**
     * Excludes historical re-audit records from HSE audits so that only the latest NCs are displayed.
     * Ensures consistency between Gemba Dashboard, Gemba Audit lists, and Gemba NC Tracker.
     * 
     * @param \CodeIgniter\Database\BaseBuilder $builder
     * @param string $tableName
     * @return \CodeIgniter\Database\BaseBuilder
     */
    function apply_gemba_latest_filter($builder, $tableName = 'alert_gemba_audits')
    {
        if ($tableName === 'alert_gemba_audits') {
            $builder->groupStart()
                ->where("{$tableName}.source_module !=", "hse_audit")
                ->orGroupStart()
                    ->where("{$tableName}.source_module", "hse_audit")
                    ->where("{$tableName}.hse_audit_id IN (SELECT MAX(hse_audit_id) FROM alert_hse_audit_master GROUP BY audit_no)")
                ->groupEnd()
            ->groupEnd();
        }
        return $builder;
    }
}
