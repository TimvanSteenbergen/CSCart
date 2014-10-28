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

use Tygh\Database;
use Tygh\Exceptions\DeveloperException;
use Tygh\Mailer;
use Tygh\Navigation\LastView;
use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

fn_define('COUPON_CODE_LENGTH', 8);
fn_define('PROMOTION_MIN_MATCHES', 5);

/**
 * Get promotions
 *
 * @param array $params array with search params
 * @param int $items_per_page
 * @param string $lang_code
 * @return array list of promotions in first element, filtered parameters in second
 */
function fn_get_promotions($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    // Init filter
    $params = LastView::instance()->update('promotions', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page,
        'get_hidden' => true
    );

    $params = array_merge($default_params, $params);

    // Define fields that should be retrieved
    $fields = array (
        "?:promotions.*",
        "?:promotion_descriptions.name",
        "?:promotion_descriptions.detailed_description",
        "?:promotion_descriptions.short_description",
    );

    // Define sort fields
    $sortings = array (
        'name' => "?:promotion_descriptions.name",
        'priority' => "?:promotions.priority",
        'zone' => "?:promotions.zone",
        'status' => "?:promotions.status",
    );

    $condition = $join = $group = '';

    $condition .= fn_get_company_condition('?:promotions.company_id');

    $statuses = array('A');
    if (!empty($params['get_hidden'])) {
        $statuses[] = 'H';
    }

    if (!empty($params['promotion_id'])) {
        $condition .= db_quote(' AND ?:promotions.promotion_id IN (?n)', $params['promotion_id']);
    }

    if (!empty($params['active'])) {
        $condition .= db_quote(" AND IF(from_date, from_date <= ?i, 1) AND IF(to_date, to_date >= ?i, 1) AND status IN (?a)", TIME, TIME, $statuses);
    }

    if (fn_allowed_for('ULTIMATE:FREE')) {
        $params['zone'] = 'catalog';
    }

    if (!empty($params['zone'])) {
        $condition .= db_quote(" AND ?:promotions.zone = ?s", $params['zone']);
    }

    if (!empty($params['coupon_code'])) {
        $condition .= db_quote(
            " AND (CONCAT(LOWER(?:promotions.conditions_hash), ';') LIKE ?l OR CONCAT(LOWER(?:promotions.conditions_hash), ';') LIKE ?l)",
            "%coupon_code={$params['coupon_code']};%",
            "%auto_coupons={$params['coupon_code']};%"
        );
    }

    if (!empty($params['coupons'])) {
        $condition .= db_quote(" AND ?:promotions.conditions_hash LIKE ?l", "%coupon_code=%");
    }

    if (!empty($params['auto_coupons'])) {
        $condition .= db_quote(" AND ?:promotions.conditions_hash LIKE ?l", "%auto_coupons=%");
    }

    $join .= db_quote(" LEFT JOIN ?:promotion_descriptions ON ?:promotion_descriptions.promotion_id = ?:promotions.promotion_id AND ?:promotion_descriptions.lang_code = ?s", $lang_code);

    fn_set_hook('get_promotions', $params, $fields, $sortings, $condition, $join);

    $sorting = db_sort($params, $sortings, 'name', 'desc');

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:promotions $join WHERE 1 $condition $group");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    if (!empty($params['simple'])) {
        return db_get_hash_single_array("SELECT ?:promotions.promotion_id, ?:promotion_descriptions.name FROM ?:promotions $join WHERE 1 $condition $group $sorting $limit", array('promotion_id', 'name'));
    } else {
        $promotions = db_get_hash_array('SELECT ' . implode(', ', $fields) . " FROM ?:promotions $join WHERE 1 $condition $group $sorting $limit", 'promotion_id');
    }

    if (!empty($params['expand'])) {
        foreach ($promotions as $k => $v) {
            $promotions[$k]['conditions'] = !empty($v['conditions']) ? unserialize($v['conditions']) : array();
            $promotions[$k]['bonuses'] = !empty($v['bonuses']) ? unserialize($v['bonuses']) : array();
        }
    }

    return array($promotions, $params);
}

/**
 * Apply promotion rules
 *
 * @param array $data data array (product - for catalog rules, cart - for cart rules)
 * @param string $zone - promotiontion zone (catalog, cart)
 * @param array $cart_products (optional) - cart products array (for car rules)
 * @param array $auth (optional) - auth array (for car rules)
 * @return bool true if rule can be applied, false - otherwise
 */
function fn_promotion_apply($zone, &$data, &$auth = NULL, &$cart_products = NULL)
{
    static $promotions = array();
    $applied_promotions = array();

    if (!isset($promotions[$zone])) {
        $params = array(
            'active' => true,
            'expand' => true,
            'zone' => $zone,
            'sort_by' => 'priority',
            'sort_order' => 'asc'
        );

        list($promotions[$zone]) = fn_get_promotions($params);
    }

    // If we're in cart, set flag that promotions available
    if ($zone == 'cart') {
        $_promotion_ids = !empty($data['promotions']) ? array_keys($data['promotions']) : array();
        $data['no_promotions'] = empty($promotions[$zone]);
        $data['promotions'] = array(); // cleanup stored promotions
        $data['subtotal_discount'] = 0; // reset subtotal discount (FIXME: move to another place)
        $data['has_coupons'] = false;
    }

    /**
     * Changes before applying promotion rules
     *
     * @param array  $promotions    List of promotions
     * @param string $zone          - promotiontion zone (catalog, cart)
     * @param array  $data          data array (product - for catalog rules, cart - for cart rules)
     * @param array  $auth          (optional) - auth array (for car rules)
     * @param array  $cart_products (optional) - cart products array (for car rules)
     */
    fn_set_hook('promotion_apply_pre', $promotions, $zone, $data, $auth, $cart_products);

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if ($zone == 'cart') {
            // Delete obsolete discounts
            foreach ($cart_products as $p_id => $_val) {
                $data['products'][$p_id]['discount'] = !empty($_val['discount']) ? $_val['discount'] : 0;
                $data['products'][$p_id]['promotions'] = !empty($_val['promotions']) ? $_val['promotions'] : array();
            }

            // Summarize discounts
            foreach ($cart_products as $k => $v) {
                if (!empty($v['promotions'])) {
                    foreach ($v['promotions'] as $pr_id => $bonuses) {
                        foreach ($bonuses['bonuses'] as $bonus) {
                            if (!empty($bonus['discount'])) {
                                $data['promotions'][$pr_id]['total_discount'] = (!empty($data['promotions'][$pr_id]['total_discount']) ? $data['promotions'][$pr_id]['total_discount'] : 0) + ($bonus['discount'] * $v['amount']);
                            }
                        }
                    }
                }
            }

            $data['no_promotions'] = $data['no_promotions'] && empty($data['promotions']);
        }
    }

    if (empty($promotions[$zone])) {
        return false;
    }

    $_SESSION['promotion_notices']['promotion'] = array(
        'applied' => false,
        'messages' => array()
    );

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        // Pre-check coupon
        if ($zone == 'cart' && !empty($data['pending_coupon'])) {
            fn_promotion_check_coupon($data, true);
        }
    }
    foreach ($promotions[$zone] as $promotion) {
        // Rule is valid and can be applied
        if (fn_promotion_check($promotion['promotion_id'], $promotion['conditions'], $data, $auth, $cart_products)) {
            if (fn_promotion_apply_bonuses($promotion, $data, $auth, $cart_products)) {
                $applied_promotions[$promotion['promotion_id']] = $promotion;

                // Stop processing further rules, if needed
                if ($promotion['stop'] == 'Y') {
                    break;
                }
            }
        }
    }

    if (!fn_allowed_for('ULTIMATE:FREE')) {
        if ($zone == 'cart') {

            // Post-check coupon
            if (!empty($data['pending_coupon'])) {
                // re-check coupons if some promotion has a coupon code "contains" condition
                if (!empty($data['pending_original_coupon'])) {
                    unset($data['coupons'][$data['pending_coupon']]);
                    $data['pending_coupon'] = $data['pending_original_coupon'];
                    unset($data['pending_original_coupon']);
                    fn_promotion_check_coupon($data, true);
                }

                fn_promotion_check_coupon($data, false, $applied_promotions);
            }

            if (!empty($applied_promotions)) {
                // Display notifications for new promotions
                $_text = array();
                foreach ($applied_promotions as $v) {
                    if (!in_array($v['promotion_id'], $_promotion_ids)) {
                        $_text[] = $v['name'];
                    }
                }

                if (!empty($_text)) {
                    $_SESSION['promotion_notices']['promotion']['applied'] = true;
                    $_SESSION['promotion_notices']['promotion']['messages'][] = 'text_applied_promotions';
                    $_SESSION['promotion_notices']['promotion']['applied_promotions'] = $_text;
                }

                $data['applied_promotions'] = $applied_promotions;

                // Delete obsolete coupons
                if (!empty($data['coupons'])) {
                    foreach ($data['coupons'] as $_coupon_code => $_p_ids) {
                        foreach ($_p_ids as $_ind => $_p_id) {
                            if (!isset($applied_promotions[$_p_id])) {
                                unset($data['coupons'][$_coupon_code][$_ind]);
                            }
                        }
                        if (empty($data['coupons'][$_coupon_code])) {
                            unset($data['coupons'][$_coupon_code]);
                        }
                    }
                }

            } else {
                $data['coupons'] = array();
            }
        }
    }

    return $applied_promotions;
}

