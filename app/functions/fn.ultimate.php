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
use Tygh\Settings;
use Tygh\BlockManager\Layout;
use Tygh\BlockManager\Location;
use Tygh\BlockManager\ProductTabs;

/* Hooks */

/**
 * If no store selected, we should display translation mode only
 * @param array $modes available modes
 * @param array $enabled_modes enabled modes
 */
function fn_ult_get_customization_modes(&$modes, &$enabled_modes)
{
    if (!Registry::get('runtime.company_id')) {
        unset($modes['theme_editor'], $modes['design']);
    }
}

function fn_ult_get_theme_path_pre(&$path, &$area, &$company_id)
{
    if ($area == 'A') {
        return false;
    }

    if ($company_id == null && Registry::get('runtime.company_id')) {
        $company_id = Registry::get('runtime.company_id');
    }
}

/**
 * Changes cache path
 *
 * @param string $path Path to files cache
 * @param boolean $relative Flag that defines if flag should be relative
 * @param string $area Area (C/A) to get setting for
 * @param integer $company_id Company identifier
 * @return boolean Always true
 */
function fn_ult_get_cache_path(&$path, &$relative, &$area, &$company_id)
{
    if ($area == 'A') {
        return false;
    }

    if ($company_id == null && Registry::get('runtime.company_id')) {
        $company_id = Registry::get('runtime.company_id');
    }

    if (!empty($company_id)) {
        $path .=  $company_id . '/';
    }

    return true;
}

function fn_ult_get_product_data_post(&$product_data, &$auth)
{
    $product_id = $product_data['product_id'];

    $product_data['shared_product'] = fn_ult_is_shared_product($product_id);

    if ($product_data['shared_product'] == 'Y' && Registry::get('runtime.company_id')) {
        $company_product_data = db_get_row("SELECT * FROM ?:ult_product_descriptions WHERE product_id = ?i AND company_id = ?i AND lang_code = ?s", $product_id, Registry::get('runtime.company_id'), DESCR_SL);
        if (!empty($company_product_data)) {

            unset($company_product_data['company_id']);
            $product_data = array_merge($product_data, $company_product_data);
        }

        unset($product_data['prices']);
        fn_get_product_prices($product_id, $product_data, $auth, Registry::get('runtime.company_id'));

        if (empty($product_data['main_category'])) {
            $product_categories = array_keys($product_data['category_ids']);
            $product_data['main_category'] = $product_categories[0];
        }
    }
}

function fn_ult_get_product_name(&$product_id, &$lang_code, &$as_array, &$field_list, &$join, &$condition)
{
    if (Registry::get('runtime.company_id')) {
        $field_list .= ', IF(shared_descr.product_id IS NOT NULL, shared_descr.product, pd.product) as product';
        $join .= db_quote(' LEFT JOIN ?:ult_product_descriptions shared_descr ON shared_descr.product_id = pd.product_id AND shared_descr.company_id = ?i AND shared_descr.lang_code = ?s', Registry::get('runtime.company_id'), $lang_code);
    }
}

function fn_ult_get_products(&$params, &$fields, &$sortings, &$condition, &$join, &$sorting, &$group_by, &$lang_code)
{
    if (Registry::get('runtime.company_id')) {
        $auth = & $_SESSION['auth'];

        // get descriptions
        if (in_array('product_name', $params['extend'])) {
            $fields['product'] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.product, descr1.product) as product';
            $fields['short_description'] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.short_description, descr1.short_description) as short_description';
            $fields['full_description'] = "IF(shared_descr.product_id IS NOT NULL, IF(shared_descr.short_description = '', shared_descr.full_description, ''), IF(descr1.short_description = '', descr1.full_description, '')) as full_description";
            $fields['meta_keywords'] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.meta_keywords, descr1.meta_keywords) as meta_keywords';
            $fields['meta_description'] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.meta_description, descr1.meta_description) as meta_description';
            $fields['search_words'] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.search_words, descr1.search_words) as search_words';
            $fields['promo_text'] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.promo_text, descr1.promo_text) as promo_text';
            $join    .= db_quote(' LEFT JOIN ?:ult_product_descriptions shared_descr ON shared_descr.product_id = products.product_id AND shared_descr.company_id = ?i AND shared_descr.lang_code = ?s', Registry::get('runtime.company_id'), $lang_code);
        }

        // get prices
        if (in_array('prices', $params['extend'])) {
            $fields['price'] = 'IF('
                        . 'shared_prices.product_id IS NOT NULL,'
                        . 'MIN(IF(shared_prices.percentage_discount = 0, shared_prices.price, shared_prices.price - (shared_prices.price * shared_prices.percentage_discount)/100)),'
                        . 'MIN(IF(prices.percentage_discount = 0, prices.price, prices.price - (prices.price * prices.percentage_discount)/100))'
                    . ') as price';
            $price_usergroup_cond_1 = db_quote(' AND shared_prices.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
            $join .= db_quote(" LEFT JOIN ?:ult_product_prices as shared_prices ON shared_prices.product_id = products.product_id AND shared_prices.lower_limit = 1 $price_usergroup_cond_1 AND shared_prices.company_id = ?i", Registry::get('runtime.company_id'));

            if (strpos($condition, 'AND prices.price >=') !== false) {
                $condition = preg_replace('/AND prices.price >= ([\d\.]+)/', 'AND (prices.price >= $1 OR shared_prices.price >= $1)', $condition);
            }

            if (strpos($condition, 'AND prices.price <=') !== false) {
                $condition = preg_replace('/AND prices.price <= ([\d\.]+)/', 'AND (prices.price <= $1 OR shared_prices.price <= $1)', $condition);
            }
        }

        // get prices for search by price
        if (in_array('prices2', $params['extend'])) {
            $auth = & $_SESSION['auth'];
            $price_usergroup_cond_2 = db_quote(' AND shared_prices_2.usergroup_id IN (?n)', (($params['area'] == 'A') ? USERGROUP_ALL : array_merge(array(USERGROUP_ALL), $auth['usergroup_ids'])));
            $join .= db_quote(" LEFT JOIN ?:ult_product_prices as shared_prices_2 ON shared_prices.product_id = shared_prices_2.product_id AND shared_prices_2.company_id = ?i AND shared_prices_2.lower_limit = 1 AND shared_prices_2.price < shared_prices.price " . $price_usergroup_cond_2, Registry::get('runtime.company_id'));
            $condition .= ' AND shared_prices_2.price IS NULL';
        }
    }
}

function fn_ult_get_product_price_post(&$product_id, &$amount, &$auth, &$price)
{
    if (Registry::get('runtime.company_id') && fn_ult_is_shared_product($product_id) == 'Y') {
        $usergroup_condition = db_quote("AND ?:ult_product_prices.usergroup_id IN (?n)", ((AREA == 'C' || defined('ORDER_MANAGEMENT')) ? array_merge(array(USERGROUP_ALL), $auth['usergroup_ids']) : USERGROUP_ALL));

        $_price = db_get_field(
            "SELECT MIN(IF(?:ult_product_prices.percentage_discount = 0, ?:ult_product_prices.price, "
                . "?:ult_product_prices.price - (?:ult_product_prices.price * ?:ult_product_prices.percentage_discount)/100)) as price "
            . "FROM ?:ult_product_prices "
            . "WHERE company_id = ?i AND lower_limit <=?i AND ?:ult_product_prices.product_id = ?i ?p "
            . "ORDER BY lower_limit DESC LIMIT 1",
            Registry::get('runtime.company_id'), $amount, $product_id, $usergroup_condition
        );

        if ($_price !== null) {
            $price = $_price;
        }
    }
}

function fn_ult_pre_get_cart_product_data(&$hash, &$product, &$skip_promotion, &$cart, &$auth, &$promotion_amount, &$fields, &$join)
{
    if (Registry::get('runtime.company_id')) {
        $fields[] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.product, ?:product_descriptions.product) as product';
        $fields[] = 'IF(shared_descr.product_id IS NOT NULL, shared_descr.short_description, ?:product_descriptions.short_description) as short_description';
        $join .= db_quote(' LEFT JOIN ?:ult_product_descriptions shared_descr ON shared_descr.product_id = ?:products.product_id AND shared_descr.company_id = ?i AND shared_descr.lang_code = ?s', Registry::get('runtime.company_id'), CART_LANGUAGE);
    }
}

/**
 * Hook checks that product features with empty categories path may be displayed on the product page if 'All stores' was selected
 *
 * @param array $data Products features data
 * @param array $params Products features search params
 * @param boolean $has_ungroupped Flag determines if there are features without group
 */
function fn_ult_get_product_features_post(&$data, &$params, &$has_ungroupped)
{
    if (!Registry::get('runtime.company_id')) {
        foreach ($data as $k => $v) {
            if (empty($v['categories_path'])) {
                if (!empty($params['category_ids'])) {
                    $company_ids = db_get_fields('SELECT company_id FROM ?:categories WHERE category_id IN (?a)', $params['category_ids']);
                } else {
                    $company_ids = array();
                }
                if (!empty($params['product_company_id'])) {
                    $company_ids[] = $params['product_company_id'];
                }
                $company_ids = array_unique($company_ids);

                if (!empty($company_ids)) {
                    if (!fn_check_shared_company_ids('product_features', $v['feature_id'], $company_ids)) {
                        unset($data[$k]);
                        continue;
                    }
                }
            }

            if (!empty($v['subfeatures'])) {
                fn_ult_get_product_features_post($v['subfeatures'], $params, $has_ungroupped);
            }
        }
    }
}

/**
 * Hook for fn_update_product function.
 * 1. Updates data for all shared stores.
 * 2. Change option's company_id to the product.company_id
 *
 * @param array $product_data Array with product data
 * @param int $product_id Product ID
 * @param string $lang_code Language code to update product data for
 * @param boolean $create Is product created or updated existing
 */
function fn_ult_update_product_post(&$product_data, &$product_id, &$lang_code, &$create)
{
    if (!$create && fn_ult_is_shared_product($product_id) == 'Y' && !Registry::get('runtime.company_id')) {
        $update_all_vendors = !empty($_REQUEST['update_all_vendors']) ? $_REQUEST['update_all_vendors'] : array();
        foreach ($update_all_vendors as $key => $v) {
            $v = !empty($v[$product_id]) ? $v[$product_id] : $v;
            if (!is_array($v) && $v != 'Y') {
                continue;
            }

            if (in_array($key, array('product', 'full_description', 'page_title', 'meta_description', 'meta_keywords', 'search_words', 'short_description', 'promo_text'))) {
                db_query('UPDATE ?:ult_product_descriptions SET `' . $key .'` = ?s WHERE product_id = ?i AND lang_code = ?s', $product_data[$key], $product_id, $lang_code);
            }

            if ($key == 'price' && $v == 'Y') {
                db_query('UPDATE ?:ult_product_prices SET `' . $key .'` = ?s WHERE product_id = ?i AND lower_limit = 1 AND usergroup_id = ?i', abs($product_data[$key]), $product_id, USERGROUP_ALL);
            }

            if ($key == 'prices') {
                $shared_company_ids = db_get_fields("SELECT DISTINCT c.company_id FROM ?:products_categories pc LEFT JOIN ?:categories c ON c.category_id = pc.category_id WHERE pc.product_id = ?i", $product_id);
                foreach ($v as $price_key => $_v) {
                    $_data = $product_data[$key][$price_key];
                    $_data['product_id'] = $product_id;
                    $_data['type'] = !empty($_data['type']) ? $_data['type'] : 'A';
                    $_data['usergroup_id'] = !empty($_data['usergroup_id']) ? $_data['usergroup_id'] : 0;

                    if ($_data['lower_limit'] == 1 && $_data['type'] == 'P' && $_data['usergroup_id'] == 0) {
                        continue;
                    }

                    db_query("DELETE FROM ?:ult_product_prices WHERE product_id = ?i AND lower_limit = ?i AND usergroup_id = ?i", $product_id, $_data['lower_limit'], $_data['usergroup_id']);

                    if (!empty($_data['lower_limit'])) {
                        if ($_data['type'] == 'P') {
                            $_data['percentage_discount'] = ($_data['price'] > 100) ? 100 : $_data['price'];
                            $_data['price'] = $product_data['price'];
                        }
                        unset($_data['type']);
                        foreach ($shared_company_ids as $cid) {
                            $_data['company_id'] = $cid;
                            db_query('REPLACE INTO ?:ult_product_prices ?e', $_data);
                        }
                    }
                }
            }
        }
    }

    if (isset($product_data['company_id'])) {
        // Assign company_id to all product options
        $options_ids = db_get_fields('SELECT option_id FROM ?:product_options WHERE product_id = ?i', $product_id);
        if ($options_ids) {
            db_query("UPDATE ?:product_options SET company_id = ?s WHERE option_id IN (?a)", $product_data['company_id'], $options_ids);
        }
    }
}

/**
 * Hook for the fn_update_product_option function.
 * 1. Updates option data for shared product in the all shared stores.
 * 2. Deletes removed variants from table with shared products data (?:ult_product_option_variants)
 *
 * @param array $option_data Array with option data
 * @param int $option_id Option ID
 * @param array $deleted_variants Array with deleted variants ids
 * @param string $lang_code Language code to update option for
 */
function fn_ult_update_product_option_post(&$option_data, &$option_id, &$deleted_variants, &$lang_code)
{
    if (!empty($option_data['product_id']) && fn_ult_is_shared_product($option_data['product_id']) == 'Y' && !Registry::get('runtime.company_id')) {
        $update_all_vendors = !empty($_REQUEST['update_all_vendors']) ? $_REQUEST['update_all_vendors'] : array();
        foreach ($update_all_vendors as $key => $v) {
            if ($v != 'Y' || empty($option_data['variants'][$key])) {
                continue;
            }

            $variant_data = $option_data['variants'][$key];
            db_query('UPDATE ?:ult_product_option_variants SET ?u WHERE variant_id = ?i', $variant_data, $variant_data['variant_id']);
        }
    }

    if (!empty($deleted_variants)) {
        db_query("DELETE FROM ?:ult_product_option_variants WHERE variant_id IN (?n)", $deleted_variants);
    }
}

