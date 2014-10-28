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
    'table' => 'shippings',
    'object_name' => 'shipping',
    'key' => array('shipping_id'),
    'references' => array (
        'shipping_descriptions' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'shipping_id' => array (
                    'db_field' => 'shipping_id'
                ),
                'lang_code' => array (
                    'param' => 'lang_code'
                )
            )
        )
    ),

    'fields' => array (
        'shipping_id' => array (
            'db_field' => 'shipping_id'
        ),
        'shipping_name' => array (
            'table' => 'shipping_descriptions',
            'db_field' => 'shipping'
        ),
        'carrier' => array (
            'db_field' => 'carrier'
        ),
        'tracking_number' => array (
            'db_field' => 'tracking_number'
        )
    )
);
return $schema;
