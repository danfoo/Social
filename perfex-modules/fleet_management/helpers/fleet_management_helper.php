<?php

defined('BASEPATH') or exit('No direct script access allowed');

/* ===================================================================== *
 * Per-role feature access
 * --------------------------------------------------------------------- *
 * Lets an admin decide, per Perfex role, which fleet features are visible
 * (e.g. a fuel manager only sees Fuel). Configuration is stored in the
 * `fleet_role_features` option as [roleid => [feature => 1, ...]].
 * A role with no entry keeps full access (backward compatible).
 * ===================================================================== */

/**
 * The configurable features: slug => [label lang key, controller path].
 */
function fleet_features()
{
    return [
        'dashboard'   => ['fleet_dashboard', 'fleet_management/dashboard'],
        'vehicles'    => ['fleet_vehicles', 'fleet_management/vehicles'],
        'rentals'     => ['fleet_rentals', 'fleet_management/rentals'],
        'maintenance' => ['fleet_maintenance', 'fleet_management/maintenance'],
        'parts'       => ['fleet_parts_articles', 'fleet_management/parts'],
        'fuel'        => ['fleet_fuel', 'fleet_management/fuel'],
        'reminders'   => ['fleet_reminders', 'fleet_management/reminders'],
        'fines'       => ['fleet_fines', 'fleet_management/fines'],
        'drivers'     => ['fleet_drivers', 'fleet_management/drivers'],
        'suppliers'   => ['fleet_suppliers', 'fleet_management/suppliers'],
        'library'     => ['fleet_library', 'fleet_management/library'],
    ];
}

function fleet_role_feature_map()
{
    $raw = get_option('fleet_role_features');
    if (!$raw) {
        return [];
    }
    $map = @unserialize($raw);

    return is_array($map) ? $map : [];
}

/** Role id of the currently logged-in staff member (cached). */
function fleet_current_role_id()
{
    static $rid = null;
    if ($rid !== null) {
        return $rid;
    }

    $CI    = &get_instance();
    $staff = $CI->db->get_where(db_prefix() . 'staff', ['staffid' => get_staff_user_id()])->row();
    $rid   = $staff ? (int) $staff->role : 0;

    return $rid;
}

/**
 * Whether the current user may see a given fleet feature. Admins always can;
 * a role with no saved configuration keeps full access.
 */
function fleet_can_feature($feature)
{
    if (is_admin()) {
        return true;
    }

    $map  = fleet_role_feature_map();
    $role = fleet_current_role_id();

    if (!isset($map[$role])) {
        return true;
    }

    return !empty($map[$role][$feature]);
}

/** URL of the first feature the current user is allowed to see ('' if none). */
function fleet_first_allowed_feature_url()
{
    foreach (fleet_features() as $slug => $f) {
        if (fleet_can_feature($slug)) {
            return admin_url($f[1]);
        }
    }

    return '';
}

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
 * Resolve a period keyword to a [start, end] SQL date range ([null, null] = all time).
 */
function fleet_period_range($period)
{
    switch ($period) {
        case 'month':
            return [date('Y-m-01'), date('Y-m-t')];
        case 'quarter':
            $q     = (int) ceil(date('n') / 3);
            $first = ($q - 1) * 3 + 1;
            $start = date('Y-' . str_pad($first, 2, '0', STR_PAD_LEFT) . '-01');

            return [$start, date('Y-m-t', strtotime(date('Y-' . str_pad($first + 2, 2, '0', STR_PAD_LEFT) . '-01')))];
        case 'year':
            return [date('Y-01-01'), date('Y-12-31')];
        case 'all':
        default:
            return [null, null];
    }
}

/**
 * Date range [start, end] for the fuel dashboard granularity:
 * day (default), week, month or year.
 */
