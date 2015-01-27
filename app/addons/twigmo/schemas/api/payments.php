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
    'table' => 'payments',
    'object_name' => 'payment',
    'key' => array('payment_id'),
    'references' => array (
        'payment_descriptions' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'payment_id' => array (
                    'db_field' => 'payment_id'
                ),
                'lang_code' => array (
                    'param' => 'lang_code'
                )
            )
        )
    ),

    'fields' => array (
        'payment_id' => array (
            'db_field' => 'payment_id'
        ),
        'position' => array (
            'db_field' => 'position'
        ),
        'status' => array (
            'db_field' => 'status'
        ),
        'payment' => array (
            'table' => 'payment_descriptions',
            'db_field' => 'payment'
        ),
        'description' => array (
            'table' => 'payment_descriptions',
            'db_field' => 'description'
        ),
        'instructions' => array (
            'name' => 'instructions'
        ),
        'params' => array (
            'db_field' => 'params'
        ),
        'add_surcharge' => array (
            'db_field' => 'a_surcharge'
        ),
        'percent_surcharge' => array (
            'db_field' => 'p_surcharge'
        ),
        'localization' => array (
            'db_field' => 'localization'
        ),
        'surcharge_value' => array (
            'name' => 'surcharge_value'
        ),
    )
);
return $schema;
