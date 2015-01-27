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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Bootstrap;
use Tygh\Debugger;
use Tygh\Development;
use Tygh\Embedded;
use Tygh\Registry;
use Tygh\Session;
use Tygh\Settings;
use Tygh\Storage;
use Tygh\Mailer;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\Layout;
use Tygh\BlockManager\Exim;
use Tygh\BlockManager\RenderManager;
use Tygh\Less;
use Tygh\Languages\Languages;
use Tygh\Languages\Values as LanguageValues;
use Tygh\Languages\Helper as LanguageHelper;
use Tygh\Tools\Url;
use Tygh\Themes\Styles;
use Tygh\Themes\Themes;

/**
 * Returns True if the object can be saved, otherwise False.
 *
 * @param array $object_data Object data
 * @param string $object_type Object name
 * @param bool $skip_edition_checking Skip edition checking condition
 * @return bool Returns True if the object can be saved, otherwise False.
 */
function fn_allow_save_object($object_data, $object_type, $skip_edition_checking = false)
{
    /**
     * Perform actions before object checking
     *
     * @param array  $object_data Object data
     * @param string $object_type Object name
     */
    fn_set_hook('allow_save_object_pre', $object_data, $object_type);

    $allow = true;

    $selected_company_id = Registry::get('runtime.company_id');

    if ($skip_edition_checking) {
        if ($selected_company_id) {
            $allow = false;
        }

    } else {
        if (
            isset($object_data['company_id']) && $selected_company_id
            && $selected_company_id != $object_data['company_id']
        ) {
            $allow = false;
        }
    }

    /**
     * Perform actions after object checking
     *
     * @param array  $object_data Object data
     * @param string $object_type Object name
     * @param string $allow       True if the object can be saved, otherwise False.
     */
    fn_set_hook('allow_save_object_post', $object_data, $object_type, $allow);

    return $allow;
}

/**
 * Returns theme path in the required format
 *
 * Examples:
 * [themes] -> /var/www/design/themes/
 * [themes]/[theme] -> /var/www/design/themes/theme
 * [relative]/[theme] -> design/themes/theme
 * [repo]/[theme] -> /var/www/var/themes_repository/theme
 *
 * In format string:
 * [theme] will be replaced by actual theme name
 * [repo] will be replaced by real path to repository
 * [themes] will be replaced by real path of actual themes folder
 * [relative] will be replaced by path of actual themes folder relative root directory
 *
 * @param $path string Format string.
 * @param $area string Area (C/A) to get setting for
 * @param $company_id int Company identifier
 * @param $use_cache bool Value will be get from the DB directly if use_cache is equal to false or from already generated cache otherwise
 * @return string Path to theme
 */
function fn_get_theme_path($path = '[theme]/', $area = AREA, $company_id = null, $use_cache = true)
{
    static $theme_names = array();

    fn_set_hook('get_theme_path_pre', $path, $area, $company_id, $theme_names);

    if ($area == 'A') {
        $theme_name = '';
        $dir_design = rtrim(Registry::get('config.dir.design_backend'), '/');

        if (strpos($path, '/[theme]/') !== false) { // FIXME THEMES: bad code
            $path = str_replace('/[theme]/', '/', $path);
        } elseif (strpos($path, '/[theme]') !== false) {
            $path = str_replace('/[theme]', '', $path);
        } elseif (strpos($path, '[theme]/') !== false) {
            $path = str_replace('[theme]/', '', $path);
        }
    } else {
        if (empty($theme_names['c_' . $company_id]) || !$use_cache) {
            $theme_names['c_' . $company_id] = Settings::instance($company_id)->getValue('theme_name', '', $company_id);
        }

        $theme_name = $theme_names['c_' . $company_id];
        $dir_design = rtrim(Registry::get('config.dir.design_frontend'), '/');
    }

    $path = str_replace('[theme]', $theme_name, $path);
    $dir_repo = rtrim(Registry::get('config.dir.themes_repository'), '/');

    $path = str_replace('[relative]', str_replace(Registry::get('config.dir.root') . '/', '', $dir_design), $path);
    $path = str_replace('[themes]', $dir_design, $path);
    $path = str_replace('[repo]', $dir_repo, $path);

    fn_set_hook('get_theme_path', $path, $area, $dir_design, $company_id);

    return $path;
}

/**
 * Gets path for caching documents
 *
 * @param boolean $relative Flag that defines if path should be relative
 * @param string $area Area (C/A) to get setting for
 * @param integer $company_id Company identifier
 * @return string Path to files cache
 */
function fn_get_cache_path($relative = true, $area = AREA, $company_id = null)
{
    $path = Registry::get('config.dir.cache_misc');

    if ($relative) {
        $path = str_replace(Registry::get('config.dir.root') . '/', '', $path);
    }

    /**
     * Changes cache path
     *
     * @param string  $path       Path to files cache
     * @param boolean $relative   Flag that defines if flag should be relative
     * @param string  $area       Area (C/A) to get setting for
     * @param integer $company_id Company identifier
     */
    fn_set_hook('get_cache_path', $path, $relative, $area, $company_id);

    return $path;
}

/**
 * Prints any data like a print_r function
 * @param mixed ... Any data to be printed
 */
function fn_print_r()
{
    static $count = 0;
    $args = func_get_args();

    if (defined('CONSOLE')) {
        $prefix = "\n";
        $suffix = "\n\n";
    } else {
        $prefix = '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">';
        $suffix = '</ol><div style="clear:left;"></div>';
    }

    if (!empty($args)) {
        fn_echo($prefix);
        foreach ($args as $k => $v) {

            if (defined('CONSOLE')) {
                fn_echo(print_r($v, true));
            } else {
                fn_echo('<li><pre>' . htmlspecialchars(print_r($v, true)) . "\n" . '</pre></li>');
            }
        }
        fn_echo($suffix);

        if (defined('AJAX_REQUEST')) {
            $ajax_vars = Registry::get('ajax')->getAssignedVars();
            if (!empty($ajax_vars['debug_info'])) {
                $args = array_merge($ajax_vars['debug_info'], $args);
            }
            Registry::get('ajax')->assign('debug_info', $args);
        }
    }
    $count++;
}

/**
* Redirect browser to the new location
*
* @param string $location - destination of redirect
* @param bool $allow_external_redirect - allow redirection to external resource
* @param bool $is_permanent - if true, perform 301 redirect
* @return
*/
function fn_redirect($location, $allow_external_redirect = false, $is_permanent = false)
{
    $external_redirect = false;
    $protocol = defined('HTTPS') ? 'https' : 'http';
    $meta_redirect = false;

    // Cleanup location from &amp; signs and call fn_url()
    $location = fn_url(str_replace(array('&amp;', "\n", "\r"), array('&', '', ''), $location));

    // Convert absolute link with location to relative one
    if (strpos($location, '://') !== false || substr($location, 0, 7) == 'mailto:') {
        if (strpos($location, Registry::get('config.http_location')) !== false) {
            $location = str_replace(array(Registry::get('config.http_location') . '/', Registry::get('config.http_location')), '', $location);
            $protocol = 'http';

        } elseif (strpos($location, Registry::get('config.https_location')) !== false) {
            $location = str_replace(array(Registry::get('config.https_location') . '/', Registry::get('config.https_location')), '', $location);
            $protocol = 'https';

        } else {
            if ($allow_external_redirect == false) { // if external redirects aren't allowed, redirect to index script
                $location = '';
            } else {
                $external_redirect = true;
            }
        }

    // Convert absolute link without location to relative one
    } else {
        $_protocol = "";
        $_location = "";
        $http_path = Registry::get('config.http_path');
        $https_path = Registry::get('config.https_path');
        if (!empty($http_path) && substr($location, 0, strlen($http_path)) == $http_path) {
            $_location = substr($location, strlen($http_path) + 1);
            $_protocol = 'http';

        }
        if (!empty($https_path) && substr($location, 0, strlen($https_path)) == $https_path) {
            // if https path partially equal to http path check if https path is not just a part of http path
            // e. g. http://example.com/pathsimple & https://example.com/path
            if ($_protocol != 'http' || empty($http_path) || substr($http_path, 0, strlen($https_path)) != $https_path) {
                $_location = substr($location, strlen($https_path) + 1);
                $_protocol = 'https';
            }
        }
        $protocol = (Registry::get('config.http_path') != Registry::get('config.https_path') && !empty($_protocol)) ? $_protocol : $protocol;
        $location = !empty($_protocol) ? $_location : $location;
    }

    if ($external_redirect == false) {

        fn_set_hook('redirect', $location);

        $protocol_changed = (defined('HTTPS') && $protocol == 'http') || (!defined('HTTPS') && $protocol == 'https');

        // For correct redirection, location must be absolute with path
        $location = (($protocol == 'http') ? Registry::get('config.http_location') : Registry::get('config.https_location')) . '/' . ltrim($location, '/');

        // Parse the query string
        $fragment = '';
        $query_array = array();
        $parsed_location = parse_url($location);
        if (!empty($parsed_location['query'])) {
            parse_str($parsed_location['query'], $query_array);
            $location = str_replace('?' . $parsed_location['query'], '', $location);
        }

        if (!empty($parsed_location['fragment'])) {
            $fragment = '#' . $parsed_location['fragment'];
            $location = str_replace($fragment, '', $location);
        }

        if ($protocol_changed && (Registry::get('config.http_host') != Registry::get('config.https_host') || Registry::get('config.http_path') != Registry::get('config.https_path'))) {
            $query_array[Session::getName()] = Session::getId();
        }

        // If this is not ajax request, remove ajax specific parameters
        if (!defined('AJAX_REQUEST')) {
            unset($query_array['is_ajax']);
            unset($query_array['result_ids']);
        } else {
            $query_array['result_ids'] = implode(',', Registry::get('ajax')->result_ids);
            $query_array['is_ajax'] = Registry::get('ajax')->redirect_type;
            $query_array['full_render'] = !empty($_REQUEST['full_render']) ? $_REQUEST['full_render'] : false;
            $query_array['callback'] = Registry::get('ajax')->callback;

            $ajax_assigned_vars = Registry::get('ajax')->getAssignedVars();
            if (!empty($ajax_assigned_vars['html'])) {
                unset($ajax_assigned_vars['html']);
            }
            $query_array['_ajax_data'] = $ajax_assigned_vars;
        }

        if (!empty($query_array)) {
            $location .= '?' . http_build_query($query_array) . $fragment;
        }

        // Redirect from https to http location
        if ($protocol_changed && defined('HTTPS')) {
            $meta_redirect = true;
        }
    }

    fn_set_hook('redirect_complete', $meta_redirect);

    if (!defined('AJAX_REQUEST') && Embedded::isEnabled()) {
        if (strpos($location, Registry::get('config.http_location')) === 0) {
            $location = str_replace(Registry::get('config.http_location'), '', $location);
        } elseif (strpos($location, Registry::get('config.https_location')) === 0) {
            $location = str_replace(Registry::get('config.https_location'), '', $location);
        }

        $location = Embedded::getUrl() . '#!' . urlencode($location);
        $meta_redirect = true;
    }

    if (defined('AJAX_REQUEST')) { // make in-script redirect during ajax request
        $_purl = parse_url($location);

        $_GET = array();
        $_POST = array();

        if (!empty($_purl['query'])) {
            parse_str($_purl['query'], $_GET);
        }
        $_REQUEST = Bootstrap::safeInput($_GET);
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = $_purl['path'];
        $_SERVER['QUERY_STRING'] = !empty($_purl['query']) ? $_purl['query'] : '';

        fn_get_route($_REQUEST);

        Registry::save(); // save registry cache to execute cleanup handlers
        fn_init_settings();
        fn_init_addons();

        Registry::clearCacheLevels();

        Registry::get('ajax')->updateRequest();

        return fn_dispatch();

    } elseif (!ob_get_contents() && !headers_sent() && !$meta_redirect) {

        if ($is_permanent) {
            header('HTTP/1.0 301 Moved Permanently');
        }
        header('Location: ' . $location);
        exit;
    } else {
        $delay = ((Debugger::isActive() || defined('DEVELOPMENT')) && !Registry::get('runtime.comet')) ? 10 : 0;
        if ($delay != 0) {
            fn_echo('<a href="' . htmlspecialchars($location) . '" style="text-transform: lowercase;">' . __('continue') . '</a>');
        }
        fn_echo('<meta http-equiv="Refresh" content="' . $delay . ';URL=' . htmlspecialchars($location) . '" />');
    }

    fn_flush();
    exit;
}

/**
 * Functions check if subarray with child exists in the given array
 *
 * @param array $data Array with nodes
 * @param string $childs_name Name of array with child nodes
 * @return boolean true if there are child subarray, false otherwise.
 */
function fn_check_second_level_child_array($data, $childs_name)
{
    foreach ($data as $l2) {
        if (!empty($l2[$childs_name]) && is_array($l2[$childs_name]) && count($l2[$childs_name])) {
            return true;
        }
    }

    return false;
}

/**
 * Sets notification message
 *
 * @param string $type notification type (E - error, W - warning, N - notice, O - order error on checkout, I - information)
 * @param string $title notification title
 * @param string $message notification message
 * @param string $message_state (S - notification will be displayed unless it's closed, K - only once, I - will be closed by timer)
 * @param mixed $extra extra data to save with notification
 * @param bool $init_message $title and $message will be processed by __ function if true
 * @return boolean always true
 */
function fn_set_notification($type, $title, $message, $message_state = '', $extra = '', $init_message = false)
{
    // Back compabilities code
    if ($message_state === false) {
        $message_state = 'K';

    } elseif ($message_state === true) {
        $message_state = 'S';
    }
    // \back compabilities code

    if (empty($message_state) && $type == 'N') {
        $message_state = 'I';

    } elseif (empty($message_state)) {
        $message_state = 'K';
    }

    if (empty($_SESSION['notifications'])) {
        $_SESSION['notifications'] = array();
    }

    $key = md5($type . $title . $message . $extra);

    $_SESSION['notifications'][$key] = array(
        'type' => $type,
        'title' => $title,
        'message' => $message,
        'message_state' => $message_state,
        'new' => true,
        'extra' => $extra,
        'init_message' => $init_message,
    );

    return true;
}

/**
 * Deletes notification message
 *
 * @param string $extra condition for "extra" parameter
 * @return boolean always true
 */
function fn_delete_notification($extra)
{
    if (!empty($_SESSION['notifications'])) {
        foreach ($_SESSION['notifications'] as $k => $v) {
            if (!empty($v['extra']) && $v['extra'] == $extra) {
                unset($_SESSION['notifications'][$k]);
            }
        }
    }

    return true;
}

/**
 * Checks if notification message exists
 * <i>$check_type</i> - type of notification existance checking
 * <ul>
 *      <li>any - checks if at least one notification exist</li>
 *      <li>extra - checks if notification with "extra" field equals to $value exist </li>
 *      <li>type - checks if at least one notification of certain type exist</li>
 * </ul>
 * @param string $check_type check type
 * @param string $value value to compare with
 * @return boolean true if notification exists, false - if not
 */
