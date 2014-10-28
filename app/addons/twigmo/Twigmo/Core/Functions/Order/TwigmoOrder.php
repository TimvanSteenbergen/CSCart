<?php

namespace Twigmo\Core\Functions\Order;

use Twigmo\Core\Api;
use Tygh\Session;
use Tygh\Registry;

class TwigmoOrder
{
    public static function apiUpdateOrder($order, $response)
    {
        if (!defined('ORDER_MANAGEMENT')) {
            define('ORDER_MANAGEMENT', true);
        }

        if (!empty($order['status'])) {

            $statuses = fn_get_statuses(STATUSES_ORDER, false, true);

            if (!isset($statuses[$order['status']])) {
                $response->addError(
                    'ERROR_OBJECT_UPDATE',
                    str_replace(
                        '[object]',
                        'orders',
                        __('twgadmin_wrong_api_object_data')
                    )
                );
            } else {
                fn_change_order_status($order['order_id'], $order['status']);
            }
        }

        $cart = array();
        fn_clear_cart($cart, true);
        $customer_auth = fn_fill_auth(array(), array(), false, 'C');

        fn_form_cart($order['order_id'], $cart, $customer_auth);
        $cart['order_id'] = $order['order_id'];

        // update only profile data
        $profile_data = fn_check_table_fields($order, 'user_profiles');

        $cart['user_data'] = fn_array_merge($cart['user_data'], $profile_data);
        $cart['user_data'] = fn_array_merge($cart['user_data'], $order);
        fn_calculate_cart_content($cart, $customer_auth, 'A', true, 'I');

        if (!empty($order['details'])) {
            db_query(
                'UPDATE ?:orders SET details = ?s WHERE order_id = ?i',
                $order['details'],
                $order['order_id']
            );
        }

        if (!empty($order['notes'])) {
            $cart['notes'] = $order['notes'];
        }

        list($order_id, $process_payment) = fn_place_order($cart, $customer_auth, 'save');

        return array($order_id, $process_payment);
    }

    public static function apiPlaceOrder($data, &$response, $lang_code = CART_LANGUAGE)
    {
        $cart = & $_SESSION['cart'];
        $auth = & $_SESSION['auth'];
        if (empty($cart)) {
            $response->addError(
                'ERROR_ACCESS_DENIED',
                __(
                    'access_denied',
                    $lang_code
                )
            );
            $response->returnResponse();
        }
        if (!empty($data['user'])) {
            fn_twg_api_set_cart_user_data(
                $data['user'],
                $response,
                $lang_code
            );
        }
        if (empty($auth['user_id']) && empty($cart['user_data'])) {
            $response->addError(
                'ERROR_ACCESS_DENIED',
                __(
                    'access_denied',
                    $lang_code
                )
            );
            $response->returnResponse();
        }
        if (empty($data['payment_info']) && !empty($cart['extra_payment_info'])) {
            $data['payment_info'] = $cart['extra_payment_info'];
        }
        if (!empty($data['payment_info'])) {
            $cart['payment_id'] = (int) $data['payment_info']['payment_id'];
            unset($data['payment_info']['payment_id']);

            if (!empty($data['payment_info'])) {
                $cart['payment_info'] = $data['payment_info'];
            }

            unset($cart['payment_updated']);
            fn_update_payment_surcharge($cart, $auth);

            fn_save_cart_content($cart, $auth['user_id']);
        }
        unset($cart['payment_info']['secure_card_number']);

        // Remove previous failed order
        if (!empty($cart['failed_order_id']) || !empty($cart['processed_order_id'])) {
            $_order_ids = !empty($cart['failed_order_id']) ? $cart['failed_order_id'] : $cart['processed_order_id'];

            foreach ($_order_ids as $_order_id) {
                fn_delete_order($_order_id);
            }
            $cart['rewrite_order_id'] = $_order_ids;
            unset($cart['failed_order_id'], $cart['processed_order_id']);
        }

        if (!empty($data['shippings'])) {
                if (!fn_checkout_update_shipping($cart, $data['shippings'])) {
                    unset($cart['shipping']);
                }
        }

        Registry::set('runtime.controller', 'checkout', true);
        list (, $_SESSION['shipping_rates']) = fn_calculate_cart_content($cart, $auth, 'E');
        Registry::set('runtime.controller', 'twigmo');

        if (empty($cart['shipping']) && $cart['shipping_failed']) {
            $response->addError(
                'ERROR_WRONG_CHECKOUT_DATA',
                __(
                    'wrong_shipping_info',
                    $lang_code
                )
            );
            $response->returnResponse();
        }
        if (empty($cart['payment_info']) && !isset($cart['payment_id'])) {
            $response->addError(
                'ERROR_WRONG_CHECKOUT_DATA',
                __(
                    'wrong_payment_info',
                    $lang_code
                )
            );
            $response->returnResponse();
        }
        if (!empty($data['notes'])) {
            $cart['notes'] = $data['notes'];
        }
        $cart['details'] = __('twgadmin_order_via_twigmo');
        list($order_id, $process_payment) = fn_place_order($cart, $auth);
        if (empty($order_id)) {
            return false;
        }
        if ($process_payment == true) {
            $payment_info =
                !empty($cart['payment_info']) ?
                    $cart['payment_info'] :
                    array();
            fn_twg_start_payment($order_id, array(), $payment_info);
        }
        self::orderPlacementRoutines($order_id);

        return $order_id;
    }

