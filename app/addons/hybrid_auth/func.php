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
use Tygh\Settings;
use Tygh\Mailer;

function fn_settings_variants_addons_hybrid_auth_icons_pack()
{
    $available_icons_packs = array();

    $theme_name = Settings::instance()->getValue('theme_name', '');
    $icons_dir = fn_get_theme_path('[themes]/', 'C') . $theme_name . '/media/images/addons/hybrid_auth/icons/';

    $icons_packs = fn_get_dir_contents($icons_dir);

    foreach ($icons_packs as $id => $icons_packs_name) {
        $available_icons_packs[$icons_packs_name] = $icons_packs_name;
    }

    return $available_icons_packs;
}

function fn_hybrid_auth_post_delete_user($user_id, $user_data, $result)
{
    return db_query("DELETE FROM ?:hybrid_auth_users WHERE user_id = ?i", $user_id);
}

function fn_hybrid_auth_delete_company($company_id, $result)
{
    return db_query("DELETE FROM ?:hybrid_auth_providers WHERE company_id = ?i", $company_id);
}

function fn_hybrid_auth_get_unlink_provider($user_id, $provider)
{
    $provider_id = fn_hybrid_auth_get_provider_id($provider);

    return db_query("DELETE FROM ?:hybrid_auth_users WHERE user_id = ?i AND provider_id = ?i", $user_id, $provider_id);
}

function fn_hybrid_auth_get_link_provider($user_id)
{
    $provider = db_get_fields("SELECT provider FROM ?:hybrid_auth_providers INNER JOIN ?:hybrid_auth_users USING(provider_id) WHERE user_id = ?i", $user_id);

    return (!empty($provider)) ? $provider : array();
}

function fn_hybrid_auth_init()
{
    $lib_path = Registry::get('config.dir.addons') . 'hybrid_auth/lib/';
    $config = $lib_path . 'config.php';

    require_once($lib_path . 'Hybrid/Auth.php');

    try {
        $hybridauth = new Hybrid_Auth($config);
    }
        // if sometin bad happen
    catch( Exception $e ){

        switch ( $e->getCode() ) {
            case 0 : $message = __('hybrid_auth.unspecified_error'); break;
            case 1 : $message = __('hybrid_auth.configuration_error'); break;
            case 2 : $message = __('hybrid_auth.provider_error_configuration'); break;
            case 3 : $message = __('hybrid_auth.wrong_provider'); break;
            case 4 : $message = __('hybrid_auth.missing_credentials'); break;
            case 5 : $message = __('hybrid_auth.failed_auth'); break;

            default: $message = __('hybrid_auth.unspecified_error');
        }

        fn_set_notification('E', __('error'), $message);
        Registry::get('view')->display('addons/hybrid_auth/views/auth/login_error.tpl');

        return false;
    }

    return $hybridauth;
}

function fn_hybrid_auth_get_auth_data($hybridauth, $provider)
{
    $adapter = $hybridauth->getAdapter($provider);

    try {
        $auth_data = $adapter->getUserProfile();

    } catch (Exception $e) {
        fn_set_notification('E', __('error'), $e->getMessage());

        return false;
    }

    return $auth_data;
}

function fn_hybrid_auth_update_provider($provider_data)
{
    if (Registry::get('runtime.company_id') && !isset($provider_data['company_id'])) {
        $provider_data['company_id'] = Registry::get('runtime.company_id');
    }

    if (isset($provider_data['params'])) {
        $provider_data['app_params'] = serialize($provider_data['params']);
        unset($provider_data['params']);
    }

    $result = db_query("REPLACE INTO ?:hybrid_auth_providers ?e", $provider_data);

    return $result;
}

function fn_hybrid_auth_get_providers_list($active = true)
{
    $condition = '';

    if (Registry::get('runtime.company_id')) {
        $condition .=  db_quote("AND company_id = ?i ", Registry::get('runtime.company_id'));
    }

    if ($active) {
        $condition .=  db_quote("AND status = 'A'");
    }

    $providers_list = db_get_hash_array("SELECT * FROM ?:hybrid_auth_providers WHERE 1 ?p ORDER BY `position`;", 'provider', $condition);

    foreach ($providers_list as $provider_id => $provider) {
        $providers_list[$provider_id]['params'] = unserialize($provider['app_params']);
    }

    return $providers_list;
}

