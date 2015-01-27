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

use Tygh\Registry;
use Tygh\Less;
use Tygh\Themes\Patterns;
use Tygh\Themes\Styles;
use Tygh\Themes\Themes;
use Tygh\BlockManager\Layout;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST' && Registry::get('config.demo_mode')) {
    // Customer do not have rights to save styles in Demo mode

    fn_set_notification('W', __('warning'), __('error_demo_mode'));
    exit;
}

if (!Registry::get('runtime.customization_mode.theme_editor') && !Registry::get('runtime.customization_mode.design')) {
    fn_set_notification('E', __('error'), __('access_denied'));

    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $_suffix = '';
    $theme = Themes::factory(fn_get_theme_path('[theme]', 'C'));

    if ($mode == 'save') {

        $theme_manifest = $theme->getManifest();

        if (empty($theme_manifest['converted_to_css'])) {
            // FIXME: Backward preset compatibility
            if (!empty($_REQUEST['preset_id'])) {
                $_REQUEST['style_id'] = $_REQUEST['preset_id'];
            }
            if (!empty($_REQUEST['preset'])) {
                $_REQUEST['style'] = $_REQUEST['preset'];
            }

            fn_theme_editor_save_style($_REQUEST['style_id'], $_REQUEST['style']);

            fn_attach_image_pairs('logotypes', 'logos');
        } else {
            if ($theme->updateCssFile($_REQUEST['selected_css_file'], $_REQUEST['css_content'])) {
                Registry::get('ajax')->assign('css_url', $theme->getCssUrl());
                fn_set_notification('N', __('notice'), __('text_changes_saved'));
            } else {
                fn_set_notification('E', __('error'), __('error_occurred'));
            }
            $_suffix = '?selected_css_file=' . $_REQUEST['selected_css_file'];
        }

    } elseif ($mode == 'convert_to_css') {

        if ($theme->convertToCss()) {

            foreach (Layout::instance()->getList() as $layout_id => $layout_data) {
                Layout::instance()->update(array(
                    'style_id' => Registry::get('runtime.layout.style_id')
                ), $layout_id);
            }

            Registry::get('ajax')->assign('css_url', $theme->getCssUrl());

            fn_set_notification('N', __('notice'), __('text_changes_saved'));
        } else {
            fn_set_notification('E', __('error'), __('error_occurred'));
        }

    } elseif ($mode == 'restore_less') {

        if ($theme->restoreLess()) {
            Registry::get('ajax')->assign('css_url', $theme->getCssUrl());
            fn_set_notification('N', __('notice'), __('text_changes_saved'));
        } else {
            fn_set_notification('E', __('error'), __('error_occurred'));
        }
    }

    return array(CONTROLLER_STATUS_OK, 'theme_editor.view' . $_suffix);
}

