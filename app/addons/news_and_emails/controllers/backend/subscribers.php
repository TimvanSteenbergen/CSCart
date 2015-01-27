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
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD']	== 'POST') {

    $suffix = '.manage';

    if ($mode == 'update') {
        if (!empty($_REQUEST['subscriber_data']['list_ids'])) {
            $list_id = reset($_REQUEST['subscriber_data']['list_ids']);
            if (!empty($list_id)) {
                $suffix .= '?list_id=' . $list_id;
            }
        }
        fn_update_subscriber($_REQUEST['subscriber_data'], $_REQUEST['subscriber_id']);
    }

    if ($mode == 'add_users') {

        if (!empty($_REQUEST['add_users'])) {
            $checked_users = array();

            $users = db_get_array("SELECT user_id, email, lang_code FROM ?:users WHERE user_id IN (?n)", $_REQUEST['add_users']);

            $list_ids = array();
            if (!empty($_REQUEST['list_id'])) {
                $list_ids[] = $_REQUEST['list_id'];
                $suffix .= '?list_id=' . $_REQUEST['list_id'];
            }

            foreach ($users as $user) {
                $subscriber_data = array(
                    'email' => $user['email'],
                    'lang_code' => $user['lang_code'],
                    'list_ids' => $list_ids,
                );

                fn_update_subscriber($subscriber_data);

            }
        }
    }

    if ($mode == 'm_update') {
        if (!empty($_REQUEST['subscribers'])) {
            foreach ($_REQUEST['subscribers'] as $subscriber_id => $v) {
                fn_update_subscriber($v, $subscriber_id);
            }
        }
    }

    if ($mode == 'm_delete') {
        fn_delete_subscribers($_REQUEST['subscriber_ids']);
    }

    return array(CONTROLLER_STATUS_OK, 'subscribers' . $suffix);
}

if ($mode == 'manage') {

    list($subscribers, $search) = fn_get_subscribers($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    foreach ($subscribers as &$subscriber) {
        if (!empty($subscriber['list_ids'])) {
            $subscriber['mailing_lists'] = array();
            foreach (explode(',', $subscriber['list_ids']) as $list_id) {
                $subscriber['mailing_lists'][$list_id] = fn_get_mailing_list_data($list_id, DESCR_SL);
                // get additional user-specific data for each mailing list (like lang_code)
                $_where = array(
                    'list_id' => $list_id,
                    'subscriber_id' => $subscriber['subscriber_id']
                );
                $subscriber_list_data = db_get_row("SELECT * FROM ?:user_mailing_lists WHERE ?w", $_where);
                $subscriber['mailing_lists'][$list_id] = array_merge($subscriber['mailing_lists'][$list_id], $subscriber_list_data);

                $subscriber['lang_code'] = $subscriber['mailing_lists'][$list_id]['lang_code'];
            }

            unset($subscriber['list_ids']);
        }
    }

    $mailing_lists = db_get_hash_array("SELECT m.list_id, d.object, ?:newsletters.newsletter_id as register_autoresponder FROM ?:mailing_lists AS m INNER JOIN ?:common_descriptions AS d ON m.list_id=d.object_id LEFT JOIN ?:newsletters ON m.register_autoresponder = ?:newsletters.newsletter_id AND ?:newsletters.status = 'A' WHERE d.object_holder='mailing_lists' AND d.lang_code = ?s", 'list_id', DESCR_SL);

    Registry::get('view')->assign('mailing_lists', $mailing_lists);
    Registry::get('view')->assign('subscribers', $subscribers);
    Registry::get('view')->assign('search', $search);

    fn_newsletters_generate_sections('subscribers');

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['subscriber_id'])) {
        fn_delete_subscribers((array) $_REQUEST['subscriber_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "subscribers.manage");
}

function fn_get_subscribers($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    // Init filter
    $params = LastView::instance()->update('subscribers', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        '?:subscribers.subscriber_id',
        '?:subscribers.email',
        '?:subscribers.timestamp',
        '?:subscribers.subscriber_id',
        "GROUP_CONCAT(?:user_mailing_lists.list_id) as list_ids",
    );

    // Define sort fields
    $sortings = array (
        'email' => '?:subscribers.email',
        'timestamp' => '?:subscribers.timestamp'
    );

    $condition = '';

    $group_by = "?:subscribers.subscriber_id";

    $join = db_quote(" LEFT JOIN ?:user_mailing_lists ON ?:user_mailing_lists.subscriber_id = ?:subscribers.subscriber_id");

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $condition .= db_quote(" AND ?:subscribers.email LIKE ?l", "%".trim($params['email'])."%");
     }

    if (!empty($params['list_id'])) {
        $condition .= db_quote(" AND ?:user_mailing_lists.list_id = ?i", $params['list_id']);
    }

    if (!empty($params['confirmed'])) {
        $condition .= db_quote(" AND ?:user_mailing_lists.confirmed = ?i", ($params['confirmed'] == 'Y'));
    }

    if (!empty($params['language'])) {
        $condition .= db_quote(" AND ?:user_mailing_lists.lang_code = ?s", $params['language']);
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:subscribers.timestamp >= ?i AND ?:subscribers.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:subscribers.subscriber_id)) FROM ?:subscribers $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $subscribers = db_get_array('SELECT ' . implode(', ', $fields) . " FROM ?:subscribers $join WHERE 1 $condition GROUP BY $group_by $sorting $limit");

    return array($subscribers, $params);
}

function fn_update_subscriber($subscriber_data, $subscriber_id = 0)
{
    $invalid_emails = array();

    if (empty($subscriber_data['list_ids'])) {
        $subscriber_data['list_ids'] = array();
    }
    if (empty($subscriber_data['mailing_lists'])) {
        $subscriber_data['mailing_lists'] = array();
    }

    $subscriber_data['list_ids'] = array_filter($subscriber_data['list_ids']);
    $subscriber_data['mailing_lists'] = array_filter($subscriber_data['mailing_lists']);

    if (empty($subscriber_id)) {
        if (!empty($subscriber_data['email'])) {
            if (db_get_field("SELECT email FROM ?:subscribers WHERE email = ?s", $subscriber_data['email']) == '') {
                if (fn_validate_email($subscriber_data['email']) == false) {
                    $invalid_emails[] = $subscriber_data['email'];
                } else {
                    $subscriber_data['timestamp'] = TIME;
                    $subscriber_id = db_query("INSERT INTO ?:subscribers ?e", $subscriber_data);
                }
            } else {
                fn_set_notification('W', __('warning'), __('ne_warning_subscr_email_exists', array(
                    '[email]' => $subscriber_data['email']
                )));
            }
        }
    } else {
        db_query("UPDATE ?:subscribers SET ?u WHERE subscriber_id = ?i", $subscriber_data, $subscriber_id);
    }

    fn_update_subscriptions($subscriber_id, $subscriber_data['list_ids'], isset($subscriber_data['confirmed']) ? $subscriber_data['confirmed'] : $subscriber_data['mailing_lists'], fn_get_notification_rules($subscriber_data), $subscriber_data['lang_code']);

    if (!empty($invalid_emails)) {
        fn_set_notification('E', __('error'), __('error_invalid_emails', array(
            '[emails]' => implode(', ', $invalid_emails)
        )));
    }

    return $subscriber_id;
}

/** /Body **/
