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
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

    $payment_id = db_get_field('SELECT payment_id FROM ?:orders WHERE order_id = ?i', $_REQUEST['order_id']);
    $processor_data = fn_get_payment_method_data($payment_id);

    $request_url = 'https://' . $processor_data['processor_params']['gateway'] . '.ewaygateway.com/Result/?' .
    'CustomerID=' . $processor_data['processor_params']['customer_id'] .
    '&UserName=' . $processor_data['processor_params']['username'] .
    '&AccessPaymentCode=' . $_REQUEST['AccessPaymentCode'];

    $return = Http::get($request_url);

    if (preg_match('/<ResponseCode>(.*)<\/ResponseCode>/', $return, $matches)) {
        $status = $matches[1];
    }

    if ($status == '00') {
        $pp_response['order_status'] = 'P';

    } elseif ($status == 'CX') {
        $pp_response['order_status'] = 'I';

    } else {
        $pp_response['order_status'] = 'D';
    }

    fn_finish_payment($_REQUEST['order_id'], $pp_response);
    fn_order_placement_routines('route', $_REQUEST['order_id']);
    exit;

} else {
    $order_prefix = !empty($processor_data['processor_params']['order_prefix']) ? $processor_data['processor_params']['order_prefix'] : '';
    $return_url = fn_url("payment_notification.notify?payment=eway_shared&order_id=$order_id", AREA, 'current');
    $MerchantInvoice = $order_prefix . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id);

    if ($processor_data['processor_params']['gateway'] == 'payment') {
        $currency = 'GBP';
    } elseif ($processor_data['processor_params']['gateway'] == 'nz') {
        $currency = 'NZD';
    } else {
        $currency = 'AUD';
    }

    $request_url = 'https://' . $processor_data['processor_params']['gateway'] . '.ewaygateway.com/Request/?' .
    'CustomerID=' . $processor_data['processor_params']['customer_id'] .
    '&UserName=' . $processor_data['processor_params']['username'] .
    '&Amount=' . fn_format_price($order_info['total'], $currency, 2, false) .
    '&Currency=' . $currency .
    '&ReturnURL=' . urlencode($return_url) .
    '&CancelURL=' . urlencode($return_url) .
    '&InvoiceDescription=' . (!empty($order_info['notice']) ? $order_info['notice'] : '') .
    '&CompanyName=' . urlencode(Registry::get('settings.Company.company_name')) .
    '&CustomerFirstName=' . urlencode($order_info['b_firstname']) .
    '&CustomerLastName=' . urlencode($order_info['b_lastname']) .
    '&CustomerAddress=' . urlencode($order_info['b_address']) .
    '&CustomerCity=' . urlencode($order_info['b_city']) .
    '&CustomerState=' . urlencode($order_info['b_state_descr']) .
    '&CustomerPostCode=' . urlencode($order_info['b_zipcode']) .
    '&CustomerCountry=' . urlencode($order_info['b_country_descr']) .
    '&CustomerPhone=' . urlencode($order_info['phone']) .
    '&CustomerEmail=' . urlencode($order_info['email']) .
    '&MerchantReference=' . urlencode($MerchantInvoice);

    $return = Http::get($request_url);

    $sucessfull = 'False';
    if (preg_match("/<Result>(.*)<\/Result>/", $return, $matches)) {
        $sucessfull = $matches[1];
    }

    if ($sucessfull == 'True') {
        if (preg_match("/<URI>(.*)<\/URI>/", $return, $matches)) {
            fn_create_payment_form($matches[1], array(), '', true, 'get');
        }

    } else {
        if (preg_match("/<Error>(.*)<\/Error>/", $return, $matches)) {
            $pp_response['reason_text'] = $matches[1];
        }

        $pp_response['order_status'] = 'D';
    }
}
