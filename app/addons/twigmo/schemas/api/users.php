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
    'table' => 'users',
    'object_name' => 'user',
    'key' => array('user_id'),
    'sortings' => array (
        'id' => 'users.user_id',
        'username' => 'users.user_login',
        'email' => 'users.email',
        'name' => array('users.firstname', 'users.lastname'),
        'date' => 'users.timestamp',
        'type' => 'users.user_type',
        'status' => 'users.status'
    ),
    'fields' => array (
        'user_id' => array (
            'db_field' => 'user_id'
        ),
        'status' => array (
            'db_field' => 'status'
        ),
        'user_type' => array (
            'db_field' => 'user_type'
        ),
        'user_login' => array (
            'db_field' => 'user_login'
        ),
        'referer' => array (
            'db_field' => 'referer'
        ),
        'last_login' => array (
            'db_field' => 'last_login'
        ),
        'timestamp' => array (
            'db_field' => 'timestamp'
        ),
        'password' => array (
            'db_field' => 'password'
        ),
        'firstname' => array (
            'db_field' => 'firstname'
        ),
        'lastname' => array (
            'db_field' => 'lastname'
        ),
        'company' => array (
            'db_field' => 'company'
        ),
        'email' => array (
            'db_field' => 'email'
        ),
        'phone' => array (
            'db_field' => 'phone'
        ),
        'fax' => array (
            'db_field' => 'fax'
        ),
        'url' => array (
            'db_field' => 'url'
        ),
        'birthday' => array (
            'db_field' => 'birthday'
        ),
        'responsible_email' => array (
            'db_field' => 'responsible_email'
        ),
        'notify_updated_user' => array (
            'name' => 'notify_updated_user'
        ),
        'password_hash' => array (
            'name' => 'password_hash'
        ),
        'profiles' => array (
            'schema' => array (
                'type' => 'profiles',
                'filter' => array (
                    'user_id' => array (
                        'db_field' => 'user_id'
                    )
                )
            ),
            'process_put' => array (
                'func' => 'Twigmo\\Core\\Api::parseApiProfilesData',
                'params' => array (
                    'profiles' => array (
                        'api_field' => 'profiles'
                    )
                )
            )
        )
    )
);
return $schema;