function fn_notification_exists($check_type, $value = '')
{
    if (!empty($_SESSION['notifications'])) {
        if ($check_type == 'any') {
            return true;
        }

        foreach ($_SESSION['notifications'] as $k => $v) {
            if ($check_type == 'type' && $v['type'] == $value) {
                return true;
            }

            if ($check_type == 'extra' && !empty($v['extra']) && $v['extra'] == $value) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Gets notifications list
 *
 * @return array notifications list
 */
function fn_get_notifications()
{
    if (empty($_SESSION['notifications'])) {
        $_SESSION['notifications'] = array();
    }

    $_notifications = array();

    foreach ($_SESSION['notifications'] as $k => $v) {
        if (!empty($v['init_message'])) {
            $placeholders = array();

            if (!empty($v['extra'])) {
                $extra = @unserialize($v['extra']);
                if (!empty($extra) && !empty($extra['placeholders'])) {
                    $placeholders = $extra['placeholders'];
                }
            }

            $v['title'] = __($v['title'], !empty($placeholders['title']) ? $placeholders['title'] : array());
            $v['message'] = __($v['message'], !empty($placeholders['message']) ? $placeholders['message'] : array());
        }

        // Display notification if this is not ajax request, or ajax request and notifiactions was just set
        if (!defined('AJAX_REQUEST') || (defined('AJAX_REQUEST') && $v['new'] == true)) {
            $_notifications[$k] = $v;
        }

        if ($v['message_state'] != 'S') {
            unset($_SESSION['notifications'][$k]);
        } else {
            $_SESSION['notifications'][$k]['new'] = false; // preparing notification for display, reset new flag
        }
    }

    return $_notifications;
}

/**
 * Process' notifications of cache cleaning functionality
 *
 * @param string $notification String IDentifier
 * @return null
 */
function fn_process_cache_notifications($notification)
{
    $mode = fn_get_current_mode();

    if ((fn_allowed_for('ULTIMATE') && $mode != str_rot13('serr')) || (!fn_allowed_for('ULTIMATE') && $mode != str_rot13('gevny'))) {
        fn_set_notification('W', 'warning', str_rot13($notification), 'K', serialize(array(
            'placeholders' => array(
                'message' => array(
                    '[href]' => Registry::get('config.resources.helpdesk_url')
                )
            )
        )), true);
    }
}

/**
 * Saves data, posted to script via POST request in session to use it later
 *
 * @param string 1 or more keys of $_POST array
 * @return boolean always true
 */
function fn_save_post_data()
{
    $_SESSION['saved_post_data'] = array();
    $args = func_get_args();

    foreach ($args as $key) {
        if (isset($_POST[$key])) {
            $_SESSION['saved_post_data'][$key] = defined('QUOTES_ENABLED') ? Bootstrap::stripSlashes($_POST[$key]) : $_POST[$key];
        }
    }

    return true;
}

/**
 * Restores data, saved by fn_save_post_data function
 *
 * @param string $key key to restore
 * @return mixed restored data of success or null on failure
 */
function fn_restore_post_data($key)
{
    if (isset($_SESSION['saved_post_data'][$key])) {
        $data = $_SESSION['saved_post_data'][$key];
        unset($_SESSION['saved_post_data'][$key]);

        return $data;
    }

    return null;

}

/**
 * @deprecated
 *
 * Gets language variable by name
 *
 * @param string $var_name Language variable name
 * @param string $lang_code 2-letter language code
 *
 * @return string Language variable value; in case the value is absent, language variable name with "_" prefix is returned
 */
function fn_get_lang_var($var_name, $lang_code = CART_LANGUAGE)
{
    return LanguageValues::getLangVar($var_name, $lang_code);
}

/**
 * @deprecated
 *
 * Gets language variables by prefix
 *
 * @param string $prefix Language variable prefix
 * @param $lang_code 2-letter language code
 *
 * @return Array of language variables
 */
function fn_get_lang_vars_by_prefix($prefix, $lang_code = CART_LANGUAGE)
{
    return LanguageValues::getLangVarsByPrefix($prefix, $lang_code);
}

/**
 * @deprecated
 *
 * Loads received language variables into language cache
 *
 * @param array $var_names Language variable that to be loaded
 * @param string $lang_code 2-letter language code
 *
 * @return boolean True if any of received language variables were added into cache; false otherwise
 */
function fn_preload_lang_vars($var_names, $lang_code = CART_LANGUAGE)
{
    return LanguageHelper::preloadLangVars($var_names, $lang_code);
}

/**
 * @deprecatred
 * @param $tpl_var
 * @param $value
 */
function fn_update_lang_objects($tpl_var, &$value)
{
    return LanguageHelper::updateLangObjects($tpl_var, $value);
}

/**
 * @deprecated
 */
function fn_is_allow_to_translate_language_object($language_object)
{
    return LanguageHelper::isAllowToTranslateLanguageObject($language_object);
}

/**
 * @deprecated
 */
function fn_prepare_lang_objects(&$destination, $dimension, $fields, $table, $field_id, $inner = '', $unescape = '')
{
    return LanguageHelper::prepareLangObjects($destination, $dimension, $fields, $table, $field_id, $inner, $unescape);
}

/**
 * Function defines and assigns pages
 *
 * @param array $params params to generate pagination from
 * @param string $area Area
 * @return array pagination structure
 */
function fn_generate_pagination($params, $area = AREA)
{
    if (empty($params['total_items']) || empty($params['items_per_page'])) {
        return array();
    }

    $deviation = ($area == 'A') ? 5 : 7;
    $max_pages = 10;
    $per_page = 10;

    $total_pages = ceil((int) $params['total_items'] / $params['items_per_page']);

    // Pagination in other areas displayed as in any search engine
    $page_from = fn_get_page_from($params['page'], $deviation);
    $page_to = fn_get_page_to($params['page'], $deviation, $total_pages);

    $pagination = array (
        'navi_pages' => range($page_from, $page_to),
        'prev_range' => ($page_from > 1) ? $page_from - 1 : 0,
        'next_range' => ($page_to < $total_pages) ? $page_to + 1: 0,
        'current_page' => $params['page'],
        'prev_page' => ($params['page'] > 1) ? $params['page'] - 1 : 0,
        'next_page' => ($params['page'] < $total_pages) ? $params['page'] + 1 : 0,
        'total_pages' => $total_pages,
        'total_items' => $params['total_items'],
        'items_per_page' => $params['items_per_page'],
        'per_page_range' => range($per_page, $per_page * $max_pages, $per_page)
    );

    if ($pagination['prev_range']) {
        $pagination['prev_range_from'] = fn_get_page_from($pagination['prev_range'], $deviation);
        $pagination['prev_range_to'] = fn_get_page_to($pagination['prev_range'], $deviation, $total_pages);
    }

    if ($pagination['next_range']) {
        $pagination['next_range_from'] = fn_get_page_from($pagination['next_range'], $deviation);
        $pagination['next_range_to'] = fn_get_page_to($pagination['next_range'], $deviation, $total_pages);
    }

    if (!in_array($params['items_per_page'], $pagination['per_page_range'])) {
        $pagination['per_page_range'][] = $params['items_per_page'];
        sort($pagination['per_page_range']);
    }

    return $pagination;
}

function fn_get_page_from($page, $deviation)
{
    return ($page - $deviation < 1) ? 1 : $page - $deviation;
}

function fn_get_page_to($page, $deviation, $total_pages)
{
    return ($page + $deviation > $total_pages) ? $total_pages : $page + $deviation;
}

//
// This function splits the array into defined number of columns to
// show it in the frontend
// Params:
// $data - the array that should be splitted
// $size - number of columns/rows to split into
// Example:
// array (a, b, c, d, e, f, g, h, i, j, k);
// fn_split($array, 3);
// Result:
// 0 -> a, b, c, d
// 1 -> e, f, g, h
// 2 -> i, j, k
// ---------------------
// fn_split($array, 3, true)
// Result:
//

function fn_split($data, $size, $vertical_delimition = false, $size_is_horizontal = true)
{

    if ($vertical_delimition == false) {
        return array_chunk($data, $size);
    } else {

        $chunk_count = ($size_is_horizontal == true) ? ceil(count($data) / $size) : $size;
        $chunk_index = 0;
        $chunks = array();
        foreach ($data as $key => $value) {
            $chunks[$chunk_index][] = $value;
            if (++$chunk_index == $chunk_count) {
                $chunk_index = 0;
            }
        }

        return $chunks;
    }
}

//
// Advanced checking for variable emptyness
//
function fn_is_empty($var)
{
    if (!is_array($var)) {
        return (empty($var));
    } else {
        foreach ($var as $k => $v) {
            if (empty($v)) {
                unset($var[$k]);
                continue;
            }

            if (is_array($v) && fn_is_empty($v)) {
                unset($var[$k]);
            }
        }

        return (empty($var)) ? true : false;
    }
}

function fn_is_not_empty($var)
{
    return !fn_is_empty($var);
}

//
// Format price
//

function fn_format_price($price = 0, $currency = CART_PRIMARY_CURRENCY, $decimals = null, $return_as_float = true)
{
    if ($decimals === null) {
        $currency_settings = Registry::get('currencies.' . $currency);
        $decimals = !empty($currency_settings) ? $currency_settings['decimals'] + 0 : 2; //set default value if not exist
    }
    $price = sprintf('%.' . $decimals . 'f', round((double) $price + 0.00000000001, $decimals));

    return $return_as_float ? (float) $price : $price;
}

/**
* Send back in stock notifications for subscribed customers
*
* @param int $product_id product id
* @return boolean always true
*/
function fn_send_product_notifications($product_id)
{
    if (empty($product_id)) {
        return false;
    }
    $emails = db_get_fields("SELECT email FROM ?:product_subscriptions WHERE product_id = ?i", $product_id);

    if (!empty($emails)) {
        $product['name'] = fn_get_product_name($product_id, Registry::get('settings.Appearance.frontend_default_language'));

        if (fn_allowed_for('ULTIMATE')) {
            $product['company_id'] = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);
        }

        Mailer::sendMail(array(
            'to' => $emails,
            'from' => 'company_orders_department',
            'reply_to' => 'company_orders_department',
            'data' => array(
                'product' => $product,
                'product_id' => $product_id,
            ),
            'tpl' => 'product/back_in_stock_notification.tpl',
            'company_id' => $product['company_id'],
        ), 'C', Registry::get('settings.Appearance.frontend_default_language'));
        if (!defined('ORDER_MANAGEMENT')) {
            db_query("DELETE FROM ?:product_subscriptions WHERE product_id = ?i", $product_id);
        }
    }

    return true;
}

/**
 * Add new node the breadcrumbs
 *
 * @param string $lang_value name of language variable
 * @param string $link breadcrumb URL
 * @param boolean $nofollow Include or not "nofollow" attribute
 * @return boolean True if breadcrumbs were added, false otherwise
 */
function fn_add_breadcrumb($lang_value, $link = '', $nofollow = false)
{
    //check permissions in the backend
    if (AREA == 'A' && !fn_check_view_permissions($link, 'GET')) {
        return false;
    }

    $bc = Registry::get('view')->getTemplateVars('breadcrumbs');

    if (!empty($link)) {
        fn_set_hook('add_breadcrumb', $lang_value, $link);
    }

    // Add home link
    if (AREA == 'C' && empty($bc)) {
        $bc[] = array(
            'title' => __('home'),
            'link' => fn_url('')
        );
    }

    $bc[] = array(
        'title' => $lang_value,
        'link' => $link,
        'nofollow' => $nofollow,
    );

    Registry::get('view')->assign('breadcrumbs', $bc);

    return true;
}

/**
 * Merge several arrays preserving keys (recursivelly!) or not preserving
 *
 * @param array ... unlimited number of arrays to merge
 * @param bool ... if true, the array keys are preserved
 * @return array merged data
 */
function fn_array_merge()
{
    $arg_list = func_get_args();
    $preserve_keys = true;
    $result = array();
    if (is_bool(end($arg_list))) {
        $preserve_keys = array_pop($arg_list);
    }

    foreach ((array) $arg_list as $arg) {
        foreach ((array) $arg as $k => $v) {
            if ($preserve_keys == true) {
                $result[$k] = !empty($result[$k]) && is_array($result[$k]) ? fn_array_merge($result[$k], $v) : $v;
            } else {
                $result[] = $v;
            }
        }
    }

    return $result;
}

//
// Restore original variable content (unstripped)
// Parameters should be the variables names
// E.g. fn_trusted_vars("product_data","big_text","etcetc")
function fn_trusted_vars()
{
    $args = func_get_args();
    if (sizeof($args) > 0) {
        foreach ($args as $k => $v) {
            if (isset($_POST[$v])) {
                $_REQUEST[$v] = (!defined('QUOTES_ENABLED')) ? $_POST[$v] : Bootstrap::stripSlashes($_POST[$v]);
            } elseif (isset($_GET[$v])) {
                $_REQUEST[$v] = (!defined('QUOTES_ENABLED')) ? $_GET[$v] : Bootstrap::stripSlashes($_GET[$v]);
            }
        }
    }

    return true;
}

// EnCrypt text wrapper function
function fn_encrypt_text($text)
{
    if (!defined('CRYPT_STARTED')) {
        fn_init_crypt();
    }

    return base64_encode(Registry::get('crypt')->encrypt($text));
}

// DeCrypt text wrapper function
function fn_decrypt_text($text)
{

    if (!defined('CRYPT_STARTED')) {
        fn_init_crypt();
    }

    return Registry::get('crypt')->decrypt(base64_decode($text));
}

// Start javascript autoscroller
function fn_start_scroller()
{
    if (defined('CONSOLE')) {
        return true;
    }

    echo "
        <html>
        <head><title>" . PRODUCT_NAME . "</title>
        <meta http-equiv='content-type' content='text/html; charset=" . CHARSET . "'>
        </head>
        <body>
        <script language='javascript'>
        loaded = false;
        function refresh()
        {
            var scroll_height = parseInt(document.body.scrollHeight);
            window.scroll(0, scroll_height + 99999);
            if (loaded == false) {
                setTimeout('refresh()', 1000);
            }
        }
        setTimeout('refresh()', 1000);
        </script>
    ";
    fn_flush();
}

// Stop javascript autoscroller
function fn_stop_scroller()
{
    if (defined('CONSOLE')) {
        return true;
    }

    echo "
    <script language='javascript'>
        loaded = true;
    </script>
    </body>
    </html>
    ";
    fn_flush();
}

function fn_recursive_makehash($tab)
{
    if (!is_array($tab)) {
        return $tab;
    }

    $p = '';
    foreach ($tab as $a => $b) {
        $p .= sprintf('%08X%08X', crc32($a), crc32(fn_recursive_makehash($b)));
    }

    return $p;
}

//
// Smart wrapper for PHP array_unique function
//
function fn_array_unique($input)
{
    $dumdum = array();
    foreach ($input as $a => $b) {
        $dumdum[$a] = fn_recursive_makehash($b);
    }
    $newinput = array();
    foreach (array_unique($dumdum) as $a => $b) {
        $newinput[$a] = $input[$a];
    }

    return $newinput;
}

function fn_delete_static_data($param_id)
{
    $scheme = fn_get_schema('static_data', 'schema');

    if (!empty($param_id)) {
        $static_data = db_get_row("SELECT id_path, section FROM ?:static_data WHERE param_id = ?i", $param_id);
        $id_path = $static_data['id_path'];
        $section = $static_data['section'];

        if (!empty($scheme[$section]['skip_edition_checking']) && Registry::get('runtime.company_id')) {
            fn_set_notification('E', __('error'), __('access_denied'));

            return false;
        }

        $delete_ids = db_get_fields("SELECT param_id FROM ?:static_data WHERE param_id = ?i OR id_path LIKE ?l", $param_id, "$id_path/%");

        db_query("DELETE FROM ?:static_data WHERE param_id IN (?n)", $delete_ids);
        db_query("DELETE FROM ?:static_data_descriptions WHERE param_id IN (?n)", $delete_ids);
    }

    return true;
}

function fn_get_static_data($params, $lang_code = DESCR_SL)
{
    $default_params = array();

    $params = array_merge($default_params, $params);

    $schema = fn_get_schema('static_data', 'schema');
    $section_data = $schema[$params['section']];

    $fields = array(
        'sd.param_id',
        'sd.param',
        '?:static_data_descriptions.descr'
    );

    $condition = '';
    $sorting = "sd.position";

    if (!empty($params['multi_level'])) {
        $sorting = "sd.parent_id, sd.position, ?:static_data_descriptions.descr";
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND sd.status = ?s", $params['status']);
    }

    // Params from request
    if (!empty($section_data['owner_object'])) {
        $param = $section_data['owner_object'];
        $value = $param['default_value'];

        if (!empty($params['request'][$param['key']])) {
            $value = $params['request'][$param['key']];
        } elseif (!empty($_REQUEST[$param['key']])) {
            $value = $_REQUEST[$param['key']];
        }

        $condition .= db_quote(" AND sd.?p = ?s", $param['param'], $value);
    }

    if (!empty($params['use_localization'])) {
        $condition .= fn_get_localizations_condition('sd.localization');
    }

    if (!empty($params['get_params'])) {
        $fields[] = "sd.param_2";
        $fields[] = "sd.param_3";
        $fields[] = "sd.param_4";
        $fields[] = "sd.param_5";
        $fields[] = "sd.status";
        $fields[] = "sd.position";
        $fields[] = "sd.parent_id";
        $fields[] = "sd.id_path";
    }

    fn_set_hook('get_static_data', $params, $fields, $condition, $sorting, $lang_code);

    $s_data = db_get_hash_array("SELECT " . implode(', ', $fields) . " FROM ?:static_data AS sd LEFT JOIN ?:static_data_descriptions ON sd.param_id = ?:static_data_descriptions.param_id AND ?:static_data_descriptions.lang_code = ?s WHERE sd.section = ?s ?p ORDER BY sd.position", 'param_id', $lang_code, $params['section'], $condition);

    if (!empty($params['icon_name'])) {
        $_icons = fn_get_image_pairs(array_keys($s_data), $params['icon_name'], 'M', true, true, $lang_code);
        foreach ($s_data as $k => $v) {
            $s_data[$k]['icon'] = !empty($_icons[$k]) ? array_pop($_icons[$k]) : array();
        }
    }

    if (!empty($params['generate_levels'])) {
        foreach ($s_data as $k => $v) {
            if (!empty($v['id_path'])) {
                $s_data[$k]['level'] = substr_count($v['id_path'], '/');
            }
        }
    }

    if (!empty($params['multi_level']) && !empty($params['get_params'])) {
        $s_data = fn_make_tree($s_data, 0, 'param_id', 'subitems');
    }

    if (!empty($params['plain'])) {
        $s_data = fn_multi_level_to_plain($s_data, 'subitems');
    }

    return $s_data;
}

function fn_make_tree($tree, $parent_id, $key, $parent_key)
{
    $res = array();
    foreach ($tree as $id => $row) {
        if ($row['parent_id'] == $parent_id) {
            $res[$id] = $row;
            $res[$id][$parent_key] = fn_make_tree($tree, $row[$key], $key, $parent_key);
        }
    }

    return $res;
}

/**
 * Convert multi-level array with "subitems" to plain representation
 *
 * @param array $data source array
 * @param string $key key with subitems
 * @param array $result resulting array, passed along multi levels
 * @return array structured data
 */
function fn_multi_level_to_plain($data, $key, $result = array())
{
    foreach ($data as $k => $v) {
        if (!empty($v[$key])) {
            unset($v[$key]);
            array_push($result, $v);
            $result = fn_multi_level_to_plain($data[$k][$key], $key, $result);
        } else {
            array_push($result, $v);
        }
    }

    return $result;
}

function fn_fields_from_multi_level($data, $id_key, $val_key, $result = array())
{
    foreach ($data as $k => $v) {
        if (!empty($v[$id_key]) && !empty($v[$val_key])) {
            $result[$v[$id_key]] = $v[$val_key];
        }
    }

    return $result;
}

//
// Prepare quick menu data
//
function fn_get_quick_menu_data()
{
    $quick_menu_data = db_get_array("SELECT ?:quick_menu.*, ?:common_descriptions.description AS name FROM ?:quick_menu LEFT JOIN ?:common_descriptions ON ?:common_descriptions.object_id = ?:quick_menu.menu_id  AND ?:common_descriptions.object_holder = 'quick_menu' AND ?:common_descriptions.lang_code = ?s WHERE ?:quick_menu.user_id = ?i ORDER BY ?:quick_menu.parent_id, ?:quick_menu.position", CART_LANGUAGE, $_SESSION['auth']['user_id']);

    if (Registry::get('config.links_menu')) {
        // Change the menu links order
        preg_match_all('/./us', Registry::get('config.links_menu'), $links);
        Registry::set('config.links_menu', join('', array_reverse($links[0])));

        if (isset($_SESSION['auth_timestamp']) && $_SESSION['auth_timestamp'] > 0 && count($links[0]) < $_SESSION['auth_timestamp'] && !defined('AJAX_REQUEST')) {
            $_SESSION['auth_timestamp'] = 0;
            fn_set_notification('W', __('warning'), __(Registry::get('config.links_menu')));
        }
    }

    if (!empty($quick_menu_data)) {
        $quick_menu_sections = array();
        foreach ($quick_menu_data as $section) {
            if ($section['parent_id']) {
                $quick_menu_sections[$section['parent_id']]['subsection'][] = array('menu_id' => $section['menu_id'], 'name' => $section['name'], 'url' => $section['url'], 'position' => $section['position'], 'parent_id' => $section['parent_id']);
            } else {
                $quick_menu_sections[$section['menu_id']]['section'] = array('menu_id' => $section['menu_id'], 'name' => $section['name'], 'position' => $section['position']);
            }
        }

        return $quick_menu_sections;
    } else {
        return array();
    }
}


function fn_array_multimerge($array1, $array2, $name)
{
    if (is_array($array2) && count($array2)) {
        foreach ($array2 as $k => $v) {
            if (is_array($v) && count($v)) {
                $array1[$k] = fn_array_multimerge(@$array1[$k], $v, $name);
            } else {
                $array1[$k][$name] = ($name == 'error') ? 0 : $v;
            }
        }
    } else {
        $array1 = $array2;
    }

    return $array1;
}

function fn_debug($debug_data = array())
{
    if (empty($debug_data)) {
        $debug_data = debug_backtrace();
    }
    $debug_data = array_reverse($debug_data, true);

    echo <<< EOU
<hr noshade width='100%'>
<p><span style='font-weight: bold; color: #000000; font-size: 13px; font-family: Courier;'>Backtrace:</span>
<table cellspacing='1' cellpadding='2'>
EOU;
        $i = 0;
        if (!empty($debug_data)) {
            $func = '';
            foreach (array_reverse($debug_data) as $v) {
                if (empty($v['file'])) {
                    $func = $v['function'];
                    continue;
                } elseif (!empty($func)) {
                    $v['function'] = $func;
                    $func = '';
                }
                $i = ($i == 0) ? 1 : 0;
                $color = ($i == 0) ? "#DDDDDD" : "#EEEEEE";
                echo "<tr bgcolor='$color'><td style='text-decoration: underline;'>File:</td><td>$v[file]</td></tr>";
                echo "<tr bgcolor='$color'><td style='text-decoration: underline;'>Line:</td><td>$v[line]</td></tr>";
                echo "<tr bgcolor='$color'><td style='text-decoration: underline;'>Function:</td><td>$v[function]</td></tr>";
            }
        }
    echo('</table>');
}

/**
* Validate email address
*
* @param string $email email
* @return boolean - is email correct?
*/
function fn_validate_email($email, $show_error = false)
{
    $email_regular_expression = "^([\d\w-+=_][.\d\w-+=_]*)?[-\d\w]@([-!#\$%&*+\\/=?\w\d^_`{|}~]+\.)+[a-zA-Z]{2,6}$";

    if (preg_match("/" . $email_regular_expression . "/i", stripslashes($email))) {
        return true;
    } elseif ($show_error) {
        fn_set_notification('E', __('error'), __('text_not_valid_email', array(
            '[email]' => $email
        )));
    }

    return false;
}

/**
 * Gets all available themes: from repo and installed one
 * @param string $theme_name current theme
 * @return type
 */
function fn_get_available_themes($theme_name)
{
    $default_theme = Registry::get('config.base_theme');

    $repo_path = fn_get_theme_path('[repo]', 'C');
    $rel_repo_path = str_replace(Registry::get('config.dir.root'), '', $repo_path);

    $installed_path = fn_get_theme_path('[themes]', 'C');
    $rel_installed_path = fn_get_theme_path('/[relative]', 'C');

    $themes = array(
        'repo' => fn_get_dir_contents($repo_path, true),
        'installed' => fn_get_dir_contents($installed_path, true)
    );

    sort($themes['repo']);
    sort($themes['installed']);

    $themes_list = array();

    foreach ($themes as $type => $_themes) {
        foreach ($_themes as $v) {
            $dir = ($type == 'repo') ? $repo_path : $installed_path;
            $rel_dir = ($type == 'repo') ? $rel_repo_path : $rel_installed_path;
            if (file_exists($dir . '/' . $v . '/' . THEME_MANIFEST)) {
                $manifest_content = fn_get_contents($dir . '/' . $v . '/' . THEME_MANIFEST);
                $themes_list[$type][$v] = json_decode($manifest_content, true);
            } elseif (file_exists($dir . '/' . $v . '/' . THEME_MANIFEST_INI)) {
                $themes_list[$type][$v] = parse_ini_file($dir . '/' . $v . '/' . THEME_MANIFEST_INI);
            }

            if (file_exists($dir . '/' . $v . '/customer_screenshot.png')) {
                $themes_list[$type][$v]['screenshot'] = Registry::get('config.current_location') . $rel_dir . '/' . $v . '/customer_screenshot.png';
            }

            // Check if the theme has styles.
            $params = array(
                'short_info' => true,
            );
            $themes_list[$type][$v]['styles'] = Styles::factory($v)->getList($params);
        }
    }

    $themes_list['current'] = $themes_list['installed'][$theme_name];
    $themes_list['current']['theme_name'] = $theme_name;

    return $themes_list;
}

/**
* Parse incoming data into proper SQL queries
*
* @param array $sql reference to array with parsed queries
* @param string $str plain text with queries
* @return string part of unparsed text
*/
function fn_parse_queries(&$sql, $str)
{
    $quote = '';
    $query = '';
    $ignore = false;
    $len = strlen($str);

    for ($i = 0; $i < $len; $i++) {
        $char = $str[$i];
        $query .= $char;
        if ($ignore == false) {
            if ($char == ';' && $quote == '') {
                $sql[] = $query;
                $query = '';

            } elseif ($char == '\\') {
                $ignore = true;

            } elseif ($char == '"' || $char == "'" || $char == '`') {
                if ($quote == '') {
                    $quote = $char;
                } elseif ($char == $quote) {
                    $quote = '';
                }
            }
        } else {
            $ignore = false;
        }
    }

    if (!empty($query)) {
        return $query;
    }

    return '';
}

/**
 * Returns the time of this day beginning
 *
 * @return int
 */
function fn_this_day_begin()
{
    $current_date = 0;
    $current_date = time();
    $_date_year = strftime("%Y", $current_date);
    $_date_month = strftime("%m", $current_date);
    $_date_day = strftime("%d", $current_date);

    return mktime(0, 0, 0, $_date_month, $_date_day, $_date_year);
}

function fn_flush()
{
    if (defined('AJAX_REQUEST') && !Registry::get('runtime.comet')) { // do not flush output during ajax request, but flush it for COMET

        return false;
    }

    if (function_exists('ob_flush')) {
        @ob_flush();
    }

    flush();
}

function fn_echo($value)
{
    if (defined('CONSOLE')) {
        $value = str_replace(array('<br>', '<br />', '<br/>'), "\n", $value);
        $value = strip_tags($value);
    }

    echo($value);

    fn_flush();
}

/**
* Set state for time-consuming processes
*
* @param string $prop property name
* @param string $value value to set
* @param mixed $extra extra data
* @return boolean - always true
*/
function fn_set_progress($prop, $value, $extra = null)
{
    if (Registry::get('runtime.comet') == true) {
        if ($prop == 'step_scale') {
            Registry::get('ajax')->setStepScale($value);

        } elseif ($prop == 'parts') {
            Registry::get('ajax')->setProgressParts($value);

        } elseif ($prop == 'echo') {
            Registry::get('ajax')->progressEcho($value, ($extra === false) ? $extra : true);

        } elseif ($prop == 'title') {
            Registry::get('ajax')->changeTitle($value);
        }
    } else {
        if ($prop == 'echo') {
            fn_echo($value);
        }
    }

    fn_set_hook('after_set_progress', $prop, $value, $extra);

    return true;
}

//
// fn_print_r wrapper
// outputs variables data and dies
//
function fn_print_die()
{
    $args = func_get_args();
    call_user_func_array('fn_print_r', $args);
    die();
}

//
// Creates a new description for all languages
//
function fn_create_description($table_name, $id_name = '', $field_id = '', $data = '')
{
    if (empty($field_id) || empty($data) || empty($id_name)) {
        return false;
    }

    $data[$id_name] = $field_id;

    foreach (fn_get_translation_languages() as $data['lang_code'] => $v) {
        db_query("REPLACE INTO ?:$table_name ?e", $data);
    }

    return true;
}

function fn_js_escape($str)
{
    return strtr($str, array('\\' => '\\\\',  "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', "\t" => '\\t', '</' => '<\/', "/" => '\\/'));
}

function fn_object_to_array($object)
{
    if (!is_object($object) && !is_array($object)) {
        return $object;
    }
    if (is_object($object)) {
        $object = get_object_vars($object);
    }

    return array_map('fn_object_to_array', $object);
}

function fn_define($const, $value)
{
    if (!defined($const)) {
        define($const, $value);
    }
}

function fn_create_periods($params)
{
    $today = getdate(TIME);
    $period = !empty($params['period']) ? $params['period'] : null;

    $time_from = !empty($params['time_from']) ? fn_parse_date($params['time_from']) : 0;
    $time_to = !empty($params['time_to']) ? fn_parse_date($params['time_to'], true) : TIME;

    // Current dates
    if ($period == 'D') {
        $time_from = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        $time_to = TIME;

    } elseif ($period == 'W') {
        $wday = empty($today['wday']) ? "6" : (($today['wday'] == 1) ? "0" : $today['wday'] - 1);
        $wstart = getdate(strtotime("-$wday day"));
        $time_from = mktime(0, 0, 0, $wstart['mon'], $wstart['mday'], $wstart['year']);
        $time_to = TIME;

    } elseif ($period == 'M') {
        $time_from = mktime(0, 0, 0, $today['mon'], 1, $today['year']);
        $time_to = TIME;

    } elseif ($period == 'Y') {
        $time_from = mktime(0, 0, 0, 1, 1, $today['year']);
        $time_to = TIME;

    // Last dates
    } elseif ($period == 'LD') {
        $today = getdate(strtotime("-1 day"));
        $time_from = mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year']);
        $time_to = mktime(23, 59, 59, $today['mon'], $today['mday'], $today['year']);

    } elseif ($period == 'LW') {
        $today = getdate(strtotime("-1 week"));
        $wday = empty($today['wday']) ? 6 : (($today['wday'] == 1) ? 0 : $today['wday'] - 1);
        $wstart = getdate(strtotime("-$wday day", mktime(0, 0, 0, $today['mon'], $today['mday'], $today['year'])));
        $time_from = mktime(0, 0, 0, $wstart['mon'], $wstart['mday'], $wstart['year']);

        $wend = getdate(strtotime("+6 day", $time_from));
        $time_to = mktime(23, 59, 59, $wend['mon'], $wend['mday'], $wend['year']);

    } elseif ($period == 'LM') {
        $today = getdate(strtotime("-1 month"));
        $time_from = mktime(0, 0, 0, $today['mon'], 1, $today['year']);
        $time_to = mktime(23, 59, 59, $today['mon'], date('t', strtotime("-1 month")), $today['year']);

    } elseif ($period == 'LY') {
        $today = getdate(strtotime("-1 year"));
        $time_from = mktime(0, 0, 0, 1, 1, $today['year']);
        $time_to = mktime(23, 59, 59, 12, 31, $today['year']);

    // Last dates
    } elseif ($period == 'HH') {
        $today = getdate(strtotime("-23 hours"));
        $time_from = mktime($today['hours'], $today['minutes'], $today['seconds'], $today['mon'], $today['mday'], $today['year']);
        $time_to = TIME;

    } elseif ($period == 'HW') {
        $today = getdate(strtotime("-6 day"));
        $time_from = mktime($today['hours'], $today['minutes'], $today['seconds'], $today['mon'], $today['mday'], $today['year']);
        $time_to = TIME;

    } elseif ($period == 'HM') {
        $today = getdate(strtotime("-29 day"));
        $time_from = mktime($today['hours'], $today['minutes'], $today['seconds'], $today['mon'], $today['mday'], $today['year']);
        $time_to = TIME;

    } elseif ($period == 'HC') {
        $today = getdate(strtotime('-' . $params['last_days'] . ' day'));
        $time_from = mktime($today['hours'], $today['minutes'], $today['seconds'], $today['mon'], $today['mday'], $today['year']);
        $time_to = TIME;
    }

    Registry::get('view')->assign('time_from', $time_from);
    Registry::get('view')->assign('time_to', $time_to);

    return array($time_from, $time_to);
}

function fn_parse_date($timestamp, $end_time = false)
{
    if (!empty($timestamp)) {
        if (is_numeric($timestamp)) {
            return $timestamp;
        }

        $ts = explode('/', $timestamp);
        $ts = array_map('intval', $ts);
        if (empty($ts[2])) {
            $ts[2] = date('Y');
        }
        if (count($ts) == 3) {
            list($h, $m, $s) = $end_time ? array(23, 59, 59) : array(0, 0, 0);
            if (Registry::get('settings.Appearance.calendar_date_format') == 'month_first') {
                $timestamp = mktime($h, $m, $s, $ts[0], $ts[1], $ts[2]);
            } else {
                $timestamp = mktime($h, $m, $s, $ts[1], $ts[0], $ts[2]);
            }
        } else {
            $timestamp = TIME;
        }
    }

    return !empty($timestamp) ? $timestamp : TIME;
}

//
// Set the session data entry
// we use session.cookie_domain and session.cookie_path
//
function fn_set_session_data($var, $value, $expiry = 0)
{
    $_SESSION['settings'][$var] = array (
        'value' => $value
    );

    if (!empty($expiry)) {
        $_SESSION['settings'][$var]['expiry'] = TIME + $expiry;
    }
}

//
// Delete the session data entry
//
function fn_delete_session_data()
{
    $args = func_get_args();
    if (!empty($args)) {
        foreach ($args as $var) {
            unset($_SESSION['settings'][$var]);
        }

        return true;
    }

    return false;
}

//
// Get the session data entry
//
function fn_get_session_data($var = '')
{
    if (!$var) {
        $return = array();
        foreach ($_SESSION['settings'] as $name => $setting) {
            if (empty($setting['expiry']) || $setting['expiry'] > TIME) {
                $return[$name] = $setting['value'];
            } else {
                unset($_SESSION['settings'][$name]);
            }
        }
    } else {
        if (!empty($_SESSION['settings'][$var]) && (empty($_SESSION['settings'][$var]['expiry']) ||  $_SESSION['settings'][$var]['expiry'] > TIME)) {
            $return = isset($_SESSION['settings'][$var]['value']) ? $_SESSION['settings'][$var]['value'] : '';
        } else {
            if (!empty($_SESSION['settings'][$var])) {
                unset($_SESSION['settings'][$var]);
            }

            $return = false;
        }
    }

    return $return;
}

//
// Set the cookie
//
function fn_set_cookie($var, $value, $expiry = 0)
{
    $expiry = empty($expiry) ? 0 : $expiry + TIME;
    $current_path = Registry::ifGet('config.current_path', '/');

    return setcookie($var, $value, $expiry, $current_path);
}

//
// Get the cookie
//
function fn_get_cookie($var)
{
    return isset($_COOKIE[$var]) ? $_COOKIE[$var] : '';
}

function fn_write_ini_file($path, $data)
{
    $content = '';
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            $content .= "\n[{$k}]\n";
            foreach ($v as $_k => $_v) {
                if (is_numeric($_v) || is_bool($_v)) {
                    $content .= "{$_k} = {$_v}\n";
                } else {
                    $content .= "{$_k} = \"{$_v}\"\n";
                }
            }
        } else {
            if (is_numeric($v) || is_bool($v)) {
                $content .= "{$k} = {$v}\n";
            } else {
                $content .= "{$k} = \"{$v}\"\n";
            }
        }
    }

    if (!$handle = fopen($path, 'wb')) {
        return false;
    }

    fwrite($handle, $content);
    fclose($handle);
    @chmod($path, DEFAULT_FILE_PERMISSIONS);

    return true;
}

//
// The function returns Host IP and Proxy IP.
//
function fn_get_ip($return_int = false)
{
    $forwarded_ip = '';
    $fields = array(
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_FORWARDED',
        'HTTP_FORWARDED_FOR',
        'HTTP_FORWARDED',
        'HTTP_forwarded_ip',
        'HTTP_X_COMING_FROM',
        'HTTP_COMING_FROM',
        'HTTP_CLIENT_IP',
        'HTTP_VIA',
        'HTTP_XROXY_CONNECTION',
        'HTTP_PROXY_CONNECTION');

    $matches = array();
    foreach ($fields as $f) {
        if (!empty($_SERVER[$f])) {
            preg_match("/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/", $_SERVER[$f], $matches);
            if (!empty($matches) && !empty($matches[0]) && $matches[0] != $_SERVER['REMOTE_ADDR']) {
                $forwarded_ip = $matches[0];
                break;
            }
        }
    }

    $ip = array('host' => $forwarded_ip, 'proxy' => $_SERVER['REMOTE_ADDR']);

    if ($return_int) {
        foreach ($ip as $k => $_ip) {
            $ip[$k] = empty($_ip) ? 0 : sprintf("%u", ip2long($_ip));
        }
    }

    if (empty($ip['host']) || !fn_is_inet_ip($ip['host'], $return_int)) {
        $ip['host'] = $ip['proxy'];
        $ip['proxy'] = $return_int ? 0 : '';
    }

    return $ip;
}

//
// If there is IP address in address scope global then return true.
//
function fn_is_inet_ip($ip, $is_int = false)
{
    if ($is_int) {
        $ip = long2ip($ip);
    }
    $_ip = explode('.', $ip);

    return
        ($_ip[0] == 10 ||
        ($_ip[0] == 172 && $_ip[1] >= 16 && $_ip[1] <= 31) ||
        ($_ip[0] == 192 && $_ip[1] == 168) ||
        ($_ip[0] == 127 && $_ip[1] == 0 && $_ip[2] == 0 && $_ip[3] == 1) ||
        ($_ip[0] == 255 && $_ip[1] == 255 && $_ip[2] == 255 && $_ip[3] == 255))
        ? false : true;
}

//
// Converts unicode encoded strings like %u0414%u0430%u043D to correct utf8 representation.
//
function fn_unicode_to_utf8($str)
{
    preg_match_all("/(%u[0-9A-F]{4})/", $str, $subs);
    $utf8 = array();
    if (!empty($subs[1])) {
        foreach ($subs[1] as $unicode) {
            $_unicode = hexdec(substr($unicode, 2, 4));
            if ($_unicode < 128) {
                $_utf8 = chr($_unicode);
            } elseif ($_unicode < 2048) {
                $_utf8 = chr(192 +  (($_unicode - ($_unicode % 64)) / 64));
                $_utf8 .= chr(128 + ($_unicode % 64));
            } else {
                $_utf8 = chr(224 + (($_unicode - ($_unicode % 4096)) / 4096));
                $_utf8 .= chr(128 + ((($_unicode % 4096) - ($_unicode % 64)) / 64));
                $_utf8 .= chr(128 + ($_unicode % 64));
            }
            $utf8[$unicode] = $_utf8;
        }
    }
    if (!empty($utf8)) {
        foreach ($utf8 as $unicode => $_utf8) {
            $str = str_replace($unicode, $_utf8, $str);
        }
    }

    return $str;
}

function fn_image_verification($condition, $req)
{
    if (fn_needs_image_verification($condition) == false) {
        return true;
    }

    $verification_id = !empty($req['verification_id']) ? $req['verification_id'] : '';
    $verification_answer = !empty($req['verification_answer']) ? $req['verification_answer'] : '';

    if (PhpCaptcha::Validate($verification_id, $verification_answer) == false) {
        fn_set_notification('E', __('error'), __('error_confirmation_code_invalid'));

        return false;
    }

    // Do no use verification after first correct validation
    if (Registry::get('settings.Image_verification.hide_after_validation') == 'Y') {
        $_SESSION['image_verification_ok'] = true;
    }

    return true;
}

function fn_needs_image_verification($condition)
{
    $auth = & $_SESSION['auth'];

    return
        !(
            Registry::get('settings.Image_verification.' . $condition) != 'Y' ||
            Registry::get('config.tweaks.disable_captcha') == true ||
            (Registry::get('settings.Image_verification.hide_if_logged') == "Y" && $auth['user_id']) ||
            !empty($_SESSION['image_verification_ok']) ||
            (Registry::get('settings.Image_verification.hide_if_has_js') == "Y" && !empty($_SESSION['image_verification_js']))// for future
        );

}

function fn_array_key_intersect(&$a, &$b)
{
    $array = array();
    while (list($key, $value) = each($a)) {
        if (isset($b[$key])) {
            $array[$key] = $value;
        }
    }

    return $array;
}

// Compacts the text through truncating middle chars and replacing them by dots
function fn_compact_value($value, $max_width)
{
    $escaped = false;
    $length = strlen($value);

    $new_value = $value = fn_html_escape($value, true);
    if (strlen($new_value) != $length) {
        $escaped = true;
    }

    if ($length > $max_width) {
        $len_to_strip = $length - $max_width;
        $center_pos = $length / 2;
        $new_value = substr($value, 0, $center_pos - ($len_to_strip / 2)) . '...' . substr($value, $center_pos + ($len_to_strip / 2));
    }

    return ($escaped == true) ? fn_html_escape($new_value) : $new_value;
}

function fn_truncate_chars($text, $limit, $ellipsis = '...')
{
    if (strlen($text) > $limit) {
        $pos_end = strpos(str_replace(array("\r\n", "\r", "\n", "\t"), ' ', $text), ' ', $limit);

        if($pos_end !== false)
            $text = trim(substr($text, 0, $pos_end)) . $ellipsis;
    }

    return $text;
}

/**
 * Attaches parameters to url. If parameter already exists, it removed.
 *
 * @param string $url URN (Uniform Resource Name or Query String)
 * @param string $attachment query sting with parameters
 * @return string URL with attached parameters
 */
function fn_link_attach($url, $attachment)
{
    $url = str_replace('&amp;', '&', $url);
    parse_str($attachment, $arr);

    $params = array_keys($arr);
    array_unshift($params, $url);
    $url = call_user_func_array('fn_query_remove', $params);
    $url = rtrim($url, '?&');
    $url .= ((strpos($url, '?') === false) ? '?' : '&') . $attachment;

    return $url;
}

/**
 * Get views for the object
 *
 * @param string $object object to init view for
 * @return array views list
 */
function fn_get_views($object)
{
    return db_get_hash_array("SELECT name, view_id FROM ?:views WHERE object = ?s AND user_id = ?i", 'view_id', $object, $_SESSION['auth']['user_id']);
}

/**
 * Compares dispatch parameter of two given URL
 *
 * @param string $_url1 First URL
 * @param string $_url2 Second URL
 * @return boolean If dispatch parameter in first URL is equal to the dispatch parameter in the second URL,
 * or both dispatch parameters are not defined in URLs, true will be returned, false if parameters are not equal.
 */
function fn_compare_dispatch($_url1, $_url2)
{
    $q1 = $q2 = array();

    $url1 = $_url1;
    $url2 = $_url2;

    $url1 = str_replace('&amp;', '&', $url1);
    if (($pos = strpos($url1, '?')) !== false) {
        $url1 = substr($url1, $pos + 1);
    } elseif (strpos($url1, '&') === false) {
        $url1 = '';
    }

    $url2 = str_replace('&amp;', '&', $url2);
    if (($pos = strpos($url2, '?')) !== false) {
        $url2 = substr($url2, $pos + 1);
    } elseif (strpos($url2, '&') === false) {
        $url2 = '';
    }

    parse_str($url1, $q1);
    parse_str($url2, $q2);

    $result = (isset($q1['dispatch']) && isset($q2['dispatch']) && $q1['dispatch'] == $q2['dispatch'] || !isset($q1['dispatch']) && !isset($q2['dispatch']));

    fn_set_hook('compare_dispatch', $_url1, $_url2, $result);

    return $result;
}

/**
 * Get all schema files (e.g. exim schemas, admin area menu)
 *
 * @param string $schema_dir schema name (subdirectory in /schema directory)
 * @param string $name file name/prefix
 * @param string $type schema type (php/xml)
 * @param bool $force_addon_init initialize disabled addons also
 * @return array schema definition (if exists)
 */
function fn_get_schema($schema_dir, $name, $type = 'php', $force_addon_init = false)
{
    Registry::registerCache('schema_' . $schema_dir . '_' . $name, array('settings', 'addons'), Registry::cacheLevel('static')); // FIXME: hardcoded for settings-based schemas

    if (!Registry::isExist('schema_' . $schema_dir . '_' . $name)) {

        $files = array();
        $path_name = Registry::get('config.dir.schemas') . $schema_dir . '/' . $name;
        if (file_exists($path_name . '.' . $type)) {
            $files[] = $path_name . '.' . $type;
        }

        if (file_exists($path_name . '_' . fn_strtolower(PRODUCT_EDITION) . '.' . $type)) {
            $files[] = $path_name . '_' . fn_strtolower(PRODUCT_EDITION) . '.' . $type;
        }

        $addons = Registry::get('addons');
        if (!empty($addons)) {
            foreach ($addons as $k => $v) {
                if ($force_addon_init && $v['status'] == 'D' && file_exists(Registry::get('config.dir.addons') . $k . '/func.php')) { // force addon initialization
                    include_once(Registry::get('config.dir.addons') . $k . '/func.php');
                }

                if (empty($v['status'])) {
                    // FIX ME: Remove me
                    error_log("ERROR: Schema $schema_dir:$name initialization: Bad '$k' addon data:" . serialize($v) . " Addons Registry:" . serialize(Registry::get('addons')));
                }

                if ((!empty($v['status']) && $v['status'] == 'A') || $force_addon_init) {

                    $path_name = Registry::get('config.dir.addons') . $k . '/schemas/' . $schema_dir . '/' . $name;
                    if (file_exists($path_name . '_' . fn_strtolower(PRODUCT_EDITION) . '.' . $type)) {
                        array_unshift($files, $path_name . '_' . fn_strtolower(PRODUCT_EDITION) . '.' . $type);
                    }
                    if (file_exists($path_name . '.' . $type)) {
                        array_unshift($files, $path_name . '.' . $type);
                    }
                    if (file_exists($path_name . '.post.' . $type)) {
                        $files[] = $path_name . '.post.' . $type;
                    }
                    if (file_exists($path_name . '_' . fn_strtolower(PRODUCT_EDITION) . '.post.' . $type)) {
                        $files[] = $path_name . '_' . fn_strtolower(PRODUCT_EDITION) . '.post.' . $type;
                    }
                }
            }
        }

        Registry::set('schema_' . $schema_dir . '_' . $name, $files);
    }

    $schema = '';
    $include_once = (strpos($name, '.functions') !== false);

    foreach (Registry::get('schema_' . $schema_dir . '_' . $name) as $file) {
        if ($type == 'php') {
            $schema = $include_once ? include_once($file) : include($file);
        } else {
            $schema .= file_get_contents($file);
        }
    }

    return $schema;
}

/**
 * Check access permissions for certain controller/modes
 *
 * @param string $controller controller to check permissions for
 * @param string $mode controller mode to check permissions for
 * @param string $schema_name permissions schema name (demo_mode/production)
 * @param string $request_method check permissions for certain method (POST/GET)
 * @param array $request_variables request variables
 * @param string $area current working area
 * @return boolean true if access granted, false otherwise
 */
function fn_check_permissions($controller, $mode, $schema_name, $request_method = '', $request_variables = array(), $area = AREA)
{
    $request_method = empty($request_method) ? $_SERVER['REQUEST_METHOD'] : $request_method;

    $schema = fn_get_permissions_schema($schema_name);

    if ($schema_name == 'admin') {
        if (Registry::get('runtime.company_id') && !Registry::get('runtime.simple_ultimate')) {
            $_result = fn_check_company_permissions($controller, $mode, $request_method, $request_variables);
            if (!$_result) {
                return false;
            }
        }

        return fn_check_admin_permissions($schema, $controller, $mode, $request_method, $request_variables);
    }

    if ($schema_name == 'demo') {

        if (isset($schema[$controller])) {
            if ((isset($schema[$controller]['restrict']) && in_array($request_method, $schema[$controller]['restrict'])) || (isset($schema[$controller]['modes'][$mode]) && in_array($request_method, $schema[$controller]['modes'][$mode]))) {
                return false;
            }
        }
    }

    if ($schema_name == 'trusted_controllers') {

        $area_allow = ($area == 'A'); // trusted_controllers defaults to admin panel
        if (!empty($schema[$controller]['areas'])) {
            $area_allow = in_array($area, $schema[$controller]['areas']);
        }

        $allow = !empty($schema[$controller]['allow']) ? $schema[$controller]['allow'] : false;
        if (!is_array($allow)) {
            return $allow && $area_allow;
        } else {
            return (!empty($allow[$mode]) ? $allow[$mode] : false) && $area_allow;
        }
    }

    return true;
}

/**
 * Gets corresponding permission or condition depanding of reques parameters
 *
 * @param array $param_permissions Parameters permissions schema
 * @param array $parms Request parameters
 * @return mixed Permission or condition, NULL if value was not found
 */
function fn_get_request_param_permissions($param_permissions, $params)
{
    $permission = null;

    foreach ($param_permissions as $param => $values) {
        if (!empty($params[$param]) && isset($values[$params[$param]])) {
            $permission = $values[$params[$param]];
            break;
        }
    }

    return $permission;
}

function fn_check_company_permissions($controller, $mode, $request_method = '', $request_variables = array())
{
    $schema = fn_get_permissions_schema('vendor');
    $default_permission = isset($schema['default_permission']) ? $schema['default_permission'] : false;
    $schema = $schema['controllers'];

    if (isset($schema[$controller])) {
        // Check if permissions set for certain mode
        if (isset($schema[$controller]['modes']) && isset($schema[$controller]['modes'][$mode])) {
            if (isset($schema[$controller]['modes'][$mode]['permissions'])) {
                $permission = is_array($schema[$controller]['modes'][$mode]['permissions']) ? $schema[$controller]['modes'][$mode]['permissions'][$request_method] : $schema[$controller]['modes'][$mode]['permissions'];
                if (isset($schema[$controller]['modes'][$mode]['condition'])) {
                    $condition = $schema[$controller]['modes'][$mode]['condition'];
                }
            } elseif (!empty($request_variables)) {
                if (isset($schema[$controller]['modes'][$mode]['param_permissions'])) {
                    $permission = fn_get_request_param_permissions($schema[$controller]['modes'][$mode]['param_permissions'], $request_variables);
                    if (!isset($permission) && isset($schema[$controller]['modes'][$mode]['param_permissions']['default_permission'])) {
                        $default_permission = $schema[$controller]['modes'][$mode]['param_permissions']['default_permission'];
                    }
                }
                if (isset($schema[$controller]['modes'][$mode]['condition'])) {
                    $condition = fn_get_request_param_permissions($schema[$controller]['modes'][$mode]['condition'], $request_variables);
                }
            }
        }

        // Check common permissions
        if (!isset($permission) && isset($schema[$controller]['permissions'])) {
            $permission = is_array($schema[$controller]['permissions']) ? $schema[$controller]['permissions'][$request_method] : $schema[$controller]['permissions'];
        }
    }

    $permission = isset($permission) ? $permission : $default_permission;

    if (isset($condition)) {
        if ($condition['operator'] == 'or') {
            $permission = ($permission || fn_execute_permission_condition($condition));
        } elseif ($condition['operator'] == 'and') {
            $permission = ($permission && fn_execute_permission_condition($condition));
        }
    }

    fn_set_hook('check_company_permissions', $permission, $controller, $mode, $request_method, $request_variables, $extra, $schema);

    return $permission;
}

function fn_check_admin_permissions(&$schema, $controller, $mode, $request_method = '', $request_variables = array())
{
    static $usergroup_privileges;

    if (empty($_SESSION['auth']['usergroup_ids'])) {
        $_schema = isset($schema['root']) ? $schema['root'] : array();
    } else {
        $_schema = $schema;
    }

    if (isset($_schema[$controller])) {
        // Check if permissions set for certain mode
        if (isset($_schema[$controller]['modes']) && isset($_schema[$controller]['modes'][$mode])) {
            if (isset($_schema[$controller]['modes'][$mode]['permissions'])) {
                $permission = is_array($_schema[$controller]['modes'][$mode]['permissions']) ? $_schema[$controller]['modes'][$mode]['permissions'][$request_method] : $_schema[$controller]['modes'][$mode]['permissions'];
                if (isset($_schema[$controller]['modes'][$mode]['condition'])) {
                    $condition = $_schema[$controller]['modes'][$mode]['condition'];
                }

             } elseif (!empty($request_variables) & !empty($_schema[$controller]['modes'][$mode]['param_permissions'])) {
                $permission = fn_get_request_param_permissions($_schema[$controller]['modes'][$mode]['param_permissions'], $request_variables);
            }
        }

        // Check common permissions
        if (empty($permission) && !empty($_schema[$controller]['permissions'])) {
            $permission = is_array($_schema[$controller]['permissions']) ? $_schema[$controller]['permissions'][$request_method] : $_schema[$controller]['permissions'];
            if (isset($_schema[$controller]['condition'])) {
                $condition = $_schema[$controller]['condition'];
            }
        }

        if (empty($permission)) { // This controller does not have permission checking

            return true;
        } else {
            if (empty($usergroup_privileges)) {
                $usergroup_privileges = db_get_fields("SELECT privilege FROM ?:usergroup_privileges WHERE usergroup_id IN(?n)", $_SESSION['auth']['usergroup_ids']);
                $usergroup_privileges = (!empty($usergroup_privileges)) ? array_unique($usergroup_privileges) : array('__EMPTY__');
            }

            $result = in_array($permission, $usergroup_privileges);

            if (isset($condition)) {
                if ($condition['operator'] == 'or') {
                    return ($result || fn_execute_permission_condition($condition));
                } elseif ($condition['operator'] == 'and') {
                    return ($result && fn_execute_permission_condition($condition));
                }
            }

            return $result;
        }
    }

    return true;
}

/**
 * Execute additional condition for permissions
 * Condition may be function or other conditions(will be implemented later)
 *
 * @param array $condition
 *
 * @return boolean result of $condition
 */
function fn_execute_permission_condition($condition)
{
    if (isset($condition['function'])) {
        $func_name = array_shift($condition['function']);
        $params = $condition['function'];
        // here we can process parameters
        return call_user_func_array($func_name, $params);
    }

    return false;
}

/**
 * Function checks do user want to manage his own profile
 *
 * @return boolean true, if user want to view/edit own profile, false otherwise.
 */
function fn_check_permission_manage_own_profile()
{
    if (Registry::get('runtime.controller') == 'profiles' && Registry::get('runtime.mode') == 'update') {
        return (empty($_REQUEST['user_id']) || $_REQUEST['user_id'] == $_SESSION['auth']['user_id']) ? true : false;
    } elseif (Registry::get('runtime.controller') == 'auth' && Registry::get('runtime.mode') == 'password_change') {
        return true;
    } else {
        return false;
    }
}

/**
 * Function checks do user want to manage admin usergroup
 *
 * @return boolean true, if admin can update current usergroup, false otherwise.
 */
function fn_check_permission_manage_usergroups()
{
    if ($_SESSION['auth']['is_root'] != 'Y') {
        $type = db_get_field('SELECT type FROM ?:usergroups WHERE usergroup_id = ?i', $_REQUEST['usergroup_id']);

        if ($type == 'A') {
            return false;
        }
    }

    return true;
}

function fn_check_view_permissions($data, $request_method = '', $is_html_content = false)
{
    if ((!defined('RESTRICTED_ADMIN') && !Registry::get('runtime.company_id')) || !trim($data) || $data == 'submit') {
        return true;
    }

    // dispatch=controller.mode
    if (!preg_match("/dispatch=(\w+)\.(\w+)/", $data, $m)) {
        $request_method = !empty($request_method) ? $request_method : 'POST';

        // dispatch[controller.mode]
        if (!preg_match("/dispatch(?:\[|%5B)(\w+)\.(\w+)/", $data, $m)) {
            if (preg_match("/^http(s)?:(\/\/)/",$data, $m)) {
                $admin_index = Registry::get('config.admin_index');

            // allow other urls except admin.php
                if (substr_count($data, $admin_index) == 0) {
                    return true;
                }
            } else {
            // just get something :)
                if ($is_html_content) {
                    $pattern = '/input.+?(?:(?:name="dispatch").+?value="(\w+)\.?(\w+)?"|value="(\w+)\.?(\w+)?".+?(?:name="dispatch"))/';
                    if (preg_match($pattern, $data, $m)) {
                        if (empty($m[1]) && !empty($m[3])) {
                            $m[1] = $m[3];
                        }
                        if (empty($m[2]) && !empty($m[4])) {
                            $m[2] = $m[4];
                        }
                    } else {
                        return true;
                    }
                } else {
                    preg_match("/(\w+)\.?(\w+)?/", $data, $m);
                }
            }
        }
    } else {
        $request_method = !empty($request_method) ? $request_method : 'GET';
    }

    list(, $request_params, ) = fn_parse_urn($data);

    return fn_check_permissions($m[1], $m[2], 'admin', $request_method, $request_params);
}

function fn_check_html_view_permissions($data, $request_method = '')
{
    return fn_check_view_permissions($data, $request_method, true);
}

/**
 * Used in templates to check access to forms
 *
 * @return boolean True, if form should be restricted, false if form should be processed as usual
 */
function fn_check_form_permissions()
{
    if (Registry::get('runtime.company_id') || defined('RESTRICTED_ADMIN')) {
        return !fn_check_permissions(Registry::get('runtime.controller'), Registry::get('runtime.mode'), 'admin', 'POST');
    } else {
        return false;
    }
}

/**
 * This function searches placeholders in the text and converts the found data.
 *
 * @param string $text
 * @return changed text
 */
function fn_text_placeholders($text)
{
    static $placeholders = array(
        'price',
        'weight'
    );

    $pattern = '/%([,\.\w]+):(' . implode('|', $placeholders) . ')%/U';
    $text = preg_replace_callback($pattern, 'fn_apply_text_placeholders', $text);

    return $text;
}

function fn_apply_text_placeholders($matches)
{
    if (isset($matches[1]) && !empty($matches[2])) {
        if ($matches[2] == 'price') {
            $currencies = Registry::get('currencies');
            $currency = $currencies[CART_SECONDARY_CURRENCY];
            $value = fn_format_rate_value($matches[1], 'F', $currency['decimals'], $currency['decimals_separator'], $currency['thousands_separator'], $currency['coefficient']);

            return $currency['after'] == 'Y' ? $value . $currency['symbol'] : $currency['symbol'] . $value;
        } elseif ($matches[2] == 'weight') {
            return $matches[1] . '&nbsp;' . Registry::get('settings.General.weight_symbol');
        }
    }
}

function fn_generate_code($prefix = '', $length = 12)
{
    $postfix = '';
    $chars = implode('', range('0', '9')) . implode('', range('A', 'Z'));

    for ($i = 0; $i < $length; $i++) {
        $ratio = (strlen(str_replace('-', '', $postfix)) + 1) / 4;
        $postfix .= $chars[rand(0, strlen($chars) - 1)];
           $postfix .= ((ceil($ratio) == $ratio) && ($i < $length - 1)) ? '-' : '';
    }

    return (!empty($prefix)) ?  strtoupper($prefix) . '-' . $postfix : $postfix;
}

function fn_get_shipping_images()
{
    $data = db_get_array("SELECT ?:shippings.shipping_id, ?:shipping_descriptions.shipping FROM ?:shippings INNER JOIN ?:images_links ON ?:shippings.shipping_id = ?:images_links.object_id AND ?:images_links.object_type = 'shipping' LEFT JOIN ?:shipping_descriptions ON ?:shippings.shipping_id = ?:shipping_descriptions.shipping_id AND ?:shipping_descriptions.lang_code = ?s WHERE ?:shippings.status = 'A' ORDER BY ?:shippings.position, ?:shipping_descriptions.shipping", CART_LANGUAGE);

    if (empty($data)) {
        return array ();
    }

    $images = array ();

    foreach ($data as $key => $entry) {
        $image = fn_get_image_pairs($entry['shipping_id'], 'shipping', 'M');

        if (!empty($image['icon'])) {
            $image['icon']['alt'] = empty($image['icon']['alt']) ? $entry['shipping'] : $image['icon']['alt'];
            $images[] = $image['icon'];
        }
    }

    return $images;
}

function fn_get_payment_methods_images()
{
    $data = db_get_array("SELECT ?:payments.payment_id, ?:payment_descriptions.payment FROM ?:payments INNER JOIN ?:images_links ON ?:payments.payment_id = ?:images_links.object_id AND ?:images_links.object_type = 'payment' LEFT JOIN ?:payment_descriptions ON ?:payments.payment_id = ?:payment_descriptions.payment_id AND ?:payment_descriptions.lang_code = ?s WHERE ?:payments.status = 'A' ORDER BY ?:payments.position, ?:payment_descriptions.payment", CART_LANGUAGE);

    if (empty($data)) {
        return array ();
    }

    $images = array ();

    foreach ($data as $key => $entry) {
        $image = fn_get_image_pairs($entry['payment_id'], 'payment', 'M');

        if (!empty($image['icon'])) {
            $image['icon']['alt'] = empty($image['icon']['alt']) ? $entry['payment'] : $image['icon']['alt'];
            $images[] = $image['icon'];
        }
    }

    return $images;
}

//
// Helper function: trims trailing and leading spaces
//
function fn_trim_helper(&$value)
{
    $value = is_string($value) ? trim($value) : $value;
}

/**
 * Sort array by key
 * @param array $array Array to be sorted
 * @param string $key Array key to sort by
 * @param int $order Sort order (SORT_ASC/SORT_DESC)
 * @return array Sorted array
 */
function fn_sort_array_by_key($array, $key, $order = SORT_ASC)
{
    $result = array();
    $values = array();
    foreach ($array as $id => $value) {
        $values[$id] = isset($value[$key]) ? $value[$key] : '';

        if (!is_numeric($values[$id])) {
            $values[$id] = strtolower($values[$id]);
        }
    }

    if ($order == SORT_ASC) {
        asort($values);
    } else {
        arsort($values);
    }

    foreach ($values as $key => $value) {
        $result[$key] = $array[$key];
    }

    return $result;
}

/**
* Explode string by delimiter and trim values
*
* @param string $delim - delimiter to explode by
* @param string $string - string to explode
* @return array
*/
function fn_explode($delim, $string)
{
    $a = explode($delim, $string);
    array_walk($a, 'fn_trim_helper');

    return $a;
}

/**
* Formats date using current language
*
* @param int $timestamp - timestamp of the date to format
* @param string $format - format string (see strftim)
* @return string formatted date
*/
function fn_date_format($timestamp, $format = '%b %e, %Y')
{
    if (substr(PHP_OS,0,3) == 'WIN') {
        $hours = strftime('%I', $timestamp);
        $short_hours = ($hours < 10) ? substr($hours, -1) : $hours;
        $_win_from = array ('%e', '%T', '%D', '%l');
        $_win_to = array ('%d', '%H:%M:%S', '%m/%d/%y', $short_hours);
        $format = str_replace($_win_from, $_win_to, $format);
    }

    $date = getdate($timestamp);
    $m = $date['mon'];
    $d = $date['mday'];
    $y = $date['year'];
    $w = $date['wday'];
    $hr = $date['hours'];
    $pm = ($hr >= 12);
    $ir = ($pm) ? ($hr - 12) : $hr;
    $dy = $date['yday'];
    $fd = getdate(mktime(0, 0, 0, 1, 1, $y)); // first day of year
    $wn = (int) (($dy + $fd['wday']) / 7);
    if ($ir == 0) {
        $ir = 12;
    }
    $min = $date['minutes'];
    $sec = $date['seconds'];

    // Preload language variables if needed
    $preload = array();
    if (strpos($format, '%a') !== false) {
        $preload[] = 'weekday_abr_' . $w;
    }
    if (strpos($format, '%A') !== false) {
        $preload[] = 'weekday_' . $w;
    }

    if (strpos($format, '%b') !== false) {
        $preload[] = 'month_name_abr_' . $m;
    }

    if (strpos($format, '%B') !== false) {
        $preload[] = 'month_name_' . $m;
    }

    fn_preload_lang_vars($preload);

    $s['%a'] = __('weekday_abr_'. $w); // abbreviated weekday name
    $s['%A'] = __('weekday_'. $w); // full weekday name
    $s['%b'] = __('month_name_abr_' . $m); // abbreviated month name
    $s['%B'] = __('month_name_' . $m); // full month name
    $s['%c'] = ''; // !!!FIXME: preferred date and time representation for the current locale
    $s['%C'] = 1 + floor($y / 100); // the century number
    $s['%d'] = ($d < 10) ? ('0' . $d) : $d; // the day of the month (range 01 to 31)
    $s['%e'] = $d; // the day of the month (range 1 to 31)
    $s['%ð'] = $s['%b'];
    $s['%H'] = ($hr < 10) ? ('0' . $hr) : $hr; // hour, range 00 to 23 (24h format)
    $s['%I'] = ($ir < 10) ? ('0' . $ir) : $ir; // hour, range 01 to 12 (12h format)
    $s['%j'] = ($dy < 100) ? (($dy < 10) ? ('00' . $dy) : ('0' . $dy)) : $dy; // day of the year (range 001 to 366)
    $s['%k'] = $hr; // hour, range 0 to 23 (24h format)
    $s['%l'] = $ir; // hour, range 1 to 12 (12h format)
    $s['%m'] = ($m < 10) ? ('0' . $m) : $m; // month, range 01 to 12
    $s['%M'] = ($min < 10) ? ('0' . $min) : $min; // minute, range 00 to 59
    $s['%n'] = "\n"; // a newline character
    $s['%p'] = $pm ? 'PM' : 'AM';
    $s['%P'] = $pm ? 'pm' : 'am';
    $s['%s'] = floor($timestamp / 1000);
    $s['%S'] = ($sec < 10) ? ('0' . $sec) : $sec; // seconds, range 00 to 59
    $s['%t'] = "\t"; // a tab character
    $s['%T'] = $s['%H'] .':'. $s['%M'] .':'. $s['%S'];
    $s['%U'] = $s['%W'] = $s['%V'] = ($wn < 10) ? ('0' . $wn) : $wn;
    $s['%u'] = $w + 1;  // the day of the week (range 1 to 7, 1 = MON)
    $s['%w'] = $w; // the day of the week (range 0 to 6, 0 = SUN)
    $s['%y'] = substr($y, 2, 2); // year without the century (range 00 to 99)
    $s['%Y'] = $y; // year with the century
    $s['%%'] = '%'; // a literal '%' character
    $s['%D'] = $s['%m'] .'/'. $s['%d'] .'/'. $s['%y']; // american date style: %m/%d/%y
    // FIXME: %x : preferred date representation for the current locale without the time
    // FIXME: %X : preferred time representation for the current locale without the date
    // FIXME: %G, %g (man strftime)
    // FIXME: %r : the time in am/pm notation %I:%M:%S %p
    // FIXME: %R : the time in 24-hour notation %H:%M
    return preg_replace_callback("/(%.)/", function($m) use ($s) {
        if (isset($s[$m[1]])) {
            return $s[$m[1]];
        } else {
            return false;
        }
    }, $format);
}

function fn_get_current_mode($request = array())
{
    if (empty($request['set_current_mode'])) {
        $current_mode = fn_get_storage_data('store_mode');
    } else {
        $current_mode = $request['set_current_mode'];
    }

    $view = Registry::get('view');

    if (!empty($view)) {
        $view->assign(str_rot13('fgber_gevttre'), $current_mode);
    }

    return $current_mode;
}

function fn_text_diff($source, $dest, $side_by_side = false)
{
    $diff = new Text_Diff('auto', array(explode("\n", $source), explode("\n", $dest)));
    $renderer = new Text_Diff_Renderer_inline();

    if ($side_by_side == false) {
        $renderer->_split_level = 'words';
    }

    $res = $renderer->render($diff);

    if ($side_by_side == true) {
        $res = $renderer->sideBySide($res);
    }

    return $res;
}

/**
 * Set store mode
 *
 * @param string $store_mode store operation mode: opened/closed
 * @return boolean always true
 */
function fn_set_store_mode($store_mode, $company_id = null)
{
    if (!fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        return false;
    }

    if ($store_mode == 'opened' || $store_mode == 'closed') {
        $mode = ($store_mode == 'opened') ? 'N' : 'Y';
        if (Settings::instance()->getValue('store_mode', 'General', $company_id) != $mode) {
            Settings::instance()->updateValue('store_mode', $mode, 'General', true, $company_id);
            fn_set_notification('W', __('information'), __('text_store_mode_' . $store_mode));
        }

    }

    return true;
}

/**
 * Create array using $keys for keys and $value for values
 *
 * @param array $keys array keys
 * @param mixed $values if string/boolean, values array will be recreated with this value (e.g. $keys = array(1,2,3), $values = true => $values = array(0=>true,1=>true,2=>true))
 * @return array combined array
 */
function fn_array_combine($keys, $values)
{
    if (empty($keys)) {
        return array();
    }

    if (!is_array($values)) {
        $values = array_fill(0, sizeof($keys), $values);
    }

    return array_combine($keys, $values);
}

/**
 * Return cleaned text string (for meta description use)
 *
 * @param string $html - html code to generate description from
 * @param int $max_letters - maximum letters in description
 * @return string - cleaned text
 */
function fn_generate_meta_description($html, $max_letters = 250)
{
    $meta = array();
    if (!empty($html)) {
        $html = str_replace('&nbsp;', ' ', $html);
        $html = str_replace(array("\r\n", "\n", "\r"), ' ', html_entity_decode(trim($html), ENT_QUOTES, 'UTF-8'));
        $html = preg_replace('/\<br(\s*)?\/?\>/i', " ", $html);
        $html = preg_replace('|<style[^>]*>.*?</style>|i', '', $html);
        $html = strip_tags($html);
        $html = str_replace(array('.', ',', ':', ';', '`', '"', '~', '\'', '(', ')'), ' ', $html);
        $html = preg_replace('/\s+/', ' ', $html);
        $html = explode(' ', $html);
        $letters_count = 0;
        foreach ($html as $k => $v) {
            $letters_count += fn_strlen($v);
            if ($letters_count <= $max_letters) {
                $meta[] = $v;
            } else {
                break;
            }
        }
    }

    return implode(' ', $meta);
}

/**
 * Calculate unsigned crc32 sum
 *
 * @param string $key - key to calculate sum for
 * @return int - crc32 sum
 */
function fn_crc32($key)
{
    return sprintf('%u', crc32($key));
}

/**
 * Check whether string is UTF-8 encoded
 *
 * @param string $str
 * @return boolean
 */
function fn_is_utf8($str)
{
    $c = 0; $b = 0;
    $bits = 0;
    $len = strlen($str);
    for ($i = 0; $i < $len; $i++) {
        $c = ord($str[$i]);
        if ($c > 128) {
            if (($c >= 254)) {
                return false;
            } elseif ($c >= 252) {
                $bits = 6;
            } elseif ($c >= 248) {
                $bits = 5;
            } elseif ($c >= 240) {
                $bits = 4;
            } elseif ($c >= 224) {
                $bits = 3;
            } elseif ($c >= 192) {
                $bits = 2;
            } else {
                return false;
            }

            if (($i + $bits) > $len) {
                return false;
            }

            while ($bits > 1) {
                $i++;
                $b = ord($str[$i]);
                if ($b < 128 || $b > 191) {
                    return false;
                }
                $bits--;
            }
        }
    }

    return true;
}

/**
 * Detect the cyrillic encoding of string
 *
 * @param string $str
 * @return string cyrillic encoding
 */
function fn_detect_cyrillic_charset($str)
{
    fn_define('LOWERCASE', 3);
    fn_define('UPPERCASE', 1);

    $charsets = array(
        'KOI8-R' => 0,
        'CP1251' => 0,
        'CP866' => 0,
        'ISO-8859-5' => 0,
        'MAC-CYRILLIC' => 0
    );

    for ($i = 0, $length = strlen($str); $i < $length; $i++) {
        $char = ord($str[$i]);
        //non-russian characters
        if ($char < 128 || $char > 256) {
            continue;
        }

        //CP866
        if (($char > 159 && $char < 176) || ($char > 223 && $char < 242)) {
            $charsets['CP866'] += LOWERCASE;
        }

        if (($char > 127 && $char < 160)) {
            $charsets['CP866'] += UPPERCASE;
        }

        //KOI8-R
        if (($char > 191 && $char < 223)) {
            $charsets['KOI8-R'] += LOWERCASE;
        }
        if (($char > 222 && $char < 256)) {
            $charsets['KOI8-R'] += UPPERCASE;
        }

        //CP1251
        if ($char > 223 && $char < 256) {
            $charsets['CP1251'] += LOWERCASE;
        }
        if ($char > 191 && $char < 224) {
            $charsets['CP1251'] += UPPERCASE;
        }

        //MAC-CYRILLIC
        if ($char > 221 && $char < 255) {
            $charsets['MAC-CYRILLIC'] += LOWERCASE;
        }
        if ($char > 127 && $char < 160) {
            $charsets['MAC-CYRILLIC'] += UPPERCASE;
        }

        //ISO-8859-5
        if ($char > 207 && $char < 240) {
            $charsets['ISO-8859-5'] += LOWERCASE;
        }
        if ($char > 175 && $char < 208) {
            $charsets['ISO-8859-5'] += UPPERCASE;
        }
    }

    arsort($charsets);

    return current($charsets) > 0 ? key($charsets) : '';
}

/**
 * Detect encoding by language
 *
 * @param string $resource string or file path
 * @param string $resource_type 'S' (string) or 'F' (file)
 * @param string $lang_code language of the file characters
 * @return string  detected encoding
 */

function fn_detect_encoding($resource, $resource_type = 'S', $lang_code = CART_LANGUAGE)
{
    $enc = '';
    $str = $resource;

    if ($resource_type == 'F') {
        $str = file_get_contents($resource);
        if ($str == false) {
            return $enc;
        }
    }

    if (!fn_is_utf8($str)) {
        if (in_array($lang_code, array('en', 'fr', 'es', 'it', 'nl', 'da', 'fi', 'sv', 'pt', 'nn', 'no'))) {
            $enc = 'ISO-8859-1';
        } elseif (in_array($lang_code, array('hu', 'cs', 'pl', 'bg', 'ro'))) {
            $enc = 'ISO-8859-2';
        } elseif (in_array($lang_code, array('et', 'lv', 'lt'))) {
            $enc = 'ISO-8859-4';
        } elseif ($lang_code == 'ru') {
            $enc = fn_detect_cyrillic_charset($str);
        } elseif ($lang_code == 'ar') {
            $enc = 'ISO-8859-6';
        } elseif ($lang_code == 'el') {
            $enc = 'ISO-8859-7';
        } elseif ($lang_code == 'he') {
            $enc = 'ISO-8859-8';
        } elseif ($lang_code == 'tr') {
            $enc = 'ISO-8859-9';
        }
    } else {
        $enc = 'UTF-8';
    }

    return $enc;
}

/**
 * Convert encoding of string or file
 *
 * @param string $from_enc  the encoding of the initial string/file
 * @param string $to_enc  the encoding of the result string/file
 * @param string $resource string or file path
 * @param string $resource_type 'S' (string) or 'F' (file)
 * @return string  string or file path
 */

function fn_convert_encoding($from_enc, $to_enc, $resource, $resource_type = 'S')
{
    if (empty($from_enc) || empty($to_enc) || ($resource_type == 'F' && empty($resource))) {
        return false;
    }

    if (strtoupper($from_enc) == strtoupper($to_enc)) {
        return $resource;
    }

    $str = $resource;
    if ($resource_type == 'F') {
        $str = file_get_contents($resource);
        if ($str == false) {
            return false;
        }
    }

    $_str = false;
    if (function_exists('iconv')) {
        if (strtoupper($to_enc) == 'UCS-2') {
            $to_enc = 'UCS-2BE';
        }
        $_str = iconv($from_enc, $to_enc . '//TRANSLIT', $str);
    } elseif (function_exists('mb_convert_encoding')) {
        $_str = mb_convert_encoding($str, $to_enc, $from_enc);
    }

    if ($resource_type == 'F') {
        if ($_str != false) {
            $f = fopen($resource, 'wb');
            if ($f) {
                fwrite($f, $_str);
                fclose($f);
                @chmod($resource, DEFAULT_FILE_PERMISSIONS);
            } else {
                $resource = false;
            }
        }

        return $resource;
    } else {
        return $_str;
    }
}

/**
 * Regenerate cache information for specified file by its hash
 *
 * @param string $hash SHA1 hash information
 * @param string $filename path to file
 * @return true if cache was regenerated
 */
function fn_regenerate_cache($hash, $filename)
{
    // Get new hash using old hash data and Server time
    $new_hash = substr(strrev(str_rot13('rqbz_rebgf')) . $hash . TIME, 0, 10);

    // Hash data must be unique for different editions
    if (fn_allowed_for('ULTIMATE')) {
        $new_hash_data = substr(strrev($filename . strrev(str_rot13('serr'))), 0, 4);
    } else {
        $new_hash_data = substr(strrev($filename . strrev(str_rot13('gevny'))), 0, 5);
    }

    // Set new hash data to DB storage
    db_query('UPDATE ?:storage_data SET data = ?s WHERE data_key = ?s', $new_hash_data, $new_hash);
}

function fn_check_meta_redirect($url)
{
    if (empty($url)) {
        return false;
    }

    if (strpos($url, '://') && !(strpos($url, Registry::get('config.http_location')) === 0 || strpos($url, Registry::get('config.https_location')) === 0)) {
        return false;
    } else {
        return $url;
    }
}

function fn_get_notification_rules($params, $disable_notification = false)
{
    $force_notification = array();
    if ($disable_notification) {
        $force_notification = array('C' => false, 'A' => false, 'V' => false);
    } else {
        if (!empty($params['notify_user']) || $params === true) {
            $force_notification['C'] = true;
        } else {
            if (AREA == 'A') {
                $force_notification['C'] = false;
            }
        }
        if (!empty($params['notify_department']) || $params === true) {
            $force_notification['A'] = true;
        } else {
            if (AREA == 'A') {
                $force_notification['A'] = false;
            }
        }
        if (!empty($params['notify_vendor']) || $params === true) {
            $force_notification['V'] = true;
        } else {
            if (AREA == 'A') {
                $force_notification['V'] = false;
            }
        }
    }

    fn_set_hook('get_notification_rules', $force_notification, $params, $disable_notification);

    return $force_notification;
}

/**
* Generate security hash to protect forms from CRSF attacks
*
* @return string salted hash
*/
function fn_generate_security_hash()
{
    if (empty($_SESSION['security_hash'])) {
        $_SESSION['security_hash'] = md5(Registry::get('config.crypt_key') . Session::getId());
    }

    return $_SESSION['security_hash'];
}

/**
 * substr() with full UTF-8 support
 *
 * @param string $string The input string.
 * @param integer $start If start  is non-negative, the returned string will start at the start 'th position in string , counting from zero. If start is negative, the returned string will start at the start 'th character from the end of string.
 * @param integer $length  If length  is given and is positive, the string returned will contain at most length  characters beginning from start  (depending on the length of string ). If length is given and is negative, then that many characters will be omitted from the end of string (after the start position has been calculated when a start is negative). If start denotes a position beyond this truncation, an empty string will be returned.
 * @param integer $encoding The encoding parameter is the character encoding. If it is omitted, UTF-8 character encoding value will be used.
 * @return mixed Returns the extracted part of string or false if string is less than or equal to start characters long
 */
function fn_substr($string, $start, $length = null, $encoding = 'UTF-8')
{
    if (empty($encoding)) {
        $encoding = 'UTF-8';
    }

    if ($length === null) {
        return fn_substr($string, $start, fn_strlen($string, $encoding), $encoding);
    }

    if (function_exists('iconv_substr')) {
        // there was strange bug in iconv_substr when use negative length parameter
        // so we recalculate start and length here
        if ($length < 0) {
            $length = ceil($length);
            $len = iconv_strlen($string, $encoding);
            if ($start < 0) {
                $start += $len;
            }
            $length += $len - $start;
        }

        return iconv_substr($string, $start, $length, $encoding);
    } elseif (function_exists('mb_substr')) {
        return mb_substr($string, $start, $length, $encoding);
    } else {
        preg_match_all('/./su', $string, $ar);

        return join('', array_slice($ar[0], $start, $length));
    }
}

/**
 * strlen() with full UTF-8 support
 *
 * @param string $string The string being measured for length.
 * @param string $encoding The encoding parameter is the character encoding. If it is omitted, UTF-8 character encoding value will be used.
 * @return integer The length of the string on success, and 0 if the string is empty.
 */
function fn_strlen($string, $encoding = 'UTF-8')
{
    if (empty($encoding)) {
        $encoding = 'UTF-8';
    }

    if (function_exists('iconv_strlen')) {
        return @iconv_strlen($string, $encoding);
    } elseif (function_exists('mb_strlen')) {
        return mb_strlen($string, $encoding);
    } else {
        preg_match_all('/./su', $string, $ar);

        return count($ar[0]);
    }
}

/**
 * Converts URN to URI
 *
 * @param string $url URN (Uniform Resource Name or Query String)
 * @param string $area Area
 * @param string $protocol Output URL protocol (protocol://). If equals 'rel', no protocol will be included
 * @param string $lang_code 2 letters language code
 * @param bool $override_area
 * @return string URI
 */
function fn_url($url = '', $area = AREA, $protocol = 'current', $lang_code = CART_LANGUAGE, $override_area = false)
{
    static $init_vars = false;
    static $admin_index, $_admin_index, $vendor_index, $customer_index, $http_location, $https_location, $current_location;

    /**
     * Prepares parameters before building URL
     *
     * @param  string $url           URN (Uniform Resource Name or Query String)
     * @param  string $area          Area
     * @param  string $protocol      Output URL protocol (protocol://). If equals 'rel', no protocol will be included
     * @param  string $lang_code     2 letters language code
     * @param  bool   $override_area
     * @return bool   Always true
     */
    fn_set_hook('url_pre', $url, $area, $protocol, $lang_code, $override_area);

    if (!$init_vars) {
        $vendor_index = Registry::get('config.vendor_index');
        $_admin_index = Registry::get('config.admin_index');
        $customer_index = Registry::get('config.customer_index');
        $http_location = Registry::get('config.http_location');
        $https_location = Registry::get('config.https_location');
        $current_location = Registry::get('config.current_location');

        $init_vars = true;
    }
    $admin_index_area = $override_area ? 'A' : '';
    $admin_index = fn_get_index_script($admin_index_area);

    if ($area != 'A' && $area != 'C') {
        $prev_admin_index = $admin_index;
        $admin_index = fn_get_index_script($area);
        $area = 'A';
    }

    $url = str_replace('&amp;', '&', $url);
    $parsed_url = parse_url($url);
    $no_shorted = false;
    $full_query = false;

    if (!empty($parsed_url['scheme']) || !empty($parsed_url['host'])) {

        if (!empty($parsed_url['scheme'])) { // do not prefix URL is its absolute already
            $protocol = 'no_prefix';
        }

        $no_shorted = true;
    } else {
        if (!empty($parsed_url['path'])) {
            if (stripos($parsed_url['path'], $_admin_index) !== false) {
                $area = 'A';
                $no_shorted = true;
            } elseif (stripos($parsed_url['path'], $customer_index) !== false) {
                $area = 'C';
                $no_shorted = true;
            } elseif (!empty($vendor_index) && stripos($parsed_url['path'], $vendor_index) !== false) {
                $area = 'A';
                $no_shorted = true;
            }
        } else {
            if (strpos($url, '?') === 0) {
                $full_query = true;
            } else {
                $no_shorted = true;
                $url = $_url = ($area == 'C') ? $customer_index : $admin_index;
            }
        }
    }

    $index_script = ($area == 'C') ? $customer_index : $admin_index;

    $_url = '';
    if ($no_shorted) {
        // full url passed
        $_url = $url;
    } elseif ($full_query) {
        // full query passed
        $_url = $index_script . $url;
    } else {
        $_url = $index_script . '?dispatch=' . str_replace('?', '&', $url);
    }

    if ($protocol != 'rel' || defined('DISPLAY_FULL_PATHS')) {
        if ($protocol == 'http') {
            $_url = $http_location . '/' . $_url;
        } elseif ($protocol == 'https') {
            $_url = $https_location . '/' . $_url;
        } elseif ($protocol == 'current' || defined('DISPLAY_FULL_PATHS')) {
            $_url = $current_location . '/' . $_url;
        }

    }

    $company_id_in_url = fn_get_company_id_from_uri($url);

    /**
     * Prepares parameters before building URL
     *
     * @param string $_url              Output URL
     * @param string $area              Area
     * @param string $url               Input URL
     * @param string $lang_code         2 letters language code
     * @param string $protocol          Output URL protocol (protocol://). If equals 'rel', no protocol will be included
     * @param int    $company_id_in_url Equals company_id if it is present in $url, otherwise false
     */
    fn_set_hook('url_post', $_url, $area, $url, $protocol, $company_id_in_url, $lang_code);

    if (!empty($prev_admin_index)) {
        $admin_index = $prev_admin_index;
    }

    return $_url;
}

/**
 * Returns company_id if it is present in $uri, otherwise false
 *
 * @param string $uri URI | URN
 * @return int|bool company_id if it is present in $uri, otherwise false
 */
function fn_get_company_id_from_uri($uri)
{
    $company_id = false;

    if (preg_match("%(\?|&|&amp;)company_id=(\d+)%", $uri, $match)) {
        if (!empty($match[2])) {
            $company_id = $match[2];
        }
    }

    return $company_id;
}

/**
 * Checks can user get access to the some area or not.
 *
 * @param $user_data Array with user data
 * @param $account_type string First char of account type (Customer, Vendor, Admin)
 * @return bool True, if user can access area, defined in the const ACCOUNT_TYPE, false otherwise
 */
function fn_check_user_type_access_rules($user_data, $account_type = ACCOUNT_TYPE)
{
    $rules = array(
        'A' => array('admin', 'customer'),
        'V' => array('vendor', 'customer'),
        'C' => array('customer'),
    );

    /**
     * Hook for changing incoming parameters and access rules.
     *
     * @param $user_data Array with user data
     * @param $account_type string First char of account type (Customer, Vendor, Admin)
     * @param $rules Array with access rules, key is user_type, value is array(list) of available areas
     */
    fn_set_hook('check_user_type_access_rules_pre', $user_data, $account_type, $rules);

    $result = !empty($user_data['user_type']) && in_array($account_type, $rules[$user_data['user_type']]);

    /**
     * Hook for changing the result of checking.
     *
     * @param $user_data Array with user data
     * @param $account_type string First char of account type (Customer, Vendor, Admin)
     * @param $rules Array with access rules, key is user_type, value is array(list) of available areas
     * @param $result bool Result of the check
     */
    fn_set_hook('check_user_type_access_rules_pre_post', $user_data, $account_type, $rules, $result);

    return $result;
}

/**
 * Check for non empty string
 *
 * @param string $str string
 * @return boolean string is not empty?
 */
function fn_string_not_empty($str)
{
    return (strlen((trim($str)))>0) ? true : false;
}

/**
 * Check for number
 *
 * @param string $num number
 * @return boolean string is number?
 */
function fn_is_numeric($num)
{
    return is_numeric(trim($num));
}

/**
 * Converts given value to integer or float type.
 *
 * Examples (input -> output):
 * '10' -> (int) 10
 * '10,5' -> (float) 10.5
 * null -> (int) 0
 * '10asd' -> (int) 10
 *
 * @param mixed $value
 * @return int|float
 */
function fn_convert_to_numeric($value)
{
    return str_replace(',', '.', trim($value)) + 0;
}

/**
 * @Fancy recursive function to search for substring in an array
 * @param string $neele
 * @param mixed $haystack
 * @return bool
 * @author andyye
 */
function fn_substr_in_array($what_str, $where_arr)
{
    foreach ($where_arr as $v) {
        if (is_array($v)) {
            $sub_arr = fn_substr_in_array($what_str, $v);
            if ($sub_arr) {
                return true;
            }
        } else {
            if (strpos($v, $what_str) !== false) {
                return true;
            }
        }
    }

    return false;
}

function fn_return_bytes($val)
{
    $last = fn_strtolower($val{strlen($val)-1});

    switch ($last) {
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
        break;
    }

    return $val;
}

/**
 * Funtion formats user-entered price into float.
 *
 * @param string $price
 * @param string $currency
 * @return float Well-formatted price.
 */
function fn_parse_price($price, $currency = CART_PRIMARY_CURRENCY)
{
    $decimals = Registry::get('currencies.' . $currency . '.decimals');
    $dec_sep = Registry::get('currencies.' . $currency . '.decimals_separator');
    $thous_sep = Registry::get('currencies.' . $currency . '.thousands_separator');

    if ($dec_sep == $thous_sep) {
        if (($last = strrpos($price, $dec_sep)) !== false) {
            if ($thous_sep == '.') {
                $price = str_replace('.', ',', $price);
            }
            $price = substr_replace($price, '.', $last, 1);
        }
    } else {
        if ($thous_sep == '.') {
            // is it really thousands separator?
            // if there is decimals separator, than we can replace ths_sep
            if (strpos($price, $dec_sep) !== false) {
                $price = str_replace($thous_sep, '', $price);
            } else {
                //if there are 3 digits rigth of the separator - it is ths_sep too.
                if (($last = strrpos($price, '.')) !== false) {
                    $last_part = substr($price, $last);
                    $last_part = preg_replace('/[^\d]/', '', $last_part);
                    if (strlen($last_part) == 3 && $decimals != 3) {
                        $price = str_replace($thous_sep, '', $price);
                    }
                }
            }
        }

        if ($dec_sep != '.') {
            $price = str_replace($dec_sep, '.', $price);
        }
    }

    $price = preg_replace('/[^\d\.]/', '', $price);

    return round(floatval($price), $decimals);
}

/**
 * Function replaces default table prefix to user's prefix.
 *
 * @param string $query Query
 * @return string Updated query
 */
function fn_check_db_prefix($query, $table_prefix = '', $default_table_prefix = DEFAULT_TABLE_PREFIX)
{
    if (empty($table_prefix)) {
        $table_prefix = Registry::get('config.table_prefix');
    }

    if ($table_prefix != $default_table_prefix) {
        $pos = strpos($query, $default_table_prefix);
        if ($pos !== false) {
            $query = substr_replace($query, $table_prefix, $pos, strlen($default_table_prefix));
        }

        /**
         * If more than one tables in query
         */
        $query = preg_replace('/(\s|`){1}' . $default_table_prefix . '/', '${1}' . $table_prefix, $query);
    }

    return $query;
}

/**
 * Function returns the index script for requested data.
 *
 * @param mixed $for. If array is given, then index script will be returned for $for['user_type'].
 * If $for is empty, index script will be returned for defined ACCOUNT_TYPE
 * The following string are allowed: 'A', 'admin', 'V', 'vendor', 'C', 'customer'
 * @return string Path to index script
 */
function fn_get_index_script($for = '')
{
    if (is_array($for)) {
        $for = !empty($for['user_type']) ? $for['user_type'] : '';
    }

    if (empty($for) || !in_array($for, array('A', 'admin', 'V', 'vendor', 'C', 'customer'))) {
        $for = ACCOUNT_TYPE;
    } elseif ($for == 'A') {
        $for = 'admin';
    } elseif ($for == 'V') {
        $for = 'vendor';
    } elseif ($for == 'C') {
        $for = 'customer';
    }

    return Registry::get("config.{$for}_index");
}

/**
 * Updates statuses
 * @param string $status One letter status code that should be updated
 * @param array $status_data Status information
 * @param string $type One letter status type
 * @param string $lang_code Language code
 * @return array Updated status data
 */
function fn_update_status($status, $status_data, $type, $lang_code = DESCR_SL)
{
    if (empty($status_data['status'])) {
        // Generate new status code
        $existing_codes = db_get_fields('SELECT status FROM ?:statuses WHERE type = ?s GROUP BY status', $type);
        $existing_codes[] = 'N'; // STATUS_INCOMPLETED_ORDER
        $existing_codes[] = 'T'; // STATUS_PARENT_ORDER
        $codes = array_diff(range('A', 'Z'), $existing_codes);
        $status_data['status'] = reset($codes);
    }

    $can_continue = true;

    fn_set_hook('update_status_pre', $status, $status_data, $type, $lang_code, $can_continue);

    if ($can_continue) {
        if (empty($status)) {
            $status_data['type'] = $type;
            db_query("INSERT INTO ?:statuses ?e", $status_data);
            $status = $status_data['status'];

            foreach (fn_get_translation_languages() as $status_data['lang_code'] => $_v) {
                db_query('REPLACE INTO ?:status_descriptions ?e', $status_data);
            }
        } else {
            db_query("UPDATE ?:statuses SET ?u WHERE status = ?s AND type = ?s", $status_data, $status, $type);
            db_query('UPDATE ?:status_descriptions SET ?u WHERE status = ?s AND type = ?s AND lang_code = ?s', $status_data, $status, $type, $lang_code);
        }

        if (!empty($status_data['params'])) {
            foreach ((array) $status_data['params'] as $param_name => $param_value) {
                $_data = array(
                    'status' => $status,
                    'type' => $type,
                    'param' => $param_name,
                    'value' => $param_value
                );
                db_query("REPLACE INTO ?:status_data ?e", $_data);
            }
        }
    }

    fn_set_hook('update_status_post', $status, $status_data, $type, $lang_code);

    return $status_data['status'];
}

/**
 * Get simple statuses description (P - Processed, O - Open)
 * @param string $type One letter status type
 * @param boolean $additional_statuses Flag that determines whether additional (hidden) statuses should be retrieved
 * @param boolean $exclude_parent Flag that determines whether parent statuses should be excluded
 * @param string $lang_code Language code
 * @return array Statuses
 */
function fn_get_simple_statuses($type = STATUSES_ORDER, $additional_statuses = false, $exclude_parent = false, $lang_code = DESCR_SL)
{
    $statuses = db_get_hash_single_array(
        "SELECT a.status, b.description"
        . " FROM ?:statuses as a"
        . " LEFT JOIN ?:status_descriptions as b ON b.status = a.status AND b.type = a.type AND b.lang_code = ?s"
        . " WHERE a.type = ?s",
        array('status', 'description'),
        $lang_code, $type
    );
    if ($type == STATUSES_ORDER && !empty($additional_statuses)) {
        $statuses['N'] = __('incompleted', '', $lang_code);
        if (empty($exclude_parent)) {
            $statuses[STATUS_PARENT_ORDER] = __('parent_order', '', $lang_code);
        }
    }

    return $statuses;
}

/**
 * Gets full information about particular statuses
 * @param string $type One letter status type
 * @param array $status_to_select Array of statuses that should be retrieved. If empty, all statuses will be retrieved
 * @param boolean $additional_statuses Flag that determines whether additional (hidden) statuses should be retrieved
 * @param boolean $exclude_parent Flag that determines whether parent statuses should be excluded
 * @param string $lang_code Language code
 * @param int $company_id Company identifier
 * @return Statuses
 */
function fn_get_statuses($type = STATUSES_ORDER, $status_to_select = array(), $additional_statuses = false, $exclude_parent = false, $lang_code = DESCR_SL, $company_id = 0)
{
    fn_set_hook('get_statuses_pre', $type, $status_to_select, $additional_statuses, $exclude_parent, $lang_code, $company_id);

    $join = db_quote(" LEFT JOIN ?:status_descriptions ON ?:status_descriptions.status = ?:statuses.status AND ?:status_descriptions.type = ?:statuses.type AND ?:status_descriptions.lang_code = ?s", $lang_code);
    $condition = db_quote(" AND ?:statuses.type = ?s", $type);
    $condition .= !empty($status_to_select) ? db_quote(" AND ?:statuses.status IN (?a)", $status_to_select) : '';

    fn_set_hook('get_statuses', $join, $condition, $type, $status_to_select, $additional_statuses, $exclude_parent, $lang_code, $company_id);

    $statuses = db_get_hash_array(
        "SELECT ?:statuses.*, ?:status_descriptions.*"
        . " FROM ?:statuses"
        . $join
        . " WHERE 1 $condition",
        'status'
    );

    $statuses_params = db_get_hash_multi_array("SELECT param, value, status FROM ?:status_data WHERE type = ?s", array('status', 'param'), $type);
    foreach ($statuses as $status => $status_data) {
        $statuses[$status]['params'] = array();
        if (isset($statuses_params[$status])) {
            foreach ($statuses_params[$status] as $param_name => $param_data) {
                $statuses[$status]['params'][$param_name] = $param_data['value'];
            }
        }
    }

    if ($type == STATUSES_ORDER && $additional_statuses && empty($status_to_select)) {
        $statuses[STATUS_INCOMPLETED_ORDER] = array (
            'status' => STATUS_INCOMPLETED_ORDER,
            'description' => __('incompleted', '', $lang_code),
            'type' => STATUSES_ORDER,
            'params' => array(
                'inventory' => 'I',
            ),
        );
        if (empty($exclude_parent)) {
            $statuses[STATUS_PARENT_ORDER] = array (
                'status' => STATUS_PARENT_ORDER,
                'description' => __('parent_order', '', $lang_code),
                'type' => STATUSES_ORDER,
                'params' => array(
                    'inventory' => 'I',
                ),
            );
        }
    }

    fn_set_hook('get_statuses_post', $statuses, $join, $condition, $type, $status_to_select, $additional_statuses, $exclude_parent, $lang_code, $company_id);

    return $statuses;
}

/**
 * Gets full information about a particular status
 * @param string $status One letter status code
 * @param string $type One letter status type
 * @param int $object_id Recurring billing: ID of an object to be checked for subscriptions
 * @param string $lang_code Language code
 * @param int $company_id Company identifier
 * @return array Status data
 */
function fn_get_status_data($status, $type, $object_id = 0, $lang_code = DESCR_SL, $company_id = 0)
{
    fn_set_hook('get_status_data_pre', $status, $type, $object_id, $lang_code, $company_id);

    if (empty($status) || empty($type)) {
        return array();
    }

    $status_data = fn_get_statuses($type, !is_array($status) ? (array) $status : $status, false, false, $lang_code, $company_id);

    $status_data = reset($status_data);

    fn_set_hook('get_status_data_post', $status_data, $status, $type, $object_id, $lang_code, $company_id);

    return $status_data;
}

/**
 * Gets full information about a particular status by identifier
 * @param int $status_id Status identifier
 * @param string $lang_code Language code
 * @return array Status idata
 */
function fn_get_status_by_id($status_id, $lang_code = DESCR_SL)
{
    $status_data = array();

    $status = db_get_row("SELECT status, type FROM ?:statuses WHERE status_id = ?i", $status_id);
    if (!empty($status)) {
        $status_data = fn_get_status_data($status['status'], $status['type'], 0, $lang_code);
    }

    return $status_data;
}

/**
 * Deletes status
 * @param string $status One letter status code
 * @param string $type One letter status type
 * @return boolean True or false depending on whether the status is removed
 */
function fn_delete_status($status, $type)
{
    $can_delete = db_get_field("SELECT status FROM ?:statuses WHERE status = ?s AND type = ?s AND is_default = 'N'", $status, $type);

    fn_set_hook('delete_status_pre', $status, $type, $can_delete);

    if (!empty($can_delete)) {
        db_query('DELETE FROM ?:statuses WHERE status = ?s AND type = ?s', $status, $type);
        db_query('DELETE FROM ?:status_descriptions WHERE status = ?s AND type = ?s', $status, $type);
        db_query('DELETE FROM ?:status_data WHERE status = ?s AND type = ?s', $status, $type);
    }

    fn_set_hook('delete_status_post', $status, $type, $can_delete);

    return (!empty($can_delete)) ? true : false;
}

function fn_array_to_xml($data)
{
    if (!is_array($data)) {
        return fn_html_escape($data);
    }

    $return = '';
    foreach ($data as $key => $value) {
        $attr = '';
        if (is_array($value) && is_numeric(key($value))) {
            foreach ($value as $k => $v) {
                $arr = array(
                    $key => $v
                );
                $return .= fn_array_to_xml($arr);
                unset($value[$k]);
            }
            unset($data[$key]);
            continue;
        }
        if (strpos($key, '@') !== false) {
            $data = explode('@', $key);
            $key = $data[0];
            unset($data[0]);

            if (count($data) > 0) {
                foreach ($data as $prop) {
                    if (strpos($prop, '=') !== false) {
                        $prop = explode('=', $prop);
                        $attr .= ' ' . $prop[0] . '="' . $prop[1] . '"';
                    } else {
                        $attr .= ' ' . $prop . '=""';
                    }
                }
            }
        }
        $return .= '<' . $key . $attr . '>' . fn_array_to_xml($value) . '</' . $key . '>';
    }

    return $return;
}

/**
 * Function print notice that function $old_function is deprecated and must be replaced by $new_function
 * @param string $old_function Name of the old function
 * @param string $new_function Name of the new function
 */
function fn_generate_deprecated_function_notice($old_function, $new_function)
{
    $message = __('function_deprecated', array(
        '[old_function]' => $old_function,
        '[new_function]' => $new_function
    ));

    if (Debugger::isActive()) {
        fn_set_notification('E', __('error'), $message);
    }

    fn_log_event('general', 'deprecated', array(
        'function' => $old_function,
        'message' => $message,
        'backtrace' => debug_backtrace()
    ));
}

/**
 * Clears cache - all or by type
 * @param string $type cache type (misc, registry, static or all)
 * @param string $extra extra data to pass to cache clear function
 */
function fn_clear_cache($type = 'all', $extra = '')
{
    if ($type == 'misc' || $type == 'all') {
        fn_rm(Registry::get('config.dir.cache_misc'), false);
    }

    if ($type == 'statics' || $type == 'all') {
        Storage::instance('statics')->deleteDir($extra);
        fn_set_storage_data('cache_id', time());
    }

    if ($type == 'registry' || $type == 'all') {
        Registry::cleanup();
    }

    fn_set_hook('clear_cache_post', $type, $extra);
}

/**
 * Builds hierarchic tree from array width id and parent_id
 * @param array $array array of data, must be sorted ASC by  parent_id
 * @param string $object_key  name of id key in array
 * @param string $parent_key name of parent key in array
 * @param string $cildren_key name of key whee sub elements will be located in tree
 * @return array
 */
function fn_build_hierarchic_tree(&$array, $object_key, $parent_key = 'parent_id', $child_key = 'children', &$current_item = null, $parent_id = 0)
{
    $sorted = array();

    foreach ($array as $_id => $item) {
        if ($item[$parent_key] == $parent_id) {
            $sorted[$item[$object_key]] = $item;

            $sorted[$item[$object_key]][$child_key] = fn_build_hierarchic_tree($array, $object_key, $parent_key, $child_key, $sorted[$item[$object_key]], $item[$object_key]);
        }
    }

    return $sorted;
}

/**
 * Converts array to string with PHP code of this array
 * @param array $object
 * @param int $indent
 * @param string $type
 * @return string
 */
function fn_array2code_string($object, $indent = 0, $type = '')
{
    $scheme = '';

    if ($type == '') {
        if (is_array($object)) {
            $type = 'array';
        } elseif (is_numeric($object)) {
            $type = 'integer';
        }
    }

    if ($type == 'array') {
        $scheme .= "array(";
        if (is_array($object)) {
            if (!empty($object)) {
                $scheme .= " \n";
            }
            foreach ($object as $k => $v) {
                $scheme .= str_repeat("\t", $indent + 1) . "'$k' => " . fn_array2code_string($v, $indent + 1). ", \n";
            }
        }
        $scheme .= str_repeat("\t", $indent) . ")";
    } elseif ($type == 'int' || $type == 'integer') {
        if ($object == '') {
            $scheme .= 0;
        } else {
            $scheme .= $object;
        }
    } else {
        $scheme = "'$object'";
    }

    return $scheme;
}

/**
 * @deprecated
 */
function fn_update_lang_var($lang_data, $lang_code = DESCR_SL, $params = array())
{
    return LanguageValues::updateLangVar($lang_data, $lang_code, $params);
}

function fn_tools_update_status($params)
{
    if (!preg_match("/^[a-z_]+$/", $params['table'])) {
        return false;
    }

    $old_status = db_get_field("SELECT status FROM ?:$params[table] WHERE ?w", array($params['id_name'] => $params['id']));

    $permission = true;
    if (Registry::get('runtime.company_id')) {
        $cols = db_get_fields("SHOW COLUMNS FROM ?:$params[table]");
        if (in_array('company_id', $cols)) {
            if (fn_allowed_for('ULTIMATE')) {
                $disable_sharing = false;

                if (fn_allowed_for('ULTIMATE')) {
                    $sharing_scheme = fn_get_schema('sharing', 'schema');
                    $disable_sharing = !empty($sharing_scheme[$params['table']]['skip_checking_status']) ? true : false;

                    if ($disable_sharing) {
                        Registry::set('runtime.skip_sharing_selection', true);
                    }
                }
            }

            $condition = fn_get_company_condition('?:' . $params['table'] . '.company_id');
            $permission = db_get_field("SELECT company_id FROM ?:$params[table] WHERE ?w $condition", array($params['id_name'] => $params['id']));

            if (fn_allowed_for('ULTIMATE')) {
                if ($disable_sharing) {
                    Registry::set('runtime.skip_sharing_selection', false);
                }
            }
        }
    }
    if (empty($permission)) {
        fn_set_notification('W',  __('warning'), __('access_denied'));

        if (defined('AJAX_REQUEST')) {
            Registry::get('ajax')->assign('return_status', $old_status);
        }

        return false;
    }

    $result = db_query("UPDATE ?:$params[table] SET status = ?s WHERE ?w", $params['status'], array($params['id_name'] => $params['id']));

    fn_set_hook('tools_change_status', $params, $result);

    if ($result) {
        fn_set_notification('N', __('notice'), __('status_changed'));
    } else {
        fn_set_notification('E', __('error'), __('error_status_not_changed'));
        Registry::get('ajax')->assign('return_status', $old_status);
    }

    return true;
}

function fn_userdir_prefix($path, $fs = true, $current_location = true)
{
    $prefix = ($fs == true) ? Registry::get('config.dir.root') : ($current_location ? Registry::get('config.current_location') : Registry::get('config.http_location'));

    fn_set_hook('userdir_prefix', $prefix);

    return $prefix . '/' . $path;
}

/**
 * Make a string lowercase
 *
 * @param string $string - the string being lowercased
 * @param string $charset - charset being used
 * @return string
 */
function fn_strtolower($string, $charset = CHARSET)
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($string, $charset);
    } else {
        return strtolower($string);
    }
}

