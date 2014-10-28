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

/*
    controller, mode - required fields
    api - api entities, lower case
    type - displaying type. available values: tpl_tabs, tools
        tpl_tabs - add a new tab direclty to templates
        tools - not implemented

    params - needs to get companies for the object.
        Example request: ?dispatch=currencies.update&currency_code=USD
        Checking controller and mode.
        Build URL to get companies for the object:
        ?dispatch=companies.get_object_share&object=currencies&object_id=USD (get from REQUEST @currency_code)

    button - if we need to add personal buttons on the New tab - we have to specify button type and name.
    Example: see shipping update page.

    table - table data for the object
        name - table name
        key_field - primary key

    request_object - needs to parse request and find owner company_id when adding a new record. (Only for ADD object, not for UPDATE)

    conditions - Extra conditions to display sharing tab or use adding sharing condition to SELECT query
        display_condition
                'group_type' => 'A' // or array('C', 'A') - for 2 and more values

        skip_selction - Do not modify SELECT query for this object
            Example: skip_selection => true // For all queries
            Or: 'skip_selection' => array( // By condition
                    'a.type' => array(
                        'value' => 'C',  // or array('C', 'A') - for 2 and more values
                        'condition' => 'equal', //or "not_equal"
                    ),
                ),
                If query has condition like ...WHERE a.type = 'C' OR ..., the additional JOIN will not be added (if any condition will be found, JOIN will not be added)

    no_item_text - This language variable will be displayed instead of "No items" when no companies shared for the object
        Example: 'no_item_text' => 'all_stores'

    pre/post_processing - Functions to be executed before/after data sharing updated.
        Example: 'post_processing' => 'fn_object_sharing'

    have_owner - Equal true if object table contains "company_id" field. Also determine that abject should be shared for all companies after creation.

    skip_checking_status - If true, sharing scheme will not be used on changing object status.
*/

return array(
    'currencies' => array(
        'controller' => 'currencies',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@currency_id',
            'object' => 'currencies'
        ),
        'table' => array(
            'name' => 'currencies',
            'key_field' => 'currency_id',
        ),
        'request_object' => 'currency_description',
        'have_owner' => false,
    ),

    'languages' => array(
        'controller' => 'languages',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@lang_id',
            'object' => 'languages'
        ),
        'table' => array(
            'name' => 'languages',
            'key_field' => 'lang_id',
        ),
        'request_object' => 'language',
        'have_owner' => false,
    ),

    'profile_fields' => array(
        'controller' => 'profile_fields',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@field_id',
            'object' => 'profile_fields',
        ),
        'table' => array(
            'name' => 'profile_fields',
            'key_field' => 'field_id',
        ),
        'request_object' => 'field_data',
        'have_owner' => false,
    ),

    'pages' => array(
        'controller' => 'pages',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@page_id',
            'object' => 'pages'
        ),
        'table' => array(
            'name' => 'pages',
            'key_field' => 'page_id',
        ),
        'request_object' => 'page_data',
        'have_owner' => true,
    ),

    'product_options' => array(
        'controller' => 'product_options',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@option_id',
            'object' => 'product_options'
        ),
        'table' => array(
            'name' => 'product_options',
            'key_field' => 'option_id',
        ),
        'conditions' => array(
            'display_condition' => array(
                'product_id' => '',
            ),
            'skip_selection' => array(
                'product_id' => array(
                    'value' => 0,
                ),
            ),
        ),
        'request_object' => 'option_data',
        'have_owner' => true,
    ),

    'shippings' => array(
        'controller' => 'shippings',
        'api' => 'shippings',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@shipping_id',
            'object' => 'shippings'
        ),
        'table' => array(
            'name' => 'shippings',
            'key_field' => 'shipping_id',
        ),
        'buttons' => array(
            'type' => 'save_cancel',
            'but_name' => 'dispatch[shippings.update_shipping]',
        ),
        'request_object' => 'shipping_data',
        'have_owner' => true,
    ),

    'payments' => array(
        'controller' => 'payments',
        'api' => 'payments',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@payment_id',
            'object' => 'payments'
        ),
        'table' => array(
            'name' => 'payments',
            'key_field' => 'payment_id',
        ),
        'request_object' => 'payment_data',
        'have_owner' => true,
    ),

    'promotions' => array(
        'controller' => 'promotions',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@promotion_id',
            'object' => 'promotions'
        ),
        'table' => array(
            'name' => 'promotions',
            'key_field' => 'promotion_id',
        ),
        'request_object' => 'promotion_data',
        'have_owner' => true,
    ),

    'product_filters' => array(
        'controller' => 'product_filters',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@filter_id',
            'object' => 'product_filters'
        ),
        'table' => array(
            'name' => 'product_filters',
            'key_field' => 'filter_id',
        ),
        'request_object' => 'filter_data',
        'have_owner' => true,
    ),

    'product_features' => array(
        'controller' => 'product_features',
        'api' => 'features',
        'mode' => 'update',
        'type' => 'tpl_tabs',
        'params' => array(
            'object_id' => '@feature_id',
            'object' => 'product_features'
        ),
        'table' => array(
            'name' => 'product_features',
            'key_field' => 'feature_id',
        ),
        'request_object' => 'feature_data',
        'post_processing' => 'fn_ult_share_features',
        'have_owner' => true,
    ),
);
