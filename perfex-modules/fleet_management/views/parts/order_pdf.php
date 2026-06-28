<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$bc        = get_base_currency();
$remaining = max(0, (float) $order->total_price - (float) $paid);
$logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
$logo_path = $logo_file ? FCPATH . 'uploads/company/' . $logo_file : '';
$has_logo  = $logo_path && is_file($logo_path);
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
            <?php if (get_option('company_vat')) : ?><span style="color:#777777;">TVA: <?php echo html_escape(get_option('company_vat')); ?></span><?php endif; ?>
        </td>
        <td style="width:45%; vertical-align:top; text-align:right;">
            <span style="font-size:15px; font-weight:bold; color:#000000;"><?php echo _l('fleet_purchase_order'); ?></span><br>
            <span style="color:#777777;">N° PO-<?php echo $order->id; ?></span><br>
            <span style="color:#777777;"><?php echo _l('fleet_order_date'); ?>: <?php echo $order->order_date ? _d($order->order_date) : '-'; ?></span><br>
            <?php if ($order->invoice_no) : ?><span style="color:#777777;"><?php echo _l('fleet_supplier_invoice_no'); ?>: <?php echo html_escape($order->invoice_no); ?></span><?php endif; ?>
        </td>
    </tr>
</table>

<br><br>
<table style="width:100%; font-size:11px;">
    <tr>
        <td style="width:50%; vertical-align:top;">
            <strong><?php echo _l('fleet_supplier'); ?></strong><br>
            <?php if ($supplier) : ?>
                <?php echo html_escape($supplier->name); ?><br>
                <?php echo html_escape($supplier->contact_name); ?><br>
                <?php echo html_escape($supplier->phone); ?> <?php echo html_escape($supplier->email); ?><br>
                <?php echo nl2br(html_escape($supplier->address)); ?><br>
                <?php if ($supplier->vat) : ?>TVA: <?php echo html_escape($supplier->vat); ?><?php endif; ?>
            <?php else : ?>—<?php endif; ?>
        </td>
        <td style="width:50%; vertical-align:top; text-align:right;">
            <strong><?php echo _l('fleet_status'); ?></strong>: <?php echo _l('fleet_ostatus_' . $order->status); ?>
        </td>
    </tr>
</table>

<br>
<table border="1" cellpadding="6" style="width:100%; font-size:11px; border-collapse:collapse;">
    <thead>
        <tr style="background-color:#000000; color:#dc9f2b;">
            <th width="7%" style="text-align:center; color:#dc9f2b;">#</th>
            <th width="47%" style="text-align:left; color:#dc9f2b;"><?php echo _l('fleet_part'); ?></th>
            <th width="13%" style="text-align:center; color:#dc9f2b;"><?php echo _l('fleet_quantity'); ?></th>
            <th width="16%" style="text-align:right; color:#dc9f2b;"><?php echo _l('fleet_unit_price'); ?></th>
            <th width="17%" style="text-align:right; color:#dc9f2b;"><?php echo _l('fleet_total'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php $n = 0; foreach ($items as $li) : $n++; ?>
            <tr>
                <td width="7%" style="text-align:center;"><?php echo $n; ?></td>
                <td width="47%"><?php echo html_escape($li['item_name']); ?><?php echo $li['item_reference'] ? ' (' . html_escape($li['item_reference']) . ')' : ''; ?></td>
                <td width="13%" style="text-align:center;"><?php echo (int) $li['quantity']; ?></td>
                <td width="16%" style="text-align:right;"><?php echo app_format_money($li['unit_price'], $bc); ?></td>
                <td width="17%" style="text-align:right;"><?php echo app_format_money($li['total_price'], $bc); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<table style="width:100%; font-size:12px;">
    <tr>
        <td style="width:60%;"></td>
        <td style="width:25%; text-align:right; color:#777777;"><?php echo _l('fleet_total'); ?></td>
        <td style="width:15%; text-align:right; font-weight:bold;"><?php echo app_format_money($order->total_price, $bc); ?></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align:right; color:#777777;"><?php echo _l('fleet_paid_amount'); ?></td>
        <td style="text-align:right;"><?php echo app_format_money($paid, $bc); ?></td>
    </tr>
    <tr>
        <td></td>
        <td style="text-align:right; color:#777777;"><?php echo _l('fleet_remaining'); ?></td>
        <td style="text-align:right; font-weight:bold;"><?php echo app_format_money($remaining, $bc); ?></td>
    </tr>
</table>

<?php if (!empty($payments)) : ?>
    <br>
    <span style="font-size:11px; font-weight:bold;"><?php echo _l('fleet_payments_history'); ?></span>
    <table border="1" cellpadding="5" style="width:100%; font-size:10px; border-collapse:collapse; margin-top:4px;">
        <tr style="background-color:#f0f2f5;">
            <th width="30%" style="text-align:left;"><?php echo _l('fleet_payment_date'); ?></th>
            <th width="45%" style="text-align:left;"><?php echo _l('fleet_payment_mode'); ?></th>
            <th width="25%" style="text-align:right;"><?php echo _l('fleet_payment_amount'); ?></th>
        </tr>
        <?php foreach ($payments as $p) : ?>
            <tr>
                <td><?php echo $p['payment_date'] ? _d($p['payment_date']) : '-'; ?></td>
                <td><?php echo html_escape($p['payment_mode']) ?: '—'; ?></td>
                <td style="text-align:right;"><?php echo app_format_money($p['amount'], $bc); ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php if ($order->notes) : ?>
    <br><br><span style="font-size:10px; color:#777777;"><?php echo nl2br(html_escape($order->notes)); ?></span>
<?php endif; ?>
