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

function fn_catalog_mode_enabled()
{
    return Registry::get('addons.catalog_mode.main_store_mode') == 'catalog' ?  'Y' : 'N';
}

function fn_catalog_mode_may_disable_minicart()
{
    if (Registry::get('addons.catalog_mode.main_store_mode') == 'store' || Registry::get('addons.catalog_mode.add_to_cart_empty_buy_now_url') == 'Y') {
        return 'N';
    }

    return 'Y';
}

function fn_is_add_to_cart_allowed($product_id)
{
    // No need to involve heavy SQL requests performed by fn_get_products()
    if (Registry::get('addons.catalog_mode.add_to_cart_empty_buy_now_url') == 'Y' && db_get_field("SELECT buy_now_url FROM ?:products WHERE product_id = ?i", $product_id) == '') {
        return true;
    }

    return false;
}

function fn_catalog_mode_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    if (AREA == 'C') {
        // Firebug protection
        foreach ($product_data as $key => &$product) {
            $product_id = (!empty($product['product_id'])) ? $product['product_id'] : $key;

            if (fn_catalog_mode_enabled() == 'Y' && !fn_is_add_to_cart_allowed($product_id)) {
                $product = array();
            }
        }
    }
}

function fn_catalog_mode_update_product_post(&$product_data, &$product_id, &$lang_code, &$create)
{
    if (!empty($product_data['buy_now_url']) && !floatval($product_data['price']) && !empty($product_data['zero_price_action']) && $product_data['zero_price_action'] == 'R') {
        fn_set_notification('N', __('notice'), __('text_catalog_mode_zero_price_action_notice'));
    }
}
