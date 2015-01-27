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

use \Tygh\Registry;

$scheme =  array(
    'orders' => array (
        'modes' => array (
            'update_status' => array (
                'permissions' => 'change_order_status'
            ),
            'delete_orders' => array (
                'permissions' => 'delete_orders'
            ),
            'delete' => array (
                'permissions' => 'delete_orders'
            ),
            'bulk_print' => array (
                'permissions' => 'view_orders'
            ),
            'remove_cc_info' => array (
                'permissions' => 'edit_order'
            ),
        ),
        'permissions' => 'view_orders'
    ),
    'taxes' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_taxes'
            ),
        ),
        'permissions' => array ('GET' => 'view_taxes', 'POST' => 'manage_taxes'),
    ),
    'sitemap' => array (
        'permissions' => 'manage_sitemap',
    ),
    'database' => array (
        'permissions' => 'database_maintenance',
    ),
    'product_options' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_catalog'
            )
        ),
        'permissions' => array ('GET' => 'view_catalog', 'POST' => 'manage_catalog'),
    ),
    'tabs' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_catalog'
            ),
            'update_status' => array (
                'permissions' => 'manage_catalog'
            ),
            'update' => array (
                'permissions' => 'manage_catalog'
            ),
            'add' => array (
                'permissions' => 'manage_catalog'
            ),
            'manage' => array (
                'permissions' => 'view_catalog'
            ),
            'picker' => array (
                'permissions' => 'view_catalog'
            ),
        ),
        'permissions' => array ('GET' => 'view_catalog', 'POST' => 'manage_catalog'),
    ),
    'products' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_catalog'
            ),
            'clone' => array (
                'permissions' => 'manage_catalog'
            ),
            'add' => array (
                'permissions' => 'manage_catalog'
            ),
            'manage' => array (
                'permissions' => 'view_catalog'
            ),
            'picker' => array (
                'permissions' => 'view_catalog'
            ),
            'options' => array (
                'permissions' => 'edit_order'
            ),
        ),
        'permissions' => array ('GET' => 'view_catalog', 'POST' => 'manage_catalog'),
    ),
    'product_filters' => array(
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_catalog'
            ),
        ),
        'permissions' => array ('GET' => 'view_catalog', 'POST' => 'manage_catalog'),
    ),
    'shippings' => array (
        'modes' => array (
            'delete_shipping' => array (
                'permissions' => 'manage_shipping'
            ),
        ),
        'permissions' => array ('GET' => 'view_shipping', 'POST' => 'manage_shipping'),
    ),
    'usergroups' => array (
        'modes' => array (
            'update_status' => array (
                'permissions' => 'manage_usergroups'
            ),
            'delete' => array (
                'permissions' => 'manage_usergroups'
            ),
            'update' => array (
                'permissions' => 'manage_usergroups',
                'condition' => array(
                    'operator' => 'and',
                    'function' => array('fn_check_permission_manage_usergroups'),
                ),
            ),
        ),
        'permissions' => array ('GET' => 'view_usergroups', 'POST' => 'manage_usergroups'),
    ),
    'customization' => array (
        'modes' => array (
            'update_mode' => array(
                'param_permissions' => array (
                    'type' => array (
                        'live_editor' => 'manage_translation',
                        'design' => 'manage_design',
                        'theme_editor' => 'manage_design',
                    )
                ),
            ),
        ),
     ),
    'profiles' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_users'
            ),
            'delete_profile' => array (
                'permissions' => 'manage_users'
            ),
            'm_delete' => array (
                'permissions' => 'manage_users'
            ),
            'add' => array (
                'permissions' => 'manage_users'
            ),
            'update' => array (
                'permissions' => array('GET' => 'view_users', 'POST' => 'manage_users'),
                'condition' => array(
                    'operator' => 'or',
                    'function' => array('fn_check_permission_manage_own_profile'),
                ),
            ),
            'update_status' => array (
                'permissions' => 'manage_users'
            ),
            'manage' => array (
                'permissions' => 'view_users'
            ),
            'export_range' => array (
                'permissions' => 'exim_access'
            ),
            'act_as_user' => array(
                'permissions' => 'manage_users',
                'condition' => array(
                    'operator' => 'or',
                    'function' => array('fn_check_permission_act_as_user'),
                )
            )
        ),
    ),
    'cart' => array (
        'permissions' => array ('GET' => 'view_users', 'POST' => 'manage_users'),
    ),
    'pages' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_pages'
            )
        ),
        'permissions' => array ('GET' => 'view_pages', 'POST' => 'manage_pages'),
    ),
    'profile_fields' => array (
        'permissions' => array ('GET' => 'view_users', 'POST' => 'manage_users'),
    ),
    'logs' => array (
        'modes' => array (
            'clean' => array (
                'permissions' => 'delete_logs'
            )
        ),
        'permissions' => 'view_logs',
    ),
    'categories' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_catalog'
            )
        ),
        'permissions' => array ('GET' => 'view_catalog', 'POST' => 'manage_catalog'),
    ),
    'settings' => array (
        'permissions' => array ('GET' => 'view_settings', 'POST' => 'update_settings'),
    ),
    'settings_wizard' => array (
        'permissions' => 'update_settings',
    ),
    'upgrade_center' => array (
        'permissions' => 'upgrade_store',
    ),
    'payments' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_payments'
            )
        ),
        'permissions' => array ('GET' => 'view_payments', 'POST' => 'manage_payments'),
    ),
    'currencies' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_currencies'
            )
        ),
        'permissions' => array ('GET' => 'view_currencies', 'POST' => 'manage_currencies'),
    ),
    'destinations' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_locations'
            )
        ),
        'permissions' => array ('GET' => 'view_locations', 'POST' => 'manage_locations'),
    ),
    'localizations' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_locations'
            )
        ),
        'permissions' => array ('GET' => 'view_locations', 'POST' => 'manage_locations'),
    ),
    'exim' => array (
        'permissions' => 'exim_access',
    ),
    'languages' => array (
        'modes' => array (
            'delete_variable' => array (
                'permissions' => 'manage_languages'
            ),
            'delete_language' => array (
                'permissions' => 'manage_languages'
            )
        ),
        'permissions' => array ('GET' => 'view_languages', 'POST' => 'manage_languages'),
    ),
    'product_features' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_catalog'
            )
        ),
        'permissions' => array ('GET' => 'view_catalog', 'POST' => 'manage_catalog'),
    ),
    'static_data' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_static_data'
            )
        ),
        'permissions' => array ('GET' => 'view_static_data', 'POST' => 'manage_static_data'),
    ),
    'statuses' => array (
        'permissions' => 'manage_order_statuses',
    ),
    'sales_reports' => array (
        'modes' => array (
            'view' => array (
                'permissions' => 'view_reports'
            ),
            'set_report_view' => array (
                'permissions' => 'view_reports'
            ),
        ),
        'permissions' => 'manage_reports',
    ),
    'addons' => array (
        'permissions' => 'update_settings',
    ),
    'states' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'manage_locations'
            )
        ),
        'permissions' => array ('GET' => 'view_locations', 'POST' => 'manage_locations'),
    ),
    'countries' => array (
        'permissions' => array ('GET' => 'view_locations', 'POST' => 'manage_locations'),
    ),
    'order_management' => array (
        'modes' => array (
            'edit' => array (
                'permissions' => 'edit_order'
            ),
            'new' => array (
                'permissions' => 'create_order'
            ),
        ),
        'permissions' => 'edit_order',
    ),
    'file_editor' => array (
        'permissions' => 'edit_files',
    ),
    'block_manager' => array (
        'permissions' => 'edit_blocks',
    ),
    'menus' => array (
        'modes' => array (
            'delete' => array (
                'permissions' => 'edit_blocks'
            ),
        ),
        'permissions' => 'edit_blocks',
    ),
    'promotions' => array (
        'permissions' => 'manage_promotions',
    ),
    'shipments' => array (
        'modes' => array (
            'manage' => array (
                'permissions' => 'view_orders',
            ),
            'delete' => array (
                'permissions' => 'edit_order',
            ),
            'picker' => array (
                'permissions' => 'edit_order',
            ),
        ),
        'permissions' => 'view_orders',
    ),
    'tools' => array (
        'modes' => array (
            'update_position' => array(
                'param_permissions' => array (
                    'table' => array (
                        'product_tabs' => 'manage_catalog',
                    )
                )
            ),
            'update_status' => array (
                'param_permissions' => array (
                    'table' => array (
                        'categories' => 'manage_catalog',
                        'states' => 'manage_locations',
                        'usergroups' => 'manage_usergroups',
                        'currencies' => 'manage_currencies',
                        'blocks' => 'edit_blocks',
                        'pages' => 'manage_pages',
                        'taxes' => 'manage_taxes',
                        'promotions' => 'manage_promotions',
                        'static_data' => 'manage_static_data',
                        'statistics_reports' => 'manage_reports',
                        'countries' => 'manage_locations',
                        'shippings' => 'manage_shipping',
                        'languages' => 'manage_languages',
                        'sitemap_sections' => 'manage_sitemap',
                        'localizations' => 'manage_locations',
                        'products' => 'manage_catalog',
                        'destinations' => 'manage_locations',
                        'product_options' => 'manage_catalog',
                        'product_features' => 'manage_catalog',
                        'payments' => 'manage_payments',
                        'product_filters' => 'manage_catalog',
                        'product_files' => 'manage_catalog',
                        'orders' => 'change_order_status'
                    )
                )
            ),
        )
    ),
    'storage' => array (
        'permissions' => 'manage_storage',
    ),
    'themes' => array (
        'permissions' => 'manage_themes',
    )
);

if (Registry::get('config.tweaks.disable_localizations') == true || fn_allowed_for('ULTIMATE:FREE')) {
    $scheme['localizations'] = $scheme['root']['localizations'] = array(
        'permissions' => 'none',
    );
}

return $scheme;
