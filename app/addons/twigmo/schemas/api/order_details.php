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
    'object_name' => 'order_detail',
    'key' => array('item_id'),
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
        'product_info' => array (
            'schema' => array (
                'name' => 'product_info',
                'type' => 'products',
                'filter' => array (
                    'product_id' => array (
                        'db_field' => 'product_id'
                    )
                )
            )
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
        'extra' => array (
            'db_field' => 'extra'
        )
    )
);
return $schema;
