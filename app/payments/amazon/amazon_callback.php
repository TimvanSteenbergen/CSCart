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

include_once (Registry::get('config.dir.payments') . 'amazon/amazon_func.php');

fn_define('AMAZON_ORDER_DATA', 'Z');

if (!empty($_POST['order-calculations-request'])) {
    $xml_response = $_POST['order-calculations-request'];

} elseif (!empty($_POST['NotificationData'])) {
    $xml_response = $_POST['NotificationData'];
}

if (!empty($_POST['order-calculations-error'])) {
    // Process the Amazon callback error
    $xml_error = $_POST['order-calculations-error'];
    $xml = @simplexml_load_string($xml_error);
    if (empty($xml)) {
        $xml = @simplexml_load_string(stripslashes($xml_error));
    }

    // Get error message
    $code = (string) $xml->OrderCalculationsErrorCode;
    $message = (string) $xml->OrderCalculationsErrorMessage;

    fn_log_event('requests', 'http', array(
        'url' => 'amazon_callback',
        'data' => '',
        'response' => var_export(array($code, $message), true),
    ));
    exit;
}

$xml = @simplexml_load_string($xml_response);
if (empty($xml)) {
    $xml = @simplexml_load_string(stripslashes($xml_response));
}

if (empty($xml)) {
    // ERROR: Failed to parse incoming XML data
    die;
} else {
    $message_recognizer = $xml->getName();
}
if ($message_recognizer == 'OrderCalculationsRequest') {
    list($amazon_sess_id, $payment_id) = explode(';', base64_decode((string) $xml->CallbackOrderCart->CartCustomData->ClientRequestId));

    $processor_data = fn_get_payment_method_data($payment_id);

    // If we use the signed cart, validate the request
    if (!fn_amazon_validate_request($processor_data, $_POST)) {
        die('Access denied');
    }

    // Restart session
    if (!empty($amazon_sess_id)) {
        Session::resetId($amazon_sess_id);
        fn_payments_set_company_id(0, $_SESSION['settings']['company_id']['value']);
        $_SESSION['cart'] = empty($_SESSION['cart']) ? array() : $_SESSION['cart'];
        $cart = & $_SESSION['cart'];
        $_SESSION['auth'] = empty($_SESSION['auth']) ? array() : $_SESSION['auth'];
        $auth = & $_SESSION['auth'];
    }

    // Compare the cart data with the Amazon request
    if (!fn_amazon_validate_cart_data($cart, $xml)) {
        fn_set_notification('E', __('error'), __('text_amazon_incorrect_products_count'));
        exit;
    }

    // Check the data needed
    $callback_response = array();
    $callback_response['Response']['CallbackOrders']['CallbackOrder'] = array();

    $calculate_taxes = (string) $xml->OrderCalculationCallbacks->CalculateTaxRates == 'true' ? true : false;
    $calculate_promotions = (string) $xml->OrderCalculationCallbacks->CalculatePromotions == 'true' ? true : false;
    $calculate_shippings = (string) $xml->OrderCalculationCallbacks->CalculateShippingRates == 'true' ? true : false;

    $address_id = (string) $xml->CallbackOrders->CallbackOrder->Address->AddressId;
    $callback_response['Response']['CallbackOrders']['CallbackOrder']['Address']['AddressId'] = $address_id;

    // Fill the cart address information from the Amazon request
    $address_xml = $xml->CallbackOrders->CallbackOrder->Address;
    $user_data = array(
        'b_address' => (string) $address_xml->AddressFieldOne,
        's_address' => (string) $address_xml->AddressFieldOne,
        'b_address2' => (string) $address_xml->AddressFieldTwo,
        's_address2' => (string) $address_xml->AddressFieldTwo,
        'b_city' => (string) $address_xml->City,
        's_city' => (string) $address_xml->City,
        'b_state' => (string) $address_xml->State,
        's_state' => (string) $address_xml->State,
        'b_zipcode' => (string) $address_xml->PostalCode,
        's_zipcode' => (string) $address_xml->PostalCode,
        'b_country' => (string) $address_xml->CountryCode,
        's_country' => (string) $address_xml->CountryCode,
    );

    if (strpos($user_data['b_zipcode'], '-') !== false) {
        $zip = explode('-', $user_data['b_zipcode']);
        $user_data['b_zipcode'] = $user_data['s_zipcode'] = $zip[0];
    }

    $cart['user_data'] = $user_data;
    $cart['calculate_shipping'] = true;
    list($cart_products, $_SESSION['shipping_product_groups']) = fn_calculate_cart_content($cart, $auth, 'A', true, 'F', true);

    $cart_shippings = array();
    foreach ($_SESSION['shipping_product_groups'] as $key_group => $group) {
        if (!empty($group['shippings'])) {
            foreach ($group['shippings'] as $shipping) {
                $shipping_id = $shipping['shipping_id'];
                if (empty($cart_shippings[$shipping_id])) {
                    $cart_shippings[$shipping_id] = $shipping;
                    $cart_shippings[$shipping_id]['rates'] = array();
                }
                $cart_shippings[$shipping_id]['rates'][$key_group] = $shipping['rate'];
            }
        }
    }

    fn_gather_additional_products_data($cart_products, array('get_icon' => false, 'get_detailed' => false, 'get_options' => true, 'get_discounts' => false));

    // Determine the tax calculation type
    $tax_calculation_type = 'amazon'; // If the tax calculation method is not based on the "subtotal", or taxes have absolute rates or one of the product has multiple applied tax, the calculation type will be changed to "default"

    if (Registry::get('settings.General.tax_calculation') == 'unit_price') {
        $tax_calculation_type = 'default';
    } else {
        $_taxed_products = array();
        if (!empty($cart['taxes'])) {
            foreach ($cart['taxes'] as $tax_id => $tax) {
                if ($tax['rate_type'] != 'P') {
                    $tax_calculation_type = 'default';
                    break;
                }
                if (!empty($tax['applies']['items']['P'])) {
                    foreach ($tax['applies']['items']['P'] as $product_id => $product) {
                        if (isset($_taxed_products[$product_id])) {
                            $tax_calculation_type = 'default';
                            break 2;
                        } else {
                            $_taxed_products[$product_id] = $tax_id;
                        }
                    }
                }
            }
        } else {
            $tax_calculation_type = 'default';
        }
    }

    $callback_response['Response']['CallbackOrders']['CallbackOrder']['CallbackOrderItems']['CallbackOrderItem'] = array();

    if ($tax_calculation_type == 'default') {
        $tax_subtotal = 0;
        $tax_description = '';

        if (!empty($cart['taxes'])) {
            foreach ($cart['taxes'] as $tax_id => $tax) {
                if ($tax['price_includes_tax'] != 'Y') {
                    if (Registry::get('settings.General.tax_calculation') == 'unit_price') {
                        foreach ($tax['applies'] as $tax_key => $tax_rate) {
                            if (strpos($tax_key, 'P_') !== false) {
                                $tax_subtotal += $tax_rate;
                            }
                        }
                    } else {
                        $tax_subtotal += $tax['applies']['P'];
                    }

                    $tax_description .= strip_tags($tax['description']) . ' (' . ($tax['rate_type'] == 'P' ? sprintf("%.2f", $tax['rate_value']) . '%' : '$' . fn_format_price($tax['rate_value'])) . ');';
                }
            }
        }
        $tax_description = empty($tax_description) ? '-' : $tax_description;

        if ($tax_subtotal > 0) {
            $callback_response['Response']['CallbackOrders']['CallbackOrder']['UpdatedCartItems'] = '';
        }
    }

    $items_shipping = '';
    $callback_response['TaxTables'] = '';

    if ($tax_calculation_type == 'amazon') {
        foreach ($cart['taxes'] as $tax_id => $tax) {
            if ($tax['price_includes_tax'] != 'Y') {
                $rate = $tax['rate_value'] / 100;
            } else {
                $rate = 0;
            }

            $callback_response['TaxTables']['TaxTable'][] = array(
                'TaxTableId' => 'tax_' . $tax_id,
                'TaxRules' => array(
                    'TaxRule' => array(
                        'Rate' => $rate,
                        'PredefinedRegion' => 'WorldAll',
                    ),
                ),
            );
        }
    } else {
        $callback_response['TaxTables']['TaxTable'][] = array(
            'TaxTableId' => 'tax_default',
            'TaxRules' => array(
                'TaxRule' => array(
                    'Rate' => '0',
                    'PredefinedRegion' => 'WorldAll',
                ),
            ),
        );
    }

    // Update promotion information
    $promotion_data = array(
        'Promotion' => array(
            'PromotionId' => 'cart-discount',
            'Description' => __('discount'),
            'Benefit' => array(
                'FixedAmountDiscount' => array(
                    'Amount' => empty($cart['subtotal_discount']) ? 0 : fn_format_price($cart['subtotal_discount']),
                    'CurrencyCode' => $processor_data['processor_params']['currency'], // FIXME: use the cart currency convertion
                ),
            ),
        ),
    );

    $callback_response['Promotions'] = $promotion_data;
    fn_set_hook('amazon_calculate_promotions', $callback_response, $cart, $processor_data);
    $callback_response['Response']['CallbackOrders']['CallbackOrder']['CallbackOrderItems'] = '';

    $free_shipping = false;
    $shipping_no_required = false;
    $shipping_required = false;
    foreach ($cart_products as $k => $product_data) {
        if ($product_data['free_shipping'] == 'Y' && $product_data['is_edp'] != 'Y') {
            $free_shipping = true;

        } elseif ($product_data['is_edp'] == 'Y' && $product_data['edp_shipping'] == 'N') {
            $shipping_no_required = true;

        } else {
            $shipping_required = true;
        }
    }

    if (!$shipping_required) {
        $cart_shippings = array();

        if ($free_shipping) {
            $cart_shippings[] = array(
                'shipping' => __('free_shipping'),
                'delivery_time' => '',
                'rates' => array(
                    '0' => 0,
                ),
            );
        } elseif ($shipping_no_required) {
            $cart_shippings[] = array(
                'shipping' => __("no_shipping_required"),
                'delivery_time' => '',
                'rates' => array(
                    '0' => 0,
                ),
            );
        }
    }

    if ($calculate_shippings && !empty($cart_shippings)) {
        // Prepare shipping methods callback
        $callback_response['ShippingMethods'] = '';

        foreach ($cart_shippings as $rate_id => $shipping) {
            // We need to include the tax value to the shipping rate
            $tax_rate = 0;
            if (!empty($shipping['taxes'])) {
                foreach ($shipping['taxes'] as $_tax_id => $tax) {
                    if ($tax['price_includes_tax'] != 'Y') {
                        $tax_rate += $tax['tax_subtotal'];
                    }
                }
            }

            // Try to determine the service level
            $service_level = 'Standard';

            if (preg_match('/(?:1|one).{0,1}day/i', $shipping['shipping'])) {
                $service_level = 'OneDay';

            } elseif (preg_match('/(?:2|two).{0,1}day/i', $shipping['shipping'])) {
                $service_level = 'TwoDay';

            } elseif (preg_match('/(?:express|expedited)/i', $shipping['shipping'])) {
                $service_level = 'Expedited';
            }

            $shipping_data = array(
                'ShippingMethodId' => $shipping['shipping'] . ' ' . $shipping['delivery_time'],
                'ServiceLevel' => $service_level,
                'Rate' => array(
                    'ShipmentBased' => array(
                        'Amount' => fn_format_price(array_sum($shipping['rates']) + $tax_rate),
                        'CurrencyCode' => $processor_data['processor_params']['currency'], // FIXME: use the cart currency convertion
                    ),
                ),
                'IncludedRegions' => array(
                    'PredefinedRegion' => 'WorldAll',
                ),
                'DisplayableShippingLabel' => $shipping['shipping'] . (empty($tax_rate) ? '' : (' (' . __('price_includes_tax') . ': $' . fn_format_price($tax_rate) . ')')),
            );

            $items_shipping['ShippingMethodId'][] = $shipping['shipping'] . ' ' . $shipping['delivery_time'];
            $callback_response['ShippingMethods']['ShippingMethod'][] = $shipping_data;
        }
    }

    $amazon_products = $cart_products;

    fn_set_hook('amazon_products', $amazon_products, $cart);

    foreach ($amazon_products as $key => $product) {
        $sku = (empty($product['product_code']) ? 'pid_' . $product['product_id'] : substr(strip_tags($product['product_code']), 0, 250));

        if ($tax_calculation_type == 'amazon' && isset($_taxed_products[$key])) {
            $tax_table_id = 'tax_' . $_taxed_products[$key];
        } else {
            $tax_table_id = 'tax_default';
        }

        $item = array(
            'CallbackOrderItemId' => $sku,
            'TaxTableId' => $tax_table_id,
            'ShippingMethodIds' => $items_shipping,
        );

        $callback_response['Response']['CallbackOrders']['CallbackOrder']['CallbackOrderItems']['CallbackOrderItem'][] = $item;
    }

    $callback_response['CartPromotionId'] = 'cart-discount';

    // Update the tax info
    if ($tax_calculation_type == 'default' && $tax_subtotal > 0) {
        $tax = array(
            'SKU' => 'taxes',
            'MerchantId' => $processor_data['processor_params']['merchant_id'],
            'Title' => substr($tax_description, 0, 250),
            'Price' => array(
                'Amount' => fn_format_price($tax_subtotal),
                'CurrencyCode' => $processor_data['processor_params']['currency'],
            ),
            'Quantity' => 1,
            'UpdateType' => 'REMOVE',
        );
        $callback_response['Response']['CallbackOrders']['CallbackOrder']['UpdatedCartItems']['UpdatedCartItem'][] = $tax;

        $tax = array(
            'SKU' => 'taxes',
            'MerchantId' => $processor_data['processor_params']['merchant_id'],
            'Title' => __('taxes') . ': ' . substr($tax_description, 0, 240),
            'Price' => array(
                'Amount' => fn_format_price($tax_subtotal),
                'CurrencyCode' => $processor_data['processor_params']['currency'],
            ),
            'Quantity' => 1,
            'ShippingMethodIds' => $items_shipping,
            'UpdateType' => 'ADD',
        );

        $callback_response['Response']['CallbackOrders']['CallbackOrder']['UpdatedCartItems']['UpdatedCartItem'][] = $tax;
    }

    // Generate the full XML response
    $callback_response = '<?xml version="1.0" encoding="UTF-8"?>' .
    '<OrderCalculationsResponse xmlns="http://payments.amazon.com/checkout/2009-05-15/">' .
    fn_array_to_xml($callback_response) .
    '</OrderCalculationsResponse>';

    $_return = 'order-calculations-response=' . urlencode($callback_response);

    if ($processor_data['processor_params']['aws_access_public_key']) {
        $sign = urlencode(fn_amazon_calculate_signature($callback_response, $processor_data['processor_params']['aws_secret_access_key']));
        $aws_access_key = urlencode($processor_data['processor_params']['aws_access_public_key']);

        $_return .= '&Signature=' . $sign;
        $_return .= '&aws-access-key-id=' . $aws_access_key;
    }

    echo $_return;
    exit;

} elseif ($message_recognizer == 'NewOrderNotification') {
    // Order was placed by Amazon checkout. We need to proceed the callback.
    list($amazon_sess_id, $payment_id) = explode(';', base64_decode((string) $xml->ProcessedOrder->ProcessedOrderItems->ProcessedOrderItem->CartCustomData->ClientRequestId));
    $processor_data = fn_get_payment_method_data($payment_id);

    // If we use the signed cart, validate the request
    if (!fn_amazon_validate_request($processor_data, $_POST)) {
        die('Access denied');
    }

    // Restart session
    if (!empty($amazon_sess_id)) {
        Session::resetId($amazon_sess_id);
        fn_payments_set_company_id(0, $_SESSION['settings']['company_id']['value']);
        $cart = & $_SESSION['cart'];
        $auth = & $_SESSION['auth'];
    }

    // Compare the cart data with the Amazon request
    if (!fn_amazon_validate_cart_data($cart, $xml)) {
        fn_set_notification('E', __('error'), 'text_amazon_incorrect_products_count');

        exit;
    }

    $transaction_id = (string) $xml->ProcessedOrder->AmazonOrderID;

    // Prevent the double notifications
    $reference_id = (string) $xml->NotificationReferenceId;
    if (!empty($_SESSION['reference_id']) && $_SESSION['reference_id'] == $reference_id) {
        exit;
    } else {
        $_SESSION['reference_id'] = $reference_id;
    }

    $_order_id = db_get_field('SELECT order_id FROM ?:order_data WHERE type = ?s AND data = ?s', 'E', $transaction_id);
    if (!empty($_order_id)) {
        exit;
    }

    $_SESSION['order_id'] = empty($_SESSION['order_id']) ? array() : $_SESSION['order_id'];
    $order_id = & $_SESSION['order_id'];
    $order_id = fn_prepare_to_place_order($xml, $cart, $auth);

    $pp_response = array (
        'transaction_id' => $transaction_id
    );

    $data = array (
        'order_id' => $order_id,
        'type' => 'E', // extra order ID
        'data' => $transaction_id,
    );
    db_query("REPLACE INTO ?:order_data ?e", $data);

    fn_update_order_payment_info($order_id, $pp_response);

    fn_order_placement_routines('route', $order_id);

} elseif ($message_recognizer == 'OrderCancelledNotification') {
    // Customer cancel this order on the Amazon side. We need to cancel the order in the shop
    list($amazon_sess_id, $payment_id) = explode(';', base64_decode((string) $xml->ProcessedOrder->ProcessedOrderItems->ProcessedOrderItem->CartCustomData->ClientRequestId));
    $processor_data = fn_get_payment_method_data($payment_id);

    // If we use the signed cart, validate the request
    if (!fn_amazon_validate_request($processor_data, $_POST)) {
        die('Access denied');
    }

    $transaction_id = (string) $xml->ProcessedOrder->AmazonOrderID;
    $order_id = db_get_field('SELECT order_id FROM ?:order_data WHERE type = ?s AND data = ?s', 'E', $transaction_id);

    if (!empty($order_id)) {
        fn_change_order_status($order_id, 'I');
    }

} elseif ($message_recognizer == 'OrderReadyToShipNotification') {
    // Order was processed by Amazon. We need to process the order in the shop
    list($amazon_sess_id, $payment_id) = explode(';', base64_decode((string) $xml->ProcessedOrder->ProcessedOrderItems->ProcessedOrderItem->CartCustomData->ClientRequestId));
    $processor_data = fn_get_payment_method_data($payment_id);

    // If we use the signed cart, validate the request
    if (!fn_amazon_validate_request($processor_data, $_POST)) {
        die('Access denied');
    }

    $transaction_id = (string) $xml->ProcessedOrder->AmazonOrderID;
    $order_id = db_get_field('SELECT order_id FROM ?:order_data WHERE type = ?s AND data = ?s', 'E', $transaction_id);

    if (!empty($order_id)) {
        fn_change_order_status($order_id, 'P');
    }
}

exit;
