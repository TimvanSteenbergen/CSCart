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

use Tygh\BlockManager\Layout;
use Tygh\Development;
use Tygh\Embedded;
use Tygh\Exceptions\InitException;
use Tygh\Registry;
use Tygh\Debugger;
use Tygh\Storage;
use Tygh\Session;
use Tygh\Settings;
use Tygh\Snapshot;
use Tygh\SmartyEngine\Core as SmartyCore;
use Tygh\Ajax;
use Tygh\Api;
use Tygh\Api\Response;
use Tygh\Themes\Styles;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Init template engine
 *
 * @return boolean always true
 */
function fn_init_templater($area = AREA)
{
    $view = new SmartyCore();
    \SmartyException::$escape = false;

    /**
     * Change templater pre-init parameters
     *
     * @param object $view Templater object
     */
    fn_set_hook('init_templater', $view);

    $view->registerResource('tygh', new Tygh\SmartyEngine\FileResource());

    if ($area == 'A' && !empty($_SESSION['auth']['user_id'])) {
        // Auto-tooltips for admin panel
        $view->registerFilter('pre', array('Tygh\SmartyEngine\Filters', 'preFormTooltip'));
    }

    // Customization mode
    if ($area == 'C') {
        $view->registerFilter('pre', array('Tygh\SmartyEngine\Filters', 'preTemplateWrapper'));

        if (Registry::get('runtime.customization_mode.design')) {
            $view->registerFilter('output', array('Tygh\SmartyEngine\Filters', 'outputTemplateIds'));
        }
    }

    if (Registry::get('config.tweaks.anti_csrf') == true) {
        // CSRF form protection
        $view->registerFilter('output', array('Tygh\SmartyEngine\Filters', 'outputSecurityHash'));
    }

    if (Embedded::isEnabled()) {
        $view->registerFilter('output', array('Tygh\SmartyEngine\Filters', 'outputEmbeddedUrl'));
    }

    if (fn_allowed_for('ULTIMATE')) {
        // Enable sharing for objects
        $view->registerFilter('output', array('Tygh\SmartyEngine\Filters', 'outputSharing'));
    }

    // Language variable retrieval optimization
    $view->registerFilter('post', array('Tygh\SmartyEngine\Filters', 'postTranslation'));

    // Live editor mode
    if (Registry::get('runtime.customization_mode.live_editor')) {
        $view->registerFilter('output', array('Tygh\SmartyEngine\Filters', 'outputLiveEditorWrapper'));
    }

    if (Registry::get('settings.General.debugging_console') == 'Y') {
        if (empty($_SESSION['debugging_console']) && !empty($_SESSION['auth']['user_id'])) {
            $user_type = db_get_field("SELECT user_type FROM ?:users WHERE user_id = ?i", $_SESSION['auth']['user_id']);
            if ($user_type == 'A') {
                $_SESSION['debugging_console'] = true;
            }
        }

        if (isset($_SESSION['debugging_console']) && $_SESSION['debugging_console'] == true) {
            error_reporting(0);
            $view->debugging = true;
        }
    }

    $smarty_plugins_dir = $view->getPluginsDir();
    $view->setPluginsDir(Registry::get('config.dir.functions') . 'smarty_plugins');
    $view->addPluginsDir($smarty_plugins_dir);

    $view->error_reporting = E_ALL & ~E_NOTICE;

    $view->registerDefaultPluginHandler(array('Tygh\SmartyEngine\Filters', 'smartyDefaultHandler'));

    $view->setArea($area);
    $view->use_sub_dirs = false;
    $view->compile_check = (Development::isEnabled('compile_check') || Debugger::isActive() || defined('DEVELOPMENT')) ? true : false;
    $view->setLanguage(CART_LANGUAGE);

    $view->assign('ldelim', '{');
    $view->assign('rdelim', '}');

    $view->assign('currencies', Registry::get('currencies'), false);
    $view->assign('primary_currency', CART_PRIMARY_CURRENCY, false);
    $view->assign('secondary_currency', CART_SECONDARY_CURRENCY, false);
    $view->assign('languages', Registry::get('languages'));
    if ($area == 'A') {
        $view->assign('addon_permissions_text', fn_get_addon_permissions_text());
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        $view->assign('localizations', fn_get_localizations(CART_LANGUAGE , true));
        if (defined('CART_LOCALIZATION')) {
            $view->assign('localization', fn_get_localization_data(CART_LOCALIZATION));
        }
    }

    if (defined('THEMES_PANEL')) {
        if (fn_allowed_for('ULTIMATE')) {
            $storefronts = db_get_array('SELECT storefront, company, company_id FROM ?:companies');
            Registry::set('demo_theme.storefronts', $storefronts);
        }
        $view->assign('demo_theme', Registry::get('demo_theme'));
    }

    Registry::set('view', $view);

    /**
     * Change templater parameters
     *
     * @param object $view Templater object
     */
    fn_set_hook('init_templater_post', $view);

    return array(INIT_STATUS_OK);
}

