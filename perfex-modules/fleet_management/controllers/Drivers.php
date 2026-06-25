<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Drivers extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fleet_management/fleet_management_model', 'fleet');

        if (!fleet_can_feature('drivers')) {
            access_denied('fleet');
        }
    }

    public function index()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $data['drivers']        = $this->fleet->get_drivers();
        $data['driver_role_id'] = get_option('fleet_driver_role_id');
        $data['title']          = _l('fleet_drivers');
        $this->load->view('fleet_management/drivers/manage', $data);
    }

    public function profile($staff_id = '')
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $driver = $this->fleet->get_driver($staff_id);
        if (!$driver) {
            show_404();
        }

        $data['driver']      = $driver;
        $data['assignments'] = $this->fleet->get_driver_assignments($staff_id);
        $data['rentals']     = $this->fleet->get_driver_rentals($staff_id);
        $data['fuel']        = $this->fleet->get_driver_fuel($staff_id);
        $data['accidents']   = $this->fleet->get_driver_accidents($staff_id);
        $data['vehicles']    = $this->fleet->get_vehicle();
        $data['title']       = $driver->full_name;
        $this->load->view('fleet_management/drivers/profile', $data);
    }

    public function save_profile($staff_id = '')
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        if (!$this->fleet->is_driver($staff_id)) {
            show_404();
        }

        $this->fleet->save_driver_profile($staff_id, $this->input->post());
        set_alert('success', _l('updated_successfully', _l('fleet_driver_profile')));
        redirect(admin_url('fleet_management/drivers/profile/' . $staff_id));
    }

    public function accident_save()
    {
        if (!staff_can('create', 'fleet')) {
            access_denied('fleet');
        }

        $data     = $this->input->post();
        $id       = isset($data['id']) ? $data['id'] : '';
        $staff_id = $data['staff_id'];
        unset($data['id']);

        if ($id == '') {
            $this->fleet->add_accident($data);
            set_alert('success', _l('added_successfully', _l('fleet_accident')));
        } else {
            $this->fleet->update_accident($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_accident')));
        }

        redirect(admin_url('fleet_management/drivers/profile/' . $staff_id));
    }

    public function accident_get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        echo json_encode($this->fleet->get_accident($id));
    }

    public function accident_delete($id, $staff_id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_accident($id);
        set_alert('success', _l('deleted', _l('fleet_accident')));
        redirect(admin_url('fleet_management/drivers/profile/' . $staff_id));
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [_l('fleet_driver'), _l('email'), _l('fleet_phone'), _l('status')];

        $rows = [];
        foreach ($this->fleet->get_drivers() as $d) {
            $rows[] = [
                $d['full_name'], $d['email'], $d['phonenumber'],
                $d['active'] ? _l('active') : _l('inactive'),
            ];
        }

        fleet_export_csv('drivers', $headers, $rows);
    }
}
