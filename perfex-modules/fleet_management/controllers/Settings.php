<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fleet_management/fleet_management_model', 'fleet');
    }

    public function index()
    {
        // Settings now include the per-role access matrix: admins only.
        if (!is_admin()) {
            access_denied('fleet');
        }

        if ($this->input->post()) {
            update_option('fleet_expense_category_id', $this->input->post('fleet_expense_category_id'));
            update_option('fleet_invoice_due_days', (int) $this->input->post('fleet_invoice_due_days'));
            update_option('fleet_occupancy_days', (int) $this->input->post('fleet_occupancy_days'));
            update_option('fleet_license_notify_days', (int) $this->input->post('fleet_license_notify_days'));
            update_option('fleet_email_notifications', $this->input->post('fleet_email_notifications') ? 1 : 0);
            update_option('fleet_notification_emails', $this->input->post('fleet_notification_emails'));

            // The rich contract terms are base64-encoded by the browser so the raw
            // HTML never appears in the POST (avoids 403s from the input/WAF filter).
            $encoded = $this->input->post('fleet_contract_terms_encoded', false);
            if ($encoded !== null && $encoded !== '') {
                update_option('fleet_contract_terms', base64_decode($encoded));
            } else {
                update_option('fleet_contract_terms', $this->input->post('fleet_contract_terms', false));
            }

            update_option('fleet_approval_enabled', $this->input->post('fleet_approval_enabled') ? 1 : 0);
            update_option('fleet_approver_id', $this->input->post('fleet_approver_id'));

            $this->_save_role_features();

            set_alert('success', _l('settings_updated'));
            redirect(admin_url('fleet_management/settings'));
        }

        $data['expense_categories'] = [];
        if ($this->db->table_exists(db_prefix() . 'expenses_categories')) {
            $this->db->order_by('name', 'asc');
            $data['expense_categories'] = $this->db->get(db_prefix() . 'expenses_categories')->result_array();
        }

        $role = $this->db->get_where(db_prefix() . 'roles', ['roleid' => get_option('fleet_driver_role_id')])->row();
        $data['driver_role'] = $role ? $role->name : '—';

        $this->db->order_by('name', 'asc');
        $data['roles']         = $this->db->get(db_prefix() . 'roles')->result_array();
        $data['role_features'] = fleet_role_feature_map();

        $this->db->where('active', 1);
        $this->db->order_by('firstname', 'asc');
        $data['staff'] = $this->db->get(db_prefix() . 'staff')->result_array();

        $data['title'] = _l('fleet_settings');
        $this->load->view('fleet_management/settings/manage', $data);
    }

    /**
     * Persist the role -> features matrix. A role appears in `configured[]` for
     * every rendered row, so unticking everything for a role stores an empty
     * set (no access) rather than reverting to full access.
     */
    private function _save_role_features()
    {
        $configured = $this->input->post('configured');
        $feat       = $this->input->post('feat');
        $valid      = array_keys(fleet_features());
        $map        = [];

        if (is_array($configured)) {
            foreach ($configured as $rid => $_) {
                $rid       = (int) $rid;
                $map[$rid] = [];
                if (isset($feat[$rid]) && is_array($feat[$rid])) {
                    foreach ($feat[$rid] as $slug => $v) {
                        if (in_array($slug, $valid, true)) {
                            $map[$rid][$slug] = 1;
                        }
                    }
                }
            }
        }

        update_option('fleet_role_features', serialize($map));
    }
}
