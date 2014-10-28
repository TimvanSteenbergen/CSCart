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

    if ($mode == 'notify') {

        if (!empty($_REQUEST['refno'])) {
            $order_id = (strpos($_REQUEST['refno'], '_')) ? substr($_REQUEST['refno'], 0, strpos($_REQUEST['refno'], '_')) : $_REQUEST['refno'];
        } else {
            die('DataTrans: incorrect parameters');
        }

        $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
        $processor_data = fn_get_payment_method_data($payment_id);

        $pp_response = array(
            'reason_text' => ''
        );

        if (!empty($_REQUEST['uppTransactionId'])) {
            $pp_response['transaction_id'] = $_REQUEST['uppTransactionId'];
        }

        if (!empty($_REQUEST['authorizationCode'])) {
            $pp_response['reason_text'] .= "AuthCode: " . $_REQUEST['authorizationCode'] . "\n";
        }

        if (!empty($_REQUEST['responseMessage'])) {
            $pp_response['reason_text'] .= "Response Message: " . $_REQUEST['responseMessage'] . "\n";
        }

        if (!empty($_REQUEST['acqAuthorizationCode'])) {
            $pp_response['reason_text'] .= "CC Issuing Bank AuthCode: " . $_REQUEST['acqAuthorizationCode'] . "\n";
        }

        if (!empty($_REQUEST['status'])) {
            $pp_response['reason_text'] .= "Status: " . $_REQUEST['status'] . "\n";
        }

        if (!empty($_REQUEST['sign2'])) {
            $pp_response['reason_text'] .= "Sign: " . $_REQUEST['sign2'] . "\n";
        }

        if (!empty($_REQUEST['errorMessage'])) {
            $pp_response['reason_text'] .= "Error message: " . $_REQUEST['errorMessage'];
            if (!empty($_REQUEST['errorDetail'])) {
                $pp_response['reason_text'] .= "(" . $_REQUEST['errorDetail'] . ")";
            }

            $pp_response['reason_text'] .= "\n";
        }

        if ($_REQUEST['status'] == 'success' && $processor_data['processor_params']['sign'] == $_REQUEST['sign']) {
            $pp_response['order_status'] = 'P';

        } elseif ($_REQUEST['status'] == 'success' && $processor_data['processor_params']['sign'] != $_REQUEST['sign']) {
            $pp_response['order_status'] = 'F';
             $pp_response['reason_text'] .= "Digital signature doesn't match\n";

        } elseif ($_REQUEST['status'] == 'error') {
            $pp_response['order_status'] = 'F';

        } elseif ($_REQUEST['status'] == 'cancel') {
            $pp_response['order_status'] = 'I';

        } else {
            $pp_response['order_status'] = 'F';
        }

        if (fn_check_payment_script('datatrans.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
            if ($action == 'cancel') {
                $mode = 'result';
            }
        }
    }
    if ($mode == 'result') {
        fn_order_placement_routines('route', $_REQUEST['order_id']);
    }
} else {

    $_order_id = ($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id;
    $pp_total = $order_info['total'] * 100;
    $pp_response_url = fn_url("payment_notification.result?payment=datatrans&order_id=$order_id", AREA, 'current');
    $pp_cancel_url = fn_url("payment_notification.notify.cancel?payment=datatrans&order_id=$order_id", AREA, 'current');

    if ($processor_data['processor_params']['mode'] == 'test') {
        $pp_url = "https://pilot.datatrans.biz/upp/jsp/upStart.jsp";
    } else {
        $pp_url = "https://payment.datatrans.biz/upp/jsp/upStart.jsp";
    }

    if (CART_LANGUAGE == 'fr') {
        $language = 'fr';
    } elseif (CART_LANGUAGE == 'de') {
        $language = 'de';
    } else {
        $language = 'en';
    }

    $post_data = array(
        'merchantId' => $processor_data['processor_params']['merchant_id'],
        'amount' => $pp_total,
        'currency' => $processor_data['processor_params']['currency'],
        'refno' => $_order_id,
        'successUrl' => $pp_response_url,
        'errorUrl' => $pp_response_url,
        'cancelUrl' => $pp_cancel_url,
        'language' => $language,
        'reqtype' => $processor_data['processor_params']['transaction_type'],
        'sign' => $processor_data['processor_params']['sign'],        
    );

    fn_create_payment_form($pp_url, $post_data, 'DataTrans');
}
exit;
