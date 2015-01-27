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
    if ($mode == 'send_form') {

        $suffix = '';
        if (fn_image_verification('use_for_form_builder', $_REQUEST) == false) {
            fn_save_post_data('form_values');

            return array(CONTROLLER_STATUS_REDIRECT, "pages.view?page_id=$_REQUEST[page_id]");
        }

        if (fn_send_form($_REQUEST['page_id'], empty($_REQUEST['form_values']) ? array() : $_REQUEST['form_values'])) {
            $suffix = '&sent=Y';
        }

        return array(CONTROLLER_STATUS_OK, "pages.view?page_id=$_REQUEST[page_id]" . $suffix);
    }

    return;
}

if ($mode == 'view' && !empty($_REQUEST['page_id'])) {

    $page_is_https = db_get_field("SELECT value FROM ?:form_options WHERE element_type = ?s AND page_id = ?i", FORM_IS_SECURE, $_REQUEST['page_id']);
    // if form is secure, redirect to https connection
    if (!defined('HTTPS') && $page_is_https == 'Y') {
        return array(CONTROLLER_STATUS_REDIRECT, Registry::get('config.https_location') . '/' . Registry::get('config.current_url'));

    } elseif (defined('HTTPS') && Registry::get('settings.Security.keep_https') != 'Y' && $page_is_https != 'Y') {
        return array(CONTROLLER_STATUS_REDIRECT, Registry::get('config.http_location') . '/' . Registry::get('config.current_url'));
    }

    $restored_form_values = fn_restore_post_data('form_values');
    if (!empty($restored_form_values)) {
        Registry::get('view')->assign('form_values', $restored_form_values);
    }

} elseif ($mode == 'sent' && !empty($_REQUEST['page_id'])) {
    $page = fn_get_page_data($_REQUEST['page_id'], CART_LANGUAGE);
    Registry::get('view')->assign('page', $page);
}
