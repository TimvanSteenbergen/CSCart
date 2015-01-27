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

$schema = array (
    'table' => 'product_options',
    'object_name' => 'option',
    'key' => array('option_id'),
    'references' => array (
        'product_options_descriptions' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'option_id' => array (
                    'db_field' => 'option_id'
                ),
                'lang_code' => array (
                    'param' => 'lang_code'
                )
            )
        ),
    ),
    'fields' => array (
        'option_id' => array (
            'db_field' => 'option_id'
        ),
        'product_id' => array (
            'db_field' => 'product_id'
        ),
        'option_name' => array (
            'table' => 'product_options_descriptions',
            'db_field' => 'option_name'
        ),
        'option_text' => array (
            'table' => 'product_options_descriptions',
            'db_field' => 'option_text'
        ),
        'required' => array (
            'db_field' => 'required'
        ),
        'option_type' => array (
            'db_field' => 'option_type'
        ),
        'position' => array (
            'db_field' => 'position'
        ),
        'value' => array (
            'db_field' => 'value'
        ),
        'status' => array (
            'db_field' => 'status'
        ),
        'variants' => array (
            'schema' => array (
                'is_single' => false,
                'type' => 'product_options_variants',
                'name' => 'variants',
                'filter' => array (
                    'variant_id' => array (
                        'db_field' => 'variant_id'
                    )
                )
            )
        ),
        // regexp for the text fields
        'regexp' => array (
            'db_field' => 'regexp'
        ),
        // inner hint
        'inner_hint' => array(
            'db_field' => 'inner_hint'
        ),
        // incorrect message
        'incorrect_message' => array(
            'db_field' => 'incorrect_message'
        ),
        // fields for the 'File' option type
        'multiupload' => array (
            'db_field' => 'multiupload'
        ),
        'allowed_extensions' => array (
            'db_field' => 'allowed_extensions'
        ),
        'max_file_size' => array (
            'db_field' => 'max_file_size'
        ),
        'inventory' => array (
            'db_field' =>  'inventory'
        ),
    )
);
return $schema;
