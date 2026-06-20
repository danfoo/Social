<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Pdf_footer_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Settings page: read on GET, persist on POST.
     */
    public function settings()
    {
        if (!is_admin()) {
            access_denied('PDF Footer Manager');
        }

        if ($this->input->post()) {
            $this->save_settings();
            set_alert('success', _l('settings_updated'));
            redirect(admin_url('pdf_footer_manager/settings'));
        }

        $data['types'] = pdf_footer_manager_document_types();
        $data['title'] = _l('pdf_footer_manager');
        $this->load->view('settings', $data);
    }

    private function save_settings()
    {
        $post = $this->input->post(null, false); // false: keep raw HTML

        update_option('pdf_footer_manager_enabled', isset($post['pdf_footer_manager_enabled']) ? 1 : 0);
        update_option('pdf_footer_manager_apply_all', isset($post['pdf_footer_manager_apply_all']) ? 1 : 0);
        update_option('pdf_footer_manager_margin', (int) ($post['pdf_footer_manager_margin'] ?? 9));
        update_option('pdf_footer_manager_global_html', $post['pdf_footer_manager_global_html'] ?? '');

        foreach (pdf_footer_manager_document_types() as $type => $class) {
            update_option(
                'pdf_footer_manager_' . $type . '_enabled',
                isset($post['pdf_footer_manager_' . $type . '_enabled']) ? 1 : 0
            );
            update_option(
                'pdf_footer_manager_' . $type . '_html',
                $post['pdf_footer_manager_' . $type . '_html'] ?? ''
            );
        }
    }
}
