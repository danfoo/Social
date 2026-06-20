<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: PDF Footer Manager
Description: Configure easily the footer of Perfex PDF documents (invoices, estimates, proposals, credit notes, contracts, payments...). The footer is injected automatically on every page through the native PDF engine (mPDF), with no core file editing, so it survives Perfex updates.
Version: 1.0.0
Requires at least: 2.3.*
Author: PERSO
*/

define('PDF_FOOTER_MANAGER_MODULE', 'pdf_footer_manager');

/**
 * Document types handled by the module and the App_pdf subclass that produces them.
 * The key is the internal type, the value is a fragment of the PDF class name used
 * to auto-detect the document type at render time (see helper).
 */
function pdf_footer_manager_document_types()
{
    return hooks()->apply_filters('pdf_footer_manager_document_types', [
        'invoice'     => 'Invoice_pdf',
        'estimate'    => 'Estimate_pdf',
        'proposal'    => 'Proposal_pdf',
        'credit_note' => 'Credit_note_pdf',
        'contract'    => 'Contract_pdf',
        'payment'     => 'Payment_pdf',
        'statement'   => 'Statement_pdf',
        'subscription'=> 'Subscription_pdf',
    ]);
}

/**
 * Run the install routine when the module is activated.
 */
register_activation_hook(PDF_FOOTER_MANAGER_MODULE, 'pdf_footer_manager_activation_hook');

function pdf_footer_manager_activation_hook()
{
    require_once __DIR__ . '/install.php';
}

/**
 * Register the settings page in the Setup menu.
 */
hooks()->add_action('admin_init', 'pdf_footer_manager_init_menu_items');

function pdf_footer_manager_init_menu_items()
{
    $CI = &get_instance();

    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item('pdf-footer-manager', [
            'name'     => _l('pdf_footer_manager'),
            'href'     => admin_url('pdf_footer_manager/settings'),
            'position' => 36,
        ]);
    }
}

/**
 * Load the helper that performs the actual footer injection into the PDF engine.
 * The helper registers itself on the PDF construction hooks.
 */
require_once __DIR__ . '/helpers/pdf_footer_manager_helper.php';
