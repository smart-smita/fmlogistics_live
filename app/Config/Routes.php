<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

// changes on 1/10/25 by darsh: added filter to the routes
$routes->get('admin/dashboard', 'Admin::dashboard', ['filter' => 'authfilter:super_admin,admin']);
$routes->get('Cluster manager/dashboard', 'Cluster manager::dashboard', ['filter' => 'authfilter:cluster_manager,admin,higher_authority,super_admin']);
$routes->get('wh_manager/reports', 'WHManager::viewReports', ['filter' => 'authfilter:wh_manager,cluster_manager,admin,higher_authority,super_admin']);
$routes->get('Masters/Audit_final_structure/reaudit/(:num)/(:num)', 'Masters\Audit_final_structure::reaudit/$1/$2');

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// changes on 1/10/25 by darsh: added filter to the routes
// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Login');
$routes->get('/ci/', 'Login');

// Profile routes
$routes->get('/profile', 'Profile::index');
$routes->post('/profile/update-password', 'Profile::updatePassword');
//$routes->get('/staff_role', 'Masters\Staff_role::index');
$routes->post('Masters/Client_nc_tracker/update_nc_status', 'Masters\Client_nc_tracker::update_nc_status');
// NEW CHANGES: OE tracker status update endpoint
$routes->post('Masters/Oe_nc_tracker/update_status', 'Masters\Oe_nc_tracker::update_status');
// NEW CHANGES: Inplant tracker status update endpoint
$routes->post('Masters/Inplant_nc_tracker/update_nc_status', 'Masters\Inplant_nc_tracker::update_nc_status');
// NEW CHANGES: FM tracker status update endpoint
$routes->post('Masters/Fm_nc_tracker/update_nc_status', 'Masters\Fm_nc_tracker::update_nc_status');

// Location Hierarchy AJAX endpoints
$routes->post('Masters/LocationHierarchy/getRegionsByCountry', 'Masters\LocationHierarchy::getRegionsByCountry');
$routes->post('Masters/LocationHierarchy/getClustersByRegion', 'Masters\LocationHierarchy::getClustersByRegion');
$routes->post('Masters/LocationHierarchy/getLocationsByCluster', 'Masters\LocationHierarchy::getLocationsByCluster');
$routes->post('Masters/LocationHierarchy/validateHierarchy', 'Masters\LocationHierarchy::validateHierarchy');
$routes->get('Masters/LocationHierarchy/getUserHierarchy/(:num)', 'Masters\LocationHierarchy::getUserHierarchy/$1');

// Admin Migration Routes (admin only)
$routes->get('Admin/DataMigration', 'Admin\DataMigration::index');
$routes->post('Admin/DataMigration/runMigration', 'Admin\DataMigration::runMigration');

// Admin ACL Test Routes (admin only)
$routes->get('Admin/ACLTest', 'Admin\ACLTest::index');
$routes->get('Admin/ACLTest/testUserAccess/(:num)', 'Admin\ACLTest::testUserAccess/$1');

// Structure Audit ACL Test Routes (admin only)
$routes->get('Admin/StructureAuditACLTest', 'Admin\StructureAuditACLTest::index');
$routes->get('Admin/StructureAuditACLTest/testUserStructureAccess/(:num)', 'Admin\StructureAuditACLTest::testUserStructureAccess/$1');
$routes->get('Admin/StructureAuditACLTest/simulateClusterManagerAccess', 'Admin\StructureAuditACLTest::simulateClusterManagerAccess');

// Debug Routes (temporary)
$routes->get('Debug/ACLDebug', 'Debug\ACLDebug::index');
$routes->get('Debug/ACLDebug/testClientNCTracker', 'Debug\ACLDebug::testClientNCTracker');

