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

use Tygh\Embedded;
use Tygh\Enum\ProductTracking;
use Tygh\Http;
use Tygh\Mailer;
use Tygh\Pdf;
use Tygh\Registry;
use Tygh\Storage;
use Tygh\Session;
use Tygh\Settings;
use Tygh\Shippings\Shippings;
use Tygh\Navigation\LastView;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

/**
 * Gets displayable product data to show it in the cart
 *
 * @param string $hash Unique product HASH
 * @param array &$product Product data
 * @param bool $skip_promotion Skip promotion calculation
 * @param array &$cart Array of cart content and user information necessary for purchase
 * @param array &$auth Array with authorization data
 * @param array $promotion_amount Amount of product in promotion (like Free products, etc)
 * @return array Product data
 */
function fn_get_cart_product_data($hash, &$product, $skip_promotion, &$cart, &$auth, $promotion_amount = 0)
{
    /**
     * Prepare params before getting product data from cart
     *
     * @param string $hash Unique product HASH
     * @param array &$product Product data
     * @param bool $skip_promotion Skip promotion calculation
     * @param array &$cart Array of cart content and user information necessary for purchase
     * @param array &$auth Array with authorization data
     * @param array $promotion_amount Amount of product in promotion (like Free products, etc)
     */
    fn_set_hook('get_cart_product_data_pre', $hash, $product, $skip_promotion, $cart, $auth, $promotion_amount);

    if (!empty($product['product_id'])) {

        $fields = array(
            '?:products.product_id',
            '?:products.company_id',
            "GROUP_CONCAT(IF(?:products_categories.link_type = 'M', CONCAT(?:products_categories.category_id, 'M'), ?:products_categories.category_id)) as category_ids",
            '?:products.product_code',
            '?:products.weight',
            '?:products.tracking',
            '?:product_descriptions.product',
            '?:product_descriptions.short_description',
            '?:products.is_edp',
            '?:products.edp_shipping',
            '?:products.shipping_freight',
            '?:products.free_shipping',
            '?:products.zero_price_action',
            '?:products.tax_ids',
            '?:products.qty_step',
            '?:products.list_qty_count',
            '?:products.max_qty',
            '?:products.min_qty',
            '?:products.amount as in_stock',
            '?:products.shipping_params',
            '?:companies.status as company_status',
            '?:companies.company as company_name'
        );

        $join  = db_quote("LEFT JOIN ?:product_descriptions ON ?:product_descriptions.product_id = ?:products.product_id AND ?:product_descriptions.lang_code = ?s", CART_LANGUAGE);

        $_p_statuses = array('A', 'H');
        $_c_statuses = array('A', 'H');
        $avail_cond = (AREA == 'C') ? " AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:categories.usergroup_ids', true) . ")" : '';
        $avail_cond .= (AREA == 'C') ? " AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:products.usergroup_ids', true) . ")" : '';
        $avail_cond .= (AREA == 'C' && !(isset($auth['area']) && $auth['area'] == 'A')) ? db_quote(' AND ?:categories.status IN (?a) AND ?:products.status IN (?a)', $_c_statuses, $_p_statuses) : '';
        $avail_cond .= (AREA == 'C') ? fn_get_localizations_condition('?:products.localization') : '';

        $join .= " INNER JOIN ?:products_categories ON ?:products_categories.product_id = ?:products.product_id INNER JOIN ?:categories ON ?:categories.category_id = ?:products_categories.category_id $avail_cond";
        $join .= " LEFT JOIN ?:companies ON ?:companies.company_id = ?:products.company_id";

        fn_set_hook('pre_get_cart_product_data', $hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, $fields, $join);

        $_pdata = db_get_row("SELECT " . implode(', ', $fields) . " FROM ?:products ?p WHERE ?:products.product_id = ?i GROUP BY ?:products.product_id", $join, $product['product_id']);

        // delete product from cart if vendor was disabled.
        if (empty($_pdata) || (!empty($_pdata['company_id']) && !defined('ORDER_MANAGEMENT') && $_pdata['company_status'] != 'A')) {
            fn_delete_cart_product($cart, $hash);

            return false;
        }

        if (!empty($_pdata['category_ids'])) {
            list($_pdata['category_ids'], $_pdata['main_category']) = fn_convert_categories($_pdata['category_ids']);
        } else {
            $_pdata['category_ids'] = array();
        }

        $_pdata['options_count'] = db_get_field("SELECT COUNT(*) FROM ?:product_options WHERE product_id = ?i AND status = 'A'", $product['product_id']);

        $amount = !empty($product['amount_total']) ? $product['amount_total'] : $product['amount'];
        $_pdata['price'] = fn_get_product_price($product['product_id'], $amount, $auth);

        $_pdata['base_price'] = (isset($product['stored_price']) && $product['stored_price'] == 'Y') ? $product['price'] : $_pdata['price'];

        fn_set_hook('get_cart_product_data', $product['product_id'], $_pdata, $product, $auth, $cart, $hash);

        $product['stored_price'] = empty($product['stored_price']) ? 'N' : $product['stored_price'];
        $product['stored_discount'] = empty($product['stored_discount']) ? 'N' : $product['stored_discount'];
        $product['product_options'] = empty($product['product_options']) ? array() : $product['product_options'];

        if (empty($_pdata['product_id'])) { // FIXME - for deleted products for OM
            fn_delete_cart_product($cart, $hash);

            return array();
        }

        if (!empty($_pdata['options_count']) && empty($product['product_options'])) {
            $cart['products'][$hash]['product_options'] = fn_get_default_product_options($product['product_id']);
        }

        if (Registry::get('settings.General.inventory_tracking') == 'Y' &&
            !empty($_pdata['tracking']) && $_pdata['tracking'] == ProductTracking::TRACK_WITH_OPTIONS &&
            !empty($product['selectable_cart_id'])
        ) {
            $_pdata['in_stock'] = db_get_field("SELECT amount FROM ?:product_options_inventory WHERE combination_hash = ?i", $product['selectable_cart_id']);
        }

        $product['amount'] = fn_check_amount_in_stock($product['product_id'], $product['amount'], $product['product_options'], $hash, $_pdata['is_edp'], !empty($product['original_amount']) ? $product['original_amount'] : 0, $cart);

        if ($product['amount'] == 0) {
            fn_delete_cart_product($cart, $hash);
            $out_of_stock = true;

            return false;
        }

        if (!fn_allowed_for('ULTIMATE:FREE')) {
            $exceptions = fn_get_product_exceptions($product['product_id'], true);
            if (!isset($product['options_type']) || !isset($product['exceptions_type'])) {
                $product = array_merge($product, db_get_row('SELECT options_type, exceptions_type FROM ?:products WHERE product_id = ?i', $product['product_id']));
            }

            if (!fn_is_allowed_options_exceptions($exceptions, $product['product_options'], $product['options_type'], $product['exceptions_type']) && !defined('GET_OPTIONS')) {
                fn_set_notification('E', __('notice'), __('product_options_forbidden_combination', array(
                    '[product]' => $_pdata['product']
                )));
                fn_delete_cart_product($cart, $hash);

                return false;
            }

            if (!fn_is_allowed_options($product)) {
                fn_set_notification('E', __('notice'), __('product_disabled_options', array(
                    '[product]' => $_pdata['product']
                )));
                fn_delete_cart_product($cart, $hash);

                return false;
            }
        }

        if (isset($product['extra']['custom_files'])) {
            $_pdata['extra']['custom_files'] = $product['extra']['custom_files'];
        }

        $_pdata['calculation'] = array();

        if (isset($product['extra']['exclude_from_calculate'])) {
            $_pdata['exclude_from_calculate'] = $product['extra']['exclude_from_calculate'];
            $_pdata['aoc'] = !empty($product['extra']['aoc']);
            $_pdata['price'] = 0;
        } else {
            if ($product['stored_price'] == 'Y') {
                $_pdata['price'] = $product['price'];
            }
        }

        $product['price'] = ($_pdata['zero_price_action'] == 'A' && isset($product['custom_user_price'])) ? $product['custom_user_price'] : floatval($_pdata['price']);
        $cart['products'][$hash]['price'] = $product['price'];

        $_pdata['original_price'] = $product['price'];

        if ($product['stored_price'] != 'Y' && !isset($product['extra']['exclude_from_calculate'])) {
            $_tmp = $product['price'];
            $product['price'] = fn_apply_options_modifiers($product['product_options'], $product['price'], 'P', array(), array('product_data' => $product));
            $product['modifiers_price'] = $_pdata['modifiers_price'] = $product['price'] - $_tmp; // modifiers
        } else {
            $product['modifiers_price'] = $_pdata['modifiers_price'] = 0;
        }

        if (isset($product['modifiers_price']) && $_pdata['zero_price_action'] == 'A') {
            $_pdata['base_price'] = $product['price'] - $product['modifiers_price'];
        }

        $_pdata['weight'] = fn_apply_options_modifiers($product['product_options'], $_pdata['weight'], 'W', array(), array('product_data' => $product));
        $_pdata['amount'] = $product['amount'];
        $_pdata['price'] = $_pdata['original_price'] = fn_format_price($product['price']);

        $_pdata['stored_price'] = $product['stored_price'];

        if ($cart['options_style'] == 'F') {
            $_pdata['product_options'] = fn_get_selected_product_options($product['product_id'], $product['product_options'], CART_LANGUAGE);
        } elseif ($cart['options_style'] == 'I') {
            $_pdata['product_options'] = fn_get_selected_product_options_info($product['product_options'], CART_LANGUAGE);
        } else {
            $_pdata['product_options'] = $product['product_options'];
        }

        fn_set_hook('get_cart_product_data_post_options', $product['product_id'], $_pdata, $product);

        if (($_pdata['free_shipping'] != 'Y' || AREA == 'A') && ($_pdata['is_edp'] != 'Y' || ($_pdata['is_edp'] == 'Y' && $_pdata['edp_shipping'] == 'Y'))) {
            $cart['shipping_required'] = true;
        }

        $cart['products'][$hash]['is_edp'] = (!empty($_pdata['is_edp']) && $_pdata['is_edp'] == 'Y') ? 'Y' : 'N';
        $cart['products'][$hash]['edp_shipping'] = (!empty($_pdata['edp_shipping']) && $_pdata['edp_shipping'] == 'Y') ? 'Y' : 'N';

        if (empty($cart['products'][$hash]['extra']['parent'])) { // count only products without parent
            if ($skip_promotion == true && !empty($promotion_amount)) {
                $cart['amount'] += $promotion_amount;
            } else {
                $cart['amount'] += $product['amount'];
            }
        }

        if ($skip_promotion == false) {
            if (empty($cart['order_id']) || !empty($cart['recalculate_catalog_promotions'])) {
                fn_promotion_apply('catalog', $_pdata, $auth);
            } else {
                if (isset($product['discount'])) {
                    $_pdata['discount'] = $product['discount'];
                    $_pdata['price'] -= $product['discount'];

                    if ($_pdata['price'] < 0) {
                        $_pdata['discount'] += $_pdata['price'];
                        $_pdata['price'] = 0;
                    }
                }
            }

            // apply discount to the product
            if (!empty($_pdata['discount'])) {
                $cart['use_discount'] = true;
            }
        }

        if (!empty($product['object_id'])) {
            $_pdata['object_id'] = $product['object_id'];
        }

        $_pdata['shipping_params'] = empty($_pdata['shipping_params']) ? array() : unserialize($_pdata['shipping_params']);

        $_pdata['stored_discount'] = $product['stored_discount'];
        $cart['products'][$hash]['modifiers_price'] = $product['modifiers_price'];

        $_pdata['subtotal'] = $_pdata['price'] * $product['amount'];
        $cart['original_subtotal'] += $_pdata['original_price'] * $product['amount'];
        $cart['subtotal'] += $_pdata['subtotal'];

        /**
         * Prepare params before getting product data from cart
         *
         * @param string $hash Unique product HASH
         * @param array &$product Product data
         * @param bool $skip_promotion Skip promotion calculation
         * @param array &$cart Array of cart content and user information necessary for purchase
         * @param array &$auth Array with authorization data
         * @param array $promotion_amount Amount of product in promotion (like Free products, etc)
         * @param array $promotion_amount Product data
         */
        fn_set_hook('get_cart_product_data_post', $hash, $product, $skip_promotion, $cart, $auth, $promotion_amount, $_pdata);

        return $_pdata;
    }

    return array();
}

/**
 * Update cart products data
 *
 * @param array $cart Array of cart content and user information necessary for purchase
 * @param array $cart_products Array of new data for products information update
 * @return boolean Always true
 */
function fn_update_cart_data(&$cart, &$cart_products)
{
    foreach ($cart_products as $k => $v) {
        if (isset($cart['products'][$k])) {
            if (!isset($v['base_price'])) {
                $cart['products'][$k]['base_price'] = $v['base_price'] = $cart['products'][$k]['stored_price'] != 'Y' ? $v['price'] : $cart['products'][$k]['price'];
            } else {
                if ($cart['products'][$k]['stored_price'] == 'Y') {
                    $cart_products[$k]['base_price'] = $cart['products'][$k]['price'];
                }
            }

            $cart['products'][$k]['base_price'] = $cart['products'][$k]['stored_price'] != 'Y' ? $v['base_price'] : $cart['products'][$k]['price'];
            $cart['products'][$k]['price'] = $cart['products'][$k]['stored_price'] != 'Y' ? $v['price'] : $cart['products'][$k]['price'];
            if (isset($v['discount'])) {
                $cart['products'][$k]['discount'] = $v['discount'];
            }
            if (isset($v['promotions'])) {
                $cart['products'][$k]['promotions'] = $v['promotions'];
            }
        }
    }

    return true;
}

/**
 * Get all available payment methods for current area
 *
 * @param array &$auth customer data
 * @param string $lang_code 2-letter language code
 * @return array found payment methods
 */
function fn_get_payment_methods(&$auth, $lang_code = CART_LANGUAGE)
{
    $condition = '';
    if (AREA == 'C') {
        $condition .= " AND (" . fn_find_array_in_set($auth['usergroup_ids'], '?:payments.usergroup_ids', true) . ")";
        $condition .= " AND ?:payments.status = 'A' ";
        $condition .= fn_get_localizations_condition('?:payments.localization');
    }

    $payment_methods = db_get_hash_array("SELECT ?:payments.payment_id, ?:payments.template, ?:payments.a_surcharge, ?:payments.p_surcharge, ?:payments.payment_category, ?:payment_descriptions.*, ?:payment_processors.processor, ?:payment_processors.type AS processor_type FROM ?:payments LEFT JOIN ?:payment_descriptions ON ?:payments.payment_id = ?:payment_descriptions.payment_id AND ?:payment_descriptions.lang_code = ?s LEFT JOIN ?:payment_processors ON ?:payment_processors.processor_id = ?:payments.processor_id WHERE 1 $condition ORDER BY ?:payments.position", 'payment_id', $lang_code);

    fn_set_hook('get_payment_methods', $payment_methods, $auth);

    return $payment_methods;
}

/**
 * Gets payment methods names list
 *
 * @param boolean $is_active Flag determines if only the active methods should be returned; default value is false
 * @param string $lang_code 2-letter language code
 * @return array Array of payment method names with payment_ids as keys
 */
function fn_get_simple_payment_methods($is_active = true, $lang_code = CART_LANGUAGE)
{
    $condition = '';
    if ($is_active) {
        $condition .= " AND status = 'A'";
    }

    return db_get_hash_single_array("SELECT ?:payments.payment_id, ?:payment_descriptions.payment FROM ?:payments LEFT JOIN ?:payment_descriptions ON ?:payments.payment_id = ?:payment_descriptions.payment_id AND ?:payment_descriptions.lang_code = ?s WHERE 1 $condition ORDER BY ?:payments.position, ?:payment_descriptions.payment", array('payment_id', 'payment'), $lang_code);
}

/**
 * Get payment method data
 *
 * @param int $payment_id payment ID
 * @param string $lang_code 2-letter language code
 * @return array payment information
 */
function fn_get_payment_method_data($payment_id, $lang_code = CART_LANGUAGE)
{
    static $payments = array();

    if (empty($payments[$payment_id])) {
        $fields = array(
            '?:payments.*',
            '?:payment_descriptions.*',
            '?:payment_processors.processor',
            '?:payment_processors.type AS processor_type'
        );

        $join = db_quote(" LEFT JOIN ?:payment_descriptions ON ?:payments.payment_id = ?:payment_descriptions.payment_id AND ?:payment_descriptions.lang_code = ?s", $lang_code);
        $join .= db_quote(" LEFT JOIN ?:payment_processors ON ?:payment_processors.processor_id = ?:payments.processor_id");

        $payment = db_get_row("SELECT " . implode(', ', $fields) . " FROM ?:payments ?p WHERE ?:payments.payment_id = ?i", $join, $payment_id);

        fn_set_hook('summary_get_payment_method', $payment_id, $payment);

        if (!empty($payment)) {
            $payment['processor_params'] = (!empty($payment['processor_params'])) ? unserialize($payment['processor_params']) : '';
            $payment['tax_ids'] = !empty($payment['tax_ids']) ? fn_explode(',', $payment['tax_ids']) : array();
            $payment['image'] = fn_get_image_pairs($payment_id, 'payment', 'M', true, true, $lang_code);
        }

        fn_set_hook('summary_get_payment_method', $payment_id, $payment);
        $payments[$payment_id] = $payment;
    }

    return $payments[$payment_id];
}

/**
 * Get payments method data
 *
 * @param string $lang_code 2-letter language code
 * @return array payments information
 */

function fn_get_payments($lang_code = CART_LANGUAGE)
{
    return db_get_array("SELECT ?:payments.*, ?:payment_descriptions.* FROM ?:payments LEFT JOIN ?:payment_descriptions ON ?:payment_descriptions.payment_id = ?:payments.payment_id AND ?:payment_descriptions.lang_code = ?s ORDER BY ?:payments.position", $lang_code);
}

/**
 * Create/Update payments data
 *
 * @param array $payment_data
 * @param int $payment_id
 * @param string $lang_code 2-letter language code
 * @return int Payment id
 */
function fn_update_payment($payment_data, $payment_id, $lang_code = DESCR_SL)
{
    $certificate_file = fn_filter_uploaded_data('payment_certificate');
    $certificates_dir = Registry::get('config.dir.certificates');

    /**
     * Create/update the certificate file
     * only for an existing payment method.
     *
     * Non-existing payment method will be created first,
     * then will be updated. (fn_update_payment calling at line 450).
     */
    if ($payment_id) {

        if ($certificate_file) {
            $file = reset($certificate_file);
            $filename = $payment_id . '/' . $file['name'];

            fn_mkdir($certificates_dir . $payment_id);
            fn_copy($file['path'], $certificates_dir . $filename);
            $payment_data['processor_params']['certificate_filename'] = $filename;
        }

        $old_params = fn_get_processor_data($payment_id);

        if (empty($payment_data['processor_params']['certificate_filename']) && isset($old_params['processor_params']['certificate_filename'])) {
            $payment_data['processor_params']['certificate_filename'] = $old_params['processor_params']['certificate_filename'];
        }

        if (!empty($payment_data['processor_params']['certificate_filename'])) {
            if (!empty($old_params['processor_params']['certificate_filename']) && $payment_data['processor_params']['certificate_filename'] != $old_params['processor_params']['certificate_filename']) {
                fn_rm($certificates_dir . $old_params['processor_params']['certificate_filename']);
            }

            if (!file_exists($certificates_dir . $payment_data['processor_params']['certificate_filename'])) {
                $payment_data['processor_params']['certificate_filename'] = '';
            }
        }
    }

    if (!empty($payment_data['processor_id'])) {
        $payment_data['template'] = db_get_field("SELECT processor_template FROM ?:payment_processors WHERE processor_id = ?i", $payment_data['processor_id']);
    }

    $payment_data['localization'] = !empty($payment_data['localization']) ? fn_implode_localizations($payment_data['localization']) : '';
    $payment_data['usergroup_ids'] = !empty($payment_data['usergroup_ids']) ? implode(',', $payment_data['usergroup_ids']) : '0';
    $payment_data['tax_ids'] = !empty($payment_data['tax_ids']) ? fn_create_set($payment_data['tax_ids']) : '';

    // Update payment processor settings
    $processor_params = array();
    if (!empty($payment_data['processor_params'])) {
        $processor_params = $payment_data['processor_params'];
        $payment_data['processor_params'] = serialize($payment_data['processor_params']);
    }

    if (!empty($payment_id)) {
        $arow = db_query("UPDATE ?:payments SET ?u WHERE payment_id = ?i", $payment_data, $payment_id);
        db_query("UPDATE ?:payment_descriptions SET ?u WHERE payment_id = ?i AND lang_code = ?s", $payment_data, $payment_id, $lang_code);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('payment'))),'','404');
            $payment_id = false;
        }
    } else {
        $payment_data['payment_id'] = $payment_id = db_query("INSERT INTO ?:payments ?e", $payment_data);
        foreach (fn_get_translation_languages() as $payment_data['lang_code'] => $_v) {
            db_query("INSERT INTO ?:payment_descriptions ?e", $payment_data);
        }

        /**
         * Update the certificate
         */
        if ($certificate_file && $payment_id) {
            unset($payment_data['lang_code']);
            $payment_data['processor_params'] = $processor_params;
            fn_update_payment($payment_data, $payment_id, $lang_code);
        }
    }

    fn_attach_image_pairs('payment_image', 'payment', $payment_id, $lang_code);

    return $payment_id;
}

/**
 * Creates/Updates currency
 *
 * @param array $currency_data Currency information
 * @param int $currency_id Currency id
 * @param string $lang_code 2-letter language code
 * @return int Currency id
 */
function fn_update_currency($currency_data, $currency_id, $lang_code = DESCR_SL)
{

    /**
     * Updates currency data before updating
     *
     * @param array  $currency_data Currency information
     * @param int    $currency_id   Currency id
     * @param string $lang_code     2-letter language code
    */
    fn_set_hook('update_currency_pre', $currency_data, $currency_id, $lang_code);

    $currency_data['currency_code'] = strtoupper($currency_data['currency_code']);
    $currency_data['coefficient'] = !empty($currency_data['is_primary']) || !isset($currency_data['coefficient']) ? 1 : $currency_data['coefficient'];

    if (empty($currency_data['coefficient']) || floatval($currency_data['coefficient']) <= 0) {
        fn_set_notification('W', __('warning'), __('currency_rate_greater_than_null'));

        return false;
    }

    $is_exists = db_get_field("SELECT COUNT(*) FROM ?:currencies WHERE currency_code = ?s AND currency_id <> ?i", $currency_data['currency_code'], $currency_id);

    if (!empty($is_exists)) {
        fn_set_notification('E', __('error'), __('error_currency_exists', array(
            '[code]' => $currency_data['currency_code']
        )));

        return false;
    }

    if (isset($currency_data['decimals']) && $currency_data['decimals'] > 2) {
        fn_set_notification('W', __('warning'), __('notice_too_many_decimals', array(
            '[DECIMALS]' => $currency_data['decimals'],
            '[CURRENCY]' => $currency_data['currency_code']
        )));
    }

    if (!empty($currency_data['is_primary'])) {
        db_query("UPDATE ?:currencies SET is_primary = 'N' WHERE is_primary = 'Y'");
    }

    if (empty($currency_id)) {
        $currency_id = db_query("INSERT INTO ?:currencies ?e", $currency_data);
        fn_create_description('currency_descriptions', 'currency_code', $currency_data['currency_code'], $currency_data);
    } else {
        $old_currency_code = db_get_field("SELECT currency_code FROM ?:currencies WHERE currency_id = ?i", $currency_id);
        db_query("UPDATE ?:currencies SET ?u WHERE currency_id = ?i", $currency_data, $currency_id);
        db_query('UPDATE ?:currency_descriptions SET ?u WHERE currency_code = ?s AND lang_code = ?s', $currency_data, $old_currency_code, $lang_code);
    }

    /**
     * Changes result of currency saving
     *
     * @param array  $currency_data Currency information
     * @param int    $currency_id   Currency id
     * @param string $lang_code     2-letter language code
     * @param int Currency id
    */
    fn_set_hook('update_currency_post', $currency_data, $currency_id, $lang_code, $currency_id);

    return $currency_id;
}

/**
 * Get product amount
 *
 * @param int $product_id
 * @param int $user_id
 * @return purchased product amount
 */
function fn_get_ordered_products_amount($product_id, $user_id)
{

    if (empty($user_id) || empty($product_id)) {
        return 0;
    }

    $where = db_quote(' AND ?:order_details.product_id = ?i', $product_id);

    $orders_company_condition = '';

    if (fn_allowed_for('ULTIMATE')) {
        $orders_company_condition = fn_get_company_condition('?:orders.company_id');
    }

    $product_amount = db_get_field(
                "SELECT sum(?:order_details.amount)"
                . " FROM ?:order_details"
                . " INNER JOIN ?:orders ON ?:orders.order_id = ?:order_details.order_id AND ?:orders.is_parent_order != 'Y' $orders_company_condition"
                . " INNER JOIN ?:status_data ON ?:orders.status = ?:status_data.status AND type = ?s AND param = ?s AND value = ?s"
                . " WHERE ?:orders.user_id = ?i" . $where
                . " GROUP BY ?:order_details.product_id",
                'O', 'inventory', 'D',
                $user_id
    );

    if (empty($product_amount)) {
        return 0;
    }

    return $product_amount;
}

//
// Update product amount
//
// returns true if inventory successfully updated and false if amount
// is negative is allow_negative_amount option set to false

function fn_update_product_amount($product_id, $amount, $product_options, $sign)
{
    if (Registry::get('settings.General.inventory_tracking') != 'Y') {
        return true;
    }

    $tracking = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product_id);

    if ($tracking == ProductTracking::DO_NOT_TRACK) {
        return true;
    }

    if ($tracking == ProductTracking::TRACK_WITHOUT_OPTIONS) {
        $product = db_get_row("SELECT amount, product_code FROM ?:products WHERE product_id = ?i", $product_id);
        $current_amount = $product['amount'];
        $product_code = $product['product_code'];
    } else {
        $cart_id = fn_generate_cart_id($product_id, array('product_options' => $product_options), true);
        $product = db_get_row("SELECT amount, product_code FROM ?:product_options_inventory WHERE combination_hash = ?i", $cart_id);
        $current_amount = empty($product['amount']) ? 0 : $product['amount'];

        if (empty($product['product_code'])) {
            $product_code = db_get_field("SELECT product_code FROM ?:products WHERE product_id = ?i", $product_id);
        } else {
            $product_code = $product['product_code'];
        }
    }

    if ($sign == '-') {
        $new_amount = $current_amount - $amount;

        // Notify administrator about inventory low stock
        if ($new_amount <= Registry::get('settings.General.low_stock_threshold')) {
            // Log product low-stock
            $company_id = fn_get_company_id('products', 'product_id', $product_id);

            fn_log_event('products', 'low_stock', array (
                'product_id' => $product_id,
            ));
            $lang_code = fn_get_company_language($company_id);
            $lang_code = !empty($lang_code) ? $lang_code : Registry::get('settings.Appearance.backend_default_language');
            $selected_product_options = ($tracking == ProductTracking::TRACK_WITH_OPTIONS)
                ? fn_get_selected_product_options_info($product_options, $lang_code)
                : '';

            Mailer::sendMail(array(
                'to' => 'company_orders_department',
                'from' => 'default_company_orders_department',
                'data' => array(
                    'product_options' => $selected_product_options,
                    'new_amount' => $new_amount,
                    'product_id' => $product_id,
                    'product_code' => $product_code,
                    'product' => db_get_field("SELECT product FROM ?:product_descriptions WHERE product_id = ?i AND lang_code = ?s", $product_id, $lang_code)
                ),
                'tpl' => 'orders/low_stock.tpl',
                'company_id' => $company_id,
            ), 'A', $lang_code);
        }

        if ($new_amount < 0 && Registry::get('settings.General.allow_negative_amount') != 'Y') {
            return false;
        }
    } else {
        $new_amount = $current_amount + $amount;
    }

    fn_set_hook('update_product_amount', $new_amount, $product_id, $cart_id, $tracking);

    if ($tracking == ProductTracking::TRACK_WITHOUT_OPTIONS) {
        db_query("UPDATE ?:products SET amount = ?i WHERE product_id = ?i", $new_amount, $product_id);
    } else {
        db_query("UPDATE ?:product_options_inventory SET amount = ?i WHERE combination_hash = ?i", $new_amount, $cart_id);
    }

    if (($current_amount <= 0) && ($new_amount > 0)) {
        fn_send_product_notifications($product_id);
    }

    return true;
}

function fn_update_order(&$cart, $order_id = 0)
{
    fn_set_hook('pre_update_order', $cart, $order_id);

    $cart['user_data'] = (isset($cart['user_data'])) ? $cart['user_data'] : array();

    $order = fn_array_merge($cart, $cart['user_data']);
    unset($order['user_data']);

    // filter hidden fields, which were hidden to checkout
    fn_filter_hidden_profile_fields($order, 'O');

    $order = fn_fill_contact_info_from_address($order);

    if (empty($order['profile_id'])) {
        $order['profile_id'] = 0;
    }

    if (!empty($cart['shipping'])) {
        $order['shipping_ids'] = fn_create_set(array_keys($cart['shipping']));
    }

    if (!empty($cart['payment_surcharge'])) {
        $cart['total'] += $cart['payment_surcharge'];
        $order['total'] = $cart['total'];

        if (fn_allowed_for('MULTIVENDOR')) {
            $cart['companies'] = fn_get_products_companies($cart['products']);
            $take_payment_surcharge_from_vendor = fn_take_payment_surcharge_from_vendor($cart['products']);

            if (Registry::get('settings.Vendors.include_payment_surcharge') == 'Y' && $take_payment_surcharge_from_vendor) {
                $cart['companies_count'] = count($cart['companies']);
                $cart['total'] -= $cart['payment_surcharge'];
                $order['total'] = $cart['total'];
            }
        }
    }

    if (empty($order_id)) {
        $ip = fn_get_ip();
        $order['ip_address'] = $ip['host'];
        $order['timestamp']  = TIME;
        $order['lang_code']  = CART_LANGUAGE;
        $order['company_id'] = 0;
        $order['status']     = STATUS_INCOMPLETED_ORDER; // incomplete by default to increase inventory
        $order_status        = $order['status'];

        if (count($order['product_groups']) > 1 && !$order['parent_order_id']) {
            $order['is_parent_order'] = 'Y';
            $order['status'] = STATUS_PARENT_ORDER;

        } elseif (!empty($order['product_groups'][0]['company_id'])) {
            $order['is_parent_order'] = 'N';
            $order['company_id'] = $order['product_groups'][0]['company_id'];
        }

        if (fn_allowed_for('ULTIMATE')) {
            if (Registry::get('runtime.company_id') == 0) {
                fn_set_notification('E', __('error'), __('text_select_vendor'));

                return false;

            } else {
                $order['company_id'] = Registry::get('runtime.company_id');
            }
        }

        if (defined('CART_LOCALIZATION')) {
            $order['localization_id'] = CART_LOCALIZATION;
        }

        if (!empty($cart['rewrite_order_id'])) {
            $order['order_id'] = array_shift($cart['rewrite_order_id']);
        }

        $order['promotions'] = serialize(!empty($cart['promotions']) ? $cart['promotions'] : array());
        if (!empty($cart['promotions'])) {
            $order['promotion_ids'] = fn_create_set(array_keys($cart['promotions']));
        }

        fn_set_hook('create_order', $order);

        $order_id = db_query("INSERT INTO ?:orders ?e", $order);

    } else {
        unset($order['order_id']);

        // We're editing existing order
        if (isset($cart['promotions'])) {
            $order['promotions'] = serialize($cart['promotions']);
            $order['promotion_ids'] = fn_create_set(array_keys($cart['promotions']));
        }

        $old_order = db_get_row("SELECT company_id, payment_id, status FROM ?:orders WHERE order_id = ?i", $order_id);
        $order['status']     = $old_order['status'];
        $order['company_id'] = $old_order['company_id'];
        $order_status        = $order['status'];

        if (!empty($cart['payment_id']) && $cart['payment_id'] == $old_order['payment_id']) {
            $payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'P'", $order_id);
            if (!empty($payment_info)) {
                $payment_info = unserialize(fn_decrypt_text($payment_info));
                $cart['payment_info'] = array_merge($payment_info, (!empty($cart['payment_info'])) ? $cart['payment_info'] : array());
            }
        }

        // incomplete the order to increase inventory amount.
        fn_change_order_status($order_id, STATUS_INCOMPLETED_ORDER, $old_order['status'], fn_get_notification_rules(array(), false));
        $order['status'] = STATUS_INCOMPLETED_ORDER;

        fn_set_hook('edit_place_order', $order_id);

        $profile_fields = db_get_hash_array("SELECT field_id, value FROM ?:profile_fields_data WHERE object_id = ?i AND object_type = 'O'", 'field_id', $order_id);
        foreach ($profile_fields as $k => $v) {
            if (!isset($cart['user_data']['fields'][$k])) {
                $cart['user_data']['fields'][$k] = $v['value'];
            }
        }

        fn_set_hook('update_order', $order, $order_id);

        db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", $order, $order_id);

        if (!empty($order['products'])) {
            db_query("DELETE FROM ?:order_details WHERE order_id = ?i", $order_id);
        }
    }

    fn_store_profile_fields($cart['user_data'], $order_id, 'O');
    fn_create_order_details($order_id, $cart);
    fn_update_order_data($order_id, $cart);

    // Log order creation/update
    $log_action = !empty($order['order_id']) ? 'update' : 'create';
    fn_log_event('orders', $log_action, array(
        'order_id' => $order_id,
    ));

    //
    // Place the order_id to new_orders table for all admin profiles
    //
    if (empty($order['parent_order_id'])) {
        $condition = "";
        if (fn_allowed_for('ULTIMATE')) {
            $condition = " AND user_type = 'A'";
        }

        if (fn_allowed_for('MULTIVENDOR')) {
            $condition = db_quote(" AND (user_type = 'A' OR (user_type = 'V' AND ?:users.company_id = ?i))", $order['company_id']);
        }

        $admins = db_get_fields("SELECT user_id FROM ?:users WHERE 1 $condition");
        foreach ($admins as $k => $v) {
            db_query("REPLACE INTO ?:new_orders (order_id, user_id) VALUES (?i, ?i)", $order_id, $v);
        }
    }

    return array($order_id, $order_status);
}

