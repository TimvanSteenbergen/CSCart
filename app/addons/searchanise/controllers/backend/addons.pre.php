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

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($mode == 'update' || $mode == 'install' || $mode == 'uninstall') {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_REQUEST['addon'] == 'seo') {
        $show_notice = true;
    }

} elseif ($mode == 'update_status') {
    if ($_REQUEST['id'] == 'seo') {
        $show_notice = true;
    }
}

if (!empty($show_notice)) {
    if (fn_se_is_registered() == true) {
        fn_set_notification('W', __('notice'), __('text_se_seo_settings_notice', array(
            '[link]' => fn_url('addons.update?addon=searchanise')
        )));
    }
}