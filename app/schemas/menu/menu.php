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

/*
    Every item can has any additional attributes.
    The base HTML struct of menu item is:
        <li class="some classes">
            <a href="some.html">Title</a>
        </li>

    So you can use the following array structure to specify your attrs:
    'addons' => array(
        'href' => 'addons.manage',
        'position' => 100,
        'attrs' => array(
            'class' => 'test-addon-class', // Classes for <li>
            'main' => array( // Attributes for <li>
                'custom-li-attr' => 'my-li-attr',
            ),
            'class_href' => 'test-addon-class', // Classes for <a>
            'href' => array( // Attributes for <a>
                'custom-a-attr' => 'my-a-attr',
            ),
        ),
    ),

    As a result you will get the following HTML code:
    <li class="some classes test-addon-class" custom-li-attr="my-li-attr">
        <a href="some.html" custom-a-attr="my-a-attr">Title</a>
    </li>
*/

$schema = array(
    'top' => array(
        'addons' => array(
            'items' => array(
                'manage_addons' => array(
                    'href' => 'addons.manage',
                    'position' => 10,
                ),
                'manage_addons_divider' => array(
                    'type' => 'divider',
                    'position' => 20
                ),
            ),
            'position' => 100,
        ),
        'administration' => array(
            'items' => array(
                'addons_divider' => array(
                    'type' => 'divider',
                    'position' => 110,
                ),
                'payment_methods' => array(
                    'href' => 'payments.manage',
                    'position' => 200,
                ),
                'shippings_taxes' => array(
                    'href' => 'shippings.manage',
                    'type' => 'title',
                    'position' => 300,
                    'subitems' => array(
                        'shipping_methods' => array(
                            'href' => 'shippings.manage',
                            'position' => 100,
                        ),
                        'taxes' => array(
                            'href' => 'taxes.manage',
                            'position' => 200,
                        ),
                        'states' => array(
                            'href' => 'states.manage',
                            'position' => 300,
                        ),
                        'countries' => array(
                            'href' => 'countries.manage',
                            'position' => 400,
                        ),
                        'locations' => array(
                            'href' => 'destinations.manage',
                            'position' => 500,
                        ),
                        'localizations' => array(
                            'href' => 'localizations.manage',
                            'position' => 600,
                        ),
                    ),
                ),
                'order_statuses' => array(
                    'href' => 'statuses.manage?type=O',
                    'position' => 400,
                ),
                'order_statuses_divider' => array(
                    'type' => 'divider',
                    'position' => 410,
                ),
                'profile_fields' => array(
                    'href' => 'profile_fields.manage',
                    'position' => 500,
                ),
                'profile_fields_divider' => array(
                    'type' => 'divider',
                    'position' => 510,
                ),
                'currencies' => array(
                    'href' => 'currencies.manage',
                    'position' => 600,
                ),
                'languages' => array(
                    'href' => 'languages.manage',
                    'type' => 'title',
                    'position' => 700,
                    'subitems' => array(
                        'translations' => array(
                            'href' => 'languages.translations',
                            'position' => 100,
                        ),
                        'manage_languages' => array(
                            'href' => 'languages.manage',
                            'position' => 200,
                        ),
                    ),
                ),
                'languages_divider' => array(
                    'type' => 'divider',
                    'position' => 710,
                ),
                'logs' => array(
                    'href' => 'logs.manage',
                    'position' => 800,
                ),
                'logs_divider' => array(
                    'type' => 'divider',
                    'position' => 900,
                ),
                'database' => array(
                    'href' => 'database.manage',
                    'position' => 1000,
                ),
                'storage' => array(
                    'href' => 'storage.index',
                    'type' => 'title',
                    'position' => 1100,
                    'subitems' => array(
                        'cdn_settings' => array(
                            'href' => 'storage.cdn',
                            'position' => 100,
                        ),
                        'configure_divider' => array(
                            'type' => 'divider',
                            'position' => 110,
                        ),
                        'clear_cache' => array(
                            'href' => 'storage.clear_cache?redirect_url=%CURRENT_URL',
                            'position' => 200,
                        ),
                        'clear_thumbnails' => array(
                            'href' => 'storage.clear_thumbnails?redirect_url=%CURRENT_URL',
                            'position' => 300,
                        ),
                    ),
                ),
                'import_data' => array(
                    'href' => 'exim.import',
                    'position' => 1200,
                    'subitems' => array(
                        'orders' => array(
                            'href' => 'exim.import?section=orders',
                            'position' => 200,
                        ),
                        'products' => array(
                            'href' => 'exim.import?section=products',
                            'position' => 300,
                        ),
                        'features' => array(
                            'href' => 'exim.import?section=features',
                            'position' => 100,
                        ),
                        'translations' => array(
                            'href' => 'exim.import?section=translations',
                            'position' => 400,
                        ),
                        'users' => array(
                            'href' => 'exim.import?section=users',
                            'position' => 500,
                        ),
                    ),
                ),
                'export_data' => array(
                    'href' => 'exim.export',
                    'position' => 1300,
                    'subitems' => array(
                        'orders' => array(
                            'href' => 'exim.export?section=orders',
                            'position' => 200,
                        ),
                        'products' => array(
                            'href' => 'exim.export?section=products',
                            'position' => 300,
                        ),
                        'features' => array(
                            'href' => 'exim.export?section=features',
                            'position' => 100,
                        ),

                        'translations' => array(
                            'href' => 'exim.export?section=translations',
                            'position' => 400,
                        ),
                        'users' => array(
                            'href' => 'exim.export?section=users',
                            'position' => 500,
                        ),
                    ),
                ),
                'export_data_divider' => array(
                    'type' => 'divider',
                    'position' => 1400,
                ),
                'upgrade_center' => array(
                    'href' => 'upgrade_center.manage',
                    'position' => 1600,
                ),
            ),
            'position' => 600,
        ),
        'design' => array(
            'items' => array(
                'themes' => array(
                    'href' => 'themes.manage',
                    'position' => 100,
                ),
                'layouts' => array(
                    'href' => 'block_manager.manage',
                    'position' => 200,
                ),
                'file_editor' => array(
                    'href' => 'file_editor.manage',
                    'position' => 300,
                ),
                'file_editor_manager_divider' => array(
                    'type' => 'divider',
                    'position' => 310,
                ),
                'menus' => array(
                    'href' => 'menus.manage',
                    'alt' => 'static_data.manage?section=A',
                    'position' => 400,
                ),
                'product_tabs' => array(
                    'href' => 'tabs.manage',
                    'position' => 500,
                ),
                'product_tabs_divider' => array(
                    'type' => 'divider',
                    'position' => 510,
                ),
            ),
            'position' => 700,
        ),
        'settings' => array(
            'items' => array(
                'General' => array(
                    'href' => 'settings.manage?section_id=General',
                    'position' => 100,
                    'type' => 'setting',
                ),
                'Appearance' => array(
                    'href' => 'settings.manage?section_id=Appearance',
                    'position' => 200,
                    'type' => 'setting',
                ),
                'Appearance_divider' => array(
                    'type' => 'divider',
                    'position' => 300,
                ),
                'Company' => array(
                    'href' => 'settings.manage?section_id=Company',
                    'position' => 400,
                    'type' => 'setting',
                ),
                'Company' => array(
                    'href' => 'settings.manage?section_id=Company',
                    'position' => 500,
                    'type' => 'setting',
                ),
                'Shippings' => array(
                    'href' => 'settings.manage?section_id=Shippings',
                    'position' => 600,
                    'type' => 'setting',
                ),
                'Emails' => array(
                    'href' => 'settings.manage?section_id=Emails',
                    'position' => 700,
                    'type' => 'setting',
                ),
                'Thumbnails' => array(
                    'href' => 'settings.manage?section_id=Thumbnails',
                    'position' => 800,
                    'type' => 'setting',
                ),
                'Sitemap' => array(
                    'href' => 'settings.manage?section_id=Sitemap',
                    'position' => 900,
                    'type' => 'setting',
                ),
                'Upgrade_center' => array(
                    'href' => 'settings.manage?section_id=Upgrade_center',
                    'position' => 1000,
                    'type' => 'setting',
                ),
                'Upgrade_center_divider' => array(
                    'type' => 'divider',
                    'position' => 1100,
                ),
                'Security' => array(
                    'href' => 'settings.manage?section_id=Security',
                    'position' => 1200,
                    'type' => 'setting',
                ),
                'Image_verification' => array(
                    'href' => 'settings.manage?section_id=Image_verification',
                    'position' => 1300,
                    'type' => 'setting',
                ),
                'Image_verification_divider' => array(
                    'type' => 'divider',
                    'position' => 1400,
                ),
                'Logging' => array(
                    'href' => 'settings.manage?section_id=Logging',
                    'position' => 1500,
                    'type' => 'setting',
                ),
                'Reports' => array(
                    'href' => 'settings.manage?section_id=Reports',
                    'position' => 1600,
                    'type' => 'setting',
                ),
                'Reports_divider' => array(
                    'position' => 1610,
                    'type' => 'divider',
                ),
                'settings_wizard' => array(
                    'href' => 'settings_wizard.view',
                    'position' => 1700,
                    'title' => __("settings_wizard"),
                ),
            ),
            'position' => 700,
        ),
    ),

    'central' => array(
        'orders' => array(
            'items' => array(
                'view_orders' => array(
                    'href' => 'orders.manage',
                    'alt' => 'order_management',
                    'position' => 100,
                ),
                'sales_reports' => array(
                    'href' => 'sales_reports.view',
                    'position' => 200,
                ),
                'shipments' => array(
                    'href' => 'shipments.manage',
                    'active_option' => 'settings.General.use_shipments',
                    'position' => 400,
                ),
            ),
            'position' => 100,
        ),
        'products' => array(
            'items' => array(
                'categories' => array(
                    'href' => 'categories.manage',
                    'position' => 100,
                ),
                'products' => array(
                    'href' => 'products.manage',
                    'alt' => 'product_options.inventory,product_options.exceptions',
                    'position' => 200,
                ),
                'features' => array(
                    'href' => 'product_features.manage',
                    'position' => 300,
                ),
                'filters' => array(
                    'href' => 'product_filters.manage',
                    'position' => 400,
                ),
                'options' => array(
                    'href' => 'product_options.manage',
                    'position' => 500,
                ),
            ),
            'position' => 200,
        ),
        'customers' => array(
            'items' => array(
                'administrators' => array(
                    'href' => 'profiles.manage?user_type=A',
                    'alt' => 'profiles.update?user_type=A',
                    'position' => 200,
                ),
                'customers' => array(
                    'href' => 'profiles.manage?user_type=C',
                    'alt' => 'profiles.update?user_type=C',
                    'position' => 300,
                ),
                'usergroups' => array(
                    'href' => 'usergroups.manage',
                    'position' => 800,
                ),
            ),
            'position' => 300,
        ),
        'website' => array(
            'items' => array(
                'content' => array(
                    'href' => 'pages.manage?get_tree=multi_level',
                    'position' => 100,
                ),
                'sitemap' => array(
                    'href' => 'sitemap.manage',
                    'position' => 1000,
                ),
            ),
            'position' => 500,
        ),
        'marketing' => array(
            'items' => array(
                'promotions' => array(
                    'href' => 'promotions.manage',
                    'position' => 100,
                ),
                'users_carts' => array(
                    'href' => 'cart.cart_list',
                    'position' => 200,
                ),
            ),
            'position' => 400,
        ),
    ),
);

if (Registry::get('config.tweaks.disable_localizations') == true) {
    unset($schema['top']['administration']['items']['shippings_taxes']['subitems']['localizations']);
}

if (Registry::get('config.tweaks.disable_localizations') != true && fn_allowed_for('ULTIMATE:FREE')) {
    $schema['top']['administration']['items']['shippings_taxes']['subitems']['localizations']['is_promo'] = true;
}

if ((!Registry::get('runtime.company_id') || Registry::get('runtime.simple_ultimate')) && !defined('RESTRICTED_ADMIN')) {
    $schema['top']['settings']['items']['store_mode'] = array(
        'position' => 999999,
        'type' => 'title',
        'href' => 'settings.change_store_mode',
        'attrs' => array(
            'class_href' => 'cm-dialog-opener cm-dialog-auto-size',
            'href' => array(
                'data-ca-target-id' => 'store_mode_dialog',
            ),
        ),
    );
}


return $schema;
