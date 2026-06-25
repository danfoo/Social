<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-check-square-o text-info"></i> <?php echo _l('fleet_approvals'); ?></h3>
            <div class="fleet-tools">
                <div class="btn-group">
                    <?php foreach (['pending', 'approved', 'rejected'] as $st) :
                        $active = $status === $st ? 'btn-primary' : 'btn-default';
                        ?>
                        <a href="<?php echo admin_url('fleet_management/approvals?status=' . $st); ?>" class="btn btn-sm <?php echo $active; ?>">
                            <?php echo _l('fleet_approval_status_' . $st); ?>
                            <?php if ($st === 'pending' && $pending > 0) : ?><span class="label label-danger"><?php echo $pending; ?></span><?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="panel_s"><div class="panel-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead><tr>
                                <th><?php echo _l('fleet_type'); ?></th>
                                <th><?php echo _l('fleet_approval_change'); ?></th>
                                <th><?php echo _l('fleet_approval_requested_by'); ?></th>
                                <th><?php echo _l('fleet_date'); ?></th>
                                <th><?php echo _l('fleet_status'); ?></th>
                                <th class="text-right"><?php echo _l('options'); ?></th>
                            </tr></thead>
                            <tbody>
                            <?php foreach ($approvals as $a) :
                                $action_color = $a['action'] === 'delete' ? 'danger' : 'warning';
                                ?>
                                <tr>
                                    <td><span class="label label-<?php echo $action_color; ?>"><?php echo _l('fleet_approval_action_' . $a['action']); ?></span></td>
                                    <td class="bold"><?php echo html_escape($this->fleet->approval_record_label($a['module'], $a['record_id'])); ?></td>
                                    <td><?php echo html_escape($a['requester_name']); ?></td>
                                    <td><?php echo _dt($a['date_created']); ?></td>
                                    <td>
                                        <?php
                                        $sc = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'default'][$a['status']] ?? 'default';
                                        echo '<span class="label label-' . $sc . '">' . _l('fleet_approval_status_' . $a['status']) . '</span>';
                                        if ($a['status'] !== 'pending' && $a['reviewer_name']) {
                                            echo '<br><small class="text-muted">' . html_escape($a['reviewer_name']) . ' · ' . _dt($a['reviewed_at']) . '</small>';
                                        }
                                        if ($a['status'] === 'rejected' && $a['note']) {
                                            echo '<br><small class="text-danger">' . html_escape($a['note']) . '</small>';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-right">
                                        <?php if ($a['status'] === 'pending') : ?>
                                            <a href="<?php echo admin_url('fleet_management/approvals/approve/' . $a['id']); ?>" class="btn btn-success btn-sm" onclick="return confirm('<?php echo _l('fleet_approval_confirm_approve'); ?>');"><i class="fa fa-check"></i> <?php echo _l('fleet_approve'); ?></a>
                                            <button type="button" class="btn btn-danger btn-sm" onclick="fleetReject(<?php echo $a['id']; ?>);"><i class="fa fa-times"></i> <?php echo _l('fleet_reject'); ?></button>
                                        <?php else : ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($approvals)) : ?>
                                <tr><td colspan="6" class="text-center text-muted" style="padding:24px;"><?php echo _l('fleet_approval_none'); ?></td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div></div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="fleet_reject_modal" tabindex="-1" role="dialog"><div class="modal-dialog"><div class="modal-content">
    <?php echo form_open(admin_url('fleet_management/approvals/reject/0'), ['id' => 'fleet_reject_form']); ?>
    <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title"><?php echo _l('fleet_reject'); ?></h4></div>
    <div class="modal-body">
        <?php echo render_textarea('note', 'fleet_approval_reason', ''); ?>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button><button type="submit" class="btn btn-danger"><?php echo _l('fleet_reject'); ?></button></div>
    <?php echo form_close(); ?>
</div></div></div>

<?php init_tail(); ?>
<script>
function fleetReject(id) {
    var form = document.getElementById('fleet_reject_form');
    form.action = '<?php echo admin_url('fleet_management/approvals/reject'); ?>/' + id;
    $('#fleet_reject_modal').modal('show');
}
</script>
</body>
</html>
