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

use Tygh\Addons\SchemesManager;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'next_step' && !empty($_REQUEST['current_step'])) {
        $steps = fn_get_schema('settings_wizard', 'steps');
        $current_step = $_REQUEST['current_step'];

        if (!isset($steps[$current_step])) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        foreach ($steps[$current_step]['items'] as $item) {
            if (!empty($item['post_handlers'])) {
                foreach ($item['post_handlers'] as $func_name => $args) {
                    $args = fn_settings_wizard_prepare_args($args, $_REQUEST);

                    call_user_func_array($func_name, $args);
                }
            }
        }

        if (!empty($_REQUEST['settings'])) {
            foreach ($_REQUEST['settings'] as $setting_name => $value) {
                Settings::instance()->updateValue($setting_name, $value);
            }
        }

        if (!empty($_REQUEST['addons'])) {
            foreach ($_REQUEST['addons'] as $addon_name => $enabled) {
                if ($enabled == 'Y') {
                    fn_install_addon($addon_name, false, false);
                }
            }
        }

        if ($action == 'finish' || empty($steps[$current_step]['next_step'])) {
            return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['return_url']);
        } else {
            return array(CONTROLLER_STATUS_REDIRECT, 'settings_wizard.view?current_step=' . $steps[$current_step]['next_step']);
        }

    }

    return array(CONTROLLER_STATUS_OK, '');
}

if ($mode == 'view') {
    $steps = fn_get_schema('settings_wizard', 'steps');

    if (empty($_REQUEST['current_step'])) {
        reset($steps);
        $current_step = key($steps);
    } else {
        $current_step = $_REQUEST['current_step'];
    }

    foreach ($steps[$current_step]['items'] as $item_id => $item_data) {
        if ($item_data['type'] == 'setting') {
            $steps[$current_step]['items'][$item_id]['setting_data'] = Settings::instance()->getSettingDataByName($item_data['setting_name']);
        }

        if ($item_data['type'] == 'addon') {
            $addon_scheme = SchemesManager::getScheme($item_data['addon_name']);
            if ($addon_scheme != false && !$addon_scheme->getUnmanaged()) {
                $addon = array(
                    'name' => $addon_scheme->getName(),
                    'description' => $addon_scheme->getDescription(),
                    'has_icon' => $addon_scheme->hasIcon()
                );
                Registry::set('wizard_addons.' . $item_data['addon_name'], $addon);
            }
        }

        if (!empty($item_data['pre_handlers'])) {
            foreach ($item_data['pre_handlers'] as $variable_name => $func_data) {
                foreach ($func_data as $func_name => $args) {
                    $args = fn_settings_wizard_prepare_args($args, $_REQUEST);

                    Registry::get('view')->assign($variable_name, call_user_func_array($func_name, $args));
                }
            }
        }
    }

    $current_step_position = array_search($current_step, array_keys($steps)) + 1;

    // Set navigation menu
    $sections = Registry::get('navigation.static.top.settings.items');
    fn_update_lang_objects('sections', $sections);

    Registry::set('navigation.dynamic.sections', $sections);
    Registry::set('navigation.dynamic.active_section', 'settings_wizard');

    Registry::get('view')->assign('wizard_addons', Registry::get('wizard_addons'));
    Registry::get('view')->assign('step_data', $steps[$current_step]);
    Registry::get('view')->assign('current_step', $current_step);
    Registry::get('view')->assign('popup_title', __('settings_wizard_title', array(
        '[current_step]' => $current_step_position,
        '[total_steps]' => count($steps)
    )));

    Registry::get('view')->assign('return_url', empty($_REQUEST['return_url']) ? fn_url() : $_REQUEST['return_url']);

} elseif ($mode == 'check_ssl') {
    $content = Http::get(fn_url('index.index?check_https=Y', 'A', 'https'));

    if (empty($content) || $content != 'OK') {
        Registry::get('view')->assign('checking_result', 'fail');
    } else {
        Registry::get('view')->assign('checking_result', 'ok');
    }

    Registry::get('view')->display('views/settings_wizard/components/ssl_checking.tpl');

    exit();

}

/**
 * Updates administrator password
 *
 * @param string $new_password Value of new password
 */
function fn_settings_wizard_update_password($new_password)
{
    if (!empty($new_password)) {
        $salt = fn_generate_salt();
        $password = fn_generate_salted_password($new_password, $salt);

        db_query('UPDATE ?:users SET salt = ?s, password = ?s WHERE user_id = ?i', $salt, $password, 1);
    }
}

/**
 * Sets store default currency
 *
 * @param string $default_currency Default currency code
 */
function fn_settings_wizard_set_default_currency($default_currency)
{
    db_query('UPDATE ?:currencies SET is_primary = ?s', 'N');
    db_query('UPDATE ?:currencies SET is_primary = ?s, coefficient = 1 WHERE currency_code = ?s', 'Y', $default_currency);
}

/**
 * Convert %params% to data from haystack
 * Example:
 *      $args = array('%test%', 'data', 3);
 *      $haystack = array('test' => 'some_text', 'data' => 123);
 *
 * Return:
 *      array('some_text', 'data', 3);
 *
 * @param array $args Data to be converted
 * @param array $haystack Data stack
 * @return array Processed data
 */
function fn_settings_wizard_prepare_args($args, $haystack)
{
    foreach ($args as $arg_id => $arg_data) {
        if (strpos($arg_data, '%') === 0) {
            $var_name = str_replace('%', '', $arg_data);
            if (isset($haystack[$var_name])) {
                $args[$arg_id] = $haystack[$var_name];
            } else {
                $args[$arg_id] = null;
            }
        }
    }

    return $args;
}

/**
 * Returns list of all store addons, exclude list of addons in params
 *
 * @return array Addons list
 */
function fn_settings_wizard_get_addons()
{
    $addons_list = array();
    $exclude_addons = func_get_args();

    $addons = fn_get_dir_contents(Registry::get('config.dir.addons'), true, false);
    $addons = array_diff($addons, $exclude_addons);

    foreach ($addons as $addon_id) {
        $addon_scheme = SchemesManager::getScheme($addon_id);
        if ($addon_scheme != false && !$addon_scheme->getUnmanaged()) {
            $addon = array(
                'name' => $addon_scheme->getName(),
                'addon_name' => $addon_id,
                'description' => $addon_scheme->getDescription(),
                'has_icon' => $addon_scheme->hasIcon()
            );

            $addons_list[$addon_id] = $addon;
        }
    }

    return $addons_list;
}
