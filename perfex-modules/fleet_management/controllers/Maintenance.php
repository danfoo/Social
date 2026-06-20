<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance extends AdminController
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

        $data['maintenance'] = $this->fleet->get_maintenance();
        $data['vehicles']    = $this->fleet->get_vehicle();
        $data['suppliers']   = $this->fleet->get_supplier();
        $data['title']       = _l('fleet_maintenance');
        $this->load->view('fleet_management/maintenance/manage', $data);
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/maintenance'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->add_maintenance($data);
            set_alert('success', _l('added_successfully', _l('fleet_maintenance_record')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_maintenance($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_maintenance_record')));
        }

        redirect($this->input->server('HTTP_REFERER') ?: admin_url('fleet_management/maintenance'));
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        echo json_encode($this->fleet->get_maintenance($id));
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_maintenance($id);
        set_alert('success', _l('deleted', _l('fleet_maintenance_record')));
        redirect($this->input->server('HTTP_REFERER') ?: admin_url('fleet_management/maintenance'));
    }
}
