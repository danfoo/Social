<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Available vehicle statuses.
 */
function fleet_vehicle_statuses()
{
    return ['available', 'rented', 'maintenance', 'out_of_service'];
}

/**
 * Render a coloured label for a vehicle status.
 */
function fleet_vehicle_status_badge($status)
{
    $map = [
        'available'      => 'success',
        'rented'         => 'info',
        'maintenance'    => 'warning',
        'out_of_service' => 'danger',
    ];
    $class = $map[$status] ?? 'default';

    return '<span class="label label-' . $class . '">' . _l('fleet_status_' . $status) . '</span>';
}

/**
 * Available rental statuses.
 */
function fleet_rental_statuses()
{
    return ['reserved', 'ongoing', 'completed', 'cancelled'];
}

function fleet_rental_status_badge($status)
{
    $map = [
        'reserved'  => 'warning',
        'ongoing'   => 'info',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];
    $class = $map[$status] ?? 'default';

    return '<span class="label label-' . $class . '">' . _l('fleet_status_' . $status) . '</span>';
}

/**
 * Reminder document types (insurance, technical inspection, registration...).
 */
function fleet_reminder_types()
{
    return ['insurance', 'technical_inspection', 'vignette', 'registration', 'service', 'other'];
}

/**
 * Maintenance operation types.
 */
function fleet_maintenance_types()
{
    return ['oil_change', 'revision', 'tires', 'brakes', 'repair', 'bodywork', 'other'];
}

/**
 * Supplier categories.
 */
function fleet_supplier_types()
{
    return ['garage', 'insurance', 'fuel_station', 'rental_partner', 'parts', 'other'];
}

/**
 * Common fuel types, reused for vehicles and fuel logs.
 */
function fleet_fuel_types()
{
    return ['diesel', 'petrol', 'lpg', 'electric', 'hybrid', 'other'];
}

/**
 * Due-date badge for a reminder, based on how close the due date is.
 */
function fleet_reminder_due_badge($due_date, $notify_days = 7)
{
    $today = strtotime(date('Y-m-d'));
    $due   = strtotime($due_date);
    $diff  = (int) floor(($due - $today) / 86400);

    if ($diff < 0) {
        return '<span class="label label-danger">' . _l('fleet_reminder_expired') . '</span>';
    }
    if ($diff <= $notify_days) {
        return '<span class="label label-warning">' . _l('fleet_reminder_soon', $diff) . '</span>';
    }

    return '<span class="label label-success">' . _l('fleet_reminder_ok', $diff) . '</span>';
}
