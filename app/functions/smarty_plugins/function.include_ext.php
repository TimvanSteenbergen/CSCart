<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Include template with ability to pass parameters as array
 *
 * Type:     function<br>
 * Name:     include_ext<br>
 * Purpose:  include template with ability to pass parameters as array
 * @param array $param params list
 * @return object $template template object
 */
function smarty_function_include_ext($params, &$template)
{
    $tpl = $template->createTemplate($params['file'], $template);
    unset($params['file']);

    $tpl->assign($params['params_array']);
    unset($params['params_array']);

    if (!empty($params)) {
        $tpl->assign($params);
    }

    return $tpl->fetch();
}

/* vim: set expandtab: */
