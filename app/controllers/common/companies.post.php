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
if ($mode == 'get_companies_list') {

    // Check if we trying to get list by non-ajax
    if (!defined('AJAX_REQUEST')) {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url());
    }

    //TODO make single function

    $params = $_REQUEST;
    $condition = '';
    $pattern = !empty($params['pattern']) ? $params['pattern'] : '';
    $start = !empty($params['start']) ? $params['start'] : 0;
    $limit = (!empty($params['limit']) ? $params['limit'] : 10) + 1;

    if (AREA == 'C') {
        $condition = " AND status = 'A' ";
    }

    fn_set_hook('get_companies_list', $condition, $pattern, $start, $limit, $params);

    $objects = db_get_hash_array("SELECT company_id as value, company AS name, CONCAT('switch_company_id=', company_id) as append FROM ?:companies WHERE 1 $condition AND company LIKE ?l ORDER BY company LIMIT ?i, ?i", 'value', $pattern . '%', $start, $limit);

    if (defined('AJAX_REQUEST') && sizeof($objects) < $limit) {
        Registry::get('ajax')->assign('completed', true);
    } else {
        array_pop($objects);
    }

    if (empty($params['start']) && empty($params['pattern'])) {
        $all_vendors = array();

        if (!empty($params['show_all']) && $params['show_all'] == 'Y') {
            $all_vendors[0] = array(
                'name' => empty($params['default_label']) ? __('all_vendors') : __($params['default_label']),
                'value' => (!empty($params['search']) && $params['search'] == 'Y') ? '' : 0,
            );
        }

        $objects = $all_vendors + $objects;
    }

    if (defined('AJAX_REQUEST') && !empty($params['action'])) {
        Registry::get('ajax')->assign('action', $params['action']);
    }

    if (!empty($params['onclick'])) {
        Registry::get('view')->assign('onclick', $params['onclick']);
    }
    Registry::get('view')->assign('objects', $objects);
    Registry::get('view')->assign('id', $params['result_ids']);
    Registry::get('view')->display('common/ajax_select_object.tpl');
    exit;
}