// Structure Audit Routes with ACL filtering
$routes->group('Masters', ['filter' => 'structure_audit_acl'], function ($routes) {
    $routes->match(['get', 'post'], 'Audit_template/save_details/(:any)', 'Masters\Audit_template::save_details/$1');
    $routes->match(['get', 'post'], 'Audit_template/importExcel', 'Masters\Audit_template::importExcel');
    $routes->match(['get', 'post'], 'Audit_template/importHSE_Excel', 'Masters\Audit_template::importHSE_Excel');
    $routes->match(['get', 'post'], 'Audit_template/importNormal_Excel', 'Masters\Audit_template::importNormal_Excel');
    $routes->match(['get', 'post'], 'Audit_final_structure/save_details/(:any)', 'Masters\Audit_final_structure::save_details/$1');
    $routes->match(['get', 'post'], 'Audit_final_structure/reaudit/(:any)/(:any)', 'Masters\Audit_final_structure::reaudit/$1/$2');
    $routes->post('Hse_audit/get_categories_by_region', 'Masters\Hse_audit::get_categories_by_region');
    $routes->post('Hse_audit/get_subcategories_by_region_and_category', 'Masters\Hse_audit::get_subcategories_by_region_and_category');
    $routes->post('Hse_audit/get_clients_by_region_category_subcategory', 'Masters\Hse_audit::get_clients_by_region_category_subcategory');
    $routes->post('Hse_audit/get_audit_questions_ajax', 'Masters\Hse_audit::get_audit_questions_ajax');
});

// Admin only
$routes->group('admin', ['filter' => 'authfilter:super_admin,admin'], function ($routes) {
    $routes->get('dashboard', 'Admin::dashboard');
    $routes->get('users', 'Admin::manageUsers');
    
    // Email Settings
    $routes->get('settings/email-settings', 'Admin\EmailSettings::index');
    $routes->post('settings/email-settings/save', 'Admin\EmailSettings::save');
    $routes->post('settings/email-settings/test', 'Admin\EmailSettings::testEmail');

    // Cron Settings Routes
    $routes->get('settings/cron-settings', 'Admin\CronSettings::index');
    $routes->post('settings/cron-settings/toggle', 'Admin\CronSettings::toggleStatus');
});

// Higher Authority
$routes->group('higher', ['filter' => 'authfilter:super_admin,admin,higher_authority'], function ($routes) {
    $routes->get('dashboard', 'HigherAuthority::dashboard');
});

// NC Tracker (cluster manager only + higher roles)
$routes->group('nc-tracker', ['filter' => 'authfilter:super_admin,admin,higher_authority,cluster_manager'], function ($routes) {
    $routes->get('/', 'NcTracker::index');
    $routes->get('view/(:num)', 'NcTracker::view/$1');
    $routes->post('update/(:num)', 'NcTracker::update/$1');
});


// WH Manager (read-only)
$routes->group('wh_manager', ['filter' => 'authfilter:super_admin,admin,higher_authority,cluster_manager,wh_manager'], function ($routes) {
    $routes->get('dashboard', 'WHManager::dashboard');
    $routes->get('reports', 'WHManager::viewReports');
});

// Gemba Audit routes
$routes->group('gemba-audit', ['namespace' => 'App\Controllers\Masters', 'filter' => 'authfilter:admin,super_admin,auditor,higher_authority,highter_authority,cluster_manager,account_manager'], static function ($routes) {
    $routes->get('/', 'GembaAudit::index');
    $routes->get('table_ajax', 'GembaAudit::table_ajax');
    $routes->match(['get', 'post'], 'create', 'GembaAudit::create');
    $routes->match(['get', 'post'], 'edit/(:num)', 'GembaAudit::edit/$1');
    $routes->get('view/(:num)', 'GembaAudit::view/$1');
    $routes->get('delete/(:num)', 'GembaAudit::delete/$1');
    $routes->get('cascading-dropdown', 'GembaAudit::cascadingDropdown');
    $routes->get('getSiteDetails', 'GembaAudit::getSiteDetails');
    $routes->get('gemba_export', 'GembaAudit::gemba_export');
    $routes->match(['get', 'post'], 'import', 'GembaAudit::import');
    $routes->get('download_error_report', 'GembaAudit::downloadErrorReport');
    $routes->get('download_sample', 'GembaAudit::downloadSample');
});

