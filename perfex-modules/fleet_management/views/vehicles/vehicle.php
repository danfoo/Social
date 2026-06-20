<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$status_options = [];
foreach (fleet_vehicle_statuses() as $s) {
    $status_options[] = ['id' => $s, 'name' => _l('fleet_status_' . $s)];
}
$driver_options = [];
foreach ($drivers as $d) {
    $driver_options[] = ['id' => $d['staffid'], 'name' => $d['full_name']];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $vehicle ? _l('fleet_edit_vehicle') : _l('fleet_add_vehicle'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open($vehicle ? admin_url('fleet_management/vehicles/vehicle/' . $vehicle->id) : admin_url('fleet_management/vehicles/vehicle')); ?>
                        <div class="row">
                            <div class="col-md-6"><?php echo render_input('name', 'fleet_vehicle_name', $vehicle->name ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('plate', 'fleet_plate', $vehicle->plate ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_select('status', $status_options, ['id', 'name'], 'fleet_status', $vehicle->status ?? 'available'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"><?php echo render_input('brand', 'fleet_brand', $vehicle->brand ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('model', 'fleet_model', $vehicle->model ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('year', 'fleet_year', $vehicle->year ?? '', 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_input('category', 'fleet_category', $vehicle->category ?? ''); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"><?php echo render_input('vin', 'fleet_vin', $vehicle->vin ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('color', 'fleet_color', $vehicle->color ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('fuel_type', 'fleet_fuel_type', $vehicle->fuel_type ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('transmission', 'fleet_transmission', $vehicle->transmission ?? ''); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"><?php echo render_input('seats', 'fleet_seats', $vehicle->seats ?? '', 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_input('odometer', 'fleet_odometer', $vehicle->odometer ?? 0, 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_input('daily_rate', 'fleet_daily_rate', $vehicle->daily_rate ?? 0, 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_input('daily_rate_with_driver', 'fleet_daily_rate_with_driver', $vehicle->daily_rate_with_driver ?? 0, 'number'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"><?php echo render_date_input('purchase_date', 'fleet_purchase_date', isset($vehicle->purchase_date) ? _d($vehicle->purchase_date) : ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('purchase_price', 'fleet_purchase_price', $vehicle->purchase_price ?? '', 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_input('insurance_company', 'fleet_insurance_company', $vehicle->insurance_company ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('insurance_policy', 'fleet_insurance_policy', $vehicle->insurance_policy ?? ''); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-12"><?php echo render_textarea('notes', 'fleet_notes', $vehicle->notes ?? ''); ?></div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <a href="<?php echo admin_url('fleet_management/vehicles'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                            <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
