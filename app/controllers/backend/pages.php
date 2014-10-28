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

/** Body **/

if (!empty($_REQUEST['page_id'])) {
    $page_id = $_REQUEST['page_id'];
} else {
    $page_id = 0;
    Registry::get('view')->assign('show_all', true);
}

if (!empty($_REQUEST['page_data']['page_id']) && $page_id == 0) {
    $page_id = intval($_REQUEST['page_data']['page_id']);
}

/* POST data processing */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars('page_data');

    //
    // Create/update page
    //
    if ($mode == 'update') {

        // Updating page record
        $page_id = fn_update_page($_REQUEST['page_data'], $_REQUEST['page_id'], DESCR_SL);

        if (isset($_REQUEST['redirect_url'])) {
            $_REQUEST['redirect_url'] .= (!empty($_REQUEST['come_from']) ? '&page_type=' . $_REQUEST['come_from'] :  '&get_tree=multi_level');
        }

        if (empty($page_id)) {
            $suffix = '.manage';
        } else {
            $suffix = ".update?page_id=$page_id" . (!empty($_REQUEST['page_data']['block_id']) ? "&selected_block_id=" . $_REQUEST['page_data']['block_id'] : "") . '&come_from=' . (!empty($_REQUEST['come_from']) ? $_REQUEST['come_from'] : '');
        }
    }

    //
    // Processing multiple updating of page elements
    //
    if ($mode == 'm_update') {
        // Update multiple pages data
        foreach ($_REQUEST['pages_data'] as $page_id => $page_data) {
            fn_update_page($page_data, $page_id, DESCR_SL);
        }

        $suffix = ".manage";
    }

    //
    // Processing deleting of multiple page elements
    //
    if ($mode == 'm_delete') {
        if (isset($_REQUEST['page_ids'])) {
            foreach ($_REQUEST['page_ids'] as $v) {
                fn_delete_page($v);
            }
        }
        unset($_SESSION['page_ids']);
        fn_set_notification('N', __('notice'), __('text_pages_have_been_deleted'));

        $suffix = ".manage?" . (empty($_REQUEST['page_type']) ? "" : ("page_type=" . $_REQUEST['page_type']));
    }

    //
    // Processing clonning of multiple page elements
    //
    if ($mode == 'm_clone') {
        $p_ids = array();
        if (!empty($_REQUEST['page_ids'])) {
            foreach ($_REQUEST['page_ids'] as $v) {
                $pdata = fn_clone_page($v);
                if (!empty($pdata)) {
                    $p_ids[] = $pdata['page_id'];
                }
            }
            fn_set_notification('N', __('notice'), __('text_pages_cloned'));
        }
        $suffix = ".manage?item_ids=" . implode(',', $p_ids);
        unset($_REQUEST['redirect_url'], $_REQUEST['page']); // force redirection
    }

    //
    // Storing selected fields for using in m_update mode
    //
    if ($mode == 'store_selection') {
        $_SESSION['page_ids'] = $_REQUEST['page_ids'];
        $_SESSION['selected_fields'] = $_REQUEST['selected_fields'];

        if (isset($_SESSION['page_ids'])) {
            $suffix = ".m_update";
        } else {
            $suffix = ".manage";
        }
    }

    //
    // This mode is using to send search data via POST method
    //
    if ($mode == 'search_pages') {
        $suffix = ".manage";
    }

    if (empty($suffix)) {
        $suffix = '.manage';
    }

    return array(CONTROLLER_STATUS_OK, "pages$suffix");
}
/* /POST data processing */

//

