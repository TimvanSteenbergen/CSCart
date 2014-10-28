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

$request_eway = fn_eway_rapidapi_build_request($order_id, $order_info, $processor_data);

$request_eway['TransactionType'] = 'Purchase';
$request_eway['Customer']['CardDetails'] = array(
    'Name' => $order_info['payment_info']['cardholder_name'],
    'Number' => $order_info['payment_info']['card_number'],
    'ExpiryMonth' => $order_info['payment_info']['expiry_month'],
    'ExpiryYear' => $order_info['payment_info']['expiry_year'],
    'CVN' => $order_info['payment_info']['cvv2']
);

$response = '';

if (fn_eway_rapidapi_request('Transaction', $request_eway, $processor_data, $response)) {

    // Check if any error returns
    if (isset($response->Errors)) {
        fn_set_notification('E', __('error'), fn_eway_get_response_message($response->Errors));
        fn_order_placement_routines('checkout_redirect');
    }

    $successfully_code = array(0, 8, 10, 11, 16);

    if (in_array($response->ResponseCode, $successfully_code)) {
        $pp_response['order_status'] = 'P';
        $pp_response["transaction_id"] = $response->TransactionID;
        $pp_response["reason_text"] = fn_eway_get_response_message($response->ResponseMessage, false, $response);
    } else {
        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = fn_eway_get_response_message($response->ResponseMessage);
    }

    if ($processor_data['processor_params']['mode'] == 'test') {
        $pp_response["reason_text"] .= "; This is a TEST transaction";
    }
} else {
    $pp_response['order_status'] = 'F';
    $pp_response["reason_text"] = 'HTTP: ' . $response;
}
