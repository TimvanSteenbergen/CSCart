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

if (!defined('BOOTSTRAP')) {
    if (!empty($_REQUEST['cs1'])) {
        // Settle data is received
        require './init_payment.php';
        $order_id = (int) $_REQUEST['cs1'];
        $order_info = fn_get_order_info($order_id);
        $processor_data = $order_info['payment_method'];
        $inner_sign = md5($processor_data['processor_params']['sharedsec'] . $_REQUEST['customer_id'] . $_REQUEST['transaction_id'] . $_REQUEST['transaction_type'] . $order_info['total']);
        if ($_REQUEST['sign'] == $inner_sign) {
            echo "\nOK";
            if (isset($order_info['payment_info']['awaiting_callback']) && $order_info['payment_info']['awaiting_callback'] == true) {
                $pp_response['order_status'] = 'P';
                $pp_response["reason_text"] = 'Approved; Customer ID: ' . $_REQUEST['customer_id'];
                $pp_response["transaction_id"] = $_REQUEST['transaction_id'];

                if (fn_check_payment_script('chronopay_form.php', $order_id)) {
                    fn_finish_payment($order_id, $pp_response);
                    fn_update_order_payment_info($order_id, array('awaiting_callback' => false));
                }
            }
        }
        exit;
    } else {
        die('Access denied');
    }
}

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'notify') {
        $order_id = (int) $_REQUEST['order_id'];
        $order_info = fn_get_order_info($order_id);
        $processor_data = $order_info['payment_method'];
        // We are trying to avoid mess with declined and success urls
        $sign = md5($processor_data['processor_params']['product_id'] . '-' . $order_info['total'] . '-' . $processor_data['processor_params']['sharedsec']);
        // Because the callback comes only after return we have to make sure that this redirect is successful
        if (in_array($order_info['status'], array('N', 'D')) || empty($_REQUEST['sign']) || $sign != $_REQUEST['sign']) {
            $pp_response['order_status'] = 'D';
            $pp_response["reason_text"] = __('text_transaction_declined');
            fn_finish_payment($order_id, $pp_response, false);
        } else {
            // Set open status until callback from chronopay service is recieved
            if (fn_check_payment_script('chronopay_form.php', $order_id)) {
                if (isset($order_info['payment_info']['awaiting_callback']) && $order_info['payment_info']['awaiting_callback'] == true) {
                    fn_change_order_status($order_id, 'O', $order_info['status'], false);
                }
            }
        }

        fn_order_placement_routines('route', $order_id);
    }

} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $post_url = fn_payment_url('current', 'chronopay_form.php');
    $return_url = fn_url("payment_notification.notify?payment=chronopay_form&order_id=$order_id", AREA, 'current');

    $country = db_get_field("SELECT code_A3 FROM ?:countries WHERE code = ?s", $order_info['b_country']);

    $product_name = "";
    // Products
    if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $v) {
            $product_name = $product_name . str_replace(', ', ' ', $v['product']) . ",<br>\n  ";
        }
    }
    // Certificates
    if (!empty($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $v) {
            $product_name = $product_name . str_replace(', ', ' ', $v['gift_cert_code']) . ",<br>\n  ";
        }
    }
    // Shippings
    if (floatval($order_info['shipping_cost'])) {
        foreach ($order_info['shipping'] as $v) {
            $product_name .= str_replace(', ', ' ', $v['shipping']) . ",<br>\n  ";
        }
    }
    $sign = md5($processor_data['processor_params']['product_id'] . '-' . $order_info['total'] . '-' . $processor_data['processor_params']['sharedsec']);

    fn_update_order_payment_info($order_id, array('awaiting_callback' => true));

    $post_data = array(
        'product_id' => $processor_data['processor_params']['product_id'],
        'product_name' => $product_name,
        'product_price' => $order_info['total'],
        'order_id' => $order_id,
        'cs1' => $order_id,
        'language' => CART_LANGUAGE,
        'f_name' => $order_info['b_firstname'],
        's_name' => $order_info['b_lastname'],
        'street' => $order_info['b_address'],
        'city' => $order_info['b_city'],
        'state' => $order_info['b_state'],
        'zip' => $order_info['b_zipcode'],
        'country' => $country,
        'phone' => $order_info['phone'],
        'email' => $order_info['email'],
        'cb_url' => $post_url,
        'cb_type' => 'P',
        'success_url' => fn_link_attach($return_url, "sign={$sign}"),
        'decline_url' => $return_url,
        'sign' => $sign    
    );

    fn_create_payment_form('https://payments.chronopay.com', $post_data, 'ChronoPay');
}
exit;
