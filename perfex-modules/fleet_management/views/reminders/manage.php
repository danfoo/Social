<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')'];
}
$type_options = [];
foreach (fleet_reminder_types() as $t) {
    $type_options[] = ['id' => $t, 'name' => _l('fleet_rtype_' . $t)];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <a href="#" class="btn btn-primary mbot15" onclick="fleet_reminder_modal(); return false;">
                                <i class="fa fa-plus"></i> <?php echo _l('fleet_add_reminder'); ?>
                            </a>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table fleet-dt-table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('fleet_vehicle'); ?></th>
                                        <th><?php echo _l('fleet_type'); ?></th>
                                        <th><?php echo _l('fleet_reminder_title'); ?></th>
                                        <th><?php echo _l('fleet_due_date'); ?></th>
                                        <th><?php echo _l('fleet_status'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reminders as $r) : ?>
                                        <tr>
                                            <td><?php echo html_escape($r['vehicle_name']); ?></td>
                                            <td><?php echo _l('fleet_rtype_' . $r['type']); ?></td>
                                            <td><?php echo html_escape($r['title']); ?></td>
                                            <td><?php echo _d($r['due_date']); ?></td>
                                            <td><?php echo fleet_reminder_due_badge($r['due_date'], $r['notify_days']); ?></td>
                                            <td>
                                                <?php if (staff_can('edit', 'fleet')) : ?>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="fleet_reminder_modal(<?php echo $r['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/reminders/delete/' . $r['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="fleet_reminder_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(admin_url('fleet_management/reminders/save')); ?>
            <input type="hidden" name="id" id="reminder_id" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fleet_reminder'); ?></h4>
            </div>
            <div class="modal-body">
                <?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?>
                <?php echo render_select('type', $type_options, ['id', 'name'], 'fleet_type'); ?>
                <?php echo render_input('title', 'fleet_reminder_title', ''); ?>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('due_date', 'fleet_due_date', ''); ?></div>
                    <div class="col-md-6"><?php echo render_input('notify_days', 'fleet_notify_days', 7, 'number'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('cost', 'fleet_cost', '', 'number'); ?></div>
                    <div class="col-md-6"><?php echo render_input('provider', 'fleet_provider', ''); ?></div>
                </div>
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
$(function() {
    if ($.fn.DataTable) {
        $('.fleet-dt-table').DataTable({ "order": [] });
    }
});
function fleet_reminder_modal(id) {
    var modal = $('#fleet_reminder_modal');
    modal.find('form')[0].reset();
    $('#reminder_id').val('');
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/reminders/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#reminder_id').val(rec.id);
            modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
            modal.find('[name="type"]').val(rec.type);
            modal.find('[name="title"]').val(rec.title);
            modal.find('[name="notify_days"]').val(rec.notify_days);
            modal.find('[name="cost"]').val(rec.cost);
            modal.find('[name="provider"]').val(rec.provider);
            modal.find('[name="description"]').val(rec.description);
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
