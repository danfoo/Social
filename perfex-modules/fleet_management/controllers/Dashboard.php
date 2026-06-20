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

        $period = $this->input->get('period') ?: 'year';
        list($start, $end) = $this->_range($period);

        $data           = $this->fleet->dashboard($start, $end);
        $data['series'] = $this->fleet->monthly_expense_series(12);
        $data['period'] = $period;
        $data['title']  = _l('fleet_dashboard');
        $this->load->view('fleet_management/dashboard/manage', $data);
    }

    /**
     * Resolve a period keyword to a [start, end] SQL date range (or [null, null] for all time).
     */
    private function _range($period)
    {
        switch ($period) {
            case 'month':
                return [date('Y-m-01'), date('Y-m-t')];
            case 'quarter':
                $q     = (int) ceil(date('n') / 3);
                $first = ($q - 1) * 3 + 1;
                $start = date('Y-' . str_pad($first, 2, '0', STR_PAD_LEFT) . '-01');

                return [$start, date('Y-m-t', strtotime(date('Y-' . str_pad($first + 2, 2, '0', STR_PAD_LEFT) . '-01')))];
            case 'all':
                return [null, null];
            case 'year':
            default:
                return [date('Y-01-01'), date('Y-12-31')];
        }
    }
}
