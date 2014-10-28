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
use Tygh\Mailer;
use Tygh\Registry;
use Tygh\Session;

// Skrill Quick Checkout and Skrill eWallet payment systems

if (!defined('BOOTSTRAP')) { die('Access denied'); }

include_once (Registry::get('config.dir.payments') . 'skrill_func.php');

if (defined('PAYMENT_NOTIFICATION')) {
    if (AREA == 'A') {
        $master_account_cust_id = '13661561';
        $master_account_secret_word = 'secretword';

        $mb_exit_needed = false;
        if (in_array($mode, array('validate_email', 'activate', 'validate_secret_word'))) {
            $mb_exit_needed = true;
        }

        if (empty($_REQUEST['payment_id']) && $mb_exit_needed) {
            fn_set_notification('W', __('warning'), __('text_skrill_payment_is_not_saved'));
            exit;
        }

        if ($mode == 'validate_email') {

            if (!empty($_REQUEST['email']) && !empty($_REQUEST['payment_id'])) {
                $processor_params = array();
                $processor_params['pay_to_email'] = $_REQUEST['email'];

                $get_data = array();
                $get_data['email'] = $_REQUEST['email'];
                $get_data['cust_id'] = $master_account_cust_id;
                $get_data['password'] = md5($master_account_secret_word);
                $result = Http::get("https://www.skrill.com/app/email_check.pl", $get_data);

                $result_array = explode(',', $result);

                if ($result_array[0] == 'OK') {
                    $processor_params['customer_id'] = $result_array[1];
                    fn_set_notification('N', __('notice'), __('text_skrill_email_is_registered'));
                } else {
                    fn_set_notification('E', __('error'), __('text_skrill_email_is_not_registered'));
                }

                $old_processor_data = fn_get_processor_data($_REQUEST['payment_id']);
                $old_processor_param = (empty($old_processor_data['processor_params'])) ? array() : $old_processor_data['processor_params'];
                $new_processor_param = $processor_params;
                $new_processor_param = array_merge($old_processor_param, $new_processor_param);
                $new_processor_data = serialize($new_processor_param);

                db_query('UPDATE ?:payments SET processor_params = ?s WHERE payment_id = ?i', $new_processor_data, $_REQUEST['payment_id']);

                Registry::get('ajax')->assign('customer_id_' . $_REQUEST['payment_id'], $processor_params['customer_id']);
            }
        }

        if ($mode == 'activate') {

            if (!empty($_REQUEST['payment_id']) && !empty($_REQUEST['email']) && !empty($_REQUEST['cust_id']) && !empty($_REQUEST['platform']) && !empty($_REQUEST['merchant_firstname']) && !empty($_REQUEST['merchant_lastname'])) {
                Mailer::sendMail(array(
                    'to' => 'ecommerce@skrill.com',
                    'from' => $_REQUEST['email'],
                    'data' => array(
                        'mb_firstname' => $_REQUEST['merchant_firstname'],
                        'mb_lastname' => $_REQUEST['merchant_lastname'],
                        'platform' => $_REQUEST['platform'],
                        'email' => $_REQUEST['email'],
                        'cust_id' => $_REQUEST['cust_id'],
                    ),
                    'tpl' => 'payments/cc_processors/activate_skrill.tpl'
                ), 'A', Registry::get('settings.Appearance.backend_default_language'));

                fn_set_notification('W', __('important'), __('text_skrill_activate_quick_checkout_short_explanation_1', array(
                    '[date]' => date('m.d.Y')
                )));

            } else {
                fn_set_notification('E', __('error'), __('text_skrill_empty_input_data') );
            }
        }

        if ($mode == 'validate_secret_word') {
            if (!empty($_REQUEST['email']) && !empty($_REQUEST['payment_id']) && !empty($_REQUEST['cust_id']) && !empty($_REQUEST['secret'])) {
                $processor_params['pay_to_email'] = $_REQUEST['email'];

                $get_data = array();
                $get_data['email'] = $_REQUEST['email'];
                $get_data['cust_id'] = $master_account_cust_id;
                $get_data['secret'] = md5(md5($_REQUEST['secret']) . md5($master_account_secret_word));
                $result = Http::get("https://www.skrill.com/app/secret_word_check.pl", $get_data);

                $result_array = explode(',', $result);

                if ($result_array[0] == 'OK') {
                    fn_set_notification('N', __('notice'), __('text_skrill_secret_word_is_correct'));
                } else {
                    fn_set_notification('E', __('error'), __('text_skrill_secret_word_is_incorrect', array(
                        '[date]' => date('m.d.Y')
                    )));
                }

                $processor_params['secret_word'] = $_REQUEST['secret'];
                $old_processor_data = fn_get_processor_data($_REQUEST['payment_id']);
                $old_processor_param = (empty($old_processor_data['processor_params'])) ? array() : $old_processor_data['processor_params'];
                $new_processor_param = $processor_params;
                $new_processor_param = array_merge($old_processor_param, $new_processor_param);
                $new_processor_data = serialize($new_processor_param);

                db_query('UPDATE ?:payments SET processor_params = ?s WHERE payment_id = ?i', $new_processor_data, $_REQUEST['payment_id']);

                Registry::get('ajax')->assign('secret_word_' . $_REQUEST['payment_id'], $processor_params['secret_word']);
            }
        }

        if ($mb_exit_needed) {
            exit;
        }
    }

    $pp_response = array();
    if ($mode == 'return') {
        if (!empty($_REQUEST['iframe_mode'])) {
            define('MB_MAX_TIME', 60); // Time for awaiting callback

            $view->assign('order_action', __('placing_order'));
            $view->display('views/orders/components/placing_order.tpl');
            fn_flush();

            $_placed = false;
            $times = 0;

            while (!$_placed) {
                $order_id = db_get_field('SELECT order_id FROM ?:order_data WHERE type = ?s AND data = ?s', 'E', $_REQUEST['order_id']);

                if (!empty($order_id)) {
                    $_placed = true;
                } else {
                    sleep(1);
                }

                $times++;
                if ($times > MB_MAX_TIME) {
                    break;
                }
            }
        } else {
            $order_id = $_REQUEST['order_id'];
        }

        // If order was placed successfully, associate the order with this customer
        if (!empty($order_id)) {
            $auth['order_ids'][] = $order_id;

            if (fn_check_payment_script('skrill_qc.php', $order_id)) {
                $order_info = fn_get_order_info($_REQUEST['order_id'], true);
                if ($order_info['status'] == 'N') {
                    fn_change_order_status($_REQUEST['order_id'], 'O', '', false);
                }
                fn_order_placement_routines('route', $order_id, false);
            }
        } else {
            fn_set_notification('E', __('error'), __('text_mb_failed_order'));
            fn_order_placement_routines('checkout_redirect');
        }

    } elseif ($mode == 'cancel') {

        if (!empty($_REQUEST['iframe_mode'])) {
            fn_set_notification('E', __('error'), __('text_transaction_cancelled'));
            fn_order_placement_routines('checkout_redirect');
        }

        if (fn_check_payment_script('skrill_qc.php', $_REQUEST['order_id'])) {
            $pp_response['order_status'] = 'N';
            $pp_response['reason_text'] = __('text_transaction_declined');

            fn_finish_payment($_REQUEST['order_id'], $pp_response);
            fn_order_placement_routines('route', $_REQUEST['order_id']);
        }
    } elseif ($mode == 'unsupported_currency') {
        if (fn_check_payment_script('skrill_qc.php', $_REQUEST['order_id'])) {
            $pp_response = array();
            $pp_response['order_status'] = 'F';
            $pp_response['reason_text'] = __('text_unsupported_currency');

            fn_finish_payment($_REQUEST['order_id'], $pp_response);
            fn_order_placement_routines('route', $_REQUEST['order_id']);
        }
    } elseif ($mode == 'status') {

        if (!empty($_REQUEST['iframe_mode'])) {
            $_REQUEST['order_id'] = fn_mb_place_order($_REQUEST);
        }

        $_status_descr = array(
            '2' => 'Processed',
            '0' => 'Pending',
            '-1' => 'Cancelled',
            '-2' => 'Failed',
            '-3' => 'Chargeback'
        );

        $payment_id = db_get_field('SELECT payment_id FROM ?:orders WHERE order_id=?i', $_REQUEST['order_id']);
        $processor_data = fn_get_payment_method_data($payment_id);
        $order_info = fn_get_order_info($_REQUEST['order_id']);

        $response_fields = array(
                'pay_to_email',
                'pay_from_email',
                'merchant_id',
                'customer_id',
                'transaction_id',
                'mb_transaction_id',
                'mb_amount',
                'mb_currency',
                'status',
                'md5sig',
                'amount',
                'currency',
                'payment_type',
                'merchant_fields',
            );

        $response_data = array();

        foreach ($response_fields as $field) {
            if (isset($_REQUEST[$field])) {
                $response_data[$field] = $_REQUEST[$field];
            }
        }

        $our_md5sig_string = $response_data['merchant_id'] . $response_data['transaction_id'] . strtoupper(md5($processor_data['processor_params']['secret_word'])) . $response_data['mb_amount'] . $response_data['mb_currency'] . $response_data['status'];

        $our_md5sig = strtoupper(md5($our_md5sig_string));

        $pp_response = array();

        $pp_response['status'] = $_REQUEST['status'] . (!empty($_status_descr[$_REQUEST['status']]) ? (' (' . $_status_descr[$_REQUEST['status']] . ')') : '');

        $pp_response['mb_transaction_id'] = $_REQUEST['mb_transaction_id'];
//		$pp_response['mb_amount'] = $mb_amount;
//		$pp_response['mb_currency'] = $mb_currency;
        $pp_response['pay_from_email'] = $_REQUEST['pay_from_email'];
        $pp_response['payment_type'] = !empty($_REQUEST['payment_type']) ? $_REQUEST['payment_type'] : '';

        $__curr = $processor_data['processor_params']['currency'];

        $adjusted_order_total = fn_mb_adjust_amount($order_info['total'], $__curr);

        if (($_REQUEST['md5sig'] == $our_md5sig) && $adjusted_order_total && ($_REQUEST['amount'] == $adjusted_order_total) && ($_REQUEST['currency'] == $processor_data['processor_params']['currency'])) {
            if ($_REQUEST['status'] == '2') {
                $pp_response['order_status'] = 'P';
                $pp_response['reason_text'] = __('approved');
            } elseif ($_REQUEST['status'] == '0') {
                $pp_response['order_status'] = 'O';
                $pp_response['reason_text'] = __('pending');
            } elseif ($_REQUEST['status'] == '-1') {
                $pp_response['order_status'] = 'I';
                $pp_response['reason_text'] = __('cancelled');
            } else {
                $pp_response['order_status'] = 'F';
                $pp_response['reason_text'] = __('failed');
            }
        } else {
            $pp_response['order_status'] = 'F';

            if ($_REQUEST['md5sig'] != $our_md5sig) {
                $pp_response['reason_text'] .= __('mb_md5_hashes_not_match');
            }
            if (!$adjusted_order_total) {
                $pp_response['reason_text'] .= __('text_unsupported_currency');
            } elseif ($_REQUEST['amount'] != $adjusted_order_total) {
                $pp_response['reason_text'] .= __('mb_amounts_not_match');
            }
            if ($_REQUEST['currency'] != $processor_data['processor_params']['currency']) {
                $pp_response['reason_text'] .= __('mb_currencies_not_match');
            }
        }

        if (fn_check_payment_script('skrill_qc.php', $_REQUEST['order_id'])) {
            fn_finish_payment($_REQUEST['order_id'], $pp_response);
        }
        exit;
    }

} else {
    $url = 'https://www.moneybookers.com/app/payment.pl';

    $suffix = (AREA != 'A' && empty($order_info['repaid']) && defined('IFRAME_MODE')) ? '&iframe_mode=true' : '';

    $post_data = array(
        'pay_to_email' => $processor_data['processor_params']['pay_to_email'],
        'recipient_description' => $processor_data['processor_params']['recipient_description'],
        'transaction_id' => $processor_data['processor_params']['order_prefix'] . (!empty($order_info['repaid']) ? ($order_id . '_' . $order_info['repaid']) : $order_id),
        'return_url' => fn_url("payment_notification.return?payment=skrill_qc&order_id=$order_id$suffix", AREA, 'current'),
        'return_url_text' => '',
        'cancel_url' => fn_url("payment_notification.cancel?payment=skrill_qc&order_id=$order_id$suffix", AREA, 'current'),

        'status_url' => fn_url("payment_notification.status?payment=skrill_qc&order_id=$order_id$suffix", AREA, 'current'),

        'language' => $processor_data['processor_params']['language'],

        'amount' => $order_info['total'],
        'currency' => $processor_data['processor_params']['currency'],

        // iframe_target
        'return_url_target' => '_parent',
        'cancel_url_target' => '_parent',

        'merchant_fields' => 'platform,mb_sess_id,inner_order_id',
        'mb_sess_id' => base64_encode(Session::getId()),
        'inner_order_id' => $order_id,

        'platform' => '21477207'

    );

    $post_data['amount'] = fn_mb_adjust_amount($post_data['amount'], $post_data['currency']);

    if (!$post_data['amount']) {
        if (!empty($suffix)) {
            echo __('text_unsupported_currency');
        } else {
            fn_set_notification('E', __('error'), __('text_unsupported_currency'));
            $url = fn_url("payment_notification.unsupported_currency?payment=skrill_qc&order_id=$order_id", AREA, 'current');

            fn_create_payment_form($url, array());
        }

        exit;
    }

    // gateway_fast_registration
    $post_data['firstname'] = $order_info['b_firstname'];
    $post_data['lastname'] = $order_info['b_lastname'];
    $post_data['pay_from_email'] = $order_info['email'];

    //$post_data['pay_from_email'] = rand(). "@" . rand() . ".com"; // uncomment to test hide login feature
    $post_data['address'] = $order_info['b_address'];
    $post_data['address2'] = $order_info['b_address_2'];
    $post_data['postal_code'] = $order_info['b_zipcode'];
    $post_data['city'] = $order_info['b_city'];
    $post_data['state'] = fn_get_state_name($order_info['b_state'], $order_info['b_country']);
    if (empty($post_data['state'])) {
        $post_data['state'] = $order_info['b_state'];
    }
    if (fn_strlen($post_data['state']) > 50) {
        $post_data['state'] = fn_substr($post_data['state'], 0, 47) . '...';
    }
    $post_data['country'] = db_get_field('SELECT code_A3 FROM ?:countries WHERE code=?s', $order_info['b_country']);
    $post_data['phone_number'] = $order_info['phone'];

    if ($processor_data['processor_params']['quick_checkout'] == 'Y') {
        $post_data['payment_methods'] = !empty($processor_data['processor_params']['payment_methods']) ? '' : 'ACC';
        $post_data['hide_login'] = '1';
    } else {
        $post_data['payment_methods'] = 'WLT';
        $post_data['hide_login'] = (!empty($suffix)) ? '1' : '0';
    }

    // split_gateway
    if (!empty($processor_data['processor_params']['payment_methods'])) {
        $post_data['payment_methods'] .= (!empty($post_data['payment_methods']) ? ',' : '') . $processor_data['processor_params']['payment_methods'];
    }
    // /split_gateway

    // logo
    if (!(!empty($processor_data['processor_params']['do_not_pass_logo']) && $processor_data['processor_params']['do_not_pass_logo'] == 'Y')) {
        $logos = fn_get_logos();
        $post_data['logo_url'] = $logos['theme']['image']['image_path'];
    }

    fn_create_payment_form($url, $post_data, 'Skrill');
    exit;    
}
