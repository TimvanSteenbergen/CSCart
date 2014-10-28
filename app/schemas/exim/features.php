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

include_once(Registry::get('config.dir.schemas') . 'exim/features.functions.php');

$schema = array(
    'section' => 'features',
    'name' => __('features'),
    'pattern_id' => 'features',
    'key' => array('feature_id'),
    'order' => 0,
    'table' => 'product_features',
    'references' => array(
        'product_features_descriptions' => array(
            'reference_fields' => array('feature_id' => '#key', 'lang_code' => '#lang_code'),
            'join_type' => 'LEFT'
        ),
        'companies' => array(
            'reference_fields' => array('company_id' => '&company_id'),
            'join_type' => 'LEFT',
            'import_skip_db_processing' => true
        ),
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        )
    ),
    'condition' => array(
        'use_company_condition' => true,
    ),
    'export_fields' => array(
        'Feature name' => array(
            'table' => 'product_features_descriptions',
            'db_field' => 'description',
            'multilang' => true,
            'required' => true,
        ),
        'Feature ID' => array(
            'db_field' => 'feature_id',
            'alt_key' => true,
            'required' => true,
        ),
        'Language' => array(
            'table' => 'product_features_descriptions',
            'db_field' => 'lang_code',
            'type' => 'languages',
            'required' => true,
            'multilang' => true
        ),
        'Type' => array(
            'db_field' => 'feature_type',
            'required' => true,
        ),
        'Feature code' => array(
            'db_field' => 'feature_code',
        ),
        'Group' => array(
            'db_field' => 'parent_id',
            'process_get' => array('fn_exim_get_product_feature_group', '#this', '#lang_code'),
            'return_result' => true,
        ),
        'Description' => array(
            'table' => 'product_features_descriptions',
            'db_field' => 'full_description',
            'multilang' => true
        ),
        'Categories' => array(
            'db_field' => 'categories_path',
            'process_get' => array('fn_exim_get_product_feature_categories', '#row', '#lang_code'),
            'return_result' => true,
        ),
        'Variants' => array(
            'process_get' => array('fn_exim_get_product_features_variants', '#key', '#lang_code'),
            'linked' => false, // this field is not linked during import-export
            //'multilang' => true
        ),
        'Prefix' => array(
            'table' => 'product_features_descriptions',
            'db_field' => 'prefix',
            'multilang' => true
        ),
        'Suffix' => array(
            'table' => 'product_features_descriptions',
            'db_field' => 'suffix',
            'multilang' => true
        ),
        'Show on the features tab' => array(
            'db_field' => 'display_on_product',
        ),
        'Show in product list' => array(
            'db_field' => 'display_on_catalog',
        ),
        'Show in product header' => array(
            'db_field' => 'display_on_header',
        ),
        'Position' => array(
            'db_field' => 'position',
        ),
        'Comparsion' => array(
            'db_field' => 'comparison',
        ),
        'Status' => array(
            'db_field' => 'status',
        ),
    ),
);

$schema['import_process_data'] = array(
    'import_feature' => array(
        'function' => 'fn_import_feature',
        'args' => array('$data', '$processed_data', '$skip_record'),
        'import_only' => true,
    ),
);

if (fn_allowed_for('ULTIMATE')) {
    $schema['export_fields']['Store'] = array(
        'table' => 'companies',
        'db_field' => 'company',
    );

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Store']['required'] = true;
    }
}

return $schema;
