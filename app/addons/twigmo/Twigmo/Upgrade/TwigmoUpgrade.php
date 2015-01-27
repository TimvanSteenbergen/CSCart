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

namespace Twigmo\Upgrade;

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;
use Tygh\BlockManager\Exim;
use Tygh\BlockManager\Layout;
use Tygh\BlockManager\Location;
use Twigmo\Core\Functions\Lang;
use Twigmo\Core\Functions\UserAgent;
use Twigmo\Core\TwigmoConnector;
use Tygh\Languages\Values as LanguageValues;
use Twigmo\Core\TwigmoSettings;

class TwigmoUpgrade
{
    private static $install_src_dir = '';
    private static $addon_path = 'twigmo/';
    private static $full_addon_path = 'addons/twigmo/';
    private static $installed_themes = array();
    private static $repo_themes = array();
    private static $dirs = array();
    private static $file_areas = array(
        'media' =>      'media/images',
        'css' =>        'css',
        'templates' =>  'templates'
    );
    private static $backup_files_path = 'backup_files/';
    private static $repo_path = 'var/themes_repository/basic/';

    private static function init($params)
    {
        self::$install_src_dir = $params['install_src_dir'];
        $available_themes = fn_get_available_themes(Registry::get('settings.theme_name'));
        self::$installed_themes = array_keys($available_themes['installed']);
        self::$repo_themes = array_keys($available_themes['repo']);

        return true;
    }

    public static function downloadDistr()
    {
        // Get needed version
        $version_info = self::GetNextVersionInfo();
        if (!$version_info['next_version'] || !$version_info['update_url']) {
            return false;

        }
        $download_file_dir = TWIGMO_UPGRADE_DIR . $version_info['next_version'] . '/';
        $download_file_path = $download_file_dir . 'twigmo.tgz';
        $unpack_path = $download_file_path . '_unpacked';
        fn_rm($download_file_dir);
        fn_mkdir($download_file_dir);
        fn_mkdir($unpack_path);

        $data = fn_get_contents($version_info['update_url']);
        if (!fn_is_empty($data)) {
            fn_put_contents($download_file_path, $data);
            $res = fn_decompress_files($download_file_path, $unpack_path);

            if (!$res) {
                fn_set_notification('E', __('error'), __('twgadmin_failed_to_decompress_files'));
                return false;
            }
            return $unpack_path . '/';

        } else {
            fn_set_notification('E', __('error'), __('text_uc_cant_download_package'));
            return false;

        }
    }

    /**
     * @param String $install_src_dir
     * @return Array
     */
    public static function getUpgradeDirs($install_src_dir)
    {
        self::init(array('install_src_dir' => $install_src_dir));

        self::setInstalledDirs();

        self::setRepoDirs();

        self::setDistrDirs();

        self::setBackupDirs();

        if (fn_allowed_for('ULTIMATE')) {
            $company_ids = fn_get_all_companies_ids();
            self::$dirs['backup_company_settings'] = array();
            foreach (array_keys(self::$file_areas) as $key) {
                self::$dirs['backup_files'][$key . '_frontend'] =
                self::$dirs['installed'][$key . '_frontend'] = array();
            }
            foreach ($company_ids as $company_id) {
                self::$dirs['backup_company_settings'][$company_id] = self::$dirs['backup_settings'] . 'companies/'
                        . $company_id . '/';

                self::setInstalledFrontend($company_id);

                self::setBackupFrontend($company_id);

            }
        }

        return self::$dirs;
    }

    private static function setInstalledDirs()
    {
        self::$dirs['installed'] = array(
            'addon' => Registry::get('config.dir.addons') . self::$addon_path,
        );
        foreach (self::$file_areas as $key => $file_area) {
            self::$dirs['installed'][$key . '_backend'] = fn_get_theme_path(
                '[themes]/[theme]/',
                'A'
            ) . $file_area . '/' . self::$full_addon_path;
            foreach (self::$installed_themes as $theme) {
                self::$dirs['installed'][$key . '_frontend'][0][$theme] = fn_get_theme_path(
                    '[themes]/' . $theme,
                'C'
                ) . '/' . $file_area . '/' . self::$full_addon_path;
            }
        }

        return true;
    }

