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

    /**
     * Full driver record: the staff member merged with the editable fleet
     * profile fields (license, personal details, emergency contact...).
     */
    public function get_driver($staff_id)
    {
        $staff = $this->db->get_where(db_prefix() . 'staff', ['staffid' => $staff_id])->row();

        if (!$staff) {
            return null;
        }

        $profile = $this->db->get_where(db_prefix() . 'fleet_driver_profiles', ['staff_id' => $staff_id])->row();

        $staff->full_name = trim($staff->firstname . ' ' . $staff->lastname);

        // Expose every profile column on the staff object (null when no profile yet).
        $fields = [
            'date_of_birth', 'national_id', 'phone', 'address', 'license_number',
            'license_category', 'license_issue_date', 'license_expiry', 'hire_date',
            'blood_type', 'emergency_contact', 'emergency_phone', 'profile_notes',
        ];
        foreach ($fields as $f) {
            $col           = $f === 'profile_notes' ? 'notes' : $f;
            $staff->{$f}   = $profile ? $profile->{$col} : null;
        }

        return $staff;
    }

    public function save_driver_profile($staff_id, $data)
    {
        $data = $this->_clean_dates($data, ['date_of_birth', 'license_issue_date', 'license_expiry', 'hire_date']);

        $exists = $this->db->get_where(db_prefix() . 'fleet_driver_profiles', ['staff_id' => $staff_id])->row();

        if ($exists) {
            // Re-arm the license notification whenever the expiry date is changed.
            if (($exists->license_expiry ?? null) != ($data['license_expiry'] ?? null)) {
                $data['license_notified'] = 0;
            }
            $this->db->where('staff_id', $staff_id);
            $this->db->update(db_prefix() . 'fleet_driver_profiles', $data);
        } else {
            $data['staff_id']     = $staff_id;
            $data['created_by']   = get_staff_user_id();
            $data['date_created'] = date('Y-m-d H:i:s');
            $this->db->insert(db_prefix() . 'fleet_driver_profiles', $data);
        }

        return true;
    }

    /** Vehicles this driver has been assigned to. */
    public function get_driver_assignments($staff_id)
    {
        $this->db->select('a.*, v.name as vehicle_name, v.plate as vehicle_plate');
        $this->db->from(db_prefix() . 'fleet_assignments a');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = a.vehicle_id', 'left');
        $this->db->where('a.staff_id', $staff_id);
        $this->db->order_by('a.date_start', 'desc');

        return $this->db->get()->result_array();
    }

    /** Rentals operated by this driver (with-driver rentals). */
    public function get_driver_rentals($staff_id)
    {
        $this->db->select('r.*, v.name as vehicle_name, v.plate as vehicle_plate, c.company as client_name');
        $this->db->from(db_prefix() . 'fleet_rentals r');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = r.vehicle_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = r.clientid', 'left');
        $this->db->where('r.driver_id', $staff_id);
        $this->db->order_by('r.date_start', 'desc');

        return $this->db->get()->result_array();
    }

    /** Fuel logs recorded against this driver. */
    public function get_driver_fuel($staff_id)
    {
        $this->db->select('f.*, v.name as vehicle_name, v.plate as vehicle_plate');
        $this->db->from(db_prefix() . 'fleet_fuel_logs f');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = f.vehicle_id', 'left');
        $this->db->where('f.driver_id', $staff_id);
        $this->db->order_by('f.date', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Active drivers whose license expires within the notice window (or is
     * already expired). Used by the Reminders page and the cron notifier.
     */
    public function get_expiring_licenses($within_days = null)
    {
        $role_id = get_option('fleet_driver_role_id');
        if ($role_id == '') {
            return [];
        }

        if ($within_days === null) {
            $within_days = (int) (get_option('fleet_license_notify_days') ?: 30);
        }

        $this->db->select('p.*, CONCAT(s.firstname, " ", s.lastname) as full_name, s.staffid');
        $this->db->from(db_prefix() . 'fleet_driver_profiles p');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = p.staff_id');
        $this->db->where('s.role', $role_id);
        $this->db->where('s.active', 1);
        $this->db->where('p.license_expiry IS NOT NULL');
        $this->db->where('p.license_expiry !=', '0000-00-00');
        $this->db->where('DATE_SUB(p.license_expiry, INTERVAL ' . (int) $within_days . ' DAY) <=', date('Y-m-d'));
        $this->db->order_by('p.license_expiry', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Cron entry point: notify fleet staff when a driver license is within its
     * renewal window. Each driver is notified once until the expiry changes.
     */
    public function send_due_license_reminders()
    {
        $drivers = $this->get_expiring_licenses();

        if (empty($drivers)) {
            return;
        }

        $staff   = $this->db->get(db_prefix() . 'staff')->result_array();
        $mail_to = $this->notification_emails();

        foreach ($drivers as $driver) {
            if (!empty($driver['license_notified'])) {
                continue; // already notified for this expiry date
            }

            foreach ($staff as $member) {
                if (!is_staff_member($member['staffid']) || !staff_can('view', 'fleet', $member['staffid'])) {
                    continue;
                }

                $notified = add_notification([
                    'description'     => 'fleet_license_due_notification',
                    'touserid'        => $member['staffid'],
                    'fromcompany'     => 1,
                    'fromuserid'      => 0,
                    'additional_data' => serialize([$driver['full_name'], _d($driver['license_expiry'])]),
                    'link'            => 'fleet_management/drivers/profile/' . $driver['staff_id'],
                ]);

                if ($notified) {
                    pusher_trigger_notification([$member['staffid']]);
                }
            }

            // E-mail notification (when enabled in the settings).
            $subject = _l('fleet_license_reminders') . ' — ' . $driver['full_name'];
            $body    = '<p>' . sprintf(_l('fleet_license_due_notification'), html_escape($driver['full_name']), _d($driver['license_expiry'])) . '</p>'
                . '<p><a href="' . admin_url('fleet_management/drivers/profile/' . $driver['staff_id']) . '">' . _l('fleet_driver_profile') . '</a></p>';
            fleet_send_email($mail_to, $subject, $body);

            $this->db->where('staff_id', $driver['staff_id']);
            $this->db->update(db_prefix() . 'fleet_driver_profiles', ['license_notified' => 1]);
        }
    }

    /* ----------------------------------------------------------------- *
     * Driver accidents / incidents
     * ----------------------------------------------------------------- */

    public function get_driver_accidents($staff_id)
    {
        $this->db->select('ac.*, v.name as vehicle_name, v.plate as vehicle_plate');
        $this->db->from(db_prefix() . 'fleet_driver_accidents ac');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = ac.vehicle_id', 'left');
        $this->db->where('ac.staff_id', $staff_id);
        $this->db->order_by('ac.accident_date', 'desc');

        return $this->db->get()->result_array();
    }

    public function get_accident($id)
    {
        return $this->db->get_where(db_prefix() . 'fleet_driver_accidents', ['id' => $id])->row();
    }

    public function add_accident($data)
    {
        $data = $this->_clean_dates($data, ['accident_date']);
        $data = $this->_clean_numeric($data, ['vehicle_id', 'cost']);

        $data['at_fault']     = !empty($data['at_fault']) ? 1 : 0;
        $data['created_by']   = get_staff_user_id();
        $data['date_created'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'fleet_driver_accidents', $data);

        return $this->db->insert_id();
    }

    public function update_accident($id, $data)
    {
        $data = $this->_clean_dates($data, ['accident_date']);
        $data = $this->_clean_numeric($data, ['vehicle_id', 'cost']);

        $data['at_fault'] = !empty($data['at_fault']) ? 1 : 0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_driver_accidents', $data);

        return true;
    }

    public function delete_accident($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_driver_accidents');

        return true;
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
        $staff       = $this->db->get(db_prefix() . 'staff')->result_array();
        $mail_to     = $this->notification_emails();

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

            // E-mail notification (when enabled in the settings).
            $subject = _l('fleet_reminders') . ' — ' . $reminder['title'] . ' — ' . $vehicle_label;
            $body    = '<p><strong>' . html_escape($reminder['title']) . '</strong></p>'
                . '<p>' . _l('fleet_vehicle') . ': ' . html_escape($vehicle_label) . '</p>'
                . '<p>' . _l('fleet_due_date') . ': ' . _d($reminder['due_date']) . '</p>'
                . '<p><a href="' . admin_url('fleet_management/reminders') . '">' . _l('fleet_reminders') . '</a></p>';
            fleet_send_email($mail_to, $subject, $body);

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
     * Active bookings (reserved / ongoing) that overlap the given date range for
     * a vehicle. Used to prevent double-booking. Returns matching rows so the
     * caller can show which rental conflicts.
     */
    public function rental_conflicts($vehicle_id, $date_start, $date_end, $exclude_id = null)
    {
        if (empty($vehicle_id) || empty($date_start) || empty($date_end)) {
            return [];
        }

        $start = to_sql_date($date_start);
        $end   = to_sql_date($date_end);

        $this->db->select('r.*, c.company as client_name');
        $this->db->from(db_prefix() . 'fleet_rentals r');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = r.clientid', 'left');
        $this->db->where('r.vehicle_id', $vehicle_id);
        $this->db->where_in('r.status', ['reserved', 'ongoing']);
        // Overlap: existing.start <= new.end AND existing.end >= new.start
        $this->db->where('r.date_start <=', $end);
        $this->db->where('r.date_end >=', $start);

        if ($exclude_id) {
            $this->db->where('r.id !=', $exclude_id);
        }

        return $this->db->get()->result_array();
    }

    /**
     * Rentals overlapping a calendar window, for the planning/Gantt view.
     */
    public function get_rentals_in_range($range_start, $range_end)
    {
        $this->db->select('r.*, c.company as client_name');
        $this->db->from(db_prefix() . 'fleet_rentals r');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = r.clientid', 'left');
        $this->db->where('r.status !=', 'cancelled');
        $this->db->where('r.date_start <=', $range_end);
        $this->db->where('r.date_end >=', $range_start);
        $this->db->order_by('r.date_start', 'asc');

        return $this->db->get()->result_array();
    }

    /**
     * Recipient e-mails for fleet notifications: every staff member allowed to
     * view the fleet plus any extra addresses configured in the settings.
     */
    public function notification_emails()
    {
        $emails = [];

        foreach ($this->db->get(db_prefix() . 'staff')->result_array() as $member) {
            if (is_staff_member($member['staffid'])
                && !empty($member['email'])
                && staff_can('view', 'fleet', $member['staffid'])) {
                $emails[] = $member['email'];
            }
        }

        $extra = get_option('fleet_notification_emails');
        if ($extra) {
            foreach (preg_split('/[,;\s]+/', $extra) as $e) {
                $e = trim($e);
                if ($e !== '') {
                    $emails[] = $e;
                }
            }
        }

        return array_values(array_unique($emails));
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
    public function get_supplier_orders($supplier_id, $start = null, $end = null)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_orders')) {
            return [];
        }

        $this->db->select('o.*');
        $this->db->from(db_prefix() . 'fleet_part_orders o');
        $this->db->where('o.supplier_id', $supplier_id);
        $this->db->where('o.status !=', 'cancelled');
        if ($start && $end) {
            $this->db->where('o.order_date >=', $start);
            $this->db->where('o.order_date <=', $end);
        }
        $this->db->order_by('o.date_created', 'desc');

        $orders = $this->db->get()->result_array();
        foreach ($orders as &$o) {
            $names               = array_column($this->get_order_items($o['id']), 'item_name');
            $o['items_summary']  = implode(', ', array_slice($names, 0, 3)) . (count($names) > 3 ? '…' : '');
            $o['paid_amount']    = $this->record_paid('fleet_part_orders', $o['id']);
            $o['remaining']      = max(0, (float) $o['total_price'] - $o['paid_amount']);
        }

        return $orders;
    }

    /**
     * Other costed records linked to a supplier (maintenance / fuel / reminders),
     * normalised into a single ledger list.
     */
    public function get_supplier_costs($supplier_id, $start = null, $end = null)
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
            if ($start && $end) {
                $this->db->where('t.' . $dateCol . ' >=', $start);
                $this->db->where('t.' . $dateCol . ' <=', $end);
            }
            $this->db->order_by('t.' . $dateCol, 'desc');

            foreach ($this->db->get()->result_array() as $row) {
                if ($table === 'fleet_maintenance') {
                    $label = _l('fleet_maintenance') . ' · ' . _l('fleet_mtype_' . $row['type']);
                } elseif ($table === 'fleet_fuel_logs') {
                    $label = _l('fleet_fuel');
                } else {
                    $label = _l('fleet_rtype_' . $row['type']) . ' · ' . $row['title'];
                }

                $amount   = (float) $row[$amountCol];
                $paidAmt  = $this->record_paid($table, $row['id']);
                $ledger[] = [
                    'table'        => $table,
                    'id'           => $row['id'],
                    'label'        => $label,
                    'vehicle'      => trim(($row['vehicle_name'] ?? '') . ' ' . ($row['vehicle_plate'] ? '(' . $row['vehicle_plate'] . ')' : '')),
                    'date'         => $row[$dateCol],
                    'amount'       => $amount,
                    'paid_amount'  => $paidAmt,
                    'remaining'    => max(0, $amount - $paidAmt),
                    'expense_id'   => $row['expense_id'] ?? null,
                ];
            }
        }

        return $ledger;
    }

    public function supplier_accounting($supplier_id, $start = null, $end = null)
    {
        $total = $paid = 0;

        foreach ($this->get_supplier_orders($supplier_id, $start, $end) as $o) {
            $total += (float) $o['total_price'];
            $paid  += min((float) $o['total_price'], (float) $o['paid_amount']);
        }
        foreach ($this->get_supplier_costs($supplier_id, $start, $end) as $c) {
            $total += $c['amount'];
            $paid  += min($c['amount'], $c['paid_amount']);
        }

        return (object) ['total' => $total, 'paid' => $paid, 'unpaid' => max(0, $total - $paid)];
    }

    /* ---------- Supplier payments (partial supported) ---------- */

    public function add_payment($source_table, $source_id, $supplier_id, $amount, $date, $mode = null, $note = null)
    {
        $amount = (float) $amount;
        if ($amount <= 0 || !$this->db->table_exists(db_prefix() . 'fleet_payments')) {
            return false;
        }

        $this->db->insert(db_prefix() . 'fleet_payments', [
            'source_table' => $source_table,
            'source_id'    => $source_id,
            'supplier_id'  => $supplier_id ?: null,
            'amount'       => $amount,
            'payment_date' => $date ? to_sql_date($date) : date('Y-m-d'),
            'payment_mode' => $mode,
            'note'         => $note,
            'created_by'   => get_staff_user_id(),
            'date_created' => date('Y-m-d H:i:s'),
        ]);

        return $this->db->insert_id();
    }

    public function record_paid($source_table, $source_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_payments')) {
            return 0;
        }
        $r = $this->db->select('COALESCE(SUM(amount),0) s')
            ->where('source_table', $source_table)->where('source_id', $source_id)
            ->get(db_prefix() . 'fleet_payments')->row();

        return $r ? (float) $r->s : 0;
    }

    public function record_total($source_table, $source_id)
    {
        $map = [
            'fleet_part_orders' => 'total_price',
            'fleet_maintenance' => 'cost',
            'fleet_fuel_logs'   => 'total_cost',
            'fleet_reminders'   => 'cost',
        ];
        if (!isset($map[$source_table])) {
            return 0;
        }
        $row = $this->db->where('id', $source_id)->get(db_prefix() . $source_table)->row();

        return $row ? (float) $row->{$map[$source_table]} : 0;
    }

    public function get_payments($source_table, $source_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_payments')) {
            return [];
        }
        $this->db->where('source_table', $source_table)->where('source_id', $source_id);
        $this->db->order_by('payment_date', 'desc')->order_by('id', 'desc');

        return $this->db->get(db_prefix() . 'fleet_payments')->result_array();
    }

    public function delete_payment($id)
    {
        $this->db->where('id', $id)->delete(db_prefix() . 'fleet_payments');

        return true;
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
     * Rental inspections (état des lieux) — checkout & check-in
     * ----------------------------------------------------------------- */

    /** Returns the two inspections of a rental keyed by type (checkout/checkin). */
    public function get_inspections($rental_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_inspections')) {
            return [];
        }

        $this->db->where('rental_id', $rental_id);
        $rows = $this->db->get(db_prefix() . 'fleet_inspections')->result_array();

        $keyed = [];
        foreach ($rows as $row) {
            $keyed[$row['type']] = $row;
        }

        return $keyed;
    }

    public function get_inspection($id)
    {
        return $this->db->get_where(db_prefix() . 'fleet_inspections', ['id' => $id])->row();
    }

    /** Upsert an inspection by (rental_id, type); returns its id. */
    public function save_inspection($data)
    {
        $data = $this->_clean_numeric($data, ['odometer', 'fuel_level']);
        $data = $this->_clean_dates($data, ['inspection_date']);

        $type = in_array(($data['type'] ?? ''), ['checkout', 'checkin'], true) ? $data['type'] : 'checkout';
        $data['type'] = $type;

        $existing = $this->db->get_where(db_prefix() . 'fleet_inspections', [
            'rental_id' => $data['rental_id'], 'type' => $type,
        ])->row();

        if ($existing) {
            $this->db->where('id', $existing->id);
            $this->db->update(db_prefix() . 'fleet_inspections', $data);

            return $existing->id;
        }

        $data['created_by']   = get_staff_user_id();
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'fleet_inspections', $data);

        return $this->db->insert_id();
    }

    public function get_inspection_files($inspection_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_inspection_files')) {
            return [];
        }

        $this->db->where('inspection_id', $inspection_id);
        $this->db->order_by('id', 'asc');

        return $this->db->get(db_prefix() . 'fleet_inspection_files')->result_array();
    }

    public function add_inspection_file($data)
    {
        $data['created_by']   = get_staff_user_id();
        $data['date_created'] = date('Y-m-d H:i:s');
        $this->db->insert(db_prefix() . 'fleet_inspection_files', $data);

        return $this->db->insert_id();
    }

    public function get_inspection_file($id)
    {
        return $this->db->get_where(db_prefix() . 'fleet_inspection_files', ['id' => $id])->row();
    }

    public function delete_inspection_file($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_inspection_files');

        return $this->db->affected_rows() > 0;
    }

    /* ----------------------------------------------------------------- *
     * Security deposit (caution) lifecycle
     * ----------------------------------------------------------------- */

    /** Mark the deposit as collected/held. */
    public function deposit_hold($rental_id)
    {
        $this->db->where('id', $rental_id);
        $this->db->update(db_prefix() . 'fleet_rentals', [
            'deposit_status'    => 'held',
            'deposit_held_date' => date('Y-m-d'),
        ]);

        return true;
    }

    /** Settle the deposit at return: withhold part/all of it and return the rest. */
    public function deposit_settle($rental_id, $withheld, $note = '')
    {
        $rental = $this->db->get_where(db_prefix() . 'fleet_rentals', ['id' => $rental_id])->row();
        if (!$rental) {
            return false;
        }

        $deposit  = (float) $rental->deposit;
        $withheld = max(0, min((float) $withheld, $deposit));

        if ($withheld <= 0) {
            $status = 'returned';
        } elseif ($withheld >= $deposit) {
            $status = 'withheld';
        } else {
            $status = 'partial';
        }

        $this->db->where('id', $rental_id);
        $this->db->update(db_prefix() . 'fleet_rentals', [
            'deposit_status'        => $status,
            'deposit_withheld'      => $withheld,
            'deposit_note'          => $note,
            'deposit_returned_date' => date('Y-m-d'),
        ]);

        return true;
    }

    /* ----------------------------------------------------------------- *
     * Vehicle documents (registration, insurance, technical inspection...)
     * ----------------------------------------------------------------- */

    public function get_vehicle_files($vehicle_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_vehicle_files')) {
            return [];
        }

        $this->db->where('vehicle_id', $vehicle_id);
        $this->db->order_by('expiry_date', 'asc');

        return $this->db->get(db_prefix() . 'fleet_vehicle_files')->result_array();
    }

    public function add_vehicle_file($data)
    {
        $data = $this->_clean_dates($data, ['issue_date', 'expiry_date']);

        $data['created_by']   = get_staff_user_id();
        $data['date_created'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'fleet_vehicle_files', $data);

        return $this->db->insert_id();
    }

    public function get_vehicle_file($id)
    {
        return $this->db->get_where(db_prefix() . 'fleet_vehicle_files', ['id' => $id])->row();
    }

    public function delete_vehicle_file($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_vehicle_files');

        return $this->db->affected_rows() > 0;
    }

    /* ----------------------------------------------------------------- *
     * Traffic fines / contraventions (PV)
     * ----------------------------------------------------------------- */

    public function get_fine($id = '')
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get(db_prefix() . 'fleet_fines')->row();
        }

        $this->db->select('f.*, v.name as vehicle_name, v.plate as vehicle_plate, CONCAT(s.firstname, " ", s.lastname) as driver_name, c.company as client_name');
        $this->db->from(db_prefix() . 'fleet_fines f');
        $this->db->join(db_prefix() . 'fleet_vehicles v', 'v.id = f.vehicle_id', 'left');
        $this->db->join(db_prefix() . 'staff s', 's.staffid = f.driver_id', 'left');
        $this->db->join(db_prefix() . 'clients c', 'c.userid = f.clientid', 'left');
        $this->db->order_by('f.fine_date', 'desc');

        return $this->db->get()->result_array();
    }

    public function add_fine($data)
    {
        $data = $this->_clean_numeric($data, ['vehicle_id', 'driver_id', 'clientid', 'rental_id', 'amount']);
        $data = $this->_clean_dates($data, ['fine_date']);

        $data['paid']         = !empty($data['paid']) ? 1 : 0;
        $data['created_by']   = get_staff_user_id();
        $data['date_created'] = date('Y-m-d H:i:s');

        $this->db->insert(db_prefix() . 'fleet_fines', $data);
        $id = $this->db->insert_id();

        if ($id && !empty($data['vehicle_id'])) {
            $this->log_activity($data['vehicle_id'], 'fine', _l('fleet_log_fine_added', $data['fine_number'] ?? ('#' . $id)));
        }

        return $id;
    }

    public function update_fine($id, $data)
    {
        $data = $this->_clean_numeric($data, ['vehicle_id', 'driver_id', 'clientid', 'rental_id', 'amount']);
        $data = $this->_clean_dates($data, ['fine_date']);

        $data['paid'] = !empty($data['paid']) ? 1 : 0;

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_fines', $data);

        return true;
    }

    public function delete_fine($id)
    {
        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'fleet_fines');

        return $this->db->affected_rows() > 0;
    }

    /**
     * Re-bill a fine to its client by creating a draft Perfex invoice.
     *
     * @return int|false Invoice id on success, false otherwise.
     */
    public function create_fine_invoice($fine_id)
    {
        $fine = $this->get_fine($fine_id);

        if (!$fine || empty($fine->clientid) || !empty($fine->invoice_id) || (float) $fine->amount <= 0) {
            return false;
        }

        $this->load->model('invoices_model');
        $this->load->model('currencies_model');

        $base_currency = $this->currencies_model->get_base_currency();
        $vehicle       = $fine->vehicle_id ? $this->get_vehicle($fine->vehicle_id) : null;
        $vehicle_label = $vehicle ? ($vehicle->name . ' (' . $vehicle->plate . ')') : '';

        $description = _l('fleet_fine_invoice_title', $fine->fine_number ?: ('#' . $fine->id));
        $long        = trim($vehicle_label . ($fine->fine_date ? ' — ' . _dt($fine->fine_date) : ''));

        $invoice_data = [
            'clientid'         => $fine->clientid,
            'number'           => get_option('next_invoice_number'),
            'date'             => _d(date('Y-m-d')),
            'duedate'          => _d(date('Y-m-d', strtotime('+' . (int) get_option('fleet_invoice_due_days') . ' days'))),
            'currency'         => $base_currency->id,
            'subtotal'         => (float) $fine->amount,
            'total'            => (float) $fine->amount,
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
                    'qty'              => 1,
                    'unit'             => '',
                    'rate'             => (float) $fine->amount,
                    'order'            => 1,
                    'taxname'          => [],
                ],
            ],
        ];

        $invoice_id = $this->invoices_model->add($invoice_data);

        if ($invoice_id) {
            $this->db->where('id', $fine_id);
            $this->db->update(db_prefix() . 'fleet_fines', ['invoice_id' => $invoice_id]);
        }

        return $invoice_id;
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
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_order_items')) {
            return 0;
        }

        $rec = $this->db->select('COALESCE(SUM(li.quantity),0) q')
            ->from(db_prefix() . 'fleet_part_order_items li')
            ->join(db_prefix() . 'fleet_part_orders o', 'o.id = li.order_id')
            ->where('li.item_id', $item_id)->where('o.status', 'received')
            ->get()->row();
        $asg = $this->db->select('COALESCE(SUM(quantity),0) q')
            ->where('item_id', $item_id)
            ->get(db_prefix() . 'fleet_part_assignments')->row();

        return (int) ($rec->q ?? 0) - (int) ($asg->q ?? 0);
    }

    public function item_last_unit_price($item_id)
    {
        $row = $this->db->select('li.unit_price')
            ->from(db_prefix() . 'fleet_part_order_items li')
            ->join(db_prefix() . 'fleet_part_orders o', 'o.id = li.order_id')
            ->where('li.item_id', $item_id)->where('o.status', 'received')
            ->order_by('o.received_date', 'desc')->order_by('li.id', 'desc')->limit(1)
            ->get()->row();

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

        $this->db->select('o.*, s.name as supplier_name');
        $this->db->from(db_prefix() . 'fleet_part_orders o');
        $this->db->join(db_prefix() . 'fleet_suppliers s', 's.id = o.supplier_id', 'left');

        if (is_numeric($id)) {
            $this->db->where('o.id', $id);

            return $this->db->get()->row();
        }

        $this->db->order_by('o.date_created', 'desc');
        $orders = $this->db->get()->result_array();

        foreach ($orders as &$o) {
            $lines             = $this->get_order_items($o['id']);
            $names             = array_column($lines, 'item_name');
            $o['items_count']  = count($lines);
            $o['items_summary'] = implode(', ', array_slice($names, 0, 2)) . (count($names) > 2 ? ' +' . (count($names) - 2) : '');
        }

        return $orders;
    }

    public function get_order_items($order_id)
    {
        if (!$this->db->table_exists(db_prefix() . 'fleet_part_order_items')) {
            return [];
        }

        $this->db->select('li.*, i.name as item_name, i.reference as item_reference');
        $this->db->from(db_prefix() . 'fleet_part_order_items li');
        $this->db->join(db_prefix() . 'fleet_part_items i', 'i.id = li.item_id', 'left');
        $this->db->where('li.order_id', $order_id);
        $this->db->order_by('li.id', 'asc');

        return $this->db->get()->result_array();
    }

    public function add_part_order($data, $lines = [])
    {
        $data = $this->_prepare_order_data($data);
        $data['status']       = $data['status'] ?? 'ordered';
        $data['date_created'] = date('Y-m-d H:i:s');
        $data['created_by']   = get_staff_user_id();

        $this->db->insert(db_prefix() . 'fleet_part_orders', $data);
        $id = $this->db->insert_id();
        if (!$id) {
            return false;
        }

        $total = $this->_save_order_lines($id, $lines);
        $this->db->where('id', $id)->update(db_prefix() . 'fleet_part_orders', ['total_price' => $total]);

        if ($data['status'] === 'received') {
            $this->db->where('id', $id)->update(db_prefix() . 'fleet_part_orders', ['received_date' => date('Y-m-d')]);
            $this->_receive_sync($id);
        }

        return $id;
    }

    public function update_part_order($id, $data, $lines = [])
    {
        $data = $this->_prepare_order_data($data);
        unset($data['status']); // status changes go through receive/cancel

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'fleet_part_orders', $data);

        $total = $this->_save_order_lines($id, $lines);
        $this->db->where('id', $id)->update(db_prefix() . 'fleet_part_orders', ['total_price' => $total]);

        $this->_receive_sync($id);

        return true;
    }

    /**
     * Replace an order's line items and return the recomputed grand total.
     */
    private function _save_order_lines($order_id, $lines)
    {
        $this->db->where('order_id', $order_id)->delete(db_prefix() . 'fleet_part_order_items');

        $total = 0;
        foreach ((array) $lines as $line) {
            $item_id = (int) ($line['item_id'] ?? 0);
            if (!$item_id) {
                continue;
            }
            $qty   = max(1, (int) ($line['quantity'] ?? 1));
            $price = (float) ($line['unit_price'] ?? 0);
            $lt    = round($qty * $price, 2);
            $total += $lt;

            $this->db->insert(db_prefix() . 'fleet_part_order_items', [
                'order_id'    => $order_id,
                'item_id'     => $item_id,
                'quantity'    => $qty,
                'unit_price'  => $price,
                'total_price' => $lt,
            ]);
        }

        return $total;
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
        $this->db->where('order_id', $id)->delete(db_prefix() . 'fleet_part_order_items');
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

        $amount  = ($order->status === 'received') ? $order->total_price : 0;
        $names   = array_column($this->get_order_items($id), 'item_name');
        $summary = implode(', ', array_slice($names, 0, 3)) . (count($names) > 3 ? '…' : '');
        $name    = _l('fleet_order') . ' #' . $id . ($order->supplier_name ? ' - ' . $order->supplier_name : '');

        $extra = [
            'reference_no' => $order->invoice_no ?? '',
            'billable'     => !empty($order->billable) ? 1 : 0,
            'clientid'     => !empty($order->clientid) ? $order->clientid : null,
        ];

        $this->_sync_record_expense('fleet_part_orders', $id, $amount, $name, $summary, $order->received_date ?: $order->order_date, $extra);
    }

    private function _prepare_order_data($data)
    {
        unset($data['order_total'], $data['line_item'], $data['line_qty'], $data['line_price']);
        $data = $this->_clean_numeric($data, ['supplier_id', 'clientid']);
        $data = $this->_clean_dates($data, ['order_date']);

        $data['billable'] = isset($data['billable']) && $data['billable'] ? 1 : 0;

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
        $data = $this->_clean_numeric($data, ['vehicle_id', 'clientid', 'driver_id', 'daily_rate', 'odometer_start', 'odometer_end', 'deposit']);
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