function fn_create_order_details($order_id, $cart)
{
    if (!empty($cart['products'])) {
        foreach ((array) $cart['products'] as $k => $v) {
            $product_code = '';
            $extra = empty($v['extra']) ? array() : $v['extra'];
            $v['discount'] = empty($v['discount']) ? 0 : $v['discount'];

            $extra['product'] = empty($v['product']) ? fn_get_product_name($v['product_id']) : $v['product'];

            $extra['company_id'] = !empty($v['company_id']) ? $v['company_id'] : 0;

            if (isset($v['is_edp'])) {
                $extra['is_edp'] = $v['is_edp'];
            }
            if (isset($v['edp_shipping'])) {
                $extra['edp_shipping'] = $v['edp_shipping'];
            }
            if (isset($v['discount'])) {
                $extra['discount'] = $v['discount'];
            }
            if (isset($v['base_price'])) {
                $extra['base_price'] = floatval($v['base_price']);
            }
            if (!empty($v['promotions'])) {
                $extra['promotions'] = $v['promotions'];
            }
            if (!empty($v['stored_price'])) {
                $extra['stored_price'] = $v['stored_price'];
            }

            if (!empty($v['product_options'])) {
                $_options = fn_get_product_options($v['product_id']);
                if (!empty($_options)) {
                    foreach ($_options as $option_id => $option) {
                        if (!isset($v['product_options'][$option_id])) {
                            $v['product_options'][$option_id] = '';
                        }
                    }
                }

                $extra['product_options'] = $v['product_options'];
                $cart_id = fn_generate_cart_id($v['product_id'], array('product_options' => $v['product_options']), true);
                $tracking = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $v['product_id']);

                if ($tracking == ProductTracking::TRACK_WITH_OPTIONS) {
                    $product_code = db_get_field("SELECT product_code FROM ?:product_options_inventory WHERE combination_hash = ?i", $cart_id);
                }

                $extra['product_options_value'] = fn_get_selected_product_options_info($v['product_options']);
            } else {
                $v['product_options'] = array();
            }

            if (empty($product_code)) {
                $product_code = db_get_field("SELECT product_code FROM ?:products WHERE product_id = ?i", $v['product_id']);
            }

            // Check the cart custom files
            if (isset($extra['custom_files'])) {
                $dir_path = 'order_data/' . $order_id;

                foreach ($extra['custom_files'] as $option_id => $files) {
                    if (is_array($files)) {
                        foreach ($files as $file_id => $file) {
                            $file['path'] = 'sess_data/' . fn_basename($file['path']);

                            Storage::instance('custom_files')->copy($file['path'], $dir_path . '/' . $file['file']);

                            Storage::instance('custom_files')->delete($file['path']);
                            Storage::instance('custom_files')->delete($file['path'] . '_thumb');

                            $extra['custom_files'][$option_id][$file_id]['path'] = $dir_path . '/' . $file['file'];
                        }
                    }
                }
            }

            $order_details = array (
                'item_id' => $k,
                'order_id' => $order_id,
                'product_id' => $v['product_id'],
                'product_code' => $product_code,
                'price' => (!empty($v['stored_price']) && $v['stored_price'] == 'Y') ? $v['price'] - $v['discount'] : $v['price'],
                'amount' => $v['amount'],
                'extra' => serialize($extra)
            );

            db_query("INSERT INTO ?:order_details ?e", $order_details);

            // Increase product popularity
            $_data = array (
                'product_id' => $v['product_id'],
                'bought' => 1,
                'total' => POPULARITY_BUY
            );

            db_query("INSERT INTO ?:product_popularity ?e ON DUPLICATE KEY UPDATE bought = bought + 1, total = total + ?i", $_data, POPULARITY_BUY);
        }
    }

}

function fn_update_order_data($order_id, $cart)
{
    $_data = array();
    $clear_types = array();

    if (!empty($cart['product_groups'])) {

        // Save products groups
        $_data[] = array (
            'order_id' => $order_id,
            'type' => 'G', //groups information
            'data' => serialize($cart['product_groups']),
        );

        // Save shipping information
        $chosen_shippings = array();
        foreach ($cart['product_groups'] as $group) {
            $group_shipping = !empty($group['chosen_shippings']) ? $group['chosen_shippings'] : array();
            $chosen_shippings = array_merge($chosen_shippings, $group_shipping);
        }

        fn_apply_stored_shipping_rates($cart, $order_id);
        $_data[] = array (
            'order_id' => $order_id,
            'type' => 'L', //shipping information
            'data' => serialize($chosen_shippings)
        );
    }

    // Save taxes
    if (!empty($cart['taxes'])) {
        $_data[] = array (
            'order_id' => $order_id,
            'type' => 'T', //taxes information
            'data' => serialize($cart['taxes']),
        );
    } elseif (isset($cart['taxes'])) {
        $clear_types[] = 'T';
    }

    // Save payment information
    if (isset($cart['payment_info'])) {
        $_data[] = array (
            'order_id' => $order_id,
            'type' => 'P', //payment information
            'data' => fn_encrypt_text(serialize($cart['payment_info'])),
        );
    }

    // Save coupons information
    if (!empty($cart['coupons'])) {
        $_data[] = array (
            'order_id' => $order_id,
            'type' => 'C', //coupons
            'data' => serialize($cart['coupons']),
        );
    } elseif (isset($cart['coupons'])) {
        $clear_types[] = 'C';
    }

    // Save secondary currency (for order notifications from payments with feedback requests)
    $_data[] = array (
        'order_id' => $order_id,
        'type' => 'R', //secondary currency
        'data' => serialize(CART_SECONDARY_CURRENCY),
    );

    if (!empty($clear_types)) {
        db_query("DELETE FROM ?:order_data WHERE order_id = ?i AND type IN (?a)", $order_id, $clear_types);
    }

    db_query("REPLACE INTO ?:order_data ?m", $_data);

    return true;
}

/**
 * Places an order
 *
 * @param array $cart Array of the cart contents and user information necessary for purchase
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @param string $action Current action. Can be empty or "save"
 * @param int $issuer_id
 * @param int $parent_order_id
 * @return int order_id in case of success, otherwise False
 */
function fn_place_order(&$cart, &$auth, $action = '', $issuer_id = null, $parent_order_id = 0)
{
    $allow = fn_allow_place_order($cart);

    fn_set_hook('pre_place_order', $cart, $allow, $cart['product_groups']);

    if ($allow === false) {
        fn_set_notification('E', __('error'), __('order_was_not_placed'), 'K', 'failed_order_message');
    }

    if ($allow == true && !fn_cart_is_empty($cart)) {

        $cart['parent_order_id'] = $parent_order_id;

        // Remove unallowed chars from cc number
        if (!empty($cart['payment_info']['card_number'])) {
            $cart['payment_info']['card_number'] = str_replace(array(' ', '-'), '', $cart['payment_info']['card_number']);
        }

        if (empty($cart['order_id'])) {
            $cart['user_id']    = $auth['user_id'];
            $cart['tax_exempt'] = $auth['tax_exempt'];
            $cart['issuer_id']  = $issuer_id;
            // Create order
            list($order_id, $order_status) = fn_update_order($cart);

        } else {
            // Update order
            list($order_id, $order_status) = fn_update_order($cart, $cart['order_id']);
        }

        if (!empty($order_id)) {

            // If customer is not logged in, store order ids in the session
            if (empty($auth['user_id'])) {
                $auth['order_ids'][] = $order_id;
            }

            // If order total is zero, just save the order without any processing procedures
            if (floatval($cart['total']) == 0) {
                $action = 'save';
                $order_status = 'P';
            }

            fn_set_hook('place_order', $order_id, $action, $order_status, $cart, $auth);

            $is_processor_script = false;
            if ($action != 'save') {
                list($is_processor_script, ) = fn_check_processor_script($cart['payment_id'], true);
            }

            if (!$is_processor_script && $order_status == STATUS_INCOMPLETED_ORDER) {
                $order_status = 'O';
            }

            $short_order_data = fn_get_order_short_info($order_id);

            // Set new order status
            fn_change_order_status($order_id, $order_status, $short_order_data['status'], (($is_processor_script || $order_status == STATUS_PARENT_ORDER) ? fn_get_notification_rules(array(), true) : fn_get_notification_rules(array())), true);

            $cart['processed_order_id'] = array();
            $cart['processed_order_id'][] = $order_id;

            if (!$parent_order_id && count($cart['product_groups']) > 1) {
                $child_orders = fn_place_suborders($order_id, $cart, $auth, $action, $issuer_id);

                array_unshift($child_orders, $order_id);
                $cart['processed_order_id'] = $child_orders;
            }

            return array($order_id, $action != 'save');
        }
    }

    return array(false, false);
}

function fn_place_suborders($order_id, $cart, &$auth, $action, $issuer_id)
{
    $order_ids = array();
    $rewrite_order_id = empty($cart['rewrite_order_id']) ? array() : $cart['rewrite_order_id'];
    foreach ($cart['product_groups'] as $key_group => $group) {
        $_cart = $cart;
        $total_products_price = 0;
        $total_shipping_cost = 0;
        $total_company_part = 0;
        foreach ($group['products'] as $product) {
            $total_products_price += $product['price'];
        }
        foreach ($_cart['products'] as $cart_id => $product) {
            if (!in_array($cart_id, array_keys($group['products']))) {
                unset($_cart['products'][$cart_id]);
            }
        }

        if (!empty($_cart['chosen_shipping'][$key_group])) {

            $chosen_shipping_id = $_cart['chosen_shipping'][$key_group];

            if (empty($group['chosen_shippings'])) {
                $total_shipping_cost += $group['shippings'][$chosen_shipping_id]['rate'];
            } else {
                foreach ($group['chosen_shippings'] as $shipping) {
                    $total_shipping_cost += $shipping['rate'];
                }
            }

            $_cart['chosen_shipping'] = array($chosen_shipping_id);

        } else {
            $_cart['chosen_shipping'] = array();
        }

        $cost = $cart['subtotal'] + $cart['shipping_cost'];
        if (!$cost) {
            $cost = 1;
        }

        $total_company_part = (($total_products_price + $total_shipping_cost)*100) / $cost;
        $_cart['payment_surcharge'] = $total_company_part * $cart['payment_surcharge'] / 100;
        $_cart['recalculate'] = true;
        if (empty($_cart['stored_shipping'])) {
            $_cart['calculate_shipping'] = true;
        }
        $_cart['rewrite_order_id'] = array();
        if ($next_id = array_shift($rewrite_order_id)) {
            $_cart['rewrite_order_id'][] = $next_id;
        }

        $_cart['company_id'] = $group['company_id'];
        $_cart['parent_order_id'] = $order_id;

        fn_calculate_cart_content($_cart, $auth);
        fn_calculate_payment_taxes($_cart, $auth);

        $_cart['product_groups'] = array($group);

        fn_set_hook('place_suborders', $cart, $_cart);

        list($order_ids[],) = fn_place_order($_cart, $auth, $action, $issuer_id, $order_id);
    }

    return $order_ids;
}

/**
 * Order payment processing
 *
 * @param array $payment payment data
 * @param int $order_id order ID
 * @param bool $force_notification force user notification (true - notify, false - do not notify, order status properties will be skipped)
 * @return bool True on success, false otherwise
 */
function fn_start_payment($order_id, $force_notification = array(), $payment_info = array())
{
    $order_info = fn_get_order_info($order_id);

    if (!empty($order_info['payment_info']) && !empty($payment_info)) {
        $order_info['payment_info'] = $payment_info;
    }

    list($is_processor_script, $processor_data) = fn_check_processor_script($order_info['payment_id']);
    if ($is_processor_script) {
        set_time_limit(300);
        $idata = array (
            'order_id' => $order_id,
            'type' => 'S',
            'data' => TIME,
        );
        db_query("REPLACE INTO ?:order_data ?e", $idata);

        $mode = Registry::get('runtime.mode');

        Embedded::leave();

        include(Registry::get('config.dir.payments') . $processor_data['processor_script']);

        return fn_finish_payment($order_id, $pp_response, $force_notification);
    }

    return false;
}

/**
 * Finish order paymnent
 *
 * @param int $order_id order ID
 * @param array $pp_response payment response
 * @param bool $force_notification force user notification (true - notify, false - do not notify, order status properties will be skipped)
 */
function fn_finish_payment($order_id, $pp_response, $force_notification = array())
{
    // Change order status
    $valid_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);

    if (!empty($valid_id)) {
        db_query("DELETE FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);

        fn_update_order_payment_info($order_id, $pp_response);

        if ($pp_response['order_status'] == 'N' && !empty($_SESSION['cart']['placement_action']) && $_SESSION['cart']['placement_action'] == 'repay') {
            $pp_response['order_status'] = 'I';
        }

        fn_set_hook('finish_payment', $order_id, $pp_response, $force_notification);

        fn_change_order_status($order_id, $pp_response['order_status'], '', $force_notification);
    }
}

//
// Store cart content in the customer's profile
//
function fn_save_cart_content(&$cart, $user_id, $type = 'C', $user_type = 'R')
{
    if (empty($user_id)) {
        if (fn_get_session_data('cu_id')) {
            $user_id = fn_get_session_data('cu_id');
        } else {
            $user_id = fn_crc32(uniqid(TIME));
            fn_set_session_data('cu_id', $user_id, COOKIE_ALIVE_TIME);
        }
        $user_type = 'U';
    }

    if (!empty($user_id)) {
        $condition = db_quote("user_id = ?i AND type = ?s AND user_type = ?s", $user_id, $type, $user_type);
        if (fn_allowed_for('ULTIMATE')) {
            $condition .= fn_get_company_condition('?:user_session_products.company_id');
        }

        db_query("DELETE FROM ?:user_session_products WHERE " . $condition);
        if (!empty($cart['products']) && is_array($cart['products'])) {
            $_cart_prods = $cart['products'];
            foreach ($_cart_prods as $_item_id => $_prod) {
                $_cart_prods[$_item_id]['user_id'] = $user_id;
                $_cart_prods[$_item_id]['timestamp'] = TIME;
                $_cart_prods[$_item_id]['type'] = $type;
                $_cart_prods[$_item_id]['user_type'] = $user_type;
                $_cart_prods[$_item_id]['item_id'] = $_item_id;
                $_cart_prods[$_item_id]['item_type'] = 'P';
                $_cart_prods[$_item_id]['extra'] = serialize($_prod);
                $_cart_prods[$_item_id]['amount'] = empty($_cart_prods[$_item_id]['amount']) ? 1 : $_cart_prods[$_item_id]['amount'];
                $_cart_prods[$_item_id]['session_id'] = Session::getId();
                $ip = fn_get_ip();
                $_cart_prods[$_item_id]['ip_address'] = $ip['host'];

                if (fn_allowed_for('ULTIMATE')) {
                    $_cart_prods[$_item_id]['company_id'] = Registry::get('runtime.company_id');
                }

                if (!empty($_cart_prods[$_item_id])) {
                    db_query('REPLACE INTO ?:user_session_products ?e', $_cart_prods[$_item_id]);
                }
            }
        }

        fn_set_hook('save_cart', $cart, $user_id, $type);

    }

    return true;
}

/**
 * Extract cart content from the customer's profile.
 * $type : C - cart, W - wishlist
 *
 * @param array $cart
 * @param integer $user_id
 * @param string $type
 *
 * @return void
 */
function fn_extract_cart_content(&$cart, $user_id, $type = 'C', $user_type = 'R')
{
    $auth = & $_SESSION['auth'];
    $old_session_id = '';

    // Restore cart content
    if (!empty($user_id)) {
        $item_types = fn_get_cart_content_item_types('X');
        $condition = db_quote("user_id = ?i AND type = ?s AND user_type = ?s AND item_type IN (?a)", $user_id, $type, $user_type, $item_types);

        fn_set_hook('pre_extract_cart', $cart, $condition, $item_types);

        $_prods = db_get_hash_array("SELECT * FROM ?:user_session_products WHERE " . $condition, 'item_id');
        if (!empty($_prods) && is_array($_prods)) {
            $cart['products'] = empty($cart['products']) ? array() : $cart['products'];
            foreach ($_prods as $_item_id => $_prod) {
                $old_session_id = $_prod['session_id'];
                $_prod_extra = unserialize($_prod['extra']);
                unset($_prod['extra']);
                $cart['products'][$_item_id] = empty($cart['products'][$_item_id]) ? fn_array_merge($_prod, $_prod_extra, true) : $cart['products'][$_item_id];
            }
        }
    }

    fn_set_hook('extract_cart', $cart, $user_id, $type, $user_type);

    if ($type == 'C') {
        $cart['change_cart_products'] = true;
        fn_calculate_cart_content($cart, $auth, 'S', false, 'I', false);
    }
}
/**
 * get cart content item types
 *
 * @param string $action
 * V - for View mode
 * X - for eXtract mode
 * @return array
 */
function fn_get_cart_content_item_types($action = 'V')
{
    $item_types = array('P');

    fn_set_hook('get_cart_item_types', $item_types, $action);

    return $item_types;
}

/**
 * Generate title string for order details page
 *
 * @param int $order_id order identifier
 * @return string
 */
function fn_get_order_name($order_id)
{
    $total = db_get_field("SELECT total FROM ?:orders WHERE order_id = ?i", $order_id);
    if ($total == '') {
        return false;
    }

    if (Registry::get('settings.General.alternative_currency') == 'use_selected_and_alternative') {
        $result = fn_format_price_by_currency($total, CART_PRIMARY_CURRENCY);
        if (CART_SECONDARY_CURRENCY != CART_PRIMARY_CURRENCY) {
            $result .= ' (' . fn_format_price_by_currency($total) . ')';
        }
    } else {
        $result = fn_format_price_by_currency($total);
    }

    return $order_id . ' - ' . $result;
}

/**
 * Gets order paid statuses
 *
 * @return array Available paid statuses
 */
function fn_get_order_paid_statuses()
{
    $paid_statuses = db_get_fields('SELECT status FROM ?:status_data WHERE type = ?s AND param = ?s AND value = ?s', 'O', 'inventory', 'D');

    /**
     * Get order paid statuses (at the end of fn_get_order_paid_statuses())
     *
     * @param array $paid_statuses List of order paid statuses
     */
    fn_set_hook('get_order_paid_statuses_post', $paid_statuses);

    return $paid_statuses;
}

/**
 * Gets orders status
 *
 * @param $params
 *        order_id - Order identifier
 *        status - Orders status
 * @return array Orders status
 */
function fn_get_orders_status($params = '')
{
    $condition = '';

    if (!empty($params['order_id'])) {
        $condition .= db_quote(' AND order_id = ?i', $params['order_id']);
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND status = ?s', $params['status']);
    }

    $statuses = db_get_array('SELECT order_id, status FROM ?:orders WHERE 1 ?p', $condition);

    return $statuses;
}

function fn_format_price_by_currency($price, $currency_code = CART_SECONDARY_CURRENCY)
{
    $currencies = Registry::get('currencies');
    $currency = $currencies[$currency_code];
    $result = fn_format_rate_value($price, 'F', $currency['decimals'], $currency['decimals_separator'], $currency['thousands_separator'], $currency['coefficient']);
    if ($currency['after'] == 'Y') {
        $result .= ' ' . $currency['symbol'];
    } else {
        $result = $currency['symbol'] . $result;
    }

    return $result;
}

//
// Get order info
//
function fn_get_order_info($order_id, $native_language = false, $format_info = true, $get_edp_files = false, $skip_static_values = false)
{
    if (!empty($order_id)) {

        $condition = fn_get_company_condition('?:orders.company_id');
        $order = db_get_row("SELECT * FROM ?:orders WHERE ?:orders.order_id = ?i $condition", $order_id);

        if (!empty($order)) {
            $lang_code = ($native_language == true) ? $order['lang_code'] : CART_LANGUAGE;

            $order['payment_method'] = fn_get_payment_method_data($order['payment_id'], $lang_code);
            // Get additional profile fields
            $additional_fields = db_get_hash_single_array(
                "SELECT field_id, value FROM ?:profile_fields_data "
                . "WHERE object_id = ?i AND object_type = 'O'",
                array('field_id', 'value'), $order_id
            );
            $order['fields'] = $additional_fields;

            $order['products'] = db_get_hash_array(
                "SELECT ?:order_details.*, ?:product_descriptions.product, ?:products.status as product_status FROM ?:order_details "
                . "LEFT JOIN ?:product_descriptions ON ?:order_details.product_id = ?:product_descriptions.product_id AND ?:product_descriptions.lang_code = ?s "
                . "LEFT JOIN ?:products ON ?:order_details.product_id = ?:products.product_id "
                . "WHERE ?:order_details.order_id = ?i ORDER BY ?:product_descriptions.product",
                'item_id', $lang_code, $order_id
            );

            $order['promotions'] = unserialize($order['promotions']);
            if (!empty($order['promotions'])) { // collect additional data
                $params = array (
                    'promotion_id' => array_keys($order['promotions']),
                );
                list($promotions) = fn_get_promotions($params);
                foreach ($promotions as $pr_id => $p) {
                    $order['promotions'][$pr_id]['name'] = $p['name'];
                    $order['promotions'][$pr_id]['short_description'] = $p['short_description'];
                }
            }

            // Get additional data
            $additional_data = db_get_hash_single_array("SELECT type, data FROM ?:order_data WHERE order_id = ?i", array('type', 'data'), $order_id);

            $order['taxes'] = array();
            $order['tax_subtotal'] = 0;
            $order['display_shipping_cost'] = $order['shipping_cost'];

            // Replace country, state and title values with their descriptions
            $order_company_id = isset($order['company_id']) ? $order['company_id'] : ''; // company_id will be rewritten by user field, so need to save it.
            fn_add_user_data_descriptions($order, $lang_code);
            $order['company_id'] = $order_company_id;

            $order['need_shipping'] = false;
            $deps = array();

            // Get shipping information
            if (!empty($additional_data['L'])) {
                $order['shipping'] = unserialize($additional_data['L']);

                foreach ($order['shipping'] as $key => $v) {
                    $shipping_id = isset($v['shipping_id']) ? $v['shipping_id'] : 0;
                    $shipping_name = fn_get_shipping_name($shipping_id, $lang_code);
                    if ($shipping_name) {
                        $order['shipping'][$key]['shipping'] = $shipping_name;
                    }
                }
            }

            if (!fn_allowed_for('ULTIMATE:FREE')) {
                // Get shipments common information
                $order['shipment_ids'] = db_get_fields(
                    "SELECT sh.shipment_id FROM ?:shipments AS sh LEFT JOIN ?:shipment_items AS s_items ON (sh.shipment_id = s_items.shipment_id) "
                    . "WHERE s_items.order_id = ?i GROUP BY s_items.shipment_id",
                    $order_id
                );

                $_products = db_get_array("SELECT item_id, SUM(amount) AS amount FROM ?:shipment_items WHERE order_id = ?i GROUP BY item_id", $order_id);
                $shipped_products = array();

                if (!empty($_products)) {
                    foreach ($_products as $_product) {
                        $shipped_products[$_product['item_id']] = $_product['amount'];
                    }
                }
                unset($_products);

            }
            foreach ($order['products'] as $k => $v) {
                //Check for product existance
                if (empty($v['product'])) {
                    $order['products'][$k]['deleted_product'] = true;
                } else {
                    $order['products'][$k]['deleted_product'] = false;
                }

                $order['products'][$k]['discount'] = 0;

                $v['extra'] = @unserialize($v['extra']);
                if ($order['products'][$k]['deleted_product'] == true && !empty($v['extra']['product'])) {
                    $order['products'][$k]['product'] = $v['extra']['product'];
                } else {
                    $order['products'][$k]['product'] = fn_get_product_name($v['product_id'], $lang_code);
                }

                $order['products'][$k]['company_id'] = empty($v['extra']['company_id']) ? 0 : $v['extra']['company_id'];

                if (!empty($v['extra']['discount']) && floatval($v['extra']['discount'])) {
                    $order['products'][$k]['discount'] = $v['extra']['discount'];
                    $order['use_discount'] = true;
                }

                if (!empty($v['extra']['promotions'])) {
                    $order['products'][$k]['promotions'] = $v['extra']['promotions'];
                }

                if (isset($v['extra']['base_price'])) {
                    $order['products'][$k]['base_price'] = floatval($v['extra']['base_price']);
                } else {
                    $order['products'][$k]['base_price'] = $v['price'];
                }
                $order['products'][$k]['original_price'] = $order['products'][$k]['base_price'];

                // Form hash key for this product
                $order['products'][$k]['cart_id'] = $v['item_id'];
                $deps['P_'.$order['products'][$k]['cart_id']] = $k;

                // Unserialize and collect product options information
                if (!empty($v['extra']['product_options'])) {
                    if ($format_info == true) {
                        if (!empty($v['extra']['product_options_value'])) {
                            $order['products'][$k]['product_options'] = $v['extra']['product_options_value'];
                        } else {
                            $order['products'][$k]['product_options'] = fn_get_selected_product_options_info($v['extra']['product_options'], $lang_code);
                        }
                    }

                    $product_options_value = ($skip_static_values == false && !empty($v['extra']['product_options_value'])) ? $v['extra']['product_options_value'] : array();

                    if (empty($v['extra']['stored_price']) || (!empty($v['extra']['stored_price']) && $v['extra']['stored_price'] != 'Y')) { // apply modifiers if this is not the custom price
                        $order['products'][$k]['original_price'] = fn_apply_options_modifiers($v['extra']['product_options'], $order['products'][$k]['base_price'], 'P', $product_options_value, array('product_data' => $v));
                    }
                }

                $order['products'][$k]['extra'] = $v['extra'];
                $order['products'][$k]['tax_value'] = 0;
                $order['products'][$k]['display_subtotal'] = $order['products'][$k]['subtotal'] = ($v['price'] * $v['amount']);

                // Get information about edp
                if ($get_edp_files == true && $order['products'][$k]['extra']['is_edp'] == 'Y') {
                    $order['products'][$k]['files'] = db_get_array(
                        "SELECT ?:product_files.file_id, ?:product_files.activation_type, ?:product_files.max_downloads, "
                        . "?:product_file_descriptions.file_name, ?:product_file_ekeys.active, ?:product_file_ekeys.downloads, "
                        . "?:product_file_ekeys.ekey, ?:product_file_ekeys.ttl FROM ?:product_files "
                        . "LEFT JOIN ?:product_file_descriptions ON ?:product_file_descriptions.file_id = ?:product_files.file_id "
                        . "AND ?:product_file_descriptions.lang_code = ?s "
                        . "LEFT JOIN ?:product_file_ekeys ON ?:product_file_ekeys.file_id = ?:product_files.file_id "
                        . "AND ?:product_file_ekeys.order_id = ?i WHERE ?:product_files.product_id = ?i",
                        $lang_code, $order_id, $v['product_id']
                    );
                }

                // Get shipments information
                // If current edition is FREE, we still need to check shipments accessibility (need to display promotion link)
                if (isset($shipped_products[$k])) {
                    $order['products'][$k]['shipped_amount'] = $shipped_products[$k];
                    $order['products'][$k]['shipment_amount'] = $v['amount'] - $shipped_products[$k];

                } else {
                    $order['products'][$k]['shipped_amount'] = 0;
                    $order['products'][$k]['shipment_amount'] = $v['amount'];
                }

                if ($order['products'][$k]['shipped_amount'] < $order['products'][$k]['amount']) {
                    if (!empty($order['shipping'])) {
                        $group_key = empty($v['extra']['group_key']) ? 0 : $v['extra']['group_key'];
                        $order['shipping'][$group_key]['need_shipment'] = true;
                    } else {
                        $order['need_shipment'] = true;
                    }
                }

                // Check if the order needs the shipping method
                if (!($v['extra']['is_edp'] == 'Y' && (!isset($v['extra']['edp_shipping']) || $v['extra']['edp_shipping'] != 'Y'))) {
                    $order['need_shipping'] = true;
                }

                // Adds flag that defines if product page is available
                $order['products'][$k]['is_accessible'] = fn_is_accessible_product($v);

                fn_set_hook('get_order_items_info_post', $order, $v, $k);
            }

            // Unserialize and collect taxes information
            if (!empty($additional_data['T'])) {
                $order['taxes'] = unserialize($additional_data['T']);
                if (is_array($order['taxes'])) {
                    foreach ($order['taxes'] as  $tax_id => $tax_data) {
                        if (Registry::get('settings.General.tax_calculation') == 'unit_price') {
                            foreach ($tax_data['applies'] as $_id => $value) {
                                if (strpos($_id, 'P_') !== false && isset($deps[$_id])) {
                                    $order['products'][$deps[$_id]]['tax_value'] += $value;
                                    if ($tax_data['price_includes_tax'] != 'Y') {
                                        $order['products'][$deps[$_id]]['subtotal'] += $value;
                                        $order['products'][$deps[$_id]]['display_subtotal'] += (Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y') ? $value : 0;
                                    }
                                }
                                if (strpos($_id, 'S_') !== false) {
                                    if ($tax_data['price_includes_tax'] != 'Y') {
                                        $order['shipping_cost'] += $value;
                                        $order['display_shipping_cost'] += (Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y') ? $value : 0;
                                    }
                                }
                            }
                        }

                        if ($tax_data['price_includes_tax'] != 'Y') {
                            $order['tax_subtotal'] += $tax_data['tax_subtotal'];
                        }
                    }
                } else {
                    $order['taxes'] = array();
                }
            }

            if (!empty($additional_data['C'])) {
                $order['coupons'] = unserialize($additional_data['C']);
            }

            if (!empty($additional_data['R'])) {
                $order['secondary_currency'] = unserialize($additional_data['R']);
            }

            if (!empty($order['issuer_id'])) {
                $order['issuer_data'] = fn_get_user_short_info($order['issuer_id']);
            }

            // Recalculate subtotal
            $order['subtotal'] = $order['display_subtotal'] = 0;
            foreach ($order['products'] as $v) {
                $order['subtotal'] += $v['subtotal'];
                $order['display_subtotal'] += $v['display_subtotal'];
            }

            // Unserialize and collect payment information
            if (!empty($additional_data['P'])) {
                $order['payment_info'] = unserialize(fn_decrypt_text($additional_data['P']));
            }

            if (empty($order['payment_info']) || !is_array($order['payment_info'])) {
                $order['payment_info'] = array();
            }

            // Get shipping information
            if (!empty($additional_data['G'])) {
                $order['product_groups'] = unserialize($additional_data['G']);
            }

            $order['doc_ids'] = db_get_hash_single_array("SELECT type, doc_id FROM ?:order_docs WHERE order_id = ?i", array('type', 'doc_id'), $order_id);
        }

        fn_set_hook('get_order_info', $order, $additional_data);

        return $order;
    }

    return false;
}

/**
 * Check that the first paid order
 *
 * @param array $order Order data
 *
 */
function fn_check_first_order(&$order)
{
    if (AREA == 'A' && !fn_get_storage_data('first_order') && $_SESSION['auth']['is_root'] == 'Y') {
        $status = !empty($order['status']) ? $order['status'] : '';

        if ($status == 'P' || $status == 'C') {
            $order['first_order'] = true;
            fn_set_storage_data('first_order', true);
            Registry::get('view')->assign('mode','notification');
            fn_set_notification('S', __('well_done'), Registry::get('view')->fetch('common/share.tpl'));
        }
    }
}

/**
 * Checks if product is currently accessible for viewing
 *
 * @param array $product Product data
 * @return boolean Flag that defines if product is accessible
 */
function fn_is_accessible_product($product)
{
    $result = false;

    $status = db_get_field('SELECT status FROM ?:products WHERE product_id = ?i', $product['product_id']);
    if (!empty($status) && $status != "D") {
        $result = true;
    }

    /**
     * Changes result of product accessability checking
     * @param array   $product Product data
     * @param boolean $result  Flag that defines if product is accessible
     */
    fn_set_hook('is_accessible_product_post', $product, $result);

    return $result;
}

//
// Get order short info
//
function fn_get_order_short_info($order_id)
{
    if (!empty($order_id)) {
        $order = db_get_row("SELECT total, status, issuer_id, firstname, lastname, timestamp, is_parent_order FROM ?:orders WHERE order_id = ?i", $order_id);

        return $order;
    }

    return false;
}

/**
 * Get suborders short info
 *
 * @param int $parent_order_id
 * @return array
 */
function fn_get_suborders_info($parent_order_id)
{
    $orders = array();

    if ($parent_order_id) {
        $orders = db_get_array('SELECT total, status, issuer_id, firstname, lastname, timestamp FROM ?:orders WHERE parent_order_id = ?i', $parent_order_id);
    }

    return $orders;
}

/**
 * Change order status
 *
 * @param int $order_id Order identifier
 * @param string $status_to New order status (one char)
 * @param string $status_from Old order status (one char)
 * @param array $force_notification Array with notification rules
 * @param boolean $place_order True, if this function have been called inside of fn_place_order function.
 * @return boolean
 */
function fn_change_order_status($order_id, $status_to, $status_from = '', $force_notification = array(), $place_order = false)
{
    $order_info = fn_get_order_info($order_id, true);

    if (!$order_info) {
        return false;
    }

    if (defined('CART_LOCALIZATION') && $order_info['localization_id'] && CART_LOCALIZATION != $order_info['localization_id']) {
        Registry::get('view')->assign('localization', fn_get_localization_data(CART_LOCALIZATION));
    }

    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, ($order_info['lang_code'] ? $order_info['lang_code'] : CART_LANGUAGE), $order_info['company_id']);

    if (empty($status_from)) {
        $status_from = $order_info['status'];
    }

    if (empty($order_info) || empty($status_to) || $status_from == $status_to) {
        return false;
    }

    if (fn_allowed_for('MULTIVENDOR')) {
        if ($order_info['is_parent_order'] == 'Y') {
            $child_ids = db_get_fields("SELECT order_id FROM ?:orders WHERE parent_order_id = ?i", $order_id);
            $res = $_res = true;
            foreach ($child_ids as $child_order_id) {
                 $_res = fn_change_order_status($child_order_id, $status_to, '', $force_notification, $place_order);
            }
            $res = $res && $_res;

            return $res;
        }
    }

    $_updated_ids = array();
    $_error = false;

    foreach ($order_info['products'] as $k => $v) {

        // Generate ekey if EDP is ordered
        if (!empty($v['extra']['is_edp']) && $v['extra']['is_edp'] == 'Y') {
            continue; // don't track inventory
        }

        // Update product amount if inventory tracking is enabled
        if (Registry::get('settings.General.inventory_tracking') == 'Y') {
            if ($order_statuses[$status_to]['params']['inventory'] == 'D' && $order_statuses[$status_from]['params']['inventory'] == 'I') {
                // decrease amount
                if (fn_update_product_amount($v['product_id'], $v['amount'], @$v['extra']['product_options'], '-') == false) {
                    $status_to = 'B'; //backorder
                    $_error = true;
                    fn_set_notification('W', __('warning'), __('low_stock_subj', array(
                        '[product]' => fn_get_product_name($v['product_id']) . ' #' . $v['product_id']
                    )));

                    break;
                } else {
                    $_updated_ids[] = $k;
                }
            } elseif ($order_statuses[$status_to]['params']['inventory'] == 'I' && $order_statuses[$status_from]['params']['inventory'] == 'D') {
                // increase amount
                fn_update_product_amount($v['product_id'], $v['amount'], @$v['extra']['product_options'], '+');
            }
        }
    }

    if ($_error) {
        if (!empty($_updated_ids)) {
            foreach ($_updated_ids as $id) {
                // increase amount
                fn_update_product_amount($order_info['products'][$id]['product_id'], $order_info['products'][$id]['amount'], @$order_info['products'][$id]['extra']['product_options'], '+');
            }
            unset($_updated_ids);
        }

        if ($status_from == $status_to) {
            return false;
        }
    }

    fn_set_hook('change_order_status', $status_to, $status_from, $order_info, $force_notification, $order_statuses, $place_order);

    if ($status_from == $status_to) {
        if (!empty($_updated_ids)) {
            foreach ($_updated_ids as $id) {
                // increase amount
                fn_update_product_amount($order_info['products'][$id]['product_id'], $order_info['products'][$id]['amount'], @$order_info['products'][$id]['extra']['product_options'], '+');
            }
            unset($_updated_ids);
        }

        return false;
    }

    fn_promotion_post_processing($status_to, $status_from, $order_info, $force_notification);

    // Log order status change
    fn_log_event('orders', 'status', array (
        'order_id' => $order_id,
        'status_from' => $status_from,
        'status_to' => $status_to,
    ));

    if (!empty($order_statuses[$status_to]['params']['appearance_type']) && ($order_statuses[$status_to]['params']['appearance_type'] == 'I' || $order_statuses[$status_to]['params']['appearance_type'] == 'C') && !db_get_field("SELECT doc_id FROM ?:order_docs WHERE type = ?s AND order_id = ?i", $order_statuses[$status_to]['params']['appearance_type'], $order_id)) {
        $_data = array (
            'order_id' => $order_id,
            'type' => $order_statuses[$status_to]['params']['appearance_type']
        );
        $order_info['doc_ids'][$order_statuses[$status_to]['params']['appearance_type']] = db_query("INSERT INTO ?:order_docs ?e", $_data);
    }

    // Check if we need to remove CC info
    if (!empty($order_statuses[$status_to]['params']['remove_cc_info']) && $order_statuses[$status_to]['params']['remove_cc_info'] == 'Y' && !empty($order_info['payment_info'])) {
        fn_cleanup_payment_info($order_id, $order_info['payment_info'], true);
    }

    $edp_data = fn_generate_ekeys_for_edp(array('status_from' => $status_from, 'status_to' => $status_to), $order_info);
    $order_info['status'] = $status_to;

    fn_order_notification($order_info, $edp_data, $force_notification);

    db_query("UPDATE ?:orders SET status = ?s WHERE order_id = ?i", $status_to, $order_id);

    return true;
}

/**
 * Function delete order
 *
 * @param int $order_id
 * @return int
 */
function fn_delete_order($order_id)
{
    if (Registry::get('runtime.company_id') && !fn_allowed_for('ULTIMATE')) {
        fn_company_access_denied_notification();

        return false;
    }

    // Log order deletion
    fn_log_event('orders', 'delete', array (
        'order_id' => $order_id,
    ));

    fn_change_order_status($order_id, STATUS_INCOMPLETED_ORDER, '', fn_get_notification_rules(array(), false)); // incomplete to increase inventory

    fn_set_hook('delete_order', $order_id);

    db_query("DELETE FROM ?:new_orders WHERE order_id = ?i", $order_id);
    db_query("DELETE FROM ?:order_data WHERE order_id = ?i", $order_id);
    db_query("DELETE FROM ?:order_details WHERE order_id = ?i", $order_id);
    $result = db_query("DELETE FROM ?:orders WHERE order_id = ?i", $order_id);
    db_query("DELETE FROM ?:product_file_ekeys WHERE order_id = ?i", $order_id);
    db_query("DELETE FROM ?:profile_fields_data WHERE object_id = ?i AND object_type='O'", $order_id);
    db_query("DELETE FROM ?:order_docs WHERE order_id = ?i", $order_id);

    // Delete shipments
    $shipment_ids = db_get_fields('SELECT shipment_id FROM ?:shipment_items WHERE order_id = ?i GROUP BY shipment_id', $order_id);
    if (!empty($shipment_ids)) {
        db_query('DELETE FROM ?:shipments WHERE shipment_id IN (?a)', $shipment_ids);
        db_query('DELETE FROM ?:shipment_items WHERE order_id = ?i', $order_id);
    }

    return $result;
}

/**
 * Function generate edp ekeys for email notification
 *
 * @param array $statuses order statuses
 * @param array $order_info order information
 * @param array $active_files array with file download statuses
 * @return array $edp_data
 */

function fn_generate_ekeys_for_edp($statuses, $order_info, $active_files = array())
{
    $edp_data = array();
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true);

    foreach ($order_info['products'] as $v) {

        // Generate ekey if EDP is ordered
        if (!empty($v['extra']['is_edp']) && $v['extra']['is_edp'] == 'Y') {

            $activations = db_get_hash_single_array("SELECT activation_type, file_id FROM ?:product_files WHERE product_id = ?i", array('file_id', 'activation_type'), $v['product_id']);

            foreach ($activations as $file_id => $activation_type) {

                $send_notification = false;

                // Check if ekey already was generated for this file
                $_ekey = db_get_row("SELECT ekey, active, file_id, product_id, order_id, ekey FROM ?:product_file_ekeys WHERE file_id = ?i AND order_id = ?i", $file_id, $order_info['order_id']);
                if (!empty($_ekey)) {
                    // If order status changed to "Processed"
                    if (($activation_type == 'P') && !empty($statuses)) {
                        if ($order_statuses[$statuses['status_to']]['params']['inventory'] == 'D' && substr_count('O', $statuses['status_to']) == 0 && ($order_statuses[$statuses['status_from']]['params']['inventory'] != 'D' || substr_count('O', $statuses['status_from']) > 0)) {
                            $active_files[$v['product_id']][$file_id] = 'Y';
                        } elseif (($order_statuses[$statuses['status_to']]['params']['inventory'] != 'D' && substr_count('O', $statuses['status_from']) == 0 || substr_count('O', $statuses['status_to']) > 0) && $order_statuses[$statuses['status_from']]['params']['inventory'] == 'D') {
                            $active_files[$v['product_id']][$file_id] = 'N';
                        }
                    }

                    if (!empty($active_files[$v['product_id']][$file_id])) {
                        db_query('UPDATE ?:product_file_ekeys SET ?u WHERE file_id = ?i AND product_id = ?i AND order_id = ?i', array('active' => $active_files[$v['product_id']][$file_id]), $_ekey['file_id'], $_ekey['product_id'], $_ekey['order_id']);

                        if ($active_files[$v['product_id']][$file_id] == 'Y' && $_ekey['active'] !== 'Y') {
                            $edp_data[$v['product_id']]['files'][$file_id] = $_ekey;
                        }
                    }

                } else {
                    $_data = array (
                        'file_id' => $file_id,
                        'product_id' => $v['product_id'],
                        'ekey' => md5(uniqid(rand())),
                        'ttl' => (TIME + (Registry::get('settings.General.edp_key_ttl') * 60 * 60)),
                        'order_id' => $order_info['order_id'],
                        'activation' => $activation_type
                    );

                    // Activate the file if type is "Immediately" or "After full payment" and order statuses is from "paid" group
                    if ($activation_type == 'I' || !empty($active_files[$v['product_id']][$file_id]) && $active_files[$v['product_id']][$file_id] == 'Y' || ($activation_type == 'P' && $order_statuses[$statuses['status_to']]['params']['inventory'] == 'D' && substr_count('O', $statuses['status_to']) == 0 && ($order_statuses[$statuses['status_from']]['params']['inventory'] != 'D' || substr_count('O', $statuses['status_from']) > 0 ))) {
                        $_data['active'] = 'Y';
                        $edp_data[$v['product_id']]['files'][$file_id] = $_data;
                    }

                    db_query('REPLACE INTO ?:product_file_ekeys ?e', $_data);
                }

                if (!empty($edp_data[$v['product_id']]['files'][$file_id])) {
                    $edp_data[$v['product_id']]['files'][$file_id]['file_size'] = db_get_field("SELECT file_size FROM ?:product_files WHERE file_id = ?i", $file_id);
                    $edp_data[$v['product_id']]['files'][$file_id]['file_name'] = db_get_field("SELECT file_name FROM ?:product_file_descriptions WHERE file_id = ?i AND lang_code = ?s", $file_id, CART_LANGUAGE);
                }
            }
        }
    }

    return $edp_data;
}

