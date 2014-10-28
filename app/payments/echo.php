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
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

@set_time_limit(300);

$status = array(
    'D' => 'Declined',
    'C' => 'Cancelled',
    'T' => 'Timeout waiting for host response',
    'R' => 'Received'
);

$error = array(
    '1' => 'Refer to card issuer. The card must be referred to the issuer before the transaction can be approved ',
    '3' => 'Invalid merchant number. The merchant submitting the request is not supported by the acquirer. ',
    '4' => 'Capture card. The card number has been listed on the Warning Bulletin File for reasons of counterfeit, fraud, or other ',
    '5' => 'Do not honor. The transaction was declined by the issuer without definition or reason. ',
    '12' => 'Invalid transaction. The transaction request presented is not supported or is not valid for the card number presented. ',
    '13' => 'Invalid amount. The amount is below the minimum limit or above the maximum limit the issuer allows for this type of transaction. ',
    '14' => 'Invalid card number. The issuer has indicated this card number is not valid. ',
    '15' => 'Invalid issuer. The issuer number is not valid. ',
    '30' => 'Format error. The transaction was not formatted properly. ',
    '41' => 'Lost card. This card has been reported lost. ',
    '43' => 'Stolen card. This card has been reported stolen. ',
    '51' => 'Over credit limit. The transaction will result in an over credit limit or insufficient funds condition.. ',
    '54' => 'Expired card. The card is expired. ',
    '55' => 'Incorrect PIN. The cardholder-entered PIN is incorrect. ',
    '57' => 'Transaction not permitted (card). This card does not support the type of transaction requested ',
    '58' => 'Transaction not permitted (merchant). The merchant\'s account does not support the type of transaction presented. ',
    '61' => 'Daily withdrawal limit exceeded. The cardholder has requested a withdrawal amount in excess of the daily defined maximum. ',
    '62' => 'Restricted card. The card has been restricted. ',
    '63' => 'Security violation. The card has been restricted. ',
    '65' => 'Withdrawal limit exceeded. The allowed number of daily transactions has been exceeded ',
    '75' => 'Pin retries exceeded. The allowed number of PIN retries has been exceeded. ',
    '76' => 'Invalid \'to\' account. The \'to\' (credit) account specified in the transaction does not exist or is not associated with the card number presented. ',
    '77' => 'Invalid \'from\' account. The \'from\' (debit) account specified in the transaction does not exist or is not associated with the card number presented. ',
    '78' => 'Invalid account. The \'from\' (debit) or \'to\' (credit) account does not exist or is not associated with the card number presented. ',
    '84' => 'Invalid cycle. The authorization life cycle is above or below limits established by the issuer. ',
    '91' => 'Issuer not available. The bank is not available to authorize this transaction ',
    '92' => 'Unable to route. The transaction does not contain enough information to be routed to the authorizing agency. ',
    '94' => 'Duplicate transmission. The host has detected a duplicate transmission. ',
    '96' => 'Authorization system error. A system error has occurred or the files required for authorization are not available. ',
    '1000' => 'Unrecoverable error. An unrecoverable error has occurred in the ECHONLINE processing. ',
    '1001' => 'Account closed. The merchant account has been closed. ',
    '1012' => 'Invalid trans code. The host computer received an invalid transaction code. ',
    '1013' => 'Invalid term id. The ECHO-ID is invalid. ',
    '1015' => 'Invalid card number. The credit card number that was sent to the host computer was invalid ',
    '1016' => 'Invalid expiry date. The card has expired or the expiration date was invalid. ',
    '1017' => 'Invalid amount. The dollar amount was less than 1.00 or greater than the maximum allowed for this card. ',
    '1021' => 'Invalid service. The merchant or card holder is not allowed to perform that kind of transaction ',
    '1024' => 'Invalid auth code. The authorization number presented with this transaction is incorrect. (deposit transactions only) ',
    '1025' => 'Invalid reference number. The reference number presented with this transaction is incorrect or is not numeric. ',
    '1508' => 'Invalid or missing order_type. ',
    '1509' => 'The merchant is not approved to submit this order_type. ',
    '1510' => 'The merchant is not approved to submit this transaction_type. ',
    '1511' => 'Duplicate transaction attempt.  ',
    '1599' => 'An system error occurred while validating the transaction input. ',
    '1801' => 'Return Code \'A\'. Address matches; ZIP does not match. ',
    '1802' => 'Return Code \'W\'. 9-digit ZIP matches; Address does not match. ',
    '1803' => 'Return Code \'Z\'. 5-digit ZIP matches; Address does not match. ',
    '1804' => 'Return Codes \'U\'. Issuer unavailable; cannot verify. ',
    '1805' => 'Return Code \'R\'. Retry; system is currently unable to process. ',
    '1806' => 'Return Code \'S\'. or \'G\' Issuer does not support AVS. ',
    '1807' => 'Return Code \'N\'. Nothing matches. ',
    '1808' => 'Return Code \'E\'. Invalid AVS only response. ',
    '1809' => 'Return Code \'B\'. Street address match. Postal code not verified because of incompatible formats. ',
    '1810' => 'Return Code \'C\'. Street address and Postal code not verified because of incompatible formats. ',
    '1811' => 'Return Code \'D\'. Street address match and Postal code match. ',
    '1812' => 'Return Code \'I\'. Address information not verified for international transaction. ',
    '1813' => 'Return Code \'M\'. Street address match and Postal code match. ',
    '1814' => 'Return Code \'P\'. Postal code match. Street address not verified because of incompatible formats. ',
    '1897' => 'Invalid response. The host returned an invalid response. ',
    '1898' => 'Disconnect. The host unexpectedly disconnected. ',
    '1899' => 'Timeout. Timeout waiting for host response. ',
    '2071' => 'Call VISA. An authorization number from the VISA Voice Center is required to approve this transaction. ',
    '2072' => 'Call Master Card. An authorization number from the Master Card Voice Center is required to approve this transaction. ',
    '2073' => 'Call Carte Blanche. An authorization number from the Carte Blanche Voice Center is required to approve this transaction. ',
    '2074' => 'Call Diners Club. An authorization number from the Diners\' Club Voice Center is required to approve this transaction. ',
    '2075' => 'Call AMEX. An authorization number from the American Express Voice Center is required to approve this transaction. ',
    '2076' => 'Call Discover. An authorization number from the Discover Voice Center is required to approve this transaction. ',
    '2078' => 'Call ECHO. The merchant must call ECHO Customer Support for approval.or because there is a problem with the merchant\'s account. ',
    '2079' => 'Call XpresscheX. The merchant must call ECHO Customer Support for approval.or because there is a problem with the merchant\'s account. ',
    '3001' => 'No ACK on Resp. The host did not receive an ACK from the terminal after sending the transaction response. ',
    '3002' => 'POS NAK\'d 3 Times. The host disconnected after the terminal replied 3 times to the host response with a NAK. ',
    '3003' => 'Drop on Wait. The line dropped before the host could send a response to the terminal. ',
    '3005' => 'Drop on Resp. The line dropped while the host was sending the response to the terminal. ',
    '3007' => 'Drop Before EOT. The host received an ACK from the terminal but the line dropped before the host could send the EOT. ',
    '3011' => 'No Resp to ENQ. The line was up and carrier detected, but the terminal did not respond to the ENQ. ',
    '3012' => 'Drop on Input. The line disconnected while the host was receiving data from the terminal. ',
    '3013' => 'FEP NAK\'d 3. Times The host disconnected after receiving 3 transmissions with incorrect LRC from the terminal. ',
    '3014' => 'No Resp to ENQ. The line disconnected during input data wait in Multi-Trans Mode. ',
    '3015' => 'Drop on Input. The host encountered a full queue and discarded the input data. ',
    '9000' => 'Host Error. The host encountered an internal error and was not able to process the transaction.'
);

