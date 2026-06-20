<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Fleet_management_model extends App_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /* ----------------------------------------------------------------- *
     * Vehicles
     * ----------------------------------------------------------------- */

    public function get_vehicle($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_vehicles')->row();
        }

        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'fleet_vehicles')->result_array();
    }

    public function add_vehicle($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();
        $data                 = $this->_clean_numeric($data, ['daily_rate', 'daily_rate_with_driver', 'purchase_price', 'odometer', 'year', 'seats']);
        $data                 = $this->_clean_dates($data, ['purchase_date']);

        $this->db->insert(db_prefix() . 'fleet_vehicles', $data);
        $id = $this->db->insert_id();

        if ($id) {
            $this->log_activity($id, 'vehicle', _l('fleet_log_vehicle_created'));
        }

        return $id;
    }

    public function update_vehicle($id, $data)
    {
        $data = $this->_clean_numeric($data, ['daily_rate', 'daily_rate_with_driver', 'purchase_price', 'odometer', 'year', 'seats']);
        $data = $this->_clean_dates($data, ['purchase_date']);

        $previous = $this->get_vehicle($id);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_vehicles', $data);

        // Track odometer changes explicitly in the history.
        if ($previous && isset($data['odometer']) && (int) $data['odometer'] !== (int) $previous->odometer) {
            $this->log_activity($id, 'odometer', _l('fleet_log_odometer', [(int) $previous->odometer, (int) $data['odometer']]));
        }

        return $this->db->affected_rows() > 0;
    }

    public function delete_vehicle($id)
    {
        // Remove the core expenses linked to this vehicle's costed records first.
        foreach (['fleet_maintenance', 'fleet_fuel_logs', 'fleet_reminders', 'fleet_parts'] as $table) {
            if (!$this->db->field_exists('expense_id', db_prefix() . $table)) {
                continue;
            }
            $this->load->model('expenses_model');
            $this->db->where('vehicle_id', $id);
            $this->db->where('expense_id IS NOT NULL', null, false);
            foreach ($this->db->get(db_prefix() . $table)->result_array() as $row) {
                $this->expenses_model->delete($row['expense_id']);
            }
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_vehicles');

        if ($this->db->affected_rows() > 0) {
            foreach (['fleet_maintenance', 'fleet_reminders', 'fleet_assignments', 'fleet_rentals', 'fleet_fuel_logs', 'fleet_parts', 'fleet_part_assignments', 'fleet_activity'] as $table) {
                $this->db->where('vehicle_id', $id);
                $this->db->delete(db_prefix() . $table);
            }

            return true;
        }

        return false;
    }

    /* ----------------------------------------------------------------- *
     * Drivers (staff holding the dedicated driver role)
     * ----------------------------------------------------------------- */

    public function get_drivers()
    {
        $role_id = get_option('fleet_driver_role_id');

        if ($role_id == '') {
            return [];
        }

        $this->db->select('staffid, CONCAT(firstname, " ", lastname) as full_name, email, phonenumber, active');
        $this->db->where('role', $role_id);
        $this->db->order_by('firstname', 'asc');

        return $this->db->get(db_prefix() . 'staff')->result_array();
    }

    public function is_driver($staff_id)
    {
        $role_id = get_option('fleet_driver_role_id');

        return $role_id != '' && total_rows(db_prefix() . 'staff', ['staffid' => $staff_id, 'role' => $role_id]) > 0;
    }

    /* ----------------------------------------------------------------- *
     * Driver assignments
     * ----------------------------------------------------------------- */

    public function get_assignments($vehicle_id)
    {
        $this->db->select('a.*, CONCAT(s.firstname, " ", s.lastname) as driver_name');
        $this->db->from(db_prefix() . 'fleet_assignments a');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = a.staff_id', 'left');
        $this->db->where('a.vehicle_id', $vehicle_id);
        $this->db->order_by('a.date_start', 'desc');

        return $this->db->get()->result_array();
    }

    public function assign_driver($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();
        $data                 = $this->_clean_dates($data, ['date_start', 'date_end']);

        $this->db->insert(db_prefix() . 'fleet_assignments', $data);
        $id = $this->db->insert_id();

        // Reflect the current driver on the vehicle for quick lookups.
        $this->db->where('id', $data['vehicle_id']);
        $this->db->update(db_prefix() . 'fleet_vehicles', ['current_driver_id' => $data['staff_id']]);

        $this->log_activity($data['vehicle_id'], 'assignment', _l('fleet_log_driver_assigned', get_staff_full_name($data['staff_id'])));

        return $id;
    }

    public function end_assignment($id)
    {
        $assignment = $this->db->get_where(db_prefix() . 'fleet_assignments', ['id' => $id])->row();

        if (!$assignment) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_assignments', [
            'status'   => 'ended',
            'date_end' => $assignment->date_end ?: date('Y-m-d'),
        ]);

        $this->db->where('id', $assignment->vehicle_id);
        $this->db->where('current_driver_id', $assignment->staff_id);
        $this->db->update(db_prefix() . 'fleet_vehicles', ['current_driver_id' => null]);

        $this->log_activity($assignment->vehicle_id, 'assignment', _l('fleet_log_assignment_ended', get_staff_full_name($assignment->staff_id)));

        return true;
    }

    /* ----------------------------------------------------------------- *
     * Maintenance
     * ----------------------------------------------------------------- */

    public function get_maintenance($id = '', $vehicle_id = '')
    {
        if (is_numeric($id)) {
            $this->db->select('m.*, v.name as vehicle_name, v.plate as vehicle_plate');
            $this->db->from(db_prefix() . 'fleet_maintenance m');
            $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = m.vehicle_id', 'left');
            $this->db->where('m.id', $id);

            return $this->db->get()->row();
        }

        $this->db->select('m.*, v.name as vehicle_name, v.plate as vehicle_plate');
        $this->db->from(db_prefix() . 'fleet_maintenance m');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = m.vehicle_id', 'left');

        if (is_numeric($vehicle_id)) {
            $this->db->where('m.vehicle_id', $vehicle_id);
        }

        $this->db->order_by('m.service_date', 'desc');

        return $this->db->get()->result_array();
    }

    public function add_maintenance($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();
        $data                 = $this->_clean_numeric($data, ['cost', 'odometer', 'next_service_odometer', 'supplier_id']);
        $data                 = $this->_clean_dates($data, ['service_date', 'next_service_date']);

        $this->db->insert(db_prefix() . 'fleet_maintenance', $data);
        $id = $this->db->insert_id();

        $type_label = isset($data['type']) ? _l('fleet_mtype_' . $data['type']) : '';

        if ($id && !empty($data['vehicle_id'])) {
            $desc = _l('fleet_log_maintenance', $type_label);
            if (!empty($data['parts'])) {
                $desc .= ' — ' . _l('fleet_parts') . ': ' . $data['parts'];
            }
            $this->log_activity($data['vehicle_id'], 'maintenance', $desc);
        }

        if ($id) {
            $this->_sync_record_expense(
                'fleet_maintenance',
                $id,
                $data['cost'] ?? 0,
                _l('fleet_maintenance') . ' - ' . $type_label . ' - ' . $this->_vehicle_label($data['vehicle_id'] ?? 0),
                $data['parts'] ?? ($data['description'] ?? ''),
                $data['service_date'] ?? null
            );
        }

        // A planned next service date automatically schedules a reminder.
        if ($id && !empty($data['vehicle_id']) && !empty($data['next_service_date'])) {
            $km = !empty($data['next_service_odometer']) ? ' (' . (int) $data['next_service_odometer'] . ' km)' : '';
            $this->add_reminder([
                'vehicle_id'  => $data['vehicle_id'],
                'type'        => 'service',
                'title'       => _l('fleet_next_service') . ' - ' . $type_label,
                'description' => _l('fleet_next_service') . $km,
                'due_date'    => _d($data['next_service_date']),
                'notify_days' => 7,
            ]);
        }

        return $id;
    }

    public function update_maintenance($id, $data)
    {
        $data = $this->_clean_numeric($data, ['cost', 'odometer', 'next_service_odometer', 'supplier_id']);
        $data = $this->_clean_dates($data, ['service_date', 'next_service_date']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_maintenance', $data);

        $record = $this->get_maintenance($id);
        if ($record) {
            $type_label = _l('fleet_mtype_' . $record->type);
            $this->_sync_record_expense(
                'fleet_maintenance',
                $id,
                $record->cost,
                _l('fleet_maintenance') . ' - ' . $type_label . ' - ' . $this->_vehicle_label($record->vehicle_id),
                $record->parts ?? $record->description,
                $record->service_date
            );
        }

        return true;
    }

    public function delete_maintenance($id)
    {
        $this->_delete_record_expense('fleet_maintenance', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_maintenance');

        return $this->db->affected_rows() > 0;
    }

    /* ----------------------------------------------------------------- *
     * Reminders (insurance, technical inspection, registration...)
     * ----------------------------------------------------------------- */

    public function get_reminders($id = '', $vehicle_id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_reminders')->row();
        }

        $this->db->select('r.*, v.name as vehicle_name, v.plate as vehicle_plate');
        $this->db->from(db_prefix() . 'fleet_reminders r');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = r.vehicle_id', 'left');

        if (is_numeric($vehicle_id)) {
            $this->db->where('r.vehicle_id', $vehicle_id);
        }

        $this->db->order_by('r.due_date', 'asc');

        return $this->db->get()->result_array();
    }

    public function add_reminder($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();
        $data                 = $this->_clean_numeric($data, ['cost', 'notify_days', 'supplier_id']);
        $data                 = $this->_clean_dates($data, ['due_date']);

        $this->db->insert(db_prefix() . 'fleet_reminders', $data);
        $id = $this->db->insert_id();

        if ($id && !empty($data['vehicle_id'])) {
            $this->log_activity($data['vehicle_id'], 'reminder', _l('fleet_log_reminder_added', $data['title'] ?? ''));
        }

        if ($id) {
            $type_label = isset($data['type']) ? _l('fleet_rtype_' . $data['type']) : '';
            $this->_sync_record_expense(
                'fleet_reminders',
                $id,
                $data['cost'] ?? 0,
                $type_label . ' - ' . $this->_vehicle_label($data['vehicle_id'] ?? 0),
                $data['title'] ?? '',
                $data['due_date'] ?? null
            );
        }

        return $id;
    }

    public function update_reminder($id, $data)
    {
        $data = $this->_clean_numeric($data, ['cost', 'notify_days', 'supplier_id']);
        $data = $this->_clean_dates($data, ['due_date']);

        // Re-arm the notification when the due date is pushed back.
        $data['is_notified'] = 0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_reminders', $data);

        $record = $this->get_reminders($id);
        if ($record) {
            $this->_sync_record_expense(
                'fleet_reminders',
                $id,
                $record->cost,
                _l('fleet_rtype_' . $record->type) . ' - ' . $this->_vehicle_label($record->vehicle_id),
                $record->title,
                $record->due_date
            );
        }

        return true;
    }

    public function delete_reminder($id)
    {
        $this->_delete_record_expense('fleet_reminders', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_reminders');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Cron entry point: notify staff for reminders whose notice window is open.
     */
    public function send_due_reminders()
    {
        $today = date('Y-m-d');

        $this->db->where('status', 'active');
        $this->db->where('is_notified', 0);
        $this->db->where('DATE_SUB(due_date, INTERVAL notify_days DAY) <=', $today);
        $reminders = $this->db->get(db_prefix() . 'fleet_reminders')->result_array();

        if (empty($reminders)) {
            return;
        }

        // Notify every staff member allowed to view the fleet.
        $staff = $this->db->get(db_prefix() . 'staff')->result_array();

        foreach ($reminders as $reminder) {
            $vehicle = $this->get_vehicle($reminder['vehicle_id']);
            $vehicle_label = $vehicle ? ($vehicle->name . ' (' . $vehicle->plate . ')') : ('#' . $reminder['vehicle_id']);

            foreach ($staff as $member) {
                if (!is_staff_member($member['staffid']) || !staff_can('view', 'fleet', $member['staffid'])) {
                    continue;
                }

                $notified = add_notification([
                    'description'     => 'fleet_reminder_due_notification',
                    'touserid'        => $member['staffid'],
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'additional_data' => serialize([$reminder['title'] . ' — ' . $vehicle_label, _dt($reminder['due_date'])]),
                    'link'            => 'fleet_management/reminders',
                ]);

                if ($notified) {
                    pusher_trigger_notification([$member['staffid']]);
                }
            }

            $this->db->where('id', $reminder['id']);
            $this->db->update(db_prefix() . 'fleet_reminders', ['is_notified' => 1]);
        }
    }

    /* ----------------------------------------------------------------- *
     * Rentals
     * ----------------------------------------------------------------- */

    public function get_rental($id = '')
    {
        if (is_numeric($id)) {
            $this->db->select('r.*, v.name as vehicle_name, v.plate as vehicle_plate, c.company as client_name, CONCAT(s.firstname, " ", s.lastname) as driver_name');
            $this->db->from(db_prefix() . 'fleet_rentals r');
            $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = r.vehicle_id', 'left');
            $this->db->join(db_prefix() . 'clients c', 'c.userid = r.clientid', 'left');
            $this->db->join(db_prefix() . 'staff s', 's.staffid = r.driver_id', 'left');
            $this->db->where('r.id', $id);

            return $this->db->get()->row();
        }

        $this->db->select('r.*, v.name as vehicle_name, v.plate as vehicle_plate, c.company as client_name');
        $this->db->from(db_prefix() . 'fleet_rentals r');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = r.vehicle_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = r.clientid', 'left');
        $this->db->order_by('r.date_start', 'desc');

        return $this->db->get()->result_array();
    }

    public function add_rental($data)
    {
        $data = $this->_prepare_rental_data($data);

        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();

        $this->db->insert(db_prefix() . 'fleet_rentals', $data);
        $id = $this->db->insert_id();

        if ($id && $data['status'] === 'ongoing') {
            $this->_set_vehicle_status($data['vehicle_id'], 'rented');
        }

        if ($id && !empty($data['vehicle_id'])) {
            $this->log_activity($data['vehicle_id'], 'rental', _l('fleet_log_rental_created', $id));
        }

        return $id;
    }

    public function update_rental($id, $data)
    {
        $data = $this->_prepare_rental_data($data);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_rentals', $data);

        if ($data['status'] === 'ongoing') {
            $this->_set_vehicle_status($data['vehicle_id'], 'rented');
        } elseif (in_array($data['status'], ['completed', 'cancelled'], true)) {
            $this->_set_vehicle_status($data['vehicle_id'], 'available');
        }

        return true;
    }

    public function delete_rental($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_rentals');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Create a draft Perfex invoice from a rental and link it back.
     *
     * @return int|false Invoice id on success, false otherwise.
     */
    public function create_invoice($rental_id)
    {
        $rental = $this->get_rental($rental_id);

        if (!$rental || !empty($rental->invoice_id)) {
            return false;
        }

        $this->load->model('invoices_model');
        $this->load->model('currencies_model');

        $base_currency = $this->currencies_model->get_base_currency();
        $rate          = (float) $rental->daily_rate;
        $qty           = (int) $rental->days;
        $line_total    = $rate * $qty;

        $description = _l('fleet_invoice_item_title', $rental->vehicle_name . ' (' . $rental->vehicle_plate . ')');
        $long        = _l('fleet_invoice_item_period', [_dt($rental->date_start), _dt($rental->date_end)]);
        if ($rental->with_driver) {
            $long .= ' — ' . _l('fleet_with_driver');
        }

        $invoice_data = [
            'clientid'         => $rental->clientid,
            'number'           => get_option('next_invoice_number'),
            'date'             => _d(date('Y-m-d')),
            'duedate'          => _d(date('Y-m-d', strtotime('+' . (int) get_option('fleet_invoice_due_days') . ' days'))),
            'currency'         => $base_currency->id,
            'subtotal'         => $line_total,
            'total'            => $line_total,
            'adjustment'       => 0,
            'discount_percent' => 0,
            'discount_total'   => 0,
            'discount_type'    => '',
            'terms'            => get_option('predefined_terms_invoice'),
            'clientnote'       => get_option('predefined_clientnote_invoice'),
            'show_quantity_as' => 1,
            'newitems'         => [
                1 => [
                    'description'      => $description,
                    'long_description' => $long,
                    'qty'              => $qty,
                    'unit'             => _l('fleet_unit_day'),
                    'rate'             => $rate,
                    'order'            => 1,
                    'taxname'          => [],
                ],
            ],
        ];

        $invoice_id = $this->invoices_model->add($invoice_data);

        if ($invoice_id) {
            $this->db->where('id', $rental_id);
            $this->db->update(db_prefix() . 'fleet_rentals', ['invoice_id' => $invoice_id]);

            $this->log_activity($rental->vehicle_id, 'invoice', _l('fleet_log_invoice_created', format_invoice_number($invoice_id)));
        }

        return $invoice_id;
    }

    /* ----------------------------------------------------------------- *
     * Catalog: categories, brands, models
     * ----------------------------------------------------------------- */

    public function get_categories()
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_categories')) {
            return [];
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'fleet_categories')->result_array();
    }

    public function add_category($name)
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }
        $this->db->insert(db_prefix() . 'fleet_categories', ['name' => $name]);

        return $this->db->insert_id();
    }

    public function delete_category($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_categories');

        return $this->db->affected_rows() > 0;
    }

    public function get_brands()
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_brands')) {
            return [];
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'fleet_brands')->result_array();
    }

    public function add_brand($name)
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }
        $this->db->insert(db_prefix() . 'fleet_brands', ['name' => $name]);

        return $this->db->insert_id();
    }

    public function delete_brand($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_brands');

        // Orphan models go away with their brand.
        $this->db->where('brand_id', $id);
        $this->db->delete(db_prefix() . 'fleet_models');

        return true;
    }

    public function get_models($brand_id = '')
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_models')) {
            return [];
        }
        $this->db->select('m.*, b.name as brand_name');
        $this->db->from(db_prefix() . 'fleet_models m');
        $this->db->join(db_prefix() . 'fleet_brands b', 'b.id = m.brand_id', 'left');

        if (is_numeric($brand_id)) {
            $this->db->where('m.brand_id', $brand_id);
        }

        $this->db->order_by('b.name', 'asc');
        $this->db->order_by('m.name', 'asc');

        return $this->db->get()->result_array();
    }

    public function add_model($brand_id, $name)
    {
        $name = trim($name);
        if ($name === '' || !is_numeric($brand_id)) {
            return false;
        }
        $this->db->insert(db_prefix() . 'fleet_models', ['brand_id' => $brand_id, 'name' => $name]);

        return $this->db->insert_id();
    }

    public function delete_model($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_models');

        return $this->db->affected_rows() > 0;
    }

    /* ----------------------------------------------------------------- *
     * Suppliers (garages, insurers, fuel stations, partners...)
     * ----------------------------------------------------------------- */

    public function get_supplier($id = '', $type = '')
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_suppliers')) {
            return is_numeric($id) ? null : [];
        }

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_suppliers')->row();
        }

        if ($type !== '') {
            $this->db->where('type', $type);
        }

        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'fleet_suppliers')->result_array();
    }

    public function add_supplier($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();
        $data['active']       = isset($data['active']) ? 1 : 0;

        $this->db->insert(db_prefix() . 'fleet_suppliers', $data);

        return $this->db->insert_id();
    }

    public function update_supplier($id, $data)
    {
        $data['active'] = isset($data['active']) ? 1 : 0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_suppliers', $data);

        return $this->db->affected_rows() > 0;
    }

    public function delete_supplier($id)
    {
        // Detach the supplier from any record that referenced it.
        foreach (['fleet_maintenance', 'fleet_reminders', 'fleet_fuel_logs'] as $table) {
            $this->db->where('supplier_id', $id);
            $this->db->update(db_prefix() . $table, ['supplier_id' => null]);
        }

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_suppliers');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Part orders placed with a supplier (for the supplier ledger).
     */
    public function get_supplier_orders($supplier_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_orders')) {
            return [];
        }

        $this->db->select('o.*, i.name as item_name');
        $this->db->from(db_prefix() . 'fleet_part_orders o');
        $this->db->join(db_prefix() . 'fleet_part_items i', 'i.id = o.item_id', 'left');
        $this->db->where('o.supplier_id', $supplier_id);
        $this->db->where('o.status !=', 'cancelled');
        $this->db->order_by('o.date_created', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Other costed records linked to a supplier (maintenance / fuel / reminders),
     * normalised into a single ledger list.
     */
    public function get_supplier_costs($supplier_id)
    {
        $ledger = [];

        $sources = [
            'fleet_maintenance' => ['cost', 'service_date', 'type', true],
            'fleet_fuel_logs'   => ['total_cost', 'date', null, false],
            'fleet_reminders'   => ['cost', 'due_date', 'title', false],
        ];

        foreach ($sources as $table => $meta) {
            if (!$this->db->table_exists(db_prefix() . $table) || !$this->db->field_exists('supplier_id', db_prefix() . $table)) {
                continue;
            }
            list($amountCol, $dateCol, $labelCol, $isMaintenanceType) = $meta;

            $this->db->select('t.*, v.name as vehicle_name, v.plate as vehicle_plate');
            $this->db->from(db_prefix() . $table . ' t');
            $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = t.vehicle_id', 'left');
            $this->db->where('t.supplier_id', $supplier_id);
            $this->db->where('t.' . $amountCol . ' >', 0);
            $this->db->order_by('t.' . $dateCol, 'desc');

            foreach ($this->db->get()->result_array() as $row) {
                if ($table === 'fleet_maintenance') {
                    $label = _l('fleet_maintenance') . ' · ' . _l('fleet_mtype_' . $row['type']);
                } elseif ($table === 'fleet_fuel_logs') {
                    $label = _l('fleet_fuel');
                } else {
                    $label = _l('fleet_rtype_' . $row['type']) . ' · ' . $row['title'];
                }

                $ledger[] = [
                    'table'        => $table,
                    'id'           => $row['id'],
                    'label'        => $label,
                    'vehicle'      => trim(($row['vehicle_name'] ?? '') . ' ' . ($row['vehicle_plate'] ? '(' . $row['vehicle_plate'] . ')' : '')),
                    'date'         => $row[$dateCol],
                    'amount'       => (float) $row[$amountCol],
                    'paid'         => (int) ($row['paid'] ?? 0),
                    'expense_id'   => $row['expense_id'] ?? null,
                ];
            }
        }

        return $ledger;
    }

    public function supplier_accounting($supplier_id)
    {
        $total = $paid = 0;

        foreach ($this->get_supplier_orders($supplier_id) as $o) {
            $total += (float) $o['total_price'];
            if (!empty($o['paid'])) {
                $paid += (float) $o['total_price'];
            }
        }
        foreach ($this->get_supplier_costs($supplier_id) as $c) {
            $total += $c['amount'];
            if (!empty($c['paid'])) {
                $paid += $c['amount'];
            }
        }

        return (object) ['total' => $total, 'paid' => $paid, 'unpaid' => $total - $paid];
    }

    /**
     * Toggle the paid flag of a costed record (supplier accounting).
     */
    public function mark_paid($table, $id, $paid)
    {
        $allowed = ['fleet_part_orders', 'fleet_maintenance', 'fleet_fuel_logs', 'fleet_reminders'];
        if (!in_array($table, $allowed, true) || !$this->db->field_exists('paid', db_prefix() . $table)) {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . $table, [
            'paid'      => $paid ? 1 : 0,
            'paid_date' => $paid ? date('Y-m-d') : null,
        ]);

        return true;
    }

    /* ----------------------------------------------------------------- *
     * Fuel logs
     * ----------------------------------------------------------------- */

    public function get_fuel_log($id = '', $vehicle_id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_fuel_logs')->row();
        }

        $this->db->select('f.*, v.name as vehicle_name, v.plate as vehicle_plate, sup.name as supplier_name, CONCAT(s.firstname, " ", s.lastname) as driver_name');
        $this->db->from(db_prefix() . 'fleet_fuel_logs f');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = f.vehicle_id', 'left');
        $this->db->join(db_prefix() . 'fleet_suppliers sup', 'sup.id = f.supplier_id', 'left');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = f.driver_id', 'left');

        if (is_numeric($vehicle_id)) {
            $this->db->where('f.vehicle_id', $vehicle_id);
        }

        $this->db->order_by('f.date', 'desc');

        return $this->db->get()->result_array();
    }

    public function add_fuel_log($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();
        $data['full_tank']    = isset($data['full_tank']) ? 1 : 0;
        $data                 = $this->_prepare_fuel_data($data);

        $this->db->insert(db_prefix() . 'fleet_fuel_logs', $data);
        $id = $this->db->insert_id();

        // Keep the vehicle odometer in sync with the latest fuel entry.
        if ($id && !empty($data['odometer'])) {
            $this->db->where('id', $data['vehicle_id']);
            $this->db->where('odometer <', $data['odometer']);
            $this->db->update(db_prefix() . 'fleet_vehicles', ['odometer' => $data['odometer']]);
        }

        if ($id && !empty($data['vehicle_id'])) {
            $liters = isset($data['liters']) ? (float) $data['liters'] : 0;
            $desc   = _l('fleet_log_fuel', $liters);
            if (!empty($data['odometer'])) {
                $desc .= ' — ' . (int) $data['odometer'] . ' km';
            }
            $this->log_activity($data['vehicle_id'], 'fuel', $desc);
        }

        if ($id) {
            $this->_sync_record_expense(
                'fleet_fuel_logs',
                $id,
                $data['total_cost'] ?? 0,
                _l('fleet_fuel') . ' - ' . $this->_vehicle_label($data['vehicle_id'] ?? 0),
                (isset($data['liters']) ? (float) $data['liters'] . ' L' : ''),
                $data['date'] ?? null
            );
        }

        return $id;
    }

    public function update_fuel_log($id, $data)
    {
        $data['full_tank'] = isset($data['full_tank']) ? 1 : 0;
        $data              = $this->_prepare_fuel_data($data);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_fuel_logs', $data);

        $record = $this->get_fuel_log($id);
        if ($record) {
            $this->_sync_record_expense(
                'fleet_fuel_logs',
                $id,
                $record->total_cost,
                _l('fleet_fuel') . ' - ' . $this->_vehicle_label($record->vehicle_id),
                (float) $record->liters . ' L',
                $record->date
            );
        }

        return true;
    }

    public function delete_fuel_log($id)
    {
        $this->_delete_record_expense('fleet_fuel_logs', $id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_fuel_logs');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Aggregate fuel figures (optionally for a single vehicle).
     */
    public function fuel_stats($vehicle_id = '')
    {
        if (is_numeric($vehicle_id)) {
            $this->db->where('vehicle_id', $vehicle_id);
        }
        $this->db->select('COUNT(*) as entries, COALESCE(SUM(liters),0) as total_liters, COALESCE(SUM(total_cost),0) as total_cost');

        return $this->db->get(db_prefix() . 'fleet_fuel_logs')->row();
    }

    /* ----------------------------------------------------------------- *
     * Activity log (per-vehicle history)
     * ----------------------------------------------------------------- */

    /**
     * Record an action performed on a vehicle. Safe to call before the table
     * exists (e.g. right after an upgrade) thanks to the guard.
     */
    public function log_activity($vehicle_id, $type, $description)
    {
        if (!$vehicle_id || !$this->db->table_exists(db_prefix() . 'fleet_activity')) {
            return;
        }

        $this->db->insert(db_prefix() . 'fleet_activity', [
            'vehicle_id'   => $vehicle_id,
            'staff_id'     => get_staff_user_id(),
            'type'         => $type,
            'description'  => $description,
            'date_created' => date('Y-m-d H:i:s'),
        ]);
    }

    public function get_activity($vehicle_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_activity')) {
            return [];
        }

        $this->db->select('a.*, CONCAT(s.firstname, " ", s.lastname) as staff_name');
        $this->db->from(db_prefix() . 'fleet_activity a');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = a.staff_id', 'left');
        $this->db->where('a.vehicle_id', $vehicle_id);
        $this->db->order_by('a.date_created', 'desc');
        $this->db->order_by('a.id', 'desc');

        return $this->db->get()->result_array();
    }

    /* ----------------------------------------------------------------- *
     * Maintenance attachments (photos with the date they were taken)
     * ----------------------------------------------------------------- */

    public function get_maintenance_files($maintenance_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_maintenance_files')) {
            return [];
        }

        $this->db->where('maintenance_id', $maintenance_id);
        $this->db->order_by('taken_date', 'desc');

        return $this->db->get(db_prefix() . 'fleet_maintenance_files')->result_array();
    }

    public function add_maintenance_file($data)
    {
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();
        $data                 = $this->_clean_dates($data, ['taken_date']);

        $this->db->insert(db_prefix() . 'fleet_maintenance_files', $data);

        return $this->db->insert_id();
    }

    public function get_maintenance_file($id)
    {
        $this->db->where('id', $id);

        return $this->db->get(db_prefix() . 'fleet_maintenance_files')->row();
    }

    public function delete_maintenance_file($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_maintenance_files');

        return $this->db->affected_rows() > 0;
    }

    /* ----------------------------------------------------------------- *
     * Parts catalog (items) with stock tracking
     * ----------------------------------------------------------------- */

    public function get_part_item($id = '')
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_items')) {
            return is_numeric($id) ? null : [];
        }

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_part_items')->row();
        }

        $this->db->order_by('name', 'asc');
        $items = $this->db->get(db_prefix() . 'fleet_part_items')->result_array();
        foreach ($items as &$item) {
            $item['stock'] = $this->item_stock($item['id']);
        }

        return $items;
    }

    public function add_part_item($data)
    {
        $data                 = $this->_clean_numeric($data, ['min_stock']);
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();

        $this->db->insert(db_prefix() . 'fleet_part_items', $data);

        return $this->db->insert_id();
    }

    public function update_part_item($id, $data)
    {
        $data = $this->_clean_numeric($data, ['min_stock']);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_part_items', $data);

        return true;
    }

    public function delete_part_item($id)
    {
        foreach ($this->db->get_where(db_prefix() . 'fleet_part_orders', ['item_id' => $id])->result_array() as $o) {
            if (!empty($o['expense_id'])) {
                $this->load->model('expenses_model');
                $this->expenses_model->delete($o['expense_id']);
            }
        }
        $this->db->where('item_id', $id)->delete(db_prefix() . 'fleet_part_orders');
        $this->db->where('item_id', $id)->delete(db_prefix() . 'fleet_part_assignments');
        $this->db->where('id', $id)->delete(db_prefix() . 'fleet_part_items');

        return true;
    }

    /**
     * Available stock for an item = received quantities - assigned quantities.
     */
    public function item_stock($item_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_orders')) {
            return 0;
        }

        $rec = $this->db->select('COALESCE(SUM(quantity),0) q')
            ->where('item_id', $item_id)->where('status', 'received')
            ->get(db_prefix() . 'fleet_part_orders')->row();
        $asg = $this->db->select('COALESCE(SUM(quantity),0) q')
            ->where('item_id', $item_id)
            ->get(db_prefix() . 'fleet_part_assignments')->row();

        return (int) ($rec->q ?? 0) - (int) ($asg->q ?? 0);
    }

    public function item_last_unit_price($item_id)
    {
        $row = $this->db->where('item_id', $item_id)->where('status', 'received')
            ->order_by('received_date', 'desc')->order_by('id', 'desc')->limit(1)
            ->get(db_prefix() . 'fleet_part_orders')->row();

        return $row ? (float) $row->unit_price : 0;
    }

    public function get_part_categories()
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_categories')) {
            return [];
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'fleet_part_categories')->result_array();
    }

    public function add_part_category($name)
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }
        $this->db->insert(db_prefix() . 'fleet_part_categories', ['name' => $name]);

        return $this->db->insert_id();
    }

    public function delete_part_category($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'fleet_part_categories');

        return true;
    }

    public function get_part_units()
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_units')) {
            return [];
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get(db_prefix() . 'fleet_part_units')->result_array();
    }

    public function add_part_unit($name)
    {
        $name = trim($name);
        if ($name === '') {
            return false;
        }
        $this->db->insert(db_prefix() . 'fleet_part_units', ['name' => $name]);

        return $this->db->insert_id();
    }

    public function delete_part_unit($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'fleet_part_units');

        return true;
    }

    public function part_items_stats()
    {
        $items = $this->get_part_item();
        $in_stock = 0;
        $low      = 0;
        foreach ($items as $it) {
            $in_stock += max(0, (int) $it['stock']);
            if ((int) $it['min_stock'] > 0 && (int) $it['stock'] <= (int) $it['min_stock']) {
                $low++;
            }
        }

        return (object) ['items' => count($items), 'in_stock' => $in_stock, 'low' => $low];
    }

    /* ----------------------------------------------------------------- *
     * Part orders (purchases from suppliers)
     * ----------------------------------------------------------------- */

    public function get_part_order($id = '')
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_orders')) {
            return is_numeric($id) ? null : [];
        }

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_part_orders')->row();
        }

        $this->db->select('o.*, i.name as item_name, i.reference as item_reference, s.name as supplier_name');
        $this->db->from(db_prefix() . 'fleet_part_orders o');
        $this->db->join(db_prefix() . 'fleet_part_items i', 'i.id = o.item_id', 'left');
        $this->db->join(db_prefix() . 'fleet_suppliers s', 's.id = o.supplier_id', 'left');
        $this->db->order_by('o.date_created', 'desc');

        return $this->db->get()->result_array();
    }

    public function add_part_order($data)
    {
        $data = $this->_prepare_order_data($data);
        $data['status']       = $data['status'] ?? 'ordered';
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();

        $this->db->insert(db_prefix() . 'fleet_part_orders', $data);
        $id = $this->db->insert_id();

        if ($id && $data['status'] === 'received') {
            $this->db->where('id', $id)->update(db_prefix() . 'fleet_part_orders', ['received_date' => date('Y-m-d')]);
            $this->_receive_sync($id);
        }

        return $id;
    }

    public function update_part_order($id, $data)
    {
        $data = $this->_prepare_order_data($data);
        unset($data['status']); // status changes go through receive/cancel

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_part_orders', $data);

        $this->_receive_sync($id);

        return true;
    }

    public function receive_part_order($id)
    {
        $order = $this->get_part_order($id);
        if (!$order || $order->status === 'received') {
            return false;
        }

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_part_orders', [
            'status'        => 'received',
            'received_date' => date('Y-m-d'),
        ]);

        $this->_receive_sync($id);

        return true;
    }

    public function cancel_part_order($id)
    {
        $this->_delete_record_expense('fleet_part_orders', $id);
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_part_orders', ['status' => 'cancelled', 'expense_id' => null]);

        return true;
    }

    public function delete_part_order($id)
    {
        $this->_delete_record_expense('fleet_part_orders', $id);
        $this->db->where('id', $id)->delete(db_prefix() . 'fleet_part_orders');

        return true;
    }

    /**
     * Post (or remove) the expense matching a received order.
     */
    private function _receive_sync($id)
    {
        $order = $this->get_part_order($id);
        if (!$order) {
            return;
        }

        $amount = ($order->status === 'received') ? $order->total_price : 0;
        $name   = _l('fleet_part') . ' - ' . $order->item_name . ($order->supplier_name ? ' - ' . $order->supplier_name : '');

        $extra = [
            'reference_no' => $order->invoice_no ?? '',
            'billable'     => !empty($order->billable) ? 1 : 0,
            'clientid'     => !empty($order->clientid) ? $order->clientid : null,
        ];

        $this->_sync_record_expense('fleet_part_orders', $id, $amount, $name, $order->item_reference, $order->received_date ?: $order->order_date, $extra);
    }

    private function _prepare_order_data($data)
    {
        unset($data['order_total']); // display-only field, not a column
        $data = $this->_clean_numeric($data, ['item_id', 'supplier_id', 'quantity', 'unit_price', 'clientid']);
        $data = $this->_clean_dates($data, ['order_date']);

        $qty = max(1, (int) ($data['quantity'] ?? 1));
        $data['quantity']    = $qty;
        $data['total_price'] = round((float) ($data['unit_price'] ?? 0) * $qty, 2);
        $data['billable']    = isset($data['billable']) && $data['billable'] ? 1 : 0;

        if (empty($data['supplier_id'])) {
            $data['supplier_id'] = null;
        }
        if (empty($data['clientid'])) {
            $data['clientid'] = null;
        }

        return $data;
    }

    /* ----------------------------------------------------------------- *
     * Part assignments (consume stock; attach to a vehicle or anything else)
     * ----------------------------------------------------------------- */

    public function get_part_assignment($id = '', $vehicle_id = '')
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_assignments')) {
            return is_numeric($id) ? null : [];
        }

        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_part_assignments')->row();
        }

        $this->db->select('a.*, i.name as item_name, i.reference as item_reference, v.name as vehicle_name, v.plate as vehicle_plate');
        $this->db->from(db_prefix() . 'fleet_part_assignments a');
        $this->db->join(db_prefix() . 'fleet_part_items i', 'i.id = a.item_id', 'left');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = a.vehicle_id', 'left');

        if (is_numeric($vehicle_id)) {
            $this->db->where('a.vehicle_id', $vehicle_id);
        }

        $this->db->order_by('a.assigned_date', 'desc');
        $this->db->order_by('a.id', 'desc');

        return $this->db->get()->result_array();
    }

    public function add_part_assignment($data)
    {
        $data = $this->_clean_numeric($data, ['item_id', 'vehicle_id', 'maintenance_id', 'quantity']);
        $data = $this->_clean_dates($data, ['assigned_date']);

        $qty               = max(1, (int) ($data['quantity'] ?? 1));
        $unit              = $this->item_last_unit_price($data['item_id']);
        $data['quantity']  = $qty;
        $data['unit_cost'] = $unit;
        $data['total_cost'] = round($unit * $qty, 2);

        foreach (['vehicle_id', 'maintenance_id'] as $fk) {
            if (empty($data[$fk])) {
                $data[$fk] = null;
            }
        }

        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();

        $this->db->insert(db_prefix() . 'fleet_part_assignments', $data);
        $id = $this->db->insert_id();

        if ($id && !empty($data['vehicle_id'])) {
            $item = $this->get_part_item($data['item_id']);
            $this->log_activity($data['vehicle_id'], 'part', _l('fleet_log_part_assigned', [($item ? $item->name : ''), $qty]));
        }

        return $id;
    }

    public function delete_part_assignment($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'fleet_part_assignments');

        return $this->db->affected_rows() > 0;
    }

    /* ----------------------------------------------------------------- *
     * Dashboard helpers
     * ----------------------------------------------------------------- */

    public function vehicles_count_by_status()
    {
        $this->db->select('status, COUNT(*) as total');
        $this->db->group_by('status');
        $rows = $this->db->get(db_prefix() . 'fleet_vehicles')->result_array();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['total'];
        }

        return $counts;
    }

    public function get_recent_vehicles($limit = 10)
    {
        $this->db->order_by('date_created', 'desc');
        $this->db->order_by('id', 'desc');
        $this->db->limit($limit);

        return $this->db->get(db_prefix() . 'fleet_vehicles')->result_array();
    }

    /**
     * Cost breakdown and KPIs per vehicle for the dashboard.
     */
    public function dashboard($start = null, $end = null, $occupancy_days = 30)
    {
        $vehicles = $this->get_vehicle();

        $maint = $this->_sum_by_vehicle('fleet_maintenance', 'cost', 'service_date', $start, $end);
        $fuel  = $this->_sum_by_vehicle('fleet_fuel_logs', 'total_cost', 'date', $start, $end);
        $rem   = $this->_sum_by_vehicle('fleet_reminders', 'cost', 'due_date', $start, $end);
        $parts = $this->_sum_by_vehicle('fleet_part_assignments', 'total_cost', 'assigned_date', $start, $end);
        foreach ($this->_sum_by_vehicle('fleet_parts', 'total_price', 'purchase_date', $start, $end) as $vid => $amt) {
            $parts[$vid] = ($parts[$vid] ?? 0) + $amt;
        }

        list($occ, $window_days) = $this->_occupancy_by_vehicle($occupancy_days);

        $rows   = [];
        $totals = ['maintenance' => 0, 'fuel' => 0, 'parts' => 0, 'reminders' => 0, 'total' => 0];

        foreach ($vehicles as $v) {
            $id  = $v['id'];
            $m   = $maint[$id] ?? 0;
            $f   = $fuel[$id] ?? 0;
            $p   = $parts[$id] ?? 0;
            $r   = $rem[$id] ?? 0;
            $t   = $m + $f + $p + $r;
            $odo = (int) $v['odometer'];

            $rows[] = [
                'vehicle'     => $v,
                'maintenance' => $m,
                'fuel'        => $f,
                'parts'       => $p,
                'reminders'   => $r,
                'total'       => $t,
                'cost_per_km' => $odo > 0 ? $t / $odo : 0,
                'occupancy'   => $occ[$id] ?? 0,
            ];

            $totals['maintenance'] += $m;
            $totals['fuel']        += $f;
            $totals['parts']       += $p;
            $totals['reminders']   += $r;
            $totals['total']       += $t;
        }

        // Highlights: most expensive, most used and most fuel-hungry vehicle.
        $top_cost = $top_used = $top_fuel = null;
        foreach ($rows as $row) {
            if ($top_cost === null || $row['total'] > $top_cost['total']) {
                $top_cost = $row;
            }
            if ($row['occupancy'] > 0 && ($top_used === null || $row['occupancy'] > $top_used['occupancy'])) {
                $top_used = $row;
            }
            if ($row['fuel'] > 0 && ($top_fuel === null || $row['fuel'] > $top_fuel['fuel'])) {
                $top_fuel = $row;
            }
        }
        if ($top_cost && $top_cost['total'] == 0) {
            $top_cost = null;
        }

        $fleet_occupancy = 0;
        if (!empty($rows)) {
            $fleet_occupancy = round(array_sum(array_column($rows, 'occupancy')) / count($rows));
        }

        return [
            'rows'            => $rows,
            'totals'          => $totals,
            'status_counts'   => $this->vehicles_count_by_status(),
            'fleet_occupancy' => $fleet_occupancy,
            'occupancy_days'  => $window_days,
            'highlights'      => ['top_cost' => $top_cost, 'top_used' => $top_used, 'top_fuel' => $top_fuel],
        ];
    }

    /**
     * Monthly expense series (last N months) split by cost type, for the chart.
     */
    public function monthly_expense_series($months = 12)
    {
        $labels = [];
        $index  = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $key            = date('Y-m', strtotime("first day of -$i month"));
            $labels[]       = $key;
            $index[$key]    = count($labels) - 1;
        }
        $since = date('Y-m-01', strtotime('first day of -' . ($months - 1) . ' month'));

        $zero = array_fill(0, count($labels), 0);
        $series = [
            'maintenance' => $zero,
            'fuel'        => $zero,
            'parts'       => $zero,
            'reminders'   => $zero,
        ];

        $sources = [
            'maintenance' => ['fleet_maintenance', 'cost', 'service_date'],
            'fuel'        => ['fleet_fuel_logs', 'total_cost', 'date'],
            'parts'       => ['fleet_part_assignments', 'total_cost', 'assigned_date'],
            'reminders'   => ['fleet_reminders', 'cost', 'due_date'],
        ];

        foreach ($sources as $key => $src) {
            list($table, $col, $dateCol) = $src;
            if (!$this->db->table_exists(db_prefix() . $table)) {
                continue;
            }
            $this->db->select("DATE_FORMAT($dateCol, '%Y-%m') as ym, COALESCE(SUM($col), 0) as total");
            $this->db->where("$dateCol >=", $since);
            $this->db->where("$dateCol IS NOT NULL", null, false);
            $this->db->group_by('ym');
            foreach ($this->db->get(db_prefix() . $table)->result_array() as $row) {
                if (isset($index[$row['ym']])) {
                    $series[$key][$index[$row['ym']]] = (float) $row['total'];
                }
            }
        }

        $labels = array_map(function ($ym) {
            return _d($ym . '-01');
        }, $labels);

        return ['labels' => $labels] + $series;
    }

    private function _sum_by_vehicle($table, $column, $date_col = null, $start = null, $end = null)
    {
        if (!$this->db->table_exists(db_prefix() . $table)) {
            return [];
        }

        $this->db->select('vehicle_id, COALESCE(SUM(' . $column . '), 0) as total');
        if ($date_col && $start && $end) {
            $this->db->where("$date_col >=", $start);
            $this->db->where("$date_col <=", $end);
        }
        $this->db->group_by('vehicle_id');

        $map = [];
        foreach ($this->db->get(db_prefix() . $table)->result_array() as $row) {
            $map[$row['vehicle_id']] = (float) $row['total'];
        }

        return $map;
    }

    /**
     * Percentage each vehicle was booked over a window. Defaults to the last
     * 30 days; uses the provided [start, end] range when given.
     *
     * @return array [occupancy_map, window_days]
     */
    private function _occupancy_by_vehicle($days = 30)
    {
        $days = (int) $days;
        if (!$this->db->table_exists(db_prefix() . 'fleet_rentals') || $days < 1) {
            return [[], max($days, 0)];
        }

        $endTs   = strtotime(date('Y-m-d'));
        $startTs = $endTs - ($days - 1) * 86400;

        $this->db->where_in('status', ['reserved', 'ongoing', 'completed']);
        $rentals = $this->db->get(db_prefix() . 'fleet_rentals')->result_array();

        $used = [];
        foreach ($rentals as $r) {
            $rs = strtotime($r['date_start']);
            $re = strtotime($r['date_end']);
            if (!$rs || !$re || $re < $startTs || $rs > $endTs) {
                continue;
            }
            $os   = max($rs, $startTs);
            $oe   = min($re, $endTs);
            $span = (int) floor(($oe - $os) / 86400) + 1;
            if ($span < 0) {
                $span = 0;
            }
            $used[$r['vehicle_id']] = ($used[$r['vehicle_id']] ?? 0) + $span;
        }

        $occ = [];
        foreach ($used as $vid => $d) {
            $occ[$vid] = min(100, (int) round($d / $days * 100));
        }

        return [$occ, $days];
    }

    /* ----------------------------------------------------------------- *
     * Internals
     * ----------------------------------------------------------------- */

    private function _prepare_rental_data($data)
    {
        $data = $this->_clean_numeric($data, ['vehicle_id', 'clientid', 'driver_id', 'daily_rate', 'odometer_start', 'odometer_end']);
        $data = $this->_clean_dates($data, ['date_start', 'date_end']);

        $data['with_driver'] = isset($data['with_driver']) && $data['with_driver'] ? 1 : 0;

        if (empty($data['driver_id'])) {
            $data['driver_id'] = null;
        }

        if (!empty($data['date_start']) && !empty($data['date_end'])) {
            $start = strtotime($data['date_start']);
            $end   = strtotime($data['date_end']);
            $days  = (int) floor(($end - $start) / 86400) + 1;
            $data['days'] = $days > 0 ? $days : 1;
        }

        $days  = isset($data['days']) ? (int) $data['days'] : 1;
        $data['total'] = (float) ($data['daily_rate'] ?? 0) * $days;

        return $data;
    }

    private function _prepare_fuel_data($data)
    {
        $data = $this->_clean_numeric($data, ['vehicle_id', 'driver_id', 'supplier_id', 'odometer', 'liters', 'price_per_liter', 'total_cost']);
        $data = $this->_clean_dates($data, ['date']);

        // Derive the total cost when only the unit price was provided.
        if ((empty($data['total_cost']) || $data['total_cost'] === null)
            && !empty($data['price_per_liter']) && !empty($data['liters'])) {
            $data['total_cost'] = round((float) $data['price_per_liter'] * (float) $data['liters'], 2);
        }

        return $data;
    }

    private function _set_vehicle_status($vehicle_id, $status)
    {
        $this->db->where('id', $vehicle_id);
        $this->db->update(db_prefix() . 'fleet_vehicles', ['status' => $status]);
    }

    private function _vehicle_label($vehicle_id)
    {
        $vehicle = $vehicle_id ? $this->get_vehicle($vehicle_id) : null;

        return $vehicle ? ($vehicle->name . ' (' . $vehicle->plate . ')') : ('#' . (int) $vehicle_id);
    }

    /**
     * Create, update or remove the Perfex core expense linked to a fleet record
     * (maintenance / fuel / reminder), keeping costs in sync with the Expenses
     * module. The link is stored in the record's `expense_id` column.
     */
    private function _sync_record_expense($table, $record_id, $amount, $name, $note, $sql_date, $extra = [])
    {
        if (!$this->db->field_exists('expense_id', db_prefix() . $table)) {
            return;
        }

        $category = get_option('fleet_expense_category_id');
        $record   = $this->db->get_where(db_prefix() . $table, ['id' => $record_id])->row();
        $existing = ($record && !empty($record->expense_id)) ? $record->expense_id : null;
        $amount   = (float) $amount;

        $this->load->model('expenses_model');

        // No positive amount (or no category configured): drop any linked expense.
        if ($amount <= 0 || !$category) {
            if ($existing) {
                $this->expenses_model->delete($existing);
                $this->db->where('id', $record_id);
                $this->db->update(db_prefix() . $table, ['expense_id' => null]);
            }

            return;
        }

        $payload = array_merge([
            'category'     => $category,
            'amount'       => $amount,
            'expense_name' => $name,
            'note'         => $note,
            'date'         => _d($sql_date ?: date('Y-m-d')),
            'currency'     => get_base_currency()->id,
        ], array_filter($extra, function ($v) {
            return $v !== null && $v !== '';
        }));

        if ($existing) {
            $this->expenses_model->update($payload, $existing);
        } else {
            $expense_id = $this->expenses_model->add($payload);
            if ($expense_id) {
                $this->db->where('id', $record_id);
                $this->db->update(db_prefix() . $table, ['expense_id' => $expense_id]);
            }
        }
    }

    private function _delete_record_expense($table, $record_id)
    {
        if (!$this->db->field_exists('expense_id', db_prefix() . $table)) {
            return;
        }

        $record = $this->db->get_where(db_prefix() . $table, ['id' => $record_id])->row();
        if ($record && !empty($record->expense_id)) {
            $this->load->model('expenses_model');
            $this->expenses_model->delete($record->expense_id);
        }
    }

    private function _clean_numeric($data, $fields)
    {
        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field] === '') {
                $data[$field] = null;
            }
        }

        return $data;
    }

    private function _clean_dates($data, $fields)
    {
        foreach ($fields as $field) {
            if (!isset($data[$field])) {
                continue;
            }
            $data[$field] = $data[$field] === '' ? null : to_sql_date($data[$field]);
        }

        return $data;
    }
}