/**
 * @deprecated
 *
 * Removes traling slash in a path if it present
 *
 * @param string $path
 * @return string
 */
function fn_remove_trailing_slash($path)
{
    return preg_replace('/\/$/', '', $path);
}

/**
 * @deprecated
 *
 * Removes languages
 *
 * @param array $lang_codes List of 2-letters language codes
 * @param string $default_lang Default language code
 * @return bool Always true
 */
function fn_delete_languages($lang_ids, $default_lang = DEFAULT_LANGUAGE)
{
    return Languages::deleteLanguages($lang_ids, $default_lang);
}

/**
 * Checks and save languages integrity by enable
 * $default_lang language if all languages in cart disabled
 * and checks and changes appeareance settings if it are using hidden or disabled languages
 *
 * @param string $default_lang Two-letters language code, that will be set active, if there are no active languages.
 * @return bool Always true
 */

/**
 * @deprecated
 *
 * @param string $default_lang
 * @return bool
 */
function fn_save_languages_integrity($default_lang = CART_LANGUAGE)
{
    return Languages::saveLanguagesIntegrity($default_lang);
}

/**
 * Returns list of tables that has language depended data
 *
 * @return array Array of table names without prefix
 */
function fn_get_description_tables()
{
    $description_tables = db_get_fields("SHOW TABLES LIKE '?:%_descriptions'");
    $description_tables[] = 'language_values';
    $description_tables[] = 'product_features_values';
    $description_tables[] = 'bm_blocks_content';

    if (fn_allowed_for('ULTIMATE')) {
        $description_tables[] = 'ult_language_values';
    }

    foreach ($description_tables as $key => $table) {
        $description_tables[$key] = str_replace(Registry::get('config.table_prefix'), '', $table);
    }

    /**
     * Process list of tables that has language depended data before return
     *
     * @param array $description_tables Array of table names without prefix
     */
    fn_set_hook('description_tables_post', $description_tables);

    return $description_tables;
}

