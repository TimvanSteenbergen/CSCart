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

if (defined('PAYMENT_NOTIFICATION')) {
    // Get the password
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
    $processor_data = fn_get_payment_method_data($payment_id);

    $transid = $_REQUEST['TransID'];
    $status  = $_REQUEST['Status'];
    $amount  = $_REQUEST['Amount'];
    $crypt   = $_REQUEST['Crypt'];

    // need to verify the integrity of the parameters to ensure they are not spoofed
    $cryptcheck = md5($status . $transid . $amount . $processor_data['processor_params']['password']);

    if ($status == 'Success' && ($crypt == $cryptcheck)) {
        $pp_response['order_status'] = ($processor_data['processor_params']['transaction_type'] == 'PAYMENT') ? 'P' : 'O';
        $pp_response['reason_text'] = 'Payment Approved';
        $pp_response['transaction_id'] = $transid;
    } else {
        if ($status == 'Fail') {
            $pp_response['order_status'] = 'D';
            $pp_response['reason_text'] = 'Status: Declined';

        } elseif ($crypt != $cryptcheck) {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = "Status: Password Check Failed $crypt $cryptcheck ";

        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = 'Status: Problem with confirming payment';
        }
    }

    fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
    fn_order_placement_routines('route', $_REQUEST['order_id']);

} else {
    $post_address = ($processor_data['processor_params']['testmode'] != "N") ? "https://testsecure.totalwebsecure.com/paypage/clear.asp" : "https://secure.totalwebsecure.com/paypage/clear.asp";

    $failed_url = fn_url("payment_notification.notify?payment=totalwebpp&order_id={$order_id}", AREA, 'current');
    $success_url = fn_url("payment_notification.notify?payment=totalwebpp&order_id={$order_id}", AREA, 'current');

    $post_data = array(
        'CustomerID' => $processor_data['processor_params']['vendor'],
        'Notes' => $processor_data['processor_params']['order_prefix'] . $order_id,
        'TransactionAmount' => $order_info['total'],
        'Amount' => $order_info['total'],
        'TransactionCurrency' => $processor_data['processor_params']['currency'],
        'redirectorfailed' => $failed_url,
        'PayPageType' => '4',
        'redirectorsuccess' => $success_url,
        'CustomerEmail' => $order_info['email'],        
    );

    fn_create_payment_form($post_address, $post_data, 'Total Web Solutions Pay Page');
}

exit;
