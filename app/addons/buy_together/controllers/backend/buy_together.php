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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    fn_trusted_vars('item_data');

    if ($mode == 'update') {
        fn_buy_together_update_chain($_REQUEST['item_id'], $_REQUEST['product_id'], $_REQUEST['item_data'], $auth, DESCR_SL);

        return array(CONTROLLER_STATUS_OK, "products.update?product_id=" . $_REQUEST['product_id'] . "&selected_section=buy_together");
    }

    return;
}

if ($mode == 'update') {

    $params = array(
        'chain_id' => $_REQUEST['chain_id'],
        'simple' => true,
        'full_info' => true,
    );

    $chain = fn_buy_together_get_chains($params, array(), DESCR_SL);

    Registry::get('view')->assign('item', $chain);

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['chain_id'])) {
        $product_id = fn_buy_together_delete_chain($_REQUEST['chain_id']);

        return array(CONTROLLER_STATUS_REDIRECT, "products.update?product_id=$product_id&selected_section=buy_together");
    }
}
