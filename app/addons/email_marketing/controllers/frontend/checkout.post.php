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

    if ($mode == 'customer_info' || $mode == 'update_steps') {

        if (!empty($_SESSION['cart']['user_data']['email'])) {
            $name = fn_em_get_subscriber_name();
            $email = $_SESSION['cart']['user_data']['email'];

            $subscriber_data = fn_em_get_subscriber_data($email);
            if (!empty($subscriber_data) && $subscriber_data['name'] != $name) {
                fn_em_update_subscriber(array(
                    'name' => $name
                ), $subscriber_data['subscriber_id']);
            }
        }
    }

    return;
}

if ($mode == 'checkout' || $mode == 'customer_info') {

    if (Registry::get('addons.email_marketing.em_show_on_checkout') == 'Y' && !empty($_SESSION['cart']['user_data']['email']) && !fn_em_is_email_subscribed($_SESSION['cart']['user_data']['email'])) {
        Registry::get('view')->assign('show_subscription_checkbox', true);
    }
}
