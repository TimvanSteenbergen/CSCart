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


if ( !defined('AREA') )	{ die('Access denied');	}

use Tygh\Registry;
use Twigmo\Core\TwigmoConnector;
use Twigmo\Core\TwigmoSettings;

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action == 'check.admin' || $action == 'check.customer') {
    $connector = new TwigmoConnector();
    $connect_till = TwigmoSettings::get('connect_till');
    if (!$connect_till or TIME > $connect_till) {
        $connector->onError('Connection timeout');
    }
    $request = $connector->parseResponse($_REQUEST['data']);
    if (empty($request['data']) or empty($request['data']['url'])) {
        $connector->onError();
    }
    // Request is ok - check url
    if ($action == 'check.admin') {
        if ($request['data']['url'] != $connector->getAdminUrl()) {
            $connector->onError('Wrong admin url');
        }
    } else {
        $stores = fn_twg_get_stores();
        $store = reset($stores);
        $my_url = $connector->getCustomerUrl($store);
        if ($request['data']['url'] != $my_url) {
            $connector->onError('Wrong customer url');
        }
    }
    $connector->respond(array('result' => 'ok'));
} elseif ($action == 'repo.updated') {
    $connector = new TwigmoConnector();
    $stores = fn_twg_get_stores();
    $store = reset($stores);
    $all_stores = TwigmoSettings::get('customer_connections');
    if (empty($store) || empty($all_stores) || !isset($all_stores[$store['company_id']])) {
        $connector->onError('store_not_found');
    }
    $all_stores[$store['company_id']]['repo_revision'] = TIME;
    TwigmoSettings::set(array('customer_connections' => $all_stores));
    $connector->respond(array('result' => 'ok'));
}
