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

use Tygh\Pdf;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $suffix = '.manage';

    if ($mode == 'add' && !empty($_REQUEST['shipment_data']) && !fn_allowed_for('ULTIMATE:FREE')) {

        $force_notification = fn_get_notification_rules($_REQUEST);
        fn_update_shipment($_REQUEST['shipment_data'], 0, 0, false, $force_notification);

        $suffix = '.details?order_id=' . $_REQUEST['shipment_data']['order_id'];
    }

    if ($mode == 'packing_slip' && !empty($_REQUEST['shipment_ids'])) {

        fn_print_shipment_packing_slips($_REQUEST['shipment_ids'], Registry::get('runtime.dispatch_extra') == 'pdf');
        exit;

    }

    if ($mode == 'm_delete' && !empty($_REQUEST['shipment_ids'])) {
        fn_delete_shipments($_REQUEST['shipment_ids']);

        if (!empty($_REQUEST['redirect_url'])) {
            return array(CONTROLLER_STATUS_REDIRECT, $_REQUEST['redirect_url']);
        }
    }

    return array(CONTROLLER_STATUS_OK, 'orders' . $suffix);
}

$params = $_REQUEST;

if ($mode == 'details') {
    if (empty($params['order_id']) && empty($params['shipment_id'])) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }

    if (!empty($params['shipment_id'])) {
        $params['order_id'] = db_get_field('SELECT ?:shipment_items.order_id FROM ?:shipment_items WHERE ?:shipment_items.shipment_id = ?i', $params['shipment_id']);
    }

    $shippings = db_get_array("SELECT a.shipping_id, a.min_weight, a.max_weight, a.position, a.status, b.shipping, b.delivery_time, a.usergroup_ids FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id = b.shipping_id AND b.lang_code = ?s WHERE a.status = ?s ORDER BY a.position", DESCR_SL, 'A');

    $order_info = fn_get_order_info($params['order_id'], false, true, true);
    if (empty($order_info)) {
        return array(CONTROLLER_STATUS_NO_PAGE);
    }
    if (!empty($params['shipment_id'])) {
        $params['advanced_info'] = true;

        list($shipment, $search) = fn_get_shipments_info($params);

        if (!empty($shipment)) {
            $shipment = array_pop($shipment);

            foreach ($order_info['products'] as $item_id => $item) {
                if (isset($shipment['products'][$item_id])) {
                    $order_info['products'][$item_id]['amount'] = $shipment['products'][$item_id];
                } else {
                    $order_info['products'][$item_id]['amount'] = 0;
                }
            }
        } else {
            $shipment = array();
        }

        Registry::get('view')->assign('shipment', $shipment);
    }

    Registry::get('view')->assign('shippings', $shippings);
    Registry::get('view')->assign('order_info', $order_info);
    Registry::get('view')->assign('carriers', fn_get_carriers());

} elseif ($mode == 'manage') {
    list($shipments, $search) = fn_get_shipments_info($params, Registry::get('settings.Appearance.admin_elements_per_page'));

    Registry::get('view')->assign('shipments', $shipments);
    Registry::get('view')->assign('search', $search);

} elseif ($mode == 'packing_slip' && !empty($_REQUEST['shipment_ids'])) {

    fn_print_shipment_packing_slips($_REQUEST['shipment_ids'], !empty($_REQUEST['format']) && $_REQUEST['format'] == 'pdf');
    exit;

} elseif ($mode == 'delete' && !empty($_REQUEST['shipment_ids']) && is_array($_REQUEST['shipment_ids'])) {
    $shipment_ids = implode(',', $_REQUEST['shipment_ids']);

    fn_delete_shipments($shipment_ids);

    return array(CONTROLLER_STATUS_OK, 'shipments.manage');
}

function fn_get_packing_info($shipment_id)
{
    $params['advanced_info'] = true;
    $params['shipment_id'] = $shipment_id;

    list($shipment, $search) = fn_get_shipments_info($params);

    if (!empty($shipment)) {
        $shipment = array_pop($shipment);

        $order_info = fn_get_order_info($shipment['order_id'], false, true, true);
        $shippings = db_get_array("SELECT a.shipping_id, a.min_weight, a.max_weight, a.position, a.status, b.shipping, b.delivery_time, a.usergroup_ids FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id = b.shipping_id AND b.lang_code = ?s ORDER BY a.position", DESCR_SL);

        $_products = db_get_array("SELECT item_id, SUM(amount) AS amount FROM ?:shipment_items WHERE order_id = ?i GROUP BY item_id", $shipment['order_id']);
        $shipped_products = array();

        if (!empty($_products)) {
            foreach ($_products as $_product) {
                $shipped_products[$_product['item_id']] = $_product['amount'];
            }
        }

        foreach ($order_info['products'] as $k => $oi) {
            if (isset($shipped_products[$k])) {
                $order_info['products'][$k]['shipment_amount'] = $oi['amount'] - $shipped_products[$k];
            } else {
                $order_info['products'][$k]['shipment_amount'] = $order_info['products'][$k]['amount'];
            }

            if (isset($shipment['products'][$k])) {
                $order_info['products'][$k]['amount'] = $shipment['products'][$k];
            } else {
                $order_info['products'][$k]['amount'] = 0;
            }
        }
    } else {
        $shipment = $order_info = array();
    }

    return array($shipment, $order_info);
}

function fn_print_shipment_packing_slips($shipment_ids, $pdf = false, $lang_code = CART_LANGUAGE)
{
    $view = Registry::get('view');

    foreach ($shipment_ids as $shipment_id) {
        list($shipment, $order_info) = fn_get_packing_info($shipment_id);
        if (empty($shipment)) {
            continue;
        }

        $view->assign('order_info', $order_info);
        $view->assign('shipment', $shipment);

        if ($pdf == true) {
            fn_disable_live_editor_mode();
            $html[] = $view->displayMail('orders/print_packing_slip.tpl', false, 'A', $order_info['company_id'], $lang_code);
        } else {
            $view->displayMail('orders/print_packing_slip.tpl', true, 'A', $order_info['company_id'], $lang_code);
            if ($shipment_id != end($shipment_ids)) {
                echo("<div style='page-break-before: always;'>&nbsp;</div>");
            }
        }
    }

    if ($pdf == true) {
        Pdf::render($html, __('shipments') . '-' . implode('-', $shipment_ids));
    }

    return true;
}
