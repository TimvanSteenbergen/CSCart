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

return array(
    'layouts' => array(
        'checked_by_default' => true,
        'function' => 'fn_clone_layouts'
    ),
    'settings' => array(
        'checked_by_default' => true,
        'tables' => array(
            array(
                'name' => 'settings_vendor_values',
                'key' => '', // Do not needed
            ),
        ),
    ),
    'profile_fields' => array(
        'checked_by_default' => true,
        'use_sharing' => true,
    ),
    'pages' => array(
        'use_sharing' => true,
        'tables' => array(
            array(
                'name' => 'pages',
                'key' => 'page_id',
                'post_process' => 'fn_clone_post_process_pages',
                'children' => array(
                    array(
                        'name' => 'page_descriptions',
                        'key' => 'page_id',
                    ),
                ),
            ),
        ),
    ),
    'promotions' => array(
        'use_sharing' => true,
        'tables' => array(
            array(
                'name' => 'promotions',
                'key' => 'promotion_id',
                'children' => array(
                    array(
                        'name' => 'promotion_descriptions',
                        'key' => 'promotion_id',
                    ),
                ),
            ),
        ),
        'tooltip' => 'text_share_promotions_tooltip',
    ),
    'shippings' => array(
        'use_sharing' => true,
        'tables' => array(
            array(
                'name' => 'shippings',
                'key' => 'shipping_id',
                'children' => array(
                    array(
                        'name' => 'shipping_rates',
                        'key' => 'shipping_id',
                        'exclude' => array('rate_id'),
                    ),
                    array(
                        'name' => 'shipping_descriptions',
                        'key' => 'shipping_id',
                    ),
                ),
            ),
        ),
    ),
    'payments' => array(
        'use_sharing' => true,
        'tables' => array(
            array(
                'name' => 'payments',
                'key' => 'payment_id',
                'children' => array(
                    array(
                        'name' => 'payment_descriptions',
                        'key' => 'payment_id',
                    ),
                ),
            ),
        ),
    ),
    'product_filters' => array(
        'use_sharing' => true,
        'tables' => array(
            array(
                'name' => 'product_filters',
                'key' => 'filter_id',
                'children' => array(
                    array(
                        'name' => 'product_filter_descriptions',
                        'key' => 'filter_id',
                    ),
                    array(
                        'name' => 'product_filter_ranges',
                        'key' => 'filter_id',
                        'exclude' => array('range_id'),
                        'return_clone_data' => array(
                            'range_id'
                        ),
                        'children' => array(
                            array(
                                'name' => 'product_filter_ranges_descriptions',
                                'key' => 'range_id',
                            ),
                        ),
                    ),
                ),
                'post_process' => 'fn_clone_post_process_filters',
            ),
        ),
        'tooltip' => 'text_share_product_filters_tooltip',
    ),
    'product_features' => array(
        'use_sharing' => true,
        'tooltip' => 'text_share_product_features_tooltip',
    ),
    'sitemap' => array(
        'tables' => array(
            array(
                'name' => 'sitemap_sections',
                'key' => 'section_id',
                'children' => array(
                    array(
                        'name' => 'common_descriptions',
                        'key' => 'object_id',
                        'condition' => array(
                            'object_holder = "sitemap_sections"'
                        ),
                    ),
                ),
                'return_clone_data' => array(
                    'section_id'
                ),
            ),
            array(
                'name' => 'sitemap_links',
                'key' => 'link_id',
                'children' => array(
                    array(
                        'name' => 'common_descriptions',
                        'key' => 'object_id',
                        'condition' => array(
                            'object_holder = "sitemap_links"'
                        ),
                    )
                )
            ),
        ),
    ),
    'static_data_clone' => array(
        'tables' => array(
            array(
                'name' => 'static_data',
                'key' => 'param_id',
                'post_process' => 'fn_clone_post_process_static_data',
                'children' => array(
                    array(
                        'name' => 'static_data_descriptions',
                        'key' => 'param_id',
                    ),
                ),
            ),
        ),
    ),
    'products' => array(
        'dependence' => 'categories',
        'function' => 'fn_share_products',
    ),
    'categories' => array(
        'tables' => array(
            array(
                'name' => 'categories',
                'key' => 'category_id',
                'post_process' => 'fn_clone_post_process_categories',
                'dependence_tree' => true,
                'children' => array(
                    array(
                        'name' => 'category_descriptions',
                        'key' => 'category_id',
                    ),
                    array(
                        'data_from' => 'products', // Include product tables data
                    )
                ),
            ),
        ),
    ),
);
