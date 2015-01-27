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
use Tygh\Settings;
use Tygh\Navigation\LastView;
use Tygh\Addons\SchemesManager;
use Tygh\BlockManager\Exim;
use Tygh\BlockManager\Layout;
use Tygh\BlockManager\Location;
use Tygh\BlockManager\ProductTabs;
use Tygh\Themes\Themes;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Updates addon settings
 *
 * @param string $settings Array of add-on's settings to be updated
 * @return bool Always true
 */
function fn_update_addon($settings)
{
    if (is_array($settings)) {
        foreach ($settings['options'] as $setting_id => $value) {
            Settings::instance()->updateValueById($setting_id, $value);

            if (!empty($_REQUEST['update_all_vendors'][$setting_id])) {
                Settings::instance()->resetAllVendorsSettings($setting_id);
            }
        }
    }

    return true;
}

/**
 * Uninstalles addon
 *
 * @param string $addon_name Addon name to be uninstalled
 * @param bool $show_message If defined as true, additionally show notification
 * @return bool True if addons uninstalled successfully, false otherwise
 */
function fn_uninstall_addon($addon_name, $show_message = true)
{
    $addon_scheme = SchemesManager::getScheme($addon_name);
    if ($addon_scheme != false) {
        // Unmanaged addons can be uninstalled via console only
        if ($addon_scheme->getUnmanaged() && !defined('CONSOLE')) {
            return false;
        }

        // Check dependencies
        $dependencies = SchemesManager::getUninstallDependencies($addon_name);
        if (!empty($dependencies)) {
            fn_set_notification('W', __('warning'), __('text_addon_uninstall_dependencies', array(
                '[addons]' => implode(',', $dependencies)
            )));

            return false;
        }

        // Execute custom functions for uninstall
        $addon_scheme->callCustomFunctions('uninstall');

        $addon_description = db_get_field(
            "SELECT name FROM ?:addon_descriptions WHERE addon = ?s and lang_code = ?s",
            $addon_name, CART_LANGUAGE
        );

        // Delete options
        db_query("DELETE FROM ?:addons WHERE addon = ?s", $addon_name);
        db_query("DELETE FROM ?:addon_descriptions WHERE addon = ?s", $addon_name);

        // Delete settings
        $section = Settings::instance()->getSectionByName($addon_name, Settings::ADDON_SECTION);
        if (isset($section['section_id'])) {
            Settings::instance()->removeSection($section['section_id']);
        }

        // Delete language variables
        $addon_scheme->uninstallLanguageValues();

        // Revert database structure
        $addon_scheme->processQueries('uninstall', Registry::get('config.dir.addons') . $addon_name);

        // Remove product tabs
        ProductTabs::instance()->deleteAddonTabs($addon_name);

        fn_uninstall_addon_templates(fn_basename($addon_name));

        if (file_exists(Registry::get('config.dir.addons') . $addon_name . '/layouts.xml')) {
            $xml = simplexml_load_file(Registry::get('config.dir.addons') . $addon_name . '/layouts.xml', '\\Tygh\\ExSimpleXmlElement', LIBXML_NOCDATA);
            foreach ($xml->location as $location) {
                if (fn_allowed_for('ULTIMATE')) {
                    foreach (fn_get_all_companies_ids() as $company) {
                        $layouts = Layout::instance($company)->getList();
                        foreach ($layouts as $layout_id => $layout) {
                            Location::instance($layout_id)->removeByDispatch((string) $location['dispatch']);
                        }
                    }
                } else {
                    $layouts = Layout::instance()->getList();
                    foreach ($layouts as $layout_id => $layout) {
                        Location::instance($layout_id)->removeByDispatch((string) $location['dispatch']);
                    }
                }
            }
        }

        if ($show_message) {
            fn_set_notification('N', __('notice'), __('text_addon_uninstalled', array(
                '[addon]' => $addon_scheme->getName()
            )));
        }

        //Clean Registry
        Registry::del('addons.' . $addon_name);
        $hooks = Registry::get('hooks');
        Registry::del('hooks');

        if (!empty($hooks)) {
            foreach ($hooks as $hook_name => $hooks_data) {
                foreach ($hooks_data as $key => $hook_data) {
                    if ($hook_data['addon'] === $addon_name) {
                         unset($hooks[$hook_name][$key]);
                    }
                }
            }
        }

        Registry::set('hooks', $hooks);

        // Clean cache
        fn_clear_cache();

        return true;
    } else {
        return false;
    }
}

