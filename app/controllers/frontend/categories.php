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

$_REQUEST['category_id'] = empty($_REQUEST['category_id']) ? 0 : $_REQUEST['category_id'];

if ($mode == 'catalog') {
    fn_add_breadcrumb(__('catalog'));

    $root_categories = fn_get_subcategories(0);

    foreach ($root_categories as $k => $v) {
        $root_categories[$k]['main_pair'] = fn_get_image_pairs($v['category_id'], 'category', 'M');
    }

    Registry::get('view')->assign('root_categories', $root_categories);

} elseif ($mode == 'view') {

    $_statuses = array('A', 'H');
    $_condition = fn_get_localizations_condition('localization', true);
    $preview = fn_is_preview_action($auth, $_REQUEST);

    if (!$preview) {
        $_condition .= ' AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'usergroup_ids', true) . ')';
        $_condition .= db_quote(' AND status IN (?a)', $_statuses);
    }

    if (fn_allowed_for('ULTIMATE')) {
        $_condition .= fn_get_company_condition('?:categories.company_id');
    }

    $category_exists = db_get_field(
        "SELECT category_id FROM ?:categories WHERE category_id = ?i ?p",
        $_REQUEST['category_id'],
        $_condition
    );

    if (!empty($category_exists)) {

        if (!empty($_REQUEST['features_hash'])) {
            $_REQUEST['features_hash'] = fn_correct_features_hash($_REQUEST['features_hash']);
        }

        // Save current url to session for 'Continue shopping' button
        $_SESSION['continue_url'] = "categories.view?category_id=$_REQUEST[category_id]";

        // Save current category id to session
        $_SESSION['current_category_id'] = $_SESSION['breadcrumb_category_id'] = $_REQUEST['category_id'];

        // Get subcategories list for current category
        Registry::get('view')->assign('subcategories', fn_get_subcategories($_REQUEST['category_id']));

        // Get full data for current category
        $category_data = fn_get_category_data($_REQUEST['category_id'], CART_LANGUAGE, '*', true, false, $preview);

        $category_parent_ids = fn_explode('/', $category_data['id_path']);
        array_pop($category_parent_ids);

        if (!empty($category_data['meta_description']) || !empty($category_data['meta_keywords'])) {
            Registry::get('view')->assign('meta_description', $category_data['meta_description']);
            Registry::get('view')->assign('meta_keywords', $category_data['meta_keywords']);
        }

        $params = $_REQUEST;

        if (!empty($_REQUEST['items_per_page'])) {
            $_SESSION['items_per_page'] = $_REQUEST['items_per_page'];
        } elseif (!empty($_SESSION['items_per_page'])) {
            $params['items_per_page'] = $_SESSION['items_per_page'];
        }

        $params['cid'] = $_REQUEST['category_id'];
        $params['extend'] = array('categories', 'description');
        $params['subcats'] = '';
        if (Registry::get('settings.General.show_products_from_subcategories') == 'Y') {
            $params['subcats'] = 'Y';
        }

        list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'), CART_LANGUAGE);

        if (isset($search['page']) && ($search['page'] > 1) && empty($products)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        fn_gather_additional_products_data($products, array(
            'get_icon' => true,
            'get_detailed' => true,
            'get_additional' => true,
            'get_options' => true,
            'get_discounts' => true,
            'get_features' => false
        ));

        $show_no_products_block = (!empty($params['features_hash']) && !$products);
        Registry::get('view')->assign('show_no_products_block', $show_no_products_block);

        $selected_layout = fn_get_products_layout($_REQUEST);
        Registry::get('view')->assign('show_qty', true);
        Registry::get('view')->assign('products', $products);
        Registry::get('view')->assign('search', $search);
        Registry::get('view')->assign('selected_layout', $selected_layout);

        Registry::get('view')->assign('category_data', $category_data);

        // If page title for this category is exist than assign it to template
        if (!empty($category_data['page_title'])) {
             Registry::get('view')->assign('page_title', $category_data['page_title']);
        }

        fn_define('FILTER_CUSTOM_ADVANCED', true); // this constant means that extended filtering should be stayed on the same page

        list($filters) = fn_get_filters_products_count($_REQUEST);
        Registry::get('view')->assign('filter_features', $filters);

        // [Breadcrumbs]
        if (!empty($category_parent_ids)) {
            Registry::set('runtime.active_category_ids', $category_parent_ids);
            $cats = fn_get_category_name($category_parent_ids);
            foreach ($category_parent_ids as $c_id) {
                fn_add_breadcrumb($cats[$c_id], "categories.view?category_id=$c_id");
            }
        }

        fn_add_breadcrumb($category_data['category'], (empty($_REQUEST['features_hash']) && empty($_REQUEST['advanced_filter'])) ? '' : "categories.view?category_id=$_REQUEST[category_id]");

        if (!empty($params['features_hash'])) {
            fn_add_filter_ranges_breadcrumbs($params, "categories.view?category_id=$_REQUEST[category_id]");
        } elseif (!empty($_REQUEST['advanced_filter'])) {
            fn_add_breadcrumb(__('advanced_filter'));
        }
        // [/Breadcrumbs]
    } else {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

} elseif ($mode == 'picker') {

    $category_count = db_get_field("SELECT COUNT(*) FROM ?:categories");
    if ($category_count < CATEGORY_THRESHOLD) {
        $params = array (
            'simple' => false
        );
         list($categories_tree, ) = fn_get_categories($params);
         Registry::get('view')->assign('show_all', true);
    } else {
        $params = array (
            'category_id' => $_REQUEST['category_id'],
            'current_category_id' => $_REQUEST['category_id'],
            'visible' => true,
            'simple' => false
        );
        list($categories_tree, ) = fn_get_categories($params);
    }

    if (!empty($_REQUEST['root'])) {
        array_unshift($categories_tree, array('category_id' => 0, 'category' => $_REQUEST['root']));
    }
    Registry::get('view')->assign('categories_tree', $categories_tree);
    if ($category_count < CATEGORY_SHOW_ALL) {
        Registry::get('view')->assign('expand_all', true);
    }
    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->assign('category_id', $_REQUEST['category_id']);
    }
    Registry::get('view')->display('pickers/categories/picker_contents.tpl');
    exit;
}
