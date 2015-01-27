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

$schema['conditions']['reward_points'] = array (
    'operators' => array ('eq', 'neq', 'lte', 'gte', 'lt', 'gt'),
    'type' => 'input',
    'field' => '@auth.points',
    'zones' => array('catalog', 'cart')
);

$schema['bonuses']['give_points'] = array (
    'type' => 'input',
    'function' => array('fn_reward_points_promotion_give_points', '#this', '@cart', '@auth', '@cart_products'),
    'zones' => array('cart'),
);

return $schema;
