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

include_once(Registry::get('config.dir.schemas') . 'last_view/frontend.functions.php');

return array (
    'products' => array (
        'list_mode' => 'search',
        'view_mode' => 'quick_view',
        'default_navigation' => array (
            'mode' => 'view',
            'function' => 'fn_lv_get_product_default_navigation'
        ),
        'func' => 'fn_get_products',
        'item_id' => 'product_id'
    ),
    'categories' => array (
        'list_mode' => 'view',
        'view_controller' => 'products',
        'func' => 'fn_get_products',
        'item_id' => 'product_id'
    ),
    'orders' => array (
        'list_mode' => 'search',
        'func' => 'fn_get_orders',
        'item_id' => 'order_id',
        'links_label' => 'order',
        'show_item_id' => true,
    ),
);
