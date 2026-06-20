<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$bc = get_base_currency();
function fleet_pay_badge($total, $paid)
{
    if ($total > 0 && $paid >= $total - 0.001) {
        return '<span class="label label-success">' . _l('fleet_paid') . '</span>';
    }
    if ($paid > 0) {
        return '<span class="label label-warning">' . _l('fleet_partial') . '</span>';
    }

    return '<span class="label label-default">' . _l('fleet_unpaid') . '</span>';
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="mbot15 clearfix">
            <a href="<?php echo admin_url('fleet_management/suppliers'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> <?php echo _l('fleet_suppliers'); ?></a>
            <div class="btn-group fleet-period pull-right" style="margin-left:8px;">
                <?php
                $periods = ['month' => _l('fleet_period_month'), 'quarter' => _l('fleet_period_quarter'), 'year' => _l('fleet_period_year'), 'all' => _l('fleet_period_all')];
                foreach ($periods as $key => $label) :
                    $active = $period === $key ? 'btn-primary' : 'btn-default';
                    ?>
                    <a href="<?php echo admin_url('fleet_management/suppliers/view/' . $supplier->id . '?period=' . $key); ?>" class="btn btn-sm <?php echo $active; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo admin_url('fleet_management/suppliers/export_ledger/' . $supplier->id . '?period=' . $period); ?>" class="btn btn-default btn-sm pull-right"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body">
                    <h3 class="bold no-margin"><?php echo html_escape($supplier->name); ?></h3>
                    <p class="text-muted"><?php echo _l('fleet_stype_' . $supplier->type); ?></p>
                    <table class="table table-borderless no-margin">
                        <tr><td class="bold"><?php echo _l('fleet_contact_name'); ?></td><td><?php echo html_escape($supplier->contact_name); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_phone'); ?></td><td><?php echo html_escape($supplier->phone); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('email'); ?></td><td><?php echo html_escape($supplier->email); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_vat'); ?></td><td><?php echo html_escape($supplier->vat); ?></td></tr>
                    </table>
                </div></div>
            </div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4"><div class="panel_s"><div class="panel-body fleet-stat">
                        <div class="ic" style="background:#6571ff;"><i class="fa fa-money"></i></div>
                        <div><h2><?php echo app_format_money($summary->total, $bc); ?></h2><span><?php echo _l('fleet_acc_total'); ?></span></div>
                    </div></div></div>
                    <div class="col-md-4"><div class="panel_s"><div class="panel-body fleet-stat">
                        <div class="ic" style="background:#71dd37;"><i class="fa fa-check"></i></div>
                        <div><h2><?php echo app_format_money($summary->paid, $bc); ?></h2><span><?php echo _l('fleet_acc_paid'); ?></span></div>
                    </div></div></div>
                    <div class="col-md-4"><div class="panel_s"><div class="panel-body fleet-stat">
                        <div class="ic" style="background:#ff3e1d;"><i class="fa fa-exclamation-circle"></i></div>
                        <div><h2><?php echo app_format_money($summary->unpaid, $bc); ?></h2><span><?php echo _l('fleet_acc_unpaid'); ?></span></div>
                    </div></div></div>
                </div>

                <!-- Part orders -->
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold no-margin"><i class="fa fa-shopping-cart"></i> <?php echo _l('fleet_supplier_orders'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr>
                                <th><?php echo _l('fleet_part'); ?></th>
                                <th><?php echo _l('fleet_total'); ?></th>
                                <th><?php echo _l('fleet_paid_amount'); ?></th>
                                <th><?php echo _l('fleet_remaining'); ?></th>
                                <th><?php echo _l('fleet_payment'); ?></th>
                                <th class="text-right"><?php echo _l('options'); ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($orders as $o) : ?>
                                <tr>
                                    <td class="bold">PO-<?php echo $o['id']; ?> <small class="text-muted"><?php echo html_escape($o['items_summary']); ?></small>
                                        <?php if ($o['invoice_no']) : ?><br><small class="text-muted"><?php echo _l('fleet_supplier_invoice_no'); ?>: <?php echo html_escape($o['invoice_no']); ?></small><?php endif; ?>
                                    </td>
                                    <td><?php echo app_format_money($o['total_price'], $bc); ?></td>
                                    <td class="text-success"><?php echo app_format_money($o['paid_amount'], $bc); ?></td>
                                    <td class="text-danger"><?php echo app_format_money($o['remaining'], $bc); ?></td>
                                    <td><?php echo fleet_pay_badge($o['total_price'], $o['paid_amount']); ?></td>
                                    <td class="text-right">
                                        <a href="<?php echo admin_url('fleet_management/parts/order_pdf/' . $o['id']); ?>" target="_blank" class="btn btn-default btn-icon btn-sm" title="PDF"><i class="fa fa-file-pdf-o"></i></a>
                                        <?php if ($o['remaining'] > 0.001 && staff_can('edit', 'fleet')) : ?>
                                            <a href="#" class="btn btn-success btn-sm" onclick="fleet_pay('fleet_part_orders', <?php echo $o['id']; ?>, <?php echo (float) $o['remaining']; ?>); return false;"><i class="fa fa-credit-card"></i> <?php echo _l('fleet_pay'); ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orders)) : ?><tr><td colspan="6" class="text-center text-muted"><?php echo _l('fleet_no_orders'); ?></td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div></div>

                <!-- Other linked costs -->
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold no-margin"><i class="fa fa-list"></i> <?php echo _l('fleet_supplier_other_costs'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr>
                                <th><?php echo _l('fleet_type'); ?></th>
                                <th><?php echo _l('fleet_vehicle'); ?></th>
                                <th><?php echo _l('fleet_total'); ?></th>
                                <th><?php echo _l('fleet_paid_amount'); ?></th>
                                <th><?php echo _l('fleet_remaining'); ?></th>
                                <th class="text-right"><?php echo _l('options'); ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($costs as $c) : ?>
                                <tr>
                                    <td><?php echo $c['label']; ?></td>
                                    <td><?php echo html_escape($c['vehicle']); ?></td>
                                    <td><?php echo app_format_money($c['amount'], $bc); ?></td>
                                    <td class="text-success"><?php echo app_format_money($c['paid_amount'], $bc); ?></td>
                                    <td class="text-danger"><?php echo app_format_money($c['remaining'], $bc); ?></td>
                                    <td class="text-right">
                                        <?php echo fleet_pay_badge($c['amount'], $c['paid_amount']); ?>
                                        <?php if ($c['remaining'] > 0.001 && staff_can('edit', 'fleet')) : ?>
                                            <a href="#" class="btn btn-success btn-xs" onclick="fleet_pay('<?php echo $c['table']; ?>', <?php echo $c['id']; ?>, <?php echo (float) $c['remaining']; ?>); return false;"><?php echo _l('fleet_pay'); ?></a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($costs)) : ?><tr><td colspan="6" class="text-center text-muted">—</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>

