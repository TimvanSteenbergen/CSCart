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
use Tygh\Mailer;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_rma_properties($type = RMA_REASON, $lang_code = CART_LANGUAGE)
{
    $status = (AREA == 'A') ? '' : " AND a.status = 'A'";

    return db_get_hash_array("SELECT a.*, b.property FROM ?:rma_properties AS a LEFT JOIN ?:rma_property_descriptions AS b ON a.property_id = b.property_id AND b.lang_code = ?s WHERE a.type = ?s $status ORDER BY a.position ASC", 'property_id', $lang_code, $type);
}

function fn_rma_delete_property($property_id)
{
    db_query("DELETE FROM ?:rma_properties WHERE property_id = ?i", $property_id);
    db_query("DELETE FROM ?:rma_property_descriptions WHERE property_id = ?i", $property_id);
}

function fn_is_returnable_product($product_id)
{
    $return_info = db_get_row("SELECT is_returnable, return_period  FROM ?:products WHERE product_id = ?i", $product_id);

    return (!empty($return_info) && $return_info['is_returnable'] == 'Y' && !empty($return_info['return_period'])) ? $return_info['return_period'] : false;
}

function fn_rma_add_to_cart(&$cart, &$product_id, &$_id)
{
    $return_period = fn_is_returnable_product($product_id);
    if ($return_period && !empty($cart['products'][$_id]['product_id'])) {
        $cart['products'][$_id]['return_period'] = $cart['products'][$_id]['extra']['return_period'] = $return_period;
    }
}

function fn_rma_get_product_data(&$product_id, &$field_list, &$join)
{
    $field_list .= ", ?:products.is_returnable, ?:products.return_period";
}

function fn_check_product_return_period($return_period, $timestamp)
{
    $weekdays = 0;
    $round_the_clock = 60 * 60 * 24;

    if (Registry::get('addons.rma.dont_take_weekends_into_account') == 'Y') {
        $passed_days = floor((TIME - $timestamp) / $round_the_clock);
        for ($i = 1; $i <= $passed_days; $i++) {
            if (strstr(SATURDAY.SUNDAY, strftime("%w", $timestamp + $i * $round_the_clock))) {
                $weekdays++;
            }
        }
    }

    return ((($return_period + $weekdays) * $round_the_clock + $timestamp) > TIME) ? true : false;
}

function fn_get_order_returnable_products($order_items, $timestamp)
{
    $item_returns_info = array();
    foreach ((array) $order_items as $k => $v) {
        if (isset($v['extra']['return_period']) &&  true == fn_check_product_return_period($v['extra']['return_period'], $timestamp)) {
            if (!isset($v['extra']['exclude_from_calculate'])) {
                $order_items[$k]['price'] = fn_format_price($v['subtotal'] / $v['amount']);
            }
            if (isset($v['extra']['returns'])) {
                foreach ((array) $v['extra']['returns'] as $return_id => $value) {
                    $item_returns_info[$k][$value['status']] = (isset($item_returns_info[$k][$value['status']]) ? $item_returns_info[$k][$value['status']] : 0) + $value['amount'];
                }
                if (0 >= $order_items[$k]['amount'] = $v['amount'] - array_sum($item_returns_info[$k])) {
                    unset($order_items[$k]);
                }
            }
        } else {
            unset($order_items[$k]);
        }
    }

    return array(
        'items'	            => $order_items,
        'item_returns_info' => $item_returns_info
    );
}

function fn_rma_generate_sections($section)
{
    Registry::set('navigation.dynamic.sections', array (
        'requests' => array (
            'title' => __('return_requests'),
            'href' => "rma.returns",
        ),
        'reasons' => array (
            'title' => __('rma_reasons'),
            'href' => "rma.properties?property_type=R",
        ),
        'actions' => array (
            'title' => __('rma_actions'),
            'href' => "rma.properties?property_type=A",
        ),
        'statuses' => array (
            'title' => __('rma_request_statuses'),
            'href' => "statuses.manage?type=R",
        ),
    ));

    Registry::set('navigation.dynamic.active_section', $section);

    return true;
}

