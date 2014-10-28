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
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

function fn_get_gift_certificate_company_condition($field)
{
    if (fn_allowed_for('ULTIMATE')) {
        return fn_get_company_condition($field);
    }

    return '';
}

function fn_change_gift_certificate_status($gift_cert_id, $status_to, $status_from = '', $force_notification = array())
{
    if (empty($gift_cert_id)) {
        return false;
    }
    $gift_cert_data = fn_get_gift_certificate_info($gift_cert_id, 'B');

    if (empty($status_from)) {
        $status_from = $gift_cert_data['status'];
    }

    if (empty($status_to) || $status_from == $status_to) {
        return false;
    }
    $result = db_query('UPDATE ?:gift_certificates SET ?u WHERE gift_cert_id = ?i', array('status' => $status_to), $gift_cert_id);

    if ($result) {
        $gift_cert_data['status'] = $status_to;
        fn_gift_certificate_notification($gift_cert_data, $force_notification);
    }

    return $result;
}

function fn_add_gift_certificate_log_record($gift_cert_id, $before_info, $after_info, $order_id = 0)
{
    $auth = & $_SESSION['auth'];

    $_data = array(
        'area' => AREA,
        'timestamp' => TIME,
        'user_id' => $auth['user_id'],
        'gift_cert_id' => $gift_cert_id,
        'amount' => $before_info['amount'],
        'products' => $before_info['products'],
        'debit' => $after_info['amount'],
        'debit_products' => $after_info['products'],
        'order_id' => $order_id
    );

    return db_query("REPLACE INTO ?:gift_certificates_log ?e", $_data);

}

function fn_prepare_gift_certificate_log($fields, $data)
{
    $fields_array = explode(',', $fields);
    array_walk($fields_array, 'fn_trim_helper');
    $result = array();

    foreach ((array) $fields_array as $field) {
        $result[$field] = '';
        if (!empty($field) && !empty($data) && !empty($data[$field]) && Registry::get('addons.gift_certificates.free_products_allow') == 'Y') {
            $result[$field] = unserialize($data[$field]);
            if (is_array($result[$field])) {
                foreach ($result[$field] as $val) {
                    fn_fill_certificate_products($result[$field]);
                }
            }
        }
    }

    return $result;
}

function fn_get_gift_certificate_log($params, $items_per_page = 0)
{
    if (empty($params['gift_cert_id'])) {
        return array(array(), $params);
    }

    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $sortings = array (
        'log_id' => "?:gift_certificates_log.log_id",
        'timestamp' => "?:gift_certificates_log.timestamp",
        'amount'    => "?:gift_certificates_log.amount",
        'debit'     => "?:gift_certificates_log.debit",
        'username'  => "?:users.user_login",
        'name'      => "?:users.lastname",
        'email'     => "?:users.email",
        'order_id'  => "?:gift_certificates_log.order_id",
    );

    $sorting = db_sort($params, $sortings, 'log_id', 'desc');

    $q_fields = array (
        "?:gift_certificates_log.*",
        "?:users.user_login",
        "?:users.email",
        "?:users.firstname",
        "?:users.lastname",
        "?:orders.email as order_email",
        "?:orders.firstname as order_firstname",
        "?:orders.lastname as order_lastname",
    );

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:gift_certificates_log WHERE gift_cert_id = ?i", $params['gift_cert_id']);
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $log  = db_get_array("SELECT " . implode(',', $q_fields) . " FROM ?:gift_certificates_log LEFT JOIN ?:users ON ?:users.user_id = ?:gift_certificates_log.user_id LEFT JOIN ?:orders ON ?:orders.order_id = ?:gift_certificates_log.order_id WHERE gift_cert_id = ?i $sorting $limit", $params['gift_cert_id']);

    foreach ($log as $k => $v) {
        $prepared_log = fn_prepare_gift_certificate_log('products, debit_products', $v);
        $log[$k] = fn_array_merge($v, $prepared_log);
    }

    return array($log, $params);
}

function fn_get_gift_certificate_name($cert_id)
{
    if (!empty($cert_id)) {
        return db_get_field("SELECT gift_cert_code FROM ?:gift_certificates WHERE gift_cert_id = ?i", $cert_id);
    }

    return false;
}

function fn_get_gift_certificate_info($certificate, $type = 'B', $stored_products = array(), $lang_code = CART_LANGUAGE)
{
    if ($type == 'B' && is_numeric($certificate)) {
        $_certificate = db_get_row("SELECT * FROM ?:gift_certificates WHERE gift_cert_id = ?i", $certificate);
    } elseif ($type == 'C' && is_numeric($certificate)) {
        $_certificate = fn_array_merge($_SESSION['cart']['gift_certificates'][$certificate], array('gift_cert_cart_id' => $certificate));
    } elseif ($type == 'P' && is_array($certificate)) {
        $_certificate = $certificate;
        if (empty($_certificate['gift_cert_code'])) {
            $_certificate['gift_cert_code'] = preg_replace('/[0-9A-Z]/', 'X', fn_generate_gift_certificate_code());
        }
    }

    fn_set_hook('get_gift_certificate_info', $_certificate, $certificate, $type);
    if (!empty($_certificate)) {
        //Prepare descriptions
        if (!empty($_certificate['state'])) {
            $_descr_state = fn_get_state_name($_certificate['state'], $_certificate['country'], $lang_code);
            $_certificate['descr_state'] = (empty($_descr_state)) ? $_certificate['state'] : $_descr_state;
        }
        if (!empty($_certificate['country'])) {
            $_certificate['descr_country'] = fn_get_country_name($_certificate['country'], $lang_code);
        }
        if (!empty($_certificate['products'])) {
            if ($type == 'B') {
                $_certificate['products'] = @unserialize($_certificate['products']);
            }
            fn_fill_certificate_products($_certificate['products'], $stored_products, $lang_code);
        }
        if (!empty($_certificate['debit_products'])) {
            if ($type == 'B') {
                $_certificate['debit_products'] = @unserialize($_certificate['debit_products']);
            }
            fn_fill_certificate_products($_certificate['debit_products'], $stored_products, $lang_code);
        }
    }

    return $_certificate;
}

function fn_fill_certificate_products(&$certificate_products, $stored_products = array(), $lang_code = CART_LANGUAGE)
{
    if (!empty($certificate_products)) {
        foreach ($certificate_products as $cp_id => $cp) {
            if (!empty($stored_products)) {
                $ok = false;
                foreach ($stored_products as $sp_id => $sp) {
                    if ($cp['product_id'] == $sp['product_id']) {
                        if ((!empty($cp['product_options']) && !empty($sp['extra']['product_options']) && array_diff($cp['product_options'], $sp['extra']['product_options']) == array()) || (empty($cp['product_options']) && empty($sp['extra']['product_options']))) {
                            if (!empty($sp['extra']['product_options_value'])) {
                                $certificate_products[$cp_id]['product_options_value'] = $sp['extra']['product_options_value'];
                            }
                            if (!empty($sp['extra']['product'])) {
                                $certificate_products[$cp_id]['product'] = $sp['extra']['product'];
                            }
                            break;
                        }
                    }
                }
            }
            if (empty($certificate_products[$cp_id]['product_options_value']) && !empty($cp['product_options'])) {
                $certificate_products[$cp_id]['product_options_value'] = fn_get_selected_product_options_info($cp['product_options']);
            }
            if (empty($certificate_products[$cp_id]['product'])) {
                $certificate_products[$cp_id]['product'] = fn_get_product_name($cp['product_id'], $lang_code);
            }
        }
    }
}

function fn_delete_gift_certificate($gift_cert_id, $extra = array())
{
    if (!empty($gift_cert_id) && fn_check_company_id('gift_certificates', 'gift_cert_id', $gift_cert_id)) {
        $gift_data = db_get_row("SELECT gift_cert_code, order_ids FROM ?:gift_certificates WHERE gift_cert_id = ?i", $gift_cert_id);

        if (!empty($gift_data['order_ids'])) {
            fn_set_notification('W', __('warning'), __('text_gift_cert_cannot_delete', array(
                '[code]' => $gift_data['gift_cert_code'],
                '[ids]' => $gift_data['order_ids']
            )));

            return false;
        }

        db_query("DELETE FROM ?:gift_certificates WHERE gift_cert_id = ?i", $gift_cert_id);
        db_query("DELETE FROM ?:gift_certificates_log WHERE gift_cert_id = ?i", $gift_cert_id);

        fn_set_hook('delete_gift_certificate', $gift_cert_id, $extra);

        return true;
    } else {
        return false;
    }
}

//
// Function for cart routine
//

