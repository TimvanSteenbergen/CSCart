<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

require_once(Registry::get('config.dir.functions') . 'fn.sales_reports.php');

$order_status_descr = fn_get_simple_statuses(STATUSES_ORDER, true, true);
Registry::get('view')->assign('order_status_descr', $order_status_descr);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $suffix = '';

    /*
     * Reports management
     */

    // Add/update report
    if ($mode == 'update') {
        $report_id = fn_update_sales_report($_REQUEST['report_data'], $_REQUEST['report_id']);

        $suffix = ".update?report_id=$report_id";
    }

    /*
     * Reports view
     */

    // Report view routines
    if ($mode == 'set_report_view') {

        $data = array (
            'period' => empty($_REQUEST['period']) ? 'C' : $_REQUEST['period'],
        );

        if (!empty($_REQUEST['period']) && $_REQUEST['period'] != 'A') {
            list($data['time_from'], $data['time_to']) = fn_create_periods($_REQUEST);
        } else {
            $data['time_from'] = $data['time_to'] = 0;
        }

        db_query("UPDATE ?:sales_reports SET ?u WHERE report_id = ?i", $data, $_REQUEST['report_id']);

        if (!empty($_REQUEST['selected_section'])) { // FIXME!!! Bad style
            $_suffix = "&table_id=" . str_replace('table_', '', $_REQUEST['selected_section']);
        }

        $suffix = ".view?report_id=$_REQUEST[report_id]" . $_suffix;
    }

    /*
     * Report tables management
     */

    // Clone table
    if ($mode == 'clone_table') {
        foreach ($_REQUEST['del'] as $k => $v) {
            fn_report_table_clone($_REQUEST['report_id'], $k);
        }

        $suffix = ".update?report_id=$_REQUEST[report_id]";
    }

    // Delete table
    if ($mode == 'm_delete_tables') {
        foreach ($_REQUEST['del'] as $k => $v) {
            fn_delete_report_data('table', $k);
        }

        $suffix = ".update?report_id=$_REQUEST[report_id]";
    }

    // Update table
    if ($mode == 'update_table') {

        $table_id = fn_sales_report_update_table($_REQUEST['table_data'], $_REQUEST['table_id']);

        $suffix = ".update_table?report_id=$_REQUEST[report_id]&table_id=$table_id";
    }

    return array(CONTROLLER_STATUS_OK, "sales_reports$suffix");
}

$depend_items = fn_get_depended();

