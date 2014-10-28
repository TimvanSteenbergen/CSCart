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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$response_mess = array(
    "0000" => "Transaction authorized for payments and pre-authorizations",
    "0099" => "Transaction authorized for payments and pre-authorizations",
    "0900" => "Transaction authorized for refunds and confirmations",
    "0101" => "Card expired",
    "0102" => "Card temporarily suspended or under suspicion of fraud",
    "0104" => "Transaction not allowed for the card or terminal",
    "0116" => "Insufficient funds",
    "0118" => "Card not registered",
    "0129" => "Security code (CVV2/CVC2) incorrect",
    "0180" => "Card not recognized",
    "0184" => "Cardholder authentication failed",
    "0190" => "Transaction declined without explanation",
    "0191" => "Wrong expiration date",
    "0202" => "Card temporarily suspended or under suspicion of fraud with confiscation order",
    "0912" => "Issuing bank not available",
    "9912" => "Issuing bank not available"
);

if (defined('PAYMENT_NOTIFICATION')) {
    $order_id = !empty($_REQUEST['order_id']) ? substr($_REQUEST['order_id'], 0, -2) : '';
    if ($mode == 'notify') {
        fn_order_placement_routines('route', $order_id, false);

    } elseif ($mode == 'result') {
        // Get the processor data
        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_payment_method_data($payment_id);
        $order_info = fn_get_order_info($order_id);

        $currency = $processor_data['processor_params']['currency'];
        $merchant = $processor_data['processor_params']['merchant_id'];
        $terminal = $processor_data['processor_params']['terminal'];
        $clave = $processor_data['processor_params']['clave'];
        if (strlen($order_id) > 6) {
            $order_n = $_REQUEST['order_id'] . (($order_info['repaid']) ? ('x'. $order_info['repaid']) : '');
        } else {
            $order_n = str_repeat('0', 6 - strlen($order_id)) . $_REQUEST['order_id'] . (($order_info['repaid']) ? ('x'. $order_info['repaid']) : '');
        }
        $amount = ($currency == '978') ? ($order_info['total'] * 100) : $order_info['total'];
        $signature = strtoupper(sha1($amount . $order_n . $merchant . $currency . $_REQUEST['Ds_Response'] . $clave));

        $pp_response = array();
        $pp_response['order_status'] = (($_REQUEST['Ds_Response'] == '0000' || $_REQUEST['Ds_Response'] == '0099') && $_REQUEST['Ds_Signature'] == $signature) ? 'P' : 'F';
        $pp_response['reason_text'] = $response_mess[$_REQUEST['Ds_Response']];
        if ($pp_response['order_status'] == 'P') {
            $pp_response['transaction_id'] = $_REQUEST['Ds_AuthorisationCode'];
        }

        fn_finish_payment($order_id, $pp_response);
        exit;

    } elseif ($mode == 'failed') {
        if (!empty($order_id) && fn_check_payment_script('servired.php', $order_id)) {
            $pp_response = array(
                'order_status' => 'F',
                'reason_text' => __('text_transaction_declined')
            );
            fn_finish_payment($order_id, $pp_response);
            fn_order_placement_routines('route', $order_id);
        }
        exit;
    }
} else {

    $post_address = ($processor_data['processor_params']['test'] == 'Y') ? "https://sis-t.sermepa.es:25443/sis/realizarPago" : "https://sis.sermepa.es/sis/realizarPago";

/*
Transaction types
 0 - Authorization
 1 - Pre-authorization
 2 - Confirmation
 3 -Automatic Refund
 4 - Payment by Cell Phone
 5 - Recurrent Transaction
 6 - Successive Transaction
 7 - Authentication
 8 - Confirmation of Authentication
*/

$currency = $processor_data['processor_params']['currency'];
$merchant = $processor_data['processor_params']['merchant_id'];
$terminal = $processor_data['processor_params']['terminal'];
$transaction_type = 0; // authorization
$clave = $processor_data['processor_params']['clave'];

$postfix = fn_date_format(time(), '%S');

if (strlen($order_id) > 6) {
    $order_n = $order_id . $postfix . (($order_info['repaid']) ? ('x' . $order_info['repaid']) : '');
} else {
    $order_n = str_repeat('0', 6 - strlen($order_id)) . $order_id . $postfix . (($order_info['repaid']) ? ('x' . $order_info['repaid']) : '');
}

$amount = ($currency == '978') ? ($order_info['total'] * 100) : $order_info['total'];

$url_merchant = fn_url("payment_notification.result?payment=servired&order_id=$order_id$postfix", AREA, 'current');
$url_ok = fn_url("payment_notification.notify?payment=servired&order_id=$order_id$postfix", AREA, 'current');
$url_nok = fn_url("payment_notification.failed?payment=servired&order_id=$order_id$postfix", AREA, 'current');

// Compute hash to sign form data
$message = $amount . $order_n . $merchant . $currency . $transaction_type . $url_merchant . $clave;
$signature = strtoupper(sha1($message));

$post_data = array(
    'Ds_Merchant_Amount' => $amount,
    'Ds_Merchant_Currency' => $currency,
    'Ds_Merchant_Order' => $order_n,
    'Ds_Merchant_MerchantCode' => $merchant,
    'Ds_Merchant_Terminal' => $terminal,
    'Ds_Merchant_TransactionType' => $transaction_type,
    'Ds_Merchant_MerchantURL' => $url_merchant,
    'Ds_Merchant_UrlOK' => $url_ok,
    'Ds_Merchant_UrlKO' => $url_nok,
    'Ds_Merchant_MerchantSignature' => $signature,    
);

fn_create_payment_form($post_address, $post_data, 'SERMEPA');
}
exit;
