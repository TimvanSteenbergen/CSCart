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
include_once(Registry::get('config.dir.addons') . 'news_and_emails/schemas/exim/subscribers.functions.php');

return array(
    'section' => 'subscribers',
    'pattern_id' => 'subscribers',
    'name' => __('subscribers'),
    'key' => array('subscriber_id'),
    'table' => 'subscribers',
    'references' => array (
        'user_mailing_lists' => array (
            'reference_fields' => array('subscriber_id' => '#key'),
            'join_type' => 'LEFT',
            'alt_key' => array('#key', 'list_id')
        ),
    ),
    'range_options' => array (
        'selector_url' => 'subscribers.manage',
        'object_name' => __('subscribers'),
    ),
    'import_after_process_data' => array(
        'check_list_id' => array(
            'function' => 'fn_news_and_emails_import_after_process_data',
            'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_db_processing_record'),
            'import_only' => true,
        ),
    ),
    'export_fields' => array (
        'E-mail' => array (
            'db_field' => 'email',
            'required' => true,
            'alt_key' => true,
        ),
        'Mailing list' => array (
            'db_field' => 'list_id',
            'required' => true,
            'table' => 'user_mailing_lists',
            'multilang' => true,
            'process_get' => array ('fn_export_convert_mailing_list', '#this', DEFAULT_LANGUAGE),
            'convert_put' => array ('fn_import_convert_mailing_list', '#this'),
        ),
        'Activation key' => array (
            'db_field' => 'activation_key',
            'table' => 'user_mailing_lists'
        ),
        'Unsubscribe key' => array (
            'db_field' => 'unsubscribe_key',
            'table' => 'user_mailing_lists'
        ),
        'Confirmed' => array (
            'db_field' => 'confirmed',
            'table' => 'user_mailing_lists'
        ),
        'Language' => array (
            'db_field' => 'lang_code',
            'required' => true,
            'table' => 'user_mailing_lists',
        ),
        'Subscribers date' => array (
            'db_field' => 'timestamp',
            'import_only' => true,
            'convert_put' => array ('fn_import_date_to_timestamp', '#this'),
            'default' => array ('fn_import_date_to_timestamp'),
        ),
        'Mailing list date' => array (
            'db_field' => 'timestamp',
            'table' => 'user_mailing_lists',
            'import_only' => true,
            'convert_put' => array ('fn_import_date_to_timestamp', '#this'),
            'default' => array ('fn_import_date_to_timestamp'),
        )
    ),
);