function fn_ult_update_category_post(&$category_data, &$category_id, &$lang_code)
{
    if (isset($category_data['company_id'])) {

        // Assign company_id to all subcategories
        $id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);

        $sub_cat_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE id_path LIKE ?l", "$id_path/%");
        $company_id = $category_data['company_id'];
        if (!empty($sub_cat_ids)) {
            db_query("UPDATE ?:categories SET company_id = ?s WHERE category_id IN (?a)", $company_id, $sub_cat_ids);
        }
        $sub_cat_ids[] = $category_id;

        // Assign company_id to all product
        $product_ids = db_get_fields("SELECT product_id FROM ?:products_categories WHERE category_id IN (?a) GROUP BY product_id", $sub_cat_ids);
        if (!empty($product_ids)) {

            if (!Registry::get('runtime.company_id') && isset($category_data['old_company_id']) && $category_data['old_company_id'] != $category_data['company_id']) {
                db_query("UPDATE ?:products SET company_id = ?s WHERE product_id IN (?a)", $company_id, $product_ids);

                // Assign company_id to all product options
                $option_ids = db_get_fields('SELECT option_id FROM ?:product_options WHERE product_id IN (?a)', $product_ids);
                if (!empty($option_ids)) {
                    db_query("UPDATE ?:product_options SET company_id = ?s WHERE option_id IN (?a)", $company_id, $option_ids);
                }
            }

            foreach ($product_ids as $pid) {
                fn_check_and_update_product_sharing($pid);
            }
        }
    }
}

/**
 * Force select company_id (Emulate PRO edition) for some objects and display entry page if needed
 *
 * @param array $params Request data
 * @param int $company_id Selected company ID
 * @param array $available_company_ids list if all available company IDs
 * @param array $result Initing status (array(INIT_STATUS_OK) by default). See 'fn_init' function
 * @return array Initing status
 */
function fn_ult_init_company_id(&$params, &$company_id, &$available_company_ids, &$result)
{
    if (!empty($params['entry_page'])) {
        $_SESSION['entry_page'] = true;

        $result = array(INIT_STATUS_REDIRECT, Registry::get('config.current_location'));

        return false;
    }

    if (AREA != 'A' && $company_id && empty($_SESSION['entry_page']) && !empty($_SERVER['REQUEST_URI'])) {
        $entry_page_condition = db_get_field('SELECT entry_page FROM ?:companies WHERE company_id = ?i AND redirect_customer != ?s', $company_id, 'Y');
        $url = str_replace(Registry::get('config.current_path') , '', $_SERVER['REQUEST_URI']);

        if (($entry_page_condition == 'index' && ($url == '/' . Registry::get('config.customer_index') || $url == '/')) || ($entry_page_condition == 'all_pages')) {
            $_SESSION['show_entry_page'] = true;
        } else {
            $hide_entry_page = true;
        }
    } else {
        $hide_entry_page = true;
    }

    if (!empty($hide_entry_page) && isset($_SESSION['show_entry_page'])) {
        unset($_SESSION['show_entry_page']);
    }

    // Set simple ultimate mode if needed
    if ($company_id == 0 || count($available_company_ids) == 1) {
        // We under root account. Check if we need to force select company.
        $companies = db_get_fields('SELECT company_id FROM ?:companies');

        if (count($companies) == 1) {
            Registry::set('runtime.forced_company_id', reset($companies));
            // This flag to be used to hide some fields, like Store, Owner, etc.
            Registry::set('runtime.simple_ultimate', true);
        }
    }
}

function fn_simple_ultimate(&$request)
{
    if (Registry::get('runtime.simple_ultimate')) {
        $scheme = fn_get_schema('permissions', 'admin');
        if (isset($scheme[Registry::get('runtime.controller')])) {
            $scheme = $scheme[Registry::get('runtime.controller')];
            if (isset($scheme['modes']) && isset($scheme['modes'][Registry::get('runtime.mode')])) {
                $_scheme = $scheme;
                unset($_scheme['modes']);

                $scheme = array_merge($_scheme, $scheme['modes'][Registry::get('runtime.mode')]);
            }
        }

        $company_id = Registry::get('runtime.forced_company_id');

        if (!empty($scheme['use_company'])) {
            // Force initing company_id
            if (is_array($scheme['use_company']) && !empty($scheme['use_company']['condition'])) {
                $condition_correct = true;

                foreach ($scheme['use_company']['condition'] as $condition_data) {
                    if (!isset($request[$condition_data['field']]) || $request[$condition_data['field']] != $condition_data['value']) {
                        $_checking_result = false;
                    } else {
                        $_checking_result = true;
                    }

                if (empty($condition_data['operator']) || $condition_data['operator'] == 'and') {
                        $condition_correct = $condition_correct && $_checking_result;
                    } else {
                        $condition_correct = $condition_correct || $_checking_result;
                    }
                }

                if ($condition_correct) {
                    Registry::set('runtime.company_id', $company_id);
                }
            } else {
                Registry::set('runtime.company_id', $company_id);
            }
        }

        if (!empty($scheme['auto_sharing'])) {
            $object_id = null;
            $_keys = explode('.', $scheme['auto_sharing']['object_id']);
            $_req = $request;

            foreach ($_keys as $key) {
                if (isset($_req[$key])) {
                    $object_id = $_req = $_req[$key];
                } else {
                    $object_id = null;
                }
            }

            $company_id = Registry::get('runtime.forced_company_id');

            if (!is_null($object_id)) {
                db_query('REPLACE INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) VALUES (?i, ?s, ?s)', $company_id, $object_id, $scheme['auto_sharing']['object_type']);
            }
        }

    }

    return array(INIT_STATUS_OK);
}

function fn_ult_init_company_data(&$params, &$company_id, &$company_data)
{
    if (Registry::get('runtime.forced_company_id')) {
        $company_data = fn_get_company_data(Registry::get('runtime.forced_company_id'));
    }
}

function fn_ult_update_static_data(&$data, &$param_id, &$condition, &$section, &$lang_code)
{
    if (Registry::get('runtime.company_id')) {
        $data['company_id'] = Registry::get('runtime.company_id');
        $condition .= db_quote(' AND company_id = ?i', Registry::get('runtime.company_id'));
    }
}

function fn_ult_delete_user(&$user_id, &$user_data)
{
    if ($user_data['is_root'] == 'Y') {
        db_query("UPDATE ?:users SET is_root = 'Y' WHERE company_id = ?i LIMIT 1", $user_data['company_id']);
    }
}

function fn_ult_delete_company(&$company_id)
{
    $filter_ids = db_get_fields("SELECT filter_id FROM ?:product_filters WHERE company_id = ?i", $company_id);
    foreach ($filter_ids as $filter_id) {
        fn_delete_product_filter($filter_id);
    }

    db_query('DELETE FROM ?:ult_objects_sharing WHERE share_company_id = ?i', $company_id);

    db_query('DELETE FROM ?:ult_product_descriptions WHERE company_id = ?i', $company_id);
    db_query('DELETE FROM ?:ult_product_prices WHERE company_id = ?i', $company_id);
    db_query('DELETE FROM ?:ult_product_option_variants WHERE company_id = ?i', $company_id);

    db_query('DELETE FROM ?:ult_language_values WHERE company_id = ?i', $company_id);

    Settings::instance()->removeVendorSettings($company_id);

    /**
     * Deletes additional data for ULT in add-ons
     *
     * @param integer $company_id Company ID
     */
    fn_set_hook('ult_delete_company', $company_id);
}

function fn_ult_delete_category_pre(&$category_id, $recurse)
{
    if ($recurse) {
        $id_path = db_get_field("SELECT id_path FROM ?:categories WHERE category_id = ?i", $category_id);
        $category_ids = db_get_fields("SELECT category_id FROM ?:categories WHERE category_id = ?i OR id_path LIKE ?l", $category_id, "$id_path/%");
    } else {
        $category_ids[] = $category_id;
    }

    foreach ($category_ids as $c_id) {
        $products_to_delete = db_get_fields("SELECT pc1.product_id FROM ?:products_categories pc1 LEFT JOIN ?:products_categories pc2 ON pc2.product_id = pc1.product_id AND pc2.category_id != pc1.category_id WHERE pc1.category_id = ?i and pc2.category_id IS NULL", $c_id);

        if (!empty($products_to_delete)) {
            foreach ($products_to_delete as $key => $value) {
                fn_delete_product($value);
            }
        }

        $products_to_check_sharing = db_get_fields("SELECT product_id FROM ?:products_categories WHERE category_id = ?i", $c_id);
        foreach ($products_to_check_sharing as $pid) {
            fn_check_and_update_product_sharing($pid);
        }
    }
}

function fn_ult_delete_product_post(&$product_id)
{
    db_query('DELETE FROM ?:ult_product_descriptions WHERE product_id = ?i', $product_id);
    db_query('DELETE FROM ?:ult_product_prices WHERE product_id = ?i', $product_id);
}

function fn_ult_delete_product_option_post(&$option_id, &$pid)
{
    db_query('DELETE FROM ?:ult_product_option_variants WHERE option_id = ?i', $option_id);
}

function fn_ult_get_lang_var(&$fields, &$tables, &$left_join, &$condition, &$params)
{
    if (Registry::get('runtime.company_id')) {
        $left_join[] = db_quote("?:ult_language_values ON ?:ult_language_values.name = lang.name AND company_id = ?i AND ?:ult_language_values.lang_code = lang.lang_code", Registry::get('runtime.company_id'));

        unset($fields['lang.value']);
        $fields['IF(?:ult_language_values.value IS NULL, lang.value, ?:ult_language_values.value) as value'] = true;
    }
}

function fn_ult_get_language_variable(&$fields, &$tables, &$left_join, &$condition, &$params)
{
    if (Registry::get('runtime.company_id')) {
        $left_join[] = db_quote("?:ult_language_values ON ?:ult_language_values.name = lang.name AND company_id = ?i AND ?:ult_language_values.lang_code = lang.lang_code", Registry::get('runtime.company_id'));

        unset($fields['lang.value']);
        $fields['IF(?:ult_language_values.value IS NULL, lang.value, ?:ult_language_values.value) as value'] = true;
        if (isset($params['q']) && fn_string_not_empty($params['q'])) {
            $condition['param2'] = db_quote('(IF(?:ult_language_values.name IS NULL, lang.name, ?:ult_language_values.name) LIKE ?l OR IF(?:ult_language_values.value IS NULL, lang.value, ?:ult_language_values.value) LIKE ?l)', '%' . trim($params['q']) . '%', '%' . trim($params['q']) . '%');
        }
    }
}

function fn_ult_live_editor_mode_update_langvar(&$table, &$update_fields, &$condition)
{
    if (Registry::get('runtime.company_id')) {
        if ($table == 'language_values') {
            $table = 'ult_language_values';
        }

        $condition['company_id'] = Registry::get('runtime.company_id');

        $is_exists = db_get_field('SELECT COUNT(*) FROM ?:ult_language_values WHERE ?w', $condition);
        if (!$is_exists) {
            $_data = $condition;

            foreach ($update_fields as $field) {
                list($_field, $_value) = explode('=', $field);
                $_data[trim($_field)] = substr($_value, 2, fn_strlen($_value) - 3);
            }

            $_data['company_id'] = Registry::get('runtime.company_id');

            db_query('INSERT INTO ?:ult_language_values ?e', $_data);
        }
    }
}

function fn_ult_update_lang_values(&$lang_data, &$lang_code, &$error_flag, &$params, &$result)
{
    if (Registry::get('runtime.company_id')) {
        foreach ($lang_data as $k => $v) {
            if (!empty($v['name'])) {
                preg_match("/(^[a-zA-z0-9][a-zA-Z0-9_]*)/", $v['name'], $matches);
                if (fn_strlen($matches[0]) == fn_strlen($v['name'])) {
                    $v['lang_code'] = $lang_code;
                    $v['company_id'] = Registry::get('runtime.company_id');
                    db_query("REPLACE INTO ?:ult_language_values ?e", $v);

                    // Check if variable not exists in General language variables
                    $exists = db_get_field('SELECT value FROM ?:language_values WHERE name = ?s AND lang_code = ?s', $v['name'], $lang_code);
                    if (!isset($exists) || empty($exists)) {
                        // Create language variable with empty content for other companies
                        $lang_data[$k]['value'] = '';
                    }

                } elseif (!$error_flag) {
                    fn_set_notification('E', __('warning'), __('warning_lanvar_incorrect_name'));
                    $error_flag = true;
                }

                $result[] = $v['name'];
            }

            if (!isset($params['clear']) || $params['clear']) {
                unset($lang_data[$k]);
            }
        }
    } else {
        $overwrite = array();

        foreach ($lang_data as $k => $v) {
            if (!empty($v['name']) && !empty($v['overwrite']) && $v['overwrite'] == 'Y') {
                $overwrite[] = $v['name'];
            }
        }

        if (!empty($overwrite)) {
            db_query('DELETE FROM ?:ult_language_values WHERE name IN (?a) AND lang_code = ?s', $overwrite, $lang_code);
        }
    }
}

function fn_ult_delete_language_variables(&$names, &$result)
{
    if (!empty($names)) {
        if (Registry::get('runtime.company_id')) {
            $result = db_query("DELETE FROM ?:ult_language_values WHERE name IN (?a) AND company_id = ?i AND lang_code = ?s", $names, Registry::get('runtime.company_id'), DESCR_SL);
        } else {
            $result = db_query("DELETE FROM ?:language_values WHERE name IN (?a)", $names);
            db_query("DELETE FROM ?:ult_language_values WHERE name IN (?a)", $names);
        }
    }

    $names = '';
}

function fn_ult_sitemap_get_links(&$links_fields, &$links_tables, &$links_left_join, &$links_condition)
{
    $links_condition[] = 'company_id ' . (Registry::get('runtime.company_id') ? db_quote(' = ?i', Registry::get('runtime.company_id')) : ' IS NULL');
}

function fn_ult_sitemap_update_object(&$object)
{
    if (Registry::get('runtime.company_id')) {
        $object['company_id'] = Registry::get('runtime.company_id');
    }
}

