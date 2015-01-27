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

function fn_amazon_calculate_signature($data, $key)
{
    $rawHmac = hash_hmac('sha1', $data, $key, true);

    return base64_encode($rawHmac);
}

function fn_amazon_validate_request($processor_data, $request)
{
    if (!empty($processor_data['processor_params']['aws_access_public_key'])) {
        $sign = fn_amazon_calculate_signature(urldecode($request['UUID']) . $request['Timestamp'], $processor_data['processor_params']['aws_secret_access_key']);

        if (trim($sign) != trim($request['Signature'])) {
            return false;
        }
    }

    return true;
}

function fn_amazon_validate_cart_data($cart, $request)
{
    $items = array();
    $_items = $request->CallbackOrderCart->CallbackOrderCartItems;
    if (empty($_items)) {
        $_items = $request->ProcessedOrder->ProcessedOrderItems;
        foreach ($_items->ProcessedOrderItem as $item) {
            $items[] = $item;
        }
    } else {
        foreach ($_items->CallbackOrderCartItem as $item) {
            $items[] = $item;
        }
    }

    $cart_items_amount = count($cart['products']);

    fn_set_hook('amazon_validate_cart', $items, $cart, $cart_items_amount);

    if (count($items) == $cart_items_amount || (count($items) - 1) == $cart_items_amount) {
        foreach ($items as $item) {
            $sku = (string) $item->Item->SKU;
            if (empty($sku)) {
                $sku = (string) $item->SKU;
                $qty = (string) $item->Quantity;
                $cart_id = (string) $item->ItemCustomData->CartID;
            } else {
                $qty = (string) $item->Item->Quantity;
                $cart_id = (string) $item->Item->ItemCustomData->CartID;
            }

            $is_valid = false;
            fn_set_hook('amazon_validate_cart_item', $cart, $sku, $qty, $cart_id, $is_valid);

            if ($is_valid || ($sku == 'taxes' && empty($cart_id))) {
                continue;
            } elseif (!isset($cart['products'][$cart_id]) || $cart['products'][$cart_id]['amount'] != $qty) {
                return false;
            }
        }
    } else {
        return false;
    }

    return true;
}

function fn_prepare_to_place_order(&$xml_data, &$cart, &$auth)
{
    // Update user info
    $bill = $ship = $xml_data->ProcessedOrder->ShippingAddress;

    $b_customer_name = $s_customer_name = (string) $bill->Name;

    $cart['user_data'] = array_merge($cart['user_data'], array(
        'firstname' => substr($s_customer_name, 0, strpos($s_customer_name, ' ')),
        'lastname' => substr($s_customer_name, strpos($s_customer_name, ' ')),
        'email' => (string) $xml_data->ProcessedOrder->BuyerInfo->BuyerEmailAddress,

        'b_firstname' => substr($b_customer_name, 0, strpos($b_customer_name, ' ')),
        'b_lastname' => substr($b_customer_name, strpos($b_customer_name, ' ')),
        'b_address' => (string) $bill->AddressFieldOne,
        'b_address_2' => (string) $bill->AddressFieldTwo,
        'b_city' => (string) $bill->City,
        //'b_state' => $bill->getValueByPath('/State'), // Amazon workaround
        'b_country' => (string) $bill->CountryCode,
        'b_zipcode' => (string) $bill->PostalCode,

        's_firstname' => substr($s_customer_name, 0, strpos($s_customer_name, ' ')),
        's_lastname' => substr($s_customer_name, strpos($s_customer_name, ' ')),
        's_address' => (string) $ship->AddressFieldOne,
        's_address_2' => (string) $ship->AddressFieldTwo,
        's_city' => (string) $ship->City,
        //'s_state' => $ship->getValueByPath('/State'), // Amazon workaround
        's_country' => (string) $ship->CountryCode,
        's_zipcode' => (string) $ship->PostalCode,
    ));

    // Update shipping info
    $selected_shipping = (string) $xml_data->ProcessedOrder->DisplayableShippingLabel;
    $selected_shipping = preg_replace('/\(' . __('price_includes_tax') . '.*/i', '', $selected_shipping);

    $shipping_id = db_get_field('SELECT shipping_id FROM ?:shipping_descriptions WHERE shipping = ?s AND lang_code = ?s', trim($selected_shipping), CART_LANGUAGE);

    $order_items = array();
    $_order_items = $xml_data->ProcessedOrder->ProcessedOrderItems;

    foreach ($_order_items->ProcessedOrderItem as $item) {
        $order_items[] = $item;
    }

    // Calculate total shipping cost
    $total = sizeof($order_items);
    $shipping_total = 0;
    for ($i = 0; $i < $total; $i++) {
        $elm = $order_items[$i];
        $attrs = $elm->ItemCharges;
        $components = array();
        if (!empty($attrs)) {
            foreach ($attrs->Component as $attr) {
                $components[] = $attr;
            }
        }

        $attrs_total = sizeof($components);
        for ($j = 0; $j < $attrs_total; $j++) {
            $attr = $components[$j];
            if (trim((string) $attr->Type) == 'Shipping') {
                $shipping_total += (string) $attr->Charge->Amount;
            }
        }
    }

    $cart['recalculate'] = true;
    list($cart_products, $product_groups) = fn_calculate_cart_content($cart, $auth, 'A', true, 'F', true);

    foreach ($product_groups as $group_key => $group) {
        foreach ($group['shippings'] as $sh_id => $shipping) {
            if ($shipping['shipping_id'] == $shipping_id) {
                $cart['chosen_shipping'][$group_key] = $sh_id;
            }
        }
    }

    $cart['payment_id'] = db_get_field("SELECT a.payment_id FROM ?:payments as a LEFT JOIN ?:payment_processors as b ON a.processor_id = b.processor_id WHERE b.processor_script = ?s", 'amazon_checkout.php');

    list($order_id) = fn_place_order($cart, $auth, 'save');
    // This string is here because payment_cc.php file wasn't executed
    db_query("REPLACE INTO ?:order_data (order_id, type, data) VALUES (?i, 'S', ?i)", $order_id, TIME);

    return $order_id;
}

function fn_amazon_successfull_response()
{
    echo '<?xml version="1.0" encoding="UTF-8"?><Response>200</Response>';
}
