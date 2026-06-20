<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Drivers extends AdminController
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

        $data['drivers']        = $this->fleet->get_drivers();
        $data['driver_role_id'] = get_option('fleet_driver_role_id');
        $data['title']          = _l('fleet_drivers');
        $this->load->view('fleet_management/drivers/manage', $data);
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
