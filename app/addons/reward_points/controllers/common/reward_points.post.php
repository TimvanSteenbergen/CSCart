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
    return;
}

if ($mode == 'userlog') {

    $params = $_REQUEST;
    if (AREA == 'C') {
        $params['user_id'] = $auth['user_id'];
    }

    if (empty($params['user_id'])) {
        if (AREA == 'C') {
            return array(CONTROLLER_STATUS_REDIRECT, "auth.login_form?return_url=" . urlencode(Registry::get('config.current_url')));
        } else {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }
    }

    if (AREA == 'A') {
        $user = fn_get_user_info($params['user_id'], false);

        if (fn_allowed_for('ULTIMATE')) {
            if (empty($user)) {
                return array(CONTROLLER_STATUS_NO_PAGE);
            }

            if (Registry::get('settings.Stores.share_users') == 'Y' && Registry::get('runtime.company_id')) {
                $orders_ids = db_get_fields("SELECT order_id FROM ?:orders WHERE user_id = ?i AND company_id = ?i", $params['user_id'], Registry::get('runtime.company_id'));
                if (empty($orders_ids)) {
                    return array(CONTROLLER_STATUS_NO_PAGE);
                }
            }
        }

        Registry::get('view')->assign('user', $user);
    } else {
        fn_add_breadcrumb(__('reward_points_log'));
    }

    list($userlog, $search) = fn_gift_registry_get_userlog($params, Registry::get('addons.reward_points.log_per_page'));

    Registry::get('view')->assign('userlog', $userlog);
    Registry::get('view')->assign('search', $search);
}

function fn_gift_registry_get_userlog($params, $items_per_page = 0)
{
    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $sortings = array (
        'timestamp' => 'timestamp',
        'amount' => 'amount'
    );

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:reward_point_changes WHERE user_id = ?i", $params['user_id']);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $userlog = db_get_array("SELECT change_id, action, timestamp, amount, reason FROM ?:reward_point_changes WHERE user_id = ?i $sorting $limit", $params['user_id']);

    return array($userlog, $params);
}
