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

return array(
    'section' => 'subscribers',
    'pattern_id' => 'em_subscribers',
    'name' => __('subscribers'),
    'key' => array('subscriber_id'),
    'table' => 'em_subscribers',
    'range_options' => array (
        'selector_url' => 'em_subscribers.manage',
        'object_name' => __('subscribers'),
    ),
    'post_processing' => array(
        'sync' => array(
            'function' => 'fn_em_exim_sync',
            'args' => array('$primary_object_ids', '$import_data', '$auth'),
            'import_only' => true,
        ),
    ),
    'export_fields' => array (
        'E-mail' => array (
            'db_field' => 'email',
            'required' => true,
            'alt_key' => true,
        ),
        'Name' => array (
            'db_field' => 'name',
            'required' => true,
        ),
        'Unsubscribe key' => array (
            'db_field' => 'unsubscribe_key',
        ),
        'Status' => array (
            'db_field' => 'status'
        ),
        'Language' => array (
            'db_field' => 'lang_code',
        ),
        'IP address' => array (
            'db_field' => 'ip_address'
        ),        
        'Date' => array (
            'db_field' => 'timestamp',
            'process_get' => array('fn_timestamp_to_date', '#this'),
            'convert_put' => array('fn_date_to_timestamp', '#this'),
        )
    ),
);
