<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Library extends AdminController
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

        $data['categories'] = $this->fleet->get_categories();
        $data['brands']     = $this->fleet->get_brands();
        $data['models']     = $this->fleet->get_models();
        $data['title']      = _l('fleet_library');
        $this->load->view('fleet_management/library/manage', $data);
    }

    public function category_save()
    {
        if (!staff_can('create', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->add_category($this->input->post('name'));
        set_alert('success', _l('added_successfully', _l('fleet_category')));
        redirect(admin_url('fleet_management/library'));
    }

    public function category_delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->delete_category($id);
        set_alert('success', _l('deleted', _l('fleet_category')));
        redirect(admin_url('fleet_management/library'));
    }

    public function brand_save()
    {
        if (!staff_can('create', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->add_brand($this->input->post('name'));
        set_alert('success', _l('added_successfully', _l('fleet_brand')));
        redirect(admin_url('fleet_management/library'));
    }

    public function brand_delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->delete_brand($id);
        set_alert('success', _l('deleted', _l('fleet_brand')));
        redirect(admin_url('fleet_management/library'));
    }

    public function model_save()
    {
        if (!staff_can('create', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->add_model($this->input->post('brand_id'), $this->input->post('name'));
        set_alert('success', _l('added_successfully', _l('fleet_model_label')));
        redirect(admin_url('fleet_management/library'));
    }

    public function model_delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->delete_model($id);
        set_alert('success', _l('deleted', _l('fleet_model_label')));
        redirect(admin_url('fleet_management/library'));
    }
}