function fn_ult_sitemap_delete_links(&$link_ids)
{
    if (Registry::get('runtime.company_id')) {
        // Check permissions to delete link objects
        $_ids = db_get_fields('SELECT link_id FROM ?:sitemap_links WHERE link_id IN (?n) AND company_id = ?i', $link_ids, Registry::get('runtime.company_id'));

        db_query("DELETE FROM ?:sitemap_links WHERE link_id IN (?n)", $_ids);
        db_query("DELETE FROM ?:common_descriptions WHERE object_holder = 'sitemap_links' AND object_id IN (?n)", $_ids);

        $link_ids = array();
    }
}

function fn_ult_sitemap_delete_sections(&$section_ids)
{
    if (Registry::get('runtime.company_id')) {
        // Check permissions to delete link objects
        $_ids = db_get_fields('SELECT section_id FROM ?:sitemap_sections WHERE section_id IN (?n) AND company_id = ?i', $section_ids, Registry::get('runtime.company_id'));

        db_query("DELETE FROM ?:sitemap_sections WHERE section_id IN (?n)", $_ids);
        db_query("DELETE FROM ?:common_descriptions WHERE object_holder = 'sitemap_sections' AND object_id IN (?n)", $_ids);

        $links = db_get_fields("SELECT link_id FROM ?:sitemap_links WHERE section_id IN (?n)", $_ids);
        if (!empty($links)) {
            db_query("DELETE FROM ?:sitemap_links WHERE section_id IN (?n)", $_ids);
            db_query("DELETE FROM ?:common_descriptions WHERE object_holder = 'sitemap_links' AND object_id IN (?n)", $links);
        }

        $section_ids = array();
    }
}

function fn_ult_init_templater(&$view)
{
    if (isset($_SESSION['show_entry_page'])) {
        $view->assign('show_entry_page', $_SESSION['show_entry_page']);
    }
}

function fn_ult_update_company(&$company_data, &$company_id, &$lang_code, &$action)
{
    if ($action == 'add') {
        // Create required data
        $clone_from = !empty($company_data['clone_from']) && $company_data['clone_from'] != 'all' ? $company_data['clone_from'] : null;

        ProductTabs::instance($company_id)->createDefaultTabs();

        if (!is_null($clone_from) && !empty($company_data['clone'])) {
            Registry::set('clone_data', $company_data['clone']);
            foreach ($company_data['clone'] as $object => $enabled) {
                if ($enabled == 'Y') {
                    fn_clone_object($object, $company_data['clone_from'], $company_id);
                }
            }
        }

        // Share currencies for new company
        foreach (Registry::get('currencies') as $currency_code => $data) {
            fn_ult_update_share_object($data['currency_id'], 'currencies', $company_id);
        }

        // Share languages for new company
        foreach (fn_get_translation_languages() as $lang_code => $data) {
            fn_ult_update_share_object($data['lang_id'], 'languages', $company_id);
        }
    }
}

function fn_ult_dispatch_before_display()
{
    static $sharing_schema;
    if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
        $sharing_schema = fn_get_schema('sharing', 'schema');
    }

    foreach ($sharing_schema as $object => $data) {
        if ($data['controller'] == Registry::get('runtime.controller') && $data['mode'] == Registry::get('runtime.mode')) {
            if ($data['type'] == 'tpl_tabs') {
                if (!empty($data['conditions']['display_condition'])) {
                    if (!fn_ult_check_display_condition($_REQUEST, $data['conditions']['display_condition'])) {
                        continue;
                    }
                }
                $params = array();
                if (!empty($data['params'])) {
                    foreach ($data['params'] as $param_id => $value) {
                        if (strpos($value, '@') !== false) {
                            $value = str_replace('@', '', $value);
                            $params[$param_id] = isset($_REQUEST[$value]) ? $_REQUEST[$value] : '';
                        } else {
                            $params[$param_id] = $value;
                        }
                    }
                }
                Registry::set('sharing.tpl_tabs.tab_' . $object, array(
                    'active' => true,
                    'params' => $params,
                ));
            }
        }
    }
}

function fn_ult_user_exist(&$user_id, &$user_data, &$condition)
{
    if (Registry::get('settings.Stores.share_users') == 'N') {
        if (empty($user_data['company_id']) && !empty($user_id)) {
            $user_data['company_id'] = db_get_field('SELECT company_id FROM ?:users WHERE user_id = ?i', $user_id);
        } elseif (Registry::get('runtime.company_id')) {
            $user_data['company_id'] = Registry::get('runtime.company_id');
        }
        $condition .= db_quote(" AND (company_id = ?i OR user_type IN ('A','V')) ", $user_data['company_id']);
    }
}

function fn_ult_get_user_info_before(&$condition, &$user_id, &$user_fields)
{
    $usertype = db_get_field('SELECT user_type FROM ?:users WHERE user_id = ?i', $user_id);

    if (Registry::get('settings.Stores.share_users') == 'Y' || fn_check_user_type_admin_area($usertype)) {
        if (AREA == 'A') {
            if (Registry::get('runtime.company_id') && !defined('ORDER_MANAGEMENT')) {
                $company_customers_ids = db_get_fields("SELECT user_id FROM ?:orders WHERE company_id = ?i", Registry::get('runtime.company_id'));
                $company_id = db_get_field('SELECT company_id FROM ?:users WHERE user_id = ?i', $user_id);
                if ($company_id == Registry::get('runtime.company_id') || in_array($user_id, $company_customers_ids) || fn_ult_check_users_usergroup_companies($user_id)) {
                    $condition = '';
                }
            } else {
                $condition = '';
            }
        } elseif (AREA == 'C') {
            $condition = '';
        }
    }
}

function fn_ult_get_users(&$params, &$fields, &$sortings, &$condition, &$join)
{
    if (Registry::get('runtime.company_id')) {
        $_condition = '';
        if (Registry::get('settings.Stores.share_users') == 'Y') {
            $company_customers_ids = db_get_fields("SELECT user_id FROM ?:orders WHERE company_id = ?i", Registry::get('runtime.company_id'));
            $_condition .= db_quote(" OR ?:users.user_id IN (?n) ", $company_customers_ids);
        }
        if (empty($params['shared_force']) || Registry::get('settings.Stores.share_users') == 'N') {
            $condition['users_company_id'] = db_quote(" AND (?:users.company_id = ?i $_condition)", Registry::get('runtime.company_id'));
        }
    } else {
        if (isset($params['company_id']) && $params['company_id'] != '') {
            $condition['users_company_id'] = db_quote(' AND ?:users.company_id = ?i ', $params['company_id']);
        }
    }
}

function fn_import_check_product_company_id(&$primary_object_id, &$object, &$pattern, &$options, &$processed_data, &$processing_groups, &$skip_record)
{
    if (Registry::get('runtime.company_id')) {
        if ($pattern['pattern_id'] == 'products') {
            $object['company_id'] = Registry::get('runtime.company_id');
        }

        if (!empty($primary_object_id)) {
            list($field, $value) = each($primary_object_id);
            $company_id = db_get_field('SELECT company_id FROM ?:products WHERE ' . $field . ' = ?s', $value);

            if ($company_id != Registry::get('runtime.company_id')) {
                $processed_data['S']++;
                $skip_record = true;
            }
        }
    }
}

function fn_import_check_order_company_id(&$primary_object_id, &$object, &$pattern, &$options, &$processed_data, &$processing_groups, &$skip_record)
{
    if (Registry::get('runtime.company_id')) {
        if ($pattern['pattern_id'] == 'orders') {
            $object['company_id'] = Registry::get('runtime.company_id');
        }

        if (!empty($primary_object_id)) {
            list($field, $value) = each($primary_object_id);
            $company_id = db_get_field('SELECT company_id FROM ?:orders WHERE ' . $field . ' = ?s', $value);

            if ($company_id != Registry::get('runtime.company_id')) {
                $processed_data['S']++;
                $skip_record = true;
            }
        }
    }
}

