<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Evaluates string which contains smarty syntax and falls back to custom error message instead fatal error
 *
 * Type:     function<br>
 * Name:     eval_string<br>
 * @return string
 */

function smarty_function_eval_string($params, &$smarty)
{
    try {
        $contents = $smarty->fetch('string:' . $params['var']);
    } catch (Exception $e) {
        $contents = $e->getMessage();
    }

    return $contents;
}
