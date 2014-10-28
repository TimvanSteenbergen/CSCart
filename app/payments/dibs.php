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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

    $pp_response = array();

    $order_id = intval($_REQUEST['order_id']);

    if ($mode == 'accept') {

        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id=?i", $order_id);
        $processor_data = fn_get_processor_data($payment_id);

        $amount = db_get_field("SELECT total FROM ?:orders WHERE order_id=?i", $order_id);
        $amount = str_replace('.', '', $amount);
        $_REQUEST['fee'] = (!empty($_REQUEST['fee']))? $_REQUEST['fee'] : 0;
        $amount_with_fee = $amount + $_REQUEST['fee'];

         if (!empty($_REQUEST['transact'])) {
            $key = md5($processor_data['processor_params']['key2'] . md5($processor_data['processor_params']['key1'] . 'transact=' . $_REQUEST['transact'] . '&amount=' . $amount . '&currency=' . $processor_data['processor_params']['currency']));
            $key_with_fee = md5($processor_data['processor_params']['key2'] . md5($processor_data['processor_params']['key1'] . 'transact=' . $_REQUEST['transact'] . '&amount=' . $amount_with_fee . '&currency=' . $processor_data['processor_params']['currency']));
        }

        if (!empty($_REQUEST['transact']) && ($_REQUEST['authkey'] == $key || $_REQUEST['authkey'] == $key_with_fee)) {
            $pp_response['order_status']   = 'P';
            $pp_response['reason_text']    = __('transaction_approved');
            $pp_response['transaction_id'] = $_REQUEST['transact'];
        } else {
            $pp_response['order_status']   = 'F';
            $pp_response['reason_text']    = __('transaction_declined');
        }
    } else {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text']  = __('transaction_declined');
    }

    if (fn_check_payment_script('dibs.php', $order_id)) {
        fn_finish_payment($order_id, $pp_response);
        fn_order_placement_routines('route', $order_id);
    }
} else {

    $currencies = array(
        208 => 'DKK',
        978 => 'EUR',
        840 => 'USD',
        826 => 'GBP',
        752 => 'SEK',
        036 => 'AUD',
        124 => 'CAD',
        352 => 'ISK',
        392 => 'JPY',
        554 => 'NZD',
        578 => 'NOK',
        756 => 'CHF',
        949 => 'TRY'
    );
    $languages = array (
        "da",
        "sv",
        "no",
        "en",
        "nl",
        "de",
        "fr",
        "fi",
        "es",
        "it",
        "fo",
        "pl",
    );

    $post_address = "https://payment.architrade.com/paymentweb/start.action";

    $msg = __('text_cc_processor_connection', array(
        '[processor]' => 'DIBS'
    ));

    $lang_code = Registry::get('settings.Appearance.backend_default_language');

    $post = array();
    $post['order_id'] = $processor_data['processor_params']['order_prefix'].(($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id);
    $post['currency'] = $processor_data['processor_params']['currency'];
    $post['amount'] = $order_info['total'] * 100;

    $md5key = md5($processor_data['processor_params']['key2'] . md5($processor_data['processor_params']['key1'] . 'merchant=' . $processor_data['processor_params']['merchant'] . '&orderid=' . $post['order_id'] . '&currency=' . $post['currency'] . '&amount=' . $post['amount']));

    $post_data = array(
        'merchant' => $processor_data['processor_params']['merchant'],
        'orderid' => $post['order_id'],
        'currency' => $post['currency'],
        'amount' => $post['amount'],
        'accepturl' => fn_url("payment_notification.accept?payment=dibs&order_id=$order_id", AREA, 'current'),
        'cancelurl' => fn_url("payment_notification.cancel?payment=dibs&order_id=$order_id", AREA, 'current'),
        'uniqueoid' =>'yes',
        'ip' => $order_info['ip_address'],
        'paytype' => 'ACCEPT,ACK,AMEX,AMEX(DK),BHBC,CCK,CKN,COBK,DIN,DIN(DK),DK,ELEC,VISA,EWORLD,FCC,FCK,FFK,FSC,FSBK,FSSBK,GSC,GRA,HBSBK,HMK,ICASBK,IBC,IKEA,JPSBK,JCB,LIC(DK),LIC(SE),MC,MC(DK),MC(SE),MTRO,MTRO(DK),MTRO(UK),MTRO(SOLO),MEDM,MERLIN(DK),MOCA,NSBK,OESBK,PGSBK,Q8SK,Q8LIC,RK,SLV,SBSBK,S/T,SBC,SBK,SEBSBK,TKTD,TUBC,TLK,VSC,V-DK,VEKO,VISA,VISA(DK),VISA(SE),ELEC,WOCO,AAK',
        'calcfee' => 'no',
        'skiplastpage' => $processor_data['processor_params']['skiplastpage'],
        'lang' => in_array(CART_LANGUAGE, $languages) ? CART_LANGUAGE : $processor_data['processor_params']['lang'],
        'color' => $processor_data['processor_params']['color'],
        'decorator' => $processor_data['processor_params']['decorator'],
        'md5key' => $md5key
    );
        
    if ($processor_data['processor_params']['test'] == 'test') {
        $post_data['test'] = 'yes';
    }

    $all_fields = fn_get_profile_fields('O');
    $i = 1;
    foreach ($all_fields as $k => $fields) {
        if ($k == 'C') {
            $name = __('contact_information', '', $lang_code);
        } elseif ($k == 'B') {
            $name = __('billing_address', '', $lang_code);
        } elseif ($k == 'S') {
            $name = __('shipping_address', '', $lang_code);
        }

        $post_data['delivery' . $i . '.' . $name] = ' ';
        $i++;
        foreach ($fields as $kf => $field) {
            $post_data['delivery' . $i . '.' . $field['description']] = $order_info[$field['field_name']];
            $i++;
        }
    }

    $post_data['ordline0-1'] = __('product_id', '', $lang_code);
    $post_data['ordline0-2'] = __('sku', '', $lang_code);
    $post_data['ordline0-3'] = __('product_name', '', $lang_code);
    $post_data['ordline0-4'] = __('amount', '', $lang_code);
    $post_data['ordline0-5'] = __('price', '', $lang_code);

    $i = 1;
    foreach ($order_info['products'] as $k => $item) {
        $post_data['ordline' . $i . '-1'] = $item['product_id'];
        $post_data['ordline' . $i . '-2'] = $item['product_code'];
        $post_data['ordline' . $i . '-3'] = $item['product'];
        $post_data['ordline' . $i . '-4'] = $item['amount'];
        $post_data['ordline' . $i . '-5'] = $item['price'];
        $i++;
    }

    if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
        foreach ($order_info['taxes'] as $tax_id => $tax) {
            if ($tax['price_includes_tax'] == 'N') {
                continue;
            }

            $post_data['ordline' . $i . '-1'] = $tax_id;
            $post_data['ordline' . $i . '-2'] = $tax['regnumber'];
            $post_data['ordline' . $i . '-3'] = $tax['description'];
            $post_data['ordline' . $i . '-4'] = 1;
            $post_data['ordline' . $i . '-5'] = $tax['tax_subtotal'];
            $i++;
        }
    }

    fn_create_payment_form($post_address, $post_data, 'Dibs', false);
}

exit;
