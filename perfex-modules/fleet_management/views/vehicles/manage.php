<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row mbot15">
                            <?php foreach (fleet_vehicle_statuses() as $status) : ?>
                                <div class="col-md-3 col-xs-6">
                                    <div class="text-center">
                                        <h2 class="bold no-margin"><?php echo (int) ($status_counts[$status] ?? 0); ?></h2>
                                        <?php echo fleet_vehicle_status_badge($status); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <hr />
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <a href="<?php echo admin_url('fleet_management/vehicles/vehicle'); ?>" class="btn btn-primary mbot15">
                                <i class="fa fa-plus"></i> <?php echo _l('fleet_add_vehicle'); ?>
                            </a>
                        <?php endif; ?>
                        <div class="text-right mbot15">
                            <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                        </div>
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
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($vehicles as $vehicle) : ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo admin_url('fleet_management/vehicles/view/' . $vehicle['id']); ?>">
                                                    <?php echo html_escape($vehicle['name']); ?>
                                                </a>
                                                <br><small class="text-muted"><?php echo html_escape($vehicle['brand'] . ' ' . $vehicle['model']); ?></small>
                                            </td>
                                            <td><?php echo html_escape($vehicle['plate']); ?></td>
                                            <td><?php echo html_escape($vehicle['category']); ?></td>
                                            <td><?php echo (int) $vehicle['odometer']; ?> km</td>
                                            <td><?php echo app_format_money($vehicle['daily_rate'], get_base_currency()); ?></td>
                                            <td><?php echo fleet_vehicle_status_badge($vehicle['status']); ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('fleet_management/vehicles/view/' . $vehicle['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-eye"></i></a>
                                                <?php if (staff_can('edit', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/vehicles/vehicle/' . $vehicle['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/vehicles/delete/' . $vehicle['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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
<?php init_tail(); ?>
<script>
</script>
</body>
</html>
