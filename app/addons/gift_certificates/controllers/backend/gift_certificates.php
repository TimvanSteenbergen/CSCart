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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD']	== 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars('gift_cert_data');

    if ($mode == 'update') {

        $min = Registry::get('addons.gift_certificates.min_amount') * 1;
        $max = Registry::get('addons.gift_certificates.max_amount') * 1;

        if ($_REQUEST['gift_cert_data']['amount'] < $min || $_REQUEST['gift_cert_data']['amount'] > $max) {
            fn_set_notification('E', __('error'), __('gift_cert_error_amount', array(
                '[min]' => $min,
                '[max]' => $max
            )));
        } else {
            $gift_cert_id = fn_update_gift_certificate($_REQUEST['gift_cert_data'], $_REQUEST['gift_cert_id'], $_REQUEST);
        }

        $suffix = ".update?gift_cert_id=$gift_cert_id";
    }

    if ($mode == 'preview') {

        if (!empty($_REQUEST['gift_cert_data'])) {
            fn_correct_gift_certificate($_REQUEST['gift_cert_data']);
            fn_show_postal_card($_REQUEST['gift_cert_data']);
            exit;
        }
    }

    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['gift_cert_ids'])) {
            foreach ($_REQUEST['gift_cert_ids'] as $v) {
                fn_delete_gift_certificate($v);
            }
        }
        $suffix = ".manage";
    }

    if ($mode == 'update_certificate_statuses' && is_array($_REQUEST['certificate_statuses'])) {
        foreach ($_REQUEST['certificate_statuses'] as $k => $v) {
            if ($_REQUEST['origin_statuses'][$k] != $v) {
                fn_change_gift_certificate_status($k, $v, $_REQUEST['origin_statuses'][$k], fn_get_notification_rules($_REQUEST)); // @GIFT_CERT_ID, @TO, @FROM
            }
        }
        $suffix = ".manage";
    }

    return array(CONTROLLER_STATUS_OK, "gift_certificates$suffix");
}


if ($mode == 'add') {

    if (!empty($_REQUEST['user_id'])) {
        $user_data = fn_get_user_info($_REQUEST['user_id']);
        $gift_cert_data = array(
            'send_via'		 => 'E',
            'recipient' 	 => "$user_data[firstname] $user_data[lastname]",
            'sender' 		 => Registry::get('settings.Company.company_name'),
            'email' 		 => $user_data['email'],
            'address' 		 => $user_data['s_address'],
            'address_2' 	 => $user_data['s_address_2'],
            'city' 	 		 => $user_data['s_city'],
            'country' 		 => $user_data['s_country'],
            'state' 		 => $user_data['s_state'],
            'zipcode' 		 => $user_data['s_zipcode'],
            'phone' 		 => $user_data['phone']
        );
        Registry::get('view')->assign('gift_cert_data', $gift_cert_data);
    }

    Registry::get('view')->assign('templates', fn_get_gift_certificate_templates());
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));

} elseif ($mode == 'update') {

    $gift_cert_id = intval($_REQUEST['gift_cert_id']);
    $gift_cert_data = fn_get_gift_certificate_info($gift_cert_id);

    if (empty($gift_cert_data) || (!empty($gift_cert_id) && !fn_check_company_id('gift_certificates', 'gift_cert_id', $gift_cert_id))) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    // [Page sections]
    Registry::set('navigation.tabs', array (
        'detailed' => array (
            'title' => __('detailed_info'),
            'js' => true
        ),
        'log' => array (
            'title' => __('history'),
            'js' => true
        ),
    ));
    // [/Page sections]

    list($log, $search) = fn_get_gift_certificate_log($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('log', $log);
    Registry::get('view')->assign('search', $search);

    if (false != ($last_item = reset($log))) {
        $gift_cert_data['amount'] = $last_item['debit'];
        $gift_cert_data['products'] = $last_item['debit_products'];
    }

    Registry::get('view')->assign('templates', fn_get_gift_certificate_templates());
    Registry::get('view')->assign('states', fn_get_all_states());
    Registry::get('view')->assign('countries', fn_get_simple_countries(true, CART_LANGUAGE));

    Registry::get('view')->assign('gift_cert_data', $gift_cert_data);

} elseif ($mode == 'manage') {

    list($gift_certificates, $search) = fn_get_gift_certificates($_REQUEST, Registry::get('addons.gift_certificates.cert_per_page'));

    Registry::get('view')->assign('gift_certificates', $gift_certificates);
    Registry::get('view')->assign('search', $search);

    fn_gift_certificates_generate_sections('manage');

} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['gift_cert_id'])) {
        $result = fn_delete_gift_certificate($_REQUEST['gift_cert_id'], @$_REQUEST['extra']);

        return array(CONTROLLER_STATUS_REDIRECT, !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : "gift_certificates." . ($result ? "manage" : ("update?gift_cert_id=" . $_REQUEST['gift_cert_id'])));
    }

} elseif ($mode == 'update_status') {
    $gift_cert_data = db_get_row("SELECT status, amount FROM ?:gift_certificates WHERE gift_cert_id = ?i", $_REQUEST['id']);
    $min = Registry::get('addons.gift_certificates.min_amount') * 1;
    $max = Registry::get('addons.gift_certificates.max_amount') * 1;

    if ($gift_cert_data['amount'] < $min || $gift_cert_data['amount'] > $max) {
        fn_set_notification('E', __('error'), __('gift_cert_error_amount', array(
            '[max]' => $max,
            '[min]' => $min
        )));

        Registry::get('ajax')->assign('return_status', $gift_cert_data['status']);
    } elseif (fn_change_gift_certificate_status($_REQUEST['id'], $_REQUEST['status'], '', fn_get_notification_rules($_REQUEST))) {
        fn_set_notification('N', __('notice'), __('status_changed'));
    } else {
        fn_set_notification('E', __('error'), __('error_status_not_changed'));
        Registry::get('ajax')->assign('return_status', $gift_cert_data['status']);
    }
    exit;
}


