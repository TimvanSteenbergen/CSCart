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
    'place_order',
    'get_order_info',
    'buy_together_calculate_cart_post',
    'change_order_status',
    'clone_product',
    array('calculate_cart_taxes_pre', 300),
    'get_cart_product_data',
    'gather_additional_product_data_post',
    'get_user_info',
    'get_product_options',
    'get_product_option_data_pre',
    'get_selected_product_options_before_select',
    'apply_option_modifiers_pre',
    'get_external_discounts',
    'form_cart',
    'allow_place_order',
    'rma_recalculate_order',
    'user_init',
    'get_users',
    'get_orders',
    'get_product_data',
    'update_product_post',
    'update_category_post',
    'global_update_products',
    'delete_order',
    'ult_delete_company',
    'update_cart_by_data_post',
    'promotion_apply_pre',
    'place_suborders'
);
