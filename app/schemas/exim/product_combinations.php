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

include_once(Registry::get('config.dir.schemas') . 'exim/product_combinations.functions.php');

$schema = array(
    'section' => 'products',
    'name' => __('product_combinations'),
    'pattern_id' => 'product_combinations',
    'key' => array('product_id'),
    'order' => 1,
    'table' => 'product_options_inventory',
    'references' => array(
        'product_descriptions' => array(
            'reference_fields' => array('product_id' => '#key', 'lang_code' => '#lang_code'),
            'join_type' => 'LEFT',
        ),
        'products' => array(
            'reference_fields' => array('product_id' => '#key'),
            'join_type' => 'INNER',
        ),
    ),
    'range_options' => array(
        'selector_url' => 'products.manage',
        'object_name' => __('products'),
    ),
    'options' => array(
        'lang_code' => array(
            'title' => 'language',
            'type' => 'languages',
            'default_value' => array(DEFAULT_LANGUAGE),
        ),
        'product_option_delimiter' => array(
            'title' => 'product_option_delimiter',
            'description' => 'text_product_option_delimiter',
            'type' => 'input',
            'default_value' => ', '
        ),
    ),
    'import_skip_db_processing' => true,
    'export_fields' => array(
        'Product ID' => array(
            'required' => true,
            'alt_key' => true,
            'db_field' => 'product_id',
        ),
        'Language' => array(
            'table' => 'product_descriptions',
            'db_field' => 'lang_code',
            'type' => 'languages',
            'alt_key' => false,
            'required' => true,
            'multilang' => true
        ),
        'Product name' => array(
            'required' => false,
            'table' => 'product_descriptions',
            'db_field' => 'product',
            'multilang' => true,
        ),
        'Combination code' => array(
            'required' => false,
            'db_field' => 'product_code',
        ),
        'Combination' => array(
            'required' => true,
            'db_field' => 'combination',
            'process_get' => array('fn_exim_get_product_combination', '#key', '#this', '@product_option_delimiter', '#lang_code'),
            'process_put' => array('fn_exim_put_product_combination', '%Product ID%', '%Product name%', '%Combination code%', '#this', '%Amount%', '#counter', '@product_option_delimiter'),
            'multilang' => true,
        ),
        'Amount' => array(
            'required' => false,
            'db_field' => 'amount',
        ),
    ),
);

$schema['import_process_data'] = array(
    'check_product_combination_company_id' => array(
        'function' => 'fn_import_check_product_combination_company_id',
        'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_record'),
        'import_only' => true,
    ),
);

if (Registry::get('runtime.company_id')) {
    $schema['references']['products']['reference_fields'] = array('product_id' => '#key', 'company_id' => Registry::get('runtime.company_id'));
}

return $schema;