/**
 * Init crypt engine
 *
 * @return boolean always true
 */
function fn_init_crypt()
{
    if (!defined('CRYPT_STARTED')) {
        $crypt = new Crypt_Blowfish(Registry::get('config.crypt_key'));
        Registry::set('crypt', $crypt);

        fn_define('CRYPT_STARTED', true);
    }

    return true;
}

/**
 * Init ajax engine
 *
 * @return boolean true if current request is ajax, false - otherwise
 */
function fn_init_ajax()
{
    if (defined('AJAX_REQUEST')) {
        return array(INIT_STATUS_OK);
    }

    Embedded::init();

    if (Ajax::validateRequest($_REQUEST)) {
        $ajax = new Ajax($_REQUEST);
        Registry::set('ajax', $ajax);
        fn_define('AJAX_REQUEST', true);
    }

    return array(INIT_STATUS_OK);
}

/**
 * Init languages
 *
 * @param array $params request parameters
 * @return boolean always true
 */
function fn_init_language($params, $area = AREA)
{
    $default_language = Registry::get('settings.Appearance.' . fn_get_area_name($area) . '_default_language');

    $show_hidden_languages = $area != 'C' ? true : false;
    $avail_languages = fn_get_avail_languages($area, $show_hidden_languages);

    if (!empty($params['sl']) && !empty($avail_languages[$params['sl']])) {
        fn_define('CART_LANGUAGE', $params['sl']);
    } elseif (!fn_get_session_data('cart_language' . $area) && $_lc = fn_get_browser_language($avail_languages)) {
        fn_define('CART_LANGUAGE', $_lc);
    } elseif (!fn_get_session_data('cart_language' . $area) && !empty($avail_languages[$default_language])) {
        fn_define('CART_LANGUAGE', $default_language);

    } elseif (($_c = fn_get_session_data('cart_language' . $area)) && !empty($avail_languages[$_c])) {
        fn_define('CART_LANGUAGE', $_c);

    } else {
        reset($avail_languages);
        fn_define('CART_LANGUAGE', key($avail_languages));
    }

    // For the backend, set description language
    if (!empty($params['descr_sl']) && !empty($avail_languages[$params['descr_sl']])) {
        fn_define('DESCR_SL', $params['descr_sl']);
        fn_set_session_data('descr_sl', $params['descr_sl'], COOKIE_ALIVE_TIME);
    } elseif (($d = fn_get_session_data('descr_sl')) && !empty($avail_languages[$d])) {
        fn_define('DESCR_SL', $d);
    } else {
        fn_define('DESCR_SL', CART_LANGUAGE);
    }

    if (CART_LANGUAGE != fn_get_session_data('cart_language' . $area)) {
        fn_set_session_data('cart_language' . $area, CART_LANGUAGE, COOKIE_ALIVE_TIME);
    }

    Registry::set('languages', $avail_languages);

    return array(INIT_STATUS_OK);
}

/**
 * Init company data
 * Company data array will be saved in the registry runtime.company_data
 *
 * @param array $params request parameters
 * @return array with init data (init status, redirect url in case of redirect)
 */
function fn_init_company_data($params)
{
    $company_data = array(
        'company' => __('all_vendors'),
    );

    $company_id = Registry::get('runtime.company_id');
    if ($company_id) {
        $company_data = fn_get_company_data($company_id);
    }

    fn_set_hook('init_company_data', $params, $company_id, $company_data);

    Registry::set('runtime.company_data', $company_data);

    return array(INIT_STATUS_OK);
}

