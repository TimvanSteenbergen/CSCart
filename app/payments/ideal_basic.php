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

    if ($mode == 'result') {
        if (fn_check_payment_script('ideal_basic.php', $_REQUEST['order_id'])) {
            $order_info = fn_get_order_info($_REQUEST['order_id'], true);
            if ($order_info['status'] == 'N') {
                fn_change_order_status($_REQUEST['order_id'], 'O', '', false);
            }
        }
        fn_order_placement_routines('route', $_REQUEST['order_id']);

    } elseif ($mode == 'cancel') {
        if (fn_check_payment_script('ideal_basic.php', $_REQUEST['order_id'])) {
            $pp_response = array();
            $pp_response['order_status'] = 'N';
            $pp_response['reason_text']  = __('text_transaction_cancelled');
            fn_finish_payment($_REQUEST['order_id'], $pp_response);
        }
        fn_order_placement_routines('route', $_REQUEST['order_id'], false);

    } else {

        $xml_response = !isset($GLOBALS['HTTP_RAW_POST_DATA']) ? file_get_contents("php://input") : $GLOBALS['HTTP_RAW_POST_DATA'];

        if (!empty($xml_response)) {
            preg_match("/<transactionID>(.*)<\/transactionID>/", $xml_response, $transaction);
            preg_match("/<purchaseID>(.*)<\/purchaseID>/", $xml_response, $purchase);
            preg_match("/<status>(.*)<\/status>/", $xml_response, $status);
            preg_match("/<createDateTimeStamp>(.*)<\/createDateTimeStamp>/", $xml_response, $date);

            $order_id = (strpos($purchase[1], '_')) ? substr($purchase[1], 0, strpos($purchase[1], '_')) : $purchase[1];
            $pp_response = array();

            if ($status[1] == 'Success') {
                $pp_response['order_status'] = 'P';

            } elseif ($status[1] == 'Open') {
                $pp_response['order_status'] = 'O';

            } elseif ($status[1] == 'Cancelled') {
                $pp_response['order_status'] = 'I';

            } else {
                $pp_response['order_status'] = 'F';
            }

            $pp_response['reason_text'] = "Status code: " . $status[1];

            $dat = $date[1];
            $time = $dat[0] . $dat[1] . $dat[2] . $dat[3] . '-' . $dat[4] . $dat[5] . '-' . $dat[6] . $dat[7] . ' ' . $dat[8] . $dat[9] . ':' . $dat[10] . $dat[11] . ':' . $dat[12] . $dat[13];

            $pp_response['reason_text'].= " (TimeStamp: ".$time.")";

            $pp_response['transaction_id'] = $transaction[1];
            if (fn_check_payment_script('ideal_basic.php', $order_id)) {
                fn_finish_payment($order_id, $pp_response); // Force customer notification
            }
        }
    }

} else {

    $langs = array(
        "US" => "en_US",
        "FR" => "fr_FR",
        "NL" => "nl_NL",
        "IT" => "it_IT",
        "DE" => "de_DE",
        "ES" => "es_ES",
        "NO" => "no_NO",
        "en" => "en_EN"
    );


$validUntil = date("Y-m-d\TH:i:s", time() + 3600 + date('Z'));
$validUntil = $validUntil . ".000Z";
$pp_merch = $processor_data['processor_params']['merchant_id'];
$pp_secret = $processor_data['processor_params']['merchant_key'];
$pp_curr = $processor_data['processor_params']['currency'];
$pp_test = ($processor_data['processor_params']['test'] == 'TRUE') ? "https://idealtest.secure-ing.com/ideal/mpiPayInitIng.do" : "https://ideal.secure-ing.com/ideal/mpiPayInitIng.do";
$pp_lang = $processor_data['processor_params']['language'];
$order_total = $order_info['total'] * 100;
$_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;

/*$shastring = "$key" . "$merchantID" . "$subID" . "$amount" . "$orderNumber" .
"$paymentType" . "$validUntil" .
"$itemNumber1" . "$itemDescription1" . $product1number . $product1price .
"$itemNumber2" . "$itemDescription2" . $product2number . $product2price .
"$itemNumber3" . "$itemDescription3" . $product3number . $product3price .
"$itemNumber4" . "$itemDescription4" . $product4number . $product4price;

concatString = merchantKey + merchantID + subID + amount + purchaseID + paymentType + validUntil + itemNumber1 + itemDescription1 + itemQuantity1
+ itemPrice1 (+ itemNumber2 + itemDescription2 + itemQuantity2 + itemPrice2 + itemNumber3 + item...)*/
$pre_sha = '';
$total = 0;
// Products
if (!empty($order_info['products'])) {
    foreach ($order_info['products'] as $k => $v) {
        $_name = str_replace('"', "", str_replace("'", "", $v['product']));
        $pre_sha = $pre_sha . $v['product_id'] . $_name . $v['amount'] . (fn_format_price(($v['subtotal'] - fn_external_discounts($v)) / $v['amount']) * 100);
        $total = $total + (fn_format_price($v['subtotal'] - fn_external_discounts($v)) * 100);
    }
}
// Gift Certificates
if (!empty($order_info['gift_certificates'])) {
    foreach ($order_info['gift_certificates'] as $k => $v) {
        $v['amount'] = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : $v['amount'];
        $pre_sha = $pre_sha . $v['gift_cert_id'] . $v['gift_cert_code'] . '1' . ($v['amount'] * 100);
        $total = $total + $v['amount'] * 100;
    }
}
// Discounts
$discount = $order_info['subtotal_discount'];
if ($discount > 0) {
    $pre_sha = $pre_sha . strtolower(__('discount')) . __('discount') . '1' . ($discount * 100 * -1);
}

if (!empty($order_info['use_gift_certificates'])) {
    foreach ($order_info['use_gift_certificates'] as $gc_code => $gc_data) {
        $pre_sha .= $gc_data['gift_cert_id'] . $gc_code . '1' . ($gc_data['amount'] * 100 * -1);
    }
}

// Taxes
if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') != 'unit_price') {
    foreach ($order_info['taxes'] as $tax_id => $tax_data) {
        if ($tax_data['price_includes_tax'] == 'N') {
            $pre_sha .= $tax_id . __('tax') . '1' . ($tax_data['tax_subtotal'] * 100);
        }
    }
}