<!-- Payment modal -->
<div class="modal fade" id="fleet_pay_modal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content">
    <?php echo form_open(admin_url('fleet_management/suppliers/pay')); ?>
    <input type="hidden" name="source_table" id="pay_table">
    <input type="hidden" name="source_id" id="pay_source_id">
    <input type="hidden" name="supplier_id" value="<?php echo $supplier->id; ?>">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('fleet_record_payment'); ?></h4></div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6"><?php echo render_input('amount', 'fleet_payment_amount', '', 'number'); ?></div>
            <div class="col-md-6"><?php echo render_date_input('payment_date', 'fleet_payment_date', _d(date('Y-m-d'))); ?></div>
        </div>
        <?php echo render_input('payment_mode', 'fleet_payment_mode', ''); ?>
        <?php echo render_textarea('note', 'fleet_notes', ''); ?>
        <p class="text-muted"><small><?php echo _l('fleet_remaining'); ?> : <span id="pay_remaining" class="bold"></span></small></p>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn btn-primary"><?php echo _l('fleet_record_payment'); ?></button></div>
    <?php echo form_close(); ?>
</div></div></div>

<?php init_tail(); ?>
<script>
function fleet_pay(table, id, remaining) {
    $('#pay_table').val(table);
    $('#pay_source_id').val(id);
    $('#fleet_pay_modal [name="amount"]').val(remaining.toFixed(2));
    $('#pay_remaining').text(remaining.toFixed(2));
    $('#fleet_pay_modal').modal('show');
}
</script>
</body>
</html>