/**
 * Init selected company
 * Selected company id will be saved in the registry runtime.company_id
 *
 * @param array $params request parameters
 * @return array with init data (init status, redirect url in case of redirect)
 */
function fn_init_company_id(&$params)
{
    $company_id = 0;
    $available_company_ids = array();
    $result = array(INIT_STATUS_OK);

    if (isset($params['switch_company_id'])) {
        $switch_company_id = intval($params['switch_company_id']);
    } else {
        $switch_company_id = false;
    }

    if (defined('API')) {
        $api = Registry::get('api');
        $api_response_status = false;
        if ($api instanceof Api) {
            if (AREA == 'A') {
                if ($user_data = $api->getUserData()) {
                    $company_id = 0;

                    if ($user_data['company_id']) {
                        $company_id = $user_data['company_id'];
                    }

                    $store = array();
                    if (preg_match('/(stores|vendors)\/(\d+)\/.+/', $api->getRequest()->getResource(), $store)) {

                        if ($company_id && $company_id != $store[2]) {
                            $api_response_status = Response::STATUS_FORBIDDEN;
                        }

                        $company_id = intval($store[2]);
                        if (!fn_get_available_company_ids($company_id)) {
                            $company_id = 0;
                        }
                    }
                } else {
                    $api_response_status = Response::STATUS_UNAUTHORIZED;
                }
            }
        } else {
            $api_response_status = Response::STATUS_FORBIDDEN;
        }

        if ($api_response_status) {
            $response = new Response($api_response_status);
            /**
             * Here is exit.
             */
            $response->send();
        }
    }

    // set company_id for vendor's admin
    if (AREA == 'A' && !empty($_SESSION['auth']['company_id'])) {
        $company_id = intval($_SESSION['auth']['company_id']);
        $available_company_ids = array($company_id);
        if (!fn_get_available_company_ids($company_id)) {
            return fn_init_company_id_redirect($params, 'access_denied');
        }
    }

    // admin switching company_id
    if (!$company_id) {
        if ($switch_company_id !== false) { // request not empty
            if ($switch_company_id) {
                if (fn_get_available_company_ids($switch_company_id)) {
                    $company_id = $switch_company_id;
                } else {
                    return fn_init_company_id_redirect($params, 'company_not_found');
                }
            }
            fn_set_session_data('company_id', $company_id, COOKIE_ALIVE_TIME);
        } else {
            $company_id = fn_init_company_id_find_in_session();
        }
    }

    if (empty($available_company_ids)) {
        $available_company_ids = fn_get_available_company_ids();
    }

    fn_set_hook('init_company_id', $params, $company_id, $available_company_ids, $result);

    Registry::set('runtime.company_id', $company_id);
    Registry::set('runtime.companies_available_count', count($available_company_ids));

    unset($params['switch_company_id']);

    return $result;
}

/**
 * Form error notice and make redirect. Used in fn_init_company_id
 *
 * @param array $params request parameters
 * @param string $message language variable name for message
 * @param int $redirect_company_id New company id for redirecting, if null, company id saved in session will be used
 * @return array with init data (init status, redirect url in case of redirect)
 */
function fn_init_company_id_redirect(&$params, $message, $redirect_company_id = null)
{
    if ('access_denied' == $message) {

        $_SESSION['auth'] = array();
        $redirect_url = 'auth.login_form' . (!empty($params['return_url']) ? '?return_url=' . urldecode($params['return_url']) : '');

    } elseif ('company_not_found' == $message) {

        $dispatch = !empty($params['dispatch']) ? $params['dispatch'] : 'auth.login_form';
        unset($params['dispatch']);
        $params['switch_company_id'] = (null === $redirect_company_id) ? fn_init_company_id_find_in_session() : $redirect_company_id;

        $redirect_url = $dispatch . '?' . http_build_query($params);
    }

    if (!defined('CART_LANGUAGE')) {
        fn_init_language($params); // we need CART_LANGUAGE in fn_get_lang_var function
    }
    fn_set_notification('E', __('error'), __($message));

    return array(INIT_STATUS_REDIRECT, $redirect_url);
}

/**
 * Tryes to find company id in session
 *
 * @return int Company id if stored in session, 0 otherwise
 */