function fn_correct_gift_certificate(&$gift_cert_data)
{
    $currencies = Registry::get('currencies');

    if (!empty($gift_cert_data['products'])) {
        foreach ($gift_cert_data['products'] as $product_id => $v) {
            if (!is_numeric($v['product_id'])) {
                unset($gift_cert_data['products'][$product_id]);
            }
        }
    }

    $min_amount = Registry::get('addons.gift_certificates.min_amount');
    $max_amount = Registry::get('addons.gift_certificates.max_amount');
    $amount_to_compare = $gift_cert_data['amount'];
    if ($currencies[CART_SECONDARY_CURRENCY]['is_primary'] != 'Y') {
        $amount_to_compare = fn_format_price($amount_to_compare * $currencies[CART_SECONDARY_CURRENCY]['coefficient']) ;
    }

    if ($amount_to_compare > $max_amount || $amount_to_compare < $min_amount) {
        $gift_cert_data['amount'] = $gift_cert_data['amount'] > $max_amount ? $max_amount : $min_amount;

        fn_set_notification('N', __('notice'), __('gift_cert_amount_changed') . "<br />" . __('text_gift_cert_amount_alert', array(
            '[min]' => $min_amount,
            '[max]' => $max_amount
        )));
    }

    $gift_cert_data['http_location'] = Registry::get('config.http_location');
    if (fn_allowed_for('ULTIMATE') && !empty($gift_cert_data['gift_cert_code'])) {
        $company_location = db_get_field('SELECT ?:companies.storefront FROM ?:companies JOIN ?:gift_certificates ON ?:gift_certificates.gift_cert_code = ?s AND ?:companies.company_id = ?:gift_certificates.company_id', $gift_cert_data['gift_cert_code']);
        $gift_cert_data['http_location'] = !empty($company_location) ? 'http://' . $company_location : $gift_cert_data['http_location'];
    }

    if (!isset($gift_cert_data['correct_amount'])) {
        $amount_to_format = $gift_cert_data['amount'];
        if ($currencies[CART_SECONDARY_CURRENCY]['is_primary'] != 'Y') {
            $amount_to_format =$amount_to_format * $currencies[CART_SECONDARY_CURRENCY]['coefficient'];
        }
        $gift_cert_data['amount'] = fn_format_price($amount_to_format);
    } else {
        unset($gift_cert_data['correct_amount']);
    }
}

/**
 * Add gift certificate to cart
 *
 * @param array $gift_cert_data array with data for the certificate to add)
 * @param array $auth user session data
 * @return array array with gift certificate ID and data if addition is successful and empty array otherwise
 */
function fn_add_gift_certificate_to_cart($gift_cert_data, &$auth)
{
    if (!empty($gift_cert_data) && is_array($gift_cert_data)) {
        fn_correct_gift_certificate($gift_cert_data);
        $gift_cert_cart_id = fn_generate_gift_certificate_cart_id($gift_cert_data);

        if (isset($gift_cert_data['products']) && !empty($gift_cert_data['products'])) {
            foreach ((array) $gift_cert_data['products'] as $pr_id => $product_item) {
                $product_data = array();
                $product_data[$product_item['product_id']] = array(
                    'product_id' => $product_item['product_id'],
                    'amount' => $product_item['amount'],
                    'extra' => array('parent' => array('certificate' => $gift_cert_cart_id))
                );
                if (isset($product_item['product_options'])) {
                    $product_data[$product_item['product_id']]['product_options'] = $product_item['product_options'];
                }

                if (fn_add_product_to_cart($product_data, $_SESSION['cart'], $auth) == array()) {
                    unset($gift_cert_data['products'][$pr_id]);
                }
            }
        }

        return array (
            $gift_cert_cart_id,
            $gift_cert_data
        );

    } else {
        return array();
    }
}

function fn_delete_cart_gift_certificate(&$cart, $gift_cert_id)
{
    if (!empty($gift_cert_id)) {
        if (isset($cart['products'])) {
            foreach ((array) $cart['products'] as $k => $v) {
                if (isset($v['extra']['parent']['certificate']) && $v['extra']['parent']['certificate'] == $gift_cert_id) {
                    fn_delete_cart_product($cart, $k);

                    $cart['recalculate'] = true;
                }
            }
        }
        unset($cart['gift_certificates'][$gift_cert_id]);
        if (empty($cart['gift_certificates'])) {
            unset($cart['gift_certificates']);
        }
    }
}

function fn_delete_gift_certificate_in_use($gift_cert_code, &$cart)
{
    if (!empty($gift_cert_code)) {
        foreach ((array) $cart['products'] as $k => $v) {
            if (isset($v['extra']['in_use_certificate'][$gift_cert_code])) {
                unset($cart['products'][$k]['extra']['in_use_certificate'][$gift_cert_code]);
                if (empty($cart['products'][$k]['extra']['in_use_certificate'])) {
                    fn_delete_cart_product($cart, $k);
                } else {
                    $cart['products'][$k]['amount'] -= $v['extra']['in_use_certificate'][$gift_cert_code];
                }
            }
        }

        if (!empty($cart['deleted_exclude_products'][GIFT_CERTIFICATE_EXCLUDE_PRODUCTS])) {
            foreach ($cart['deleted_exclude_products'][GIFT_CERTIFICATE_EXCLUDE_PRODUCTS] as $cart_id => $v) {
                if (isset($v['in_use_certificate'][$gift_cert_code])) {
                    unset($cart['deleted_exclude_products'][GIFT_CERTIFICATE_EXCLUDE_PRODUCTS][$cart_id]);
                }
            }
        }

        if (!empty($cart['use_gift_certificates'][$gift_cert_code]['products'])) {
            unset($_SESSION['shipping_rates']);
        }
        $cart['reset_use_gift_certificates'][] = $cart['use_gift_certificates'][$gift_cert_code]['gift_cert_id'];
        unset($cart['use_gift_certificates'][$gift_cert_code]);
    }
}

function fn_generate_gift_certificate_cart_id($gift_cert_data)
{
    $_gc = array();
    $_gc[] = TIME;
    if (!empty($gift_cert_data)) {
        foreach ((array) $gift_cert_data as $k => $v) {
            if ($k == 'products') {
                if (!empty($v)) {
                    foreach ($v as $product_item) {
                        $_gc[] = $product_item['product_id'];
                        $_gc[] = $product_item['amount'];
                        if (isset($product_item['product_options'])) {
                            $_gc[] = serialize($product_item['product_options']);
                        }
                    }
                }
            } elseif ($k == 'extra' && !empty($v)) {
                if (!empty($v)) {
                    foreach ($v as $field => $data) {
                        $_gc[] = $field;
                        $_gc[] = is_array($data) ? serialize($data) : $data;
                    }
                }
            } else {
                $_gc[] = $v;
            }
        }
    }

    return fn_crc32(implode('_', $_gc));
}

function fn_get_gift_certificate_templates()
{
    $templates = array();
    $dir = fn_get_theme_path('[themes]/[theme]/mail/templates', 'C') . '/addons/gift_certificates/templates';

    $files = fn_get_dir_contents($dir, false, true, 'tpl');
    foreach ($files as $file) {
        $path_parts = explode(".", $file);
        $file_name = __($path_parts[0]);
        $templates[$file] = !empty($file_name) ? $file_name : ucfirst($path_parts[0]);
    }

    return 	$templates;
}

function fn_show_postal_card($gift_cert_data, $stored_products = array())
{
    $templates = fn_get_gift_certificate_templates();

    if (empty($gift_cert_data['template']) || !isset($templates[$gift_cert_data['template']])) {
        $gift_cert_data['template'] = key($templates);
    }

    $gc_data = fn_get_gift_certificate_info($gift_cert_data, 'P', $stored_products);

    $company_id = !empty($gift_cert_data['company_id']) ? $gift_cert_data['company_id'] : Registry::get('runtime.company_id');

    $view = Registry::get('view');
    $view->assign('gift_cert_data', $gc_data);
    $view->displayMail('addons/gift_certificates/templates/' . $gift_cert_data['template'], true, 'C', $company_id);

    return true;
}

function fn_generate_gift_certificate_code($quantity = 12)
{
    return fn_generate_code(Registry::get('addons.gift_certificates.code_prefix'), $quantity);
}

function fn_check_gift_certificate_code($code, $is_usable = false, $company_id = 0)
{
    if (empty($code)) {
        return false;
    }

    $condition = "gift_cert_code " . (is_array($code) ? "IN (?a) " : "= ?s");

    if ($is_usable) {
        $condition .= " AND status = 'A'";
    }

    if (!empty($company_id)) {
        $condition .= db_quote(" AND company_id = ?i", $company_id);
    }

    $status = db_get_hash_array("SELECT status, gift_cert_code FROM ?:gift_certificates WHERE $condition", 'gift_cert_code', $code);

    return !empty($status) ? $status : false;
}

//
// INCLUDE FUNCTION
//

function fn_gift_certificates_generate_cart_id(&$_cid, &$extra)
{

    if (isset($extra['parent']['certificate'])) {
        $_cid[] = $extra['parent']['certificate'];
    }

    return true;
}

function fn_gift_certificates_save_cart(&$cart, &$user_id, &$type)
{
    if (!empty($cart['products']) && is_array($cart['products'])) {
        foreach ($cart['products'] as $_item_id => $_prod) {
            if (isset($_prod['extra']['parent']['certificate'])) {
                db_query("UPDATE ?:user_session_products SET ?u WHERE item_id = ?i AND item_type = 'P' AND type = ?s AND user_id = ?i AND product_id = ?i", array('item_type' => 'C'), $_item_id, $type, $user_id, $_prod['product_id']);
                foreach ($cart['gift_certificates'][$_prod['extra']['parent']['certificate']]['products'] as $free_prod_id => $free_prod) {
                    if ($free_prod['product_id'] == $_prod['product_id']) {
                        $cart['gift_certificates'][$_prod['extra']['parent']['certificate']]['products'][$free_prod_id]['amount'] = $_prod['amount'];
                        break;
                    }
                }
            }
        }
    }

    if (!empty($cart['gift_certificates']) && is_array($cart['gift_certificates'])) {
        $_cart_gift_cert = $cart['gift_certificates'];
        foreach ($_cart_gift_cert as $_item_id => $_gift_cert) {
            $_cart_gift_cert[$_item_id]['user_id'] = $user_id;
            $_cart_gift_cert[$_item_id]['timestamp'] = TIME;
            $_cart_gift_cert[$_item_id]['type'] = $type;
            $_cart_gift_cert[$_item_id]['item_id'] = $_item_id;
            $_cart_gift_cert[$_item_id]['item_type'] = 'G';//Gift certificate
            $_cart_gift_cert[$_item_id]['extra'] = serialize($_gift_cert);
            $_cart_gift_cert[$_item_id]['price'] = $_gift_cert['amount'];
            $_cart_gift_cert[$_item_id]['amount'] = 1;
            $_cart_gift_cert[$_item_id]['user_type'] = empty($_SESSION['auth']['user_id']) ? 'U' : 'R';

            db_query('REPLACE INTO ?:user_session_products ?e', $_cart_gift_cert[$_item_id]);
        }
    }
}

