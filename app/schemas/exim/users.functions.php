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

function fn_import_set_default_value(&$import_data)
{
    foreach ($import_data as $key => $data) {
        $import_data[$key]['user_type'] = !empty($import_data[$key]['user_type']) ? $import_data[$key]['user_type'] : 'C';
    }

    return true;
}

function fn_import_set_user_company_id(&$import_data)
{
    foreach ($import_data as $key => $data) {
        if ((empty($import_data[$key]['user_type']) || $import_data[$key]['user_type'] != 'A') && !Registry::get('runtime.simple_ultimate')) {
            if (!Registry::get('runtime.company_id')) {
                $import_data[$key]['company_id'] = (!empty($import_data[$key]['company_id'])) ? fn_get_company_id_by_name($import_data[$key]['company_id']) : 0;
            } else {
                $import_data[$key]['company_id'] = Registry::get('runtime.company_id');
            }
        } else {
            $user = fn_exim_get_user_info($import_data[$key]['email']);

            if (!empty($user)) {
                $import_data[$key]['company_id'] = (int) $user['company_id'];
            } else {
                if (!Registry::get('runtime.company_id')) {
                    $import_data[$key]['company_id'] = (!empty($import_data[$key]['company_id'])) ? fn_get_company_id_by_name($import_data[$key]['company_id']) : 0;
                } else {
                    $import_data[$key]['company_id'] = Registry::get('runtime.company_id');
                }
            }
        }
    }

    return true;
}

function fn_set_allowed_company_ids(&$conditions)
{
    if (Registry::get('runtime.company_id') && !Registry::get('runtime.simple_ultimate')) {
        $company_customers_ids = implode(',', db_get_fields("SELECT user_id FROM ?:orders WHERE company_id = ?i", Registry::get('runtime.company_id')));
        if (Registry::get('settings.Stores.share_users') == 'Y' && !empty($company_customers_ids)) {
            $conditions[] = "(users.company_id = " . Registry::get('runtime.company_id') . " OR users.user_id IN ($company_customers_ids))";
        } else {
            $conditions[] = "users.company_id = " . Registry::get('runtime.company_id');
        }
    }
}

function fn_import_check_user_company_id(&$primary_object_id, &$object, &$processed_data, &$skip_record)
{
    if (!empty($primary_object_id)) {
        if (Registry::get('runtime.company_id') && !Registry::get('runtime.simple_ultimate')) {
            if (isset($primary_object_id['company_id']) && $primary_object_id['company_id'] != Registry::get('runtime.company_id')) {
                $processed_data['S']++;
                $skip_record = true;
            }
        } else {
            unset($object['company_id']);
        }
    } else {
        if (!Registry::get('runtime.company_id')) {
            if (!in_array($object['user_type'], array('A'))) {
                $object['company_id'] = isset($object['company_id']) ? $object['company_id'] : 0;
            }
        } else {
            $object['company_id'] = Registry::get('runtime.company_id');
        }
    }
}

function fn_exim_get_user_info($email)
{
    if (!empty($email)) {
        $user = db_get_row("SELECT company_id, is_root FROM ?:users WHERE email = ?s", $email);
    } else {
        $user = false;
    }

    return $user;
}

function fn_exim_process_password($user_data, $skip_record)
{
    $password = '';

    if (strlen($user_data['password']) == 32) {
        $password = $user_data['password'];
    } else {
        if (!isset($user_data['salt']) || empty($user_data['salt'])) {
            $password = md5($user_data['password']);
        } else {
            $password = fn_generate_salted_password($user_data['password'], $user_data['salt']);
        }
    }

    return $password;
}

function fn_exim_get_extra_fields($user_id, $lang_code = CART_LANGUAGE)
{
    $fields = array();

    $_user = db_get_hash_single_array("SELECT d.description, f.value FROM ?:profile_fields_data as f LEFT JOIN ?:profile_field_descriptions as d ON d.object_id = f.field_id AND d.object_type = 'F' AND d.lang_code = ?s WHERE f.object_id = ?i AND f.object_type = 'U'", array('description', 'value'), $lang_code, $user_id);

    $_profile = db_get_hash_single_array("SELECT d.description, f.value FROM ?:profile_fields_data as f LEFT JOIN ?:profile_field_descriptions as d ON d.object_id = f.field_id AND d.object_type = 'F' AND d.lang_code = ?s LEFT JOIN ?:user_profiles as p ON f.object_id = p.profile_id AND f.object_type = 'P' WHERE p.user_id = ?i", array('description', 'value'), $lang_code, $user_id);

    if (!empty($_user)) {
        $fields['user'] = $_user;
    }
    if (!empty($_profile)) {
        $fields['profile'] = $_profile;
    }

    if (!empty($fields)) {
        return fn_exim_json_encode($fields);
    }

    return '';
}

function fn_exim_set_extra_fields($data, $user_id)
{
    $data = json_decode($data, true);

    if (is_array($data) && !empty($data)) {
        foreach ($data as $type => $_data) {
            foreach ($_data as $field => $value) {
                // Check if field is exist
                $field_id = db_get_field("SELECT object_id FROM ?:profile_field_descriptions WHERE description = ?s AND object_type = 'F' LIMIT 1", $field);
                if (!empty($field_id)) {
                    $update = array(
                        'object_id' => (($type == 'user') ? $user_id : (db_get_field("SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i LIMIT 1", $user_id))),
                        'object_type' => (($type == 'user') ? 'U' : 'P'),
                        'field_id' => $field_id,
                        'value' => $value
                    );

                    db_query('REPLACE INTO ?:profile_fields_data ?e', $update);
                }
            }
        }

        return true;
    }

    return false;
}

if (!fn_allowed_for('ULTIMATE:FREE')) {
function fn_exim_get_usergroups($user_id)
{
    $pair_delimiter = ':';
    $set_delimiter = '; ';

    $result = array();
    $usergroups = db_get_array("SELECT usergroup_id, status FROM ?:usergroup_links WHERE user_id = ?i", $user_id);
    if (!empty($usergroups)) {
        foreach ($usergroups as $ug) {
            $result[] = $ug['usergroup_id'] . $pair_delimiter . $ug['status'];
        }
    }

    return !empty($result) ? implode($set_delimiter, $result) : '';
}

function fn_exim_set_usergroups($user_id, $data)
{
    $pair_delimiter = ':';
    $set_delimiter = '; ';

    db_query("DELETE FROM ?:usergroup_links WHERE user_id = ?i", $user_id);
    if (!empty($data)) {
        $usergroups = explode($set_delimiter, $data);
        if (!empty($usergroups)) {
            foreach ($usergroups as $ug) {
                $ug_data = explode($pair_delimiter, $ug);
                if (is_array($ug_data)) {
                    // Check if user group exists
                    $ug_id = db_get_field("SELECT usergroup_id FROM ?:usergroups WHERE usergroup_id = ?i", $ug_data[0]);
                    if (!empty($ug_id)) {
                        $_data = array(
                            'user_id' => $user_id,
                            'usergroup_id' => $ug_id,
                            'status' => $ug_data[1]
                        );
                        db_query('REPLACE INTO ?:usergroup_links ?e', $_data);
                    }
                }
            }
        }
    }

    return true;
}
}
