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

if (defined('PAYMENT_NOTIFICATION')) {

    if ($mode == 'notify') {

        fn_order_placement_routines('route', $_REQUEST['order_id']);

    } elseif ($mode == 'process') {

        $pp_response = array(
            'order_status' => 'F',
            'pp_response' => '',
            'reason_text' => ''
        );
        $order_id = $_REQUEST['order_id'];

        if (!empty($_REQUEST['payment_number'])) {
            $pp_response['transaction_id'] = $_REQUEST['payment_number'];

            $conf_key = db_get_field("SELECT data FROM ?:order_data WHERE type = 'E' AND order_id = ?i", $order_id);

            if (empty($conf_key) || $conf_key != $_REQUEST['conf_key']) {
                $pp_response['reason_text'] .= 'Confirmation key does not match; ';
            } else {
                db_query("DELETE FROM ?:order_data WHERE type = 'E' AND order_id = ?i", $order_id);
                $pp_response['order_status'] = 'P';
            }
        } else {
            $pp_response['reason_text'] .= 'Payment number is empty; ';
        }

        $pp_response['reason_text'] .= ("Received from: " . $_SERVER['REMOTE_ADDR']);

        if (fn_check_payment_script('direct_one.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response);
        }
    }

} else {
    $conf_key = md5($order_id . TIME . $_SESSION['auth']['user_id']);
    $data = array (
        'order_id' => $order_id,
        'type' => 'E', // extra order ID
        'data' => $conf_key,
    );
    db_query("REPLACE INTO ?:order_data ?e", $data);

    $submit_url = 'https://vault.safepay.com.au/cgi-bin/' . ($processor_data['processor_params']['mode'] == 'live' ? 'make' : 'test') . '_payment.pl';
    $return_url = fn_url("payment_notification.notify?payment=direct_one&order_id=$order_id", AREA, 'current');
    $process_url = fn_url("payment_notification.process?payment=direct_one&order_id=$order_id&payment_number=&conf_key=$conf_key", AREA, 'current');
    $post_data = array(
        'vendor_name' => $processor_data['processor_params']['merchant_id'],
        'return_link_url' => $return_url,
        'reply_link_url' => $process_url,
        'Billing_name' => $order_info['b_firstname'],
        'Billing_address1' => $order_info['b_address'],
        'Billing_address2' => $order_info['b_address_2'],
        'Billing_city' => $order_info['b_city'],
        'Billing_state' => $order_info['b_state_descr'],
        'Billing_zip' => $order_info['b_zipcode'],
        'Billing_country' => $order_info['b_country_descr'],
        'Delivery_name' => $order_info['s_firstname'],
        'Delivery_address1' => $order_info['s_address'],
        'Delivery_address2' => $order_info['s_address_2'],
        'Delivery_city' => $order_info['s_city'],
        'Delivery_state' => $order_info['s_state_descr'],
        'Delivery_zip' => $order_info['s_zipcode'],
        'Delivery_country' => $order_info['s_country_descr'],
        'Contact_email' => $order_info['email'],
        'Contact_phone' => $order_info['phone'],
        'information_fields' => 'Billing_name,Billing_address1,Billing_address2,Billing_city,Billing_state,Billing_zip,Billing_country,Delivery_name,Delivery_address1,Delivery_address2,Delivery_city,Delivery_state,Delivery_zip,Delivery_country,Contact_email,Contact_phone',
        'suppress_field_names' => '',
        'hidden_fields' => '',
        'print_zero_qty' => false,
    );

    if (empty($order_info['use_gift_certificates']) && !floatval($order_info['subtotal_discount']) && empty($order_info['points_info']['in_use'])) {
        // Products
        if (!empty($order_info['products'])) {
            foreach ($order_info['products'] as $k => $v) {
                $v['product'] = htmlspecialchars(strip_tags($v['product']));
                $v['price'] = fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount']);
                $post_data[$v['product']] = "$v[amount],$v[price]";
            }
        }

        // Taxes
        if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
            foreach ($order_info['taxes'] as $tax_id => $tax) {
                if ($tax['price_includes_tax'] == 'Y') {
                    continue;
                }
                $item_name = htmlspecialchars(strip_tags($tax['description']));
                $item_price = fn_format_price($tax['tax_subtotal']);
                $post_data[$item_name] = "1,$item_price";
            }
        }

        // Gift Certificates
        if (!empty($order_info['gift_certificates'])) {
            foreach ($order_info['gift_certificates'] as $k => $v) {
                $v['gift_cert_code'] = htmlspecialchars($v['gift_cert_code']);
                $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : fn_format_price($v['amount']);
                $post_data[$v['gift_cert_code']] = "1,$v[amount]";
            }
        }

        // Payment surcharge
        if (floatval($order_info['payment_surcharge'])) {
            $name = __('surcharge');
            $payment_surcharge_amount = fn_format_price($order_info['payment_surcharge']);
            $post_data[$name] = "1,$payment_surcharge_amount";
        }

        // Shipping
        $_shipping_cost = fn_order_shipping_cost($order_info);
        if (floatval($_shipping_cost)) {
            $name = __('shipping_cost');
            $payment_shipping_cost = fn_format_price($_shipping_cost);
            $post_data[$name] = "1,$payment_shipping_cost";
        }
    } else {
        $total_description = __('total_product_cost');
        $post_data[$total_description] = "1,$order_info[total]";
    }

    fn_create_payment_form($submit_url, $post_data, 'DirectOne server', false);
exit;
}
