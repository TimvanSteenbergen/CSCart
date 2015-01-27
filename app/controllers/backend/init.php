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
use Tygh\BackendMenu;
use Tygh\Navigation\Breadcrumbs;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

Registry::get('view')->assign('descr_sl', DESCR_SL);

if (!empty($auth['user_id']) && $auth['area'] != AREA) {
    $auth = array();

    return array(CONTROLLER_STATUS_REDIRECT, fn_url());
}

if (empty($auth['user_id']) && !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'trusted_controllers')) {
    if (Registry::get('runtime.controller') != 'index') {
        fn_set_notification('E', __('access_denied'), __('error_not_logged'));

        if (defined('AJAX_REQUEST')) {
            Registry::get('ajax')->assign('force_redirection', fn_url('auth.login_form?return_url=' . urlencode(Registry::get('config.current_url'))));
            exit;
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=" . urlencode(Registry::get('config.current_url')));
} elseif (!empty($auth['user_id']) && !fn_check_user_type_access_rules($auth)) {
    fn_set_notification('E', __('error'), __('error_area_access_denied'));

    return array(CONTROLLER_STATUS_DENIED);
} elseif (!empty($auth['user_id']) && !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'trusted_controllers') && $_SERVER['REQUEST_METHOD'] != 'POST') {
    // PCI DSS Compliance
    $auth['password_change_timestamp'] = !empty($auth['password_change_timestamp']) ? $auth['password_change_timestamp'] : 0;
    $time_diff = TIME - $auth['password_change_timestamp'];
    $expire = Registry::get('settings.Security.admin_password_expiration_period') * SECONDS_IN_DAY;

    if (!isset($auth['first_expire_check'])) {
        $auth['first_expire_check'] = true;
    }

    // We do not need to change the timestamp if this is an Ajax requests
    if (!defined('AJAX_REQUEST')) {
        $_SESSION['auth_timestamp'] = !isset($_SESSION['auth_timestamp']) ? 0 : ++$_SESSION['auth_timestamp'];
    }

    // Make user change the password if:
    // - password has expired
    // - this is the first admin's login and change_admin_password_on_first_login is enabled
    // - this is the first vendor admin's login
    if (($auth['password_change_timestamp'] <= 1 && ((Registry::get('settings.Security.change_admin_password_on_first_login') == 'Y') || (!empty($auth['company_id']) && empty($auth['password_change_timestamp'])))) || ($expire && $time_diff >= $expire)) {

        $_SESSION['auth']['forced_password_change'] = true;

        if ($auth['first_expire_check']) {
            // we can redirect only on first check, else we can corrupt some admin's working processes ( such as ajax requests
            fn_delete_notification('insecure_password');
            $return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : Registry::get('config.current_url');

            return array(CONTROLLER_STATUS_REDIRECT, "auth.password_change?return_url=" . urlencode($return_url));
        } else {
            if (!fn_notification_exists('extra', 'password_expire')) {
                fn_set_notification('E', __('warning'), __('error_password_expired_change', array(
                    '[link]' => fn_url('profiles.update', 'A')
                )), 'S', 'password_expire');
            }
        }
    } else {
        $auth['first_expire_check'] = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (fn_allowed_for('ULTIMATE')) {
        fn_ult_parse_request($_REQUEST);
    }

    return;
}

list($static, $actions, $selected_items) = BackendMenu::instance(
    Registry::get('runtime.controller'),
    Registry::get('runtime.mode')
)->generate($_REQUEST);

Registry::set('navigation', array(
    'static' => $static,
    'dynamic' => array('actions' => $actions),
    'selected_tab' => $selected_items['section'],
    'subsection' => $selected_items['item']
));

if (fn_allowed_for('ULTIMATE')) {
    if (!fn_ult_check_store_permission($_REQUEST, $redirect_controller)) {
        return array(CONTROLLER_STATUS_REDIRECT, $redirect_controller . '.manage');
    }
}

// Navigation is passed in view->display method to allow its modification in controllers
Registry::get('view')->assign('quick_menu', fn_get_quick_menu_data());


// update request history
// save only current and previous page requests in history
if (!defined('AJAX_REQUEST')) {
    $current_dispatch = Registry::get('runtime.controller') . '.' . Registry::get('runtime.mode');
    if (!empty($_SESSION['request_history']['current']['dispatch'])) {
        $hist_dispatch = !empty($_SESSION['request_history']['current']['dispatch']) ? $_SESSION['request_history']['current']['dispatch'] : '';
        if ($hist_dispatch != $current_dispatch) {
            // replace previously saved reuest if new page is opened
            $_SESSION['request_history']['prev'] = $_SESSION['request_history']['current'];
        }
    }
    $_SESSION['request_history']['current'] = array (
        'dispatch' => $current_dispatch,
        'params' => $_REQUEST
    );
}

// generate breadcrumbs
$prev_request = !empty($_SESSION['request_history']['prev']['params']) ? $_SESSION['request_history']['prev']['params'] : array();
$breadcrumbs = Breadcrumbs::instance(Registry::get('runtime.controller'), Registry::get('runtime.mode'), AREA, $_REQUEST, $prev_request)->getLinks();
Registry::get('view')->assign('breadcrumbs', $breadcrumbs);

// Check if we need translate characters to UTF-8 format
$schema = fn_get_schema('literal_converter', 'utf8');
if (isset($schema['need_converting']) && $schema['need_converting']) {
    Registry::get('view')->assign('data', $schema['data']);
}

$schema = fn_get_schema('last_edited_items', 'schema');
$last_items_cnt = LAST_EDITED_ITEMS_COUNT;

if (empty($_SESSION['last_edited_items'])) {
    $stored_items = fn_get_user_additional_data('L');
    $last_edited_items = empty($stored_items) ? array() : $stored_items;
    $_SESSION['last_edited_items'] = $last_edited_items;
} else {
    $last_edited_items = $_SESSION['last_edited_items'];
}

if (!empty($schema[Registry::get('runtime.controller') . '.' . Registry::get('runtime.mode')])) {
    $items_schema = $schema[Registry::get('runtime.controller') . '.' . Registry::get('runtime.mode')];
    if (empty($items_schema['func'])) {
        $c_elm = '';
    } else {
        $c_elm = $items_schema['func'];
        foreach ($c_elm as $k => $v) {
            if (strpos($v, '@') !== false) {
                $ind = str_replace('@', '', $v);
                if (!empty($auth[$ind]) || !empty($_REQUEST[$ind])) {
                    $c_elm[$k] = ($ind == 'user_id' && empty($_REQUEST[$ind])) ? $auth[$ind] : $_REQUEST[$ind];
                }
            }
        }
    }

    $url = Registry::get('config.current_url');

    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.simple_ultimate')) {
        $url = fn_link_attach($url, 'switch_company_id=' . Registry::ifGet('runtime.company_id', 'all'));
        $url = str_replace('&amp;', '&', $url); // FIXME: workaround for fn_link_attach return result
    }

    $last_item = array('func' => $c_elm, 'url' => $url, 'icon' => (empty($items_schema['icon']) ? '' : $items_schema['icon']), 'text' => (empty($items_schema['text']) ? '' : $items_schema['text']));
    $current_hash = fn_crc32(!empty($c_elm) ? implode('', $c_elm) : $items_schema['text']);

    // remove element if it already exists and add it to the end of history
    unset($last_edited_items[$current_hash]);
    $last_edited_items[$current_hash] = $last_item;

    if (count($last_edited_items) > $last_items_cnt) {
        foreach ($last_edited_items as $k => $v) {
            unset($last_edited_items[$k]);
            if (count($last_edited_items) == $last_items_cnt) {
                break;
            }
        }
    }
}

$last_items = array();
if (!empty($last_edited_items)) {
    foreach ($last_edited_items as $hash => $v) {

        if (!empty($current_hash) && $hash == $current_hash) {
            // ignore current page
            continue;
        }

        if (!empty($v['func'])) {
            $func = array_shift($v['func']);
            if (function_exists($func)) {
                $content = call_user_func_array($func, $v['func']);
                if (!empty($content)) {
                    $name = (empty($v['text']) ? '' : __($v['text']) . ': ') . $content;
                    array_unshift($last_items, array('name' => $name, 'url' => $v['url'], 'icon' => $v['icon']));
                } else {
                    unset($last_edited_items[$hash]);
                }
            } else {
                unset($last_edited_items[$hash]);
            }
        } else {
            array_unshift($last_items, array('name' => __($v['text']), 'url' => $v['url'], 'icon' => $v['icon']));
        }
    }
}

Registry::get('view')->assign('last_edited_items', $last_items);

// save changed items history
$_SESSION['last_edited_items'] = $last_edited_items;
fn_save_user_additional_data('L', $last_edited_items);

if (empty($auth['company_id']) && !empty($auth['user_id']) && $auth['area'] == AREA && $auth['is_root']) {
    $messages = fn_get_storage_data('hd_messages');
    if (!empty($messages)) {
        $messages = unserialize($messages);
        foreach ($messages as $message) {
            fn_set_notification($message['type'], $message['title'], $message['text']);
        }

        fn_set_storage_data('hd_messages', '');
    }
}


/* HIDE IT! */
$store_mode = fn_get_storage_data('store_mode');
$store_mode_errors = fn_get_storage_data('store_mode_errors');

$license_number = fn_get_storage_data('store_mode_license');

if (empty($license_number)) {
    $license_number = Settings::instance()->getValue('license_number', 'Upgrade_center');;
}

Registry::get('view')->assign('store_mode_license', $license_number);

if (!Registry::get('runtime.company_id') && Registry::get('runtime.controller') != 'auth' && empty($store_mode) || !empty($store_mode_errors)) {

    Registry::get('view')->assign('show_sm_dialog', true);
    Registry::get('view')->assign('store_mode_errors', unserialize($store_mode_errors));

    fn_set_storage_data('store_mode_errors', null);
    fn_set_storage_data('store_mode_license', null);
}

Registry::get('view')->assign('store_mode', $store_mode);
/* /HIDE IT! */
