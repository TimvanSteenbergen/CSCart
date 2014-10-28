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

use Tygh\Helpdesk;
use Tygh\Http;
use Tygh\Registry;
use Tygh\Settings;

/**
 * Check if secure connection is available
 */
function fn_settings_actions_general_secure_auth(&$new_value, $old_value)
{
    if ($new_value == 'Y') {
        if (!fn_allowed_for('ULTIMATE') || (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id'))) {

            $suffix = '';
            if (fn_allowed_for('ULTIMATE')) {
                $suffix = '&company_id=' . Registry::get('runtime.company_id');
            }

            $storefront_url = fn_url('index.index?check_https=Y' . $suffix, 'C', 'https');

            $content = Http::get($storefront_url);
            if (empty($content) || $content != 'OK') {
                // Disable https
                Settings::instance()->updateValue('secure_checkout', 'N', 'General');
                Settings::instance()->updateValue('secure_admin', 'N', 'General');
                Settings::instance()->updateValue('secure_auth', 'N', 'General');
                $new_value = 'N';

                fn_set_notification('W', __('warning'), __('warning_https_disabled'));
            }
        }
    }
}

/**
 * Check if secure connection is available
 */
function fn_settings_actions_general_secure_checkout(&$new_value, $old_value)
{
    return fn_settings_actions_general_secure_auth($new_value, $old_value);
}

/**
 * Check if secure connection is available
 */
function fn_settings_actions_general_secure_admin(&$new_value, $old_value)
{
    return fn_settings_actions_general_secure_auth($new_value, $old_value);
}

/**
 * Alter order initial ID
 */
function fn_settings_actions_general_order_start_id(&$new_value, $old_value)
{
    if (intval($new_value)) {
        db_query("ALTER TABLE ?:orders AUTO_INCREMENT = ?i", $new_value);
    }
}

/**
 * Save empty value if has no checked check boxes
 */
function fn_settings_actions_general_search_objects(&$new_value, $old_value)
{
    if ($new_value == 'N') {
        $new_value = '';
    }
}

/**
 * Enable/disable Canada Post
 */
function fn_settings_actions_shippings_can_enabled(&$new_value, $old_value)
{
    $currencies = Registry::get('currencies');
    if ($new_value == 'Y' && empty($currencies['CAD'])) {
        fn_set_notification('E', __('warning'), __('canada_post_activation_error'), 'S');
        $new_value = 'N';
    }
}

/**
 * Enable/disable Temando
 */
function fn_settings_actions_shippings_temando_enabled(&$new_value, $old_value)
{
    if ($new_value == 'Y') {
        $fields = fn_get_table_fields('user_profiles');

        $b_suburb = db_get_field('SHOW COLUMNS FROM ?:user_profiles LIKE ?l', 'b_suburb');
        $s_suburb = db_get_field('SHOW COLUMNS FROM ?:user_profiles LIKE ?l', 's_suburb');

        if (empty($b_suburb)) {
            db_query("ALTER TABLE ?:user_profiles ADD b_suburb varchar(128) NOT NULL AFTER b_zipcode");
        }
        if (empty($s_suburb)) {
            db_query("ALTER TABLE ?:user_profiles ADD s_suburb varchar(128) NOT NULL AFTER s_zipcode");
        }

        $billing_profile_field_id = db_get_field("SELECT field_id FROM ?:profile_fields WHERE field_name = ?s", 'b_suburb');
        if (empty($billing_profile_field_id)) {
            $profile_data = array(
                'field_name' => 'suburb',
                'profile_show' => 'Y',
                'profile_required' => 'N',
                'checkout_show' => 'Y',
                'checkout_required' => 'N',
                'partner_show' => 'Y',
                'partner_required' => 'N',
                'field_type' => 'I',
                'position' => '170',
                'is_default' => 'Y',
                'section' => 'BS',
                'matching_id' => '',
                'class' => '',
                'description' => 'Suburb',
                'add_values' => array(
                    array(
                        'position' => '',
                        'description' => ''
                    )
                )
            );
            $profile_field = array();
            $profile_field['b'] = fn_update_profile_field($profile_data, 0);
            $profile_field['s'] = db_get_field("SELECT field_id FROM ?:profile_fields WHERE field_name = ?s", 's_suburb');

        }
        //We should just update settings if they was created previously, and create new setting othervise.
        $setting_data_c_suburb = array(
            'name' => 'company_suburb',
            'edition_type' => 'ROOT,ULT:VENDOR',
            'section_id' => 5,
            'type' => 'I',
            'position' => 41,
            'is_global' => 'Y'
        );
        if (Settings::instance()->isExists('company_suburb', 'Company')) {
            $setting_data_c_suburb['object_id'] = Settings::instance()->getId('company_suburb', 'Company');
        } else {
            $descriptions_c = array();
            foreach (fn_get_translation_languages() as $lang_code => $lang_value) {
                $descriptions_c[] = array(
                    'value' => 'Company suburb',
                    'object_type' => 'O',
                    'lang_code' => $lang_code
                );
            }
        }
        Settings::instance()->update($setting_data_c_suburb, null, (isset($descriptions_c) ? $descriptions_c : null));

        $setting_data_d_suburb = array(
            'name' => 'default_suburb',
            'edition_type' => 'ROOT,ULT:VENDOR',
            'section_id' => 2,
            'type' => 'I',
            'position' => 115,
            'is_global' => 'Y'
        );
        if (Settings::instance()->isExists('default_suburb', 'General')) {
            $setting_data_d_suburb['object_id'] = Settings::instance()->getId('default_suburb', 'General');
        } else {
            $descriptions_d = array();
            foreach (fn_get_translation_languages() as $lang_code => $lang_value) {
                $descriptions_d[] = array(
                    'value' => 'Default suburb',
                    'object_type' => 'O',
                    'lang_code' => $lang_code
                );
            }
        }
        Settings::instance()->update($setting_data_d_suburb, null, (isset($descriptions_d) ? $descriptions_d : null));
    } else {
        //Disable settings
        $c_id = Settings::instance()->getId('company_suburb', 'Company');
        Settings::instance()->update(array('object_id' => $c_id, 'edition_type' => 'NONE'));
        $d_id = Settings::instance()->getId('default_suburb', 'General');
        Settings::instance()->update(array('object_id' => $d_id, 'edition_type' => 'NONE'));
    }
}

function fn_settings_actions_upgrade_center_license_number(&$new_value, &$old_value)
{
    if (empty($new_value)) {
        $new_value = $old_value;

        fn_set_notification('E', __('error'), __('license_number_cannot_be_empty'));

        return false;
    }

    $mode = fn_get_storage_data('store_mode');

    $data = Helpdesk::getLicenseInformation($new_value);
    list($license_status, $updates, $messages) = Helpdesk::parseLicenseInformation($data, $_SESSION['auth'], true);

    if ($license_status == 'ACTIVE' && ($mode != 'full' || empty($old_value))) {
        fn_set_storage_data('store_mode', 'full');
        $_SESSION['mode_recheck'] = true;
    } else {
        if ($license_status != 'ACTIVE') {
            $new_value = $old_value;
        }
    }
}

function fn_settings_actions_appearance_backend_default_language(&$new_value, &$old_value)
{
    if (fn_allowed_for('ULTIMATE')) {
        db_query("UPDATE ?:companies SET lang_code = ?s", $new_value);
    }
}

if (fn_allowed_for('ULTIMATE')) {
    function fn_settings_actions_stores_share_users(&$new_value, $old_value)
    {
        $emails = fn_get_double_user_emails();
        if (!empty($emails)) {
            fn_delete_notification('changes_saved');
            fn_set_notification('E', __('error'), __('ult_share_users_setting_disabled'));
            $new_value = $old_value;
        }
    }
}

function fn_settings_actions_appearance_notice_displaying_time(&$new_value, $old_value)
{
    $new_value = fn_convert_to_numeric($new_value);
}