<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();

if (!$CI->db->table_exists(db_prefix() . 'fleet_vehicles')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_vehicles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `plate` VARCHAR(50) NULL,
        `brand` VARCHAR(100) NULL,
        `model` VARCHAR(100) NULL,
        `year` INT(11) NULL,
        `vin` VARCHAR(100) NULL,
        `color` VARCHAR(50) NULL,
        `category` VARCHAR(100) NULL,
        `fuel_type` VARCHAR(50) NULL,
        `transmission` VARCHAR(50) NULL,
        `seats` INT(11) NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'available',
        `daily_rate` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `daily_rate_with_driver` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `odometer` INT(11) NOT NULL DEFAULT 0,
        `purchase_date` DATE NULL,
        `purchase_price` DECIMAL(15,2) NULL,
        `insurance_company` VARCHAR(191) NULL,
        `insurance_policy` VARCHAR(100) NULL,
        `current_driver_id` INT(11) NULL,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_maintenance')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_maintenance` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `vehicle_id` INT(11) NOT NULL,
        `type` VARCHAR(100) NULL,
        `description` TEXT NULL,
        `service_date` DATE NULL,
        `cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `odometer` INT(11) NULL,
        `provider` VARCHAR(191) NULL,
        `next_service_date` DATE NULL,
        `next_service_odometer` INT(11) NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'completed',
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_reminders')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_reminders` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `vehicle_id` INT(11) NOT NULL,
        `type` VARCHAR(100) NULL,
        `title` VARCHAR(191) NULL,
        `description` TEXT NULL,
        `due_date` DATE NOT NULL,
        `notify_days` INT(11) NOT NULL DEFAULT 7,
        `cost` DECIMAL(15,2) NULL,
        `provider` VARCHAR(191) NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'active',
        `is_notified` TINYINT(1) NOT NULL DEFAULT 0,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_assignments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_assignments` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `vehicle_id` INT(11) NOT NULL,
        `staff_id` INT(11) NOT NULL,
        `date_start` DATE NULL,
        `date_end` DATE NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'active',
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_rentals')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_rentals` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `vehicle_id` INT(11) NOT NULL,
        `clientid` INT(11) NOT NULL,
        `driver_id` INT(11) NULL,
        `with_driver` TINYINT(1) NOT NULL DEFAULT 0,
        `date_start` DATE NOT NULL,
        `date_end` DATE NOT NULL,
        `days` INT(11) NOT NULL DEFAULT 1,
        `daily_rate` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `total` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `odometer_start` INT(11) NULL,
        `odometer_end` INT(11) NULL,
        `pickup_location` VARCHAR(191) NULL,
        `return_location` VARCHAR(191) NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'reserved',
        `invoice_id` INT(11) NULL,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`),
        KEY `clientid` (`clientid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_suppliers')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_suppliers` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `type` VARCHAR(50) NOT NULL DEFAULT 'other',
        `contact_name` VARCHAR(191) NULL,
        `phone` VARCHAR(50) NULL,
        `email` VARCHAR(191) NULL,
        `address` TEXT NULL,
        `vat` VARCHAR(100) NULL,
        `website` VARCHAR(191) NULL,
        `notes` TEXT NULL,
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_fuel_logs')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_fuel_logs` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `vehicle_id` INT(11) NOT NULL,
        `driver_id` INT(11) NULL,
        `supplier_id` INT(11) NULL,
        `date` DATE NOT NULL,
        `odometer` INT(11) NULL,
        `liters` DECIMAL(10,2) NOT NULL DEFAULT 0,
        `price_per_liter` DECIMAL(10,3) NULL,
        `total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `fuel_type` VARCHAR(50) NULL,
        `full_tank` TINYINT(1) NOT NULL DEFAULT 1,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Link maintenance and reminders to a supplier (idempotent upgrade for
// installations created before these columns existed).
if (!$CI->db->field_exists('supplier_id', db_prefix() . 'fleet_maintenance')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_maintenance` ADD `supplier_id` INT(11) NULL AFTER `provider`');
}
if (!$CI->db->field_exists('supplier_id', db_prefix() . 'fleet_reminders')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_reminders` ADD `supplier_id` INT(11) NULL AFTER `provider`');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_categories` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(150) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_brands')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_brands` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(150) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_models')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_models` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `brand_id` INT(11) NOT NULL,
        `name` VARCHAR(150) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `brand_id` (`brand_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

/**
 * Create a dedicated "Driver" staff role once, and remember its id.
 * Drivers are simply staff members holding this role.
 */
if (get_option('fleet_driver_role_id') == '') {
    $existing = $CI->db->get_where(db_prefix() . 'roles', ['name' => 'Chauffeur'])->row();

    if ($existing) {
        $role_id = $existing->roleid;
    } else {
        $CI->db->insert(db_prefix() . 'roles', [
            'name'        => 'Chauffeur',
            'permissions' => serialize([]),
        ]);
        $role_id = $CI->db->insert_id();
    }

    add_option('fleet_driver_role_id', $role_id);
}

add_option('fleet_invoice_due_days', 14);

// Mark the schema as up to date so the auto-migration stops re-running.
$fleet_db_version = defined('FLEET_MANAGEMENT_DB_VERSION') ? FLEET_MANAGEMENT_DB_VERSION : '1.0.3';
if (get_option('fleet_management_db_version') === '') {
    add_option('fleet_management_db_version', $fleet_db_version);
} else {
    update_option('fleet_management_db_version', $fleet_db_version);
}
