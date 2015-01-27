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

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$tvp_responsess = array(
    'YMYOK' => 'All fields checked were the identical, transaction verified',
    'NMYMISSINGCREDITID' => ' Missing credit index',
    'NMYMISSINGDEBITID' => 'Missing debit index',
    'NMYINVALIDDEBITID' => 'Invalid debit index',
    'NMYINVALIDAMOUNT' => 'Invalid total amount',
    'NMYALREADYVERIFIED' => 'Transaction already verified',
    'NMYALREADYREJECTED' => 'Transaction already rejected',
    'NMYINVALIDMSG' => 'Optional message too long or invalid',
    'NMYINPROGRESS' => 'Transaction is in progress',
    'NMYSYSNOTAVAIL' => 'System not available, try again later',
    'NMYINITERROR' => 'Internal error'
);

if (defined('PAYMENT_NOTIFICATION')) {

    if ($mode == 'notify') {

        $order_info = fn_get_order_info($_REQUEST['order_id']);
        if ($order_info['status'] == 'O') {
            $pp_response = array();
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = 'No response recieved';
            fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
        }

        fn_order_placement_routines('route', $_REQUEST['order_id']);

    } elseif ($mode == 'tvp') {
        $msg = __('epassporte_msg');

        $pp_response = array();
        $pp_response['order_status'] = (substr($_REQUEST['ans'], 0, 1) == 'Y') ? 'P' : 'F';
        $pp_response['reason_text'] = __('order_id') . '-' . $_REQUEST['order_id'];
        $pp_response['transaction_id'] = $_REQUEST['credit_trans_idx'];

        if (fn_check_payment_script('epassporte.php', $_REQUEST['order_id'])) {
            fn_finish_payment($_REQUEST['order_id'], $pp_response);
        }

        $post_data = array(
            'credit_trans_idx' => $credit_trans_idx,
            'debit_trans_idx' => $debit_trans_idx,
            'total_amount' => $total_amount,
            'action' => 'verify',
            'msg' => $msg,            
        );

        fn_create_payment_form('https://www.epassporte.com/secure/eppurchaseverify.cgi', $post_data);
        exit;
    }

} else {

    $product_name = '';
    // Products
    if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $v) {
            $product_name = $product_name . $v['product'] . ";  ";
        }
    }
    // Gift Certificates
    if (!empty($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $v) {
            $product_name = $product_name . $v['gift_cert_code'] . ";  ";
        }
    }
    $product_name = substr($product_name, 0, 128);

    $tax_amount = (!empty($order_info['tax_subtotal'])) ? fn_format_price($order_info['tax_subtotal']) : 0;
    $shipping_amount = fn_order_shipping_cost($order_info);

    $current_location = Registry::get('config.current_location');
    $return_url = fn_url("payment_notification.notify?payment=epassporte&order_id=$order_id", AREA, 'current');
    $response_post = fn_url("payment_notification.tvp?payment=epassporte&order_id=$order_id", AREA, 'current');

    $post_data = array(
        'acct_num' => $processor_data['processor_params']['acct_num'],
        'pi_code' => $processor_data['processor_params']['pi_code'],
        'amount' => $order_info['subtotal'],
        'return_url' => $return_url,
        'response_post' => $response_post,
        'product_name' => $product_name,
        'tax_amount' => $tax_amount,
        'shipping_amount' => $shipping_amount,
    );

    fn_create_payment_form('https://www.epassporte.com/secure/eppurchase.cgi', $post_data, 'ePpayment');
}
exit;
