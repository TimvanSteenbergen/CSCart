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

if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = $_REQUEST['pass_through'];
    $payment_id = db_get_field("SELECT ?:orders.payment_id FROM ?:orders WHERE ?:orders.order_id = ?i", $order_id);
    $processor_data = fn_get_payment_method_data($payment_id);

    include(Registry::get('config.dir.payments') . 'emerchantpay_files/ParamAuthenticate.inc');

    if ($mode == 'process') {
        $pp_response = array(
            'order_status' => 'P',
            'reason_text' => ''
        );

        if (!empty($_REQUEST['trans_id'])) {
            $pp_response['transaction_id'] = $_REQUEST['trans_id'];
        }

        $authenticatedParam = ParamAuthenticate($processor_data['processor_params']['secret_key'], $_POST);

        if (!$authenticatedParam) {
            $pp_response['reason_text'] = 'Data tampering detected or offer expired';
            $pp_response['order_status'] = 'F';
        } else {
            if (!empty($_REQUEST['response'])) {
                $pp_response['reason_text'] .= ('Response: ' . $_REQUEST['response'] . '; ');
            }
            if (!empty($_REQUEST['responsecode'])) {
                $pp_response['reason_text'] .= ('Response Code: ' . $_REQUEST['responsecode'] . '; ');
            }
            if (!empty($_REQUEST['responsetext'])) {
                $pp_response['reason_text'] .= ('Response Text: ' . $_REQUEST['responsetext'] . '; ');
            }
        }

        $customer_info_fields = fn_get_customer_info_fields();
        $new_order_info = array();
        foreach ($customer_info_fields as $k => $v) {
            if (isset($_REQUEST[$k])) {
                $new_order_info[$v] = $_REQUEST[$k];
            }
        }
        if (!empty($new_order_info)) {
            fn_update_order_customer_info($new_order_info, $order_id);
        }

        if (fn_check_payment_script('emerchantpay.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response);
        }

        echo('OK');
    } elseif ($mode == 'notify') {
        if (fn_check_payment_script('emerchantpay.php', $order_id)) {
            fn_order_placement_routines('route', $order_id, false);
        }
    } elseif ($mode == 'decline') {

        $authenticatedParam = ParamAuthenticate($processor_data['processor_params']['secret_key'], $_GET);

        if (fn_check_payment_script('emerchantpay.php', $order_id) && $authenticatedParam) {
            $pp_response = array(
                'order_status' => 'F',
                'reason_text' => __('text_transaction_declined')
            );

            fn_finish_payment($order_id, $pp_response);
            fn_order_placement_routines('route', $order_id, false);
        }
    }
} else {
    $payment_url =  $processor_data['processor_params']['payment_form_url'] . '/payment/form/post';
    $payment_lang = CART_LANGUAGE;
    $payment_order = ($order_info['repaid']) ? ($order_id . '_'. $order_info['repaid']) : $order_id;
    $payment_currency = $processor_data['processor_params']['currency'];
    $payment_mode = ($processor_data['processor_params']['mode'] == 'TEST') ? 1 : 0;
    $b_payment_state = ($order_info['b_country'] == 'US' || $order_info['b_country'] == 'CA') ? $order_info['b_state'] : $order_info['b_state_descr'];
    $s_payment_state = ($order_info['s_country'] == 'US' || $order_info['s_country'] == 'CA') ? $order_info['s_state'] : $order_info['s_state_descr'];

    include(Registry::get('config.dir.payments') . 'emerchantpay_files/ParamSigner.class');

    $ps = new Paramsigner;
    $ps->setSecret($processor_data['processor_params']['secret_key']);

    $ps->setParam('client_id', $processor_data['processor_params']['client_id']);
    $ps->setParam('form_id', $processor_data['processor_params']['form_id']);
    $ps->setParam('form_language', $payment_lang);
    $ps->setParam('order_reference', $payment_order);
    $ps->setParam('order_currency', $payment_currency);
    $ps->setParam('test_transaction', $payment_mode);
    $ps->setParam('pass_through', $order_id);

    $customer_info_fields = fn_get_customer_info_fields();

    foreach ($customer_info_fields as $param => $field) {
        if (!empty($order_info[$field])) {
            $ps->setParam($param, $order_info[$field]);
        }
    }
    $ps->setParam('customer_state', $b_payment_state);
    $ps->setParam('shipping_state', $s_payment_state);

    $i = 1;
    if (empty($order_info['use_gift_certificates']) && !floatval($order_info['subtotal_discount']) && empty($order_info['points_info']['in_use'])) {
        // Products
        if (!empty($order_info['products'])) {
            foreach ($order_info['products'] as $k => $v) {
                $v['product'] = htmlspecialchars(strip_tags($v['product']));
                $v['price'] = fn_emerchantpay_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount']);
                $p_code = !empty($v['product_code']) ? $v['product_code'] : $v['product_id'];

                fn_emerchantpay_set_product_params($ps, $i++, $p_code, $v['amount'], $v['product'], $payment_currency, $v['price']);
            }
        }

        // Taxes
        if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
            foreach ($order_info['taxes'] as $tax_id => $tax) {
                if ($tax['price_includes_tax'] == 'Y') {
                    continue;
                }
                $item_name = htmlspecialchars(strip_tags($tax['description'])) . str_repeat('0', 5 - strlen($tax['description']));

                $item_price = fn_emerchantpay_format_price($tax['tax_subtotal']);
                $t_code = 'TAX' . $i;

                fn_emerchantpay_set_product_params($ps, $i++, $t_code, 1, $item_name, $payment_currency, $item_price);
            }
        }

        // Gift Certificates
        if (!empty($order_info['gift_certificates'])) {
            foreach ($order_info['gift_certificates'] as $k => $v) {
                $v['gift_cert_code'] = htmlspecialchars($v['gift_cert_code']);
                $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : fn_emerchantpay_format_price($v['amount']);
                $item_name = htmlspecialchars(__('gift_certificate'));

                fn_emerchantpay_set_product_params($ps, $i++, $v['gift_cert_code'], 1, $item_name, $payment_currency, $v['amount']);
            }
        }

        // Payment surcharge
        if (floatval($order_info['payment_surcharge'])) {
            $item_name = htmlspecialchars(__('surcharge'));
            $payment_surcharge_amount = fn_emerchantpay_format_price($order_info['payment_surcharge']);

            fn_emerchantpay_set_product_params($ps, $i++, "PSURCHARGE", 1, $item_name, $payment_currency, $payment_surcharge_amount);
        }

        //Shipping_cost
        if (fn_order_shipping_cost($order_info)) {
            $item_name = htmlspecialchars(__('shipping_cost'));
            $item_price = fn_emerchantpay_format_price(fn_order_shipping_cost($order_info));

            fn_emerchantpay_set_product_params($ps, $i++, "SHIPPING", 1, $item_name, $payment_currency, $item_price);
        }
    } else {
        $total_description = __('total_product_cost');
        $item_price = fn_emerchantpay_format_price($order_info['total']);

        fn_emerchantpay_set_product_params($ps, $i, $total_description, 1, $total_description, $payment_currency, $item_price);
    }

    $requestString=$ps->getQueryString();
    $location = $payment_url . '?' . $requestString;

    fn_echo('<meta http-equiv="Refresh" content="0;URL=' . htmlspecialchars($location) . '" />');
}
exit;

