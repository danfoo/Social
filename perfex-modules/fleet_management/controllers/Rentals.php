<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rentals extends AdminController
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

        $data['rentals'] = $this->fleet->get_rental();
        $data['title']   = _l('fleet_rentals');
        $this->load->view('fleet_management/rentals/manage', $data);
    }

    public function rental($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();

            if ($id == '') {
                if (!staff_can('create', 'fleet')) {
                    access_denied('fleet');
                }
                $id = $this->fleet->add_rental($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('fleet_rental')));
                }
                redirect(admin_url('fleet_management/rentals/rental/' . $id));
            }

            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_rental($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_rental')));
            redirect(admin_url('fleet_management/rentals/rental/' . $id));
        }

        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $this->load->model('clients_model');
        $data['rental']            = $id == '' ? null : $this->fleet->get_rental($id);
        $data['vehicles']          = $this->fleet->get_vehicle();
        $data['drivers']           = $this->fleet->get_drivers();
        $data['clients']           = $this->clients_model->get();
        $data['preselect_vehicle'] = $this->input->get('vehicle_id');
        $data['title']             = $data['rental'] ? _l('fleet_rental') . ' #' . $id : _l('fleet_add_rental');
        $this->load->view('fleet_management/rentals/rental', $data);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        if ($this->fleet->delete_rental($id)) {
            set_alert('success', _l('deleted', _l('fleet_rental')));
        }

        redirect(admin_url('fleet_management/rentals'));
    }

    public function create_invoice($id)
    {
        if (!staff_can('create', 'fleet') || !has_permission('invoices', '', 'create')) {
            access_denied('fleet');
        }

        $invoice_id = $this->fleet->create_invoice($id);

        if ($invoice_id) {
            set_alert('success', _l('fleet_invoice_created'));
            redirect(admin_url('invoices/list_invoices/' . $invoice_id));
        }

        set_alert('warning', _l('fleet_invoice_create_failed'));
        redirect(admin_url('fleet_management/rentals/rental/' . $id));
    }
}
