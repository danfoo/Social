<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $bc = get_base_currency(); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="mbot15 clearfix">
            <a href="<?php echo admin_url('fleet_management/suppliers'); ?>" class="btn btn-default btn-sm"><i class="fa fa-arrow-left"></i> <?php echo _l('fleet_suppliers'); ?></a>
            <div class="btn-group fleet-period pull-right" style="margin-left:8px;">
                <?php
                $periods = ['month' => _l('fleet_period_month'), 'quarter' => _l('fleet_period_quarter'), 'year' => _l('fleet_period_year'), 'all' => _l('fleet_period_all')];
                foreach ($periods as $key => $label) :
                    $active = $period === $key ? 'btn-primary' : 'btn-default';
                    ?>
                    <a href="<?php echo admin_url('fleet_management/suppliers/view/' . $supplier->id . '?period=' . $key); ?>" class="btn btn-sm <?php echo $active; ?>"><?php echo $label; ?></a>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo admin_url('fleet_management/suppliers/export_ledger/' . $supplier->id . '?period=' . $period); ?>" class="btn btn-default btn-sm pull-right"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
        </div>

        <div class="row">
            <!-- Supplier info -->
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body">
                    <h3 class="bold no-margin"><?php echo html_escape($supplier->name); ?></h3>
                    <p class="text-muted"><?php echo _l('fleet_stype_' . $supplier->type); ?></p>
                    <table class="table table-borderless no-margin">
                        <tr><td class="bold"><?php echo _l('fleet_contact_name'); ?></td><td><?php echo html_escape($supplier->contact_name); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_phone'); ?></td><td><?php echo html_escape($supplier->phone); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('email'); ?></td><td><?php echo html_escape($supplier->email); ?></td></tr>
                        <tr><td class="bold"><?php echo _l('fleet_vat'); ?></td><td><?php echo html_escape($supplier->vat); ?></td></tr>
                    </table>
                </div></div>
            </div>
            <!-- Accounting summary -->
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4"><div class="panel_s"><div class="panel-body fleet-stat">
                        <div class="ic" style="background:#6571ff;"><i class="fa fa-money"></i></div>
                        <div><h2><?php echo app_format_money($summary->total, $bc); ?></h2><span><?php echo _l('fleet_acc_total'); ?></span></div>
                    </div></div></div>
                    <div class="col-md-4"><div class="panel_s"><div class="panel-body fleet-stat">
                        <div class="ic" style="background:#71dd37;"><i class="fa fa-check"></i></div>
                        <div><h2><?php echo app_format_money($summary->paid, $bc); ?></h2><span><?php echo _l('fleet_acc_paid'); ?></span></div>
                    </div></div></div>
                    <div class="col-md-4"><div class="panel_s"><div class="panel-body fleet-stat">
                        <div class="ic" style="background:#ff3e1d;"><i class="fa fa-exclamation-circle"></i></div>
                        <div><h2><?php echo app_format_money($summary->unpaid, $bc); ?></h2><span><?php echo _l('fleet_acc_unpaid'); ?></span></div>
                    </div></div></div>
                </div>

                <!-- Part orders -->
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold no-margin"><i class="fa fa-shopping-cart"></i> <?php echo _l('fleet_supplier_orders'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr>
                                <th><?php echo _l('fleet_part'); ?></th>
                                <th><?php echo _l('fleet_quantity'); ?></th>
                                <th><?php echo _l('fleet_total'); ?></th>
                                <th><?php echo _l('fleet_status'); ?></th>
                                <th><?php echo _l('fleet_supplier_invoice_no'); ?></th>
                                <th><?php echo _l('expense'); ?></th>
                                <th><?php echo _l('fleet_payment'); ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($orders as $o) : ?>
                                <tr>
                                    <td class="bold"><?php echo html_escape($o['item_name']); ?></td>
                                    <td><?php echo (int) $o['quantity']; ?></td>
                                    <td><?php echo app_format_money($o['total_price'], $bc); ?></td>
                                    <td><?php echo _l('fleet_ostatus_' . $o['status']); ?></td>
                                    <td><?php echo html_escape($o['invoice_no']); ?></td>
                                    <td><?php echo !empty($o['expense_id']) ? '<a href="' . admin_url('expenses/list_expenses/' . $o['expense_id']) . '"><i class="fa fa-external-link"></i></a>' : '<span class="text-muted">—</span>'; ?></td>
                                    <td>
                                        <?php if ($o['paid']) : ?>
                                            <span class="label label-success"><?php echo _l('fleet_paid'); ?></span>
                                            <?php if (staff_can('edit', 'fleet')) : ?><a href="<?php echo admin_url('fleet_management/suppliers/mark_paid/fleet_part_orders/' . $o['id'] . '/0'); ?>" class="text-muted" title="<?php echo _l('fleet_mark_unpaid'); ?>"><i class="fa fa-undo"></i></a><?php endif; ?>
                                        <?php else : ?>
                                            <?php if (staff_can('edit', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/suppliers/mark_paid/fleet_part_orders/' . $o['id'] . '/1'); ?>" class="btn btn-success btn-xs"><?php echo _l('fleet_mark_paid'); ?></a>
                                            <?php else : ?><span class="label label-warning"><?php echo _l('fleet_unpaid'); ?></span><?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($orders)) : ?><tr><td colspan="7" class="text-center text-muted"><?php echo _l('fleet_no_orders'); ?></td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div></div>

                <!-- Other linked costs -->
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold no-margin"><i class="fa fa-list"></i> <?php echo _l('fleet_supplier_other_costs'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr>
                                <th><?php echo _l('fleet_type'); ?></th>
                                <th><?php echo _l('fleet_vehicle'); ?></th>
                                <th><?php echo _l('fleet_date'); ?></th>
                                <th><?php echo _l('fleet_total'); ?></th>
                                <th><?php echo _l('expense'); ?></th>
                                <th><?php echo _l('fleet_payment'); ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($costs as $c) : ?>
                                <tr>
                                    <td><?php echo $c['label']; ?></td>
                                    <td><?php echo html_escape($c['vehicle']); ?></td>
                                    <td><?php echo $c['date'] ? _d($c['date']) : '-'; ?></td>
                                    <td><?php echo app_format_money($c['amount'], $bc); ?></td>
                                    <td><?php echo !empty($c['expense_id']) ? '<a href="' . admin_url('expenses/list_expenses/' . $c['expense_id']) . '"><i class="fa fa-external-link"></i></a>' : '<span class="text-muted">—</span>'; ?></td>
                                    <td>
                                        <?php if ($c['paid']) : ?>
                                            <span class="label label-success"><?php echo _l('fleet_paid'); ?></span>
                                            <?php if (staff_can('edit', 'fleet')) : ?><a href="<?php echo admin_url('fleet_management/suppliers/mark_paid/' . $c['table'] . '/' . $c['id'] . '/0'); ?>" class="text-muted" title="<?php echo _l('fleet_mark_unpaid'); ?>"><i class="fa fa-undo"></i></a><?php endif; ?>
                                        <?php else : ?>
                                            <?php if (staff_can('edit', 'fleet')) : ?>
                                                <a href="<?php echo admin_url('fleet_management/suppliers/mark_paid/' . $c['table'] . '/' . $c['id'] . '/1'); ?>" class="btn btn-success btn-xs"><?php echo _l('fleet_mark_paid'); ?></a>
                                            <?php else : ?><span class="label label-warning"><?php echo _l('fleet_unpaid'); ?></span><?php endif; ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($costs)) : ?><tr><td colspan="6" class="text-center text-muted">—</td></tr><?php endif; ?>
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