function fleet_fuel_period_range($period)
{
    switch ($period) {
        case 'week':
            return [date('Y-m-d', strtotime('monday this week')), date('Y-m-d', strtotime('sunday this week'))];
        case 'month':
            return [date('Y-m-01'), date('Y-m-t')];
        case 'year':
            return [date('Y-01-01'), date('Y-12-31')];
        case 'day':
        default:
            return [date('Y-m-d'), date('Y-m-d')];
    }
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
        'invoice'    => 'fa-file-text',
        'fine'       => 'fa-gavel',
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

/**
 * Send an HTML e-mail to one or several recipients using Perfex's configured
 * mailer (SMTP settings + decryption handled by application/config/email.php).
 * No-op when fleet e-mail notifications are disabled in the settings.
 */
function fleet_send_email($recipients, $subject, $message)
{
    if (!get_option('fleet_email_notifications')) {
        return false;
    }

    $recipients = array_filter(array_unique((array) $recipients));
    if (empty($recipients)) {
        return false;
    }

    $CI = &get_instance();
    $CI->load->library('email');

    $from_email = get_option('smtp_email') ?: get_option('email');
    $from_name  = get_option('companyname');

    foreach ($recipients as $to) {
        $CI->email->clear(true);
        $CI->email->set_mailtype('html');
        if ($from_email) {
            $CI->email->from($from_email, $from_name);
        }
        $CI->email->to($to);
        $CI->email->subject($subject);
        $CI->email->message($message);
        // Swallow transport errors so a bad address never breaks the cron run.
        @$CI->email->send(false);
    }

    return true;
}

/**
 * Shared company footer block (HTML) for the module's generated PDFs.
 */
function fleet_pdf_footer_html()
{
    $footer = '<div style="font-size: 8px; color: #777777; text-align: center; line-height: 1.4; border-top: 1px solid #dddddd; padding-top: 4px;"><strong>{company_name} ( LRC )</strong><br>T&eacute;l. {company_phone} &nbsp;&middot;&nbsp; {company_email} &nbsp;&middot;&nbsp; {website}<br>Si&egrave;ge Social Mamelle Cit&eacute; Mbackiyou Faye. DAKAR - SENEGAL - R.C SN.DKR.2024.B.37304 - N.I.N.E.A 011532480</div>';

    return str_replace(
        ['{company_name}', '{company_phone}', '{company_email}', '{website}'],
        [get_option('invoice_company_name'), get_option('invoice_company_phonenumber'), get_option('smtp_email'), site_url()],
        $footer
    );
}

/**
 * Human-readable fuel gauge level stored in eighths (0..8).
 */
function fleet_fuel_eighths_label($value)
{
    if ($value === null || $value === '') {
        return '—';
    }

    $value = max(0, min(8, (int) $value));
    $map   = [
        0 => _l('fleet_fuel_empty'),
        8 => _l('fleet_fuel_full'),
    ];

    return isset($map[$value]) ? $map[$value] : ($value . '/8');
}

function fleet_fine_types()
{
    return ['speeding', 'parking', 'red_light', 'documents', 'phone', 'other'];
}

function fleet_fine_status_badge($status)
{
    $map = [
        'pending'   => 'warning',
        'paid'      => 'success',
        'contested' => 'info',
        'cancelled' => 'default',
    ];
    $color = isset($map[$status]) ? $map[$status] : 'default';

    return '<span class="label label-' . $color . '">' . _l('fleet_fstatus_' . $status) . '</span>';
}

function fleet_deposit_status_badge($status)
{
    $map = [
        'none'     => 'default',
        'held'     => 'info',
        'returned' => 'success',
        'withheld' => 'danger',
        'partial'  => 'warning',
    ];
    $color = isset($map[$status]) ? $map[$status] : 'default';

    return '<span class="label label-' . $color . '">' . _l('fleet_deposit_status_' . $status) . '</span>';
}

function fleet_vehicle_document_types()
{
    return ['registration', 'insurance', 'technical_inspection', 'vignette', 'permit', 'other'];
}

function fleet_document_expiry_badge($expiry_date)
{
    if (empty($expiry_date) || $expiry_date === '0000-00-00') {
        return '';
    }

    $days = (int) floor((strtotime($expiry_date) - strtotime(date('Y-m-d'))) / 86400);

    if ($days < 0) {
        return '<span class="label label-danger">' . _l('fleet_expired') . '</span>';
    }
    if ($days <= 30) {
        return '<span class="label label-warning">' . _l('fleet_reminder_soon', $days) . '</span>';
    }

    return '<span class="label label-success">' . _l('fleet_valid') . '</span>';
}

function fleet_accident_severity_badge($severity)
{
    $map = [
        'minor'    => 'default',
        'moderate' => 'warning',
        'severe'   => 'danger',
    ];
    $color = isset($map[$severity]) ? $map[$severity] : 'default';

    return '<span class="label label-' . $color . '">' . _l('fleet_severity_' . $severity) . '</span>';
}
