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

namespace Tygh;

Use Tygh\Session;
Use Tygh\Registry;

class Debugger
{
    const DEFAULT_TOKEN = 'debug';
    const CACHE_MEDIUM_QUERY_TIME = 0.0005;
    const CACHE_LONG_QUERY_TIME = 0.001;

    const MEDIUM_QUERY_TIME = 0.2;
    const LONG_QUERY_TIME = 3;
    const EXPIRE_DEBUGGER = 360; // 1 hour

    protected static $active_debug_mode = false;
    protected static $allow_backtrace_sql = false;
    protected static $debugger_cookie = '';
    protected static $actives = '';

    public static $checkpoints = array();
    public static $queries = array();
    public static $cache_queries = array();
    public static $backtraces = array();
    public static $totals = array(
        'count_queries' => 0,
        'time_queries' => 0,
        'time_cache_queries' => 0,
        'time_page' => 0,
        'memory_page' => 0,
    );

    public static function init($reinit = false, $config = array())
    {
        self::$active_debug_mode = false;

        self::$allow_backtrace_sql = isset($_REQUEST['sql_backtrace']);
        self::$debugger_cookie = !empty($_COOKIE['debugger']) ? $_COOKIE['debugger'] : '';

        if ($reinit) {
            Registry::registerCache('debugger', SESSION_ALIVE_TIME, Registry::cacheLevel('time'), true);
            self::$actives = fn_get_storage_data('debugger_active');
            self::$actives = !empty(self::$actives) ? unserialize(self::$actives) : array();
            $active_in_registry = !empty(self::$actives[self::$debugger_cookie]) && (time() - self::$actives[self::$debugger_cookie]) < 0 ? true : false;
        }

        $debugger_token = !empty($config) ? $config['debugger_token'] : Registry::get('config.debugger_token');

        switch (true) {
            case (defined('AJAX_REQUEST') && substr($_REQUEST['dispatch'], 0, 8) !== 'debugger'):
                break;

            case (defined('DEBUG_MODE') && DEBUG_MODE == true):
            case (!$reinit && (!empty(self::$debugger_cookie) || isset($_REQUEST[$debugger_token]))):
                self::$active_debug_mode = true;
                break;

            case (!$reinit):
                break;

            // next if reinit

            case (!empty(self::$debugger_cookie) && !empty($active_in_registry)):
                self::$active_debug_mode = true;
                break;

            case (isset($_REQUEST[$debugger_token])):

                $salt = '';
                if ($_SESSION['auth']['user_type'] == 'A' && $_SESSION['auth']['is_root'] == 'Y') {
                    $user_admin = db_get_row('SELECT email, password FROM ?:users WHERE user_id = ?i', $_SESSION['auth']['user_id']);
                    $salt = $user_admin['email'] . $user_admin['password'];
                }

                if ($debugger_token != self::DEFAULT_TOKEN || !empty($salt)) { // for non-default token allow full access
                    self::$debugger_cookie = substr(md5(SESSION::getId() . $salt), 0, 8);

                    $active_in_registry = true;
                    self::$active_debug_mode = true;
                }

                if (AREA == 'C' && !empty($_REQUEST[$debugger_token])) {
                    if (!empty(self::$actives[$_REQUEST[$debugger_token]]) && (time() - self::$actives[$_REQUEST[$debugger_token]]) < 0) {
                        $active_in_registry = true;
                        self::$debugger_cookie = $_REQUEST[$debugger_token];
                        self::$active_debug_mode = true;
                    }
                }

                fn_set_cookie('debugger', self::$debugger_cookie, SESSION_ALIVE_TIME);

                break;
        }

        if ($reinit && self::$active_debug_mode && !empty(self::$debugger_cookie)) {
            self::$actives[self::$debugger_cookie] = time() + self::EXPIRE_DEBUGGER;
            fn_set_storage_data('debugger_active', serialize(self::$actives));
            $active_in_registry = true;
        }

        if ($reinit && !empty(self::$debugger_cookie) && empty($active_in_registry)) {
            fn_set_cookie('debugger', '', 0);
            unset(self::$actives[self::$debugger_cookie]);
            fn_set_storage_data('debugger_active', serialize(self::$actives));
        }

        return self::$active_debug_mode;
    }

    public static function isActive()
    {
        return self::$active_debug_mode;
    }

    public static function quit()
    {
        if (!(defined('DEBUG_MODE') && DEBUG_MODE == true)) {
            fn_set_cookie('debugger', '', 0);
            unset(self::$actives[self::$debugger_cookie]);
            fn_set_storage_data('debugger_active', serialize(self::$actives));
            Registry::del('debugger.data.' . self::$debugger_cookie);
        }
    }

