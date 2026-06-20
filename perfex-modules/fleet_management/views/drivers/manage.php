<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="alert alert-info">
                            <?php echo _l('fleet_drivers_help'); ?>
                            <?php if (has_permission('staff', '', 'create')) : ?>
                                <a href="<?php echo admin_url('staff/member'); ?>" class="btn btn-info btn-xs pull-right"><?php echo _l('fleet_add_staff_member'); ?></a>
                            <?php endif; ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th><?php echo _l('fleet_driver'); ?></th>
                                        <th><?php echo _l('email'); ?></th>
                                        <th><?php echo _l('fleet_phone'); ?></th>
                                        <th><?php echo _l('status'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($drivers as $d) : ?>
                                        <tr>
                                            <td><?php echo html_escape($d['full_name']); ?></td>
                                            <td><?php echo html_escape($d['email']); ?></td>
                                            <td><?php echo html_escape($d['phonenumber']); ?></td>
                                            <td><?php echo $d['active'] ? '<span class="label label-success">' . _l('active') . '</span>' : '<span class="label label-default">' . _l('inactive') . '</span>'; ?></td>
                                            <td>
                                                <?php if (has_permission('staff', '', 'edit')) : ?>
                                                    <a href="<?php echo admin_url('staff/member/' . $d['staffid']); ?>" class="btn btn-default btn-icon"><i class="fa fa-pencil-square-o"></i></a>
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
