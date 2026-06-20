<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $bc = get_base_currency(); ?>
<?php init_head(); ?>
<style>
.fleet-dash .panel_s { border-radius: 10px; box-shadow: 0 2px 10px rgba(20,30,60,.05); border: 0; }
.fleet-dash .panel_s .panel-body { padding: 18px 20px; }
.fleet-kpi { display: flex; align-items: center; }
.fleet-kpi .fleet-ic { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 22px; color: #fff; margin-right: 14px; flex: 0 0 52px; }
.fleet-kpi .fleet-meta h2 { margin: 0; font-size: 23px; font-weight: 700; line-height: 1.1; }
.fleet-kpi .fleet-meta span { color: #97a1b3; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
.fleet-split-tile { display: flex; align-items: center; }
.fleet-split-tile .fleet-dot { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 16px; margin-right: 12px; }
.fleet-split-tile .v { font-size: 17px; font-weight: 700; line-height: 1; }
.fleet-split-tile .l { color: #97a1b3; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
.fleet-highlight { display: flex; align-items: center; }
.fleet-highlight .fleet-trophy { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; color: #fff; margin-right: 14px; }
.fleet-highlight .fleet-hl-label { color: #97a1b3; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; margin: 0; }
.fleet-highlight h4 { margin: 2px 0 0; font-weight: 700; }
.fleet-dash table.fleet-vtable > tbody > tr > td { vertical-align: middle; }
.fleet-dash .fleet-occ { height: 8px; border-radius: 6px; background: #eef0f5; overflow: hidden; }
.fleet-dash .fleet-occ > span { display: block; height: 100%; border-radius: 6px; }
.fleet-period .btn { border-radius: 8px; }
.fleet-chip { display:inline-block; width:10px; height:10px; border-radius:50%; margin-right:5px; }
</style>
<div id="wrapper">
    <div class="content fleet-dash">

        <!-- Header + period filter -->
        <div class="row mbot15">
            <div class="col-md-6">
                <h3 class="bold no-margin" style="margin-top:6px;"><i class="fa fa-dashboard text-info"></i> <?php echo _l('fleet_dashboard'); ?></h3>
            </div>
            <div class="col-md-6 text-right">
                <div class="btn-group fleet-period" role="group">
                    <?php
                    $periods = ['month' => _l('fleet_period_month'), 'quarter' => _l('fleet_period_quarter'), 'year' => _l('fleet_period_year'), 'all' => _l('fleet_period_all')];
                    foreach ($periods as $key => $label) :
                        $active = $period === $key ? 'btn-primary' : 'btn-default';
                        ?>
                        <a href="<?php echo admin_url('fleet_management/dashboard?period=' . $key); ?>" class="btn btn-sm <?php echo $active; ?>"><?php echo $label; ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body fleet-kpi">
                    <div class="fleet-ic" style="background:#6571ff;"><i class="fa fa-credit-card"></i></div>
                    <div class="fleet-meta"><h2><?php echo app_format_money($totals['total'], $bc); ?></h2><span><?php echo _l('fleet_dash_total_cost'); ?></span></div>
                </div></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body fleet-kpi">
                    <div class="fleet-ic" style="background:#03c3ec;"><i class="fa fa-car"></i></div>
                    <div class="fleet-meta"><h2><?php echo array_sum($status_counts); ?></h2><span><?php echo _l('fleet_vehicles'); ?></span></div>
                </div></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body fleet-kpi">
                    <div class="fleet-ic" style="background:#71dd37;"><i class="fa fa-check"></i></div>
                    <div class="fleet-meta"><h2><?php echo (int) ($status_counts['available'] ?? 0); ?></h2><span><?php echo _l('fleet_status_available'); ?></span></div>
                </div></div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="panel_s"><div class="panel-body fleet-kpi">
                    <div class="fleet-ic" style="background:#ffab00;"><i class="fa fa-tachometer"></i></div>
                    <div class="fleet-meta"><h2><?php echo (int) $fleet_occupancy; ?>%</h2><span><?php echo _l('fleet_dash_occupancy', $occupancy_days); ?></span></div>
                </div></div>
            </div>
        </div>

        <!-- Highlights -->
        <div class="row">
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body fleet-highlight">
                    <div class="fleet-trophy" style="background:#03c3ec;"><i class="fa fa-trophy"></i></div>
                    <div>
                        <p class="fleet-hl-label"><?php echo _l('fleet_dash_most_used'); ?></p>
                        <?php if (!empty($highlights['top_used'])) : $tu = $highlights['top_used']; ?>
                            <h4><a href="<?php echo admin_url('fleet_management/vehicles/view/' . $tu['vehicle']['id']); ?>"><?php echo html_escape($tu['vehicle']['name']); ?></a></h4>
                            <span class="text-success bold"><?php echo (int) $tu['occupancy']; ?>%</span> <span class="text-muted"><?php echo _l('fleet_dash_occupancy_short'); ?></span>
                        <?php else : ?><h4 class="text-muted">—</h4><?php endif; ?>
                    </div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body fleet-highlight">
                    <div class="fleet-trophy" style="background:#ff3e1d;"><i class="fa fa-money"></i></div>
                    <div>
                        <p class="fleet-hl-label"><?php echo _l('fleet_dash_most_expensive'); ?></p>
                        <?php if (!empty($highlights['top_cost'])) : $tc = $highlights['top_cost']; ?>
                            <h4><a href="<?php echo admin_url('fleet_management/vehicles/view/' . $tc['vehicle']['id']); ?>"><?php echo html_escape($tc['vehicle']['name']); ?></a></h4>
                            <span class="text-danger bold"><?php echo app_format_money($tc['total'], $bc); ?></span>
                        <?php else : ?><h4 class="text-muted">—</h4><?php endif; ?>
                    </div>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body fleet-highlight">
                    <div class="fleet-trophy" style="background:#71dd37;"><i class="fa fa-tint"></i></div>
                    <div>
                        <p class="fleet-hl-label"><?php echo _l('fleet_dash_most_fuel'); ?></p>
                        <?php if (!empty($highlights['top_fuel'])) : $tf = $highlights['top_fuel']; ?>
                            <h4><a href="<?php echo admin_url('fleet_management/vehicles/view/' . $tf['vehicle']['id']); ?>"><?php echo html_escape($tf['vehicle']['name']); ?></a></h4>
                            <span class="text-success bold"><?php echo app_format_money($tf['fuel'], $bc); ?></span>
                        <?php else : ?><h4 class="text-muted">—</h4><?php endif; ?>
                    </div>
                </div></div>
            </div>
        </div>

        <!-- Charts: evolution + cost split -->
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold no-margin"><?php echo _l('fleet_dash_expense_evolution'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <canvas id="fleetExpenseChart" height="120"></canvas>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold no-margin"><?php echo _l('fleet_dash_cost_split'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <canvas id="fleetSplitChart" height="150"></canvas>
                    <div class="mtop15">
                        <div class="clearfix mbot5"><span class="fleet-chip" style="background:#f0ad4e;"></span> <?php echo _l('fleet_maintenance'); ?> <span class="pull-right bold"><?php echo app_format_money($totals['maintenance'], $bc); ?></span></div>
                        <div class="clearfix mbot5"><span class="fleet-chip" style="background:#5cb85c;"></span> <?php echo _l('fleet_fuel'); ?> <span class="pull-right bold"><?php echo app_format_money($totals['fuel'], $bc); ?></span></div>
                        <div class="clearfix mbot5"><span class="fleet-chip" style="background:#337ab7;"></span> <?php echo _l('fleet_parts_articles'); ?> <span class="pull-right bold"><?php echo app_format_money($totals['parts'], $bc); ?></span></div>
                        <div class="clearfix"><span class="fleet-chip" style="background:#d9534f;"></span> <?php echo _l('fleet_reminders'); ?> <span class="pull-right bold"><?php echo app_format_money($totals['reminders'], $bc); ?></span></div>
                    </div>
                </div></div>
            </div>
        </div>

        <!-- Per-vehicle breakdown -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold no-margin"><?php echo _l('fleet_dash_per_vehicle'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="table-responsive">
                        <table class="table fleet-vtable">
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
                                    <th style="min-width:130px;"><?php echo _l('fleet_dash_occupancy_short'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row) : $v = $row['vehicle']; $occ = (int) $row['occupancy'];
                                    $occColor = $occ >= 66 ? '#71dd37' : ($occ >= 33 ? '#ffab00' : '#ff3e1d'); ?>
                                    <tr>
                                        <td>
                                            <a href="<?php echo admin_url('fleet_management/vehicles/view/' . $v['id']); ?>" class="bold"><?php echo html_escape($v['name']); ?></a>
                                            <br><small class="text-muted"><?php echo html_escape($v['plate']); ?></small>
                                        </td>
                                        <td><?php echo (int) $v['odometer']; ?> km</td>
                                        <td><?php echo app_format_money($row['maintenance'], $bc); ?></td>
                                        <td><?php echo app_format_money($row['fuel'], $bc); ?></td>
                                        <td><?php echo app_format_money($row['parts'], $bc); ?></td>
                                        <td><?php echo app_format_money($row['reminders'], $bc); ?></td>
                                        <td class="bold"><?php echo app_format_money($row['total'], $bc); ?></td>
                                        <td><?php echo $row['cost_per_km'] > 0 ? app_format_money($row['cost_per_km'], $bc) . '/km' : '<span class="text-muted">-</span>'; ?></td>
                                        <td>
                                            <div class="fleet-occ"><span style="width:<?php echo $occ; ?>%;background:<?php echo $occColor; ?>;"></span></div>
                                            <small class="text-muted"><?php echo $occ; ?>%</small>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rows)) : ?>
                                    <tr><td colspan="9" class="text-center text-muted"><?php echo _l('fleet_history_empty'); ?></td></tr>
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
<script>
$(function() {
    if (typeof Chart === 'undefined') { return; }
    var isV3 = !!(Chart.version && parseInt(Chart.version, 10) >= 3);

    var bar = document.getElementById('fleetExpenseChart');
    if (bar) {
        var scales = isV3
            ? { x: { stacked: true }, y: { stacked: true, beginAtZero: true } }
            : { xAxes: [{ stacked: true }], yAxes: [{ stacked: true, ticks: { beginAtZero: true } }] };
        new Chart(bar, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($series['labels']); ?>,
                datasets: [
                    { label: '<?php echo _l('fleet_maintenance'); ?>', backgroundColor: '#f0ad4e', borderRadius: 4, data: <?php echo json_encode($series['maintenance']); ?> },
                    { label: '<?php echo _l('fleet_fuel'); ?>', backgroundColor: '#5cb85c', borderRadius: 4, data: <?php echo json_encode($series['fuel']); ?> },
                    { label: '<?php echo _l('fleet_parts_articles'); ?>', backgroundColor: '#337ab7', borderRadius: 4, data: <?php echo json_encode($series['parts']); ?> },
                    { label: '<?php echo _l('fleet_reminders'); ?>', backgroundColor: '#d9534f', borderRadius: 4, data: <?php echo json_encode($series['reminders']); ?> }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, scales: scales, plugins: { legend: { position: 'bottom' } }, legend: { position: 'bottom' } }
        });
    }

    var split = document.getElementById('fleetSplitChart');
    if (split) {
        new Chart(split, {
            type: 'doughnut',
            data: {
                labels: ['<?php echo _l('fleet_maintenance'); ?>', '<?php echo _l('fleet_fuel'); ?>', '<?php echo _l('fleet_parts_articles'); ?>', '<?php echo _l('fleet_reminders'); ?>'],
                datasets: [{
                    backgroundColor: ['#f0ad4e', '#5cb85c', '#337ab7', '#d9534f'],
                    data: [<?php echo (float) $totals['maintenance']; ?>, <?php echo (float) $totals['fuel']; ?>, <?php echo (float) $totals['parts']; ?>, <?php echo (float) $totals['reminders']; ?>]
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, cutout: '62%', cutoutPercentage: 62, plugins: { legend: { display: false } }, legend: { display: false } }
        });
    }
});
</script>
</body>
</html>
