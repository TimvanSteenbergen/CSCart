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
use Tygh\Mailer;
use Tygh\Navigation\LastView;
use Tygh\Session;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Gets user info
 *
 * @param string $user_id User identifier
 * @param int $get_profile Gets profile with user or not
 * @param int $profile_id Prodile identifier to get
 * @return array User data
 */
function fn_get_user_info($user_id, $get_profile = true, &$profile_id = NULL)
{
    /**
     * Actions before getting user data
     *
     * @param string $user_id     User identifier
     * @param int    $get_profile Gets profile with user or not
     * @param int    $profile_id  Prodile identifier to get
     */
    fn_set_hook('get_user_info_pre', $user_id, $get_profile, $profile_id);

    $user_fields = array (
        '?:users.*',
    );

    $condition = ($user_id != $_SESSION['auth']['user_id']) ? fn_get_company_condition('?:users.company_id') : '';
    $join = '';

    /**
     * Prepare params for getting user info query
     *
     * @param string $condition   Query condition; it is treated as a WHERE clause
     * @param int    $user_id     User identifier
     * @param array  $user_fields Array of table column names to be returned
     */
    fn_set_hook('get_user_info_before', $condition, $user_id, $user_fields, $join);

    $user_fields = implode(',', $user_fields);
    $user_data = db_get_row("SELECT $user_fields FROM ?:users $join WHERE user_id = ?i $condition", $user_id);

    if (empty($user_data)) {
        return array();
    }

    $user_data['usergroups'] = fn_get_user_usergroups($user_id);

    if ($get_profile == true) {
        if (!empty($profile_id)) {
            $profile_data = db_get_row("SELECT * FROM ?:user_profiles WHERE user_id = ?i AND profile_id = ?i", $user_data['user_id'], $profile_id);
        }

        if (empty($profile_data)) {
            $profile_data = db_get_row("SELECT * FROM ?:user_profiles WHERE user_id = ?i AND profile_type = 'P'", $user_data['user_id']);
            $profile_id = $profile_data['profile_id'];
        }

        $user_data = fn_array_merge($user_data, $profile_data);
    }

    // Get additional fields
    $prof_cond = ($get_profile && !empty($profile_data['profile_id'])) ? db_quote("OR (object_id = ?i AND object_type = 'P')", $profile_data['profile_id']) : '';
    $additional_fields = db_get_hash_single_array("SELECT field_id, value FROM ?:profile_fields_data WHERE (object_id = ?i AND object_type = 'U') $prof_cond", array('field_id', 'value'), $user_id);

    $user_data['fields'] = $additional_fields;

    fn_add_user_data_descriptions($user_data);

    /**
     * Actions after getting user data
     *
     * @param string $user_id     User identifier
     * @param int    $get_profile Gets profile with user or not
     * @param int    $profile_id  Prodile identifier to get
     * @param array  $user_data   User data
     */
    fn_set_hook('get_user_info', $user_id, $get_profile, $profile_id, $user_data);

    return $user_data;
}

//
// Get user short info
//
function fn_get_user_short_info($user_id)
{
    return db_get_row("SELECT user_id, user_login, company_id, firstname, lastname, email, user_type FROM ?:users WHERE user_id = ?i", $user_id);
}

//
// Get user name
//
function fn_get_user_name($user_id)
{
    if (!empty($user_id)) {
        $user_data = db_get_row("SELECT firstname, lastname FROM ?:users WHERE user_id = ?i", $user_id);
        if (!empty($user_data)) {
            return $user_data['firstname'] . ' ' . $user_data['lastname'];
        }
    }

    return false;
}

/**
 * Get user data for API.
 *
 * @param string $email
 * @param string $api_key
 * @return array
 */
function fn_get_api_user($email, $api_key)
{
    return db_get_row('SELECT * FROM ?:users WHERE email = ?s AND api_key = ?s', $email, $api_key);
}

//
// Get all user profiles
//
function fn_get_user_profiles($user_id)
{
    $profiles = array();
    if (!empty($user_id)) {
        $profiles = db_get_array("SELECT profile_id, profile_type, profile_name FROM ?:user_profiles WHERE user_id = ?i", $user_id);
    }

    return $profiles;
}

/**
 * Checks if shipping and billing addresses are different
 *
 * @param array $profile_fields profile fields
 * @return bool true if different, false - otherwise
 */
