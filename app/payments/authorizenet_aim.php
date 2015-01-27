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

$transaction_types = array(
    "P" => "AUTH_CAPTURE",
    "A" => "AUTH_ONLY",
    "C" => "CAPTURE_ONLY",
    "R" => "CREDIT",
    "I" => "PRIOR_AUTH_CAPTURE"
);

$trans_type = $processor_data['processor_params']['transaction_type'];
$__version = '3.1';
$post = array();

if ($trans_type == 'R') {
    $post['x_trans_id'] = $order_info['payment_info']['transaction_id'];
}

$processor_error = array(); // !!!FIXME: should be international descriptions

$processor_error['avs'] = array(
    "A" => "Address (Street) matches, ZIP does not",
    "B" => "Address information not provided for AVS check",
    "E" => "AVS error",
    "N" => "No Match on Address (Street) or ZIP",
    "P" => "AVS not applicable for this transaction",
    "R" => "Retry. System unavailable or timed out",
    "S" => "Service not supported by issuer",
    "U" => "Address information is unavailable",
    "W" => "9 digit ZIP matches, Address (Street) does not",
    "X" => "Exact AVS Match",
    "Y" => "Address (Street) and 5 digit ZIP match",
    "Z" => "5 digit ZIP matches, Address (Street) does not"
);

$processor_error['cvv'] = array(
    "M" => "Match",
    "N" => "No Match",
    "P" => "Not Processed",
    "S" => "Should have been present",
    "U" => "Issuer unable to process request"
);

$processor_error['cavv'] = array(
    "0" => "CAVV not validated because erroneous data was submitted",
    "1" => "CAVV failed validation",
    "2" => "CAVV passed validation",
    "3" => "CAVV validation could not be performed; issuer attempt incomplete",
    "4" => "CAVV validation could not be performed; issuer system error",
    "7" => "CAVV attempt - failed validation - issuer available (US issued card/non-US acquirer)",
    "8" => "CAVV attempt - passed validation - issuer available (US issued card/non-US acquirer)",
    "9" => "CAVV attempt - failed validation - issuer unavailable (US issued card/non-US acquirer)",
    "A" => "CAVV attempt - passed validation - issuer unavailable (US issued card/non-US acquirer)",
    "B" => "CAVV passed validation, information only, no liability shift"
);

$processor_error['order_status'] = array(
    "1" => "P",
    "2" => "D",
    "3" => "F",
    "4" => "O" // Transaction is held for review... I think open order status is good for such situation
);

// Gateway parameters
$post['x_login'] = $processor_data['processor_params']['login'];
$post['x_tran_key'] = $processor_data['processor_params']['transaction_key'];
$post['x_version'] = $__version;
$post['x_test_request'] = (($processor_data['processor_params']['mode'] == 'test' || $processor_data['processor_params']['mode'] == 'developer') ? 'TRUE' : 'FALSE');

$post['x_delim_data'] = 'TRUE';
$post['x_delim_char'] = ',';
$post['x_encap_char'] = '|';

// Billing address
$post['x_first_name'] = $order_info['b_firstname']; // !!!FIXME: Shoud have separate first/lastnames for shipping/billing
$post['x_last_name'] = $order_info['b_lastname'];
$post['x_address'] = $order_info['b_address'];
$post['x_city'] = $order_info['b_city'];
$post['x_zip'] = $order_info['b_zipcode'];
$post['x_state'] = $order_info['b_state'];
$post['x_country'] = $order_info['b_country'];
$post['x_company'] = $order_info['company'];

// Shipping address
$post['x_ship_to_first_name'] = $order_info['firstname']; // !!!FIXME: Shoud have separate first/lastnames for shipping/billing
$post['x_ship_to_last_name'] = $order_info['lastname'];
$post['x_ship_to_address'] = $order_info['s_address'];
$post['x_ship_to_company'] = $order_info['company'];
$post['x_ship_to_city'] = $order_info['s_city'];
$post['x_ship_to_state'] = $order_info['s_state'];
$post['x_ship_to_zip'] = $order_info['s_zipcode'];
$post['x_ship_to_country'] = $order_info['s_country'];

