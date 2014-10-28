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
    'get' => array(
        'catalog' => array(
            'dispatch'  => 'categories.view',
            'key'       => 'category_id',
            'key_name'  => 'category_id'
        ),
        'orders' => array(
            'dispatch'  => 'orders.search',
        ),
        'products' => array(
            'dispatch'  => 'products.search',
            'key'       => 'q',
            'key_name'  => 'q'
        )
    ),
    'details' => array(
        'products' => array(
            'dispatch'  => 'products.view',
            'key'       => 'product_id',
            'key_name'  => 'id'
        ),
        'order' => array(
            'dispatch'  => 'orders.details',
            'key'       => 'order_id',
            'key_name'  => 'id'
        ),


    )
);

return $schema;
