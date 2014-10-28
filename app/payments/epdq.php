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

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'process') {
        if (isset($_REQUEST['SHASIGN']) && isset($_REQUEST['orderID']) & isset($_REQUEST['STATUS'])) {
            $status_code = (int) $_REQUEST['STATUS'];

            $order_id = (int) $_REQUEST['orderID'];
            $order_id = (strpos($_REQUEST['orderID'], '_')) ? substr($_REQUEST['orderID'], 0, strpos($_REQUEST['orderID'], '_')) : $_REQUEST['orderID'];
            $order_info = fn_get_order_info($order_id);
            $processor_data = $order_info['payment_method']['processor_params'];

            $data = $_REQUEST;
            unset($data['dispatch']);
            unset($data['payment']);
            unset($data['SHASIGN']);
            uksort($data, 'strcasecmp');

            $sucess_statuses = array(
                '5' => 'Authorised',
                '9' => 'Payment requested',
            );

            $intermediary_statuses = array(
                '50' => 'Authorized waiting external result',
                '51' => 'Authorisation waiting',
                '52' => 'Authorisation not known',
                '55' => 'Standby',
                '56' => 'OK with scheduled payments',
                '57' => 'Not OK with scheduled payments',
                '59' => 'Authoris. to be requested manually',
                '91' => 'Payment processing',
                '92' => 'Payment uncertain',
                '93' => 'Payment refused',
                '94' => 'Refund declined by the acquirer',
                '95' => 'Payment processed by merchant',
                '99' => 'Being processed',
            );

            if (strtoupper($_REQUEST['SHASIGN']) == strtoupper(fn_epdq_generate_sha1_hash($data, $processor_data['epdq_passphrase'], false))) {
                if (in_array($status_code, array_keys($sucess_statuses))) {
                    $pp_response['order_status'] = 'P';
                    $pp_response['reason_text'] = $sucess_statuses[$status_code];
                } elseif (in_array($status_code, array_keys($intermediary_statuses))) {
                    $pp_response['order_status'] = 'O';
                    $pp_response['reason_text'] = $intermediary_statuses[$status_code];
                } else {
                    $pp_response['order_status'] = 'F';
                    $pp_response['reason_text'] = $_REQUEST['NCERROR'] . ': ' . $_REQUEST['NCERRORPLUS'];
                }
            } else {
                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = ($status_code == 1) ? __('text_transaction_declined') : __('payments.epdq.hash_error');
            }
            fn_finish_payment($order_id, $pp_response);
            exit;
        } else {
            die('Access_denied');
        }
    } elseif ($mode == 'sucess' || $mode == 'decline') {
        //we can use common code for both modes bacause necessary actions was done in callback ($mode == 'process')
        if (isset($_REQUEST['order_id'])) {
            $order_id = (int) $_REQUEST['order_id'];
            if (fn_check_payment_script('epdq.php', $order_id)) {
                fn_order_placement_routines('route', $order_id, false);
            }
        }
    } elseif ($mode == 'cancel') {
        if (isset($_REQUEST['order_id'])) {
            $order_id = (int) $_REQUEST['order_id'];
            if (fn_check_payment_script('epdq.php', $order_id)) {
                $pp_response['order_status'] = 'N';
                $pp_response['reason_text']  = __('text_transaction_cancelled');
                fn_finish_payment($order_id, $pp_response);
                fn_order_placement_routines('route', $order_id, false);
            }
        }
    }
} else {
    $form_url = ($processor_data['processor_params']['epdq_mode'] == 'test') ? 'https://mdepayments.epdq.co.uk/ncol/test/orderstandard_utf8.asp' : 'https://payments.epdq.co.uk/ncol/prod/orderstandard_utf8.asp';
    $sucess_url = fn_url("payment_notification.sucess?payment=epdq&order_id=$order_id", AREA, 'current');
    $decline_url = fn_url("payment_notification.decline?payment=epdq&order_id=$order_id", AREA, 'current');
    $exception_url = $cancel_url = fn_url("payment_notification.cancel?payment=epdq&order_id=$order_id", AREA, 'current');
    $post = array(
        'PSPID' => $processor_data['processor_params']['epdq_pspid'],
        'ORDERID' => (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . '_' . fn_date_format(time(), '%H_%M_%S'),
        'AMOUNT' => $order_info['total'] * 100,
        'CURRENCY' => $processor_data['processor_params']['epdq_currency'],
        'LANGUAGE' => $processor_data['processor_params']['epdq_language'],
        'CN' => (!empty($order_info['b_firstname']) ? $order_info['b_firstname'] : '') . ' ' . (!empty($order_info['b_lastname']) ? $order_info['b_lastname'] : ''),
        'EMAIL' => !empty($order_info['email']) ? $order_info['email'] : '',
        'OWNERADDRESS' => (!empty($order_info['b_address']) ? $order_info['b_address'] : '') . ' ' . (!empty($order_info['b_address2']) ? $order_info['b_address2'] : ''),
        'OWNERZIP' => !empty($order_info['b_zipcode']) ? $order_info['b_zipcode'] : '',
        'OWNERTOWN' => !empty($order_info['b_city']) ? $order_info['b_city'] : '',
        'OWNERCTY' => !empty($order_info['b_country']) ? $order_info['b_country'] : '',
        'OWNERTELNO' => !empty($order_info['b_phone']) ? $order_info['b_phone'] : '',
        'TITLE' => $processor_data['processor_params']['epdq_form_title'],
        'BGCOLOR' => $processor_data['processor_params']['epdq_form_bgcolor'],
        'TXTCOLOR' => $processor_data['processor_params']['epdq_form_textcolor'],
        'TBLBGCOLOR' => $processor_data['processor_params']['epdq_form_tbl_bgcolor'],
        'TBLTXTCOLOR' => $processor_data['processor_params']['epdq_form_tbl_textcolor'],
        'BUTTONBGCOLOR' => $processor_data['processor_params']['epdq_form_btn_bgcolor'],
        'BUTTONTXTCOLOR' => $processor_data['processor_params']['epdq_form_btn_textcolor'],
        'FONTTYPE' => $processor_data['processor_params']['epdq_form_font_type'],
        'LOGO' => $processor_data['processor_params']['epdq_logo'],
        'ACCEPTURL' => $sucess_url,
        'DECLINEURL' => $decline_url,
        'EXCEPTIONURL' => $exception_url,
        'CANCELURL' => $cancel_url,
        'BACKURL' => $cancel_url,
        'CATALOGURL' => fn_url(),
        'HOMEURL' => fn_url(),
        'OPERATION' =>  $processor_data['processor_params']['epdq_operation']
    );

    if ($processor_data['processor_params']['epdq_3dsecure'] !== 'none') {
        $post['WIN3DS'] = $processor_data['processor_params']['epdq_3dsecure'];
    }
    //Calculate percentage discount
    $discount = 0;
    $discount_amount = 0;
    if (!empty($order_info['subtotal_discount'])) {
        $discount_amount += $order_info['subtotal_discount'];
    }
    if (!empty($order_info['use_gift_certificates'])) {
        foreach ($order_info['use_gift_certificates'] as $gc_data) {
            $discount_amount += $gc_data['amount'];
        }
    }
    if ($discount_amount) {
        $discount = ($discount_amount / $order_info['subtotal']) * 100;
    }
    $key = 1;
    if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $order_product) {
            $post["ITEMID$key"] = $order_product['product_id'];
            $post["ITEMNAME$key"] = fn_format_long_string($order_product['product'], 40);
            $post["ITEMPRICE$key"] = $order_product['price'];
            $post["ITEMQUANT$key"] = $order_product['amount'];
            $post["ITEMDISCOUNT$key"] = $discount;
            $key ++;
        }
    }
    $shipping_cost = floatval($order_info['shipping_cost']);
    //Shipping cost and taxes should be handled as separate items, so the AMOUNT parameter value is the same as the sum of all submitted items.
    if (!empty($shipping_cost)) {
        $post["ITEMID$key"] = $order_info['shipping_ids'];
        $post["ITEMNAME$key"] = __('shipping_cost');
        $post["ITEMPRICE$key"] = $shipping_cost;
        $post["ITEMQUANT$key"] = 1;
        $key ++;
    }

    if (!empty($order_info['taxes'])) {
        foreach ($order_info['taxes'] as $tax_id => $tax_data) {
            if ($tax_data['price_includes_tax'] == 'N') {
                $post["ITEMID$key"] = $tax_id;
                $post["ITEMNAME$key"] = fn_format_long_string($tax_data['description'], 40);
                $post["ITEMPRICE$key"] = $tax_data['tax_subtotal'];
                $post["ITEMQUANT$key"] = 1;
                $key ++;
            }
        }
    }
    $payment_surcharge = floatval($order_info['payment_surcharge']);
    if (!empty($payment_surcharge)) {
        $post["ITEMID$key"] = 1;
        $post["ITEMNAME$key"] = __('payment_surcharge');
        $post["ITEMPRICE$key"] = $payment_surcharge;
        $post["ITEMQUANT$key"] = 1;
        $key ++;
    }

    //All values should be sorted alphabetically before make hash
    ksort($post);
    $post['SHASIGN'] = fn_epdq_generate_sha1_hash($post, $processor_data['processor_params']['epdq_passphrase']);

    fn_create_payment_form($form_url, $post, 'ePDQ');
}
exit;

function fn_epdq_generate_sha1_hash($data, $passphrase, $exlude_empty_values = true)
{
    $str = '';
    foreach ($data as $key => $value) {
        if (!empty($value) || (!$exlude_empty_values && $value == 0)) {
            $str .= strtoupper($key) . '=' . $value . $passphrase;
        }
    }

    return sha1($str);
}