function fn_emerchantpay_format_price($price)
{
    // allows 8 or 8.00 formats only
    return number_format(fn_format_price($price), 2, '.', '');
}

function fn_emerchantpay_set_product_params($ps, $suffix, $p_code, $amount, $p_name, $payment_currency, $p_price)
{
    $ps->setParam("item_{$suffix}_code", $p_code);
    $ps->setParam("item_{$suffix}_qty", $amount);
    $ps->setParam("item_{$suffix}_predefined", 0);
    $ps->setParam("item_{$suffix}_name", $p_name);
    $ps->setParam("item_{$suffix}_unit_price_{$payment_currency}", $p_price);

    return true;
}

function fn_get_customer_info_fields()
{
    return array(
        'customer_first_name' => 'b_firstname',
        'customer_last_name' => 'b_lastname',
        'customer_company' => 'company',
        'shipping_company' => 'company',
        'customer_address' => 'b_address',
        'customer_address2' => 'b_address_2',
        'customer_city' => 'b_city',
        'customer_country' => 'b_country',
        'customer_postcode' => 'b_zipcode',
        'customer_email' => 'email',
        'customer_phone' => 'b_phone',
        'shipping_first_name' => 's_firstname',
        'shipping_last_name' => 's_lastname',
        'shipping_address' => 's_address',
        'shipping_address2' => 's_address_2',
        'shipping_city' => 's_city',
        'shipping_country' => 's_country',
        'shipping_postcode' => 's_zipcode',
        'shipping_phone' => 's_phone'
    );
}
