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

if ($mode == 'cart_list') {

    $item_types = fn_get_cart_content_item_types();

    if (empty($_REQUEST['user_id'])) {

        $carts_list = Registry::get('view')->getTemplateVars('carts_list');

        if (!empty($carts_list)) {
            $all_wishlist_products = array();
            if (fn_allowed_for('ULTIMATE')) {
                foreach ($carts_list as $key => $cart_data) {
                    $all_wishlist_products[$key] = db_get_array(
                        "SELECT COUNT(item_id) as count"
                        . " FROM ?:user_session_products"
                        . " WHERE user_id = ?i AND company_id = ?i AND type = 'W'"
                        . " GROUP BY user_id, company_id",
                        $cart_data['user_id'], $cart_data['company_id']
                    );
                    $carts_list[$key]['wishlist_products'] = !empty($all_wishlist_products[$key][0]['count']) ? $all_wishlist_products[$key][0]['count'] : 0;
                    $carts_list[$key]['user_data'] = empty($carts_list[$key]['user_data']) ? fn_get_user_info($cart_data['user_id'], true) : $carts_list[$key]['user_data'];
                }
            } else {
                foreach ($carts_list as $key => $cart_data) {
                    $all_wishlist_products[$key] = db_get_array(
                        "SELECT COUNT(item_id) as count"
                        . " FROM ?:user_session_products"
                        . " WHERE user_id = ?i AND type = 'W'"
                        . " GROUP BY user_id",
                        $cart_data['user_id']
                    );
                    $carts_list[$key]['wishlist_products'] = !empty($all_wishlist_products[$key][0]['count']) ? $all_wishlist_products[$key][0]['count'] : 0;
                    $carts_list[$key]['user_data'] = empty($carts_list[$key]['user_data']) ? fn_get_user_info($cart_data['user_id'], true) : $carts_list[$key]['user_data'];
                }
            }
        }

        Registry::get('view')->assign('carts_list', $carts_list);

    } else {
        if (fn_allowed_for('ULTIMATE') && !empty($_REQUEST['c_company_id'])) {
            $products = db_get_array(
                "SELECT ?:user_session_products.item_id, ?:user_session_products.item_type, ?:user_session_products.product_id, ?:user_session_products.amount, ?:user_session_products.price, ?:user_session_products.extra, ?:product_descriptions.product"
                . " FROM ?:user_session_products"
                . " LEFT JOIN ?:product_descriptions ON ?:user_session_products.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s"
                . " WHERE ?:user_session_products.user_id = ?i AND ?:user_session_products.company_id = ?i AND ?:user_session_products.type = 'W' AND ?:user_session_products.item_type IN (?a)",
                DESCR_SL, $_REQUEST['user_id'], $_REQUEST['c_company_id'], $item_types
            );
        } else {
            $products = db_get_array(
                "SELECT ?:user_session_products.item_id, ?:user_session_products.item_type, ?:user_session_products.product_id, ?:user_session_products.amount, ?:user_session_products.price, ?:user_session_products.extra, ?:product_descriptions.product"
                . " FROM ?:user_session_products"
                . " LEFT JOIN ?:product_descriptions ON ?:user_session_products.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s"
                . " WHERE ?:user_session_products.user_id = ?i AND ?:user_session_products.type = 'W' AND ?:user_session_products.item_type IN (?a)",
                DESCR_SL, $_REQUEST['user_id'], $item_types
            );
        }

        if (!empty($products)) {
            foreach ($products as $key => $product) {
                $products[$key]['extra'] = unserialize($product['extra']);
            }
        }

        Registry::get('view')->assign('wishlist_products', $products);
    }
}