function fn_gift_certificates_get_cart_item_types(&$item_types, &$action)
{
    $item_types[] = 'C';//product in Certificate
    if ($action == 'V') {
        $item_types[] = 'G';//Gift certificate
    }
}

function fn_gift_certificates_init_secure_controllers(&$controllers)
{
    $controllers['gift_certificates'] = 'passive';
}

function fn_gift_certificates_extract_cart(&$cart, &$user_id, &$type, &$user_type)
{
    if (!empty($user_id)) {
        $_cart_gift_cert = db_get_hash_array("SELECT * FROM ?:user_session_products WHERE user_id = ?i AND type = ?s AND item_type = 'G' AND user_type = ?s", 'item_id', $user_id, $type, $user_type);
        if (!empty($_cart_gift_cert) && is_array($_cart_gift_cert)) {
            $cart['gift_certificates'] = empty($cart['gift_certificates']) ? array() : $cart['gift_certificates'];
            foreach ($_cart_gift_cert as $_item_id => $_gift_cert) {
                $_gift_cert_extra = unserialize($_gift_cert['extra']);
                unset($_gift_cert['extra']);
                $cart['gift_certificates'][$_item_id] = empty($cart['gift_certificates'][$_item_id]) ? fn_array_merge($_gift_cert, $_gift_cert_extra, true) : $cart['gift_certificates'][$_item_id];
            }
        }
    }
}

function fn_gift_certificates_pre_place_order(&$cart)
{
    if (!empty($cart['use_gift_certificates'])) {
        foreach ($cart['use_gift_certificates'] as $k => $v) {
            if (!empty($v['log_id']) && !empty($v['previous_state'])) {
                unset($cart['use_gift_certificates'][$k]['log_id']);
                unset($cart['use_gift_certificates'][$k]['previous_state']);
            }
        }
    }
}

