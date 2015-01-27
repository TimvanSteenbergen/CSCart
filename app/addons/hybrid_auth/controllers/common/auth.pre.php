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

if ($mode == 'login_provider' || $mode == 'link_provider') {

    $status = fn_hybrid_auth_process($mode, $redirect_url);

    if ($status == HYBRID_AUTH_LOADING) {
        Registry::get('view')->display('addons/hybrid_auth/views/auth/loading.tpl');

    } else {
        unset($_SESSION['edit_step']);
        Registry::get('view')->assign('redirect_url', fn_url($redirect_url));
        Registry::get('view')->display('addons/hybrid_auth/views/auth/login_error.tpl');
    }

    exit;

} elseif ($mode == 'process') {
    $lib_path = Registry::get('config.dir.addons') . 'hybrid_auth/lib/';

    require_once($lib_path . 'Hybrid/Auth.php');
    require_once($lib_path . 'Hybrid/Endpoint.php');

    Hybrid_Endpoint::process();

} elseif ($mode == 'login_form') {

    $providers_list = fn_hybrid_auth_get_providers_list();
    if (!empty($providers_list)) {
        Registry::get('view')->assign('providers_list', $providers_list);
    }

} elseif ($mode == 'logout') {
    // Remove Hybrid auth data
    unset($_SESSION['HA::CONFIG'], $_SESSION['HA::STORE']);

} elseif ($mode == 'connect_social') {

    $email = !empty($_SESSION['hybrid_auth']['email']) ? $_SESSION['hybrid_auth']['email'] : '';
    $identifier = !empty($_SESSION['hybrid_auth']['identifier']) ? $_SESSION['hybrid_auth']['identifier'] : '';
    $provider = !empty($_SESSION['hybrid_auth']['provider']) ? $_SESSION['hybrid_auth']['provider'] : '';
    $redirect_url = !empty($_SESSION['hybrid_auth']['redirect_url']) ? $_SESSION['hybrid_auth']['redirect_url'] : fn_url();

    if (!empty($_SESSION['auth']['user_id'])) {

        fn_hybrid_auth_link_provider($_SESSION['auth']['user_id'], $identifier, $provider);
        unset($_SESSION['hybrid_auth']);

        return array(CONTROLLER_STATUS_REDIRECT, $redirect_url);
    }

    if (AREA != 'A') {
        fn_add_breadcrumb(__('hybrid_auth.connect_social'));
    }

    $user_id = fn_is_user_exists(0, array('email' => $email));

    if (!empty($user_id)) {
        $user_data = fn_get_user_short_info($user_id);

        if (Registry::get('settings.General.use_email_as_login') == 'Y') {
            $user_login = $user_data['email'];
        } else {
            $user_login = $user_data['user_login'];
        }
    } else {
        $user_login = '';
    }

    Registry::get('view')->assign('user_login', $user_login);
    Registry::get('view')->assign('identifier', $identifier);
    Registry::get('view')->assign('view_mode', 'simple');

} elseif ($mode == 'specify_email') {

    if (!empty($_REQUEST['user_email'])) {
        fn_hybrid_auth_process('login_provider', $redirect_url);
        $_REQUEST['redirect_url'] = $redirect_url;

        return array(CONTROLLER_STATUS_REDIRECT, fn_url($redirect_url));
    }

    $identifier = !empty($_SESSION['hybrid_auth']['identifier']) ? $_SESSION['hybrid_auth']['identifier'] : '';
    $provider = !empty($_SESSION['hybrid_auth']['provider']) ? $_SESSION['hybrid_auth']['provider'] : '';
    $redirect_url = !empty($_SESSION['hybrid_auth']['redirect_url']) ? $_SESSION['hybrid_auth']['redirect_url'] : fn_url();

    if (AREA != 'A') {
        fn_add_breadcrumb(__('hybrid_auth.specify_email'));
    }

    Registry::get('view')->assign('identifier', $identifier);
    Registry::get('view')->assign('provider', $provider);
    Registry::get('view')->assign('redirect_url', $redirect_url);
    Registry::get('view')->assign('view_mode', 'simple');

}
