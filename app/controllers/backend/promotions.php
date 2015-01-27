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

$_REQUEST['promotion_id'] = empty($_REQUEST['promotion_id']) ? 0 : $_REQUEST['promotion_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    fn_trusted_vars('promotion_data', 'promotions');
    $suffix = '';

    //
    // Update promotion
    //
    if ($mode == 'update') {
        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            if (!empty($_REQUEST['promotion_id']) && !fn_check_company_id('promotions', 'promotion_id', $_REQUEST['promotion_id'])) {
                fn_company_access_denied_notification();

                return array(CONTROLLER_STATUS_OK, 'promotions.update?promotion_id=' . $_REQUEST['promotion_id']);
            }
            if (!empty($_REQUEST['promotion_id'])) {
                unset($_REQUEST['promotion_data']['company_id']);
            }
        }

        $promotion_id = fn_update_promotion($_REQUEST['promotion_data'], $_REQUEST['promotion_id'], DESCR_SL);

        $suffix = ".update?promotion_id=$promotion_id";
    }

    //
    // Delete selected promotions
    //
    if ($mode == 'm_delete') {

        if (!empty($_REQUEST['promotion_ids'])) {
            fn_delete_promotions($_REQUEST['promotion_ids']);
        }

        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, "promotions$suffix");
}

// ----------------------------- GET routines -------------------------------------------------

