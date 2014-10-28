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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

    if (!empty($_REQUEST['key'])) {
        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
        $processor_data = fn_get_payment_method_data($payment_id);
        $order_info = fn_get_order_info($_REQUEST['order_id']);

        $order_number_id = ($processor_data['processor_params']['mode'] == 'test') ? '1' : $_REQUEST['order_number'];

        $pp_response = array();
        if ((strtoupper(md5($processor_data['processor_params']['secret_word'] . $processor_data['processor_params']['account_number'] . $order_number_id . $order_info['total'])) == $_REQUEST['key']) && ($_REQUEST['credit_card_processed'] == 'Y')) {
            $pp_response['order_status'] = ($processor_data['processor_params']['fraud_verification'] == 'Y') ? $processor_data['processor_params']['fraud_wait'] : 'P';
            $pp_response['reason_text'] = __('order_id') . '-' . $_REQUEST['order_number'];

        } else {
            $pp_response['order_status'] = ($_REQUEST['credit_card_processed'] == 'K') ? 'O' : 'F';
            $pp_response['reason_text'] = ($_REQUEST['credit_card_processed'] == 'Y') ? "MD5 Hash is invalid" : __('order_id') . '-' . $_REQUEST['order_number'];
        }

        $pp_response['transaction_id'] = (!empty($_REQUEST['tcoid'])) ? $_REQUEST['tcoid'] : '';

        if (fn_check_payment_script('2checkout.php', $_REQUEST['order_id'])) {
            if ($processor_data['processor_params']['fraud_verification'] == 'Y') {
                fn_update_order_payment_info($_REQUEST['order_id'], $pp_response);
                fn_change_order_status($_REQUEST['order_id'], $pp_response['order_status'], '', false);
            } else {
                fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
            }
            fn_order_placement_routines('route', $_REQUEST['order_id']);
        }

    // Fraud checking notification
    } elseif (!empty($_REQUEST['message_type']) && $_REQUEST['message_type'] == 'FRAUD_STATUS_CHANGED') {
        if (!empty($_REQUEST['vendor_order_id'])) {
            list($order_id) = explode('_', $_REQUEST['vendor_order_id']);
            if (!empty($order_id)) {

                $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
                $processor_data = fn_get_payment_method_data($payment_id);

                $pp_response = array();
                if ($_REQUEST['fraud_status'] == 'pass') {
                    $pp_response['order_status'] = 'P';
                } elseif ($_REQUEST['fraud_status'] == 'fail') {
                    $pp_response['order_status'] = $processor_data['processor_params']['fraud_fail'];
                }

                if (!empty($pp_response) && fn_check_payment_script('2checkout.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                }
            }
        }
    }
    exit;

} else {
    $__bstate = $order_info['b_state'];
    if ($order_info['b_country'] != 'US' && $order_info['b_country'] != 'CA') {
        $__bstate = "XX";
    }
    $__sstate = @$order_info['s_state'];
    if ($order_info['s_country'] != 'US' && $order_info['s_country'] != 'CA') {
        $__sstate = "XX";
    }
    $is_test = ($processor_data['processor_params']['mode'] == 'test') ? 'Y' : 'N';
    $cart_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;
    $sh_cost = fn_order_shipping_cost($order_info);

    $form_data = array(
        'sid' => $processor_data['processor_params']['account_number'],
        'total' => $order_info['total'],
        'merchant_order_id' => $cart_order_id,
        'cart_order_id' => $cart_order_id,
        'card_holder_name' => $order_info['b_firstname'] . ' ' . $order_info['b_lastname'],
        'street_address' => $order_info['b_address'],
        'city' => $order_info['b_city'],
        'state' => $__bstate,
        'zip' => $order_info['b_zipcode'],
        'country' => $order_info['b_country'],
        'email' => $order_info['email'],
        'phone' => $order_info['phone'],
        'ship_name' => $order_info['s_firstname'] . ' ' . $order_info['s_lastname'],
        'ship_street_address' => $order_info['s_address'],
        'ship_city' => $order_info['s_city'],
        'ship_state' => $__sstate,
        'ship_zip' => $order_info['s_zipcode'],
        'ship_country' => $order_info['s_country'],
        'fixed' => 'Y',
        'id_type' => '1',
        'sh_cost' => $sh_cost,
        'demo' => $is_test,
        'dispatch' => 'payment_notification',
        'payment' => '2checkout',
        'order_id' => $order_id        
    );

    // Products
    $it = 0;
    if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $k => $v) {
            $it++;
            $is_tangible = (!empty($v['extra']['is_edp']) && $v['extra']['is_edp'] == 'Y') ? 'N' : 'Y';
            $price = fn_format_price($v['price'] - (fn_external_discounts($v) / $v['amount']));
            $suffix = "_$it";
            
            $form_data["c_prod{$suffix}"] = $v['product_id'] . ',' . $v['amount'];
            $form_data["c_name{$suffix}"] = $v['product'];
            $form_data["c_description{$suffix}"] = $v['product'];
            $form_data["c_price{$suffix}"] = $price;
            $form_data["c_tangible{$suffix}"] = $is_tangible;
        }
    }

    // Certificates
    if (!empty($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $k => $v) {
            $it++;
            $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
            $suffix = "_$it";

            $form_data["c_prod{$suffix}"] = $v['gift_cert_id'] . ',1';
            $form_data["c_name{$suffix}"] = $v['gift_cert_code'];
            $form_data["c_description{$suffix}"] = $v['gift_cert_code'];
            $form_data["c_price{$suffix}"] = $v['amount'];
            $form_data["c_tangible{$suffix}"] = 'N';
        }
    }

    fn_create_payment_form('https://www.2checkout.com/2co/buyer/purchase', $form_data, '2Checkout', false);
}
exit;
