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

if (!empty($_REQUEST['Ref'])) {

    $order_id = (strpos($_REQUEST['Ref'], '_')) ? substr($_REQUEST['Ref'], 0, strpos($_REQUEST['Ref'], '_')) : $_REQUEST['Ref'];
    if (fn_check_payment_script('proxypay3.php', $order_id)) {
        fn_change_order_status($order_id, 'P', '', true);
        $pp_response = array();
        $pp_response['order_status'] = 'P';
        print '[OK]';
    } else {
        $pp_response['reason_text'] = 'Error in data confirmation'; // FIXME: this variable is not used
        print '[ERROR]';
    }
}
exit;
