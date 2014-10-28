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

if (!empty($_REQUEST['etat']) && !empty($_REQUEST['id_trans']) && !empty($_REQUEST['devise_sent']) && !empty($_REQUEST['divers']) && !empty ($_REQUEST['ref'])) {
    require './init_payment.php';
    $order_id = intval($_REQUEST['ref']);
    if (fn_check_payment_script('paysitecash.php', $order_id)) {
        //Parse "divers"
        $divers = array();
        parse_str(base64_decode($_REQUEST['divers']), $divers);
        //Params check
        if ($divers['key'] != md5(fn_format_price($_REQUEST['montant_sent'], CART_PRIMARY_CURRENCY, null, false) . Registry::get('config.crypt_key') . $_REQUEST['ref'])) {
            exit('Wrong checksum');
        }
        $etat = $_REQUEST['etat'];
        $id_trans = $_REQUEST['id_trans'];
        $order_id = intval($_REQUEST['ref']);
        $devise = $_REQUEST['devise_sent'];
        $error = !empty($_REQUEST['errordetail']) ? urldecode($_REQUEST['errordetail']) : '';
        if ($devise != CART_PRIMARY_CURRENCY) {
            $etat = 'currency';
        }
        $pp_response = array (
            'transaction_id' => $id_trans,
        );
        if ($etat == 'ok') {
            $pp_response['order_status'] = 'P';
            $pp_response['reason_text'] = 'Transaction accepted';
        } elseif ($etat == 'ko') {
            $pp_response['order_status'] = 'D';
            $pp_response['reason_text'] = 'Transaction refused' . $error;
        } elseif ($etat == 'wait') {
            $pp_response['order_status'] = 'O';
            $pp_response['reason_text'] = 'Transaction pending';
        } elseif ($etat == 'end') {
            $pp_response['order_status'] = 'O';
            $pp_response['reason_text'] = 'End of subscription';
        } elseif ($etat == 'refund') {
            $pp_response['order_status'] = 'C';
            $pp_response['reason_text'] = 'Refund';
        } elseif ($etat == 'chargeback') {
            $pp_response['order_status'] = 'C';
            $pp_response['reason_text'] = 'Chargeback';
        } elseif ($etat == 'currency') {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = 'Wrong currency';
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = 'Unknown status';
        }
        fn_finish_payment($order_id, $pp_response);
        exit;
    }
} elseif (defined('PAYMENT_NOTIFICATION')) {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }
    if ($mode == 'process') {
        $order_id = intval($_REQUEST['ref']);
        fn_order_placement_routines('route', $order_id, false);
    } elseif ($mode == 'cancel') {
        $params = array();
        parse_str(base64_decode($_REQUEST['divers']), $params);
        $pp_response['order_status'] = 'N';
        $pp_response['reason_text'] = __('text_transaction_cancelled');
        fn_finish_payment($params['order_id'], $pp_response, false);
        fn_order_placement_routines('route', $params['order_id'], false);
    }
} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }
    // Params
    $url = array('psc' => 'https://billing.paysite-cash.biz', 'ep' => 'https://secure.easy-pay.net');
    $site_id = $processor_data['processor_params']['site_id'];
    $currency = $processor_data['processor_params']['currency'];
    $processor = $processor_data['processor_params']['processor'];
    $test = $processor_data['processor_params']['mode'];
    $debug = $processor_data['processor_params']['debug'];
    $nocurrencies = $processor_data['processor_params']['nocurrencies'];

    $order_id = $order_info['order_id'];
    $email = $order_info['email'];
    $total_amount = $order_info['total'];
    $lang = $order_info['lang_code'];
    $divers = base64_encode('key=' . md5($total_amount . Registry::get('config.crypt_key') . $order_id) . '&order_id=' . $order_id);

    $post_data = array(
        'site' => $site_id,
        'ref' => $order_id,
        'montant' => $total_amount,
        'devise' => $currency,
        'divers' => $divers,
        'email' => $email,
        'test' => $test,
        'debug' => $debug,
        'nocurrencies' => $nocurrencies,
        'lang' => $lang,        
    );

    fn_create_payment_form($url[$processor], $post_data);
    exit;
}