function fn_rma_get_order_info(&$order, &$additional_data)
{
    if (!empty($order)) {
        $status_data = fn_get_status_params($order['status'], STATUSES_ORDER);

        if (!empty($status_data) && $status_data['allow_return'] == 'Y' && isset($additional_data[ORDER_DATA_PRODUCTS_DELIVERY_DATE])) {
            $order_returnable_products = fn_get_order_returnable_products($order['products'], $additional_data[ORDER_DATA_PRODUCTS_DELIVERY_DATE]);
            if (!empty($order_returnable_products['items'])) {
                $order['allow_return'] = 'Y';
            }
            if (!empty($order_returnable_products['item_returns_info'])) {
                foreach ($order_returnable_products['item_returns_info'] as $item_id => $returns_info) {
                    $order['products'][$item_id]['returns_info'] = $returns_info;
                }
            }
        }

        if (!empty($additional_data[ORDER_DATA_PRODUCTS_DELIVERY_DATE])) {
            $order['products_delivery_date'] = $additional_data[ORDER_DATA_PRODUCTS_DELIVERY_DATE];
        }

        if (!empty($additional_data[ORDER_DATA_RETURN])) {
            $order_return_info = @unserialize($additional_data[ORDER_DATA_RETURN]);
            $order['return'] = @$order_return_info['return'];
            $order['returned_products'] = @$order_return_info['returned_products'];
                foreach ((array) $order['returned_products'] as $k => $v) {
                    $v['product'] = !empty($v['extra']['product']) ? $v['extra']['product'] : fn_get_product_name($v['product_id'], CART_LANGUAGE);
                    if (empty($v['product'])) {
                        $v['product'] = strtoupper(__('deleted_product'));
                    }
                    $v['discount'] = (!empty($v['extra']['discount']) && floatval($v['extra']['discount'])) ? $v['extra']['discount'] : 0 ;

                    if (!empty($v['extra']['product_options_value'])) {
                        $v['product_options'] = $v['extra']['product_options_value'];
                    }
                    $v['subtotal'] = ($v['price'] * $v['amount'] - $v['discount']);
                    $order['returned_products'][$k] = $v;
                }
        }

        if (0 < $returns_count = db_get_field("SELECT COUNT(*) FROM ?:rma_returns WHERE order_id = ?i", $order['order_id'])) {
            $order['isset_returns'] = 'Y';
        }
    }
}

function fn_get_return_info($return_id)
{
    if (!empty($return_id)) {
        $return = db_get_row("SELECT * FROM ?:rma_returns WHERE return_id = ?i", $return_id);

        if (empty($return)) {
            return array();
        }

        $return['items'] = db_get_hash_multi_array("SELECT ?:rma_return_products.*, ?:products.product_id as original_product_id FROM ?:rma_return_products LEFT JOIN ?:products ON ?:rma_return_products.product_id = ?:products.product_id WHERE ?:rma_return_products.return_id = ?i", array('type', 'item_id'), $return_id);
        foreach ($return['items'] as $type => $value) {
            foreach ($value as $k => $v) {
                if (0 == floatval($v['price'])) {
                    $return['items'][$type][$k]['price'] = '';
                }

                if (empty($v['original_product_id'])) {
                    $return['items'][$type][$k]['deleted_product'] = true;
                }

                if (empty($v['product'])) {
                    $v['product'] = strtoupper(__('deleted_product'));
                }

                $return['items'][$type][$k]['product_options'] = !empty($return['items'][$type][$k]['product_options']) ? unserialize($return['items'][$type][$k]['product_options']) : array();
            }
        }

        return $return;
    }

    return false;
}

