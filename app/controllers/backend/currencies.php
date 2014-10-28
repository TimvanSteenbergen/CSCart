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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Define trusted variables that shouldn't be stripped
    fn_trusted_vars (
        'currency_data'
    );

    //
    // Update currency
    //
    if ($mode == 'update') {
        $currency_id = fn_update_currency($_REQUEST['currency_data'], $_REQUEST['currency_id'], DESCR_SL);
        if (empty($currency_id)) {
            fn_delete_notification('changes_saved');
        }
    }

    return array(CONTROLLER_STATUS_OK, "currencies.manage");
}

// ---------------------- GET routines ---------------------------------------

if ($mode == 'manage') {

    if (fn_allowed_for('ULTIMATE:FREE') && !defined('AJAX_REQUEST')) {
        fn_set_notification('N', __('notice'), __('change_currency_in_free_mode'), 'K');
    }

    $currencies = fn_get_currencies_list(array(), AREA, DESCR_SL);

    Registry::get('view')->assign('currencies_data', $currencies);

} elseif ($mode == 'update') {

    if (!empty($_REQUEST['currency_id'])) {
        $currency = db_get_row("SELECT a.*, b.description FROM ?:currencies as a LEFT JOIN ?:currency_descriptions as b ON a.currency_code = b.currency_code AND lang_code = ?s WHERE a.currency_id = ?s", DESCR_SL, $_REQUEST['currency_id']);

        Registry::get('view')->assign('currency', $currency);
    }
} elseif ($mode == 'delete') {

    if (!empty($_REQUEST['currency_id'])) {
        $currency_code = db_get_field("SELECT currency_code FROM ?:currencies WHERE currency_id = ?i", $_REQUEST['currency_id']);

        if ($currency_code != CART_PRIMARY_CURRENCY) {
            db_query("DELETE FROM ?:currencies WHERE currency_code = ?s", $currency_code);
            db_query("DELETE FROM ?:currency_descriptions WHERE currency_code = ?s", $currency_code);
            fn_set_notification('N', __('notice'), __('currency_deleted'));
        } else {
            fn_set_notification('W', __('warning'), __('base_currency_not_deleted'));
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, "currencies.manage");

} elseif ($mode == 'update_status') {
    if (fn_allowed_for('ULTIMATE:FREE')) {
        $currency_data = fn_get_currencies_list(array('currency_id' => $_REQUEST['id']), AREA, DESCR_SL);
        $currency_data = reset($currency_data);

        if ($currency_data['is_primary'] == 'Y' && $_REQUEST['status'] != 'A') {
            fn_set_notification('E', __('error'), __('default_currency_status'));

            return array(CONTROLLER_STATUS_REDIRECT, fn_url('currencies.manage'));
        } elseif ($_REQUEST['status'] != 'A') {
            fn_set_notification('E', __('error'), __('currency_hidden_status_free'));

            return array(CONTROLLER_STATUS_REDIRECT, fn_url('currencies.manage'));

        } else {
            $currency_data['is_primary'] = 'Y';

            fn_update_currency($currency_data, $_REQUEST['id'], DESCR_SL);
        }
    }

    fn_tools_update_status($_REQUEST);

    return array(CONTROLLER_STATUS_REDIRECT, fn_url('currencies.manage'));

}
