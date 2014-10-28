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

if (!defined('BOOTSTRAP')) {
    $avserr = array(
            "X" => "Exact match - 9 digit zip",
            "Y" => "Exact match - 5 digit zip",
            "A" => "Address match only",
            "W" => "9-digit zip match only",
            "Z" => "5-digit zip match only",
            "N" => "No address or zip match",
            "U" => "Address unavailable",
            "G" => "Non-U.S. Issuer",
            "R" => "Issuer system unavailable"
    );

    if (!empty($_REQUEST['RefNo']) && !empty($_REQUEST['Auth'])) {
        require './init_payment.php';

        $order_id = (strpos($_REQUEST['RefNo'], '_')) ? substr($_REQUEST['RefNo'], 0, strpos($_REQUEST['RefNo'], '_')) : $_REQUEST['RefNo'];

        if ($_REQUEST['Auth'] != "Declined") {
            $pp_response['order_status'] = 'P';
            $pp_response["reason_text"] = "AuthCode: " . $_REQUEST['Auth'];

        } else {
            $pp_response['order_status'] = 'F';
            $pp_response["reason_text"] = $_REQUEST['Auth']. ": " . $_REQUEST['Notes'];
        }

        if (!empty($_REQUEST['TransID'])) {
            $pp_response["transaction_id"] = $_REQUEST['TransID'];
        }

        if (!empty($_REQUEST['AVSCode'])) {
            $pp_response["descr_avs"] = empty($avserr[$_REQUEST['AVSCode']]) ? "AVS Code: " . $_REQUEST['AVSCode'] : $avserr[$_REQUEST['AVSCode']];
        }

        if (fn_check_payment_script('pri_form.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    } else {
        die('Access denied');
    }
} else {
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    $return_url = fn_payment_url('current', 'pri_form.php');
    $submit_url = 'https://webservices.primerchants.com/billing/TransactionCentral/EnterTransaction.asp';
    $post_data = array(
        'MerchantID' => $processor_data['processor_params']['merchant_id'],
        'RegKey' => $processor_data['processor_params']['key'],
        'Amount' => $order_info['total'],
        'AVSADDR' => $order_info['b_address'],
        'AVSZIP' => $order_info['b_zipcode'],
        'REFID' => $_order_id,
        'RURL' => $return_url,
        'TransType' => 'CC',
    );

    fn_create_payment_form($submit_url, $post_data, 'PRI server');
exit;
}
