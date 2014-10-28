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
/*
    Field types:
        header - "text" Header text (language variable)
        text - "text" General text (language variable)
        template - "template" - path to template
        setting - "setting_name" - Name of setting (data will be get/set automatically)

    Process fields:
        'pre_handlers' - Get data for this field before display
            'var_name1' => array(
                'func_name1' => array('%arg1', 'param', CONST),
            ),
            'var_name2' => array(
                'func_name2' => array(),
            ),

        'post_handlers' - Processing data (POST method) after Form submit
            'func_name1' => array('%arg1%', '%arg2%'),
            'func_name2' => array('%arg3%', CONST, 'param'),
*/

$scheme = array(
    'security_settings' => array(
        'items' => array(
            array(
                'type' => 'header',
                'text' => 'security_settings',
            ),
            array(
                'type' => 'text',
                'text' => 'warning_insecure_admin_script',
                'placeholders' => array(
                    '[href]' => Registry::get('config.resources.admin_protection_url')
                )
            ),
            array(
                'type' => 'text',
                'text' => 'change_access_permission_to_config',
            ),
            array(
                'type' => 'template',
                'template' => 'views/settings_wizard/components/password.tpl',
                'post_handlers' => array(
                    'fn_settings_wizard_update_password' => array('%new_password%'),
                ),
            ),
            array(
                'type' => 'template',
                'template' => 'views/settings_wizard/components/ssl_checking.tpl',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'secure_checkout',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'secure_admin',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'secure_auth',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'min_admin_password_length',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'admin_passwords_must_contain_mix',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'change_admin_password_on_first_login',
            ),
        ),
        'next_step' => 'appearance_settings',
    ),

    'appearance_settings' => array(
        'items' => array(
            array(
                'type' => 'header',
                'text' => 'appearance_settings',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'default_wysiwyg_editor',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'default_image_previewer',
            ),
            array(
                'type' => 'template',
                'template' => 'views/settings_wizard/components/default_currency.tpl',
                'pre_handlers' => array(
                    'currencies' => array(
                        'fn_block_manager_get_currencies' => array(),
                    ),
                ),
                'post_handlers' => array(
                    'fn_settings_wizard_set_default_currency' => array('%default_currency%'),
                )
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'backend_default_language',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'frontend_default_language',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'taxes_using_default_address',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'show_prices_taxed_clean',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'cart_prices_w_taxes',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'in_stock_field',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'thumbnails_gallery',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'quantity_changer',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'product_details_in_tab',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'date_format',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'time_format',
            ),
        ),
        'next_step' => 'company_settings',
    ),

    'company_settings' => array(
        'items' => array(
            array(
                'type' => 'header',
                'text' => 'company_settings',
            ),
            array(
                'type' => 'template',
                'template' => 'views/settings_wizard/components/profiles_scripts.tpl',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_name',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_address',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_city',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_country',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_state',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_zipcode',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_phone',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_phone_2',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_fax',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_website',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_users_department',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_site_administrator',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'company_orders_department',
            ),
        ),
        'next_step' => 'user_settings',
    ),

    'user_settings' => array(
        'items' => array(
            array(
                'type' => 'header',
                'text' => 'user_settings',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'approve_user_profiles',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'use_email_as_login',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'allow_create_account_after_order',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'quick_registration',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'address_position',
            ),
            array(
                'type' => 'setting',
                'setting_name' => 'user_multiple_profiles',
            ),
        ),
        'next_step' => 'addons',
    ),

    'addons' => array(
        'items' => array(
            array(
                'type' => 'header',
                'text' => 'most_popular_addons',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'form_builder',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'banners',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'google_analytics',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'bestsellers',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'social_buttons',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'seo',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'wishlist',
            ),
            array(
                'type' => 'addon',
                'addon_name' => 'tags',
            ),
            array(
                'type' => 'header',
                'text' => 'other_addons',
            ),
            array(
                'type' => 'template',
                'template' => 'views/settings_wizard/components/addons.tpl',
                'pre_handlers' => array(
                    'wizard_addons_list' => array(
                        'fn_settings_wizard_get_addons' => array('form_builder', 'banners', 'google_analytics', 'bestsellers', 'social_buttons', 'seo', 'wishlist', 'tags'),
                    ),
                ),
            ),
        ),
    ),
);

if (fn_allowed_for('ULTIMATE')) {
    $scheme['user_settings']['items'][] = array(
        'type' => 'setting',
        'setting_name' => 'share_users',
    );
}

return $scheme;
