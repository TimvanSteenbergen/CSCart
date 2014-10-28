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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

    // Get the password
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
    $processor_data = fn_get_payment_method_data($payment_id);

    $result = "&" . fn_sagepay_simplexor(base64_decode(str_replace(' ', '+', $_REQUEST['crypt'])), $processor_data["processor_params"]["password"]) . "&";

    preg_match("/Status=(.+)&/U", $result, $a);

    if (trim($a[1]) == "OK") {
        $pp_response['order_status'] = ($processor_data["processor_params"]["transaction_type"] == 'PAYMENT') ? 'P' : 'O';

        if (preg_match("/TxAuthNo=(.+)&/U", $result, $_authno)) {
            $pp_response["reason_text"] = "AuthNo: " . $_authno[1];
        }

        if (preg_match("/VPSTxID={(.+)}/U", $result, $transaction_id)) {
            $pp_response["transaction_id"] = $transaction_id[1];
        }

    } else {
        $pp_response['order_status'] = 'F';
        if (preg_match("/StatusDetail=(.+)&/U", $result, $stat)) {
            $pp_response["reason_text"] = "Status: " . trim($stat[1]) . " (".trim($a[1]) . ") ";
        }
    }

    if (preg_match("/AVSCV2=(.*)&/U", $result, $avs)) {
        $pp_response['descr_avs'] = $avs[1];
    }

    fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
    fn_order_placement_routines('route', $_REQUEST['order_id']);

} else {

    if ($processor_data['processor_params']['testmode'] == 'Y') {
        $post_address = "https://test.sagepay.com/gateway/service/vspform-register.vsp";
    } elseif ($processor_data['processor_params']['testmode'] == 'N') {
        $post_address = "https://live.sagepay.com/gateway/service/vspform-register.vsp";
    } elseif ($processor_data['processor_params']['testmode'] == 'S') {
        $post_address = "https://test.sagepay.com/Simulator/VSPFormGateway.asp";
    }

    $post["VPSProtocol"] = "2.23";
    $post["TxType"] = $processor_data["processor_params"]["transaction_type"];
    $post["Vendor"] = htmlspecialchars($processor_data["processor_params"]["vendor"]);

    $post_encrypted = 'VendorTxCode=' . $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . '-' . fn_date_format(time(), '%H_%M_%S') . "&";
    $post_encrypted .= 'Amount=' . $order_info['total'] . '&';
    $post_encrypted .= 'Currency=' . $processor_data['processor_params']['currency'] . '&';
    $post_encrypted .= 'Description=Payment for Order' . $order_id . '&';
    $post_encrypted .= 'SuccessURL=' . fn_url("payment_notification.notify?payment=sagepay_form&order_id=$order_id", AREA, 'http') . '&';
    $post_encrypted .= 'FailureURL=' . fn_url("payment_notification.notify?payment=sagepay_form&order_id=$order_id", AREA, 'http') . '&';
    $post_encrypted .= 'CustomerEMail=' . $order_info['email'] . '&';
    $post_encrypted .= 'VendorEmail=' . Registry::get('settings.Company.company_orders_department') . '&';
    $post_encrypted .= 'CustomerName=' . $order_info['firstname'] . ' ' .$order_info['lastname'] . '&';
    $post_encrypted .= 'ContactNumber=' . $order_info['phone'] . '&';
    $post_encrypted .= 'ContactFax=' . $order_info['fax'] . '&';

    // Billing address
    $post_encrypted .= 'BillingAddress1=' . $order_info['b_address'] . '&';
    if (!empty($order_info['b_address_2'])) {
        $post_encrypted .= 'BillingAddress2=' . $order_info['b_address_2'] . '&';
    }
    $post_encrypted .= 'BillingPostCode=' . $order_info['b_zipcode'] . '&';
    $post_encrypted .= 'BillingCountry=' . $order_info['b_country'] . '&';
    if ($order_info['b_country'] == 'US') {
        $post_encrypted .= 'BillingState=' . $order_info['b_state'] . '&';
    }
    $post_encrypted .= 'BillingCity=' . $order_info['b_city'] . '&';
    $post_encrypted .= 'BillingFirstnames=' . $order_info['b_firstname'] . '&';
    $post_encrypted .= 'BillingSurname=' . $order_info['b_lastname'] . '&';

    // Shipping Address
    $post_encrypted .= 'DeliveryAddress1=' . $order_info['s_address'] . '&';
    if (!empty($order_info['s_address_2'])) {
        $post_encrypted .= 'DeliveryAddress2=' . $order_info['s_address_2'] . '&';
    }
    $post_encrypted .= 'DeliveryPostCode=' . $order_info['s_zipcode'] . '&';
    $post_encrypted .= 'DeliveryCountry=' . $order_info['s_country'] . '&';
    if ($order_info['s_country'] == 'US') {
        $post_encrypted .= 'DeliveryState=' . $order_info['s_state'] . '&';
    }
    $post_encrypted .= 'DeliveryCity=' . $order_info['s_city'] . '&';
    $post_encrypted .= 'DeliveryFirstnames=' . $order_info['s_firstname'] . '&';
    $post_encrypted .= 'DeliverySurname=' . $order_info['s_lastname'] . '&';

    // Form Ordered products
    $strings = 0;
    $products_string = '';
    if (!empty($order_info['products']) && is_array($order_info['products'])) {
        $strings += count($order_info['products']);
    }
    if (!empty($order_info['gift_certificates']) && is_array($order_info['gift_certificates'])) {
        $strings += count($order_info['gift_certificates']);
    }

    if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $v) {
    //		$v['discount'] = empty($v['discount']) ? 0 : $v['discount'];
            $products_string .= ":".str_replace(":", " ", $v['product']).":".$v['amount'].":".fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount'], CART_PRIMARY_CURRENCY, null, false).":::".fn_format_price($v['subtotal'] - fn_external_discounts($v), CART_PRIMARY_CURRENCY, null, false);
        }
    }
    if (!empty($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $v) {
            $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
            $products_string .= ":".str_replace(":", " ", $v['gift_cert_code']).":1:".fn_format_price($v['amount'], CART_PRIMARY_CURRENCY, null, false).":::".fn_format_price($v['amount'], CART_PRIMARY_CURRENCY, null, false);
        }
    }
    if (floatval($order_info['payment_surcharge'])) {
        $products_string .= ":Payment surcharge:---:---:---:---:".fn_format_price($order_info['payment_surcharge'], CART_PRIMARY_CURRENCY, null, false);
        $strings ++;
    }
    if (fn_order_shipping_cost($order_info)) {
        $products_string .= ":Shipping cost:---:---:---:---:".fn_order_shipping_cost($order_info);
        $strings ++;
    }

    if (floatval($order_info['subtotal_discount'])) {
        $desc = __('order_discount');
        $pr = fn_format_price($order_info['subtotal_discount'], CART_PRIMARY_CURRENCY, null, false);
        $products_string .= ":{$desc}:---:---:---:---:-" . fn_format_price($order_info['subtotal_discount'], CART_PRIMARY_CURRENCY, null, false);
        $strings ++;
    }

    if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
        foreach ($order_info['taxes'] as $tax_id => $tax) {
            if ($tax['price_includes_tax'] == 'N') {
                $desc = $tax['description'];
                $products_string .= ":{$desc}:---:---:---:---:" . fn_format_price($tax['tax_subtotal'], CART_PRIMARY_CURRENCY, null, false);
                $strings ++;
            }
        }
    }

    $post_encrypted .= "Basket=" . $strings . $products_string;

    $post["Crypt"] = base64_encode(fn_sagepay_simplexor($post_encrypted, $processor_data["processor_params"]["password"]));
    $post["Crypt"] = htmlspecialchars($post["Crypt"]);

    fn_create_payment_form($post_address, $post, 'SagePay server');
}

exit;

//
// ---------------- Additional functions ------------
//
function fn_sagepay_simplexor($str, $key)
{
    $list = array();
    $result = '';

    for ($i = 0; $i < strlen($key); $i++) {
        $list[$i] = ord(substr($key, $i, 1));
    }

    for ($i = 0; $i < strlen($str); $i++) {
        $result .= chr(ord(substr($str, $i, 1)) ^ ($list[$i % strlen($key)]));
    }

    return $result;
}
