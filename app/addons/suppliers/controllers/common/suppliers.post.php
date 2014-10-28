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

// Ajax content
if ($mode == 'get_suppliers_list') {

    $params = $_REQUEST;
    $condition = '';
    $pattern = !empty($params['pattern']) ? $params['pattern'] : '';
    $start = !empty($params['start']) ? $params['start'] : 0;
    $limit = (!empty($params['limit']) ? $params['limit'] : 10) + 1;

    if (AREA == 'C') {
        $condition .= " AND ?:suppliers.status = 'A' ";
    }

    if (isset($params['exclude_supplier_id'])) {
        $condition .= db_quote(" AND ?:suppliers.supplier_id != ?i", intval($params['exclude_supplier_id']));
    }

    if (isset($params['company_id']) || Registry::get('runtime.company_id')) {
        $copmpany_id = isset($params['company_id']) ? intval($params['company_id']) : Registry::get('runtime.company_id');
        $condition .= fn_get_company_condition("?:suppliers.company_id", true, $copmpany_id);
    }

    $suppliers = db_get_hash_array("SELECT ?:suppliers.supplier_id as value, ?:suppliers.name FROM ?:suppliers WHERE 1 ?p AND ?:suppliers.name LIKE ?l ORDER BY ?:suppliers.name LIMIT ?i, ?i", 'value', $condition, $pattern . '%', $start, $limit);

    if (!$start) {
        array_unshift($suppliers, array('value' => 0, 'name' => '-' . __('none') . '-'));
    }

    if (defined('AJAX_REQUEST') && sizeof($suppliers) < $limit) {
        Registry::get('ajax')->assign('completed', true);
    } else {
        array_pop($suppliers);
    }

    Registry::get('view')->assign('objects', $suppliers);
    Registry::get('view')->assign('id', $params['result_ids']);
    Registry::get('view')->display('common/ajax_select_object.tpl');
    exit;
}
