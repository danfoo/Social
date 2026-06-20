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

if (!$CI->db->table_exists(db_prefix() . 'fleet_activity')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_activity` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `vehicle_id` INT(11) NOT NULL,
        `staff_id` INT(11) NULL,
        `type` VARCHAR(50) NOT NULL DEFAULT 'other',
        `description` TEXT NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_maintenance_files')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_maintenance_files` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `maintenance_id` INT(11) NOT NULL,
        `file_name` VARCHAR(191) NOT NULL,
        `original_name` VARCHAR(191) NULL,
        `taken_date` DATE NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `maintenance_id` (`maintenance_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_parts')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_parts` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `reference` VARCHAR(100) NULL,
        `vehicle_id` INT(11) NULL,
        `supplier_id` INT(11) NULL,
        `maintenance_id` INT(11) NULL,
        `quantity` INT(11) NOT NULL DEFAULT 1,
        `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `total_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `purchase_date` DATE NULL,
        `status` VARCHAR(50) NOT NULL DEFAULT 'installed',
        `notes` TEXT NULL,
        `expense_id` INT(11) NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `vehicle_id` (`vehicle_id`),
        KEY `supplier_id` (`supplier_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Parts inventory workflow: catalog items -> supplier orders -> stock -> assignments.
if (!$CI->db->table_exists(db_prefix() . 'fleet_part_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_part_items` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(191) NOT NULL,
        `reference` VARCHAR(100) NULL,
        `category` VARCHAR(150) NULL,
        `unit` VARCHAR(50) NULL,
        `min_stock` INT(11) NOT NULL DEFAULT 0,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_part_orders')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_part_orders` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `item_id` INT(11) NOT NULL,
        `supplier_id` INT(11) NULL,
        `quantity` INT(11) NOT NULL DEFAULT 1,
        `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `total_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `status` VARCHAR(30) NOT NULL DEFAULT 'ordered',
        `order_date` DATE NULL,
        `received_date` DATE NULL,
        `expense_id` INT(11) NULL,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `item_id` (`item_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

if (!$CI->db->table_exists(db_prefix() . 'fleet_part_assignments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_part_assignments` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `item_id` INT(11) NOT NULL,
        `vehicle_id` INT(11) NULL,
        `maintenance_id` INT(11) NULL,
        `assigned_to` VARCHAR(191) NULL,
        `quantity` INT(11) NOT NULL DEFAULT 1,
        `unit_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `assigned_date` DATE NULL,
        `notes` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `item_id` (`item_id`),
        KEY `vehicle_id` (`vehicle_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Multiple line items per part order.
if (!$CI->db->table_exists(db_prefix() . 'fleet_part_order_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_part_order_items` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `order_id` INT(11) NOT NULL,
        `item_id` INT(11) NOT NULL,
        `quantity` INT(11) NOT NULL DEFAULT 1,
        `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `total_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
        PRIMARY KEY (`id`),
        KEY `order_id` (`order_id`),
        KEY `item_id` (`item_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    // Migrate existing single-item orders into their first line item.
    if ($CI->db->table_exists(db_prefix() . 'fleet_part_orders') && $CI->db->field_exists('item_id', db_prefix() . 'fleet_part_orders')) {
        foreach ($CI->db->get(db_prefix() . 'fleet_part_orders')->result_array() as $o) {
            if (!empty($o['item_id'])) {
                $CI->db->insert(db_prefix() . 'fleet_part_order_items', [
                    'order_id'    => $o['id'],
                    'item_id'     => $o['item_id'],
                    'quantity'    => $o['quantity'],
                    'unit_price'  => $o['unit_price'],
                    'total_price' => $o['total_price'],
                ]);
            }
        }
    }
}

// Parts replaced during a maintenance operation (idempotent upgrade).
if (!$CI->db->field_exists('parts', db_prefix() . 'fleet_maintenance')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_maintenance` ADD `parts` TEXT NULL AFTER `description`');
}

// Link maintenance / fuel / reminders to a Perfex core expense (idempotent).
foreach (['fleet_maintenance', 'fleet_fuel_logs', 'fleet_reminders'] as $fleet_table) {
    if ($CI->db->table_exists(db_prefix() . $fleet_table) && !$CI->db->field_exists('expense_id', db_prefix() . $fleet_table)) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . $fleet_table . '` ADD `expense_id` INT(11) NULL');
    }
}

// Configurable part categories and units.
if (!$CI->db->table_exists(db_prefix() . 'fleet_part_categories')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_part_categories` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(150) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}
if (!$CI->db->table_exists(db_prefix() . 'fleet_part_units')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_part_units` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(80) NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Payment tracking (supplier accounting) + supplier invoice reference.
foreach (['fleet_part_orders', 'fleet_maintenance', 'fleet_fuel_logs', 'fleet_reminders'] as $fleet_table) {
    if (!$CI->db->table_exists(db_prefix() . $fleet_table)) {
        continue;
    }
    if (!$CI->db->field_exists('paid', db_prefix() . $fleet_table)) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . $fleet_table . '` ADD `paid` TINYINT(1) NOT NULL DEFAULT 0');
    }
    if (!$CI->db->field_exists('paid_date', db_prefix() . $fleet_table)) {
        $CI->db->query('ALTER TABLE `' . db_prefix() . $fleet_table . '` ADD `paid_date` DATE NULL');
    }
}
if ($CI->db->table_exists(db_prefix() . 'fleet_part_orders') && !$CI->db->field_exists('invoice_no', db_prefix() . 'fleet_part_orders')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_part_orders` ADD `invoice_no` VARCHAR(100) NULL');
}
if ($CI->db->table_exists(db_prefix() . 'fleet_part_orders') && !$CI->db->field_exists('billable', db_prefix() . 'fleet_part_orders')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_part_orders` ADD `billable` TINYINT(1) NOT NULL DEFAULT 0');
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_part_orders` ADD `clientid` INT(11) NULL');
}

// Supplier payments ledger (supports partial payments against any costed record).
if (!$CI->db->table_exists(db_prefix() . 'fleet_payments')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_payments` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `source_table` VARCHAR(50) NOT NULL,
        `source_id` INT(11) NOT NULL,
        `supplier_id` INT(11) NULL,
        `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
        `payment_date` DATE NULL,
        `payment_mode` VARCHAR(100) NULL,
        `note` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `source` (`source_table`, `source_id`),
        KEY `supplier_id` (`supplier_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Driver profiles (personal/license details, one row per driver staff member).
if (!$CI->db->table_exists(db_prefix() . 'fleet_driver_profiles')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_driver_profiles` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `staff_id` INT(11) NOT NULL,
        `date_of_birth` DATE NULL,
        `national_id` VARCHAR(100) NULL,
        `phone` VARCHAR(50) NULL,
        `address` TEXT NULL,
        `license_number` VARCHAR(100) NULL,
        `license_category` VARCHAR(100) NULL,
        `license_issue_date` DATE NULL,
        `license_expiry` DATE NULL,
        `hire_date` DATE NULL,
        `blood_type` VARCHAR(10) NULL,
        `emergency_contact` VARCHAR(191) NULL,
        `emergency_phone` VARCHAR(50) NULL,
        `notes` TEXT NULL,
        `license_notified` TINYINT(1) NOT NULL DEFAULT 0,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        UNIQUE KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// License-expiry notification tracking (idempotent upgrade).
if ($CI->db->table_exists(db_prefix() . 'fleet_driver_profiles') && !$CI->db->field_exists('license_notified', db_prefix() . 'fleet_driver_profiles')) {
    $CI->db->query('ALTER TABLE `' . db_prefix() . 'fleet_driver_profiles` ADD `license_notified` TINYINT(1) NOT NULL DEFAULT 0');
}

// Driver incident/accident log.
if (!$CI->db->table_exists(db_prefix() . 'fleet_driver_accidents')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "fleet_driver_accidents` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `staff_id` INT(11) NOT NULL,
        `vehicle_id` INT(11) NULL,
        `accident_date` DATE NULL,
        `location` VARCHAR(191) NULL,
        `severity` VARCHAR(30) NOT NULL DEFAULT 'minor',
        `at_fault` TINYINT(1) NOT NULL DEFAULT 0,
        `third_party` VARCHAR(191) NULL,
        `cost` DECIMAL(15,2) NULL,
        `description` TEXT NULL,
        `created_by` INT(11) NULL,
        `date_created` DATETIME NULL,
        PRIMARY KEY (`id`),
        KEY `staff_id` (`staff_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');
}

// Dedicated expense category so fleet costs are grouped in the Expenses module.
if (get_option('fleet_expense_category_id') == '' && $CI->db->table_exists(db_prefix() . 'expenses_categories')) {
    $CI->db->insert(db_prefix() . 'expenses_categories', [
        'name'        => 'Fleet management',
        'description' => 'Auto-generated by the Fleet Management module',
    ]);
    add_option('fleet_expense_category_id', $CI->db->insert_id());
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
add_option('fleet_occupancy_days', 30);
add_option('fleet_license_notify_days', 30);

// Mark the schema as up to date so the auto-migration stops re-running.
$fleet_db_version = defined('FLEET_MANAGEMENT_DB_VERSION') ? FLEET_MANAGEMENT_DB_VERSION : '1.0.3';
if (get_option('fleet_management_db_version') === '') {
    add_option('fleet_management_db_version', $fleet_db_version);
} else {
    update_option('fleet_management_db_version', $fleet_db_version);
}
