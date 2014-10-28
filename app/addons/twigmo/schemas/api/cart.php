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
    'object_name' => 'cart',
    'fields' => array (
        'products' => array (
            'process_get' => array (
                'func' => 'fn_twg_api_get_cart_products',
                'params' => array (
                    'products' => array (
                        'db_field' => 'products'
                    ),
                    'lang_code' => array (
                        'param' => 'lang_code'
                    )
                )
            ),
        ),
        'amount' => array (
            'name' => 'amount'
        ),
        'total' => array (
            'name' => 'total'
        ),
        'subtotal' => array (
            'name' => 'subtotal'
        ),
        'display_subtotal' => array (
            'name' => 'display_subtotal'
        ),
        'including_discount' => array (
            'name' => 'discount'
        ),
        'order_discount' => array (
            'name' => 'subtotal_discount'
        ),
        'shipping_cost' => array (
            'name' => 'shipping_cost'
        ),
        'tax' => array (
            'name' => 'tax_subtotal'
        ),
        'taxes' => array (
            'name' => 'taxes'
        ),
        'payment_surcharge' => array (
            'name' => 'payment_surcharge'
        ),
        'shipping_required' => array (
            'name' => 'shipping_required'
        ),
        'payment_id' => array (
            'name' => 'payment_id'
        ),
        'promotion_input_field' => array (
            'process_get' => array (
                'func' => 'fn_twg_api_get_cart_promotion_input_field',
                'params' => array ()
            ),
        )
    )
);
return $schema;