function fn_ult_db_query_process(&$query)
{
    // Automatically add Sharing condition for SELECT queries
    // Condition will be added only for sharing objects. (Share schema)
    // Example:
    //  before: SELECT ?:pages.page_id FROM ?:pages WHERE page_id = 2
    //  after:  SELECT ?:pages.page_id FROM ?:pages INNER JOIN ?:ult_objects_sharing ON (?:ult_objects_sharing.share_object_id = ?:pages.page_id AND ?:ult_objects_sharing.share_company_id = ?:pages.company_id) WHERE page_id = 2

    if (Registry::get('runtime.company_id') && !Registry::get('runtime.skip_sharing_selection')) {
        // Cart was inited
        if (stripos($query, 'select') === 0) { // Add condition only for SELECT queries
            static $sharing_schema;
            if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
                $sharing_schema = fn_get_schema('sharing', 'schema');
            }

            preg_match('/FROM(.*?)((JOIN\s|WHERE\s|GROUP\s|HAVING\s|ORDER\s|LIMIT\s|$).*)/i', $query, $from);

            if (empty($from)) {
                return $query;
            }

            $tables = array();
            $_tables = fn_explode(',', $from[1]);

            foreach ($_tables as $table) {
                $table_parse = explode(' ', $table);
                $tables[] = reset($table_parse);
            }

            $tables = array_unique($tables);

            foreach ($tables as $table) {

                $table = str_replace(Registry::get('config.table_prefix'), '', $table);
                if (isset($sharing_schema[$table])) {

                    // Divide query into separate parts, like SELECT, FROM, etc...
                    preg_match('/SELECT(.*?)FROM/i', $query, $select);
                    preg_match('/FROM(.*?)((WHERE\s|GROUP\s|HAVING\s|ORDER\s|LIMIT\s|$).*)/i', $query, $from);
                    preg_match('/WHERE(.*?)((?:GROUP\s|HAVING\s|ORDER\s|LIMIT\s|$).*)/i', $query, $where);

                    // Check if this query should stay without changes
                    if (isset($sharing_schema[$table]['conditions']['skip_selection'])) {
                        $condition = $sharing_schema[$table]['conditions']['skip_selection'];

                        if (!is_array($condition) && $condition === true) {
                            continue;

                        } elseif (is_array($condition) && !empty($where[1])) {
                            $alias_pref = '(' . Registry::get('config.table_prefix') . $table . '\.|[^\.a-zA-Z_])'; // field used without alias or with full table name

                            if (preg_match('/' . Registry::get('config.table_prefix') . $table . '\s+AS\s+([a-zA-Z_]+)/i',$query, $alias)) {
                                $alias_pref = '(' . $alias[1] . '\.)';
                            }

                            preg_match_all('/' . $alias_pref . '([a-zA-Z_]+)\s*(=|!=|<|>|<>|IN)\s*(?:\'|")?([^ \'"]+)/', $where[1], $params);

                            if (!empty($params[2])) {
                                foreach ($params[2] as $id => $param) {
                                    if (isset($condition[$param])) {
                                        $values = is_array($condition[$param]['value']) ? $condition[$param]['value'] : array($condition[$param]['value']);
                                        foreach ($values as $value) {
                                            if (empty($condition[$param]['condition'])) {
                                                if (($params[3][$id] == 'IN' && $value == '(' . $params[4][$id] . ')') || ($params[3][$id] == '=' && $value == $params[4][$id])) {
                                                    continue 3;
                                                }
                                            } elseif ($condition[$param]['condition'] == 'equal' && (($params[3][$id] == '=' && $value == $params[4][$id]) || ($params[3][$id] == 'IN' && $value == '(' . $params[4][$id] . ')'))) {
                                                continue 3;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $additional_condition = '';

                    if (!empty($where)) {
                        $additional_condition = $where[2];
                    } elseif (!empty($from[2])) {
                        $additional_condition = $from[2];
                    }

                    // Get table alias if defined
                    if (preg_match("/$table\s?as\s?([a-zA-Z_]+)/i", $query, $alias)) {
                        $alias = $alias[1];
                    } else {
                        $alias = '?:' . $table;
                    }

                    $key_field = $sharing_schema[$table]['table']['key_field'];

                    // Build new query
                    $query = db_quote(
                        'SELECT ' . $select[1]
                        . ' FROM' . $from[1]
                        . ' INNER JOIN ?:ult_objects_sharing ON ('
                            . '?:ult_objects_sharing.share_object_id = ' . "$alias.$key_field"
                            . ' AND ?:ult_objects_sharing.share_company_id = ?i'
                            . ' AND ?:ult_objects_sharing.share_object_type = ?s)'
                        . (!empty($where[1]) ? 'WHERE ' . $where[1] : '')
                        . " $additional_condition",
                        Registry::get('runtime.company_id'),
                        $table
                    );

                    $query = db_process($query);
                }
            }
        }
    }
}

function fn_ult_db_query_executed(&$query, &$result)
{
    static $schema;

    if (Registry::get('runtime.skip_sharing_selection')) {
        return true;
    }

    if (empty($schema) && Registry::get('addons_initiated') === true) {
        $schema = fn_get_schema('sharing', 'schema');
    }

    if (preg_match('/(?:INSERT|REPLACE)\s?INTO\s?([a-zA-Z_0-9]+)/i', $query, $tables)) {
        $object = str_replace(Registry::get('config.table_prefix'), '', $tables[1]);
        if (isset($schema[$object])) {
            $object_id = db_get_field('SELECT LAST_INSERT_ID()');
            if (empty($object_id)) {
                preg_match('/VALUES\s?\((.*?)\)/i', $query, $values);
                $values = explode(',', $values[1]);

                if (preg_match('/\((.*?)\)\sVALUES/i', $query, $fields)) {
                    $fields = explode(',', $fields[1]);
                } else {
                    $fields = fn_get_table_fields($object, array(), false);
                }

                $data = array();
                foreach ($fields as $key => $field) {
                    $data[str_replace('`', '', trim($field))] = trim(str_replace('\'', '', $values[$key]));
                }

                $object_id = $data[$schema[$object]['table']['key_field']];
            }

            if (Registry::get('sharing_owner.' . $object)) {
                db_query('REPLACE INTO ?:ult_objects_sharing (`share_company_id`, `share_object_id`, `share_object_type`) VALUES (?i, ?s, ?s)', Registry::get('sharing_owner.' . $object), $object_id, $object);
            }
            if (!empty($object_id) && $schema[$object]['have_owner'] === false) {
                fn_share_object_to_all($object, $object_id);
            }
        }
    } elseif (preg_match('/(?:DELETE)\s?FROM\s?([a-zA-Z_]+)/i', $query, $tables)) {
        $object = str_replace(Registry::get('config.table_prefix'), '', $tables[1]);
        if (isset($schema[$object])) {
            preg_match('/' . $schema[$object]['table']['key_field'] . '\s?=\s?\'?\"?([0-9a-zA-Z]+)/i', $query, $_object);
            if (!empty($_object[1])) {
                $object_id = $_object[1];

                db_query('DELETE FROM ?:ult_objects_sharing WHERE share_object_id = ?s AND share_object_type = ?s', $object_id, $object);
            }
        }
    }
}

function fn_ult_get_shared_companies($object_id, $object_type)
{
    $company_ids = db_get_fields('SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_id = ?s AND share_object_type = ?s', $object_id, $object_type);

    return $company_ids;
}

function fn_ult_dispatch_assign_template($controller, $mode, $area)
{
    if ($area == 'A' && fn_check_object_exists_for_root($controller, $mode)) {
        $view = Registry::get('view');
        $view->assign('content_tpl', 'common/select_company.tpl');
        $view->assign('select_id', 'vendor_selector');

        $schema = fn_get_permissions_schema('admin');

        if (isset($schema[$controller]['modes'][$mode]['page_title'])) {
            $view->assign('title', $schema[$controller]['modes'][$mode]['page_title']);

        } elseif (isset($schema[$controller]['page_title'])) {
            $view->assign('title', $schema[$controller]['page_title']);
        }
    }
}

function fn_ult_url_post(&$result_url, &$area, &$url, &$prefix, &$company_id_in_url, &$lang_code)
{
    if ($company_id_in_url !== false) {
        $company_id = $company_id_in_url;
    } elseif (Registry::get('runtime.company_id') || Registry::get('runtime.forced_company_id')) {
        $company_id = Registry::get('runtime.company_id');
    }

    if (isset($company_id) && ($prefix == 'https' || $prefix == 'http' || $prefix == 'current')) {
        if ($area == 'C') { // Build link to the frontend in the backend

            if ($prefix == 'current') {
                $protocol = defined('HTTPS') ? 'https' : 'http';
            } else {
                $protocol = $prefix;
            }

            $storefront = fn_get_storefront_urls($company_id);
            $location = Registry::get('config.' . $protocol . '_location');

            $result_url = str_replace($location, $storefront[$protocol . '_location'], $result_url);
            $result_url = fn_query_remove($result_url, 'company_id');
        }
    }

    if (isset($company_id)) {
        $company_id_in_url = $company_id;
    }
}

function fn_ult_pre_extract_cart(&$cart, &$condition, &$item_types)
{
    $condition .= fn_get_company_condition('?:user_session_products.company_id');
}

function fn_ult_get_carts(&$type_restrictions, &$params, &$condition, &$join, &$fields, &$group)
{
    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND ?:user_session_products.company_id = ?i", Registry::get('runtime.company_id'));
    } else {
        $company_ids = db_get_fields("SELECT company_id FROM ?:user_session_products WHERE type = 'C' OR type = 'W' GROUP BY company_id");
        $condition .= db_quote(" AND ?:user_session_products.company_id in (?n)", $company_ids);
    }

    $group .= " , ?:user_session_products.company_id";

    if (!empty($params['company_id'])) {
        $condition .= db_quote(" AND ?:user_session_products.company_id = ?i", $params['company_id']);
    }

    $fields[] = '?:user_session_products.company_id';
}

function fn_ult_get_order_info(&$order, &$additional_data)
{
    if (!empty($order['company_id']) && !Registry::get('runtime.company_id')) {
        // Update Company information from the root company to order owner company.
        $company_settings = Settings::instance()->getValues('Company', Settings::CORE_SECTION, true, $order['company_id']);

        Registry::set('settings.Company', $company_settings);
    }
    if (!empty($order['products'])) {
        foreach ($order['products'] as $cart_id => $item) {
            if (!$item['deleted_product']) {
                $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $order['products'][$cart_id]['product_id']);
                $order['products'][$cart_id]['shared_product'] = (fn_ult_is_shared_product($item['product_id'], $order['company_id']) == 'Y' || (Registry::get('runtime.company_id') && Registry::get('runtime.company_id') == $product_company_id)) ? true : false;
            }
        }
    }
}

function fn_ult_sid(&$sess_id)
{
    $suffix = '_0';

    if (AREA != 'A') {
        if (Registry::get('runtime.company_id')) {
            $suffix = '_' . Registry::get('runtime.company_id');
        } elseif (isset($_REQUEST['switch_company_id']) && $_REQUEST['switch_company_id'] != 'all') {
            $suffix = '_' . $_REQUEST['switch_company_id'];
        }
    }

    $sess_id .= $suffix;

    return true;
}

function fn_ult_clone_page_pre(&$page_id, &$data)
{
    if (Registry::get('runtime.company_id')) {
        if ($data['company_id'] != Registry::get('runtime.company_id')) {
            $data['parent_id'] = 0;
            $data['id_path'] = $page_id;
        }
        $data['company_id'] = Registry::get('runtime.company_id');
    }
}

function fn_ult_clone_page(&$page_id, &$new_page_id)
{
    $share_company_ids = array();
    if (Registry::get('runtime.company_id')) {
        $share_company_ids[] = Registry::get('runtime.company_id');
    } else {
        $share_company_ids = db_get_fields("SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_id = ?i AND share_object_type = ?s", $page_id, 'pages');
    }
    foreach ($share_company_ids as $share_company_id) {
        db_query('INSERT INTO ?:ult_objects_sharing (share_object_id, share_object_type, share_company_id) VALUES (?i, ?s, ?i)', $new_page_id, 'pages', $share_company_id);
    }
}

function fn_ult_form_cart(&$order_info, &$cart)
{
    if (isset($order_info['company_id'])) {
        $cart['order_company_id'] = $order_info['company_id'];
    }
}

function fn_ult_update_page_before(&$page_data, &$page_id, &$lang_code)
{
    if (!empty($page_data['page'])) {
        $page_data['company_id'] = fn_set_page_company_id($page_data, $lang_code);
    }
}

function fn_ult_update_page_post(&$page_data, &$page_id, &$lang_code, &$create, &$old_page_data)
{
    if (empty($page_data['page'])) {
        return false;
    }

    if ($create) {
        //create new page
        if (!empty($page_data['parent_id'])) {
            $parent_page_companies = db_get_fields("SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_type = 'pages' AND share_object_id = ?i", $page_data['parent_id']);
            foreach ($parent_page_companies as $parent_page_company_id) {
                db_query("REPLACE INTO ?:ult_objects_sharing (share_object_id, share_company_id, share_object_type) VALUES (?i, ?i, 'pages')", $page_id, $parent_page_company_id);
            }
        }
    } else {
        //update page
        $page_childrens = db_get_fields("SELECT page_id FROM ?:pages WHERE id_path LIKE ?l AND parent_id != 0", '%' . $page_id . '%');
        $root_pages = explode('/', db_get_field("SELECT id_path FROM ?:pages WHERE page_id = ?i", $page_id));
        $share_pages = array_merge($page_childrens, $root_pages);
        $share_objects_count = !empty($_REQUEST['share_objects']['pages']) ? count($_REQUEST['share_objects']['pages']) : 0;
        $old_share_objects_count = !empty($_REQUEST['selected_companies_count']) ? $_REQUEST['selected_companies_count'] : 0;

        if ($page_data['parent_id'] != 0 && $old_page_data['parent_id'] == 0) {
            $parent_page_companies = db_get_fields("SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_type = 'pages' AND share_object_id = ?i", $page_data['parent_id']);
            fn_ult_share_page((array) $page_id, $parent_page_companies);
        }

        $page_companies = db_get_fields("SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_type = 'pages' AND share_object_id = ?i", $page_id);

        if ($page_data['parent_id'] != 0) {
            if ($share_objects_count < $old_share_objects_count) {
                //companies was deleted from sharing, we should update only childrens
                fn_ult_share_page($page_childrens, $page_companies);
            } else {
                fn_ult_share_page($share_pages, $page_companies);
            }
        } else {
            fn_ult_share_page($share_pages, $page_companies);
        }

        if (!empty($page_childrens)) {
            //update childrens company if we update company for root page.
            if ($page_data['parent_id'] == 0 || $old_page_data['parent_id'] == 0) {
//              db_query("UPDATE ?:pages SET company_id = ?i WHERE page_id IN (?n)", $page_data['company_id'], $page_childrens);
                fn_change_page_company($page_id, $page_data['company_id']);
            }
        }
    }
}

function fn_ult_delete_user_cart(&$user_ids, &$condition, &$data)
{
    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(' AND company_id = ?i', Registry::get('runtime.company_id'));
    } else {
        $condition .= db_quote(' AND company_id = ?i', $data);
    }
}

function fn_ult_get_companies_list(&$condition, &$pattern, &$start, &$limit, &$params)
{
    if (!empty($_SESSION['auth']['company_id'])) {
        $condition .= db_quote(' AND company_id = ?i', $_SESSION['auth']['company_id']);
        $params['show_all'] = 'N';
    }
}

/**
 * Uses post hook of allow_save_object_post function. Deny to save
 *
 * @param array $object_data Object data information
 * @param string $object_type Type of object ('currencies', 'pages', etc)
 * @param bool $allow Save object flag
 */
function fn_ult_allow_save_object_post(&$object_data, &$object_type, &$allow)
{
    $sharing_schema = fn_get_schema('sharing', 'schema');

    if (Registry::get('runtime.company_id') && isset($sharing_schema[$object_type]) && empty($sharing_schema[$object_type]['have_owner'])) {
        $allow = false;
    }
}

/**
 * Hook for getting values of shared product option variants
 *
 * @param string $v_fields Fields to be selected
 * @param string $v_condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
 * @param string $v_join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
 * @param string $v_sorting String with the information for the "order by" statement
 * @param array $option_ids Options identifiers
 * @param string $lang_code 2-letters language code
 */
function fn_ult_get_product_options_get_variants(&$v_fields, &$v_condition, &$v_join, &$v_sorting, &$option_ids, &$lang_code)
{
    if (Registry::get('runtime.company_id')) {
        $v_fields .= ', IF(shared_option_variants.variant_id IS NOT NULL, shared_option_variants.modifier, a.modifier) as modifier';
        $v_fields .= ', IF(shared_option_variants.variant_id IS NOT NULL, shared_option_variants.modifier_type, a.modifier_type) as modifier_type';
        $v_join .= db_quote(' LEFT JOIN ?:ult_product_option_variants shared_option_variants ON shared_option_variants.variant_id = a.variant_id AND shared_option_variants.company_id = ?i', Registry::get('runtime.company_id'));
    }
}

/**
* Hook for getting option modifiers of shared product
*
* @param string $type Calculation type (price or weight)
* @param string $fields Fields to be selected
* @param string $om_condition String containing SQL-query condition possibly prepended with a logical operator (AND or OR)
* @param string $om_join String with the complete JOIN information (JOIN type, tables and fields) for an SQL-query
* @param array $variant_id Variant identifier
*/
function fn_ult_apply_option_modifiers_get_option_modifiers(&$type, &$fields, &$om_join, &$om_condition, &$variant_id)
{
    if ($type == 'P' && Registry::get('runtime.company_id')) {
        $fields .= ', IF(shared_option_variants.variant_id IS NOT NULL, shared_option_variants.modifier, a.modifier) as modifier';
        $fields .= ', IF(shared_option_variants.variant_id IS NOT NULL, shared_option_variants.modifier_type, a.modifier_type) as modifier_type';
        $om_join .= db_quote(' LEFT JOIN ?:ult_product_option_variants shared_option_variants ON shared_option_variants.variant_id = a.variant_id AND shared_option_variants.company_id = ?i', Registry::get('runtime.company_id'));
    }
}

/* Functions */

/**
 * Check if product is shared.
 *
 * @param int $product_id Product id
 * @param int $company_id Company id, if company id is not empty,
 * check if product is shared for selected company
 * @return string 'Y' or 'N'
 */
function fn_ult_is_shared_product($product_id, $company_id = 0)
{
    $company_condition = !empty($company_id) ? fn_get_company_condition('c.company_id', true, $company_id) : '';
    $companies = db_get_fields("SELECT c.company_id FROM ?:products_categories pc LEFT JOIN ?:categories c ON c.category_id = pc.category_id WHERE pc.product_id = ?i $company_condition GROUP BY c.company_id LIMIT 2", $product_id);
    $companies_count = count($companies);
    if ($companies_count == 1) {
        $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product_id);

        if (empty($product_company_id)) {
            // Inconsistent product. Product company cannot be null or zero
            return 'N';

        } elseif (reset($companies) != $product_company_id) {
            return 'Y';
        }
    }

    return $companies_count > 1 ? 'Y' : 'N';
}

/**
 * If product with given $product_id is shared, function returns company ids of stores for which product is shared.
 * If product is not shared, only the company id of store owner will be returned.
 *
 * @param int $product_id Product ID
 * @return array Company ids
 */
function fn_ult_get_shared_product_companies($product_id)
{
    $product_company_ids = db_get_fields(
        "SELECT c.company_id"
        . " FROM ?:products_categories pc"
        . " LEFT JOIN ?:categories c ON c.category_id = pc.category_id"
        . " WHERE pc.product_id = ?i"
        . " GROUP BY c.company_id",
        $product_id
    );

    return $product_company_ids;
}

/**
 * Function checks and changes shared product data
 *
 * @param int $product_id Product ID
 */
function fn_check_and_update_product_sharing($product_id)
{
    $shared = false;
    $new_categories_company_ids = db_get_fields(
        "SELECT DISTINCT c.company_id"
        . " FROM ?:products_categories pc"
        . " LEFT JOIN ?:categories c ON c.category_id = pc.category_id"
        . " WHERE pc.product_id = ?i",
        $product_id
    );

    $shared_categories_company_ids = db_get_fields(
        "SELECT DISTINCT company_id FROM ?:ult_product_descriptions WHERE product_id = ?i",
        $product_id
    );

    $count = count($new_categories_company_ids);
    $company_id = reset($new_categories_company_ids);
    $product_company_id = db_get_field(
        'SELECT company_id FROM ?:products WHERE product_id = ?i',
        $product_id
    );

    if ($count == 1 && $company_id == $product_company_id) {
        // product belongs to one store. It is not shared now

        // check that products.company_id == category.company_id
        /* Do not touch product.company_id.
        if ($company_id != $product_company_id) {
            db_query('UPDATE ?:products SET company_id = ?i WHERE product_id = ?i', $company_id, $product_id);
        }*/

        db_query('DELETE FROM ?:ult_product_descriptions WHERE product_id = ?i', $product_id);
        db_query('DELETE FROM ?:ult_product_prices WHERE product_id = ?i', $product_id);

        $product_options = fn_get_product_options($product_id, DESCR_SL, false, false, false, true);
        $product_options = array_keys($product_options);
        if (!empty($product_options)) {
            db_query('DELETE FROM ?:ult_product_option_variants WHERE option_id IN (?a)', $product_options);
        }

    } else {
        $shared = true;
        $new_company_ids = array_diff($new_categories_company_ids, $shared_categories_company_ids);
        if (!empty($new_company_ids)) {
            $product_options = fn_get_product_options($product_id, DESCR_SL, false, false, false, true);
            $product_options = array_keys($product_options);
        }
        foreach ($new_company_ids as $new_cid) {

            // coping owner data to tables with shared data
            db_query(
                'REPLACE INTO ?:ult_product_descriptions ('
                    . ' product_id, lang_code, company_id, product, shortname, short_description,'
                    . ' full_description, meta_keywords, meta_description, search_words, page_title,'
                    . ' age_warning_message, promo_text)'
                . ' SELECT'
                    . ' product_id, lang_code, ?i, product, shortname, short_description,'
                    . ' full_description, meta_keywords, meta_description, search_words,'
                    . ' page_title, age_warning_message, promo_text'
                . ' FROM ?:product_descriptions'
                . ' WHERE product_id = ?i',
                $new_cid, $product_id
            );

            db_query(
                'REPLACE INTO ?:ult_product_prices ('
                    . ' product_id, price, percentage_discount, lower_limit, company_id, usergroup_id)'
                . ' SELECT product_id, price, percentage_discount, lower_limit, ?i, usergroup_id'
                . ' FROM ?:product_prices'
                . ' WHERE product_id = ?i',
                $new_cid, $product_id
            );

            if (!empty($product_options)) {
                db_query(
                    'REPLACE INTO ?:ult_product_option_variants ('
                    . ' variant_id, option_id, company_id, modifier, modifier_type)'
                    . ' SELECT variant_id, option_id, ?i, modifier, modifier_type'
                    . ' FROM ?:product_option_variants'
                    . ' WHERE option_id IN (?a)',
                    $new_cid, $product_options
                );
            }
        }

        $deleted_company_ids = array_diff($shared_categories_company_ids, $new_categories_company_ids);
        if (!empty($deleted_company_ids)) {

            // deleting data from shared tables
            db_query(
                'DELETE FROM ?:ult_product_descriptions'
                . ' WHERE product_id = ?i AND company_id IN (?a)',
                $product_id, $deleted_company_ids
            );

            db_query(
                'DELETE FROM ?:ult_product_prices'
                . ' WHERE product_id = ?i AND company_id IN (?a)',
                $product_id, $deleted_company_ids
            );

            if (!isset($product_options)) {
                $product_options = fn_get_product_options($product_id, DESCR_SL, false, false, false, true);
                $product_options = array_keys($product_options);
            }

            if (!empty($product_options)) {
                db_query(
                    'DELETE FROM ?:ult_product_option_variants'
                    . ' WHERE option_id IN (?a) AND company_id IN (?a)',
                    $product_options, $deleted_company_ids
                );
            }
        }

        $global_option_links = db_get_fields("SELECT option_id FROM ?:product_global_option_links WHERE product_id = ?i", $product_id);
        $product_options = array_merge((isset($product_options) ? $product_options : array()), $global_option_links);

        if (!empty($product_options)) {
            foreach ($product_options as $po_id) {
                fn_ult_share_product_option($po_id, $product_id);
            }
        }
    }

    $cids = db_get_fields('SELECT category_id FROM ?:products_categories WHERE product_id = ?i', $product_id);
    fn_update_product_count($cids);

    /**
    * Processed addition/deletion of companies product is shared between
    *
    * @param int     $product_id
    * @param boolean $shared
    * @param array   $shared_categories_company_ids
    * @param array   $new_categories_company_ids
    */
    fn_set_hook('check_and_update_product_sharing', $product_id, $shared, $shared_categories_company_ids, $new_categories_company_ids);
}

function fn_ult_update_shared_product(&$product_data, $product_id, $company_id, $lang_code = CART_LANGUAGE)
{
    if (empty($product_id)) {
        return false;
    }

    $_data = $product_data;

    if (isset($product_data['product']) && empty($product_data['product'])) {
        unset($product_data['product']);
    }

    if (!empty($_data['product'])) {
        $_data['product'] = trim($_data['product'], " -");
    }
    $_data['product_id'] = $product_id;
    $_data['lang_code'] = $lang_code;
    $_data['company_id'] = $company_id;

    // Get old product data
    $old_description = db_get_row('SELECT * FROM ?:ult_product_descriptions WHERE product_id = ?i AND lang_code = ?s AND company_id = ?i', $_data['product_id'], $_data['lang_code'], $_data['company_id']);
    $_data = array_merge($old_description, $_data);

    db_query("REPLACE INTO ?:ult_product_descriptions ?e", $_data);

    // Update product prices
    if (!empty($_REQUEST['update_all_vendors']['price']) && $_REQUEST['update_all_vendors']['price'] == 'Y') {
        $companies = fn_ult_get_shared_product_companies($product_id);
        $_product_data = $product_data;
        unset($_product_data['prices']);
        foreach ($companies as $_company_id) {
            fn_update_product_prices($product_id, $_product_data, $_company_id);
        }
        unset($_product_data);
    } else {
        fn_update_product_prices($product_id, $product_data, $company_id);
    }

    return $product_id;
}

function fn_ult_share_features($object_id, $object_type, $companies)
{
    if (!empty($companies)) {
        $parent_id = db_get_field('SELECT parent_id FROM ?:product_features WHERE feature_id = ?i', $object_id);

        if (!empty($parent_id)) {
            // Share parent object to companies
            foreach ($companies as $company_id) {
                db_query('REPLACE INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) VALUES (?i, ?i, ?s)', $company_id, $parent_id, $object_type);
            }
        }
    }
}

function fn_ult_update_shared_product_option($option_data, $option_id, $company_id, $lang_code = DESCR_SL)
{
    if (!empty($option_data['variants'])) {
        // Generate special variants structure for checkbox (2 variants, 1 hidden)
        if ($option_data['option_type'] == 'C') {
            $option_data['variants'] = array_slice($option_data['variants'], 0, 1); // only 1 variant should be here
            reset($option_data['variants']);
            $_k = key($option_data['variants']);
            $option_data['variants'][$_k]['position'] = 1; // checked variant
            $v_id = db_get_field("SELECT variant_id FROM ?:product_option_variants WHERE option_id = ?i AND position = 0", $option_id);
            $option_data['variants'][] = array ( // unchecked variant
                'position' => 0,
                'variant_id' => $v_id
            );
        }

        foreach ($option_data['variants'] as $k => $v) {
            if (empty($v['variant_name']) && empty($v['variant_id'])) {
                continue;
            }

            // Update product options variants
            if (isset($v['modifier'])) {
                $v['modifier'] = floatval($v['modifier']);
                if (floatval($v['modifier']) > 0) {
                    $v['modifier'] = '+' . $v['modifier'];
                }
            }

            $v['option_id'] = $option_id;
            $v['company_id'] = $company_id;

            if (empty($v['variant_id']) || (!empty($v['variant_id']) && !db_get_field("SELECT variant_id FROM ?:ult_product_option_variants WHERE variant_id = ?i AND company_id = ?i", $v['variant_id'], $company_id))) {
                $v['variant_id'] = db_query("INSERT INTO ?:ult_product_option_variants ?e", $v);
            } else {
                db_query("UPDATE ?:ult_product_option_variants SET ?u WHERE variant_id = ?i AND company_id = ?i", $v, $v['variant_id'], $company_id);
            }
        }
    }

    return $option_id;
}

function fn_init_store_params_by_host(&$request, $area = AREA)
{
    if ($area == 'A' && empty($request['allow_initialization'])) {
        return array(INIT_STATUS_OK);
    }

    $host = $_SERVER['HTTP_HOST'];
    $short_host = preg_replace('/^www[0-9]*\./i', '', $host);

    $field = defined('HTTPS') ? 'secure_storefront' : 'storefront';
    $companies = db_get_array("SELECT company_id, $field FROM ?:companies WHERE $field LIKE ?l OR $field LIKE ?l", $short_host . '%', $host . '%');

    /**
     * Actions before choosing a company by host
     *
     * @param array $request    Request
     * @param str   $area       Area
     * @param str   $host       Host
     * @param str   $short_host Short Host
     * @param str   $field      Field name
     * @param array $companies  Companies list
     */
    fn_set_hook('init_store_params_by_host', $request, $area, $host, $short_host, $field, $companies);

    if (!empty($companies)) {
        if (count($companies) == 1) {
            $request['switch_company_id'] = $companies[0]['company_id'];
        } else {
            $found_companies = array();
            foreach ($companies as $company) {
                $parsed_url = parse_url('http://' . $company[$field]); // protocol prefix does not matter

                if (empty($parsed_url['path'])) {
                    $found_companies[0] = $company['company_id'];

                } elseif (!empty($_SERVER['REQUEST_URI']) && preg_match("/^" . preg_quote($parsed_url['path'], '/') . "([\/\?].*?)?$/", $_SERVER['REQUEST_URI'], $m)) {
                    $priority = count(explode('/', $parsed_url['path']));
                    $found_companies[$priority] = $company['company_id'];
                }
            }

            if (!empty($found_companies)) {
                krsort($found_companies);
                $request['switch_company_id'] = reset($found_companies);
            }
        }
    }

    if (!empty($request['switch_company_id']) && $request['switch_company_id'] != 'all' && !isset($request['skip_config_changing'])) { // theme for company with id = 0 cannot be loaded.
        $company_data = db_get_row('SELECT company_id, storefront, secure_storefront, redirect_customer FROM ?:companies WHERE company_id = ?i', $request['switch_company_id']);

        if (empty($company_data)) {
            return array(INIT_STATUS_OK);
        }

        if ($company_data['redirect_customer'] == 'Y' && !fn_get_cookie('storefront_redirect_' . $request['switch_company_id'])) {
            $_ip = fn_get_ip(true);
            $_country = fn_get_country_by_ip($_ip['host']);

            if (!empty($_country)) {
                // Check if found country assigned to some companies
                $redirect = db_get_hash_array('SELECT company_id, storefront FROM ?:companies WHERE FIND_IN_SET(?s, countries_list) LIMIT 1', 'company_id', $_country);

                if (!empty($redirect) && !isset($redirect[$request['switch_company_id']])) {
                    if (!defined('CRAWLER')) {
                        $redirect_url = reset($redirect);
                        $redirect_url = 'http://' . $redirect_url['storefront'];

                        fn_set_cookie('storefront_redirect_' . $request['switch_company_id'], true);

                        return array(INIT_STATUS_REDIRECT, $redirect_url);
                    }
                }
            }
        }

        $config = Registry::get('config');

        $url_data = fn_get_storefront_urls(0, $company_data);
        $config = fn_array_merge($config, $url_data);
        $config['images_path'] = $config['current_path'] . '/media/images/';
        $config['origin_http_location'] = $config['http_location'];
        $config['origin_https_location'] = $config['https_location'];

        Registry::set('config', $config);

        $status = INIT_STATUS_OK;
        $message = '';
    } else {
        $status = INIT_STATUS_FAIL;
        $message = 'No storefronts defined for this domain';
    }

    /**
     * Actions after choosing a company by host
     *
     * @param array $request Request
     * @param str   $area    Area
     * @param array $config  Config
     * @param str   $status  Status
     * @param str   $message Message text
     */
    fn_set_hook('init_store_params_by_host_post', $request, $area, $config, $status, $message);

    return array($status, '', $message);
}

function fn_init_clone_schemas()
{
    $schema = fn_get_schema('clone', 'objects');

    fn_set_hook('init_clone_schema', $schema);

    return $schema;
}

function fn_ult_set_company_settings_information($data, $company_id)
{
    foreach ($data as $k => $v) {
        Settings::instance()->updateValueById($k, $v, $company_id);
    }
}

function fn_share_object($object, $from, $to)
{
    $share_data = db_get_fields('SELECT share_object_id FROM ?:ult_objects_sharing WHERE share_company_id = ?i AND share_object_type = ?s', $from, $object);
    if (!empty($share_data)) {
        $query = 'REPLACE INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) VALUES ';
        $data = array();
        foreach ($share_data as $object_id) {
            $data[] = db_quote('(?i, ?s, ?s)', $to, $object_id, $object);
        }

        $query .= implode(',', $data);
        db_query($query);
    }
}

function fn_share_object_to_all($object, $object_id)
{
    static $company_ids = array();

    if (!$company_ids) {
        $company_ids = db_get_fields("SELECT company_id FROM ?:companies");
    }

    foreach ($company_ids as $cid) {
        db_query('REPLACE INTO ?:ult_objects_sharing (share_company_id, share_object_id, share_object_type) VALUES (?i, ?s, ?s)', $cid, $object_id, $object);
    }
}

function fn_clone_object($object, $from, $to)
{
    static $schema, $sharing_schema;

    if (empty($schema)) {
        $schema = fn_init_clone_schemas();
        $sharing_schema = fn_get_schema('sharing', 'schema');
    }

    if (!isset($schema[$object])) {
        // Clone schema not found
        return false;
    } elseif (!empty($schema[$object]['use_sharing'])) {
        fn_share_object($object, $from, $to);

        return true;
    }

    if (!empty($schema[$object]['dependence'])) {
        // Schema has dependence. Use other schema which uses this one.
        return true;
    }

    if (!empty($schema[$object]['tables'])) {
        $result = array();

        foreach ($schema[$object]['tables'] as $table_data) {
            list($result, $new_data) = fn_clone_table_data($table_data, array(), 0, $from, $to, $result);

            if (!empty($new_data) && isset($sharing_schema[$table_data['name']])) {
                // Clone object found in sharing schema. Share new object as well
                foreach ($new_data as $old_id => $new_id) {
                    $company_ids = db_get_fields('SELECT share_company_id FROM ?:ult_objects_sharing WHERE share_object_type = ?s AND share_object_id = ?s', $table_data['name'], $old_id);

                    $data = array();
                    $data[] = db_quote('(?s, ?s, ?i)', $new_id, $table_data['name'], $to);

                    foreach ($company_ids as $company_id) {
                        $data[] = db_quote('(?s, ?s, ?i)', $new_id, $table_data['name'], $company_id);
                    }

                    if (!empty($data)) {
                        db_query('REPLACE INTO ?:ult_objects_sharing (share_object_id, share_object_type, share_company_id) VALUES ' . implode(', ', $data));
                    }
                }
            }
        }
    }

    if (!empty($schema[$object]['function']) && function_exists($schema[$object]['function'])) {
        call_user_func($schema[$object]['function'], $schema[$object], $from, $to);
    }

    return true;
}

function fn_build_dependence_tree($table, $key, $parent = 'parent_id', $company_id = 0, $tree = array(), $from_ids = array())
{
    if (!empty($from_ids)) {
        $parent_id = $from_ids;
    } else {
        $parent_id = array(0);
    }

    $from_ids = db_get_fields('SELECT ' . $key . ' FROM ?:' . $table . ' WHERE ' . $parent . ' IN (?a) AND company_id = ?i', $parent_id, $company_id);

    if (!empty($from_ids)) {
        foreach ($from_ids as $id) {
            array_push($tree, $id);
        }
    }

    if (!empty($from_ids)) {
        $tree = fn_build_dependence_tree($table, $key, $parent, $company_id, $tree, $from_ids);
    }

    return $tree;
}

function fn_clone_table_data($table_data, $clone_data, $start, $from, $to, $extra = array())
{
    static $schema;
    static $cloned_ids = array();

    $clone_id = $table_data['name'];

    if (!isset($cloned_ids[$clone_id])) {
        $cloned_ids[$clone_id] = array();
    }

    if (empty($schema)) {
        $schema = fn_init_clone_schemas();
    }

    $limit = 50; // Clone 50 lines per one iteration
    $return = array();

    $condition = '';
    if (!empty($table_data['condition'])) {
        $condition = ' AND ' . implode(' AND ', $table_data['condition']);

        preg_match_all('/%(.*?)%/', $condition, $variables);
        foreach ($variables[1] as $variable) {
            $variable = fn_strtolower($variable);
            $var = $$variable;
            if (is_array($var)) {
                $var = implode(', ', $var);
            }

            $condition = preg_replace('/%(.*?)%/', $var, $condition, 1);
        }
    }

    if (!empty($table_data['dependence_tree'])) {
        $ids = fn_build_dependence_tree($table_data['name'], $table_data['key'], $parent = 'parent_id', $from);
        $data = $_data = array();

        if (!empty($ids)) {
            $_data = db_get_hash_array('SELECT * FROM ?:' . $table_data['name'] . ' WHERE company_id = ?i ' . $condition . 'AND ' . $table_data['key'] . ' IN (?a)', $table_data['key'], $from, $ids);
        }

        foreach ($ids as $id) {
            if (isset($_data[$id])) {
                $data[] = $_data[$id];
            }
        }

        unset($_data, $ids);

        $start = db_get_field('SELECT COUNT(*) FROM ?:' . $table_data['name'] . ' WHERE company_id = ?i', $from);

    } elseif (empty($clone_data)) {
        $data = db_get_array('SELECT * FROM ?:' . $table_data['name'] . ' WHERE company_id = ?i ' . $condition . ' LIMIT ?i, ?i', $from, $start, $limit);
    } else {
        $data = db_get_array('SELECT * FROM ?:' . $table_data['name'] . ' WHERE ' . $table_data['key'] . ' IN (?a)' . $condition, array_keys($clone_data));
    }

    if (!empty($data)) {
        // We using sharing. So do not use "quick" insert schema...
        if (false && empty($table_data['children']) && empty($table_data['pre_process']) && empty($table_data['post_process']) && empty($table_data['return_clone_data'])) {
            $exclude = array((empty($clone_data) ? $table_data['key'] : ''));
            if (!empty($table_data['exclude'])) {
                $exclude = array_merge($exclude, $table_data['exclude']);
            }
            $fields = fn_get_table_fields($table_data['name'], $exclude, true);

            $query = 'REPLACE INTO ?:' . $table_data['name'] . ' (' . implode(',', $fields) . ') VALUES ';
            $rows = array();
            foreach ($data as $row) {
                if (empty($clone_data)) {
                    unset($row[$table_data['key']]);
                } else {
                    $row[$table_data['key']] = $clone_data[$row[$table_data['key']]];
                }

                if (!empty($extra)) {
                    foreach ($extra as $field => $field_data) {
                        if (isset($field_data[$row[$field]])) {
                            $row[$field] = $field_data[$row[$field]];
                        }
                    }
                }

                if (isset($row['company_id'])) {
                    $row['company_id'] = $to;
                }

                if (!empty($table_data['exclude'])) {
                    foreach ($table_data['exclude'] as $exclude_field) {
                        unset($row[$exclude_field]);
                    }
                }

                $row = explode('(###)', addslashes(implode('(###)', $row)));
                $rows[] = "('" . implode("', '", $row) . "')";
            }

            $query .= implode(', ', $rows);
            db_query($query);

        } else {
            foreach ($data as $id => $row) {
                if (!empty($table_data['key'])) {
                    $key = $row[$table_data['key']];

                    if (empty($clone_data)) {
                        unset($row[$table_data['key']]);
                    } else {
                        $row[$table_data['key']] = $clone_data[$row[$table_data['key']]];
                    }
                }

                if (isset($row['company_id'])) {
                    $row['company_id'] = $to;
                }

                if (!empty($extra)) {
                    foreach ($extra as $field => $field_data) {
                        if (isset($field_data[$row[$field]])) {
                            $row[$field] = $field_data[$row[$field]];
                        }
                    }
                }

                if (!empty($table_data['exclude'])) {
                    foreach ($table_data['exclude'] as $exclude_field) {
                        unset($row[$exclude_field]);
                    }
                }

                if (!empty($table_data['pre_process']) && function_exists($table_data['pre_process'])) {
                    call_user_func($table_data['pre_process'], $table_data, $row, $clone_data, $cloned_ids[$clone_id], $extra);
                }

                $new_key = db_query('REPLACE INTO ?:' . $table_data['name'] . ' ?e', $row);
                if (!empty($key)) {
                    $cloned_ids[$clone_id][$key] = $new_key;
                }

                if (!empty($table_data['return_clone_data'])) {
                    if (count($table_data['return_clone_data']) == 1 && reset($table_data['return_clone_data']) == $table_data['key']) {
                        $return[$table_data['key']][$key] = $new_key;
                    } else {
                        $_key = !empty($table_data['return_clone_data']) ? reset($table_data['return_clone_data']) : $table_data['key'];
                        $new_data = db_get_row('SELECT ' . implode(', ', $table_data['return_clone_data']) . ' FROM ?:' . $table_data['name'] . ' WHERE `' . $_key . '` = ?s', $new_key);

                        foreach ($table_data['return_clone_data'] as $field) {
                            $return[$field][$data[$id][$field]] = $new_data[$field];
                        }
                    }
                }

                if (!empty($table_data['post_process']) && function_exists($table_data['post_process'])) {
                    call_user_func($table_data['post_process'], $new_key, $table_data, $row, $clone_data, $cloned_ids[$clone_id], $extra);
                }
            }

            if (!empty($table_data['children'])) {
                $__data = !empty($table_data['return_clone_data']) ? reset($return) : $cloned_ids[$clone_id];
                foreach ($table_data['children'] as $child_data) {
                    if (!empty($child_data['data_from'])) {
                        if (Registry::get('clone_data.' . $child_data['data_from']) == 'Y') {
                            $data_from = $schema[$child_data['data_from']];
                            if (!empty($tables['tables'])) {
                                foreach ($tables['tables'] as $_table_data) {
                                    fn_clone_table_data($_table_data, $__data, 0, $from, $to);
                                }
                            } elseif (!empty($data_from['function']) && function_exists($data_from['function'])) {
                                call_user_func($data_from['function'], $table_data, $cloned_ids[$clone_id], $start, $from, $to, $extra);
                            }
                        }
                    } else {
                        fn_clone_table_data($child_data, $__data, 0, $from, $to);
                    }
                }
            }
        }
    }

    if (empty($clone_data)) {
        $total = db_get_field('SELECT COUNT(*) FROM ?:' . $table_data['name'] . ' WHERE company_id = ?i', $from);
        if ($total >= $start + $limit) {
            $start += $limit;

            fn_clone_table_data($table_data, array(), $start, $from, $to);
        }
    }

    return array($return, $cloned_ids[$clone_id]);
}

function fn_clone_post_process_pages($new_id, $table_data, $row, $clone_data, $new_ids)
{
    if (!empty($row['id_path'])) {
        $path = explode('/', $row['id_path']);
        $new_path = array();
        foreach ($path as $id) {
            $new_path[] = isset($new_ids[$id]) ? $new_ids[$id] : $id;
        }
        $path = implode('/', $new_path);
        $parent_id = isset($new_ids[$row['parent_id']]) ? $new_ids[$row['parent_id']] : 0;

        db_query('UPDATE ?:pages SET id_path = ?s, parent_id = ?i WHERE page_id = ?i', $path, $parent_id, $new_id);
    }
}

function fn_clone_layouts($data, $from, $to)
{
    // We need to clone logos, not attached to any layout too
    $logos = fn_get_logos($from, 0);

    if (!empty($logos)) {
        Registry::set('runtime.allow_upload_external_paths', true);

        foreach ($logos as $type => $logo) {
            fn_create_logo(array(
                'type' => $logo['type'],
                'layout_id' => $logo['layout_id'],
                'image_path' => !empty($logo['image']['absolute_path']) ? $logo['image']['absolute_path'] : ''
            ), $to);
        }

        Registry::set('runtime.allow_upload_external_paths', false);
    }

    return Layout::instance($from)->copy($to);
}

function fn_clone_create_table_data($table, $keys, $ids, $inserted_ids, $exclude = array())
{
    $key = reset($keys);
    $rows = db_get_array('SELECT * FROM ?:' . $table . ' WHERE ' . $key . ' IN (?a)', array_keys($ids));
    if (!empty($rows)) {
        $query = 'REPLACE INTO ?:' . $table . ' (' . implode(', ', fn_get_table_fields($table, $exclude, true)) . ') VALUES ';
        $data = array();
        foreach ($rows as $row) {
            $row = array_diff_key($row, array_flip($exclude));
            foreach ($keys as $key) {
                if (isset($inserted_ids[$row[$key]])) {
                    $row[$key] = $inserted_ids[$row[$key]];
                }
            }
            $data[] = '(\'' . implode("', '", $row) . '\')';
        }

        $query .= implode(', ', $data);

        db_query($query);
    }
}

function fn_clone_post_process_static_data($new_id, $table_data, $row, $clone_data, $new_ids)
{
    static $menus_ids = array();
    static $static_scheme = array();

    if (empty($static_scheme)) {
        $static_scheme = fn_get_schema('static_data', 'schema');
    }

    if (!empty($row['id_path'])) {
        $path = explode('/', $row['id_path']);
        $new_path = array();
        foreach ($path as $id) {
            $new_path[] = isset($new_ids[$id]) ? $new_ids[$id] : $id;
        }
        $path = implode('/', $new_path);
        $parent_id = isset($new_ids[$row['parent_id']]) ? $new_ids[$row['parent_id']] : 0;

        db_query('UPDATE ?:static_data SET id_path = ?s, parent_id = ?i WHERE param_id = ?i', $path, $parent_id, $new_id);
    }

    // Create owner item if needed
    if (!empty($row['section']) && !empty($static_scheme[$row['section']]['owner_object'])) {
        $owner_scheme = $static_scheme[$row['section']]['owner_object'];
        $old_owner = $row[$owner_scheme['param']];

        if (!empty($old_owner)) {
            if (isset($menus_ids[$old_owner])) {
                $new_owner = $menus_ids[$old_owner];
            } else {
                $owner_data = db_get_row('SELECT * FROM ?:' . $owner_scheme['table'] . ' WHERE ' . $owner_scheme['key'] . ' = ?i', $old_owner);
                unset($owner_data[$owner_scheme['key']]);
                $owner_data['company_id'] = $row['company_id'];

                $new_owner = db_query('INSERT INTO ?:' . $owner_scheme['table'] . ' ?e', $owner_data);
                $menus_ids[$old_owner] = $new_owner;

                if (!empty($owner_scheme['children'])) {
                    $owner_data = db_get_array('SELECT * FROM ?:' . $owner_scheme['children']['table'] . ' WHERE ' . $owner_scheme['children']['key'] . ' = ?i', $old_owner);
                    foreach ($owner_data as $_data) {
                        $_data[$owner_scheme['children']['key']] = $new_owner;
                        db_query('INSERT INTO ?:' . $owner_scheme['children']['table'] . ' ?e', $_data);
                    }
                }
            }

            db_query('UPDATE ?:static_data SET ' . $owner_scheme['param'] . ' = ?i WHERE param_id = ?i', $new_owner, $new_id);
        }
    }
}

function fn_clone_post_process_categories($new_id, $table_data, $row, $clone_data, $new_ids)
{
    if (!empty($row['id_path'])) {
        $path = explode('/', $row['id_path']);
        $new_path = array();
        foreach ($path as $id) {
            $new_path[] = isset($new_ids[$id]) ? $new_ids[$id] : $id;
        }
        $path = implode('/', $new_path);
        $parent_id = isset($new_ids[$row['parent_id']]) ? $new_ids[$row['parent_id']] : 0;

        db_query('UPDATE ?:categories SET id_path = ?s, parent_id = ?i WHERE category_id = ?i', $path, $parent_id, $new_id);
    }

    fn_clone_image_pairs($new_id, array_search($new_id, $new_ids), 'category');

    fn_update_product_count(array($new_id));
}

function fn_clone_post_process_filters($new_id, $table_data, $row, $clone_data, $new_ids)
{
    db_query('UPDATE ?:product_filters SET categories_path = ?s WHERE filter_id = ?i', '', $new_id);
}

function fn_clone_products($table_data, $clone_data, $start, $from, $to, $extra)
{
    if (!empty($clone_data)) {
        $limit = 50;
        $start = 0;

        do {
            $data = db_get_array('SELECT product_id, category_id FROM ?:products_categories WHERE link_type = ?s AND category_id IN (?a) LIMIT ?i, ?i', 'M', array_keys($clone_data), $start, $limit);

            foreach ($data as $item) {
                $result = fn_clone_product($item['product_id']);

                db_query('UPDATE ?:products SET company_id = ?i WHERE product_id = ?i', $to, $result['product_id']);
                db_query('UPDATE ?:products_categories SET category_id = ?i WHERE product_id = ?i AND link_type = ?s', $clone_data[$item['category_id']], $result['product_id'], 'M');

                // Get category ids to update product count
                $cids = db_get_fields('SELECT category_id FROM ?:products_categories WHERE product_id = ?i OR product_id = ?i', $item['product_id'], $result['product_id']);

                fn_update_product_count($cids);
            }

            $total = db_get_field('SELECT COUNT(*) FROM ?:products_categories WHERE link_type = ?s AND category_id IN (?a)', 'M', array_keys($clone_data));

            $start += $limit;

        } while ($total >= $start);
    }
}

function fn_share_products($table_data, $clone_data, $start, $from, $to, $extra)
{
    if (!empty($clone_data)) {
        $limit = 50;
        $start = 0;

        do {
            $data = db_get_array('SELECT product_id, category_id FROM ?:products_categories WHERE category_id IN (?a) ORDER BY category_id LIMIT ?i, ?i', array_keys($clone_data), $start, $limit);

            if (!empty($data)) {
                foreach ($data as $item) {
                    $_share_data = array(
                        'product_id' => $item['product_id'],
                        'category_id' => $clone_data[$item['category_id']],
                        'link_type' => 'A',
                        'position' => 0,
                    );

                    db_query('REPLACE INTO ?:products_categories ?e', $_share_data);

                    fn_check_and_update_product_sharing($item['product_id']);
                }
            }

            $total = db_get_field('SELECT COUNT(*) FROM ?:products_categories WHERE category_id IN (?a)', array_keys($clone_data));

            $start += $limit;

        } while ($total >= $start);
    }
}

/**
 * Checks if parameters correspond the condition
 *
 * @param array $params Passed parameters data
 * @param array $condition Conditions
 * @return boolean Result of the checking
 */
function fn_ult_check_display_condition($params, $condition)
{
    $result = true;
    foreach ($condition as $field => $value) {
        if ((!empty($value) && !isset($params[$field])) || (isset($params[$field]) && isset($value) && ($params[$field] != $value && (!is_array($value) || !in_array($params[$field], $value))))) {
            $result = false;
        }
    }

    return $result;
}

function fn_ult_get_controller_shared_companies($object_id, $controller = '', $mode = '')
{
    if (empty($object_id)) {
        return false;
    }

    static $sharing_schema;
    if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
        $sharing_schema = fn_get_schema('sharing', 'schema');
    }

    $controller = empty($controller) ? Registry::get('runtime.controller') : $controller;
    $mode = empty($mode) ? Registry::get('runtime.mode') : $mode;

    foreach ($sharing_schema as $object => $data) {
        if ($data['controller'] == $controller && $data['mode'] == $mode) {
            if ($data['type'] == 'tpl_tabs') {
                $companies = fn_ult_get_object_shared_companies($object, $object_id);

                return $companies ? implode(',', $companies) : '';
            }
        }
    }

    return false;
}

function fn_ult_get_object_shared_companies($object, $object_id)
{
    $companies = db_get_fields(
        'SELECT share_company_id AS company_id'
        . ' FROM ?:ult_objects_sharing'
        . ' WHERE share_object_type = ?s AND share_object_id = ?s',
        $object, $object_id
    );

    return $companies;
}

function fn_ult_check_users_usergroup_companies($user_id)
{
    if (Registry::get('runtime.company_id')) {
        $user_groups = fn_get_user_usergroups($user_id);
        foreach ($user_groups as $user_group) {
            if ($user_group['status'] == 'A') {
                $user_group_companies = fn_ult_get_object_shared_companies('usergroups', $user_group['usergroup_id']);
                if (in_array(Registry::get('runtime.company_id'), $user_group_companies)) {
                    return true;
                }
            }
        }
        if ((defined('RESTRICTED_ADMIN') || $_SESSION['auth']['is_root'] == 'Y') && $user_id == $_SESSION['auth']['user_id']) {
            return true;
        }
    }

    return false;
}

function fn_get_double_user_emails()
{
    return db_get_fields('SELECT email FROM ?:users GROUP BY email HAVING COUNT(*) > 1');
}

function fn_ult_parse_api_request($object, &$params)
{
    static $sharing_schema;
    if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
        $sharing_schema = fn_get_schema('sharing', 'schema');
    }

    foreach ($sharing_schema as $object_id => $object_schema) {
        if (!empty($object_schema['api']) && $object == $object_schema['api']) {
            if (Registry::get('runtime.company_id')) {
                $company_id = Registry::get('runtime.company_id');

            } elseif (isset($params['company_id'])) {
                $company_id = $params['company_id'];

            } else {
                $company_id = 0;
            }

            Registry::set('sharing_owner.' . $object_id, $company_id);
            $params['company_id'] = $company_id;
        }
    }

    if (!empty($request['share_objects']) && !Registry::get('runtime.company_id')) {
        fn_ult_update_share_objects($request);
    }
}

