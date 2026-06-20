<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('pdf_footer_manager'); ?></h4>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open(admin_url('pdf_footer_manager/settings')); ?>

                        <div class="row">
                            <div class="col-md-3">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="pdf_footer_manager_enabled" id="pdf_footer_manager_enabled" value="1" <?php echo get_option('pdf_footer_manager_enabled') == '1' ? 'checked' : ''; ?> />
                                    <label for="pdf_footer_manager_enabled"><?php echo _l('pdf_footer_manager_enabled'); ?></label>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="pdf_footer_manager_apply_all" id="pdf_footer_manager_apply_all" value="1" <?php echo get_option('pdf_footer_manager_apply_all') == '1' ? 'checked' : ''; ?> />
                                    <label for="pdf_footer_manager_apply_all"><?php echo _l('pdf_footer_manager_apply_all'); ?></label>
                                </div>
                                <p class="text-muted tw-text-xs"><?php echo _l('pdf_footer_manager_apply_all_help'); ?></p>
                            </div>
                            <div class="col-md-3">
                                <?php echo render_input('pdf_footer_manager_margin', 'pdf_footer_manager_margin', get_option('pdf_footer_manager_margin'), 'number'); ?>
                            </div>
                        </div>

                        <div class="alert alert-info mtop15">
                            <strong><?php echo _l('pdf_footer_manager_merge_fields'); ?> :</strong>
                            <code>{company_name}</code> <code>{company_address}</code> <code>{company_city}</code>
                            <code>{company_phone}</code> <code>{company_email}</code> <code>{company_vat}</code>
                            <code>{website}</code> <code>{year}</code>
                            &middot; <em>mPDF :</em> <code>{PAGENO}</code> <code>{nbpg}</code> <code>{DATE j-m-Y}</code>
                        </div>

                        <div class="form-group">
                            <label for="pdf_footer_manager_global_html"><?php echo _l('pdf_footer_manager_global_html'); ?></label>
                            <textarea name="pdf_footer_manager_global_html" id="pdf_footer_manager_global_html" rows="6" class="form-control" style="font-family:monospace;"><?php echo html_escape(get_option('pdf_footer_manager_global_html')); ?></textarea>
                        </div>

                        <hr />
                        <h4 class="bold"><?php echo _l('pdf_footer_manager_per_type'); ?></h4>
                        <p class="text-muted"><?php echo _l('pdf_footer_manager_per_type_help'); ?></p>

                        <div class="panel-group" id="pdf-footer-accordion">
                            <?php $i = 0; foreach ($types as $type => $class) : $i++; ?>
                                <div class="panel panel-default">
                                    <div class="panel-heading">
                                        <h4 class="panel-title">
                                            <a data-toggle="collapse" data-parent="#pdf-footer-accordion" href="#pdf-footer-<?php echo $type; ?>">
                                                <?php echo _l('pdf_footer_manager_type_' . $type); ?>
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="pdf-footer-<?php echo $type; ?>" class="panel-collapse collapse">
                                        <div class="panel-body">
                                            <div class="checkbox checkbox-primary">
                                                <input type="checkbox" name="pdf_footer_manager_<?php echo $type; ?>_enabled" id="pdf_footer_manager_<?php echo $type; ?>_enabled" value="1" <?php echo get_option('pdf_footer_manager_' . $type . '_enabled') == '1' ? 'checked' : ''; ?> />
                                                <label for="pdf_footer_manager_<?php echo $type; ?>_enabled"><?php echo _l('pdf_footer_manager_use_custom'); ?></label>
                                            </div>
                                            <textarea name="pdf_footer_manager_<?php echo $type; ?>_html" rows="5" class="form-control" style="font-family:monospace;"><?php echo html_escape(get_option('pdf_footer_manager_' . $type . '_html')); ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="submit" class="btn btn-primary"><?php echo _l('submit'); ?></button>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>
</html>