/**
 * Disables addon
 *
 * @param string $addon_name Addons name to be disabled
 * @param string $caller_addon_name TODO: NOT USED. Must be refactored.
 * @param bool $show_notification
 * @return bool Always true
 */
function fn_disable_addon($addon_name, $caller_addon_name, $show_notification = true)
{
    $func = 'fn_settings_actions_addons_' . $addon_name;
    if (function_exists($func)) {
        $new_status = 'D';
        $old_status = 'A';
        $func($new_status, $old_status);
    }
    db_query("UPDATE ?:addons SET status = ?s WHERE addon = ?s", 'D', $addon_name);

    if ($show_notification == true) {
        fn_set_notification('N', __('notice'), __('status_changed'));
    }

    return true;
}

/**
 * Installes addon
 *
 * @param string $addon Addon to install
 * @param bool $show_notification Display notification if set to true
 * @param bool $install_demo If defined as true, addon's demo data will be installed
 * @return bool True if addons installed successfully, false otherwise
 */
function fn_install_addon($addon, $show_notification = true, $install_demo = false)
{
    $status = db_get_field("SELECT status FROM ?:addons WHERE addon = ?s", $addon);
    // Return true if addon is installed
    if (!empty($status)) {
        return true;
    }

    $addon_scheme = SchemesManager::getScheme($addon);

    if (empty($addon_scheme)) {
        // Required add-on was not found in store.
        return false;
    }

    // Unmanaged addons can be installed via console only
    if ($addon_scheme->getUnmanaged() && !defined('CONSOLE')) {
        return false;
    }

    if ($addon_scheme != false) {
        // Register custom classes
        Registry::get('class_loader')->add('', Registry::get('config.dir.addons') . $addon);

        if ($addon_scheme->isPromo()) {

            $texts = fn_get_addon_permissions_text();
            fn_set_notification('E', __('error'), $texts['text']);

            return false;
        }

        $_data = array (
            'addon' => $addon_scheme->getId(),
            'priority' =>  $addon_scheme->getPriority(),
            'dependencies' => implode(',', $addon_scheme->getDependencies()),
            'conflicts' => implode(',', $addon_scheme->getConflicts()),
            'requirements' => $addon_scheme->getRequirements(),
            'version' => $addon_scheme->getVersion(),
            'separate' => ($addon_scheme->getSettingsLayout() == 'separate') ? 1 : 0,
            'has_icon' => $addon_scheme->hasIcon(),
            'unmanaged' => $addon_scheme->getUnmanaged(),
            'status' => 'D' // addon is disabled by default when installing
        );

        // Check system requirements (needed versions, installed extensions, etc.)
        if (!$addon_scheme->checkRequirements($_data['requirements'])) {
            return false;
        }

        $dependencies = SchemesManager::getInstallDependencies($_data['addon']);
        if (!empty($dependencies)) {
            fn_set_notification('W', __('warning'), __('text_addon_install_dependencies', array(
                '[addon]' => implode(',', $dependencies)
            )));

            return false;
        }

        if ($addon_scheme->callCustomFunctions('before_install') == false) {
            fn_uninstall_addon($addon, false);

            return false;
        }

        // Add add-on to registry
        Registry::set('addons.' . $addon, array(
            'status' => 'D',
            'priority' => $_data['priority'],
        ));

        // Execute optional queries
        if ($addon_scheme->processQueries('install', Registry::get('config.dir.addons') . $addon) == false) {
            fn_uninstall_addon($addon, false);

            return false;
        }

        if (fn_update_addon_settings($addon_scheme) == false) {
            fn_uninstall_addon($addon, false);

            return false;
        }

        db_query("REPLACE INTO ?:addons ?e", $_data);

        foreach ($addon_scheme->getAddonTranslations() as $translation) {
            db_query("REPLACE INTO ?:addon_descriptions ?e", array(
                'lang_code' => $translation['lang_code'],
                'addon' =>  $addon_scheme->getId(),
                'name' => $translation['value'],
                'description' => isset($translation['description']) ? $translation['description'] : ''
            ));
        }

        if ($original = $addon_scheme->getOriginals()) {
            db_query("REPLACE INTO ?:original_values ?e", array(
                'msgctxt' => 'Addon:' . $addon,
                'msgid' => $original['name']
            ));

            db_query("REPLACE INTO ?:original_values ?e", array(
                'msgctxt' => 'AddonDescription:' . $addon,
                'msgid' => isset($original['description']) ? $original['description'] : ''
            ));
        }

        // Install templates
        fn_install_addon_templates($addon_scheme->getId());

        // Put this addon settings to the registry
        $settings = Settings::instance()->getValues($addon_scheme->getId(), Settings::ADDON_SECTION, false);
        if (!empty($settings)) {
            Registry::set('settings.' . $addon, $settings);
            $addon_data = Registry::get('addons.' . $addon);
            Registry::set('addons.' . $addon, fn_array_merge($addon_data, $settings));
        }

        // Add optional language variables
        $language_variables = $addon_scheme->getLanguageValues(false);
        if (!empty($language_variables)) {
            db_query('REPLACE INTO ?:language_values ?m', $language_variables);
        }

        // Get only original values
        $language_variables = $addon_scheme->getLanguageValues(true);
        if (!empty($language_variables)) {
            db_query('REPLACE INTO ?:original_values ?m', $language_variables);
        }

        if (fn_allowed_for('ULTIMATE')) {
            foreach (fn_get_all_companies_ids() as $company) {
                ProductTabs::instance($company)->createAddonTabs($addon_scheme->getId(), $addon_scheme->getTabOrder());
            }
        } else {
            ProductTabs::instance()->createAddonTabs($addon_scheme->getId(), $addon_scheme->getTabOrder());
        }
        
        // Execute custom functions
        if ($addon_scheme->callCustomFunctions('install') == false) {
            fn_uninstall_addon($addon, false);

            return false;
        }

        if ($show_notification == true) {
            fn_set_notification('N', __('notice'), __('text_addon_installed', array(
                '[addon]' => $addon_scheme->getName()
            )));
        }

        // If we need to activate addon after install, call "update status" procedure
        if ($addon_scheme->getStatus() != 'D') {
            fn_update_addon_status($addon, $addon_scheme->getStatus(), false);
        }

        if (file_exists(Registry::get('config.dir.addons') . $addon . '/layouts.xml')) {
            if (fn_allowed_for('ULTIMATE')) {
                foreach (fn_get_all_companies_ids() as $company) {
                    $layouts = Layout::instance($company)->getList();
                    foreach ($layouts as $layout_id => $layout) {
                        Exim::instance($company, $layout_id)->importFromFile(Registry::get('config.dir.addons') . $addon . '/layouts.xml');
                    }
                }
            } else {
                $layouts = Layout::instance()->getList();
                foreach ($layouts as $layout_id => $layout) {
                    Exim::instance(0, $layout_id)->importFromFile(Registry::get('config.dir.addons') . $addon . '/layouts.xml');
                }
            }
        }

        // Clean cache
        fn_clear_cache();



        if ($install_demo) {
            $addon_scheme->processQueries('demo', Registry::get('config.dir.addons') . $addon);
            if ($addon_scheme->callCustomFunctions('demo') == false) {
                fn_uninstall_addon($addon, false);

                return false;
            }
        }

        return true;
    } else {
        // Addon was not installed because scheme is not exists.
        return false;
    }
}

