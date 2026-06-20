<?php

defined('BASEPATH') or exit('No direct script access allowed');

# Module & menu
$lang['fleet_management']              = 'Fleet Management';
$lang['fleet_vehicles']               = 'Vehicles';
$lang['fleet_rentals']                = 'Rentals';
$lang['fleet_maintenance']            = 'Maintenance';
$lang['fleet_reminders']              = 'Reminders';
$lang['fleet_drivers']                = 'Drivers';

# Vehicle
$lang['fleet_vehicle']                = 'Vehicle';
$lang['fleet_add_vehicle']            = 'Add vehicle';
$lang['fleet_edit_vehicle']           = 'Edit vehicle';
$lang['fleet_vehicle_name']           = 'Name / label';
$lang['fleet_plate']                  = 'Registration plate';
$lang['fleet_brand']                  = 'Brand';
$lang['fleet_model']                  = 'Model';
$lang['fleet_year']                   = 'Year';
$lang['fleet_category']               = 'Category';
$lang['fleet_vin']                    = 'VIN / chassis number';
$lang['fleet_color']                  = 'Color';
$lang['fleet_fuel_type']              = 'Fuel type';
$lang['fleet_transmission']           = 'Transmission';
$lang['fleet_seats']                  = 'Seats';
$lang['fleet_odometer']               = 'Odometer (km)';
$lang['fleet_daily_rate']             = 'Daily rate';
$lang['fleet_daily_rate_with_driver'] = 'Daily rate (with driver)';
$lang['fleet_purchase_date']          = 'Purchase date';
$lang['fleet_purchase_price']         = 'Purchase price';
$lang['fleet_insurance_company']      = 'Insurance company';
$lang['fleet_insurance_policy']       = 'Insurance policy no.';
$lang['fleet_notes']                  = 'Notes';
$lang['fleet_status']                 = 'Status';
$lang['fleet_phone']                  = 'Phone';

# Vehicle statuses
$lang['fleet_status_available']       = 'Available';
$lang['fleet_status_rented']          = 'Rented';
$lang['fleet_status_maintenance']     = 'In maintenance';
$lang['fleet_status_out_of_service']  = 'Out of service';

# Rental statuses
$lang['fleet_status_reserved']        = 'Reserved';
$lang['fleet_status_ongoing']         = 'Ongoing';
$lang['fleet_status_completed']       = 'Completed';
$lang['fleet_status_cancelled']       = 'Cancelled';

# Rentals
$lang['fleet_rental']                 = 'Rental';
$lang['fleet_add_rental']             = 'New rental';
$lang['fleet_edit_rental']            = 'Edit rental';
$lang['fleet_period']                 = 'Period';
$lang['fleet_days']                   = 'Days';
$lang['fleet_total']                  = 'Total';
$lang['fleet_date_start']             = 'Start date';
$lang['fleet_date_end']               = 'End date';
$lang['fleet_with_driver']            = 'With driver';
$lang['fleet_driver']                 = 'Driver';
$lang['fleet_odometer_start']         = 'Odometer out';
$lang['fleet_odometer_end']           = 'Odometer in';
$lang['fleet_pickup_location']        = 'Pickup location';
$lang['fleet_return_location']        = 'Return location';
$lang['fleet_create_invoice']         = 'Create invoice';
$lang['fleet_invoice_created']        = 'Draft invoice created successfully';
$lang['fleet_invoice_create_failed']  = 'Could not create the invoice';
$lang['fleet_invoice_item_title']     = 'Vehicle rental — %s';
$lang['fleet_invoice_item_period']    = 'Rental period: %s to %s';
$lang['fleet_unit_day']               = 'day(s)';

# Maintenance
$lang['fleet_add_maintenance']        = 'Add maintenance';
$lang['fleet_maintenance_record']     = 'Maintenance record';
$lang['fleet_type']                   = 'Type';
$lang['fleet_service_date']           = 'Service date';
$lang['fleet_cost']                   = 'Cost';
$lang['fleet_provider']               = 'Garage / provider';
$lang['fleet_next_service_date']      = 'Next service date';
$lang['fleet_next_service_odometer']  = 'Next service (km)';
$lang['fleet_description']            = 'Description';
$lang['fleet_maintenance_in_global']  = 'Manage all maintenance records under';

$lang['fleet_mtype_oil_change']       = 'Oil change';
$lang['fleet_mtype_revision']         = 'Service / revision';
$lang['fleet_mtype_tires']            = 'Tires';
$lang['fleet_mtype_brakes']           = 'Brakes';
$lang['fleet_mtype_repair']           = 'Repair';
$lang['fleet_mtype_bodywork']         = 'Bodywork';
$lang['fleet_mtype_other']            = 'Other';

# Reminders
$lang['fleet_add_reminder']           = 'Add reminder';
$lang['fleet_reminder']               = 'Reminder';
$lang['fleet_reminder_title']         = 'Title';
$lang['fleet_due_date']               = 'Due date';
$lang['fleet_notify_days']            = 'Notify days before';
$lang['fleet_reminder_expired']       = 'Expired';
$lang['fleet_reminder_soon']          = 'In %s day(s)';
$lang['fleet_reminder_ok']            = 'In %s day(s)';
$lang['fleet_reminder_due_notification'] = 'Fleet reminder: %s (due %s)';

$lang['fleet_rtype_insurance']            = 'Insurance';
$lang['fleet_rtype_technical_inspection'] = 'Technical inspection';
$lang['fleet_rtype_vignette']             = 'Road tax / vignette';
$lang['fleet_rtype_registration']         = 'Registration document';
$lang['fleet_rtype_service']              = 'Scheduled service';
$lang['fleet_rtype_other']                = 'Other';

# Drivers
$lang['fleet_drivers_help']           = 'Drivers are Perfex staff members holding the dedicated "Driver" role. Assign that role to a staff member to make them available here.';
$lang['fleet_add_staff_member']       = 'Add staff member';

# Assignments
$lang['fleet_driver_assigned']        = 'Driver assigned to the vehicle';
$lang['fleet_assignment_ended']       = 'Assignment ended';
$lang['fleet_assign']                 = 'Assign';
$lang['fleet_assignment_start']       = 'Start date';
$lang['fleet_assignment_end']         = 'End date';
$lang['fleet_ended']                  = 'Ended';
$lang['fleet_end_assignment']         = 'End';
