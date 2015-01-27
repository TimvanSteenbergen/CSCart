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
 * Name:     sort_by<br>
 * Purpose:  allows arrays of named arrays to be sorted by a given field
 * Example:  {$fields|@sort_by:"-name, #age"}
 * ------
 *
 *  -------------------------------------------------------
 */

//
// Modifier: sortby -
//
function smarty_modifier_sort_by($arrData, $sortfields)
{
    array_sort_by_fields($arrData ,$sortfields);

    return $arrData;
}

function array_sort_by_fields(&$data, $sortby)
{
    static $sort_funcs = array();

    if (empty($sort_funcs[$sortby])) {
        $code = "\$c=0;";
        foreach (fn_explode(',', $sortby) as $key) {
            $d = '1';
            if (substr($key, 0, 1) == '-') {
                $d = '-1';
                $key = substr($key, 1);
            }
            if (substr($key, 0, 1) == '#') {
                $key = substr($key, 1);
                $code .= "if ( \$a['$key'] > \$b['$key']) return $d * 1;\n";
                $code .= "if ( \$a['$key'] < \$b['$key']) return $d * -1;\n";
            } else {
                $code .= "if ( (\$c = strcasecmp(\$a['$key'],\$b['$key'])) != 0 ) return $d * \$c;\n";
            }
        }
        $code .= 'return $c;';
        $sort_func = $sort_funcs[$sortby] = create_function('$a, $b', $code);
    } else {
        $sort_func = $sort_funcs[$sortby];
    }

    uasort($data, $sort_func);
}

/* vim: set expandtab: */
