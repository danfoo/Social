<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Reminders extends AdminController
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

        $data['reminders'] = $this->fleet->get_reminders();
        $data['vehicles']  = $this->fleet->get_vehicle();
        $data['title']     = _l('fleet_reminders');
        $this->load->view('fleet_management/reminders/manage', $data);
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/reminders'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->add_reminder($data);
            set_alert('success', _l('added_successfully', _l('fleet_reminder')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_reminder($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_reminder')));
        }

        redirect($this->input->server('HTTP_REFERER') ?: admin_url('fleet_management/reminders'));
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        echo json_encode($this->fleet->get_reminders($id));
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_reminder($id);
        set_alert('success', _l('deleted', _l('fleet_reminder')));
        redirect($this->input->server('HTTP_REFERER') ?: admin_url('fleet_management/reminders'));
    }
}