// Customer information
$post['x_phone'] = $order_info['phone'];
$post['x_fax'] = $order_info['fax'];
$post['x_cust_id'] = $_SESSION['auth']['user_id']; // !!!FIXME (what about not registered?)
$post['x_customer_ip'] = $_SERVER['REMOTE_ADDR'];
$post['x_email'] = $order_info['email'];
$post['x_email_customer'] = 'FALSE';

// Merchant information
$post['x_merchant_email'] = Registry::get('settings.Company.company_orders_department');
$post['x_invoice_num'] = $processor_data['processor_params']['order_prefix'] . $order_id . (($order_info['repaid']) ? "_$order_info[repaid]" : '')  . '_' . fn_date_format(time(), '%H_%M_%S');
$post['x_amount'] = fn_format_price($order_info['total']);
$post['x_currency_code'] = $processor_data['processor_params']['currency'];
$post['x_method'] = 'CC';
$post['x_recurring_billing'] = 'NO';
$post['x_type'] = $transaction_types[$trans_type];

// CC information
$post['x_card_num'] = $order_info['payment_info']['card_number'];
$post['x_exp_date'] = $order_info['payment_info']['expiry_month'] . '/' . $order_info['payment_info']['expiry_year'];
$post['x_card_code'] = $order_info['payment_info']['cvv2'];

// Cart totals
$post['x_relay_response'] = 'FALSE';
$post['x_tax'] = fn_format_price($order_info['tax_subtotal']);
$post['x_freight'] = fn_format_price($order_info['shipping_cost']);

$payment_url = ($processor_data['processor_params']['mode'] == 'developer') ? "https://test.authorize.net/gateway/transact.dll" : "https://secure.authorize.net:443/gateway/transact.dll";

Registry::set('log_cut_data', array('x_card_num', 'x_exp_date', 'x_card_code'));
$__response = Http::post($payment_url, $post);

// TESTING: failed response
//$__response = "|3|,|2|,|33|,|(TESTMODE) A valid referenced transaction ID is required.|,|000000|,|P|,|0|,|TO-40|,||,|78.00|,|CC|,|prior_auth_capture|,|1|,|admin|,|admin|,|Company|,|admin|,|admin|,|MI|,|admin|,|US|,|admin|,||,|customer@192.168.0.33|,|admin|,|admin|,|Company|,|admin|,|admin|,|MI|,|admin|,|US|,|0.0000|,||,||,||,||,|BBF4A22888BA05DD5B5E738F451680E5|,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||";

// TESTING: approved response
//$__response = "|1|,|1|,|1|,|(TESTMODE) This transaction has been approved.|,|000000|,|P|,|0|,|TO-69|,||,|999.00|,|CC|,|auth_capture|,|1|,|admin|,|admin|,|Company|,|admin|,|admin|,|MI|,|admin|,|US|,|admin|,||,|aa@bb.cc|,|admin|,|admin|,|Company|,|admin|,|admin|,|MI|,|admin|,|US|,|0.0000|,||,|0.0000|,||,||,|6C4073133067D5176BE6F9F389CCE229|,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||,||";

// Gateway answered
if (!empty($__response)) {
    $response_data = explode('|,|', '|,' . $__response . ',|');

// Gateway didn't answer - set some kind of error ;)
} else {
    $response_data = array();
    $response_data[1] = 3; // Transaction failed
    $response_data[4] = '';
}

$pp_response = array();
if (!empty($processor_error['order_status'][$response_data[1]])) {
    $pp_response['order_status'] = $processor_error['order_status'][$response_data[1]];
} else {
    $pp_response['order_status'] = 'F';
    $response_data[4] = 'Processor does not reponse';
}

$pp_response['reason_text'] = $response_data[4];
$pp_response['transaction_id'] = (!empty($response_data[7])) ? $response_data[7] : '';

$pp_response['descr_avs'] = (!empty($response_data[6])) ? $processor_error['avs'][$response_data[6]] : '';
$pp_response['descr_cvv']  = (!empty($response_data[39])) ? $processor_error['cvv'][$response_data[39]] : '';
$pp_response['descr_cavv'] = (!empty($response_data[40])) ? $processor_error['cavv'][$response_data[40]] : '';
