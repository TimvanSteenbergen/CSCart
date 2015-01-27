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

namespace Tygh\Api\Entities;

use Tygh\Api\AEntity;
use Tygh\Api\Response;
use Tygh\Registry;

class Orders extends AEntity
{
    public function index($id = 0, $params = array())
    {
        if (!empty($id)) {
            $data = fn_get_order_info($id, false, false);

            if (empty($data)) {
                $status = Response::STATUS_NOT_FOUND;
            } else {
                $status = Response::STATUS_OK;
            }

        } else {
            $items_per_page = $this->safeGet($params, 'items_per_page', Registry::get('settings.Appearance.admin_products_per_page'));
            list($data, $params) =  fn_get_orders($params, $items_per_page);
            $data = array(
                'orders' => $data,
                'params' => $params,
            );
            $status = Response::STATUS_OK;
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function create($params)
    {
        $data = array();
        $valid_params = true;
        $status = Response::STATUS_BAD_REQUEST;

        fn_clear_cart($cart, true);
        if (!empty($params['user_id'])) {
            $cart['user_data'] = fn_get_user_info($params['user_id']);
        } elseif (!empty($params['user_data'])) {
            $cart['user_data'] = $params['user_data'];
        }
        $cart['user_data'] = array_merge($cart['user_data'], $params);

        if (empty($params['user_id']) && empty($params['user_data'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'user_id/user_data'
            ));
            $valid_params = false;

        } elseif (empty($params['payment_id'])) {
            $data['message'] = __('api_required_field', array(
                '[field]' => 'payment_id'
            ));
            $valid_params = false;

        }

        if ($valid_params) {

            $cart['payment_id'] = $params['payment_id'];

            $customer_auth = fn_fill_auth($cart['user_data']);

            fn_add_product_to_cart($params['products'], $cart, $customer_auth);
            fn_calculate_cart_content($cart, $customer_auth);

            if (!empty($cart['product_groups']) && !empty($params['shipping_ids'])) {
                foreach ($cart['product_groups'] as $key => $group) {
                    foreach ($group['shippings'] as $shipping_id => $shipping) {
                        if ($params['shipping_ids'] == $shipping['shipping_id']) {
                            $cart['chosen_shipping'][$key] = $shipping_id;
                            break;
                        }
                    }
                }
            }

            $cart['calculate_shipping'] = true;
            fn_calculate_cart_content($cart, $customer_auth);

            if (empty($cart['shipping_failed']) || empty($params['shipping_ids'])) {

                fn_update_payment_surcharge($cart, $customer_auth);

                list($order_id, ) = fn_place_order($cart, $customer_auth, 'save', $this->auth['user_id']);

                if (!empty($order_id)) {
                    $status = Response::STATUS_CREATED;
                    $data = array(
                        'order_id' => $order_id,
                    );
                }
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function update($id, $params)
    {
        fn_define('ORDER_MANAGEMENT', true);
        $data = array();
        $valid_params = true;
        $status = Response::STATUS_BAD_REQUEST;

        if ($valid_params) {

            fn_clear_cart($cart, true);
            $customer_auth = fn_fill_auth(array(), array(), false, 'C');
            $cart_status = md5(serialize($cart));

            // Order info was not found or customer does not have enought permissions
            if (fn_form_cart($id, $cart, $customer_auth) && $cart_status != md5(serialize($cart))) {

                unset($params['product_groups']);

                if (empty($params['shipping_id'])) {
                    $shipping = reset($cart['shipping']);
                    if (!empty($shipping['shipping_id'])) {
                        $params['shipping_id'] = $shipping['shipping_id'];
                    }
                }

                $cart['order_id'] = $id;

                fn_calculate_cart_content($cart, $customer_auth);

                if (!empty($params['user_id'])) {
                    $cart['user_data'] = fn_get_user_info($params['user_id']);
                } elseif (!empty($params)) {
                    $cart['user_data'] = array_merge($cart['user_data'], $params);
                }

                if (!empty($cart['product_groups']) && !empty($params['shipping_id'])) {
                    foreach ($cart['product_groups'] as $key => $group) {
                        foreach ($group['shippings'] as $shipping_id => $shipping) {
                            if ($params['shipping_id'] == $shipping['shipping_id']) {
                                $cart['chosen_shipping'][$key] = $shipping_id;
                                break;
                            }
                        }
                    }
                }

                if (!empty($params['payment_id'])) {

                    if (!empty($params['payment_info'])) {
                        $cart['payment_info'] = $params['payment_info'];
                    } elseif ($params['payment_id'] != $cart['payment_id']) {
                        $cart['payment_info'] = array();
                    }

                    $cart['payment_id'] = $params['payment_id'];
                }

                if (!empty($params['products'])) {
                    $cart['products'] = $params['products'];
                }

                fn_calculate_cart_content($cart, $customer_auth);

                if (!empty($cart) && empty($cart['shipping_failed'])) {

                    $cart['parent_order_id'] = 0;
                    fn_update_payment_surcharge($cart, $customer_auth);

                    list($order_id, ) = fn_update_order($cart, $id);

                    if ($order_id) {

                        if (!empty($params['status'])) {
                            fn_change_order_status($order_id, $params['status'], '', fn_get_notification_rules($params, false));
                        }

                        $status = Response::STATUS_OK;
                        $data = array(
                            'order_id' => $order_id
                        );
                    }
                }
            }
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function delete($id)
    {
        $data = array();
        $status = Response::STATUS_NOT_FOUND;

        if (fn_delete_order($id)) {
           $status = Response::STATUS_OK;
            $data['message'] = 'Ok';
        }

        return array(
            'status' => $status,
            'data' => $data
        );
    }

    public function privileges()
    {
        return array(
            'create' => 'create_order',
            'update' => 'edit_order',
            'delete' => 'delete_orders',
            'index'  => 'view_orders'
        );
    }
}
