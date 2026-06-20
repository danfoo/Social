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
            <span style="font-size:20px; font-weight:bold; color:#4f5fff;"><?php echo _l('fleet_purchase_order'); ?></span><br>
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
        <tr style="background-color:#f2f3f7;">
            <th style="text-align:left;"><?php echo _l('fleet_part'); ?></th>
            <th style="text-align:center;"><?php echo _l('fleet_quantity'); ?></th>
            <th style="text-align:right;"><?php echo _l('fleet_unit_price'); ?></th>
            <th style="text-align:right;"><?php echo _l('fleet_total'); ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><?php echo html_escape($order->item_name); ?><?php echo $order->item_reference ? ' (' . html_escape($order->item_reference) . ')' : ''; ?></td>
            <td style="text-align:center;"><?php echo (int) $order->quantity; ?></td>
            <td style="text-align:right;"><?php echo app_format_money($order->unit_price, $bc); ?></td>
            <td style="text-align:right;"><?php echo app_format_money($order->total_price, $bc); ?></td>
        </tr>
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

<?php if ($order->notes) : ?>
    <br><br><span style="font-size:10px; color:#777777;"><?php echo nl2br(html_escape($order->notes)); ?></span>
<?php endif; ?>
