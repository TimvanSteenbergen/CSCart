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

use Tygh\Session;
use Tygh\Registry;

$avs_res = array(
    '0' => 'Not Supported',
    '1' => 'Not Checked',
    '2' => 'Matched',
    '4' => 'Not Matched',
    '8' => 'Partially Matched'
);
$mode_test_declined = 101;
$mode_test = 100;
$mode_live = 0;
$card_holder_for_declined_test = 'REFUSED';

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'notify') {
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }

} elseif (!empty($_REQUEST['cartId']) && !empty($_REQUEST['transStatus'])) {

    require './init_payment.php';
    $order_id = (strpos($_REQUEST['cartId'], '_')) ? substr($_REQUEST['cartId'], 0, strpos($_REQUEST['cartId'], '_')) : $_REQUEST['cartId'];
    fn_payments_set_company_id($order_id);
    $pp_response["reason_text"] = '';

    $payment_id = db_get_field("SELECT ?:orders.payment_id FROM ?:orders WHERE ?:orders.order_id = ?i", $order_id);
    $processor_data = fn_get_payment_method_data($payment_id);

    $pp_response['order_status'] = ($_REQUEST['transStatus'] == 'Y' && (!empty($processor_data['processor_params']['callback_password']) ? (!empty($_REQUEST['callbackPW']) && $_REQUEST['callbackPW'] == $processor_data['processor_params']['callback_password']) : true)) ? 'P' : 'F';

    if ($_REQUEST['transStatus'] == 'Y') {
        $pp_response['reason_text'] = $_REQUEST['rawAuthMessage'];
        $pp_response['transaction_id'] = $_REQUEST['transId'];
        $pp_response['descr_avs'] = ('CVV (Security Code): ' . $avs_res[substr($_REQUEST['AVS'], 0, 1)] . '; Postcode: ' . $avs_res[substr($_REQUEST['AVS'], 1, 1)] . '; Address: ' . $avs_res[substr($_REQUEST['AVS'], 2, 1)] . '; Country: ' . $avs_res[substr($_REQUEST['AVS'], 3)]);
    }

    if (!empty($_REQUEST['testMode'])) {
        $pp_response['reason_text'] .= '; This a TEST Transaction';
    }

    $area = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'E'", $order_id);
    $override = ($area == 'A') ? true : false;
    fn_finish_payment($order_id, $pp_response, false);

    echo "<head><meta http-equiv='refresh' content='0; url=" . fn_url("payment_notification.notify?payment=worldpay&order_id=$order_id", $area, 'current', CART_LANGUAGE, $override) . "'></head><body><wpdisplay item=banner></body>";
    exit;
} else {

    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    $s_id = Session::getId();
    $sess_name = Session::getName();
    $card_holder = $processor_data['processor_params']['test'] == $mode_test_declined ? $card_holder_for_declined_test : $order_info['b_firstname'] . ' ' . $order_info['b_lastname'];
    $test_mode_id = $processor_data['processor_params']['test'] == $mode_test_declined ? $mode_test : $processor_data['processor_params']['test'];
    $signature = md5($processor_data['processor_params']['md5_secret'] . ':' . $processor_data['processor_params']['account_id'] . ':' . $order_info['total'] . ':' . $processor_data['processor_params']['currency'] . ':' . $_order_id);
    $data = array(
        'signatureFields' => 'instId:amount:currency:cartId',
        'signature' => $signature,
        'instId' => $processor_data['processor_params']['account_id'],
        'cartId' => $_order_id,
        'amount' => $order_info['total'],
        'currency' => $processor_data['processor_params']['currency'],
        'testMode' => $test_mode_id,
        'authMode' => $processor_data['processor_params']['authmode'],
        'name' => $card_holder,
        'tel' => $order_info['phone'],
        'email' => $order_info['email'],
        'address' => $order_info['b_address'] . ' ' . $order_info['b_city'] . ' ' . $order_info['b_state'] . ' ' . $order_info['b_country'],
        'postcode' => $order_info['b_zipcode'],
        'country' => $order_info['b_country'],
        "MC_$sess_name" => $s_id,
    );

    $order_data = array(
        'order_id' => $order_id,
        'type' => 'E',
        'data' => AREA,
    );
    db_query("REPLACE INTO ?:order_data ?e", $order_data);

    $submit_url = ($processor_data['processor_params']['test'] == $mode_test_declined || $processor_data['processor_params']['test'] == $mode_test) ? 'https://secure-test.worldpay.com/wcc/purchase' : 'https://secure.worldpay.com/wcc/purchase';
    fn_create_payment_form($submit_url, $data, 'World Pay server', false);
exit;
}