/**
 * Checks and clears error promotion notices if promotion applied
 *
 * @return boolean Always true
 */
function fn_check_promotion_notices()
{
    $was_applied = false;

    if (isset($_SESSION['promotion_notices']) && !empty($_SESSION['promotion_notices'])) {
        foreach ($_SESSION['promotion_notices'] as $key => $value) {
            if ($value['applied']) {
                foreach ($value['messages'] as $message) {
                    if (!empty($message) && !empty($value['applied_promotions'])) {
                        fn_set_notification('N', __('notice'), __($message) . ': ' . implode(', ', $value['applied_promotions']), '', $message);
                    } elseif (!empty($message) && empty($value['applied_promotions'])) {
                        fn_set_notification('N', __('notice'), __($message), '', $message);
                    }
                }
                $was_applied = true;
                unset($_SESSION['promotion_notices']);
                break;
            }
        }
    }

    if (!$was_applied && !empty($_SESSION['promotion_notices'])) {
        foreach ($_SESSION['promotion_notices'] as $addon => $notices) {
            if (!empty($notices['messages'])) {
                foreach ($notices['messages'] as $message_key) {
                    fn_set_notification('W', __('warning'), __($message_key), '', $message_key);
                    break;
                }
                unset($_SESSION['promotion_notices']);
                break;
            }
        }
    }

    return true;
}

/**
 * Apply discount to the product
 *
 * @param int $promotion_id promotion ID
 * @param array $bonus promotion bonus
 * @param array $product product array (product - for catalog rules, cart - for cart rules)
 * @param bool $use_base use base price or with applied discounts
 * @return bool true if rule can be applied, false - otherwise
 */

function fn_promotion_apply_discount($promotion_id, $bonus, &$product, $use_base = true)
{
    if (!isset($product['promotions'])) {
        $product['promotions'] = array();
    }

    if (!isset($product['discount'])) {
        $product['discount'] = 0;
    }

    if (!isset($product['base_price'])) {
        $product['base_price'] = $product['price'];
    }

    $base_price = ($use_base == true) ? $product['base_price'] + (empty($product['modifiers_price']) ? 0 : $product['modifiers_price']) : $product['price'];

    $discount = fn_promotions_calculate_discount($bonus['discount_bonus'], $base_price, $bonus['discount_value'], $product['price']);
    $discount = fn_format_price($discount);

    $product['discount'] += $discount;
    $product['price'] -= $discount;

    if ($product['price'] < 0) {
        $product['discount'] += $product['price'];
        $product['price'] = 0;
    }

    $product['promotions'][$promotion_id]['bonuses'][] = array (
        'discount_bonus' =>	$bonus['discount_bonus'],
        'discount_value' => $bonus['discount_value'],
        'discount' => $product['discount']
    );

    if (isset($product['subtotal'])) {
        $product['subtotal'] = $product['price'] * $product['amount'];
    }

    if (!empty($base_price)) {
        $product['discount_prc'] = sprintf('%d', round($product['discount'] * 100 / $base_price));
    } else {
        $product['discount_prc'] = 0;
    }

    return true;
}

/**
 * Apply promotion catalog rule
 *
 * @param array $promotion promotion array
 * @param array $product product array (product - for catalog rules, cart - for cart rules)
 * @param array $auth (optional) - auth array
 * @return bool true if rule can be applied, false - otherwise
 */
function fn_promotion_apply_catalog_rule($bonus, &$product, &$auth)
{
    if ($bonus['bonus'] == 'product_discount') {
        if (!isset($product['extra']['promotions'][$bonus['promotion_id']]) && !isset($product['promotions'][$bonus['promotion_id']])) {
            fn_promotion_apply_discount($bonus['promotion_id'], $bonus, $product);
        }
    }

    return true;
}


/**
 * Apply promotion cart rule
 *
 * @param array $promotion promotion array
 * @param array $cart cart array
 * @param array $auth (optional) - auth array
 * @param array $cart_products (optional) - cart products array (for cart rules)
 * @return bool true if rule can be applied, false - otherwise
 */
