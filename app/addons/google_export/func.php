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

function fn_google_export_add_features()
{
    $lang = DEFAULT_LANGUAGE;
    $new_features = fn_google_export_get_new_features_list();

    if (fn_allowed_for('ULTIMATE')) {
        $company_id = fn_get_default_company_id();
        $parent_feature_id = db_query(
            "INSERT INTO ?:product_features"
            . " (feature_type, categories_path, parent_id, display_on_product, display_on_catalog, status, position, comparison, company_id)"
            . " VALUES"
            . " ('G', '', 0, 0, 0, 'A', 0, 'N', " . $company_id . ")"
        );
        fn_share_object_to_all('product_features', $parent_feature_id);
    } else {
        $parent_feature_id = db_query(
            "INSERT INTO ?:product_features"
            . " (feature_type, categories_path, parent_id, display_on_product, display_on_catalog, status, position, comparison)"
            . " VALUES"
            . " ('G', '', 0, 0, 0, 'A', 0, 'N')"
        );
    }
    db_query(
        "INSERT INTO ?:product_features_descriptions"
        . " (feature_id, description, full_description, prefix, suffix, lang_code)"
        . " VALUES"
        . " (?i, 'Google export features', '', '', '', ?s)",
        $parent_feature_id, $lang
    );

    fn_google_export_add_feature($new_features, $parent_feature_id);

    fn_google_export_update_alt_languages('product_features_descriptions', 'feature_id');
    fn_google_export_update_alt_languages('product_feature_variant_descriptions', 'variant_id');
}

function fn_google_export_add_feature($new_features, $parent_feature_id, $show_process = false, $lang = DEFAULT_LANGUAGE)
{
    static $company_id = 0;

    if (!$company_id) {
        $company_id = fn_get_default_company_id();
    }

    foreach ($new_features as $feature_name => $feature_data) {
        foreach ($feature_data as $feature_type => $feature_variants) {
            if (fn_allowed_for('ULTIMATE')) {
                $f_id = db_query(
                    "INSERT INTO ?:product_features"
                    . " (feature_type, categories_path, parent_id, display_on_product, display_on_catalog, status, position, comparison, company_id)"
                    . " VALUES"
                    . " (?s, '', ?i, 0, 0, 'A', 0, 'N', ?i)",
                    $feature_type, $parent_feature_id, $company_id
                );
                fn_share_object_to_all('product_features', $f_id);
            } else {
                $f_id = db_query(
                    "INSERT INTO ?:product_features"
                    . " (feature_type, categories_path, parent_id, display_on_product, display_on_catalog, status, position, comparison)"
                    . " VALUES"
                    . " (?s, '', ?i, 0, 0, 'A', 0, 'N')",
                    $feature_type, $parent_feature_id
                );
            }
            db_query(
                "INSERT INTO ?:product_features_descriptions"
                . " (feature_id, description, full_description, prefix, suffix, lang_code)"
                . " VALUES"
                . " (?i, ?s, '', '', '', ?s)",
                $f_id, $feature_name, $lang
            );
            if ($show_process) {
                fn_echo(' .');
            }
            fn_google_export_add_feature_variants($f_id, $feature_variants, $show_process);
        }
    }
}

function fn_google_export_add_feature_variants($feature_id, $feature_variants, $show_process = false, $lang_code = DEFAULT_LANGUAGE)
{
    if (empty($feature_variants)) {
        return;
    }

    foreach ($feature_variants as $key => $val) {
        if ($show_process && ($key % 100 == 0)) {
            fn_echo(' .');
        }
        $variant_id = db_query("INSERT INTO ?:product_feature_variants (feature_id, position) VALUES (?i, 0)", $feature_id);
        db_query("INSERT INTO ?:product_feature_variant_descriptions (variant_id, variant, lang_code) VALUES (?i, ?s, ?s);", $variant_id, $val, $lang_code);
    }
}

function fn_get_google_categories($lang_code = DEFAULT_LANGUAGE)
{
    $urls = fn_google_export_available_categories();

    if (empty($urls[$lang_code])) {
        return false;
    }
    $url = $urls[$lang_code];

    $content = fn_get_contents($url);
    if ($content) {
        $result = explode("\n", $content);
        $result = array_diff($result, array(''));

        return array_slice($result, 1);
    }

    return false;
}

