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
    'table' => 'user_profiles',
    'object_name' => 'profile',
    'key' => array('profile_id'),
    'fields' => array (
        'profile_id' => array (
            'db_field' => 'profile_id'
        ),
        'profile_name' => array (
            'db_field' => 'profile_name'
        ),
        'profile_type' => array (
            'db_field' => 'profile_type'
        ),
        'addresses' => array (
            'process_get' => array (
                'func' => 'Twigmo\\Core\\Api::getApiProfileAddresses',
                'params' => array (
                    'profile_fields' => array (
                        'db_field' => '*'
                    ),
                    'lang_code' => array (
                        'param' => 'lang_code'
                    )
                )
            ),
            'schema' => array (
                'type' => 'addresses',
            ),
        ),
    )
);
return $schema;
