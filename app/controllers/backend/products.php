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

use Tygh\Enum\ProductTracking;
use Tygh\Registry;
use Tygh\BlockManager\SchemesManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['product_id'] = empty($_REQUEST['product_id']) ? 0 : $_REQUEST['product_id'];

if (fn_allowed_for('MULTIVENDOR')) {
    if (
        isset($_REQUEST['product_id']) && !fn_company_products_check($_REQUEST['product_id'])
        ||
        isset($_REQUEST['product_ids']) && !fn_company_products_check($_REQUEST['product_ids'])
    ) {
        return array(CONTROLLER_STATUS_DENIED);
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'product_data',
        'override_products_data',
        'product_files_descriptions',
        'add_product_files_descriptions',
        'products_data',
        'product_file'
    );

    //
    // Apply Global Option
    //
    if ($mode == 'apply_global_option') {
        if ($_REQUEST['global_option']['link'] == 'N') {
            fn_clone_product_options(0, $_REQUEST['product_id'], $_REQUEST['global_option']['id']);
        } else {
            db_query("REPLACE INTO ?:product_global_option_links (option_id, product_id) VALUES(?i, ?i)", $_REQUEST['global_option']['id'], $_REQUEST['product_id']);

            if (fn_allowed_for('ULTIMATE')) {
                fn_ult_share_product_option($_REQUEST['global_option']['id'], $_REQUEST['product_id']);
            }
        }
        $suffix = ".update?product_id=$_REQUEST[product_id]";
    }
    //
    // Create/update product
    //
    if ($mode == 'update') {
        if (!empty($_REQUEST['product_data']['product'])) {

            fn_companies_filter_company_product_categories($_REQUEST, $_REQUEST['product_data']);
            if (empty($_REQUEST['product_data']['category_ids'])) {
                fn_set_notification('E', __('error'), __('category_is_empty'));

                return array(CONTROLLER_STATUS_REDIRECT, !empty($_REQUEST['product_id']) ? "products.update?product_id=" . $_REQUEST['product_id'] : "products.add");
            } else {
                $_REQUEST['product_data']['category_ids'] = explode(',', $_REQUEST['product_data']['category_ids']);
            }

            $product_id = fn_update_product($_REQUEST['product_data'], $_REQUEST['product_id'], DESCR_SL);

            if ($product_id === false) {
                // Some error occured
                fn_save_post_data('product_data');

                return array(CONTROLLER_STATUS_REDIRECT, !empty($_REQUEST['product_id']) ? "products.update?product_id=" . $_REQUEST['product_id'] : "products.add");
            }
        }

        if (!empty($_REQUEST['product_id'])) {
            if (!empty($_REQUEST['add_users'])) {
                // Updating product subscribers
                $users = db_get_array("SELECT user_id, email FROM ?:users WHERE user_id IN (?n)", $_REQUEST['add_users']);

                if (!empty($users)) {
                    foreach ($users as $user) {
                        $subscription_id = db_get_field("SELECT subscription_id FROM ?:product_subscriptions WHERE product_id = ?i AND email = ?s", $_REQUEST['product_id'], $user['email']);
                        if (empty($subscription_id)) {
                            $subscription_id = db_query("INSERT INTO ?:product_subscriptions ?e", array('product_id' => $_REQUEST['product_id'], 'user_id' => $user['user_id'], 'email' => $user['email']));
                        } else {
                            db_query("REPLACE INTO ?:product_subscriptions ?e", array('subscription_id' => $subscription_id, 'product_id' => $_REQUEST['product_id'], 'user_id' => $user['user_id'], 'email' => $user['email']));
                        }
                    }
                } elseif (!empty($_REQUEST['add_users_email'])) {
                    if (!db_get_field("SELECT subscription_id FROM ?:product_subscriptions WHERE product_id = ?i AND email = ?s", $_REQUEST['product_id'], $_REQUEST['add_users_email'])) {
                        db_query("INSERT INTO ?:product_subscriptions ?e", array('product_id' => $_REQUEST['product_id'], 'user_id' => 0, 'email' => $_REQUEST['add_users_email']));
                    } else {
                        fn_set_notification('E', __('error'), __('warning_subscr_email_exists', array(
                            '[email]' => $_REQUEST['add_users_email']
                        )));
                    }
                }
            } elseif (!empty($_REQUEST['subscriber_ids'])) {
                db_query("DELETE FROM ?:product_subscriptions WHERE subscription_id IN (?n)", $_REQUEST['subscriber_ids']);
            }

            return array(CONTROLLER_STATUS_OK, "products.update?product_id=" . $_REQUEST['product_id'] . "&selected_section=subscribers");
        }

        if (!empty($product_id)) {
            $suffix = ".update?product_id=$product_id" . (!empty($_REQUEST['product_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['product_data']['block_id'] : "");
        } else {
            $suffix = '.manage';
        }

    }

    //
    // Processing mulitple addition of new product elements
    //
    if ($mode == 'm_add') {

        if (is_array($_REQUEST['products_data'])) {
            $p_ids = array();
            foreach ($_REQUEST['products_data'] as $k => $v) {
                if (!empty($v['product']) && !empty($v['category_ids'])) {  // Checking for required fields for new product
                    fn_companies_filter_company_product_categories($_REQUEST, $v);
                    $p_id = fn_update_product($v);
                    if (!empty($p_id)) {
                        $p_ids[] = $p_id;
                    }
                }
            }

            if (!empty($p_ids)) {
                fn_set_notification('N', __('notice'), __('text_products_added'));
            }
        }
        $suffix = ".manage" . (empty($p_ids) ? "" : "?pid[]=" . implode('&pid[]=', $p_ids));
    }

    //
    // Processing multiple updating of product elements
    //
    if ($mode == 'm_update') {
        // Update multiple products data
        if (!empty($_REQUEST['products_data'])) {

            if (fn_allowed_for('MULTIVENDOR') && !fn_company_products_check(array_keys($_REQUEST['products_data']))) {
                return array(CONTROLLER_STATUS_DENIED);
            }

            // Update images
            fn_attach_image_pairs('product_main', 'product', 0, DESCR_SL);

            foreach ($_REQUEST['products_data'] as $k => $v) {
                if (!empty($v['product'])) { // Checking for required fields for new product

                    if (fn_allowed_for('ULTIMATE,MULTIVENDOR') && Registry::get('runtime.company_id')) {
                        unset($v['company_id']);
                    }

                    fn_companies_filter_company_product_categories($_REQUEST, $v);

                    if (!empty($v['category_ids'])) {
                        $v['category_ids'] = explode(',', $v['category_ids']);
                    }

                    fn_update_product($v, $k, DESCR_SL);

                    // Updating products position in category
                    if (isset($v['position']) && !empty($_REQUEST['category_id'])) {
                        db_query("UPDATE ?:products_categories SET position = ?i WHERE category_id = ?i AND product_id = ?i", $v['position'], $_REQUEST['category_id'], $k);
                    }
                }
            }
        }
        $suffix = ".manage";
    }

    //
    // Processing global updating of product elements
    //

    if ($mode == 'global_update') {

        fn_global_update_products($_REQUEST['update_data']);

        $suffix = '.global_update';

    }

    //
    // Override multiple products with the one value
    //
    if ($mode == 'm_override') {
        // Update multiple products data
        if (!empty($_SESSION['product_ids'])) {

            if (fn_allowed_for('MULTIVENDOR') && !fn_company_products_check($_SESSION['product_ids'])) {
                return array(CONTROLLER_STATUS_DENIED);
            }

            $product_data = !empty($_REQUEST['override_products_data']) ? $_REQUEST['override_products_data'] : array();
            if (isset($product_data['avail_since'])) {
                $product_data['avail_since'] = fn_parse_date($product_data['avail_since']);
            }
            if (isset($product_data['timestamp'])) {
                $product_data['timestamp'] = fn_parse_date($product_data['timestamp']);
            }

            if (fn_allowed_for('ULTIMATE,MULTIVENDOR') && Registry::get('runtime.company_id')) {
                unset($product_data['company_id']);
            }

            fn_define('KEEP_UPLOADED_FILES', true);

            fn_companies_filter_company_product_categories($_REQUEST, $product_data);
            if (!empty($product_data['category_ids'])) {
                $product_data['category_ids'] = explode(',', $product_data['category_ids']);
            }

            foreach ($_SESSION['product_ids'] as $_o => $p_id) {
                // Update product
                fn_update_product($product_data, $p_id, DESCR_SL);
            }
        }
    }


    //
    // Processing deleting of multiple product elements
    //
    if ($mode == 'm_delete') {
        if (isset($_REQUEST['product_ids'])) {
            foreach ($_REQUEST['product_ids'] as $v) {
                fn_delete_product($v);
            }
        }
        unset($_SESSION['product_ids']);
        fn_set_notification('N', __('notice'), __('text_products_have_been_deleted'));
        $suffix = ".manage";
    }

    //
    // Processing deleting of multiple product subscriptions
    //
    if ($mode == 'm_delete_subscr') {
        if (isset($_REQUEST['product_ids'])) {
            db_query("DELETE FROM ?:product_subscriptions WHERE product_id IN (?n)", $_REQUEST['product_ids']);
        }
        unset($_SESSION['product_ids']);
        $suffix = ".p_subscr";
    }

    //
    // Processing clonning of multiple product elements
    //
    if ($mode == 'm_clone') {
        $p_ids = array();
        if (!empty($_REQUEST['product_ids'])) {
            foreach ($_REQUEST['product_ids'] as $v) {
                $pdata = fn_clone_product($v);
                $p_ids[] = $pdata['product_id'];
            }

            fn_set_notification('N', __('notice'), __('text_products_cloned'));
        }
        $suffix = ".manage?pid[]=" . implode('&pid[]=', $p_ids);
        unset($_REQUEST['redirect_url'], $_REQUEST['page']); // force redirection
    }

    //
    // Storing selected fields for using in m_update mode
    //
    if ($mode == 'store_selection') {

        if (!empty($_REQUEST['product_ids'])) {
            $_SESSION['product_ids'] = $_REQUEST['product_ids'];
            $_SESSION['selected_fields'] = $_REQUEST['selected_fields'];

            unset($_REQUEST['redirect_url']);

            $suffix = ".m_update";
        } else {
            $suffix = ".manage";
        }
    }

    //
    // Add edp files to the product
    //
    if ($mode == 'update_file') {
        if (!empty($_REQUEST['product_file'])) {

            if (empty($_REQUEST['product_file']['folder_id'])) {
                $_REQUEST['product_file']['folder_id'] = null;
            }
            $file_id = fn_update_product_file($_REQUEST['product_file'], $_REQUEST['file_id'], DESCR_SL);
        }

        $suffix = ".update?product_id=$_REQUEST[product_id]";
    }

    //
    // Add edp folder to the product
    //
    if ($mode == 'update_folder') {

        if (!empty($_REQUEST['product_file_folder'])) {
            $folder_id = fn_update_product_file_folder($_REQUEST['product_file_folder'], $_REQUEST['folder_id'], DESCR_SL);
        }

        $suffix = ".update?product_id=$_REQUEST[product_id]";
    }

    if ($mode == 'export_range') {
        if (!empty($_REQUEST['product_ids'])) {
            if (empty($_SESSION['export_ranges'])) {
                $_SESSION['export_ranges'] = array();
            }

            if (empty($_SESSION['export_ranges']['products'])) {
                $_SESSION['export_ranges']['products'] = array('pattern_id' => 'products');
            }

            $_SESSION['export_ranges']['products']['data'] = array('product_id' => $_REQUEST['product_ids']);

            unset($_REQUEST['redirect_url']);

            return array(CONTROLLER_STATUS_REDIRECT, "exim.export?section=products&pattern_id=" . $_SESSION['export_ranges']['products']['pattern_id']);
        }
    }

    return array(CONTROLLER_STATUS_OK, "products$suffix");
}

//
// 'Management' page
//
if ($mode == 'manage' || $mode == 'p_subscr') {
    unset($_SESSION['product_ids']);
    unset($_SESSION['selected_fields']);

    $params = $_REQUEST;
    $params['only_short_fields'] = true;
    $params['extend'][] = 'companies';

    if (fn_allowed_for('ULTIMATE')) {
        $params['extend'][] = 'sharing';
    }

    if ($mode == 'p_subscr') {
        $params['get_subscribers'] = true;
    }

    list($products, $search) = fn_get_products($params, Registry::get('settings.Appearance.admin_products_per_page'), DESCR_SL);
    fn_gather_additional_products_data($products, array('get_icon' => true, 'get_detailed' => true, 'get_options' => false, 'get_discounts' => false));

    Registry::get('view')->assign('products', $products);
    Registry::get('view')->assign('search', $search);

    if (!empty($_REQUEST['redirect_if_one']) && $search['total_items'] == 1) {
        return array(CONTROLLER_STATUS_REDIRECT, "products.update?product_id={$products[0]['product_id']}");
    }

    $selected_fields = fn_get_product_fields();

    Registry::get('view')->assign('selected_fields', $selected_fields);
    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $filter_params = array(
            'get_variants' => true,
            'short' => true
        );
        list($filters) = fn_get_product_filters($filter_params);
        Registry::get('view')->assign('filter_items', $filters);
        unset($filters);
    }

    $feature_params = array(
        'plain' => true,
        'variants' => true,
        'exclude_group' => true,
        'exclude_filters' => true
    );
    list($features) = fn_get_product_features($feature_params);
    Registry::get('view')->assign('feature_items', $features);
    unset($features);
}
//
// 'Add new product' page
//
if ($mode == 'add') {

    Registry::get('view')->assign('taxes', fn_get_taxes());

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'images' => array (
            'title' => __('images'),
            'js' => true
        ),
        'seo' => array(
            'title' => __('seo'),
            'js' => true
        ),
        'qty_discounts' => array (
            'title' => __('qty_discounts'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    ));
    // [/Page sections]

    $product_data = fn_restore_post_data('product_data');
    Registry::get('view')->assign('product_data', $product_data);

//
// 'Multiple products addition' page
//
} elseif ($mode == 'm_add') {

//
// 'product update' page
//
} elseif ($mode == 'update') {
    $selected_section = (empty($_REQUEST['selected_section']) ? 'detailed' : $_REQUEST['selected_section']);

    // Get current product data
    $product_data = fn_get_product_data($_REQUEST['product_id'], $auth, DESCR_SL, '', true, true, true, true, false, true, false);

    if (!empty($_REQUEST['deleted_subscription_id'])) {
        if (!Registry::get('runtime.company_id') || Registry::get('runtime.company_id') && $product_data['company_id'] == Registry::get('runtime.company_id')) {
            db_query("DELETE FROM ?:product_subscriptions WHERE subscription_id = ?i", $_REQUEST['deleted_subscription_id']);
        }
    }

    if (empty($product_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if (fn_allowed_for('ULTIMATE') && !empty($product_data['shared_product']) && $product_data['shared_product'] = 'Y') {
        $product_data = fn_get_product_data($_REQUEST['product_id'], $auth, DESCR_SL, '', true, true, true, true, false, true, true);
    }

    $taxes = fn_get_taxes();
    arsort($product_data['category_ids']);

    if (fn_allowed_for('MULTIVENDOR')) {
        // reload form (refresh categories list if vendor was changed)
        if (defined('AJAX_REQUEST') && !empty($_REQUEST['reload_form'])) {
            $company_id = isset($_REQUEST['product_data']['company_id']) ? $_REQUEST['product_data']['company_id'] : 0;
            $company_data = fn_get_company_data($company_id);
            if (!empty($company_data['categories'])) {
                $params = array (
                    'simple' => false,
                    'company_ids' => $company_id,
                );
                list($cat_ids, ) = fn_get_categories($params);
                $cat_ids = array_keys($cat_ids);
                //Assign available category ids to be displayed after admin changes product owner.
                $product_data['category_ids'] = array_intersect($product_data['category_ids'], $cat_ids);
            }
            //Assign received company_id to product data for the correct company categories to be displayed in the picker.
            $product_data['company_id'] = $company_id;
            Registry::get('view')->assign('product_data', $product_data);
            Registry::get('view')->display('views/products/update.tpl');
            exit;
        }
    }

    Registry::get('view')->assign('product_data', $product_data);
    Registry::get('view')->assign('taxes', $taxes);

    $product_options = fn_get_product_options($_REQUEST['product_id'], DESCR_SL);
    if (!empty($product_options)) {
        $has_inventory = false;
        foreach ($product_options as $p) {
            if ($p['inventory'] == 'Y') {
                $has_inventory = true;
                break;
            }
        }
        Registry::get('view')->assign('has_inventory', $has_inventory);
    }
    Registry::get('view')->assign('product_options', $product_options);
    list($global_options) = fn_get_product_global_options();
    Registry::get('view')->assign('global_options', $global_options);

    // If the product is electronnicaly distributed, get the assigned files
    list($product_files) = fn_get_product_files(array('product_id' => $_REQUEST['product_id']));
    list($product_file_folders) = fn_get_product_file_folders( array('product_id' => $_REQUEST['product_id']) );
    $files_tree = fn_build_files_tree($product_file_folders, $product_files);

    Registry::get('view')->assign('product_file_folders', $product_file_folders);
    Registry::get('view')->assign('product_files', $product_files);
    Registry::get('view')->assign('files_tree', $files_tree);

    Registry::get('view')->assign('expand_all', true);

    list($subscribers, $search) = fn_get_product_subscribers($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));
    Registry::get('view')->assign('product_subscribers', $subscribers);
    Registry::get('view')->assign('product_subscribers_search', $search);

    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'images' => array (
            'title' => __('images'),
            'js' => true
        ),
        'seo' => array(
            'title' => __('seo'),
            'js' => true
        ),
        'options' => array (
            'title' => __('options'),
            'js' => true
        ),
        'shippings' => array (
            'title' => __('shipping_properties'),
            'js' => true
        ),
        'qty_discounts' => array (
            'title' => __('qty_discounts'),
            'js' => true
        ),
        'files' => array (
            'title' => __('sell_files'),
            'js' => true
        ),
        'subscribers' => array (
            'title' => __('subscribers'),
            'js' => true
        ),
    );

    $tabs['addons'] = array (
        'title' => __('addons'),
        'js' => true
    );

    // If we have some additional product fields, lets add a tab for them
    if (!empty($product_data['product_features'])) {
        $tabs['features'] = array(
            'title' => __('features'),
            'js' => true
        );
    }

    // [Product tabs]
    // block manager is disabled for vendors.
    if (!(
        fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')
        ||
        fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')
    )) {
        $dynamic_object = SchemesManager::getDynamicObject($_REQUEST['dispatch'], AREA);
        if (!empty($dynamic_object)) {
            if (AREA == 'A' && Registry::get('runtime.mode') != 'add' && !empty($_REQUEST[$dynamic_object['key']])) {

                $params = array(
                    'dynamic_object' => array(
                        'object_type' => $dynamic_object['object_type'],
                        'object_id' => $_REQUEST[$dynamic_object['key']]
                    ),
                    $dynamic_object['key'] => $_REQUEST[$dynamic_object['key']]
                );

                $tabs['product_tabs'] = array(
                    'title' => __('product_tabs'),
                    'href' => 'tabs.manage_in_tab?' . http_build_query($params),
                    'ajax' => true,
                );
            }
        }
    }
    // [/Product tabs]
    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]

//
// 'Mulitple products updating' page
//
} elseif ($mode == 'm_update') {

    if (empty($_SESSION['product_ids']) || empty($_SESSION['selected_fields']) || empty($_SESSION['selected_fields']['object']) || $_SESSION['selected_fields']['object'] != 'product') {
        return array(CONTROLLER_STATUS_REDIRECT, "products.manage");
    }

    $product_ids = $_SESSION['product_ids'];

    if (fn_allowed_for('MULTIVENDOR') && !fn_company_products_check($product_ids)) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    $selected_fields = $_SESSION['selected_fields'];

    $field_groups = array (
        'A' => array ( // inputs
            'product' => 'products_data',
            'product_code' => 'products_data',
            'page_title' => 'products_data',
        ),

        'B' => array ( // short inputs
            'price' => 'products_data',
            'list_price' => 'products_data',
            'amount' => 'products_data',
            'min_qty' => 'products_data',
            'max_qty' => 'products_data',
            'weight' => 'products_data',
            'shipping_freight' => 'products_data',
            'box_height' => 'products_data',
            'box_length' => 'products_data',
            'box_width' => 'products_data',
            'min_items_in_box' => 'products_data',
            'max_items_in_box' => 'products_data',
            'qty_step' => 'products_data',
            'list_qty_count' => 'products_data',
            'popularity' => 'products_data'
        ),

        'C' => array ( // checkboxes
            'is_edp' => 'products_data',
            'edp_shipping' => 'products_data',
            'free_shipping' => 'products_data',
            'feature_comparison' => 'products_data'
        ),

        'D' => array ( // textareas
            'short_description' => 'products_data',
            'full_description' => 'products_data',
            'meta_keywords' => 'products_data',
            'meta_description' => 'products_data',
            'search_words' => 'products_data',
        ),
        'T' => array( // dates
            'timestamp' => 'products_data',
            'avail_since' => 'products_data',
        ),
        'S' => array ( // selectboxes
            'out_of_stock_actions' => array (
                'name' => 'products_data',
                'variants' => array (
                    'N' => 'none',
                    'B' => 'buy_in_advance',
                    'S' => 'sign_up_for_notification'
                ),
            ),
            'status' => array (
                'name' => 'products_data',
                'variants' => array (
                    'A' => 'active',
                    'D' => 'disabled',
                    'H' => 'hidden'
                ),
            ),
            'tracking' => array (
                'name' => 'products_data',
                'variants' => array (
                    ProductTracking::TRACK_WITH_OPTIONS => 'track_with_options',
                    ProductTracking::TRACK_WITHOUT_OPTIONS => 'track_without_options',
                    ProductTracking::DO_NOT_TRACK => 'dont_track'
                ),
            ),
            'zero_price_action' => array (
                'name' => 'products_data',
                'variants' => array (
                    'R' => 'zpa_refuse',
                    'P' => 'zpa_permit',
                    'A' => 'zpa_ask_price'
                ),
            ),
        ),
        'E' => array ( // categories
            'categories' => 'products_data'
        ),
        'W' => array( // Product details layout
            'details_layout' => 'products_data'
        )
    );

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $field_groups['L'] = array( // miltiple selectbox (localization)
            'localization' => array(
                'name' => 'localization'
            ),
        );
    }

    $data = array_keys($selected_fields['data']);
    $get_main_pair = false;
    $get_taxes = false;
    $get_features = false;

    $fields2update = $data;

    // Process fields that are not in products or product_descriptions tables
    if (!empty($selected_fields['categories']) && $selected_fields['categories'] == 'Y') {
        $fields2update[] = 'categories';
    }
    if (!empty($selected_fields['main_pair']) && $selected_fields['main_pair'] == 'Y') {
        $get_main_pair = true;
        $fields2update[] = 'main_pair';
    }
    if (!empty($selected_fields['data']['taxes']) && $selected_fields['data']['taxes'] == 'Y') {
        Registry::get('view')->assign('taxes', fn_get_taxes());
        $fields2update[] = 'taxes';
        $get_taxes = true;
    }
    if (!empty($selected_fields['data']['features']) && $selected_fields['data']['features'] == 'Y') {
        $fields2update[] = 'features';
        $get_features = true;

        // get features for categories of selected products only
        $id_paths = db_get_fields("SELECT ?:categories.id_path FROM ?:products_categories LEFT JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id WHERE product_id IN (?n)", $product_ids);

        $_params = array(
            'variants' => true,
            'category_ids' => array_unique(explode('/', implode('/', $id_paths)))
        );

        list($all_product_features) = fn_get_product_features($_params, 0, DESCR_SL);

        Registry::get('view')->assign('all_product_features', $all_product_features);
    }

    foreach ($product_ids as $value) {
        $products_data[$value] = fn_get_product_data($value, $auth, DESCR_SL, '?:products.*, ?:product_descriptions.*', false, $get_main_pair, $get_taxes, false, false, $get_features, true);
    }

    $filled_groups = array();
    $field_names = array();

    foreach ($fields2update as $k => $field) {
        if ($field == 'main_pair') {
            $desc = 'image_pair';
        } elseif ($field == 'tracking') {
            $desc = 'inventory';
        } elseif ($field == 'edp_shipping') {
            $desc = 'downloadable_shipping';
        } elseif ($field == 'is_edp') {
            $desc = 'downloadable';
        } elseif ($field == 'timestamp') {
            $desc = 'creation_date';
        } elseif ($field == 'categories') {
            $desc = 'categories';
        } elseif ($field == 'status') {
            $desc = 'status';
        } elseif ($field == 'avail_since') {
            $desc = 'available_since';
        } elseif ($field == 'min_qty') {
            $desc = 'min_order_qty';
        } elseif ($field == 'max_qty') {
            $desc = 'max_order_qty';
        } elseif ($field == 'qty_step') {
            $desc = 'quantity_step';
        } elseif ($field == 'list_qty_count') {
            $desc = 'list_quantity_count';
        } elseif ($field == 'usergroup_ids') {
            $desc = 'usergroups';
        } elseif ($field == 'details_layout') {
            $desc = 'product_details_layout';
        } elseif ($field == 'max_items_in_box') {
            $desc = 'maximum_items_in_box';
        } elseif ($field == 'min_items_in_box') {
            $desc = 'minimum_items_in_box';
        } elseif ($field == 'amount') {
            $desc = 'quantity';
        } else {
            $desc = $field;
        }

        if (!empty($field_groups['A'][$field])) {
            $filled_groups['A'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['B'][$field])) {
            $filled_groups['B'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['C'][$field])) {
            $filled_groups['C'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['D'][$field])) {
            $filled_groups['D'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['S'][$field])) {
            $filled_groups['S'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['T'][$field])) {
            $filled_groups['T'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['E'][$field])) {
            $filled_groups['E'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['L'][$field])) {
            $filled_groups['L'][$field] = __($desc);
            continue;
        } elseif (!empty($field_groups['W'][$field])) {
            $filled_groups['W'][$field] = __($desc);
            continue;
        }

        $field_names[$field] = __($desc);
    }


    ksort($filled_groups, SORT_STRING);

    Registry::get('view')->assign('field_groups', $field_groups);
    Registry::get('view')->assign('filled_groups', $filled_groups);

    Registry::get('view')->assign('field_names', $field_names);
    Registry::get('view')->assign('products_data', $products_data);

//
// Delete product
//
} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['product_id'])) {
        $result = fn_delete_product($_REQUEST['product_id']);
        if ($result) {
            fn_set_notification('N', __('notice'), __('text_product_has_been_deleted'));
        } else {
            return array(CONTROLLER_STATUS_REDIRECT, "products.update?product_id=$_REQUEST[product_id]");
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, "products.manage");

} elseif ($mode == 'delete_subscr') {

    if (!empty($_REQUEST['product_id'])) {
        db_query("DELETE FROM ?:product_subscriptions WHERE product_id = ?i", $_REQUEST['product_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "products.p_subscr");

} elseif ($mode == 'get_file') {

    if (fn_get_product_file($_REQUEST['file_id'], !empty($_REQUEST['file_type'])) == false) {
        return array(CONTROLLER_STATUS_DENIED);
    }
    exit;

} elseif ($mode == 'clone') {
    if (!empty($_REQUEST['product_id'])) {
        $pid = $_REQUEST['product_id'];
        $pdata = fn_clone_product($pid);
        if (!empty($pdata['product_id'])) {
            $pid = $pdata['product_id'];
            fn_set_notification('N', __('notice'), __('text_product_cloned'));
        }

        return array(CONTROLLER_STATUS_REDIRECT, "products.update?product_id=$pid");
    }
} elseif ($mode == 'update_file') {

    if (!empty($_REQUEST['product_id'])) {

        if (!empty($_REQUEST['file_id'])) {
            $params = array (
                'product_id' => $_REQUEST['product_id'],
                'file_ids' => $_REQUEST['file_id']
            );

            list($product_files) = fn_get_product_files($params);
            $product_file = reset($product_files);
            $product_file['company_id'] = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $_REQUEST['product_id']);

            Registry::get('view')->assign('product_file', $product_file);
        }

        list($product_file_folders) = fn_get_product_file_folders(array('product_id' => $_REQUEST['product_id']));
        Registry::get('view')->assign('product_file_folders', $product_file_folders);

        Registry::get('view')->assign('product_id', $_REQUEST['product_id']);
    }

} elseif ($mode == 'update_folder') {

    if (!empty($_REQUEST['product_id'])) {

        if (!empty($_REQUEST['folder_id'])) {
            $params = array (
                'product_id' => $_REQUEST['product_id'],
                'folder_ids' => $_REQUEST['folder_id']
            );

            list($product_file_folders) = fn_get_product_file_folders($params);
            $product_file_folder = reset($product_file_folders);
            $product_file_folder['company_id'] = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $_REQUEST['product_id']);

            Registry::get('view')->assign('product_file_folder', $product_file_folder);
        }

        Registry::get('view')->assign('product_id', $_REQUEST['product_id']);
    }

} elseif ($mode == 'delete_file') {

    if (!empty($_REQUEST['file_id']) && !empty($_REQUEST['product_id'])) {

        if (fn_delete_product_files($_REQUEST['file_id']) == false) {
            return array(CONTROLLER_STATUS_DENIED);
        }

        list($_files) = fn_get_product_files(array('product_id' => $_REQUEST['product_id']));
        list($_folder) = fn_get_product_file_folders(array('product_id' => $_REQUEST['product_id']));

        if (empty($_files) && empty($_folder)) {
            Registry::get('view')->assign('product_id', $_REQUEST['product_id']);
        }
    }

    return array(CONTROLLER_STATUS_OK, fn_url('products.update?product_id=' . $_REQUEST['product_id'] . '&selected_section=files'));

} elseif ($mode == 'delete_folder') {

    if (!empty($_REQUEST['folder_id']) && !empty($_REQUEST['product_id'])) {

        if (fn_delete_product_file_folders($_REQUEST['folder_id'], $_REQUEST['product_id']) == false) {
            return array(CONTROLLER_STATUS_DENIED);
        }

        list($product_files) = fn_get_product_files(array('product_id' => $_REQUEST['product_id']));
        list($product_file_folders) = fn_get_product_file_folders( array('product_id' => $_REQUEST['product_id']) );
        $files_tree = fn_build_files_tree($product_file_folders, $product_files);

        Registry::get('view')->assign('product_file_folders', $product_file_folders);
        Registry::get('view')->assign('product_files', $product_files);
        Registry::get('view')->assign('files_tree', $files_tree);

        Registry::get('view')->assign('product_id', $_REQUEST['product_id']);
    }

    return array(CONTROLLER_STATUS_OK, fn_url('products.update?product_id=' . $_REQUEST['product_id'] . '&selected_section=files'));
}
