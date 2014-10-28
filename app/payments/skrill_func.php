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

function fn_mb_adjust_amount($price, $payment_currency)
{
    $currencies = Registry::get('currencies');

    if (array_key_exists($payment_currency, $currencies)) {
        if ($currencies[$payment_currency]['is_primary'] != 'Y') {
            $price = fn_format_price($price / $currencies[$payment_currency]['coefficient']);
        }
    } else {
        return false;
    }

    return $price;
}

function fn_mb_place_order($data)
{
    define('SKIP_SESSION_VALIDATION', true);
    $order_id = 0;
    $mb_sess_id = base64_decode($data['mb_sess_id']);

    if (!empty($mb_sess_id)) {
        Session::resetId($mb_sess_id);
        $cart = & $_SESSION['cart'];
        $auth = & $_SESSION['auth'];

        list($order_id, $process_payment) = fn_place_order($cart, $auth);

        if (!empty($_REQUEST['order_id'])) {
            $data = array (
                'order_id' => $order_id,
                'type' => 'S',
                'data' => TIME,
            );
            db_query('REPLACE INTO ?:order_data ?e', $data);

            $data = array (
                'order_id' => $order_id,
                'type' => 'E', // extra order ID
                'data' => $_REQUEST['inner_order_id'],
            );
            db_query('REPLACE INTO ?:order_data ?e', $data);
        }
    }

    return $order_id;
}
