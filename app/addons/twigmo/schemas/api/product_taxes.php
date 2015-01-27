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

$schema = array(
    'object_name' => 'tax',
    'fields' => array(
        'price_includes_tax' => array(
            'db_field' => 'price_includes_tax'
        ),
        'rate_type' => array(
            'db_field' => 'rate_type'
        ),
        'rate_value' => array(
            'db_field' => 'rate_value'
        )
    )
);

return $schema;
