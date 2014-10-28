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
    'object_name' => 'companies_rates',
    'key' => array('company_id'),
    'fields' => array (
        'group_key' => array(
            'name' => 'group_key'
        ),
        'company_id' => array (
            'name' => 'company_id'
        ),
        'supplier_id' => array (
            'name' => 'supplier_id'
        ),
        'company' => array (
            'name' => 'name'
        ),
        'rates' => array (
            'process_get' => array (
                'func' => 'Twigmo\\Core\\Api::getAsList',
                'params' => array (
                    'shipping_methods' => array (
                        'value' => 'shipping_methods'
                    ),
                    'shippings' => array (
                        'db_field' => 'shippings'
                    )
                )
            )
        )
    )
);
return $schema;
