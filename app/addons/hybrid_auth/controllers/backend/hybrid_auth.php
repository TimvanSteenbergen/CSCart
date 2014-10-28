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

    if ($mode == 'update_provider' && isset($_REQUEST['provider_data'])) {
        fn_hybrid_auth_update_provider($_REQUEST['provider_data']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "hybrid_auth.manage");
}

if ($mode == 'update' && !empty($_REQUEST['provider'])) {

    $providers_schema = fn_get_schema('hybrid_auth', 'providers');
    $available_providers = array_keys($providers_schema);
    $provider_data = fn_hybrid_auth_get_provider_data($_REQUEST['provider']);

    // Delete currently used providers except the one you edit at the moment.
    $providers = array_keys(fn_hybrid_auth_get_providers_list());
    $providers = array_diff($providers, array($_REQUEST['provider']));
    $available_providers = array_diff($available_providers, $providers);

    if (!empty($provider_data['provider_id'])) {
        Registry::get('view')->assign('id', $provider_data['provider_id']);
    } else {
        Registry::get('view')->assign('id', 0);
    }

    Registry::get('view')->assign('provider', $_REQUEST['provider']);
    Registry::get('view')->assign('providers_schema', $providers_schema);
    Registry::get('view')->assign('available_providers', $available_providers);
    Registry::get('view')->assign('provider_data', $provider_data);

} elseif ($mode == 'manage') {

    $providers_schema = fn_get_schema('hybrid_auth', 'providers');
    $available_providers = array_keys($providers_schema);
    $providers_list = fn_hybrid_auth_get_providers_list(false);

    $available_providers = array_diff($available_providers, array_keys($providers_list));

    Registry::get('view')->assign('id', 0);
    Registry::get('view')->assign('provider', reset($available_providers));
    Registry::get('view')->assign('providers_schema', $providers_schema);
    Registry::get('view')->assign('available_providers', $available_providers);
    Registry::get('view')->assign('providers_list', $providers_list);

} elseif ($mode == 'delete_provider') {
    if (!empty($_REQUEST['provider'])) {
        fn_hybrid_auth_delete_provider($_REQUEST['provider']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "hybrid_auth.manage");

} elseif ($mode == 'select_provider') {

    $providers_schema = fn_get_schema('hybrid_auth', 'providers');
    $provider_data = fn_hybrid_auth_get_provider_data($_REQUEST['provider']);

    Registry::get('view')->assign('id', $_REQUEST['id']);
    Registry::get('view')->assign('provider', $_REQUEST['provider']);
    Registry::get('view')->assign('providers_schema', $providers_schema);
    Registry::get('view')->assign('provider_data', $provider_data);

    Registry::get('view')->display('addons/hybrid_auth/views/hybrid_auth/update.tpl');

    exit;
}
