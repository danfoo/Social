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
 * Stream an array of rows as a downloadable CSV file and stop execution.
 * Uses ";" as the separator and a UTF-8 BOM so Excel opens accents correctly.
 *
 * @param string $filename Base file name (date is appended).
 * @param array  $headers  Column header labels.
 * @param array  $rows     Array of rows (each an indexed array of scalar values).
 */
function fleet_export_csv($filename, $headers, $rows)
{
    if (ob_get_length()) {
        ob_end_clean();
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '-' . date('Y-m-d') . '.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    fputs($out, chr(0xEF) . chr(0xBB) . chr(0xBF));
    fputcsv($out, $headers, ';');
    foreach ($rows as $row) {
        fputcsv($out, $row, ';');
    }
    fclose($out);
    exit;
}

/**
 * Part / article statuses.
 */
function fleet_part_statuses()
{
    return ['installed', 'in_stock', 'ordered', 'returned'];
}

/**
 * Activity log types and their icon / colour for the vehicle timeline.
 */
function fleet_activity_icon($type)
{
    $map = [
        'vehicle'    => 'fa-car',
        'assignment' => 'fa-user',
        'maintenance'=> 'fa-wrench',
        'part'       => 'fa-cog',
        'photo'      => 'fa-camera',
        'reminder'   => 'fa-bell',
        'fuel'       => 'fa-tint',
        'odometer'   => 'fa-tachometer',
        'rental'     => 'fa-calendar',
        'invoice'    => 'fa-file-text-o',
    ];

    return $map[$type] ?? 'fa-circle';
}

function fleet_activity_color($type)
{
    $map = [
        'vehicle'    => 'default',
        'assignment' => 'info',
        'maintenance'=> 'warning',
        'part'       => 'primary',
        'photo'      => 'warning',
        'reminder'   => 'danger',
        'fuel'       => 'success',
        'odometer'   => 'primary',
        'rental'     => 'info',
        'invoice'    => 'success',
    ];

    return $map[$type] ?? 'default';
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
