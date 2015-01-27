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

$schema['news'] = array (
    'modes' => array (
        'delete' => array (
            'permissions' => 'manage_news'
        ),
        'update' => array(
            'use_company' => true,
        ),
    ),
    'permissions' => array ('GET' => 'view_news', 'POST' => 'manage_news')
);
$schema['newsletters'] = array (
    'modes' => array (
        'delete' => array (
            'permissions' => 'manage_news'
        ),
        'delete_campaign' => array (
            'permissions' => 'manage_news'
        )
    ),
    'permissions' => array ('GET' => 'view_news', 'POST' => 'manage_news')
);
$schema['subscribers'] = array (
    'modes' => array (
        'delete' => array (
            'permissions' => 'manage_news'
        )
    ),
    'permissions' => array ('GET' => 'view_news', 'POST' => 'manage_news')
);
$schema['campaigns'] = array (
    'permissions' => array ('GET' => 'view_news', 'POST' => 'manage_news')
);
$schema['mailing_lists'] = array (
    'modes' => array (
        'delete' => array (
            'permissions' => 'manage_news'
        )
    ),
    'permissions' => array ('GET' => 'view_news', 'POST' => 'manage_news')
);
$schema['tools']['modes']['update_status']['param_permissions']['table']['newsletter_campaigns'] = 'manage_news';
$schema['tools']['modes']['update_status']['param_permissions']['table']['news'] = 'manage_news';
$schema['tools']['modes']['update_status']['param_permissions']['table']['mailing_lists'] = 'manage_news';

return $schema;
