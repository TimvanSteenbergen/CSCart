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

fn_trusted_vars("processor_params", "payment_data");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Update payment method
    //
    if ($mode == 'update') {
        $payment_id = fn_update_payment($_REQUEST['payment_data'], $_REQUEST['payment_id']);
    }

    return array(CONTROLLER_STATUS_OK, "payments.manage");
}

if ($mode == 'delete_certificate') {
    if (!empty($_REQUEST['payment_id'])) {
        $payment_data = fn_get_payment_method_data($_REQUEST['payment_id']);

        if ($payment_data['processor_params']['certificate_filename']) {
            fn_rm(Registry::get('config.dir.certificates') . $_REQUEST['payment_id']);
            $payment_data['processor_params']['certificate_filename'] = '';

            fn_update_payment($payment_data, $_REQUEST['payment_id']);
        }
    }

    return array(CONTROLLER_STATUS_REDIRECT, 'payments.processor?payment_id=' . $_REQUEST['payment_id']);
}

// If any method is selected - show it's settings
if ($mode == 'processor') {
    $processor_data = fn_get_processor_data($_REQUEST['payment_id']);

    // We're selecting new processor
    if (!empty($_REQUEST['processor_id']) && (empty($processor_data['processor_id']) || $processor_data['processor_id'] != $_REQUEST['processor_id'])) {
        $processor_data = db_get_row("SELECT * FROM ?:payment_processors WHERE processor_id = ?i", $_REQUEST['processor_id']);
        $processor_data['processor_params'] = array();
        $processor_data['currencies'] = (!empty($processor_data['currencies'])) ? explode(',', $processor_data['currencies']) : array();
    }

    if (!empty($processor_data) && $processor_data['callback'] == "Y") {
        Registry::get('view')->assign('curl_info', Http::getCurlInfo($processor_data['processor']));
    }

    if (!empty($processor_data['processor_params']['certificate_filename'])) {
        $processor_data['processor_params']['certificate_filename'] = fn_basename($processor_data['processor_params']['certificate_filename']);
    }

    Registry::get('view')->assign('processor_template', $processor_data['admin_template']);
    Registry::get('view')->assign('processor_params', $processor_data['processor_params']);
    Registry::get('view')->assign('processor_name', $processor_data['processor']);
    Registry::get('view')->assign('callback', $processor_data['callback']);
    Registry::get('view')->assign('payment_id', $_REQUEST['payment_id']);

// Show methods list
} elseif ($mode == 'manage') {

    $payments = fn_get_payments(DESCR_SL);

    Registry::get('view')->assign('usergroups', fn_get_usergroups('C', DESCR_SL));
    Registry::get('view')->assign('payments', $payments);
    Registry::get('view')->assign('templates', fn_get_payment_templates());
    Registry::get('view')->assign('payment_processors', fn_get_payment_processors());

} elseif ($mode == 'update') {
    $payment = fn_get_payment_method_data($_REQUEST['payment_id'], DESCR_SL);
    $payment['icon'] = fn_get_image_pairs($payment['payment_id'], 'payment', 'M', true, true, DESCR_SL);

    Registry::get('view')->assign('usergroups', fn_get_usergroups('C', DESCR_SL));
    Registry::get('view')->assign('payment', $payment);
    Registry::get('view')->assign('templates', fn_get_payment_templates($payment));
    Registry::get('view')->assign('payment_processors', fn_get_payment_processors());
    Registry::get('view')->assign('taxes', fn_get_taxes());

    if (Registry::get('runtime.company_id') && Registry::get('runtime.company_id') != $payment['company_id']) {
        Registry::get('view')->assign('hide_for_vendor', true);
    }

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['payment_id'])) {
        $result = fn_delete_payment($_REQUEST['payment_id']);

        if ($result) {
            fn_set_notification('N', __('notice'), __('text_payment_have_been_deleted'));
        } else {
            fn_set_notification('W', __('warning'), __('text_payment_have_not_been_deleted'));
        }

        $count = db_get_field("SELECT COUNT(*) FROM ?:payments");
        if (empty($count)) {
            Registry::get('view')->display('views/payments/manage.tpl');
        }
    }
    exit;
}

function fn_get_payment_templates($payment = array())
{
    $templates = array();
    $company_id = null;

    if (fn_allowed_for('ULTIMATE')) {
        if (!empty($payment['company_id'])) {
            $company_id = $payment['company_id'];
        } else {
            $company_id = Registry::ifGet('runtime.company_id', fn_get_default_company_id());
        }
    }

    $theme_path = fn_get_theme_path('[themes]/[theme]', 'C', $company_id);
    $_templates = fn_get_dir_contents($theme_path . '/templates/views/orders/components/payments/', false, true, '.tpl');

    foreach ($_templates as $template) {
        $templates[$template] = 'views/orders/components/payments/' . $template;
    }

    // Get addons templates as well
    $path = 'addons/[addon]/views/orders/components/payments/';

    $addons = Registry::get('addons');

    foreach ($addons as $addon_id => $addon) {
        $addon_path = str_replace('[addon]', $addon_id, $path);
        $addon_templates = fn_get_dir_contents($theme_path . '/templates/' . $addon_path, false, true, '.tpl');

        if (!empty($addon_templates)) {
            foreach ($addon_templates as $template) {
                $templates[$template] = $addon_path . $template;
            }
        }
    }

    return $templates;
}

function fn_get_payment_processors($lang_code = CART_LANGUAGE)
{
    return db_get_hash_array("SELECT a.processor_id, a.processor, a.type, b.value as description FROM ?:payment_processors as a LEFT JOIN ?:language_values as b ON b.name = CONCAT('processor_description_', REPLACE(a.processor_script, '.php', '')) AND lang_code = ?s ORDER BY processor", 'processor_id', $lang_code);
}
