<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')'];
}
$driver_options = [];
foreach ($drivers as $d) {
    $driver_options[] = ['id' => $d['staffid'], 'name' => $d['full_name']];
}
$supplier_options = [];
foreach ($suppliers as $s) {
    $supplier_options[] = ['id' => $s['id'], 'name' => $s['name']];
}
$fuel_options = [];
foreach (fleet_fuel_types() as $t) {
    $fuel_options[] = ['id' => $t, 'name' => _l('fleet_fuel_' . $t)];
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
                            <div class="col-md-4 text-center">
                                <h2 class="bold no-margin"><?php echo (int) $stats->entries; ?></h2>
                                <span class="text-muted"><?php echo _l('fleet_fuel_entries'); ?></span>
                            </div>
                            <div class="col-md-4 text-center">
                                <h2 class="bold no-margin"><?php echo (float) $stats->total_liters; ?> L</h2>
                                <span class="text-muted"><?php echo _l('fleet_fuel_total_liters'); ?></span>
                            </div>
                            <div class="col-md-4 text-center">
                                <h2 class="bold no-margin"><?php echo app_format_money($stats->total_cost, get_base_currency()); ?></h2>
                                <span class="text-muted"><?php echo _l('fleet_fuel_total_cost'); ?></span>
                            </div>
                        </div>
                        <hr />
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <a href="#" class="btn btn-primary mbot15" onclick="fleet_fuel_modal(); return false;">
                                <i class="fa fa-plus"></i> <?php echo _l('fleet_add_fuel'); ?>
                            </a>
                        <?php endif; ?>
                        <div class="text-right mbot15">
                            <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                        </div>
                        <div class="table-responsive">
                            <table class="table fleet-list">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('fleet_date'); ?></th>
                                        <th><?php echo _l('fleet_vehicle'); ?></th>
                                        <th><?php echo _l('fleet_driver'); ?></th>
                                        <th><?php echo _l('fleet_odometer'); ?></th>
                                        <th><?php echo _l('fleet_liters'); ?></th>
                                        <th><?php echo _l('fleet_price_per_liter'); ?></th>
                                        <th><?php echo _l('fleet_total'); ?></th>
                                        <th><?php echo _l('fleet_station'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $f) : ?>
                                        <tr>
                                            <td><?php echo _d($f['date']); ?></td>
                                            <td><?php echo html_escape($f['vehicle_name']); ?></td>
                                            <td><?php echo html_escape($f['driver_name']); ?></td>
                                            <td><?php echo $f['odometer'] ? (int) $f['odometer'] . ' km' : '-'; ?></td>
                                            <td><?php echo (float) $f['liters']; ?> L</td>
                                            <td><?php echo $f['price_per_liter'] ? app_format_money($f['price_per_liter'], get_base_currency()) : '-'; ?></td>
                                            <td><?php echo app_format_money($f['total_cost'], get_base_currency()); ?></td>
                                            <td><?php echo html_escape($f['supplier_name']); ?></td>
                                            <td>
                                                <?php if (staff_can('edit', 'fleet')) : ?>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="fleet_fuel_modal(<?php echo $f['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/fuel/delete/' . $f['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="fleet_fuel_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(admin_url('fleet_management/fuel/save')); ?>
            <input type="hidden" name="id" id="fuel_id" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fleet_fuel_log'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6"><?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?></div>
                    <div class="col-md-6"><?php echo render_select('driver_id', $driver_options, ['id', 'name'], 'fleet_driver'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('date', 'fleet_date', _d(date('Y-m-d'))); ?></div>
                    <div class="col-md-6"><?php echo render_input('odometer', 'fleet_odometer', '', 'number'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><?php echo render_input('liters', 'fleet_liters', '', 'number'); ?></div>
                    <div class="col-md-4"><?php echo render_input('price_per_liter', 'fleet_price_per_liter', '', 'number'); ?></div>
                    <div class="col-md-4"><?php echo render_input('total_cost', 'fleet_total_cost', '', 'number'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_select('fuel_type', $fuel_options, ['id', 'name'], 'fleet_fuel_type'); ?></div>
                    <div class="col-md-6"><?php echo render_select('supplier_id', $supplier_options, ['id', 'name'], 'fleet_station'); ?></div>
                </div>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="full_tank" id="fuel_full_tank" value="1" checked>
                    <label for="fuel_full_tank"><?php echo _l('fleet_full_tank'); ?></label>
                </div>
                <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
                <p class="text-muted"><small><?php echo _l('fleet_fuel_total_hint'); ?></small></p>
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
$(function() {
    var $f = $('#fleet_fuel_modal');

    // Mark a field as manually edited so it is not overwritten by auto-compute.
    $f.on('input', '[name="liters"]', function() { $(this).data('touched', true); fleet_fuel_recompute(); });
    $f.on('input', '[name="price_per_liter"]', function() { $(this).data('touched', true); fleet_fuel_recompute(); });
    $f.on('input', '[name="total_cost"]', function() { $(this).data('touched', true); fleet_fuel_recompute(); });

    // Fill whichever of liters / price / total the user did not type, so the
    // liters value can be derived from "amount paid" + "price per liter".
    window.fleet_fuel_recompute = function() {
        var lF = $f.find('[name="liters"]'), pF = $f.find('[name="price_per_liter"]'), tF = $f.find('[name="total_cost"]');
        var l = parseFloat(lF.val()), p = parseFloat(pF.val()), t = parseFloat(tF.val());

        if (!lF.data('touched') && p > 0 && !isNaN(t)) { lF.val((t / p).toFixed(2)); return; }
        if (!tF.data('touched') && !isNaN(l) && !isNaN(p)) { tF.val((l * p).toFixed(2)); return; }
        if (!pF.data('touched') && l > 0 && !isNaN(t)) { pF.val((t / l).toFixed(3)); return; }
    };
});
function fleet_fuel_modal(id) {
    var modal = $('#fleet_fuel_modal');
    modal.find('form')[0].reset();
    $('#fuel_id').val('');
    $('#fuel_full_tank').prop('checked', true);
    modal.find('[name="liters"], [name="price_per_liter"], [name="total_cost"]').data('touched', false);
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/fuel/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#fuel_id').val(rec.id);
            modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
            modal.find('[name="driver_id"]').val(rec.driver_id);
            modal.find('[name="odometer"]').val(rec.odometer);
            modal.find('[name="liters"]').val(rec.liters).data('touched', true);
            modal.find('[name="price_per_liter"]').val(rec.price_per_liter).data('touched', true);
            modal.find('[name="total_cost"]').val(rec.total_cost).data('touched', true);
            modal.find('[name="fuel_type"]').val(rec.fuel_type);
            modal.find('[name="supplier_id"]').val(rec.supplier_id);
            modal.find('[name="notes"]').val(rec.notes);
            $('#fuel_full_tank').prop('checked', rec.full_tank == 1);
            if (modal.find('.selectpicker').length) {
                modal.find('.selectpicker').selectpicker('refresh');
            }
        });
    }
    modal.modal('show');
}
<?php if ($this->input->get('open')) : ?>
$(function() {
    fleet_fuel_modal();
    <?php if ($this->input->get('vehicle_id')) : ?>
    var pm = $('#fleet_fuel_modal');
    pm.find('[name="vehicle_id"]').val('<?php echo (int) $this->input->get('vehicle_id'); ?>');
    if (pm.find('.selectpicker').length) { pm.find('.selectpicker').selectpicker('refresh'); }
    <?php endif; ?>
});
<?php endif; ?>
</script>
</body>
</html>
