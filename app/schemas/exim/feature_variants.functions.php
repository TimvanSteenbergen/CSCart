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

if (fn_allowed_for('ULTIMATE')) {

    function fn_import_check_feature_variants_company_id(&$primary_object_id, &$object, &$processed_data, &$skip_record)
    {
        if (!empty($primary_object_id)) {

            $feature_id = db_get_field('SELECT feature_id FROM ?:product_feature_variants WHERE variant_id = ?i', $primary_object_id['variant_id']);

            if (empty($feature_id)) {
                $processed_data['S']++;
                $skip_record = true;
            } else {
                if (Registry::get('runtime.company_id') && !Registry::get('runtime.simple_ultimate')) {
                     $feature = fn_get_product_feature_data($feature_id);

                     if (isset($feature['company_id']) && $feature['company_id'] != Registry::get('runtime.company_id')) {
                         $processed_data['S']++;
                         $skip_record = true;
                     }
                }
            }
        }
    }

}

function fn_import_feature_variant($data, $options, &$processed_data, &$skip_record)
{

    static $features;
    $skip_record = true;

    $variant = reset($data);

    if (empty($variant['Feature name'])) {
        return false;
    }

    $langs = array_keys($data);
    $main_lang = reset($langs);

    if (!empty($variant['Feature group'])) {
        $feature_group = fn_exim_get_feature_data_by_name($variant['Feature group'], '');
        $variant['parent_id'] = $feature_group['feature_id'];
    } else {
        $variant['parent_id'] = 0;
    }

    $feature = fn_exim_get_feature_data_by_name($variant['Feature name'], $variant['Feature group']);

    if (empty($feature)) {
        $processed_data['S']++;

        return false;
    }

    $feature_id = $feature['feature_id'];
    $company_id = $feature['company_id'];

    if (Registry::get('runtime.company_id') && Registry::get('runtime.company_id') != $company_id) {
        $processed_data['S']++;

        return false;
    }

    if (!isset($features)) {
        list($features) = fn_get_product_features(array('plain' => true), 0, $main_lang);
    }

    if (!empty($feature_id)) {

        if (isset($variant['variant_id'])) {
            $variant_id = db_get_field('SELECT variant_id FROM ?:product_feature_variants WHERE variant_id = ?i', $variant['variant_id']);
        }

        if (empty($variant_id)) {
            $join = db_quote('INNER JOIN ?:product_feature_variants fv ON fv.variant_id = fvd.variant_id');
            $variant_id = db_get_field("SELECT fvd.variant_id FROM ?:product_feature_variant_descriptions AS fvd $join WHERE variant = ?s AND feature_id = ?i", $variant['variant'], $feature_id);
        }

        $new_variant_id = fn_update_product_feature_variant($feature_id, $features[$feature_id]['feature_type'], $variant, $main_lang);

        if ($variant_id == $new_variant_id) {
            $processed_data['E']++;
        } else {
            $processed_data['N']++;
            $variant_id = $new_variant_id;
        }

        foreach ($data as $lang_code => $variant) {
            fn_update_product_feature_variant($feature_id, $features[$feature_id]['feature_type'], $variant, $lang_code);
        }

        if (!empty($variant['image_id'])) {
            fn_import_images($options['images_path'], $variant['image_id'], '', 0, 'V', $variant_id, 'feature_variant');
        }
    }

    return $variant_id;

}

function fn_exim_get_feature_data_by_name($feature_name, $parent_name)
{
    if (!empty($parent_name)) {
        $parent = fn_exim_get_feature_data_by_name($parent_name, '');
        $parent_id = $parent['feature_id'];
    } else {
        $parent_id = 0;
    }

    $join = "INNER JOIN ?:product_features AS pf ON pf.feature_id = pfd.feature_id";

    return db_get_row("SELECT pfd.feature_id, company_id FROM ?:product_features_descriptions AS pfd $join WHERE description = ?s AND parent_id = ?i", $feature_name, $parent_id);
}

function fn_exim_get_feature_name($variant_id, $lang_code)
{
    $join = "INNER JOIN ?:product_feature_variants AS pfv ON pfd.feature_id = pfv.feature_id";

    return db_get_field("SELECT description FROM ?:product_features_descriptions AS pfd $join WHERE variant_id = ?i AND lang_code = ?s", $variant_id, $lang_code);
}

function fn_exim_get_product_feature_group_name($variant_id, $lang_code)
{
    $join = " INNER JOIN ?:product_features AS pf ON pfd.feature_id = pf.parent_id ";
    $join .= " INNER JOIN ?:product_feature_variants AS pfv ON pf.feature_id = pfv.feature_id ";

    return db_get_field("SELECT description FROM ?:product_features_descriptions AS pfd $join WHERE variant_id = ?i AND lang_code = ?s", $variant_id, $lang_code);
}
