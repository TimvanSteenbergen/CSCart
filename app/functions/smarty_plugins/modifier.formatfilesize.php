<?php

/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     modifier
 * Name:     formatfilesize
 * Purpose:  format size of file with configurable thousands separators and return size in kilobytes.
 * -------------------------------------------------------------
 */
function smarty_modifier_formatfilesize($size, $thousand_delim = ",")
{

    if (empty($size))
        return 0;
    $size = $size / 1024;
    $formatted_size = number_format($size)."&nbsp;Kb";
    if ($thousand_delim!=",")
        $formatted_size = str_replace(",",$thousand_delim,$formatted_size);

    return $formatted_size;

}
