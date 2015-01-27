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

include_once(Registry::get('config.dir.schemas') . 'exim/orders.functions.php');

$schema = array(
    'section' => 'orders',
    'pattern_id' => 'orders',
    'name' => __('orders'),
    'key' => array('order_id'),
    'order' => 0,
    'table' => 'orders',
    'condition' => array(
        'conditions' => array('is_parent_order' => 'N'),
        'use_company_condition' => true,
    ),
    'range_options' => array(
        'selector_url' => 'orders.manage',
        'object_name' => __('orders'),
    ),
    'export_fields' => array(
        'Order ID' => array(
            'db_field' => 'order_id',
            'alt_key' => true,
            'required' => true,
        ),
        'E-mail' => array(
            'db_field' => 'email',
            'required' => true,
        ),
        'User ID' => array(
            'db_field' => 'user_id'
        ),
        'Total' => array(
            'db_field' => 'total'
        ),
        'Subtotal' => array(
            'db_field' => 'subtotal'
        ),
        'Discount' => array(
            'db_field' => 'discount'
        ),
        'Payment surcharge' => array(
            'db_field' => 'payment_surcharge'
        ),
        'Shipping cost' => array(
            'db_field' => 'shipping_cost'
        ),
        'Date' => array(
            'db_field' => 'timestamp',
            'process_get' => array('fn_timestamp_to_date', '#this'),
            'convert_put' => array('fn_date_to_timestamp', '#this'),
        ),
        'Status' => array(
            'db_field' => 'status',
        ),
        'Notes' => array(
            'db_field' => 'notes',
        ),
        'Payment ID' => array(
            'db_field' => 'payment_id',
        ),
        'IP address' => array(
            'db_field' => 'ip_address',
        ),
        'Details' => array(
            'db_field' => 'details',
        ),
        'Payment information' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_get_data', '#key', 'P'),
            'process_put' => array('fn_exim_orders_set_data', '#key', '#this', 'P')
        ),
        'Taxes' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_get_data', '#key', 'T'),
            'process_put' => array('fn_exim_orders_set_data', '#key', '#this', 'T')
        ),
        'Coupons' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_get_data', '#key', 'C'),
            'process_put' => array('fn_exim_orders_set_data', '#key', '#this', 'C')
        ),
        'Shipping' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_get_data', '#key', 'L'),
            'process_put' => array('fn_exim_orders_set_data', '#key', '#this', 'L')
        ),
        'Invoice ID' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_get_docs', '#key', 'I'),
            'process_put' => array('fn_exim_orders_set_docs', '#key', '#this', 'I')
        ),
        'Credit memo ID' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_get_docs', '#key', 'C'),
            'process_put' => array('fn_exim_orders_set_docs', '#key', '#this', 'C')
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
        'Language' => array(
            'db_field' => 'lang_code'
        ),
        'Billing: first name' => array(
            'db_field' => 'b_firstname',
        ),
        'Billing: last name' => array(
            'db_field' => 'b_lastname',
        ),
        'Billing: address' => array(
            'db_field' => 'b_address',
        ),
        'Billing: address (line 2)' => array(
            'db_field' => 'b_address_2',
        ),
        'Billing: city' => array(
            'db_field' => 'b_city',
        ),
        'Billing: state' => array(
            'db_field' => 'b_state',
        ),
        'Billing: country' => array(
            'db_field' => 'b_country',
        ),
        'Billing: zipcode' => array(
            'db_field' => 'b_zipcode',
        ),
        'Shipping: first name' => array(
            'db_field' => 's_firstname',
        ),
        'Shipping: last name' => array(
            'db_field' => 's_lastname',
        ),
        'Shipping: address' => array(
            'db_field' => 's_address',
        ),
        'Shipping: address (line 2)' => array(
            'db_field' => 's_address_2',
        ),
        'Shipping: city' => array(
            'db_field' => 's_city',
        ),
        'Shipping: state' => array(
            'db_field' => 's_state',
        ),
        'Shipping: country' => array(
            'db_field' => 's_country',
        ),
        'Shipping: zipcode' => array(
            'db_field' => 's_zipcode',
        ),
        'Extra fields' => array(
            'linked' => false,
            'process_get' => array('fn_exim_orders_get_extra_fields', '#key', '#lang_code'),
            'process_put' => array('fn_exim_orders_set_extra_fields', '#this', '#key', '#lang_code')
        )
    ),
);

if (fn_allowed_for('ULTIMATE')) {
    $schema['export_fields']['Store'] = array(
        'db_field' => 'company_id',
        'process_get' => array('fn_get_company_name', '#this'),
        'convert_put' => array('fn_get_company_id_by_name', '#this'),
    );
    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Store']['required'] = true;
    }
    $schema['import_process_data']['check_product_company_id'] = array(
        'function' => 'fn_import_check_order_company_id',
        'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_record'),
        'import_only' => true,
    );
}

if (fn_allowed_for('MULTIVENDOR')) {
    $schema['export_fields']['Vendor'] = array(
        'db_field' => 'company_id',
        'process_get' => array('fn_get_company_name', '#this'),
        'convert_put' => array('fn_get_company_id_by_name', '#this'),
    );

    if (!Registry::get('runtime.company_id')) {
        $schema['export_fields']['Vendor']['required'] = true;
    }
}

return $schema;
