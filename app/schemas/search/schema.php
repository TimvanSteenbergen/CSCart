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
    'products' => array(
        'condition_function' => 'fn_create_products_condition',
        'default_params' => array(
            'pshort' => 'Y',
            'pfull' => 'Y',
            'pname' => 'Y',
            'pkeywords' => 'Y',
        ),
        'title' => __('products'),
        'more_data_function' => '',
        'bulk_data_function' => 'fn_gather_additional_products_data_for_search',
        'action_link' => 'products.manage?compact=Y&q=%search%&pshort=Y&pfull=Y&pname=Y&pkeywords=Y&pcode=%search%&pid=%search%&match=any&content_id=products_content',
        'detailed_link' => 'products.update?product_id=%id%',
        'show_in_search' => false,
        'default' => true
    ),
    'pages' => array(
        'condition_function' => 'fn_create_pages_condition',
        'default_params' => array(
            'pdescr' => 'Y',
            'pname' => 'Y',
        ),
        'title' => __('pages'),
        'more_data_function' => '',
        'bulk_data_function' => '',
        'action_link' => 'pages.manage?compact=Y&q=%search%&match=any&content_id=pages_content&pdescr=Y',
        'detailed_link' => 'pages.update?page_id=%id%',
        'show_in_search' => true
    )
);

if (AREA == 'A') {
    $scheme['orders'] = array(
        'condition_function' => 'fn_create_orders_condition',
        'default_params' => array(),
        'title' => __('orders'),
        'more_data_function' => '',
        'bulk_data_function' => '',
        'action_link' => 'orders.manage?order_id=%search%&compact=Y&email=%search%&cname=%search%&content_id=order_content',
        'detailed_link' => 'orders.details?order_id=%id%',
        'show_in_search' => false
    );
    $scheme['users'] = array(
        'condition_function' => 'fn_create_users_condition',
        'default_params' => array(),
        'title' => __('customers'),
        'more_data_function' => '',
        'bulk_data_function' => '',
        'action_link' => 'profiles.manage?name=%search%&email=%search%&user_login=%search%&compact=Y&content_id=users_content',
        'detailed_link' => 'profiles.update?user_id=%id%',
        'show_in_search' => false
     );
}

return $scheme;
