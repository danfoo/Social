<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Dashboard extends AdminController
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

        $data         = $this->fleet->dashboard();
        $data['title'] = _l('fleet_dashboard');
        $this->load->view('fleet_management/dashboard/manage', $data);
    }
}
