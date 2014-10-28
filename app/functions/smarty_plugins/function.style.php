<?php

use Tygh\Registry;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_style($params, &$smarty)
{
    list($_area) = Registry::get('view')->getArea();
    $params['src'] = !empty($params['src']) ? $params['src'] : '';
    $location = Registry::get('config.current_location') . (strpos($params['src'], '/') === 0 ? '' : ('/' . fn_get_theme_path('[relative]/[theme]', $_area) . '/css'));
    $url = $location . '/' . $params['src'];

    if (!empty($params['content'])) {
        return '<style type="text/css"' . (!empty($params['media']) ? (' media="' . $params['media'] . '"') : '') .'>' . $params['content'] . '</style>';
    }

    return '<link type="text/css" rel="stylesheet"' . (!empty($params['media']) ? (' media="' . $params['media'] . '"') : '') .
           ' href="' . $url . '" />';

}
