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
use Tygh\Session;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$processor_response = array(
    'INITIALISE' => 'A credit application is initiated when the retailer makes an HTTP POST.',
    'PREDECLINE' => 'The credit application has been declined by CreditSentry.',
    'ACCEPT' => 'The consumer completes, signs and submits the credit application and the lender responds with an ACCEPT decision. ACCEPT decisions are valid for 30 days.',
    'DECLINE' => 'The credit application is submitted and the lender responds with a DECLINE decision.',
    'REFER' => 'The credit application is submitted and the lender responds with a REFER decision.',
    'VERIFIED' => 'The consumer has successfully paid their deposit using a credit or debit card.',
    'AMENDED' => 'The credit application has been amended and is awaiting the consumers approval.',
    'FULFILLED' => 'The retailer has notified Pay4Later that they have fulfilled the order. Fulfilment is defined as consumer having receipt of all items eg their complete order.',
    'COMPLETE' => 'The credit application has been included in a settlement payment from the lender to the retailer.',
    'CANCELLED' => 'The credit application has been cancelled.',
    'INFO NEEDED' => 'An underwriter requires additional information before the credit application can be decisioned.'
);

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'notify') {
        if (!isset($_REQUEST['CreditRequestID'])) {
            die('Access denied');
        } else {
            $transactions_id = (int) $_REQUEST['CreditRequestID'];
        }

        $prefix = ((Registry::get('settings.Security.secure_auth') == 'Y') && (AREA == 'C')) ? Registry::get('config.https_location') . '/' : '';

        $trans_order_id = db_get_field("SELECT order_id FROM ?:order_data WHERE data = ?i AND type = 'E'", $transactions_id);
        $order_id = !empty($_REQUEST['retaileruniqueref']) ? (int) $_REQUEST['retaileruniqueref'] : 0;

        if (!isset($trans_order_id) || $trans_order_id != $order_id) {
            die('Access denied');
        }

        if ($action == 'verified') {
            fn_set_notification('N', __('order_placed'), __('text_order_placed_successfully'));
            fn_order_placement_routines('route', $order_id, false);
        }
    } elseif ($mode == 'process') {
        $_order_id = (strpos($_REQUEST['Identification']['RetailerUniqueRef'], '_')) ? substr($_REQUEST['Identification']['RetailerUniqueRef'], 0, strpos($_REQUEST['Identification']['RetailerUniqueRef'], '_')) : $_REQUEST['Identification']['RetailerUniqueRef'];
        $order_id = (strpos($_order_id, '[')) ? substr($_order_id, 0, strpos($_order_id, '[')) : $_order_id;

        $pp_response = array(
            'reason_text' => '',
            'order_status' => 'O'
        );

        if ($_REQUEST['Status'] == 'PREDECLINE' || $_REQUEST['Status'] == 'DECLINE' || $_REQUEST['Status'] == 'CANCELLED') {
            $pp_response['order_status'] = 'F';
        } elseif ($_REQUEST['Status'] == 'VERIFIED') {
            $pp_response['order_status'] = 'P';
        }

        $pp_response['reason_text'] = 'Status: ' . $_REQUEST['Status'] . '; ';
        if (!empty($processor_response[$_REQUEST['Status']])) {
            $pp_response['reason_text'] .= 'Response: ' . $processor_response[$_REQUEST['Status']] . '; ';
        }
        if (!empty($_REQUEST['Status']['Finance'])) {
            $pp_response['reason_text'] .= 'Deposit: ' . $_REQUEST['Finance']['Deposit'] . '; ';
        }
        if (!empty($_REQUEST['CreditRequestID'])) {
            $pp_response['transaction_id'] = $_REQUEST['CreditRequestID'];
        }

        if (fn_check_payment_script('pay4later.php', $order_id)) {
            // allow script to receive status updates
            $idata = array (
                'order_id' => $order_id,
                'type' => 'S',
                'data' => TIME,
            );
            db_query("REPLACE INTO ?:order_data ?e", $idata);
            if (!empty($_REQUEST['CreditRequestID'])) {
                $data = array (
                    'order_id' => $order_id,
                    'type' => 'E', // extra order ID
                    'data' => $_REQUEST['CreditRequestID'],
                );
            }
            db_query("REPLACE INTO ?:order_data ?e", $data);

            $customer_info = array(
                'b_firstname' => 'Forename',
                'b_lastname' => 'Surname',
                'b_address' => 'Street',
                'b_city' => 'Town',
                'b_zipcode' => 'Postcode',
                'email' => 'EmailAddress',
                'b_phone' => 'PhoneNumber'
            );

            $new_customer_info = array();
            foreach ($customer_info as $k => $v) {
                if (isset($_REQUEST['Consumer'][$v])) {
                    $new_customer_info[$k] = $_REQUEST['Consumer'][$v];
                }
            }
            if (!empty($new_customer_info)) {
                fn_update_order_customer_info($new_customer_info, $order_id);
            }

            fn_finish_payment($order_id, $pp_response);
        }
    } elseif ($mode == 'cancel') {
        if (!isset($_SESSION['order_id'])) {
            die('Access denied');
        }
        fn_set_notification('W', __('important'), __('text_transaction_cancelled'));
        fn_order_placement_routines('route', $_SESSION['order_id'], false);
    } elseif ($mode == 'decline') {
        if (!isset($_SESSION['order_id'])) {
            die('Access denied');
        }
        fn_set_notification('E', '', __('text_order_placed_error'));
        fn_order_placement_routines('route', $_SESSION['order_id'], false);
    } elseif ($mode == 'refer') {
        if (!isset($_SESSION['order_id'])) {
            die('Access denied');
        }
        fn_set_notification('W', __('important'), $processor_response['REFER']);
        fn_order_placement_routines('route', $_SESSION['order_id'], false);
    }
} else {
    $post_url = ($processor_data['processor_params']['mode']=='test') ? 'https://test.pay4later.com/credit_app/' : 'https://secure.pay4later.com/credit_app/';
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    $order_description = __('order') . " #$order_id";
    $_SESSION['order_id'] = $order_id;

    $post_data = array(
        'Identification[api_key]' => $processor_data['processor_params']['merchant_key'],
        'Identification[RetailerUniqueRef]' => $_order_id,
        'Identification[InstallationID]' => $processor_data['processor_params']['installation_id'],
        'Goods[0][Description]' => $order_description,
        'Goods[0][Quantity]' => '1',
        'Goods[0][Price]' => $order_info['total'],
        'Finance[Code]' => $processor_data['processor_params']['finance_product_code'],
        'Finance[Deposit]' => $processor_data['processor_params']['deposit_amount'],
    );

    fn_create_payment_form($post_url, $post_data, 'Pay4Later');
}
exit;

function fn_pay4later_order_placement_routines()
{
    $_SESSION['cart'] = array(
        'user_data' => !empty($_SESSION['cart']['user_data']) ? $_SESSION['cart']['user_data'] : array(),
        'profile_id' => !empty($_SESSION['cart']['profile_id']) ? $_SESSION['cart']['profile_id'] : 0,
        'user_id' => !empty($_SESSION['cart']['user_id']) ? $_SESSION['cart']['user_id'] : 0,
    );
    $_SESSION['shipping_rates'] = array();
    unset($_SESSION['shipping_hash']);

    db_query('DELETE FROM ?:user_session_products WHERE session_id = ?s AND type = ?s', Session::getId(), 'C');
}
