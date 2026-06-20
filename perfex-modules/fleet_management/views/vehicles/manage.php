<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$status_meta = [
    'available'      => ['#71dd37', 'fa-check-circle'],
    'rented'         => ['#03c3ec', 'fa-key'],
    'maintenance'    => ['#ffab00', 'fa-wrench'],
    'out_of_service' => ['#ff3e1d', 'fa-ban'],
];
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">

        <!-- Toolbar -->
        <div class="fleet-toolbar">
            <h3><i class="fa fa-car text-info"></i> <?php echo _l('fleet_vehicles'); ?></h3>
            <div class="fleet-tools">
                <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                <a href="<?php echo admin_url('fleet_management/vehicles/export'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="<?php echo admin_url('fleet_management/vehicles/vehicle'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_vehicle'); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Status KPI cards -->
        <div class="row">
            <?php foreach (fleet_vehicle_statuses() as $status) : $meta = $status_meta[$status]; ?>
                <div class="col-md-3 col-sm-6">
                    <div class="panel_s"><div class="panel-body fleet-stat">
                        <div class="ic" style="background:<?php echo $meta[0]; ?>;"><i class="fa <?php echo $meta[1]; ?>"></i></div>
                        <div>
                            <h2><?php echo (int) ($status_counts[$status] ?? 0); ?></h2>
                            <span><?php echo _l('fleet_status_' . $status); ?></span>
                        </div>
                    </div></div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Vehicles table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s"><div class="panel-body">
                    <div class="table-responsive">
                        <table class="table fleet-list">
                            <thead>
                                <tr>
                                    <th><?php echo _l('fleet_vehicle'); ?></th>
                                    <th><?php echo _l('fleet_plate'); ?></th>
                                    <th><?php echo _l('fleet_category'); ?></th>
                                    <th><?php echo _l('fleet_odometer'); ?></th>
                                    <th><?php echo _l('fleet_daily_rate'); ?></th>
                                    <th><?php echo _l('fleet_status'); ?></th>
                                    <th class="text-right"><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($vehicles as $vehicle) : ?>
                                    <tr>
                                        <td>
                                            <div class="fleet-veh">
                                                <span class="av"><i class="fa fa-car"></i></span>
                                                <div>
                                                    <a href="<?php echo admin_url('fleet_management/vehicles/view/' . $vehicle['id']); ?>" class="bold"><?php echo html_escape($vehicle['name']); ?></a>
                                                    <br><small class="text-muted"><?php echo html_escape(trim($vehicle['brand'] . ' ' . $vehicle['model'])); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $vehicle['plate'] ? '<span class="fleet-plate">' . html_escape($vehicle['plate']) . '</span>' : '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo $vehicle['category'] ? html_escape($vehicle['category']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td><?php echo (int) $vehicle['odometer']; ?> km</td>
                                        <td class="bold"><?php echo app_format_money($vehicle['daily_rate'], get_base_currency()); ?></td>
                                        <td><?php echo fleet_vehicle_status_badge($vehicle['status']); ?></td>
                                        <td class="text-right">
                                            <a href="<?php echo admin_url('fleet_management/vehicles/view/' . $vehicle['id']); ?>" class="btn btn-default btn-icon btn-sm"><i class="fa fa-eye"></i></a>
                                            <?php if (staff_can('edit', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/vehicles/vehicle/' . $vehicle['id']); ?>" class="btn btn-default btn-icon btn-sm"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php endif; ?>
                                            <?php if (staff_can('delete', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/vehicles/delete/' . $vehicle['id']); ?>" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-remove"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($vehicles)) : ?>
                                    <tr><td colspan="7" class="text-center text-muted" style="padding:30px;"><i class="fa fa-car fa-2x"></i><br><?php echo _l('fleet_add_vehicle'); ?></td></tr>
                                <?php endif; ?>
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
