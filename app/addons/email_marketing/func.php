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
use Tygh\Mailer;
use Tygh\Navigation\LastView;
use Tygh\BlockManager\Block;
use Tygh\EmailSync;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }


function fn_em_update_subscriber($subscriber_data, $subscriber_id = 0, $sync = true, $lang_code = CART_LANGUAGE)
{
    $invalid_emails = array();

    if (empty($subscriber_id)) {
        if (!empty($subscriber_data['email'])) {
            if (db_get_field("SELECT email FROM ?:em_subscribers WHERE email = ?s", $subscriber_data['email']) == '') {
                if (fn_validate_email($subscriber_data['email']) == false) {
                    $invalid_emails[] = $subscriber_data['email'];
                } else {

                    if (empty($subscriber_data['timestamp'])) {
                        $subscriber_data['timestamp'] = time();
                    }

                    if (empty($subscriber_data['ip_address'])) {
                        $ip = fn_get_ip();
                        $subscriber_data['ip_address'] = $ip['host'];
                    }

                    if (empty($subscriber_data['unsubscribe_key'])) {
                        $subscriber_data['unsubscribe_key'] = md5(uniqid());
                    }

                    $subscriber_data['lang_code'] = $lang_code;
                    $subscriber_id = db_query("INSERT INTO ?:em_subscribers ?e", $subscriber_data);
                }
            } else {
                fn_set_notification('W', __('warning'), __('email_marketing.warning_email_exists', array(
                    '[email]' => $subscriber_data['email']
                )));
            }
        }
    } else {
        db_query("UPDATE ?:em_subscribers SET ?u WHERE subscriber_id = ?i", $subscriber_data, $subscriber_id);
    }

    if (!empty($invalid_emails)) {
        fn_set_notification('E', __('error'), __('error_invalid_emails', array(
            '[emails]' => implode(', ', $invalid_emails)
        )));
    } elseif ($sync) {
        $subscriber_data = fn_em_get_subscriber_data('', $subscriber_id);
        $subscribed = EmailSync::instance()->subscribe(array(
            'email' => $subscriber_data['email'],
            'timestamp' => $subscriber_data['timestamp'],
            'lang_code' => $subscriber_data['lang_code'],
            'ip_address' => $subscriber_data['ip_address'],
            'name' => $subscriber_data['name']
        ));

        if ($subscribed == false) {
            fn_em_delete_subscribers(array($subscriber_id), false);
            $subscriber_id = false;
        }
    }

    return $subscriber_id;
}

function fn_em_confirm_subscription($email)
{
    $subscriber_data = fn_em_get_subscriber_data($email);

    $subscribed = EmailSync::instance()->subscribe(array(
        'email' => $email,
        'timestamp' => $subscriber_data['timestamp'],
        'lang_code' => $subscriber_data['lang_code'],
        'ip_address' => $subscriber_data['ip_address'],
        'name' => $subscriber_data['name']
    ));

    if ($subscribed) {
        $unsubscribe_key = md5(uniqid());
        db_query("UPDATE ?:em_subscribers SET ?u WHERE email = ?s", array(
            'status' => 'A',
            'unsubscribe_key' => $unsubscribe_key
        ), $email);

        if (Registry::get('addons.email_marketing.em_welcome_letter') == 'Y') {
            Mailer::sendMail(array(
                'to' => $email,
                'from' => 'default_company_newsletter_email',
                'data' => array(
                    'url' => fn_url('em_subscribers.unsubscribe?unsubscribe_key=' . $unsubscribe_key)
                ),
                'tpl' => Registry::get('addons.email_marketing.em_double_opt_in') == 'Y' ? 'addons/email_marketing/welcome_2optin.tpl' : 'addons/email_marketing/welcome.tpl'
            ));
        }
    }

    return $subscribed;
}

function fn_em_unsubscribe($unsubscribe_key)
{
    $unsubscribed = false;
    $email = db_get_field("SELECT email FROM ?:em_subscribers WHERE unsubscribe_key = ?s", $unsubscribe_key);
    if (!empty($email)) {
        if (EmailSync::instance()->unsubscribe($email)) {
            fn_em_delete_subscribers_by_email(array($email));
            $unsubscribed = true;
        }
    }

    return $unsubscribed;
}

function fn_em_subscribe_email($email, $data = array())
{
    $subscriber_id = fn_em_update_subscriber(array(
        'email' => $email,
        'name' => !empty($data['name']) ? $data['name'] : '',
        'status' => 'P'
    ), 0, false);

    if (!empty($subscriber_id)) {

        if (Registry::get('addons.email_marketing.em_double_opt_in') == 'Y') {

            Registry::get('view')->assign('notification_msg', __('email_marketing.text_subscription_pending'));
            $msg = Registry::get('view')->fetch('addons/email_marketing/common/notification.tpl');
            fn_set_notification('I', __('email_marketing.subscription_pending'), $msg);

            Mailer::sendMail(array(
                'to' => $email,
                'from' => 'default_company_newsletter_email',
                'data' => array(
                    'url' => fn_url('em_subscribers.confirm?ekey=' . fn_generate_ekey($email, 'E', SECONDS_IN_DAY)),
                ),
                'tpl' => 'addons/email_marketing/confirmation.tpl'
            ));
        } else {
            if (fn_em_confirm_subscription($email)) {
                Registry::get('view')->assign('notification_msg', __('email_marketing.text_subscription_confirmed'));
                $msg = Registry::get('view')->fetch('addons/email_marketing/common/notification.tpl');
                fn_set_notification('I', __('email_marketing.subscription_confirmed'), $msg);
            } else {
                fn_em_delete_subscribers_by_email(array($email));
            }
        }
    }
}

