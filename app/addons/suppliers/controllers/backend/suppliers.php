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
    $suffix = '.manage';

    if ($mode == 'update') {
        // Update supplier data
        $supplier_id = empty($_REQUEST['supplier_id']) ? 0 : $_REQUEST['supplier_id'];
        $supplier_id = fn_update_supplier($supplier_id, $_REQUEST['supplier_data']);

        if ($supplier_id) {
            $suffix = '.update?supplier_id=' . $supplier_id;
        }
    }

    if ($mode == 'm_delete') {

        if (!empty($_REQUEST['supplier_ids'])) {
            foreach ($_REQUEST['supplier_ids'] as $v) {
                fn_delete_supplier($v);
            }
        }

        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, 'suppliers' . $suffix);
}

if ($mode == 'manage') {
    list($suppliers, $search) = fn_get_suppliers($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    $view = Registry::get('view');
    $view->assign('search', $search);
    $view->assign('suppliers', $suppliers);
    $view->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    $view->assign('states', fn_get_all_states());

}

if ($mode == 'update' || $mode == 'add') {

    Registry::set('navigation.tabs', array (
        'general' => array (
            'title' => __('general'),
            'js' => true
        ),
        'products' => array (
            'title' => __('products'),
            'js' => true
        ),
        'shippings' => array (
            'title' => __('shippings'),
            'js' => true
        ),
    ));

    $supplier = !empty($_REQUEST['supplier_id']) ? fn_get_supplier_data($_REQUEST['supplier_id']) : array();

    $condition = " AND ?:shippings.status = 'A'";
    if (Registry::get('runtime.company_id') && !fn_allowed_for('ULTIMATE')) {
        $condition = fn_get_company_condition('?:shippings.company_id');
        $company_data = Registry::get('runtime.company_data');
        if (!empty($company_data['shippings'])) {
            $condition .= db_quote(" OR ?:shippings.shipping_id IN (?n)", explode(',', $company_data['shippings']));
        }
    }

    $shippings = db_get_hash_array("SELECT ?:shippings.shipping_id, ?:shipping_descriptions.shipping FROM ?:shippings LEFT JOIN ?:shipping_descriptions ON ?:shippings.shipping_id = ?:shipping_descriptions.shipping_id AND ?:shipping_descriptions.lang_code = ?s LEFT JOIN ?:companies ON ?:companies.company_id = ?:shippings.company_id WHERE 1 $condition ORDER BY ?:shippings.position", 'shipping_id', CART_LANGUAGE);

    $view = Registry::get('view');

    $view->assign('shippings', $shippings);
    $view->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    $view->assign('states', fn_get_all_states());

    $view->assign('supplier', $supplier);

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['supplier_id'])) {
        $supplier_data = fn_get_supplier_data($_REQUEST['supplier_id']);
        if (!empty($supplier_data)) {
            $result = fn_delete_supplier($supplier_data['supplier_id']);
            if ($result) {
                fn_set_notification('N', __('notice'), __('supplier_deleted'));
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, 'suppliers.manage');

} elseif ($mode == 'update_status') {

    $condition = fn_get_company_condition('?:suppliers.company_id');
    $supplier_data = db_get_row("SELECT * FROM ?:suppliers WHERE supplier_id = ?i $condition", $_REQUEST['id']);
    if (!empty($supplier_data)) {
        $result = fn_update_status_supplier($supplier_data['supplier_id'], $_REQUEST['status']);
        if ($result) {
            fn_set_notification('N', __('notice'), __('status_changed'));
        } else {
            fn_set_notification('E', __('error'), __('error_status_not_changed'));
            Registry::get('ajax')->assign('return_status', $supplier_data['status']);
        }
    }

    exit;
}