function fn_promotion_apply_cart_rule($bonus, &$cart, &$auth, &$cart_products)
{
    // Clean bonuses
    if (!isset($cart['promotions'][$bonus['promotion_id']]['bonuses'])) {
        $cart['promotions'][$bonus['promotion_id']]['bonuses'] = array();
    }
    $bonus_id = count($cart['promotions'][$bonus['promotion_id']]['bonuses']);
    $cart['promotions'][$bonus['promotion_id']]['bonuses'][$bonus_id] = $bonus;

    if ($bonus['bonus'] == 'order_discount') {
        if (floatval($cart['subtotal'])) {
            if (!isset($cart['subtotal_discount'])) {
                $cart['subtotal_discount'] = 0;
            }

            if (fn_allowed_for('MULTIVENDOR')) {
                $discount = fn_promotions_calculate_order_discount($bonus, $bonus_id, $cart);
            } else {
                $discount = fn_promotions_calculate_discount($bonus['discount_bonus'], $cart['subtotal'], $bonus['discount_value']);
            }

            if (floatval($discount)) {
                $cart['use_discount'] = true;
                $cart['subtotal_discount'] += fn_format_price($discount);
            }
        }

    } elseif ($bonus['bonus'] == 'discount_on_products') {
        foreach ($cart_products as $k => $v) {
            if (isset($v['exclude_from_calculate']) || (!floatval($v['base_price']) && $v['base_price'] != 0)) {
                continue;
            }

            if (fn_promotion_validate_attribute($v['product_id'], $bonus['value'], 'in') && !isset($cart['products'][$k]['extra']['promotions'][$bonus['promotion_id']])) {
                if (fn_promotion_apply_discount($bonus['promotion_id'], $bonus, $cart_products[$k])) {
                    $cart['use_discount'] = true;
                }
            }
        }

    } elseif ($bonus['bonus'] == 'discount_on_categories') {
        foreach ($cart_products as $k => $v) {
            if (isset($v['exclude_from_calculate']) || (!floatval($v['base_price']) && $v['base_price'] != 0)) {
                continue;
            }

            if (fn_promotion_validate_attribute($v['category_ids'], $bonus['value'], 'in') && !isset($cart['products'][$k]['extra']['promotions'][$bonus['promotion_id']])) {
                if (fn_promotion_apply_discount($bonus['promotion_id'], $bonus, $cart_products[$k])) {
                    $cart['use_discount'] = true;
                }
            }
        }

    } elseif ($bonus['bonus'] == 'give_usergroup') {
        $cart['promotions'][$bonus['promotion_id']]['bonuses'][$bonus_id]['pending'] = true;

    } elseif ($bonus['bonus'] == 'give_coupon') {
        $cart['promotions'][$bonus['promotion_id']]['bonuses'][$bonus_id]['pending'] = true;
        $cart['promotions'][$bonus['promotion_id']]['bonuses'][$bonus_id]['coupon_code'] = fn_generate_code('', COUPON_CODE_LENGTH);

    } elseif ($bonus['bonus'] == 'free_shipping') {

        $cart['free_shipping'][] = $bonus['value'];

    } elseif ($bonus['bonus'] == 'free_products') {

        foreach ($bonus['value'] as $p_data) {

            $product_data = array (
                $p_data['product_id'] => array (
                    'amount' => $p_data['amount'],
                    'product_id' => $p_data['product_id'],
                    'extra' => array (
                        'exclude_from_calculate' => true,
                        'aoc' => empty($p_data['product_options']),
                        'saved_options_key' => $bonus['promotion_id'] . '_' . $p_data['product_id'],
                    )
                ),
            );

            if (!empty($cart['saved_product_options'][$bonus['promotion_id'] . '_' . $p_data['product_id']])) {
                $product_data[$p_data['product_id']]['product_options'] = $cart['saved_product_options'][$bonus['promotion_id'] . '_' . $p_data['product_id']];
            } elseif (!empty($p_data['product_options'])) {
                $product_data[$p_data['product_id']]['product_options'] = $p_data['product_options'];
            }

            // Restore object_id if needed
            if (!empty($cart['saved_object_ids'][$bonus['promotion_id'] . '_' . $p_data['product_id']])) {
                $product_data[$p_data['product_id']]['saved_object_id'] = $cart['saved_object_ids'][$bonus['promotion_id'] . '_' . $p_data['product_id']];
            }

            $existing_products = array_keys($cart['products']);

            if (!fn_allowed_for('ULTIMATE') || (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id') && (fn_check_company_id('products', 'product_id', $p_data['product_id'], Registry::get('runtime.company_id')) || fn_ult_is_shared_product($p_data['product_id'], Registry::get('runtime.company_id')) == 'Y'))) {

                if ($ids = fn_add_product_to_cart($product_data, $cart, $auth)) {
                    $new_products = array_diff(array_keys($cart['products']), $existing_products);
                    if (!empty($new_products)) {
                        $hash = array_pop($new_products);
                    } else {
                        $hash = key($ids);
                    }

                    $_cproduct = fn_get_cart_product_data($hash, $cart['products'][$hash], true, $cart, $auth, !empty($new_products) ? 0 : $p_data['amount']);
                    if (!empty($_cproduct)) {
                        $cart_products[$hash] = $_cproduct;
                    }
                }
            }
        }
    }

    return true;
}

/**
 * Check promotiontion conditions
 *
 * @param int $promotion_id promotion ID
 * @param array $condition conditions set
 * @param array $data data array
 * @param array $auth auth array (for cart rules)
 * @param array $cart_products cart products array (for cart rules)
 * @return bool true if promotion can be applied, false - otherwise
 */
function fn_promotion_check($promotion_id, $condition, &$data, &$auth, &$cart_products)
{
    // This is unconditional promotiontion
    if (empty($condition)) {
        return true;
    }

    // if this is the conditions group, check each condition in cycle
    if (!empty($condition['conditions'])) {
        foreach ($condition['conditions'] as $cond) {
            if (!empty($cond['condition']) && ($cond['condition'] == 'coupon_code' || $cond['condition'] == 'auto_coupons')) {
                $data['has_coupons'] = true;
            }

            if (!empty($cond['conditions'])) {
                $c_res = fn_promotion_check($promotion_id, $cond, $data, $auth, $cart_products);
            } else {
                $c_res = fn_promotion_validate($promotion_id, $cond, $data, $auth, $cart_products);
            }

            if (!isset($result)) {
                $result = $c_res;
            }

            // Check result, if any condition is correct
            if ($condition['set'] == 'any' && $c_res == $condition['set_value']) {
               return true;

            // If we need to compare all conditions, summ the result
            } elseif ($condition['set'] == 'all') {
                $result = $result & $c_res;
            }
        }

        return ($condition['set_value'] == true) ? $result : !$result;

    // If this is the ordinary condition, check it directly
    } else {
        return fn_promotion_validate($promotion_id, $condition, $data, $auth, $cart_products);
    }
}

/**
 * Validate rule
 *
 * @param int $promotion_id promotion ID
 * @param array $promotion rule data
 * @param array $data data array
 * @param array $auth auth array (for cart rules)
 * @param array $cart_products cart products array (for cart rules)
 * @return bool true if rule can be applied, false - otherwise
 */
function fn_promotion_validate($promotion_id, $promotion, &$data, &$auth, &$cart_products)
{
    $schema = fn_promotion_get_schema('conditions');

    $stop_validating = false;
    $result = true;
    static $parent_orders = array();

    fn_set_hook('pre_promotion_validate', $promotion_id, $promotion, $data, $stop_validating, $result, $auth, $cart_products);

    if ($stop_validating) {
        return $result;
    }

    if (empty($promotion['condition'])) { // if promotion is unconditional, apply it

        return true;
    }

    $promotion['value'] = !isset($promotion['value']) ? '' : $promotion['value'];

    if (!empty($schema[$promotion['condition']])) {
        $value = '';
        $parent_order_value = '';

        if (!empty($data['parent_order_id']) && empty($parent_orders[$data['parent_order_id']])) {
            $parent_orders[$data['parent_order_id']] = fn_get_order_info($data['parent_order_id']);
        }

        // Ordinary field
        if (!empty($schema[$promotion['condition']]['field'])) {

            // Array definition, parse it
            if (strpos($schema[$promotion['condition']]['field'], '@') === 0) {
                $value = fn_promotion_get_object_value($schema[$promotion['condition']]['field'], $data, $auth, $cart_products);
            } else {

                // If field can be used in both zones, it means that we're using products
                if (in_array('catalog', $schema[$promotion['condition']]['zones']) && in_array('cart', $schema[$promotion['condition']]['zones']) && !empty($cart_products)) {// this is the "cart" zone. FIXME!!!
                    foreach ($cart_products as $v) {
                        if ($promotion['operator'] == 'nin') {
                            if (fn_promotion_validate_attribute($v[$schema[$promotion['condition']]['field']], $promotion['value'], 'in')) {
                                return false;
                            }
                        } else {
                            if (fn_promotion_validate_attribute($v[$schema[$promotion['condition']]['field']], $promotion['value'], $promotion['operator'])) {
                                return true;
                            }
                        }
                    }

                    return $promotion['operator'] == 'nin' ? true : false;
                }

                if (!isset($data[$schema[$promotion['condition']]['field']])) {
                    return false;
                }

                $value = $data[$schema[$promotion['condition']]['field']];

                if (!empty($data['parent_order_id']) && !empty($parent_orders[$data['parent_order_id']][$schema[$promotion['condition']]['field']])) {
                    $parent_order_value = $parent_orders[$data['parent_order_id']][$schema[$promotion['condition']]['field']];
                }
            }

        // Field is the result of function
        } elseif (!empty($schema[$promotion['condition']]['field_function'])) {
            $p = $schema[$promotion['condition']]['field_function'];
            $func = array_shift($p);
            $p_orig = $p;

            // If field can be used in both zones, it means that we're using products
            if (in_array('catalog', $schema[$promotion['condition']]['zones']) && in_array('cart', $schema[$promotion['condition']]['zones']) && !empty($cart_products)) { // this is the "cart" zone. FIXME!!!
                foreach ($cart_products as $product) {
                    $p = $p_orig;
                    foreach ($p as $k => $v) {
                        if (strpos($v, '@') !== false) {
                           $p[$k] = & fn_promotion_get_object_value($v, $product, $auth, $cart_products);
                        } elseif ($v == '#this') {
                            $p[$k] = & $promotion;
                        } elseif ($v == '#id') {
                            $p[$k] = & $promotion_id;
                        }
                    }

                    $value = call_user_func_array($func, $p);

                    if ($promotion['operator'] == 'nin') {
                        if (fn_promotion_validate_attribute($value, $promotion['value'], 'in')) {
                            return false;
                        }
                    } else {
                        if (fn_promotion_validate_attribute($value, $promotion['value'], $promotion['operator'])) {
                            return true;
                        }
                    }
                }

                return $promotion['operator'] == 'nin' ? true : false;
            }

            foreach ($p as $k => $v) {
                if (strpos($v, '@') !== false) {
                   $p[$k] = & fn_promotion_get_object_value($v, $data, $auth, $cart_products);
                } elseif ($v == '#this') {
                    $p[$k] = & $promotion;
                } elseif ($v == '#id') {
                    $p[$k] = & $promotion_id;
                }
            }

            $value = call_user_func_array($func, $p);

            if (!empty($data['parent_order_id']) && !empty($parent_orders[$data['parent_order_id']])) {
                $parent_p = $p_orig;
                foreach ($parent_p as $k => $v) {
                    if (strpos($v, '@') !== false) {
                        $parent_p[$k] = & fn_promotion_get_object_value($v, $parent_orders[$data['parent_order_id']], $auth, $cart_products);
                    } elseif ($v == '#this') {
                        $parent_p[$k] = & $promotion;
                    } elseif ($v == '#id') {
                        $parent_p[$k] = & $promotion_id;
                    }
                }

                $parent_order_value = call_user_func_array($func, $parent_p);
            }
        }

        // Value is validated
        $result = fn_promotion_validate_attribute($value, $promotion['value'], $promotion['operator']);

        if ($parent_order_value) {
            $result = $result || fn_promotion_validate_attribute($parent_order_value, $promotion['value'], $promotion['operator']);
        }

        return $result;
    }

    return false;
}

/**
 * Get object value by path
 *
 * @param string $path path to object value
 * @param array $data data array
 * @param array $auth auth array (for cart rules)
 * @param array $cart_products cart products array (for cart rules)
 * @return mixed object value, dies if path does not exist
 */
function & fn_promotion_get_object_value($path, &$data, &$auth, &$cart_products = NULL)
{
    $p = explode('.', $path);
    $object = array_shift($p);
    if ($object == '@cart' || $object == '@product') {
        $obj = & $data;
    } elseif ($object == '@auth') {
        $obj = & $auth;
    } elseif ($object == '@cart_products') {
        $obj = & $cart_products;
    } else {
        throw new DeveloperException("Promotions : object '$object' is not implemented");
    }

    foreach ($p as $v) {
        if (!isset($obj[$v])) {
            $obj[$v] = array(); // FIXME?? Is it correct? //die("promotion:incorrect_key[$v]");
        }

        $obj = & $obj[$v];
    }

    return $obj;
}

/**
 * Validate attribute
 *
 * @param mixed $val value to compare with (can be one-dimensional array, in this case, every item will be checked)
 * @param mixed $condition value to compare to
 * @param string $op compare operator
 * @return bool true in success, false - otherwise
 */
function fn_promotion_validate_attribute($value, $condition, $op)
{
    $result = false;

    fn_set_hook('pre_validate_promotion_attribute', $value, $condition, $op, $result);

    if (!isset($condition)) { // condition can't be empty, I think...

        return false;
    }

    $val = !is_array($value) ? array($value) : $value;

    if ($op == 'neq') {
        return !in_array($condition, $val);
    }

    foreach ($val as $v) {
        if ($op == 'eq') {
            $result = ($v == $condition);

        } elseif ($op == 'lte') {
            $result = ($v <= $condition);

        } elseif ($op == 'lt') {
            $result = ($v < $condition);

        } elseif ($op == 'gte') {
            $result = ($v >= $condition);

        } elseif ($op == 'gt') {
            $result = ($v > $condition);

        } elseif ($op == 'cont') {
            $result = (stripos((string) $v, (string) $condition) !== false);

        } elseif ($op == 'ncont') {
            $result = (stripos((string) $v, (string) $condition) === false);

        } elseif ($op == 'in') {
            $condition = is_array($condition) ? $condition : fn_explode(',', $condition);
            if (is_array($v)) {
                foreach ($condition as $item) {
                    if (sizeof($v) != sizeof($item)) {
                        if (sizeof(array_intersect_assoc($v, $item)) == sizeof($item)) {
                            $result = true;
                            break;
                        }
                    } else {
                        array_multisort($v);
                        array_multisort($item);
                        if ($v == $item) {
                            $result = true;
                            break;
                        }
                    }
                }
            } else {
                $result = in_array($v, $condition, is_bool($v));
            }

        } elseif ($op == 'nin') {
            $condition = is_array($condition) ? $condition : fn_explode(',', $condition);
            if (is_array($v)) {
                $result = true;
                foreach ($condition as $item) {
                    if (sizeof($v) != sizeof($item)) {
                        if (sizeof(array_intersect_assoc($v, $item)) == sizeof($item)) {
                            $result = false;
                            break;
                        }
                    } else {
                        array_multisort($v);
                        array_multisort($item);
                        if ($v == $item) {
                            $result = false;
                            break;
                        }
                    }
                }
            } else {
                $result = !in_array($v, $condition);
            }
        }

        if (!empty($result)) {
            return true;
        }
    }

    return false;
}

/**
 * Apply promotiontion bonuses
 *
 * @param array $promotion promotiontion data
 * @param array $data data array
 * @param array $auth auth array
 * @param array $cart_products cart products
 * @return bool true in success, false - otherwise
 */
function fn_promotion_apply_bonuses($promotion, &$data, &$auth, &$cart_products)
{
    $schema = fn_promotion_get_schema('bonuses');
    $can_apply = false;
    if (!empty($cart_products)) { // FIXME: this is cart
        $data['promotions'][$promotion['promotion_id']]['bonuses'] = array();
    }

    foreach ($promotion['bonuses'] as $bonus) {
        if (!empty($schema[$bonus['bonus']])) {
            $p = $schema[$bonus['bonus']]['function'];

            $func = array_shift($p);

            foreach ($p as $k => $v) {
                if ($v == '#this') {
                    $bonus['promotion_id'] = $promotion['promotion_id'];
                    $p[$k] = & $bonus;

                } elseif (strpos($v, '@') === 0) {
                    $p[$k] = & fn_promotion_get_object_value($v, $data, $auth, $cart_products);
                }
            }

            if (call_user_func_array($func, $p) == true) {
                $can_apply = true;
            }
        }
    }

    if (!empty($cart_products) && $can_apply == false) { // FIXME: this is cart
        unset($data['promotions'][$promotion['promotion_id']]);
    }

    return $can_apply;
}

/**
 * Get promotion schema
 *
 * @param string $type schema type (conditions, bonuses)
 * @return array schema of definite type
 */
function fn_promotion_get_schema($type = '')
{
    static $schema = array();

    if (empty($schema)) {
        $schema = fn_get_schema('promotions', 'schema');
    }

    return !empty($type) ? $schema[$type] : $schema;
}

/**
 * Distribute fixed discount amount all products
 *
 * @param array $cart_products products list
 * @param float $value discount for distribution
 * @param bool $use_base use base price for calculation or with applied discounts
 * @return array discounts list
 */
function fn_promotion_distribute_discount(&$cart_products, $value, $use_base = true)
{
    // Calculate subtotal
    $subtotal = 0;
    foreach ($cart_products as $k => $v) {
        if (isset($v['exclude_from_calculate'])) {
            continue;
        }
        $subtotal += (($use_base == true) ? $v['base_price'] : $v['price']) * $v['amount'];
    }

    // Calculate discount for each product
    $discount = array();

    foreach ($cart_products as $k => $v) {
        if (isset($v['exclude_from_calculate'])) {
            continue;
        }
        $discount[$k] = fn_format_price(((($use_base == true) ? $v['base_price'] : $v['price']) / $subtotal) * $value);
    }

    $sum = array_sum($discount);

    // If sum of distributed values does not equal to total discount, correct it
    /*if ($sum != $value) {
        $diff = $sum - $value;

        foreach ($discount as $k => $v) {
            if ($v + $sum - $value > 0) {
                $discount[$k] = $v + $sum - $value;
                break;
            }
        }
    } */

    return $discount;
}

/**
 * Determines if the status is positive or negative
 *
 * @param array $status status
 * @param bool $exclude_open Do not consider the "Open" status as decreased
 * @return boolean if status inventory param is 'Decreasing' and status is not 'Open' then true
 */
function fn_status_is_positive($status, $exclude_open = false)
{
    $extra_condition = $exclude_open ? true : $status['status'] != 'O';

    return isset($status['params']) && isset($status['status']) && $status['params']['inventory'] == 'D' && $extra_condition;
}

/**
 * Promotions post processing
 *
 * @param string $status_to new order status
 * @param string $status_from original order status
 * @param array $order_info order information
 * @param array $force_notification Array with notification rules
 * @return boolean always true
 */
function fn_promotion_post_processing($status_to, $status_from, $order_info, $force_notification = array())
{
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true);

    $notify_user = isset($force_notification['C']) ? $force_notification['C'] : (!empty($order_statuses[$status_to]['params']['notify']) && $order_statuses[$status_to]['params']['notify'] == 'Y' ? true : false);

    $status_from_is_positive = fn_status_is_positive($order_statuses[$status_from]);
    $status_to_is_positive = fn_status_is_positive($order_statuses[$status_to]);

    if (empty($order_info['promotions'])) {
        return false;
    }

    // Process numbers of usage for Open statuses
    if ($status_to != $status_from && fn_status_is_positive($order_statuses[$status_from], true) != fn_status_is_positive($order_statuses[$status_to], true)) {
        // Post processing
        if (fn_status_is_positive($order_statuses[$status_to], true)) {
            db_query("UPDATE ?:promotions SET number_of_usages = number_of_usages + 1 WHERE promotion_id IN (?n)", array_keys($order_info['promotions']));
        } else {
            db_query("UPDATE ?:promotions SET number_of_usages = number_of_usages - 1 WHERE promotion_id IN (?n)", array_keys($order_info['promotions']));
        }
    }

    if ($status_to != $status_from && $status_from_is_positive != $status_to_is_positive) {
        // Apply pending actions
        foreach ($order_info['promotions'] as $k => $v) {
            if (!empty($v['bonuses'])) {
                foreach ($v['bonuses'] as $bonus) {
                    // Assign usergroup
                    if ($bonus['bonus'] == 'give_usergroup') {
                        $is_ug_already_assigned = false;
                        if (empty($order_info['user_id'])) {
                            continue;
                        }

                        // Don't assing a disabled usergroup
                        $system_usergroups = fn_get_usergroups('C', CART_LANGUAGE);
                        if (!empty($system_usergroups[$bonus['value']]['status']) && $system_usergroups[$bonus['value']]['status'] == 'A') {
                            if ($order_statuses[$status_to]['params']['inventory'] == 'D') {

                                // Don't assing the usergroup to the user if it's already assigned
                                $current_user_usergroups = fn_get_user_usergroups($order_info['user_id']);

                                foreach ($current_user_usergroups as $ug) {
                                    if (isset($ug['usergroup_id']) && $bonus['value'] == $ug['usergroup_id'] && $ug['status'] == 'A') {
                                        $is_ug_already_assigned = true;
                                        break;
                                    }
                                }
                                if (!$is_ug_already_assigned) {
                                    db_query("REPLACE INTO ?:usergroup_links SET user_id = ?i, usergroup_id = ?i, status = 'A'", $order_info['user_id'], $bonus['value']);
                                    $activated = true;
                                }
                            } else {
                                db_query("UPDATE ?:usergroup_links SET status = 'F' WHERE user_id = ?i AND usergroup_id = ?i", $order_info['user_id'], $bonus['value']);
                                $activated = false;
                            }

                            if ($notify_user == true && !$is_ug_already_assigned) {
                                $prefix = ($activated == true) ? 'activation' : 'disactivation';

                                Mailer::sendMail(array(
                                    'to' => $order_info['email'],
                                    'from' => 'company_users_department',
                                    'data' => array(
                                        'user_data' => fn_get_user_info($order_info['user_id']),
                                        'usergroups' => fn_get_usergroups('F', $order_info['lang_code']),
                                        'usergroup_ids' => (array) $bonus['value']
                                    ),
                                    'tpl' => 'profiles/usergroup_' . $prefix . '.tpl',
                                    'company_id' => $order_info['company_id'],
                                ), 'C', $order_info['lang_code']);
                            }
                        } else {
                            if (AREA == 'C') {
                                fn_set_notification('E', __('error'), __('unable_to_assign_usergroup'));
                            }
                        }

                    } elseif ($bonus['bonus'] == 'give_coupon') {

                        $promotion_data = fn_get_promotion_data($bonus['value']);
                        if (empty($promotion_data)) {
                            continue;
                        }


                        if ($status_to_is_positive) {

                            fn_promotion_update_condition($promotion_data['conditions']['conditions'], 'add', 'auto_coupons', $bonus['coupon_code']);

                            if ($notify_user == true) {

                                Mailer::sendMail(array(
                                    'to' => $order_info['email'],
                                    'from' => 'company_users_department',
                                    'data' => array(
                                        'promotion_data' => $promotion_data,
                                        'bonus_data' => $bonus,
                                        'order_info' => $order_info
                                    ),
                                    'tpl' => 'promotions/give_coupon.tpl',
                                    'company_id' => $order_info['company_id'],
                                ), 'C', $order_info['lang_code']);
                            }

                        } else {
                            fn_promotion_update_condition($promotion_data['conditions']['conditions'], 'remove', 'auto_coupons', $bonus['coupon_code']);
                        }

                        db_query("UPDATE ?:promotions SET conditions = ?s, conditions_hash = ?s, users_conditions_hash = ?s WHERE promotion_id = ?i", serialize($promotion_data['conditions']), fn_promotion_serialize($promotion_data['conditions']['conditions']), fn_promotion_serialize_users_conditions($promotion_data['conditions']['conditions']), $bonus['value']);
                    }
                }
            }
        }
    }

    return true;
}

