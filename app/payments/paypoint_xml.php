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

$error_message = array(
    "N" => "Transaction not authorised. Failure message text available to merchant",
    "C" => "Communication problem. Trying again later may well work",
    "P:A" => "Pre-bank checks. Amount not supplied or invalid",
    "P:X" => "Pre-bank checks. Not all mandatory parameters supplied",
    "P:P" => "Pre-bank checks. Same payment presented twice",
    "P:S" => "Pre-bank checks. Start date invalid",
    "P:E" => "Pre-bank checks. Expiry date invalid",
    "P:I" => "Pre-bank checks. Issue number invalid",
    "P:C" => "Pre-bank checks. Card number fails LUHN check",
    "P:T" => "Pre-bank checks. Card type invalid - i.e. does not match card number prefix",
    "P:N" => "Pre-bank checks. Customer name not supplied",
    "P:M" => "Pre-bank checks. Merchant does not exist or not registered yet",
    "P:B" => "Pre-bank checks. Merchant account for card type does not exist",
    "P:D" => "Pre-bank checks. Merchant account for this currency does not exist",
    "P:V" => "Pre-bank checks. CV2 security code mandatory and not supplied / invalid",
    "P:R" => "Pre-bank checks. Transaction timed out awaiting a virtual circuit. Merchant may not have enough virtual circuits for the volume of business.",
    "P:#" => "Pre-bank checks. No MD5 hash / token key set up against account"
);

$_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
// Order details
$ord = '';
if (!empty($order_info['products'])) {
    foreach ($order_info['products'] as $v) {
        $ord .= ",prod=" . $v['product_code'];
        $ord .= ",item_amount=" . fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount']) . "x" . $v['amount'];
    }
}

if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
    foreach ($order_info['taxes'] as $tax_id => $tax) {
        if ($tax['price_includes_tax'] == 'N') {
            $ord .= ",prod=" . $tax['description'];
            $ord .= ",item_amount=" . fn_format_price($tax['tax_subtotal']) . "x1";
        }
    }
}

if (!empty($order_info['gift_certificates'])) {
    foreach ($order_info['gift_certificates'] as $v) {
        $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
        $ord .= ",prod=" . $v['gift_cert_code'];
        $ord .= ",item_amount=" . fn_format_price($v['amount']) . "x1";
    }
}
if (floatval($order_info['subtotal_discount'])) {
    $pr = fn_format_price($order_info['subtotal_discount']);
    $ord .= ",prod=ORDER_DISCOUNT";
    $ord .= ",item_amount=-" . $pr . "x1";
}

$ord = ltrim(str_replace(" ", "+", $ord), ',');

//Billing address
$bill = "name=" . $order_info['firstname'] . '+' . $order_info['lastname'];
$bill .= ",company=" . $order_info['firstname'];
$bill .= ",addr_1=" . $order_info["b_address"];
$bill .= ",addr_2=" . $order_info["b_address_2"];
$bill .= ",city=" . $order_info["b_city"];
$bill .= ",state=" . $order_info['b_state_descr'];
$bill .= ",country=" . $order_info['b_country_descr'];
$bill .= ",post_code=" . $order_info["b_zipcode"];
$bill .= ",tel=" . $order_info["phone"];
$bill .= ",email=" . $order_info["email"];
$bill .= ",url=" . $order_info["url"];
$bill = str_replace(" ", "+", $bill);

//Shipping ddress
$ship = "name=" . $order_info['firstname'] . '+' . $order_info['lastname'];
$ship .= ",company=" . $order_info['firstname'];
$ship .= ",addr_1=" . $order_info["s_address"];
$ship .= ",addr_2=" . $order_info["s_address_2"];
$ship .= ",city=" . $order_info["s_city"];
$ship .= ",state=" . $order_info['s_state_descr'];
$ship .= ",country=" . $order_info['s_country_descr'];
$ship .= ",post_code=" . $order_info["s_zipcode"];
$ship .= ",tel=" . $order_info["phone"];
$ship .= ",email=" . $order_info["email"];
$ship .= ",url=" . $order_info["url"];
$ship = str_replace(" ", "+", $ship);

