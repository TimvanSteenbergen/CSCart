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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'category_data',
        'categories_data'
    );

    //
    // Create/update category
    //
    if ($mode == 'update') {
        if (fn_allowed_for('ULTIMATE')) {
            if (!empty($_REQUEST['category_id']) && !fn_check_company_id('categories', 'category_id', $_REQUEST['category_id'])) {
                fn_company_access_denied_notification();

                return array(CONTROLLER_STATUS_OK, "categories.update?category_id=$_REQUEST[category_id]");
            }
        }

        $category_id = fn_update_category($_REQUEST['category_data'], $_REQUEST['category_id'], DESCR_SL);

        if (!empty($category_id)) {
            fn_attach_image_pairs('category_main', 'category', $category_id, DESCR_SL);

            $suffix = ".update?category_id=$category_id" . (!empty($_REQUEST['category_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['category_data']['block_id'] : "");
        } else {
            $suffix = '.manage';
        }
    }

    //
    // Processing mulitple addition of new category elements
    //
    if ($mode == 'm_add') {
        if (!fn_is_empty($_REQUEST['categories_data'])) {
            $is_added = false;
            foreach ($_REQUEST['categories_data'] as $k => $v) {
                if (!empty($v['category'])) {  // Checking for required fields for new category
                    if (fn_update_category($v)) {
                        $is_added = true;
                    }
                }
            }

            if ($is_added) {
                fn_set_notification('N', __('notice'), __('categories_have_been_added'));
            }
        }


        $suffix = ".manage";
    }

    //
    // Processing multiple updating of category elements
    //
    if ($mode == 'm_update') {

        // Update multiple categories data
        if (is_array($_REQUEST['categories_data'])) {
            fn_attach_image_pairs('category_main', 'category', 0, DESCR_SL);

            foreach ($_REQUEST['categories_data'] as $k => $v) {
                if (!fn_allowed_for('ULTIMATE') || (fn_allowed_for('ULTIMATE') && fn_check_company_id('categories', 'category_id', $k))) {
                    if (fn_allowed_for('ULTIMATE')) {
                        fn_set_company_id($v);
                    }
                    fn_update_category($v, $k, DESCR_SL);
                }
            }
        }

        $suffix = ".manage";
    }

    //
    // Processing deleting of multiple category elements
    //
    if ($mode == 'm_delete') {

        if (isset($_REQUEST['category_ids'])) {
            foreach ($_REQUEST['category_ids'] as $v) {
                if (fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('ULTIMATE') && fn_check_company_id('categories', 'category_id', $v))) {
                    fn_delete_category($v);
                }
            }
        }

        unset($_SESSION['category_ids']);

        fn_set_notification('N', __('notice'), __('text_categories_have_been_deleted'));
        $suffix = ".manage";
    }


    //
    // Store selected fields for using in 'm_update' mode
    //
    if ($mode == 'store_selection') {

        if (!empty($_REQUEST['category_ids'])) {
            $_SESSION['category_ids'] = $_REQUEST['category_ids'];
            $_SESSION['selected_fields'] = $_REQUEST['selected_fields'];

            $suffix = ".m_update";
        } else {
            $suffix = ".manage";
        }
    }

    return array(CONTROLLER_STATUS_OK, "categories$suffix");
}

//
// 'Add new category' page
//
if ($mode == 'add') {

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
        'layout' => array(
            'title' => __('layout'),
            'js' => true
        ),
    ));
    // [/Page sections]


    if (!empty($_REQUEST['parent_id'])) {
        $category_data['parent_id'] = $_REQUEST['parent_id'];
        Registry::get('view')->assign('category_data', $category_data);
    }

