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

Use Ebay\Ebay;
Use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update') {
        if ($template_id = fn_update_ebay_template($_REQUEST['template_data'], $_REQUEST['template_id'])) {
            return array(CONTROLLER_STATUS_OK, "ebay.update?template_id=$template_id");
        } else {
            fn_save_post_data('template_data');
            fn_delete_notification('changes_saved');
        }
        
        return array(CONTROLLER_STATUS_OK, "ebay.add");
    }

    if ($mode == 'm_delete') {
        foreach ($_REQUEST['template_ids'] as $template_id) {
            fn_delete_ebay_template($template_id);
        }
    }

    if ($mode == 'export') {
        $products_data = db_get_hash_multi_array("SELECT product_id,company_id,ebay_template_id FROM ?:products WHERE product_id IN (?n)", array('company_id', 'product_id'), $_REQUEST['product_ids']);
        $template_ids = array();
        if (!empty($products_data)) {
            foreach ($products_data as $company_id => $products) {
                foreach($products as $product_id => $product) {
                    if (!empty($product['ebay_template_id'])) {
                        $template_ids[$product['ebay_template_id']][] = $product_id;
                    } else {
                        $default_template_id = db_get_field("SELECT template_id FROM ?:ebay_templates WHERE use_as_default = 'Y' AND company_id = ?i", $company_id);
                        if (!empty($default_template_id)) {
                            $template_ids[$default_template_id][] = $product_id;
                        } else {
                            $company_name = fn_get_company_name($company_id);
                            fn_set_notification('E', __('error'), __('ebay_default_template_not_found', array('[company_name]' => $company_name)));
                        }
                    }
                }
            }
        }

        $result = true;
        foreach ($template_ids as $template_id => $product_ids) {
            //We can switch sharing of because we select necessary templates before.
            Registry::set('runtime.skip_sharing_selection', true);
            $template_data = fn_get_ebay_template($template_id);
            Registry::set('runtime.skip_sharing_selection', false);
            $result = fn_export_ebay_products($template_data, $product_ids, $auth);
        }

        if (!$result) {
            fn_set_notification('E', __('error'), __('ebay_export_error'));
        }

        return array(CONTROLLER_STATUS_OK, "products.manage");
    }
    
    return array(CONTROLLER_STATUS_OK, "ebay.manage");
}

if ($mode == 'manage') {

    $params = $_REQUEST;
    
    list($templates, $search) = fn_get_ebay_templates($params, Registry::get('settings.Appearance.admin_items_per_page'), DESCR_SL);
    Registry::get('view')->assign('templates', $templates);
    Registry::get('view')->assign('search', $search);
    
} elseif ($mode == 'add') {

    $template_data = fn_restore_post_data('template_data');
    if (isset($_REQUEST['site_id'])) {
        $template_data['site_id'] = $_REQUEST['site_id'];
    }
    if (isset($template_data['site_id'])) {
        Ebay::instance()->site_id = $template_data['site_id'];
    }

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'shippings' => array (
            'title' => __('shippings'),
            'js' => true
        ),
        'payments' => array (
            'title' => __('payments'),
            'js' => true
        ),
        'returnPolicy' => array (
            'title' => __('return_policy'),
            'js' => true
        )
    ));
    // [/Page sections]
    $ebay_root_categories = fn_get_ebay_categories(0);
    
    Registry::get('view')->assign('ebay_root_categories', $ebay_root_categories);
    Registry::get('view')->assign('template_data', $template_data);
    
} elseif ($mode == 'update') {

    $template_data = fn_get_ebay_template($_REQUEST['template_id']);
    if (isset($_REQUEST['site_id'])) {
        $template_data['site_id'] = $_REQUEST['site_id'];
    }
    if (isset($template_data['site_id'])) {
        Ebay::instance()->site_id = $template_data['site_id'];
    }

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'shippings' => array (
            'title' => __('shippings'),
            'js' => true
        ),
        'payments' => array (
            'title' => __('payments'),
            'js' => true
        ),
        'returnPolicy' => array (
            'title' => __('return_policy'),
            'js' => true
        )
    ));
    // [/Page sections]
    $ebay_root_categories = fn_get_ebay_categories(0);
    
    Registry::get('view')->assign('ebay_root_categories', $ebay_root_categories);
    Registry::get('view')->assign('template_data', $template_data);
    
} elseif ($mode == 'get_subcategories') {

    $subcategories = array();
    if (!empty($_REQUEST['parent_id'])) {
        $subcategories = fn_get_ebay_categories($_REQUEST['parent_id'], true);
    }
    
    Registry::get('view')->assign('ebay_categories', $subcategories);
    Registry::get('view')->assign('data_id', $_REQUEST['data_id']);
    Registry::get('view')->assign('required_field', $_REQUEST['required_field']);
    
    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->display('addons/ebay/views/ebay/components/ebay_categories.tpl');
        exit;
    }
    
} elseif ($mode == 'get_category_features') {

    $template_data = $features = array();
    if (!empty($_REQUEST['category_id'])) {
        $features = fn_get_ebay_category_features($_REQUEST['category_id']);
    }
    if (!empty($_REQUEST['template_id'])) {
        $template_data = fn_get_ebay_template($_REQUEST['template_id']);
    }
    
    Registry::get('view')->assign('template_data', $template_data);
    Registry::get('view')->assign('category_features', $features);
    Registry::get('view')->assign('data_id', $_REQUEST['data_id']);

    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->display('addons/ebay/views/ebay/components/category_features.tpl');
        exit;
    } else {
    fn_print_die($features, $_REQUEST);
    }
    
} elseif ($mode == 'get_shippings') {

    $template_data = fn_get_ebay_template($_REQUEST['template_id']);
    
    Registry::get('view')->assign('shipping_type', $_REQUEST['shipping_type']);
    Registry::get('view')->assign('template_data', $template_data);
    if (defined('AJAX_REQUEST')) {
        Registry::get('view')->display('addons/ebay/views/ebay/update.tpl');
        exit;
    }
} elseif ($mode == 'get_orders') {
    if (fn_allowed_for('ULTIMATE')) {
        if (Registry::get('runtime.company_id')) {
            list($success_orders, $failed_orders) = fn_get_ebay_orders();
            
            if (!empty($success_orders)) {
                fn_set_notification('N', __('successful'), __('ebay_success_orders_notice', array('[SUCCESS_IDS]' => implode(', ', $success_orders))));
            }
            
            if (!empty($failed_orders)) {
                fn_set_notification('W', __('warning'), __('ebay_failed_orders_notice', array('[FAILED_EBAY_IDS]' => implode(', ', $failed_orders))));
            }
        } else {
            fn_set_notification('W', __('warning'), __('store_object_denied', array(
                '[object_type]' => '',
                '[object_name]' => ''
            )), '', 'store_object_denied');
        }
    } else {
        list($success_orders, $failed_orders) = fn_get_ebay_orders();
        
        if (!empty($success_orders)) {
            fn_set_notification('N', __('successful'), __('ebay_success_orders_notice', array('[SUCCESS_IDS]' => implode(', ', $success_orders))));
        }
        
        if (!empty($failed_orders)) {
            fn_set_notification('W', __('warning'), __('ebay_failed_orders_notice', array('[FAILED_EBAY_IDS]' => implode(', ', $failed_orders))));
        }
    }
    return array(CONTROLLER_STATUS_REDIRECT, 'orders.manage');

} elseif ($mode == 'delete_template') {
    if (!empty($_REQUEST['template_id'])) {
        fn_delete_ebay_template($_REQUEST['template_id']);
    }

    return array(CONTROLLER_STATUS_OK, "ebay.manage");
}
