<?php

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_call_request($params, &$smarty)
{
    $params = array_merge(array(
        'link_text' => __('call_requests.request_call'),
        'product' => false,
    ), $params);

    $template = $smarty->createTemplate('addons/call_requests/views/call_requests/components/popup.tpl', null, null, $smarty);

    foreach ($params as $key => &$value) {
        $template->assign($key, $value);
    }

    return $template->fetch();
}
