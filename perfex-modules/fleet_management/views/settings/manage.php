<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
$cat_options = [];
foreach ($expense_categories as $c) {
    $cat_options[] = ['id' => $c['id'], 'name' => $c['name']];
}
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content fleet-list-page">
        <div class="fleet-toolbar">
            <h3><i class="fa fa-cogs text-info"></i> <?php echo _l('fleet_settings'); ?></h3>
        </div>
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s"><div class="panel-body">
                    <?php echo form_open(admin_url('fleet_management/settings')); ?>

                    <h4 class="bold"><i class="fa fa-money text-success"></i> <?php echo _l('fleet_set_billing'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_select('fleet_expense_category_id', $cat_options, ['id', 'name'], 'fleet_set_expense_category', get_option('fleet_expense_category_id')); ?>
                            <p class="text-muted tw-text-xs"><?php echo _l('fleet_set_expense_category_help'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php echo render_input('fleet_invoice_due_days', 'fleet_set_invoice_due_days', get_option('fleet_invoice_due_days'), 'number'); ?>
                            <p class="text-muted tw-text-xs"><?php echo _l('fleet_set_invoice_due_days_help'); ?></p>
                        </div>
                    </div>

                    <h4 class="bold mtop20"><i class="fa fa-dashboard text-info"></i> <?php echo _l('fleet_dashboard'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_input('fleet_occupancy_days', 'fleet_set_occupancy_days', get_option('fleet_occupancy_days'), 'number'); ?>
                            <p class="text-muted tw-text-xs"><?php echo _l('fleet_set_occupancy_days_help'); ?></p>
                        </div>
                    </div>

                    <h4 class="bold mtop20"><i class="fa fa-id-card text-danger"></i> <?php echo _l('fleet_drivers'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_input('fleet_license_notify_days', 'fleet_set_license_notify_days', get_option('fleet_license_notify_days'), 'number'); ?>
                            <p class="text-muted tw-text-xs"><?php echo _l('fleet_set_license_notify_days_help'); ?></p>
                        </div>
                    </div>

                    <h4 class="bold mtop20"><i class="fa fa-envelope text-warning"></i> <?php echo _l('fleet_set_notifications'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <div class="checkbox checkbox-primary">
                        <input type="checkbox" name="fleet_email_notifications" id="fleet_email_notifications" value="1" <?php echo get_option('fleet_email_notifications') ? 'checked' : ''; ?>>
                        <label for="fleet_email_notifications"><?php echo _l('fleet_set_email_notifications'); ?></label>
                    </div>
                    <p class="text-muted tw-text-xs"><?php echo _l('fleet_set_email_notifications_help'); ?></p>
                    <div class="row">
                        <div class="col-md-8">
                            <?php echo render_input('fleet_notification_emails', 'fleet_set_notification_emails', get_option('fleet_notification_emails')); ?>
                            <p class="text-muted tw-text-xs"><?php echo _l('fleet_set_notification_emails_help'); ?></p>
                        </div>
                    </div>

                    <h4 class="bold mtop20"><i class="fa fa-file-text text-info"></i> <?php echo _l('fleet_contract'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <?php
                    $terms_value = get_option('fleet_contract_terms');
                    if (trim($terms_value) === '') {
                        $terms_value = _l('fleet_contract_terms');
                    }
                    ?>
                    <div class="form-group">
                        <label for="fleet_contract_terms" class="control-label"><?php echo _l('fleet_set_contract_terms'); ?></label>
                        <textarea name="fleet_contract_terms" id="fleet_contract_terms" class="form-control tinymce" rows="8"><?php echo $terms_value; ?></textarea>
                        <input type="hidden" name="fleet_contract_terms_encoded" id="fleet_contract_terms_encoded" value="">
                    </div>
                    <p class="text-muted tw-text-xs"><?php echo _l('fleet_set_contract_terms_help'); ?></p>

                    <div class="mtop15">
                        <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                    </div>
                    <?php echo form_close(); ?>
                </div></div>
            </div>
            <div class="col-md-4">
                <div class="panel_s"><div class="panel-body">
                    <h4 class="bold"><i class="fa fa-info-circle text-info"></i> <?php echo _l('fleet_drivers'); ?></h4>
                    <hr class="hr-panel-heading" />
                    <p><?php echo _l('fleet_set_driver_role'); ?> : <span class="label label-info"><?php echo html_escape($driver_role); ?></span></p>
                    <p class="text-muted"><?php echo _l('fleet_drivers_help'); ?></p>
                    <a href="<?php echo admin_url('fleet_management/drivers'); ?>" class="btn btn-default btn-block"><?php echo _l('fleet_drivers'); ?></a>
                </div></div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
// The contract terms hold rich HTML. Posting raw HTML on this module URL can be
// blocked (403) by Perfex's input filter or a server WAF, so we base64-encode
// the editor content before submit and decode it server-side. The raw textarea
// is disabled so no HTML tags appear in the request.
$(function () {
    var $area = $('#fleet_contract_terms');
    if (!$area.length) { return; }

    $area.closest('form').on('submit', function () {
        if (typeof tinymce !== 'undefined' && tinymce.get('fleet_contract_terms')) {
            tinymce.triggerSave();
        }
        var html = $area.val();
        $('#fleet_contract_terms_encoded').val(window.btoa(unescape(encodeURIComponent(html))));
        $area.prop('disabled', true);
    });
});
</script>
</body>
</html>
