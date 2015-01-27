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
use Tygh\EmailSync;

function fn_settings_variants_addons_email_marketing_em_mailchimp_list()
{
    if (Registry::get('addons.email_marketing.status') == 'A' && Registry::get('addons.email_marketing.em_mailchimp_api_key')) {
        $list = array('' => __('none'));
        $list = array_merge($list, EmailSync::instance('mailchimp')->getLists());

    } else {
        $list = array(
            '' => __('email_marketing.enter_api_key_and_save')
        );
    }

    return $list;
}

function fn_settings_actions_addons_email_marketing_em_mailchimp_list(&$new_value, $old_value)
{
    if (Registry::get('addons.email_marketing.status') != 'A') {
        return false;
    }
    // resubscribe web hooks
    if ($new_value != $old_value) {
        $result = true;
        if (!empty($old_value)) {
            $result = EmailSync::instance('mailchimp')->unsubscribeCallback($old_value);
        }

        if (!empty($new_value) && $result) {
            $result = EmailSync::instance('mailchimp')->subscribeCallback($new_value);
        }

        if ($result == false) {
            $new_value = $old_value;
        }
    }
}

function fn_settings_variants_addons_email_marketing_em_madmimi_list()
{
    if (Registry::get('addons.email_marketing.status') == 'A' && Registry::get('addons.email_marketing.em_madmimi_api_key')) {
        $list = array('' => __('none'));
        $list = array_merge($list, EmailSync::instance('madmimi')->getLists());
    } else {
        $list = array(
            '' => __('email_marketing.enter_api_key_and_save')
        );
    }

    return $list;
}
