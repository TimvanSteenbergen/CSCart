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
    'table' => 'product_options_exceptions',
    'object_name' => 'exception',
    'key' => array('product_id', 'options_inventory_id'),
    'group_by' => 'product_options_inventory.product_id',
    'fields' => array (
        'product_id' => array (
            'db_field' => 'product_id'
        ),
        'exception_id' => array (
            'db_field' => 'exception_id'
        ),
        'combination' => array (
            'schema' => array (
                'is_single' => false,
                'name' => 'combination',
                'type' => 'product_options_variants',
            )
        )
    )
);
return $schema;
