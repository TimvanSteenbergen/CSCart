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

$scheme = array(
    'conditions' => array (
        'price' => array (
            'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
            'type' => 'input',
            'field' => 'base_price',
            'zones' => array(
                'catalog',
            )
        ),
        'categories' => array (
            'operators' => array ('in', 'nin'),
            'type' => 'picker',
            'picker_props' => array (
                'picker' => 'pickers/categories/picker.tpl',
                'params' => array (
                    'multiple' => true,
                    'use_keys' => 'N',
                    'view_mode' => 'table',
                ),
            ),
            'field' => 'category_ids',
            'zones' => array(
                'catalog',
            )
        ),
        'products' => array (
            'operators' => array ('in', 'nin'),
            'type' => 'picker',
            'picker_props' => array (
                'picker' => 'pickers/products/picker.tpl',
                'params_cart' => array (
                    'type' => 'table',
                    'aoc' => true
                ),
                'params_catalog' => array (
                    'type' => 'links',
                ),
            ),
            'field_function' => array('fn_promotion_validate_product', '#this', '@product', '@cart_products'),
            'zones' => array(
                'catalog',
            )
        ),
        'purchased_products' => array (
            'operators' => array ('in'),
            'type' => 'picker',
            'picker_props' => array (
                'picker' => 'pickers/products/picker.tpl',
                'params' => array (
                    'type' => 'table',
                    'display' => ''
                ),
            ),
            'field_function' => array('fn_promotion_validate_purchased_product', '#this', '@product', '@auth'),
            'zones' => array(
                'catalog',
            )
        ),
        'users' => array (
            'operators' => array ('in', 'nin'),
            'type' => 'picker',
            'picker_props' => array (
                'picker' => 'pickers/users/picker.tpl',
                'params' => array (
                    'disable_no_item_text' => false,
                ),
            ),
            'field' => '@auth.user_id',
            'zones' => array(
                'catalog',
            )
        ),
        'feature' => array (
            'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt', 'in', 'nin', 'cont', 'ncont'),
            'type' => 'mixed',
            'conditions_function' => array('fn_promotions_get_features'),
            'field_function' => array('fn_promotions_check_features', '#this', '@product'),
            'zones' => array(
                'catalog',
            )
        ),
    ),
    'bonuses' => array(
        'product_discount' => array (
            'function' => array('fn_promotion_apply_catalog_rule', '#this', '@product', '@auth'),
            'discount_bonuses' => array('to_percentage', 'by_percentage', 'to_fixed', 'by_fixed'),
            'zones' => array('catalog'),
        ),
    )
);

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $scheme['conditions']['price']['zones'][] = 'cart';
    $scheme['conditions']['categories']['zones'][] = 'cart';
    $scheme['conditions']['products']['zones'][] = 'cart';
    $scheme['conditions']['users']['zones'][] = 'cart';
    $scheme['conditions']['feature']['zones'][] = 'cart';

    $scheme['conditions']['usergroup'] = array(
        'operators' => array ('eq', 'neq'),
        'type' => 'select',
        'variants_function' => array('fn_get_simple_usergroups', 'C', true),
        'field' => '@auth.usergroup_ids',
        'zones' => array('catalog', 'cart')
    );
    $scheme['conditions']['country'] = array(
        'operators' => array ('eq', 'neq'),
        'type' => 'select',
        'variants_function' => array('fn_get_simple_countries', true),
        'field' => '@cart.user_data.s_country',
        'zones' => array('cart')
    );
    $scheme['conditions']['state'] = array(
        'operators' => array ('eq', 'neq', 'in', 'nin'),
        'type' => 'input',
        'field' => '@cart.user_data.s_state',
        'zones' => array('cart')
    );
    $scheme['conditions']['zip_postal_code'] = array(
        'operators' => array ('eq', 'neq', 'cont', 'ncont', 'in', 'nin'),
        'type' => 'input',
        'field' => '@cart.user_data.s_zipcode',
        'zones' => array('cart')
    );
    $scheme['conditions']['subtotal'] = array(
        'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt', 'in', 'nin'),
        'type' => 'input',
        'field' => 'subtotal',
        'zones' => array('cart')
    );
    $scheme['conditions']['products_number'] = array(
        'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt', 'in', 'nin'),
        'type' => 'input',
        'field_function' => array('fn_get_products_amount', '@cart', '@cart_products', 'C'),
        'zones' => array('cart')
    );
    $scheme['conditions']['total_weight'] = array(
        'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt', 'in', 'nin'),
        'type' => 'input',
        'field_function' => array('fn_get_products_weight', '@cart', '@cart_products', 'C'),
        'zones' => array('cart')
    );
    $scheme['conditions']['payment'] = array(
        'operators' => array ('eq', 'neq'),
        'type' => 'select',
        'variants_function' => array ('fn_get_simple_payment_methods', false),
        'field' => 'payment_id',
        'zones' => array('cart')
    );
    $scheme['conditions']['coupon_code'] = array(
        'operators' => array ('eq', 'in'),
        // 'cont' - 'contains' was removed as ambiguous, but you can uncomment it back
        //'operators' => array ('eq', 'cont', 'in'),
        'type' => 'input',
        'field_function' => array('fn_promotion_validate_coupon', '#this', '@cart', '#id'),
        'zones' => array('cart'),
        'applicability' => array( // applicable for "positive" groups only
            'group' => array(
                'set_value' => true
            ),
        ),
    );
    $scheme['conditions']['number_of_usages'] = array(
        'operators' => array ('lte', 'lt'),
        'type' => 'input',
        'field_function' => array('fn_promotion_get_dynamic', '#id', '#this', 'number_of_usages', '@cart'),
        'zones' => array('cart')
    );
    $scheme['conditions']['once_per_customer'] = array(
        'type' => 'statement',
        'field_function' => array('fn_promotion_get_dynamic', '#id', '#this', 'once_per_customer', '@cart', '@auth'),
        'zones' => array('cart')
    );
    $scheme['conditions']['auto_coupons'] = array(
        'type' => 'list',
        'field_function' => array('fn_promotion_validate_coupon', '#this', '@cart', '#id'),
        'zones' => array('cart'),
        'applicability' => array( // applicable for "positive" groups only
            'group' => array(
                'set_value' => true
            ),
        ),
    );

    $scheme['bonuses']['order_discount'] = array(
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'discount_bonuses' => array('to_percentage', 'by_percentage', 'to_fixed', 'by_fixed'),
        'zones' => array('cart'),
    );
    $scheme['bonuses']['discount_on_products'] = array(
        'type' => 'picker',
        'picker_props' => array (
            'picker' => 'pickers/products/picker.tpl',
            'params' => array (
                'type' => 'links',
            ),
        ),
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'discount_bonuses' => array('to_percentage', 'by_percentage', 'to_fixed', 'by_fixed'),
        'zones' => array('cart'),
    );
    $scheme['bonuses']['discount_on_categories'] = array(
        'type' => 'picker',
        'picker_props' => array (
            'picker' => 'pickers/categories/picker.tpl',
            'params' => array (
                'multiple' => true,
                'use_keys' => 'N',
                'view_mode' => 'table',
            ),
        ),
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'discount_bonuses' => array('to_percentage', 'by_percentage', 'to_fixed', 'by_fixed'),
        'zones' => array('cart'),
    );
    $scheme['bonuses']['give_usergroup'] = array(
        'type' => 'select',
        'variants_function' => array('fn_get_simple_usergroups', 'C'),
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'zones' => array('cart'),
    );
    $scheme['bonuses']['give_coupon'] = array(
        'type' => 'select',
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'variants_function' => array('fn_get_promotions', array('zone' => 'cart', 'auto_coupons' => true, 'simple' => true)),
        'zones' => array('cart'),
    );
    $scheme['bonuses']['free_shipping'] = array(
        'type' => 'select',
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'variants_function' => array('fn_get_shippings', true),
        'zones' => array('cart'),
    );
    $scheme['bonuses']['free_products'] = array(
        'type' => 'picker',
        'picker_props' => array (
            'picker' => 'pickers/products/picker.tpl',
            'params' => array (
                'type' => 'table',
                'aoc' => true
            ),
        ),
        'function' => array('fn_promotion_apply_cart_rule', '#this', '@cart', '@auth', '@cart_products'),
        'zones' => array('cart'),
    );
}

return $scheme;
