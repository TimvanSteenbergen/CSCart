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
    require './init_payment.php';

    $order_id = (strpos($_REQUEST['strCartID'], '_')) ? substr($_REQUEST['strCartID'], 0, strpos($_REQUEST['strCartID'], '_')) : $_REQUEST['strCartID'];

    if (!isset($_REQUEST['intAccountID'])) {
        fn_order_placement_routines('route', $order_id);
    } else {
        $pp_response = array();

        if (empty($_REQUEST['intStatus'])) {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('failed');

        } elseif ($_REQUEST['intStatus'] == 1) {
            $pp_response['order_status'] = 'P';
            $pp_response['reason_text'] = __('order_id') . '-' . $order_id;

        } else {
            $pp_response['order_status'] = 'N';
            $pp_response['reason_text'] = __('cancelled');
        }

        $pp_response['transaction_id'] = $_REQUEST['intTransID'];

        if (fn_check_payment_script('mcpe.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response);
        }
    }
} else {
    $test = $processor_data['processor_params']['mode'];
    $_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;
    $submit_url = 'https://secure.metacharge.com/mcpe/purser';
    $post_data = array(
        'intTestMode' => $test,
        'intInstID' => $processor_data['processor_params']['merchant_id'],
        'strCartID' => $_order_id,
        'fltAmount' => $order_info['total'],
        'strCurrency' => $processor_data['processor_params']['currency'],
        'strDesc' => "Payment for Order {$order_id}",
    );

    fn_create_payment_form($submit_url, $post_data, 'metacharge.com server');
}
exit;
