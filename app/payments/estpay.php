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

$_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;

// XML request sablonu
$post_data = "DATA=<?xml version=\"1.0\" encoding=\"ISO-8859-9\"?>
<CC5Request>
<Name>".$processor_data['processor_params']['merchant_name']."</Name>
<Password>".$processor_data['processor_params']['merchant_password']."</Password>
<ClientId>".$processor_data['processor_params']['client_id']."</ClientId>
<IPAddress>".$_SERVER['REMOTE_ADDR']."</IPAddress>
<Email>".$order_info['email']."</Email>
<Mode>P</Mode>
<OrderId>".$_order_id."</OrderId>
<GroupId></GroupId>
<TransId></TransId>
<UserId></UserId>
<Type>Auth</Type>
<Number>".$order_info['payment_info']['card_number']."</Number>
<Expires>". $order_info['payment_info']['expiry_month'] . '/' . $order_info['payment_info']['expiry_year'] ."</Expires>
<Cvv2Val>".$order_info['payment_info']['cvv2']."</Cvv2Val>
<Total>".$order_info['total']."</Total>
<Currency>".$processor_data['processor_params']['currency']."</Currency>
<BillTo>
    <Name>".$order_info['firstname'] .'+'. $order_info['lastname']."</Name>
    <Street1>".$order_info["b_address"]."</Street1>
    <Street2>".$order_info["b_address_2"]."</Street2>
    <Street3></Street3>
    <City>".$order_info["b_city"]."</City>
    <StateProv>".$order_info["b_state"]."</StateProv>
    <PostalCode>".$order_info["b_zipcode"]."</PostalCode>
    <Country>".$order_info["b_country"]."</Country>
    <Company>".$order_info["company"]."</Company>
    <TelVoice>".$order_info['phone']."</TelVoice>
</BillTo>
    <ShipTo>
    <Name>".$order_info['firstname'] .'+'. $order_info['lastname']."</Name>
    <Street1>".$order_info["s_address"]."</Street1>
    <Street2>".$order_info["s_address_2"]."</Street2>
    <Street3></Street3>
    <City>".$order_info["s_city"]."</City>
    <StateProv>".$order_info["s_state"]."</StateProv>
    <PostalCode>".$order_info["s_zipcode"]."</PostalCode>
    <Country>".$order_info["s_country"]."</Country>
</ShipTo>
<Extra></Extra>
</CC5Request>
";

$url = ($processor_data['processor_params']['mode'] == 'test') ? "https://cc5test.est.com.tr/servlet/cc5ApiServer" : "https://vpos.est.com.tr/servlet/cc5ApiServer";
Registry::set('log_cut_data', array('Number', 'Expires', 'Cvv2Val'));
$return = Http::post($url, $post_data);

$pp_response = array();

if (preg_match("/<Response>(.*)<\/Response>/", $return, $response)) {
    $pp_response['order_status'] = ($response[1] == 'Approved') ? 'P' : 'D';
    $pp_response['reason_text'] = '';

    if (preg_match("/<TransId>(.*)<\/TransId>/", $return, $transaction_id)) {
        $pp_response['transaction_id'] = $transaction_id[1];
    }
    if ($response[1] === "Approved") {
        if (preg_match("/<AuthCode>(.*)<\/AuthCode>/", $return, $auth_code)) {
            $pp_response['reason_text'] = 'Auth code: ' . $auth_code[1] . ' ';
        }

        $pp_response['reason_text'] .= $response[1];
    } else {
        if (preg_match("/<ProcReturnCode>(.*)<\/ProcReturnCode>/", $return, $proc_return_code)) {
            $pp_response['reason_text'] = 'Response code: ' . $proc_return_code[1] . ' ';
        }

        if (preg_match("/<ErrMsg>(.*)<\/ErrMsg>/", $return, $error)) {
            $pp_response['reason_text'] .= '(' . $error[1] . ')';
        }
    }
} else {
    $pp_response['order_status'] = 'F';
}