function fn_check_shipping_billing($user_data, $profile_fields)
{
    if (empty($user_data)) {
        return false;
    }

    if (Registry::get('settings.General.address_position') == 'billing_first') {
        $first = 'S';
        $second = 'B';
    } else {
        $first = 'B';
        $second = 'S';
    }

    if (!empty($profile_fields[$second])) {
        foreach ($profile_fields[$second] as $v) {
            // Workaround for email field
            if ($v['field_name'] == 'email') {
                continue;
            }

            $id = !empty($v['field_name']) ? $v['field_name'] : $v['field_id'];
            $matching_id = !empty($profile_fields[$first][$v['matching_id']]) ? (!empty($v['field_name']) ? ($profile_fields[$first][$v['matching_id']]['field_name']) : $v['matching_id']) : 0;
            $udata = !empty($v['field_name']) ? $user_data : (!empty($user_data['fields']) ? $user_data['fields'] : array());

            // If field is set in shipping section and disabled in billing, so - different
            if ((!empty($udata[$id]) || (empty($udata[$id]) && !empty($v['required']) && $v['required'] == 'Y')) && empty($matching_id)) {
                return true;
            }

            // If field set in both sections and fields are different, so -
            if (isset($udata[$id]) && isset($udata[$matching_id]) && $udata[$id] != $udata[$matching_id]) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Compare fields in shipping and billing sections
 *
 * @param array $profile_fields profile fields
 * @return bool true if billing section contains all fields from shipping section, false - otherwise
 */
function fn_compare_shipping_billing($profile_fields)
{

    if (Registry::get('settings.General.address_position') == 'billing_first') {
        $from_section = 'B';
        $to_section = 'S';
    } else {
        $from_section = 'S';
        $to_section = 'B';
    }

    if (empty($profile_fields[$from_section]) || empty($profile_fields[$to_section])) {
        return false;
    }

    foreach ($profile_fields[$to_section] as $v) {
        // If field is set in shipping section and disabled in billing, so - different
        if (empty($profile_fields[$from_section][$v['matching_id']]) && $v['required'] == 'Y') {
            return false;

        } elseif (!empty($profile_fields[$from_section][$v['matching_id']]) && $v['required'] == 'Y' && $profile_fields[$from_section][$v['matching_id']]['required'] != 'Y') {
            return false;
        }
    }

    return true;
}

/**
 * Get all usergroups list
 *
 * @param string $type Type of usergroup (C - Customers, A - administrators)
 * @param string $lang_code Language for usergroup name
 * @return array of usergroups
 */
function fn_get_usergroups($type, $lang_code = CART_LANGUAGE)
{
    fn_set_hook('pre_get_usergroups', $type, $lang_code);

    $usergroups = array();

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (AREA == 'A') {
            $where = " a.status != 'D'";
        } else {
            $where = " a.status = 'A'";
        }

        if ($type == 'C' || AREA == 'C') {
            $where .= " AND a.type = 'C'";
        } elseif ($type == 'A') {
            $where .= " AND a.type = 'A'";
        }

        $usergroups = db_get_hash_array(
            "SELECT a.usergroup_id, a.status, a.type, b.usergroup"
            . " FROM ?:usergroups as a"
            . " LEFT JOIN ?:usergroup_descriptions as b ON b.usergroup_id = a.usergroup_id AND b.lang_code = ?s"
            . " WHERE $where ORDER BY usergroup",
            'usergroup_id', $lang_code
        );
    }

    fn_set_hook('post_get_usergroups', $usergroups, $type, $lang_code);

    return $usergroups;
}

function fn_get_default_usergroups($lang_code = CART_LANGUAGE)
{
    $default_usergroups = array(
        array(
            'usergroup_id' => USERGROUP_ALL,
            'status' => 'A',
            'type' => 'C',
            'usergroup' => __('all', '', $lang_code)
        ),
        array(
            'usergroup_id' => USERGROUP_GUEST,
            'status' => 'A',
            'type' => 'C',
            'usergroup' => __('guest', '', $lang_code)
        ),
        array(
            'usergroup_id' => USERGROUP_REGISTERED,
            'status' => 'A',
            'type' => 'C',
            'usergroup' => __('usergroup_registered', '', $lang_code)
        )
    );

    fn_set_hook('get_default_usergroups', $default_usergroups, $lang_code);

    return $default_usergroups;
}

/**
 * Get simple list of usergroups
 *
 * @param string $type Type of usergroups (C - customers, A - administrators)
 * @param bool $get_default If set, default usergroups will be returned too (all, guest, registred)
 * @param strging $lang_code 2-letters language code
 * @return array like usergroup_id => usergroup_name
 */
function fn_get_simple_usergroups($type, $get_default = false, $lang_code = CART_LANGUAGE)
{
    $usergroups = array();

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if ($get_default) {
            $default_usergroups = fn_get_default_usergroups($lang_code);
            foreach ($default_usergroups as $usergroup) {
                if ($usergroup['usergroup_id'] == USERGROUP_ALL) {
                    continue;
                }
                $usergroups[$usergroup['usergroup_id']] = $usergroup['usergroup'];
            }
        }

        $where = (AREA == 'C') ? " a.status = 'A'" : " a.status IN ('A', 'H')";

        if ($type == 'C' || AREA == 'C') {
            $where .= " AND a.type = 'C'";
        } elseif ($type == 'A') {
            $where .= " AND a.type = 'A'";
        }
        $_usergroups = db_get_hash_single_array(
            'SELECT a.usergroup_id, b.usergroup'
                . ' FROM ?:usergroups as a'
                . ' LEFT JOIN ?:usergroup_descriptions as b'
                . ' ON b.usergroup_id = a.usergroup_id AND b.lang_code = ?s'
                . ' WHERE ?p ORDER BY usergroup',
            array('usergroup_id', 'usergroup'),
            $lang_code,
            $where
        );
        $usergroups = $usergroups + $_usergroups;
    }

    return $usergroups;
}

//
// Get usergroup description
//
function fn_get_usergroup_name($id, $lang_code = CART_LANGUAGE)
{
    if (!empty($id)) {
        return db_get_field("SELECT usergroup FROM ?:usergroup_descriptions WHERE usergroup_id = ?i AND lang_code = ?s", $id, $lang_code);
    }

    return false;
}

function fn_add_user_data_descriptions(&$user_data, $lang_code = CART_LANGUAGE)
{
    fn_fill_user_fields($user_data);

    // Replace country and state values with their descriptions
    if (!empty($user_data['b_country'])) {
        $user_data['b_country_descr'] = fn_get_country_name($user_data['b_country'], $lang_code);
    }
    if (!empty($user_data['s_country'])) {
        $user_data['s_country_descr'] = fn_get_country_name($user_data['s_country'], $lang_code);
    }
    if (!empty($user_data['b_state'])) {
        $user_data['b_state_descr'] = fn_get_state_name($user_data['b_state'], $user_data['b_country'], $lang_code);
        if (empty($user_data['b_state_descr'])) {
            $user_data['b_state_descr'] = $user_data['b_state'];
        }
    }
    if (!empty($user_data['s_state'])) {
        $user_data['s_state_descr'] = fn_get_state_name($user_data['s_state'], $user_data['s_country'], $lang_code);
        if (empty($user_data['s_state_descr'])) {
            $user_data['s_state_descr'] = $user_data['s_state'];
        }
    }
}

function fn_fill_address(&$user_data, &$profile_fields, $use_default = false)
{
    if (Registry::get('settings.General.address_position') == 'billing_first' || $use_default) {
        $from = 'B';
        $to = 'S';
    } else {
        $from = 'S';
        $to = 'B';
    }

    if (!empty($profile_fields[$to])) {
        // Clean shipping/billing data
        foreach ($profile_fields[$to] as $field_id => $v) {
            if (!empty($v['matching_id']) && isset($profile_fields[$from][$v['matching_id']]['field_name'])) {
                if ($profile_fields[$from][$v['matching_id']]['field_name'] == $v['field_name']) {
                    continue;
                }
            }

            if (!empty($v['field_name'])) {
                if (empty($v['matching_id']) || (!empty($v['matching_id']) && isset($profile_fields[$from][$v['matching_id']]))) {
                    $user_data[$v['field_name']] = '';
                }
            } else {
                if (empty($v['matching_id']) || (!empty($v['matching_id']) && isset($profile_fields[$from][$v['matching_id']]))) {
                    $user_data['fields'][$v['field_id']] = '';
                }
            }
        }

        // Fill shipping/billing data with billing/shipping
        foreach ($profile_fields[$to] as $v) {
            if (isset($profile_fields[$from][$v['matching_id']])) {
                if (!empty($v['field_name']) && !empty($user_data[$profile_fields[$from][$v['matching_id']]['field_name']])) {
                    $user_data[$v['field_name']] = $user_data[$profile_fields[$from][$v['matching_id']]['field_name']];
                } elseif (isset($user_data['fields'][$profile_fields[$from][$v['matching_id']]['field_id']])) {
                    $user_data['fields'][$v['field_id']] = $user_data['fields'][$profile_fields[$from][$v['matching_id']]['field_id']];
                }
            }
        }
    }
}

function fn_fill_user_fields(&$user_data)
{
    $exclude = array(
        'user_login',
        'password',
        'user_type',
        'status',
        'cart_content',
        'timestamp',
        'referer',
        'last_login',
        'lang_code',
        'user_id',
        'profile_id',
        'profile_type',
        'profile_name',
        'tax_exempt',
        'salt',
        'company_id'
    );

    fn_set_hook('fill_user_fields', $exclude);

    $profile_fields = fn_get_table_fields('user_profiles', $exclude);
    $fields = fn_array_merge($profile_fields, fn_get_table_fields('users', $exclude), false);

    $fill = array(
        'b_firstname' => array('firstname', 's_firstname'),
        'b_lastname' => array('lastname', 's_lastname'),
        's_firstname' => array('b_firstname'),
        's_lastname' => array('b_lastname'),
        'firstname' => array('b_firstname', 's_firstname'),
        'lastname' => array('b_lastname', 's_lastname'),
    );

    foreach ($fill as $k => $v) {
        if (!isset($user_data[$k])) {
            @list($f, $s) = $v;
            $user_data[$k] = !empty($user_data[$f]) ? $user_data[$f] : (!empty($s) && !empty($user_data[$s]) ? $user_data[$s] : '');
        }
    }

    // Fill empty fields to avoid php notices
    foreach ($fields as $field) {
        if (empty($user_data[$field])) {
            $user_data[$field] = '';
        }
    }

    // Fill address with default data
    if (!fn_is_empty($user_data)) {
        $default = array(
            's_country' => 'default_country',
            'b_country' => 'default_country',
        );

        foreach ($default as $k => $v) {
            if (empty($user_data[$k])) {
                $user_data[$k] = Registry::get('settings.General.' . $v);
            }
        }
    }

    return true;
}

function fn_get_profile_fields($location = 'C', $_auth = array(), $lang_code = CART_LANGUAGE, $params = array())
{
    $auth = & $_SESSION['auth'];
    $select = '';

    if (empty($_auth)) {
        $_auth = $auth;
    }

    if (!empty($params['get_custom'])) {
        $condition = "WHERE ?:profile_fields.is_default = 'N' ";
    } else {
        $condition = "WHERE 1 ";
    }

    if (!empty($params['get_profile_required'])) {
        $condition .= "AND ?:profile_fields.profile_required = 'Y' ";
    }

    fn_set_hook('change_location', $location, $select, $condition, $params);

    if ($location == 'A' || $location == 'V' || $location == 'C') {
        $select .= ", ?:profile_fields.profile_required as required";
        $condition .= " AND ?:profile_fields.profile_show = 'Y'";
    } elseif ($location == 'O' || $location == 'I') {
        $select .= ", ?:profile_fields.checkout_required as required";
        $condition .= " AND ?:profile_fields.checkout_show = 'Y'";
    }

    if (!empty($params['field_id'])) {
        $condition .= db_quote(' AND ?:profile_fields.field_id = ?i', $params['field_id']);
    }

    fn_set_hook('get_profile_fields', $location, $select, $condition);

    // Determine whether to retrieve or not email field
    $skip_email_field = false;

    if ($location != 'I') {
        if (Registry::get('settings.General.use_email_as_login') == 'Y') {
            if ($location == 'O' && Registry::get('settings.General.disable_anonymous_checkout') == 'Y' && empty($_auth['user_id'])) {
                $skip_email_field = true;
            } elseif (strpos('APVC', $location) !== false) {
                $skip_email_field = true;
            }
        }
    }

    if ($skip_email_field) {
        $condition .= " AND ?:profile_fields.field_type != 'E'";
    }
    $profile_fields = db_get_hash_multi_array("SELECT ?:profile_fields.*, ?:profile_field_descriptions.description $select FROM ?:profile_fields LEFT JOIN ?:profile_field_descriptions ON ?:profile_field_descriptions.object_id = ?:profile_fields.field_id AND ?:profile_field_descriptions.object_type = 'F' AND lang_code = ?s $condition ORDER BY ?:profile_fields.position", array('section', 'field_id'), $lang_code);
    $matches = array();

    // Collect matching IDs
    if (!empty($profile_fields['S'])) {
        foreach ($profile_fields['S'] as $v) {
            $matches[$v['matching_id']] = $v['field_id'];
        }
    }

    $profile_fields['E'][] = array(
        'section' => 'E',
        'field_type' => 'I',
        'field_name' => 'email',
        'description' => __('email'),
        'required' => 'Y',
    );

    foreach ($profile_fields as $section => $fields) {
        foreach ($fields as $k => $v) {
            if ($v['field_type'] == 'S' || $v['field_type'] == 'R') {
                $_id = $v['field_id'];
                if ($section == 'B' && empty($v['field_name'])) {
                    // If this field is enabled in billing section
                    if (!empty($matches[$v['field_id']])) {
                        $_id = $matches[$v['field_id']];
                    // Otherwise, get it from database
                    } else {
                        $_id = db_get_field("SELECT field_id FROM ?:profile_fields WHERE matching_id = ?i", $v['field_id']);
                    }
                }
                $profile_fields[$section][$k]['values'] = db_get_hash_single_array("SELECT ?:profile_field_values.value_id, ?:profile_field_descriptions.description FROM ?:profile_field_values LEFT JOIN ?:profile_field_descriptions ON ?:profile_field_descriptions.object_id = ?:profile_field_values.value_id AND ?:profile_field_descriptions.object_type = 'V' AND ?:profile_field_descriptions.lang_code = ?s WHERE ?:profile_field_values.field_id = ?i ORDER BY ?:profile_field_values.position", array('value_id', 'description'), $lang_code, $_id);
            }
        }
    }

    if (!empty($params['field_id'])) {
        $result = reset($profile_fields);
        if (!empty($result[$params['field_id']])) {
            return $result[$params['field_id']];
        } else {
            return array();
        }
    }

    $sections = array(
        'C' => true,
        'B' => true,
        'S' => true,
        'E' => true
    );

    $sections = array_intersect_key($sections, $profile_fields);
    $profile_fields = array_merge($sections, $profile_fields);

    return $profile_fields;
}

function fn_store_profile_fields($user_data, $object_id, $object_type)
{
    // Delete existing fields
    if ($object_type == 'UP') {
        db_query("DELETE FROM ?:profile_fields_data WHERE (object_id = ?i AND object_type = ?s) OR (object_id = ?i AND object_type = ?s)", $object_id['U'], 'U', $object_id['P'], 'P');
    } else {
        db_query("DELETE FROM ?:profile_fields_data WHERE object_id = ?i AND object_type = ?s", $object_id, $object_type);
    }

    if (!empty($user_data['fields'])) {
        $fields_info = db_get_hash_array("SELECT field_id, field_type, section FROM ?:profile_fields WHERE field_id IN (?n)", 'field_id', array_keys($user_data['fields']));

        $_data = array ();
        foreach ($user_data['fields'] as $field_id => $value) {
            if ($object_type == 'UP') {
                $_data['object_type'] = ($fields_info[$field_id]['section'] == 'C') ? 'U' : 'P';
                $_data['object_id'] = ($fields_info[$field_id]['section'] == 'C') ? $object_id['U'] : $object_id['P'];
            } else {
                $_data['object_type'] = $object_type;
                $_data['object_id'] = $object_id;
            }
            $_data['field_id'] = $field_id;
            $_data['value'] = ($fields_info[$field_id]['field_type'] == 'D') ? fn_parse_date($value) : $value;

            db_query("REPLACE INTO ?:profile_fields_data ?e", $_data);
        }
    }

    return true;
}

//
// Fill auth array
//
function fn_fill_auth($user_data = array(), $order_ids = array(), $act_as_user = false, $area = AREA)
{
    $active_usergroups = fn_define_usergroups($user_data, $area);

    $_auth = array (
        'area' => !fn_check_user_type_admin_area($user_data) ? 'C' : 'A',
        'user_id' => empty($user_data['user_id']) ? 0 : $user_data['user_id'],
        'user_type' => !empty($user_data['user_type']) ? $user_data['user_type'] : 'C',
        'tax_exempt' => empty($user_data['tax_exempt']) ? 'N' : $user_data['tax_exempt'],
        'last_login' => empty($user_data['last_login']) ? 0 : $user_data['last_login'],
        'usergroup_ids' => $active_usergroups,
        'order_ids' => $order_ids,
        'act_as_user' => $act_as_user,
        'this_login' => TIME,
        'password_change_timestamp' => empty($user_data['password_change_timestamp']) ? 0 : $user_data['password_change_timestamp'],
        'company_id' => empty($user_data['company_id']) ? 0 : $user_data['company_id'],
        'is_root' => empty($user_data['is_root']) ? 'N' : $user_data['is_root'],
        'referer' => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '',
    );

    if (Registry::get('runtime.simple_ultimate')) {
        unset($_auth['company_id']);
    }

    fn_set_hook('fill_auth', $_auth, $user_data, $area);

    return $_auth;
}

function fn_define_usergroups($user_data = array(), $area = AREA)
{
    fn_set_hook('pre_define_usergroups', $user_data, $area);

    if (fn_allowed_for('ULTIMATE:FREE')) {
        $active_usergroups = ($area == 'A') ? array() : array(USERGROUP_ALL, empty($user_data['user_id']) ? USERGROUP_GUEST : USERGROUP_REGISTERED);
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        // Get usergroup info
        if (empty($user_data['usergroups']) && !empty($user_data['user_id'])) {
            $_active_usergroups = db_get_fields("SELECT lnk.usergroup_id FROM ?:usergroup_links as lnk INNER JOIN ?:usergroups ON ?:usergroups.usergroup_id = lnk.usergroup_id AND ?:usergroups.status != 'D' AND ?:usergroups.type = ?s WHERE lnk.user_id = ?i AND lnk.status = 'A'", $area, $user_data['user_id']);
        }

        $active_usergroups = ($area == 'A') ? array() : array(USERGROUP_ALL, empty($user_data['user_id']) ? USERGROUP_GUEST : USERGROUP_REGISTERED);

        if ($area == 'C' && !empty($user_data['user_id']) && $user_data['user_id'] == 1) {
            $active_usergroups[] = USERGROUP_GUEST;
        }

        if (!empty($user_data['usergroups'])) {
            foreach ($user_data['usergroups'] as $ug_data) {
                if ($ug_data['status'] == 'A' && $ug_data['type'] == $area) {
                    $active_usergroups[] = $ug_data['usergroup_id'];
                }
            }
        }
        if (!empty($_active_usergroups)) {
            $active_usergroups = array_merge($active_usergroups, $_active_usergroups);
            $active_usergroups = array_unique($active_usergroups);
        }
    }

    fn_set_hook('post_define_usergroups', $active_usergroups, $user_data, $area);

    return $active_usergroups;
}

//
// The function saves information into user_data table.
//
function fn_save_user_additional_data($type, $data, $user_id = 0)
{
    $auth = & $_SESSION['auth'];

    if (empty($user_id) && !empty($auth['user_id'])) {
        $user_id = $auth['user_id'];
    }

    if (empty($user_id)) {
        return false;
    }

    return db_query('REPLACE INTO ?:user_data ?e', array('user_id' => $user_id, 'type' => $type, 'data' => serialize($data)));
}

//
// The function returns information from user_data table.
//
function fn_get_user_additional_data($type, $user_id = 0)
{
    $auth = & $_SESSION['auth'];

    if (empty($user_id) && !empty($auth['user_id'])) {
        $user_id = $auth['user_id'];
    }

    if (empty($user_id)) {
        return false;
    }

    $data = db_get_field("SELECT data FROM ?:user_data WHERE user_id = ?i AND type = ?s", $user_id, $type);
    if (!empty($data)) {
        $data = unserialize($data);
    }

    return $data;
}

//
// The function returns description of user type.
//
function fn_get_user_type_description($type, $plural = false, $lang_code = CART_LANGUAGE)
{
    $type_descr = array(
        'S' => array(
            'C' => 'customer',
            'A' => 'administrator',
        ),
        'P' => array(
            'C' => 'customers',
            'A' => 'administrators',
        ),
    );

    fn_set_hook('get_user_type_description', $type_descr);

    $s = ($plural == true) ? 'P' : 'S';

    return __($type_descr[$s][$type], '', $lang_code);
}

/**
 * Getting users list
 *
 * @param  array  $params          Params list
 * @param  array  $auth            Auth
 * @param  int    $items_per_page  Items per page
 * @param  str    $custom_view     Custom view
 * @return array
 */
function fn_get_users($params, &$auth, $items_per_page = 0, $custom_view = '')
{
    /**
     * Actions before getting users list
     *
     * @param array $params         Params list
     * @param array $auth           Auth data
     * @param int   $items_per_page Items per page
     * @param str   $custom_view    Custom view
     */
    fn_set_hook('get_users_pre', $params, $auth, $items_per_page, $custom_view);

    // Init filter
    $_view = !empty($custom_view) ? $custom_view : 'users';
    $params = LastView::instance()->update($_view, $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array(
        "?:users.user_id",
        "?:users.user_login",
        "?:users.is_root",
        "?:users.timestamp",
        "?:users.user_type",
        "?:users.status",
        "?:users.firstname",
        "?:users.lastname",
        "?:users.email",
        "?:users.company",
        "?:users.company_id",
        "?:companies.company as company_name",
    );

    // Define sort fields
    $sortings = array(
        'id' => "?:users.user_id",
        'username' => "?:users.user_login",
        'email' => "?:users.email",
        'name' => array("?:users.lastname", "?:users.firstname"),
        'date' => "?:users.timestamp",
        'type' => "?:users.user_type",
        'status' => "?:users.status",
        'company' => "company_name",
    );

    if (isset($params['compact']) && $params['compact'] == 'Y') {
        $union_condition = ' OR ';
    } else {
        $union_condition = ' AND ';
    }

    $condition = array();
    $join = $group = '';

    $group .= " GROUP BY ?:users.user_id";

    if (isset($params['company']) && fn_string_not_empty($params['company'])) {
        $condition['company'] = db_quote(" AND ?:users.company LIKE ?l", "%".trim($params['company'])."%");
    }

    if (isset($params['name']) && fn_string_not_empty($params['name'])) {
        $arr = fn_explode(' ', $params['name']);
        foreach ($arr as $k => $v) {
            if (!fn_string_not_empty($v)) {
                unset($arr[$k]);
            }
        }
        if (sizeof($arr) == 2) {
            $condition['name'] = db_quote(" AND (?:users.firstname LIKE ?l AND ?:users.lastname LIKE ?l)",  "%".array_shift($arr)."%", "%".array_shift($arr)."%");
        } else {
            $condition['name'] = db_quote(" AND (?:users.firstname LIKE ?l OR ?:users.lastname LIKE ?l)", "%".trim($params['name'])."%", "%".trim($params['name'])."%");
        }
    }

    if (isset($params['user_login']) && fn_string_not_empty($params['user_login'])) {
        $condition['user_login'] = db_quote(" $union_condition ?:users.user_login LIKE ?l", "%".trim($params['user_login'])."%");
    }

    if (!empty($params['tax_exempt'])) {
        $condition['tax_exempt'] = db_quote(" AND ?:users.tax_exempt = ?s", $params['tax_exempt']);
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (isset($params['usergroup_id']) && $params['usergroup_id'] != ALL_USERGROUPS) {
            if (!empty($params['usergroup_id'])) {
                $join .= db_quote(" LEFT JOIN ?:usergroup_links ON ?:usergroup_links.user_id = ?:users.user_id AND ?:usergroup_links.usergroup_id = ?i", $params['usergroup_id']);
                $condition['usergroup_links'] = " AND ?:usergroup_links.status = 'A'";
            } else {
                $join .= " LEFT JOIN ?:usergroup_links ON ?:usergroup_links.user_id = ?:users.user_id AND ?:usergroup_links.status = 'A'";
                $condition['usergroup_links'] = " AND ?:usergroup_links.user_id IS NULL";
            }
        }
    }

    if (!empty($params['status'])) {
        $condition['status'] = db_quote(" AND ?:users.status = ?s", $params['status']);
    }

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $condition['email'] = db_quote(" $union_condition ?:users.email LIKE ?l", "%".trim($params['email'])."%");
    }

    if (isset($params['address']) && fn_string_not_empty($params['address'])) {
        $condition['address'] = db_quote(" AND (?:user_profiles.b_address LIKE ?l OR ?:user_profiles.s_address LIKE ?l)", "%".trim($params['address'])."%", "%".trim($params['address'])."%");
    }

    if (isset($params['zipcode']) && fn_string_not_empty($params['zipcode'])) {
        $condition['zipcode'] = db_quote(" AND (?:user_profiles.b_zipcode LIKE ?l OR ?:user_profiles.s_zipcode LIKE ?l)", "%".trim($params['zipcode'])."%", "%".trim($params['zipcode'])."%");
    }

    if (!empty($params['country'])) {
        $condition['country'] = db_quote(" AND (?:user_profiles.b_country LIKE ?l OR ?:user_profiles.s_country LIKE ?l)", "%$params[country]%", "%$params[country]%");
    }

    if (isset($params['state']) && fn_string_not_empty($params['state'])) {
        $condition['state'] = db_quote(" AND (?:user_profiles.b_state LIKE ?l OR ?:user_profiles.s_state LIKE ?l)", "%".trim($params['state'])."%", "%".trim($params['state'])."%");
    }

    if (isset($params['city']) && fn_string_not_empty($params['city'])) {
        $condition['city'] = db_quote(" AND (?:user_profiles.b_city LIKE ?l OR ?:user_profiles.s_city LIKE ?l)", "%".trim($params['city'])."%", "%".trim($params['city'])."%");
    }

    if (!empty($params['user_id'])) {
        $condition['user_id'] = db_quote(' AND ?:users.user_id IN (?n)', $params['user_id']);
    }

    if (!empty($params['p_ids']) || !empty($params['product_view_id'])) {
        $arr = (strpos($params['p_ids'], ',') !== false || !is_array($params['p_ids'])) ? explode(',', $params['p_ids']) : $params['p_ids'];
        if (empty($params['product_view_id'])) {
            $condition['order_product_id'] = db_quote(" AND ?:order_details.product_id IN (?n)", $arr);
        } else {
            $condition['order_product_id'] = db_quote(" AND ?:order_details.product_id IN (?n)", db_get_fields(fn_get_products(array('view_id' => $params['product_view_id'], 'get_query' => true))));
        }

        $join .= db_quote(" LEFT JOIN ?:orders ON ?:orders.user_id = ?:users.user_id AND ?:orders.is_parent_order != 'Y' LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id");
    }

    if (defined('RESTRICTED_ADMIN')) { // FIXME: NOT GOOD
        $condition['restricted_admin'] = db_quote(" AND ((?:users.user_type != 'A' AND ?:users.user_type != 'V') OR (?:users.user_type = 'A' AND ?:users.user_id = ?i))", $auth['user_id']);
    }

    // sometimes other vendor's admins could buy products from other vendors.
    if (!empty($params['user_type']) && (!($params['user_type'] == 'C' && Registry::get('runtime.company_id')) || fn_allowed_for('ULTIMATE'))) {
        $condition['user_type'] = db_quote(' AND ?:users.user_type = ?s', $params['user_type']);
    } else {

        // Get active user types
        $user_types = array_keys(fn_get_user_types());

        // Select only necessary groups frm all available
        if (!empty($params['user_types'])) {
            $user_types = array_intersect($user_types, $params['user_types']);
        }

        if (!empty($params['exclude_user_types'])) {
            $user_types = array_diff($user_types, $params['exclude_user_types']);
        }

        $condition['user_type'] = db_quote(" AND ?:users.user_type IN(?a)", $user_types);
    }

    $join .= db_quote(" LEFT JOIN ?:user_profiles ON ?:user_profiles.user_id = ?:users.user_id");

    $join .= db_quote(" LEFT JOIN ?:companies ON ?:companies.company_id = ?:users.company_id");

    /**
     * Prepare params for getting users query
     *
     * @param array $params    Params list
     * @param array $fields    Fields list
     * @param array $sortings  Sorting variants
     * @param array $condition Conditions set
     * @param str   $join      Joins list
     * @param array $auth      Auth data
     */
    fn_set_hook('get_users', $params, $fields, $sortings, $condition, $join, $auth);

    $sorting = db_sort($params, $sortings, 'name', 'asc');

    // Used for Extended search
    if (!empty($params['get_conditions'])) {
        return array($fields, $join, $condition);
    }

    // Paginate search results
    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:users.user_id)) FROM ?:users $join WHERE 1 ". implode(' ', $condition));
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $users = db_get_array("SELECT " . implode(', ', $fields) . " FROM ?:users $join WHERE 1" . implode('', $condition) . " $group $sorting $limit");

    LastView::instance()->processResults('users', $users, $params);

    /**
     * Actions after getting users list
     *
     * @param array $users  Users list
     * @param array $params Params list
     * @param array $auth   Auth data
     */
    fn_set_hook('get_users_post', $users, $params, $auth);

    return array($users, $params);
}

