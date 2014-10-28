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
use Tygh\Shippings\Shippings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

$_REQUEST['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : $_REQUEST['shipping_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '';

    //
    // Update shipping method
    //
    if ($mode == 'update') {
        if ((!empty($_REQUEST['shipping_id']) && fn_check_company_id('shippings', 'shipping_id', $_REQUEST['shipping_id'])) || empty($_REQUEST['shipping_id'])) {
            fn_set_company_id($_REQUEST['shipping_data']);
            $_REQUEST['shipping_id'] = fn_update_shipping($_REQUEST['shipping_data'], $_REQUEST['shipping_id']);

        }

        $_extra = empty($_REQUEST['destination_id']) ? '' : '&destination_id=' . $_REQUEST['destination_id'];
        $suffix = '.update?shipping_id=' . $_REQUEST['shipping_id'] . $_extra;
    }

    // Delete selected rates
    if ($mode == 'delete_rate_values') {
        if (fn_check_company_id('shippings', 'shipping_id', $_REQUEST['shipping_id'])) {
            foreach ($_REQUEST['delete_rate_data'] as $destination_id => $rates) {
                fn_delete_rate_values($rates, $_REQUEST['shipping_id'], $destination_id);
            }
        }

        $suffix = ".update?shipping_id=$_REQUEST[shipping_id]";
    }

    //
    // Update shipping methods
    //
    if ($mode == 'm_update') {

        if (!empty($_REQUEST['shipping_data']) && is_array($_REQUEST['shipping_data'])) {
            foreach ($_REQUEST['shipping_data'] as $k => $v) {
                if (empty($v)) {
                    continue;
                }

                if (fn_check_company_id('shippings', 'shipping_id', $k)) {
                    fn_update_shipping($v, $k);
                }
            }
        }

        $suffix .= '.manage';
    }

    if ($mode == 'test') {

        $shipping_data = $_REQUEST['shipping_data'];

        if (!empty($shipping_data['service_id']) && !empty($_REQUEST['shipping_id'])) {
            // Set package information (weight is only needed)
            $weight = floatval($shipping_data['test_weight']);
            $weight = !empty($weight) ? sprintf("%.2f", $weight) : '0.01';

            $package_info = array(
                'W' => $weight,
                'C' => 100,
                'I' => 1,
                'origination' => array(
                    'name' => Registry::get('settings.Company.company_name'),
                    'address' => Registry::get('settings.Company.company_address'),
                    'city' => Registry::get('settings.Company.company_city'),
                    'country' => Registry::get('settings.Company.company_country'),
                    'state' => Registry::get('settings.Company.company_state'),
                    'zipcode' => Registry::get('settings.Company.company_zipcode'),
                    'phone' => Registry::get('settings.Company.company_phone'),
                    'fax' => Registry::get('settings.Company.company_fax'),
                )
            );

            // Set default location
            $location = $package_info['location'] = fn_get_customer_location(array('user_id' => 0), array());
            $service_params = !empty($shipping_data['service_params']) ? $shipping_data['service_params'] : array();

            $shipping = Shippings::getShippingForTest($_REQUEST['shipping_id'], $shipping_data['service_id'], $service_params, $package_info);
            $rates = Shippings::calculateRates(array($shipping));

            Registry::get('view')->assign('data', $rates[0]);
            Registry::get('view')->assign('weight', $weight);
            Registry::get('view')->assign('service', db_get_field("SELECT description FROM ?:shipping_service_descriptions WHERE service_id = ?i AND lang_code = ?s", $shipping_data['service_id'], DESCR_SL));
        }

        Registry::get('view')->display('views/shippings/components/test.tpl');
        exit;

    }

    //
    // Delete shipping methods
    //
    //TODO make security check for company_id
    if ($mode == 'm_delete') {

        if (!empty($_REQUEST['shipping_ids'])) {
            foreach ($_REQUEST['shipping_ids'] as $id) {
                if (fn_check_company_id('shippings', 'shipping_id', $id)) {
                    fn_delete_shipping($id);
                }
            }
        }

        $suffix = '.manage';
    }

    return array(CONTROLLER_STATUS_OK, "shippings$suffix");
}

