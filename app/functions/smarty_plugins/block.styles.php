<?php

use Tygh\Embedded;
use Tygh\Registry;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */
function smarty_block_styles($params, $content, &$smarty, &$repeat)
{
    if ($repeat == true) {
        return;
    }

    $prepend_prefix = Embedded::isEnabled() ? 'html#tygh_html body#tygh_body .tygh' : '';

    $styles = array();
    $internal_styles = '';

    //if (preg_match_all('/\<link(.*?href ?= ?"([^"]+)")?[^\>]*\>/is', $content, $m)) {
    if (preg_match_all('/\<link(.*?href\s?=\s?(?:"|\')([^"]+)(?:"|\'))?[^\>]*\>/is', $content, $m)) {
        foreach ($m[2] as $k => $v) {
            $v = preg_replace('/\?.*?$/', '', $v);
            $media = '';
            if (strpos($m[1][$k], 'media=') !== false && preg_match('/media="(.*?)"/', $m[1][$k], $_m)) {
                $media = $_m[1];
            }

            $styles[] = array(
                'file' => str_replace(Registry::get('config.current_location'), Registry::get('config.dir.root'), $v),
                'relative' => str_replace(Registry::get('config.current_location') . '/', '', $v),
                'media' => $media
            );
        }
    }

    if (preg_match_all('/\<style.*\>(.*)\<\/style\>/isU', $content, $m)) {
        $internal_styles = implode("\n\n", $m[1]);
    }

    if (!empty($styles) || !empty($internal_styles)) {
        fn_set_hook('styles_block_files', $styles);

        list($_area) = Registry::get('view')->getArea();
        $filename = fn_merge_styles($styles, $internal_styles, $prepend_prefix, $params, $_area);

        $content = '<link type="text/css" rel="stylesheet" href="' . $filename . '" />';
    }

    return $content;
}