function fn_gift_certificates_place_order(&$order_id, &$action, &$order_status, &$cart)
{
    if (!empty($order_id)) {
        $is_parent_order = db_get_field("SELECT is_parent_order FROM ?:orders WHERE order_id = ?i", $order_id);
        if ($is_parent_order == 'Y') {
            return false;
        }

        if (defined('ORDER_MANAGEMENT')) {
            // If the purchased certificate was deleted when editing, then it should be updated in the database
            if (!empty($cart['gift_certificates_previous_state'])) {
                $flip_gcps = array_flip(array_keys($cart['gift_certificates_previous_state']));
                $flip_gc = array_flip(array_keys((!empty($cart['gift_certificates'])) ? $cart['gift_certificates'] : array()));
                $diff = array_diff_key($flip_gcps, $flip_gc);
                if (!empty($diff)) {
                    foreach ($diff as $gift_cert_cart_id => $v) {
                        db_query("UPDATE ?:gift_certificates SET order_ids = ?p WHERE gift_cert_id = ?i", fn_remove_from_set('order_ids', $order_id), $cart['gift_certificates_previous_state'][$gift_cert_cart_id]['gift_cert_id']);
                    }
                    db_query("DELETE FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, ORDER_DATA_PURCHASED_GIFT_CERTIFICATES);
                }
            }
        }

        if (isset($cart['reset_use_gift_certificates'])) {
            foreach ($cart['reset_use_gift_certificates'] as $v) {
                db_query("UPDATE ?:gift_certificates SET order_ids = ?p WHERE gift_cert_id = ?i", fn_remove_from_set('order_ids', $order_id), $v);
            }
            unset($cart['reset_use_gift_certificates']);
        }

        if (isset($cart['gift_certificates'])) {

            static $certificates = array();

            foreach ($cart['gift_certificates'] as $k => $v) {
                if (defined('ORDER_MANAGEMENT') && !empty($v['gift_cert_code'])) {
                    $code = $v['gift_cert_code'];
                } else {
                    do {
                        $code = fn_generate_gift_certificate_code();
                    } while (true == fn_check_gift_certificate_code($code));
                }

                if (empty($cart['parent_order_id']) || empty($certificates[$cart['parent_order_id']])) {
                    $_data = $v;
                    $_data['gift_cert_code'] = $code;
                    $_data['timestamp'] = TIME;
                    $_data['status'] = 'P';
                    $_data['products'] = !empty($v['products']) ? serialize($v['products']) : '';

                    $company_id = Registry::get('runtime.company_id');
                    if (!empty($company_id)) {
                        $_data['company_id'] = $company_id;
                    }

                    $gift_cert_id = db_query('REPLACE INTO ?:gift_certificates ?e', $_data);
                    $_data['gift_cert_id'] = $gift_cert_id;

                    if (!empty($cart['parent_order_id'])) {
                        $certificates[$cart['parent_order_id']] = $_data;
                    }
                } else {
                    $_data = $certificates[$cart['parent_order_id']];
                    $gift_cert_id = $_data['gift_cert_id'];
                }

                $cart['gift_certificates'][$k] = fn_array_merge($v, array('gift_cert_id' => $gift_cert_id, 'gift_cert_code' => $_data['gift_cert_code']));
                db_query("UPDATE ?:gift_certificates SET order_ids = ?p WHERE gift_cert_id = ?i", fn_add_to_set('order_ids', $order_id), $gift_cert_id);
                if (defined('ORDER_MANAGEMENT')) {
                //If the certificate was not removed from the order, it is necessary to check  whether the products and amount have been changed and modify the log.
                    $debit_info = db_get_row("SELECT debit AS amount, debit_products AS products FROM ?:gift_certificates_log WHERE gift_cert_id = ?i ORDER BY timestamp DESC", $gift_cert_id);
                    if (empty($debit_info)) {
                        $debit_info = db_get_row("SELECT amount, products FROM ?:gift_certificates WHERE gift_cert_id = ?i", $gift_cert_id);
                    }

                    if (($_data['amount'] - $debit_info['amount'] != 0) || (md5($_data['products']) != md5($debit_info['products']))) {
                        $_info = array(
                            'amount' => $_data['amount'],
                            'products' => $_data['products']
                        );
                        fn_add_gift_certificate_log_record($gift_cert_id, $debit_info, $_info);
                    }
                }
            }

            $order_data = array(
                'order_id' => $order_id,
                'type' => ORDER_DATA_PURCHASED_GIFT_CERTIFICATES,
                'data' => serialize($cart['gift_certificates'])
            );
            db_query("REPLACE INTO ?:order_data ?e", $order_data);
        }

//--> FIXME: optimize this code:
        if (!empty($cart['use_gift_certificates_previous_state'])) {
            $flip_ugcps = array_flip(array_keys($cart['use_gift_certificates_previous_state']));
            $flip_ugc = array_flip(array_keys((!empty($cart['use_gift_certificates'])) ? $cart['use_gift_certificates'] : array()));
            $diff = array_diff_key($flip_ugcps, $flip_ugc);
            if (!empty($diff)) {
                foreach ($diff as $gift_cert_code => $v) {
                    $gc_data = $cart['use_gift_certificates_previous_state'][$gift_cert_code]['previous_state'];
                    $log_records = db_get_array("SELECT log_id, amount, debit, products, debit_products FROM ?:gift_certificates_log WHERE log_id >= ?i AND gift_cert_id = ?i ORDER BY timestamp ASC", $gc_data['log_id'], $gc_data['gift_cert_id']);
                    foreach ($log_records as $record) {
                        if (!empty($gc_data['products'])) {
                            if ($record['log_id'] != $gc_data['log_id']) {
                                $record['products'] = unserialize($record['products']);
                                foreach ($gc_data['products'] as $po_product_id => $po_quantity) {
                                    if (!isset($record['products'][$po_product_id])) {
                                        $record['products'][$po_product_id] = $po_quantity;
                                    } else {
                                        $record['products'][$po_product_id] += $po_quantity;
                                    }
                                    if (empty($record['products'][$po_product_id])) {
                                        unset($record['products'][$po_product_id]);
                                    }
                                }
                                $record['products'] = serialize($record['products']);
                            }

                            $record['debit_products'] = unserialize($record['debit_products']);
                            foreach ($gc_data['products'] as $po_product_id => $po_quantity) {
                                if (!isset($record['debit_products'][$po_product_id])) {
                                    $record['debit_products'][$po_product_id] = $po_quantity;
                                } else {
                                    $record['debit_products'][$po_product_id] += $po_quantity;
                                }
                                if (empty($record['debit_products'][$po_product_id])) {
                                    unset($record['debit_products'][$po_product_id]);
                                }
                            }
                            $record['debit_products'] = serialize($record['debit_products']);
                        }

                        if ($record['log_id'] != $gc_data['log_id']) {
                            $record['amount'] += $gc_data['cost'];
                        }
                        $record['debit'] += $gc_data['cost'];

                        db_query("UPDATE ?:gift_certificates_log SET ?u WHERE log_id = ?i", $record, $record['log_id']);

                        if (floatval($record['debit']) > 0 || unserialize($record['debit_products']) != array() && db_get_field("SELECT status FROM ?:gift_certificates WHERE gift_cert_id = ?", $gc_data['gift_cert_id']) == 'U') {
                            fn_change_gift_certificate_status($gc_data['gift_cert_id'], 'A');
                        }
                    }
                }
            }
        }

        if (isset($cart['use_gift_certificates'])) {
            $debit_products = array();
            $use_gift_certificates = array();

            if (!empty($cart['deleted_exclude_products'][GIFT_CERTIFICATE_EXCLUDE_PRODUCTS])) {
                foreach ($cart['deleted_exclude_products'][GIFT_CERTIFICATE_EXCLUDE_PRODUCTS] as $cart_id => $v) {
                    foreach ($v['in_use_certificate'] as $gift_cert_code => $amount) {
                        $debit_products[$gift_cert_code]['products'][$v['product_id']] = $amount;
                    }
                }
            }

            $use_gift_certificate_products = array();
            if (!empty($cart['products'])) {
                foreach ($cart['products'] as $product) {
                    if (!empty($product['extra']['exclude_from_calculate']) && $product['extra']['exclude_from_calculate'] == GIFT_CERTIFICATE_EXCLUDE_PRODUCTS) {
                        foreach ($product['extra']['in_use_certificate'] as $gift_cert_code => $quantity) {
                            $use_gift_certificate_products[$gift_cert_code][$product['product_id']] = $quantity;
                        }
                    }
                }
            }

            foreach ($cart['use_gift_certificates'] as $k=>$v) {
                if (!empty($v['log_id'])) {
                    $product_odds = array();
                    $amount_odds = $v['previous_state']['cost'] - $v['cost'];
                    $current_state_products = (!empty($use_gift_certificate_products[$k]) ? $use_gift_certificate_products[$k] : array());
                    if (sizeof($v['previous_state']['products']) != sizeof($current_state_products) || serialize($v['previous_state']['products']) != serialize($current_state_products)) {
                        if (!empty($v['previous_state']['products'])) {
                            foreach ($v['previous_state']['products'] as $product_id => $quantity) {
                                if (!isset($current_state_products[$product_id])) {
                                    $product_odds[$product_id] = $quantity;
                                } else {
                                    $product_odds[$product_id] = $quantity - $current_state_products[$product_id];
                                }
                                if (empty($product_odds[$product_id])) {
                                    unset($product_odds[$product_id]);
                                }
                            }
                        } elseif (!empty($current_state_products)) {
                            foreach ($current_state_products as $product_id => $quantity) {
                                $product_odds[$product_id] = -$quantity;
                            }
                        }
                    }

                    if ($amount_odds != 0 || !empty($product_odds)) {
                        $log_records = db_get_array("SELECT log_id, amount, debit, products, debit_products FROM ?:gift_certificates_log WHERE log_id >= ?i AND gift_cert_id = ?i ORDER BY timestamp ASC", $v['log_id'], $v['gift_cert_id']);
                        foreach ($log_records as $record) {
                            if (!empty($product_odds)) {
                                if ($record['log_id'] != $v['log_id']) {
                                    $record['products'] = unserialize($record['products']);
                                    foreach ($product_odds as $po_product_id => $po_quantity) {
                                        if (!isset($record['products'][$po_product_id])) {
                                            $record['products'][$po_product_id] = $po_quantity;
                                        } else {
                                            $record['products'][$po_product_id] += $po_quantity;
                                        }
                                        if (empty($record['products'][$po_product_id])) {
                                            unset($record['products'][$po_product_id]);
                                        }
                                    }
                                    $record['products'] = serialize($record['products']);
                                }

                                $record['debit_products'] = unserialize($record['debit_products']);
                                foreach ($product_odds as $po_product_id => $po_quantity) {
                                    if (!isset($record['debit_products'][$po_product_id])) {
                                        $record['debit_products'][$po_product_id] = $po_quantity;
                                    } else {
                                        $record['debit_products'][$po_product_id] += $po_quantity;
                                    }
                                    if (empty($record['debit_products'][$po_product_id])) {
                                        unset($record['debit_products'][$po_product_id]);
                                    }
                                }
                                $record['debit_products'] = serialize($record['debit_products']);														} else {
                                if ($record['log_id'] != $v['log_id']) {
                                    $record['amount'] += $amount_odds;
                                }
                                $record['debit'] += $amount_odds;
                            }
                            db_query("UPDATE ?:gift_certificates_log SET ?u WHERE log_id = ?i", $record, $record['log_id']);

                            $use_gift_certificates[$k] = array (
                                'gift_cert_id' => $v['gift_cert_id'],
                                'amount' 	=> $v['previous_state']['amount'],
                                'cost' => $v['cost'],
                                'log_id' => $v['log_id']
                            );

                            if (floatval($record['debit']) <= 0 &&  unserialize($record['debit_products']) == array()) {
                                fn_change_gift_certificate_status($v['gift_cert_id'], 'U');
                            } elseif (floatval($record['debit']) > 0 || unserialize($record['debit_products']) != array() && db_get_field("SELECT status FROM ?:gift_certificates WHERE gift_cert_id = ?i", $v['gift_cert_id']) == 'U') {
                                fn_change_gift_certificate_status($v['gift_cert_id'], 'A');
                            }
                        }
                    }
//<-- FIXME: optimize this code
                } else {
                    $before_info = array(
                        'amount' 	=> $v['amount'],
                        'products'  => serialize(!empty($v['products']) ? $v['products'] : array())
                    );
                    $after_info = array(
                        'amount' 	=> fn_format_price($v['amount'] - $v['cost']),
                        'products'  => serialize(!empty($debit_products[$k]['products']) ? $debit_products[$k]['products'] : array())
                    );
                    $log_id = fn_add_gift_certificate_log_record($v['gift_cert_id'], $before_info, $after_info, $order_id);

                    $use_gift_certificates[$k] = array(
                        'gift_cert_id' => $v['gift_cert_id'],
                        'amount' 	=> $v['amount'],
                        'cost' => $v['cost'],
                        'log_id' => $log_id
                    );
                    if (floatval($v['amount'] - $v['cost']) <= 0 &&  !isset($debit_products[$k]['products'])) {
                        fn_change_gift_certificate_status($v['gift_cert_id'], 'U');
                    }
                }
                db_query("UPDATE ?:gift_certificates SET order_ids = ?p  WHERE gift_cert_id = ?i", fn_add_to_set('order_ids', $order_id), $v['gift_cert_id']);
            }

            $order_data = array(
                'order_id' => $order_id,
                'type' => 'U',
                'data' => serialize($use_gift_certificates)
            );
            db_query("REPLACE INTO ?:order_data ?e", $order_data);
        }
    }

}

function fn_gift_certificates_get_order_info(&$order, &$additional_data)
{
    if (!empty($additional_data[ORDER_DATA_PURCHASED_GIFT_CERTIFICATES])) {
        $subtotal = 0;
        $purchased_certificates = @unserialize($additional_data[ORDER_DATA_PURCHASED_GIFT_CERTIFICATES]);
        if (!empty($purchased_certificates)) {
            foreach ($purchased_certificates as $k => $v) {
                $purchased_certificates[$k]['subtotal'] = $v['amount'];
                $purchased_certificates[$k]['display_subtotal'] = $v['amount'];

                if (!isset($v['extra']['exclude_from_calculate'])) {
                    $subtotal += $v['amount'];
                }

                if (!empty($v['products'])) {
                    foreach ($order['products'] as $cart_id => $product) {
                        if (!empty($product['extra']['parent']['certificate']) && $product['extra']['parent']['certificate'] == $k) {
                            $purchased_certificates[$k]['subtotal'] += $product['subtotal'];
                            $purchased_certificates[$k]['display_subtotal'] += $product['display_subtotal'];
                        }
                    }
                }
            }

            $order['subtotal'] += $subtotal;
            $order['display_subtotal'] += $subtotal;
            $order['pure_subtotal'] = (isset($order['pure_subtotal']) ? $order['pure_subtotal'] : 0) + $subtotal;
            $order['gift_certificates'] = $purchased_certificates;
        }
    }

    if (!empty($additional_data[ORDER_DATA_USE_GIFT_CERTIFICATES])) {
        $order['use_gift_certificates'] = @unserialize($additional_data[ORDER_DATA_USE_GIFT_CERTIFICATES]);
    }
}

function fn_gift_certificates_exclude_products_from_calculation(&$cart, &$auth, &$pure_subtotal, &$subtotal)
{

    if (isset($cart['gift_certificates']) && !fn_is_empty($cart['gift_certificates'])) {
        foreach ($cart['gift_certificates'] as $k => $v) {
            if (isset($v['extra']['exclude_from_calculate'])) {
                unset($cart['gift_certificates'][$k]);
            } else {
                $subtotal += $v['amount'];
                $pure_subtotal += $v['amount'];
            }
        }
    }

    if (!empty($cart['use_gift_certificates'])) {
        foreach ($cart['use_gift_certificates'] as $code => $value) {

            // This step is performed when editing the existent order only.
            if (is_array($value) && isset($value['log_id'])) {// indicates that the order is being edited

                $gift_cert_data = $value;

                // Merge with the current balance.
                $last_log_item = db_get_row("SELECT log_id, debit, debit_products FROM ?:gift_certificates_log WHERE gift_cert_id = ?i ORDER BY log_id DESC", $value['gift_cert_id']);
                $last_log_item['debit_products'] = unserialize($last_log_item['debit_products']);

                $gift_cert_data['amount'] = $gift_cert_data['previous_state']['cost'] + $last_log_item['debit'];
                if (!empty($last_log_item['debit_products'])) {
                    foreach ($last_log_item['debit_products'] as $product_id => $quantity) {
                        if (!isset($gift_cert_data['products'][$product_id])) {
                            $gift_cert_data['products'][$product_id] = $quantity['amount'];
                        } else {
                            $gift_cert_data['products'][$product_id] = (isset($gift_cert_data['previous_state']['products'][$product_id]) ? $gift_cert_data['previous_state']['products'][$product_id] : 0) + $quantity['amount'];
                        }
                    }
                }
                $cart['use_gift_certificates_previous_state'][$code] = $gift_cert_data;

            // This step is performed when editing the existent order only.
            } elseif (defined('ORDER_MANAGEMENT') && !empty($cart['use_gift_certificates_previous_state'][$code])) {
                //
                // If the certificate was deleted when editing, and then it was applied again.
                // It is necessary to set its data (not currect ones) again with the performed changes.
                //
                $gift_cert_data = $cart['use_gift_certificates_previous_state'][$code];

            // This step is performed only on Create order and in the frontend.
            } else {
                $gift_cert_data = db_get_row("SELECT gift_cert_id, amount, products  FROM ?:gift_certificates WHERE gift_cert_code = ?s ?p", $code, fn_get_gift_certificate_company_condition('company_id'));
                if (!$gift_cert_data) {
                    return false;
                }
                $gift_cert_data['products'] = empty($gift_cert_data['products']) ? array() : @unserialize($gift_cert_data['products']);
                $debit_balance = db_get_row("SELECT debit AS amount, debit_products as products FROM ?:gift_certificates_log WHERE gift_cert_id = ?i ORDER BY log_id DESC", $gift_cert_data['gift_cert_id']);
                if (!empty($debit_balance)) {
                    $debit_balance['products'] = @unserialize($debit_balance['products']);
                    $gift_cert_data  = fn_array_merge($gift_cert_data, $debit_balance);
                }
            }

            $cart['use_gift_certificates'][$code] = $gift_cert_data;

            if (!empty($gift_cert_data['products']) && AREA == 'C') {
                $product_data = array();
                foreach ((array) $gift_cert_data['products'] as $key => $product_item) {
                    if (!empty($debit_balance) && !isset($debit_balance['products'][$key])) {
                        continue;
                    }

                    $product_data[$product_item['product_id']] = array(
                            'product_id' => $product_item['product_id'],
                            'amount' => $product_item['amount'],
                            'extra' => array(
                                'exclude_from_calculate' => GIFT_CERTIFICATE_EXCLUDE_PRODUCTS,
                                'in_use_certificate' => array($code => $product_item['amount'])
                            )
                    );
                    if (isset($product_item['product_options'])) {
                        $product_data[$product_item['product_id']]['product_options'] = $product_item['product_options'];
                    }
                    // Сhoose the option which the product had before editing.
                    if (!empty($value['log_id']) && !empty($value['product_options'][$product_id])) {
                        $product_data[$product_id]['product_options'] = $value['product_options'][$product_id];
                    }
                }
                fn_add_product_to_cart($product_data, $cart, $auth);

                $cart['recalculate'] = true;
            }
        }
    }
}

/**
 * Hook of "calculate_cart_items"
 *
 * @param array $cart Array of the cart contents and user information necessary for purchase
 * @param array $cart_products cart products
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return bool always true
 */
function fn_gift_certificates_calculate_cart_items(&$cart, &$cart_products, &$auth)
{
    foreach ($cart['products'] as $cart_id => $product) {
        if (!empty($product['extra']['parent']['certificate'])) {
            $cart_products[$cart_id]['free_shipping'] = 'N';
            $cart_products[$cart_id]['shipping_no_required'] = 'Y';
        }
    }

    return true;
}

function fn_gift_certificates_calculate_cart(&$cart, &$cart_products, &$auth)
{
    $subtotal = 0;

    if (isset($cart['additional_gift_certificates'])) {
        $cart['gift_certificates'] = fn_array_merge((!empty($cart['gift_certificates']) ? $cart['gift_certificates'] : array()), $cart['additional_gift_certificates']);
        unset($cart['additional_gift_certificates']);
    }

    if (!empty($cart['gift_certificates'])) {
        foreach ((array) $cart['gift_certificates'] as $k => $v) {
            $cart['gift_certificates'][$k]['subtotal'] = $v['amount'];
            $cart['gift_certificates'][$k]['display_subtotal'] = $v['amount'];
            $cart['gift_certificates'][$k]['tax_value'] = 0;

            if (!isset($v['extra']['exclude_from_calculate'])) {
                $subtotal += $v['amount'];
            }
            if (!empty($v['products'])) {
                foreach ($cart['products'] as $cart_id => $product) {
                    if (!empty($product['extra']['parent']['certificate']) && $product['extra']['parent']['certificate'] == $k) {
                        $cart['gift_certificates'][$k]['subtotal'] += $cart_products[$cart_id]['subtotal'];
                        $cart['gift_certificates'][$k]['display_subtotal'] += $cart_products[$cart_id]['display_subtotal'];
                        /*if (!empty($cart_products[$cart_id]['tax_summary']['added'])) {
                            $cart['gift_certificates'][$k]['tax_value'] += $cart_products[$cart_id]['tax_summary']['added'];
                        }*/
                    }
                }
                foreach ($v['products'] as $id => $val) {
                    $exists = false;
                    foreach ($cart['products'] as $cart_id => $product) {
                        if ($product['product_id'] == $val['product_id']) {
                            $exists = true;
                            break;
                        }
                    }
                    if (!$exists && empty($cart['parent_order_id'])) {
                        unset($cart['gift_certificates'][$k]['products'][$id]);
                    }
                }
            }
        }

        $cart['amount'] = (isset($cart['amount']) ? $cart['amount'] : 0) + sizeof($cart['gift_certificates']);
        $cart['total'] = (isset($cart['total']) ? $cart['total'] : 0) + $subtotal;
        $cart['subtotal'] += $subtotal;
        $cart['display_subtotal'] += $subtotal;

        $cart['pure_subtotal'] = (isset($cart['pure_subtotal']) ? $cart['pure_subtotal'] : 0) + $subtotal;
    }

    if (!empty($cart['use_gift_certificates'])) {

        $_original = $_subtotal = (Registry::get('addons.gift_certificates.redeem_shipping_cost') == 'Y') ? $cart['total'] : $cart['subtotal'];

        foreach ((array) $cart['use_gift_certificates'] as $code => $value) {
            $_subtotal -= $value['amount'];
            if ($_subtotal >= 0) {
                $cart['use_gift_certificates'][$code]['cost'] = $value['amount'];
            } else {
                $cart['use_gift_certificates'][$code]['cost'] = $value['amount'] + $_subtotal;
                $_subtotal = 0;
            }
        }

        $cart['total'] -= ($_original - $_subtotal);
        $cart['total'] = fn_format_price($cart['total']);
    }

    // We need to display coupon code field
    $cart['has_coupons'] = true;
}

function fn_gift_certificates_order_notification(&$order_info, &$order_statuses, &$force_notification)
{
    if (isset($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $k => $v) {
            if (!empty($order_statuses[$order_info['status']]['params']['gift_cert_status'])) {
                $gift_cert_data = fn_get_gift_certificate_info($v['gift_cert_id'], 'B');
                fn_gift_certificate_notification($gift_cert_data, $force_notification);
            }
        }
    }
}

function fn_gift_certificate_notification(&$gift_cert_data, $force_notification = array())
{
    static $notified = array();

    if (!empty($notified[$gift_cert_data['gift_cert_id']])) {
        return true;
    }

    $status_params = fn_get_status_params($gift_cert_data['status'], STATUSES_GIFT_CERTIFICATE);

    $notify_user = isset($force_notification['C']) ? $force_notification['C'] : (!empty($status_params['notify']) && $status_params['notify'] == 'Y' ? true : false);

    if ($notify_user == true && $gift_cert_data['email'] && $gift_cert_data['send_via'] == 'E') {
        $notified[$gift_cert_data['gift_cert_id']] = true;

        $templates = fn_get_gift_certificate_templates();
        $gift_cert_data['template'] = isset($templates[$gift_cert_data['template']]) ? $gift_cert_data['template'] : key($templates);

        Mailer::sendMail(array(
            'to' => $gift_cert_data['email'],
            'from' => 'company_orders_department',
            'data' => array(
                'gift_cert_data' => $gift_cert_data,
                'certificate_status' => fn_get_status_data($gift_cert_data['status'], STATUSES_GIFT_CERTIFICATE, $gift_cert_data['gift_cert_id'])
            ),
            'tpl' => 'addons/gift_certificates/gift_certificate.tpl',
            'company_id' => $gift_cert_data['company_id']
        ), 'C');

        return true;
    }

    return false;
}

function fn_gift_certificates_is_cart_empty(&$cart, &$result)
{
    if (!empty($cart['gift_certificates'])) {
        $result = false;
    }

    if ($result && !empty($cart['products'])) {
        foreach ($cart['products'] as $v) {
            if (isset($v['extra']['exclude_from_calculate']) && $v['extra']['exclude_from_calculate'] === GIFT_CERTIFICATE_EXCLUDE_PRODUCTS) {
                $result = false;
                break;
            }
        }
    }
}

function fn_gift_certificates_delete_order(&$order_id)
{
    db_query("UPDATE ?:gift_certificates SET order_ids = ?p", fn_remove_from_set('order_ids', $order_id));
}

function fn_gift_certificates_delete_cart_product(&$cart, &$cart_id)
{
    if (!empty($cart_id)) {
        if (isset($cart['products'][$cart_id]['extra']['parent']['certificate'])) {
            $gift_cert_cart_id = $cart['products'][$cart_id]['extra']['parent']['certificate'];
            $product_id = $cart['products'][$cart_id]['product_id'];

            if (isset($cart['gift_certificates'][$gift_cert_cart_id]['products'])) {
                foreach ($cart['gift_certificates'][$gift_cert_cart_id]['products'] as $id => $v) {
                    if ($v['product_id'] == $product_id) {
                        unset($cart['gift_certificates'][$gift_cert_cart_id]['products'][$id]);
                        break;
                    }
                }
            }

            if (empty($cart['gift_certificates'][$gift_cert_cart_id]['products'])) {
                unset($cart['gift_certificates'][$gift_cert_cart_id]['products']);
            }
        }
    }
}

function fn_create_return_gift_certificate($order_id, $amount)
{

    $min = Registry::get('addons.gift_certificates.min_amount') * 1;
    $max = Registry::get('addons.gift_certificates.max_amount') * 1;

    $order_info = fn_get_order_info($order_id);
    $templates = fn_get_gift_certificate_templates();

    $_data = array(
        'send_via'		 => 'E',
        'recipient' 	 => "$order_info[firstname] $order_info[lastname]",
        'sender' 		 => Registry::get('settings.Company.company_name'),
        'amount' 		 => $amount,
        'email' 		 => $order_info['email'],
        'address' 		 => $order_info['s_address'],
        'address_2' 	 => $order_info['s_address_2'],
        'city' 	 		 => $order_info['s_city'],
        'country' 		 => $order_info['s_country'],
        'state' 		 => $order_info['s_state'],
        'zipcode' 		 => $order_info['s_zipcode'],
        'phone' 		 => $order_info['phone'],
        'template'       => key($templates)
    );

    if (fn_allowed_for('ULTIMATE')) {
        $_data['company_id'] = Registry::ifGet('runtime.company_id', $order_info['company_id']);
    }

    do {
        $code = fn_generate_gift_certificate_code();
    } while (true == fn_check_gift_certificate_code($code));

    if ($amount < $min || $amount > $max) {

        fn_set_notification('E', __('error'), __('gift_cert_error_amount', array(
            '[min]' => $min,
            '[max]' => $max
        )));

        $result = array();
    } else {
        $_data = fn_array_merge($_data, array('gift_cert_code' => $code, 'timestamp' => TIME));
        $gift_cert_id = db_query('INSERT INTO ?:gift_certificates ?e', $_data);
        $result = array($gift_cert_id => array('code' => $code, 'amount' => $amount));
    }

    return $result;
}

function fn_gift_certificates_exclude_from_shipping_calculation(&$product, &$exclude)
{
    if (Registry::get('addons.gift_certificates.redeem_shipping_cost') != 'Y' && !empty($product['extra']['parent']['certificate'])) {
        $exclude = true;
    }
}

function fn_gift_certificates_form_cart(&$order_info, &$cart)
{
    if (!empty($order_info['gift_certificates'])) {
        $cart['gift_certificates'] = $cart['gift_certificates_previous_state'] = $order_info['gift_certificates'];
    }

    if (!empty($order_info['use_gift_certificates'])) {
        foreach ($order_info['use_gift_certificates'] as $gift_cert_code => $v) {
            $cart['use_gift_certificates'][$gift_cert_code] = $v;
            $cart['use_gift_certificates'][$gift_cert_code]['previous_state'] = $v;
            $cart['use_gift_certificates'][$gift_cert_code]['products'] =  array();
            $cart['use_gift_certificates'][$gift_cert_code]['previous_state']['products'] =  array();
        }

        // Set only those products that were used.
        foreach ($cart['products'] as $cart_id => $v) {
            if (!empty($v['extra']['exclude_from_calculate']) && $v['extra']['exclude_from_calculate'] == GIFT_CERTIFICATE_EXCLUDE_PRODUCTS) {
                foreach ($v['extra']['in_use_certificate'] as $gift_cert_code => $quantity) {
                    $cart['use_gift_certificates'][$gift_cert_code]['products'][$v['product_id']] = $quantity;
                    $cart['use_gift_certificates'][$gift_cert_code]['previous_state']['products'][$v['product_id']] = $quantity;
                    if (!empty($v['product_options'])) {
                        $cart['use_gift_certificates'][$gift_cert_code]['product_options'][$v['product_id']] = $v['product_options'];
                    }
                }
            }
        }
    }
}

function fn_gift_certificates_allow_place_order(&$total, &$cart)
{
    if (!empty($cart['use_gift_certificates'])) {
        foreach ($cart['use_gift_certificates'] as $k => $v) {
            $total += $v['cost'];
        }
    }

    return true;
}

function fn_gift_certificates_get_orders(&$params, &$fields, &$sortings, &$condition, &$join)
{
    if (isset($params['gift_cert_code']) && fn_string_not_empty($params['gift_cert_code'])) {
        $condition .= db_quote(" AND gc_order_data.data LIKE ?l", "%" . trim($params['gift_cert_code']) . "%");
        $join .= db_quote(" LEFT JOIN ?:order_data as gc_order_data ON gc_order_data.order_id = ?:orders.order_id AND gc_order_data.type IN (?a)", explode('|', $params['gift_cert_in']));
    }

    return true;
}

function fn_get_gift_certificates($params, $items_per_page = 0)
{
    // Init filter
    $params = LastView::instance()->update('gift_certs', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        '?:gift_certificates.gift_cert_id',
        '?:gift_certificates.gift_cert_code',
        '?:gift_certificates.timestamp',
        '?:gift_certificates.amount',
        '?:gift_certificates.status',
        '?:gift_certificates.recipient',
        '?:gift_certificates.sender',
        '?:gift_certificates.send_via',
        '?:gift_certificates.email',
    );

    if (fn_allowed_for('ULTIMATE')) {
        $fields[] = '?:gift_certificates.company_id';
    }

    // Define sort fields
    $sortings = array (
        'timestamp' => "?:gift_certificates.timestamp",
        'amount' => "?:gift_certificates.amount",
        'recipient' => "?:gift_certificates.recipient",
        'sender' => "?:gift_certificates.sender",
        'status' => "?:gift_certificates.status",
        'gift_cert_code' => "?:gift_certificates.gift_cert_code",
        'send_via' => "?:gift_certificates.send_via",
    );

    $sorting = db_sort($params, $sortings, 'timestamp', 'desc');

    $condition = $join = '';

    if (isset($params['sender']) && fn_string_not_empty($params['sender'])) {
        $condition .= db_quote(" AND ?:gift_certificates.sender LIKE ?l", "%" . trim($params['sender']) ."%");
    }

    if (isset($params['recipient']) && fn_string_not_empty($params['recipient'])) {
        $condition .= db_quote(" AND ?:gift_certificates.recipient LIKE ?l", "%" . trim($params['recipient']) ."%");
    }

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $condition .= db_quote(" AND ?:gift_certificates.email LIKE ?l", "%" . trim($params['email']) ."%");
    }

    if (!empty($params['amount_from'])) {
        $condition .= db_quote(" AND ?:gift_certificates.amount >= ?d", $params['amount_from']);
    }

    if (!empty($params['amount_to'])) {
        $condition .= db_quote(" AND ?:gift_certificates.amount <= ?d", $params['amount_to']);
    }

    if (!empty($params['gift_cert_ids'])) {
        $condition .= db_quote(" AND ?:gift_certificates.gift_cert_id IN (?n)", $params['gift_cert_ids']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(" AND ?:gift_certificates.status IN (?a)", $params['status']);
    }

    if (isset($params['gift_cert_code']) && fn_string_not_empty($params['gift_cert_code'])) {
        $condition .= db_quote(" AND ?:gift_certificates.gift_cert_code LIKE ?l", "%" . trim($params['gift_cert_code']) ."%");
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:gift_certificates.timestamp >= ?i AND ?:gift_certificates.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:gift_certificates WHERE 1 ?p", $condition . fn_get_gift_certificate_company_condition('?:gift_certificates.company_id'));
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $gift_certificates = db_get_array(
        "SELECT ?p  FROM ?:gift_certificates WHERE 1 ?p ?p ?p",
        implode(',', $fields), $condition . fn_get_gift_certificate_company_condition('?:gift_certificates.company_id'), $sorting, $limit
    );

    foreach ($gift_certificates as $k => $v) {
        $debit_balance = db_get_row("SELECT debit, debit_products FROM ?:gift_certificates_log WHERE gift_cert_id = ?i ORDER BY log_id DESC", $v['gift_cert_id']);
        $gift_certificates[$k]['debit'] = (empty($debit_balance)) ? $v['amount'] : $debit_balance['debit'];
    }

    LastView::instance()->processResults('gift_certificates', $gift_certificates, $params);

    return array($gift_certificates, $params);
}

function fn_gift_certificates_pre_add_to_cart(&$product_data, &$cart, &$auth, &$update)
{
    if (fn_allowed_for('MULTIVENDOR') && !empty($cart['gift_certificates'])) {
        fn_set_notification('W', 'Warning', __('gift_cert_with_products'));

        $product_data = array();
    }
    if ($update == true) {
        $certificate_products = array();
        foreach ($product_data as $k => $v) {
            if (isset($v['parent']['certificate'])) {
                $certificate_products[$v['parent']['certificate']][$v['product_id']] = $v['amount'];
            }
        }

        if (!empty($certificate_products)) {
            foreach ($certificate_products as $gift_cert_cart_id => $products) {
                $cart['gift_certificates'][$gift_cert_cart_id]['products'] = $products;
            }
        }
    }
}

function fn_gift_certificates_delete_cart_products(&$cart, $cart_id)
{
    $product = $cart['products'][$cart_id];

    if (!empty($product['extra']['exclude_from_calculate']) && $product['extra']['exclude_from_calculate'] == GIFT_CERTIFICATE_EXCLUDE_PRODUCTS) {
        $cart['deleted_exclude_products'][GIFT_CERTIFICATE_EXCLUDE_PRODUCTS][$cart_id] = array(
            'product_id' => $product['product_id'],
            'in_use_certificate' => $product['extra']['in_use_certificate']
        );
    }

    if (isset($product['extra']['parent']['certificate'])) {
        foreach ($cart['gift_certificates'][$product['extra']['parent']['certificate']]['products'] as $id => $v) {
            if ($v['product_id'] == $product['product_id']) {
                unset($cart['gift_certificates'][$product['extra']['parent']['certificate']]['products'][$id]);
                break;
            }
        }
    }
}

function fn_gift_certificates_promotion_gift_certificate($bonus, &$cart, &$auth, &$cart_products)
{
    $cart['promotions'][$bonus['promotion_id']]['bonuses'][$bonus['bonus']] = $bonus;

    if ($bonus['bonus'] == 'gift_certificate') {

        $_data = array(
            'send_via' => 'E', // email
            'recipient' => !empty($cart['user_data']['firstname']) ? ($cart['user_data']['firstname'] . ' ' . $cart['user_data']['lastname']) : '',
            'sender' => Registry::get('settings.Company.company_name'),
            'amount' => $bonus['value'],
            'correct_amount' => 'N',
            'email' => !empty($cart['user_data']['email']) ? $cart['user_data']['email'] : '',
            'products' => array(),
            'extra' => array(
                'exclude_from_calculate' => 'PR',
            ),
            'template' => 'default.tpl'
        );

        list($gc_cart_id, $gc) = fn_add_gift_certificate_to_cart($_data, $auth);

        if (!empty($gc_cart_id)) {
            $cart['additional_gift_certificates'][$gc_cart_id] = $gc;
        }

        $cart['promotions'][$bonus['promotion_id']]['bonuses'][$bonus['bonus']]['gc_cart_id'] = $gc_cart_id;
    }

    return true;
}

//
// Generate navigation
//
function fn_gift_certificates_generate_sections($section)
{
    Registry::set('navigation.dynamic.sections', array (
        'manage' => array (
            'title' => __('gift_certificates'),
            'href' => 'gift_certificates.manage',
        ),
        'statuses' => array (
            'title' => __('gift_certificate_statuses'),
            'href' => 'statuses.manage?type=' . STATUSES_GIFT_CERTIFICATE,
        ),
    ));
    Registry::set('navigation.dynamic.active_section', $section);

    return true;
}

function fn_gift_certificates_get_status_params_definition(&$status_params, $type)
{
    if ($type == STATUSES_ORDER) {
        $status_params['gift_cert_status'] = array (
            'type' => 'status',
            'label' => 'change_gift_certificate_status',
            'status_type' => STATUSES_GIFT_CERTIFICATE
        );

    } elseif ($type == STATUSES_GIFT_CERTIFICATE) {
        $status_params = array (
            'color' => array (
                'type' => 'color',
                'label' => 'color'
            ),
            'notify' => array (
                'type' => 'checkbox',
                'label' => 'notify_customer'
            ),
        );
    }

    return true;
}

function fn_gift_certificates_reorder(&$order_info, &$cart, &$auth)
{
    // Check whether gift certificates exist
    if (isset($order_info['gift_certificates'])) {

        if (isset($order_info['products'])) {
            foreach ($order_info['products'] as $k => $item) {
                if (isset($order_info['products'][$k]['extra']['parent']['certificate'])) {
                    unset($order_info['products'][$k]);
                }
            }
        }

        // Gift certificates is empty, create it
        if (empty($cart['gift_certificates'])) {
            $cart['gift_certificates'] = array();
        }

        foreach ($order_info['gift_certificates'] as $v) {
            unset($v['gift_cert_id']);
            unset($v['gift_cert_code']);
            unset($v['subtotal']);
            unset($v['display_subtotal']);
            unset($v['tax_value']);

            list($gift_cert_id, $gift_cert) = fn_add_gift_certificate_to_cart($v, $auth);

            if (!empty($gift_cert_id)) {
                $cart['gift_certificates'][$gift_cert_id] = $gift_cert;
            }
        }
    }

    return true;
}

/**
 * Add gift certificate to wishlist
 *
 * @param array $wishlist wishlist data storage
 * @param array $gift_cert_data array with data for the certificate to add
 * @return array array with gift certificate ID and data if addition is successful and empty array otherwise
 */
function fn_add_gift_certificate_to_wishlist(&$wishlist, $gift_cert_data)
{
    if (!empty($gift_cert_data) && is_array($gift_cert_data)) {
            fn_correct_gift_certificate($gift_cert_data);
            // Generate wishlist id
            $gift_cert_wishlist_id = fn_generate_gift_certificate_cart_id($gift_cert_data);
            $wishlist['gift_certificates'][$gift_cert_wishlist_id] = $gift_cert_data;
            $gift_cert_data['display_subtotal'] = $gift_cert_data['amount'];
            if (!empty($gift_cert_data['products'])) {
                $product_data = array();

                foreach ($gift_cert_data['products'] as $w_id => $_data) {
                    if (empty($_data['amount'])) {
                        unset($gift_cert_data['products'][$w_id]);
                        continue;
                    }

                    if (empty($_data['product_options'])) {
                        $_data['product_options'] = fn_get_default_product_options($_data['product_id']);
                    }

                    $wishlist_id = fn_generate_cart_id($_data['product_id'], array('product_options' => $_data['product_options'], 'parent' => array('certificate' => $gift_cert_wishlist_id)), true);
                    $product_data[$wishlist_id] = $_data;

                    $wishlist['products'][$wishlist_id] = array(
                        'product_id' => $_data['product_id'],
                        'product_options' => $_data['product_options'],
                        'amount' => $_data['amount'],
                        'extra' => array(
                            'parent' => array(
                                'certificate' => $gift_cert_wishlist_id
                            ),
                        ),
                    );

                    $product = fn_get_product_data($_data['product_id'], $_SESSION['auth']);
                    $gift_cert_data['display_subtotal'] += $_data['amount'] * $product['price'];
                }

                $gift_cert_data['products'] = $wishlist['gift_certificates'][$gift_cert_wishlist_id]['products'] = $product_data;
            }

            return array (
                $gift_cert_wishlist_id,
                $gift_cert_data
            );

    } else {
        return array();

    }
}

/**
 * Delete gift certificate from the wishlist
 *
 * @param array $wishlist wishlist data storage
 * @param int $gift_cert_wishlist_id gift certificate ID in the wishlist
 * @return boolean always true
 */
function fn_delete_wishlist_gift_certificate(&$wishlist, $gift_cert_wishlist_id)
{

    if (!empty($gift_cert_wishlist_id)) {
        $wishlist['products'] = empty($wishlist['products']) ? array() : $wishlist['products'];
        foreach ((array) $wishlist['products'] as $k=>$v) {
            if (isset($v['extra']['parent']['certificate']) && $v['extra']['parent']['certificate'] == $gift_cert_wishlist_id) {
                unset($wishlist['products'][$k]);
            }
        }
        unset($wishlist['gift_certificates'][$gift_cert_wishlist_id]);
        if (empty($wishlist['gift_certificates'])) {
            unset($wishlist['gift_certificates']);
        }
    }

    return true;
}

function fn_gift_certificates_order_placement_routines(&$order_id, &$force_notification, &$order_info, &$_error)
{
    if (in_array($order_info['status'], array('N', 'F', 'D')) && !empty($order_info['use_gift_certificates'])) {

        foreach ($order_info['use_gift_certificates'] as $k => $v) {
            db_query("UPDATE ?:gift_certificates SET status = 'A' WHERE gift_cert_id = ?i", $v['gift_cert_id']);
        }

        db_query("DELETE FROM ?:gift_certificates_log WHERE order_id = ?i", $order_id);
    }
}

function fn_gift_certificates_amazon_calculate_promotions(&$callback_response, &$cart, &$processor_data)
{
    if (isset($cart['use_gift_certificates'])) {
        foreach ($cart['use_gift_certificates'] as $gc_cert) {
            $callback_response['Promotions']['Promotion']['Benefit']['FixedAmountDiscount']['Amount'] += $gc_cert['amount'];
        }
    }
}

function fn_gift_certificates_amazon_products(&$cart_products, &$cart)
{
    if (!empty($cart['gift_certificates'])) {
        foreach ($cart['gift_certificates'] as $gc_id => $gc_data) {
            $cart_products[$gc_id] = array(
                'product_code' => 'gc_' . $gc_id,
                'product' => __('gift_certificate'),
                'price' => $gc_data['amount'],
                'amount' => 1,
            );
        }
    }
}

function fn_gift_certificates_amazon_validate_cart(&$items, &$cart, &$cart_items_amount)
{
    if (!empty($cart['gift_certificates'])) {
        $cart_items_amount += count($cart['gift_certificates']);
    }
}

function fn_gift_certificates_amazon_validate_cart_item(&$cart, &$sku, &$qty, &$cart_id, &$is_valid)
{
    if (!empty($cart['gift_certificates'][$cart_id])) {
        $is_valid = true;
    }
}

function fn_gift_certificates_display_promotion_input_field_post(&$cart, &$result)
{
    if (!empty($cart['gift_certificates'])) {
        $result = true;
    }
}

/**
 * Changes wishlist items count
 */
function fn_gift_certificates_wishlist_get_count_post(&$wishlist, &$result)
{
    if (!empty($wishlist['gift_certificates'])) {
        $result += count($wishlist['gift_certificates']);
    }

    return true;
}

function fn_gift_certificates_install($d, $action)
{
    if ($action == 'install') {
        if (fn_allowed_for('ULTIMATE')) {
            $company_ids = fn_get_all_companies_ids();
        } else {
            $company_ids = array(0);
        }

        foreach ($company_ids as $company_id) {
            fn_create_logo(array(
                'type' => 'gift_cert',
                'image_path' => fn_get_theme_path('[themes]/[theme]/mail/media/', 'C', $company_id, false) . 'images/gift_cert_logo.png',
            ), $company_id);
        }
    } else {
        fn_delete_logo('gift_cert');
    }
}

function fn_gift_certificates_logo_types(&$types, $for_company)
{
    if ($for_company == true && fn_allowed_for('MULTIVENDOR')) {
        return false;
    }

    $types['gift_cert'] = array(
        'text' => 'text_gift_certificate_logo'
    );

    return true;
}

/**
 * Apply gift certificates from cart data
 *
 * @param array $cart Array of cart content and user information necessary for purchase
 * @param array $new_cart_data Array of new data for products, totals, discounts and etc. update
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return boolean Always true
 */
function fn_gift_certificates_update_cart_by_data_post(&$cart, &$new_cart_data, &$auth)
{
    if (!empty($new_cart_data['gift_cert_code'])) {
        $company_id = Registry::get('runtime.company_id');
        if (fn_check_gift_certificate_code($new_cart_data['gift_cert_code'], true, $company_id) == true) {
            if (!isset($cart['use_gift_certificates'][$new_cart_data['gift_cert_code']])) {
                $cart['use_gift_certificates'][$new_cart_data['gift_cert_code']] = 'Y';
            }
        }
    }

    return true;
}

/**
 * Process gift certificates in cart products
 *
 * @param array $cart Array of cart content and user information necessary for purchase
 * @param array $product_data Array of new products data
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return boolean Always true
 */
function fn_gift_certificates_update_cart_products_post(&$cart, &$product_data, &$auth)
{
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $cart_id => $v) {
            if (isset($v['extra']['parent']['certificate'])) {
                $gift_certificates[$v['extra']['parent']['certificate']]['products'][$cart_id] = array(
                    'product_id' => $v['product_id'],
                    'product_options' => empty($v['extra']['product_options']) ? array() : $v['extra']['product_options'],
                    'amount' => $v['amount'],
                );
            }
        }

        if (!empty($gift_certificates)) {
            foreach ($gift_certificates as $cert_id => $cert_data) {
                $cart['gift_certificates'][$cert_id]['products'] = $cert_data['products'];
            }
        }
    }

    return true;
}

