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
                        <div class="table-responsive">
                            <table class="table fleet-dt-table">
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
    if ($.fn.DataTable) {
        $('.fleet-dt-table').DataTable({ "order": [] });
    }
    // Auto-compute total cost from liters * unit price when total is left blank.
    $('#fleet_fuel_modal').on('input', '[name="liters"], [name="price_per_liter"]', function() {
        var l = parseFloat($('#fleet_fuel_modal [name="liters"]').val());
        var p = parseFloat($('#fleet_fuel_modal [name="price_per_liter"]').val());
        var totalField = $('#fleet_fuel_modal [name="total_cost"]');
        if (!totalField.data('touched') && !isNaN(l) && !isNaN(p)) {
            totalField.val((l * p).toFixed(2));
        }
    });
    $('#fleet_fuel_modal [name="total_cost"]').on('input', function() { $(this).data('touched', true); });
});
function fleet_fuel_modal(id) {
    var modal = $('#fleet_fuel_modal');
    modal.find('form')[0].reset();
    $('#fuel_id').val('');
    $('#fuel_full_tank').prop('checked', true);
    modal.find('[name="total_cost"]').data('touched', false);
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/fuel/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#fuel_id').val(rec.id);
            modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
            modal.find('[name="driver_id"]').val(rec.driver_id);
            modal.find('[name="odometer"]').val(rec.odometer);
            modal.find('[name="liters"]').val(rec.liters);
            modal.find('[name="price_per_liter"]').val(rec.price_per_liter);
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
</script>
</body>
</html>
