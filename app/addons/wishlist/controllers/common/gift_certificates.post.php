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

if (AREA != 'C') { // this script for the frontend only

    return;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $wishlist = & $_SESSION['wishlist'];

    if ($mode == 'wishlist_add') {

        if (!empty($_REQUEST['gift_cert_data']) && is_array($_REQUEST['gift_cert_data'])) {

            $gift_cert_data = $_REQUEST['gift_cert_data'];

            // wishlist is empty, create it
            if (empty($wishlist)) {
                $wishlist = array();
            }

            // Gift certificates is empty, create it
            if (empty($wishlist['gift_certificates'])) {
                $wishlist['gift_certificates'] = array();
            }

            list($gift_cert_wishlist_id, $gift_cert) = fn_add_gift_certificate_to_wishlist($wishlist, $gift_cert_data);

            fn_save_cart_content($wishlist, $auth['user_id'], 'W');
            if (defined('AJAX_REQUEST')) {
                $gift_cert_amount = floatval($gift_cert['amount']);
                if (empty($gift_cert_amount) && empty($gift_cert['products'])) {
                    fn_delete_wishlist_gift_certificate($wishlist, $gift_cert_wishlist_id);
                    fn_set_notification('N', __('notice'), __('text_failed_gift_certificate_addition'));
                    exit;
                }

                $gift_cert['gift_cert_id'] = $gift_cert_wishlist_id;
                Registry::get('view')->assign('gift_cert', $gift_cert);
                $msg = Registry::get('view')->fetch('addons/wishlist/views/wishlist/components/product_notification.tpl');
                fn_set_notification('I', __('text_gift_cert_added_to_wishlist'), $msg, 'I');
            }
        }

        return array(CONTROLLER_STATUS_REDIRECT, "wishlist.view");
    }

    if ($mode == 'update') {

        if (!empty($_REQUEST['gift_cert_data']) && !empty($_REQUEST['gift_cert_id']) && $_REQUEST['type'] == 'W') {
            fn_delete_wishlist_gift_certificate($wishlist, $_REQUEST['gift_cert_id']);

            list($gift_cert_id, $gift_cert) = fn_add_gift_certificate_to_wishlist($wishlist, $_REQUEST['gift_cert_data']);

            if (!empty($gift_cert_id)) {
                $wishlist['gift_certificates'][$gift_cert_id] = $gift_cert;
            }

            fn_save_cart_content($wishlist, $auth['user_id'], $_REQUEST['type']);

            return array(CONTROLLER_STATUS_REDIRECT, "wishlist.view");
        }
    }
}

if ($mode == 'update') {
    if (!empty($_REQUEST['gift_cert_wishlist_id'])) {
        $gift_cert_data = fn_get_gift_certificate_info($_REQUEST['gift_cert_wishlist_id'], 'W');

        if (!empty($gift_cert_data['extra']['exclude_from_calculate'])) {
            return array(CONTROLLER_STATUS_NO_PAGE);
        }

        Registry::get('view')->assign('gift_cert_data', $gift_cert_data);
        Registry::get('view')->assign('gift_cert_id', $_REQUEST['gift_cert_wishlist_id']);
        Registry::get('view')->assign('type', 'W');
    }

} elseif ($mode == 'wishlist_delete') {

    if (isset($_REQUEST['gift_cert_wishlist_id'])) {
        fn_delete_cart_gift_certificate($_SESSION['wishlist'], $_REQUEST['gift_cert_wishlist_id']);

    }

    return array(CONTROLLER_STATUS_REDIRECT, "wishlist.view");
}
