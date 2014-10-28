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

if ($mode == 'complete') {
    $orders_info = array();
    $order_info = Registry::get('view')->getTemplateVars('order_info');
    if (!fn_allowed_for('MULTIVENDOR') || (fn_allowed_for('MULTIVENDOR') && $order_info['is_parent_order'] == 'N')) {
        $orders_info[0] = $order_info;
        $orders_info[0]['ga_company_name'] = fn_get_company_name($order_info['company_id']);
    } else {
        $order_ids = explode(',', $order_info['child_ids']);
        foreach ($order_ids as $k => $order_id) {
            $_order_info = fn_get_order_info($order_id);
            $orders_info[$k] = $_order_info;
            $orders_info[$k]['ga_company_name'] = fn_get_company_name($_order_info['company_id']);
        }
    }
    Registry::get('view')->assign('orders_info', $orders_info);
}
