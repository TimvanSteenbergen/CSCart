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

if (isset($_REQUEST['result'])) {

    require './init_payment.php';

    $result = $_REQUEST['result'];

    include(Registry::get('config.dir.payments') . 'dps_files/pxaccess.inc');

    $payment_id = db_get_field("SELECT ?:payments.payment_id FROM ?:payments LEFT JOIN ?:payment_processors ON ?:payment_processors.processor_id = ?:payments.processor_id WHERE ?:payment_processors.processor_script = 'dps_access.php'");
    $processor_data = fn_get_payment_method_data($payment_id);

    $PxAccess_Url    = "https://sec.paymentexpress.com/pxpay/pxpay.aspx";
    $PxAccess_Userid = $processor_data["processor_params"]["user_id"]; //Change to your user ID
    $PxAccess_Key    =  $processor_data["processor_params"]["key"]; //Your DES Key from DPS
    $Mac_Key = $processor_data["processor_params"]["mac_key"]; //Your MAC key from DPS

    $pxaccess = new PxAccess($PxAccess_Url, $PxAccess_Userid, $PxAccess_Key, $Mac_Key);
    $enc_hex = $result;

    $rsp = $pxaccess->getResponse($enc_hex);
    $order_alias = $rsp->getMerchantReference();
    $_order_id = !empty($order_alias) ? $order_alias : $_SESSION['dps_access']['order_id'];
    $order_id = (strpos($_order_id, '_')) ? substr($_order_id, 0, strpos($_order_id, '_')) : $_order_id;
    $pp_response = array();
    $pp_response['order_status'] = ($rsp->getSuccess() == "1") ? 'P' : 'F';
    $pp_response['reason_text'] = $rsp->getResponseText();
    if ($pp_response['order_status'] == 'P') {
        $pp_response['reason_text'] .= ("; Auth code: " . $rsp->getAuthCode());  // from bank
    }
    $pp_response['transaction_id'] = $rsp->getDpsTxnRef();
    //This payment send two absolutely identical response, so, to avoid double email notifications we should check session data
    if (!isset($_SESSION['dps_access']) && fn_check_payment_script('dps_access.php', $order_id)) {
        fn_finish_payment($order_id, $pp_response, false);
    } else {
        fn_order_placement_routines('route', $order_id);
    }
} else {
    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    // This file is a SAMPLE showing redirect to Payments Page from PHP.
    //Inlcude PxAccess Objects
    include(Registry::get('config.dir.payments') . 'dps_files/pxaccess.inc');

    $PxAccess_Url    = "https://sec.paymentexpress.com/pxpay/pxpay.aspx";
    $PxAccess_Userid = $processor_data["processor_params"]["user_id"]; //Change to your user ID
    $PxAccess_Key    =  $processor_data["processor_params"]["key"]; //Your DES Key from DPS
    $Mac_Key = $processor_data["processor_params"]["mac_key"]; //Your MAC key from DPS

    $pxaccess = new PxAccess($PxAccess_Url, $PxAccess_Userid, $PxAccess_Key, $Mac_Key);

    $request = new PxPayRequest();
    $script_url = fn_payment_url('current', 'dps_access.php');
    $_order_id = ($order_info['repaid']) ? ($order_id .'_'. $order_info['repaid']) : $order_id;
    $_SESSION['dps_access']['order_id'] = $order_id;

    //Set up PxPayRequest Object
    $request->setAmountInput($order_info['total']);
    $request->setTxnData1("");	// whatever you want to appear
    $request->setTxnData2("");	// whatever you want to appear
    $request->setTxnData3("");	// whatever you want to appear
    $request->setTxnType("Purchase");
    $request->setInputCurrency($processor_data["processor_params"]["currency"]);
    $request->setMerchantReference($_order_id); // fill this with your order number
    $request->setEmailAddress($order_info['email']);
    $request->setUrlFail($script_url);
    $request->setUrlSuccess($script_url);

    //Call makeResponse of PxAccess object to obtain the 3-DES encrypted payment request
    $request_string = $pxaccess->makeRequest($request);
    fn_create_payment_form($request_string, array(), 'DPS server', true, 'get');
}