if (fn_allowed_for('ULTIMATE')) {
    function fn_gift_certificates_ult_check_store_permission($params, &$object_type, &$object_name, &$table, &$key, &$key_id)
    {
        if (Registry::get('runtime.controller') == 'gift_certificates' && !empty($params['gift_cert_id'])) {
            $key = 'gift_cert_id';
            $key_id = $params[$key];
            $table = 'gift_certificates';
            $object_name = fn_get_gift_certificate_name($key_id);
            $object_type = __('gift_certificate');
        }
    }
}

function fn_gift_certificates_paypal_express_get_order_data(&$data, &$order_data, &$product_index)
{
    if (!empty($data['gift_certificates'])) {
        foreach ($data['gift_certificates'] as $cart_id => $gift_certificate) {
            if (!empty($gift_certificate['extra']) && isset($gift_certificate['extra']['exclude_from_calculate']) && $gift_certificate['extra']['exclude_from_calculate'] == 'PR') {
                //Gift certificate was added as bonus with promotion
                continue;
            }
            $order_data['L_PAYMENTREQUEST_0_NAME' . $product_index] = __('gift_certificate');
            $order_data['L_PAYMENTREQUEST_0_NUMBER' . $product_index] = $cart_id;
            $order_data['L_PAYMENTREQUEST_0_DESC' . $product_index] = fn_paypal_express_get_certificate_data($gift_certificate);
            $order_data['L_PAYMENTREQUEST_0_QTY' . $product_index] = 1;
            $order_data['L_PAYMENTREQUEST_0_AMT' . $product_index] = $gift_certificate['amount'];

            $product_index++;
        }
    }
}

