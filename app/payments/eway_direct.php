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

$test_mode = ($processor_data['processor_params']['test'] == 'Y') ? 'TRUE' : '';
if ($processor_data['processor_params']['test'] == 'Y') {
    $request_script = ($processor_data['processor_params']['include_cvn'] == 'true') ? 'gateway_cvn/xmltest/testpage.asp' : 'gateway/xmltest/TestPage.asp';
} else {
    $request_script = ($processor_data['processor_params']['include_cvn'] == 'true') ? 'gateway_cvn/xmlpayment.asp' : 'gateway/xmlpayment.asp';
}
$_order_id = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id);

$payment_description = 'Products:';
// Products
if (!empty($order_info['products'])) {
    foreach ($order_info['products'] as $v) {
        $payment_description .= (preg_replace('/[^\w\s]/i', '', $v['product']) ."; amount=" . $v['amount'] . ";");
    }
}
// Gift Certificates
if (!empty($order_info['gift_certificates'])) {
    foreach ($order_info['gift_certificates'] as $v) {
        $payment_description .= ($v['gift_cert_code'] ."; amount=1;");
    }
}

$post = array(
    'ewaygateway' => array(
        'ewayCustomerID' => $processor_data['processor_params']['client_id'],
        'ewayTotalAmount' => (100*$order_info['total']),
        'ewayCustomerFirstName' => $order_info['b_firstname'],
        'ewayCustomerLastName' => $order_info['b_lastname'],
        'ewayCustomerEmail' => $order_info['email'],
        'ewayCustomerAddress' => $order_info['b_address'],
        'ewayCustomerPostcode' => $order_info['b_zipcode'],
        'ewayCustomerInvoiceDescription' => $payment_description,
        'ewayCustomerInvoiceRef' => $_order_id,
        'ewayCardHoldersName' => $order_info['payment_info']['cardholder_name'],
        'ewayCardNumber' => $order_info['payment_info']['card_number'],
        'ewayCardExpiryMonth' => $order_info['payment_info']['expiry_month'],
        'ewayCardExpiryYear' => $order_info['payment_info']['expiry_year'],
        'ewayTrxnNumber' => '',
        'ewayOption1' => '',
        'ewayOption2' => '',
        'ewayOption3' => $test_mode
    )
);

if ($processor_data['processor_params']['include_cvn'] == 'true' && !empty($order_info['payment_info']['cvv2'])) {
    $post['ewaygateway']['ewayCVN'] = $order_info['payment_info']['cvv2'];
}

Registry::set('log_cut_data', array('ewayCardNumber', 'ewayCardExpiryMonth', 'ewayCardExpiryYear'));

$return = Http::post("https://www.eway.com.au/" . $request_script, fn_array_to_xml($post), array(
        'headers' => array(
            'Content-type: text/xml'
        )
    ));

preg_match("/<ewayTrxnStatus>(.*)<\/ewayTrxnStatus>/", $return, $result);
preg_match("/<ewayReturnAmount>(.*)<\/ewayReturnAmount>/", $return, $amount);

if ($result[1] == "True" && fn_format_price($amount[1]) == fn_format_price($order_info['total'] * 100)) {
    $pp_response['order_status'] = 'P';
    preg_match("/<ewayAuthCode>(.*)<\/ewayAuthCode>/", $return, $authno);
    $pp_response["reason_text"] = "AuthNo: ".$authno[1];

} else {
    $pp_response['order_status'] = 'F';
    preg_match("/<ewayTrxnError>(.*)<\/ewayTrxnError>/", $return, $error);
    if (!empty($error[1])) {
        $pp_response["reason_text"] = "Error:" .$error[1];
    }
}
preg_match("/<ewayTrxnNumber>(.*)<\/ewayTrxnNumber>/", $return, $transaction_id);
if (!empty($transaction_id[1])) {
    $pp_response["transaction_id"] = @$transaction_id[1];
}
preg_match("/<ewayOption3>(.*)<\/ewayOption3>/", $return, $test);
if (!empty($test[1])) {
    $pp_response["reason_text"] .= "; This is a TEST transaction";
}
