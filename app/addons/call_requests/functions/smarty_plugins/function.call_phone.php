<?php

use Tygh\Registry;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_call_phone($params, &$smarty)
{
    $phone = fn_call_requests_get_phone();

    $length = Registry::get('addons.call_requests.phone_prefix_length');

    $phone_prefix = substr($phone, 0, $length);
    $phone_postfix = substr($phone, $length);

    return '<span><span class="ty-cr-phone-prefix">' . $phone_prefix . '</span>' . $phone_postfix . '</span>';
}