function fn_get_user_types()
{
    $types = array (
        'C' => 'add_customer',
        'A' => 'add_administrator',
    );

    fn_set_hook('get_user_types', $types);

    return $types;
}

function fn_get_user_edp($params, $items_per_page = 0)
{
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $fields = array (
        '?:order_details.product_id',
        '?:order_details.order_id',
        '?:order_details.extra',
        '?:orders.status',
        '?:products.unlimited_download',
        '?:product_descriptions.product'
    );

    $where = db_quote(' AND ?:orders.status != ?s', STATUS_INCOMPLETED_ORDER);
    $orders_company_condition = '';
    $limit = '';

    if (fn_allowed_for('ULTIMATE')) {
        $orders_company_condition = fn_get_company_condition('?:orders.company_id');
    }

    if (!empty($params['order_ids'])) {
        if (!is_array($params['order_ids'])) {
            $params['order_ids'] = array($params['order_ids']);
        }

        $where = db_quote(" AND ?:orders.order_id IN (?n)", $params['order_ids']);
    } elseif (!empty($params['items_per_page'])) {
        $params['total_items'] = count(db_get_fields(
            "SELECT COUNT(*)"
            . " FROM ?:order_details"
            . " INNER JOIN ?:orders ON ?:orders.order_id = ?:order_details.order_id AND ?:orders.is_parent_order != 'Y' $orders_company_condition"
            . " INNER JOIN ?:product_file_ekeys ON ?:product_file_ekeys.product_id = ?:order_details.product_id AND ?:product_file_ekeys.order_id = ?:order_details.order_id"
            . " INNER JOIN ?:product_files ON ?:product_files.product_id = ?:order_details.product_id"
            . " WHERE ?:orders.user_id = ?i AND ?:product_files.status = 'A'" . $where
            . " GROUP BY ?:order_details.product_id, ?:order_details.order_id",
            $params['user_id']
        ));
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $products = db_get_array(
        "SELECT " . implode(', ', $fields)
        . " FROM ?:order_details"
        . " INNER JOIN ?:product_files ON ?:product_files.product_id = ?:order_details.product_id"
        . " INNER JOIN ?:orders ON ?:orders.order_id = ?:order_details.order_id AND ?:orders.is_parent_order != 'Y' $orders_company_condition"
        . " INNER JOIN ?:product_file_ekeys ON ?:product_file_ekeys.product_id = ?:order_details.product_id AND ?:product_file_ekeys.order_id = ?:order_details.order_id"
        . " LEFT JOIN ?:products ON ?:products.product_id = ?:order_details.product_id"
        . " LEFT JOIN ?:product_descriptions ON ?:product_descriptions.product_id = ?:order_details.product_id AND ?:product_descriptions.lang_code = ?s"
        . " LEFT JOIN ?:product_file_folders ON ?:product_file_folders.product_id = ?:order_details.product_id"
        . " WHERE ?:orders.user_id = ?i AND ?:orders.is_parent_order != 'Y' AND ?:product_files.status = 'A' AND (?:product_file_folders.status = 'A' OR ?:product_files.folder_id IS NULL)" . $where
        . " GROUP BY ?:order_details.order_id, ?:order_details.product_id"
        . " ORDER BY ?:orders.order_id DESC $limit",
        CART_LANGUAGE, $params['user_id']
    );

    if (!empty($products)) {
        foreach ($products as $k => $v) {
            $_params = array (
                'product_id' => $v['product_id'],
                'order_id' => $v['order_id']
            );
            list($product_file_folders) = fn_get_product_file_folders($_params);
            list($product_files) = fn_get_product_files($_params);
            $products[$k]['files_tree'] = fn_build_files_tree($product_file_folders, $product_files);
        }
    }

    return array($products, $params);
}

function fn_is_restricted_admin($params)
{
    if (!defined('RESTRICTED_ADMIN')) {
        return false;
    }

    $auth = & $_SESSION['auth'];

    $not_own_profile = false;
    $is_restricted = false;

    fn_set_hook('is_restricted_admin', $params, $is_restricted);

    if ($is_restricted) {
        return true;
    }

    if (!empty($params['user_id']) && $params['user_id'] != $auth['user_id']) {
        $requested_utype = db_get_field("SELECT user_type FROM ?:users WHERE user_id = ?i", $params['user_id']);
        if (in_array($requested_utype, array('A', 'V'))) {
            return true;
        }
        $not_own_profile = true;
    } elseif (empty($params['user_id'])) {
        $not_own_profile = true;
    }

    $user_type = isset($params['user_data']['user_type']) ? $params['user_data']['user_type'] : (isset($params['user_type']) ? $params['user_type'] : '');
    if ($not_own_profile && fn_check_user_type_admin_area($user_type)) {
        return true;
    }

    return false;
}

/**
 * This function initializes the session data of a selected user (cart, wishlist etc...)
 *
 * @param array $sess_data
 * @param int $user_id
 * @return true
 */

function fn_init_user_session_data(&$sess_data, $user_id, $skip_cart_saving = false)
{
    // Restore cart content
    $sess_data['cart'] = empty($sess_data['cart']) ? array() : $sess_data['cart'];

    // Cleanup cached shipping rates
    unset($sess_data['shipping_rates']);

    fn_extract_cart_content($sess_data['cart'], $user_id, 'C');
    $sess_data['cart']['user_data'] = fn_get_user_info($user_id);

    $sess_data['product_notifications'] = array (
        'email' => !empty($sess_data['cart']['user_data']['email']) ? $sess_data['cart']['user_data']['email'] : '',
        'product_ids' => db_get_fields("SELECT product_id FROM ?:product_subscriptions WHERE user_id = ?i", $user_id)
    );

    if (!$skip_cart_saving) {
        fn_save_cart_content($sess_data['cart'], $user_id);
    }

    fn_set_hook('init_user_session_data', $sess_data, $user_id);

    return true;
}

/**
 * Generate a random user password
 *
 * @param int $length - supposed lenght of the password
 * @return string - the password generated
 */
function fn_generate_password($length = USER_PASSWORD_LENGTH)
{
    $chars = 'abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $nums = '1234567890';
    $i = 0;
    $password = '';
    while ($i < $length) {
        if ($i%2) {
            $password .= $chars {
                mt_rand(0, strlen($chars) - 1)
            };
        } else {
            $password .= $nums {
                mt_rand(0, strlen($nums) - 1)
            };
        }
        $i++;
    }

    return $password;
}

/**
 * Restores password, password1 and password2 with clear POST data.
 * Example: password1 = "Some<secret>password"
 *     Processed before $_REQUEST['user_data']['password1'] = 'Somepassword'
 *     Returned value $_REQUEST['user_data']['password1'] = 'Some<secret>password'
 *
 * @param array &$destination $_REQUEST
 * @param array &$source $_POST
 */
function fn_restore_processed_user_password(&$destination, &$source)
{
    $fields = array(
        'password', 'password1', 'password2'
    );

    foreach ($fields as $field) {
        if (isset($source[$field])) {
            $destination[$field] = $source[$field];
        }
    }
}

/**
 * Add/update user
 *
 * @param int $user_id - user ID to update (empty for new user)
 * @param array $user_data - user data
 * @param array $auth - authentication information
 * @param bool $ship_to_another - flag indicates that shipping and billing fields are different
 * @param bool $notify_user - flag indicates that user should be notified
 * @param bool $send_password - TRUE if the password should be included into the e-mail
 * @return array with user ID and profile ID if success, false otherwise
 */
function fn_update_user($user_id, $user_data, &$auth, $ship_to_another, $notify_user, $send_password = false)
{
    /**
     * Actions before updating user
     *
     * @param int   $user_id         User ID to update (empty for new user)
     * @param array $user_data       User data
     * @param array $auth            Authentication information
     * @param bool  $ship_to_another Flag indicates that shipping and billing fields are different
     * @param bool  $notify_user     Flag indicates that user should be notified
     * @param bool  $send_password   TRUE if the password should be included into the e-mail
     */
    fn_set_hook('update_user_pre', $user_id, $user_data, $auth, $ship_to_another, $notify_user, $send_password);

    array_walk($user_data, 'fn_trim_helper');
    $register_at_checkout = isset($user_data['register_at_checkout']) && $user_data['register_at_checkout'] == 'Y' ? true : false;

    if (fn_allowed_for('ULTIMATE')) {
        if (AREA == 'A' && !empty($user_data['user_type']) && $user_data['user_type'] == 'C' && (empty($user_data['company_id']) || (Registry::get('runtime.company_id') &&  $user_data['company_id'] != Registry::get('runtime.company_id')))) {
            fn_set_notification('W', __('warning'), __('access_denied'));

            return false;
        }
    }

    if (!empty($user_id)) {
        $current_user_data = db_get_row("SELECT user_id, company_id, is_root, status, user_type, user_login, lang_code, password, salt, last_passwords FROM ?:users WHERE user_id = ?i", $user_id);

        if (empty($current_user_data)) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('user'))),'','404');

            return false;
        }

        if (!fn_check_editable_permissions($auth, $current_user_data)) {
            fn_set_notification('E', __('error'), __('access_denied'));

            return false;
        }

        if (!empty($user_data['profile_id']) && AREA != 'A') {
            $profile_ids = db_get_fields("SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i", $user_id);
            if (!in_array($user_data['profile_id'], $profile_ids)) {
                fn_set_notification('W', __('warning'), __('access_denied'));

                return false;
            }
        }

        if (fn_allowed_for('ULTIMATE')) {
            if (AREA != 'A' || empty($user_data['company_id'])) {
                //we should set company_id for the frontdend, in the backend company_id received from form
                if ($current_user_data['user_type'] == 'A') {
                    if (!isset($user_data['company_id']) || AREA != 'A' || Registry::get('runtime.company_id')) {
                        // reset administrator's company if it was not set to root
                        $user_data['company_id'] = $current_user_data['company_id'];
                    }
                } elseif (Registry::get('settings.Stores.share_users') == 'Y') {
                    $user_data['company_id'] = $current_user_data['company_id'];
                } else {
                    $user_data['company_id'] = Registry::ifGet('runtime.company_id', 1);
                }
            }
        }

        if (fn_allowed_for('MULTIVENDOR')) {
            if (AREA != 'A') {
                //we should set company_id for the frontend
                $user_data['company_id'] = $current_user_data['company_id'];
            }
        }

        $action = 'update';
    } else {
        $current_user_data = array(
            'status' => (AREA != 'A' && Registry::get('settings.General.approve_user_profiles') == 'Y') ? 'D' : (!empty($user_data['status']) ? $user_data['status'] : 'A'),
            'user_type' => 'C', // FIXME?
        );

        if (fn_allowed_for('ULTIMATE')) {
            if (!empty($user_data['company_id']) || Registry::get('runtime.company_id') || AREA == 'A') {
                //company_id can be received when we create user account from the backend
                $company_id = !empty($user_data['company_id']) ? $user_data['company_id'] : Registry::get('runtime.company_id');
                if (empty($company_id)) {
                    $company_id = fn_check_user_type_admin_area($user_data['user_type']) ? $user_data['company_id'] : fn_get_default_company_id();
                }
                $user_data['company_id'] = $current_user_data['company_id'] = $company_id;
            } else {
                fn_set_notification('W', __('warning'), __('access_denied'));

                return false;
            }
        }

        $action = 'add';

        $user_data['lang_code'] = !empty($user_data['lang_code']) ? $user_data['lang_code'] : CART_LANGUAGE;
        $user_data['timestamp'] = TIME;
    }

    $original_password = '';
    $current_user_data['password'] = !empty($current_user_data['password']) ? $current_user_data['password'] : '';
    $current_user_data['salt'] = !empty($current_user_data['salt']) ? $current_user_data['salt'] : '';

    // Set the user type
    $user_data['user_type'] = fn_check_user_type($user_data, $current_user_data);

    if (
        Registry::get('runtime.company_id')
        && !fn_allowed_for('ULTIMATE')
        && (
            !fn_check_user_type_admin_area($user_data['user_type'])
            || (
                isset($current_user_data['company_id'])
                && $current_user_data['company_id'] != Registry::get('runtime.company_id')
            )
        )
    ) {
        fn_set_notification('W', __('warning'), __('access_denied'));

        return false;
    }

    // Check if this user needs login/password
    if (fn_user_need_login($user_data['user_type'])) {
        // Check if user_login already exists
        // FIXME
        if (!isset($user_data['email'])) {
            $user_data['email'] = db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $user_id);
        }

        $is_exist = fn_is_user_exists($user_id, $user_data);

        if ($is_exist) {
            fn_set_notification('E', __('error'), __('error_user_exists'), '', 'user_exist');

            return false;
        }

        // Check the passwords
        if (!empty($user_data['password1']) || !empty($user_data['password2'])) {
            $original_password = trim($user_data['password1']);
            $user_data['password1'] = !empty($user_data['password1']) ? trim($user_data['password1']) : '';
            $user_data['password2'] = !empty($user_data['password2']) ? trim($user_data['password2']) : '';
        }

        // if the passwords are not set and this is not a forced password check
        // we will not update password, otherwise let's check password
        if (!empty($_SESSION['auth']['forced_password_change']) || !empty($user_data['password1']) || !empty($user_data['password2'])) {

            $valid_passwords = true;

            if ($user_data['password1'] != $user_data['password2']) {
                $valid_passwords = false;
                fn_set_notification('E', __('error'), __('error_passwords_dont_match'));
            }

            // PCI DSS Compliance
            if (fn_check_user_type_admin_area($user_data['user_type'])) {

                $msg = array();
                // Check password length
                $min_length = Registry::get('settings.Security.min_admin_password_length');
                if (strlen($user_data['password1']) < $min_length || strlen($user_data['password2']) < $min_length) {
                    $valid_passwords = false;
                    $msg[] = str_replace("[number]", $min_length, __('error_password_min_symbols'));
                }

                // Check password content
                if (Registry::get('settings.Security.admin_passwords_must_contain_mix') == 'Y') {
                    $tmp_result = preg_match('/\d+/', $user_data['password1']) && preg_match('/\D+/', $user_data['password1']) && preg_match('/\d+/', $user_data['password2']) && preg_match('/\D+/', $user_data['password2']);
                    if (!$tmp_result) {
                        $valid_passwords = false;
                        $msg[] = __('error_password_content');
                    }
                }

                if ($msg) {
                    fn_set_notification('E', __('error'), implode('<br />', $msg));
                }

                // Check last 4 passwords
                if (!empty($user_id)) {
                    $prev_passwords = !empty($current_user_data['last_passwords']) ? explode(',', $current_user_data['last_passwords']) : array();

                    if (!empty($_SESSION['auth']['forced_password_change'])) {
                        // if forced password change - new password can't be equal to current password.
                        $prev_passwords[] = $current_user_data['password'];
                    }

                    if (in_array(fn_generate_salted_password($user_data['password1'], $current_user_data['salt']), $prev_passwords)) {
                        $valid_passwords = false;
                        fn_set_notification('E', __('error'), __('error_password_was_used'));
                    } else {
                        if (count($prev_passwords) >= 5) {
                            array_shift($prev_passwords);
                        }
                        $user_data['last_passwords'] = implode(',', $prev_passwords);
                    }
                }
            } // PCI DSS Compliance

            if (!$valid_passwords) {
                return false;
            }

            $user_data['salt'] = fn_generate_salt();
            $user_data['password'] = fn_generate_salted_password($user_data['password1'], $user_data['salt']);
            if ($user_data['password'] != $current_user_data['password'] && !empty($user_id)) {
                // if user set current password - there is no necessity to update password_change_timestamp
                $user_data['password_change_timestamp'] = $_SESSION['auth']['password_change_timestamp'] = TIME;
            }
            unset($_SESSION['auth']['forced_password_change']);
            fn_delete_notification('password_expire');

        }
    }

    $user_data['status'] = (AREA != 'A' || empty($user_data['status'])) ? $current_user_data['status'] : $user_data['status']; // only administrator can change user status

    // Fill the firstname, lastname and phone from the billing address if the profile was created or updated through the admin area.
    if (AREA == 'A' || Registry::get('settings.General.address_position') == 'billing_first') {
        $main_address_zone = BILLING_ADDRESS_PREFIX;
        $alt_address_zone = SHIPPING_ADDRESS_PREFIX;
    } else {
        $main_address_zone = SHIPPING_ADDRESS_PREFIX;
        $alt_address_zone = BILLING_ADDRESS_PREFIX;
    }

    $user_data = fn_fill_contact_info_from_address($user_data, $main_address_zone, $alt_address_zone);

    if (!fn_allowed_for('ULTIMATE')) {
        //for ult company_id was set before
        fn_set_company_id($user_data);
    }

    if (!empty($current_user_data['is_root']) && $current_user_data['is_root'] == 'Y') {
        $user_data['is_root'] = 'Y';
    } else {
        $user_data['is_root'] = 'N';
    }

    // check if it is a root admin
    $is_root_admin_exists = db_get_field(
        "SELECT user_id FROM ?:users WHERE company_id = ?i AND is_root = 'Y' AND user_id != ?i",
        $user_data['company_id'], !empty($user_id) ? $user_id : 0
    );
    $user_data['is_root'] = empty($is_root_admin_exists) && $user_data['user_type'] !== 'C' ? 'Y' : 'N';

    unset($user_data['user_id']);

    if (!empty($user_id)) {
        db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", $user_data, $user_id);

        fn_clean_usergroup_links($user_id, $current_user_data['user_type'], $user_data['user_type']);

        fn_log_event('users', 'update', array(
            'user_id' => $user_id,
        ));
    } else {
        if (!isset($user_data['password_change_timestamp'])) {
            $user_data['password_change_timestamp'] = 1;
        }

        $user_id = db_query("INSERT INTO ?:users ?e" , $user_data);

        fn_log_event('users', 'create', array(
            'user_id' => $user_id,
        ));
    }
    $user_data['user_id'] = $user_id;

    // Set/delete insecure password notification
    if (AREA == 'A' && Registry::get('config.demo_mode') != true && !empty($user_data['password1'])) {
        if (!fn_compare_login_password($user_data, $user_data['password1'])) {
            fn_delete_notification('insecure_password');
        } else {

            $lang_var = 'warning_insecure_password';
            if (Registry::get('settings.General.use_email_as_login') == 'Y') {
                $lang_var = 'warning_insecure_password_email';
            }

            fn_set_notification('E', __('warning'), __($lang_var, array(
                '[link]' => fn_url("profiles.update?user_id=" . $user_id)
            )), 'K', 'insecure_password');
        }
    }

    if (empty($user_data['user_login'])) { // if we're using email as login or user type does not require login, fill login field
        db_query("UPDATE ?:users SET user_login = 'user_?i' WHERE user_id = ?i AND user_login = ''", $user_id, $user_id);
    }

    // Fill shipping info with billing if needed
    if (empty($ship_to_another)) {
        $profile_fields = fn_get_profile_fields($user_data['user_type']);
        $use_default = (AREA == 'A') ? true : false;
        fn_fill_address($user_data, $profile_fields, $use_default);
    }

    $user_data['profile_id'] = fn_update_user_profile($user_id, $user_data, $action);

    $user_data = fn_get_user_info($user_id, true, $user_data['profile_id']);

    if ($register_at_checkout) {
        $user_data['register_at_checkout'] = 'Y';
    }
    $lang_code = (AREA == 'A' && !empty($user_data['lang_code'])) ? $user_data['lang_code'] : CART_LANGUAGE;

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $user_data['usergroups'] = db_get_hash_array(
            "SELECT lnk.link_id, lnk.usergroup_id, lnk.status, a.type, b.usergroup"
            . " FROM ?:usergroup_links as lnk"
            . " INNER JOIN ?:usergroups as a ON a.usergroup_id = lnk.usergroup_id AND a.status != 'D'"
            . " LEFT JOIN ?:usergroup_descriptions as b ON b.usergroup_id = a.usergroup_id AND b.lang_code = ?s"
            . " WHERE a.status = 'A' AND lnk.user_id = ?i AND lnk.status != 'D' AND lnk.status != 'F'"
            , 'usergroup_id', $lang_code, $user_id
        );
    }

    // Send notifications to customer
    if (!empty($notify_user)) {
        $from = 'company_users_department';

        if (fn_allowed_for('MULTIVENDOR')) {
            // Vendor administrator's notification
            // is sent from root users department
            if ($user_data['user_type'] == 'V') {
                $from = 'default_company_users_department';
            }
        }

        // Notify customer about profile activation (when update profile only)
        if ($action == 'update' && $current_user_data['status'] === 'D' && $user_data['status'] === 'A') {
            Mailer::sendMail(array(
                'to' => $user_data['email'],
                'from' => $from,
                'data' => array(
                    'password' => $original_password,
                    'send_password' => $send_password,
                    'user_data' => $user_data
                ),
                'tpl' => 'profiles/profile_activated.tpl',
                'company_id' => $user_data['company_id']
            ), fn_check_user_type_admin_area($user_data['user_type']) ? 'A' : 'C', $lang_code);
        }

        // Notify customer about profile add/update
        $prefix = ($action == 'add') ? 'create' : 'update';

        Mailer::sendMail(array(
            'to' => $user_data['email'],
            'from' => $from,
            'data' => array(
                'password' => $original_password,
                'send_password' => $send_password,
                'user_data' => $user_data,
            ),
            'tpl' => 'profiles/' . $prefix . '_profile.tpl',
            'company_id' => $user_data['company_id']
        ), fn_check_user_type_admin_area($user_data['user_type']) ? 'A' : 'C', $lang_code);
    }

    if ($action == 'add') {

        $skip_auth = AREA != 'C' ? true : false;

        if (AREA != 'A') {
            if (Registry::get('settings.General.approve_user_profiles') == 'Y') {
                fn_set_notification('W', __('important'), __('text_profile_should_be_approved'));

                // Notify administrator about new profile
                Mailer::sendMail(array(
                    'to' => 'company_users_department',
                    'from' => 'company_users_department',
                    'reply_to' => $user_data['email'],
                    'data' => array(
                        'user_data' => $user_data,
                    ),
                    'tpl' => 'profiles/activate_profile.tpl',
                    'company_id' => $user_data['company_id']
                ), 'A', Registry::get('settings.Appearance.backend_default_language'));

                $skip_auth = true;
            } else {
                fn_set_notification('N', __('information'), __('text_profile_is_created'));
            }
        }

        if (!is_null($auth)) {
            if (!empty($auth['order_ids'])) {
                db_query("UPDATE ?:orders SET user_id = ?i WHERE order_id IN (?n)", $user_id, $auth['order_ids']);
            }

            if (empty($skip_auth)) {
                $auth = fn_fill_auth($user_data);
            }
        }
    } else {
        if (AREA == 'C') {
            fn_set_notification('N', __('information'), __('text_profile_is_updated'));
        }
    }

    fn_set_hook('update_profile', $action, $user_data, $current_user_data);

    return array($user_id, !empty($user_data['profile_id']) ? $user_data['profile_id'] : false);
}