/**
 * Updates order payment information
 *
 * @param int $order_id
 * @param array $pp_response Response from payment processor
 * @return boolean true
 */
function fn_update_order_payment_info($order_id, $pp_response)
{
    if (empty($order_id) || empty($pp_response) || !is_array($pp_response)) {
        return false;
    }

    $payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'P'", $order_id);
    if (!empty($payment_info)) {
        $payment_info = unserialize(fn_decrypt_text($payment_info));
    } else {
        $payment_info = array();
    }

    foreach ($pp_response as $k => $v) {
        $payment_info[$k] = $v;
    }

    $data = array (
        'data' => fn_encrypt_text(serialize($payment_info)),
        'order_id' => $order_id,
        'type' => 'P'
    );

    db_query("REPLACE INTO ?:order_data ?e", $data);

    $child_orders_ids = db_get_fields("SELECT order_id FROM ?:orders WHERE parent_order_id = ?i", $order_id);
    if (!empty($child_orders_ids)) {
        foreach ($child_orders_ids as $child_id) {
            fn_update_order_payment_info($child_id, $pp_response);
        }
    }

    return true;
}

/**
 * Get shipping info
 *
 * @param int $shipping_id Shipping ID
 * @param string $lang_code Language code
 * @return array Shipping info
 */
function fn_get_shipping_info($shipping_id, $lang_code = CART_LANGUAGE)
{

    $fields = array(
        '?:shippings.*',
        '?:shipping_descriptions.shipping',
        '?:shipping_descriptions.delivery_time'
    );
    $join = db_quote(" LEFT JOIN ?:shipping_descriptions ON ?:shipping_descriptions.shipping_id = ?:shippings.shipping_id AND ?:shipping_descriptions.lang_code = ?s", $lang_code);
    $conditions = "";

    fn_set_hook('get_shipping_info', $shipping_id, $fields, $join, $conditions);

    $shipping = array();
    if (!empty($shipping_id)) {
        $shipping = db_get_row("SELECT ?p FROM ?:shippings ?p WHERE ?:shippings.shipping_id = ?i ?p", implode(', ', $fields), $join, $shipping_id, $conditions);
    }

    if (!empty($shipping)) {
        $shipping['tax_ids'] = empty($shipping['tax_ids']) ? array() : fn_explode(',', $shipping['tax_ids']);
        $shipping['icon'] = fn_get_image_pairs($shipping['shipping_id'], 'shipping', 'M', true, true, $lang_code);

        if (!empty($shipping['service_params'])) {
            $shipping['service_params'] = unserialize($shipping['service_params']);
        }

        $destinations = array();
        if ($shipping['rate_calculation'] == 'M') {
            $destinations = fn_get_destinations();

            foreach ($destinations as $k => $v) {

                if (!empty($shipping['localization'])) { // check available destinations, but skip default destination
                    $_s = fn_explode(',', $shipping['localization']);
                    $_l = fn_explode(',', $v['localization']);
                    if (!array_intersect($_s, $_l)) {
                        continue;
                    }
                }
                $destinations[$k] = array_merge($destinations[$k], fn_get_shipping_rate($shipping_id, $v['destination_id']));
            }
        } else {
            $destinations[0] = db_get_row("SELECT rate_id, rate_value, destination_id FROM ?:shipping_rates WHERE shipping_id = ?i AND destination_id = 0", $shipping_id);
            $destinations[0] = array_merge($destinations[0], fn_get_shipping_rate($shipping_id, 0));
        }

        $shipping['rates'] = $destinations;

    }

    return $shipping;
}

/**
 * Get shipping rate
 *
 * @param int $shipping_id Shipping ID
 * @param int $destination_id location
 * @return array rate info
 */
function fn_get_shipping_rate($shipping_id, $destination_id)
{
    $rate = db_get_row("SELECT rate_id, rate_value, destination_id, IF(rate_value = '', 0, 1) as rates_defined FROM ?:shipping_rates WHERE shipping_id = ?i AND destination_id = ?i", $shipping_id, $destination_id);

    if (!empty($rate)) {
        $rate['rate_value'] = unserialize($rate['rate_value']);
    }

    if (empty($rate['rate_value']['C'][0])) {
        $rate['rate_value']['C'][0] = array();
    }
    if (empty($rate['rate_value']['W'][0])) {
        $rate['rate_value']['W'][0] = array();
    }
    if (empty($rate['rate_value']['I'][0])) {
        $rate['rate_value']['I'][0] = array();
    }

    return $rate;
}

//
// Get all shippings list
//
function fn_get_shippings($simple, $lang_code = CART_LANGUAGE)
{
    $conditions = '1';

    if (AREA == 'C') {
        $conditions .= " AND (" . fn_find_array_in_set($_SESSION['auth']['usergroup_ids'], 'a.usergroup_ids', true) . ")";
        $conditions .= " AND a.status = 'A'";
        $conditions .= fn_get_localizations_condition('a.localization');
    }

    if ($simple == true) {
        return db_get_hash_single_array("SELECT a.shipping_id, b.shipping FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id = b.shipping_id AND b.lang_code = ?s WHERE ?p ORDER BY a.position", array('shipping_id', 'shipping'), $lang_code, $conditions);
    } else {
        return db_get_array("SELECT a.shipping_id, a.min_weight, a.max_weight, a.position, a.status, b.shipping, b.delivery_time, a.usergroup_ids FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id = b.shipping_id AND b.lang_code = ?s WHERE ?p ORDER BY a.position", $lang_code, $conditions);
    }
}

/**
 * Get all available carriers
 *
 * @return array List of available carrier codes
 */
function fn_get_carriers()
{
    $carriers = db_get_fields('SELECT module FROM ?:shipping_services GROUP BY module');

    return $carriers;
}

/**
 * Gets shipping name
 *
 * @param int $shipping_id shipping identifier
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return string Shipping name if shipping identifier is not null; false otherwise
 */
function fn_get_shipping_name($shipping_id, $lang_code = CART_LANGUAGE)
{
    if (!empty($shipping_id)) {
        return db_get_field("SELECT shipping FROM ?:shipping_descriptions WHERE shipping_id = ?i AND lang_code = ?s", $shipping_id, $lang_code);
    }

    return false;
}

/**
 * Create/Update shipping name
 *
 * @param array $shipping_data shipping info
 * @param int $shipping_id shipping identifier
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return string Shipping name if shipping identifier is not null; false otherwise
 */
function fn_update_shipping($shipping_data, $shipping_id, $lang_code = DESCR_SL)
{
    if (!empty($shipping_data['shipping']) || !empty($shipping_id)) {
        $shipping_data['localization'] = empty($shipping_data['localization']) ? '' : fn_implode_localizations($shipping_data['localization']);

        $shipping_data['tax_ids'] = !empty($shipping_data['tax_ids']) ? fn_create_set($shipping_data['tax_ids']) : '';

        $shipping_data['usergroup_ids'] = empty($shipping_data['usergroup_ids']) ? '0' : implode(',', $shipping_data['usergroup_ids']);
        unset($shipping_data['shipping_id']);

        if (isset($shipping_data['service_params'])) {
            $shipping_data['service_params'] = serialize($shipping_data['service_params']);
        }

        fn_set_hook('update_shipping', $shipping_data, $shipping_id, $lang_code);

        if (!empty($shipping_id)) {
            $action = 'update';

            $arow = db_query("UPDATE ?:shippings SET ?u WHERE shipping_id = ?i", $shipping_data, $shipping_id);
            db_query("UPDATE ?:shipping_descriptions SET ?u WHERE shipping_id = ?i AND lang_code = ?s", $shipping_data, $shipping_id, $lang_code);

            if ($arow === false) {
                fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('shipping'))),'','404');
                $shipping_id = false;
            }
        } else {
            $action = 'add';

            $shipping_id = $shipping_data['shipping_id'] = db_query("INSERT INTO ?:shippings ?e", $shipping_data);

            foreach (fn_get_translation_languages() as $shipping_data['lang_code'] => $_v) {
                db_query("INSERT INTO ?:shipping_descriptions ?e", $shipping_data);
            }
        }

        fn_set_hook('update_shipping_post', $shipping_data, $shipping_id, $lang_code, $action);

        if ($shipping_id) {
            fn_attach_image_pairs('shipping', 'shipping', $shipping_id, $lang_code);
            if (!empty($shipping_data['rates'])) {
                fn_update_shipping_rates($shipping_data, $shipping_id);
            }
        }
    }

    return $shipping_id;
}

/**
 * Update shipping rates
 *
 * @param array $shipping_data shipping info
 * @param int $shipping_id shipping identifier
 * @return string Shipping name if shipping identifier is not null; false otherwise
 */
function fn_update_shipping_rates($shipping_data, $shipping_id)
{
    if (!empty($shipping_id)) {

        foreach ($shipping_data['rates'] as $destination_id => $rate) {

            if (!empty($rate['destination_id'])) {
                $destination_id = $rate['destination_id'];
            }

            $rate_types = array('C','W','I'); // Rate types: Cost, Weight, Items
            $normalized_data = array();

            if (empty($rate['rate_value']['C'][0])) {
                $rates['C'][0] = array();
            }
            if (empty($rates['rate_value']['W'][0])) {
                $rates['W'][0] = array();
            }
            if (empty($rates['rate_value']['I'][0])) {
                $rates['I'][0] = array();
            }

            foreach ($rate_types as $type) {
                // Update rate values
                if (!empty($rate['rate_value'][$type]) && is_array($rate['rate_value'][$type])) {
                    fn_normalized_shipping_rate($normalized_data, $rate['rate_value'][$type], $type);
                }

                // Add new rate values
                if (!empty($shipping_data['add_rates']) && is_array($shipping_data['add_rates'][$destination_id]['rate_value'][$type])) {
                    fn_normalized_shipping_rate($normalized_data, $shipping_data['add_rates'][$destination_id]['rate_value'][$type], $type);
                }

                if (!empty($normalized_data[$type]) && is_array($normalized_data[$type])) {
                    ksort($normalized_data[$type], SORT_NUMERIC);
                }
            }

            if (is_array($normalized_data)) {
                foreach ($normalized_data as $k => $v) {
                    if ((count($v)==1) && (floatval($v[0]['value'])==0)) {
                        unset($normalized_data[$k]);
                        continue;
                    }
                }
            }

            if (fn_is_empty($normalized_data)) {
                db_query("DELETE FROM ?:shipping_rates WHERE shipping_id = ?i AND destination_id = ?i", $shipping_id, $destination_id);
            } else {
                $normalized_data = serialize($normalized_data);
                db_query("REPLACE INTO ?:shipping_rates (rate_value, shipping_id, destination_id) VALUES(?s, ?i, ?i)", $normalized_data, $shipping_id, $destination_id);
            }
        }
    }

}

/**
 * Normalized shipping rates
 *
 * @param array $normalized_data shipping info
 * @param array $rate rate info
 * @param string $rate_type rate types: Cost, Weight, Items
 */
function fn_normalized_shipping_rate(&$normalized_data, $rate, $rate_type)
{
    foreach ($rate as $amount => $v) {

        if (!isset($v['value'])) {
            $v['value'] = 0;
        }
        if (!isset($v['type'])) {
            $v['type'] = 'F';
        }
        if (!isset($v['amount'])) {
            $v['amount'] = $amount;
        }

        $v['amount'] = strval(($rate_type == 'I') ? intval($v['amount']) : floatval($v['amount']));
        $v['value'] = fn_format_price($v['value']);
        $v['per_unit'] = empty($v['per_unit']) ? 'N' : $v['per_unit'];

        if (!isset($normalized_data[$rate_type][$v['amount']]) || floatval($normalized_data[$rate_type][$v['amount']]['value']) == 0) {
            $normalized_data[$rate_type]["$v[amount]"] = array ('amount' => $v['amount'], 'value' => $v['value'], 'type' => $v['type'], 'per_unit' => $v['per_unit']);
        }
    }
}

//
// Get all taxes list
//
function fn_get_taxes($lang_code = CART_LANGUAGE)
{
    return db_get_hash_array("SELECT a.*, b.tax FROM ?:taxes as a LEFT JOIN ?:tax_descriptions as b ON b.tax_id = a.tax_id AND b.lang_code = ?s ORDER BY a.priority", 'tax_id', $lang_code);
}

/**
 * Gets tax data
 *
 * @param int $tax_id tax identifier
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return array Tax data if tax identifier is not null; false otherwise
 */
function fn_get_tax($tax_id, $lang_code = CART_LANGUAGE)
{
    $tax = array();
    if (!empty($tax_id)) {
        $tax = db_get_row("SELECT a.*, tax FROM ?:taxes as a LEFT JOIN ?:tax_descriptions as b ON b.tax_id = a.tax_id AND b.lang_code = ?s WHERE a.tax_id = ?i", $lang_code, $tax_id);
    }

    return $tax;
}

//
// Get tax name
//
function fn_get_tax_name($tax_id = 0, $lang_code = CART_LANGUAGE, $as_array = false)
{
    if (!empty($tax_id)) {
        if (!is_array($tax_id) && strpos($tax_id, ',') !== false) {
            $tax_id = explode(',', $tax_id);
        }
        if (is_array($tax_id) || $as_array == true) {
            return db_get_hash_single_array("SELECT tax_id, tax FROM ?:tax_descriptions WHERE tax_id IN (?n) AND lang_code = ?s", array('tax_id', 'tax'), $tax_id, $lang_code);
        } else {
            return db_get_field("SELECT tax FROM ?:tax_descriptions WHERE tax_id = ?i AND lang_code = ?s", $tax_id, $lang_code);
        }
    }

    return false;
}

//
// Get all rates for specific tax
//
function fn_get_tax_rates($tax_id, $destination_id = 0)
{
    if (empty($tax_id)) {
        return false;
    }

    return db_get_array("SELECT * FROM ?:tax_rates WHERE tax_id = ?i AND destination_id = ?i", $tax_id, $destination_id);
}

//
// Get selected taxes
//
function fn_get_set_taxes($taxes_set)
{
    if (empty($taxes_set)) {
        return false;
    }

    if (!is_array($taxes_set)) {
        $taxes_set = explode(',', $taxes_set);
    }

    return db_get_hash_array("SELECT tax_id, address_type, priority, price_includes_tax, regnumber FROM ?:taxes WHERE tax_id IN (?n) AND status = 'A' ORDER BY priority", 'tax_id', $taxes_set);
}

/**
 * Create/update tax
 *
 * @param array $data Tax data
 * @param int $tax_id tax identifier
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return int $tax_id tax identifier update or create tax
 */
function fn_update_tax($tax_data, $tax_id, $lang_code = CART_LANGUAGE)
{
    /**
     * Update tax data (running before fn_update_tax() function)
     *
     * @param array  $tax_data  Tax data
     * @param int    $tax_id    Product identifier
     * @param string $lang_code Two-letter language code (e.g. 'en', 'ru', etc.)
     */
    fn_set_hook('update_tax_pre', $tax_data, $tax_id, $lang_code);

    if (!empty($tax_id)) {
        $arow = db_query('UPDATE ?:taxes SET ?u WHERE tax_id = ?i', $tax_data, $tax_id);
        db_query('UPDATE ?:tax_descriptions SET ?u WHERE tax_id = ?i AND lang_code = ?s', $tax_data, $tax_id, $lang_code);

        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('tax'))),'','404');
            $tax_id = false;
        }
    } else {
        unset($tax_data['tax_id']);
        $tax_id = $tax_data['tax_id'] = db_query("INSERT INTO ?:taxes ?e", $tax_data);

        foreach (fn_get_translation_languages() as $tax_data['lang_code'] => $_v) {
            db_query("INSERT INTO ?:tax_descriptions ?e", $tax_data);
        }
    }

    // Update rates data
    if (!empty($tax_id) && !empty($tax_data['rates'])) {
        $destination_ids = db_get_fields("SELECT destination_id FROM ?:destinations");

        foreach ($tax_data['rates'] as $destination_id => $v) {
            if (in_array($destination_id, $destination_ids)) {

                $rate_id = db_get_field("SELECT rate_id FROM ?:tax_rates WHERE destination_id = ?i AND tax_id = ?i", $destination_id, $tax_id);

                if (!empty($rate_id)) {
                    $v['rate_value'] = floatval($v['rate_value']);
                    if (empty($v['rate_value'])) {
                        db_query("DELETE FROM ?:tax_rates WHERE rate_id = ?i", $rate_id);
                    } else {
                        db_query("UPDATE ?:tax_rates SET ?u WHERE rate_id = ?i", $v, $rate_id);
                    }
                } elseif (!empty($v['rate_value'])) {
                    $v['destination_id'] = $destination_id;
                    $v['tax_id'] = $tax_id;
                    db_query("INSERT INTO ?:tax_rates ?e", $v);
                }
            }
        }
    }

    return $tax_id;
}

/**
 * Delete tax
 *
 * @param int $tax_id ID of the tax to be removed.
 * @return boolean
 */
function fn_delete_tax($tax_id)
{
    /**
     * Delete taxes (running before fn_delete_taxes() function)
     *
     * @param array $tax_ids Taxes identifier
     */
    fn_set_hook('delete_tax_pre', $tax_id);

    $result = db_query("DELETE FROM ?:taxes WHERE tax_id = ?i", $tax_id);
    db_query("DELETE FROM ?:tax_descriptions WHERE tax_id = ?i", $tax_id);
    db_query("DELETE FROM ?:tax_rates WHERE tax_id = ?i", $tax_id);
    db_query("UPDATE ?:products SET tax_ids = ?p", fn_remove_from_set('tax_ids', $tax_id));
    db_query("UPDATE ?:shippings SET tax_ids = ?p", fn_remove_from_set('tax_ids', $tax_id));

    return $result;
}

/**
 * Delete taxes
 *
 * @param array $tax_ids IDs of the taxes to be removed.
 * @return boolean true
 */
function fn_delete_taxes($tax_ids)
{
    /**
     * Delete taxes (running before fn_delete_taxes() function)
     *
     * @param array $tax_ids Taxes identifier
     */
    fn_set_hook('delete_taxes_pre', $tax_ids);

    foreach ((array) $tax_ids as $v) {
        fn_delete_tax($v);
    }

    return true;
}

function fn_add_exclude_products(&$cart, &$auth)
{
    $subtotal = 0;
    $original_subtotal = 0;

    if (isset($cart['products']) && is_array($cart['products'])) {
        foreach ($cart['products'] as $cart_id => $product) {
            if (empty($product['product_id'])) {
                continue;
            }

            if (isset($product['extra']['exclude_from_calculate'])) {
                if ((empty($cart['order_id']) || defined('ORDER_MANAGEMENT')) && !isset($cart['company_id'])) {
                    fn_delete_cart_product($cart, $cart_id);
                }
            } else {
                if (!isset($product['product_options'])) {
                    $product['product_options'] = array();
                }

                $product_subtotal = fn_apply_options_modifiers($product['product_options'], $product['price'], 'P', array(), array('product_data' => $product)) * $product['amount'];
                $original_subtotal += $product_subtotal;
                $subtotal += $product_subtotal - ((isset($product['discount'])) ? $product['discount'] : 0);
            }
        }
    }

    fn_set_hook('exclude_products_from_calculation', $cart, $auth, $original_subtotal, $subtotal);

}

//
// Calculate cart content
//
// options style:
// F - full
// S - skip selection
// I - info
// calculate_shipping:
// A - calculate all available methods
// E - calculate selected methods only (from cart[shipping])
// S - skip calculation

// Products prices definition
// base_price - price without options modifiers
// original_price - price without discounts (with options modifiers)
// price - price includes discount and taxes
// original_subtotal - original_price * product qty
// subtotal - price * product qty
// discount - discount for this product
// display_price - the displayed price (price does not use in the calculaton)
// display_subtotal - the displayed subtotal (price does not use in the calculaton)

// Cart prices definition
// shipping_cost - total shipping cost
// subtotal - sum (price * amount) of all products
// original_subtotal - sum (original_price * amount) of all products
// tax_subtotal - sum of all the tax values
// display_subtotal - the displayed subtotal (does not use in the calculaton)
// subtotal_discount - the order discount
// discount - sum of all products discounts (except subtotal_discount)
// total - order total

