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

use Tygh\Http;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

    if ($mode == 'notify') {

        $order_id = (strpos($_REQUEST['MerchantReference'], '_')) ? substr($_REQUEST['MerchantReference'], 0, strpos($_REQUEST['MerchantReference'], '_')) : $_REQUEST['MerchantReference'];
        $order_info = fn_get_order_info($order_id);
        $processor_data = fn_get_payment_method_data($order_info['payment_id']);

        $pp_response = array();
        $pp_response['transaction_id'] = $_REQUEST['TransactionId'];
        $pp_response['transaction_datetime'] = $_REQUEST['TransactionDateTime'];
        $pp_response['reason_text'] = $_REQUEST['ResponseDescription'];

        if ($_REQUEST['ResultCode'] == 0) {
            if ( $_REQUEST['StatusFlag'] == 'Success' && in_array($_REQUEST['ResponseCode'], array('00', '08', '10', '11', '16')) ) {

                $tran_ticket = db_get_field("SELECT data FROM ?:order_data WHERE type = 'E' AND order_id = ?i", $order_id);

                $cart_hashkey_str = $tran_ticket . $processor_data['processor_params']['posid']. $processor_data['processor_params']['acquirerid'] . $_REQUEST['MerchantReference'] . $_REQUEST['ApprovalCode'] . $_REQUEST['Parameters'] . $_REQUEST['ResponseCode'] . $_REQUEST['SupportReferenceID'] . $_REQUEST['AuthStatus'] . $_REQUEST['PackageNo'] . $_REQUEST['StatusFlag'];
                $cart_hashkey = strtoupper(hash( 'sha256', $cart_hashkey_str ));

                if ($cart_hashkey == $_REQUEST['HashKey']) {
                    $pp_response['order_status'] = 'P';

                    db_query("DELETE FROM ?:order_data WHERE type = 'E' AND order_id = ?i", $order_id);

                    if (!empty($_REQUEST['SupportReferenceID'])) {
                        $pp_response['reason_text'] .= '; SupportReferenceID: ' . $_REQUEST['SupportReferenceID'];
                    }

                    if (!empty($_REQUEST['ApprovalCode'])) {
                        $pp_response['reason_text'] .= '; ApprovalCode: ' . $_REQUEST['ApprovalCode'];
                    }
                } else {
                    $pp_response['order_status'] = 'F';
                    $pp_response['reason_text'] .= 'Hash value is incorrect';
                }
            } elseif ($_REQUEST['StatusFlag'] == 'Failure') {
                $pp_response['order_status'] = 'F';

            } elseif ($_REQUEST['StatusFlag'] == 'Asynchronous') {
                $pp_response['order_status'] = 'O';
                $pp_response['reason_text'] .= '; Asynchronous';
            }

        } else {
            $pp_response['order_status'] = 'F';
        }

        if (fn_check_payment_script('piraeus.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    } elseif ($mode == 'cancel') {

        if (!empty($_SESSION['stored_piraeus_orderid'])) {
            $order_id = $_SESSION['stored_piraeus_orderid'];
            unset($_SESSION['stored_piraeus_orderid']);
        } else {
            fn_order_placement_routines('checkout_redirect');
        }

        $pp_response['order_status'] = 'N';
        $pp_response["reason_text"] = __('text_transaction_cancelled');

        if (fn_check_payment_script('piraeus.php', $order_id)) {
            fn_finish_payment($order_id, $pp_response, false);
        }

        fn_order_placement_routines('route', $order_id);
    }

} else {
    $ticketing_data = Array (
        'AcquirerId' => $processor_data['processor_params']['acquirerid'],
        'MerchantId' => $processor_data['processor_params']['merchantid'],
        'PosId' => $processor_data['processor_params']['posid'],
        'Username' => $processor_data['processor_params']['username'],
        'Password' => md5($processor_data['processor_params']['password']),
        'RequestType' => $processor_data['processor_params']['requesttype'],
        'CurrencyCode' => $processor_data['processor_params']['currencycode'],
        'MerchantReference' => (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id),
        'Amount' => $order_info['total'],
        'Installments' => 0,
        'Bnpl' => 0,
        'ExpirePreauth' => (($processor_data['processor_params']['requesttype'] == '00') ? $processor_data['processor_params']['expirepreauth'] : '0'),
        'Parameters' => ''
    );

    $str = <<<EOT
<?xml version="1.0" encoding="utf-8"?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<IssueNewTicket xmlns="http://piraeusbank.gr/paycenter/redirection">
<Request>
EOT;
    $str .= fn_array_to_xml($ticketing_data);
    $str .= <<<EOT
</Request>
</IssueNewTicket>
</soap:Body>
</soap:Envelope>
EOT;
    $str = str_replace(array("\t", "\n", "\r"), '', $str);

    $response_data = Http::post("https://paycenter.piraeusbank.gr/services/tickets/issuer.asmx", $str, array(
        'headers' => array(
            'Content-type: text/xml; charset=utf-8',
            'SOAPAction: http://piraeusbank.gr/paycenter/redirection/IssueNewTicket'
        )
    ));

    $resultcode = true;
    $pp_response = array();

    if (strpos($response_data, '<ResultCode') !== false) {
        if (preg_match('!<ResultCode[^>]*>([^>]+)</ResultCode>!', $response_data, $matches)) {
            $resultcode = $matches[1];
        }
    }

    if ($resultcode == "0") {

        if (strpos($response_data, '<TranTicket') !== false) {
            if (preg_match('!<TranTicket[^>]*>([^>]+)</TranTicket>!', $response_data, $matches)) {
                $data = array (
                    'order_id' => $order_id,
                    'type' => 'E',
                    'data' => $matches[1],
                );
                db_query("REPLACE INTO ?:order_data ?e", $data);
            }
        }

        $post_url = 'https://paycenter.piraeusbank.gr/redirection/pay.aspx';

        $post_data = array (
            'AcquirerId' => $processor_data['processor_params']['acquirerid'],
            'MerchantId' => $processor_data['processor_params']['merchantid'],
            'PosId' => $processor_data['processor_params']['posid'],
            'User' => $processor_data['processor_params']['username'],
            'LanguageCode' => $processor_data['processor_params']['languagecode'],
            'MerchantReference' => (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id),
            'ParamBackLink' => ""
        );

        $_SESSION['stored_piraeus_orderid'] = $order_id;

        fn_create_payment_form($post_url, $post_data, 'Piraeus server');
    exit;

    } else {
        $pp_response['order_status'] = 'F';
        $pp_response["ResultCode"] = $resultcode;

        if (strpos($response_data, '<ResultDescription') !== false) {
            if (preg_match('!<ResultDescription[^>]*>([^>]+)</ResultDescription>!', $response_data, $matches)) {
                $pp_response["reason_text"] = $matches[1];
            }
        }
    }

}
