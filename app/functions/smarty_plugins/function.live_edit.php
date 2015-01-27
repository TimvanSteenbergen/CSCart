<?php

use Tygh\Registry;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

function smarty_function_live_edit($params, &$smarty)
{
    if (Registry::get('runtime.customization_mode.live_editor') && !empty($params['name'])) {

        $content = ' data-ca-live-editor-obj="' . $params['name'] . '"';

        if (!empty($params['phrase'])) {
            $phrase = htmlspecialchars($params['phrase']);
            $content .= ' data-ca-live-editor-phrase="' . $phrase . '"';
        }

        if (!empty($params['need_render'])) {
            $content .= ' data-ca-live-editor-need-render="true"';
        }

        if (!empty($params['input_type'])) {
            $content .= ' data-ca-live-editor-input-type="' . $params['input_type'] . '"';
        }

        return $content;
    }

}
