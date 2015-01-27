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

//
// Currently defined sections:
// A - menu
// ----------------------

//
// Defaults
//
$section = empty($_REQUEST['section']) ? 'N' : $_REQUEST['section'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $schema = fn_get_schema('static_data', 'schema');
    $section_data = $schema[$section];

    $redirect_url = "static_data.manage?section=$section";

    if (!empty($section_data['owner_object'])) {
        $param = $section_data['owner_object'];
        if (!empty($_REQUEST[$param['key']])) {
            $redirect_url .= "&" . $param['key'] . "=" . $_REQUEST[$param['key']];
        }
    }

    if ($mode == 'update') {
        fn_update_static_data($_REQUEST['static_data'], $_REQUEST['param_id'], $section);
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['static_data'])) {
            foreach ($_REQUEST['static_data'] as $k => $v) {
                $localizations = db_get_field("SELECT localization FROM ?:static_data WHERE section = ?s AND param_id = ?i", $section, $k);
                $v['localization'] = fn_explode_localizations($localizations);
                fn_update_static_data($v, $k, $section);
            }
        }
    }

    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['static_data_ids'])) {
            foreach ($_REQUEST['static_data_ids'] as $k => $v) {
                fn_delete_static_data($v);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, $redirect_url);
}

//
// Delete
//
if ($mode == 'delete') {
    fn_delete_static_data($_REQUEST['param_id']);

    return array(CONTROLLER_STATUS_OK, fn_url('static_data.manage?section=' . $_REQUEST['section'] . '&menu_id=' . $_REQUEST['menu_id']));

} elseif ($mode == 'update') {

    $schema = fn_get_schema('static_data', 'schema');
    $section_data = $schema[$section];
    Registry::get('view')->assign('section_data', $section_data);

    $static_data = db_get_row("SELECT sd.*, ?:static_data_descriptions.descr FROM ?:static_data AS sd LEFT JOIN ?:static_data_descriptions ON sd.param_id = ?:static_data_descriptions.param_id AND ?:static_data_descriptions.lang_code = ?s WHERE sd.section = ?s AND sd.param_id = ?i", DESCR_SL, $_REQUEST['section'], $_REQUEST['param_id']);

    if (!empty($section_data['icon'])) {
        $static_data['icon'] = fn_get_image_pairs($static_data['param_id'], $section_data['icon']['type'], 'M', true, true, DESCR_SL);
    }

    if (!empty($section_data['multi_level'])) {
        $params = array(
            'section' => $_REQUEST['section'],
            'generate_levels' => true,
            'get_params' => true,
            'multi_level' => true,
            'plain' => true,
        );
        Registry::get('view')->assign('parent_items', fn_get_static_data($params));
    }

    if (!empty($section_data['owner_object']['check_owner_function']) && function_exists($section_data['owner_object']['check_owner_function'])) {
        if ($section_data['owner_object']['check_owner_function']($_REQUEST[$section_data['owner_object']['key']]) == false) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
    }

    Registry::get('view')->assign('static_data', $static_data);

    Registry::get('view')->assign('section', $section);

} elseif ($mode == 'manage') {

    $schema = fn_get_schema('static_data', 'schema');
    $section_data = $schema[$section];
    Registry::get('view')->assign('section_data', $section_data);

    $params = array(
        'section' => $_REQUEST['section'],
        'multi_level' => !empty($section_data['multi_level']),
        'generate_levels' => !empty($section_data['multi_level']),
        'icon_name' => !empty($section_data['icon']) ? $section_data['icon']['type'] : '',
        'get_params' => true
    );
    $static_data = fn_get_static_data($params);

    if (!empty($section_data['multi_level'])) {
        $params = array(
            'section' => $_REQUEST['section'],
            'generate_levels' => true,
            'get_params' => true,
            'multi_level' => true,
            'plain' => true,
        );
        Registry::get('view')->assign('parent_items', fn_get_static_data($params));
    }

    if (!empty($section_data['owner_object']['name_function']) && function_exists($section_data['owner_object']['name_function'])) {
        Registry::get('view')->assign('owner_object_name', $section_data['owner_object']['name_function']($_REQUEST[$section_data['owner_object']['key']]));
    }

    if (!empty($section_data['owner_object']['check_owner_function']) && function_exists($section_data['owner_object']['check_owner_function'])) {
        if ($section_data['owner_object']['check_owner_function']($_REQUEST[$section_data['owner_object']['key']]) == false) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
    }

    Registry::get('view')->assign('static_data', $static_data);
    Registry::get('view')->assign('section', $section);
}

function fn_update_static_data($data, $param_id, $section, $lang_code = DESCR_SL)
{
    $current_id_path = '';
    $schema = fn_get_schema('static_data', 'schema');
    $section_data = $schema[$section];

    if (!empty($section_data['has_localization'])) {
        $data['localization'] = empty($data['localization']) ? '' : fn_implode_localizations($data['localization']);
    }

    if (!empty($data['megabox'])) { // parse megabox value
        foreach ($data['megabox']['type'] as $p => $v) {
            if (!empty($v)) {
                $data[$p] = $v . ':' . intval($data[$p][$v]) . ':' . $data['megabox']['use_item'][$p];
            } else {
                $data[$p] = '';
            }
        }
    }

    $condition = db_quote('param_id = ?i', $param_id);

    fn_set_hook('update_static_data', $data, $param_id, $condition, $section, $lang_code);

    if (!empty($param_id)) {
        $current_id_path = db_get_field("SELECT id_path FROM ?:static_data WHERE $condition");
        db_query("UPDATE ?:static_data SET ?u WHERE param_id = ?i", $data, $param_id);
        db_query('UPDATE ?:static_data_descriptions SET ?u WHERE param_id = ?i AND lang_code = ?s', $data, $param_id, $lang_code);
    } else {
        $data['section'] = $section;

        $param_id = $data['param_id'] = db_query("INSERT INTO ?:static_data ?e", $data);
        foreach (fn_get_translation_languages() as $data['lang_code'] => $_v) {
            db_query('REPLACE INTO ?:static_data_descriptions ?e', $data);
        }
    }

    // Generate ID path
    if (isset($data['parent_id'])) {
        if (!empty($data['parent_id'])) {
            $new_id_path = db_get_field("SELECT id_path FROM ?:static_data WHERE param_id = ?i", $data['parent_id']);
            $new_id_path .= '/' . $param_id;
        } else {
            $new_id_path = $param_id;
        }

        if (!empty($current_id_path) && $current_id_path != $new_id_path) {
            db_query("UPDATE ?:static_data SET id_path = CONCAT(?s, SUBSTRING(id_path, ?i)) WHERE id_path LIKE ?l", "$new_id_path/", strlen($current_id_path . '/') + 1, "$current_id_path/%");
        }
        db_query("UPDATE ?:static_data SET id_path = ?s WHERE param_id = ?i", $new_id_path, $param_id);
    }

    if (!empty($section_data['icon'])) {
        fn_attach_image_pairs('static_data_icon', $section_data['icon']['type'], $param_id, $lang_code);
    }

    return $param_id;
}

function fn_static_data_megabox($value)
{
    if (!empty($value)) {
        list($type, $id, $use_item) = explode(':', $value);

        return array(
            'types' => array(
                $type => array(
                    'value' => intval($id)
                ),
            ),
            'use_item' => $use_item,
        );
    } else {
        return array();
    }
}
