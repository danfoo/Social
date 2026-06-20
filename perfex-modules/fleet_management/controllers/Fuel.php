<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fuel extends AdminController
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

        $data['logs']      = $this->fleet->get_fuel_log();
        $data['stats']     = $this->fleet->fuel_stats();
        $data['vehicles']  = $this->fleet->get_vehicle();
        $data['drivers']   = $this->fleet->get_drivers();
        $data['suppliers'] = $this->fleet->get_supplier('', 'fuel_station');
        $data['title']     = _l('fleet_fuel');
        $this->load->view('fleet_management/fuel/manage', $data);
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/fuel'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->add_fuel_log($data);
            set_alert('success', _l('added_successfully', _l('fleet_fuel_log')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_fuel_log($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_fuel_log')));
        }

        redirect(admin_url('fleet_management/fuel'));
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        echo json_encode($this->fleet->get_fuel_log($id));
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_fuel_log($id);
        set_alert('success', _l('deleted', _l('fleet_fuel_log')));
        redirect(admin_url('fleet_management/fuel'));
    }
}
