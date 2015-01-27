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

    if ($mode == 'update') {
        fn_update_product_filter($_REQUEST['filter_data'], $_REQUEST['filter_id'], DESCR_SL);
    }

    return array(CONTROLLER_STATUS_OK, "product_filters.manage");
}

if ($mode == 'manage' || $mode == 'picker') {

    $params = $_REQUEST;
    $params['get_descriptions'] = true;

    list($filters, $search) = fn_get_product_filters($params, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('filters', $filters);
    Registry::get('view')->assign('search', $search);

    if (fn_allowed_for('ULTIMATE:FREE') && count($filters) > FILTERS_LIMIT) {
        fn_set_notification('W', __('warning'), __('product_filters_free_limit'));
    }

    if ($mode == 'manage') {
        Registry::get('view')->assign('filter_fields', fn_get_product_filter_fields());

        if (empty($filters) && defined('AJAX_REQUEST')) {
            Registry::get('ajax')->assign('force_redirection', fn_url('product_filters.manage'));
        }

        $params = array(
            'variants' => true,
            'plain' => true,
            'feature_types' => array('S', 'E', 'N', 'M', 'O', 'D'),
        );

        list($filter_features) = fn_get_product_features($params, 0, DESCR_SL);
        Registry::get('view')->assign('filter_features', $filter_features);
    }

    if ($mode == 'picker') {
        Registry::get('view')->display('pickers/filters/picker_contents.tpl');
        exit;
    }

} elseif ($mode == 'update') {

    $params = $_REQUEST;
    $params['get_variants'] = true;

    list($filters) = fn_get_product_filters($params);
    Registry::get('view')->assign('filter', array_shift($filters));

    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
        Registry::get('view')->assign('picker_selected_companies', fn_ult_get_controller_shared_companies($_REQUEST['filter_id']));
    }

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['filter_id'])) {
        if (fn_allowed_for('ULTIMATE')) {
            if (!fn_check_company_id('product_filters', 'filter_id', $_REQUEST['filter_id'])) {
                fn_company_access_denied_notification();

                return array(CONTROLLER_STATUS_REDIRECT, "product_filters.manage");
            }
        }

        fn_delete_product_filter($_REQUEST['filter_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "product_filters.manage");
}

function fn_update_product_filter($filter_data, $filter_id, $lang_code = DESCR_SL)
{
    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        if (!empty($filter_id) && !fn_check_company_id('product_filters', 'filter_id', $filter_id)) {
            fn_company_access_denied_notification();

            return false;
        }
        if (!empty($filter_id)) {
            unset($filter_data['company_id']);
        }
    }

    // Parse filter type
    if (strpos($filter_data['filter_type'], 'FF-') === 0 || strpos($filter_data['filter_type'], 'RF-') === 0 || strpos($filter_data['filter_type'], 'DF-') === 0) {
        $filter_data['feature_id'] = str_replace(array('RF-', 'FF-', 'DF-'), '', $filter_data['filter_type']);
        $filter_data['feature_type'] = db_get_field("SELECT feature_type FROM ?:product_features WHERE feature_id = ?i", $filter_data['feature_id']);
    } else {
        $filter_data['field_type'] = str_replace(array('R-', 'B-'), '', $filter_data['filter_type']);
        $filter_fields = fn_get_product_filter_fields();
    }

    if (isset($filter_data['display_more_count']) && isset($filter_data['display_count']) && $filter_data['display_more_count'] < $filter_data['display_count']) {
        $filter_data['display_more_count'] = $filter_data['display_count'];
    }

    if (!empty($filter_id)) {
        db_query('UPDATE ?:product_filters SET ?u WHERE filter_id = ?i', $filter_data, $filter_id);
        db_query('UPDATE ?:product_filter_descriptions SET ?u WHERE filter_id = ?i AND lang_code = ?s', $filter_data, $filter_id, $lang_code);
    } else {
        $filter_data['filter_id'] = $filter_id = db_query('INSERT INTO ?:product_filters ?e', $filter_data);
        foreach (fn_get_translation_languages() as $filter_data['lang_code'] => $_d) {
            db_query("INSERT INTO ?:product_filter_descriptions ?e", $filter_data);
        }
    }

    $delete_all_ranges = false;

    // if filter has ranges
    if ((!empty($filter_data['feature_type']) && strpos('ODN', $filter_data['feature_type']) !== false) || (!empty($filter_data['field_type']) && !empty($filter_fields[$filter_data['field_type']]['is_range']))) {

        $range_ids = array();
        foreach ($filter_data['ranges'] as $k => $range) {
            if (!empty($filter_data['feature_type']) && $filter_data['feature_type'] == 'D') {
                $range['to'] = fn_parse_date($filter_data['dates_ranges'][$k]['to']);
                $range['from'] = fn_parse_date($filter_data['dates_ranges'][$k]['from']);
            }

            $range['filter_id'] = $filter_id;
            if (!empty($filter_data['feature_id'])) {
                $range['feature_id'] = $filter_data['feature_id'];
            }

            if (!empty($range['range_id'])) {
                db_query("UPDATE ?:product_filter_ranges SET ?u WHERE range_id = ?i", $range, $range['range_id']);
                db_query('UPDATE ?:product_filter_ranges_descriptions SET ?u WHERE range_id = ?i AND lang_code = ?s', $range, $range['range_id'], $lang_code);

            } elseif ((!empty($range['from']) || !empty($range['to'])) && !empty($range['range_name'])) {
                $range['range_id'] = db_query("INSERT INTO ?:product_filter_ranges ?e", $range);
                foreach (fn_get_translation_languages() as $range['lang_code'] => $_d) {
                    db_query("INSERT INTO ?:product_filter_ranges_descriptions ?e", $range);
                }
            }

            if (!empty($range['range_id'])) {
                $range_ids[] = $range['range_id'];
            }
        }

        if (!empty($range_ids)) {
            $deleted_ranges = db_get_fields("SELECT range_id FROM ?:product_filter_ranges WHERE filter_id = ?i AND range_id NOT IN (?n)", $filter_id, $range_ids);
            if (!empty($deleted_ranges)) {
                db_query("DELETE FROM ?:product_filter_ranges WHERE range_id IN (?n)", $deleted_ranges);
                db_query("DELETE FROM ?:product_filter_ranges_descriptions WHERE range_id IN (?n)", $deleted_ranges);
            }
        } else {
            $delete_all_ranges = true;
        }
    } else {
        $delete_all_ranges = true;
    }

    if ($delete_all_ranges) {
        $deleted_ranges = db_get_fields("SELECT range_id FROM ?:product_filter_ranges WHERE filter_id = ?i", $filter_id);
        db_query("DELETE FROM ?:product_filter_ranges WHERE filter_id = ?i", $filter_id);
        db_query("DELETE FROM ?:product_filter_ranges_descriptions WHERE range_id IN (?n)", $deleted_ranges);
    }

    fn_set_hook('update_product_filter', $filter_data, $filter_id, $lang_code);

    return $filter_id;
}
