<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */

use Tygh\Registry;

function smarty_block_hook($params, $content, &$smarty)
{
    static $overrides = array();
    $hook_content = '';
    $hook_name = $smarty->template_area . '_' . str_replace(':', '__', $params['name']);

    Registry::registerCache('thooks_' . $hook_name, array('addons'), Registry::cacheLevel('static'));
    $hooks_list = Registry::ifGet('thooks_' . $hook_name, array());

    if (empty($hooks_list)) {
        list($dir, $name) = explode(':', $params['name']);

        $hooks_list = array(
            'pre' => array(),
            'post' => array(),
            'override' => array()
        );

        foreach (Registry::get('addons') as $addon => $data) {

            if ($data['status'] == 'D') {
                continue;
            }

            $files = array();

            foreach (Registry::get('addons') as $_addon => $_data) {
                if ($_data['status'] == 'D' || $_addon == $addon) {
                    continue;
                }

                $files[] = 'addons/' . $addon . '/addons/' . $_addon . '/hooks/' . $dir . '/' . $name;
            }

            $files[] = 'addons/' . $addon . '/hooks/' . $dir . '/' . $name;

            foreach ($files as $file) {

                if ($smarty->templateExists($file . '.pre.tpl')) {
                    $hooks_list['pre'][] = $file . '.pre.tpl';
                }
                if ($smarty->templateExists($file . '.post.tpl')) {
                    $hooks_list['post'][] = $file . '.post.tpl';
                }
                if ($smarty->templateExists($file . '.override.tpl')) {
                    $hooks_list['override'][] = $file . '.override.tpl';
                }
            }
        }

        Registry::set('thooks_' . $hook_name, $hooks_list);
    }

    if (is_null($content)) {
        // reset override for current hook
        $overrides[$params['name']] = false;

        // override hook should be call for opened tag to prevent pre/post hook execution
        if (!empty($hooks_list['override'])) {
            $override_content = '';
            foreach ($hooks_list['override'] as $tpl) {
                if ($tpl == $smarty->template_resource) {
                    continue;
                }

                $_hook_content = $smarty->fetch($tpl);
                if (trim($_hook_content)) {
                    $overrides[$params['name']] = true;

                    $hook_content = $_hook_content;
                }
            }
        }

        // prehook should be called for the opening {hook} tag to allow variables passed from hook to body
        if (empty($overrides[$params['name']])) {
            if (!empty($hooks_list['pre'])) {
                foreach ($hooks_list['pre'] as $tpl) {
                    $hook_content .= $smarty->fetch($tpl);
                }
            }
        }

    } else {
        // post hook should be called only if override hook was no executed
        if (empty($overrides[$params['name']])) {
            if (!empty($hooks_list['post'])) {
                foreach ($hooks_list['post'] as $tpl) {
                    $hook_content .= $smarty->fetch($tpl);
                }
            }

            $hook_content =  $content . "\n" . $hook_content;
        }
    }

    fn_set_hook('smarty_block_hook_post', $params, $content, $overrides, $smarty, $hook_content);

    return $hook_content;
}
