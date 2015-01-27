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

$avserr = array(
    "X" => "Exact match - 9 digit zip",
    "Y" => "Exact match - 5 digit zip",
    "A" => "Address match only",
    "W" => "9-digit zip match only",
    "Z" => "5-digit zip match only",
    "N" => "No address or zip match",
    "U" => "Address unavailable",
    "G" => "Non-U.S. Issuer",
    "R" => "Issuer system unavailable"
);

$cvverr = array (
    'M' => 'Match',
    'N' => 'No Match',
    'U' => 'Issuer Not Identified'
);

$post = array();
$post['MerchantID'] = $processor_data['processor_params']['merchant_id'];
$post['RegKey'] = $processor_data['processor_params']['key'];
$post['Amount'] = $order_info['total'];
$post['REFID'] = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id);
$post['AccountNo'] = $order_info['payment_info']['card_number'];
$post['CCMonth'] = $order_info['payment_info']['expiry_month'];
$post['CCYear'] = $order_info['payment_info']['expiry_year'];
$post['NameonAccount'] = $order_info['payment_info']['cardholder_name'];
$post['AVSADDR'] = $order_info['b_address'];
$post['AVSZIP'] = $order_info['b_zipcode'];
$post['CVV2'] = $order_info['payment_info']['cvv2'];
$post['CCRURL'] = 'Unix';

Registry::set('log_cut_data', array('AccountNo', 'CCMonth', 'CCYear', 'CVV2'));
$return = Http::post("https://webservices.primerchants.com:443/billing/TransactionCentral/processCC.asp", $post);

if (preg_match("/Auth=(.*)&/U", $return, $res)) {
    if ($res[1] != "Declined") {
        $pp_response['order_status'] = 'P';
        $pp_response["reason_text"] = "AuthCode: ".$res[1];
        if ($res[1] == '999999') {
            $pp_response["reason_text"] .= "; " . __('test_transaction');
        }
    } else {
        $pp_response['order_status'] = 'F';
        preg_match("/Notes=(.*)&/U", $return, $mess);
        $pp_response["reason_text"] = $res[1].": ".$mess[1];
    }

    if(preg_match("/TransID=(.*)&/U", $return, $tran))
        $pp_response["transaction_id"] = $tran[1];

    if(preg_match("/AVSCode=(.*)&/U", $return, $avs))
        $pp_response['descr_avs'] = empty($avserr[$avs[1]]) ? "AVS Code: ".$avs[1] : $avserr[$avs[1]];

    if(preg_match("/CVV2ResponseMsg=([^&]*)/U", $return, $cvv))
        $pp_response['descr_cvv'] = empty($cvverr[$cvv[1]]) ? "CVV Code: ".$cvv[1] : $cvverr[$cvv[1]];

} else {
    $pp_response['order_status'] = 'F';
    $pp_response['reason_text'] = strip_tags($return);
}