function fn_return_product_routine($return_id, $item_id, $item, $direction)
{

    $reverse = array(
        RETURN_PRODUCT_ACCEPTED => RETURN_PRODUCT_DECLINED,
        RETURN_PRODUCT_DECLINED => RETURN_PRODUCT_ACCEPTED
    );

    if (!empty($return_id) && !empty($item_id) && !empty($direction) && !empty($item)) {
        $is_amount = db_get_field("SELECT amount FROM ?:rma_return_products WHERE return_id = ?i AND item_id = ?i AND type = ?s", $return_id, $item_id, $direction);
        if (($item['previous_amount'] - $item['amount']) <= 0) {
            if (empty($is_amount)) {
                db_query('UPDATE ?:rma_return_products SET ?u WHERE return_id = ?i AND item_id = ?i AND type = ?s', array('type' => $direction), $return_id, $item_id, $reverse[$direction]);
            } else {
                db_query("DELETE FROM ?:rma_return_products WHERE return_id = ?i AND item_id = ?i AND type = ?s", $return_id, $item_id, $reverse[$direction]);
            }
        } else {
            $_data = db_get_row("SELECT * FROM ?:rma_return_products WHERE return_id = ?i AND item_id = ?i AND type = ?s", $return_id, $item_id, $reverse[$direction]);
            db_query('UPDATE ?:rma_return_products SET ?u WHERE return_id = ?i AND item_id = ?i AND type = ?s', array('amount' => $_data['amount'] - $item['amount']), $return_id, $item_id, $reverse[$direction]);

            if (empty($is_amount)) {
                $_data['amount'] = $item['amount'];
                $_data['type'] = $direction;
                db_query("REPLACE INTO ?:rma_return_products ?e", $_data);
            }
        }
        if (!empty($is_amount)) {
            db_query('UPDATE ?:rma_return_products SET ?u WHERE return_id = ?i AND item_id = ?i AND type = ?s', array('amount' => $is_amount + $item['amount']), $return_id, $item_id, $direction);
        }
    }

    return false;
}

function fn_delete_return($return_id)
{

    $items = db_get_array("SELECT item_id, ?:order_details.extra, ?:order_details.order_id FROM ?:order_details LEFT JOIN ?:rma_returns ON ?:order_details.order_id = ?:rma_returns.order_id WHERE  return_id = ?i", $return_id);
    foreach ($items as $item) {
        $extra = unserialize($item['extra']);
        if (isset($extra['returns'])) {
            unset($extra['returns']);
        }
        db_query('UPDATE ?:order_details SET ?u WHERE item_id = ?i AND order_id = ?i', array('extra' => serialize($extra)), $item['item_id'],  $item['order_id']);
    }

    db_query("DELETE FROM ?:rma_returns WHERE return_id = ?i", $return_id);
    db_query("DELETE FROM ?:rma_return_products WHERE return_id = ?i", $return_id);
}

function fn_send_return_mail(& $return_info, & $order_info, $force_notification = array())
{
    $return_statuses = fn_get_statuses(STATUSES_RETURN);
    $status_params = $return_statuses[$return_info['status']]['params'];

    $notify_user = isset($force_notification['C']) ? $force_notification['C'] : (!empty($status_params['notify']) && $status_params['notify'] == 'Y' ? true : false);
    $notify_department = isset($force_notification['A']) ? $force_notification['A'] : (!empty($status_params['notify_department']) && $status_params['notify_department'] == 'Y' ? true : false);
    $notify_vendor = isset($force_notification['V']) ? $force_notification['V'] : (!empty($status_params['notify_vendor']) && $status_params['notify_vendor'] == 'Y' ? true : false);

    if ($notify_user == true || $notify_department == true || $notify_vendor == true) {

        $rma_reasons = fn_get_rma_properties(RMA_REASON);
        $rma_actions = fn_get_rma_properties(RMA_ACTION);

        // Notify customer
        if ($notify_user == true) {
            Mailer::sendMail(array(
                'to' => $order_info['email'],
                'from' => 'company_orders_department',
                'data' => array(
                    'order_info' => $order_info,
                    'return_info' => $return_info,
                    'reasons' => $rma_reasons,
                    'actions' => $rma_actions,
                    'return_status' => fn_get_status_data($return_info['status'], STATUSES_RETURN, $return_info['return_id'], $order_info['lang_code'])
                ),
                'tpl' => 'addons/rma/slip_notification.tpl',
                'company_id' => $order_info['company_id'],
            ), 'C', $order_info['lang_code']);
        }

        if ($notify_vendor == true) {
            if (fn_allowed_for('MULTIVENDOR') && !empty($order_info['company_id'])) {
                $company_language = fn_get_company_language($order_info['company_id']);

                Mailer::sendMail(array(
                    'to' => 'company_orders_department',
                    'from' => 'default_company_orders_department',
                    'data' => array(
                        'order_info' => $order_info,
                        'return_info' => $return_info,
                        'reasons' => $rma_reasons,
                        'actions' => $rma_actions,
                        'return_status' => fn_get_status_data($return_info['status'], STATUSES_RETURN, $return_info['return_id'], $company_language)
                    ),
                    'tpl' => 'addons/rma/slip_notification.tpl',
                    'company_id' => $order_info['company_id'],
                ), 'A', $company_language);
            }
        }

        // Notify administrator (only if the changes performed from the frontend)
        if ($notify_department == true) {
            Mailer::sendMail(array(
                'to' => 'company_orders_department',
                'from' => 'default_company_orders_department',
                'reply_to' => Registry::get('settings.Company.company_orders_department'),
                'data' => array(
                    'order_info' => $order_info,
                    'return_info' => $return_info,
                    'reasons' => $rma_reasons,
                    'actions' => $rma_actions,
                    'return_status' => fn_get_status_data($return_info['status'], STATUSES_RETURN, $return_info['return_id'], Registry::get('settings.Appearance.backend_default_language'))
                ),
                'tpl' => 'addons/rma/slip_notification.tpl',
                'company_id' => $order_info['company_id'],
            ), 'A', Registry::get('settings.Appearance.backend_default_language'));

        }

    }
}

