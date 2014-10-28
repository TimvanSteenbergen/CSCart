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
use Tygh\Mailer;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (!empty($_REQUEST['order_id']) && $mode != 'search') {
    // If user is not logged in and trying to see the order, redirect him to login form
    if (empty($auth['user_id']) && empty($auth['order_ids'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=" . urlencode(Registry::get('config.current_url')));
    }

    $orders_company_condition = '';
    if (fn_allowed_for('ULTIMATE')) {
        $orders_company_condition = fn_get_company_condition('?:orders.company_id');
    }

    if (!empty($auth['user_id'])) {
        $allowed_id = db_get_field("SELECT user_id FROM ?:orders WHERE user_id = ?i AND order_id = ?i $orders_company_condition", $auth['user_id'], $_REQUEST['order_id']);

    } elseif (!empty($auth['order_ids'])) {
        $allowed_id = in_array($_REQUEST['order_id'], $auth['order_ids']);
    }

    // Check order status (incompleted order)
    if (!empty($allowed_id)) {
        $status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i $orders_company_condition", $_REQUEST['order_id']);
        if ($status == STATUS_INCOMPLETED_ORDER) {
            $allowed_id = 0;
        }
    }

    fn_set_hook('is_order_allowed', $_REQUEST['order_id'], $allowed_id);

    if (empty($allowed_id)) { // Access denied

        return array(CONTROLLER_STATUS_DENIED);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'repay') {
        $order_info = fn_get_order_info($_REQUEST['order_id']);

        $payment_info = empty($_REQUEST['payment_info']) ? array() : $_REQUEST['payment_info'];

        // Save payment information
        if (!empty($payment_info)) {

            // This should not be here, repay must be refactored to use fn_place_order
            if (!empty($payment_info['card_number'])) {
                $payment_info['card_number'] = str_replace(array(' ', '-'), '', $payment_info['card_number']);
            }
            $_data = array (
                'order_id' => $_REQUEST['order_id'],
                'type' => 'P', //payment information
                'data' => fn_encrypt_text(serialize($payment_info)),
            );

            db_query("REPLACE INTO ?:order_data ?e", $_data);
        } else {
            db_query("DELETE FROM ?:order_data WHERE type = 'P' AND order_id = ?i", $_REQUEST['order_id']);
        }

        // Change payment method
        $update_order['payment_id'] = $_REQUEST['payment_id'];
        $update_order['repaid'] = ++ $order_info['repaid'];

        // Add new customer notes
        if (!empty($_REQUEST['customer_notes'])) {
            $update_order['notes'] = (!empty($order_info['notes']) ? $order_info['notes'] . "\n" : '') . $_REQUEST['customer_notes'];
        }

        // Update total and surcharge amount
        $payment = fn_get_payment_method_data($_REQUEST['payment_id']);
        if (!empty($payment['p_surcharge']) || !empty($payment['a_surcharge'])) {
            $surcharge_value = 0;
            if (floatval($payment['a_surcharge'])) {
                $surcharge_value += $payment['a_surcharge'];
            }
            if (floatval($payment['p_surcharge'])) {
                $surcharge_value += fn_format_price(($order_info['total'] - $order_info['payment_surcharge']) * $payment['p_surcharge'] / 100);
            }
            $update_order['payment_surcharge'] = $surcharge_value;
            if (fn_allowed_for('MULTIVENDOR') && fn_take_payment_surcharge_from_vendor($order_info['products'])) {
                $update_order['total'] = fn_format_price($order_info['total']);
            } else {
                $update_order['total'] = fn_format_price($order_info['total'] - $order_info['payment_surcharge'] + $surcharge_value);
            }
        } else {
            if (fn_allowed_for('MULTIVENDOR') && fn_take_payment_surcharge_from_vendor($order_info['products'])) {
                $update_order['total'] = fn_format_price($order_info['total']);
            } else {
                $update_order['total'] = fn_format_price($order_info['total'] - $order_info['payment_surcharge']);
            }
            $update_order['payment_surcharge'] = 0;
        }

        fn_set_hook('repay_order', $order_info, $update_order, $payment, $payment_info);

        db_query('UPDATE ?:orders SET ?u WHERE order_id = ?i', $update_order, $_REQUEST['order_id']);

        // Change order status back to Open and restore amount.
        fn_change_order_status($order_info['order_id'], STATUSES_ORDER, $order_info['status'], fn_get_notification_rules(array(), false));

        $_SESSION['cart']['placement_action'] = 'repay';

        // Process order (payment)
        fn_start_payment($order_info['order_id'], array(), $payment_info);

        fn_order_placement_routines('repay', $order_info['order_id'], array(), true);
    }

    return array(CONTROLLER_STATUS_OK, "orders.details?order_id=$_REQUEST[order_id]");
}

fn_add_breadcrumb(__('orders'), $mode == 'search' ? '' : "orders.search");

//
// Show invoice
//
if ($mode == 'invoice') {
    fn_add_breadcrumb(__('order') . ' #' . $_REQUEST['order_id'], "orders.details?order_id=$_REQUEST[order_id]");
    fn_add_breadcrumb(__('invoice'));

    Registry::get('view')->assign('order_info', fn_get_order_info($_REQUEST['order_id']));

//
// Show invoice on separate page
//
} elseif ($mode == 'print_invoice') {

    if (!empty($_REQUEST['order_id'])) {
        fn_print_order_invoices($_REQUEST['order_id'], !empty($_REQUEST['format']) && $_REQUEST['format'] == 'pdf');
    }
    exit;

//
// Track orders by ekey
//
} elseif ($mode == 'track') {
    if (!empty($_REQUEST['ekey'])) {
        $email = fn_get_object_by_ekey($_REQUEST['ekey'], 'T');

        if (empty($email)) {
            return array(CONTROLLER_STATUS_DENIED);
        }

        $auth['order_ids'] = db_get_fields("SELECT order_id FROM ?:orders WHERE email = ?s", $email);

        if (!empty($_REQUEST['o_id']) && in_array($_REQUEST['o_id'], $auth['order_ids'])) {
            return array(CONTROLLER_STATUS_REDIRECT, "orders.details?order_id=$_REQUEST[o_id]");
        } else {
            return array(CONTROLLER_STATUS_REDIRECT, "orders.search");
        }
    } else {
        return array(CONTROLLER_STATUS_DENIED);
    }

    exit;

//
// Request for order tracking
//
} elseif ($mode == 'track_request') {

    if (fn_image_verification('use_for_track_orders', $_REQUEST) == false) {
        exit;
    }

    $condition = fn_get_company_condition('?:orders.company_id');

    if (!empty($auth['user_id'])) {

        $allowed_id = db_get_field(
            'SELECT user_id '
            . 'FROM ?:orders '
            . 'WHERE user_id = ?i AND order_id = ?i AND is_parent_order != ?s' . $condition,
            $auth['user_id'], $_REQUEST['track_data'], 'Y'
        );

        if (!empty($allowed_id)) {
            Registry::get('ajax')->assign('force_redirection', fn_url('orders.details?order_id=' . $_REQUEST['track_data']));
            exit;
        } else {
            fn_set_notification('E', __('error'), __('warning_track_orders_not_allowed'));
        }
    } else {
        $email = '';

        if (!empty($_REQUEST['track_data'])) {
            $o_id = 0;
            // If track by email
            if (strpos($_REQUEST['track_data'], '@') !== false) {
                $order_info = db_get_row("SELECT order_id, email, company_id, lang_code FROM ?:orders WHERE email = ?s $condition ORDER BY timestamp DESC LIMIT 1", $_REQUEST['track_data']);
            // Assume that this is order number
            } else {
                $order_info = db_get_row("SELECT order_id, email, company_id, lang_code FROM ?:orders WHERE order_id = ?i $condition", $_REQUEST['track_data']);
            }
        }

        if (!empty($order_info['email'])) {
            // Create access key
            $ekey = fn_generate_ekey($order_info['email'], 'T', SECONDS_IN_HOUR);

            $company_id = fn_get_company_id('orders', 'order_id', $order_info['order_id']);

            $result = Mailer::sendMail(array(
                'to' => $order_info['email'],
                'from' => 'company_orders_department',
                'data' => array(
                    'access_key' => $ekey,
                    'o_id' => $order_info['order_id'],
                ),
                'tpl' => 'orders/track.tpl',
                'company_id' => $company_id,
            ), 'C', $order_info['lang_code']);

            if ($result) {
                fn_set_notification('N', __('notice'), __('text_track_instructions_sent'));
            }
        } else {
            fn_set_notification('E', __('error'), __('warning_track_orders_not_found'));
        }
    }

    return array(CONTROLLER_STATUS_OK, $_REQUEST['return_url']);
//
// Show order details
//
} elseif ($mode == 'details') {

    fn_add_breadcrumb(__('order_info'));

    $order_info = fn_get_order_info($_REQUEST['order_id']);

    if (empty($order_info)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if ($order_info['is_parent_order'] == 'Y') {
        $child_ids = db_get_fields("SELECT order_id FROM ?:orders WHERE parent_order_id = ?i", $_REQUEST['order_id']);

        return array(CONTROLLER_STATUS_REDIRECT, "orders.search?period=A&order_id=" . implode(',', $child_ids));
    }

    if (fn_allowed_for('MULTIVENDOR')) {
        Registry::get('view')->assign('take_surcharge_from_vendor', fn_take_payment_surcharge_from_vendor($order_info['products']));
    }
    // Repay functionality
    $statuses = fn_get_statuses(STATUSES_ORDER, array(), true);

    if (Registry::get('settings.General.repay') == 'Y' && (!empty($statuses[$order_info['status']]['params']['repay']) && $statuses[$order_info['status']]['params']['repay'] == 'Y')) {
        fn_prepare_repay_data(empty($_REQUEST['payment_id']) ? 0 : $_REQUEST['payment_id'], $order_info, $auth);
    }

    $navigation_tabs = array(
        'general' => array(
            'title' => __('general'),
            'js' => true,
            'href' => 'orders.details?order_id=' . $_REQUEST['order_id'] . '&selected_section=general'
        ),
    );

    list($shipments) = fn_get_shipments_info(array('order_id' => $order_info['order_id'], 'advanced_info' => true));
    $use_shipments = !fn_one_full_shipped($shipments);

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (Registry::get('settings.General.use_shipments') == 'Y' || $use_shipments) {
            $navigation_tabs['shipment_info'] = array(
                'title' => __('shipment_info'),
                'js' => true,
                'href' => 'orders.details?order_id=' . $_REQUEST['order_id'] . '&selected_section=shipment_info'
            );
            $use_shipments = true;
        }
    }

    Registry::get('view')->assign('shipments', $shipments);
    Registry::get('view')->assign('use_shipments', $use_shipments);

    Registry::set('navigation.tabs', $navigation_tabs);
    Registry::get('view')->assign('order_info', $order_info);
    Registry::get('view')->assign('status_settings', $statuses[$order_info['status']]['params']);

    if (!empty($_REQUEST['selected_section'])) {
        Registry::get('view')->assign('selected_section', $_REQUEST['selected_section']);
    }

    if (!empty($_REQUEST['active_tab'])) {
        Registry::get('view')->assign('active_tab', $_REQUEST['active_tab']);
    }

//
// Search orders
//
} elseif ($mode == 'search') {

    $params = $_REQUEST;
    if (!empty($auth['user_id'])) {
        $params['user_id'] = $auth['user_id'];

    } elseif (!empty($auth['order_ids'])) {
        if (empty($params['order_id'])) {
            $params['order_id'] = $auth['order_ids'];
        } else {
            $ord_ids = is_array($params['order_id']) ? $params['order_id'] : explode(',', $params['order_id']);
            $params['order_id'] = array_intersect($ord_ids, $auth['order_ids']);
        }

    } else {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=" . urlencode(Registry::get('config.current_url')));
    }

    list($orders, $search) = fn_get_orders($params, Registry::get('settings.Appearance.orders_per_page'));

    Registry::get('view')->assign('orders', $orders);
    Registry::get('view')->assign('search', $search);

//
// Reorder order
//
} elseif ($mode == 'reorder') {

    fn_reorder($_REQUEST['order_id'], $_SESSION['cart'], $auth);

    return array(CONTROLLER_STATUS_REDIRECT, "checkout.cart");

} elseif ($mode == 'downloads') {

    if (empty($auth['user_id']) && empty($auth['order_ids'])) {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url());
    }

    fn_add_breadcrumb(__('downloads'));

    $params = $_REQUEST;
    $params['user_id'] = $auth['user_id'];
    $params['order_ids'] = !empty($auth['order_ids']) ? $auth['order_ids'] : array();

    list($products, $search) = fn_get_user_edp($params, Registry::get('settings.Appearance.elements_per_page'));

    Registry::get('view')->assign('products', $products);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'order_downloads') {

    if (empty($auth['user_id']) && empty($auth['order_ids'])) {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url());
    }

    if (!empty($_REQUEST['order_id'])) {
        if (empty($auth['user_id']) && !in_array($_REQUEST['order_id'], $auth['order_ids'])) {
            return array(CONTROLLER_STATUS_DENIED);
        }
        $orders_company_condition = '';
        if (fn_allowed_for('ULTIMATE')) {
            $orders_company_condition = fn_get_company_condition('?:orders.company_id');
        }

        $order = db_get_row("SELECT user_id, order_id FROM ?:orders WHERE ?:orders.order_id = ?i AND is_parent_order != 'Y' $orders_company_condition", $_REQUEST['order_id']);

        if (empty($order)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        fn_add_breadcrumb(__('order') . ' #' . $_REQUEST['order_id'], "orders.details?order_id=" . $_REQUEST['order_id']);
        fn_add_breadcrumb(__('downloads'));

        $params = array(
            'user_id' => $order['user_id'],
            'order_ids' => $order['order_id']
        );
        list($products) = fn_get_user_edp($params);

        Registry::get('view')->assign('products', $products);
    } else {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

} elseif ($mode == 'get_file') {

    if (empty($_REQUEST['file_id']) || (empty($_REQUEST['ekey']) && empty($_REQUEST['preview']))) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if (fn_get_product_file($_REQUEST['file_id'], !empty($_REQUEST['preview']), $_REQUEST['ekey']) == false) {
        return array(CONTROLLER_STATUS_DENIED);
    }
    exit;

//
// Display list of files for downloadable product
//
} elseif ($mode == 'download') {
    if (!empty($_REQUEST['ekey'])) {

        $ekey_info = fn_get_product_edp_info($_REQUEST['product_id'], $_REQUEST['ekey']);

        if (empty($ekey_info)) {
            return array(CONTROLLER_STATUS_DENIED);
        }

        $product = array(
            'ekey' => $_REQUEST['ekey'],
            'product_id' => $ekey_info['product_id'],
        );

        if (!empty($product['product_id'])) {
            $product['product'] = db_get_field("SELECT product FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $product['product_id'], CART_LANGUAGE);
            $params = array (
                'product_id' => $product['product_id'],
                'order_id' => $ekey_info['order_id']
            );
            $product['files'] = fn_get_product_files($params);
        }
    }

    if (!empty($auth['user_id'])) {
        fn_add_breadcrumb(__('downloads'), "profiles.downloads");
    }

    fn_add_breadcrumb($product['product'], "products.view?product_id=$product[product_id]");
    fn_add_breadcrumb(__('download'));

    if (!empty($product['files'])) {
        Registry::get('view')->assign('product', $product);
    } else {
        return array(CONTROLLER_STATUS_DENIED);
    }

} elseif ($mode == 'get_custom_file') {
    $filename = !empty($_REQUEST['filename']) ? $_REQUEST['filename'] : '';

    if (!empty($_REQUEST['file'])) {
        if (!empty($_REQUEST['order_id'])) {
            $file_path = 'order_data/' . $_REQUEST['order_id'] . '/' . fn_basename($_REQUEST['file']);
        } else {
            $file_path = 'sess_data/' . fn_basename($_REQUEST['file']);
        }

        if (Storage::instance('custom_files')->isExist($file_path)) {
            Storage::instance('custom_files')->get($file_path, $filename);
        }
    }
}

function fn_reorder($order_id, &$cart, &$auth)
{
    $order_info = fn_get_order_info($order_id, false, false, false, true);
    unset($_SESSION['shipping_hash']);
    unset($_SESSION['edit_step']);

    fn_set_hook('reorder', $order_info, $cart, $auth);

    foreach ($order_info['products'] as $k => $item) {
        // refresh company id
        $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $item['product_id']);
        $order_info['products'][$k]['company_id'] = $company_id;

        unset($order_info['products'][$k]['extra']['ekey_info']);
        $order_info['products'][$k]['product_options'] = empty($order_info['products'][$k]['extra']['product_options']) ? array() : $order_info['products'][$k]['extra']['product_options'];
        $order_info['products'][$k]['main_pair'] = fn_get_cart_product_icon($item['product_id'], $order_info['products'][$k]);
    }

    if (!empty($cart) && !empty($cart['products'])) {
        $cart['products'] = fn_array_merge($cart['products'], $order_info['products']);
    } else {
        $cart['products'] = $order_info['products'];
    }

    foreach ($cart['products'] as $k => $v) {
        $_is_edp = db_get_field("SELECT is_edp FROM ?:products WHERE product_id = ?i", $v['product_id']);
        if ($amount = fn_check_amount_in_stock($v['product_id'], $v['amount'], $v['product_options'], $k, $_is_edp, 0, $cart)) {
            $cart['products'][$k]['amount'] = $amount;

            // Change the path of custom files
            if (!empty($v['extra']['custom_files'])) {
                foreach ($v['extra']['custom_files'] as $option_id => $_data) {
                    if (!empty($_data)) {
                        foreach ($_data as $file_id => $file) {
                            $cart['products'][$k]['extra']['custom_files'][$option_id][$file_id]['path'] = 'sess_data/' . fn_basename($file['path']);
                        }
                    }
                }
            }
        } else {
            unset($cart['products'][$k]);
        }
    }

    // Restore custom files for editing
    $dir_path = 'order_data/' . $order_id;

    if (Storage::instance('custom_files')->isExist($dir_path)) {
        Storage::instance('custom_files')->copy($dir_path, 'sess_data');
    }

    // Redirect customer to step three after reordering
    $cart['payment_updated'] = true;

    fn_save_cart_content($cart, $auth['user_id']);
    unset($cart['product_groups']);
}

function fn_prepare_repay_data($payment_id, $order_info, $auth)
{
    if (empty($payment_id)) {
        $payment_id = $order_info['payment_id'];
    }

    //Get payment methods
    $payment_methods = fn_get_payment_methods($auth);

    fn_set_hook('prepare_repay_data', $payment_id, $order_info, $auth, $payment_methods);

    if (!empty($payment_methods)) {
        // Get payment method info
        $payment_groups = fn_prepare_checkout_payment_methods($order_info, $auth);
        if (!empty($payment_id)) {
            $order_payment_id = $payment_id;
        } else {
            $first = reset($payment_methods);
            $order_payment_id = $first['payment_id'];
        }

        $payment_data = fn_get_payment_method_data($order_payment_id);
        $payment_data['surcharge_value'] = 0;

        if (floatval($payment_data['a_surcharge'])) {
            $payment_data['surcharge_value'] += $payment_data['a_surcharge'];
        }

        if (floatval($payment_data['p_surcharge'])) {
            if (fn_allowed_for('MULTIVENDOR') && fn_take_payment_surcharge_from_vendor($order_info['products'])) {
                $payment_data['surcharge_value'] += fn_format_price($order_info['total']);
            } else {
                $payment_data['surcharge_value'] += fn_format_price(($order_info['total'] - $order_info['payment_surcharge']) * $payment_data['p_surcharge'] / 100);
            }
        }

        Registry::get('view')->assign('payment_methods', $payment_groups);
        Registry::get('view')->assign('order_payment_id', $order_payment_id);
        Registry::get('view')->assign('payment_method', $payment_data);
    }
}
