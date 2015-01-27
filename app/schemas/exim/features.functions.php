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

function fn_exim_get_product_feature_categories($data, $lang_code)
{

    $categories = '';
    if (empty($data['Group'])) {

        $categories = fn_get_category_name($data['Categories'], $lang_code);

        if (is_array($categories)) {
            $categories = implode(',', $categories);
        }
    }

    return $categories;
}

/**
 * Convert Category names to its IDs
 * Example:
 *      IN array(
 *          'some_data' => ...,
 *          'categories_path' => 'Electronics,Processors'
 *      )
 *      OUT array(
 *          'some_data' => ...,
 *          'categories_path' => '12,52'
 *      )
 *
 * @param array $feature_data List of feature properties
 * @param string $lang_code 2-letters lang code
 * @return string Converted categories_path
 */
function fn_exim_get_features_convert_category_path($feature_data, $lang_code)
{
    if (!empty($feature_data['parent_id'])) {

        $categories_path = '';
        $parent_feature = fn_get_product_feature_data($feature_data['parent_id']);

        if (!empty($parent_feature['categories_path'])) {
            $categories_path = $parent_feature['categories_path'];
        }

    } else {
        if (!empty($feature_data['categories_path'])) {
            $categories_path = array();

            if (!isset($categories_ids)) {
                $categories_ids = db_get_hash_single_array('SELECT category, category_id FROM ?:category_descriptions WHERE lang_code = ?s', array('category', 'category_id'), $lang_code);
            }

            $categories = explode(',', $feature_data['categories_path']);

            if (!empty($categories)) {
                foreach ($categories as $category) {
                    if (!empty($categories_ids[$category])) {
                        $categories_path[] = $categories_ids[$category];
                    }
                }
            }
            $categories_path = implode(',', $categories_path);

        } else {
            $categories_path = '';
        }
    }

    return $categories_path;
}

function fn_exim_set_product_feature_categories($feature_id, $feature_data, $lang_code)
{
    static $categories_ids;

    $categories_path = fn_exim_get_features_convert_category_path($feature_data, $lang_code);

    db_query("UPDATE ?:product_features SET categories_path = ?s WHERE feature_id = ?i", $categories_path, $feature_id);

    if ($feature_data['feature_type'] == 'G') {
        db_query("UPDATE ?:product_features SET categories_path = ?s WHERE parent_id = ?i", $categories_path, $feature_id);
    }

    return true;
}

function fn_exim_get_product_feature_group($group_id, $lang_code = CART_LANGUAGE)
{
    $group_name = false;

    if (!empty($group_id)) {
        $group_name = db_get_field('SELECT description FROM ?:product_features_descriptions WHERE feature_id = ?i AND lang_code = ?s', $group_id, $lang_code);
    }

    return $group_name;
}

function fn_exim_get_product_feature_group_id($group_name, $company_id, &$new_groups, $lang_code = CART_LANGUAGE)
{
    $group_id = false;

    if (!empty($group_name)) {
        $group_id = db_get_field('SELECT feature_id FROM ?:product_features_descriptions WHERE description = ?s AND lang_code = ?s', $group_name, $lang_code);

        if (empty($group_id)) {
            $group_data = array(
                'feature_id' => 0,
                'description' => $group_name,
                'lang_code' => $lang_code,
                'feature_type' => 'G',
                'status' => 'A',
                'company_id' => $company_id
            );

            $group_id = fn_update_product_feature($group_data, 0, $lang_code);
            $new_groups[] = $group_id;

            if (fn_allowed_for('ULTIMATE')) {
                if (!empty($company_id)) {
                    fn_exim_update_share_feature($group_id, $company_id);
                }
            }
        }
    }

    return $group_id;
}

function fn_import_get_feature_id(&$primary_object_id, $object, &$skip_get_primary_object_id)
{

    $feature_id = db_get_field('SELECT feature_id FROM ?:product_features_descriptions WHERE description = ?s AND lang_code = ?s', $object['description'], $object['lang_code']);

    if ($feature_id) {
        $primary_object_id = array(
            'feature_id' => $feature_id
        );
        $skip_get_primary_object_id = true;
    }
}

