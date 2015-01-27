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

function fn_required_products_get_products(&$params, &$fields, &$sortings, &$condition, &$join, &$sorting, &$group_by)
{
    if (!empty($params['for_required_product'])) {
        $join .= " LEFT JOIN ?:product_required_products ON products.product_id = ?:product_required_products.required_id";
        $condition .= db_quote(" AND ?:product_required_products.product_id = ?i", $params['for_required_product']);
    }

}

function fn_required_products_get_product_data_post(&$product, &$auth)
{
    if (!empty($product['product_id'])) {

        list($required) = fn_get_products(array('for_required_product' => $product['product_id']));

        if (count($required)) {
            $product['have_required'] = 'Y';

            $ids = array ();
            foreach ($required as $entry) {
                $ids[] = $entry['product_id'];
            }

            $have = fn_required_products_get_existent($auth, $ids, false);

            $product['required_products'] = array ();

            fn_gather_additional_products_data($required, array('get_icon' => true, 'get_detailed' => true, 'get_options' => true, 'get_discounts' => true));

            foreach ($required as $entry) {
                $id = $entry['product_id'];

                $product['required_products'][$id] = $entry;
                $product['required_products'][$id]['bought'] = ($have && in_array($id, $have)) ? 'Y' : 'N';
            }

            if (!empty($have) && count($have) >= count($ids)) {
                $product['can_add_to_cart'] = 'Y';
            } else {
                $product['can_add_to_cart'] = 'N';
            }
        } else {
            $product['have_required'] = 'N';
        }
    }
}

function fn_required_products_in_cart($auth, $ids)
{
    $data = array ();

    if (!empty($_SESSION['cart']) && !empty($_SESSION['cart']['products'])) {
        foreach ($ids as $id) {
            foreach ($_SESSION['cart']['products'] as $entry) {
                if ($entry['product_id'] == $id) {
                    $data[] = $id;
                }
            }
        }
    }

    $data = array_unique($data);

    return $data;
}

function fn_required_products_get_existent($auth, $ids, $look_in_cart = true)
{
    if (empty($ids)) {
        return false;
    }

    if (!empty($auth['user_id'])) {
        $data = db_get_fields('SELECT ?:order_details.product_id FROM ?:orders LEFT JOIN ?:order_details ON ?:orders.order_id = ?:order_details.order_id WHERE ?:orders.status IN (?a) AND ?:orders.user_id = ?i AND ?:order_details.product_id IN (?n) GROUP BY ?:order_details.product_id', array ('P', 'C'), $auth['user_id'], $ids);
    } else {
        $data = array();
    }

    if ($look_in_cart) {
        $data = array_merge($data, fn_required_products_in_cart($auth, $ids));
        $data = array_unique($data);
    }

    return $data;
}

/**
 * Gets required products for products
 *
 * @param int $product_id Product identifier
 * @return array Reuired products identifiers
 */
function fn_get_required_products_ids($product_id)
{
    $join = db_quote(' LEFT JOIN ?:products ON req_prod.required_id = ?:products.product_id');
    $condition = db_quote(' req_prod.product_id = ?i AND ?:products.status != ?s', $product_id, 'D');

    if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
        $join .= db_quote(' LEFT JOIN ?:products_categories ON req_prod.required_id = ?:products_categories.product_id');
        $join .= db_quote(' LEFT JOIN ?:categories ON ?:products_categories.category_id = ?:categories.category_id');
        $condition .= fn_get_company_condition('?:categories.company_id');
    }

    $ids = db_get_fields("SELECT req_prod.required_id FROM ?:product_required_products as req_prod $join WHERE $condition GROUP BY req_prod.required_id");

    return $ids;
}

/**
 * Checks product list and adds required products
 *
 * @param array $product_data Products data
 * @param mixed $auth Array with authorization data
 * @param array $cart
 * @param array $added_products Products that are checked for further products that need to be added
 * @return bool False if some products were removed, otherwise - true
 */
