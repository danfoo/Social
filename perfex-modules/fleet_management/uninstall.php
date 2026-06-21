<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$tables = [
    'fleet_fines',
    'fleet_vehicle_files',
    'fleet_inspection_files',
    'fleet_inspections',
    'fleet_driver_accidents',
    'fleet_driver_profiles',
    'fleet_payments',
    'fleet_maintenance_files',
    'fleet_part_assignments',
    'fleet_part_order_items',
    'fleet_part_orders',
    'fleet_part_units',
    'fleet_part_categories',
    'fleet_part_items',
    'fleet_parts',
    'fleet_activity',
    'fleet_fuel_logs',
    'fleet_rentals',
    'fleet_assignments',
    'fleet_reminders',
    'fleet_maintenance',
    'fleet_models',
    'fleet_brands',
    'fleet_categories',
    'fleet_suppliers',
    'fleet_vehicles',
];

foreach ($tables as $table) {
    if ($CI->db->table_exists(db_prefix() . $table)) {
        $CI->db->query('DROP TABLE ' . db_prefix() . $table);
    }
}

delete_option('fleet_driver_role_id');
delete_option('fleet_invoice_due_days');
delete_option('fleet_expense_category_id');
delete_option('fleet_management_db_version');
