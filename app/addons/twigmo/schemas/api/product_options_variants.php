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
    'table' => 'product_options_variants',
    'object_name' => 'variant',
    'key' => array('option_id'),
    'references' => array (
        'product_option_variants_descriptions' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'variant_id' => array (
                    'db_field' => 'variant_id'
                ),
                'lang_code' => array (
                    'param' => 'lang_code'
                )
            )
        ),
    ),
    'fields' => array (
        'variant_id' => array (
            'db_field' => 'variant_id'
        ),
        'option_id' => array (
            'db_field' => 'option_id'
        ),
        'position' => array (
            'db_field' => 'position'
        ),
        'variant_name' => array (
            'table' => 'product_option_variants_descriptions',
            'db_field' => 'variant_name'
        ),
        'modifier' => array (
            'db_field' => 'modifier'
        ),
        'modifier_type' => array (
            'db_field' => 'modifier_type'
        ),
        'weight_modifier' => array (
            'db_field' => 'weight_modifier'
        ),
        'weight_modifier_type' => array (
            'db_field' => 'weight_modifier_type'
        ),
        'point_modifier' => array (
            'db_field' => 'point_modifier'
        ),
        'point_modifier_type' => array (
            'db_field' => 'point_modifier_type'
        ),
        'status' => array (
            'db_field' => 'status'
        ),
    )
);
return $schema;
