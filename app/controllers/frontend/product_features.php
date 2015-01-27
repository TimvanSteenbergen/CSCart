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

$_REQUEST['variant_id'] = empty($_REQUEST['variant_id']) ? 0 : $_REQUEST['variant_id'];

if (empty($action)) {
    $action = 'show_all';
}

$list = 'features';

if (empty($_SESSION['excluded_features'])) {
    $_SESSION['excluded_features'] = array();
}

if (empty($_SESSION['excluded_features'])) {
    $_SESSION['excluded_features'] = array();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Add feature to comparison list
    if ($mode == 'add_feature') {
        if (!empty($_REQUEST['add_features'])) {
            $_SESSION['excluded_features'] = array_diff($_SESSION['excluded_features'], $_REQUEST['add_features']);
        }
    }

    return array(CONTROLLER_STATUS_OK);
}

// Add product to comparison list
if ($mode == 'add_product') {
    if (empty($_SESSION['comparison_list'])) {
        $_SESSION['comparison_list'] = array();
    }

    $p_id = $_REQUEST['product_id'];

    if (!in_array($p_id, $_SESSION['comparison_list'])) {
        array_unshift($_SESSION['comparison_list'], $p_id);
        $added_products = array();
        $added_products[$p_id]['product_id'] = $p_id;
        $added_products[$p_id]['display_price'] = fn_get_product_price($p_id, 1, $_SESSION['auth']);
        $added_products[$p_id]['amount'] = 1;
        $added_products[$p_id]['main_pair'] = fn_get_cart_product_icon($p_id);
        Registry::get('view')->assign('added_products', $added_products);

        $title = __('product_added_to_cl');
        $msg = Registry::get('view')->fetch('views/product_features/components/product_notification.tpl');
        fn_set_notification('I', $title, $msg, 'I');
    } else {
        fn_set_notification('W', __('notice'), __('product_in_compare_list'));   
    }

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'clear_list') {
    unset($_SESSION['comparison_list']);
    unset($_SESSION['excluded_features']);

    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->assign('compared_products', array());
        Registry::get('view')->display('blocks/static_templates/feature_comparison.tpl');
        exit;
    }

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'delete_product' && !empty($_REQUEST['product_id'])) {
    $key = array_search ($_REQUEST['product_id'], $_SESSION['comparison_list']);
    unset($_SESSION['comparison_list'][$key]);

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'delete_feature') {
    $_SESSION['excluded_features'][] = $_REQUEST['feature_id'];

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'compare') {
    fn_add_breadcrumb(__('feature_comparison'));
    if (!empty($_SESSION['comparison_list'])) {
        Registry::get('view')->assign('comparison_data', fn_get_product_data_for_compare($_SESSION['comparison_list'], $action));
        Registry::get('view')->assign('total_products', count($_SESSION['comparison_list']));
    }
    Registry::get('view')->assign('list', $list);
    Registry::get('view')->assign('action', $action);

    if (!empty($_SESSION['continue_url'])) {
        Registry::get('view')->assign('continue_url', $_SESSION['continue_url']);
    }
}