function fn_init_company_id_find_in_session()
{
    $session_company_id = intval(fn_get_session_data('company_id'));
    if ($session_company_id && !fn_get_available_company_ids($session_company_id)) {
        fn_delete_session_data('company_id');
        $session_company_id = 0;
    }

    return $session_company_id;
}

/**
 * Init currencies
 *
 * @param array $params request parameters
 * @param  string $area Area ('A' for admin or 'C' for customer)
 * @return boolean always true
 */
function fn_init_currency($params, $area = AREA)
{
    $_params = array();
    if (fn_allowed_for('ULTIMATE:FREE')) {
        $_params['only_primary'] = 'Y';
    } elseif ($area == 'C') {
        $_params['status'] = array('A', 'H');
    } else {
        $_params['status'] = array('A', 'H');
    }

    $currencies = fn_get_currencies_list($_params, $area, CART_LANGUAGE);

    if (!empty($params['currency']) && !empty($currencies[$params['currency']])) {
        $secondary_currency = $params['currency'];
    } elseif (($c = fn_get_session_data('secondary_currency' . $area)) && !empty($currencies[$c])) {
        $secondary_currency = $c;
    } else {
        foreach ($currencies as $v) {
            if ($v['is_primary'] == 'Y') {
                $secondary_currency = $v['currency_code'];
                break;
            }
        }
    }

    if (empty($secondary_currency)) {
        reset($currencies);
        $secondary_currency = key($currencies);
    }

    if ($secondary_currency != fn_get_session_data('secondary_currency' . $area)) {
        fn_set_session_data('secondary_currency'.$area, $secondary_currency, COOKIE_ALIVE_TIME);
    }

    $primary_currency = '';

    foreach ($currencies as $v) {
        if ($v['is_primary'] == 'Y') {
            $primary_currency = $v['currency_code'];
            break;
        }
    }

    if (empty($primary_currency)) {
        reset($currencies);
        $first_currency = current($currencies);
        $primary_currency = $first_currency['currency_code'];
    } elseif ($area == 'C') {
        if ($currencies[$secondary_currency]['status'] != 'A') {
            $first_currency = '';
            foreach ($currencies as $key => $currency) {
                if ($currency['status'] != 'A' && $currency['is_primary'] != 'Y') {
                    unset($currencies[$key]);
                } elseif ($currency['status'] == 'A' && !$first_currency) {
                    $first_currency = $currency;
                }
            }
            $secondary_currency = $first_currency['currency_code'];
        }
    }

    define('CART_PRIMARY_CURRENCY', $primary_currency);
    define('CART_SECONDARY_CURRENCY', $secondary_currency);

    Registry::set('currencies', $currencies);

    return array(INIT_STATUS_OK);
}

/**
 * Init layout
 *
 * @param array $params request parameters
 * @return boolean always true
 */
function fn_init_layout($params)
{
    if (fn_allowed_for('ULTIMATE')) {
        if (!Registry::get('runtime.company_id')) {
            return array(INIT_STATUS_OK);
        }
    }

    $key_name = 'stored_layout' . (Embedded::isEnabled() ? '_embedded' : '');
    $stored_layout = fn_get_session_data($key_name);

    if (!empty($params['s_layout'])) {
        $stored_layout = $params['s_layout'];

        fn_set_session_data($key_name, $params['s_layout']);
    }

    // Replace default theme with selected for current area
    if (!empty($stored_layout)) {
        $layout = Layout::instance()->get($stored_layout);

        if (!isset($layout['theme_name']) || $layout['theme_name'] != fn_get_theme_path('[theme]', 'C')) {
            unset($layout);
        }
    }

    if (empty($layout)) {
        $layout = Layout::instance()->getDefault(); // get default
    }

    $available_styles = Styles::factory($layout['theme_name'])->getList(array(
        'short_info' => true
    ));

    if (!isset($available_styles[$layout['style_id']])) {
        $layout['style_id'] = Styles::factory($layout['theme_name'])->getDefault();
    }

    Registry::set('runtime.layout', $layout);

    return array(INIT_STATUS_OK);
}

/**
 * Init user
 *
 * @return boolean always true
 */
