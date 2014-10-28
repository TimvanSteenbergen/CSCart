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

use Tygh\Enum\ProductTracking;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// Generate dashboard
if ($mode == 'index') {

    // Check for feedback request
    if (
        (!Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate'))
        && (Registry::get('settings.General.feedback_type') == 'auto' || fn_allowed_for('ULTIMATE:FREE'))
        && fn_is_expired_storage_data('send_feedback', SECONDS_IN_DAY * 30)
    ) {
        $redirect_url = 'feedback.send?action=auto&redirect_url=' . urlencode(Registry::get('config.current_url'));

        return array(CONTROLLER_STATUS_REDIRECT, $redirect_url);
    }

    $time_from = !empty($_REQUEST['time_from']) ? $_REQUEST['time_from'] : strtotime('-30 day');
    $time_to = !empty($_REQUEST['time_to']) ? $_REQUEST['time_to']-1 : strtotime('now');
    $time_difference = $time_to - $time_from;
    $is_day = ($time_to - $time_from) <= SECONDS_IN_DAY ? true : false;

    $stats = '';

    if (!defined('HTTPS')) {
        $stats .= base64_decode('PGltZyBzcmM9Imh0dHA6Ly93d3cuY3MtY2FydC5jb20vaW1hZ2VzL2JhY2tncm91bmQuZ2lmIiBoZWlnaHQ9IjEiIHdpZHRoPSIxIiBhbHQ9IiIgLz4=');
    }

    $general_stats = array();

    /* Products */
    if (fn_check_view_permissions('products.manage', 'GET')) {
        $general_stats['products'] = array();

        $params = array(
            'only_short_fields' => true,
            'extend' => array('companies', 'sharing'),
            'status' => 'A',
            'get_conditions' => true,
        );

        list($fields, $join, $condition) = fn_get_products($params);

        db_query('SELECT SQL_CALC_FOUND_ROWS 1 FROM ?:products AS products' . $join . ' WHERE 1 ' . $condition . 'GROUP BY products.product_id');
        $general_stats['products']['total_products'] = db_get_found_rows();

        $params = array(
            'amount_to' => 0,
            'tracking' => array(
                ProductTracking::TRACK_WITHOUT_OPTIONS, ProductTracking::TRACK_WITH_OPTIONS,
            ),
            'get_conditions' => true,
        );

        $params['extend'][] = 'companies';

        if (fn_allowed_for('ULTIMATE')) {
            $params['extend'][] = 'sharing';
        }
        list($fields, $join, $condition) = fn_get_products($params);

        db_query('SELECT SQL_CALC_FOUND_ROWS ' . implode(', ', $fields) . ' FROM ?:products AS products' . $join . ' WHERE 1 ' . $condition . ' GROUP BY products.product_id');
        $general_stats['products']['out_of_stock_products'] = db_get_found_rows();
    }

    /* Customers */
    if (fn_check_view_permissions('profiles.manage', 'GET')) {
        $general_stats['customers'] = array();
        $users_company_condition = fn_get_company_condition('?:users.company_id');
        $general_stats['customers']['registered_customers'] = db_get_field('SELECT COUNT(*) FROM ?:users WHERE user_type = ?s ?p', 'C', $users_company_condition);
    }

    /* Categories */
    if (fn_check_view_permissions('categories.manage', 'GET')) {
        $general_stats['categories'] = array();
        list($fields, $join, $condition, $group_by, $sorting, $limit) = fn_get_categories(array('get_conditions' => true));
        $general_stats['categories']['total_categories'] = db_get_field('SELECT COUNT(*) FROM ?:categories WHERE 1 ?p', $condition);
    }

    /* Storefronts */
    if (fn_check_view_permissions('companies.manage', 'GET')) {
        $general_stats['companies'] = array();
        if (Registry::get('runtime.company_id')) {
            $general_stats['companies']['total_companies'] = 1;
        } else {
            $general_stats['companies']['total_companies'] = db_get_field('SELECT COUNT(*) FROM ?:companies');
        }
    }

    /* Pages */
    if (fn_check_view_permissions('pages.manage', 'GET')) {
        $general_stats['pages'] = array();
        list($fields, $join, $condition) = fn_get_pages(array('get_conditions' => true));
        $general_stats['pages']['total_pages'] = db_get_field('SELECT COUNT(*) FROM ?:pages ' . $join . ' WHERE ' . $condition);
    }

    /* Order */
    $orders_stat = array();

    if (fn_check_view_permissions('orders.manage', 'GET') || fn_check_view_permissions('sales_reports.view', 'GET') || fn_check_view_permissions('taxes.manage', 'GET')) {
        $params = array(
            'period' => 'C',
            'time_from' => $time_from,
            'time_to' => $time_to,
        );
        list($orders_stat['orders'], $search_params, $orders_stat['orders_total']) = fn_get_orders($params, 0, true);

        $time_difference = $time_to - $time_from;
        $params = array(
            'period' => 'C',
            'time_from' => $time_from - $time_difference,
            'time_to' => $time_to - $time_difference,
        );
        list($orders_stat['prev_orders'], $search_params, $orders_stat['prev_orders_total']) = fn_get_orders($params, 0, true);

        $orders_stat['diff']['orders_count'] = count($orders_stat['orders']) - count($orders_stat['prev_orders']);

        $orders_stat['diff']['sales'] = fn_calculate_differences($orders_stat['orders_total']['totally_paid'], $orders_stat['prev_orders_total']['totally_paid']);
    }

    /* Abandoned carts */
    $company_condition = '';

    if (fn_allowed_for('ULTIMATE')) {
        $company_condition = fn_get_company_condition('?:user_session_products.company_id');
    }

    if (fn_check_view_permissions('cart.cart_list', 'GET')) {
        $orders_stat['abandoned_cart_total'] = array_sum(db_get_fields('SELECT COUNT(*) FROM ?:user_session_products WHERE `timestamp` BETWEEN ?i AND ?i ?p GROUP BY user_id', $time_from, $time_to, $company_condition));
        $orders_stat['prev_abandoned_cart_total'] = array_sum(db_get_fields('SELECT COUNT(*) FROM ?:user_session_products WHERE `timestamp` BETWEEN ?i AND ?i ?p GROUP BY user_id', $time_from - $time_difference, $time_to - $time_difference, $company_condition));

        $orders_stat['diff']['abandoned_carts'] = fn_calculate_differences($orders_stat['abandoned_cart_total'], $orders_stat['prev_abandoned_cart_total']);
    }

    // Calculate orders taxes.
    if (fn_check_view_permissions('taxes.manage', 'GET')) {
        $orders_stat['taxes']['subtotal'] = fn_get_orders_taxes_subtotal($orders_stat['orders'], $search_params);
        $orders_stat['taxes']['prev_subtotal'] = fn_get_orders_taxes_subtotal($orders_stat['prev_orders'], $search_params);

        $orders_stat['taxes']['diff'] = fn_calculate_differences($orders_stat['taxes']['subtotal'], $orders_stat['taxes']['prev_subtotal']);
    }

    if (!fn_check_view_permissions('orders.manage', 'GET')) {
        $orders_stat['orders'] = array();
        $orders_stat['prev_orders'] = array();
    }

    if (!fn_check_view_permissions('sales_reports.view', 'GET')) {
        $orders_stat['orders_total'] = array();
        $orders_stat['prev_orders_total'] = array();
    }
    /* /Orders */

    /* Order statuses */
    $order_statuses = array();

    if (fn_check_view_permissions('orders.manage', 'GET')) {
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), false, true);
    }
    /* /Order statuses */

    /* Recent activity block */
    $logs = array();

    if (fn_check_view_permissions('logs.manage', 'GET')) {
        list($logs, $search) = fn_get_logs(array('time_from' => $time_from, 'time_to' => $time_to, 'period' => 'C'), 10); // Get last 10 items
    }
    /* /Recent activity block */

    /* Order by statuses */
    $order_by_statuses = array();

    if (fn_check_view_permissions('orders.manage', 'GET')) {
        $company_condition = fn_get_company_condition('?:orders.company_id');

        $order_by_statuses = db_get_array(
                                    "SELECT "
                                        . "?:status_descriptions.description as status_name, "
                                        . "?:orders.status, "
                                        . "COUNT(*) as count, "
                                        . "SUM(?:orders.total) as total, "
                                        . "SUM(?:orders.shipping_cost) as shipping "
                                    . "FROM ?:orders "
                                    . "INNER JOIN ?:status_descriptions "
                                        . "ON ?:status_descriptions.status = ?:orders.status "
                                    . "WHERE ?:status_descriptions.type = ?s "
                                        . "AND ?:orders.timestamp > ?i "
                                        . "AND ?:orders.timestamp < ?i "
                                        . "AND ?:status_descriptions.lang_code = ?s "
                                        . "?p "
                                    . "GROUP BY ?:orders.status ",
                                    'O', $time_from, $time_to, CART_LANGUAGE, $company_condition);
    }
    /* /Order by statuses */

    /* Statistics */
    $graphs = fn_dashboard_get_graphs_data($time_from, $time_to, $is_day);
    /* /Statistics */

    if (!empty($_SESSION['stats'])) {
        $stats .= implode('', $_SESSION['stats']);
        unset($_SESSION['stats']);
    }

    Registry::get('view')->assign('general_stats', $general_stats);
    Registry::get('view')->assign('orders_stat', $orders_stat);
    Registry::get('view')->assign('stats', $stats);
    Registry::get('view')->assign('logs', $logs);

    Registry::get('view')->assign('order_statuses', $order_statuses);

    Registry::get('view')->assign('graphs', $graphs);
    Registry::get('view')->assign('is_day', $is_day);

    Registry::get('view')->assign('time_from', $time_from);
    Registry::get('view')->assign('time_to', $time_to);

    Registry::get('view')->assign('order_by_statuses', $order_by_statuses);

    if (!empty($_REQUEST['welcome']) && $_REQUEST['welcome'] == 'setup_completed') {
        Registry::get('view')->assign('show_welcome', true);
        Registry::get('view')->assign('product_name', PRODUCT_NAME);
    }
}

