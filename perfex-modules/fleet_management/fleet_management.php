<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Fleet Management
Description: Vehicle fleet management for rental companies (with or without driver): vehicles, maintenance, insurance & document reminders, driver assignment and rental billing through native Perfex invoices.
Version: 1.0.0
Requires at least: 2.3.*
Author: PERSO
*/

define('FLEET_MANAGEMENT_MODULE', 'fleet_management');

// Bump this whenever the database schema changes so the auto-migration below
// recreates any missing table/column without a manual deactivate/reactivate.
define('FLEET_MANAGEMENT_DB_VERSION', '1.0.21');

$CI = &get_instance();

register_activation_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_activation_hook');
register_deactivation_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_deactivation_hook');
register_uninstall_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_uninstall_hook');

register_language_files(FLEET_MANAGEMENT_MODULE, [FLEET_MANAGEMENT_MODULE]);

// Load the module helper deterministically so its functions are always defined
// in controllers and views (a deferred loader call can silently fail at bootstrap).
require_once __DIR__ . '/helpers/fleet_management_helper.php';

function fleet_management_activation_hook()
{
    require __DIR__ . '/install.php';
}

/**
 * Self-healing schema: run the idempotent installer whenever the stored schema
 * version is behind the code, so new tables/columns appear without forcing a
 * manual module reactivation.
 */
hooks()->add_action('admin_init', 'fleet_management_maybe_upgrade_schema', 1);

function fleet_management_maybe_upgrade_schema()
{
    if (get_option('fleet_management_db_version') != FLEET_MANAGEMENT_DB_VERSION) {
        require __DIR__ . '/install.php';
    }
}

function fleet_management_deactivation_hook()
{
    // Keep data on deactivation; only the menu/permissions disappear.
}

function fleet_management_uninstall_hook()
{
    require_once __DIR__ . '/uninstall.php';
}

/**
 * Register module permissions (Perfex >= 2.3 capabilities API).
 */
hooks()->add_action('admin_init', 'fleet_management_permissions');

function fleet_management_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    register_staff_capabilities('fleet', $capabilities, _l('fleet_management'));
}

/**
 * Build the sidebar menu.
 */
hooks()->add_action('admin_init', 'fleet_management_init_menu_items');

function fleet_management_init_menu_items()
{
    $CI = &get_instance();

    if (!staff_can('view', 'fleet')) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('fleet-management', [
        'name'     => _l('fleet_management'),
        'icon'     => 'fa fa-car',
        'position' => 30,
        'href'     => admin_url('fleet_management/dashboard'),
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-dashboard',
        'name'     => _l('fleet_dashboard'),
        'href'     => admin_url('fleet_management/dashboard'),
        'position' => 0,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-vehicles',
        'name'     => _l('fleet_vehicles'),
        'href'     => admin_url('fleet_management/vehicles'),
        'position' => 1,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-rentals',
        'name'     => _l('fleet_rentals'),
        'href'     => admin_url('fleet_management/rentals'),
        'position' => 2,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-planning',
        'name'     => _l('fleet_planning'),
        'href'     => admin_url('fleet_management/rentals/planning'),
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-maintenance',
        'name'     => _l('fleet_maintenance'),
        'href'     => admin_url('fleet_management/maintenance'),
        'position' => 4,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-parts',
        'name'     => _l('fleet_parts_articles'),
        'href'     => admin_url('fleet_management/parts'),
        'position' => 5,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-fuel',
        'name'     => _l('fleet_fuel'),
        'href'     => admin_url('fleet_management/fuel'),
        'position' => 6,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-reminders',
        'name'     => _l('fleet_reminders'),
        'href'     => admin_url('fleet_management/reminders'),
        'position' => 7,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-fines',
        'name'     => _l('fleet_fines'),
        'href'     => admin_url('fleet_management/fines'),
        'position' => 8,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-drivers',
        'name'     => _l('fleet_drivers'),
        'href'     => admin_url('fleet_management/drivers'),
        'position' => 9,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-suppliers',
        'name'     => _l('fleet_suppliers'),
        'href'     => admin_url('fleet_management/suppliers'),
        'position' => 10,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-library',
        'name'     => _l('fleet_library'),
        'href'     => admin_url('fleet_management/library'),
        'position' => 11,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-settings',
        'name'     => _l('fleet_settings'),
        'href'     => admin_url('fleet_management/settings'),
        'position' => 12,
    ]);
}

/**
 * Lightweight client-side search for the module list tables. A single delegated
 * handler filters any table marked ".fleet-list" from a ".fleet-search" input in
 * the same panel. Output once in the admin footer to keep the views DRY.
 */
hooks()->add_action('app_admin_footer', 'fleet_management_admin_footer');

function fleet_management_admin_footer()
{
    echo '<style>
.fleet-list-page .panel_s{border:0;border-radius:10px;box-shadow:0 2px 10px rgba(20,30,60,.05);}
.fleet-list-page .fleet-toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;margin-bottom:18px;}
.fleet-list-page .fleet-toolbar h3{margin:0;font-weight:700;font-size:20px;}
.fleet-list-page .fleet-tools{display:flex;align-items:center;flex-wrap:wrap;}
.fleet-list-page .fleet-tools>*{margin-left:8px;margin-top:4px;}
.fleet-list-page .fleet-stat{display:flex;align-items:center;}
.fleet-list-page .fleet-stat .ic{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;margin-right:12px;flex:0 0 46px;}
.fleet-list-page .fleet-stat h2{margin:0;font-size:20px;font-weight:700;line-height:1.1;}
.fleet-list-page .fleet-stat span{color:#97a1b3;font-size:11px;text-transform:uppercase;letter-spacing:.5px;}
.fleet-list-page table.fleet-list{margin-bottom:0;}
.fleet-list-page table.fleet-list>thead>tr>th{border-bottom:1px solid #e6e9f0;color:#97a1b3;font-size:11px;text-transform:uppercase;letter-spacing:.4px;font-weight:600;}
.fleet-list-page table.fleet-list>tbody>tr>td{vertical-align:middle;border-top:1px solid #f0f2f5;}
.fleet-list-page table.fleet-list>tbody>tr:hover{background:#f8f9fc;}
.fleet-list-page .fleet-search{border-radius:20px;}
.fleet-list-page .fleet-veh{display:flex;align-items:center;}
.fleet-list-page .fleet-veh .av{width:38px;height:38px;border-radius:10px;background:#eef1ff;color:#6571ff;display:flex;align-items:center;justify-content:center;margin-right:10px;flex:0 0 38px;}
.fleet-list-page .fleet-plate{display:inline-block;background:#f0f2f5;border-radius:6px;padding:2px 8px;font-weight:600;font-size:12px;letter-spacing:.5px;}
.fleet-list-page .fleet-list .label{border-radius:20px;padding:.4em .85em;font-weight:600;font-size:11px;}
</style>
<script>
(function($){
    $(document).on("keyup", ".fleet-search", function(){
        var q = $(this).val().toLowerCase();
        $(this).closest(".panel-body").find("table.fleet-list > tbody > tr").each(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(q) > -1);
        });
    });
})(jQuery);
</script>';
}

/**
 * Send reminder notifications for expiring vehicle documents (insurance,
 * technical inspection...) X days before the due date. Runs on the Perfex cron.
 */
hooks()->add_action('app_cron', 'fleet_management_cron');

function fleet_management_cron()
{
    $CI = &get_instance();
    $CI->load->model('fleet_management/fleet_management_model');
    $CI->fleet_management_model->send_due_reminders();
    $CI->fleet_management_model->send_due_license_reminders();
}
