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

// Prepare data to post to the DPS server
// $processor_data["processor_params"] - data that configured on the processor configure page
// $order_info["payment_info"] - payment info that customer submitted on the place order page

$post = array();
$post[] = "<Txn>";
$post[] = "<PostUsername>" . $processor_data["processor_params"]["merchantid"] . "</PostUsername>";
$post[] = "<PostPassword>" . $processor_data["processor_params"]["password"] . "</PostPassword>";
$post[] = "<TxnType>Purchase</TxnType>";
$post[] = "<CardHolderName>" . $order_info['payment_info']['cardholder_name'] . "</CardHolderName>";
$post[] = "<CardNumber>" . $order_info['payment_info']['card_number'] . "</CardNumber>";
$post[] = "<Cvc2>" . $order_info['payment_info']['cvv2'] . "</Cvc2>";
$post[] = "<Amount>" . $order_info["total"] . "</Amount>";
$post[] = "<DateExpiry>" . $order_info['payment_info']['expiry_month'] . $order_info['payment_info']['expiry_year'] . "</DateExpiry>";
$post[] = "<MerchantReference>" . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . "</MerchantReference>";
$post[] = "<InputCurrency>" . $processor_data["processor_params"]["currency"] . "</InputCurrency>";
$post[] = "</Txn>";

// Post a request and analyse the response

Registry::set('log_cut_data', array('CardHolderName', 'CardNumber', 'Cvc2', 'DateExpiry'));
$return = Http::post("https://sec.paymentexpress.com/pxpost.aspx", implode("\n", $post));

preg_match("/<Success>(.*)<\/Success>/", $return, $success);
preg_match("/<Amount>(.*)<\/Amount>/", $return, $amount);
// Check whethe success parameter is 1 and amount is equal to the cart[total], If everything allright than order is Processed
if ($success[1] == "1" && fn_format_price($amount[1]) == fn_format_price($order_info['total'])) {
    $pp_response['order_status'] = 'P';
    preg_match("/<AuthCode>(.*)<\/AuthCode>/", $return, $authcode);
    $pp_response["reason_text"] = "(AuthCode: " . $authcode[1] . ") ";
} else {
// Otherwise the order is failed
    $pp_response['order_status'] = 'F';
    $pp_response["reason_text"] = '';
}
// Fill the payment info that will be shown on the order details in admin area.
preg_match("/<MerchantResponseText>(.*)<\/MerchantResponseText>/", $return, $text);
preg_match("/<MerchantResponseDescription>(.*)<\/MerchantResponseDescription>/", $return, $text2);
$pp_response["reason_text"] .= $text[1] . ': ' . $text2[1];
preg_match("/<DpsTxnRef>(.*)<\/DpsTxnRef>/", $return, $transaction);
$pp_response["transaction_id"] = $transaction[1];
preg_match("/<TestMode>(.*)<\/TestMode>/", $return, $_test);
if (!empty($_test[1])) {
    $pp_response["reason_text"] .= '; TEST TRANSACTION';
}
