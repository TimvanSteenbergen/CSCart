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

//Environment
$sandbox = ($processor_data['processor_params']['mode'] == 'test') ? 'base' : 'api';
$post_address = "https://$sandbox.merchantwarrior.com/post/";

//Pass Phrase
$passPhrase = $processor_data['processor_params']['api_passphrase'];

//Post Array
$post = array();

//Method
$post['method'] = 'processCard';

// Authentication Params
$post['merchantUUID'] = $processor_data['processor_params']['merchant_id'];
$post['apiKey'] = $processor_data['processor_params']['api_key'];

// General Transaction Params
$post['transactionCurrency'] = $processor_data['processor_params']['currency'];
$post['transactionProduct'] = $processor_data['processor_params']['order_prefix'] . (($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id);
$post['transactionAmount'] = $order_info['total'];

// Payment Params
$post['paymentCardName'] = $order_info['payment_info']['cardholder_name'];
$post['paymentCardNumber'] = $order_info['payment_info']['card_number'];
$post['paymentCardExpiry'] = $order_info['payment_info']['expiry_month'] . $order_info['payment_info']['expiry_year'];
$post['paymentCardCSC'] = $order_info['payment_info']['cvv2'];

// Customer Params
$post['customerName'] = $order_info['b_firstname'] . ' ' . $order_info['b_lastname'];
$post['customerCountry'] = $order_info['b_country'];
$post['customerState'] = $order_info['b_state_descr'];
$post['customerCity'] = $order_info['b_city'];
$post['customerAddress'] = $order_info['b_address'] . (!empty($order_info['b_address_2'])? ' ; ' . $order_info['b_address_2'] : '');
$post['customerPostCode'] = $order_info['b_zipcode'];
$post['customerIP'] = $_SERVER['REMOTE_ADDR'];

//Transaction Hash
$post['hash'] = md5(fn_strtolower($passPhrase . $processor_data['processor_params']['merchant_id'] . $order_info['total'] . $processor_data['processor_params']['currency']));

// Post a request and analyse the response
Registry::set('log_cut_data', array('paymentCardName', 'paymentCardNumber', 'paymentCardExpiry', 'paymentCardCSC'));
$response_data = Http::post($post_address, $post);

if (!empty($response_data)) {
    // Parse the XML
    $xml = simplexml_load_string($response_data);
    // Convert the result from a SimpleXMLObject into an array
    $xml = (array) $xml;

    // Validate the response - the only successful code is 0
    $status = ((int) $xml['responseCode'] === 0) ? 'P' : 'F';

    // Pass TRN Status, Id and Response
    $pp_response = array(
        'order_status' 	=> $status,
        'transaction_id'=> (isset($xml['transactionID']) ? $xml['transactionID'] : null),
        'reason_text' 	=> ((($pos = strpos($xml['responseMessage'], ':')) === false) ? $xml['responseMessage'] : substr($xml['responseMessage'] , $pos + 1))
    );
} else {
    // Invalid response
    $pp_response = array(
        'order_status' 	=> 'F',
        'transaction_id'=> null,
        'reason_text' 	=> 'API response invalid.'
    );
}