$cvv_error = array(
    'M' => 'Good match ',
    'N' => 'No match ',
    'P' => 'Not processed ',
    'S' => 'Card issued with Security Code; merchant indicates Security Code is not present ',
    'U' => 'Issuer does not support Security Code '
);

$avs_error = array(
    'X' => 'All digits of address and ZIP match (9-digit ZIP)',
    'Y' => 'All digits of address and ZIP match (5-digit ZIP)',
    'D' => 'Street address and postal code match',
    'M' => 'Street address and postal code match',
    'A' => 'Address matches, ZIP does not',
    'B' => 'Street address match. Postal code not verified because of incompatible formats',
    'P' => 'Postal code match. Street address not verified because of incompatible formats',
    'W' => '9-digit ZIP matches; address does not',
    'Z' => '5-digit ZIP matches, address does not',
    'C' => 'Street address and postal code could not be verified due to incompatible formats',
    'G' => 'Issuer unavailable or AVS not supported (non-US Issuer)',
    'I' => 'Address information not verified for international transaction',
    'R' => 'Retry; system is currently unable to process',
    'S' => 'Card issuer does not support AVS',
    'U' => 'Issuer unavailable or AVS not supported (US Issuer)',
    'E' => 'ECHO received an invalid response from the issuer.',
    'N' => 'Nothing matches'
);

$post = array();
$post['transaction_type'] = 'AV';
$post['order_type'] = 'S';
$post['merchant_echo_id'] = $processor_data['processor_params']['merchant_id'];
$post['merchant_pin'] = $processor_data['processor_params']['merchant_pin'];
$post['billing_ip_address'] = $_SERVER['REMOTE_ADDR'];
$post['merchant_email'] = Registry::get('settings.Company.company_orders_department');
$post['grand_total'] = $order_info['total'];
$post['billing_first_name'] = $order_info['b_firstname'];
$post['billing_last_name'] = $order_info['b_lastname'];
$post['billing_address1'] = $order_info['b_address'];
$post['billing_city'] = $order_info['b_city'];
$post['billing_state'] = $order_info['b_state'];
$post['billing_zip'] = $order_info['b_zipcode'];
$post['billing_country'] = $order_info['b_country'];
$post['billing_phone'] = $order_info['phone'];
$post['billing_email'] = $order_info['email'];
$post['cc_number'] = $order_info['payment_info']['card_number'];
$post['ccexp_month'] = $order_info['payment_info']['expiry_month'];
$post['ccexp_year'] = '20' . $order_info['payment_info']['expiry_year'];
$post['cnp_security'] = $order_info['payment_info']['cvv2'];
$post['merchant_trace_nbr'] = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id);
$post['counter'] = '1';