/**
 * Copies addon templates from repository
 *
 * @param string $addon_name Addons name to copy templates for
 * @return bool Always true
 */
function fn_install_addon_templates($addon_name)
{

    $installed_themes = fn_get_installed_themes();
    $design_dir = fn_get_theme_path('[themes]/', 'C');
    foreach ($installed_themes as $theme_name) {
        $manifest = Themes::factory($theme_name)->getRepoManifest();

        if (empty($manifest)) {
            $manifest = Themes::factory($theme_name)->getManifest();
        }

        if (isset($manifest['parent_theme'])) {
            if (empty($manifest['parent_theme'])) {
                $parent_path = fn_get_theme_path('[repo]/' . $theme_name . '/');
            } else {
                $parent_path = fn_get_theme_path('[repo]/' . $manifest['parent_theme'] . '/');
            }
        } else {
            $parent_path = fn_get_theme_path('[repo]/' . Registry::get('config.base_theme') . '/');
        }

        $repo_path = array(
            fn_get_theme_path('[repo]/basic' . '/'),
            $parent_path,
            fn_get_theme_path('[repo]/' . $theme_name . '/')
        );

        foreach ($repo_path as $path) {
            fn_copy_addon_templates_from_repo($path, $design_dir, $addon_name, $theme_name);
        }
    }

    return true;
}

