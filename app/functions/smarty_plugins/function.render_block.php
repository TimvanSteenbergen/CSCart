<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

use Tygh\BlockManager\RenderManager;
use Tygh\BlockManager\Block;
use Tygh\BlockManager\SchemesManager;

function smarty_function_render_block($params, &$smarty)
{
    if (!empty($params['block_id'])) {
        $block_id =  $params['block_id'];
        $snapping_id = !empty($params['snapping_id']) ? $params['snapping_id'] : 0;

        if (!empty($params['dispatch'])) {
            $dispatch = $params['dispatch'];
        } else {
            $dispatch = !empty($_REQUEST['dispatch']) ? $_REQUEST['dispatch'] : 'index.index';
        }

        $area = !empty($params['area']) ?  $params['area'] : AREA;

        if (!empty($params['dynamic_object'])) {
            $dynamic_object = $params['dynamic_object'];
        } elseif (!empty($_REQUEST['dynamic_object']) && $area != 'C') {
            $dynamic_object = $_REQUEST['dynamic_object'];
        } else {
            $dynamic_object_scheme = SchemesManager::getDynamicObject($dispatch, $area);
            if (!empty($dynamic_object_scheme) && !empty($_REQUEST[$dynamic_object_scheme['key']])) {
                $dynamic_object['object_type'] = $dynamic_object_scheme['object_type'];
                $dynamic_object['object_id'] = $_REQUEST[$dynamic_object_scheme['key']];
            } else {
                $dynamic_object = array();
            }
        }

        $block = Block::instance()->getById($block_id, $snapping_id, $dynamic_object, DESCR_SL);

        return RenderManager::renderBlock($block);
    }
}

function smarty_function_group_output($blocks, $group_data, &$smarty)
{

}

function smarty_function_block_output($_block_data, &$smarty)
{

}