function fn_calculate_cart_content(&$cart, $auth, $calculate_shipping = 'A', $calculate_taxes = true, $options_style = 'F', $apply_cart_promotions = true)
{
    $shipping_rates = array();
    $product_groups = array();
    $cart_products = array();
    $cart['subtotal'] = $cart['display_subtotal'] = $cart['original_subtotal'] = $cart['amount'] = $cart['total'] = $cart['discount'] = $cart['tax_subtotal'] = 0;

    $cart['use_discount'] = false;
    $cart['shipping_required'] = false;
    $cart['shipping_failed'] = $cart['company_shipping_failed'] = false;
    $cart['stored_taxes'] = empty($cart['stored_taxes']) ? 'N': $cart['stored_taxes'];
    $cart['display_shipping_cost'] = $cart['shipping_cost'] = 0;
    $cart['coupons'] = empty($cart['coupons']) ? array() : $cart['coupons'];
    $cart['recalculate'] = isset($cart['recalculate']) ? $cart['recalculate'] : false;
    $cart['free_shipping'] = array();
    $cart['options_style'] = $options_style;
    $cart['products'] = !empty($cart['products']) ? $cart['products'] : array();

    fn_add_exclude_products($cart, $auth);

    if (isset($cart['products']) && is_array($cart['products'])) {

        $amount_totals = array();
        if (Registry::get('settings.General.disregard_options_for_discounts') == 'Y') {
            foreach ($cart['products'] as $k => $v) {
                if (!empty($amount_totals[$v['product_id']])) {
                    $amount_totals[$v['product_id']] += $v['amount'];
                } else {
                    $amount_totals[$v['product_id']] = $v['amount'];
                }
            }
        }

        // Collect product data
        foreach ($cart['products'] as $k => $v) {
            $cart['products'][$k]['amount_total'] = isset($amount_totals[$v['product_id']]) ? $amount_totals[$v['product_id']] : $v['amount'];

            $_cproduct = fn_get_cart_product_data($k, $cart['products'][$k], false, $cart, $auth);
            if (empty($_cproduct)) { // FIXME - for deleted products for OM
                fn_delete_cart_product($cart, $k);

                continue;
            }

            $cart_products[$k] = $_cproduct;
        }

        fn_set_hook('calculate_cart_items', $cart, $cart_products, $auth);

        // Apply cart promotions
        if ($apply_cart_promotions == true && $cart['subtotal'] >= 0) {
            if (!empty($cart['stored_subtotal_discount'])) {
                $prev_discount = $cart['subtotal_discount'];
            }
            $cart['applied_promotions'] = fn_promotion_apply('cart', $cart, $auth, $cart_products);
            if (!empty($cart['stored_subtotal_discount'])) {
                $cart['subtotal_discount'] = $prev_discount;
            }
        }
        fn_check_promotion_notices();

        if (Registry::get('settings.Shippings.disable_shipping') == 'Y') {
            $cart['shipping_required'] = false;
        }

        if (!empty($cart['change_cart_products'])) {
            $location = fn_get_customer_location($auth, $cart);
            $product_groups = Shippings::groupProductsList($cart_products, $location);
            if (!empty($cart['product_groups']) && count($product_groups) == count($cart['product_groups'])) {
                foreach ($product_groups as $key_group => $group) {
                    $cart['product_groups'][$key_group]['products'] = $group['products'];
                }
            } else {
                if (!empty($cart['chosen_shipping']) && count($cart['chosen_shipping']) == count($product_groups)) {
                    $cart['calculate_shipping'] = true;
                }
                if (!empty($cart['product_groups']) && count($cart['product_groups']) !== count($product_groups)) {
                    unset($cart['product_groups']);
                }
            }

            unset($cart['change_cart_products']);
            $cart['stored_taxes'] = 'N';
        }

        if (!empty($cart['calculate_shipping']) || empty($cart['product_groups'])) {
            $location = fn_get_customer_location($auth, $cart);
            $product_groups = Shippings::groupProductsList($cart_products, $location);
            $shippings = array();

            if ($cart['shipping_required'] !== false) {
                $cart['shipping_required'] = false;
                foreach ($product_groups as $key_group => $group) {
                    if ($group['shipping_no_required'] === false) {
                        $cart['shipping_required'] = true;
                        break;
                    }
                }
            }

            foreach ($product_groups as $key_group => $group) {
                if ($cart['shipping_required'] === false) {
                    $product_groups[$key_group]['free_shipping'] = true;
                    $product_groups[$key_group]['shipping_no_required'] = true;
                }

                $product_groups[$key_group]['shippings'] = array();
                $shippings_group = Shippings::getShippingsList($group);

                // Adding a shipping method from the created order, if the shipping is not yet in the list.
                if (!empty($cart['chosen_shipping']) && !empty($cart['shipping']) && !empty($cart['order_id'])) {
                    foreach ($cart['shipping'] as $shipping) {
                        if (!isset($shippings_group[$shipping['shipping_id']])) {
                            $shippings_group[$shipping['shipping_id']] = $shipping;
                        }
                    }
                }

                foreach ($shippings_group as $shipping_id => $shipping) {
                    if (!empty($shipping['service_params']['max_weight_of_box'])) {
                        $_group = Shippings::repackProductsByWeight($group, $shipping['service_params']['max_weight_of_box']);
                    } else {
                        $_group = $group;
                    }

                    $_shipping = $shipping;
                    $_shipping['package_info'] = $_group['package_info'];
                    $_shipping['keys'] = array(
                        'group_key' => $key_group,
                        'shipping_id' => $shipping_id,
                    );
                    $shippings[] = $_shipping;

                    $shipping['group_key'] = $key_group;
                    $shipping['rate'] = 0;

                    if (in_array($shipping_id, $cart['free_shipping']) || $group['free_shipping']) {
                        $shipping['free_shipping'] = true;
                    }

                    $product_groups[$key_group]['shippings'][$shipping_id] = $shipping;
                }
            }

            if (!empty($cart['calculate_shipping'])) {
                $rates = Shippings::calculateRates($shippings);

                foreach ($rates as $rate) {
                    $g_key = $rate['keys']['group_key'];
                    $sh_id = $rate['keys']['shipping_id'];

                    if ($rate['price'] !== false) {
                        $rate['price'] += !empty($product_groups[$g_key]['package_info']['shipping_freight']) ? $product_groups[$g_key]['package_info']['shipping_freight'] : 0;
                        $product_groups[$g_key]['shippings'][$sh_id]['rate'] = empty($product_groups[$g_key]['shippings'][$sh_id]['free_shipping']) ? $rate['price'] : 0;
                    } else {
                        unset($product_groups[$g_key]['shippings'][$sh_id]);
                    }
                }
            }
            $cart['product_groups'] = $product_groups;
        }

        $product_groups = &$cart['product_groups'];

        // FIXME
        $cart['shipping_cost'] = 0;
        $cart['shipping'] = array();
        $cart['chosen_shipping'] = !empty($cart['chosen_shipping']) ? $cart['chosen_shipping'] : array();

        $count_shipping_failed = 0;
        foreach ($product_groups as $key_group => $group) {
            if (!empty($cart['calculate_shipping']) && (!isset($cart['chosen_shipping'][$key_group]) || empty($group['shippings'][$cart['chosen_shipping'][$key_group]])) && !$group['free_shipping']) {
                $cart['chosen_shipping'][$key_group] = key($group['shippings']);
            }

            if ($group['shipping_no_required']) {
                unset($cart['chosen_shipping'][$key_group]);
            }

            if (!isset($cart['chosen_shipping'][$key_group]) && !$group['free_shipping'] && !$group['shipping_no_required']) {
                $count_shipping_failed++;
                $cart['company_shipping_failed'] = true;
            }

            foreach ($group['shippings'] as $shipping_id => $shipping) {
                if (isset($cart['chosen_shipping'][$key_group]) && $cart['chosen_shipping'][$key_group] == $shipping_id) {
                    $cart['shipping_cost'] += $shipping['rate'];
                }
            }

            if (!empty($group['shippings']) && isset($cart['chosen_shipping'][$key_group])) {
                $shipping = $group['shippings'][$cart['chosen_shipping'][$key_group]];
                $shipping_id = $shipping['shipping_id'];
                if (empty($cart['shipping'][$shipping_id])) {
                    $cart['shipping'][$shipping_id] = $shipping;
                    $cart['shipping'][$shipping_id]['rates'] = array();
                }
                $cart['shipping'][$shipping_id]['rates'][$key_group] = $shipping['rate'];
            }
        }
        $cart['display_shipping_cost'] = $cart['shipping_cost'];

        if (!empty($product_groups) && count($product_groups) == $count_shipping_failed) {
            $cart['shipping_failed'] = true;
        }

        foreach ($cart['chosen_shipping'] as $key_group => $shipping_id) {
            if (!empty($product_groups[$key_group]) && !empty($product_groups[$key_group]['shippings'][$shipping_id])) {
                $shipping = $product_groups[$key_group]['shippings'][$shipping_id];
                $shipping['group_name'] = $product_groups[$key_group]['name'];
                $product_groups[$key_group]['chosen_shippings'] = array($shipping);
            } else {
                unset($cart['chosen_shipping'][$key_group]);
            }
        }

        fn_apply_stored_shipping_rates($cart);

        fn_set_hook('calculate_cart_taxes_pre', $cart, $cart_products, $product_groups, $calculate_taxes, $auth);

        $calculated_taxes_summary = array();

        foreach ($product_groups as $key_group => &$group) {
            foreach ($group['products'] as $cart_id => $product) {
                if (!empty($cart_products[$cart_id])) {
                    $group['products'][$cart_id] = $cart_products[$cart_id];
                }
            }

            // Calculate taxes
            if ($calculate_taxes == true && $auth['tax_exempt'] != 'Y') {
                $calculated_taxes = fn_calculate_taxes($cart, $key_group, $group['products'], $group['shippings'], $auth);

                if (empty($calculated_taxes_summary)) {
                    $calculated_taxes_summary = array();
                }
                foreach ($calculated_taxes as $tax_id => $tax) {
                    if (empty($calculated_taxes_summary[$tax_id])) {
                        $calculated_taxes_summary[$tax_id] = $calculated_taxes[$tax_id];
                    } else {
                        $calculated_taxes_summary[$tax_id]['tax_subtotal'] += $calculated_taxes[$tax_id]['applies']['S'];
                        $calculated_taxes_summary[$tax_id]['applies']['S'] += $calculated_taxes[$tax_id]['applies']['S'];
                        $calculated_taxes_summary[$tax_id]['tax_subtotal'] += $calculated_taxes[$tax_id]['applies']['P'];
                        $calculated_taxes_summary[$tax_id]['applies']['P'] += $calculated_taxes[$tax_id]['applies']['P'];
                    }
                }
            } elseif ($cart['stored_taxes'] != 'Y') {
                $cart['taxes'] = $cart['tax_summary'] = array();
            }

            unset($group);
        }

        fn_apply_calculated_taxes($calculated_taxes_summary, $cart);
        // /FIXME

        fn_set_hook('calculate_cart_taxes_post', $cart, $cart_products, $shipping_rates, $calculate_taxes, $auth);

        $cart['subtotal'] = $cart['display_subtotal'] = 0;

        fn_update_cart_data($cart, $cart_products);

        foreach ($cart['products'] as $product_code => $product) {
            foreach ($product_groups as $key_group => $group) {
                if (in_array($product_code, array_keys($group['products']))) {
                    $product_groups[$key_group]['products'][$product_code] = $product;
                }
            }
        }

        // Calculate totals
        foreach ($product_groups as $key_group => $group) {
            foreach ($group['products'] as $product_code => $product) {
                $_tax = (!empty($product['tax_summary']) ? ($product['tax_summary']['added'] / $product['amount']) : 0);
                $cart_products[$product_code]['display_price'] = $cart_products[$product_code]['price'] + (Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y' ? $_tax : 0);
                $cart_products[$product_code]['subtotal'] = $cart_products[$product_code]['price'] * $product['amount'];

                $cart_products[$product_code]['display_subtotal'] = $cart_products[$product_code]['display_price'] * $product['amount'];

                if (!empty($product['tax_summary'])) {
                    $cart_products[$product_code]['tax_summary'] = $product['tax_summary'];
                }

                $cart['subtotal'] += $cart_products[$product_code]['subtotal'];
                $cart['display_subtotal'] += $cart_products[$product_code]['display_subtotal'];
                $cart['products'][$product_code]['display_price'] = $cart_products[$product_code]['display_price'];
                $product_groups[$key_group]['products'][$product_code]['display_price'] = $cart_products[$product_code]['display_price'];

                $cart['tax_subtotal'] += (!empty($product['tax_summary']) ? ($product['tax_summary']['added']) : 0);
                $cart['total'] += ($cart_products[$product_code]['price'] - 0) * $product['amount'];

                if (!empty($product['discount'])) {
                    $cart['discount'] += $product['discount'] * $product['amount'];
                }
            }
        }

        if (Registry::get('settings.General.tax_calculation') == 'subtotal') {
            $cart['tax_subtotal'] += (!empty($cart['tax_summary']['added']) ? ($cart['tax_summary']['added']) : 0);
        }

        $cart['subtotal'] = fn_format_price($cart['subtotal']);
        $cart['display_subtotal'] = fn_format_price($cart['display_subtotal']);

        $cart['total'] += $cart['tax_subtotal'];

        $cart['total'] = fn_format_price($cart['total'] + $cart['shipping_cost']);

        if (!empty($cart['subtotal_discount'])) {
            $cart['total'] -= ($cart['subtotal_discount'] < $cart['total']) ? $cart['subtotal_discount'] : $cart['total'];
        }
    }

    /**
     * Processes cart data after calculating all prices and other data (taxes, shippings etc)
     *
     * @param array  $cart               Cart data
     * @param array  $cart_products      Cart products
     * @param array  $auth               Auth data
     * @param string $calculate_shipping // 1-letter flag
     *      A - calculate all available methods
     *      E - calculate selected methods only (from cart[shipping])
     *      S - skip calculation
     * @param bool $calculate_taxes       Flag determines if taxes should be calculated
     * @param bool $apply_cart_promotions Flag determines if promotions should be applied to the cart
     */
    fn_set_hook('calculate_cart', $cart, $cart_products, $auth, $calculate_shipping, $calculate_taxes, $apply_cart_promotions);

    if (!empty($cart['calculate_shipping']) || empty($cart['product_groups'])) {
        $cart['product_groups'] = $product_groups;
    }
    $cart['recalculate'] = false;
    $cart['calculate_shipping'] = false;

    /**
     * Processes cart data after calculating all prices and other data (taxes, shippings etc) including products group
     *
     * @param array  $cart               Cart data
     * @param array  $auth               Auth data
     * @param string $calculate_shipping // 1-letter flag
     *      A - calculate all available methods
     *      E - calculate selected methods only (from cart[shipping])
     *      S - skip calculation
     * @param bool   $calculate_taxes Flag determines if taxes should be calculated
     * @param string $options_style   1-letter flag
     *      "F" - Full option information (with exceptions)
     *      "I" - Short info
     *      "" - "Source" info. Only ids array (option_id => variant_id)
     * @param bool  $apply_cart_promotions Flag determines if promotions should be applied to the cart
     * @param array $cart_products         Cart products
     * @param array $product_groups        Products grouped by packages, suppliers, vendors
     */
    fn_set_hook('calculate_cart_post', $cart, $auth, $calculate_shipping, $calculate_taxes, $options_style, $apply_cart_promotions, $cart_products, $product_groups);

    return array(
        $cart_products,
        $product_groups
    );
}

function fn_cart_is_empty($cart)
{
    $result = true;

    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $v) {
            if (!isset($v['extra']['exclude_from_calculate']) && empty($v['extra']['parent'])) {
                $result = false;
                break;
            }
        }
    }

    fn_set_hook('is_cart_empty', $cart, $result);

    return $result;
}

/**
 * Calculate total weight of products in cart
 *
 * @param array $cart cart information
 * @param array $cart_products cart products
 * @param char $type S - weight for shipping, A - all, C - all, exception excluded from calculation
 * @return int products weight
 */
function fn_get_products_weight($cart, $cart_products, $type = 'S')
{
    $weight = 0;

    if (is_array($cart_products)) {
        foreach ($cart_products as $k => $v) {
            if ($type == 'S') {
                if (($v['is_edp'] == 'Y' && $v['edp_shipping'] != 'Y') || $v['free_shipping'] == 'Y' || fn_exclude_from_shipping_calculate($cart['products'][$k])) {
                    continue;
                }
            } elseif ($type == 'C') {
                if (isset($v['exclude_from_calculate'])) {
                    continue;
                }
            }

            if (isset($v['weight'])) {
                $weight += ($v['weight'] * $v['amount']);
            }
        }
    }

    return !empty($weight) ? sprintf("%.2f", $weight) : '0.01';
}

/**
 * Calculate total quantity of products in cart
 *
 * @param array $cart cart information
 * @param array $cart_products cart products
 * @param char $type S - quantity for shipping, A - all, C - all, exception excluded from calculation
 * @return int products quantity
 */
function fn_get_products_amount($cart, $cart_products, $type = 'S')
{
    $amount = 0;

    foreach ($cart_products as $k => $v) {
        if ($type == 'S') {
            if (($v['is_edp'] == 'Y' && $v['edp_shipping'] != 'Y') || $v['free_shipping'] == 'Y' || fn_exclude_from_shipping_calculate($cart['products'][$k])) {
                continue;
            }
        } elseif ($type == 'C') {
            if (isset($v['exclude_from_calculate'])) {
                continue;
            }
        }

        $amount += $v['amount'];
    }

    return $amount;
}

// Get Payment processor data
function fn_get_processor_data($payment_id)
{
    $pdata = db_get_row("SELECT processor_id, processor_params FROM ?:payments WHERE payment_id = ?i", $payment_id);
    if (empty($pdata)) {
        return false;
    }

    $processor_data = db_get_row("SELECT * FROM ?:payment_processors WHERE processor_id = ?i", $pdata['processor_id']);
    $processor_data['processor_params'] = unserialize($pdata['processor_params']);

    $processor_data['currencies'] = (!empty($processor_data['currencies'])) ? explode(',', $processor_data['currencies']) : array();

    return $processor_data;
}

/**
 * Get processor data by processor script
 *
 * @param string $processor_script name of processor script
 * @return (array) processor data
 */
function fn_get_processor_data_by_name($processor_script)
{
    $processor_data = db_get_row("SELECT * FROM ?:payment_processors WHERE processor_script = ?s", $processor_script);

    return $processor_data;
}

/**
 * Get payment method by processor_id
 *
 * @param string $processor_id
 * @param string $lang_code
 * @return (array) payment methods which use this processor
 */
function fn_get_payment_by_processor($processor_id, $lang_code = CART_LANGUAGE)
{
    $payment_methods = db_get_hash_array("SELECT ?:payments.payment_id, ?:payments.a_surcharge, ?:payments.p_surcharge, ?:payments.payment_category, ?:payment_descriptions.*, ?:payment_processors.type AS processor_type, ?:payments.status FROM ?:payments LEFT JOIN ?:payment_descriptions ON ?:payments.payment_id = ?:payment_descriptions.payment_id AND ?:payment_descriptions.lang_code = ?s LEFT JOIN ?:payment_processors ON ?:payment_processors.processor_id = ?:payments.processor_id WHERE ?:payments.processor_id = ?i ORDER BY ?:payments.position", 'payment_id', $lang_code, $processor_id);

    return $payment_methods;
}

//
// Returns customer location or default location
//
function fn_get_customer_location($auth, $cart, $billing = false)
{
    $s_info = array();
    $prefix = 's_';
    if ($billing == true) {
        $prefix = 'b_';
    }

    $user_data = isset($cart['user_data']) ? $cart['user_data'] : array();

    foreach ($user_data as $key => $value) {
        if (empty($value)) {
            unset($user_data[$key]);
        }
    }

    $u_info = (!empty($user_data)) ? $user_data : ((empty($user_data) && !empty($auth['user_id'])) ? fn_get_user_info($auth['user_id'], true, $cart['profile_id']) : array());

    if (empty($u_info)) {
        foreach (Registry::get('settings.General') as $f_name => $f_value) {
            if (strpos($f_name, 'default_') === 0) {
                $f_name = substr($f_name, 8);
                $u_info[$prefix . $f_name] = $f_value;
            }
        }
    }

    $s_info = array();
    foreach ($u_info as $field_name => $field_value) {
        if (strpos($field_name, $prefix) === 0) {
            $f_name = substr($field_name, 2);
            $s_info[$f_name] = !empty($field_value) ? $field_value : Registry::get('settings.General.default_' . $f_name);;
        }
    }

    if (empty($s_info)) {
        return array();
    }

    // Add residential address flag
    $s_info['address_type'] = (!empty($u_info['s_address_type'])) ? $u_info['s_address_type'] : 'residential';

    // Get First and Last names
    $u_info['firstname'] = !empty($u_info['firstname']) ? $u_info['firstname'] : 'John';
    $u_info['lastname'] = !empty($u_info['lastname']) ? $u_info['lastname'] : 'Doe';

    if ($prefix == 'b') {
        $s_info['firstname'] = (!empty($u_info['b_firstname'])) ? $u_info['b_firstname'] : $u_info['firstname'];
        $s_info['lastname'] = (!empty($u_info['b_lastname'])) ? $u_info['b_lastname'] : $u_info['lastname'];
    } else {
        $s_info['firstname'] = (!empty($u_info['s_firstname'])) ? $u_info['s_firstname'] : (!empty($u_info['b_firstname']) ? $u_info['b_firstname'] : $u_info['firstname']);
        $s_info['lastname'] = (!empty($u_info['s_lastname'])) ? $u_info['s_lastname'] : (!empty($u_info['b_lastname']) ? $u_info['b_lastname'] : $u_info['lastname']);
    }

    // Get country/state descriptions
    $avail_country = db_get_field("SELECT COUNT(*) FROM ?:countries WHERE code = ?s AND status = 'A'", $s_info['country']);
    if (empty($avail_country)) {
        return array();
    }

    if (!empty($s_info['country']) && !empty($s_info['state'])) {
        $avail_state = db_get_field("SELECT COUNT(*) FROM ?:states WHERE country_code = ?s AND code = ?s AND status = 'A'", $s_info['country'], $s_info['state']);
        if (empty($avail_state)) {
            $s_info['state'] = '';
        }
    }

    return $s_info;
}

/**
 * Calculate products and shipping taxes
 *
 * @param array $cart Cart data
 * @param array $group_key Group number
 * @param array $group_products Products data
 * @param array $shipping_rates
 * @param array $auth Auth data
 * @return boolean Always false
 */
function fn_calculate_taxes(&$cart, $group_key, &$group_products, &$shipping_rates, $auth)
{
    /**
     * Prepare params before applying products and shipping taxes to cart
     *
     * @param array $cart           Cart data
     * @param array $group_products Products data
     * @param array $shipping_rates
     * @param array $auth           Auth data
     */
    fn_set_hook('calculate_taxes_pre', $cart, $group_products, $shipping_rates, $auth);

    $calculated_data = array();

    if (Registry::get('settings.General.tax_calculation') == 'unit_price') {
        // Tax calculation method based on UNIT PRICE

        // Calculate product taxes
        foreach ($group_products as $k => $product) {
            $taxes = fn_get_product_taxes($k, $cart, $group_products);

            if (empty($taxes)) {
                continue;
            }

            if (isset($product['subtotal'])) {
                if ($product['price'] == $product['subtotal'] && $product['amount'] != 1) {
                    $price = fn_format_price($product['price']);
                } else {
                    $price = fn_format_price($product['subtotal'] / $product['amount']);
                }

                $calculated_data['P_' . $k] = fn_calculate_tax_rates($taxes, $price, $product['amount'], $auth, $cart);

                $group_products[$k]['tax_summary'] = array('included' => 0, 'added' => 0, 'total' => 0); // tax summary for 1 unit of product
                $cart['products'][$k]['tax_summary'] = array('included' => 0, 'added' => 0, 'total' => 0); // tax summary for 1 unit of product

                // Apply taxes to product subtotal
                if (!empty($calculated_data['P_' . $k])) {
                    foreach ($calculated_data['P_' . $k] as $_k => $v) {
                        $group_products[$k]['taxes'][$_k] = $v;
                        $cart['products'][$k]['taxes'][$_k] = $v;
                        if ($taxes[$_k]['price_includes_tax'] != 'Y') {
                            $group_products[$k]['tax_summary']['added'] += $v['tax_subtotal'];
                            $cart['products'][$k]['tax_summary']['added'] += $v['tax_subtotal'];
                        } else {
                            $group_products[$k]['tax_summary']['included'] += $v['tax_subtotal'];
                            $cart['products'][$k]['tax_summary']['included'] += $v['tax_subtotal'];
                        }
                    }
                    $group_products[$k]['tax_summary']['total'] = $group_products[$k]['tax_summary']['added'] + $group_products[$k]['tax_summary']['included'];
                    $cart['products'][$k]['tax_summary']['total'] = $cart['products'][$k]['tax_summary']['added'] + $cart['products'][$k]['tax_summary']['included'];
                }
            }
        }

        // Calculate shipping taxes
        if (!empty($shipping_rates)) {
            foreach ($shipping_rates as $shipping_id => $shipping) {
                $taxes = fn_get_shipping_taxes($shipping_id, $shipping_rates, $cart);

                if (!empty($taxes)) {

                    $shipping_rates[$shipping_id]['taxes'] = array();

                    $calculate_rate = true;

                    if (!empty($cart['chosen_shipping'][$group_key]) && $cart['chosen_shipping'][$group_key] == $shipping_id) {

                        $calculated_data['S_' . $group_key . '_' . $shipping_id] = fn_calculate_tax_rates($taxes, $shipping['rate'], 1, $auth, $cart);

                        if (!empty($calculated_data['S_' . $group_key . '_' . $shipping_id])) {
                            foreach ($calculated_data['S_' . $group_key . '_' . $shipping_id] as $__k => $__v) {
                                if ($taxes[$__k]['price_includes_tax'] != 'Y') {
                                    $cart['display_shipping_cost'] += Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y' ? $__v['tax_subtotal'] : 0;
                                    $cart['tax_subtotal'] += $__v['tax_subtotal'];
                                }

                                if ($cart['stored_taxes'] == 'Y') {
                                    $cart['taxes'][$__k]['applies']['S_' . $group_key . '_' . $shipping_id] = $__v['tax_subtotal'];
                                }
                            }

                            $shipping_rates[$shipping_id]['taxes']['S_' . $group_key . '_' . $shipping_id] = $calculated_data['S_' . $group_key . '_' . $shipping_id];
                            $calculate_rate = false;
                        }
                    }

                    if ($calculate_rate) {
                        $cur_shipping_rates = fn_calculate_tax_rates($taxes, $shipping['rate'], 1, $auth, $cart);
                        if (!empty($cur_shipping_rates)) {
                            $shipping_rates[$shipping_id]['taxes'] = $cur_shipping_rates;
                        }
                    }
                }
            }

            foreach ($shipping_rates as $shipping_id => $shipping) {
                // Calculate taxes for each shipping rate
                $taxes = fn_get_shipping_taxes($shipping_id, $shipping_rates, $cart);

                $shipping_rates[$shipping_id]['taxed_price'] = 0;
                unset($shipping_rates[$shipping_id]['inc_tax']);

                if (!empty($taxes)) {
                    $shipping_rates[$shipping_id]['taxes'] = array();

                    $tax = fn_calculate_tax_rates($taxes, fn_format_price($shipping['rate']), 1, $auth, $cart);

                    $shipping_rates[$shipping_id]['taxes'] = $tax;

                    if (!empty($tax) && Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y') {
                        foreach ($tax as $_id => $_tax) {
                            if ($_tax['price_includes_tax'] != 'Y') {
                                $shipping_rates[$shipping_id]['taxed_price'] += $_tax['tax_subtotal'];
                            }
                        }
                        $shipping_rates[$shipping_id]['inc_tax'] = true;
                    }

                    if (!empty($shipping_rates[$shipping_id]['rate']) && $shipping_rates[$shipping_id]['taxed_price'] > 0) {
                        $shipping_rates[$shipping_id]['taxed_price'] += $shipping_rates[$shipping_id]['rate'];
                    }
                }
            }
        }

    } else {
        // Tax calculation method based on SUBTOTAL

        // Calculate discounted subtotal
        if (!isset($cart['subtotal_discount'])) {
            $cart['subtotal_discount'] = 0;
        }
        $discounted_subtotal = $cart['subtotal'] - $cart['subtotal_discount'];

        if ($discounted_subtotal < 0) {
            $discounted_subtotal = 0;
        }

        // Get discount distribution coefficient (DDC) between taxes
        if ($cart['subtotal'] > 0) {
            $ddc = $discounted_subtotal / $cart['subtotal'];
        } else {
            $ddc = 1;
        }

        //
        // Group subtotal by taxes
        //
        $subtotal = array();

        // Get products taxes
        foreach ($group_products as $cart_id => $product) {
            $taxes = fn_get_product_taxes($cart_id, $cart, $group_products);

            if (!empty($taxes)) {
                foreach ($taxes as $tax_id => $tax) {
                    if (empty($subtotal[$tax_id])) {
                        $subtotal[$tax_id] = fn_init_tax_subtotals($tax);
                    }

                    $_subtotal = ($product['price'] == $product['subtotal'] && $product['amount'] != 1) ? fn_format_price($product['price'] * $product['amount']) : $product['subtotal'];

                    $subtotal[$tax_id]['subtotal'] += $_subtotal;
                    $subtotal[$tax_id]['applies']['P'] += $_subtotal;
                    $subtotal[$tax_id]['applies']['items']['P'][$cart_id] = true;

                    if (isset($product['company_id'])) {
                        if (!isset($subtotal[$tax_id]['group'][$group_key])) {
                            $subtotal[$tax_id]['group'][$group_key]['products'] = 0;
                        }
                        $subtotal[$tax_id]['group'][$group_key]['products'] += $_subtotal;
                        $priority_stack['products'][$group_key] = -1;
                        $applied_taxes['products'][$group_key] = 0;
                    }
                }
            }
        }

        // Get shipping taxes
        if (!empty($shipping_rates)) {
            foreach ($shipping_rates as $shipping_id => $shipping) {
                // Calculate taxes for each shipping rate
                $taxes = fn_get_shipping_taxes($shipping_id, $shipping_rates, $cart);

                $shipping_rates[$shipping_id]['taxed_price'] = 0;
                unset($shipping_rates[$shipping_id]['inc_tax']);

                // Display shipping with taxes at cart/checkout page
                if (!empty($taxes)) {
                    $shipping_rates[$shipping_id]['taxes'] = array();

                    $tax = fn_calculate_tax_rates($taxes, fn_format_price($shipping['rate']), 1, $auth, $cart);
                    $shipping_rates[$shipping_id]['taxes'] = $tax;

                    if (!empty($tax) && Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y') {
                        foreach ($tax as $_id => $_tax) {
                            if ($_tax['price_includes_tax'] != 'Y') {
                                $shipping_rates[$shipping_id]['taxed_price'] += $_tax['tax_subtotal'];
                            }
                        }
                        $shipping_rates[$shipping_id]['inc_tax'] = true;
                    }

                    if (!empty($shipping_rates[$shipping_id]['rate']) && $shipping_rates[$shipping_id]['taxed_price'] > 0) {
                        $shipping_rates[$shipping_id]['taxed_price'] += $shipping_rates[$shipping_id]['rate'];
                    }
                }

                if (!isset($cart['chosen_shipping'][$group_key]) || $cart['chosen_shipping'][$group_key] != $shipping_id) {
                    continue;
                }

                // Add shipping taxes to "tax" array
                if (!empty($taxes)) {
                    foreach ($taxes as $tax_id => $tax) {
                        if (empty($subtotal[$tax_id])) {
                            $subtotal[$tax_id] = fn_init_tax_subtotals($tax);
                        }

                        $subtotal[$tax_id]['subtotal'] += $shipping['rate'];
                        $subtotal[$tax_id]['applies']['S'] += $shipping['rate'];
                        $subtotal[$tax_id]['applies']['items']['S'][$group_key][$shipping_id] = true;

/*                        if (!isset($subtotal[$tax_id]['groups'][$group_key]['shippings'])) {
                            $subtotal[$tax_id]['groups'][$group_key]['shippings'] = 0;
                        }

                        $subtotal[$tax_id]['groups'][$group_key]['shippings'] += $shipping['rate'];*/
                        $priority_stack['shippings'][$group_key] = -1;
                        $applied_taxes['shippings'][$group_key] = 0;
                    }
                }
            }
        }

        if (!empty($subtotal)) {
            $subtotal = fn_sort_array_by_key($subtotal, 'priority');
        }

        // Apply DDC and calculate tax rates
        $calculated_taxes = array();

        if (empty($priority_stack)) {
            $priority_stack['products'][0] = -1;
            $priority_stack['shippings'][0] = -1;
            $applied_taxes['products'][0] = 0;
            $applied_taxes['shippings'][0] = 0;
        }

        foreach ($subtotal as $tax_id => $_st) {
            if (empty($_st['tax_id'])) {
                $_st['tax_id'] = $tax_id;
            }

            $product_tax = fn_calculate_tax_rates(array($_st), fn_format_price($_st['applies']['P'] * $ddc), 1, $auth, $cart);
            $shipping_tax = fn_calculate_tax_rates(array($_st), fn_format_price($_st['applies']['S']), 1, $auth, $cart);

            if (empty($product_tax) && empty($shipping_tax)) {
                continue;
            }

            if (empty($_st['groups'])) {
                $_st['groups'][0]['products'] = $_st['applies']['P'];
                $_st['groups'][0]['shippings'] = $_st['applies']['S'];
            }

            foreach ($_st['groups'] as $group_key => $applies) {
                $apply_tax_stack = array(
                    'products' => 0,
                    'shippings' => 0,
                );

                if (!isset($priority_stack['products'][$group_key])) {
                    $priority_stack['products'][$group_key] = -1;
                }
                if (!isset($priority_stack['shippings'][$group_key])) {
                    $priority_stack['shippings'][$group_key] = -1;
                }

                if ($priority_stack['products'][$group_key] < 0 && !empty($applies['products'])) {
                    $priority_stack['products'][$group_key] = $_st['priority'];

                } elseif (!empty($applies['products']) && $priority_stack['products'][$group_key] != $_st['priority']) {
                    $apply_tax_stack['products'] = $applied_taxes['products'][$group_key];
                    $priority_stack['products'][$group_key] = $_st['priority'];
                }

                if ($priority_stack['shippings'][$group_key] < 0 && !empty($applies['shippings'])) {
                    $priority_stack['shippings'][$group_key] = $_st['priority'];

                } elseif (!empty($applies['shippings']) && $priority_stack['shippings'][$group_key] != $_st['priority']) {
                    $apply_tax_stack['shippings'] = $applied_taxes['shippings'][$group_key];
                    $priority_stack['shippings'][$group_key] = $_st['priority'];
                }

                if (empty($calculated_data[$tax_id])) {
                    $calculated_data[$tax_id] = empty($product_tax) ? reset($shipping_tax) : reset($product_tax);
                }

                if (!empty($applies['products'])) {
                    $products_tax = fn_calculate_tax_rates(array($_st), fn_format_price($applies['products'] * $ddc + $apply_tax_stack['products']), 1, $auth, $cart);
                } else {
                    $products_tax[$tax_id]['tax_subtotal'] = 0;
                }

                if (!empty($applies['shippings'])) {
                    $shippings_tax = fn_calculate_tax_rates(array($_st), fn_format_price($applies['shippings'] + $apply_tax_stack['shippings']), 1, $auth, $cart);
                } else {
                    $shippings_tax[$tax_id]['tax_subtotal'] = 0;
                }

                if (!isset($applied_taxes['products'][$group_key])) {
                    $applied_taxes['products'][$group_key] = 0;
                }
                if (!isset($applied_taxes['shippings'][$group_key])) {
                    $applied_taxes['shippings'][$group_key] = 0;
                }

                if ($_st['price_includes_tax'] != 'Y') {
                    $applied_taxes['products'][$group_key] += $products_tax[$tax_id]['tax_subtotal'];
                    $applied_taxes['shippings'][$group_key] += $shippings_tax[$tax_id]['tax_subtotal'];
                }

                if (!isset($calculated_data[$tax_id]['applies']['P'])) {
                    $calculated_data[$tax_id]['applies']['P'] = 0;
                }
                if (!isset($calculated_data[$tax_id]['applies']['S'])) {
                    $calculated_data[$tax_id]['applies']['S'] = 0;
                }
                $calculated_data[$tax_id]['applies']['P'] += $products_tax[$tax_id]['tax_subtotal'];
                $calculated_data[$tax_id]['applies']['S'] += $shippings_tax[$tax_id]['tax_subtotal'];
                $calculated_data[$tax_id]['applies']['items'] = $_st['applies']['items'];
                $calculated_data[$tax_id]['tax_subtotal'] = $calculated_data[$tax_id]['applies']['P'] + $calculated_data[$tax_id]['applies']['S'];
            }
        }
    }

    /**
     * Processes products data after applying products and shipping taxes to cart)
     *
     * @param array $cart            Cart data
     * @param array $group_products  Products data
     * @param array $shipping_rates  Shipping rates data
     * @param array $auth            Auth data
     * @param array $calculated_data Payment taxes data
     */
    fn_set_hook('calculate_taxes_post', $cart, $group_products, $shipping_rates, $auth, $calculated_data);

    return $calculated_data;
}

