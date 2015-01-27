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
    if ($mode == 'process_embedded') {
        if (!empty($_REQUEST['data'])) {
            $data = json_decode($_REQUEST['data'], true);
            if (!empty($data)) {
                fn_create_payment_form($data['submit_url'], $data['data'], $data['payment_name'], $data['exclude_empty_values'], $data['method']);
            }
        }
        exit;
    }
}

if (!empty($_REQUEST['payment'])) {
    define('PAYMENT_NOTIFICATION', true);

    $payment = fn_basename($_REQUEST['payment']);

    if (fn_check_prosessor_status($payment)) {
        $payment_script = Registry::get('config.dir.payments') . $payment . '.php';
        if (in_array($mode, array('checkout_redirect', 'index_redirect'))) {
            fn_order_placement_routines($mode);
        } elseif (is_file($payment_script)) {
            include($payment_script);
        }
    }
}
