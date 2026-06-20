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
$category_options = [];
foreach ($categories as $c) {
    $category_options[] = ['id' => $c['name'], 'name' => $c['name']];
}
$insurer_options = [];
foreach ($insurers as $ins) {
    $insurer_options[] = ['id' => $ins['name'], 'name' => $ins['name']];
}
$current_brand = $vehicle->brand ?? '';
$current_model = $vehicle->model ?? '';
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
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="brand"><?php echo _l('fleet_brand'); ?></label>
                                    <select name="brand" id="brand" class="form-control">
                                        <option value=""></option>
                                        <?php foreach ($brands as $b) : ?>
                                            <option value="<?php echo html_escape($b['name']); ?>" <?php echo $current_brand === $b['name'] ? 'selected' : ''; ?>><?php echo html_escape($b['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="control-label" for="model"><?php echo _l('fleet_model'); ?></label>
                                    <select name="model" id="model" class="form-control">
                                        <option value=""></option>
                                        <?php foreach ($models as $m) : ?>
                                            <option value="<?php echo html_escape($m['name']); ?>" data-brand="<?php echo html_escape($m['brand_name']); ?>" <?php echo $current_model === $m['name'] ? 'selected' : ''; ?>><?php echo html_escape($m['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3"><?php echo render_input('year', 'fleet_year', $vehicle->year ?? '', 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_select('category', $category_options, ['id', 'name'], 'fleet_category', $vehicle->category ?? ''); ?></div>
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
                            <div class="col-md-3"><?php echo render_select('insurance_company', $insurer_options, ['id', 'name'], 'fleet_insurance_company', $vehicle->insurance_company ?? ''); ?></div>
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
<script>
$(function() {
    // Brand -> model dependent dropdown (models carry their brand name).
    var $model = $('#model');
    var allModels = $model.find('option').clone();

    function filterModels() {
        var brand = $('#brand').val();
        var current = $model.val();
        $model.empty().append('<option value=""></option>');
        allModels.each(function() {
            var o = $(this);
            if (o.val() === '') { return; }
            if (!brand || o.data('brand') === brand) {
                $model.append(o.clone());
            }
        });
        $model.val(current);
    }

    $('#brand').on('change', function() { filterModels(); });
    filterModels();
});
</script>
</body>
</html>
