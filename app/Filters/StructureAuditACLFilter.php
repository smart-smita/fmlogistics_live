<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Structure Audit ACL Filter
 * Restricts Cluster Managers to read-only access for structure audits.
 * Also allows Account Manager and Cluster Manager read-only AJAX access
 * to OE Dashboard dropdown filter APIs.
 */
class StructureAuditACLFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
{
    $role = strtolower(trim($_SESSION['role'] ?? ''));

    if (($_SESSION['admin_flag'] ?? 0) == 1) {
        return null; // Admin flag bypasses all structure audit restrictions
    }

    $uri      = $request->getUri();
    $segments = $uri->getSegments();
    $method   = $request->getMethod();

    // Resolve segments — handle optional base-path prefix
    // URL pattern: Customer/Audit_dashboard/get_regions_by_year
    // segments[0] = 'Customer', [1] = 'Audit_dashboard', [2] = method
    $prefixes = ['Masters', 'Admin', 'Customer', 'Seller', 'Reports', 'Api', 'Demo'];
    $offset = 0;
    if (!in_array($segments[0] ?? '', $prefixes, true) && in_array($segments[1] ?? '', $prefixes, true)) {
        $offset = 1;
    }
    $controller = $segments[$offset]      ?? '';
    $action     = $segments[$offset + 1]  ?? '';
    $function   = $segments[$offset + 2]  ?? '';

    /*
    |--------------------------------------------------------------------------
    | ALLOW AJAX DROPDOWN METHODS for Account Manager AND Cluster Manager
    | These are read-only filter APIs — always permitted for both roles.
    |--------------------------------------------------------------------------
    */
    $allowedDropdownMethods = [
        'get_regions_by_year',
        'get_clusters_by_region',
        'get_locations_by_cluster',
    ];

    if (
        in_array($controller, ['Customer', 'Oe_dashboard'], true) &&
        in_array($action, array_merge(['Audit_dashboard'], $allowedDropdownMethods), true) &&
        (
            in_array($function, $allowedDropdownMethods, true) ||
            in_array($action, $allowedDropdownMethods, true)
        )
    ) {
        return null;
    }

    // Also allow direct access to Oe_dashboard index for Account Manager
    if ($controller === 'Oe_dashboard' && in_array($action, ['index', ''], true)) {
        return null;
    }

    // Only apply structure-audit write restrictions to Cluster Managers
    // Account Managers are handled by AuthFilter; skip them here.
    if (!in_array($role, ['cluster manager', 'cluster_manager'], true)) {
        return null;
    }

    // Structure Audit related restrictions
    $restrictedControllers = [

        'Masters' => [

            'Audit_template' => [
                'restricted_methods' => [
                    'save_details',
                    'delete',
                    'importExcel',
                    'importHSE_Excel',
                    'importNormal_Excel'
                ],
                'read_only_methods' => [
                    'index',
                    'get_form_data',
                    'table_ajax',
                    'view'
                ]
            ],

            'Audit_final_structure' => [
                'restricted_methods' => [
                    'save_details',
                    'delete',
                    'create',
                    'update'
                ],
                'read_only_methods' => [
                    'index',
                    'get_form_data',
                    'table_ajax',
                    'view'
                ]
            ]
        ],

        'Audit' => [

            'perform_audit_view' => [
                'restricted_methods' => [
                    'save_audit',
                    'submit_audit',
                    'create'
                ],
                'read_only_methods' => [
                    'index',
                    'view'
                ]
            ]
        ]
    ];

    // Check if current request is restricted
    if (isset($restrictedControllers[$controller])) {

        foreach ($restrictedControllers[$controller] as $subController => $restrictions) {

            if (
                strpos($action, $subController) !== false ||
                $action === $subController
            ) {

                if (
                    in_array($function, $restrictions['restricted_methods']) ||
                    (
                        $method === 'POST' &&
                        !in_array($function, $restrictions['read_only_methods'])
                    )
                ) {

                    log_message(
                        'warning',
                        "Cluster manager {$_SESSION['login_id']} attempted restricted action: {$controller}/{$action}/{$function}"
                    );

                    if ($request->isAJAX()) {

                        return service('response')
                            ->setJSON([
                                'status'  => 'error',
                                'message' => 'Access denied.'
                            ])
                            ->setStatusCode(403);
                    }

                    return redirect()
                        ->to(base_url('Customer/Audit_dashboard/OE_Audit'))
                        ->with(
                            'error',
                            'Access denied. You have read-only access.'
                        );
                }
            }
        }
    }

    return null;
}

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing to do here
    }
}