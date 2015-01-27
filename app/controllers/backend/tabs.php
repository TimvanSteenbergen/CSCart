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
use Tygh\BlockManager\ProductTabs;
use Tygh\BlockManager\SchemesManager;
use Tygh\BlockManager\Location;
use Tygh\BlockManager\Block;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //
    // Update product tab
    //
    if (($mode == 'update') || ($mode == 'add_tab')) {
        if (!empty($_REQUEST['tab_data'])) {
            $tab_data = $_REQUEST['tab_data'];
            $tab_data['lang_code'] = DESCR_SL;

            if (!empty($_REQUEST['block_data']['block_id'])) {
                $tab_data['block_id'] = $_REQUEST['block_data']['block_id'];
            }

            if (!isset($tab_data['tab_id']) || $tab_data['tab_id'] == 0) {
                $tab_data['position'] =  ProductTabs::instance()->getMaxPosition() + 1;;
            }

            ProductTabs::instance()->update($tab_data);
        }
    }

    //
    // Delete product tab
    //
    if ($mode == 'delete') {
        if (!empty($_REQUEST['tab_id'])) {
            ProductTabs::instance()->delete($_REQUEST['tab_id']);
        }
    }

    return array(CONTROLLER_STATUS_OK, "tabs.manage");
}

// ---------------------- GET routines ---------------------------------------

if ($mode == 'manage' || $mode == 'manage_in_tab') {

    $product_id = 0;
    if (!empty($_REQUEST['dynamic_object'])) {
        Registry::get('view')->assign('dynamic_object', $_REQUEST['dynamic_object']);
        $product_id = $_REQUEST['dynamic_object']['object_id'];
        $dynamic_object_scheme = SchemesManager::getDynamicObjectByType($_REQUEST['dynamic_object']['object_type']);
        $selected_location = Location::instance()->get($dynamic_object_scheme['customer_dispatch'], $_REQUEST['dynamic_object'], DESCR_SL);
        Registry::get('view')->assign('location', $selected_location);
        Registry::get('view')->assign('dynamic_object_scheme', $dynamic_object_scheme);
    }

    $product_tabs = ProductTabs::instance()->getList('', $product_id, DESCR_SL);

    Registry::get('view')->assign('product_tabs', $product_tabs);

    if ($mode == 'manage_in_tab') {
        Registry::get('view')->display('views/tabs/manage_in_tab.tpl');
        exit;
    }

} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['tab_id'])) {
        ProductTabs::instance()->delete($_REQUEST['tab_id']);
    }

    return array(CONTROLLER_STATUS_OK, "tabs.manage");

} elseif ($mode == 'update') {
    $tab_id = isset($_REQUEST['tab_data']['tab_id']) ? $_REQUEST['tab_data']['tab_id'] : 0;
    $tab_type = isset($_REQUEST['tab_data']['tab_type']) ? $_REQUEST['tab_data']['tab_type'] : 'T';

    if (!empty($_REQUEST['dynamic_object'])) {
        Registry::get('view')->assign('dynamic_object', $_REQUEST['dynamic_object']);
    }

    $dynamic_object_scheme = SchemesManager::getDynamicObjectByType('products');
    $selected_location = Location::instance()->get('products.view', array(), DESCR_SL);
    Registry::get('view')->assign('location', $selected_location);
    Registry::get('view')->assign('dynamic_object_scheme', $dynamic_object_scheme);

    if (!empty($_REQUEST['tab_data'])) {
        $tab_data = $_REQUEST['tab_data'];
    } else {
        $tab_data = array();
    }

    // If edit block
    if ($tab_id > 0 && empty($_REQUEST['tab_data']['content'])) {
        $tab_data = current(ProductTabs::instance()->getList(db_quote(' AND ?:product_tabs.tab_id=?i', $tab_id), 0, DESCR_SL));
    }

    if (isset($tab_data['block_id']) && $tab_data['block_id'] > 0) {
        if (!empty($_REQUEST['dynamic_object'])) {
            $dynamic_object = $_REQUEST['dynamic_object'];
        } else {
            $dynamic_object = array();
        }

        Registry::get('view')->assign('block_data', Block::instance()->getById($tab_data['block_id'], 0, $dynamic_object, DESCR_SL));
    }

    if (empty($tab_data['type'])) {
        $tab_data['type'] = $tab_type;
    }

    Registry::get('view')->assign('tab_data', $tab_data);
} elseif ($mode == 'update_status') {
    $result = false;
    if (!empty($_REQUEST['id']) && !empty($_REQUEST['status'])) {

        $tab = ProductTabs::instance()->getList(db_quote(" AND ?:product_tabs.tab_id=?i", $_REQUEST['id']), 0);
        $tab = current($tab);

        if (!empty($_REQUEST['dynamic_object']['object_id']) && $_REQUEST['dynamic_object']['object_id'] > 0) {
            // If it's status update for dynamic object
            $object_ids = explode(',', $tab['product_ids']);

            $key = array_search($_REQUEST['dynamic_object']['object_id'], $object_ids);

            if ($_REQUEST['status'] == $tab['status'] && isset($object_ids[$key])) {
                unset($object_ids[$key]);
            } elseif ($_REQUEST['status'] != $tab['status']) {
                $object_ids[] = $_REQUEST['dynamic_object']['object_id'];
            }

            foreach ($object_ids as $k => $v) {
                if (empty($v)) {
                    unset($object_ids[$k]);
                }
            }

            ProductTabs::instance()->update(array(
                'tab_id' => $_REQUEST['id'],
                'product_ids' => implode(',', $object_ids)
            ));

            $result = true;
        } else {
            // If it's simple status update just do it
            ProductTabs::instance()->update(array(
                'tab_id' => $_REQUEST['id'],
                'status' => $_REQUEST['status'],
                'product_ids' => '',
            ));

            $result = true;
        }

        if ($result) {
            fn_set_notification('N', __('notice'), __('status_changed'));
        } else {
            fn_set_notification('E', __('error'), __('error_status_not_changed'));
            Registry::get('ajax')->assign('return_status', $tab['status']);
        }
    }
    exit;
}
