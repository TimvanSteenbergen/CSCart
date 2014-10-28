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

class Values
{
    /**
     * Deletes language variables
     *
     * @param  array   $names List of language variables go be deleted
     * @return boolean Always true
     */
    public static function deleteVariables($names)
    {
        if (!is_array($names)) {
            $names = array($names);
        }

        $result = true;

        fn_set_hook('delete_language_variables', $names, $result);

        if (!empty($names)) {
            $result = db_query("DELETE FROM ?:language_values WHERE name IN (?a)", $names);
        }

        return $result;
    }

    public static function getVariables($params, $items_per_page = 0, $lang_code = DESCR_SL)
    {
        // Set default values to input params
        $default_params = array (
            'page' => 1,
            'items_per_page' => $items_per_page
        );

        $params = array_merge($default_params, $params);

        $fields = array(
            'lang.value' => true,
            'lang.name' => true,
        );

        if (!empty($params['fields'])) {
            $fields = array_merge($fields, $params['fields']);
        }

        $tables = array(
            '?:language_values as lang',
        );

        $left_join = array();
        $condition = array();

        $condition['param1'] = db_quote('lang.lang_code = ?s', $lang_code);
        if (isset($params['q']) && fn_string_not_empty($params['q'])) {
            $condition['param2'] = db_quote('(lang.name LIKE ?l OR lang.value LIKE ?l)', '%' . trim($params['q']) . '%', '%' . trim($params['q']) . '%');
        }

        fn_set_hook('get_language_variable', $fields, $tables, $left_join, $condition, $params);

        $joins = !empty($left_join) ? ' LEFT JOIN ' . implode(', ', $left_join) : '';

        $limit = '';
        if (!empty($params['items_per_page'])) {
            $params['total_items'] = db_get_field('SELECT COUNT(*) FROM ' . implode(', ', $tables) . $joins . ' WHERE ' . implode(' AND ', $condition));
            $limit = db_paginate($params['page'], $params['items_per_page']);
        }

        $lang_data = db_get_array('SELECT ' . implode(', ', array_keys($fields)) . ' FROM ' . implode(', ', $tables) . $joins . ' WHERE ' . implode(' AND ', $condition) . ' ORDER BY lang.name ' . $limit);

        return array($lang_data, $params);
    }

    /**
     * Gets language variable by name
     *
     * @param string $var_name  Language variable name
     * @param string $lang_code 2-letter language code
     *
     * @return string Language variable value; in case the value is absent, language variable name with "_" prefix is returned
     */
    public static function getLangVar($var_name, $lang_code = CART_LANGUAGE)
    {
        Registry::registerCache('lang_cache', array('language_values', 'ult_language_values'), Registry::cacheLevel('dispatch'), true);

        if (empty($var_name)) {
            return '';
        }

        $values = Registry::get('lang_cache.' . $lang_code);
        if (empty($values)) {
            $values = array();
        }

        $var_name = strtolower($var_name);
        if (!isset($values[$var_name])) {

            $params = array();
            $left_join = array();

            $fields = array(
                'lang.value' => true,
            );

            $tables = array(
                '?:language_values lang',
            );

            $condition = array(
                db_quote('lang.lang_code = ?s', $lang_code),
                db_quote('lang.name = ?s', $var_name),
            );

            fn_set_hook('get_lang_var', $fields, $tables, $left_join, $condition, $params);

            $joins = !empty($left_join) ? ' LEFT JOIN ' . implode(', ', $left_join) : '';

            $values[$var_name] = db_get_field('SELECT ' . implode(', ', array_keys($fields)) . ' FROM ' . implode(', ', $tables) . $joins . ' WHERE ' . implode(' AND ', $condition));

            if (!db_get_found_rows()) {
                unset($values[$var_name]);
            }

            Registry::set('lang_cache.' . $lang_code, $values);
        }

        if (!isset($values[$var_name])) {
            return '_' . $var_name;
        }

        if (Registry::get('runtime.customization_mode.live_editor')) {
            return '[lang name=' . $var_name . (preg_match('/\[[\w]+\]/', $values[$var_name]) ? ' cm-pre-ajax' : '') . ']' . $values[$var_name] . '[/lang]';
        }

        return $values[$var_name];
    }

    public static function getByName($name, $lang_code = CART_LANGUAGE)
    {
        if (!$name) {
            return array();
        }

        $query = 'SELECT * FROM ?:language_values WHERE name = ?s';

        if ($lang_code) {
            $query .= ' AND lang_code = ?s';
        }

        return db_get_array($query, $name, $lang_code);
    }

    /**
     * Gets language variables by prefix
     *
     * @param string $prefix Language variable prefix
     * @param $lang_code 2-letter language code
     *
     * @return Array of language variables
     */
    public static function getLangVarsByPrefix($prefix, $lang_code = CART_LANGUAGE)
    {
        $params = array();
        $left_join = array();
        $lang_vars = array();

        $fields = array(
            'lang.value' => true,
            'lang.name' => true,
        );

        $tables = array(
            '?:language_values lang',
        );

        $condition = array(
            db_quote('lang.lang_code = ?s', $lang_code),
            db_quote('lang.name LIKE ?l', $prefix . '%'),
        );

        fn_set_hook('get_lang_var', $fields, $tables, $left_join, $condition, $params);

        $joins = !empty($left_join) ? ' LEFT JOIN ' . implode(', ', $left_join) : '';

        $result = db_get_hash_array('SELECT ' . implode(', ', array_keys($fields)) . ' FROM ' . implode(', ', $tables) . $joins . ' WHERE ' . implode(' AND ', $condition), 'name');

        if (!empty($result)) {
            foreach ($result as $var_name => $value_info) {
                $lang_vars[$var_name] = $value_info['value'];

                if (Registry::get('runtime.customization_mode.live_editor') == 'Y') {
                    $lang_vars[$var_name] = '[lang name=' . $var_name . (preg_match('/\[[\w]+\]/', $lang_vars[$var_name]) ? ' cm-pre-ajax' : '') . ']' . $lang_vars[$var_name] . '[/lang]';
                }
            }
        }

        return $lang_vars;
    }

    /**
     * @param  array  $lang_data
     * @param  string $lang_code
     * @param  array  $params
     * @return array  List of updated lang_names
     */
    public static function updateLangVar($lang_data, $lang_code = DESCR_SL, $params = array())
    {
        $error_flag = false;
        $result = array();

        fn_set_hook('update_lang_values', $lang_data, $lang_code, $error_flag, $params, $result);

        foreach ($lang_data as $k => $v) {
            if (!empty($v['name'])) {
                preg_match("/(^[a-zA-z0-9][a-zA-Z0-9_\.]*)/", $v['name'], $matches);
                if (fn_strlen($matches[0]) == fn_strlen($v['name'])) {
                    $v['lang_code'] = $lang_code;
                    $res = db_query("REPLACE INTO ?:language_values ?e", $v);
                    if ($res) {
                        $result[] = $v['name'];
                    }
                } elseif (!$error_flag) {
                    fn_set_notification('E', __('warning'), __('warning_lanvar_incorrect_name'));
                    $error_flag = true;
                }
            }
        }

        return $result;
    }
}