function fn_check_added_required_products(&$product_data, $auth, &$cart, $added_products = array())
{
    $result = true;

    foreach ($product_data as $key => $entry) {
        if (!empty($entry['amount']) && !empty($key)) {
            $product_id = !empty($entry['product_id']) ? $entry['product_id'] : $key;
            $added_products[$product_id] = $entry;

            $ids = fn_get_required_products_ids($product_id);

            if (!empty($ids)) {
                $have = fn_required_products_get_existent($auth, $ids);

                if (empty($have) || count($have) != count($ids)) {
                    $products_to_cart = array_diff($ids, $have);
                    $out_of_stock = array();
                    $check_products = array();
                    foreach ($products_to_cart as $id) {
                        if (!empty($added_products[$id])) {
                            continue;
                        }

                        $amount = fn_check_amount_in_stock($id, 1, fn_get_default_product_options($id), 0, 'N', 0, $cart);

                        if (!$amount) {
                            $out_of_stock[] = $id;
                        } else {
                            $check_products[$id] = array('product_id' => $id, 'amount' => $amount);
                        }
                    }

                    if (empty($out_of_stock) && fn_check_added_required_products($check_products, $auth, $cart, $added_products)) {
                        $cart['change_cart_products'] = true;
                        $msg = __('required_products_added');

                        foreach ($check_products as $id => $v) {
                            if (empty($added_products[$id])) {
                                $added_products[$id] = $v;
                                $product_data[$id] = $v;
                                $msg .= "<br />" . fn_get_product_name($id);

                                $cart['amount'] = !isset($cart['amount']) ? $v['amount'] : $cart['amount'] + $v['amount'];
                            }
                        }
                    } else {
                        unset($product_data[$key]);
                        unset($added_products[$product_id]);
                        $result = false;
                        $msg = __('required_products_out_of_stock');
                        foreach ($out_of_stock as $id) {
                            $msg .= "<br />" . fn_get_product_name($id);
                        }
                    }
                    fn_set_notification('N', __('notice'), $msg);
                }
            }
        }
    }

    return $result;
}

function fn_required_products_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    fn_check_added_required_products($product_data, $auth, $cart);

    return true;
}

/**
 * Checks if any products should be deleted from cart with the deleted cart product
 *
 * @param array $cart Array of the cart contents and user information necessary for purchase
 * @param int $cart_id Deleted product identifier
 * @return bool Always true
 */
function fn_check_deleted_required_products(&$cart, $cart_id)
{
    $auth = !empty($_SESSION['auth']['user_id']) ? $_SESSION['auth']['user_id'] : array();

    if (!empty($cart_id) && !empty($cart['products'][$cart_id])) {
        $product_id = $cart['products'][$cart_id]['product_id'];

        $products = db_get_fields('SELECT product_id FROM ?:product_required_products WHERE required_id = ?i', $product_id);

        if (count($products)) {
            foreach ($cart['products'] as $key => $product) {
                if (in_array($product['product_id'], $products)) {
                    $haved = fn_required_products_get_existent($auth, array($product_id), false);

                    if (!empty($haved)) {
                        fn_check_deleted_required_products($cart, $key);
                        unset($cart['products'][$key]);
                        foreach ($cart['product_groups'] as $key_group => $group) {
                            if (in_array($key, array_keys($group['products']))) {
                                unset($cart['product_groups'][$key_group]['products'][$key]);
                            }
                        }
                    }
                }
            }
        }
    }

    return true;
}

function fn_required_products_delete_cart_product(&$cart, &$cart_id, &$full_erase)
{
    fn_check_deleted_required_products($cart, $cart_id);

    return true;
}

function fn_required_products_delete_product_post($product_id)
{
    if (!empty($product_id)) {
        db_query('DELETE FROM ?:product_required_products WHERE product_id = ?i OR required_id = ?i', $product_id, $product_id);
    }

    return true;
}

/**
 * Checks required products on recalculation
 *
 * @param array $cart Array of the cart contents and user information necessary for purchase
 * @param array $cart_products Cart products
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return bool Always true
 */
function fn_check_calculated_required_products(&$cart, &$cart_products, $auth)
{
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $key => $entry) {
            if (!empty($entry['product_id'])) {
                $ids = fn_get_required_products_ids($entry['product_id']);

                if (!empty($ids)) {
                    $have = fn_required_products_get_existent($auth, $ids);
                    if (empty($have) || count($have) != count($ids)) {
                        if (empty($entry['extra']['parent'])) {
                            $cart['amount'] -= $entry['amount'];
                        }
                        unset($cart['products'][$key]);
                        unset($cart_products[$key]);
                        if (isset($cart['product_groups'])) {
                            foreach ($cart['product_groups'] as $key_group => $group) {
                                if (in_array($key, array_keys($group['products']))) {
                                    unset($cart['product_groups'][$key_group]['products'][$key]);
                                }
                            }
                        }
                        fn_check_calculated_required_products($cart, $cart_products, $auth);
                    }
                }
            }
        }
    }

    return true;
}

function fn_required_products_calculate_cart_items(&$cart, &$cart_products, &$auth)
{
    fn_check_calculated_required_products($cart, $cart_products, $auth);
}