function fn_hybrid_auth_get_providers_list_content()
{
    $content = fn_hybrid_auth_get_providers_list();

    return !empty($content) ? $content : '&nbsp';
}

function fn_hybrid_auth_get_provider_data($provider)
{
    if (Registry::get('runtime.company_id')) {
        $condition =  db_quote("AND company_id = ?i ", Registry::get('runtime.company_id'));
    }

    $provider_data = db_get_row("SELECT * FROM ?:hybrid_auth_providers WHERE provider = ?s ?p", $provider, $condition);

    $provider_data['params'] = '';
    if (!empty($provider_data['app_params'])) {
        $provider_data['params'] = unserialize($provider_data['app_params']);
    }

    return $provider_data;
}

function fn_hybrid_auth_delete_provider($provider)
{
    if (Registry::get('runtime.company_id')) {
        $condition =  db_quote("AND company_id = ?i ", Registry::get('runtime.company_id'));
    }

    $result = db_query("DELETE FROM ?:hybrid_auth_providers WHERE provider = ?s ?p", $provider, $condition);

    return $result;
}

function fn_hybrid_auth_get_provider_id($provider)
{
    if (Registry::get('runtime.company_id')) {
        $condition =  db_quote("AND company_id = ?i ", Registry::get('runtime.company_id'));
    }

    $provider_id = db_get_field("SELECT provider_id FROM ?:hybrid_auth_providers WHERE provider = ?s ?p", $provider, $condition);

    return $provider_id;
}

function fn_hybrid_auth_get_user_data($auth_data)
{
    $condition = db_quote('?:hybrid_auth_users.identifier = ?s', $auth_data->identifier);

    if (fn_allowed_for('ULTIMATE')) {
        if (Registry::get('settings.Stores.share_users') == 'N' && AREA != 'A') {
            $condition .= fn_get_company_condition('?:users.company_id');
        }
    }

    $join = 'JOIN ?:hybrid_auth_users ON ?:hybrid_auth_users.user_id = ?:users.user_id';

    return db_get_row("SELECT ?:users.user_id, password FROM ?:users ?p WHERE ?p", $join, $condition);
}

function fn_hybrid_auth_create_user($auth_data, $provider)
{
    Registry::get('settings.General.address_position') == 'billing_first' ? $address_zone = 'b' : $address_zone = 's';
    $user_data = array();
    $user_data['email'] = (!empty($auth_data->verifiedEmail)) ? $auth_data->verifiedEmail : ((!empty($auth_data->email)) ? $auth_data->email : '');
    $user_data['user_login'] = (!empty($auth_data->verifiedEmail)) ? $auth_data->verifiedEmail : ((!empty($auth_data->email)) ? $auth_data->email : $auth_data->displayName);
    $user_data['user_type'] = 'C';
    $user_data['is_root'] = 'N';
    $user_data['password'] = $user_data['password1'] = $user_data['password2'] = fn_generate_password();
    $user_data[$address_zone . '_firstname'] = (!empty($auth_data->firstName)) ? $auth_data->firstName : '';
    $user_data[$address_zone . '_lastname'] = (!empty($auth_data->lastName)) ? $auth_data->lastName : '';
    $user_data[$address_zone . '_phone'] = (!empty($auth_data->phone)) ? $auth_data->phone : '';
    $user_data[$address_zone . '_address'] = (!empty($auth_data->address)) ? $auth_data->address : '';
    $user_data[$address_zone . '_country'] = (!empty($auth_data->country)) ? $auth_data->country : '';
    $user_data[$address_zone . '_state'] = (!empty($auth_data->region)) ? $auth_data->region : '';
    $user_data[$address_zone . '_city'] = (!empty($auth_data->city)) ? $auth_data->city : '';
    $user_data[$address_zone . '_zipcode'] = (!empty($auth_data->zip)) ? $auth_data->zip : '';

    list($user_data['user_id'], $profile_id) = fn_update_user('', $user_data, $auth, true, false, false);

    if (!empty($user_data['email'])) {
        Mailer::sendMail(array(
            'to' => $user_data['email'],
            'from' => 'company_orders_department',
            'data' => array(
                'user_data' => $user_data,
                'user_name' => $user_data[$address_zone . '_firstname'] . " " . $user_data[$address_zone . '_lastname'],
            ),
            'tpl' => 'addons/hybrid_auth/create_profile.tpl',
        ), 'C', DESCR_SL);
    }

    return $user_data;
}

