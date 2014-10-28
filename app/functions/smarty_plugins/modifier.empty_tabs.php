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
 * Name:     empty_tabs<br>
 * Purpose:  find ids of empty tabs
 * Example:  {$a|empty_tabs}
 * -------------------------------------------------------------
 */

function smarty_modifier_empty_tabs($content)
{
    if (!empty($content)) {
        preg_match_all('/\<div id="([\w]*)"( class="[\w- ]*")*>[\n\r\t ]*(\<\!--([\w]*)--\>)?[\n\r\t ]*\<\/div>/is', $content, $matches);

        if (!empty($matches[1])) {
            return array_map('smarty_change_tab_id', $matches[1]);
        }
    }

    return array();
}

function smarty_change_tab_id($str)
{
    return substr($str, strpos($str, '_') + 1);
}

/* vim: set expandtab: */
