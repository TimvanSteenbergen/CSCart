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

$processor_error = array(
    "A4" => "A link error has occurred between the bank and the modem.",
    "A5" => "The secure PIN Pad unit is not responding.",
    "A6" => "No free PIN Pad slots were available to service the transaction request.",
    "A7" => "A generic interface request specified an illegal value in 'Polled' field.",
    "A8" => "An invalid amount was specified.",
    "AA" => "An invalid card number was specified.",
    "AB" => "An account invalid value for account was specified",
    "AC" => "A past date was specified for expiry",
    "AD" => "The specified account is not available on the server.",
    "AE" => "A queued Authorisation timed-out.",
    "AF" => "A journal lookup did not find the requested transaction.",
    "U9" => "A valid response was not received in time from the Bank Host.",
    "W6" => "The function requested is not supported by the OCV servers bank.",
);

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_REQUEST['ewayTrxnStatus'])) {

    $order_info = fn_get_order_info($_REQUEST['order_id']);

    if (fn_strtolower($_REQUEST['ewayTrxnStatus']) == 'true' && (fn_format_price(str_replace(array('$',','), '', $_REQUEST['eWAYReturnAmount'])) == fn_format_price($order_info['total']))) {
        $pp_response['order_status'] = 'P';
        $pp_response["reason_text"] = $_REQUEST['eWAYresponseText'];
    } else {
        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = $_REQUEST['eWAYresponseText'] . ":" . @$processor_error[$_REQUEST['eWAYresponseCode']];
    }

    if (fn_strtolower($_REQUEST['eWAYoption3']) == 'true') {
        $pp_response["reason_text"] .= "; This is a TEST transaction";
    }

    $pp_response["transaction_id"] = $_REQUEST['ewayTrxnReference'];

    if (fn_check_payment_script('eway_form.php', $_REQUEST['order_id'])) {
        fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }

} else {
    $return_url = fn_url("payment_notification.notify?payment=eway_form&order_id=$order_id", AREA, 'current');
    $order_total = 100 * $order_info['total'];
    $testmode = ($processor_data['processor_params']['test']=='Y') ? "TRUE" : "FALSE";
    $_order_id = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id);

    $post_data = array(
        'ewayCustomerID' => $processor_data['processor_params']['client_id'],
        'ewayTotalAmount' => $order_total,
        'ewayCustomerInvoiceRef' => $_order_id,
        'ewayCustomerFirstName' => $order_info['firstname'],
        'ewayCustomerLastName' => $order_info['lastname'],
        'ewayCustomerEmail' => $order_info['email'],
        'ewayCustomerAddress' => $order_info['b_address'],
        'ewayCustomerPostcode' => $order_info['b_zipcode'],
        'ewayOption3' => $testmode,
        'ewayURL' => $return_url,
    );

    fn_create_payment_form('https://www.eway.com.au/gateway/payment.asp', $post_data, 'eWay');
    exit;
}