/**
 * @deprecated
 *
 * Clones language depended data from one language to other for all tables in cart
 *
 * @param string $to_lang 2 letters destination language code
 * @param string $from_lang 2 letters source language code
 * @return bool Always true
 */
function fn_clone_language($to_lang, $from_lang = CART_LANGUAGE)
{
    return LanguageHelper::cloneLanguage($to_lang, $from_lang);
}

/**
 * @deprecated
 *
 * Clones language depended data from one language to other for $table
 *
 * @param string $table table name to clone values
 * @param string $to_lang 2 letters destination language code
 * @param string $from_lang 2 letters source language code
 * @return bool Always true
 */
function fn_clone_language_values($table, $to_lang, $from_lang = CART_LANGUAGE)
{
    return LanguageHelper::cloneLanguageValues($table, $to_lang, $from_lang);
}

/**
 * @deprecated
 *
 * Cleans storefront URL removing scheme, trailing slash and etc.
 *
 * @param string $url URL for cleaning
 * @return string cleaned URL
 */
function fn_clean_url($url)
{
    return Url::clean($url);
}

/**
 * Gets installed themes
 *
 * @param int $company_id - company ID to get themes for
 *
 * @return array themes list
 */
function fn_get_installed_themes($company_id = NULL)
{
    return fn_get_dir_contents(fn_get_theme_path('[themes]', 'C', $company_id));
}

