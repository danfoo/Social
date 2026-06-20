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
                        <?php echo form_open(admin_url('pdf_footer_manager/settings'), ['id' => 'pdf-footer-manager-form']); ?>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="checkbox checkbox-primary">
                                    <input type="checkbox" name="pdf_footer_manager_enabled" id="pdf_footer_manager_enabled" value="1" <?php echo get_option('pdf_footer_manager_enabled') == '1' ? 'checked' : ''; ?> />
                                    <label for="pdf_footer_manager_enabled"><?php echo _l('pdf_footer_manager_enabled'); ?></label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <?php echo render_input('pdf_footer_manager_margin', 'pdf_footer_manager_margin', get_option('pdf_footer_manager_margin'), 'number'); ?>
                            </div>
                        </div>

                        <div class="alert alert-info mtop15">
                            <strong><?php echo _l('pdf_footer_manager_merge_fields'); ?> :</strong>
                            <span class="text-muted"><?php echo _l('pdf_footer_manager_insert_hint'); ?></span><br>
                            <code class="pdf-merge-field">{company_name}</code> <code class="pdf-merge-field">{company_address}</code> <code class="pdf-merge-field">{company_city}</code>
                            <code class="pdf-merge-field">{company_phone}</code> <code class="pdf-merge-field">{company_email}</code> <code class="pdf-merge-field">{company_vat}</code>
                            <code class="pdf-merge-field">{website}</code> <code class="pdf-merge-field">{year}</code>
                            &middot; <em><?php echo _l('pdf_footer_manager_pagination'); ?> :</em> <code class="pdf-merge-field">{PAGENO}</code> <code class="pdf-merge-field">{nbpg}</code>
                        </div>

                        <div class="form-group">
                            <label for="pdf_footer_manager_global_html"><?php echo _l('pdf_footer_manager_global_html'); ?></label>
                            <textarea name="pdf_footer_manager_global_html" id="pdf_footer_manager_global_html" rows="6" class="form-control pdf-footer-editor"><?php echo html_escape(get_option('pdf_footer_manager_global_html')); ?></textarea>
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
                                            <textarea name="pdf_footer_manager_<?php echo $type; ?>_html" id="pdf_footer_manager_<?php echo $type; ?>_html" rows="5" class="form-control pdf-footer-editor"><?php echo html_escape(get_option('pdf_footer_manager_' . $type . '_html')); ?></textarea>
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
<script>
(function($) {
    'use strict';

    // Graceful fallback: if TinyMCE is not available on this page, the plain
    // textareas remain fully usable.
    if (typeof tinymce === 'undefined') {
        return;
    }

    // Permissive config so inline styles, tables and {merge_fields} are kept
    // untouched. The "code" plugin exposes a raw-HTML source view (</> button).
    var baseConfig = {
        height: 200,
        menubar: false,
        branding: false,
        convert_urls: false,
        plugins: 'code link lists table',
        toolbar: 'bold italic underline | bullist numlist | alignleft aligncenter alignright | link table | removeformat | code',
        valid_elements: '*[*]',
        extended_valid_elements: '*[*]',
        verify_html: false
    };

    function initEditor(id) {
        tinymce.init($.extend({selector: '#' + id}, baseConfig));
    }

    // Global footer editor is visible immediately.
    initEditor('pdf_footer_manager_global_html');

    // Per-type editors live inside collapsed accordions: initialise each one
    // the first time its panel is opened (lighter, avoids hidden-init quirks).
    $('#pdf-footer-accordion').on('shown.bs.collapse', function(e) {
        var $ta = $(e.target).find('textarea.pdf-footer-editor');
        if ($ta.length && !$ta.data('mce-init')) {
            $ta.data('mce-init', true);
            initEditor($ta.attr('id'));
        }
    });

    // Clicking a merge field inserts it at the caret of the focused editor.
    $('.pdf-merge-field').css('cursor', 'pointer').on('click', function() {
        if (tinymce.activeEditor && !tinymce.activeEditor.isHidden()) {
            tinymce.activeEditor.execCommand('mceInsertContent', false, $(this).text());
        }
    });

    // Push editor content back into the textareas before the form is submitted.
    $('#pdf-footer-manager-form').on('submit', function() {
        tinymce.triggerSave();
    });
})(jQuery);
</script>
</body>
</html>
