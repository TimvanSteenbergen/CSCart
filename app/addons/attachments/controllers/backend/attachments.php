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

if (Registry::get('runtime.company_id')) {
    $permission = false;
    if (!empty($_REQUEST['object_type']) && $_REQUEST['object_type'] == 'product' && !empty($_REQUEST['object_id'])) {
        $product_id = db_get_field(
            "SELECT product_id FROM ?:products WHERE product_id = ?i " . fn_get_company_condition('?:products.company_id'),
            $_REQUEST['object_id']
        );
        if (!empty($product_id)) {
            $permission = true;
        }
    }
    if (!$permission) {
        fn_set_notification('W', __('warning'), __('access_denied'));
        if (defined('AJAX_REQUEST')) {
            exit;
        } else {
            return array(CONTROLLER_STATUS_DENIED);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Create/update attachments
    //
    if ($mode == 'update') {
        if (!empty($_REQUEST['attachment_data'])) {
            fn_update_attachments($_REQUEST['attachment_data'], $_REQUEST['attachment_id'], $_REQUEST['object_type'], $_REQUEST['object_id'], 'M', null, DESCR_SL);
        }
    }

    return array(CONTROLLER_STATUS_OK); // redirect should be performed via redirect_url always
}

if ($mode == 'getfile') {
    if (!empty($_REQUEST['attachment_id'])) {
        fn_get_attachment($_REQUEST['attachment_id']);
    }
    exit;

} elseif ($mode == 'delete') {
    fn_delete_attachments(array($_REQUEST['attachment_id']), $_REQUEST['object_type'], $_REQUEST['object_id']);
    $attachments = fn_get_attachments($_REQUEST['object_type'], $_REQUEST['object_id']);
    if (empty($attachments)) {
        Registry::get('view')->display('addons/attachments/views/attachments/manage.tpl');
    }
    exit;

} elseif ($mode == 'update') {
    // Assign attachments files for products
    $attachments = fn_get_attachments($_REQUEST['object_type'], $_REQUEST['object_id'], 'M', DESCR_SL);

    Registry::set('navigation.tabs.attachments', array (
        'title' => __('attachments'),
        'js' => true
    ));

    Registry::get('view')->assign('attachments', $attachments);
}
