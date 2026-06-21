<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$bc        = get_base_currency();
$logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
$logo_path = $logo_file ? FCPATH . 'uploads/company/' . $logo_file : '';
$has_logo  = $logo_path && is_file($logo_path);

$client_name = $client ? $client->company : '';
?>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:55%; vertical-align:top;">
            <?php if ($has_logo) : ?>
                <img src="<?php echo $logo_path; ?>" style="height:48px;"><br><br>
            <?php endif; ?>
            <span style="font-size:15px; font-weight:bold;"><?php echo html_escape(get_option('invoice_company_name')); ?></span><br>
            <span style="color:#777777;"><?php echo nl2br(html_escape(get_option('invoice_company_address'))); ?></span><br>
            <?php if (get_option('invoice_company_phonenumber')) : ?><span style="color:#777777;"><?php echo html_escape(get_option('invoice_company_phonenumber')); ?></span><br><?php endif; ?>
        </td>
        <td style="width:45%; vertical-align:top; text-align:right;">
            <span style="font-size:16px; font-weight:bold; color:#000000;"><?php echo _l('fleet_contract'); ?></span><br>
            <span style="color:#777777;">N° LOC-<?php echo $rental->id; ?></span><br>
            <span style="color:#777777;"><?php echo _d(date('Y-m-d')); ?></span>
        </td>
    </tr>
</table>

<br>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:50%; vertical-align:top;">
            <strong><?php echo _l('fleet_contract_lessor'); ?></strong><br>
            <?php echo html_escape(get_option('invoice_company_name')); ?><br>
            <?php if (get_option('company_vat')) : ?>TVA / NINEA: <?php echo html_escape(get_option('company_vat')); ?><?php endif; ?>
        </td>
        <td style="width:50%; vertical-align:top;">
            <strong><?php echo _l('fleet_contract_lessee'); ?></strong><br>
            <?php echo html_escape($client_name); ?><br>
            <?php if ($client) : ?>
                <?php echo html_escape($client->phonenumber); ?><br>
                <?php echo nl2br(html_escape(trim(($client->address ?? '') . ' ' . ($client->city ?? '') . ' ' . ($client->zip ?? '')))); ?><br>
                <?php if (!empty($client->vat)) : ?>TVA: <?php echo html_escape($client->vat); ?><?php endif; ?>
            <?php endif; ?>
        </td>
    </tr>
</table>

<br>
<table border="1" cellpadding="6" style="width:100%; font-size:11px; border-collapse:collapse;">
    <tr style="background-color:#000000; color:#dc9f2b;">
        <th width="50%" style="text-align:left; color:#dc9f2b;"><?php echo _l('fleet_vehicle'); ?></th>
        <th width="50%" style="text-align:left; color:#dc9f2b;"><?php echo _l('fleet_period'); ?></th>
    </tr>
    <tr>
        <td width="50%">
            <strong><?php echo html_escape($rental->vehicle_name); ?></strong><br>
            <?php echo _l('fleet_plate'); ?>: <?php echo html_escape($rental->vehicle_plate); ?>
        </td>
        <td width="50%">
            <?php echo _d($rental->date_start); ?> &rarr; <?php echo _d($rental->date_end); ?><br>
            <?php echo (int) $rental->days; ?> <?php echo _l('fleet_days'); ?>
            · <?php echo $rental->with_driver ? _l('fleet_with_driver') : _l('fleet_without_driver'); ?>
            <?php if ($rental->with_driver && $rental->driver_name) : ?><br><?php echo _l('fleet_driver'); ?>: <?php echo html_escape($rental->driver_name); ?><?php endif; ?>
        </td>
    </tr>
</table>

<br>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:50%; vertical-align:top;">
            <?php if ($rental->pickup_location) : ?><?php echo _l('fleet_pickup_location'); ?>: <strong><?php echo html_escape($rental->pickup_location); ?></strong><br><?php endif; ?>
            <?php if ($rental->return_location) : ?><?php echo _l('fleet_return_location'); ?>: <strong><?php echo html_escape($rental->return_location); ?></strong><?php endif; ?>
        </td>
        <td style="width:50%;">
            <table style="width:100%; font-size:12px;">
                <tr><td style="text-align:right; color:#777777;"><?php echo _l('fleet_daily_rate'); ?></td><td style="text-align:right;"><?php echo app_format_money($rental->daily_rate, $bc); ?></td></tr>
                <tr><td style="text-align:right; color:#777777;"><?php echo _l('fleet_total'); ?></td><td style="text-align:right; font-weight:bold;"><?php echo app_format_money($rental->total, $bc); ?></td></tr>
                <tr><td style="text-align:right; color:#777777;"><?php echo _l('fleet_deposit'); ?></td><td style="text-align:right;"><?php echo $rental->deposit !== null ? app_format_money($rental->deposit, $bc) : '—'; ?></td></tr>
            </table>
        </td>
    </tr>
</table>

<?php if ($rental->notes) : ?>
    <br><span style="font-size:10px; color:#777777;"><?php echo nl2br(html_escape($rental->notes)); ?></span>
<?php endif; ?>

<br><br>
<?php
$contract_terms = get_option('fleet_contract_terms');
// When the configured value is empty (or only HTML whitespace), fall back to
// the built-in plain-text default. The value may contain rich HTML from the
// editor, which TCPDF renders directly.
if (trim(strip_tags($contract_terms)) === '') {
    $contract_terms = nl2br(html_escape(_l('fleet_contract_terms')));
}
?>
<div style="font-size:9px; color:#777777;"><?php echo $contract_terms; ?></div>

<br><br><br>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:50%;"><strong><?php echo _l('fleet_contract_lessor'); ?></strong><br><br>__________________________</td>
        <td style="width:50%;"><strong><?php echo _l('fleet_contract_lessee'); ?></strong><br><span style="color:#777777;"><?php echo _l('fleet_contract_read_approved'); ?></span><br>__________________________</td>
    </tr>
</table>
