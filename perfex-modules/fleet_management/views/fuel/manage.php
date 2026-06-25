<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')'];
}
$driver_options = [];
foreach ($drivers as $d) {
    $driver_options[] = ['id' => $d['staffid'], 'name' => $d['full_name']];
}
$supplier_options = [];
foreach ($suppliers as $s) {
    $supplier_options[] = ['id' => $s['id'], 'name' => $s['name']];
}
$fuel_options = [];
foreach (fleet_fuel_types() as $t) {
    $fuel_options[] = ['id' => $t, 'name' => _l('fleet_fuel_' . $t)];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-tint text-info"></i> <?php echo _l('fleet_fuel'); ?></h3>
            <div class="fleet-tools">
                <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                <a href="<?php echo admin_url('fleet_management/fuel/export?period=' . $period . ($specific_date ? '&date=' . $specific_date : '') . ($supplier_id ? '&supplier_id=' . $supplier_id : '')); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="#" class="btn btn-primary btn-sm" onclick="fleet_fuel_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_fuel'); ?></a>
                <?php endif; ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-6"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#6571ff;"><i class="fa fa-tint"></i></div>
                <div><h2><?php echo (int) $stats->entries; ?></h2><span><?php echo _l('fleet_fuel_entries'); ?></span></div>
            </div></div></div>
            <div class="col-md-4 col-sm-6"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#03c3ec;"><i class="fa fa-flask"></i></div>
                <div><h2><?php echo (float) $stats->total_liters; ?> L</h2><span><?php echo _l('fleet_fuel_total_liters'); ?></span></div>
            </div></div></div>
            <div class="col-md-4 col-sm-6"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#71dd37;"><i class="fa fa-money"></i></div>
                <div><h2><?php echo app_format_money($stats->total_cost, get_base_currency()); ?></h2><span><?php echo _l('fleet_fuel_total_cost'); ?></span></div>
            </div></div></div>
        </div>

        <!-- Period / station filter bar -->
        <div class="panel_s"><div class="panel-body">
            <?php
            $periods = ['day' => _l('fleet_period_day'), 'week' => _l('fleet_period_week'), 'month' => _l('fleet_period_month'), 'year' => _l('fleet_period_year')];
            ?>
            <div class="btn-group fleet-period">
                <?php foreach ($periods as $key => $label) :
                    $active = ($period === $key && empty($specific_date)) ? 'btn-primary' : 'btn-default';
                    $url    = admin_url('fleet_management/fuel?period=' . $key . ($supplier_id ? '&supplier_id=' . $supplier_id : ''));
                    ?>
                    <a href="<?php echo $url; ?>" class="btn btn-sm <?php echo $active; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
            <span style="display:inline-block;margin-left:10px;">
                <input type="date" class="form-control input-sm" style="display:inline-block;width:auto;<?php echo !empty($specific_date) ? 'border-color:#6571ff;' : ''; ?>" value="<?php echo html_escape($specific_date); ?>" onchange="fleet_fuel_date_filter(this.value);" title="<?php echo _l('fleet_specific_date'); ?>">
            </span>
            <span class="text-muted" style="margin-left:6px;"><i class="fa fa-calendar"></i>
                <?php echo $filter_start === $filter_end ? _d($filter_start) : (_d($filter_start) . ' → ' . _d($filter_end)); ?>
            </span>
            <div class="pull-right" style="min-width:220px;">
                <select class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('fleet_all_stations'); ?>" onchange="fleet_fuel_station_filter(this.value);">
                    <option value=""><?php echo _l('fleet_all_stations'); ?></option>
                    <?php foreach ($suppliers as $s) : ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo ($supplier_id == $s['id']) ? 'selected' : ''; ?>><?php echo html_escape($s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div></div>

        <!-- Consumption per station -->
        <div class="panel_s"><div class="panel-body">
            <h4 class="bold no-margin"><i class="fa fa-flask text-info"></i> <?php echo _l('fleet_fuel_by_station'); ?></h4>
            <hr class="hr-panel-heading" />
            <?php if (!empty($by_station)) : ?>
                <div style="position:relative;height:240px;width:100%;margin-bottom:10px;"><canvas id="fuelStationChart"></canvas></div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr>
                        <th><?php echo _l('fleet_station'); ?></th>
                        <th class="text-right"><?php echo _l('fleet_fuel_entries'); ?></th>
                        <th class="text-right"><?php echo _l('fleet_fuel_total_liters'); ?></th>
                        <th class="text-right"><?php echo _l('fleet_fuel_total_cost'); ?></th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($by_station as $st) : ?>
                        <tr>
                            <td class="bold"><?php echo $st['supplier_name'] ? html_escape($st['supplier_name']) : ('<span class="text-muted">' . _l('fleet_no_station') . '</span>'); ?></td>
                            <td class="text-right"><?php echo (int) $st['entries']; ?></td>
                            <td class="text-right bold"><?php echo (float) $st['total_liters']; ?> L</td>
                            <td class="text-right"><?php echo app_format_money($st['total_cost'], get_base_currency()); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($by_station)) : ?><tr><td colspan="4" class="text-center text-muted"><?php echo _l('fleet_no_data'); ?></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div></div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="table-responsive">
                            <table class="table fleet-list">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('fleet_date'); ?></th>
                                        <th><?php echo _l('fleet_vehicle'); ?></th>
                                        <th><?php echo _l('fleet_driver'); ?></th>
                                        <th><?php echo _l('fleet_odometer'); ?></th>
                                        <th><?php echo _l('fleet_liters'); ?></th>
                                        <th><?php echo _l('fleet_price_per_liter'); ?></th>
                                        <th><?php echo _l('fleet_total'); ?></th>
                                        <th><?php echo _l('fleet_station'); ?></th>
                                        <th><?php echo _l('fleet_photos'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($logs as $f) : ?>
                                        <tr>
                                            <td><?php echo _d($f['date']); ?></td>
                                            <td><?php echo html_escape($f['vehicle_name']); ?></td>
                                            <td><?php echo html_escape($f['driver_name']); ?></td>
                                            <td><?php echo $f['odometer'] ? (int) $f['odometer'] . ' km' : '-'; ?></td>
                                            <td><?php echo (float) $f['liters']; ?> L</td>
                                            <td><?php echo $f['price_per_liter'] ? app_format_money($f['price_per_liter'], get_base_currency()) : '-'; ?></td>
                                            <td><?php echo app_format_money($f['total_cost'], get_base_currency()); ?></td>
                                            <td><?php echo html_escape($f['supplier_name']); ?></td>
                                            <td>
                                                <?php if (!empty($f['photos_count'])) : ?>
                                                    <a href="#" onclick="fleet_fuel_modal(<?php echo $f['id']; ?>); return false;" class="label label-info"><i class="fa fa-camera"></i> <?php echo (int) $f['photos_count']; ?></a>
                                                <?php else : ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if (staff_can('edit', 'fleet')) : ?>
                                                    <a href="#" class="btn btn-default btn-icon" onclick="fleet_fuel_modal(<?php echo $f['id']; ?>); return false;"><i class="fa fa-pencil-square"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/fuel/delete/' . $f['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                    <?php if (empty($logs)) : ?>
                                        <tr><td colspan="10" class="text-center text-muted" style="padding:24px;"><?php echo _l('fleet_no_data'); ?></td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fleet_fuel_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <?php echo form_open_multipart(admin_url('fleet_management/fuel/save')); ?>
            <input type="hidden" name="id" id="fuel_id" value="">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('fleet_fuel_log'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6"><?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?></div>
                    <div class="col-md-6"><?php echo render_select('driver_id', $driver_options, ['id', 'name'], 'fleet_driver'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_date_input('date', 'fleet_date', _d(date('Y-m-d'))); ?></div>
                    <div class="col-md-6"><?php echo render_input('odometer', 'fleet_odometer', '', 'number'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-4"><?php echo render_input('liters', 'fleet_liters', '', 'number'); ?></div>
                    <div class="col-md-4"><?php echo render_input('price_per_liter', 'fleet_price_per_liter', '', 'number'); ?></div>
                    <div class="col-md-4"><?php echo render_input('total_cost', 'fleet_total_cost', '', 'number'); ?></div>
                </div>
                <div class="row">
                    <div class="col-md-6"><?php echo render_select('fuel_type', $fuel_options, ['id', 'name'], 'fleet_fuel_type'); ?></div>
                    <div class="col-md-6"><?php echo render_select('supplier_id', $supplier_options, ['id', 'name'], 'fleet_station'); ?></div>
                </div>
                <div class="checkbox checkbox-primary">
                    <input type="checkbox" name="full_tank" id="fuel_full_tank" value="1" checked>
                    <label for="fuel_full_tank"><?php echo _l('fleet_full_tank'); ?></label>
                </div>
                <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
                <p class="text-muted"><small><?php echo _l('fleet_fuel_total_hint'); ?></small></p>

                <div class="form-group">
                    <label class="control-label"><?php echo _l('fleet_photos'); ?></label>
                    <input type="file" name="files[]" accept="image/*" multiple class="form-control">
                </div>
                <div id="fuel_photos" class="fleet-insp-photos"></div>
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
<style>
#fuel_photos{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px;}
#fuel_photos .fuel-thumb{position:relative;width:78px;height:78px;border-radius:8px;overflow:hidden;border:1px solid #e6e9f0;}
#fuel_photos .fuel-thumb img{width:100%;height:100%;object-fit:cover;}
#fuel_photos .fuel-thumb .del{position:absolute;top:2px;right:2px;background:#dc3545;color:#fff;width:18px;height:18px;line-height:18px;text-align:center;border-radius:50%;font-size:10px;}
</style>
<script>
function fleet_fuel_station_filter(supplierId) {
    var url = '<?php echo admin_url('fleet_management/fuel'); ?>?period=<?php echo $period; ?>';
    <?php if (!empty($specific_date)) : ?>url += '&date=<?php echo $specific_date; ?>';<?php endif; ?>
    if (supplierId) { url += '&supplier_id=' + supplierId; }
    window.location.href = url;
}
function fleet_fuel_date_filter(date) {
    var url = '<?php echo admin_url('fleet_management/fuel'); ?>?';
    if (date) { url += 'date=' + date; } else { url += 'period=<?php echo $period; ?>'; }
    <?php if ($supplier_id) : ?>url += '&supplier_id=<?php echo $supplier_id; ?>';<?php endif; ?>
    window.location.href = url;
}
$(function() {
    // Litres-per-station bar chart for the selected period.
    if (typeof Chart !== 'undefined') {
        var sc = document.getElementById('fuelStationChart');
        if (sc) {
            var isV3 = !!(Chart.version && parseInt(Chart.version, 10) >= 3);
            var scales = isV3 ? { y: { beginAtZero: true } } : { yAxes: [{ ticks: { beginAtZero: true } }] };
            new Chart(sc, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_map(function ($s) { return $s['supplier_name'] ?: _l('fleet_no_station'); }, $by_station)); ?>,
                    datasets: [{
                        label: '<?php echo _l('fleet_liters'); ?>',
                        backgroundColor: '#03c3ec',
                        borderRadius: 4,
                        data: <?php echo json_encode(array_map(function ($s) { return (float) $s['total_liters']; }, $by_station)); ?>
                    }]
                },
                options: { responsive: true, maintainAspectRatio: false, scales: scales, plugins: { legend: { display: false } }, legend: { display: false } }
            });
        }
    }

    var $f = $('#fleet_fuel_modal');

    // Mark a field as manually edited so it is not overwritten by auto-compute.
    $f.on('input', '[name="liters"]', function() { $(this).data('touched', true); fleet_fuel_recompute(); });
    $f.on('input', '[name="price_per_liter"]', function() { $(this).data('touched', true); fleet_fuel_recompute(); });
    $f.on('input', '[name="total_cost"]', function() { $(this).data('touched', true); fleet_fuel_recompute(); });

    // Fill whichever of liters / price / total the user did not type, so the
    // liters value can be derived from "amount paid" + "price per liter".
    window.fleet_fuel_recompute = function() {
        var lF = $f.find('[name="liters"]'), pF = $f.find('[name="price_per_liter"]'), tF = $f.find('[name="total_cost"]');
        var l = parseFloat(lF.val()), p = parseFloat(pF.val()), t = parseFloat(tF.val());

        if (!lF.data('touched') && p > 0 && !isNaN(t)) { lF.val((t / p).toFixed(2)); return; }
        if (!tF.data('touched') && !isNaN(l) && !isNaN(p)) { tF.val((l * p).toFixed(2)); return; }
        if (!pF.data('touched') && l > 0 && !isNaN(t)) { pF.val((t / l).toFixed(3)); return; }
    };
});
function fleet_fuel_render_photos(files) {
    var box = $('#fuel_photos').empty();
    (files || []).forEach(function(f) {
        box.append(
            '<div class="fuel-thumb">' +
            '<a href="' + f.url + '" target="_blank"><img src="' + f.url + '"></a>' +
            '<a href="' + f.del + '" class="del _delete"><i class="fa fa-remove"></i></a>' +
            '</div>'
        );
    });
}
function fleet_fuel_modal(id) {
    var modal = $('#fleet_fuel_modal');
    modal.find('form')[0].reset();
    $('#fuel_id').val('');
    $('#fuel_full_tank').prop('checked', true);
    fleet_fuel_render_photos([]);
    modal.find('[name="liters"], [name="price_per_liter"], [name="total_cost"]').data('touched', false);
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/fuel/get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#fuel_id').val(rec.id);
            modal.find('[name="date"]').val(rec.date_display || '');
            modal.find('[name="vehicle_id"]').val(rec.vehicle_id);
            modal.find('[name="driver_id"]').val(rec.driver_id);
            modal.find('[name="odometer"]').val(rec.odometer);
            modal.find('[name="liters"]').val(rec.liters).data('touched', true);
            modal.find('[name="price_per_liter"]').val(rec.price_per_liter).data('touched', true);
            modal.find('[name="total_cost"]').val(rec.total_cost).data('touched', true);
            modal.find('[name="fuel_type"]').val(rec.fuel_type);
            modal.find('[name="supplier_id"]').val(rec.supplier_id);
            modal.find('[name="notes"]').val(rec.notes);
            $('#fuel_full_tank').prop('checked', rec.full_tank == 1);
            fleet_fuel_render_photos(rec.files);
            if (modal.find('.selectpicker').length) {
                modal.find('.selectpicker').selectpicker('refresh');
            }
        });
    }
    modal.modal('show');
}
<?php if ($this->input->get('open')) : ?>
$(function() {
    fleet_fuel_modal();
    <?php if ($this->input->get('vehicle_id')) : ?>
    var pm = $('#fleet_fuel_modal');
    pm.find('[name="vehicle_id"]').val('<?php echo (int) $this->input->get('vehicle_id'); ?>');
    if (pm.find('.selectpicker').length) { pm.find('.selectpicker').selectpicker('refresh'); }
    <?php endif; ?>
});
<?php endif; ?>
</script>
</body>
</html>
