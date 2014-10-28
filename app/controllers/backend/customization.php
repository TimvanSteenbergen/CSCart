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

if ($mode == 'update_mode') {
    if (!empty($_REQUEST['status']) && !empty($_REQUEST['type'])) {
        $return_url = !empty($_REQUEST['return_url']) ? $_REQUEST['return_url'] : '';

        if (fn_allowed_for('ULTIMATE') && Registry::ifGet('runtime.company_id', 0) == 0) {
            fn_set_notification('W', __('warning'), __('text_select_vendor'));

            return array(CONTROLLER_STATUS_REDIRECT, $return_url);
        }

        $c_mode = $_REQUEST['type'];
        $status = $_REQUEST['status'];
        $avail_modes = array_keys(fn_get_customization_modes());

        if (!in_array($c_mode, $avail_modes)) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        $changed_modes = array();

        if ($status == 'enable') {
            // disable all other modes
            $changed_modes = array_fill_keys($avail_modes, 'disable');
        }

        $changed_modes[$c_mode] = $status;

        fn_update_customization_mode($changed_modes);

        if ($status == 'enable') {

            // redirect to frontend after enabling mode
            if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id') && !Registry::get('runtime.simple_ultimate')) {
                $extra_url = '&switch_company_id=' . Registry::get('runtime.company_id');
            } else {
                $extra_url = '';
            }

            if (!empty($_REQUEST['s_layout'])) {
                $extra_url .= '&redirect_url=' . urlencode('index.index?s_layout=' . $_REQUEST['s_layout']);
            }

            $url = 'profiles.act_as_user?user_id=' . $auth['user_id'] . '&area=C' . $extra_url;

            return array(CONTROLLER_STATUS_REDIRECT, $url);
        }

        return array(CONTROLLER_STATUS_REDIRECT, $return_url);
    }
}
