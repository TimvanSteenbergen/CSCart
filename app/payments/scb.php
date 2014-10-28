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

    $processor_response = array(
        "canceled" => "Customer canceled from SIPS Payment. No payment has been done.",
        "error" => "SIPS application error.",
        "inprocess" => "Transaction in process, waiting for creditcard approval result.",
        "approved" => "Creditcard authorization approved.",
        "declined" => "Creditcard authorization declined.",
        "outofservice" => "Outofservice.",
    );

    $processor_error = array (
        "002" => "Host Approve",
            "003" => "Host Reject",
            "006" => "Error",
            "007" => "SIPS is down",
            "008" => "SIPS is down",
    );

    if ($mode == 'process') {
        fn_order_placement_routines('route', $_REQUEST['order_id']);

    } elseif ($mode == 'result' || empty($mode)) {

        $order_id = (strpos($_REQUEST['ref_no'], '_')) ? substr($_REQUEST['ref_no'], 0, strpos($_REQUEST['ref_no'], '_')) : $_REQUEST['ref_no'];

        if (!empty($_REQUEST['payment_status']) && $_REQUEST['payment_status'] == '002') {
            $pp_response["order_status"] = 'P';
            $pp_response["reason_text"] = "Approval Code: " . $_REQUEST['appr_code'];

        } else {
            $pp_response["order_status"] = 'F';
            $pp_response["reason_text"] = "Response code: ";
            if (!empty($processor_error[$_REQUEST['payment_status']])) {
                $pp_response["reason_text"] .= $processor_error[$_REQUEST['payment_status']];
            } else {
                $pp_response["reason_text"] .= $_REQUEST['payment_status'];
            }
        }

        $pp_response['transaction_id'] = $_REQUEST['trans_no'];

        if (fn_check_payment_script('scb.php', $_REQUEST['order_id'])) {
            fn_finish_payment($_REQUEST['order_id'], $pp_response);
        }

        exit;
    }

} else {

$customer_url = fn_url("payment_notification.process?payment=scb&order_id=$order_id", AREA, 'current');
$_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
$today = date('Ymdhis');

$post_data = array(
    'mid' => $processor_data['processor_params']['merchant_id'],
    'terminal' => $processor_data['processor_params']['terminal_id'],
    'version' => '2_5_1',
    'command' => 'CRAUTH',
    'ref_no' => $_order_id,
    'ref_date' => $today,
    'service_id' => '00',
    'cust_id' => $order_info['user_id'],
    'cur_abbr' => $processor_data['processor_params']['currency'],
    'amount' => $order_info['total'],
    'cust_lname' => $order_info['lastname'],
    'cust_fname' => $order_info['firstname'],
    'cust_email' => $order_info['email'],
    'cust_country' => $order_info['b_country'],
    'cust_address1' => $order_info['b_address'],
    'description' => 'Shopping cart',
    'cust_address2' => $order_info['b_address_2'],
    'cust_city' => $order_info['b_city'],
    'cust_province' => $order_info['b_state'],
    'cust_zip' => $order_info['b_zipcode'],
    'backURL' => $customer_url,    
);

fn_create_payment_form('https://sips.scb.co.th/cc/webcredit/web_credit_payment.phtml', $post_data, 'SCB');
exit;
}