    private static function setRepoDirs()
    {
        self::$dirs['repo'] = array(
            'addon' => Registry::get('config.dir.addons') . self::$addon_path,
        );
        foreach (self::$file_areas as $key => $file_area) {
            self::$dirs['repo'][$key . '_backend'] = fn_get_theme_path('[themes]/', 'A') . $file_area . '/'
                . self::$full_addon_path;

            foreach (self::$repo_themes as $theme) {
                self::$dirs['repo'][$key . '_frontend'][$theme] = fn_get_theme_path(
                    '[repo]/' . $theme,
                'C'
                ) . '/' . $file_area . '/' . self::$full_addon_path;
            }
        }

        return true;
    }

    private static function setDistrDirs()
    {
        self::$dirs['distr'] = array(
            'addon' => self::$install_src_dir . 'app/' . self::$full_addon_path,
        );

        foreach (self::$file_areas as $key => $file_area) {
            self::$dirs['distr'][$key . '_backend'] = self::$install_src_dir . 'design/backend/' . $file_area . '/'
                . self::$full_addon_path;

            foreach (self::$repo_themes as $theme) {
                self::$dirs['distr'][$key . '_frontend'][$theme] = self::$install_src_dir
                        . self::$repo_path . $file_area . '/' . self::$full_addon_path;
            }
        }
    }

    private static function setBackupDirs()
    {
        self::$dirs['backup_root'] = TwigmoUpgradeMethods::getBackupDir();
        self::$dirs['backup_files'] = array(
            'addon' => self::$dirs['backup_root']
                . self::$backup_files_path
             . 'app/'
                . self::$full_addon_path,
        );
        foreach (self::$file_areas as $key => $file_area) {
            self::$dirs['backup_files'][$key . '_backend'] =
                self::$dirs['backup_root']
                    . self::$backup_files_path
                    . fn_get_theme_path('[relative]/[theme]/', 'A')
                . $file_area . '/'
                    . self::$full_addon_path;
            foreach (self::$installed_themes as $theme) {
                self::$dirs['backup_files'][$key . '_frontend'][0][$theme] =
                    self::$dirs['backup_root']
                    . self::$backup_files_path
                    . fn_get_theme_path('[relative]/' . $theme . '/', 'C')
                . $file_area . '/'
                    . self::$full_addon_path;
            }
        }
        self::$dirs['backup_settings'] = self::$dirs['backup_root'] . 'backup_settings/';
        self::$dirs['backup_company_settings'] = array(self::$dirs['backup_settings'] . 'companies/0/');

        return true;
    }

    private static function setInstalledFrontend($company_id)
    {
        foreach (self::$file_areas as $key => $file_area) {
            foreach (self::$installed_themes as $theme) {
                self::$dirs['installed'][$key . '_frontend'][$company_id][$theme] = fn_get_theme_path(
                    '[themes]/' . $theme,
                        'C',
                        $company_id
                ) . '/' . $file_area . '/' . self::$full_addon_path;
            }
        }

        return true;
    }

    private static function setBackupFrontend($company_id)
    {
        foreach (self::$file_areas as $key => $file_area) {
            foreach (self::$installed_themes as $theme) {
                self::$dirs['backup_files'][$key . '_frontend'][$company_id][$theme] = self::$dirs['backup_root']
                    . self::$backup_files_path . fn_get_theme_path(
                        '[relative]/' . $theme . '/',
                           'C',
                           $company_id
                    ) . $file_area . '/' . self::$full_addon_path;
            }
        }
    }

    public static function checkUpgradePermissions($upgrade_dirs, $is_writable = true)
    {
        foreach ($upgrade_dirs as $upgrade_dir) {

            if (is_array($upgrade_dir)) {
                $is_writable = self::checkUpgradePermissions($upgrade_dir, $is_writable);

            } elseif (!is_dir($upgrade_dir)) {
                fn_uc_mkdir($upgrade_dir);
                self::checkUpgradePermissions(array($upgrade_dir), $is_writable);

            } elseif (!self::isWritableDest($upgrade_dir)) {
                return false;

            }
            if (!is_array($upgrade_dir)) {
                $check_result = array();
                fn_uc_check_files($upgrade_dir, array(), $check_result, '', '');
                $is_writable = empty($check_result);

            }
            if (!$is_writable) {
                break;

            }
        }

        return $is_writable;
    }

