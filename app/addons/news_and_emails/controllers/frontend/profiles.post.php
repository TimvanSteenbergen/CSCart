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

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    if ($mode == 'update') {
        $subscriber = db_get_row("SELECT * FROM ?:subscribers WHERE email = ?s", $_REQUEST['user_data']['email']);
        if (!empty($_REQUEST['mailing_lists']) && !fn_is_empty($_REQUEST['mailing_lists'])) {
            if (empty($subscriber)) {
                $_data = array(
                    'email' => $_REQUEST['user_data']['email'],
                    'timestamp' => TIME,
                );

                $subscriber_id = db_query("INSERT INTO ?:subscribers ?e", $_data);
            } else {
                $subscriber_id = $subscriber['subscriber_id'];
            }

            fn_update_subscriptions($subscriber_id, $_REQUEST['mailing_lists'], NULL, fn_get_notification_rules(true));
        } else {
            if (!empty($subscriber)) {
                fn_delete_subscribers($subscriber['subscriber_id']);
            }
        }
    }

    return;
}

if ($mode == 'add' || $mode == 'update') {
    list($page_mailing_lists) = fn_get_mailing_lists();
    Registry::get('view')->assign('page_mailing_lists', $page_mailing_lists);
}

if ($mode == 'update') {
    $email = db_get_field("SELECT email FROM ?:users WHERE user_id = ?i", $_SESSION['auth']['user_id']);
    $mailing_lists = db_get_hash_array("SELECT * FROM ?:subscribers INNER JOIN ?:user_mailing_lists ON ?:subscribers.subscriber_id = ?:user_mailing_lists.subscriber_id WHERE ?:subscribers.email = ?s", 'list_id', $email);
    Registry::get('view')->assign('user_mailing_lists', $mailing_lists);
}