/**
 * Pre/Post coupon checking/applying
 *
 * @param array $cart cart
 * @param boolean $initial_check true for pre-check, false - for post-check
 * @param array $applied_promotions list of applied promotions
 * @return boolean true if coupon is applied, false - otherwise
 */
function fn_promotion_check_coupon(&$cart, $initial_check, $applied_promotions = array())
{
    $result = true;

    // Pre-check: find if coupon is already used or only single coupon is allowed
    if ($initial_check == true) {
        fn_set_hook('pre_promotion_check_coupon', $cart['pending_coupon'], $cart);

        if (!empty($cart['coupons'][$cart['pending_coupon']])) {
            $_SESSION['promotion_notices']['promotion']['messages'][] = 'coupon_already_used';
            unset($cart['pending_coupon']);

            $result = false;

        } elseif (Registry::get('settings.General.use_single_coupon') == 'Y' && sizeof($cart['coupons']) > 0) {
            $_SESSION['promotion_notices']['promotion']['messages'][] = 'single_coupon_is_allowed';
            unset($cart['pending_coupon']);
            $result = false;

        } else {
            $cart['coupons'][$cart['pending_coupon']] = true;
        }

    // Post-check: check if coupon was applied successfully
    } else {
        if (!empty($cart['pending_coupon'])) {

            if (!empty($applied_promotions)) {
                $params = array (
                    'active' => true,
                    'coupon_code' => !empty($cart['pending_original_coupon']) ? $cart['pending_original_coupon'] : $cart['pending_coupon'],
                    'promotion_id' => array_keys($applied_promotions)
                );

                list($coupon) = fn_get_promotions($params);
            }

            if (empty($coupon)) {
                if (!fn_notification_exists('extra', 'error_coupon_already_used')) {
                    $_SESSION['promotion_notices']['promotion']['messages'][] = 'no_such_coupon';
                }
                unset($cart['coupons'][$cart['pending_coupon']]);

                $result = false;
            } else {
                $cart['coupons'][$cart['pending_coupon']] = array_keys($coupon);
                fn_set_hook('promotion_check_coupon', $cart['pending_coupon'], $cart);
            }

            unset($cart['pending_coupon'], $cart['pending_original_coupon']);
        }
    }

    return $result;
}

