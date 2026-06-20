<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Register the injection callback on the PDF construction hooks.
 *
 * Perfex fires an action right after the PDF object (App_pdf, which extends
 * \Mpdf\Mpdf on recent versions) is built. We register on the known candidate
 * hook names so the module keeps working across Perfex versions. A hook that
 * does not exist on the running version simply never fires, so registering
 * several of them is harmless.
 */
hooks()->add_action('pdf_construct', 'pdf_footer_manager_apply');
hooks()->add_action('app_pdf', 'pdf_footer_manager_apply');
hooks()->add_action('after_app_pdf_init', 'pdf_footer_manager_apply');

/**
 * Inject the configured footer into the PDF instance.
 *
 * @param mixed $pdf The App_pdf / mPDF instance passed by the hook.
 */
function pdf_footer_manager_apply($pdf)
{
    if (get_option('pdf_footer_manager_enabled') != '1') {
        return $pdf;
    }

    // Some hooks may pass the object wrapped in an array.
    if (is_array($pdf) && isset($pdf[0]) && is_object($pdf[0])) {
        $pdf = $pdf[0];
    }

    // We rely on mPDF's HTML footer API. Older TCPDF-based installs do not have
    // it; in that case we bail out gracefully (see README for the alternative).
    if (!is_object($pdf) || !method_exists($pdf, 'SetHTMLFooter')) {
        return $pdf;
    }

    $type = pdf_footer_manager_detect_type($pdf);
    $html = pdf_footer_manager_get_html($type);

    if ($html === '') {
        return $pdf;
    }

    // Reserve room at the bottom of the page for the footer (in millimetres).
    $margin = (int) get_option('pdf_footer_manager_margin');
    if ($margin > 0 && property_exists($pdf, 'margin_footer')) {
        $pdf->margin_footer = $margin;
    }

    $pdf->SetHTMLFooter($html);

    return $pdf;
}

/**
 * Resolve the internal document type from the concrete PDF class name.
 * Each Perfex document has its own App_pdf subclass (Invoice_pdf, Estimate_pdf...),
 * so the class name reliably identifies the document being generated.
 *
 * @return string Internal type (e.g. "invoice") or "" when unknown.
 */
function pdf_footer_manager_detect_type($pdf)
{
    $class = get_class($pdf);

    foreach (pdf_footer_manager_document_types() as $type => $needle) {
        if (stripos($class, $needle) !== false) {
            return $type;
        }
    }

    return '';
}

/**
 * Return the final footer HTML for a given document type, with merge fields
 * already replaced. Falls back to the global footer when "apply to all" is on
 * or when no type-specific footer is configured.
 *
 * @return string Empty string when nothing should be rendered.
 */
function pdf_footer_manager_get_html($type)
{
    $apply_all = get_option('pdf_footer_manager_apply_all') == '1';

    $html = '';

    if (!$apply_all && $type !== ''
        && get_option('pdf_footer_manager_' . $type . '_enabled') == '1') {
        $html = (string) get_option('pdf_footer_manager_' . $type . '_html');
    }

    // Use the global footer when there is no enabled type-specific override.
    if ($html === '') {
        $html = (string) get_option('pdf_footer_manager_global_html');
    }

    $html = trim($html);

    if ($html === '') {
        return '';
    }

    return pdf_footer_manager_parse_merge_fields($html);
}

/**
 * Replace the supported merge fields with live company data.
 * mPDF native placeholders ({PAGENO}, {nbpg}, {DATE j-m-Y}) are left untouched
 * on purpose so the PDF engine resolves them per page.
 */
function pdf_footer_manager_parse_merge_fields($html)
{
    $replacements = hooks()->apply_filters('pdf_footer_manager_merge_fields', [
        '{company_name}'    => get_option('invoice_company_name'),
        '{company_address}' => trim(get_option('invoice_company_address')),
        '{company_city}'    => get_option('invoice_company_city'),
        '{company_phone}'   => get_option('invoice_company_phonenumber'),
        '{company_email}'   => get_option('smtp_email'),
        '{company_vat}'     => get_option('company_vat'),
        '{website}'         => get_option('companyname') ? site_url() : '',
        '{year}'            => date('Y'),
    ]);

    // Some company address fields may contain new lines; turn them into <br>.
    if (isset($replacements['{company_address}'])) {
        $replacements['{company_address}'] = nl2br($replacements['{company_address}']);
    }

    return str_replace(array_keys($replacements), array_values($replacements), $html);
}