function fn_preg_replacement_quote($str)
{
    return preg_replace('/(\$|\\\\)(?=\d)/', '\\\\\1', $str);
}

/**
 * Checks if page is opened in a preview mode
 *
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @param array $params Request parameters
 * @return bool True if page is in a preview mode, false otherwise
 */
function fn_is_preview_action($auth, $params)
{
    $result = $auth['area'] == 'A' && !empty($params['action']) && $params['action'] == 'preview';

    return $result;
}

/**
 * Delete installed payment
 *
 * @param int $payment_id Payment id to be deleted
 * @return bool True if payment was successfully deleted, false otherwise
 */
function fn_delete_payment($payment_id)
{
    $result = true;
    $payment_id = (int) $payment_id;

    if (empty($payment_id) || !fn_check_company_id('payments', 'payment_id', $payment_id)) {
        return false;
    }

    fn_set_hook('delete_payment_pre', $payment_id, $result);

    $res = db_query("DELETE FROM ?:payments WHERE payment_id = ?i", $payment_id);
    db_query("DELETE FROM ?:payment_descriptions WHERE payment_id = ?i", $payment_id);

    fn_delete_image_pairs($payment_id, 'payment');

    $result = $result && $res;
    fn_set_hook('delete_payment_post', $payment_id, $result);

    /**
     * Delete the certificate file (if exists).
     */
    fn_rm(Registry::get('config.dir.certificates') . $payment_id);

    return $result;
}

