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

/** Body **/

$return_id = isset($_REQUEST['return_id']) ? intval($_REQUEST['return_id']) : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    //
    // Adding new RMA properties
    //
    $suffix = '';
    if ($mode == 'add_properties') {

        if (!empty($_REQUEST['add_property_data'])) {
            foreach ($_REQUEST['add_property_data'] as $key => $value) {
                if (!empty($value['property'])) {
                    $value['type'] = $_REQUEST['property_type'];
                    $property_id = db_query("INSERT INTO ?:rma_properties ?e", $value);

                    $value['property_id'] = $property_id;
                    foreach (fn_get_translation_languages() as $value['lang_code'] => $v) {
                        db_query("INSERT INTO ?:rma_property_descriptions ?e", $value);
                    }
                }
            }
        }
        $suffix = ".properties?property_type=$_REQUEST[property_type]";

    }

    //
    // Updating RMA properties
    //
    if ($mode == 'update_properties') {

        foreach ($_REQUEST['property_data'] as $property_id => $value) {
            if (!empty($value['property'])) {

                db_query("UPDATE ?:rma_properties SET ?u WHERE property_id = ?i", $value, $property_id);
                db_query("UPDATE ?:rma_property_descriptions SET ?u WHERE property_id = ?i AND lang_code = ?s", $value, $property_id, DESCR_SL);
            }
        }

        $suffix = ".properties?property_type=$_REQUEST[property_type]";

    }

    //
    // Deleting selected RMA properties
    //
    if ($mode == 'm_delete_properties') {

        foreach ($_REQUEST['property_ids'] as $property_id) {
            fn_rma_delete_property($property_id);
        }

        $suffix = ".properties?property_type=$_REQUEST[property_type]";

    }

    //
    // Updating return details
    //
    if ($mode == 'update_details') {

        $change_return_status = $_REQUEST['change_return_status'];

        $_data = array();
        if (isset($_REQUEST['comment'])) {
            $_data['comment'] = $_REQUEST['comment'];
        }

        $is_refund = fn_is_refund_action($change_return_status['action']);
        $confirmed = isset($_REQUEST['confirmed']) ? $_REQUEST['confirmed'] : '';
        $st_inv = fn_get_statuses(STATUSES_RETURN);
        $show_confirmation = false;
        if ((
                ($change_return_status['recalculate_order'] == 'M' && $is_refund == 'Y') ||
                $change_return_status['recalculate_order'] == 'R'
            ) &&
                $change_return_status['status_to'] != $change_return_status['status_from'] &&
                !($st_inv[$change_return_status['status_from']]['params']['inventory'] == 'D' && $change_return_status['status_to'] == RMA_DEFAULT_STATUS) &&
                !($st_inv[$change_return_status['status_to']]['params']['inventory'] == 'D' && $change_return_status['status_from'] == RMA_DEFAULT_STATUS)
            ) {
            $show_confirmation = true;
        }

        $suffix = ".details?return_id=$change_return_status[return_id]";

        if ($show_confirmation == true) {

            if ($confirmed == 'Y') {
                fn_rma_recalculate_order($change_return_status['order_id'], $change_return_status['recalculate_order'], $change_return_status['return_id'], $is_refund, $change_return_status);

                $_data['status'] = $change_return_status['status_to'];
            } else {
                $change_return_status['inventory_to'] = $st_inv[$change_return_status['status_to']]['params']['inventory'];
                $change_return_status['inventory_from'] = $st_inv[$change_return_status['status_from']]['params']['inventory'];

                $_SESSION['change_return_status'] = $change_return_status;

                $suffix = ".confirmation";
            }
        } else {
            $_data['status'] = $change_return_status['status_to'];
        }

        if (!empty($_data)) {
            db_query("UPDATE ?:rma_returns SET ?u WHERE return_id = ?i", $_data, $change_return_status['return_id']);
        }

        if (($show_confirmation == false || ($show_confirmation == true && $confirmed == 'Y')) && $change_return_status['status_from'] != $change_return_status['status_to']) {
            //Update order details
            $order_items = db_get_hash_single_array("SELECT item_id, extra FROM ?:order_details WHERE ?:order_details.order_id = ?i", array('item_id', 'extra'), $change_return_status['order_id']);

            foreach ($order_items as $item_id => $extra) {
                $extra = @unserialize($extra);
                if (isset($extra['returns'][$change_return_status['return_id']])) {
                    $extra['returns'][$change_return_status['return_id']]['status'] = $change_return_status['status_to'];
                    db_query('UPDATE ?:order_details SET ?u WHERE item_id = ?i AND order_id = ?i', array('extra' => serialize($extra)), $item_id, $change_return_status['order_id']);
                }
            }

            //Send mail
            $return_info = fn_get_return_info($change_return_status['return_id']);
            $order_info = fn_get_order_info($change_return_status['order_id']);
            fn_send_return_mail($return_info, $order_info, fn_get_notification_rules($change_return_status));
        }

        return array(CONTROLLER_STATUS_OK, "rma$suffix");
    }

    if ($mode == 'bulk_slip_print' && !empty($_REQUEST['return_ids'])) {

        fn_rma_print_packing_slips($_REQUEST['return_ids'], $auth);
        exit;
    }

    if ($mode == 'm_delete_returns' && !empty($_REQUEST['return_ids'])) {

        foreach ($_REQUEST['return_ids'] as $return_id) {
            fn_delete_return($return_id);
        }

        $suffix = ".returns";
    }

    if ($mode == 'decline_products') {

        if (!empty($_REQUEST['accepted'])) {
            $decline_amount = 0;
            $change_return_status = $_REQUEST['change_return_status'];

            $order_items = db_get_hash_single_array("SELECT item_id, extra FROM ?:order_details WHERE order_id = ?i", array('item_id', 'extra'), $change_return_status['order_id']);

            foreach ((array) $_REQUEST['accepted'] as $item_id => $v) {
                if (isset($v['chosen']) && $v['chosen'] == 'Y') {
                    fn_return_product_routine($change_return_status['return_id'], $item_id, $v, RETURN_PRODUCT_DECLINED);
                    $decline_amount += $v['amount'];
                    $extra = @unserialize($order_items[$item_id]);

                    if (($v['previous_amount'] - $v['amount']) <= 0) {
                        unset($extra['returns'][$change_return_status['return_id']]);
                        if (empty($extra['returns'])) {
                            unset($extra['returns']);
                        }
                    } else {
                        $extra['returns'][$change_return_status['return_id']] = array(
                            'amount' => $extra['returns'][$change_return_status['return_id']]['amount'] - $v['amount'],
                            'status' => $change_return_status['status_from']
                        );
                    }

                    //update order detail data
                    $order_details_data = array('extra' => serialize($extra));
                    db_query("UPDATE ?:order_details SET ?u WHERE item_id = ?i AND order_id = ?i", $order_details_data, $item_id, $change_return_status['order_id']);

                    unset($order_details_data);
                }
            }

            db_query('UPDATE ?:rma_returns SET ?u WHERE return_id = ?i', array('total_amount' => $_REQUEST['total_amount'] - $decline_amount), $change_return_status['return_id']);
        }

        $suffix = ".details?return_id=$change_return_status[return_id]";

    }

    if ($mode == 'accept_products') {

        if (!empty($_REQUEST['declined'])) {
            $accept_amount = 0;

            $change_return_status = $_REQUEST['change_return_status'];

            $order_items = db_get_hash_single_array("SELECT item_id, extra FROM ?:order_details WHERE ?:order_details.order_id = ?i", array('item_id', 'extra'), $change_return_status['order_id']);

            foreach ((array) $_REQUEST['declined'] as $item_id => $v) {
                if (isset($v['chosen']) && $v['chosen'] == 'Y') {
                    fn_return_product_routine($change_return_status['return_id'], $item_id, $v, RETURN_PRODUCT_ACCEPTED);
                    $accept_amount += $v['amount'];
                    $extra = @unserialize($order_items[$item_id]);

                    if (!isset($extra['returns'])) {
                        $extra['returns'] = array();
                    }

                    $extra['returns'][$change_return_status['return_id']] = array(
                        'amount' => (isset($extra['returns'][$change_return_status['return_id']]['amount']) ? $extra['returns'][$change_return_status['return_id']]['amount'] : 0) + $v['amount'],
                        'status' => $change_return_status['status_from']
                    );

                    // update order detail data
                    $order_details_data = array('extra' => serialize($extra));
                    db_query("UPDATE ?:order_details SET ?u WHERE item_id = ?i AND order_id = ?i", $order_details_data, $item_id, $change_return_status['order_id']);

                    unset($order_details_data);
                }
            }

            db_query('UPDATE ?:rma_returns SET ?u WHERE return_id = ?i', array('total_amount' => $_REQUEST['total_amount'] + $accept_amount), $change_return_status['return_id']);
        }

        $suffix = ".details?return_id=$change_return_status[return_id]";

    }

    return array(CONTROLLER_STATUS_OK, "rma$suffix");
}

