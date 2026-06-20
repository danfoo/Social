<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')', 'data-rate' => $v['daily_rate'], 'data-rate-driver' => $v['daily_rate_with_driver']];
}
$client_options = [];
foreach ($clients as $c) {
    $client_options[] = ['id' => $c['userid'], 'name' => $c['company']];
}
$driver_options = [];
foreach ($drivers as $d) {
    $driver_options[] = ['id' => $d['staffid'], 'name' => $d['full_name']];
}
$status_options = [];
foreach (fleet_rental_statuses() as $s) {
    $status_options[] = ['id' => $s, 'name' => _l('fleet_status_' . $s)];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $rental ? _l('fleet_edit_rental') : _l('fleet_add_rental'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open($rental ? admin_url('fleet_management/rentals/rental/' . $rental->id) : admin_url('fleet_management/rentals/rental')); ?>
                        <div class="row">
                            <div class="col-md-6"><?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle', $rental->vehicle_id ?? ($preselect_vehicle ?? '')); ?></div>
                            <div class="col-md-6"><?php echo render_select('clientid', $client_options, ['id', 'name'], 'client', $rental->clientid ?? ''); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3"><?php echo render_date_input('date_start', 'fleet_date_start', isset($rental->date_start) ? _d($rental->date_start) : ''); ?></div>
                            <div class="col-md-3"><?php echo render_date_input('date_end', 'fleet_date_end', isset($rental->date_end) ? _d($rental->date_end) : ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('daily_rate', 'fleet_daily_rate', $rental->daily_rate ?? 0, 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_select('status', $status_options, ['id', 'name'], 'fleet_status', $rental->status ?? 'reserved'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox checkbox-primary mtop25">
                                    <input type="checkbox" name="with_driver" id="with_driver" value="1" <?php echo (isset($rental->with_driver) && $rental->with_driver) ? 'checked' : ''; ?>>
                                    <label for="with_driver"><?php echo _l('fleet_with_driver'); ?></label>
                                </div>
                            </div>
                            <div class="col-md-3"><?php echo render_select('driver_id', $driver_options, ['id', 'name'], 'fleet_driver', $rental->driver_id ?? ''); ?></div>
                            <div class="col-md-3"><?php echo render_input('odometer_start', 'fleet_odometer_start', $rental->odometer_start ?? '', 'number'); ?></div>
                            <div class="col-md-3"><?php echo render_input('odometer_end', 'fleet_odometer_end', $rental->odometer_end ?? '', 'number'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><?php echo render_input('pickup_location', 'fleet_pickup_location', $rental->pickup_location ?? ''); ?></div>
                            <div class="col-md-6"><?php echo render_input('return_location', 'fleet_return_location', $rental->return_location ?? ''); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-12"><?php echo render_textarea('notes', 'fleet_notes', $rental->notes ?? ''); ?></div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <a href="<?php echo admin_url('fleet_management/rentals'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                            <?php if ($rental && empty($rental->invoice_id) && staff_can('create', 'fleet')) : ?>
                                <a href="<?php echo admin_url('fleet_management/rentals/create_invoice/' . $rental->id); ?>" class="btn btn-success"><i class="fa fa-file-text"></i> <?php echo _l('fleet_create_invoice'); ?></a>
                            <?php endif; ?>
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
    // Pre-fill the daily rate from the chosen vehicle (with/without driver).
    function applyRate() {
        var opt = $('select[name="vehicle_id"]').find('option:selected');
        if (!opt.length) { return; }
        var withDriver = $('#with_driver').is(':checked');
        var rate = withDriver ? opt.data('rate-driver') : opt.data('rate');
        if (typeof rate !== 'undefined' && rate !== null && rate !== '') {
            $('input[name="daily_rate"]').val(rate);
        }
    }
    $('select[name="vehicle_id"]').on('change', applyRate);
    $('#with_driver').on('change', applyRate);
});
</script>
</body>
</html>