function fn_init_user($area = AREA)
{
    $user_info = array();
    if (!empty($_SESSION['auth']['user_id'])) {
        $user_info = fn_get_user_short_info($_SESSION['auth']['user_id']);
        if (empty($user_info)) { // user does not exist in the database, but exists in session
            $_SESSION['auth'] = array();
        } else {
            $_SESSION['auth']['usergroup_ids'] = fn_define_usergroups(array('user_id' => $_SESSION['auth']['user_id'], 'user_type' => $user_info['user_type']));
        }
    }

    $first_init = false;
    if (empty($_SESSION['auth'])) {

        $udata = array();
        $user_id = fn_get_session_data($area . '_user_id');

        if ($area == 'A' && defined('CONSOLE')) {
            $user_id = 1;
        }

        if ($user_id) {
            fn_define('LOGGED_VIA_COOKIE', true);
        }

        fn_login_user($user_id);

        if (!defined('NO_SESSION')) {
            $_SESSION['cart'] = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        }

        if ((defined('LOGGED_VIA_COOKIE') && !empty($_SESSION['auth']['user_id'])) || ($cu_id = fn_get_session_data('cu_id'))) {
            $first_init = true;
            if (!empty($cu_id)) {
                fn_define('COOKIE_CART' , true);
            }

            // Cleanup cached shipping rates

            unset($_SESSION['shipping_rates']);

            $_utype = empty($_SESSION['auth']['user_id']) ? 'U' : 'R';
            $_uid = empty($_SESSION['auth']['user_id']) ? $cu_id : $_SESSION['auth']['user_id'];
            fn_extract_cart_content($_SESSION['cart'], $_uid , 'C' , $_utype);
            fn_save_cart_content($_SESSION['cart'] , $_uid , 'C' , $_utype);
            if (!empty($_SESSION['auth']['user_id'])) {
                $_SESSION['cart']['user_data'] = fn_get_user_info($_SESSION['auth']['user_id']);
                $user_info = fn_get_user_short_info($_SESSION['auth']['user_id']);
            }
        }
    }

    if (fn_is_expired_storage_data('cart_products_next_check', SECONDS_IN_HOUR * 12)) {
        db_query("DELETE FROM ?:user_session_products WHERE user_type = 'U' AND timestamp < ?i", (TIME - SECONDS_IN_DAY * 30));
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        // If administrative account has usergroup, it means the access restrictions are in action
        if ($area == 'A' && !empty($_SESSION['auth']['usergroup_ids'])) {
            fn_define('RESTRICTED_ADMIN', true);
        }
    }

    if (!empty($user_info) && $user_info['user_type'] == 'A' && (empty($user_info['company_id']) || (fn_allowed_for('ULTIMATE') && $user_info['company_id'] == Registry::get('runtime.company_id')))) {
        $customization_mode = fn_array_combine(explode(',', Registry::get('settings.customization_mode')), true);
        if (!empty($customization_mode)) {
            Registry::set('runtime.customization_mode', $customization_mode);

            if ($area == 'A') {
                Registry::set('runtime.customization_mode.live_editor', false);
            }
        }
    }

    fn_set_hook('user_init', $_SESSION['auth'], $user_info, $first_init);

    Registry::set('user_info', $user_info);

    return array(INIT_STATUS_OK);
}

/**
 * Init localizations
 *
 * @param array $params request parameters
 * @return boolean true if localizations exists, false otherwise
 */