function fn_paypal_express_get_certificate_data($data)
{
    $options = array(
        __('gift_cert_to') . ': ' . $data['recipient'],
        __('gift_cert_from') . ': ' . $data['sender'],
        __('send_via') . ': ' . (($data['send_via'] == 'E') ? __('email') : __('postal_mail'))
    );

    return implode(', ', $options);
}

function fn_gift_certificates_quickbooks_export_order($order, $order_products, $spl, &$export)
{

    $order_date = fn_date_format($order['timestamp'], "%m/%d/%Y");

    if (!empty($order['gift_certificates'])) {
        foreach ($order['gift_certificates'] as $gift) {
            $export[] = sprintf($spl, $order_date,  Registry::get('addons.quickbooks.accnt_product'),
                $order['b_lastname'], $order['b_firstname'], Registry::get('addons.quickbooks.trns_class'), -$gift['amount'],
                $order['order_id'], 'GIFT CERTIFICATE:', $gift['gift_cert_code'], $gift['amount'], -1, 'GIFT CERTIFICATE', ''
            );
        }
    }

    if (!empty($order['use_gift_certificates'])) {
        foreach ($order['use_gift_certificates'] as $code => $use_gift) {
            $export[] = sprintf($spl, $order_date, Registry::get('addons.quickbooks.accnt_discount'),
                $order['b_lastname'], $order['b_firstname'], Registry::get('addons.quickbooks.trns_class'), $use_gift['cost'],
                $order['order_id'], 'GIFT CERTIFICATE:', $code, -$use_gift['cost'], -1, 'GIFT CERTIFICATE', ''
            );

        }
    }

}

