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
use Tygh\Session;
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($mode == 'm_delete') {
        if (!empty($_REQUEST['user_ids'])) {
            if (fn_allowed_for('ULTIMATE')) {
                foreach ($_REQUEST['user_ids'] as $company_id => $user_ids) {
                    fn_delete_user_cart($user_ids, $company_id);
                }
            } else {
                fn_delete_user_cart($_REQUEST['user_ids']);
            }
        }
    }

    if ($mode == 'm_delete_all') {
        if (!empty($_SESSION['abandoned_carts'])) {
            if (fn_allowed_for('ULTIMATE')) {
                foreach ($_SESSION['abandoned_carts'] as $company_id => $user_ids) {
                    fn_delete_user_cart($user_ids, $company_id);
                }
            } else {
                fn_delete_user_cart($_SESSION['abandoned_carts']);
            }
        }
    }

    return array(CONTROLLER_STATUS_OK, "cart.cart_list");
}

if ($mode == 'cart_list') {
    $item_types = fn_get_cart_content_item_types();

    list($carts_list, $search) = fn_get_carts($_REQUEST, Registry::get('settings.Appearance.admin_elements_per_page'));
    if (!empty($_REQUEST['user_id'])) {
        if (fn_allowed_for('ULTIMATE') && !empty($_REQUEST['c_company_id'])) {
            $cart_products = db_get_array(
                "SELECT ?:user_session_products.item_id, ?:user_session_products.item_type, ?:user_session_products.product_id, ?:user_session_products.amount, ?:user_session_products.price, ?:user_session_products.extra, ?:product_descriptions.product"
                . " FROM ?:user_session_products"
                . " LEFT JOIN ?:product_descriptions ON ?:user_session_products.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s"
                . " WHERE ?:user_session_products.user_id = ?i AND ?:user_session_products.company_id = ?i AND ?:user_session_products.type = 'C' AND ?:user_session_products.item_type IN (?a)",
                DESCR_SL, $_REQUEST['user_id'], $_REQUEST['c_company_id'], $item_types
            );
        } else {
            $cart_products = db_get_array(
                "SELECT ?:user_session_products.item_id, ?:user_session_products.item_type, ?:user_session_products.product_id, ?:user_session_products.amount, ?:user_session_products.price, ?:user_session_products.extra, ?:product_descriptions.product"
                . " FROM ?:user_session_products"
                . " LEFT JOIN ?:product_descriptions ON ?:user_session_products.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s"
                . " WHERE ?:user_session_products.user_id = ?i AND ?:user_session_products.type = 'C' AND ?:user_session_products.item_type IN (?a)",
                DESCR_SL, $_REQUEST['user_id'], $item_types
            );
        }

        if (!empty($cart_products)) {
            foreach ($cart_products as $key => $product) {
                $cart_products[$key]['extra'] = unserialize($product['extra']);
            }
        }
        Registry::get('view')->assign('cart_products', $cart_products);
        Registry::get('view')->assign('sl_user_id', $_REQUEST['user_id']);
    }
    if (!empty($carts_list) && is_array($carts_list)) {
        $all_cart_products = array();
        if (fn_allowed_for('ULTIMATE')) {
            foreach ($carts_list as $key => $cart_data) {
                $all_cart_products[$key] = db_get_row(
                    "SELECT SUM(amount) as count, SUM(amount) as sum, SUM(amount * price) as total, ip_address"
                    . " FROM ?:user_session_products"
                    . " WHERE user_id = ?i AND company_id = ?i AND item_type IN (?a) AND type = 'C'"
                    . " GROUP BY ?:user_session_products.user_id, ?:user_session_products.company_id",
                    $cart_data['user_id'], $cart_data['company_id'], $item_types
                );
                if (!empty($all_cart_products[$key])) {
                    $carts_list[$key]['cart_products'] = $all_cart_products[$key]['count'];
                    $carts_list[$key]['cart_all_products'] = $all_cart_products[$key]['sum'];
                    $carts_list[$key]['total'] = $all_cart_products[$key]['total'];
                    $carts_list[$key]['user_data'] = fn_get_user_info($cart_data['user_id'], true);
                    $carts_list[$key]['ip_address'] = $all_cart_products[$key]['ip_address'];
                }
                $_SESSION['abandoned_carts'][$cart_data['company_id']][] = $cart_data['user_id'];
            }
        } else {
            foreach ($carts_list as $key => $cart_data) {
                $all_cart_products[$key] = db_get_row(
                    "SELECT SUM(amount) as count, SUM(amount) as sum, SUM(amount * price) as total, ip_address"
                    . " FROM ?:user_session_products"
                    . " WHERE user_id = ?i AND item_type IN (?a) AND type = 'C'"
                    . " GROUP BY ?:user_session_products.user_id",
                    $cart_data['user_id'], $item_types
                );
                if (!empty($all_cart_products[$key])) {
                    $carts_list[$key]['cart_products'] = $all_cart_products[$key]['count'];
                    $carts_list[$key]['cart_all_products'] = $all_cart_products[$key]['sum'];
                    $carts_list[$key]['total'] = $all_cart_products[$key]['total'];
                    $carts_list[$key]['user_data'] = fn_get_user_info($cart_data['user_id'], true);
                    $carts_list[$key]['ip_address'] = $all_cart_products[$key]['ip_address'];
                }
                $_SESSION['abandoned_carts'][] = $cart_data['user_id'];
            }
        }
    }

    Registry::get('view')->assign('carts_list', $carts_list);
    Registry::get('view')->assign('search', $search);
}

