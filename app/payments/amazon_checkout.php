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

if (!defined('BOOTSTRAP')) {
    define('SKIP_SESSION_VALIDATION', true);
    require './init_payment.php';
    include(Registry::get('config.dir.payments') . 'amazon/amazon_callback.php');
    exit;
}

include_once (Registry::get('config.dir.payments') . 'amazon/amazon_func.php');

if (defined('PAYMENT_NOTIFICATION')) {
    define('AMAZON_MAX_TIME', 60); // Time for awaiting callback
    $amazon_order_id = $_REQUEST['amznPmtsOrderIds'];

    $view = Registry::get('view');
    $view->assign('order_action', __('placing_order'));
    $view->display('views/orders/components/placing_order.tpl');
    fn_flush();
    $_placed = false;
    $times = 0;

    while (!$_placed) {
        $order_id = db_get_field('SELECT order_id FROM ?:order_data WHERE type = ?s AND data = ?s', 'E', $amazon_order_id);

        if (!empty($order_id)) {
            $_placed = true;
        } else {
            sleep(1);
        }

        $times++;
        if ($times > AMAZON_MAX_TIME) {
            break;
        }
    }
    // If order was placed successfully, associate the order with this customer
    if (!empty($order_id)) {
        $auth['order_ids'][] = $order_id;
        $pp_response['order_status'] = 'P';
        fn_finish_payment($order_id, $pp_response);
    } else {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = __('text_amazon_failed_order');
        fn_finish_payment($order_id, $pp_response);
    }
    fn_order_placement_routines('route', $order_id, false);
    exit;
} elseif (!empty($_payment_id) && !fn_cart_is_empty($cart)) {
    $base_domain = 'https://' . (($processor_data['processor_params']['test'] != 'Y') ? 'payments.amazon.com' : 'payments-sandbox.amazon.com');
    $merchant_id = $processor_data['processor_params']['merchant_id'];
    $_currency = $processor_data['processor_params']['currency'];
    $amazon_products = $cart['products'];

    fn_set_hook('amazon_products', $amazon_products, $cart);
    // Get cart items
    $amazon_order = array();
    foreach ($amazon_products as $key => $product) {
        // Get product options
        $item_options = ' ';
        if (!empty($product['product_options'])) {
            $_options = fn_get_selected_product_options_info($cart['products'][$key]['product_options']);
            foreach ($_options as $opt) {
                $item_options .= $opt['option_name'] . ': ' . $opt['variant_name'] . '; ';
            }
            $item_options = ' [' . trim($item_options, '; ') . ']';
        }

        $amazon_order['Cart']['Items']['Item'][] = array(
            'SKU' => (empty($product['product_code']) ? 'pid_' . $product['product_id'] : substr(strip_tags($product['product_code']), 0, 250)),
            'MerchantId' => $processor_data['processor_params']['merchant_id'],
            'Title' => substr(strip_tags($product['product']), 0, 250) . $item_options,
            'Price' => array(
                'Amount' => fn_format_price($product['price']),
                'CurrencyCode' => $_currency,
            ),
            'Quantity' => $product['amount'],
            'ItemCustomData' => array(
                'CartID' => $key
            )
        );
    }

    $amazon_order['Cart']['CartCustomData'] = array (
        // Generate request ID using the SESSION_ID
        'ClientRequestId' => base64_encode(Session::getId() . ';' . $_payment_id)
    );

    // Activate the Amazon callbacks functionality
    $amazon_order['ReturnUrl'] = Registry::get('config.http_location') . '/' . Registry::get('config.customer_index') . '?dispatch=payment_notification.placement&payment=amazon_checkout';
    $amazon_order['CancelUrl'] = fn_url('checkout.cart');
    $amazon_order['OrderCalculationCallbacks'] = array(
        'CalculateTaxRates' => 'true',
        'CalculatePromotions' => 'true',
        'CalculateShippingRates' => 'true',
        'OrderCallbackEndpoint' => Registry::get('config.origin_http_location') . '/app/payments/amazon_checkout.php',
        'ProcessOrderOnCallbackFailure' => $processor_data['processor_params']['process_on_failure'] == 'Y' ? 'true' : 'false',
    );

    $amazon_order['DisablePromotionCode'] = 'true';

    $amazon_cart = '<?xml version="1.0" encoding="UTF-8"?>' .
    '<Order xmlns="http://payments.amazon.com/checkout/2009-05-15/">' . fn_array_to_xml($amazon_order) . '</Order>';

    // Calculate cart signature
    if (!empty($processor_data['processor_params']['aws_access_public_key'])) {
        $sign = fn_amazon_calculate_signature($amazon_cart, $processor_data['processor_params']['aws_secret_access_key']);
        $sign = ';signature:' . $sign . ';aws-access-key-id:' . $processor_data['processor_params']['aws_access_public_key'];
        $order_type = 'merchant-signed-order/aws-accesskey/1';
    } else {
        $sign = '';
        $order_type = 'unsigned-order';
    }

    $base64cart = base64_encode($amazon_cart);

    // The necessary Amazon scripts
    if ($processor_data['processor_params']['test'] == 'Y') {
        if ($processor_data['processor_params']['currency'] == 'USD') {
            $scripts = '<script type="text/javascript" src="https://static-na.payments-amazon.com/cba/js/us/sandbox/PaymentWidgets.js"></script>';
        } elseif ($processor_data['processor_params']['currency'] == 'EUR') {
            $scripts = '<script type="text/javascript" src="https://static-eu.payments-amazon.com/cba/js/de/sandbox/PaymentWidgets.js"></script>';
        } else {
            $scripts = '<script type="text/javascript" src="https://static-eu.payments-amazon.com/cba/js/gb/sandbox/PaymentWidgets.js"></script>';
        }
    } else {
        if ($processor_data['processor_params']['currency'] == 'USD') {
            $scripts = '<script type="text/javascript" src="https://static-na.payments-amazon.com/cba/js/us/PaymentWidgets.js"></script>';
        } elseif ($processor_data['processor_params']['currency'] == 'EUR') {
            $scripts = '<script type="text/javascript" src="https://static-eu.payments-amazon.com/cba/js/de/PaymentWidgets.js"></script>';
        } else {
            $scripts = '<script type="text/javascript" src="https://static-eu.payments-amazon.com/cba/js/gb/PaymentWidgets.js"></script>';
        }
    }

    if (empty($_payment_id)) {
        $_payment_id = '0';
    }

    $checkout_buttons[$_payment_id] = '
    ' . $scripts . '
    <div id="cbaButton"></div>
    <script>
        new CBA.Widgets.StandardCheckoutWidget({
        merchantId:"' . $merchant_id . '",
        orderInput: {format: "XML",
        value: "type:' . $order_type . ';order:' . $base64cart . ';' . $sign . '"},
        buttonSettings: {size:"' . $processor_data['processor_params']['button_size'] . '", color:"' . $processor_data['processor_params']['button_color'] . '",
        background:"' . $processor_data['processor_params']['button_background'] . '"}}).render("cbaButton");
    </script>';
}