/**
 * Cleans usergroup links if usertype was changed
 *
 * @param int $user_id User identifier
 * @param string $current_user_type Current user type
 * @param string $new_user_type New user type
 * @return bool True on success, false otherwise
 */
function fn_clean_usergroup_links($user_id, $current_user_type, $new_user_type)
{
    if ($current_user_type != $new_user_type) {
        $usergroup_links = db_get_fields(
            "SELECT ?:usergroup_links.link_id "
                . "FROM ?:usergroups JOIN ?:usergroup_links ON ?:usergroups.usergroup_id = ?:usergroup_links.usergroup_id "
            ."WHERE ?:usergroup_links.user_id = ?i AND ?:usergroup_links.status = 'A' AND ?:usergroups.type = ?s",
            $user_id, in_array($new_user_type, array('A', 'V')) ? 'C' : 'A'
        );

        if (!empty($usergroup_links)) {
            db_query("DELETE FROM ?:usergroup_links WHERE link_id IN (?a)", $usergroup_links);

            return true;
        }
    }

    return false;
}

/**
 * Updates profile data of registered user
 *
 * @param int $user_id User identifier
 * @param array $user_data Profile information
 * @param string $action Current action (Example: 'add')
 * @return int profile ID
 */
function fn_update_user_profile($user_id, $user_data, $action = '')
{
    /**
     * Modify profile data of registered user
     *
     * @param int    $user_id   User identifier
     * @param array  $user_data Profile information
     * @param string $action    Current action (Example: 'add')
     */
    fn_set_hook('update_user_profile_pre', $user_id, $user_data, $action);

    // Add new profile or update existing
    if ((isset($user_data['profile_id']) && empty($user_data['profile_id'])) || $action == 'add') {
        if ($action == 'add') {
            unset($user_data['profile_id']);

            $user_data['profile_type'] = 'P';
            $user_data['profile_name'] = empty($user_data['profile_name']) ? __('main') : $user_data['profile_name'];
        } else {
            $user_data['profile_type'] = 'S';
        }

        $user_data['profile_id'] = db_query("INSERT INTO ?:user_profiles ?e", $user_data);
    } else {
        if (empty($user_data['profile_id'])) {
            $user_data['profile_id'] = db_get_field("SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i AND profile_type = 'P'", $user_id);
        }
        $is_exists = db_get_field('SELECT COUNT(*) FROM ?:user_profiles WHERE user_id = ?i AND profile_id = ?i', $user_id, $user_data['profile_id']);

        if ($is_exists) {
            db_query("UPDATE ?:user_profiles SET ?u WHERE profile_id = ?i", $user_data, $user_data['profile_id']);
        } else {
            return false;
        }
    }

    // Add/Update additional fields
    fn_store_profile_fields($user_data, array('U' => $user_id, 'P' => $user_data['profile_id']), 'UP');

    /**
     * Perform actions after user profile update
     *
     * @param int    $user_id   User identifier
     * @param array  $user_data Profile information
     * @param string $action    Current action (Example: 'add')
     */
    fn_set_hook('update_user_profile_post', $user_id, $user_data, $action);

    return $user_data['profile_id'];
}

