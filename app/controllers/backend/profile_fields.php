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

// ----------
// fields types:
// I - input
// T - textarea
// C - checkbox
// S - selectbox
// R - radiobutton
// H - header
// D - data
// P - phone
// --
// A - states
// O - country
// M - usergroup
// W - password
// N - address_type

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $_suffix = '.manage';

    if ($mode == 'update') {

        $field_data = $_REQUEST['field_data'];

        $field_id = fn_update_profile_field($field_data, $_REQUEST['field_id'], DESCR_SL);

        $_suffix = '.update?field_id=' . $field_id;
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['fields_data'])) {
            $fields_data = $_REQUEST['fields_data'];
            if (isset($fields_data['email'])) {
                foreach ($fields_data['email'] as $enable_for => $field_id) {
                    $fields_data[$field_id][$enable_for] = 'Y';
                }

                unset($fields_data['email']);
            }

            foreach ($fields_data as $field_id => $data) {
                fn_update_profile_field($data, $field_id, DESCR_SL);
            }
        }
    }

    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['field_ids'])) {
            foreach ($_REQUEST['field_ids'] as $field_id) {
                fn_delete_profile_field($field_id);
            }
        }

        if (!empty($_REQUEST['value_ids'])) {
            foreach ($_REQUEST['value_ids'] as $value_id) {
                db_query("DELETE FROM ?:profile_field_descriptions WHERE object_id = ?i AND object_type = 'V'", $value_id);
                db_query("DELETE FROM ?:profile_field_values WHERE value_id = ?i", $value_id);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, 'profile_fields' . $_suffix);
}

if ($mode == 'manage') {

    $profile_fields = fn_get_profile_fields('ALL', array(), DESCR_SL);

    Registry::get('view')->assign('profile_fields_areas', fn_profile_fields_areas());
    Registry::get('view')->assign('profile_fields', $profile_fields);

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['field_id'])) {
        fn_delete_profile_field($_REQUEST['field_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "profile_fields.manage");

} elseif ($mode == 'update' || $mode == 'add') {

    if ($mode == 'update') {
        $params['field_id'] = $_REQUEST['field_id'];
        $field = fn_get_profile_fields('ALL', array(), DESCR_SL, $params);

        Registry::get('view')->assign('field', $field);
    }

    Registry::get('view')->assign('profile_fields_areas', fn_profile_fields_areas());

}

// -------------- Functions ----------------
function fn_add_field_values($values = array(), $field_id = 0)
{
    if (empty($values) || empty($field_id)) {
        return false;
    }

    foreach ($values as $_v) {

        if (empty($_v['description'])) {
            continue;
        }
        // Insert main data
        $_v['field_id'] = $field_id;
        $value_id = db_query("INSERT INTO ?:profile_field_values ?e", $_v);

        // Insert descriptions
        $_data = array (
            'object_id' => $value_id,
            'object_type' => 'V',
            'description' => $_v['description'],
        );

        foreach (fn_get_translation_languages() as $_data['lang_code'] => $_v) {
            db_query("INSERT INTO ?:profile_field_descriptions ?e", $_data);
        }
    }

    return true;
}

function fn_delete_field_values($field_id, $value_ids = array())
{
    if (empty($value_ids)) {
        $value_ids = db_get_fields("SELECT value_id FROM ?:profile_field_values WHERE field_id = ?i", $field_id);
    }

    if (!empty($value_ids)) {
        db_query("DELETE FROM ?:profile_field_descriptions WHERE object_id IN (?n) AND object_type = 'V'", $value_ids);
        db_query("DELETE FROM ?:profile_field_values WHERE value_id IN (?n)", $value_ids);
    }
}

function fn_delete_profile_field($field_id, $no_match = false)
{
    $matching_id = db_get_field("SELECT matching_id FROM ?:profile_fields WHERE field_id = ?i", $field_id);
    if (!$no_match && !empty($matching_id)) {
        fn_delete_profile_field($matching_id, true);
    }

    fn_delete_field_values($field_id);
    db_query("DELETE FROM ?:profile_fields WHERE field_id = ?i", $field_id);
    db_query("DELETE FROM ?:profile_fields_data WHERE field_id = ?i", $field_id);
    db_query("DELETE FROM ?:profile_field_descriptions WHERE object_id = ?i AND object_type = 'F'", $field_id);
}

function fn_profile_fields_areas()
{
    $areas = array (
        'profile' => 'profile',
        'checkout' => 'checkout'
    );

    fn_set_hook('profile_fields_areas', $areas);

    return $areas;
}
