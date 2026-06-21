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
$lang['fleet_export']                 = 'Export CSV';

# Settings
$lang['fleet_settings']                     = 'Settings';
$lang['fleet_set_billing']                  = 'Billing & expenses';
$lang['fleet_set_expense_category']         = 'Expense category for fleet costs';
$lang['fleet_set_expense_category_help']    = 'Maintenance, fuel, parts and costed reminders are posted to this Expenses category.';
$lang['fleet_set_invoice_due_days']         = 'Invoice due (days)';
$lang['fleet_set_invoice_due_days_help']    = 'Number of days added to today for the due date of invoices created from a rental.';
$lang['fleet_set_occupancy_days']           = 'Occupancy window (days)';
$lang['fleet_set_occupancy_days_help']      = 'Rolling window used to compute the vehicle occupancy rate on the dashboard.';
$lang['fleet_set_driver_role']              = 'Driver role';
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
$lang['fleet_suppliers']              = 'Suppliers';
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
$lang['fleet_parts_count']            = 'Catalog items';
$lang['fleet_parts_total_cost']       = 'Total purchase cost';
$lang['fleet_pstatus_installed']      = 'Installed';
$lang['fleet_pstatus_in_stock']       = 'In stock';
$lang['fleet_pstatus_ordered']        = 'Ordered';
$lang['fleet_pstatus_returned']       = 'Returned';
$lang['fleet_log_part_added']         = 'Part purchased: %s';
$lang['fleet_log_part_assigned']      = 'Part assigned: %s (x%s)';
$lang['fleet_atype_part']             = 'Part';

# Parts catalog configuration
$lang['fleet_part_categories']        = 'Part categories';
$lang['fleet_part_units']             = 'Part units';

# Supplier accounting
$lang['fleet_supplier_invoice_no']    = 'Supplier invoice no.';
$lang['fleet_payment']                = 'Payment';
$lang['fleet_paid']                   = 'Paid';
$lang['fleet_unpaid']                 = 'Unpaid';
$lang['fleet_mark_paid']              = 'Mark paid';
$lang['fleet_mark_unpaid']            = 'Mark unpaid';
$lang['fleet_payment_updated']        = 'Payment status updated';
$lang['fleet_acc_total']              = 'Total purchases';
$lang['fleet_acc_paid']               = 'Paid';
$lang['fleet_acc_unpaid']             = 'Outstanding';
$lang['fleet_supplier_orders']        = 'Part orders';
$lang['fleet_supplier_other_costs']   = 'Other linked costs';
$lang['fleet_billable']               = 'Re-billable to a client (billable expense)';
$lang['fleet_reinvoice_client']       = 'Client to re-invoice';
$lang['fleet_paid_amount']            = 'Paid';
$lang['fleet_remaining']              = 'Outstanding';
$lang['fleet_partial']                = 'Partial';
$lang['fleet_pay']                    = 'Pay';
$lang['fleet_record_payment']         = 'Record a payment';
$lang['fleet_payment_amount']         = 'Amount';
$lang['fleet_payment_date']           = 'Payment date';
$lang['fleet_payment_mode']           = 'Payment mode';
$lang['fleet_payment_recorded']       = 'Payment recorded';
$lang['fleet_payment_invalid']        = 'Invalid payment amount';
$lang['fleet_purchase_order']         = 'PURCHASE ORDER';

# Parts inventory workflow
$lang['fleet_unit']                   = 'Unit';
$lang['fleet_min_stock']              = 'Minimum stock';
$lang['fleet_stock']                  = 'Stock';
$lang['fleet_in_stock']               = 'In stock';
$lang['fleet_low_stock']              = 'Low stock';
$lang['fleet_catalog']                = 'Catalog';
$lang['fleet_orders']                 = 'Orders';
$lang['fleet_assignments_tab']        = 'Stock & assignments';
$lang['fleet_buy']                    = 'Buy';
$lang['fleet_place_order']            = 'Place order';
$lang['fleet_new_order']              = 'New order';
$lang['fleet_new_assignment']         = 'Assign from stock';
$lang['fleet_order']                  = 'Order';
$lang['fleet_order_date']             = 'Order date';
$lang['fleet_receive']                = 'Receive';
$lang['fleet_cancel_order']           = 'Cancel';
$lang['fleet_no_orders']              = 'No order yet.';
$lang['fleet_no_assignments']         = 'No assignment yet.';
$lang['fleet_assigned_to']            = 'Assigned to';
$lang['fleet_assigned_to_other']      = 'Or assigned to (other)';
$lang['fleet_assignment_label']       = 'Assignment';
$lang['fleet_ostatus_ordered']        = 'Ordered';
$lang['fleet_ostatus_received']       = 'Received';
$lang['fleet_ostatus_cancelled']      = 'Cancelled';
$lang['fleet_order_placed']           = 'Order placed';
$lang['fleet_add_line']               = 'Add line';
$lang['fleet_lines_count']            = 'Items';
$lang['fleet_order_no_lines']         = 'Add at least one item line';
$lang['fleet_order_received_done']    = 'Order received and added to stock';
$lang['fleet_order_cancelled']        = 'Order cancelled';
$lang['fleet_part_assigned_done']     = 'Part assigned from stock';
$lang['fleet_not_enough_stock']       = 'Not enough stock (available: %s)';

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

