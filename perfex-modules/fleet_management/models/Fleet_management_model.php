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

        return $this->db->insert_id();
    }

    public function update_vehicle($id, $data)
    {
        $data = $this->_clean_numeric($data, ['daily_rate', 'daily_rate_with_driver', 'purchase_price', 'odometer', 'year', 'seats']);
        $data = $this->_clean_dates($data, ['purchase_date']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_vehicles', $data);

        return $this->db->affected_rows() > 0;
    }

    public function delete_vehicle($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_vehicles');

        if ($this->db->affected_rows() > 0) {
            foreach (['fleet_maintenance', 'fleet_reminders', 'fleet_assignments', 'fleet_rentals'] as $table) {
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

        return true;
    }

    /* ----------------------------------------------------------------- *
     * Maintenance
     * ----------------------------------------------------------------- */

    public function get_maintenance($id = '', $vehicle_id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_maintenance')->row();
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

        return $this->db->insert_id();
    }

    public function update_maintenance($id, $data)
    {
        $data = $this->_clean_numeric($data, ['cost', 'odometer', 'next_service_odometer', 'supplier_id']);
        $data = $this->_clean_dates($data, ['service_date', 'next_service_date']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_maintenance', $data);

        return $this->db->affected_rows() > 0;
    }

    public function delete_maintenance($id)
    {
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

        return $this->db->insert_id();
    }

    public function update_reminder($id, $data)
    {
        $data = $this->_clean_numeric($data, ['cost', 'notify_days', 'supplier_id']);
        $data = $this->_clean_dates($data, ['due_date']);

        // Re-arm the notification when the due date is pushed back.
        $data['is_notified'] = 0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_reminders', $data);

        return $this->db->affected_rows() > 0;
    }

    public function delete_reminder($id)
    {
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
        }

        return $invoice_id;
    }

    /* ----------------------------------------------------------------- *
     * Suppliers (garages, insurers, fuel stations, partners...)
     * ----------------------------------------------------------------- */

    public function get_supplier($id = '', $type = '')
    {
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

        return $id;
    }

    public function update_fuel_log($id, $data)
    {
        $data['full_tank'] = isset($data['full_tank']) ? 1 : 0;
        $data              = $this->_prepare_fuel_data($data);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_fuel_logs', $data);

        return $this->db->affected_rows() > 0;
    }

    public function delete_fuel_log($id)
    {
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