    public static function getData($data_time)
    {
        $debugger_id = !empty(self::$debugger_cookie) ? self::$debugger_cookie : substr(Session::getId(), 0, 8);

        return !empty($data_time) ? Registry::get('debugger.data.' . $debugger_id . '.' . $data_time) : array();
    }

    public static function checkpoint($name)
    {
        if (!self::isActive()) {
            return false;
        }

        self::$checkpoints[$name] = array(
            'time' => self::microtime(),
            'memory' => memory_get_usage(),
            'included_files' => count(get_included_files()),
            'queries' => count(self::$queries),
        );

        return true;
    }

    public static function microtime()
    {
        list($usec, $sec) = explode(' ', microtime());

        return ((float) $usec + (float) $sec);
    }

    public static function displaySimple($show_sql = false)
    {
        if (!self::isActive()) {
            return false;
        }

        if ($show_sql) {
            $total_time = 0;
            echo '<ul style="list-style:none; border: 1px solid #cccccc; padding: 3px;">';
            foreach (self::$queries as $key => $query) {
                $total_time += $query['time'];
                $color = ($query['time'] > LONG_QUERY_TIME) ? '#FF0000' : (($query['time'] > 0.2) ? '#FFFFCC' : '');
                echo '<li ' . ($color ? "style=\"background-color: $color\">" : ($key % 2 ? 'style="background-color: #eeeeee;">' : '>')) . $query['time'] . ' - ' . $query['query'] . '</li>';
            }
            echo '</ul>';

            echo '<br />- Queries time: ' . sprintf("%.4f", array_sum($total_time)) . '<br />';
        }

        $first = true;
        $previous = array();
        $cummulative = array();
        foreach (self::$checkpoints as $name => $c) {
            echo '<br /><b>' . $name . '</b><br />';
            if ($first == false) {
                echo '- Memory: ' . (number_format($c['memory'] - $previous['memory'])) . ' (' . number_format($c['memory']) . ')' . '<br />';
                echo '- Files: ' . ($c['included_files'] - $previous['included_files']) . ' (' . $c['included_files'] . ')' . '<br />';
                echo '- Queries: ' . ($c['queries'] - $previous['queries']) . ' (' . $c['queries'] . ')' . '<br />';
                echo '- Time: ' . sprintf("%.4f", $c['time'] - $previous['time']) . ' (' . sprintf("%.4f", $c['time'] - $cummulative['time']) . ')' . '<br />';
            } else {
                echo '- Memory: ' . number_format($c['memory']) . '<br />';
                echo '- Files: ' . $c['included_files'] . '<br />';
                echo '- Queries: ' . $c['queries'] . '<br />';

                $first = false;
                $cummulative = $c;
            }
            $previous = $c;
        }
        echo '<br /><br />';

        exit();
    }

