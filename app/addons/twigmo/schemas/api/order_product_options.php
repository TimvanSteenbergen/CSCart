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
    'object_name' => 'product_option',
    'key' => array('option_id'),
    'fields' => array (
        'option_id' => array (
            'db_field' => 'option_id'
        ),
        'option_name' => array (
            'db_field' => 'option_name'
        ),
        'option_text' => array (
            'db_field' => 'option_text'
        ),
        'position' => array (
            'db_field' => 'position'
        ),
        'variant_name' => array (
            'db_field' => 'variant_name'
        ),
        'value' => array (
            'db_field' => 'value'
        )
    )
);
return $schema;
