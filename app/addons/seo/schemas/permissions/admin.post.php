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

$schema['seo_rules'] = array (
    'modes' => array (
        'delete_rule' => array (
            'permissions' => 'manage_seo_rules'
        ),
        'manage' => array(
            'vendor_only' => true,
            'use_company' => true,
            'page_title' => 'seo_rules',
        ),
        'update' => array(
            'use_company' => true,
        ),
        'm_update' => array(
            'use_company' => true,
        ),
    ),
    'permissions' => array ('GET' => 'view_seo_rules', 'POST' => 'manage_seo_rules')
);

$schema['seo_redirects'] = array (
    'modes' => array (
        'delete' => array (
            'permissions' => 'manage_seo_rules'
        ),
        'manage' => array(
            'vendor_only' => true,
            'use_company' => true,
            'page_title' => 'seo.redirects_manager',
        ),
        'update' => array(
            'use_company' => true,
        ),
        'm_update' => array(
            'use_company' => true,
        ),
    ),
    'permissions' => array ('GET' => 'view_seo_rules', 'POST' => 'manage_seo_rules')
);

return $schema;
