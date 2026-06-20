<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$bc = get_base_currency();

// Build vehicle options for the accident modal.
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ($v['plate'] ? ' (' . $v['plate'] . ')' : '')];
}

$severities = ['minor', 'moderate', 'severe'];

// Age from date of birth.
$age = '';
if (!empty($driver->date_of_birth) && $driver->date_of_birth !== '0000-00-00') {
    $age = (new DateTime($driver->date_of_birth))->diff(new DateTime('now'))->y;
}

// License expiry status.
$license_badge = '';
if (!empty($driver->license_expiry) && $driver->license_expiry !== '0000-00-00') {
    $days = (strtotime($driver->license_expiry) - time()) / 86400;
    if ($days < 0) {
        $license_badge = '<span class="label label-danger">' . _l('fleet_expired') . '</span>';
    } elseif ($days < 30) {
        $license_badge = '<span class="label label-warning">' . _l('fleet_expiring_soon') . '</span>';
    } else {
        $license_badge = '<span class="label label-success">' . _l('fleet_valid') . '</span>';
    }
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="mbot15 clearfix">
            <a href="<?php echo admin_url('fleet_management/drivers'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> <?php echo _l('fleet_drivers'); ?></a>
            <?php if (has_permission('staff', '', 'edit')) : ?>
                <a href="<?php echo admin_url('staff/member/' . $driver->staffid); ?>" class="btn btn-default btn-sm pull-right"><i class="fa fa-user"></i> <?php echo _l('fleet_staff_account'); ?></a>
            <?php endif; ?>
        </div>

        <div class="row">
            <!-- Driver card -->
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body text-center">
                    <img src="<?php echo staff_profile_image_url($driver->staffid, 'thumb'); ?>" class="img-circle" style="width:96px;height:96px;object-fit:cover;margin-bottom:12px;">
                    <h3 class="bold no-margin"><?php echo html_escape($driver->full_name); ?></h3>
                    <p class="text-muted"><?php echo _l('fleet_driver'); ?>
                        <?php echo $driver->active ? '<span class="label label-success">' . _l('active') . '</span>' : '<span class="label label-default">' . _l('inactive') . '</span>'; ?>
                    </p>
                    <table class="table table-borderless no-margin text-left">
                        <tr><td class="bold"><?php echo _l('email'); ?></td><td><?php echo html_escape($driver->email); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_phone'); ?></td><td><?php echo html_escape($driver->phone ?: $driver->phonenumber); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_age'); ?></td><td><?php echo $age !== '' ? $age . ' ' . _l('fleet_years') : '—'; ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_license_number'); ?></td><td><?php echo html_escape($driver->license_number) ?: '—'; ?> <?php echo $license_badge; ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_hire_date'); ?></td><td><?php echo (!empty($driver->hire_date) && $driver->hire_date !== '0000-00-00') ? _d($driver->hire_date) : '—'; ?></td></tr>
                    </table>
                </div></div>

                <div class="row">
                    <div class="col-xs-4"><div class="panel_s"><div class="panel-body text-center">
                        <h2 class="bold no-margin text-info"><?php echo count($assignments); ?></h2>
                        <small class="text-muted"><?php echo _l('fleet_vehicles'); ?></small>
                    </div></div></div>
                    <div class="col-xs-4"><div class="panel_s"><div class="panel-body text-center">
                        <h2 class="bold no-margin text-success"><?php echo count($rentals); ?></h2>
                        <small class="text-muted"><?php echo _l('fleet_rentals'); ?></small>
                    </div></div></div>
                    <div class="col-xs-4"><div class="panel_s"><div class="panel-body text-center">
                        <h2 class="bold no-margin text-danger"><?php echo count($accidents); ?></h2>
                        <small class="text-muted"><?php echo _l('fleet_accidents'); ?></small>
                    </div></div></div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="col-md-8">
                <div class="panel_s"><div class="panel-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#tab_info" role="tab" data-toggle="tab"><i class="fa fa-id-card"></i> <?php echo _l('fleet_personal_info'); ?></a></li>
                        <li role="presentation"><a href="#tab_vehicles" role="tab" data-toggle="tab"><i class="fa fa-car"></i> <?php echo _l('fleet_vehicles'); ?></a></li>
                        <li role="presentation"><a href="#tab_rentals" role="tab" data-toggle="tab"><i class="fa fa-calendar"></i> <?php echo _l('fleet_rentals'); ?></a></li>
                        <li role="presentation"><a href="#tab_fuel" role="tab" data-toggle="tab"><i class="fa fa-tint"></i> <?php echo _l('fleet_fuel'); ?></a></li>
                        <li role="presentation"><a href="#tab_accidents" role="tab" data-toggle="tab"><i class="fa fa-exclamation-triangle"></i> <?php echo _l('fleet_accidents'); ?></a></li>
                    </ul>
                    <div class="tab-content mtop15">

                        <!-- Personal info / editable profile -->
                        <div role="tabpanel" class="tab-pane active" id="tab_info">
                            <?php echo form_open(admin_url('fleet_management/drivers/save_profile/' . $driver->staffid)); ?>
                            <div class="row">
                                <div class="col-md-6"><?php echo render_date_input('date_of_birth', 'fleet_date_of_birth', (!empty($driver->date_of_birth) && $driver->date_of_birth !== '0000-00-00') ? _d($driver->date_of_birth) : ''); ?></div>
                                <div class="col-md-6"><?php echo render_input('national_id', 'fleet_national_id', $driver->national_id); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><?php echo render_input('phone', 'fleet_phone', $driver->phone); ?></div>
                                <div class="col-md-6"><?php echo render_input('blood_type', 'fleet_blood_type', $driver->blood_type); ?></div>
                            </div>
                            <?php echo render_textarea('address', 'fleet_address', $driver->address); ?>

                            <h5 class="bold text-muted"><i class="fa fa-id-card"></i> <?php echo _l('fleet_license'); ?></h5>
                            <hr class="mtop5 mbot15" />
                            <div class="row">
                                <div class="col-md-6"><?php echo render_input('license_number', 'fleet_license_number', $driver->license_number); ?></div>
                                <div class="col-md-6"><?php echo render_input('license_category', 'fleet_license_category', $driver->license_category); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><?php echo render_date_input('license_issue_date', 'fleet_license_issue_date', (!empty($driver->license_issue_date) && $driver->license_issue_date !== '0000-00-00') ? _d($driver->license_issue_date) : ''); ?></div>
                                <div class="col-md-6"><?php echo render_date_input('license_expiry', 'fleet_license_expiry', (!empty($driver->license_expiry) && $driver->license_expiry !== '0000-00-00') ? _d($driver->license_expiry) : ''); ?></div>
                            </div>

                            <h5 class="bold text-muted"><i class="fa fa-info-circle"></i> <?php echo _l('fleet_employment_emergency'); ?></h5>
                            <hr class="mtop5 mbot15" />
                            <div class="row">
                                <div class="col-md-6"><?php echo render_date_input('hire_date', 'fleet_hire_date', (!empty($driver->hire_date) && $driver->hire_date !== '0000-00-00') ? _d($driver->hire_date) : ''); ?></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6"><?php echo render_input('emergency_contact', 'fleet_emergency_contact', $driver->emergency_contact); ?></div>
                                <div class="col-md-6"><?php echo render_input('emergency_phone', 'fleet_emergency_phone', $driver->emergency_phone); ?></div>
                            </div>
                            <?php echo render_textarea('notes', 'fleet_notes', $driver->profile_notes); ?>
                            <?php if (staff_can('edit', 'fleet')) : ?>
                                <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                            <?php endif; ?>
                            <?php echo form_close(); ?>
                        </div>

                        <!-- Assigned vehicles -->
                        <div role="tabpanel" class="tab-pane" id="tab_vehicles">
                            <table class="table">
                                <thead><tr><th><?php echo _l('fleet_vehicle'); ?></th><th><?php echo _l('fleet_assignment_start'); ?></th><th><?php echo _l('fleet_assignment_end'); ?></th><th><?php echo _l('fleet_status'); ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($assignments as $a) : ?>
                                    <tr>
                                        <td><a href="<?php echo admin_url('fleet_management/vehicles/view/' . $a['vehicle_id']); ?>"><?php echo html_escape($a['vehicle_name']); ?></a> <span class="text-muted"><?php echo html_escape($a['vehicle_plate']); ?></span></td>
                                        <td><?php echo $a['date_start'] ? _d($a['date_start']) : '-'; ?></td>
                                        <td><?php echo $a['date_end'] ? _d($a['date_end']) : '-'; ?></td>
                                        <td><?php echo $a['status'] === 'active' ? '<span class="label label-success">' . _l('active') . '</span>' : '<span class="label label-default">' . _l('fleet_ended') . '</span>'; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($assignments)) : ?><tr><td colspan="4" class="text-center text-muted">—</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Rentals -->
                        <div role="tabpanel" class="tab-pane" id="tab_rentals">
                            <table class="table">
                                <thead><tr><th><?php echo _l('fleet_vehicle'); ?></th><th><?php echo _l('fleet_client'); ?></th><th><?php echo _l('fleet_period'); ?></th><th><?php echo _l('fleet_total'); ?></th><th><?php echo _l('fleet_status'); ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($rentals as $r) : ?>
                                    <tr>
                                        <td><?php echo html_escape($r['vehicle_name']); ?></td>
                                        <td><?php echo html_escape($r['client_name']); ?></td>
                                        <td><?php echo _d($r['date_start']); ?> <span class="text-muted">&rarr;</span> <?php echo _d($r['date_end']); ?></td>
                                        <td><?php echo app_format_money($r['total'], $bc); ?></td>
                                        <td><?php echo fleet_rental_status_badge($r['status']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rentals)) : ?><tr><td colspan="5" class="text-center text-muted">—</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Fuel -->
                        <div role="tabpanel" class="tab-pane" id="tab_fuel">
                            <table class="table">
                                <thead><tr><th><?php echo _l('fleet_date'); ?></th><th><?php echo _l('fleet_vehicle'); ?></th><th><?php echo _l('fleet_liters'); ?></th><th><?php echo _l('fleet_total'); ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($fuel as $f) : ?>
                                    <tr>
                                        <td><?php echo _d($f['date']); ?></td>
                                        <td><?php echo html_escape($f['vehicle_name']); ?></td>
                                        <td><?php echo (float) $f['liters']; ?></td>
                                        <td><?php echo app_format_money($f['total_cost'], $bc); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($fuel)) : ?><tr><td colspan="4" class="text-center text-muted">—</td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Accidents -->
                        <div role="tabpanel" class="tab-pane" id="tab_accidents">
                            <?php if (staff_can('create', 'fleet')) : ?>
                                <div class="mbot15 text-right">
                                    <a href="#" class="btn btn-danger btn-sm" onclick="fleet_accident_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_accident'); ?></a>
                                </div>
                            <?php endif; ?>
                            <table class="table">
                                <thead><tr><th><?php echo _l('fleet_date'); ?></th><th><?php echo _l('fleet_vehicle'); ?></th><th><?php echo _l('fleet_location'); ?></th><th><?php echo _l('fleet_severity'); ?></th><th><?php echo _l('fleet_at_fault'); ?></th><th><?php echo _l('fleet_cost'); ?></th><th class="text-right"><?php echo _l('options'); ?></th></tr></thead>
                                <tbody>
                                <?php foreach ($accidents as $ac) : ?>
                                    <tr>
                                        <td><?php echo $ac['accident_date'] ? _d($ac['accident_date']) : '-'; ?></td>
                                        <td><?php echo html_escape($ac['vehicle_name']); ?></td>
                                        <td><?php echo html_escape($ac['location']); ?>
                                            <?php if ($ac['description']) : ?><br><small class="text-muted"><?php echo html_escape($ac['description']); ?></small><?php endif; ?>
                                        </td>
                                        <td><?php echo fleet_accident_severity_badge($ac['severity']); ?></td>
                                        <td><?php echo $ac['at_fault'] ? '<span class="label label-danger">' . _l('fleet_yes') . '</span>' : '<span class="label label-default">' . _l('fleet_no') . '</span>'; ?></td>
                                        <td><?php echo $ac['cost'] !== null ? app_format_money($ac['cost'], $bc) : '-'; ?></td>
                                        <td class="text-right">
                                            <?php if (staff_can('edit', 'fleet')) : ?>
                                                <a href="#" class="btn btn-default btn-icon btn-sm" onclick='fleet_accident_modal(<?php echo html_escape(json_encode(array_merge($ac, ['accident_date' => $ac['accident_date'] ? _d($ac['accident_date']) : '']))); ?>); return false;'><i class="fa fa-pencil-square"></i></a>
                                            <?php endif; ?>
                                            <?php if (staff_can('delete', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/drivers/accident_delete/' . $ac['id'] . '/' . $driver->staffid); ?>" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-remove"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($accidents)) : ?><tr><td colspan="7" class="text-center text-muted"><?php echo _l('fleet_no_accidents'); ?></td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>

<!-- Accident modal -->
<div class="modal fade" id="fleet_accident_modal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content">
    <?php echo form_open(admin_url('fleet_management/drivers/accident_save')); ?>
    <input type="hidden" name="id" id="accident_id" value="">
    <input type="hidden" name="staff_id" value="<?php echo $driver->staffid; ?>">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('fleet_accident'); ?></h4></div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-6"><?php echo render_date_input('accident_date', 'fleet_date', _d(date('Y-m-d'))); ?></div>
            <div class="col-md-6"><?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?></div>
        </div>
        <div class="row">
            <div class="col-md-6"><?php echo render_input('location', 'fleet_location', ''); ?></div>
            <div class="col-md-6">
                <?php
                $sev_options = [];
                foreach ($severities as $s) {
                    $sev_options[] = ['id' => $s, 'name' => _l('fleet_severity_' . $s)];
                }
                echo render_select('severity', $sev_options, ['id', 'name'], 'fleet_severity');
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6"><?php echo render_input('cost', 'fleet_cost', '', 'number'); ?></div>
            <div class="col-md-6"><?php echo render_input('third_party', 'fleet_third_party', ''); ?></div>
        </div>
        <?php echo render_textarea('description', 'fleet_description', ''); ?>
        <div class="checkbox checkbox-primary">
            <input type="checkbox" name="at_fault" id="accident_at_fault" value="1">
            <label for="accident_at_fault"><?php echo _l('fleet_at_fault'); ?></label>
        </div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button></div>
    <?php echo form_close(); ?>
</div></div></div>

<?php init_tail(); ?>
<script>
function fleet_accident_modal(rec) {
    var modal = $('#fleet_accident_modal');
    modal.find('form')[0].reset();
    $('#accident_id').val('');
    $('#accident_at_fault').prop('checked', false);
    if (typeof rec === 'object' && rec !== null) {
        $('#accident_id').val(rec.id);
        modal.find('[name="accident_date"]').val(rec.accident_date || '');
        modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
        modal.find('[name="location"]').val(rec.location);
        modal.find('[name="severity"]').val(rec.severity);
        modal.find('[name="cost"]').val(rec.cost);
        modal.find('[name="third_party"]').val(rec.third_party);
        modal.find('[name="description"]').val(rec.description);
        $('#accident_at_fault').prop('checked', rec.at_fault == 1);
        if (modal.find('.selectpicker').length) {
            modal.find('.selectpicker').selectpicker('refresh');
        }
    }
    modal.modal('show');
}
</script>
</body>
</html>