// Gemba Sites Master routes (Admin and Auditor)
$routes->group('gemba-sites', ['namespace' => 'App\Controllers\Masters', 'filter' => 'authfilter:admin,super_admin,auditor,higher_authority,highter_authority,cluster_manager,account_manager'], static function ($routes) {
    $routes->get('/', 'GembaSites::index');
    $routes->get('table_ajax', 'GembaSites::table_ajax');
    $routes->match(['get', 'post'], 'create', 'GembaSites::create');
    $routes->match(['get', 'post'], 'edit/(:num)', 'GembaSites::edit/$1');
    $routes->get('delete/(:num)', 'GembaSites::delete/$1');
});

// Gemba Dashboard
$routes->group('gemba-dashboard', ['namespace' => 'App\Controllers\Customer', 'filter' => 'authfilter:admin,super_admin,auditor,higher_authority,highter_authority,cluster_manager,account_manager'], static function ($routes) {
    $routes->get('/', 'Gemba_Dashboard::index');
    $routes->match(['get', 'post'], 'get_data', 'Gemba_Dashboard::get_data');
    $routes->match(['get', 'post'], 'get_aging_data', 'Gemba_Dashboard::get_aging_data');
    $routes->get('export_gemba', 'Gemba_Dashboard::export_gemba');
    $routes->get('export_aging', 'Gemba_Dashboard::export_aging');
});

// Software Documentation Routes
$routes->group('software-documentation', ['namespace' => 'App\Controllers\Masters', 'filter' => 'authfilter:admin,super_admin,auditor,higher_authority,cluster_manager,account_manager'], static function ($routes) {
    $routes->get('/', 'SoftwareDocumentation::index');
    $routes->get('create', 'SoftwareDocumentation::create');
    $routes->post('store', 'SoftwareDocumentation::store');
    $routes->get('edit/(:num)', 'SoftwareDocumentation::edit/$1');
    $routes->post('update/(:num)', 'SoftwareDocumentation::update/$1');
    $routes->get('view/(:num)', 'SoftwareDocumentation::view/$1');
    $routes->get('download/(:num)', 'SoftwareDocumentation::download/$1');
    $routes->get('download/(:num)/(:num)', 'SoftwareDocumentation::download/$1/$2');
    $routes->get('delete/(:num)', 'SoftwareDocumentation::delete/$1');
    $routes->get('restore/(:num)', 'SoftwareDocumentation::restore/$1');
});

// Software Definitions Routes (Knowledge Base)
$routes->group('software-definitions', ['namespace' => 'App\Controllers\Masters', 'filter' => 'authfilter:admin,super_admin,auditor,higher_authority,cluster_manager,account_manager'], static function ($routes) {
    $routes->get('/', 'SoftwareDefinitions::index');
    $routes->get('create', 'SoftwareDefinitions::create');
    $routes->post('store', 'SoftwareDefinitions::store');
    $routes->get('edit/(:num)', 'SoftwareDefinitions::edit/$1');
    $routes->post('update/(:num)', 'SoftwareDefinitions::update/$1');
    $routes->get('view/(:num)', 'SoftwareDefinitions::view/$1');
    $routes->get('delete/(:num)', 'SoftwareDefinitions::delete/$1');
    $routes->get('export/csv', 'SoftwareDefinitions::exportCsv');
    $routes->get('print', 'SoftwareDefinitions::printView');
});

// Reports - RT-WeeklyML (Weekly Gemba Update)
$routes->group('Reports', ['namespace' => 'App\Controllers\Reports', 'filter' => 'authfilter:admin,super_admin,auditor,higher_authority,highter_authority'], static function ($routes) {
    $routes->get('RtWeeklyMl', 'RtWeeklyMl::index');
    $routes->match(['get', 'post'], 'RtWeeklyMl/get_report_data', 'RtWeeklyMl::get_report_data');
    $routes->get('RtWeeklyMl/export_excel', 'RtWeeklyMl::export_excel');
    $routes->get('RtWeeklyMl/export_csv', 'RtWeeklyMl::export_csv');
    $routes->post('RtWeeklyMl/send_report_email', 'RtWeeklyMl::send_report_email');
});

if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
