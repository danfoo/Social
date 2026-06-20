<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')'];
}
$type_options = [];
foreach (fleet_maintenance_types() as $t) {
    $type_options[] = ['id' => $t, 'name' => _l('fleet_mtype_' . $t)];
}
$supplier_options = [];
foreach ($suppliers as $sup) {
    $supplier_options[] = ['id' => $sup['id'], 'name' => $sup['name']];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-wrench text-info"></i> <?php echo _l('fleet_maintenance'); ?></h3>
            <div class="fleet-tools">
                <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                <a href="<?php echo admin_url('fleet_management/maintenance/export'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="#" class="btn btn-primary btn-sm" onclick="fleet_maintenance_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_maintenance'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table fleet-list">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('fleet_vehicle'); ?></th>
                                        <th><?php echo _l('fleet_type'); ?></th>
                                        <th><?php echo _l('fleet_service_date'); ?></th>
                                        <th><?php echo _l('fleet_cost'); ?></th>
                                        <th><?php echo _l('fleet_odometer'); ?></th>
                                        <th><?php echo _l('fleet_next_service_date'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($maintenance as $m) : ?>
                                        <tr>
                                            <td><?php echo html_escape($m['vehicle_name']); ?></td>
                                            <td><?php echo _l('fleet_mtype_' . $m['type']); ?></td>
                                            <td><?php echo $m['service_date'] ? _d($m['service_date']) : '-'; ?></td>
                                            <td><?php echo app_format_money($m['cost'], get_base_currency()); ?></td>
                                            <td><?php echo $m['odometer'] ? (int) $m['odometer'] . ' km' : '-'; ?></td>
                                            <td><?php echo $m['next_service_date'] ? _d($m['next_service_date']) : '-'; ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('fleet_management/maintenance/files/' . $m['id']); ?>" class="btn btn-info btn-icon" title="<?php echo _l('fleet_maintenance_photos'); ?>"><i class="fa fa-camera"></i></a>
                                                <?php if (staff_can('edit', 'fleet')) : ?>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="fleet_maintenance_modal(<?php echo $m['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/maintenance/delete/' . $m['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="fleet_maintenance_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(admin_url('fleet_management/maintenance/save')); ?>
            <input type="hidden" name="id" id="maintenance_id" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fleet_maintenance_record'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?>
                <?php echo render_select('type', $type_options, ['id', 'name'], 'fleet_type'); ?>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('service_date', 'fleet_service_date', _d(date('Y-m-d'))); ?></div>
                    <div class="col-md-6"><?php echo render_input('cost', 'fleet_cost', 0, 'number'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('odometer', 'fleet_odometer', '', 'number'); ?></div>
                    <div class="col-md-6"><?php echo render_select('supplier_id', $supplier_options, ['id', 'name'], 'fleet_provider'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('next_service_date', 'fleet_next_service_date', ''); ?></div>
                    <div class="col-md-6"><?php echo render_input('next_service_odometer', 'fleet_next_service_odometer', '', 'number'); ?></div>
                </div>
                <?php echo render_textarea('parts', 'fleet_parts', '', ['placeholder' => _l('fleet_parts_hint')]); ?>
                <?php echo render_textarea('description', 'fleet_description', ''); ?>
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
function fleet_maintenance_modal(id) {
    var modal = $('#fleet_maintenance_modal');
    modal.find('form')[0].reset();
    $('#maintenance_id').val('');
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/maintenance/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#maintenance_id').val(rec.id);
            modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
            modal.find('[name="type"]').val(rec.type);
            modal.find('[name="cost"]').val(rec.cost);
            modal.find('[name="odometer"]').val(rec.odometer);
            modal.find('[name="supplier_id"]').val(rec.supplier_id);
            modal.find('[name="parts"]').val(rec.parts);
            modal.find('[name="next_service_odometer"]').val(rec.next_service_odometer);
            modal.find('[name="description"]').val(rec.description);
            if (modal.find('[name="vehicle_id"]').hasClass('selectpicker')) {
                modal.find('.selectpicker').selectpicker('refresh');
            }
        });
    }
    modal.modal('show');
}
<?php if ($this->input->get('open')) : ?>
$(function() {
    fleet_maintenance_modal();
    <?php if ($this->input->get('vehicle_id')) : ?>
    var pm = $('#fleet_maintenance_modal');
    pm.find('[name="vehicle_id"]').val('<?php echo (int) $this->input->get('vehicle_id'); ?>');
    if (pm.find('.selectpicker').length) { pm.find('.selectpicker').selectpicker('refresh'); }
    <?php endif; ?>
});
<?php endif; ?>
</script>
</body>
</html>
