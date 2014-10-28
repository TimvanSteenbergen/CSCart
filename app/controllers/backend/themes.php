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

use Tygh\BlockManager\Layout;
use Tygh\Development;
use Tygh\Themes\Styles;
use Tygh\Themes\Themes;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Storage;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'clone') {

        $theme_src = basename($_REQUEST['theme_data']['theme_src']);
        $theme_dest = basename(str_replace(' ', '_', $_REQUEST['theme_data']['theme_dest']));
        $theme_path = fn_get_theme_path('[themes]/' . $theme_dest, 'C');

        if (!file_exists($theme_path)) {

            if (fn_install_theme_files($theme_src, $theme_dest, false)) {
                // Clone layouts
                $layouts = Layout::instance()->getList(array(
                    'theme_name' => $theme_src,
                ));

                foreach ($layouts as $layout) {
                    $src_layout_id = $layout['layout_id'];
                    unset($layout['layout_id']);
                    $layout['theme_name'] = $theme_dest;

                    $dst_layout_id = Layout::instance()->update($layout);

                    Layout::instance()->copyById($src_layout_id, $dst_layout_id);

                    if (!empty($layout['is_default'])) {
                        // Re-init layout data
                        fn_init_layout(array('s_layout' => $dst_layout_id));
                    }
                }

                // Update manifest
                if (file_exists($theme_path . '/' . THEME_MANIFEST)) {
                    $manifest_content = fn_get_contents($theme_path . '/' . THEME_MANIFEST);
                    $manifest = json_decode($manifest_content, true);
                } else {
                    $manifest = parse_ini_file($theme_path . '/' . THEME_MANIFEST_INI);
                }

                $manifest['title'] = $_REQUEST['theme_data']['title'];
                $manifest['description'] = $_REQUEST['theme_data']['description'];

                // Put logos of current layout to manifest
                $logos = fn_get_logos(Registry::get('runtime.company_id'));
                foreach ($logos as $type => $logo) {
                    $filename = fn_basename($logo['image']['relative_path']);
                    Storage::instance('images')->export($logo['image']['relative_path'], $theme_path . '/media/images/' . $filename);
                    $manifest[$type] = 'media/images/' . $filename;
                }

                fn_put_contents($theme_path . '/' . THEME_MANIFEST, json_encode($manifest));
                fn_install_theme($theme_dest, Registry::get('runtime.company_id'), false);
            }

        } else {
            fn_set_notification('W', __('warning'), __('warning_theme_clone_dir_exists'));
        }

    } elseif ($mode == 'upload') {
        $theme_pack = fn_filter_uploaded_data('theme_pack', Registry::get('config.allowed_pack_exts'));

        if (empty($theme_pack[0])) {
            fn_set_notification('E', __('error'), __('text_allowed_to_upload_file_extension', array('[ext]' => implode(',', Registry::get('config.allowed_pack_exts')))));
        } else {
            $theme_pack = $theme_pack[0];

            // Extract the add-on pack and check the permissions
            $extract_path = Registry::get('config.dir.cache_misc') . 'tmp/theme_pack/';
            $destination = Registry::get('config.dir.themes_repository');

            // Re-create source folder
            fn_rm($extract_path);
            fn_mkdir($extract_path);

            fn_copy($theme_pack['path'], $extract_path . $theme_pack['name']);

            if (fn_decompress_files($extract_path . $theme_pack['name'], $extract_path)) {
                fn_rm($extract_path . $theme_pack['name']);

                $non_writable_folders = fn_check_copy_ability($extract_path, $destination);

                if (!empty($non_writable_folders)) {
                    Registry::get('view')->assign('non_writable', $non_writable_folders);

                    if (defined('AJAX_REQUEST')) {
                        Registry::get('view')->display('views/themes/components/correct_permissions.tpl');

                        exit();
                    }

                } else {
                    fn_copy($extract_path, $destination);
                    fn_rm($extract_path);

                    if (defined('AJAX_REQUEST')) {
                        Registry::get('ajax')->assign('force_redirection', fn_url('themes.manage'));

                        exit();
                    }
                }
            }
        }

        if (defined('AJAX_REQUEST')) {
            Registry::get('view')->display('views/themes/components/upload_theme.tpl');

            exit();
        }

    } elseif ($mode == 'recheck') {
        $source = Registry::get('config.dir.cache_misc') . 'tmp/theme_pack/';
        $destination = Registry::get('config.dir.themes_repository');

        if ($action == 'ftp_upload') {
            $ftp_access = array(
                'hostname' => $_REQUEST['ftp_access']['ftp_hostname'],
                'username' => $_REQUEST['ftp_access']['ftp_username'],
                'password' => $_REQUEST['ftp_access']['ftp_password'],
                'directory' => $_REQUEST['ftp_access']['ftp_directory'],
            );

            $ftp_copy_result = fn_copy_by_ftp($source, $destination, $ftp_access);

            if ($ftp_copy_result !== true) {
                fn_set_notification('E', __('error'), $ftp_copy_result);
            }

            if (defined('AJAX_REQUEST')) {
                Registry::get('ajax')->assign('force_redirection', fn_url('themes.manage'));

                exit();
            } else {
                return array(CONTROLLER_STATUS_OK, 'themes.manage');
            }
        }

        $non_writable_folders = fn_check_copy_ability($source, $destination);

        if (!empty($non_writable_folders)) {
            if (!empty($_REQUEST['ftp_access'])) {
                Registry::get('view')->assign('ftp_access', $_REQUEST['ftp_access']);
            }

            Registry::get('view')->assign('non_writable', $non_writable_folders);

            if (defined('AJAX_REQUEST')) {
                Registry::get('view')->display('views/themes/components/correct_permissions.tpl');

                exit();
            }

        } else {
            fn_copy($source, $destination);
            fn_rm($source);

            if (defined('AJAX_REQUEST')) {
                Registry::get('ajax')->assign('force_redirection', fn_url('themes.manage'));

                exit();
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, 'themes.manage');
}

if ($mode == 'install') {
    if (!empty($_REQUEST['theme_name'])) {

        // Copy theme files to design/themes directory
        fn_install_theme_files($_REQUEST['theme_name'], $_REQUEST['theme_name']);
    }

    return array(CONTROLLER_STATUS_OK, 'themes.manage?selected_section=general');

} elseif ($mode == 'delete') {

    fn_delete_theme($_REQUEST['theme_name']);

    return array(CONTROLLER_STATUS_REDIRECT, 'themes.manage');

} elseif ($mode == 'set') {
    $is_exist = Layout::instance()->getList(array(
        'theme_name' => $_REQUEST['theme_name']
    ));

    $company_id = Registry::get('runtime.company_id');

    if (empty($is_exist)) {
        // Create new layout
        fn_install_theme($_REQUEST['theme_name'], $company_id);

    } else {
        Settings::instance()->updateValue('theme_name', $_REQUEST['theme_name'], '', true, $company_id);
    }

    $layout = Layout::instance($company_id)->getDefault($_REQUEST['theme_name']);

    if (!empty($_REQUEST['style'])) {
        $theme = Themes::factory(fn_get_theme_path('[theme]', 'C'));
        $theme_manifest = $theme->getManifest();

        if (empty($theme_manifest['converted_to_css'])) {
            Styles::factory($_REQUEST['theme_name'])->setStyle($layout['layout_id'], $_REQUEST['style']);
        } else {
            fn_set_notification('E', __('error'), __('theme_editor.error_theme_converted_to_css', array(
                '[url]' => fn_url("customization.update_mode?type=theme_editor&status=enable&s_layout=$layout[layout_id]")
            )));
        }
    }

    // We need to re-init layout
    fn_init_layout(array('s_layout' => $layout['layout_id']));

    // Delete compiled CSS file
    fn_clear_cache('statics');

    return array(CONTROLLER_STATUS_REDIRECT, 'themes.manage');

} elseif ($mode == 'manage') {

    $company_id = Registry::get('runtime.simple_ultimate') ? Registry::get('runtime.forced_company_id') : Registry::get('runtime.company_id');
    $available_themes = fn_get_available_themes(Registry::get('settings.theme_name'));

    if (!empty($available_themes['repo']) && !empty($available_themes['installed'])) {
        $available_themes['repo'] = array_diff_key($available_themes['repo'], $available_themes['installed']);
    }

    Registry::get('view')->assign('themes_prefix', fn_get_theme_path('[relative]', 'C'));
    Registry::get('view')->assign('repo_prefix', fn_get_theme_path('[repo]', 'C'));

    Registry::set('navigation.tabs', array(
        'installed_themes' => array (
            'title' => __('installed_themes'),
            'js' => true
        ),
        'browse_all_available_themes' => array (
            'title' => __('browse_all_available_themes'),
            'js' => true
        )
    ));

    $theme_name = fn_get_theme_path('[theme]', 'C', $company_id, false);
    $layout = Layout::instance()->getDefault($theme_name);

    $style = Styles::factory($theme_name)->get($layout['style_id']);
    $layout['style_name'] = empty($style['name']) ? '' : $style['name'];

    Registry::get('view')->assign('layout', $layout);

    foreach ($available_themes['installed'] as $theme_id => $theme) {
        $layouts_params = array(
            'theme_name' => $theme_id
        );

        $available_themes['installed'][$theme_id]['layouts'] = Layout::instance()->getList($layouts_params);

        if ($theme_id == $theme_name) {
            $available_themes['current']['layouts'] = $available_themes['installed'][$theme_id]['layouts'];
        }
    }

    Registry::get('view')->assign('available_themes', $available_themes);
    Registry::get('view')->assign('dev_modes', Development::get());

} elseif ($mode == 'styles') {
    if ($action == 'update_status') {
        $theme = Themes::factory(fn_get_theme_path('[theme]', 'C'));
        $theme_manifest = $theme->getManifest();

        if (empty($theme_manifest['converted_to_css'])) {
            Styles::factory(fn_get_theme_path('[theme]', 'C'))->setStyle($_REQUEST['id'], $_REQUEST['status']);

            // Delete compiled CSS file
            fn_clear_cache('statics');
        } else {
            $layout = Layout::instance(Registry::get('runtime.company_id'))->getDefault();
            fn_set_notification('E', __('error'), __('theme_editor.error_theme_converted_to_css', array(
                '[url]' => fn_url("customization.update_mode?type=theme_editor&status=enable&s_layout=$layout[layout_id]")
            )));
        }
    }

    return array(CONTROLLER_STATUS_OK, 'themes.manage');

} elseif ($mode == 'update_dev_mode') {
    if (!empty($_REQUEST['dev_mode'])) {

        if (!empty($_REQUEST['state'])) {
            Development::enable($_REQUEST['dev_mode']);
        } else {
            Development::disable($_REQUEST['dev_mode']);
        }

        if ($_REQUEST['dev_mode'] == 'compile_check') {
            if (!empty($_REQUEST['state'])) {
                fn_set_notification('W', __('warning'), __('warning_store_optimization_dev', array('[link]' => fn_url('themes.manage'))));
            } else {
                fn_set_notification('W', __('warning'), __('warning_store_optimization_dev_disabled', array('[link]' => fn_url('themes.manage?ctpl'))));
            }
        }
    }

    exit;
}