/**
 * Calculate payment surcharge taxes, calculated separately from products and shipping taxes
 * becuase payment surcharge is calculated based on cart totals.
 *
 * @param array $cart Cart data
 * @param array $auth Auth data
 * @return boolean always false
 */
function fn_calculate_payment_taxes(&$cart, $auth)
{
    /**
     * Prepare params before applying payment taxes to cart
     *
     * @param array $cart Cart data
     * @param array $auth Auth data
     */
    fn_set_hook('calculate_payment_taxes_pre', $cart, $auth);

    if ($auth['tax_exempt'] == 'Y') {
        return false;
    }

    if (fn_allowed_for('MULTIVENDOR')) {
        if (Registry::get('settings.Vendors.include_payment_surcharge') == 'Y' && fn_take_payment_surcharge_from_vendor($cart['products'])) {
            return false;
        }
    }
    $calculated_data = array();

    if (Registry::get('settings.General.tax_calculation') == 'unit_price') {
        // Tax calculation method based on UNIT PRICE

        if (!empty($cart['payment_id']) && !empty($cart['payment_surcharge'])) {
            $payment_id = $cart['payment_id'];
            $taxes = fn_get_payment_taxes($payment_id, $cart);

            if (!empty($taxes)) {
                $calculated_data['PS_' . $payment_id] = fn_calculate_tax_rates($taxes, fn_format_price($cart['payment_surcharge']), 1, $auth, $cart);

                if (!empty($calculated_data['PS_' . $payment_id])) {
                    foreach ($calculated_data['PS_' . $payment_id] as $__k => $__v) {
                        if ($taxes[$__k]['price_includes_tax'] != 'Y') {
                            if (Registry::get('settings.Appearance.cart_prices_w_taxes') == 'Y') {
                                $cart['payment_surcharge'] += $__v['tax_subtotal'];
                            }
                        }
                    }
                    $calculate_rate = false;
                }
            }
        }

    } else {
        if (!empty($cart['payment_id']) && !empty($cart['payment_surcharge'])) {
            $taxes = fn_get_payment_taxes($cart['payment_id'], $cart);
            $priority = 0;
            $calc_surcharge = $cart['payment_surcharge'];
            $taxed_surcharge =  $cart['payment_surcharge'];
            if (!empty($taxes)) {
                foreach ($taxes as $tax_id => $tax) {
                    if ($tax['priority'] > $priority) {
                        $calc_surcharge = $taxed_surcharge;
                    }

                    $calculated_tax = fn_calculate_tax_rates(array($tax), fn_format_price($calc_surcharge), 1, $auth, $cart);
                    if (empty($calculated_tax[$tax_id])) {
                        continue;
                    }
                    $calculated_data[$tax_id] = fn_init_tax_subtotals($calculated_tax[$tax_id]);

                    $calculated_data[$tax_id]['tax_subtotal'] = $calculated_tax[$tax_id]['tax_subtotal'];
                    $calculated_data[$tax_id]['applies']['PS'] = $calculated_tax[$tax_id]['tax_subtotal'];
                    $calculated_data[$tax_id]['applies']['items']['PS'][$cart['payment_id']] = true;
                    $taxed_surcharge += $calculated_tax[$tax_id]['tax_subtotal'];
                }
            }
        }

    }

    /**
     * Processes payment data taxes after applying payment taxes to cart
     *
     * @param array $cart            Cart data
     * @param array $auth            Auth data
     * @param array $calculated_data Payment data taxes
     */
    fn_set_hook('calculate_payment_taxes_post', $cart, $auth, $calculated_data);

    fn_apply_payment_taxes($calculated_data, $cart);

    return false;
}

/**
 * Apply payment surcharge taxes to cart, payment surcharge taxes calculated and applied
 * separately from products and shipping taxes
 * cart taxes are supposed to keep shippings and products taxes
 *
 * @param array $calculated_data payment data taxes
 * @param array $cart cart data
 * @return boolean always true
 */
function fn_apply_payment_taxes($calculated_data, &$cart)
{
    $tax_added = 0;

    if (empty($cart['taxes'])) {
        $cart['taxes'] = array();
        $cart['tax_subtotal'] = 0;
    }
    if (!empty($calculated_data)) {
        if (Registry::get('settings.General.tax_calculation') == 'unit_price') {
            // Based on the unit price
            foreach ($calculated_data as $id => $_taxes) {
                if (empty($_taxes)) {
                    continue;
                }
                foreach ($_taxes as $k => $v) {
                    if (empty($cart['taxes'][$k])) {
                        $cart['taxes'][$k] = $v;
                        $cart['taxes'][$k]['tax_subtotal'] = 0;
                    }
                    $cart['taxes'][$k]['applies'][$id] = $v['tax_subtotal'];
                    $cart['taxes'][$k]['tax_subtotal'] += $v['tax_subtotal'];

                    if ($v['price_includes_tax'] == 'N') {
                        if (Registry::get('settings.Appearance.cart_prices_w_taxes') != 'Y') {
                            $tax_added += $v['tax_subtotal'];
                        }
                        $cart['tax_subtotal'] += $v['tax_subtotal'];
                    }
                }
            }
        } else {
            if (empty($cart['tax_summary'])) {
                // Based on the order subtotal
                $cart['tax_summary'] = array(
                    'included' => 0,
                    'added' => 0,
                    'total' => 0
                );
            }

            foreach ($calculated_data as $tax_id => $v) {
                if (!empty($cart['taxes'][$tax_id])) {
                    $cart['taxes'][$tax_id]['applies']['PS'] =  $v['applies']['PS'];
                    $cart['taxes'][$tax_id]['applies']['items']['PS'] =  $v['applies']['items']['PS'];
                    $cart['taxes'][$tax_id]['tax_subtotal'] += $v['tax_subtotal'];
                } else {
                    $cart['taxes'][$tax_id] = $v;
                }

                if ($v['price_includes_tax'] == 'Y') {
                    $cart['tax_summary']['included'] += $v['tax_subtotal'];
                } else {
                    $cart['tax_summary']['added'] += $v['tax_subtotal'];
                    $tax_added += $v['tax_subtotal'];
                }

                $cart['tax_summary']['total'] += $v['tax_subtotal'];
            }
        }
    }
    if (!empty($tax_added)) {
        $cart['total'] = fn_format_price($cart['total'] + $tax_added);
    }

    /**
     * Apply payment taxes (running after fn_apply_payment_taxes function)
     *
     * @param array $calculated_data payment data taxes
     * @param array $cart            cart data
     */
    fn_set_hook('apply_payment_taxes_post', $calculated_data, $cart);

    return true;
}

/**
 * Init taxes array: add additional params to tax array for calculation
 *
 * @param array $tax base tax array
 * @return array array with inited params
 */
function fn_init_tax_subtotals($tax)
{
    $tax['subtotal'] = $tax['applies']['P'] = $tax['applies']['S'] = 0;
    $tax['applies']['items']['P'] = $tax['applies']['items']['S'] = array();

    /**
     * Init tax subtotals (running after fn_init_tax_subtotals function)
     *
     * @param array $tax tax array
     */
    fn_set_hook('init_tax_subtotals_post', $tax);

    return $tax;
}

function fn_get_product_taxes($idx, $cart, $cart_products)
{
    if ($cart['stored_taxes'] == 'Y') {
        $_idx = '';
        if (isset($cart['products'][$idx]['original_product_data']['cart_id'])) {
            $_idx = $cart['products'][$idx]['original_product_data']['cart_id'];
        }

        $taxes = array();
        foreach ((array) $cart['taxes'] as $_k => $_v) {
            $tax = array();
            if (isset($_v['applies']['P_'.$idx]) || isset($_v['applies']['items']['P'][$idx]) || isset($_v['applies']['P_'.$_idx]) || isset($_v['applies']['items']['P'][$_idx])) {
                $taxes[$_k] = $_v;
            }
        }
    }
    if ($cart['stored_taxes'] != 'Y' || empty($taxes)) {
        $taxes = fn_get_set_taxes($cart_products[$idx]['tax_ids']);
    }

    return $taxes;
}

/**
 * Get payment taxes
 *
 * @param integer $payment_id payment method id
 * @param array $cart cart data
 * @return array array with taxes
 */
function fn_get_payment_taxes($payment_id, $cart)
{
    // get current tax ids
    $tax_ids = db_get_field("SELECT tax_ids FROM ?:payments WHERE payment_id = ?i", $payment_id);
    if (!empty($tax_ids)) {
        $taxes = fn_get_set_taxes($tax_ids);

        // apply new rates if exists
        if ($cart['stored_taxes'] == 'Y' && !empty($cart['stored_taxes_data'])) {

            foreach ((array) $cart['stored_taxes_data'] as $_k => $_v) {

                if (!empty($taxes[$_k]) && (!empty($_v['applies']['PS_'.$payment_id]) || !empty($_v['applies']['items']['PS'][$payment_id]))) {
                    if (!empty($_v['rate_value']) && !empty($_v['rate_type'])) {
                        $taxes[$_k]['rate_value'] = $_v['rate_value'];
                        $taxes[$_k]['rate_type'] = $_v['rate_type'];
                    }
                }
            }
        }

    }

    /**
     * Init payment taxes (running after fn_get_payment_taxes function)
     *
     * @param integer $payment_id payment method id
     * @param array   $cart       cart data
     * @param array   $taxes      array with taxes
     */
    fn_set_hook('get_payment_taxes_post', $payment_id, $cart, $taxes);

    return $taxes;
}

function fn_get_shipping_taxes($shipping_id, $shipping_rates, $cart)
{
    $tax_ids = array();
    if (defined('ORDER_MANAGEMENT')) {
        $shipping_ids = array();
        foreach ($shipping_rates as $shipping) {
            $shipping_ids[] = $shipping['shipping_id'];
        }
        $_taxes = db_get_hash_single_array("SELECT tax_ids, shipping_id FROM ?:shippings WHERE shipping_id IN (?n)", array('shipping_id', 'tax_ids'), $shipping_ids);

        if (!empty($_taxes)) {
            foreach ($_taxes as $_ship => $_tax) {
                if (!empty($_tax)) {
                    $_tids = explode(',', $_tax);
                    foreach ($_tids as $_tid) {
                        $tax_ids[$_ship][$_tid] = $_tax;
                    }
                }
            }
        }
    }

    if ($cart['stored_taxes'] == 'Y') {
        $taxes = array();

        foreach ((array) $cart['taxes'] as $_k => $_v) {
            isset($_v['applies']['items']['S'][$shipping_id]) ? $exists = true : $exists = false;
            foreach ($_v['applies'] as $aid => $av) {
                if (strpos($aid, 'S_' . $shipping_id . '_') !== false) {
                    $exists = true;

                }
            }
            if ($exists == true || (!empty($tax_ids[$shipping_id]) && !empty($tax_ids[$shipping_id][$_k]))) {
                $taxes[$_k] = $_v;
                $taxes[$_k]['applies'] = array();
            }
        }
    } else {
        $taxes = array();
        $tax_ids = db_get_field("SELECT tax_ids FROM ?:shippings WHERE shipping_id = ?i", $shipping_id);
        if (!empty($tax_ids)) {
            $taxes = db_get_hash_array("SELECT tax_id, address_type, priority, price_includes_tax, regnumber FROM ?:taxes WHERE tax_id IN (?n) AND status = 'A' ORDER BY priority", 'tax_id', explode(',', $tax_ids));
        }
    }

    return $taxes;
}

/**
 * Apply calculated products and shipping taxes to cart
 * cart taxes are supposed to be empty
 *
 * @param array $calculated_data payment data taxes
 * @param array $cart cart data
 * @return boolean always true
 */
function fn_apply_calculated_taxes($calculated_data, &$cart)
{
    if ($cart['stored_taxes'] == 'Y') {
        // save taxes to prevent payment taxes loss
        $cart['stored_taxes_data'] = $cart['taxes'];
    }

    $cart['taxes'] = array();
    $cart['tax_subtotal'] = !empty($cart['tax_subtotal']) ? $cart['tax_subtotal'] : 0;
    $cart['tax_summary'] = array(
        'included' => 0,
        'added' => 0,
        'total' => 0
    );

    if (!empty($calculated_data)) {
        if (Registry::get('settings.General.tax_calculation') == 'unit_price') {
            // Based on the unit price
            $taxes_data = array();
            foreach ($calculated_data as $id => $_taxes) {
                if (empty($_taxes)) {
                    continue;
                }
                foreach ($_taxes as $k => $v) {
                    if (empty($taxes_data[$k])) {
                        $taxes_data[$k] = $v;
                        $taxes_data[$k]['tax_subtotal'] = 0;
                    }
                    $taxes_data[$k]['applies'][$id] = $v['tax_subtotal'];
                    $taxes_data[$k]['tax_subtotal'] += $v['tax_subtotal'];
                }
            }

            $calculated_data = $taxes_data;
        }

        foreach ($calculated_data as $tax_id => $v) {
            $cart['taxes'][$tax_id] = $v;

            if ($v['price_includes_tax'] == 'Y') {
                $cart['tax_summary']['included'] += $v['tax_subtotal'];
            } else {
                $cart['tax_summary']['added'] += $v['tax_subtotal'];
            }

            $cart['tax_summary']['total'] += $v['tax_subtotal'];
        }

    } else { // FIXME!!! Test on order management
        $cart['taxes'] = array();
        $cart['tax_summary'] = array();
    }

    /**
     * Apply products and shipping taxes (running after fn_apply_calculated_taxes function)
     *
     * @param array $calculated_data payment data taxes
     * @param array $cart            cart data
     */
    fn_set_hook('apply_calculated_taxes_post', $calculated_data, $cart);

    return true;
}

function fn_format_rate_value($rate_value, $rate_type, $decimals='2', $dec_point='.', $thousands_sep=',', $coefficient = '')
{
    if (!empty($coefficient) && @$rate_type != 'P') {
        $rate_value = $rate_value / floatval($coefficient);
    }

    if (empty($rate_type)) {
        $rate_type = 'F';
    }

    fn_set_hook('format_rate_value', $rate_value, $rate_type, $decimals, $dec_point, $thousands_sep, $coefficient);

    $value = number_format(fn_format_price($rate_value, '', $decimals), $decimals, $dec_point, $thousands_sep);
    if ($rate_type == 'F') { // Flat rate

        return $value;
    } elseif ($rate_type == 'P') { // Percent rate

        return $value.'%';
    }

    return $rate_value;

}

function fn_check_amount_in_stock($product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, &$cart, $update_id = 0)
{
    fn_set_hook('check_amount_in_stock', $product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart);

    // If the product is EDP don't track the inventory
    if ($is_edp == 'Y') {
        return 1;
    }

    $product = db_get_row("SELECT ?:products.tracking, ?:products.amount, ?:products.min_qty, ?:products.max_qty, ?:products.qty_step, ?:products.list_qty_count, ?:product_descriptions.product FROM ?:products LEFT JOIN ?:product_descriptions ON ?:product_descriptions.product_id = ?:products.product_id AND lang_code = ?s WHERE ?:products.product_id = ?i", CART_LANGUAGE, $product_id);

    if (isset($product['tracking']) &&
        Registry::get('settings.General.inventory_tracking') == 'Y' &&
        $product['tracking'] != ProductTracking::DO_NOT_TRACK
    ) {
        // Track amount for ordinary product
        if ($product['tracking'] == ProductTracking::TRACK_WITHOUT_OPTIONS) {
            $current_amount = $product['amount'];

        // Track amount for product with options
        } elseif ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
            $selectable_cart_id = fn_generate_cart_id($product_id, array('product_options' => $product_options), true);
            $current_amount = db_get_field(
                "SELECT amount FROM ?:product_options_inventory WHERE combination_hash = ?i",
                $selectable_cart_id
            );
            $current_amount = intval($current_amount);
        }

        if (!empty($cart['products']) && is_array($cart['products'])) {
            $product_not_in_cart = true;
            foreach ($cart['products'] as $k => $v) {
                // Check if the product with the same selectable options already exists ( for tracking = O)
                if ($k != $cart_id) {
                    if (isset ($product['tracking']) &&
                        (
                            $product['tracking'] == ProductTracking::TRACK_WITHOUT_OPTIONS &&
                            $v['product_id'] == $product_id
                        ) ||
                        (
                            $product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS &&
                            @$v['selectable_cart_id'] == $selectable_cart_id
                        )
                    ) {
                        $current_amount -= $v['amount'];
                    }
                } else {
                    $product_not_in_cart = false;
                }
            }

            if ($product['tracking'] == ProductTracking::TRACK_WITHOUT_OPTIONS &&
                !empty($update_id) && $product_not_in_cart && !empty($cart['products'][$update_id])
            ) {
                $current_amount += $cart['products'][$update_id]['amount'];
            }

            if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
                // Store cart_id for selectable options in cart variable, so if the same product is added to
                // the cart with the same selectable options, but different text options,
                // the total amount will be tracked anyway as it is the one product
                if (!empty($selectable_cart_id) && isset($cart['products'][$cart_id])) {
                    $cart['products'][$cart_id]['selectable_cart_id'] = $selectable_cart_id;
                }
            }
        }
    }

    $min_qty = 1;

    if (!empty($product['min_qty']) && $product['min_qty'] > $min_qty) {
        $min_qty = fn_ceil_to_step($product['min_qty'], $product['qty_step']);
    }

    if (!empty($product['qty_step']) && $product['qty_step'] > $min_qty) {
        $min_qty = $product['qty_step'];
    }

    $cart_amount_changed = false;
    // Step parity check
    if (!empty($product['qty_step']) && $amount % $product['qty_step']) {
        $amount = fn_ceil_to_step($amount, $product['qty_step']);
        $cart_amount_changed = true;
    }

    if (isset($current_amount) && $current_amount >= 0 && $current_amount - $amount < 0 && Registry::get('settings.General.allow_negative_amount') != 'Y') {
        // For order edit: add original amount to existent amount
        $current_amount += $original_amount;

        if ($current_amount > 0 && $current_amount - $amount < 0 && Registry::get('settings.General.allow_negative_amount') != 'Y') {
            if (!defined('ORDER_MANAGEMENT')) {
                fn_set_notification('W', __('important'), __('text_cart_amount_corrected', array(
                    '[product]' => $product['product']
                )));
                $amount = fn_ceil_to_step($current_amount, $product['qty_step']);
            } else {
                if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
                    fn_set_notification('E', __('warning'), __('text_combination_out_of_stock'));
                } else {
                    fn_set_notification('W', __('warning'), __('text_cart_not_enough_inventory'));
                }
            }
        } elseif ($current_amount - $amount < 0 && Registry::get('settings.General.allow_negative_amount') != 'Y') {
            if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
                fn_set_notification('E', __('notice'), __('text_combination_out_of_stock'));
            } else {
                fn_set_notification('E', __('notice'), __('text_cart_zero_inventory', array(
                    '[product]' => $product['product']
                )));
            }

            return false;
        } elseif ($current_amount <= 0 && $amount <= 0 && Registry::get('settings.General.allow_negative_amount') != 'Y') {
            fn_set_notification('E', __('notice'), __('text_cart_zero_inventory_and_removed', array(
                '[product]' => $product['product']
            )));

            return false;
        }
    }

    if ($amount < $min_qty || (isset($current_amount) && $amount > $current_amount && Registry::get('settings.General.allow_negative_amount') != 'Y' && Registry::get('settings.General.inventory_tracking') == 'Y') && isset($product_not_in_cart) && !$product_not_in_cart) {
        if (($current_amount < $min_qty || $current_amount == 0) && Registry::get('settings.General.allow_negative_amount') != 'Y' && Registry::get('settings.General.inventory_tracking') == 'Y') {
            if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
                fn_set_notification('E', __('warning'), __('text_combination_out_of_stock'));
            } else {
                fn_set_notification('W', __('warning'), __('text_cart_not_enough_inventory'));
            }
            if (!defined('ORDER_MANAGEMENT')) {
                $amount = false;
            }
        } elseif ($amount > $current_amount && Registry::get('settings.General.allow_negative_amount') != 'Y' && Registry::get('settings.General.inventory_tracking') == 'Y') {
            if ($product['tracking'] == ProductTracking::TRACK_WITH_OPTIONS) {
                fn_set_notification('E', __('warning'), __('text_combination_out_of_stock'));
            } else {
                fn_set_notification('W', __('warning'), __('text_cart_not_enough_inventory'));
            }
            if (!defined('ORDER_MANAGEMENT')) {
                $amount = fn_floor_to_step($current_amount, $product['qty_step']);
            }
        } elseif ($amount < $min_qty) {
            fn_set_notification('W', __('notice'), __('text_cart_min_qty', array(
                '[product]' => $product['product'],
                '[quantity]' => $min_qty
            )));

            $cart_amount_changed = false;

            if (!defined('ORDER_MANAGEMENT')) {
                $amount = $min_qty;
            }
        }
    }

    $max_qty = fn_floor_to_step($product['max_qty'], $product['qty_step']);
    if (!empty( $max_qty) && $amount >  $max_qty) {
        fn_set_notification('W', __('notice'), __('text_cart_max_qty', array(
            '[product]' => $product['product'],
            '[quantity]' =>  $max_qty
        )));
        $cart_amount_changed = false;

        if (!defined('ORDER_MANAGEMENT')) {
            $amount = $max_qty;
        }
    }

    if ($cart_amount_changed) {
        fn_set_notification('W', __('important'), __('text_cart_amount_changed', array('[product]' => $product['product'])));
    }

    fn_set_hook('post_check_amount_in_stock', $product_id, $amount, $product_options, $cart_id, $is_edp, $original_amount, $cart);

    return empty($amount) ? false : $amount;
}

//
// Calculate unique product id in the cart
//
function fn_generate_cart_id($product_id, $extra, $only_selectable = false)
{
    $_cid = array();

    if (!empty($extra['product_options']) && is_array($extra['product_options'])) {

        // Try to select all options (including Globals)
        Registry::set('runtime.skip_sharing_selection', true);

        foreach ($extra['product_options'] as $k => $v) {
            if ($only_selectable == true && ((string) intval($v) != $v || db_get_field("SELECT inventory FROM ?:product_options WHERE option_id = ?i", $k) != 'Y')) {
                continue;
            }
            $_cid[] = $v;
        }

        Registry::set('runtime.skip_sharing_selection', false);
    }

    if (isset($extra['exclude_from_calculate'])) {
        $_cid[] = $extra['exclude_from_calculate'];
    }

    fn_set_hook('generate_cart_id', $_cid, $extra, $only_selectable);

    natsort($_cid);
    array_unshift($_cid, $product_id);
    $cart_id = fn_crc32(implode('_', $_cid));

    return $cart_id;
}


//
// Normalize product amount
//
function fn_normalize_amount($amount = '1')
{
    $amount = abs(intval($amount));

    return empty($amount) ? 0 : $amount;
}


function fn_order_placement_routines($action = '', $order_id = 0, $force_notification = array(), $clear_cart = true, $area = AREA)
{

    if (Embedded::isLeft() && !Embedded::isEnabled()) {
        Embedded::enable();
    }

    if ($action == 'checkout_redirect') {
        if ($area == 'A') {
            fn_redirect("order_management.edit?order_id=" . reset($_SESSION['cart']['processed_order_id']));
        } else {
            fn_redirect('checkout.' . (Registry::get('settings.General.checkout_style') != 'multi_page' ? 'checkout' : 'summary'));
        }
    } elseif (in_array($action, array('save', 'repay', 'route')) && !empty($order_id)) {
        $order_info = fn_get_order_info($order_id, true);

        $display_notification = true;

        fn_set_hook('placement_routines', $order_id, $order_info, $force_notification, $clear_cart, $action, $display_notification);

        if (!empty($_SESSION['cart']['placement_action'])) {
            if (empty($action)) {
                $action = $_SESSION['cart']['placement_action'];
            }
            unset($_SESSION['cart']['placement_action']);
        }

        if ($area == 'C' && !empty($order_info['user_id'])) {
            $__fake = '';
            fn_save_cart_content($__fake, $order_info['user_id']);
        }

        $edp_data = fn_generate_ekeys_for_edp(array(), $order_info);
        fn_order_notification($order_info, $edp_data, $force_notification);

        $_error = false;

        if ($action == 'save') {
            if ($display_notification) {
                fn_set_notification('N', __('congratulations'), __('text_order_saved_successfully'));
            }
        } else {
            if ($order_info['status'] == STATUS_PARENT_ORDER) {
                $child_orders = db_get_hash_single_array("SELECT order_id, status FROM ?:orders WHERE parent_order_id = ?i", array('order_id', 'status'), $order_id);
                $status = reset($child_orders);
                $child_orders = array_keys($child_orders);
            } else {
                $status = $order_info['status'];
            }
            if (in_array($status, fn_get_order_paid_statuses())) {
                if ($action == 'repay') {
                    fn_set_notification('N', __('congratulations'), __('text_order_repayed_successfully'));
                } else {
                    fn_set_notification('N', __('order_placed'), __('text_order_placed_successfully'));
                }
            } elseif ($status == STATUS_BACKORDERED_ORDER) {
                fn_set_notification('W', __('important'), __('text_order_backordered'));
            } else {
                if ($area == 'A' || $action == 'repay') {
                    if ($status != STATUS_CANCELED_ORDER) {
                        $_payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = 'P'", $order_id);
                        if (!empty($_payment_info)) {
                            $_payment_info = unserialize(fn_decrypt_text($_payment_info));
                            $_msg = !empty($_payment_info['reason_text']) ? $_payment_info['reason_text'] : '';
                            $_msg .= empty($_msg) ? __('text_order_placed_error') : '';
                            fn_set_notification('E', '', $_msg);
                        }
                    }
                } else {
                    $_error = true;
                    if (!empty($child_orders)) {
                        array_unshift($child_orders, $order_id);
                    } else {
                        $child_orders = array();
                        $child_orders[] = $order_id;
                    }
                    $_SESSION['cart'][($status == STATUS_INCOMPLETED_ORDER ? 'processed_order_id' : 'failed_order_id')] = $child_orders;
                }
                if ($status == STATUS_INCOMPLETED_ORDER || ($action == 'repay' && $status == STATUS_CANCELED_ORDER)) {
                    fn_set_notification('W', __('important'), __('text_transaction_cancelled'));
                }
            }
        }

        // Empty cart
        if ($clear_cart == true && $_error == false) {
            $_SESSION['cart'] = array(
                'user_data' => !empty($_SESSION['cart']['user_data']) ? $_SESSION['cart']['user_data'] : array(),
                'profile_id' => !empty($_SESSION['cart']['profile_id']) ? $_SESSION['cart']['profile_id'] : 0,
                'user_id' => !empty($_SESSION['cart']['user_id']) ? $_SESSION['cart']['user_id'] : 0,
            );
            $_SESSION['shipping_rates'] = array();
            unset($_SESSION['shipping_hash']);

            db_query('DELETE FROM ?:user_session_products WHERE session_id = ?s AND type = ?s', Session::getId(), 'C');
        }

        fn_set_hook('order_placement_routines', $order_id, $force_notification, $order_info, $_error);

        if ($area == 'A') {
            fn_redirect("orders.details?order_id=$order_id");
        } else {
            fn_redirect('checkout.' . ($_error == true ? (Registry::get('settings.General.checkout_style') != 'multi_page' ? 'checkout' : 'summary') : "complete?order_id=$order_id"));
        }
    } elseif ($action == 'index_redirect') {
        fn_redirect(fn_url('', 'C', 'http'));
    } else {
        fn_redirect(fn_url($action, 'C', 'http'));
    }
}

//
// Calculate difference
//
function fn_less_zero($first_arg, $second_arg = 0, $zero = false)
{
    if (!empty($second_arg)) {
        if ($first_arg - $second_arg > 0) {
            return $first_arg - $second_arg;
        } else {
            return 0;
        }
    } else {
        if (empty($zero)) {
            return $first_arg;
        } else {
            return 0;
        }
    }
}

//
// Check if product was added to cart
//
function fn_check_add_product_to_cart($cart, $product, $product_id)
{
    $result = true;

    /**
     * Change parmetres of checking if product can be added to cart (run before fn_check_add_product_to_cart func)
     *
     * @param array   $cart       Array of the cart contents and user information necessary for purchase
     * @param array   $product    Params with that product is adding to cart
     * @param int     $product_id Identifier of adding product
     * @param boolean $result     Flag determines if product can be added to cart
     */
    fn_set_hook('check_add_to_cart_pre', $cart, $product, $product_id, $result);

    if (fn_allowed_for('ULTIMATE,MULTIVENDOR')) {
        if ((fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) || (fn_allowed_for('MULTIVENDOR') && isset($cart['company_id']))) {
            $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);
        }

        if (fn_allowed_for('ULTIMATE') && Registry::get('runtime.company_id')) {
            if ($product_company_id != Registry::get('runtime.company_id') && fn_ult_is_shared_product($product_id, Registry::get('runtime.company_id')) != 'Y') {
                $result = false;
            }
        }

        if (fn_allowed_for('MULTIVENDOR') && isset($cart['company_id'])) {
            if ($product_company_id != $cart['company_id']) {
                $result = false;
            }
        }
    }

    /**
     * Change parmetres of checking if product can be added to cart (run before fn_check_add_product_to_cart func)
     *
     * @param array   $cart       Array of the cart contents and user information necessary for purchase
     * @param array   $product    Params with that product is adding to cart
     * @param int     $product_id Identifier of adding product
     * @param boolean $result     Flag determines if product can be added to cart
     */
    fn_set_hook('check_add_to_cart_post', $cart, $product, $product_id, $result);

    return $result;
}

