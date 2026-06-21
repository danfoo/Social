<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fines extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fleet_management/fleet_management_model', 'fleet');
    }

    public function index()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $this->load->model('clients_model');
        $data['fines']    = $this->fleet->get_fine();
        $data['vehicles'] = $this->fleet->get_vehicle();
        $data['drivers']  = $this->fleet->get_drivers();
        $data['clients']  = $this->clients_model->get();
        $data['title']    = _l('fleet_fines');
        $this->load->view('fleet_management/fines/manage', $data);
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/fines'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->add_fine($data);
            set_alert('success', _l('added_successfully', _l('fleet_fine')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_fine($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_fine')));
        }

        redirect(admin_url('fleet_management/fines'));
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        $fine = $this->fleet->get_fine($id);
        if ($fine) {
            $fine->fine_date_display = $fine->fine_date ? _d($fine->fine_date) : '';
        }

        echo json_encode($fine);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_fine($id);
        set_alert('success', _l('deleted', _l('fleet_fine')));
        redirect(admin_url('fleet_management/fines'));
    }

    public function create_invoice($id)
    {
        if (!staff_can('create', 'fleet') || !has_permission('invoices', '', 'create')) {
            access_denied('fleet');
        }

        $invoice_id = $this->fleet->create_fine_invoice($id);

        if ($invoice_id) {
            set_alert('success', _l('fleet_invoice_created'));
            redirect(admin_url('invoices/list_invoices/' . $invoice_id));
        }

        set_alert('warning', _l('fleet_invoice_create_failed'));
        redirect(admin_url('fleet_management/fines'));
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            _l('fleet_fine_number'), _l('fleet_fine_type'), _l('fleet_date'), _l('fleet_vehicle'),
            _l('fleet_driver'), _l('fleet_client'), _l('fleet_location'), _l('fleet_amount'),
            _l('fleet_status'), _l('invoice'),
        ];

        $rows = [];
        foreach ($this->fleet->get_fine() as $f) {
            $rows[] = [
                $f['fine_number'], _l('fleet_ftype_' . $f['type']),
                $f['fine_date'] ? _d($f['fine_date']) : '', $f['vehicle_name'], $f['driver_name'],
                $f['client_name'], $f['location'], $f['amount'], _l('fleet_fstatus_' . $f['status']),
                !empty($f['invoice_id']) ? format_invoice_number($f['invoice_id']) : '',
            ];
        }

        fleet_export_csv('fines', $headers, $rows);
    }
}
