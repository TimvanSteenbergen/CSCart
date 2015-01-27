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

require './init_payment.php';

$totals_100 = array('EUR', 'USD', 'GBP', 'CHF', 'NLG', 'DEM', 'FRF', 'ATS');

if (!empty($_REQUEST['Ref'])) {
    $order_id = (strpos($_REQUEST['Ref'], '_')) ? substr($_REQUEST['Ref'], 0, strpos($_REQUEST['Ref'], '_')) : $_REQUEST['Ref'];

    $result = db_get_row("SELECT * FROM ?:orders WHERE order_id = ?i", $order_id);
    $processor_data = fn_get_payment_method_data($result['payment_id']);

    if (!empty($_REQUEST['totals_100']) && !in_array($processor_data['processor_params']['currency'], $_REQUEST['totals_100'])) {
        $total_cost = $result['total'];
    } else {
        $total_cost = $result['total'] * 100;
    }

    $amount = !empty($_REQUEST['Amount']) ? ($_REQUEST['Amount'] + 0) : 0; // what is zero for?
    $currency = !empty($_REQUEST['Currency']) ? ($_REQUEST['Currency'] + 0) : 0;
    if (fn_check_payment_script('proxypay3.php', $order_id)) {
        if ((0 + $result['total'] == $amount) && ($processor_data['processor_params']['merchantid'] == $_REQUEST['Shop']) && (0 + $processor_data['processor_params']['currency'] == $currency) && !empty($order_id) && !empty($amount) && !empty($currency)) {
            fn_change_order_status($order_id, 'O');
            echo('[OK]');
        } else {
            fn_change_order_status($order_id, 'D', '', true);
            echo('[ERROR]');
        }
    }

} else {
    echo('[NOREF]');
}