// promotion data
if ($mode == 'update') {

    Registry::set('navigation.tabs', array (
        'details' => array (
            'title' => __('general'),
            'href' => "promotions.update?promotion_id=$_REQUEST[promotion_id]&selected_section=details",
            'js' => true
        ),
        'conditions' => array (
            'title' => __('conditions'),
            'href' => "promotions.update?promotion_id=$_REQUEST[promotion_id]&selected_section=conditions",
            'js' => true
        ),
        'bonuses' => array (
            'title' => __('bonuses'),
            'href' => "promotions.update?promotion_id=$_REQUEST[promotion_id]&selected_section=bonuses",
            'js' => true
        ),
    ));

    $promotion_data = fn_get_promotion_data($_REQUEST['promotion_id']);

    if (empty($promotion_data)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    Registry::get('view')->assign('promotion_data', $promotion_data);

    Registry::get('view')->assign('zone', $promotion_data['zone']);
    Registry::get('view')->assign('schema', fn_promotion_get_schema());

    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
        Registry::get('view')->assign('picker_selected_companies', fn_ult_get_controller_shared_companies($_REQUEST['promotion_id']));
    }

// Add promotion
} elseif ($mode == 'add') {

    $zone = !empty($_REQUEST['zone']) ? $_REQUEST['zone'] : 'catalog';

    if (fn_allowed_for('ULTIMATE:FREE') && $zone == 'cart') {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    Registry::set('navigation.tabs', array (
        'details' => array (
            'title' => __('general'),
            'href' => "promotions.add?selected_section=details",
            'js' => true
        ),
        'conditions' => array (
            'title' => __('conditions'),
            'href' => "promotions.add?selected_section=conditions",
            'js' => true
        ),
        'bonuses' => array (
            'title' => __('bonuses'),
            'href' => "promotions.add?selected_section=bonuses",
            'js' => true
        ),
    ));

    Registry::get('view')->assign('zone', $zone);
    Registry::get('view')->assign('schema', fn_promotion_get_schema());

} elseif ($mode == 'dynamic') {
    Registry::get('view')->assign('schema', fn_promotion_get_schema());
    Registry::get('view')->assign('prefix', $_REQUEST['prefix']);
    Registry::get('view')->assign('elm_id', $_REQUEST['elm_id']);

    if (!empty($_REQUEST['zone'])) {
        Registry::get('view')->assign('zone', $_REQUEST['zone']);
    }

    if (!empty($_REQUEST['condition'])) {
        Registry::get('view')->assign('condition_data', array('condition' => $_REQUEST['condition']));

    } elseif (!empty($_REQUEST['bonus'])) {
        Registry::get('view')->assign('bonus_data', array('bonus' => $_REQUEST['bonus']));
    }

    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
        Registry::get('view')->assign('picker_selected_companies', fn_ult_get_controller_shared_companies($_REQUEST['promotion_id'], 'promotions', 'update'));
    }

// promotions list
} elseif ($mode == 'manage') {

    list($promotions, $search) = fn_get_promotions($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'), DESCR_SL);

    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('promotions', $promotions);

// Delete selected promotions
} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['promotion_id'])) {
        fn_delete_promotions($_REQUEST['promotion_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "promotions.manage");
}

function fn_update_promotion($data, $promotion_id, $lang_code = DESCR_SL)
{
    if (!empty($data['conditions']['conditions'])) {
        $data['conditions_hash'] = fn_promotion_serialize($data['conditions']['conditions']);
        $data['users_conditions_hash'] = fn_promotion_serialize_users_conditions($data['conditions']['conditions']);
    } else {
        $data['conditions_hash'] = $data['users_conditions_hash'] = '';
    }

    $data['conditions'] = empty($data['conditions']) ? array() : $data['conditions'];
    $data['bonuses'] = empty($data['bonuses']) ? array() : $data['bonuses'];

    fn_promotions_check_group_conditions($data['conditions']);

    if ($data['bonuses']) {
        foreach ($data['bonuses'] as $k => $v) {
            if (empty($v['bonus'])) {
                unset($data['bonuses'][$k]);
            }
        }
    }

    $data['conditions'] = serialize($data['conditions']);
    $data['bonuses'] = serialize($data['bonuses']);

    $from_date = $data['from_date'];
    $to_date = $data['to_date'];

    $data['from_date'] = !empty($from_date) ? fn_parse_date($from_date) : 0;
    $data['to_date'] = !empty($to_date) ? fn_parse_date($to_date, true) : 0;

    if (!empty($data['to_date']) && $data['to_date'] < $data['from_date']) { // protection from incorrect date range (special for isergi :))
        $data['from_date'] = fn_parse_date($to_date);
        $data['to_date'] = fn_parse_date($from_date, true);
    }

    if (!empty($promotion_id)) {
        db_query("UPDATE ?:promotions SET ?u WHERE promotion_id = ?i", $data, $promotion_id);
        db_query('UPDATE ?:promotion_descriptions SET ?u WHERE promotion_id = ?i AND lang_code = ?s', $data, $promotion_id, $lang_code);
    } else {
        $promotion_id = $data['promotion_id'] = db_query("REPLACE INTO ?:promotions ?e", $data);

        foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
            db_query("REPLACE INTO ?:promotion_descriptions ?e", $data);
        }
    }

    return $promotion_id;
}

function fn_promotions_check_group_conditions(&$conditions, $parents = array())
{
    static $schema = array();

    if (empty($schema)) {
        $schema = fn_promotion_get_schema();
    }

    if (!empty($conditions['set'])) {
        if (!empty($conditions['conditions'])) {
            $parents[] = array(
                'set_value' => $conditions['set_value'],
                'set' => $conditions['set']
            );

            fn_promotions_check_group_conditions($conditions['conditions'], $parents);
        }
    } else {
        foreach ($conditions as $k => $c) {
            if (!empty($c['conditions'])) {
                fn_promotions_check_group_conditions($conditions[$k]['conditions'], fn_array_merge($parents, array('set_value' => $c['set_value'], 'set' => $c['set']), false));

                if (!$c['conditions']) {
                    unset($c['conditions']);
                }
            } elseif (empty($c['condition']) || !isset($c['value'])) {
                unset($conditions[$k]);
            } elseif (!empty($schema['conditions'][$c['condition']]['applicability']['group'])) {
                foreach ($parents as $_c) {
                    if ($_c['set_value'] != $schema['conditions'][$c['condition']]['applicability']['group']['set_value']) {

                        fn_set_notification('W', __('warning'), __('warning_promotions_incorrect_condition', array(
                            '[condition]' => __('promotion_cond_' . $c['condition']),
                            '[set_value]' => __($schema['conditions'][$c['condition']]['applicability']['group']['set_value'] == true ? 'true': 'false')
                        )));
                        unset($conditions[$k]);
                    }
                }
            }
        }
    }
}