function fn_init_localization($params)
{
    if (AREA != 'C') {
        return array(INIT_STATUS_OK);
    }

    $locs = db_get_hash_array("SELECT localization_id, custom_weight_settings, weight_symbol, weight_unit FROM ?:localizations WHERE status = 'A'", 'localization_id');

    if (!empty($locs)) {
        if (!empty($_REQUEST['lc']) && !empty($locs[$_REQUEST['lc']])) {
            $cart_localization = $_REQUEST['lc'];

        } elseif (($l = fn_get_session_data('cart_localization')) && !empty($locs[$l])) {
            $cart_localization = $l;

        } else {
            $_ip = fn_get_ip(true);
            $_country = fn_get_country_by_ip($_ip['host']);
            $_lngs = db_get_hash_single_array("SELECT lang_code, 1 as 'l' FROM ?:languages WHERE status = 'A'", array('lang_code', 'l'));
            $_language = fn_get_browser_language($_lngs);

            $cart_localization = db_get_field("SELECT localization_id, COUNT(localization_id) as c FROM ?:localization_elements WHERE (element = ?s AND element_type = 'C') OR (element = ?s AND element_type = 'L') GROUP BY localization_id ORDER BY c DESC LIMIT 1", $_country, $_language);

            if (empty($cart_localization) || empty($locs[$cart_localization])) {
                $cart_localization = db_get_field("SELECT localization_id FROM ?:localizations WHERE status = 'A' AND is_default = 'Y'");
            }
        }

        if (empty($cart_localization)) {
            reset($locs);
            $cart_localization = key($locs);
        }

        if ($cart_localization != fn_get_session_data('cart_localization')) {
            fn_set_session_data('cart_localization', $cart_localization, COOKIE_ALIVE_TIME);
        }

        if ($locs[$cart_localization]['custom_weight_settings'] == 'Y') {
            Registry::set('config.localization.weight_symbol', $locs[$cart_localization]['weight_symbol']);
            Registry::set('config.localization.weight_unit', $locs[$cart_localization]['weight_unit']);
        }

        fn_define('CART_LOCALIZATION', $cart_localization);
    }

    return array(INIT_STATUS_OK);
}

/**
 * Detect user agent
 *
 * @return boolean true always
 */
function fn_init_ua()
{
    static $crawlers = array(
        'google', 'bot', 'yahoo',
        'spider', 'archiver', 'curl',
        'python', 'nambu', 'twitt',
        'perl', 'sphere', 'PEAR',
        'java', 'wordpress', 'radian',
        'crawl', 'yandex', 'eventbox',
        'monitor', 'mechanize', 'facebookexternal'
    );

    $http_ua = isset($_SERVER['HTTP_USER_AGENT']) ? fn_strtolower($_SERVER['HTTP_USER_AGENT']) : '';

    if (strpos($http_ua, 'shiretoko') !== false || strpos($http_ua, 'firefox') !== false) {
        $ua = 'firefox';
    } elseif (strpos($http_ua, 'chrome') !== false) {
        $ua = 'chrome';
    } elseif (strpos($http_ua, 'safari') !== false) {
        $ua = 'safari';
    } elseif (strpos($http_ua, 'opera') !== false) {
        $ua = 'opera';
    } elseif (strpos($http_ua, 'msie') !== false || strpos($http_ua, 'trident/7.0; rv:11.0') !== false) {
        // IE11 does not send normal headers and seems like Mozilla:
        // Mozilla/5.0 (Windows NT 6.1; Trident/7.0; rv:11.0) like Gecko
        $ua = 'ie';
        if (preg_match("/msie (6|7|8)/i", $http_ua)) {
            Registry::set('runtime.unsupported_browser', true);
        }
    } elseif (empty($http_ua) || preg_match('/(' . implode('|', $crawlers) . ')/', $http_ua, $m)) {
        $ua = 'crawler';
        if (!empty($m)) {
            fn_define('CRAWLER', $m[1]);
        }
        if (!defined('SKIP_SESSION_VALIDATION')) {
            fn_define('NO_SESSION', true); // do not start session for crawler
        }
    } else {
        $ua = 'unknown';
    }

    fn_define('USER_AGENT', $ua);

    return array(INIT_STATUS_OK);
}

