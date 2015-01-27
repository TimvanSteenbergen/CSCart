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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('GC_PRODUCTS_PER_PAGE', 5);

if ($mode == 'print') {

    $order_info = fn_get_order_info($_REQUEST['order_id']);

    if (isset($order_info['gift_certificates'][$_REQUEST['gift_cert_cart_id']])) {

        $stored_products = array();
        if (!empty($order_info['products'])) {
            foreach ($order_info['products'] as $id => $v) {
                if (isset($v['extra']['parent']['certificate']) && $v['extra']['parent']['certificate'] == $_REQUEST['gift_cert_cart_id']) {
                    $stored_products[$id] = $v;
                }
            }
        }

        fn_show_postal_card($order_info['gift_certificates'][$_REQUEST['gift_cert_cart_id']], $stored_products);
        exit;
    }
}