function fn_get_orders_taxes_subtotal($orders, $params)
{
    $subtotal = 0;

    if (!empty($orders)) {
        $_oids = array();
        foreach ($orders as $order) {
            if (in_array($order['status'], $params['paid_statuses'])) {
                $oids[] = $order['order_id'];
            }
        }

        if (empty($oids)) {
            return $subtotal;
        }

        $taxes = db_get_fields('SELECT data FROM ?:order_data WHERE order_id IN (?a) AND type = ?s', $oids, 'T');

        if (!empty($taxes)) {
            foreach ($taxes as $tax) {
                $tax = unserialize($tax);
                foreach ($tax as $id => $tax_data) {
                    $subtotal += !empty($tax_data['tax_subtotal']) ? $tax_data['tax_subtotal'] : 0;
                }
            }
        }
    }

    return $subtotal;
}

function fn_calculate_differences($new_value, $old_value)
{
    if ($old_value > 0) {
        $diff = ($new_value * 100) / $old_value;
        $diff = number_format($diff, 2);
    } else {
        $diff = '&infin;';
    }

    return $diff;
}

function fn_dashboard_get_graphs_data($time_from, $time_to, $is_day)
{
    $company_condition = fn_get_company_condition('?:orders.company_id');

    $graphs = array();
    $graph_tabs = array();

    $time_to = mktime(23, 59, 59, date("n", $time_to), date("j", $time_to), date("Y", $time_to));

    if (fn_check_view_permissions("sales_reports.view", "GET")) {
        $graphs['dashboard_statistics_sales_chart'] = array();
        $paid_statuses = array('P', 'C');

        for ($i = $time_from; $i <= $time_to; $i = $i + ($is_day ? 60*60 : SECONDS_IN_DAY)) {
            $date = !$is_day ? date("Y, (n-1), j", $i) : date("H", $i);
            if (empty($graphs['dashboard_statistics_sales_chart'][$date])) {
                $graphs['dashboard_statistics_sales_chart'][$date] = array(
                    'cur' => 0,
                    'prev' => 0,
                );
            }
        }

        $sales = db_get_array("SELECT "
                                . "?:orders.timestamp, "
                                . "?:orders.total "
                            . "FROM ?:orders "
                            . "WHERE ?:orders.timestamp BETWEEN ?i AND ?i "
                                . "AND ?:orders.status IN (?a) "
                                . "?p ",
                            $time_from, $time_to, $paid_statuses, $company_condition);
        foreach ($sales as $sale) {
            $date = !$is_day ? date("Y, (n-1), j", $sale['timestamp']) : date("H", $sale['timestamp']);
            $graphs['dashboard_statistics_sales_chart'][$date]['cur'] += $sale['total'];
        }

        $sales_prev = db_get_array("SELECT "
                                    . "?:orders.timestamp, "
                                    . "?:orders.total "
                                . "FROM ?:orders "
                                . "WHERE ?:orders.timestamp BETWEEN ?i AND ?i "
                                    . "AND ?:orders.status IN (?a) "
                                    . "?p ",
                                $time_from - ($time_to - $time_from), $time_from, $paid_statuses, $company_condition);
        foreach ($sales_prev as $sale) {
            $date = $sale['timestamp'] + ($time_to - $time_from);
            $date = !$is_day ? date("Y, (n-1), j", $date) : date("H", $date);
            $graphs['dashboard_statistics_sales_chart'][$date]['prev'] += $sale['total'];
        }

        $graph_tabs['sales_chart'] = array (
            'title' => __('sales'),
            'js' => true
        );
    }

    fn_set_hook('dashboard_get_graphs_data', $time_from, $time_to, $graphs, $graph_tabs, $is_day);

    Registry::set('navigation.tabs', $graph_tabs);

    return $graphs;
}
