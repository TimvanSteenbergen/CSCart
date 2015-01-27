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

if ($processor_data['processor_params']['mode'] == 'test') {
    $base_url =  'https://uat.payleap.com/TransactServices.svc/ProcessCreditCard';
} else {
    $base_url = 'https://secure1.payleap.com/TransactServices.svc/ProcessCreditCard';
}

$post = array();
$post["UserName"] = $processor_data['processor_params']['username'];
$post["Password"] = $processor_data['processor_params']['password'];
$post["TransType"] = 'Sale';
$post["NameOnCard"] = $order_info['payment_info']['cardholder_name'];
$post["CardNum"] = $order_info['payment_info']['card_number'];
$post["ExpDate"] = $order_info['payment_info']['expiry_month'] . $order_info['payment_info']['expiry_year'];
if (!empty($order_info['payment_info']['cvv2'])) {
    $post["CVNum"] =  $order_info['payment_info']['cvv2'];
}
$post["Amount"] =  $order_info['total'];

$payleap_order_id = $order_info['repaid'] ? $order_id . '_' . $order_info['repaid'] : $order_id;
$payleap_order_id = $processor_data['processor_params']['order_prefix'] . $payleap_order_id;
$post["ExtData"] = fn_payleap_build_ext_data($order_info, $payleap_order_id);
$post["InvNum"] = $payleap_order_id;
$post["PNRef"] = '';
$post["MagData"] = '';

$response = Http::post($base_url, $post);

$xml = @simplexml_load_string($response);

$pp_response = array();

if ($xml === false) {
    $pp_response['reason_text'] = __('unknown_server_response');
    $pp_response["order_status"] = 'F';
} else {
    $result = (string) $xml->Result;
    $pp_response['host_code'] = (string) $xml->HostCode;
    $pp_response['auth_code'] = (string) $xml->AuthCode;
    $pp_response["reason_text"] = __('reason_text') . ': ' . (string) $xml->RespMSG;
    $pp_response["order_status"] = ($result == '0') ? 'P' : 'F';
}

function fn_payleap_build_xml_items($items)
{
    $_items = '';

    foreach ($items as $k => $v) {

        $data = "";
        $data[] = "<Item>";
        $data[] = "<Description>" . htmlspecialchars($v['product']) . "</Description>";
        $data[] = "<SKU>" . htmlspecialchars($v['product_code']) . "</SKU>";
        $data[] = "<DiscountAmt>{$v['discount']}</DiscountAmt>";
        $data[] = "<TaxAmt>{$v['tax_value']}</TaxAmt>";
        $data[] = "<TotalAmt>{$v['subtotal']}</TotalAmt>";
        $data[] = "<TaxRate></TaxRate>";
        $data[] = "</Item>";

        $_items .= implode("\n", $data);
    }

    return $_items;
}

function fn_payleap_build_ext_data($order_info, $order_id)
{
    $date = date('ymd');
    $items = fn_payleap_build_xml_items($order_info['products']);

    $data = "";
    $data[] = "<CustCode>{$order_info['user_id']}</CustCode>";
    $data[] = "<BillToState>{$order_info['b_state']}</BillToState>";
    $data[] = "<CustomerID>{$order_info['user_id']}";
    $data[] = "</CustomerID>";
    $data[] = "<Invoice>";
    $data[] = "<InvNum>" . htmlspecialchars($order_id) . "</InvNum>";
    $data[] = "<Date>{$date}</Date>";
    $data[] = "<BillTo>";
    $data[] = "<CustomerId>{$order_info['user_id']}</CustomerId>";
    $data[] = "<Name>" . htmlspecialchars($order_info['b_firstname'] . ' ' . $order_info['b_lastname']) . "</Name>";
    $data[] = "<Address>";
    $data[] = "<Street>" . htmlspecialchars($order_info['b_address']) . "</Street>";
    $data[] = "<City>" . htmlspecialchars($order_info['b_city']) . "</City>";
    $data[] = "<State>" . htmlspecialchars($order_info['b_state']) . "</State>";
    $data[] = "<Zip>" . htmlspecialchars($order_info['b_zipcode']) . "</Zip>";
    $data[] = "<Country>" . htmlspecialchars($order_info['b_country']) . "</Country>";
    $data[] = "</Address>";
    $data[] = "<Email>{$order_info['email']}</Email>";
    $data[] = "<Phone>{$order_info['phone']}</Phone>";
    $data[] = "<Fax>{$order_info['fax']}</Fax>";
    $data[] = "<CustCode>{$order_info['user_id']}</CustCode>";
    $data[] = "<TaxExempt>{$order_info['tax_exempt']}</TaxExempt>";
    $data[] = "</BillTo>";
    $data[] = "<Description></Description>";
    $data[] = "<Items>";
    $data[] = "{$items}";
    $data[] = "</Items>";
    $data[] = "<DiscountAmt>{$order_info['discount']}</DiscountAmt>";
    $data[] = "<ShippingAmt>{$order_info['shipping_cost']}</ShippingAmt>";
    $data[] = "<DutyAmt></DutyAmt>";
    $data[] = "<TaxAmt>{$order_info['tax_subtotal']}</TaxAmt>";
    $data[] = "<NationalTaxInc></NationalTaxInc>";
    $data[] = "<TotalAmt>{$order_info['total']}</TotalAmt>";
    $data[] = "</Invoice>";

    return implode("\n", $data);
}
