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

$CI = &get_instance();

register_activation_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_activation_hook');
register_deactivation_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_deactivation_hook');
register_uninstall_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_uninstall_hook');

register_language_files(FLEET_MANAGEMENT_MODULE, [FLEET_MANAGEMENT_MODULE]);

$CI->load->helper(FLEET_MANAGEMENT_MODULE . '/fleet_management');

function fleet_management_activation_hook()
{
    require_once __DIR__ . '/install.php';
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
        'slug'     => 'fleet-reminders',
        'name'     => _l('fleet_reminders'),
        'href'     => admin_url('fleet_management/reminders'),
        'position' => 4,
    ]);

    $CI->app_menu->add_sidebar_children_item('fleet-management', [
        'slug'     => 'fleet-drivers',
        'name'     => _l('fleet_drivers'),
        'href'     => admin_url('fleet_management/drivers'),
        'position' => 5,
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
