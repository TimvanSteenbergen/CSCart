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

use Tygh\Mailer;
use Tygh\Pdf;
use Tygh\Registry;
use Tygh\Storage;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $suffix = '';

    if ($mode == 'm_delete' && !empty($_REQUEST['order_ids'])) {
        foreach ($_REQUEST['order_ids'] as $v) {
            fn_delete_order($v);
        }
    }

    if ($mode == 'update_details') {
        fn_trusted_vars('update_order');

        // Update customer's email if its changed in customer's account
        if (!empty($_REQUEST['update_customer_details']) && $_REQUEST['update_customer_details'] == 'Y') {
            $u_id = db_get_field("SELECT user_id FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
            $current_email = db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $u_id);
            db_query("UPDATE ?:orders SET email = ?s WHERE order_id = ?i", $current_email, $_REQUEST['order_id']);
        }

        // Log order update
        fn_log_event('orders', 'update', array(
            'order_id' => $_REQUEST['order_id'],
        ));

        db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $_REQUEST['update_order'], $_REQUEST['order_id']);

        //Update shipping info
        if (!empty($_REQUEST['update_shipping'])) {
            foreach ($_REQUEST['update_shipping'] as $group_key => $shipment) {
                $shipment['shipment_data']['order_id'] = $_REQUEST['order_id'];
                $shipment_id = isset($shipment['shipment_id']) ? $shipment['shipment_id'] : 0;
                fn_update_shipment($shipment['shipment_data'], $shipment_id, $group_key, true);
            }
        }

        // Add new shipping info
        /*if (!empty($_REQUEST['add_shipping'])) {
            $shipping = db_get_field('SELECT shipping FROM ?:shipping_descriptions WHERE shipping_id = ?i', $_REQUEST['add_shipping']['shipping_id']);
            $shippings[$_REQUEST['add_shipping']['shipping_id']] = array(
                'shipping' => $shipping,
                'tracking_number' => $_REQUEST['add_shipping']['tracking_number'],
                'carrier' => $_REQUEST['add_shipping']['carrier'],
            );

            $_data = array(
                'data' => serialize($shippings),
                'order_id' => $_REQUEST['order_id'],
                'type' => 'L',
            );

            db_query('REPLACE INTO ?:order_data ?e', $_data);
        }*/

        $order_info = fn_get_order_info($_REQUEST['order_id'], false, true, false, true);
        fn_order_notification($order_info, array(), fn_get_notification_rules($_REQUEST));

        if (!empty($_REQUEST['prolongate_data']) && is_array($_REQUEST['prolongate_data'])) {
            foreach ($_REQUEST['prolongate_data'] as $ekey => $v) {
                $newttl = fn_parse_date($v, true);
                db_query('UPDATE ?:product_file_ekeys SET ?u WHERE ekey = ?s', array('ttl' => $newttl), $ekey);
            }
        }

        if (!empty($_REQUEST['activate_files'])) {
            $edp_data = fn_generate_ekeys_for_edp(array(), $order_info, $_REQUEST['activate_files']);
        }

        if (!empty($edp_data)) {
            Mailer::sendMail(array(
                'to' => $order_info['email'],
                'from' => 'company_orders_department',
                'data' => array(
                    'order_info' => $order_info,
                    'edp_data' => $edp_data,
                ),
                'tpl' => 'orders/edp_access.tpl',
                'company_id' => $order_info['company_id'],
            ), 'C', $order_info['lang_code']);
        }

        // Update file downloads section
        if (!empty($_REQUEST['edp_downloads'])) {
            foreach ($_REQUEST['edp_downloads'] as $ekey => $v) {
                foreach ($v as $file_id => $downloads) {
                    $max_downloads = db_get_field("SELECT max_downloads FROM ?:product_files WHERE file_id = ?i", $file_id);
                    if (!empty($max_downloads)) {
                        db_query('UPDATE ?:product_file_ekeys SET ?u WHERE ekey = ?s', array('downloads' => $max_downloads - $downloads), $ekey);
                    }
                }
            }
        }

        $suffix = ".details?order_id=$_REQUEST[order_id]";
    }

    if ($mode == 'bulk_print' && !empty($_REQUEST['order_ids'])) {

        fn_print_order_invoices($_REQUEST['order_ids'], Registry::get('runtime.dispatch_extra') == 'pdf');
        exit;
    }

    if ($mode == 'packing_slip' && !empty($_REQUEST['order_ids'])) {

        fn_print_order_packing_slips($_REQUEST['order_ids'], Registry::get('runtime.dispatch_extra') == 'pdf');
        exit;
    }

    if ($mode == 'remove_cc_info' && !empty($_REQUEST['order_ids'])) {

        fn_set_progress('parts', sizeof($_REQUEST['order_ids']));

        foreach ($_REQUEST['order_ids'] as $v) {
            $payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'P'", $v);
            fn_cleanup_payment_info($v, $payment_info);
        }

        fn_set_notification('N', __('notice'), __('done'));

        if (count($_REQUEST['order_ids']) == 1) {
            $o_id = array_pop($_REQUEST['order_ids']);
            $suffix = ".details?order_id=$o_id";
        } else {
            exit;
        }
    }

    if ($mode == 'export_range') {
        if (!empty($_REQUEST['order_ids'])) {
            if (empty($_SESSION['export_ranges'])) {
                $_SESSION['export_ranges'] = array();
            }

            if (empty($_SESSION['export_ranges']['orders'])) {
                $_SESSION['export_ranges']['orders'] = array('pattern_id' => 'orders');
            }

            $_SESSION['export_ranges']['orders']['data'] = array('order_id' => $_REQUEST['order_ids']);

            unset($_REQUEST['redirect_url']);

            return array(CONTROLLER_STATUS_REDIRECT, "exim.export?section=orders&pattern_id=" . $_SESSION['export_ranges']['orders']['pattern_id']);
        }
    }

    if ($mode == 'products_range') {
        if (!empty($_REQUEST['order_ids'])) {
            unset($_REQUEST['redirect_url']);

            return array(CONTROLLER_STATUS_REDIRECT, "products.manage?order_ids=" . implode(',', $_REQUEST['order_ids']));
        }
    }

    return array(CONTROLLER_STATUS_OK, "orders" . $suffix);
}

