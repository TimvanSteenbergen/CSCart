<?php
/***************************************************************************
*                                                                          *
*   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

namespace Tygh\SmartyEngine;

use Tygh\Registry;
use Tygh\Embedded;
use Tygh\Tools\Url;

class Filters
{
    /**
     * Prefilter: form tooltip
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function preFormTooltip($content, \Smarty_Internal_Template $template)
    {
        $pattern = '/\<label[^>]*\>.*(\{__\("([^\}]+)"\)\}[^:\<]*).*\<\/label\>/';

        if (preg_match_all($pattern, $content, $matches)) {
            $cur_templ = $template->template_resource;

            $template_pattern = '/([^\/\.]+)/';
            $template_name = '';
            $ignored_names = array('tpl');

            if (preg_match_all($template_pattern, $cur_templ, $template_matches)) {
                foreach ($template_matches[0] as $k => $m) {
                    if (!in_array($template_matches[1][$k], $ignored_names)) {
                        $template_name .= $template_matches[1][$k] . '_';
                    }
                }
            }

            $template_pref = 'tt_' . $template_name;
            $template_tooltips = fn_get_lang_vars_by_prefix($template_pref);

            foreach ($matches[0] as $k => $m) {
                $field_name = $matches[2][$k];
                preg_match("/(^[a-zA-z0-9][\.a-zA-Z0-9_]*)/", $field_name, $name_matches);

                if (@strlen($name_matches[0]) != strlen($field_name)) {
                    continue;
                }

                $label = $matches[1][$k];

                $template_lang_var = $template_pref . $field_name;
                $common_lang_var = 'ttc_' . $field_name;

                if (isset($_REQUEST['stt'])) {
                    $template_text = isset($template_tooltips[$template_lang_var]) ? '{__("' . $template_lang_var . '")}' : '';
                    $common_tip = __($common_lang_var);
                    $common_text = '';
                    if ($common_tip != '_' . $common_lang_var) {
                        $common_text = '{__("' . $common_lang_var . '")}';
                    }

                    $tooltip_text = sprintf("%s: %s <br/> %s: %s", $common_lang_var, $common_text, $template_lang_var, $template_text);
                    $tooltip = '{capture name="tooltip"}' . $tooltip_text . '{/capture}{include file="common/tooltip.tpl" tooltip=$smarty.capture.tooltip}';
                } else {
                    if (isset($template_tooltips[$template_lang_var])) {
                        $tooltip_text = '__("' . $template_lang_var . '")';
                    } else {
                        $tooltip = __($common_lang_var);
                        if ($tooltip == '_' . $common_lang_var || empty($tooltip)) {
                            continue;
                        }
                        $tooltip_text = '__("' . $common_lang_var . '")';
                    }

                    $tooltip = '{include file="common/tooltip.tpl" tooltip=' . $tooltip_text . '}';
                }
                $tooltip_added = str_replace($label, $label . $tooltip, $matches[0][$k]);

                $content = str_replace($matches[0][$k], $tooltip_added, $content);
            }
        }

        return $content;
    }

    /**
     * Prefilter: template wrapper for design mode
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function preTemplateWrapper($content, \Smarty_Internal_Template $template)
    {
        $cur_templ = fn_addon_template_overrides($template->template_resource, $template);

        $ignored_template = array(
            'index.tpl',
            'common/pagination.tpl',
            'views/categories/components/menu_items.tpl',
            'views/block_manager/render/location.tpl',
            'views/block_manager/render/container.tpl',
            'views/block_manager/render/grid.tpl',
            'views/block_manager/render/block.tpl'
        );

        if (!in_array($cur_templ, $ignored_template) && fn_get_file_ext($cur_templ) == 'tpl') { // process only "real" templates (not eval'ed, etc.)
            $content =
                '{if $runtime.customization_mode.design == "Y" && $smarty.const.AREA == "C"}' .
                    '{capture name="template_content"}' . $content . '{/capture}' .
                    '{if $smarty.capture.template_content|trim}' .
                        '{if $auth.area == "A"}' .
                            '<span class="cm-template-box template-box" data-ca-te-template="' . $cur_templ . '" id="{set_id name="' . $cur_templ . '"}">' .
                            '<div class="cm-template-icon icon-edit ty-icon-edit hidden"></div>' .
                            '{$smarty.capture.template_content nofilter}<!--[/tpl_id]--></span>' .
                        '{else}{$smarty.capture.template_content nofilter}{/if}{/if}' .
                '{else}' . $content . '{/if}';
        }

        return $content;
    }

    /**
     * Postfilter: gets all available language variables in templates and puts their retrieving to the template start
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function postTranslation($content, \Smarty_Internal_Template $template)
    {
        $content = preg_replace('/([^\>])__\(/', '\1$_smarty_tpl->__(', $content);

        if (preg_match_all('/__\(\"([\w\.]*?)\"/i', $content, $matches)) {
            return "<?php\nfn_preload_lang_vars(array('" . implode("','", $matches[1]) . "'));\n?>\n" . $content;
        }

        return $content;
    }

    /**
     * Output filter: translation mode
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function outputLiveEditorWrapper($content, \Smarty_Internal_Template $template)
    {
        $pattern = '/\<(input|img|div)[^>]*?(\[lang name\=([\w-\.]+?)( cm\-pre\-ajax)?\](.*?)\[\/lang\])[^>]*?\>/';
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $k => $m) {
                $phrase_replaced = str_replace($matches[2][$k], $matches[5][$k], $matches[0][$k]);
                if (strpos($m, 'class="') !== false) {
                    $class_added = str_replace('class="', 'data-ca-live-edit="langvar::' . $matches[3][$k] . $matches[4][$k] . '" class="cm-live-editor-need-wrap ', $phrase_replaced);
                } else {
                    $class_added = str_replace($matches[1][$k], $matches[1][$k] . ' data-ca-live-edit="langvar::' . $matches[3][$k] . $matches[4][$k] . '" class="cm-live-editor-need-wrap"', $phrase_replaced);
                }

                if ($matches[1][$k] == 'div') {
                    $content = str_replace($matches[0][$k], $phrase_replaced, $content);
                } else {
                    $content = str_replace($matches[0][$k], $class_added, $content);
                }
            }
        }

        $pattern = '/(\<(textarea|option)[^<]*?)\>(\[lang name\=([\w-\.]+?)( cm\-pre\-ajax)?\](.*?)\[\/lang\])[^>]*?\>/is';
        if (preg_match_all($pattern, $content, $matches)) {
            foreach ($matches[0] as $k => $m) {
                $phrase_replaced = str_replace($matches[3][$k], $matches[6][$k], $matches[0][$k]);
                if (strpos($m, 'class="') !== false) {
                    $class_added = str_replace('class="', 'data-ca-live-edit="langvar::' . $matches[4][$k] . $matches[5][$k] . '" class="cm-live-editor-need-wrap ', $phrase_replaced);
                } else {
                    $class_added = str_replace('<' . $matches[2][$k], '<' . $matches[2][$k] . ' data-ca-live-edit="langvar::' . $matches[4][$k] . $matches[5][$k] . '" class="cm-live-editor-need-wrap"', $phrase_replaced);
                }
                $content = str_replace($matches[0][$k], $class_added, $content);
            }
        }

        $pattern = '/<title>(.*?)<\/title>/is';
        $pattern_inner = '/\[(lang) name\=([\w-]+?)( cm\-pre\-ajax)?\](.*?)\[\/\1\]/is';
        preg_match($pattern, $content, $matches);
        $phrase_replaced = $matches[0];
        $phrase_replaced = preg_replace($pattern_inner, '$4', $phrase_replaced);
        $content = str_replace($matches[0], $phrase_replaced, $content);

        // remove translation tags from elements attributes
        $pattern = '/(\<[^<>]*\=[^<>]*)(\[lang name\=([\w-\.]+?)( cm\-pre\-ajax)?\](.*?)\[\/lang\])[^<>]*?\>/is';
        while (preg_match($pattern, $content, $matches)) {
            $phrase_replaced = preg_replace($pattern_inner, '$4', $matches[0]);
            $content = str_replace($matches[0], $phrase_replaced, $content);
        }

        $pattern = '/(?<=>)[^<]*?\[(lang) name\=([\w-\.]+?)( cm\-pre\-ajax)?\](.*?)\[\/\1\]/is';
        $pattern_inner = '/\[(lang) name\=([\w-\.]+?)( cm\-pre\-ajax)?\]((?:(?>[^\[]+)|\[(?!\1[^\]]*\]))*?)\[\/\1\]/is';
        $replacement = '<var class="live-edit-wrap"><i class="cm-icon-live-edit icon-live-edit ty-icon-live-edit"></i><var data-ca-live-edit="langvar::$2$3" class="cm-live-edit live-edit-item">$4</var></var>';
        while (preg_match($pattern, $content, $matches)) {
            $phrase_replaced = $matches[0];
            while (preg_match($pattern_inner, $phrase_replaced)) {
                $phrase_replaced = preg_replace($pattern_inner, $replacement, $phrase_replaced);
            }
            $content = str_replace($matches[0], $phrase_replaced, $content);
        }

        $pattern = '/\[(lang) name\=([\w-\.]+?)( cm\-pre\-ajax)?\](.*?)\[\/\1\]/';
        $replacement = '$4';
        $content = preg_replace($pattern, $replacement, $content);

        return $content;
    }

    /**
     * Output filter: design mode
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function outputTemplateIds($content, \Smarty_Internal_Template $template)
    {
        $pattern = '/(\<head\>.*?)(\<span[^<>]*\>|\<\/span\>|\<img[^<>]*\>|\<!--[\w]*--\>)+?(.*?\<\/head\>)/is';
        while (preg_match($pattern, $content, $match)) {
            $content = str_replace($match[0], $match[1] . $match[3], $content);
        }
        $pattern = '/\<span[^<>]*\>|\<\/span\>|\<img[^<>]*\>|\<!--[\w]*--\>/is';
        $glob_pattern = '/\<script[^<>]*\>.*?\<\/script\>/is';
        if (preg_match_all($glob_pattern, $content, $matches)) {
            foreach ($matches[0] as $k => $m) {
                $replace_script = preg_replace($pattern, '', $matches[0][$k]);
                $content = str_replace($matches[0][$k], $replace_script, $content);
            }
        }

        static $template_ids;

        if (!isset($template_ids)) {
            $template_ids = array();
        }

        $pattern = '/\[(tpl_id) ([^ ]*)\]((?:(?>[^\[]+)|\[(?!\1[^\]]*\]))*?)\[\/\1\]/is';
        while (preg_match($pattern, $content, $matches)) {
            $id = 'te' . md5($matches[2]);
            if (empty($template_ids[$matches[2]])) {
                $template_ids[$matches[2]] = 1;
            } else {
                $template_ids[$matches[2]]++;
                $id .= '_' . $template_ids[$matches[2]];
            }
            $content = preg_replace($pattern, $id . '${3}' . $id, $content, 1);
        }

        return $content;
    }

    /**
     * Output filter: adds unique field to all forms to protect from CSRF attacks
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function outputSecurityHash($content, \Smarty_Internal_Template $template)
    {
        $content = preg_replace('/<input type="hidden" name="security_hash".*?>/i', '', $content);
        $content = str_replace('</form>', '<input type="hidden" name="security_hash" class="cm-no-hide-input" value="'. fn_generate_security_hash() .'" /></form>', $content);

        return $content;
    }

    /**
     * Output filter: adds ability to share objects per companies
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function outputSharing($content, \Smarty_Internal_Template $template)
    {
        if (!fn_allowed_for('ULTIMATE')) {
            return $content;
        }

        if (Registry::get('runtime.simple_ultimate')) {
            return $content;
        }

        $sharing = Registry::get('sharing');
        $content_expr = '/<!--Content-->.*?<!--\/Content-->/is';

        if (defined('AJAX_REQUEST')) {
            $central_content = $content;
        } elseif (preg_match($content_expr, $content, $central_content)) {
            $central_content = $central_content[0];
        }

        if (!empty($central_content)) {
            if (!empty($sharing['tpl_tabs'])) {
                foreach ($sharing['tpl_tabs'] as $object => $data) {
                    // Add a new tab
                    $tab_expr = '/(<div[^>]+?class[^>]*?tabs.*?>.*?<ul.*?>)(.*?)(<\/ul>)/is';
                    if (preg_match($tab_expr, $central_content, $matches)) {
                        if (!empty($matches[2])) {
                            // Add a new tab
                            $tab_content = $matches[1] . $matches[2] . '<li id="tab_share_object' . $data['params']['object_id'] . '" class="cm-js cm-ajax"><a href="' . fn_url('companies.get_object_share?object=' . $data['params']['object'] . '&object_id=' . $data['params']['object_id']) . '">' . __('share') . '</a></li>' . $matches[3];

                            $central_content = preg_replace($tab_expr, fn_preg_replacement_quote($tab_content), $central_content, 1);
                        }

                        // Get main form to add tab content inside.
                        $form_content_expr = '/<form.*?>.*?<\/form>/is';
                        if (preg_match($form_content_expr, $central_content, $matches)) {
                            $form = $matches[0];

                            // Add tab content
                            $tab_content_expr = '/<div[^>]+?id[^>]*?content_.*?>/is';
                            if (preg_match($tab_content_expr, $form, $tab_matches)) {
                                $tab_content = '<div class="cm-tabs-content hidden" id="content_tab_share_object' . $data['params']['object_id'] . '"></div>' . $tab_matches[0];

                                $form = preg_replace($tab_content_expr, fn_preg_replacement_quote($tab_content), $form, 1);
                                $central_content = preg_replace($form_content_expr, fn_preg_replacement_quote($form), $central_content, 1);
                            }
                        }
                    }
                }

                if (defined('AJAX_REQUEST')) {
                    $content = $central_content;
                } else {
                    $content = preg_replace($content_expr, fn_preg_replacement_quote($central_content), $content, 1);

                }
            }
        }

        return $content;
    }

    /**
     * Output filter: Transforms URLs to the appropriate format for the embedded mode
     * @param  string                    $content  template content
     * @param  \Smarty_Internal_Template $template template instance
     * @return string                    template content
     */
    public static function outputEmbeddedUrl($content, \Smarty_Internal_Template $template)
    {
        $path = Registry::get('config.current_host') . Registry::get('config.current_path');

        // Transform 'href' attribute values of the 'a' elements, which:
        // - have 'href' attribute
        // - the 'href' value contains current path and host, or its a relative url
        // - do not have class attribute starting with 'cm-' prefix

        $pattern = '{'
            . '<(?:a)\s+'
            . '(?=[^>]*\bhref="([^"]*//' . $path . '[^"]*|(?!//)(?!https?)[^"]*)")'
            . '(?![^>]*\bclass="[^"]*cm-[^"]*")'
            . '[^>]*>'
            . '}Usi';

        $content = preg_replace_callback($pattern, function($matches) {
            return str_replace(
                $matches[1],
                Embedded::resolveUrl($matches[1]),
                $matches[0]
            );
        }, $content);

        // Transform relative 'src'attribute values

        $pattern = '{<[^>]+\bsrc="((?!//)(?!https?)[^"]+)"[^>]*>}Usi';

        $content = preg_replace_callback($pattern, function($matches) {
            return str_replace(
                $matches[1],
                Url::resolve($matches[1], Registry::get('config.current_location')),
                $matches[0]
            );
        }, $content);

        $area = Registry::get('view')->getArea();

        if ($area[1] == 'mail') {

            // Transform URLs in the text

            $pattern = '{\bhttps?://' . $path . '[^\s<>"\']*(?=[^>]*<)}s';

            $content = preg_replace_callback($pattern, function($matches) {
                return Embedded::resolveUrl($matches[0]);
            }, $content);

        }

        return $content;
    }

    /**
     * Default Plugin Handler
     * called when Smarty encounters an undefined tag during compilation
     *
     * @param string $name name of the undefined tag
     * @param string $type tag type (e.g. Smarty::PLUGIN_FUNCTION, Smarty::PLUGIN_BLOCK,
     *                                          Smarty::PLUGIN_COMPILER, Smarty::PLUGIN_MODIFIER, Smarty::PLUGIN_MODIFIERCOMPILER)
     * @param object $template template object
     * @param string &$callback returned function name
     * @param string &$script optional returned script filepath if function is external
     * @param bool &$cacheable true by default, set to false if plugin is not cachable (Smarty >= 3.1.8)
     * @return bool true if successfull
     */
    public static function smartyDefaultHandler($name, $type, $template, &$callback, &$script, &$cacheable)
    {
        /* Process all MVE tags */
        if (fn_allowed_for('ULTIMATE')) {
            if (strpos($name, '_mve_') !== false) {
                $callback = array('Tygh\SmartyEngine\Filters', 'smartyHandlerProcessing');

                return true;
            }
        }

        /* Process all ULT tags */
        if (fn_allowed_for('MULTIVENDOR')) {
            if (strpos($name, '_ult_') !== false) {
                $callback = array('Tygh\SmartyEngine\Filters', 'smartyHandlerProcessing');

                return true;
            }
        }

        return false;
    }

    /**
     * Process value of undefined tag
     *
     * @param  mixed $variable_value
     * @return mixed Processed value
     */
    public static function smartyHandlerProcessing($variable_value)
    {
        return false;
    }

}
