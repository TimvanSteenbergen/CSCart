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

$sslcert = Registry::get('config.dir.certificates') . (isset($processor_data['processor_params']['certificate_filename']) ? $processor_data['processor_params']['certificate_filename'] : '');
$post_url = ($processor_data["processor_params"]["mode"] == 'test') ? "https://webmerchantaccount.ptc.quickbooks.com/j/AppGateway" : "https://webmerchantaccount.quickbooks.com/j/AppGateway";

$post_data = array();
$post_data[] = "<?xml version=\"1.0\"?>";
$post_data[] = "<?qbmsxml version=\"2.0\"?>";
$post_data[] = "<QBMSXML>";
$post_data[] = " <SignonMsgsRq>";
$post_data[] = "  <SignonAppCertRq>";
$post_data[] = "   <ClientDateTime>".date("Y-d-m\TH:i:s")."</ClientDateTime>";
$post_data[] = "   <ApplicationLogin>".$processor_data["processor_params"]["app_login"]."</ApplicationLogin>";
$post_data[] = "   <ConnectionTicket>".$processor_data["processor_params"]["connection_ticket"]."</ConnectionTicket>";
$post_data[] = "  </SignonAppCertRq>";
$post_data[] = " </SignonMsgsRq>";
$post_data[] = "</QBMSXML>";

$_response = Http::post($post_url, implode("\n", $post_data), array(
    'headers' => array(
        'Content-type: application/x-qbmsxml'
    ),
    'ssl_cert' => $sslcert,
    'ssl_key' => $sslcert
));

$root = fn_qb_get_xml_body($_response);
if (is_object($root)) {
    $session_ticket = $root->getValueByPath('SignonMsgsRs/SignonAppCertRs/SessionTicket');

    // POST Credit Card information
    $post_data = array();
    $post_data[] = '<?xml version="1.0"?>';
    $post_data[] = '<?qbmsxml version="2.0"?>';
    $post_data[] = '<QBMSXML>';
    $post_data[] = '<SignonMsgsRq>';
    $post_data[] = '<SignonTicketRq>';
    $post_data[] = '<ClientDateTime>' . date('Y-d-m\TH:i:s') . '</ClientDateTime>';
    $post_data[] = '<SessionTicket>' . $session_ticket . '</SessionTicket>';
    $post_data[] = '</SignonTicketRq>';
    $post_data[] = '</SignonMsgsRq>';
    $post_data[] = '<QBMSXMLMsgsRq>';
    $post_data[] = '<CustomerCreditCardChargeRq requestID="1">';
    $post_data[] = '<TransRequestID>' . $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id) . '</TransRequestID>';
    $post_data[] = '<CreditCardNumber>' . $order_info['payment_info']['card_number'] . '</CreditCardNumber>';
    $post_data[] = '<ExpirationMonth>' . $order_info['payment_info']['expiry_month'] . '</ExpirationMonth>';
    $post_data[] = '<ExpirationYear>20' . $order_info['payment_info']['expiry_year'] . '</ExpirationYear>';
    $post_data[] = '<IsCardPresent>1</IsCardPresent>';
    $post_data[] = '<Amount>' . $order_info['total'] . '</Amount>';
    $post_data[] = '<NameOnCard>' . $order_info['payment_info']['cardholder_name'] . '</NameOnCard>';
    $post_data[] = '<CreditCardAddress>' . $order_info['b_address'] . '</CreditCardAddress>';
    $post_data[] = '<CreditCardPostalCode>' . $order_info['b_zipcode'] . '</CreditCardPostalCode>';
    $post_data[] = '<CardSecurityCode>' . $order_info['payment_info']['cvv2'] . '</CardSecurityCode>';
    $post_data[] = '</CustomerCreditCardChargeRq>';
    $post_data[] = '</QBMSXMLMsgsRq>';
    $post_data[] = '</QBMSXML>';

    // Make a request to the QBMS Server
    Registry::set('log_cut_data', array('CreditCardNumber', 'ExpirationMonth', 'ExpirationYear', 'CardSecurityCode'));
    $__response = Http::post($post_url, implode("\n", $post_data), array(
        'headers' => array(
            'Content-type: application/x-qbmsxml'
        ),
        'ssl_cert' => $sslcert,
        'ssl_key' => $sslcert
    ));

    // Parse the Response from the Server
    $root = fn_qb_get_xml_body($__response);
    $signon = $root->getElementByPath("SignonMsgsRs/SignonTicketRs");
    $response['signon_status'] = $signon->getAttribute("statusCode");
    $customer = $root->getElementByPath("QBMSXMLMsgsRs/CustomerCreditCardChargeRs");
    $response['customer_status'] = $customer->getAttribute("statusCode");

    // Got Signon error
    if (!empty($response['signon_status'])) {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = $response['signon_status'] . ': ' . $signon->getAttribute("statusMessage");

    // Got Customer error
    } elseif (!empty($response['customer_status'])) {
        $pp_response['order_status'] = 'F';
        $pp_response['reason_text'] = $response['customer_status'] . ': ' . $customer->getAttribute("statusMessage");

    // Transaction is successfull
    } else {
        $pp_response['order_status'] = 'P';
        $pp_response['reason_text'] = $customer->getValueByPath('/PaymentStatus'). '; Auth Code: ' . $customer->getValueByPath('/AuthorizationCode');
        $pp_response['transaction_id'] = $customer->getValueByPath('/CreditCardTransID') .'; '. __('customer').': '. $customer->getValueByPath('/ClientTransID');
        $pp_response['descr_avs'] = 'AVSStreet: '.$customer->getValueByPath('/AVSStreet') .'; AVSZip: '.$customer->getValueByPath('/AVSZip');
        $customer->getValueByPath('/CardSecurityCodeMatch');
    }
} else {
    $pp_response['order_status'] = 'F';
}

function fn_qb_get_xml_body($response)
{
    $doc = new XMLDocument();
    $xp = new XMLParser();
    $xp->setDocument($doc);
    $xp->parse($response);
    $doc = $xp->getDocument();
    $root = $doc->getRoot();

    return $root;
}