function fn_google_export_remove_features()
{
    $features = fn_google_export_get_new_features_list();
    $parent_feature_id = db_get_field("SELECT ?:product_features_descriptions.feature_id FROM ?:product_features_descriptions LEFT JOIN ?:product_features ON ?:product_features_descriptions.feature_id = ?:product_features.feature_id WHERE ?:product_features_descriptions.description = 'Google export features' AND ?:product_features_descriptions.lang_code = ?s AND ?:product_features.feature_type = 'G' AND ?:product_features.parent_id = 0", DEFAULT_LANGUAGE);
    foreach ($features as $feature_name => $feature_data) {
        $f_id = db_get_field("SELECT ?:product_features_descriptions.feature_id FROM ?:product_features_descriptions LEFT JOIN ?:product_features ON ?:product_features_descriptions.feature_id = ?:product_features.feature_id WHERE ?:product_features_descriptions.description = ?s AND ?:product_features_descriptions.lang_code = ?s AND ?:product_features.parent_id = ?i", $feature_name, DEFAULT_LANGUAGE, $parent_feature_id);
        if (!empty($f_id)) {
            fn_delete_feature($f_id);
        }
    }
    fn_delete_feature($parent_feature_id);
    fn_google_export_remove_additional_google_categories();
}

function fn_google_export_get_new_features_list()
{
    return array (
        'GTIN' => array (
            'T' => array()
        ),
        'MPN' => array (
            'T' => array()
        ),
        'Brand' => array (
            'T' => array()
        ),
        'Availability' => array (
            'S' => array (
                'in stock',
                'available for order',
                'out of stock',
                'preorder'
            )
        ),
        'Condition' => array (
            'S' => array (
                'new',
                'used',
                'refurbished'
            )
        ),
        'Google product category (US)' => array (
            'S' => fn_get_google_categories()
        )
    );
}

function fn_google_export_add_feed()
{
    $fields = array (
        array (
            'position' => 0,
            'export_field_name' => 'id',
            'field' => 'Product id',
            'avail' => 'Y'
        ),
        array (
            'position' => 0,
            'export_field_name' => 'title',
            'field' => 'Product name',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'link',
            'field' => 'Product URL',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'description',
            'field' => 'Google description',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'condition',
            'field' => 'Condition',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'price',
            'field' => 'Price',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'availability',
            'field' => 'Availability',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'image_link',
            'field' => 'Image URL',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'gtin',
            'field' => 'GTIN',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'brand',
            'field' => 'Brand',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'mpn',
            'field' => 'MPN',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'google_product_category',
            'field' => 'Google product category (US)',
            'avail' => 'Y'
        ),
        Array (
            'position' => 0,
            'export_field_name' => 'product_type',
            'field' => 'Category',
            'avail' => 'Y'
        )
    );

    $export_options = array (
        'lang_code' => array(DEFAULT_LANGUAGE),
        'category_delimiter' => ' > ',
        'features_delimiter' => '///',
        'price_dec_sign_delimiter' => '.'
    );

    $data = array (
        'categories' => '',
        'products' => '',
        'fields' => serialize($fields),
        'export_location' => '',
        'export_by_cron' => 'N',
        'ftp_url' => '',
        'ftp_user' => '',
        'ftp_pass' => '',
        'file_name' => 'google_base.csv',
        'enclosure' => '',
        'csv_delimiter' => 'T',
        'exclude_disabled_products' => 'N',
        'export_options' => serialize($export_options),
        'save_dir' => fn_get_files_dir_path(),
        'status' => 'A'
    );
    $data_feed_id = db_query("INSERT INTO ?:data_feeds ?e", $data);

    foreach (fn_get_translation_languages() as $language) {
        db_query(
            "INSERT INTO ?:data_feed_descriptions (datafeed_id, datafeed_name, lang_code) VALUES (?i, 'Google base', ?s);",
            $data_feed_id, $language['lang_code']
        );
    }
}