# Driver profile
$lang['fleet_driver_profile']         = 'Driver profile';
$lang['fleet_personal_info']          = 'Personal information';
$lang['fleet_staff_account']          = 'Staff account';
$lang['fleet_age']                    = 'Age';
$lang['fleet_years']                  = 'years';
$lang['fleet_date_of_birth']          = 'Date of birth';
$lang['fleet_national_id']            = 'National ID';
$lang['fleet_blood_type']             = 'Blood type';
$lang['fleet_hire_date']              = 'Hire date';
$lang['fleet_emergency_contact']      = 'Emergency contact';
$lang['fleet_emergency_phone']        = 'Emergency phone';
$lang['fleet_employment_emergency']   = 'Employment & emergency';
$lang['fleet_license']                = 'Driver license';
$lang['fleet_license_number']         = 'License number';
$lang['fleet_license_category']       = 'License category';
$lang['fleet_license_issue_date']     = 'Issue date';
$lang['fleet_license_expiry']         = 'Expiry date';
$lang['fleet_valid']                  = 'Valid';
$lang['fleet_expired']                = 'Expired';
$lang['fleet_expiring_soon']          = 'Expiring soon';
$lang['fleet_location']               = 'Location';
$lang['fleet_client']                 = 'Client';
$lang['fleet_yes']                    = 'Yes';
$lang['fleet_no']                     = 'No';

# Driver accidents
$lang['fleet_accidents']              = 'Accidents';
$lang['fleet_accident']               = 'Accident / incident';
$lang['fleet_add_accident']           = 'Report an accident';
$lang['fleet_no_accidents']           = 'No accident recorded';
$lang['fleet_severity']               = 'Severity';
$lang['fleet_severity_minor']         = 'Minor';
$lang['fleet_severity_moderate']      = 'Moderate';
$lang['fleet_severity_severe']        = 'Severe';
$lang['fleet_at_fault']               = 'At fault';
$lang['fleet_third_party']            = 'Third party';

# Driver license reminders
$lang['fleet_license_reminders']            = 'Driver licenses to renew';
$lang['fleet_license_due_notification']     = '%s driver license is due for renewal (expires %s)';
$lang['fleet_set_license_notify_days']      = 'License alert (days before)';
$lang['fleet_set_license_notify_days_help'] = 'Number of days before a driver license expires from which a reminder is generated and staff are notified.';

# Planning & booking
$lang['fleet_planning']                       = 'Planning';
$lang['fleet_no_vehicles']                    = 'No vehicle';
$lang['fleet_booking_conflict']               = 'Booking not possible: this vehicle is already booked from %s to %s (rental %s). Change the dates or the vehicle.';

# E-mail notifications
$lang['fleet_set_notifications']              = 'Notifications';
$lang['fleet_set_email_notifications']        = 'Enable e-mail notifications';
$lang['fleet_set_email_notifications_help']   = 'Sends an e-mail (in addition to the in-app notification) when a vehicle document or driver license reminder is due.';
$lang['fleet_set_notification_emails']        = 'Additional recipients';
$lang['fleet_set_notification_emails_help']   = 'Extra e-mail addresses to notify, comma separated. Staff allowed to view the fleet are already notified.';

# Rental contract & inspection
$lang['fleet_deposit']                  = 'Security deposit';
$lang['fleet_without_driver']           = 'Without driver';
$lang['fleet_contract']                 = 'Rental agreement';
$lang['fleet_contract_lessor']          = 'The lessor';
$lang['fleet_contract_lessee']          = 'The lessee';
$lang['fleet_contract_read_approved']   = 'Read and approved';
$lang['fleet_contract_terms']           = 'The lessee acknowledges receiving the above vehicle in good working order and undertakes to return it in the same condition, at the agreed date and place. Fuel, fines, penalties and any damage not covered by insurance remain the lessee\'s responsibility. The deposit may be withheld in whole or in part for damage noted on return. This agreement is governed by Senegalese law.';
$lang['fleet_inspection']               = 'Condition report';
$lang['fleet_inspections']              = 'Condition reports (out / in)';
$lang['fleet_inspection_checkout']      = 'Condition report — Checkout';
$lang['fleet_inspection_checkin']       = 'Condition report — Check-in';
$lang['fleet_inspection_save_first']    = 'Save this condition report first to be able to add photos.';
$lang['fleet_inspection_not_done']      = 'Not done';
$lang['fleet_fuel_level']               = 'Fuel level';
$lang['fleet_fuel_empty']               = 'Empty (0/8)';
$lang['fleet_fuel_full']                = 'Full (8/8)';
$lang['fleet_exterior_condition']       = 'Exterior condition';
$lang['fleet_interior_condition']       = 'Interior condition';
$lang['fleet_damages']                  = 'Noted damages';
$lang['fleet_photos']                   = 'Photos';
$lang['fleet_upload']                   = 'Upload';
