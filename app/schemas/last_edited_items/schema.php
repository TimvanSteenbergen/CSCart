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
    'products.update' => array(
        'func' => array('fn_get_product_name', '@product_id'),
        'icon' => 'product-item',
        'text' => 'product'
    ),
    'orders.details' => array(
        'func' => array('fn_get_order_name', '@order_id'),
        'icon' => 'order-item',
        'text' => 'order'
    ),
    'categories.update' => array(
        'func' => array('fn_get_category_name', '@category_id'),
        'text' => 'category'
    ),
    'profiles.update' => array(
        'func' => array('fn_get_user_name', '@user_id'),
        'text' => 'user'
    ),
    'shippings.update' => array(
        'func' => array('fn_get_shipping_name', '@shipping_id'),
        'text' => 'shipping_method'
    ),
    'taxes.update' => array(
        'func' => array('fn_get_tax_name', '@tax_id'),
        'text' => 'tax'
    ),
    'destinations.update' => array(
        'func' => array('fn_get_destination_name', '@destination_id'),
        'text' => 'destination'
    ),
    'payments.manage' => array(
        'text' => 'payment_methods'
    ),
    'pages.update' => array(
        'func' => array('fn_get_page_name', '@page_id'),
        'text' => 'page'
    ),
    'companies.update' => array(
        'func' => array('fn_get_company_name', '@company_id'),
        'text' => (fn_allowed_for('MULTIVENDOR')) ? 'vendor' : 'store'
    )
);

if (!fn_allowed_for('ULTIMATE:FREE')) {
    $scheme['usergroups.assign_privileges'] = array(
        'func' => array('fn_get_usergroup_name', '@usergroup_id'),
        'text' => 'usergroup'
    );
}

return $scheme;
