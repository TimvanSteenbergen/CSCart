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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    return;
}

if ($mode == 'view' && Registry::get('addons.tags.tags_for_products') == 'Y') {
    $product = Registry::get('view')->getTemplateVars('product');
    $product['tags']['popular'] = $product['tags']['user'] = array();
    list($tags) = fn_get_tags(array('object_type' => 'P', 'object_id' => $product['product_id'], 'user_and_popular' => $auth['user_id']));

    foreach ($tags as $k => $v) {
        if (!empty($v['my_tag'])) {
            $product['tags']['user'][$v['tag_id']] = $v;
        }
        if ($v['status'] == 'A') {
            $product['tags']['popular'][$v['tag_id']] = $v;
        }
    }
    Registry::get('view')->assign('product', $product);
}
