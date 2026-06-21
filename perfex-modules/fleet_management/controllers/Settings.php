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
        if (!is_admin() && !staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        if ($this->input->post()) {
            update_option('fleet_expense_category_id', $this->input->post('fleet_expense_category_id'));
            update_option('fleet_invoice_due_days', (int) $this->input->post('fleet_invoice_due_days'));
            update_option('fleet_occupancy_days', (int) $this->input->post('fleet_occupancy_days'));
            update_option('fleet_license_notify_days', (int) $this->input->post('fleet_license_notify_days'));
            update_option('fleet_email_notifications', $this->input->post('fleet_email_notifications') ? 1 : 0);
            update_option('fleet_notification_emails', $this->input->post('fleet_notification_emails'));
            update_option('fleet_contract_terms', $this->input->post('fleet_contract_terms', false));
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

        $data['title'] = _l('fleet_settings');
        $this->load->view('fleet_management/settings/manage', $data);
    }
}