/**
 * Validate coupon
 *
 * @param array $promotion values to validate with
 * @param array $cart cart
 * @return mixed coupon code if coupon exist, false otherwise
 */
function fn_promotion_validate_coupon(&$promotion, &$cart, $promotion_id = 0)
{
    $values = fn_explode(',', $promotion['value']);

    // Check already applied coupons
    if (!empty($cart['coupons'])) {
        $coupons = array_keys($cart['coupons']);
        if ($promotion['operator'] == 'cont') {
            $codes = array();
            foreach ($coupons as $coupon_val) {
                foreach ($values as $cond_val) {
                    $cond_val = strtolower($cond_val);
                    if (stripos($coupon_val, $cond_val) !== false) {
                        $codes[] = $cond_val;
                        if (!empty($cart['pending_coupon']) && $cart['pending_coupon'] == $coupon_val) {
                            $cart['pending_original_coupon'] = $cond_val;
                        }
                    }
                }
            }
        } else {
            $codes = array();
            foreach ($values as $expected_coupon_code) {
                if (in_array(strtolower($expected_coupon_code), $coupons)) {
                    $codes[] = $expected_coupon_code;
                }
            }
        }

        if (!empty($codes) && !empty($promotion_id)) {
            foreach ($codes as $_code) {
                $_code = strtolower($_code);
                if (is_array($cart['coupons'][$_code]) && !in_array($promotion_id, $cart['coupons'][$_code])) {
                    $cart['coupons'][$_code][] = $promotion_id;
                }
            }
        }

        return $codes;
    }

    return false;
}

