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

use Tygh\Registry;

include_once(Registry::get('config.dir.schemas') . 'breadcrumbs/backend.functions.php');

return array(
    'addons.update' => array(
        array(
            'title' => 'addons',
            'link' => 'addons.manage'
        ),
    ),
    'categories.update' => array(
        array(
            'title' => 'categories',
            'link' => 'categories.manage'
        ),
    ),
    'categories.m_update' => array(
        array(
            'title' => 'categories',
            'link' => 'categories.manage'
        ),
    ),

    'companies.update' => array(
        array(
            'title' => 'vendors',
            'link' => 'companies.manage'
        ),
    ),
    'companies.merge' => array(
        array(
            'title' => 'vendors',
            'link' => 'companies.manage'
        ),
    ),

    'destinations.update' => array(
        array(
            'title' => 'locations',
            'link' => 'destinations.manage'
        ),
    ),

    'localizations.update' => array(
        array(
            'title' => 'localizations',
            'link' => 'localizations.manage'
        ),
    ),

    'orders.details' => array(
        array(
            'type' => 'search',
            'prev_dispatch' => 'orders.manage',
            'title' => 'search_results',
            'link' => 'orders.manage.last_view'
        ),
        array(
            'title' => 'orders',
            'link' => 'orders.manage.reset_view'
        ),
    ),

    'pages.update' => array(
        array(
            'type' => 'search',
            'prev_dispatch' => 'pages.manage',
            'title' => 'search_results',
            'link' => 'pages.manage.last_view'
        ),
        array(
            'title' => 'pages',
            'link' => array(
                'function' => array('fn_br_get_pages_manage_url', '@come_from')
            ),
        ),
    ),

    'product_options.inventory' => array(
        array(
            'title' => array(
                'function' => array('fn_get_product_name', '@product_id')
            ),
            'link' => 'products.update?product_id=%PRODUCT_ID&selected_section=options'
        ),
    ),
    'product_options.exceptions' => array(
        array(
            'title' => array(
                'function' => array('fn_get_product_name', '@product_id')
            ),
            'link' => 'products.update?product_id=%PRODUCT_ID&selected_section=options'
        ),
    ),

    'products.p_subscr' => array(
        array(
            'title' => 'products',
            'link' => 'products.manage'
        ),
    ),
    'products.global_update' => array(
        array(
            'title' => 'products',
            'link' => 'products.manage'
        ),
    ),
    'products.update' => array(
        array(
            'type' => 'search',
            'prev_dispatch' => 'products.manage',
            'title' => 'search_results',
            'link' => 'products.manage.last_view'
        ),
        array(
            'title' => 'products',
            'link' => 'products.manage.reset_view'
        ),
        array(
            'function' => array('fn_br_get_product_main_category_link', '@product_id')
        ),
    ),
    'products.m_update' => array(
        array(
            'title' => 'products',
            'link' => 'products.manage'
        ),
    ),

    'profile_fields.update' => array(
        array(
            'title' => 'profile_fields',
            'link' => 'profile_fields.manage'
        ),
    ),

    'profiles.update' => array(
        array(
            'type' => 'search',
            'prev_dispatch' => 'profiles.manage',
            'title' => 'search_results',
            'link' => 'profiles.manage.last_view'
        ),
        array(
            'prev_check_func' => array('fn_br_check_users_link', '@prev_request'),
            'title' => 'users',
            'link' => 'profiles.manage.reset_view'
        ),
        array(
            'prev_check_func' => array('fn_br_check_user_type_link', '@prev_request', '@user_type'),
            'title' => array(
                'function' => array('fn_get_user_type_description', '@user_type', true)
            ),
            'link' => 'profiles.manage?user_type=%USER_TYPE'
        ),
    ),

    'promotions.update' => array(
        array(
            'title' => 'promotions',
            'link' => 'promotions.manage'
        ),
    ),

    'sales_reports.update' => array(
        array(
            'title' => 'reports',
            'link' => 'sales_reports.manage'
        ),
    ),

    'sales_reports.update_table' => array(
        array(
            'title' => array(
                'function' => array('fn_br_get_report_description', '@report_id')
            ),
            'link' => 'sales_reports.update?report_id=%REPORT_ID'
        ),
    ),

    'shipments.details' => array(
        array(
            'type' => 'search',
            'prev_dispatch' => 'shipments.manage',
            'title' => 'search_results',
            'link' => 'shipments.manage.last_view'
        ),
        array(
            'title' => 'shipments',
            'link' => 'shipments.manage'
        ),
    ),

    'shippings.update' => array(
        array(
            'title' => 'shipping_methods',
            'link' => 'shippings.manage'
        ),
    ),

    'sitemap.update' => array(
        array(
            'title' => 'sitemap',
            'link' => 'sitemap.manage'
        ),
    ),

    'static_data.manage' => array(
        array(
            'function' => array('fn_br_get_static_data_owner_link', '@section')
        ),
    ),

    'taxes.update' => array(
        array(
            'title' => 'taxes',
            'link' => 'taxes.manage'
        ),
    ),

    'upgrade_center.check' => array(
        array(
            'title' => 'upgrade_center',
            'link' => 'upgrade_center.manage'
        ),
    ),
    'upgrade_center.installed_upgrades' => array(
        array(
            'title' => 'upgrade_center',
            'link' => 'upgrade_center.manage'
        ),
    ),
    'upgrade_center.diff' => array(
        array(
            'title' => 'upgrade_center',
            'link' => 'upgrade_center.manage'
        ),
        array(
            'title' => 'installed_upgrades',
            'link' => 'installed_upgrades.manage'
        ),
    ),

    'usergroups.requests' => array(
        array(
            'title' => 'usergroups',
            'link' => 'usergroups.manage'
        ),
    )
);