function fn_ult_parse_request($request)
{
    static $sharing_schema;
    if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
        $sharing_schema = fn_get_schema('sharing', 'schema');
    }

    foreach ($sharing_schema as $object_id => $object) {
        if (!empty($object['request_object'])) {
            if (isset($request[$object['request_object']])) {
                if (Registry::get('runtime.company_id')) {
                    $company_id = Registry::get('runtime.company_id');
                } elseif (isset($request[$object['request_object']]['company_id'])) {
                    $company_id = $request[$object['request_object']]['company_id'];
                } else {
                    $company_id = 0;
                }

                Registry::set('sharing_owner.' . $object_id, $company_id);
            }
        }
    }

    if (!empty($request['share_objects']) && !Registry::get('runtime.company_id')) {
        fn_ult_update_share_objects($request);
    }
}

function fn_ult_update_share_objects($share_data)
{

    static $sharing_schema;
    if (empty($sharing_schema) && Registry::get('addons_initiated') === true) {
        $sharing_schema = fn_get_schema('sharing', 'schema');
    }

    foreach ($share_data['share_objects'] as $object_type => $object_data) {
        if (!empty($object_data)) {
            foreach ($object_data as $object_id => $companies) {
                if (empty($companies)) {
                    $companies = array();
                }

                if (!empty($sharing_schema[$object_type]['pre_processing']) && function_exists($sharing_schema[$object_type]['pre_processing'])) {
                    call_user_func_array($sharing_schema[$object_type]['pre_processing'], array(
                        'object_id' => $object_id,
                        'object_type' => $object_type,
                        'companies' => $companies,
                    ));
                }

                db_query('DELETE FROM ?:ult_objects_sharing WHERE share_object_id = ?s AND share_object_type = ?s', $object_id, $object_type);

                if (!empty($sharing_schema[$object_type]['table'])) {
                    $company_id = Registry::get('sharing_owner.' . $object_type);
                    if (empty($company_id)) {
                        $fields = fn_get_table_fields($sharing_schema[$object_type]['table']['name']);
                        if (in_array('company_id', $fields)) {
                            $owner_id = db_get_field(
                                'SELECT company_id'
                                . ' FROM ?:' . $sharing_schema[$object_type]['table']['name']
                                . ' WHERE ' . $sharing_schema[$object_type]['table']['key_field'] . ' = ?s',
                                $object_id
                            );
                            if (!in_array($owner_id, $companies)) {
                                $companies[] = $owner_id;
                            }
                        }
                    } else {
                        $companies[] = $company_id;
                    }
                }

                if (!empty($companies)) {
                    $companies = array_unique($companies);
                    $query = array();

                    // Get new object id if it was updated
                    if (!empty($share_data[$object_type][$object_id][$sharing_schema[$object_type]['table']['key_field']])) {
                        $object_id = $share_data[$object_type][$object_id][$sharing_schema[$object_type]['table']['key_field']];
                    }

                    foreach ($companies as $company_id) {
                        if (!empty($company_id)) {
                            $query[] = db_quote('(?s, ?s, ?i)', $object_id, $object_type, $company_id);
                        }
                    }

                    if (!empty($query)) {
                        db_query('REPLACE INTO ?:ult_objects_sharing (share_object_id, share_object_type, share_company_id) VALUES ' . implode(', ', $query));
                    }
                }

                if (!empty($sharing_schema[$object_type]['post_processing']) && function_exists($sharing_schema[$object_type]['post_processing'])) {
                    call_user_func_array($sharing_schema[$object_type]['post_processing'], array(
                        'object_id' => $object_id,
                        'object_type' => $object_type,
                        'companies' => $companies,
                    ));
                }
            }
        }
    }
}