/**
 * Deletes user profile
 *
 * @param int $user_id User identifier
 * @param int $profile_id User profile identifier
 * @param bool $allow_delete_main Denie/Allow to delete main profile
 * @return bool True on success, false otherwise
 */
function fn_delete_user_profile($user_id, $profile_id, $allow_delete_main = false)
{
    // Allows to delete only secondary profiles
    $profile_condition = $allow_delete_main ? '' : db_quote(' AND profile_type = ?s', 'S');

    $can_delete = db_get_field(
        'SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i AND profile_id = ?i ?p',
        $user_id, $profile_id, $profile_condition
    );

    if (!empty($can_delete)) {
        db_query('DELETE FROM ?:user_profiles WHERE profile_id = ?i', $profile_id);
        db_query('DELETE FROM ?:profile_fields_data WHERE object_id = ?i AND object_type = ?s', $profile_id, 'P');
    }

    return !empty($can_delete);
}

/**
 * Check if user login/email equal to customer password
 *
 * @param array $user_data user data
 * @param string $password user password
 * @return boolean
 */
function fn_compare_login_password($user_data, $password)
{
    /**
     * Change user data
     *
     * @param array  $user_data user data
     * @param string $password  user password
     */
    fn_set_hook('compare_login_password_pre', $user_data, $password);

    if (Registry::get('settings.General.use_email_as_login') == 'Y') {
        $account = !empty($user_data['email']) ? $user_data['email'] : '';
    } else {
        $account = !empty($user_data['user_login']) ? $user_data['user_login'] : '';
    }

    $result = $password == $account ? true : false;

    /**
     * Change user data and checking result
     *
     * @param array   $user_data user data
     * @param string  $password  user password
     * @param string  $account   depends on use_email_as_login setting. Contains customer email ot customer login
     * @param boolean $result    checking result
     */
    fn_set_hook('compare_login_password_post', $user_data, $password, $account, $result);

    return $result;
}

