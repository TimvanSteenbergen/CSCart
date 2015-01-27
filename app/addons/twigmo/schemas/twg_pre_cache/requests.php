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
    'index.index' => array(
        'params' => array(
            'action' => 'get',
            'object' => 'homepage'
        )
    ),
    'products.view' => array(
        'params' => array(
            'action' => 'details',
            'object' => 'products',
        ),
        'param_values' => array(
            'id' => 'product_id'
        )
    ),
    'categories.view' => array(
        'params' => array(
            'action' => 'get',
            'object' => 'catalog',
        ),
        'param_values' => array(
            'category_id' => 'category_id'
        )
    ),
    'pages.view' => array(
        'params' => array(
            'action' => 'get',
            'object' => 'page',
        ),
        'param_values' => array(
            'page_id' => 'page_id'
        )
    ),
);

return $schema;
