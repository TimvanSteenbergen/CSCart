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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

use Tygh\Settings;
use Tygh\Registry;

function fn_get_store_locations($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $default_params = array (
        'page' => 1,
        'q' => '',
        'match' => 'any',
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $fields = array (
        '?:store_locations.*',
        '?:store_location_descriptions.*',
        '?:country_descriptions.country as country_title'
    );

    $join = db_quote(" LEFT JOIN ?:store_location_descriptions ON ?:store_locations.store_location_id = ?:store_location_descriptions.store_location_id AND ?:store_location_descriptions.lang_code = ?s", $lang_code);

    $join .= db_quote(" LEFT JOIN ?:country_descriptions ON ?:store_locations.country = ?:country_descriptions.code AND ?:country_descriptions.lang_code = ?s", $lang_code);

    $condition = 1;

    if (AREA == 'C') {
        $condition .= " AND status = 'A'";
    }

    // Search string condition for SQL query
    if (!empty($params['q'])) {

        if ($params['match'] == 'any') {
            $pieces = explode(' ', $params['q']);
            $search_type = ' OR ';
        } elseif ($params['match'] == 'all') {
            $pieces = explode(' ', $params['q']);
            $search_type = ' AND ';
        } else {
            $pieces = array($params['q']);
            $search_type = '';
        }

        $_condition = array();
        foreach ($pieces as $piece) {
            $tmp = db_quote("?:store_location_descriptions.name LIKE ?l", "%$piece%"); // check search words

            $tmp .= db_quote(" OR ?:store_location_descriptions.description LIKE ?l", "%$piece%");

            $tmp .= db_quote(" OR ?:store_location_descriptions.city LIKE ?l", "%$piece%");

            $tmp .= db_quote(" OR ?:country_descriptions.country LIKE ?l", "%$piece%");

            $_condition[] = '(' . $tmp . ')';
        }

        $_cond = implode($search_type, $_condition);

        if (!empty($_condition)) {
            $condition .= ' AND (' . $_cond . ') ';
        }

        unset($_condition);
    }

    $condition .= (AREA == 'C' && defined('CART_LOCALIZATION')) ? fn_get_localizations_condition('?:store_locations.localization') : '';

    $sorting = "?:store_locations.position, ?:store_location_descriptions.name";

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(?:store_locations.store_location_id) FROM ?:store_locations ?p WHERE ?p", $join, $condition);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $data = db_get_array('SELECT ?p FROM ?:store_locations ?p WHERE ?p GROUP BY ?:store_locations.store_location_id ORDER BY ?p ?p', implode(', ', $fields), $join, $condition, $sorting, $limit);

    return array($data, $params);

}

function fn_get_store_location($store_location_id, $lang_code = CART_LANGUAGE)
{
    $fields = array (
        '?:store_locations.*',
        '?:store_location_descriptions.*',
        '?:country_descriptions.country as country_title'
    );

    $join = db_quote(" LEFT JOIN ?:store_location_descriptions ON ?:store_locations.store_location_id = ?:store_location_descriptions.store_location_id AND ?:store_location_descriptions.lang_code = ?s", $lang_code);
    $join .= db_quote(" LEFT JOIN ?:country_descriptions ON ?:store_locations.country = ?:country_descriptions.code AND ?:country_descriptions.lang_code = ?s", $lang_code);

    $condition = db_quote(" ?:store_locations.store_location_id = ?i ", $store_location_id);
    $condition .= (AREA == 'C' && defined('CART_LOCALIZATION')) ? fn_get_localizations_condition('?:store_locations.localization') : '';

    $store_location = db_get_row('SELECT ?p FROM ?:store_locations ?p WHERE ?p', implode(', ', $fields), $join, $condition);

    return $store_location;
}

function fn_get_store_location_name($store_location_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($store_location_id)) {
        return db_get_field('SELECT `name` FROM ?:store_location_descriptions WHERE store_location_id = ?i AND lang_code = ?s', $store_location_id, $lang_code);
    }

    return false;
}