function fn_is_refund_action($action)
{
    return 	db_get_field("SELECT update_totals_and_inventory FROM ?:rma_properties WHERE property_id = ?i", $action);
}

function fn_rma_delete_gift_certificate(&$gift_cert_id, &$extra)
{

    $potentional_certificates = array();

    if (isset($extra['return_id'])) {
        $potentional_certificates[$extra['return_id']] = db_get_field("SELECT extra FROM ?:rma_returns WHERE return_id = ?i", $extra['return_id']);
    } else {
        $potentional_certificates = db_get_hash_single_array("SELECT return_id, extra FROM ?:rma_returns WHERE extra IS NOT NULL", array('return_id', 'extra'));
    }

    if (!empty($potentional_certificates)) {
        foreach ($potentional_certificates as $return_id => $return_extra) {
            $return_extra = @unserialize($return_extra);
            if (isset($return_extra['gift_certificates'])) {
                foreach ((array) $return_extra['gift_certificates'] as $k => $v) {
                    if ($k == $gift_cert_id) {
                        unset($return_extra['gift_certificates'][$k]);
                        if (empty($return_extra['gift_certificates'])) {
                            unset($return_extra['gift_certificates']);
                        }
                        db_query('UPDATE ?:rma_returns SET ?u WHERE return_id = ?i', array('extra' => serialize($return_extra)), $return_id);
                        break;
                    }
                }
            }
        }
    }
}

function fn_rma_declined_product_correction($order_id, $item_id, $available_amount, $amount)
{
    $declined_items_amount = db_get_field("SELECT SUM(?:rma_return_products.amount) FROM ?:rma_return_products LEFT JOIN ?:rma_returns ON ?:rma_returns.return_id = ?:rma_return_products.return_id AND ?:rma_returns.order_id = ?i  WHERE ?:rma_return_products.item_id = ?i AND ?:rma_return_products.type = ?s GROUP BY ?:rma_return_products.item_id", $order_id, $item_id, RETURN_PRODUCT_DECLINED);
    if ($available_amount - $amount >= $declined_items_amount) {
        return true;
    } else {
        $declined_items	 = db_get_hash_array("SELECT ?:rma_return_products.return_id, item_id, amount FROM ?:rma_return_products LEFT JOIN ?:rma_returns ON ?:rma_returns.return_id = ?:rma_return_products.return_id AND ?:rma_returns.order_id = ?i WHERE ?:rma_return_products.item_id = ?i AND ?:rma_return_products.type = ?s", 'return_id', $order_id, $item_id, RETURN_PRODUCT_DECLINED);
        foreach ($declined_items as $return_id => $v) {
            $difference = $v['amount'] - $amount;
            if ($difference > 0) {
                db_query('UPDATE ?:rma_return_products SET ?u WHERE return_id = ?i AND item_id = ?i AND type = ?s', array('amount' => $difference), $return_id, $v['item_id'], RETURN_PRODUCT_DECLINED);

                return true;
            } elseif ($difference <= 0) {
                db_query("DELETE FROM ?:rma_return_products WHERE return_id = ?i AND item_id = ?i AND type = ?s", $return_id, $v['item_id'], RETURN_PRODUCT_DECLINED);
                if ($difference == 0) {
                    return true;
                }
            }
        }
    }
}