// The list of all reports
if ($mode == 'manage') {

    Registry::get('view')->assign('reports', fn_get_order_reports());

// Delete single report
} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['report_id'])) {
        fn_delete_report_data('report', $_REQUEST['report_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "sales_reports.manage");

// Edit report
} elseif ($mode == 'update' || $mode == 'add') {

    Registry::set('navigation.tabs.general', array (
        'title' => __('general'),
        'js' => true
    ));

    if (!empty($_REQUEST['report_id'])) {
        Registry::set('navigation.tabs.tables', array (
            'title' => __('charts'),
            'js' => true
        ));

        $report_data = fn_get_report_data($_REQUEST['report_id']);
        Registry::get('view')->assign('report', $report_data);
    }

// Update table
} elseif ($mode == 'update_table') {

    Registry::set('navigation.tabs.general', array (
        'title' => __('general'),
        'js' => true
    ));

    foreach ($depend_items as $value) {
        Registry::set('navigation.tabs.' . $value['code'], array (
            'title' => __('reports_parameter_' . $value['element_id']),
            'js' => true
        ));
    }

    Registry::get('view')->assign('search_condition', true);
    Registry::get('view')->assign('intervals', db_get_array("SELECT * FROM ?:sales_reports_intervals ORDER BY interval_id"));

    // Payments
    Registry::get('view')->assign('payment_processors', db_get_array("SELECT processor_id, processor FROM ?:payment_processors"));
    Registry::get('view')->assign('payments', db_get_array("SELECT ?:payments.*, ?:payment_descriptions.* FROM ?:payments LEFT JOIN ?:payment_descriptions ON ?:payment_descriptions.payment_id = ?:payments.payment_id AND ?:payment_descriptions.lang_code = ?s ORDER BY ?:payments.position", DESCR_SL));

    // Users Location
    Registry::get('view')->assign('usergroups', fn_get_usergroups('C', CART_LANGUAGE));
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('destinations', fn_get_destinations(CART_LANGUAGE));

    // Locations
    Registry::get('view')->assign('destinations', fn_get_destinations(CART_LANGUAGE));

    if (!empty($_REQUEST['table_id'])) {
        $table_data = fn_get_report_data($_REQUEST['report_id'], $_REQUEST['table_id']);
        $conditions = fn_get_table_condition($_REQUEST['table_id']);

        if (empty($conditions)) {
            $conditions = array();
        }

        Registry::get('view')->assign('conditions', $conditions);
        Registry::get('view')->assign('table', $table_data);
    }

// Delete report table
} elseif ($mode == 'delete_table') {
    if (!empty($_REQUEST['table_id'])) {
        fn_delete_report_data('table', $_REQUEST['table_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "sales_reports.update?report_id=$_REQUEST[report_id]&selected_section=tables");

// Clear table conditions
} elseif ($mode == 'clear_conditions') {
    db_query("DELETE FROM ?:sales_reports_table_conditions WHERE table_id = ?i", $_REQUEST['table_id']);

    fn_set_notification('N', __('notice'), __('text_conditions_cleared'));

    return array(CONTROLLER_STATUS_REDIRECT, "sales_reports.update_table?report_id=$_REQUEST[report_id]&table_id=$_REQUEST[table_id]");

// View report
} elseif ($mode == 'view') {

    $report_id = empty($_REQUEST['report_id']) ? db_get_field("SELECT report_id FROM ?:sales_reports WHERE status = 'A' ORDER BY position ASC LIMIT 1") : $_REQUEST['report_id'];
    $table_id = empty($_REQUEST['table_id']) ? db_get_field("SELECT table_id FROM ?:sales_reports_tables WHERE report_id = ?i ORDER BY position ASC LIMIT 1", $report_id) : intval($_REQUEST['table_id']);

    $reports = fn_get_order_reports(true, $report_id);

    // If some reports defined calculate data for them
    if (!empty($reports)) {
        $report = $reports[$report_id];

        // Get report data for each table;
        if (!empty($report['tables'])) {
            if (isset($report['tables'][$table_id])) {
                $table = $report['tables'][$table_id];
                if (!empty($table['elements']) && !empty($table['intervals']) && $table['type'] == 'T') {
                    $_table_cond = fn_get_table_condition($table['table_id']);

                    if (!empty($_table_cond)) {
                        $table_conditions[$table['table_id']] = fn_reports_get_conditions($_table_cond);
                    }
                    $_values = fn_get_report_statistics($table);
                    $report['tables'][$table_id]['values'] = $_values;

                    // Calculate totals
                    $report['tables'][$table_id]['totals'] = array();
                    foreach ($_values as $v) {
                        foreach ($v as $_k => $_v) {
                            $report['tables'][$table_id]['totals'][$_k] = empty($report['tables'][$table_id]['totals'][$_k]) ? $_v : ($report['tables'][$table_id]['totals'][$_k] + $_v);
                        }
                    }

                    $_element_id =  db_get_field("SELECT element_id FROM ?:sales_reports_table_elements WHERE table_id = ?i", $table['table_id']);
                    if (!empty($_element_id)) {
                        $report['tables'][$table_id]['parameter'] = __("reports_parameter_$_element_id");
                    }
                    if (fn_is_empty($_values)) {
                        $report['tables'][$table_id]['empty_values'] = 'Y';
                    }
                    // Find max value
                    $_max = 0;
                    foreach (@$_values as $kkk => $vvv) {
                        foreach ($vvv as $kk => $vv) {
                            if ($vv > $_max) {
                                $_max = $vv;
                            }
                        }
                    }
                    $report['tables'][$table_id]['max_value'] = $_max;
                    if ($table['type'] == 'B' && $intervals_limits[count($table['elements'])] < count($table['intervals'])) {
                        $report['tables'][$table_id]['pages'] = ceil(count($table['intervals']) / $intervals_limits[count($table['elements'])]);
                    }
                // Chart and Pie
                } elseif (!empty($table['elements']) && !empty($table['intervals']) && $table['type'] == 'P') {
                    $_values = fn_get_report_statistics($table);
                    foreach ($table['elements'] as $key => $value) {
                        foreach ($_values[$value['element_hash']] as $k => $v) {
                            $_new_array[] = array(
                                'label' => $value['description'],
                                'count' => $v
                            );
                        }
                    }
                    $new_array['pie_data'] = $_new_array;
                    $new_array['title'] = $table['description'];
                    Registry::get('view')->assign('new_array', $new_array);
                // Bar
                } elseif (!empty($table['elements']) && !empty($table['intervals']) && ($table['type'] == 'B')) {
                    $_values = fn_get_report_statistics($table);
                    foreach ($table['elements'] as $key => $value) {
                        foreach ($_values[$value['element_hash']] as $k => $v) {
                            $_new_array[] = array(
                                    'title' => $value['description'],
                                    'value' => $v,
                                    'color' => fn_sr_get_random_color()
                                );
                        }
                    }
                    $new_array['title'] = $table['description'];
                    $new_array['column_data'] = $_new_array;
                    Registry::get('view')->assign('new_array', $new_array);
                }
            }
        }

        if (!empty($table_conditions)) {
            Registry::get('view')->assign('table_conditions', $table_conditions);
        }
        // Periods

        $intervals = db_get_array("SELECT a.* FROM ?:sales_reports_intervals as a ORDER BY a.interval_id");

        // [Page sections]
        foreach ($reports as $key => $value) {
            Registry::set('navigation.dynamic.sections.' . $key, array (
                'title' => $value['description'],
                'href' => "sales_reports.view?report_id=$key",
                'ajax' => true
            ));
        }

        Registry::set('navigation.dynamic.active_section', $report_id);

        foreach ($reports[$report_id]['tables'] as $key => $value) {
            Registry::set('navigation.tabs.table_' . $value['table_id'], array (
                'title' => $value['description'],
                'href' => "sales_reports.view?report_id=$report_id&table_id=" . $value['table_id'],
                'ajax' => true
            ));
        }
        // [/Page sections]

        Registry::get('view')->assign('report_id', $report_id);
        Registry::get('view')->assign('intervals', $intervals);
        Registry::get('view')->assign('table', $report['tables'][$table_id]); // FIX IT
        Registry::get('view')->assign('report', $report);
    }
}

// ********************************* // ********************************
if (!empty($_REQUEST['report_id'])) {
    Registry::get('view')->assign('report_elements', fn_get_parameters($_REQUEST['report_id']));
    Registry::get('view')->assign('report_id', $_REQUEST['report_id']);
}

$colors = array('pink', 'peru', 'plum', 'azure', 'aquamarine', 'blueviolet', 'firebrick', 'royalblue', 'darkgreen', 'darkorange', 'deepskyblue', 'gold ', 'darkseagreen ', 'tomato', 'wheat', 'seagreen');

Registry::get('view')->assign('colors', $colors);
Registry::get('view')->assign('depend_items', $depend_items);

function fn_sr_get_random_color()
{
    $numbers = array('1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'A', 'B', 'C', 'D', 'E', 'F');
    $color = '';
    for ($i = 0; $i < 6; $i++) {
        $color .= $numbers[rand(0, 15)];
    }

    return '#' . $color;
}

function fn_update_sales_report($report_data, $report_id = 0, $lang_code = DESCR_SL)
{
    if (empty($report_id)) {
        $report_data['type'] = !empty($report_data['type']) ? $report_data['type'] : 'O';
        $report_data['period'] = !empty($report_data['period']) ? $report_data['period'] : 'Y';
        list($report_data['time_from'], $report_data['time_to']) = fn_create_periods($report_data);

        $report_id = db_query("INSERT INTO ?:sales_reports ?e", $report_data);
        fn_create_description('sales_reports_descriptions', 'report_id', $report_id, $report_data);
    } else {
        db_query('UPDATE ?:sales_reports SET ?u WHERE report_id = ?i', $report_data, $report_id);
        db_query('UPDATE ?:sales_reports_descriptions SET ?u WHERE report_id = ?i AND lang_code = ?s', $report_data, $report_id, $lang_code);
    }

    if (!empty($report_data['tables'])) {
        foreach ($report_data['tables'] as $k => $value) {
            if (!extension_loaded('gd') && $value['type'] != 'T') {
                if (empty($_flag)) {
                    fn_set_notification('W',__('warning'), __('text_gd_not_avail'));
                }
                $_flag = true;
                $value['type'] = 'T';
            }

            db_query("UPDATE ?:sales_reports_tables SET ?u WHERE table_id = ?i", $value, $k);
            db_query('UPDATE ?:sales_reports_table_descriptions SET ?u WHERE table_id = ?i AND lang_code = ?s', $value, $k, $lang_code);

            if ($value['type'] == 'P') {
                db_query("UPDATE ?:sales_reports_tables SET interval_id = 1 WHERE table_id = ?i", $k);
            }
        }
    }

    return $report_id;
}

function fn_sales_report_update_table($table_data, $table_id = 0, $lang_code = DESCR_SL)
{
    if ($table_data['type'] == 'P') {
        $table_data['interval_id'] = '1';
    }

    if (empty($table_id)) {
        $table_id = db_query("INSERT INTO ?:sales_reports_tables ?e", $table_data);
        fn_create_description('sales_reports_table_descriptions', "table_id", $table_id, $table_data);

        // Create parameters
        $_data = $table_data['elements'];
        $_data['table_id'] = $table_id;
        $_data['report_id'] = $table_data['report_id'];
        $_data['element_hash'] = fn_generate_element_hash($table_id, $_data['element_id'], '');
        db_query("INSERT INTO ?:sales_reports_table_elements ?e", $_data);
    } else {
        db_query('UPDATE ?:sales_reports_tables SET ?u WHERE table_id = ?i', $table_data, $table_id);
        db_query('UPDATE ?:sales_reports_table_descriptions SET ?u WHERE table_id = ?i AND lang_code = ?s', $table_data, $table_id, $lang_code);

        // Update parameters
        foreach ($table_data['elements'] as $k => $v) {
            if ($v['element_id'] == '4' && $table_data['interval_id'] != '1') {
                db_query("UPDATE ?:sales_reports_tables SET interval_id = 1 WHERE table_id = ?i", $table_id);
                fn_set_notification('W',__('warning'), __('text_status_is_float'));
            }

            db_query('UPDATE ?:sales_reports_table_elements SET ?u WHERE table_id = ?i AND element_hash = ?s', $v, $table_id, $k);
            if ($table_data['type'] != 'T' && $v['limit_auto'] > 25) {
                db_query("UPDATE ?:sales_reports_table_elements SET limit_auto = 25 WHERE table_id = ?i AND element_hash = ?s", $table_id, $k);
                fn_set_notification('W',__('warning'), __('text_max_limit_of_parameters'));
            }
        }
    }

    foreach ($table_data['conditions'] as $section => $ids) {
        db_query("DELETE FROM ?:sales_reports_table_conditions WHERE table_id = ?i AND code = ?s", $table_id, $section);
        $object_ids = is_array($ids) ? $ids : (empty($ids) ? array() : explode(',', $ids));
        foreach ($object_ids as $o_id) {
            $data = array (
                'sub_element_id' => $o_id,
                'table_id' => $table_id,
                'code' => $section
            );

            db_query('REPLACE INTO ?:sales_reports_table_conditions ?e', $data);
        }
    }

    return $table_id;
}
