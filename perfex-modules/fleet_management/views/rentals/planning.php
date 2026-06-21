<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$days_in_month = (int) date('t', strtotime($range_start));
$year_month    = date('F Y', strtotime($range_start));
$today         = date('Y-m-d');

$status_colors = [
    'reserved'  => '#ffab00',
    'ongoing'   => '#03c3ec',
    'completed' => '#71dd37',
];

// Map each vehicle to a day => rental lookup for the visible month.
$by_vehicle = [];
foreach ($rentals as $r) {
    $start = max(strtotime($r['date_start']), strtotime($range_start));
    $end   = min(strtotime($r['date_end']), strtotime($range_end));
    for ($t = $start; $t <= $end; $t += 86400) {
        $day = (int) date('j', $t);
        $by_vehicle[$r['vehicle_id']][$day] = $r;
    }
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-calendar text-info"></i> <?php echo _l('fleet_planning'); ?></h3>
            <div class="fleet-tools">
                <a href="<?php echo admin_url('fleet_management/rentals'); ?>" class="btn btn-default btn-sm"><i class="fa fa-list"></i> <?php echo _l('fleet_rentals'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="<?php echo admin_url('fleet_management/rentals/rental'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_rental'); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <div class="panel_s"><div class="panel-body">
            <div class="clearfix mbot15">
                <div class="btn-group">
                    <a href="<?php echo admin_url('fleet_management/rentals/planning?month=' . $prev_month); ?>" class="btn btn-default btn-sm"><i class="fa fa-angle-left"></i></a>
                    <a href="<?php echo admin_url('fleet_management/rentals/planning?month=' . date('Y-m')); ?>" class="btn btn-default btn-sm"><?php echo _l('fleet_period_month'); ?></a>
                    <a href="<?php echo admin_url('fleet_management/rentals/planning?month=' . $next_month); ?>" class="btn btn-default btn-sm"><i class="fa fa-angle-right"></i></a>
                </div>
                <span class="bold" style="font-size:16px;margin-left:12px;text-transform:capitalize;"><?php echo $year_month; ?></span>
                <div class="pull-right fleet-legend">
                    <span><i style="background:<?php echo $status_colors['reserved']; ?>"></i> <?php echo _l('fleet_status_reserved'); ?></span>
                    <span><i style="background:<?php echo $status_colors['ongoing']; ?>"></i> <?php echo _l('fleet_status_ongoing'); ?></span>
                    <span><i style="background:<?php echo $status_colors['completed']; ?>"></i> <?php echo _l('fleet_status_completed'); ?></span>
                </div>
            </div>

            <div class="table-responsive fleet-planning-wrap">
                <table class="fleet-planning">
                    <thead>
                        <tr>
                            <th class="veh-col"><?php echo _l('fleet_vehicle'); ?></th>
                            <?php for ($d = 1; $d <= $days_in_month; $d++) :
                                $date = date('Y-m-d', strtotime($range_start . ' +' . ($d - 1) . ' day'));
                                $dow  = date('N', strtotime($date));
                                $cls  = ($dow >= 6 ? 'we' : '') . ($date === $today ? ' today' : '');
                                ?>
                                <th class="day-col <?php echo $cls; ?>"><?php echo $d; ?><br><small><?php echo date('D', strtotime($date)); ?></small></th>
                            <?php endfor; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vehicles as $v) : ?>
                            <tr>
                                <td class="veh-col">
                                    <span class="bold"><?php echo html_escape($v['name']); ?></span><br>
                                    <span class="fleet-plate"><?php echo html_escape($v['plate']); ?></span>
                                </td>
                                <?php for ($d = 1; $d <= $days_in_month; $d++) :
                                    $date = date('Y-m-d', strtotime($range_start . ' +' . ($d - 1) . ' day'));
                                    $dow  = date('N', strtotime($date));
                                    $cls  = ($dow >= 6 ? 'we' : '') . ($date === $today ? ' today' : '');
                                    $rent = $by_vehicle[$v['id']][$d] ?? null;

                                    if ($rent) {
                                        $color   = $status_colors[$rent['status']] ?? '#6571ff';
                                        $is_start = (date('j', max(strtotime($rent['date_start']), strtotime($range_start))) == $d);
                                        $tip      = trim(($rent['client_name'] ?: _l('fleet_rental') . ' #' . $rent['id']))
                                            . ' · ' . _d($rent['date_start']) . ' → ' . _d($rent['date_end']);
                                        ?>
                                        <td class="day-col <?php echo $cls; ?> booked" style="--c:<?php echo $color; ?>" title="<?php echo html_escape($tip); ?>">
                                            <a href="<?php echo admin_url('fleet_management/rentals/rental/' . $rent['id']); ?>" class="bar" style="background:<?php echo $color; ?>">
                                                <?php echo $is_start ? html_escape(mb_substr($rent['client_name'] ?: ('#' . $rent['id']), 0, 10)) : '&nbsp;'; ?>
                                            </a>
                                        </td>
                                    <?php } else { ?>
                                        <td class="day-col <?php echo $cls; ?>"></td>
                                    <?php } ?>
                                <?php endfor; ?>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($vehicles)) : ?>
                            <tr><td class="veh-col">—</td><td colspan="<?php echo $days_in_month; ?>" class="text-center text-muted"><?php echo _l('fleet_no_vehicles'); ?></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
<style>
.fleet-planning-wrap{overflow-x:auto;}
.fleet-planning{border-collapse:collapse;width:100%;font-size:12px;}
.fleet-planning th,.fleet-planning td{border:1px solid #eef0f4;text-align:center;padding:0;height:34px;}
.fleet-planning th.day-col,.fleet-planning td.day-col{width:30px;min-width:30px;}
.fleet-planning th.day-col{font-weight:600;color:#97a1b3;font-size:11px;line-height:1.1;padding:3px 0;}
.fleet-planning th.day-col small{font-weight:400;font-size:9px;}
.fleet-planning .veh-col{width:180px;min-width:180px;text-align:left;padding:6px 10px;position:sticky;left:0;background:#fff;z-index:2;}
.fleet-planning thead .veh-col{z-index:3;}
.fleet-planning .we{background:#f7f8fb;}
.fleet-planning .today{background:#eef3ff;}
.fleet-planning td.booked{padding:2px;}
.fleet-planning .bar{display:block;height:26px;line-height:26px;border-radius:5px;color:#fff;font-size:10px;font-weight:600;white-space:nowrap;overflow:hidden;text-decoration:none;}
.fleet-legend span{margin-left:14px;color:#697a8d;font-size:12px;}
.fleet-legend i{display:inline-block;width:12px;height:12px;border-radius:3px;vertical-align:middle;margin-right:4px;}
.fleet-list-page .fleet-plate{display:inline-block;background:#f0f2f5;border-radius:6px;padding:1px 6px;font-weight:600;font-size:11px;}
</style>
<?php init_tail(); ?>
</body>
</html>
