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

if (defined('PAYMENT_NOTIFICATION')) {

    if ($mode == 'notify') {
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }

} elseif (empty($processor_data)) {

    if ($_REQUEST['txnRef']) {
        require './init_payment.php';

        $order_id = (strpos($_REQUEST['txnRef'], '_')) ? substr($_REQUEST['txnRef'], 0, strpos($_REQUEST['txnRef'], '_')) : $_REQUEST['txnRef'];
        $pp_response = array();
        $pp_response['order_status'] = ($_REQUEST['status'] == 'succ') ? 'P' : 'F';
        $pp_response['reason_text'] = __('order_id') . '-' . $order_id;
        $pp_response['transaction_id'] = '';

        if (fn_check_payment_script('enets.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    }

} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $submit_url = 'https://www.enets.sg/enets2/enps.do';
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    $post_data = array(
        'txnRef' => $_order_id,
        'mid' => $processor_data['processor_params']['merchantid'],
        'amount' => $order_info['total'],
        'umapiType' => 'lite',
    );

    fn_create_payment_form($submit_url, $post_data, 'eNPS server');
}
exit;
