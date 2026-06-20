<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$driver_options = [];
foreach ($drivers as $d) {
    $driver_options[] = ['id' => $d['staffid'], 'name' => $d['full_name']];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="bold"><?php echo html_escape($vehicle->name); ?> <?php echo fleet_vehicle_status_badge($vehicle->status); ?></h4>
                        <p class="text-muted"><?php echo html_escape($vehicle->brand . ' ' . $vehicle->model . ' · ' . $vehicle->year); ?></p>
                        <table class="table table-borderless no-margin">
                            <tr><td class="bold"><?php echo _l('fleet_plate'); ?></td><td><?php echo html_escape($vehicle->plate); ?></td></tr>
                            <tr><td class="bold"><?php echo _l('fleet_category'); ?></td><td><?php echo html_escape($vehicle->category); ?></td></tr>
                            <tr><td class="bold"><?php echo _l('fleet_odometer'); ?></td><td><?php echo (int) $vehicle->odometer; ?> km</td></tr>
                            <tr><td class="bold"><?php echo _l('fleet_daily_rate'); ?></td><td><?php echo app_format_money($vehicle->daily_rate, get_base_currency()); ?></td></tr>
                            <tr><td class="bold"><?php echo _l('fleet_daily_rate_with_driver'); ?></td><td><?php echo app_format_money($vehicle->daily_rate_with_driver, get_base_currency()); ?></td></tr>
                            <tr><td class="bold"><?php echo _l('fleet_insurance_company'); ?></td><td><?php echo html_escape($vehicle->insurance_company); ?></td></tr>
                        </table>
                        <?php if (staff_can('edit', 'fleet')) : ?>
                            <a href="<?php echo admin_url('fleet_management/vehicles/vehicle/' . $vehicle->id); ?>" class="btn btn-default btn-block mtop15"><?php echo _l('edit'); ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <div class="mbot15 fleet-quick-actions">
                                <a href="<?php echo admin_url('fleet_management/rentals/rental?vehicle_id=' . $vehicle->id); ?>" class="btn btn-info btn-sm"><i class="fa fa-calendar"></i> <?php echo _l('fleet_add_rental'); ?></a>
                                <a href="<?php echo admin_url('fleet_management/maintenance?vehicle_id=' . $vehicle->id . '&open=1'); ?>" class="btn btn-warning btn-sm"><i class="fa fa-wrench"></i> <?php echo _l('fleet_add_maintenance'); ?></a>
                                <a href="<?php echo admin_url('fleet_management/fuel?vehicle_id=' . $vehicle->id . '&open=1'); ?>" class="btn btn-success btn-sm"><i class="fa fa-tint"></i> <?php echo _l('fleet_add_fuel'); ?></a>
                                <a href="<?php echo admin_url('fleet_management/parts?assign_vehicle=' . $vehicle->id); ?>#stock" class="btn btn-primary btn-sm"><i class="fa fa-cog"></i> <?php echo _l('fleet_assign'); ?></a>
                                <a href="<?php echo admin_url('fleet_management/reminders?vehicle_id=' . $vehicle->id . '&open=1'); ?>" class="btn btn-danger btn-sm"><i class="fa fa-bell"></i> <?php echo _l('fleet_add_reminder'); ?></a>
                            </div>
                            <hr class="mtop10 mbot15" />
                        <?php endif; ?>
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#tab_history" role="tab" data-toggle="tab"><?php echo _l('fleet_history'); ?></a></li>
                            <li role="presentation"><a href="#tab_assign" role="tab" data-toggle="tab"><?php echo _l('fleet_drivers'); ?></a></li>
                            <li role="presentation"><a href="#tab_maintenance" role="tab" data-toggle="tab"><?php echo _l('fleet_maintenance'); ?></a></li>
                            <li role="presentation"><a href="#tab_parts" role="tab" data-toggle="tab"><?php echo _l('fleet_parts_articles'); ?></a></li>
                            <li role="presentation"><a href="#tab_reminders" role="tab" data-toggle="tab"><?php echo _l('fleet_reminders'); ?></a></li>
                        </ul>
                        <div class="tab-content mtop15">
                            <div role="tabpanel" class="tab-pane active" id="tab_history">
                                <?php if (empty($activity)) : ?>
                                    <p class="text-muted"><?php echo _l('fleet_history_empty'); ?></p>
                                <?php else : ?>
                                    <ul class="fleet-timeline">
                                        <?php foreach ($activity as $act) : ?>
                                            <li class="fleet-timeline-item">
                                                <span class="label label-<?php echo fleet_activity_color($act['type']); ?>"><i class="fa <?php echo fleet_activity_icon($act['type']); ?>"></i> <?php echo _l('fleet_atype_' . $act['type']); ?></span>
                                                <span class="fleet-timeline-text"><?php echo html_escape($act['description']); ?></span>
                                                <small class="text-muted">
                                                    <?php echo _dt($act['date_created']); ?>
                                                    <?php if (!empty($act['staff_name'])) : ?>· <?php echo html_escape($act['staff_name']); ?><?php endif; ?>
                                                </small>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php endif; ?>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab_assign">
                                <?php if (staff_can('edit', 'fleet')) : ?>
                                    <?php echo form_open(admin_url('fleet_management/vehicles/assign_driver')); ?>
                                    <input type="hidden" name="vehicle_id" value="<?php echo $vehicle->id; ?>">
                                    <div class="row">
                                        <div class="col-md-5"><?php echo render_select('staff_id', $driver_options, ['id', 'name'], 'fleet_driver'); ?></div>
                                        <div class="col-md-4"><?php echo render_date_input('date_start', 'fleet_assignment_start', _d(date('Y-m-d'))); ?></div>
                                        <div class="col-md-3">
                                            <label class="control-label">&nbsp;</label>
                                            <button type="submit" class="btn btn-primary btn-block"><?php echo _l('fleet_assign'); ?></button>
                                        </div>
                                    </div>
                                    <?php echo form_close(); ?>
                                    <hr />
                                <?php endif; ?>
                                <table class="table">
                                    <thead><tr><th><?php echo _l('fleet_driver'); ?></th><th><?php echo _l('fleet_assignment_start'); ?></th><th><?php echo _l('fleet_assignment_end'); ?></th><th><?php echo _l('fleet_status'); ?></th><th></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($assignments as $a) : ?>
                                        <tr>
                                            <td><?php echo html_escape($a['driver_name']); ?></td>
                                            <td><?php echo $a['date_start'] ? _d($a['date_start']) : '-'; ?></td>
                                            <td><?php echo $a['date_end'] ? _d($a['date_end']) : '-'; ?></td>
                                            <td><?php echo $a['status'] === 'active' ? '<span class="label label-success">' . _l('active') . '</span>' : '<span class="label label-default">' . _l('fleet_ended') . '</span>'; ?></td>
                                            <td>
                                                <?php if ($a['status'] === 'active' && staff_can('edit', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/vehicles/end_assignment/' . $a['id'] . '/' . $vehicle->id); ?>" class="btn btn-default btn-xs"><?php echo _l('fleet_end_assignment'); ?></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab_maintenance">
                                <p class="text-muted"><?php echo _l('fleet_maintenance_in_global'); ?> <a href="<?php echo admin_url('fleet_management/maintenance'); ?>"><?php echo _l('fleet_maintenance'); ?></a>.</p>
                                <table class="table">
                                    <thead><tr><th><?php echo _l('fleet_type'); ?></th><th><?php echo _l('fleet_service_date'); ?></th><th><?php echo _l('fleet_cost'); ?></th><th><?php echo _l('fleet_next_service_date'); ?></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($maintenance as $m) : ?>
                                        <tr>
                                            <td><?php echo _l('fleet_mtype_' . $m['type']); ?></td>
                                            <td><?php echo $m['service_date'] ? _d($m['service_date']) : '-'; ?></td>
                                            <td><?php echo app_format_money($m['cost'], get_base_currency()); ?></td>
                                            <td><?php echo $m['next_service_date'] ? _d($m['next_service_date']) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab_parts">
                                <p class="text-muted"><?php echo _l('fleet_maintenance_in_global'); ?> <a href="<?php echo admin_url('fleet_management/parts'); ?>"><?php echo _l('fleet_parts_articles'); ?></a>.</p>
                                <table class="table">
                                    <thead><tr><th><?php echo _l('fleet_part_name'); ?></th><th><?php echo _l('fleet_reference'); ?></th><th><?php echo _l('fleet_quantity'); ?></th><th><?php echo _l('fleet_total'); ?></th><th><?php echo _l('fleet_date'); ?></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($parts as $p) : ?>
                                        <tr>
                                            <td><?php echo html_escape($p['item_name']); ?></td>
                                            <td><?php echo html_escape($p['item_reference']); ?></td>
                                            <td><?php echo (int) $p['quantity']; ?></td>
                                            <td><?php echo app_format_money($p['total_cost'], get_base_currency()); ?></td>
                                            <td><?php echo $p['assigned_date'] ? _d($p['assigned_date']) : '-'; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane" id="tab_reminders">
                                <table class="table">
                                    <thead><tr><th><?php echo _l('fleet_type'); ?></th><th><?php echo _l('fleet_reminder_title'); ?></th><th><?php echo _l('fleet_due_date'); ?></th><th></th></tr></thead>
                                    <tbody>
                                    <?php foreach ($reminders as $r) : ?>
                                        <tr>
                                            <td><?php echo _l('fleet_rtype_' . $r['type']); ?></td>
                                            <td><?php echo html_escape($r['title']); ?></td>
                                            <td><?php echo _d($r['due_date']); ?></td>
                                            <td><?php echo fleet_reminder_due_badge($r['due_date'], $r['notify_days']); ?></td>
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
</div>
<style>
.fleet-timeline { list-style: none; margin: 0; padding: 0; }
.fleet-timeline-item { padding: 10px 0; border-bottom: 1px solid #eee; }
.fleet-timeline-item:last-child { border-bottom: 0; }
.fleet-timeline-item .fleet-timeline-text { display: block; margin: 4px 0; }
</style>
<?php init_tail(); ?>
</body>
</html>
