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
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('LOG_MAX_DATA_LENGTH', 10000);

function fn_log_event($type, $action, $data = array())
{
    $object_primary_keys = array(
        'users' => 'user_id',
        'orders' => 'order_id',
        'products' => 'product_id',
        'categories' => 'category_id',
    );

    $update = false;
    $content = array();

    $actions = Registry::get('settings.Logging.log_type_' . $type);

    $cut_log = Registry::ifGet('log_cut', false);
    Registry::del('log_cut');

    $cut_data = Registry::ifGet('log_cut_data', false);
    Registry::del('log_cut_data');

    if (empty($actions) || ($action && !empty($actions) && empty($actions[$action])) || !empty($cut_log)) {
        return false;
    }

    if (!empty($_SESSION['auth']['user_id'])) {
        $user_id = $_SESSION['auth']['user_id'];
    } else {
        $user_id = 0;
    }

    if ($type == 'users' && $action == 'logout' && !empty($data['user_id'])) {
        $user_id = $data['user_id'];
    }

    if ($user_id) {
        $udata = db_get_row("SELECT firstname, lastname, email FROM ?:users WHERE user_id = ?i", $user_id);
    }

    $event_type = 'N'; // notice

    if (!empty($data['backtrace'])) {
        $_btrace = array();
        $func = '';
        foreach (array_reverse($data['backtrace']) as $v) {
            if (!empty($v['file'])) {
                $v['file'] = fn_get_rel_dir($v['file']);
            }

            if (empty($v['file'])) {
                $func = $v['function'];
                continue;
            } elseif (!empty($func)) {
                $v['function'] = $func;
                $func = '';
            }

            $_btrace[] = array(
                'file' => !empty($v['file']) ? $v['file'] : '',
                'line' => !empty($v['line']) ? $v['line'] : '',
                'function' => $v['function'],
            );
        }

        $data['backtrace'] = serialize($_btrace);
    } else {
        $data['backtrace'] = '';
    }

    if ($type == 'general') {
        if ($action == 'deprecated') {
            $content['deprecated_function'] = $data['function'];
        }
        $content['message'] = $data['message'];
    } elseif ($type == 'orders') {

        $order_status_descr = fn_get_simple_statuses(STATUSES_ORDER, true, true);

        $content = array (
            'order' => '# ' . $data['order_id'],
            'id' => $data['order_id'],
        );

        if ($action == 'status') {
            $content['status'] = $order_status_descr[$data['status_from']] . ' -> ' . $order_status_descr[$data['status_to']];
        }

    } elseif ($type == 'products') {

        $product = db_get_field("SELECT product FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $data['product_id'], Registry::get('settings.Appearance.backend_default_language'));
        $content = array (
            'product' => $product . ' (#' . $data['product_id'] . ')',
            'id' => $data['product_id'],
        );

        if ($action == 'low_stock') { // log stock - warning
            $event_type = 'W';
        }

    } elseif ($type == 'categories') {

        $category = db_get_field("SELECT category FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $data['category_id'], Registry::get('settings.Appearance.backend_default_language'));
        $content = array (
            'category' => $category . ' (#' . $data['category_id'] . ')',
            'id' => $data['category_id'],
        );

    } elseif ($type == 'database') {
        if ($action == 'error') {
            $content = array (
                'error' => $data['error']['message'],
                'query' => $data['error']['query'],
            );
            $event_type = 'E';
        }

    } elseif ($type == 'requests') {
        if (!empty($cut_data)) {
            $data['data'] = preg_replace("/\<(" . implode('|', $cut_data) . ")\>(.*?)\<\/(" . implode('|', $cut_data) . ")\>/s", '<${1}>******</${1}>', $data['data']);
            $data['data'] = preg_replace("/%3C(" . implode('|', $cut_data) . ")%3E(.*?)%3C%2F(" . implode('|', $cut_data) . ")%3E/s", '%3C${1}%3E******%3C%2F${1}%3E', $data['data']);
            $data['data'] = preg_replace("/(" . implode('|', $cut_data) . ")=(.*?)(&)/s", '${1}=******${3}', $data['data']);
        }

        $content = array (
            'url' => $data['url'],
            'request' => (fn_strlen($data['data']) < LOG_MAX_DATA_LENGTH && preg_match('//u',$data['data'])) ? $data['data'] : '',
            'response' => (fn_strlen($data['response']) < LOG_MAX_DATA_LENGTH && preg_match('//u',$data['response'])) ? $data['response'] : '',
        );

    } elseif ($type == 'users') {

        if (!empty($data['time'])) {
            if (empty($_SESSION['log']['login_log_id'])) {
                return false;
            }

            $content = db_get_field('SELECT content FROM ?:logs WHERE log_id = ?i', $_SESSION['log']['login_log_id']);
            $content = unserialize($content);

            $minutes = ceil($data['time'] / 60);

            $hours = floor($minutes / 60);

            if ($hours) {
                $minutes -= $hours * 60;
            }

            if ($hours || $minutes) {
                $content['loggedin_time'] = ($hours ? $hours . ' |hours| ': '') . ($minutes ? $minutes . ' |minutes|' : '');
            }

            if (!empty($data['timeout']) && $data['timeout']) {
                $content['timeout'] = true;
            }

            $update = $_SESSION['log']['login_log_id'];

        } else {
            if (!empty($data['user_id'])) {
                $info = db_get_row("SELECT firstname, lastname, email FROM ?:users WHERE user_id = ?i", $data['user_id']);
                $content = array (
                    'user' => $info['firstname'] . ($info['firstname'] && $info['lastname'] ? ' ' : '' ) . $info['lastname'] . ($info['firstname'] || $info['lastname'] ? '; ' : '' ) . $info['email'] . ' (#' . $data['user_id'] . ')',
                );
                $content['id'] = $data['user_id'];

            } elseif (!empty($data['user'])) {
                $content = array (
                    'user' => $data['user'],
                );
            }

            if (in_array($action, array ('session', 'failed_login'))) {
                $ip = fn_get_ip();
                $content['ip_address'] = empty($data['ip']) ? $ip['host'] : $data['ip'];
            }
        }

        if ($action == 'failed_login') { // failed login - warning
            $event_type = 'W';
        }
    }

    fn_set_hook('save_log', $type, $action, $data, $user_id, $content, $event_type, $object_primary_keys);

    $content = serialize($content);
    if ($update) {
        db_query('UPDATE ?:logs SET content = ?s WHERE log_id = ?i', $content, $update);
    } else {

        if (Registry::get('runtime.company_id')) {
            $company_id = Registry::get('runtime.company_id');

        } elseif (!empty($object_primary_keys[$type]) && !empty($data[$object_primary_keys[$type]])) {
            $company_id = fn_get_company_id($type, $object_primary_keys[$type], $data[$object_primary_keys[$type]]);

        } else {
            $company_id = 0;
        }

        $row = array (
            'user_id' => $user_id,
            'timestamp' => TIME,
            'type' => $type,
            'action' => $action,
            'event_type' => $event_type,
            'content' => $content,
            'backtrace' => $data['backtrace'],
            'company_id' => $company_id,
        );

        $log_id = db_query("INSERT INTO ?:logs ?e", $row);

        if ($type == 'users' && $action == 'session') {
            $_SESSION['log']['login_log_id'] = $log_id;
        }
    }

    return true;
}

/**
 * Add a record to the log if the user session is expired
 *
 * @param array $entry - session record
 * @return bool Always true
 */
function fn_log_user_logout($entry, $data)
{
    if (!empty($data['auth']) && $data['auth']['user_id']) {
        $this_login = empty($data['auth']['this_login']) ? 0 : $data['auth']['this_login'];

        // Log user logout
        fn_log_event('users', 'session', array (
            'user_id' => $data['auth']['user_id'],
            'ip' => empty($data['auth']['ip']) ? '' : $data['auth']['ip'],
            'time' => ($entry['expiry'] - $this_login),
            'timeout' => true,
            'expiry' => $entry['expiry'],
        ));
    }

    return true;
}

/**
 * Returns store logs
 *
 * @param array $params Search parameters
 * @param int $items_per_page Logs limit
 * @return array Logs with search parameters
 */
function fn_get_logs($params, $items_per_page = 0)
{
    // Init filter
    $params = LastView::instance()->update('logs', $params);

    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $sortings = array (
        'timestamp' => array ('?:logs.timestamp', '?:logs.log_id'),
        'user' => array ('?:users.lastname', '?:users.firstname'),
    );

    $fields = array (
        '?:logs.*',
        '?:users.firstname',
        '?:users.lastname'
    );

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $join = "LEFT JOIN ?:users USING(user_id)";

    $condition = '';

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($time_from, $time_to) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:logs.timestamp >= ?i AND ?:logs.timestamp <= ?i)", $time_from, $time_to);
    }

    if (isset($params['q_user']) && fn_string_not_empty($params['q_user'])) {
        $condition .= db_quote(" AND (?:users.lastname LIKE ?l OR ?:users.firstname LIKE ?l)", "%".trim($params['q_user'])."%", "%".trim($params['q_user'])."%");
    }

    if (!empty($params['q_type'])) {
        $condition .= db_quote(" AND ?:logs.type = ?s", $params['q_type']);
    }

    if (!empty($params['q_action'])) {
        $condition .= db_quote(" AND ?:logs.action = ?s", $params['q_action']);
    }

    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND ?:logs.company_id = ?i", Registry::get('runtime.company_id'));
    }

    fn_set_hook('admin_get_logs', $params, $condition, $join, $sorting);

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:logs.log_id)) FROM ?:logs ?p WHERE 1 ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $data = db_get_array("SELECT " . join(', ', $fields) . " FROM ?:logs ?p WHERE 1 ?p $sorting $limit", $join, $condition);

    foreach ($data as $k => $v) {
        $data[$k]['backtrace'] = !empty($v['backtrace']) ? unserialize($v['backtrace']) : array();
        $data[$k]['content'] = !empty($v['content']) ? unserialize($v['content']) : array();
    }

    return array($data, $params);
}

/**
 * Gets all available types of logs
 *
 * @return array Log types
 */
function fn_get_log_types()
{
    $types = array();
    $section = Settings::instance()->getSectionByName('Logging');

    $settings = Settings::instance()->getList($section['section_id']);

    foreach ($settings['main'] as $setting_id => $setting_data) {
        $types[$setting_data['name']]['type'] = str_replace('log_type_', '', $setting_data['name']);
        $types[$setting_data['name']]['description'] = $setting_data['description'];
        $types[$setting_data['name']]['actions'] = $setting_data['variants'];
    }

    return $types;
}
