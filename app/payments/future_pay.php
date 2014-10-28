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

use Tygh\Http;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('FUTUREPAY_PLATFORM', 'eb0650c3e9547b1b21675ed60a942b1c47d9193dFPM276052870');

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'result') {
        $order_id = $_REQUEST['order_id'];

        $conf_key = db_get_field("SELECT data FROM ?:order_data WHERE type = 'E' AND order_id = ?i", $order_id);
        if (!empty($conf_key) && $conf_key == $_REQUEST['conf_key']) {
            $pp_response = array(
                'order_status' => 'F',
                'reason_text' => ''
            );
            if (!empty($_REQUEST['code'])) {
                $pp_response['reason_text'] .= "Code: " . $_REQUEST['code'] . "; ";
            }
            if (!empty($_REQUEST['reason'])) {
                $pp_response['reason_text'] .= "Reason: " . $_REQUEST['reason'] . "; ";
            }
            if (!empty($_REQUEST['status'])) {
                $pp_response['reason_text'] .= "Status: " . $_REQUEST['status'] . "; ";
            }
            if (!empty($_REQUEST['transaction_id'])) {
                $pp_response['transaction_id'] = $_REQUEST['transaction_id'];
                $pp_response['order_status'] = 'P';
            } else {  // this feature has been requested by client
                $pp_response['reason_text'] = $_REQUEST['reason'];
            }

            if (fn_check_payment_script('future_pay.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response, false);
                fn_order_placement_routines('route', $order_id);
            }
        }
    }
} else {
    if ($processor_data['processor_params']['mode'] == 'T') {
        $post_url = 'https://sandbox.futurepay.com/remote/merchant-request-order-token';
    } else {
        $post_url = 'https://api.futurepay.com/remote/merchant-request-order-token';
    }

    $post_data = array();
    $post_data['pmid'] = FUTUREPAY_PLATFORM;
    $post_data['gmid'] = $processor_data['processor_params']['merchant_id'];
    $post_data['reference'] = $order_id;

    $post_data['email'] = $order_info['email'];
    $post_data['first_name'] = $order_info['b_firstname'];
    $post_data['last_name'] = $order_info['b_lastname'];
    $post_data['address_line_1'] = $order_info['b_address'];
    $post_data['address_line_2'] = $order_info['b_address_2'];
    $post_data['city'] = $order_info['b_city'];
    // if ($order_info['b_country'] == 'US') {
    $post_data['state'] = $order_info['b_state'];
    // }
    $post_data['country'] = $order_info['b_country'];
    $post_data['zip'] = $order_info['b_zipcode'];
    $post_data['phone'] = $order_info['b_phone'];

    $post_data['shipping_address_line_1'] = $order_info['s_address'];
    $post_data['shipping_address_line_2'] = $order_info['s_address_2'];
    $post_data['shipping_city'] = $order_info['s_city'];
    // if ($order_info['b_country'] == 'US') {
    $post_data['shipping_state'] = $order_info['s_state'];
    // }
    $post_data['shipping_country'] = $order_info['s_country'];
    $post_data['shipping_zip'] = $order_info['s_zipcode'];

    if (!empty($order_info['b_company'])) {
        $post_data['company'] = $order_info['b_company'];
    }

    if (empty($order_info['use_gift_certificates']) && !floatval($order_info['subtotal_discount']) && empty($order_info['points_info']['in_use'])) {
        // Products
        if (!empty($order_info['products'])) {
            foreach ($order_info['products'] as $k => $v) {
                $tax = !empty($v['tax_value']) ? $v['tax_value'] : 0;
                $post_data['sku'][] = $v['product_code'];
                $post_data['price'][] = fn_format_price(($v['subtotal'] - fn_external_discounts($v) - $tax) / $v['amount']);
                $post_data['tax_amount'][] = $tax / $v['amount'];
                $post_data['description'][] = htmlspecialchars(strip_tags($v['product']));
                $post_data['quantity'][] = $v['amount'];
            }
        }

        // Taxes
        if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
            foreach ($order_info['taxes'] as $tax_id => $tax) {
                if ($tax['price_includes_tax'] == 'Y') {
                    continue;
                }
                $post_data['sku'][] = 'TAX' . $tax_id;
                $post_data['price'][] = $tax['tax_subtotal'];
                $post_data['tax_amount'][] = 0;
                $post_data['description'][] = htmlspecialchars(strip_tags($tax['description']));
                $post_data['quantity'][] = 1;
            }
        }

        // Gift Certificates
        if (!empty($order_info['gift_certificates'])) {
            foreach ($order_info['gift_certificates'] as $k => $v) {
                $post_data['sku'][] = htmlspecialchars($v['gift_cert_code']);
                $post_data['price'][] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
                $post_data['tax_amount'][] = 0;
                $post_data['description'][] = __('gift_certificate');
                $post_data['quantity'][] = 1;
            }
        }

        // Payment surcharge
        if (floatval($order_info['payment_surcharge'])) {
            $post_data['sku'][] = 'SURCHARGE';
            $post_data['price'][] = $order_info['payment_surcharge'];
            $post_data['tax_amount'][] = 0;
            $post_data['description'][] = __('surcharge');
            $post_data['quantity'][] = 1;
        }

        //Shipping cost
        if (fn_order_shipping_cost($order_info)) {
            $post_data['sku'][] = 'SHIPPING';
            $post_data['price'][] = !empty($order_info['shipping_cost']) ? floatval($order_info['shipping_cost']) : 0;
            $post_data['tax_amount'][] = fn_order_shipping_taxes_cost($order_info);
            $post_data['description'][] = __('shipping_cost');
            $post_data['quantity'][] = 1;
        }
    } else {
        $post_data['sku'][] = 'TOTAL';
        $post_data['price'][] = $order_info['total'];
        $post_data['tax_amount'][] = 0;
        $post_data['description'][] = __('total_product_cost');
        $post_data['quantity'][] = 1;
    }

    $result = Http::post($post_url, $post_data);

    // try another e-mail if you receive the FP_EXISTING_INVALID_CUSTOMER_STATUS error
    if (strpos($result, 'FP_') === false) {
        if ($processor_data['processor_params']['mode'] == 'T') {
            $script_url = 'https://demo.futurepay.com/remote/cart-integration/' . $result;
        } else {
            $script_url = 'https://api.futurepay.com/remote/cart-integration/' . $result;
        }
        $continue_url = fn_url('checkout.checkout');

        $conf_key = md5($order_id . TIME . $_SESSION['auth']['user_id']);
        $data = array (
            'order_id' => $order_id,
            'type' => 'E', // extra order ID
            'data' => $conf_key,
        );
        db_query("REPLACE INTO ?:order_data ?e", $data);

        $return_url = fn_url("payment_notification.result?payment=future_pay&order_id=$order_id&conf_key=$conf_key", AREA, 'current');

        Registry::get('view')->assign('script_url', $script_url);
        Registry::get('view')->assign('return_url', $return_url);
        Registry::get('view')->assign('continue_url', $continue_url);

        Registry::get('view')->display('views/checkout/processors/future_pay.tpl');

    } else {

        $_error_texts = array(
            'FP_PRE_ORDER_EXCEEDS_MAXIMUM' => __('payments.futurepay.amount_error'),
            'FP_COUNTRY_US_ONLY' => __('payments.futurepay.country_error'),
            'FP_EXISTING_INVALID_CUSTOMER_STATUS' => __('payments.futurepay.customer_error'),
            'FP_MISSING_REQUIRED_PHONE' => __('payments.futurepay.phone_error'),
            'FP_NO_ZIP_FOUND' => __('payments.futurepay.zipcode_error'),
            'FP_INVALID_STATE_ZIP_COMBINATION' => __('payments.futurepay.zipcode_combination_error'),
            'FP_ORDER_EXISTS' => __('payments.futurepay.order_exists')
        );

        $pp_response = array(
            'order_status' => 'F',
            'reason_text' => !empty($_error_texts[trim($result)]) ? $_error_texts[trim($result)] : $result
        );

        if (fn_check_payment_script('future_pay.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
            fn_order_placement_routines('route', $order_id);
        }
    }
}
exit();