//
// 'Multiple categories addition' page
//
} elseif ($mode == 'm_add') {

//
// 'category update' page
//
} elseif ($mode == 'update') {

    $category_id = $_REQUEST['category_id'];
    // Get current category data
    $category_data = fn_get_category_data($category_id, DESCR_SL);

    if (empty($category_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    // [Page sections]
    $tabs = array (
        'detailed' => array (
            'title' => __('general'),
            'js' => true
        )
    );

    $tabs['addons'] = array (
        'title' => __('addons'),
        'js' => true
    );

    $tabs['views'] = array (
        'title' => __('views'),
        'js' => true
    );
    Registry::set('navigation.tabs', $tabs);
    // [/Page sections]
    Registry::get('view')->assign('category_data', $category_data);

    $params = array (
        'active_category_id' => $category_id,
    );
    $category_count = db_get_field("SELECT COUNT(*) FROM ?:categories");
    if ($category_count > CATEGORY_THRESHOLD) {
        $params['current_category_id'] = $category_id;
        $params['visible'] = true;
    }
    list($categories_tree, ) = fn_get_categories($params, DESCR_SL);
    Registry::get('view')->assign('categories_tree', $categories_tree);

//
// 'Mulitple categories updating' page
//
} elseif ($mode == 'm_update') {

    $category_ids = $_SESSION['category_ids'];
    $selected_fields = $_SESSION['selected_fields'];

    if (empty($category_ids) || empty($selected_fields) || empty($selected_fields['object']) || $selected_fields['object'] != 'category') {
        return array(CONTROLLER_STATUS_REDIRECT, "categories.manage");
    }

    $field_groups = array (
        'A' => array (
            'category' => 'categories_data',
            'page_title' => 'categories_data',
            'position' => 'categories_data',
        ),

        'C' => array ( // textareas
            'description' => 'categories_data',
            'meta_keywords' => 'categories_data',
            'meta_description' => 'categories_data',
        ),
    );

    $get_main_pair = false;

    $fields2update = $selected_fields['data'];

    $data_search_fields = implode($fields2update, ', ');

    if (!empty($data_search_fields)) {
        $data_search_fields = ', ' . $data_search_fields;
    }

    if (!empty($selected_fields['images'])) {
        foreach ($selected_fields['images'] as $value) {
            $fields2update[] = $value;
            if ($value == 'image_pair') {
                $get_main_pair = true;
            }
        }
    }

    $filled_groups = array();
    $field_names = array();
    foreach ($fields2update as $field) {
        if ($field == 'usergroup_ids') {
            $desc = 'usergroups';
        } elseif ($field == 'timestamp') {
            $desc = 'creation_date';
        } else {
            $desc = $field;
        }
        if ($field == 'category_id') {
            continue;
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
        }

        $field_names[$field] = __($desc);
    }

    ksort($filled_groups, SORT_STRING);

    $categories_data = array();
    foreach ($category_ids as $value) {
        $categories_data[$value] = fn_get_category_data($value, DESCR_SL, '?:categories.category_id, ?:categories.company_id' . $data_search_fields, $get_main_pair);
    }

    Registry::get('view')->assign('field_groups', $field_groups);
    Registry::get('view')->assign('filled_groups', $filled_groups);

    Registry::get('view')->assign('fields2update', $fields2update);
    Registry::get('view')->assign('field_names', $field_names);

    Registry::get('view')->assign('categories_data', $categories_data);
}
//
// Delete category
//
elseif ($mode == 'delete') {

    if (!empty($_REQUEST['category_id'])) {
        fn_delete_category($_REQUEST['category_id']);
    }

    fn_set_notification('N', __('notice'), __('text_category_has_been_deleted'));

    return array(CONTROLLER_STATUS_REDIRECT, "categories.manage");

//
// 'Management' page
//
} elseif ($mode == 'manage' || $mode == 'picker') {

    if ($mode == 'manage') {
        unset($_SESSION['category_ids']);
        unset($_SESSION['selected_fields']);
        Registry::get('view')->assign('categories_stats', fn_get_categories_stats());
    }

    $category_count = db_get_field("SELECT COUNT(*) FROM ?:categories");
    $category_id = empty($_REQUEST['category_id']) ? 0 : $_REQUEST['category_id'];
    $except_id = 0;
    if (!empty($_REQUEST['except_id'])) {
        $except_id = $_REQUEST['except_id'];
        Registry::get('view')->assign('except_id', $_REQUEST['except_id']);
    }
    if ($category_count < CATEGORY_THRESHOLD) {
        $params = array (
            'simple' => false,
            'add_root' => !empty($_REQUEST['root']) ? $_REQUEST['root'] : '',
            'b_id' => !empty($_REQUEST['b_id']) ? $_REQUEST['b_id'] : '',
            'except_id' => $except_id,
            'company_ids' => !empty($_REQUEST['company_ids']) ? $_REQUEST['company_ids'] : '',
            'save_view_results' => !empty($_REQUEST['save_view_results']) ? $_REQUEST['save_view_results'] : ''
        );
        list($categories_tree, ) = fn_get_categories($params, DESCR_SL);
        Registry::get('view')->assign('show_all', true);
    } else {
        $params = array (
            'category_id' => $category_id,
            'current_category_id' => $category_id,
            'visible' => true,
            'simple' => false,
            'add_root' => !empty($_REQUEST['root']) ? $_REQUEST['root'] : '',
            'b_id' => !empty($_REQUEST['b_id']) ? $_REQUEST['b_id'] : '',
            'except_id' => $except_id,
            'company_ids' => !empty($_REQUEST['company_ids']) ? $_REQUEST['company_ids'] : '',
            'save_view_results' => !empty($_REQUEST['save_view_results']) ? $_REQUEST['save_view_results'] : ''
        );
        list($categories_tree, ) = fn_get_categories($params, DESCR_SL);
    }

    Registry::get('view')->assign('categories_tree', $categories_tree);
    if ($category_count < CATEGORY_SHOW_ALL) {
        Registry::get('view')->assign('expand_all', true);
    }
    if (defined('AJAX_REQUEST')) {
        if (!empty($_REQUEST['random'])) {
            Registry::get('view')->assign('random', $_REQUEST['random']);
        }
        Registry::get('view')->assign('category_id', $category_id);
    }
}

//
// Categories picker
//
if ($mode == 'picker') {
    Registry::get('view')->display('pickers/categories/picker_contents.tpl');
    exit;
}
