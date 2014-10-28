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

$schema = array (
    'table' => 'shipments',
    'object_name' => 'shipment',
    'key' => array('shipment_id'),
    'sortings' => array (
        'id' => 'shipments.shipment_id',
        'order_id' => 'orders.order_id',
        'shipment_date' => 'shipments.timestamp',
        'order_date' => 'orders.timestamp',
        'customer' => array('orders.s_lastname', '?:orders.s_firstname'),
    ),
    'references' => array (
        'shipment_items' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'shipment_id' => array (
                    'db_field' => 'shipment_id'
                )
            )
        ),
        'shipping_descriptions' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'shipping_id' => array (
                    'db_field' => 'shipping_id'
                ),
                'lang_code' => array (
                    'param' => 'lang_code'
                )
            )
        ),
        'orders' => array (
            'join_type' => 'LEFT',
            'fields' => array (
                'order_id' => array (
                    'table' => 'shipment_items',
                    'db_field' => 'order_id'
                )
            )
        )
    ),
    'group_by' => 'shipments.shipment_id',
    'fields' => array (
        'shipment_id' => array (
            'db_field' => 'shipment_id'
        ),
        'order_id' => array (
            'table' => 'shipment_items',
            'db_field' => 'order_id',
            'required' => true
        ),
        'shipping_id' => array (
            'db_field' => 'shipping_id'
        ),
        'shipping_name' => array (
            'table' => 'shipping_descriptions',
            'db_field' => 'shipping'
        ),
        'carrier' => array (
            'db_field' => 'carrier'
        ),
        'tracking_number' => array (
            'db_field' => 'tracking_number'
        ),
        'timestamp' => array (
            'query_field' => 'shipments.timestamp AS shipment_timestamp',
            'db_field' => 'shipment_timestamp'
        ),
        'comments' => array (
            'db_field' => 'comments'
        ),
        'products' => array (
            'schema' => array (
                'type' => 'shipment_products',
                'name' => 'products',
                'filter' => array (
                    'shipment_id' => array (
                        'db_field' => 'shipment_id'
                    )
                )
            ),
            'required' => true
        ),
        'order_date' => array (
            'table' => 'orders',
            'query_field' => 'orders.timestamp as order_timestamp',
            'db_field' => 'order_timestamp'
        ),
        'customer_firstname' => array (
            'table' => 'orders',
            'query_field' => 'orders.s_firstname as s_firstname',
            'db_field' => 's_firstname'
        ),
        'customer_lastname' => array (
            'table' => 'orders',
            'query_field' => 'orders.s_lastname as s_lastname',
            'db_field' => 's_lastname'
        )
    )
);
return $schema;
