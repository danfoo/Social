<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Rentals extends AdminController
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

        $data['rentals'] = $this->fleet->get_rental();
        $data['title']   = _l('fleet_rentals');
        $this->load->view('fleet_management/rentals/manage', $data);
    }

    public function planning()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        // Selected month (Y-m), defaulting to the current month.
        $month = $this->input->get('month');
        if (!$month || !preg_match('/^\d{4}-\d{2}$/', $month)) {
            $month = date('Y-m');
        }

        $range_start = $month . '-01';
        $range_end   = date('Y-m-t', strtotime($range_start));

        $data['month']       = $month;
        $data['range_start'] = $range_start;
        $data['range_end']   = $range_end;
        $data['prev_month']  = date('Y-m', strtotime($range_start . ' -1 month'));
        $data['next_month']  = date('Y-m', strtotime($range_start . ' +1 month'));
        $data['vehicles']    = $this->fleet->get_vehicle();
        $data['rentals']     = $this->fleet->get_rentals_in_range($range_start, $range_end);
        $data['title']       = _l('fleet_planning');
        $this->load->view('fleet_management/rentals/planning', $data);
    }

    public function rental($id = '')
    {
        if ($this->input->post()) {
            $data = $this->input->post();

            // Guard against double-booking the same vehicle on overlapping dates.
            if (!empty($data['vehicle_id']) && !empty($data['date_start']) && !empty($data['date_end'])
                && in_array(($data['status'] ?? 'reserved'), ['reserved', 'ongoing'], true)) {
                $conflicts = $this->fleet->rental_conflicts($data['vehicle_id'], $data['date_start'], $data['date_end'], $id ?: null);
                if (!empty($conflicts)) {
                    $c     = $conflicts[0];
                    $label = '#' . $c['id'] . ($c['client_name'] ? ' - ' . $c['client_name'] : '');
                    set_alert('warning', _l('fleet_booking_conflict', [_d($c['date_start']), _d($c['date_end']), $label]));
                    redirect($id == '' ? admin_url('fleet_management/rentals/rental') : admin_url('fleet_management/rentals/rental/' . $id));
                }
            }

            if ($id == '') {
                if (!staff_can('create', 'fleet')) {
                    access_denied('fleet');
                }
                $id = $this->fleet->add_rental($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('fleet_rental')));
                }
                redirect(admin_url('fleet_management/rentals/rental/' . $id));
            }

            if (!staff_can('edit', 'fleet')) {
                access_denied('fleet');
            }
            $this->fleet->update_rental($id, $data);
            set_alert('success', _l('updated_successfully', _l('fleet_rental')));
            redirect(admin_url('fleet_management/rentals/rental/' . $id));
        }

        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $this->load->model('clients_model');
        $data['rental']            = $id == '' ? null : $this->fleet->get_rental($id);
        $data['vehicles']          = $this->fleet->get_vehicle();
        $data['drivers']           = $this->fleet->get_drivers();
        $data['clients']           = $this->clients_model->get();
        $data['preselect_vehicle'] = $this->input->get('vehicle_id');
        $data['inspections']       = ($id == '') ? [] : $this->fleet->get_inspections($id);
        $data['inspection_files']  = [];
        foreach ($data['inspections'] as $type => $insp) {
            $data['inspection_files'][$type] = $this->fleet->get_inspection_files($insp['id']);
        }
        $data['title']             = $data['rental'] ? _l('fleet_rental') . ' #' . $id : _l('fleet_add_rental');
        $this->load->view('fleet_management/rentals/rental', $data);
    }

    public function delete($id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        if ($this->fleet->delete_rental($id)) {
            set_alert('success', _l('deleted', _l('fleet_rental')));
        }

        redirect(admin_url('fleet_management/rentals'));
    }

    public function export()
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $headers = [
            '#', _l('fleet_vehicle'), _l('fleet_plate'), _l('client'), _l('fleet_date_start'),
            _l('fleet_date_end'), _l('fleet_days'), _l('fleet_daily_rate'), _l('fleet_total'),
            _l('fleet_with_driver'), _l('fleet_status'), _l('invoice'),
        ];

        $rows = [];
        foreach ($this->fleet->get_rental() as $r) {
            $rows[] = [
                $r['id'], $r['vehicle_name'], $r['vehicle_plate'], $r['client_name'],
                _d($r['date_start']), _d($r['date_end']), $r['days'], $r['daily_rate'], $r['total'],
                $r['with_driver'] ? _l('yes') : _l('no'),
                _l('fleet_status_' . $r['status']),
                !empty($r['invoice_id']) ? format_invoice_number($r['invoice_id']) : '',
            ];
        }

        fleet_export_csv('rentals', $headers, $rows);
    }

    public function create_invoice($id)
    {
        if (!staff_can('create', 'fleet') || !has_permission('invoices', '', 'create')) {
            access_denied('fleet');
        }

        $invoice_id = $this->fleet->create_invoice($id);

        if ($invoice_id) {
            set_alert('success', _l('fleet_invoice_created'));
            redirect(admin_url('invoices/list_invoices/' . $invoice_id));
        }

        set_alert('warning', _l('fleet_invoice_create_failed'));
        redirect(admin_url('fleet_management/rentals/rental/' . $id));
    }

    /* ---------------- Inspections (état des lieux) ---------------- */

    public function save_inspection()
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $data      = $this->input->post();
        $rental_id = (int) $data['rental_id'];

        $this->fleet->save_inspection($data);
        set_alert('success', _l('updated_successfully', _l('fleet_inspection')));
        redirect(admin_url('fleet_management/rentals/rental/' . $rental_id) . '#inspections');
    }

    public function inspection_upload($inspection_id)
    {
        if (!staff_can('edit', 'fleet')) {
            access_denied('fleet');
        }

        $inspection = $this->fleet->get_inspection($inspection_id);
        if (!$inspection) {
            show_404();
        }

        if (isset($_FILES['file']) && $_FILES['file']['name'] != '') {
            $path = FCPATH . 'uploads/fleet_management/inspections/' . $inspection_id . '/';
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
                $this->fleet->add_inspection_file([
                    'inspection_id' => $inspection_id,
                    'file_name'     => $uploaded['file_name'],
                    'original_name' => $uploaded['orig_name'],
                ]);
                set_alert('success', _l('fleet_photo_uploaded'));
            } else {
                set_alert('warning', strip_tags($this->upload->display_errors()));
            }
        }

        redirect(admin_url('fleet_management/rentals/rental/' . $inspection->rental_id) . '#inspections');
    }

    public function inspection_delete_file($file_id)
    {
        if (!staff_can('delete', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_inspection_file($file_id);
        if ($file) {
            $inspection = $this->fleet->get_inspection($file->inspection_id);
            $full       = FCPATH . 'uploads/fleet_management/inspections/' . $file->inspection_id . '/' . $file->file_name;
            if (is_file($full)) {
                @unlink($full);
            }
            $this->fleet->delete_inspection_file($file_id);
            set_alert('success', _l('deleted', _l('fleet_photo')));

            if ($inspection) {
                redirect(admin_url('fleet_management/rentals/rental/' . $inspection->rental_id) . '#inspections');
            }
        }

        redirect(admin_url('fleet_management/rentals'));
    }

    public function inspection_image($file_id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $file = $this->fleet->get_inspection_file($file_id);
        if (!$file) {
            show_404();
        }

        $full = FCPATH . 'uploads/fleet_management/inspections/' . $file->inspection_id . '/' . $file->file_name;
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

    /* ---------------- PDF documents ---------------- */

    public function contract_pdf($id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $rental = $this->fleet->get_rental($id);
        if (!$rental) {
            show_404();
        }

        $this->load->model('clients_model');
        $data = [
            'rental' => $rental,
            'client' => $this->clients_model->get($rental->clientid),
        ];

        $html = $this->load->view('fleet_management/rentals/contract_pdf', $data, true);
        $this->_output_pdf($html, 'contrat-location-' . $id, _l('fleet_contract') . ' #' . $id);
    }

    public function inspection_pdf($id)
    {
        if (!staff_can('view', 'fleet')) {
            access_denied('fleet');
        }

        $rental = $this->fleet->get_rental($id);
        if (!$rental) {
            show_404();
        }

        $this->load->model('clients_model');
        $inspections = $this->fleet->get_inspections($id);
        $files       = [];
        foreach ($inspections as $type => $insp) {
            $files[$type] = $this->fleet->get_inspection_files($insp['id']);
        }

        $data = [
            'rental'      => $rental,
            'client'      => $this->clients_model->get($rental->clientid),
            'inspections' => $inspections,
            'files'       => $files,
        ];

        $html = $this->load->view('fleet_management/rentals/inspection_pdf', $data, true);
        $this->_output_pdf($html, 'etat-des-lieux-' . $id, _l('fleet_inspection') . ' #' . $id);
    }

    /** Render HTML to a PDF with the shared company footer pinned to the bottom. */
    private function _output_pdf($html, $filename, $title)
    {
        if (!class_exists('TCPDF')) {
            echo $html; // graceful fallback if the PDF engine is unavailable
            return;
        }

        require_once __DIR__ . '/../libraries/Fleet_po_pdf.php';

        $pdf = new Fleet_po_pdf('P', 'mm', 'A4', true, 'UTF-8');
        $pdf->footerHtml = fleet_pdf_footer_html();
        $pdf->SetCreator(get_option('companyname'));
        $pdf->SetTitle($title);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFooterMargin(18);
        $pdf->SetMargins(12, 12, 12);
        $pdf->SetAutoPageBreak(true, 28);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output($filename . '.pdf', 'I');
    }
}
