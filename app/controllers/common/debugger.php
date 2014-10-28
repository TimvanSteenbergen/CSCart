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
use Tygh\Debugger;
use Tygh\Database;

if ( !defined('BOOTSTRAP') || !Debugger::isActive() ) { die('Access denied'); }

$data = !empty($_REQUEST['debugger_hash']) ? Debugger::getData($_REQUEST['debugger_hash']) : array();

if ($mode == 'sql_parse') {

    fn_trusted_vars('query');

    if (!empty($data) && isset($_REQUEST['sql_id'])) {
        $query = stripslashes($data['sql']['queries'][$_REQUEST['sql_id']]['query']);
        $backtrace = !empty($data['backtraces']) ? $data['backtraces'][$_REQUEST['sql_id']] : array();
        $_REQUEST['sandbox'] = true;
    } else {
        $query = $_REQUEST['query'];
    }

    $result = $explain = array();
    $query_time = $start_time = 0;

    if (!empty($_REQUEST['sandbox'])) {
        db_query('SET AUTOCOMMIT=0');
        db_query('START TRANSACTION');
    }

    $stop_queries = array('DROP','CREATE', 'TRANSACTION', 'ROLLBACK');
    $stop_exec = false;
    foreach ($stop_queries as $stop_query) {
        if (stripos(trim($query), $stop_query) !== false) {
            $result = false;
            $stop_exec = true;
            break;
        }
    }

    if (!$stop_exec) {
        Database::$raw = true;

        $time_start = microtime(true);

        if (stripos(trim($query), 'SELECT') !== false) {
            $result = db_get_array($query);
            $result_columns = !empty($result[0]) ? array_keys($result[0]) : array();
        } else {
            $result = db_query($query);
        }

        $query_time = microtime(true) - $time_start;
    }

    if (strpos($query, 'SELECT') === 0) {
        $explain = db_get_array('EXPLAIN ' . $query);
    }

    if (!empty($_REQUEST['sandbox'])) {
        db_query('ROLLBACK');
    }

    if (!$stop_exec) {
        try {
            $parser = new PHPSQLParser($query);
            $creator = new PHPSQLCreator($parser->parsed);
            $query = $creator->created;
        } catch (Exception $e) {

        }
    }

    if ($stop_exec) {
        Registry::get('view')->assign('stop_exec', $stop_exec);
    }
    if (!empty($query_time)) {
        Registry::get('view')->assign('query_time', sprintf('%.5f', $query_time));
    }
    if (!empty($query)) {
        Registry::get('view')->assign('query', $query);
    }
    if (!empty($explain)) {
        Registry::get('view')->assign('explain', $explain);
    }
    if (isset($result)) {
        Registry::get('view')->assign('result', $result);
    }
    if (!empty($result_columns)) {
        Registry::get('view')->assign('result_columns', $result_columns);
    }
    if (!empty($backtrace)) {
        Registry::get('view')->assign('backtrace', $backtrace, false);
    }
    Registry::get('view')->display('views/debugger/components/sql_parse.tpl');
    exit();

} elseif ($mode == 'server') {

    Registry::get('view')->display('views/debugger/components/server_tab.tpl');
    exit();

} elseif ($mode == 'request') {

    if (!empty($data['request'])) {
        Registry::get('view')->assign('data', $data['request']);
        Registry::get('view')->assign('debugger_hash', $_REQUEST['debugger_hash']);
        Registry::get('view')->display('views/debugger/components/request_tab.tpl');
    }
    exit();

} elseif ($mode == 'config') {

    if (!empty($data['config'])) {
        Registry::get('view')->assign('data', $data['config']);
        Registry::get('view')->display('views/debugger/components/config_tab.tpl');
    }
    exit();

} elseif ($mode == 'sql') {

    if (!empty($data['sql'])) {
        $sql_data = array(
            'totals' => $data['sql']['totals'],
            'list' => $data['sql']['queries'],
            'count' => array(),
        );
        foreach ($sql_data['list'] as $sql_query) {
            if (empty($sql_data['count'][md5($sql_query['query'])])) {
                $sql_data['count'][md5($sql_query['query'])] = array(
                    'query' => $sql_query['query'],
                    'total_time' => 0,
                    'count_time' => 0,
                    'count' => 0,
                );
            }
            $sql_data['count'][md5($sql_query['query'])]['total_time'] += $sql_query['time'];
            $sql_data['count'][md5($sql_query['query'])]['count_time']++;
            $sql_data['count'][md5($sql_query['query'])]['count']++;
            if ($sql_data['count'][md5($sql_query['query'])]['count'] > $sql_data['totals']['rcount']) {
                $sql_data['totals']['rcount'] = $sql_data['count'][md5($sql_query['query'])]['count'];
            }
            if (!isset($sql_data['count'][md5($sql_query['query'])]['min_time']) || $sql_data['count'][md5($sql_query['query'])]['max_time'] < $sql_query['time']) {
                $sql_data['count'][md5($sql_query['query'])]['max_time'] = $sql_query['time'];
            }
            if (!isset($sql_data['count'][md5($sql_query['query'])]['min_time']) || $sql_data['count'][md5($sql_query['query'])]['min_time'] > $sql_query['time']) {
                $sql_data['count'][md5($sql_query['query'])]['min_time'] = $sql_query['time'];
            }
        }
        Registry::get('view')->assign('medium_query_time', Debugger::MEDIUM_QUERY_TIME);
        Registry::get('view')->assign('long_query_time', Debugger::LONG_QUERY_TIME);

        Registry::get('view')->assign('data', $sql_data);
        Registry::get('view')->assign('debugger_hash', $_REQUEST['debugger_hash']);
        Registry::get('view')->display('views/debugger/components/sql_tab.tpl');
    }
    exit();

} elseif ($mode == 'cache_queries') {

    if (!empty($data['cache_queries'])) {
        $query_data = array(
            'totals' => $data['cache_queries']['totals'],
            'list' => $data['cache_queries']['queries'],
            'count' => array(),
        );
        foreach ($query_data['list'] as $query) {
            if (empty($query_data['count'][md5($query['query'])])) {
                $query_data['count'][md5($query['query'])] = array(
                    'query' => $query['query'],
                    'total_time' => 0,
                    'count_time' => 0,
                    'count' => 0,
                );
            }
            $query_data['count'][md5($query['query'])]['total_time'] += $query['time'];
            $query_data['count'][md5($query['query'])]['count_time']++;
            $query_data['count'][md5($query['query'])]['count']++;
            if ($query_data['count'][md5($query['query'])]['count'] > $query_data['totals']['rcount']) {
                $query_data['totals']['rcount'] = $query_data['count'][md5($query['query'])]['count'];
            }
            if (!isset($query_data['count'][md5($query['query'])]['min_time']) || $query_data['count'][md5($query['query'])]['max_time'] < $query['time']) {
                $query_data['count'][md5($query['query'])]['max_time'] = $query['time'];
            }
            if (!isset($query_data['count'][md5($query['query'])]['min_time']) || $query_data['count'][md5($query['query'])]['min_time'] > $query['time']) {
                $query_data['count'][md5($query['query'])]['min_time'] = $query['time'];
            }
        }

        Registry::get('view')->assign('medium_query_time', Debugger::CACHE_MEDIUM_QUERY_TIME);
        Registry::get('view')->assign('long_query_time', Debugger::CACHE_LONG_QUERY_TIME);

        Registry::get('view')->assign('data', $query_data);
        Registry::get('view')->assign('debugger_hash', $_REQUEST['debugger_hash']);
        Registry::get('view')->display('views/debugger/components/cache_queries_tab.tpl');
    }
    exit();

} elseif ($mode == 'logging') {

    if (!empty($data['logging'])) {
        Registry::get('view')->assign('data', $data['logging']);
        Registry::get('view')->assign('debugger_hash', $_REQUEST['debugger_hash']);
        Registry::get('view')->display('views/debugger/components/logging_tab.tpl');
    }
    exit();

} elseif ($mode == 'templates') {

    if (!empty($data['templates'])) {
        $data['templates']['tpls'] = Debugger::parseTplsList($data['templates']['tpls'], 0);

        Registry::get('view')->assign('data', $data['templates']);
        Registry::get('view')->assign('debugger_hash', $_REQUEST['debugger_hash']);
        Registry::get('view')->display('views/debugger/components/templates_tab.tpl');
    }
    exit();

} elseif ($mode == 'quit') {
    Debugger::quit();

    return array(CONTROLLER_STATUS_REDIRECT, fn_query_remove($_REQUEST['redirect_url'], Registry::get('config.debugger_token')));
}
