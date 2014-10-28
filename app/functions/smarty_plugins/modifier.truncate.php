<?php

use Tygh\Registry;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty truncate modifier plugin
 *
 * Type:     modifier<br>
 * Name:     truncate<br>
 * Purpose:  Truncate a string to a certain length if necessary,
 *           optionally splitting in the middle of a word, and
 *           appending the $etc string or inserting $etc into the middle.
 * @link http://smarty.php.net/manual/en/language.modifier.truncate.php
 *          truncate (Smarty online manual)
 * @author   Monte Ohrt <monte at ohrt dot com>
 * @param string
 * @param integer
 * @param string
 * @param boolean
 * @param boolean
 * @return string
 */
function smarty_modifier_truncate($string, $length = 80, $etc = '...',
                                  $break_words = false, $middle = false)
{
    if ($length == 0)
        return '';

    if (Registry::get('runtime.customization_mode.live_editor') && preg_match('/(\[lang name\=[\w-]+?( [pre\-ajx]*)?\])(.*?)(\[\/lang\])/is', $string, $matches)) {
        list(, $pre, , $string, $post) = $matches;
    } else {
        $pre = $post = '';
    }

    if (fn_strlen($string) > $length) {
        $length -= min($length, fn_strlen($etc));
        if (!$break_words && !$middle) {
            $string = preg_replace('/\s+?(\S+)?$/u', '', fn_substr($string, 0, $length + 1));
        }
        if (!$middle) {
            return fn_substr($string, 0, $length) . $etc;
        } else {
            return fn_substr($string, 0, $length / 2) . $etc . fn_substr($string, -$length / 2);
        }
    } else {
        return $string;
    }
}

/* vim: set expandtab: */