/**
 * Check if specified account needs login
 *
 * @param string $user_type - user account type
 * @return bool true if needs login, false otherwise
 */
function fn_user_need_login($user_type)
{
    $types = array(
        'A',
        'C'
    );

    fn_set_hook('user_need_login', $types);

    return in_array($user_type, $types);
}

/**
 * Check compatible user types
 *
 * @param array $user_data - new user data
 * @param array $current_user_data - current user data
 * @return char user type
 */
function fn_check_user_type($user_data, $current_user_data)
{
    $compatible_types = array();

    $current_u_type = $current_user_data['user_type'];
    $u_type = !empty($user_data['user_type']) ? $user_data['user_type'] : $current_user_data['user_type'];

    if (AREA == 'A' || $u_type == $current_u_type) {
        return $u_type;
    }

    fn_set_hook('check_user_type', $compatible_types);

    return !empty($compatible_types[$current_u_type]) && in_array($u_type, $compatible_types[$current_u_type]) ? $u_type : $current_u_type;
}

function fn_check_user_type_admin_area($user_type = '')
{
    if (is_array($user_type)) {
        $user_type = !empty($user_type['user_type']) ? $user_type['user_type'] : '';
    }

    return ($user_type == 'A' || $user_type == 'V') ? true : false;
}

/**
 * Filter hidden fields, which were hidden to checkout
 *
 * @param array $user_data - user data
 * @param char $location - flag for including data
 * @return array filtered fields
 */
function fn_filter_hidden_profile_fields(&$user_data, $location)
{
    $condition = "WHERE 1 ";

    if ($location == 'O') {
        $condition .= " AND ?:profile_fields.checkout_show = 'N'";
    }

    $filtered = db_get_array("SELECT ?:profile_fields.field_name FROM ?:profile_fields $condition");
    foreach ($filtered as $field) {
        if (!empty($field['field_name'])) {
            /* Workaround for 'email' field */
            if ($field['field_name'] == 'email') {
                continue;
            }

            unset($user_data[$field['field_name']]);
        }
    }

    return $filtered;
}

function fn_check_profile_fields_population($user_data, $section, $profile_fields)
{
    // If this section does not have fields, assume it's filled
    // or if we're checking shipping/billing section and shipping/billing address does not differ from billing/shipping, assume that fields filled correctly
    if ($section == 'B') {
        $check_section = 'S';
    } else {
        $check_section = 'B';
    }

    if (empty($profile_fields[$section]) || ($section == $check_section && fn_check_shipping_billing($user_data, $profile_fields) == false)) {
        return true;
    }

    foreach ($profile_fields[$section] as $field) {
        if ($field['required'] == 'Y' && ((!empty($field['field_name']) && empty($user_data[$field['field_name']])) || (empty($field['field_name']) && empty($user_data['fields'][$field['field_id']])))) {
            return false;
        }
    }

    return true;
}

/**
 * Check if specified user has access to the specified mode
 *
 * @param int $user_id - user id
 * @param string $mode - the mode, should be checked
 * @return boolean true if the user has access, false otherwise
 */
function fn_check_user_access($user_id, $mode)
{
    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $access = db_get_fields('SELECT ?:usergroup_privileges.privilege, ?:usergroup_privileges.usergroup_id FROM ?:usergroup_privileges LEFT JOIN ?:usergroup_links ON (?:usergroup_privileges.usergroup_id = ?:usergroup_links.usergroup_id) WHERE ?:usergroup_links.user_id = ?i AND ?:usergroup_links.status = ?s', $user_id, 'A');
    }

    if ((!empty($access) && in_array($mode, $access)) || empty($access)) {
        return true;
    }

    return false;
}

/**
 * Get available usergroups for user
 *
 * @param int $user_id User ID
 * @return array with available usergroups
 */
function fn_get_user_usergroups($user_id)
{
    $usergroups = array();

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $usergroups = db_get_hash_array("SELECT lnk.link_id, lnk.usergroup_id, lnk.status, ?:usergroups.type FROM ?:usergroup_links as lnk INNER JOIN ?:usergroups ON ?:usergroups.usergroup_id = lnk.usergroup_id AND ?:usergroups.status != 'D' WHERE lnk.user_id = ?i", 'usergroup_id', $user_id);
    }

    return $usergroups;
}

/**
 * Checks is it possible or not to delete user
 *
 * @param array $user_data Array with user data (should contain at least user_id, is_root and company_id fields)
 * @param array $auth Array with authorization data
 * @return bool True if user can be deleted, false otherwise.
 */
