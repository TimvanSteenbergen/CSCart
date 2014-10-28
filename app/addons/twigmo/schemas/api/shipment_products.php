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
    'table' => 'shipment_items',
    'object_name' => 'product',
    'key' => array('item_id', 'shipment_id'),
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
            'db_field' => 'item_id',
            'required' => true
        ),
        'shipment_id' => array (
            'db_field' => 'shipment_id'
        ),
        'product_id' => array (
            'db_field' => 'product_id'
        ),
        'amount' => array (
            'db_field' => 'amount',
            'required' => true
        ),
        'product' => array (
            'table' => 'product_descriptions',
            'db_field' => 'product'
        ),
    )
);
return $schema;