    public static function getOrdersAsApiList($orders, $lang_code)
    {
        $order_ids = array();
        foreach ($orders as $order) {
            $order_ids[] = $order['order_id'];
        }

        if (!empty($order_ids)) {
            $payment_names = db_get_hash_array(
                "SELECT order_id, payment
                 FROM ?:orders, ?:payment_descriptions
                 WHERE ?:payment_descriptions.payment_id = ?:orders.payment_id
                 AND ?:payment_descriptions.lang_code = ?s
                 AND ?:orders.order_id IN (?a)",
                'order_id',
                $lang_code,
                $order_ids
            );
            $shippings  = db_get_hash_array(
                "SELECT order_id, data
                 FROM ?:order_data
                 WHERE type = ?s
                 AND order_id IN (?a)",
                'order_id',
                'L',
                $order_ids
            );
        } else {
            $payment_names = array();
            $shippings = array();
        }

        foreach ($orders as $k => $v) {
            $orders[$k]['payment'] = !empty($payment_names[$v['order_id']]['payment'])?
                $payment_names[$v['order_id']]['payment'] : '';
            $orders[$k]['shippings'] = array();
            if (!empty($shippings[$v['order_id']]['data'])) {
                $shippings = unserialize($shippings[$v['order_id']]['data']);

                if (empty($shippings)) {
                    continue;
                }

                foreach ($shippings as $shipping) {
                    $orders[$k]['shippings'][] = array (
                        'carrier' => !empty($shipping['carrier']) ? $shipping['carrier'] : '',
                        'shipping' => !empty($shipping['shipping']) ? $shipping['shipping'] : '',
                    );
                }
            }
        }

        $fields = array (
            'order_id',
            'user_id',
            'total',
            'timestamp',
            'status',
            'date',
            'status_info',
            'firstname',
            'lastname',
            'email',
            'payment_name',
            'shippings'
        );

        return Api::getAsList('orders', $orders, $fields);
    }

    /**
     * Get order data
     */
    public static function getOrderInfo($order_id)
    {
        $object = fn_get_order_info($order_id, false, true, true);
        $object['date'] = fn_twg_format_time($object['timestamp']);
        $status_data = fn_get_status_data($object['status'], STATUSES_ORDER);
        if (AREA == 'C') {
            $object['status'] = empty($status_data['description']) ? '' : $status_data['description'];
        }

        $object['shipping'] =
            array_values(
                isset($object['shipping']) ? $object['shipping'] : array()
            );
        $object['taxes'] = array_values($object['taxes']);
        $object['items'] = self::setProductsPointsInfo(
            array(
                'products' => array_values($object['products'])
            )
        );
        unset($object['products']);
        foreach ($object['items'] as &$product) {
            if (!empty($product['extra']['points_info']) && Registry::get('addons.reward_points.status') != 'A') {
                unset($product['extra']['points_info']);
            }
        }

        if (Registry::get('settings.General.use_shipments') == 'Y') {
            $shipments = db_get_array('SELECT ?:shipments.shipment_id, ?:shipments.comments, ?:shipments.tracking_number, ?:shipping_descriptions.shipping AS shipping, ?:shipments.carrier FROM ?:shipments LEFT JOIN ?:shipment_items ON (?:shipments.shipment_id = ?:shipment_items.shipment_id) LEFT JOIN ?:shipping_descriptions ON (?:shipments.shipping_id = ?:shipping_descriptions.shipping_id) WHERE ?:shipment_items.order_id = ?i AND ?:shipping_descriptions.lang_code = ?s GROUP BY ?:shipments.shipment_id', $order_id, DESCR_SL);
            if (!empty($shipments)) {
                foreach ($shipments as $id => $shipment) {
                    $shipments[$id]['items'] = db_get_array('SELECT item_id, amount FROM ?:shipment_items WHERE shipment_id = ?i', $shipment['shipment_id']);
                }
            }
            $object['shipments'] = $shipments;
        }
        return $object;
    }

