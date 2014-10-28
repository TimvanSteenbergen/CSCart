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
    'table' => 'order_details',
    'object_name' => 'product',
    'key' => array('item_id'),
    'references' => array (
        'product_descriptions' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'product_id' => array (
                    'db_field' => 'product_id'
                ),
                'lang_code' => array (
                    'param' => 'lang_code'
                )
            )
        )
    ),
    'fields' => array (
        'item_id' => array (
            'db_field' => 'item_id'
        ),
        'order_id' => array (
            'db_field' => 'order_id'
        ),
        'product_id' => array (
            'db_field' => 'product_id'
        ),
        'product_code' => array (
            'db_field' => 'product_code'
        ),
        'price' => array (
            'db_field' => 'price'
        ),
        'amount' => array (
            'db_field' => 'amount'
        ),
        'product' => array (
            'table' => 'product_descriptions',
            'db_field' => 'product'
        ),
        'discount' => array (
            'db_field' => 'discount'
        ),
        'base_price' => array (
            'db_field' => 'base_price'
        ),
        'original_price' => array (
            'db_field' => 'original_price'
        ),
        'tax_value' => array (
            'db_field' => 'tax_value'
        ),
        'subtotal' => array (
            'db_field' => 'subtotal'
        ),
        'display_subtotal' => array (
            'db_field' => 'display_subtotal'
        ),
        'shipped_amount' => array (
            'db_field' => 'shipped_amount'
        ),
        'extra' => array (
            'db_field' => 'extra'
        ),
        'product_options' => array (
            'schema' => array (
                'name' => 'product_options',
                'type' => 'order_product_options',
                'filter' => array (
                    'product_id' => array (
                        'db_field' => 'product_id'
                    )
                )
            )
        )
    )
);
return $schema;
