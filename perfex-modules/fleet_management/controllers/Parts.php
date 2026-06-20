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

        $data['items']        = $this->fleet->get_part_item();
        $data['orders']       = $this->fleet->get_part_order();
        $data['assignments']  = $this->fleet->get_part_assignment();
        $data['stats']        = $this->fleet->part_items_stats();
        $data['suppliers']    = $this->fleet->get_supplier();
        $data['vehicles']     = $this->fleet->get_vehicle();
        $data['maintenances']    = $this->fleet->get_maintenance();
        $data['part_categories'] = $this->fleet->get_part_categories();
        $data['part_units']      = $this->fleet->get_part_units();
        $data['title']           = _l('fleet_parts_articles');
        $this->load->view('fleet_management/parts/manage', $data);
    }

    /* ---------------- Catalog items ---------------- */

    public function item_save()
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
            $this->fleet->add_part_item($data);
            set_alert('success', _l('added_successfully', _l('fleet_part')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_part_item($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_part')));
        }
        redirect(admin_url('fleet_management/parts'));
    }

    public function item_get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }
        echo json_encode($this->fleet->get_part_item($id));
    }

    public function item_delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->delete_part_item($id);
        set_alert('success', _l('deleted', _l('fleet_part')));
        redirect(admin_url('fleet_management/parts'));
    }

    /* ---------------- Orders (purchases) ---------------- */

    public function order_save()
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
            $this->fleet->add_part_order($data);
            set_alert('success', _l('fleet_order_placed'));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_part_order($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_order')));
        }
        redirect(admin_url('fleet_management/parts#orders'));
    }

    public function order_get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }
        echo json_encode($this->fleet->get_part_order($id));
    }

    public function order_receive($id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->receive_part_order($id);
        set_alert('success', _l('fleet_order_received_done'));
        redirect(admin_url('fleet_management/parts#orders'));
    }

    public function order_cancel($id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->cancel_part_order($id);
        set_alert('success', _l('fleet_order_cancelled'));
        redirect(admin_url('fleet_management/parts#orders'));
    }

    public function order_delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->delete_part_order($id);
        set_alert('success', _l('deleted', _l('fleet_order')));
        redirect(admin_url('fleet_management/parts#orders'));
    }

    /* ---------------- Assignments (use stock) ---------------- */

    public function assignment_save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/parts'));
        }
        if (!staff_can('create', 'fleet')) {
            access_denied('fleet');
        }

        $data    = $this->input->post();
        $item_id = (int) ($data['item_id'] ?? 0);
        $qty     = (int) ($data['quantity'] ?? 1);
        $stock   = $this->fleet->item_stock($item_id);

        if ($qty > $stock) {
            set_alert('warning', _l('fleet_not_enough_stock', $stock));
            redirect(admin_url('fleet_management/parts#stock'));
        }

        $this->fleet->add_part_assignment($data);
        set_alert('success', _l('fleet_part_assigned_done'));
        redirect(admin_url('fleet_management/parts#stock'));
    }

    public function assignment_delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->delete_part_assignment($id);
        set_alert('success', _l('deleted', _l('fleet_assignment_label')));
        redirect(admin_url('fleet_management/parts#stock'));
    }

    /* ---------------- Export ---------------- */

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            _l('fleet_part_name'), _l('fleet_reference'), _l('fleet_category'),
            _l('fleet_unit'), _l('fleet_stock'), _l('fleet_min_stock'),
        ];

        $rows = [];
        foreach ($this->fleet->get_part_item() as $it) {
            $rows[] = [$it['name'], $it['reference'], $it['category'], $it['unit'], $it['stock'], $it['min_stock']];
        }

        fleet_export_csv('parts-catalog', $headers, $rows);
    }
}
