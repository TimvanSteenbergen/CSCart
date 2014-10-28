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

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Settings;
use Twigmo\Upgrade\TwigmoUpgrade;
use Tygh\Registry;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'upgrade_twigmo' && !fn_twg_is_on_saas()) {

        $uc_settings = Settings::instance()->getValues('Upgrade_center');

        fn_start_scroller();
        fn_echo('<b>' . __('twgadmin_upgrade_addon') . '</b><br>');
        fn_echo(__('twgadmin_download_twigmo') . '<br>');

        $install_src_dir = TwigmoUpgrade::downloadDistr();

        if (!$install_src_dir) {
            $error_string = __('text_uc_cant_download_package');
            fn_echo('<span style="color:red">' . $error_string . '</span><br>');
            fn_set_notification('E', __('error'), $error_string);
            fn_stop_scroller();

            return array(CONTROLLER_STATUS_REDIRECT, 'addons.update?addon=twigmo');
        }

        fn_ftp_connect($uc_settings);

        $upgrade_dirs = TwigmoUpgrade::getUpgradeDirs($install_src_dir);

        fn_echo(__('twgadmin_checking_permissions') . '<br>');
        $installed_dirs_ready = TwigmoUpgrade::checkUpgradePermissions($upgrade_dirs['installed']);
        $repo_dirs_ready = TwigmoUpgrade::checkUpgradePermissions($upgrade_dirs['repo']);

        if (!($installed_dirs_ready && $repo_dirs_ready)) {
            $url = fn_url('settings.manage&section_id=Upgrade_center');
            $error_string = __(
                'twgadmin_no_files_permissions',
                array(
                    '[link]' => '<a href="' . $url . '">',
                    '[/link]' => '</a>'
                )
            );
            fn_echo('<span style="color:red">' . $error_string . '</span><br>');
            fn_set_notification('E', __('error'), $error_string);
            fn_stop_scroller();

            return array(CONTROLLER_STATUS_REDIRECT, 'addons.update?addon=twigmo');
        }

        // backup files
        fn_echo(__('twgadmin_backup_files') . '<br>');
        TwigmoUpgrade::copyFiles(
            $upgrade_dirs['installed'],
            $upgrade_dirs['backup_files']
        );

        // Execute pre functions
        TwigmoUpgrade::execUpgradeFunc($install_src_dir, 'pre_upgrade');

        // Get and save current settings
        fn_echo('<br>' . __('twgadmin_backup_settings') . '<br>');
        TwigmoUpgrade::backupSettings($upgrade_dirs);

        // Uninstal addon
        fn_echo(__('twgadmin_uninstall_addon') . '<br>');
        fn_uninstall_addon('twigmo', false);

         // Update twigmo files
        fn_echo('Copying new Twigmo add-on files...<br>');

        TwigmoUpgrade::updateFiles($upgrade_dirs);

        // Install
        fn_echo('<br>Installing Twigmo add-on...<br>');
        fn_install_addon('twigmo', false);

        $_SESSION['twigmo_upgrade'] = array(
            'upgrade_dirs' => $upgrade_dirs,
            'install_src_dir' => $install_src_dir
        );
        fn_stop_scroller();
        echo '<br><br>';
        fn_redirect('upgrade_center.upgrade_twigmo.step2');
    }
}

if ($mode == 'upgrade_twigmo' and $action == 'step2' and isset($_SESSION['twigmo_upgrade'])  && !fn_twg_is_on_saas()) {
    fn_start_scroller();
    fn_echo(__('twgadmin_restore_settings') . '<br>');
    fn_ftp_connect(Settings::instance()->getValues('Upgrade_center'));
    fn_echo('.');
    $upgrade_dirs = $_SESSION['twigmo_upgrade']['upgrade_dirs'];

    fn_echo('.');
    // Uninstal addon
    fn_uninstall_addon('twigmo', false);
    fn_echo('.');

    // Install
    fn_install_addon('twigmo', false);
    fn_echo('.');
    // Restore settings
    TwigmoUpgrade::restoreSettingsAndCSS($upgrade_dirs, $auth['user_id']);
    fn_echo('.');

    // Clear template cache
    fn_rm(Registry::get('config.dir.cache_templates'));
    fn_echo('.');

    fn_echo('<br><b>' . __('twgadmin_upgrade_completed') . '<b><br>');
    unset($_SESSION['twigmo_upgrade']);
    fn_stop_scroller();

    return array(CONTROLLER_STATUS_REDIRECT, 'addons.update?addon=twigmo');
}
