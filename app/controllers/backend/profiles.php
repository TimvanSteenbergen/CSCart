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
use Tygh\Session;
use Tygh\Mailer;
use Tygh\Api;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'm_delete') {

        if (!empty($_REQUEST['user_ids'])) {
            foreach ($_REQUEST['user_ids'] as $v) {
                fn_delete_user($v);
            }
        }

        return array(CONTROLLER_STATUS_OK, "profiles.manage" . (isset($_REQUEST['user_type']) ? "?user_type=" . $_REQUEST['user_type'] : '' ));
    }

    if ($mode == 'export_range') {
        if (!empty($_REQUEST['user_ids'])) {

            if (empty($_SESSION['export_ranges'])) {
                $_SESSION['export_ranges'] = array();
            }

            if (empty($_SESSION['export_ranges']['users'])) {
                $_SESSION['export_ranges']['users'] = array('pattern_id' => 'users');
            }

            $_SESSION['export_ranges']['users']['data'] = array('user_id' => $_REQUEST['user_ids']);

            unset($_REQUEST['redirect_url']);

            return array(CONTROLLER_STATUS_REDIRECT, "exim.export?section=users&pattern_id=" . $_SESSION['export_ranges']['users']['pattern_id']);
        }
    }

    //
    // Create/Update user
    //
    if ($mode == 'update' || $mode == 'add') {
        $profile_id = !empty($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : 0;
        $_uid = !empty($profile_id) ? db_get_field("SELECT user_id FROM ?:user_profiles WHERE profile_id = ?i", $profile_id) : $auth['user_id'];
        $user_id = empty($_REQUEST['user_id']) ? (($mode == 'add') ? '' : $_uid) : $_REQUEST['user_id'];

        $mode = empty($_REQUEST['user_id']) ? 'add' : 'update';
        // TODO: FIXME user_type
        if (Registry::get('runtime.company_id') && $user_id != $auth['user_id']) {
            $_REQUEST['user_data']['user_type'] = !empty($_REQUEST['user_type']) ? $_REQUEST['user_type'] : 'C';
        }

        // Restricted admin cannot change its user type
        if (fn_is_restricted_admin($_REQUEST) && $user_id == $auth['user_id'] || ($user_id == $auth['user_id'] && $auth['area'] == 'A')) {
            $_REQUEST['user_type'] = '';
            $_REQUEST['user_data']['user_type'] = $auth['user_type'];
        }

        /**
         * Only admin can set the api key.
         */
        if (empty($_REQUEST['user_api_status']) || $_REQUEST['user_api_status'] == 'N') {
            $_REQUEST['user_data']['api_key'] = '';
        }

        fn_restore_processed_user_password($_REQUEST['user_data'], $_POST['user_data']);

        $res = fn_update_user($user_id, $_REQUEST['user_data'], $auth, !empty($_REQUEST['ship_to_another']), !empty($_REQUEST['notify_customer']));

        if ($res) {
            list($user_id, $profile_id) = $res;

            if (!empty($_REQUEST['return_url'])) {
                return array(CONTROLLER_STATUS_OK, $_REQUEST['return_url']);
            }
        } else {
            fn_save_post_data('user_data');
            fn_delete_notification('changes_saved');
        }

        $redirect_params =  array(
            'user_id' => $user_id
        );

        if (Registry::get('settings.General.user_multiple_profiles') == 'Y') {
            $redirect_params['profile_id'] = $profile_id;
        }

        if (!empty($_REQUEST['user_type'])) {
            $redirect_params['user_type'] = $_REQUEST['user_type'];
        }

        if (!empty($_REQUEST['return_url'])) {
            $redirect_params['return_url'] = urlencode($_REQUEST['return_url']);
        }

        return array(CONTROLLER_STATUS_OK, "profiles." . (!empty($user_id) ? "update" : "add") . "?" . http_build_query($redirect_params));
    }

}

