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

use Tygh\Http;

if (!defined('BOOTSTRAP') && is_array($_POST)) {
    require './init_payment.php';

    $post = array();
    $post['transaction_id'] = $_REQUEST['transaction_id'];
    $post['transaction_date'] = $_REQUEST['transaction_date'];
    $post['from_email'] = $_REQUEST['from_email'];
    $post['to_email'] = $_REQUEST['to_email'];
    $post['order_id'] = $_REQUEST['order_id'];
    $post['amount'] = $_REQUEST['amount'];
    $post['security_key'] = $_REQUEST['security_key'];

    $order_id = (strpos($_REQUEST['order_id'], '_')) ? substr($_REQUEST['order_id'], 0, strpos($_REQUEST['order_id'], '_')) : $_REQUEST['order_id'];
    $order_info = fn_get_order_info($order_id);

    // Post a request and analyse the response
    $return = Http::post("https://www.nochex.com/nochex.dll/apc/apc", $post);
    $result = str_replace("\n","&", $return);

    $order_info['total'] = fn_format_price($order_info['total']);
    $_REQUEST['amount']  = fn_format_price($_REQUEST['amount']);

    $pp_response['order_status'] = ($result == 'AUTHORISED' && $order_info['total'] == $_REQUEST['amount']) ? 'P' : 'F';
    $pp_response["reason_text"] = "SecurityKey: $_REQUEST[security_key], Transaction Date: $_REQUEST[transaction_date]";
    if ($order_info['total'] != $_REQUEST['amount']) {
        $pp_response["reason_text"] .= '; ' . __('order_total_not_correct');
    }
    $pp_response["transaction_id"] = $_REQUEST['transaction_id'];

    fn_finish_payment($order_id, $pp_response);
    exit;

} elseif (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'notify') {
        $order_info = fn_get_order_info($_REQUEST['order_id']);
        if ($order_info['status'] == 'O') {
            $pp_response['order_status'] = 'F';
            fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
        }

        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }

} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $return_url_s = fn_url("payment_notification.notify?payment=nochex&order_id=$order_id", AREA, 'current');
    $return_url_c = fn_url("payment_notification.notify?payment=nochex&order_id=$order_id", AREA, 'current');
    $responder_url = fn_payment_url('current', "nochex.php");
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;

    $post_data = array(
        'merchant_id' => $processor_data['processor_params']['merchantid'],
        'amount' => $order_info['total'],
        'status' => 'test',
        'description' => $processor_data['processor_params']['payment_description'],
        'order_id' => $_order_id,
        'success_url' => $return_url_s,
        'cancel_url' => $return_url_c,
        'callback_url' => $responder_url,
        'billing_fullname' => $order_info['b_firstname'] . ' ' . $order_info['b_lastname'],
        'billing_address' => $order_info['b_address'] . ' ' . $order_info['b_address_2'] . ' ' . $order_info['b_city'] . ' ' . $order_info['b_state'],
        'billing_postcode' => $order_info['b_zipcode'],
        'delivery_fullname' => $order_info['s_firstname'] . ' ' . $order_info['s_lastname'],
        'delivery_address' => $order_info['s_address'] . ' ' . $order_info['s_address_2'] . ' ' . $order_info['s_city'] . ' ' . $order_info['s_state'],
        'delivery_postcode' => $order_info['b_zipcode'],
        'email_address' => $order_info['email'],
        'customer_phone_number' => $order_info['phone'],        
    );

    fn_create_payment_form('https://secure.nochex.com', $post_data, 'Nochex');
}
exit;