/**
 * Validate product (convert to common format)
 *
 * @param array $product product data
 * @return array converted product data
 */
function fn_promotion_validate_product($promotion, $product, $cart_products)
{
    if (!isset($product['product_id'])) {
        return array();
    }

    $options = array();

    if (!empty($promotion['value']) && is_array($promotion['value'])) {

        if (!empty($product['product_options'])) {

            if (!empty($cart_products)) { // cart promotion validated
                foreach ($promotion['value'] as $p_v) {
                    if ($p_v['product_id'] == $product['product_id'] && empty($p_v['product_options']) && $p_v['amount'] > 1) {
                        $_amount = 0;
                        foreach ($cart_products as $c_pr) {
                            if ($c_pr['product_id'] == $p_v['product_id']) {
                                $_amount += $c_pr['amount'];
                            }
                        }

                        if ($_amount == $p_v['amount']) {
                            $product['amount'] = $p_v['amount'];
                            break;
                        }
                    }
                }
            }

            foreach ($product['product_options'] as $item) {
                $options[$item['option_id']] = $item['value'];
            }

            $upd_product = array('product_options' => $options, 'product_id' => $product['product_id'], 'amount' => $product['amount']);
        } else {
            $upd_product = array('product_id' => $product['product_id'], 'amount' => $product['amount']);
        }
        foreach ($promotion['value'] as $p_v) {
            if ($upd_product['amount'] >= $p_v['amount']) {
                $upd_product['amount'] = $p_v['amount'];
            }
        }
    } else {
        $upd_product = $product['product_id'];
    }

    return array($upd_product);
}

/**
 * Validate product (convert to common format)
 *
 * @param array $product product data
 * @return array converted product data
 */
function fn_promotion_validate_purchased_product($promotion, $product, $auth)
{
    $options = array();
    if (!isset($auth['user_id'])) {
        $auth['user_id'] = 0;
    }

    if (!isset($product['product_id'])) {
        $product['product_id'] = 0;
    }

    if (!empty($promotion['value']) && is_array($promotion['value'])) {

        if (!empty($product['product_options'])) {

            foreach ($product['product_options'] as $item) {
                $options[$item['option_id']] = $item['value'];
            }
            $upd_product = array('product_options' => $options, 'product_id' => $product['product_id']);
        } else {
            $upd_product = array('product_id' => $product['product_id']);
        }

        $upd_product['amount'] = fn_get_ordered_products_amount($product['product_id'], $auth['user_id']);

        foreach ($promotion['value'] as $p_v) {
            if (isset($p_v['product_id']) && isset($p_v['amount']) && $upd_product['product_id'] == $p_v['product_id'] && $upd_product['amount'] > $p_v['amount']) {
                $upd_product['amount'] = $p_v['amount'];
            }
        }
    } else {
        $upd_product = $product['product_id'];
    }

    return array($upd_product);
}

/**
 * Check if the promotion is already used by customer.
 *
 * @param int $promotion_id
 * @param array $cart
 * @return int|bool
 */
