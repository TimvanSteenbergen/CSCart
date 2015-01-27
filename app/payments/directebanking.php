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

if (defined('PAYMENT_NOTIFICATION')) {

    $order_id = $_REQUEST['order_id'];
    $pp_response = array();

    if ($mode == 'success') {
        if (fn_check_payment_script('directebanking.php', $order_id)) {
            fn_order_placement_routines('route', $order_id);
        }
    } elseif ($mode == 'abort') {
        if (fn_check_payment_script('directebanking.php', $order_id)) {
            fn_order_placement_routines('route', $order_id);
        }
    } elseif ($mode == 'notification') {

        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_payment_method_data($payment_id);
        $order_info = fn_get_order_info($order_id);

        $response_data = Array (
            'transaction' => $_REQUEST['transaction'],
            'user_id' => $_REQUEST['user_id'],
            'project_id' => $_REQUEST['project_id'],
            'sender_holder' => $_REQUEST['sender_holder'],
            'sender_account_number' => $_REQUEST['sender_account_number'],
            'sender_bank_code' => $_REQUEST['sender_bank_code'],
            'sender_bank_name' => $_REQUEST['sender_bank_name'],
            'sender_bank_bic' => $_REQUEST['sender_bank_bic'],
            'sender_iban' => $_REQUEST['sender_iban'],
            'sender_country_id' => $_REQUEST['sender_country_id'],
            'recipient_holder' => $_REQUEST['recipient_holder'],
            'recipient_account_number' => $_REQUEST['recipient_account_number'],
            'recipient_bank_code' => $_REQUEST['recipient_bank_code'],
            'recipient_bank_name' => $_REQUEST['recipient_bank_name'],
            'recipient_bank_bic' => $_REQUEST['recipient_bank_bic'],
            'recipient_iban' => $_REQUEST['recipient_iban'],
            'recipient_country_id' => $_REQUEST['recipient_country_id'],
            'international_transaction' => $_REQUEST['international_transaction'],
            'amount' => $_REQUEST['amount'],
            'currency_id' => $_REQUEST['currency_id'],
            'reason_1' => $_REQUEST['reason_1'],
            'reason_2' => $_REQUEST['reason_2'],
            'security_criteria' => $_REQUEST['security_criteria'],
            'user_variable_0' => $_REQUEST['user_variable_0'],
            'user_variable_1' => $_REQUEST['user_variable_1'],
            'user_variable_2' => $_REQUEST['user_variable_2'],
            'user_variable_3' => $_REQUEST['user_variable_3'],
            'user_variable_4' => $_REQUEST['user_variable_4'],
            'user_variable_5' => $_REQUEST['user_variable_5'],
            'created' => $_REQUEST['created'],
            'project_password' => $processor_data['processor_params']['project_password']
        );
        $response_data_implode = implode('|', $response_data);
        $our_hash = sha1($response_data_implode);

        if ($our_hash == $_REQUEST['hash']) {
            if ($_REQUEST['security_criteria'] == 1) {
                $pp_response["order_status"] = 'P';
                $pp_response["reason_text"] = 'Approved.';
            } else {
                $pp_response["order_status"] = 'O';
                $pp_response["reason_text"] = 'Please wait for the actual incoming payment and after that change order status manualy.';
            }
        } else {
            $pp_response["order_status"] = 'F';
            $pp_response["reason_text"] = 'Hash value is incorrect in the notification request.';
        }

        $pp_response["reason_1"] = $_REQUEST['reason_1'];
        $pp_response["transaction"] = $_REQUEST['transaction'];
        $pp_response["sender_holder"] = $_REQUEST['sender_holder'];
        $pp_response["sender_account_number"] = $_REQUEST['sender_account_number'];
        $pp_response["sender_bank_code"] = $_REQUEST['sender_bank_code'];
        $pp_response["sender_bank_name"] = $_REQUEST['sender_bank_name'];
        $pp_response["sender_bank_bic"] = $_REQUEST['sender_bank_bic'];
        $pp_response["sender_iban"] = $_REQUEST['sender_iban'];
        $pp_response["sender_country_id"] = $_REQUEST['sender_country_id'];
        $pp_response["created"] = $_REQUEST['created'];

        if (fn_check_payment_script('directebanking.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }
        exit;
    }
} else {

    $url = 'https://www.directebanking.com/payment/start';

    // multicurrency
    $currencies = Registry::get('currencies');
    $currency_code = $processor_data['processor_params']['currency_id'];

    $amount = 0;
    foreach ($currencies as $k => $v) {
        if ($k == $currency_code) {
            $amount = fn_format_price($order_info['total'] / $v['coefficient']);
        }
    }
    // /multicurrency

    $post_data = Array (
        'user_id' => $processor_data['processor_params']['user_id'],
        'project_id' => $processor_data['processor_params']['project_id'],
        'sender_holder' => '',
        'sender_account_number' => '',
        'sender_bank_code' => '',
        'sender_country_id' => '',
        'amount' => $amount,
        'currency_id' => strtoupper($currency_code),
        'reason_1' => 'Order ID ' . $order_id,
        'reason_2' => '',
        'user_variable_0' => $order_id,
        'user_variable_1' => '',
        'user_variable_2' => '',
        'user_variable_3' => '',
        'user_variable_4' => '',
        'user_variable_5' => '',
        'project_password' => $processor_data['processor_params']['project_password']
    );

    $post_data_implode = implode('|', $post_data);
    $post_data['hash'] = sha1($post_data_implode);
    $post_data['language_id'] = $processor_data['processor_params']['language_id'];

    fn_create_payment_form($url, $post_data, 'DIRECTebanking');

    exit;
}
