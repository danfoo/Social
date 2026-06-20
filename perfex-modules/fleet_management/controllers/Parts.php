<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Parts extends AdminController
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

        $data['parts']        = $this->fleet->get_part();
        $data['stats']        = $this->fleet->parts_stats();
        $data['vehicles']     = $this->fleet->get_vehicle();
        $data['suppliers']    = $this->fleet->get_supplier();
        $data['maintenances'] = $this->fleet->get_maintenance();
        $data['title']        = _l('fleet_parts_articles');
        $this->load->view('fleet_management/parts/manage', $data);
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/parts'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->add_part($data);
            set_alert('success', _l('added_successfully', _l('fleet_part')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_part($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_part')));
        }

        redirect(admin_url('fleet_management/parts'));
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            _l('fleet_part_name'), _l('fleet_reference'), _l('fleet_vehicle'), _l('fleet_supplier'),
            _l('fleet_quantity'), _l('fleet_unit_price'), _l('fleet_total'), _l('fleet_purchase_date'),
            _l('fleet_status'),
        ];

        $rows = [];
        foreach ($this->fleet->get_part() as $p) {
            $rows[] = [
                $p['name'], $p['reference'], $p['vehicle_name'], $p['supplier_name'],
                $p['quantity'], $p['unit_price'], $p['total_price'],
                $p['purchase_date'] ? _d($p['purchase_date']) : '', _l('fleet_pstatus_' . $p['status']),
            ];
        }

        fleet_export_csv('parts', $headers, $rows);
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        echo json_encode($this->fleet->get_part($id));
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_part($id);
        set_alert('success', _l('deleted', _l('fleet_part')));
        redirect(admin_url('fleet_management/parts'));
    }
}
