<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vehicles extends AdminController
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

        $data['vehicles']     = $this->fleet->get_vehicle();
        $data['status_counts'] = $this->fleet->vehicles_count_by_status();
        $data['title']        = _l('fleet_vehicles');
        $this->load->view('fleet_management/vehicles/manage', $data);
    }

    public function vehicle($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();

            if ($id == '') {
                if (!staff_can('create', 'fleet')) {
                    access_denied('fleet');
                }
                $id = $this->fleet->add_vehicle($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('fleet_vehicle')));
                }
                redirect(admin_url('fleet_management/vehicles/vehicle/' . $id));
            }

            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $success = $this->fleet->update_vehicle($id, $data);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('fleet_vehicle')));
            }
            redirect(admin_url('fleet_management/vehicles/vehicle/' . $id));
        }

        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $data['vehicle']    = $id == '' ? null : $this->fleet->get_vehicle($id);
        $data['drivers']    = $this->fleet->get_drivers();
        $data['categories'] = $this->fleet->get_categories();
        $data['brands']     = $this->fleet->get_brands();
        $data['models']     = $this->fleet->get_models();
        $data['insurers']   = $this->fleet->get_supplier('', 'insurance');
        $data['title']      = $data['vehicle'] ? $data['vehicle']->name : _l('fleet_add_vehicle');
        $this->load->view('fleet_management/vehicles/vehicle', $data);
    }

    public function view($id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $vehicle = $this->fleet->get_vehicle($id);
        if (!$vehicle) {
            show_404();
        }

        $data['vehicle']     = $vehicle;
        $data['maintenance'] = $this->fleet->get_maintenance('', $id);
        $data['reminders']   = $this->fleet->get_reminders('', $id);
        $data['assignments'] = $this->fleet->get_assignments($id);
        $data['drivers']     = $this->fleet->get_drivers();
        $data['title']       = $vehicle->name;
        $this->load->view('fleet_management/vehicles/view', $data);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        if ($this->fleet->delete_vehicle($id)) {
            set_alert('success', _l('deleted', _l('fleet_vehicle')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('fleet_vehicle')));
        }

        redirect(admin_url('fleet_management/vehicles'));
    }

    public function assign_driver()
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $data = $this->input->post();
        $this->fleet->assign_driver($data);
        set_alert('success', _l('fleet_driver_assigned'));
        redirect(admin_url('fleet_management/vehicles/view/' . $data['vehicle_id']));
    }

    public function end_assignment($id, $vehicle_id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->end_assignment($id);
        set_alert('success', _l('fleet_assignment_ended'));
        redirect(admin_url('fleet_management/vehicles/view/' . $vehicle_id));
    }
}