//
// Add product to cart
//
// @param array $product_data array with data for the product to add)(product_id, price, amount, product_options, is_edp)
// @return mixed cart ID for the product if addition is successful and false otherwise
//
function fn_add_product_to_cart($product_data, &$cart, &$auth, $update = false)
{
    $ids = array();
    if (!empty($product_data) && is_array($product_data)) {
        if (!defined('GET_OPTIONS')) {
            list($product_data, $cart) = fn_add_product_options_files($product_data, $cart, $auth, $update);
        }

        fn_set_hook('pre_add_to_cart', $product_data, $cart, $auth, $update);

        foreach ($product_data as $key => $data) {
            if (empty($key)) {
                continue;
            }
            if (empty($data['amount'])) {
                continue;
            }

            $data['stored_price'] = (!empty($data['stored_price']) && AREA != 'C') ? $data['stored_price'] : 'N';

            if (empty($data['extra'])) {
                $data['extra'] = array();
            }

            $product_id = (!empty($data['product_id'])) ? intval($data['product_id']) : intval($key);
            if (!fn_check_add_product_to_cart($cart, $data, $product_id)) {
                continue;
            }

            // Check if product options exist
            if (!isset($data['product_options'])) {
                $data['product_options'] = fn_get_default_product_options($product_id);
            }

            // Generate cart id
            $data['extra']['product_options'] = $data['product_options'];

            $_id = fn_generate_cart_id($product_id, $data['extra'], false);

            if (isset($ids[$_id]) && $key == $_id) {
                continue;
            }

            if (isset($data['extra']['exclude_from_calculate'])) {
                if (!empty($cart['products'][$key]) && !empty($cart['products'][$key]['extra']['aoc'])) {
                    $cart['saved_product_options'][$cart['products'][$key]['extra']['saved_options_key']] = $data['product_options'];
                }
                if (isset($cart['deleted_exclude_products'][$data['extra']['exclude_from_calculate']][$_id])) {
                    continue;
                }
            }
            $amount = fn_normalize_amount(@$data['amount']);

            if (!isset($data['extra']['exclude_from_calculate'])) {
                if ($data['stored_price'] != 'Y') {
                    $allow_add = true;
                    // Check if the product price with options modifiers equals to zero
                    $price = fn_get_product_price($product_id, $amount, $auth);
                    $zero_price_action = db_get_field("SELECT zero_price_action FROM ?:products WHERE product_id = ?i", $product_id);
                    if (!floatval($price) && $zero_price_action == 'A') {
                        if (isset($cart['products'][$key]['custom_user_price'])) {
                            $price = $cart['products'][$key]['custom_user_price'];
                        } else {
                            $custom_user_price = empty($data['price']) ? 0 : $data['price'];
                        }
                    }
                    $price = fn_apply_options_modifiers($data['product_options'], $price, 'P', array(), array('product_data' => $data));
                    if (!floatval($price)) {
                        $data['price'] = isset($data['price']) ? fn_parse_price($data['price']) : 0;

                        if (($zero_price_action == 'R' || ($zero_price_action == 'A' && floatval($data['price']) < 0)) && AREA == 'C') {
                            if ($zero_price_action == 'A') {
                                fn_set_notification('E', __('error'), __('incorrect_price_warning'));
                            }
                            $allow_add = false;
                        }

                        $price = empty($data['price']) ? 0 : $data['price'];
                    }

                    /**
                     * Recalculates price and checks if product can be added with the current price
                     *
                     * @param array $data Adding product data
                     * @param float $price Calculated product price
                     * @param boolean $allow_add Flag that determines if product can be added to cart
                     */
                    fn_set_hook('add_product_to_cart_check_price', $data, $price, $allow_add);

                    if (!$allow_add) {
                        continue;
                    }

                } else {
                    $price = empty($data['price']) ? 0 : $data['price'];
                }
            } else {
                $price = 0;
            }

            $_data = db_get_row('SELECT is_edp, options_type, tracking, unlimited_download FROM ?:products WHERE product_id = ?i', $product_id);
            if (isset($_data['is_edp'])) {
                $data['is_edp'] = $_data['is_edp'];
            } elseif (!isset($data['is_edp'])) {
                $data['is_edp'] = 0;
            }
            if (isset($_data['options_type'])) {
                $data['options_type'] = $_data['options_type'];
            }
            if (isset($_data['tracking'])) {
                $data['tracking'] = $_data['tracking'];
            }
            if (isset($_data['unlimited_download'])) {
                $data['extra']['unlimited_download'] = $_data['unlimited_download'];
            }

            // Check the sequential options
            if (!empty($data['tracking']) &&
                $data['tracking'] == ProductTracking::TRACK_WITH_OPTIONS &&
                $data['options_type'] == 'S'
            ) {
                $inventory_options = db_get_fields("SELECT a.option_id FROM ?:product_options as a LEFT JOIN ?:product_global_option_links as c ON c.option_id = a.option_id WHERE (a.product_id = ?i OR c.product_id = ?i) AND a.status = 'A' AND a.inventory = 'Y'", $product_id, $product_id);

                $sequential_completed = true;
                if (!empty($inventory_options)) {
                    foreach ($inventory_options as $option_id) {
                        if (!isset($data['product_options'][$option_id]) || empty($data['product_options'][$option_id])) {
                            $sequential_completed = false;
                            break;
                        }
                    }
                }

                if (!$sequential_completed) {
                    fn_set_notification('E', __('error'), __('select_all_product_options'));
                    // Even if customer tried to add the product from the catalog page, we will redirect he/she to the detailed product page to give an ability to complete a purchase
                    $redirect_url = fn_url('products.view?product_id=' . $product_id . '&combination=' . fn_get_options_combination($data['product_options']));
                    $_REQUEST['redirect_url'] = $redirect_url; //FIXME: Very very very BAD style to use the global variables in the functions!!!

                    return false;
                }
            }

            if (!isset($cart['products'][$_id])) { // If product doesn't exists in the cart
                $amount = empty($data['original_amount']) ? fn_check_amount_in_stock($product_id, $amount, $data['product_options'], $_id, $data['is_edp'], 0, $cart, $update == true ? $key : 0) : $data['original_amount'];

                if ($amount === false) {
                    continue;
                }

                $cart['products'][$_id]['product_id'] = $product_id;
                $cart['products'][$_id]['product_code'] = fn_get_product_code($product_id, $data['product_options']);
                $cart['products'][$_id]['product'] = fn_get_product_name($product_id);
                $cart['products'][$_id]['amount'] = $amount;
                $cart['products'][$_id]['product_options'] = $data['product_options'];
                $cart['products'][$_id]['price'] = $price;
                if (!empty($zero_price_action) && $zero_price_action == 'A') {
                    if (isset($custom_user_price)) {
                        $cart['products'][$_id]['custom_user_price'] = $custom_user_price;
                    } elseif (isset($cart['products'][$key]['custom_user_price'])) {
                        $cart['products'][$_id]['custom_user_price'] = $cart['products'][$key]['custom_user_price'];
                    }
                }
                $cart['products'][$_id]['stored_price'] = $data['stored_price'];

                // add image for minicart
                $cart['products'][$_id]['main_pair'] = fn_get_cart_product_icon($product_id, $data);

                fn_define_original_amount($product_id, $_id, $cart['products'][$_id], $data);

                if ($update == true && $key != $_id) {
                    fn_delete_cart_product($cart, $key, false);
                }

            } else { // If product is already exist in the cart

                $_initial_amount = empty($cart['products'][$_id]['original_amount']) ? $cart['products'][$_id]['amount'] : $cart['products'][$_id]['original_amount'];

                // If ID changed (options were changed), summ the total amount of old and new products
                if ($update == true && $key != $_id) {
                    $amount += $_initial_amount;
                    fn_delete_cart_product($cart, $key, false);
                }

                $cart['products'][$_id]['amount'] = fn_check_amount_in_stock($product_id, (($update == true) ? 0 : $_initial_amount) + $amount, $data['product_options'], $_id, (!empty($data['is_edp']) && $data['is_edp'] == 'Y' ? 'Y' : 'N'), 0, $cart, $update == true ? $key : 0);
            }

            $cart['products'][$_id]['extra'] = (empty($data['extra'])) ? array() : $data['extra'];
            $cart['products'][$_id]['stored_discount'] = @$data['stored_discount'];
            if (defined('ORDER_MANAGEMENT')) {
                $cart['products'][$_id]['discount'] = @$data['discount'];
            }

            // Increase product popularity
            if (empty($_SESSION['products_popularity']['added'][$product_id])) {
                $_data = array (
                    'product_id' => $product_id,
                    'added' => 1,
                    'total' => POPULARITY_ADD_TO_CART
                );

                db_query("INSERT INTO ?:product_popularity ?e ON DUPLICATE KEY UPDATE added = added + 1, total = total + ?i", $_data, POPULARITY_ADD_TO_CART);

                $_SESSION['products_popularity']['added'][$product_id] = true;
            }

            $company_id = db_get_field("SELECT company_id FROM ?:products WHERE product_id = ?i", $product_id);
            $cart['products'][$_id]['company_id'] = $company_id;

            if (!empty($data['saved_object_id'])) {
                $cart['products'][$_id]['object_id'] = $data['saved_object_id'];
            }

            fn_set_hook('add_to_cart', $cart, $product_id, $_id);

            $ids[$_id] = $product_id;
        }

        /**
         * Change product data after adding product to cart
         *
         * @param array $product_data Product data
         * @param array $cart Cart data
         * @param array $auth Auth data
         * @param bool $update Flag the determains if cart data are updated
         */
        fn_set_hook('post_add_to_cart', $product_data, $cart, $auth, $update);

        $cart['recalculate'] = true;

        if (!empty($cart['chosen_shipping'])) {
            $cart['calculate_shipping'] = true;
            unset($cart['product_groups']);
        }

        return $ids;

    } else {
        return false;
    }
}

function fn_form_cart($order_id, &$cart, &$auth)
{
    $order_info = fn_get_order_info($order_id, false, false);

    if (empty($order_info)) {
        fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('order'))),'','404');

        return false;
    }

    // Fill the cart
    foreach ($order_info['products'] as $_id => $item) {
        $_item = array (
            $item['product_id'] => array (
                'amount' => $item['amount'],
                'product_options' => (!empty($item['extra']['product_options']) ? $item['extra']['product_options'] : array()),
                'price' => $item['original_price'],
                'stored_discount' => 'Y',
                'stored_price' => 'Y',
                'discount' => (!empty($item['extra']['discount']) ? $item['extra']['discount'] : 0),
                'original_amount' => $item['amount'], // the original amount, that stored in order
                'original_product_data' => array ( // the original cart ID and amount, that stored in order
                    'cart_id' => $_id,
                    'amount' => $item['amount'],
                ),
            ),
        );
        if (isset($item['extra'])) {
            $_item[$item['product_id']]['extra'] = $item['extra'];
        }

        fn_add_product_to_cart($_item, $cart, $auth);
    }

    // Workaround for the add-ons that do not add a product to cart unless the parent product is already added.
    if (count($order_info['products']) > count($cart['products'])) {
        foreach ($order_info['products'] as $_id => $item) {
            if (empty($cart['products'][$_id])) {
                $_item = array (
                    $item['product_id'] => array (
                        'amount' => $item['amount'],
                        'product_options' => (!empty($item['extra']['product_options']) ? $item['extra']['product_options'] : array()),
                        'price' => $item['original_price'],
                        'stored_discount' => 'Y',
                        'stored_price' => 'Y',
                        'discount' => (!empty($item['extra']['discount']) ? $item['extra']['discount'] : 0),
                        'original_amount' => $item['amount'], // the original amount, that stored in order
                        'original_product_data' => array ( // the original cart ID and amount, that stored in order
                            'cart_id' => $_id,
                            'amount' => $item['amount'],
                        ),
                    ),
                );
                if (isset($item['extra'])) {
                    $_item[$item['product_id']]['extra'] = $item['extra'];
                }
                fn_add_product_to_cart($_item, $cart, $auth);
            }
        }
    }

    // Restore custom files
    $dir_path = 'order_data/' . $order_id;

    if (Storage::instance('custom_files')->isExist($dir_path)) {
        Storage::instance('custom_files')->copy($dir_path, 'sess_data');
    }

    $cart['payment_id'] = $order_info['payment_id'];
    $cart['stored_taxes'] = 'Y';
    $cart['stored_discount'] = 'Y';
    $cart['taxes'] = $order_info['taxes'];
    $cart['promotions'] = !empty($order_info['promotions']) ? $order_info['promotions'] : array();

    $cart['shipping'] = (!empty($order_info['shipping'])) ? $order_info['shipping'] : array();
    $cart['stored_shipping'] = array();
    foreach ($cart['shipping'] as $sh_id => $v) {
        if (!empty($v['rates'])) {
            $cart['stored_shipping'][$sh_id] = array_sum($v['rates']);
        }
    }

    if (!empty($order_info['product_groups'])) {
        $cart['product_groups'] = $order_info['product_groups'];
        foreach ($order_info['product_groups'] as $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $key => $chosen_shipping) {
                    foreach ($group['shippings'] as $shipping_id => $shipping) {
                        if ($shipping_id == $chosen_shipping['shipping_id']) {
                            $cart['chosen_shipping'][$chosen_shipping['group_key']] = $shipping_id;
                        }
                    }
                }
            }
        }
    } else {
        $cart['product_groups'] = array();
    }

    $cart['notes'] = $order_info['notes'];
    $cart['details'] = $order_info['details'];
    $cart['payment_info'] = @$order_info['payment_info'];
    $cart['profile_id'] = $order_info['profile_id'];

    // Add order discount
    if (floatval($order_info['subtotal_discount'])) {
        $cart['stored_subtotal_discount'] = 'Y';
        $cart['subtotal_discount'] = $cart['original_subtotal_discount'] = fn_format_price($order_info['subtotal_discount']);
    }

    // Fill the cart with the coupons
    if (!empty($order_info['coupons'])) {
        $cart['coupons'] = $order_info['coupons'];
    }

    // Set the customer if exists
    $_data = array();
    if (!empty($order_info['user_id'])) {
        $_data = db_get_row("SELECT user_id, user_login as login FROM ?:users WHERE user_id = ?i", $order_info['user_id']);
    }
    $auth = fn_fill_auth($_data, array(), false, 'C');
    $auth['tax_exempt'] = $order_info['tax_exempt'];

    // Fill customer info
    $cart['user_data'] = fn_check_table_fields($order_info, 'user_profiles');
    $cart['user_data'] = fn_array_merge(fn_check_table_fields($order_info, 'users'), $cart['user_data']);
    if (!empty($order_info['fields'])) {
        $cart['user_data']['fields'] = $order_info['fields'];
    }
    fn_add_user_data_descriptions($cart['user_data']);

    fn_set_hook('form_cart', $order_info, $cart);

    return true;
}

//
// Calculate taxes for products or shippings
//
function fn_calculate_tax_rates($taxes, $price, $amount, $auth, &$cart)
{
    static $destination_id;
    static $tax_description;
    static $user_data;

    $taxed_price = $price;

    if (!empty($cart['user_data']) && !fn_is_empty($cart['user_data'])) {
        $profile_fields = fn_get_profile_fields('O', $auth);
        $billing_population = fn_check_profile_fields_population($cart['user_data'], 'B', $profile_fields);
        $shipping_population = fn_check_profile_fields_population($cart['user_data'], 'S', $profile_fields);

        if (empty($auth['user_id']) && (!$shipping_population || !$billing_population)) {
            fn_define('ESTIMATION', true);
        }
    }

    if (empty($auth['user_id']) && (empty($cart['user_data']) || fn_is_empty($cart['user_data']) || $billing_population != true || $shipping_population != true) && Registry::get('runtime.checkout') && Registry::get('settings.Appearance.taxes_using_default_address') !== 'Y' && !defined('ESTIMATION')) {
        return false;
    }

    if ((empty($destination_id) || $user_data != @$cart['user_data'])) {
        // Get billing location
        $location = fn_get_customer_location($auth, $cart, true);
        $destination_id['B'] = fn_get_available_destination($location);

        // Get shipping location
        $location = fn_get_customer_location($auth, $cart);
        $destination_id['S'] = fn_get_available_destination($location);
    }

    if (!empty($cart['user_data'])) {
        $user_data = $cart['user_data'];
    }
    $_tax = 0;
    $previous_priority = -1;
    $previous_price = '';

    foreach ($taxes as $key => $tax) {
        if (empty($tax['tax_id'])) {
            $tax['tax_id'] = $key;
        }

        if (empty($tax['priority'])) {
            $tax['priority'] = 0;
        }

        $_is_zero = floatval($taxed_price);
        if (empty($_is_zero)) {
            continue;
        }

        if (!empty($cart['stored_taxes']) && $cart['stored_taxes'] == 'Y' && (!empty($tax['rate_type']) || isset($cart['taxes'][$tax['tax_id']]['rate_value']))) {
            $rate = array (
                'rate_value' => isset($cart['taxes'][$tax['tax_id']]['rate_value']) ? $cart['taxes'][$tax['tax_id']]['rate_value'] : $tax['rate_value'],
                'rate_type' => isset($cart['taxes'][$tax['tax_id']]['rate_type']) ? $cart['taxes'][$tax['tax_id']]['rate_type'] : $tax['rate_type']
            );

        } else {
            if (!isset($destination_id[$tax['address_type']])) {
                continue;
            }

            $rate = db_get_row("SELECT destination_id, rate_value, rate_type FROM ?:tax_rates WHERE tax_id = ?i AND destination_id = ?i", $tax['tax_id'], $destination_id[$tax['address_type']]);
            if (!@floatval($rate['rate_value'])) {
                continue;
            }
        }

        $base_price = ($tax['priority'] == $previous_priority) ? $previous_price : $taxed_price;

        if ($rate['rate_type'] == 'P') { // Percent dependence
            // If tax is included into the price
            if ($tax['price_includes_tax'] == 'Y') {
                $_tax = fn_format_price($base_price - $base_price / ( 1 + ($rate['rate_value'] / 100)));
                // If tax is NOT included into the price
            } else {
                $_tax = fn_format_price($base_price * ($rate['rate_value'] / 100));
                $taxed_price += $_tax;
            }

        } else {
            $_tax = fn_format_price($rate['rate_value']);
            // If tax is NOT included into the price
            if ($tax['price_includes_tax'] != 'Y') {
                $taxed_price += $_tax;
            }
        }

        $previous_priority = $tax['priority'];
        $previous_price = $base_price;

        if (empty($tax_description[$tax['tax_id']])) {
            $tax_description[$tax['tax_id']] = db_get_field("SELECT tax FROM ?:tax_descriptions WHERE tax_id = ?i AND lang_code = ?s", $tax['tax_id'], CART_LANGUAGE);
        }

        $taxes_data[$tax['tax_id']] = array (
            'rate_type' => $rate['rate_type'],
            'rate_value' => $rate['rate_value'],
            'price_includes_tax' => $tax['price_includes_tax'],
            'regnumber' => @$tax['regnumber'],
            'priority' => @$tax['priority'],
            'tax_subtotal' => fn_format_price($_tax * $amount),
            'description' => $tax_description[$tax['tax_id']],
        );
    }

    return empty($taxes_data) ? false : $taxes_data;
}

function fn_get_predefined_statuses($type)
{
    $statuses = array(
        'profiles' => array(
            'A' => __('active'),
            'P' => __('pending'),
            'F' => __('available'),
            'D' => __('declined')
        ),
        'usergroups' => array(
            'A' => __('active'),
            'P' => __('pending'),
            'F' => __('available'),
            'D' => __('declined'),
        ),
    );

    fn_set_hook('get_predefined_statuses', $type, $statuses);

    return $statuses[$type];
}

//
//Get order payment data
//
function fn_get_payment_data($payment_id, $object_id = 0, $lang_code = CART_LANGUAGE)
{
    $data = db_get_row("SELECT * FROM ?:payment_descriptions WHERE payment_id = ?i AND lang_code = ?s", $payment_id, $lang_code);

    fn_set_hook('get_payment_data', $data, $payment_id, $object_id, $lang_code);

    return $data;
}

function fn_get_status_params($status, $type = STATUSES_ORDER)
{
    return db_get_hash_single_array("SELECT param, value FROM ?:status_data WHERE status = ?s AND type = ?s", array('param', 'value'), $status, $type);
}

/**
 * Gets parameter value of the status
 *
 * @param string $status Status code
 * @param string $param Parameter name
 * @param string $type Status type (order type defualt)
 * @return string Parameter value
 */
function fn_get_status_param_value($status, $param, $type = STATUSES_ORDER)
{
    return db_get_field("SELECT value FROM ?:status_data WHERE status = ?s AND param = ?s AND type = ?s", $status, $param, $type);
}

//
// Delete product from the cart
//
function fn_delete_cart_product(&$cart, $cart_id, $full_erase = true)
{
    fn_set_hook('delete_cart_product', $cart, $cart_id, $full_erase);

    if (!empty($cart_id) && !empty($cart['products'][$cart_id])) {
        // Decrease product popularity
        if (!empty($cart['products'][$cart_id]['product_id'])) {
            $product_id = $cart['products'][$cart_id]['product_id'];

            $_data = array (
                'product_id' => $product_id,
                'deleted' => 1,
                'total' => 0
            );

            db_query("INSERT INTO ?:product_popularity ?e ON DUPLICATE KEY UPDATE deleted = deleted + 1, total = total - ?i", $_data, POPULARITY_DELETE_FROM_CART);

            unset($_SESSION['products_popularity']['added'][$product_id]);
        }

        // Delete saved product files
        if (isset($cart['products'][$cart_id]['extra']['custom_files']) && $full_erase) {
            foreach ($cart['products'][$cart_id]['extra']['custom_files'] as $option_id => $images) {
                if (!empty($images)) {
                    foreach ($images as $image) {
                        Storage::instance('custom_files')->delete($image['path']);
                        Storage::instance('custom_files')->delete($image['path'] . '_thumb');
                    }
                }
            }
        }

        unset($cart['products'][$cart_id]);

        if (!empty($cart['product_groups'])) {
            foreach ($cart['product_groups'] as $group_key => $group) {
                if (isset($group['products'][$cart_id])) {
                    unset($cart['product_groups'][$group_key]['products'][$cart_id]);
                }
            }
        }

        if (!empty($cart['chosen_shipping'])) {
            $cart['calculate_shipping'] = true;
            unset($cart['product_groups']);
        }

        $cart['recalculate'] = true;
        $cart['change_cart_products'] = true;
    }

    return true;
}

//
// Checks whether this order used the current payment and calls the payment_cc_complete.php file
//
function fn_check_payment_script($script_name, $order_id, &$processor_data = null)
{
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
    $processor_data = fn_get_processor_data($payment_id);
    if ($processor_data['processor_script'] == $script_name) {
        return true;
    }

    return false;
}

//
// This function calculates product prices without taxes and with taxes
//
function fn_get_taxed_and_clean_prices(&$product, &$auth)
{
    $tax_value = 0;
    $included_tax = false;

    if (empty($product) || empty($product['product_id']) || empty($product['tax_ids'])) {
        return false;
    }
    if (isset($product['subtotal'])) {
        $tx_price =  $product['subtotal'];
    } elseif (empty($product['price'])) {
        $tx_price = 0;
    } elseif (isset($product['discounted_price'])) {
        $tx_price = $product['discounted_price'];
    } else {
        $tx_price = $product['price'];
    }

    $product_taxes = fn_get_set_taxes($product['tax_ids']);

    $calculated_data = fn_calculate_tax_rates($product_taxes, $tx_price, 1, $auth, $_SESSION['cart']);
    // Apply taxes to product subtotal
    if (!empty($calculated_data)) {
        foreach ($calculated_data as $_k => $v) {
            $tax_value += $v['tax_subtotal'];
            if ($v['price_includes_tax'] != 'Y') {
                $included_tax = true;
                $tx_price += $v['tax_subtotal'];
            }
        }
    }

    $product['clean_price'] = $tx_price - $tax_value;
    $product['taxed_price'] = $tx_price;
    $product['taxes'] = $calculated_data;
    $product['included_tax'] = $included_tax;

    return true;
}

function fn_clear_cart(&$cart, $complete = false, $clear_all = false)
{
    fn_set_hook('clear_cart', $cart, $complete, $clear_all);

    // Decrease products popularity
    if (!empty($cart['products'])) {
        $pids = array();

        foreach ($cart['products'] as $product) {
            $pids[] = $product['product_id'];
            unset($_SESSION['products_popularity']['added'][$product['product_id']]);
        }

        db_query("UPDATE ?:product_popularity SET deleted = deleted + 1, total = total - ?i WHERE product_id IN (?n)", POPULARITY_DELETE_FROM_CART, $pids);
    }

    if ($clear_all) {
        $cart = array();
    } else {
        $cart = array (
            'products' => array(),
            'recalculate' => false,
            'user_data' => !empty($cart['user_data']) && $complete == false ? $cart['user_data'] : array(),
        );
    }

    return true;
}

function fn_external_discounts($product)
{
    $discounts = 0;

    fn_set_hook('get_external_discounts', $product, $discounts);

    return $discounts;
}

// FIX-EVENT - must be revbuilt to check edp, free, etc
function fn_exclude_from_shipping_calculate($product)
{
    $exclude = false;

    fn_set_hook('exclude_from_shipping_calculation', $product, $exclude);

    return $exclude;
}

//
// This function is used to find out the total shipping cost. Used in payments, quickbooks
//
function fn_order_shipping_cost($order_info)
{
    $cost = (floatval($order_info['shipping_cost'])) ? $order_info['shipping_cost'] : 0;

    if (floatval($order_info['shipping_cost']) && Registry::get('settings.General.tax_calculation') != 'unit_price') {
        $cost += fn_order_shipping_taxes_cost($order_info);
    }

    return $cost ? fn_format_price($cost) : 0;
}

/**
 * Calculate the shipping taxes total.
 *
 * @param array $order_info
 * @return int
 */
function fn_order_shipping_taxes_cost($order_info)
{
    $cost = 0;

    if (!empty($order_info['taxes'])) {
        foreach ($order_info['taxes'] as $tax) {
            if ($tax['price_includes_tax'] == 'N') {
                foreach ($tax['applies'] as $_id => $value) {
                    if (strpos($_id, 'S_') !== false) {
                        $cost += $value;
                    }
                }
            }
        }
    }

    return $cost;
}

//
// Cleanup payment information
//
function fn_cleanup_payment_info($order_id = '', $payment_info, $silent = false)
{
    if ($silent == false) {
        fn_set_progress('echo', __('processing_order') . '&nbsp;<b>#'.$order_id.'</b>...');
    }

    if (!is_array($payment_info)) {
        $info = @unserialize(fn_decrypt_text($payment_info));
    } else {
        $info = $payment_info;
    }

    if (!empty($info['cvv2'])) {
        $info['cvv2'] = 'XXX';
    }
    if (!empty($info['card_number'])) {
        $info['card_number'] = str_replace(array('-', ' '), '', $info['card_number']);
        $info['card_number'] = substr_replace($info['card_number'], str_repeat('X', strlen($info['card_number']) - 4), 0, strlen($info['card_number']) - 4);
    }

    foreach (array('expiry_month', 'expiry_year') as $v) {
        if (!empty($info[$v])) {
            $info[$v] = 'XX';
        }
    }

    $_data = fn_encrypt_text(serialize($info));
    if (!empty($order_id)) {
        db_query("UPDATE ?:order_data SET data = ?s WHERE order_id = ?i AND type = 'P'", $_data, $order_id);
    } else {
        return $_data;
    }
}

//
// Checks if order can be placed
//
function fn_allow_place_order(&$cart)
{
    $total = Registry::get('settings.General.min_order_amount_type') == 'products_with_shippings' ? $cart['total'] : $cart['subtotal'];

    fn_set_hook('allow_place_order', $total, $cart);

    $cart['amount_failed'] = (Registry::get('settings.General.min_order_amount') > $total && floatval($total));

    if (!empty($cart['amount_failed']) || !empty($cart['shipping_failed']) || !empty($cart['company_shipping_failed'])) {
        return false;
    }

    return true;
}

/**
 * Returns orders
 *
 * @param array $params array with search params
 * @param int $items_per_page
 * @param bool $get_totals
 * @param string $lang_code
 * @return array
 */
