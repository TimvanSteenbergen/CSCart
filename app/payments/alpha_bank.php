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
	$pp_response = array();
	$pp_response['order_status'] = 'F';
	$pp_response['reason_text'] = __('text_transaction_declined');
    $order_id = !empty($_REQUEST['order_id']) ? (int)$_REQUEST['order_id'] : 0;

    if ($mode == 'success' && !empty($_REQUEST['order_id'])) {
		$order_info = fn_get_order_info($order_id);
	
		if (empty($processor_data)) {
			$processor_data = fn_get_processor_data($order_info['payment_id']);
		}
	
        $post_data = array();
        $post_data_values = array(
            'mid',
            'orderid',
            'status',
            'orderAmount',
            'currency',
            'paymentTotal',
            'riskScore',
            'payMethod',
            'txId',
            'paymentRef'
        );

        foreach ($post_data_values as $post_data_value) {
            if (isset($_REQUEST[$post_data_value])) {
                $post_data[] = $_REQUEST[$post_data_value];
            }
        }

		$digest = base64_encode(sha1(implode('', $post_data) . $processor_data['processor_params']['shared_secret'], true));

		if($_REQUEST['status'] == 'CAPTURED') {
			$pp_response['order_status'] = 'P';
			$pp_response['reason_text'] = __('transaction_approved');
			$pp_response['transaction_id'] = $_REQUEST['paymentRef'];
		}
	}
	
	if (fn_check_payment_script('alpha_bank.php', $order_id)) {
        fn_finish_payment($order_id, $pp_response);
        fn_order_placement_routines('route', $order_id);
    }
	
} else {
	if ($processor_data['processor_params']['mode'] == 'test') {
		$payment_url = 'https://alpha.test.modirum.com/vpos/shophandlermpi';
	} else {
		$payment_url = 'https://www.alphaecommerce.gr/vpos/shophandlermpi';
	}

	$amount = fn_format_price($order_info['total'], $processor_data['processor_params']['currency']);
	$confirm_url = fn_url("payment_notification.success?payment=alpha_bank&order_id=$order_id", AREA, 'current');
	$cancel_url = fn_url("payment_notification.fail?payment=alpha_bank&order_id=$order_id", AREA, 'current');

	$post_data = array(
		'mid' => $processor_data['processor_params']['merchant_id'],
		'lang' => $processor_data['processor_params']['language'],
		'orderid' => time() . $order_id,
		'orderDesc' => '#' . $order_id,
		'orderAmount' => $amount,
		'currency' => $processor_data['processor_params']['currency'],
		'payerEmail' => $order_info['email'],
		'payerPhone' => $order_info['b_phone'],
		'trType' => '1',
		'confirmUrl' => $confirm_url,
		'cancelUrl' => $cancel_url
	);

	$post_data['digest'] = base64_encode(sha1(implode('', $post_data) . $processor_data['processor_params']['shared_secret'], true));

	fn_create_payment_form($payment_url, $post_data, 'Alpha Bank', false);
}
exit;
