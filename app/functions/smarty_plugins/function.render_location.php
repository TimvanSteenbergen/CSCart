<?php

use Tygh\BlockManager\SchemesManager;
use Tygh\BlockManager\RenderManager;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_render_location($params, &$smarty)
{
    if (!empty($params['dispatch'])) {
        $dispatch = $params['dispatch'];
    } elseif ($smarty->getTemplateVars('exception_status')) {
        $dispatch = 'no_page';
    } else {
        $dispatch = !empty($_REQUEST['dispatch']) ? $_REQUEST['dispatch'] : 'index.index';
    }

    $location_id = 0;
    if (!empty($params['location_id'])) {
        $location_id = $params['location_id'];
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

    $lang_code = !empty($params['lang_code']) ? $params['lang_code'] : DESCR_SL;

    $br = new RenderManager($dispatch, $area, $dynamic_object, $location_id, $lang_code);

    return $br->render();
}
