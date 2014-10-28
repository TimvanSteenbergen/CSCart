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

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

fn_register_hooks(
    'additional_fields_in_search',
    'before_dispatch',
    'dispatch_before_display',
    'get_categories',
    'get_products',
    'get_shipments',
    'get_users',
    'place_order',
    'order_placement_routines',
    'user_init',
    'redirect_complete',
    'check_static_location',
    'init_secure_controllers',
    'get_pages',
    'shippings_get_shippings_list'
);
