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

$avs_responses = array(
    "X" => "Both the zip code (the AVS 9-digit) and the street address match.",
    "Y" => "Both the zip (the AVS 5-digit) and the street address match.",
    "A" => "The street address matches, but the zip code does not match.",
    "W" => "The 9-digit zip codes matches, but the street address does not match.",
    "Z" => "The 5-digit zip codes matches, but the street address does not match.",
    "N" => "Neither the street address nor the postal code matches.",
    "R" => "Retry, System unavailable (maybe due to timeout).",
    "S" => "Service not supported.",
    "U" => "Address information unavailable.",
    "E" => "Data not available/error invalid.",
    "G" => "Non-US card issuer that does not participate in AVS"
);

// Get CC type
$card_type = fn_get_payment_card($order_info['payment_info']['card_number'], array(
    'visa' => 'visa',
    'mastercard' => 'mc',
    'amex' => 'amex',
    'jcb' => 'jcb',
    'diners_club_carte_blanche' => 'diners',
    'diners_club_international' => 'diners',
    'discover' => 'discover'
));

// Prepare data to post to Innovative server
$post = array();
$post['VPSProtocol'] = '2.22';
$post['target_app'] = 'WebCharge_v5.06';
$post['response_mode'] = 'simple';
$post['response_fmt'] = 'delimited';
$post['upg_auth'] = 'zxcvlkjh';
$post['delimited_fmt_field_delimiter'] = '=';
$post['delimited_fmt_include_fields'] = 'true';
$post['delimited_fmt_value_delimiter'] = '||';
$post['username'] = $processor_data['processor_params']['username'];
$post['pw'] = $processor_data['processor_params']['password'];
$post['trantype'] = 'sale';
$post['cardtype'] = $card_type;
$post['ccnumber'] = $order_info['payment_info']['card_number'];
$post['month'] = $order_info['payment_info']['expiry_month']; // Must be TWO DIGIT month.
$post['year'] = $order_info['payment_info']['expiry_year']; // Must be TWO or FOUR DIGIT year.
$post['fulltotal'] = $order_info['total']; // Total amount WITHOUT dollar sign.
$post['ccname'] = $order_info['payment_info']['cardholder_name'];
$post['baddress'] = $order_info['b_address'];
$post['baddress1'] = $order_info['b_address_2'];
$post['bcity'] = $order_info['b_city'];
$post['bstate'] = $order_info['b_state'];
$post['bzip'] = $order_info['b_zipcode'];
$post['bcountry'] = $order_info['b_country']; // TWO DIGIT COUNTRY (United States = "US")
$post['bphone'] = $order_info['phone'];
$post['email'] = $order_info['email'];

// Check if test mode is used
if ($processor_data['processor_params']['mode'] == 'test') {
    $post['test_override_errors'] = 'Y';
}

// Post a request and analyse the response
Registry::set('log_cut_data', array('ccnumber', 'month', 'year'));
$return = Http::post("https://transactions.innovativegateway.com/servlet/com.gateway.aai.Aai", $post);

// Create array with response values
$response_ = explode('||', $return);
foreach ($response_ as $v) {
    $response[substr($v, 0, strpos($v, '='))] = substr(strstr($v, "="), 1);
}

// Form an order result data
$pp_response['order_status'] = (empty($response['error']) && !empty($response['approval'])) ? 'P' : 'F';
$pp_response['transaction_id'] = $response['anatransid'];
if (!empty($response['avs'])) {
    $pp_response['descr_avs'] =  $avs_responses[$response['avs']];
}
$pp_response['reason_text'] =  (empty($response['error']) && !empty($response['approval'])) ? ("Approval code: " . $response['approval']) : strip_tags($response['error']);
if (!empty($response['test_override_errors'])) {
    $pp_response["reason_text"] .= '; TEST TRANSACTION!';
}
