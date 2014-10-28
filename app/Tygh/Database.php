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

use Tygh\Debugger;
use Tygh\Exceptions\DatabaseException;
use Tygh\Registry;

class Database
{
    private static $_dbs = array(); // database connections list
    private static $_db; // current database connection
    private static $_table_prefix; // table prefix for current connection
    public static $raw = false; // if set to true, next query will be executed without additional processing by hooks

    /**
     * Connects to the database server
     * @param  string  $user     user name
     * @param  string  $passwd   password
     * @param  string  $host     host name
     * @param  string  $database database name
     * @param  array   $params   connection params
     * @return boolean true on success, false otherwise
     */
    public static function connect($user, $passwd, $host, $database, $params = array())
    {
        if (empty($params['dbc_name'])) {
            $params['dbc_name'] = 'main';
        }

        $params['table_prefix'] = ($params['dbc_name'] == 'main') ? Registry::get('config.table_prefix') : $params['table_prefix'];
        if (empty(self::$_dbs[$params['dbc_name']])) {
            $_db_class = Registry::ifGet('config.database_backend', 'mysqli');
            $_db_class = '\\Tygh\\Backend\\Database\\' . ucfirst($_db_class);

            self::$_dbs[$params['dbc_name']] = array(
                'db' => new $_db_class(),
                'table_prefix' => $params['table_prefix']
            );

            Debugger::checkpoint('Before database connect');
            $result = self::$_dbs[$params['dbc_name']]['db']->connect($user, $passwd, $host, $database);
            Debugger::checkpoint('After database connect');
            if (!$result) {
                self::$_dbs[$params['dbc_name']] = null;
            }
        } else {
            $result = true;
        }

        if ($result) {
            self::$_db = & self::$_dbs[$params['dbc_name']]['db'];
            self::$_table_prefix = self::$_dbs[$params['dbc_name']]['table_prefix'];

            if (empty($params['names'])) {
                $params['names'] = 'utf8';
            }
            if (empty($params['group_concat_max_len'])) {
                $params['group_concat_max_len'] = 3000; // 3Kb
            }

            self::$_db->initCommand(self::quote("SET NAMES ?s, sql_mode = ?s, SESSION group_concat_max_len = ?i", $params['names'], '', $params['group_concat_max_len']));
        }

        return $result;
    }

    /**
     * Changes database for current or passed connection
     * @param  string  $database database name
     * @param  string  $dbc_name database connection name
     * @return boolean true if database was changed, false - otherwise
     */
    public static function changeDb($database, $params = array())
    {
        if (empty($params['dbc_name'])) {
            $params['dbc_name'] = 'main';
        }

        if (!empty(self::$_dbs[$params['dbc_name']]) && self::$_dbs[$params['dbc_name']]['db']->changeDb($database)) {
            self::$_db = & self::$_dbs[$params['dbc_name']]['db'];
            self::$_table_prefix = !empty($params['table_prefix']) ? $params['table_prefix'] : self::$_dbs[$params['dbc_name']]['table_prefix'];

            return true;
        }

        return false;
    }

    /**
     * Execute query and format result as associative array with column names as keys
     *
     * @param string $query unparsed query
     * @param mixed ... unlimited number of variables for placeholders
     * @return array structured data
     */
    public static function getArray($query)
    {
        if ($_result = call_user_func_array(array('self', 'query'), func_get_args())) {

            while ($arr = self::$_db->fetchRow($_result)) {
                $result[] = $arr;
            }

            self::$_db->freeResult($_result);
        }

        return !empty($result) ? $result : array();
    }

    /**
     * Execute query and format result as associative array with column names as keys and index as defined field
     *
     * @param string $query unparsed query
     * @param string $field field for array index
     * @param mixed ... unlimited number of variables for placeholders
     * @return array structured data
     */
    public static function getHash($query, $field)
    {
        $args = array_slice(func_get_args(), 2);
        array_unshift($args, $query);

        if ($_result = call_user_func_array(array('self', 'query'), $args)) {
            while ($arr = self::$_db->fetchRow($_result)) {
                if (isset($arr[$field])) {
                    $result[$arr[$field]] = $arr;
                }
            }

            self::$_db->freeResult($_result);
        }

        return !empty($result) ? $result : array();
    }

