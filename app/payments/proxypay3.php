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

$totals_100 = array('EUR', 'USD', 'GBP', 'CHF', 'NLG', 'DEM', 'FRF', 'ATS');

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'notify') {
        if ($action == 'ok') {
            $__status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
            $pp_response = array();
            $pp_response['order_status'] = $__status;
            $pp_response['reason_text'] = __('order_id') . '-' . $_REQUEST['order_id'];

            if (fn_check_payment_script('proxypay3.php', $_REQUEST['order_id'])) {
                fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
            }

            fn_order_placement_routines('route', $_REQUEST['order_id']);

        } elseif ($action == 'nok') {
            if (empty($_REQUEST['order_id'])) {
                fn_set_notification('E', __('error'), __('connection_error'));
                fn_order_placement_routines('checkout_redirect');
            } else {
                $pp_response = array(
                    'order_status' => 'D',
                    'reason_text' => 'Error in data validation',
                );

                fn_finish_payment($_REQUEST['order_id'], $pp_response, false);
                fn_order_placement_routines('route', $_REQUEST['order_id']);
            }
        }
    }
} else {

    $lang = (CART_LANGUAGE == 'el') ? 'GR' : 'EN';

    if (in_array($processor_data['processor_params']['currency'], $totals_100)) {
        $total_cost = $order_info['total'];
    } else {
        $total_cost = $order_info['total'] * 100;
    }

    $post_data = array(
        'APACScommand' => 'NewPayment',
        'merchantID' => $processor_data['processor_params']['merchantid'],
        'amount' => $total_cost,
        'merchantRef' => $order_id,
        'merchantDesc' => $processor_data['processor_params']['details'],
        'currency' => $processor_data['processor_params']['currency'],
        'lang' => $lang,
        'CustomerEmail' => $order_info['email'],        
    );

    fn_create_payment_form('https://' . $processor_data['processor_params']['url'], $post_data, 'Eurobank');
}
exit;