Registry::set('log_cut_data', array('cc_number', 'ccexp_month', 'ccexp_year', 'cnp_security'));
$result = Http::post("https://wwws1.echo-inc.com:443/scripts/INR200.EXE", $post);

$pp_response['reason_text'] = '';
$response_code = '';

if (preg_match("/<ECHOTYPE3>.*<status>(.*)<\/status>.*<\/ECHOTYPE3>/U", $result, $m)) {
    $response_code = $m[1];
}

if ($response_code == 'G') {
    if (preg_match("/<ECHOTYPE3>.*<order_number>(.*)<\/order_number>.*<\/ECHOTYPE3>/U", $result, $m)) {
        $pp_response['reason_text'].= " (OrderNumber=" . $m[1] . ")";
        $order_number = $m[1];
    }

    if (preg_match("/<ECHOTYPE3>.*<auth_code>(.*)<\/auth_code>.*<\/ECHOTYPE3>/U", $result, $m)) {
        $pp_response['reason_text'].= " (AuthCode=" . $m[1] . ")";
        $authorisation = $m[1];
    }

    if (preg_match("/<ECHOTYPE3>.*<avs_result>(.*)<\/avs_result>.*<\/ECHOTYPE3>/U", $result, $m)) {
        $pp_response['descr_avs'] = (($avs_error[$m[1]]) ? $avs_error[$m[1]] : "AVS Code: " . $m[1]);
    }

    if (preg_match("/<ECHOTYPE3>.*<security_result>(.*)<\/security_result>.*<\/ECHOTYPE3>/U", $result, $m)) {
        $pp_response['descr_cvv'] = (($cvv_error[$m[1]]) ? $cvv_error[$m[1]] : "CVV Code: " . $m[1]);
    }

    $post = array();
    $post['transaction_type'] ='DS';
    $post['order_type'] = 'S';
    $post['merchant_echo_id'] = $processor_data['processor_params']['merchant_id'];
    $post['merchant_pin'] = $processor_data['processor_params']['merchant_pin'];
    $post['billing_ip_address'] = $_SERVER['REMOTE_ADDR'];
    $post['authorization'] = $authorisation;
    $post['merchant_email'] = Registry::get('settings.Company.company_orders_department');
    $post['grand_total'] = $order_info['total'];
    $post['original_amount'] = $order_info['total'];
    $post['cc_number'] = $order_info['payment_info']['card_number'];
    $post['ccexp_month'] = $order_info['payment_info']['expiry_month'];
    $post['ccexp_year'] = '20' . $order_info['payment_info']['expiry_year'];
    $post['cnp_security'] = $order_info['payment_info']['cvv2'];
    $post['merchant_trace_nbr'] = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id);
    $post['order_number'] = $order_number;
    $post['original_trandate_mm'] = date('m');
    $post['original_trandate_dd'] = date('d');
    $post['original_trandate_yyyy'] = date('Y');
    $post['counter'] = '1';

    Registry::set('log_cut_data', array('cc_number', 'ccexp_month', 'ccexp_year', 'cnp_security'));
    $result = Http::post("https://wwws1.echo-inc.com:443/scripts/INR200.EXE", $post);

    $response_code = '';
    if (preg_match("/<ECHOTYPE3>.*<status>(.*)<\/status>.*<\/ECHOTYPE3>/U", $result, $m)) {
        $response_code = $m[1];
    }

    if ($response_code == 'G') {
        if (preg_match("/<ECHOTYPE3>.*<echo_reference>(.*)<\/echo_reference>.*<\/ECHOTYPE3>/U", $result, $m)) {
            $pp_response['reason_text'].= " (ECHO Reference=" . $m[1] . ")";
        }

        $pp_response['order_status'] = 'P';
    } else {
        if (preg_match("/<ECHOTYPE3>.*<decline_code>(.*)<\/decline_code>.*<\/ECHOTYPE3>/U", $result, $m)) {
            $_code = intval($m[1]);
            $_code = $_code > 9000 ? 9000 : $_code;

            $pp_response['reason_text'] .= ": " . (($error[$_code]) ? $error[$_code] : "DeclineCode: " . $_code);
        }

        $pp_response['order_status'] = 'F';
    }

} else {

    $pp_response['order_status'] = 'F';
    $pp_response['reason_text'] .= $status[$response_code];

    if (preg_match("/<ECHOTYPE3>.*<decline_code>(.*)<\/decline_code>.*<\/ECHOTYPE3>/U", $result, $m)) {
        $_code = intval($m[1]);
        $_code = $_code > 9000 ? 9000 : $_code;

        $pp_response['reason_text'] .= ": " . (($error[$_code]) ? $error[$_code] : "DeclineCode: " . $_code);
    }

}

if (empty($result)) {
    $pp_response['order_status'] = 'F';
}