// Shipping
$shipping=$order_info['shipping_cost'];
if ($shipping > 0) {
    $pre_sha = $pre_sha . "SH" . "Shipping" . "1" . ($shipping * 100);
}

$shastring = "$pp_secret"."$pp_merch"."0"."$order_total"."$_order_id"."ideal"."$validUntil".$pre_sha;

$shastring = str_replace(" ", "", $shastring);
$shastring = str_replace("\t", "", $shastring);
$shastring = str_replace("\n", "", $shastring);
$shastring = str_replace("&amp;", "&", $shastring);
$shastring = str_replace("&lt;", "<", $shastring);
$shastring = str_replace("&gt;", ">", $shastring);
$shastring = str_replace("&quot;", "\"", $shastring);

$shasign = sha1($shastring);

$counter = 1;

$return_url = fn_url("payment_notification.result?payment=ideal_basic&order_id=$order_id", AREA, 'current');
$cancel_url = fn_url("payment_notification.cancel?payment=ideal_basic&order_id=$order_id", AREA, 'current');
$post_data = array(
    'merchantID' => $pp_merch,
    'subID' => '0',
    'amount' => $order_total,
    'purchaseID' => $_order_id,
    'language' => $pp_lang,
    'currency' => 'EUR',
    'description' => 'iDEAL Basic purchase',
    'hash' => $shasign,
    'paymentType' => 'ideal',
    'validUntil' => $validUntil,
    'urlCancel' => $cancel_url,
    'urlSuccess' => $return_url,
    'urlError' => $return_url
);

// Products
if (!empty($order_info['products'])) {
    foreach ($order_info['products'] as $k => $v) {
        $pr = fn_format_price(($v['subtotal'] - fn_external_discounts($v))/$v['amount']) * 100;
        $_name = str_replace('"', "", str_replace("'", "", $v['product']));

        $post_data['itemNumber' . $counter] = $v['product_id'];
        $post_data['itemDescription' . $counter] = $_name;
        $post_data['itemQuantity' . $counter] = $v['amount'];
        $post_data['itemPrice' . $counter] = $pr;
        $counter++;
    }
}
// Gift Cartificates
if (!empty($order_info['gift_certificates'])) {
    foreach ($order_info['gift_certificates'] as $k => $v) {
        $pr = (!empty($v['extra']['exclude_from_calculate'])) ? 0 : ($v['amount'] * 100);

        $post_data['itemNumber' . $counter] = $v['gift_cert_id'];
        $post_data['itemDescription' . $counter] = $v['gift_cert_code'];
        $post_data['itemQuantity' . $counter] = 1;
        $post_data['itemPrice' . $counter] = $pr;
        $counter++;
    }
}

// Discount
$discount=$order_info['subtotal_discount'];
if ($discount > 0) {
    $discounts = $order_info['subtotal_discount'] * 100 * (-1);
    $msg = __('discount');

    $post_data['itemNumber' . $counter] = $msg;
    $post_data['itemDescription' . $counter] = $msg;
    $post_data['itemQuantity' . $counter] = 1;
    $post_data['itemPrice' . $counter] = $discounts;    
    $counter++;
}

if (!empty($order_info['use_gift_certificates'])) {
    foreach ($order_info['use_gift_certificates'] as $gc_code => $gc_data) {
        $amount = fn_format_price($gc_data['amount']) * 100 * (-1);

        $post_data['itemNumber' . $counter] = $gc_data['gift_cert_id'];
        $post_data['itemDescription' . $counter] = $gc_code;
        $post_data['itemQuantity' . $counter] = 1;
        $post_data['itemPrice' . $counter] = $amount;
        $counter++;
    }
}

// Taxes
if (!empty($order_info['taxes']) && Registry::get('settings.General.tax_calculation') != 'unit_price') {
    $msg = __('tax');
    foreach ($order_info['taxes'] as $tax_id => $tax_data) {
        if ($tax_data['price_includes_tax'] == 'N') {
            $amount = fn_format_price($tax_data['tax_subtotal']) * 100;

            $post_data['itemNumber' . $counter] = $tax_id;
            $post_data['itemDescription' . $counter] = $msg;
            $post_data['itemQuantity' . $counter] = 1;
            $post_data['itemPrice' . $counter] = $amount;    
            $counter++;
        }
    }
}

// Shipping
$shipping=$order_info['shipping_cost'];
if ($shipping > 0) {
    $ship = $order_info['shipping_cost'] * 100;

    $post_data['itemNumber' . $counter] = 'SH';
    $post_data['itemDescription' . $counter] = 'Shipping';
    $post_data['itemQuantity' . $counter] = 1;
    $post_data['itemPrice' . $counter] = $ship;    
}

fn_create_payment_form($pp_test, $post_data, 'iDeal', false);

exit;
}