    public static function copyFiles($source, $dest)
    {
        if (is_array($source)) {
            foreach ($source as $key => $src) {
                self::copyFiles($src, $dest[$key]);
            }

        } else {
            fn_uc_copy_files($source, $dest);

        }

        return true;
    }

    public static function execUpgradeFunc($install_src_dir, $file_name)
    {
        $file = $install_src_dir . '/addons/twigmo/' . $file_name . '.php';
        if (file_exists($file)) {
            require_once($file);
        }

        return true;
    }

    public static function backupSettings($upgrade_dirs)
    {
        // Backup addon's settings to the session
        $_SESSION['twigmo_backup_settings'] = TwigmoSettings::get();
        // Backup twigmo blocks
        $old_company_id = Registry::get('runtime.company_id');
        $old_layout_id = Registry::get('runtime.layout.layout_id');
        foreach ($upgrade_dirs['backup_company_settings'] as $company_id => $dir) {
            Registry::set('runtime.company_id', $company_id);
            $default_layout_id = fn_twg_get_default_layout_id();
            Registry::set('runtime.layout.layout_id', $default_layout_id);
            $location = Location::instance($default_layout_id)->get('twigmo.post');
            if ($location) {
                $exim = Exim::instance($company_id, $default_layout_id);
                if (version_compare(PRODUCT_VERSION, '4.1.1', '>=')) {
                    $content = $exim->export($default_layout_id, array($location['location_id']));

                } else {
                    $content = $exim->export(array($location['location_id']));

                }
                if ($content) {
                    fn_twg_write_to_file($dir . '/blocks.xml', $content, false);

                }
            }
        }
        Registry::set('runtime.company_id', $old_company_id);
        Registry::set('runtime.layout.layout_id', $old_layout_id);

        // Backup twigmo langvars
        $languages = Lang::getLanguages();
        foreach ($languages as $language) {
            // Prepare langvars for backup
            $langvars = Lang::getAllLangVars($language['lang_code']);
            $langvars_formated = array();
            foreach ($langvars as $name => $value) {
                $langvars_formated[] = array('name' => $name, 'value' => $value);
            }
            fn_twg_write_to_file(
                $upgrade_dirs['backup_settings'] . '/lang_' . $language['lang_code'] . '.bak',
                $langvars_formated
            );
        }
        if (fn_allowed_for('ULTIMATE')) {
            db_export_to_file(
                $upgrade_dirs['backup_settings'] . 'lang_ult.sql',
                array(db_quote('?:ult_language_values')),
                'Y',
                'Y',
                false,
                false,
                false
            );
        }

        return true;
    }

    public static function updateFiles($upgrade_dirs)
    {
        // Remove all addon's files
        foreach ($upgrade_dirs['repo'] as $dir) {
            TwigmoUpgradeMethods::removeDirectoryContent($dir);
        }
        // Copy files from distr to repo
        self::copyFiles($upgrade_dirs['distr'], $upgrade_dirs['repo']);

        return true;
    }

    /**
     * @param Boolean $display_service_notifications
     * @return Boolean
     */
    public static function checkForUpgrade($display_service_notifications = true)
    {
        $is_upgradable = false;
        $user_have_upgrade_priveleges = isset($_SESSION['auth']) && $_SESSION['auth']['area'] == 'A'
                && !empty($_SESSION['auth']['user_id'])
                && fn_check_user_access($_SESSION['auth']['user_id'], 'upgrade_store');
        if ($user_have_upgrade_priveleges) {
            $is_upgradable = !fn_twg_is_on_saas() && TwigmoConnector::checkUpdates();
            TwigmoConnector::updateUARules();
            if (TwigmoConnector::getAccessID('A')) {
                $connector = new TwigmoConnector();
                $connector->updateConnections();
                self::displayServiceNotifications(array(
                        'display_service_notifications' => $display_service_notifications,
                        'connector' => $connector
                    )
                );

            }
            UserAgent::sendUaStat();
        }

        return $is_upgradable;
    }

