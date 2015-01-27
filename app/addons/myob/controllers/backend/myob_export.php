<?php
/***************************************************************************
*                                                                          *
*    Copyright (c) 2009 Kabarty P/L. All rights reserved.    			   *
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

    if ($mode == 'export_profiles') {
        header('Content-type: text/csv');
        header('Content-disposition: attachment; filename=shop-customers' . date('Ymd') . '.txt');
        foreach ($_REQUEST['user_ids'] as $k => $v) {
            $users[$k] = fn_get_user_info($v);
        }

        Registry::get('view')->assign('users', $users);

        Registry::get('view')->display('addons/myob/views/users/components/export_to_myob.tpl');
        exit;
    }

    if ($mode == 'export_orders') {
        header('Content-type: text/csv');
        header('Content-disposition: attachment; filename=shop-orders' . date('Ymd') . '.txt');

        foreach ($_REQUEST['order_ids'] as $k => $v) {
            $orders[$k] = fn_get_order_info($v);
            $orders[$k]['order_date'] = date('j/n/Y', $orders[$k]['timestamp']);
            $orders[$k]['paid_amount'] = 0;	//TODO: Update this!

            foreach ($orders[$k]['products'] as $ik => $iv) {
                $option_desc = "";
                $desc_count = 0;
                if (!empty($orders[$k]['products'][$ik]['product_options'])) {
                    foreach ($orders[$k]['products'][$ik]['product_options'] as $option_key => $option_value) {
                        if ($desc_count > 0) {
                            $option_desc .= ", ";
                        } else {
                            $option_desc = ' (';
                        }
                        $option_desc .= $option_value['option_name'] . ' = ' . $option_value['variant_name'];
                        $desc_count++;
                    }
                }
                if ($option_desc != '') {
                    $option_desc .= ' )';
                }
                $orders[$k]['products'][$ik]['prod_opts_description'] = $orders[$k]['products'][$ik]['product'] . $option_desc;
            }
        }

        Registry::get('view')->assign('orders', $orders);

        Registry::get('view')->display('addons/myob/views/orders/components/export_to_myob.tpl');
        exit;
    }
}
