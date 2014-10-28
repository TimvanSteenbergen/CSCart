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

include_once(Registry::get('config.dir.schemas') . 'exim/products.functions.php');
include_once(Registry::get('config.dir.schemas') . 'exim/features.functions.php');

$schema = array(
    'section' => 'products',
    'name' => __('products'),
    'pattern_id' => 'products',
    'key' => array('product_id'),
    'order' => 0,
    'table' => 'products',
    'references' => array(
        'product_descriptions' => array(
            'reference_fields' => array('product_id' => '#key', 'lang_code' => '#lang_code'),
            'join_type' => 'LEFT'
        ),
        'product_prices' => array(
            'reference_fields' => array('product_id' => '#key', 'lower_limit' => 1, 'usergroup_id' => 0),
            'join_type' => 'LEFT'
        ),
        'images_links' => array(
            'reference_fields' => array('object_id' => '#key', 'object_type' => 'product', 'type' => 'M'),
            'join_type' => 'LEFT',
            'import_skip_db_processing' => true
        ),
        'companies' => array(
            'reference_fields' => array('company_id' => '&company_id'),
            'join_type' => 'LEFT',
            'import_skip_db_processing' => true
        )
    ),
    'condition' => array(
        'use_company_condition' => true,
    ),
    'pre_processing' => array(
        'reset_inventory' => array(
            'function' => 'fn_exim_reset_inventory',
            'args' => array('@reset_inventory'),
        ),
        'check_product_code' => array(
            'function' => 'fn_check_product_code',
            'args' => array('$import_data'),
            'import_only' => true,
        )
    ),
    'post_processing' => array(
        'send_product_notifications' => array(
            'function' => 'fn_exim_send_product_notifications',
            'args' => array('$primary_object_ids', '$import_data', '$auth'),
            'import_only' => true,
        ),
    ),
    'import_get_primary_object_id' => array(
        'fill_products_alt_keys' => array(
            'function' => 'fn_import_fill_products_alt_keys',
            'args' => array('$pattern', '$alt_keys', '$object', '$skip_get_primary_object_id'),
            'import_only' => true,
        ),
    ),
    'import_process_data' => array(
        'unset_product_id' => array(
            'function' => 'fn_import_unset_product_id',
            'args' => array('$object'),
            'import_only' => true,
        ),
    ),
    'range_options' => array(
        'selector_url' => 'products.manage',
        'object_name' => __('products'),
    ),
    'notes' => array(
        'text_exim_import_options_note',
        'text_exim_import_features_note',
        'text_exim_import_images_note',
        'text_exim_import_files_note',
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        ),
        'category_delimiter' => array(
            'title' => 'category_delimiter',
            'description' => 'text_category_delimiter',
            'type' => 'input',
            'default_value' => '///'
        ),
        'features_delimiter' => array(
            'title' => 'features_delimiter',
            'description' => 'text_features_delimiter',
            'type' => 'input',
            'default_value' => '///'
        ),
        'images_path' => array(
            'title' => 'images_directory',
            'description' => 'text_images_directory',
            'type' => 'input',
            'default_value' => 'exim/backup/images/',
            'notes' => __('text_file_editor_notice', array('[href]' => fn_url('file_editor.manage?active_section=files&selected_path=/'))),
        ),
        'files_path' => array(
            'title' => 'files_directory',
            'description' => 'text_files_directory',
            'type' => 'input',
            'default_value' => 'exim/backup/downloads/',
            'notes' => __('text_file_editor_notice', array('[href]' => fn_url('file_editor.manage?active_section=files&selected_path=/'))),
        ),
        'delete_files' => array(
            'title' => 'drop_existing_data',
            'type' => 'checkbox',
            'import_only' => true
        ),
        'reset_inventory' => array(
            'title' => 'reset_inventory',
            'description' => 'text_reset_inventory_description',
            'type' => 'checkbox',
            'import_only' => true
        ),
        'price_dec_sign_delimiter' => array(
            'title' => 'price_dec_sign_delimiter',
            'description' => 'text_price_dec_sign_delimiter',
            'type' => 'input',
            'default_value' => '.'
        ),
    ),
    'export_fields' => array(
        'Product code' => array(
            'db_field' => 'product_code',
            'alt_key' => true,
            'required' => true,
            'alt_field' => 'product_id'
        ),
        'Language' => array(
            'table' => 'product_descriptions',
            'db_field' => 'lang_code',
            'type' => 'languages',
            'required' => true,
            'multilang' => true
        ),
        'Product id' => array(
            'db_field' => 'product_id'
        ),
        'Category' => array(
            'process_get' => array('fn_exim_get_product_categories', '#key', 'M', '@category_delimiter', '#lang_code'),
            'process_put' => array('fn_exim_set_product_categories', '#key', 'M', '#this', '@category_delimiter'),
            'multilang' => true,
            'linked' => false, // this field is not linked during import-export
            'default' => 'Products' // default value applies only when we creating new record
        ),
        'List price' => array(
            'db_field' => 'list_price',
            'convert_put' => array('fn_exim_import_price', '#this', '@price_dec_sign_delimiter'),
            'process_get' => array('fn_exim_export_price', '#this', '@price_dec_sign_delimiter'),
        ),
        'Price' => array(
            'table' => 'product_prices',
            'db_field' => 'price',
            'convert_put' => array('fn_exim_import_price', '#this', '@price_dec_sign_delimiter'),
            'process_put' => array('fn_import_product_price', '#key', '#this', '#new'),
            'process_get' => array('fn_exim_export_price', '#this', '@price_dec_sign_delimiter'),
        ),
        'Status' => array(
            'db_field' => 'status'
        ),
        'Quantity' => array(
            'db_field' => 'amount'
        ),
        'Weight' => array(
            'db_field' => 'weight'
        ),
        'Min quantity' => array(
            'db_field' => 'min_qty'
        ),
        'Max quantity' => array(
            'db_field' => 'max_qty'
        ),
        'Quantity step' => array(
            'db_field' => 'qty_step'
        ),
        'List qty count' => array(
            'db_field' => 'list_qty_count'
        ),
        'Shipping freight' => array(
            'db_field' => 'shipping_freight',
            'convert_put' => array('fn_exim_import_price', '#this', '@price_dec_sign_delimiter'),
            'process_get' => array('fn_exim_export_price', '#this', '@price_dec_sign_delimiter'),
        ),
        'Date added' => array(
            'db_field' => 'timestamp',
            'process_get' => array('fn_timestamp_to_date', '#this'),
            'convert_put' => array('fn_date_to_timestamp', '#this'),
            'return_result' => true
        ),
        'Downloadable' => array(
            'db_field' => 'is_edp',
        ),
        'Files' => array(
            'process_get' => array('fn_exim_export_file', '#key', '@files_path'),
            'process_put' => array('fn_exim_import_file', '#key', '#this', '@files_path', '@delete_files'),
            'linked' => false, // this field is not linked during import-export
        ),
        'Ship downloadable' => array(
            'db_field' => 'edp_shipping',
        ),
        'Inventory tracking' => array(
            'db_field' => 'tracking',
        ),
        'Out of stock actions' => array(
            'db_field' => 'out_of_stock_actions',
        ),
        'Free shipping' => array(
            'db_field' => 'free_shipping',
        ),
        'Feature comparison' => array(
            'db_field' => 'feature_comparison',
        ),
        'Zero price action' => array(
            'db_field' => 'zero_price_action',
        ),
        'Thumbnail' => array(
            'table' => 'images_links',
            'db_field' => 'image_id',
            'use_put_from' => '%Detailed image%',
            'process_get' => array('fn_export_image', '#this', 'product', '@images_path')
        ),
        'Detailed image' => array(
            'db_field' => 'detailed_id',
            'table' => 'images_links',
            'process_get' => array('fn_export_image', '#this', 'detailed', '@images_path'),
            'process_put' => array('fn_import_images', '@images_path', '%Thumbnail%', '#this', '0', 'M', '#key', 'product')
        ),
        'Product name' => array(
            'table' => 'product_descriptions',
            'db_field' => 'product',
            'multilang' => true
        ),
        'Description' => array(
            'table' => 'product_descriptions',
            'db_field' => 'full_description',
            'multilang' => true
        ),
        'Short description' => array(
            'table' => 'product_descriptions',
            'db_field' => 'short_description',
            'multilang' => true
        ),
        'Meta keywords' => array(
            'table' => 'product_descriptions',
            'db_field' => 'meta_keywords',
            'multilang' => true
        ),
        'Meta description' => array(
            'table' => 'product_descriptions',
            'db_field' => 'meta_description',
            'multilang' => true
        ),
        'Search words' => array(
            'table' => 'product_descriptions',
            'db_field' => 'search_words',
            'multilang' => true
        ),
        'Page title' => array(
            'table' => 'product_descriptions',
            'db_field' => 'page_title',
            'multilang' => true
        ),
        'Taxes' => array(
            'db_field' => 'tax_ids',
            'process_get' => array('fn_exim_get_taxes', '#this', '#lang_code'),
            'process_put' => array('fn_exim_set_taxes', '#key', '#this'),
            'multilang' => true,
            'return_result' => true
        ),
        'Features' => array(
            'process_get' => array('fn_exim_get_product_features', '#key', '@features_delimiter', '#lang_code'),
            'process_put' => array('fn_exim_set_product_features', '#key', '#this', '@features_delimiter', '#lang_code'),
            'linked' => false, // this field is not linked during import-export
        ),
        'Options' => array(
            'process_get' => array('fn_exim_get_product_options', '#key', '#lang_code'),
            'process_put' => array('fn_exim_set_product_options', '#key', '#this', '#lang_code'),
            'linked' => false, // this field is not linked during import-export
        ),
        'Secondary categories' => array(
            'process_get' => array('fn_exim_get_product_categories', '#key', 'A', '@category_delimiter', '#lang_code'),
            'process_put' => array('fn_exim_set_product_categories', '#key', 'A', '#this', '@category_delimiter'),
            'multilang' => true,
            'linked' => false, // this field is not linked during import-export
        ),
        'Product URL' => array(
            'process_get' => array('fn_exim_get_product_url', '#key', '#lang_code'),
            'multilang' => true,
            'linked' => false,
            'export_only' => true,
        ),
        'Image URL' => array(
            'process_get' => array('fn_exim_get_image_url', '#key', 'product', 'M', true, false, '#lang_code'),
            'multilang' => true,
            'db_field' => 'image_id',
            'table' => 'images_links',
            'export_only' => true,
        ),
        'Detailed image URL' => array(
            'process_get' => array('fn_exim_get_detailed_image_url', '#key', 'product', 'M', '#lang_code'),
            'db_field' => 'detailed_id',
            'table' => 'images_links',
            'export_only' => true,
        ),
        'Items in box' => array(
            'process_get' => array('fn_exim_get_items_in_box', '#key'),
            'process_put' => array('fn_exim_put_items_in_box', '#key', '#this'),
            'linked' => false, // this field is not linked during import-export
        ),
        'Box size' => array(
            'process_get' => array('fn_exim_get_box_size', '#key'),
            'process_put' => array('fn_exim_put_box_size', '#key', '#this'),
            'linked' => false, // this field is not linked during import-export
        ),
    ),
);

