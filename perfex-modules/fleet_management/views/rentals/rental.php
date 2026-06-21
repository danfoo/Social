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
                            <div class="col-md-4"><?php echo render_input('pickup_location', 'fleet_pickup_location', $rental->pickup_location ?? ''); ?></div>
                            <div class="col-md-4"><?php echo render_input('return_location', 'fleet_return_location', $rental->return_location ?? ''); ?></div>
                            <div class="col-md-4"><?php echo render_input('deposit', 'fleet_deposit', $rental->deposit ?? '', 'number'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-12"><?php echo render_textarea('notes', 'fleet_notes', $rental->notes ?? ''); ?></div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <a href="<?php echo admin_url('fleet_management/rentals'); ?>" class="btn btn-default"><?php echo _l('cancel'); ?></a>
                            <?php if ($rental) : ?>
                                <a href="<?php echo admin_url('fleet_management/rentals/contract_pdf/' . $rental->id); ?>" target="_blank" class="btn btn-info"><i class="fa fa-file-pdf"></i> <?php echo _l('fleet_contract'); ?></a>
                                <a href="<?php echo admin_url('fleet_management/rentals/inspection_pdf/' . $rental->id); ?>" target="_blank" class="btn btn-info"><i class="fa fa-file-pdf"></i> <?php echo _l('fleet_inspection'); ?></a>
                            <?php endif; ?>
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

        <?php if ($rental) : ?>
            <?php
            $fuel_options = [['id' => '', 'name' => '—']];
            for ($i = 0; $i <= 8; $i++) {
                $fuel_options[] = ['id' => $i, 'name' => fleet_fuel_eighths_label($i)];
            }

            // Renders one inspection block (checkout / checkin).
            $render_inspection = function ($type, $icon, $title_key) use ($rental, $inspections, $inspection_files, $fuel_options) {
                $insp  = $inspections[$type] ?? null;
                $files = $insp ? ($inspection_files[$type] ?? []) : [];
                ?>
                <div class="col-md-6">
                    <div class="panel_s"><div class="panel-body">
                        <h4 class="bold"><i class="fa <?php echo $icon; ?> text-info"></i> <?php echo _l($title_key); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('fleet_management/rentals/save_inspection')); ?>
                        <input type="hidden" name="rental_id" value="<?php echo $rental->id; ?>">
                        <input type="hidden" name="type" value="<?php echo $type; ?>">
                        <div class="row">
                            <div class="col-md-6"><?php echo render_date_input('inspection_date', 'fleet_date', isset($insp['inspection_date']) && $insp['inspection_date'] ? _d($insp['inspection_date']) : _d(date('Y-m-d'))); ?></div>
                            <div class="col-md-6"><?php echo render_input('odometer', 'fleet_odometer', $insp['odometer'] ?? '', 'number'); ?></div>
                        </div>
                        <?php echo render_select('fuel_level', $fuel_options, ['id', 'name'], 'fleet_fuel_level', $insp['fuel_level'] ?? '', ['data-none-selected-text' => '']); ?>
                        <?php echo render_textarea('exterior_condition', 'fleet_exterior_condition', $insp['exterior_condition'] ?? ''); ?>
                        <?php echo render_textarea('interior_condition', 'fleet_interior_condition', $insp['interior_condition'] ?? ''); ?>
                        <?php echo render_textarea('damages', 'fleet_damages', $insp['damages'] ?? ''); ?>
                        <button type="submit" class="btn btn-primary btn-sm"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>

                        <?php if ($insp) : ?>
                            <hr />
                            <label class="control-label"><?php echo _l('fleet_photos'); ?></label>
                            <div class="fleet-insp-photos">
                                <?php foreach ($files as $f) : ?>
                                    <div class="fleet-insp-thumb">
                                        <a href="<?php echo admin_url('fleet_management/rentals/inspection_image/' . $f['id']); ?>" target="_blank">
                                            <img src="<?php echo admin_url('fleet_management/rentals/inspection_image/' . $f['id']); ?>">
                                        </a>
                                        <?php if (staff_can('delete', 'fleet')) : ?>
                                            <a href="<?php echo admin_url('fleet_management/rentals/inspection_delete_file/' . $f['id']); ?>" class="del _delete"><i class="fa fa-remove"></i></a>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php echo form_open_multipart(admin_url('fleet_management/rentals/inspection_upload/' . $insp['id']), ['class' => 'mtop10']); ?>
                            <div class="input-group">
                                <input type="file" name="file" accept="image/*" class="form-control input-sm" required>
                                <span class="input-group-btn"><button type="submit" class="btn btn-default btn-sm"><i class="fa fa-upload"></i> <?php echo _l('fleet_upload'); ?></button></span>
                            </div>
                            <?php echo form_close(); ?>
                        <?php else : ?>
                            <p class="text-muted mtop10"><small><?php echo _l('fleet_inspection_save_first'); ?></small></p>
                        <?php endif; ?>
                    </div></div>
                </div>
                <?php
            };
            ?>
            <div class="row" id="inspections">
                <div class="col-md-12"><h4 class="bold"><i class="fa fa-clipboard"></i> <?php echo _l('fleet_inspections'); ?></h4></div>
                <?php $render_inspection('checkout', 'fa-sign-out', 'fleet_inspection_checkout'); ?>
                <?php $render_inspection('checkin', 'fa-sign-in', 'fleet_inspection_checkin'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
.fleet-insp-photos{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;}
.fleet-insp-thumb{position:relative;width:84px;height:84px;border-radius:8px;overflow:hidden;border:1px solid #e6e9f0;}
.fleet-insp-thumb img{width:100%;height:100%;object-fit:cover;}
.fleet-insp-thumb .del{position:absolute;top:2px;right:2px;background:#dc3545;color:#fff;width:18px;height:18px;line-height:18px;text-align:center;border-radius:50%;font-size:10px;}
</style>
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
