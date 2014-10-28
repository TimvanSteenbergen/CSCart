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
use Tygh\Settings;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    fn_trusted_vars (
        'addon_data'
    );

    if ($mode == 'update') {
        if (isset($_REQUEST['addon_data'])) {
            fn_update_addon($_REQUEST['addon_data']);
        }

        return array(CONTROLLER_STATUS_OK, "addons.update?addon=" . $_REQUEST['addon']);

    } elseif ($mode == 'recheck') {
        $source = Registry::get('config.dir.cache_misc') . 'tmp/addon_pack/';
        $destination = Registry::get('config.dir.root');

        if ($action == 'ftp_upload') {
            $ftp_access = array(
                'hostname' => $_REQUEST['ftp_access']['ftp_hostname'],
                'username' => $_REQUEST['ftp_access']['ftp_username'],
                'password' => $_REQUEST['ftp_access']['ftp_password'],
                'directory' => $_REQUEST['ftp_access']['ftp_directory'],
            );

            $ftp_copy_result = fn_copy_by_ftp($source, $destination, $ftp_access);

            if ($ftp_copy_result === true) {
                $struct = fn_get_dir_contents($source, false, true, '', '', true);
                $addon_name = '';

                $relative_addon_path = str_replace(Registry::get('config.dir.root') . '/', '', Registry::get('config.dir.addons'));

                foreach ($struct as $file) {
                    if (preg_match('#' . $relative_addon_path . '[^a-zA-Z0-9_]*([a-zA-Z0-9_-]+).+?addon.xml$#i', $file, $matches)) {
                        if (!empty($matches[1])) {
                            $addon_name = $matches[1];
                            break;
                        }
                    }
                }

                fn_install_addon($addon_name);

            } else {
                fn_set_notification('E', __('error'), $ftp_copy_result);
            }

            if (defined('AJAX_REQUEST')) {
                Registry::get('ajax')->assign('non_ajax_notifications', true);
                Registry::get('ajax')->assign('force_redirection', fn_url('addons.manage'));

                exit();
            } else {
                return array(CONTROLLER_STATUS_OK, 'addons.manage');
            }
        }

        $non_writable_folders = fn_check_copy_ability($source, $destination);

        if (!empty($non_writable_folders)) {
            if (!empty($_REQUEST['ftp_access'])) {
                Registry::get('view')->assign('ftp_access', $_REQUEST['ftp_access']);
            }

            Registry::get('view')->assign('non_writable', $non_writable_folders);

            if (defined('AJAX_REQUEST')) {
                Registry::get('view')->display('views/addons/components/correct_permissions.tpl');

                exit();
            }

        } else {
            fn_addons_move_and_install($extract_path, Registry::get('config.dir.root'));

            if (defined('AJAX_REQUEST')) {
                Registry::get('ajax')->assign('force_redirection', fn_url('addons.manage'));

                exit();
            }
        }

    } elseif ($mode == 'upload') {
        if (defined('RESTRICTED_ADMIN') || Registry::get('runtime.company_id')) {
            fn_set_notification('E', __('error'), __('access_denied'));

            return array(CONTROLLER_STATUS_REDIRECT, 'addons.manage');
        }

        $addon_pack = fn_filter_uploaded_data('addon_pack', Registry::get('config.allowed_pack_exts'));

        if (empty($addon_pack[0])) {
            fn_set_notification('E', __('error'), __('text_allowed_to_upload_file_extension', array('[ext]' => implode(',', Registry::get('config.allowed_pack_exts')))));
        } else {
            // Extract the add-on pack and check the permissions
            $extract_path = Registry::get('config.dir.cache_misc') . 'tmp/addon_pack/';
            $addon_pack = $addon_pack[0];

            // Re-create source folder
            fn_rm($extract_path);
            fn_mkdir($extract_path);

            fn_copy($addon_pack['path'], $extract_path . $addon_pack['name']);

            if (fn_decompress_files($extract_path . $addon_pack['name'], $extract_path)) {
                fn_rm($extract_path . $addon_pack['name']);

                $struct = fn_get_dir_contents($extract_path, false, true, '', '', true);
                $addon_name = '';
                $relative_addon_path = str_replace(Registry::get('config.dir.root') . '/', '', Registry::get('config.dir.addons'));

                foreach ($struct as $file) {
                    if (preg_match('#' . $relative_addon_path . '[^a-zA-Z0-9_]*([a-zA-Z0-9_-]+).+?addon.xml$#i', $file, $matches)) {
                        if (!empty($matches[1])) {
                            $addon_name = $matches[1];
                        }
                    }
                }

                if (empty($addon_name)) {
                    fn_set_notification('E', __('error'), __('broken_addon_pack'));

                    if (defined('AJAX_REQUEST')) {
                        Registry::get('ajax')->assign('non_ajax_notifications', true);
                        Registry::get('ajax')->assign('force_redirection', fn_url('addons.manage'));

                        exit();
                    } else {
                        return array(CONTROLLER_STATUS_REDIRECT, 'addons.manage');
                    }
                }

                $non_writable_folders = fn_check_copy_ability($extract_path, Registry::get('config.dir.root'));

                if (!empty($non_writable_folders)) {
                    Registry::get('view')->assign('non_writable', $non_writable_folders);

                    if (defined('AJAX_REQUEST')) {
                        Registry::get('view')->display('views/addons/components/correct_permissions.tpl');

                        exit();
                    }

                } else {
                    fn_addons_move_and_install($extract_path, Registry::get('config.dir.root'));

                    if (defined('AJAX_REQUEST')) {
                        Registry::get('ajax')->assign('force_redirection', fn_url('addons.manage'));

                        exit();
                    }
                }
            }
        }

        if (defined('AJAX_REQUEST')) {
            Registry::get('view')->display('views/addons/components/upload_addon.tpl');

            exit();
        }
    }

    return array(CONTROLLER_STATUS_OK, "addons.manage");
}

