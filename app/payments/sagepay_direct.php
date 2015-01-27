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

if ($_SERVER['REQUEST_METHOD'] == "POST" && !empty($_REQUEST['PaRes']) && !empty($_REQUEST['MD'])) {

    require './init_payment.php';

    $post['MD'] = $_REQUEST['MD'];
    $post['PARes'] = $_REQUEST['PaRes'];
    $secure_verified_3d = true;
    $order_id = $_REQUEST['order_id'];
}

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_SESSION['already_posted'] = empty($_SESSION['already_posted']) ? false : $_SESSION['already_posted'];
$already_posted = & $_SESSION['already_posted'];

if (!empty($secure_verified_3d) && empty($already_posted)) {
    if ($_REQUEST['payment_mode'] == 'Y') {
        $post_address = 'https://test.sagepay.com/gateway/service/direct3dcallback.vsp';
    } elseif ($_REQUEST['payment_mode'] == 'N') {
        $post_address = 'https://live.sagepay.com/gateway/service/direct3dcallback.vsp';
    } elseif ($_REQUEST['payment_mode'] == 'S') {
        $post_address = 'https://test.sagepay.com/Simulator/VSPDirectCallback.asp';
    }

    $result = Http::post($post_address, $post);
    $already_posted = true;

} else {

    $pp_merch = $processor_data['processor_params']['vendor'];
    $pp_curr = $processor_data['processor_params']['currency'];

    if ($processor_data['processor_params']['testmode'] == 'Y') {
        $post_address = 'https://test.sagepay.com/gateway/service/vspdirect-register.vsp';
    } elseif ($processor_data['processor_params']['testmode'] == 'N') {
        $post_address = 'https://live.sagepay.com/gateway/service/vspdirect-register.vsp';
    } elseif ($processor_data['processor_params']['testmode'] == 'S') {
        $post_address = 'https://test.sagepay.com/Simulator/VSPDirectGateway.asp';
    }

    $already_posted = false;
    $card_type = fn_get_payment_card($order_info['payment_info']['card_number'], array(
        'visa' => 'VISA',
        'visa_debit' => 'DELTA',
        'mastercard' => 'MC',
        'mastercard_debit' => 'MCDEBIT',
        'amex' => 'AMEX',
        'jcb' => 'JCB',
        'maestro' => 'MAESTRO',
        'visa_electron' => 'UKE',
        'laser' => 'LASER',
        'diners_club_carte_blanche' => 'DINERS',
        'diners_club_international' => 'DINERS'
    ));

    $post = array();
    $post['VPSProtocol'] = '2.23';
    $post['TxType'] = $processor_data['processor_params']['transaction_type'];
    $post['Vendor'] = $pp_merch;
    $post['VendorTxCode'] = ((!empty($processor_data['processor_params']['order_prefix']) ? $processor_data['processor_params']['order_prefix'] : 'O') . '-' . (($order_info['repaid']) ? ($order_info['order_id'] . '-' . $order_info['repaid']) : $order_info['order_id'])) . '-' . fn_date_format(time(), '%H_%M_%S');
    $post['Amount'] = $order_info["total"];
    $post['Currency'] = $pp_curr;
    $post['Description'] = 'Your Cart';
    $post['CardHolder'] = $order_info['payment_info']['cardholder_name'];
    $post['CardNumber'] = $order_info['payment_info']['card_number'];
    $post['ExpiryDate'] = $order_info['payment_info']['expiry_month'] . $order_info['payment_info']['expiry_year'];
    $post['CV2'] = $order_info['payment_info']['cvv2'];
    $post['CardType'] = $card_type;
    $post['Apply3DSecure'] = 0;

    $post['BillingAddress1'] = $order_info['b_address'];
    $post['BillingAddress2'] = $order_info['b_address_2'];
    //Workariund for the Irish customers. According to the documentation we should enter zip code anyway.
    $post['BillingPostCode'] = !empty($order_info['b_zipcode']) ? $order_info['b_zipcode'] : '0000';
    $post['BillingCountry'] = $order_info['b_country'];
    if ($order_info['b_country'] == 'US') { // state is for US customers only
        $post['BillingState'] = $order_info['b_state'];
    }
    $post['BillingCity'] = $order_info['b_city'];
    $post['BillingFirstnames'] = $order_info['b_firstname'];
    $post['BillingSurname'] = $order_info['b_lastname'];

    $post['DeliveryAddress1'] = $order_info['s_address'];
    $post['DeliveryAddress2'] = $order_info['s_address_2'];
    $post['DeliveryPostCode'] = !empty($order_info['s_zipcode']) ? $order_info['s_zipcode'] : '0000';
    $post['DeliveryCountry'] = $order_info['s_country'];

    if ($order_info['s_country'] == 'US') {// state is for US customers only
        $post['DeliveryState'] = $order_info['s_state'];
    }
    $post['DeliveryCity'] = $order_info['s_city'];
    $post['DeliveryFirstnames'] = $order_info['s_firstname'];
    $post['DeliverySurname'] = $order_info['s_lastname'];

    $post['CustomerName'] = $order_info['firstname'] . ' ' . $order_info['lastname'];
    $post['ContactNumber'] = $order_info['phone'];
    $post['ContactFax'] = $order_info['fax'];
    $post['CustomerEMail'] = $order_info['email'];

    // Form Cart products
    $strings = 0;
    $products_string = '';
    if (!empty($order_info['products']) && is_array($order_info['products'])) {
        $strings += count($order_info['products']);
    }
    if (!empty($order_info['gift_certificates']) && is_array($order_info['gift_certificates'])) {
        $strings += count($order_info['gift_certificates']);
    }
    if (!empty($order_info['products'])) {
        foreach ($order_info['products'] as $v) {
            $products_string .= ":" . str_replace(':', ' ', $v['product']) . ':' . $v['amount'] . ':' . fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount']) . ':::' . fn_format_price($v['subtotal'] - fn_external_discounts($v));
        }
    }
    if (!empty($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $v) {
            $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
            $products_string .= ':' . str_replace(':', ' ', $v['gift_cert_code']) . ':1:' . fn_format_price($v['amount']) . ':::' . fn_format_price($v['amount']);
        }
    }
    if (floatval($order_info['payment_surcharge'])) {
        $products_string .= ':Payment surcharge:---:---:---:---:' . fn_format_price($order_info['payment_surcharge']);
        $strings ++;
    }
    if (fn_order_shipping_cost($order_info)) {
        $products_string .= ':Shipping cost:---:---:---:---:' . fn_format_price($order_info['shipping_cost']);
        $strings ++;
    }

    if (floatval($order_info['subtotal_discount'])) {
        $desc = __('order_discount');
        $pr = fn_format_price($order_info['subtotal_discount']);
        $products_string .= ":{$desc}:---:---:---:---:-" . fn_format_price($order_info['subtotal_discount']);
        $strings ++;
    }

    if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') == 'subtotal') {
        foreach ($order_info['taxes'] as $tax_id => $tax) {
            if ($tax['price_includes_tax'] == 'N') {
                $desc = $tax['description'];
                $products_string .= ":{$desc}:---:---:---:---:" . fn_format_price($tax['tax_subtotal']);
                $strings ++;
            }
        }
    }

    $post['Basket'] = $strings . $products_string;

    $post['ClientIPAddress'] = $_SERVER['REMOTE_ADDR'];

    Registry::set('log_cut_data', array('CardNumber', 'ExpiryDate', 'StartDate', 'CV2'));
    $result = Http::post($post_address, $post);
}