    /**
     * Execute query and format result as associative array with column names as keys and then return first element of this array
     *
     * @param string $query unparsed query
     * @param mixed ... unlimited number of variables for placeholders
     * @return array structured data
     */
    public static function getRow($query)
    {
        if ($_result = call_user_func_array(array('self', 'query'), func_get_args())) {

            $result = self::$_db->fetchRow($_result);

            self::$_db->freeResult($_result);

        }

        return is_array($result) ? $result : array();
    }

    /**
     * Execute query and returns first field from the result
     *
     * @param string $query unparsed query
     * @param mixed ... unlimited number of variables for placeholders
     * @return array structured data
     */
    public static function getField($query)
    {
        if ($_result = call_user_func_array(array('self', 'query'), func_get_args())) {

            $result = self::$_db->fetchRow($_result, 'indexed');

            self::$_db->freeResult($_result);

        }

        return (isset($result) && is_array($result)) ? $result[0] : '';
    }

    /**
     * Execute query and format result as set of first column from all rows
     *
     * @param string $query unparsed query
     * @param mixed ... unlimited number of variables for placeholders
     * @return array structured data
     */
    public static function getColumn($query)
    {
        $result = array();

        if ($_result = call_user_func_array(array('self', 'query'), func_get_args())) {
            while ($arr = self::$_db->fetchRow($_result, 'indexed')) {
                $result[] = $arr[0];
            }

            self::$_db->freeResult($_result);
        }

        return $result;
    }

    /**
     * Execute query and format result as one of: field => array(field_2 => value), field => array(field_2 => row_data), field => array([n] => row_data)
     *
     * @param string $query  unparsed query
     * @param array  $params array with 3 elements (field, field_2, value)
     * @param mixed ... unlimited number of variables for placeholders
     * @return array structured data
     */
    public static function getMultiHash($query, $params)
    {
        @list($field, $field_2, $value) = $params;

        $args = array_slice(func_get_args(), 2);
        array_unshift($args, $query);

        if ($_result = call_user_func_array(array('self', 'query'), $args)) {
            while ($arr = self::$_db->fetchRow($_result)) {
                if (!empty($field_2)) {
                    $result[$arr[$field]][$arr[$field_2]] = !empty($value) ? $arr[$value] : $arr;
                } else {
                    $result[$arr[$field]][] = $arr;
                }
            }

            self::$_db->freeResult($_result);

        }

        return !empty($result) ? $result : array();
    }

    /**
     * Execute query and format result as key => value array
     *
     * @param string $query  unparsed query
     * @param array  $params array with 2 elements (key, value)
     * @param mixed ... unlimited number of variables for placeholders
     * @return array structured data
     */
    public static function getSingleHash($query, $params)
    {
        @list($key, $value) = $params;

        $args = array_slice(func_get_args(), 2);
        array_unshift($args, $query);

        if ($_result = call_user_func_array(array('self', 'query'), $args)) {
            while ($arr = self::$_db->fetchRow($_result)) {
                $result[$arr[$key]] = $arr[$value];
            }

            self::$_db->freeResult($_result);
        }

        return !empty($result) ? $result : array();
    }

    /**
     *
     * Prepare data and execute REPLACE INTO query to DB
     * If one of $data element is null function unsets it before querry
     *
     * @param  string    $table Name of table that condition generated. Must be in SQL notation without placeholder.
     * @param  array     $data  Array of key=>value data of fields need to insert/update
     * @return db_result
     */
    public static function replaceInto($table, $data)
    {
        if (!empty($data)) {
            return self::query('INSERT INTO ?:' . $table . ' ?e ON DUPLICATE KEY UPDATE ?u', $data, $data);
        }

        return false;
    }

    /**
     * Creates new database
     * @param  string  $database database name
     * @return boolean true on success, false - otherwise
     */
    public static function createDb($database)
    {
        if (self::query("CREATE DATABASE IF NOT EXISTS `" . self::$_db->escape($database) . "`")) {
            return true;
        }

        return false;
    }

