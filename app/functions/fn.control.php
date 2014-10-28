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

use Tygh\Exceptions\InputException;
use Tygh\Exceptions\DeveloperException;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Debugger;
use Tygh\Session;
use Tygh\BlockManager\Location;
use Tygh\BlockManager\SchemesManager;
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

define('GET_CONTROLLERS', 1);
define('GET_PRE_CONTROLLERS', 2);
define('GET_POST_CONTROLLERS', 3);

/**
 * Set hook to use by addons
 *
 * @param mixed $argN argument, passed to addon
 * @return boolean always true
 */
function fn_set_hook($arg0 = NULL, &$arg1 = NULL, &$arg2 = NULL, &$arg3 = NULL, &$arg4 = NULL, &$arg5 = NULL, &$arg6 = NULL, &$arg7 = NULL, &$arg8 = NULL, &$arg9 = NULL, &$arg10 = NULL, &$arg11 = NULL, &$arg12 = NULL, &$arg13 = NULL, &$arg14 = NULL, &$arg15 = NULL)
{
    $hooks = Registry::get('hooks');
    static $callable_functions;
    static $hooks_already_sorted;

    for ($args = array(), $i = 0; $i < 16; $i++) {
        $name = 'arg' . $i;
        if ($i < func_num_args()) {
            $args[$i] = &$$name;
        }
        unset($$name, $name);
    }

    $hook_name = array_shift($args);

    // Check for the core functions
    $core_func = 'fn_core_' . $hook_name;
    if (is_callable($core_func)) {
        call_user_func_array($core_func, $args);
    }

    $edition_acronym = fn_get_edition_acronym(PRODUCT_EDITION);
    if (!empty($edition_acronym)) {
        $edition_hook_func = "fn_{$edition_acronym}_{$hook_name}";
        if (function_exists($edition_hook_func)) {
            call_user_func_array($edition_hook_func, $args);
        }
    }

    if (isset($hooks[$hook_name])) {
        // cache hooks sorting
        if (!isset($hooks_already_sorted[$hook_name])) {
            $hooks[$hook_name] = fn_sort_array_by_key($hooks[$hook_name], 'priority');
            $hooks_already_sorted[$hook_name] = true;
            Registry::set('hooks', $hooks, true);
        }

        foreach ($hooks[$hook_name] as $callback) {
            // cache if hook function callable
            if (!isset($callable_functions[$callback['func']])) {
                if (!is_callable($callback['func'])) {
                    throw new DeveloperException("Hook $callback[func] is not callable");

                }
                $callable_functions[$callback['func']] = true;
            }
            call_user_func_array($callback['func'], $args);
        }
    }

    return true;
}

/**
 * Register hooks addon uses
 *
 * @return boolean always true
 */
function fn_register_hooks()
{
    $args = func_get_args();
    $backtrace = debug_backtrace();

    $addon_path = fn_unified_path($backtrace[0]['file']);

    $path_dirs = explode('/', $addon_path);
    array_pop($path_dirs);
    $addon_name = array_pop($path_dirs);

    $hooks = Registry::get('hooks');

    $addon_priority = Registry::get('addons.' . $addon_name . '.priority');
    foreach ($args as &$hook) {
        $priority = $addon_priority;
        $addon = $addon_name;

        // if we get array we need to set priority manually
        if (is_array($hook)) {
            if (isset($hook[2])) {
                $addon = $hook[2];
                if (Registry::get('addons.' . $addon . '.status') != 'A') { // skip hook registration if addon is not enabled
                    continue;
                }
                if ($priority === '') {
                    $priority = Registry::get('addons.' . $addon . '.priority');
                }
            }

            $priority = $hook[1];
            $hook = $hook[0];
        }

        $callback = 'fn_' . $addon . '_' . $hook;

        if (!isset($hooks[$hook])) {
            $hooks[$hook] = array();
        }

        $hooks[$hook][] = array('func' => $callback, 'addon' => $addon, 'priority' => $priority);
    }

    Registry::set('hooks', $hooks, true);

    return true;
}