    private static function setProductsPointsInfo($params)
    {
        if (empty($params['products'])) {
            return array();
        }
        foreach ($params['products'] as &$product) {
            if (!empty($product['extra']['points_info'])) {
                if (Registry::get('addons.reward_points.status') == 'A') {
                    $product['points_info'] = $product['extra']['points_info'];
                }
                unset($product['extra']['points_info']);
            }
        }
        return $params['products'];
    }

    /**
     * Return order/orders info after the order placing
     * @param int   $order_id
     * @param array $response
     */
    public static function returnPlacedOrders(
        $order_id,
        &$response,
        $items_per_page,
        $lang_code
    ) {
        $order = self::getOrderInfo($order_id);

        $_error = false;

        $status = db_get_field('SELECT status FROM ?:orders WHERE order_id=?i', $order_id);

        if ($status == STATUS_PARENT_ORDER) {
            $child_orders = db_get_hash_single_array(
                "SELECT order_id, status
                 FROM ?:orders
                 WHERE parent_order_id = ?i",
                array('order_id', 'status'),
                $order_id
            );
            $status = reset($child_orders);
            $order['child_orders'] = array_keys($child_orders);
        }

        if (!substr_count('OP', $status) > 0) {
            $_error = true;
            if ($status != 'B') {
                if (!empty($child_orders)) {
                    array_unshift($child_orders, $order_id);
                } else {
                    $child_orders = array();
                    $child_orders[] = $order_id;
                }
                $order_id_field = $status == 'N' ? 'processed_order_id' : 'failed_order_id';
                $_SESSION['cart'][$order_id_field] = $child_orders;

                $cart = &$_SESSION['cart'];
                if (!empty($cart['failed_order_id'])) {
                    $_ids =
                        !empty($cart['failed_order_id']) ?
                            $cart['failed_order_id'] :
                            $cart['processed_order_id'];
                    $_order_id = reset($_ids);
                    $_payment_info = db_get_field(
                        "SELECT data
                         FROM ?:order_data
                         WHERE order_id = ?i AND type = 'P'",
                        $_order_id
                    );
                    if (!empty($_payment_info)) {
                        $_payment_info = unserialize(fn_decrypt_text($_payment_info));
                    }
                    $_msg =
                        !empty($_payment_info['reason_text']) ?
                            $_payment_info['reason_text'] :
                            '';
                    $_msg .=
                        empty($_msg) ?
                            __('text_order_placed_error') :
                            '';
                    $response->addError(
                        'ERROR_FAIL_POST_ORDER',
                        $_msg
                    );
                    $cart['processed_order_id'] = $cart['failed_order_id'];
                    unset($cart['failed_order_id']);
                } elseif (
                    !fn_twg_set_internal_errors(
                        $response,
                        'ERROR_FAIL_POST_ORDER'
                    )
                ) {
                    $response->addError(
                        'ERROR_FAIL_POST_ORDER',
                        __('fail_post_order', $lang_code)
                    );
                }
            } else {
                if (!fn_twg_set_internal_errors($response, 'ERROR_ORDER_BACKORDERED')) {
                    $response->addError(
                        'ERROR_ORDER_BACKORDERED',
                        __('text_order_backordered', $lang_code)
                    );
                }
            }
            $response->returnResponse();
        }

        if (empty($order['child_orders'])) {
            $response->setData($order);
        } else {
            $params = array();
            if (empty($_SESSION['auth']['user_id'])) {
                $params['order_id'] = $_SESSION['auth']['order_ids'];
            } else {
                $params['user_id'] = $_SESSION['auth']['user_id'];
            }
            list($orders,,$totals) = fn_get_orders(
                $params,
                $items_per_page,
                true
            );
            $response->setMeta(
                !empty($totals['gross_total']) ? $totals['gross_total'] : 0,
                'gross_total'
            );
            $response->setMeta(
                !empty($totals['totally_paid']) ? $totals['totally_paid'] : 0,
                'totally_paid'
            );
            $response->setMeta($order, 'order');
            $response->setResponseList(
                TwigmoOrder::getOrdersAsApiList($orders, $lang_code)
            );
            $pagination_params = array(
                'items_per_page' => !empty($items_per_page)? $items_per_page : TWG_RESPONSE_ITEMS_LIMIT,
                'page' => !empty($_REQUEST['page'])? $_REQUEST['page'] : 1
            );
            fn_twg_set_response_pagination($response, $pagination_params);
        }
    }

