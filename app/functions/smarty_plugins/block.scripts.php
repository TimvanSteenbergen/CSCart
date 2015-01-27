<?php

use Tygh\Development;
use Tygh\Registry;
use Tygh\Storage;

/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */
function smarty_block_scripts($params, $content, &$smarty, &$repeat)
{
    if ($repeat == true) {
        return;
    }

    if (Registry::get('config.tweaks.dev_js')) {
        return $content;
    }

    $scripts = array();
    $dir_root = Registry::get('config.dir.root');
    $return = '';

    if (preg_match_all('/\<script(.*?)\>(.*?)\<\/script\>/s', $content, $m)) {
        $contents = '';

        foreach ($m[1] as $src) {
            if (!empty($src) && preg_match('/src ?= ?"([^"]+)"/', $src, $_m)) {
                $scripts[] = str_replace(Registry::get('config.current_location'), '', preg_replace('/\?.*?$/', '', $_m[1]));
            }
        }

        // Check file changes in dev mode
        $names = $scripts;
        if (Development::isEnabled('compile_check')) {
            foreach ($names as $index => $name) {
                if (is_file($dir_root . '/' . $name)) {
                    $names[$index] .= filemtime($dir_root . '/' . $name);
                }
            }
        }

        $gz_suffix = (Registry::get('config.tweaks.gzip_css_js') ? '.gz' : '');
        $filename = 'js/tygh/scripts-' . md5(implode(',', $names)) . fn_get_storage_data('cache_id') . '.js';
        if (!Storage::instance('statics')->isExist($filename . $gz_suffix)) {

            foreach ($scripts as $src) {
                $contents .= fn_get_contents(Registry::get('config.dir.root') . $src);
            }

            $contents = str_replace('[files]', implode("\n", $scripts), Registry::get('config.js_css_cache_msg')) . $contents;

            Storage::instance('statics')->put($filename . $gz_suffix, array(
                'contents' => $contents,
                'compress' => Registry::get('config.tweaks.gzip_css_js'),
                'caching' => true
            ));
        }

        $return = '<script type="text/javascript" src="' . Storage::instance('statics')->getUrl($filename) . '?ver=' . PRODUCT_VERSION . '"></script>';

        foreach ($m[2] as $sc) {
            if (!empty($sc)) {
                $return .= '<script type="text/javascript">' . $sc . '</script>' . "\n";
            }
        }
    }

    return $return;
}