if ($mode == 'view') {
    // FIXME: Bacward preset compatibility
    if (!empty($_REQUEST['preset_id'])) {
        $_REQUEST['style_id'] = $_REQUEST['preset_id'];
    }

    if (!empty($_REQUEST['style_id'])) {
        fn_theme_editor_set_style($_REQUEST['style_id']);
    }

    fn_theme_editor($_REQUEST);

    Registry::get('view')->display('views/theme_editor/view.tpl');
    exit;

} elseif ($mode == 'delete_style' || $mode == 'delete_preset') {
    // FIXME: Bacward preset compatibility
    if (!empty($_REQUEST['preset_id'])) {
        $_REQUEST['style_id'] = $_REQUEST['preset_id'];
    }

    Styles::factory(fn_get_theme_path('[theme]', 'C'))->delete($_REQUEST['style_id']);

    return array(CONTROLLER_STATUS_OK, 'theme_editor.view');

} elseif ($mode == 'get_css') {

    // FIXME: Backward presets compatibility
    if (!empty($_REQUEST['preset'])) {
        $_REQUEST['style'] = $_REQUEST['preset'];
    }

    $css_filename = !empty($_REQUEST['css_filename']) ? fn_basename($_REQUEST['css_filename']) : '';
    $theme_name = fn_get_theme_path('[theme]', 'C');
    $content = '';

    if (!empty($css_filename)) {

        $content = fn_get_contents(fn_get_cache_path(false) . 'theme_editor/' . $css_filename);
        if (strpos($content, '#LESS') !== false) {
            list($css_content, $less_content) = explode('#LESS#', $content);
            $css_content = Less::parseUrls($css_content, Registry::get('config.dir.root'), fn_get_theme_path('[themes]/[theme]/media'));
        } else {
            $less_content = $content;
            $css_content = '';
        }

        $data = array();

        // FIXME: Bacward preset compatibility
        if (!empty($_REQUEST['preset_id'])) {
            $_REQUEST['style_id'] = $_REQUEST['preset_id'];
        }

        // If theme ID passed, set default theme
        if (!empty($_REQUEST['style_id'])) {
            fn_theme_editor_set_style($_REQUEST['style_id']);

        // If theme elements passed, get them
        } elseif (!empty($_REQUEST['style']['data'])) {
            $data = $_REQUEST['style']['data'];
            $data = Styles::factory($theme_name)->processCopy('', $data);
        }

        $less = new Less();

        $import_path[] = Registry::get('config.dir.root') . fn_get_theme_path('/[relative]/[theme]/css');
        $import_path[] = Registry::get('config.dir.root') . fn_get_theme_path('/[relative]/[theme]/css/tygh');
        $less->setImportDir($import_path);

        $content = $css_content . $less->customCompile($less_content, Registry::get('config.dir.root'), $data);

        // remove external fonts to avoid flickering when styles are reloaded
        //$content = preg_replace("/@font-face \{.*?\}/s", '', $content);
    }

    header('content-type: text/css');
    fn_echo($content);
    exit;

} elseif ($mode == 'duplicate') {

    // FIXME: Bacward preset compatibility
    if (!empty($_REQUEST['preset_id'])) {
        $_REQUEST['style_id'] = $_REQUEST['preset_id'];
    }

    if (!empty($_REQUEST['name']) && Styles::factory(fn_get_theme_path('[theme]', 'C'))->copy($_REQUEST['style_id'], $_REQUEST['name'])) {
        fn_theme_editor_set_style($_REQUEST['name']);
    } else {
        //FIXME: Presets backward compability
        $path = fn_get_theme_path('[relative]/[theme]/styles');
        if (!is_dir($path)) {
            $path = fn_get_theme_path('[relative]/[theme]/presets');
        }

        fn_set_notification('E', __('error'), __('theme_editor.style_data_cannot_be_saved', array(
            '[theme_dir]' => $path
        )));
    }

    return array(CONTROLLER_STATUS_OK, 'theme_editor.view');

}

function fn_theme_editor_set_style($style_id)
{
    $style_id = fn_basename($style_id);
    $theme_name = Registry::get('runtime.layout.theme_name');
    $layout_id = Registry::get('runtime.layout.layout_id');

    Styles::factory($theme_name)->setStyle($layout_id, $style_id);
    Registry::set('runtime.layout.style_id', $style_id);

    fn_clear_cache('statics', 'design/');

    return true;
}

function fn_theme_editor_save_style($style_id, $style)
{
    $theme_name = fn_get_theme_path('[theme]', 'C');

    if (empty($style_id) && !empty($style['name'])) {
        $style_id = $style['name'];

        Styles::factory($theme_name)->copy(Registry::get('runtime.layout.style_id'), $style_id);
    }

    if (empty($style) || empty($style['data']) || empty($style_id)) {
        return false;
    }

    // Attach patterns
    $uploaded_data = fn_filter_uploaded_data('backgrounds');
    if (!empty($uploaded_data)) {
        $style = Patterns::instance()->save($style_id, $style, $uploaded_data);
    }

    // Save style data
    if (!Styles::factory($theme_name)->update($style_id, $style)) {
        //FIXME: Presets backward compability
        $path = fn_get_theme_path('[relative]/[theme]/styles');
        if (!is_dir($path)) {
            $path = fn_get_theme_path('[relative]/[theme]/presets');
        }

        fn_set_notification('E', __('error'), __('theme_editor.style_data_cannot_be_saved', array(
            '[theme_dir]' => $path
        )));

        return false;
    }

    fn_theme_editor_set_style($style_id);

    return $style_id;
}

