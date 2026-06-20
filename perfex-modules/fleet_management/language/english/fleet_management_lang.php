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

# Catalog (categories / brands / models)
$lang['fleet_library']                = 'Configuration';
$lang['fleet_categories']             = 'Categories';
$lang['fleet_brands']                 = 'Brands';
$lang['fleet_models']                 = 'Models';
$lang['fleet_model_label']            = 'Model';

# Dashboard
$lang['fleet_dashboard']              = 'Dashboard';
$lang['fleet_dash_total_cost']        = 'Total cost';
$lang['fleet_dash_occupancy']         = 'Occupancy (%s d)';
$lang['fleet_dash_occupancy_short']   = 'Occupancy';
$lang['fleet_dash_per_vehicle']       = 'Cost per vehicle';
$lang['fleet_dash_cost_per_km']       = 'Cost / km';
$lang['fleet_dash_expense_evolution'] = 'Expense evolution (12 months)';
$lang['fleet_dash_cost_split']        = 'Cost split';
$lang['fleet_dash_recent_vehicles']   = 'Last 10 vehicles';
$lang['fleet_search']                 = 'Search...';
$lang['fleet_dash_most_used']         = 'Most used vehicle';
$lang['fleet_dash_most_expensive']    = 'Most expensive vehicle';
$lang['fleet_dash_most_fuel']         = 'Highest fuel cost';
$lang['fleet_period_month']           = 'Month';
$lang['fleet_period_quarter']         = 'Quarter';
$lang['fleet_period_year']            = 'Year';
$lang['fleet_period_all']             = 'All time';

# Part <-> maintenance link
$lang['fleet_link_maintenance']       = 'Link to a maintenance (optional)';
$lang['fleet_no_link']                = '— None —';

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
$lang['fleet_next_service']           = 'Next service';

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

# Suppliers
$lang['fleet_supplier']               = 'Supplier';
$lang['fleet_add_supplier']           = 'Add supplier';
$lang['fleet_supplier_name']          = 'Name';
$lang['fleet_contact_name']           = 'Contact name';
$lang['fleet_website']                = 'Website';
$lang['fleet_vat']                    = 'VAT number';
$lang['fleet_address']                = 'Address';

$lang['fleet_stype_garage']           = 'Garage';
$lang['fleet_stype_insurance']        = 'Insurance company';
$lang['fleet_stype_fuel_station']     = 'Fuel station';
$lang['fleet_stype_rental_partner']   = 'Rental partner';
$lang['fleet_stype_parts']            = 'Parts supplier';
$lang['fleet_stype_other']            = 'Other';

# Fuel
$lang['fleet_fuel']                   = 'Fuel';
$lang['fleet_fuel_log']               = 'Fuel entry';
$lang['fleet_add_fuel']               = 'Add fuel entry';
$lang['fleet_date']                   = 'Date';
$lang['fleet_liters']                 = 'Liters';
$lang['fleet_price_per_liter']        = 'Price / liter';
$lang['fleet_total_cost']             = 'Total cost';
$lang['fleet_station']                = 'Fuel station';
$lang['fleet_full_tank']              = 'Full tank';
$lang['fleet_fuel_type']              = 'Fuel type';
$lang['fleet_fuel_entries']           = 'Entries';
$lang['fleet_fuel_total_liters']      = 'Total liters';
$lang['fleet_fuel_total_cost']        = 'Total cost';
$lang['fleet_fuel_total_hint']        = 'Leave the total cost blank to compute it automatically from liters × price per liter.';

$lang['fleet_fuel_diesel']            = 'Diesel';
$lang['fleet_fuel_petrol']            = 'Petrol';
$lang['fleet_fuel_lpg']               = 'LPG';
$lang['fleet_fuel_electric']          = 'Electric';
$lang['fleet_fuel_hybrid']            = 'Hybrid';
$lang['fleet_fuel_other']             = 'Other';

# Vehicle history (activity timeline)
$lang['fleet_history']                = 'History';
$lang['fleet_history_empty']          = 'No activity recorded yet for this vehicle.';
$lang['fleet_atype_vehicle']          = 'Vehicle';
$lang['fleet_atype_assignment']       = 'Driver';
$lang['fleet_atype_maintenance']      = 'Maintenance';
$lang['fleet_atype_photo']            = 'Photo';
$lang['fleet_atype_reminder']         = 'Reminder';
$lang['fleet_atype_fuel']             = 'Fuel';
$lang['fleet_atype_odometer']         = 'Odometer';
$lang['fleet_atype_rental']           = 'Rental';
$lang['fleet_atype_invoice']          = 'Invoice';
$lang['fleet_atype_other']            = 'Activity';

$lang['fleet_log_vehicle_created']    = 'Vehicle created';
$lang['fleet_log_odometer']           = 'Odometer updated from %s to %s km';
$lang['fleet_log_driver_assigned']    = 'Driver %s assigned';
$lang['fleet_log_assignment_ended']   = 'Assignment of %s ended';
$lang['fleet_log_maintenance']        = 'Maintenance: %s';
$lang['fleet_log_reminder_added']     = 'Reminder added: %s';
$lang['fleet_log_fuel']               = 'Refuel: %s L';
$lang['fleet_log_rental_created']     = 'Rental created (#%s)';
$lang['fleet_log_invoice_created']    = 'Invoice %s created';
$lang['fleet_log_photo_added']        = 'Photo added (%s)';

# Parts / articles inventory
$lang['fleet_parts_articles']         = 'Parts / Articles';
$lang['fleet_part']                   = 'Part';
$lang['fleet_add_part']               = 'Add part';
$lang['fleet_part_name']              = 'Part / article name';
$lang['fleet_reference']              = 'Reference';
$lang['fleet_quantity']               = 'Quantity';
$lang['fleet_unit_price']             = 'Unit purchase price';
$lang['fleet_parts_count']            = 'Parts purchased';
$lang['fleet_parts_total_cost']       = 'Total purchase cost';
$lang['fleet_pstatus_installed']      = 'Installed';
$lang['fleet_pstatus_in_stock']       = 'In stock';
$lang['fleet_pstatus_ordered']        = 'Ordered';
$lang['fleet_pstatus_returned']       = 'Returned';
$lang['fleet_log_part_added']         = 'Part purchased: %s';
$lang['fleet_atype_part']             = 'Part';

# Maintenance parts & photos
$lang['fleet_parts']                  = 'Replaced parts';
$lang['fleet_parts_hint']             = 'List the parts replaced (one per line)';
$lang['fleet_maintenance_photos']     = 'Maintenance photos';
$lang['fleet_photo']                  = 'Photo';
$lang['fleet_photo_taken_date']       = 'Date taken';
$lang['fleet_photo_upload']           = 'Upload';
$lang['fleet_photo_uploaded']         = 'Photo uploaded successfully';
$lang['fleet_no_photos']              = 'No photo attached yet.';

# Assignments
$lang['fleet_driver_assigned']        = 'Driver assigned to the vehicle';
$lang['fleet_assignment_ended']       = 'Assignment ended';
$lang['fleet_assign']                 = 'Assign';
$lang['fleet_assignment_start']       = 'Start date';
$lang['fleet_assignment_end']         = 'End date';
$lang['fleet_ended']                  = 'Ended';
$lang['fleet_end_assignment']         = 'End';
