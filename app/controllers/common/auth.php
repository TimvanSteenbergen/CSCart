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

use Tygh\Development;
use Tygh\Registry;
use Tygh\Session;
use Tygh\Helpdesk;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Login mode
    //
    if ($mode == 'login') {

        $redirect_url = '';

        if (AREA != 'A') {
            if (fn_image_verification('use_for_login', $_REQUEST) == false) {
                fn_save_post_data('user_login');

                return array(CONTROLLER_STATUS_REDIRECT);
            }
        }

        fn_restore_processed_user_password($_REQUEST, $_POST);

        list($status, $user_data, $user_login, $password, $salt) = fn_auth_routines($_REQUEST, $auth);

        if (!empty($_REQUEST['redirect_url'])) {
            $redirect_url = $_REQUEST['redirect_url'];
        } else {
            $redirect_url = fn_url('auth.login' . !empty($_REQUEST['return_url']) ? '?return_url=' . $_REQUEST['return_url'] : '');
        }

        if ($status === false) {
            fn_save_post_data('user_login');

            return array(CONTROLLER_STATUS_REDIRECT, $redirect_url);
        }
        //
        // Success login
        //
        if (!empty($user_data) && !empty($password) && fn_generate_salted_password($password, $salt) == $user_data['password']) {

            // Regenerate session_id for security reasons
            Session::regenerateId();

            //
            // If customer placed orders before login, assign these orders to this account
            //
            if (!empty($auth['order_ids'])) {
                foreach ($auth['order_ids'] as $k => $v) {
                    db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", array('user_id' => $user_data['user_id']), $v);
                }
            }

            fn_login_user($user_data['user_id']);

            Helpdesk::auth();

            // Set system notifications
            if (Registry::get('config.demo_mode') != true && AREA == 'A') {
                // If username equals to the password
                if (!defined('DEVELOPMENT') && fn_compare_login_password($user_data, $password)) {

                    $lang_var = 'warning_insecure_password';
                    if (Registry::get('settings.General.use_email_as_login') == 'Y') {
                        $lang_var = 'warning_insecure_password_email';
                    }

                    fn_set_notification('E', __('warning'), __($lang_var, array(
                        '[link]' => fn_url('profiles.update')
                    )), 'S', 'insecure_password');
                }
                if (empty($user_data['company_id']) && !empty($user_data['user_id'])) {
                    // Insecure admin script
                    if (!defined('DEVELOPMENT') && Registry::get('config.admin_index') == 'admin.php') {
                        fn_set_notification('E', __('warning'), __('warning_insecure_admin_script', array('[href]' => Registry::get('config.resources.admin_protection_url'))), 'S');
                    }

                    if (!defined('DEVELOPMENT') && is_file(Registry::get('config.dir.root') . '/install/index.php')) {
                        fn_set_notification('W', __('warning'), __('delete_install_folder'), 'S');
                    }

                    if (Development::isEnabled('compile_check')) {
                        fn_set_notification('W', __('warning'), __('warning_store_optimization_dev', array('[link]' => fn_url("themes.manage"))));
                    }

                    fn_set_hook('set_admin_notification', $user_data);
                }

            }

            if (!empty($_REQUEST['remember_me'])) {
                fn_set_session_data(AREA . '_user_id', $user_data['user_id'], COOKIE_ALIVE_TIME);
                fn_set_session_data(AREA . '_password', $user_data['password'], COOKIE_ALIVE_TIME);
            }

            // Set last login time
            db_query("UPDATE ?:users SET ?u WHERE user_id = ?i", array('last_login' => TIME), $user_data['user_id']);

            $_SESSION['auth']['this_login'] = TIME;
            $_SESSION['auth']['ip'] = $_SERVER['REMOTE_ADDR'];

            // Log user successful login
            fn_log_event('users', 'session', array(
                'user_id' => $user_data['user_id'],
            ));

            if (!empty($_REQUEST['return_url'])) {
                $redirect_url = $_REQUEST['return_url'];
            }

            unset($_REQUEST['redirect_url']);

            if (AREA == 'C') {
                fn_set_notification('N', __('notice'), __('successful_login'));
            }

            if (AREA == 'A' && Registry::get('runtime.unsupported_browser')) {
                $redirect_url = "upgrade_center.ie7notify";
            }

        } else {
        //
        // Login incorrect
        //
            // Log user failed login
            fn_log_event('users', 'failed_login', array (
                'user' => $user_login
            ));

            $auth = array();
            fn_set_notification('E', __('error'), __('error_incorrect_login'));
            fn_save_post_data('user_login');

            return array(CONTROLLER_STATUS_REDIRECT, $redirect_url);
        }

        unset($_SESSION['edit_step']);
    }

    //
    // Recover password mode
    //
    if ($mode == 'recover_password') {
        $user_email = !empty($_REQUEST['user_email']) ? $_REQUEST['user_email'] : '';
        $redirect_url = '';
        if (!fn_recover_password_generate_key($user_email)) {
            $redirect_url = "auth.recover_password";
        }
    }

    //
    // Change expired password
    //
    if ($mode == 'password_change') {
        fn_restore_processed_user_password($_REQUEST['user_data'], $_POST['user_data']);

        if (fn_update_user($auth['user_id'], $_REQUEST['user_data'], $auth, false, true)) {
            $redirect_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : '';
        } else {
            $redirect_url = 'auth.password_change';
            if (!empty($_REQUEST['return_url'])) {
                $redirect_url .= '?return_url=' . urlencode($_REQUEST['return_url']);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, !empty($redirect_url)? $redirect_url : fn_url());
}

//
// Perform user log out
//
if ($mode == 'logout') {
    fn_user_logout($auth);

    return array(CONTROLLER_STATUS_OK, fn_url());
}

//
// Recover password mode
//
if ($mode == 'recover_password') {

    $ekey = !empty($_REQUEST['ekey']) ? $_REQUEST['ekey'] : '';
    $redirect_url = '';
    $result = fn_recover_password_login($ekey);

    if (!is_null($result)) {
        if ($result === LOGIN_STATUS_USER_NOT_FOUND || $result === LOGIN_STATUS_USER_DISABLED) {
            $redirect_url = fn_url();
        } elseif ($result === false) {
            $redirect_url = 'auth.recover_password';
        } else {
            $redirect_url = "profiles.update?user_id=$result";
        }
    }

    if ($redirect_url) {
        return array(CONTROLLER_STATUS_OK, $redirect_url);
    }

    if (AREA != 'A') {
        fn_add_breadcrumb(__('recover_password'));
    }
    Registry::get('view')->assign('view_mode', 'simple');
}

if ($mode == 'ekey_login') {

    $ekey = !empty($_REQUEST['ekey']) ? $_REQUEST['ekey'] : '';
    $redirect_url = fn_url();
    $result = fn_recover_password_login($ekey);

    if (!is_null($result)) {
        if ($result === LOGIN_STATUS_USER_NOT_FOUND || $result === LOGIN_STATUS_USER_DISABLED) {
            $redirect_url = fn_url();
        } elseif ($result === false) {
            $redirect_url = fn_url();
        } else {
            fn_delete_notification('notice_text_change_password');

            if (!empty($_REQUEST['redirect_url'])) {
                $redirect_url = $_REQUEST['redirect_url'];

                if (strpos($redirect_url, '://') === false) {
                    $redirect_url = 'http://' . $redirect_url;
                }
            } else {
                $redirect_url = fn_url();
            }
        }
    }

    fn_redirect($redirect_url, true);
}

//
// Display login form in the mainbox
//
if ($mode == 'login_form') {
    if (defined('AJAX_REQUEST') && empty($auth)) {
        exit;
    }

    if (!empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url());
    }

    $stored_user_login = fn_restore_post_data('user_login');
    if (!empty($stored_user_login)) {
        Registry::get('view')->assign('stored_user_login', $stored_user_login);
    }

    if (AREA != 'A') {
        fn_add_breadcrumb(__('sign_in'));
    }

    Registry::get('view')->assign('view_mode', 'simple');

} elseif ($mode == 'password_change' && AREA == 'A') {
    if (defined('AJAX_REQUEST') && empty($auth)) {
        exit;
    }

    if (empty($auth['user_id'])) {
        return array(CONTROLLER_STATUS_REDIRECT, fn_url());
    }

    $profile_id = 0;
    $user_data = fn_get_user_info($auth['user_id'], true, $profile_id);

    Registry::get('view')->assign('user_data', $user_data);
    Registry::get('view')->assign('view_mode', 'simple');

} elseif ($mode == 'change_login') {
    $auth = $_SESSION['auth'];

    if (!empty($auth['user_id'])) {
        // Log user logout
        fn_log_event('users', 'session', array(
            'user_id' => $auth['user_id'],
            'time' => TIME - $auth['this_login'],
            'timeout' => false,
        ));
    }

    unset($_SESSION['cart']['user_data']);
    fn_login_user();

    fn_delete_session_data(AREA . '_user_id', AREA . '_password');

    return array(CONTROLLER_STATUS_OK, 'checkout.checkout');
}
