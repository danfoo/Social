<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Fleet Management
Description: Vehicle fleet management for rental companies (with or without driver): vehicles, maintenance, insurance & document reminders, driver assignment and rental billing through native Perfex invoices.
Version: 1.0.0
Requires at least: 2.3.*
Author: PERSO
*/

define('FLEET_MANAGEMENT_MODULE', 'fleet_management');

// Bump this whenever the database schema changes so the auto-migration below
// recreates any missing table/column without a manual deactivate/reactivate.
define('FLEET_MANAGEMENT_DB_VERSION', '1.0.26');

$CI = &get_instance();

register_activation_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_activation_hook');
register_deactivation_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_deactivation_hook');
register_uninstall_hook(FLEET_MANAGEMENT_MODULE, 'fleet_management_uninstall_hook');

register_language_files(FLEET_MANAGEMENT_MODULE, [FLEET_MANAGEMENT_MODULE]);

// Load the module helper deterministically so its functions are always defined
// in controllers and views (a deferred loader call can silently fail at bootstrap).
require_once __DIR__ . '/helpers/fleet_management_helper.php';

function fleet_management_activation_hook()
{
    require __DIR__ . '/install.php';
}

/**
 * Self-healing schema: run the idempotent installer whenever the stored schema
 * version is behind the code, so new tables/columns appear without forcing a
 * manual module reactivation.
 */
hooks()->add_action('admin_init', 'fleet_management_maybe_upgrade_schema', 1);

function fleet_management_maybe_upgrade_schema()
{
    if (get_option('fleet_management_db_version') != FLEET_MANAGEMENT_DB_VERSION) {
        require __DIR__ . '/install.php';
    }
}

function fleet_management_deactivation_hook()
{
    // Keep data on deactivation; only the menu/permissions disappear.
}

function fleet_management_uninstall_hook()
{
    require_once __DIR__ . '/uninstall.php';
}

/**
 * Register module permissions (Perfex >= 2.3 capabilities API).
 */
hooks()->add_action('admin_init', 'fleet_management_permissions');

function fleet_management_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
        ],
    ];

    register_staff_capabilities('fleet', $capabilities, _l('fleet_management'));
}

/**
 * Build the sidebar menu.
 */
hooks()->add_action('admin_init', 'fleet_management_init_menu_items');