    public static function display()
    {
        if (!self::isActive()) {
            return false;
        }

        $data_time = time();
        $debugger_id = !empty(self::$debugger_cookie) ? self::$debugger_cookie : substr(Session::getId(), 0, 8);

        $ch_p = array_values(self::$checkpoints);

        $included_templates = array();
        $depth = array();
        $d = 0;
        foreach (Registry::get('view')->template_objects as $k => $v) {
            if (count(explode('#', $k)) == 1) {
                continue;
            }

            list(, $tpl) = explode('#', $k);

            if (!empty($v->parent)) {
                if (property_exists($v->parent, 'template_resource')) {

                    if (empty($depth[$v->parent->template_resource])) {
                        $depth[$v->parent->template_resource] = ++$d;
                    }

                    $included_templates[] = array(
                        'filename' => $tpl,
                        'depth' => $depth[$v->parent->template_resource]
                    );
                }
            }
        }

        $assigned_vars = Registry::get('view')->tpl_vars;
        ksort($assigned_vars);
        $exclude_vars = array('_REQUEST', 'config', 'settings', 'runtime', 'demo_password', 'demo_username', 'empty', 'ldelim', 'rdelim');
        foreach ($assigned_vars as $name => $value_obj) {
            if (in_array($name, $exclude_vars)) {
                unset($assigned_vars[$name]);
            } else {
                $assigned_vars[$name] = $value_obj->value;
            }
        }

        self::$totals['time_page'] = $ch_p[count($ch_p)-1]['time'] - $ch_p[0]['time'];
        self::$totals['memory_page'] = ($ch_p[count($ch_p)-1]['memory'] - $ch_p[0]['memory']) / 1024;
        self::$totals['count_queries'] = count(self::$queries);
        self::$totals['count_cache_queries'] = count(self::$cache_queries);
        self::$totals['count_tpls'] = count($included_templates);

        $runtime = fn_foreach_recursive(Registry::get('runtime'), '.');
        foreach ($runtime as $key => $value) {
            if (in_array(gettype($value), array('object', 'resource'))) {
                $runtime[$key] = gettype($value);
            }
        }

        $data = array(
            'request' => array(
                'request' => $_REQUEST,
                'server' => $_SERVER,
                'cookie' => $_COOKIE,
            ),
            'config' => array(
                'runtime' => $runtime,
            ),
            'sql' => array(
                'totals' => array(
                    'count' => self::$totals['count_queries'],
                    'rcount' => 0,
                    'time' => self::$totals['time_queries'],
                ),
                'queries' => self::$queries,
            ),
            'cache_queries' => array(
                'totals' => array(
                    'count' => self::$totals['count_cache_queries'],
                    'rcount' => 0,
                    'time' => self::$totals['time_cache_queries'],
                ),
                'queries' => self::$cache_queries,
            ),
            'backtraces' => self::$backtraces,
            'logging' => self::$checkpoints,
            'templates' => array(
                'tpls' => $included_templates,
                'vars' => $assigned_vars,
            ),
            'totals' => self::$totals,
        );

        $datas = Registry::get('debugger.data');
        $datas = is_array($datas) ? $datas : array();
        foreach (array_keys($datas) as $id) {
            foreach (array_keys($datas[$id]) as $time) {
                if ($time < time() - self::EXPIRE_DEBUGGER) {
                    unset($datas[$id][$time]);
                }
            }
            if (empty($datas[$id])) {
                unset($datas[$id]);
            }
        }
        $datas[$debugger_id][$data_time] = $data;
        Registry::set('debugger.data', $datas);

        Registry::get('view')->assign('debugger_id', $debugger_id);
        Registry::get('view')->assign('debugger_hash', $data_time);
        Registry::get('view')->assign('totals', self::$totals);

        Registry::get('view')->display('views/debugger/debugger.tpl');

        return true;
    }

    public static function set_query($query, $time)
    {
        if (!self::isActive()) {
            return false;
        }

        self::$queries[] = array(
            'query' => $query,
            'time' => $time,
        );

        if (self::$allow_backtrace_sql) {
            if (defined('DEBUG_BACKTRACE_IGNORE_ARGS')) {
                $debug_backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            } else {
                $debug_backtrace = debug_backtrace(false);
            }
            array_shift($debug_backtrace);
            foreach ($debug_backtrace as $key => $backtrace) {
                $backtrace['file'] = !empty($backtrace['file']) ? $backtrace['file'] : '';
                $backtrace['function'] = !empty($backtrace['function']) ? $backtrace['function'] : '';
                $backtrace['line'] = !empty($backtrace['line']) ? $backtrace['line'] : '';

                $debug_backtrace[$key] = $backtrace['file'] . '#' . $backtrace['function'] . '#' . $backtrace['line'];
            }
            self::$backtraces[] = $debug_backtrace;
        } else {
            self::$backtraces[] = array();
        }

        self::$totals['time_queries'] += $time;

        return true;
    }

    public static function set_cache_query($query, $time)
    {
        if (!self::isActive()) {
            return false;
        }

        self::$cache_queries[] = array(
            'query' => $query,
            'time' => $time,
        );

        self::$totals['time_cache_queries'] += $time;

        return true;
    }

    public static function parseTplsList($tpls_list, $i, $return_i = false)
    {
        $tpls_ar = array();
        foreach ($tpls_list as $key => $tpl) {
            if ($key < $i) {
                continue;
            }

            $ar = array();
            $ar['name'] = $tpl['filename'];
            if (!empty($tpls_list[$key+1]) && $tpls_list[$key+1]['depth'] > $tpl['depth']) {
                list($ar['childs'], $i) = self::parseTplsList($tpls_list, $key+1, true);
            }

            $tpls_ar[] = $ar;
            if (($i > $key && !empty($tpls_list[$i]) && $tpls_list[$i]['depth'] < $tpl['depth']) || !empty($tpls_list[$key+1]) && $tpls_list[$key+1]['depth'] < $tpl['depth']) {
                $key = $i > $key ? $i-1 : $key;
                break;
            }
        }

        if ($return_i) {
            $return = array($tpls_ar, $key+1);
        } else {
            $return = $tpls_ar;
        }

        return $return;
    }

}
