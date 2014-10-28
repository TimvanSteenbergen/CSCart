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

if ( !defined('AREA') ) { die('Access denied');    }

use Twigmo\Twigmo;
use Twigmo\Upgrade\TwigmoUpgrade;
use Twigmo\Core\Functions\Image\TwigmoImage;
use Tygh\Registry;
use Twigmo\Core\TwigmoSettings;
use Twigmo\Core\TwigmoConnector;

if (!empty($_REQUEST['addon']) && $_REQUEST['addon'] == 'twigmo' && $mode != 'uninstall') {
    $twigmo_requirements_errors = Twigmo::checkRequirements();
    if (!empty($twigmo_requirements_errors)) {
        foreach ($twigmo_requirements_errors as $error) {
            fn_set_notification('W', __('notice'),  $error);
        }
        return;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'tw_connect') {
        $tw_register = empty($_REQUEST['tw_register']) ? array() : $_REQUEST['tw_register'];
        $connector = new TwigmoConnector();
        $user_data = array(
            'email' => empty($tw_register['email']) ? '' : $tw_register['email'],
            'password' => empty($tw_register['password']) ? '' : $tw_register['password'],
            'user_id' => $auth['user_id']
        );
        $stores = empty($tw_register['stores']) ? array() : $tw_register['stores'];
        $is_connected = $connector->connect($stores, $user_data);

        $connector->displayServiceNotifications(true);
        if ($is_connected) {
            fn_set_notification('N', __('notice'), __('twgadmin_text_store_connected'));
        }
        return array(CONTROLLER_STATUS_REDIRECT, 'addons.update?addon=twigmo');
    }

    if ($mode == 'tw_disconnect') {
        $stores = empty($_REQUEST['disconnect_stores']) ? array() : $_REQUEST['disconnect_stores'];
        TwigmoConnector::disconnect($stores, $_REQUEST['disconnect_admin'] == 'Y');
        return array(CONTROLLER_STATUS_REDIRECT, 'addons.update?addon=twigmo&disconnect=Y');
    }

    if ($mode == 'tw_svc_auth_cp') {
        $connector = new TwigmoConnector();
        $action = 'cp';
        $connector->authPage($action);
        exit;
    }

    if ($mode == 'tw_svc_auth_te') {
        $connector = new TwigmoConnector();
        $action = 'te';
        $connector->authPage($action);
        exit;
    }
    if ($mode == 'update' && $_REQUEST['addon'] == 'twigmo') {
        if (!empty($_REQUEST['tw_settings'])) {
            $company_id = fn_twg_get_current_company_id();
            TwigmoSettings::set(array('customer_connections' => array($company_id => $_REQUEST['tw_settings'])));
        }
        return array(CONTROLLER_STATUS_REDIRECT, 'addons.update?addon=twigmo');
    }
} elseif ($mode == 'update') {
    if ($_REQUEST['addon'] == 'twigmo') {
        if (!empty($_REQUEST['selected_section']) and $_REQUEST['selected_section'] == 'twigmo_addon') {
            fn_delete_notification('twigmo_upgrade');
        }
        if (!fn_twg_is_updated()) {
            fn_set_notification('W', __('notice'),  __('twgadmin_reinstall'));
        }
        $company_id = fn_twg_get_current_company_id();
        $view = Registry::get('view');
        $view->assign('default_logo', TwigmoImage::getDefaultLogoUrl($company_id));
        $urls = TwigmoConnector::getMobileScriptsUrls();
        $view->assign('favicon', $urls['favicon']);
        $view->assign('logo_object_id', $company_id * 10 + 1);
        $view->assign('favicon_object_id', $company_id * 10 + 2);
        $tw_register['version'] = TWIGMO_VERSION;
        $view->assign('tw_register', $tw_register);
        $view->assign('next_version_info', TwigmoUpgrade::getNextVersionInfo());
        $view->assign('twg_is_connected', TwigmoConnector::anyFrontendIsConnected());
        $stores = fn_twg_get_stores($auth);
        $view->assign('stores', $stores);
        $view->assign('twg_all_stores_connected', TwigmoConnector::allStoresAreConnected($stores));
        $view->assign('reset_pass_link', TwigmoConnector::getResetPassLink());
        $admin_access_id = TwigmoConnector::getAccessID('A');
        $view->assign('admin_access_id', $admin_access_id);
        $view->assign('is_disconnect_mode', isset($_REQUEST['disconnect']) && $admin_access_id);
        $view->assign('tw_settings', TwigmoSettings::get());
        $view->assign('is_on_saas', fn_twg_is_on_saas());
    }
}