function fn_google_export_remove_feed()
{
    $data_feed_id = db_get_field("SELECT datafeed_id FROM ?:data_feed_descriptions WHERE datafeed_name = 'Google base' AND lang_code = ?s", DESCR_SL);
    if (!empty($data_feed_id)) {
        db_query('DELETE FROM ?:data_feeds WHERE datafeed_id = ?i', $data_feed_id);
        db_query('DELETE FROM ?:data_feed_descriptions WHERE datafeed_id = ?i', $data_feed_id);
    }
}

function fn_google_export_update_alt_languages($table, $keys, $show_process = false)
{
    $langs = fn_get_translation_languages();

    if (empty($langs)) {
        $langs = db_get_fields("SELECT lang_code FROM ?:languages");
    } else {
        $langs = array_keys($langs);
    }

    if (!is_array($keys)) {
        $keys = array($keys);
    }

    $i = 0;
    $step = 50;
    while ($items = db_get_array("SELECT * FROM ?:$table WHERE lang_code = ?s LIMIT $i, $step", DEFAULT_LANGUAGE)) {
        if ($show_process) {
            fn_echo(' .');
        }
        $i += $step;
        foreach ($items as $v) {
            foreach ($langs as $lang) {
                $condition = array();
                foreach ($keys as $key) {
                    $condition[] = "$key = '" . $v[$key] . "'";
                }
                $condition = implode(' AND ', $condition);
                $exists = db_get_field("SELECT COUNT(*) FROM ?:$table WHERE $condition AND lang_code = ?s", $lang);
                if (empty($exists)) {
                    $v['lang_code'] = $lang;
                    db_query("REPLACE INTO ?:$table ?e", $v);
                }
            }
        }
    }
}

function fn_settings_actions_addons_google_export_additional_langs($new_value, $old_value)
{
    if ($new_value != $old_value) {
        if ($new_value == 'Y') {
            fn_google_export_add_additional_google_categories();
        } else {
            fn_google_export_remove_additional_google_categories();
        }
    }
}

function fn_google_export_add_additional_google_categories()
{
    $available_langs = array_keys(fn_google_export_available_categories());
    fn_echo(__('google_export_start_import'));
    foreach ($available_langs as $lang) {
        $new_feature = array (
            "Google product category ($lang)" => array (
                'S' => fn_get_google_categories($lang)
            )
        );
        $parent_feature_id = db_get_field("SELECT feature_id FROM ?:product_features_descriptions WHERE description = 'Google export features' AND lang_code = ?s", DEFAULT_LANGUAGE);
        fn_google_export_add_feature($new_feature, $parent_feature_id, true);
        fn_google_export_update_alt_languages('product_features_descriptions', 'feature_id', true);
        fn_google_export_update_alt_languages('product_feature_variant_descriptions', 'variant_id', true);
    }
}

function fn_google_export_remove_additional_google_categories()
{
    $available_langs = array_keys(fn_google_export_available_categories());

    foreach ($available_langs as $lang) {
        $feature_id = db_get_field("SELECT feature_id FROM ?:product_features_descriptions WHERE description = 'Google product category ($lang)' AND lang_code = ?s", DEFAULT_LANGUAGE);
        if (!empty($feature_id)) {
            fn_delete_feature($feature_id);
        }
    }
}

function fn_google_export_generate_info()
{
    return __('google_export_general_info');
}

function fn_google_export_available_categories()
{
    return array(
        'ru' => 'http://www.google.com/basepages/producttype/taxonomy.ru-RU.txt',
        'en' => 'http://www.google.com/basepages/producttype/taxonomy.en-US.txt',
        'FR' => 'http://www.google.com/basepages/producttype/taxonomy.fr-FR.txt',
        'DE' => 'http://www.google.com/basepages/producttype/taxonomy.de-DE.txt',
        'IT' => 'http://www.google.com/basepages/producttype/taxonomy.it-IT.txt',
        'NL' => 'http://www.google.com/basepages/producttype/taxonomy.nl-NL.txt',
        'ES' => 'http://www.google.com/basepages/producttype/taxonomy.es-ES.txt',
        'GB' => 'http://www.google.com/basepages/producttype/taxonomy.en-GB.txt'
    );
}