function fleet_management_init_menu_items()
{
    $CI = &get_instance();

    if (!staff_can('view', 'fleet')) {
        return;
    }

    $first_url = fleet_first_allowed_feature_url();

    // A non-admin role with no enabled feature gets no fleet menu at all.
    if ($first_url === '' && !is_admin()) {
        return;
    }

    $CI->app_menu->add_sidebar_menu_item('fleet-management', [
        'name'     => _l('fleet_management'),
        'icon'     => 'fa fa-car',
        'position' => 30,
        'href'     => $first_url ?: admin_url('fleet_management/dashboard'),
    ]);

    // Each child item is shown only when its feature is enabled for the role.
    $children = [
        ['dashboard',   'fleet-dashboard',   'fleet_dashboard',       'fleet_management/dashboard'],
        ['vehicles',    'fleet-vehicles',    'fleet_vehicles',        'fleet_management/vehicles'],
        ['rentals',     'fleet-rentals',     'fleet_rentals',         'fleet_management/rentals'],
        ['rentals',     'fleet-planning',    'fleet_planning',        'fleet_management/rentals/planning'],
        ['maintenance', 'fleet-maintenance', 'fleet_maintenance',     'fleet_management/maintenance'],
        ['parts',       'fleet-parts',       'fleet_parts_articles',  'fleet_management/parts'],
        ['fuel',        'fleet-fuel',        'fleet_fuel',            'fleet_management/fuel'],
        ['reminders',   'fleet-reminders',   'fleet_reminders',       'fleet_management/reminders'],
        ['fines',       'fleet-fines',       'fleet_fines',           'fleet_management/fines'],
        ['drivers',     'fleet-drivers',     'fleet_drivers',         'fleet_management/drivers'],
        ['suppliers',   'fleet-suppliers',   'fleet_suppliers',       'fleet_management/suppliers'],
        ['library',     'fleet-library',     'fleet_library',         'fleet_management/library'],
    ];

    $position = 0;
    foreach ($children as $child) {
        list($feature, $slug, $lang, $path) = $child;
        if (!fleet_can_feature($feature)) {
            continue;
        }
        $CI->app_menu->add_sidebar_children_item('fleet-management', [
            'slug'     => $slug,
            'name'     => _l($lang),
            'href'     => admin_url($path),
            'position' => $position++,
        ]);
    }

    // Approval queue: always visible to anyone allowed to validate changes
    // (admins + the designated approver), so it is discoverable even before the
    // workflow is switched on.
    if (fleet_is_approver()) {
        $CI->load->model('fleet_management/fleet_management_model', 'fleet');
        $pending = $CI->fleet->pending_approvals_count();
        $badge   = $pending > 0 ? ' <span class="badge" style="background:#e74c3c;">' . $pending . '</span>' : '';

        $CI->app_menu->add_sidebar_children_item('fleet-management', [
            'slug'     => 'fleet-approvals',
            'name'     => _l('fleet_approvals') . $badge,
            'href'     => admin_url('fleet_management/approvals'),
            'position' => $position++,
        ]);
    }

    // Settings (incl. the per-role access matrix) are reserved for admins.
    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('fleet-management', [
            'slug'     => 'fleet-settings',
            'name'     => _l('fleet_settings'),
            'href'     => admin_url('fleet_management/settings'),
            'position' => $position++,
        ]);
    }
}

/**
 * Lightweight client-side search for the module list tables. A single delegated
 * handler filters any table marked ".fleet-list" from a ".fleet-search" input in
 * the same panel. Output once in the admin footer to keep the views DRY.
 */
hooks()->add_action('app_admin_footer', 'fleet_management_admin_footer');

