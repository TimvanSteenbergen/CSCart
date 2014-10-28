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

$error_message = array(
    "N" => "Transaction not authorised. Failure message text available to merchant",
    "C" => "Communication problem. Trying again later may well work",
    "P:A" => "Pre-bank checks. Amount not supplied or invalid",
    "P:X" => "Pre-bank checks. Not all mandatory parameters supplied",
    "P:P" => "Pre-bank checks. Same payment presented twice",
    "P:S" => "Pre-bank checks. Start date invalid",
    "P:E" => "Pre-bank checks. Expiry date invalid",
    "P:I" => "Pre-bank checks. Issue number invalid",
    "P:C" => "Pre-bank checks. Card number fails LUHN check",
    "P:T" => "Pre-bank checks. Card type invalid - i.e. does not match card number prefix",
    "P:N" => "Pre-bank checks. Customer name not supplied",
    "P:M" => "Pre-bank checks. Merchant does not exist or not registered yet",
    "P:B" => "Pre-bank checks. Merchant account for card type does not exist",
    "P:D" => "Pre-bank checks. Merchant account for this currency does not exist",
    "P:V" => "Pre-bank checks. CV2 security code mandatory and not supplied / invalid",
    "P:R" => "Pre-bank checks. Transaction timed out awaiting a virtual circuit. Merchant may not have enough virtual circuits for the volume of business.",
    "P:#" => "Pre-bank checks. No MD5 hash / token key set up against account"
);

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'process') {

        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $_REQUEST['order_id']);
        $processor_data = fn_get_payment_method_data($payment_id);
        $order_info = fn_get_order_info($_REQUEST['order_id']);

        if (!empty($processor_data["processor_params"]["password"])) {
            $callback_url = fn_url("payment_notification.process?payment=paypoint&order_id=$_REQUEST[order_id]", AREA, 'current');
            $md5_hash = md5("trans_id=" . $_REQUEST['trans_id'] . "&amount=" . $order_info['total'] . "&callback=" . $callback_url . "&" . $processor_data["processor_params"]["password"]);
        } else {
            $md5_hash = $_REQUEST['hash'];
        }

        $pp_response = array();
        if ($_REQUEST['code'] == "A" && $md5_hash == $_REQUEST['hash']) {
            $pp_response['order_status'] = 'P';
            $pp_response['reason_text'] ="AuthCode: " . $_REQUEST['auth_code'];
            $pp_response['transaction_id'] = $_REQUEST['trans_id'];
        } else {
            $text = '';
            if ($md5_hash != $_REQUEST['hash']) {
                $text = "MD5 hash is wrong; ";

            } elseif (isset($error_message[$_REQUEST['code']])) {
                $text = $error_message[$_REQUEST['code']];

                if (!empty($_REQUEST['message'])) {
                    $text .= " ($_REQUEST[message])";
                }

                if (!empty($_REQUEST['resp_code'])) {
                    $text .= "; RespCode: $_REQUEST[resp_code]";
                }
            }
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = "Declined: " . $text;
            $pp_response['transaction_id'] = $_REQUEST['trans_id'];
        }

        if (!empty($_REQUEST['test_status']) && $_REQUEST['test_status'] != 'live') {
            $pp_response['reason_text'] .= "; Test status: ". $_REQUEST['test_status'];
        }

        fn_finish_payment($_REQUEST['order_id'], $pp_response);

        $return_url = fn_url("payment_notification.notify?payment=paypoint&order_id=$_REQUEST[order_id]", AREA, 'current');
        echo "<html><body onLoad=\"javascript: self.location='" . $return_url . "'\"></body></html>";
        exit;

    } elseif ($mode == 'notify') {
        fn_order_placement_routines('route', $_REQUEST['order_id'], false);
    }

} else {
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    $md5_hash = (!empty($processor_data["processor_params"]["password"])) ? md5($processor_data['processor_params']['order_prefix'] . $_order_id . $order_info['total'] . $processor_data["processor_params"]["password"]) : '';

    //$processor_data['processor_params']['merchant_id'] .= "-jredir";  // unsupported way to get worked redirection from the paypoint server

    $options = 'test_status=' . $processor_data['processor_params']['mode'] . ',cb_post=true,dups=false,md_flds=trans_id:amount:callback';
    if (!empty($order_info['payment_info']['cvv2'])) {
        $options .= ",cv2=" . $order_info['payment_info']['cvv2'];
    }

    $callback_url = fn_url("payment_notification.process?payment=paypoint&order_id={$order_id}", AREA, 'current');

    $post_data = array(
        'merchant' => $processor_data['processor_params']['merchant_id'],
        'trans_id' => $processor_data['processor_params']['order_prefix'] . $_order_id,
        'amount' => $order_info['total'],
        'callback' => $callback_url,
        'currency' => $processor_data['processor_params']['currency'],
        'options' => $options,
        'mail_subject' => $processor_data['processor_params']['mail_subject'],
        'mail_message' => $processor_data['processor_params']['mail_message'],

        'bill_name' => $order_info['firstname'] . ' ' . $order_info['lastname'],
        'bill_company' => $order_info['company'],
        'bill_addr_1' => $order_info['b_address'],
        'bill_addr_2' => $order_info['b_address_2'],
        'bill_city' => $order_info['b_city'],
        'bill_state' => $order_info['b_state_descr'],
        'bill_country' => $order_info['b_country_descr'],
        'bill_post_code' => $order_info['b_zipcode'],
        'bill_tel' => $order_info['phone'],
        'bill_email' => $order_info['email'],
        'bill_url' => $order_info['url'],

        'ship_name' => $order_info['firstname'] . ' ' . $order_info['lastname'],
        'ship_company' => $order_info['company'],
        'ship_addr_1' => $order_info['s_address'],
        'ship_addr_2' => $order_info['s_address_2'],
        'ship_city' => $order_info['s_city'],
        'ship_state' => $order_info['s_state_descr'],
        'ship_country' => $order_info['s_country_descr'],
        'ship_post_code' => $order_info['s_zipcode'],
        'ship_tel' => $order_info['phone'],
        'ship_email' => $order_info['email'],
        'ship_url' => $order_info['url'],        
    );

    if ($md5_hash) {
        $post_data['digest'] = $md5_hash;
    }

    if (!empty($processor_data['processor_params']['deferred'])) {
        $post_data['deferred'] = $processor_data['processor_params']['deferred'];
    }

    fn_create_payment_form('https://www.secpay.com/java-bin/ValCard', $post_data, 'PayPoint');
    exit;
}