function fn_check_rights_delete_user($user_data, $auth)
{
    $result = true;

    if (
        ($user_data['is_root'] == 'Y' && !$user_data['company_id']) // root admin
        || fn_is_restricted_admin($user_data) // have no rights to delete user
        || (!empty($auth['user_id']) && $auth['user_id'] == $user_data['user_id']) // trying to delete himself
        || (Registry::get('runtime.company_id') && $user_data['is_root'] == 'Y') // vendor root admin
        || (Registry::get('runtime.company_id') && fn_allowed_for('ULTIMATE') && $user_data['company_id'] != Registry::get('runtime.company_id')) // user from other store
    ) {
        $result = false;
    }

    /**
    * Hook for changing the result of check
    *
    * @param array $user_data Array with user data
    * @param bool $result Result of check
    */
    fn_set_hook('check_rights_delete_user', $user_data, $auth, $result);

    return $result;
}

/**
 * Deletes user and all related data
 *
 * @param int $user_id User identificator
 * @return boolean False, if user can not be deleted, true if user was successfully deleted
 */
function fn_delete_user($user_id)
{
    fn_set_hook('pre_delete_user', $user_id);

    $condition = fn_get_company_condition('?:users.company_id');
    $user_data = db_get_row("SELECT user_id, is_root, company_id FROM ?:users WHERE user_id = ?i $condition", $user_id);

    if (empty($user_data)) {
        return false;
    }

    $auth = $_SESSION['auth'];

    if (!fn_check_rights_delete_user($user_data, $auth)) {
        fn_set_notification('W', __('warning'), __('user_cannot_be_deleted', array(
            '[user_id]' => $user_id
        )), '', 'user_delete_no_permissions');

        return false;
    }

    // Log user deletion
    fn_log_event('users', 'delete', array (
        'user_id' => $user_id,
    ));

    fn_set_hook('delete_user', $user_id, $user_data);

    $result = db_query("DELETE FROM ?:users WHERE user_id = ?i", $user_id);
    db_query('DELETE FROM ?:profile_fields_data WHERE object_id = ?i AND object_type = ?s', $user_id, 'U');
    db_query('DELETE FROM ?:user_session_products WHERE user_id = ?i', $user_id);
    db_query('DELETE FROM ?:user_data WHERE user_id = ?i', $user_id);
    db_query('UPDATE ?:orders SET user_id = 0 WHERE user_id = ?i', $user_id);

    $profile_ids = db_get_fields('SELECT profile_id FROM ?:user_profiles WHERE user_id = ?i', $user_id);
    foreach ($profile_ids as $profile_id) {
        fn_delete_user_profile($user_id, $profile_id, true);
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        db_query('DELETE FROM ?:usergroup_links WHERE user_id = ?i', $user_id);
    }

    /**
     * Hook for deleting related user data in addons
     *
     * @param int   $user_id   User identificator
     * @param array $user_data Array with user data (contains user_id, is_root and company_id fields)
     * @param int count of affected rows
     */
    fn_set_hook('post_delete_user', $user_id, $user_data, $result);

    return $result;
}

/**
 * Log in user using only user id
 * return 0 - we can't find user with provided user_id
 * return 1 - user was successfully loggined
 * return 2 - user disabled
 *
 */

function fn_login_user($user_id = '')
{
    $udata = array();
    $auth = & $_SESSION['auth'];
    $condition = '';
    $result = LOGIN_STATUS_USER_NOT_FOUND;

    fn_set_hook('login_user_pre', $user_id, $udata, $auth, $condition);

    if (!empty($user_id)) {
        if (fn_allowed_for('ULTIMATE')) {
            if (Registry::get('settings.Stores.share_users') == 'N' && AREA != 'A') {
                $condition .= fn_get_company_condition('?:users.company_id');
            }
        }

        $udata = db_get_row("SELECT * FROM ?:users WHERE user_id = ?i AND status = 'A'" . $condition, $user_id);
        if (empty($udata)) {
            $udata = db_get_row("SELECT * FROM ?:users WHERE user_id = ?i AND user_type IN ('A', 'V', 'P')", $user_id);
        }

        unset($_SESSION['status']);

        $auth = fn_fill_auth($udata, isset($auth['order_ids']) ? $auth['order_ids'] : array());
        if (!empty($udata)) {
            fn_set_hook('sucess_user_login', $udata, $auth);
            if (AREA == 'C') {
                if ($cu_id = fn_get_session_data('cu_id')) {
                    fn_clear_cart($cart);
                    fn_save_cart_content($cart, $cu_id, 'C', 'U');
                    fn_delete_session_data('cu_id');
                }
                fn_init_user_session_data($_SESSION, $udata['user_id']);
            }

            $result = LOGIN_STATUS_OK;
        } else {
            $result = LOGIN_STATUS_USER_DISABLED;
        }
    } else {
        $auth = fn_fill_auth($udata, isset($auth['order_ids']) ? $auth['order_ids'] : array());

        $result = LOGIN_STATUS_USER_NOT_FOUND;
    }

    fn_init_user();

    fn_set_hook('login_user_post', $user_id, $cu_id, $udata, $auth, $condition, $result);

    return $result;
}

function fn_check_permission_act_as_user()
{
    return !empty($_SESSION['auth']['user_id']) && !empty($_REQUEST['user_id']) && $_SESSION['auth']['user_id'] == $_REQUEST['user_id'] ? true : false;
}

/**
 * Checks if administrator can mange profiles
 *
 * @param char $user_type Type of profiles
 * @return boolean Flag: indicates if administrator has the permission to manage profiles
 */
function fn_check_permission_manage_profiles($user_type)
{
    $result = true;

    /**
     * Changes the result of administrator access to profile management check
     *
     * @param boolean $result    Result of check: true if administeator has access, false otherwise
     * @param char    $user_type Types of profiles
     */
    fn_set_hook('check_permission_manage_profiles', $result, $user_type);

    return $result;
}

/**
 * Generate random salt
 *
 * @param int $length - salt length
 * @return string salt
 */
function fn_generate_salt($length = 10)
{
    $length = $length > 10 ? 10 : $length;

    $salt = '';

    for ($i = 0; $i < $length; $i++) {
        $salt .= chr(rand(33, 126));
    }

    return $salt;
}

/**
 * Generate password with salt
 *
 * @param string $password - simple text password
 * @param string $salt - password salt
 * @return string generated password
 */
function fn_generate_salted_password($password, $salt)
{
    $_pass = '';

    if (empty($salt)) {
        $_pass = md5($password);
    } else {
        $_pass = md5(md5($password) . md5($salt));
    }

    return $_pass;
}

function fn_get_my_account_title_class()
{
    return !empty($_SESSION['auth']['user_id']) ? 'logged' : 'unlogged';
}

/**
 * Check if user already registered in store
 *
 * @param int $user_id User identifier
 * @param array $user_data User data
 * @return bool true if user already exists in the DB, false otherwise
 */
function fn_is_user_exists($user_id, $user_data)
{
    /**
     * Change parameter for user checking
     *
     * @param int  $user_id   User identifier
     * @param type $user_data User data
     */
    fn_set_hook('is_user_exists_pre', $user_id, $user_data);

    $condition = db_quote(" (?p ?p) ", (!empty($user_data['email']) ? db_quote('email = ?s', $user_data['email']) : '0'), (empty($user_data['user_login']) ? '' : db_quote(" OR user_login = ?s", $user_data['user_login'])));
    $condition .= db_quote(" AND user_id != ?i", $user_id);

    fn_set_hook('user_exist', $user_id, $user_data, $condition);

    $is_exist = db_get_field("SELECT user_id FROM ?:users WHERE $condition");

    /**
     * Change parameter for user checking
     *
     * @param int   $user_id   User identifier
     * @param array $user_data User data
     * @param bool  $is_exist  Result of checking
     */
    fn_set_hook('is_user_exists_post', $user_id, $user_data, $is_exist);

    return $is_exist;
}

/**
 * Get profile field value for printing
 *
 * @param array $profile Profile data
 * @param array $field Field data
 * @return string Field value
 */
function fn_get_profile_field_value($profile, $field)
{
    $value = '';

    if (!empty($field['field_name'])) {
        $data_id = $field['field_name'];
        $value = !empty($profile[$data_id]) ? $profile[$data_id] : '';
    } else {
        $data_id = $field['field_id'];
        $value =!empty($profile['fields'][$data_id]) ? $profile['fields'][$data_id] : '';
    }

    if (!empty($value)) {
        if (strpos("AO", $field['field_type']) !== false) {
            // States/Countries
            $title = $data_id . '_descr';
            $value = !empty($profile[$title]) ? $profile[$title] : '-';
        } elseif ($field['field_type'] == "C") {
            // Checkbox
            $value = ($value == "Y") ? __('yes') : __('no');
        } elseif ($field['field_type'] == "D") {
            // Date
            $value = fn_date_format($value, Registry::get('settings.Appearance.date_format'));
        } elseif (strpos("RS", $field['field_type']) !== false) {
            // Selectbox/Radio
            $value = $field['values'][$value];
        }
    }

    return $value;
}

/**
 * Gets user type from request parameters
 *
 * @param array $params Request parameters
 * @param string $area current application area
 * @return char User type
 */
function fn_get_request_user_type($params, $area = AREA)
{
    $user_type = '';

    if (!empty($params['user_type'])) {
        $user_type =  $params['user_type'];
    } elseif (!empty($params['user_id'])) {
        $user_type = db_get_field("SELECT user_type FROM ?:users WHERE user_id = ?i", $params['user_id']);
    }

    // Use customer user type by default
    $user_type = !empty($user_type) ? $user_type : 'C';

    /**
     * Changes defined user type
     *
     * @param char User type
     * @param array  $params Request parameters
     * @param string $area   current application area
     */
    fn_set_hook('get_request_user_type', $user_type, $params, $area);

    return $user_type;
}

/**
 * Checks if user has active user groups
 *
 * @param array $user_data Array of user data @see fn_get_user_info
 * @return bool True on success, false otherwise
 */
function fn_user_has_active_usergroups($user_data)
{
    $has = false;

    if (!empty($user_data['usergroups'])) {
        foreach ($user_data['usergroups'] as $_user_group) {
            if ($_user_group['status'] == 'A') {
                $has = true;
                break;
            }
        }
    }

    return $has;
}

function fn_auth_routines($request, $auth)
{
    $status = true;

    $user_login = (!empty($request['user_login'])) ? trim($request['user_login']) : '';
    $password = (!empty($request['password'])) ? $request['password']: '';
    $field = (Registry::get('settings.General.use_email_as_login') == 'Y') ? 'email' : 'user_login';

    $condition = '';

    if (fn_allowed_for('ULTIMATE')) {
        if (Registry::get('settings.Stores.share_users') == 'N' && AREA != 'A') {
            $condition = fn_get_company_condition('?:users.company_id');
        }
    }

    $user_data = db_get_row("SELECT * FROM ?:users WHERE $field = ?s" . $condition, $user_login);

    if (empty($user_data)) {
        $user_data = db_get_row("SELECT * FROM ?:users WHERE $field = ?s AND user_type IN ('A', 'V', 'P')", $user_login);
    }

    if (!empty($user_data)) {
        $user_data['usergroups'] = fn_get_user_usergroups($user_data['user_id']);
    }

    if (!empty($user_data) && (!fn_check_user_type_admin_area($user_data) && AREA == 'A' || !fn_check_user_type_access_rules($user_data))) {
        fn_set_notification('E', __('error'), __('error_area_access_denied'));
        $status = false;
    }

    if (!empty($user_data['status']) && $user_data['status'] == 'D') {
        fn_set_notification('E', __('error'), __('error_account_disabled'));
        $status = false;
    }

    $salt = isset($user_data['salt']) ? $user_data['salt'] : '';

    return array($status, $user_data, $user_login, $password, $salt);
}