function fn_ult_update_share_object($object_id, $object, $company_id)
{
    //$object_id can be a string value, for example for a lang variables.
    $id = db_query('REPLACE INTO ?:ult_objects_sharing (share_object_id, share_object_type, share_company_id) VALUES (?s, ?s, ?i)', $object_id, $object, $company_id);

    return $id;
}

function fn_check_object_exists_for_root($controller = '', $mode = '')
{
    $schema = fn_get_permissions_schema('admin');

    $controller = empty($controller) ? Registry::get('runtime.controller') : $controller;
    $mode = empty($mode) ? Registry::get('runtime.mode') : $mode;

    $vendor_only = false;

    if (!Registry::get('runtime.company_id')) {
        if (isset($schema[$controller]['modes'][$mode]['vendor_only'])) {
            $vendor_only = $schema[$controller]['modes'][$mode]['vendor_only'];
        } elseif (isset($schema[$controller]['vendor_only']) && is_array($schema[$controller]['vendor_only']['display_condition']) && !empty($schema[$controller]['vendor_only']['display_condition'])) {
            $vendor_only = fn_ult_check_display_condition($_REQUEST, $schema[$controller]['vendor_only']['display_condition']);
        } elseif (isset($schema[$controller]['vendor_only']) && $schema[$controller]['vendor_only'] == true) {
            $vendor_only = $schema[$controller]['vendor_only'];
        }
    }

    return $vendor_only;
}

