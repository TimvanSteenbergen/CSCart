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
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('ORDER_MANAGEMENT', true); // Defines that the cart is in order management mode now

$_SESSION['cart'] = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
$cart = & $_SESSION['cart'];

$_SESSION['customer_auth'] = isset($_SESSION['customer_auth']) ? $_SESSION['customer_auth'] : array();
$customer_auth = & $_SESSION['customer_auth'];

$_SESSION['shipping_rates'] = isset($_SESSION['shipping_rates']) ? $_SESSION['shipping_rates'] : array();
$shipping_rates = & $_SESSION['shipping_rates'];

if (empty($customer_auth)) {
    $customer_auth = fn_fill_auth(array(), array(), false, 'C');
}

$_suffix = !empty($cart['order_id']) ? 'update' : 'add';

if (fn_allowed_for('ULTIMATE') && $mode != 'edit' && $mode != 'new') {
    if (
        (Registry::get('runtime.company_id') && !empty($cart['order_company_id']) && Registry::get('runtime.company_id') != $cart['order_company_id'])
        || (!Registry::get('runtime.company_id') && !empty($cart['order_company_id']))
    ) {
        if (Registry::get('runtime.company_id')) {
            fn_set_notification('W', __('warning'), __('orders_not_allow_to_change_company'));
        }

        if (fn_get_available_company_ids($cart['order_company_id'])) {
            return array(CONTROLLER_STATUS_REDIRECT, fn_link_attach(Registry::get('config.current_url'), 'switch_company_id=' . $cart['order_company_id']));
        } else {
            return array(CONTROLLER_STATUS_DENIED);
        }

    } elseif (empty($cart['order_company_id']) && Registry::get('runtime.company_id')) {
        $cart['order_company_id'] = Registry::get('runtime.company_id');
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Add product to the cart
    if ($mode == 'add') {
        // Cart is empty, create it
        if (empty($cart)) {
            fn_clear_cart($cart);
        }

        // Remove products with empty amount
        foreach ($_REQUEST['product_data'] as $k => $v) {
            if (empty($v['amount'])) {
                if ($k != 'custom_files') {
                    unset($_REQUEST['product_data'][$k]);
                }
            }
        }

        fn_add_product_to_cart($_REQUEST['product_data'], $_SESSION['cart'], $customer_auth);
        foreach ($cart['products'] as $id => $product) {
            if (!empty($product['extra']['promotions'])) {
                unset($cart['products'][$id]['extra']['promotions']);
            }
        }

        $cart['recalculate_catalog_promotions'] = true;
        fn_calculate_cart_content($cart, $customer_auth);
    }

    // Delete products from the cart
    if ($mode == 'delete') {
        if (!empty($_REQUEST['cart_ids'])) {
            foreach ($_REQUEST['cart_ids'] as $cart_id) {
                fn_delete_cart_product($cart, $cart_id);
            }
        }
    }

    // Select customer
    if ($mode == 'select_customer') {
        if (!empty($_REQUEST['selected_user_id'])) {
            $cart['user_id'] = $_REQUEST['selected_user_id'];
            $u_data = db_get_row("SELECT user_id, tax_exempt, user_type FROM ?:users WHERE user_id = ?i", $cart['user_id']);
            $customer_auth = fn_fill_auth($u_data, array(), false, 'C');
            $cart['user_data'] = array();
        }
    }

    // update products quantity and etc.
    if ($mode == 'update_totals') {
        fn_update_cart_by_data($cart, $_REQUEST, $customer_auth);
    }

    if ($mode == 'customer_info') {

        if (!empty($_REQUEST['profile_id'])) {
            $cart['profile_id'] = $_REQUEST['profile_id'];
        }

        $profile_fields = fn_get_profile_fields('O', $customer_auth);
        // Clean up saved shipping rates
        unset($_SESSION['shipping_rates']);
        if (is_array($_REQUEST['user_data'])) {

            // Fill shipping info with billing if needed
            if (empty($_REQUEST['ship_to_another'])) {
                fn_fill_address($_REQUEST['user_data'], $profile_fields, true);
            }
            // Add descriptions for countries and states
            fn_add_user_data_descriptions($_REQUEST['user_data']);
            $cart['user_data'] = $_REQUEST['user_data'];
            $cart['ship_to_another'] = !empty($_REQUEST['ship_to_another']);

            if (empty($cart['order_id']) && (Registry::get('settings.General.disable_anonymous_checkout') == 'Y' && !empty($_REQUEST['user_data']['password1']))) {
                $cart['profile_registration_attempt'] = true;
                if (fn_update_user(0, $cart['user_data'], $customer_auth, !empty($_REQUEST['ship_to_another']), true) == false) {
                    $action = '';
                }
            }
        }
    }

    if ($mode == 'place_order') {

        // Clean up saved shipping rates
        unset($_SESSION['shipping_rates']);

        // update totals and etc.
        fn_update_cart_by_data($cart, $_REQUEST, $customer_auth);

        if (!empty($_REQUEST['shipping_ids'])) {
            fn_checkout_update_shipping($cart, $_REQUEST['shipping_ids']);
        }

        if (empty($cart['stored_shipping'])) {
            $cart['calculate_shipping'] = true;
        }

        // recalculate cart content after update
        list ($cart_products, $product_groups) = fn_calculate_cart_content($cart, $customer_auth);

        $cart['notes'] = !empty($_REQUEST['customer_notes']) ? $_REQUEST['customer_notes'] : '';
        $cart['payment_info'] = !empty($_REQUEST['payment_info']) ? $_REQUEST['payment_info'] : array();

        list($order_id, $process_payment) = fn_place_order($cart, $customer_auth, $action, $auth['user_id']);

        if (!empty($order_id)) {
            if ($action != 'save') {
                $action = 'route';
            }

            if ($process_payment == true) {
                $payment_info = !empty($cart['payment_info']) ? $cart['payment_info'] : array();
                fn_start_payment($order_id, fn_get_notification_rules($_REQUEST), $payment_info);
            }

            if (!empty($_REQUEST['update_order']['details'])) {
                db_query('UPDATE ?:orders SET details = ?s WHERE order_id = ?i', $_REQUEST['update_order']['details'], $order_id);
            }

            $notification_rules = fn_get_notification_rules($_REQUEST);
            // change status if it posted
            if (!empty($_REQUEST['order_status'])) {
                $order_info = fn_get_order_short_info($order_id);

                if ($order_info['status'] != $_REQUEST['order_status']) {
                    if ($process_payment == true) {
                        fn_set_notification('W', __('warning'), __('status_changed_after_process_payment'));
                    } elseif (fn_change_order_status($order_id, $_REQUEST['order_status'], '', $notification_rules)) {
                        $order_info = fn_get_order_short_info($order_id);
                        $new_status = $order_info['status'];
                        if ($_REQUEST['order_status'] != $new_status) {
                            fn_set_notification('W', __('warning'), __('status_changed'));
                        }
                    } else {
                        $error = false;

                        if ($order_info['is_parent_order'] == 'Y') {
                            $suborders = fn_get_suborders_info($order_id);

                            if ($suborders) {
                                foreach ($suborders as $suborder) {
                                    if ($suborder['status'] != $_REQUEST['order_status']) {
                                        $error = true;
                                        break;
                                    }
                                }
                            } else {
                                $error = true;
                            }
                        } else {
                            $error = true;
                        }

                        if ($error) {
                            fn_set_notification('E', __('error'), __('error_status_not_changed'));
                        }
                    }
                }
            }

            fn_order_placement_routines($action, $order_id, $notification_rules, true);

        } else {
            return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");
        }
    }

    return array(CONTROLLER_STATUS_OK, "order_management.$_suffix");
}

if ($mode == 'customer_info') {
    if (!empty($_REQUEST['profile_id'])) {
        $user_data = fn_get_user_info($customer_auth['user_id'], true, $_REQUEST['profile_id']);
        Registry::get('view')->assign('user_data', $user_data);
    }

    return array(CONTROLLER_STATUS_OK, "order_management.$_suffix");
}

// Delete discount coupon
if ($mode == 'delete_coupon') {
    unset($cart['coupons'][$_REQUEST['c_id']], $cart['pending_coupon']);

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");
}

//
// Edit order
//
if ($mode == 'edit' && !empty($_REQUEST['order_id'])) {

    fn_clear_cart($cart, true);
    $customer_auth = fn_fill_auth(array(), array(), false, 'C');

    $cart_status = md5(serialize($cart));
    fn_form_cart($_REQUEST['order_id'], $cart, $customer_auth);

    if (!empty($cart['product_groups'])) {
        foreach ($cart['product_groups'] as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    if (!empty($shipping['stored_shipping']) && empty($cart['stored_shipping'][$group_key][$shipping_key])) {
                        $cart['stored_shipping'][$group_key][$shipping_key] = $shipping['rate'];
                    }
                }
            }
        }
    }

    if ($cart_status == md5(serialize($cart))) {
        // Order info was not found or customer does not have enought permissions
        return array(CONTROLLER_STATUS_DENIED, '');
    }
    $cart['order_id'] = $_REQUEST['order_id'];

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.update");

