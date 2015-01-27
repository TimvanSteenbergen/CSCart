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

namespace Tygh\Languages;

use Tygh\Registry;

class Helper
{
    /**
     * Loads received language variables into language cache
     *
     * @param array  $var_names Language variable that to be loaded
     * @param string $lang_code 2-letter language code
     *
     * @return boolean True if any of received language variables were added into cache; false otherwise
     */
    public static function preloadLangVars($var_names, $lang_code = CART_LANGUAGE)
    {
        Registry::registerCache('lang_cache', array('language_values', 'ult_language_values'), Registry::cacheLevel('dispatch'), true);

        $values = Registry::get('lang_cache.' . $lang_code);
        if (empty($values)) {
            $values = array();
        }

        $var_names = array_diff($var_names, array_keys($values));

        if ($var_names) {

            foreach ($var_names as $index => $var_name) {
                $var_names[$index] = strtolower($var_name);
                if (isset($values[$var_name])) {
                    unset($var_names[$index]);
                }
            }

            if (empty($var_names)) {
                return true;
            }

            $fields = array(
                'lang.name' => true,
                'lang.value' => true,
            );

            $tables = array(
                '?:language_values lang',
            );

            $left_join = array();

            $condition = array(
                db_quote('lang.lang_code = ?s', $lang_code),
                db_quote('lang.name IN (?a)', $var_names),
            );

            $params = array();

            fn_set_hook('get_lang_var', $fields, $tables, $left_join, $condition, $params);

            $joins = !empty($left_join) ? ' LEFT JOIN ' . implode(', ', $left_join) : '';

            $new_values = db_get_hash_single_array('SELECT ' . implode(', ', array_keys($fields)) . ' FROM ' . implode(', ', $tables) . $joins . ' WHERE ' . implode(' AND ', $condition), array('name', 'value'));

            foreach ($var_names as $var_name) {
                if (!isset($new_values[$var_name])) {
                    $new_values[$var_name] = '_' . $var_name;
                }
            }

            $values = fn_array_merge($values, $new_values);

            Registry::set('lang_cache.' . $lang_code, $values);

            return true;
        }

        return false;
    }

    /**
     * @param $tpl_var
     * @param $value
     * @return bool true
     */
    public static function updateLangObjects($tpl_var, &$value)
    {
        static $live_editor_mode, $init = false, $schema;
        if (!$init) {
            $live_editor_mode = Registry::get('runtime.customization_mode.live_editor');
            $init = true;
        }

        if ($live_editor_mode) {
            if (empty($schema)) {
                $schema = fn_get_schema('translate', 'schema');
            }

            $controller = Registry::get('runtime.controller');
            $mode = Registry::get('runtime.mode');

            if (!empty($schema[$controller][$mode])) {
                foreach ($schema[$controller][$mode] as $var_name => $var) {
                    if ($tpl_var == $var_name && self::isAllowToTranslateLanguageObject($var)) {
                        self::prepareLangObjects($value, $var['dimension'], $var['fields'], $var['table_name'], $var['where_fields'], (isset($var['inner']) ? $var['inner'] : ''), (isset($var['unescape']) ? $var['unescape'] : ''));
                    }
                }
            }
            foreach ($schema['any']['any'] as $var_name => $var) {
                if ($tpl_var == $var_name && self::isAllowToTranslateLanguageObject($var)) {
                    self::prepareLangObjects($value, $var['dimension'], $var['fields'], $var['table_name'], $var['where_fields'], (isset($var['inner']) ? $var['inner'] : ''), (isset($var['unescape']) ? $var['unescape'] : ''));
                }
            }
        }

        return true;
    }

    /**
     * @param  array $language_object
     * @return bool
     */
    public static function isAllowToTranslateLanguageObject($language_object)
    {
        $allow = false;

        $root_only = isset ($language_object['root_only']) ?  $language_object['root_only'] : false;
        $vendor_only = isset ($language_object['vendor_only']) ?  $language_object['vendor_only'] : false;

        if (($root_only == $vendor_only) || (!Registry::get('runtime.company_id') && $root_only) || (Registry::get('runtime.company_id') && $vendor_only)) {
            $allow = true;
        }

        return $allow;
    }

    /**
     * @param $destination
     * @param $dimension
     * @param $fields
     * @param $table
     * @param $field_id
     * @param  string $inner
     * @param  string $unescape
     * @return bool   always true
     */
    public static function prepareLangObjects(&$destination, $dimension, $fields, $table, $field_id, $inner = '', $unescape = '')
    {
        if ($dimension > 0) {
            foreach ($destination as $i => $v) {
                self::prepareLangObjects($destination[$i], $dimension - 1, $fields, $table, $field_id, $inner, $unescape);
            }
        } else {
            foreach ($fields as $i => $v) {
                if (isset($destination[$v])) {
                    $where_fields = '';
                    foreach ($field_id as $to_name => $orig_name) {
                        if (is_array($orig_name)) {
                            foreach ($orig_name as $val) {
                                if (!empty($destination[$val])) {
                                    $where_fields .= '-' . $to_name . '-' . $destination[$val];
                                }
                            }
                        } else {
                            $where_fields .= '-' . $to_name . '-' . $destination[$orig_name];
                        }
                    }
                    $what = is_string($i) ? $i : $v;

                    if ($unescape) {
                        $destination[$v] = htmlspecialchars_decode($destination[$v]);
                    }

                    $pattern = '/\[(lang) name\=([\w-]+?)( [cm\-pre\-ajx]*)?\](.*?)\[\/\1\]/is';
                    if (!preg_match($pattern, $destination[$v])) {
                        $destination[$v] = "[lang name=$table-$what$where_fields]$destination[$v][/lang]";
                    }
                    if (!empty($inner) && isset($destination[$inner[0]])) {
                        self::prepareLangObjects($destination[$inner[0]], $inner[1], $fields, $table, $field_id, false, $unescape);
                    }
                }
            }
        }

        return true;
    }

    /**
     * Clones language depended data from one language to other for all tables in cart
     *
     * @param  string $to_lang   2 letters destination language code
     * @param  string $from_lang 2 letters source language code
     * @return bool   Always true
     */
    public static function cloneLanguage($to_lang, $from_lang = CART_LANGUAGE)
    {
        $description_tables = fn_get_description_tables();

        foreach ($description_tables as $table) {
            self::cloneLanguageValues($table, $to_lang, $from_lang);
        }
    }

    /**
     * Clones language depended data from one language to other for $table
     *
     * @param  string $table     table name to clone values
     * @param  string $to_lang   2 letters destination language code
     * @param  string $from_lang 2 letters source language code
     * @return bool   Always true
     */
    public static function cloneLanguageValues($table, $to_lang, $from_lang = CART_LANGUAGE)
    {
        $fields_select = fn_get_table_fields($table, array(), true);
        $fields_insert = fn_get_table_fields($table, array(), true);
        $k = array_search('`lang_code`', $fields_select);
        $fields_select[$k] = db_quote("?s as lang_code", $to_lang);

        db_query(
            "INSERT IGNORE INTO ?:$table (" . implode(', ', $fields_insert) . ") "
            . "SELECT " . implode(', ', $fields_select) . " FROM ?:$table WHERE lang_code = ?s",
            $from_lang
        );

        return true;
    }
}
