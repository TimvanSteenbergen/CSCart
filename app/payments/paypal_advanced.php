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

if (defined('PAYMENT_NOTIFICATION') && !empty($_REQUEST['order_id'])) {
    $order_id = (int) $_REQUEST['order_id'];
    if ($mode == 'return') {
        if (fn_check_payment_script('paypal_advanced.php', $order_id)) {
            $url = fn_url("payment_notification.finish?payment=paypal_advanced&order_id=$order_id");
            $pp_response['order_status'] = $_REQUEST['RESULT'] === '0' ? 'P' : 'F';
            $pp_response["reason_text"] = 'Reason : ' . $_REQUEST['RESULT'] . ' / ' . urldecode($_REQUEST['RESPMSG']);
            fn_finish_payment($order_id, $pp_response, false);
            Registry::get('view')->assign('onload', 'javascript: top.location = ' . "'$url'" . ';');
            Registry::get('view')->assign('order_action', __('text_paypal_processing_payment'));
            Registry::get('view')->display('views/orders/components/placing_order.tpl');
            fn_flush();
        }
    } elseif ($mode == 'cancel') {
        $pp_response['order_status'] = 'N';
        $pp_response['reason_text'] = __('text_transaction_cancelled');
        fn_finish_payment($order_id, $pp_response, false);
        fn_order_placement_routines('route', $order_id);
    } elseif ($mode == 'finish') {
        fn_order_placement_routines('route', $order_id);
    }
    exit;
} else {
        $paypal_total = fn_format_price($order_info['total']);
        $cancel_url = fn_url("payment_notification.cancel?payment=paypal_advanced&order_id=$order_id");
        $post_data = array(
            'VENDOR'            => $processor_data['processor_params']['merchant_login'],
            'PARTNER'           => $processor_data['processor_params']['api_partner'],
            'USER'              => $processor_data['processor_params']['api_user'],
            'PWD'               => $processor_data['processor_params']['api_password'],
            'TRXTYPE'           => 'S',
            'AMT'               => $paypal_total,
            'TENDER' 	    	=> 'C',
            'CREATESECURETOKEN' => 'Y',
            'SECURETOKENID'     => uniqid(rand()),
            'DISABLERECEIPT'    => 'TRUE',
            'RETURNURL'         => fn_url("payment_notification.return?payment=paypal_advanced&order_id=$order_id&security_hash=" . fn_generate_security_hash()),
            'CANCELURL'         => $cancel_url,
            'ERRORURL'          => fn_url("payment_notification.return?payment=paypal_advanced&order_id=$order_id&security_hash=" . fn_generate_security_hash()),
            'URLMETHOD'         => 'POST',
            'TEMPLATE'          => $processor_data['processor_params']['layout'],
            'PAGECOLLAPSEBGCOLOR' => $processor_data['processor_params']['collapse_bg_color'],
            'PAGECOLLAPSETEXTCOLOR' => $processor_data['processor_params']['collapse_text_color'],
            'PAGEBUTTONBGCOLOR' => $processor_data['processor_params']['button_bgcolor'],
            'PAGEBUTTONTEXTCOLOR' => $processor_data['processor_params']['button_text_color'],
            'BUTTONTEXT' => $processor_data['processor_params']['label_text_color'],
            'PAYFLOWCOLOR' => $processor_data['processor_params']['payflowcolor'],
            'HDRIMG' => $processor_data['processor_params']['header_image'],
            'BILLTOFIRSTNAME' => $order_info['b_firstname'],
            'BILLTOLASTNAME' => $order_info['b_lastname'],
            'BILLTOSTREET' => $order_info['b_address'],
            'BILLTOCITY' => $order_info['b_city'],
            'BILLTOSTATE' => fn_pp_get_state($order_info, 'b_'),
            'BILLTOZIP' => $order_info['b_zipcode'],
            'BILLTOCOUNTRY' => $order_info['b_country'],
            'SHIPTOFIRSTNAME' => $order_info['s_firstname'],
            'SHIPTOLASTNAME' => $order_info['s_firstname'],
            'SHIPTOSTREET' => $order_info['s_address'],
            'SHIPTOCITY' => $order_info['s_city'],
            'SHIPTOSTATE' => fn_pp_get_state($order_info, 's_'),
            'SHIPTOZIP' => $order_info['s_zipcode'],
            'SHIPTOCOUNTRY' => $order_info['s_country'],
            'EMAIL' => $order_info['email'],
            'PHONENUM' => $order_info['phone'],
    );

    $result = fn_pp_request($post_data, $processor_data['processor_params']['testmode']);

    if ($result['RESULT'] == '0') {
        $query_data = array(
            'SECURETOKEN' => $result['SECURETOKEN'],
            'SECURETOKENID' => $result['SECURETOKENID'],
            'MODE' => ($processor_data['processor_params']['testmode'] == 'Y' ? 'TEST' : '')
            );

        $iframe_url = 'https://payflowlink.paypal.com/?' . http_build_query($query_data);

        Registry::get('view')->assign('iframe_url', $iframe_url);
        Registry::get('view')->assign('cancel_url', $cancel_url);

        Registry::get('view')->display('views/checkout/processors/paypal_advanced.tpl');
        exit;
    } else {
        $pp_response['order_status'] = 'F';
        $pp_response["reason_text"] = 'RESULT:' . $result['RESULT'] . '; RESPMSG:' . $result['RESPMSG'];
    }
}

function fn_pp_get_state($order_info, $prefix = 's_')
{
    if ($order_info[$prefix . 'state']) {
        $state = $order_info[$prefix . 'state'];
    } else {
        $state = 'Other';
    }

    return $state;
}

function fn_pp_request($data, $mode)
{
    $_post = array();
    if (!empty($data)) {
        foreach ($data as $index => $value) {
            $_post[] = $index . '[' . strlen($value) . ']='. $value;
        }
    }
    $_post = implode('&', $_post);
    $url = 'https://' . ($mode == 'Y' ? 'pilot-payflowpro.paypal.com' : 'payflowpro.paypal.com');
    $response = Http::post($url, $_post, array(
        'headers' => array(
            'Content-type: application/x-www-form-urlencoded',
            'Connection: close'
        ),
    ));
    $result = fn_pp_get_result($response);

    return $result;
}

function fn_pp_get_result($data)
{
    if (!$data || !is_string($data)) {
        return false;
    }

    parse_str($data);
    $res = array(
            'RESULT' => $RESULT,
            'SECURETOKENID' => $SECURETOKENID,
            'SECURETOKEN' => $SECURETOKEN,
            'RESPMSG' => $RESPMSG
    );

    return $res;
}