    /**
     * Execute query
     *
     * @param string $query unparsed query
     * @param mixed ... unlimited number of variables for placeholders
     * @return mixed result set for "SELECT" statement / generated ID for an AUTO_INCREMENT field for insert statement / Affected rows count for DELETE/UPDATE statements
     */
    public static function query($query)
    {
        if (!self::$raw) {
            fn_set_hook('db_query', $query);
        }

        $args = func_get_args();
        $query = self::process($query, array_slice($args, 1), true);
        $result = false;

        if (!empty($query)) {
            if (!self::$raw) {
                fn_set_hook('db_query_process', $query);
            }

            if (defined('DEBUG_QUERIES')) {
                fn_print_r($query);
            }

            $time_start = microtime(true);

            $result = self::$_db->query($query);
            $a_row = self::$_db->affectedRows($result);

            Debugger::set_query($query, microtime(true) - $time_start);

            self::_error($result, $query);

            // Get last inserted ID
            $i_id = self::$_db->insertId();

            if (!self::$raw) {
                fn_set_hook('db_query_executed', $query, $result);
            }

            // "true" will be returned for Update/Delete/Insert/Replace statements. "SELECT" returns MySQLi/PDO object
            if ($result === true) {
                $cmd = substr($query, 0, 6);

                // Check if it was insert statement with auto_increment value and return it
                if (!empty($i_id)) {
                    $result = $i_id;

                } elseif ($cmd == 'UPDATE' || $cmd == 'DELETE' || $cmd == 'INSERT') {
                    $result = $a_row;
                }

                // Check if query updated data in the database and run cache handlers
                if (!empty($result) && preg_match("/^(UPDATE|INSERT INTO|REPLACE INTO|DELETE FROM) " . self::$_table_prefix . "(\w+) /", $query, $m)) {
                    Registry::setChangedTables($m[2]);
                }
            }
        }

        self::$raw = false;

        return $result;
    }

    /**
     * Parse query and replace placeholders with data
     *
     * @param string $query unparsed query
     * @param mixed ... unlimited number of variables for placeholders
     * @return string parsed query
     */
    public static function quote()
    {
        $args = func_get_args();
        $pattern = array_shift($args);

        return self::process($pattern, $args, false);
    }

    /**
     * Parse query and replace placeholders with data
     *
     * @param  string $query unparsed query
     * @param  array  $data  data for placeholders
     * @return string parsed query
     */
    public static function process($pattern, $data = array(), $replace = true)
    {
        // Replace table prefixes
        if ($replace) {
            $pattern = str_replace('?:', self::$_table_prefix, $pattern);
        }

        if (!empty($data) && preg_match_all("/\?(i|s|l|d|a|n|u|e|m|p|w|f)+/", $pattern, $m)) {
            $offset = 0;
            foreach ($m[0] as $k => $ph) {
                if ($ph == '?u' || $ph == '?e') {

                    $table_pattern = '\?\:';
                    if ($replace) {
                        $table_pattern = self::$_table_prefix;
                    }
                    if (preg_match("/^(UPDATE|INSERT INTO|REPLACE INTO|DELETE FROM) " . $table_pattern . "(\w+) /", $pattern, $m)) {
                        $data[$k] = self::checkTableFields($data[$k], $m[2]);

                        if (empty($data[$k])) {
                            return false;
                        }
                    }
                }

                if ($ph == '?i') { // integer
                    $pattern = self::_strReplace($ph, self::_intVal($data[$k]), $pattern, $offset); // Trick to convert int's and longint's

                } elseif ($ph == '?s') { // string

                    $pattern = self::_strReplace($ph, "'" . self::$_db->escape($data[$k]) . "'", $pattern, $offset);

                } elseif ($ph == '?l') { // string for LIKE operator
                    $pattern = self::_strReplace($ph, "'" . self::$_db->escape(str_replace("\\", "\\\\", $data[$k])) . "'", $pattern, $offset);

                } elseif ($ph == '?d') { // float
                    $pattern = self::_strReplace($ph, sprintf('%01.2f', $data[$k]), $pattern, $offset);

                } elseif ($ph == '?a') { // array FIXME: add trim
                    $data[$k] = !is_array($data[$k]) ? array($data[$k]) : $data[$k];
                    if (!empty($data[$k])) {
                        $pattern = self::_strReplace($ph, implode(', ', self::_filterData($data[$k], true)), $pattern, $offset);
                    } else {
                        if (Debugger::isActive() || defined('DEVELOPMENT')) {
                            trigger_error('Empty array was passed into SQL statement IN()', E_USER_DEPRECATED);
                        }
                        $pattern = self::_strReplace($ph, 'NULL', $pattern, $offset);
                    }

                } elseif ($ph == '?n') { // array of integer FIXME: add trim
                    $data[$k] = !is_array($data[$k]) ? array($data[$k]) : $data[$k];
                    $pattern = self::_strReplace($ph, !empty($data[$k]) ? implode(', ', array_map(array('self', '_intVal'), $data[$k])) : "''", $pattern, $offset);

                } elseif ($ph == '?u' || $ph == '?w') { // update/condition with and
                    $clue = ($ph == '?u') ? ', ' : ' AND ';
                    $q = implode($clue, self::_filterData($data[$k], false));
                    $pattern = self::_strReplace($ph, $q, $pattern, $offset);

                } elseif ($ph == '?e') { // insert
                    $filtered = self::_filterData($data[$k], true);
                    $pattern = self::_strReplace($ph, "(" . implode(', ', array_keys($filtered)) . ") VALUES (" . implode(', ', array_values($filtered)) . ")", $pattern, $offset);

                } elseif ($ph == '?m') { // insert multi
                    $values = array();
                    foreach ($data[$k] as $value) {
                        $filtered = self::_filterData($value, true);
                        $values[] = "(" . implode(', ', array_values($filtered)) . ")";
                    }
                    $pattern = self::_strReplace($ph, "(" . implode(', ', array_keys($filtered)) . ") VALUES " . implode(', ', $values), $pattern, $offset);

                } elseif ($ph == '?f') { // field/table/database name
                    $pattern = self::_strReplace($ph, self::_field($data[$k]), $pattern, $offset);

                } elseif ($ph == '?p') { // prepared statement
                    $pattern = self::_strReplace($ph, self::_tablePrefixReplace('?:', self::$_table_prefix, $data[$k]), $pattern, $offset);
                }
            }
        }

        return $pattern;
    }