function fleet_management_admin_footer()
{
    echo '<style>
.fleet-list-page .panel_s{border:0;border-radius:10px;box-shadow:0 2px 10px rgba(20,30,60,.05);}
.fleet-list-page .fleet-toolbar{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;margin-bottom:18px;}
.fleet-list-page .fleet-toolbar h3{margin:0;font-weight:700;font-size:20px;}
.fleet-list-page .fleet-tools{display:flex;align-items:center;flex-wrap:wrap;}
.fleet-list-page .fleet-tools>*{margin-left:8px;margin-top:4px;}
.fleet-list-page .fleet-stat{display:flex;align-items:center;}
.fleet-list-page .fleet-stat .ic{width:46px;height:46px;border-radius:12px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:18px;margin-right:12px;flex:0 0 46px;}
.fleet-list-page .fleet-stat h2{margin:0;font-size:20px;font-weight:700;line-height:1.1;}
.fleet-list-page .fleet-stat span{color:#97a1b3;font-size:11px;text-transform:uppercase;letter-spacing:.5px;}
.fleet-list-page table.fleet-list{margin-bottom:0;}
.fleet-list-page table.fleet-list>thead>tr>th{border-bottom:1px solid #e6e9f0;color:#97a1b3;font-size:11px;text-transform:uppercase;letter-spacing:.4px;font-weight:600;}
.fleet-list-page table.fleet-list>tbody>tr>td{vertical-align:middle;border-top:1px solid #f0f2f5;}
.fleet-list-page table.fleet-list>tbody>tr:hover{background:#f8f9fc;}
.fleet-list-page .fleet-search{border-radius:20px;}
.fleet-list-page .fleet-veh{display:flex;align-items:center;}
.fleet-list-page .fleet-veh .av{width:38px;height:38px;border-radius:10px;background:#eef1ff;color:#6571ff;display:flex;align-items:center;justify-content:center;margin-right:10px;flex:0 0 38px;}
.fleet-list-page .fleet-plate{display:inline-block;background:#f0f2f5;border-radius:6px;padding:2px 8px;font-weight:600;font-size:12px;letter-spacing:.5px;}
.fleet-list-page .fleet-list .label{border-radius:20px;padding:.4em .85em;font-weight:600;font-size:11px;}
</style>
<script>
(function($){
    $(document).on("keyup", ".fleet-search", function(){
        var q = $(this).val().toLowerCase();
        $(this).closest(".panel-body").find("table.fleet-list > tbody > tr").each(function(){
            $(this).toggle($(this).text().toLowerCase().indexOf(q) > -1);
        });
    });
})(jQuery);
</script>';
}

/**
 * Send reminder notifications for expiring vehicle documents (insurance,
 * technical inspection...) X days before the due date. Runs on the Perfex cron.
 */
hooks()->add_action('app_cron', 'fleet_management_cron');

function fleet_management_cron()
{
    $CI = &get_instance();
    $CI->load->model('fleet_management/fleet_management_model');
    $CI->fleet_management_model->send_due_reminders();
    $CI->fleet_management_model->send_due_license_reminders();
    $CI->fleet_management_model->notify_ending_rentals();
}

/**
 * Inject a "fleet supplier" dropdown into the core Perfex expense add/edit form
 * (right below the Client field) so an expense can be assigned to a supplier.
 */
hooks()->add_action('app_admin_footer', 'fleet_expense_supplier_field');

function fleet_expense_supplier_field()
{
    $CI = &get_instance();

    // Detect the expense add/edit page regardless of the leading "admin/"
    // segment (admin controllers live in a sub-directory).
    $uri = $CI->uri->uri_string();
    if (strpos($uri, 'expenses/expense') === false) {
        return;
    }
    if (!staff_can('view', 'fleet')) {
        return;
    }

    // Expense id is the segment right after "expense" (edit page only).
    $parts      = explode('/', $uri);
    $idx        = array_search('expense', $parts, true);
    $expense_id = ($idx !== false && isset($parts[$idx + 1]) && is_numeric($parts[$idx + 1])) ? (int) $parts[$idx + 1] : 0;

    $CI->load->model('fleet_management/fleet_management_model', 'fleet');
    $suppliers = $CI->fleet->get_supplier();

    $selected = $expense_id ? (int) $CI->fleet->get_expense_supplier($expense_id) : 0;

    $options = '<option value="">' . _l('fleet_no_supplier') . '</option>';
    foreach ($suppliers as $s) {
        $sel = ($selected == $s['id']) ? 'selected' : '';
        $options .= '<option value="' . $s['id'] . '" ' . $sel . '>' . html_escape($s['name']) . '</option>';
    }

    echo '<div id="fleet_expense_supplier_wrap" style="display:none;">
        <div class="form-group fleet-expense-supplier" style="margin-top:10px;">
            <label class="control-label" for="fleet_supplier_id">' . _l('fleet_expense_supplier_label') . '</label>
            <select name="fleet_supplier_id" id="fleet_supplier_id" class="selectpicker form-control" data-width="100%" data-live-search="true" data-none-selected-text="' . _l('fleet_no_supplier') . '">' . $options . '</select>
        </div>
    </div>
    <script>
    (function(){
        var tries = 0;
        function inject(){
            if (document.getElementById("fleet_supplier_id") && document.getElementById("fleet_supplier_id").offsetParent !== null) { return; }
            var $wrap = jQuery("#fleet_expense_supplier_wrap .fleet-expense-supplier");
            if (!$wrap.length) { return; }

            // Find the expense form (the form holding the amount / client / category fields).
            var $form = jQuery("form").filter(function(){
                return jQuery(this).find(\'[name="amount"],[name="clientid"],[name="category"]\').length > 0;
            }).first();

            if (!$form.length) { if (tries++ < 40) { setTimeout(inject, 300); } return; }

            // Preferred anchor: right after the Client field.
            var $client = $form.find(\'[name="clientid"]\').first();
            if ($client.length) {
                var $container = $client.closest(".form-group, .mb-3, .mbot15, .form-group-custom");
                if ($container.length) { $container.after($wrap); }
                else { $client.after($wrap); }
            } else {
                var $submit = $form.find(\'button[type="submit"], [type="submit"]\').first();
                if ($submit.length) { $submit.closest("div").before($wrap); }
                else { $form.append($wrap); }
            }

            jQuery("#fleet_expense_supplier_wrap").remove();
            if (jQuery.fn.selectpicker) { jQuery("#fleet_supplier_id").selectpicker(); }
        }
        if (window.jQuery) { jQuery(inject); } else { setTimeout(inject, 500); }
    })();
    </script>';
}

/**
 * Remove our custom field from the POST very early (before Perfex's expense
 * controller inserts the POST into tblexpenses, which would raise an "Unknown
 * column" SQL error / 500). The value is stashed for the after-save hook.
 */
hooks()->add_action('app_init', 'fleet_capture_expense_supplier');

function fleet_capture_expense_supplier()
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        return;
    }
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (strpos($uri, 'expenses/expense') === false) {
        return;
    }
    if (isset($_POST['fleet_supplier_id'])) {
        $GLOBALS['fleet_expense_supplier_value'] = $_POST['fleet_supplier_id'];
        unset($_POST['fleet_supplier_id']);
    }
}

/**
 * Belt-and-suspenders: also strip the field from the data array Perfex filters
 * just before the DB write, in case it reached that far.
 */
hooks()->add_filter('before_expense_added', 'fleet_filter_expense_data');
hooks()->add_filter('before_expense_updated', 'fleet_filter_expense_data');
hooks()->add_filter('before_update_expense', 'fleet_filter_expense_data');

function fleet_filter_expense_data($data)
{
    if (is_array($data)) {
        unset($data['fleet_supplier_id']);
        if (isset($data['data']) && is_array($data['data'])) {
            unset($data['data']['fleet_supplier_id']);
        }
    }

    return $data;
}

/**
 * Persist / clean the expense -> fleet supplier assignment.
 */
hooks()->add_action('after_expense_added', 'fleet_save_expense_supplier');
hooks()->add_action('after_expense_updated', 'fleet_save_expense_supplier');

function fleet_save_expense_supplier($id)
{
    $CI = &get_instance();

    if (is_array($id)) {
        $id = $id['id'] ?? (isset($id['expenseid']) ? $id['expenseid'] : reset($id));
    }
    $id = (int) $id;
    if (!$id) {
        $parts = explode('/', $CI->uri->uri_string());
        $idx   = array_search('expense', $parts, true);
        if ($idx !== false && isset($parts[$idx + 1]) && is_numeric($parts[$idx + 1])) {
            $id = (int) $parts[$idx + 1];
        }
    }
    if (!$id) {
        return;
    }

    $supplier = $GLOBALS['fleet_expense_supplier_value'] ?? $CI->input->post('fleet_supplier_id');

    $CI->load->model('fleet_management/fleet_management_model', 'fleet');
    $CI->fleet->set_expense_supplier($id, $supplier);
}

hooks()->add_action('after_expense_deleted', 'fleet_delete_expense_supplier');

function fleet_delete_expense_supplier($id)
{
    $CI = &get_instance();
    if (is_array($id)) {
        $id = $id['id'] ?? reset($id);
    }
    if ($CI->db->table_exists(db_prefix() . 'fleet_expense_suppliers')) {
        $CI->db->where('expense_id', (int) $id)->delete(db_prefix() . 'fleet_expense_suppliers');
    }
}
