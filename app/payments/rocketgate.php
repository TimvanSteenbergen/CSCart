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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$transaction_types = array(
    'P' => 'AUTH_CAPTURE',
    'A' => 'AUTH_ONLY',
    'C' => 'CAPTURE_ONLY',
    'R' => 'CREDIT',
    'I' => 'PRIOR_AUTH_CAPTURE'
);

$trans_type = $processor_data['processor_params']['transaction_type'];
$__version = '3.1';
$post = array();

if ($trans_type == 'R') {
    $post['x_trans_id'] = $order_info['payment_info']['transaction_id'];
}

$processor_error = array();

$processor_error['avs'] = array(
    'A' => 'Address (Street) matches, ZIP does not',
    'B' => 'Address information not provided for AVS check',
    'D' => 'Exact AVS Match',
    'E' => 'AVS error',
    'F' => 'Exact AVS Match',
    'G' => 'Service not supported by issuer',
    'M' => 'Address (Street) matches',
    'N' => 'No Match on Address (Street) or ZIP',
    'P' => 'ZIP/Postal code matches, Address (Street) does not',
    'R' => 'Retry. System unavailable or timed out',
    'S' => 'Service not supported by issuer',
    'U' => 'Address information is unavailable',
    'W' => '9 digit ZIP matches, Address (Street) does not',
    'X' => 'Exact AVS Match',
    'Y' => 'Address (Street) and 5 digit ZIP match',
    'Z' => '5 digit ZIP matches, Address (Street) does not',
    '1' => 'Exact AVS Match',
    '2' => 'Exact AVS Match',
    '3' => 'Address (Street) matches, ZIP does not'
);

$processor_error['cvv'] = array(
    'M' => 'Match',
    'N' => 'CVV2 code: No Match',
    'P' => 'CVV2 code: Not Processed',
    'S' => 'CVV2 code: Should have been present',
    'U' => 'CVV2 code: Issuer unable to process request'
);

$processor_error['cavv'] = array(
    '0' => 'CAVV not validated because erroneous data was submitted',
    '1' => 'CAVV failed validation',
    '2' => 'CAVV passed validation',
    '3' => 'CAVV validation could not be performed; issuer attempt incomplete',
    '4' => 'CAVV validation could not be performed; issuer system error',
    '7' => 'CAVV attempt - failed validation - issuer available (US issued card/non-US acquirer)',
    '8' => 'CAVV attempt - passed validation - issuer available (US issued card/non-US acquirer)',
    '9' => 'CAVV attempt - failed validation - issuer unavailable (US issued card/non-US acquirer)',
    'A' => 'CAVV attempt - passed validation - issuer unavailable (US issued card/non-US acquirer)',
    'B' => 'CAVV passed validation, information only, no liability shift'
);

$processor_error['order_status'] = array(
    '1' => 'P',
    '2' => 'D',
    '3' => 'F',
    '4' => 'O' // Transaction is held for review...
);

// Gateway parameters
$post['x_login'] = $processor_data['processor_params']['login'];
$post['x_tran_key'] = $processor_data['processor_params']['transaction_key'];
$post['x_version'] = $__version;

$post['x_delim_data'] ='TRUE';
$post['x_delim_char'] = ',';
$post['x_encap_char'] = '|';

// Billing address
$post['x_first_name'] = $order_info['b_firstname'];
$post['x_last_name'] = $order_info['b_lastname'];
$post['x_address'] = $order_info['b_address'];
$post['x_city'] = $order_info['b_city'];
$post['x_zip'] = $order_info['b_zipcode'];
$post['x_state'] = $order_info['b_state'];
$post['x_country'] = $order_info['b_country'];

// Customer information
$post['x_phone'] = $order_info['phone'];
$post['x_cust_id'] = $_SESSION['auth']['user_id'];
$post['x_customer_ip'] = $_SERVER['REMOTE_ADDR'];
$post['x_email'] = $order_info['email'];

// Merchant information
$post['x_invoice_num'] = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id);
$post['x_amount'] = fn_format_price($order_info['total']);
$post['x_currency_code'] = $processor_data['processor_params']['currency'];
$post['x_method'] = 'CC';
$post['x_type'] = $transaction_types[$trans_type];

// CC information
$post['x_card_num'] = $order_info['payment_info']['card_number'];
$post['x_exp_date'] = $order_info['payment_info']['expiry_month'] . '/' . $order_info['payment_info']['expiry_year'];
$post['x_card_code'] = $order_info['payment_info']['cvv2'];

$payment_url = ($processor_data['processor_params']['mode'] == 'test') ? 'https://dev-secure.rocketgate.com/hostedpage/servlet/AuthNetEmulator' : 'https://secure.rocketgate.com/hostedpage/servlet/AuthNetEmulator';

$__response = Http::post($payment_url, $post);

// Gateway answered
if (!empty($__response)) {
    $response_data = explode('|,|', '|,' . $__response . ',|');

// Gateway didn't answer - set some kind of error
} else {
    $response_data = array();
    $response_data[1] = 3; // Transaction failed
    $response_data[4] = '';
}

$pp_response = array();

if (!empty($response_data[1]) && !empty($processor_error['order_status'][$response_data[1]])) {
    $pp_response['order_status'] = $processor_error['order_status'][$response_data[1]];
} else {
    $pp_response['order_status'] = 'F';
    $response_data[4] = 'Processor does not reponse';
}

if (!empty($response_data[6]) && $response_data[6] != 'Y') {
    $pp_response['order_status'] = 'F';
    $response_data[4] = $processor_error['avs'][$response_data[6]];
}

if (!empty($response_data[39]) && $response_data[39] != 'M') {
    $pp_response['order_status'] = 'F';
    $response_data[4] = $processor_error['cvv'][$response_data[39]];
}

if (!empty($response_data[40])) {
    $pp_response['order_status'] = 'F';
    $response_data[4] = $processor_error['cavv'][$response_data[40]];
}

$pp_response['reason_text'] = $response_data[4];
$pp_response['transaction_id'] = (!empty($response_data[7])) ? $response_data[7] : '';