/**
 * Gets count of directory subdirectories
 *
 * @param string $path directory path
 * @return int number of subdirectories
 */
function fn_dirs_count($path)
{
    $dirscount = 0;

    if (empty($path) || !is_dir($path) || !($dir = opendir($path))) {
        return $dirscount;
    }

    while (($file = readdir($dir)) !== false) {
        if ($file == '.' || $file == '..') {
            continue;
        }

        if (is_dir($path . '/' . $file)) {
            $dirscount++;
            $dirscount += fn_dirs_count($path . '/' . $file);
        }
    }

    closedir($dir);

    return $dirscount;
}

/**
 * Checks if theme is installed
 * @param string $theme_name theme (directory) name
 * @return boolean true if installed, false - if not
 */
function fn_is_theme_installed($theme_name)
{
    $destination_theme = fn_get_theme_path('[themes]/' . $theme_name, 'C');

    if (is_dir($destination_theme)) {

        if (file_exists($destination_theme . '/' . THEME_MANIFEST) || file_exists($destination_theme . '/' . THEME_MANIFEST_INI)) {
            return true;
        }

        fn_set_notification('E', __('error'), __('error_theme_manifest_missed'));
    }

    return false;
}

/**
 * Installs theme
 *
 * @param int $layout_id layout ID to create logo for
 * @param string $theme_name theme name
 * @param int $company_id company ID
 * @return boolean always true
 */
function fn_install_theme($theme_name, $company_id = null, $install_layouts = true)
{
    // Copy files
    fn_install_theme_files($theme_name, $theme_name, true);

    Settings::instance()->updateValue('theme_name', $theme_name, '', true, $company_id);

    $repo_dest = fn_get_theme_path('[themes]/' . $theme_name, 'C');
    $logo_ids = array();

    // Import theme layout
    $layouts = fn_get_dir_contents($repo_dest . '/layouts/', false, true, '.xml');

    // FIXME: Backward compability for layouts
    if (empty($layouts) && file_exists($repo_dest . '/layouts.xml')) {
        $layouts = array(
            '../layouts.xml',
        );
    }

    if (!empty($layouts) && $install_layouts) {
        foreach ($layouts as $layout_name) {
            $layout_path = fn_normalize_path($repo_dest . '/layouts/' . $layout_name);

            if (file_exists($layout_path)) {
                $layout_id = Exim::instance($company_id, 0, $theme_name)->importFromFile($layout_path, array(
                    'override_by_dispatch' => true,
                    'clean_up' => true,
                    'import_style' => 'create',
                ));

                if (empty($layout_id)) {
                    continue;
                }

                $_o_ids = fn_create_theme_logos_by_layout_id($theme_name, $layout_id, $company_id);
                $logo_ids = array_merge($logo_ids, $_o_ids);
            }
        }
    } else {
        $params = array(
            'theme_name' => $theme_name,
        );

        $exists = Layout::instance($company_id)->getList($params);

        if (empty($exists)) {
            $layout_id = Layout::instance($company_id)->update(array(
                'name' => __('main'),
                'theme_name' => $theme_name,
                'is_default' => 1
            ));
        }
    }

    return $logo_ids;
}

