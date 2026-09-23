<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

// changes on 1/10/25 by darsh - heirarchical RBAC
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 1. Check if user logged in
        if (!$session->has('role')) {
            return redirect()->to(base_url("Login"));
        }

        // 1.5. Check if user is deactivated
        $user_id = $session->get('login_id') ?? $session->get('user_id');
        if ($user_id && $user_id !== '0') {
            $db = \Config\Database::connect();
            $user = $db->table('alert_users')->where('user_id', $user_id)->get()->getRowArray();
            if ($user && ($user['status'] != 1 && $user['status'] !== '1')) {
                if (session_id()) {
                    $db->table('alert_login_history')
                       ->where('session_id', session_id())
                       ->update(['logout_time' => date('Y-m-d H:i:s')]);
                }
                $session->destroy();
                return redirect()->to(base_url("Login"));
            }
        }

        // Track last activity for online users dashboard
        if (session_id()) {
            $db = \Config\Database::connect();
            $db->table('alert_login_history')
               ->where('session_id', session_id())
               ->update(['last_activity' => date('Y-m-d H:i:s')]);
        }

        $role = strtolower(trim($session->get('role') ?? ''));

        $segments = $request->uri->getSegments();
        $prefixes = ['Masters', 'Admin', 'Customer', 'Seller', 'Reports', 'Api', 'Demo'];
        $offset = 0;
        $seg0 = $segments[0] ?? '';
        $seg1 = $segments[1] ?? '';
        if (!in_array($seg0, $prefixes, true) && in_array($seg1, $prefixes, true)) {
            $offset = 1;
        }
        $seg0 = $segments[$offset] ?? '';
        $seg1 = $segments[$offset + 1] ?? '';
        $seg2 = $segments[$offset + 2] ?? '';
        $hasPrefix = in_array($seg0, $prefixes, true);
        $controller = $hasPrefix ? $seg1 : $seg0;
        $action = $hasPrefix ? $seg2 : $seg1;
        if (empty($action)) {
            $action = 'index';
        }

        if (($_SESSION['admin_flag'] ?? 0) == 1) {
            return null; // Admin flag bypasses all role restrictions
        }

        // 2. If route requires role(s), check permissions
        if ($arguments) {
            $normalized_role_under = str_replace(' ', '_', $role);
            $normalized_role_space = str_replace('_', ' ', $role);
            if (!in_array($role, $arguments) && !in_array($normalized_role_under, $arguments) && !in_array($normalized_role_space, $arguments)) {
                return redirect()->to(base_url("NoAccess"));
            }
        }

        // 3. Higher Authority restriction -> only allow Read-Only modules and trackers
        if ($role === "higher authority" || $role === "higher_authority") {
            $allowedModules = [
                'oe_nc_tracker',
                'hse_nc_tracker',
                'gembanctracker',
                'normal_nc_tracker',
                'client_nc_tracker',
                'fm_nc_tracker',
                'inplant_nc_tracker',
                'noaccess',
                'login',
                'gemba_dashboard',
                'audit_dashboard',
                'audit_dashboard_hse',
                'oe_dashboard',
                'dashboard',
                'gemba_ajax',
                'softwaredocumentation',
                'softwaredefinitions',
                'audit_template',
                'audit_final_structure',
                'hse_audit',
                'reaudit',
                'sixs_audit',
                'onlineusers',
                'online_users',
                'OnlineUsers'
            ];

            if ($hasPrefix && strtolower($seg0) === 'masters' && !in_array(strtolower($controller), $allowedModules, true)) {
                 $session->setFlashdata('access_denied_message', 'Higher Authority can only access read-only modules.');
                 return redirect()->to(base_url("NoAccess"));
            }
        }

        // 4. Cluster Manager restriction → only allow NC Tracker module and Gemba modules
        if ($role === "cluster_manager" || $role === "cluster manager") {
            // Allowed module (adjust controller/route name as per your project)
            $allowedModules = [
                'Oe_nc_tracker',
                'Hse_nc_tracker',
                'client_nc_tracker',
                'Fm_nc_tracker',
                'Inplant_nc_tracker',
                'NoAccess',
                'Login',
                'GembaAudit',
                'GembaSites',
                'gemba-audit',
                'gemba-sites',
                'Gemba_Dashboard',
                'GembaNcTracker',
                'Gemba_Ajax',
                'Audit_dashboard',
                'Audit_Dashboard_HSE',
                'Oe_dashboard',
                'Dashboard',
                'Cluster manager',
                'Cluster_manager',
                'Normal_nc_tracker',
                'Audit_final_structure',
                'Hse_audit',
                'Reaudit',
                'SoftwareDocumentation',
                'softwaredocumentation',
                'software-documentation',
                'SoftwareDefinitions',
                'softwaredefinitions',
                'software-definitions',
                'OnlineUsers',
                'onlineusers',
                'online_users'
            ];

            if (!in_array($controller, $allowedModules, true)) {
                $session->setFlashdata('access_denied_message', 'Cluster Managers can only access NC Tracker and Gemba modules. Access to other modules is restricted.');
                return redirect()->to(base_url("NoAccess"));
            }
        }

        // 5. WH Manager is read-only (you can later enforce POST/PUT/DELETE blocking here)
        // Note: Supports both "WH Manager" and legacy "Engineer" designation
        //if (($role === "WH Manager" || $role === "Engineer") && in_array($request->getMethod(), ['post','put','delete'])) {
        //    return redirect()->to(base_url("NoAccess"));
        //}
        if (($role === "wh manager" || $role === "engineer") && in_array($request->getMethod(), ['post', 'put', 'delete'])) {
            return redirect()->to(base_url("NoAccess"));
        }
        // 6. Account Manager is Read-Only generally, with write access ONLY for specific actions
        // Updated: Allow AJAX dropdown APIs for OE Dashboard (get_regions_by_year, get_clusters_by_region, get_locations_by_cluster)
        if (($role === "account manager" || $role === "account_manager") && in_array($request->getMethod(), ['post', 'put', 'delete'])) {
            // Allowed Write Actions
            $allowedWrite = false;

            // Allow Login/Logout related
            if (in_array($controller, ['Login', 'login'])) {
                $allowedWrite = true;
            }
            // Allow NC Tracker actions (OE, HSE, Gemba & Normal)
            elseif (
                in_array($controller, ['Oe_nc_tracker', 'Hse_nc_tracker', 'GembaNcTracker', 'Normal_nc_tracker']) &&
                in_array($action, [
                    'update_status',
                    'update_nc_status',
                    'save_details',
                    'get_form_data',
                    'get_cluster',
                    'get_location',
                    'get_filter_data',
                    'get_dependent_dropdowns'
                ], true)
            ) {
                $allowedWrite = true;
            }
            // Allow Dashboard filter AJAX actions (OE, HSE, Gemba)
            elseif (
                in_array($controller, ['Audit_dashboard', 'Audit_Dashboard_HSE', 'Oe_dashboard', 'Gemba_Dashboard']) &&
                in_array($action, [
                    'index',
                    'HSE_filter',
                    'OE_filter',
                    'OE_Audit',
                    'get_regions_by_year',
                    'get_clusters_by_region',
                    'get_locations_by_cluster',
                    'get_data',
                    'get_aging_data',
                    'get_dependent_dropdowns',
                    'get_normal_audit_filters',
                    'get_normal_dashboard_dependent_filters'
                ], true)
            ) {
                $allowedWrite = true;
            }
            // Allow Gemba Audit and Gemba Sites write actions (create, edit, delete, import)
            elseif (
                in_array($controller, ['GembaAudit', 'gemba-audit', 'GembaSites', 'gemba-sites']) &&
                in_array($action, ['create', 'edit', 'delete', 'import'], true)
            ) {
                $allowedWrite = true;
            }
            // Allow basic ajax table fetches and exports if they use POST
            elseif (strpos($action, 'ajax') !== false || strpos(strtolower($action), 'export') !== false) {
                $allowedWrite = true;
            }

            if (!$allowedWrite) {
                return redirect()->to(base_url("NoAccess"));
            }
        }

        // 7. Account Manager — allow GET access to OE Dashboard and its AJAX dropdown APIs
        // Without this, Account Manager would hit NoAccess on GET requests to Oe_dashboard
        if (($role === "account manager" || $role === "account_manager") && $request->getMethod() === 'get') {
            // All GET requests are allowed for Account Manager (data is filtered at query level)
            return null;
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing for now
    }
}