if ($mode == 'update' || $mode == 'add') {
    $page_type = isset($_REQUEST['page_type']) ? $_REQUEST['page_type'] : PAGE_TYPE_TEXT;

    $tabs = array (
        'basic' => array (
            'title' => __('general'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        ),
    );

    Registry::set('navigation.tabs', $tabs);

    if ($mode == "update") {
        // Get current page data
        $page_data = fn_get_page_data($page_id, DESCR_SL);

        if (empty($page_data)) {
            $page_data = !empty($_REQUEST['page_data']) ? $_REQUEST['page_data'] : array();

            if (empty($page_data)) {
                return array(CONTROLLER_STATUS_NO_PAGE);
            }
        }

        $page_type = isset($page_data['page_type']) ? $page_data['page_type'] : PAGE_TYPE_TEXT;
    } else {
        $page_data = array();

        $page_data['page_type'] = $page_type;

        if (!empty($_REQUEST['parent_id'])) {
            $page_data['parent_id'] = $_REQUEST['parent_id'];
        }
    }

    if (!empty($_REQUEST['page_data']['company_id']) && fn_allowed_for('ULTIMATE') || isset($_REQUEST['page_data']['company_id']) && fn_allowed_for('MULTIVENDOR') ) {
        $page_data['company_id'] = $_REQUEST['page_data']['company_id'];
    } elseif (empty($page_data['company_id']) && Registry::get('runtime.company_id')) {
        $page_data['company_id'] = Registry::get('runtime.company_id');
    } elseif (!isset($page_data['company_id']) && fn_allowed_for('ULTIMATE')) {
        $company_ids = fn_get_all_companies_ids();
        if (count($company_ids) > 1) {
            $page_data['company_id'] = reset($company_ids);
        }
    }

    if (Registry::get('runtime.company_id') && isset($page_data['company_id']) && $page_data['company_id'] != Registry::get('runtime.company_id')) {
        $var = Registry::get('navigation.dynamic.actions');
        $vars = array('delete_this_page', 'add_page', 'add_link');
        foreach ($vars as $val) {
            if (isset($var[$val])) {
                unset($var[$val]);
            }
        }
        Registry::set('navigation.dynamic.actions', $var);
    }

    if (!empty($page_id)) {
        $params = array(
            'get_tree' => 'multi_level',
            'active_page_id' => $page_id
        );
        $pages_count = db_get_field("SELECT COUNT(*) FROM ?:pages WHERE ?:pages.page_type IN (?a)", array_keys(fn_get_page_object_by_type()));
        if ($pages_count > PAGE_THRESHOLD) {
            $params['current_page_id'] = $page_id;
            $params['visible'] = true;
        }

        list($pages_tree, ) = fn_get_pages($params);
        Registry::get('view')->assign('pages_tree', $pages_tree);
    }

    $params = array();
    if (!empty($page_data['company_id'])) {
        $params['company_id'] = $page_data['company_id'];
    } elseif (Registry::get('runtime.company_id')) {
        $params['company_id'] = Registry::get('runtime.company_id');
    }

    Registry::get('view')->assign('come_from', !empty($_REQUEST['come_from']) ? $_REQUEST['come_from'] : '');
    Registry::get('view')->assign('page_type', $page_data['page_type']);
    Registry::get('view')->assign('page_data', $page_data);
    Registry::get('view')->assign('page_type_data', fn_get_page_object_by_type($page_data['page_type']));

    $parent_pages = fn_get_pages_plain_list($params);

    if (count($parent_pages) < PAGE_THRESHOLD) {
        Registry::get('view')->assign('parent_pages', $parent_pages);
    }
//
// Delete page
//
} elseif ($mode == 'delete') {
    $suffix = '';

    if (!empty($page_id)) {
        if (!empty($_REQUEST['come_from'])) {
            $suffix = '?page_type=' . $_REQUEST['come_from'];
        } else {
            $suffix = '?get_tree=multi_level';
        }
        fn_delete_page($page_id);
        fn_set_notification('N', __('notice'), __('text_page_has_been_deleted'));
    }

    return array(CONTROLLER_STATUS_REDIRECT, "pages.manage$suffix");

//
// Clone page
//
} elseif ($mode == 'clone') {
    if (!empty($_REQUEST['page_id'])) {
        $pdata = fn_clone_page($_REQUEST['page_id']);

        fn_set_notification('N', __('notice'), __('page_cloned', array(
            '[page]' => $pdata['orig_name']
        )));

        return array(CONTROLLER_STATUS_REDIRECT, "pages.update?page_id=$pdata[page_id]");
    }
//
// 'Management' page
//
} elseif ($mode == 'manage' || $mode == 'picker') {

    $params = $_REQUEST;
    if ($mode == 'picker') {
        $params['skip_view'] = 'Y';
        $params['get_parent_pages'] = true;
    }

    $items_per_page = 0;
    if (!empty($params['get_tree'])) { // manage page, show tree
        $condition = db_quote(" AND ?:pages.page_type IN (?a)", array_keys(fn_get_page_object_by_type()));
        $total = db_get_field("SELECT COUNT(*) FROM ?:pages WHERE 1 ?p", $condition);
        if ($total > PAGE_THRESHOLD) {
            $params['get_children_count'] = true;
            $params['get_tree'] = '';
            $params['parent_id'] = !empty($params['parent_id']) ? $params['parent_id'] : 0;

            if (defined('AJAX_REQUEST')) {
                Registry::get('view')->assign('parent_id', $params['parent_id']);
                Registry::get('view')->assign('hide_header', true);
            }

            Registry::get('view')->assign('hide_show_all', true);
        }
        if ($total < PAGE_SHOW_ALL) {
            Registry::get('view')->assign('expand_all', true);
        }
    } else { // search page
        $items_per_page = Registry::get('settings.Appearance.admin_pages_per_page');
    }

    $params['add_root'] = !empty($_REQUEST['root']) ? $_REQUEST['root'] : '';

    list($pages, $params) = fn_get_pages($params, $items_per_page);

    Registry::get('view')->assign('pages_tree', $pages);
    Registry::get('view')->assign('search', $params);
    Registry::get('view')->assign('page_types', fn_get_page_object_by_type());

    if (!empty($_REQUEST['except_id'])) {
        Registry::get('view')->assign('except_id', $_REQUEST['except_id']);
    }

    if ($mode == 'picker') {
        if (!empty($_REQUEST['combination_suffix'])) {
            Registry::get('view')->assign('combination_suffix', $_REQUEST['combination_suffix']);
        }
        Registry::get('view')->display('pickers/pages/picker_contents.tpl');
        exit;
    }
} elseif ($mode == 'get_parent_pages') {
    $page_data = array();
    if (isset($_REQUEST['page_id'])) {
        Registry::get('view')->assign('page_id', $_REQUEST['page_id']);
        $page_data = fn_get_page_data($_REQUEST['page_id'], DESCR_SL);
    } else {
        Registry::get('view')->assign('show_all', true);
    }

    if (!empty($_REQUEST['parent_id'])) {
        $page_data['parent_id'] = $_REQUEST['parent_id'];
    }

    Registry::get('view')->assign('page_data', $page_data);
    Registry::get('view')->assign('parent_pages', fn_get_pages_plain_list());
    Registry::get('view')->display('views/pages/components/parent_page_selector.tpl');
    exit;
}

Registry::get('view')->assign('usergroups', fn_get_usergroups('C', DESCR_SL));
/* /Preparing page data for templates and performing simple actions*/

/** /Body **/
