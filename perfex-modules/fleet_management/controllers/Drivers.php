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
}
