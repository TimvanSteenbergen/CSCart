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
    'object_name' => 'tax',
    'fields' => array (
        'rate_type' => array (
            'db_field' => 'rate_type'
        ),
        'rate_value' => array (
            'db_field' => 'rate_value'
        ),
        'price_includes_tax' => array (
            'db_field' => 'price_includes_tax'
        ),
        'regnumber' => array (
            'db_field' => 'regnumber'
        ),
        'priority' => array (
            'db_field' => 'priority'
        ),
        'tax_subtotal' => array (
            'db_field' => 'tax_subtotal'
        ),
        'description' => array (
            'db_field' => 'description'
        ),
    )
);
return $schema;
