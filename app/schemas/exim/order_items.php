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

include_once(Registry::get('config.dir.schemas') . 'exim/order_items.functions.php');

return array(
    'section' => 'orders',
    'pattern_id' => 'order_items',
    'name' => __('order_items'),
    'key' => array('item_id', 'order_id'),
    'order' => 1,
    'table' => 'order_details',
    'references' => array(
        'orders' => array(
            'reference_fields' => array('order_id' => '&order_id'),
            'join_type' => 'LEFT',
            'alt_key' => array('order_id'),
            'import_skip_db_processing' => true
        ),
    ),
    'condition' => array(
        'conditions' => array('&orders.is_parent_order' => 'N'),
    ),
    'range_options' => array(
        'selector_url' => 'orders.manage',
        'object_name' => __('orders'),
    ),
    'import_process_data' => array(
        'check_order_existence' => array(
            'function' => 'fn_check_order_existence',
            'args' => array('$primary_object_id', '$object', '$pattern', '$options', '$processed_data', '$processing_groups', '$skip_record'),
            'import_only' => true,
        ),
    ),
    'export_fields' => array(
        'Order ID' => array(
            'db_field' => 'order_id',
            'alt_key' => true,
            'required' => true,
        ),
        'Item ID' => array(
            'db_field' => 'item_id',
            'alt_key' => true,
            'required' => true,
        ),
        'Product ID' => array(
            'db_field' => 'product_id'
        ),
        'Product code' => array(
            'db_field' => 'product_code'
        ),
        'Price' => array(
            'db_field' => 'price'
        ),
        'Quantity' => array(
            'db_field' => 'amount'
        ),
        'Extra' => array(
            'db_field' => 'extra',
            'linked' => true,
            'process_get' => array('fn_exim_orders_get_extra', '#this'),
            'convert_put' => array('fn_exim_orders_set_extra', '#this')
        ),
    ),
);