function fn_set_page_company_id($page_data, $lang_code)
{
    if (isset($page_data['parent_id']) && $page_data['parent_id'] != 0) {
        $parent_page_data = fn_get_page_data($page_data['parent_id'], $lang_code);
        $company_id = $parent_page_data['company_id'];
    } elseif ($_company_id = Registry::get('runtime.company_id')) {
        $company_id = $_company_id;
    } else {
        $company_id = isset($page_data['company_id']) ? $page_data['company_id'] : 0;
    }

    return $company_id;
}

function fn_ult_share_page($share_pages, $page_companies)
{
    if (empty($share_pages) || empty($page_companies)) {
        return false;
    }

    foreach ($share_pages as $share_page_id) {
        db_query("DELETE FROM ?:ult_objects_sharing WHERE share_object_id = ?i AND share_object_type = 'pages'", $share_page_id);
        foreach ($page_companies as $page_company) {
            db_query("REPLACE INTO ?:ult_objects_sharing (share_object_id, share_company_id, share_object_type) VALUES (?i, ?i, 'pages')", $share_page_id, $page_company);
        }
    }
}

/**
 * Shares product option among the companies for which the given product is shared.
 *
 * @param int $option_id Option identifier
 * @param int $product_id Product identifier
 */
function fn_ult_share_product_option($option_id, $product_id)
{
    $product_company_ids = fn_ult_get_shared_product_companies($product_id);
    foreach ($product_company_ids as $product_company_id) {
        fn_ult_update_share_object($option_id, 'product_options', $product_company_id);
    }
}

function fn_ult_increase_category_level(&$categories)
{
    foreach ($categories as &$cat) {
        if (isset($cat['level'])) {
            $cat['level']++;
        }
        if (!empty($cat['subcategories'])) {
            fn_ult_increase_category_level($cat['subcategories']);
        }
    }
}

function fn_ult_get_categories(&$params, &$join, &$condition, &$fields, &$group_by, &$sortings)
{
    $fields[] = '?:categories.company_id';
    $condition .= fn_get_company_condition('?:categories.company_id');
    if (!empty($params['company_ids']) && Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND ?:categories.company_id IN (?a)", explode(',', $params['company_ids']));
    }
}