function fn_check_cache($params)
{
    $regenerated = true;
    $dir_root = Registry::get('config.dir.root') . '/';

    if (isset($params['ct']) && ((AREA == 'A' && !(fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id'))) || Debugger::isActive() || defined('DEVELOPMENT'))) {
        Storage::instance('images')->deleteDir('thumbnails');
    }

    // Clean up cache
    if (isset($params['cc']) && ((AREA == 'A' && !(fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id'))) || Debugger::isActive() || defined('DEVELOPMENT'))) {
        fn_clear_cache();
    }

    // Clean up templates cache
    if (isset($params['ctpl']) && ((AREA == 'A' && !(fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id'))) || Debugger::isActive() || defined('DEVELOPMENT'))) {
        fn_rm(Registry::get('config.dir.cache_templates'));
    }

    if (!in_array(AREA, array('A', 'V'))) {
        return array(INIT_STATUS_OK);
    }

    /* Add extra files for cache checking if needed */
    $core_hashes = array(
        'bdfa7f29a2e34ae071200c9318b3a374279c77e7' => array(
            'file' => 'cuc.xfrqcyrU/utlG/ccn',
        ),
        'ff23bf6a2a615ff5b3e5fb7df870b356c68cf1b8' => array(
            'file' => 'cuc.8sgh/ergeriabp_ynergvy/fnzrupf/ccn',
        ),
    );

    if (fn_allowed_for('ULTIMATE')) {
        $core_hashes['bdfa7f29a2e34ae071200c9318b3a374279c77e7']['notice'] = $core_hashes['ff23bf6a2a615ff5b3e5fb7df870b356c68cf1b8']['notice'] = 'fgber_zbqr_jvyy_or_punatrq_gb_serr';
    } else {
        $core_hashes['bdfa7f29a2e34ae071200c9318b3a374279c77e7']['notice'] = $core_hashes['ff23bf6a2a615ff5b3e5fb7df870b356c68cf1b8']['notice'] = 'fgber_zbqr_jvyy_or_punatrq_gb_gevny';
    }

    foreach ($core_hashes as $hash => $file) {
        if ($hash != sha1_file($dir_root . strrev(str_rot13($file['file'])))) {
            if (filemtime($dir_root . strrev(str_rot13($file['file']))) < TIME - SECONDS_IN_DAY * 2) { // 2-days cache
                fn_regenerate_cache($hash, $file['file']);
            } else {
                $regenerated = false;
            }

            fn_process_cache_notifications($file['notice']);

            break;
        }
    }

    return array(INIT_STATUS_OK);
}

function fn_init_settings()
{
    $company_id = Registry::get('runtime.simple_ultimate') ? Registry::get('runtime.forced_company_id') : Registry::get('runtime.company_id');

    Registry::registerCache('settings', array('settings_objects', 'settings_vendor_values', 'settings_descriptions', 'settings_sections', 'settings_variants'), Registry::cacheLevel('static'));
    if (Registry::isExist('settings') == false) {
        Registry::set('settings', Settings::instance($company_id)->getValues('', Settings::CORE_SECTION, true, $company_id));
    }

    // Set timezone
    date_default_timezone_set(Registry::get('settings.Appearance.timezone'));

    fn_define('DEFAULT_LANGUAGE', Registry::get('settings.Appearance.backend_default_language'));

    return array(INIT_STATUS_OK);
}

/**
 * Initialize all enabled addons
 *
 * @return INIT_STATUS_OK
 */
function fn_init_addons()
{
    Registry::registerCache('addons', array('addons', 'settings_objects', 'settings_vendor_values', 'settings_descriptions', 'settings_sections', 'settings_variants'), Registry::cacheLevel('static'));

    if (Registry::isExist('addons') == false) {

        $init_addons = Registry::get('settings.init_addons');
        if ($init_addons == 'none') {
            $_addons = array();
        } else {
            $condition = '';

            if ($init_addons == 'core') {
                $core_addons = Snapshot::getCoreAddons();
                if ($core_addons) {
                    $condition = db_quote(' AND addon IN (?a)', $core_addons);
                }
            }

            $_addons = db_get_hash_array("SELECT addon, priority, status, unmanaged FROM ?:addons WHERE 1 $condition ORDER BY priority", 'addon');
        }

        foreach ($_addons as $k => $v) {
            $_addons[$k] = Settings::instance()->getValues($v['addon'], Settings::ADDON_SECTION, false);
            if (fn_check_addon_snapshot($k)) {
                $_addons[$k]['status'] = $v['status'];
            } else {
                $_addons[$k]['status'] = 'D';
            }
            $_addons[$k]['priority'] = $v['priority'];
            $_addons[$k]['unmanaged'] = $v['unmanaged'];
        }

        // Some addons could be disabled for vendors.
        if (fn_allowed_for('MULTIVENDOR') && Registry::get('runtime.company_id')) {
            Registry::set('addons', $_addons);

            // So, we have to parse it one more time
            foreach ($_addons as $k => $v) {
                // and check permissions schema.
                // We couldn't make it in the previous cycle because the fn_get_scheme func works only with full list of addons.
                if (!fn_check_addon_permission($k)) {
                    unset($_addons[$k]);
                }
            }
        }

        Registry::set('addons', $_addons);
    }

    foreach ((array) Registry::get('addons') as $addon_name => $data) {
        if (empty($data['status'])) {
            // FIX ME: Remove me
            error_log("ERROR: Addons initialization: Bad '$addon_name' addon data:" . serialize($data) . " Addons Registry:" . serialize(Registry::get('addons')));
        }
        if (!empty($data['status']) && $data['status'] == 'A') {
            fn_load_addon($addon_name);
        }
    }

    Registry::set('addons_initiated', true);

    return array(INIT_STATUS_OK);
}

/**
 * Initialize unmanaged addons
 *
 * @return INIT_STATUS_OK
 */
function fn_init_unmanaged_addons()
{
    // Do not use cache here, because company ID is not initialized yet
    $addons = db_get_fields("SELECT addon FROM ?:addons WHERE unmanaged = 1 AND status = 'A' ORDER BY priority");

    foreach ($addons as $addon_name) {
        fn_load_addon($addon_name);
    }

    return array(INIT_STATUS_OK);
}

function fn_init_full_path($request)
{
    // Display full paths cresecure payment processor
    if (isset($request['display_full_path']) && ($request['display_full_path'] == 'Y')) {
        define('DISPLAY_FULL_PATHS', true);
        Registry::set('config.full_host_name', (defined('HTTPS') ? 'https://' . Registry::get('config.https_host') : 'http://' . Registry::get('config.http_host')));
    } else {
        Registry::set('config.full_host_name', '');
    }

    return array(INIT_STATUS_OK);
}

function fn_init_stack()
{
    $stack = Registry::get('init_stack');
    if (empty($stack)) {
        $stack = array();
    }

    $stack_data = func_get_args();

    foreach ($stack_data as $data) {
        $stack[] = $data;
    }

    Registry::set('init_stack', $stack);

    return true;
}

/**
 * Run init functions
 *
 * @param array $request $_REQUEST global variable
 * @return bool always true
 */
function fn_init(&$request)
{
    $stack = Registry::get('init_stack');

    // Cleanup stack
    Registry::set('init_stack', array());

    foreach ($stack as $function_data) {
        $function = array_shift($function_data);

        if (!is_callable($function)) {
            continue;
        }

        $result = call_user_func_array($function, $function_data);

        $status = !empty($result[0]) ? $result[0] : INIT_STATUS_OK;
        $url = !empty($result[1]) ? $result[1] : '';
        $message = !empty($result[2]) ? $result[2] : '';
        $permanent = !empty($result[3]) ? $result[3] : '';

        if ($status == INIT_STATUS_OK && !empty($url)) {
            $redirect_url = $url;

        } elseif ($status == INIT_STATUS_REDIRECT && !empty($url)) {
            $redirect_url = $url;
            break;

        } elseif ($status == INIT_STATUS_FAIL) {
            if (empty($message)) {
                $message = 'Initialization failed in <b>' . (is_array($function) ? implode('::', $function) : $function) . '</b> function';
            }

            throw new InitException($message);
        }
    }

    if (!empty($redirect_url)) {
        if (!defined('CART_LANGUAGE')) {
            fn_init_language($request); // we need CART_LANGUAGE in fn_url function that called in fn_redirect
        }
        fn_redirect($redirect_url, false, !empty($permanent));
    }

    $stack = Registry::get('init_stack');
    if (!empty($stack)) {
        // New init functions were added to stack. Execute them
        fn_init($request);
    }

    Debugger::init(true);

    return true;
}

/**
 * Init paths for storage store data (mse, saas)
 *
 * @return boolean true always
 */
function fn_init_storage()
{
    fn_set_hook('init_storage');

    $storage = Settings::instance()->getValue('storage', '');

    Registry::set('runtime.storage', unserialize($storage));

    Registry::set('config.images_path', Storage::instance('images')->getUrl()); // FIXME this path should be removed

    return array(INIT_STATUS_OK);
}

/**
 * Init api object and put it to Registry.
 */
function fn_init_api()
{
    $api = new Api();
    Registry::set('api', $api, true);

    return array(INIT_STATUS_OK);
}