$params = $_REQUEST;

if ($mode == 'delete') {
    fn_delete_order($_REQUEST['order_id']);

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'print_invoice') {
    if (!empty($_REQUEST['order_id'])) {
        fn_print_order_invoices($_REQUEST['order_id'], !empty($_REQUEST['format']) && $_REQUEST['format'] == 'pdf');
    }
    exit;

} elseif ($mode == 'print_packing_slip') {
    if (!empty($_REQUEST['order_id'])) {
        fn_print_order_packing_slips($_REQUEST['order_id'], !empty($_REQUEST['format']) && $_REQUEST['format'] == 'pdf');
    }
    exit;

} elseif ($mode == 'details') {
    $_REQUEST['order_id'] = empty($_REQUEST['order_id']) ? 0 : $_REQUEST['order_id'];

    $order_info = fn_get_order_info($_REQUEST['order_id'], false, true, true, true);
    fn_check_first_order($order_info);

    if (empty($order_info)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if (!empty($order_info['is_parent_order']) && $order_info['is_parent_order'] == 'Y') {
        // Get children orders
        $children_order_ids = db_get_fields('SELECT order_id FROM ?:orders WHERE parent_order_id = ?i', $order_info['order_id']);

        return array(CONTROLLER_STATUS_REDIRECT, 'orders.manage?order_id=' . implode(',', $children_order_ids));
    }

    if (isset($order_info['need_shipping']) && $order_info['need_shipping']) {
        $company_id = !empty($order_info['company_id']) ? $order_info['company_id'] : null;

        $shippings = fn_get_available_shippings($company_id);
        Registry::get('view')->assign('shippings', $shippings);
    }

    Registry::set('navigation.tabs', array (
        'general' => array (
            'title' => __('general'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    ));

    if (fn_allowed_for('MULTIVENDOR')) {
        Registry::get('view')->assign('take_surcharge_from_vendor', fn_take_payment_surcharge_from_vendor($order_info['products']));
    }

    foreach ($order_info['products'] as $v) {
        if (!empty($v['extra']['is_edp']) && $v['extra']['is_edp'] == 'Y') {
            Registry::set('navigation.tabs.downloads', array (
                'title' => __('downloads'),
                'js' => true
            ));
            Registry::get('view')->assign('downloads_exist', true);
            break;
        }
    }

    if (!empty($order_info['promotions'])) {
        Registry::set('navigation.tabs.promotions', array (
            'title' => __('promotions'),
            'js' => true
        ));
    }

    list($shipments) = fn_get_shipments_info(array('order_id' => $params['order_id'], 'advanced_info' => true));
    $use_shipments = !fn_one_full_shipped($shipments);

    // Check for the shipment access
    // If current edition is FREE, we still need to check shipments accessibility (need to display promotion link)
    if (Settings::instance()->getValue('use_shipments', '', $order_info['company_id']) == 'Y') {
        if (!fn_check_user_access($auth['user_id'], 'edit_order')) {
            $order_info['need_shipment'] = false;
        }
        $use_shipments = true;
    } else {
        Registry::get('view')->assign('shipments', $shipments);
    }

    Registry::get('view')->assign('use_shipments', $use_shipments);
    Registry::get('view')->assign('carriers', fn_get_carriers());

    Registry::get('view')->assign('order_info', $order_info);
    Registry::get('view')->assign('status_settings', fn_get_status_params($order_info['status']));

    // Delete order_id from new_orders table
    db_query("DELETE FROM ?:new_orders WHERE order_id = ?i AND user_id = ?i", $_REQUEST['order_id'], $auth['user_id']);

    // Check if customer's email is changed
    if (!empty($order_info['user_id'])) {
        $current_email = db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
        if (!empty($current_email) && $current_email != $order_info['email']) {
            Registry::get('view')->assign('email_changed', true);
        }
    }

} elseif ($mode == 'picker') {
    $_REQUEST['skip_view'] = 'Y';

    list($orders, $search) = fn_get_orders($_REQUEST, Registry::get('settings.Appearance.admin_orders_per_page'));
    Registry::get('view')->assign('orders', $orders);
    Registry::get('view')->assign('search', $search);

    Registry::get('view')->display('pickers/orders/picker_contents.tpl');
    exit;

} elseif ($mode == 'update_status') {

    $order_info = fn_get_order_short_info($_REQUEST['id']);
    $old_status = $order_info['status'];
    if (fn_change_order_status($_REQUEST['id'], $_REQUEST['status'], '', fn_get_notification_rules($_REQUEST))) {
        $order_info = fn_get_order_short_info($_REQUEST['id']);
        fn_check_first_order($order_info);
        $new_status = $order_info['status'];
        if ($_REQUEST['status'] != $new_status) {
            Registry::get('ajax')->assign('return_status', $new_status);
            Registry::get('ajax')->assign('color', fn_get_status_param_value($new_status, 'color'));

            fn_set_notification('W', __('warning'), __('status_changed'));
        } else {
            fn_set_notification('N', __('notice'), __('status_changed'));
        }
    } else {
        fn_set_notification('E', __('error'), __('error_status_not_changed'));
        Registry::get('ajax')->assign('return_status', $old_status);
        Registry::get('ajax')->assign('color', fn_get_status_param_value($old_status, 'color'));
    }

    if (empty($_REQUEST['return_url'])) {
        exit;
    } else {
        return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['return_url']);
    }

} elseif ($mode == 'manage') {

    if (!empty($params['status']) && $params['status'] == STATUS_INCOMPLETED_ORDER) {
        $params['include_incompleted'] = true;
    }

    if (fn_allowed_for('MULTIVENDOR')) {
        $params['company_name'] = true;
    }

    list($orders, $search, $totals) = fn_get_orders($params, Registry::get('settings.Appearance.admin_orders_per_page'), true);

    if (!empty($params['include_incompleted']) || !empty($search['include_incompleted'])) {
        Registry::get('view')->assign('incompleted_view', true);
    }

    if (!empty($_REQUEST['redirect_if_one']) && count($orders) == 1) {
        return array(CONTROLLER_STATUS_REDIRECT, "orders.details?order_id={$orders[0]['order_id']}");
    }

    $shippings = fn_get_shippings(true, CART_LANGUAGE);
    if (Registry::get('runtime.company_id')) {
        $company_shippings = fn_get_companies_shipping_ids(Registry::get('runtime.company_id'));
        if (fn_allowed_for('ULTIMATE')) {
            $company_shippings = db_get_fields('SELECT shipping_id FROM ?:shippings');
        }
        foreach ($shippings as $shipping_id => $shipping) {
            if (!in_array($shipping_id, $company_shippings)) {
                unset($shippings[$shipping_id]);
            }
        }
    }

    $remove_cc = db_get_field("SELECT COUNT(*) FROM ?:status_data WHERE type = 'O' AND param = 'remove_cc_info' AND value = 'N'");
    $remove_cc = $remove_cc > 0 ? true : false;
    Registry::get('view')->assign('remove_cc', $remove_cc);

    Registry::get('view')->assign('orders', $orders);
    Registry::get('view')->assign('search', $search);

    Registry::get('view')->assign('totals', $totals);
    Registry::get('view')->assign('display_totals', fn_display_order_totals($orders));
    Registry::get('view')->assign('shippings', $shippings);

    $payments = fn_get_simple_payment_methods(false);
    Registry::get('view')->assign('payments', $payments);

} elseif ($mode == 'get_custom_file') {
    if (!empty($_REQUEST['file']) && !empty($_REQUEST['order_id'])) {
        $file_path = 'order_data/' . $_REQUEST['order_id'] . '/' . $_REQUEST['file'];

        if (Storage::instance('custom_files')->isExist($file_path)) {

            $filename = !empty($_REQUEST['filename']) ? $_REQUEST['filename'] : '';
            Storage::instance('custom_files')->get($file_path, $filename);
        }
    }
}

//
// Calculate gross total and totally paid values for the current set of orders
//
function fn_display_order_totals($orders)
{
    $result = array();
    $result['gross_total'] = 0;
    $result['totally_paid'] = 0;

    if (is_array($orders)) {
        foreach ($orders as $k => $v) {
            $result['gross_total'] += $v['total'];
            if ($v['status'] == 'C' || $v['status'] == 'P') {
                $result['totally_paid'] += $v['total'];
            }
        }
    }

    return $result;
}

function fn_print_order_packing_slips($order_ids, $pdf = false, $lang_code = CART_LANGUAGE)
{
    $view = Registry::get('view');
    $html = array();

    if (!is_array($order_ids)) {
        $order_ids = array($order_ids);
    }

    foreach ($order_ids as $order_id) {
        $order_info = fn_get_order_info($order_id, false, true, false, true);

        if (empty($order_info)) {
            continue;
        }

        $view->assign('order_info', $order_info);

        if ($pdf == true) {
            fn_disable_live_editor_mode();
            $html[] = $view->displayMail('orders/print_packing_slip.tpl', false, 'A', $order_info['company_id'], $lang_code);
        } else {
            $view->displayMail('orders/print_packing_slip.tpl', true, 'A', $order_info['company_id'], $lang_code);
        }

        if ($order_id != end($order_ids)) {
            echo("<div style='page-break-before: always;'>&nbsp;</div>");
        }
    }

    if ($pdf == true) {
        Pdf::render($html, __('packing_slip') . '-' . implode('-', $order_ids));
    }

    return true;
}