/**
 * Copies files from base repository to store folder
 *
 * @param string $repo_dir Path to the repository
 * @param string $design_dir Path to store design folder
 * @param string $addon_name Name of installing add-on
 * @param string $theme_name Using theme name
 */
function fn_copy_addon_templates_from_repo($repo_dir, $design_dir, $addon_name, $theme_name)
{
    $paths = array(
        'templates/addons/' . $addon_name,
        'css/addons/' . $addon_name,
        'media/images/addons/' . $addon_name,

        // Copy Mail directory
        'mail/templates/addons/' . $addon_name,
        'mail/media/images/addons/' . $addon_name,
        'mail/css/addons/' . $addon_name,
    );

    foreach ($paths as $path) {
        if (is_dir($repo_dir . $path)) {
            fn_copy($repo_dir . $path, $design_dir . $theme_name . '/' . $path);
        }
    }
}

/**
 * Removes addon's templates from theme folder
 *
 * @param string $addon Addon name to remove templates for
 * @return bool Always true
 */
function fn_uninstall_addon_templates($addon)
{
    if (defined('DEVELOPMENT')) {
        return false;
    }

    $installed_themes = fn_get_installed_themes();
    $design_dir = fn_get_theme_path('[themes]/', 'C');

    foreach ($installed_themes as $theme_name) {
        $paths = array(
            $design_dir . $theme_name . '/templates/addons/' . $addon,
            $design_dir . $theme_name . '/css/addons/' . $addon,
            $design_dir . $theme_name . '/media/images/addons/' . $addon,
            $design_dir . $theme_name . '/mail/templates/addons/' . $addon,
            $design_dir . $theme_name . '/mail/media/images/addons/' . $addon,
            $design_dir . $theme_name . '/mail/css/addons/' . $addon,
        );

        foreach ($paths as $path) {
            if (is_dir($path)) {
                fn_rm($path);
            }
        }
    }

    return true;
}