function fn_hybrid_auth_link_provider($user_id, $identifier, $provider)
{
    $provider_id = fn_hybrid_auth_get_provider_id($provider);
    $_user_data = array(
        'user_id' => $user_id,
        'provider_id' => $provider_id,
        'identifier' => $identifier,
        'timestamp' => TIME,
    );

    $result = db_query("REPLACE INTO ?:hybrid_auth_users ?e", $_user_data);

    return $result;
}

function fn_hybrid_auth_process($action, &$redirect_url = '')
{
    $hybridauth = fn_hybrid_auth_init();

    if (!$hybridauth) {
        return HYBRID_AUTH_FALSE;
    }

    unset($_SESSION['hybrid_auth']);
    $provider = !empty($_REQUEST['provider']) ? $_REQUEST['provider'] : '';

    if (!empty($provider) && $hybridauth->isConnectedWith($provider)) {

        $auth_data = fn_hybrid_auth_get_auth_data($hybridauth, $provider);

        if (!$auth_data) {
            return HYBRID_AUTH_ERROR_AUTH_DATA;
        }

        fn_hybrid_auth_fix_old_user($auth_data, $provider); // linked users without providers. for compatibility with the old version of the add-on
        $user_data = fn_hybrid_auth_get_user_data($auth_data);

        if ($action == 'login_provider') {
            $redirect_url = fn_hybrid_auth_login($user_data, $auth_data, $provider);

        } elseif ($action == 'link_provider') {
            $redirect_url = fn_hybrid_auth_link($user_data, $auth_data, $provider);

        } elseif ($action == 'link_provider_profile') {
            $redirect_url = fn_hybrid_auth_link_profile($auth_data, $provider);
        }

        $status = HYBRID_AUTH_LOGIN;

    } else {

        if (!empty($provider)) {
            $params = array();

            if ($provider == "OpenID") {
                $params["openid_identifier"] = @ $_REQUEST["openid_identifier"];
            }
        }

        if (!empty($_REQUEST['redirect_to_idp'])) {

            try {
                $adapter = $hybridauth->authenticate($provider, $params);
                $status = HYBRID_AUTH_OK;

            } catch (Exception $e) {
                fn_set_notification('E', __('error'), $e->getMessage());
                $status = HYBRID_AUTH_LOGIN;
            }

        } else {
            Registry::get('view')->assign('provider', $provider);
            $status = HYBRID_AUTH_LOADING;
        }
    }

    return $status;
}

