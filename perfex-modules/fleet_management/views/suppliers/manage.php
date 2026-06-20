<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$type_options = [];
foreach (fleet_supplier_types() as $t) {
    $type_options[] = ['id' => $t, 'name' => _l('fleet_stype_' . $t)];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-truck text-info"></i> <?php echo _l('fleet_suppliers'); ?></h3>
            <div class="fleet-tools">
                <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                <a href="<?php echo admin_url('fleet_management/suppliers/export'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="#" class="btn btn-primary btn-sm" onclick="fleet_supplier_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_supplier'); ?></a>
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
                                        <th><?php echo _l('fleet_supplier_name'); ?></th>
                                        <th><?php echo _l('fleet_type'); ?></th>
                                        <th><?php echo _l('fleet_contact_name'); ?></th>
                                        <th><?php echo _l('fleet_phone'); ?></th>
                                        <th><?php echo _l('email'); ?></th>
                                        <th><?php echo _l('status'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($suppliers as $s) : ?>
                                        <tr>
                                            <td><a href="<?php echo admin_url('fleet_management/suppliers/view/' . $s['id']); ?>" class="bold"><?php echo html_escape($s['name']); ?></a></td>
                                            <td><?php echo _l('fleet_stype_' . $s['type']); ?></td>
                                            <td><?php echo html_escape($s['contact_name']); ?></td>
                                            <td><?php echo html_escape($s['phone']); ?></td>
                                            <td><?php echo html_escape($s['email']); ?></td>
                                            <td><?php echo $s['active'] ? '<span class="label label-success">' . _l('active') . '</span>' : '<span class="label label-default">' . _l('inactive') . '</span>'; ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('fleet_management/suppliers/view/' . $s['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-eye"></i></a>
                                                <?php if (staff_can('edit', 'fleet')) : ?>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="fleet_supplier_modal(<?php echo $s['id']; ?>); return false;"><i class="fa fa-pencil-square-o"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/suppliers/delete/' . $s['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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

<div class="modal fade" id="fleet_supplier_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open(admin_url('fleet_management/suppliers/save')); ?>
            <input type="hidden" name="id" id="supplier_id" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fleet_supplier'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7"><?php echo render_input('name', 'fleet_supplier_name', ''); ?></div>
                    <div class="col-md-5"><?php echo render_select('type', $type_options, ['id', 'name'], 'fleet_type'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('contact_name', 'fleet_contact_name', ''); ?></div>
                    <div class="col-md-6"><?php echo render_input('phone', 'fleet_phone', ''); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('email', 'email', '', 'email'); ?></div>
                    <div class="col-md-6"><?php echo render_input('website', 'fleet_website', ''); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_input('vat', 'fleet_vat', ''); ?></div>
                </div>
                <?php echo render_textarea('address', 'fleet_address', ''); ?>
                <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="active" id="supplier_active" value="1" checked>
                    <label for="supplier_active"><?php echo _l('active'); ?></label>
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
function fleet_supplier_modal(id) {
    var modal = $('#fleet_supplier_modal');
    modal.find('form')[0].reset();
    $('#supplier_id').val('');
    $('#supplier_active').prop('checked', true);
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/suppliers/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#supplier_id').val(rec.id);
            modal.find('[name="name"]').val(rec.name);
            modal.find('[name="type"]').val(rec.type);
            modal.find('[name="contact_name"]').val(rec.contact_name);
            modal.find('[name="phone"]').val(rec.phone);
            modal.find('[name="email"]').val(rec.email);
            modal.find('[name="website"]').val(rec.website);
            modal.find('[name="vat"]').val(rec.vat);
            modal.find('[name="address"]').val(rec.address);
            modal.find('[name="notes"]').val(rec.notes);
            $('#supplier_active').prop('checked', rec.active == 1);
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
