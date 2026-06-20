<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Create default options on activation. add_option() is a no-op when the option
 * already exists, so re-activating the module never overwrites saved settings.
 */

$pdf_footer_manager_default_html = '<div style="border-top:1px solid #cccccc;padding-top:6px;font-size:8px;color:#777777;text-align:center;">'
    . '{company_name} &middot; {company_address} &middot; {company_email}<br>'
    . 'Page {PAGENO} / {nbpg}'
    . '</div>';

add_option('pdf_footer_manager_enabled', 1);
add_option('pdf_footer_manager_apply_all', 1);
add_option('pdf_footer_manager_margin', 9);
add_option('pdf_footer_manager_global_html', $pdf_footer_manager_default_html);

foreach (pdf_footer_manager_document_types() as $type => $class) {
    add_option('pdf_footer_manager_' . $type . '_enabled', 0);
    add_option('pdf_footer_manager_' . $type . '_html', '');
}
