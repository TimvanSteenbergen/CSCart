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
    if ($mode == 'notify') {
        $order_id = $_REQUEST['order_id'];
        $order_info = fn_get_order_info($order_id);

        $pp_response = array(
            'reason_text' => '',
            'order_status' => 'F'
        );
        if (!empty($_REQUEST['mm_status'])) {
            $pp_response['order_status'] = ($_REQUEST['mm_status'] == 'success') ? "P" : "D";
            $pp_response['reason_text'] .= "Status: $_REQUEST[mm_status]; ";
        }

        if (!empty($_REQUEST['mm_transid'])) {
            $pp_response['transaction_id'] = $_REQUEST['mm_transid'];
        }
        if (!empty($_REQUEST['mm_checkNo'])) {
            $pp_response['reason_text'] .= "CheckNumber: $_REQUEST[mm_checkNo]; ";
            if ($order_info['payment_info']['check_number'] != $_REQUEST['mm_checkNo']) {
                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] .= 'CheckNumber does not match; ';
            }

        }
        if (!empty($_REQUEST['mm_msg'])) {
            $pp_response['reason_text'] .= "Reason: $_REQUEST[mm_msg]; ";
        }
        if (!empty($_REQUEST['mm_excp'])) {
            $pp_response['reason_text'] .= "Exception: $_REQUEST[mm_excp]; ";
        }
        if (!empty($_REQUEST['mm_code'])) {
            $pp_response['reason_text'] .= "ErrorCode: $_REQUEST[mm_code]; ";
        }

        if (fn_check_payment_script('p21_simple_api.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response);
            fn_order_placement_routines('route', $order_id);
        }
    }

} else {
    $_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;
    $return_url = fn_url("payment_notification.notify?payment=p21_simple_api&order_id=$order_id", AREA, 'current');
    $birth_date = date("m/d/Y", fn_parse_date($order_info['payment_info']['date_of_birth']));

    $post_data = array(
        'mm_userid' => $processor_data['processor_params']['merchant_id'],
        'mm_pwd' => $processor_data['processor_params']['password'],
        'mm_ip_address' => $processor_data['processor_params']['ip_address'],
        'mm_company' => $processor_data['processor_params']['company'],
        'mm_redirecturl' => $return_url,
        'mm_errorurl' => $return_url,
        'mm_updatedby' => 'xxxx',
        'mm_merchantcustomerid' => $order_info['user_id'],
        'mm_merchanttransid' => $_order_id,
        'mm_firstname' => $order_info['b_firstname'],
        'mm_lastname' => $order_info['b_lastname'],
        'mm_dateofbirth' => $birth_date,
        'mm_address' => $order_info['b_address'],
        'mm_address2' => $order_info['b_address_2'],
        'mm_last4ssn' => $order_info['payment_info']['last4ssn'],
        'mm_city' => $order_info['b_city'],
        'mm_state' => $order_info['b_state'],
        'mm_zipcode' => $order_info['b_zipcode'],
        'mm_country' => $order_info['b_country'],
        'mm_phone' => $order_info['payment_info']['phone'],
        'mm_email' => $order_info['email'],
        'mm_amount' => $order_info['total'],
        'mm_routingcode' => $order_info['payment_info']['routing_code'],
        'mm_accountnr' => $order_info['payment_info']['account_number'],
        'mm_checknr' => $order_info['payment_info']['check_number'],
        'mm_passportnr' => $order_info['payment_info']['passport_number'],
        'mm_driverlicensenr' => $order_info['payment_info']['drlicense_number'],
        'mm_agree' => $order_info['payment_info']['mm_agree'],
        'mm_TOC' => '1'        
    );

    fn_create_payment_form('https://www.payment21.com/interfaces/mmltdonline/p21paybycheck/default.aspx', $post_data, 'Payment21');
}
exit;
