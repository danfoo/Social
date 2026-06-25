<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Maintenance extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('fleet_management/fleet_management_model', 'fleet');

        if (!fleet_can_feature('maintenance')) {
            access_denied('fleet');
        }
    }

    public function index()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $data['maintenance'] = $this->fleet->get_maintenance();
        $data['vehicles']    = $this->fleet->get_vehicle();
        $data['suppliers']   = $this->fleet->get_supplier();
        $data['title']       = _l('fleet_maintenance');
        $this->load->view('fleet_management/maintenance/manage', $data);
    }

    public function save()
    {
        if (!$this->input->post()) {
            redirect(admin_url('fleet_management/maintenance'));
        }

        $data = $this->input->post();
        $id   = $data['id'] ?? '';
        unset($data['id']);

        if ($id == '') {
            if (!staff_can('create', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->add_maintenance($data);
            set_alert('success', _l('added_successfully', _l('fleet_maintenance_record')));
        } else {
            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_maintenance($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_maintenance_record')));
        }

        redirect($this->input->server('HTTP_REFERER') ?: admin_url('fleet_management/maintenance'));
    }

    public function get($id)
    {
        if (!staff_can('view', 'fleet')) {
            ajax_access_denied();
        }

        echo json_encode($this->fleet->get_maintenance($id));
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->delete_maintenance($id);
        set_alert('success', _l('deleted', _l('fleet_maintenance_record')));
        redirect($this->input->server('HTTP_REFERER') ?: admin_url('fleet_management/maintenance'));
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            _l('fleet_vehicle'), _l('fleet_type'), _l('fleet_service_date'), _l('fleet_cost'),
            _l('fleet_odometer'), _l('fleet_provider'), _l('fleet_next_service_date'),
            _l('fleet_next_service_odometer'), _l('fleet_parts'),
        ];

        $rows = [];
        foreach ($this->fleet->get_maintenance() as $m) {
            $rows[] = [
                $m['vehicle_name'], _l('fleet_mtype_' . $m['type']),
                $m['service_date'] ? _d($m['service_date']) : '', $m['cost'], $m['odometer'],
                $m['provider'] ?? '', $m['next_service_date'] ? _d($m['next_service_date']) : '',
                $m['next_service_odometer'], $m['parts'] ?? '',
            ];
        }

        fleet_export_csv('maintenance', $headers, $rows);
    }

    /**
     * Photos attached to a maintenance record (each with the date it was taken).
     */
    public function files($id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $record = $this->fleet->get_maintenance($id);
        if (!$record) {
            show_404();
        }

        $data['record'] = $record;
        $data['files']  = $this->fleet->get_maintenance_files($id);
        $data['title']  = _l('fleet_maintenance_photos');
        $this->load->view('fleet_management/maintenance/files', $data);
    }

    public function upload_file($id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $record = $this->fleet->get_maintenance($id);
        if (!$record) {
            show_404();
        }

        if (isset($_FILES['file']) && $_FILES['file']['name'] != '') {
            $path = FCPATH . 'uploads/fleet_management/maintenance/' . $id . '/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $this->load->library('upload');
            $this->upload->initialize([
                'upload_path'   => $path,
                'allowed_types' => 'jpg|jpeg|png|gif|webp|heic',
                'max_size'      => 15000,
                'encrypt_name'  => true,
            ]);

            if ($this->upload->do_upload('file')) {
                $uploaded = $this->upload->data();
                $this->fleet->add_maintenance_file([
                    'maintenance_id' => $id,
                    'file_name'      => $uploaded['file_name'],
                    'original_name'  => $uploaded['orig_name'],
                    'taken_date'     => $this->input->post('taken_date'),
                ]);

                $this->fleet->log_activity(
                    $record->vehicle_id,
                    'photo',
                    _l('fleet_log_photo_added', isset($record->type) ? _l('fleet_mtype_' . $record->type) : '')
                );

                set_alert('success', _l('fleet_photo_uploaded'));
            } else {
                set_alert('warning', strip_tags($this->upload->display_errors()));
            }
        }

        redirect(admin_url('fleet_management/maintenance/files/' . $id));
    }

    public function delete_file($file_id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_maintenance_file($file_id);
        if ($file) {
            $full = FCPATH . 'uploads/fleet_management/maintenance/' . $file->maintenance_id . '/' . $file->file_name;
            if (is_file($full)) {
                @unlink($full);
            }
            $this->fleet->delete_maintenance_file($file_id);
            set_alert('success', _l('deleted', _l('fleet_photo')));
            redirect(admin_url('fleet_management/maintenance/files/' . $file->maintenance_id));
        }

        redirect(admin_url('fleet_management/maintenance'));
    }

    public function download_file($file_id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_maintenance_file($file_id);
        if (!$file) {
            show_404();
        }

        $full = FCPATH . 'uploads/fleet_management/maintenance/' . $file->maintenance_id . '/' . $file->file_name;
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
}