function fn_ult_get_categories_before_cut_levels(&$tmp, &$params)
{
    if (!Registry::get('runtime.company_id')) {
        fn_ult_increase_category_level($tmp);
        if (empty($params['category_id'])) {
            $_tmp = $tmp;
            if (!empty($params['group_by_level']) && empty($params['plain'])) {
                foreach ($_tmp as $id => $c) {
                    if (!empty($c['company_id'])) {
                        unset($_tmp[$id]);
                        $_key = 'company_' . $c['company_id'];
                        $_tmp[$_key]['company_categories'] = 'Y';
                        $_tmp[$_key]['category_id'] = $c['category_id'];
                        $_tmp[$_key]['company_id'] = $c['company_id'];
                        $_tmp[$_key]['subcategories'][] = $c;
                    }
                }
                foreach ($_tmp as $key => $company_categories) {
                    if (!empty($company_categories['company_id'])) {
                        $_tmp[$key]['category'] = __('vendor') . ': ' . fn_get_company_name($company_categories['company_id']);
                    }
                }
            } elseif (!empty($params['group_by_level']) && !empty($params['plain'])) {
                $result_cat = array();
                foreach ($_tmp as $cat) {
                    $result_cat[$cat['company_id']][] = $cat;
                }
                $_tmp = array();
                foreach ($result_cat as $cid => $cats) {
                    $_tmp[] = array('category' => __('vendor') . ': ' . fn_get_company_name($cid), 'store' => true);
                    $_tmp = array_merge($_tmp, $cats);
                }
            }
            $tmp = $_tmp;
        }
    }
}

/**
 * Checks if product is currently accessible for selected store
 *
 * @param array $product Product data
 * @param boolean $result Flag that defines if product is accessible
 * @return boolean Always true
 */
function fn_ult_is_accessible_product_post(&$product, &$result)
{
    if (Registry::get('runtime.company_id')) {
        $product_company_id = db_get_field('SELECT company_id FROM ?:products WHERE product_id = ?i', $product['product_id']);

        if ($product_company_id != Registry::get('runtime.company_id')) {
            $is_shared = fn_ult_is_shared_product($product['product_id'], Registry::get('runtime.company_id'));

            if ($is_shared != 'Y') {
                $result = false;
            }
        }
    }

    return true;
}

/**
 * Changes the result of administrator access to profiles checking
 *
 * @param boolean $result Result of check : true if administeator has access, false otherwise
 * @param string $user_type Types of profiles
 * @return bool Always true
 */
function fn_ult_check_permission_manage_profiles(&$result, &$user_type)
{
    $params = array (
        'user_type' => $user_type
    );
    $result = $result && !fn_is_restricted_admin($params);

    if (Registry::get('runtime.company_id') && $result) {
        $result = true;
    }

    return true;
}

/**
 * Adds global not shared languages to store languages list
 *
 * @param array $languages Languages list
 * @param boolean $edit Flag that determines if language list is used to be edited
 * @return boolean Always true
 */
function fn_ult_get_translation_languages(&$languages, &$edit)
{
    if (Registry::get('runtime.company_id') && !$edit) {
        // adds not shared languges
        Registry::set('runtime.skip_sharing_selection', true);
        $languages = db_get_hash_array("SELECT ?:languages.* FROM ?:languages", 'lang_code');
        Registry::set('runtime.skip_sharing_selection', false);
    }

    return true;
}

function fn_ult_update_status_pre(&$status, &$status_data, &$type, &$lang_code, &$can_continue)
{
    $update_all_vendors = !empty($_REQUEST['update_all_vendors']) ? $_REQUEST['update_all_vendors'] : array();
    $data = $status_data;
    if (!Registry::get('runtime.company_id')) {
        //if we update both subj and header then we should delete all records. Othervise we should update necessary fields.
        if (isset($update_all_vendors['email_subj']) && $update_all_vendors['email_subj'] == 'Y' && isset($update_all_vendors['email_header']) && $update_all_vendors['email_header'] == 'Y') {
            db_query("DELETE FROM ?:ult_status_descriptions WHERE type = ?s AND status = ?s AND lang_code = ?s", $type, $status, $lang_code);
        } elseif (isset($update_all_vendors['email_subj']) && $update_all_vendors['email_subj'] == 'Y') {
            db_query("UPDATE ?:ult_status_descriptions SET email_subj = ?s WHERE type = ?s AND status = ?s AND lang_code = ?s", $data['email_subj'], $type, $status, $lang_code);
        } elseif (isset($update_all_vendors['email_header']) && $update_all_vendors['email_header'] == 'Y') {
            db_query("UPDATE ?:ult_status_descriptions SET email_header = ?s WHERE type = ?s AND status = ?s AND lang_code = ?s", $data['email_header'], $type, $status, $lang_code);
        }
    } else {
        $data['company_id'] = Registry::get('runtime.company_id');
        $data['lang_code'] = $lang_code;
        $data['type'] = $type;
        db_query("REPLACE INTO ?:ult_status_descriptions ?e", $data);
        $can_continue = false;
    }
}

function fn_ult_get_statuses_post(&$statuses, &$join, &$condition, &$type, &$status_to_select, &$additional_statuses, &$exclude_parent, &$lang_code, &$company_id)
{
    $_company_id = !empty($company_id) ? $company_id : Registry::get('runtime.company_id');
    if ($_company_id) {
        $data = db_get_hash_array("SELECT status,email_subj,email_header FROM ?:ult_status_descriptions WHERE type = ?s AND company_id = ?i AND lang_code = ?s", 'status', $type, $_company_id, $lang_code);
        foreach ($data as $status => $status_data) {
            if (isset($statuses[$status])) {
                $statuses[$status] = array_merge($statuses[$status], $status_data);
            }
        }
    }
}

function fn_ult_delete_status_post(&$status, &$type, &$can_delete)
{
    if (!empty($can_delete)) {
        db_query("DELETE FROM ?:ult_status_descriptions WHERE type = ?s AND status = ?s", $type, $status);
    }
}

function fn_ult_check_store_permission($params, &$redirect_controller)
{
    $result = true;
    $controller = Registry::get('runtime.controller');
    $redirect_controller = $controller;
    // FIXME: move in schema
    switch ($controller) {
        case 'products':
            if (!empty($params['product_id'])) {
                $key = 'product_id';
                $key_id = $params[$key];
                $table = 'products';
                $object_name = fn_get_product_name($key_id, DESCR_SL);
                $object_type = __('product');
                $check_store_permission = array(
                    'func' => 'fn_ult_check_store_permission_product',
                    'args' => array('$table', '$key', '$key_id'),
                );
            }
            break;

        case 'categories':
            if (!empty($params['category_id'])) {
                $key = 'category_id';
                $key_id = $params[$key];
                $table = 'categories';
                $object_name = fn_get_category_name($key_id, DESCR_SL);
                $object_type = __('category');
            }

            break;

        case 'orders':
            if (!empty($params['order_id'])) {
                $key = 'order_id';
                $key_id = $params[$key];
                $table = 'orders';
                $object_name = '#' . $key_id;
                $object_type = __('order');

            }
            break;

        case 'shippings':
            if (!empty($params['shipping_id'])) {
                $key = 'shipping_id';
                $key_id = $params[$key];
                $table = 'shippings';
                $object_name = fn_get_shipping_name($key_id, DESCR_SL);
                $object_type = __('shipping');
            }
            break;

        case 'promotions':
            if (!empty($params['promotion_id'])) {
                $key = 'promotion_id';
                $key_id = $params[$key];
                $table = 'promotions';
                $object_name = fn_get_promotion_name($key_id, DESCR_SL);
                $object_type = __('promotion');
            }
            break;

        case 'pages':
            if (!empty($params['page_id'])) {
                $key = 'page_id';
                $key_id = $params[$key];
                $table = 'pages';
                $object_name = fn_get_page_name($key_id, DESCR_SL);
                $object_type = __('content');
            }
            break;

        case 'profiles':
            if (!empty($params['user_id'])) {
                $key = 'user_id';
                $key_id = $params[$key];
                $table = 'users';
                $object_name = fn_get_user_name($key_id, DESCR_SL);
                $object_type = __('user');
                $check_store_permission = array(
                    'func' => 'fn_ult_check_store_permission_profiles',
                    'args' => array('$params', '$table', '$key', '$key_id'),
                );
            }
            break;

        case 'settings':
            if (!empty($params['section_id'])) {
                $object_name = $params['section_id'];
                $object_type = __('section');
                $table = 'settings';
                $check_store_permission = array(
                    'func' => 'fn_ult_check_store_permission_settings',
                    'args' => array('$object_name'),
                );
            }

            break;

        case 'shipments':
            if (!empty($params['shipment_id'])) {
                $key = 'shipment_id';
                $key_id = $params[$key];
                $table = 'shipments';
                $object_name = '#' . $key_id;
                $object_type = __('shipment');
                $check_store_permission = array(
                    'func' => 'fn_ult_check_store_permission_shipments',
                    'args' => array('$key_id'),
                );
            }

            break;

        case 'static_data':
            if (!empty($params['menu_id'])) {
                $key = 'menu_id';
                $key_id = $params[$key];
                $table = 'menus';
                $object_name = fn_get_menu_name($key_id);
                $object_type = __('menu');
                $redirect_controller = 'menus';
            }
            break;

        case 'companies':
            if (!empty($params['company_id'])) {
                $key = 'company_id';
                $key_id = $params[$key];
                $table = 'companies';
                $object_name = fn_get_company_name($key_id);
                $object_type = __('company');
            }
            break;
    }

    fn_set_hook('ult_check_store_permission', $params, $object_type, $object_name, $table, $key, $key_id);

    if (!empty($object_name)) {
        if (!empty($check_store_permission)) {

            $args = array();
            foreach ($check_store_permission['args'] as $arg) {
                if ($arg[0] == '$') {
                    $arg = ltrim($arg, "$");
                    $args[] = $$arg;
                }
            }

            $result = call_user_func_array($check_store_permission['func'], $args);

        } else {
            $result = fn_check_company_id($table, $key, $key_id) || fn_check_shared_company_id($table, $key_id);
        }
    }

    fn_set_hook('ult_check_store_permission_post', $params, $object_type, $object_name, $result);

    if ($result == false) {
        fn_set_notification('W', __('warning'), __('store_object_denied', array(
            '[object_type]' => $object_type,
            '[object_name]' => fn_truncate_chars($object_name, 20)
        )), '', 'store_object_denied');
    }

    return $result;
}

function fn_ult_check_store_permission_product($table, $key, $key_id)
{
    return fn_check_company_id($table, $key, $key_id) || fn_ult_is_shared_product($key_id, Registry::get('runtime.company_id')) == 'Y';
}

function fn_ult_check_store_permission_settings($object_name)
{
    return Settings::instance()->checkPermissionCompanyId($object_name, Registry::get('runtime.company_id'));
}

function fn_ult_check_store_permission_profiles($params, $table, $key, $key_id)
{
    if (Registry::get('runtime.company_id')) {
        $auth = $_SESSION['auth'];

        $result = fn_check_company_id($table, $key, $key_id) || !empty($params['area']);
        $result = $result || (fn_check_company_id($table, $key, $key_id, 0) && $auth['user_id'] == $key_id);

        if (!$result && Registry::get('settings.Stores.share_users') == 'Y') {
            $company_customers_ids = db_get_fields("SELECT user_id FROM ?:orders WHERE company_id = ?i", Registry::get('runtime.company_id'));
            $result = in_array($key_id, $company_customers_ids);
        }
    } else {
        $result = true;
    }

    return $result;
}

function fn_ult_check_store_permission_shipments($key_id)
{
    $condition = '';
    $join = " INNER JOIN ?:shipment_items as s ON s.order_id = o.order_id ";

    if (Registry::get('runtime.company_id')) {
        $condition .= db_quote(" AND o.company_id = ?i ", Registry::get('runtime.company_id'));
    }
    $condition .= db_quote(" AND s.shipment_id = ?i ", $key_id);

    return db_get_field("SELECT count(*) FROM ?:orders as o $join WHERE 1 $condition") > 0;
}

/**
 * Gets company storefront URLs
 * @param integer $company_id company ID
 * @param array $company_data company data (if passed, company_id won't be used)
 * @return array storefront URLs
 */
function fn_get_storefront_urls($company_id, $company_data = array())
{
    static $cache = array();
    $urls = array();

    $company_id = !empty($company_data) ? $company_data['company_id'] : $company_id;

    if (empty($company_id) && Registry::get('runtime.simple_ultimate')) {
        $company_id = Registry::get('runtime.forced_company_id');
    }

    if (!empty($company_id)) {
        if (!empty($cache[$company_id])) {
            $company_data = $cache[$company_id];
        }

        if (empty($company_data)) {
            $company_data = db_get_row('SELECT storefront, secure_storefront FROM ?:companies WHERE company_id = ?i', $company_id);
        }

        fn_set_hook('get_storefront_urls', $company_data);

        $cache[$company_id] = $company_data;

        $url = 'http://' . $company_data['storefront'];
        $secure_url = 'https://' . $company_data['secure_storefront'];

        $info = parse_url($url);
        $secure_info = parse_url($secure_url);

        $http_path = !empty($info['path']) ? str_replace('\\', '/', $info['path']) : '';
        $http_path = rtrim($http_path, '/');

        $https_path = !empty($secure_info['path']) ? str_replace('\\', '/', $secure_info['path']) : '';
        $https_path = rtrim($https_path, '/');

        $http_host = !empty($info['port']) ? $info['host'] . ':' . $info['port'] : $info['host'];
        $https_host = !empty($secure_info['port']) ? $secure_info['host'] . ':' . $secure_info['port'] : $secure_info['host'];

        $current_host = (defined('HTTPS')) ? $https_host : $http_host;
        $current_path = (defined('HTTPS')) ? $https_path : $http_path;

        $http_location = 'http://' . $http_host . $http_path;
        $https_location = 'https://' . $https_host . $https_path;

        $current_location = defined('HTTPS') ? $https_location : $http_location;

        $urls = array(
            'http_host' => $http_host,
            'http_path' => $http_path,
            'http_location' => $http_location,

            'https_host' => $https_host,
            'https_path' => $https_path,
            'https_location' => $https_location,

            'current_host' => $current_host,
            'current_path' => $current_path,
            'current_location' => $current_location
        );
    }

    return $urls;
}