function fn_import_feature($data, &$processed_data, &$skip_record)
{
    static $new_groups = array();
    $skip_record = true;

    $feature = reset($data);
    $langs = array_keys($data);
    $main_lang = reset($langs);

    if (Registry::get('runtime.company_id')) {
        $company_id = Registry::get('runtime.company_id');

    } else {

        if (!empty($feature['company'])) {
            $company_id = fn_get_company_id_by_name($feature['company']);
        } else {
            $company_id = isset($feature['company_id']) ? $feature['company_id'] : Registry::get('runtime.company_id');
        }
    }

    if (!empty($feature['feature_id'])) {
        $feature_id = db_get_field('SELECT ?:product_features.feature_id FROM ?:product_features WHERE feature_id = ?i', $feature['feature_id']);
    }

    $parent_id = fn_exim_get_product_feature_group_id($feature['parent_id'], $company_id, $new_groups, $main_lang);

    if (empty($feature_id)) {

        $condition = db_quote("WHERE description = ?s AND lang_code = ?s AND feature_type = ?s", $feature['description'], $main_lang, $feature['feature_type']);
        $condition .= db_quote(" AND parent_id = ?i", $parent_id);

        $feature_id = db_get_field(
            'SELECT ?:product_features.feature_id FROM ?:product_features_descriptions ' .
            'LEFT JOIN ?:product_features ON ?:product_features.feature_id = ?:product_features_descriptions.feature_id ' . $condition
        );
    }

    unset($feature['feature_id']);
    $feature['company_id'] = $company_id;
    $feature['parent_id'] = $parent_id;

    $feature['variants'] = array();
    if (!empty($feature['Variants'])) {
        $variants = fn_explode(',', $feature['Variants']);

        list($origin_variants) = fn_get_product_feature_variants(array('feature_id' => $feature_id), 0, $main_lang);
        $feature['original_var_ids'] = implode(',', array_keys($origin_variants));

        foreach ($variants as $variant) {
            $feature['variants'][]['variant'] = $variant;
        }
    }

    $skip = false;

    if (empty($feature_id)) {
        $feature_id = fn_update_product_feature($feature, 0, $main_lang);
        $processed_data['N']++;
        fn_set_progress('echo', __('updating') . ' features <b>' . $feature_id . '</b>. ', false);

    } else {
        if (!fn_check_company_id('product_features', 'feature_id', $feature_id)) {
            $processed_data['S']++;
            $skip = true;
        } else {
            // Convert categories from Names to C_IDS: Electronics,Processors -> 3,45
            $_data = $feature;
            $_data['categories_path'] = fn_exim_get_features_convert_category_path($feature, $main_lang);

            fn_update_product_feature($_data, $feature_id, $main_lang);

            if (in_array($feature_id, $new_groups)) {
                $processed_data['N']++;
            } else {
                $processed_data['E']++;
                fn_set_progress('echo', __('creating') . ' features <b>' . $feature_id . '</b>. ', false);
            }
        }
    }

    if (!$skip) {

        fn_exim_set_product_feature_categories($feature_id, $feature, $main_lang);

        foreach ($data as $lang_code => $feature_data) {
            unset($feature_data['feature_id']);
            db_query('UPDATE ?:product_features_descriptions SET ?u WHERE feature_id = ?i AND lang_code = ?s', $feature_data, $feature_id, $lang_code);
        }

        if (fn_allowed_for('ULTIMATE')) {
            if (!empty($company_id)) {
                fn_exim_update_share_feature($feature_id, $company_id);
            }
        }
    }

    return $feature_id;
}

function fn_exim_get_product_features_variants($feature_id, $lang_code)
{
    list($feature_variants) = fn_get_product_feature_variants(array('feature_id' => $feature_id), 0, $lang_code);

    $variants = array();
    foreach ($feature_variants as $variant) {
        $variants[] = $variant['variant'];
    }

    $variants = implode(', ', $variants);

    return $variants;
}

if (fn_allowed_for('ULTIMATE')) {

    function fn_exim_update_share_feature($feature_id, $company_id)
    {
        static $feature = array();

        if (!isset($feature[$company_id . '_' .$feature_id]) && !fn_check_shared_company_id('product_features', $feature_id, $company_id)) {
            fn_ult_update_share_object($feature_id, 'product_features', $company_id);
            $feature[$company_id . '_' .$feature_id] = true;
        }
    }

}
