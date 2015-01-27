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

if ($mode == 'view') {
    fn_add_breadcrumb(__('tags'));

    $tag = '';

    if (!empty($_REQUEST['tag'])) {
        if (Registry::get('addons.tags.tags_for_products') == 'Y') {
            $params = $_REQUEST;
            $params['extend'] = array('description');

            list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'));

            fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => true));

            $selected_layout = fn_get_products_layout($params);

            Registry::get('view')->assign('selected_layout', $selected_layout);
            Registry::get('view')->assign('products', $products);
            Registry::get('view')->assign('search', $search);
        }

        if (Registry::get('addons.tags.tags_for_pages') == 'Y') {
            $params = $_REQUEST;
            $params['status'] = (!empty($_REQUEST['see']) && $_REQUEST['see'] == 'my') ? array('A', 'H') : array('A');

            list($pages, $params) = fn_get_pages($params);
            Registry::get('view')->assign('pages', $pages);
        }

        $tag = $_REQUEST['tag'];
    }

    if (empty($_REQUEST['see']) || $_REQUEST['see'] != 'my') {
        $title = __('items_marked_by_tag', array(
            '[tag]' => $tag
        ));
    } else {
        $title = __('my_items_marked_by_tag', array(
            '[tag]' => $tag
        ));
    }

    Registry::get('view')->assign('page_title', $title);
    fn_add_breadcrumb($title);

    if (!empty($products) || !empty($pages)) {
        Registry::get('view')->assign('tag_objects_exist', true);
    }

// summary mode: tag - product list, tag - product list
} elseif ($mode == 'summary') {
    fn_add_breadcrumb(__('tags'));
    if (!empty($auth['user_id'])) {
        list($user_tags) = fn_get_tags(array('user_id' => $auth['user_id']));
        foreach ($user_tags as &$tag) {
            $tag['total'] = 0;
            if (Registry::get('addons.tags.tags_for_products') == 'Y') {
                $product_ids = db_get_fields(
                    "SELECT object_id FROM ?:tag_links WHERE object_type = ?s AND user_id = ?i AND tag_id = ?i",
                    'P', $auth['user_id'], $tag['tag_id']
                );
                $tag['products'] = fn_get_product_name($product_ids);
                $tag['total'] += count($product_ids);
            }

            if (Registry::get('addons.tags.tags_for_products') == 'Y') {
                $page_ids = db_get_fields(
                    "SELECT object_id FROM ?:tag_links WHERE object_type = ?s AND user_id = ?i AND tag_id = ?i",
                    'A', $auth['user_id'], $tag['tag_id']
                );
                $tag['pages'] = fn_get_page_name($page_ids);
                $tag['total'] += count($page_ids);
            }
        }

        Registry::get('view')->assign('tags_summary', $user_tags);
    }

// ajax autocomplete mode
} elseif ($mode == 'list') {
    if (defined('AJAX_REQUEST')) {
        $tags = fn_get_tag_names(array('tag' => $_REQUEST['q']));
        Registry::get('ajax')->assign('autocomplete', $tags);

        exit();
    }

} elseif ($mode == 'update'  && !empty($auth['user_id'])) {
    if (defined('AJAX_REQUEST')) {
        $params = $_REQUEST;
        $params['user_id'] = $auth['user_id'];
        fn_update_tag($params);

        Registry::get('ajax')->assign('tag_name', fn_get_tag_names($params));
        exit();
    }

} elseif ($mode == 'delete' && !empty($auth['user_id'])) {
    $params = $_REQUEST;
    $params['user_id'] = $auth['user_id'];

    if (!empty($params['tag']) || !empty($params['tag_id'])) {
        fn_delete_tags_by_params($params);
    }

    if (defined('AJAX_REQUEST')) {
        Registry::get('ajax')->assign('tag_name', fn_get_tag_names($params));
        exit();
    }
}
