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

$xml = '<?xml version="1.0" encoding="'. CHARSET .'"?>';

// Products management
if ($mode == 'get_products') {

    $_REQUEST['extend'] = empty($_REQUEST['type'])? array('description') : array($_REQUEST['type']);

    list($products) = fn_get_products($_REQUEST, Registry::get('settings.Appearance.products_per_page'));

    fn_gather_additional_products_data($products, array('get_icon' => true));

    $xml .= fn_array_to_xml($products, 'products');
}

//
// View product details
//
if ($mode == 'get_product') {

    $_REQUEST['product_id'] = empty($_REQUEST['product_id'])? 0 : $_REQUEST['product_id'];

    $product = fn_get_product_data($_REQUEST['product_id'], $auth, CART_LANGUAGE);
    if (!empty($product)) {
        if (!empty($_REQUEST['combination'])) {
            $product['combination'] = $combination;
        }

        fn_gather_additional_product_data($product, true, true);

        $xml .= fn_array_to_xml($product, 'product_data');
    }
}

if ($mode == 'get_categories') {

    $_REQUEST['category_id'] = empty($_REQUEST['category_id'])? 0 : $_REQUEST['category_id'];

    $params = array (
        'category_id' => $_REQUEST['category_id'],
        'visible' => false,
        'plain' => (!empty($_REQUEST['format']) && $_REQUEST['format'] == 'plain') ? true : false
    );
    list($categories, ) = fn_get_categories($params, CART_LANGUAGE);
    $xml .= fn_array_to_xml($categories, 'categories');
}

if ($mode == 'get_category') {

    $_REQUEST['category_id'] = empty($_REQUEST['category_id'])? 0 : $_REQUEST['category_id'];

    $category_data = fn_get_category_data($_REQUEST['category_id'], CART_LANGUAGE, '*');
    if (!empty($category_data)) {
        $xml .= fn_array_to_xml($category_data, 'category_data');
    }
}

echo $xml;
exit;
