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

$_scheme = array(
    'menus' => array(
        'vendor_only' => true,
        'use_company' => true,
        'page_title' => 'menus',
    ),
    'tabs' => array(
        'vendor_only' => true,
        'use_company' => true,
        'page_title' => 'product_tabs',
    ),
    'block_manager' => array(
        'vendor_only' => true,
        'use_company' => true,
        'page_title' => 'layouts',
    ),
    'sitemap' => array(
        'vendor_only' => true,
        'use_company' => true,
        'page_title' => 'sitemap',
    ),
    'themes' => array(
        'vendor_only' => true,
        'use_company' => true,
        'page_title' => 'themes',
    ),
    'customization' => array(
        'vendor_only' => true,
        'use_company' => true,
        'page_title' => 'customization',
    ),
    'order_management' => array(
        'vendor_only' => true,
        'use_company' => true,
        'page_title' => 'order_management',
    ),
    'static_data' => array(
        'vendor_only' => array(
            'display_condition' => array(
                'section' => 'A',
            ),
        ),
        'use_company' => array(
            'condition' => array(
                array(
                    'field' => 'section',
                    'value' => 'A'
                ),
            ),
        ),
        'page_title' => 'static_data',
    ),
    'companies' => array(
        'modes' => array(
            'manage' => array(
                'permissions' => 'view_stores'
            ),
            'add' => array(
                'permissions' => 'manage_stores'
            ),
            'update' => array(
                'permissions' => array(
                    'GET' => 'view_stores',
                    'POST' => 'manage_stores'
                ),
            ),
        ),
        'page_title' => 'companies',
    ),
    'products' => array(
        'modes' => array(
            'update' => array(
                'use_company' => true,
            ),
            'add' => array(
                'use_company' => true,
            ),
        ),
        'page_title' => 'products',
    ),
    'product_features' => array (
        'modes' => array (
            'update' => array(
                'use_company' => true,
            ),
            'get_variants' => array(
                'use_company' => true,
            ),
        ),
    ),
    'categories' => array(
        'modes' => array(
            'update' => array(
                'use_company' => true,
            ),
            'add' => array(
                'use_company' => true,
            ),
        ),
        'page_title' => 'categories',
    ),
    'pages' => array(
        'modes' => array(
            'update' => array(
                'use_company' => true,
            ),
            'add' => array(
                'use_company' => true,
            ),
        ),
        'page_title' => 'pages',
    ),
    'payments' => array(
        'modes' => array(
            'add' => array(
                'use_company' => true,
            ),
        ),
        'page_title' => 'payments',
    ),
    'currencies' => array(
        'modes' => array(
            'update' => array(
                'auto_sharing' => array(
                    'object_id' => 'currency_data.currency_id',
                    'object_type' => 'currencies'
                ),
            ),
        ),
        'page_title' => 'currencies',
    ),
    'languages' => array(
        'modes' => array(
            'update' => array(
                'auto_sharing' => array(
                    'object_id' => 'language_data.lang_id',
                    'object_type' => 'languages'
                ),
            ),
        ),
        'page_title' => 'languages',
    ),
    'profile_fields' => array(
        'modes' => array(
            'update' => array(
                'auto_sharing' => array(
                    'object_id' => 'field_id',
                    'object_type' => 'profile_fields'
                ),
            ),
        ),
        'page_title' => 'profile_fields',
    ),
    'file_editor' => array(
        'page_title' => 'file_editor',
        'use_company' => true,
    ),
    'exim' => array(
        'use_company' => true,
    ),
);

$schema = array_merge_recursive($schema, $_scheme);

if (fn_allowed_for('ULTIMATE:FREE')) {
    $schema['root']['usergroups'] = array(
        'permissions' => 'none',
    );
    $schema['root']['product_options']['modes']['exceptions'] = array(
        'permissions' => 'none',
    );
}

return $schema;
