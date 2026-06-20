<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Lightweight TCPDF subclass used for the purchase order PDF so the company
 * footer is rendered by the native Footer() hook and therefore pinned to the
 * bottom of every page.
 */
class Fleet_po_pdf extends TCPDF
{
    public $footerHtml = '';

    public function Footer()
    {
        if ($this->footerHtml === '') {
            return;
        }
        $this->SetY(-24);
        $this->writeHTML($this->footerHtml, true, false, true, false, '');
    }
}
