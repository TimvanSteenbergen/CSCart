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

if (!defined('BOOTSTRAP')) {
    die('Access denied');
}

use Tygh\Registry;
use Twigmo\Upgrade\TwigmoUpgrade;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'check') {
        if (!TwigmoUpgrade::checkForUpgrade(false)) {
            fn_set_notification('N', __('notice'), __('text_no_upgrades_available'));
        } else {
            $view = Registry::get('view');
            $view->assign('next_version_info', TwigmoUpgrade::getNextVersionInfo());
            $view->display('addons/twigmo/settings/upgrade.tpl');
        }
        exit;
    }
}
