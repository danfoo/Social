<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Inject the configured footer into every Perfex PDF document.
 *
 * Perfex 3.x fires this action while building the PDF object
 * (application/libraries/pdf/App_pdf.php), before the document HTML is written:
 *
 *     hooks()->do_action('pdf_construct', ['pdf_instance' => $this, 'type' => $this->type()]);
 *
 * So the callback receives an associative array with the mPDF instance and the
 * document type ("invoice", "estimate", "proposal", "credit_note"...). We set the
 * HTML footer here so it repeats on every page, with no core file editing.
 */
hooks()->add_action('pdf_construct', 'pdf_footer_manager_apply');

function pdf_footer_manager_apply($data)
{
    if (get_option('pdf_footer_manager_enabled') != '1') {
        return $data;
    }

    $pdf  = is_array($data) && isset($data['pdf_instance']) ? $data['pdf_instance'] : $data;
    $type = is_array($data) && isset($data['type']) ? (string) $data['type'] : '';

    // We rely on mPDF's HTML footer API (Perfex >= 2.3). Bail out gracefully
    // on the legacy TCPDF engine, which does not expose SetHTMLFooter.
    if (!is_object($pdf) || !method_exists($pdf, 'SetHTMLFooter')) {
        return $data;
    }

    // Fall back to class-name detection if the hook did not provide a type.
    if ($type === '') {
        $type = pdf_footer_manager_detect_type($pdf);
    }

    $html = pdf_footer_manager_get_html($type);

    if ($html === '') {
        return $data;
    }

    // Reserve room at the bottom of the page for the footer (in millimetres).
    $margin = (int) get_option('pdf_footer_manager_margin');
    if ($margin > 0 && property_exists($pdf, 'margin_footer')) {
        $pdf->margin_footer = $margin;
    }

    $pdf->SetHTMLFooter($html);

    return $data;
}

/**
 * Resolve the internal document type from the concrete PDF class name, used only
 * as a fallback when the hook payload does not carry the type.
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
        '{website}'         => site_url(),
        '{year}'            => date('Y'),
    ]);

    // Some company address fields may contain new lines; turn them into <br>.
    if (isset($replacements['{company_address}'])) {
        $replacements['{company_address}'] = nl2br($replacements['{company_address}']);
    }

    return str_replace(array_keys($replacements), array_values($replacements), $html);
}
