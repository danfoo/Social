<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Approvals extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fleet_management/fleet_management_model', 'fleet');

        if (!fleet_is_approver()) {
            access_denied('fleet');
        }
    }

    public function index()
    {
        $status = $this->input->get('status');
        if (!in_array($status, ['pending', 'approved', 'rejected'], true)) {
            $status = 'pending';
        }

        $data['status']    = $status;
        $data['approvals'] = $this->fleet->get_approvals($status);
        $data['pending']   = $this->fleet->pending_approvals_count();
        $data['title']     = _l('fleet_approvals');
        $this->load->view('fleet_management/approvals/manage', $data);
    }

    public function approve($id)
    {
        if ($this->fleet->apply_approval($id)) {
            set_alert('success', _l('fleet_approval_approved_done'));
        } else {
            set_alert('warning', _l('fleet_approval_invalid'));
        }
        redirect(admin_url('fleet_management/approvals'));
    }

    public function reject($id)
    {
        if ($this->fleet->reject_approval($id, $this->input->post('note'))) {
            set_alert('success', _l('fleet_approval_rejected_done'));
        } else {
            set_alert('warning', _l('fleet_approval_invalid'));
        }
        redirect(admin_url('fleet_management/approvals'));
    }
}