    private static function displayServiceNotifications($params)
    {
        if ($params['display_service_notifications']) {
            $params['connector']->displayServiceNotifications();

        }
        return true;
    }

    public static function restoreSettingsAndCSS($upgrade_dirs)
    {
        // Restore langvars - for all languages except EN and RU
        $languages = Lang::getLanguages();
        $except_langs = array('en', 'ru');
        foreach ($languages as $language) {
            $backup_file =
                $upgrade_dirs['backup_settings']
                . 'lang_' . $language['lang_code'] . '.bak';
            if (
                !in_array(
                    $language['lang_code'],
                    $except_langs
                )
                and file_exists($backup_file)
            ) {
                LanguageValues::updateLangVar(
                    unserialize(
                        fn_get_contents($backup_file)
                    ),
                    $language['lang_code']
                );
            }
        }

        // Restore blocks
        $old_company_id = Registry::get('runtime.company_id');
        foreach ($upgrade_dirs['backup_company_settings'] as $company_id => $dir) {
            Registry::set('runtime.company_id', $company_id);
            $backup_file = $dir . 'blocks.xml';
            if (file_exists($backup_file)) {
                if (version_compare(PRODUCT_VERSION, '4.1.1', '>=')) {
                    Registry::set('runtime.layout', Layout::instance()->getDefault());
                }
                Exim::instance($company_id, fn_twg_get_default_layout_id())->importFromFile($backup_file);
            }
        }
        Registry::set('runtime.company_id', $old_company_id);

        // Restore settings if addon was connected
        $restored_settings = array(
            'my_private_key',
            'my_public_key',
            'his_public_key',
            'email',
            'customer_connections',
            'admin_connection'
        );
        $settings = array();
        foreach ($_SESSION['twigmo_backup_settings'] as $setting => $value) {
            if (in_array($setting, $restored_settings)) {
                $settings[$setting] = $value;
            }
        }
        $settings['version'] = TWIGMO_VERSION;
        unset($_SESSION['twigmo_backup_settings']);
        TwigmoSettings::set($settings);
        $connector = new TwigmoConnector();
        if (!$connector->updateConnections(true)) {
            $connector->disconnect(array(), true);
        }

        // Restore images
        $directories_exists = self::checkIsPathExists($upgrade_dirs['backup_files']['media_frontend'])
            && self::checkIsPathExists($upgrade_dirs['installed']['media_frontend']);
        if ($directories_exists) {
            self::copyFiles(
                $upgrade_dirs['backup_files']['media_frontend'],
                $upgrade_dirs['installed']['media_frontend']
            );
        }

        return true;
    }

    private static function checkIsPathExists($directory)
    {
        if (is_array($directory)) {
            foreach ($directory as $path) {
                self::checkIsPathExists($path);
            }

        } else {
            return is_dir($directory) || is_file($directory);

        }
        return false;
    }

    public static function getNextVersionInfo()
    {
        $version_info = fn_get_contents(TWIGMO_UPGRADE_DIR . TWIGMO_UPGRADE_VERSION_FILE);
        if ($version_info) {
            $version_info = unserialize($version_info);

        } else {
            $version_info = array('next_version' => '', 'description' => '', 'update_url' => '');

        }
        return $version_info;
    }

    /**
     * This is a copy of the fn_uc_is_writable_dest function which was removed in 4.1.5
     *
     * Check if destination is writable
     *
     * @param string $dest destination file/directory
     * @return boolean true if writable, false - if not
     */
    private static function isWritableDest($dest)
    {
        $dest = rtrim($dest, '/');

        if (is_file($dest)) {
            $f = @fopen($dest, 'ab');
            if ($f === false) {
                return false;
            }
            fclose($f);
        } elseif (is_dir($dest)) {
            if (!fn_put_contents($dest . '/zzzz.zz', '1')) {
                return false;
            }
            fn_rm($dest . '/zzzz.zz');
        } else {
            return false;
        }

        return true;
    }
}
