<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty array split function plugin
 *
 * Type:     function<br>
 * Name:     split<br>
 * Purpose:  Split array into chunks
 * @param array
 * @param integer
 * @param bool
 * @param bool
 * @return array
 */

function smarty_function_split($params, &$smarty)
{

    if (empty($params['data'])) {
        //$smarty->trigger_error("split: array doesn't defined");
        return;
    }
    if (empty($params['size'])) {
        $smarty->trigger_error("split: size doesn't defined");

        return;
    }
    if (empty($params['assign'])) {
        $smarty->trigger_error("split: assing variable doesn't defined");

        return;
    }

    $chunks = array();
    $size = count($params['data']);
    if ($params['simple'] == true) {
        $items_per_column = !empty($params['size_is_horizontal']) ? ceil($size / $params['size']) : $params['size'];
        for ($i=0; $i<$size; $i=$i+$items_per_column) {
            $chunks[] = array_slice($params['data'], $i, $items_per_column);
        }

    } else {
        if ($params['vertical_delimition'] == false) {
            $chunks = array_chunk($params['data'], $params['size'], $params['preverse_keys']);
        } else {

            $chunk_count = ($params['size_is_horizontal'] == true) ? ceil(count($params['data']) / $params['size']) : $size;
            $chunk_index = 0;
            foreach ($params['data'] as $key => $value) {
                $chunks[$chunk_index][] = $value;
                if (++$chunk_index == $chunk_count) {
                    $chunk_index = 0;
                }
            }
        }

        if (empty($params['skip_complete'])) {
            end($chunks);
            $end_key = key($chunks);
            while (sizeof($chunks[$end_key]) < $params['size']) {
                $chunks[$end_key][] = '';
            }
        }
    }

    $smarty->assign($params['assign'], $chunks, false);
}

/* vim: set expandtab: */
