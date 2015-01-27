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

if (defined('PAYMENT_NOTIFICATION')) {

    if (!defined('BOOTSTRAP')) { die('Access denied'); }

    fn_order_placement_routines('route', $_REQUEST['order_id'], false);

} else {
    if (!defined('BOOTSTRAP')) {

        // response from payment
        require './init_payment.php';

        if (!empty($_REQUEST['invoice_id'])) {
            $order_id = $_REQUEST['invoice_id'];
        } elseif (!empty($_REQUEST['ClientUniqueID'])) {
            $order_id = $_REQUEST['ClientUniqueID'];
        } elseif (!empty($_REQUEST['merchant_unique_id'])) {
            $order_id = $_REQUEST['merchant_unique_id'];
        } else {
            exit;
        }

        if (!fn_check_payment_script('gate2shop.php', $order_id, $processor_data)) {
            exit;
        }

        $keys = array('TransactionID', 'ErrCode', 'ExErrCode', 'Status');
        foreach ($keys as $k) {
            if (empty($_REQUEST[$k])) {
                $_REQUEST[$k] = 0;
            }
        }

        $hashed = md5($processor_data['processor_params']['secret_string'] . $_REQUEST['TransactionID'] . $_REQUEST['ErrCode'] . $_REQUEST['ExErrCode'] . $_REQUEST['Status']);

        $pp_response = array();

        if (!empty($_REQUEST['Status']) && $_REQUEST['Status'] == 'APPROVED' && !empty($_REQUEST['responsechecksum']) && $_REQUEST['responsechecksum'] == $hashed) {
            $pp_response['order_status']   = 'P';
            $pp_response['reason_text']    = __('transaction_approved');
            $pp_response['transaction_id'] = !empty($_REQUEST['TransactionID']) ? $_REQUEST['TransactionID'] : '';
        } else {
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text']  = '';
            if (!empty($_REQUEST['Reason'])) {
                $pp_response['reason_text'] .= ($_REQUEST['Reason'] . ' ;');
            }
            if (!empty($_REQUEST['ExErrCode']) && !empty($_REQUEST['error'])) {
                $pp_response['reason_text'] .= ($_REQUEST['ExErrCode'] . ': ' . $_REQUEST['error'] . ';');
            }
            $pp_response['reason_text'] .= __('md5_checksum_failed');
        }

        fn_finish_payment($order_id, $pp_response);

        if (!empty($_REQUEST['unknownParameters']) && strpos($_REQUEST['unknownParameters'], 'admin') !== false) {
            $return_area = 'A';
        } else {
            $return_area = 'C';
        }
        $return_url = fn_url("payment_notification.return?payment=gate2shop&order_id=$order_id", $return_area, 'current');

        echo "<html><body onLoad=\"javascript: self.location='$return_url'\"></body></html>";

    } else {

        if (!defined('BOOTSTRAP')) { die('Access denied'); }

        $post = array();

        $post['merchant_site_id']   = $processor_data['processor_params']['merchant_site_id'];
        $post['merchant_id']        = $processor_data['processor_params']['merchant_id'];
        $post['time_stamp']         = gmdate('Y-m-d.H:i:s');
        $post['currency']           = $processor_data['processor_params']['currency'];
        $post['total_amount']       = $order_info['total'];
        $post['numberofitems']      = 1;
        $post['invoice_id']         = $order_id;
        $post['merchant_unique_id'] = $processor_data['processor_params']['order_prefix'] . $order_id . ($order_info['repaid'] ? "_{$order_info['repaid']}" : '');

        $post['item_name_1']   = __('order_id') . ': ' . $processor_data['processor_params']['order_prefix'] . $order_id . ($order_info['repaid'] ? "_{$order_info['repaid']}" : '');
        $post['item_name_1']   = fn_convert_encoding('UTF-8', 'ISO-8859-1', $post['item_name_1']);
        $post['item_number_1'] = 1;
        $post['item_quantity_1']    = 1;
        $post['item_amount_1'] = $post['total_amount'];
        $products2checksum = ($post['item_name_1'] . $post['item_amount_1'] . $post['item_quantity_1']);

        $post['first_name'] = $order_info['b_firstname'];
        $post['last_name']  = $order_info['b_lastname'];
        $post['email']      = $order_info['email'];
        $post['phone1']     = $order_info['phone'];
        $post['address1']   = $order_info['b_address'];
        $post['address2']   = $order_info['b_address_2'];
        $post['city']       = $order_info['b_city'];
        $post['state']      = $order_info['b_state'];
        $post['zip']        = $order_info['b_zipcode'];
        $post['country']    = $order_info['b_country'];

        $post['checksum'] = md5($processor_data['processor_params']['secret_string'] . $post['merchant_id'] . $post['currency'] . $post['total_amount'] . $products2checksum . $post['time_stamp']);

        $post['version'] = '3.0.0';

        if (AREA == 'A') {
            $post['area'] = 'admin';
        }

        fn_create_payment_form('https://secure.gate2shop.com/ppp/purchase.do', $post, 'Gate2Shop');
        exit;
    }
}
