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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'add') {
        if (fn_allowed_for('MULTIVENDOR') && empty($_SESSION['cart']['gift_certificates']) && !empty($_SESSION['cart']['products'])) {
            fn_set_notification('W', 'Warning', __('gift_cert_with_products'));

            return array(CONTROLLER_STATUS_OK, "gift_certificates.add");
        }

        if (!empty($_REQUEST['gift_cert_data']) && is_array($_REQUEST['gift_cert_data'])) {

            $gift_cert_data = $_REQUEST['gift_cert_data'];

            if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
                $gift_cert_data['company_id'] = Registry::get('runtime.company_id');
            }

            // Cart is empty, create it
            if (empty($_SESSION['cart'])) {
                fn_clear_cart($_SESSION['cart']);
            }
            unset($_SESSION['cart']['product_groups']);

            if (!empty($_REQUEST['gift_cert_data']['email']) && !fn_validate_email($_REQUEST['gift_cert_data']['email'], true)) {
                if (defined('AJAX_REQUEST')) {
                    exit();
                } else {
                    return array(CONTROLLER_STATUS_OK, "gift_certificates.add");
                }
            }

            // Gift certificates is empty, create it
            if (empty($_SESSION['cart']['gift_certificates'])) {
                $_SESSION['cart']['gift_certificates'] = array();
            }

            $previous_cart_total = isset($_SESSION['cart']['total']) ? floatval($_SESSION['cart']['total']) : 0;
            list($gift_cert_id, $gift_cert) = fn_add_gift_certificate_to_cart($gift_cert_data, $auth);

            if (!empty($gift_cert_id)) {
                $_SESSION['cart']['gift_certificates'][$gift_cert_id] = $gift_cert;
                $gift_cert['gift_cert_id'] = $gift_cert_id;

                $gift_cert['display_subtotal'] = $gift_cert['amount'];
                fn_calculate_cart_content($_SESSION['cart'], $auth, 'S', true, 'F', true);
                $gift_cert['display_subtotal'] = $_SESSION['cart']['gift_certificates'][$gift_cert_id]['display_subtotal'];

                Registry::get('view')->assign('gift_cert', $gift_cert);
                $msg = Registry::get('view')->fetch('views/checkout/components/product_notification.tpl');
                fn_set_notification('I', __('gift_certificate_added_to_cart'), $msg, 'I');
            }

            fn_save_cart_content($_SESSION['cart'], $auth['user_id']);

            if (defined('AJAX_REQUEST')) {
                fn_calculate_cart_content($_SESSION['cart'], $auth, false, false, 'F', false);
            }
        }
    }

    if ($mode == 'update') {

        if (!empty($_REQUEST['gift_cert_data']) && !empty($_REQUEST['gift_cert_id']) && $_REQUEST['type'] == 'C') {
            fn_delete_cart_gift_certificate($_SESSION['cart'], $_REQUEST['gift_cert_id']);
            unset($_SESSION['cart']['product_groups']);

            list($gift_cert_id, $gift_cert) = fn_add_gift_certificate_to_cart($_REQUEST['gift_cert_data'], $auth);

            if (!empty($gift_cert_id)) {
                $_SESSION['cart']['gift_certificates'][$gift_cert_id] = $gift_cert;
            }

            fn_save_cart_content($_SESSION['cart'], $auth['user_id'], $_REQUEST['type']);
        }
    }

    if ($mode == 'preview') {
        if (!empty($_REQUEST['gift_cert_data'])) {
            fn_correct_gift_certificate($_REQUEST['gift_cert_data']);
            fn_show_postal_card($_REQUEST['gift_cert_data']);
            exit;
        }
    }

    return array(CONTROLLER_STATUS_OK, "checkout.cart");
}

if ($mode == 'verify') {

    fn_add_breadcrumb(__('gift_certificate_verification'));

    $verify_id = db_get_field("SELECT gift_cert_id FROM ?:gift_certificates WHERE gift_cert_code = ?s ?p", $_REQUEST['verify_code'], fn_get_gift_certificate_company_condition('?:gift_certificates.company_id'));
    if (!empty($verify_id)) {
        Registry::set('navigation.tabs', array (
            'detailed' => array (
                'title' => __('detailed_info'),
                'js' => true
            ),
            'log' => array (
                'title' => __('history'),
                'js' => true
            )
        ));

        $params = $_REQUEST;
        $params['gift_cert_id'] = $verify_id;
        list($log, $search) = fn_get_gift_certificate_log($params, Registry::get('settings.Appearance.elements_per_page'));

        Registry::get('view')->assign('log', $log);
        Registry::get('view')->assign('search', $search);

        $verify_data = fn_get_gift_certificate_info($verify_id, 'B');
        if (false != ($last_item = reset($log))) {
            $verify_data['amount'] = $last_item['debit'];
            $verify_data['products'] = $last_item['debit_products'];
        }

        Registry::get('view')->assign('verify_data', $verify_data);
    } else {
        fn_set_notification('W', __('warning'), __('error_gift_cert_code'));

        if (defined('AJAX_REQUEST')) {
            exit();
        }
    }

} elseif ($mode == 'add') {

    fn_add_breadcrumb(__('gift_certificates'));

    Registry::get('view')->assign('templates', fn_get_gift_certificate_templates());
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));

} elseif ($mode == 'update') {

    if (!empty($_REQUEST['gift_cert_id']) && !isset($_SESSION['cart']['gift_certificates'][$_REQUEST['gift_cert_id']])) {
        return array(CONTROLLER_STATUS_REDIRECT, "gift_certificates.add");
    }

    fn_add_breadcrumb(__('gift_certificates'));

    if (!empty($_REQUEST['gift_cert_id'])) {
        $gift_cert_data = fn_get_gift_certificate_info($_REQUEST['gift_cert_id'], 'C');

        if (!empty($gift_cert_data['extra']['exclude_from_calculate'])) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        Registry::get('view')->assign('gift_cert_data', $gift_cert_data);
        Registry::get('view')->assign('gift_cert_id', $_REQUEST['gift_cert_id']);
    }

    Registry::get('view')->assign('templates', fn_get_gift_certificate_templates());
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('type', 'C');

} elseif ($mode == 'delete') {

    if (isset($_REQUEST['gift_cert_id'])) {
        $cart = & $_SESSION['cart'];
        fn_delete_cart_gift_certificate($cart, $_REQUEST['gift_cert_id']);

        if (fn_cart_is_empty($cart) == true) {
            fn_clear_cart($cart);
        }

        fn_save_cart_content($cart, $auth['user_id']);

        $cart['recalculate'] = true;
        fn_calculate_cart_content($cart, $auth, 'A', true, 'F', true);

        return array(CONTROLLER_STATUS_REDIRECT);
    }
}