if ($mode == 'manage') {

    if (
        Registry::get('runtime.company_id')
        && !empty($_REQUEST['user_type'])
        && (
            $_REQUEST['user_type'] == 'P'
            || (
                $_REQUEST['user_type'] == 'A'
                && !fn_check_permission_manage_profiles('A')
            )
        )
    ) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    if (!empty($_REQUEST['user_type']) && $_REQUEST['user_type'] == 'V' && fn_allowed_for('ULTIMATE')) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    list($users, $search) = fn_get_users($_REQUEST, $auth, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('users', $users);
    Registry::get('view')->assign('search', $search);

    if (!empty($search['user_type'])) {
        Registry::get('view')->assign('user_type_description', fn_get_user_type_description($search['user_type']));
    }

    $user_types = fn_get_user_types();
    if (Registry::get('runtime.company_id') && fn_allowed_for("MULTIVENDOR")) {
        unset($user_types['C']);
    }
    if (fn_is_restricted_admin(array('user_type' => 'V'))) {
        unset($user_types['V']);
    }

    Registry::get('view')->assign('user_types', $user_types);
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('usergroups', fn_get_usergroups('F', DESCR_SL));

} elseif ($mode == 'act_as_user' || $mode == 'view_product_as_user') {

    if (fn_is_restricted_admin($_REQUEST) == true) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    $condition = '';
    $_suffix = '';

    if (fn_allowed_for('MULTIVENDOR') && $mode == 'act_as_user') {
        $condition = fn_get_company_condition('?:users.company_id');
    }

    $user_data = db_get_row("SELECT * FROM ?:users WHERE user_id = ?i $condition", $_REQUEST['user_id']);

    if (!empty($user_data)) {
        if (!empty($_REQUEST['area'])) {
            $area = $_REQUEST['area'];
        } else {
            $area = fn_check_user_type_admin_area($user_data) ? 'A' : 'C';
        }

        if (fn_allowed_for('MULTIVENDOR')) {
            if ($user_data['user_type'] == 'V') {
                $area = ($area == 'A') ? 'V' : $area;
            }
        }

        $sess_data = array(
            'auth' => fn_fill_auth($user_data, array(), true, $area),
            'last_status' => empty($_SESSION['last_status']) ? '' : $_SESSION['last_status'],
        );

        if (Registry::get('settings.General.store_mode') == 'Y') {
            $sess_data['store_access_key'] = Registry::get('settings.General.store_access_key');
        }

        $areas = array(
            'A' => 'admin',
            'V' => 'vendor',
            'C' => 'customer',
        );

        fn_init_user_session_data($sess_data, $_REQUEST['user_id'], true);

        $old_sess_id = Session::getId();

        $redirect_url = !empty($_REQUEST['redirect_url']) ? $_REQUEST['redirect_url'] : '';

        if ($area != 'C') {
            Session::setName($areas[$area]);
            $sess_id = Session::regenerateId();
            Session::save($sess_id, $sess_data, $area);

            Session::setName(ACCOUNT_TYPE);
            Session::setId($old_sess_id, false);
        } else {
            // Save unique key for session
            $key = fn_crc32(microtime()) . fn_crc32(microtime() + 1);
            fn_set_storage_data('session_' . $key . '_data', serialize($sess_data));

            if (fn_allowed_for('ULTIMATE')) {
                $company_id_in_url = fn_get_company_id_from_uri($redirect_url);

                if (Registry::get('runtime.company_id') || !empty($user_data['company_id']) || Registry::get('runtime.simple_ultimate') || !empty($company_id_in_url)) {

                    // Redirect to the personal frontend
                    $company_id = !empty($user_data['company_id']) ? $user_data['company_id'] : Registry::get('runtime.company_id');
                    if (!$company_id && Registry::get('runtime.simple_ultimate')) {
                        $company_id = fn_get_default_company_id();
                    } elseif (!$company_id) {
                        $company_id = $company_id_in_url;
                    }
                    $url = $area == 'C' ? fn_link_attach($redirect_url, 'skey=' . $key . '&company_id=' . $company_id) : $redirect_url;

                    return array(CONTROLLER_STATUS_REDIRECT, fn_url($url, $area), true);
                }
            } else {
                $url = fn_link_attach($redirect_url, 'skey=' . $key);

                return array(CONTROLLER_STATUS_REDIRECT, fn_url($url, $area), true);
            }

        }

        return array(CONTROLLER_STATUS_REDIRECT, fn_url($redirect_url, $area));
    }

} elseif ($mode == 'picker') {
    $params = $_REQUEST;
    $params['exclude_user_types'] = array ('A', 'V');
    $params['skip_view'] = 'Y';

    list($users, $search) = fn_get_users($params, $auth, Registry::get('settings.Appearance.admin_elements_per_page'));
    Registry::get('view')->assign('users', $users);
    Registry::get('view')->assign('search', $search);

    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('usergroups', fn_get_usergroups('F', CART_LANGUAGE));

    Registry::get('view')->display('pickers/users/picker_contents.tpl');
    exit;

} elseif ($mode == 'delete') {

    fn_delete_user($_REQUEST['user_id']);

    return array(CONTROLLER_STATUS_REDIRECT);

} elseif ($mode == 'update_status') {

    $condition = fn_get_company_condition('?:users.company_id');
    $user_data = db_get_row("SELECT * FROM ?:users WHERE user_id = ?i $condition", $_REQUEST['id']);
    if (!empty($user_data)) {
        $result = db_query("UPDATE ?:users SET status = ?s WHERE user_id = ?i", $_REQUEST['status'], $_REQUEST['id']);
        if ($result && $_REQUEST['id'] != 1) {
            fn_set_notification('N', __('notice'), __('status_changed'));
            $force_notification = fn_get_notification_rules($_REQUEST);
            if (!empty($force_notification['C']) && $_REQUEST['status'] == 'A' && $user_data['status'] == 'D') {
                Mailer::sendMail(array(
                    'to' => $user_data['email'],
                    'from' => 'company_users_department',
                    'data' => array(
                        'user_data' => $user_data,
                    ),
                    'tpl' => 'profiles/profile_activated.tpl',
                    'company_id' => $user_data['company_id'],
                ), fn_check_user_type_admin_area($user_data['user_type']) ? 'A' : 'C', $user_data['lang_code']);
            }
        } else {
            fn_set_notification('E', __('error'), __('error_status_not_changed'));
            Registry::get('ajax')->assign('return_status', $user_data['status']);
        }
    }

    exit;
} elseif ($mode == 'password_reminder') {

    $cron_password = Registry::get('settings.Security.cron_password');

    if ((!isset($_REQUEST['cron_password']) || $cron_password != $_REQUEST['cron_password']) && (!empty($cron_password))) {
        die(__('access_denied'));
    }

    $expire = Registry::get('settings.Security.admin_password_expiration_period') * SECONDS_IN_DAY;

    if ($expire) {
        // Get available admins
        $recepients = db_get_array("SELECT user_id FROM ?:users WHERE user_type IN('A', 'V') AND status = 'A' AND (UNIX_TIMESTAMP() - password_change_timestamp) >= ?i", $expire);
        if (!empty($recepients)) {
            foreach ($recepients as $v) {
                $_user_data = fn_get_user_info($v['user_id'], true);

                Mailer::sendMail(array(
                    'to' => $_user_data['email'],
                    'from' => 'company_users_department',
                    'data' => array(
                        'days' => round((TIME - $_user_data['password_change_timestamp']) / SECONDS_IN_DAY),
                        'user_data' => $_user_data,
                        'link' => fn_url('auth.password_change', $_user_data['user_type'], (Registry::get('settings.Security.secure_admin') == "Y") ? 'https' : 'http')
                    ),
                    'tpl' => 'profiles/reminder.tpl',
                    'company_id' => $_user_data['company_id'],
                ), 'A', $_user_data['lang_code']);
            }
        }

        fn_echo(__('administrators_notified', array(
            '[count]' => count($recepients)
        )));
    }

    exit;
} elseif ($mode == 'update' || $mode == 'add') {

    if (empty($_REQUEST['user_type']) && (empty($_REQUEST['user_id']) || $_REQUEST['user_id'] != $auth['user_id'])) {

        $user_type = fn_get_request_user_type($_REQUEST);

        $params = array();
        if (!empty($_REQUEST['user_id'])) {
            $params[] = "user_id=" . $_REQUEST['user_id'];
        }
        $params[] = "user_type=" . $user_type;

        return array(CONTROLLER_STATUS_REDIRECT, "profiles." . $mode . "?" . implode("&", $params));
    }

    if ($mode == 'add') {
        if (fn_allowed_for('ULTIMATE')) {
            if (!empty($_REQUEST['user_type']) && $_REQUEST['user_type'] == 'V') {
                return array(CONTROLLER_STATUS_NO_PAGE);
            }

            if (Registry::get('runtime.company_id')) {
                if (empty($_REQUEST['user_type'])) {
                    $_GET['user_type'] = 'C';

                    return array(CONTROLLER_STATUS_REDIRECT, 'profiles.add?' . http_build_query($_GET));
                } elseif ($_REQUEST['user_type'] == 'A' && !fn_check_permission_manage_profiles('A')) {
                    return array(CONTROLLER_STATUS_DENIED);
                }
            }
        }

        if (fn_allowed_for('MULTIVENDOR')) {
            if (Registry::get('runtime.company_id')) {
                if (empty($_REQUEST['user_type'])) {
                    return array(CONTROLLER_STATUS_REDIRECT, 'profiles.add?user_type=' . fn_get_request_user_type($_REQUEST));
                } elseif ($_REQUEST['user_type'] == 'C') {
                    return array(CONTROLLER_STATUS_DENIED);
                } elseif ($_REQUEST['user_type'] == 'A') {
                    $_GET['user_type'] = 'V';

                    return array(CONTROLLER_STATUS_REDIRECT, 'profiles.add?' . http_build_query($_GET));
                }
            }
        }

    } else {
        if (fn_allowed_for('MULTIVENDOR')) {
            if (Registry::get('runtime.company_id') && !empty($_REQUEST['user_id']) && $_REQUEST['user_id'] != $auth['user_id']) {
                if (empty($_REQUEST['user_type'])) {
                    $_GET['user_type'] = fn_get_request_user_type($_REQUEST);

                    return array(CONTROLLER_STATUS_REDIRECT, 'profiles.update?' . http_build_query($_GET));
                } elseif ($_REQUEST['user_type'] == 'A') {
                    $_GET['user_type'] = 'V';

                    return array(CONTROLLER_STATUS_REDIRECT, 'profiles.update?' . http_build_query($_GET));
                }
            }
        }
    }

    if (
        Registry::get('runtime.company_id')
        && !empty($_REQUEST['user_type'])
        && (
            $_REQUEST['user_type'] == 'P'
            || (
                $_REQUEST['user_type'] == 'A'
                && !fn_check_permission_manage_profiles('A')
            )
        )
    ) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    if (!empty($_REQUEST['user_id']) && !empty($_REQUEST['user_type'])) {
        if ($_REQUEST['user_id'] == $auth['user_id'] && defined('RESTRICTED_ADMIN') && !in_array($_REQUEST['user_type'], array('A', ''))) {
            return array(CONTROLLER_STATUS_REDIRECT, "profiles.update?user_id=" . $_REQUEST['user_id']);
        }
    }

    if (fn_is_restricted_admin($_REQUEST) == true) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    // copy to add below this line
    $profile_id = !empty($_REQUEST['profile_id']) ? $_REQUEST['profile_id'] : 0;
    $_uid = !empty($profile_id) ? db_get_field("SELECT user_id FROM ?:user_profiles WHERE profile_id = ?i", $profile_id) : $auth['user_id'];
    $user_id = empty($_REQUEST['user_id']) ? (($mode == 'add') ? '' : $_uid) : $_REQUEST['user_id'];

    if (!empty($_REQUEST['profile']) && $_REQUEST['profile'] == 'new') {
        $user_data = fn_get_user_info($user_id, false);
    } else {
        $user_data = fn_get_user_info($user_id, true, $profile_id);
    }

    $saved_user_data = fn_restore_post_data('user_data');
    if (!empty($saved_user_data)) {
        $user_data = fn_array_merge($user_data, $saved_user_data);
    }

    if ($mode == 'update') {
        if (empty($user_data)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
    }

    $user_data['user_id'] = empty($user_data['user_id']) ? (!empty($user_id) ? $user_id : 0) : $user_data['user_id'];
    $user_data['user_type'] = empty($user_data['user_type']) ? 'C' : $user_data['user_type'];
    $user_type = (!empty($_REQUEST['user_type'])) ? ($_REQUEST['user_type']) : $user_data['user_type'];

    $usergroups = fn_get_usergroups((fn_check_user_type_admin_area($user_type) ? 'F' : 'C'), CART_LANGUAGE);

    $auth['is_root'] = isset($auth['is_root']) ? $auth['is_root'] : '';

    $navigation = array (
        'general' => array (
            'title' => __('general'),
            'js' => true
        ),
        'addons' => array (
            'title' => __('addons'),
            'js' => true
        )
    );

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if ($mode == 'update' &&
            (
                (!fn_check_user_type_admin_area($user_type) && !Registry::get('runtime.company_id')) // Customers
                ||
                (fn_check_user_type_admin_area($user_type) && !Registry::get('runtime.company_id') && $auth['is_root'] == 'Y' && (!empty($user_data['company_id']) || (empty($user_data['company_id']) && (!empty($user_data['is_root']) && $user_data['is_root'] != 'Y')))) // root admin for other admins
                ||
                ($user_data['user_type'] == 'V' && Registry::get('runtime.company_id') && $auth['is_root'] == 'Y' && $user_data['user_id'] != $auth['user_id'] && $user_data['company_id'] == Registry::get('runtime.company_id')) // vendor for other vendor admins
            )
        ) {
            $navigation['usergroups'] = array (
                'title' => __('usergroups'),
                'js' => true
            );
        } else {
            $usergroups = array();
        }
    }

    if (empty($user_data['api_key'])) {
        Registry::get('view')->assign('new_api_key', Api::generateKey());
    }

    /**
     * Only admin can set the api key.
     */
    if (fn_check_user_type_admin_area($user_data) && !empty($user_data['user_id']) && ($auth['user_type'] == 'A' || $user_data['api_key'])) {
        $navigation['api'] = array (
            'title' => __('api_access'),
            'js' => true
        );

        Registry::get('view')->assign('show_api_tab', true);

        if ($auth['user_type'] != 'A') {
            Registry::get('view')->assign('hide_api_checkbox', true);
        }
    }

    Registry::set('navigation.tabs', $navigation);

    Registry::get('view')->assign('usergroups', $usergroups);
    Registry::get('view')->assign('hide_inputs', !fn_check_editable_permissions($auth, $user_data));

    $profile_fields = fn_get_profile_fields($user_type);
    Registry::get('view')->assign('user_type', $user_type);
    Registry::get('view')->assign('profile_fields', $profile_fields);
    Registry::get('view')->assign('user_data', $user_data);
    Registry::get('view')->assign('ship_to_another', fn_check_shipping_billing($user_data, $profile_fields));
    if (Registry::get('settings.General.user_multiple_profiles') == 'Y' && !empty($user_id)) {
        Registry::get('view')->assign('user_profiles', fn_get_user_profiles($user_id));
    }

    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));
    Registry::get('view')->assign('states', fn_get_all_states());

} elseif ($mode == 'delete_profile') {

    if (fn_is_restricted_admin($_REQUEST)) {
        return array(CONTROLLER_STATUS_DENIED);
    }

    $user_id = empty($_REQUEST['user_id']) ? $auth['user_id'] : $_REQUEST['user_id'];

    fn_delete_user_profile($user_id, $_REQUEST['profile_id']);

    return array(CONTROLLER_STATUS_OK, "profiles.update?user_id=" . $user_id);
}
