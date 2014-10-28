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

use Tygh\Http;
use Tygh\Registry;

/**
 * Check if mod_rewrite is active and clean up templates cache
 */
function fn_settings_actions_addons_seo(&$new_value, $old_value)
{
    if ($new_value == 'A') {
        Http::get(Registry::get('config.http_location') . '/catalog.html?version');
        $headers = Http::getHeaders();

        if (strpos($headers, '200 OK') === false) {
            $new_value = 'D';
            fn_set_notification('W', __('warning'), __('warning_seo_urls_disabled'));
        }
    }

    fn_clear_cache();

    return true;
}

function fn_settings_actions_addons_seo_seo_product_type($new_value, $old_value)
{
    if (!empty($old_value) && $new_value != $old_value) {

        $redirect_only = false;
        $options = array('product_file', 'product_file_nohtml');
        if (in_array($new_value, $options) && in_array($old_value, $options)) {
            $redirect_only = true;
        }

        $options = array('product_category', 'product_category_nohtml');
        if (in_array($new_value, $options) && in_array($old_value, $options)) {
            $redirect_only = true;
        }

        fn_seo_settings_update('p', 'seo_product_type', $new_value, $redirect_only);
    }
}

function fn_settings_actions_addons_seo_seo_category_type($new_value, $old_value)
{
    if (!empty($old_value) && $new_value != $old_value) {

        $redirect_only = false;
        $options = array('category', 'file');
        if (in_array($new_value, $options) && in_array($old_value, $options)) {
            $redirect_only = true;
        }

        fn_seo_settings_update('c', 'seo_category_type', $new_value, $redirect_only);
    }
}

function fn_settings_actions_addons_seo_seo_page_type($new_value, $old_value)
{
    if (!empty($old_value) && $new_value != $old_value) {

        $redirect_only = false;
        $options = array('page', 'file');
        if (in_array($new_value, $options) && in_array($old_value, $options)) {
            $redirect_only = true;
        }

        fn_seo_settings_update('a', 'seo_page_type', $new_value, $redirect_only);
    }
}

function fn_seo_settings_update($type, $option, $new_value, $redirect_only)
{
    $i = 0;
    $items_per_pass = 100;
    $old_value = Registry::get('addons.seo.' . $option);

    $company_condition = fn_get_seo_company_condition('?:seo_names.company_id', $type);

    while ($update_data = db_get_array("SELECT * FROM ?:seo_names WHERE type = ?s ?p LIMIT $i, $items_per_pass", $type, $company_condition)) {
        foreach ($update_data as $data) {
            Registry::set('addons.seo.' . $option, $old_value);
            $url = fn_generate_seo_url_from_schema(array(
                'type' => $data['type'],
                'object_id' => $data['object_id'],
                'lang_code' => $data['lang_code']
            ), false);

            fn_seo_update_redirect(array(
                'src' => $url,
                'type' => $data['type'],
                'object_id' => $data['object_id'],
                'company_id' => $data['company_id'],
                'lang_code' => $data['lang_code']
            ), 0, false);

            if (!$redirect_only) {
                Registry::set('addons.seo.' . $option, $new_value);
                fn_create_seo_name($data['object_id'], $data['type'], $data['name'], 0, '', $data['company_id'], $data['lang_code'], true);
            }
        }
        $i += $items_per_pass;
    }
}