function fn_theme_editor($params, $lang_code = CART_LANGUAGE)
{
    $view = Registry::get('view');

    $theme_name = Registry::get('runtime.layout.theme_name');
    $layout_id = Registry::get('runtime.layout.layout_id');

    if (!Registry::get('runtime.layout.style_id')) {
        $default_style_id = Styles::factory($theme_name)->getDefault();

        db_query('UPDATE ?:bm_layouts SET style_id = ?s WHERE layout_id = ?i', $default_style_id, $layout_id);
        Registry::set('runtime.layout.style_id', $default_style_id);
    }

    $style_id = Registry::get('runtime.layout.style_id');
    // Backward presets compatibility
    Registry::set('runtime.layout.preset_id', $style_id);

    // get current style
    $current_style = Styles::factory($theme_name)->get($style_id, array('parse' => true));

    // get all styles
    $styles_list = Styles::factory($theme_name)->getList();

    $schema = Styles::factory($theme_name)->getSchema();
    $sections = array(
        'te_general' => 'theme_editor.general',
        'te_logos' => 'theme_editor.logos',
        'te_colors' => 'theme_editor.colors',
        'te_fonts' => 'theme_editor.fonts',
        'te_backgrounds' => 'theme_editor.backgrounds',
        'te_css' => 'theme_editor.css',
    );

    foreach ($sections as $section_id => $section) {
        if ($section_id == 'te_logos') { // Logos is hardcoded section, no need to define it in schema
            continue;
        }
        $section_id = str_replace('te_', '', $section_id);
        if (!isset($schema[$section_id])) {
            unset($sections['te_' . $section_id]);
        }
    }

    if (empty($params['selected_section']) || !isset($sections[$params['selected_section']])) {
        reset($sections);
        $params['selected_section'] = key($sections);
    }

    $theme = Themes::factory($theme_name);
    $theme_manifest = $theme->getManifest();

    if (!empty($theme_manifest['converted_to_css'])) {
        if (empty($params['selected_css_file'])) {
            $params['selected_css_file'] = Themes::$compiled_less_filename;
        }

        $view->assign('selected_css_file', $params['selected_css_file']);
        $view->assign('css_files_list', $theme->getCssFilesList());
        $view->assign('css_content', $theme->getCssContents($params['selected_css_file']));
    }

    $view->assign('cse_logo_types', fn_get_logo_types());
    $view->assign('cse_logos', fn_get_logos(Registry::get('runtime.company_id')));
    $view->assign('selected_section', $params['selected_section']);
    $view->assign('te_sections', $sections);
    $view->assign('current_style', $current_style);
    $view->assign('props_schema', $schema);
    $view->assign('theme_patterns', Patterns::instance()->get($style_id));
    $view->assign('styles_list', $styles_list);

    // FIXME: Backward compatibility
    $view->assign('presets_list', $styles_list);
    $view->assign('current_preset', $current_style);

    $view->assign('manifest', Styles::factory($theme_name)->getManifest());
    $view->assign('theme_manifest', $theme_manifest);

    $view->assign('layouts', Layout::instance()->getList(array(
        'theme_name' => $theme_name
    )));
    $view->assign('layout_data', Layout::instance()->get($layout_id));
    $view->assign('theme_url', fn_url(empty($params['theme_url']) ? '' : $params['theme_url']));
}