/**
* Updates addon settings in database
*
* @param XmlScheme $addon_scheme Data from addon.xml file
* @return bool True in success, false otherwise
*/
function fn_update_addon_settings($addon_scheme, $execute_functions = true)
{
    $tabs = $addon_scheme->getSections();

    // If isset section settings in xml data and that addon settings is not exists
    if (!empty($tabs)) {
        Registry::set('runtime.database.skip_errors', true);

        // Create root settings section
        $addon_section_id = Settings::instance()->updateSection(array(
            'parent_id'    => 0,
            'edition_type' => $addon_scheme->getEditionType(),
            'name'         => $addon_scheme->getId(),
             'type'         => Settings::ADDON_SECTION,
        ));

        foreach ($tabs as $tab_index => $tab) {
            // Add addon tab as setting section tab
            $section_tab_id = Settings::instance()->updateSection(array(
                'parent_id'    => $addon_section_id,
                'edition_type' => $tab['edition_type'],
                'name'         => $tab['id'],
                'position'     => $tab_index * 10,
                 'type'         => isset($tab['separate']) ? Settings::SEPARATE_TAB_SECTION : Settings::TAB_SECTION,
            ));

            // Import translations for tab
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
                            Settings::instance()->updateValueById($setting_id, $setting['default_value'], null, $execute_functions);

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
        Registry::set('runtime.database.skip_errors', false);

        $errors = Registry::get('runtime.database.errors');
        if (!empty($errors)) {
            $error_text = '';
            foreach ($errors as $error) {
                $error_text .= '<br/>' . $error['message'] . ': <code>'. $error['query'] . '</code>';
            }
            fn_set_notification('E', __('addon_sql_error'), $error_text);

            return false;
        }
    }

    return true;
}

/**
 * Updates addon settings descriptions
 *
 * @param int $object_id Descriptions identifier
 * @param string $object_type Descriptions type (Settings::VARIANT_DESCRIPTION | Settings::SETTING_DESCRIPTION | Settings::SECTION_DESCRIPTION)
 * @param array $translations List of descriptions @see Settings::updateDescription()
 * @return bool Always true
 */
function fn_update_addon_settings_descriptions($object_id, $object_type, $translations)
{
    if (!empty($translations)) {
        foreach ($translations as $translation) {
            $translation['object_type'] = $object_type;
            $translation['object_id'] = $object_id;
            Settings::instance()->updateDescription($translation);
        }
    }

    return true;
}

/**
 * Checks if addon has correct shaphot
 *
 * @param string $addon Addon name (ID)
 * @return bool true if correct
 */
function fn_check_addon_snapshot($addon)
{
    static $addons_snapshots = array();
    static $mode = '';
    $status = true;

    if (empty($addons_snapshots)) {
        $addons_snapshots = fn_get_storage_data('addons_snapshots');
        $addons_snapshots = explode(',', $addons_snapshots);

        $mode = fn_get_storage_data('store_mode');
    }

    if ($mode == strrev('eerf') && in_array(md5($addon), $addons_snapshots)) {
        $status = false;
    }

    fn_set_hook('addon_snapshot', $addon, $status);

    return $status;
}

/**
 * Cleans up addons with incorrect snaphost
 *
 * @return bool Always true
 */
function fn_clean_up_addons()
{
    $_addons = db_get_hash_single_array("SELECT addon, status FROM ?:addons", array('addon', 'status'));
    $skipped_snapshots = fn_get_storage_data('skipped_snapshots');
    $skipped_snapshots = !empty($skipped_snapshots) ? explode(',', $skipped_snapshots) : array();

    foreach ($_addons as $addon => $status) {
        $snaphost = md5(str_rot13($addon));
        if (!fn_check_addon_snapshot($addon)) {
            if ($status == 'A') {
                fn_update_addon_status($addon, 'D');
                $skipped_snapshots[] = $snaphost;
            }
        } elseif (in_array($snaphost, $skipped_snapshots)) {
            fn_update_addon_status($addon, 'A');
            $skipped_snapshots = array_diff($skipped_snapshots, array($snaphost));
        }
    }

    $skipped_snapshots = array_unique($skipped_snapshots);
    fn_set_storage_data('skipped_snapshots', implode(',', $skipped_snapshots));

    return true;
}

/**
 * Updates addon status
 * @param string $addon Addon to update status for
 * @param string $status Status to change to
 * @param bool $show_notification Display notification if set to true
 * @param bool $on_install If status was changed right after install process
 * @return bool|string True on success, old status ID if status was not changed
 */
function fn_update_addon_status($addon, $status, $show_notification = true, $on_install = false)
{
    $old_status = db_get_field("SELECT status FROM ?:addons WHERE addon = ?s", $addon);
    $new_status = $status;

    $scheme = SchemesManager::getScheme($addon);

    // Unmanaged addons can be enabled/disabled via console only
    if ($scheme->getUnmanaged() && !defined('CONSOLE')) {
        return false;
    }

    if ($old_status != $new_status) {

        // Check if addon can be enabled
        $conflicts = db_get_fields("SELECT addon FROM ?:addons WHERE status = 'A' AND FIND_IN_SET(?s, conflicts)", $addon);
        if ($new_status == 'A' && !empty($conflicts)) {
            $scheme = SchemesManager::getScheme($addon);

            fn_set_notification('W', __('warning'), __('text_addon_cannot_enable', array(
                '[addons]' => implode(', ', SchemesManager::getNames($conflicts)),
                '[addon_name]' => $scheme->getName()
            )));

            return $old_status;
        }

        fn_get_schema('settings', 'actions.functions', 'php', true);

        $func = 'fn_settings_actions_addons_' . $addon;

        if (function_exists($func)) {
            $func($new_status, $old_status, $on_install);
        }

        // If status change is allowed, update it
        if ($old_status != $new_status) {
            if ($new_status != 'D') {
                // Check that addon have conflicts
                $scheme = SchemesManager::getScheme($addon);

                $conflicts = db_get_field("SELECT conflicts FROM ?:addons WHERE addon = ?s", $addon);

                if (!empty($conflicts)) {
                    $conflicts = explode(',', $conflicts);
                    $conflicted_addons = db_get_fields("SELECT addon FROM ?:addons WHERE addon IN (?a) AND status = 'A'", $conflicts);
                    if (!empty($conflicted_addons)) {
                        $lang_var = 'text_addon_confclicts_on_install';

                        if (!$on_install) {
                            foreach ($conflicts as $conflict) {
                                fn_disable_addon($conflict, $scheme->getName(), $show_notification);
                            }

                            $lang_var = 'text_addon_confclicts';
                        }

                        fn_set_notification('W', __('warning'), __($lang_var, array(
                            '[addons]' => implode(', ', SchemesManager::getNames($conflicts)),
                            '[addon_name]' => $scheme->getName()
                        )));

                        // On install we cannot enable addon with conflicts automaticly
                        if ($on_install) {
                            return $old_status;
                        }
                    }
                }
            }

            db_query("UPDATE ?:addons SET status = ?s WHERE addon = ?s", $status, $addon);

            $func = 'fn_settings_actions_addons_post_' . $addon;

            if (function_exists($func)) {
                $func($status);
            }

            if ($show_notification == true) {
                fn_set_notification('N', __('notice'), __('status_changed'));
            }

            // Enable/disable tabs for addon
            ProductTabs::instance()->updateAddonTabStatus($addon, $new_status);

            Registry::set('addons.' . $addon . '.status', $status);

        } else {
            return $old_status;
        }

    }

    // Clean cache
    fn_clear_cache();

    if ($status == 'A') {
        foreach (fn_get_installed_themes() as $theme_name) {

            $theme = Themes::factory($theme_name);
            $theme_manifest = $theme->getManifest();

            // Precompile addon LESS files if the theme has been converted to CSS
            if (!empty($theme_manifest['converted_to_css']) && !$theme->convertAddonToCss($addon)) {
                fn_update_addon_status($addon, 'D', $show_notification, $on_install);

                return $old_status;
            }
        }
    }

    return true;
}

/**
 * Returns addon's version
 * @param string $addon Addon name to return version for
 * @return string Addon's version
 */
function fn_get_addon_version($addon)
{
    return db_get_field("SELECT version FROM ?:addons where addon=?s", $addon);
}

/**
 * Gets addons list
 *
 * @param array $params search params
 * @param int $items_per_page items per page for pagination
 * @param string $lang_code language code
 * @return array addons list and filtered search params
 */
function fn_get_addons($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $params = LastView::instance()->update('addons', $params);

    $default_params = array(
        'type' => 'any',
    );

    $params = array_merge($default_params, $params);

    $addons = array();
    $sections =  Settings::instance()->getAddons();
    $all_addons = fn_get_dir_contents(Registry::get('config.dir.addons'), true, false);
    $installed_addons = db_get_hash_array(
        'SELECT a.addon, a.status, b.name as name, b.description as description, a.separate, a.unmanaged, a.has_icon '
        . 'FROM ?:addons as a LEFT JOIN ?:addon_descriptions as b ON b.addon = a.addon AND b.lang_code = ?s'
        . 'ORDER BY b.name ASC',
        'addon', $lang_code
    );

    foreach ($installed_addons as $key => $addon) {
        $installed_addons[$key]['has_sections'] = Settings::instance()->sectionExists($sections, $addon['addon']);
        $installed_addons[$key]['has_options'] = ($installed_addons[$key]['has_sections']) ? Settings::instance()->optionsExists($addon['addon'], 'ADDON') : false;

        // Check add-on snaphot
        if (!fn_check_addon_snapshot($key)) {
            $installed_addons[$key]['status'] = 'D';
            $installed_addons[$key]['snapshot_correct'] = false;
        } else {
            $installed_addons[$key]['snapshot_correct'] = true;
        }
    }

    foreach ($all_addons as $addon) {
        $addon_scheme = SchemesManager::getScheme($addon);

        if (in_array($params['type'], array('any', 'installed', 'active', 'disabled'))) {

            $search_status = $params['type'] == 'active' ? 'A' : ($params['type'] == 'disabled' ? 'D' : '');

            if (!empty($installed_addons[$addon])) {
                // exclude unmanaged addons from the list
                if ($installed_addons[$addon]['unmanaged'] == true) {
                    continue;
                }

                if (!empty($search_status) && $installed_addons[$addon]['status'] != $search_status) {
                    continue;
                }

                $addons[$addon] = $installed_addons[$addon];
                $addons[$addon]['delete_url'] = '';
                $addons[$addon]['url'] = fn_url("addons.update?addon=$addon&return_url=" . urlencode(Registry::get('config.current_url')));
                if (!Registry::get('runtime.company_id')) {
                    $addons[$addon]['delete_url'] = fn_url("addons.uninstall?addon=$addon&redirect_url=" . urlencode(Registry::get('config.current_url')));
                }

                if ($addon_scheme != false && !$addon_scheme->getUnmanaged()) {
                    $addons[$addon]['originals'] = $addon_scheme->getOriginals();
                }

                fn_update_lang_objects('installed_addon', $addons[$addon]);

                if (is_file(Registry::get('config.dir.addons') . $addon . '/func.php')) {
                    require_once(Registry::get('config.dir.addons') . $addon . '/func.php');

                    if (is_file(Registry::get('config.dir.addons') . $addon . '/config.php')) {
                        require_once(Registry::get('config.dir.addons') . $addon . '/config.php');
                    }

                    // Generate custom description
                    $func = 'fn_addon_dynamic_description_' . $addon;
                    if (function_exists($func)) {
                        $addons[$addon]['description'] = $func($addons[$addon]['description']);
                    }

                    //Generate custom url
                    $url_func = 'fn_addon_dynamic_url_' . $addon;
                    if (function_exists($url_func)) {
                        list($addons[$addon]['url'], $addons[$addon]['delete_url']) = $url_func($addons[$addon]['url'], $addons[$addon]['delete_url']);
                    }
                }
            }
        }

        if (empty($installed_addons[$addon]) && empty($params['for_company']) && (in_array($params['type'], array('any', 'not_installed')))) {
            if ($addon_scheme != false && !$addon_scheme->getUnmanaged()) {
                $addons[$addon] = array(
                    'status' => 'N', // Because it's not installed
                    'name' => $addon_scheme->getName(),
                    'snapshot_correct' => fn_check_addon_snapshot($addon),
                    'description' => $addon_scheme->getDescription(),
                    'has_icon' => $addon_scheme->hasIcon(),
                );
            }
        }
    }

    if (!empty($params['q'])) {
        foreach ($addons as $addon => $addon_data) {
            if (!preg_match('/' . preg_quote($params['q'], '/') . '/ui', $addon_data['name'], $m)) {
                unset($addons[$addon]);
            }
        }
    }

    $addons = fn_sort_array_by_key($addons, 'name', SORT_ASC);

    return array($addons, $params);
}

/**
 * Move addon pack from temporarily folder to specified place and install it if possible
 *
 * @param string $from Source path
 * @param string $to Destination path
 * @return bool true if installed, false otherwise
 */
function fn_addons_move_and_install($from, $to)
{
    if (defined('AJAX_REQUEST')) {
        Registry::get('ajax')->assign('non_ajax_notifications', true);
    }

    $struct = fn_get_dir_contents($from, false, true, '', '', true);
    $addon_name = '';

    foreach ($struct as $file) {
        if (preg_match('/app.+?addons[^a-zA-Z0-9_]+([a-zA-Z0-9_-]+).+?addon.xml$/i', $file, $matches)) {
            if (!empty($matches[1])) {
                $addon_name = $matches[1];
                break;
            }
        }
    }

    $relative_addon_path = str_replace(Registry::get('config.dir.root') . '/', '', Registry::get('config.dir.addons'));

    if (!file_exists($from . $relative_addon_path . $addon_name . '/addon.xml')) {
        fn_set_notification('E', __('error'), __('broken_addon_pack'));

        return false;
    }

    fn_copy($from, $to);

    fn_install_addon($addon_name);

    fn_rm($from);

    return true;
}

function fn_get_addon_permissions_text()
{
    $messages = array(
        'title' => __('text_full_mode_required'),
        'text' => __('text_forbidden_functionality')
    );

    fn_set_hook('addon_permissions_text', $messages);

    return $messages;
}

/**
 * Load addon
 *
 * @param string $addon_name addon name
 * @return boolean true if addon loaded, false otherwise
 */
function fn_load_addon($addon_name)
{
    static $cache = array(); // FIXME: duplicate with fn_set_hook

    if (!isset($cache[$addon_name])) {

        if (is_file(Registry::get('config.dir.addons') . $addon_name . '/init.php')) {
            include_once(Registry::get('config.dir.addons') . $addon_name . '/init.php');
        }
        if (file_exists(Registry::get('config.dir.addons') . $addon_name . '/func.php')) {
            include_once(Registry::get('config.dir.addons') . $addon_name . '/func.php');
        }
        if (file_exists(Registry::get('config.dir.addons') . $addon_name . '/config.php')) {
            include_once(Registry::get('config.dir.addons') . $addon_name . '/config.php');
        }

        Registry::get('class_loader')->add('', Registry::get('config.dir.addons') . $addon_name);

        $cache[$addon_name] = true;
    }

    return $cache[$addon_name];
}