if ($mode == 'update') {
    $addon_name = addslashes($_REQUEST['addon']);

    $section = Settings::instance()->getSectionByName($_REQUEST['addon'], Settings::ADDON_SECTION);

    if (empty($section)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    $subsections = Settings::instance()->getSectionTabs($section['section_id'], CART_LANGUAGE);
    $options = Settings::instance()->getList($section['section_id']);

    fn_update_lang_objects('sections', $subsections);
    fn_update_lang_objects('options', $options);

    Registry::get('view')->assign('options', $options);
    Registry::get('view')->assign('subsections', $subsections);

    $addon =  db_get_row(
        'SELECT a.addon, a.status, b.name as name, b.description as description, a.separate '
        . 'FROM ?:addons as a LEFT JOIN ?:addon_descriptions as b ON b.addon = a.addon AND b.lang_code = ?s WHERE a.addon = ?s'
        . 'ORDER BY b.name ASC',
        CART_LANGUAGE, $_REQUEST['addon']
    );

    if ($addon['separate'] == true || !defined('AJAX_REQUEST')) {
        Registry::get('view')->assign('separate', true);
        Registry::get('view')->assign('addon_name', $addon['name']);
    }

} elseif ($mode == 'install') {

    fn_install_addon($_REQUEST['addon']);

    return array(CONTROLLER_STATUS_OK, "addons.manage");

} elseif ($mode == 'uninstall') {

    fn_uninstall_addon($_REQUEST['addon']);

    return array(CONTROLLER_STATUS_OK, "addons.manage");

} elseif ($mode == 'update_status') {

    $is_snapshot_correct = fn_check_addon_snapshot($_REQUEST['id']);

    if (!$is_snapshot_correct) {
        $status = false;

    } else {
        $status = fn_update_addon_status($_REQUEST['id'], $_REQUEST['status']);
    }

    if ($status !== true) {
        Registry::get('ajax')->assign('return_status', $status);
    }

    exit;

} elseif ($mode == 'manage') {

    $params = $_REQUEST;
    $params['for_company'] = (bool) Registry::get('runtime.company_id');

    list($addons, $search) = fn_get_addons($params);

    Registry::get('view')->assign('search', $search);
    Registry::get('view')->assign('addons_list', $addons);
}
