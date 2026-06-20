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
define('FLEET_MANAGEMENT_DB_VERSION', '1.0.4');

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
        'href'     => admin_url('fleet_management/vehicles'),
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
        'slug'     => 'fleet-maintenance',
        'name'     => _l('fleet_maintenance'),
        'href'     => admin_url('fleet_management/maintenance'),
        'position' => 3,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-fuel',
        'name'     => _l('fleet_fuel'),
        'href'     => admin_url('fleet_management/fuel'),
        'position' => 4,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-reminders',
        'name'     => _l('fleet_reminders'),
        'href'     => admin_url('fleet_management/reminders'),
        'position' => 5,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-drivers',
        'name'     => _l('fleet_drivers'),
        'href'     => admin_url('fleet_management/drivers'),
        'position' => 6,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-suppliers',
        'name'     => _l('fleet_suppliers'),
        'href'     => admin_url('fleet_management/suppliers'),
        'position' => 7,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-library',
        'name'     => _l('fleet_library'),
        'href'     => admin_url('fleet_management/library'),
        'position' => 8,
    ]);
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
}
