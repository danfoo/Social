<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$bc = get_base_currency();

$item_options = [];
foreach ($items as $it) {
    $label          = $it['name'] . ($it['reference'] ? ' (' . $it['reference'] . ')' : '');
    $item_options[] = ['id' => $it['id'], 'name' => $label];
}
$supplier_options = [];
foreach ($suppliers as $s) {
    $supplier_options[] = ['id' => $s['id'], 'name' => $s['name']];
}
$vehicle_options = [];
foreach ($vehicles as $v) {
    $vehicle_options[] = ['id' => $v['id'], 'name' => $v['name'] . ' (' . $v['plate'] . ')'];
}

$order_status = ['ordered' => 'warning', 'received' => 'success', 'cancelled' => 'default'];

$pcat_options = [];
foreach ($part_categories as $c) {
    $pcat_options[] = ['id' => $c['name'], 'name' => $c['name']];
}
$punit_options = [];
foreach ($part_units as $u) {
    $punit_options[] = ['id' => $u['name'], 'name' => $u['name']];
}
$client_options = [];
foreach ($clients as $cl) {
    $client_options[] = ['id' => $cl['userid'], 'name' => $cl['company']];
}
$item_options_html = '<option value=""></option>';
foreach ($items as $it) {
    $label              = $it['name'] . ($it['reference'] ? ' (' . $it['reference'] . ')' : '');
    $item_options_html .= '<option value="' . $it['id'] . '">' . html_escape($label) . '</option>';
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-cog text-info"></i> <?php echo _l('fleet_parts_articles'); ?></h3>
            <div class="fleet-tools">
                <a href="<?php echo admin_url('fleet_management/parts/export'); ?>" class="btn btn-default btn-sm"><i class="fa fa-download"></i> <?php echo _l('fleet_export'); ?></a>
                <?php if (staff_can('create', 'fleet')) : ?>
                    <a href="#" class="btn btn-primary btn-sm" onclick="fleet_part_item_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_part'); ?></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- KPI cards -->
        <div class="row">
            <div class="col-md-4 col-sm-4"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#337ab7;"><i class="fa fa-list"></i></div>
                <div><h2><?php echo (int) $stats->items; ?></h2><span><?php echo _l('fleet_parts_count'); ?></span></div>
            </div></div></div>
            <div class="col-md-4 col-sm-4"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#71dd37;"><i class="fa fa-cubes"></i></div>
                <div><h2><?php echo (int) $stats->in_stock; ?></h2><span><?php echo _l('fleet_in_stock'); ?></span></div>
            </div></div></div>
            <div class="col-md-4 col-sm-4"><div class="panel_s"><div class="panel-body fleet-stat">
                <div class="ic" style="background:#ff3e1d;"><i class="fa fa-exclamation-triangle"></i></div>
                <div><h2><?php echo (int) $stats->low; ?></h2><span><?php echo _l('fleet_low_stock'); ?></span></div>
            </div></div></div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s"><div class="panel-body">
                    <ul class="nav nav-tabs" role="tablist">
                        <li role="presentation" class="active"><a href="#tab_catalog" data-toggle="tab"><?php echo _l('fleet_catalog'); ?></a></li>
                        <li role="presentation"><a href="#tab_orders" data-toggle="tab"><?php echo _l('fleet_orders'); ?></a></li>
                        <li role="presentation"><a href="#tab_stock" data-toggle="tab"><?php echo _l('fleet_assignments_tab'); ?></a></li>
                    </ul>
                    <div class="tab-content mtop15">

                        <!-- CATALOG -->
                        <div role="tabpanel" class="tab-pane active" id="tab_catalog">
                            <div class="table-responsive">
                                <table class="table fleet-list">
                                    <thead><tr>
                                        <th><?php echo _l('fleet_part_name'); ?></th>
                                        <th><?php echo _l('fleet_reference'); ?></th>
                                        <th><?php echo _l('fleet_category'); ?></th>
                                        <th><?php echo _l('fleet_stock'); ?></th>
                                        <th class="text-right"><?php echo _l('options'); ?></th>
                                    </tr></thead>
                                    <tbody>
                                        <?php foreach ($items as $it) : $stockColor = $it['stock'] <= 0 ? 'danger' : (($it['min_stock'] > 0 && $it['stock'] <= $it['min_stock']) ? 'warning' : 'success'); ?>
                                            <tr>
                                                <td class="bold"><?php echo html_escape($it['name']); ?></td>
                                                <td><?php echo $it['reference'] ? html_escape($it['reference']) : '<span class="text-muted">—</span>'; ?></td>
                                                <td><?php echo $it['category'] ? html_escape($it['category']) : '<span class="text-muted">—</span>'; ?></td>
                                                <td><span class="label label-<?php echo $stockColor; ?>"><?php echo (int) $it['stock']; ?> <?php echo html_escape($it['unit'] ?: _l('fleet_unit')); ?></span></td>
                                                <td class="text-right">
                                                    <?php if (staff_can('create', 'fleet')) : ?>
                                                        <a href="#" class="btn btn-success btn-sm" onclick="fleet_part_order_modal(undefined, <?php echo $it['id']; ?>); return false;"><i class="fa fa-shopping-cart"></i> <?php echo _l('fleet_buy'); ?></a>
                                                        <?php if ($it['stock'] > 0) : ?>
                                                            <a href="#" class="btn btn-info btn-sm" onclick="fleet_part_assign_modal(undefined, <?php echo $it['id']; ?>); return false;"><i class="fa fa-share"></i> <?php echo _l('fleet_assign'); ?></a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    <?php if (staff_can('edit', 'fleet')) : ?>
                                                        <a href="#" class="btn btn-default btn-icon btn-sm" onclick="fleet_part_item_modal(<?php echo $it['id']; ?>); return false;"><i class="fa fa-pencil-square"></i></a>
                                                    <?php endif; ?>
                                                    <?php if (staff_can('delete', 'fleet')) : ?>
                                                        <a href="<?php echo admin_url('fleet_management/parts/item_delete/' . $it['id']); ?>" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-remove"></i></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($items)) : ?><tr><td colspan="5" class="text-center text-muted" style="padding:25px;"><?php echo _l('fleet_add_part'); ?></td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- ORDERS -->
                        <div role="tabpanel" class="tab-pane" id="tab_orders">
                            <?php if (staff_can('create', 'fleet')) : ?>
                                <a href="#" class="btn btn-primary btn-sm mbot15" onclick="fleet_part_order_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_new_order'); ?></a>
                            <?php endif; ?>
                            <div class="table-responsive">
                                <table class="table fleet-list">
                                    <thead><tr>
                                        <th>#</th>
                                        <th><?php echo _l('fleet_part'); ?></th>
                                        <th><?php echo _l('fleet_supplier'); ?></th>
                                        <th><?php echo _l('fleet_lines_count'); ?></th>
                                        <th><?php echo _l('fleet_total'); ?></th>
                                        <th><?php echo _l('fleet_order_date'); ?></th>
                                        <th><?php echo _l('fleet_status'); ?></th>
                                        <th class="text-right"><?php echo _l('options'); ?></th>
                                    </tr></thead>
                                    <tbody>
                                        <?php foreach ($orders as $o) : ?>
                                            <tr>
                                                <td class="text-muted">PO-<?php echo $o['id']; ?></td>
                                                <td class="bold"><?php echo html_escape($o['items_summary']); ?></td>
                                                <td><?php echo $o['supplier_name'] ? html_escape($o['supplier_name']) : '<span class="text-muted">—</span>'; ?></td>
                                                <td><span class="label label-default"><?php echo (int) $o['items_count']; ?></span></td>
                                                <td class="bold"><?php echo app_format_money($o['total_price'], $bc); ?></td>
                                                <td><?php echo $o['order_date'] ? _d($o['order_date']) : '-'; ?></td>
                                                <td><span class="label label-<?php echo $order_status[$o['status']] ?? 'default'; ?>"><?php echo _l('fleet_ostatus_' . $o['status']); ?></span></td>
                                                <td class="text-right">
                                                    <a href="<?php echo admin_url('fleet_management/parts/order_pdf/' . $o['id']); ?>" target="_blank" class="btn btn-default btn-icon btn-sm" title="PDF"><i class="fa fa-file-pdf"></i></a>
                                                    <?php if ($o['status'] === 'ordered' && staff_can('edit', 'fleet')) : ?>
                                                        <a href="<?php echo admin_url('fleet_management/parts/order_receive/' . $o['id']); ?>" class="btn btn-success btn-sm"><i class="fa fa-check"></i> <?php echo _l('fleet_receive'); ?></a>
                                                        <a href="<?php echo admin_url('fleet_management/parts/order_cancel/' . $o['id']); ?>" class="btn btn-default btn-sm"><?php echo _l('fleet_cancel_order'); ?></a>
                                                    <?php endif; ?>
                                                    <?php if (staff_can('delete', 'fleet')) : ?>
                                                        <a href="<?php echo admin_url('fleet_management/parts/order_delete/' . $o['id']); ?>" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-remove"></i></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($orders)) : ?><tr><td colspan="8" class="text-center text-muted" style="padding:25px;"><?php echo _l('fleet_no_orders'); ?></td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- STOCK / ASSIGNMENTS -->
                        <div role="tabpanel" class="tab-pane" id="tab_stock">
                            <?php if (staff_can('create', 'fleet')) : ?>
                                <a href="#" class="btn btn-primary btn-sm mbot15" onclick="fleet_part_assign_modal(); return false;"><i class="fa fa-plus"></i> <?php echo _l('fleet_new_assignment'); ?></a>
                            <?php endif; ?>
                            <div class="table-responsive">
                                <table class="table fleet-list">
                                    <thead><tr>
                                        <th><?php echo _l('fleet_part'); ?></th>
                                        <th><?php echo _l('fleet_assigned_to'); ?></th>
                                        <th><?php echo _l('fleet_quantity'); ?></th>
                                        <th><?php echo _l('fleet_total'); ?></th>
                                        <th><?php echo _l('fleet_date'); ?></th>
                                        <th class="text-right"><?php echo _l('options'); ?></th>
                                    </tr></thead>
                                    <tbody>
                                        <?php foreach ($assignments as $a) : ?>
                                            <tr>
                                                <td class="bold"><?php echo html_escape($a['item_name']); ?></td>
                                                <td>
                                                    <?php if ($a['vehicle_name']) : ?>
                                                        <a href="<?php echo admin_url('fleet_management/vehicles/view/' . $a['vehicle_id']); ?>"><?php echo html_escape($a['vehicle_name']); ?></a>
                                                    <?php else : ?>
                                                        <?php echo $a['assigned_to'] ? html_escape($a['assigned_to']) : '<span class="text-muted">—</span>'; ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo (int) $a['quantity']; ?></td>
                                                <td><?php echo app_format_money($a['total_cost'], $bc); ?></td>
                                                <td><?php echo $a['assigned_date'] ? _d($a['assigned_date']) : '-'; ?></td>
                                                <td class="text-right">
                                                    <?php if (staff_can('delete', 'fleet')) : ?>
                                                        <a href="<?php echo admin_url('fleet_management/parts/assignment_delete/' . $a['id']); ?>" class="btn btn-danger btn-icon btn-sm _delete"><i class="fa fa-remove"></i></a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                        <?php if (empty($assignments)) : ?><tr><td colspan="6" class="text-center text-muted" style="padding:25px;"><?php echo _l('fleet_no_assignments'); ?></td></tr><?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>

<!-- Item modal -->
<div class="modal fade" id="fleet_part_item_modal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content">
    <?php echo form_open(admin_url('fleet_management/parts/item_save')); ?>
    <input type="hidden" name="id" id="item_id" value="">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('fleet_part'); ?></h4></div>
    <div class="modal-body">
        <div class="row">
            <div class="col-md-8"><?php echo render_input('name', 'fleet_part_name', ''); ?></div>
            <div class="col-md-4"><?php echo render_input('reference', 'fleet_reference', ''); ?></div>
        </div>
        <div class="row">
            <div class="col-md-5"><?php echo render_select('category', $pcat_options, ['id', 'name'], 'fleet_category'); ?></div>
            <div class="col-md-3"><?php echo render_select('unit', $punit_options, ['id', 'name'], 'fleet_unit'); ?></div>
            <div class="col-md-4"><?php echo render_input('min_stock', 'fleet_min_stock', 0, 'number'); ?></div>
        </div>
        <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button></div>
    <?php echo form_close(); ?>
</div></div></div>

<!-- Order modal -->
<div class="modal fade" id="fleet_part_order_modal" tabindex="-1" role="dialog"><div class="modal-dialog modal-lg"><div class="modal-content">
    <?php echo form_open(admin_url('fleet_management/parts/order_save')); ?>
    <input type="hidden" name="id" id="order_id" value="">
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('fleet_buy'); ?></h4></div>
    <div class="modal-body">
        <label class="control-label"><?php echo _l('fleet_orders'); ?></label>
        <table class="table" id="order_lines_table" style="margin-bottom:6px;">
            <thead><tr>
                <th style="width:45%;"><?php echo _l('fleet_part'); ?></th>
                <th style="width:14%;"><?php echo _l('fleet_quantity'); ?></th>
                <th style="width:20%;"><?php echo _l('fleet_unit_price'); ?></th>
                <th style="width:16%;"><?php echo _l('fleet_total'); ?></th>
                <th style="width:5%;"></th>
            </tr></thead>
            <tbody id="order_lines_body">
                <tr class="order-line">
                    <td><select name="line_item[]" class="form-control"><?php echo $item_options_html; ?></select></td>
                    <td><input type="number" name="line_qty[]" class="form-control line-qty" value="1" min="1"></td>
                    <td><input type="number" name="line_price[]" class="form-control line-price" step="0.01"></td>
                    <td class="line-total" style="vertical-align:middle;">0.00</td>
                    <td style="vertical-align:middle;"><a href="#" class="text-danger line-remove"><i class="fa fa-remove"></i></a></td>
                </tr>
            </tbody>
        </table>
        <button type="button" class="btn btn-default btn-sm" id="order_add_line"><i class="fa fa-plus"></i> <?php echo _l('fleet_add_line'); ?></button>
        <span class="pull-right bold" style="margin-top:6px;"><?php echo _l('fleet_total'); ?>: <span id="order_grand_total">0.00</span></span>
        <div class="clearfix"></div>
        <hr>
        <?php echo render_select('supplier_id', $supplier_options, ['id', 'name'], 'fleet_supplier'); ?>
        <div class="row">
            <div class="col-md-6"><?php echo render_date_input('order_date', 'fleet_order_date', _d(date('Y-m-d'))); ?></div>
            <div class="col-md-6"><?php echo render_input('invoice_no', 'fleet_supplier_invoice_no', ''); ?></div>
        </div>
        <div class="checkbox checkbox-primary">
            <input type="checkbox" name="billable" id="order_billable" value="1">
            <label for="order_billable"><?php echo _l('fleet_billable'); ?></label>
        </div>
        <div id="order_client_wrap" style="display:none;">
            <?php echo render_select('clientid', $client_options, ['id', 'name'], 'fleet_reinvoice_client'); ?>
        </div>
        <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn btn-primary"><?php echo _l('fleet_place_order'); ?></button></div>
    <?php echo form_close(); ?>
</div></div></div>

<!-- Assignment modal -->
<div class="modal fade" id="fleet_part_assign_modal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content">
    <?php echo form_open(admin_url('fleet_management/parts/assignment_save')); ?>
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('fleet_assign'); ?></h4></div>
    <div class="modal-body">
        <?php echo render_select('item_id', $item_options, ['id', 'name'], 'fleet_part'); ?>
        <div class="row">
            <div class="col-md-6"><?php echo render_select('vehicle_id', $vehicle_options, ['id', 'name'], 'fleet_vehicle'); ?></div>
            <div class="col-md-6"><?php echo render_input('assigned_to', 'fleet_assigned_to_other', ''); ?></div>
        </div>
        <div class="form-group">
            <label class="control-label" for="assign_maintenance_id"><?php echo _l('fleet_link_maintenance'); ?></label>
            <select name="maintenance_id" id="assign_maintenance_id" class="form-control">
                <option value=""><?php echo _l('fleet_no_link'); ?></option>
                <?php foreach ($maintenances as $mm) : ?>
                    <option value="<?php echo $mm['id']; ?>" data-vehicle="<?php echo (int) $mm['vehicle_id']; ?>"><?php echo html_escape($mm['vehicle_plate'] . ' · ' . _l('fleet_mtype_' . $mm['type']) . ' · ' . ($mm['service_date'] ? _d($mm['service_date']) : '')); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="row">
            <div class="col-md-6"><?php echo render_input('quantity', 'fleet_quantity', 1, 'number'); ?></div>
            <div class="col-md-6"><?php echo render_date_input('assigned_date', 'fleet_date', _d(date('Y-m-d'))); ?></div>
        </div>
        <?php echo render_textarea('notes', 'fleet_notes', ''); ?>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn btn-primary"><?php echo _l('fleet_assign'); ?></button></div>
    <?php echo form_close(); ?>
</div></div></div>

<?php init_tail(); ?>
<script>
$(function() {
    $('#fleet_part_order_modal').on('input', '.line-qty, .line-price', fleetOrderRecalc);
    $('#order_add_line').on('click', function() { fleetOrderAddLine(); });
    $('#fleet_part_order_modal').on('click', '.line-remove', function(e) {
        e.preventDefault();
        if ($('#order_lines_body tr.order-line').length > 1) {
            $(this).closest('tr').remove();
        } else {
            var $r = $(this).closest('tr');
            $r.find('select').val(''); $r.find('.line-qty').val(1); $r.find('.line-price').val('');
        }
        fleetOrderRecalc();
    });
    $('#fleet_part_order_modal').on('change', '#order_billable', function() {
        $('#order_client_wrap').toggle($(this).is(':checked'));
    });
    $('#fleet_part_assign_modal').on('change', '[name="vehicle_id"]', fleetAssignFilterMaintenance);
    // open the tab referenced in the URL hash
    if (window.location.hash) { $('.nav-tabs a[href="' + window.location.hash + '"]').tab('show'); }
});

function fleetAssignFilterMaintenance() {
    var $m = $('#assign_maintenance_id');
    if (!$m.data('all')) { $m.data('all', $m.find('option').clone()); }
    var vid = $('#fleet_part_assign_modal [name="vehicle_id"]').val();
    var cur = $m.val();
    $m.empty().append('<option value=""><?php echo _l('fleet_no_link'); ?></option>');
    $m.data('all').each(function() {
        var o = $(this);
        if (o.val() === '') { return; }
        if (!vid || String(o.data('vehicle')) === String(vid)) { $m.append(o.clone()); }
    });
    $m.val(cur);
}

function fleet_part_item_modal(id) {
    var modal = $('#fleet_part_item_modal');
    modal.find('form')[0].reset();
    $('#item_id').val('');
    if (typeof id !== 'undefined') {
        $.getJSON('<?php echo admin_url('fleet_management/parts/item_get'); ?>/' + id, function(rec) {
            if (!rec) { return; }
            $('#item_id').val(rec.id);
            modal.find('[name="name"]').val(rec.name);
            modal.find('[name="reference"]').val(rec.reference);
            modal.find('[name="category"]').val(rec.category);
            modal.find('[name="unit"]').val(rec.unit);
            modal.find('[name="min_stock"]').val(rec.min_stock);
            modal.find('[name="notes"]').val(rec.notes);
            if (modal.find('.selectpicker').length) { modal.find('.selectpicker').selectpicker('refresh'); }
        });
    }
    modal.modal('show');
}

function fleetOrderRecalc() {
    var grand = 0;
    $('#order_lines_body tr.order-line').each(function() {
        var q = parseFloat($(this).find('.line-qty').val());
        var p = parseFloat($(this).find('.line-price').val());
        var t = (!isNaN(q) && !isNaN(p)) ? q * p : 0;
        $(this).find('.line-total').text(t.toFixed(2));
        grand += t;
    });
    $('#order_grand_total').text(grand.toFixed(2));
}

function fleetOrderAddLine(item_id, qty, price) {
    var $row = $('#order_lines_body tr.order-line:first').clone();
    $row.find('select').val(item_id || '');
    $row.find('.line-qty').val(qty || 1);
    $row.find('.line-price').val(typeof price !== 'undefined' && price !== null ? price : '');
    $row.find('.line-total').text('0.00');
    $('#order_lines_body').append($row);
    fleetOrderRecalc();
    return $row;
}

function fleetOrderResetLines() {
    $('#order_lines_body tr.order-line:gt(0)').remove();
    var $f = $('#order_lines_body tr.order-line:first');
    $f.find('select').val('');
    $f.find('.line-qty').val(1);
    $f.find('.line-price').val('');
    $f.find('.line-total').text('0.00');
    $('#order_grand_total').text('0.00');
}

function fleet_part_order_modal(id, itemId) {
    var modal = $('#fleet_part_order_modal');
    modal.find('form')[0].reset();
    $('#order_id').val('');
    $('#order_client_wrap').hide();
    fleetOrderResetLines();
    if (typeof id !== 'undefined' && id !== null) {
        $.getJSON('<?php echo admin_url('fleet_management/parts/order_get'); ?>/' + id, function(resp) {
            var rec = resp.order, lines = resp.items || [];
            if (!rec) { return; }
            $('#order_id').val(rec.id);
            modal.find('[name="supplier_id"]').val(rec.supplier_id);
            if (rec.order_date) { modal.find('[name="order_date"]').val(rec.order_date); }
            modal.find('[name="invoice_no"]').val(rec.invoice_no);
            modal.find('[name="notes"]').val(rec.notes);
            $('#order_billable').prop('checked', rec.billable == 1);
            $('#order_client_wrap').toggle(rec.billable == 1);
            modal.find('[name="clientid"]').val(rec.clientid);
            fleetOrderResetLines();
            lines.forEach(function(l, idx) {
                if (idx === 0) {
                    var $f = $('#order_lines_body tr.order-line:first');
                    $f.find('select').val(l.item_id);
                    $f.find('.line-qty').val(l.quantity);
                    $f.find('.line-price').val(l.unit_price);
                } else {
                    fleetOrderAddLine(l.item_id, l.quantity, l.unit_price);
                }
            });
            fleetOrderRecalc();
            if (modal.find('.selectpicker').length) { modal.find('.selectpicker').selectpicker('refresh'); }
        });
    } else if (typeof itemId !== 'undefined') {
        $('#order_lines_body tr.order-line:first select').val(itemId);
        if (modal.find('.selectpicker').length) { modal.find('.selectpicker').selectpicker('refresh'); }
    }
    modal.modal('show');
}

function fleet_part_assign_modal(id, itemId) {
    var modal = $('#fleet_part_assign_modal');
    modal.find('form')[0].reset();
    if (typeof itemId !== 'undefined') { modal.find('[name="item_id"]').val(itemId); }
    fleetAssignFilterMaintenance();
    if (modal.find('.selectpicker').length) { modal.find('.selectpicker').selectpicker('refresh'); }
    modal.modal('show');
}
<?php if ($this->input->get('assign_vehicle')) : ?>
$(function() {
    fleet_part_assign_modal();
    $('#fleet_part_assign_modal [name="vehicle_id"]').val('<?php echo (int) $this->input->get('assign_vehicle'); ?>');
    if ($('#fleet_part_assign_modal .selectpicker').length) { $('#fleet_part_assign_modal .selectpicker').selectpicker('refresh'); }
    fleetAssignFilterMaintenance();
});
<?php endif; ?>
</script>
</body>
</html>
