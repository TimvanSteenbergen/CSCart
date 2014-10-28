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

if (defined('PAYMENT_NOTIFICATION')) {
    if ($mode == 'process') {
        $pp_response["order_status"] = (($_REQUEST['Result'] == "1") ? 'P' : 'F');

        if ($_REQUEST['Result'] == 2) {
            $pp_response["reason_text"] = "Error";

        } elseif ($_REQUEST['Result'] == 3) {
            $pp_response["order_status"] = 'I';
            $pp_response["reason_text"] = "Cancelled";
        }

        if (isset($_REQUEST['ErrorMessage'])) {
            $pp_response["reason_text"].= ": " . $_REQUEST['ErrorMessage'];
        }

        if (isset($_REQUEST['DeltaPayId'])) {
            $pp_response["transaction_id"] = $_REQUEST['DeltaPayId'];
        }

        $order_id = (strpos($_REQUEST['Param1'], '_')) ? substr($_REQUEST['Param1'], 0, strpos($_REQUEST['Param1'], '_')) : $_REQUEST['Param1'];

        if (fn_check_payment_script('deltapay.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
            fn_order_placement_routines('route', $order_id);
        }
    }

} else {
    $amount = str_replace('.', ',', $order_info["total"]);
    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;

    $submit_url = 'https://www.deltapay.gr/entry.asp';
    $post_data = array(
        'merchantCode' => $processor_data['processor_params']['merchant_id'],
        'param1' => $_order_id,
        'charge' => $amount,
        'currencycode' => $processor_data['processor_params']['currency'],
        'transactiontype' => '1',
        'installments' => '0',
        'cardholderemail' => $order_info['email']
    );

    fn_create_payment_form($submit_url, $post_data, 'DeltaPay server');
exit;
}