function fn_delete_user_cart($user_ids, $data = '')
{
    $condition = db_quote(' AND user_id IN (?a)', $user_ids);

    fn_set_hook('delete_user_cart', $user_ids, $condition, $data);

    db_query("DELETE FROM ?:user_session_products WHERE 1 $condition");

    return true;
}

function fn_get_carts($params, $items_per_page = 0)
{
    // Init filter
    $params = LastView::instance()->update('carts', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        '?:user_session_products.user_id',
        '?:users.firstname',
        '?:users.lastname',
        '?:user_session_products.timestamp AS date',
    );

    // Define sort fields
    $sortings = array (
        'customer' => "CONCAT(?:users.lastname, ?:users.firstname)",
        'date' => "?:user_session_products.timestamp",
    );

    if (fn_allowed_for('ULTIMATE')) {
        $sortings['company_id'] = "?:user_session_products.company_id";
    }

    $sorting = db_sort($params, $sortings, 'customer', 'asc');

    $condition = $join = '';

    $group = " GROUP BY ?:user_session_products.user_id";
    $group_post = '';
    if (isset($params['cname']) && fn_string_not_empty($params['cname'])) {
        $arr = fn_explode(' ', $params['cname']);
        foreach ($arr as $k => $v) {
            if (!fn_string_not_empty($v)) {
                unset($arr[$k]);
            }
        }
        if (sizeof($arr) == 2) {
            $condition .= db_quote(" AND ?:users.firstname LIKE ?l AND ?:users.lastname LIKE ?l", "%".array_shift($arr)."%", "%".array_shift($arr)."%");
        } else {
            $condition .= db_quote(" AND (?:users.firstname LIKE ?l OR ?:users.lastname LIKE ?l)", "%".trim($params['cname'])."%", "%".trim($params['cname'])."%");
        }
    }

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $condition .= db_quote(" AND ?:users.email LIKE ?l", "%".trim($params['email'])."%");
    }

    if (!empty($params['user_id'])) {
        $condition .= db_quote(" AND ?:user_session_products.user_id = ?i", $params['user_id']);
    }

    if (!empty($params['online_only'])) {
        $sessions = Session::getOnline('C');
        if (!empty($sessions)) {
            $condition .= db_quote(" AND ?:user_session_products.session_id IN (?a)", $sessions);    
        } else {
            $condition .= db_quote(" AND 0");
        }
    }

    if (!empty($params['with_info_only'])) {
        $condition .= db_quote(" AND ?:users.email != ''");
    }

    if (!empty($params['users_type'])) {
        if ($params['users_type'] == 'R') {
            $condition .= db_quote(" AND !ISNULL(?:users.user_id)");
        } elseif ($params['users_type'] == 'G') {
            $condition .= db_quote(" AND ISNULL(?:users.user_id)");
        }
    }

    if (!empty($params['total_from']) || !empty($params['total_to'])) {
        $having = '';
        if (fn_is_numeric($params['total_from'])) {
            $having .= db_quote(" AND SUM(price * amount) >= ?d", $params['total_from']);
        }

        if (fn_is_numeric($params['total_to'])) {
            $having .= db_quote(" AND SUM(price * amount) <= ?d", $params['total_to']);
        }

        if (!empty($having)) {
            $users4total = db_get_fields("SELECT user_id FROM ?:user_session_products GROUP BY user_id HAVING 1 $having");
            if (!empty($users4total)) {
                $condition .= db_quote(" AND (?:user_session_products.user_id IN (?n))", $users4total);
            } else {
                $condition .= " AND (?:user_session_products.user_id = 'no')";
            }
        }
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);
        $condition .= db_quote(" AND (?:user_session_products.timestamp >= ?i AND ?:user_session_products.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $_condition = array();
    if (!empty($params['product_type_c'])) {
        $_condition[] = "?:user_session_products.type = 'C'";
    }
    if (!empty($params['product_type_w']) && $params['product_type_w'] == 'Y') {
        $_condition[] = "?:user_session_products.type = 'W'";
    }

    if (!empty($_condition)) {
        $condition .= " AND (" . implode(" OR ", $_condition).")";
    }

    if (!empty($params['p_ids']) || !empty($params['product_view_id'])) {
        $arr = (strpos($params['p_ids'], ',') !== false || !is_array($params['p_ids'])) ? explode(',', $params['p_ids']) : $params['p_ids'];
        if (empty($params['product_view_id'])) {
            $condition .= db_quote(" AND ?:user_session_products.product_id IN (?n)", $arr);
        } else {
            $condition .= db_quote(" AND ?:user_session_products.product_id IN (?n)", db_get_fields(fn_get_products(array('view_id' => $params['product_view_id'], 'get_query' => true))));
        }

        $group_post .=  " HAVING COUNT(?:user_session_products.user_id) >= " . count($arr);
    }

    $join .= " LEFT JOIN ?:users ON ?:user_session_products.user_id = ?:users.user_id";

    // checking types for retrieving from the database
    $type_restrictions = array('C');
    fn_set_hook('get_carts', $type_restrictions, $params, $condition, $join, $fields, $group, $array_index_field);

    if (!empty($type_restrictions) && is_array($type_restrictions)) {
        $condition .= " AND ?:user_session_products.type IN ('" . implode("', '", $type_restrictions) . "')";
    }

    $carts_list = array();

    $group .= $group_post;

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    if (fn_allowed_for('ULTIMATE')) {
        $group = " GROUP BY ?:user_session_products.user_id, ?:user_session_products.company_id";
    }

    $carts_list = db_get_array("SELECT SQL_CALC_FOUND_ROWS " . implode(', ', $fields) . " FROM ?:user_session_products $join WHERE 1 $condition $group $sorting $limit");

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_found_rows();
    }

    unset($_SESSION['abandoned_carts']);

    return array($carts_list, $params);
}