function fn_promotion_check_existence($promotion_id, &$cart)
{
    static $statuses = null;

    if (is_null($statuses)) {
        $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true);
        foreach ($order_statuses as $status) {
            if ($status['params']['inventory'] == 'D') { // decreasing (positive) status
                $statuses[] = $status['status'];
            }
        }
    }

    if (!$statuses) {
        return false;
    }

    $udata = $cart['user_data'];
    fn_fill_user_fields($udata);

    if (defined('ORDER_MANAGEMENT') && !empty($cart['order_id'])) {
        $order_management_condition = db_quote(' order_id != ?i AND ', $cart['order_id']);
    } else {
        $order_management_condition = '';
    }

    $exists = db_get_field("SELECT ((firstname = ?s) + (lastname = ?s) + (b_city = ?s) + (b_state = ?s) + (b_country = ?s) + (b_zipcode = ?s) + (email = ?s) * 6) as r FROM ?:orders WHERE ?p FIND_IN_SET(?i, promotion_ids) AND status IN (?a) HAVING r >= ?i LIMIT 1", $udata['firstname'], $udata['lastname'], $udata['b_city'], $udata['b_state'], $udata['b_country'], $udata['b_zipcode'], $udata['email'], $order_management_condition, $promotion_id, $statuses, PROMOTION_MIN_MATCHES);

    return $exists;
}

/**
 * Get promotion dynamic properties
 *
 * @param array $promotion_id promotion ID
 * @param array $promotion promotion condition
 * @param array $condition condition
 * @param array $cart cart
 * @param array $auth auth information
 * @return mixed
 */
function fn_promotion_get_dynamic($promotion_id, $promotion, $condition, &$cart, &$auth = NULL)
{
    if ($condition == 'number_of_usages') {
        $usages = db_get_field("SELECT number_of_usages FROM ?:promotions WHERE promotion_id = ?i", $promotion_id);

        return intval($usages) + 1;

    } elseif ($condition == 'once_per_customer') {

        $exists = fn_promotion_check_existence($promotion_id, $cart);

        if (empty($cart['user_data'])) {
            return 'Y';
        }

        $promotion_data = fn_get_promotion_data($promotion_id);
        $coupon_exist = false;
        if (!empty($promotion_data['conditions']['conditions'])) {
            foreach ($promotion_data['conditions']['conditions'] as $val) {
                if ($val['condition'] == 'coupon_code') {
                    $coupon_exist = fn_promotion_validate_coupon($val, $cart);
                    if (!empty($coupon_exist) && $exists) {
                        fn_set_notification('W', __('warning'), __('text_can_be_used_once'), "K", 'error_coupon_already_used');
                        $_SESSION['promotion_notices']['promotion']['messages'][] = 'coupon_already_used';
                    }
                    break;
                }
            }
        }

        if ($exists) {
            return 'N';
        }

        return 'Y'; // this is checkbox with values (Y/N), so we need to return appropriate values
    }
}

/**
 * Serialize promotion conditions for search
 *
 * @param array $conditions conditions
 * @param boolean $plain flag - return as string (true) or array (false)
 * @return mixed serialized data
 */
function fn_promotion_serialize($conditions, $plain = true)
{
    $result = array();
    foreach ($conditions as $c) {
        if (!empty($c['conditions'])) {
            $result = fn_array_merge($result, fn_promotion_serialize($c['conditions']), false);
        } elseif (isset($c['value'])) {
            if ($c['condition'] == 'auto_coupons' || $c['condition'] == 'coupon_code') {
                $vals = explode(',', $c['value']);
                foreach ($vals as $v) {
                    $result[] = $c['condition'] . '=' . $v;
                }
            } else {
                if (is_array($c['value'])) {
                    $c['value'] = implode(',', array_keys($c['value']));
                }

                $result[] = $c['condition'] . '=' . $c['value'];
            }
        }
    }

    return ($plain == true) ? implode(';', $result) : $result;
}

/**
 * Serialize users promotion conditions for search
 *
 * @param array $conditions conditions
 * @return mixed serialized data
 */
function fn_promotion_serialize_users_conditions($conditions)
{
    $result = '';
    foreach ($conditions as $c) {
        if (!empty($c['condition']) && $c['condition'] == 'users') {
            $result = ',' . $c['value'] . ',';
        }
    }

    return $result;
}

/**
 * Get promotion data
 *
 * @param int $promotion_id promotion ID
 * @param string $lang_code code language
 * @return array promotion data
 */
function fn_get_promotion_data($promotion_id, $lang_code = DESCR_SL)
{
    $extra_condition = '';

    if (fn_allowed_for('ULTIMATE:FREE')) {
        $extra_condition = Database::quote(' AND p.zone = ?s', 'catalog');
    }

    $promotion_data = db_get_row("SELECT * FROM ?:promotions as p LEFT JOIN ?:promotion_descriptions as d ON p.promotion_id = d.promotion_id AND d.lang_code = ?s WHERE p.promotion_id = ?i ?p", $lang_code, $promotion_id, $extra_condition);

    if (!empty($promotion_data)) {
        $promotion_data['conditions'] = !empty($promotion_data['conditions']) ? unserialize($promotion_data['conditions']) : array();
        $promotion_data['bonuses'] = !empty($promotion_data['bonuses']) ? unserialize($promotion_data['bonuses']) : array();

        if (!empty($promotion_data['conditions']['conditions'])) {
            foreach ($promotion_data['conditions']['conditions'] as $key => $condition) {
                if (!empty($condition['condition']) && $condition['condition'] == 'feature') {
                    $condition['value_name'] = fn_get_product_feature_variant($condition['value']);
                    $promotion_data['conditions']['conditions'][$key]['value_name'] = $condition['value_name']['variant'];
                }
            }
        }
    }

    return $promotion_data;
}

function fn_get_promotion_name($promotion_id, $lang_code = DESCR_SL)
{
    fn_set_hook('get_promotion_name_pre', $promotion_id, $lang_code, $as_array);

    $promotion_name = false;

    if (!empty($promotion_id)) {
        $promotion_name = db_get_field("SELECT name FROM ?:promotion_descriptions WHERE promotion_id = ?i AND lang_code = ?s", $promotion_id, $lang_code);
    }

    return $promotion_name;
}

/**
 * Update promotion condition
 *
 * @param array $conditions conditions
 * @param string $action update action
 * @param string $field condition field to update
 * @param string $value value to update field with
 * @return boolean always true
 */
function fn_promotion_update_condition(&$conditions, $action, $field, $value)
{
    foreach ($conditions as $k => $c) {
        if (!empty($c['conditions'])) {
            fn_promotion_update_condition($c['conditions'], $action, $field, $value);
        } elseif ($c['condition'] == $field) {
            if ($action == 'add') {
                $conditions[$k]['value'] .= (!empty($c['value']) ? ',' : '') . $value;
            } else {
                $conditions[$k]['value'] = preg_replace("/(\b{$value}\b[,]?[ ]?)/", '', $c['value']);
            }
        }
    }

    return true;
}

/**
 * Call function and return its result
 *
 * @param array $data array with function and parameters
 * @return mixed function result
 */
function fn_get_promotion_variants($data)
{
    $f = array_shift($data);

    return call_user_func_array($f, $data);
}

/**
 * Get product features and convert the to common format
 *
 * @param string $lang_code language code
 * @return array formatted data
 */