//
// Create new order
//
} elseif ($mode == 'new') {

    fn_clear_cart($cart, true);
    $customer_auth = fn_fill_auth(array(), array(), false, 'C');

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.add");
//
// Update order page
//
} elseif ($mode == 'update' || $mode == 'add') {

    if ($mode == 'update' && empty($cart['order_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "order_management.new");
    }

    //
    // Prepare order status info
    //
    $get_additional_statuses = false;
    if (!empty($cart['order_id'])) {
        $order_info = fn_get_order_short_info($cart['order_id']);
        $cart['order_status'] = $order_info['status'];

        if ($cart['order_status'] == STATUS_INCOMPLETED_ORDER) {
            $get_additional_statuses = true;
        }

        if (!empty($order_info['issuer_id'])) {
            $cart['issuer_data'] = fn_get_user_short_info($order_info['issuer_id']);
        }
    }
    $order_statuses = fn_get_simple_statuses(STATUSES_ORDER, $get_additional_statuses, true);
    Registry::get('view')->assign('order_statuses', $order_statuses);

    //
    // Prepare customer info
    //
    $profile_fields = fn_get_profile_fields('O', $customer_auth);

    $cart['profile_id'] = empty($cart['profile_id']) ? 0 : $cart['profile_id'];
    Registry::get('view')->assign('profile_fields', $profile_fields);

    //Get user profiles
    $user_profiles = fn_get_user_profiles($customer_auth['user_id']);
    Registry::get('view')->assign('user_profiles', $user_profiles);

    //Get countries and states
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('usergroups', fn_get_usergroups('C', DESCR_SL));

    if (!empty($customer_auth['user_id']) && (empty($cart['user_data']) || (!empty($_REQUEST['profile_id']) && $cart['profile_id'] != $_REQUEST['profile_id']))) {
        $cart['profile_id'] = !empty($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : 0;
        $cart['user_data'] = fn_get_user_info($customer_auth['user_id'], true, $cart['profile_id']);
        fn_filter_hidden_profile_fields($cart['user_data'], 'O');
    }

    if (!empty($cart['user_data'])) {
        $cart['ship_to_another'] = fn_check_shipping_billing($cart['user_data'], $profile_fields);
    }

    //
    // Get products info
    // and shipping rates
    //

    // Clean up saved shipping rates
    // unset($_SESSION['shipping_rates']);

    if (!empty($shipping_rates)) {
        define('CACHED_SHIPPING_RATES', true);
    }

    $cart['calculate_shipping'] = true;

    // calculate cart - get products with options, full shipping rates info and promotions
    list ($cart_products, $product_groups) = fn_calculate_cart_content($cart, $customer_auth);
    Registry::get('view')->assign('product_groups', $product_groups);

    if (fn_allowed_for('MULTIVENDOR') && !empty($cart['order_id'])) {
        $order_info = fn_get_order_info($cart['order_id']);
        if (isset($order_info['company_id'])) {
            Registry::get('view')->assign('order_company_id', $order_info['company_id']);
        }
    }

    fn_gather_additional_products_data($cart_products, array('get_icon' => false, 'get_detailed' => false, 'get_options' => true, 'get_discounts' => false));

    Registry::get('view')->assign('cart_products', $cart_products);

    //
    //Get payment methods
    //
    $payment_methods = fn_get_payment_methods($customer_auth);

    // Check if payment method has surcharge rates
    foreach ($payment_methods as $k => $v) {
        if (!isset($cart['payment_id'])) {
            $cart['payment_id'] = $v['payment_id'];
        }
        $payment_methods[$k]['surcharge_value'] = 0;
        if (floatval($v['a_surcharge'])) {
            $payment_methods[$k]['surcharge_value'] += $v['a_surcharge'];
        }
        if (floatval($v['p_surcharge'])) {
            $payment_methods[$k]['surcharge_value'] += fn_format_price($cart['total'] * $v['p_surcharge'] / 100);
        }
    }

    fn_update_payment_surcharge($cart, $auth);
    if (!empty($cart['payment_surcharge'])) {
        $payment_methods[$cart['payment_id']]['surcharge_value'] = $cart['payment_surcharge'];
    }

    //Get payment method info
    if (!empty($cart['payment_id'])) {
        $payment_data = fn_get_payment_method_data($cart['payment_id']);
        Registry::get('view')->assign('payment_method', $payment_data);
    }

    Registry::get('view')->assign('payment_methods', $payment_methods);

    //
    // Check if order information is complete
    //
    if (fn_cart_is_empty($cart)) {
        Registry::get('view')->assign('is_empty_cart', true);
    }

    if (empty($cart['user_data']) || !fn_check_profile_fields($cart['user_data'], 'O', $customer_auth)) {
        Registry::get('view')->assign('is_empty_user_data', true);
    }

} elseif ($mode == 'delete' && isset($_REQUEST['cart_id'])) {
    //
    // Delete product from the cart
    //
    fn_delete_cart_product($cart, $_REQUEST['cart_id']);

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");

} elseif ($mode == 'get_custom_file' && isset($_REQUEST['cart_id']) && isset($_REQUEST['option_id']) && isset($_REQUEST['file'])) {
    if (isset($cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']])) {
        $file = $cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']];

        Storage::instance('custom_files')->get($file['path'], $file['name']);
    }

} elseif ($mode == 'delete_file' && isset($_REQUEST['cart_id'])) {

    if (isset($cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']])) {
        // Delete saved custom file
        $file = $cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']];

        Storage::instance('custom_files')->delete($file['path']);
        Storage::instance('custom_files')->delete($file['path'] . '_thumb');

        unset($cart['products'][$_REQUEST['cart_id']]['extra']['custom_files'][$_REQUEST['option_id']][$_REQUEST['file']]);
    }

    fn_save_cart_content($cart, $customer_auth['user_id']);

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");

} elseif ($mode == 'update_payment') {
    //
    // Update payment method
    //
    $cart['payment_id'] = (!empty($_REQUEST['payment_id'])) ? $_REQUEST['payment_id'] : 0;

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");

} elseif ($mode == 'update_shipping' && isset($_REQUEST['shipping_id'])) {
    //
    // Update shipping method
    //
    $supplier_id = !empty($_REQUEST['supplier_id']) ? $_REQUEST['supplier_id'] : 0;
    fn_checkout_update_shipping($cart, array($supplier_id => $_REQUEST['shipping_id']));

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");
}

Registry::get('view')->assign('cart', $cart);

if (!Registry::get('view')->getTemplateVars('user_data') && !empty($cart['user_data'])) {
    Registry::get('view')->assign('user_data', $cart['user_data']);
}

Registry::get('view')->assign('customer_auth', $customer_auth);
