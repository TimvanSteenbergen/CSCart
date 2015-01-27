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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) {
    require './init_payment.php';
    $order_id = (int)$_REQUEST['order_id'];
    if (!empty($_REQUEST['payer_merchant_reference_id']) || (!empty($_REQUEST['payer_callback_type']) && $_REQUEST['payer_callback_type'] == 'settle')) {
        // Settle data is received
        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_payment_method_data($payment_id);
        $order_info = fn_get_order_info($order_id);

        if ($order_info['status'] == 'N' || $order_info['status'] == 'O') {
            $pp_response = array();
            $req_url = ($_SERVER['SERVER_PORT'] == '80' ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            $ok1 = (fn_strtolower($_REQUEST['md5sum']) == fn_strtolower(md5($processor_data['processor_params']['key_1'] . substr($req_url, 0, strpos($req_url, '&md5sum')) . $processor_data['processor_params']['key_2'])));
            $valid_ips = array(
                '217.151.207.84',
                '79.136.103.5',
                '79.136.103.9',
                '94.140.57.180',
                '94.140.57.184',
                '192.168.100.1'
            );
            $ok2 = in_array($_SERVER['REMOTE_ADDR'], $valid_ips);
            $pp_response['order_status'] = ($ok1 && $ok2) ? 'P' : 'F';
            $pp_response['reason_text'] = __('order_id') . '-' . $order_id;
            $pp_response['transaction_id'] = !empty($_REQUEST['payread_payment_id']) ? $_REQUEST['payread_payment_id'] : 'BANK';
            fn_finish_payment($order_id, $pp_response);
        }
        echo "TRUE";
        exit;

    } else {
        // Customer is redirected from the Pay&Read server
        // Check if the settle data was recieved and order status was upsated otherwise transaction is failed
        $order_info = fn_get_order_info($order_id);
        if ($order_info['status'] == 'N' || $order_info['status'] == 'O') {
            $pp_response = array();
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('order_id') . '-' . $order_id;
            fn_finish_payment($order_id, $pp_response, false);
        }
        fn_order_placement_routines('route', $order_id);
        exit;
    }
} else {
// Prepare payment data and submit the form
$post = "";
$post[] = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>";
$post[] = "<payread_post_api_0_2 xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:noNamespaceSchemaLocation=\"payread_post_api_0_2.xsd\">";
$post[] = "<seller_details>";
$post[] = "<agent_id>" . $processor_data["processor_params"]["agent_id"]."</agent_id>";
$post[] = "</seller_details>";
// Buyer details
$post[] = "<buyer_details>";
$post[] = "<first_name>" . $order_info["b_firstname"]."</first_name>";
$post[] = "<last_name>" . $order_info["b_lastname"]."</last_name>";
$post[] = "<address_line_1>" . $order_info["b_address"]."</address_line_1>";
$post[] = "<address_line_2>" . $order_info["b_address_2"]."</address_line_2>";
$post[] = "<postal_code>" . $order_info["b_zipcode"]."</postal_code>";
$post[] = "<city>" . $order_info["b_city"]."</city>";
$post[] = "<country_code>" . $order_info["b_country"]."</country_code>";
$post[] = "<phone_home></phone_home>";
$post[] = "<phone_mobile></phone_mobile>";
$post[] = "<phone_work>" . $order_info["phone"]."</phone_work>";
$post[] = "<email>" . $order_info["email"]."</email>";
$post[] = "</buyer_details>";

// Purchase
$post[] = "<purchase>";
$post[] = "<currency>" . $processor_data["processor_params"]["currency"]."</currency>";
$post[] = "<reference_id>". (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . "</reference_id>";

$post[] = "<purchase_list>";

$i = 0;

// Products
if (!empty($order_info['products'])) {
    foreach ($order_info['products'] as $k => $v) {
        $product_tax = 0;
        if (!empty($order_info['taxes'])) {
            list($product_tax, $tax_percent) = fn_get_pay_read_tax($order_info['taxes'], $k);
        }
        $post[] = "<freeform_purchase>";
        $post[] = "<line_number>" . ++$i . "</line_number>";
        $post[] = "<description><![CDATA[" . $v['product'] . "]]></description>";
        $post[] = "<price_including_vat>" . fn_format_price($v['price'] - fn_external_discounts($v) + $product_tax / $v['amount']) . "</price_including_vat>";
        $post[] = "<vat_percentage>$tax_percent</vat_percentage>";
        $post[] = "<quantity>" . $v['amount'] . "</quantity>";
        $post[] = "</freeform_purchase>";
    }
}
// Gift Cartificates
if (!empty($order_info['gift_certificates'])) {
    foreach ($order_info['gift_certificates'] as $k => $v) {
        $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
        $post[] = "<freeform_purchase>";
        $post[] = "<line_number>".++$i."</line_number>";
        $post[] = "<description><![CDATA[" . $v['gift_cert_code'] . "]]></description>";
        $post[] = "<price_including_vat>".fn_format_price($v['amount']) . "</price_including_vat>";
        $post[] = "<vat_percentage>0</vat_percentage>";
        $post[] = "<quantity>1</quantity>";
        $post[] = "</freeform_purchase>";
    }
}
// Surcharge
if (floatval($order_info['payment_surcharge'])) {
    $post[] = "<freeform_purchase>";
    $post[] = "<line_number>" . ++$i . "</line_number>";
    $post[] = "<description>" . __('surcharge') . "</description>";
    $post[] = "<price_including_vat>" . $order_info['payment_surcharge'] . "</price_including_vat>";
    $post[] = "<vat_percentage>0</vat_percentage>";
    $post[] = "<quantity>1</quantity>";
    $post[] = "</freeform_purchase>";
}

// Shipping
if (floatval($order_info['shipping_cost'])) {
    foreach ($order_info['shipping'] as $key => $value) {
        $shipping_tax = 0;
        if (!empty($order_info['taxes'])) {
            $shipping_key = $value['group_key'] . '_' . $value['shipping_id'];
            list($shipping_tax, $tax_percent) = fn_get_pay_read_tax($order_info['taxes'], $shipping_key, 'S');
        }
        $post[] = "<freeform_purchase>";
        $post[] = "<line_number>" . ++$i . "</line_number>";
        $post[] = "<description>" . $value['shipping']."</description>";
        $post[] = "<price_including_vat>" . fn_format_price($value['rate'] + $shipping_tax) . "</price_including_vat>";
        $post[] = "<vat_percentage>$tax_percent</vat_percentage>";
        $post[] = "<quantity>1</quantity>";
        $post[] = "</freeform_purchase>";
    }
}

// Used Gift Cartificates
if (!empty($order_info['use_gift_certificates'])) {
    foreach ($order_info['use_gift_certificates'] as $k => $v) {
        $post[] = "<freeform_purchase>";
        $post[] = "<line_number>" . ++$i . "</line_number>";
        $post[] = "<description>" . htmlentities($k) . "</description>";
        $post[] = "<price_including_vat>-" . fn_format_price($v['cost']) . "</price_including_vat>";
        $post[] = "<vat_percentage>0</vat_percentage>";
        $post[] = "<quantity>1</quantity>";
        $post[] = "</freeform_purchase>";
    }
}

// Order discounts
if (floatval($order_info['subtotal_discount'])) {
    $post[] = "<freeform_purchase>";
    $post[] = "<line_number>" . ++$i . "</line_number>";
    $post[] = "<description>" . htmlentities(__('order_discount')) . "</description>";
    $post[] = "<price_including_vat>-" . fn_format_price($order_info['subtotal_discount']) . "</price_including_vat>";
    $post[] = "<vat_percentage>0</vat_percentage>";
    $post[] = "<quantity>1</quantity>";
    $post[] = "</freeform_purchase>";
}

if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
    foreach ($order_info['taxes'] as $tax_id => $tax) {
        if ($tax['price_includes_tax'] == 'Y') {
            continue;
        }
        $post[] = "<freeform_purchase>";
        $post[] = "<line_number>" . ++$i . "</line_number>";
        $post[] = "<description>" . htmlspecialchars($tax['description']) . "</description>";
        $post[] = "<price_including_vat>" .fn_format_price($tax['tax_subtotal']) . "</price_including_vat>";
        $post[] = "<vat_percentage>0</vat_percentage>";
        $post[] = "<quantity>1</quantity>";
        $post[] = "</freeform_purchase>";
    }
}

$post[] = "</purchase_list>";
$post[] = "</purchase>";

//Processing control
$url = fn_payment_url('current', "pay_read.php?order_id=$order_id");
$post[] = "<processing_control>";
$post[] = "<success_redirect_url>" . $url . "</success_redirect_url>";
$post[] = "<authorize_notification_url>" . $url . "</authorize_notification_url>";
$post[] = "<settle_notification_url>" . $url . "</settle_notification_url>";
$post[] = "<redirect_back_to_shop_url>" . $url . "</redirect_back_to_shop_url>";
$post[] = "</processing_control>";

// Database overrides
$post[] = "<database_overrides>";
$post[] = "<accepted_payment_methods>";
$post[] = "<payment_method>card</payment_method>";
$post[] = "</accepted_payment_methods>";

// Debug mode
$post[] = "<debug_mode>silent</debug_mode>";
// Test mode
$post[] = "<test_mode>" . $processor_data["processor_params"]["test"] . "</test_mode>";
// Language
$post[] = "<language>" . $processor_data["processor_params"]["language"] . "</language>";
$post[] = "</database_overrides>";
$post[] = "</payread_post_api_0_2>";

$post_str = base64_encode(implode($post));
$checksum = md5($processor_data["processor_params"]["key_1"] . $post_str . $processor_data["processor_params"]["key_2"]);
$post_data = array(
    'payread_agentid' => $processor_data['processor_params']['agent_id'],
    'payread_xml_writer' => 'payread_php_0_2',
    'payread_data' => $post_str,
    'payread_checksum' => $checksum,
);
$post_url = 'https://secure.pay-read.se/PostAPI_V1/InitPayFlow';

fn_create_payment_form($post_url, $post_data, 'Pay&Read server');
}

function fn_get_pay_read_tax($order_taxes, $item_id, $type = 'P')
{
    $tax = 0;
    $tax_percent = 0;
    if (!empty($order_taxes) && Registry::get('settings.General.tax_calculation') != 'subtotal') {
        foreach ($order_taxes as $tax_id => $tax_data) {
            $tax_percent += (($tax_data['rate_type'] == 'P') ? $tax_data['rate_value'] : '0');
            if (isset($tax_data['applies'][$type . '_' . $item_id]) && $tax_data['applies'][$type . '_' . $item_id] && $tax_data['price_includes_tax'] != 'Y') {
                $tax += $tax_data['applies'][$type . '_' . $item_id];
            }
        }
    }

    return array($tax, $tax_percent);
}