function fn_rma_change_order_status(&$status_to, &$status_from, &$order_info)
{

    $status_data = fn_get_status_params($status_to, STATUSES_ORDER);

    if (!empty($status_data) && $status_data['allow_return'] == 'Y') {
        $_data = array(
            'order_id' => $order_info['order_id'],
            'type' => ORDER_DATA_PRODUCTS_DELIVERY_DATE,
            'data' => TIME
        );
        db_query("REPLACE INTO ?:order_data ?e", $_data);
    } else {
        db_query("DELETE FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_info['order_id'], ORDER_DATA_PRODUCTS_DELIVERY_DATE);
    }
}

//
// This function updates the taxes basing on amount inrement/decrement
//
function fn_rma_update_order_taxes(&$tax_data, $item_id, $old_amount, $new_amount, &$order)
{
    if (is_array($tax_data)) {
        foreach ($tax_data as $k => $v) {
            if (isset($v['applies']['P_' . $item_id])) {
                $_tax = $v['applies']['P_' . $item_id];
                $_new_tax = fn_format_price($_tax * $new_amount / $old_amount);
                $tax_data[$k]['applies']['P_' . $item_id] = $_new_tax;
                $tax_data[$k]['tax_subtotal'] -=  ($_tax - $_new_tax);
            }
            if ($v['price_includes_tax'] == 'N') {
                $order['subtotal'] += ($_new_tax - $_tax);
                $order['total'] += ($_new_tax - $_tax);
            }
        }
    }

    return true;
}

//
// This function updates shipping costs and their taxes taxes.
//
function fn_update_shipping_taxes(&$tax_data, &$shipping_cost, &$order)
{
    if (is_array($tax_data) && is_array($shipping_cost)) {
        foreach ($shipping_cost as $shipping_id => $sh_data) {
            foreach ($sh_data['rates'] as $s_id => $rate) {
                foreach ($tax_data as $k => $tax) {
                    if (isset($tax['applies']['S_' . $shipping_id . '_' . $s_id])) {

                        if ($tax['rate_type'] == 'P') { // Percent dependence
                            // If tax is included into the price
                            if ($tax['price_includes_tax'] == 'Y') {
                                $_tax = fn_format_price($rate - $rate / (1 + ($tax['rate_value'] / 100)));
                                // If tax is NOT included into the price
                            } else {
                                $_tax = fn_format_price($rate * ($tax['rate_value'] / 100));
                            }

                        } else {
                            $_tax = fn_format_price($tax['rate_value']);
                        }

                        $tax_data[$k]['applies']['S_' . $shipping_id . '_' . $s_id] = $_tax;
                        $tax_data[$k]['tax_subtotal'] = array_sum($tax_data[$k]['applies']);

                        if ($tax['price_includes_tax'] === 'N') {
                            $order['subtotal'] += ($_tax - $tax['applies']['S_' . $shipping_id . '_' . $s_id]);
                            $order['total'] += ($_tax - $tax['applies']['S_' . $shipping_id . '_' . $s_id]);
                        }
                    }
                }
            }
        }
    }

    return true;
}

/**
* $type values
*
* M-O+   change main and optional data
* O-     change optional data
* M+     change main data
*
*/
function fn_rma_recalculate_order_routine(&$order, &$item, $mirror_item, $type = '', $ex_data = array())
{
    $amount = 0;
    if (!isset($item['extra']['exclude_from_calculate'])) {
        if (in_array($type, array('M+', 'M-O+'))) {
            $sign = ($type == 'M+') ? 1 : -1;

            $delta = ($mirror_item['price'] * $mirror_item['extra']['returns'][$ex_data['return_id']]['amount']);
            $order['subtotal'] = $order['subtotal'] + $sign * $delta;
            $order['total'] = $order['total'] + $sign * $delta;

            $_discount = isset($mirror_item['extra']['discount']) ? $mirror_item['extra']['discount'] : (isset($item['extra']['discount']) ? $item['extra']['discount'] : 0);
            $order['discount'] = $order['discount'] + $sign * $_discount * $item['amount'];
            unset($mirror_item['extra']['discount'], $item['extra']['discount']);
        }
        if (in_array($type, array('O-', 'M-O+'))) {
            $amount = fn_rma_recalculate_product_amount($item['item_id'], $item['product_id'], @$item['extra']['product_options'], $type, $ex_data);
        }
    } else {
        if (in_array($type, array('O-', 'M-O+'))) {
            fn_rma_recalculate_product_amount($item['item_id'], $item['product_id'], @$item['extra']['product_options'], $type, $ex_data);
        }
    }

    fn_set_hook('rma_recalculate_order', $item, $mirror_item, $type, $ex_data, $amount);
}

function fn_rma_recalculate_product_amount($item_id, $product_id, $product_options, $type, $ex_data)
{

    $sign = ($type == 'O-') ? '-' : '+';
    $amount = db_get_field("SELECT amount FROM ?:rma_return_products WHERE return_id = ?i AND item_id = ?i AND type = ?s", $ex_data['return_id'], $item_id, RETURN_PRODUCT_ACCEPTED);
    fn_update_product_amount($product_id, $amount, $product_options, $sign);

    return $amount;
}

function fn_rma_recalculate_order($order_id, $recalculate_type, $return_id, $is_refund,  $ex_data)
{
    if (empty($recalculate_type) || empty($return_id) || empty($order_id) || !is_array($ex_data) || ($recalculate_type == 'M' && !isset($ex_data['total']))) {
        return false;
    }

    $order = db_get_row("SELECT total, subtotal, discount, shipping_cost, status FROM ?:orders WHERE order_id = ?i", $order_id);
    $order_items = db_get_hash_array("SELECT * FROM ?:order_details WHERE ?:order_details.order_id = ?i", 'item_id', $order_id);
    $additional_data = db_get_hash_single_array("SELECT type, data FROM ?:order_data WHERE order_id = ?i", array('type', 'data'), $order_id);
    $order_return_info = @unserialize(@$additional_data[ORDER_DATA_RETURN]);
    $order_tax_info = @unserialize(@$additional_data['T']);
    $status_order = $order['status'];
    unset($order['status']);
    if ($recalculate_type == 'R') {
        $product_groups = @unserialize(@$additional_data['G']);
        if ($is_refund == 'Y') {
            $sign = ($ex_data['inventory_to'] == 'I') ? -1 : 1;
            // What for is this section ???
            if (!empty($order_return_info['returned_products'])) {
                foreach ($order_return_info['returned_products'] as $item_id => $item) {
                    if (isset($item['extra']['returns'][$return_id])) {
                        $r_item = $o_item = $item;
                        unset($r_item['extra']['returns'][$return_id]);
                        $r_item['amount'] = $item['amount'] - $item['extra']['returns'][$return_id]['amount'];
                        fn_rma_recalculate_order_routine($order, $r_item, $item, 'O-', $ex_data);
                        if (empty($r_item['amount'])) {
                            unset($order_return_info['returned_products'][$item_id]);
                        } else {
                            $order_return_info['returned_products'][$item_id] = $r_item;
                        }

                        $o_item['primordial_amount'] = (isset($order_items[$item_id]) ? $order_items[$item_id]['amount'] : 0) + $item['extra']['returns'][$return_id]['amount'];
                        $o_item['primordial_discount'] = @$o_item['extra']['discount'];
                        fn_rma_recalculate_order_routine($order, $o_item, $item, 'M+', $ex_data);
                        $o_item['amount'] = (isset($order_items[$item_id]) ? $order_items[$item_id]['amount'] : 0) + $item['extra']['returns'][$return_id]['amount'];

                        if (isset($order_items[$item_id]['extra'])) {
                            $o_item['extra'] = @unserialize($order_items[$item_id]['extra']);
                        }
                        $o_item['extra']['returns'][$return_id] = $item['extra']['returns'][$return_id];

                        $o_item['extra'] = serialize($o_item['extra']);
                        if (!isset($order_items[$item_id])) {
                            db_query("REPLACE INTO ?:order_details ?e", $o_item);
                        } else {
                            db_query("UPDATE ?:order_details SET ?u WHERE item_id = ?i AND order_id = ?i", $o_item, $item_id, $order_id);
                        }

                    }
                }
            }

            // Check all the products and update their amount and cost.
            foreach ($order_items as $item_id => $item) {
                $item['extra'] = @unserialize($item['extra']);

                if (isset($item['extra']['returns'][$return_id])) {
                    $o_item = $item;
                    $o_item['amount'] = $o_item['amount'] + $sign * $item['extra']['returns'][$return_id]['amount'];
                    unset($o_item['extra']['returns'][$return_id]);
                    if (empty($o_item['extra']['returns'])) {
                        unset($o_item['extra']['returns']);
                    }

                    fn_rma_recalculate_order_routine($order, $o_item, $item, '', $ex_data);
                    if (empty($o_item['amount'])) {
                        db_query("DELETE FROM ?:order_details WHERE item_id = ?i AND order_id = ?i", $item_id, $order_id);
                    } else {
                        $o_item['extra'] = serialize($o_item['extra']);
                        db_query("UPDATE ?:order_details SET ?u WHERE item_id = ?i AND order_id = ?i", $o_item, $item_id, $order_id);
                    }

                    if (!isset($order_return_info['returned_products'][$item_id])) {
                        $r_item = $item;
                        unset($r_item['extra']['returns']);
                        $r_item['amount'] = $item['extra']['returns'][$return_id]['amount'];
                    } else {
                        $r_item = $order_return_info['returned_products'][$item_id];
                        $r_item['amount'] = $r_item['amount'] + $item['extra']['returns'][$return_id]['amount'];
                    }
                    fn_rma_recalculate_order_routine($order, $r_item, $item, 'M-O+', $ex_data);
                    $r_item['extra']['returns'][$return_id] = $item['extra']['returns'][$return_id];
                    $order_return_info['returned_products'][$item_id] = $r_item;
                    fn_rma_update_order_taxes($order_tax_info, $item_id, $item['amount'], $o_item['amount'], $order);
                }
            }

            $_ori_data = array(
                'order_id' => $order_id,
                'type' 	   => ORDER_DATA_RETURN,
                'data'     => $order_return_info
            );
        }

        $shipping_info = array();
        if ($product_groups) {

            $_total = 0;

            foreach ($product_groups as $key_group => $group) {
                if (isset($group['chosen_shippings'])) {
                    foreach ($group['chosen_shippings'] as $key_shipping => $shipping) {
                        $_total += $shipping['rate'];
                    }
                }
            }

            foreach ($product_groups as $key_group => $group) {
                if (isset($group['chosen_shippings'])) {    
                    foreach ((array) $ex_data['shipping_costs'] as $shipping_id => $cost) {
                        foreach ($group['chosen_shippings'] as $key_shipping => $shipping) {
                            $shipping_id = $shipping['shipping_id'];
                            $product_groups[$key_group]['chosen_shippings'][$key_shipping]['rate'] = fn_format_price($_total ? (($shipping['rate'] / $_total) * $cost) : ($cost / count($product_groups)));
                            $product_groups[$key_group]['shippings'][$shipping_id]['rate'] = fn_format_price($_total ? (($shipping['rate'] / $_total) * $cost) : ($cost / count($product_groups)));
                            if (empty($shipping_info[$shipping_id])) {
                                $shipping_info[$shipping_id] = $product_groups[$key_group]['shippings'][$shipping_id];
                            }
                            $shipping_info[$shipping_id]['rates'][$key_group] = $product_groups[$key_group]['shippings'][$shipping_id]['rate'];
                        }
                    }
                }    
            }
            db_query("UPDATE ?:order_data SET ?u WHERE order_id = ?i AND type = 'G'", array('data' => serialize($product_groups)), $order_id);

            fn_update_shipping_taxes($order_tax_info, $shipping_info, $order);
        }

        $order['total'] -= $order['shipping_cost'];
        $order['shipping_cost'] = isset($ex_data['shipping_costs']) ? array_sum($ex_data['shipping_costs']) : $order['shipping_cost'];
        $order['total'] += $order['shipping_cost'];

        $order['total'] = ($order['total'] < 0) ? 0 : $order['total'];

        if (!empty($order_tax_info)) {
            db_query("UPDATE ?:order_data SET ?u WHERE order_id = ?i AND type = 'T'", array('data' => serialize($order_tax_info)), $order_id);
        }

    } elseif ($recalculate_type == 'M') {
        $order['total'] = $order['total'] + $ex_data['total'];
        $_ori_data = array(
            'order_id' => $order_id,
            'type'     =>  ORDER_DATA_RETURN,
            'data'     => array(
                'return' 			=> fn_format_price(((isset($order_return_info['return']) ? $order_return_info['return'] : 0) - $ex_data['total'])),
                'returned_products' => (isset($order_return_info['returned_products'])) ? $order_return_info['returned_products'] : ''
            )
        );

        $return_products = db_get_hash_array("SELECT * FROM ?:rma_return_products WHERE return_id = ?i AND type = ?s", 'item_id', $return_id, RETURN_PRODUCT_ACCEPTED);
        foreach ((array) $return_products as $item_id => $v) {
            $v['extra']['product_options'] = @unserialize($v['extra']['product_options']);
            if ($ex_data['inventory_to'] == 'D' || $ex_data['status_to'] == RMA_DEFAULT_STATUS) {
                fn_update_product_amount($v['product_id'], $v['amount'], @$v['extra']['product_options'], '-');
            } elseif ($ex_data['inventory_to'] == 'I') {
                fn_update_product_amount($v['product_id'], $v['amount'], $v['extra']['product_options'], '+');
            }
        }
    }

    if ($is_refund == 'Y') {
        if (isset($_ori_data['data']['return']) && floatval($_ori_data['data']['return']) == 0) {
            unset($_ori_data['data']['return']);
        }
        if (empty($_ori_data['data']['returned_products'])) {
            unset($_ori_data['data']['returned_products']);
        }

        if (!empty($_ori_data['data'])) {
            $_ori_data['data'] = serialize($_ori_data['data']);
            db_query("REPLACE INTO ?:order_data ?e", $_ori_data);
        } else {
            db_query("DELETE FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, ORDER_DATA_RETURN);
        }
    }

    foreach ($order as $k => $v) {
        $order[$k] = fn_format_price($v);
    }

    db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", $order, $order_id);

    if (fn_allowed_for('MULTIVENDOR')) {
        $_SESSION['cart'] = isset($_SESSION['cart']) ? $_SESSION['cart'] : array();
        $cart = & $_SESSION['cart'];

        $action = 'save';
        fn_mve_place_order($order_id, $action, $status_order, $cart);
    }
}

function fn_rma_get_status_params_definition(&$status_params, &$type)
{
    if ($type == STATUSES_ORDER) {
        $status_params['allow_return'] = array (
                'type' => 'checkbox',
                'label' => 'allow_return_registration'
        );

    } elseif ($type == STATUSES_RETURN) {
        $status_params = array (
            'inventory' => array (
                'type' => 'select',
                'label' => 'inventory',
                'variants' => array (
                    'I' => 'increase',
                    'D' => 'decrease',
                ),
                'not_default' => true
            )
        );
    }

    return true;
}

function fn_rma_delete_order(&$order_id)
{
    $return_ids = db_get_fields("SELECT return_id FROM ?:rma_returns WHERE order_id = ?i", $order_id);
    if (!empty($return_ids)) {
        foreach ($return_ids as $return_id) {
            fn_delete_return($return_id);
        }
    }
}

function fn_rma_print_packing_slips($return_ids, $auth, $area = AREA)
{
    $view = Registry::get('view');
    $passed = false;

    if (!is_array($return_ids)) {
        $return_ids = array($return_ids);
    }

    $view->assign('reasons', fn_get_rma_properties(RMA_REASON));
    $view->assign('actions', fn_get_rma_properties(RMA_ACTION));
    $view->assign('order_status_descr', fn_get_simple_statuses(STATUSES_RETURN));

    foreach ($return_ids as $return_id) {
        $return_info = fn_get_return_info($return_id);

        if (empty($return_info) || ($area == 'C' && $return_info['user_id'] != $auth['user_id'])) {
            continue;
        }

        $order_info = fn_get_order_info($return_info['order_id']);

        if (empty($order_info)) {
            continue;
        }

        $passed = true;
        $view->assign('return_info', $return_info);
        $view->assign('order_info', $order_info);
        $view->assign('company_data', fn_get_company_placement_info($order_info['company_id']));
        $view->displayMail('addons/rma/print_slip.tpl', true, $area, $order_info['company_id']);
        if ($return_id != end($return_ids)) {
            echo("<div style='page-break-before: always;'>&nbsp;</div>");
        }
    }

    return $passed;
}

/**
 * Gets return request name
 *
 * @param int return_id Return identifier
 * @return string Return title
 */
function fn_rma_get_return_name($return_id)
{
    return $return_id;
}
