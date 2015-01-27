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

function smarty_modifier_unpuny($url)
{
    return Tygh\Tools\Url::decode($url);
}

/* vim: set expandtab: */
