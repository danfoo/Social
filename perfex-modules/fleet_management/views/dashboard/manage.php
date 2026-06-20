<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $bc = get_base_currency(); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <!-- KPI cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body text-center">
                    <h2 class="bold no-margin"><?php echo app_format_money($totals['total'], $bc); ?></h2>
                    <span class="text-muted"><?php echo _l('fleet_dash_total_cost'); ?></span>
                </div></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body text-center">
                    <h2 class="bold no-margin"><?php echo array_sum($status_counts); ?></h2>
                    <span class="text-muted"><?php echo _l('fleet_vehicles'); ?></span>
                </div></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body text-center">
                    <h2 class="bold no-margin"><?php echo (int) ($status_counts['available'] ?? 0); ?></h2>
                    <span class="text-muted"><?php echo _l('fleet_status_available'); ?></span>
                </div></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body text-center">
                    <h2 class="bold no-margin"><?php echo (int) $fleet_occupancy; ?>%</h2>
                    <span class="text-muted"><?php echo _l('fleet_dash_occupancy', $occupancy_days); ?></span>
                </div></div>
            </div>
        </div>

        <!-- Cost split -->
        <div class="row">
            <div class="col-md-3 col-xs-6"><div class="panel_s"><div class="panel-body text-center">
                <i class="fa fa-wrench text-warning"></i> <span class="bold"><?php echo app_format_money($totals['maintenance'], $bc); ?></span>
                <p class="text-muted no-margin"><?php echo _l('fleet_maintenance'); ?></p>
            </div></div></div>
            <div class="col-md-3 col-xs-6"><div class="panel_s"><div class="panel-body text-center">
                <i class="fa fa-tint text-success"></i> <span class="bold"><?php echo app_format_money($totals['fuel'], $bc); ?></span>
                <p class="text-muted no-margin"><?php echo _l('fleet_fuel'); ?></p>
            </div></div></div>
            <div class="col-md-3 col-xs-6"><div class="panel_s"><div class="panel-body text-center">
                <i class="fa fa-cog text-primary"></i> <span class="bold"><?php echo app_format_money($totals['parts'], $bc); ?></span>
                <p class="text-muted no-margin"><?php echo _l('fleet_parts_articles'); ?></p>
            </div></div></div>
            <div class="col-md-3 col-xs-6"><div class="panel_s"><div class="panel-body text-center">
                <i class="fa fa-bell text-danger"></i> <span class="bold"><?php echo app_format_money($totals['reminders'], $bc); ?></span>
                <p class="text-muted no-margin"><?php echo _l('fleet_reminders'); ?></p>
            </div></div></div>
        </div>

        <!-- Per-vehicle breakdown -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s"><div class="panel-body">
                    <h4 class="no-margin"><?php echo _l('fleet_dash_per_vehicle'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><?php echo _l('fleet_vehicle'); ?></th>
                                    <th><?php echo _l('fleet_odometer'); ?></th>
                                    <th><?php echo _l('fleet_maintenance'); ?></th>
                                    <th><?php echo _l('fleet_fuel'); ?></th>
                                    <th><?php echo _l('fleet_parts_articles'); ?></th>
                                    <th><?php echo _l('fleet_reminders'); ?></th>
                                    <th><?php echo _l('fleet_dash_total_cost'); ?></th>
                                    <th><?php echo _l('fleet_dash_cost_per_km'); ?></th>
                                    <th><?php echo _l('fleet_dash_occupancy_short'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row) : $v = $row['vehicle']; ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('fleet_management/vehicles/view/' . $v['id']); ?>"><?php echo html_escape($v['name']); ?></a>
                                            <br><small class="text-muted"><?php echo html_escape($v['plate']); ?></small>
                                        </td>
                                        <td><?php echo (int) $v['odometer']; ?> km</td>
                                        <td><?php echo app_format_money($row['maintenance'], $bc); ?></td>
                                        <td><?php echo app_format_money($row['fuel'], $bc); ?></td>
                                        <td><?php echo app_format_money($row['parts'], $bc); ?></td>
                                        <td><?php echo app_format_money($row['reminders'], $bc); ?></td>
                                        <td class="bold"><?php echo app_format_money($row['total'], $bc); ?></td>
                                        <td><?php echo $row['cost_per_km'] > 0 ? app_format_money($row['cost_per_km'], $bc) . '/km' : '-'; ?></td>
                                        <td style="min-width:120px;">
                                            <div class="progress no-margin" style="height:18px;">
                                                <div class="progress-bar progress-bar-info" role="progressbar" style="width:<?php echo (int) $row['occupancy']; ?>%;">
                                                    <?php echo (int) $row['occupancy']; ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
