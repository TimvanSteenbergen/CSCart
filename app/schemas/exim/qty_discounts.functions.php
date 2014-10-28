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

/**
 * The function checks if an entered percentage discount for the lower limit value equal to 1 to be greater than 0
 *
 * @param array $product_info Product information
 * @param string $lang_code 2-letter language code
 * @param bool $skip_record Skip or not current record
 * @return bool false if the record should be skipped or the "lower_limit" value of the currect record
 */
function fn_exim_check_discount($product_info, $lang_code, $skip_record)
{
    if (!isset($product_info['percentage_discount'])) {
        $product_info['percentage_discount'] = 0;
    }

    if (!isset($product_info['lower_limit'])) {
        $skip_record = true;
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (!isset($product_info['usergroup_id'])) {
            $skip_record = true;
        }

        $usergroup_id = fn_get_usergroup_id($product_info['usergroup_id'], $lang_code);
    }

    if ($product_info['lower_limit'] == 1 && $product_info['percentage_discount'] > 0) {
        if (!fn_allowed_for('ULTIMATE:FREE')) {
            if ($usergroup_id == 0) {
                $skip_record = true;
            }
        }

        if (fn_allowed_for('ULTIMATE:FREE')) {
            $skip_record = true;
        }
    }

    return ($skip_record) ? false : $product_info['lower_limit'];
}

if (!fn_allowed_for('ULTIMATE:FREE')) {
/**
 * The function gets usergroup id by usergroup name
 *
 * @param string $ug_name Usergroup name
 * @param string $lang_code 2-letter language code
 * @return int usergroup id
 */
function fn_get_usergroup_id($ug_name, $lang_code)
{
    $usergroup_id = db_get_field("SELECT usergroup_id FROM ?:usergroup_descriptions WHERE usergroup = ?s AND lang_code = ?s LIMIT 1", $ug_name, $lang_code);

    return !empty($usergroup_id) ? $usergroup_id : 0;
}

/**
 * The function gets usergroup name by usergroup id
 *
 * @param int $usergroup_id Usergroup id
 * @param string $lang_code 2-letter language code
 * @return string usergroup name
 */
function fn_exim_get_usergroup($usergroup_id, $lang_code = '')
{
    if ($usergroup_id < ALLOW_USERGROUP_ID_FROM) {
        $default_usergroups = fn_get_default_usergroups($lang_code);
        $usergroup = !empty($default_usergroups[$usergroup_id]['usergroup'])? $default_usergroups[$usergroup_id]['usergroup'] : '';
    } else {
        $usergroup = db_get_field("SELECT usergroup FROM ?:usergroup_descriptions WHERE usergroup_id = ?i AND lang_code = ?s", $usergroup_id, $lang_code);
    }

    return $usergroup;
}

/**
 * The function converts a user group name into a user group ID or creates a new user group if a user group specified in the import file does not exist
 *
 * @param string $usergroup Usergroup name presented in the file
 * @param string $lang_code 2-letter language code
 * @return int usergroup id
 */
function fn_exim_put_usergroup($usergroup, $lang_code)
{
    $default_usergroups = fn_get_default_usergroups($lang_code);
    foreach ($default_usergroups as $usergroup_id => $ug) {
        if ($ug['usergroup'] == $usergroup) {
            return $usergroup_id;
        }
    }

    $usergroup_id = fn_get_usergroup_id($usergroup, $lang_code);

    // Create new usergroup
    if (empty($usergroup_id)) {
        $_data = array(
            'type' => 'C', //customer
            'status' => 'A'
        );

        $usergroup_id = db_query("INSERT INTO ?:usergroups ?e", $_data);

        $_data = array(
            'usergroup_id' => $usergroup_id,
            'usergroup' => $usergroup,
        );

        foreach (fn_get_translation_languages() as $_data['lang_code'] => $v) {
            db_query("INSERT INTO ?:usergroup_descriptions ?e", $_data);
        }
    }

    return $usergroup_id;
}
}
