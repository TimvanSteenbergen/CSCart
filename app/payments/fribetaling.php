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
// Order=1786&Amount=4396&Currency=EUR&Transno=15600&Validatemd=A074BB0B7FC7DB5C1C9E03F9F2013F8B
// Order=1788&Amount=4396&Currency=EUR&Transno=15601

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'result') {

        if (!empty($_REQUEST['Validatemd'])) {
            $order_info = fn_get_order_info($_REQUEST['order_id']);

            $md5_string = '';
            if (!empty($processor_data['processor_params'])) {
                $md5_string = strtoupper(md5($_REQUEST['Order'] . $_REQUEST['Amount'] . $_REQUEST['Currency'] . $_REQUEST['Transno'] . $processor_data['processor_params']['mac_key']));
            }

            if ($_REQUEST['Validatemd'] == $md5_string) {
                $pp_response['order_status'] = 'P';

            } else {
                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = 'MD5 string is not accepted';
            }
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('text_transaction_declined');

            if (!empty($_REQUEST['Error'])) {
                $pp_response['reason_text'] .= " (" . $_REQUEST['Error'] . ")";
            }
        }

        $pp_response["transaction_id"] = $_REQUEST['Transno'];

        if (fn_check_payment_script('fribetaling.php', $_REQUEST['order_id'])) {
            fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
            fn_order_placement_routines('route', $_REQUEST['order_id']);
        }
    }

} else {
    $total = $order_info['total'] * 100;
    $r_url = fn_url("payment_notification.result?payment=fribetaling&order_id=$order_id", AREA, 'current');
    $expmm = $order_info['payment_info']['expiry_month'];
    $expyy = $order_info['payment_info']['expiry_year'];
    $_order_id = ($order_info['repaid']) ? ($order_id .'z'. $order_info['repaid']) : $order_id;

    $post_data = array(
        'Amount' => $total,
        'Currency' => $processor_data['processor_params']['currency'],
        'Decline' => $r_url,
        'Accept' => $r_url,
        'Cardnumber' => $order_info['payment_info']['card_number'],
        'CVC' => $order_info['payment_info']['cvv2'],
        'Expmm' => $expmm,
        'Expyy' => $expyy,
        'Merchant' => $processor_data['processor_params']['merchant_id'],
        'Ordernumber' => $order_id,
    );

    if ($processor_data["processor_params"]["mode"] == 'A') {
        $post_data['Testtransaction'] = 'A';
    } elseif ($processor_data["processor_params"]["mode"] == 'D') {
        $post_data['Testtransaction'] = 'D';
    }

    fn_create_payment_form('https://pgw.fribetaling.dk/betal.fri', $post_data, 'FRIbetaling');
    exit;
}
