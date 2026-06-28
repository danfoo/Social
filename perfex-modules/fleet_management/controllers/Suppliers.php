<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fleet_management/fleet_management_model', 'fleet');

        if (!fleet_can_feature('suppliers')) {
            access_denied('fleet');
        }
    }

    public function index()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $data['suppliers'] = $this->fleet->get_supplier();
        $data['title']     = _l('fleet_suppliers');
        $this->load->view('fleet_management/suppliers/manage', $data);
    }

    public function view($id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $supplier = $this->fleet->get_supplier($id);
        if (!$supplier) {
            show_404();
        }

        $period            = $this->input->get('period') ?: 'all';
        list($start, $end) = fleet_period_range($period);

        $this->load->model('payment_modes_model');

        $data['supplier']      = $supplier;
        $data['orders']        = $this->fleet->get_supplier_orders($id, $start, $end);
        $data['costs']         = $this->fleet->get_supplier_costs($id, $start, $end);
        $data['expenses']      = $this->fleet->get_supplier_assigned_expenses($id, $start, $end);
        $data['payments']      = $this->fleet->get_supplier_payments($id, $start, $end);
        $data['payment_modes'] = $this->payment_modes_model->get('', ['active' => 1]);
        $data['summary']       = $this->fleet->supplier_accounting($id, $start, $end);
        $data['period']        = $period;
        $data['title']         = $supplier->name;
        $this->load->view('fleet_management/suppliers/view', $data);
    }

    public function export_ledger($id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }
        $supplier = $this->fleet->get_supplier($id);
        if (!$supplier) {
            show_404();
        }

        list($start, $end) = fleet_period_range($this->input->get('period') ?: 'all');

        $headers = [
            _l('fleet_date'), _l('fleet_part') . ' / ' . _l('fleet_type'), _l('fleet_vehicle'),
            _l('fleet_quantity'), _l('fleet_total'), _l('fleet_supplier_invoice_no'), _l('fleet_payment'),
        ];

        $rows = [];
        foreach ($this->fleet->get_supplier_orders($id, $start, $end) as $o) {
            $rows[] = [
                $o['order_date'] ? _d($o['order_date']) : '',
                $o['items_summary'] . ' [' . _l('fleet_ostatus_' . $o['status']) . ']',
                '', $o['quantity'], $o['total_price'], $o['invoice_no'],
                $o['paid'] ? _l('fleet_paid') : _l('fleet_unpaid'),
            ];
        }
        foreach ($this->fleet->get_supplier_costs($id, $start, $end) as $c) {
            $rows[] = [
                $c['date'] ? _d($c['date']) : '', strip_tags($c['label']), $c['vehicle'],
                '', $c['amount'], '', $c['paid'] ? _l('fleet_paid') : _l('fleet_unpaid'),
            ];
        }

        fleet_export_csv('supplier-' . $id . '-ledger', $headers, $rows);
    }

    public function pay()
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $table       = $this->input->post('source_table');
        $sid         = (int) $this->input->post('source_id');
        $supplier_id = (int) $this->input->post('supplier_id');
        $amount      = (float) $this->input->post('amount');

        $remaining = $this->fleet->record_total($table, $sid) - $this->fleet->record_paid($table, $sid);
        if ($amount > $remaining + 0.001) {
            $amount = $remaining;
        }

        if ($this->fleet->add_payment($table, $sid, $supplier_id, $amount, $this->input->post('payment_date'), $this->input->post('payment_mode'), $this->input->post('note'))) {
            set_alert('success', _l('fleet_payment_recorded'));
        } else {
            set_alert('warning', _l('fleet_payment_invalid'));
        }

        redirect(admin_url('fleet_management/suppliers/view/' . $supplier_id));
    }

    public function delete_payment($payment_id, $supplier_id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_payment($payment_id);
        set_alert('success', _l('fleet_payment_deleted'));
        redirect(admin_url('fleet_management/suppliers/view/' . $supplier_id));
    }

    public function mark_paid($table, $id, $paid)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }
        $this->fleet->mark_paid($table, $id, (int) $paid);
        set_alert('success', _l('fleet_payment_updated'));
        redirect($this->input->server('HTTP_REFERER') ?: admin_url('fleet_management/suppliers'));
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/suppliers'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->add_supplier($data);
            set_alert('success', _l('added_successfully', _l('fleet_supplier')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_supplier($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_supplier')));
        }

        redirect(admin_url('fleet_management/suppliers'));
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            _l('fleet_supplier_name'), _l('fleet_type'), _l('fleet_contact_name'), _l('fleet_phone'),
            _l('email'), _l('fleet_website'), _l('fleet_vat'), _l('status'),
        ];

        $rows = [];
        foreach ($this->fleet->get_supplier() as $s) {
            $rows[] = [
                $s['name'], _l('fleet_stype_' . $s['type']), $s['contact_name'], $s['phone'],
                $s['email'], $s['website'], $s['vat'], $s['active'] ? _l('active') : _l('inactive'),
            ];
        }

        fleet_export_csv('suppliers', $headers, $rows);
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        echo json_encode($this->fleet->get_supplier($id));
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_supplier($id);
        set_alert('success', _l('deleted', _l('fleet_supplier')));
        redirect(admin_url('fleet_management/suppliers'));
    }
}
