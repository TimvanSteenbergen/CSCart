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

use Tygh\BlockManager\Block;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'view') {
    $lang_code = empty($_REQUEST['lang']) ? CART_LANGUAGE : $_REQUEST['lang'];
    list($items_data, $additional_data) = fn_rssf_get_items($_REQUEST, $lang_code);

    header('Content-Type: text/xml; charset=utf-8');
    fn_echo(fn_generate_rss($items_data, $additional_data));
    exit;

} elseif ($mode == 'add_to_cart') {
    if (empty($auth['user_id']) && Registry::get('settings.General.allow_anonymous_shopping') != 'allow_shopping') {
        return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=" . urlencode($_SERVER['HTTP_REFERER']));
    }

    $cart = & $_SESSION['cart'];
    $lang_code = empty($_REQUEST['lang']) ? CART_LANGUAGE : $_REQUEST['lang'];

    $product_data = array (
        $_REQUEST['product_id'] => array (
            'product_id' => $_REQUEST['product_id'],
            'amount' => 1
        )
    );

    fn_add_product_to_cart($product_data, $cart, $auth);
    fn_save_cart_content($cart, $auth['user_id']);
    fn_calculate_cart_content($cart, $auth, 'S', true, 'F', true);

    return array(CONTROLLER_STATUS_OK, fn_url('checkout.cart?sl=' . $lang_code, 'C', 'http', $lang_code, true));
}

function fn_rssf_get_items($params, $lang_code = CART_LANGUAGE)
{
    $items_data = $additional_data = $block_data = array();

    if (!empty($params['bid']) && !empty($params['sid']) && empty($params['category_id'])) {
        $block_data = Block::instance()->getById($params['bid'], $params['sid'], array(), $lang_code);

        if (!empty($block_data['content']['filling']) && $block_data['content']['filling'] == 'products') {

            $_params = array (
                'sort_by' => ($block_data['properties']['filling']['products']['rss_sort_by'] == 'U') ? 'updated_timestamp' : 'timestamp',
                'sort_order' => 'desc'
            );

            $max_items = !empty($block_data['properties']['max_item']) ? $block_data['properties']['max_item'] : 5;

            list($products) = fn_get_products($_params, $max_items, $lang_code);
            fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => false, 'get_discounts' => false));

            $additional_data['title'] = !empty($block_data['properties']['feed_title']) ? $block_data['properties']['feed_title'] : __('products') . '::' . __('page_title', '', $lang_code);
            $additional_data['description'] = !empty($block_data['properties']['feed_description']) ? $block_data['properties']['feed_description'] : $additional_data['title'];
            $additional_data['link'] = fn_url('', 'C', 'http', $lang_code);
            $additional_data['language'] = $lang_code;
            $additional_data['lastBuildDate'] = !empty($products[0]['updated_timestamp']) ? $products[0]['updated_timestamp'] : 0;

            $items_data = fn_format_products_items($products, $block_data['properties']['filling']['products'], $lang_code);
        }

    } else {
        //show rss feed for categories page
        list($items_data, $additional_data) = fn_format_categories_items($params, $lang_code);
    }

    fn_set_hook('generate_rss_feed', $items_data, $additional_data, $block_data, $lang_code);

    return array($items_data, $additional_data);
}