    /**
     * Get column names from table
     *
     * @param  string $table_name table name
     * @param  array  $exclude    optional array with fields to exclude from result
     * @param  bool   $wrap_quote optional parameter, if true, the fields will be enclosed in quotation marks
     * @return array  columns array
     */
    public static function getTableFields($table_name, $exclude = array(), $wrap = false)
    {
        static $table_fields_cache = array();

        if (!isset($table_fields_cache[$table_name])) {
            $table_fields_cache[$table_name] = self::getColumn("SHOW COLUMNS FROM ?:$table_name");
        }

        $fields = $table_fields_cache[$table_name];
        if (!$fields) {
            return false;
        }

        if ($exclude) {
            $fields = array_diff($fields, $exclude);
        }

        if ($wrap) {
            foreach ($fields as &$v) {
                $v = "`$v`";
            }
        }

        return $fields;
    }

    /**
     * Check if passed data corresponds columns in table and remove unnecessary data
     *
     * @param  array $data       data for compare
     * @param  array $table_name table name
     * @return mixed array with filtered data or false if fails
     */
    public static function checkTableFields($data, $table_name)
    {
        $_fields = self::getTableFields($table_name);
        if (is_array($_fields)) {
            foreach ($data as $k => $v) {
                if (!in_array($k, $_fields)) {
                    unset($data[$k]);
                }
            }
            if (func_num_args() > 3) {
                for ($i = 3; $i < func_num_args(); $i++) {
                    unset($data[func_get_arg($i)]);
                }
            }

            return $data;
        }

        return false;
    }

    /**
     * Get enum/set possible values in field of database
     *
     * @param  string $table_name Table name
     * @param  string $field_name Field name
     * @return array  List of elements
     */
    public static function getListElements($table_name, $field_name)
    {
        $column_info = self::getRow('SHOW COLUMNS FROM ?:?p WHERE Field = ?s', $table_name, $field_name);

        if (
            !empty($column_info)
            && preg_match('/^(\w{3,4})\((.*)\)$/', $column_info['Type'], $matches)
            && in_array($matches[1], array('set', 'enum'))
            && !empty($matches[2])
        ) {
            $elements = array();
            foreach (explode(',', $matches[2]) as $element) {
                $elements[] = trim($element, "'");
            }

            return $elements;
        }

        return false;

    }

