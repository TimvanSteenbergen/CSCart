<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

/**
 * Smarty id set function plugin
 *
 * Type:     function<br>
 * Name:     set_id<br>
 * Purpose:  function generate id for template
 * @return string
 */

function smarty_function_set_id($param, &$smarty)
{
    $template_tree = smarty_helper_template_tree($smarty);

    $tree = array();
    $count = count($template_tree) - 1;
    if ($template_tree[$count]['filename'] != $param['name']) {
        array_push($tree, $param['name']);
    }
    $depth = $template_tree[$count]['depth'] + 1;
    for ($i = $count; $i >= 0; $i--) {
        if ($template_tree[$i]['depth'] < $depth) {
            $depth = $template_tree[$i]['depth'];
            array_unshift($tree, $template_tree[$i]['filename']);
        }
        if ($depth == 0) {
            break;
        }
    }
    $cur_id = join(',', $tree);

    $id = '[tpl_id ' . $cur_id . ']';

    return $id;
}

function smarty_helper_template_tree($smarty)
{
    $res = array();
    $depth = array();
    $d = 0;
    foreach ($smarty->template_objects as $k => $v) {

        list(, $tpl) = explode('#', $k);
        //$tpl = $v->template_resource;

        if (!empty($v->parent)) {
            if (property_exists($v->parent, 'template_resource')) {

                if (empty($depth[$v->parent->template_resource])) {
                    $depth[$v->parent->template_resource] = ++$d;
                }

                $res[] = array(
                    'filename' => $tpl,
                    'depth' => $depth[$v->parent->template_resource]
                );
            }
        }
    }

    return $res;

}
