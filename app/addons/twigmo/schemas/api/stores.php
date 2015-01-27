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
    'table' => 'stores',
    'object_name' => 'store',
    'key' => array('store_id'),
    'fields' => array (
        'store_id' => array (
            'db_field' => 'store_id'
        ),
        'domain' => array (
            'db_field' => 'domain'
        ),
        'name' => array (
            'db_field' => 'name'
        ),
        'admin_url' => array (
            'db_field' => 'admin_url'
        ),
        'access_id' => array (
            'name' => 'access_id'
        ),
        'secret_access_key' => array (
            'name' => 'secret_access_key'
        )
    )
);
return $schema;