/**
 * Fills empty contact information fields from billing/shipping addresses.
 * Useful when some profile fields were disabled.
 *
 * @param array       $data                 User data to be modified
 * @param string|null $main_address_zone    Address zone which will be used as data source
 * @param string|null $alt_address_zone     Address zone which will be used as secondary data
 *                                          source (when main zone doesn't contain required data)
 *
 * @return array User data with filled contact information fields
 */
function fn_fill_contact_info_from_address($data, $main_address_zone = null, $alt_address_zone = null)
{
    if ($main_address_zone === null && $alt_address_zone === null) {
        $main_address_zone = SHIPPING_ADDRESS_PREFIX;
        $alt_address_zone = BILLING_ADDRESS_PREFIX;
        $profile_fields = fn_get_profile_fields('O');

        if (Registry::get('settings.General.address_position') == 'billing_first' && !empty($profile_fields['B'])) {
            $main_address_zone = BILLING_ADDRESS_PREFIX;
            $alt_address_zone = SHIPPING_ADDRESS_PREFIX;
        }
    }

    if (empty($data['firstname'])) {
        if (!empty($data[$main_address_zone . '_firstname'])) {
            $data['firstname'] = $data[$main_address_zone . '_firstname'];
        } elseif (!empty($data[$alt_address_zone . '_firstname'])) {
            $data['firstname'] = $data[$alt_address_zone . '_firstname'];
        }
    }
    if (empty($data['lastname'])) {
        if (!empty($data[$main_address_zone . '_lastname'])) {
            $data['lastname'] = $data[$main_address_zone . '_lastname'];
        } elseif (!empty($data[$alt_address_zone . '_lastname'])) {
            $data['lastname'] = $data[$alt_address_zone . '_lastname'];
        }
    }
    if (empty($data['phone'])) {
        if (!empty($data[$main_address_zone . '_phone'])) {
            $data['phone'] = $data[$main_address_zone . '_phone'];
        } elseif (!empty($data[$alt_address_zone . '_phone'])) {
            $data['phone'] = $data[$alt_address_zone . '_phone'];
        }
    }

    return $data;
}

/**
 * Checks if user data are complete
 *
 * @param array $user_data Array of user data @see fn_get_user_info
 * @param string $location Location identifier
 * @param array $auth - authentication information
 * @return bool True on success, false otherwise
 */
function fn_check_profile_fields($user_data, $location = 'C', $auth = array())
{
    $result = true;
    $profile_fields = fn_get_profile_fields($location, $auth);
    if ($location == 'O') {
        unset($profile_fields['E']);
    }

    foreach ($profile_fields as $section => $fields) {
        if (!fn_check_profile_fields_population($user_data, $section, $profile_fields)) {
            $result = false;
            break;
        }
    }

    return $result;
}

/**
 * Update or create profile field. Also can create fields matching.
 *
 * @param array $field_data Array of profile field data
 * @param int $field_id Profile field id to be updated. If empty - new field will be created
 * @param strging $lang_code 2-letters language code
 *
 * @return int $field_id New or updated field id
 */
function fn_update_profile_field($field_data, $field_id, $lang_code = DESCR_SL)
{
    if (empty($field_id)) {

        $add_match = false;

        $field_name = $field_data['field_name'];
        if ($field_data['section'] == 'BS') {
            $field_data['section'] = 'B';
            $field_data['field_name'] = !empty($field_name) ? ('b_' . $field_name) : '';
            $add_match = true;
        }

        // Insert main data
        $field_id = db_query("INSERT INTO ?:profile_fields ?e", $field_data);

        // Insert descriptions
        $_data = array (
            'object_id' => $field_id,
            'object_type' => 'F',
            'description' => $field_data['description'],
        );

        foreach (fn_get_translation_languages() as $_data['lang_code'] => $_v) {
            db_query("INSERT INTO ?:profile_field_descriptions ?e", $_data);
        }

        if (substr_count('SR', $field_data['field_type']) && is_array($field_data['add_values']) && $add_match == false) {
            fn_add_field_values($field_data['add_values'], $field_id);
        }

        if ($add_match == true) {
            $field_data['section'] = 'S';
            $field_data['field_name'] = !empty($field_name) ? ('s_' . $field_name) : '';
            $field_data['matching_id'] = $field_id;

            // Update match for the billing field
            $s_field_id = fn_update_profile_field($field_data, 0, $lang_code);
            if (!empty($s_field_id)) {
                db_query('UPDATE ?:profile_fields SET matching_id = ?i WHERE field_id = ?i', $s_field_id, $field_id);
            }
        }

    } else {
        db_query("UPDATE ?:profile_fields SET ?u WHERE field_id = ?i", $field_data, $field_id);

        if (!empty($field_data['matching_id']) && $field_data['section'] == 'S') {
            db_query('UPDATE ?:profile_fields SET field_type = ?s WHERE field_id = ?i', $field_data['field_type'], $field_data['matching_id']);
        }

        db_query("UPDATE ?:profile_field_descriptions SET ?u WHERE object_id = ?i AND object_type = 'F' AND lang_code = ?s", $field_data, $field_id, $lang_code);

        if (!empty($field_data['field_type'])) {
            if (strpos('SR', $field_data['field_type']) !== false) {
                if (!empty($field_data['values'])) {
                    foreach ($field_data['values'] as $value_id => $vdata) {
                        db_query("UPDATE ?:profile_field_values SET ?u WHERE value_id = ?i", $vdata, $value_id);
                        db_query("UPDATE ?:profile_field_descriptions SET ?u WHERE object_id = ?i AND object_type = 'V' AND lang_code = ?s", $vdata, $value_id, $lang_code);
                    }

                    // Completely delete removed values
                    $existing_ids = db_get_fields("SELECT value_id FROM ?:profile_field_values WHERE field_id = ?i", $field_id);
                    $val_ids = array_diff($existing_ids, array_keys($field_data['values']));

                    if (!empty($val_ids)) {
                        fn_delete_field_values($field_id, $val_ids);
                    }
                } else {
                   if (isset($field_data['add_values'])) {
                        fn_delete_field_values($field_id);
                    }
                }

                if (!empty($field_data['add_values']) && is_array($field_data['add_values'])) {
                    fn_add_field_values($field_data['add_values'], $field_id);
                }
            } else {
                fn_delete_field_values($field_id);
            }
        }
    }

    return $field_id;
}

/**
 * Authorize user by ekey
 * and delete expired ekeys.
 *
 * @param string $ekey
 * @return bool|string
 */
function fn_recover_password_login($ekey = null)
{
    if ($ekey) {
        $u_id = fn_get_object_by_ekey($ekey, 'U');

        if ($u_id) {
            $user_status = fn_login_user($u_id);

            if ($user_status == LOGIN_STATUS_OK) {
                fn_set_notification('N', __('notice'), __('text_change_password'), 'I', 'notice_text_change_password');

                return $u_id;
            } else {
                fn_set_notification('E', __('error'), __('error_login_not_exists'));

                return $user_status;
            }
        } else {
            fn_set_notification('E', __('error'), __('text_ekey_not_valid'));

            return false;
        }
    }

    return null;
}

/**
 * Generate ekey.
 *
 * @param string $user_email
 * @return bool
 */
function fn_recover_password_generate_key($user_email, $notify = true)
{
    $result = true;

    if ($user_email) {
        $condition = '';

        if (fn_allowed_for('ULTIMATE')) {
            if (Registry::get('settings.Stores.share_users') == 'N' && AREA != 'A') {
                $condition = fn_get_company_condition('?:users.company_id');
            }
        }

        $uid = db_get_field("SELECT user_id FROM ?:users WHERE email = ?s" . $condition, $user_email);

        $u_data = fn_get_user_info($uid, false);
        if (isset($u_data['status']) && $u_data['status'] == 'D') {
            fn_set_notification('E', __('error'), __('error_account_disabled'));

            return false;
        }
        if (!empty($u_data['email'])) {

            $ekey = fn_generate_ekey($u_data['user_id'], 'U', SECONDS_IN_DAY);

            if ($notify) {
                Mailer::sendMail(array(
                    'to' => $u_data['email'],
                    'from' => 'default_company_users_department',
                    'data' => array(
                        'ekey' => $ekey,
                        'zone' => $u_data['user_type'],
                    ),
                    'tpl' => 'profiles/recover_password.tpl',
                ), fn_check_user_type_admin_area($u_data['user_type']) ? 'A' : 'C', $u_data['lang_code']);

                fn_set_notification('N', __('information'), __('text_password_recovery_instructions_sent'));
            } else {
                $result = array('company_id' => $u_data['company_id'], 'key' => $ekey, 'user_type' => $u_data['user_type']);
            }

        } else {
            fn_set_notification('E', __('error'), __('error_login_not_exists'));
            $result = false;
        }
    } else {
        fn_set_notification('E', __('error'), __('error_login_not_exists'));
        $result = false;
    }

    return $result;
}

/**
 * @param array $auth
 */
function fn_user_logout($auth)
{
    // Regenerate session_id for security reasons

    fn_save_cart_content($_SESSION['cart'], $auth['user_id']);

    Session::regenerateId();
    fn_init_user();
    $auth = $_SESSION['auth'];

    if (!empty($auth['user_id'])) {
        // Log user logout
        fn_log_event('users', 'session', array(
            'user_id' => $auth['user_id'],
            'time' => TIME - $auth['this_login'],
            'timeout' => false,
        ));
    }

    unset($_SESSION['auth']);
    fn_clear_cart($_SESSION['cart'], false, true);

    fn_delete_session_data(AREA . '_user_id', AREA . '_password');

    unset($_SESSION['product_notifications']);

    fn_login_user(); // need to fill $_SESSION['auth'] array for anonymous user
}

/**
 * Checks if current user can edit specified profile
 *
 * @param array $auth authentication information
 * @param array $user_data Edited profile
 * @return bool true if authenticated user can edit specified profile, false otherwise
 */
function fn_check_editable_permissions($auth, $user_data)
{
    $has_permissions = true;

    if ($auth['is_root'] == 'Y' && $auth['user_type'] == 'A') {
        $has_permissions = true;

    } elseif (isset($user_data['is_root'])) {
        if ($user_data['is_root'] == 'Y' && $auth['is_root'] != 'Y' && $user_data['user_type'] == 'A') {
            $has_permissions = false;

        } elseif ($auth['user_type'] == 'V' && $user_data['is_root'] == 'Y') {
            $has_permissions = false;
        }
    }

    /**
     * Modifies the result of permission checking to modify profile
     *
     * @param array $auth            authentication information
     * @param array $user_data       Edited profile
     * @param bool  $has_permissions checking result
     */
    fn_set_hook('check_editable_permissions_post', $auth, $user_data, $has_permissions);

    return $has_permissions;
}