if (!fn_allowed_for('ULTIMATE:FREE') && Registry::get('config.tweaks.disable_localizations') == false) {
    $schema['export_fields']['Localizations'] = array(
        'db_field' => 'localization',
        'process_get' => array('fn_exim_get_localizations', '#this', '#lang_code'),
        'process_put' => array('fn_exim_set_localizations', '#key', '#this'),
        'return_result' => true,
        'multilang' => true,
    );
}

$company_schema = array(
    'table' => 'companies',
    'db_field' => 'company',
    'process_put' => array('fn_exim_set_product_company', '#key', '#this')
);

if (fn_allowed_for('ULTIMATE')) {
    $schema['export_fields']['Store'] = $company_schema;
    $schema['export_fields']['Price']['process_put'] = array('fn_import_product_price', '#key', '#this', '#new', '%Store%');

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Store']['required'] = true;
        $schema['export_fields']['Category']['process_put'] = array('fn_exim_set_product_categories', '#key', 'M', '#this', '@category_delimiter', '%Store%');
        $schema['export_fields']['Features']['process_put'] = array('fn_exim_set_product_features', '#key', '#this', '@features_delimiter', '#lang_code', '%Store%');
        $schema['export_fields']['Secondary categories']['process_put'] = array('fn_exim_set_product_categories', '#key', 'A', '#this', '@category_delimiter', '%Store%');
    }

    $schema['import_process_data']['check_product_company_id'] = array(
        'function' => 'fn_import_check_product_company_id',
        'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_record'),
        'import_only' => true,
    );
}
if (fn_allowed_for('MULTIVENDOR')) {
    $schema['export_fields']['Vendor'] = $company_schema;

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Vendor']['required'] = true;

    } else {
        $schema['import_process_data']['mve_import_check_product_data'] = array(
            'function' => 'fn_mve_import_check_product_data',
            'args' => array('$object', '$primary_object_id','$options', '$processed_data', '$skip_record'),
            'import_only' => true,
        );

        $schema['import_process_data']['mve_import_check_object_id'] = array(
            'function' => 'fn_mve_import_check_object_id',
            'args' => array('$primary_object_id', '$processed_data', '$skip_record'),
            'import_only' => true,
        );
    }
}

return $schema;
