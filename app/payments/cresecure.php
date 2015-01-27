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

if (!defined('BOOTSTRAP')) {
    define('SKIP_SESSION_VALIDATION', true);
    //it will be merged with the $_REQUEST later
    $_GET['dispatch'] = 'checkout.cresecure_template';

    require './init_payment.php';
    require (Registry::get('config.dir.root') . '/app/controllers/frontend/init.php');
    Registry::get('view')->assign('display_base_href', true);
    //We should assign this information to display in the default checkout blocks (Order summary and Products in your order)
    Registry::get('view')->assign('cart', $_SESSION['cart']);
    Registry::get('view')->assign('cart_products', $_SESSION['cart']['products']);

    fn_add_breadcrumb(__('payment_information'));
    Registry::get('view')->assign('content_tpl', 'views/orders/processors/cresecure.tpl');

    Registry::get('view')->display(Registry::get('runtime.root_template'));
} else {

if (defined('PAYMENT_NOTIFICATION')) {

    if ($mode == 'return') {
        //NOTE: do not remove intval() !
        $order_id = intval($_REQUEST['order_id']);

        $pp_response = array();
        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id=?i", $order_id);
        $processor_data = fn_get_processor_data($payment_id);

        if (empty($_REQUEST['error']) && !empty($_REQUEST['msg']) && ($_REQUEST['msg'] == 'Success' || $_REQUEST['msg'] == 'Approved')) {
            $pp_response['order_status'] = 'P';
            $pp_response['reason_text'] = $_REQUEST['msg'];
            $pp_response['transaction_id'] = $_REQUEST['TxnGUID'];

            $pp_response['card_number'] = $_REQUEST['mPAN'];
            $pp_response['card'] = $_REQUEST['type'];
            $pp_response['cardholder_name'] = $_REQUEST['name'];
            $pp_response['expiry_month'] = substr($_REQUEST['exp'], 0, 2);
            $pp_response['expiry_year'] = substr($_REQUEST['exp'], -2);

        } elseif (!empty($_REQUEST['error'])) {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = !empty($_REQUEST['msg'])? $_REQUEST['msg'] : __('error');

        } else {
            $pp_response['order_status'] = 'N';
            $pp_response['reason_text'] = __('transaction_cancelled');
        }

        if (fn_check_payment_script('cresecure.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response);
            fn_order_placement_routines('route', $order_id);
        }
    }
} else {

    if ($processor_data['processor_params']['test'] == 'live') {
        $post_address = "https://safe.cresecure.net/securepayments/a1/cc_collection.php";
    } else {
        $post_address = "https://sandbox-cresecure.net/securepayments/a1/cc_collection.php";
    }

    $post_data = array(
        'CRESecureID' => $processor_data['processor_params']['cresecureid'],
        'total_amt' => sprintf('%.2f', $order_info['total']),
        'return_url' => fn_url("payment_notification.return?payment=cresecure&order_id=$order_id", AREA, 'https'),
        'content_template_url' => fn_payment_url('https', "cresecure.php?order_id=$order_id&display_full_path=Y"),
        'b_country' => db_get_field('SELECT a.code_A3 FROM ?:countries as a WHERE a.code = ?s', $order_info['b_country']),
        's_country' => db_get_field('SELECT a.code_A3 FROM ?:countries as a WHERE a.code = ?s', $order_info['s_country']),
        'customer_address' => $order_info['b_address'] . ((!empty($order_info['b_address_2']))? ' ' . $order_info['b_address_2'] : ''),
        'delivery_address' => $order_info['s_address'] . ((!empty($order_info['s_address_2']))? ' ' . $order_info['s_address_2'] : ''),
        'customer_phone' => !empty($order_info['b_phone'])? $order_info['b_phone'] : '',
        'delivery_phone' => !empty($order_info['s_phone'])? $order_info['s_phone'] : '',
        'allowed_types' => !empty($processor_data['processor_params']['allowed_types'])? join('|', $processor_data['processor_params']['allowed_types']) : 'Visa|MasterCard',
        'sess_id' => Session::getId(),
        'sess_name' => Session::getName(),
        'order_id' => $order_info['order_id'],
        'currency' => $processor_data['processor_params']['currency'],
        'CRESecureAPIToken' => $processor_data['processor_params']['cresecureapitoken'],
        'customer_id' => $order_info['user_id'],
        'customer_company' => $order_info['company'],
        'customer_firstname' => $order_info['b_firstname'],
        'customer_lastname' => $order_info['b_lastname'],
        'customer_email' => $order_info['email'],
        'customer_city' => $order_info['b_city'],
        'customer_state' => $order_info['b_state'],
        'customer_postal_code' => $order_info['b_zipcode'],
        'customer_country' => $order_info['b_country'],
        'delivery_firstname' => $order_info['s_firstname'],
        'delivery_lastname' => $order_info['s_lastname'],
        'delivery_city' => $order_info['s_city'],
        'delivery_state' => $order_info['s_state'],
        'delivery_postal_code' => $order_info['s_zipcode'],
        'ip_address' => $_SERVER['REMOTE_ADDR'],
    );

    fn_create_payment_form($post_address, $post_data, 'CRE secure', false);
}
exit;
}
