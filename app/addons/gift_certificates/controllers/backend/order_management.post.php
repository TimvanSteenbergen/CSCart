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

$cart = & $_SESSION['cart'];
$_suffix = !empty($cart['order_id']) ? 'update' : 'add';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   return;
}

//
// Delete attached certificate
//
if ($mode == 'delete_use_certificate') {
    fn_delete_gift_certificate_in_use($_REQUEST['gift_cert_code'], $cart);

    return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");

//
// Display totals
//
} elseif ($mode == 'update' || $mode == 'add') {
    $gift_certificate_condition = (!empty($cart['use_gift_certificates'])) ? db_quote(" AND gift_cert_code NOT IN (?a)", array_keys($cart['use_gift_certificates'])) : '';
    Registry::get('view')->assign('gift_certificates',
        db_get_fields(
            "SELECT gift_cert_code FROM ?:gift_certificates WHERE status = 'A' ?p",
            $gift_certificate_condition . fn_get_gift_certificate_company_condition('?:gift_certificates.company_id')
        )
    );

//
// Delete certificate from the cart
//
} elseif ($mode == 'delete_certificate') {
    if (!empty($_REQUEST['gift_cert_cart_id'])) {
        fn_delete_cart_gift_certificate($cart, $_REQUEST['gift_cert_cart_id']);

        return array(CONTROLLER_STATUS_REDIRECT, "order_management.$_suffix");
    }
}
