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
    'categories' => 'categories',
    'categories.catalog' => 'catalog',
    'categories.view' => 'view_categories',
    'checkout' => 'checkout',
    'checkout.cart' => 'cart',
    'checkout.complete' => 'order_landing_page',
    'index' => 'index',
    'orders' => 'orders',
    'orders.details' => 'order_details',
    'orders.search' => 'order_search',
    'pages' => 'pages',
    'pages.view' => 'view_page',
    'product_features' => 'features',
    'product_features.compare' => 'compare_product_features',
    'product_features.view' => 'view_product_features',
    'product_features.view_all' => 'view_all_product_features',
    'products' => 'products',
    'products.search' => 'search_product',
    'products.view' => 'view_product',
    'profiles' => 'profiles',
    'promotions' => 'promotions',
    'search' => 'search',
    'search.results' => 'search_results',
);

if (fn_allowed_for('MULTIVENDOR')) {
    $scheme['companies.view'] = 'vendors';
}

return $scheme;
