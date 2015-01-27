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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($mode == 'send') {
        if (fn_image_verification('use_for_email_share', $_REQUEST) == false) {
            fn_save_post_data('send_data');

            return array(CONTROLLER_STATUS_REDIRECT);
        }

        if (!empty($_REQUEST['send_data']['to_email'])) {

            $lnk = fn_url(Registry::get('config.current_url'));
            $redirect_url = fn_query_remove($_REQUEST['redirect_url'], 'selected_section');
            $index_script = Registry::get('config.customer_index');

            if (strpos($lnk, $index_script) !== false) {
                $redirect_url = str_replace($index_script, '', $redirect_url);
            }
            $lnk .= $redirect_url;

            $from = array(
                'email' => !empty($_REQUEST['send_data']['from_email']) ? $_REQUEST['send_data']['from_email'] : Registry::get('settings.Company.company_users_department'),
                'name' => !empty($_REQUEST['send_data']['from_name']) ? $_REQUEST['send_data']['from_name'] : Registry::get('settings.Company.company_name'),
            );

            $mail_sent = Mailer::sendMail(array(
                'to' => $_REQUEST['send_data']['to_email'],
                'from' => $from,
                'data' => array(
                    'link' => $lnk,
                    'send_data' => $_REQUEST['send_data']
                ),
                'tpl' => 'addons/social_buttons/mail.tpl',
            ), 'C');

            if ($mail_sent == true) {
                fn_set_notification('N', __('notice'), __('text_email_sent'));
            }
        } else {
            fn_set_notification('E', __('error'), __('error_no_recipient_address'));
        }

        return array(CONTROLLER_STATUS_REDIRECT);
    }
}