function fn_update_store_location($store_location_data, $store_location_id, $lang_code = DESCR_SL)
{
    $store_location_data['localization'] = empty($store_location_data['localization']) ? '' : fn_implode_localizations($store_location_data['localization']);

    if (empty($store_location_id)) {
        if (empty($store_location_data['position'])) {
            $store_location_data['position'] = db_get_field('SELECT MAX(position) FROM ?:store_locations');
            $store_location_data['position'] += 10;
        }

        $store_location_id = db_query('INSERT INTO ?:store_locations ?e', $store_location_data);

        $store_location_data['store_location_id'] = $store_location_id;

        foreach (fn_get_translation_languages() as $store_location_data['lang_code'] => $v) {
            db_query("INSERT INTO ?:store_location_descriptions ?e", $store_location_data);
        }
    } else {
        db_query('UPDATE ?:store_locations SET ?u WHERE store_location_id = ?i', $store_location_data, $store_location_id);
        db_query('UPDATE ?:store_location_descriptions SET ?u WHERE store_location_id = ?i AND lang_code = ?s', $store_location_data, $store_location_id, $lang_code);
    }

    return $store_location_id;
}

function fn_delete_store_location($store_location_id)
{
    $deleted = true;

    $affected_rows = db_query('DELETE FROM ?:store_locations WHERE store_location_id = ?i', $store_location_id);
    db_query('DELETE FROM ?:store_location_descriptions WHERE store_location_id = ?i', $store_location_id);

    if (empty($affected_rows)) {
        $deleted = false;
    }

    return $deleted;
}

function fn_store_locator_google_langs($lang_code)
{
    $supported_langs = array ('en', 'eu', 'ca', 'da', 'nl', 'fi', 'fr', 'gl', 'de', 'el', 'it', 'ja', 'no', 'nn', 'ru' , 'es', 'sv', 'th');

    if (in_array($lang_code, $supported_langs)) {
        return $lang_code;
    }

    return '';
}

function fn_store_locator_yandex_langs($lang_code)
{
    $supported_langs = array ('en' => 'en-US', 'tr' => 'tr-TR', 'ru' => 'ru-RU');
    $default_lang_code = 'en';

    if (isset($supported_langs[$lang_code])) {
        return $supported_langs[$lang_code];
    }

    return $supported_langs[$default_lang_code];
}

function fn_store_locator_get_info()
{
    $text = '<a href="http://code.google.com/apis/maps/signup.html">' . __('singup_google_url') . '</a>';

    return $text;
}

function fn_get_store_locator_settings($company_id = null)
{
    static $cache;

    if (empty($cache['settings_' . $company_id])) {
        $settings = Settings::instance()->getValue('store_locator_', '', $company_id);
        $settings = unserialize($settings);

        if (empty($settings)) {
            $settings = array();
        }

        $cache['settings_' . $company_id] = $settings;
    }

    return $cache['settings_' . $company_id];
}

function fn_get_store_locator_map_templates($area)
{
    $templates = array();

    if (empty($area) || !in_array($area, array('A', 'C'))) {
        return $templates;
    }

    $skin_path = fn_get_theme_path('[themes]/[theme]', $area);
    $relative_directory_path = 'addons/store_locator/views/store_locator/components/maps/';
    $template_path =  $skin_path . '/templates/' . $relative_directory_path;
    $_templates = fn_get_dir_contents($template_path, false, true, '.tpl');

    if (!empty($_templates)) {
        foreach ($_templates as $template) {
            $template_provider = str_replace('.tpl', '', strtolower($template)); // Get provider name
            $templates[$template_provider] = $relative_directory_path . $template;
        }
    }

    return $templates;
}

if (fn_allowed_for('ULTIMATE')) {
    function fn_store_locator_ult_check_store_permission($params, &$object_type, &$object_name, &$table, &$key, &$key_id)
    {
        if (Registry::get('runtime.controller') == 'store_locator' && !empty($params['store_location_id'])) {
            $key = 'store_location_id';
            $key_id = $params[$key];
            $table = 'store_locations';
            $object_name = fn_get_store_location_name($key_id, DESCR_SL);
            $object_type = __('store_locator');
        }
    }
}
