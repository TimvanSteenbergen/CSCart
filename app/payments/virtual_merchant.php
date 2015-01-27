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
    $processor_error = array (
        'status' => array(
            'APPROVAL' => 'P',
            'APPROVED' => 'P',
            'ACCEPTED' => 'O',
            'BAL.: 99999999.99' => 'O',
            'PICK UP CARD' => 'O',
            'AMOUNT ERROR' => 'D',
            'APPL TYPE ERROR' => 'O',
            'DECLINED' => 'D',
            'DECLINED-HELP 9999' => 'F',
            'EXCEEDS BAL.' => 'D',
            'EXPIRED CARD' => 'D',
            'INVALID CARD' => 'D',
            'INCORRECT PIN' => 'F',
            'INVALID TERM ID' => 'F',
            'INVLD TERM ID 1' => 'F',
            'INVLD TERM ID 2' => 'F',
            'INVLD VOID DATA' => 'F',
            'MUST SETTLE MMDD' => 'F',
            'ON FILE' => 'D',
            'RECORD NOT FOUND' => 'F',
            'FOUND SERV NOT ALLOWED' => 'F',
            'SEQ ERR PLS CALL' => 'O',
            'CALL AUTH.' => 'O',
            'CENTER CALL REF.; 999999' => 'O',
            'DECLINE CVV2' => 'D'
        ),
        'result' => array(
            'APPROVAL' => 'Approved',
            'APPROVED' => 'Approved',
            'ACCEPTED' => 'Frequency Approval',
            'BAL.: 99999999.99' => 'Debit Card Balance Inquiry Response',
            'PICK UP CARD' => 'Pick up card',
            'AMOUNT ERROR' => 'Tran Amount Error',
            'APPL TYPE ERROR' => 'Call for Assistance',
            'DECLINED' => 'Do Not Honor',
            'DECLINED-HELP 9999' => 'System Error',
            'EXCEEDS BAL.' => 'Req. exceeds balance',
            'EXPIRED CARD' => 'Expired Card',
            'INVALID CARD' => 'Invalid Card',
            'INCORRECT PIN' => 'Invalid PIN',
            'INVALID TERM ID' => 'Invalid Terminal ID',
            'INVLD TERM ID 1' => 'Invalid Merchant Number',
            'INVLD TERM ID 2' => 'Invalid SE Number',
            'INVLD VOID DATA' => 'Invalid Data',
            'MUST SETTLE MMDD' => 'Must settle POS Device, open batch is more than 7 days old.',
            'ON FILE' => 'Cardholder not found',
            'RECORD NOT FOUND' => 'Record not on Host',
            'FOUND SERV NOT ALLOWED' => 'Invalid request',
            'SEQ ERR PLS CALL' => 'Call for Assistance',
            'CALL AUTH.' => 'Refer to Issuer',
            'CENTER CALL REF.; 999999' => 'Refer to Issuer',
            'DECLINE CVV2' => 'Do Not Honor; Declined due to CVV2 mismatch/failure'
        ),
        'avs' => array(
            'A' => 'Address (Street) matches, ZIP does not',
            'E' => 'AVS error',
            'N' => 'No Match on Address (Street) or ZIP',
            'P' => 'AVS not applicable for this transaction',
            'R' => 'Retry. System unavailable or timed out',
            'S' => 'Service not supported by issuer',
            'U' => 'Address information is unavailable',
            'W' => '9 digit ZIP matches, Address (Street) does not',
            'X' => 'Exact AVS Match',
            'Y' => 'Address (Street) and 5 digit ZIP match',
            'Z' => '5 digit ZIP matches, Address (Street) does not'
        ),
        'cvv' => array(
            'M' => 'Match',
            'N' => 'No Match',
            'P' => 'Not Processed',
            'S' => 'Should have been present',
            'U' => 'Issuer unable to process request'
        )
    );

    $pp_response = array();

    if ($mode == 'notify' && !empty($_REQUEST['order_id']) && !empty($_REQUEST['ssl_result_message']) && isset($_REQUEST['ssl_result'])) {

        $pp_response['order_status'] = $processor_error['status'][$_REQUEST['ssl_result_message']];
        $pp_response['reason_text'] = $processor_error['result'][$_REQUEST['ssl_result_message']];
        $pp_response['transaction_id'] = $_REQUEST['ssl_txn_id'];
        $pp_response['descr_avs'] = $processor_error['avs'][$_REQUEST['ssl_avs_response']];
        $pp_response['descr_cvv'] = $processor_error['cvv'][$_REQUEST['ssl_cvv2_response']];

    } elseif ($mode == 'error' && !empty($_REQUEST['order_id'])) {

        $pp_response['order_status'] = 'F';
        $pp_response['errorCode'] = str_replace('?', '', $_REQUEST['?errorCode']);
        $pp_response['errorName'] = $_REQUEST['errorName'];
        $pp_response['reason_text'] = $_REQUEST['errorMsg'];

    } else {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] =  __('error');;
    }

    if (fn_check_payment_script('virtual_merchant.php', $_REQUEST['order_id'])) {
        fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
    }

    fn_order_placement_routines('route', $_REQUEST['order_id']);

} else {

    $post_data['ssl_invoice_number'] = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;
    $post_data['ssl_merchant_id'] = $processor_data['processor_params']['merchant_id'];
    $post_data['ssl_user_id'] = $processor_data['processor_params']['user_id'];
    $post_data['ssl_pin'] = $processor_data['processor_params']['user_pin'];
    $post_data['ssl_customer_code'] = $order_info['user_id'] ? $order_info['user_id'] : 'n/a';
    $post_data['ssl_salestax'] = fn_format_price($order_info['tax_subtotal']);
    $post_data['ssl_description'] = $processor_data['processor_params']['order_prefix'].$order_id;
    $post_data['ssl_test_mode'] = $processor_data['processor_params']['mode'] == 'live' ? 'FALSE' : 'TRUE';
    $post_data['ssl_receipt_link_url'] = fn_url("payment_notification.notify?payment=virtual_merchant&order_id=$order_id&", AREA, 'http');
    $post_data['ssl_error_url'] = fn_url("payment_notification.error?payment=virtual_merchant&order_id=$order_id&", AREA, 'http');
    $post_data['ssl_receipt_link_method'] = 'REDG';
    $post_data['ssl_amount'] = $order_info['total'];
    $post_data['ssl_transaction_type'] = 'ccSALE';
    $post_data['ssl_card_number'] = $order_info['payment_info']['card_number'];
    $post_data['ssl_exp_date'] = $order_info['payment_info']['expiry_month'] . '' . $order_info['payment_info']['expiry_year'];
    $post_data['ssl_company'] = $order_info['company'];
    $post_data['ssl_first_name'] = $order_info['b_firstname'];
    $post_data['ssl_last_name'] = $order_info['b_lastname'];
    $post_data['ssl_avs_address'] = $order_info['b_address'];
    $post_data['ssl_city'] = $order_info['b_city'];
    $post_data['ssl_state'] = $order_info['b_state'] ? $order_info['b_state'] : 'n/a';
    $post_data['ssl_avs_zip'] = fn_vm_process_zip($order_info['b_zipcode']);
    $post_data['ssl_country'] = $order_info['b_country'];
    $post_data['ssl_phone'] = $order_info['phone'];
    $post_data['ssl_email'] = $order_info['email'];
    $post_data['ssl_ship_to_company'] = $order_info['company'];
    $post_data['ssl_ship_to_first_name'] = $order_info['s_firstname'];
    $post_data['ssl_ship_to_last_name'] = $order_info['s_lastname'];
    $post_data['ssl_ship_to_address'] = $order_info['s_address'];
    $post_data['ssl_ship_to_city'] = $order_info['s_city'];
    $post_data['ssl_ship_to_state'] = $order_info['s_state'] ? $order_info['s_state'] : 'n/a';
    $post_data['ssl_ship_to_country'] = $order_info['s_country'];
    $post_data['ssl_ship_to_zip'] = fn_vm_process_zip($order_info['s_zipcode']);
    $post_data['ssl_show_form'] = 'FALSE';

    if ($processor_data['processor_params']['avs'] == 'Y') {
        $post_data['ssl_avs_address'] = $order_info['b_address'];
        $post_data['ssl_avs_zip'] = fn_vm_process_zip($order_info['b_zipcode']);
    }
    if ($processor_data['processor_params']['cvv2'] && !empty($order_info['payment_info']['cvv2'])) {
        $post_data['ssl_cvv2cvc2_indicator'] = '1';
        $post_data['ssl_cvv2cvc2'] = $order_info['payment_info']['cvv2'];
    }

    $post_url = ($processor_data['processor_params']['mode'] != 'demo')? "https://www.myvirtualmerchant.com/VirtualMerchant/process.do" : "https://demo.myvirtualmerchant.com/VirtualMerchantDemo/process.do";

    fn_create_payment_form($post_url, $post_data, 'Virtual Merchant');
    exit;
}

function fn_vm_process_zip($str)
{
    if (!empty($str)) {
        $str = preg_replace('/[^0-9]/', '', $str);
    }

    return $str;
}
