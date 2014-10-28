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

use Tygh\Registry;

include_once(Registry::get('config.dir.addons') . 'rma/schemas/breadcrumbs/backend.functions.php');

$schema['rma.confirmation'] = array (
    array(
        'title' => 'return_requests',
        'link' => 'rma.returns'
    )
);
$schema['rma.details'] = array (
    array (
        'type' => 'search',
        'prev_dispatch' => 'rma.returns',
        'title' => 'search_results',
        'link' => 'rma.returns.last_view'
    ),
    array (
        'title' => 'return_requests',
        'link' => 'rma.returns.reset_view'
    )
);
$schema['rma.create_return'] = array (
    array(
        'title' => array(
            'function' => array('fn_br_rma_order_title', '@order_id')
        ),
        'link' => 'orders.details?order_id=%ORDER_ID'
    )
);

return $schema;
