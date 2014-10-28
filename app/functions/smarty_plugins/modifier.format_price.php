<?php

use Tygh\Registry;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier<br>
 * Name:     price<br>
 * Purpose:  getting formatted price with grouped thousands and
 *           decimal separators
 * Example:  {$price|price:"2":".":","}
 * -------------------------------------------------------------
 */

function smarty_modifier_format_price($price, $currency, $span_id = '', $class = '', $is_secondary = false, $live_editor_name = '')
{

    $value = fn_format_rate_value(
        $price,
        $number_type,
        $currency['decimals'],
        $currency['decimals_separator'],
        $currency['thousands_separator'],
        $currency['coefficient']
    );

    if (!empty($span_id) && $is_secondary) {
        $span_id = 'sec_' . $span_id;
    }

    $span_id = !empty($span_id) ? ' id="' . $span_id . '"' : '';
    $class = !empty($class) ? ' class="' . $class . '"' : '';

    $live_editor_attrs = '';
    if (Registry::get('runtime.customization_mode.live_editor') && !empty($live_editor_name)) {
        $live_editor_attrs = ' data-ca-live-editor-obj="' . $live_editor_name . '"';
    }

    if ($class) {
        $currency['symbol'] = '<span' . $class . '>' . $currency['symbol'] . '</span>';
    }

    $data = array (
        '<span' . $span_id . $class . $live_editor_attrs . '>',
        $value,
        '</span>',
    );

    if ($currency['after'] == 'Y') {
        array_push($data, '&nbsp;' . $currency['symbol']);
    } else {
        array_unshift($data, $currency['symbol']);
    }

    return implode('', $data);

}

/* vim: set expandtab: */