function fn_hybrid_auth_login($user_data, $auth_data, $provider)
{
    if (empty($user_data['user_id'])) {

        if (!empty($auth_data->verifiedEmail)) {
            $email = $auth_data->verifiedEmail;

        } elseif (!empty($auth_data->email)) {
            $email = $auth_data->email;

        } elseif (!empty($_REQUEST['user_email'])) {
            $email = $_REQUEST['user_email'];
            $auth_data->email = $email;

        } elseif (Registry::get('addons.hybrid_auth.autogen_email') == 'Y') {
            $email = $provider . '-' . $auth_data->identifier . '@example.com';
            $auth_data->email = $email;

        } else {
            $email = '';
        }

        if (empty($email)) {
            $user_status = LOGIN_STATUS_NOT_FOUND_EMAIL;

        } else {
            $user_id = fn_is_user_exists(0, array('email' => $email));

            if (empty($user_id)) {
                $user_data = fn_hybrid_auth_create_user($auth_data, $provider);
                fn_hybrid_auth_link_provider($user_data['user_id'], $auth_data->identifier, $provider);

            } else {
                $user_status = LOGIN_STATUS_USER_EXIST;
                $user_data = fn_get_user_info($user_id);
            }
        }
    }

    if (empty($user_status)) {
        if (!empty($user_data['user_id'])) {
            $user_status = fn_login_user($user_data['user_id']);
        } else {
            $user_status = LOGIN_STATUS_USER_NOT_FOUND;
        }
    }

    $redirect_url = (!empty($_REQUEST['redirect_url'])) ? $_REQUEST['redirect_url'] : fn_url();

    if ($user_status == LOGIN_STATUS_USER_DISABLED) {
        fn_set_notification('E', __('error'), __('error_account_disabled'));

    } elseif ($user_status == LOGIN_STATUS_USER_NOT_FOUND) {
        fn_delete_notification('user_exist');
        fn_set_notification('W', __('warning'), __('hybrid_auth.cant_create_profile'));

    } elseif ($user_status == LOGIN_STATUS_USER_EXIST) {

        $_SESSION['hybrid_auth']['email'] = $user_data['email'];
        $_SESSION['hybrid_auth']['identifier'] = $auth_data->identifier;
        $_SESSION['hybrid_auth']['provider'] = $provider;
        $_SESSION['hybrid_auth']['redirect_url'] = $redirect_url;

        $redirect_url = 'auth.connect_social';

    } elseif ($user_status == LOGIN_STATUS_NOT_FOUND_EMAIL) {

        $_SESSION['hybrid_auth']['identifier'] = $auth_data->identifier;
        $_SESSION['hybrid_auth']['provider'] = $provider;
        $_SESSION['hybrid_auth']['redirect_url'] = $redirect_url;

        $redirect_url = 'auth.specify_email';
    }

    return $redirect_url;
}

function fn_hybrid_auth_link($user_data, $auth_data, $provider)
{
    if (empty($user_data['user_id'])) {
        fn_hybrid_auth_link_provider($user_data['user_id'], $auth_data->identifier, $provider);
    }

    $user_status = (empty($user_data['user_id'])) ? LOGIN_STATUS_USER_NOT_FOUND : fn_login_user($user_data['user_id']);

    $redirect_url = (!empty($_REQUEST['redirect_url'])) ? $_REQUEST['redirect_url'] : fn_url();

    if ($user_status == LOGIN_STATUS_USER_DISABLED) {
        fn_set_notification('E', __('error'), __('error_account_disabled'));

    } elseif ($user_status == LOGIN_STATUS_USER_NOT_FOUND) {
        fn_delete_notification('user_exist');
        fn_set_notification('W', __('warning'), __('hybrid_auth.cant_create_profile'));
    }

    return $redirect_url;
}

function fn_hybrid_auth_link_profile($auth_data, $provider)
{
    if (!fn_hybrid_auth_is_exist($auth_data)) {
        if (!empty($_SESSION['auth']['user_id'])) {
            fn_hybrid_auth_link_provider($_SESSION['auth']['user_id'], $auth_data->identifier, $provider);
        }
    } else {
        fn_set_notification('W', __('notice'), __("text_hybrid_auth.user_is_already_link"));
    }

    $redirect_url = (!empty($_REQUEST['return_url'])) ? $_REQUEST['return_url'] : fn_url('profiles.update');

    return $redirect_url;
}

function fn_hybrid_auth_is_exist($auth_data)
{
    $user_data = db_get_row("SELECT user_id FROM ?:hybrid_auth_users WHERE identifier = ?s", $auth_data->identifier);

    return !empty($user_data);
}

function fn_hybrid_auth_fix_old_user($auth_data, $provider)
{
    $user_data = db_get_row("SELECT user_id FROM ?:hybrid_auth_users WHERE identifier = ?s AND provider_id = 0", $auth_data->identifier);

    if (!empty($user_data)) {
        $provider_id = fn_hybrid_auth_get_provider_id($provider);
        db_query("UPDATE ?:hybrid_auth_users SET provider_id = ?i WHERE user_id = ?i", $provider_id, $user_data['user_id']);
    }
}