function fn_update_gift_certificate($gift_cert_data, $gift_cert_id = 0, $params = array())
{
    fn_correct_gift_certificate($gift_cert_data);

    $gift_cert_data['products'] = !empty($gift_cert_data['products']) ? serialize($gift_cert_data['products']) : '';

    if (empty($gift_cert_id)) {
        do {
            $code = fn_generate_gift_certificate_code();
        } while (true == fn_check_gift_certificate_code($code));

        $gift_cert_data = fn_array_merge($gift_cert_data, array(
            'gift_cert_code' => $code,
            'timestamp' => TIME)
        );

        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            $gift_cert_data['company_id'] = Registry::get('runtime.company_id');
        }

        $gift_cert_id = db_query("INSERT INTO ?:gift_certificates ?e", $gift_cert_data);
    } else {

        // Change certfificate status
        fn_change_gift_certificate_status($gift_cert_id, $gift_cert_data['status'], '', fn_get_notification_rules(array(), false));

        //if difference then add line in log
        $debit_info = db_get_row("SELECT debit AS amount, debit_products AS products FROM ?:gift_certificates_log WHERE gift_cert_id = ?i ORDER BY timestamp DESC", $gift_cert_id);

        if (empty($debit_info)) {
            $debit_info = db_get_row("SELECT amount, products FROM ?:gift_certificates WHERE gift_cert_id = ?i", $gift_cert_id);
        }

        $is_diff = (($gift_cert_data['amount'] - $debit_info['amount'] != 0) || (md5($gift_cert_data['products']) != md5($debit_info['products'])));
        if ($is_diff == true) {
            $_info = array(
                'amount' => $gift_cert_data['amount'],
                'products' => $gift_cert_data['products']
            );
            fn_add_gift_certificate_log_record($gift_cert_id, $debit_info, $_info);
        }

        //Update certificate data
        $_data = $gift_cert_data;
        db_query("UPDATE ?:gift_certificates SET ?u WHERE gift_cert_id = ?i", $gift_cert_data, $gift_cert_id);
    }

    $gc_data = fn_get_gift_certificate_info($gift_cert_id);
    fn_gift_certificate_notification($gc_data, fn_get_notification_rules($params));

    return $gift_cert_id;
}