if ($mode == 'configure') {

    static $templates = array();
    static $addons_path = array();

    $shipping_id = !empty($_REQUEST['shipping_id']) ? $_REQUEST['shipping_id'] : 0;

    if (Registry::get('runtime.company_id')) {
        $shipping = db_get_row("SELECT company_id, service_params FROM ?:shippings WHERE shipping_id = ?i", $shipping_id);
        if ($shipping['company_id'] != Registry::get('runtime.company_id')) {
            exit;
        }
    }

    if (empty($templates)) {
        $templates = fn_get_dir_contents(fn_get_theme_path('[themes]/[theme]') . '/templates/views/shippings/components/services/', false, true, '.tpl');

        // Get addons templates as well
        $path = fn_get_theme_path('[themes]/[theme]') . '/templates/addons/[addon]/views/shippings/components/services/';

        $addons = Registry::get('addons');
        foreach ($addons as $addon_id => $addon) {
            $addon_path = str_replace('[addon]', $addon_id, $path);
            $addon_templates = fn_get_dir_contents($addon_path, false, true, '.tpl');

            if (!empty($addon_templates)) {
                $templates = array_merge($templates, $addon_templates);

                foreach ($addon_templates as $template) {
                    $addons_path[basename($template, '.tpl')] = str_replace('[addon]', $addon_id, 'addons/[addon]/views/shippings/components/services/');
                }
            }
        }
    }

    $module = !empty($_REQUEST['module']) ? $_REQUEST['module'] : '';

    if ($module && !in_array("$module.tpl", $templates)) {
        exit;
    }

    if (isset($shipping['service_params'])) {
        $shipping['service_params'] = unserialize($shipping['service_params']);
        if (empty($shipping['service_params'])) {
            $shipping['service_params'] = array();
        }
    } else {
        $shipping['service_params'] = fn_get_shipping_params($shipping_id);
    }
    Registry::get('view')->assign('shipping', $shipping);
    Registry::get('view')->assign('service_template', $module);
    Registry::get('view')->assign('addons_path', $addons_path);

    $code = !empty($_REQUEST['code']) ? $_REQUEST['code'] : '';
    Registry::get('view')->assign('code', $code);
// Add new shipping method
} elseif ($mode == 'add') {

    $rate_data = array(
        'rate_value' => array(
            'C' => array(),
            'W' => array(),
            'I' => array(),
        )
    );

    Registry::get('view')->assign('shipping_settings', Settings::instance()->getValues('Shippings'));
    Registry::get('view')->assign('services', fn_get_shipping_services());
    Registry::get('view')->assign('rate_data', $rate_data);
    Registry::get('view')->assign('taxes', fn_get_taxes());
    Registry::get('view')->assign('usergroups', fn_get_usergroups('C', DESCR_SL));

// Collect shipping methods data
} elseif ($mode == 'update') {
    $shipping = fn_get_shipping_info($_REQUEST['shipping_id'], DESCR_SL);

    if (empty($shipping)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if (Registry::get('runtime.company_id') && !fn_allowed_for('ULTIMATE')) {
        $company_data = Registry::get('runtime.company_data');

        if ((!in_array($_REQUEST['shipping_id'], explode(',', $company_data['shippings'])) && $shipping['company_id'] != Registry::get('runtime.company_id')) || ($shipping['company_id'] != Registry::get('runtime.company_id') && $shipping['company_id'] != 0)) {
            return array(CONTROLLER_STATUS_DENIED);
        }
    }

    if ($shipping['rate_calculation'] == 'M') {
        $rates_defined = db_get_hash_array("SELECT destination_id, IF(rate_value = '', 0, 1) as defined FROM ?:shipping_rates WHERE shipping_id = ?i", 'destination_id', $_REQUEST['shipping_id']);
        foreach ($shipping['rates'] as $rate_key => $rate) {
            if (!empty($rates_defined[$rate['destination_id']]['defined'])) {
                $shipping['rates'][$rate_key]['rate_defined'] = true;
            }
        }
    }

    Registry::get('view')->assign('shipping', $shipping);

    $tabs = array (
        'general' => array (
            'title' => __('general'),
            'js' => true
        ),
        'configure' => array (
            'title' => __('configure'),
            'ajax' => true,
        ),
        'shipping_charges' => array (
            'title' => __('shipping_charges'),
            'js' => true
        ),
    );

    $service = fn_get_shipping_service_data($shipping['service_id']);
    $shipping_settings = Settings::instance()->getValues('Shippings');
    if (!empty($shipping['rate_calculation']) && $shipping['rate_calculation'] == 'R' && !empty($service['module']) && $shipping_settings[$service['module'] . '_enabled'] == 'Y') {
        $tabs['configure']['href'] = 'shippings.configure?shipping_id=' . $shipping['shipping_id'] . '&module=' . $service['module'] . '&code=' . $service['code'];
        $tabs['configure']['hidden'] = 'N';
    } else {
        $tabs['configure']['hidden'] = 'Y';
    }

    if (Registry::get('runtime.company_id') && Registry::get('runtime.company_id') != $shipping['company_id']) {
        unset($tabs['configure']);
        Registry::get('view')->assign('hide_for_vendor', true);
    }

    Registry::set('navigation.tabs', $tabs);

    Registry::get('view')->assign('services', fn_get_shipping_services());
    Registry::get('view')->assign('taxes', fn_get_taxes());
    Registry::get('view')->assign('usergroups', fn_get_usergroups('C', DESCR_SL));

// Show all shipping methods
} elseif ($mode == 'manage') {

    $company_id = Registry::ifGet('runtime.company_id', null);
    Registry::get('view')->assign('shippings', fn_get_available_shippings($company_id));

    Registry::get('view')->assign('usergroups', fn_get_usergroups('C', DESCR_SL));

// Delete shipping method
} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['shipping_id']) && fn_check_company_id('shippings', 'shipping_id', $_REQUEST['shipping_id'])) {
        fn_delete_shipping($_REQUEST['shipping_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "shippings.manage");

// Delete selected rate
} elseif ($mode == 'delete_rate_value') {

    if (fn_check_company_id('shippings', 'shipping_id', $_REQUEST['shipping_id'])) {
        fn_delete_rate_values(array($_REQUEST['rate_type'] => array($_REQUEST['amount'] => 'Y')), $_REQUEST['shipping_id'], $_REQUEST['destination_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "shippings.update?shipping_id=$_REQUEST[shipping_id]&destination_id=$_REQUEST[destination_id]&selected_section=shipping_charges");
}

function fn_delete_rate_values($delete_rate_data, $shipping_id, $destination_id)
{
    $rate_values = db_get_field("SELECT rate_value FROM ?:shipping_rates WHERE shipping_id = ?i AND destination_id = ?i", $shipping_id, $destination_id);

    if (!empty($rate_values)) {
        $rate_values = unserialize($rate_values);
    }

    foreach ((array) $rate_values as $rate_type => $rd) {
        foreach ((array) $rd as $amount => $data) {
            if (isset($delete_rate_data[$rate_type][$amount]) && $delete_rate_data[$rate_type][$amount] == 'Y') {
                unset($rate_values[$rate_type][$amount]);
            }
        }
    }

    if (is_array($rate_values)) {
        foreach ($rate_values as $k => $v) {
            if ((count($v)==1) && (floatval($v[0]['value'])==0)) {
                unset($rate_values[$k]);
                continue;
            }
        }
    }

    if (fn_is_empty($rate_values)) {
            db_query("DELETE FROM ?:shipping_rates WHERE shipping_id = ?i AND destination_id = ?i", $shipping_id, $destination_id);
    } else {
        db_query("UPDATE ?:shipping_rates SET ?u WHERE shipping_id = ?i AND destination_id = ?i", array('rate_value' => serialize($rate_values)), $shipping_id, $destination_id);
    }
}

function fn_get_shipping_services()
{
    $shipping_settings = Settings::instance()->getValues('Shippings');

    $enabled_services = array();
    foreach ($shipping_settings as $setting_name => $val) {
        if (strpos($setting_name, '_enabled') !== false && $val == 'Y') {
            $enabled_services[] = str_replace('_enabled', '', $setting_name);
        }
    }

    $services = empty($enabled_services) ? array() : db_get_array("SELECT ?:shipping_services.*, ?:shipping_service_descriptions.description FROM ?:shipping_services LEFT JOIN ?:shipping_service_descriptions ON ?:shipping_service_descriptions.service_id = ?:shipping_services.service_id AND ?:shipping_service_descriptions.lang_code = ?s WHERE ?:shipping_services.module IN (?a) ORDER BY ?:shipping_service_descriptions.description", DESCR_SL, $enabled_services);

    return $services;
}
