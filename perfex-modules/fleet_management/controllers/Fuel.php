<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fuel extends AdminController
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

        // Dashboard granularity: day (default) / week / month / year, or a
        // specific date when the ?date=YYYY-MM-DD parameter is supplied.
        $period = $this->input->get('period');
        if (!in_array($period, ['day', 'week', 'month', 'year'], true)) {
            $period = 'day';
        }

        $specific = $this->input->get('date');
        $specific = ($specific && preg_match('/^\d{4}-\d{2}-\d{2}$/', $specific)) ? $specific : null;

        if ($specific) {
            $start = $end = $specific;
        } else {
            list($start, $end) = fleet_fuel_period_range($period);
        }

        $supplier_id = $this->input->get('supplier_id');
        $supplier_id = is_numeric($supplier_id) ? (int) $supplier_id : null;

        $data['period']        = $period;
        $data['specific_date'] = $specific;
        $data['filter_start'] = $start;
        $data['filter_end']   = $end;
        $data['supplier_id']  = $supplier_id;
        $data['logs']        = $this->fleet->get_fuel_log('', '', $start, $end, $supplier_id);
        $data['stats']       = $this->fleet->fuel_stats('', $start, $end, $supplier_id);
        $data['by_station']  = $this->fleet->fuel_by_station($start, $end);
        $data['vehicles']    = $this->fleet->get_vehicle();
        $data['drivers']     = $this->fleet->get_drivers();
        $data['suppliers']   = $this->fleet->get_supplier('', 'fuel_station');
        $data['title']       = _l('fleet_fuel');
        $this->load->view('fleet_management/fuel/manage', $data);
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/fuel'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $id = $this->fleet->add_fuel_log($data);
            set_alert('success', _l('added_successfully', _l('fleet_fuel_log')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_fuel_log($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_fuel_log')));
        }

        if ($id) {
            $this->_upload_photos($id);
        }

        redirect(admin_url('fleet_management/fuel'));
    }

    /** Store any photos attached to a fuel entry (supports multiple files). */
    private function _upload_photos($fuel_id)
    {
        if (empty($_FILES['files']['name']) || empty($_FILES['files']['name'][0])) {
            return;
        }

        $path = FCPATH . 'uploads/fleet_management/fuel/' . $fuel_id . '/';
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }

        $this->load->library('upload');
        $count = count($_FILES['files']['name']);

        for ($i = 0; $i < $count; $i++) {
            if (empty($_FILES['files']['name'][$i])) {
                continue;
            }

            $_FILES['fuel_photo'] = [
                'name'     => $_FILES['files']['name'][$i],
                'type'     => $_FILES['files']['type'][$i],
                'tmp_name' => $_FILES['files']['tmp_name'][$i],
                'error'    => $_FILES['files']['error'][$i],
                'size'     => $_FILES['files']['size'][$i],
            ];

            $this->upload->initialize([
                'upload_path'   => $path,
                'allowed_types' => 'jpg|jpeg|png|gif|webp|heic',
                'max_size'      => 15000,
                'encrypt_name'  => true,
            ]);

            if ($this->upload->do_upload('fuel_photo')) {
                $uploaded = $this->upload->data();
                $this->fleet->add_fuel_file([
                    'fuel_id'       => $fuel_id,
                    'file_name'     => $uploaded['file_name'],
                    'original_name' => $uploaded['orig_name'],
                ]);
            }
        }
    }

    public function delete_file($file_id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_fuel_file($file_id);
        if ($file) {
            $full = FCPATH . 'uploads/fleet_management/fuel/' . $file->fuel_id . '/' . $file->file_name;
            if (is_file($full)) {
                @unlink($full);
            }
            $this->fleet->delete_fuel_file($file_id);
            set_alert('success', _l('deleted', _l('fleet_photo')));
        }

        redirect(admin_url('fleet_management/fuel'));
    }

    public function image($file_id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_fuel_file($file_id);
        if (!$file) {
            show_404();
        }

        $full = FCPATH . 'uploads/fleet_management/fuel/' . $file->fuel_id . '/' . $file->file_name;
        if (!is_file($full)) {
            show_404();
        }

        $mime = function_exists('mime_content_type') ? mime_content_type($full) : 'application/octet-stream';
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $file->original_name . '"');
        header('Content-Length: ' . filesize($full));
        readfile($full);
        exit;
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            _l('fleet_date'), _l('fleet_vehicle'), _l('fleet_driver'), _l('fleet_odometer'),
            _l('fleet_liters'), _l('fleet_price_per_liter'), _l('fleet_total_cost'),
            _l('fleet_station'), _l('fleet_full_tank'),
        ];

        $period = $this->input->get('period');
        if (!in_array($period, ['day', 'week', 'month', 'year'], true)) {
            $period = 'day';
        }
        $specific = $this->input->get('date');
        $specific = ($specific && preg_match('/^\d{4}-\d{2}-\d{2}$/', $specific)) ? $specific : null;
        if ($specific) {
            $start = $end = $specific;
        } else {
            list($start, $end) = fleet_fuel_period_range($period);
        }
        $supplier_id = $this->input->get('supplier_id');
        $supplier_id = is_numeric($supplier_id) ? (int) $supplier_id : null;

        $rows = [];
        foreach ($this->fleet->get_fuel_log('', '', $start, $end, $supplier_id) as $f) {
            $rows[] = [
                $f['date'] ? _d($f['date']) : '', $f['vehicle_name'], $f['driver_name'],
                $f['odometer'], $f['liters'], $f['price_per_liter'], $f['total_cost'],
                $f['supplier_name'], $f['full_tank'] ? _l('yes') : _l('no'),
            ];
        }

        fleet_export_csv('fuel', $headers, $rows);
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        $log = $this->fleet->get_fuel_log($id);
        if ($log) {
            $log->date_display = $log->date ? _d($log->date) : '';
            $files = [];
            foreach ($this->fleet->get_fuel_files($id) as $f) {
                $files[] = [
                    'id'  => $f['id'],
                    'url' => admin_url('fleet_management/fuel/image/' . $f['id']),
                    'del' => admin_url('fleet_management/fuel/delete_file/' . $f['id']),
                ];
            }
            $log->files = $files;
        }

        echo json_encode($log);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_fuel_log($id);
        set_alert('success', _l('deleted', _l('fleet_fuel_log')));
        redirect(admin_url('fleet_management/fuel'));
    }
}
