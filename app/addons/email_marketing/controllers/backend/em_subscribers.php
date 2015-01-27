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

use Tygh\EmailSync;
use Tygh\Registry;
use Tygh\Settings;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD']	== 'POST') {

    $suffix = '.manage';

    if ($mode == 'update') {
        $subscriber_id = fn_em_update_subscriber($_REQUEST['subscriber_data'], $_REQUEST['subscriber_id']);
    }

    if ($mode == 'm_delete') {
        fn_em_delete_subscribers($_REQUEST['subscriber_ids']);
    }

    if ($mode == 'export_range') {
        if (!empty($_REQUEST['subscriber_ids'])) {
            if (empty($_SESSION['export_ranges'])) {
                $_SESSION['export_ranges'] = array();
            }

            if (empty($_SESSION['export_ranges']['subscribers'])) {
                $_SESSION['export_ranges']['subscribers'] = array('pattern_id' => 'em_subscribers');
            }

            $_SESSION['export_ranges']['subscribers']['data'] = array('subscriber_id' => $_REQUEST['subscriber_ids']);

            unset($_REQUEST['redirect_url']);

            return array(CONTROLLER_STATUS_REDIRECT, "exim.export?section=subscribers&pattern_id=em_subscribers");
        }
    }    

    return array(CONTROLLER_STATUS_OK, 'em_subscribers' . $suffix);
}

if ($mode == 'manage') {

    list($subscribers, $search) = fn_em_get_subscribers($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));

    // Get settings
    $em_settings = array();
    foreach (fn_em_get_managable_settings() as $option) {
         $option_data = Settings::instance()->getSettingDataByName($option);
         $em_settings[$option_data['object_id']] = $option_data;
    }
    
    Registry::get('view')->assign('em_settings', $em_settings);
    Registry::get('view')->assign('em_support', EmailSync::instance()->supports());

    Registry::get('view')->assign('subscribers', $subscribers);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'delete') {
    if (!empty($_REQUEST['subscriber_id'])) {
        fn_em_delete_subscribers((array) $_REQUEST['subscriber_id']);
    }

    return array(CONTROLLER_STATUS_OK, "em_subscribers.manage");

} elseif ($mode == 'sync') {

    EmailSync::instance()->sync();

    return array(CONTROLLER_STATUS_OK, "em_subscribers.manage");

} elseif ($mode == 'import') {
    EmailSync::instance()->import();

    return array(CONTROLLER_STATUS_OK, "em_subscribers.manage");    
}

function fn_em_get_managable_settings()
{
    return array('em_show_on_checkout', 'em_checkout_enabled', 'em_double_opt_in', 'em_welcome_letter');
}