/**
 * Gets list of secure controllers which use https connection
 *
 * @return array list of secure controllers
 */
function fn_get_secure_controllers()
{
    $secure_controllers = array(
        'payment_notification' => 'passive',
        'image' => 'passive',
    );

    if (Registry::get('settings.Security.secure_auth') == 'Y') {
        $secure_controllers = array_merge($secure_controllers, array(
            'auth' => 'active',
            'orders' => 'active',
            'profiles' => 'active',
        ));
    }

    if (Registry::get('settings.Security.secure_checkout') == 'Y') {
        $secure_controllers = array_merge($secure_controllers, array(
            'checkout' => 'active',
        ));
    }

    fn_set_hook('init_secure_controllers', $secure_controllers);

    return $secure_controllers;
}

/**
 * Dispathes the execution control to correct controller
 *
 * @return nothing
 */
function fn_dispatch($controller = '', $mode = '', $action = '', $dispatch_extra = '', $area = AREA)
{
    Debugger::checkpoint('After init');

    fn_set_hook('before_dispatch');

    $controller = empty($controller) ? Registry::get('runtime.controller') : $controller;
    $mode = empty($mode) ? Registry::get('runtime.mode') : $mode;
    $action = empty($action) ? Registry::get('runtime.action') : $action;
    $dispatch_extra = empty($dispatch_extra) ? Registry::get('runtime.dispatch_extra') : $dispatch_extra;

    $regexp = "/^[a-zA-Z0-9_\+]+$/";
    if (!preg_match($regexp, $controller) || !preg_match($regexp, $mode)) {
        throw new InputException('Error processing request');
    }

    $view = Registry::get('view');
    $run_controllers = true;
    $external = false;
    $status = CONTROLLER_STATUS_NO_PAGE;

    // Security
    if (Registry::get('config.tweaks.anti_csrf') == true) {
        $trusted_csrf_controllers = array(
            'auth'
        );
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && !in_array($controller, $trusted_csrf_controllers) && (empty($_SESSION['security_hash']) || empty($_REQUEST['security_hash']) || $_REQUEST['security_hash'] != $_SESSION['security_hash'])) {
            fn_set_notification('E', __('error'), __('text_csrf_attack'));
            fn_redirect(fn_url());
        }
    }

    // If $config['http_host'] was different from the domain name, there was redirection to $config['http_host'] value.
    if (Registry::get('config.current_host') != REAL_HOST && $_SERVER['REQUEST_METHOD'] == 'GET' && !defined('CONSOLE')) {
        if (!empty($_SERVER['REDIRECT_URL'])) {
            $qstring = $_SERVER['REDIRECT_URL'];
        } else {
            if (!empty($_SERVER['REQUEST_URI'])) {
                $qstring = $_SERVER['REQUEST_URI'];
            } else {
                $qstring = Registry::get('config.current_url');
            }
        }

        $curent_path = Registry::get('config.current_path');
        if (!empty($curent_path) && strpos($qstring, $curent_path) === 0) {
            $qstring = substr_replace($qstring, '', 0, fn_strlen($curent_path));
        }

        fn_redirect(Registry::get('config.current_location') . $qstring, false, true);
    }

    if (isset($_SERVER['CONTENT_LENGTH']) && ($_SERVER['CONTENT_LENGTH'] > fn_return_bytes(ini_get('upload_max_filesize')) || $_SERVER['CONTENT_LENGTH'] > fn_return_bytes(ini_get('post_max_size')))) {
        $max_size = fn_return_bytes(ini_get('upload_max_filesize')) < fn_return_bytes(ini_get('post_max_size')) ? ini_get('upload_max_filesize') : ini_get('post_max_size');

        fn_set_notification('E', __('error'), __('text_forbidden_uploaded_file_size', array(
            '[size]' => $max_size
        )));
        fn_redirect($_SERVER['HTTP_REFERER']);
    }

    // If URL contains session ID, remove it
    if (!defined('AJAX_REQUEST') && !empty($_REQUEST[Session::getName()]) && $_SERVER['REQUEST_METHOD'] == 'GET') {
        fn_redirect(fn_query_remove(Registry::get('config.current_url'), Session::getName()));
    }

    // If demo mode is enabled, check permissions FIX ME - why did we need one more user login check?
    if ($area == 'A') {
        if (Registry::get('config.demo_mode') == true) {
            $run_controllers = fn_check_permissions($controller, $mode, 'demo');

            if ($run_controllers == false) {
                fn_set_notification('W', __('demo_mode'), __('demo_mode_content_text'), 'K', 'demo_mode');
                if (defined('AJAX_REQUEST')) {
                    exit;
                }

                fn_delete_notification('changes_saved');

                $status = CONTROLLER_STATUS_REDIRECT;
                $_REQUEST['redirect_url'] = !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : fn_url('');
            }

        } else {
            $run_controllers = fn_check_permissions($controller, $mode, 'admin', '', $_REQUEST);
            if ($run_controllers == false) {
                if (defined('AJAX_REQUEST')) {
                    $_info = (Debugger::isActive() || defined('DEVELOPMENT')) ? ' ' . $controller . '.' . $mode : '';
                    fn_set_notification('W', __('warning'), __('access_denied') . $_info);
                    exit;
                }
                $status = CONTROLLER_STATUS_DENIED;
            }
        }
    }

    if ($area == 'A' && (Registry::get('settings.Security.secure_admin') == 'Y')  && !defined('HTTPS') && ($_SERVER['REQUEST_METHOD'] != 'POST') && !defined('AJAX_REQUEST') && empty($_REQUEST['keep_location']) && !defined('CONSOLE')) {
        fn_redirect(Registry::get('config.https_location') . '/' . Registry::get('config.current_url'));
    } elseif ($area == 'C' && $_SERVER['REQUEST_METHOD'] != 'POST' && !defined('AJAX_REQUEST')) {
        $secure_controllers = fn_get_secure_controllers();
        // if we are not on https but controller is secure, redirect to https
        if (isset($secure_controllers[$controller]) && $secure_controllers[$controller] == 'active' && !defined('HTTPS')) {
            fn_redirect(Registry::get('config.https_location') . '/' . Registry::get('config.current_url'));
        }

        // if we are on https and the controller is insecure, redirect to http
        if (!isset($secure_controllers[$controller]) && defined('HTTPS') && Registry::get('settings.Security.keep_https') != 'Y') {
            fn_redirect('http://' . Registry::get('config.http_host') . Registry::get('config.http_path') . '/' . Registry::get('config.current_url'));
        }
    }

    LastView::instance()->prepare($_REQUEST);

    $controllers_cascade = array();
    $controllers_list = array('init');
    if ($run_controllers == true) {
        $controllers_list[] = $controller;
        $controllers_list = array_unique($controllers_list);
    }
    foreach ($controllers_list as $ctrl) {
        $core_controllers = fn_init_core_controllers($ctrl);
        list($addon_controllers) = fn_init_addon_controllers($ctrl);

        if (empty($core_controllers) && empty($addon_controllers)) {
            //$controllers_cascade = array(); // FIXME: controllers_cascade contains INIT. We should not clear initiation code.
            $status = CONTROLLER_STATUS_NO_PAGE;
            $run_controllers = false;
            break;
        }

        if ((count($core_controllers) + count($addon_controllers)) > 1) {
            throw new DeveloperException('Duplicate controller ' . $controller . var_export(array_merge($core_controllers, $addon_controllers), true));
        }

        $core_pre_controllers = fn_init_core_controllers($ctrl, GET_PRE_CONTROLLERS);
        $core_post_controllers = fn_init_core_controllers($ctrl, GET_POST_CONTROLLERS);

        list($addon_pre_controllers) = fn_init_addon_controllers($ctrl, GET_PRE_CONTROLLERS);
        list($addon_post_controllers, $addons) = fn_init_addon_controllers($ctrl, GET_POST_CONTROLLERS);

        // we put addon post-controller to the top of post-controller cascade if current addon serves this request
        if (count($addon_controllers)) {
            $addon_post_controllers = fn_reorder_post_controllers($addon_post_controllers, $addon_controllers[0]);
        }

        $controllers_cascade = array_merge($controllers_cascade, $addon_pre_controllers, $core_pre_controllers, $core_controllers, $addon_controllers, $core_post_controllers, $addon_post_controllers);

        if (empty($controllers_cascade)) {
            throw new DeveloperException("No controllers for: $ctrl");
        }
    }

    if ($mode == 'add') {
        $tpl = 'update.tpl';
    } elseif (strpos($mode, 'add_') === 0) {
        $tpl = str_replace('add_', 'update_', $mode) . '.tpl';
    } else {
        $tpl = $mode . '.tpl';
    }

    $view = Registry::get('view');
    if ($view->templateExists('views/' . $controller . '/' . $tpl)) { // try to find template in base views
        $view->assign('content_tpl', 'views/' . $controller . '/' . $tpl);
    } elseif (defined('LOADED_ADDON_PATH') && $view->templateExists('addons/' . LOADED_ADDON_PATH . '/views/' . $controller . '/' . $tpl)) { // try to find template in addon views
        $view->assign('content_tpl', 'addons/' . LOADED_ADDON_PATH . '/views/' . $controller . '/' . $tpl);
    } elseif (!empty($addons)) { // try to find template in addon views that extend base views
        foreach ($addons as $addon => $_v) {
            if ($view->templateExists('addons/' . $addon . '/views/' . $controller . '/' . $tpl)) {
                $view->assign('content_tpl', 'addons/' . $addon . '/views/' . $controller . '/' . $tpl);
                break;
            }
        }
    }

    fn_set_hook('dispatch_assign_template', $controller, $mode, $area);

    foreach ($controllers_cascade as $item) {
        $_res = fn_run_controller($item, $controller, $mode, $action, $dispatch_extra); // 0 - status, 1 - url

        $url = !empty($_res[1]) ? $_res[1] : '';
        $external = !empty($_res[2]) ? $_res[2] : false;
        $permanent = !empty($_res[3]) ? $_res[3] : false;

        // Status could be changed only if we allow to run controllers despite of init controller
        if ($run_controllers == true) {
            $status = !empty($_res[0]) ? $_res[0] : CONTROLLER_STATUS_OK;
        }

        if ($status == CONTROLLER_STATUS_OK && !empty($url)) {
            $redirect_url = $url;
        } elseif ($status == CONTROLLER_STATUS_REDIRECT && !empty($url)) {
            $redirect_url = $url;
            break;
        } elseif ($status == CONTROLLER_STATUS_DENIED || $status == CONTROLLER_STATUS_NO_PAGE) {
            break;
        }
    }

    LastView::instance()->init($_REQUEST);

    // In console mode, just stop here
    if (defined('CONSOLE')) {
        exit;
    }

    if (!empty($_SESSION['auth']['this_login']) && Registry::ifGet($_SESSION['auth']['this_login'], 'N') === 'Y') {
        fn_set_notification('E', __('error'), __(ACCOUNT_TYPE . LOGIN_STATUS_USER_DISABLED));
        $status = CONTROLLER_STATUS_DENIED;
    }

    // [Block manager]
    // block manager is disabled for vendors.
    if (!(
        (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id'))
        ||
        (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id'))
    )) {
        if (fn_check_permissions('block_manager', 'manage', 'admin')) {
            $dynamic_object = SchemesManager::getDynamicObject($_REQUEST['dispatch'], $area);
            if (!empty($dynamic_object)) {
                if ($area == 'A' && Registry::get('runtime.mode') != 'add' && !empty($_REQUEST[$dynamic_object['key']])) {
                    $object_id = $_REQUEST[$dynamic_object['key']];
                    $location = Location::instance()->get($dynamic_object['customer_dispatch'], $dynamic_object, CART_LANGUAGE);

                    if (!empty($location) && $location['is_default'] != 1) {
                        $params = array(
                            'dynamic_object' => array(
                                'object_type' => $dynamic_object['object_type'],
                                'object_id' => $object_id
                            ),
                            $dynamic_object['key'] => $object_id,
                            'manage_url' => Registry::get('config.current_url')
                        );

                        Registry::set('navigation.tabs.blocks', array(
                            'title' => __('layouts'),
                            'href' => 'block_manager.manage_in_tab?' . http_build_query($params),
                            'ajax' => true,
                        ));
                    }
                }
            }
        }
    }
    // [/Block manager]

    // Redirect if controller returned successful/redirect status only
    if (in_array($status, array(CONTROLLER_STATUS_OK, CONTROLLER_STATUS_REDIRECT)) && !empty($_REQUEST['redirect_url']) && !$external) {
        $redirect_url = $_REQUEST['redirect_url'];
    }

    // If controller returns "Redirect" status, check if redirect url exists
    if ($status == CONTROLLER_STATUS_REDIRECT && empty($redirect_url)) {
        $status = CONTROLLER_STATUS_NO_PAGE;
    }

    // In backend show "changes saved" notification
    if ($area == 'A' && $_SERVER['REQUEST_METHOD'] == 'POST' && in_array($status, array(CONTROLLER_STATUS_OK, CONTROLLER_STATUS_REDIRECT))) {
        if (strpos($mode, 'update') !== false && !fn_notification_exists('extra', 'demo_mode') && !fn_notification_exists('type', 'E')) {
            fn_set_notification('N', __('notice'), __('text_changes_saved'), 'I', 'changes_saved');
        }
    }

    // Attach params and redirect if needed
    if (in_array($status, array(CONTROLLER_STATUS_OK, CONTROLLER_STATUS_REDIRECT)) && !empty($redirect_url)) {
        $params = array (
            'page',
            'selected_section',
            'active_tab'
        );

        $url_params = array();
        foreach ($params as $param) {
            if (!empty($_REQUEST[$param])) {
                $url_params[$param] = $_REQUEST[$param];
            }
        }

        if (!empty($url_params)) {
            $redirect_url = fn_link_attach($redirect_url, http_build_query($url_params));
        }

        if (!isset($external)) {
            $external = false;
        }

        if (!isset($permanent)) {
            $permanent = false;
        }
        fn_redirect($redirect_url, $external, $permanent);
    }

    if (!$view->getTemplateVars('content_tpl') && $status == CONTROLLER_STATUS_OK) { // FIXME
        $status = CONTROLLER_STATUS_NO_PAGE;
    }

    if ($status != CONTROLLER_STATUS_OK) {

        if ($status == CONTROLLER_STATUS_NO_PAGE) {
            if ($area == 'A' && empty($_SESSION['auth']['user_id'])) {
                // If admin is not logged in redirect to login page from not found page
                fn_set_notification('W', __('page_not_found'), __('page_not_found_text'));
                fn_redirect("auth.login_form");
            }

            header(' ', true, 404);
        }
        $view->assign('exception_status', $status);
        if ($area == 'A') {
            $view->assign('content_tpl', 'exception.tpl'); // for backend only
        }
        if ($status == CONTROLLER_STATUS_DENIED) {
            $view->assign('page_title', __('access_denied'));
        } elseif ($status == CONTROLLER_STATUS_NO_PAGE) {
            $view->assign('page_title', __('page_not_found'));
        }
    }

    fn_set_hook('dispatch_before_display');

    Debugger::checkpoint('Before TPL');

    // Pass current URL to ajax response only if we render whole page
    if (defined('AJAX_REQUEST') && Registry::get('runtime.root_template') == 'index.tpl') {
        Registry::get('ajax')->assign('current_url', fn_url(Registry::get('config.current_url'), $area, 'current'));
    }

    Registry::get('view')->display(Registry::get('runtime.root_template'));
    Debugger::checkpoint('After TPL');
    Debugger::display();

    fn_set_hook('complete');

    exit; // stop execution
}

/**
 * Puts the addon post-controller to the top of post-controllers cascade if current addon serves this request
 *
 * @param array $addon_post_controllers post controllers from addons
 * @param array $current_controller current controllers list
 * @return array controllers list
 */
function fn_reorder_post_controllers($addon_post_controllers, $current_controller)
{
    if (empty($addon_post_controllers) || empty($current_controller)) {
        return $addon_post_controllers;
    }

    // get addon name from the path like /var/www/html/cart/app/addons/[addon]/controllers/backend/[controller].php
    $part = substr($current_controller, strlen(Registry::get('config.dir.addons')));
    // we have [addon]/controllers/backend/[controller].php in the $part
    $addon_name = substr($part, 0, strpos($part, '/'));

    // we search post-controller of the addon that owns active controller of current request
    // and if we find it we put this post-controller to the top of the cascade
    foreach ($addon_post_controllers as $k => $post_controller) {
        if (strpos($post_controller, Registry::get('config.dir.addons') . $post_controller) !== false) {
            // delete in current place..
            unset($addon_post_controllers[$k]);
            // and put at the beginning
            array_unshift($addon_post_controllers, $post_controller);
            break; // only one post controller can be here
        }
    }

    return $addon_post_controllers;
}

/**
 * Runs specified controller by including its file
 *
 * @param string $path path to controller
 * @return array controller return status
 */
function fn_run_controller($path, $controller, $mode, $action, $dispatch_extra)
{
    static $check_included = array();

    $auth = & $_SESSION['auth'];

    //TODO Remove in 3.2.1
    $ajax = new Tygh\SmartyEngine\ViewDeprecated('ajax');
    $view = new Tygh\SmartyEngine\ViewDeprecated('view');

    if (!empty($check_included[$path])) {
        $code = fn_get_contents($path);
        $code = str_replace(array('function fn', '<?php', '?>'), array('function _fn', '', ''), $code);

        return eval($code);

    } else {
        $check_included[$path] = true;

        return include($path);
    }
}

/**
 * Generates list of core (pre/post)controllers
 *
 * @param string $controller controller name
 * @param string $type controller type (pre/post)
 * @return array controllers list
 */
function fn_init_core_controllers($controller, $type = GET_CONTROLLERS, $area = AREA)
{
    $controllers = array();

    $prefix = '';
    $area_name = fn_get_area_name($area);

    if ($type == GET_POST_CONTROLLERS) {
        $prefix = '.post';
    } elseif ($type == GET_PRE_CONTROLLERS) {
        $prefix = '.pre';
    }

    // try to find area-specific controller
    if (is_readable(Registry::get('config.dir.root') . '/app/controllers/' . $area_name . '/' . $controller . $prefix . '.php')) {
        $controllers[] = Registry::get('config.dir.root') . '/app/controllers/' . $area_name . '/' . $controller . $prefix . '.php';
    }

    // try to find common controller
    if (is_readable(Registry::get('config.dir.root') . '/app/controllers/common/' . $controller . $prefix . '.php')) {
        $controllers[] = Registry::get('config.dir.root') . '/app/controllers/common/' . $controller . $prefix . '.php';
    }

    return $controllers;
}

/**
 * Generates list of (pre/post)controllers from active addons
 *
 * @param string $controller controller name
 * @param string $type controller type (pre/post)
 * @return array controllers list and active addons
 */
function fn_init_addon_controllers($controller, $type = GET_CONTROLLERS, $area = AREA)
{
    $controllers = array();
    static $addons = array();
    $prefix = '';
    $area_name = fn_get_area_name($area);

    if ($type == GET_POST_CONTROLLERS) {
        $prefix = '.post';
    } elseif ($type == GET_PRE_CONTROLLERS) {
        $prefix = '.pre';
    }

    foreach ((array) Registry::get('addons') as $addon_name => $data) {
        if ($data['status'] == 'A') {
            // try to find area-specific controller
            $dir = Registry::get('config.dir.addons') . $addon_name . '/controllers/' . $area_name . '/';
            if (is_readable($dir . $controller . $prefix . '.php')) {
                $controllers[] = $dir . $controller . $prefix . '.php';
                $addons[$addon_name] = true;
                if (empty($prefix)) {
                    fn_define('LOADED_ADDON_PATH', $addon_name);
                }
            }

            // try to find common controller
            $dir = Registry::get('config.dir.addons') . $addon_name . '/controllers/common/';
            if (is_readable($dir . $controller . $prefix . '.php')) {
                $controllers[] = $dir . $controller . $prefix . '.php';
                $addons[$addon_name] = true;
                if (empty($prefix)) {
                    fn_define('LOADED_ADDON_PATH', $addon_name);
                }
            }
        }
    }

    return array($controllers, $addons);
}

/**
 * Looks for "dispatch" parameter in REQUEST array and extracts controller, mode, action and extra parameters.
 *
 * @param array $req Request parameters
 * @param string $area Area
 * @return boolean always true
 */
function fn_get_route(&$req, $area = AREA)
{
    $result = array(INIT_STATUS_OK);

    $is_allowed_url = fn_check_requested_url();

    fn_set_hook('get_route', $req, $result, $area, $is_allowed_url);

    if (!$is_allowed_url) {

        $current_path = Registry::get('config.current_path');
        $clean_uri = substr($_SERVER['REQUEST_URI'], strlen($current_path) + 1);

        $images_substring = '/thumbnails/';
        if (strpos($clean_uri, $images_substring) !== false) {
            list(, $clean_uri) = explode($images_substring, $clean_uri);
            if (preg_match("/^(\d+)[\/]?(\d+)?\/(.*)$/", $clean_uri, $m)) {
                $req['dispatch'] = 'image.thumbnail';
                $req['w'] = $m[1];
                $req['h'] = $m[2];
                $req['image_path'] = $m[3];

                $is_allowed_url = true;
            }
        }

        if (!$is_allowed_url) {
            $req = array(
                'dispatch' => '_no_page'
            );
        }
    }

    if (!empty($req['dispatch'])) {
        $dispatch = is_array($req['dispatch']) ? key($req['dispatch']) : $req['dispatch'];
    } else {
        $dispatch = 'index.index';
    }

    rtrim($dispatch, '/.');
    $dispatch = str_replace('/', '.', $dispatch);

    @list($c, $m, $a, $e) = explode('.', $dispatch);

    Registry::set('runtime.controller', empty($c) ? 'index' : $c);
    Registry::set('runtime.mode', empty($m) ? 'index' : $m);
    Registry::set('runtime.action', $a);
    Registry::set('runtime.dispatch_extra', $e);
    Registry::set('runtime.checkout', false);
    Registry::set('runtime.root_template', 'index.tpl');

    $req['dispatch'] = $dispatch;

    // URL's assignments
    Registry::set('config.current_url', fn_url_remove_service_params(Registry::get('config.' . ACCOUNT_TYPE . '_index') . ((!empty($_SERVER['QUERY_STRING'])) ? '?' . $_SERVER['QUERY_STRING'] : '')));

    return $result;
}

/**
 * Parse addon options
 *
 * @param string $options serialized options
 * @return array parsed options list
 */
function fn_parse_addon_options($options)
{
    $options = unserialize($options);
    if (!empty($options)) {
        foreach ($options as $k => $v) {
            if (strpos($v, '#M#') === 0) {
                parse_str(str_replace('#M#', '', $v), $options[$k]);
            }
        }
    }

    return $options;
}

/**
 * Get list of templates that should be overridden by addons
 *
 * @param  string $resource_name    Base template name
 * @param  Smarty $view             Templater object
 *
 * @return string Overridden template name
 */
function fn_addon_template_overrides($resource_name, &$view)
{
    static $init = array();

    $o_name = 'template_overrides_' . AREA;
    $template_dir = ($view == null) ? '' : rtrim($view->getTemplateDir(0), '/').'/';

    if (!isset($init[$o_name])) {
        Registry::registerCache($o_name, array('addons'), Registry::cacheLevel('static'));

        if (!Registry::isExist($o_name)) {
            $template_overrides = array();

            foreach (Registry::get('addons') as $a => $_settings) {
                $odir =  $template_dir . 'addons/' . $a . '/overrides';
                if ($_settings['status'] == 'A' && is_dir($odir)) {
                    $tpls = fn_get_dir_contents($odir, false, true, '', '', true);

                    foreach ($tpls as $k => $t) {
                        $tpl_hash = md5($t);
                        if (empty($template_overrides[$tpl_hash])) {
                            $template_overrides[$tpl_hash] = $template_dir . 'addons/' . $a . '/overrides/' . $t;
                        }
                    }
                }
            }

            if (empty($template_overrides)) {
                $template_overrides['plug'] = true;
            }

            Registry::set($o_name, $template_overrides);
        }

        $init[$o_name] = true;
    }

    return Registry::ifGet($o_name . '.' . md5($resource_name), $resource_name);
}

/**
 * Check if functionality is available for the edition
 *
 * @param string $editions Allowed editions ('ULTIMATE,MULTIVENDOR')
 * @return bool true if available
 */
function fn_allowed_for($editions)
{
    if ($editions == 'TRUNK') {
        return true;
    }

    $is_allowed = false;

    $_mode = fn_get_storage_data('store_mode');

    if ($_mode == 'free') {
        $store_mode = ':FREE';
        $extra = '';

    } elseif ($_mode == 'full') {
        $store_mode = ':FULL';
        $extra = '';

    } else {
        $store_mode = ':FULL';
        $extra = ':TRIAL';
    }

    $editions = explode(',', $editions);

    foreach ($editions as $edition) {
        if (strpos($edition, ':') !== false) {

            if ($edition == PRODUCT_EDITION . $store_mode || $edition == PRODUCT_EDITION . $store_mode . $extra) {
                $is_allowed = true;
                break;
            }


        } elseif ($edition == PRODUCT_EDITION) {
            $is_allowed = true;
            break;
        }
    }

    return $is_allowed;
}

/**
 * Puts data to storage
 * @param string $key key
 * @param string $data data
 * @return integer data ID
 */
function fn_set_storage_data($key, $data = '')
{
    $data_id = 0;
    if (!empty($data)) {
        $data_id = db_query('REPLACE ?:storage_data (`data_key`, `data`) VALUES(?s, ?s)', $key, $data);
        Registry::set('storage_data.' . $key, $data);
    } else {
        db_query('DELETE FROM ?:storage_data WHERE `data_key` = ?s', $key);
        Registry::del('storage_data.' . $key);
    }

    return $data_id;
}

/**
 * Gets data from storage
 * @param string $key key
 * @return mixed key value
 */
function fn_get_storage_data($key)
{
    if (!Registry::isExist('storage_data.' . $key)) {
        Registry::set('storage_data.' . $key, db_get_field('SELECT `data` FROM ?:storage_data WHERE `data_key` = ?s', $key));
    }

    return Registry::get('storage_data.' . $key);
}

/**
 * Checks is some key is expired (value of given key should be timestamp).
 *
 * @param string $key Key name
 * @param int $time_period Time period (in seconds), that should be added to the current timestamp for the future check.
 * @return boolean True, if saved timestamp is less than current timestamp, false otherwise.
 */
function fn_is_expired_storage_data($key, $time_period = null)
{
    $time = fn_get_storage_data($key);
    if ($time < TIME && $time_period) {
        fn_set_storage_data($key, TIME + $time_period);
    }

    return $time < TIME;
}


/**
 * Removes service parameters from URL
 * @param string $url URL
 * @return string clean URL
 */
function fn_url_remove_service_params($url)
{
    $params = array(
        'is_ajax',
        'callback',
        'full_render',
        'result_ids',
        'init_context',
        'skip_result_ids_check',
        'anchor',
        Session::getName()
    );

    array_unshift($params, $url);

    return call_user_func_array('fn_query_remove', $params);
}
