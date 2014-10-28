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

use Tygh\Development;
use Tygh\Registry;
use Tygh\Settings;
use Tygh\Logger;
use Tygh\Addons\SchemesManager as AddonSchemesManager;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (in_array($mode, array('upgrade', 'revert'))) {
    // temporary set development mode, for full error displaying
    Development::enable('compile_check');
}

$custom_theme_files = array(
);

$skip_files = array(
    'manifest.json'
);

$backend_files = array(
    'admin_index' => 'admin.php',
    'vendor_index' => 'vendor.php',
);

$uc_settings = Settings::instance()->getValues('Upgrade_center');

// If we're performing the update, check if upgrade center override controller is exist in the package
if (!empty($_SESSION['uc_package']) && file_exists(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/uc_override.php')) {
    return include(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/uc_override.php');
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'update_settings') {
        if (!empty($_REQUEST['settings_data'])) {
            foreach ($_REQUEST['settings_data'] as $setting_name => $setting_value) {
                Settings::instance()->updateValue($setting_name, $setting_value, 'Upgrade_center');
            }
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT);
}

if ($mode == 'manage') {

    if (!fn_allowed_for('ULTIMATE:FULL:TRIAL')) {
        // Create directory structure
        fn_uc_create_structure();

        Registry::get('view')->assign('installed_upgrades', fn_uc_check_installed_upgrades());

        if (empty($uc_settings['license_number']) && !fn_allowed_for('ULTIMATE:FREE')) {
            Registry::get('view')->assign('require_license_number', true);
        } else {
            $check_file_hash = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.get_fh_code');
            $check_file_hash = base64_decode($check_file_hash);
            eval($check_file_hash);
        }

        Registry::get('view')->assign('uc_settings', $uc_settings);
    }

} elseif ($mode == 'refresh') {
    if (file_exists(Registry::get('config.dir.upgrade') . 'packages.xml') && false === fn_rm(Registry::get('config.dir.upgrade') . 'packages.xml')) {
        fn_set_notification('W', __('warning'), __('text_uc_unable_to_remove_packages_xml'));
    }
/*
    if (file_exists(Registry::get('config.dir.upgrade') . 'edition_packages.xml') && false === fn_rm(Registry::get('config.dir.upgrade') . 'edition_packages.xml')) {
        fn_set_notification('W', __('warning'), __('text_uc_unable_to_remove_packages_xml'));
    }
 */
    unset($_SESSION['uc_package']);
    unset($_SESSION['uc_base_package']);
    unset($_SESSION['uc_stages']);

    return array(CONTROLLER_STATUS_OK, !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "upgrade_center.manage");

} elseif ($mode == 'unlock') {

    $lock_file_path = Registry::get('config.dir.upgrade') . '.upgrade_lock';
    if (is_file($lock_file_path)) {
        @unlink($lock_file_path);
        if (is_file($lock_file_path)) {
            fn_set_notification('W', __('warning'), __('text_uc_unable_to_remove_upgrade_lock', array(
                '[file]' => $lock_file_path
            )));
        }
    }

    return array(CONTROLLER_STATUS_OK, "upgrade_center.refresh");

} elseif ($mode == 'get_upgrade') {

    $package = fn_uc_get_package_details($_REQUEST['package_id']);
    if (fn_uc_get_package($_REQUEST['package_id'], $_REQUEST['md5'], $package, $uc_settings, $backend_files) == true) {
        $_SESSION['uc_package'] = $package['file'];
        $suffix = '.check';
    } else {
        unset($_SESSION['uc_package']);
        $suffix = '.manage';
    }

    return array(CONTROLLER_STATUS_OK, "upgrade_center" . $suffix);

} elseif ($mode == 'check') {

    if (empty($_SESSION['uc_package']) && empty($_SESSION['uc_base_package'])) {
        fn_set_notification('E', __('error'), __('text_uc_upgrade_not_selected'));

        return array(CONTROLLER_STATUS_REDIRECT, "upgrade_center.manage");
    }

    $package_path = Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/package';

    fn_set_store_mode('closed'); // close the store
    if (fn_allowed_for('ULTIMATE')) {
        $company_ids = fn_get_all_companies_ids();
        foreach ($company_ids as $company_id) {
            fn_set_store_mode('closed', $company_id);
        }
    }

    if (!empty($_SESSION['uc_base_package']) && is_file(Registry::get('config.dir.upgrade') .  $_SESSION['uc_base_package'] . '/packages_info.xml') || !empty($_SESSION['uc_package']) && is_file(Registry::get('config.dir.upgrade') .  $_SESSION['uc_package'] . '/packages_info.xml')) {
        if (empty($_SESSION['uc_base_package'])) {
            $_SESSION['uc_base_package'] = $_SESSION['uc_package'];
        }
        $packages_info = simplexml_load_file(Registry::get('config.dir.upgrade') . $_SESSION['uc_base_package'] . '/packages_info.xml', NULL, LIBXML_NOERROR);

        if (is_file(Registry::get('config.dir.upgrade') . $_SESSION['uc_base_package'] . '/stages.php')) {
            include(Registry::get('config.dir.upgrade') . $_SESSION['uc_base_package'] . '/stages.php');
        } else {
            $stages = array();
        }

        $i = 0;
        foreach ($packages_info->item as $item) {
            $i++;
            $stage = (string) $item;
            if (!empty($stages[$stage]) && $stages[$stage] == 'done') {
                continue;
            } else {
                $_stages['total'] = (string) $packages_info['count'];
                $_stages['stage_number'] = $i;
                $_stages['stage'] = $stage;
                $_stages['installed'] = $stages;
                $_SESSION['uc_stages'] = $_stages;
                $_SESSION['uc_package'] = $_SESSION['uc_base_package'] . '/' . $stage;
                break;
            }
        }

        if (!empty($_stages)) {
            Registry::get('view')->assign('stages', $_stages);
        }
    }

    $xml = simplexml_load_file(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/uc.xml', NULL, LIBXML_NOERROR);

    if (empty($xml)) {
        fn_set_notification('E', __('error'), __('text_uc_unable_to_parse_uc_xml'));
    } else {
        $hash_table = array();
        $result = array(
            'non_writable' => array(),
            'changed' => array(),
            'new' => array()
        );

        // Get array with original files hashes
        if (isset($xml->original_files)) {
            foreach ($xml->original_files->item as $item) {
                $hash_table[(string) $item['file']] = (string) $item;
            }
        }

        fn_ftp_connect($uc_settings);

        fn_uc_run_continuous_job('fn_uc_create_themes', array($package_path, $_SESSION['uc_package'], $skip_files, $custom_theme_files));

        fn_uc_check_files($package_path, $hash_table, $result, $_SESSION['uc_package'], $custom_theme_files);
        fn_uc_check_file_access($result, $package_path, $_SESSION['uc_package'], true);
        if (!empty($result['non_writable']) && is_resource(Registry::get('ftp_connection'))) {
            fn_set_notification('E', __('error'), __('error_permissions_not_changed'));
        }

        fn_uc_check_database_priviliges($result);

        $udata = $data = array();
        if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
            include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
        }

        if (!empty($result['changed'])) {
            foreach ($result['changed'] as $f) {
                $data[$f] = false;
            }
        }

        $udata[$_SESSION['uc_package']]['files'] = $data;
        $udata[$_SESSION['uc_package']]['not_installed'] = true;
        fn_uc_update_installed_upgrades($udata);
    }

    Registry::get('view')->assign('check_results', $result);
    Registry::get('view')->assign('uc_settings', $uc_settings);

} elseif ($mode == 'run_backup') {

    if (empty($_SESSION['uc_package'])) {
        fn_set_notification('E', __('error'), __('text_uc_upgrade_not_selected'));

        return array(CONTROLLER_STATUS_REDIRECT, "upgrade_center.manage");
    }

    $backup_details = array(
        'files' => array(),
        'tables' => array()
    );

    fn_uc_backup_backend_files($backend_files, Registry::get('config.dir.upgrade') . $_SESSION['uc_package']);

    fn_uc_run_continuous_job('fn_uc_backup_files', array(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/package', Registry::get('config.dir.root'), &$backup_details['files'], $_SESSION['uc_package']));
    $obsolete_files = fn_uc_run_continuous_job('fn_uc_backup_obsolete_files', array(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/backup/', Registry::get('config.dir.root'), Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/uc.xml'));
    $backup_details['files'] = array_merge($backup_details['files'], $obsolete_files);
    sort($backup_details['files']);

    $backup_details['tables'] = fn_uc_run_continuous_job('fn_uc_backup_database', array(Registry::get('config.dir.upgrade') . $_SESSION['uc_package']));

    if ($backup_details['tables'] === false) {
        return array(CONTROLLER_STATUS_OK, "upgrade_center.check");
    }

    $udata = array();
    if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
        include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
    } else {
        fn_set_notification('W', __('warning'), __('text_uc_list_of_updates_missing'));
    }

    $udata[$_SESSION['uc_package']]['backup_details'] = $backup_details;
    $udata[$_SESSION['uc_package']]['not_installed'] = true;
    fn_uc_update_installed_upgrades($udata);

    /*fn_set_notification('W', __('warning'), 'Please refresh the page to clear browser cache if a blank page is loaded.');*/

    return array(CONTROLLER_STATUS_OK, "upgrade_center.backup?go=go");

} elseif ($mode == 'backup') {

    if (empty($_SESSION['uc_package'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "upgrade_center.manage");
    }

    if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
        include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
    } else {
        fn_set_notification('W', __('warning'), __('text_uc_list_of_updates_missing'));

        return array(CONTROLLER_STATUS_REDIRECT, "upgrade_center.check");
    }

    // Put data to emergency restore script
    $c = fn_get_contents(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/restore.php');

    $data = "\$uc_settings = " . var_export($uc_settings, true) . ";\n\n";
    $data .= "\$db = array (" .
        "'db_host' => '" . Registry::get('config.db_host') . "'," .
        "'db_user' => '" . Registry::get('config.db_user') . "'," .
        "'db_password' => '" . Registry::get('config.db_password') . "'," .
        "'db_name' => '" . Registry::get('config.db_name') . "'" .
        ");\n\n";
    $data .= ("\$multistage = " . (!empty($_SESSION['uc_base_package']) ? 'true' : 'false') . ";\n\n");

    $data .= ("\$dir_cache_templates = '" . Registry::get('config.dir.cache_templates') . "';\n\n");
    $data .= ("\$dir_cache_registry = '" . Registry::get('config.dir.cache_registry') . "';\n\n");
    $data .= ("\$dir_cache_misc = '" . Registry::get('config.dir.cache_misc') . "';\n\n");
    $data .= ("\$base_theme = '" . Registry::get('config.base_theme') . "';\n\n");

    $restore_key = md5(uniqid());
    $data .= "\$uak = '" . $restore_key . "';";

    $start = strpos($c, '//[params]') + strlen('//[params]') + 1;
    $end = strpos($c, '//[/params]') - 1;

    $c = substr_replace($c, $data, $start, $end - $start);
    fn_put_contents(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/restore.php', $c, '', 0644);

    Registry::get('view')->assign('restore_key', $restore_key);
    Registry::get('view')->assign('backup_details', $udata[$_SESSION['uc_package']]['backup_details']);

    if (!empty($_SESSION['uc_stages'])) {
        Registry::get('view')->assign('stages', $_SESSION['uc_stages']);
    }

} elseif ($mode == 'upgrade') {

    if (empty($_SESSION['uc_package'])) {
        return array(CONTROLLER_STATUS_REDIRECT, "upgrade_center.manage");
    }

    fn_ftp_connect($uc_settings);
    fn_uc_run_continuous_job('fn_uc_copy_files', array(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/package', Registry::get('config.dir.root')));
    fn_uc_run_continuous_job('fn_uc_rm_files', array(Registry::get('config.dir.root'), Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/uc.xml', 'deleted_files'));

    $dumps = array('db_scheme.sql', 'db_data.sql');
    $product_build = (defined('PRODUCT_BUILD') && PRODUCT_BUILD) ? PRODUCT_BUILD : 'en';
    $dumps[$product_build] = 'db_lang_' . $product_build . '.sql';

    $add_langs = array();
    $all_sqls = fn_get_dir_contents(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'], true, true, '.sql');
    foreach ($all_sqls as $sql) {
        preg_match('/db_lang_([a-z]{2})\.sql/S', $sql, $lang);
        if (!empty($lang[1])) {
            $add_langs[] = $lang[1];
        }
    }
    $langs = db_get_fields("SELECT lang_code FROM ?:languages");
    foreach ($add_langs as $add_lang) {
        if (in_array($add_lang, $langs)) {
            $dumps[$add_lang] = 'db_lang_' . $add_lang . '.sql';
        }
    }

    foreach ($dumps as $dump) {
        fn_uc_run_continuous_job('db_import_sql_file', array(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'] . '/' . $dump, 16384, true, 1, true, true, true));
        fn_uc_run_continuous_job('fn_uc_check_db_errors', array($dump));
    }

    fn_uc_run_continuous_job('fn_uc_post_upgrade', array(Registry::get('config.dir.upgrade') . $_SESSION['uc_package'], 'upgrade'));
    fn_uc_run_continuous_job('fn_uc_check_db_errors', array('post upgrade'));

    fn_uc_restore_settings();
    fn_uc_run_continuous_job('fn_uc_check_db_errors', array('settings'));

    fn_uc_run_continuous_job('fn_uc_cleanup_cache', array($_SESSION['uc_package'], 'upgrade', $dumps));

    $udata = array();
    if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
        include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
    }

    $udata[$_SESSION['uc_package']]['not_installed'] = false;
    if (!empty($_SESSION['uc_stages'])) {
        $udata[$_SESSION['uc_package']]['stage'] = $_SESSION['uc_stages']['stage'];
        $udata[$_SESSION['uc_package']]['base_package'] = $_SESSION['uc_base_package'];
    }

    fn_uc_update_installed_upgrades($udata);

    if (!empty($_SESSION['uc_stages'])) {
        $_SESSION['uc_stages']['installed'][$_SESSION['uc_stages']['stage']] = 'done';
        if (!fn_put_contents(Registry::get('config.dir.upgrade') . $_SESSION['uc_base_package'] . '/stages.php', "<?php\n if (!defined('BOOTSTRAP')) { die('Access denied'); }\n \$stages = " . var_export($_SESSION['uc_stages']['installed'], true) . ";\n?>")) {
            fn_set_notification('W', __('warning'), __('text_uc_unable_to_update_list_of_installed_upgrades'));
        }

        if ($_SESSION['uc_stages']['stage_number'] < $_SESSION['uc_stages']['total']) {
            $_SESSION['uc_package'] = $_SESSION['uc_base_package'];

            return array(CONTROLLER_STATUS_OK, 'upgrade_center.check');
        } else {
            unset($_SESSION['uc_package']);
            unset($_SESSION['uc_base_package']);
            unset($_SESSION['uc_stages']);

            return array(CONTROLLER_STATUS_OK, 'upgrade_center.summary');
        }
    } else {
        $package = $_SESSION['uc_package'];
        unset($_SESSION['uc_package']);

        return array(CONTROLLER_STATUS_OK, "upgrade_center.summary?package=" . $package);
    }

} elseif ($mode == 'revert') {

    fn_ftp_connect($uc_settings);
    fn_uc_run_continuous_job('fn_uc_copy_files', array(Registry::get('config.dir.upgrade') . $_REQUEST['package'] . '/backup', Registry::get('config.dir.root')));
    @fn_uc_rm(Registry::get('config.dir.root') . '/uc.sql');
    fn_uc_run_continuous_job('fn_uc_rm_files', array(Registry::get('config.dir.root'), Registry::get('config.dir.upgrade') . $_REQUEST['package'] . '/uc.xml', 'new_files'));

    fn_uc_run_continuous_job('db_import_sql_file', array(Registry::get('config.dir.upgrade') . $_REQUEST['package'] . '/backup/uc.sql', 16384, true, 1, true, false, true));
    fn_uc_run_continuous_job('fn_uc_post_upgrade', array(Registry::get('config.dir.upgrade') . $_REQUEST['package'], 'revert'));

    if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
        include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');

        if (isset($udata[$_REQUEST['package']])) {
            if (isset($udata[$_REQUEST['package']]['stage']) && isset($udata[$_REQUEST['package']]['base_package']) && is_file(Registry::get('config.dir.upgrade') . $udata[$_REQUEST['package']]['base_package'] . '/stages.php')) {
                include(Registry::get('config.dir.upgrade') . $udata[$_REQUEST['package']]['base_package'] . '/stages.php');
                if (!empty($stages)) {
                    unset($stages[$udata[$_REQUEST['package']]['stage']]);
                    if (!fn_put_contents(Registry::get('config.dir.upgrade') . $udata[$_REQUEST['package']]['base_package'] . '/stages.php', "<?php\n if (!defined('BOOTSTRAP')) { die('Access denied'); }\n \$stages = " . var_export($stages, true) . ";\n?>")) {
                        fn_set_notification('W', __('warning'), __('text_uc_unable_to_update_list_of_installed_upgrades'));
                    }
                }
            }
            unset($udata[$_REQUEST['package']]);
        }

        if (!empty($udata)) {
            fn_uc_update_installed_upgrades($udata);
        } else {
            fn_rm(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
        }
    } else {
        fn_set_notification('W', __('warning'), __('text_uc_list_of_updates_missing'));
    }

    fn_rm(Registry::get('config.dir.upgrade') . 'packages.xml'); // cleanup packages list
//    fn_rm(Registry::get('config.dir.upgrade') . 'edition_packages.xml'); // cleanup packages list
    fn_uc_run_continuous_job('fn_uc_cleanup_cache', array($_REQUEST['package'], 'revert'));

    fn_set_notification('W', __('important'), __('text_uc_upgrade_reverted'));

    return array(CONTROLLER_STATUS_OK, "upgrade_center.manage");


} elseif ($mode == 'summary') {

    $new_license = fn_uc_get_new_license_data();
    if (!empty($new_license['new_license']) && !empty($new_license['new_license_to']) && $new_license['new_license_to'] == PRODUCT_EDITION) {
        Settings::instance()->updateValue( 'license_number',  $new_license['new_license'], 'Upgrade_center');
        fn_uc_save_new_license_data(array('new_license_package' => '', 'new_license' => '', 'new_license_from' => '', 'new_license_to' => ''));
    }

    fn_rm(Registry::get('config.dir.upgrade') . 'packages.xml'); // cleanup packages list

    if (!empty($_SESSION['uc_upgrade_errors'])) {
        Registry::get('view')->assign('uc_upgrade_errors', true);
        unset($_SESSION['uc_upgrade_errors']);
    }

} elseif ($mode == 'installed_upgrades') {

    $udata = array();
    if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
        include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
    } else {
        fn_set_notification('W', __('warning'), __('text_uc_list_of_updates_missing'));
    }

    $packages = array();
    foreach ($udata as $pkg => $f) {
        if (!empty($f['not_installed'])) {
            continue;
        }

        $details = array();
        if (file_exists(Registry::get('config.dir.upgrade') . $pkg . '/package_details.php')) {
            $details = include(Registry::get('config.dir.upgrade') . $pkg . '/package_details.php');
        }
        $packages[$pkg] = array(
            'details' => $details,
            'files' => $f['files']
        );
    }

    if (empty($packages)) {
        return array(CONTROLLER_STATUS_REDIRECT, "upgrade_center.manage");
    }

    Registry::get('view')->assign('packages', $packages);

} elseif ($mode == 'diff') {

    Registry::get('view')->assign('diff', fn_text_diff(fn_get_contents(Registry::get('config.dir.upgrade') . $_REQUEST['package'] . '/backup/' . $_REQUEST['file']), fn_get_contents(Registry::get('config.dir.root') . '/' . $_REQUEST['file'])));

} elseif ($mode == 'conflicts') {

    if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
        include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');

        if (isset($udata[$_REQUEST['package']]['files'][$_REQUEST['file']])) {
            $udata[$_REQUEST['package']]['files'][$_REQUEST['file']] = ($action == 'mark') ? true : false;

            fn_uc_update_installed_upgrades($udata);
        }
    } else {
        fn_set_notification('W', __('warning'), __('text_uc_list_of_updates_missing'));
    }

    return array(CONTROLLER_STATUS_OK, "upgrade_center.installed_upgrades");

} elseif ($mode == 'remove') {

    if (!empty($_REQUEST['package'])) {
        if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
            include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
        } else {
            fn_set_notification('W', __('warning'), __('text_uc_list_of_updates_missing'));
        }

        $delete_dirs = array();
        foreach ($udata as $dir => $v) {
            $delete_dirs[] = $dir;
            if ($dir == $_REQUEST['package']) {
                break;
            }
        }

        if (!empty($delete_dirs)) {
            foreach ($delete_dirs as $dir) {
                fn_rm(Registry::get('config.dir.upgrade') . $dir, true);

                if (!empty($udata[$dir])) {
                    if (isset($udata[$dir]['base_package']) && !fn_get_dir_contents(Registry::get('config.dir.upgrade') . $udata[$dir]['base_package'], true, false)) {
                        fn_rm(Registry::get('config.dir.upgrade') . $udata[$dir]['base_package'], true);
                    }
                    unset($udata[$dir]);
                }
            }
        }

        if (!empty($udata)) {
            fn_uc_update_installed_upgrades($udata);
        } else {
            fn_rm(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');
        }
    }

    return array(CONTROLLER_STATUS_OK, "upgrade_center.installed_upgrades");
/*
} elseif ($mode == 'manage_editions') {

    if (!fn_allowed_for('ULTIMATE:FULL:TRIAL')) {
        // Create directory structure
        fn_uc_create_structure();

        Registry::get('view')->assign('installed_upgrades', fn_uc_check_installed_upgrades());

        if (empty($uc_settings['license_number'])) {
            Registry::get('view')->assign('require_license_number', true);
        } else {
            Registry::get('view')->assign('packages', fn_uc_get_edition_update_packages($uc_settings));
            $new_license_data = fn_uc_get_new_license_data();
            if (!empty($new_license_data['new_license_package']) && !is_dir(Registry::get('config.dir.upgrade') . $new_license_data['new_license_package'])) {
                unset($new_license_data['new_license_package']);
                fn_uc_save_new_license_data(array('new_license_package' => ''));
            }
            Registry::get('view')->assign('new_license_data', $new_license_data);
        }

        Registry::get('view')->assign('uc_settings', $uc_settings);
    }

} elseif ($mode == 'check_edition_license') {

    $new_license = !empty($_REQUEST['new_license']) ? $_REQUEST['new_license'] : '';
    $new_license_from = !empty($_REQUEST['new_license_from']) ? $_REQUEST['new_license_from'] : '';
    $new_license_to = !empty($_REQUEST['new_license_to']) ? $_REQUEST['new_license_to'] : '';

    if (!empty($new_license)) {
        $result = fn_uc_check_edition_license($new_license, $uc_settings);

        $xml = @simplexml_load_string($result);

        if (isset($xml->errors)) {
            foreach ($xml->errors->item as $error) {
                fn_set_notification('E', __('error'), (string) $error);
            }
        }

        $is_license_valid = (isset($xml->response->license) && (string) $xml->response->license == 'VALID');
        $is_license_expired = (isset($xml->response->license) && (string) $xml->response->license == 'EXPIRED');
        $is_package_available = (isset($xml->response->package) && (string) $xml->response->package == 'AVAILABLE');

        if ($is_license_valid || $is_license_expired) {
            fn_uc_save_new_license_data(array('new_license_package' => '', 'new_license' => $new_license, 'new_license_from' => $new_license_from, 'new_license_to' => $new_license_to));
        }

        if ($is_license_expired) {
            fn_set_notification('E', __('error'), __('update_period_expired', array(
                '[url]' => Registry::get('config.resources.updates_server')
            )));
        }

        if ($is_package_available) {
            $package = fn_uc_get_edition_package_details($_REQUEST['package_id']);

            $package['file'] = fn_strtolower('upgrade_' . PRODUCT_VERSION . '_' . PRODUCT_EDITION . '-' . PRODUCT_VERSION . '_' . $new_license_to . '.tgz');
            $package['to_version'] = PRODUCT_VERSION . '_' . $new_license_to;

          if (fn_uc_get_edition_package($new_license, $package, $uc_settings, $backend_files) == true) {
                $_SESSION['uc_package'] = $package['file'];
                fn_uc_save_new_license_data(array('new_license_package' => $package['file']));
                $suffix = '.check';
            } else {
                unset($_SESSION['uc_base_package']);
                unset($_SESSION['uc_package']);
                unset($_SESSION['uc_stages']);
                $suffix = '.manage_editions';
            }
        } else {
            $suffix = '.manage_editions';
        }
    } else {
        $suffix = '.manage_editions';
    }

    return array(CONTROLLER_STATUS_OK, "upgrade_center" . $suffix);
*/
}

/**
 * Check if some errors were saved in the Registry and display it
 *
 * @param string $label Filename of name of action, that was run before
 * @return boolean true, if there were errors, false otherwise
 */
function fn_uc_check_db_errors($label, $log)
{
    $errors = Registry::get('runtime.database.errors');
    if (!empty($errors)) {
        foreach ($errors as $error) {
            $error_text = $error['message'] . ': <code>'. $error['query'] . '</code>';
            $log->write("DB error in $label: $error_text");
            fn_set_notification('E', __('error_occurred') . ': ' . $label, $error_text);
            $_SESSION['uc_upgrade_errors'] = true;
        }

        Registry::set('runtime.database.errors', array());
    }

    return empty($errors);
}

function fn_uc_get_setting_obj_id($setting_name, $section_name = '')
{
    if (!empty($setting_name)) {
        if (!empty($section_name)) {
            $section_condition = db_quote(" AND ?:settings_sections.name = ?s", $section_name);
        } else {
            $section_condition = '';
        }

        return db_get_field(
            "SELECT object_id FROM ?:settings_objects "
            . "LEFT JOIN ?:settings_sections ON ?:settings_objects.section_id = ?:settings_sections.section_id "
            . "WHERE ?:settings_objects.name = ?s ?p",
            $setting_name,
            $section_condition
        );
    } else {
        return false;
    }
}

function fn_uc_update_setting_value($name, $value, $section_name, $company_id = null)
{
    $object_id = fn_uc_get_setting_obj_id($name, $section_name);
    if ($object_id) {
        fn_uc_update_setting_value_by_id($object_id, $value, $company_id);
    }
}

function fn_uc_update_setting_value_by_id($object_id, $value, $company_id = null)
{
    $table = 'settings_objects';
    $data = array(
        'object_id' => $object_id,
        'value' => $value,
    );
    if (fn_allowed_for('ULTIMATE') && !is_null($company_id)) {
        $table = 'settings_vendor_values';
    }

    db_replace_into($table, $data);
}

function fn_uc_update_addon_settings($addon_scheme) {
    $tabs = $addon_scheme->getSections();
    if (!empty($tabs)) {
        $addon_section_id = Settings::instance()->updateSection(array(
            'parent_id'    => 0,
            'edition_type' => $addon_scheme->getEditionType(),
            'name'         => $addon_scheme->getId(),
             'type'         => Settings::ADDON_SECTION,
        ));

        foreach ($tabs as $tab_index => $tab) {
            $section_tab_id = Settings::instance()->updateSection(array(
                'parent_id'    => $addon_section_id,
                'edition_type' => $tab['edition_type'],
                'name'         => $tab['id'],
                'position'     => $tab_index * 10,
                 'type'         => isset($tab['separate']) ? Settings::SEPARATE_TAB_SECTION : Settings::TAB_SECTION,
            ));

            if (!empty($section_tab_id)) {
                fn_update_addon_settings_descriptions($section_tab_id, Settings::SECTION_DESCRIPTION, $tab['translations']);
                $settings = $addon_scheme->getSettings($tab['id']);

                foreach ($settings as $k => $setting) {
                    if (!empty($setting['id'])) {
                        if (!empty($setting['parent_id'])) {
                            $setting['parent_id'] = Settings::instance()->getId($setting['parent_id'], $addon_scheme->getId());
                        }

                        $setting_id = Settings::instance()->update(array(
                            'name' =>           $setting['id'],
                            'section_id' =>     $addon_section_id,
                            'section_tab_id' => $section_tab_id,
                            'type' =>           $setting['type'],
                            'position' =>       isset($setting['position']) ? $setting['position'] : $k * 10,
                            'edition_type' =>   $setting['edition_type'],
                            'is_global' =>      'N',
                            'handler' =>        $setting['handler'],
                            'parent_id' =>      intval($setting['parent_id'])
                        ));

                        if (!empty($setting_id)) {
                            fn_uc_update_setting_value($setting['id'], $setting['default_value'], $tab['id']);

                            fn_update_addon_settings_descriptions($setting_id, Settings::SETTING_DESCRIPTION, $setting['translations']);

                            if (isset($setting['variants'])) {
                                foreach ($setting['variants'] as $variant_k => $variant) {
                                    $variant_id = Settings::instance()->updateVariant(array(
                                        'object_id'  => $setting_id,
                                        'name'       => $variant['id'],
                                         'position'   => isset($variant['position']) ? $variant['position'] : $variant_k * 10,
                                    ));

                                    if (!empty($variant_id)) {
                                        fn_update_addon_settings_descriptions($variant_id, Settings::VARIANT_DESCRIPTION, $variant['translations']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}

function fn_uc_restore_settings($new_edition = '')
{
    $was_settings_backed_up = db_get_field("SHOW TABLES LIKE '?:settings_objects_upg'");

    if (!empty($was_settings_backed_up)) {
        $addons = db_get_fields('SELECT addon FROM ?:addons');
        $languages = db_get_fields("SELECT lang_code FROM ?:languages");

        if (!empty($new_edition)) {
            Settings::instance()->setNewEdition($new_edition);
        }

        foreach ($addons as $addon) {
            $addon_scheme = AddonSchemesManager::getScheme($addon);
            if (!empty($addon_scheme)) {
                fn_uc_update_addon_settings($addon_scheme);
                if ($original = $addon_scheme->getOriginals()) {
                    db_query("REPLACE INTO ?:original_values ?e", array(
                        'msgctxt' => 'Addon:' . $addon,
                        'msgid' => $original['name']
                    ));

                    db_query("REPLACE INTO ?:original_values ?e", array(
                        'msgctxt' => 'AddonDescription:' . $addon,
                        'msgid' => $original['description']
                    ));
                }

                $language_variables = $addon_scheme->getLanguageValues(true);
                if (!empty($language_variables)) {
                    db_query('REPLACE INTO ?:original_values ?m', $language_variables);
                }

//                $addon_scheme->installLanguageValues();
//                foreach ($languages as $lang_code) {
//                    $description = $addon_scheme->getDescription($lang_code);
//                    $addon_name = $addon_scheme->getName($lang_code);
//                    db_query("UPDATE ?:addon_descriptions SET description = ?s, name = ?s WHERE addon = ?s AND lang_code = ?s", $description, $addon_name, $addon, $lang_code);
//                }
            }
        }

        $settings = db_get_array('SELECT * FROM ?:settings_objects_upg');
        foreach ($settings as $setting) {
            fn_uc_update_setting_value($setting['name'], $setting['value'], $setting['section_name']);
        }
        db_query('DROP TABLE ?:settings_objects_upg');

        $was_company_settings_backed_up = db_get_field("SHOW TABLES LIKE '?:settings_vendor_values_upg'");
        if (!empty($was_company_settings_backed_up)) {
            $company_settings = db_get_array('SELECT * FROM ?:settings_vendor_values_upg');
            foreach ($company_settings as $setting) {
                fn_uc_update_setting_value($setting['name'], $setting['value'], $setting['section_name'], $setting['company_id']);
            }
            db_query('DROP TABLE ?:settings_vendor_values_upg');
        }
    }

    return true;
}

function fn_uc_get_package_contents_from_uc_xml($path)
{
    $contents = array();

    $xml = simplexml_load_file($path . '/uc.xml', NULL, LIBXML_NOERROR);

    if (!empty($xml)) {
        if (isset($xml->original_files)) {
            foreach ($xml->original_files->item as $item) {
                $contents[] = (string) $item['file'];
            }
        }

        if (isset($xml->new_files)) {
            foreach ($xml->new_files->item as $item) {
                $contents[] = (string) $item['file'];
            }
        }
    }

    return $contents;
}

function fn_uc_get_new_license_data()
{
    $settings = Settings::instance()->getValues('Upgrade_center');

    return $settings;
}

function fn_uc_save_new_license_data($new_license_data)
{
    foreach ($new_license_data as $setting_name => $setting_value) {
        if (Settings::instance()->isExists($setting_name, 'Upgrade_center')) {
            Settings::instance()->updateValue($setting_name, $setting_value, 'Upgrade_center');
        } else {
            $section_data = Settings::instance()->getSectionByName('Upgrade_center');
            Settings::instance()->update(
                array(
                    'name' => $setting_name,
                    'value' => $setting_value,
                    'section_id' => $section_data['section_id']
                )
            );
        }
    }
}

function fn_uc_check_edition_license($new_license, $uc_settings)
{
    $data = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.check_edition&ver=' . PRODUCT_VERSION . '&new_license_number=' . $new_license . '&license_number=' . $uc_settings['license_number']);

    return $data;
}

function fn_uc_check_database_priviliges(&$result)
{
    return true;
    $result['no_db_rights'] = false;

    $table = '?:mysql_priviliges_test';
    $table_exists = db_get_row("SHOW TABLES LIKE '$table'");

    $skip_errors = Registry::get('runtime.database.skip_errors');
    Registry::set('runtime.database.skip_errors', true);

    if ($table_exists) {
        db_query("DROP TABLE $table");
        $drop_check = db_get_row("SHOW TABLES LIKE '$table'");
        if ($drop_check) {
            $result['no_db_rights']['drop'] = 'DROP';
            Registry::set('runtime.database.skip_errors', $skip_errors);

            return false;
        }
    }

    db_query("CREATE TABLE $table (`test` INT NOT NULL) ENGINE = MYISAM");
    $table_exists = db_get_row("SHOW TABLES LIKE '$table'");
    if (!$table_exists) {
        $result['no_db_rights']['create'] = 'CREATE';
        Registry::set('runtime.database.skip_errors', $skip_errors);

        return false;
    }

    db_query("ALTER TABLE $table CHANGE `test` `test1` INT( 11 ) NOT NULL");
    $column_changed = db_get_row("SHOW COLUMNS FROM $table WHERE field LIKE 'test1'");
    if (!$column_changed) {
        $result['no_db_rights']['alter'] = 'ALTER';
        Registry::set('runtime.database.skip_errors', $skip_errors);

        return false;
    }

    db_query("DROP TABLE $table");
    if (!isset($drop_check)) {
        $table_exists = db_get_row("SHOW TABLES LIKE '$table'");
        if ($table_exists) {
            $result['no_db_rights']['drop'] = 'DROP';
            Registry::set('runtime.database.skip_errors', $skip_errors);

            return false;
        }
    }

    Registry::set('runtime.database.skip_errors', $skip_errors);

    return true;
}

function fn_uc_rename_backend_files($backend_files, $dir)
{
    foreach ($backend_files as $key => $backend) {
        $new_name = Registry::get("config.$key");
        if (is_file($dir . $backend) && !empty($new_name)) {
            fn_rename($dir . $backend, $dir . $new_name);
        }
    }
}

function fn_uc_backup_backend_files($backend_files, $dir)
{
    foreach ($backend_files as $key => $backend) {
        if (is_file($dir . '/package/' . $backend) && is_file(Registry::get('config.dir.root') . '/' . $backend)) {
            fn_uc_copy(Registry::get('config.dir.root') . "/$backend", $dir . "/backup/$backend");
        }
    }
}

/**
 * Get upgrade packages list
 *
 * @param array $uc_settings Upgrade center settings
 * @return array packages list
 */
function fn_uc_get_packages($uc_settings, $data)
{
    $result = array();

    if (!empty($data)) {
        $xml = simplexml_load_string($data, NULL, LIBXML_NOERROR);
        if (!empty($xml)) {
            // Get array with original files hashes
            if (isset($xml->packages)) {
                foreach ($xml->packages->item as $package) {

                    $c = array();
                    if (isset($package->contents)) {
                        foreach ($package->contents->item as $item) {
                            $c[] = str_replace('package/', '', (string) $item);
                        }
                    }

                    $result[] = array(
                        'md5' => (string) $package->file['md5'],
                        'package_id' => (string) $package['id'],
                        'file' => (string) $package->file,
                        'name' => (string) $package->name,
                        'timestamp' => (string) $package->timestamp,
                        'description' => (string) $package->description,
                        'from_version' => (string) $package->from_version,
                        'to_version' => (string) $package->to_version,
                        'size' => (string) $package->size,
                        'is_avail' => (string) $package->is_avail,
                        'purchase_time_limit' => (string) $package->purchase_time_limit,
                        'contents' => $c
                    );
                }
            }

            if (isset($xml->errors)) {
                foreach ($xml->errors->item as $error) {
                    fn_set_notification('E', __('error'), (string) $error);
                }
                fn_rm(Registry::get('config.dir.upgrade') . 'packages.xml'); // if we have errors, do not cache server response
            }
        }
    }

    return $result;
}

function fn_uc_get_edition_update_packages($uc_settings)
{
    $result = array();

    // Cache packages list
    if (!file_exists(Registry::get('config.dir.upgrade') . 'edition_packages.xml') || filemtime(Registry::get('config.dir.upgrade') . 'edition_packages.xml') < (TIME - 60 * 60 * 24)) {
        $data = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.get_editions&ver=' . PRODUCT_VERSION . '&license_number=' . $uc_settings['license_number']);
        fn_put_contents(Registry::get('config.dir.upgrade') . 'edition_packages.xml', $data);
    } else {
        $data = fn_get_contents(Registry::get('config.dir.upgrade') . 'edition_packages.xml');
    }

    if (!empty($data)) {
        $xml = simplexml_load_string($data, NULL, LIBXML_NOERROR);
        if (!empty($xml)) {
            // Get array with original files hashes
            if (isset($xml->packages)) {
                foreach ($xml->packages->item as $package) {

                    $c = array();
                    if (isset($package->contents)) {
                        foreach ($package->contents->item as $item) {
                            $c[] = str_replace('package/', '', (string) $item);
                        }
                    }

                    $result[] = array(
                        'package_id' => (string) $package['id'],
                        'name' => (string) $package->name,
                        'timestamp' => (string) $package->timestamp,
                        'size' => (string) $package->size,
                        'description' => (string) $package->description,
                        'from_version' => (string) $package->from_version,
                        'to_version' => (string) $package->to_version,
                        'from_edition' => (string) $package->from_edition,
                        'to_edition' => (string) $package->to_edition,
                        'is_avail' => (string) $package->is_avail,
                        'purchase_time_limit' => (string) $package->purchase_time_limit,
                    );
                }
            }

            if (isset($xml->errors)) {
                foreach ($xml->errors->item as $error) {
                    fn_set_notification('E', __('error'), (string) $error);
                }
                fn_rm(Registry::get('config.dir.upgrade') . 'edition_packages.xml'); // if we have errors, do not cache server response
            }
        }
    }

    return $result;
}

/**
 * Get upgrade package details
 *
 * @param int $package_id package ID
 * @return array package details
 */
function fn_uc_get_package_details($package_id)
{
    $result = array();

    $data = fn_get_contents(Registry::get('config.dir.upgrade') . 'packages.xml');
    if (!empty($data)) {
        $xml = simplexml_load_string($data, NULL, LIBXML_NOERROR);
        if (!empty($xml)) {
            // Get array with original files hashes
            if (isset($xml->packages)) {
                foreach ($xml->packages->item as $p) {
                    if ((string) $p['id'] == $package_id) {
                        $result = array(
                            'md5' => (string) $p->file['md5'],
                            'package_id' => (string) $p['id'],
                            'file' => (string) $p->file,
                            'name' => (string) $p->name,
                            'description' => (string) $p->description,
                            'timestamp' => (string) $p->timestamp,
                            'size' => (string) $p->size,
                            'is_avail' => (string) $p->is_avail,
                            'purchase_time_limit' => (string) $p->purchase_time_limit,
                            'from_version' => (string) $p->from_version,
                            'to_version' => (string) $p->to_version,
                        );

                        if (isset($p->contents)) {
                            foreach ($p->contents->item as $item) {
                                $result['contents'][] = (string) $item;
                            }
                        }

                        break;
                    }
                }
            }
        }
    }

    return $result;
}

function fn_uc_get_edition_package_details($package_id)
{
    $result = array();

    $data = fn_get_contents(Registry::get('config.dir.upgrade') . 'edition_packages.xml');
    if (!empty($data)) {
        $xml = simplexml_load_string($data, NULL, LIBXML_NOERROR);
        if (!empty($xml)) {
            // Get array with original files hashes
            if (isset($xml->packages)) {
                foreach ($xml->packages->item as $p) {
                    if ((string) $p['id'] == $package_id) {
                        $result = array(
                            'md5' => (string) $p->md5,
                            'package_id' => (string) $p['id'],
                            'name' => (string) $p->name,
                            'description' => (string) $p->description,
                            'timestamp' => (string) $p->timestamp,
                            'size' => (string) $p->size,
                            'from_version' => (string) $p->from_version,
                            'to_version' => (string) $p->to_version,
                            'from_edition' => (string) $p->from_edition,
                            'to_edition' => (string) $p->to_edition,
                        );

                        break;
                    }
                }
            }
        }
    }

    return $result;
}

/**
 * Get upgrade package
 *
 * @param int $package_id package ID
 * @param string $md5 md5 hash of package
 * @param array $package package details
 * @param array $uc_settings Upgrade center settings
 * @return boolean true if package downloaded and extracted successfully, false - otherwise
 */
function fn_uc_get_package($package_id, $md5, $package, $uc_settings, $backend_files)
{
    $result = true;

    if ($package['is_avail'] != 'Y') {
        fn_set_notification('E', __('error'), __('text_uc_package_not_available'));

        return false;
    }

    $data = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.get_package&package_id=' . $package_id . '&edition=' . PRODUCT_EDITION . '&license_number=' . $uc_settings['license_number']);
    if (!empty($data)) {

        fn_put_contents(Registry::get('config.dir.upgrade') . 'uc.tgz', $data);

        if (md5_file(Registry::get('config.dir.upgrade') . 'uc.tgz') == $md5) {
            $dir = fn_basename($package['file']);
            fn_mkdir(Registry::get('config.dir.upgrade') . $dir);
            fn_put_contents(Registry::get('config.dir.upgrade') . $dir . '/package_details.php', "<?php\n return " . var_export($package, true) . "; \n?>");

            $res = fn_decompress_files(Registry::get('config.dir.upgrade') . 'uc.tgz', Registry::get('config.dir.upgrade') . $dir);

            if ($res) {
                fn_uc_rename_backend_files($backend_files, Registry::get('config.dir.upgrade') . $dir . '/package/');
            } else {
                fn_set_notification('E', __('error'), __('text_uc_failed_to_decompress_files'));
            }

            return $res;
        } else {
            fn_set_notification('E', __('error'), __('text_uc_broken_package'));
            $result = false;
        }
    } else {
        fn_set_notification('E', __('error'), __('text_uc_cant_download_package'));
        $result = false;
    }

    return $result;
}

function fn_uc_get_edition_package($new_license, $package, $uc_settings, $backend_files)
{
    $result = true;

    $data = fn_get_contents(Registry::get('config.resources.updates_server') . '/index.php?dispatch=product_updates.get_edition_upgrade&license_number=' . $uc_settings['license_number'] . '&new_license_number=' . $new_license . '&ver=' . PRODUCT_VERSION);

    if (!empty($data)) {

        fn_put_contents(Registry::get('config.dir.upgrade') . 'uc.tgz', $data);

        if (md5_file(Registry::get('config.dir.upgrade') . 'uc.tgz') == $package['md5']) {
            $dir = fn_basename($package['file']);
            fn_mkdir(Registry::get('config.dir.upgrade') . $dir);

            $result = fn_decompress_files(Registry::get('config.dir.upgrade') . 'uc.tgz', Registry::get('config.dir.upgrade') . $dir);

            if ($result) {
                if (is_file(Registry::get('config.dir.upgrade') . $dir . '/packages_info.xml')) {
                    // if multipackage archive
                    $packages = simplexml_load_file(Registry::get('config.dir.upgrade') . $dir . '/packages_info.xml', NULL, LIBXML_NOERROR);
                    foreach ($packages->item as $item) {
                        $filename = (string) $item;
                        fn_uc_rename_backend_files($backend_files, Registry::get('config.dir.upgrade') . "$dir/$filename/package/");
                        $_package = $package;
                        $_package = array(
                            'to_version' => substr($filename, strrpos($filename, '-') + 1),
                            'name' => str_replace('upgrade_', '', $filename),
                            'description' => '',
                            'size' => '',
                        );
                        $_package['contents'] = fn_uc_get_package_contents_from_uc_xml(Registry::get('config.dir.upgrade') . "$dir/$filename");
                        fn_put_contents(Registry::get('config.dir.upgrade') . "$dir/$filename/package_details.php", "<?php\n return " . var_export($_package, true) . "; \n?>");
                    }
                } else {
                    fn_uc_rename_backend_files($backend_files, Registry::get('config.dir.upgrade') . $dir . '/package/');
                    $package['contents'] = fn_uc_get_package_contents_from_uc_xml(Registry::get('config.dir.upgrade') . $dir);
                    fn_put_contents(Registry::get('config.dir.upgrade') . $dir . '/package_details.php', "<?php\n return " . var_export($package, true) . "; \n?>");
                }
            } else {
                fn_set_notification('E', __('error'), __('text_uc_failed_to_decompress_files'));
            }

        } else {
            fn_set_notification('E', __('error'), __('text_uc_broken_package'));
            $result = false;
        }

        return $result;
    } else {
        fn_set_notification('E', __('error'), __('text_uc_cant_download_package'));
        $result = false;
    }

    return $result;
}

function fn_uc_check_file_access(&$result, $path, $package, $correct_permissions)
{
    if (is_file($path)) {
        $original_file = str_replace(Registry::get('config.dir.upgrade') . $package . '/package/', Registry::get('config.dir.root') . '/', $path);

        $relative_file = str_replace(Registry::get('config.dir.root') . '/', '', $original_file);
        if (!is_writable($original_file) && $correct_permissions) {
            @chmod($original_file, 0777);
        }
        if (!is_writable($original_file) && $correct_permissions) {
            fn_ftp_chmod_file($original_file);
        }
        if (is_file($original_file) && !is_writable($original_file)) {
            $result['non_writable'][] = $relative_file;
        }
    }

    if (is_dir($path)) {
        foreach (scandir($path) as $subfile) {
            $skip_files = array('.', '..');
            if (!in_array($subfile, $skip_files)) {
                fn_uc_check_file_access($result, $path . '/' . $subfile, $package, $correct_permissions);
            }
        }
    }

    return true;
}

/**
 * Check if files can be upgraded
 *
 * @param string $path files path
 * @param array $hash_table table with hashes of original files
 * @param array $result resulting array
 * @param string $package package to check files from
 * @param array $custom_theme_files list of custom theme files
 * @return boolean always true
 */
function fn_uc_check_files($path, $hash_table, &$result, $package, $custom_theme_files)
{
    // Simple copy for a file
    if (is_file($path)) {
        // Get original file name
        $original_file = str_replace(Registry::get('config.dir.upgrade') . $package . '/package/', Registry::get('config.dir.root') . '/', $path);
        $relative_file = str_replace(Registry::get('config.dir.root') . '/', '', $original_file);
        $file_name = fn_basename($original_file);

        if (file_exists($original_file)) {
            if (md5_file($original_file) != md5_file($path)) {

                $_relative_file = $relative_file;
                // For themes, convert relative path to themes_repository
                if (strpos($relative_file, 'design/themes/') === 0) {
                    $_relative_file = str_replace('design/themes/', 'var/themes_repository/', $relative_file);

                    // replace all themes except base
                    if (fn_uc_check_array_value($relative_file, $custom_theme_files) && strpos($relative_file, '/' . Registry::get('config.base_theme') . '/') === false) {
                        $_relative_file = preg_replace('!design/\themes/([\w]+)/!S', 'var/themes_repository/${1}/', $relative_file);
                    }
                }

                if (!empty($hash_table[$_relative_file])) {
                    if (md5_file($original_file) != $hash_table[$_relative_file]) {
                        $result['changed'][] = $relative_file;
                    }
                } else {
                    $result['changed'][] = $relative_file;
                }
            }
        } else {
            $result['new'][] = $relative_file;
        }

        return true;
    }

    if (is_dir($path)) {
        $dir = dir($path);
        while (false !== ($entry = $dir->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            fn_uc_check_files(rtrim($path, '/') . '/' . $entry, $hash_table, $result, $package, $custom_theme_files);
        }
        // Clean up
        $dir->close();

        return true;
    } else {
        fn_set_notification('E', __('error'), __('text_uc_incorrect_upgrade_path'));

        return false;
    }
}

/**
 * Create directory taking into account accessibility via php/ftp
 *
 * @param string $dir directory
 * @return boolean true if directory created successfully, false - otherwise
 */
function fn_uc_mkdir($dir)
{
    $result = true;
    fn_mkdir($dir);

    if (!is_dir($dir)) {
        fn_uc_ftp_mkdir($dir);
    }
    if (!is_dir($dir)) {
        fn_set_notification('E', __('error'), __('text_uc_failed_to_create_directory'));
        $result = false;
    }

    return $result;
}

/**
 * Copy file taking into account accessibility via php/ftp
 *
 * @param string $source source file
 * @param string $dest destination file/directory
 * @return boolean true if directory copied correctly, false - otherwise
 */
function fn_uc_copy($source, $dest)
{
    $result = false;
    $file_name = fn_basename($source);

    if (!file_exists($dest)) {
        if (fn_basename($dest) == $file_name) { // if we're copying the file, create parent directory
            fn_uc_mkdir(dirname($dest));
        } else {
            fn_uc_mkdir($dest);
        }
    }

    fn_echo(' .');

    if (is_writable($dest) || (is_writable(dirname($dest)) && !file_exists($dest))) {
        if (is_dir($dest)) {
            $dest .= '/' . fn_basename($source);
        }
        $result = copy($source, $dest);
        fn_uc_chmod_file($dest);
    }

    if (!$result && is_resource(Registry::get('ftp_connection'))) { // try ftp
        $result = fn_uc_ftp_copy($source, $dest);
    }

    if (!$result) {
        fn_set_notification('E', __('error'), __('cannot_write_file', array(
            '[file]' => $dest
        )));
    }

    return $result;
}

function fn_uc_chmod_file($filename)
{
    $ext = fn_get_file_ext($filename);
    $perm = ($ext == 'php' ? 0644 : DEFAULT_FILE_PERMISSIONS);

    $result = @chmod($filename, $perm);

    if (!$result) {
        $ftp = Registry::get('ftp_connection');
        if (is_resource($ftp)) {
            $dest = dirname($filename);
            $dest = rtrim($dest, '/') . '/'; // force adding trailing slash to path

            $rel_path = str_replace(Registry::get('config.dir.root') . '/', '', $dest);
            $cdir = ftp_pwd($ftp);

            if (empty($rel_path)) { // if rel_path is empty, assume it's root directory
                $rel_path = $cdir;
            }

            if (ftp_chdir($ftp, $rel_path)) {
                $result = @ftp_site($ftp, "CHMOD " . sprintf('0%o', $perm) . " " . fn_basename($filename));
                ftp_chdir($ftp, $cdir);
            }
        }
    }

    return $result;
}

/**
 * Copy files from one directory to another
 *
 * @param string $source source directory
 * @param string $dest destination directory
 * @return boolean true if directory copied correctly, false - otherwise
 */
function fn_uc_copy_files($source, $dest)
{
    // Simple copy for a file
    if (is_file($source)) {
        return fn_uc_copy($source, $dest);
    }

    // Loop through the folder
    if (is_dir($source)) {
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }

            // Deep copy directories
            if ($dest !== $source . '/' . $entry) {
                if (fn_uc_copy_files(rtrim($source, '/') . '/' . $entry, $dest . '/' . $entry) == false) {
                    return false;
                }
            }
        }

        // Clean up
        $dir->close();

        return true;
    } else {
        fn_set_notification('E', __('error'), __('cannot_write_file', array(
            '[file]' => $dest
        )));

        return false;
    }
}

/**
 * Run post-upgrade script
 *
 * @param string $path directory with post-upgrade script
 * @param string $upgrade_type script execution type - "upgrade" or "revert"
 * @return boolean always true
 */
function fn_uc_post_upgrade($path, $upgrade_type)
{
    if (file_exists($path . '/uc.php')) {
        include($path . '/uc.php');
    }

    return true;
}

/**
 * Create directory structure for upgrade
 *
 * @return boolean true if structured created correctly, false - otherwise
 */
function fn_uc_create_structure()
{
    if (fn_mkdir(Registry::get('config.dir.upgrade'))) {
        return true;
    } else {
        fn_set_notification('E', __('error'), __('text_uc_unable_to_create_upgrade_folder'));

        return false;
    }
}

function fn_uc_get_parent_theme($theme_name, $package)
{
    $result = 'basic';
    if (!empty($theme_name)) {
        $manifest_content = fn_get_contents(Registry::get('config.dir.design_frontend') . $theme_name . '/' . THEME_MANIFEST);
        if (!empty($manifest_content)) {
            $manifest = json_decode($manifest_content, true);
            if (isset($manifest['parent_theme'])) {
                //FIXME: Workaround for 415. Parent theme in responsive theme was empty in this version. Also we should check that base theme exists in the themes_repository
                $result = ($manifest['parent_theme'] == "") ? 'responsive' : $manifest['parent_theme'];
            }
        }
    }

    return $result;
}

/**
 * Create directory structure for current active themes and copy templates there
 *
 * @param string $path path with themes repository
 * @param string $package package to create themes structure in
 * @param array $skip_files list of files that should not be copied to installed themes
 * @param array $custom_theme_files list of custom theme files
 * @return boolean true if structured created correctly, false - otherwise
 */
function fn_uc_create_themes($path, $package, $skip_files, $custom_theme_files, $log)
{
    static $themes_paths = array();
    static $installed_themes = array();

    if (empty($themes_paths)) {
        $themes_paths = fn_get_theme_path('[relative]', 'C');
    }

    if (empty($installed_themes)) {
        $installed_themes = fn_get_dir_contents(Registry::get('config.dir.root') . '/' . $themes_paths, true, false);
    }

    if (is_file($path)) {
        $files = array();
        //Found theme file
        if (strpos($path, '/var/themes_repository/')) {
            foreach ($installed_themes as $installed_theme) {
                $base_theme = fn_uc_get_parent_theme($installed_theme, $package);
                if (strpos($path, '/' . $base_theme . '/')) {
                    $files[] = str_replace(Registry::get('config.dir.upgrade') . $package . '/package/var/themes_repository/' . $base_theme, Registry::get('config.dir.upgrade') . $package . '/package/design/themes/' . $installed_theme . '/', $path);
                }
            }
        }
        foreach ($files as $file) {
            $fname = fn_basename($file);
            if (!in_array($fname, $skip_files) && !(file_exists($file))) {
                fn_mkdir(dirname($file));
                $log->write('Copying file: ' . $file, __FILE__, __LINE__);
                fn_copy($path, dirname($file));
            }
        }

        return true;
    }

    if (is_dir($path)) {
        $dir = dir($path);
        while (false !== ($entry = $dir->read())) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            fn_uc_create_themes(rtrim($path, '/') . '/' . $entry, $package, $skip_files, $custom_theme_files, $log);
        }
        // Clean up
        $dir->close();

        return true;
    } else {
        fn_set_notification('E', __('error'), __('text_uc_failed_to_create_themes'));

        return false;
    }
}

/**
 * Check if file is writable using ftp
 *
 * @param string $path file path
 * @return boolean true if file is writable, false - otherwise
 */
function fn_uc_ftp_is_writable($path)
{
    $result = false;
    // If ftp connection is available, check file/directory via ftp
    $ftp = Registry::get('ftp_connection');
    if (is_resource($ftp)) {
        $rel_path = ltrim(str_replace(Registry::get('config.dir.root'), '', $path), '/');
        if (empty($rel_path)) {
            $rel_path = '.';
        }
        $ftp_path = (is_dir($path) || is_file($path)) ?  $rel_path : (dirname($rel_path));
        if (is_file($path)) {
            $perm = (fn_get_file_ext($path) == 'php') ? 0644 : DEFAULT_FILE_PERMISSIONS;
        } else {
            $perm = DEFAULT_DIR_PERMISSIONS;
        }
        $ftp_site_result = @ftp_site($ftp, 'CHMOD ' . sprintf('0%o', $perm) . ' ' . $ftp_path);
        if ($ftp_site_result) {
            $result = true;
        }
    }

    return $result;
}

/**
 * Copy file using ftp
 *
 * @param string $source source file
 * @param string $dest destination file/directory
 * @return boolean true if copied successfully, false - otherwise
 */
function fn_uc_ftp_copy($source, $dest)
{
    $result = false;

    $ftp = Registry::get('ftp_connection');
    if (is_resource($ftp)) {
        if (!is_dir($dest)) { // file
            $dest = dirname($dest);
        }
        $dest = rtrim($dest, '/') . '/'; // force adding trailing slash to path

        $rel_path = str_replace(Registry::get('config.dir.root') . '/', '', $dest);
        $cdir = ftp_pwd($ftp);

        if (empty($rel_path)) { // if rel_path is empty, assume it's root directory
            $rel_path = $cdir;
        }

        if (ftp_chdir($ftp, $rel_path) && ftp_put($ftp, fn_basename($source), $source, FTP_BINARY)) {
            @ftp_site($ftp, "CHMOD " . (fn_get_file_ext($source) == 'php' ? '0644' : sprintf('0%o', DEFAULT_FILE_PERMISSIONS)) . " " . fn_basename($source));
            $result = true;
            ftp_chdir($ftp, $cdir);
        }
    }

    if (false === $result) {
        fn_set_notification('E', __('error'), __('text_uc_failed_to_ftp_copy'));
    }

    return $result;
}

/**
 * Create directory using ftp
 *
 * @param string $dir directory
 * @return boolean true if directory created successfully, false - otherwise
 */
function fn_uc_ftp_mkdir($dir)
{
    if (@is_dir($dir)) {
        return true;
    }

    $ftp = Registry::get('ftp_connection');
    if (!is_resource($ftp)) {
        fn_set_notification('E', __('error'), __('text_uc_ftp_connection_failed'));

        return false;
    }

    $result = false;

    $rel_path = str_replace(Registry::get('config.dir.root') . '/', '', $dir);
    $path = '';
    $dir_arr = array();
    if (strstr($rel_path, '/')) {
        $dir_arr = explode('/', $rel_path);
    } else {
        $dir_arr[] = $rel_path;
    }

    foreach ($dir_arr as $k => $v) {
        $path .= (empty($k) ? '' : '/') . $v;
        if (!@is_dir(Registry::get('config.dir.root') . '/' . $path)) {
            if (ftp_mkdir($ftp, $path)) {
                $result = true;
            } else {
                $result = false;
                break;
            }
        } else {
            $result = true;
        }
    }

    if (false === $result) {
        fn_set_notification('E', __('error'), __('text_uc_failed_to_ftp_mkdir'));
    }

    return $result;
}

/**
 * Backup database data which will be affected during upgrade
 *
 * @param string $path path to backup directory
 * @param Logger $log
 * @return array backed up tables list
 */
function fn_uc_backup_database($path, $log)
{
    $log->write('Backing up database: ' . $path, __FILE__, __LINE__);

    $tables = array();

    $db_files = array('db_scheme.sql', 'db_data.sql', 'db_lang_en.sql', 'db_lang_ru.sql');

    foreach ($db_files as $db_file) {
        if (file_exists($path . '/' . $db_file)) {

            $f = fopen($path . '/' . $db_file, 'rb');
            if (!empty($f)) {
                while (!feof($f)) {
                    $s = fgets($f);

                    if (preg_match_all("/(INSERT INTO|REPLACE INTO|UPDATE|ALTER TABLE|RENAME TABLE|DELETE FROM|DELETE [\w, ]+ FROM|DROP TABLE|CREATE TABLE)( IF EXISTS| IF NOT EXISTS)? [`]?(\w+)[`]?/", $s, $m)) {
                        $tables[$m[3][0]] = true;
                    }
                }
                fclose($f);
            }
        }
    }

    $tables = array_keys($tables);
    @fn_uc_rm($path . '/backup/uc.sql');
    @fn_uc_rm($path . '/db_backup_tables.txt');

    $bak_tables = fn_uc_backup_tables($tables, $path, $path . '/backup/uc.sql', $log);

    if (false === $bak_tables) {
        fn_set_notification('E', __('error'), __('text_uc_failed_to_backup_tables'));

        return false;
    }

    return $bak_tables;
}

/**
 * Backup files
 *
 * @param string $source upgrade package directory
 * @param string $dest working directory
 * @param array $result resulting list of backed up files
 * @param string $package package to make backup for
 * @param Logger $log
 * @return boolean true if directory copied correctly, false - otherwise
 */
function fn_uc_backup_files($source, $dest, &$result, $package, $log)
{
    // Simple copy for a file
    if (is_file($source)) {
        $log->write('Backing up: ' . $source, __FILE__, __LINE__);

        return fn_uc_backup_file($source, $dest, $result, $package);
    }

    // Loop through the folder
    if (is_dir($source)) {
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $log->write('Backing up: ' . $entry, __FILE__, __LINE__);
            // Deep backup directories
            if ($dest !== $source . '/' . $entry) {
                if (fn_uc_backup_files(rtrim($source, '/') . '/' . $entry, $dest . '/' . $entry, $result, $package, $log) == false) {
                    return false;
                }
            }
        }

        // Clean up
        $dir->close();

        return true;
    } else {
        fn_set_notification('E', __('error'), __('text_uc_incorrect_upgrade_path'));

        return false;
    }
}

/**
 * Backup certain file
 *
 * @param string $source source file
 * @param string $dest destination file/directory
 * @param array $result resulting list of backed up files
 * @param string $package package to make backup for
 * @return string filename of backed up file
 */
function fn_uc_backup_file($source, $dest, &$result, $package)
{
    $file_name = fn_basename($source);

    if (is_file($dest)) {
        fn_echo(' .');
        $relative_path = str_replace(Registry::get('config.dir.root') . '/', '', $dest);
        fn_mkdir(dirname(Registry::get('config.dir.upgrade') . $package . '/backup/' . $relative_path));
        if (false === fn_copy($dest, Registry::get('config.dir.upgrade') . $package . '/backup/' . $relative_path)) {
            fn_set_notification('E', __('error'), __('text_uc_failed_to_copy_file'));
        }
        $result[] = $relative_path;
    }

    return true;
}

/**
 * Function backup obsolete files before deleting
 *
 * @param string $dest Destanation directory
 * @param string $source Source directory
 * @param string $xml_file Path to xml file with list of files.
 */
function fn_uc_backup_obsolete_files($dest, $source, $xml_file, $log)
{
    $files_list = fn_uc_get_files_from_xml($xml_file, 'deleted_files');

    $themes_files = fn_uc_find_in_themes($files_list, $source);
    $files_list = array_merge($files_list, $themes_files);

    foreach ($files_list as $l) {
        $log->write('Backing up:' . $l, __FILE__, __LINE__);
        fn_echo(' .');
        fn_mkdir(dirname($dest . $l));
        fn_copy($source . '/' . $l, $dest . $l);
    }

    return $files_list;
}

/**
 * Function remove obsolete or new files after upgrade or reverting upgrade.
 *
 * @param string $source Source directory
 * @param string $xml_file Path to xml file with list of files.
 * @param string $section section of the xml file
 */
function fn_uc_rm_files($source, $xml_file, $section)
{
    $files_list = fn_uc_get_files_from_xml($xml_file, $section);

    $themes_files = fn_uc_find_in_themes($files_list, $source);
    $files_list = array_merge($files_list, $themes_files);

    foreach ($files_list as $file) {
        fn_uc_rm($source . '/' . $file);
    }
}

/**
 * Function finds files from base theme in all installed themes and return full path to those files.
 *
 * @param array $files Array with relative name of the files
 * @param string $source Path to root folder
 * @return array Array of the copies of file from all installed themes.
 */
function fn_uc_find_in_themes($files, $source)
{
    $base_theme = 'var/themes_repository/' . Registry::get('config.base_theme') . '/';
    $len = strlen($base_theme);

    $installed_themes = array();

    $themes_dirs = array();

    $root_themes_path = fn_get_theme_path('[relative]', 'C');
    $themes_dirs[$root_themes_path] = fn_get_dir_contents(Registry::get('config.dir.root') . '/' . $root_themes_path);

    if (fn_allowed_for('ULTIMATE')) {
        $company_ids = fn_get_all_companies_ids();
        foreach ($company_ids as $company_id) {
            $themes_path = fn_get_theme_path('[relative]', 'C', $company_id);
            $themes_dirs[$themes_path] = fn_get_dir_contents(Registry::get('config.dir.root') . '/' . $themes_path);
        }
    }

    $result = array();
    foreach ($files as $file) {
        if (substr($file, 0, $len) == $base_theme) {
            foreach ($themes_dirs as $rel_path => $installed_themes) {
                foreach ($installed_themes as $theme_name) {
                    $relative_name = "$rel_path/$theme_name/" . substr($file, $len);
                    if (file_exists($source . '/' . $relative_name)) {
                        $result[] = $relative_name;
                    }
                }
            }
        }
    }

    return $result;
}

/**
 * Function get list of files from package xml. This list will be used for deleting obsolete or new files after upgrading or reverting.
 *
 * @param string $xml_file Path to the xml file
 * @param string $section section of the xml file
 *
 * @return array list of files
 */
function fn_uc_get_files_from_xml($xml_file, $section)
{
    $xml = simplexml_load_file($xml_file, NULL, LIBXML_NOERROR);

    $result = array();

    if (empty($xml)) {
        fn_set_notification('E', __('error'), __('text_uc_unable_to_parse_uc_xml'));
    } else {
        // Get files list
        if (isset($xml->$section)) {
            foreach ($xml->$section->item as $item) {
                $result[] = (string) $item['file'];
            }
        }
    }

    return $result;
}

/**
 * Check installed upgrades
 *
 * @return array array which indicates, if any upgrade has conflicts and if any upgrade exist
 */
function fn_uc_check_installed_upgrades()
{
    $result = array(
        'has_conflicts' => false,
        'has_upgrades' => false,
    );

    $upgrades = 0;

    if (is_file(Registry::get('config.dir.upgrade') . 'installed_upgrades.php')) {
        include(Registry::get('config.dir.upgrade') . 'installed_upgrades.php');

        foreach ($udata as $p => $f) {
            if (!empty($f['files']) && empty($f['not_installed'])) {
                foreach ($f['files'] as $_f => $_s) {
                    if ($_s == false) {
                        $result['has_conflicts'] = true;
                        break;
                    }
                }
            }
            if (empty($f['not_installed'])) {
                $upgrades++;
            }
        }

        $result['has_upgrades'] = $upgrades;
    }

    return $result;
}

function fn_uc_update_installed_upgrades($data)
{
    if (fn_put_contents(Registry::get('config.dir.upgrade') . 'installed_upgrades.php', "<?php\n \$udata = " . var_export($data, true) . ";\n?>")) {
        return true;
    } else {
        fn_set_notification('W', __('warning'), __('text_uc_unable_to_update_list_of_installed_upgrades'));

        return false;
    }
}

/**
 * Cleanup upgrade cache
 *
 * @param string $package package name
 * @param string $type upgrade type (upgrade/revert)
 * @param array $dumps Array with upgrade sql dumps
 * @return boolean always true
 */
function fn_uc_cleanup_cache($package, $type, $dumps = array())
{
    if ($type == 'upgrade') {
        foreach ($dumps as $dump) {
            @unlink(Registry::get('config.dir.upgrade') . $package . '/' . $dump . '.tmp');
        }
    } else {
        @unlink(Registry::get('config.dir.upgrade') . $package . '/backup/uc.sql.tmp');
    }
}

/**
 * Check if array item exists in the string
 *
 * @param string $value string to search array item in
 * @param array $array items list
 * @return boolean true if value found, false - otherwise
 */
function fn_uc_check_array_value($value, $array)
{
    foreach ($array as $v) {
        if (strpos($value, $v) !== false) {
            return true;
        }
    }

    return false;
}

function fn_uc_rm($path)
{
    fn_rm($path);
    if (file_exists($path)) {
        fn_uc_ftp_rm($path);
    }
    if (file_exists($path)) {
        fn_set_notification('E', __('error'), __('text_uc_unable_to_remove_file') . ' ' . $path);
    }

    return true;
}

function fn_uc_ftp_rm($path)
{
    $ftp = Registry::get('ftp_connection');
    if (is_resource($ftp)) {
        $rel_path = str_replace(Registry::get('config.dir.root') . '/', '', $path);
        if (is_file($path)) {
            return @ftp_delete($ftp, $rel_path);
        }

        // Loop through the folder
        if (is_dir($path)) {
            $dir = dir($path);
            while (false !== $entry = $dir->read()) {
                // Skip pointers
                if ($entry == '.' || $entry == '..') {
                    continue;
                }
                if (fn_uc_ftp_rm($path . '/' . $entry) == false) {
                    return false;
                }
            }
            // Clean up
            $dir->close();

            return @ftp_rmdir($ftp, $rel_path);
        }
    }

    return false;
}

/**
 * Functions creates dump of tables in $file.
 *
 * @param mixed $tables Array of tables
 * @param string $dir Directory for saving file with table names
 * @param string $file Dump file name  \
 * @param Logger $log
 * @return bool Boolean False on failure
 */
function fn_uc_backup_tables($tables, $dir, $file, $log)
{
    if (empty($tables)) {
        return array();
    }

    if (!is_array($tables)) {
        $tables = array($tables);
    }

    $new_license_data = fn_uc_get_new_license_data();
    if (!empty($new_license_data['new_license']) && !in_array(Registry::get('config.table_prefix') . 'settings', $tables)) {
        $tables[] = Registry::get('config.table_prefix') . 'settings';
    }

    $file_backuped_tables = "$dir/db_backup_tables.txt";
    $backuped_tables = is_file($file_backuped_tables) ? explode("\n", fn_get_contents($file_backuped_tables)) : array();

    foreach ($tables as $key => &$table) {
        $table = fn_check_db_prefix($table, Registry::get('config.table_prefix'));
        if (in_array($table, $backuped_tables)) {
            unset($tables[$key]);
        }
    }

    if (empty($tables)) {
        return $tables;
    }

    if (false === fn_uc_is_enough_disk_space($file, $tables)) {
        fn_set_notification('W', __('warning'), __('text_uc_no_enough_space_to_backup_database'));

        return false;
    }

    if (fn_uc_is_mysqldump_available(Registry::get('config.db_password'))) {
        $log->write('Using mysqldump', __FILE__, __LINE__);
        $command = 'mysqldump --compact --no-create-db --add-drop-table --default-character-set=utf8 --skip-comments --verbose --host=' . Registry::get('config.db_host') . ' --user=' . Registry::get('config.db_user') . ' --password=\'' . Registry::get('config.db_password') . '\' --databases ' . Registry::get('config.db_name') . ' --tables ' . implode(' ', $tables) . ' >> ' . $file;
        system($command, $retval);

        if (0 === $retval) {
            $backuped_tables = array_merge($backuped_tables, $tables);
            fn_put_contents($file_backuped_tables, implode("\n", $backuped_tables));

            return $tables;
        }
        $log->write('mysqldump has reported failure', __FILE__, __LINE__);
    }

    $rows_per_pass = 40;
    $max_row_size = 10000;

    $t_status = db_get_hash_array("SHOW TABLE STATUS", 'Name');
    $f = fopen($file, 'ab');
    if (!empty($f)) {
        foreach ($tables as &$table) {
            $log->write('Backing up table: ' . $table, __FILE__, __LINE__);
            fwrite($f, "\nDROP TABLE IF EXISTS " . str_replace('?:', Registry::get('config.table_prefix'), $table) . ";\n");
            if (empty($t_status[str_replace('?:', Registry::get('config.table_prefix'), $table)])) { // new table in upgrade, we need drop statement only
                continue;
            }
            $scheme = db_get_row("SHOW CREATE TABLE $table");
            fwrite($f, array_pop($scheme) . ";\n\n");

            $total_rows = db_get_field("SELECT COUNT(*) FROM $table");

            // Define iterator
            if ($t_status[str_replace('?:', Registry::get('config.table_prefix'), $table)]['Avg_row_length'] < $max_row_size) {
                $it = $rows_per_pass;
            } else {
                $it = 1;
            }

            fn_echo(' .');

            for ($i = 0; $i < $total_rows; $i = $i + $it) {
                $table_data = db_get_array("SELECT * FROM $table LIMIT $i, $it");
                foreach ($table_data as $_tdata) {
                    $_tdata = fn_add_slashes($_tdata, true);
                    $values = array();
                    foreach ($_tdata as $v) {
                        $values[] = ($v !== null) ? "'$v'" : 'NULL';
                    }
                    fwrite($f, "INSERT INTO " . str_replace('?:', Registry::get('config.table_prefix'), $table) . " (`" . implode('`, `', array_keys($_tdata)) . "`) VALUES (" . implode(', ', $values) . ");\n");
                }
            }

            $backuped_tables[] = "$table";
        }
        fclose($f);
        @chmod($file, DEFAULT_FILE_PERMISSIONS);
        fn_put_contents($file_backuped_tables, implode("\n", $backuped_tables));

        return $tables;
    }
}

/**
 * Function updates language variables for all installed languages.
 *
 * @param string $table Name of table for updating
 * @param mixed $keys Array of primary keys in table for data comparison
 * @param boolean $show_process Echo or no ' .' for showing process.
 */
function fn_uc_update_alt_languages($table, $keys, $show_process = true)
{
    static $langs;

    if (empty($langs)) {
        $langs = db_get_fields("SELECT lang_code FROM ?:languages");
    }

    if (!is_array($keys)) {
        $keys = array($keys);
    }

    $i = 0;
    $step = 50;
    while ($items = db_get_array("SELECT * FROM ?:$table WHERE lang_code = ?s LIMIT $i, $step", DEFAULT_LANGUAGE)) {
        $i += $step;
        foreach ($items as $v) {
            foreach ($langs as $lang) {
                $condition = array();
                foreach ($keys as $key) {
                    $lang_var = $v[$key];
                    $condition[] = db_quote("$key = ?s", $lang_var);
                }
                $condition = implode(' AND ', $condition);
                $exists = db_get_field("SELECT COUNT(*) FROM ?:$table WHERE $condition AND lang_code = ?s", $lang);
                if (empty($exists)) {
                    $v['lang_code'] = $lang;
                    db_query("REPLACE INTO ?:$table ?e", $v);
                    if ($show_process) {
                        fn_echo(' .');
                    }
                }
            }
        }
    }
}

function fn_uc_is_mysqldump_available($password)
{
    // check that password do not have special char '
    if (strpos($password, "'") !== false) {
        return false;
    }

    if (function_exists('exec') && function_exists('system')) {
        exec('mysqldump', $output, $retval);

        return 1 === $retval;
    }

    return false;
}

function fn_uc_is_enough_disk_space($path, $tables)
{
    setlocale(LC_ALL, 'en_US.UTF8');
    $avilable_space = @disk_free_space(dirname($path));

    // we've got an strange error on some servers: "Value too large for defined data type"
    // In this case disk_free_space returns false. So try to continue upgrade
    if ($avilable_space === false) {
        return true;
    }

    $size_of_tables = 0;
    foreach ($tables as $table) {
        $table_data = db_get_array("SHOW TABLE STATUS LIKE '" . $table . "'");
        foreach ($table_data as $_tdata) {
            $size_of_tables += $_tdata['Data_length'] + $_tdata['Index_length'];
        }
    }

    return $avilable_space > $size_of_tables;
}

function fn_uc_run_continuous_job($fn_name, $args)
{
    // make sure no another upgrade processes are still active
    $lock_file_path = Registry::get('config.dir.upgrade') . '.upgrade_lock';

    if (is_file($lock_file_path)) {
        fn_set_notification('E', __('error'), __('text_uc_another_update_process_running', array(
            '[filename]' => $lock_file_path,
            '[url]' => fn_url('upgrade_center.unlock')
        )));
        exit;
    } else {
        $h = fopen($lock_file_path, 'w');
        if (!$h) {
            fn_set_notification('E', __('error'), __('text_uc_cannot_lock_upgrade_process'));
            exit;
        }
        fclose($h);
    }

    // adjust PHP to handle the long-running process
    set_time_limit(0);

    // set up the logger
    try {
        $log = Logger::instance();
        $log->logfile = Registry::get('config.dir.upgrade') . 'upgrade.log';
    } catch (Exception $e) {
        fn_set_notification('W', __('warning'), __('text_uc_upgrade_log_file_not_writable'));
    }
    $args[] = $log;

    // run the job
    $result = call_user_func_array($fn_name, $args);

    // remove the lock after the job is done
    @unlink($lock_file_path);
    if (is_file($lock_file_path)) {
        fn_set_notification('W', __('warning'), __('text_uc_unable_to_remove_upgrade_lock', array(
            '[file]' => $lock_file_path
        )));
    }

    return $result;
}
