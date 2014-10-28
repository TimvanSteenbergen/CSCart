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
$customer_auth = & $_SESSION['customer_auth'];
$_suffix = !empty($cart['order_id']) ? 'update' : 'add';

if (!empty($customer_auth['user_id']) && !isset($customer_auth['points'])) {
    $customer_auth['points'] = db_get_field("SELECT data FROM ?:user_data WHERE type='W' AND user_id = ?i", $customer_auth['user_id']);
    Registry::get('view')->assign('customer_auth', $customer_auth);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

//
// Display totals
//
if ($mode == 'update' || $mode == 'add') {

    $prev_points = !empty($cart['previous_points_info']['in_use']['points']) ? $cart['previous_points_info']['in_use']['points'] : 0;
    $user_points = fn_get_user_additional_data(POINTS, $customer_auth['user_id']) + $prev_points;

    Registry::get('view')->assign('user_points', $user_points);

//
// Delete point in use from the cart
//
} elseif ($mode == 'delete_points_in_use') {
    unset($cart['points_info']['in_use']);

    return array(CONTROLLER_STATUS_REDIRECT, "order_management." . $_suffix);
}