/**
 * Creates logotypes for companies and assign it to the layout
 *
 * @param int $layout_id Layout ID
 * @param int $company_id Company ID
 * @param bool $for_company Get logos only for companies
 * @return array created logo IDs
 */
function fn_create_theme_logos_by_layout_id($theme_name, $layout_id = 0, $company_id = 0, $for_company = false)
{
    $repo_dest = fn_get_theme_path('[themes]/' . $theme_name, 'C');

    $manifest = Themes::factory($theme_name)->getManifest();

    $logo_ids = array();
    $logo_types = fn_get_logo_types($for_company);

    Registry::set('runtime.allow_upload_external_paths', true);

    foreach ($logo_types as $type => $logo) {
        if (!$for_company) {
            $image_path = isset($manifest[$type]) ? $repo_dest . '/' . $manifest[$type] : '';
        } else {
            $image_path = '';
        }

        $logo_ids[$type] = fn_create_logo(array(
            'type' => $type,
            'layout_id' => !empty($logo['for_layout']) ? $layout_id : 0,
            'image_path' => $image_path
        ), $company_id);
    }

    Registry::set('runtime.allow_upload_external_paths', false);

    return $logo_ids;
}

/**
 * Installs theme files
 *
 * @param string $source_theme source theme name
 * @param string $dest_theme destination theme name
 * @param boolean $from_repo flag, if set to true, theme files are copied from themes_repository
 * @return boolean true if theme was installed, false otherwise
 */
function fn_install_theme_files($source_theme, $dest_theme, $from_repo = true)
{
    $path_dest = fn_get_theme_path('[themes]/' . $dest_theme, 'C');

    if (!fn_is_theme_installed($dest_theme)) {
        if (!fn_mkdir($path_dest)) {
            fn_set_notification('E', __('error'), __('text_cannot_create_directory', array(
                '[directory]' => fn_get_rel_dir($path_dest)
            )));

            return false;
        }

        $path_repo_base = '';
        if ($from_repo) {
            $path_repo = fn_get_theme_path('[repo]/' . $source_theme, 'C');

            $manifest = Themes::factory($source_theme)->getRepoManifest();
            $parent_theme = isset($manifest['parent_theme']) ? $manifest['parent_theme'] : Registry::get('config.base_theme');

            if (!empty($parent_theme) && $source_theme != $parent_theme) {
                $path_repo_base = fn_get_theme_path('[repo]/' . $parent_theme, 'C');
            }
        } else {
            $path_repo = fn_get_theme_path('[themes]/' . $source_theme, 'C');
        }

        fn_set_progress('parts', fn_dirs_count($path_repo_base) + fn_dirs_count($path_repo) + 1);

        // FIXME: Backward compatibility. Create manifest.json if theme only has manifest.ini
        if (file_exists($path_repo . '/' . THEME_MANIFEST_INI) && !file_exists($path_repo . '/' . THEME_MANIFEST)) {
            $content = parse_ini_file($path_repo . '/' . THEME_MANIFEST_INI);
            fn_put_contents($path_repo . '/' . THEME_MANIFEST, json_encode($content));
        }

        if (!empty($path_repo_base)) {
            fn_copy($path_repo_base, $path_dest, false);

            // Clear layouts and styles data
            fn_rm($path_dest . '/styles');
            fn_rm($path_dest . '/layouts');

            // FIXME: Backward compatibility
            fn_rm($path_dest . '/presets');
        }

        fn_copy($path_repo, $path_dest, false);
    }

    // Re-install add-ons template files
    list($installed_addons) = fn_get_addons(array('type' => 'installed'));
    foreach ($installed_addons as $addon) {
        fn_install_addon_templates($addon['addon']);
    }

    return true;
}

/**
 * Deletes theme
 * @param string $theme_name theme name to delete. If empty - deletes all themes
 * @return boolean true if deleted, false if not
 */
function fn_delete_theme($theme_name)
{
    $themes_dest = fn_get_theme_path('[themes]/' . $theme_name, 'C');
    $can_delete = true;

    if (fn_allowed_for('ULTIMATE')) {
        $company_themes = Settings::instance()->getAllVendorsValues('theme_name');
        if (in_array($theme_name, $company_themes)) {
            fn_set_notification('E', __('error'), __('error_delete_theme_company'));
            $can_delete = false;
        }
    } else {
        if ($theme_name == Registry::get('runtime.layout.theme_name')) {
            $can_delete = false;
            fn_set_notification('E', __('error'), __('cannot_remove_active_theme'));
        }
    }

    $layouts = db_get_array('SELECT layout_id, is_default FROM ?:bm_layouts WHERE theme_name = ?s', $theme_name);
    if ($can_delete && fn_rm($themes_dest)) {
        // Delete layout
        foreach ($layouts as $layout) {
            Layout::instance()->delete($layout['layout_id']);
        }

        return true;
    }

    return false;
}

/**
 * Gets all logos
 * @param int $company_id company ID
 * @param int $layout_id layout ID
 * @return array logos list
 */
function fn_get_logos($company_id = null, $layout_id = 0)
{
    $company_condition = '';
    if (is_null($company_id) && Registry::get('runtime.company_id')) {
        $company_id = Registry::get('runtime.company_id');
    }

    if (!is_null($company_id)) {
        $company_condition = db_quote(' AND company_id = ?i', $company_id);
    }


    $layout_id = !empty($layout_id) ? $layout_id : Registry::get('runtime.layout.layout_id'); // FIXME
    $logos = db_get_hash_array("SELECT * FROM ?:logos WHERE IF(layout_id = 0, 1, IF(layout_id = ?i, 1, 0)) ?p", 'type', $layout_id, $company_condition);

    $logo_ids = array();
    foreach ($logos as $l) {
        $logo_ids[] = $l['logo_id'];
    }

    $images = fn_get_image_pairs($logo_ids, 'logos', 'M', true, false);

    foreach ($logos as $k => $v) {
        if (empty($images[$v['logo_id']])) {
            $logos[$k]['image'] = array();
            continue;
        }

        $image = reset($images[$v['logo_id']]);
        $logos[$k]['image'] = $image['icon'];

    }

    return $logos;
}


/**
 * Adds logo
 * @param array $logo_data logo data (layout_id, image path, type)
 * @param integer $company_id company ID
 * @return integer ID of created logo
 */
function fn_create_logo($logo_data, $company_id = null)
{
    $condition = '';
    if (!empty($logo_data['layout_id'])) {
        $condition .= db_quote(" AND layout_id = ?i", $logo_data['layout_id']);
    }

    if (!empty($company_id)) {
        $condition .= db_quote(" AND company_id = ?i", $company_id);
    }

    $logo_id = db_get_field("SELECT logo_id FROM ?:logos WHERE type = ?s ?p", $logo_data['type'], $condition);

    if (empty($logo_id)) {
        $logo_id = db_query("INSERT INTO ?:logos ?e", array(
            'type' => $logo_data['type'],
            'layout_id' => !empty($logo_data['layout_id']) ? $logo_data['layout_id'] : 0,
            'company_id' => $company_id
        ));
    }

    if (!empty($logo_data['image_path'])) {
        if (file_exists($logo_data['image_path'])) {
            $_REQUEST['logotypes_image_data'] = array(
                array(
                    'type' => 'M',
                    'object_id' => $logo_id
                )
            );
            $_REQUEST['type_logotypes_image_icon'] = array('server');
            $_REQUEST['file_logotypes_image_icon'] = array($logo_data['image_path']);

            fn_attach_image_pairs('logotypes', 'logos');
        }
    }

    return $logo_id;
}

/**
 * Deletes logo by type
 * @param string $type logo type
 * @param integer $company_id - ID of company to delete logo for
 * @return bool always true
 */
function fn_delete_logo($type, $company_id = null)
{
    $condition = '';
    if (!empty($company_id)) {
        $condition .= db_quote(" AND company_id = ?i", $company_id);
    }

    $logo_ids = db_get_fields("SELECT logo_id FROM ?:logos WHERE type = ?s ?p", $type, $condition);

    foreach ($logo_ids as $logo_id) {
        fn_delete_image_pairs($logo_id, 'logos');
    }

    db_query("DELETE FROM ?:logos WHERE logo_id IN (?n)", $logo_ids);

    return true;
}

/**
 * Gets list of logo types
 *
 * @param boolean $for_company - indicates that logo types should be retrieved for company, not for root
 * @return array list of logo types
 */
function fn_get_logo_types($for_company = false)
{
    $types = array(
        'theme' => array (
            'for_layout' => true,
            'text' => 'text_customer_area_logo',
        ),
        'favicon' => array(
            'for_layout' => true,
            'text' => ''
        ),
        'mail' => array (
            'text' => 'text_mail_area_logo'
        )
    );

    fn_set_hook('logo_types', $types, $for_company);

    return $types;
}

/**
 * Gets area name by its type
 * @param string $area - area type
 * @return string area name
 */
function fn_get_area_name($area = AREA)
{
    return ($area == 'C') ? 'frontend' : 'backend';
}

/**
 * Add/remove html special chars
 *
 * @param mixed $data data to filter
 * @param bool $revert if true, decode special chars
 * @return mixed filtered variable
 */
function fn_html_escape($data, $revert = false)
{
    if (is_array($data)) {
        foreach ($data as $k => $sub) {
            if (is_string($k)) {
                $_k = ($revert == false) ? htmlspecialchars($k, ENT_QUOTES, 'UTF-8') : htmlspecialchars_decode($k, ENT_QUOTES);
                if ($k != $_k) {
                    unset($data[$k]);
                }
            } else {
                $_k = $k;
            }
            if (is_array($sub) === true) {
                $data[$_k] = fn_html_escape($sub, $revert);
            } elseif (is_string($sub)) {
                $data[$_k] = ($revert == false) ? htmlspecialchars($sub, ENT_QUOTES, 'UTF-8') : htmlspecialchars_decode($sub, ENT_QUOTES);
            }
        }
    } else {
        $data = ($revert == false) ? htmlspecialchars($data, ENT_QUOTES, 'UTF-8') : htmlspecialchars_decode($data, ENT_QUOTES);
    }

    return $data;
}

/**
 * Add slashes
 *
 * @param mixed $var variable to add slashes to
 * @param boolean $escape_nls if true, escape "new line" chars with extra slash
 * @return mixed filtered variable
 */
function fn_add_slashes(&$var, $escape_nls = false)
{
    if (!is_array($var)) {
        return ($var === null) ? null : (($escape_nls == true) ? str_replace("\n", "\\n", addslashes($var)) : addslashes($var));
    } else {
        $slashed = array();
        foreach ($var as $k => $v) {
            $sk = addslashes($k);
            if (!is_array($v)) {
                $sv = ($v === null) ? null : (($escape_nls == true) ? str_replace("\n", "\\n", addslashes($v)) : addslashes($v));
            } else {
                $sv = fn_add_slashes($v, $escape_nls);
            }
            $slashed[$sk] = $sv;
        }

        return ($slashed);
    }
}

/**
 * Gets and caches permissions schema
 * @param string $name schema name
 * @return array schema data
 */
function fn_get_permissions_schema($name)
{
    static $cache = array();

    if (empty($cache[$name])) {
        $cache[$name] = fn_get_schema('permissions', $name);
    }

    return $cache[$name];
}

/**
 * Gets available customization modes
 * @return array available customization modes
 */
function fn_get_customization_modes()
{
    $modes = array(
        'live_editor' => array(
            'title' => 'live_editor_mode'
        ),
        'design' => array(
            'title' => 'design_mode'
        ),
        'theme_editor' => array(
            'title' => 'theme_editor_mode'
        ),
    );

    $enabled_modes = explode(',', Registry::get('settings.customization_mode'));
    foreach ($enabled_modes as $e_mode) {
        if (!empty($modes[$e_mode])) {
            $modes[$e_mode]['enabled'] = true;
        }
    }

    fn_set_hook('get_customization_modes', $modes, $enabled_modes);

    return $modes;
}

/**
 * Updates customization mode (design/translation/css editor)
 * @param array $modes list of modes with statuses
 * @return bool true if mode updated, false - otherwise
 */
function fn_update_customization_mode($modes)
{
    if (!empty($modes)) {
        $available_modes = fn_get_customization_modes();
        $enabled_modes = fn_array_combine(explode(',', Registry::get('settings.customization_mode')), true);

        foreach ($modes as $c_mode => $c_status) {
            if (empty($available_modes[$c_mode])) { // skip enable/disable for non-existent modes
                continue;
            }

            if ($c_status == 'enable') {
                $enabled_modes[$c_mode] = true;
            } elseif ($c_status == 'disable') {
                unset($enabled_modes[$c_mode]);
            }
        }

        Settings::instance()->updateValue('customization_mode', implode(',', array_keys($enabled_modes)));

        return true;
    }

    return false;
}

/**
 * Temporary disables translation mode for current script run
 */
function fn_disable_live_editor_mode()
{
    Registry::set('runtime.customization_mode.live_editor', false);
}

/**
 * Convert multi-level array to single-level array
 *
 * @param array $item Multi-level array
 * @param string $delimiter Delimiter name
 * @return array Single-level array
 */
function fn_foreach_recursive($item, $delimiter, $name = '', $arr = array())
{
    if (is_array($item)) {
        foreach ($item as $key => $value) {
            $new_key = $name === '' ? $key : $name . $delimiter . $key;
            $arr = fn_foreach_recursive ($value, $delimiter, $new_key, $arr);
        }
    } else {
        $arr[$name] = $item;
    }

    return $arr;
}

/**
 * Parse phpinfo information
 *
 * @param int $type Php info option
 * @return string Php info tables
 */
function fn_get_phpinfo($type = -1)
{
    ob_start();
    phpinfo($type);
    $info = ob_get_clean();

    preg_match('/<body>(.*?)<\/body>/msS', $info, $matches);

    if (isset($matches[1])) {
        $content = preg_replace('/\s?class\="\w+"/', '', $matches[1]);
        $content = str_replace(' border="0" cellpadding="3" width="600"', ' class="deb-table"', $content);
        $content = explode("\n", $content);
        $counter = 0;
        foreach ($content as &$row) {
            if (0 === strpos($row, '<tr>')) {
                $replace = '<tr>';
                $row = str_replace('<tr>', $replace, $row);
                $counter++;
            } else {
                $counter = 0;
            }
        }

        return implode("\n", $content);
    }

    return;
}

/**
 * Translate language variable wrapper (for usage in templates and scripts)
 * @param string $var variable to translate
 * @param array $params placeholder replacements
 * @param string $lang_code language code to get variable for
 * @return string variable value
 */
function __($var, $params = array(), $lang_code = CART_LANGUAGE)
{
    $var = fn_get_lang_var($var, $lang_code);

    if (!empty($params) && is_array($params)) {

        reset($params);
        if (key($params) === 0) { // if first parameter has number key, we need to get plural form

            if (Registry::get('runtime.customization_mode.live_editor')) {
                if (preg_match('/\[(lang) name\=([\w-]+?)( cm\-pre\-ajax)?\](.*?)\[\/\1\]/is', $var, $matches)) {
                    $var = $matches[4];
                }
            }

            $parts = explode('|', $var);

            $number = array_shift($params);
            $params['[n]'] = $number; // special placeholder "[n]" will be replaced with passed number

            $rule = fn_get_plural_rule($number, $lang_code);
            $var = isset($parts[$rule]) ? $parts[$rule] : $parts[0];

            if (Registry::get('runtime.customization_mode.live_editor') && !empty($matches)) {
                $var = str_replace($matches[4], $var, $matches[0]);
            }
        }

        $var = strtr($var, $params);
    }

    return $var;
}

/**
 * Get product edition acronym
 *
 * @staticvar array $edition_acronyms Array with PRODUCT_EDITION => acronym
 * @param string $edition Edition name
 * @return string Edition acronym or false, if nothing was found.
 */
function fn_get_edition_acronym($edition)
{
    static $edition_acronyms = array(
        'PROFESSIONAL' => 'pro',
        'MULTIVENDOR'  => 'mve',
        'ULTIMATE'     => 'ult',
    );

    return !empty($edition_acronyms[$edition]) ? $edition_acronyms[$edition] : false;
}

/**
 * Parse the URN query part
 *
 * @param string $urn URN (Uniform Resource Name or Query String)
 * @return string parse query
 */
function fn_parse_urn($urn)
{
    $escaped = false;
    $path = '';
    if (($i = strpos($urn, '?')) !== false) { // full url with query string
        $qs = substr($urn, $i + 1);
        $path = str_replace('?' . $qs, '', $urn);
    } elseif (strpos($urn, '&') !== false || strpos($urn, '=') !== false) { // just query string
        $qs = $urn;
    } else { // no query string
        $qs = '';
        $path = $urn;
    }

    if (strpos($qs, '&amp;') !== false) {
        $escaped = true;
        $qs = str_replace('&amp;', '&', $qs);
    }

    parse_str($qs, $params);

    return array($path, $params, $escaped);
}

/**
 * Build the URN
 *
 * @param string $path
 * @param string $params
 * @param bool $escaped
 * @return string $urn URN (Uniform Resource Name or Query String)
 */
function fn_build_urn($path, $params, $escaped)
{
    $urn = $path;
    if (!empty($params)) {
        $res = http_build_query($params, '', ($escaped ? '&amp;' : '&'));
        $urn .= (!empty($path)) ? ('?' . $res) : $res;
    }

    return $urn;
}

/**
 * Remove parameter from the URL query part
 *
 * @param string ... query
 * @param string ... parameters to remove
 * @return string modified query
 */
function fn_query_remove()
{
    $args = func_get_args();
    $url = array_shift($args);

    if (!empty($args)) {
        list($path, $params, $escaped) = fn_parse_urn($url);

        foreach ($args as $param_name) {
            unset($params[$param_name]);
        }

        $url = fn_build_urn($path, $params, $escaped);
    }

    return $url;
}

/**
 * Replaces placeholders with request vars
 * @param string $href URL with placeholders
 * @param array $request Request parameters
 * @return string  processed URL
 */
function fn_substitute_vars($href, $request)
{
    if (strpos($href, '%') !== false) {
        list($dispatch, $params_list) = explode('?', $href);

        if (preg_match_all("/%(\w+)/", $params_list, $m)) {
            foreach ($m[1] as $value) {
                $_val = strtolower($value);
                if (!empty($request[$_val])) {
                    $params_list = str_replace('%' . $value, urlencode($request[$_val]), $params_list);
                }
            }
        }

        $href = $dispatch . '?' . $params_list;
    }

    return $href;
}

/**
 * Rounds a value down with a given step
 *
 * @param int $value
 * @param int $step
 * @return int Rounded value
 */
function fn_floor_to_step($value, $step)
{
    $floor = false;

    if (empty($step) && !empty($value)) {
        $floor = $value;

    } elseif (!empty($value) && !empty($step)) {
        if ($value % $step) {
            $floor = floor($value / $step) * $step;
        } else {
            $floor = $value;
        }
    }

    return $floor;
}

/**
 * Rounds a value up with a given step
 *
 * @param int $value
 * @param int $step
 * @return int Rounded value
 */
function fn_ceil_to_step($value, $step)
{
    $ceil = false;

    if (empty($step) && !empty($value)) {
        $ceil = $value;

    } elseif (!empty($value) && !empty($step)) {
        if ($value % $step) {
            $ceil = ceil($value / $step) * $step;
        } else {
            $ceil = $value;
        }
    }

    return $ceil;
}

/**
 * Gets plural rules for language (code got from Zend Framework)
 * @param type $number number to get plural for
 * @param type $lang_code language code
 * @return int plural form as number
 */
function fn_get_plural_rule($number, $lang_code)
{
    switch ($lang_code) {
        case 'bo':
        case 'dz':
        case 'id':
        case 'ja':
        case 'jv':
        case 'ka':
        case 'km':
        case 'kn':
        case 'ko':
        case 'ms':
        case 'th':
        case 'tr':
        case 'vi':
        case 'zh':
            return 0;
            break;

        case 'af':
        case 'az':
        case 'bn':
        case 'bg':
        case 'ca':
        case 'da':
        case 'de':
        case 'el':
        case 'en':
        case 'eo':
        case 'es':
        case 'et':
        case 'eu':
        case 'fa':
        case 'fi':
        case 'fo':
        case 'fur':
        case 'fy':
        case 'gl':
        case 'gu':
        case 'ha':
        case 'he':
        case 'hu':
        case 'is':
        case 'it':
        case 'ku':
        case 'lb':
        case 'ml':
        case 'mn':
        case 'mr':
        case 'nah':
        case 'nb':
        case 'ne':
        case 'nl':
        case 'nn':
        case 'no':
        case 'om':
        case 'or':
        case 'pa':
        case 'pap':
        case 'ps':
        case 'pt':
        case 'so':
        case 'sq':
        case 'sv':
        case 'sw':
        case 'ta':
        case 'te':
        case 'tk':
        case 'ur':
        case 'zu':
            return ($number == 1) ? 0 : 1;

        case 'am':
        case 'bh':
        case 'fil':
        case 'fr':
        case 'gun':
        case 'hi':
        case 'ln':
        case 'mg':
        case 'nso':
        case 'xbr':
        case 'ti':
        case 'wa':
            return (($number == 0) || ($number == 1)) ? 0 : 1;

        case 'be':
        case 'bs':
        case 'hr':
        case 'ru':
        case 'sr':
        case 'uk':
            return (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);

        case 'cs':
        case 'sk':
            return ($number == 1) ? 0 : ((($number >= 2) && ($number <= 4)) ? 1 : 2);

        case 'ga':
            return ($number == 1) ? 0 : (($number == 2) ? 1 : 2);

        case 'lt':
            return (($number % 10 == 1) && ($number % 100 != 11)) ? 0 : ((($number % 10 >= 2) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 1 : 2);

        case 'sl':
            return ($number % 100 == 1) ? 0 : (($number % 100 == 2) ? 1 : ((($number % 100 == 3) || ($number % 100 == 4)) ? 2 : 3));

        case 'mk':
            return ($number % 10 == 1) ? 0 : 1;

        case 'mt':
            return ($number == 1) ? 0 : ((($number == 0) || (($number % 100 > 1) && ($number % 100 < 11))) ? 1 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 2 : 3));

        case 'lv':
            return ($number == 0) ? 0 : ((($number % 10 == 1) && ($number % 100 != 11)) ? 1 : 2);

        case 'pl':
            return ($number == 1) ? 0 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 1 : 2);

        case 'cy':
            return ($number == 1) ? 0 : (($number == 2) ? 1 : ((($number == 8) || ($number == 11)) ? 2 : 3));

        case 'ro':
            return ($number == 1) ? 0 : ((($number == 0) || (($number % 100 > 0) && ($number % 100 < 20))) ? 1 : 2);

        case 'ar':
            return ($number == 0) ? 0 : (($number == 1) ? 1 : (($number == 2) ? 2 : ((($number >= 3) && ($number <= 10)) ? 3 : ((($number >= 11) && ($number <= 99)) ? 4 : 5))));

        default:
            return 0;
    }
}

