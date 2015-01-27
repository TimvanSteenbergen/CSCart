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

include_once(Registry::get('config.dir.schemas') . 'exim/feature_variants.functions.php');
include_once(Registry::get('config.dir.schemas') . 'exim/features.functions.php');

$schema = array(
    'section' => 'features',
    'name' => __('variants'),
    'pattern_id' => 'feature_variants',
    'key' => array('variant_id'),
    'order' => 1,
    'table' => 'product_feature_variants',
    'references' => array(
        'product_feature_variant_descriptions' => array(
            'reference_fields' => array('variant_id' => '#key', 'lang_code' => '#lang_code'),
            'join_type' => 'LEFT'
        ),
        'images_links' => array(
            'reference_fields' => array('object_id' => '#key', 'object_type' => 'feature_variant', 'type' => 'V'),
            'join_type' => 'LEFT'
        )
    ),
    'import_process_data' => array(
        'import_feature_variant' => array(
            'function' => 'fn_import_feature_variant',
            'args' => array('$data', '$options', '$processed_data', '$skip_record'),
            'import_only' => true,
        )
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        ),
        'images_path' => array(
            'title' => 'images_directory',
            'description' => 'text_images_directory',
            'type' => 'input',
            'default_value' => 'exim/backup/variants_image/',
            'notes' => __('text_file_editor_notice', array('[href]' => fn_url('file_editor.manage?active_section=files&selected_path=/'))),
        ),
    ),
    'order_by' => 'feature_id, variant',
    'export_fields' => array(
        'Variant' => array(
            'table' => 'product_feature_variant_descriptions',
            'db_field' => 'variant',
            'required' => true,
            'multilang' => true,
        ),
        'Variant ID' => array(
            'db_field' => 'variant_id',
            'alt_key' => true,
            'required' => true,
        ),
        'Language' => array(
            'table' => 'product_feature_variant_descriptions',
            'db_field' => 'lang_code',
            'type' => 'languages',
            'required' => true,
            'multilang' => true
        ),
        'Feature name' => array(
            'process_get' => array('fn_exim_get_feature_name', '#key', '#lang_code'),
            'linked' => false,
            'required' => true,
        ),
        'Feature group' => array(
            'process_get' => array('fn_exim_get_product_feature_group_name', '#key', '#lang_code'),
            'linked' => false,
            'return_result' => true,
        ),
        'Image' => array(
            'db_field' => 'image_id',
            'table' => 'images_links',
            'process_get' => array('fn_export_image', '#this', 'feature_variant', '@images_path')
        ),
        'URL' => array(
            'db_field' => 'url',
        ),
        'Position' => array(
            'db_field' => 'position',
        ),
        'Description' => array(
            'table' => 'product_feature_variant_descriptions',
            'db_field' => 'description',
        ),
        'Page title' => array(
            'table' => 'product_feature_variant_descriptions',
            'db_field' => 'page_title',
        ),
        'Meta keywords' => array(
            'table' => 'product_feature_variant_descriptions',
            'db_field' => 'meta_keywords',
        ),
        'Meta description' => array(
            'table' => 'product_feature_variant_descriptions',
            'db_field' => 'meta_description',
        ),
    ),
);

return $schema;
