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

// Authentication
$post['dc_logon'] = $processor_data['processor_params']['username'];
$post['dc_password'] = $processor_data['processor_params']['password'];

// Credit Card
$post['dc_name'] = $order_info['payment_info']['cardholder_name'];
$post['dc_number'] = $order_info['payment_info']['card_number'];
$post['dc_expiration_month'] = $order_info['payment_info']['expiry_month'];
$post['dc_expiration_year'] = $order_info['payment_info']['expiry_year'];
$post['dc_verification_number'] = $order_info['payment_info']['cvv2'];

// Transaction
$post['dc_transaction_amount'] = $order_info['total'];
$post['dc_transaction_type'] = $processor_data['processor_params']['type'];;
$post['dc_version'] = '1.2';

// Credit Card Address
$post['dc_address'] = $order_info['b_address'];
$post['dc_city'] = $order_info['b_city'];
$post['dc_state'] = $order_info['b_state'];
$post['dc_zipcode'] = $order_info['b_zipcode'];
$post['dc_country'] = $order_info['b_country'];

Registry::set('log_cut_data', array('dc_number', 'dc_expiration_month', 'dc_expiration_year', 'dc_verification_number'));
$return = Http::post("https://payjunction.com/quick_link", $post);

$return = strtr($return,array(chr(28)=>"&"));
parse_str($return, $response);

$pp_response = array();
if (empty($response["response_code"])) {
    $response["response_code"] = @$response["dc_response_code"];
}

$pp_response['order_status'] = ($response["response_code"] == "00" || $response["response_code"] == "85" ? 'P' : 'D');
$pp_response["reason_text"] = !empty($response["response_message"]) ? $response["response_message"] : @$response["dc_response_message"];

if (!empty($response["dc_approval_code"])) {
    $pp_response["reason_text"] .= " (Approval Code: ".$response["dc_approval_code"].")";
}
if (!empty($response["dc_posture"])) {
    $pp_response["reason_text"] .= " (Posture status: ".$response["dc_posture"].")";
}
if (!empty($response["dc_transaction_id"]) && $response["dc_transaction_id"] != 'null') {
    $pp_response["transaction_id"] = $response["dc_transaction_id"];
}
if (!empty($response["dc_invoice_number"])) {
    $pp_response["reason_text"] .= " (Invoice#: ".$response["dc_invoice_number"].")";
}
if (!empty($response["dc_card_brand"])) {
    $pp_response["reason_text"] .= " (Card Brand/CC#: ".$response["dc_card_brand"]."/".$response["dc_card_number"].")";
}
if (!empty($response["dc_device_id"])) {
    $pp_response["reason_text"] .= " (Device ID: ".$response["dc_device_id"].")";
}
if (!empty($response["dc_security"])) {
    list($response["avs"], $response["cvv"], $response["preauth"], $response["avsforce"], $response["cvvforce"]) = explode("|",$response["dc_security"]);
    $pp_response['descr_cvv'] = fn_payjunction_response_val("cvvforce", $response)." :: ".fn_payjunction_response_val("cvv", $response);
    $pp_response['descr_avs'] = fn_payjunction_response_val("avsforce", $response)." :: ".fn_payjunction_response_val("avs", $response);
}

function fn_payjunction_response_val($key, $response)
{
    $processor_response = array(
        "avs" => array(
            "AWZ" => "Match Address OR Zip",
            "XY" => "Match Address AND Zip",
            "WZ" => "Match Zip",
            "AW" => "Match Address OR 9 Digit Zip",
            "AZ" => "Match Address OR 5 Digit Zip",
            "A" => "Match Address",
            "X" => "Match Address AND 9 Digit Zip",
            "Y" => "Match Address AND 5 Digit Zip",
            "W" => "Match 9 Digit Zip",
            "Z" => "Match 5 Digit Zip"
        ),
        "cvv" => array(
            "M" => "CVV On",
            "I" => "CVV Off"
        ),
        "preauth" => array(
            "true" => "Pre-auth On",
            "false" => "Pre-auth Off"
        ),
        "avsforce" => array(
            "true" => "AVS Force On",
            "false" => "AVS Force Off"
        ),
        "cvvforce" => array(
            "true" => "CVV Force On",
            "false" => "CVV Force Off"
        )
    );

    $key_ = strtr(fn_strtolower($key),array(" "=>"_"));

    return $processor_response[$key_][$response[$key_]] ? $processor_response[$key_][$response[$key_]] : $key." Code: ".$response[$key_];
}
