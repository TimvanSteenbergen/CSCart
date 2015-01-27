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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_register_hooks(
    'update_product_post',
    'get_product_data',
    'get_products',
    'get_product_fields',
    'get_shipping_info',
    'update_shipping_post',
    'shippings_group_products_list',
    'shippings_get_shippings_list',
    'pre_place_order',
    'order_notification',
    'get_notification_rules',
    'get_status_params_definition',
    'get_shipments_info_post',
    'get_orders_post',
    'get_order_info',
    'clone_product'
);