function fn_get_orders($params, $items_per_page = 0, $get_totals = false, $lang_code = CART_LANGUAGE)
{
    // Init filter
    $params = LastView::instance()->update('orders', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    if (AREA != 'C') {
        $params['include_incompleted'] = empty($params['include_incompleted']) ? false : $params['include_incompleted']; // default incomplited orders should not be displayed
        if (!empty($params['status']) && (is_array($params['status']) && in_array(STATUS_INCOMPLETED_ORDER, $params['status']) || !is_array($params['status']) && $params['status'] == STATUS_INCOMPLETED_ORDER)) {
            $params['include_incompleted'] = true;
        }
    } else {
        $params['include_incompleted'] = false;
    }

    // Define fields that should be retrieved
    $fields = array (
        "distinct ?:orders.order_id",
        "?:orders.issuer_id",
        "?:orders.user_id",
        "?:orders.is_parent_order",
        "?:orders.parent_order_id",
        "?:orders.company_id",
        "?:orders.timestamp",
        "?:orders.firstname",
        "?:orders.lastname",
        "?:orders.email",
        "?:orders.phone",
        "?:orders.status",
        "?:orders.total",
        "invoice_docs.doc_id as invoice_id",
        "memo_docs.doc_id as credit_memo_id"
    );

    // Define sort fields
    $sortings = array (
        'order_id' => "?:orders.order_id",
        'status' => "?:orders.status",
        'customer' => array("?:orders.lastname", "?:orders.firstname"),
        'email' => "?:orders.email",
        'date' => array("?:orders.timestamp", "?:orders.order_id"),
        'total' => "?:orders.total",
    );

    fn_set_hook('pre_get_orders', $params, $fields, $sortings, $get_totals, $lang_code);

    if (isset($params['compact']) && $params['compact'] == 'Y') {
        $union_condition = ' OR ';
    } else {
        $union_condition = ' AND ';
    }

    $condition = $_condition = $join = $group = '';

    $condition .= " AND ?:orders.is_parent_order != 'Y' ";
    $condition .= fn_get_company_condition('?:orders.company_id');

    if (isset($params['cname']) && fn_string_not_empty($params['cname'])) {
        $customer_name = fn_explode(' ', $params['cname']);
        $customer_name = array_filter($customer_name, "fn_string_not_empty");
        if (sizeof($customer_name) == 2) {
            $_condition .= db_quote(" $union_condition ?:orders.firstname LIKE ?l AND ?:orders.lastname LIKE ?l", "%" . array_shift($customer_name) . "%", "%" . array_shift($customer_name) . "%");
        } else {
            $_condition .= db_quote(" $union_condition (?:orders.firstname LIKE ?l OR ?:orders.lastname LIKE ?l)", "%" . trim($params['cname']) . "%", "%" . trim($params['cname']) . "%");
        }
    }

    if (isset($params['issuer']) && fn_string_not_empty($params['issuer'])) {
        $issuer_name = fn_explode(' ', $params['issuer']);
        $issuer_name = array_filter($issuer_name, "fn_string_not_empty");
        if (sizeof($issuer_name) == 2) {
            $issuer_ids = db_get_field("SELECT user_id FROM ?:users WHERE user_type IN ('A', 'V') AND  firstname LIKE ?l AND lastname LIKE ?l", "%" . array_shift($issuer_name) . "%", "%" . array_shift($issuer_name) . "%");
        } else {
            $issuer_ids = db_get_field("SELECT user_id FROM ?:users WHERE user_type IN ('A', 'V') AND (firstname LIKE ?l OR lastname LIKE ?l)", "%" . trim($params['issuer']) . "%", "%" . trim($params['issuer']) . "%");
        }

        $_condition .= db_quote(" $union_condition ?:orders.issuer_id IN (?a)", $issuer_ids);
    }

    if (isset($params['company_id']) && $params['company_id'] != '') {
        $condition .= db_quote(' AND ?:orders.company_id = ?i ', $params['company_id']);
    }

    if (!empty($params['tax_exempt'])) {
        $condition .= db_quote(" AND ?:orders.tax_exempt = ?s", $params['tax_exempt']);
    }

    if (isset($params['email']) && fn_string_not_empty($params['email'])) {
        $_condition .= db_quote(" $union_condition ?:orders.email LIKE ?l", "%" . trim($params['email']) . "%");
    }

    if (!empty($params['user_id'])) {
        $condition .= db_quote(' AND ?:orders.user_id IN (?n)', $params['user_id']);
    }

    if (isset($params['total_from']) && fn_is_numeric($params['total_from'])) {
        $condition .= db_quote(" AND ?:orders.total >= ?d", fn_convert_price($params['total_from']));
    }

    if (!empty($params['total_to']) && fn_is_numeric($params['total_to'])) {
        $condition .= db_quote(" AND ?:orders.total <= ?d", fn_convert_price($params['total_to']));
    }

    if (!empty($params['status'])) {
        $condition .= db_quote(' AND ?:orders.status IN (?a)', $params['status']);
    }

    if (empty($params['include_incompleted'])) {
        $condition .= db_quote(' AND ?:orders.status != ?s', STATUS_INCOMPLETED_ORDER);
    }

    if (!empty($params['order_id'])) {
        $_condition .= db_quote($union_condition . ' ?:orders.order_id IN (?n)', (!is_array($params['order_id']) && (strpos($params['order_id'], ',') !== false) ? explode(',', $params['order_id']) : $params['order_id']));
    }

    if (!empty($params['p_ids']) || !empty($params['product_view_id'])) {
        $arr = (strpos($params['p_ids'], ',') !== false || !is_array($params['p_ids'])) ? explode(',', $params['p_ids']) : $params['p_ids'];

        if (empty($params['product_view_id'])) {
            $condition .= db_quote(" AND ?:order_details.product_id IN (?n)", $arr);
        } else {
            $condition .= db_quote(" AND ?:order_details.product_id IN (?n)", db_get_fields(fn_get_products(array('view_id' => $params['product_view_id'], 'get_query' => true))));
        }

        $join .= " LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id";
        $group .=  " GROUP BY ?:orders.order_id ";
    }

    if (!empty($params['admin_user_id'])) {
        $condition .= db_quote(" AND ?:new_orders.user_id = ?i", $params['admin_user_id']);
        $join .= " LEFT JOIN ?:new_orders ON ?:new_orders.order_id = ?:orders.order_id";
    }

    $docs_conditions = array();
    if (!empty($params['invoice_id']) || !empty($params['has_invoice'])) {
        if (!empty($params['has_invoice'])) {
            $docs_conditions[] = "invoice_docs.doc_id IS NOT NULL";
        } elseif (!empty($params['invoice_id'])) {
            $docs_conditions[] = db_quote("invoice_docs.doc_id = ?i", $params['invoice_id']);
        }
    }
    $join .= " LEFT JOIN ?:order_docs as invoice_docs ON invoice_docs.order_id = ?:orders.order_id AND invoice_docs.type = 'I'";

    if (!empty($params['credit_memo_id']) || !empty($params['has_credit_memo'])) {
        if (!empty($params['has_credit_memo'])) {
            $docs_conditions[] = "memo_docs.doc_id IS NOT NULL";
        } elseif (!empty($params['credit_memo_id'])) {
            $docs_conditions[] = db_quote("memo_docs.doc_id = ?i", $params['credit_memo_id']);
        }
    }
    $join .= " LEFT JOIN ?:order_docs as memo_docs ON memo_docs.order_id = ?:orders.order_id AND memo_docs.type = 'C'";

    if (!empty($docs_conditions)) {
        $condition .= ' AND (' . implode(' OR ', $docs_conditions) . ')';
    }

    if (!empty($params['shippings'])) {
        $set_conditions = array();
        foreach ($params['shippings'] as $v) {
            $set_conditions[] = db_quote("FIND_IN_SET(?s, ?:orders.shipping_ids)", $v);
        }
        $condition .= ' AND (' . implode(' OR ', $set_conditions) . ')';
    }

    if (!empty($params['payments'])) {
        $condition .= db_quote(" AND ?:orders.payment_id IN (?a)", $params['payments']);
    }

    if (!empty($params['period']) && $params['period'] != 'A') {
        list($params['time_from'], $params['time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:orders.timestamp >= ?i AND ?:orders.timestamp <= ?i)", $params['time_from'], $params['time_to']);
    }

    if (!empty($params['custom_files']) && $params['custom_files'] == 'Y') {
        $condition .= db_quote(" AND ?:order_details.extra LIKE ?l", '%custom_files%');

        if (empty($params['p_ids']) && empty($params['product_view_id'])) {
            $join .= " LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id";
        }
    }

    if (!empty($params['company_name'])) {
        $fields[] = '?:companies.company as company_name';
        $join .= " LEFT JOIN ?:companies ON ?:companies.company_id = ?:orders.company_id";
    }

    if (!empty($_condition)) {
        $condition .= ' AND (' . ($union_condition == ' OR ' ? '0 ' : '1 ') . $_condition . ')';
    }

    fn_set_hook('get_orders', $params, $fields, $sortings, $condition, $join, $group);

    $sorting = db_sort($params, $sortings, 'date', 'desc');

    // Used for Extended search
    if (!empty($params['get_conditions'])) {
        return array($fields, $join, $condition);
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT (?:orders.order_id)) FROM ?:orders $join WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $orders = db_get_array('SELECT ' . implode(', ', $fields) . " FROM ?:orders $join WHERE 1 $condition $group $sorting $limit");

    fn_set_hook('get_orders_post', $params, $orders);

    if ($get_totals == true) {
        $paid_statuses = array('P', 'C');
        fn_set_hook('get_orders_totals', $paid_statuses, $join, $condition, $group);
        $totals = array (
            'gross_total' => db_get_field("SELECT sum(t.total) FROM ( SELECT total FROM ?:orders $join WHERE 1 $condition $group) as t"),
            'totally_paid' => db_get_field("SELECT sum(t.total) FROM ( SELECT total FROM ?:orders $join WHERE ?:orders.status IN (?a) $condition $group) as t", $paid_statuses),
        );

        $params['paid_statuses'] = $paid_statuses;
    }

    LastView::instance()->processResults('orders', $orders, $params);

    return array($orders, $params, ($get_totals == true ? $totals : array()));
}

/**
 * Gets shipping method parameters by identifier
 *
 * @param int $shipping_id Shipping identifier
 * @return array Shipping parameters
 */
function fn_get_shipping_params($shipping_id)
{
    $params = array();
    if ($shipping_id) {
        $params = db_get_field("SELECT service_params FROM ?:shippings WHERE shipping_id = ?i", $shipping_id);
        $params = unserialize($params);
    }

    return $params;
}

/**
 * Gets shipping service data by identifier
 *
 * @param int $service_id Shipping service identifier
 * @return array Shipping service data
 */
function fn_get_shipping_service_data($service_id)
{
    static $services = array();

    if (!isset($services[$service_id])) {

        $service = db_get_row("SELECT code, module FROM ?:shipping_services WHERE service_id = ?i AND status = 'A'", $service_id);

        if (empty($service)) {
            $services[$service_id] = false;

            return false;
        }

        $services[$service_id] = $service;
    }

    return $services[$service_id];
}

/**
 * Convert weight to pounds/ounces
 *
 * @param float $weight weight
 * @return array converted data
 */
function fn_expand_weight($weight)
{
    $full_ounces = ceil(round($weight * Registry::get('settings.General.weight_symbol_grams') / 28.35, 3));
    $full_pounds = sprintf("%.1f", $full_ounces/16);
    $pounds = floor($full_ounces/16);
    $ounces = $full_ounces - $pounds * 16;

    return array (
        'full_ounces' => $full_ounces,
        'full_pounds' => $full_pounds,
        'pounds' => $pounds,
        'ounces' => $ounces,
        'plain' => $weight,
    );
}

/**
 * Generate unique ID to cache rates calculation results
 *
 * @param mixed parameters to generate unique ID from
 * @return mixed array with rates if calculated, false otherwise
 */
function fn_generate_cached_rate_id()
{
    return md5(serialize(func_get_args()));
}

/**
 * Send order notification
 *
 * @param array $order_info order information
 * @param array $edp_data information about downloadable products
 * @param mixed $force_notification user notification flag (true/false), if not set, will be retrieved from status parameters
 * @return array structured data
 */
function fn_order_notification(&$order_info, $edp_data = array(), $force_notification = array())
{
    static $notified = array();

    $send_order_notification = true;

    if ((!empty($notified[$order_info['order_id']][$order_info['status']]) && $notified[$order_info['order_id']][$order_info['status']]) || $order_info['status'] == STATUS_INCOMPLETED_ORDER || $order_info['status'] == STATUS_PARENT_ORDER) {
        $send_order_notification = false;
    }

    fn_set_hook('send_order_notification', $order_info, $edp_data, $force_notification, $notified, $send_order_notification);

    $take_surcharge_from_vendor = false;
    if (fn_allowed_for('MULTIVENDOR')) {
        $take_surcharge_from_vendor = fn_take_payment_surcharge_from_vendor($order_info['products']);
    }

    if (!$send_order_notification) {
        return true;
    }

    if (!is_array($force_notification)) {
        $force_notification = fn_get_notification_rules($force_notification, !$force_notification);
    }
    $order_statuses = fn_get_statuses(STATUSES_ORDER, array(), true, false, ($order_info['lang_code'] ? $order_info['lang_code'] : CART_LANGUAGE), $order_info['company_id']);
    $status_params = $order_statuses[$order_info['status']]['params'];

    $notify_user = isset($force_notification['C']) ? $force_notification['C'] : (!empty($status_params['notify']) && $status_params['notify'] == 'Y' ? true : false);
    $notify_department = isset($force_notification['A']) ? $force_notification['A'] : (!empty($status_params['notify_department']) && $status_params['notify_department'] == 'Y' ? true : false);
    $notify_vendor = isset($force_notification['V']) ? $force_notification['V'] : (!empty($status_params['notify_vendor']) && $status_params['notify_vendor'] == 'Y' ? true : false);

    if ($notify_user == true || $notify_department == true || $notify_vendor == true) {

        $notified[$order_info['order_id']][$order_info['status']] = true;

        $order_status = $order_statuses[$order_info['status']];
        $payment_method = fn_get_payment_data((!empty($order_info['payment_method']['payment_id']) ? $order_info['payment_method']['payment_id'] : 0), $order_info['order_id'], $order_info['lang_code']);
        $status_settings = $order_statuses[$order_info['status']]['params'];
        $profile_fields = fn_get_profile_fields('I', '', $order_info['lang_code']);
        $secondary_currency = '';

        list($shipments) = fn_get_shipments_info(array('order_id' => $order_info['order_id'], 'advanced_info' => true));
        $use_shipments = !fn_one_full_shipped($shipments);

        // restore secondary currency
        if (!empty($order_info['secondary_currency']) && Registry::get("currencies.{$order_info['secondary_currency']}")) {
            $secondary_currency = $order_info['secondary_currency'];
        }

        // Notify customer
        if ($notify_user == true) {

            Mailer::sendMail(array(
                'to' => $order_info['email'],
                'from' => 'company_orders_department',
                'data' => array(
                    'order_info' => $order_info,
                    'shipments' => $shipments,
                    'use_shipments' => $use_shipments,
                    'order_status' => $order_status,
                    'payment_method' => $payment_method,
                    'status_settings' => $status_settings,
                    'profile_fields' => $profile_fields,
                    'secondary_currency' => $secondary_currency,
                    'take_surcharge_from_vendor' => $take_surcharge_from_vendor
                ),
                'tpl' => 'orders/order_notification.tpl',
                'company_id' => $order_info['company_id'],
            ), 'C', $order_info['lang_code']);

            if (!empty($edp_data)) {
                Mailer::sendMail(array(
                    'to' => $order_info['email'],
                    'from' => 'company_orders_department',
                    'data' => array(
                        'order_info' => $order_info,
                        'edp_data' => $edp_data,
                    ),
                    'tpl' => 'orders/edp_access.tpl',
                    'company_id' => $order_info['company_id'],
                ), 'C', $order_info['lang_code']);
            }
        }

        if ($notify_vendor == true) {
            if (fn_allowed_for('MULTIVENDOR') && !empty($order_info['company_id'])) {

                $company_lang_code = fn_get_company_language($order_info['company_id']);

                // Translate descriptions to admin language
                fn_translate_products($order_info['products'], '', $company_lang_code, true);

                Mailer::sendMail(array(
                    'to' => 'company_orders_department',
                    'from' => 'default_company_orders_department',
                    'reply_to' => $order_info['email'],
                    'data' => array(
                        'order_info' => $order_info,
                        'shipments' => $shipments,
                        'use_shipments' => $use_shipments,
                        'order_status' => fn_get_status_data($order_info['status'], STATUSES_ORDER, $order_info['order_id'], $company_lang_code),
                        'payment_method' => fn_get_payment_data($order_info['payment_method']['payment_id'], $order_info['order_id'], $company_lang_code),
                        'status_settings' => $status_settings,
                        'profile_fields' => fn_get_profile_fields('I', '', $company_lang_code),
                        'secondary_currency' => $secondary_currency
                    ),
                    'tpl' => 'orders/order_notification.tpl',
                    'company_id' => $order_info['company_id'],
                ), 'A', $company_lang_code);
            }
        }

        // Notify order department
        if ($notify_department == true) {
            // Translate descriptions to admin language
            fn_translate_products($order_info['products'], '', Registry::get('settings.Appearance.backend_default_language'), true);
            $payment_method = array();
            if (!empty($order_info['payment_method']['payment_id'])) {
                $payment_method = fn_get_payment_data($order_info['payment_method']['payment_id'], $order_info['order_id'], Registry::get('settings.Appearance.backend_default_language'));
            }

            fn_add_user_data_descriptions($order_info, Registry::get('settings.Appearance.backend_default_language'));
            Mailer::sendMail(array(
                'to' => 'default_company_orders_department',
                'from' => 'default_company_orders_department',
                'reply_to' => $order_info['email'],
                'data' => array(
                    'order_info' => $order_info,
                    'shipments' => $shipments,
                    'use_shipments' => $use_shipments,
                    'order_status' => fn_get_status_data($order_info['status'], STATUSES_ORDER, $order_info['order_id'], Registry::get('settings.Appearance.backend_default_language')),
                    'payment_method' => $payment_method,
                    'status_settings' => $status_settings,
                    'profile_fields' => fn_get_profile_fields('I', '', Registry::get('settings.Appearance.backend_default_language')),
                    'secondary_currency' => $secondary_currency
                ),
                'tpl' => 'orders/order_notification.tpl',
                'company_id' => $order_info['company_id'],
            ), 'A', Registry::get('settings.Appearance.backend_default_language'));
        }
    }

    if (!empty($edp_data) && !$notify_user) {
        // Send out download links for EDP with "Immediately" Activation mode

        // TRUE if the EDP download links e-mail has already been sent. Used to avoid sending duplicate e-mails.
        $download_email_sent = false;
        foreach ($edp_data as $edp_item) {
            foreach ($edp_item['files'] as $file) {
                if (!empty($file['activation']) && $file['activation'] == 'I' && !$download_email_sent) {

                    Mailer::sendMail(array(
                        'to' => $order_info['email'],
                        'from' => 'company_orders_department',
                        'data' => array(
                            'order_info' => $order_info,
                            'edp_data' => $edp_data,
                        ),
                        'tpl' => 'orders/edp_access.tpl',
                        'company_id' => $order_info['company_id'],
                    ), 'C', $order_info['lang_code']);

                    $download_email_sent = true;
                    break;
                }
            }
        }
    }

    fn_set_hook('order_notification', $order_info, $order_statuses, $force_notification);
}

/**
 *
 * @param int $payment_id payment ID
 * @param string $action action
 * @return array (boolean, string)
 */
function fn_check_processor_script($payment_id, $additional_params = false)
{

    if ($additional_params) {
        if (!empty($_REQUEST['skip_payment']) && AREA == 'C') {
            return array(false, '');
        }
    }

    $payment = fn_get_payment_method_data((int) $payment_id);

    if (!empty($payment['processor_id'])) {
        $processor_data = fn_get_processor_data($payment['payment_id']);
        if (!empty($processor_data['processor_script']) && file_exists(Registry::get('config.dir.payments') . $processor_data['processor_script'])) {
            return array(true, $processor_data);
        }
    }

    return array(false, '');
}

/**
 * Check if store can use processor script
 *
 * @param string $processor name of processor script
 * @param string $area current working area
 * @return bool
 */
function fn_check_prosessor_status($processor, $area = AREA)
{
    $is_active = false;

    $processor = fn_get_processor_data_by_name($processor . '.php');
    if (!empty($processor)) {
        $payments = fn_get_payment_by_processor($processor['processor_id']);

        if (!empty($payments)) {
            foreach ($payments as $payment) {
                if ($payment['status'] == 'A' || $area == 'A') { // admin can use disable payments
                    $is_active = true;
                }
            }
        }
    }

    return $is_active;
}

function fn_add_product_options_files($product_data, &$cart, &$auth, $update = false, $location = 'cart')
{
    // Check if products have cusom images
    if (!$update) {
        $uploaded_data = fn_filter_uploaded_data('product_data');
    } else {
        $uploaded_data = fn_filter_uploaded_data('cart_products');
    }

    // Check for the already uploaded files
    if (!empty($product_data['custom_files']['uploaded'])) {
        foreach ($product_data['custom_files']['uploaded'] as $file_id => $file_data) {
            if (Storage::instance('images')->isExist('sess_data/' . fn_basename($file_data['path']))) {
                $id = $file_data['product_id'] . $file_data['option_id'] . $file_id;
                $uploaded_data[$id] = array(
                    'name' => $file_data['name'],
                    'path' => 'sess_data/' . fn_basename($file_data['path']),
                );

                $product_data['custom_files'][$id] = $file_data['product_id'] . '_' . $file_data['option_id'];
            }
        }
    }

    if (!empty($uploaded_data) && !empty($product_data['custom_files'])) {
        $files_data = array();

        foreach ($uploaded_data as $key => $file) {
            $file_info = fn_pathinfo($file['name']);
            $file['extension'] = empty($file_info['extension']) ? '' : $file_info['extension'];
            $file['is_image'] = fn_get_image_extension($file['type']);

            $_data = explode('_', $product_data['custom_files'][$key]);
            $product_id = empty($_data[0]) ? 0 : $_data[0];
            $option_id = empty($_data[1]) ? 0 : $_data[1];
            $file_id = str_replace($option_id . $product_id, '', $key);

            if (empty($file_id)) {
                $files_data[$product_id][$option_id][] = $file;
            } else {
                $files_data[$product_id][$option_id][$file_id] = $file;
            }
        }
    }

    unset($product_data['custom_files']);

    foreach ($product_data as $key => $data) {
        $product_id = (!empty($data['product_id'])) ? $data['product_id'] : $key;

        // Check if product has cusom images
        if ($update || isset($files_data[$key])) {
            $hash = $key;
        } else {
            $hash = $product_id;
        }

        $_options = fn_get_product_options($product_id);
        if (!empty($files_data[$hash]) && is_array($files_data[$hash])) {

            foreach ($files_data[$hash] as $option_id => $files) {
                foreach ($files as $file_id => $file) {
                    // Check for the allowed extensions
                    if (!empty($_options[$option_id]['allowed_extensions'])) {
                        if ((empty($file['extension']) && !empty($_options[$option_id]['allowed_extensions'])) || !preg_match("/\b" . $file['extension'] . "\b/i", $_options[$option_id]['allowed_extensions'])) {
                            fn_set_notification('E', __('error'), $file['name'] . ': ' . __('text_forbidden_uploaded_file_extension', array(
                                '[ext]' => $file['extension'],
                                '[exts]' => $_options[$option_id]['allowed_extensions']
                            )));
                            unset($files_data[$hash][$option_id][$file_id]);
                            continue;
                        }
                    }

                    // Check for the max file size

                    if (!empty($_options[$option_id]['max_file_size'])) {
                        if (empty($file['size'])) {
                            $file['size'] = filesize($file['path']);
                        }

                        if ($file['size'] > $_options[$option_id]['max_file_size'] * 1024) {
                            fn_set_notification('E', __('error'), $file['name'] . ': ' . __('text_forbidden_uploaded_file_size', array(
                                '[size]' => $_options[$option_id]['max_file_size'] . ' kb'
                            )));
                            unset($files_data[$hash][$option_id][$file_id]);
                            continue;
                        }
                    }

                    $_file_path = 'sess_data/file_' . uniqid(TIME);

                    list(, $_file_path) = Storage::instance('custom_files')->put($_file_path, array(
                        'file' => $file['path']
                    ));

                    if (!$_file_path) {
                        fn_set_notification('E', __('error'), __('text_cannot_create_file', array(
                            '[file]' => $file['name']
                        )));

                        unset($files_data[$hash][$option_id][$file_id]);
                        continue;
                    }

                    $file['path'] = $_file_path;
                    $file['file'] = fn_basename($file['path']);

                    if ($file['is_image']) {
                        $file['thumbnail'] = 'image.custom_image?image=' . $file['file'] . '&type=T';
                        $file['detailed'] = 'image.custom_image?image=' . $file['file'] . '&type=D';
                    }

                    $file['location'] = $location;

                    if ($update) {
                        $cart['products'][$key]['extra']['custom_files'][$option_id][] = $file;
                    } else {
                        $data['extra']['custom_files'][$option_id][] = $file;

                    }
                }

                if ($update) {
                    if (!empty($cart['products'][$key]['product_options'][$option_id])) {
                        $cart['products'][$key]['product_options'][$option_id] = md5(serialize($cart['products'][$key]['extra']['custom_files'][$option_id]));
                    }
                } else {
                    if (!empty($data['extra']['custom_files'][$option_id])) {
                        $data['product_options'][$option_id] = md5(serialize($data['extra']['custom_files'][$option_id]));
                    }
                }
            }

            // Check the required options
            if (empty($data['extra']['parent'])) {
                foreach ($_options as $option) {
                    if ($option['option_type'] == 'F' && $option['required'] == 'Y' && !$update) {
                        if (empty($data['product_options'][$option['option_id']])) {
                            fn_set_notification('E', __('error'), __('product_cannot_be_added'));

                            unset($product_data[$key]);

                            return array($product_data, $cart);
                        }
                    }
                }
            }

        } else {
            if (empty($data['extra']['parent'])) {
                foreach ($_options as $option) {
                    if ($option['option_type'] == 'F' && $option['required'] == 'Y' && empty($cart['products'][$hash]['extra']['custom_files'][$option['option_id']]) && empty($data['extra']['custom_files'][$option['option_id']])) {
                        fn_set_notification('E', __('error'), __('product_cannot_be_added'));

                        unset($product_data[$key]);

                        return array($product_data, $cart);
                    }
                }
            }
        }

        if ($update) {
            foreach ($_options as $option) {
                if ($option['option_type'] == 'F' && empty($cart['products'][$key]['extra']['custom_files'][$option['option_id']])) {
                    unset($cart['products'][$key]['extra']['custom_files'][$option['option_id']]);
                    unset($cart['products'][$key]['product_options'][$option['option_id']]);
                    unset($data['product_options'][$option['option_id']]);
                }
            }
        }

        if (isset($cart['products'][$key]['extra']['custom_files'])) {
            foreach ($cart['products'][$key]['extra']['custom_files'] as $option_id => $files) {
                foreach ($files as $file) {
                    $data['extra']['custom_files'][$option_id][] = $file;
                }

                $data['product_options'][$option_id] = md5(serialize($files));
            }
        }

        $product_data[$key] = $data;
    }

    return array($product_data, $cart);
}

/**
 *   save stored taxes for products
 * @param array $cart cart
 * @param int $update_id   key of $cart['products'] to be updated
 * @param int $new_id  new key
 * @param bool $consider_existing  whether consider or not existing key
 */
function fn_update_stored_cart_taxes(&$cart, $update_id, $new_id, $consider_existing = false)
{
    if (!empty($cart['taxes']) && is_array($cart['taxes'])) {
        foreach ($cart['taxes'] as $t_id => $s_tax) {
            if (!empty($s_tax['applies']) && is_array($s_tax['applies'])) {
                $compare_key = 'P_' . $update_id;
                $new_key = 'P_' . $new_id;
                if (array_key_exists($compare_key, $s_tax['applies'])) {
                    $cart['taxes'][$t_id]['applies'][$new_key] = (isset($s_tax['applies'][$new_key]) && $consider_existing ? $s_tax['applies'][$new_key] : 0) + $s_tax['applies'][$compare_key];
                    unset($cart['taxes'][$t_id]['applies'][$compare_key]);
                }
            }
        }
    }
}

function fn_define_original_amount($product_id, $cart_id, &$product, $prev_product)
{
    if (!empty($prev_product['original_product_data']) && !empty($prev_product['original_product_data']['amount'])) {
        $tracking = db_get_field("SELECT tracking FROM ?:products WHERE product_id = ?i", $product_id);
        if ($tracking != ProductTracking::TRACK_WITH_OPTIONS ||
            $tracking == ProductTracking::TRACK_WITH_OPTIONS &&
            $prev_product['original_product_data']['cart_id'] == $cart_id
        ) {
            $product['original_amount'] = $prev_product['original_product_data']['amount'];
        }
        $product['original_product_data'] = $prev_product['original_product_data'];
    } elseif (!empty($prev_product['original_amount'])) {
        $product['original_amount'] = $prev_product['original_amount'];
    }
}

function fn_get_shipments_info($params, $items_per_page = 0)
{
    // Init view params
    $params = LastView::instance()->update('shipments', $params);

    // Set default values to input params
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    $params = array_merge($default_params, $params);

    $fields_list = array(
        '?:shipments.shipment_id',
        '?:shipments.timestamp AS shipment_timestamp',
        '?:shipments.comments',
        '?:shipment_items.order_id',
        '?:orders.timestamp AS order_timestamp',
        '?:orders.s_firstname',
        '?:orders.s_lastname',
        '?:orders.user_id',
    );

    $joins = array(
        'LEFT JOIN ?:shipment_items ON (?:shipments.shipment_id = ?:shipment_items.shipment_id)',
        'LEFT JOIN ?:orders ON (?:shipment_items.order_id = ?:orders.order_id)',
    );

    $condition = '';
    if (Registry::get('runtime.company_id')) {
        $joins[] = 'LEFT JOIN ?:companies ON (?:companies.company_id = ?:orders.company_id)';
        $condition = db_quote(' AND ?:companies.company_id = ?i', Registry::get('runtime.company_id'));
    }

    $group = array(
        '?:shipments.shipment_id',
    );

    // Define sort fields
    $sortings = array (
        'id' => "?:shipments.shipment_id",
        'order_id' => "?:orders.order_id",
        'shipment_date' => "?:shipments.timestamp",
        'order_date' => "?:orders.timestamp",
        'customer' => array("?:orders.s_lastname", "?:orders.s_firstname"),
    );

    $sorting = db_sort($params, $sortings, 'id', 'desc');

    if (isset($params['advanced_info']) && $params['advanced_info']) {
        $fields_list[] = '?:shipments.shipping_id';
        $fields_list[] = '?:shipping_descriptions.shipping AS shipping';
        $fields_list[] = '?:shipments.tracking_number';
        $fields_list[] = '?:shipments.carrier';

        $joins[] = ' LEFT JOIN ?:shippings ON (?:shipments.shipping_id = ?:shippings.shipping_id)';
        $joins[] = db_quote(' LEFT JOIN ?:shipping_descriptions ON (?:shippings.shipping_id = ?:shipping_descriptions.shipping_id AND ?:shipping_descriptions.lang_code = ?s)', DESCR_SL);
    }

    if (!empty($params['order_id'])) {
        $condition .= db_quote(' AND ?:shipment_items.order_id = ?i', $params['order_id']);
    }

    if (!empty($params['shipment_id'])) {
        $condition .= db_quote(' AND ?:shipments.shipment_id = ?i', $params['shipment_id']);
    }

    if (isset($params['cname']) && fn_string_not_empty($params['cname'])) {
        $arr = fn_explode(' ', $params['cname']);
        foreach ($arr as $k => $v) {
            if (!fn_string_not_empty($v)) {
                unset($arr[$k]);
            }
        }
        if (sizeof($arr) == 2) {
            $condition .= db_quote(" AND ?:orders.firstname LIKE ?l AND ?:orders.lastname LIKE ?l", "%".array_shift($arr)."%", "%".array_shift($arr)."%");
        } else {
            $condition .= db_quote(" AND (?:orders.firstname LIKE ?l OR ?:orders.lastname LIKE ?l)", "%".trim($params['cname'])."%", "%".trim($params['cname'])."%");
        }
    }

    if (!empty($params['p_ids']) || !empty($params['product_view_id'])) {
        $arr = (strpos($params['p_ids'], ',') !== false || !is_array($params['p_ids'])) ? explode(',', $params['p_ids']) : $params['p_ids'];

        if (empty($params['product_view_id'])) {
            $condition .= db_quote(" AND ?:shipment_items.product_id IN (?n)", $arr);
        } else {
            $condition .= db_quote(" AND ?:shipment_items.product_id IN (?n)", db_get_fields(fn_get_products(array('view_id' => $params['product_view_id'], 'get_query' => true)), ','));
        }

        $joins[] = "LEFT JOIN ?:order_details ON ?:order_details.order_id = ?:orders.order_id";
    }

    if (!empty($params['shipment_period']) && $params['shipment_period'] != 'A') {
        $params['time_from'] = $params['shipment_time_from'];
        $params['time_to'] = $params['shipment_time_to'];
        $params['period'] = $params['shipment_period'];

        list($params['shipment_time_from'], $params['shipment_time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:shipments.timestamp >= ?i AND ?:shipments.timestamp <= ?i)", $params['shipment_time_from'], $params['shipment_time_to']);
    }

    if (!empty($params['order_period']) && $params['order_period'] != 'A') {
        $params['time_from'] = $params['order_time_from'];
        $params['time_to'] = $params['order_time_to'];
        $params['period'] = $params['order_period'];

        list($params['order_time_from'], $params['order_time_to']) = fn_create_periods($params);

        $condition .= db_quote(" AND (?:orders.timestamp >= ?i AND ?:orders.timestamp <= ?i)", $params['order_time_from'], $params['order_time_to']);
    }

    fn_set_hook('get_shipments', $params, $fields_list, $joins, $condition, $group);

    $fields_list = implode(', ', $fields_list);
    $joins = implode(' ', $joins);
    $group = implode(', ', $group);

    if (!empty($group)) {
        $group = ' GROUP BY ' . $group;
    }

    $limit = '';
    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(DISTINCT(?:shipments.shipment_id)) FROM ?:shipments $joins WHERE 1 $condition");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $shipments = db_get_array("SELECT $fields_list FROM ?:shipments $joins WHERE 1 $condition $group $sorting $limit");

    if (isset($params['advanced_info']) && $params['advanced_info'] && !empty($shipments)) {
        $shipment = reset($shipments);
        $order_info = fn_get_order_info($shipment['order_id']);

        foreach ($shipments as $id => $shipment) {
            $items = db_get_array('SELECT item_id, amount FROM ?:shipment_items WHERE shipment_id = ?i', $shipment['shipment_id']);
            if (!empty($items)) {
                foreach ($items as $item) {
                    $shipments[$id]['products'][$item['item_id']] = $item['amount'];
                    if (!empty($order_info['products'][$item['item_id']]['extra']['group_key'])) {
                        $shipments[$id]['group_key'] = $order_info['products'][$item['item_id']]['extra']['group_key'];
                    } else {
                        $shipments[$id]['group_key'] = 0;
                    }
                }

            }
        }

        if (Settings::instance()->getValue('use_shipments', '', $order_info['company_id']) != 'Y') {
            foreach ($shipments as $id => $shipment) {
                $shipments[$id]['one_full'] = true;

                foreach ($order_info['products'] as $product_key => $product) {
                    $group_key = !empty($product['extra']['group_key']) ? $product['extra']['group_key'] : 0;
                    if ($shipment['group_key'] == $group_key) {
                        if (empty($shipment['products'][$product_key]) || $shipment['products'][$product_key] < $product['amount']) {
                            $shipments[$id]['one_full'] = false;
                            break;
                        }
                    }
                }
            }
        }
    }

    /**
     * Changes selected shipments
     *
     * @param array $shipments Array of shipments
     * @param array $params    Shipments search params
     */
    fn_set_hook('get_shipments_info_post', $shipments, $params);

    LastView::instance()->processResults('shipments_info', $shipments, $params);

    return array($shipments, $params);
}

/**
 * Verification that at least one product was chosen.
 *
 * @param array $products Array products data
 * @return bool true - if at least one product was chosen, else "false".
 */
function fn_check_shipped_products($products)
{
    $allow = true;
    $total_amount = 0;

    if (!empty($products) && is_array($products)) {
        foreach ($products as $key => $amount) {
            $total_amount += empty($amount) ? 0 : $amount;
        }

        if ($total_amount == 0) {
            $allow = false;
        }

    } else {
        $allow = false;
    }

    return $allow;
}

/**
 * Verification, that all products were delivered by the same shipment.
 *
 * @param array $shipments - shipments data.
 * @return bool true - if all products in the order were delivered by the same shipment
 */
function fn_one_full_shipped(&$shipments)
{
    $full_shipment = true;
    $sort_shipments = array();

    if (!empty($shipments) && is_array($shipments)) {
        foreach ($shipments as $shipment) {
            if (empty($shipment['one_full'])) {
                $full_shipment = false;
                break;
            }
            $sort_shipments[$shipment['group_key']] = $shipment;
        }
        if ($full_shipment) {
            $shipments = $sort_shipments;
        }
    }

    return $full_shipment;
}

/**
 * Create/update shipment
 *
 * @param array $shipment_data Array of shipment data.
 * @param int $shipment_id Shipment identifier
 * @param int $group_key Group number
 * @param bool $all_products
 * @param mixed $force_notification user notification flag (true/false), if not set, will be retrieved from status parameters
 * @return int $shipment_id
 */
function fn_update_shipment($shipment_data, $shipment_id = 0, $group_key = 0, $all_products = false, $force_notification = array())
{

    if (!empty($shipment_id)) {
        $arow = db_query("UPDATE ?:shipments SET tracking_number = ?s, carrier = ?s WHERE shipment_id = ?i", $shipment_data['tracking_number'], $shipment_data['carrier'], $shipment_id);
        if ($arow === false) {
            fn_set_notification('E', __('error'), __('object_not_found', array('[object]' => __('shipment'))),'','404');
            $shipment_id = false;
        }
    } else {

        if (empty($shipment_data['order_id']) || empty($shipment_data['shipping_id'])) {
            return false;
        }

        $order_info = fn_get_order_info($shipment_data['order_id'], false, true, true);
        $use_shipments = (Settings::instance()->getValue('use_shipments', '', $order_info['company_id']) == 'Y') ? true : false;

        if (!$use_shipments && empty($shipment_data['tracking_number']) && empty($shipment_data['tracking_number'])) {
            return false;
        }

        if (!$use_shipments && $all_products) {
            foreach ($order_info['product_groups'] as $group) {
                foreach ($group['products'] as $item_key => $product) {

                    if (!empty($product['extra']['group_key'])) {
                        if ($group_key == $product['extra']['group_key']) {
                            $shipment_data['products'][$item_key] = $product['amount'];
                        }
                    } elseif ($group_key == 0) {
                        $shipment_data['products'][$item_key] = $product['amount'];
                    }
                }
            }
        }

        if (!empty($shipment_data['products']) && fn_check_shipped_products($shipment_data['products'])) {

            fn_set_hook('create_shipment', $shipment_data, $order_info, $group_key, $all_products);

            foreach ($shipment_data['products'] as $key => $amount) {
                if (isset($order_info['products'][$key])) {
                    $amount = intval($amount);

                    if ($amount > ($order_info['products'][$key]['amount'] - $order_info['products'][$key]['shipped_amount'])) {
                        $shipment_data['products'][$key] = $order_info['products'][$key]['amount'] - $order_info['products'][$key]['shipped_amount'];
                    }
                }
            }

            if (fn_check_shipped_products($shipment_data['products'])) {

                $shipment_data['timestamp'] = time();
                $shipment_id = db_query("INSERT INTO ?:shipments ?e", $shipment_data);

                foreach ($shipment_data['products'] as $key => $amount) {

                    if ($amount == 0) {
                        continue;
                    }

                    $_data = array(
                        'item_id' => $key,
                        'shipment_id' => $shipment_id,
                        'order_id' => $shipment_data['order_id'],
                        'product_id' => $order_info['products'][$key]['product_id'],
                        'amount' => $amount,
                    );

                    db_query("INSERT INTO ?:shipment_items ?e", $_data);
                }

                if (!empty($shipment_data['order_status'])) {
                    fn_change_order_status($shipment_data['order_id'], $shipment_data['order_status']);
                }

                /**
                 * Called after new shipment creation.
                 *
                 * @param array $shipment_data Array of shipment data.
                 * @param array $order_info Shipment order info
                 * @param int $group_key Group number
                 * @param bool $all_products
                 * @param int $shipment_id Created shipment identifier
                 */
                fn_set_hook('create_shipment_post', $shipment_data, $order_info, $group_key, $all_products, $shipment_id);

                if (!empty($force_notification['C'])) {
                    $shipment = array(
                        'shipment_id' => $shipment_id,
                        'timestamp' => $shipment_data['timestamp'],
                        'shipping' => db_get_field('SELECT shipping FROM ?:shipping_descriptions WHERE shipping_id = ?i AND lang_code = ?s', $shipment_data['shipping_id'], $order_info['lang_code']),
                        'tracking_number' => $shipment_data['tracking_number'],
                        'carrier' => $shipment_data['carrier'],
                        'comments' => $shipment_data['comments'],
                        'items' => $shipment_data['products'],
                    );

                    Mailer::sendMail(array(
                        'to' => $order_info['email'],
                        'from' => 'company_orders_department',
                        'data' => array(
                            'shipment' => $shipment,
                            'order_info' => $order_info,
                        ),
                        'tpl' => 'shipments/shipment_products.tpl',
                        'company_id' => $order_info['company_id'],
                    ), 'C', $order_info['lang_code']);

                }

                fn_set_notification('N', __('notice'), __('shipment_has_been_created'));
            }

        } else {
            fn_set_notification('E', __('error'), __('products_for_shipment_not_selected'));
        }

    }

    return $shipment_id;
}

function fn_delete_shipments($shipment_ids)
{
    $result = false;
    if (!empty($shipment_ids)) {
        $result = db_query('DELETE FROM ?:shipments WHERE shipment_id IN (?a)', $shipment_ids);
        db_query('DELETE FROM ?:shipment_items WHERE shipment_id IN (?a)', $shipment_ids);
    }

    /**
     * Called after shipments deletion
     *
     * @param array $shipment_ids Identifiers of deleted shipments
     * @param int   $result       Number of affected by deletion database rows
     */
    fn_set_hook('delete_shipments', $shipment_ids, $result);

    return $result;
}

/**
 * Deletes shipping method by identifier
 *
 * @param int $shipping_id Shipping identifier
 * @return bool Always true
 */
function fn_delete_shipping($shipping_id)
{
    db_query("DELETE FROM ?:shipping_rates WHERE shipping_id = ?i", $shipping_id);
    db_query("DELETE FROM ?:shipping_descriptions WHERE shipping_id = ?i", $shipping_id);
    $result = db_query("DELETE FROM ?:shippings WHERE shipping_id = ?i", $shipping_id);

    fn_set_hook('delete_shipping', $shipping_id, $result);

    return $result;
}

function fn_purge_undeliverable_products(&$cart)
{
    foreach ((array) $cart['products'] as $k => $v) {
        if (isset($v['shipping_failed']) && $v['shipping_failed']) {
            unset($cart['products'][$k]);
        }
    }
}

function fn_apply_stored_shipping_rates(&$cart, $order_id = 0)
{
    if (!empty($cart['stored_shipping'])) {
        $total_cost = 0;
        foreach ($cart['product_groups'] as $group_key => $group) {
            foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                if (isset($cart['stored_shipping'][$group_key][$shipping_key])) {
                    if (!empty($cart['free_shipping']) && in_array($shipping['shipping_id'], $cart['free_shipping'])) {
                        if (!empty($cart['stored_shipping'][$group_key][$shipping_key])) {
                            // save original value
                            $cart['original_stored_shipping'][$group_key][$shipping_key] = $cart['stored_shipping'][$group_key][$shipping_key];
                            // apply free shipping
                            $cart['stored_shipping'][$group_key][$shipping_key] = 0;
                        } else {
                            // save calulated rates as orignal: shipping is zero due to free shipping
                            $cart['original_stored_shipping'][$group_key][$shipping_key] = $shipping['rate'];
                        }
                    } elseif (empty($cart['stored_shipping'][$group_key][$shipping_key]) && isset($cart['original_stored_shipping'][$group_key][$shipping_key])) {
                        // free shiping was disabled - restore previous price
                        $cart['stored_shipping'][$group_key][$shipping_key] = !empty($cart['original_stored_shipping'][$group_key][$shipping_key]) ? $cart['original_stored_shipping'][$group_key][$shipping_key] : $shipping['rate'];
                        unset($cart['original_stored_shipping'][$group_key][$shipping_key]);
                    }

                    $piece = fn_format_price($cart['stored_shipping'][$group_key][$shipping_key]);
                    $cart['product_groups'][$group_key]['chosen_shippings'][$shipping_key]['rate'] = $piece;
                    $cart['product_groups'][$group_key]['shippings'][$shipping['shipping_id']]['rate'] = $piece;
                    $cart['product_groups'][$group_key]['chosen_shippings'][$shipping_key]['stored_shipping'] = true;
                    $cart['product_groups'][$group_key]['shippings'][$shipping['shipping_id']]['stored_shipping'] = true;
                    $shipping['rate'] = $piece;
                    $total_cost += $piece;
                } else {
                    if (!empty($shipping['rate'])) {
                        $total_cost += $shipping['rate'];
                    }
                }
            }
        }
        if (!empty($order_id)) {
            db_query("UPDATE ?:orders SET shipping_cost = ?i WHERE order_id = ?i", $total_cost, $order_id);
        }
        $cart['shipping_cost'] = $total_cost;
    }
}

function fn_checkout_update_shipping(&$cart, $shipping_ids)
{

    $cart['chosen_shipping'] = $shipping_ids;

    return true;
}

/**
 * Applies surcharge of selected payment to cart total
 *
 * @param array $cart Array of the cart contents and user information necessary for purchase
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @param string $lang_code 2-letter language code (e.g. 'en', 'ru', etc.)
 * @return bool Always true
 */
function fn_update_payment_surcharge(&$cart, $auth, $lang_code = CART_LANGUAGE)
{
    $cart['payment_surcharge'] = 0;
    if (!empty($cart['payment_id'])) {
        $_data = db_get_row("SELECT a_surcharge, p_surcharge FROM ?:payments WHERE payment_id = ?i", $cart['payment_id']);

        if (!empty($_data)) {
            if (floatval($_data['a_surcharge'])) {
                $cart['payment_surcharge'] += $_data['a_surcharge'];
            }
            if (floatval($_data['p_surcharge'])) {
                $cart['payment_surcharge'] += fn_format_price($cart['total'] * $_data['p_surcharge'] / 100);
            }
        }
    }

    if (!empty($cart['payment_surcharge'])) {
        $cart['payment_surcharge_title'] = db_get_field("SELECT surcharge_title FROM ?:payment_descriptions WHERE payment_id = ?i AND lang_code = ?s", $cart['payment_id'], $lang_code);

        // apply tax
        fn_calculate_payment_taxes($cart, $auth);
    }

    return true;
}

function fn_get_cart_product_icon($product_id, $product_data = array())
{
    if (!empty($product_data['product_options'])) {
        $combination_hash = fn_generate_cart_id($product_id, array('product_options' => $product_data['product_options']), true);
        $image = fn_get_image_pairs($combination_hash, 'product_option', 'M', true, true);
        if (!empty($image)) {
            return $image;
        }
    }

    return fn_get_image_pairs($product_id, 'product', 'M', true, true);
}

function fn_prepare_checkout_payment_methods(&$cart, &$auth, $lang_code = CART_LANGUAGE)
{
    static $payment_methods, $payment_groups;

    //Get payment methods
    if (empty($payment_methods)) {
        $payment_methods = fn_get_payment_methods($auth);
    }

    // Check if payment method has surcharge rates
    foreach ($payment_methods as $k => $v) {

        if ($payment_methods[$k]['processor_type'] == 'C') {
            continue;
        }

        $payment_methods[$k]['surcharge_value'] = 0;
        if (floatval($v['a_surcharge'])) {
            $payment_methods[$k]['surcharge_value'] += $v['a_surcharge'];
        }
        if (floatval($v['p_surcharge']) && !empty($cart['total'])) {
            $payment_methods[$k]['surcharge_value'] += fn_format_price($cart['total'] * $v['p_surcharge'] / 100);
        }

        $payment_methods[$k]['image'] = fn_get_image_pairs($v['payment_id'], 'payment', 'M', true, true, $lang_code);

        $payment_groups[$v['payment_category']][$k] = $payment_methods[$k];
    }

    if (!empty($payment_groups)) {
        ksort($payment_groups);
    }

    fn_set_hook('prepare_checkout_payment_methods', $cart, $auth, $payment_groups);

    return $payment_groups;
}

function fn_print_order_invoices($order_ids, $pdf = false, $area = AREA, $lang_code = CART_LANGUAGE)
{
    $view = Registry::get('view');
    $html = array();

    $view->assign('order_status_descr', fn_get_simple_statuses(STATUSES_ORDER, true, true));
    $view->assign('profile_fields', fn_get_profile_fields('I'));

    if (!is_array($order_ids)) {
        $order_ids = array($order_ids);
    }

    foreach ($order_ids as $order_id) {
        $order_info = fn_get_order_info($order_id, false, true, false, true);

        if (empty($order_info)) {
            continue;
        }

        if (fn_allowed_for('MULTIVENDOR')) {
            $view->assign('take_surcharge_from_vendor', fn_take_payment_surcharge_from_vendor($order_info['products']));
        }

        list($shipments) = fn_get_shipments_info(array('order_id' => $order_info['order_id'], 'advanced_info' => true));
        $use_shipments = !fn_one_full_shipped($shipments);

        $view->assign('order_info', $order_info);
        $view->assign('shipments', $shipments);
        $view->assign('use_shipments', $use_shipments);
        $view->assign('payment_method', fn_get_payment_data((!empty($order_info['payment_method']['payment_id']) ? $order_info['payment_method']['payment_id'] : 0), $order_info['order_id'], $lang_code));
        $view->assign('order_status', fn_get_status_data($order_info['status'], STATUSES_ORDER, $order_info['order_id'], $lang_code, $order_info['company_id']));
        $view->assign('status_settings', fn_get_status_params($order_info['status']));

        $view->assign('company_data', fn_get_company_placement_info($order_info['company_id'], $lang_code));

        if ($pdf == true) {
            fn_disable_live_editor_mode();
            $html[] = $view->displayMail('orders/print_invoice.tpl', false, $area, $order_info['company_id'], $lang_code);
        } else {
            $view->displayMail('orders/print_invoice.tpl', true, $area, $order_info['company_id'], $lang_code);
            if ($order_id != end($order_ids)) {
                echo("<div style='page-break-before: always;'>&nbsp;</div>");
            }
        }
    }

    if ($pdf == true) {
        Pdf::render($html, __('invoices') . '-' . implode('-', $order_ids));
    }

    return true;
}

function fn_update_order_customer_info($data, $order_id)
{
    $order_info = fn_get_order_info($order_id);
    $new_order_info = array();
    $need_update = false;

    if (empty($order_info)) {
        return false;
    }

    foreach ($data as $k => $v) {
        if ($data[$k] != $order_info[$k]) {
            $need_update = true;
            $new_order_info[$k] = $v;
        }
    }

    if ($need_update) {
        db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", $new_order_info, $order_id);
    }

    return true;
}

/**
 * Returns all available shippings for root/vendor company
 *
 * @param int $company_id Company identifier
 * @return array List of shippings
 */
function fn_get_available_shippings($company_id = null)
{
    $condition = '';
    if ($company_id != null && !fn_allowed_for('ULTIMATE')) {
        $company_shippings = db_get_field('SELECT shippings FROM ?:companies WHERE company_id = ?i', $company_id);
        $condition .= db_quote('AND (a.company_id = ?i ', $company_id);

        if (!empty($company_shippings)) {
            $condition .= db_quote(' OR a.shipping_id IN (?n)', explode(',', $company_shippings));
        }

        $condition .= ')';
    }

    $res = db_get_hash_array("SELECT a.shipping_id, a.company_id, a.min_weight, a.max_weight, a.position, a.status, a.tax_ids, b.shipping, b.delivery_time, a.usergroup_ids, c.company as company_name FROM ?:shippings as a LEFT JOIN ?:shipping_descriptions as b ON a.shipping_id = b.shipping_id AND b.lang_code = ?s LEFT JOIN ?:companies c ON c.company_id = a.company_id WHERE 1 $condition ORDER BY a.position", 'shipping_id', DESCR_SL);

    return $res;
}

function fn_payment_url($method, $script)
{
    if (empty($method)) {
        return '';
    }

    $url = fn_url('', 'C', $method);
    if (strpos($url, Registry::get('config.customer_index')) !== false) {
        $url = dirname($url);
    }

    return rtrim($url, '/') . '/app/payments/' . $script;
}

/**
 * Gets URI for checkout
 *
 * @param string $url URN (Uniform Resource Name or Query String)
 * @param string $area Area
 * @return string URI
 */
function fn_checkout_url($url = '', $area = AREA)
{
    $protocol = Registry::get('settings.Security.secure_checkout') == 'Y' ? 'https' : 'http';

    return fn_url($url, $area, $protocol);
}

/**
 * Update cart products from passed products data
 *
 * @param array $cart Array of cart content and user information necessary for purchase
 * @param array $product_data Array of new products data
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return boolean Always true
 */
function fn_update_cart_products(&$cart, $product_data, $auth)
{
    /**
     * Actions before updating cart products
     *
     * @param array $cart         Array of cart content and user information necessary for purchase
     * @param array $product_data Array of new products data
     * @param array $auth         Array of user authentication data (e.g. uid, usergroup_ids, etc.)
     */
    fn_set_hook('update_cart_products_pre', $cart, $product_data, $auth);

    if (is_array($cart['products']) && !empty($product_data)) {

        list($product_data, $cart) = fn_add_product_options_files($product_data, $cart, $auth, true);
        unset($product_data['custom_files']);

        foreach ($product_data as $k => $v) {
            if (!isset($cart['products'][$k]['extra']['exclude_from_calculate'])) {
                if (empty($v['extra'])) {
                    $v['extra'] = array();
                }

                if ($v['price'] < 0) {
                    $v['price'] = 0;
                }

                unset($v['object_id']);

                $amount = fn_normalize_amount($v['amount']);
                $price = fn_get_product_price($v['product_id'], $amount, $auth);

                $v['extra'] = empty($cart['products'][$k]['extra']) ? array() : $cart['products'][$k]['extra'];
                $v['extra']['product_options'] = empty($v['product_options']) ? array() : $v['product_options'];
                $_id = fn_generate_cart_id($v['product_id'], $v['extra']);

                if (!isset($cart['products'][$_id])) { //if combination doesn't exist in the cart
                    $cart['products'][$_id] = $v;
                    $cart['products'][$_id]['company_id'] = !empty($cart['products'][$k]['company_id']) ? $cart['products'][$k]['company_id'] : 0;
                    $_product = $cart['products'][$k];

                    fn_define_original_amount($v['product_id'], $_id, $cart['products'][$_id], $_product);

                    fn_delete_cart_product($cart, $k);

                } elseif ($k != $_id) { // if the combination is exist but differs from the current
                    $amount += $cart['products'][$_id]['amount'];
                    fn_delete_cart_product($cart, $k);
                }

                if (empty($amount)) {
                    fn_delete_cart_product($cart, $_id);
                    continue;
                } else {
                    $_product_options = !empty($v['product_options']) ? $v['product_options'] : array();
                    $cart['products'][$_id]['amount'] = fn_check_amount_in_stock($v['product_id'], $amount, $_product_options, $_id, (!empty($cart['products'][$_id]['is_edp']) && $cart['products'][$_id]['is_edp'] == 'Y' ? 'Y' : 'N'), !empty($cart['products'][$_id]['original_amount']) ? $cart['products'][$_id]['original_amount'] : 0, $cart);

                    if ($cart['products'][$_id]['amount'] == false && !empty($_product)) {
                        $cart['products'][$_id] = $_product;
                        unset($_product);
                    }
                }

                if ($k != $_id) {
                    $cart['products'][$_id]['prev_cart_id'] = $k;

                    // save stored taxes for products
                    fn_update_stored_cart_taxes($cart, $k, $_id, true);

                } elseif (isset($cart['products'][$_id]['prev_cart_id'])) {
                    unset($cart['products'][$_id]['prev_cart_id']);
                }

                $cart['products'][$_id]['stored_price'] = !empty($v['stored_price']) ? $v['stored_price'] : 'N';
                if ($cart['products'][$_id]['stored_price'] == 'Y') {
                    $cart['products'][$_id]['price'] = $v['price'];
                }

                $cart['products'][$_id]['stored_discount'] = !empty($v['stored_discount']) ? $v['stored_discount'] : 'N';
                if ($cart['products'][$_id]['stored_discount'] == 'Y') {
                    $cart['products'][$_id]['discount'] = $v['discount'];
                }
            }
        }
    }

    /**
     * Additional cart products updates from passed params
     *
     * @param array $cart         Array of cart content and user information necessary for purchase
     * @param array $product_data Array of new products data
     * @param array $auth         Array of user authentication data (e.g. uid, usergroup_ids, etc.)
     */
    fn_set_hook('update_cart_products_post', $cart, $product_data, $auth);

    return true;
}

/**
 * Update cart products and etc. from passed params
 *
 * @param array $cart Array of cart content and user information necessary for purchase
 * @param array $new_cart_data Array of new data for products, totals, discounts and etc. update
 * @param array $auth Array of user authentication data (e.g. uid, usergroup_ids, etc.)
 * @return boolean Always true
 */
function fn_update_cart_by_data(&$cart, $new_cart_data, $auth)
{
    // Clean up saved shipping rates
    unset($_SESSION['shipping_rates']);

    // update products
    $product_data = !empty($new_cart_data['cart_products']) ? $new_cart_data['cart_products'] : array();
    fn_update_cart_products($cart, $product_data, $auth);

    // Update shipping cost
    $cart['stored_shipping'] = array();
    if (!empty($cart['product_groups'])) {
        foreach ($cart['product_groups'] as $group_key => $group) {
            if (!empty($group['chosen_shippings'])) {
                foreach ($group['chosen_shippings'] as $shipping_key => $shipping) {
                    if (!empty($new_cart_data['stored_shipping'][$group_key][$shipping_key]) && $new_cart_data['stored_shipping'][$group_key][$shipping_key] != 'N') {
                        $cart['stored_shipping'][$group_key][$shipping_key] = (float) $new_cart_data['stored_shipping_cost'][$group_key][$shipping_key];
                        $cart['product_groups'][$group_key]['chosen_shippings'][$shipping_key]['rate'] = $cart['stored_shipping'][$group_key][$shipping_key];
                    } else {
                        unset($cart['product_groups'][$group_key]['chosen_shippings'][$shipping_key]['stored_shippings']);
                        unset($cart['product_groups'][$group_key]['shippings'][$shipping['shipping_id']]['stored_shippings']);
                    }
                }
            }
        }
    }

    // Update taxes
    if (!empty($new_cart_data['taxes']) && @$new_cart_data['stored_taxes'] == 'Y') {
        foreach ($new_cart_data['taxes'] as $id => $rate) {
            $cart['taxes'][$id]['rate_value'] = $rate;
        }
    }

    $cart['stored_taxes'] = !empty($new_cart_data['stored_taxes']) ? $new_cart_data['stored_taxes'] : array();

    if (!empty($new_cart_data['stored_subtotal_discount']) && $new_cart_data['stored_subtotal_discount'] == 'Y') {
        $cart['stored_subtotal_discount'] = 'Y';
        $cart['subtotal_discount'] = $new_cart_data['subtotal_discount'];
    } else {
        unset($cart['stored_subtotal_discount']);
        $cart['subtotal_discount'] = !empty($cart['original_subtotal_discount']) ? $cart['original_subtotal_discount'] : 0;
    }

    // Apply coupon
    if (!empty($new_cart_data['coupon_code'])) {
        fn_trusted_vars('coupon_code');
        // That's why $cart->setPendingCoupon() is better
        $cart['pending_coupon'] = strtolower($new_cart_data['coupon_code']);
    }

    /**
     * Additional cart updates from passed params
     *
     * @param array $cart          Array of cart content and user information necessary for purchase
     * @param array $new_cart_data Array of new data for products, totals, discounts and etc. update
     * @param array $auth          Array of user authentication data (e.g. uid, usergroup_ids, etc.)
     */
    fn_set_hook('update_cart_by_data_post', $cart, $new_cart_data, $auth);

    return true;
}

/**
 * Enables checkout mode
 */
function fn_enable_checkout_mode()
{
    Registry::set('runtime.checkout', true);
}

/**
 * Gets credit card type by its number
 * @param integer $number credir card number
 * @return mixed card type or false on failure
 */
function fn_get_credit_card_type($number)
{
    $card_types = array(
        'amex' => array(
            'pattern' => "/^3[47]/",
            'valid_length' => array(15)
        ),
        'diners_club_carte_blanche' => array(
            'pattern' => "/^30[0-5]/",
            'valid_length' => array(14)
        ),
        'diners_club_international' => array(
            'pattern' => "/^36/",
            'valid_length' => array(14)
        ),
        'jcb' => array(
            'pattern' => "/^35(2[89]|[3-8][0-9])/",
            'valid_length' => array(16)
        ),
        'laser' => array(
            'pattern' => "/^(6304|670[69]|6771)/",
            'valid_length' => array(16, 17, 18, 19)
        ),
        'visa_debit' => array(
            'pattern' => "/^(456735|400626|40854749|40940002|41228586|41373337|41378788|418760|41917679|419772|420672|42159294|422793|423769|431072|444001|44400508|44620011|44621354|44625772|44627483|446286|446294|446200|450875|45397879|454313|45443235|454742|45672545|46583079|46590150|47511059|47571059|47622069|47634089|48440910|484427|49096079|49218182|400115|40083739|41292123|417935|419740|419741|41977376|424519|4249623|444000|48440608|48441126|48442855|491880)/",
            'valid_length' => array(16)
        ),
        'visa_electron' => array(
            'pattern' => "/^(4026|417500|4508|4844|491(3|7))/",
            'valid_length' => array(16)
        ),
        'visa' => array(
            'pattern' => "/^4/",
            'valid_length' => array(16)
        ),
        'mastercard_debit' => array(
            'pattern' => "/^(516730|516979|517000|517049|535110|535309|535420|535819|537210|537609|557347|557496|557498|557547)/",
            'valid_length' => array(16)
        ),
        'mastercard' => array(
            'pattern' => "/^5[1-5]/",
            'valid_length' => array(16)
        ),
        'maestro' => array(
            'pattern' => "/^(5018|5020|5038|6304|6759|676[1-3])/",
            'valid_length' => array(12, 13, 14, 15, 16, 17, 18, 19)
        ),
        'discover' => array(
            'pattern' => "/^(6011|622(12[6-9]|1[3-9][0-9]|[2-8][0-9]{2}|9[0-1][0-9]|92[0-5]|64[4-9])|65)/",
            'valid_length' => array(16)
        )
    );

    $number_length = strlen($number);
    foreach ($card_types as $card => $tests) {
        if (preg_match($tests['pattern'], $number, $m) && in_array($number_length, $tests['valid_length'])) {
            return $card;
        }
    }

    return false;
}

/**
 * Gets credit card type by its number and maps it to payment-specific name
 * @param string $card_number card number
 * @param array $cards_map key->value array of card types map
 * @return string payment-specific card type or empty string for unknown card
 */
function fn_get_payment_card($card_number, $cards_map)
{
    $card_type = fn_get_credit_card_type($card_number);

    return isset($cards_map[$card_type]) ? $cards_map[$card_type] : '';
}

/**
 * Filters credit card data, removes cleaned up fields
 *
 * @param array $payment_info Credit card data
 * @param string $area Current working area
 * @return array Filtered card data
 */
function fn_filter_card_data($payment_info, $area = AREA)
{
    $checked = array();
    if ($area == 'A' && !empty($payment_info)) {
        if (!empty($payment_info['card_number']) && strpos($payment_info['card_number'], 'X') === false) {
            $checked = $payment_info;
        } else {
            $checked = array (
                'cardholder_name' => !empty($payment_info['cardholder_name']) ? $payment_info['cardholder_name'] : ''
            );
        }
    }

    return $checked;
}

/**
 * Create HTML form with payment data and submit it.
 *
 * @param string $submit_url URL to send payment data
 * @param array $data Payment data to be submitted
 * @param string $payment_name Payment name to be displayed duron form submitting
 * @param boolean $exclude_empty_values Define that payment data elements with empty values should be excluded from payment form
 * @param string $method form submit method (get/post)
 */
function fn_create_payment_form($submit_url, $data, $payment_name = '', $exclude_empty_values = true, $method = 'post')
{
    Embedded::leave();

    if (Embedded::isEnabled()) {
        list($submit_url, $data, $method, $payment_name) = Embedded::processPaymentForm($submit_url, $data, $payment_name, $exclude_empty_values, $method);
    }

    if (Embedded::isEnabled()) {
        list($submit_url, $data, $method, $payment_name) = Embedded::processPaymentForm($submit_url, $data, $payment_name, $exclude_empty_values, $method);
    }

    $parsed_url = parse_url($submit_url);
    if (!empty($parsed_url['query'])) {
        $_data = array();
        parse_str($parsed_url['query'], $_data);
        $data = fn_array_merge($data, $_data);
        $submit_url = $parsed_url['scheme'] . '://' . $parsed_url['host'] . $parsed_url['path'];
    }
    echo <<<EOT
        <form method="$method" action="$submit_url" name="process">
EOT;

    foreach ($data as $name => $value) {
        if (!empty($value) || (empty($value) && $exclude_empty_values === false)) {
            echo('<input type="hidden" name="' . htmlentities($name) . '" value="' . htmlentities($value) . '" />' . "\n");
        }
    }

    if (!empty($payment_name)) {
        echo(__('text_cc_processor_connection', array(
            '[processor]' => $payment_name
        )));
    }

    echo <<<EOT
        </form>
        <script type="text/javascript">
            window.onload = function(){
               document.process.submit();
            };
        </script>
        </body>
    </html>
EOT;
    exit;
}

function fn_checkout_summary(&$cart)
{
    if (fn_cart_is_empty($cart) == true) {
        return false;
    }

    fn_set_hook('checkout_summary', $cart);

    //Get payment methods
    $payment_data = fn_get_payment_method_data($cart['payment_id']);

    Registry::get('view')->assign('payment_method', $payment_data);

    // Downlodable files agreements
    $agreements = array();
    if (!empty($cart['products'])) {
        foreach ($cart['products'] as $item) {
            if ($item['is_edp'] == 'Y') {
                if ($_agreement = fn_get_edp_agreements($item['product_id'], true)) {
                    $agreements[$item['product_id']] = $_agreement;
                }
            }
        }
    }

    if (!empty($agreements)) {
        Registry::get('view')->assign('cart_agreements', $agreements);
    }
}

function fn_need_shipping_recalculation(&$cart)
{
    if ($cart['recalculate'] == true) {
        return true;
    }

    $recalculate_shipping = false;
    if (!empty($_SESSION['customer_loc'])) {
        foreach ($_SESSION['customer_loc'] as $k => $v) {
            if (!empty($v) && empty($cart['user_data'][$k])) {
                $recalculate_shipping = true;
                break;
            }
        }
    }

    if ($recalculate_shipping == false && !empty($_SESSION['checkout_mode']) && ($_SESSION['checkout_mode'] == 'cart' && Registry::get('runtime.mode') == 'checkout')) {
        $recalculate_shipping = true;
    }

    unset($_SESSION['customer_loc']);

    return $recalculate_shipping;

}

function fn_get_checkout_payment_buttons(&$cart, &$cart_products, &$auth)
{
    $checkout_buttons = array();

    if (Registry::get('settings.General.disable_anonymous_checkout') == 'Y' && empty($auth['user_id'])) {
        $url = fn_url("auth.login_form?return_url=" . urlencode(fn_url('checkout.cart')));

        return array(__('payments.signin_to_checkout', array("[url]" => $url)));
    }

    $ug_condition = 'AND (' . fn_find_array_in_set($auth['usergroup_ids'], 'b.usergroup_ids', true) . ')';
    $checkout_payments = db_get_fields("SELECT b.payment_id FROM ?:payment_processors as a LEFT JOIN ?:payments as b ON a.processor_id = b.processor_id WHERE a.type != 'P' AND b.status = 'A' ?p", $ug_condition);

    if (!empty($checkout_payments)) {
        foreach ($checkout_payments as $_payment_id) {
            $processor_data = fn_get_processor_data($_payment_id);
            if (!empty($processor_data['processor_script']) && file_exists(Registry::get('config.dir.payments') . $processor_data['processor_script'])) {
                include(Registry::get('config.dir.payments') . $processor_data['processor_script']);
            }
        }
    }

    return $checkout_buttons;
}

function fn_get_default_credit_card(&$cart, $user_data)
{
    if (!empty($user_data['credit_cards'])) {
        $cards = unserialize(fn_decrypt_text($user_data['credit_cards']));
        foreach ((array) $cards as $cc) {
            if ($cc['default']) {
                $cart['payment_info'] = $cc;
                break;
            }
        }
    } elseif (isset($cart['payment_info'])) {
        unset($cart['payment_info']);
    }
}

function fn_get_shipping_hash($product_groups)
{
    // If shipping methods changed and shipping step is completed, display notification
    $shipping_hash = '';

    if (!empty($product_groups)) {
        $rates = array();
        foreach ($product_groups as $key_group => $group) {
            $rates[$key_group] = array();
            foreach ($group['shippings'] as $key_shipping => $shipping) {
                $rates[$key_group][$key_shipping] = $shipping['rate'];
            }
            ksort($rates[$key_group]);
        }
        ksort($rates);
        $shipping_hash = md5(fn_recursive_makehash($rates));
    }

    return $shipping_hash;
}