//Options
$opts = "test_status=" . $processor_data["processor_params"]["mode"];
$opts .= ",currency=" . $processor_data["processor_params"]["currency"];
$opts .= ",dups=" . $processor_data["processor_params"]["dups"];
$opts .= ",mail_subject=" . $processor_data['processor_params']['mail_subject'];
$opts .= ",mail_message=" . $processor_data['processor_params']['mail_message'];
if (!empty($processor_data['processor_params']['deferred'])) {
    $opts .= ",deferred=" . $processor_data['processor_params']['deferred'];
}
if (!empty($order_info['payment_info']['cvv2'])) {
    $opts .= ",cv2=" . $order_info['payment_info']['cvv2'];
}
$opts = str_replace(" ", "+", $opts);

$exp_date = $order_info['payment_info']['expiry_month'] . '/' . $order_info['payment_info']['expiry_year'];

// empty params are: cc issue number and start date
$post_data = "<?xml version='1.0'?>
    <methodCall>
    <methodName>SECVPN.validateCardFull</methodName>
    <params>
        <param>
            <value><string>" . htmlspecialchars($processor_data["processor_params"]["merchant_id"]) . "</string></value>
        </param>
        <param>
            <value><string>" . htmlspecialchars($processor_data["processor_params"]["password"]) . "</string></value>
        </param>
        <param>
            <value><string>" . htmlspecialchars($processor_data["processor_params"]["order_prefix"]) . $_order_id . "</string></value>
        </param>
        <param>
            <value><string>" . $_SERVER['REMOTE_ADDR'] . "</string></value>
        </param>
        <param>
            <value><string>" . htmlspecialchars($order_info['payment_info']['cardholder_name']) . "</string></value>
        </param>
        <param>
            <value><string>" . $order_info['payment_info']['card_number'] . "</string></value>
        </param>
        <param>
            <value><string>" . $order_info['total'] . "</string></value>
        </param>
        <param>
            <value><string>" . $exp_date . "</string></value>
        </param>
        <param>
            <value><string></string></value>
        </param>
        <param>
            <value><string></string></value>
        </param>
        <param>
            <value><string>" . htmlspecialchars($ord) . "</string></value>
        </param>
        <param>
            <value><string>" . htmlspecialchars($bill) . "</string></value>
        </param>
        <param>
            <value><string>" . htmlspecialchars($ship) . "</string></value>
        </param>
        <param>
            <value><string>" . htmlspecialchars($opts) . "</string></value>
        </param>
    </params>
</methodCall>";

Registry::set('log_cut_data', array('methodCall'));
$response = Http::post("https://www.secpay.com/secxmlrpc/make_call", $post_data);

$_response = new SimpleXMLElement($response);
$pp_response['order_status'] = 'F';
if ($_response->params) {
    parse_str(substr($_response->params->param->value, 1), $result);
    $pp_response['order_status'] = ($result['code'] == 'A') ? 'P' : 'F';
    $pp_response['transaction_id'] = $result['trans_id'];
    $pp_response['reason_text'] = '';
    if (!empty($error_message[$result['code']])) {
        $pp_response['reason_text'] .= $error_message[$result['code']] . "; ";
    }
    $pp_response['reason_text'] = $result['message'] . "; ";
    if (!empty($result['auth_code'])) {
        $pp_response['reason_text'] .= ("Auth Code: " . $result['auth_code'] . "; ");
    }
    if ($processor_data["processor_params"]["mode"] != 'live') {
        $pp_response['reason_text'] .= "This is a TEST TRANSACTION!";
    }
} elseif ($_response->fault) {
    $pp_response['reason_text'] = "Fault code: " . $_response->fault->value->struct->member[1]->value->int . ". Reason '" . $_response->fault->value->struct->member[0]->value . "'";
}
