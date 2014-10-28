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

require_once (Registry::get('config.dir.payments') . 'eway/eway_rapidapi.functions.php');

if (defined('PAYMENT_NOTIFICATION')) {

    if ($mode == 'cancel') {

        $order_info = fn_get_order_info($_REQUEST['order_id']);

        if ($order_info['status'] == 'O' || $order_info['status'] == 'I') {
            $pp_response['order_status'] = 'I';
            $pp_response["reason_text"] = __('text_transaction_cancelled');
            fn_finish_payment($order_info['order_id'], $pp_response);
        }

        fn_order_placement_routines('route', $_REQUEST['order_id'], false);

    } else {
        $order_id = (!empty($_REQUEST['order_id'])) ? $_REQUEST['order_id'] : 0;
        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);

        $processor_data = fn_get_payment_method_data($payment_id);
        $access_code = !empty($_REQUEST['AccessCode']) ? $_REQUEST['AccessCode'] : '';
        $response = '';

        if (fn_eway_rapidapi_request('AccessCode/' . $access_code, '', $processor_data, $response, false)) {

            $pp_response['transaction_id'] = $response->TransactionID;
            if (!empty($response->Errors) || !$response->TransactionStatus) {
                $pp_response['order_status'] = 'F';
                $pp_response["reason_text"] = fn_eway_get_response_message($response->Errors, false, $response);
            } else {
                $pp_response['order_status'] = 'P';
                $pp_response["reason_text"] = fn_eway_get_response_message('', false, $response);
            }

            if (fn_check_payment_script('eway_rapidapi_rsp.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response);
                fn_order_placement_routines('route', $order_id, false);
            }
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response["reason_text"] = 'HTTP: ' . $response;
        }
    }

    exit;
}

$request = fn_eway_rapidapi_build_request($order_id, $order_info, $processor_data);

$request = array_merge($request, array(
    'RedirectUrl' => fn_url("payment_notification.notify?payment=eway_rapidapi_rsp&order_id=$order_id", AREA, 'current'),
    'CancelUrl' => fn_url("payment_notification.cancel?payment=eway_rapidapi_rsp&order_id=$order_id", AREA, 'current'),
    'TransactionType' => 'Purchase',
    'Method' => 'ProcessPayment',
    'CustomView' => $processor_data['processor_params']['theme'],
    'HeaderText' => $processor_data['processor_params']['headertext'],
));

$response = '';

if (fn_eway_rapidapi_request('AccessCodesShared', $request, $processor_data, $response)) {
    // Check if any error returns
    if (empty($response->Errors)) {
        $url = explode('?', $response->SharedPaymentUrl);
        $get_data = array(
            'AccessCode' => $response->AccessCode,
        );
        fn_create_payment_form(reset($url), $get_data, 'eWAY payment', true, 'get');
    } else {
        fn_set_notification('E', __('error'), fn_eway_get_response_message($response->Errors));
        fn_order_placement_routines('checkout_redirect');
    }
} else {
    fn_set_notification('E', __('error'), 'HTTP: ' . $response);
    fn_order_placement_routines('checkout_redirect');
}
