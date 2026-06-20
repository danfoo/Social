<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

$tables = [
    'fleet_fuel_logs',
    'fleet_rentals',
    'fleet_assignments',
    'fleet_reminders',
    'fleet_maintenance',
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