if ($mode == 'properties') {

    $property_type = !empty($_REQUEST['property_type']) ? $_REQUEST['property_type'] : RMA_REASON;

    fn_rma_generate_sections($property_type == RMA_REASON ? 'reasons' : 'actions');

    Registry::get('view')->assign('properties', fn_get_rma_properties($property_type, DESCR_SL));

} elseif ($mode == 'delete' && !empty($_REQUEST['return_id'])) {

    fn_delete_return($_REQUEST['return_id']);

    return array(CONTROLLER_STATUS_REDIRECT, "rma.returns");

} elseif ($mode == 'confirmation') {

    $change_return_status = $_SESSION['change_return_status'];
    unset($_SESSION['change_return_status']);

    if ($change_return_status['recalculate_order'] == 'R') {
        $additional_data = db_get_hash_single_array("SELECT type,data FROM ?:order_data WHERE order_id = ?i", array('type', 'data'), $change_return_status['order_id']);
        $shipping_info = @unserialize($additional_data['L']);

        Registry::get('view')->assign('shipping_info', $shipping_info);
    } else {
        $total = db_get_field("SELECT SUM(amount*price) FROM ?:rma_return_products WHERE return_id = ?i AND type = ?s", $change_return_status['return_id'], RETURN_PRODUCT_ACCEPTED);
        $change_return_status['total'] = ($change_return_status['inventory_to'] =='I' && !($change_return_status['inventory_from'] == 'I' && $change_return_status['status_to'] == RMA_DEFAULT_STATUS)) ? - $total : $total;
    }

    Registry::get('view')->assign('change_return_status', $change_return_status);
    Registry::get('view')->assign('status_descr', fn_get_simple_statuses(STATUSES_RETURN));

} elseif ($mode == 'delete_property') {

    if (!empty($_REQUEST['property_id'])) {
        fn_rma_delete_property($_REQUEST['property_id']);
    }

    return array(CONTROLLER_STATUS_REDIRECT, "rma.properties?property_type=$_REQUEST[property_type]");
}