    /**
     * Placeholder replace helper
     *
     * @param  string $needle      string to replace
     * @param  string $replacement replacement
     * @param  string $subject     string to search for replace
     * @param  int    $offset      offset to search from
     * @return string with replaced fragment
     */
    private static function _strReplace($needle, $replacement, $subject, &$offset)
    {
        $pos = strpos($subject, $needle, $offset);
        $offset = $pos + strlen($replacement);

        // substr_replace does not work properly with mb_* and UTF8 encoded strings.
        //$return = substr_replace($subject, $replacement, $pos, 2);
        $return = substr($subject, 0, $pos) . $replacement . substr($subject, $pos + 2);

        return $return;
    }

    /**
     * Function finds $needle and replace it by $replacement only when $needle is not in quotes.
     * For example in sting "SELECT ?:products ..." ?: will be replaced,
     * but in "... WHERE name = '?:products'" ?: will not be replaced by table_prefix
     *
     * @param  string $needle      string to replace
     * @param  string $replacement replacement
     * @param  string $subject     string to search for replace
     * @return string
     */
    private static function _tablePrefixReplace($needle, $replacement, $subject)
    {
        // check that needle exists
        if (($pos = strpos($subject, $needle)) === false) {
            return $subject;
        }

        // if there are no ', replace all occurrences
        if (strpos($subject, "'") === false) {
            return str_replace($needle, $replacement, $subject);
        }

        $needle_len = strlen($needle);
        // find needle
        while (($pos = strpos($subject, $needle, $pos)) !== false) {
            // get the first part of string
            $tmp = substr($subject, 0, $pos);
            // remove slashed single quotes
            $tmp = str_replace("\'", '', $tmp);
            // if we have even count of ', it means that we are not in the quotes
            if (substr_count($tmp, "'") % 2 == 0) {
                // so we should make a replacement
                $subject = substr_replace($subject, $replacement, $pos, $needle_len);
            } else {
                // we are in the quotes, skip replacement and move forward
                $pos += $needle_len;
            }
        }

        return $subject;
    }

    /**
     * Convert variable to int/longint type
     *
     * @param  mixed $int variable to convert
     * @return mixed int/intval variable
     */
    private static function _intVal($int)
    {
        return $int + 0;
    }

    /**
     * Check if variable is valid database table name, table field or database name
     *
     * @param  string $field field to check
     * @return mixed  passed variable if valid, empty string otherwise
     */
    private static function _field($field)
    {
        if (preg_match("/([\w]+)/", $field, $m) && $m[0] == $field) {
            return $field;
        }

        return '';
    }

    /**
     * Display database error
     *
     * @param  resource $result result, returned by database server
     * @param  string   $query  SQL query, passed to server
     * @return mixed    false if no error, dies with error message otherwise
     */
    private static function _error($result, $query)
    {
        if ((!empty($result) || self::$_db->errorCode() == 0)) {
            // it's ok
        } else {
            $error = array (
                'message' => self::$_db->error() . ' <b>(' . self::$_db->errorCode() . ')</b>',
                'query' => $query,
            );

            if (Registry::get('runtime.database.skip_errors') == true) {
                Registry::push('runtime.database.errors', $error);
            } else {

                // Log database errors
                fn_log_event('database', 'error', array(
                    'error' => $error,
                    'backtrace' => debug_backtrace()
                ));

                throw new DatabaseException($error['message'] . "<p>{$error['query']}</p>");
            }
        }

        return false;
    }

    /**
     * Filters data to form correct SQL string
     * @param  array $data      key-value array of fields and values to filter
     * @param  bool  $key_value return result as key-value array if set true or as array of field-value pairs if set to false
     * @return array filtered data
     */
    private static function _filterData($data, $key_value)
    {
        $filtered = array();
        foreach ($data as $field => $value) {
            if (is_int($value) || is_float($value)) {
                //ok
            } elseif (is_numeric($value) && $value === strval($value + 0)) {
                $value += 0;
            } elseif (is_null($value)) {
                $value = 'NULL';
            } else {
                $value = "'" . self::$_db->escape($value) . "'";
            }

            if ($key_value == true) {
                $filtered['`' . self::_field($field) . '`'] = $value;
            } else {
                $filtered[] = '`' . self::_field($field) . '` = ' . $value;
            }

        }

        return $filtered;
    }
}
