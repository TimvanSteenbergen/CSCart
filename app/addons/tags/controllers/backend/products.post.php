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

//
// View product details
//
if ($mode == 'add' && Registry::get('addons.tags.tags_for_products') == 'Y') {
    if (Registry::get('runtime.company_id') && fn_allowed_for('ULTIMATE') || fn_allowed_for('MULTIVENDOR')) {
        Registry::set('navigation.tabs.tags', array(
            'title' => __('tags'),
            'js' => true
        ));
    }

} elseif ($mode == 'update' && Registry::get('addons.tags.tags_for_products') == 'Y') {
    if (Registry::get('runtime.company_id') && fn_allowed_for('ULTIMATE') || fn_allowed_for('MULTIVENDOR')) {
        Registry::set('navigation.tabs.tags', array(
            'title' => __('tags'),
            'js' => true
        ));
    }

    $product = Registry::get('view')->getTemplateVars('product_data');

    $product['tags']['popular'] = $product['tags']['user'] = array();
    list($tags) = fn_get_tags(array('object_type' => 'P', 'object_id' => $product['product_id'], 'user_and_popular' => $auth['user_id'], 'skip_view' => 'Y'));

    foreach ($tags as $k => $v) {
        if (!empty($v['my_tag'])) {
            $product['tags']['user'][$v['tag_id']] = $v;
        }
        if ($v['status'] == 'A') {
            $product['tags']['popular'][$v['tag_id']] = $v;
        }
    }

    Registry::get('view')->assign('product_data', $product);
}
