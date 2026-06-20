<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')'];
}
$supplier_options = [];
foreach ($suppliers as $s) {
    $supplier_options[] = ['id' => $s['id'], 'name' => $s['name']];
}
$status_options = [];
foreach (fleet_part_statuses() as $st) {
    $status_options[] = ['id' => $st, 'name' => _l('fleet_pstatus_' . $st)];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row mbot15">
                            <div class="col-md-6 text-center">
                                <h2 class="bold no-margin"><?php echo (int) $stats->entries; ?></h2>
                                <span class="text-muted"><?php echo _l('fleet_parts_count'); ?></span>
                            </div>
                            <div class="col-md-6 text-center">
                                <h2 class="bold no-margin"><?php echo app_format_money($stats->total_cost, get_base_currency()); ?></h2>
                                <span class="text-muted"><?php echo _l('fleet_parts_total_cost'); ?></span>
                            </div>
                        </div>
                        <hr />
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <a href="#" class="btn btn-primary mbot15" onclick="fleet_part_modal(); return false;">
                                <i class="fa fa-plus"></i> <?php echo _l('fleet_add_part'); ?>
                            </a>
                        <?php endif; ?>
                        <div class="text-right mbot15">
                            <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table fleet-list">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('fleet_part_name'); ?></th>
                                        <th><?php echo _l('fleet_reference'); ?></th>
                                        <th><?php echo _l('fleet_vehicle'); ?></th>
                                        <th><?php echo _l('fleet_supplier'); ?></th>
                                        <th><?php echo _l('fleet_quantity'); ?></th>
                                        <th><?php echo _l('fleet_unit_price'); ?></th>
                                        <th><?php echo _l('fleet_total'); ?></th>
                                        <th><?php echo _l('fleet_purchase_date'); ?></th>
                                        <th><?php echo _l('fleet_status'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($parts as $p) : ?>
                                        <tr>
                                            <td><?php echo html_escape($p['name']); ?></td>
                                            <td><?php echo html_escape($p['reference']); ?></td>
                                            <td><?php echo $p['vehicle_name'] ? html_escape($p['vehicle_name']) : '<span class="text-muted">—</span>'; ?></td>
                                            <td><?php echo $p['supplier_name'] ? html_escape($p['supplier_name']) : '<span class="text-muted">—</span>'; ?></td>
                                            <td><?php echo (int) $p['quantity']; ?></td>
                                            <td><?php echo app_format_money($p['unit_price'], get_base_currency()); ?></td>
                                            <td><?php echo app_format_money($p['total_price'], get_base_currency()); ?></td>
                                            <td><?php echo $p['purchase_date'] ? _d($p['purchase_date']) : '-'; ?></td>
                                            <td><span class="label label-default"><?php echo _l('fleet_pstatus_' . $p['status']); ?></span></td>
                                            <td>
                                                <?php if (staff_can('edit', 'fleet')) : ?>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="fleet_part_modal(<?php echo $p['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/parts/delete/' . $p['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fleet_part_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(admin_url('fleet_management/parts/save')); ?>
            <input type="hidden" name="id" id="part_id" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fleet_part'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-8"><?php echo render_input('name', 'fleet_part_name', ''); ?></div>
                    <div class="col-md-4"><?php echo render_input('reference', 'fleet_reference', ''); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?></div>
                    <div class="col-md-6"><?php echo render_select('supplier_id', $supplier_options, ['id', 'name'], 'fleet_supplier'); ?></div>
                </div>
                <div class="form-group">
                    <label class="control-label" for="part_maintenance_id"><?php echo _l('fleet_link_maintenance'); ?></label>
                    <select name="maintenance_id" id="part_maintenance_id" class="form-control">
                        <option value=""><?php echo _l('fleet_no_link'); ?></option>
                        <?php foreach ($maintenances as $mm) : ?>
                            <option value="<?php echo $mm['id']; ?>" data-vehicle="<?php echo (int) $mm['vehicle_id']; ?>"><?php echo html_escape($mm['vehicle_plate'] . ' · ' . _l('fleet_mtype_' . $mm['type']) . ' · ' . ($mm['service_date'] ? _d($mm['service_date']) : '')); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-4"><?php echo render_input('quantity', 'fleet_quantity', 1, 'number'); ?></div>
                    <div class="col-md-4"><?php echo render_input('unit_price', 'fleet_unit_price', '', 'number'); ?></div>
                    <div class="col-md-4"><?php echo render_input('total_price', 'fleet_total', '', 'number', ['readonly' => true]); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('purchase_date', 'fleet_purchase_date', _d(date('Y-m-d'))); ?></div>
                    <div class="col-md-6"><?php echo render_select('status', $status_options, ['id', 'name'], 'fleet_status', 'installed'); ?></div>
                </div>
                <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
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
var fleetPartAllMaint = null;

function fleetPartFilterMaintenance() {
    var modal = $('#fleet_part_modal');
    var $m = $('#part_maintenance_id');
    if (fleetPartAllMaint === null) { fleetPartAllMaint = $m.find('option').clone(); }
    var vid = modal.find('[name="vehicle_id"]').val();
    var current = $m.val();
    $m.empty().append('<option value=""><?php echo _l('fleet_no_link'); ?></option>');
    fleetPartAllMaint.each(function() {
        var o = $(this);
        if (o.val() === '') { return; }
        if (!vid || String(o.data('vehicle')) === String(vid)) { $m.append(o.clone()); }
    });
    $m.val(current);
}

$(function() {
    $('#fleet_part_modal').on('input', '[name="quantity"], [name="unit_price"]', function() {
        var q = parseFloat($('#fleet_part_modal [name="quantity"]').val());
        var u = parseFloat($('#fleet_part_modal [name="unit_price"]').val());
        if (!isNaN(q) && !isNaN(u)) {
            $('#fleet_part_modal [name="total_price"]').val((q * u).toFixed(2));
        }
    });
    $('#fleet_part_modal').on('change', '[name="vehicle_id"]', fleetPartFilterMaintenance);
});

function fleet_part_modal(id) {
    var modal = $('#fleet_part_modal');
    modal.find('form')[0].reset();
    $('#part_id').val('');
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/parts/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#part_id').val(rec.id);
            modal.find('[name="name"]').val(rec.name);
            modal.find('[name="reference"]').val(rec.reference);
            modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
            modal.find('[name="supplier_id"]').val(rec.supplier_id);
            modal.find('[name="quantity"]').val(rec.quantity);
            modal.find('[name="unit_price"]').val(rec.unit_price);
            modal.find('[name="total_price"]').val(rec.total_price);
            modal.find('[name="status"]').val(rec.status);
            modal.find('[name="notes"]').val(rec.notes);
            fleetPartFilterMaintenance();
            $('#part_maintenance_id').val(rec.maintenance_id);
            if (modal.find('.selectpicker').length) {
                modal.find('.selectpicker').selectpicker('refresh');
            }
        });
    } else {
        fleetPartFilterMaintenance();
    }
    modal.modal('show');
}
<?php if ($this->input->get('open')) : ?>
$(function() {
    fleet_part_modal();
    <?php if ($this->input->get('vehicle_id')) : ?>
    var pm = $('#fleet_part_modal');
    pm.find('[name="vehicle_id"]').val('<?php echo (int) $this->input->get('vehicle_id'); ?>');
    if (pm.find('.selectpicker').length) { pm.find('.selectpicker').selectpicker('refresh'); }
    fleetPartFilterMaintenance();
    <?php endif; ?>
});
<?php endif; ?>
</script>
</body>
</html>
