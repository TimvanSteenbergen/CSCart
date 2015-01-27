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

include_once(Registry::get('config.dir.schemas') . 'exim/users.functions.php');

$schema = array(
    'section' => 'users',
    'pattern_id' => 'users',
    'name' => __('users'),
    'key' => array('user_id'),
    'order' => 0,
    'table' => 'users',
    'references' => array(
        'user_profiles' => array(
            'reference_fields' => array('user_id' => '#key', 'profile_type' => 'P'),
            'join_type' => 'LEFT'
        ),
    ),
    'range_options' => array(
        'selector_url' => 'profiles.manage',
        'object_name' => __('users'),
    ),
    'export_fields' => array(
        'E-mail' => array(
            'db_field' => 'email',
            'alt_key' => true,
            'required' => true,
        ),
        'Login' => array(
            'db_field' => 'user_login'
        ),
        'User type' => array(
            'db_field' => 'user_type'
        ),
        'Status' => array(
            'db_field' => 'status'
        ),
        'Password' => array(
            'db_field' => 'password',
            'pre_insert' => array('fn_exim_process_password', '#row'),
        ),
        'Salt' => array(
            'db_field' => 'salt'
        ),
        'First name' => array(
            'db_field' => 'firstname'
        ),
        'Last name' => array(
            'db_field' => 'lastname'
        ),
        'Company' => array(
            'db_field' => 'company'
        ),
        'Fax' => array(
            'db_field' => 'fax'
        ),
        'Phone' => array(
            'db_field' => 'phone'
        ),
        'Web site' => array(
            'db_field' => 'url'
        ),
        'Tax exempt' => array(
            'db_field' => 'tax_exempt'
        ),
        'Registration date' => array(
            'db_field' => 'timestamp',
            'process_get' => array('fn_timestamp_to_date', '#this'),
            'convert_put' => array('fn_date_to_timestamp', '#this'),
            'default' => array('time')
        ),
        'Language' => array(
            'db_field' => 'lang_code'
        ),
        'Billing: first name' => array(
            'db_field' => 'b_firstname',
            'table' => 'user_profiles',
        ),
        'Billing: last name' => array(
            'db_field' => 'b_lastname',
            'table' => 'user_profiles',
        ),
        'Billing: address' => array(
            'db_field' => 'b_address',
            'table' => 'user_profiles',
        ),
        'Billing: address (line 2)' => array(
            'db_field' => 'b_address_2',
            'table' => 'user_profiles',
        ),
        'Billing: city' => array(
            'db_field' => 'b_city',
            'table' => 'user_profiles',
        ),
        'Billing: state' => array(
            'db_field' => 'b_state',
            'table' => 'user_profiles',
        ),
        'Billing: country' => array(
            'db_field' => 'b_country',
            'table' => 'user_profiles',
        ),
        'Billing: zipcode' => array(
            'db_field' => 'b_zipcode',
            'table' => 'user_profiles',
        ),
        'Billing: phone' => array(
            'db_field' => 'b_phone',
            'table' => 'user_profiles',
        ),
        'Shipping: first name' => array(
            'db_field' => 's_firstname',
            'table' => 'user_profiles',
        ),
        'Shipping: last name' => array(
            'db_field' => 's_lastname',
            'table' => 'user_profiles',
        ),
        'Shipping: address' => array(
            'db_field' => 's_address',
            'table' => 'user_profiles',
        ),
        'Shipping: address (line 2)' => array(
            'db_field' => 's_address_2',
            'table' => 'user_profiles',
        ),
        'Shipping: city' => array(
            'db_field' => 's_city',
            'table' => 'user_profiles',
        ),
        'Shipping: state' => array(
            'db_field' => 's_state',
            'table' => 'user_profiles',
        ),
        'Shipping: country' => array(
            'db_field' => 's_country',
            'table' => 'user_profiles',
        ),
        'Shipping: zipcode' => array(
            'db_field' => 's_zipcode',
            'table' => 'user_profiles',
        ),
        'Shipping: phone' => array(
            'db_field' => 's_phone',
            'table' => 'user_profiles',
        ),
        'Extra fields' => array(
            'linked' => false,
            'process_get' => array('fn_exim_get_extra_fields', '#key', '#lang_code'),
            'process_put' => array('fn_exim_set_extra_fields', '#this', '#key')
        ),
    ),
);

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $schema['export_fields']['User group IDs'] = array(
        'process_get' => array('fn_exim_get_usergroups', '#key'),
        'process_put' => array('fn_exim_set_usergroups', '#key', '#this'),
        'linked' => false, // this field is not linked during import-export
    );
}

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['export_fields']['Vendor'] = array (
        'db_field' => 'company_id',
        'process_get' => array('fn_get_company_name', '#this'),
    );

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Vendor']['required'] = true;
    }

    $schema['import_process_data'] = array(
        'check_company_id' => array(
            'function' => 'fn_import_check_user_company_id',
            'args' => array('$primary_object_id', '$object', '$processed_data', '$skip_record'),
            'import_only' => true,
        ),
    );

    $schema['pre_processing'] = array(
        'set_user_company_id' => array(
            'function' => 'fn_import_set_user_company_id',
            'args' => array('$import_data'),
            'import_only' => true,
        ),
        'set_default_value' => array(
            'function' => 'fn_import_set_default_value',
            'args' => array('$import_data'),
            'import_only' => true,
        ),
    );
}

if (fn_allowed_for('ULTIMATE')) {
    $schema['export_fields']['Store'] = array (
        'db_field' => 'company_id',
        'process_get' => array('fn_get_company_name', '#this'),
    );

    $schema['key'][] = 'company_id';

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Store']['required'] = true;
    }

    $schema['pre_processing'] = array(
        'set_user_company_id' => array(
            'function' => 'fn_import_set_user_company_id',
            'args' => array('$import_data'),
            'import_only' => true,
        ),
        'set_default_value' => array(
            'function' => 'fn_import_set_default_value',
            'args' => array('$import_data'),
            'import_only' => true,
        ),
    );

    $schema['pre_export_process'] = array(
        'set_allowed_company_ids' => array(
            'function' => 'fn_set_allowed_company_ids',
            'args' => array('$conditions'),
            'export_only' => true,
        ),
    );

    $schema['import_process_data'] = array(
        'check_company_id' => array(
            'function' => 'fn_import_check_user_company_id',
            'args' => array('$primary_object_id', '$object', '$processed_data', '$skip_record'),
            'import_only' => true,
        ),
    );

    //We should add company_id as alt key that $primary_object_id will be filled correctly.
    if (Registry::get('settings.Stores.share_users') == 'N' && !Registry::get('runtime.simple_ultimate')) {
        $schema['export_fields']['Store']['alt_key'] = true;
    }
}

return $schema;
