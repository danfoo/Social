<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Inject the configured footer into every Perfex PDF document.
 *
 * Perfex 3.x fires these actions from application/libraries/pdf/App_pdf.php,
 * passing the engine instance and the document type:
 *
 *     hooks()->do_action('pdf_construct', ['pdf_instance' => $this, 'type' => $this->type()]);
 *     hooks()->do_action('pdf_footer',    ['pdf_instance' => $this, 'type' => $this->type()]);
 *
 * App_pdf may extend either \Mpdf\Mpdf OR TCPDF depending on the install. We
 * support both, without editing any core file:
 *
 *   - mPDF  : set the HTML footer once at construction (SetHTMLFooter).
 *   - TCPDF : write the footer on every page from the Footer() render hook.
 */
hooks()->add_action('pdf_construct', 'pdf_footer_manager_on_construct');
hooks()->add_action('pdf_footer', 'pdf_footer_manager_on_footer');

/**
 * mPDF path: set a repeating HTML footer once, during construction.
 */
function pdf_footer_manager_on_construct($data)
{
    if (get_option('pdf_footer_manager_enabled') != '1') {
        return $data;
    }

    [$pdf, $candidates] = pdf_footer_manager_extract($data);

    // Only mPDF exposes SetHTMLFooter; TCPDF is handled by the footer hook.
    if (!is_object($pdf) || !method_exists($pdf, 'SetHTMLFooter')) {
        return $data;
    }

    $html = pdf_footer_manager_get_html($candidates);
    if ($html === '') {
        return $data;
    }

    $margin = (int) get_option('pdf_footer_manager_margin');
    if ($margin > 0 && property_exists($pdf, 'margin_footer')) {
        $pdf->margin_footer = $margin;
    }

    // mPDF resolves {PAGENO} / {nbpg} natively, nothing to convert.
    $pdf->SetHTMLFooter($html);

    return $data;
}

/**
 * TCPDF path: this hook fires inside App_pdf::Footer(), once per page.
 */
function pdf_footer_manager_on_footer($data)
{
    if (get_option('pdf_footer_manager_enabled') != '1') {
        return $data;
    }

    [$pdf, $candidates] = pdf_footer_manager_extract($data);

    // Skip when running on mPDF (already handled at construction) or when the
    // object cannot render HTML cells.
    if (!is_object($pdf)
        || method_exists($pdf, 'SetHTMLFooter')
        || !method_exists($pdf, 'writeHTMLCell')) {
        return $data;
    }

    $html = pdf_footer_manager_get_html($candidates);
    if ($html === '') {
        return $data;
    }

    // Resolve page-number tokens to TCPDF aliases (replaced by TCPDF on output).
    $html = str_replace(
        ['{PAGENO}', '{nbpg}'],
        [$pdf->getAliasNumPage(), $pdf->getAliasNbPages()],
        $html
    );

    $margin = (int) get_option('pdf_footer_manager_margin');
    if ($margin <= 0) {
        $margin = 12;
    }

    // Position from the bottom of the page and write the footer HTML.
    // Auto page-break is disabled by TCPDF while Footer() runs, so this is safe.
    $pdf->SetY(-1 * $margin);
    $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, false, true, 'C', true);

    return $data;
}

/**
 * Normalise the hook payload into [pdf_instance, candidate_types].
 *
 * We keep *several* candidate type keys: the one provided by the hook AND the one
 * derived from the concrete PDF class name (Invoice_pdf -> invoice). This way the
 * per-type footer resolves correctly whether or not Perfex's type() string
 * matches our option keys exactly.
 */
function pdf_footer_manager_extract($data)
{
    if (is_array($data)) {
        $pdf  = $data['pdf_instance'] ?? ($data[0] ?? null);
        $type = (string) ($data['type'] ?? '');
    } else {
        $pdf  = $data;
        $type = '';
    }

    $candidates = [];
    if ($type !== '') {
        $candidates[] = $type;
    }
    if (is_object($pdf)) {
        $byClass = pdf_footer_manager_detect_type($pdf);
        if ($byClass !== '') {
            $candidates[] = $byClass;
        }
    }

    return [$pdf, array_values(array_unique($candidates))];
}

/**
 * Resolve the internal document type from the concrete PDF class name.
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
 * Return the final footer HTML, with company merge fields already replaced.
 * A per-type footer wins whenever its checkbox is enabled and it is not empty;
 * otherwise the global footer is used. Page-number tokens ({PAGENO}/{nbpg}) are
 * kept intact so each engine can resolve them.
 *
 * @param array $candidates Ordered candidate type keys for this document.
 * @return string Empty string when nothing should be rendered.
 */
function pdf_footer_manager_get_html($candidates)
{
    $html = '';

    foreach ((array) $candidates as $type) {
        if ($type !== '' && get_option('pdf_footer_manager_' . $type . '_enabled') == '1') {
            $candidate_html = trim((string) get_option('pdf_footer_manager_' . $type . '_html'));
            if ($candidate_html !== '') {
                $html = $candidate_html;
                break;
            }
        }
    }

    // No enabled, non-empty per-type override: fall back to the global footer.
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
 * Replace the supported company merge fields with live data.
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

    if (isset($replacements['{company_address}'])) {
        $replacements['{company_address}'] = nl2br($replacements['{company_address}']);
    }

    return str_replace(array_keys($replacements), array_values($replacements), $html);
}
