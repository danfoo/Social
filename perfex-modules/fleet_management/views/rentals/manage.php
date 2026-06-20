<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$bc            = get_base_currency();
$total_rentals = count($rentals);
$counts        = ['reserved' => 0, 'ongoing' => 0, 'completed' => 0, 'cancelled' => 0];
$revenue       = 0;
foreach ($rentals as $r) {
    if (isset($counts[$r['status']])) {
        $counts[$r['status']]++;
    }
    if ($r['status'] !== 'cancelled') {
        $revenue += (float) $r['total'];
    }
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">

        <!-- Toolbar -->
        <div class="fleet-toolbar">
            <h3><i class="fa fa-calendar text-info"></i> <?php echo _l('fleet_rentals'); ?></h3>
            <div class="fleet-tools">
                <input type="text" class="form-control input-sm fleet-search" placeholder="<?php echo _l('fleet_search'); ?>" style="display:inline-block;width:auto;min-width:240px;">
                <a href="<?php echo admin_url('fleet_management/rentals/export'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="<?php echo admin_url('fleet_management/rentals/rental'); ?>" class="btn btn-primary btn-sm"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_rental'); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="row">
            <div class="col-md-3 col-sm-6"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#6571ff;"><i class="fa fa-calendar"></i></div>
                <div><h2><?php echo $total_rentals; ?></h2><span><?php echo _l('fleet_rentals'); ?></span></div>
            </div></div></div>
            <div class="col-md-3 col-sm-6"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#03c3ec;"><i class="fa fa-key"></i></div>
                <div><h2><?php echo (int) $counts['ongoing']; ?></h2><span><?php echo _l('fleet_status_ongoing'); ?></span></div>
            </div></div></div>
            <div class="col-md-3 col-sm-6"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#ffab00;"><i class="fa fa-clock-o"></i></div>
                <div><h2><?php echo (int) $counts['reserved']; ?></h2><span><?php echo _l('fleet_status_reserved'); ?></span></div>
            </div></div></div>
            <div class="col-md-3 col-sm-6"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#71dd37;"><i class="fa fa-money"></i></div>
                <div><h2><?php echo app_format_money($revenue, $bc); ?></h2><span><?php echo _l('fleet_total'); ?></span></div>
            </div></div></div>
        </div>

        <!-- Rentals table -->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s"><div class="panel-body">
                    <div class="table-responsive">
                        <table class="table fleet-list">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo _l('fleet_vehicle'); ?></th>
                                    <th><?php echo _l('client'); ?></th>
                                    <th><?php echo _l('fleet_period'); ?></th>
                                    <th><?php echo _l('fleet_days'); ?></th>
                                    <th><?php echo _l('fleet_total'); ?></th>
                                    <th><?php echo _l('fleet_status'); ?></th>
                                    <th><?php echo _l('invoice'); ?></th>
                                    <th class="text-right"><?php echo _l('options'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rentals as $r) : ?>
                                    <tr>
                                        <td class="text-muted">#<?php echo $r['id']; ?></td>
                                        <td>
                                            <div class="fleet-veh">
                                                <span class="av"><i class="fa fa-car"></i></span>
                                                <div>
                                                    <span class="bold"><?php echo html_escape($r['vehicle_name']); ?></span>
                                                    <br><small class="fleet-plate"><?php echo html_escape($r['vehicle_plate']); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo $r['client_name'] ? html_escape($r['client_name']) : '<span class="text-muted">—</span>'; ?></td>
                                        <td><i class="fa fa-calendar-o text-muted"></i> <?php echo _d($r['date_start']); ?> <span class="text-muted">&rarr;</span> <?php echo _d($r['date_end']); ?></td>
                                        <td><?php echo (int) $r['days']; ?></td>
                                        <td class="bold"><?php echo app_format_money($r['total'], $bc); ?></td>
                                        <td><?php echo fleet_rental_status_badge($r['status']); ?></td>
                                        <td>
                                            <?php if (!empty($r['invoice_id'])) : ?>
                                                <a href="<?php echo admin_url('invoices/list_invoices/' . $r['invoice_id']); ?>"><?php echo format_invoice_number($r['invoice_id']); ?></a>
                                            <?php else : ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-right">
                                            <a href="<?php echo admin_url('fleet_management/rentals/rental/' . $r['id']); ?>" class="btn btn-default btn-icon btn-sm"><i class="fa fa-pencil-square-o"></i></a>
                                            <?php if (empty($r['invoice_id']) && staff_can('create', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/rentals/create_invoice/' . $r['id']); ?>" class="btn btn-success btn-icon btn-sm" title="<?php echo _l('fleet_create_invoice'); ?>"><i class="fa fa-file-text-o"></i></a>
                                            <?php endif; ?>
                                            <?php if (staff_can('delete', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/rentals/delete/' . $r['id']); ?>" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-remove"></i></a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (empty($rentals)) : ?>
                                    <tr><td colspan="9" class="text-center text-muted" style="padding:30px;"><i class="fa fa-calendar fa-2x"></i><br><?php echo _l('fleet_add_rental'); ?></td></tr>
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