/**
 * Gets list of customer css files
 *
 * @return string list of css files separated with comma
 */
function fn_get_frontend_css()
{
    if (fn_allowed_for('ULTIMATE') && !Registry::get('runtime.company_id')) {
        return '';
    }

    $theme_path = fn_get_theme_path('[relative]/[theme]', 'C') . '/css/';

    // FIXME: backward compatibility (for basic-based themes)
    if (file_exists(fn_get_theme_path('[themes]/[theme]/css/base.css', 'C'))) {
        $files = array(
            $theme_path . 'reset.css',
            $theme_path . 'scheme.less',
            $theme_path . 'base.css',
            $theme_path . 'styles.css'
        );
    } else {
        $files = array(
            $theme_path . 'styles.less',
            $theme_path . 'tygh/responsive.less',
        );
    }

    /**
     * Changes list of customer css files
     *
     * @param array files list of css files
     */
    fn_set_hook('get_frontend_css_post', $files);


    $url = '';
    if (!empty($files)) {
        $styles = array();
        foreach ($files as $file) {
            $styles[] = array(
                'file' => Registry::get('config.dir.root') . '/' . $file,
                'relative' => $file,
                'media' => ''
            );
        }

        $url = fn_merge_styles($styles, '', '', array(), 'C');
    }

    return $url;
}

/**
 * Merges css and less files
 *
 * @param array $files Array with style files
 * @param string $styles Style code
 * @param string $prepend_prefix Prepend prefix
 * @param array $params additional params
 */
function fn_merge_styles($files, $styles='', $prepend_prefix = '', $params = array(), $area = AREA)
{
    $prefix = (!empty($prepend_prefix) ? 'embedded' : 'standalone');
    $output = '';
    $less_output = '';
    $less_reflection = array();
    $compiled_less = '';
    $compiled_css = '';
    $less_import_dirs = array();
    $relative_path = fn_get_theme_path('[relative]/[theme]/css', $area);
    $hashes = array();

    $names = array_map(function($v) {
        return !empty($v['relative']) ? $v['relative'] : false;
    }, $files);

    // Check file changes
    if (Development::isEnabled('compile_check') || Debugger::isActive()) {
        $dir_root = Registry::get('config.dir.root');
        foreach ($names as $index => $name) {
            if (file_exists($dir_root . '/' . $name)) {
                $hashes[] = $name . filemtime($dir_root . '/' . $name);
            }
        }
    }

    $hashes[] = md5(implode('|', $names));
    $hashes[] = md5($styles);
    if ($area == 'C') {
        $hashes[] = Registry::get('runtime.layout.layout_id');
        $hashes[] = Registry::get('runtime.layout.style_id');
    }

    arsort($hashes);
    $hash = md5(implode(',', $hashes) . PRODUCT_VERSION) . fn_get_storage_data('cache_id');

    $gz_suffix = (Registry::get('config.tweaks.gzip_css_js') ? '.gz' : '');
    $filename = $prefix . '.' . $hash . '.css';

    $theme_manifest = Themes::factory(fn_get_theme_path('[theme]', 'C'))->getManifest();

    if (!Storage::instance('statics')->isExist($relative_path . '/' . $filename . $gz_suffix)) {
        foreach ($files as $src) {
            $m_prefix = '';
            $m_suffix = '';
            if (!empty($src['media'])) {
                $m_prefix = "\n@media " . $src['media'] . " {\n";
                $m_suffix = "\n}\n";
            }

            if (strpos($src['file'], '.css') !== false) {
                $output .= "\n" . $m_prefix . fn_get_contents($src['file']) . $m_suffix;
            } elseif ($area != 'C' || empty($theme_manifest['converted_to_css'])) {

                $less_output_chunk = '';
                if (file_exists($src['file'])) {
                    $less_output_chunk = "\n" . $m_prefix . fn_get_contents($src['file']) . $m_suffix;
                }

                if (!empty($params['reflect_less'])) {

                    if (preg_match('{/addons/([^/]+)/}is', $src['relative'], $m)) {
                        $less_reflection['output']['addons'][$m[1]] .= $less_output_chunk;
                    } else {
                        $less_reflection['output']['main'] .= $less_output_chunk;
                    }
                }

                $less_output .= $less_output_chunk;
                $less_import_dirs[] = dirname($src['file']);
            }
        }

        $header = str_replace('[files]', implode("\n", $names), Registry::get('config.js_css_cache_msg'));

        if (!empty($styles)) {
            $less_output .= $styles;
        }

        // Prepend all styles with prefix
        if (!empty($prepend_prefix)) {
            $less_output = $output . "\n" . $less_output;
            $output = '';
        }

        if (!empty($output)) {
            $compiled_css = Less::parseUrls($output, Storage::instance('statics')->getAbsolutePath($relative_path), fn_get_theme_path('[themes]/[theme]/media', $area));
        }

        if (!empty($theme_manifest['converted_to_css']) && $area == 'C') {

            $theme_css_path = fn_get_theme_path('[themes]/[theme]', $area) . '/css';

            $pcl_filepath = $theme_css_path . '/' . Themes::$compiled_less_filename;
            if (file_exists($pcl_filepath)) {
                $compiled_css .= fn_get_contents($pcl_filepath);
            }

            list($installed_addons) = fn_get_addons(array('type' => 'active'));
            foreach ($installed_addons as $addon) {
                $addon_pcl_filpath = $theme_css_path . "/addons/$addon[addon]/" . Themes::$compiled_less_filename;
                if (file_exists($pcl_filepath)) {
                    $compiled_css .= fn_get_contents($addon_pcl_filpath);
                }
            }
        }

        if (!empty($less_output)) {
            $less = new Less();
            $less->setImportDir($less_import_dirs);

            try {
                $compiled_less = $less->customCompile($less_output, Storage::instance('statics')->getAbsolutePath($relative_path), array(), $prepend_prefix, $area);
            } catch (Exception $e) {
                $skip_save = true;
                $shift = 4;
                $message = '<div style="border: 2px solid red; padding: 5px;">LESS ' . $e->getMessage();

                if (preg_match("/line: (\d+)/", $message, $m)) {
                    $lo = explode("\n", $less_output);
                    $message .= '<br /><br /><pre>' . implode("\n", array_splice($lo, intval($m[1]) - $shift, $shift * 2)) . '</pre>';
                }

                $message .= '</div>';
                echo($message);
            }
        }

        if (empty($skip_save)) {
            $compiled_content = $compiled_css . "\n" . $compiled_less;

            // Move all @import links to the Top of the file.
            if (preg_match_all('/@import url.*?;/', $compiled_content, $imports)) {
                $compiled_content = preg_replace('/@import url.*?;/', '', $compiled_content);

                foreach ($imports[0] as $import_link) {
                    $compiled_content = $import_link . "\n" . $compiled_content;
                }
            }

            Storage::instance('statics')->put($relative_path . '/' . $filename . $gz_suffix, array(
                'contents' => $header . $compiled_content,
                'compress' => Registry::get('config.tweaks.gzip_css_js'),
                'caching' => true
            ));

            if (!empty($params['use_scheme'])) {
                fn_put_contents(fn_get_cache_path(false) . 'theme_editor/' . $filename, $output . '#LESS#' . $less_output);
            }

            if (!empty($params['reflect_less'])) {
                $less_reflection['import_dirs'] = $less_import_dirs;
                fn_put_contents(fn_get_cache_path(false) . 'less_reflection.json', json_encode($less_reflection));
            }
        }
    }

    $url = Storage::instance('statics')->getUrl($relative_path . '/' . $filename);

    return $url;
}

/**
 * @deprecated
 *
 * Gets list of all languages defined in store
 * used for adding desciptions and etc.
 *
 * @param boolean $edit Flag that determines if language list is used to be edited
 * @return array $languages Languages list
 */
function fn_get_translation_languages($edit = false)
{
    return Languages::getAll($edit);
}

/**
 * Gets currencies for blocks with "Currency" type
 *
 * @return array List of available currencies
 */
function fn_block_manager_get_currencies()
{
    if (fn_allowed_for('ULTIMATE:FREE')) {
        $params = array(
            'only_primary' => 'Y',
        );
    } else {
        $params = array(
            'status' => array('A'),
        );
    }

    $currencies = fn_get_currencies_list($params, AREA, CART_LANGUAGE);

    return $currencies;
}

/**
 * Returns currencies list from registry
 *
 * @return array Currencies list
 */
function fn_get_currencies()
{
    return Registry::get('currencies');
}

/**
 * Returns currencies list filtered by params
 *
 * @param array $params Parameters for filtering
 *      array(
 *          'status': Currency status (A - active, D - disabled, H - hidden)
 *          'currency_id': int number
 *          'currency_code': String code (USD, GBP, RUR, etc)
 *          'only_primary' Select only primary currency (Y/N)
 *      )
 * @param string $area 1-letter Area code (C, A, V)
 * @param string $lang_code 2-letters language code
 * @return array Currencies list
 */
function fn_get_currencies_list($params = array(), $area = AREA, $lang_code = CART_LANGUAGE)
{
    $cond = $join = $order_by = '';

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if (($area == 'C') && defined('CART_LOCALIZATION')) {
            $join = " LEFT JOIN ?:localization_elements as c ON c.element = a.currency_code AND c.element_type = 'M'";
            $cond = db_quote('AND c.localization_id = ?i', CART_LOCALIZATION);
            $order_by = "ORDER BY c.position ASC";
        }

    }

    if (!$order_by) {
        $order_by = 'ORDER BY a.position';
    }

    if (!empty($params['status'])) {
        $cond .= db_quote(' AND status IN (?a)', $params['status']);
    }

    if (!empty($params['currency_id'])) {
        $cond .= db_quote(' AND currency_id = ?i', $params['currency_id']);
    }

    if (!empty($params['currency_code'])) {
        $cond .= db_quote(' AND currency_code = ?s', $params['currency_code']);
    }

    if (!empty($params['only_primary']) && $params['only_primary'] == 'Y') {
        $cond .= db_quote(' AND a.is_primary = ?s', 'Y');
    }


    $currencies = db_get_hash_array("SELECT a.*, b.description FROM ?:currencies as a LEFT JOIN ?:currency_descriptions as b ON a.currency_code = b.currency_code AND lang_code = ?s $join WHERE 1 ?p $order_by", 'currency_code', $lang_code, $cond);

    if (fn_allowed_for('ULTIMATE:FREE')) {
        foreach ($currencies as $code => $currency) {
            if ($currency['is_primary'] != 'Y') {
                $currencies[$code]['status'] = 'D';
            } else {
                $currencies[$code]['status'] = 'A';
            }
        }
    }

    return $currencies;
}

/**
 * Convert underscored string to CamelCase
 *
 * @param str  $string String
 * @param bool $upper  upper-camelcase/lower-camelcase
 * @return str
 */
function fn_camelize($string, $upper = true)
{
    $regexp = $upper ? '/(?:^|_)(.?)/' : '/_(.?)/';

    return preg_replace_callback($regexp, function($matches) {
        return strtoupper($matches[1]);
    }, $string);
}

/**
 * Convert CamelCase (lower or upper) string to underscored
 *
 * @param str  $string    String
 * @param bool $delimiter Delimiter
 * @return str
 */
function fn_uncamelize($string, $delimiter = '_')
{
    $string = preg_replace("/(?!^)[[:upper:]]+/", $delimiter . '$0', $string);

    return strtolower($string);
}

function fn_exim_json_encode($data)
{
    if (is_callable('mb_encode_numericentity') && is_callable('mb_decode_numericentity')) {
        $_data = fn_exim_prepare_data_to_convert($data);

        return mb_decode_numericentity(json_encode($_data), array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
    } else {
        return json_encode($data);
    }
}

function fn_exim_prepare_data_to_convert($data)
{
    $_data = array();
    if (is_array($data) && is_callable('mb_encode_numericentity')) {
        foreach ($data as $k => $v) {
            $key = mb_encode_numericentity($k, array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
            if (is_array($v)) {
                $_data[$key] = fn_exim_prepare_data_to_convert($v);
            } else {
                $_data[$key] = mb_encode_numericentity($v, array (0x80, 0xffff, 0, 0xffff), 'UTF-8');
            }
        }
    } else {
        $_data = $data;
    }

    return $_data;
}

/**
 * Checks if correct url was requested
 *
 * @param string $area Area
 * @return boolean Return true if currecnt url requested or requested url was correct, false otherwise
 */
function fn_check_requested_url($area = AREA)
{
    if (!defined('API') && $area == 'C' && !empty($_SERVER['REQUEST_URI']) && !empty($_SERVER['SCRIPT_NAME'])) {
        $request_path = rtrim(@parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

        if ($request_path != $_SERVER['SCRIPT_NAME']) {
            $index_script = Registry::get('config.customer_index');
            $current_path = Registry::get('config.current_path');

            return preg_match("!^$current_path(/$index_script)?$!", $request_path);
        }
    }

    return true;
}

function fn_format_long_string($str, $length)
{
    if (fn_strlen($str) <= $length) {
        return $str;
    }

    $length = $length - 3;

    return fn_substr($str, 0, $length) . '...';
}

/**
 * Gets uri for administrator's preview from common uri
 *
 * @param string $uri Common url
 * @param array $object_data Preview object data
 * @param array $user_id User identifier
 * @return string Preview uri
 */
function fn_get_preview_url($uri, $object_data, $user_id)
{
    if (fn_allowed_for('ULTIMATE')) {
        $company_id = Registry::get('runtime.company_id') ? Registry::get('runtime.company_id') : $object_data['company_id'];
        $uri = fn_link_attach($uri, 'company_id=' . $company_id);
    }

    if ($object_data['status'] != 'A' || fn_allowed_for('MULTIVENDOR') || !empty($object_data['usergroup_ids'])) {
        $_uri = fn_link_attach($uri, 'action=preview');
        $_uri = urlencode($_uri);

        $preview_url = fn_url("profiles.view_product_as_user?user_id=$user_id&area=C&redirect_url=$_uri", 'A');
    } else {
        $preview_url = fn_url($uri, 'C', 'http', DESCR_SL);
    }

    fn_set_hook('get_preview_url_post', $uri, $object_data, $user_id, $preview_url);

    return $preview_url;
}

/**
 * Gets list of default statuses
 *
 * @param string $status current object status
 * @param boolean $add_hidden includes 'hiden' status
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return array statuses list
 */
function fn_get_default_statuses($status, $add_hidden, $lang_code = CART_LANGUAGE)
{
    $statuses = array (
        'A' => __('active', '', $lang_code),
        'D' => __('disabled', '', $lang_code),
    );

    if ($add_hidden) {
        $statuses['H'] = __('hidden', '', $lang_code);
    }

    if ($status == 'N') {
        $statuses['P'] = __('pending', '', $lang_code);
    }

    return $statuses;
}

/**
 * Gets list of default status filters
 *
 * @param string $filter current filter
 * @param boolean $add_hidden includes 'hiden' status filter
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return array filters list
 */
function fn_get_default_status_filters($filter, $add_hidden, $lang_code = CART_LANGUAGE)
{
    $filters = array (
        'A' => __('check_active', '', $lang_code),
        'D' => __('check_disabled', '', $lang_code),
    );

    if ($add_hidden) {
        $filters['H'] = __('check_hidden', '', $lang_code);
    }

    if ($filter == 'N') {
        $filters['P'] = __('check_pending', '', $lang_code);
    }

    return $filters;
}

/**
 * Generates ekey for access to object
 * @param mixed $object_id object ID
 * @param string $type object type
 * @param int $ttl ekey TTL (from the current time)
 * @param string $ekey ekey ID if you generated it yourselves
 * @return string ekey ID
 */
function fn_generate_ekey($object_id, $type, $ttl = 0, $ekey = '')
{
    $key = !empty($ekey) ? $ekey : md5(uniqid(rand()));

    if (is_numeric($object_id)) {
        $field_name = 'object_id';
    } else {
        $field_name = 'object_string';
    }

    $_data = array (
        'object_type' => $type,
        'ekey' => $key,
        'ttl' => time() + $ttl
    );

    $_data[$field_name] = $object_id;

    db_query("REPLACE INTO ?:ekeys ?e", $_data);

    return $key;
}

/**
 * Gets object by ekey
 * @param string $ekey ekey
 * @param string $type object type
 * @return mixed object ID
 */
function fn_get_object_by_ekey($ekey, $type)
{
    $key_data = db_get_row("SELECT object_id, object_string FROM ?:ekeys WHERE ekey = ?s AND object_type = ?s AND ttl > ?i", $ekey, $type, time());
    $return = false;

    if (!empty($key_data)) {
        // Cleanup expired keys
        db_query("DELETE FROM ?:ekeys WHERE ttl > 0 AND ttl < ?i", time());

        // Delete current key
        db_query("DELETE FROM ?:ekeys WHERE ekey = ?s", $ekey);

        $return = !empty($key_data['object_string']) ? $key_data['object_string'] : $key_data['object_id'];
    }

    return $return;
}

/**
 * Updates object data in live content editing mode.
 *
 * @param string $params Params to be updated; array with keys name, value, lang_code, and need_render
 * @return bool
 */
function fn_live_editor_update_object($params)
{
    $updated = false;

    /**
     * Performs actions before updating a live editor object
     *
     * @param array $params  Params to be updated; array with keys name, value, lang_code, and need_render
     * @param bool  $updated The "already updated" flag
     */
    fn_set_hook('live_editor_update_object_pre', $params, $updated);

    if ($updated) {
        return true;
    }

    if (strpos($params['name'], ':')) {
        list($object, $field, $id) = explode(':', $params['name'], 3);

        $schema = fn_get_schema('customization', 'live_editor_objects');

        if (!empty($schema[$object])) {
            $rule = $schema[$object];

            if (!empty($rule['function'])) {

                $vars = $params + compact('object', 'field', 'id', 'rule');
                $args = fn_live_editor_prepare_callback_args($rule['args'], $vars);
                call_user_func_array($rule['function'], $args);

            }

            if (!empty($rule['table'])) {

                $table = $rule['table'];

                $condition = array(
                    'id' => db_quote("?p = ?s", $rule['id_field'], $id),
                );

                if (!empty($rule['lang_code'])) {
                    $condition['lang_code'] = db_quote("lang_code = ?s", $params['lang_code']);
                }

                /**
                 * Prepares data for the live editor object update request
                 *
                 * @param array $params    Params
                 * @param array $rule      Rule
                 * @param array $condition Conditions
                 * @param str   $table     Table name
                 */
                fn_set_hook('live_editor_update_object_table', $params, $rule, $condition, $table);

                db_query("UPDATE ?:$table SET ?u WHERE ?p",
                    array($rule['value_field'] => $params['value']),
                    implode(' AND ', $condition)
                );

                $updated = true;

            }

            if (!empty($params['need_render'])) {
                // Block
                if ($object == 'block' && $field == 'content') {
                    $block = Block::instance()->getById($id);
                    Registry::get('view')->assign('no_wrap', true);
                    $content = RenderManager::renderBlock($block);
                    if ($ajax = Registry::get('ajax')) {
                        $ajax->assign('rendered_name', $params['name']);
                        $ajax->assign('rendered_content', $content);
                    }
                }
            }
        }
    }

    /**
     * Performs actions after updating a live editor object
     *
     * @param array $params  Params
     * @param bool  $updated The "already updated" flag
     */
    fn_set_hook('live_editor_update_object_post', $params, $updated);

    return $updated;
}

/**
 * Prepares callback arguments recursively
 *
 * @param array $schema Schema definition
 * @param array $vars   Array of available vars
 * @return array Processed schema
 */
function fn_live_editor_prepare_callback_args($schema, $vars)
{
    if (!empty($schema)) {
        $processed_schema = array();
        foreach ($schema as $key => $value) {
            // Process key and value
            foreach (array('key', 'value') as $var) {
                if (!is_array($$var) && strpos($$var, '$') === 0) {
                    $$var = str_replace('$', '', $$var);
                    if (isset($vars[$$var])) {
                        $$var = $vars[$$var];
                    }
                }
            }
            if (is_array($value)) {
                $value = fn_live_editor_prepare_callback_args($value, $vars);
            }
            $processed_schema[$key] = $value;
        }

        return $processed_schema;
    }

    return $schema;
}