$rarr = explode("\r\n", $result);
$response = array();
foreach ($rarr as $v) {
    if (preg_match('/([^=]+?)=(.+)/', $v, $m)) {
        $response[$m[1]] = trim($m[2]);
    }
}

if ($response['Status'] == '3DAUTH') {

$payment_mode = $processor_data['processor_params']['testmode'];

$term_url = fn_payment_url('https', "sagepay_direct.php?order_id=" . $order_info['order_id'] . "&payment_mode=$payment_mode");

$post_data = array(
    'PaReq' => $response['PAReq'],
    'TermUrl' => $term_url,
    'MD' => $response['MD'],
);

fn_create_payment_form($response['ACSURL'], $post_data, '3D Secure');
exit;

} elseif ($response['Status'] == 'OK' || $response['Status'] == 'AUTHENTICATED'  || $response['Status'] == 'REGISTERED') {
    $pp_response['order_status'] = 'P';
    if (!empty($response['TxAuthNo'])) $pp_response['reason_text'] = 'AuthNo: '.@$response['TxAuthNo'];
    if (!empty($response['SecurityKey'])) {
        $pp_response['reason_text'] = 'SecurityKey: '.$response['SecurityKey'];
    } else {
        $pp_response['reason_text'] = '';
    }
} else {
    $pp_response['order_status'] = 'F';
    $pp_response['reason_text'] = '';
}

if (!empty($response['Status'])) {
    $pp_response['reason_text'] = 'Status: ' . @$response['StatusDetail'] . ' (' . $response['Status'] . ') ';
}
if (!empty($response['VPSTxId'])) {
    $pp_response['transaction_id'] = $response['VPSTxId'];
}
if (!empty($response['AVSCV2']) && $response['AVSCV2'] != 'DATA NOT CHECKED') {
    $pp_response['reason_text'] .= ' (AVS/CVV2: {' . $response['AVSCV2'] . '})  ';
}
if (!empty($response['AddressResult']) && $response['AddressResult'] != 'NOTPROVIDED') {
    $pp_response['reason_text'] .= ' (Address: {' . $response['AddressResult'] . '})  ';
}
if (!empty($response['PostCodeResult']) && $response['PostCodeResult'] != 'NOTPROVIDED') {
    $pp_response['reason_text'] .= ' (PostCode: {' . $response['PostCodeResult'] . '})  ';
}
if (!empty($response['CV2Result']) && $response['CV2Result'] != 'NOTPROVIDED') {
    $pp_response['reason_text'] .= ' (CV2: {' . $response['CV2Result'] . '})  ';
}
if (!empty($response['3DSecureStatus'])) {
    $pp_response['reason_text'] .= ' (3D Result: {' . $response['3DSecureStatus'] . '})  ';
}

if (!empty($secure_verified_3d) && !empty($order_id) && fn_check_payment_script('sagepay_direct.php', $order_id) == true) {
    unset($_SESSION['already_posted']);

    fn_finish_payment($order_id, $pp_response, false);
    fn_order_placement_routines('route', $order_id);
}
