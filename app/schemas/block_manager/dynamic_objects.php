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
    'products' => array (
        'admin_dispatch' => 'products.update',
        'customer_dispatch' => 'products.view',
        'key' => 'product_id',
        'picker' => 'pickers/products/picker.tpl',
        'picker_params' => array (
            'type' => 'links',
        ),
    ),
    'pages' => array (
        'admin_dispatch' => 'pages.update',
        'customer_dispatch' => 'pages.view',
        'key' => 'page_id',
        'picker' => 'pickers/pages/picker.tpl',
        'picker_params' => array (
            'multiple' => true,
            'status' => 'A',
        )
    ),
    'categories' => array (
        'key' => 'category_id',
        'admin_dispatch' => 'categories.update',
        'customer_dispatch' => 'categories.view',
        'picker' => 'pickers/categories/picker.tpl',
        'picker_params' => array (
            'view_mode' => 'blocks',
            'multiple' => true,
            'use_keys' => 'N',
            'status' => 'A',
        ),
    ),
);

if (fn_allowed_for('MULTIVENDOR')) {
    $scheme['companies'] = array(
        'admin_dispatch' => 'companies.update',
        'customer_dispatch' => 'companies.view',
        'key' => 'company_id',
        'picker' => 'pickers/companies/picker.tpl',
        'picker_params' => array(
            'type' => 'links',
            'multiple' => true
        ),
    );
}

return $scheme;