if ($mode == 'view_all') {

    if (!empty($_REQUEST['q'])) {
        parse_str(substr($_REQUEST['q'], strpos($_REQUEST['q'], '?') + 1), $params);
    } else {
        $params = $_REQUEST;
    }

    if (empty($params['filter_id'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $params['view_all'] = 'Y';
    $params['get_custom'] = 'Y';

    if (!empty($params['category_id'])) {
        $parent_ids = explode('/', db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $params['category_id']));

        if (!empty($parent_ids)) {
            $cats = fn_get_category_name($parent_ids);
            foreach ($cats as $c_id => $c_name) {
                fn_add_breadcrumb($c_name, "categories.view?category_id=$c_id");
            }
        }
    }

    list( , $view_all_filter) = fn_get_filters_products_count($params);

    fn_add_breadcrumb(db_get_field("SELECT filter FROM ?:product_filter_descriptions WHERE filter_id = ?i AND lang_code = ?s", $params['filter_id'], CART_LANGUAGE));

    Registry::get('view')->assign('params', $params);
    Registry::get('view')->assign('view_all_filter', $view_all_filter);

} elseif ($mode == 'view') {

    $variant_data = fn_get_product_feature_variant($_REQUEST['variant_id']);
    Registry::get('view')->assign('variant_data', $variant_data);

    if (!empty($_REQUEST['features_hash']) || !empty($_REQUEST['advanced_filter'])) {
        fn_add_breadcrumb($variant_data['variant'], "product_features.view?variant_id=$_REQUEST[variant_id]");
        fn_add_filter_ranges_breadcrumbs($_REQUEST, "product_features.view?variant_id=$_REQUEST[variant_id]");
    } else {
        fn_add_breadcrumb($variant_data['variant']);
    }

    // Override meta description/keywords
    if (!empty($variant_data['meta_description']) || !empty($variant_data['meta_keywords'])) {
        Registry::get('view')->assign('meta_description', $variant_data['meta_description']);
        Registry::get('view')->assign('meta_keywords', $variant_data['meta_keywords']);
    }

    // Override page title
    if (!empty($variant_data['page_title'])) {
        Registry::get('view')->assign('page_title', $variant_data['page_title']);
    }

    fn_define('FILTER_CUSTOM_ADVANCED', true); // this constant means that extended filtering should be stayed on the same page

    $params = $_REQUEST;
    $params['features_hash'] = (!empty($params['features_hash']) ? ($params['features_hash'] . '.') : '') . 'V' . $params['variant_id'];

    if (!empty($params['advanced_filter']) && $params['advanced_filter'] == 'Y') {
        fn_add_breadcrumb(__('advanced_filter'));
        list($filters) = fn_get_filters_products_count($params);
        Registry::get('view')->assign('filter_features', $filters);
    }

    // Get products
    $params['extend'] = array('description');
    list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.products_per_page'));

    fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => true, 'get_features' => false));

    $selected_layout = fn_get_products_layout($_REQUEST);

    Registry::get('view')->assign('products', $products);
    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('selected_layout', $selected_layout);
}

function fn_get_product_data_for_compare($product_ids, $action)
{
    $auth = & $_SESSION['auth'];

    $comparison_data = array(
        'product_features' => array(0 => array())
    );
    $tmp = array();
    foreach ($product_ids as $product_id) {
        $product_data = fn_get_product_data($product_id, $auth, CART_LANGUAGE, '', false, true, false, false);

        fn_gather_additional_product_data($product_data, false, false, false, true, false);

        if (!empty($product_data['product_features'])) {
            foreach ($product_data['product_features'] as $k => $v) {
                if ($v['feature_type'] == 'G' && empty($v['subfeatures'])) {
                    continue;
                }
                $_features = ($v['feature_type'] == 'G') ? $v['subfeatures'] : array($k => $v);
                $group_id = ($v['feature_type'] == 'G') ? $k : 0;
                $comparison_data['feature_groups'][$k] = $v['description'];
                foreach ($_features as $_k => $_v) {
                    if (in_array($_k, $_SESSION['excluded_features'])) {
                        if (empty($comparison_data['hidden_features'][$_k])) {
                            $comparison_data['hidden_features'][$_k] = $_v['description'];
                        }
                        continue;
                    }

                    if (empty($comparison_data['product_features'][$group_id][$_k])) {
                        $comparison_data['product_features'][$group_id][$_k] = $_v['description'];
                    }
                }
            }
        }

        $comparison_data['products'][] = $product_data;
        unset($product_data);
    }

    if ($action != 'show_all' && !empty($comparison_data['product_features'])) {
        $value = '';

        foreach ($comparison_data['product_features'] as $group_id => $v) {
            foreach ($v as $feature_id => $_v) {
                unset($value);
                $c = ($action == 'similar_only') ? true : false;
                foreach ($comparison_data['products'] as $product) {
                    $features = !empty($group_id) && isset($product['product_features'][$group_id]) ? $product['product_features'][$group_id]['subfeatures'] : $product['product_features'];
                    if (empty($features[$feature_id])) {
                        $c = !$c;
                        break;
                    }
                    if (!isset($value)) {
                        $value = fn_get_feature_selected_value($features[$feature_id]);
                        continue;
                    } elseif ($value != fn_get_feature_selected_value($features[$feature_id])) {
                        $c = !$c;
                        break;
                    }
                }

                if ($c == false) {
                    unset($comparison_data['product_features'][$group_id][$feature_id]);
                }
            }
        }
    }

    return $comparison_data;
}

function fn_get_feature_selected_value($feature)
{
    $value = null;

    if (strpos('SMNE', $feature['feature_type']) !== false) {
        if ($feature['feature_type'] == 'M') {
            foreach ($feature['variants'] as $v) {
                if ($v['selected']) {
                    $value[] = $v['variant_id'];
                }
            }
        } else {
            $value = $feature['variant_id'];
        }

    } elseif (strpos('OD', $feature['feature_type']) !== false) {
        $value = $feature['value_int'];
    } else {
        $value = $feature['value'];
    }

    return $value;
}