function fn_em_get_subscriber_data($email = '', $subscriber_id = 0)
{
    if (!empty($subscriber_id)) {
        $condition = db_quote("subscriber_id = ?i", $subscriber_id);
    } else {
        $condition = db_quote("email = ?s", $email);
    }

    return db_get_row("SELECT *  FROM ?:em_subscribers WHERE ?p", $condition);
}

function fn_em_delete_subscribers($subscriber_ids, $sync = true)
{
    if (!empty($subscriber_ids)) {
        $emails = db_get_fields("SELECT email FROM ?:em_subscribers WHERE subscriber_id IN (?n)", $subscriber_ids);

        if ($sync) {
            EmailSync::instance()->batchUnsubscribe($emails);
        }

        return db_query("DELETE FROM ?:em_subscribers WHERE subscriber_id IN (?n)", $subscriber_ids);
    }

    return false;
}

function fn_em_delete_subscribers_by_email($emails)
{
    if (!empty($emails)) {
        return db_query("DELETE FROM ?:em_subscribers WHERE email IN (?a)", $emails);
    }

    return false;
}


function fn_em_install()
{
    $token = md5(uniqid());

    Settings::instance()->updateValue('em_token', $token, 'email_marketing');
    Settings::instance()->updateValue('em_lastsync', time(), 'email_marketing');
}

function fn_em_is_email_subscribed($email)
{
    $subscriber_id = db_get_field("SELECT subscriber_id FROM ?:em_subscribers WHERE email = ?s", $email);

    return !empty($subscriber_id);
}

function fn_em_get_subscriber_name()
{
    $name = '';

    if (!empty($_SESSION['cart']['user_data']['firstname'])) {
        $name = $_SESSION['cart']['user_data']['firstname'];
    } elseif (!empty($_SESSION['auth']['user_id'])) {
        $user_info = fn_get_user_info($_SESSION['auth']['user_id'], false);
        $name = $user_info['firstname'];
    }

    return $name;
}

function fn_em_exim_sync($primary_object_ids, $import_data, $auth) 
{
    foreach ($import_data as $data) {
        $data = array_pop($data); // remove index with language code
        if (empty($data['timestamp'])) {
            $data['timestamp'] = fn_timestamp_to_date(time());
        }

        EmailSync::instance()->batchAdd(array(
            'email' => $data['email'],
            'name' => $data['name'],
            'timestamp' => fn_date_to_timestamp($data['timestamp']),
            'lang_code' => !empty($data['lang_code']) ? $data['lang_code'] : CART_LANGUAGE,
            'ip_address' => !empty($data['ip_address']) ? $data['ip_address'] : '',
        ));
    }

    EmailSync::instance()->batchSubscribe();
}

function fn_em_get_subscribers($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    // Init filter
    $params = LastView::instance()->update('em_subscribers', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        '?:em_subscribers.subscriber_id',
        '?:em_subscribers.email',
        '?:em_subscribers.timestamp',
        '?:em_subscribers.name',
        '?:em_subscribers.unsubscribe_key',
        '?:em_subscribers.status',
    );

    // Define sort fields
    $sortings = array (
        'email' => '?:em_subscribers.email',
        'name' => '?:em_subscribers.name',
        'status' => '?:em_subscribers.status',
        'timestamp' => '?:em_subscribers.timestamp'
    );

    $condition = '';
    $group_by = '';
    $join = '';

    if (!empty($params['subscriber_id'])) {
        $condition .= db_quote(" AND ?:em_subscribers.subscriber_id = ?i", $params['subscriber_id']);
    }

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $condition .= db_quote(" AND ?:em_subscribers.email LIKE ?l", "%".trim($params['email'])."%");
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:em_subscribers.status = ?s", $params['status']);
    }

    if (!empty($params['name'])) {
        $condition .= db_quote(" AND ?:em_subscribers.name LIKE ?l", "%" . $params['name'] . "%");
    }

    if (!empty($params['lang_code'])) {
        $condition .= db_quote(" AND ?:em_subscribers.lang_code = ?s", $params['lang_code']);
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:em_subscribers.timestamp >= ?i AND ?:em_subscribers.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(?:em_subscribers.subscriber_id) FROM ?:em_subscribers $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $subscribers = db_get_array('SELECT ' . implode(', ', $fields) . " FROM ?:em_subscribers $join WHERE 1 $condition $group_by $sorting $limit");

    return array($subscribers, $params);
}
