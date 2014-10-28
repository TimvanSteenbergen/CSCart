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

$hsbc_errors = array(
    "1"	=> "The user cancelled the transaction.",
    "2"	=> "The processor declined the transaction for an unknown reason.",
    "3"	=> "The transaction was declined because of a problem with the card. For example, an invalid card number or expiration date was specified.",
    "4"	=> "The processor did not return a response.",
    "5"	=> "The amount specified in the transaction was either too high or too low for the processor.",
    "6"	=> "The specified currency is not supported by either the processor or the card.",
    "7"	=> "The order is invalid because the order ID is a duplicate.",
    "8"	=> "The transaction was rejected by FraudShield.",
    "9"	=> "The transaction was placed in Review state by FraudShield.",
    "10" => "The transaction failed because of invalid input data.",
    "11" => "The transaction failed because the CPI was configured incorrectly.",
    "12" => "The transaction failed because the Storefront was configured incorrectly.",
    "13" => "The connection timed out.",
    "14" => "The transaction failed because the cardholders browser refused a cookie.",
    "15" => "The customers browser does not support 128-bit encryption.",
    "16" => "The CPI cannot communicate with the Secure ePayment engine."
);

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'notify') {
        $pp_response = array();

        if (empty($_REQUEST['CpiResultsCode'])) {
            $pp_response["order_status"] = "P";
            $pp_response["reason_text"] = "CpiResultsCode: " . $_REQUEST['CpiResultsCode'];
        } else {
            $pp_response["order_status"] = "F";
            $pp_response["reason_text"] = $hsbc_errors[$_REQUEST['CpiResultsCode']];
        }

        $order_id = $_REQUEST['OrderId'];

        if (fn_check_payment_script('hsbc.php', $order_id)) {
            fn_change_order_status($order_id, $pp_response["order_status"], '', true);
        }
        exit;

    } elseif ($mode == 'invoice') {
        if (empty($_REQUEST['CpiResultsCode'])) {
            $pp_response["order_status"] = "P";
            $pp_response["reason_text"] = "CpiResultsCode: " . $_REQUEST['CpiResultsCode'];
        } else {
            $pp_response["order_status"] = "F";
            $pp_response["reason_text"] = $hsbc_errors[$_REQUEST['CpiResultsCode']];
        }

        $order_id = $_REQUEST['OrderId'];

        if (fn_check_payment_script('hsbc.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
            fn_order_placement_routines('route', $order_id);
        }
        exit;
    }

} else {
    $hashkey = $processor_data['processor_params']['cpihashkey'];

    $post_data = array(
    "CpiDirectResultUrl"	=> fn_url("payment_notification.notify?payment=hsbc&order_id=$order_id", AREA, 'https'),
    "CpiReturnUrl"		=> fn_url("payment_notification.invoice?payment=hsbc&order_id=$order_id", AREA, 'https'),
    "MerchantData"			=> "ORDER ".$order_id,
    "Mode"					=> $processor_data['processor_params']['mode'],
    "OrderDesc"				=> "ORDER ".$order_id. (($order_info['repaid']) ? ('_'. $order_info['repaid']) : ''),
    "OrderId"				=> (($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id),
    "PurchaseAmount"		=> $order_info['total']*(($processor_data['processor_params']['currency'] != '392') ? 100 : 1),
    "PurchaseCurrency"		=> $processor_data['processor_params']['currency'],
    "StorefrontId"			=> $processor_data['processor_params']['store_id'],
    "TimeStamp"				=> (time())."000",
    "TransactionType"		=> "Capture",
    "UserId"				=> $order_info['firstname'] . " " . $order_info['lastname'],

    "BillingAddress1"		=> str_replace('\n', '', $order_info['b_address']),
    "BillingCity"			=> $order_info['b_city'],
    "BillingCountry"		=> db_get_field("SELECT code_N3 FROM ?:countries WHERE code = ?s", $order_info['b_country']),
    "BillingCounty"			=> $order_info['b_state'] ? $order_info['b_state'] : 'n/a',
    "BillingFirstName"		=> $order_info['b_firstname'],
    "BillingLastName"		=> $order_info['b_lastname'],
    "BillingPostal"			=> $order_info['b_zipcode'],
    "ShopperEmail"			=> $order_info['email'],

    "ShippingAddress1"		=> str_replace('\n', '', $order_info['s_address']),
    "ShippingCity"			=> $order_info['s_city'],
    "ShippingCountry"		=> db_get_field("SELECT code_N3 FROM ?:countries WHERE code = ?s", $order_info['s_country']),
    "ShippingCounty"		=> $order_info['s_state'] ? $order_info['s_state'] : 'n/a',
    "ShippingFirstName"		=> $order_info['s_firstname'],
    "ShippingLastName"		=> $order_info['s_lastname'],
    "ShippingPostal"		=> $order_info['s_zipcode']
    );

    $_current_os = fn_strtolower(substr(PHP_OS,0,3));

    $post_data_line = escapeshellarg(implode("\" \"", $post_data));

    // Generate Hash
    if ($_current_os == 'win') {
        @exec('PATH ' . Registry::get('config.dir.payments') . 'hsbc_files/lib/' . $_current_os);
        @exec(Registry::get('config.dir.payments') . 'hsbc_files/modules/' . $_current_os . '/TestHash.exe ' . $hashkey . " \"" . $post_data_line . "\"", $data);
    } elseif ($_current_os == 'sun') {
        putenv("LD_LIBRARY_PATH=" . Registry::get('config.dir.payments') . "hsbc_files/lib/$_current_os");
        @exec(Registry::get('config.dir.payments') . "hsbc_files/modules/$_current_os/TestHash.e ".$hashkey . " \"" . $post_data_line . "\"", $data);
    } elseif ($_current_os == 'lin') {
        putenv("LD_LIBRARY_PATH=" . Registry::get('config.dir.payments') . "hsbc_files/lib/$_current_os");
        @exec(Registry::get('config.dir.payments') . "hsbc_files/modules/$_current_os/TestHash.e ".$hashkey . " \"" . $post_data_line . "\"", $data);
    }

    if (!preg_match("/^Hash value:  (.*)$/", @$data[0], $a)) {
        //Set notification
        fn_set_notification('E', __('error'), __('error_hash_generation'));
        if ($order_info['repaid']) {
            fn_order_placement_routines('repay', $order_id);
        } else {
            fn_order_placement_routines('checkout_redirect');
        }
        exit;
    } else {
        $post_data["OrderHash"] = $a[1];

        fn_create_payment_form('https://www.cpi.hsbc.com/servlet', $post_data, 'HSBC');
        exit;
    }
}
