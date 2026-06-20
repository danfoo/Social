<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (staff_can('create', 'fleet')) : ?>
                            <a href="<?php echo admin_url('fleet_management/rentals/rental'); ?>" class="btn btn-primary mbot15">
                                <i class="fa fa-plus"></i> <?php echo _l('fleet_add_rental'); ?>
                            </a>
                        <?php endif; ?>
                        <div class="table-responsive">
                            <table class="table">
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
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($rentals as $r) : ?>
                                        <tr>
                                            <td><?php echo $r['id']; ?></td>
                                            <td><?php echo html_escape($r['vehicle_name']); ?> <small class="text-muted">(<?php echo html_escape($r['vehicle_plate']); ?>)</small></td>
                                            <td><?php echo html_escape($r['client_name']); ?></td>
                                            <td><?php echo _d($r['date_start']); ?> → <?php echo _d($r['date_end']); ?></td>
                                            <td><?php echo (int) $r['days']; ?></td>
                                            <td><?php echo app_format_money($r['total'], get_base_currency()); ?></td>
                                            <td><?php echo fleet_rental_status_badge($r['status']); ?></td>
                                            <td>
                                                <?php if (!empty($r['invoice_id'])) : ?>
                                                    <a href="<?php echo admin_url('invoices/list_invoices/' . $r['invoice_id']); ?>"><?php echo format_invoice_number($r['invoice_id']); ?></a>
                                                <?php else : ?>
                                                    <span class="text-muted">—</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo admin_url('fleet_management/rentals/rental/' . $r['id']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
                                                <?php if (empty($r['invoice_id']) && staff_can('create', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/rentals/create_invoice/' . $r['id']); ?>" class="btn btn-success btn-icon" title="<?php echo _l('fleet_create_invoice'); ?>"><i class="fa fa-file-text-o"></i></a>
                                                <?php endif; ?>
                                                <?php if (staff_can('delete', 'fleet')) : ?>
                                                    <a href="<?php echo admin_url('fleet_management/rentals/delete/' . $r['id']); ?>" class="btn btn-danger btn-icon _delete"><i class="fa fa-remove"></i></a>
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
