<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$logo_file   = get_option('company_logo_dark') ?: get_option('company_logo');
$logo_path   = $logo_file ? FCPATH . 'uploads/company/' . $logo_file : '';
$has_logo    = $logo_path && is_file($logo_path);
$client_name = $client ? $client->company : '';

$render_block = function ($type, $title) use ($inspections, $files) {
    $insp = $inspections[$type] ?? null;
    ?>
    <table border="1" cellpadding="6" style="width:100%; font-size:11px; border-collapse:collapse;">
        <tr style="background-color:#000000; color:#dc9f2b;">
            <th colspan="2" style="text-align:left; color:#dc9f2b;"><?php echo $title; ?></th>
        </tr>
        <?php if (!$insp) : ?>
            <tr><td colspan="2" style="color:#777777;"><?php echo _l('fleet_inspection_not_done'); ?></td></tr>
        <?php else : ?>
            <tr>
                <td width="50%"><?php echo _l('fleet_date'); ?>: <strong><?php echo $insp['inspection_date'] ? _d($insp['inspection_date']) : '—'; ?></strong></td>
                <td width="50%"><?php echo _l('fleet_odometer'); ?>: <strong><?php echo $insp['odometer'] !== null ? ((int) $insp['odometer'] . ' km') : '—'; ?></strong></td>
            </tr>
            <tr>
                <td colspan="2"><?php echo _l('fleet_fuel_level'); ?>: <strong><?php echo fleet_fuel_eighths_label($insp['fuel_level']); ?></strong></td>
            </tr>
            <tr><td colspan="2"><?php echo _l('fleet_exterior_condition'); ?>: <?php echo nl2br(html_escape($insp['exterior_condition'])) ?: '—'; ?></td></tr>
            <tr><td colspan="2"><?php echo _l('fleet_interior_condition'); ?>: <?php echo nl2br(html_escape($insp['interior_condition'])) ?: '—'; ?></td></tr>
            <tr><td colspan="2"><?php echo _l('fleet_damages'); ?>: <?php echo nl2br(html_escape($insp['damages'])) ?: '—'; ?></td></tr>
        <?php endif; ?>
    </table>

    <?php
    $photos = $insp ? ($files[$type] ?? []) : [];
    if (!empty($photos)) : ?>
        <table style="width:100%; font-size:10px;"><tr>
            <?php foreach ($photos as $i => $f) :
                $abs = FCPATH . 'uploads/fleet_management/inspections/' . $f['inspection_id'] . '/' . $f['file_name'];
                if (!is_file($abs)) { continue; }
                ?>
                <td style="width:25%; text-align:center; padding:3px;"><img src="<?php echo $abs; ?>" style="width:38mm;"></td>
                <?php if (($i + 1) % 4 === 0) : ?></tr><tr><?php endif; ?>
            <?php endforeach; ?>
        </tr></table>
    <?php endif; ?>
    <br>
    <?php
};
?>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:55%; vertical-align:top;">
            <?php if ($has_logo) : ?><img src="<?php echo $logo_path; ?>" style="height:42px;"><br><br><?php endif; ?>
            <span style="font-size:14px; font-weight:bold;"><?php echo html_escape(get_option('invoice_company_name')); ?></span>
        </td>
        <td style="width:45%; vertical-align:top; text-align:right;">
            <span style="font-size:16px; font-weight:bold; color:#000000;"><?php echo _l('fleet_inspection'); ?></span><br>
            <span style="color:#777777;">N° LOC-<?php echo $rental->id; ?></span>
        </td>
    </tr>
</table>

<br>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:50%;"><strong><?php echo _l('fleet_vehicle'); ?>:</strong> <?php echo html_escape($rental->vehicle_name); ?> (<?php echo html_escape($rental->vehicle_plate); ?>)</td>
        <td style="width:50%;"><strong><?php echo _l('fleet_client'); ?>:</strong> <?php echo html_escape($client_name); ?></td>
    </tr>
    <tr>
        <td colspan="2"><strong><?php echo _l('fleet_period'); ?>:</strong> <?php echo _d($rental->date_start); ?> &rarr; <?php echo _d($rental->date_end); ?></td>
    </tr>
</table>

<br>
<?php $render_block('checkout', _l('fleet_inspection_checkout')); ?>
<?php $render_block('checkin', _l('fleet_inspection_checkin')); ?>

<br>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:50%;"><strong><?php echo _l('fleet_contract_lessor'); ?></strong><br><br>__________________________</td>
        <td style="width:50%;"><strong><?php echo _l('fleet_contract_lessee'); ?></strong><br><br>__________________________</td>
    </tr>
</table>