function fn_gift_certificates_quickbooks_export_items($orders, $invitem, &$export)
{
    foreach ($orders as $order) {

        if (!empty($order['gift_certificates'])) {
            foreach ($order['gift_certificates'] as $gift) {
                $export[] = sprintf($invitem, $gift['gift_cert_code'], 'GIFT CERTIFICATE ', $gift['gift_cert_code'],
                    'GIFT CERTIFICATE', '', Registry::get('addons.quickbooks.accnt_product'), Registry::get('addons.quickbooks.accnt_asset'),
                    Registry::get('addons.quickbooks.accnt_cogs'), $gift['amount']);
            }
        }
    }
}

function fn_gift_certificates_change_order_status($status_to, $status_from, &$order_info, $force_notification, $order_statuses)
{
    if (isset($order_info['gift_certificates'])) {
        foreach ($order_info['gift_certificates'] as $k => $v) {
            if (!empty($order_statuses[$status_to]['params']['gift_cert_status'])) {
                fn_change_gift_certificate_status($v['gift_cert_id'], $order_statuses[$status_to]['params']['gift_cert_status'], '', fn_get_notification_rules(array(), true)); // skip notification, it will be sent later in order_notification hook
            }
        }
    }
}

function fn_gift_certificates_paypal_apply_discount_post(&$data, &$order_data, &$product_index, &$discount_applied)
{
    $gc_total_amount = 0;
    if (isset($data['use_gift_certificates']) && !empty($data['use_gift_certificates'])) {
        foreach ($data['use_gift_certificates'] as $gc_code => $gc_data) {
            if ($gc_data['amount'] > 0) {
                $gc_total_amount += $gc_data['amount'];
            }
        }
    }

    if ($gc_total_amount) {
        if ($discount_applied) {
            $product_index++;
        }
        if (isset($order_data['L_PAYMENTREQUEST_0_AMT' . $product_index])) {
            $order_data['L_PAYMENTREQUEST_0_AMT' . $product_index] -= $gc_total_amount;
        } else {
            $order_data['L_PAYMENTREQUEST_0_NAME' . $product_index] = __('gift_certificate');
            $order_data['L_PAYMENTREQUEST_0_QTY' . $product_index] = 1;
            $order_data['L_PAYMENTREQUEST_0_AMT' . $product_index] = -$gc_total_amount;
        }
        $order_data['PAYMENTREQUEST_0_ITEMAMT'] -= $gc_total_amount;
        $discount_applied = true;
    }
}
