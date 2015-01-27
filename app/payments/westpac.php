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

if (!defined('BOOTSTRAP')) {

    require './init_payment.php';

    if (!empty($_REQUEST['EncryptedParameters'])) {
        $payment_id = db_get_field("SELECT a.payment_id FROM ?:payments as a LEFT JOIN ?:payment_processors as b ON a.processor_id = b.processor_id WHERE a.status = 'A' AND b.processor_script = 'westpac.php' LIMIT 1");
        $processor_data = fn_get_payment_method_data($payment_id);

        $params = fn_payway_decrypt_parameters($processor_data['processor_params']['encryption_key'], $_REQUEST['EncryptedParameters'], $_REQUEST['Signature']);
        if (!empty($params)) {
            $status = db_get_field("SELECT status FROM ?:orders WHERE order_id = ?i", $params['payment_reference']);
            if ($status == 'N') {
                $approved_response_codes = array('00', '08', 'QS');
                if (!empty($params['bank_reference']) && in_array($params['response_code'], $approved_response_codes)) {
                    $pp_response["order_status"] = 'P';
                    $pp_response["reason_text"] = "Authorization code: " . $params['bank_reference'];
                } else {
                    $pp_response["order_status"] = 'F';
                }

                $pp_response['transaction_id'] = $params['payment_number'];
                if (fn_check_payment_script('westpac.php', $params['payment_reference'])) {
                    fn_finish_payment($params['payment_reference'], $pp_response, false);
                }
            }

            fn_order_placement_routines('route', $params['payment_reference']);
        }
    }
    exit;

} else {
    $merchant_id = ($processor_data['processor_params']['mode'] == 'test') ? 'TEST' : $processor_data['processor_params']['merchant_id'];
    $biller_code = $processor_data['processor_params']['biller_code'];
    $url = 'https://www.payway.com.au/MakePayment';

    $data = array(
        'merchant_id' => $merchant_id,
        'biller_code' => $biller_code,
        'payment_reference' => $order_id,
        'receipt_address' => $order_info['email']
    );

    // Products
    if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $k => $v) {
            if (!empty($v['product_options'])) {
                $opts = '';
                foreach ($v['product_options'] as $key => $val) {
                    $opts .= $val['option_name'] . ':' . $val['variant_name'] . '; ';
                }
                $v['product'] .= ' (' . $opts . ')';
            }
            $v['one_product_price'] = fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount']);
            $data[$v['product']] = $v['amount'] . ',' . $v['one_product_price'];
        }
    }
    
    // Gift Certificates
    if (!empty($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $v) {
            $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
            $data[$v['gift_cert_code']] = '1,' . $v['amount'];
        }
    }

    if (!empty($order_info['use_gift_certificates'])) {
        foreach ($order_info['use_gift_certificates'] as $k => $v) {
            $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
            $data[$k] = '1,-' . $v['amount'];
        }
    }

    // Payment surcharge
    if (floatval($order_info['payment_surcharge'])) {
        $desc = __('payment_surcharge');
        $data[$desc] = $order_info['payment_surcharge'];
    }


    if (floatval($order_info['subtotal_discount'])) {
        $desc = __('order_discount');
        $pr = fn_format_price($order_info['subtotal_discount']);
        $data[$desc] = '-' . $pr;
    }

    // Shipping
    if ($sh = fn_order_shipping_cost($order_info)) {
        $desc = __('shipping_cost');
        $data[$desc] = $sh;
    }

    fn_create_payment_form($url, $data, 'PayWay server');
}

function fn_payway_pkcs5_unpad($text)
{
    $pad = ord($text{strlen($text)-1});
    if ($pad > strlen($text)) {
        return false;
    }
    if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
        return false;
    }

    return substr($text, 0, -1 * $pad);
}

function fn_payway_decrypt_parameters($key, $params, $signature)
{
    $key = base64_decode($key);
    $iv = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";
    $td = mcrypt_module_open('rijndael-128', '', 'cbc', '');

    // Decrypt the parameter text
    mcrypt_generic_init($td, $key, $iv);
    $parameters_text = mdecrypt_generic($td, base64_decode($params));
    $parameters_text = fn_payway_pkcs5_unpad($parameters_text);
    mcrypt_generic_deinit($td);

    // Decrypt the signature value
    mcrypt_generic_init($td, $key, $iv);
    $hash = mdecrypt_generic($td, base64_decode($signature));
    $hash = bin2hex( fn_payway_pkcs5_unpad($hash));
    mcrypt_generic_deinit($td);

    mcrypt_module_close($td);

    // Compute the MD5 hash of the parameters
    $computed_hash = md5($parameters_text);

    // Check the provided MD5 hash against the computed one
    if ($computed_hash != $hash) {
        trigger_error( "Invalid parameters signature" );
    }

    $parameter_array = explode('&', $parameters_text);
    $parameters = array();

    // Loop through each parameter provided
    foreach ($parameter_array as $parameter) {
        list($param_name, $param_value ) = explode('=', $parameter);
        $parameters[urldecode($param_name)] = urldecode($param_value);
    }

    return $parameters;
}
