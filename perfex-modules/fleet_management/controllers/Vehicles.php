<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Vehicles extends AdminController
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

        $data['vehicles']     = $this->fleet->get_vehicle();
        $data['status_counts'] = $this->fleet->vehicles_count_by_status();
        $data['title']        = _l('fleet_vehicles');
        $this->load->view('fleet_management/vehicles/manage', $data);
    }

    public function vehicle($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();

            if ($id == '') {
                if (!staff_can('create', 'fleet')) {
                    access_denied('fleet');
                }
                $id = $this->fleet->add_vehicle($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('fleet_vehicle')));
                }
                redirect(admin_url('fleet_management/vehicles/vehicle/' . $id));
            }

            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $success = $this->fleet->update_vehicle($id, $data);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('fleet_vehicle')));
            }
            redirect(admin_url('fleet_management/vehicles/vehicle/' . $id));
        }

        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $data['vehicle']    = $id == '' ? null : $this->fleet->get_vehicle($id);
        $data['drivers']    = $this->fleet->get_drivers();
        $data['categories'] = $this->fleet->get_categories();
        $data['brands']     = $this->fleet->get_brands();
        $data['models']     = $this->fleet->get_models();
        $data['insurers']   = $this->fleet->get_supplier('', 'insurance');
        $data['title']      = $data['vehicle'] ? $data['vehicle']->name : _l('fleet_add_vehicle');
        $this->load->view('fleet_management/vehicles/vehicle', $data);
    }

    public function view($id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $vehicle = $this->fleet->get_vehicle($id);
        if (!$vehicle) {
            show_404();
        }

        $data['vehicle']     = $vehicle;
        $data['maintenance'] = $this->fleet->get_maintenance('', $id);
        $data['reminders']   = $this->fleet->get_reminders('', $id);
        $data['assignments'] = $this->fleet->get_assignments($id);
        $data['activity']    = $this->fleet->get_activity($id);
        $data['parts']       = $this->fleet->get_part_assignment('', $id);
        $data['drivers']     = $this->fleet->get_drivers();
        $data['documents']   = $this->fleet->get_vehicle_files($id);
        $data['title']       = $vehicle->name;
        $this->load->view('fleet_management/vehicles/view', $data);
    }

    /* ---------------- Vehicle documents ---------------- */

    public function upload_document($vehicle_id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $vehicle = $this->fleet->get_vehicle($vehicle_id);
        if (!$vehicle) {
            show_404();
        }

        if (isset($_FILES['file']) && $_FILES['file']['name'] != '') {
            $path = FCPATH . 'uploads/fleet_management/vehicles/' . $vehicle_id . '/';
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }

            $this->load->library('upload');
            $this->upload->initialize([
                'upload_path'   => $path,
                'allowed_types' => 'jpg|jpeg|png|gif|webp|heic|pdf',
                'max_size'      => 15000,
                'encrypt_name'  => true,
            ]);

            if ($this->upload->do_upload('file')) {
                $uploaded = $this->upload->data();
                $this->fleet->add_vehicle_file([
                    'vehicle_id'    => $vehicle_id,
                    'type'          => $this->input->post('type'),
                    'title'         => $this->input->post('title'),
                    'issue_date'    => $this->input->post('issue_date'),
                    'expiry_date'   => $this->input->post('expiry_date'),
                    'note'          => $this->input->post('note'),
                    'file_name'     => $uploaded['file_name'],
                    'original_name' => $uploaded['orig_name'],
                ]);
                set_alert('success', _l('fleet_document_uploaded'));
            } else {
                set_alert('warning', strip_tags($this->upload->display_errors()));
            }
        }

        redirect(admin_url('fleet_management/vehicles/view/' . $vehicle_id) . '#tab_documents');
    }

    public function delete_document($file_id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_vehicle_file($file_id);
        if ($file) {
            $full = FCPATH . 'uploads/fleet_management/vehicles/' . $file->vehicle_id . '/' . $file->file_name;
            if (is_file($full)) {
                @unlink($full);
            }
            $this->fleet->delete_vehicle_file($file_id);
            set_alert('success', _l('deleted', _l('fleet_document')));
            redirect(admin_url('fleet_management/vehicles/view/' . $file->vehicle_id) . '#tab_documents');
        }

        redirect(admin_url('fleet_management/vehicles'));
    }

    public function download_document($file_id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_vehicle_file($file_id);
        if (!$file) {
            show_404();
        }

        $full = FCPATH . 'uploads/fleet_management/vehicles/' . $file->vehicle_id . '/' . $file->file_name;
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

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        if ($this->fleet->delete_vehicle($id)) {
            set_alert('success', _l('deleted', _l('fleet_vehicle')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('fleet_vehicle')));
        }

        redirect(admin_url('fleet_management/vehicles'));
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            _l('fleet_vehicle_name'), _l('fleet_plate'), _l('fleet_brand'), _l('fleet_model'),
            _l('fleet_year'), _l('fleet_category'), _l('fleet_fuel_type'), _l('fleet_seats'),
            _l('fleet_odometer'), _l('fleet_daily_rate'), _l('fleet_daily_rate_with_driver'),
            _l('fleet_insurance_company'), _l('fleet_status'),
        ];

        $rows = [];
        foreach ($this->fleet->get_vehicle() as $v) {
            $rows[] = [
                $v['name'], $v['plate'], $v['brand'], $v['model'], $v['year'], $v['category'],
                $v['fuel_type'], $v['seats'], $v['odometer'], $v['daily_rate'], $v['daily_rate_with_driver'],
                $v['insurance_company'], _l('fleet_status_' . $v['status']),
            ];
        }

        fleet_export_csv('vehicles', $headers, $rows);
    }

    public function assign_driver()
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $data = $this->input->post();
        $this->fleet->assign_driver($data);
        set_alert('success', _l('fleet_driver_assigned'));
        redirect(admin_url('fleet_management/vehicles/view/' . $data['vehicle_id']));
    }

    public function end_assignment($id, $vehicle_id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $this->fleet->end_assignment($id);
        set_alert('success', _l('fleet_assignment_ended'));
        redirect(admin_url('fleet_management/vehicles/view/' . $vehicle_id));
    }
}
