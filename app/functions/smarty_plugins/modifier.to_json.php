<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier<br>
 * Name:     to_json<br>
 * Purpose:  converts php array to javascript object notation
 * Example:  {$a|to_json}
 * -------------------------------------------------------------
 */

function smarty_modifier_to_json($data)
{
    if (function_exists('json_encode')) {
        return json_encode($data);
    }

    if (is_null($data)) {
        $content = 'null';

    } elseif ($data === false) {
        $content = 'false';

    } elseif ($data === true) {
        $content = 'true';

    } elseif (is_array($data)) {
        $result = array();
        $akeys = array_keys($data);
        $diff = array_diff($akeys, range(0, sizeof($akeys) - 1));
        $is_list = empty($diff);

        if ($is_list) {
            foreach ($data as $v) {
                $result[] = smarty_modifier_to_json($v);
            }
            $content = '[' . join(',', $result) . ']';
        } else {
            foreach ($data as $k => $v) {
                $result[] = smarty_modifier_to_json($k) . ':' . smarty_modifier_to_json($v);
            }
            $content = '{' . join(',', $result) . '}';
        }
    } else {
        $content = empty($data) ? "''" : (is_string($data) ? "'" . fn_js_escape($data) . "'" : $data);
    }

    return $content;
}

/* vim: set expandtab: */
