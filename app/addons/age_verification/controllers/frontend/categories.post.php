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

if ($mode == 'view' && !empty($_REQUEST['category_id'])) {
    list ($result, $category_id) = fn_age_verification_category_check($_REQUEST['category_id']);

    if ($category_id) {
        $message = db_get_field("SELECT age_warning_message FROM ?:category_descriptions WHERE category_id = ?i AND lang_code = ?s", $category_id, CART_LANGUAGE);

        Registry::get('view')->assign('age_warning_message', $message);
    }

    if ($result == 'form') {
        fn_add_breadcrumb(__('age_verification'));
        Registry::get('view')->assign('content_tpl', 'addons/age_verification/views/categories/components/form.tpl');

        return array (CONTROLLER_STATUS_OK);
    } elseif ($result == 'deny') {
        fn_set_notification('E', __('error'), __('access_denied'));
        Registry::get('view')->assign('content_tpl', 'addons/age_verification/views/categories/components/deny.tpl');

        return array (CONTROLLER_STATUS_OK);
    }
}
