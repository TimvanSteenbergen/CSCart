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

$post = array();
$post['UN']        = $processor_data['processor_params']['username'];
$post['PSWD']      = $processor_data['processor_params']['password'];
if ($processor_data['processor_params']['test'] == 'Y') {
    $post['TEST']  = 'Y';
}
$post['TERMS']     = 'Y';
$post['METHOD']    = 'ProcessTranx';
$post['TRANXTYPE'] = 'Sale';
$post['CC']        = $order_info['payment_info']['card_number'];
$post['EXPMNTH']   = $order_info['payment_info']['expiry_month'];
$post['EXPYR']     = $order_info['payment_info']['expiry_year'];
$post['AMOUNT']    = $order_info['total'];
$post['CSC']       = $order_info['payment_info']['cvv2'];
$post['BADDRESS']  = $order_info['b_address'];
$post['BZIP']      = $order_info['b_zipcode'];
if (!empty($order_info['b_address_2'])) {
    $post['BADDRESS2'] = $order_info['b_address_2'];
}
$post['BNAME'] = $order_info['payment_info']['cardholder_name'];
$post['BCITY'] = $order_info['b_city'];
$post['EMAIL'] = $order_info['email'];
$post['PHONE'] = $order_info['phone'];
$post['INVOICE'] = $order_id;

$parts = array();
foreach ($post as $k => $v) {
    $parts[] = $k . '~' . $v;
}

Registry::set('log_cut_data', array('CC', 'EXPMNTH', 'EXPYR', 'CSC', 'CVV2', 'StartMonth', 'StartYear'));
$response = Http::post("https://paytrace.com/api/default.pay", array('parmlist' => implode('|', $parts) . '|'));

$response = explode('|', $response);
$vars = array();
foreach ($response as $pair) {
    $tmp = explode('~', $pair);
    if (!empty($tmp[1])) {
        $vars[$tmp[0]] = $tmp[1];
    }
}

$approved = false;
$error_message = '';

foreach ($vars as $key => $value) {
    if ($key == 'APPCODE') {
        if (!empty($value)) {
            $approved = true;
        }
    } elseif ($key == 'ERROR') {
        $error_message .= $value;
    }
}

$pp_response = array();
if (!empty($error_message)) {
    $pp_response['order_status'] = 'F';
    $pp_response['reason_text']  = 'Declined: ' . $error_message;
} else {
    if ($approved == true) {
        $pp_response['order_status']   = 'P';
        $pp_response['transaction_id'] = $vars['TRANSACTIONID'];
        $pp_response['reason_text']  = $vars['APPMSG'];
    } else {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text']  = $vars['APPMSG'];
    }
}
