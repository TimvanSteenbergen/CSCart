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
 * Name:     in_array<br>
 * Purpose:  check if array contains the value
 * Example:  {$a|in_array:$b}
 * -------------------------------------------------------------
 */

function smarty_modifier_in_array($needle, $haystack)
{
    if (!empty($haystack) && !is_array($haystack)) {
        $haystack = array($haystack);
    }

    if (is_array($haystack)) {
        return in_array($needle, $haystack);
    }

    return false;
}

/* vim: set expandtab: */