function fn_promotions_get_features($lang_code = CART_LANGUAGE)
{
    $params = array(
        'variants' => true,
        'plain' => false,
    );

    list($features) = fn_get_product_features($params);

    $res = array();
    foreach ($features as $k => $v) {
        if ($v['feature_type'] == 'G') {
            $res[$k]['is_group'] = true;
            $res[$k]['group'] = $v['description'];
            $res[$k]['items'] = array();
            if (!empty($v['subfeatures'])) {
                foreach ($v['subfeatures'] as $_k => $_v) {
                    $res[$k]['items'][$_k]['value'] = $_v['description'];
                    if (!empty($_v['variants'])) {
                        foreach ($_v['variants'] as $__k => $__v) {
                            $res[$k]['items'][$_k]['variants'][$__k] = $__v['variant'];
                        }
                    } elseif ($_v['feature_type'] == 'C') {
                        $res[$k]['items'][$_k]['variants'] = array(
                            'Y' => __('yes'),
                            'N' => __('no'),
                        );
                    }
                }
            }
        } else {
            $res[$k]['value'] = $v['description'];
            if (!empty($v['variants'])) {
                foreach ($v['variants'] as $__k => $__v) {
                    $res[$k]['variants'][$__k] = $__v['variant'];
                }
            } elseif ($v['feature_type'] == 'C') {
                $res[$k]['variants'] = array(
                    'Y' => __('yes'),
                    'N' => __('no'),
                );
            }
        }
    }

    return $res;
}

/**
 * Check if product has certain features
 *
 * @param array $promotion promotion data
 * @param array $product product data
 * @return mixed feature value if found, boolean false otherwise
 */
function fn_promotions_check_features($promotion, $product)
{
    $features = db_get_hash_multi_array("SELECT feature_id, variant_id, value, value_int FROM ?:product_features_values WHERE product_id = ?i AND lang_code = ?s", array('feature_id'), $product['product_id'], CART_LANGUAGE);

    if (!empty($features) && !empty($promotion['condition_element']) && !empty($features[$promotion['condition_element']])) {
        $f = $features[$promotion['condition_element']];

        $result = array();
        foreach ($f as $v) {
            $result[] = !empty($v['variant_id']) ? $v['variant_id'] : ($v['value_int'] != '' ? $v['value_int'] : $v['value']);
        }

        return $result;
    }

    return false;
}

/**
 * Calculate order discount for sub orders (used in MVE)
 *
 * @param string $type discount type
 * @param array $bonus Array with promotion data
 * @param int $bonus_id Bonus ID
 * @param array $cart Array with cart data
 * @return float calculated discount value
 */
function fn_promotions_calculate_order_discount($bonus, $bonus_id, $cart)
{
    $type = $bonus['discount_bonus'];
    $price = $cart['subtotal'];
    $value = $bonus['discount_value'];

    static $parent_orders = array();

    // this calculations are actual only for the fixed (absolute) amount
    if ($type == 'to_fixed' || $type == 'by_fixed') {

        // if it is parent or usual order
        if (empty($cart['parent_order_id'])) {

            // calculate usual discount
            $discount = fn_promotions_calculate_discount($type, $price, $value);

            // save order discount for future calculations of suborders
            $discount = fn_format_price($discount);
            $session_orders_discount = & $_SESSION['orders_discount'][$bonus['promotion_id'] . '_' . $bonus_id];
            $session_orders_discount['parent_order_discount'] = $discount;
            $session_orders_discount['suborders_discount'] = 0;

        } else {
            // this is sub order

            $parent_order_id = $cart['parent_order_id'];

            // get parent order subtotal info
            if (!isset($parent_orders[$parent_order_id]['subtotal'])) {
                $parent_order_info = fn_get_order_info($parent_order_id);
                $parent_orders[$parent_order_id]['subtotal'] = $parent_order_info['subtotal'];
            }

            if (!empty($parent_orders[$parent_order_id]['subtotal'])) {
                // calculate the share of the full discount
                $value = $value * $price / $parent_orders[$parent_order_id]['subtotal'];
            }

            $discount = fn_promotions_calculate_discount($type, $price, $value);
            $discount = fn_format_price($discount);

            $session_orders_discount = & $_SESSION['orders_discount'][$bonus['promotion_id'] . '_' . $bonus_id];
            $parent_order_discount = !empty($session_orders_discount['parent_order_discount']) ? $session_orders_discount['parent_order_discount'] : 0;
            $suborders_discount = !empty($session_orders_discount['suborders_discount']) ? $session_orders_discount['suborders_discount'] : 0;

            // check that total suborders discount is less than parent_order_discount
            // or this is last sub order, so we have to distract discount, to avoid the extra cents
            $new_suborders_discount = $suborders_discount + $discount;
            if ($new_suborders_discount > $parent_order_discount || (!empty($cart['companies']) && end($cart['companies']) == $cart['company_id'])) {
                $discount = $parent_order_discount - (!empty($session_orders_discount['suborders_discount']) ? $session_orders_discount['suborders_discount'] : 0);

                if ($discount < 0) {
                    $discount = 0;
                }
            }

            $session_orders_discount['suborders_discount'] = $suborders_discount + $discount;

        }

    } else {
        $discount = fn_promotions_calculate_discount($type, $price, $value);
    }

    return $discount;
}

/**
 * Calculate discount
 *
 * @param string $type discount type
 * @param float $price price to apply discount to
 * @param float $value discount value
 * @param float $current_price current price, for fixed discount calculation
 * @return float calculated discount value
 */
function fn_promotions_calculate_discount($type, $price, $value, $current_price = 0)
{
    $discount = 0;

    if ($value === '') {
        return 0;
    }

    if ($type == 'to_percentage') {
        $discount = $price * (100 - $value) / 100;

    } elseif ($type == 'by_percentage') {
        $discount = $price * $value / 100;

    } elseif ($type == 'to_fixed') {
        $discount = (!empty($current_price) ? $current_price : $price) - $value;

    } elseif ($type == 'by_fixed') {
        $discount = $value;
    }

    if ($discount < 0) {
        $discount = 0;
    }

    return $discount;
}

function fn_delete_promotions($promotion_ids)
{
    if (!is_array($promotion_ids)) {
        $promotion_ids = array($promotion_ids);
    }

    if (fn_allowed_for('ULTIMATE')) {
        foreach ($promotion_ids as $promotion_id => $promotion) {
            if (!fn_check_company_id('promotions', 'promotion_id', $promotion)) {
                fn_set_notification('E', __('error'), __('access_denied'));
                unset($promotion_ids[$promotion_id]);
            }
        }
    }

    foreach ($promotion_ids as $pr_id) {
        db_query("DELETE FROM ?:promotions WHERE promotion_id = ?i", $pr_id);
        db_query("DELETE FROM ?:promotion_descriptions WHERE promotion_id = ?i", $pr_id);
    }
}



/**
 * Checks if the promotion code input field should be displayed.
 *
 * @param array $cart Array of cart content and user information necessary for purchase
 * @return bool
 */
function fn_display_promotion_input_field($cart)
{
    /**
     * Checks if the promotion code input field should be displayed.
     *
     * @param array $cart Array of cart content and user information necessary for purchase
     */
    fn_set_hook('display_promotion_input_field_pre', $cart);

    $result = false;

    if (!empty($cart['has_coupons'])) {
        $result = true;
    }

    /**
     * Modify result of the promotion code input field visibility check.
     *
     * @param type $cart   Array of cart content and user information necessary for purchase
     * @param bool $result Checking result
     */
    fn_set_hook('display_promotion_input_field_post', $cart, $result);

    return $result;
}