    /**
     * Check if a user have an access to an order
     * @param array $response
     * @param array $auth
     */
    public static function checkIfOrderAllowed($order_id, &$_auth, &$response)
    {
        $allow = true;
        // If user is not logged in and trying to see the order, redirect him to login form
        if (empty($_auth['user_id']) && empty($_auth['order_ids'])) {
            $response->addError('ERROR_ACCESS_DENIED', __('access_denied'));
            $response->returnResponse();
            $allow = false;
        }

        $allowed_id = 0;

        if (!empty($_auth['user_id'])) {
            $allowed_id = db_get_field(
                "SELECT user_id
                 FROM ?:orders
                 WHERE user_id = ?i AND order_id = ?i",
                $_auth['user_id'],
                $order_id
            );
        } elseif (!empty($_auth['order_ids'])) {
            $allowed_id = in_array($order_id, $_auth['order_ids']);
        }

        // Check order status (incompleted order)
        if (!empty($allowed_id)) {
            $status = db_get_field(
                'SELECT status
                 FROM ?:orders
                 WHERE order_id = ?i',
                $order_id
            );
            if ($status == STATUS_INCOMPLETED_ORDER) {
                $allowed_id = 0;
            }
        }
        fn_set_hook('is_order_allowed', $order_id, $allowed_id);

        if (empty($allowed_id)) { // Access denied
            $response->addError(
                'ERROR_ACCESS_DENIED',
                __('access_denied')
            );
            $response->returnResponse();
            $allow = false;
        }

        return $allow;
    }

    public function orderPlacementRoutines(
        $order_id,
        $force_notification = array(),
        $clear_cart = true,
        $action = ''
    ) {
        // don't show notifications
        // only clear cart
        $order_info = fn_get_order_info($order_id, true);
        $display_notification = true;

        fn_set_hook(
            'placement_routines',
            $order_id,
            $order_info,
            $force_notification,
            $clear_cart,
            $action,
            $display_notification
        );

        if (!empty($_SESSION['cart']['placement_action'])) {
            if (empty($action)) {
                $action = $_SESSION['cart']['placement_action'];
            }
            unset($_SESSION['cart']['placement_action']);
        }

        if (AREA == 'C' && !empty($order_info['user_id'])) {
            $__fake = '';
            fn_save_cart_content($__fake, $order_info['user_id']);
        }

        $edp_data = fn_generate_ekeys_for_edp(array(), $order_info);
        fn_order_notification($order_info, $edp_data, $force_notification);

        // Empty cart
        if ($clear_cart == true && (substr_count('OPT', $order_info['status']) > 0)) {
            $_SESSION['cart'] = array(
                'user_data' => !empty($_SESSION['cart']['user_data'])?
                        $_SESSION['cart']['user_data']:
                        array(),
                'profile_id' => !empty($_SESSION['cart']['profile_id'])?
                        $_SESSION['cart']['profile_id']:
                        0,
                'user_id' => !empty($_SESSION['cart']['user_id'])?
                        $_SESSION['cart']['user_id']:
                        0,
            );

            db_query(
                'DELETE FROM ?:user_session_products WHERE session_id = ?s AND type = ?s',
                Session::getId(),
                'C'
            );
        }

        $is_twg_hook = true;
        $_error = false;
        fn_set_hook(
            'order_placement_routines',
            $order_id,
            $force_notification,
            $order_info,
            $_error,
            $is_twg_hook
        );
    }
}
