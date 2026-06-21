<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$bc = get_base_currency();

$vehicle_options = [['id' => '', 'name' => '—']];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')'];
}
$driver_options = [['id' => '', 'name' => '—']];
foreach ($drivers as $d) {
    $driver_options[] = ['id' => $d['staffid'], 'name' => $d['full_name']];
}
$client_options = [['id' => '', 'name' => '—']];
foreach ($clients as $c) {
    $client_options[] = ['id' => $c['userid'], 'name' => $c['company']];
}
$type_options = [];
foreach (fleet_fine_types() as $t) {
    $type_options[] = ['id' => $t, 'name' => _l('fleet_ftype_' . $t)];
}
$status_options = [];
foreach (['pending', 'paid', 'contested', 'cancelled'] as $s) {
    $status_options[] = ['id' => $s, 'name' => _l('fleet_fstatus_' . $s)];
}

$total_amount = 0;
$unpaid       = 0;
foreach ($fines as $f) {
    if ($f['status'] !== 'cancelled') {
        $total_amount += (float) $f['amount'];
        if (!$f['paid']) {
            $unpaid += (float) $f['amount'];
        }
    }
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-gavel text-info"></i> <?php echo _l('fleet_fines'); ?></h3>
            <div class="fleet-tools">
                <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                <a href="<?php echo admin_url('fleet_management/fines/export'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="#" class="btn btn-primary btn-sm" onclick="fleet_fine_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_fine'); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4 col-sm-4"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#6571ff;"><i class="fa fa-gavel"></i></div>
                <div><h2><?php echo count($fines); ?></h2><span><?php echo _l('fleet_fines'); ?></span></div>
            </div></div></div>
            <div class="col-md-4 col-sm-4"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#03c3ec;"><i class="fa fa-money"></i></div>
                <div><h2><?php echo app_format_money($total_amount, $bc); ?></h2><span><?php echo _l('fleet_total'); ?></span></div>
            </div></div></div>
            <div class="col-md-4 col-sm-4"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#ff3e1d;"><i class="fa fa-exclamation-circle"></i></div>
                <div><h2><?php echo app_format_money($unpaid, $bc); ?></h2><span><?php echo _l('fleet_unpaid'); ?></span></div>
            </div></div></div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s"><div class="panel-body">
                    <div class="table-responsive">
                        <table class="table fleet-list">
                            <thead>
                                <tr>
                                    <th><?php echo _l('fleet_fine_number'); ?></th>
                                    <th><?php echo _l('fleet_fine_type'); ?></th>
                                    <th><?php echo _l('fleet_date'); ?></th>
                                    <th><?php echo _l('fleet_vehicle'); ?></th>
                                    <th><?php echo _l('fleet_driver'); ?></th>
                                    <th><?php echo _l('fleet_client'); ?></th>
                                    <th><?php echo _l('fleet_amount'); ?></th>
                                    <th><?php echo _l('fleet_status'); ?></th>
                                    <th class="text-right"><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($fines as $f) : ?>
                                    <tr>
                                        <td class="bold"><?php echo html_escape($f['fine_number']) ?: ('#' . $f['id']); ?></td>
                                        <td><?php echo _l('fleet_ftype_' . $f['type']); ?></td>
                                        <td><?php echo $f['fine_date'] ? _d($f['fine_date']) : '-'; ?></td>
                                        <td><?php echo html_escape($f['vehicle_name']); ?> <span class="text-muted"><?php echo html_escape($f['vehicle_plate']); ?></span></td>
                                        <td><?php echo html_escape($f['driver_name']); ?></td>
                                        <td><?php echo html_escape($f['client_name']) ?: '—'; ?></td>
                                        <td><?php echo app_format_money($f['amount'], $bc); ?></td>
                                        <td><?php echo fleet_fine_status_badge($f['status']); ?> <?php echo $f['paid'] ? '<span class="label label-success">' . _l('fleet_paid') . '</span>' : ''; ?></td>
                                        <td class="text-right">
                                            <?php if (!empty($f['invoice_id'])) : ?>
                                                <a href="<?php echo admin_url('invoices/list_invoices/' . $f['invoice_id']); ?>" class="btn btn-default btn-icon btn-sm" title="<?php echo _l('invoice'); ?>"><i class="fa fa-file-text"></i></a>
                                            <?php elseif (!empty($f['clientid']) && (float) $f['amount'] > 0 && staff_can('create', 'fleet') && has_permission('invoices', '', 'create')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/fines/create_invoice/' . $f['id']); ?>" class="btn btn-success btn-sm" title="<?php echo _l('fleet_rebill_client'); ?>"><i class="fa fa-file-text"></i> <?php echo _l('fleet_rebill'); ?></a>
                                            <?php endif; ?>
                                            <?php if (staff_can('edit', 'fleet')) : ?>
                                                <a href="#" class="btn btn-default btn-icon btn-sm" onclick="fleet_fine_modal(<?php echo $f['id']; ?>); return false;"><i class="fa fa-pencil-square"></i></a>
                                            <?php endif; ?>
                                            <?php if (staff_can('delete', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/fines/delete/' . $f['id']); ?>" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-remove"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($fines)) : ?><tr><td colspan="9" class="text-center text-muted"><?php echo _l('fleet_no_fines'); ?></td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fleet_fine_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(admin_url('fleet_management/fines/save')); ?>
            <input type="hidden" name="id" id="fine_id" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fleet_fine'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('fine_number', 'fleet_fine_number', ''); ?></div>
                    <div class="col-md-6"><?php echo render_select('type', $type_options, ['id', 'name'], 'fleet_fine_type'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('fine_date', 'fleet_date', _d(date('Y-m-d'))); ?></div>
                    <div class="col-md-6"><?php echo render_input('amount', 'fleet_amount', '', 'number'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?></div>
                    <div class="col-md-6"><?php echo render_select('driver_id', $driver_options, ['id', 'name'], 'fleet_driver'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_select('clientid', $client_options, ['id', 'name'], 'fleet_rebill_client'); ?></div>
                    <div class="col-md-6"><?php echo render_select('status', $status_options, ['id', 'name'], 'fleet_status'); ?></div>
                </div>
                <?php echo render_input('location', 'fleet_location', ''); ?>
                <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="paid" id="fine_paid" value="1">
                    <label for="fine_paid"><?php echo _l('fleet_paid'); ?></label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
function fleet_fine_modal(id) {
    var modal = $('#fleet_fine_modal');
    modal.find('form')[0].reset();
    $('#fine_id').val('');
    $('#fine_paid').prop('checked', false);
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/fines/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#fine_id').val(rec.id);
            modal.find('[name="fine_number"]').val(rec.fine_number);
            modal.find('[name="type"]').val(rec.type);
            modal.find('[name="fine_date"]').val(rec.fine_date_display || '');
            modal.find('[name="amount"]').val(rec.amount);
            modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
            modal.find('[name="driver_id"]').val(rec.driver_id);
            modal.find('[name="clientid"]').val(rec.clientid);
            modal.find('[name="status"]').val(rec.status);
            modal.find('[name="location"]').val(rec.location);
            modal.find('[name="notes"]').val(rec.notes);
            $('#fine_paid').prop('checked', rec.paid == 1);
            if (modal.find('.selectpicker').length) {
                modal.find('.selectpicker').selectpicker('refresh');
            }
        });
    }
    modal.modal('show');
}
</script>
</body>
</html>
