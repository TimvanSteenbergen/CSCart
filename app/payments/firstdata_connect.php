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
    $pp_response['order_status'] = ($_REQUEST['status'] == 'APPROVED') ? 'P' : 'F';
    $pp_response['reason_text'] = $_REQUEST['approval_code'];
    $pp_response['transaction_id'] = substr($_REQUEST['approval_code'], strrpos(substr($_REQUEST['approval_code'], 0, strlen($_REQUEST['approval_code']) - 1), ":") + 1);
    $pp_response['transaction_id'] = rtrim($pp_response['transaction_id'], ":");

    if (!empty($_REQUEST['failReason'])) {
        $pp_response['reason_text'] .= " Error: " . $_REQUEST['failReason'];
    }

    if (fn_check_payment_script('firstdata_connect.php', $_REQUEST['order_id'])) {
        fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }

} else {
    if ($processor_data['processor_params']['test'] == 'LIVE') {
        $post_address = "https://secure.linkpt.net/lpcentral/servlet/lppay";
    } else {
        $post_address = "https://www.staging.linkpointcentral.com/lpc/servlet/lppay";
    }
    $_order_id = (($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id) . '_' . fn_date_format(time(), '%H_%M_%S');

    $response_url = fn_url("payment_notification&payment=firstdata_connect&order_id=$order_id", AREA, 'http');

    $post_data = array(
        'responseURL' => $response_url,
        'storename' => $processor_data['processor_params']['store'],
        'chargetotal' => $order_info['total'],
        'txnorg' => 'eci',
        'mode' => 'fullpay',
        'txntype' => $processor_data['processor_params']['transaction_type'],
        'bname' => $order_info['firstname'] . ' ' . $order_info['lastname'],
        'oid' => $processor_data['processor_params']['prefix'] . $_order_id,
        'baddr1' => $order_info['b_address'],
        'baddr2' => $order_info['b_address_2'],
        'bcity' => $order_info['b_city'],
        'bstate' => $order_info['b_state'],
        'bcountry' => $order_info['b_country'],
        'bzip' => $order_info['b_zipcode'],
        'sname' => $order_info['firstname'] . ' ' . $order_info['lastname'],
        'saddr1' => $order_info['s_address'],
        'saddr2' => $order_info['s_address_2'],
        'scity' => $order_info['s_city'],
        'sstate' => $order_info['s_state'],
        'scountry' => $order_info['s_country'],
        'szip' => $order_info['s_zipcode'],
        'phone' => $order_info['phone'],
        'fax' => $order_info['fax'],
        'email' => $order_info['email']
    );

    fn_create_payment_form($post_address, $post_data, 'FirstData');
}
exit;
